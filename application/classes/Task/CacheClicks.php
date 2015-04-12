<?php defined('SYSPATH') or die('No direct script access.');
 
class Task_Cacheclicks extends Minion_Task
{

    protected function _execute(array $params)
    {
		$affiliates = ORM::factory('Affiliate')->find_all();
		
		foreach ($affiliates as $a) {
			$affiliate_id = $a->id;
			//echo "$affiliate_id \n";
			$date_start = date('m/d/Y', strtotime('-90 day'));
	
			$first_click = ORM::factory('Click')
				->where('affiliate_id', '=', $affiliate_id)
				->order_by('date_added', 'asc');
			
			if (!empty($date_start)) {
				$first_click->and_where('date_added', '>=', date('Y-m-d 00:00:00', strtotime($date_start)));
			}
			
			if (!empty($date_end)) {
				$first_click->and_where('date_added', '<=', date('Y-m-d 23:59:59', strtotime('today')));
			}		
			
/*
			if (!empty($product)) {
				$first_click->and_where('product', '=', $product);
			}
			
			if (!empty($campaign)) {
				$first_click->and_where('campaign', '=', $campaign);
			}	
*/	
			
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
					
					Model_Click::_for_stats(
						array(
							'affiliate_id' => $affiliate_id,
							'date'         => $now->format('Y-m-d'),
							'product'      => '',
							'campaign'     => '',
						),
						true
					);
					
					Model_Click::_for_product_stats(
						array(
							'affiliate_id' => $affiliate_id,
							'date'         => $now->format('Y-m-d'),
						),
						true
					);
					
					$now->sub(new DateInterval('P1D'));
					
				} while ($now >= $start_date);			
			}
		}
    }
}