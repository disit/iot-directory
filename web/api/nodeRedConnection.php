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
	$result['log'] = 'nodeRedConnection.php action not present';
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
if ((isset($_REQUEST['token']))&&($_REQUEST['token']!='undefined'))
	$accessToken = $_REQUEST['token'];

if (empty($accessToken))
{
    $result["status"]="ko";
    $result['msg'] = "Access Token not present";
    $result["error_msg"] .= "Access Token not present";
    $result["log"]= "nodeRedConnection.php AccessToken not present\r\n";
    my_log($result);
    exit();
}

//retrieve username, organization and role from the accetoken
//TODO avoid passing all the parameters for LDAP
get_user_info($accessToken, $username, $organization, $oidc, $role, $result, $ldapBaseName, $ldapServer, $ldapPort, $ldapAdminName, $ldapAdminPwd);

if ($action=="get_organization")
{  
	if (empty($organization))
	{
        $result["status"]="ko";
        $result['msg'] = "Organization is empty";
        $result["error_msg"] .= "Problem in get organization (Organization is empty)";
        $result["log"]= "action=get organization - error Organization is empty"." \r\n";

	}
	else 
	{
        $result["status"]="ok";
        $info=get_organization_info($organizationApiURI, $organization);
		$result["content"]=$organization;
		$result["info"]=$info;
        $result["msg"].=" ok";
	}

	my_log($result);
}

/*
function checkLdapMembership($connection, $userDn, $tool, $ldapBaseName) 
{
	 $result = ldap_search($connection, $ldapBaseName, '(&(objectClass=posixGroup)(memberUid=' . $userDn . '))');
	 $entries = ldap_get_entries($connection, $result);
	 foreach ($entries as $key => $value) 
	 {
		if(is_numeric($key)) 
		{
		   if($value["cn"]["0"] == $tool) {
			  return true;
		   }
		}
	 }
	 return false;
 }

function checkLdapRole($connection, $userDn, $role, $ldapBaseName) 
{
  $result = ldap_search($connection, $ldapBaseName, '(&(objectClass=organizationalRole)(cn=' . $role . ')(roleOccupant=' . $userDn . '))');
  $entries = ldap_get_entries($connection, $result);
  foreach ($entries as $key => $value) 
  {
	 if(is_numeric($key)) 
	 {
		if($value["cn"]["0"] == $role) {
		   return true;
		}
	 }
  }
  return false;
}

function findLdapOrganizationalUnit($connection, $userDn, $tool, $ldapBaseName) 
{
	$result = ldap_search($connection, $ldapBaseName, '(&(objectClass=organizationalUnit)(l=' . $userDn . '))');
	$entries = ldap_get_entries($connection, $result);

    if (ldap_count_entries($connection,$result)==0){
        return "NULL";
        }
    else{
        $ou=$entries["0"]["ou"][0];
        return $ou;
    }
}*/
