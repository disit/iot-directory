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
include ('../config.php');
include ('common.php');
$link = mysqli_connect($host, $username, $password) or die("failed to connect to server !!");
mysqli_select_db($link, $dbname);
error_reporting(E_ERROR | E_NOTICE);

if (!$link->set_charset("utf8")) {
    exit();
}

if (isset($_REQUEST['action']) && !empty($_REQUEST['action'])) {
    $action = $_REQUEST['action'];
    $action_lwr = strtolower($action);
} else {
    $result['status'] = 'ko';
    $result['msg'] = 'action not present';
    $result['error_msg'] = 'action not present';
    $result['log'] = 'model.php action not present';
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
$oidc->providerConfigParam(array('token_endpoint' => $keycloakHostUri . '/auth/realms/master/protocol/openid-connect/token',
    'userinfo_endpoint' => $keycloakHostUri . '/auth/realms/master/protocol/openid-connect/userinfo'));

$accessToken = "";
if (isset($_REQUEST['nodered'])) {
    if ((isset($_REQUEST['token'])) && ($_REQUEST['token'] != 'undefined'))
        $accessToken = $_REQUEST['token'];
} else {
    if (isset($_REQUEST['token'])) {
        $mctime = microtime(true);
        $tkn = $oidc->refreshToken($_REQUEST['token']);
        error_log("---- model.php:" . (microtime(true) - $mctime));
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
if(is_array($organization)) {
    if(isset($_COOKIE['organization'])) {
        if(array_search($_COOKIE['organization'], $organization)!==false) {
            $organization = $_COOKIE['organization'];
        } else {
            error_log("WARNING model.php invalid organization COOKIE ".$_COOKIE['organization']." for multi org user $username");
            $organization = $organization[0];
        }
    } else {
        $organization = $organization[0];
        error_log("WARNING model.php missing organization COOKIE for multi org user $username");
    }
}

if ($result["status"] != "ok") {
    $result["status"] = "ko";
    $result['msg'] = "Cannot retrieve user information";
    $result["error_msg"] .= "Problem in insert context broker (Cannot retrieve user information)";
    $result["log"] = "model.php action=insert - error Cannot retrieve user information\r\n";
    my_log($result);
    mysqli_close($link);
    exit();
}

if ($action_lwr == "get_fiware_model" || $action_lwr == "get_fiwire_model") {
    if (!($GLOBALS['FIWAREon'])) {
        $result = array(
            "data" => [],
            "msg" => '',
            "log" => "\r\n action=get_fiware_model is disabled\r\n",
            "status" => 'ok'
        );

        my_log($result);
        return;
    }

    if (isset($_REQUEST['length']))
        $length = mysqli_real_escape_string($link, $_REQUEST['length']);
    else
        $length = -1;
    $start = 1; //default is 1 but should throw an error
    if (($length != -1) && (isset($_REQUEST['start'])))
        $start = mysqli_real_escape_string($link, $_REQUEST['start']);
    if (isset($_REQUEST['draw']))
        $draw = mysqli_real_escape_string($link, $_REQUEST['draw']);
    else
        $draw = 1;
    if (!isset($_REQUEST['columns']))
        $_REQUEST["columns"] = array();
    if (isset($_REQUEST['select']))
        $selection = json_decode($_REQUEST['select']);
    else
        $selection = array();


    $selectedrows = -1;
    if ($length != -1) {
        $offset = $length;
        $tobelimited = true;
    } else {
        $tobelimited = false;
    }

    if (isset($_REQUEST['id'])) {
        $id = mysqli_real_escape_string($link, $_REQUEST['id']);
        $version = mysqli_real_escape_string($link, $_REQUEST['version']);
        $domain = mysqli_real_escape_string($link, $_REQUEST['domain']);
        $subdomain = mysqli_real_escape_string($link, $_REQUEST['subdomain']);
        $q = "SELECT * from iotdb.raw_schema_model where model='$id' and domain='$domain'and subdomain='$subdomain' and version ='$version' ;";
    } else {
        $q = "SELECT domain, subdomain,model,version,attributes, subnature from iotdb.raw_schema_model;";
    }




    $r = mysqli_query($link, $q);
    if ($r) {
        $data = array();

        while ($row = mysqli_fetch_assoc($r)) {
            $selectedrows++;
            if (!$tobelimited || ($tobelimited && $selectedrows >= $start && $selectedrows < ($start + $offset))) {
                $rec = array();
                $rec["domain"] = $row["domain"];
                $rec["subdomain"] = $row["subdomain"];
                $rec["model"] = $row["model"];
                $rec["version"] = $row["version"];
                $rec["attributes"] = $row["attributes"];
                $rec["subnature"] = $row["subnature"];
                array_push($data, $rec);
            }
        }

        $result = format_result($draw, $selectedrows + 1, $selectedrows + 1, $data, "", "\r\n action=get_fiware_model \r\n", 'ok');

        logAction($link, $username, 'FIWARE Model', 'get_fiware_model', '', $organization, '', 'success');
    } else {
        logAction($link, $username, 'FIWARE Model', 'get_fiware_model', '', $organization, 'Error: errors in reading data about fiware models.', 'faliure');
        $result = format_result($draw, 0, 0, $data, 'Error: errors in reading data about fiware models. <br/>' . generateErrorMessage($link), '\n\r Error: errors in reading data about fiware models.' . generateErrorMessage($link), 'ko');
    }

    my_log($result);
    mysqli_close($link);
} else if ($action == "get_model") {
    $missingParams = missingParameters(array('name'));

    if (!empty($missingParams)) {
        $result["status"] = "ko";
        $result['msg'] = "Missing Parameters";
        $result["error_msg"] .= "Problem in getting_model (Missing parameters: " . implode(", ", $missingParams) . " )";
        $result["log"] = "action=get_model - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
    } else {
        $name = mysqli_real_escape_string($link, $_REQUEST['name']);

        //id management
        //get_model
        $q = "SELECT m.id,m.name,m.description,m.devicetype,m.kind,m.producer,m.frequency,m.policy,m.attributes,m.link,m.contextbroker,m.protocol,
	                m.format,m.healthiness_criteria,m.healthiness_value,m.k1,m.k2,m.kgenerator,m.edgegateway_type,m.organization,
    	            m.visibility,m.subnature,m.static_attributes,m.service,m.servicePath,m.hlt,cb.organization as cb_organization
        	        FROM model m JOIN contextbroker cb on m.contextbroker = cb.name WHERE m.name = '$name'";
        $r = mysqli_query($link, $q);
        if (!$r) {
            $result["status"] = "ko";
            $result['msg'] = "Unrecognized model";
            $result["error_msg"] .= "Problem in getting model (Unrecognized model)";
            $result["log"] = "action=get_model - error Unrecognized model\n";
        } else {
            $row = mysqli_fetch_assoc($r);
            $obj_organization = $row["organization"];
            $eId = $obj_organization . ":" . $name;

            if ((count($row) == 0) || (!enforcementRights($username, $accessToken, $role, $eId, 'ModelID', 'read', $result))) {
                $result["status"] = "ko";
                $result['msg'] = "Not ownership or enough right to getting model";
                $result["error_msg"] .= "Problem in getting model (Not ownership or enough right to getting model)";
                $result["log"] .= "action=get_model - error Not ownership or enough right to getting model\r\n";
            } else {
                $result["status"] = "ok";
                $result["content"] = $row;
                $result["log"] = "action=get_model:" . $name . " \r\n";
            }
        }
    }

    my_log($result);
    mysqli_close($link);
} else if ((($action == "get_all_models")) || ($action == "get_all_models_simple")) {
    get_all_models($username, $organization, $role, $accessToken, $link, $result);

    my_log($result);
    mysqli_close($link);
}
//TODO can be unified with get_all_models
else if ($action == "get_all_models_DataTable") {
    $missingParams = missingParameters(array());

    if (!empty($missingParams)) {
        $result["status"] = "ko";
        $result['msg'] = "Missing Parameters";
        $result["error_msg"] .= "Problem in getting models (Missing parameters: " . implode(", ", $missingParams) . " )";
        $result["log"] = "action=get_all_models_DataTable - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
    } else {
        //default value if called from outside
        if (isset($_REQUEST['length']))
            $length = mysqli_real_escape_string($link, $_REQUEST['length']);
        else
            $length = -1;
        $start = 1; //default is 1 but should throw an error
        if (($length != -1) && (isset($_REQUEST['start'])))
            $start = mysqli_real_escape_string($link, $_REQUEST['start']);
        if (isset($_REQUEST['draw']))
            $draw = mysqli_real_escape_string($link, $_REQUEST['draw']);
        else
            $draw = 1;
        if (!isset($_REQUEST['columns']))
            $_REQUEST["columns"] = array();

        getOwnerShipObject($accessToken, "ModelID", $result);
        getDelegatedObject($accessToken, $username, "ModelID", $result);

        $q = "SELECT * FROM model";
        $r = create_datatable_data($link, $_REQUEST, $q, '');
        $selectedrows = -1;
        if ($length != -1) {
            $offset = $length;
            $tobelimited = true;
        } else {
            $tobelimited = false;
        }

        if ($r) {
            $data = array();
            while ($row = mysqli_fetch_assoc($r)) {
                $idTocheck = $row["organization"] . ":" . $row["name"];
                $isSameOrg = ($row["organization"] == $organization);
                $isOwner = (isset($result["keys"][$idTocheck]) && $result["keys"][$idTocheck]["owner"] == $username);
                $isDelegatedPersonal = (isset($result["delegation"][$idTocheck]) && $result["delegation"][$idTocheck]["kind"] != "anonymous");
                $isPublic = ($row["visibility"] == 'public') ||
                        (isset($result["delegation"][$idTocheck]) && $result["delegation"][$idTocheck]["kind"] == "anonymous");
                if (
                        ($role == 'RootAdmin') || ($role == 'ToolAdmin') ||
                        $isPublic ||
                        ($isSameOrg && ($isDelegatedPersonal || $isOwner))
                ) {

                    $selectedrows++;
                    if (!$tobelimited || ($tobelimited && $selectedrows >= $start && $selectedrows < ($start + $offset))) {

                        if (($isOwner && ($role !== 'RootAdmin') && ($role !== 'ToolAdmin')) ||
                                ($isOwner && (($role === 'RootAdmin') || ($role === 'ToolAdmin')))) {
                            //it's mine
                            if ($row["visibility"] == "public") {
                                $row["visibility"] = "MyOwnPublic";
                            } else {
                                if (isset($result["delegation"][$idTocheck]) && $result["delegation"][$idTocheck]["kind"] == "anonymous")
                                    $row["visibility"] = "MyOwnPublic";
                                else
                                    $row["visibility"] = "MyOwnPrivate";
                            }
                        } else {//it's not mine
                            if ($isPublic) {//it's delegated as public
                                $row["visibility"] = 'public';
                            } else if ($isDelegatedPersonal) {//it's delegated personally
                                $row["visibility"] = 'delegated';
                            } else {
                                $row["visibility"] = $row["visibility"];
                            }
                        }

                        $row["owner"] = '';
                        if (isset($result["keys"][$idTocheck]))
                            $row["owner"] = $result["keys"][$idTocheck]["owner"];
                        array_push($data, $row);
                    }
                }
            }

            $output = format_result($draw, $selectedrows + 1, $selectedrows + 1, $data, "", "\r\n action=get_all_models_DataTable \r\n", 'ok');
        logAction($link, $username, 'model', 'get_all_models_DataTable', '', $organization, '', 'success');
        } else {
            $output = format_result($draw, 0, 0, null, 'Error: errors in reading data about model. <br/>' .
                    generateErrorMessage($link), '\n\r Error: errors in reading data about model.' . generateErrorMessage($link), 'ko');
            logAction($link, $username, 'model', 'get_all_models_DataTable', '', $organization, 'Error: errors in reading data about model.', 'faliure');
        }
    }

    my_log($output);
    mysqli_close($link);
} else if ($action == "insert") {
    $missingParams = missingParameters(array('name', 'description', 'contextbroker', 'type', 'kind', 'format', 'frequency', 'kgenerator', 'hc', 'hv', 'attributes', 'producer'));

    if (!empty($missingParams)) {
        $result["status"] = "ko";
        $result['msg'] = "Missing Parameters";
        $result["error_msg"] .= "Problem in insert model (Missing parameters: " . implode(", ", $missingParams) . " )";
        $result["log"] = "action=insert - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
    } else {
        $name = mysqli_real_escape_string($link, $_REQUEST['name']);
        $description = mysqli_real_escape_string($link, $_REQUEST['description']);
        $type = mysqli_real_escape_string($link, $_REQUEST['type']);
        if (isset($_REQUEST['kind']))
            $kind = mysqli_real_escape_string($link, $_REQUEST['kind']);
        else
            $kind = "sensor";
        $producer = mysqli_real_escape_string($link, $_REQUEST['producer']);
        if (isset($_REQUEST['frequency']))
            $frequency = mysqli_real_escape_string($link, $_REQUEST['frequency']);
        else
            $frequency = "600";
        if (isset($_REQUEST['kgenerator']))
            $kgenerator = mysqli_real_escape_string($link, $_REQUEST['kgenerator']);
        else
            $kgenerator = "normal";
        if (isset($_REQUEST['edgegateway_type']))
            $edgegateway_type = mysqli_real_escape_string($link, $_REQUEST['edgegateway_type']);
        else
            $edgegateway_type = "";
        $contextbroker = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
        $format = mysqli_real_escape_string($link, $_REQUEST['format']);
        $hc = mysqli_real_escape_string($link, $_REQUEST['hc']);
        $hv = mysqli_real_escape_string($link, $_REQUEST['hv']);
        $listAttributes = mysqli_real_escape_string($link, $_REQUEST['attributes']);
        if (isset($_REQUEST['subnature']))
            $subnature = mysqli_real_escape_string($link, $_REQUEST['subnature']);
        else
            $subnature = "";
        if (isset($_REQUEST['static_attributes']))
            $staticAttributes = mysqli_real_escape_string($link, $_REQUEST['static_attributes']);
        else
            $staticAttributes = "[]";
        if (isset($_REQUEST['service']))
            $service = mysqli_real_escape_string($link, $_REQUEST['service']);
        else
            $service = "";
        if (isset($_REQUEST['servicePath']))
            $servicePath = mysqli_real_escape_string($link, $_REQUEST['servicePath']);
        else
            $servicePath = "";
        if (isset($_REQUEST['HLT']))
            $HLT = mysqli_real_escape_string($link, $_REQUEST['HLT']);
        else
            $HLT = "";

        $protocol = getProtocol($contextbroker, $link);

        if (empty($protocol)) {//it also ensure the contextbroker name is valid
            $result["status"] = "ko";
            $result['msg'] = "Unrecognized contextbroker/protocol";
            $result["error_msg"] .= "Problem in insert model (Unrecognized contextbroker/protocol)";
            $result["log"] = "action=insert - error Unrecognized contextbroker/protocol\r\n";
        } else {
            //id management
            $eId = $organization . ":" . $name;

            checkRegisterOwnerShipObject($accessToken, 'ModelID', $result);

            if ($result["status"] == 'ok') {

                //TODO check if needed
                $syntaxRes = 0;
                if ($protocol == 'ngsi w/MultiService') {
                    $syntaxRes = servicePathSyntaxCheck($servicePath);
                    $service = "'$service'";
                    $servicePath = "'$servicePath'";
                } else {
                    $service = "NULL";
                    $servicePath = "NULL";
                }

                if ($syntaxRes == 0) {

                    $q = "INSERT INTO model(name, description, devicetype, kind, producer, frequency, contextbroker, protocol, format, 
							healthiness_criteria, healthiness_value, kgenerator, attributes, edgegateway_type, organization, visibility, subnature, static_attributes, service, servicePath, hlt)
							VALUES('$name', '$description', '$type', '$kind', '$producer', '$frequency', '$contextbroker', '$protocol', '$format', '$hc', '$hv', '$kgenerator', 
							'$listAttributes', '$edgegateway_type', '$organization', 'private', '$subnature', '$staticAttributes', $service, $servicePath, '$HLT')";
                    $r = mysqli_query($link, $q);

                    if(mysqli_errno($link) == 1062){
                        $result["error_msg"] .= "A model with this name already exist, please chose another one.";
                    }

                    if ($r) {
                        $result["status"] = 'ok';
                        $result["log"] = "action=insert " . $q . " \r\n";
                        $ownmsg = array();
                        $ownmsg["elementId"] = $eId;
                        $ownmsg["elementName"] = $eId;
                        $ownmsg["elementUrl"] = $eId;
                        $ownmsg["elementType"] = "ModelID";
			            $ownmsg["elementDetails"] = array();


                        $param = str_replace( array( '\\', '"' , '[',']' ), '', $staticAttributes);
                        $param=explode(",",$param);
                        foreach ($param as $key => $value){
                            if($value =="http://www.disit.org/km4city/schema#isCertified"){
                                $ownmsg["elementDetails"]["Certified"] = "true";
                            }
                        }



                        registerOwnerShipObject($ownmsg, $accessToken, 'ModelID', $result);
                        if ($result["status"] == 'ok') {
                            logAction($link, $username, 'model', 'insert', $name, $organization, 'Registering the ownership of model', 'success');
                            if($ownmsg["elementDetails"]["Certified"]== "true"){
                                $bc_result = modelBcCertification($name,$type,$frequency,$kind,$protocol,$format,$producer,$subnature,$staticAttributes,$service,$servicePath,$listAttributes,$organization,$accessToken);
                                if($bc_result == '200'){
                                    $result['msg']="OK - model certification";
                                }else{
                                    $result['msg'] = "error during certification";
                                    $result["error_msg"] .= "Error communicating with the blockchain";
                                    $result["log"] = "Blockchain returned HTTP error code : " . $bc_result ."\r\n";
                                }
                            }
                        } else {
                            logAction($link, $username, 'model', 'insert', $name, $organization, 'Registering the ownership of model', 'faliure');
                        }

                        logAction($link, $username, 'model', 'insert', $name, $organization, '', 'success');
                    } else {
                        $result["status"] = 'ko';
                        $result["msg"] = "Error: An error occurred when registering the Model $name : " . mysqli_error($link) . " Please enter again the Model". $r;
                        $result["log"] = "action=insert -" . $q . " error " . mysqli_error($link) . "\r\n";
                        if(mysqli_errno($link) == 1062){
                            logAction($link, $username, 'model', 'insert', $name, $organization, 'Tried to insert a duplicate name for model', 'faliure');
                        }else {
                            $result["error_msg"]="Problems with the database";
                            logAction($link, $username, 'model', 'insert', $name, $organization, 'An error occurred when registering the Model', 'faliure');
                        }
                    }
                } else {
                    $result["status"] = 'ko';
                    $result["error_msg"] = $servicePath . " is NOT a valid servicePath";
                }
            }
        }
    }

    my_log($result);
    mysqli_close($link);
} else
if ($action == "update") {
    $missingParams = missingParameters(array('id', 'name', 'description', 'type', 'contextbroker', 'kind', 'format', 'producer', 'frequency', 'attributes', 'kgenerator', 'hc', 'hv'));

    if (!empty($missingParams)) {
        $result["status"] = "ko";
        $result['msg'] = "Missing Parameters";
        $result["error_msg"] .= "Problem in update model (Missing parameters: " . implode(", ", $missingParams) . " )";
        $result["log"] = "action=update - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
    } else {
        $id = mysqli_real_escape_string($link, $_REQUEST['id']);
        $name = mysqli_real_escape_string($link, $_REQUEST['name']);
        $description = mysqli_real_escape_string($link, $_REQUEST['description']);
        $type = mysqli_real_escape_string($link, $_REQUEST['type']);
        $kind = mysqli_real_escape_string($link, $_REQUEST['kind']);
        $producer = mysqli_real_escape_string($link, $_REQUEST['producer']);
        $frequency = mysqli_real_escape_string($link, $_REQUEST['frequency']);
        $kgenerator = mysqli_real_escape_string($link, $_REQUEST['kgenerator']);
        $contextbroker = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
        $format = mysqli_real_escape_string($link, $_REQUEST['format']);
        $hc = mysqli_real_escape_string($link, $_REQUEST['hc']);
        $hv = mysqli_real_escape_string($link, $_REQUEST['hv']);
        $listAttributes = mysqli_real_escape_string($link, $_REQUEST['attributes']);
        $hlt = mysqli_real_escape_string($link, $_REQUEST['hlt']);
        if (isset($_REQUEST['subnature']))
            $subnature = mysqli_real_escape_string($link, $_REQUEST['subnature']);
        else
            $subnature = "";
        if (isset($_REQUEST['static_attributes']))
            $staticAttributes = mysqli_real_escape_string($link, $_REQUEST['static_attributes']);
        else
            $staticAttributes = "[]";
        if (isset($_REQUEST['service']))
            $service = mysqli_real_escape_string($link, $_REQUEST['service']);
        else
            $service = "";
        if (isset($_REQUEST['servicePath']))
            $servicePath = mysqli_real_escape_string($link, $_REQUEST['servicePath']);
        else
            $servicePath = "";
        if (isset($_REQUEST['edgegateway_type']))
            $edgegateway_type = mysqli_real_escape_string($link, $_REQUEST['edgegateway_type']);
        else
            $edgegateway_type = "";

        $protocol = getProtocol($contextbroker, $link);

        if (empty($protocol)) {//it also ensure the contextbroker name is valid
            $result["status"] = "ko";
            $result['msg'] = "Unrecognized contextbroker/protocol";
            $result["error_msg"] .= "Problem in update model (Unrecognized contextbroker/protocol)";
            $result["log"] = "action=update - error Unrecognized contextbroker/protocol\r\n";
        } else {
            //id management
            //get_model
            $q = "SELECT * FROM model WHERE id = '$id'";
            $r = mysqli_query($link, $q);
            if (!$r) {
                $result["status"] = "ko";
                $result['msg'] = "Unrecognized model";
                $result["error_msg"] .= "Problem in update model (Unrecognized device)";
                $result["log"] = "action=update - error Unrecognized device\r\n";
            } else {
                $row = mysqli_fetch_assoc($r);
                $obj_organization = $row["organization"];
                $old_name = $row["name"];
                $eId = $obj_organization . ":" . $old_name;
                $new_eId = $obj_organization . ":" . $name;

                if ((count($row) == 0) || (!enforcementRights($username, $accessToken, $role, $eId, 'ModelID', 'write', $result))) {
                    $result["status"] = "ko";
                    $result['msg'] = "Not ownership or enough right to update";
                    $result["error_msg"] .= "Problem in update model (Not ownership or enough right to update)";
                    $result["log"] .= "action=update - error Not ownership or enough right to update\r\n";
                } else {

                    //TODO check if needed
                    $syntaxRes = 0;
                    if ($protocol == 'ngsi w/MultiService') {
                        $syntaxRes = servicePathSyntaxCheck($servicePath);
                        $service = "'$service'";
                        $servicePath = "'$servicePath'";
                    } else {
                        $service = "NULL";
                        $servicePath = "NULL";
                    }

                    if ($syntaxRes == 0) {
                        $q = "UPDATE model SET name = '$name', attributes = '$listAttributes', description = '$description', devicetype = '$type', 
								kind = '$kind',  producer= '$producer', frequency = '$frequency', contextbroker='$contextbroker', protocol = '$protocol', 
								format = '$format', healthiness_criteria = '$hc', healthiness_value='$hv', kgenerator = '$kgenerator', edgegateway_type = '$edgegateway_type', 
								subnature='$subnature', static_attributes='$staticAttributes' , service = $service, servicePath = $servicePath , hlt = '$hlt' WHERE id = '$id'";

                        $r = mysqli_query($link, $q);

                        //we need to update the ownership since the name can change
                        if (strcmp($name, $old_name) != 0) {
                            if ($r) {
                                //remove old ownership
                                removeOwnerShipObject($eId, $accessToken, "ModelID", $result);

                                //TODO if RootAdmin update this device, he became the owner of the device... instead of remove and add again, here we need to updateOwnership!!!
                                $ownmsg = array();
                                $ownmsg["elementId"] = $new_eId;
                                $ownmsg["elementName"] = $new_eId;
                                $ownmsg["elementUrl"] = $new_eId;
                                $ownmsg["elementType"] = "ModelID";
                                registerOwnerShipObject($ownmsg, $accessToken, 'ModelID', $result);

                                if ($result["status"] == 'ok') {
                                    $result["log"] = "action=update " . $q . " \r\n";
                                    logAction($link, $username, 'model', 'update', $name, $organization, '', 'success');
                                } else {
                                    logAction($link, $username, 'model', 'update', $name, $organization, 'in ownership registration', 'faliure');
                                }
                            } else {
                                logAction($link, $username, 'model', 'update', $name, $organization, 'An error occurred when updating the model', 'faliure');
                                $result["status"] = 'ko';
                                $result["msg"] = "Error: An error occurred when updating the model $name. <br/>" . mysqli_error($link) . ' Please enter again the model';
                                $result["log"] = "action=update -" . $q . " error " . mysqli_error($link) . "\r\n";
                            }
                        }
                    } else {
                        $result["status"] = 'ko';
                        $result["error_msg"] = $servicePath . " is NOT a valid servicePath";
                    }
                }
            }
        }
    }

    my_log($result);
    mysqli_close($link);
} else
if ($action == "delete") {
    $missingParams = missingParameters(array('id'));

    if (!empty($missingParams)) {
        $result["status"] = "ko";
        $result['msg'] = "Missing Parameters";
        $result["error_msg"] .= "Problem in delete model (Missing parameters: " . implode(", ", $missingParams) . " )";
        $result["log"] = "action=delete - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
    } else {
        $id = mysqli_real_escape_string($link, $_REQUEST['id']);

        //id management
        //get_model
        $q = "SELECT * FROM model WHERE id = '$id'";
        $r = mysqli_query($link, $q);
        if (!$r) {
            $result["status"] = "ko";
            $result['msg'] = "Unrecognized model";
            $result["error_msg"] .= "Problem in update model (Unrecognized device)";
            $result["log"] = "action=update - error Unrecognized device\r\n";
        } else {
            $row = mysqli_fetch_assoc($r);
            $obj_organization = $row["organization"];
            $name = $row["name"];
            $eId = $obj_organization . ":" . $name;

            if ((count($row) == 0) || (!enforcementRights($username, $accessToken, $role, $eId, 'ModelID', 'write', $result))) {
                $result["status"] = "ko";
                $result['msg'] = "Not ownership or enough right to update";
                $result["error_msg"] .= "Problem in delete model (Not ownership or enough right to update)";
                $result["log"] .= "action=delete - error Not ownership or enough right to update\r\n";
            } else {

                $q = "DELETE FROM model WHERE id = '$id'";
                $r = mysqli_query($link, $q);
                if ($r) {
                    logAction($link, $username, 'model', 'delete', $name, $organization, '', 'success');

                    $result["status"] = 'ok';

                    removeOwnerShipObject($eId, $accessToken, "ModelID", $result);
                    if ($result["status"] = 'ok') {
                        $result["log"] .= '\n\r action: delete ok. ' . $q;
                        logAction($link, $username, 'model', 'delete', $name, $organization, '', 'success');
                    } else {
                        $result["log"] .= '\n\r action: delete ok from database, delete Ownership failed. ';
                        logAction($link, $username, 'model', 'delete', $name, $organization, 'delete ok from database, delete Ownership failed.', 'faliure');
                    }
                } else {
                    logAction($link, $username, 'model', 'delete', $name, $organization, '', 'faliure');
                    $result["status"] = 'ko';
                    $result["log"] = "action=delete -" . $q . " error " . mysqli_error($link) . "\r\n";
                    $result["msg"] = 'Model <b>' . $name . '</b> &nbsp; deletion failed, ' . mysqli_error($link) . $q . ' Please enter again.';
                }
            }
        }
    }

    my_log($result);
    mysqli_close($link);
} else if ($action == 'get_value_attributes' || $action == 'get_value_attributes_read') {
    $missingParams = missingParameters(array('id'));

    if (!empty($missingParams)) {
        $result["status"] = "ko";
        $result['msg'] = "Missing Parameters";
        $result["error_msg"] .= "Problem in getting model value attributes (Missing parameters: " . implode(", ", $missingParams) . " )";
        $result["log"] = "action=get_value_attributes - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
    } else {
        $id = mysqli_real_escape_string($link, $_REQUEST['id']);

        //id management
        //get_model
        $q = "SELECT * FROM model WHERE id = '$id'";
        $r = mysqli_query($link, $q);
        if (!$r) {
            $result["status"] = "ko";
            $result['msg'] = "Unrecognized model";
            $result["error_msg"] .= "Problem in getting model value attributes (Unrecognized device)";
            $result["log"] = "action=get_value_attributes - error Unrecognized device\r\n";
        } else {
            $row = mysqli_fetch_assoc($r);
            $obj_organization = $row["organization"];
            $name = $row["name"];
            $eId = $obj_organization . ":" . $name;

            if ($action == 'get_value_attributes' && ((count($row) == 0) || (!enforcementRights($username, $accessToken, $role, $eId, 'ModelID', 'write', $result)))) {
                $result["status"] = "ko";
                $result['msg'] = "Not ownership or enough right to update";
                $result["error_msg"] .= "Problem in getting model value attributes (Not ownership or enough right to update)";
                $result["log"] .= "action=get_value_attributes - error Not ownership or enough right to update\r\n";
            } else if ($action == 'get_value_attributes_read' && ( (count($row) == 0) || (!enforcementRights($username, $accessToken, $role, $eId, 'ModelID', 'read', $result)))) {
                $result["status"] = "ko";
                $result['msg'] = "Not ownership or enough right to read";
                $result["error_msg"] .= "Problem in getting model value attributes (Not ownership or enough right to read)";
                $result["log"] .= "action=$action - error Not ownership or enough right to read\r\n";
            } else {

                $result = array();

                $q = "SELECT attributes FROM model WHERE id = '$id'";
                $r = mysqli_query($link, $q);
                if ($r) {
                    $row = mysqli_fetch_assoc($r);
                    $result["status"] = "ok";
                    $result["content"] = $row;

                    $result["log"] = "action=$action of model " . $id . " \r\n";
                } else {
                    $result['status'] = 'ko';
                    $result['msg'] = 'Error: errors in reading data about attributes of the model. <br/>' . generateErrorMessage($link);
                    $result["log"] = "action=$action of model " . $id . " error " . mysqli_error($link) . "\r\n";
                }
            }
        }
    }

    my_log($result);
    mysqli_close($link);
} else if ($action_lwr == 'update_values_attributes_fiware' || $action == 'Update_values_attributes_FIWIRE') {
    $missingParams = missingParameters(array('id', 'version', 'domain', 'subdomain', 'change'));
    if (!empty($missingParams)) {
        $result["status"] = "ko";
        $result['msg'] = "Missing Parameters";
        $result["error_msg"] .= "Problem in updating model value attributes (Missing parameters: " . implode(", ", $missingParams) . " )";
        $result["log"] = "action=update_values_attributes_fiware - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
    } else {
        $id = mysqli_real_escape_string($link, $_REQUEST['id']);
        $version = mysqli_real_escape_string($link, $_REQUEST['version']);
        $domain = mysqli_real_escape_string($link, $_REQUEST['domain']);
        $subdomain = mysqli_real_escape_string($link, $_REQUEST['subdomain']);
        $subNat = mysqli_real_escape_string($link, $_REQUEST['subNat']);
        $change = mysqli_real_escape_string($link, $_REQUEST['change']);
        $change = stripslashes($change);
        $change = json_decode($change, true);

        $q = "SELECT attributes,attributesLog  FROM raw_schema_model WHERE model = '$id' AND version= '$version' AND domain='$domain' AND subdomain='$subdomain'";
        $r = mysqli_query($link, $q);

        if (!$r) {
            $result['status'] = 'ko';
            $result['msg'] = 'Error: errors in reading data about attributes of the model. <br/>' . generateErrorMessage($link);
            $result["log"] = "action=$action of model " . $id . " error " . mysqli_error($link) . "\r\n";
        } else {
            $result["status"] = "ok - first query done";

            $row = mysqli_fetch_assoc($r);
            $arrtibLog = $row["attributesLog"];
            $arrtib = $row["attributes"];
            $arrtib = json_decode($arrtib, true);
            $arrtibLog = json_decode($arrtibLog, true);

            foreach ($arrtib as $key => $val) {
                if ($key != 'type') {
                    if (isset($_REQUEST['make_rule'])) {
                        $num = rand(10, 100);
                        $namerule = " " . $domain . "_" . $subdomain . "_" . $id . "_" . $key . $num . " ";
                        $IF_st = array("field" => "value_name", "operator" => "IsEqual", "value" => $key);
                        $THEN_st = array(array("field" => "value_type", "valueThen" => $change[$key]["value_type"]),
                            array("field" => "value_unit", "valueThen" => $change[$key]["value_unit"]),
                            array("field" => "data_type", "valueThen" => $change[$key]["data_type"]));

                        $q2 = "INSERT INTO `iotdb`.`EXT_values_rules` (`Name`,`If_statement`, `Then_statement`, `Organization`, `Timestamp`,`mode`, `contextbroker`, `service`, `servicePath` )"
                                . " VALUES ('$namerule','" . mysqli_real_escape_string($link, json_encode($IF_st)) . "', '" . mysqli_real_escape_string($link, json_encode($THEN_st)) . "', '$organization', NOW(), '1', '', '', '');";

                        $r = mysqli_query($link, $q2);

                        if (!$r) {

                            $result['status'] = 'ko';
                            $result['msg'] = 'Error: errors in reading data about attributes of the model. <br/>' . generateErrorMessage($link);
                            $result["log"] = "action=$action of model " . $id . " error " . mysqli_error($link) . "\r\n";
                            $result["query"] = $q2;
                             my_log($result);
                            exit;
                        } else {

                            $result["status"] = "ok";
                            $result['msg'] = "ok - rule done";
                            $result["log"] = "action=$action of model " . $id . "domain='$domain', subdomain='$subdomain', version='$version' \r\n";
                        }
                        $arrtib[$key]["value_type"] = $change[$key]["value_type"];
                        $arrtib[$key]["data_type"] = $change[$key]["data_type"];
                        $arrtib[$key]["value_unit"] = $change[$key]["value_unit"];
                        $arrtib[$key]["checked"] = 'True';
                    } else {

                        $val["value_type"] = $change[$key]["value_type"];
                        $val["data_type"] = $change[$key]["data_type"];
                        $val["value_unit"] = $change[$key]["value_unit"];

                        array_push($arrtibLog[$key], " s4c_rule value_type " . $change[$key]["value_type"] . " data_type " . $change[$key]["data_type"] . " value_unit " . $change[$key]["value_unit"] . "");
                    }
                }
            }

            $queryUpdate = "UPDATE raw_schema_model SET  subnature='$subNat', attributes='" . mysqli_real_escape_string($link, json_encode($arrtib)) . "' WHERE domain='$domain' AND subdomain='$subdomain' AND version='$version' and model='$id' ";
            $r = mysqli_query($link, $queryUpdate);
            if (!$r) {

                $result['status'] = 'ko';
                $result['msg'] = 'Error: errors in reading data about attributes of the model. <br/>' . generateErrorMessage($link);
                $result["log"] = "action=$action of model " . $id . " error " . mysqli_error($link) . "\r\n";
                $result["query"] = $queryUpdate;
            } else {

                $result["status"] = "ok";
                $result['msg'] = "ok - second query done";
                $result["log"] = "action=$action of model " . $id . "domain='$domain', subdomain='$subdomain', version='$version' \r\n";
            }

            if (!isset($_REQUEST['make_rule'])) {
                $queryUpdate = "UPDATE raw_schema_model SET  subnature='$subNat', attributesLog='" . mysqli_real_escape_string($link, json_encode($arrtibLog)) . "' WHERE domain='$domain' AND subdomain='$subdomain' AND version='$version' and model='$id' ";

                $r = mysqli_query($link, $queryUpdate);
                if (!$r) {

                    $result['status'] = 'ko';
                    $result['msg'] = 'Error: errors in reading data about attributes of the model. <br/>' . generateErrorMessage($link);
                    $result["log"] = "action=$action of model " . $id . " error " . mysqli_error($link) . "\r\n";
                    $result["query"] = $queryUpdate;
                } else {

                    $result["status"] = "ok";
                    $result['msg'] = "ok - second query done";
                    $result["log"] = "action=$action of model " . $id . "domain='$domain', subdomain='$subdomain', version='$version' \r\n";
                }
            }
        }
    }
    my_log($result);
    mysqli_close($link);
} else if ($action_lwr == 'get_value_attributes_fiware' || $action == 'get_value_attributes_FIWIRE') {
    $missingParams = missingParameters(array('id', 'version', 'domain', 'subdomain'));

    if (!empty($missingParams)) {
        $result["status"] = "ko";
        $result['msg'] = "Missing Parameters";
        $result["error_msg"] .= "Problem in getting model value attributes fiware (Missing parameters: " . implode(", ", $missingParams) . " )";
        $result["log"] = "action=get_value_attributes_fiware - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
    } else {
        $id = mysqli_real_escape_string($link, $_REQUEST['id']);
        $version = mysqli_real_escape_string($link, $_REQUEST['version']);
        $domain = mysqli_real_escape_string($link, $_REQUEST['domain']);
        $subdomain = mysqli_real_escape_string($link, $_REQUEST['subdomain']);

        $q = "SELECT attributes,subnature FROM raw_schema_model WHERE model = '$id' AND version= '$version' AND domain='$domain' AND subdomain='$subdomain'";
        $r = mysqli_query($link, $q);
        if (!$r) {
            $result['status'] = 'ko';
            $result['msg'] = 'Error: errors in reading data about attributes of the model. <br/>' . generateErrorMessage($link);
            $result["log"] = "action=$action of model " . $id . " error " . mysqli_error($link) . "\r\n";
        } else {

            $row = mysqli_fetch_assoc($r);
            $result["status"] = "ok";

            if (isset($_REQUEST['proposal'])) {
                $TOTatr = json_decode($row['attributes']);
                $result["proposal"] = MAke_PROP($TOTatr, $link);

                $result["content"] = $row;
            } else {
                $result["content"] = $row;
            }


            $result["log"] = "action=$action of model " . $id . " \r\n";
        }
    }

    my_log($result);
    mysqli_close($link);
} else {
    $result['status'] = 'ko';
    $result['msg'] = 'invalid action ' . $action;
    $result["log"] = "action=unknown \r\n";
    echo json_encode($result);
}

function modelBcCertification($name,$type,$frequency,$kind,$protocol,$format,$producer,$subnature,$staticAttributes,$service,$servicePath,$listAttributes,$organization,$accessToken){
    if(isset($GLOBALS['blockchainApiBaseUrl'])) {
        $blockchainApiBaseUrl = $GLOBALS['blockchainApiBaseUrl'];
    } else {            
        error_log("IOT-DIR-BC ERROR model.php missing blockchainApiBaseUrl in configuration");
        return -1;
    }
    
    $listAttributes = str_replace( '\\', '', $listAttributes);
    $staticAttributes = str_replace( '\\', '', $staticAttributes);

    $bcmessage = array(
        'name' => $name,
        'type' => $type,
        'frequency'=> $frequency,
        'kind'=> $kind,
        'protocol'=> $protocol,
        'format'=> $format,
        'producer'=> $producer,
        'subnature'=> $subnature,
        'static_attributes'=> $staticAttributes,
        'service'=> $service,
        'servicePath'=> $servicePath,
        'strDev' => $listAttributes,
        'organization' => $organization
    );

    $bcmessage=json_encode($bcmessage);
    $bearer="Bearer ".$accessToken;
    $headers = array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($bcmessage),
        'authorization: '. $bearer
    );

    $ch = curl_init();
    $urlBc = $blockchainApiBaseUrl . "/api/addmodel/";
    curl_setopt($ch, CURLOPT_URL, $urlBc);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $bcmessage);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response_bc = curl_exec($ch);

    if(curl_errno($ch)){
        error_log("IOT-DIR-BC ERROR model.php sending POST $urlBc ".$bcmessage." : ".curl_error($ch));
        //echo 'Request Error:' . curl_error($ch);
        $result = 0;
    } else {
        $response_bc = json_decode($response_bc);
        $result = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if($result != 200) {
            error_log("IOT-DIR-BC ERROR model.php sending POST $urlBc ".$bcmessage." : CODE " . $result . " " . $response_bc);
        }
    }
    curl_close($ch);
    return $result;
}
