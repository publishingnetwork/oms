<?php defined('SYSPATH') or die('No direct script access.');
 
class Task_Downloadpaypal extends Minion_Task
{

    protected function _execute(array $params)
    {
		require_once(SYSPATH . '../vendor/autoload.php');
		
		Log::instance()->add(Log::INFO, 'Download execute: ' . print_r($_GET, true) . ' ' . print_r($_POST, true));
				
		$search_request_type = new TransactionSearchRequestType();
		$search_request_type->StartDate = date("Y-m-d\TH:i:sO", strtotime('-2 day 00:00'));
		
		$search_request = new TransactionSearchReq();
		$search_request->TransactionSearchRequest = $search_request_type;
		
		
		$service = new PayPalAPIInterfaceServiceService();
		try {
			$apiCredential = new PPSignatureCredential('tycg85_api1.gmail.com', 'WZNECZM4GQMFHMJ4', 'AFcWxV21C7fd0v3bYYYRCpSSRl31A9l6qEDi02xkaCpZfFqI8kuwQ9hS');
			
			/* wrap API method calls on the service object with a try catch */
			$response = $service->TransactionSearch($search_request,$apiCredential);

			if ($response->Ack == 'Success' || $response->Ack == 'SuccessWithWarning') {
				
				Log::instance()->add(Log::INFO, 'PaymentTransactions execute: ' . print_r($response->PaymentTransactions, true));
				
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
		
		try {
			$apiCredential = new PPSignatureCredential('payment_api1.holistichealthlabs.com', 'D4N7C9ZTXRLLGAW9', 'AeWPD6GoYXc3DoYa13UkIOrSS7wTAljOMT-Ry2mfagl8-zl9jK.wYp8d');
			
			/* wrap API method calls on the service object with a try catch */
			$response = $service->TransactionSearch($search_request,$apiCredential);

			if ($response->Ack == 'Success' || $response->Ack == 'SuccessWithWarning') {
				
				Log::instance()->add(Log::INFO, 'PaymentTransactions execute: ' . print_r($response->PaymentTransactions, true));
				
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
							$response = $service->GetTransactionDetails($request, $apiCredential);
							
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