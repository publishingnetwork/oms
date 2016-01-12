<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Order extends Controller_Check {

	protected $_mismatch_fields = array('email', 'name', 'address1', 'address2', 'zip', 'country', 'city', 'state');

	public function action_index() {
		require_once(Kohana::find_file('vendor', 'geo_arrays'));

		$per_page = 30;
		$page           = !empty($_GET['page']) ? $_GET['page'] : 1;
		$filter         = !empty($_GET['filter']) ? $_GET['filter'] : '';
                $paypal_account = !empty($_GET['paypal_account']) ? $_GET['paypal_account'] : 'any';
		$search         = !empty($_GET['search']) ? trim($_GET['search']) : '';
		$date_start     = !empty($_GET['date_start']) ? $_GET['date_start'] : '';
		$date_end       = !empty($_GET['date_end']) ? $_GET['date_end'] : '';
		$affiliate      = !empty($_GET['affiliate']) ? $_GET['affiliate'] : '';
		empty($date_start) &&
			$date_start = date('m/d/Y', strtotime('-7 day 00:00'));

		$orders = ORM::Factory('Order');

		//so not to mess with guessing whether and_where or where should be used
		$orders->where(DB::expr('1'), '=', 1);

		if ($filter == 'unprocessed') {
			$orders->and_where('internal_status', 'IN', DB::expr('("new", "email")'));
		}

		if (!empty($date_start)) {
			$orders->and_where('date_added', '>=', date('Y-m-d 00:00', strtotime($date_start)));
		}

		if (!empty($date_end)) {
			$orders->and_where('date_added', '<=', date('Y-m-d 00:00', strtotime($date_end)));
		}

		if (!empty($affiliate)) {

			if ($affiliate == 'any') {
				$orders->and_where('affiliate_id', 'IS NOT', NULL);
			} elseif ($affiliate == 'direct') {
				$orders->and_where('affiliate_id', 'IS', NULL);
			} else {
				$orders->and_where('affiliate_id', '=', $affiliate);
			}

		}

		$order_count = 0;

		$orders = $orders->order_by('date_added', 'DESC')->find_all();

		$orders_view = array();
                $paypal_order_ids = array();
		foreach ($orders as $order) {

			$order_rows = array();

			if (!empty($order->affiliate_id)) {
				$affiliate_name = ORM::factory('Affiliate', $order->affiliate_id)->name;
			} else {
				$affiliate_name = null;
			}

			$additional = array('internal_status', 'tracking_id', 'shipping_method', 'fullfillment_status', 'fullfillment_id', 'comments', 'public_id', 'commission', 'affiliate_status', 'campaign', 'subcampaign', 'ip');

			$paypal_details = $order->PaypalDetails->order_by('date_added', 'ASC')->find_all()->as_array();


			if (count($paypal_details) > 0) {
				$current_paypal = array_shift($paypal_details);
				$current_paypal = $current_paypal->as_array();

                                // This filters paypal orders by one of merchant accounts
				if($paypal_account!='any' && $filter=='paypal' && $paypal_account!=$current_paypal['paypal_account']){
					continue;
				}

				foreach ($additional as $a) {
					$current_paypal[$a] = $order->$a;
				}
				$current_paypal['skus'] = $order->_get_skus();

				if (empty($order->ShippingDetail->id)) {
					$current_paypal['checkbox'] = true;
				}

				if (!empty($affiliate_name)) {
					$current_paypal['affiliate'] = $affiliate_name;
				}

                                $paypal_order_ids[] = $current_paypal['order_id'];
				$order_rows[] = $current_paypal;
			}

			if (!empty($order->ShippingDetail->id)) {
				$shipping_detail = $order->ShippingDetail->as_array();


				if($filter == 'paypal' && !in_array($shipping_detail['order_id'], $paypal_order_ids)){
					continue;
				}


				foreach ($additional as $a) {
					$shipping_detail[$a] = $order->$a;
				}

				if (!empty($affiliate_name)) {
					$shipping_detail['affiliate'] = $affiliate_name;
				}

				$order_rows[] = $shipping_detail;
			}

			if (count($paypal_details) > 0) {

				array_walk($paypal_details, function($p_a, $index) use ($order, &$order_rows, $additional, $affiliate_name) {
					$paypal_details[$index] = $p_a = $p_a->as_array();

					foreach ($additional as $a) {
						$p_a[$a] = $order->$a;
					}

					if (!empty($affiliate_name)) {
						$p_a['affiliate'] = $affiliate_name;
					}

					$order_rows[] = $p_a;
				});

			}

			foreach ($this->_mismatch_fields as $m_a) {
				$compare_array = array();

				foreach ($order_rows as $o_r) {
					$compare_array[] = $o_r[$m_a];
				}

				if (count(array_unique($compare_array)) != 1) {
					//we have a mismatch

					array_walk($order_rows, function($row, $index) use (&$order_rows, $m_a) {
						$order_rows[$index][$m_a] .= '_MISMATCH';
					});
				}
			}

			if (!empty($search)) {
				$matching = false;
				foreach ($order_rows as $o_r) {
					foreach ($o_r as $k => $v) {
						if (is_array($v)) {
							foreach ($v as $value) {
								if (is_array($value)) {
									foreach ($value as $val) {
										if (preg_match('/' . $search . '/i', $val)) {
											$matching = true;
											break;
										}
									}
								} else {
									if (preg_match('/' . $search . '/i', $value)) {
										$matching = true;
										break;
									}
								}
							}
						} else {
							if (preg_match('/' . $search . '/i', $v)) {
								$matching = true;
								break;
							}
						}
					}
				}

				if ($matching) {
					$orders_view = array_merge($orders_view, $order_rows);

				}
			} else {
				$orders_view = array_merge($orders_view, $order_rows);
				//$order_count++;
			}

		}

		$order_count = count($orders_view);
		$pages = ceil($order_count/$per_page);

		$orders_view = array_slice($orders_view, ($page-1) * $per_page, $per_page);

		$pending_orders = ORM::factory('Order')
			->and_where_open()
				->where('fullfillment_id', 'IS', NULL)
				->or_where('fullfillment_id', '=', '')
			->and_where_close()
			->and_where('internal_status', '<>', 'cancelled')
			->and_where('internal_status', '<>', 'refunded')
			->find_all();



		$ids = array();
		foreach ($pending_orders as $p_o) {

		//var_dump($p_o);
			$ids[] = $p_o->id;
		}
		//var_dump('(' .implode(',', $ids) . ')');exit;
		$products_to_orders = ORM::factory('ProductsToOrder')->where('order_id', 'IN', DB::expr('(' .implode(',', $ids) . ')'))->find_all();

		$products = ORM::factory('Product')->find_all();

		$product_quantities = array();
		foreach ($products_to_orders as $p_t_o) {
			//if (in_array($p_t_o->product_id, array(19,18))) {
			//	var_dump($p_t_o->order_id);
			//}

			$product_quantities[$p_t_o->product_id] = empty($product_quantities[$p_t_o->product_id]) ? 1 : $product_quantities[$p_t_o->product_id] + 1;
		}

		$products_return = array();
		array_walk($product_quantities, function($value, $index) use ($product_quantities, $products, &$products_return) {
			foreach ($products as $p) {
				if ($p->id == $index) {
					$products_return[$p->id] = $value;
				}
			}
		});
//	echo '<pre>';
//var_dump($product_quantities, $products_return);exit;
		$skus_return = array();
		foreach ($products_return as $id => $q) {
			$skus = ORM::factory('SkusToProduct')->where('product_id', '=', $id)->find_all();

			foreach ($skus as $s) {
				$skus_return[$s->sku] = empty($skus_return[$s->sku]) ? ($q * $s->quantity) : $skus_return[$s->sku] + ($q * $s->quantity);
			}
		}

		$this->page_view->body = View::Factory('order')
			->set('order_rows', $orders_view)
			->set('countries', $geo_countries)
			->set('products', $products)
			->set('product_quantities', $skus_return)
			->set('page', $page)
			->set('pages', $pages)
			->set('filter', $filter)
                        ->set('paypal_account', $paypal_account)
			->set('date_start', $date_start)
			->set('date_end', $date_end)
			->set('search', $search)
			->set('affiliate', $affiliate)
			->set('affiliates', ORM::factory('Affiliate')->where('status', '<>', 'deleted')->find_all())
			->render();
		$this->response->body($this->page_view);
	}

	public function action_export() {
		$ids = explode('-', $this->request->param('id'));

		$file_name = APPPATH . '../csv/' . md5(time()) . '.csv';

		$out = fopen($file_name, 'w');

		$csv = array();

		$row = array(
			'orderNum',
			'orderRef',
			'orderShipMethod',
			'orderComments',
			'custEmail',
			'custPhone',
			'custFName',
			'custLName',
			'custCompany',
			'custAddress1',
			'custAddress2',
			'custCity',
			'custState',
			'custZip',
			'custCountry',
			'custBillFName',
			'custBillLName',
			'custBillCompany',
			'custBillAddress1',
			'custBillAddress2',
			'custBillCity',
			'custBillState',
			'custBillZip',
			'custBillCountry',
			'items',
		);

		for ($i = 1; $i <= 20; $i++) {
			$row[] = 'SKU' . $i;
			$row[] = 'QTY' . $i;
		}

		fputcsv($out, $row);

		foreach ($ids as $id) {
			$order = ORM::factory('Order', $id);

			if (!$order->loaded())
				continue;

			$shipping_detail = ORM::factory('ShippingDetail')->where('order_id', '=', $id)->find();

			if (!$shipping_detail->loaded()) {
				$shipping_detail = ORM::factory('PaypalDetail')->where('order_id', '=', $id)->find()->as_array();
			} else {
				$shipping_detail = $shipping_detail->as_array();
			}

			$skus = $order->_get_skus();
			$order = $order->as_array();

			$names = explode(' ', $shipping_detail['name']);

			if (count($names) == 1) {
				$fname = $names[0];
				$lname = '';
			} else {
				$fname = $names[0];
				$lname = $names[1];
			}

			$row = array(
				$order['public_id'],
				$order['public_id'],
				$order['shipping_method'],
				str_replace("\n", " ", $order['comments']),
				$shipping_detail['email'],
				$shipping_detail['phone'],
				$fname,
				$lname,
				'',
				$shipping_detail['address1'],
				!empty($shipping_detail['address2']) ? $shipping_detail['address2'] : '',
				$shipping_detail['city'],
				$shipping_detail['state'],
				$shipping_detail['zip'],
				$shipping_detail['country'],
				'',
				'',
				'',
				'',
				'',
				'',
				'',
				'',
				'',
				count($skus),
			);

/*
			foreach ($skus as $sku) {
				$row[] = $sku['sku'];
				$row[] = $sku['quantity'];
			}
*/
			for ($i = 1; $i <= 20; $i++) {
				$row[] = !empty($skus[$i-1]['sku']) ? $skus[$i-1]['sku'] : '';
				$row[] = !empty($skus[$i-1]['quantity']) ? $skus[$i-1]['quantity'] : '';
			}
			//$csv[] = $row;

			fputcsv($out, $row);
			//orderNum,orderRef,orderShipMethod,orderComments,custEmail,custPhone,custFName,custLName,custCompany,custAddress1,custAddress2,custCity,custState,custZip,custCountry,custBillFName,custBillLName,custBillCompany,custBillAddress1,custBillAddress2,custBillCity,custBillState,custBillZip,custBillCountry,items,SKU1,QTY1,SKU2,QTY2,SKU3,QTY3,SKU4,QTY4,SKU5,QTY5,SKU6,QTY6,SKU7,QTY7,SKU8,QTY8,SKU9,QTY9,SKU10,QTY10,SKU11,QTY11,SKU12,QTY12,SKU13,QTY13,SKU14,QTY14,SKU15,QTY15,SKU16,QTY16,SKU17,QTY17,SKU18,QTY18,SKU19,QTY19,SKU20,QTY20


		}

		$this->response->send_file($file_name);

	}

	public function action_fix_address() {
		$this->response->headers('Content-Type', 'application/json; charset=utf-8');

		$id    = $this->request->post('id');
		$type  = $this->request->post('type');
		$fields = explode(', ', $this->request->post('fields'));

		$record = $type == 'paypal' ? ORM::factory('PaypalDetail', $id) : ORM::factory('ShippingDetail', $id);

		if ($record->loaded()) {

			if ($type == 'paypal') {
				$shipping_detail = ORM::factory('ShippingDetail')->where('order_id', '=', $record->order_id)->find();

				if ($shipping_detail->loaded()) {

/*
					foreach ($this->_mismatch_fields as $field) {
						$shipping_detail->$field = $record->$field;
					}
*/
					foreach ($fields as $field) {
						if ($field == 'address1') {
							$shipping_detail->address2 = $record->address2;
						}

						$shipping_detail->$field = $record->$field;
					}
					$shipping_detail->save();
				}

				$paypal_details = ORM::factory('PaypalDetail')->where('id', '<>', $id)->and_where('order_id', '=', $record->order_id)->find_all();

				foreach ($paypal_details as $p_a) {
/*
					foreach ($this->_mismatch_fields as $field) {
						$p_a->$field = $record->$field;
					}
*/

/*
					if ($field == 'address1') {
						$p_a->address2 = $record->address2;
					}
*/

					foreach ($fields as $field) {
						$p_a->$field = $record->$field;
					}
					$p_a->save();
				}

			} else {
				$paypal_details = ORM::factory('PaypalDetail')->where('order_id', '=', $record->order_id)->find_all();

				foreach ($paypal_details as $p_a) {
/*
					foreach ($this->_mismatch_fields as $field) {
						$p_a->$field = $record->$field;
					}
*/
					foreach ($fields as $field) {
						$p_a->$field = $record->$field;
					}
					$p_a->save();
				}
			}
			$this->response->body(json_encode(array(
				'status'  => 'success',
			)));
			return;
		} else {
			$this->response->body(json_encode(array(
				'status'  => 'error',
				'message' => 'An error occurred, please try again!'
			)));
			return;
		}
	}

	public function action_delete() {
		$this->response->headers('Content-Type', 'application/json; charset=utf-8');


		$shipping_details = ORM::factory('ShippingDetail', $this->request->post('id'));

		if ($this->request->post('type') == 'manual') {
			$order = ORM::factory('Order', $shipping_details->order_id);

			if (!$order->loaded()) {
				$this->response->body(json_encode(array(
					'status'  => 'error',
					'message' => 'Order does not exist!'
				)));
				return;
			}

			DB::delete('products_to_orders')->where('order_id', '=', $order->id)->execute();

			$order->delete();
		}

		$shipping_details->delete();


		$this->response->body(json_encode(array(
			'status'  => 'success',
		)));
		return;
	}

	public function action_send_for_fullfillment() {
		$this->response->headers('Content-Type', 'application/json; charset=utf-8');

		$ids = $this->request->post('id') ? array($this->request->post('id')) : explode(',', $this->request->post('ids'));

		$orders = array();
//		$order_error = array();
		foreach ($ids as $order_id) {
			$aus_fulfilment = false;
			$shipping_detail = ORM::factory('ShippingDetail')->where('order_id', '=', $order_id)->find();

			$order = ORM::factory('Order', $order_id);

			if (!$order->loaded())
				continue;

			$order->date_sent = DB::expr('NOW()');
			$order->save();


			if (!$shipping_detail->loaded()) {
				$order_ff = ORM::factory('PaypalDetail')->where('order_id', '=', $order_id)->find()->as_array();
			} else {
				$order_ff = $shipping_detail->as_array();
			}

			/*if($order_id == 25439) {
				$aus_fulfilment = true;
			}*/

			$products_to_orders = ORM::factory('ProductsToOrder')
				->where('order_id', '=', $order_id)
				->find_all();

			foreach ($products_to_orders as $p_a) {
				$product = ORM::factory('Product', $p_a->product_id);
				if ($product->fulfilment_center == 'AUS') {
					$aus_fulfilment = true;
				}
			}

			if ($aus_fulfilment) {
				Log::instance()->add(Log::INFO, 'SmartTurn Request');

				$quantity_count = 0;
				$items = $order->_get_skus();

				foreach ($items as $item) {
					if ($item['quantity'] > 0) {
						$quantity_count = $quantity_count + $item['quantity'];
					}
				}

				$body = '<?xml version="1.0" encoding="UTF-8"?>
						<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
						   <soapenv:Body>
						      <saveSalesOrder xmlns="http://www.smartturn.com/services/OccamService/sales-order">
						         <inCredential>
						            <ns1:UserId xmlns:ns1="http://www.smartturn.com/services/occamtypes">infotech@holisticlabs.com</ns1:UserId>
						            <ns2:Password xmlns:ns2="http://www.smartturn.com/services/occamtypes">hHL.123</ns2:Password>
						         </inCredential>
								 <inSalesOrders>
						            <ns3:externalNumber xmlns:ns3="http://www.smartturn.com/services/sales-order-types">HHL-' . $order->public_id . '</ns3:externalNumber>
						            <ns4:type xmlns:ns4="http://www.smartturn.com/services/sales-order-types">EXTERNAL</ns4:type>
									<ns5:date xmlns:ns5="http://www.smartturn.com/services/sales-order-types">' . date('Y-m-d') . 'T00:04:34.479Z</ns5:date>
						            <ns6:dateDue xmlns:ns6="http://www.smartturn.com/services/sales-order-types">' . date('Y-m-d') . 'T00:04:34.479Z</ns6:dateDue>
									<ns7:customerName xmlns:ns7="http://www.smartturn.com/services/sales-order-types"></ns7:customerName>
						            <ns8:customerContact xmlns:ns8="http://www.smartturn.com/services/sales-order-types">' . htmlspecialchars($order_ff['name']) . '</ns8:customerContact>
						            <ns9:customerContactPhone xmlns:ns9="http://www.smartturn.com/services/sales-order-types">' . $order_ff['email'] . '</ns9:customerContactPhone>
						            <ns10:customerAddress xmlns:ns10="http://www.smartturn.com/services/sales-order-types">
						               <ns11:addressLine1 xmlns:ns11="http://www.smartturn.com/services/occamtypes">' . htmlspecialchars($order_ff['address1']) . '</ns11:addressLine1>
						               <ns12:addressLine2 xmlns:ns12="http://www.smartturn.com/services/occamtypes">' . htmlspecialchars($order_ff['address2']) . '</ns12:addressLine2>
						               <ns13:city xmlns:ns13="http://www.smartturn.com/services/occamtypes">' . $order_ff['city'] . '</ns13:city>
						               <ns14:state xmlns:ns14="http://www.smartturn.com/services/occamtypes">' . strtoupper($order_ff['state']) . '</ns14:state>
						               <ns15:country xmlns:ns15="http://www.smartturn.com/services/occamtypes">' . $order_ff['country'] . '</ns15:country>
						               <ns16:postalCode xmlns:ns16="http://www.smartturn.com/services/occamtypes">' . $order_ff['zip'] . '</ns16:postalCode>
						            </ns10:customerAddress>
									<ns18:useShipToAsBillAddress xmlns:ns18="http://www.smartturn.com/services/sales-order-types">false</ns18:useShipToAsBillAddress>
						            <ns19:shipToName xmlns:ns19="http://www.smartturn.com/services/sales-order-types"></ns19:shipToName>
						            <ns20:shipToContact xmlns:ns20="http://www.smartturn.com/services/sales-order-types">' . $order_ff['name'] . '</ns20:shipToContact>
						            <ns21:shipToContactPhone xmlns:ns21="http://www.smartturn.com/services/sales-order-types">' . $order_ff['email'] . '</ns21:shipToContactPhone>
						            <ns22:ShipTo xmlns:ns22="http://www.smartturn.com/services/sales-order-types">
						               <ns23:addressLine1 xmlns:ns23="http://www.smartturn.com/services/occamtypes">' . $order_ff['address1'] . '</ns23:addressLine1>
						               <ns24:addressLine2 xmlns:ns24="http://www.smartturn.com/services/occamtypes">' . $order_ff['address2'] . '</ns24:addressLine2>
						               <ns25:city xmlns:ns25="http://www.smartturn.com/services/occamtypes">' . $order_ff['city'] . '</ns25:city>
						               <ns26:state xmlns:ns26="http://www.smartturn.com/services/occamtypes">' . strtoupper($order_ff['state']) . '</ns26:state>
						               <ns27:country xmlns:ns27="http://www.smartturn.com/services/occamtypes">' . $order_ff['country'] . '</ns27:country>
						               <ns28:postalCode xmlns:ns28="http://www.smartturn.com/services/occamtypes">' . $order_ff['zip'] . '</ns28:postalCode>
						            </ns22:ShipTo>
						            <ns23:status xmlns:ns23="http://www.smartturn.com/services/sales-order-types">SAVED</ns23:status>
						            <ns24:item xmlns:ns24="http://www.smartturn.com/services/sales-order-types">
						               <ns24:itemMasterId>HHL-ERG</ns24:itemMasterId>
						               <ns24:description>Energizer Greens</ns24:description>
						               <ns24:orderedQuantity>
						                  <ns26:value xmlns:ns26="http://www.smartturn.com/services/occamtypes">' . $quantity_count . '</ns26:value>
						                  <ns27:unitAbbreviation xmlns:ns27="http://www.smartturn.com/services/occamtypes">ea</ns27:unitAbbreviation>
						               </ns24:orderedQuantity>
						               <ns24:customerRequestDate>' . date('Y-m-d') . 'T00:04:34.479Z</ns24:customerRequestDate>
						            </ns24:item>
						            <ns31:comments xmlns:ns31="http://www.smartturn.com/services/sales-order-types">' . $order->shipping_method . '</ns31:comments>
						            <ns32:priority xmlns:ns32="http://www.smartturn.com/services/sales-order-types">R</ns32:priority>
								    <ns33:ownerName xmlns:ns33="http://www.smartturn.com/services/sales-order-types">Holistic Labs Limited</ns33:ownerName>
								    <ns34:ownerCode xmlns:ns34="http://www.smartturn.com/services/sales-order-types">HHL</ns34:ownerCode>
						            </inSalesOrders>
						         </saveSalesOrder>
						     </soapenv:Body>
						</soapenv:Envelope>';

				/*$headers = array(
				    "Content-type: text/xml;charset=\"utf-8\"",
				    "Accept: text/xml",
				    "Cache-Control: no-cache",
				    "Pragma: no-cache",
				    "Content-length: ".strlen($body)
				);

				$request->client()->options(array(
				    CURLOPT_POST => true,
				    CURLOPT_POSTFIELDS => $body,
				    CURLOPT_SSL_VERIFYPEER => false,
				    CURLOPT_HEADER => $headers,
				    CURLOPT_HTTPHEADER => $headers
				));

				$response = $request->execute();

				Log::instance()->add(Log::INFO, 'SmartTurn response ' . serialize($response));*/
				$request = Request::factory('https://services.smartturn.com/occam/services/OccamService?wsdl')
					->method(Request::POST)
					->body($body)
					->headers('Content-Type', 'text/xml')
					->headers('SOAPAction', 'saveSalesOrder')
					->execute();
				Log::instance()->add(Log::INFO, 'SmartTurn response ' . serialize($request));
//				$your_xml_response = $request->body();
//				$doc = new DOMDocument();
//				$doc->loadXML($your_xml_response);
//				if ($doc->getElementsByTagName('status')->item(0)->nodeValue == 'FAILURE') {
//					$order_error['exist'][] = $order->public_id;
//				} else {
//					$order_error['new'][] = $order->public_id;
//				}

				$order->internal_status = 'processed';
				$order->save();

			} else {
				$order_ff['shipping_method'] = $order->shipping_method;
				$order_ff['public_id'] = $order->public_id;
				$order_ff['comments'] = $order->comments;

				$order_ff['items'] = $order->_get_skus();
				$orders[] = $order_ff;

			}
		}
//		var_dump($order_error);exit;
		//UPDATE DATE_SENT
		if (empty($orders) && !$aus_fulfilment) {
			$this->response->body(json_encode(array(
				'status'  => 'error',
				'message' => 'Nothing to send!',
			)));
			return;
		} elseif (!empty($orders)) {

			//http://xcp.xpertfulfillment.com/xml/XmlOrdersTesting.php

			//$request = Request::factory('http://xcp.xpertfulfillment.com/xml/XmlOrdersTesting.php')

//			foreach ($orders as $key=>$order) {
//				$order_check = ORM::factory('Order')->_scrape_fullfillment(array(), $order['public_id']);
//				if ($order_check == $order['public_id']) {
//					$order_error['exist'][] = $order['public_id'];
//					unset($orders[$key]);
//				} else {
//					$order_error['new'][] = $order['public_id'];
//				}
//			}
			$xml = View::factory('fullfillment_xml')
				->set('orders', $orders)
				->render();
			$request = Request::factory('http://xcp.xpertfulfillment.com/xml/XmlOrders.php')
				->method(Request::POST)
				->body($xml)
				->headers('Content-Type', 'text/xml')
				->execute();

			Log::instance()->add(Log::INFO, 'XpertFulfillment response ' . serialize($request));

		}
		ORM::factory('Order')->_scrape_fullfillment(array('PENDING', 'INPROCESS', 'BACKORDERED'));
		ORM::factory('Order')->_scrape_fullfillment_aus();
		$this->response->body(json_encode(array(
			'status'  => 'success',
			'message' => 'Orders sent!',
//			'orders' => $order_error,
		)));

		return;
	}

	public function action_modal_fullfillment() {

		$ids = $this->request->post('id') ? array($this->request->post('id')) : explode(',', $this->request->post('ids'));

		$orders = array();
		foreach ($ids as $order_id) {
			$order = ORM::factory('Order', $order_id);

			if (!$order->loaded())
				continue;

			$address_detail = ORM::factory('ShippingDetail')
				->where('order_id', '=', $order->id)
				->find();

			if (!$address_detail->loaded()) {
				$address_detail = ORM::factory('PaypalDetail')
					->where('order_id', '=', $order->id)
					->find();

				if (!$address_detail->loaded())
					continue; //no address at all?
			}

			$order_return = array(
				'public_id'       => $order->public_id,
				'shipping_method' => $order->shipping_method,
				'skus'            => $order->_get_skus(),
			);

			foreach ($this->_mismatch_fields as $f) {
				$order_return[$f] = $address_detail->$f;
			}

			$orders[] = $order_return;
		}

		$modal = View::factory('modal_fullfillment')
			->set('orders', $orders)
			->render();

		$this->response->body($modal);

	}

	public function action_modal_details() {
		$order = ORM::factory('Order', $this->request->param('id'));

		if ($order->loaded()) {
			$order = $order->as_array();

			$paypal_details = ORM::factory('PaypalDetail')
				->where('order_id', '=', $this->request->param('id'))
				->find_all()->as_array();

			$order['total'] = 0;

			array_walk($paypal_details, function($p_a, $index) use (&$paypal_details, &$order) {
				$paypal_details[$index] = $p_a->as_array();

				$order['total'] += $p_a->gross;
			});

			$products_to_orders = ORM::factory('ProductsToOrder')
				->where('order_id', '=', $this->request->param('id'))
				->find_all();

			$items = array();
			foreach ($products_to_orders as $p_a) {
				$product = ORM::factory('Product', $p_a->product_id);
				$items[] = $product->name;
			}

			$order['items'] = implode(', ', $items);

			$shipping_details = ORM::factory('ShippingDetail')
				->where('order_id', '=', $this->request->param('id'))
				->find()->as_array();

			$modal = View::factory('modal_details')
				->set('paypal_details', $paypal_details)
				->set('shipping', $shipping_details)
				->set('order', $order)
				->render();

			$this->response->body($modal);
		}
	}

	public function action_modal_history() {
		$record = ORM::factory('ShippingDetail')
			->where('order_id', '=', $this->request->param('id'))
			->find();

		if (!$record->loaded()) {

			//let's use PayPal address
			$record = ORM::factory('PaypalDetail')
				->where('order_id', '=', $this->request->param('id'))
				->find();
		}

		$customer = array(
			'name'    => $record->name,
			'address' => $record->address1,
			'email'   => $record->email,
			'city'    => $record->city,
			'state'   => $record->state,
			'country' => $record->country,
			'zip'     => $record->zip,
			'orders'  => array(),
		);

		$shipping_details = ORM::factory('ShippingDetail')
			->where('email', '=', $record->email)
			->find_all();


		foreach ($shipping_details as $s_a) {
			$order = ORM::factory('Order', $s_a->order_id);

			if (!$order->loaded())
				continue;

			$order = $order->as_array();

			$paypal_details = ORM::factory('PaypalDetail')
				->where('order_id', '=', $s_a->order_id)
				->find_all()->as_array();

			$order['total'] = 0;

			if (count($paypal_details)) {
				$order['paypal_status'] = $paypal_details[0]->status;


				array_walk($paypal_details, function($p_a, $index) use (&$paypal_details, &$order) {
					$paypal_details[$index] = $p_a->as_array();

					$order['total'] += $p_a->gross;
				});
			} else {
				$order['paypal_status'] = 'N/A';
			}

			$customer['orders'][] = $order;
		}

		//let's find orders that don't have shipping address
		$orders = ORM::factory('Order')->find_all();

		foreach ($orders as $order) {

			if (ORM::factory('ShippingDetail')->where('order_id', '=', $order->id)->find()->loaded())
				continue;

			$paypal_details = ORM::factory('PaypalDetail')
				->where('order_id', '=', $order->id)
				->and_where('email', '=', $record->email)
				->find_all()->as_array();

			if (!count($paypal_details))
				continue; //not this customer order

			$order = $order->as_array();
			$order['total'] = 0;
			$order['paypal_status'] = $paypal_details[0]->status;


			array_walk($paypal_details, function($p_a, $index) use (&$paypal_details, &$order) {
				$paypal_details[$index] = $p_a->as_array();

				$order['total'] += $p_a->gross;
			});

			$customer['orders'][] = $order;
		}

		$modal = View::factory('modal_history')
			->set('customer', $customer)
			->render();


		$this->response->body($modal);
	}

	public function action_get_orders_for_merge() {
		$this->response->headers('Content-Type', 'application/json; charset=utf-8');

		$orders = ORM::factory('Order')->where('id', '<>', $this->request->param('id'))->find_all();

		$orders_return = array();
		foreach ($orders as $o) {
			$orders_return[] = array(
				'id'        => $o->id,
				'public_id' => $o->public_id,
				'date'      => date('m/d/Y', strtotime($o->date_added)),
			);
		}

		$this->response->body(json_encode(array(
			'status'  => 'success',
			'orders'    => $orders_return
		)));
	}

	public function action_merge() {
		$this->response->headers('Content-Type', 'application/json; charset=utf-8');

		$child_order = ORM::factory('Order', $this->request->post('child_order_id'));

		if ($child_order->loaded()) {
			$child_order->_merge_into($this->request->post('parent_order_id'));

			$this->response->body(json_encode(array(
				'status'  => 'success',
			)));
		} else {
			$this->response->body(json_encode(array(
				'status'  => 'error',
			)));
		}
	}

	public function action_get_address() {
		$this->response->headers('Content-Type', 'application/json; charset=utf-8');

		$record = $this->request->post('type') == 'paypal' ? ORM::factory('PaypalDetail', $this->request->post('id')) : ORM::factory('ShippingDetail', $this->request->post('id'));

		if (!$record->loaded()) {
			$this->response->body(json_encode(array(
				'status'  => 'error',
				'message' => 'An error occured, please try again!',
			)));
			return;
		}

		$fields = array('name', 'address1', 'address2', 'city', 'state', 'country', 'zip', 'phone', 'email');

		$address = array(
			'id'   => $this->request->post('id'),
			'type' => $this->request->post('type')
		);

		foreach ($fields as $f) {
			$address[$f] = $record->$f;
		}

		$this->response->body(json_encode(array(
			'status'  => 'success',
			'data'    => $address
		)));

	}

	public function action_save_address() {

		$this->response->headers('Content-Type', 'application/json; charset=utf-8');

		$fields = array('name', 'address1', 'address2', 'city', 'state', 'country', 'zip', 'phone', 'email');

		$record = $this->request->post('type') == 'paypal' ? ORM::factory('PaypalDetail', $this->request->post('id')) : ORM::factory('ShippingDetail', $this->request->post('id'));

		if (!$record->loaded()) {
			$this->response->body(json_encode(array(
				'status'  => 'error',
				'message' => 'An error occured, please try again!'
			)));
			return;
		}

		foreach ($fields as $f) {
			$record->$f = $this->request->post($f);
		}

		$record->save();

		$this->response->body(json_encode(array(
			'status'  => 'success',
			'message' => 'Address updated!'
		)));

	}

	public function action_get_details() {
		$this->response->headers('Content-Type', 'application/json; charset=utf-8');

		$order = ORM::factory('Order', $this->request->param('id'));

		if (!$order->loaded()) {
			$this->response->body(json_encode(array(
				'status'  => 'error',
				'message' => 'An error occured, please try again!',
			)));
			return;
		}

		$fields = array('public_id', 'comments', 'shipping_method', 'internal_status', 'fullfillment_status', 'fullfillment_id', 'tracking_id');

		$order_return = array(
			'id'       => $this->request->param('id'),
			'products' => $order->_get_items(),
		);

		foreach ($fields as $f) {
			$order_return[$f] = $order->$f;
		}

		$this->response->body(json_encode(array(
			'status'  => 'success',
			'data'    => $order_return
		)));

	}

	public function action_save_status() {
		$this->response->headers('Content-Type', 'application/json; charset=utf-8');

		$order = ORM::factory('Order', $this->request->post('id'));

		if (!$order->loaded()) {
			$this->response->body(json_encode(array(
				'status'  => 'error',
				'message' => 'An error occured, please try again!'
			)));
			return;
		}

		$f = 'internal_status';
		$order->$f = $this->request->post('value');

		$order->save();

		$this->response->body(json_encode(array(
			'status'  => 'success',
			'message' => 'Status updated!'
		)));

	}

	public function action_save_campaign() {
		$this->response->headers('Content-Type', 'application/json; charset=utf-8');

		$order = ORM::factory('Order', $this->request->post('id'));

		if (!$order->loaded()) {
			$this->response->body(json_encode(array(
				'status'  => 'error',
				'message' => 'An error occured, please try again!'
			)));
			return;
		}

		$f = $this->request->post('type');
		$order->$f = $this->request->post('value');

		$order->save();

		$this->response->body(json_encode(array(
			'status'  => 'success',
			'message' => 'Campaign updated!'
		)));

	}

	public function action_save_affiliate() {
		$this->response->headers('Content-Type', 'application/json; charset=utf-8');

		$order = ORM::factory('Order', $this->request->post('id'));

		if (!$order->loaded()) {
			$this->response->body(json_encode(array(
				'status'  => 'error',
				'message' => 'An error occured, please try again!'
			)));
			return;
		}

		$f = 'affiliate_id';
		$order->$f = $this->request->post('value');

		$order->save();

		$this->response->body(json_encode(array(
			'status'  => 'success',
			'message' => 'Campaign updated!'
		)));

	}

	public function action_save_shipping() {
		$this->response->headers('Content-Type', 'application/json; charset=utf-8');

		$order = ORM::factory('Order', $this->request->post('id'));

		if (!$order->loaded()) {
			$this->response->body(json_encode(array(
				'status'  => 'error',
				'message' => 'An error occured, please try again!'
			)));
			return;
		}

		$f = 'shipping_method';
		$order->$f = $this->request->post('value');

		$order->save();

		$this->response->body(json_encode(array(
			'status'  => 'success',
			'message' => 'Shipping method updated!'
		)));

	}

	public function action_save_details() {

		$this->response->headers('Content-Type', 'application/json; charset=utf-8');

		$fields = array('public_id', 'comments', 'shipping_method', 'internal_status', 'fullfillment_status', 'fullfillment_id', 'tracking_id');

		$order = ORM::factory('Order', $this->request->post('id'));

		if (!$order->loaded()) {
			$this->response->body(json_encode(array(
				'status'  => 'error',
				'message' => 'An error occured, please try again!'
			)));
			return;
		}

		foreach ($fields as $f) {
			$order->$f = $this->request->post($f);
		}

		$order->save();
		$order->_update_affiliate_status();

		$order->_save_items($this->request->post('items'));
		$order->_calculate_commission();

		$this->response->body(json_encode(array(
			'status'  => 'success',
			'message' => 'Details updated!'
		)));

	}

	public function action_new_order() {
		$this->response->headers('Content-Type', 'application/json; charset=utf-8');

		$shipping_fields = array('name', 'address1', 'city', 'country', 'zip', 'state', 'phone', 'email');

		$order = ORM::factory('Order');
		$order_fields = array('shipping_method');
		foreach ($order_fields as $o_f) {
			$order->$o_f = $this->request->post($o_f);
		}

		if ($this->request->post('public_id')) {
			$order->public_id = $this->request->post('public_id');
		} else {
			$order->public_id = $order->_generate_public_id();
		}

		$order->date_added = DB::expr('NOW()');
		$order->save();

		$order->_save_items($this->request->post('items'));

		$shipping_detail = ORM::factory('ShippingDetail');
		foreach ($shipping_fields as $s_f) {
			$shipping_detail->$s_f = $this->request->post($s_f);
		}
		$shipping_detail->type = 'manual';
		$shipping_detail->date_added = DB::expr('NOW()');
		$shipping_detail->order_id = $order->id;
		$shipping_detail->save();

		$this->response->body(json_encode(array(
			'status'  => 'success',
			'message' => 'Order added!'
		)));
	}

	protected function _handle_upload($uploaded_file) {
		if (!Upload::valid($uploaded_file) || !Upload::not_empty($uploaded_file)) {
			return false;
		}
		$path = 'uploads/';

		$directory = DOCROOT . $path;

		if ($file = Upload::save($uploaded_file, NULL, $directory)) {
			$tmp = explode('/', $file);
			$file_name = array_pop($tmp);
			return $path . $file_name;
		} else {
			return false;
		}
	}

	public function action_report() {

		if (empty($_GET['type']) || empty($_GET['start_date'])) {

			$dates = array();
			$current = new DateTime();

			for ($i = 0; $i < 6; $i++) {
				$dates[$current->format('F Y')] = $current->format('Y-m-01');//first day
				$current->sub(DateInterval::createFromDateString('1 month'));
			}

		} else {
			$reports = array();

			if ($_GET['start_date'] == 'last_6_months') {

				$current = new DateTime('first day of this month');
				//do this for each of the last 6 months
				for ($i = 0; $i < 6; $i++) {
					$start_date = $current;
					$end_date   = new DateTime($current->format('Y-m-t 23:59:59'));



					//let's check if report has been cached
					if (empty($_GET['regenerate'])) {
						$report = ORM::factory('Report')
							->where('start_date', '=', $start_date->format('Y-m-d'))
							->where('end_date', '=', $end_date->format('Y-m-d'))
							->where('type', '=', $_GET['type'])
							->find();

						if ($report->loaded()) {
							$report = json_decode($report->report, true);
						} else {
							unset($report);
						}
					} else {
						//get rid of the old report
						DB::delete('reports')
							->where('start_date', '=', $start_date->format('Y-m-d'))
							->where('end_date', '=', $end_date->format('Y-m-d'))
							->where('type', '=', $_GET['type'])
							->execute();
					}


					if (!isset($report)) {

						if ($_GET['type'] == 'sales') {
							$report = Model_Order::_report(
								$start_date->format('Y-m-d 00:00:00'),
								$end_date->format('Y-m-d 23:59:59')
							);
						} else {
							$affiliates = ORM::factory('Affiliate')
								->where('status', '=', 'active')
								->find_all();

							$report = array();

							foreach ($affiliates as $a) {
								$report[$a->name] = Model_Order::_report(
									$start_date->format('Y-m-d 00:00:00'),
									$end_date->format('Y-m-d 23:59:59'),
									$a->id
								);
							}
						}

						$report_cache = ORM::factory('Report');
						$report_cache->start_date = $start_date->format('Y-m-d');
						$report_cache->end_date   = $end_date->format('Y-m-d');
						$report_cache->type       = $_GET['type'];
						$report_cache->date_added = DB::expr('NOW()');
						$report_cache->report     = json_encode($report);
						$report_cache->save();
					}

					$reports[$start_date->format('F Y')] = $report;

					$current->sub(DateInterval::createFromDateString('1 month'));
				}

			} else {

				//get start & end dates
				$start_date = new DateTime($_GET['start_date']);
				$end_date   = new DateTime($start_date->format('Y-m-t 23:59:59'));

				//let's check if report has been cached
				if (empty($_GET['regenerate'])) {
					$report = ORM::factory('Report')
						->where('start_date', '=', $start_date->format('Y-m-d'))
						->where('end_date', '=', $end_date->format('Y-m-d'))
						->where('type', '=', $_GET['type'])
						->find();

					if ($report->loaded()) {
						$report = json_decode($report->report, true);
					} else {
						unset($report);
					}
				} else {
					//get rid of the old report
					DB::delete('reports')
						->where('start_date', '=', $start_date->format('Y-m-d'))
						->where('end_date', '=', $end_date->format('Y-m-d'))
						->where('type', '=', $_GET['type'])
						->execute();
				}

				if (!isset($report)) {

					if ($_GET['type'] == 'sales') {
						$report = Model_Order::_report(
							$start_date->format('Y-m-d 00:00:00'),
							$end_date->format('Y-m-d 23:59:59')
						);
					} else {
						$affiliates = ORM::factory('Affiliate')
							->where('status', '=', 'active')
							->find_all();

						$report = array();

						foreach ($affiliates as $a) {
							$report[$a->name] = Model_Order::_report(
								$start_date->format('Y-m-d 00:00:00'),
								$end_date->format('Y-m-d 23:59:59'),
								$a->id
							);
						}
					}

					$report_cache = ORM::factory('Report');
					$report_cache->start_date = $start_date->format('Y-m-d');
					$report_cache->end_date   = $end_date->format('Y-m-d');
					$report_cache->type       = $_GET['type'];
					$report_cache->date_added = DB::expr('NOW()');
					$report_cache->report     = json_encode($report);
					$report_cache->save();
				}
				$reports[$start_date->format('F Y')] = $report;
			}

		}

		$this->page_view->body = View::factory('report')
			->set('dates',      isset($dates)        ? $dates : NULL)
			//->set('report',     isset($report)       ? $report : NULL)
			->set('reports',    isset($reports)      ? $reports : NULL)
			->set('type',       isset($_GET['type']) ? $_GET['type'] : NULL)
			->set('start_date', isset($start_date)   ? $start_date : NULL)
			->set('end_date',   isset($end_date)     ? $end_date : NULL)
			->render();
		$this->response->body($this->page_view);
	}
}