<?php

function send_lead() {
	$url = 'http://energizegreens.com/oms/system/catch_lead';
	//$url = 'http://localhost/oms-holistic/system/catch_lead';

	$curl  = curl_init();

	curl_setopt_array($curl, array(
		CURLOPT_URL            => $url,
		CURLOPT_POST           => 1,
		//CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_POSTFIELDS     => array(
			'domain'          => $_SERVER['HTTP_HOST'],
			'ip'              => $_SERVER['REMOTE_ADDR'],
			'user_agent_hash' => md5($_SERVER['HTTP_USER_AGENT']),
			'affiliate_id'    => !empty($_COOKIE['affiliate_id']) ? $_COOKIE['affiliate_id'] : '',
		),
	));
	
	curl_exec($curl);
}

send_lead();