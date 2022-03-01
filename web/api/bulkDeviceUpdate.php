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
    $result['msg'] = 'action not present';
    $result['error_msg'] = 'action not present';
    $result['log'] = 'bulkDeviceUpdate.php action not present';
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
    'token_endpoint' => $keycloakHostUri . '/auth/realms/master/protocol/openid-connect/token',
    'userinfo_endpoint' => $keycloakHostUri . '/auth/realms/master/protocol/openid-connect/userinfo'
));

$accessToken = "";
if (isset($_REQUEST['nodered'])) {
    if ((isset($_REQUEST['token'])) && ($_REQUEST['token'] != 'undefined'))
        $accessToken = $_REQUEST['token'];
} else {
    if (isset($_REQUEST['token'])) {
        $mctime = microtime(true);
        $tkn = $oidc->refreshToken($_REQUEST['token']);
        error_log("---- bulkDeviceUpdate.php:" . (microtime(true) - $mctime));
        $accessToken = $tkn->access_token;
    }
}
if (empty($accessToken)) {
    $result["status"] = "ko";
    $result['msg'] = "Access Token not present";
    $result["error_msg"] .= "Access Token not present";
    $result["log"] = "bulkDeviceUpdate.php AccessToken not present\r\n";
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
    $result["log"] = "bulkDeviceUpdate.php Cannot retrieve user information\r\n";
    my_log($result);
    mysqli_close($link);
    exit();
}
//if ($action == "get_number_temporary_devices_grouped_by_CB") {
//
//    $cb = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
//    $q = "SELECT DISTINCT count(id), contextBroker FROM iotdb.temporary_devices;";
//    $r = mysqli_query($link, $q);
//    
//   
//    if ($r) {
//       // $row = mysqli_fetch_assoc($r);
//       // $row= json_encode($row);
//        $cb_number = array();
//        while ($row = mysqli_fetch_assoc($r)) {
//            
//            $sub_row= array($row["contextBroker"] => $row["count(id)"]);
//            array_push($cb_number, $sub_row);
//        }
//        
//        $result['status'] = 'ok';
//        $result['content'] = $cb_number;
//        $result['log'] .= "\n\r action:get_number_temporary_devices_grouped_by_CB. access to " . $q;
//    } else {
//        $result['status'] = 'ko'; // . $q1 . generateErrorMessage($link);
//        $result['msg'] = 'Error: errors in reading data about cb. <br/>' . generateErrorMessage($link);
//        $result['log'] .= '\n\naction:get_number_temporary_devices_grouped_by_CB. Error: errors in reading data about devices. ' . generateErrorMessage($link);
//    }
//    my_log($result);
//    mysqli_close($link);
//}
//else if ($action == "get_frequency_timestamStatus_CB") {
//
//    // $cb = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
//    
//    $q = "SELECT req_frequency, timestampstatus, name FROM iotdb.contextbroker ";
//    $r = mysqli_query($link, $q);
//    
//   
//   
//    if ($r) {
//      // $row = mysqli_fetch_assoc($r);
//       // $row= json_encode($row);
//        $cb_number = array();
//        while ($row = mysqli_fetch_assoc($r)) {
//            $sub_row["name"]= $row["name"];
//            $sub_row["req_frequency"]= $row["req_frequency"];
//            $sub_row["timestampstatus"]= $row["timestampstatus"];
//            
//            array_push($cb_number, $sub_row);
//        }
//        
//        $result['status'] = 'ok';
//        $result['content'] = $cb_number;
//        $result['log'] .= "\n\r action:get_frequency_timestamStatus_CB. access to " . $q;
//    } else {
//        $result['status'] = 'ko'; // . $q1 . generateErrorMessage($link);
//        $result['msg'] = 'Error: errors in reading data about cb. <br/>' . generateErrorMessage($link);
//        $result['log'] .= '\n\naction:get_frequency_timestamStatus_CB. Error: errors in reading data about devices. ' . generateErrorMessage($link);
//    }
//    my_log($result);
//    mysqli_close($link);
//}
//else 
    if ($action== "Save_device_rules"){
    $missingParams = missingParameters(array('attributesIf', 'attributesThen'));

    if (!empty($missingParams)) {
        $result["status"] = "ko";
        $result['msg'] = "Missing Parameters";
        $result["error_msg"] .= "Problem in get bulk device (Missing parameters: " . implode(", ", $missingParams) . " )";
        $result["log"] = "action=Save_device_rules - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
        //TODO Copy to output
    } else if(mysqli_real_escape_string($link, $_REQUEST['update'])){
        $attributesIf = mysqli_real_escape_string($link, $_REQUEST['attributesIf']);
        $attributesThen = mysqli_real_escape_string($link, $_REQUEST['attributesThen']);
        $namerule = mysqli_real_escape_string($link, $_REQUEST['name']);
        $mode = mysqli_real_escape_string($link, $_REQUEST['mode']);
         $q="UPDATE `iotdb`.`EXT_values_rules` SET `Timestamp`=NOW(), `If_statement`= '$attributesIf', `Then_statement`= '$attributesThen', `mode`='$mode' WHERE `Name`='$namerule' ;";
            
        $r = mysqli_query($link, $q);
        
         if($r){
            $row = mysqli_fetch_assoc($r);
            $result['status'] = 'ok';
            $result['content'] = $row;
            $result['log'] .= "\n\r action:Save_device_rules. access to " . $q;
        } else {
            $result['status'] = 'ko'; // . $q1 . generateErrorMessage($link);
            $result['msg'] = 'Error: errors in writing data about devices. <br/>' . generateErrorMessage($link);
            $result['log'] .= '\n\naction:Save_device_rules. Error: errors in reading data about devices. ' . generateErrorMessage($link);
        
        }
        echo json_encode($result);
        
    }
    else {
        $attributesIf = mysqli_real_escape_string($link, $_REQUEST['attributesIf']);
        $attributesThen = mysqli_real_escape_string($link, $_REQUEST['attributesThen']);
        $namerule = mysqli_real_escape_string($link, $_REQUEST['name']);
        $mode = mysqli_real_escape_string($link, $_REQUEST['mode']);
        
        $q= "INSERT INTO `iotdb`.`EXT_values_rules` (`Name`,`If_statement`, `Then_statement`, `Organization`, `Timestamp`,`mode` ) VALUES ('$namerule','$attributesIf', '$attributesThen', '$organization', NOW(), '$mode');";
        $r = mysqli_query($link, $q);
        
        if($r){
            $row = mysqli_fetch_assoc($r);
            $result['status'] = 'ok';
            $result['content'] = $row;
            $result['log'] .= "\n\r action:Save_device_rules. access to " . $q;
        } else {
            $result['status'] = 'ko'; // . $q1 . generateErrorMessage($link);
            $result['msg'] = 'Error: errors in writing data about devices. <br/>' . generateErrorMessage($link);
            $result['log'] .= '\n\naction:Save_device_rules. Error: errors in reading data about devices. ' . generateErrorMessage($link);
        
        }
        echo json_encode($result);
    }
}

