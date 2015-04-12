<?php defined('SYSPATH') or die('No direct script access.');

class Controller_AffiliateStat extends Controller_Check {
	
	public function action_index() {
		//let's find the affiliate's oldest click
		
		$affiliate_id = $_GET['affiliate_id'];
		
		$product   = $this->request->post('product') ? $this->request->post('product') : '';
		$campaign     = $this->request->post('campaign') ? $this->request->post('campaign') : '';

		$date_start = $this->request->post('date_start');
		$date_end   = $this->request->post('date_end');
		
		!$date_start &&
			$date_start = date('m/d/Y', strtotime('-7 day 00:00'));

		$first_click = ORM::factory('Click')
			->where('affiliate_id', '=', $affiliate_id)
			->order_by('date_added', 'asc');
		
		if (!empty($date_start)) {
			$first_click->and_where('date_added', '>=', date('Y-m-d 00:00:00', strtotime($date_start)));
		}
		
		if (!empty($date_end)) {
			$first_click->and_where('date_added', '<=', date('Y-m-d 23:59:59', strtotime($date_end)));
		}		
		
		if (!empty($product)) {
			$first_click->and_where('product', '=', $product);
		}
		
		if (!empty($campaign)) {
			$first_click->and_where('campaign', '=', $campaign);
		}		
		
		$first_click = $first_click->find();

		$stats = array();

		if ($first_click->loaded()) {	
			$start_date = new DateTime($first_click->date_added);
			$start_date->setTime(0, 0, 0);

			$now = new DateTime();
			
			
			//$now->setTimeZone(new DateTimeZone('UTC'));
			//$now->setTime(0, 0, 0);
			
			//var_dump(date('Y-m-d H:i:sP'), $start_date->format('Y-m-d H:i:sP'), $now->format('Y-m-d H:i:sP'));exit;
			
			do {
				
				$stats[$now->format('m/d/Y')] = Model_Click::_for_stats(
					array(
						'affiliate_id' => $affiliate_id,
						'date'         => $now->format('Y-m-d'),
						'product'      => $product,
						'campaign'     => $campaign,
					),
					false
				);
				
				$now->sub(new DateInterval('P1D'));
				
			} while ($now >= $start_date);			
		}				

		$affiliate = ORM::factory('Affiliate', $affiliate_id);
			
		$all_products  = array();
		$user_products = $affiliate->_get_products();
		foreach ($user_products as $u_p) {
			$all_products[] = $u_p->actual_name;
		}			
			
		$this->page_view->body = View::Factory('affiliate_stats')
			->set('stats',        $stats)
			->set('affiliate_name', $affiliate->name)
			->set('products',       array_unique($all_products))
			->set('campaigns',      $affiliate->_get_campaigns())
			->set('affiliate_id'  , $affiliate_id)
			->set('campaign'  ,     $campaign)
			->set('product'    ,    $product)
			->set('date_start',     $date_start)
			->set('date_end',       $date_end)			
			->render();
		$this->response->body($this->page_view);
		
	}

}