<?php

class Model_Order extends ORM {
	
	protected $_primary_key = 'id';
	
	protected $_has_many = array(
		'PaypalDetails'     => array('model' => 'PaypalDetail'), 
		
	);
	
	protected $_has_one = array(
		'ShippingDetail'   => array(),
	);
	
	public function _generate_public_id() {
		$result = DB::query(Database::SELECT, 'SELECT max(public_id) as last_id FROM orders')->execute();
		
		if ($result->count() == 0) {
			$last_id = 1000;
		} else {
			$result = $result->as_array();
			$last_id = intval($result[0]['last_id']);
		}
		
		return ++$last_id;
	}
	
	public function _merge_into($parent_order_id) {
		$result = DB::update('products_to_orders')->set(array(
			'order_id' => $parent_order_id
		))->where('order_id', '=', $this->id)->execute();
		
		$result = DB::update('shipping_details')->set(array(
			'order_id' => $parent_order_id
		))->where('order_id', '=', $this->id)->execute();
		
		$result = DB::update('paypal_details')->set(array(
			'order_id' => $parent_order_id
		))->where('order_id', '=', $this->id)->execute();
		
		$this->delete();
	}
	
	public function _get_skus() {
		$products_to_orders = ORM::factory('ProductsToOrder')->where('order_id', '=', $this->id)->find_all();
		
		$items = array();
		foreach ($products_to_orders as $p_t_o) {
			$skus = ORM::factory('SkusToProduct')->where('product_id', '=', $p_t_o->product_id)->find_all();
			
			foreach ($skus as $s) {
				$items[] = array(
					'sku'      => $s->sku,
					'quantity' => $s->quantity
				);
			}
		}
		
		return $items;
	}
	
	public function _get_items() {
		$products_to_orders = ORM::factory('ProductsToOrder')->where('order_id', '=', $this->id)->find_all();
		
		$items = array();
		foreach ($products_to_orders as $p_t_o) {
			$product = ORM::factory('Product', $p_t_o->product_id);
			$items[] = $product->name;
			
		}
		return $items;
	}

	public function _get_total() {
		$paypal_details = ORM::factory('PaypalDetail')
			->where('order_id', '=', $this->id)
			->find_all();
		
		$total = 0;
		
		foreach ($paypal_details as $p_d) {
			$total += $p_d->gross - $p_d->shipping_cost;
		}

		return $total;	
	}

	public function _calculate_commission() {

		//let's try to find if there should be affiliate for this order
		if (!$this->_assign_affiliate()) {
			return;
		}
		
		$affiliate = ORM::factory('Affiliate', $this->affiliate_id);
		
		$paypal_details = ORM::factory('PaypalDetail')
			->where('order_id', '=', $this->id)
			->find_all();
			
		$commission_total = 0;
		
		foreach ($paypal_details as $p_d) {
			$commission_percentage = $affiliate->_get_commission($p_d->product_id, $p_d->country);

			$commission_total += ($p_d->gross - $p_d->shipping_cost) * ($commission_percentage/100);
		}
		
		$this->commission = number_format($commission_total, 2);
				
		$this->save();
		
		return $this->commission;
	}
	
	public function _update_affiliate_status() {
		if ($this->internal_status == 'cancelled' || $this->internal_status == 'refunded') {
			if ($this->affiliate_status == 'paid') {
				//sales has already been paid
				$this->affiliate_status = 'awaiting_refund';
			} else {
				//sales has not been paid yet, so no need to refund commission
				$this->affiliate_status = 'refunded';
			}
			
			
		} elseif ($this->affiliate_status == 'awaiting_refund') {
			$this->affiliate_status = 'paid';
			
		} elseif ($this->affiliate_status == 'refunded') {
			$this->affiliate_status = 'awaiting_payment';
			
		}
		
		$this->save();
	}

