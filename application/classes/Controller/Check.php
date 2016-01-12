<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Check extends Controller {
	protected $_permissions = array(
		'admin' => array(
			'Order' => array(
				'index',
				'fix_address',
				'send_for_fullfillment',
				'order_info',
				'modal_details',
				'modal_history',
				'modal_fullfillment',
				'get_address',
				'save_address',
				'get_details',
				'save_details',
				'new_order',
				'export',
				'delete',
				'get_orders_for_merge',
				'merge',
				'report',
				'save_status',
				'save_campaign',
				'save_affiliate',
				'save_shipping',
			),
			'Product' => array(
				'index',
				'new_product',
				'get',
				'save',
				'delete',
			),
			'System' => array(
				'index',
				'logout',
			),
			'User' => array(
				'index',
				'get_hidden_columns',
				'save_hidden_columns',
				'get',
				'save',
				'delete',
			),
			'Affiliate' => array(
				'index',
				'get',
				'save',
				'delete',
				'get_commissions',
				'save_commissions',
				'get_products',
				'save_products',
			),
			'Affiliatepayment' => array(
				'index',
				'get',
				'save',
				'delete',
				'calculate',
				'new',
			),
			'Affiliatestat' => array(
				'index',
			)
		),
		'staff' => array(
			'System' => array(
				'logout',
				'index',
			),
			'User' => array(
				'get_hidden_columns',
				'save_hidden_columns',
				'index',
			),
			'Order' => array(
				'index',
				'fix_address',
				'order_info',
				'modal_details',
				'modal_history',
				'get_address',
				'save_address',
				'get_details',
				'save_details',
				'new_order',
				'export',
				'get_orders_for_merge',
				'merge',
				'report',
				'save_status',
				'save_shipping',
			),
			'Product' => array(
				'index',
				'new_product',
				'get',
				'save',
				'delete',
			),
			'Affiliate' => array(
				'index',
				'get',
				'save',
				'delete',
				'get_commissions',
				'save_commissions',
				'get_products',
				'save_products',
			),
			'Affiliatepayment' => array(
				'index',
				'get',
				'save',
				'delete',
				'calculate',
				'new',
			),
			'Affiliatestat' => array(
				'index',
			)
		),
		'guest' => array(
			'System' => array(
				'logout',
				'index',
				
			),
			'User' => array(
				'get_hidden_columns',
				'save_hidden_columns'
			),
			'Order' => array(
				'index',
				'order_info',
				'modal_details',
				'modal_history',
				'export',
/*
				'get_address',
				'save_address',
				'get_details',
				'save_details',
*/
			),
			'Product' => array(
				'index'
			),
			'Affiliate' => array(
				'index',
			),
			'Affiliatepayment' => array(
				'index',
			),
			'Affiliatestat' => array(
				'index',
			)
		),
		'nobody' => array(
			'Inquiry' => array(),
			'System' => array(
				'login',
				//'ipn',
				'index',
				'catch_fullfillment_update',
				'catch_data',
				'catch_click',
				'catch_lead',
				'scrape_fullfillment',
				'scrape_fullfillment_aus',
				'update_paypal',
				'download_paypal',
				'test_paypal',
			),
			'User' => array(),
			'Order' => array(),
		),
	

	);
	
	protected $_titles = array(
		'Order'   => 'Orders',
		'Product' => 'Products',
		'User'    => 'Users',
	);
	
	public function before() {
		$session = Session::instance();
		
		if (!$session->get('user_id')) {
			$this->user_type = 'nobody';
			$this->user = null;
		} else {
			$this->user = ORM::Factory('User', $session->get('user_id'));
			$this->user_type = $this->user->type;
		}

		if (!in_array($this->request->action(), $this->_permissions[$this->user_type][$this->request->controller()])) {
		
			if ($this->user_type == 'nobody') {
				$this->redirect('system/login');
			} else {
			
				if ($this->request->is_ajax()) {
					$this->response->headers('Content-Type', 'application/json; charset=utf-8');
				
					echo $this->response->body(json_encode(array(
						'status'  => 'error',
						'message' => 'You are not authorized'
					)));
					exit;
				} else {
					$this->redirect('order');
				}

			}
		}

		$this->page_view = View::factory('page');

		View::set_global('user_type', $this->user_type);
		View::set_global('user', $this->user);
		View::set_global('controller', strtolower($this->request->controller()));
		View::set_global('title', !empty($this->_titles[$this->request->controller()]) ? $this->_titles[$this->request->controller()] : '');

	}
	
}