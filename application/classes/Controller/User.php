<?php defined('SYSPATH') or die('No direct script access.');

class Controller_User extends Controller_Check {
	
	public function action_index() {


		$users = ORM::Factory('User')->find_all();

		
		$this->page_view->body = View::Factory('user')
			->set('users', $users)
			->render();
		$this->response->body($this->page_view);
	}
	
	
	public function action_get() {
		$this->response->headers('Content-Type', 'application/json; charset=utf-8');
	
		$user = ORM::factory('user', $this->request->param('id'));
		
		if ($user->loaded()) {
			
			$user = $user->as_array();
			unset($user['password']);
			
			$this->response->body(json_encode(array(
				'status' => 'success',
				'data'   => $user
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
		
		$user = $id ? ORM::factory('user', $id) : ORM::factory('user');
		
		$fields = array('name', 'login', 'type', 'status');
		
		foreach ($fields as $f) {
			$user->$f = $this->request->post($f);
		}
		
		$password = $this->request->post('password');
		if ($id) {
			if (!empty($password)) {
				$user->password = sha1($password);
			}
		} else {
			$user->hidden_columns = 'public_id,time_added,internal_status,comments,tracking_id,gross,fee,net,shipping_cost,shipping_method,fullfillment_status,fullfillment_id';
			$user->date_added = DB::expr('NOW()');
			$user->password = sha1($password);
		}
		
		$user->save();
		
		$this->response->body(json_encode(array(
			'status'  => 'success',
		)));
		return;
	}
	
	public function action_delete() {
		$this->response->headers('Content-Type', 'application/json; charset=utf-8');
		
		ORM::factory('user', $this->request->param('id'))->delete();

		$this->response->body(json_encode(array(
			'status'  => 'success'
		)));
	}

	
	public function action_get_hidden_columns() {
		$this->response->headers('Content-Type', 'application/json; charset=utf-8');
		
		$hidden_columns = explode(',', $this->user->hidden_columns);
		
		$this->response->body(json_encode(array(
			'status'         => 'success',
			'hidden_columns' => $hidden_columns
		)));

	}	
	
	public function action_save_hidden_columns() {
		
		$post = $this->request->post();

		$hidden_columns = array();
		foreach ($post as $name) {
			$hidden_columns[] = $name;
		}
		
		$this->user->hidden_columns = implode(',', $hidden_columns);
		$this->user->save();
		
		$this->redirect('order');
	}
	

}