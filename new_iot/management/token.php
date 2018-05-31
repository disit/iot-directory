<?php

require '../config.php';
require '../sso/autoload.php';
use Jumbojett\OpenIDConnectClient;

session_start();

echo "in";

print_r($_REQUEST);

if (isset($_SESSION['refreshToken'])) {
  $oidc = new OpenIDConnectClient('https://www.snap4city.org', $clientId, $clientSecret);
  $oidc->providerConfigParam(array('token_endpoint' => 'https://www.snap4city.org/auth/realms/master/protocol/openid-connect/token'));

  $tkn = $oidc->refreshToken($_SESSION['refreshToken']);

  $accessToken = $tkn->access_token;
  $_SESSION['refreshToken'] = $tkn->refresh_token;


$a ="http://192.168.0.207/ownership-api/v1/list/?type=IOTID&accessToken=". $accessToken;

echo $a;


$json = file_get_contents("http://192.168.0.207/ownership-api/v1/list/?type=IOTID&username=finaluser1");    //accessToken=". $accessToken);

print_r($json);


$json = file_get_contents("http://192.168.0.207/ownership-api/v1/list/?type=IOTID&accessToken=". $accessToken);

print_r($json);
echo "done";
}


echo "out";


?>

