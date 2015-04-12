<?php


function get_value($cookie, $get) {
	if (empty($_COOKIE[$cookie]) && !empty($_GET[$get])) {
		setcookie($cookie, $_GET[$get], time() + 60*60*24*30, '/');
		$value = $_GET[$get];
		
	} elseif (!empty($_COOKIE[$cookie]) && !empty($_GET[$get])) {
		if ($_COOKIE[$cookie] != $_GET[$get]) {

			setcookie($cookie, $_GET[$get], time() + 60*60*24*30, '/');
			$value = $_GET[$get];
		} else {
			$value = $_COOKIE[$cookie];
		}
	} else {
		$value = !empty($_COOKIE[$cookie]) ? $_COOKIE[$cookie] : '';
	}
	
	return $value;
}

function send_click($affiliate_id, $product, $campaign, $subcampaign) {
	if (empty($product))
		return;

	$url = 'http://energizegreens.com/oms/system/catch_click';
	//$url = 'http://localhost/oms-holistic/system/catch_click';

	$curl  = curl_init();
	
	curl_setopt_array($curl, array(
		CURLOPT_URL            => $url,
		CURLOPT_POST           => 1,
		//CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_POSTFIELDS     => array(
			'domain'          => $_SERVER['HTTP_HOST'],
			'affiliate_id'    => $affiliate_id,
			'product'         => $product,
			'ip'              => $_SERVER['REMOTE_ADDR'],
			'user_agent_hash' => md5($_SERVER['HTTP_USER_AGENT']),
			'campaign'        => $campaign,
			'subcampaign'     => $subcampaign,
		),
	));
	
	curl_exec($curl);
}

$affiliate_id = get_value('affiliate_id', 'aid');
$campaign     = get_value('campaign',     'campaign');
$subcampaign  = get_value('subcampaign',  'pl');

send_click($affiliate_id, $product, $campaign, $subcampaign);