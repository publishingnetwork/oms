<?php defined('SYSPATH') or die('No direct script access.');

class Controller_System extends Controller_Check {

	public function action_index() {
		$this->redirect('order');
	}


	public function action_scrape_fullfillment() {
		ORM::factory('Order')->_scrape_fullfillment();
	}
	
	public function action_update_paypal() {
		require_once(SYSPATH . '../vendor/autoload.php');
	
		set_time_limit(0);
		ignore_user_abort(true);
	
		$paypal_details = ORM::factory('PaypalDetail')
			->where('date_added', '>', DB::expr('DATE_SUB(NOW(), INTERVAL 21 day)'))
			->find_all();

		foreach ($paypal_details as $p_d) {
			$transaction_details = new GetTransactionDetailsRequestType();
			$transaction_details->TransactionID = $p_d->transaction_id;
			
			$request = new GetTransactionDetailsReq();
			$request->GetTransactionDetailsRequest = $transaction_details;
			
			$service = new PayPalAPIInterfaceServiceService();
			try {
				/* wrap API method calls on the service object with a try catch */
				$response = $service->GetTransactionDetails($request);
				
				if ($response->Ack == 'Success') {
					$p_d->status = $response->PaymentTransactionDetails->PaymentInfo->PaymentStatus;
					$p_d->save();
					
					Log::instance()->add(Log::INFO, 'PayPal status updated for # ' . $p_d->id . '. New status: ' . $response->PaymentTransactionDetails->PaymentInfo->PaymentStatus);
					
					if ($p_d->status == 'Refunded' || $p_d->status == 'Reversed') {
						$order = ORM::factory('Order', $p_d->order_id);
						$order->internal_status = 'refunded';
						$order->_update_affiliate_status();
						$order->save();
						Log::instance()->add(Log::INFO, 'Order internal status updated for # ' . $order->id . '. New status: REFUNDED');
					}
					
				} else {
					
					Log::instance()->add(Log::WARNING, 'Failed to fetch PayPal status for # ' . $p_d->id . '. Errors: ' . json_encode($response->Errors[0]));

				}
			} catch (Exception $e) {			
				Log::instance()->add(Log::WARNING, 'Failed to fetch PayPal status for # ' . $p_d->id . '. Error: ' . $e->getMessage());
			}
				
		}
	}

