<?php

class Model_Click extends ORM {
	
	protected $_primary_key = 'id';
	
	/*
		1) If there's already a click with this affiliate_id (wihtin 24 hours), it will increment raw_clicks counter
		2) If there's no click with this session_id, it will try to match by IP & user_agent_hash combination
		3) If both methods fail, it will create a new click record
	*/
	public function _handle($data) {
			
		if (!empty($data['affiliate_id'])) {
			$this
				->where(    'affiliate_id',    '=', $data['affiliate_id'])
				->and_where('product',         '=', $data['product'])
				->and_where('ip',              '=', $data['ip'])
				->and_where('user_agent_hash', '=', $data['user_agent_hash'])
				->and_where('date_added',      '>', DB::expr('DATE_SUB(NOW(), INTERVAL 24 HOUR)'))
				->find();
		}
		if (!$this->loaded() && empty($data['affiliate_id'])) {
			$this
				->reset()
				->where(    'ip',              '=',   $data['ip'])
				->and_where('user_agent_hash', '=',   $data['user_agent_hash'])
				->and_where('product',         '=', $data['product'])
				->and_where('date_added',      '>', DB::expr('DATE_SUB(NOW(), INTERVAL 24 HOUR)'))
				->order_by( 'date_added',      'desc')
				->find();
		}
		
		if ($this->loaded()) {	
			$this->campaign    = $data['campaign'];
			$this->subcampaign = $data['subcampaign'];
			$this->raw_clicks += 1;
			$this->save();
			return;
		}
		
		
		//nothing found, must be a new one, but only if we have affiliate_id
		if (!empty($data['affiliate_id'])) {
			$fields = array('affiliate_id', 'ip', 'user_agent_hash', 'campaign', 'subcampaign', 'product');
			
			$this->reset();
			
			foreach ($fields as $f) {
				$this->$f = !empty($data[$f]) ? $data[$f] : NULL;
			}
			
			$this->date_added = DB::expr('NOW()');
			$this->date_day   = DB::expr('CURDATE()');
			$this->save();
		}
		
	}


	public function _handle_lead($data) {
		if (!empty($data['affiliate_id'])) {
			$this
				->where(    'affiliate_id',    '=', $data['affiliate_id'])
				->and_where('ip',              '=', $data['ip'])
				->and_where('user_agent_hash', '=', $data['user_agent_hash'])
				->and_where('date_added',      '>', DB::expr('DATE_SUB(NOW(), INTERVAL 24 HOUR)'))
				->find();
			
		}
			
		if (!$this->loaded() && empty($data['affiliate_id'])) {
			$this
				->reset()
				->where(    'ip',              '=',   $data['ip'])
				->and_where('user_agent_hash', '=',   $data['user_agent_hash'])
				->and_where('date_added',      '>', DB::expr('DATE_SUB(NOW(), INTERVAL 24 HOUR)'))
				->order_by( 'date_added',      'desc')
				->find();
		}
		
		if ($this->loaded()) {	
			//$this->leads = empty($this->leads) ? 1 : $this->leads + 1;
			$this->leads = 1;
			$this->save();
		}

	}
	
