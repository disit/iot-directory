
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

if (isset($_REQUEST['action']) && !empty($_REQUEST['action'])) {
	$action = $_REQUEST['action'];
} else {
	$result['status'] = 'ko';
	$result['msg'] = 'action not present';
	$result['error_msg'] = 'action not present';
	$result['log'] = 'device.php action not present';
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
	$result["log"] = "model.php AccessToken not present\r\n";
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
	$result["error_msg"] .= "Problem in insert context broker (Cannot retrieve user information)";
	$result["log"] = "action=insert - error Cannot retrieve user information\r\n";
	my_log($result);
	mysqli_close($link);
	exit();
}

if ($action == "insert") {
	$missingParams = missingParameters(array('contextbroker', 'device', 'value_name', 'data_type', 'value_type', 'editable', 'value_unit', 'healthiness_criteria', 'healthiness_value'));

	if (!empty($missingParams)) {
		$result["status"] = "ko";
		$result['msg'] = "Missing Parameters";
		$result["error_msg"] .= "Problem in insert value (Missing parameters: " . implode(", ", $missingParams) . " )";
		$result["log"] = "action=insert - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
	} else {
		$cb = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
		$device = mysqli_real_escape_string($link, $_REQUEST['device']);
		$value_name = mysqli_real_escape_string($link, $_REQUEST['value_name']);
		$data_type = mysqli_real_escape_string($link, $_REQUEST['data_type']);
		$value_type = mysqli_real_escape_string($link, $_REQUEST['value_type']);
		$editable = mysqli_real_escape_string($link, $_REQUEST['editable']);
		if (empty($editable)) $editable = "0";
		$value_unit = mysqli_real_escape_string($link, $_REQUEST['value_unit']);
		$healthiness_criteria = mysqli_real_escape_string($link, $_REQUEST['healthiness_criteria']);
		$healthiness_value = mysqli_real_escape_string($link, $_REQUEST['healthiness_value']);
		if (empty($healthiness_value)) $healthiness_value = "0";

		$hc = "";
		if ($healthiness_criteria == "refresh_rate") $hc = "value_refresh_rate";
		else if ($healthiness_criteria == "different_values") $hc = "different_values";
		else $hc = "value_bounds";

		$protocol = getProtocol($cb, $link);

		if (empty($protocol)) //it also ensure the contextbroker name is valid
		{
			$result["status"] = "ko";
			$result['msg'] = "Unrecognized contextbroker/protocol";
			$result["error_msg"] .= "Problem in insert value (Unrecognized contextbroker/protocol)";
			$result["log"] = "action=insert - error Unrecognized contextbroker/protocol\r\n";
		} else {
			//get device ---- it also assure device existence
			$q  = "SELECT * FROM devices WHERE id = '$device' and contextBroker = '$cb'";
			$r = mysqli_query($link, $q);
			if ((!$r) || (count(mysqli_fetch_assoc($r)) == 0)) {
				$result["status"] = "ko";
				$result['msg'] = "Unrecognized device";
				$result["error_msg"] .= "Problem in getting device (Unrecognized device)";
				$result["log"] = "action=insert - error Unrecognized device\r\n";
			} else {
				$q = "INSERT INTO event_values(cb, device, value_name, data_type, value_type, editable, value_unit, healthiness_criteria, `$hc`) " .
					"VALUES('$cb', '$device', '$value_name',  '$data_type', '$value_type', '$editable', '$value_unit', '$healthiness_criteria', '$healthiness_value')"; //, '$order' )";
				$r = mysqli_query($link, $q);

				if ($r) {
					$result["log"] .= "\r\n Value $cb/$device/$value_name correctly inserted \r\n";
					modify_valueKB($link, $device, $cb, $organization, retrieveKbUrl($organizationApiURI, $organization), $result);
					$result["editable"] = $editable;
					if ($result["content"] == null) $result["active"] = false;
					else $result["active"] = true;
					$result["msg"] .= '\n insertion in the db of the value was ok';
					if (!isset($result["status"])) {
						logAction($link, $username, 'event_values', 'insert', $device . " " . $cb . " " . $value_name, $organization, '', 'success');
						$result["status"] = "ok";
					} else if ($result["status"] == "ko") {
						logAction($link, $username, 'event_values', 'insert', $device . " " . $cb . " " . $value_name, $organization, 'Error occurred in the KB', 'faliure');
						$result["msg"] .= '\n an error occurred in the KB or context broker';
						$result["log"] .= '\n an error occurred in the KB or context broker';
					} else if ($result["status"] == "ok") {
						logAction($link, $username, 'event_values', 'insert', $device . " " . $cb . " " . $value_name, $organization, '', 'success');
					}
				} else {
					logAction($link, $username, 'event_values', 'insert', $device . " " . $cb . " " . $value_name, $organization, 'Error occurred registering the value', 'faliure');
					$result["status"] = 'ko';
					$result["msg"] = "Error: An error occurred when registering the value	$value_name. <br/>" .   generateErrorMessage($link) .   '<br/> Please enter again the value_name';
					$result["log"] = "\n\r Error: An error occurred when registering the value	 $value_name. <br/>" .   generateErrorMessage($link) .   '<br/> Please enter again the value_name';
				}
			}
		}
	}

	my_log($result);
	mysqli_close($link);
} else if ($action == "update") {
	$missingParams = missingParameters(array('contextbroker', 'device', 'value_name', 'data_type', 'value_type', 'editable', 'value_unit', 'healthiness_criteria', 'healthiness_value'));

	if (!empty($missingParams)) {
		$result["status"] = "ko";
		$result['msg'] = "Missing Parameters";
		$result["error_msg"] .= "Problem update the value information (Missing parameters: " . implode(", ", $missingParams) . " )";
		$result["log"] = "action=update - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
	} else {
		$cb = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
		$device = mysqli_real_escape_string($link, $_REQUEST['device']);
		$value_name = mysqli_real_escape_string($link, $_REQUEST['value_name']);
		$data_type = mysqli_real_escape_string($link, $_REQUEST['data_type']);
		$value_type = mysqli_real_escape_string($link, $_REQUEST['value_type']);
		$editable = mysqli_real_escape_string($link, $_REQUEST['editable']);
		$value_unit = mysqli_real_escape_string($link, $_REQUEST['value_unit']);
		$healthiness_criteria = mysqli_real_escape_string($link, $_REQUEST['healthiness_criteria']);
		$healthiness_value = mysqli_real_escape_string($link, $_REQUEST['healthiness_value']);

		$hc = "";
		if ($healthiness_criteria == "refresh_rate") $hc = "value_refresh_rate";
		else if ($healthiness_criteria == "different_values") $hc = "different_values";
		else $hc = "value_bounds";

		$protocol = getProtocol($cb, $link);

		if (empty($protocol)) //it also ensure the contextbroker name is valid
		{
			$result["status"] = "ko";
			$result['msg'] = "Unrecognized contextbroker/protocol";
			$result["error_msg"] .= "Problem in update value (Unrecognized contextbroker/protocol)";
			$result["log"] = "action=update - error Unrecognized contextbroker/protocol\r\n";
		} else {
			get_device($username, $role, $device, $cb,  $accessToken, $link, $result);

			if (empty($result["content"])) {
				$result["status"] = "ko";
				$result['msg'] = "Unrecognized device";
				$result["error_msg"] .= "Problem in update value (Unrecognized device)";
				$result["log"] = "action=update - error Unrecognized device\r\n";
			} else {
				$dev_organization = $result["content"]["organization"];
				$eId = $dev_organization . ":" . $cb . ":" . $device;

				if (!enforcementRights($username, $accessToken, $role, $eId, 'IOTID', 'write', $result)) {
					$result["status"] = "ko";
					$result['msg'] = "Not ownership or enough right to update";
					$result["error_msg"] .= "Problem in update value (Not ownership or enough right to update)";
					$result["log"] .= "action=update - error Not ownership or enough right to update\r\n";
				} else {
					$q = "UPDATE event_values SET cb = '$cb', device = '$device', value_name = '$value_name', data_type = '$data_type', value_type = '$value_type', 
						editable = '$editable', value_unit = '$value_unit', healthiness_criteria = '$healthiness_criteria', $hc = '$healthiness_value' 
						WHERE cb = '$cb' and device='$device' and value_name='$value_name'";
					$r = mysqli_query($link, $q);

					if ($r) {
						modify_valueKB($link, $device, $cb, $organization, retrieveKbUrl($organizationApiURI, $organization), $result);
						$result["editable"] = $editable;
						if ($result["content"] == null) $result["active"] = false;
						else $result["active"] = true;
						$result["msg"] .= '\n update in the db of the value was ok';
						$result["log"] .= "\r\n Value $cb/$device/$value_name correctly updated";
						if (!isset($result["status"])) {
							logAction($link, $username, 'event_values', 'update', $device . " " . $cb . " " . $value_name, $organization, '', 'success');
							$result["status"] = "ok";
						} else {
							if ($result["status"] == "ko") {
								logAction($link, $username, 'event_values', 'update', $device . " " . $cb . " " . $value_name, $organization, 'Error occurred in the KB', 'faliure');
								$result["msg"] .= '\n an error occurred in the KB or context broker';
								$result["log"] .= '\n an error occurred in the KB or context broker';
							}
							if ($result["status"] == "ok") {
								logAction($link, $username, 'event_values', 'update', $device . " " . $cb . " " . $value_name, $organization, '', 'success');
							}
						}
					} else {
						logAction($link, $username, 'event_values', 'update', $device . " " . $cb . " " . $value_name, $organization, '', 'faliure');
						$result["status"] = 'ko';
						$result["msg"] = 'event_values <b>' . $value_name . '</b> &nbsp; update failed, ' .  generateErrorMessage($link) . ' Please enter again.';
						$result["log"] = '\n\r event_values <b>' . $value_name . '</b> &nbsp; update failed, ' .  generateErrorMessage($link);
					}
				}
			}
		}
	}

	my_log($result);
	mysqli_close($link);
} else if ($action == "delete") {
	$missingParams = missingParameters(array('contextbroker', 'device', 'value_name', 'editable'));

	if (!empty($missingParams)) {
		$result["status"] = "ko";
		$result['msg'] = "Missing Parameters";
		$result["error_msg"] .= "Problem delete the value information (Missing parameters: " . implode(", ", $missingParams) . " )";
		$result["log"] = "action=delete - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
	} else {
		$cb = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
		$device = mysqli_real_escape_string($link, $_REQUEST['device']);
		$value_name = mysqli_real_escape_string($link, $_REQUEST['value_name']);
		$editable = mysqli_real_escape_string($link, $_REQUEST['editable']);

		$protocol = getProtocol($cb, $link);

		if (empty($protocol)) //it also ensure the contextbroker name is valid
		{
			$result["status"] = "ko";
			$result['msg'] = "Unrecognized contextbroker/protocol";
			$result["error_msg"] .= "Problem in delete value (Unrecognized contextbroker/protocol)";
			$result["log"] = "action=delete - error Unrecognized contextbroker/protocol\r\n";
		} else {
			get_device($username, $role, $device, $cb,  $accessToken, $link, $result);

			if (empty($result["content"])) {
				$result["status"] = "ko";
				$result['msg'] = "Unrecognized device";
				$result["error_msg"] .= "Problem in update value (Unrecognized device)";
				$result["log"] = "action=update - error Unrecognized device\r\n";
			} else {
				$dev_organization = $result["content"]["organization"];
				$eId = $dev_organization . ":" . $cb . ":" . $device;

				if (!enforcementRights($username, $accessToken, $role, $eId, 'IOTID', 'write', $result)) {
					$result["status"] = "ko";
					$result['msg'] = "Not ownership or enough right to update";
					$result["error_msg"] .= "Problem in update value (Not ownership or enough right to update)";
					$result["log"] .= "action=update - error Not ownership or enough right to update\r\n";
				} else {

					$q = "DELETE FROM event_values WHERE cb = '$cb' and device='$device' and value_name='$value_name'";
					$r = mysqli_query($link, $q);
					if ($r) {
						modify_valueKB($link, $device, $cb, $organization, retrieveKbUrl($organizationApiURI, $organization), $result);
						$result["editable"] = $editable;
						if ($result["content"] == null) $result["active"] = false;
						else $result["active"] = true;
						$result["msg"] .= '\n delete in the db of the value was ok';
						$result["log"] .= '\n delete in the db of the value was ok';
						if (!isset($result["status"])) {
							logAction($link, $username, 'event_values', 'delete', $device . " " . $cb . " " . $value_name, $organization, '', 'success');
							$result["status"] = "ok";
						} else {
							if ($result["status"] == "ko") {
								logAction($link, $username, 'event_values', 'delete', $device . " " . $cb . " " . $value_name, $organization, 'Error occurred in the KB', 'faliure');
								$result["error_msg"] .= 'An error occurred in the KB or context broker. ';
								$result["msg"] .= '\n an error occurred in the KB or context broker';
								$result["log"] .= '\n an error occurred in the KB or context broker';
							}
							if ($result["status"] == "ok") {
								logAction($link, $username, 'event_values', 'delete', $device . " " . $cb . " " . $value_name, $organization, '', 'success');
							}
						}
					} else {
						logAction($link, $username, 'event_values', 'delete', $device . " " . $cb . " " . $value_name, $organization, '', 'faliure');
						$result["status"] = 'ko';
						$result["error_msg"] = 'Event_values ' . $value_name . ': deletion failed, ';
						$result["msg"] = 'event_values <b>' . $value_name . '</b> &nbsp; deletion failed, ' . generateErrorMessage($link) .  ' Please enter again.';
						$result["log"] = '\n\r event_values ' . $value_name . ' deletion failed, ' .    generateErrorMessage($link);
					}
				}
			}
		}
	}

	my_log($result);
	mysqli_close($link);
} else if ($action == "check_if_last_value") {
	$missingParams = missingParameters(array('contextbroker', 'device'));

	if (!empty($missingParams)) {
		$result["status"] = "ko";
		$result['msg'] = "Missing Parameters";
		$result["error_msg"] .= "Problem in check last value (Missing parameters: " . implode(", ", $missingParams) . " )";
		$result["log"] = "action=check_if_last_value - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
	} else {
		$cb = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
		$device = mysqli_real_escape_string($link, $_REQUEST['device']);

		$protocol = getProtocol($cb, $link);

		if (empty($protocol)) //it also ensure the contextbroker name is valid
		{
			$result["status"] = "ko";
			$result['msg'] = "Unrecognized contextbroker/protocol";
			$result["error_msg"] .= "Problem in check value (Unrecognized contextbroker/protocol)";
			$result["log"] = "action=check_if_last_value - error Unrecognized contextbroker/protocol\r\n";
		} else {
			//get device ---- it also assure device existence
			$q  = "SELECT * FROM devices WHERE id = '$device' and contextBroker = '$cb'";
			$r = mysqli_query($link, $q);
			if ((!$r) || (count(mysqli_fetch_assoc($r)) == 0)) {
				$result["status"] = "ko";
				$result['msg'] = "Unrecognized device";
				$result["error_msg"] .= "Problem in getting device (Unrecognized device)";
				$result["log"] = "action=update - error Unrecognized device\r\n";
			} else {
				$q = "SELECT * FROM event_values WHERE cb = '$cb' and device='$device'";
				$r = mysqli_query($link, $q);
				if ($r) {
					$rowcount = mysqli_num_rows($r);
					logAction($link, $username, 'event_values', 'check_if_last_value', $device, $organization, '', 'success');
					$result["status"] = "ok";
					$result["content"] = $rowcount;
				} else {
					logAction($link, $username, 'event_values', 'check_if_last_value', $device, $organization, '', 'faliure');
					$result["status"] = 'ko';
					$result["error_msg"] = 'Event_values ' . $device . ': check_if_last_value failed, ';
					$result["msg"] = 'event_values <b>' . $device . '</b> &nbsp; check_if_last_value failed, ' . generateErrorMessage($link) .      ' Please enter again.';
					$result["log"] = '\n\r event_values ' . $device . ' check_if_last_value failed, ' .    generateErrorMessage($link);
				}
			}
		}
	}

	my_log($result);
	mysqli_close($link);
}/*
else if($action == 'get_event_value') 
{
	$cb = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
	$device = mysqli_real_escape_string($link, $_REQUEST['device']);
	$value_name = mysqli_real_escape_string($link, $_REQUEST['value_name']);
	$organization = mysqli_real_escape_string($link, $_REQUEST['organization']);
	
	$q = "SELECT * FROM event_values WHERE cb = '$cb' and device='$device' and value_name='$value_name'";
	$r = mysqli_query($link, $q);

	if($r) 
	{
		$row = mysqli_fetch_assoc($r);
		$result['log'] .= "\n\r action:get_event_value. ok " . $q;
		$result['status'] = 'ok';
		$result['content'] = $row;
	} 
	else
	{
		$result['status'] = 'ko';
		$result['msg'] = "Error: reading data for the event_value $value_name. <br/>" .
						   generateErrorMessage($link);
		$result['log'] = "\n\r Error: reading data for the event_value $value_name. <br/>" .
						   generateErrorMessage($link);				   
	}
	 my_log($result);
	mysqli_close($link); 
}*/ else if ($action == 'get_all_event_value') {
	if (isset($_REQUEST['length']))	$length = mysqli_real_escape_string($link, $_REQUEST['length']);
	else $length = -1;
	$start = 1; //default is 1 but should throw an error
	if (($length != -1) && (isset($_REQUEST['start']))) $start = mysqli_real_escape_string($link, $_REQUEST['start']);
	if (isset($_REQUEST['draw'])) $draw = mysqli_real_escape_string($link, $_REQUEST['draw']);
	else $draw = 1;
	if (!isset($_REQUEST['columns'])) $_REQUEST["columns"] = array();
	if (isset($_REQUEST['select'])) $selection = json_decode($_REQUEST['select']);
	else $selection = array();

	getOwnerShipDevice($accessToken, $result);
	getDelegatedDevice($accessToken, $username, $result);

	$q = "SELECT cb.sha,cb.accesslink, cb.accessport, v.*, d.kind, d.contextbroker, d.latitude, d.longitude, 
			d.visibility, d.devicetype, d.uri, d.created, d.privatekey, d.organization, d.certificate,
			d.visibility, d.`protocol`, d.`service`, d.`servicePath`, 
			CASE WHEN d.mandatoryproperties AND d.mandatoryvalues THEN \"active\" ELSE \"idle\" END	AS status1 
			FROM event_values v JOIN devices d ON (v.device=d.id AND d.contextbroker=v.cb) JOIN contextbroker cb ON (v.cb=cb.name )";

	if (count($selection) != 0) {
		$a = 0;
		$cond = "";
		while ($a < count($selection)) {
			$sel = $selection[$a];
			$cond .= " (device='" . $sel->id . "' AND cb = '" . $sel->cb . "' AND value_name= '" . $sel->value_name . "') ";
			if ($a != count($selection) - 1)  $cond .= " OR ";
			$a++;
		}
		$r = create_datatable_data($link, $_REQUEST, $q, "deleted IS null AND (" . $cond . ")");
	} else {
		$r = create_datatable_data($link, $_REQUEST, $q, "deleted IS null");
	}

	$selectedrows = -1;
	if ($length != -1) {
		$offset = $_REQUEST['length'];
		$tobelimited = true;
	} else {
		$tobelimited = false;
	}

	if ($r) {
		$data = array();

		while ($row = mysqli_fetch_assoc($r)) {
			$eid = $row["organization"] . ":" . $row["contextbroker"] . ":" . $row["device"];
			if (($role == "RootAdmin") ||
				((
					(
						($row["organization"] == $organization) &&
						(
							($row["visibility"] == 'public'
								||
								(isset($row["uri"]) && $row["uri"] != "" &&
									isset($result["delegation"][$row["uri"] . "/" . $row["value_name"]])) && $result["delegation"][$row["uri"] . "/" . $row["value_name"]]["kind"] == "anonymous"))))
					||
					(isset($row["uri"]) && $row["uri"] != "" &&
						((isset($result["delegation"][$eid]) && $result["delegation"][$eid]["kind"] != "anonymous")
							||
							(isset($result["delegation"][$row["uri"] . "/" . $row["value_name"]])) && $result["delegation"][$row["uri"] . "/" . $row["value_name"]]["kind"] != "anonymous"))
					||
					(isset($result["keys"][$eid])))
			) {
				$selectedrows++;
				if (!$tobelimited || ($tobelimited && $selectedrows >= $start && $selectedrows < ($start + $offset))) {
					$rec = array();
					$rec["cb"] = $row["cb"];
					$rec["device"] = $row["device"];
					$rec["devicetype"] = $row["devicetype"];
					$rec["value_name"] = $row["value_name"];
					$rec["data_type"] = $row["data_type"];
					$rec["value_type"] = $row["value_type"];
					$rec["editable"] = $row["editable"];
					$rec["value_unit"] = $row["value_unit"];
					$rec["healthiness_criteria"] = $row["healthiness_criteria"];
					$rec["order"] = $row["order"];
					$rec["value_refresh_rate"] = $row["value_refresh_rate"];
					$rec["organization"] = $row["organization"];
					$rec["latitude"] = $row["latitude"];
					$rec["longitude"] = $row["longitude"];
					$rec["kind"] = $row["kind"];
					$rec["uri"] = $row["uri"];
					$rec["status1"] = $row["status1"];
					$rec["created"] = $row["created"];
					$rec["privatekey"] = $row["privatekey"];
					$rec["certificate"] = $row["certificate"];
					$rec["sha"] = $row["sha"];
					$rec["accesslink"] = $row["accesslink"];
					$rec["accessport"] = $row["accessport"];
					$rec["service"] = $row["service"];
					$rec["servicePath"] = $row["servicePath"];
					if ($row["protocol"] == "ngsi w/MultiService") {
						$rec["device"] = explode(".", $row["device"])[2];
					}

					if (isset($result["keys"][$eid])) { //it's mine
						if ((isset($result["delegation"][$row["uri"] . "/" . $row["value_name"]])
								&& $result["delegation"][$row["uri"] . "/" . $row["value_name"]]["kind"] == "anonymous")
							||
							(isset($result["delegation"][$eid])
								&& $result["delegation"][$eid]["kind"] == "anonymous")
						)
							$rec["visibility"] = "MyOwnPublic";
						else
							$rec["visibility"] = "MyOwnPrivate";

						$rec["k1"] = $result["keys"][$eid]["k1"];
						$rec["k2"] = $result["keys"][$eid]["k2"];
					} else { //it's not mine
						if ((isset($result["delegation"][$row["uri"] . "/" . $row["value_name"]])
								&& ($result["delegation"][$row["uri"] . "/" . $row["value_name"]]["kind"] == "anonymous"))
							||
							(isset($result["delegation"][$eid])
								&& ($result["delegation"][$eid]["kind"] == "anonymous"))
						) { //it's delegated as public
							$rec["visibility"] = 'public';
							$rec["k1"] = "";
							$rec["k2"] = "";
						} else if ((isset($result["delegation"][$row["uri"] . "/" . $row["value_name"]]))
							||
							(isset($result["delegation"][$eid]))
						) { //it's delegated personally
							$rec["visibility"] = 'delegated';
							$rec["k1"] = "";
							$rec["k2"] = "";

							if (isset($result["delegation"][$row["uri"] . "/" . $row["value_name"]]["k1"])) {
								$rec["k1"] = $result["delegation"][$row["uri"] . "/" . $row["value_name"]]["k1"]; // to be fixed 
								$rec["k2"] = $result["delegation"][$row["uri"] . "/" . $row["value_name"]]["k2"]; // to be fixed 
							} else if (isset($result["delegation"][$eid]["k1"])) {
								$rec["k1"] = $result["delegation"][$eid]["k1"]; // to be fixed 
								$rec["k2"] = $result["delegation"][$eid]["k2"]; // to be fixed 
							}
						} else { //not mine, not delegated
							$rec["visibility"] = $row["visibility"];
							$rec["k1"] = "";
							$rec["k2"] = "";
						}
					}
					array_push($data, $rec);
				}
			}
		}
		$output = format_result($draw, $selectedrows + 1, $selectedrows + 1, $data, "", "\r\n action=get_all_event_value \r\n", 'ok');
	} else {
		$output = format_result($draw, 0, 0, null, 'Error: errors in reading data about values. <br/>' .
			generateErrorMessage($link), '\n\r Error: errors in reading data about values.' . generateErrorMessage($link), 'ko');
	}

	my_log($output);
	mysqli_close($link);
}
//TODO can be unified with get_all_event_value)
else if ($action == 'get_all_event_value_admin') {
	//encforce RootAdmin
	if ($role != "RootAdmin") {
		$result["status"] = "ko";
		$result['msg'] = "Role mismatch";
		$result["error_msg"] .= "Problem in get values (Role mismatch)";
		$result["log"] = "action=get_all_event_value_admin - error Role mismatch\r\n";
	} else {
		if (isset($_REQUEST['length'])) $length = mysqli_real_escape_string($link, $_REQUEST['length']);
		else $length = -1;
		$start = 1; //default is 1 but should throw an error
		if (($length != -1) && (isset($_REQUEST['start']))) $start = mysqli_real_escape_string($link, $_REQUEST['start']);
		if (isset($_REQUEST['draw'])) $draw = mysqli_real_escape_string($link, $_REQUEST['draw']);
		else $draw = 1;
		if (!isset($_REQUEST['columns'])) $_REQUEST["columns"] = array();
		if (isset($_REQUEST['select'])) $selection = json_decode($_REQUEST['select']);
		else $selection = array();

		$ownDevices = getOwnerShipDevice($accessToken, $result);
		getDelegatedDevice($accessToken, $username, $result);

		$q = "SELECT v.*, d.`kind`, d.`latitude`, d.`longitude`, d.`organization`, d.`devicetype`, d.`visibility`, d.`uri`,d.`created`, cb.sha, 
			cb.accesslink, cb.accessport, d.protocol, d.`service`, d.`servicePath`, 
		 	CASE WHEN d.mandatoryproperties AND d.mandatoryvalues THEN \"active\" ELSE \"idle\" END 
		 	AS status1, d.`visibility` FROM `event_values` v JOIN `devices` d ON (v.device=d.id AND d.contextbroker=v.cb) JOIN contextbroker cb ON (v.cb=cb.name )";

		if (count($selection) != 0) {
			$a = 0;
			$cond = "";
			while ($a < count($selection)) {
				$sel = $selection[$a];
				$cond .= " (device='" . $sel->id . "' AND cb = '" . $sel->cb . "' AND value_name= '" . $sel->value_name . "') ";
				if ($a != count($selection) - 1)  $cond .= " OR ";
				$a++;
			}
			$r = create_datatable_data($link, $_REQUEST, $q, "deleted IS null AND (" . $cond . ")");
		} else {
			$r = create_datatable_data($link, $_REQUEST, $q, "deleted IS null");
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

			logAction($link, $username, 'event_value', 'get_all_event_value_admin', '', $organization, '', 'success');
			$data = array();

			while ($row = mysqli_fetch_assoc($r)) {
				$selectedrows++;
				if (!$tobelimited || ($tobelimited && $selectedrows >= $start && $selectedrows < ($start + $offset))) {
					$rec = array();
					$rec["cb"] = $row["cb"];
					$rec["device"] = $row["device"];
					$rec["devicetype"] = $row["devicetype"];
					$rec["value_name"] = $row["value_name"];
					$rec["data_type"] = $row["data_type"];
					$rec["value_type"] = $row["value_type"];
					$rec["editable"] = $row["editable"];
					$rec["value_unit"] = $row["value_unit"];
					$rec["healthiness_criteria"] = $row["healthiness_criteria"];
					$rec["order"] = $row["order"];
					$rec["value_refresh_rate"] = $row["value_refresh_rate"];
					$rec["organization"] = $row["organization"];
					$rec["latitude"] = $row["latitude"];
					$rec["longitude"] = $row["longitude"];
					$rec["kind"] = $row["kind"];
					$rec["uri"] = $row["uri"];
					$rec["status1"] = $row["status1"];
					$rec["sha"] = $row["status1"];
					$rec["accesslink"] = $row["accesslink"];
					$rec["accessport"] = $row["accessport"];
					$rec["created"] = $row["created"];
					$rec["service"] = $row["service"];
					$rec["servicePath"] = $row["servicePath"];
					if ($row["protocol"] == "ngsi w/MultiService") {
						$rec["device"] = explode(".", $row["device"])[2];
					}
					$eid = $row["organization"] . ":" . $row["cb"] . ":" . $row["device"];

					if (isset($result["keys"][$eid]) && $result["keys"][$eid]["owner"] == $username) {
						if ($row["visibility"] == "public") $rec["visibility"] = "MyOwnPublic";
						else {
							if (
								isset($result["delegation"][$row["uri"] . "/" . $row["value_name"]])
								&& $result["delegation"][$row["uri"] . "/" . $row["value_name"]]["kind"] == "anonymous"
							)
								$rec["visibility"] = "MyOwnPublic";
							else
								$rec["visibility"] = "MyOwnPrivate";
						}
						$rec["k1"] = $result["keys"][$eid]["k1"];
						$rec["k2"] = $result["keys"][$eid]["k2"];
					} else { //it's not mine
						if (
							isset($result["delegation"][$row["uri"] . "/" . $row["value_name"]])
							&& ($result["delegation"][$row["uri"] . "/" . $row["value_name"]]["kind"] == "anonymous")
						) { //it's delegated as public
							$rec["visibility"] = 'public';
							$rec["k1"] = "";
							$rec["k2"] = "";
						} else if (isset($result["delegation"][$row["uri"] . "/" . $row["value_name"]])) { //it's delegated personally
							$rec["visibility"] = 'delegated';
							$rec["k1"] = "";
							$rec["k2"] = "";

							if (isset($result["delegation"][$row["uri"] . "/" . $row["value_name"]]["k1"])) {
								$rec["k1"] = $result["delegation"][$row["uri"] . "/" . $row["value_name"]]["k1"]; // to be fixed
								$rec["k2"] = $result["delegation"][$row["uri"] . "/" . $row["value_name"]]["k2"]; // to be fixed
							}
						} else { //other
							$rec["visibility"] = $row["visibility"]; // =="public")?"MyOwnPublic":"MyOwnPrivate";
							$rec["k1"] = "";
							$rec["k2"] = "";
							$rec["owner"] = "";
						}
					}
					array_push($data, $rec);
				}
			}
			$output = format_result($_REQUEST["draw"], $selectedrows + 1, $selectedrows + 1, $data, "", "\r\n action=get_all_event_value_admin \r\n", 'ok');
		} else {
			logAction($link, $username, 'event_value', 'get_all_event_value_admin', '', $organization, 'Error: errors in reading data about values.', 'faliure');
			$output = format_result(
				$_REQUEST["draw"],
				0,
				0,
				null,
				'Error: errors in reading data about values. <br/>' . generateErrorMessage($link),
				'\n\r Error: errors in reading data about values.' . generateErrorMessage($link),
				'ko'
			);
		}
	}

	my_log($output);
	mysqli_close($link);
}
//TODO can be unified with get_all_event_value)
else if ($action == 'get_all_private_event_value') {
	$ownDevices = getOwnerShipDevice($accessToken, $result);
	getDelegatedDevice($accessToken, $username, $result);

	if ($ownDevices != "") {
		$q = "SELECT v.*, d.`id`, d.`kind`, d.`contextbroker`,  d.`latitude`, d.`longitude`, d.`devicetype`,  d.`organization`, d.`visibility`, 
			d.`uri`,d.`model`, d.`created`, d.`privatekey`, d.`certificate`, cb.`sha`,cb.`accesslink`, cb.`accessport`, d.`protocol`, d.`service`, d.`servicePath`,
	      	CASE WHEN d.mandatoryproperties AND d.mandatoryvalues THEN \"active\" ELSE \"idle\" END AS status1,
	      	d.`visibility` FROM `event_values` v JOIN `devices` d ON (v.device=d.id AND d.contextbroker=v.cb) JOIN `contextbroker` cb ON (v.cb=cb.name )";

		$r = create_datatable_data($link, $_REQUEST, $q, "deleted IS null");
		$selectedrows = -1;

		if ($_REQUEST["length"] != -1) {
			$start = $_REQUEST['start'];
			$offset = $_REQUEST['length'];
			$tobelimited = true;
		} else {
			$tobelimited = false;
		}

		if ($r) {
			logAction($link, $username, 'event_value', 'get_all_private_event_value', '', $organization, '', 'success');

			$data = array();

			while ($row = mysqli_fetch_assoc($r)) {
				$eid = $row["organization"] . ":" . $row["contextbroker"] . ":" . $row["id"];
				if ((isset($result["keys"][$eid])
					&& ((($result["keys"][$eid]["owner"] == $username) && ($role === 'RootAdmin'))
						||
						($role != 'RootAdmin')))) {
					$selectedrows++;
					if (!$tobelimited || ($tobelimited && $selectedrows >= $start && $selectedrows < ($start + $offset))) {
						$rec = array();
						$rec["id"] = $row["id"];
						$rec["cb"] = $row["cb"];
						$rec["uri"] = $row["uri"];
						$rec["model"] = $row["model"];
						$rec["device"] = $row["device"];
						$rec["devicetype"] = $row["devicetype"];
						$rec["value_name"] = $row["value_name"];
						$rec["data_type"] = $row["data_type"];
						$rec["value_type"] = $row["value_type"];
						$rec["editable"] = $row["editable"];
						$rec["value_unit"] = $row["value_unit"];
						$rec["healthiness_criteria"] = $row["healthiness_criteria"];
						$rec["order"] = $row["order"];
						$rec["value_refresh_rate"] = $row["value_refresh_rate"];
						$rec["latitude"] = $row["latitude"];
						$rec["longitude"] = $row["longitude"];
						$rec["organization"] = $row["organization"];
						$rec["kind"] = $row["kind"];
						$rec["status1"] = $row["status1"];
						$rec["created"] = $row["created"];
						$rec["privatekey"] = $row["privatekey"];
						$rec["certificate"] = $row["certificate"];
						$rec["sha"] = $row["sha"];
						$rec["accesslink"] = $row["accesslink"];
						$rec["accessport"] = $row["accessport"];
						$rec["protocol"] = $row["protocol"];
						$rec["service"] = $row["service"];
						$rec["servicePath"] = $row["servicePath"];
						if ($row["protocol"] == "ngsi w/MultiService") {
							$rec["device"] = explode(".", $row["device"])[2];
							$rec["id"] = explode(".", $row["id"])[2];
						}

						if ((isset($result["delegation"][$row["uri"] . "/" . $row["value_name"]])
								&& $result["delegation"][$row["uri"] . "/" . $row["value_name"]]["kind"] == "anonymous")
							||
							(isset($result["delegation"][$eid])
								&& $result["delegation"][$eid]["kind"] == "anonymous")
						)
							$rec["visibility"] = "MyOwnPublic";
						else
							$rec["visibility"] = "MyOwnPrivate";

						if (isset($result["keys"][$rec["device"]])) {
							$rec["k1"] = $result["keys"][$rec["device"]]["k1"];
							$rec["k2"] = $result["keys"][$rec["device"]]["k2"];
							$rec["edgegateway_type"] = $result["keys"][$rec["device"]]["edgegateway_type"];
							$rec["edgegateway_uri"] = $result["keys"][$rec["device"]]["edgegateway_uri"];
						} else {
							$rec["k1"] = $result["keys"][$eid]["k1"];
							$rec["k2"] = $result["keys"][$eid]["k2"];
							$rec["edgegateway_type"] = $result["keys"][$eid]["edgegateway_type"];
							$rec["edgegateway_uri"] = $result["keys"][$eid]["edgegateway_uri"];
						}
						array_push($data, $rec);
					}
				}
			}
			$output = format_result($_REQUEST["draw"], $selectedrows + 1, $selectedrows + 1, $data, "", "\r\n action=get_all_private_event_value \r\n", 'ok');
		} else {
			logAction($link, $username, 'event_value', 'get_all_private_event_value', '', $organization, 'Error: errors in reading data about devices.', 'faliure');
			$output = format_result($_REQUEST["draw"], 0, 0, null, 'Error: errors in reading data about devices. <br/>' . generateErrorMessage($link), '\n\r Error: errors in reading data about devices.' . generateErrorMessage($link), 'ko');
		}
	} else {
		$output = format_result($_REQUEST["draw"], 0, 0, array(), "", "\r\n action=get_all_private_event_value \r\n", 'ok');
	}

	my_log($output);
	mysqli_close($link);
}
//TODO can be unified with get_all_event_value)
else if ($action == 'get_all_delegated_event_value') {
	getDelegatedDevice($accessToken, $username, $result);

	$q = "SELECT v.*, d.`id`,d.`contextbroker`, d.`kind`, d.`latitude`, d.`longitude`, d.`devicetype`,  d.`organization`, d.`visibility`, 
			d.`uri`,d.`model`, d.`created` ,cb.`accesslink`, cb.`accessport`, d.`protocol`, d.`service`, d.`servicePath`, 
			CASE WHEN d.mandatoryproperties AND d.mandatoryvalues THEN \"active\" ELSE \"idle\" END AS status1, d.`visibility` 
			FROM `event_values` v JOIN `devices` d ON (v.device=d.id AND d.contextbroker=v.cb) JOIN `contextbroker` cb ON (v.cb=cb.name )";

	$r = create_datatable_data($link, $_REQUEST, $q, "deleted IS null");

	$selectedrows = -1;
	if ($_REQUEST["length"] != -1) {
		$start = $_REQUEST['start'];
		$offset = $_REQUEST['length'];
		$tobelimited = true;
	} else {
		$tobelimited = false;
	}

	if ($r) {

		logAction($link, $username, 'event_value', 'get_all_delegated_event_value', '', $organization, '', 'success');
		$data = array();

		while ($row = mysqli_fetch_assoc($r)) {
			$eid = $row["organization"] . ":" . $row["contextbroker"] . ":" . $row["id"];
			if (
				isset($row["uri"]) && $row["uri"] != "" &&
				(
					(isset($result["delegation"][$row["uri"] . "/" . $row["value_name"]])
						&&  $result["delegation"][$row["uri"] . "/" . $row["value_name"]]["kind"] != "anonymous")
					||
					(isset($result["delegation"][$eid])
						&&  $result["delegation"][$eid]["kind"] != "anonymous"))
			) {
				$selectedrows++;
				if (!$tobelimited || ($tobelimited && $selectedrows >= $start && $selectedrows < ($start + $offset))) {
					$rec = array();
					$rec["id"] = $row["id"];
					$rec["cb"] = $row["cb"];
					$rec["device"] = $row["device"];
					$rec["devicetype"] = $row["devicetype"];
					$rec["value_name"] = $row["value_name"];
					$rec["data_type"] = $row["data_type"];
					$rec["value_type"] = $row["value_type"];
					$rec["editable"] = $row["editable"];
					$rec["value_unit"] = $row["value_unit"];
					$rec["healthiness_criteria"] = $row["healthiness_criteria"];
					$rec["order"] = $row["order"];
					$rec["value_refresh_rate"] = $row["value_refresh_rate"];
					$rec["latitude"] = $row["latitude"];
					$rec["longitude"] = $row["longitude"];
					$rec["organization"] = $row["organization"];
					$rec["accesslink"] = $row["accesslink"];
					$rec["accessport"] = $row["accessport"];
					$rec["created"] = $row["created"];
					$rec["kind"] = $row["kind"];
					$rec["status1"] = $row["status1"];
					$rec["k1"] = "";
					$rec["k2"] = "";
					$rec["service"] = $row["service"];
					$rec["servicePath"] = $row["servicePath"];
					if ($row["protocol"] == "ngsi w/MultiService") {
						$rec["device"] = explode(".", $row["device"])[2];
						$rec["id"] = explode(".", $row["id"])[2];
					}
					if (isset($result["delegation"][$row["uri"] . "/" . $row["value_name"]]["k1"])) {
						$rec["k1"] = $result["delegation"][$row["uri"] . "/" . $row["value_name"]]["k1"]; // to be fixed
						$rec["k2"] = $result["delegation"][$row["uri"] . "/" . $row["value_name"]]["k2"]; // to be fixed 
					} else if (isset($result["delegation"][$eid]["k1"])) {
						$rec["k1"] = $result["delegation"][$eid]["k1"]; // to be fixed
						$rec["k2"] = $result["delegation"][$eid]["k2"]; // to be fixed 
					}
					$rec["visibility"] = "delegated";

					array_push($data, $rec);
				}
			}
		}
		$output = format_result($_REQUEST["draw"], $selectedrows + 1, $selectedrows + 1, $data, "", "\r\n action=get_all_delegated_event_value \r\n", 'ok');
	} else {
		logAction($link, $username, 'event_value', 'get_all_delegated_event_value', '', $organization, '', 'faliure');
		$output = format_result(
			$_REQUEST["draw"],
			0,
			0,
			null,
			'Error: errors in reading data about devices. <br/>' . generateErrorMessage($link),
			'\n\r Error: errors in reading data about devices.' . generateErrorMessage($link),
			'ko'
		);
	}

	my_log($output);
	mysqli_close($link);
} else if ($action == 'get_all_private_event_value_map') {
	$ownDevices = getOwnerShipDevice($accessToken, $result);

	if ($ownDevices != "") {
		$q = "SELECT v.*, d.`id`, d.`kind`, d.`latitude`, d.`longitude`, d.`devicetype`,  d.`organization`, d.`visibility`, 
			d.`uri`, d.`created`, d.`privatekey`, d.`certificate`, cb.`sha`,cb.`accesslink`, cb.`accessport`,
	      	CASE WHEN d.mandatoryproperties AND d.mandatoryvalues THEN \"active\" ELSE \"idle\" END AS status1,
	      	d.`visibility` FROM `event_values` v JOIN `devices` d ON (v.device=d.id AND d.contextbroker=v.cb) JOIN `contextbroker` cb ON (v.cb=cb.name ) where deleted IS null";

		$r = mysqli_query($link, $q);

		if ($r) {
			logAction($link, $username, 'event_value', 'get_all_private_event_value_map', '', $organization, '', 'success');
			$result['status'] = 'ok';
			$result['log'] .= "\n\r action:get_all_private_event_value. ok " . $q;
			$result['content'] = array();
			while ($row = mysqli_fetch_assoc($r)) {
				$eid = $row["organization"] . ":" . $row["cb"] . ":" . $row["id"];

				if (
					isset($result["keys"][$eid])
					&& ((($result["keys"][$eid]["owner"] == $username) && ($role === 'RootAdmin'))
						||
						($role != 'RootAdmin'))
				) {
					$rec = array();
					$rec["id"] = $row["id"];
					$rec["cb"] = $row["cb"];
					$rec["device"] = $row["device"];
					$rec["devicetype"] = $row["devicetype"];
					$rec["value_name"] = $row["value_name"];
					$rec["data_type"] = $row["data_type"];
					$rec["value_type"] = $row["value_type"];
					$rec["editable"] = $row["editable"];
					$rec["value_unit"] = $row["value_unit"];
					$rec["healthiness_criteria"] = $row["healthiness_criteria"];
					$rec["order"] = $row["order"];
					$rec["value_refresh_rate"] = $row["value_refresh_rate"];
					$rec["latitude"] = $row["latitude"];
					$rec["longitude"] = $row["longitude"];
					$rec["kind"] = $row["kind"];
					$rec["status1"] = $row["status1"];
					$rec["created"] = $row["created"];
					$rec["privatekey"] = $row["privatekey"];
					$rec["certificate"] = $row["certificate"];
					$rec["sha"] = $row["sha"];
					$rec["accesslink"] = $row["accesslink"];
					$rec["accessport"] = $row["accessport"];

					if (isset($result["keys"][$eid])) {
						if ($row["visibility"] == "public") {
							$rec["visibility"] = "MyOwnPublic";
						} else {
							if (
								isset($result["delegation"][$row["uri"] . "/" . $row["value_name"]])
								&& $result["delegation"][$row["uri"] . "/" . $row["value_name"]]["kind"] == "anonymous"
							)
								$rec["visibility"] = "MyOwnPublic";
							else
								$rec["visibility"] = "MyOwnPrivate";
						}
						$rec["k1"] = $result["keys"][$eid]["k1"];
						$rec["k2"] = $result["keys"][$eid]["k2"];
					} else {
						$rec["visibility"] = $row["visibility"];
						$rec["k1"] = "";
						$rec["k2"] = "";
					}
					array_push($result['content'], $rec);
				}
			}
		} else {
			logAction($link, $username, 'event_value', 'get_all_private_event_value_map', '', $organization, '', 'faliure');

			$result['status'] = 'ko';
			$result['msg'] = 'Error: errors in action get_all_private_event_value_map <br/>' .   generateErrorMessage($link);
			$result['log'] = 'Error: errors in action get_all_private_event_value_map <br/>' .   generateErrorMessage($link);
		}
	}

	my_log($result);
	mysqli_close($link);
} else if ($action == 'get_all_delegated_event_value_map') {
	getDelegatedDevice($accessToken, $username, $result);

	$q = "SELECT v.*,d.`id`, d.`kind`, d.`latitude`, d.`longitude`, d.`devicetype`, d.`visibility`, d.`organization`, d.`uri`,
	      CASE WHEN d.`mandatoryproperties` AND d.`mandatoryvalues` THEN \"active\" ELSE \"idle\" END 
	      AS status1, d.`visibility` FROM `event_values` v JOIN `devices` d ON (v.`device`=d.`id` AND d.`contextbroker`=v.`cb`) where deleted IS null;";

	$r = mysqli_query($link, $q);

	if ($r) {
		logAction($link, $username, 'event_value', 'get_all_delegated_event_value_map', '', $organization, '', 'success');
		$result['status'] = 'ok';
		$result['log'] .= "\n\r action:get_all_delegated_event_value_map. ok " . $q;
		$result['content'] = array();
		while ($row = mysqli_fetch_assoc($r)) {
			$eid = $row["organization"] . ":" . $row["cb"] . ":" . $row["id"];
			if (
				isset($row["uri"]) && $row["uri"] != "" &&
				(
					(isset($result["delegation"][$row["uri"] . "/" . $row["value_name"]])
						&&  $result["delegation"][$row["uri"] . "/" . $row["value_name"]]["kind"] != "anonymous")
					||
					(isset($result["delegation"][$eid])
						&&  $result["delegation"][$eid]["kind"] != "anonymous"))

			) {
				$rec = array();
				$rec["id"] = $row["id"];
				$rec["cb"] = $row["cb"];
				$rec["device"] = $row["device"];
				$rec["devicetype"] = $row["devicetype"];
				$rec["value_name"] = $row["value_name"];
				$rec["data_type"] = $row["data_type"];
				$rec["value_type"] = $row["value_type"];
				$rec["editable"] = $row["editable"];
				$rec["value_unit"] = $row["value_unit"];
				$rec["healthiness_criteria"] = $row["healthiness_criteria"];
				$rec["order"] = $row["order"];
				$rec["value_refresh_rate"] = $row["value_refresh_rate"];
				$rec["latitude"] = $row["latitude"];
				$rec["longitude"] = $row["longitude"];
				$rec["kind"] = $row["kind"];
				$rec["status1"] = $row["status1"];
				$rec["k1"] = "";
				$rec["k2"] = "";
				if (isset($result["delegation"][$row["uri"] . "/" . $row["value_name"]]["k1"])) {
					$rec["k1"] = $result["delegation"][$row["uri"] . "/" . $row["value_name"]]["k1"]; // to be fixed
					$rec["k2"] = $result["delegation"][$row["uri"] . "/" . $row["value_name"]]["k2"]; // to be fixed 
				}
				$rec["visibility"] = "delegated";
				array_push($result['content'], $rec);
			}
		}
	} else {
		logAction($link, $username, 'event_value', 'get_all_delegated_event_value_map', '', $organization, '', 'faliure');
		$result['status'] = 'ko';
		$result['msg'] = 'Error: errors in action: get_all_delegated_event_value_map. <br/>' .   generateErrorMessage($link);
		$result['log'] = '\n\r Error: errors in action: get_all_delegated_event_value_map.' .   generateErrorMessage($link);
	}

	my_log($result);
	mysqli_close($link);
} else if ($action == "get_all_value_latlong") {
	$ownDevices = getOwnerShipDevice($accessToken, $result);
	getDelegatedDevice($accessToken, $username, $result);

	$q = "SELECT cb.sha,cb.accesslink, cb.accessport, v.*, d.id,d.contextbroker, d.kind, d.latitude, d.longitude, d.visibility, 
		d.devicetype, d.uri, d.created, d.privatekey, d.organization, d.certificate,d.visibility, 
		CASE WHEN d.mandatoryproperties AND d.mandatoryvalues THEN \"active\" ELSE \"idle\" END	AS status1 
		FROM event_values v JOIN devices d ON (v.device=d.id AND d.contextbroker=v.cb) JOIN contextbroker cb ON (v.cb=cb.name ) where deleted IS null;";

	$r = mysqli_query($link, $q);

	if ($r) {
		$result['status'] = 'ok';
		$result['log'] .= "\n\r action:get_all_value_latlong. ok " . $q;
		$result['content'] = array();

		while ($row = mysqli_fetch_assoc($r)) {
			$eid = $row["organization"] . ":" . $row["contextbroker"] . ":" . $row["device"];
			if (($role == "RootAdmin") ||
				((
					(
						($row["organization"] == $organization) &&
						(
							($row["visibility"] == 'public'
								||
								(isset($row["uri"]) && $row["uri"] != "" &&
									isset($result["delegation"][$row["uri"] . "/" . $row["value_name"]])) && $result["delegation"][$row["uri"] . "/" . $row["value_name"]]["kind"] == "anonymous"))))
					||
					(isset($row["uri"]) && $row["uri"] != "" &&
						((isset($result["delegation"][$eid]) && $result["delegation"][$eid]["kind"] != "anonymous")
							||
							(isset($result["delegation"][$row["uri"] . "/" . $row["value_name"]])) && $result["delegation"][$row["uri"] . "/" . $row["value_name"]]["kind"] != "anonymous"))

					||
					(isset($result["keys"][$eid])))
			) {
				array_push($result['content'], $row);
			}
		}
	} else {
		$result['status'] = 'ko';
		$result['msg'] = 'Error: errors in reading data about location of the device. <br/>' .  generateErrorMessage($link);
		$result['log'] = 'Error: errors in reading data about location of the device. <br/>' .  generateErrorMessage($link);
	}

	my_log($result);
	mysqli_close($link);
} else
if ($action == "delegate_value_list") {
	$missingParams = missingParameters(array('value_name', 'uri'));

	if (!empty($missingParams)) {
		$result["status"] = "ko";
		$result['msg'] = "Missing Parameters";
		$result["error_msg"] .= "Problem in insert device (Missing parameters: " . implode(", ", $missingParams) . " )";
		$result["log"] = "action=insert - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
	} else {
		$value_name = mysqli_real_escape_string($link, $_REQUEST['value_name']);
		$uri =  mysqli_real_escape_string($link, $_REQUEST['uri']);

		getDelegatorDevice($accessToken, $username, $result, $uri . "/" . $value_name);
	}

	my_log($result);
}
//TODO make mandary delegated group or delgated user
else if ($action == "delegate_value") {
	$missingParams = missingParameters(array('contextbroker', 'value_name', 'uri', 'k1', 'k2'));

	if (!empty($missingParams)) {
		$result["status"] = "ko";
		$result['msg'] = "Missing Parameters";
		$result["error_msg"] .= "Problem in insert device (Missing parameters: " . implode(", ", $missingParams) . " )";
		$result["log"] = "action=insert - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
	} else {
		$cb = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
		$value_name = mysqli_real_escape_string($link, $_REQUEST['value_name']);
		$uri =  mysqli_real_escape_string($link, $_REQUEST['uri']);
		$delegated_user = (isset($_REQUEST['delegated_user'])) ? mysqli_real_escape_string($link, $_REQUEST['delegated_user']) : "";
		$delegated_group = (isset($_REQUEST['delegated_group'])) ? mysqli_real_escape_string($link, $_REQUEST['delegated_group']) : "";
		$k1 = mysqli_real_escape_string($link, $_REQUEST['k1']);
		$k2 = mysqli_real_escape_string($link, $_REQUEST['k2']);

		if (($delegated_user != "" || $delegated_group != "") && $username != "") {
			delegateDeviceValue($uri . "/" . $value_name, $cb, $value_name, $username, $delegated_user, $delegated_group, $accessToken, $k1, $k2, $result);

			if ($result["status"] == 'ok') {
				logAction($link, $username, 'event_values', 'delegate_value', $uri . " " . $value_name, $organization, 'Delegated user: ' . $delegated_user, 'success');
			} else if ($result["status"] == 'ko') {
				logAction($link, $username, 'event_values', 'delegate_value', $uri . " " . $value_name, $organization, 'Delegated user: ' . $delegated_user, 'faliure');
			}
		} else {
			logAction($link, $username, 'event_values', 'delegate_value', $uri . " " . $value_name, $organization, 'Mandatory parameters not specified', 'faliure');
			$result["status"] = 'ko';
			$result["error_msg"] = 'The value delegation has been called without specifying mandatory parameters. ';
			$result["msg"] = '\n the function delegate_value has been called without specifying mandatory parameters';
			$result["log"] = '\n the function delegate_value has been called without specifying mandatory parameters';
		}
	}

	my_log($result);
} else
if ($action == "remove_delegation") {
	$missingParams = missingParameters(array('uri', 'value_name', 'delegationId'));

	if (!empty($missingParams)) {
		$result["status"] = "ko";
		$result['msg'] = "Missing Parameters";
		$result["error_msg"] .= "Problem in insert device (Missing parameters: " . implode(", ", $missingParams) . " )";
		$result["log"] = "action=insert - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
	} else {
		$uri = mysqli_real_escape_string($link, $_REQUEST['uri']);
		$value_name = mysqli_real_escape_string($link, $_REQUEST['value_name']);
		$delegationId = mysqli_real_escape_string($link, $_REQUEST['delegationId']);

		removeDelegationValue($accessToken, $username, $delegationId, $result);

		if ($result["status"] == 'ok') {
			logAction($link, $username, 'event_values', 'remove_delegation', $uri . " " . $value_name, $organization, '', 'success');
		} else if ($result["status"] == 'ko') {
			logAction($link, $username, 'event_values', 'remove_delegation', $uri . " " . $value_name, $organization, '', 'faliure');
		}
	}

	my_log($result);
} else if ($action == "remove_delegate_value") {
	$missingParams = missingParameters(array('uri', 'value_name'));

	if (!empty($missingParams)) {
		$result["status"] = "ko";
		$result['msg'] = "Missing Parameters";
		$result["error_msg"] .= "Problem in insert device (Missing parameters: " . implode(", ", $missingParams) . " )";
		$result["log"] = "action=insert - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
	} else {
		$uri = mysqli_real_escape_string($link, $_REQUEST['uri']);
		$value_name = mysqli_real_escape_string($link, $_REQUEST['value_name']);

		getDelegatorDevice($accessToken, $username, $result, $uri . "/" . $value_name);

		$delegated = $result["delegation"];
		$found = false;
		$i = 0;
		while (!$found && $i < count($delegated)) {
			if ($delegated[$i]["userDelegated"] == 'ANONYMOUS') {
				$found = true;
				$delegationId = $delegated[$i]["delegationId"];
			}
			$i++;
		}
		if ($found) {
			logAction($link, $username, 'event_values', 'remove_delegate_value', $uri . " " . $value_name, $organization, '', 'success');
			$result["status"] = "ok";
			$result["msg"] = "The delegation to anonymous has been changed";
			$result["log"] = "The delegation to anonymous has been changed";
			removeDelegationValue($accessToken, $username, $delegationId, $result);
		} else {
			logAction($link, $username, 'event_values', 'remove_delegate_value', $uri . " " . $value_name, $organization, '', 'faliure');
			$result["status"] = "ko";
			$result["error_msg"] = "The delegation to anonymous was not found. ";
			$result["msg"] = "The delegation to anonymous was not found";
			$result["log"] = "The delegation to anonymous was not found";
		}
	}
	my_log($result);
} else if ($action == 'get_all_temporary_event_value_for_registered_devices') {
	if (isset($_REQUEST['length']))	$length = mysqli_real_escape_string($link, $_REQUEST['length']);
	else $length = -1;
	$start = 1; //default is 1 but should throw an error
	if (($length != -1) && (isset($_REQUEST['start']))) $start = mysqli_real_escape_string($link, $_REQUEST['start']);
	if (isset($_REQUEST['draw'])) $draw = mysqli_real_escape_string($link, $_REQUEST['draw']);
	else $draw = 1;
	if (!isset($_REQUEST['columns'])) $_REQUEST["columns"] = array();
	if (isset($_REQUEST['select'])) $selection = json_decode($_REQUEST['select']);
	else $selection = array();

	getOwnerShipDevice($accessToken, $result);
	getDelegatedDevice($accessToken, $username, $result);

	$q = "SELECT cb.sha,cb.accesslink, cb.accessport, v.*,d.kind, d.contextbroker, d.latitude, d.longitude, 
			d.visibility, d.devicetype, d.uri, d.created, d.privatekey, d.organization, d.certificate,
			d.model, d.`protocol`, d.`service`, d.`servicePath`, d.format ,
			CASE WHEN d.mandatoryproperties AND d.mandatoryvalues THEN \"active\" ELSE \"idle\" END	AS status1 
			FROM temporary_event_values_for_registered_devices v JOIN devices d ON (v.device=d.id AND d.contextbroker=v.cb) JOIN contextbroker cb ON (v.cb=cb.name )";

	if (count($selection) != 0) {
		$a = 0;
		$cond = "";
		while ($a < count($selection)) {
			$sel = $selection[$a];
			$cond .= " (device='" . $sel->id . "' AND cb = '" . $sel->cb . "' AND value_name= '" . $sel->value_name . "') ";
			if ($a != count($selection) - 1)  $cond .= " OR ";
			$a++;
		}
		$r = create_datatable_data($link, $_REQUEST, $q, "deleted IS null AND (" . $cond . ") AND (toDelete IS NULL OR toDelete != 'yes')");
	} else {
		$r = create_datatable_data($link, $_REQUEST, $q, "deleted IS null AND (toDelete IS NULL OR toDelete != 'yes')");
	}

	$selectedrows = -1;
	if ($length != -1) {
		$offset = $_REQUEST['length'];
		$tobelimited = true;
	} else {
		$tobelimited = false;
	}

	if ($r) {
		$data = array();

		while ($row = mysqli_fetch_assoc($r)) {
			$eid = $row["organization"] . ":" . $row["contextbroker"] . ":" . $row["device"];
			if (($role == "RootAdmin") ||
				((
					(
						($row["organization"] == $organization) &&
						(
							($row["visibility"] == 'public'
								||
								(isset($row["uri"]) && $row["uri"] != "" &&
									isset($result["delegation"][$row["uri"] . "/" . $row["value_name"]])) && $result["delegation"][$row["uri"] . "/" . $row["value_name"]]["kind"] == "anonymous"))))
					||
					(isset($row["uri"]) && $row["uri"] != "" &&
						((isset($result["delegation"][$eid]) && $result["delegation"][$eid]["kind"] != "anonymous")
							||
							(isset($result["delegation"][$row["uri"] . "/" . $row["value_name"]])) && $result["delegation"][$row["uri"] . "/" . $row["value_name"]]["kind"] != "anonymous"))
					||
					(isset($result["keys"][$eid])))
			) {
				$selectedrows++;
				if (!$tobelimited || ($tobelimited && $selectedrows >= $start && $selectedrows < ($start + $offset))) {
					$rec = array();
					$rec["cb"] = $row["cb"];
					$rec["device"] = $row["device"];
					$rec["devicetype"] = $row["devicetype"];
					$rec["value_name"] = $row["value_name"];
					$rec["data_type"] = $row["data_type"];
					$rec["value_type"] = $row["value_type"];
					$rec["editable"] = $row["editable"];
					$rec["value_unit"] = $row["value_unit"];
					$rec["healthiness_criteria"] = $row["healthiness_criteria"];
					$rec["order"] = $row["order"];
					$rec["value_refresh_rate"] = $row["value_refresh_rate"];
					$rec["organization"] = $row["organization"];
					$rec["latitude"] = $row["latitude"];
					$rec["longitude"] = $row["longitude"];
					$rec["kind"] = $row["kind"];
					$rec["format"] = $row["format"];
					$rec["uri"] = $row["uri"];
					$rec["status1"] = $row["status1"];
					$rec["created"] = $row["created"];
					$rec["privatekey"] = $row["privatekey"];
					$rec["certificate"] = $row["certificate"];
					$rec["sha"] = $row["sha"];
					$rec["accesslink"] = $row["accesslink"];
					$rec["accessport"] = $row["accessport"];
					$rec["service"] = $row["service"];
					$rec["servicePath"] = $row["servicePath"];
					$rec["model"] = $row["model"];
					if ($row["protocol"] == "ngsi w/MultiService") {
						$rec["device"] = explode(".", $row["device"])[2];
					}

					if (isset($result["keys"][$eid])) { //it's mine
						if ((isset($result["delegation"][$row["uri"] . "/" . $row["value_name"]])
								&& $result["delegation"][$row["uri"] . "/" . $row["value_name"]]["kind"] == "anonymous")
							||
							(isset($result["delegation"][$eid])
								&& $result["delegation"][$eid]["kind"] == "anonymous")
						)
							$rec["visibility"] = "MyOwnPublic";
						else
							$rec["visibility"] = "MyOwnPrivate";

						$rec["k1"] = $result["keys"][$eid]["k1"];
						$rec["k2"] = $result["keys"][$eid]["k2"];
					} else { //it's not mine
						if ((isset($result["delegation"][$row["uri"] . "/" . $row["value_name"]])
								&& ($result["delegation"][$row["uri"] . "/" . $row["value_name"]]["kind"] == "anonymous"))
							||
							(isset($result["delegation"][$eid])
								&& ($result["delegation"][$eid]["kind"] == "anonymous"))
						) { //it's delegated as public
							$rec["visibility"] = 'public';
							$rec["k1"] = "";
							$rec["k2"] = "";
						} else if ((isset($result["delegation"][$row["uri"] . "/" . $row["value_name"]]))
							||
							(isset($result["delegation"][$eid]))
						) { //it's delegated personally
							$rec["visibility"] = 'delegated';
							$rec["k1"] = "";
							$rec["k2"] = "";

							if (isset($result["delegation"][$row["uri"] . "/" . $row["value_name"]]["k1"])) {
								$rec["k1"] = $result["delegation"][$row["uri"] . "/" . $row["value_name"]]["k1"]; // to be fixed 
								$rec["k2"] = $result["delegation"][$row["uri"] . "/" . $row["value_name"]]["k2"]; // to be fixed 
							} else if (isset($result["delegation"][$eid]["k1"])) {
								$rec["k1"] = $result["delegation"][$eid]["k1"]; // to be fixed 
								$rec["k2"] = $result["delegation"][$eid]["k2"]; // to be fixed 
							}
						} else { //not mine, not delegated
							$rec["visibility"] = $row["visibility"];
							$rec["k1"] = "";
							$rec["k2"] = "";
						}
					}
					array_push($data, $rec);
				}
			}
		}
		$output = format_result($draw, $selectedrows + 1, $selectedrows + 1, $data, "", "\r\n action=get_all_temporary_event_value_for_registered_devices \r\n", 'ok');
	} else {
		$output = format_result($draw, 0, 0, null, 'Error: errors in reading data about values. <br/>' .
			generateErrorMessage($link), '\n\r Error: errors in reading data about values.' . generateErrorMessage($link), 'ko');
	}

	my_log($output);
	mysqli_close($link);
} else if ($action == "update_temporary_event_values_for_registered_devices") {
	$missingParams = missingParameters(array('contextbroker', 'device', 'value_name', 'old_value_name', 'data_type', 'value_type', 'editable', 'value_unit', 'healthiness_criteria', 'healthiness_value'));

	if (!empty($missingParams)) {
		$result["status"] = "ko";
		$result['msg'] = "Missing Parameters";
		$result["error_msg"] .= "Problem update the value information (Missing parameters: " . implode(", ", $missingParams) . " )";
		$result["log"] = "action=update - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
	} else {
		$cb = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
		$device = mysqli_real_escape_string($link, $_REQUEST['device']);
		$value_name = mysqli_real_escape_string($link, $_REQUEST['value_name']);
		$old_value_name = mysqli_real_escape_string($link, $_REQUEST['old_value_name']);
		$data_type = mysqli_real_escape_string($link, $_REQUEST['data_type']);
		$value_type = mysqli_real_escape_string($link, $_REQUEST['value_type']);
		$editable = mysqli_real_escape_string($link, $_REQUEST['editable']);
		$value_unit = mysqli_real_escape_string($link, $_REQUEST['value_unit']);
		$healthiness_criteria = mysqli_real_escape_string($link, $_REQUEST['healthiness_criteria']);
		$healthiness_value = mysqli_real_escape_string($link, $_REQUEST['healthiness_value']);

		$hc = "";
		if ($healthiness_criteria == "refresh_rate") $hc = "value_refresh_rate";
		else if ($healthiness_criteria == "different_values") $hc = "different_values";
		else $hc = "value_bounds";

		$protocol = getProtocol($cb, $link);

		if (empty($protocol)) //it also ensure the contextbroker name is valid
		{
			$result["status"] = "ko";
			$result['msg'] = "Unrecognized contextbroker/protocol";
			$result["error_msg"] .= "Problem in update value (Unrecognized contextbroker/protocol)";
			$result["log"] = "action=update - error Unrecognized contextbroker/protocol\r\n";
		} else {
			get_device($username, $role, $device, $cb,  $accessToken, $link, $result);

			if (empty($result["content"])) {
				$result["status"] = "ko";
				$result['msg'] = "Unrecognized device";
				$result["error_msg"] .= "Problem in update value (Unrecognized device)";
				$result["log"] = "action=update - error Unrecognized device\r\n";
			} else {
				$dev_organization = $result["content"]["organization"];
				$eId = $dev_organization . ":" . $cb . ":" . $device;

				if (!enforcementRights($username, $accessToken, $role, $eId, 'IOTID', 'write', $result)) {
					$result["status"] = "ko";
					$result['msg'] = "Not ownership or enough right to update";
					$result["error_msg"] .= "Problem in update value (Not ownership or enough right to update)";
					$result["log"] .= "action=update - error Not ownership or enough right to update\r\n";
				} else {
					$q = "UPDATE temporary_event_values_for_registered_devices SET cb = '$cb', device = '$device', value_name = '$value_name', old_value_name = '$old_value_name', data_type = '$data_type', value_type = '$value_type', 
						editable = '$editable', value_unit = '$value_unit', healthiness_criteria = '$healthiness_criteria', $hc = '$healthiness_value' 
						WHERE cb = '$cb' and device='$device' and value_name='$value_name'";
					$r = mysqli_query($link, $q);

					if ($r) {
						//modify_valueKB($link, $device, $cb, $organization, retrieveKbUrl($organizationApiURI, $organization),$result);	   
						$result["editable"] = $editable;
						if ($result["content"] == null) $result["active"] = false;
						else $result["active"] = true;
						$result["msg"] .= '\n update in the db of the value was ok';
						$result["log"] .= "\r\n Value $cb/$device/$value_name correctly updated";
						if (!isset($result["status"])) {
							logAction($link, $username, 'event_values', 'update', $device . " " . $cb . " " . $value_name, $organization, '', 'success');
							$result["status"] = "ok";
						} else {
							if ($result["status"] == "ko") {
								logAction($link, $username, 'event_values', 'update', $device . " " . $cb . " " . $value_name, $organization, 'Error occurred in the KB', 'faliure');
								$result["msg"] .= '\n an error occurred in the KB or context broker';
								$result["log"] .= '\n an error occurred in the KB or context broker';
							}
							if ($result["status"] == "ok") {
								logAction($link, $username, 'event_values', 'update', $device . " " . $cb . " " . $value_name, $organization, '', 'success');
							}
						}
					} else {
						logAction($link, $username, 'event_values', 'update', $device . " " . $cb . " " . $value_name, $organization, '', 'faliure');
						$result["status"] = 'ko';
						$result["msg"] = 'event_values <b>' . $value_name . '</b> &nbsp; update failed, ' .  generateErrorMessage($link) . ' Please enter again.';
						$result["log"] = '\n\r event_values <b>' . $value_name . '</b> &nbsp; update failed, ' .  generateErrorMessage($link);
					}
				}
			}
		}
	}

	my_log($result);
	mysqli_close($link);
} else if ($action == "delete_temporary_event_values_for_registered_devices") {
	$missingParams = missingParameters(array('contextbroker', 'device', 'value_name'));

	if (!empty($missingParams)) {
		$result["status"] = "ko";
		$result['msg'] = "Missing Parameters";
		$result["error_msg"] .= "Problem delete the value information (Missing parameters: " . implode(", ", $missingParams) . " )";
		$result["log"] = "action=delete - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
	} else {
		$cb = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
		$device = mysqli_real_escape_string($link, $_REQUEST['device']);
		$value_name = mysqli_real_escape_string($link, $_REQUEST['value_name']);
		//$editable = mysqli_real_escape_string($link, $_REQUEST['editable']);

		$protocol = getProtocol($cb, $link);

		if (empty($protocol)) //it also ensure the contextbroker name is valid
		{
			$result["status"] = "ko";
			$result['msg'] = "Unrecognized contextbroker/protocol";
			$result["error_msg"] .= "Problem in delete value (Unrecognized contextbroker/protocol)";
			$result["log"] = "action=delete - error Unrecognized contextbroker/protocol\r\n";
		} else {
			get_device($username, $role, $device, $cb,  $accessToken, $link, $result);

			if (empty($result["content"])) {
				$result["status"] = "ko";
				$result['msg'] = "Unrecognized device";
				$result["error_msg"] .= "Problem in update value (Unrecognized device)";
				$result["log"] = "action=update - error Unrecognized device\r\n";
			} else {
				$dev_organization = $result["content"]["organization"];
				$eId = $dev_organization . ":" . $cb . ":" . $device;

				if (!enforcementRights($username, $accessToken, $role, $eId, 'IOTID', 'write', $result)) {
					$result["status"] = "ko";
					$result['msg'] = "Not ownership or enough right to update";
					$result["error_msg"] .= "Problem in update value (Not ownership or enough right to update)";
					$result["log"] .= "action=update - error Not ownership or enough right to update\r\n";
				} else {

					// $q = "DELETE FROM temporary_event_values_for_registered_devices WHERE cb = '$cb' and device='$device' and value_name='$value_name'";
					$q = "UPDATE temporary_event_values_for_registered_devices SET toDelete='yes' WHERE cb = '$cb' and device='$device' and value_name='$value_name'";
					$r = mysqli_query($link, $q);
					if ($r) {
						//modify_valueKB($link, $device, $cb, $organization, retrieveKbUrl($organizationApiURI, $organization), $result);	   
						//$result["editable"]=$editable;		
						if ($result["content"] == null) $result["active"] = false;
						else $result["active"] = true;
						$result["msg"] .= '\n delete in the db of the value was ok';
						$result["log"] .= '\n delete in the db of the value was ok';
						if (!isset($result["status"])) {
							logAction($link, $username, 'event_values', 'delete', $device . " " . $cb . " " . $value_name, $organization, '', 'success');
							$result["status"] = "ok";
						} else {
							if ($result["status"] == "ko") {
								logAction($link, $username, 'event_values', 'delete', $device . " " . $cb . " " . $value_name, $organization, 'Error occurred in the KB', 'faliure');
								$result["error_msg"] .= 'An error occurred in the KB or context broker. ';
								$result["msg"] .= '\n an error occurred in the KB or context broker';
								$result["log"] .= '\n an error occurred in the KB or context broker';
							}
							if ($result["status"] == "ok") {
								logAction($link, $username, 'event_values', 'delete', $device . " " . $cb . " " . $value_name, $organization, '', 'success');
							}
						}
					} else {
						logAction($link, $username, 'event_values', 'delete', $device . " " . $cb . " " . $value_name, $organization, '', 'faliure');
						$result["status"] = 'ko';
						$result["error_msg"] = 'Event_values ' . $value_name . ': deletion failed, ';
						$result["msg"] = 'event_values <b>' . $value_name . '</b> &nbsp; deletion failed, ' . generateErrorMessage($link) .  ' Please enter again.';
						$result["log"] = '\n\r event_values ' . $value_name . ' deletion failed, ' .    generateErrorMessage($link);
					}
				}
			}
		}
	}

	my_log($result);
	mysqli_close($link);
} else if ($action == "insert_all_valid_event_values") {
	$missingParams = missingParameters(array('listAttributes'));

	if (!empty($missingParams)) {
		$result["status"] = "ko";
		$result['msg'] = "Missing Parameters";
		$result["error_msg"] .= "Problem in insert value (Missing parameters: " . implode(", ", $missingParams) . " )";
		$result["log"] = "action=insert - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
	} else {

		$q = "DELETE FROM temporary_event_values_for_registered_devices WHERE toDelete='yes'";
		$r = mysqli_query($link, $q);
		//$list = ($link, $_REQUEST['listAttributes']);
		$listAttributes = json_decode($_REQUEST['listAttributes']);

		//$result['msg'] = $listAttributes;

		foreach ($listAttributes as $attr) {
			$cb = $attr->contextbroker;
			$device = $attr->device;
			$value_name = $attr->attribute->value_name;
			$data_type = $attr->attribute->data_type;
			$value_type = $attr->attribute->value_type;
			$editable = $attr->attribute->editable;
			if (empty($editable)) $editable = "0";
			$value_unit = $attr->attribute->value_unit;
			$healthiness_criteria = $attr->attribute->healthiness_criteria;
			$healthiness_value = $attr->attribute->healthiness_value;
			if (empty($healthiness_value)) $healthiness_value = "0";

			$hc = "";
			if ($healthiness_criteria == "refresh_rate") $hc = "value_refresh_rate";
			else if ($healthiness_criteria == "different_values") $hc = "different_values";
			else $hc = "value_bounds";

			$protocol = getProtocol($cb, $link);
			if (empty($protocol)) //it also ensure the contextbroker name is valid
			{
				$result["status"] = "ko";
				$result['msg'] = "Unrecognized contextbroker/protocol";
				$result["error_msg"] .= "Problem in insert value (Unrecognized contextbroker/protocol)";
				$result["log"] = "action=insert - error Unrecognized contextbroker/protocol\r\n";
			} else {
				//get device ---- it also assure device existence
				$q  = "SELECT * FROM devices WHERE id = '$device' and contextBroker = '$cb'";
				$r = mysqli_query($link, $q);
				if ((!$r) || (count(mysqli_fetch_assoc($r)) == 0)) {
					$result["status"] = "ko";
					$result['msg'] = "Unrecognized device";
					$result["error_msg"] .= "Problem in getting device (Unrecognized device)";
					$result["log"] = "action=insert - error Unrecognized device\r\n";
				} else {
					$q = "INSERT INTO event_values(cb, device, value_name, data_type, value_type, editable, value_unit, healthiness_criteria, `$hc`) " .
						"VALUES('$cb', '$device', '$value_name',  '$data_type', '$value_type', '$editable', '$value_unit', '$healthiness_criteria', '$healthiness_value')"; //, '$order' )";
					$r = mysqli_query($link, $q);

					if ($r) {
						$result["log"] .= "\r\n Value $cb/$device/$value_name correctly inserted \r\n";
						modify_valueKB($link, $device, $cb, $organization, retrieveKbUrl($organizationApiURI, $organization), $result);
						$result["editable"] = $editable;
						if ($result["content"] == null) $result["active"] = false;
						else $result["active"] = true;
						$result["msg"] .= '\n insertion in the db of the value was ok';
						if (!isset($result["status"])) {
							logAction($link, $username, 'event_values', 'insert', $device . " " . $cb . " " . $value_name, $organization, '', 'success');
							$result["status"] = "ok";
						} else if ($result["status"] == "ko") {
							logAction($link, $username, 'event_values', 'insert', $device . " " . $cb . " " . $value_name, $organization, 'Error occurred in the KB', 'faliure');
							$result["msg"] .= '\n an error occurred in the KB or context broker';
							$result["log"] .= '\n an error occurred in the KB or context broker';
						} else if ($result["status"] == "ok") {
							logAction($link, $username, 'event_values', 'insert', $device . " " . $cb . " " . $value_name, $organization, '', 'success');
						}
						$q = "DELETE FROM temporary_event_values_for_registered_devices WHERE cb = '$cb' and device='$device' and value_name='$value_name'";
						$r = mysqli_query($link, $q);
					} else {
						logAction($link, $username, 'event_values', 'insert', $device . " " . $cb . " " . $value_name, $organization, 'Error occurred registering the value', 'faliure');
						$result["status"] = 'ko';
						$result["msg"] = "Error: An error occurred when registering the value	$value_name. <br/>" .   generateErrorMessage($link) .   '<br/> Please enter again the value_name';
						$result["log"] = "\n\r Error: An error occurred when registering the value	 $value_name. <br/>" .   generateErrorMessage($link) .   '<br/> Please enter again the value_name';
					}
				}
			}
		}


		// $cb = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
		// $device = mysqli_real_escape_string($link, $_REQUEST['device']);
		// $value_name = mysqli_real_escape_string($link, $_REQUEST['value_name']);
		// $data_type = mysqli_real_escape_string($link, $_REQUEST['data_type']);
		// $value_type = mysqli_real_escape_string($link, $_REQUEST['value_type']);
		// $editable = mysqli_real_escape_string($link, $_REQUEST['editable']);
		// if (empty($editable)) $editable = "0";
		// $value_unit = mysqli_real_escape_string($link, $_REQUEST['value_unit']);
		// $healthiness_criteria = mysqli_real_escape_string($link, $_REQUEST['healthiness_criteria']);
		// $healthiness_value = mysqli_real_escape_string($link, $_REQUEST['healthiness_value']);
		// if (empty($healthiness_value)) $healthiness_value = "0";

		// $hc = "";
		// if ($healthiness_criteria == "refresh_rate") $hc = "value_refresh_rate";
		// else if ($healthiness_criteria == "different_values") $hc = "different_values";
		// else $hc = "value_bounds";

		// $protocol = getProtocol($cb, $link);

		// if (empty($protocol)) //it also ensure the contextbroker name is valid
		// {
		// 	$result["status"] = "ko";
		// 	$result['msg'] = "Unrecognized contextbroker/protocol";
		// 	$result["error_msg"] .= "Problem in insert value (Unrecognized contextbroker/protocol)";
		// 	$result["log"] = "action=insert - error Unrecognized contextbroker/protocol\r\n";
		// } else {
		// 	//get device ---- it also assure device existence
		// 	$q  = "SELECT * FROM devices WHERE id = '$device' and contextBroker = '$cb'";
		// 	$r = mysqli_query($link, $q);
		// 	if ((!$r) || (count(mysqli_fetch_assoc($r)) == 0)) {
		// 		$result["status"] = "ko";
		// 		$result['msg'] = "Unrecognized device";
		// 		$result["error_msg"] .= "Problem in getting device (Unrecognized device)";
		// 		$result["log"] = "action=insert - error Unrecognized device\r\n";
		// 	} else {
		// 		$q = "INSERT INTO event_values(cb, device, value_name, data_type, value_type, editable, value_unit, healthiness_criteria, `$hc`) " .
		// 			"VALUES('$cb', '$device', '$value_name',  '$data_type', '$value_type', '$editable', '$value_unit', '$healthiness_criteria', '$healthiness_value')"; //, '$order' )";
		// 		$r = mysqli_query($link, $q);

		// 		if ($r) {
		// 			$result["log"] .= "\r\n Value $cb/$device/$value_name correctly inserted \r\n";
		// 			modify_valueKB($link, $device, $cb, $organization, retrieveKbUrl($organizationApiURI, $organization), $result);
		// 			$result["editable"] = $editable;
		// 			if ($result["content"] == null) $result["active"] = false;
		// 			else $result["active"] = true;
		// 			$result["msg"] .= '\n insertion in the db of the value was ok';
		// 			if (!isset($result["status"])) {
		// 				logAction($link, $username, 'event_values', 'insert', $device . " " . $cb . " " . $value_name, $organization, '', 'success');
		// 				$result["status"] = "ok";
		// 			} else if ($result["status"] == "ko") {
		// 				logAction($link, $username, 'event_values', 'insert', $device . " " . $cb . " " . $value_name, $organization, 'Error occurred in the KB', 'faliure');
		// 				$result["msg"] .= '\n an error occurred in the KB or context broker';
		// 				$result["log"] .= '\n an error occurred in the KB or context broker';
		// 			} else if ($result["status"] == "ok") {
		// 				logAction($link, $username, 'event_values', 'insert', $device . " " . $cb . " " . $value_name, $organization, '', 'success');
		// 			}
		// 		} else {
		// 			logAction($link, $username, 'event_values', 'insert', $device . " " . $cb . " " . $value_name, $organization, 'Error occurred registering the value', 'faliure');
		// 			$result["status"] = 'ko';
		// 			$result["msg"] = "Error: An error occurred when registering the value	$value_name. <br/>" .   generateErrorMessage($link) .   '<br/> Please enter again the value_name';
		// 			$result["log"] = "\n\r Error: An error occurred when registering the value	 $value_name. <br/>" .   generateErrorMessage($link) .   '<br/> Please enter again the value_name';
		// 		}
		// 	}
		// }
	}

	my_log($result);
	mysqli_close($link);
} else {
	$result['status'] = 'ko';
	$result['msg'] = 'invalid action ' . $action;
	$result['log'] = 'invalid action ' . $action;
	my_log($result);
}