	public function action_download_paypal() {
		require_once(SYSPATH . '../vendor/autoload.php');
		
		Log::instance()->add(Log::INFO, 'Download: ' . print_r($_GET, true) . ' ' . print_r($_POST, true));
				
		$search_request_type = new TransactionSearchRequestType();
		$search_request_type->StartDate = date("Y-m-d\TH:i:sO", strtotime('-2 day 00:00'));
		
		$search_request = new TransactionSearchReq();
		$search_request->TransactionSearchRequest = $search_request_type;
		
		
		$service = new PayPalAPIInterfaceServiceService();
		try {
			/* wrap API method calls on the service object with a try catch */
			$response = $service->TransactionSearch($search_request);

			if ($response->Ack == 'Success') {
			
				Log::instance()->add(Log::INFO, 'PaymentTransactions: ' . print_r($response->PaymentTransactions, true));
				
				foreach ($response->PaymentTransactions as $transaction) {
				
					$paypal_details = ORM::factory('PaypalDetail')->where('transaction_id', '=', $transaction->TransactionID)->find();
				
					if (!$paypal_details->loaded()) {
						Log::instance()->add(Log::INFO, 'Found a PayPal transaction ' . $transaction->TransactionID . ' missing in the OMS. Veryfing item IDs...');
						
						
						$transaction_details = new GetTransactionDetailsRequestType();
						$transaction_details->TransactionID = $transaction->TransactionID;
						
						$request = new GetTransactionDetailsReq();
						$request->GetTransactionDetailsRequest = $transaction_details;
						
						$service = new PayPalAPIInterfaceServiceService();
						try {
							/* wrap API method calls on the service object with a try catch */
							$response = $service->GetTransactionDetails($request);
							
							if ($response->Ack == 'Success') {
							
								if (empty($response->PaymentTransactionDetails->PaymentItemInfo->PaymentItem[0]->Number)) {
									continue;
								}
								
								$item_id = $response->PaymentTransactionDetails->PaymentItemInfo->PaymentItem[0]->Number;

								//let's check if there's an item id and if it matches OMS product
								$product = ORM::factory('Product')->where('paypal_ids', 'REGEXP', '(^|,)[ ]*' . $item_id . '([^0-9]+|$)')->find();
								if ($product->loaded()) {
									$paypal_details = ORM::factory('PaypalDetail');
									$paypal_details->transaction_id = $transaction->TransactionID;
									$paypal_details->_from_paypal_response($response);
									$paypal_details->product_id = $product->id;
								
									if (empty($paypal_details->email)) {
										Log::instance()->add(Log::WARNING, 'Transaction is empty in api download, giving up');
										//$record->delete();
										return;
									}
								
								
									$paypal_details_check = ORM::factory('PaypalDetail')
										->where('email', '=', $paypal_details->email)
										->and_where('date_added', '>', DB::expr('DATE_SUB(NOW(), INTERVAL 24 HOUR)'))
										->find();
									
									if ($paypal_details_check->loaded()) {
										//we have an order for this PayPal email
										$order = ORM::factory('Order', $paypal_details_check->order_id);
										
										Log::instance()->add(Log::INFO, 'Found a PayPal order (API, no session_id) by email, order #' . $paypal_details->order_id);
									} else {
										//Second Attempt, try to match email address
										
										$shipping_details_check = ORM::factory('ShippingDetail')
											->where('email', '=', $paypal_details->email)
											->and_where('date_added', '>', DB::expr('DATE_SUB(NOW(), INTERVAL 24 HOUR)'))
											->find();
										
										if ($shipping_details_check->loaded()) {
											//we have an order for this PayPal email
											$order = ORM::factory('Order', $shipping_details_check->order_id);
											
											Log::instance()->add(Log::INFO, 'Found a ShippingDetail order (API, no session_id) by email, order #' . $shipping_details_check->order_id);
										} else {
											$order = ORM::factory('Order');
											$order->date_added = $paypal_details->date_added;//it already has nicely parsed PayPal date
											$order->public_id  = $order->_generate_public_id();

											if ($paypal_details->country == 'US') {
												$order->shipping_method = 'PMD';
											}
											
											$order->save();

										}

									}
									
									//affiliate_id may have been sent, otherwise don't overwrite
									$order->affiliate_id  = !empty($response->PaymentTransactionDetails->PaymentItemInfo->Custom) ?
											$response->PaymentTransactionDetails->PaymentItemInfo->Custom : $order->affiliate_id;
																		
									$order->comments = empty($paypal_details->notes) ? '' : DB::expr('CONCAT(comments, " ", ' . Database::instance()->escape($paypal_details->notes) . ')');
													
									$order->save();
								
									$p_t_o             = ORM::factory('ProductsToOrder');
									$p_t_o->product_id = $product->id;
									$p_t_o->order_id   = $order->id;
									$p_t_o->date_added = $paypal_details->date_added;
									$p_t_o->save();
								
									$paypal_details->order_id = $order->id;
									$paypal_details->save();
									
									Log::instance()->add(Log::INFO, 'New PayPal transaction downloaded and saved # ' . $transaction->TransactionID);
									
									if ($commission = $order->_calculate_commission()) {
										Log::instance()->add(Log::INFO, 'Commission calculated for DOWNLOADED order ' . $order->id . ', commission: $' . $commission . ', affiliate_id: ' . $order->affiliate_id);
									}
								}
							} else {
								
								Log::instance()->add(Log::WARNING, 'Failed to fetch PayPal status for # ' . $transaction->TransactionID . '. Errors: ' . json_encode($response->Errors[0]));
			
							}
						} catch (Exception $e) {			
							Log::instance()->add(Log::WARNING, 'Failed to fetch PayPal status for # ' . $transaction->TransactionID . '. Error: ' . $e->getMessage());
						}
						
						
					}
					
				}
				
			} else {
				
				Log::instance()->add(Log::WARNING, 'Failed to fetch PayPal status for # ' . $p_d->id . '. Errors: ' . json_encode($response->Errors[0]));

			}
		} catch (Exception $e) {	
		//die($e->getMessage());		
			Log::instance()->add(Log::WARNING, 'Failed to fetch PayPal status for # ' . $p_d->id . '. Error: ' . $e->getMessage());
		}
	}