else if ($action == "get_temporary_devices") {
    $missingParams = missingParameters(array('should_be_registered'));

    if (!empty($missingParams)) {
        $result["status"] = "ko";
        $result['msg'] = "Missing Parameters";
        $result["error_msg"] .= "Problem in get bulk device (Missing parameters: " . implode(", ", $missingParams) . " )";
        $result["log"] = "action=get_temporary_devices - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
        //TODO Copy to output
    } else {
        $should_be_registered = mysqli_real_escape_string($link, $_REQUEST['should_be_registered']);
        $usernameNotHashed = $username;
        $username = md5($username);

        // $qdel = "DELETE FROM temporary_devices   WHERE username = '$username' AND 	 toDelete = 'yes' AND deleted is null AND status = 'valid'";
        // $rdel = mysqli_query($link, $qdel);

        $q = "SELECT contextBroker, id, devicetype, model, status, macaddress,frequency,kind, 
		protocol,format,latitude, longitude, visibility, k1, k2,producer, edge_gateway_type, edge_gateway_uri, validity_msg, subnature, static_attributes, service, servicePath
		 FROM temporary_devices"; // WHERE username = '$username' AND deleted IS null;";
        //TODO not sure is should be registered has to be included or no
        $r = create_datatable_data($link, $_REQUEST, $q, 
                "deleted IS null AND username = '$username' AND organization='$organization' AND should_be_registered='$should_be_registered' AND (toDelete IS NULL OR toDelete != 'yes')");

        $selectedrows = -1;
        if ($_REQUEST["length"] != -1) {
            $start = $_REQUEST['start'];
            $offset = $_REQUEST['length'];
            $tobelimited = true;
        } else {
            $tobelimited = false;
        }

        if ($r) {
           
            $device = array();
            $option=array();
//        $dev2=array();
            
            $result["log"] = "\r\n action=get_temporary_devices \r\n";

            while ($row = mysqli_fetch_assoc($r)) {
              
                $selectedrows++;
                if (!$tobelimited || ($tobelimited && $selectedrows >= $start && $selectedrows < ($start + $offset))) {
                    $rec = array();
                    $format_msg = str_replace("-"," <br> <br> - ",$row["validity_msg"]);
                    $count_err= substr_count($format_msg, "- ");
                    
//                    $format_msg = str_replace("For "," <br> <br> For ",$row["validity_msg"]);
//                    $format_msg = str_replace("-","",$format_msg);
//                    $count_err= substr_count($format_msg, "For ");
                    
                    
                    $rec["contextbroker"] = $row["contextBroker"];
                    $rec["name"] = $row["id"];
                    $rec["devicetype"] = $row["devicetype"];
                    $rec["model"] = $row["model"];
                    $rec["status"] = $row["status"];
                    $rec["macaddress"] = $row["macaddress"];
                    $rec["frequency"] = $row["frequency"];
                    $rec["kind"] = $row["kind"];
                    $rec["protocol"] = $row["protocol"];
                    $rec["format"] = $row["format"];
                    $rec["latitude"] = $row["latitude"];
                    $rec["longitude"] = $row["longitude"];
                    $rec["visibility"] = $row["visibility"];
                    $rec["producer"] = $row["producer"];
                    $rec["k1"] = $row["k1"];
                    $rec["k2"] = $row["k2"];
                    $rec["edge_gateway_type"] = $row["edge_gateway_type"];
                    $rec["edge_gateway_uri"] = $row["edge_gateway_uri"];
                    
                    $rec["validity_msg"] = "<b>There is ". $count_err." errors. </b>" . $format_msg;
                            
                            //$row["validity_msg"];
                            //
                    $rec["subnature"] = $row["subnature"];
                    $rec["static_attributes"] = $row["static_attributes"];
                    $rec["service"] = $row["service"];
                    $rec["servicePath"] = $row["servicePath"];

                    array_push($device, $rec);
//                    array_push($dev2, $row);
                   
                  
                     
                }
            }
         
           // $output = format_result($_REQUEST["draw"], $selectedrows + 1, $selectedrows + 1, $device, "", "\r\n action=get_temporary_devices \r\n", 'ok');
          //  echo json_encode($searchPanes);
            
             $output = format_result_serverside($_REQUEST["draw"], $selectedrows + 1, $selectedrows + 1, $device, "", "\r\n action=get_temporary_devices \r\n", 'ok', $option, $searchPanes=  get_searchPanes_CB($device, 'contextbroker'));
        } else {
            $output = format_result($_REQUEST["draw"], 0, 0, null, 'Error: errors in reading data about devices. <br/>' .
                    generateErrorMessage($link), '\n\r Error: errors in reading data about devices.' . generateErrorMessage($link), 'ko');
        }
    }

    my_log($output);
    mysqli_close($link);
} 

