<?php

require '../config.php';
require '../sso/autoload.php';
use Jumbojett\OpenIDConnectClient;

session_start();

echo "in";

print_r($_SESSION['refreshToken']);

echo "<br><br>";

if (isset($_SESSION['refreshToken'])) {
  $oidc = new OpenIDConnectClient($keycloakHostUri, $clientId, $clientSecret);
  $oidc->providerConfigParam(array('token_endpoint' => $keycloakHostUri.'/auth/realms/master/protocol/openid-connect/token'));

  $tkn = $oidc->refreshToken($_SESSION['refreshToken']);

  $accessToken = $tkn->access_token;
  $_SESSION['refreshToken'] = $tkn->refresh_token;


$a =$ownershipURI."ownership-api/v1/list/?type=IOTID&accessToken=". $accessToken;

echo $a;


$json = file_get_contents($ownershipURI."ownership-api/v1/list/?type=IOTID&username=finaluser1");    //accessToken=". $accessToken);

print_r($json);


echo "<br><br>";

$json = file_get_contents($ownershipURI."ownership-api/v1/list/?type=IOTID&accessToken=". $accessToken);

print_r($json);
echo "done";
}


echo "out";


?>