	public static function _for_stats($params, $regenerate = false) {	
		
		//let's check if's cached
		$report_query = ORM::factory('ClickReport')
			->where('date_day',     '=', $params['date'])
			->and_where('type', '=', 'daily_stats')
			->and_where('affiliate_id', '=', $params['affiliate_id']);
			
		if (!empty($params['product'])) {
			$report_query->and_where('product', '=', $params['product']);
		}
		
		if (!empty($params['campaign'])) {
			$report_query->and_where('campaign', '=', $params['campaign']);
		}
		
		$report = $report_query->find();
		
		if ($report->loaded() && !$regenerate)
		{
			return json_decode($report->report, true);
		} elseif ($report->loaded() && $regenerate) {
			$report->delete();
		}	
		
		$clicks = ORM::factory('Click');
		
		if (!empty($params['affiliate_id'])) {
			$clicks->where('affiliate_id', '=', $params['affiliate_id']);
		}

		if (!empty($params['product'])) {
			$clicks->and_where('product', '=', $params['product']);
		}
		
		if (!empty($params['campaign'])) {
			$clicks->and_where('campaign', '=', $params['campaign']);
		}
		
		$clicks->where('date_day', '=', $params['date']);

		$clicks = $clicks->find_all();
		
		//$now->setTimeZone(new DateTimeZone('UTC'));
		//$now->setTime(0, 0, 0);
		
		//var_dump(date('Y-m-d H:i:sP'), $start_date->format('Y-m-d H:i:sP'), $now->format('Y-m-d H:i:sP'));exit;
							
		$stats = array(
			'unique'     => 0,
			'raw'        => 0,
			'leads'      => 0,
			'customers'  => 0,
			'sales'      => 0,
			'commission' => 0,
		);

		if ($clicks->count() == 0) {
			self::_cache_report($params, 'daily_stats', $stats);
			return $stats;
		}

		$orders_for_period = Model_Order::for_clicks_stats(
			$params['date'] . ' 00:00:00',
			$params['date'] . ' 23:59:59',
			!empty($params['product']) ? $params['product'] : null,
			!empty($params['campaign']) ? $params['campaign'] : null,
			$params['affiliate_id']
		);

		$date_cache = array();

		foreach ($clicks as $c) {
			
			$stats['unique']     += 1;
			$stats['raw']        += $c->raw_clicks;
			$stats['leads']      += !empty($c->leads) ? $c->leads : 0;
		
		}
		
		foreach ($orders_for_period as $date => $orders) {
		
			foreach ($orders as $order) {	
				$stats['customers']  += $order['customers'];
				$stats['sales']      += $order['sales'];
				$stats['commission'] += $order['commission'];
			}	
		}			
		
		self::_cache_report($params, 'daily_stats', $stats);

		return $stats;
	}
	