	/*
		Finds click by affiliate_id and makes it converted
		Tries to find affiliate by ip  & user agent hash
	*/
	public function _assign_affiliate() {
		//if commission is not NULL, it means that we have already updated click and affiliate_id is assigned
		if ($this->commission) {
			return true;
		}
	
		
	
		//we already have affiliate_id, now let's update the click record
		if (!empty($this->affiliate_id)) {
			$click = ORM::factory('Click')
				->where(    'affiliate_id', '=',   $this->affiliate_id)
				->and_where('date_added',   '>', DB::expr('DATE_SUB(NOW(), INTERVAL 24 HOUR)'))
				->order_by( 'date_added',      'desc')
				->find();
				
				
		} else {

			//try to find by IP & user_agent_hash combination
			$click = ORM::factory('Click')
				->where(    'ip',              '=',   $this->ip)
				->and_where('user_agent_hash', '=',   $this->user_agent_hash)
				->and_where('date_added',   '>', DB::expr('DATE_SUB(NOW(), INTERVAL 30 DAY)'))
				->order_by( 'date_added',      'desc')
				->find();
				
			$click->loaded() && Log::instance()->add(Log::INFO, 'Affiliate assigned by IP & user_agent_hash = ' . $this->ip .', ' . $this->user_agent_hash . ', affiliate_id = ' . $click->affiliate_id . ', click_id = ' . $click->id . ', order_id = ' . $this->id);	
		}
		
		if ($click->loaded()) {
			$this->affiliate_id     = $click->affiliate_id;
			$this->campaign         = $click->campaign;
			$this->subcampaign      = $click->subcampaign;
			$this->affiliate_status = 'awaiting_payment';
			$this->save();
			
			$click->order_id = $this->id;
			$click->save();

			return true;
		} else {
			//DIRECT SALE
			return false;
		}

	}

	public function _save_items($item_string) {
		DB::delete('products_to_orders')
			->where('order_id', '=', $this->id)
			->execute();
	
		$items = explode(',', $item_string);
		foreach ($items as $i) {
			$product = ORM::factory('Product')
				->where('name', '=', $i)
				->find();
			
			if (!$product->loaded())
				continue;
				
			$products_to_order             = ORM::factory('ProductsToOrder');
			$products_to_order->product_id = $product->id;
			$products_to_order->order_id   = $this->id;
			$products_to_order->date_added = DB::expr('NOW()');
			$products_to_order->save();
		}
	}
	
	public function _scrape_fullfillment($statuses_limit = array()) {
		
		//http://xcp.xpertfulfillment.com/user.php, usernameview, passwordview
		
		require_once(Kohana::find_file('vendor', 'curl'));
		
		$curl = new Curl;
		$curl->set_user_agent('Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.13 (KHTML, like Gecko) Chrome/24.0.1284.0 Safari/537.13');
		$response = $curl->post('http://xcp.xpertfulfillment.com/user.php', array(
			'usernameview' => 'george',
			'passwordview' => 'tee2498',
		));
		
		if (preg_match('/Location: user\.php/', $response)) {
			
			$base     = 'http://xcp.xpertfulfillment.com/showorders2.php?client=Holistic%20Labs%20Ltd&status=';
			
			if (!empty($statuses_limit)) {
				$statuses = $statuses_limit;
			} else {
				$statuses = array('SHIPPED', 'PENDING', 'INPROCESS', 'BACKORDERED', 'CANCELLED',  'RETURNED');
			}
			
			foreach ($statuses as $s) {
			
				//scrape 3 pages
				for ($page = 0; $page <= 2; $page++) {
					$response = $curl->get($base . $s . '&pag=' . $page);
	
					$dom = new DOMDocument();
					@$dom->loadHtml($response);
					
					$xpath = new DOMXPath($dom);
					$tr_nodes = $xpath->query('//tr[@class="menu"]/../tr[@bgcolor=""]');
					
					if ($tr_nodes->length) {
						foreach ($tr_nodes as $tr) {
							$td_nodes = $tr->getElementsByTagName('td');
							
							$scraped = array(
								'fullfillment_id' => $td_nodes->item(0)->firstChild->nodeValue,
								'public_id'       => $td_nodes->item(1)->firstChild->nodeValue,	
							);
	
							if ($s == 'SHIPPED') {
							
								$a_nodes = $td_nodes->item(2)->getElementsByTagName('a');
								if ($a_nodes->length) {
									$scraped['tracking_id'] = $a_nodes->item(0)->nodeValue;
								} else {
									$scraped['tracking_id'] = 'N/A';
								}
								
							}
												
							$this
								->clear()
								->where('public_id', '=', $scraped['public_id'])
								->find();
								
							if (!$this->loaded()) {
								Log::instance()->add(Log::WARNING, 'Unknown product at XpertFulfillment, # ' . $scraped['public_id']);
								continue;
							}
									
							$this->tracking_id         = empty($scraped['tracking_id']) ? DB::expr('NULL') : $scraped['tracking_id'];
							$this->fullfillment_id     = $scraped['fullfillment_id'];
							$this->fullfillment_status = $s;
							$this->internal_status     = 'processed';
							$this->save();
							
							Log::instance()->add(Log::INFO, 'XpertFulfillment order update # ' .$scraped['public_id'] . ', status: ' . $s);
						}
					}
				}
			}
			
		} else {
			Log::instance()->add(Log::EMERGENCY, 'Failed to login to XpertFulfillment');
		}
	}
	