	public function action_login() {
		$error = $message = '';
	
		$session = Session::instance();
	
		if ($this->request->method() == HTTP_REQUEST::POST) {

			$result = ORM::Factory('User')
				->where('login', '=', $this->request->post('login'))
				->where('password', '=', sha1($this->request->post('password')))
				->find_all();
			
			if ($result->count() == 1) {
				$user = $result[0];
			
				$session->set('user_id', $user->id);
				$this->redirect('order');
				
			} else {
				$error = 'Wrong credentials';
			}

		}

		$view = View::factory('login')
			->set('message', $message)
			->set('error', $error);
		
		$this->response->body($view);
	}



	public function action_get_settings() {
		$this->response->headers('Content-Type', 'application/json; charset=utf-8');

		$records = ORM::Factory('setting')->where('setting', '<>', 'password')->find_all();
		
		$settings = array();
		foreach ($records as $record) {
			$settings[$record->setting] = $record->value;
		}
		
		$this->response->body(json_encode(array(
			'status'   => 'success',
			'settings' => $settings,
		)));

	}

	public function action_catch_click() {
		$click = ORM::factory('Click');
		//var_dump($this->request->post());
		
		$post = $this->request->post();
		
		if (!empty($post)) {
			$click->_handle($post);
			
		}
	}
	
	public function action_catch_lead() {
		$click = ORM::factory('Click');
		$post = $this->request->post();
		
		if (!empty($post)) {
			
			$click->_handle_lead($post);
			
		}
	}	

