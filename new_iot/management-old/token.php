<?php

require '../config.php';
require '../sso/autoload.php';
use Jumbojett\OpenIDConnectClient;

session_start();

echo "in";

if (isset($_SESSION['refreshToken'])) {
  $oidc = new OpenIDConnectClient('https://www.snap4city.org', $clientId, $clientSecret);
  $oidc->providerConfigParam(array('token_endpoint' => 'https://www.snap4city.org/auth/realms/master/protocol/openid-connect/token'));

  $tkn = $oidc->refreshToken($_SESSION['refreshToken']);

  $accessToken = $tkn->access_token;
  $_SESSION['refreshToken'] = $tkn->refresh_token;

// echo $accessToken;

$json = file_get_contents("http://192.168.0.10/ownership-api/v1/list/?type=appid&username=snap4city"); // accessToken=" . $accessToken);

print_r($json);
echo "done";
}


echo "out";


?>

