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
error_reporting(E_ERROR);

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
    $result['log'] = 'associationRulesApi.php action not present';
    my_log($result);
    mysqli_close($link);
    exit();
}

$headers = get_request_headers();
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
        error_log("---- associationRulesApi.php:" . (microtime(true) - $mctime));
        $accessToken = $tkn->access_token;
    }
}
if (empty($accessToken)) {
    $result["status"] = "ko";
    $result['msg'] = "Access Token not present";
    $result["error_msg"] .= "Access Token not present";
    $result["log"] = "associationRulesApi.php AccessToken not present\r\n";
    my_log($result);
    mysqli_close($link);
    exit();
}

//retrieve username, organization and role from the accetoken
//TODO avoid passing all the parameters for LDAP
get_user_info($accessToken, $username, $organization, $oidc, $role, $result, $ldapBaseName, $ldapServer, $ldapPort, $ldapAdminName, $ldapAdminPwd);

//TODO move to contextbroker apis
if ($action == "get_cb_details") {

    $missingParams = missingParameters(array('cb'));

    if (!empty($missingParams)) {
        $result["status"] = "ko";
        $result['msg'] = "Missing Parameters";
        $result["error_msg"] .= "Problem in get cb details (Missing parameters: " . implode(", ", $missingParams) . " )";
        $result["log"] = "action=get_cb_details - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
    } else {

        $contextbroker = mysqli_real_escape_string($link, $_REQUEST['cb']);
        if (mysqli_real_escape_string($link, $_REQUEST['freq'])) {

            $freq = mysqli_real_escape_string($link, $_REQUEST['freq']);
            $q2 = "UPDATE `contextbroker` SET `req_frequency`='$freq', `timestampstatus`=NOW() WHERE name= '$contextbroker' AND organization = '$organization';";
            $r2 = mysqli_query($link, $q2);
        } else {
            
            $q2 = "UPDATE `contextbroker` SET  `timestampstatus`=NOW() WHERE name= '$contextbroker' AND organization = '$organization';";
            $r2 = mysqli_query($link, $q2);
        }
    }

    $q = "SELECT * FROM contextbroker WHERE name= '$contextbroker' AND organization = '$organization';";

    $r = mysqli_query($link, $q);

    $context = array();
    if ($r) {
        while ($row = mysqli_fetch_assoc($r)) {
            $rec = array();
            $rec["contextbroker"] = $row["name"];
            $rec["protocol"] = $row["protocol"];
            $rec["ip"] = $row["ip"];
            $rec["accessLink"] = $row["accesslink"];
            $rec["accessport"] = $row["accessport"];
            $rec["protocol"] = $row["protocol"];
            $rec["username"] = md5($username);
            $rec["apikey"] = $row["apikey"];
            $rec["kind"] = $row["kind"];
            $rec["path"] = $row["path"];

            array_push($context, $rec);
        }
        $result['status'] = 'ok';
        $result['content'] = $context;
    } else {
        $result["status"] = 'ko';
        $result["msg"] .= "faliure";
        $result["log"] .= "\n Problem in get count temporary devices" . generateErrorMessage($link);
    }

   
    my_log($result);
    mysqli_close($link);
}
//TODO move to contextbroker APIS
else if ($action == "get_multiple_cb_details") {
    $missingParams = missingParameters(array('cb'));

    if (!empty($missingParams)) {
        $result["status"] = "ko";
        $result['msg'] = "Missing Parameters";
        $result["error_msg"] .= "Problem in get_multiple_cb_details (Missing parameters: " . implode(", ", $missingParams) . " )";
        $result["log"] = "action=get_multiple_cb_details - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
    } else {
        $contextbrokers = json_decode($_REQUEST['cb']);

        $i = 0;
        $context = array();

        while ($i < count($contextbrokers)) {
            $cb = $contextbrokers[$i];
            $q = "SELECT * FROM contextbroker WHERE name= '$cb' AND organization = '$organization';";
            $r = mysqli_query($link, $q);

            if ($r) {
                while ($row = mysqli_fetch_assoc($r)) {
                    $rec = array();
                    $rec["contextbroker"] = $row["name"];
                    $rec["protocol"] = $row["protocol"];
                    $rec["ip"] = $row["ip"];
                    $rec["accessLink"] = $row["accesslink"];
                    $rec["port"] = $row["port"];
                    $rec["protocol"] = $row["protocol"];
                    $rec["username"] = md5($username);
                    $rec["apikey"] = $row["apikey"];
                    $rec["kind"] = $row["kind"];
                    $rec["path"] = $row["path"];

                    array_push($context, $rec);
                }
                $i++;
            } else {
                $result["status"] = 'ko';
                $result["msg"] .= "faliure";
                $result["log"] .= "\n Problem in get count temporary devices" . generateErrorMessage($link);
            }
            $result['status'] = 'ok';
            $result['content'] = $context;
        }
    }

    my_log($result);
    mysqli_close($link);
} else if ($action == 'suggest_associations') {
    $missingParams = missingParameters(array('value'));

    if (!empty($missingParams)) {
        $result["status"] = "ko";
        $result['msg'] = "Missing Parameters";
        $result["error_msg"] .= "Problem in suggest associations (Missing parameters: " . implode(", ", $missingParams) . " )";
        $result["log"] = "action=suggest_associations - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
    } else {
        $value = mysqli_real_escape_string($link, $_REQUEST['value']);
        $username = md5($username);

        $value2 = $value;
        $value = 'output_' . $value;
        $query = "SELECT * FROM association_rules  WHERE $value <> '' AND $value IS NOT NULL AND lift>1 ORDER BY lift DESC";
        $r = mysqli_query($link, $query);

        $rules = array();
        $eraseDuplicate = array();
        $sameDevice = array();

        if ($r) {
            while ($row = mysqli_fetch_assoc($r)) {
                $associations = array();
                $associations['input_data_type'] = $row['input_data_type'];
                $associations['input_value_type'] = $row['input_value_type'];
                $associations['input_value_unit'] = $row['input_value_unit'];
                $associations['input_context_broker'] = $row['input_context_broker'];
                $associations['input_device_type'] = $row['input_device_type'];
                $associations['input_model'] = $row['input_model'];
                $associations['input_protocol'] = $row['input_protocol'];
                $associations['input_format'] = $row['input_format'];
                $associations['output_data_type'] = $row['output_data_type'];
                $associations['output_value_type'] = $row['output_value_type'];
                $associations['output_value_unit'] = $row['output_value_unit'];
                $associations['output_context_broker'] = $row['output_context_broker'];
                $associations['output_device_type'] = $row['output_device_type'];
                $associations['output_model'] = $row['output_model'];
                $associations['output_protocol'] = $row['output_protocol'];
                $associations['output_format'] = $row['output_format'];
                $associations['support'] = $row['support'];
                $associations['lift'] = $row['lift'];
                array_push($eraseDuplicate, $associations);
            }

            $associations = array_unique($eraseDuplicate, SORT_REGULAR);

            foreach ($associations as $a) {
                $listAffected = "SELECT count(*) as tot, td.id , te.value_name  FROM temporary_event_values te, temporary_devices td 
				WHERE td.contextbroker = te.cb AND td.id = te.device AND ($value2 IS NULL OR $value2 = '') AND (te.toDelete IS NULL OR te.toDelete != 'yes')";

                $query_where = "";
                foreach ($a as $key => $q) {
                    $q = strtolower($q);
                    if ($q != '' or!strcmp($q, 'null') === 0) {
                        $key2 = $key;
                        if ((strcmp(substr($key, 0, 7), "output_") === 0) or (strcmp($key, "support") === 0) or (strcmp($key, "lift") === 0)) {
                            continue;
                        }
                        $query_where .= " AND " . substr($key, 6, strlen($key)) . " = '" . $q . "' ";
                    }
                }
                $listAffected .= $query_where . " AND td.username = '$username' AND td.organization = '$organization' GROUP BY td.id, te.value_name;";
                /*
                  AND (te.data_type = '".$associations['input_data_type']."' OR te.data_type IS NULL) AND (te.value_type ='". $associations['input_value_type'] ."' OR te.value_type IS NULL)AND (te.value_unit ='". $associations['input_value_unit']."'
                  OR te.value_unit IS NULL) AND	(te.cb = '".$associations['input_context_broker']."' OR te.cb IS NULL) AND (td.device_type ='". $associations['input_device_type'] ."' OR te.device_type IS NULL)
                  AND td.model = '".$associations['input_model']."' OR td.model IS NULL) AND (td.protocol ='". $associations['input_protocol']."' OR td.protocol IS NULL) AND (td.format = '".$arrociations['input_format']. "'OR td.format IS NULL);";
                 */
                $numberRowsAffected = mysqli_query($link, $listAffected);
                if ($numberRowsAffected) {
                    while ($res = mysqli_fetch_assoc($numberRowsAffected)) {
                        $strDevValue = $res['id'] . $res['value_name'];
                        if ($res['tot'] > 0 && !in_array($strDevValue, $sameDevice)) {
                            array_push($sameDevice, $strDevValue);
                            array_push($rules, $a);
                        }
                    }
                }
            }
            $rules = array_unique($rules);
            $result["status"] = 'ok';
            $result['content'] = $rules;
            $result["msg"] .= "Association rules correctly retrieved";
        } else {
            $result["status"] = 'ko';
            $result["msg"] .= "\n Problems during the retrieval of the association rules";
            $result["log"] .= "\r\n Problem in retrieving the rules" . generateErrorMessage($link);
        }
    }

    my_log($result);
    mysqli_close($link);
} else if ($action == "get_rules_affecting_count") {
    $missingParams = missingParameters(array('attributesIf', 'value'));

    if (!empty($missingParams)) {
        $result["status"] = "ko";
        $result['msg'] = "Missing Parameters";
        $result["error_msg"] .= "Problem in insert device (Missing parameters: " . implode(", ", $missingParams) . " )";
        $result["log"] = "action=insert - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
    } else {
        $attributesIf = json_decode($_REQUEST['attributesIf']);
        $value = mysqli_real_escape_string($link, $_REQUEST['value']);

        $usernameNotHashed = $username;
        $username = md5($username);

        if (count($attributesIf) != 0) {
            $query = "SELECT count(*) as tot FROM temporary_event_values te, temporary_devices td  WHERE te.cb = td.contextbroker AND te.device = td.id";

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
                            $query .= " LIKE  '%" . $attIf->value . "%'";
                        }
                    } else if ($attIf->operator == "IsNotEqual") {
                        $query .= "<> '" . $attIf->value . "'";
                    }
                }

                $a++;
            }

            $query .= " AND username =  '$username' AND organization = '$organization' AND ($value = '' OR $value IS NULL) AND (te.toDelete IS NULL OR te.toDelete != 'yes') ;";

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
    }

    my_log($result);
    mysqli_close($link);
} else if ($action == "get_rules_affecting_data") {
    $missingParams = missingParameters(array('attributes', 'value'));

    if (!empty($missingParams)) {
        $result["status"] = "ko";
        $result['msg'] = "Missing Parameters";
        $result["error_msg"] .= "Problem in insert device (Missing parameters: " . implode(", ", $missingParams) . " )";
        $result["log"] = "action=insert - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
    } else {
        $attributesIf = json_decode($_REQUEST['attributes']);
        $value = mysqli_real_escape_string($link, $_REQUEST['value']);

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
        $query = "te.cb = td.contextbroker AND te.device = td.id AND deleted IS null AND organization = '$organization' AND username = '$username' AND ($value = '' OR $value IS NULL) AND (te.toDelete IS NULL OR te.toDelete != 'yes') ";

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
                        $query .= " LIKE  '%" . $attIf->value . "%'";
                    }
                } else if ($attIf->operator == "IsNotEqual") {
                    $query .= "<> '" . $attIf->value . "'";
                }
            }
            $a++;
        }
        if (count($attributesIf) > 0) {

            $q = "SELECT * FROM temporary_event_values te, temporary_devices td";
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
                $output = format_result($_REQUEST["draw"], 0, 0, null, 'Error: errors in reading data about devices. <br/>' . generateErrorMessage($link), '\n\r Error: errors in reading data about devices.' . generateErrorMessage($link), 'ko');
            }
            $result = $output;
        }
    }

    my_log($result);
    mysqli_close($link);
} else if ($action == 'apply_rules') {
    $missingParams = missingParameters(array('attributesThen', 'attributesIf'));

    if (!empty($missingParams)) {
        $result["status"] = "ko";
        $result['msg'] = "Missing Parameters";
        $result["error_msg"] .= "Problem in apply rules (Missing parameters: " . implode(", ", $missingParams) . " )";
        $result["log"] = "action=apply_rules - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
    } else {
        $attributesThen = json_decode($_REQUEST['attributesThen']);
        $attributesIf = json_decode($_REQUEST['attributesIf']);

        $deviceElements = array("device_type", "protocol", "model", "format", "contextbroker");
        $valueElements = array("data_type", "value_type", "value_unit", "contextbroker");

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

        $queryDevices = "UPDATE temporary_devices";
        $queryValues = "UPDATE temporary_event_values";
        $a = 0;
        $setDev = 0;
        $setVal = 0;
        $logFields = "";

        while ($a < count($attributesThen)) {
            $attThen = $attributesThen[$a];

            if (in_array($attThen->field, $deviceElements)) {
                $logFields .= $attThen->field . ", ";

                if ($attThen->field == "contextbroker") {
                    $contextbroker = $attThen->valueThen;
                }

                if ($setDev == 0) {
                    $queryDevices .= " SET ";
                    if (strtoupper($attThen->valueThen) == "NULL") {
                        $queryDevices .= $attThen->field . " = NULL";
                    } else {
                        $queryDevices .= $attThen->field . " = '" . $attThen->valueThen . "'";
                    }
                    $setDev = 1;
                } else {
                    if (strtoupper($attThen->valueThen) == "NULL") {
                        $queryDevices .= $attThen->field . " = NULL";
                    } else {
                        $queryDevices .= ", " . $attThen->field . " = '" . $attThen->valueThen . "'";
                    }
                }
            } else if (in_array($attThen->field, $valueElements)) {
                $logFields .= $attThen->field . ", ";

                if ($attThen->field == "contextbroker") {
                    $contextbroker = $attThen->valueThen;
                }

                if ($setVal == 0) {
                    $queryValues .= " SET ";

                    if (strcmp($attThen->field, "contextbroker") == 0) {
                        $attThen->field = "cb";
                    }
                    if (strtoupper($attThen->valueThen) == "NULL") {
                        $queryValues .= $attThen->field . " = NULL";
                    } else {
                        $queryValues .= $attThen->field . " = '" . $attThen->valueThen . "'";
                    }
                    $setVal = 1;
                } else {
                    if (strtoupper($attThen->valueThen) == "NULL") {
                        $queryValues .= $attThen->field . " = NULL";
                    } else {
                        $queryValues .= ", " . $attThen->field . " = '" . $attThen->valueThen . "'";
                    }
                }
            }
            $a++;
        }
        $logFields = substr($logFields, 0, strlen($logFields) - 2);

        $b = 0;
        $whereDev = 0;
        $whereVal = 0;

        while ($b < count($attributesIf)) {

            $attIf = $attributesIf[$b];

            if (in_array($attIf->field, $deviceElements)) {
                if ($whereDev == 0) {
                    $queryDevices .= " WHERE ";
                    $queryDevices .= $attIf->field;
                    $whereDev = 1;
                } else {
                    $queryDevices .= " AND " . $attIf->field;
                }
                if ($attIf->operator == "IsNull") {
                    $queryDevices .= " IS NULL";
                } else {
                    if ($attIf->operator == "IsEqual") {
                        $queryDevices .= "=  '" . $attIf->value . "'";
                    } else if ($attIf->operator == "IsNotEqual") {
                        $queryDevices .= "<> '" . $attIf->value . "'";
                    }
                }
            } else if (in_array($attIf, $valueElements)) {
                if ($whereVal == 0) {
                    $queryValues .= " WHERE ";
                    $queryValues .= $attIf->field;
                    $whereVal = 1;
                } else {
                    $queryValues .= " AND " . $attIf->field;
                }
                if ($attIf->operator == "IsNull") {
                    $queryValues .= " IS NULL";
                } else {
                    if ($attIf->operator == "IsEqual") {
                        $queryValues .= "=  '" . $attIf->value . "'";
                    } else if ($attIf->operator == "IsNotEqual") {
                        $queryValues .= "<> '" . $attIf->value . "'";
                    }
                }
            }
            $b++;
        }
        if ($setVal == 0) {
            $queryValues = "";
        } else {
            if ($whereVal == 0) {
                $queryValues .= " WHERE username =  '$username' AND organization = '$organization' AND (toDelete IS NULL OR toDelete != 'yes') ;";
            } else {
                $queryValues .= " AND username =  '$username' AND organization = '$organization' AND (toDelete IS NULL OR toDelete != 'yes') ;";
            }
        }

        if ($setDev == 0) {
            $queryDevices = "";
        } else {
            if ($whereDev == 0) {
                $queryDevices .= " WHERE username =  '$username' AND organization = '$organization';";
            } else {
                $queryDevices .= " AND username =  '$username' AND organization = '$organization';";
            }
        }
        if ($queryValues != "") {
            $exec = mysqli_query($link, $queryValues);
        }
        if ($queryDevices != "") {
            $r = mysqli_query($link, $queryDevices);

            if ($r) {
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
                                $q1 .= "=  '" . $contextbroker . "'";
                            } else if ($attIf->operator == "IsNotEqual") {
                                $q1 .= "<> '" . $contextbroker . "'";
                            }
                        } else {
                            if ($attIf->operator == "IsEqual") {
                                $q1 .= "=  '" . $attIf->value . "'";
                            } else if ($attIf->operator == "IsNotEqual") {
                                $q1 .= "<> '" . $attIf->value . "'";
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
                        $q2 = 'SELECT * FROM temporary_event_values WHERE device="' . $row1["id"] . '" AND cb = "' . $contextbroker . '" AND (te.toDelete IS NULL OR te.toDelete != "yes") ';

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
                } else {
                    $result['status'] = 'ko';
                    $result["msg"] .= "Error during the update 5" . generateErrorMessage($link);
                    $result["log"] .= "\r\n Error during update";
                }
            } else {
                $result['status'] = 'ko';
                $result['msg'] .= "Error during the update 6" . generateErrorMessage($link);
            }
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