	public static function _for_product_stats($params, $regenerate = false) {
		//let's check if's cached
		$report_query = ORM::factory('ClickReport')
			->where('date_day',     '=', $params['date'])
			->and_where('type', '=', 'product_breakdown')
			->and_where('affiliate_id', '=', $params['affiliate_id']);
					
		$report = $report_query->find();
		
		if ($report->loaded() && !$regenerate)
		{
			return json_decode($report->report, true);
		} elseif ($report->loaded() && $regenerate) {
			$report->delete();
		}
				
		$click_model = ORM::factory('Click')
			->where('affiliate_id', '=', $params['affiliate_id'])
			->and_where('date_day', '=', $params['date']);
		
		$clicks = $click_model->find_all();

		$stats = array();
		
		foreach ($clicks as $c) {
			
			$product = !empty($c->product) ? $c->product : 'n/a';
			
			$stats[$product] = empty($stats[$product]) ? array(
				'campaigns' => array(),
				'total'     => array(),
			) : $stats[$product];
			
			$stats[$product]['total'] = empty($stats[$product]['total']) ? 
				array(
					'unique'     => 0,
					'raw'        => 0,
					'customers'  => 0,
					'leads'      => 0,
					'sales'      => 0,
					'commission' => 0,
				) : $stats[$product]['total'];
			
			$stats[$product]['total']['unique']     += 1;
			$stats[$product]['total']['raw']        += $c->raw_clicks;
			$stats[$product]['total']['leads']      += !empty($c->leads) ? $c->leads : 0;
			
			if (!empty($c->order_id)) {
				$order = ORM::factory('Order', $c->order_id);
				if ($order->loaded() && $order->internal_status != 'cancelled' && $order->internal_status != 'refunded') {
					$stats[$product]['total']['customers']  += 1;
					$stats[$product]['total']['sales']      += $order->_get_total();
					$stats[$product]['total']['commission'] += $order->commission;
				}
			}
		
			$campaign  = empty($c->campaign) ? 'n/a' : $c->campaign;
			
			
			$stats[$product]['campaigns'][$campaign] = empty($stats[$product]['campaigns'][$campaign]) ? array(
				'subcampaigns' => array(),
				'total'     => array(),
			) : $stats[$product]['campaigns'][$campaign];
			
			$stats[$product]['campaigns'][$campaign]['total'] = empty($stats[$product]['campaigns'][$campaign]['total']) ? 
				array(
					'unique'     => 0,
					'raw'        => 0,
					'customers'  => 0,
					'leads'      => 0,
					'sales'      => 0,
					'commission' => 0,
				) : $stats[$product]['campaigns'][$campaign]['total'];
			
			$stats[$product]['campaigns'][$campaign]['total']['unique']     += 1;
			$stats[$product]['campaigns'][$campaign]['total']['raw']        += $c->raw_clicks;
			$stats[$product]['campaigns'][$campaign]['total']['leads']      += !empty($c->leads) ? $c->leads : 0;
			
			if (!empty($c->order_id)) {
				if ($order->loaded() && $order->internal_status != 'cancelled' && $order->internal_status != 'refunded') {
					$stats[$product]['campaigns'][$campaign]['total']['customers']  += 1;
					$stats[$product]['campaigns'][$campaign]['total']['sales']      += $order->_get_total();
					$stats[$product]['campaigns'][$campaign]['total']['commission'] += $order->commission;
				}
			}
			
			
			
			if (empty($c->subcampaign)) {
				continue;
			}
			
			$subcampaign  = $c->subcampaign;
			
			$stats[$product]['campaigns'][$campaign]['subcampaigns'][$subcampaign] = empty($stats[$product]['campaigns'][$campaign]['subcampaigns'][$subcampaign]) ? 
				array(
					'unique'     => 0,
					'raw'        => 0,
					'customers'  => 0,
					'leads'      => 0,
					'sales'      => 0,
					'commission' => 0,
				) : $stats[$product]['campaigns'][$campaign]['subcampaigns'][$subcampaign];
			
			$stats[$product]['campaigns'][$campaign]['subcampaigns'][$subcampaign]['unique']     += 1;
			$stats[$product]['campaigns'][$campaign]['subcampaigns'][$subcampaign]['raw']        += $c->raw_clicks;
			$stats[$product]['campaigns'][$campaign]['subcampaigns'][$subcampaign]['leads']      += !empty($c->leads) ? $c->leads : 0;
			
			if (!empty($c->order_id)) {
				if ($order->loaded() && $order->internal_status != 'cancelled' && $order->internal_status != 'refunded') {
					$stats[$product]['campaigns'][$campaign]['subcampaigns'][$subcampaign]['customers']  += 1;
					$stats[$product]['campaigns'][$campaign]['subcampaigns'][$subcampaign]['sales']      += $order->_get_total();
					$stats[$product]['campaigns'][$campaign]['subcampaigns'][$subcampaign]['commission'] += $order->commission;
				}
			}
		
		}
		
/*
		ksort($stats);
		
		array_walk($stats, function($data, $product) use (&$stats) {
			ksort($stats[$product]['campaigns']);
		});
		
		foreach ($stats as $p => $d1) {
			foreach ($stats[$p]['campaigns'] as $c => $d2) {
				array_walk($stats, function($data, $subcampaign) use (&$stats, $p, $c) {
					ksort($stats[$p]['campaigns'][$c]['subcampaigns']);
				});
			}
		
		}
*/
		
		self::_cache_report($params, 'product_breakdown', $stats);
		
		return $stats;
	}

	public static function _cache_report($params, $type, $report) {
		//cache the report
		$report_cache = ORM::factory('ClickReport');
		$report_cache->date_day = $params['date'];

		if (!empty($params['product'])) {
			$report_cache->product = $params['product'];
		}
		
		if (!empty($params['campaign'])) {
			$report_cache->campaign = $params['campaign'];
		}

		$report_cache->affiliate_id = $params['affiliate_id'];
		
		$report_cache->report = json_encode($report);
		$report_cache->date_added = DB::expr('NOW()');
		$report_cache->type       = $type;
		$report_cache->save();
	}
}