else if ($action == "get_temporary_attributes") {
    $missingParams = missingParameters(array('id', 'contextbroker'));

    if (!empty($missingParams)) {
        $result["status"] = "ko";
        $result['msg'] = "Missing Parameters";
        $result["error_msg"] .= "Problem in get bulk attributes (Missing parameters: " . implode(", ", $missingParams) . " )";
        $result["log"] = "action=get_temporary_attributes - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
    } else {

        $id = mysqli_real_escape_string($link, $_REQUEST['id']);
        $cb = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);

        $q1 = "SELECT * FROM temporary_event_values WHERE device = '$id' AND cb = '$cb' AND (toDelete IS NULL OR toDelete != 'yes')";

        $r1 = mysqli_query($link, $q1);
      
         if (isset( $_REQUEST['value'])) {
               $type = mysqli_real_escape_string($link, $_REQUEST['type']);
        $service = mysqli_real_escape_string($link, $_REQUEST['service']);
        $servicePath = mysqli_real_escape_string($link, $_REQUEST['servicePath']);
        $version = mysqli_real_escape_string($link, $_REQUEST['version']);
             Loading_value($link, $id, $type, $cb, $service, $servicePath, $version, $result);
              // Loading_value($link, $id, 'SwitchingGroup', $cb, '', '', 'v2', $result);
               //echo $type;
            // echo json_encode($result);
                $result['value']=  $result['content'];
            }

        $attributes = array();
        if ($r1) {
            while ($row1 = mysqli_fetch_assoc($r1)) {
                $rec1 = array();
                $rec1["value_name"] = $row1["value_name"];
                $rec1["data_type"] = $row1["data_type"];
                $rec1["value_type"] = $row1["value_type"];
                $rec1["editable"] = $row1["editable"];
                $rec1["value_unit"] = $row1["value_unit"];
                $rec1["old_value_name"] = $row1["old_value_name"];
                $rec1["healthiness_criteria"] = $row1["healthiness_criteria"];
                if ($rec1["healthiness_criteria"] == "refresh_rate")
                    $rec1["healthiness_value"] = $row1["value_refresh_rate"];
                if ($rec1["healthiness_criteria"] == "different_values")
                    $rec1["healthiness_value"] = $row1["different_values"];
                if ($rec1["healthiness_criteria"] == "within_bounds")
                    $rec1["healthiness_value"] = $row1["value_bounds"];
                array_push($attributes, $rec1);
            }
            $result['status'] = 'ok';
            $result['content'] = $attributes;
            $result['log'] .= "\n\r action:get_device_attributes. access to " . $q1;
            
           
            
            
        } else {
            $result['status'] = 'ko'; // . $q1 . generateErrorMessage($link);
            $result['msg'] = 'Error: errors in reading data about devices. <br/>' . generateErrorMessage($link);
            $result['log'] .= '\n\naction:get_device_attributes. Error: errors in reading data about devices. ' . generateErrorMessage($link);
        }
    }

    my_log($result);
    mysqli_close($link);
} else if ($action == "get_all_temporary_attributes") {
    $missingParams = missingParameters(array('should_be_registered'));

    if (!empty($missingParams)) {
        $result["status"] = "ko";
        $result['msg'] = "Missing Parameters";
        $result["error_msg"] .= "Problem in get bulk attributes (Missing parameters: " . implode(", ", $missingParams) . " )";
        $result["log"] = "action=get_all_temporary_attributes - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
    } else {
        $usernameNotHashed = $username;
        $username = md5($username);
        //$q1 = "SELECT * FROM temporary_event_values WHERE toDelete IS NULL OR toDelete != 'yes'";
        $q1 = "SELECT * FROM temporary_event_values te JOIN temporary_devices td ON te.cb = td.contextbroker AND te.device = td.id WHERE td.username = '$username' AND td.organization='$organization' AND (te.toDelete IS NULL OR te.toDelete != 'yes')";

        $r1 = mysqli_query($link, $q1);

        $list_attributes = array();
        if ($r1) {
            $result["log"] = "\r\n action=get_all_temporary_attributes \r\n";
            while ($row1 = mysqli_fetch_assoc($r1)) {

                $rec1 = array();
                $rec1["contextbroker"] = $row1["cb"];
                $rec1["device"] = $row1["device"];
                $rec1["value_name"] = $row1["value_name"];
                $rec1["data_type"] = $row1["data_type"];
                $rec1["value_type"] = $row1["value_type"];
                $rec1["editable"] = $row1["editable"];
                $rec1["value_unit"] = $row1["value_unit"];
                $rec1["old_value_name"] = $row1["old_value_name"];
                $rec1["healthiness_criteria"] = $row1["healthiness_criteria"];
                if ($rec1["healthiness_criteria"] == "refresh_rate")
                    $rec1["healthiness_value"] = $row1["value_refresh_rate"];
                if ($rec1["healthiness_criteria"] == "different_values")
                    $rec1["healthiness_value"] = $row1["different_values"];
                if ($rec1["healthiness_criteria"] == "within_bounds")
                    $rec1["healthiness_value"] = $row1["value_bounds"];
                array_push($list_attributes, $rec1);
            }
            $result['status'] = 'ok';
            $result['content'] = $list_attributes;
            $result['log'] .= "\n\r action:get_all_temporary_attributes. access to " . $q1;
        } else {
            $result['status'] = 'ko';
            $result['msg'] = 'Error: errors in reading data about devices. <br/>' . generateErrorMessage($link);
            $result['log'] .= '\n\naction:get_all_temporary_attributes. Error: errors in reading data about devices. ' . generateErrorMessage($link);
        }
    }

    my_log($result);
    mysqli_close($link);
}
//TODO introduce enforcement for this part
else if ($action == "get_affected_devices_count") {
    $attributesIf = json_decode($_REQUEST['attributesIf']);

    if (count($attributesIf) != 0) {
        $usernameNotHashed = $username;
        $username = md5($username);

        /* 	if ($listAttributes==null) $merge=$listnewAttributes;
          else if ($listnewAttributes==null) $merge=$listAttributes;
          else $merge=array_merge($listAttributes,$listnewAttributes, 'compare_values');

         */
        $query = "SELECT count(*) as tot FROM temporary_devices";

        $a = 0;
        $where = 0;
        while ($a < count($attributesIf)) {
            $attIf = $attributesIf[$a];
            if ($attIf->field == "empty") {
                $a++;
                break;
            }
            if ($where == 0) {
                $query .= " WHERE ";
                $query .= $attIf->field;
                $where = 1;
            } else {
                $query .= " AND " . $attIf->field;
            }
            if ($attIf->operator == "IsNull") {
                $query .= " IS NULL";
            } else {
                if ($attIf->operator == "IsEqual") {
                    if ($attIf->value == "Empty" || empty($attIf->value)) {
                        $query .= " = ''";
                    } else {
                        $query .= " =  '" . $attIf->value . "'";
                    }
                } else if ($attIf->operator == "Contains") {
                    if ($attIf->value == "Empty" || empty($attIf->value)) {
                        $query .= " LIKE ''";
                    } else {
                        $query .= " LIKE  '%" . $attIf->value . "%'";
                    }
                } else if ($attIf->operator == "IsNotEqual") {
                    $query .= "<> '" . $attIf->value . "'";
                }
            }
            $a++;
        }

        $query .= " AND username =  '$username' AND organization = '$organization';";

        if (count($attributesIf) > 0) {
            $r = mysqli_query($link, $query);

            if ($r) {
                $row = mysqli_fetch_assoc($r);

                $result['status'] = 'ok';
                $result['content'] = $row['tot'];
                $result["msg"] .= "Selected ok " . $query;
            } else {
                $result['status'] = 'ko';
                $result['content'] = 0;
                $result['msg'] .= $query;
            }
        } else {
            $result['status'] = 'ok';
            $result['msg'] .= "";
            $result['content'] = 0;
        }
    } else {
        $result['status'] = 'ok';
        $result['msg'] .= "";
        $result['content'] = 0;
    }
    my_log($result);
    mysqli_close($link);
} else if ($action == "get_affected_devices") {
    $attributesIf = json_decode($_REQUEST['attributes']);
    $usernameNotHashed = $username;
    $username = md5($username);

    $a = 0;
    $query = "deleted IS null AND username = '$username' AND organization='$organization'";
    while ($a < count($attributesIf)) {
        $attIf = $attributesIf[$a];
        if ($attIf->field == "empty") {
            $a++;
            break;
        }
        $query .= " AND " . $attIf->field;

        if ($attIf->operator == "IsNull") {
            $query .= " IS NULL";
        } else {
            if ($attIf->operator == "IsEqual") {
                if ($attIf->value == "Empty" || empty($attIf->value)) {
                    $query .= " = ''";
                } else {
                    $query .= " =  '" . $attIf->value . "'";
                }
            } else if ($attIf->operator == "Contains") {
                if ($attIf->value == "Empty" || empty($attIf->value)) {
                    $query .= " LIKE  ''";
                } else {
                    $query .= " LIKE  '%" . $attIf->value . "%'";
                }
            } else if ($attIf->operator == "IsNotEqual") {
                $query .= "<> '" . $attIf->value . "'";
            }
        }
        $a++;
    }

    if (count($attributesIf) > 0) {

        $q = "SELECT contextBroker, id, devicetype, model, status, macaddress,frequency,kind, 
		protocol,format,latitude, longitude, visibility, k1, k2,producer, edge_gateway_type, edge_gateway_uri, validity_msg
	FROM temporary_devices";

        $r = create_datatable_data($link, $_REQUEST, $q, $query);

        $selectedrows = -1;
        if ($_REQUEST["length"] != -1) {
            $start = $_REQUEST['start'];
            $offset = $_REQUEST['length'];
            $tobelimited = true;
        } else {
            $tobelimited = false;
        }

        if ($r) {
            //$result['status'] = 'ok';
            //$result['content'] = array();
            $device = array();
            $result["log"] = "\r\n action=get_preview \r\n";

            while ($row = mysqli_fetch_assoc($r)) {
                $selectedrows++;
                if (!$tobelimited || ($tobelimited && $selectedrows >= $start && $selectedrows < ($start + $offset))) {
                    $rec = array();
                    $rec["contextbroker"] = $row["contextBroker"];
                    $rec["name"] = $row["id"];
                    $rec["devicetype"] = $row["devicetype"];
                    $rec["protocol"] = $row["protocol"];
                    $rec["format"] = $row["format"];
                    array_push($device, $rec);
                }
            }
            $output = format_result($_REQUEST["draw"], $selectedrows + 1, $selectedrows + 1, $device, "", "\r\n action=get_preview \r\n", 'ok');
        } else {
            $output = format_result($_REQUEST["draw"], 0, 0, null, 'Error: errors in reading data about devices. <br/>' .
                    generateErrorMessage($link), '\n\r Error: errors in reading data about devices.' . generateErrorMessage($link), 'ko');
        }
        $result = $output;
    }

    my_log($result);
    mysqli_close($link);
} else if ($action == "update_all_devices") {
    $attributesThen = json_decode($_REQUEST['attributesThen']);
    $attributesIf = json_decode($_REQUEST['attributesIf']);
    $usernameNotHashed = $username;
    $username = md5($username);

    $modelsdata = array();
    $queryModel = "SELECT * FROM model";
    $resModel = mysqli_query($link, $queryModel);
    if ($resModel) {
        while ($rowModel = mysqli_fetch_assoc($resModel)) {
            array_push($modelsdata, $rowModel);
        }
    }

    $datatypes = array();

    $queryDatatype = "SELECT * FROM data_types";
    $resData = mysqli_query($link, $queryDatatype);
    if ($resData) {
        while ($row = mysqli_fetch_assoc($resData)) {
            array_push($datatypes, $row["data_type"]);
        }
    }
    $valuetypes = array();
    $valueunits = array();

    retrieveFromDictionary("value%20type", $result_value_types);
    $array = $result_value_types["content"];
    for ($i = 0; $i < count($array); $i++) {
        array_push($valuetypes, $array[$i]->value);
    }

    retrieveFromDictionary("value%20unit", $result_value_units);
    $array = $result_value_units["content"];
    for ($i = 0; $i < count($array); $i++) {
        array_push($valueunits, $array[$i]->value);
    }

    $contextbroker = "";

    $query = "UPDATE temporary_devices";

    $a = 0;
    $set = 0;
    $logFields = "";
    while ($a < count($attributesThen)) {
        $attThen = $attributesThen[$a];
        $logFields .= $attThen->field . ", ";

        if ($attThen->field == "contextbroker") {
            $contextbroker = $attThen->valueThen;
        }

        if ($set == 0) {
            $query .= " SET ";
            if (strtoupper($attThen->valueThen) == "NULL") {
                $query .= $attThen->field . " = NULL";
            } else {
                $query .= $attThen->field . " = '" . $attThen->valueThen . "'";
            }
            $set = 1;
        } else {
            if (strtoupper($attThen->valueThen) == "NULL") {
                $query .= $attThen->field . " = NULL";
            } else {
                $query .= ", " . $attThen->field . " = '" . $attThen->valueThen . "'";
            }
        }
        $a++;
    }
    $logFields = substr($logFields, 0, strlen($logFields) - 2);

    $b = 0;
    $where = 0;

    while ($b < count($attributesIf)) {

        $attIf = $attributesIf[$b];
        if ($where == 0) {
            $query .= " WHERE ";
            $query .= $attIf->field;
            $where = 1;
        } else {
            $query .= " AND " . $attIf->field;
        }
        if ($attIf->operator == "IsNull") {
            $query .= " IS NULL";
        } else {
            if ($attIf->operator == "IsEqual") {
                $query .= "=  '" . $attIf->value . "'";
            } else if ($attIf->operator == "IsNotEqual") {
                $query .= "<> '" . $attIf->value . "'";
            } else if ($attIf->operator == "Contains") {
                $query .= " LIKE  '%" . $attIf->value . "%'";
            }
        }
        $b++;
    }
    $query .= " AND username =  '$username' AND organization = '$organization';";

    $r = mysqli_query($link, $query);
    if ($r) {
        logAction($link, $usernameNotHashed, 'bulk_update', 'update_all_devices', '', $organization, 'updated ' . $logFields . ' fields', 'success');
        $q1 = "SELECT * FROM temporary_devices";

        $a1 = 0;
        $where = 0;
        //acquisition of data for validate device function and color hilight
        while ($a1 < count($attributesIf)) {
            $attIf = $attributesIf[$a1];
            if ($where == 0) {
                $q1 .= " WHERE ";
                $q1 .= $attIf->field;
                $where = 1;
            } else {
                $q1 .= " AND " . $attIf->field;
            }
            if ($attIf->operator == "IsNull") {
                $q1 .= " IS NULL";
            } else {

                if ($attIf->field == "contextBroker" & $contextbroker != "") {
                    if ($attIf->operator == "IsEqual") {
                        $q1 .= "= '" . $contextbroker . "'";
                    } else if ($attIf->operator == "IsNotEqual") {
                        $q1 .= "<> '" . $contextbroker . "'";
                    } else if ($attIf->operator == "Contains") {
                        $q1 .= "= '" . $contextbroker . "'";
                    }
                } else {
                    if ($attIf->operator == "IsEqual") {
                        $q1 .= "=  '" . $attIf->value . "'";
                    } else if ($attIf->operator == "IsNotEqual") {
                        $q1 .= "<> '" . $attIf->value . "'";
                    } else if ($attIf->operator == "Contains") {
                        $q1 .= " LIKE '%" . $attIf->value . "%'";
                    }
                }
            }

            $a1++;
        }

        $q1 .= " AND username =  '$username' AND organization = '$organization';";
        $result["msg"] .= $q1;

        $r1 = mysqli_query($link, $q1);

        $resultDevices = array();

        if ($r1) {

            while ($row1 = mysqli_fetch_assoc($r1)) {
                if ($contextbroker == "") {
                    $contextbroker = $row1["contextBroker"];
                }
                $q2 = 'SELECT * FROM temporary_event_values WHERE device="' . $row1["id"] . '" AND cb = "' . $contextbroker . '" AND (toDelete IS NULL OR toDelete != "yes")';

                $r2 = mysqli_query($link, $q2);
                if ($r2) {
                    $devValues = array();
                    $values = array();

                    while ($row2 = mysqli_fetch_assoc($r2)) {
                        $hvalue = "";
                        if ($row2["healthiness_criteria"] == "refresh_rate")
                            $hvalue = $row2["value_refresh_rate"];
                        if ($row2["healthiness_criteria"] == "different_values")
                            $hvalue = $row2["different_values"];
                        if ($row2["healthiness_criteria"] == "within_bounds")
                            $hvalue = $row2["value_bounds"];

                        $devValues["value_name"] = $row2["value_name"];
                        $devValues["data_type"] = $row2["data_type"];
                        $devValues["value_type"] = $row2["value_type"];
                        $devValues["editable"] = $row2["editable"];
                        $devValues["value_unit"] = $row2["value_unit"];
                        $devValues["healthiness_criteria"] = $row2["healthiness_criteria"];
                        $devValues["healthiness_value"] = $hvalue;
                        array_push($values, $devValues);
                    }
                    //	$devValues= rtrim($devValues,',');
                    //	$devValues .= "]";
                    //	$updatedDevice='{"contextbroker": "'.$row1["contextBroker"].'", "name": "'.$row1["id"].'", "devicetype": "'.$row1["devicetype"].'", "model": "'.$row1["model"].'","macaddress": "'.$row1["macaddress"].'", "frequency": "'.$row1["frequency"].'", "kind": "'.$row1["kind"].'", "protocol": "'.$row1["protocol"]. '", "format": "'.$row1["format"].'", "latitude": "' .$row1["latitude"].'", "longitude":"'.$row1["longitude"].'", "visibility":"'.$row1["visibility"].'", "k1":"'.$row1["k1"].'", "k2": "'.$row1["k2"].'", "producer":"'.$row1["producer"].'", "edge_gateway_type": "' .$row1["edge_gateway_type"].'", "edge_gateway_uri":"'.$row1["edge_gateway_uri"].'", "deviceValues": '. $devValues.'}';
                    $updatedDevice["contextbroker"] = $row1["contextBroker"];
                    $updatedDevice["name"] = $row1["id"];
                    $updatedDevice["devicetype"] = $row1["devicetype"];
                    $updatedDevice["model"] = $row1["model"];
                    $updatedDevice["macaddress"] = $row1["macaddress"];
                    $updatedDevice["frequency"] = $row1["frequency"];
                    $updatedDevice["kind"] = $row1["kind"];
                    $updatedDevice["protocol"] = $row1["protocol"];
                    $updatedDevice["format"] = $row1["format"];
                    $updatedDevice["latitude"] = $row1["latitude"];
                    $updatedDevice["longitude"] = $row1["longitude"];
                    $updatedDevice["visibility"] = $row1["visibility"];
                    $updatedDevice["k1"] = $row1["k1"];
                    $updatedDevice["k2"] = $row1["k2"];
                    $updatedDevice["producer"] = $row1["producer"];
                    $updatedDevice["edge_gateway_type"] = $row1["edge_gateway_type"];
                    $updatedDevice["edge_gateway_uri"] = $row1["edge_gateway_uri"];
                    $updatedDevice["deviceValues"] = array();
                    $updatedDevice["deviceValues"] = $values;
                    array_push($resultDevices, $updatedDevice);
                }
            }

            while (!empty($resultDevices)) {

                $device = array_pop($resultDevices);
                $verification = verifyDevice($device, $modelsdata, $datatypes, $valuetypes, $valueunits);
                $validity = "valid";
                if ($verification["isvalid"] == 0) {
                    $validity = "invalid";
                }
                $sql = 'UPDATE temporary_devices SET validity_msg =\'' . trim($verification["message"]) . '\', status = "' . $validity . '" WHERE contextbroker = "' . $device["contextbroker"] . '" AND id ="' . $device["name"] . '";';

                $r = mysqli_query($link, $sql);
                if ($r) {
                    $result['status'] = 'ok';
                    $result["msg"] .= "temporary update done";
                    $result["content"] .= $sql;
                } else {
                    $result['status'] = 'ko';
                    $result["msg"] .= "Error during the update 4" . generateErrorMessage($link);
                    $result["log"] .= "\r\n Error during update";
                }
            }

            $result['status'] = 'ok';
            $result["msg"] .= "temporary update while empty";
        } else {
            $result['status'] = 'ko';
            $result["msg"] .= "Error during the update 5" . generateErrorMessage($link);
            $result["log"] .= "\r\n Error during update";
        }
    } else {
        $result['status'] = 'ko';
        $result['msg'] .= "Error during the update 6. " . generateErrorMessage($link);

        logAction($link, $usernameNotHashed, 'bulk_update', 'update_all_devices', '', $organization, 'Error: bulk update has failed ', 'faliure');
    }
    my_log($result);
    mysqli_close($link);
} else if ($action == "get_affected_values_count") {
    
    $attributesIf = json_decode($_REQUEST['attributesIf']);
    $usernameNotHashed = $username;
    $username = md5($username);

    if (count($attributesIf) != 0) {
        $query = "SELECT count(*) as tot FROM temporary_event_values te, temporary_devices td  WHERE te.cb = td.contextbroker AND te.device = td.id AND (te.toDelete IS NULL OR te.toDelete != 'yes')";

        //for healthiness value
        $hv = 0;
        $hcriteria = "";
        while ($hv < count($attributesIf)) {
            $attHv = $attributesIf[$hv];
            if ($attHv->field == "healthiness_criteria") {
                if ($attHv->value == "refresh_rate") {
                    $hcriteria = "value_refresh_rate";
                } else if ($attHv->value == "within_bounds") {
                    $hcriteria = "value_bounds";
                } else {
                    $hcriteria = $attHv->value;
                }
                break;
            }
            $hv++;
        }
        //end

        $a = 0;
        while ($a < count($attributesIf)) {
            $attIf = $attributesIf[$a];
            if ($attIf->field == "empty") {
                $a++;
                break;
            }
            if ($attIf->field == "healthiness_value") {
                $query .= " AND " . $hcriteria;
            } else {
                $query .= " AND " . $attIf->field;
            }
            if ($attIf->operator == "IsNull") {
                $query .= " IS NULL";
            } else {

                if ($attIf->operator == "IsEqual") {
                    if ($attIf->value == "Empty" || empty($attIf->value)) {
                        $query .= " = ''";
                    } else {
                        $query .= " =  '" . $attIf->value . "'";
                    }
                }
                if ($attIf->operator == "Contains") {
                    if ($attIf->value == "Empty" || empty($attIf->value)) {
                        $query .= " LIKE ''";
                    } else {
                        $query .= " LIKE  '%" . $attIf->value . "%'";
                    }
                } else if ($attIf->operator == "IsNotEqual") {
                    $query .= "<> '" . $attIf->value . "'";
                }
            }

            $a++;
        }

        $query .= " AND username =  '$username' AND organization = '$organization';";

        if (count($attributesIf) > 0) {

            $r = mysqli_query($link, $query);
            if ($r) {

                $row = mysqli_fetch_assoc($r);

                $result['status'] = 'ok';
                $result['content'] = $row['tot'];
                $result["msg"] .= "Selected ok " . $query;
            } else {
                $result['status'] = 'ko';
                $result["msg"] .= $query . " error" . generateErrorMessage($link);
                $result["log"] .= "\r\n Error during selection";
            }
        } else {
            $result['status'] = 'ok';
            $result['content'] = 0;
        }
    } else {
        $result['status'] = 'ok';
        $result['content'] = 0;
    }

    my_log($result);
    mysqli_close($link);
} else if ($action == "update_all_values") {
    $attributesThen = json_decode($_REQUEST['attributesThen']);
    $attributesIf = json_decode($_REQUEST['attributesIf']);

    $usernameNotHashed = $username;
    $username = md5($username);

    $modelsdata = array();
    $queryModel = "SELECT * FROM model";
    $resModel = mysqli_query($link, $queryModel);
    if ($resModel) {
        while ($rowModel = mysqli_fetch_assoc($resModel)) {
            array_push($modelsdata, $rowModel);
        }
    }

    $datatypes = array();

    $queryDatatype = "SELECT * FROM data_types";
    $resData = mysqli_query($link, $queryDatatype);
    if ($resData) {
        while ($row = mysqli_fetch_assoc($resData)) {
            array_push($datatypes, $row["data_type"]);
        }
    }

    $valuetypes = array();
    $valueunits = array();

    retrieveFromDictionary("value%20type", $result_value_types);
    $array = $result_value_types["content"];
    for ($i = 0; $i < count($array); $i++) {
        array_push($valuetypes, $array[$i]->value);
    }

    retrieveFromDictionary("value%20unit", $result_value_units);
    $array = $result_value_units["content"];
    for ($i = 0; $i < count($array); $i++) {
        array_push($valueunits, $array[$i]->value);
    }

    $hv = 0;
    $hcriteriaIf = "";
    while ($hv < count($attributesIf)) {
        $attHv = $attributesIf[$hv];
        if ($attHv->field == "healthiness_criteria") {
            if ($attHv->value == "refresh_rate") {
                $hcriteriaIf = "value_refresh_rate";
            } else if ($attHv->value == "within_bounds") {
                $hcriteriaIf = "value_bounds";
            } else {
                $hcriteriaIf = $attHv->value;
            }
            break;
        }
        $hv++;
    }

    $hv = 0;
    $hcriteriaThen = "";
    while ($hv < count($attributesThen)) {
        $attHv = $attributesThen[$hv];
        if ($attHv->field == "healthiness_criteria") {
            if ($attHv->valueThen == "refresh_rate") {
                $hcriteriaThen = "value_refresh_rate";
            } else if ($attHv->valueThen == "within_bounds") {
                $hcriteriaThen = "value_bounds";
            } else {
                $hcriteriaThen = $attHv->valueThen;
            }
            break;
        }
        $hv++;
    }

    $query = "UPDATE temporary_event_values te JOIN temporary_devices td ON te.cb = td.contextbroker AND te.device = td.id ";

    $a = 0;
    $set = 0;
    $logFields = "";
    while ($a < count($attributesThen)) {
        $attThen = $attributesThen[$a];
        $logFields .= $attThen->field . ", ";

        if ($set == 0) {
            $query .= " SET ";
            if ($attThen->field == "healthiness_value") {
                $query .= $hcriteriaThen . " = '" . $attThen->valueThen . "'";
            } else {
                if (strtoupper($attThen->valueThen) == "NULL") {
                    $query .= $attThen->field . " = NULL";
                } else {
                    $query .= $attThen->field . " = '" . $attThen->valueThen . "'";
                }
            }
            $set = 1;
        } else {
            if (strtoupper($attThen->valueThen) == "NULL") {
                $query .= $attThen->field . " = NULL";
            } else {
                $query .= ", " . $attThen->field . " = '" . $attThen->valueThen . "'";
            }
        }
        $a++;
    }
    $b = 0;
    $where = 0;
    while ($b < count($attributesIf)) {
        $attIf = $attributesIf[$b];

        if ($attIf->field == "healthiness_value") {
            $attIf->field = $hcriteriaIf;
        }
        if ($where == 0) {
            $query .= " WHERE ";
            $query .= $attIf->field;
            $where = 1;
        } else {
            $query .= " AND " . $attIf->field;
        }

        if ($attIf->operator == "IsNull") {
            $query .= " IS NULL";
        } else {
            if ($attIf->operator == "IsEqual") {
                $query .= " =  '" . $attIf->value . "'";
            } else if ($attIf->operator == "IsNotEqual") {
                $query .= "<> '" . $attIf->value . "'";
            } else if ($attIf->operator == "Contains") {
                $query .= " LIKE  '%" . $attIf->value . "%'";
            }
        }

        $b++;
    }
    $logFields = substr($logFields, 0, strlen($logFields) - 2);

    $query .= " AND username =  '$username' AND organization = '$organization' AND (te.toDelete IS NULL OR te.toDelete != 'yes');";

    $r = mysqli_query($link, $query);

    if ($r) {
        logAction($link, $usernameNotHashed, 'bulk_update', 'update_all_values', '', $organization, 'updated ' . $logFields . " fields", 'success');

        $q1 = "SELECT * FROM temporary_devices";
        $a1 = 0;
        $where = 0;
        //acquisition of data for validate device function and color hilight
        while ($a1 < count($attributesIf)) {
            $attIf = $attributesIf[$a1];
            if ($where == 0) {
                $q1 .= " WHERE ";
                if ($attIf->field == 'device') {
                    $q1 .= ' id ';
                } else {
                    $q1 .= $attIf->field;
                }
                $where = 1;
            } else {
                $q1 .= " AND ";
                if ($attIf->field == 'device') {
                    $q1 .= ' id ';
                } else {
                    $q1 .= $attIf->field;
                }
                //$q1 .=  $attIf->field;
            }
            if ($attIf->operator == "IsNull") {
                $q1 .= " IS NULL";
            } else {
                if ($attIf->operator == "IsEqual") {
                    $q1 .= "=  '" . $attIf->value . "'";
                } else if ($attIf->operator == "IsNotEqual") {
                    $q1 .= "<> '" . $attIf->value . "'";
                } else if ($attIf->operator == "Contains") {
                    $q1 .= " LIKE '%" . $attIf->value . "%'";
                }
            }

            $a1++;
        }

        $q1 .= " AND username =  '$username' AND organization = '$organization';";
        // $q1 .= " ;";

        $r1 = mysqli_query($link, $q1);

        $resultDevices = array();
        if ($r1) {

            while ($row1 = mysqli_fetch_assoc($r1)) {
                $q2 = 'SELECT * FROM temporary_event_values WHERE device="' . $row1["id"] . '" AND cb = "' . $row1["contextBroker"] . '" AND (toDelete IS NULL OR toDelete != "yes")';
                $r2 = mysqli_query($link, $q2);
                $updatedDevice = array();

                if ($r2) {
                    $devValues = array();
                    $values = array();

                    while ($row2 = mysqli_fetch_assoc($r2)) {
                        $hvalue;
                        if ($row2["healthiness_criteria"] == "refresh_rate")
                            $hvalue = $row2["value_refresh_rate"];
                        if ($row2["healthiness_criteria"] == "different_values")
                            $hvalue = $row2["different_values"];
                        if ($row2["healthiness_criteria"] == "within_bounds")
                            $hvalue = $row2["value_bounds"];
                        ////	$att= '{"value_name": "'.$row2["value_name"].'", "value_type": "'.$row2["value_type"].'", "editable": "'. $row2["editable"]. '",  "value_unit": "'.$row2["value_unit"].'", "healthiness_criteria":"'.$row2["healthiness_criteria"].'", "healthiness_value":"'.$hvalue.'"}';
                        //	$devValues .=  $att.",";
                        $devValues["value_name"] = $row2["value_name"];
                        $devValues["value_type"] = $row2["value_type"];
                        $devValues["data_type"] = $row2["data_type"];
                        $devValues["editable"] = $row2["editable"];
                        $devValues["value_unit"] = $row2["value_unit"];
                        $devValues["healthiness_criteria"] = $row2["healthiness_criteria"];
                        $devValues["healthiness_value"] = $hvalue;
                        array_push($values, $devValues);
                    }
                    //	$devValues= rtrim($devValues,',');
                    //	$devValues .= "]";
                    //	$updatedDevice='{"contextbroker": "'.$row1["contextBroker"].'", "name": "'.$row1["id"].'", "devicetype": "'.$row1["devicetype"].'", "model": "'.$row1["model"].'","macaddress": "'.$row1["macaddress"].'", "frequency": "'.$row1["frequency"].'", "kind": "'.$row1["kind"].'", "protocol": "'.$row1["protocol"]. '", "format": "'.$row1["format"].'", "latitude": "' .$row1["latitude"].'", "longitude":"'.$row1["longitude"].'", "visibility":"'.$row1["visibility"].'", "k1":"'.$row1["k1"].'", "k2": "'.$row1["k2"].'", "producer":"'.$row1["producer"].'", "edge_gateway_type": "' .$row1["edge_gateway_type"].'", "edge_gateway_uri":"'.$row1["edge_gateway_uri"].'", "deviceValues": '. $devValues.'}';
                    $updatedDevice["contextbroker"] = $row1["contextBroker"];
                    $updatedDevice["name"] = $row1["id"];
                    $updatedDevice["devicetype"] = $row1["devicetype"];
                    $updatedDevice["model"] = $row1["model"];
                    $updatedDevice["macaddress"] = $row1["macaddress"];
                    $updatedDevice["frequency"] = $row1["frequency"];
                    $updatedDevice["kind"] = $row1["kind"];
                    $updatedDevice["protocol"] = $row1["protocol"];
                    $updatedDevice["format"] = $row1["format"];
                    $updatedDevice["latitude"] = $row1["latitude"];
                    $updatedDevice["longitude"] = $row1["longitude"];
                    $updatedDevice["visibility"] = $row1["visibility"];
                    $updatedDevice["k1"] = $row1["k1"];
                    $updatedDevice["k2"] = $row1["k2"];
                    $updatedDevice["producer"] = $row1["producer"];
                    $updatedDevice["edge_gateway_type"] = $row1["edge_gateway_type"];
                    $updatedDevice["edge_gateway_uri"] = $row1["edge_gateway_uri"];
                    $updatedDevice["deviceValues"] = array();
                    $updatedDevice["deviceValues"] = $values;
                    array_push($resultDevices, $updatedDevice);
                }
            }

            while (count($resultDevices) > 0) {
                $device = array_pop($resultDevices);
                $verification = verifyDevice($device, $modelsdata, $datatypes, $valuetypes, $valueunits);
                $validity = "valid";
                if ($verification["isvalid"] == 0) {
                    $validity = "invalid";
                }
                $result["validity_message"] .= trim($verification["message"]);
                $result["valid"] .= $validity;
                $sql = 'UPDATE temporary_devices SET validity_msg =\'' . trim($verification["message"]) . '\', status = "' . $validity . '" WHERE contextbroker = "' . $device["contextbroker"] . '" AND id ="' . $device["name"] . '";';
                $r = mysqli_query($link, $sql);
                if ($r) {
                    $result['status'] = 'ok';
                    $result["msg"] .= "Update correctly executed";
                } else {
                    $result['status'] = 'ko';
                    $result["msg"] .= "Error during the update 1" . generateErrorMessage($link);
                    $result["log"] .= "\r\n Error during update";
                }
            }
        } else {
            $result["msg"] .= "Empty Result";
            $result["log"] .= "\r\n No data";
            $result['status'] = 'ok';
            $result['query'] .= $q1;
        }
    } else {
        logAction($link, $usernameNotHashed, 'bulk_update', 'update_all_values', '', $organization, 'Error: bulk update of values has failed ', 'faliure');

        $result['status'] = 'ko';
        $result["msg"] .= "Error during the update 2" . generateErrorMessage($link);
        $result["log"] .= "\r\n Error during update";
    }
    my_log($result);
    mysqli_close($link);
} else if ($action == "get_affected_values") {
    $attributesIf = json_decode($_REQUEST['attributes']);

    $usernameNotHashed = $username;
    $username = md5($username);

    $hv = 0;
    $hcriteria = "";
    while ($hv < count($attributesIf)) {
        $attHv = $attributesIf[$hv];
        if ($attHv->field == "healthiness_criteria") {
            if ($attHv->value == "refresh_rate") {
                $hcriteria = "value_refresh_rate";
            } else if ($attHv->value == "within_bounds") {
                $hcriteria = "value_bounds";
            } else {
                $hcriteria = $attHv->value;
            }
            break;
        }
        $hv++;
    }

    $a = 0;
    $query = "te.cb = td.contextbroker AND te.device = td.id AND deleted IS null AND username = '$username' AND organization='$organization' AND (te.toDelete IS NULL OR te.toDelete != 'yes')";
    while ($a < count($attributesIf)) {
        $attIf = $attributesIf[$a];
        if ($attIf->field == "empty") {
            $a++;
            break;
        }
        if ($attIf->field == "healthiness_value") {
            $query .= " AND " . $hcriteria;
        } else {
            $query .= " AND " . $attIf->field;
        }
        if ($attIf->operator == "IsNull") {
            $query .= " IS NULL";
        } else {
            if ($attIf->operator == "IsEqual") {
                if ($attIf->value == "Empty" || empty($attIf->value)) {
                    $query .= " = ''";
                } else {
                    $query .= " =  '" . $attIf->value . "'";
                }
            } else if ($attIf->operator == "Contains") {
                if ($attIf->value == "Empty" || empty($attIf->value)) {
                    $query .= " LIKE ''";
                } else {
                    $query .= " LIKE  '%" . $attIf->value . "%'";
                }
            } else if ($attIf->operator == "IsNotEqual") {
                $query .= "<> '" . $attIf->value . "'";
            }
        }
        $a++;
    }
    if (count($attributesIf) > 0) {
//echo "qui";
       // $q = " SELECT * FROM temporary_event_values te, temporary_devices td ";
        
        $q2="SELECT * FROM temporary_devices td INNER JOIN temporary_event_values te limit 300";

        $r = create_datatable_data($link, $_REQUEST, $q2, $query);

        $selectedrows = -1;
        if ($_REQUEST["length"] != -1) {
            $start = $_REQUEST['start'];
            $offset = $_REQUEST['length'];
            $tobelimited = true;
        } else {
            $tobelimited = false;
        }

        if ($r) {
            //$result['status'] = 'ok';
            //$result['content'] = array();
            $value = array();
            $result["log"] = "\r\n action=get_preview \r\n";
            while ($row = mysqli_fetch_assoc($r)) {
                $selectedrows++;
                if (!$tobelimited || ($tobelimited && $selectedrows >= $start && $selectedrows < ($start + $offset))) {
                    $rec = array();
                    $rec["contextbroker"] = $row["cb"];
                    $rec["name"] = $row["device"];
                    $rec["value_name"] = $row["value_name"];
                    $rec["data_type"] = $row["data_type"];
                    $rec["value_type"] = $row["value_type"];
                    $rec["value_unit"] = $row["value_unit"];
                    $rec["healthiness_criteria"] = $row["healthiness_criteria"];

                    if ($rec["healthiness_criteria"] == "refresh_rate") {
                        $rec["healthiness_value"] = $row["value_refresh_rate"];
                    } else if ($rec["healthiness_criteria"] == "within_bounds") {
                        $rec["healthiness_value"] = $row["value_bounds"];
                    } else {
                        $rec["healthiness_value"] = $row["different_values"];
                    }
                    array_push($value, $rec);
                }
            }
            $output = format_result($_REQUEST["draw"], $selectedrows + 1, $selectedrows + 1, $value, "", "\r\n action=get_preview \r\n", 'ok');
        } else {
            $output = format_result($_REQUEST["draw"], 0, 0, null, 'Error: errors in reading data about devices. <br/>' .
                    generateErrorMessage($link), '\n\r Error: errors in reading data about devices.' . generateErrorMessage($link), 'ko');
        }
        $result = $output;
    }

    my_log($result);
    mysqli_close($link);
} else if ($action == "get_fields") {
    $fieldIf = mysqli_real_escape_string($link, $_REQUEST['fieldIf']);

    $sql = 'SELECT * FROM fieldType WHERE fieldName ="' . $fieldIf . '";';

    $r = mysqli_query($link, $sql);
    if ($r) {
        $rec = array();
        while ($row = mysqli_fetch_assoc($r)) {
            if ($row["fieldName"] == 'value_type') {
                retrieveFromDictionary("value%20type", $result_value_types);
                //$r1 = mysqli_query($link, $row["query"]);
                $htmlstring = "<select class= \"fieldSelectIf\" >";
                // $array = array();
                $array = $result_value_types["content"];
                for ($i = 0; $i < count($array); $i++) {
                    $htmlstring .= "<option value=" . $array[$i]->value . ">" . $array[$i]->label . "</option>";
                }

                $rec["fieldsHtml"] = $htmlstring . "</select>";
                $rec["query"] = $row["query"];
                $rec["fieldName"] = $row["fieldName"];
                $rec["menuType"] = $row["menuType"];
            } else if ($row["fieldName"] == 'value_unit') {
                retrieveFromDictionary("value%20unit", $result_value_units);
                //$r1 = mysqli_query($link, $row["query"]);
                $htmlstring = "<select class= \"fieldSelectIf\" >";
                // $array = array();
                $array = $result_value_units["content"];
                for ($i = 0; $i < count($array); $i++) {
                    $htmlstring .= "<option value=" . $array[$i]->value . ">" . $array[$i]->label . "</option>";
                }

                $rec["fieldsHtml"] = $htmlstring . "</select>";
                $rec["query"] = $row["query"];
                $rec["fieldName"] = $row["fieldName"];
                $rec["menuType"] = $row["menuType"];
            } else {
                if ($row["query"] == NULL) {
                    $rec["query"] = $row["query"];
                    $rec["fieldName"] = $row["fieldName"];
                    $rec["menuType"] = $row["menuType"];
                    $rec["fieldsHtml"] = $row["fieldsHtml"];
                } else {
                    $r1 = mysqli_query($link, $row["query"]);
                    $htmlstring = "<select class= \"fieldSelectIf\" >";
                    while ($row1 = mysqli_fetch_assoc($r1)) {
                       // echo json_encode($row1);
                        if ($row["menuType"] == "select") {
                            $htmlstring .= "<option value=" . $row1["contextBroker"] . ">" . $row1["contextBroker"] . "</option>";
                        }
                    }
                    if ($row["fieldName"] == "model") {
                        $htmlstring .= "<option value=\"custom\">custom</option>";
                    }
                    $rec["fieldsHtml"] = $htmlstring . "</select>";
                    $rec["query"] = $row["query"];
                    $rec["fieldName"] = $row["fieldName"];
                    $rec["menuType"] = $row["menuType"];
                }

                $autocomplete = array();
                if ($row["autocomplete"] != NULL) {

                    $r2 = mysqli_query($link, $row["autocomplete"]);
                    while ($row2 = mysqli_fetch_assoc($r2)) {
                        array_push($autocomplete, $row2["id"]);
                    }
                    $rec["autocomplete"] = $autocomplete;
                } else {
                    $rec["autocomplete"] = null;
                }
            }
        }
        $result['status'] = 'ok';
        $result['content'] = array();
        array_push($result['content'], $rec);
        $result['log'] .= "\n\r  get field type";
    } else {
        $result['status'] = 'ko';
        $result["msg"] .= "Error during the update 3" . generateErrorMessage($link);
        $result["log"] .= "\r\n Error during update";
    }

    my_log($result);
    mysqli_close($link);
} else {
    $result['status'] = 'ko';
    $result['msg'] = 'invalid action ' . $action;
    $result['log'] = 'invalid action ' . $action;
    my_log($result);
}

