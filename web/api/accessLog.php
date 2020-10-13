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
$link = mysqli_connect($host, $username, $password) or die("failed to connect to server !!");
mysqli_select_db($link, $dbname);
error_reporting(E_ERROR | E_NOTICE);

if(!$link->set_charset("utf8")) 
{
    exit();
}

if(isset($_REQUEST['action']) && !empty($_REQUEST['action'])) 
{
        $action = $_REQUEST['action'];
}
else
{
	$result['status'] = 'ko';
	$result['msg'] = 'action not present'; 
	$result['error_msg']='action not present';
	$result['log'] = 'accessLog.php action not present';
	my_log($result);
	mysqli_close($link);
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
		error_log("---- accessLog.php:".(microtime(true)-$mctime));
        $accessToken = $tkn->access_token;
    }
}
if (empty($accessToken))
{
    $result["status"]="ko";
    $result['msg'] = "Access Token not present";
    $result["error_msg"] .= "Access Token not present";
    $result["log"]= "accessLog.php AccessToken not present\r\n";
    my_log($result);
    mysqli_close($link);
    exit();
}

//retrieve username, organization and role from the accetoken
//TODO avoid passing all the parameters for LDAP
get_user_info($accessToken, $username, $organization, $oidc, $role, $result, $ldapBaseName, $ldapServer, $ldapPort, $ldapAdminName, $ldapAdminPwd);

if ($result["status"]!="ok")
{
    $result["status"]="ko";
    $result['msg'] = "Cannot retrieve user information";
    $result["error_msg"] .= "Problem in insert context broker (Cannot retrieve user information)";
    $result["log"]= "accessLog.php Cannot retrieve user information\r\n";
    my_log($result);
    mysqli_close($link);
    exit();
}

if ($action=="get_log")
{
	$q = "SELECT * FROM access_log"; 
	if($role == 'Root' || $role == 'RootAdmin'){
		$r=create_datatable_data($link,$_REQUEST,$q,"");
	}
	else{
		$r=create_datatable_data($link,$_REQUEST,$q,"accessed_by='$username'");
	}
	
	$selectedrows=-1;
	if($_REQUEST["length"] != -1)
	{
			$start= $_REQUEST['start'];
			$offset=$_REQUEST['length'];
			$tobelimited=true;
	}
	else
	{
		$tobelimited=false;
	}
	 
	if($r) 
	{
	  //$result['status'] = 'ok';
	  //$result['content'] = array();
	  $device = array();
	  $result["log"]= "\r\n action=get_log \r\n";
		 
	 while($row = mysqli_fetch_assoc($r)) 
		{	
			$selectedrows++;
			if (!$tobelimited || ($tobelimited && $selectedrows >= $start && $selectedrows < ($start+$offset)))
			{
				$rec= array();
				$rec["time"]=$row["time"];
				$rec["accessed_by"]=$row["accessed_by"];
				$rec["target_entity_type"]=$row["target_entity_type"];
				$rec["access_type"]=$row["access_type"];
				$rec["entity_name"]=$row["entity_name"];
				$rec["notes"]=$row["notes"];
				$rec["result"]=$row["result"];
				array_push($device, $rec);           
			}
		}
	 $output= format_result($_REQUEST["draw"], $selectedrows+1, $selectedrows+1, $device, "", "\r\n action=get_log \r\n", 'ok');	

	}
	else{
		$output= format_result($_REQUEST["draw"], 0, 0, null, 'Error: errors in reading data about log. <br/>' . generateErrorMessage($link), '\n\r Error: errors in reading data about log.' . generateErrorMessage($link), 'ko');
	}    
	
	my_log($output);
	mysqli_close($link);
}
else 
{
	$result['status'] = 'ko';
	$result['msg'] = 'invalid action ' . $action;
	$result['log'] = 'invalid action ' . $action;
	my_log($result);
}
