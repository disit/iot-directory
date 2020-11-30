<?php
   /* Snap4City: IoT-Directory
   Copyright (C) 2017 DISIT Lab https://www.disit.org - University of Florence

   This program is free software; you can redistribute it and/or
   modify it under the terms of the GNU General Public License
   as published by the Free Software Foundation; either version 2
   of the License, or (at your option) any later version.
   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.
   You should have received a copy of the GNU General Public License
   along with this program; if not, write to the Free Software
   Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA. */


$result=array("status"=>"","msg"=>"","content"=>"","log"=>"", "error_msg"=>"");	
/* all the primitives return an array "result" with the following structure

result["status"] = ok/ko; reports the status of the operation (mandatory)
result["msg"] a message related to the execution of the operation (optional)
result["content"] in case of positive execution of the operation the content extracted from the db (optional)
result["log"] keep trace of the operations executed on the db

This array should be encoded in json
*/	

header("Content-type: application/json");
header("Access-Control-Allow-Origin: *\r\n");
include ('../config.php');
include ('common.php');
error_reporting(E_ERROR | E_NOTICE);

if(isset($_REQUEST['action']) && !empty($_REQUEST['action'])) 
{
	$action = $_REQUEST['action'];
}
else
{
	$result['status'] = 'ko';
	$result['msg'] = 'action not present'; 
	$result['error_msg']='action not present';
	$result['log'] = 'ldap.php action not present';
	my_log($result);
	exit();
}

$headers = apache_request_headers();
if (isset($headers['Authorization']) && strlen($headers['Authorization'])>8 )
{
	$_REQUEST['token']=substr($headers['Authorization'],7);
}

require '../sso/autoload.php';
use Jumbojett\OpenIDConnectClient;

$oidc = new OpenIDConnectClient($keycloakHostUri, $clientId, $clientSecret);
$oidc->providerConfigParam(array('token_endpoint'    => $keycloakHostUri.'/auth/realms/master/protocol/openid-connect/token',
                                 'userinfo_endpoint' => $keycloakHostUri.'/auth/realms/master/protocol/openid-connect/userinfo'));

$accessToken = "";
if (isset($_REQUEST['nodered']))
{
    if ((isset($_REQUEST['token']))&&($_REQUEST['token']!='undefined'))
        $accessToken = $_REQUEST['token'];
}
else
{
    if (isset($_REQUEST['token']))
    {
		$mctime=microtime(true);
        $tkn = $oidc->refreshToken($_REQUEST['token']);
		error_log("---- ldap.php:".(microtime(true)-$mctime));
        $accessToken = $tkn->access_token;
    }
}
if (empty($accessToken))
{
    $result["status"]="ko";
    $result['msg'] = "Access Token not present";
    $result["error_msg"] .= "Access Token not present";
    $result["log"]= "ldap.php AccessToken not present\r\n";
    my_log($result);
    exit();
}

//retrieve username, organization and role from the accetoken
//TODO avoid passing all the parameters for LDAP
get_user_info($accessToken, $username, $organization, $oidc, $role, $result, $ldapBaseName, $ldapServer, $ldapPort, $ldapAdminName, $ldapAdminPwd);

if ($result["status"]!="ok")
{
    $result["status"]="ko";
    $result['msg'] = "Cannot retrieve user information";
    $result["error_msg"] .= "Problem in ldap (Cannot retrieve user information)";
    $result["log"]= "ldap.php Cannot retrieve user information\r\n";
    my_log($result);
    exit();
}

if($action == 'get_all_ou')
{
	$connection = ldap_connect($ldapServer, $ldapPort);
	ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, 3);
	$bind = ldap_bind($connection, $ldapAdminName, $ldapAdminPwd);
	$resultldap = ldap_search($connection, $ldapBaseName, '(objectClass=organizationalUnit)');
	$entries = ldap_get_entries($connection, $resultldap);

	if (ldap_count_entries($connection,$resultldap)==0)
	{
		$result["error_msg"] .="No LDAP organization Unit found at all";
		$result['status'] = 'ko'; 
		$result['msg'] = 'Error: No LDAP organization Unit found at all <br/>';
		$result['log'] .= '\n\r action:get_all_ou. Error: No LDAP organization Unit found at all ';
	}
	else
	{
		for ($i = 0; $i<$entries["count"]; $i++) 
		{
			$allOu[$i]=$entries[$i]["ou"][0];
		}
		$result['status'] = 'ok';
		$result['content'] =  $allOu;
		$result['log'] .= "\n\r action:get_all_ou. Ok, got n-entries:".count($allOu);
	}	
	my_log($result);
}
else if($action == 'get_logged_ou')
{
	$connection = ldap_connect($ldapServer, $ldapPort);
	ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, 3);
	$bind = ldap_bind($connection, $ldapAdminName, $ldapAdminPwd);
	$userDN="cn=". $username .",".$ldapBaseName;
	$resultldap = ldap_search($connection, $ldapBaseName, '(&(objectClass=organizationalUnit)(l=' . $userDN . '))');
	$entries = ldap_get_entries($connection, $resultldap);

	if (ldap_count_entries($connection,$resultldap)==0)
	{
		$result["error_msg"] .="No LDAP organization Unit found for user".$userDN;
		$result['status'] = 'ko';
		$result['msg'] = 'Error: No LDAP organization Unit found for user '.$userDN.' <br/>';
		$result['log'] .= '\n\r action:get_logged_ou. Error: No LDAP organization Unit found for user '.$userDN;
	}
	else
	{
		$result["status"] = 'ok';
		$result["content"] =  $entries["0"]["ou"][0];
		$result["log"] .= "\n\r action:get_logged_ou. Ok, got ".$entries["0"]["ou"][0];
	}
	my_log($result);
}
else if($action == 'get_group_for_ou')
{
	$missingParams=missingParameters(array('ou'));

	if (!empty($missingParams))
	{
		$result["status"]="ko";
        $result['msg'] = "Missing Parameters";
        $result["error_msg"] .= "Problem in getting groups (Missing parameters: ".implode(", ",$missingParams)." )";
        $result["log"]= "action=get_group_for_ou - error Missing Parameters: ".implode(", ",$missingParams)." \r\n";
	}
	else 
	{
		$connection = ldap_connect($ldapServer, $ldapPort);
		ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, 3);
		$bind = ldap_bind($connection, $ldapAdminName, $ldapAdminPwd);
		$resultldap = ldap_search($connection, $ldapBaseName, '(&(objectClass=groupOfNames)(ou='.$_REQUEST['ou'].'))');
		$entries = ldap_get_entries($connection, $resultldap);

		$allGroupsUserOu=array();
		for ($i = 0; $i<$entries["count"]; $i++) 
		{
			$allGroupsUserOu[$i]=$entries[$i]["cn"][0];
		}
        
		$result['status'] = 'ok';
		$result['content'] =  $allGroupsUserOu;
		$result['log'] .= "\n\r action:get_group_for_ou. Ok, got n-entries: ". count($allGroupsUserOu);
	}

	my_log($result);
}
else 
{
	$result['status'] = 'ko';
	$result['msg'] = 'invalid action ' . $action;
	$result['log'] = 'invalid action ' . $action;
	my_log($result);
}