function verifyDevice($deviceToverify, $modelsdata, $datatypes, $valuetypes, $valueunits) {
    $msg = "";
    $regexpMAC = '/([a-fA-F0-9]{2}[:|\-]?){6}/';
    $answer = array();
    $isvalid = 1;
    $answer["isvalid"] = 1;
    $answer["message"] = "Your device is valid";

    if (!isset($deviceToverify["name"]) || strlen($deviceToverify["name"]) < 5) {
        $msg .= "-name is mandatory, of 5 characters at least.";
    }
    if (!isset($deviceToverify["devicetype"]) || $deviceToverify["devicetype"] == "") {
        $msg .= "-devicetype is mandatory.";
    }
    if (!isset($deviceToverify["macaddress"]) && !preg_match($regexpMAC, $deviceToverify["macaddress"])) {
        $msg .= "-macaddress is mandatory and Mac format should be Letter (A-F) and number (eg. 3D:F2:C9:A6:B3:4F).";
    }
    if (!isset($deviceToverify["frequency"]) || $deviceToverify["frequency"] == "" || !is_finite($deviceToverify["frequency"])) {
        $msg .= "-frequency is mandatory, and should be numeric.";
    }
    if (!isset($deviceToverify["kind"]) || $deviceToverify["kind"] == "") {
        $msg .= "-kind is mandatory.";
    }
    if (!isset($deviceToverify["protocol"]) || $deviceToverify["protocol"] == "") {
        $msg .= "-protocol is mandatory.";
    }
    if (!isset($deviceToverify["format"]) || $deviceToverify["format"] == "") {
        $msg .= "-format is mandatory.";
    }
    if (!isset($deviceToverify["latitude"]) || !isLatitude($deviceToverify["latitude"])) {
        $msg .= "-Latitude is mandatory, with the correct numeric format.";
    }
    if (!isset($deviceToverify["longitude"]) || !isLongitude($deviceToverify["longitude"])) {
        $msg .= "-Longitude is mandatory, with the correct numeric format.";
    }
    // if (!isset($deviceToverify["k1"]) || $deviceToverify["k1"] == "") {
    // 	$msg .= "-k1 is mandatory.";
    // }
    // if (!isset($deviceToverify["k2"]) || $deviceToverify["k2"] == "") {
    // 	$msg .= "-k2 is mandatory.";
    // }

    if (strlen($msg) > 0) {
        $isvalid = 0;
    }

    if (sizeof(array_keys($deviceToverify["deviceValues"])) < 1) {
        $isvalid = 0;
        $msg .= "-Your device should at least have 1 attributes.";
    }
    if ($deviceToverify["model"] != "custom") {
        for ($i = 0; $i < sizeof(array_keys($modelsdata)); $i++) {
            if ($modelsdata[$i]["name"] != $deviceToverify["model"]) {
                continue;
            }
            $modelAttributes = json_decode($modelsdata[$i]["attributes"], true);
            if (sizeof(array_keys($modelAttributes)) != sizeof(array_keys($deviceToverify["deviceValues"]))) {

                $isvalid = 0;
                $msg .= "-Your device has different number of attributes than the selected model ";
            } else {

                for ($j = 0; $j < sizeof(array_keys($deviceToverify["deviceValues"])); $j++) {
                    $found = 0;

                    for ($l = 0; $l < sizeof(array_keys($modelAttributes)); $l++) {
                        if ($modelAttributes[$l]["value_name"] == $deviceToverify["deviceValues"][$j]["value_name"]) {
                            $found = 1;
                            $msg_attr_detail = "";

                            if ($modelAttributes[$l]["value_type"] != $deviceToverify["deviceValues"][$l]["value_type"]) {
                                $msg_attr_detail .= " value type,";
                            }
                            if ($modelAttributes[$l]["data_type"] != $deviceToverify["deviceValues"][$l]["data_type"]) {
                                $msg_attr_detail .= " data type,";
                            }
                            if ($modelAttributes[$l]["editable"] != $deviceToverify["deviceValues"][$l]["editable"]) {
                                $msg_attr_detail .= " editable,";
                            }
                            if ($modelAttributes[$l]["healthiness_criteria"] != $deviceToverify["deviceValues"][$l]["healthiness_criteria"]) {
                                $msg_attr_detail .= " healthiness criteria,";
                            }
                            if ($modelAttributes[$l]["healthiness_value"] != $deviceToverify["deviceValues"][$l]["healthiness_value"]) {
                                $msg_attr_detail .= " healthiness value,";
                            }
                            if ($modelAttributes[$l]["value_unit"] != $deviceToverify["deviceValues"][$l]["value_unit"]) {
                                $msg_attr_detail .= " value unit,";
                            }

                            if (strlen($msg_attr_detail) > 0) {
                                $isvalid = 0;
                                $msg .= "The attribute " . $deviceToverify["deviceValues"][$j]["value_name"] . " has the details:" . $msg_attr_detail . " not compatible with its model.";
                            } else {
                                array_splice($modelAttributes, $l, 1);
                            }
                        } //end if
                    } //end for l

                    if ($found == 0) {
                        $isvalid = 0;
                        $msg .= "-The device attribute name " . $deviceToverify["deviceValues"][$j]["value_name"] . " do not comply with its model.";
                    }
                } //end for j
            } //end else

            $h3 = ($modelsdata[$i]["edgegateway_type"] == $deviceToverify["edge_gateway_type"]) ||
                    (
                    (!isset($modelsdata[$i]["edgegateway_type"]) || $modelsdata[$i]["edgegateway_type"] == "" || (($modelsdata[$i]["edgegateway_type"] == null) &&
                    (!isset($deviceToverify["edge_gateway_type"]) || $deviceToverify["edge_gateway_type"] == "" || $deviceToverify["edge_gateway_type"] == null))));

            if ($modelsdata[$i]["contextbroker"] != $deviceToverify["contextbroker"]) {
                $isvalid = 0;
                $msg .= "-The device property: context broker does not comply with its model.";
            }
            if ($modelsdata[$i]["devicetype"] != $deviceToverify["devicetype"]) {
                $isvalid = 0;
                $msg .= "-The device property: type does not comply with its model.";
            }
            if (!$h3) {
                $isvalid = 0;
                $msg .= "-The device property: edge gateway type does not comply with its model.";
            }
            if ($modelsdata[$i]["format"] != $deviceToverify["format"]) {
                $isvalid = 0;
                $msg .= "-The device property: format does not comply with its model.";
            }
            if ($modelsdata[$i]["frequency"] != $deviceToverify["frequency"]) {
                $isvalid = 0;
                $msg .= "-The device property: frequency does not comply with its model.";
            }
            if ($modelsdata[$i]["kind"] != $deviceToverify["kind"]) {
                $isvalid = 0;
                $msg .= "-The device property: kind does not comply with its model.";
            }
            if ($modelsdata[$i]["producer"] != $deviceToverify["producer"]) {
                $isvalid = 0;
                $msg .= "-The device property: producer does not comply with its model.";
            }
            if ($modelsdata[$i]["protocol"] != $deviceToverify["protocol"]) {
                $isvalid = 0;
                $msg .= "-The device property: protocol does not comply with its model.";
            }
        } //end for models data
    } //end if not custom	
    else {
        $all_attr_msg = "";
        $all_attr_status = "true";
        $healthiness_criteria_options = array();
        array_push($healthiness_criteria_options, "refresh_rate");
        array_push($healthiness_criteria_options, "different_values");
        array_push($healthiness_criteria_options, "within_bounds");

        for ($i = 0; $i < sizeof(array_keys($deviceToverify["deviceValues"])); $i++) {
            $v = $deviceToverify["deviceValues"][$i];

            if (!isset($v)) {
                continue;
            }

            $attr_status = true;
            $attr_msg = "";

            $empty_name = 0;

            if (!isset($v["value_name"]) || $v["value_name"] == "") {
                $attr_status = 0;
                $empty_name = true;
            }
            //set default values

            if (!isset($v["data_type"]) || $v["data_type"] == "" || !in_array($v["data_type"], $datatypes)) {
                $attr_status = 0;
                $attr_msg = $attr_msg . " data_type";
            }
            if (!isset($v["value_unit"]) || $v["value_unit"] == "" || !in_array($v["value_unit"], $valueunits)) {
                $attr_status = 0;
                $attr_msg .= " value_unit";
            }

            if (!isset($v["value_type"]) || $v["value_type"] == "" || !in_array($v["value_type"], $valuetypes)) {
                $attr_status = 0;
                $attr_msg .= " value_type";
            }
            if ($v["editable"] != "0" && $v["editable"] != "1") {
                $attr_status = 0;
                $attr_msg .= " editable";
            }

            if (!isset($v["healthiness_criteria"]) || $v["healthiness_criteria"] == "" || !in_array($v["healthiness_criteria"], $healthiness_criteria_options)) {
                $attr_status = 0;
                $attr_msg .= " healthiness_criteria";
            }
            if (!isset($v["healthiness_value"]) || $v["healthiness_value"] == "") {
                $attr_status = 0;
                $attr_msg .= " healthiness_value";
            }

            if ($attr_status == 0) {

                $all_attr_status = 0;
                if ($empty_name) {
                    $all_attr_msg .= "The attribute name cannot be empty";
                    if ($attr_msg != "") {
                        $all_attr_msg .= ", other errors in: " . $attr_msg;
                    }
                } else {
                    $all_attr_msg .= "For the attribute: " . $v["value_name"] . ", error in: " . $attr_msg;
                }
            }
        }

        if (!$all_attr_status) {
            $isvalid = 0;
            $msg .= " -" . $all_attr_msg;
        }
    }

    //answer.isvalid=true;
    if ($isvalid) {
        $answer["isvalid"] = true;
        return $answer;
    } else {
        $answer["message"] = $msg;
        $answer["isvalid"] = 0;
        return $answer;
    }
    return $answer;
}

function isLatitude($lat) {
    return is_finite($lat) && abs($lat) <= 90;
}

function isLongitude($lng) {
    return is_finite($lng) && abs($lng) <= 180;
}
