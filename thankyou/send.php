<?php

if (empty($_COOKIE['oms_session'])) {
	$session_id = md5(time());
	setcookie('oms_session', $session_id, time() + 60*60*24, '/');	
} else {
	$session_id = $_COOKIE['oms_session'];
}

function send_data($data, $session_id) {
	$url = 'http://energizegreens.com/oms/system/catch_data';
	//var_dump($data);
	
	if (empty($data) || !is_array($data))
		return;

	if (array_key_exists('custom_fields', $data)) {
		$type = 'aweber';
		$data = array_merge($data['contact_fields'], $data['custom_fields']);
	} elseif (array_key_exists('custom_Address', $data) || array_key_exists('getpostdata', $data)) {
		$type = 'getresponse';
	} else {
		$type = 'paypal';
	}

	$curl  = curl_init();
	
	curl_setopt_array($curl, array(
		CURLOPT_URL            => $url,
		CURLOPT_POST           => 1,
		//CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_POSTFIELDS     => array(
			'domain'          => $_SERVER['HTTP_HOST'],
			'session_id'      => $session_id,
			'ip'              => $_SERVER['REMOTE_ADDR'],
			'user_agent_hash' => md5($_SERVER['HTTP_USER_AGENT']),
			'data'            => base64_encode(json_encode($data)),
			'type'            => $type,
			'affiliate_id'    => !empty($_COOKIE['affiliate_id']) ? $_COOKIE['affiliate_id'] : '',
		),
	));
	
	curl_exec($curl);
}

send_data(!empty($_POST) ? $_POST : $_GET, $session_id);