	public function action_catch_data() {
		
		require_once(SYSPATH . '../vendor/autoload.php');
		
		if (HTTP_Request::POST == $this->request->method()) {
			$json = base64_decode($this->request->post('data'));

			Log::instance()->add(Log::INFO, 'Data (' . $this->request->post('type') . ') from ' . $this->request->post('domain') . ', session # ' . $this->request->post('session_id') . ', json: ' . $json.', post: '.json_encode($this->request->post()));
			
			
			$data = json_decode($json);

			unset($json);

			if (empty($data))
				return;
			
			//let's try to find if there's an order for this session #

			if ($this->request->post('type') == 'paypal') {
				$paypal_details = ORM::factory('PaypalDetail')->where('transaction_id', '=', $data->tx)->find();
				
				if ($paypal_details->loaded()) {
					Log::instance()->add(Log::WARNING, 'Duplicate PayPal transaction ' . $data->tx . ' skipped...');
					return;
				}
				
				
			} else {
				//check by email date, even session_id may be different o_O
				$shipping_details = ORM::factory('ShippingDetail')
					->where('email', '=', $data->email)
					->and_where('date_added', '>', DB::expr('DATE_SUB(NOW(), INTERVAL 1 HOUR)'))
					->find();
				
				if ($shipping_details->loaded()) {
					Log::instance()->add(Log::WARNING, 'Duplicate shipping details for email ' . $data->email . ' within 1 hour, skipping...');
					return;
				}
			}
			
			$order = ORM::factory('Order')
				->where('session_id', '=', $this->request->post('session_id'))
				->and_where('session_id', '<>', NULL)
				->find();

			if ($order->loaded()) {
				//make sure we don't save duplicates
				
				 if ($this->request->post('type') != 'paypal') {
					$shipping_details = ORM::factory('ShippingDetail')->where('order_id', '=', $order->id);
					
					if ($shipping_details->loaded()) {
						Log::instance()->add(Log::WARNING, 'Duplicate shipping details for order ' . $order->id . ' skipped...');
						return;
					}

				}
				
			}
			
			
			$field_translate = array(
				'paypal' => array(
					'mc_gross'             => 'gross',
					'address_street'       => 'address1',
					'payment_status'       => 'status',
					'payment_date'         => 'date_added',
					'address_zip'          => 'zip',
					'mc_fee'               => 'fee',
					'address_name'         => 'name',
					'address_country_code' => 'country',
					'address_city'         => 'city',
					'payer_email'          => 'email',
					'txn_id'               => 'transaction_id',
					'address_state'        => 'state',
					'item_name'            => 'item_title',
					'item_number'          => 'item_id',
					'shipping'             => 'shipping_cost',
					'memo'                 => 'notes',
				),
				'aweber' => array(
					'Country'        => 'country',
					'Zip_Code'       => 'zip',
					'Phone_Number'   => 'phone',
					'name'           => 'name',
					'Street_Address' => 'address1',
					'State'          => 'state',
					'email'          => 'email',
					'City'           => 'city',
				),
				'getresponse' => array(
					'custom_Country'   => 'country',
					'country'          => 'country',
					'custom_ZipPostal' => 'zip',
					'Zip'              => 'zip',
					'custom_Phone'     => 'phone',
					'Phone'            => 'phone',
					'name'             => 'name',
					'custom_Address'   => 'address1',
					'address'          => 'address1',
					'custom_State'     => 'state',
					'state'            => 'state',
					'email'            => 'email',
					'custom_City'      => 'city',
					'city'             => 'city',
				),
			);
			
			$record = $this->request->post('type') == 'paypal' ? ORM::factory('PaypalDetail') : ORM::factory('ShippingDetail');
			
			
			if ($this->request->post('type') != 'paypal') {
			
				if (!$order->loaded()) {
					
					//let's try to match by email
					$paypal_details_check = ORM::factory('PaypalDetail')
						->where('email', '=', $data->email)
						->and_where('date_added', '>', DB::expr('DATE_SUB(NOW(), INTERVAL 48 HOUR)'))
						->find();
					
					if ($paypal_details_check->loaded()) {
						//we have an order for this PayPal email
						$order = ORM::factory('Order', $paypal_details_check->order_id);
						
						Log::instance()->add(Log::INFO, 'Order for Shipping Details found by email ' . $data->email);
					} else {
						
						
						//affiliate_id may have been sent
						$order->affiliate_id    = $this->request->post('affiliate_id') ? $this->request->post('affiliate_id') : NULL;
						
						$order->session_id      = $this->request->post('session_id');
						$order->date_added      = DB::expr('NOW()');
						$order->public_id       = $order->_generate_public_id();
						//$order->comments   = $this->request->post('domain');
						
						$order->save()->reload();
						
						Log::instance()->add(Log::INFO, 'New order saved for session # ' . $order->session_id);
					}
				}
				
				$record->order_id   = $order->id;
				$record->type       = $this->request->post('type');
				//$record->date_added = DB::expr('NOW()');
				$record->date_added = $order->date_added;
				
			} else {
				
			
				if (!empty($data->tx)) {
					$record->transaction_id = $data->tx;
					$record->save();
					//it's an autoredirect
					
					Log::instance()->add(Log::INFO, 'PayPal saved transaction # ' . $data->tx . ' before calling API to prevent duplicates');
					
					$transaction_details = new GetTransactionDetailsRequestType();
					$transaction_details->TransactionID = $data->tx;
					
					$request = new GetTransactionDetailsReq();
					$request->GetTransactionDetailsRequest = $transaction_details;
					
					$service = new PayPalAPIInterfaceServiceService();
					try {
						/* wrap API method calls on the service object with a try catch */
						$response = $service->GetTransactionDetails($request);
						
						if ($response->Ack == 'Success') {
							//

							$record->_from_paypal_response($response);
							
							if (empty($record->email)) {
								Log::instance()->add(Log::WARNING, 'PayPal transaction is empty, giving up. Tx #: ' . $data->tx);
								$record->delete();
								return;
							}
							
							//let's check the date, if older than 7 days, don't include it
							if (time() - 60*60*24*7  >  strtotime($record->date_added)) {
								Log::instance()->add(Log::WARNING, 'PayPal transaction is too OLD ' . $data->tx . ' : ' . $record->date_added);
								$record->delete();
								return;
							}
							
							Log::instance()->add(Log::INFO, 'PayPal status updated for # ' . $data->tx . '. New status: ' . $response->PaymentTransactionDetails->PaymentInfo->PaymentStatus);
							
							//we may try to get affiliate_id from PayPal
							$order->affiliate_id  = !empty($response->PaymentTransactionDetails->PaymentItemInfo->Custom) ?
												$response->PaymentTransactionDetails->PaymentItemInfo->Custom : $order->affiliate_id;
						} else {
							
							Log::instance()->add(Log::WARNING, 'Failed to fetch PayPal status for # ' . $data->tx . '. Errors: ' . json_encode($response->Errors[0]));
							$record->delete();
		
						}
					} catch (Exception $e) {			
						Log::instance()->add(Log::WARNING, 'Failed to fetch PayPal status for # ' . $data->tx . '. Error: ' . $e->getMessage());
						$record->delete();
					}
					$product = ORM::factory('Product')->where('paypal_ids', 'REGEXP', '(^|,)[ ]*' . $record->item_id . '([^0-9]+|$)')->find();
				} else {
					$record->net = $data->mc_gross - $data->mc_fee;
					$product = ORM::factory('Product')->where('paypal_ids', 'REGEXP', '(^|,)[ ]*' . $data->item_number . '([^0-9]+|$)')->find();
				}

				if (!$order->loaded()) {
					$order->affiliate_id    = $this->request->post('affiliate_id') ? $this->request->post('affiliate_id') : NULL;		
							
					$paypal_details = ORM::factory('PaypalDetail')
						->where('email', '=', $record->email)
						->and_where('date_added', '>', DB::expr('DATE_SUB(NOW(), INTERVAL 24 HOUR)'))
						->find();
					
					if ($paypal_details->loaded()) {
						//we have an order for this PayPal email
						$order = ORM::factory('Order', $paypal_details->order_id);
						
						Log::instance()->add(Log::INFO, 'Found a PayPal order by email (session_id incorrect), order #' . $paypal_details->order_id);
					} else {
						$order->session_id = $this->request->post('session_id');
						//$order->date_added = DB::expr('NOW()');
						$order->date_added = $record->date_added;
						$order->public_id  = $order->_generate_public_id();
						//$order->comments   = $this->request->post('domain');
						$order->save();
						
						Log::instance()->add(Log::INFO, 'New order saved for session # ' . $order->session_id);
					}
				}				
				$order->comments = empty($record->notes) ? '' : DB::expr('CONCAT(comments, " ", ' . Database::instance()->escape($record->notes) . ')');

				$record->order_id   = $order->id;

				if ($product->loaded()) {
					$record->product_id = $product->id;
					$p_t_o              = ORM::factory('ProductsToOrder');
					$p_t_o->product_id  = $product->id;
					$p_t_o->order_id    = $order->id;
					$p_t_o->date_added  = DB::expr('NOW()');
					$p_t_o->save();
				} else {
					Log::instance()->add(Log::ERROR, 'Failed to match OMS product for PayPal # ' . $data->item_number);
				}

			}
		
			if (empty($data->tx)) {
				
			
				if (!empty($data->payment_date)) {
					$date = new DateTime($data->payment_date);
					$data->payment_date = $date->setTimeZone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');		
				}
								
				foreach ($field_translate[$this->request->post('type')] as $foreign => $ours) {
					//$record->$ours = !empty($data->$foreign) ? trim($data->$foreign) : DB::expr('NULL');
					if (!empty($data->$foreign)) {
						$record->$ours = trim($data->$foreign);
					}
				}
				
				if ($record->country == 'USA')
					$record->country = 'US';
			}
			$record->save();
			
			//default shipping
			if ($record->country == 'US') {
				$order->shipping_method = 'PMD';
				$order->save();
			}
			
			Log::instance()->add(Log::INFO, 'New ' . $this->request->post('type') . ' record saved for session # ' . $order->session_id);

			$order->ip              = $this->request->post('ip');
			$order->user_agent_hash = $this->request->post('user_agent_hash');
			$order->save();
					
			if ($commission = $order->_calculate_commission()) {
				Log::instance()->add(Log::INFO, 'Commission calculated for order ' . $order->id . ', commission: $' . $commission . ', affiliate_id: ' . $order->affiliate_id);
			}

		} else {
			Log::instance()->add(Log::WARNING, 'GET request to catch_data');
		}
		
		
	}

