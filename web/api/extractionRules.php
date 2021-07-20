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


$result = array("status" => "", "msg" => "", "content" => "", "log" => "", "error_msg" => "");
/* all the primitives return an array "result" with the following structure

result["status"] = ok/ko; reports the status of the operation (mandatory)
result["msg"] a message related to the execution of the operation (optional)
result["content"] in case of positive execution of the operation the content extracted from the db (optional)
result["log"] keep trace of the operations executed on the db

This array should be encoded in json
*/

header("Content-type: application/json");
header("Access-Control-Allow-Origin: *\r\n");
include('../config.php');
include('common.php');
$link = mysqli_connect($host, $username, $password) or die("failed to connect to server !!");
mysqli_select_db($link, $dbname);
error_reporting(E_ERROR | E_NOTICE);

if (!$link->set_charset("utf8")) {
	exit();
}

$node_data = json_decode(file_get_contents("php://input"));
if ($node_data != null) {
	$data_parallel = $node_data->data_parallel;
	$action = $node_data->action;
} else if (isset($_REQUEST['action']) && !empty($_REQUEST['action'])) {
	$action = $_REQUEST['action'];
} else {
	$result['status'] = 'ko';
	$result['msg'] = 'Action not present';
	$result['error_msg'] = 'Action not present';
	$result['log'] = 'extractionRules.php Action not present';
	my_log($result);
	mysqli_close($link);
	exit();
}

$headers = apache_request_headers();
if (isset($headers['Authorization']) && strlen($headers['Authorization']) > 8) {
	$_REQUEST['token'] = substr($headers['Authorization'], 7);
}

require '../sso/autoload.php';

use Jumbojett\OpenIDConnectClient;

$oidc = new OpenIDConnectClient($keycloakHostUri, $clientId, $clientSecret);
$oidc->providerConfigParam(array(
	'token_endpoint'    => $keycloakHostUri . '/auth/realms/master/protocol/openid-connect/token',
	'userinfo_endpoint' => $keycloakHostUri . '/auth/realms/master/protocol/openid-connect/userinfo'
));

$accessToken = "";
if (isset($_REQUEST['nodered'])) {
	if ((isset($_REQUEST['token'])) && ($_REQUEST['token'] != 'undefined'))
		$accessToken = $_REQUEST['token'];
} else {
	if (isset($_REQUEST['token'])) {
		$tkn = $oidc->refreshToken($_REQUEST['token']);
		$accessToken = $tkn->access_token;
	}
}
if (empty($accessToken)) {
	$result["status"] = "ko";
	$result['msg'] = "Access Token not present";
	$result["error_msg"] .= "Access Token not present";
	$result["log"] = "extractionRules.php AccessToken not present\r\n";
	my_log($result);
	mysqli_close($link);
	exit();
}

//retrieve username, organization and role from the accetoken
//TODO avoid passing all the parameters for LDAP
get_user_info($accessToken, $username, $organization, $oidc, $role, $result, $ldapBaseName, $ldapServer, $ldapPort, $ldapAdminName, $ldapAdminPwd);

if ($result["status"] != "ok") {
	$result["status"] = "ko";
	$result['msg'] = "Cannot retrieve user information";
	$result["error_msg"] .= "Cannot retrieve user information";
	$result["log"] = "extractionRules.php Cannot retrieve user information\r\n";
	my_log($result);
	mysqli_close($link);
	exit();
}

