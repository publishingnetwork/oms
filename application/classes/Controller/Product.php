<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Product extends Controller_Check {
	
	public function action_index() {
		
		$products = ORM::factory('Product')
			->order_by('actual_name', 'desc')
			->find_all();
		
		$products_view = array();
		foreach ($products as $p) {
			$p = $p->as_array();
			
			$skus = ORM::factory('SkusToProduct')->where('product_id', '=', $p['id'])->find_all();
			
			$p['skus'] = array();
			foreach ($skus as $s) {
				$p['skus'][] =  $s->as_array();
			}
			
			$products_view[] = $p;
		}

		$this->page_view->body = View::Factory('product')
			->set('products', $products_view)
			->render();
		$this->response->body($this->page_view);
	}
	
	public function action_get() {
		$this->response->headers('Content-Type', 'application/json; charset=utf-8');
	
		$product = ORM::factory('Product', $this->request->param('id'));
		
		if ($product->loaded()) {
			
			$product_return = array(
				'actual_name'            => $product->actual_name,
				'name'                   => $product->name,
				'paypal_ids'             => $product->paypal_ids,
				'default_commission'     => $product->default_commission,
				'default_int_commission' => $product->default_int_commission,
				'urls'                   => $product->urls,
				'affiliate_status'       => $product->affiliate_status,
				'fulfilment_center'      => $product->fulfilment_center,
				'skus'                   => array()
			);
			
			$skus = ORM::factory('SkusToProduct')->where('product_id', '=', $product->id)->find_all();
			
			foreach ($skus as $s) {
				$product_return['skus'][] =  $s->as_array();
			}
			
			$this->response->body(json_encode(array(
				'status' => 'success',
				'data'   => $product_return
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
	
	public function action_save() {
		$this->response->headers('Content-Type', 'application/json; charset=utf-8');
		
		$id = $this->request->post('id');
		
		$product = $id ? ORM::factory('Product', $id) : ORM::factory('Product');
		
		$product->name                   = $this->request->post('name');
		$product->actual_name            = $this->request->post('actual_name');
		$product->default_commission     = $this->request->post('default_commission');
		$product->default_int_commission = $this->request->post('default_int_commission');
		$product->affiliate_status       = $this->request->post('affiliate_status');
		$product->fulfilment_center      = $this->request->post('fulfilment_center');
		$product->urls                   = $this->request->post('urls');
		$product->paypal_ids             = $this->request->post('paypal_ids');
		
		if (!$id) {
			$product->date_added = DB::expr('NOW()');
		}
		
		$product->save();
				
		DB::delete('skus_to_products')->where('product_id', '=', $product->id)->execute();
		
		$skus_input = explode(',', $this->request->post('skus'));

		
		$sku = ORM::factory('SkusToProduct');
		foreach ($skus_input as $s_i) {
			list($name, $quantity) = explode('=', $s_i);
			$sku->clear();
			
			$sku->product_id = $product->id;
			$sku->sku = $name;
			$sku->quantity = $quantity;
			$sku->date_added = DB::expr('NOW()');
			$sku->save();
		}
		
		$this->response->body(json_encode(array(
			'status'  => 'success',
			'message' => 'Product saved!'
		)));
		return;
	}
	
	public function action_delete() {
		$this->response->headers('Content-Type', 'application/json; charset=utf-8');
		
		ORM::factory('Product', $this->request->param('id'))->delete();
		
		$this->response->body(json_encode(array(
			'status'  => 'success'
		)));
	}
}