	public function action_catch_fullfillment_update() {
		//action=shipped&orderid=276&xpertorderid=1535583&trackingnumber=9410810200881640000000&shippingcompany=USPS
		
		$fullfillment_id = $this->request->post('xpertorderid');
		$public_id       = $this->request->post('orderid');
		$tracking_id     = $this->request->post('trackingnumber');
		//$shipping_method = $this->request->post('shippingcompany');
		$action          = $this->request->post('action');
		
		$order = ORM::factory('Order')->where('public_id', '=', $public_id)->or_where('fullfillment_id', '=', $fullfillment_id)->find();
		
		if (!$order->loaded()) {
			Log::instance()->add(Log::WARNING, 'Xpert Notification for unknown order JSON: ' . json_encode($this->request->post()));
			return;
		}
		
		Log::instance()->add(Log::INFO, 'Xpert Notification for order #' . $order->id . ' JSON: ' .json_encode($this->request->post()));
		
		$order->fullfillment_id = $fullfillment_id;
		$order->tracking_id = $tracking_id;
		//$order->shipping_method = $shipping_method;
		$order->fullfillment_status = $action;

		$order->save();		
	}

	public function action_logout() {
		$session = Session::instance();
		$session->delete('user_id');
		$session->delete('user');
		$this->redirect('system/login');
	}

/*
	public function action_ipn() {
		require_once(Kohana::find_file('vendor', 'paypal/ipnlistener'));
		
		$listener = new IpnListener();
		//$listener->use_sandbox = true;

		try {
			$verified = $listener->processIpn();
		} catch (Exception $e) {
			Log::instance()->add(Log::ERROR, $e->getMessage());
			exit(0);
		}
		
		
		$post = $this->request->post();
		if ($verified) {
			Log::instance()->add(Log::INFO, 'Verified IPN: ' . json_encode($post));
				
			if ($post['txn_type'] == 'subscr_payment') {
			
				if ($post['payment_status'] == 'Completed') {
				
					//let's see if we should use IPN subscription id or some other one
					
					if (count(explode('||', $post['custom'])) == 2) {
						//renewal
						list($email, $subscription_id) = explode('||', $post['custom']);
						$user = ORM::factory('user')
							->where('subscription_id', '=', $subscription_id)
							->or_where('email', '=', $email)
							->find();
							
						if (!$user->loaded()) {	
							Log::instance()->add(Log::WARNING, 'Renewal attempt for a missing user: ' . $email . ', subscription ' . $subscription_id);
							exit;	
						} else {
							Log::instance()->add(Log::INFO, 'Manual renewal : ' . $email . ', subscription ' . $subscription_id . ' replaced with ' . $post['subscr_id']);
						}
					} else {
						$user = ORM::factory('user')->where('subscription_id', '=', $post['subscr_id'])->find();
					}
				
					
					
					if ($user->loaded()) {
						$user->active_through = DB::expr('FROM_UNIXTIME(' . strtotime('+7 day 23:59') . ')');
						$user->subscription_id = $post['subscr_id'];
						$user->save();
						Log::instance()->add(Log::INFO, 'Subscription renewed #' . $post['subscr_id']);
					} else {
					
						$user = ORM::factory('user');
						list($user->name, $user->email, $user->password) = explode('||', $post['custom']);
						$user->password = md5($user->password);
						$user->date_added = DB::expr('NOW()');
						$user->active_through = DB::expr('FROM_UNIXTIME(' . strtotime('+7 day 23:59') . ')');
						$user->subscription_id = $post['subscr_id'];
						$user->save();
						
						if ($user->saved()) {
							Log::instance()->add(Log::INFO, 'New user ' . $user->name . ', ' . $user->email);
						}
					
	
					}
				} else {
					Log::instance()->add(Log::WARNING, 'Wrong payment status: ' . $post['payment_status'] . ' ' . json_encode($post));
				}

	
			}

			

			
		} else {
			Log::instance()->add(Log::ERROR, 'Failed to verify PayPal IPN ' . json_encode($post));
		}
	}
*/
	public function action_manual_download_paypal() {
		require_once(SYSPATH . '../vendor/autoload.php');
		
		Log::instance()->add(Log::INFO, 'Download manual: ' . print_r($_GET, true) . ' ' . print_r($_POST, true));
				
		$search_request_type = new TransactionSearchRequestType();
		$search_request_type->StartDate = date("Y-m-d\TH:i:sO", strtotime('-10 day 00:00'));
		//$search_request_type->StartDate = date("Y-m-d\TH:i:sO", strtotime('-4 day 23:59'));
		//$search_request_type->TransactionID = '9XC746231V9647441';
		
		
		$search_request = new TransactionSearchReq();
		$search_request->TransactionSearchRequest = $search_request_type;
		
		
		$service = new PayPalAPIInterfaceServiceService();
		try {
			/* wrap API method calls on the service object with a try catch */
			$response = $service->TransactionSearch($search_request);

			if ($response->Ack == 'Success' || $response->Ack == 'SuccessWithWarning') {
				//echo '<pre>';var_dump($response);exit;
				Log::instance()->add(Log::INFO, 'PaymentTransactions manual: ' . print_r($response->PaymentTransactions, true));
				
				foreach ($response->PaymentTransactions as $transaction) {
				
					$paypal_details = ORM::factory('PaypalDetail')->where('transaction_id', '=', $transaction->TransactionID)->find();
				
					if (!$paypal_details->loaded()) {
						Log::instance()->add(Log::INFO, 'Found a PayPal transaction ' . $transaction->TransactionID . ' missing in the OMS. Veryfing item IDs...');
						
						
						$transaction_details = new GetTransactionDetailsRequestType();
						$transaction_details->TransactionID = $transaction->TransactionID;
						
						$request = new GetTransactionDetailsReq();
						$request->GetTransactionDetailsRequest = $transaction_details;
						
						$service = new PayPalAPIInterfaceServiceService();
						try {
							/* wrap API method calls on the service object with a try catch */
							$response = $service->GetTransactionDetails($request);
							var_dump($response);
							if ($response->Ack == 'Success') {
							
								if (empty($response->PaymentTransactionDetails->PaymentItemInfo->PaymentItem[0]->Number)) {
									continue;
								}
								
								$item_id = $response->PaymentTransactionDetails->PaymentItemInfo->PaymentItem[0]->Number;

								//let's check if there's an item id and if it matches OMS product
								$product = ORM::factory('Product')->where('paypal_ids', 'REGEXP', '(^|,)[ ]*' . $item_id . '([^0-9]+|$)')->find();
								if ($product->loaded()) {
									$paypal_details = ORM::factory('PaypalDetail');
									$paypal_details->transaction_id = $transaction->TransactionID;
									$paypal_details->_from_paypal_response($response);
									$paypal_details->product_id = $product->id;
								
									$paypal_details_check = ORM::factory('PaypalDetail')
										->where('email', '=', $paypal_details->email)
										->and_where('date_added', '>', DB::expr('DATE_SUB(NOW(), INTERVAL 24 HOUR)'))
										->find();
									
									if ($paypal_details_check->loaded()) {
										//we have an order for this PayPal email
										$order = ORM::factory('Order', $paypal_details_check->order_id);
										
										Log::instance()->add(Log::INFO, 'Found a PayPal order (API, no session_id) by email, order #' . $paypal_details->order_id);
									} else {
										//Second Attempt, try to match email address
										
										$shipping_details_check = ORM::factory('ShippingDetail')
											->where('email', '=', $paypal_details->email)
											->and_where('date_added', '>', DB::expr('DATE_SUB(NOW(), INTERVAL 24 HOUR)'))
											->find();
										
										if ($shipping_details_check->loaded()) {
											//we have an order for this PayPal email
											$order = ORM::factory('Order', $shipping_details_check->order_id);
											
											Log::instance()->add(Log::INFO, 'Found a ShippingDetail order (API, no session_id) by email, order #' . $shipping_details_check->order_id);
										} else {
											$order = ORM::factory('Order');
											$order->date_added = $paypal_details->date_added;//it already has nicely parsed PayPal date
											$order->public_id  = $order->_generate_public_id();

											if ($paypal_details->country == 'US') {
												$order->shipping_method = 'PMD';
											}
											
											$order->save();

										}

									}
									
									//affiliate_id may have been sent, otherwise don't overwrite
									$order->affiliate_id  = !empty($response->PaymentTransactionDetails->PaymentItemInfo->Custom) ?
											$response->PaymentTransactionDetails->PaymentItemInfo->Custom : $order->affiliate_id;
																		
									$order->comments = empty($paypal_details->notes) ? '' : DB::expr('CONCAT(comments, " ", ' . Database::instance()->escape($paypal_details->notes) . ')');
													
									$order->save();
								
									$p_t_o             = ORM::factory('ProductsToOrder');
									$p_t_o->product_id = $product->id;
									$p_t_o->order_id   = $order->id;
									$p_t_o->date_added = $paypal_details->date_added;
									$p_t_o->save();
								
									$paypal_details->order_id = $order->id;
									$paypal_details->save();
									
									Log::instance()->add(Log::INFO, 'New PayPal transaction downloaded and saved # ' . $transaction->TransactionID);
									
									if ($commission = $order->_calculate_commission()) {
										Log::instance()->add(Log::INFO, 'Commission calculated for DOWNLOADED order ' . $order->id . ', commission: $' . $commission . ', affiliate_id: ' . $order->affiliate_id);
									}
								}
							} else {
								
								Log::instance()->add(Log::WARNING, 'Failed to fetch PayPal status for # ' . $transaction->TransactionID . '. Errors: ' . json_encode($response->Errors[0]));
			
							}
						} catch (Exception $e) {			
							Log::instance()->add(Log::WARNING, 'Failed to fetch PayPal status for # ' . $transaction->TransactionID . '. Error: ' . $e->getMessage());
						}
						
						
					}
					
				}
				
			} else {
				
				Log::instance()->add(Log::WARNING, 'Failed to fetch PayPal status for # ' . $p_d->id . '. Errors: ' . json_encode($response->Errors[0]));

			}
		} catch (Exception $e) {	
		die($e->getMessage());		
			Log::instance()->add(Log::WARNING, 'Failed to fetch PayPal status for # ' . $p_d->id . '. Error: ' . $e->getMessage());
		}
	}
}
