<?php defined('SYSPATH') or die('No direct script access.');

class Controller_AffiliatePayment extends Controller_Check {
	
	public function action_index() {

		$affiliate_payments = ORM::Factory('AffiliatePayment');

		if (!empty($_GET['affiliate_id'])) {
			$affiliate_payments->where('affiliate_id', '=', $_GET['affiliate_id']);
		
			$affiliate = ORM::factory('Affiliate', $_GET['affiliate_id']);
		}
		
		$affiliate_payments = $affiliate_payments
			->order_by('date_added', 'desc')
			->find_all();
		
		
		$total_paid = 0;
		$affiliate_payments_view = array();
		foreach ($affiliate_payments as $a_p) {
			$a_p = $a_p->as_array();
			$a_p['affiliate'] = ORM::factory('Affiliate', $a_p['affiliate_id'])->name;
			$affiliate_payments_view[] = (object) $a_p;
			$total_paid += $a_p['total'];
		}
		
		if (!empty($_GET['affiliate_id'])) {
			$total_made      = 0;
			$total_refunds   = 0;
			$current_balance = 0;
			
			$orders = ORM::factory('Order')
				->where('affiliate_id', '=', $_GET['affiliate_id'])
				//->and_where('internal_status', '<>', 'cancelled')
				->find_all();
			
				
			foreach ($orders as $o) {
				if ($o->internal_status != 'cancelled' && $o->internal_status != 'refunded') {
					$total_made    += $o->commission;
					
					$o->affiliate_status == 'awaiting_payment' &&
						$current_balance += $o->commission;
						
				} elseif ($o->affiliate_status == 'awaiting_refund') {
					$total_refunds += $o->commission;
				}
			}
			$current_balance -= $total_refunds;
		}

		
		
		$this->page_view->body = View::Factory('affiliate_payment')
			->set('affiliate_payments', $affiliate_payments_view)
			->set('affiliate_name',     !empty($affiliate) && $affiliate->loaded() ? $affiliate->name : '')
			->set('paypal_email',       !empty($affiliate) && $affiliate->loaded() ? $affiliate->paypal_email : '')
			->set('affiliate_id',       empty($_GET['affiliate_id']) ? false : $_GET['affiliate_id'])
			->set('total_paid',         $total_paid)
			->set('total_refunds',      !empty($total_refunds) ? $total_refunds : 0)
			->set('total_made',         !empty($total_made) ? $total_made : 0)
			->set('current_balance',         !empty($current_balance) ? $current_balance : 0)
			->render();
		$this->response->body($this->page_view);
	}
	
	
	public function action_get() {
		$this->response->headers('Content-Type', 'application/json; charset=utf-8');
	
		$affiliate_payment = ORM::factory('AffiliatePayment', $this->request->param('id'));
		
		if ($affiliate_payment->loaded()) {
			
			$affiliate_payment = $affiliate_payment->as_array();
			$affiliate_payment['affiliate'] = ORM::factory('Affiliate', $affiliate_payment['affiliate_id'])->name;
			
			$this->response->body(json_encode(array(
				'status' => 'success',
				'data'   => $affiliate_payment
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
	
	public function action_new() {
		$this->response->headers('Content-Type', 'application/json; charset=utf-8');
		
		$affiliate_payment = ORM::factory('AffiliatePayment');
		
		$fields = array('paypal_email', 'total');
		
		foreach ($fields as $f) {
			$affiliate_payment->$f = $this->request->post($f);
		}
		
		$affiliate_payment->affiliate_id = $this->request->post('affiliate_id'); 
		$affiliate_payment->date_added   = DB::expr('NOW()');
		$affiliate_payment->save()->reload();
		
		if ($this->request->post('orders_awaiting_payment')) {
		$orders_awaiting_payment = ORM::factory('Order')
				->where('id', 'IN', DB::expr('(' . $this->request->post('orders_awaiting_payment') . ')'))
				->find_all();
				
			foreach ($orders_awaiting_payment as $o) {
				$o->affiliate_status     = 'paid';
				$o->affiliate_payment_id = $affiliate_payment->id;
				$o->save();
			}
		}
		
		if ($this->request->post('orders_awaiting_refund')) {
			$orders_awaiting_refund = ORM::factory('Order')
				->where('id', 'IN', DB::expr('(' . $this->request->post('orders_awaiting_refund') . ')'))
				->find_all();
				
			foreach ($orders_awaiting_refund as $o) {
				$o->affiliate_status     = 'refunded';
				$o->affiliate_payment_id = $affiliate_payment->id;
				$o->save();
			}			
		}
		$this->response->body(json_encode(array(
			'status'  => 'success',
		)));
		return;
	}	
	
	public function action_save() {
		$this->response->headers('Content-Type', 'application/json; charset=utf-8');
		
		$id = $this->request->post('id');
		
		$affiliate_payment = $id ? ORM::factory('AffiliatePayment', $id) : ORM::factory('AffiliatePayment');
		
		if (!$id) {
			$affiliate_payment->affiliate_id = $this->request->post('affiliate_id');
		}
		
		$fields = array('paypal_email', 'total');
		
		foreach ($fields as $f) {
			$affiliate_payment->$f = $this->request->post($f);
		}
		
		
		if (!$id) {
			$affiliate_payment->date_added = DB::expr('NOW()');
		}
		
		$affiliate_payment->save();
		
		$this->response->body(json_encode(array(
			'status'  => 'success',
		)));
		return;
	}
	
	public function action_calculate() {
		$this->response->headers('Content-Type', 'application/json; charset=utf-8');
		
		$affiliate_id = $this->request->param('id');

		$orders = ORM::factory('Order')
			->where(    'date_added',       '>=', date('Y-m-d 00:00:00', strtotime($this->request->post('start_date'))))
			->and_where('date_added',       '<=', date('Y-m-d 23:59:59', strtotime($this->request->post('end_date'))))
			->and_where('affiliate_status', '=', 'awaiting_payment')
			->and_where('affiliate_id',     '=', $affiliate_id)
			->find_all();
			
		$commissions = 0;
		$orders_awaiting_payment = array();
		foreach ($orders as $o) {
			$commissions += $o->commission;
			$orders_awaiting_payment[] = $o->id;
		}
		
		$orders = ORM::factory('Order')
			->and_where('affiliate_status', '=', 'awaiting_refund')
			->and_where('affiliate_id',     '=', $affiliate_id)
			->find_all();
			
		$refunds = 0;
		$orders_awaiting_refund = array();
		foreach ($orders as $o) {
			$refunds += $o->commission;
			$orders_awaiting_refund[] = $o->id;
		}
		
		$this->response->body(json_encode(array(
			'status'                  => 'success',
			'orders_awaiting_payment' => $orders_awaiting_payment,
			'orders_awaiting_refund'  => $orders_awaiting_refund,
			'refunds'                 => $refunds,
			'commissions'             => $commissions,
		)));
	}
	
	public function action_delete() {
		$this->response->headers('Content-Type', 'application/json; charset=utf-8');
		
		$orders = ORM::factory('Order')
			->where('affiliate_payment_id', '=', $this->request->param('id'))
			->find_all();
			
		foreach ($orders as $o) {
			if ($o->affiliate_status == 'paid') {
				$o->affiliate_status =  'awaiting_payment';
				$o->save();
			} elseif ($o->affiliate_status == 'refunded') {
				$o->affiliate_status =  'awaiting_refund';
				$o->save();
			}
		}	
		
		ORM::factory('AffiliatePayment', $this->request->param('id'))->delete();

		$this->response->body(json_encode(array(
			'status'  => 'success'
		)));
	}

}