<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Affiliate extends Controller_Check {
	
	public function action_index() {
	
		require_once(Kohana::find_file('vendor', 'geo_arrays'));

		$affiliates = ORM::factory('Affiliate')
			->where('status', '<>', 'deleted')
			->find_all();

		
		$this->page_view->body = View::Factory('affiliate')
			->set('affiliates', $affiliates)
			->set('countries', $geo_countries)
			->render();
		$this->response->body($this->page_view);
	}
	
	
	public function action_get_commissions() {
		$this->response->headers('Content-Type', 'application/json; charset=utf-8');
	
		//$affiliate_id = ORM::factory('Affiliate', $this->request->param('id'));
		
		$products = ORM::factory('Product')->find_all();
		
		$commissions = array();
		
		foreach ($products as $p) {
			$custom_commission = ORM::factory('AffiliateCommission')
				->where('affiliate_id', '=', $this->request->param('id'))
				->and_where('product_id', '=', $p->id)
				->find();
				
			if ($custom_commission->loaded()) {
				if ($this->request->param('subid') == 'normal') {
					$commission         = $custom_commission->commission;
					$default_commission = $p->default_commission;
				} else {
					$commission         = $custom_commission->int_commission;
					$default_commission = $p->default_int_commission;
				}
			
				$commissions[] = array(
					'product_id'         => $p->id,
					'product_name'       => $p->name,
					'type'               => 'custom',
					'commission'         => $commission,
					'default_commission' => $default_commission,
				);
			} else {
				if ($this->request->param('subid') == 'normal') {
					$default_commission = $p->default_commission;
				} else {
					$default_commission = $p->default_int_commission;
				}			
			
				$commissions[] = array(
					'product_id'   => $p->id,
					'product_name' => $p->name,
					'type'         => 'default',
					'commission'   => $default_commission,
					'default_commission' => $default_commission,
				);
			}
		}

		$this->response->body(json_encode(array(
			'status'  => 'success',
			'data'    => $commissions
		)));

	}
	
	public function action_save_commissions() {
		$this->response->headers('Content-Type', 'application/json; charset=utf-8');
		
		$post = $this->request->post();
		
		$affiliate_id = $post['affiliate_id'];
		$type         = $post['type'];
		
		foreach ($post['product'] as $id => $p) {
			$commission = ORM::factory('AffiliateCommission')
					->where('affiliate_id', '=', $affiliate_id)
					->and_where('product_id', '=', $id)
					->find();
		
			if ($p['type'] == 'custom') {
				
				if ($commission->loaded()) {
					if ($type == 'normal') {
						$commission->commission   = $p['commission'];
					} else {
						$commission->int_commission   = $p['commission'];
					}
				
					
				} else {
					if ($type == 'normal') {
						$commission->commission   = $p['commission'];
					} else {
						$commission->int_commission   = $p['commission'];
					}
					$commission->affiliate_id = $affiliate_id;
					$commission->product_id   = $id;
				}
				$commission->save();
				
			} else {
				$commission->loaded() &&
					$commission->delete();
			}
		}
		
		$this->response->body(json_encode(array(
			'status'  => 'success',
		)));
	}	

	public function action_get_products() {
		$this->response->headers('Content-Type', 'application/json; charset=utf-8');
	
		//$affiliate_id = ORM::factory('Affiliate', $this->request->param('id'));
		
		$products = ORM::factory('Product')->find_all();
		
		$product_statuses = array();
		
		foreach ($products as $p) {
			$custom_status = ORM::factory('AffiliateProduct')
				->where(    'affiliate_id', '=', $this->request->param('id'))
				->and_where('product_id',   '=', $p->id)
				->find();
				
			if ($custom_status->loaded()) {
				$product_statuses[] = array(
					'product_id'         => $p->id,
					'product_name'       => $p->name,
					'status'             => $custom_status->status
				);
			} else {
				$product_statuses[] = array(
					'product_id'   => $p->id,
					'product_name' => $p->name,
					'status'         => 'default'
				);
			}
		}

		$this->response->body(json_encode(array(
			'status'  => 'success',
			'data'    => $product_statuses
		)));

	}

	public function action_save_products() {
		$this->response->headers('Content-Type', 'application/json; charset=utf-8');
		
		$post = $this->request->post();
		
		$affiliate_id = $post['affiliate_id'];
		
		foreach ($post['product'] as $id => $p) {
			$product_status = ORM::factory('AffiliateProduct')
					->where('affiliate_id', '=', $affiliate_id)
					->and_where('product_id', '=', $id)
					->find();
		
			if ($p['status'] != 'default') {
				
				if ($product_status->loaded()) {
					$product_status->status       = $p['status'];
				} else {
					$product_status->status       = $p['status'];
					$product_status->affiliate_id = $affiliate_id;
					$product_status->product_id   = $id;
				}
				$product_status->save();
				
			} else {
				$product_status->loaded() &&
					$product_status->delete();
			}
		}
		
		$this->response->body(json_encode(array(
			'status'  => 'success',
		)));
	}

	public function action_get() {
		$this->response->headers('Content-Type', 'application/json; charset=utf-8');
	
		$affiliate = ORM::factory('Affiliate', $this->request->param('id'));
		
		if ($affiliate->loaded()) {
			
			$affiliate = $affiliate->as_array();
			unset($affiliate['password']);
			
			$this->response->body(json_encode(array(
				'status' => 'success',
				'data'   => $affiliate
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
		
		$affiliate = $id ? ORM::factory('Affiliate', $id) : ORM::factory('Affiliate');
		
		$fields = array('name', 'business_name', 'login', 'status', 'email', 'paypal_email', 'website', 'country');
		
		foreach ($fields as $f) {
			$affiliate->$f = $this->request->post($f);
		}
		
		$password = $this->request->post('password');
		if ($id) {
			if (!empty($password)) {
				$affiliate->password = sha1($password);
			}
		} else {
			$affiliate->date_added = DB::expr('NOW()');
			$affiliate->password   = sha1($password);
		}
		
		$affiliate->save();
		
		$this->response->body(json_encode(array(
			'status'  => 'success',
		)));
		return;
	}
	
	public function action_delete() {
		$this->response->headers('Content-Type', 'application/json; charset=utf-8');
		
		$affiliate = ORM::factory('Affiliate', $this->request->param('id'));
		
		$affiliate->status = 'deleted';
		$affiliate->save();

		$this->response->body(json_encode(array(
			'status'  => 'success'
		)));
	}

}