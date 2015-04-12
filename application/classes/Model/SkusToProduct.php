<?php

class Model_SkusToProduct extends ORM {
	protected $_table_name = 'skus_to_products';
	
	public static function _get_table() {
		$table = array();
		$skus_to_product = ORM::factory('SkusToProduct')
			->find_all();
			
		foreach ($skus_to_product as $s) {
			if (!isset($table[$s->product_id])) {
				$table[$s->product_id] = array();
			}
			
			if (isset($table[$s->product_id][$s->sku])) {
				//we already have this sku in the table, so we have to increment the number
				$table[$s->product_id][$s->sku] += $s->quantity;
			} else {
				//we don't have the sku yet
				$table[$s->product_id][$s->sku] = $s->quantity;
			}
		}
		
		return $table;
	}
}