if ($action == "insert") {
	$missingParams = missingParameters(array('id', 'contextbroker', 'format', 'selector', 'kind', 'value_type', 'value_unit', 'data_type', 'structure_flag', 'service', 'service_path'));

	if (!empty($missingParams)) {
		$result["status"] = "ko";
		$result['msg'] = "Missing Parameters";
		$result["error_msg"] .= "Problem in insert extraction rules (Missing parameters: " . implode(", ", $missingParams) . " )";
		$result["log"] = "action=insert - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
	} else {
		$id = mysqli_real_escape_string($link, $_REQUEST['id']);
		$contextbroker = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
		$format = mysqli_real_escape_string($link, $_REQUEST['format']);
		$selector = mysqli_real_escape_string($link, $_REQUEST['selector']);
		$kind = mysqli_real_escape_string($link, $_REQUEST['kind']);
		$value_type = mysqli_real_escape_string($link, $_REQUEST['value_type']);
		$value_unit = mysqli_real_escape_string($link, $_REQUEST['value_unit']);
		$data_type = mysqli_real_escape_string($link, $_REQUEST['data_type']);
		$structure_flag = mysqli_real_escape_string($link, $_REQUEST['structure_flag']);
		$service = mysqli_real_escape_string($link, $_REQUEST['service']);
		$service_path = mysqli_real_escape_string($link, $_REQUEST['service_path']);

		$insertquery = "INSERT INTO `extractionRules`(`id`, `contextbroker`, `format`, `selector`, `kind`, `value_type`, `value_unit`, `data_type`, 
			`structure_flag`, `service`,`servicePath`, `organization`, `username`)	VALUES ('$id','$contextbroker','$format', '$selector','$kind','$value_type','$value_unit',
			'$data_type','$structure_flag','$service', '$service_path','$organization','" . md5($username) . "');";

		$r1 = mysqli_query($link, $insertquery);
		if ($r1) {
			$result['status'] = 'ok';
			logAction($link, $username, 'extractionRule', 'insert', $id . " " . $contextbroker, $organization, '', 'success');
		} else {
			$result["status"] = 'ko';
			$result["msg"] .= "faliure";
			$result["log"] .= "\n Problem in inserting rule" . generateErrorMessage($link);
			logAction($link, $username, 'extractionRule', 'insert', $id . " " . $contextbroker, $organization, '', 'faliure');
		}
	}

	my_log($result);
	mysqli_close($link);
}
/*else if ($action=="get_cb_details")
{
	$missingParams=missingParameters(array('contextbroker'));

	if (!empty($missingParams))
	{
		$result["status"]="ko";
        $result['msg'] = "Missing Parameters";
        $result["error_msg"] .= "Problem in get cb detailse (Missing parameters: ".implode(", ",$missingParams)." )";
        $result["log"]= "action=insert - error Missing Parameters: ".implode(", ",$missingParams)." \r\n";
	}
	else 
	{
		$contextbroker = mysqli_real_escape_string($link,$_REQUEST['cb']);
	
		$q = "SELECT * FROM contextbroker WHERE name= '$contextbroker';";
		$r = mysqli_query($link, $q);
		$context = array();
		if($r)
		{
			while($row = mysqli_fetch_assoc($r)) 
	        {
				$rec= array();
				$rec["protocol"]=$row["protocol"];
				$rec["ip"]=$row["ip"];
				$rec["accessLink"]=$row["accesslink"];
				$rec["port"]=$row["port"];
				$rec["protocol"]=$row["protocol"];
				$rec["username"]=md5($username);
				$rec["apikey"]=$row["apikey"];
				$rec["kind"]=$row["kind"];
				$rec["path"]=$row["path"];
				array_push($context, $rec);         
			}	
			$result['status'] = 'ok';
			$result['content'] = $context;		
		}
		else
		{
			$result["status"]='ko';
			$result["msg"] .= "faliure"; 
			$result["log"] .= "\n Problem in get count temporary devices". generateErrorMessage($link); 
		}
	}

	my_log($result);
    mysqli_close($link);
}*/ else if ($action == "get_rules") {
	if (!isset($_REQUEST['length']))	$_REQUEST['length'] = -1;
	if (!isset($_REQUEST['draw'])) $_REQUEST['draw'] = 1;
	if (!isset($_REQUEST['columns'])) $_REQUEST["columns"] = array();
	if (!isset($_REQUEST['select'])) $_REQUEST['select'] = array();


	$q = "SELECT contextbroker, id, selector, kind, format, data_type, value_type, value_unit, structure_flag, service, servicePath FROM extractionRules";
	if ($role == 'ToolAdmin' || $role == 'RootAdmin') {
		$r = create_datatable_data($link, $_REQUEST, $q, "");
	} else {
		$r = create_datatable_data($link, $_REQUEST, $q, "username = '" + md5($username) + "' AND organization='$organization'");
	}
	$selectedrows = -1;
	if ($_REQUEST["length"] != -1) {
		$start = $_REQUEST['start'];
		$offset = $_REQUEST['length'];
		$tobelimited = true;
	} else {
		$tobelimited = false;
	}
	if ($r) {
		$rules = array();
		$result["log"] = "\r\n action=get_rules \r\n";

		while ($row = mysqli_fetch_assoc($r)) {
			$selectedrows++;
			if (!$tobelimited || ($tobelimited && $selectedrows >= $start && $selectedrows < ($start + $offset))) {
				$rec = array();
				$rec["id"] = $row["id"];
				$rec["contextbroker"] = $row["contextbroker"];
				$rec["selector"] = $row["selector"];
				$rec["format"] = $row["format"];
				$rec["kind"] = $row["kind"];
				$rec["data_type"] = $row["data_type"];
				$rec["value_type"] = $row["value_type"];
				$rec["value_unit"] = $row["value_unit"];
				$rec["structure_flag"] = $row["structure_flag"];
				$rec["service"] = $row["service"];
				$rec["servicepath"] = $row["servicePath"];
				array_push($rules, $rec);
			}
		}
		$output = format_result($_REQUEST["draw"], $selectedrows + 1, $selectedrows + 1, $rules, "", "\r\n action=get_rules \r\n", 'ok');
	} else {
		$output = format_result($_REQUEST["draw"], 0, 0, null, 'Error: errors in reading data about  rules. <br/>', generateErrorMessage($link), 'ko');
	}

	my_log($output);
	mysqli_close($link);
} else if ($action == "update") {
	$missingParams = missingParameters(array('id', 'old_id', 'contextbroker', 'old_cb', 'selector', 'format', 'kind', 'data_type', 'value_type', 'value_unit', 'structure_flag', 'service', 'service_path'));

	if (!empty($missingParams)) {
		$result["status"] = "ko";
		$result['msg'] = "Missing Parameters";
		$result["error_msg"] .= "Problem in update extraction rules (Missing parameters: " . implode(", ", $missingParams) . " )";
		$result["log"] = "action=update - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
	} else {
		$id = mysqli_real_escape_string($link, $_REQUEST['id']);
		$old_id = mysqli_real_escape_string($link, $_REQUEST['old_id']);
		$contextbroker = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
		$old_cb = mysqli_real_escape_string($link, $_REQUEST['old_cb']);
		$selector = mysqli_real_escape_string($link, $_REQUEST['selector']);
		$format = mysqli_real_escape_string($link, $_REQUEST['format']);
		$kind = mysqli_real_escape_string($link, $_REQUEST['kind']);
		$data_type = mysqli_real_escape_string($link, $_REQUEST['data_type']);
		$value_type = mysqli_real_escape_string($link, $_REQUEST['value_type']);
		$value_unit = mysqli_real_escape_string($link, $_REQUEST['value_unit']);
		$structure_flag = mysqli_real_escape_string($link, $_REQUEST['structure_flag']);
		$service = mysqli_real_escape_string($link, $_REQUEST['service']);
		$service_path = mysqli_real_escape_string($link, $_REQUEST['service_path']);


		// The following code has to decide the search key in the tables, then the behavior is analogous.
		$q = "UPDATE extractionRules SET id='$id', contextBroker='$contextbroker', selector='$selector', format='$format', kind='$kind', 
			data_type='$data_type', value_type='$value_type', value_unit='$value_unit', service='$service', servicePath='$service_path' , structure_flag='$structure_flag' WHERE id='$old_id' and contextBroker='$old_cb';";
		$r = mysqli_query($link, $q);
		if ($r) {
			$result['status'] = 'ok';
			$result["msg"] .= "update";
			logAction($link, $username, 'extractionRule', 'update', $id . " " . $contextbroker, $organization, '', 'success');
		} else {
			$result["status"] = 'ko';
			$result["msg"] .= "update faliure";
			$result["log"] .= "\n Problem in updating rule $id" . generateErrorMessage($link);
			logAction($link, $username, 'extractionRule', 'update', $id . " " . $contextbroker, $organization, '', 'faliure');
		}
	}

	my_log($result);
	mysqli_close($link);
} else if ($action == "delete_rule") {
	$missingParams = missingParameters(array('id', 'contextbroker'));

	if (!empty($missingParams)) {
		$result["status"] = "ko";
		$result['msg'] = "Missing Parameters";
		$result["error_msg"] .= "Problem in delete extraction rule (Missing parameters: " . implode(", ", $missingParams) . " )";
		$result["log"] = "action=delete_rule - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
	} else {
		$id = mysqli_real_escape_string($link, $_REQUEST['id']);
		$cb = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);

		$query = "DELETE FROM extractionRules  WHERE id = '$id' and contextBroker='$cb'";
		$r = mysqli_query($link, $query);
		if ($r) {
			$result["status"] = 'ok';
			$result["msg"] .= "\n Device $id/$cb and corresponding values correctly removed from temporary devices";
			$result["log"] .= "\n Device $id/$cb and corresponding values correctly removed from temporary devices";
			logAction($link, $username, 'extractionRules', 'delete', $id . " " . $cb, $organization, '', 'success');
		} else {
			$result["status"] = 'ko';
			$result["msg"] .= "\n Problem in deleting the device $id: " . generateErrorMessage($link);
			$result["log"] .= "\n Problem in deleting the device $id: " . $query . " " . generateErrorMessage($link);
			logAction($link, $username, 'extractionRules', 'delete', $id . " " . $cb, $organization, '', 'faliure');
		}
	}

	my_log($result);
	mysqli_close($link);
} else {
	$result['status'] = 'ko';
	$result['msg'] = 'invalid action ' . $action;
	$result['log'] = 'invalid action ' . $action;
	my_log($result);
}