	public static function for_clicks_stats($date_start, $date_end, $product, $campaign, $affiliate_id) {
		$orders_query = ORM::factory('Order')
			->where('affiliate_id', '=', $affiliate_id);
			
		if (!empty($date_start)) {
			$orders_query->where('date_added', '>=', date('Y-m-d 00:00:00', strtotime($date_start)));
		}
		
		if (!empty($date_end)) {
			$orders_query->where('date_added', '<=', date('Y-m-d 23:59:59', strtotime($date_end)));
		}			
			
		$orders = $orders_query->find_all();
		
		$return_orders = array();
		
		foreach ($orders as $o) {
			$date = date('m/d/Y', strtotime($o->date_added));
			
			$return_orders[$date] = !empty($return_orders[$date]) ? $return_orders[$date] : array();
			
			if ($o->internal_status != 'cancelled' && $o->internal_status != 'refunded') {
			
			
				if (!empty($product) || !empty($campaign)) {
					$click = ORM::factory('Click')
						->where('order_id', '=', $o->id)
						->find();
					
					if ($click) {
						if (!empty($product) && $click->product != $product) {
							continue;
						}
						
						if (!empty($campaign) && $click->campaign != $campaign) {
							continue;
						}
					}
					
				}
			
				$ret = array(
					'customers'  => 1,
					'sales'      => $o->_get_total(),
					'commission' => $o->commission,
				);
				
				$return_orders[$date][] = $ret;
			}
		}
		
		return $return_orders;
	}
	
	public static function _report($start_date, $end_date, $affiliate_id = NULL) {
		$skus = array();
		$totals   = array(
			'sales'   => 0,
			'orders'  => 0,
			'bottles' => 0,
		);
		$orders_query = ORM::factory('order')
			->where('date_added', '>=', $start_date)
			->where('internal_status', '<>', 'cancelled')
			->where('internal_status', '<>', 'refunded')
			->where('date_added', '<=', $end_date);
			
		if (!empty($affiliate_id)) {
			$orders_query->where('affiliate_id', '=', $affiliate_id);
		}
		
		//products -> skus/quantities
		$sku_table = Model_SkusToProduct::_get_table();
		
		$orders = $orders_query->find_all();
		
		foreach ($orders as $o) {
			$totals['sales']  += $o->_get_total();
			$totals['orders'] += 1;
			
			$products_to_orders = ORM::factory('ProductsToOrder')->where('order_id', '=', $o->id)->find_all();
			
			foreach ($products_to_orders as $p) {
				//let's check in our $sku_table what skus and quantities we got here
				if (!array_key_exists($p->product_id, $sku_table)) {
					//weird, we don't even know this product
					continue;
				}
								
				//let's iterate through this product's SKUs
				foreach ($sku_table[$p->product_id] as $sku => $quantity) {
					$totals['bottles'] += $quantity;
					
					if (isset($skus[$sku])) {
						$skus[$sku] += $quantity;
					} else {
						$skus[$sku] = $quantity;
					}
				}
			}
		}
		
		return array(
			'totals' => $totals,
			'skus'   => $skus,
		);
	}
}