<?php

	/*
	 * Simple CURL wrapper class.
	 */
	class Curl {
		
		private $curl_handle; //CURL handle
		
		public function __construct($options = false) {
			$this->curl_handle = curl_init();
			curl_setopt($this->curl_handle, CURLOPT_FOLLOWLOCATION, true); //follow redirects
			curl_setopt($this->curl_handle, CURLOPT_RETURNTRANSFER, true); //return contents of the requested url
			curl_setopt($this->curl_handle, CURLOPT_HEADER, true);
			curl_setopt($this->curl_handle, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($this->curl_handle, CURLOPT_SSLVERSION, 3);
			curl_setopt($this->curl_handle, CURLOPT_CONNECTTIMEOUT, 60);
			curl_setopt($this->curl_handle, CURLOPT_TIMEOUT, 60);
			
			/*
			 * If user passes $options array, we set these are CURL options
			 */
			if (is_array($options)) {
				foreach ($options as $option => $value) {
					curl_setopt($this->curl_handle, $option, $value);
				}
			}
			$cookie_file = tempnam('/tmp', 'cookie'); //temporary file where cookies will be stored in
			curl_setopt($this->curl_handle, CURLOPT_COOKIEFILE, $cookie_file);
			
		}
		
		public function get($url) {
			$this->reset();
			curl_setopt($this->curl_handle, CURLOPT_POST, false); //false, because it's a GET query
			curl_setopt($this->curl_handle, CURLOPT_URL, $url); 
			
			
			$result = curl_exec($this->curl_handle);
			
			if (!$result) {
				return false;
			}
		
			return $result;
		}
		
		public function post($url, $params) {
		
			
			$post_query = http_build_query($params); //generating query string from $params array
			$this->reset();
			curl_setopt($this->curl_handle, CURLOPT_POST, true);
			curl_setopt($this->curl_handle, CURLOPT_POSTFIELDS, $post_query);
			curl_setopt($this->curl_handle, CURLOPT_URL, $url);
			
			$result = curl_exec($this->curl_handle);

			if (!$result) {
				return false;
			}
		
			return $result;
			
		}
		
		public function set_proxy($proxy = '') {
			curl_setopt($this->curl_handle, CURLOPT_PROXY, $proxy);
		}
		
		public function set_user_agent($ua = '') {
			curl_setopt($this->curl_handle, CURLOPT_USERAGENT, $ua);
		}
		
		public function get_http_code() {
			 return curl_getinfo($this->curl_handle, CURLINFO_HTTP_CODE);
		}
		
		public function get_content_type() {
			 return curl_getinfo($this->curl_handle, CURLINFO_CONTENT_TYPE);
		}
		
		public function get_redirect_url() {
			 return curl_getinfo($this->curl_handle, CURLINFO_REDIRECT_URL);
		}
		
		public function get_error_code() {
			 return curl_errno($this->curl_handle);
		}
		
		public function __destruct() {
			curl_close($this->curl_handle);
		}
		
		public function get_file($url, $path, $binary = true) {
			
			if ($binary) {
				$file_handle = fopen($path, 'wb');
			} else {
				$file_handle = fopen($path, 'w');
			}
			$this->reset();
			curl_setopt($this->curl_handle, CURLOPT_URL, $url);
			curl_setopt($this->curl_handle, CURLOPT_FILE, $file_handle);
			curl_setopt($this->curl_handle, CURLOPT_BINARYTRANSFER, true);
			curl_setopt($this->curl_handle, CURLOPT_HEADER, false);
			
			$result = curl_exec($this->curl_handle);
		
			return $result;
		}
		
		public function reset() {
			curl_setopt($this->curl_handle, CURLOPT_BINARYTRANSFER, false);
			curl_setopt($this->curl_handle, CURLOPT_HEADER, true);
			curl_setopt($this->curl_handle, CURLOPT_POST, false);
			curl_setopt($this->curl_handle, CURLOPT_RETURNTRANSFER, true);
		}
	}