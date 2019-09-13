<?php

include ('../config.php');

require('Requests.php');

$urls = array();
$headers = array();
$params = array();

$url_to_bulkphp = $redirectUri.'/api/contextBrokerRetrieval_e.php';

$urls[] = $url_to_bulkphp;

$headers[] = array('Content-Type:application/json');
$payload = json_encode( $_POST['data'] );

$params[] = $payload;

$callback = function($data, $info)
{
	echo $data;
};


$requests = new Requests();
$requests->process($urls, $headers, $params, $callback);

?>
