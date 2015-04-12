<?php

class Model_Affiliate extends ORM {
	
	protected $_primary_key = 'id';
	
	public function _get_commission($product_id, $country) {
		$commission = ORM::factory('AffiliateCommission')
			->where(    'product_id',   '=', $product_id)
			->and_where('affiliate_id', '=', $this->id)
			->find();

		if ($country == 'US') {
			$default_field = 'default_commission';
			$field = 'commission';
		} else {
			$default_field = 'default_int_commission';
			$field = 'int_commission';
		}

		return !$commission->loaded() ?
			ORM::factory('Product', $product_id)->$default_field : //fallback to default commission
			$commission->$field;
	}
	
	public function _get_products() {
		$products = ORM::factory('Product')->find_all();
		
		$products_return = array();
		foreach ($products as $p) {
			$custom_status = ORM::factory('AffiliateProduct')
				->where('affiliate_id', '=', $this->id)
				->and_where('product_id', '=', $p->id)
				->find();
				
			if ($custom_status->loaded()) {
				if ($custom_status->status == 'show') {
					$products_return[] = $p;
				}
			} else {
				if ($p->affiliate_status == 'show') {
					$products_return[] = $p;
				}
			}	
		}
		
		return $products_return;
	}
	
	public function _get_campaigns() {
		$campaigns = DB::select('campaign')
			->from('clicks')
			->where('affiliate_id', '=', $this->id)
			->group_by('campaign')
			->execute();
			
		$campaigns_return = array();
		
		foreach ($campaigns as $c) {
			!empty($c['campaign']) &&
				$campaigns_return[] = $c['campaign'];	
		}	
		
		return $campaigns_return;
	}
}