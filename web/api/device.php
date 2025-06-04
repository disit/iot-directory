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
        error_log("---- device.php:" . (microtime(true) - $mctime));
        $accessToken = $tkn->access_token;
    }
}
if ($action != 'get_param_values') {
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
}




foreach ($_REQUEST as $key => $param) {
    if ($key == "id" || $key == "device" || $key == "cb" || $key == "contextbroker" || $key == "model" || $key == "value_name") {
        if ($key == "model") {
            $param = str_replace(' ', '', $param);
        }
        if ($key == "id" || $key == "device") {
            $param = str_replace(':', '', $param);
        }
        preg_match($regphp, $param, $matches);
        if (count($matches) > 0) {
            $result["status"] = "ko";
            $result["error_msg"] = "strange characters are not allowed";
            $action = "";
            mysqli_close($link);
        }
    } else if ($key == "attributes" || $key == "newattributes" || $key == "deleteattributes") {
        $listAttributes = json_decode($param);
        $a = 0;
        while ($a < count($listAttributes)) {
            $att = $listAttributes[$a];
            $attName = $att->value_name;
            preg_match($regphp, $attName, $matches);
            if (count($matches) > 0) {
                $result["status"] = "ko";
                $result["error_msg"] = "strange characters are not allowed";
                $action = "";
                mysqli_close($link);
            }
            $a++;
        }
    }
}

if ($action == "insert") {
    $missingParams = missingParameters(array(
        'id', 'type', 'contextbroker', 'kind', 'format', 'model', 'producer',
        'latitude', 'longitude', 'k1', 'k2', 'frequency', 'attributes'
    ));

    if (!empty($missingParams)) {
        $result["status"] = "ko";
        $result['msg'] = "Missing Parameters";
        $result["error_msg"] .= "Problem in insert device (Missing parameters: " . implode(", ", $missingParams) . " )";
        $result["log"] = "action=insert - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
    } else {
        $id = mysqli_real_escape_string($link, $_REQUEST['id']);
        $devicetype = mysqli_real_escape_string($link, $_REQUEST['type']);
        $contextbroker = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
        $kind = mysqli_real_escape_string($link, $_REQUEST['kind']);
        $format = mysqli_real_escape_string($link, $_REQUEST['format']);
        $hlt = mysqli_real_escape_string($link, $_REQUEST['hlt']);
        if (isset($_REQUEST['mac']))
            $macaddress = mysqli_real_escape_string($link, $_REQUEST['mac']);
        else
            $macaddress = "";
        if (isset($_REQUEST['model']))
            $model = mysqli_real_escape_string($link, $_REQUEST['model']);
        else
            $model = "custom";
        if (isset($_REQUEST['producer']))
            $producer = mysqli_real_escape_string($link, $_REQUEST['producer']);
        else
            $producer = "";
        $latitude = mysqli_real_escape_string($link, $_REQUEST['latitude']);
        $longitude = mysqli_real_escape_string($link, $_REQUEST['longitude']);
        if (isset($_REQUEST['visibility']))
            $visibility = mysqli_real_escape_string($link, $_REQUEST['visibility']);
        else
            $visibility = "private";
        $frequency = mysqli_real_escape_string($link, $_REQUEST['frequency']);
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
        if (isset($_REQUEST['shouldbeRegistered']))
            $shouldbeRegistered = $_REQUEST['shouldbeRegistered'];
        else
            $shouldbeRegistered = "yes";
        if (isset($_REQUEST['k1']))
            $k1 = $_REQUEST['k1'];
        else
            $k1 = "";
        if (isset($_REQUEST['k2']))
            $k2 = $_REQUEST['k2'];
        else
            $k2 = "";
        if (isset($_REQUEST['edgegateway_type']))
            $edgegateway_type = $_REQUEST['edgegateway_type'];
        else
            $edgegateway_type = "";
        if (isset($_REQUEST['edgegateway_uri']))
            $edgegateway_uri = $_REQUEST['edgegateway_uri'];
        else
            $edgegateway_uri = "";
        if (isset($_REQUEST['wktGeometry']))
            $wktGeometry = mysqli_real_escape_string($link, $_REQUEST['wktGeometry']);
        else
            $wktGeometry = null;

        $listAttributes = json_decode($_REQUEST['attributes']);

        $protocol = getProtocol($contextbroker, $link);



        if (empty($protocol)) { //it also ensure the contextbroker name is valid
            $result["status"] = "ko";
            $result['msg'] = "Unrecognized contextbroker/protocol";
            $result["error_msg"] .= "Problem in insert device (Unrecognized contextbroker/protocol)";
            $result["log"] = "action=insert - error Unrecognized contextbroker/protocol\r\n";
        } else {
            insert_device(
                    $link,
                    $id,
                    $devicetype,
                    $contextbroker,
                    $kind,
                    $protocol,
                    $format,
                    $macaddress,
                    $model,
                    $producer,
                    $latitude,
                    $longitude,
                    $visibility,
                    $frequency,
                    $k1,
                    $k2,
                    $edgegateway_type,
                    $edgegateway_uri,
                    $listAttributes,
                    $subnature,
                    $staticAttributes,
                    $pathCertificate,
                    $accessToken,
                    $result,
                    $shouldbeRegistered,
                    $organization,
                    retrieveKbUrl($organizationApiURI, $organization),
                    $username,
                    $service,
                    $servicePath,
                    $wktGeometry,
                $hlt
            );

            if ($result["status"] == "ok") {
                logAction($link, $username, 'device', 'insert', $id . " " . $contextbroker, $organization, '', 'success');
                $certificationCheck = $staticAttributes;

                $certificationCheck = str_replace( '\\', '', $certificationCheck);
                $certificationCheck = str_replace( '[', '', $certificationCheck);
                $certificationCheck = str_replace( ']', '', $certificationCheck);
                $staticAttributesArray = explode(',',$certificationCheck);

                for($i = 0; $i < sizeof($staticAttributesArray); $i++){
                    if($staticAttributesArray[$i] == '"http://www.disit.org/km4city/schema#isCertified"'){
                        $bc_result = deviceBcCertification($id,$devicetype,$frequency,$kind,$protocol,$format,$producer,$subnature,$staticAttributes,$service,$servicePath,$listAttributes,$organization,$accessToken,$contextbroker);

                        if($bc_result == '200'){
                            $result['msg']="OK - Device certification";
                        }else{
                            $result['msg'] = "error during certification";
                            $result["error_msg"] .= "Error communicating with the blockchain";
                            $result["log"] = "Blockchain returned HTTP error code : " . $bc_result ."\r\n";
                        }
                    }
                }

                $q = "SELECT count(*) FROM temporary_devices WHERE contextBroker = '$contextbroker' AND id='$id'";
                $rcount = mysqli_query($link, $q);
                $totalValid = 0;
                if ($rcount) {
                    $row = mysqli_fetch_assoc($rcount);
                    $totalValid = $row["count(*)"];
                }
                logAction($link, $username, 'temporary_devices', 'count' . $totalValid, $id . " " . $contextbroker, $organization, '', 'success');
                if ($totalValid > 0) {
                    $q1 = "DELETE FROM temporary_devices  WHERE id = '$id' AND contextBroker = '$contextbroker'";
                    $qdelete = mysqli_query($link, $q1);
                    if ($qdelete) {
                        $result['msg'] = "OK removing temporary device";
                        //$result["error_msg"] .= "Error removing temporary device";
                        $result["log"] = "action=insert - OK removing temporary device\r\n";
                        logAction($link, $username, 'temporary_devices', 'temporary device deleted after insertion', $id . " " . $contextbroker, $organization, '', 'success');
                    } else {
                        $result['msg'] = "error removing temporary device";
                        $result["error_msg"] .= "Error removing temporary device";
                        $result["log"] = "action=insert - OK error temporary device\r\n";
                        logAction($link, $username, 'device', 'insert', $id . " " . $contextbroker, $organization, 'Problem in deleting the device', 'faliure');
                    }
                }
            } else {
                logAction($link, $username, 'device', 'insert', $id . " " . $contextbroker, $organization, '', 'failure');
            }
        }
    }
    my_log($result);
    mysqli_close($link);
} else if ($action == "update") {
    $missingParams = missingParameters(array(
        'id', 'type', 'contextbroker', 'gb_old_cb', 'kind', 'format', 'model', 'producer',
        'latitude', 'longitude', 'k1', 'k2', 'frequency'
    ));

    if (!empty($missingParams)) {
        $result["status"] = "ko";
        $result['msg'] = "Missing Parameters";
        $result["error_msg"] .= "Problem update the device information (Missing parameters: " . implode(", ", $missingParams) . " )";
        $result["log"] = "action=update - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
        my_log($result);
        mysqli_close($link);
    } else {
        $id = mysqli_real_escape_string($link, $_REQUEST['id']);
        $devicetype = mysqli_real_escape_string($link, $_REQUEST['type']);
        $contextbroker = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
        $old_contextbroker = mysqli_real_escape_string($link, $_REQUEST['gb_old_cb']);
        $kind = mysqli_real_escape_string($link, $_REQUEST['kind']);
        $format = mysqli_real_escape_string($link, $_REQUEST['format']);
        $hlt = mysqli_real_escape_string($link, $_REQUEST['hlt']);
        if (isset($_REQUEST['wktGeometry']))
            $wktGeometry=mysqli_real_escape_string($link, $_REQUEST['wktGeometry']);
        else
            $wktGeometry=null;

        if (isset($_REQUEST['mac']))
            $macaddress = mysqli_real_escape_string($link, $_REQUEST['mac']);
        else
            $macaddress = "";
        if (isset($_REQUEST['model']))
            $model = mysqli_real_escape_string($link, $_REQUEST['model']);
        else
            $model = "custom";
        if (isset($_REQUEST['producer']))
            $producer = mysqli_real_escape_string($link, $_REQUEST['producer']);
        else
            $producer = "";
        $latitude = mysqli_real_escape_string($link, $_REQUEST['latitude']);
        $longitude = mysqli_real_escape_string($link, $_REQUEST['longitude']);
        if (isset($_REQUEST['k1']))
            $k1 = $_REQUEST['k1'];
        else
            $k1 = "";
        if (isset($_REQUEST['k2']))
            $k2 = $_REQUEST['k2'];
        else
            $k2 = "";
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
            $edgegateway_type = $_REQUEST['edgegateway_type'];
        else
            $edgegateway_type = "";
        if (isset($_REQUEST['edgegateway_uri']))
            $edgegateway_uri = $_REQUEST['edgegateway_uri'];
        else
            $edgegateway_uri = "";
        if (isset($_REQUEST['visibility']))
            $visibility = mysqli_real_escape_string($link, $_REQUEST['visibility']);
        else
            $visibility = "private";
        $frequency = mysqli_real_escape_string($link, $_REQUEST['frequency']);
        if (isset($_REQUEST['deleteattributes']))
            $listdeleteAttributes = json_decode($_REQUEST['deleteattributes']);
        else
            $listdeleteAttributes = json_decode("[]");
        $listAttributes = json_decode($_REQUEST['attributes']);
        if (isset($_REQUEST['newattributes']))
            $listnewAttributes = json_decode($_REQUEST['newattributes']);
        else
            $listnewAttributes = json_decode("[]");

        if ($listAttributes == null)
            $merge = $listnewAttributes;
        else if ($listnewAttributes == null)
            $merge = $listAttributes;
        else
            $merge = array_merge($listAttributes, $listnewAttributes);
        if ($listdeleteAttributes != null)
            $merge = array_udiff($merge, $listdeleteAttributes, 'compare_values');

        $protocol = getProtocol($contextbroker, $link);

        if (empty($protocol)) { //it also ensure the contextbroker name is valid
            $result["status"] = "ko";
            $result['msg'] = "Unrecognized contextbroker/protocol";
            $result["error_msg"] .= "Problem in update device (Unrecognized contextbroker/protocol)";
            $result["log"] = "action=update - error Unrecognized contextbroker/protocol\r\n";
            my_log($result);
            mysqli_close($link);
        } else {
            //id management
            if ($protocol == "ngsi w/MultiService")
                $id = $service . "." . $servicePath . "." . $id;
            get_device($username, $role, $id, $contextbroker, $accessToken, $link, $result);

            if (empty($result["content"])) {
                $result["status"] = "ko";
                $result['msg'] = "Unrecognized device";
                $result["error_msg"] .= "Problem in update device (Unrecognized device)";
                $result["log"] = "action=update - error Unrecognized device\r\n";
                my_log($result);
                mysqli_close($link);
            } else {
                $dev_organization = $result["content"]["organization"];
                $uri = $result["content"]["uri"];
                $eId = $dev_organization . ":" . $contextbroker . ":" . $id;

                if (!enforcementRights($username, $accessToken, $role, $eId, 'IOTID', 'write', $result)) {
                    $result["status"] = "ko";
                    $result['msg'] = "Not ownership or enough right to update";
                    $result["error_msg"] .= "Problem in update device ()";
                    $result["log"] .= "action=update - error Not ownership or enough right to update\r\n";
                    my_log($result);
                    mysqli_close($link);
                } else {
                    $hasOwnership = !isset($result["delegationKind"]);
                    $hasModifyDelegation = !$hasOwnership && $result["delegationKind"] === "MODIFY";
                    $notDuplicate = true;
                    $selectDevices = "SELECT contextBroker, id FROM devices WHERE contextBroker = '$contextbroker' AND id = '$id';";
                    $s1 = mysqli_query($link, $selectDevices);
                    $notDuplicate = (mysqli_num_rows($s1) != 0);

                    if ($notDuplicate) {

                        // Check static attributes	
                        try {
                            if ($staticAttributes && $staticAttributes != "[]") {
                                retrieveAvailableStaticAttribute($subnature, $result);
                                foreach (explode("],[", str_replace("]]", "", str_replace("[[", "", $staticAttributes))) as $staticAttribute) {
                                    $isValid = false;
                                    foreach (json_decode($result["content"], true) as $validStaticAttribute) {
                                        if (explode("\\\",\\\"", trim($staticAttribute, "\\\""))[0] == $validStaticAttribute["uri"]) {
                                            $isValid = true;
                                        }
                                    }
                                    if (!$isValid) {
                                        $result["status"] = 'ko';
                                        $result["error_msg"] .= "The static attribute " . explode("\\\",\\\"", trim($staticAttribute, "\\\""))[0] . " is not valid.";
                                        $result["msg"] .= "\n The static attribute " . explode("\\\",\\\"", trim($staticAttribute, "\\\""))[0] . " is not valid.";
                                        $result["log"] .= "\r\n The static attribute " . explode("\\\",\\\"", trim($staticAttribute, "\\\""))[0] . " is not valid.";
                                    } else {
                                        $result["log"] .= "\r\n " . explode("\\\",\\\"", trim($staticAttribute, "\\\""))[0] . " is valid.";
                                    }
                                }
                            }
                        } catch (Exception $sace) {
                            $result["status"] == 'ko';
                            $result["error_msg"] .= "An error occurred while validating the static attributes: " . ($sace->getMessage());
                            $result["msg"] .= "\n An error occurred while validating the static attributes: " . ($sace->getMessage());
                            $result["log"] .= "\r\n An error occurred while validating the static attributes: " . ($sace->getMessage());
                        }
                        if ($result["status"] == 'ko') {
                            logAction($link, $username, 'device', 'update', $id . " " . $contextbroker, $organization, '', 'faliure');
                            my_log($result);
                            mysqli_close($link);
                            return $result;
                        }
                        // End of the check of the static attributes


                        updateKB(
                                $link,
                                $id,
                                $devicetype,
                                $contextbroker,
                                $kind,
                                $protocol,
                                $format,
                                $macaddress,
                                $model,
                                $producer,
                                $latitude,
                                $longitude,
                                $visibility,
                                $frequency,
                                $merge,
                                $listdeleteAttributes,
                                $uri,
                                $dev_organization,
                                $subnature,
                                $staticAttributes,
                                $result,
                                $service,
                                $servicePath,
                                retrieveKbUrl($organizationApiURI, $dev_organization),
                                $accessToken,
                                $hlt,
                                $wktGeometry
                        );

                        if ($result["status"] == 'ko') {
                            logAction($link, $username, 'device', 'update', $id . " " . $contextbroker, $organization, '', 'faliure');
                            $result["status"] = 'ko';
                            $result["error_msg"] .= "Problem in updating the device in the KB $id. ";
                            $result["msg"] .= "\n Problem in updating the device in the KB $id:" . generateErrorMessage($link);
                            $result["log"] .= "\n Problem in updating the device in the KB $id:" . generateErrorMessage($link);
                            my_log($result);
                            mysqli_close($link);
                            return $result;
                        }

                        if ($result["status"] == 'ok' && $result["content"] == null) {
                            $q = "UPDATE devices SET contextBroker='$contextbroker', devicetype='$devicetype', kind= '$kind', protocol='$protocol', format='$format', 
						macaddress='$macaddress', model='$model', producer='$producer', latitude='$latitude', longitude='$longitude', frequency = '$frequency', 
						organization='$dev_organization', subnature='$subnature', static_attributes='$staticAttributes', hlt='$hlt', wktGeometry='$wktGeometry'
						WHERE id='$id' and contextBroker='$old_contextbroker'";
                        } else {
                            $q = "UPDATE  devices SET uri = '" . $result["content"] . "', mandatoryproperties=1, mandatoryvalues=1, contextBroker='$contextbroker', 
						devicetype='$devicetype', kind= '$kind', protocol='$protocol', format='$format', macaddress='$macaddress', model='$model', 
						producer='$producer', latitude='$latitude', longitude='$longitude', frequency = '$frequency',organization='$dev_organization', 
						subnature='$subnature', static_attributes='$staticAttributes',hlt='$hlt' , wktGeometry='$wktGeometry'  WHERE id='$id' and contextBroker='$old_contextbroker'";
                        }

                        $r = mysqli_query($link, $q);

                        if ($r) {
                            $result["msg"] .= "\n Device $contextbroker/$id correctly updated";
                            $result["log"] .= "\r\n Device $contextbroker/$id correctly updated";

                            $q = "UPDATE devices SET is_in_db = 'success' WHERE id = '$id'";
                            $r = mysqli_query($link, $q);

                            if ($result["status"] == "ok") {
                                logAction($link, $username, 'device', 'update', $id . " " . $contextbroker, $organization, '', 'success');
                            }
                            $result["visibility"] = $visibility;
                            if ($result["content"] == null)
                                $result["active"] = false;
                            else
                                $result["active"] = true;

                            $ownmsg = array();
                            $ownmsg["elementId"] = $eId;
                            $ownmsg["elementName"] = $id;
                            $ownmsg["elementUrl"] = $result["content"];
                            $ownmsg["elementDetails"] = array();
                            $ownmsg["elementDetails"]["k1"] = $k1;
                            $ownmsg["elementDetails"]["k2"] = $k2;
                            if ($edgegateway_type != "")
                                $ownmsg["elementDetails"]["edgegateway_type"] = $edgegateway_type;
                            if ($edgegateway_uri != "")
                                $ownmsg["elementDetails"]["edgegateway_uri"] = $edgegateway_uri;
                            $ownmsg["elementDetails"]["contextbroker"] = $contextbroker;


                            $param = str_replace( array( '\\', '"' , '[',']' ), '', $staticAttributes);
                            $param=explode(",",$param);
                            foreach ($param as $key => $value){
                            if($value =="http://www.disit.org/km4city/schema#isCertified"){
                                $ownmsg["elementDetails"]["Certified"]= "true";
                             }
                            }

                            if ($hasOwnership) {
                                registerOwnerShipDevice($eId, $ownmsg, $accessToken, $result);
                            }

                            $ok = true;
                            $q = "";
                            $a = 0;
                            $b = 1;
                            while ($a < count($listAttributes) && $ok) {
                                $att = $listAttributes[$a];
                                if ($att->healthiness_criteria == "refresh_rate")
                                    $hc = "value_refresh_rate";
                                else if ($att->healthiness_criteria == "different_values")
                                    $hc = "different_values";
                                else
                                    $hc = "value_bounds";

                                $b = $a + 1;
                                $upquery = "UPDATE event_values SET cb='$contextbroker', device = '$id',value_name='$att->value_name',data_type='$att->data_type', 
					event_values.order='$b',value_type='$att->value_type', editable='$att->editable', value_unit='$att->value_unit', 
					healthiness_criteria='$att->healthiness_criteria', value_refresh_rate='$att->healthiness_value', old_value_name= '$att->value_name',real_time_flag='$att->real_time_flag'
					WHERE  cb='$old_contextbroker' AND device='$id' AND value_name='$att->old_value_name';";
                                $r1 = mysqli_query($link, $upquery);
                                if ($r1) {
                                    $result["msg"] .= "\n attribute $att->value_name with old name $att->old_value_name correctly updated";
                                    $result["log"] .= "\n attribute $att->value_name correctly updated";
                                } else {
                                    $result["error_msg"] .= "Attribute $att->value_name was not updated. ";
                                    $result["msg"] .= "<br/> attribute $att->value_name was not updated " . generateErrorMessage($link);
                                    $result["log"] .= "\r\n attribute $att->value_name was not updated " . $upquery . " " . generateErrorMessage($link);
                                    $ok = false;
                                }
                                $a++;
                            }
                            if ($ok == true) {
                                $result["msg"] .= "\n old attributes correctly updated";
                                $result["log"] .= "\n old attributes correctly updated" . $q;

                                $q = "";
                                $a = 0;
                                while ($a < count($listnewAttributes) && $ok) {
                                    $att = $listnewAttributes[$a];
                                    if ($att->healthiness_criteria == "refresh_rate")
                                        $hc = "value_refresh_rate";
                                    else if ($att->healthiness_criteria == "different_values")
                                        $hc = "different_values";
                                    else
                                        $hc = "value_bounds";

                                    $insertquery = "INSERT INTO `event_values`(`cb`, `device`, `old_value_name`, `value_name`, `data_type`, `order`, 
							`value_type`, `editable`, `value_unit`, `healthiness_criteria`, `$hc`,`real_time_flag`)
							VALUES ('$contextbroker','$id','$att->value_name', '$att->value_name','$att->data_type','$b','$att->value_type',
							'$att->editable','$att->value_unit','$att->healthiness_criteria','$att->healthiness_value','$att->real_time_flag');";
                                    $r1 = mysqli_query($link, $insertquery);
                                    if ($r1) {
                                        $result["msg"] .= "\n attribute $att->value_name correctly inserted";
                                        $result["log"] .= "\n attribute $att->value_name correctly inserted";
                                    } else {
                                        $result["error_msg"] .= "Attribute $att->value_name was not inserted. ";
                                        $result["msg"] .= "\n attribute $att->value_name was not inserted " . generateErrorMessage($link);
                                        $result["log"] .= "\n attribute $att->value_name was not inserted " . $insertquery . " " . generateErrorMessage($link);
                                        $ok = false;
                                    }
                                    $b++;
                                    $a++;
                                }
                            }
                            if ($ok == true) {
                                $result["msg"] .= "\n new attributes correctly inserted";
                                $result["log"] .= "\n new attributes correctly inserted" . $q;
                                $q = "";
                                $a = 0;
                                while ($a < count($listdeleteAttributes) && $ok) {
                                    $att = $listdeleteAttributes[$a];
                                    $a++;
                                    $deletequery = "DELETE FROM `event_values` WHERE `cb`='$contextbroker' AND `device`='$id' AND value_name='" . $att->value_name . "';";
                                    $r1 = mysqli_query($link, $deletequery);
                                    if ($r1) {
                                        $result["msg"] .= "\n attribute $att->value_name correctly deleted";
                                        $result["log"] .= "\n attribute $att->value_name correctly deleted";
                                    } else {
                                        $result["error_msg"] .= "Attribute $att->value_name was not deleted. ";
                                        $result["msg"] .= "\n attribute $att->value_name was not deleted " . generateErrorMessage($link);
                                        $result["log"] .= "\n attribute $att->value_name was not deleted " . $deletequery . " " . generateErrorMessage($link);
                                        $ok = false;
                                    }
                                }
                            }
                            if ($ok == true) {
                                $result["status"] = 'ok';
                            } else {
                                $result["status"] = 'ko';
                            }
                            my_log($result);
                            mysqli_close($link);
                        } else {
                            logAction($link, $username, 'device', 'update', $id . " " . $contextbroker, $organization, '', 'faliure');
                            $result["status"] = 'ko';
                            $result["error_msg"] .= "Problem in updating the device value $id. ";
                            $result["msg"] .= "\n Problem in updating the device value $id:" . generateErrorMessage($link);
                            $result["log"] .= "\n Problem in updating the device value $id:" . $q . " " . generateErrorMessage($link);
                            $q = "UPDATE devices SET is_in_db = 'problem_updating_device_value' WHERE id = '$id'";
                            $r = mysqli_query($link, $q);
                            my_log($result);
                            mysqli_close($link);
                        }
                    } else {
                        logAction($link, $username, 'device', 'update', $id . " " . $contextbroker, $organization, '', 'faliure');
                        $result["status"] = 'ko';
                        $result["error_msg"] .= "Problem in updating the device $id. ";
                        $result["msg"] .= "\n Problem in updating the device $id: Not Present";
                        $result["log"] .= "\n Problem in updating the device $id: Not Present";
                        my_log($result);
                        mysqli_close($link);
                    }
                }
            }
        }
    }
} else if ($action == "delete") {
    $missingParams = missingParameters(array('id', 'contextbroker'));

    if (!empty($missingParams)) {
        $result["status"] = "ko";
        $result['msg'] = "Missing Parameters";
        $result["error_msg"] .= "Problem deleting the device information (Missing parameters: " . implode(", ", $missingParams) . " )";
        $result["log"] = "action=delete - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
    } else {
        $id = $_REQUEST['id'];
        $cb = $_REQUEST['contextbroker'];
        if (isset($_REQUEST['service']))
            $service = mysqli_real_escape_string($link, $_REQUEST['service']);
        else
            $service = "";
        if (isset($_REQUEST['servicePath']))
            $servicePath = mysqli_real_escape_string($link, $_REQUEST['servicePath']);
        else
            $servicePath = "";

        $protocol = getProtocol($cb, $link);

        if (empty($protocol)) { //it also ensure the contextbroker name is valid
            $result["status"] = "ko";
            $result['msg'] = "Unrecognized contextbroker/protocol";
            $result["error_msg"] .= "Problem in delete device (Unrecognized contextbroker/protocol)";
            $result["log"] = "action=delete - error Unrecognized contextbroker/protocol $cb\r\n";
        } else {
            //id management
            if ($protocol == "ngsi w/MultiService")
                $id = $service . "." . $servicePath . "." . $id;
            get_device($username, $role, $id, $cb, $accessToken, $link, $result);

            if (empty($result["content"])) {
                $result["status"] = "ko";
                $result['msg'] = "Unrecognized device";
                $result["error_msg"] .= "Problem in delete device (Unrecognized device)";
                $result["log"] = "action=delete - error Unrecognized device\r\n";
            } else {
                $dev_organization = $result["content"]["organization"];
                $eId = $dev_organization . ":" . $cb . ":" . $id;

                if (!enforcementRights($username, $accessToken, $role, $eId, 'IOTID', 'write', $result)) {
                    $result["status"] = "ko";
                    $result['msg'] = "Not ownership or enough right to delete";
                    $result["error_msg"] .= "Problem in delete device (Not ownership or enough right to delete)";
                    $result["log"] = "action=delete - error Not ownership or enough right to delete\r\n";
                } else {
                    $result["log"] = "action=delete id: $id cb: $cb org: $dev_organization ";
                    deleteKB($link, $id, $cb, retrieveKbUrl($organizationApiURI, $dev_organization), $result, $service, $servicePath, $accessToken);

                    if ($result["status"] == 'ok') {
                        removeOwnerShipDevice($eId, $accessToken, $result);
                        if($result["status"] == 'ok') {
                            //unique name for deleted devices
                            $milliseconds = round(microtime(true) * 1000);
                            $deleted_id = $id . "_" . $milliseconds;

                            $q0 = "UPDATE devices SET id= '$deleted_id' WHERE id = '$id' and contextBroker='$cb';";
                            $q1 = "UPDATE event_values SET device='$deleted_id' WHERE device = '$id' and cb='$cb';";

                            $id = $deleted_id;

                            $q2 = "UPDATE devices SET deleted = '" . date("Y/m/d") . "' WHERE id = '$id' and contextBroker='$cb';";

                            $q3 = "INSERT INTO deleted_devices select * from devices WHERE id = '$id' and contextBroker='$cb'and deleted IS NOT NULL;";
                            $q4 = "INSERT INTO deleted_event_values (select cb,device,value_name,data_type,value_type,editable,value_unit,healthiness_criteria,
                                value_refresh_rate, different_values,value_bounds, event_values.order, real_time_flag from event_values where device = '$id' and cb='$cb' );";

                            $q5 = "DELETE FROM event_values WHERE device = '$id' and cb='$cb';";
                            $q6 = "DELETE FROM devices WHERE id = '$id' and contextBroker='$cb';";

                            $r0 = mysqli_query($link, $q0);
                            if ($r0) {
                                $r1 = mysqli_query($link, $q1);
                                if ($r1) {
                                    $r2 = mysqli_query($link, $q2);
                                    if ($r2) {
                                        $r3 = mysqli_query($link, $q3);
                                        if ($r3) {
                                            $r4 = mysqli_query($link, $q4);
                                            if ($r4) {
                                                $r5 = mysqli_query($link, $q5);
                                                if ($r5) {
                                                    $r6 = mysqli_query($link, $q6);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            if ((isset($r6)) && ($r6)) {
                                logAction($link, $username, 'device', 'delete', $id . " " . $cb, $organization, '', 'success');
                                $result["status"] = 'ok';
                            } else {
                                logAction($link, $username, 'device', 'delete', $id . " " . $cb, $organization, '', 'faliure');
                                $result["status"] = 'ko';
                                $result["msg"] .= "\n Problem in deleting the device $id: " . generateErrorMessage($link);
                                $result["log"] .= "\n Problem in deleting the device $id: " . generateErrorMessage($link);
                            }
                        }
                    }
                }
            }
        }
    }
    my_log($result);
    mysqli_close($link);
}
//TODO in case of RootAdmin, use the username of the owner of the device
else if ($action == 'change_visibility') {
    $missingParams = missingParameters(array('id', 'contextbroker', 'visibility'));

    if (!empty($missingParams)) {
        $result["status"] = "ko";
        $result['msg'] = "Missing Parameters";
        $result["error_msg"] .= "Problem changing visibility (Missing parameters: " . implode(", ", $missingParams) . " )";
        $result["log"] = "action=change_visibility - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
    } else {
        $id = mysqli_real_escape_string($link, $_REQUEST['id']);
        $contextbroker = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
        $visibility = mysqli_real_escape_string($link, $_REQUEST['visibility']);
        if (isset($_REQUEST['service']))
            $service = mysqli_real_escape_string($link, $_REQUEST['service']);
        else
            $service = "";
        if (isset($_REQUEST['servicePath']))
            $servicePath = mysqli_real_escape_string($link, $_REQUEST['servicePath']);
        else
            $servicePath = "";

        $protocol = getProtocol($contextbroker, $link);

        if (empty($protocol)) { //it also ensure the contextbroker name is valid
            $result["status"] = "ko";
            $result['msg'] = "Unrecognized contextbroker/protocol";
            $result["error_msg"] .= "Problem in change visibility  (Unrecognized contextbroker/protocol)";
            $result["log"] = "action=change_visibility - error Unrecognized contextbroker/protocol\r\n";
        } else {
            //id management
            if ($protocol == "ngsi w/MultiService")
                $id = $service . "." . $servicePath . "." . $id;
            get_device($username, $role, $id, $contextbroker, $accessToken, $link, $result, true);
            if (empty($result["content"])) {
                $result["status"] = "ko";
                $result['msg'] = "Unrecognized device";
                $result["error_msg"] .= "Problem in change visibility (Unrecognized device)";
                $result["log"] = "action=change_visibility - error Unrecognized device\r\n";
            } else {
                $dev_organization = $result["content"]["organization"];
                $uri = $result["content"]["uri"];
                $eId = $dev_organization . ":" . $contextbroker . ":" . $id;

                if (!enforcementRights($username, $accessToken, $role, $eId, 'IOTID', 'write', $result)) {
                    $result["status"] = "ko";
                    $result['msg'] = "Not ownership or enough right to update";
                    $result["error_msg"] .= "Problem in change visibility (Not ownership or enough right to update)";
                    $result["log"] = "action=change_visibility - error Not ownership or enough right to update\r\n";
                } else {
                    $q = "UPDATE devices SET  visibility = '$visibility' WHERE id='$id' and contextBroker='$contextbroker'";
                    $r = mysqli_query($link, $q);

                    if ($r) {
                        logAction($link, $username, 'device', 'change_visibility', $id . " " . $contextbroker, $organization, 'new visibility ' . $visibility, 'success');

                        $result["status"] = 'ok';
                        $result["msg"] = "\n Device Visibility correctly updated";
                        $result["log"] .= "\n Device $id: Visibility correctly updated";

                        // information to be passed to the interface
                        $result["visibility"] = $visibility;

                        //retrieve any values of this this device
                        $q1 = "SELECT * FROM event_values WHERE cb = '$contextbroker' and device='$id'";
                        $values = mysqli_query($link, $q1);

                        if ($visibility === 'public') {
                            //make public the device
                            delegateDeviceValue($eId, $contextbroker, NULL, $username, "ANONYMOUS", "", $accessToken, "", "", $result);

                            //make public any values of this device
                            while ($value = mysqli_fetch_assoc($values)) {
                                delegateDeviceValue($uri . "/" . $value["value_name"], $contextbroker, $value["value_name"], $username, "ANONYMOUS", "", $accessToken, "", "", $result);
                            }
                        } else {
                            getDelegatorDevice($accessToken, $username, $result, $eId);
                            $delegated = $result["delegation"];
                            $found = false;
                            $i = 0;
                            while (!$found && $i < count($delegated)) {
                                if (isset($delegated[$i]["userDelegated"]) && $delegated[$i]["userDelegated"] == 'ANONYMOUS') {
                                    $found = true;
                                    $delegationId = $delegated[$i]["delegationId"];
                                }
                                $i++;
                            }
                            if ($found) {
                                removeDelegationValue($accessToken, $username, $delegationId, $result);
                            }
                            //retrieve any public values
                            getDelegatedDevice($accessToken, "ANONYMOUS", $result);
                            $publicElement = $result["delegation"];
                            //remove public from any values of this device (if was public)
                            while ($value = mysqli_fetch_assoc($values)) {
                                foreach ($publicElement as $public => $public_value) {
                                    if ($uri . "/" . $value["value_name"] === $public) {
                                        removeDelegationValue($accessToken, $username, $public_value['delegationId'], $result);
                                    }
                                }
                            }
                            unset($result["delegation"]);
                        }

                        ServerCacheManage($token, 'clear');
                        $result["status"] = "ok";
                        $result["msg"] = "The delegation to anonymous has been changed";
                        $result["log"] = "The delegation to anonymous has been changed";
                    } else {
                        logAction($link, $username, 'device', 'change_visibility', $id . " " . $contextbroker, $organization, 'new visibility ' . $visibility, 'faliure');

                        $result["status"] = 'ko';
                        $result["msg"] .= "\n Problem in changing the visibility of the device $id: " . generateErrorMessage($link);
                        $result["log"] .= "\n Problem in changing the visibility of the device $id: " . generateErrorMessage($link);
                    }
                }
            }
        }
    }
    my_log($result);
    mysqli_close($link);
} else if ($action == 'change_owner') {
    $missingParams = missingParameters(array('id', 'contextbroker', 'newOwner', 'k1', 'k2', 'model'));

    if (!empty($missingParams)) {
        $result["status"] = "ko";
        $result['msg'] = "Missing Parameters";
        $result["error_msg"] .= "Problem changing owner (Missing parameters: " . implode(", ", $missingParams) . " )";
        $result["log"] = "action=change_owner - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
    } else {
        //TODO: in this routine the error returned from the ownership is not managed and UI in not updated with error information!!!
        $id = mysqli_real_escape_string($link, $_REQUEST['id']);
        $cb = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
        $newuser = mysqli_real_escape_string($link, $_REQUEST['newOwner']);
        //$currentowner = mysqli_real_escape_string($link, $_REQUEST['owner']);     //TODO this is the loggedUser... but should be the owner of the device, since rootadmin could act!
        $k1 = mysqli_real_escape_string($link, $_REQUEST['k1']);
        $k2 = mysqli_real_escape_string($link, $_REQUEST['k2']);
        $model = mysqli_real_escape_string($link, $_REQUEST['model']);
        if (isset($_REQUEST['edgegateway_type']))
            $edgegateway_type = $_REQUEST['edgegateway_type'];
        else
            $edgegateway_type = "";
        if (isset($_REQUEST['edgegateway_uri']))
            $edgegateway_uri = $_REQUEST['edgegateway_uri'];
        else
            $edgegateway_uri = "";
        if (isset($_REQUEST['service']))
            $service = mysqli_real_escape_string($link, $_REQUEST['service']);
        else
            $service = "";
        if (isset($_REQUEST['servicePath']))
            $servicePath = mysqli_real_escape_string($link, $_REQUEST['servicePath']);
        else
            $servicePath = "";

        $protocol = getProtocol($cb, $link);

        if (empty($protocol)) { //it also ensure the contextbroker name is valid
            $result["status"] = "ko";
            $result['msg'] = "Unrecognized contextbroker/protocol";
            $result["error_msg"] .= "Problem in change owner (Unrecognized contextbroker/protocol)";
            $result["log"] = "action=change_owner - error Unrecognized contextbroker/protocol\r\n";
        } else {
            //id management
            if ($protocol == "ngsi w/MultiService")
                $id = $service . "." . $servicePath . "." . $id;
            get_device($username, $role, $id, $cb, $accessToken, $link, $result);
            if (empty($result["content"])) {
                $result["status"] = "ko";
                $result['msg'] = "Unrecognized device";
                $result["error_msg"] .= "Problem in change owner (Unrecognized device)";
                $result["log"] = "action=change_owner - error Unrecognized device\r\n";
            } else {
                $dev_organization = $result["content"]["organization"];
                $url = $result["content"]["uri"];
                $eId = $dev_organization . ":" . $cb . ":" . $id;

                if (!enforcementRights($username, $accessToken, $role, $eId, 'IOTID', 'write', $result)) {
                    $result["status"] = "ko";
                    $result['msg'] = "Not ownership or enough right to update";
                    $result["error_msg"] .= "Problem in change owner(Not ownership or enough right to update)";
                    $result["log"] = "action=change_owner - error Not ownership or enough right to update\r\n";
                } else {
                    //for change ownership, a new certificate has to be created (if model is authenticated)
                    $errorThrown = false;
                    if (registerCertificatePrivateKey($link, $cb, $id, $model, $pathCertificate, $result, $username)) {
                        $privatekey = $id . "-key.pem";
                        $certificate = $id . "-crt.pem";
                        $publickey = $id . "-pubkey.pem";

                        //update also local db information
                        $q = "UPDATE devices SET privatekey='" . $privatekey . "', certificate='" . $certificate . "' where id='" . $id . "';";
                        $r = mysqli_query($link, $q);
                        if ($r) {
                            logAction($link, $username, 'device', 'change_owner', $id . " " . $cb, $organization, 'new owner: ' . $newuser, 'success');

                            $result["msg"] .= "\n cert correctly updated";
                            $result["log"] .= "\r\n cert correctly updated\r\n";
                        } else {
                            logAction($link, $username, 'device', 'change_owner', $id . " " . $cb, $organization, 'new owner: ' . $newuser, 'faliure');

                            $result["msg"] .= "\n cert NOT correctly updated";
                            $result["log"] .= "\r\n cert NOT correctly updated\r\n";
                            $errorThrown = true;
                        }
                    } else {
                        $privatekey = "";
                        $certificate = "";
                        $publickey = "";
                    }

                    if (!$errorThrown) {
                        $ownmsg = array();
                        $ownmsg["elementId"] = $eId;
                        $ownmsg["elementName"] = $id;
                        $ownmsg["elementUrl"] = $url;
                        $ownmsg["deleted"] = date("Y/m/d");
                        $ownmsg["username"] = $username;
                        $ownmsg["elementDetails"] = array();
                        $ownmsg["elementDetails"]["k1"] = $k1;
                        $ownmsg["elementDetails"]["k2"] = $k2;
                        if ($edgegateway_type != "")
                            $ownmsg["elementDetails"]["edgegateway_type"] = $edgegateway_type;
                        if ($edgegateway_uri != "")
                            $ownmsg["elementDetails"]["edgegateway_uri"] = $edgegateway_uri;
                        $ownmsg["elementDetails"]["contextbroker"] = $cb;

                        //check if a device is certified
                        $param = str_replace( array( '\\', '"' , '[',']' ), '', $staticAttributes);
                        $param=explode(",",$param);
                            foreach ($param as $key => $value){
                                if($value =="http://www.disit.org/km4city/schema#isCertified"){
                                    $ownmsg["elementDetails"]["Certified"]= "true";
                                }
                            }

                        registerOwnerShipDevice($eId, $ownmsg, $accessToken, $result); //delete old ownership
                        unset($ownmsg["deleted"]);
                        $ownmsg["username"] = $newuser;
                        if ($publickey != "") {
                            $pub_key_str = str_replace("\n", "", file_get_contents($pathCertificate . "/" . $publickey));
                            $ownmsg["elementDetails"]["publickey"] = substr($pub_key_str, 26, 736);
                        }
                        registerOwnerShipDevice($eId, $ownmsg, $accessToken, $result); //insert new ownership
                        $result["status"] = 'ok';

                        logAction($link, $username, 'device', 'change_owner', $id . " " . $cb, $organization, 'new owner: ' . $newuser, 'success');
                    } else {
                        logAction($link, $username, 'device', 'change_owner', $id . " " . $cb, $organization, 'new owner: ' . $newuser, 'faliure');
                        $result["status"] = 'ko';
                    }
                }
            }
        }
    }
    my_log($result);
    mysqli_close($link);
} else if ($action == 'get_device_attributes') {
    $missingParams = missingParameters(array('id', 'contextbroker'));

    if (!empty($missingParams)) {
        $result["status"] = "ko";
        $result['msg'] = "Missing Parameters";
        $result["error_msg"] .= "Problem getting all the device attr information (Missing parameters: " . implode(", ", $missingParams) . " )";
        $result["log"] = "action=get_device_attributes - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
    } else {
        $id = mysqli_real_escape_string($link, $_REQUEST['id']);
        $cb = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
        if (isset($_REQUEST['service']))
            $service = mysqli_real_escape_string($link, $_REQUEST['service']);
        else
            $service = "";
        if (isset($_REQUEST['servicePath']))
            $servicePath = mysqli_real_escape_string($link, $_REQUEST['servicePath']);
        else
            $servicePath = "";

        $protocol = getProtocol($cb, $link);

        if (empty($protocol)) { //it also ensure the contextbroker name is valid
            $result["status"] = "ko";
            $result['msg'] = "Unrecognized contextbroker/protocol";
            $result["error_msg"] .= "Problem in get device attributes (Unrecognized contextbroker/protocol)";
            $result["log"] = "action=get_device_attributes - error Unrecognized contextbroker/protocol\r\n";
        } else {
            //id management
            if ($protocol == "ngsi w/MultiService")
                $id = $service . "." . $servicePath . "." . $id;
            get_device($username, $role, $id, $cb, $accessToken, $link, $result);
            if (empty($result["content"])) {
                $result["status"] = "ko";
                $result['msg'] = "Unrecognized device";
                $result["error_msg"] .= "Problem in get_device_attribute (Unrecognized device)";
                $result["log"] = "action=get_device_attributes - error Unrecognized device\r\n";
            } else {
                $dev_organization = $result["content"]["organization"];
                $eId = $dev_organization . ":" . $cb . ":" . $id;

                if (!enforcementRights($username, $accessToken, $role, $eId, 'IOTID', 'read', $result)) {
                    $result["status"] = "ko";
                    $result['msg'] = "Not ownership or enough right to update";
                    $result["error_msg"] .= "Problem in get device attributes (Not ownership or enough right to update)";
                    $result["log"] = "action=get_device_attributes - error Not ownership or enough right to update\r\n";
                } else {
                    $q1 = "SELECT * FROM event_values WHERE cb = '$cb' AND device = '$id'";
                    $r1 = mysqli_query($link, $q1);
                    $attributes = array();
                    if ($r1) {
                        while ($row = mysqli_fetch_assoc($r1)) {
                            $rec = array();
                            $rec["cb"] = $row["cb"];
                            $rec["device"] = $row["device"];
                            $rec["value_name"] = $row["value_name"];
                            $rec["data_type"] = $row["data_type"];
                            $rec["value_type"] = $row["value_type"];
                            $rec["editable"] = $row["editable"];
                            $rec["value_unit"] = $row["value_unit"];
                            $rec["order"] = $row["order"];
                            $rec["healthiness_criteria"] = $row["healthiness_criteria"];
                            $rec["real_time_flag"]=$row["real_time_flag"];
                            if ($rec["healthiness_criteria"] == "refresh_rate")
                                $rec["healthiness_value"] = $row["value_refresh_rate"];
                            if ($rec["healthiness_criteria"] == "different_values")
                                $rec["healthiness_value"] = $row["different_values"];
                            if ($rec["healthiness_criteria"] == "within_bounds")
                                $rec["healthiness_value"] = $row["value_bounds"];
                            array_push($attributes, $rec);
                        }
                        $result['status'] = 'ok';
                        $result['content'] = $attributes;
                        $result['log'] .= "\n\r action:get_device_attributes. access to " . $q1;
                    } else {
                        $result['status'] = 'ko';
                        $result['msg'] = 'Error: errors in reading data about devices. <br/>' . generateErrorMessage($link);
                        $result['log'] .= '\n\naction:get_device_attributes. Error: errors in reading data about devices. ' . generateErrorMessage($link);
                    }
                }
            }
        }
    }
    my_log($result);
    mysqli_close($link);
} else if ($action == 'Loading_value') {

    $result["log"] .= "\n  ;invoked Loading_value from device.php";

    $missingParams = missingParameters(array('id', 'type', 'contextbroker', 'version'));

    if (!empty($missingParams)) {
        $result["status"] = "ko";
        $result['msg'] = "Missing Parameters";
        $result["error_msg"] = "Problem inserting data (Missing parameters: " . implode(", ", $missingParams) . " )";
        $result["log"] .= "\n action=Loading_Value - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
    } else {
        $id = mysqli_real_escape_string($link, $_REQUEST['id']);
        $type = mysqli_real_escape_string($link, $_REQUEST['type']);
        $cb = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
        $version = mysqli_real_escape_string($link, $_REQUEST['version']);

        // $payload =  $_REQUEST['payload'];
        if (isset($_REQUEST['service']) && $_REQUEST['service'] != "null")
            $service = mysqli_real_escape_string($link, $_REQUEST['service']);
        else
            $service = "";
        if (isset($_REQUEST['servicePath']) && $_REQUEST['servicePath'] != "null")
            $servicePath = mysqli_real_escape_string($link, $_REQUEST['servicePath']);
        else
            $servicePath = "";

        $result["log"] .= "\n id:" . $id . " type:" . $type . " cb:" . $cb . " service:" . $service . " servicepath:" . $servicePath;

        $protocol = getProtocol($cb, $link);

        if (empty($protocol)) { //it also ensure the contextbroker name is valid
            $result["status"] = "ko";
            $result['msg'] = "Unrecognized contextbroker/protocol";
            $result["error_msg"] = "Problem in insert data (Unrecognized contextbroker/protocol)";
            $result["log"] .= "\n action=Loading_Value - error Unrecognized contextbroker/protocol\r\n";
        } else {
            //id management: use snap_id, since in the get_device_data we need the original id
            if ($protocol == "ngsi w/MultiService")
                $snap_id = $service . "." . $servicePath . "." . $id;
            else
                $snap_id = $id;

            get_device($username, $role, $snap_id, $cb, $accessToken, $link, $result);

            if (empty($result["content"])) {
                $result["status"] = "ko";
                $result['msg'] = "Unrecognized device";
                $result["error_msg"] = "Problem to keep  data (Unrecognized device)";
                $result["log"] .= "\n action=Loading_value - error Unrecognized device\r\n";
            } else {
                $dev_organization = $result["content"]["organization"];
                $eId = $dev_organization . ":" . $cb . ":" . $snap_id;

                if (!enforcementRights($username, $accessToken, $role, $eId, 'IOTID', 'read', $result)) {
                    $result["status"] = "ko";
                    $result['msg'] = "Not ownership or enough right to load data";
                    $result["error_msg"] = "Problem in load data data (Not ownership or enough right to update)";
                    $result["log"] .= "\n action=Loading_value - error Not ownership or enough right to update\r\n";
                } else {

                    Loading_value($link, $id, $type, $cb, $service, $servicePath, $version, $result);
                }
            }
        }
    }

    $result["log"] .= "\n returning " . json_encode($result) . " from Loading_value from device.php";

    simple_log($result);
    mysqli_close($link);

    if ($result["status"] == "ok") {
        echo json_encode($result);
        //echo $result["isMobile"];
    } else {
        header('HTTP/1.1 500 Internal Server Error');
        echo $result["error_msg"];
    }
} else if ($action == 'Insert_Value') {

    $result["log"] .= "\n  ;invoked Insert_Value from device.php";

    $missingParams = missingParameters(array('id', 'type', 'contextbroker', 'version', 'payload'));

    if (!empty($missingParams)) {
        $result["status"] = "ko";
        $result['msg'] = "Missing Parameters";
        $result["error_msg"] = "Problem inserting data (Missing parameters: " . implode(", ", $missingParams) . " )";
        $result["log"] .= "\n action=Insert_Value - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
    } else {
        $id = mysqli_real_escape_string($link, $_REQUEST['id']);
        $type = mysqli_real_escape_string($link, $_REQUEST['type']);
        $cb = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
        $version = mysqli_real_escape_string($link, $_REQUEST['version']);
        $payload = $_REQUEST['payload'];
        if (isset($_REQUEST['service']) && $_REQUEST['service'] != "null")
            $service = mysqli_real_escape_string($link, $_REQUEST['service']);
        else
            $service = "";
        if (isset($_REQUEST['servicePath']) && $_REQUEST['servicePath'] != "null")
            $servicePath = mysqli_real_escape_string($link, $_REQUEST['servicePath']);
        else
            $servicePath = "";

        $result["log"] .= "\n id:" . $id . " type:" . $type . " cb:" . $cb . " service:" . $service . " servicepath:" . $servicePath;

        $protocol = getProtocol($cb, $link);

        if (empty($protocol)) { //it also ensure the contextbroker name is valid
            $result["status"] = "ko";
            $result['msg'] = "Unrecognized contextbroker/protocol";
            $result["error_msg"] = "Problem in insert data (Unrecognized contextbroker/protocol)";
            $result["log"] .= "\n action=Insert_Value - error Unrecognized contextbroker/protocol\r\n";
        } else {
            //id management: use snap_id, since in the get_device_data we need the original id
            if ($protocol == "ngsi w/MultiService")
                $snap_id = $service . "." . $servicePath . "." . $id;
            else
                $snap_id = $id;

            get_device($username, $role, $snap_id, $cb, $accessToken, $link, $result);

            if (empty($result["content"])) {
                $result["status"] = "ko";
                $result['msg'] = "Unrecognized device";
                $result["error_msg"] = "Problem in insert  data (Unrecognized device)";
                $result["log"] .= "\n action=Insert_Value - error Unrecognized device\r\n";
            } else {
                $dev_organization = $result["content"]["organization"];
                $eId = $dev_organization . ":" . $cb . ":" . $snap_id;

                if (!enforcementRights($username, $accessToken, $role, $eId, 'IOTID', 'write', $result)) {
                    $result["status"] = "ko";
                    $result['msg'] = "Not ownership or enough right to update";
                    $result["error_msg"] = "Problem in insert data (Not ownership or enough right to update)";
                    $result["log"] .= "\n action=Insert_Value - error Not ownership or enough right to update\r\n";
                } else {

                    Insert_Value($link, $id, $type, $cb, $service, $servicePath, $version, $payload, $result);
                }
            }
        }
    }

    $result["log"] .= "\n returning " . json_encode($result) . " from Insert_Value from device.php";

    simple_log($result);
    mysqli_close($link);

    if ($result["status"] == "ok")
        echo $result["content"];
    else {
        header('HTTP/1.1 500 Internal Server Error');
        echo $result["error_msg"];
    }
} else if ($action == 'get_param_values') {
    $newresult = array("status" => "", "msg" => "", "content" => "", "log" => "", "error_msg" => "");
    $newresult['status'] = 'ok';

    $newresult['data_type'] = generatedatatypes($link);
    mysqli_close($link);

    retrieveFromDictionary("value%20type", $result);
    if ($result["status"] == 'ok') {
        $newresult['value_type'] = $result["content"];
        retrieveFromDictionary("value%20unit", $result);
        if ($result["status"] == 'ok') {
            $newresult['value_unit'] = $result["content"];
            retrieveFromDictionary("subnature", $result);
            if ($result["status"] == 'ok') {
                $newresult['subnature'] = $result["content"];
            } else {
                $newresult['status'] = 'ko';
                $newresult['error_msg'] = 'Problem contacting the Snap4City server (Dictionary). Please try later';
                $newresult['log'] .= '\n Problem contacting the Snap4City server (Dictionary subnature)';
            }
        } else {
            $newresult['status'] = 'ko';
            $newresult['error_msg'] = 'Problem contacting the Snap4City server (Dictionary). Please try later';
            $newresult['log'] .= '\n Problem contacting the Snap4City server (Dictionary value unit)';
        }
    } else {
        $newresult['status'] = 'ko';
        $newresult['error_msg'] = 'Problem contacting the Snap4City server (Dictionary). Please try later';
        $newresult['log'] .= '\n Problem contacting the Snap4City server (Dictionary value type)';
    }

    $newresult['log'] .= '\n\naction:get_param_values';
    my_log($newresult);
} else if ($action == 'get_available_static') {
    $missingParams = missingParameters(array('subnature'));

    $newresult = array("status" => "", "msg" => "", "content" => "", "log" => "", "error_msg" => "");
    $newresult['status'] = 'ok';

    if (!empty($missingParams)) {
        $newresult["status"] = "ko";
        $newresult['msg'] = "Missing Parameters";
        $newresult["error_msg"] .= "Problem getting available static (Missing parameters: " . implode(", ", $missingParams) . " )";
        $newresult["log"] = "action=get_available_static - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
    } else {
        $subnature = mysqli_real_escape_string($link, $_REQUEST['subnature']);

        retrieveAvailableStaticAttribute($subnature, $result);

        if ($result["status"] == 'ok') {
            $newresult['availibility'] = $result["content"];
            $newresult['log'] .= "\n Returning " . $result["content"];
        } else {
            $newresult['status'] = 'ko';
            $newresult['error_msg'] = 'Problem contacting the Snap4City server (Service map list-static-attr). Please try later';
            $newresult['log'] .= '\n Problem contacting the Snap4City server (Service map list-static-attr)';
        }

        $newresult['log'] .= '\n\naction:get_available_static';
    }
    my_log($newresult);
} else if ($action == 'get_device_data') {

    $result["log"] .= "\n invoked get_device_data from device.php";

    $missingParams = missingParameters(array('id', 'type', 'contextbroker', 'version'));

    if (!empty($missingParams)) {
        $result["status"] = "ko";
        $result['msg'] = "Missing Parameters";
        $result["error_msg"] = "Problem getting device data (Missing parameters: " . implode(", ", $missingParams) . " )";
        $result["log"] .= "\n action=get_device_data - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
    } else {
        $id = mysqli_real_escape_string($link, $_REQUEST['id']);
        $type = mysqli_real_escape_string($link, $_REQUEST['type']);
        $cb = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
        $version = mysqli_real_escape_string($link, $_REQUEST['version']);
        if (isset($_REQUEST['service']))
            $service = mysqli_real_escape_string($link, $_REQUEST['service']);
        else
            $service = "";
        if (isset($_REQUEST['servicePath']))
            $servicePath = mysqli_real_escape_string($link, $_REQUEST['servicePath']);
        else
            $servicePath = "";

        $result["log"] .= "\n id:" . $id . " type:" . $type . " cb:" . $cb . " service:" . $service . " servicepath:" . $servicePath;

        $protocol = getProtocol($cb, $link);

        if (empty($protocol)) { //it also ensure the contextbroker name is valid
            $result["status"] = "ko";
            $result['msg'] = "Unrecognized contextbroker/protocol";
            $result["error_msg"] = "Problem in get device data (Unrecognized contextbroker/protocol)";
            $result["log"] .= "\n action=get_device_data - error Unrecognized contextbroker/protocol\r\n";
        } else {
            //id management: use snap_id, since in the get_device_data we need the original id
            if ($protocol == "ngsi w/MultiService")
                $snap_id = $service . "." . $servicePath . "." . $id;
            else
                $snap_id = $id;
            get_device($username, $role, $snap_id, $cb, $accessToken, $link, $result);
            if (empty($result["content"])) {
                $result["status"] = "ko";
                $result['msg'] = "Unrecognized device";
                $result["error_msg"] = "Problem in get device data (Unrecognized device)";
                $result["log"] .= "\n action=get_device_data - error Unrecognized device\r\n";
            } else {
                $dev_organization = $result["content"]["organization"];
                $eId = $dev_organization . ":" . $cb . ":" . $snap_id;

                if (!enforcementRights($username, $accessToken, $role, $eId, 'IOTID', 'read', $result)) {
                    $result["status"] = "ko";
                    $result['msg'] = "Not ownership or enough right to update";
                    $result["error_msg"] = "Problem in get device data (Not ownership or enough right to update)";
                    $result["log"] .= "\n action=get_device_data - error Not ownership or enough right to update\r\n";
                } else {
                    // la prende dal common.php
                    get_device_data($link, $id, $type, $cb, $service, $servicePath, $version, $result);
                    if($result["delegationKind"]=="WRITE_ONLY"){
                        //I valori del device vengono sostituiti con HIDDEN
                        $data_array = json_decode($result["content"], true);
                        $obfuscated_data = obfuscate_data_values($data_array);
                        $obfuscated_json = json_encode($obfuscated_data, JSON_PRETTY_PRINT);
                        $result["content"]=$obfuscated_json;
                    }
                }
            }
        }
    }

    $result["log"] .= "\n returning " . json_encode($result) . " from get_device_data from device.php";

    simple_log($result);
    mysqli_close($link);

    if ($result["status"] == "ok")
        echo $result["content"];
    else {
        header('HTTP/1.1 500 Internal Server Error');
        echo $result["error_msg"];
    }
}
//NEVER USED FROM FRONTEND... MAYBE SOME APIs ARE STILL USING?
//LONG TIME NOT MANTAINED (NO DELEGATION INVOLVED)... COMMENTING OUT
/* else if ($action == 'get_device'){
  $result["log"]="\r\n get_device invoked\n";

  $id = mysqli_real_escape_string($link, $_REQUEST['id']);
  $cb = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
  $organization = mysqli_real_escape_string($link, $_REQUEST['organization']);

  if (!empty($accessToken))
  {
  $ownDevices = getOwnerShipDevice($accessToken, $result);
  }

  $q = "SELECT d.`contextBroker`,d.`organization`, d.`id`, d.`uri`, d.`devicetype`, d.`kind`,
  CASE WHEN mandatoryproperties AND mandatoryvalues THEN \"active\"
  ELSE \"idle\" END AS status1, d.`macaddress`, d.`model`,
  d.`producer`, d.`longitude`, d.`latitude`, d.`protocol`,
  d.`format`, d.`visibility`, d.`frequency`, d.`created`,
  d.`privatekey`, d.`certificate`, cb.`accesslink`,
  cb.`accessport`,cb.`sha`, d.`subnature`, d.`static_attributes`,
  d.`service`, d.`servicePath`
  FROM `devices` d JOIN `contextbroker` cb
  ON (d.contextBroker=cb.name)
  WHERE deleted IS null and d.organization='".$organization."' and d.id='".$id."' AND
  d.contextBroker='".$cb."';";
  $r = mysqli_query($link, $q);
  if($r){
  $row = mysqli_fetch_assoc($r);
  if ($row){
  $rec= array();
  $rec["contextBroker"]= $row["contextBroker"];
  $rec["id"]= $row["id"];
  $rec["uri"]= $row["uri"];
  $rec["devicetype"]= $row["devicetype"];
  $rec["kind"]= $row["kind"];
  $rec["status1"]= $row["status1"];
  $rec["macaddress"]= $row["macaddress"];
  $rec["model"]= $row["model"];
  $rec["producer"]= $row["producer"];
  $rec["longitude"]= $row["longitude"];
  $rec["latitude"]= $row["latitude"];
  $rec["protocol"]= $row["protocol"];
  $rec["format"]= $row["format"];
  $rec["frequency"]= $row["frequency"];
  $rec["created"]= $row["created"];
  $rec["accesslink"]= $row["accesslink"];
  $rec["accessport"]= $row["accessport"];
  $rec["sha"]= $row["sha"];
  $rec["privatekey"]= "";
  $rec["certificate"]= "";
  $rec["edgegateway_type"]= "";
  $rec["edgegateway_uri"]= "";
  $rec["subnature"]=($row["subnature"]==null)?"":$row["subnature"];
  $rec["staticAttributes"]=($row["static_attributes"]==null)?"[]":$row["static_attributes"];
  $rec["service"] = $row["service"];
  $rec["servicePath"] = $row["servicePath"];

  $eid=$row["organization"].":".$row["contextBroker"].":".$row["id"];
  if (isset($result["keys"][$eid]))
  {
  $rec["visibility"]= ($row["visibility"]=="public")?"MyOwnPublic":"MyOwnPrivate";
  $rec["k1"]=$result["keys"][$eid]["k1"];
  $rec["k2"]=$result["keys"][$eid]["k2"];
  $rec["edgegateway_type"]= $result["keys"][$eid]["edgegateway_type"];
  $rec["edgegateway_uri"]= $result["keys"][$eid]["edgegateway_uri"];
  // the following two information should be shown only to the device owner
  $rec["privatekey"]= $row["privatekey"];
  $rec["certificate"]= $row["certificate"];
  }
  else{
  $rec["visibility"]= $row["visibility"];
  $rec["k1"]="";
  $rec["k2"]="";
  }
  $result['status'] = 'ok';
  $result['content'] = $rec;
  $result['log']='\r\n get_device success\n';
  }else{
  $result['status'] = 'ko';
  $result['msg'] = 'Error: No data for specified device: '.$id.' '.$cb.' <br/>' . generateErrorMessage($link);
  $result['log'] = '\n\r Error: No data for specified id.' . generateErrorMessage($link);
  }
  }else{
  $result['status'] = 'ko';
  $result['msg'] = 'Error: errors in reading data about device. <br/>' . generateErrorMessage($link);
  $result['log'] = '\n\r Error: errors in reading data about device.' . generateErrorMessage($link);
  }
  mysqli_close($link);
  my_log($result);
  } */ else if (($action == "get_device_simple") || ($action == "get_device")) {
    $missingParams = missingParameters(array('id', 'contextbroker'));

    if (!empty($missingParams)) {
        $result["status"] = "ko";
        $result['msg'] = "Missing Parameters";
        $result["error_msg"] .= "Problem getting device information (Missing parameters: " . implode(", ", $missingParams) . " )";
        $result["log"] = "action=get_device - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
    } else {
        $id = mysqli_real_escape_string($link, $_REQUEST['id']);
        $cb = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
        if (isset($_REQUEST['service']))
            $service = mysqli_real_escape_string($link, $_REQUEST['service']);
        else
            $service = "";
        if (isset($_REQUEST['servicePath']))
            $servicePath = mysqli_real_escape_string($link, $_REQUEST['servicePath']);
        else
            $servicePath = "";

        $protocol = getProtocol($cb, $link);

        if (empty($protocol)) { //it also ensure the contextbroker name is valid
            $result["status"] = "ko";
            $result['msg'] = "Unrecognized contextbroker/protocol";
            $result["error_msg"] .= "Problem getting device information (Unrecognized contextbroker/protocol)";
            $result["log"] = "action=get_device - error Unrecognized contextbroker/protocol\r\n";
        } else {
            //id management
            if ($protocol == "ngsi w/MultiService")
                $id = $service . "." . $servicePath . "." . $id;
            get_device($username, $role, $id, $cb, $accessToken, $link, $result);
            if (empty($result["content"])) {
                $result["status"] = "ko";
                $result['msg'] = "Unrecognized device";
                $result["error_msg"] .= "Problem in getting device information  (Unrecognized device)";
                $result["log"] = "action=get_device - error Unrecognized device\r\n";
            } else {
                $dev_organization = $result["content"]["organization"];
                $eId = $dev_organization . ":" . $cb . ":" . $id;

                if (!enforcementRights($username, $accessToken, $role, $eId, 'IOTID', 'read', $result)) {
                    $result["status"] = "ko";
                    $result['msg'] = "Not ownership or enough right to update";
                    $result["error_msg"] .= "Problem in getting device information (Not ownership or enough right to update)";
                    $result["log"] = "action=get_device - error Not ownership or enough right to update\r\n";
                } else {
                    get_device($username, $role, $id, $cb, $accessToken, $link, $result);
                }
            }
        }
    }
    my_log($result);
}  else if ($action == "get_all_device") {
    
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

    $Sel_Time = ( (isset($_REQUEST['start_time'])) || (isset($_REQUEST['end_time'])) );
    
      $flag_mod=(isset($_REQUEST['model']));
       if(isset($_REQUEST['model'])){
       $target_model= mysqli_real_escape_string($link, $_REQUEST['model']);
       $flag_mod=true;
   }   
    if(isset($_REQUEST['only_certified']))
        $certified_flag=true;

    $ownDevices = getOwnerShipDevice($accessToken, $result);
    getDelegatedDevice($accessToken, $username, $result);

    $q = "SELECT d.`contextBroker`, d.`id`, d.`uri`, d.`devicetype`, d.`kind`,
				CASE WHEN mandatoryproperties AND mandatoryvalues THEN \"active\" ELSE \"idle\" END AS status1, 
				d.`macaddress`, d.`model`, d.`producer`, d.`longitude`, d.`latitude`, d.`protocol`, d.`format`, d.`visibility`, 
				d.`frequency`, d.`created`, d.`privatekey`, d.`certificate`,d.`organization`, cb.`accesslink`, cb.`accessport`, cb.`version`,
				cb.`sha`, d.`subnature`, d.`static_attributes`,d.`service`, d.`servicePath`,d.`hlt`,d.`wktGeometry`,d.`is_in_kb`,d.`is_in_db`,d.`is_in_broker`,d.`is_in_own` FROM `devices` d JOIN `contextbroker` cb ON (d.contextBroker=cb.name)";

    if ($Sel_Time) {
        $start_int = mysqli_real_escape_string($link, $_REQUEST['start_time']);
        $end_int = mysqli_real_escape_string($link, $_REQUEST['end_time']);
        $q .= " WHERE d.created BETWEEN CAST('$start_int' AS DATETIME) AND CAST('$end_int' AS DATETIME)";
    }
    
    if($flag_mod && !$Sel_Time){ // no where clause yet
        $q .= " WHERE d.model = '$target_model' ";        
    }else if ($flag_mod && $Sel_Time){
        $q .= "AND  d.model = '$target_model' ";
    }

    if($certified_flag)
        $q .= "WHERE d.static_attributes LIKE '%isCertified%'";

    if (count($selection) != 0) {
        $a = 0;
        $cond = "";
        while ($a < count($selection)) {
            $sel = $selection[$a];
            $cond .= " (id = '" . $sel->id . "' AND contextBroker = '" . $sel->contextBroker . "') ";
            if ($a != count($selection) - 1)
                $cond .= " OR ";
            $a++;
        }
        $r = create_datatable_data($link, $_REQUEST, $q, "deleted IS null AND (" . $cond . ") ");
    } else {
        $r = create_datatable_data($link, $_REQUEST, $q, "deleted IS null");
    }

    $selectedrows = -1;
    if ($length != -1) {
        $offset = $length;
        $tobelimited = true;
    } else {
        $tobelimited = false;
    }

    if (isset($_REQUEST['delegated']) || isset($_REQUEST['public']) || isset($_REQUEST['own'])) {
        $subset = true;
    } else {
        $subset = false;
    }
    
    $data = array();
    if ($r) {
        while ($row = mysqli_fetch_assoc($r)) {
            $eid = $row["organization"] . ":" . $row["contextBroker"] . ":" . $row["id"];

            $SelPub = ($row["organization"] == $organization) && ($row["visibility"] == 'public' || ( isset($result["delegation"][$eid]) && $result["delegation"][$eid]["kind"] == "anonymous") );
            $SelDel = (isset($result["delegation"][$eid]) && ($result["delegation"][$eid]["kind"] != "anonymous") && $row["visibility"] != "public");

            $SelOwn = (isset($result["keys"][$eid]) && $result["keys"][$eid]["owner"] == $username );

            if (!$subset) {
                $COND = $SelPub || $SelDel || $SelOwn || $role === 'RootAdmin';
            } else {
                if (isset($_REQUEST['delegated'])) {
                    $COND = $SelDel;
                } else if (isset($_REQUEST['public'])) {
                    $COND = $SelPub;
                } else if (isset($_REQUEST['own'])) {
                    $COND = $SelOwn;
                }
            }

            if ($COND) {
                $selectedrows++;
                if (!$tobelimited || ($tobelimited && $selectedrows >= $start && $selectedrows < ($start + $offset))) {
                    $rec = array();
                    $rec["contextBroker"] = $row["contextBroker"];
                    $rec["id"] = $row["id"];
                    $rec["uri"] = $row["uri"];
                    $rec["devicetype"] = $row["devicetype"];
                    $rec["kind"] = $row["kind"];
                    $rec["status1"] = $row["status1"];
                    $rec["macaddress"] = $row["macaddress"];
                    $rec["model"] = $row["model"];
                    $rec["producer"] = $row["producer"];
                    $rec["longitude"] = $row["longitude"];
                    $rec["latitude"] = $row["latitude"];
                    $rec["protocol"] = $row["protocol"];
                    $rec["format"] = $row["format"];
                    $rec["frequency"] = $row["frequency"];
                    $rec["created"] = $row["created"];
                    $rec["organization"] = $row["organization"];
                    $rec["subnature"] = ($row["subnature"] == null) ? "" : $row["subnature"];
                    $rec["staticAttributes"] = ($row["static_attributes"] == null) ? "[]" : $row["static_attributes"];
                    $rec["service"] = $row["service"];
                    $rec["servicePath"] = $row["servicePath"];
                    $rec["hlt"] = $row["hlt"];
                    $rec["wktGeometry"] = $row["wktGeometry"];
                    $rec["accesslink"] = $row["accesslink"];
                    $rec["accessport"] = $row["accessport"];
                    $rec["version"] = $row["version"];
                    $rec["sha"] = $row["sha"];
                    $rec["privatekey"] = "";
                    $rec["certificate"] = "";
                    $rec["edgegateway_type"] = "";
                    $rec["edgegateway_uri"] = "";
                    $rec["is_in_kb"] = $row["is_in_kb"];
                    $rec["is_in_db"] = $row["is_in_db"];
                    $rec["is_in_broker"] = $row["is_in_broker"];
                    $rec["is_in_own"] = $row["is_in_own"];
                    $rec["url"] = get_LDgraph_link($logUriLD, $organizationApiURI, $row["organization"], $row["uri"]);
                    $rec["m_url"] = get_ServiceMap_link($row["uri"], $organizationApiURI, $row["organization"]);

                    if (isset($result["delegation"][$eid]["delegationKind"])) {
                        $rec["delegationKind"] = $result["delegation"][$eid]["delegationKind"];
                    }

                    if ($row["protocol"] == "ngsi w/MultiService") {
                        // get the name from id
                        $rec["id"] = explode(".", $row["id"])[2];
                    }

                    if (((isset($result["keys"][$eid])) && ($role !== 'RootAdmin')) ||
                            ((isset($result["keys"][$eid])) && ($result["keys"][$eid]["owner"] == $username) && ($role === 'RootAdmin'))
                    ) {
                        //it's mine or RootAdmin
                        if ($row["visibility"] == "public") {
                            $rec["visibility"] = "MyOwnPublic";
                        } else {
                            if (isset($result["delegation"][$row["uri"]]) && $result["delegation"][$row["uri"]]["kind"] == "anonymous")
                                $rec["visibility"] = "MyOwnPublic";
                            else
                                $rec["visibility"] = "MyOwnPrivate";
                        }

                        $rec["k1"] = $result["keys"][$eid]["k1"];
                        $rec["k2"] = $result["keys"][$eid]["k2"];
                        $rec["edgegateway_type"] = $result["keys"][$eid]["edgegateway_type"];
                        $rec["edgegateway_uri"] = $result["keys"][$eid]["edgegateway_uri"];
                        $rec["privatekey"] = $row["privatekey"];
                        $rec["certificate"] = $row["certificate"];
                    } else {
                        //it's not mine
                        if (isset($result["delegation"][$eid]) && ($result["delegation"][$eid]["kind"] == "anonymous")) {
                            //it's delegated as public
                            $rec["visibility"] = 'public';
                            $rec["k1"] = "";
                            $rec["k2"] = "";
                        } else if (isset($result["delegation"][$eid]) && ($result["delegation"][$eid]["kind"] != "anonymous") && $row["visibility"] != "public") {
                            //it's delegated personally
                            $rec["visibility"] = 'delegated';
                            $rec["k1"] = "";
                            $rec["k2"] = "";
                            if (isset($result["delegation"][$eid]["k1"])) {
                                $rec["k1"] = $result["delegation"][$eid]["k1"]; // to be fixed
                                $rec["k2"] = $result["delegation"][$eid]["k2"]; // to be fixed
                            }
                        } else {
                            $rec["visibility"] = $row["visibility"];
                            $rec["k1"] = "";
                            $rec["k2"] = "";
                        }
                    }


                    array_push($data, $rec);
                }
            }
            
            
            
        }

        $output = format_result($draw, $selectedrows + 1, $selectedrows + 1, $data, "", "\r\n action=get_all_device \r\n", 'ok');
        $output['cache']=$result['cache'];
        //$output = format_result($draw, $selectedrows + 1, $selectedrows + 1, $data, "", "\r\n action=get_all_device \r\n", 'ok');
        logAction($link, $username, 'device', 'get_all_device', '', $organization, '', 'success');
    } else {
        logAction($link, $username, 'device', 'get_all_device', '', $organization, 'Error: errors in reading data about devices.', 'faliure');
        $output = format_result($_REQUEST["draw"], 0, 0, $data, 'Error: errors in reading data about devices. <br/>' . generateErrorMessage($link), '\n\r Error: errors in reading data about devices.' . generateErrorMessage($link), 'ko');
    }
    my_log($output);
    mysqli_close($link);
}
//TODO can be unified with get_all_device
//assurance on ADMIN is guarranteed by getOwnerShipDevice
else if ($action == "get_all_device_admin") {
    if (isset($_REQUEST['select']))
        $selection = json_decode($_REQUEST['select']);
    else
        $selection = array();

    $ownDevices = getOwnerShipDevice($accessToken, $result);
    if($role != 'RootAdmin')
      getDelegatedDevice($accessToken, $username, $result);

    $q = "SELECT d.`contextBroker`, d.`id`, d.`uri`, d.`devicetype`, d.`kind`, 
	    	  CASE WHEN mandatoryproperties AND mandatoryvalues THEN \"active\" ELSE \"idle\" END AS status1, 
		      d.`macaddress`, d.`model`, d.`producer`, d.`longitude`, d.`latitude`, d.`protocol`, d.`format`, d.`visibility`, d.`organization`,
		      d.`frequency`, d.`created`, d.`privatekey`, d.`certificate`, cb.`accesslink`,  cb.`accessport`,cb.`sha`, d.`subnature`, d.`static_attributes`, d.`service`, d.`servicePath` ,d.`is_in_kb`,d.`is_in_db`,d.`is_in_broker`,d.`is_in_own`
			  FROM `devices` d JOIN `contextbroker` cb ON (d.contextBroker=cb.name) ";

    if (count($selection) != 0) {
        $a = 0;
        $cond = "";
        while ($a < count($selection)) {
            $sel = $selection[$a];
            $cond .= " (id = '" . $sel->id . "' AND contextBroker = '" . $sel->contextBroker . "') ";
            if ($a != count($selection) - 1)
                $cond .= " OR ";
            $a++;
        }
        $r = create_datatable_data($link, $_REQUEST, $q, "deleted IS null AND (" . $cond . ") ");
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
    
    if (isset($_REQUEST['delegated']) || isset($_REQUEST['public']) || isset($_REQUEST['own'])) {
        $subset = true;
    } else {
        $subset = false;
    }
    
    if ($r) {
        $data = array();
        logAction($link, $username, 'device', 'get_all_device_admin', '', $organization, '', 'success');

        while ($row = mysqli_fetch_assoc($r)) {            
            $eid = $row["organization"] . ":" . $row["contextBroker"] . ":" . $row["id"];

            $SelPub = ($row["organization"] == $organization) && ($row["visibility"] == 'public' || ( isset($result["delegation"][$eid]) && $result["delegation"][$eid]["kind"] == "anonymous") );
            $SelDel = (isset($result["delegation"][$eid]) && ($result["delegation"][$eid]["kind"] != "anonymous") && $row["visibility"] != "public");
            $SelOwn = (isset($result["keys"][$eid]) && $result["keys"][$eid]["owner"] == $username );

            if (!$subset) {
                $COND = $SelPub || $SelDel || $SelOwn || $role === 'RootAdmin';
            } else {
                if (isset($_REQUEST['delegated'])) {
                    $COND = $SelDel;
                } else if (isset($_REQUEST['public'])) {
                    $COND = $SelPub;
                } else if (isset($_REQUEST['own'])) {
                    $COND = $SelOwn;
                }
            }

            if ($COND) {
                $selectedrows++;
                if (!$tobelimited || ($tobelimited && $selectedrows >= $start && $selectedrows < ($start + $offset))) {
                    $rec = array();
                    $rec["contextBroker"] = $row["contextBroker"];
                    $rec["id"] = $row["id"];
                    $rec["uri"] = $row["uri"];
                    $rec["devicetype"] = $row["devicetype"];
                    $rec["kind"] = $row["kind"];
                    $rec["status1"] = $row["status1"];
                    $rec["macaddress"] = $row["macaddress"];
                    $rec["model"] = $row["model"];
                    $rec["producer"] = $row["producer"];
                    $rec["longitude"] = $row["longitude"];
                    $rec["organization"] = $row["organization"];
                    $rec["latitude"] = $row["latitude"];
                    $rec["protocol"] = $row["protocol"];
                    $rec["format"] = $row["format"];
                    $rec["frequency"] = $row["frequency"];
                    $rec["created"] = $row["created"];
                    $rec["accesslink"] = $row["accesslink"];
                    $rec["accessport"] = $row["accessport"];
                    $rec["sha"] = $row["sha"];
                    $rec["privatekey"] = "";
                    $rec["certificate"] = "";
                    $rec["edgegateway_type"] = "";
                    $rec["edgegateway_uri"] = "";
                    $rec["subnature"] = ($row["subnature"] == null) ? "" : $row["subnature"];
                    $rec["staticAttributes"] = ($row["static_attributes"] == null) ? "[]" : $row["static_attributes"];
                    $rec["service"] = $row["service"];
                    $rec["servicePath"] = $row["servicePath"];
                    $rec["url"] = get_LDgraph_link($logUriLD, $organizationApiURI, $row["organization"], $row["uri"]);
                    $rec["m_url"] = get_ServiceMap_link($row["uri"], $organizationApiURI, $row["organization"]);
                    $rec["is_in_kb"]=$row["is_in_kb"];
                    $rec["is_in_db"]=$row["is_in_db"];
                    $rec["is_in_broker"]=$row["is_in_broker"];
                    $rec["is_in_own"]=$row["is_in_own"];

                    if ($row["protocol"] == "ngsi w/MultiService") {
                        $rec["id"] = explode(".", $row["id"])[2];
                    }

                    $eid = $row["organization"] . ":" . $row["contextBroker"] . ":" . $row["id"];

                    if (isset($result["keys"][$eid]) && $result["keys"][$eid]["owner"] == $username) {
                        $rec["visibility"] = ($row["visibility"] == "public") ? "MyOwnPublic" : "MyOwnPrivate";
                        $rec["k1"] = $result["keys"][$eid]["k1"];
                        $rec["k2"] = $result["keys"][$eid]["k2"];
                        $rec["edgegateway_type"] = $result["keys"][$eid]["edgegateway_type"];
                        $rec["edgegateway_uri"] = $result["keys"][$eid]["edgegateway_uri"];
                        $rec["owner"] = $result["keys"][$eid]["owner"];
                        // the following two information should be shown only to the device owner				
                        $rec["privatekey"] = $row["privatekey"];
                        $rec["certificate"] = $row["certificate"];
                    } else if (isset($result["keys"][$eid]) && $result["keys"][$eid]["owner"] != $username) {
                        $rec["visibility"] = $row["visibility"]; // =="public")?"MyOwnPublic":"MyOwnPrivate";
                        $rec["k1"] = $result["keys"][$eid]["k1"];
                        $rec["k2"] = $result["keys"][$eid]["k2"];
                        $rec["edgegateway_type"] = $result["keys"][$eid]["edgegateway_type"];
                        $rec["edgegateway_uri"] = $result["keys"][$eid]["edgegateway_uri"];
                        $rec["owner"] = $result["keys"][$eid]["owner"];
                        // the following two information should be added, if we wish to give the chaance
                        // to the administrator to see the privatekey and certificate of one of its user_error 
                        // $rec["privatekey"]= $row["privatekey"];
                        // $rec["certificate"]= $row["certificate"];
                    } else {
                        $rec["visibility"] = $row["visibility"]; // =="public")?"MyOwnPublic":"MyOwnPrivate";
                        $rec["k1"] = "";
                        $rec["k2"] = "";
                        $rec["owner"] = "UnKnown";
                    }
                    array_push($data, $rec);
                }
            }
        }
        
        $output = format_result($_REQUEST["draw"], $selectedrows + 1, $selectedrows + 1, $data, "", "\r\n action=get_all_device_admin \r\n", 'ok');
        $output['query'] = $q;
    } else {
        logAction($link, $username, 'device', 'get_all_device_admin', '', $organization, '', 'faliure');
        $output = format_result($_REQUEST["draw"], 0, 0, null, 'Error: errors in reading data about devices. <br/>' . generateErrorMessage($link), '\n\r Error: errors in reading data about devices.' . generateErrorMessage($link), 'ko');
    }
    my_log($output);
    mysqli_close($link);
} else if ($action == "get_all_device_latlong") {
    $ownDevices = "";
    if (!empty($accessToken)) {
        getOwnerShipDevice($accessToken, $result);
        getDelegatedDevice($accessToken, $username, $result);
    }

    $q = "SELECT DISTINCT id, contextBroker, latitude, longitude, uri, visibility,organization FROM devices where deleted IS null; ";

    $r = mysqli_query($link, $q);

    if ($r) {
        $result['status'] = 'ok';
        $result["log"] = "\r\n action=get_all_device_latlong " . $q . " \r\n";
        $result['content'] = array();

        while ($row = mysqli_fetch_assoc($r)) {

            $eid = $row["organization"] . ":" . $row["contextBroker"] . ":" . $row["id"];
            if ((
                    ($row["organization"] == $organization) &&
                    (
                    ($row["visibility"] == 'public' ||
                    (isset($result["delegation"][$eid]) && $result["delegation"][$eid]["kind"] == "anonymous")))) ||
                    (isset($result["delegation"][$eid]) && $result["delegation"][$eid]["kind"] != "anonymous") ||
                    (isset($result["keys"][$eid]) && $result["keys"][$eid]["owner"] == $username)
            )
                array_push($result['content'], $row);
        }
    } else {
        $result['status'] = 'ko';
        $result['msg'] = 'Error: errors in reading data about devices. <br/>' .
                generateErrorMessage($link);
        $result['msg'] = '\r\n action=get_all_device_latlong -- Error: errors in reading data about devices.' . $q .
                generateErrorMessage($link);
    }
    my_log($result);
    mysqli_close($link);
} else if ($action == 'download') {
    $missingParams = missingParameters(array('id', 'contextbroker', 'filename'));

    if (!empty($missingParams)) {
        $result["status"] = "ko";
        $result['msg'] = "Missing Parameters";
        $result["error_msg"] .= "Problem in download (Missing parameters: " . implode(", ", $missingParams) . " )";
        $result["log"] = "action=download - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
    } else {
        $result["log"] = "\r\n download invoked\n";

        $id = mysqli_real_escape_string($link, $_REQUEST['id']);
        $contextbroker = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
        $filename = mysqli_real_escape_string($link, $_REQUEST['filename']);

        $protocol = getProtocol($contextbroker, $link);

        if (empty($protocol)) { //it also ensure the contextbroker name is valid
            $result["status"] = "ko";
            $result['msg'] = "Unrecognized contextbroker/protocol";
            $result["error_msg"] .= "Problem in download (Unrecognized contextbroker/protocol)";
            $result["log"] = "action=download - error Unrecognized contextbroker/protocol\r\n";
        } else {
            //id management
            if ($protocol == "ngsi w/MultiService")
                $id = $service . "." . $servicePath . "." . $id;
            get_device($username, $role, $id, $contextbroker, $accessToken, $link, $result);
            if (empty($result["content"])) {
                $result["status"] = "ko";
                $result['msg'] = "Unrecognized device";
                $result["error_msg"] .= "Problem in download (Unrecognized device)";
                $result["log"] = "action=download - error Unrecognized device\r\n";
            } else {
                $dev_organization = $result["content"]["organization"];
                $eId = $dev_organization . ":" . $contextbroker . ":" . $id;

                if (!enforcementRights($username, $accessToken, $role, $eId, 'IOTID', 'read', $result)) {
                    $result["status"] = "ko";
                    $result['msg'] = "Not ownership or enough right to update";
                    $result["error_msg"] .= "Problem in download (Not ownership or enough right to update)";
                    $result["log"] = "action=download - error Not ownership or enough right to update\r\n";
                } else {
                    //TODO support Multi-tenancy scenario
                    //TODO remove this check... this is already enforced above
                    $url = $GLOBALS["ownershipURI"] . "ownership-api/v1/list/?type=IOTID&accessToken=" . $accessToken . "&elementId=" . $eId;
                    $local_result = file_get_contents($url);
                    if (strpos($http_response_header[0], '200') === false) {
                        $result["status"] = 'ko';
                        $result["msg"] .= "\n error in acceding the ownership";
                        $result["log"] .= "\n error in acceding the ownership";
                    } else {
                        if (strpos($filename, $id) === false) {
                            logAction($link, $username, 'devices', 'download', $id . " " . $filename . " filename was tempered", $organization, '', 'faliure');

                            $result["status"] = 'ko';
                            $result["msg"] .= "\n the filename was tempered";
                            $result["log"] .= "\n the filename was tempered";
                        } else {
                            if (strpos($local_result, $id) === false) {
                                logAction($link, $username, 'devices', 'download', $id . " " . $filename . " you don't own the requested device", $organization, '', 'success');
                                $result["status"] = 'ko';
                                $result["msg"] .= "\n you don't own the requested device";
                                $result["log"] .= "\n you don't own the requested device";
                            } else {
                                $result['status'] = 'ok';
                                $result['msg'] = file_get_contents($pathCertificate . $filename);
                                $result['log'] = '\r\n download success';
                                logAction($link, $username, 'devices', 'download', $id . " " . $filename, $organization, '', 'success');
                            }
                        }
                    }
                }
            }
        }
    }

    my_log($result);
    mysqli_close($link);
} else if ($action == "get_delegations") {
    $missingParams = missingParameters(array('id', 'contextbroker'));

    if (!empty($missingParams)) {
        $result["status"] = "ko";
        $result['msg'] = "Missing Parameters";
        $result["error_msg"] .= "Problem in get delegations (Missing parameters: " . implode(", ", $missingParams) . " )";
        $result["log"] = "action=get_delegations - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
    } else {
        $cb = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
        $id = mysqli_real_escape_string($link, $_REQUEST['id']);
        if (isset($_REQUEST['service']))
            $service = mysqli_real_escape_string($link, $_REQUEST['service']);
        else
            $service = "";
        if (isset($_REQUEST['servicePath']))
            $servicePath = mysqli_real_escape_string($link, $_REQUEST['servicePath']);
        else
            $servicePath = "";

        $protocol = getProtocol($cb, $link);

        if (empty($protocol)) { //it also ensure the contextbroker name is valid
            $result["status"] = "ko";
            $result['msg'] = "Unrecognized contextbroker/protocol";
            $result["error_msg"] .= "Problem in retreive delegations (Unrecognized contextbroker/protocol)";
            $result["log"] = "action=get_delegations - error Unrecognized contextbroker/protocol\r\n";
        } else {
            //id management
            if ($protocol == "ngsi w/MultiService")
                $id = $service . "." . $servicePath . "." . $id;
            get_device($username, $role, $id, $cb, $accessToken, $link, $result);
            if (empty($result["content"])) {
                $result["status"] = "ko";
                $result['msg'] = "Unrecognized device";
                $result["error_msg"] .= "Problem in retrieve delegation (Unrecognized device)";
                $result["log"] = "action=get_delegations - error Unrecognized device\r\n";
            } else {
                $dev_organization = $result["content"]["organization"];
                $eId = $dev_organization . ":" . $cb . ":" . $id;

                if (!enforcementRights($username, $accessToken, $role, $eId, 'IOTID', 'read', $result)) {
                    $result["status"] = "ko";
                    $result['msg'] = "Not ownership or enough right to update";
                    $result["error_msg"] .= "Problem in retrieve delegations (Not ownership or enough right to update)";
                    $result["log"] = "action=get_delegations - error Not ownership or enough right to update\r\n";
                } else {
                    getDelegatorDevice($accessToken, $username, $result, $eId);
                }
            }
        }
    }
    my_log($result);
    mysqli_close($link);
}
//TODO in case of RootAdmin, use the username of the owner of the device
else if ($action == "add_delegation") {
    $missingParams = missingParameters(array('id', 'contextbroker', 'k1', 'k2'));

    if (!empty($missingParams)) {
        $result["status"] = "ko";
        $result['msg'] = "Missing Parameters";
        $result["error_msg"] .= "Problem in add delegation (Missing parameters: " . implode(", ", $missingParams) . " )";
        $result["log"] = "action=add_delegation - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
    } else {
        $cb = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
        $id = mysqli_real_escape_string($link, $_REQUEST['id']);
        $delegated_user = (isset($_REQUEST['delegated_user'])) ? mysqli_real_escape_string($link, $_REQUEST['delegated_user']) : "";
        $delegated_group = (isset($_REQUEST['delegated_group'])) ? mysqli_real_escape_string($link, $_REQUEST['delegated_group']) : "";
        $k1 = mysqli_real_escape_string($link, $_REQUEST['k1']);
        $k2 = mysqli_real_escape_string($link, $_REQUEST['k2']);
        $kind = isset($_REQUEST['kind']) ? $_REQUEST['kind'] : 'READ_ACCESS';

        if (isset($_REQUEST['service']))
            $service = mysqli_real_escape_string($link, $_REQUEST['service']);
        else
            $service = "";
        if (isset($_REQUEST['servicePath']))
            $servicePath = mysqli_real_escape_string($link, $_REQUEST['servicePath']);
        else
            $servicePath = "";

        $protocol = getProtocol($cb, $link);

        if (empty($protocol)) { //it also ensure the contextbroker name is valid
            $result["status"] = "ko";
            $result['msg'] = "Unrecognized contextbroker/protocol";
            $result["error_msg"] .= "Problem in adding delegations (Unrecognized contextbroker/protocol)";
            $result["log"] = "action=add_delegations - error Unrecognized contextbroker/protocol\r\n";
        } else {
            //id management
            if ($protocol == "ngsi w/MultiService")
                $id = $service . "." . $servicePath . "." . $id;
            get_device($username, $role, $id, $cb, $accessToken, $link, $result, true);
            if (empty($result["content"])) {
                $result["status"] = "ko";
                $result['msg'] = "Unrecognized device";
                $result["error_msg"] .= "Problem in adding delegations (Unrecognized device)";
                $result["log"] = "action=add_delegations - error Unrecognized device\r\n";
            } else {
                $dev_organization = $result["content"]["organization"];
                $uri = $result["content"]["uri"];
                $eId = $dev_organization . ":" . $cb . ":" . $id;

                if (!enforcementRights($username, $accessToken, $role, $eId, 'IOTID', 'write', $result)) {
                    $result["status"] = "ko";
                    $result['msg'] = "Not ownership or enough right to update";
                    $result["error_msg"] .= "Problem in adding delegation (Not ownership or enough right to update)";
                    $result["log"] = "action=add_delegations - error Not ownership or enough right to update\r\n";
                } else {
                    if (($delegated_user != "" || $delegated_group != "") && $username != "") {
                        //retrieve any values of this this device
                        $q1 = "SELECT * FROM event_values WHERE cb = '$cb' and device='$id'";
                        $values = mysqli_query($link, $q1);

                        //delegate the device
                        delegateDeviceValue($eId, $cb, NULL, $username, $delegated_user, $delegated_group, $accessToken, $k1, $k2, $result, $kind);
                        if($result["status"]=='ok'){

                            //delegate any values of this device

                            while ($value = mysqli_fetch_assoc($values)) {
                                delegateDeviceValue($uri . "/" . $value["value_name"], $cb, $value["value_name"], $username, $delegated_user, $delegated_group, $accessToken, $k1, $k2, $result, $kind);
                                if ($result["status"]!='ok')
                                {
                                    break;
                                }
                            }

                        }

                    } else {
                        $result["status"] = 'ko';
                        $result["msg"] = '\n the function delegate_value has been called without specifying mandatory parameters';
                        $result["log"] = '\n the function delegate_value has been called without specifying mandatory parameters';
                    }
                }
            }
        }
    }
    my_log($result);
    mysqli_close($link);
} else if ($action == "remove_delegation") {
    $missingParams = missingParameters(array('id', 'contextbroker', 'delegationId'));

    if (!empty($missingParams)) {
        $result["status"] = "ko";
        $result['msg'] = "Missing Parameters";
        $result["error_msg"] .= "Problem in remove delegation (Missing parameters: " . implode(", ", $missingParams) . " )";
        $result["log"] = "action=remove_delegation - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
    } else {
        $delegationId = mysqli_real_escape_string($link, $_REQUEST['delegationId']);
        if (isset($_REQUEST['userDelegated']))
            $userDelegated = mysqli_real_escape_string($link, $_REQUEST['userDelegated']);
        if (isset($_REQUEST['groupDelegated'])) {
            $provgroupDelegated = mysqli_real_escape_string($link, $_REQUEST['groupDelegated']);
            $indexcomma = strrpos($provgroupDelegated, "All groups");
            if ($indexcomma == false) {
                $indexcomma = strrpos($provgroupDelegated, ",");
                $groupDelegated = "cn=" . substr($provgroupDelegated, $indexcomma + 1) . ",ou=" . substr($provgroupDelegated, 0, $indexcomma) . "," . $GLOBALS["ldapBaseName"];
            } else {
                $groupDelegated = "ou=" . substr($provgroupDelegated, 0, $indexcomma - 1) . "," . $GLOBALS["ldapBaseName"];
            }
        }
        $cb = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
        $id = mysqli_real_escape_string($link, $_REQUEST['id']);
        if (isset($_REQUEST['service']))
            $service = mysqli_real_escape_string($link, $_REQUEST['service']);
        else
            $service = "";
        if (isset($_REQUEST['servicePath']))
            $servicePath = mysqli_real_escape_string($link, $_REQUEST['servicePath']);
        else
            $servicePath = "";

        $protocol = getProtocol($cb, $link);

        if (empty($protocol)) { //it also ensure the contextbroker name is valid
            $result["status"] = "ko";
            $result['msg'] = "Unrecognized contextbroker/protocol";
            $result["error_msg"] .= "Problem in remove delegations (Unrecognized contextbroker/protocol)";
            $result["log"] = "action=remove_delegations - error Unrecognized contextbroker/protocol\r\n";
        } else {
            //id management
            if ($protocol == "ngsi w/MultiService")
                $id = $service . "." . $servicePath . "." . $id;
            get_device($username, $role, $id, $cb, $accessToken, $link, $result);
            if (empty($result["content"])) {
                $result["status"] = "ko";
                $result['msg'] = "Unrecognized device";
                $result["error_msg"] .= "Problem in remove_delegations (Unrecognized device)";
                $result["log"] = "action=remove_delegations - error Unrecognized device\r\n";
            } else {
                $dev_organization = $result["content"]["organization"];
                $uri = $result["content"]["uri"];
                $eId = $dev_organization . ":" . $cb . ":" . $id;

                if (!enforcementRights($username, $accessToken, $role, $eId, 'IOTID', 'write', $result)) {
                    $result["status"] = "ko";
                    $result['msg'] = "Not ownership or enough right to update";
                    $result["error_msg"] .= "Problem in remove delegations (Not ownership or enough right to update)";
                    $result["log"] = "action=remove_delegations - error Not ownership or enough right to update\r\n";
                } else {
                    //remove delegation the device
                    removeDelegationValue($accessToken, $username, $delegationId, $result);

                    //retrieve any values of this device
                    $q1 = "SELECT * FROM event_values WHERE cb = '$cb' and device='$id'";
                    $values = mysqli_query($link, $q1);

                    //retrieve any delegation made on values of this this device
                    while ($value = mysqli_fetch_assoc($values)) {
                        getDelegatorDevice($accessToken, $username, $result, $uri . "/" . $value["value_name"]);
                        $delegated = $result["delegation"];
                        $i = 0;
                        while ($i < count($delegated)) {
                            if (isset($delegated[$i]["userDelegated"]) && (isset($userDelegated)) && $delegated[$i]["userDelegated"] == $userDelegated) {
                                $delegationId = $delegated[$i]["delegationId"];
                                removeDelegationValue($accessToken, $username, $delegationId, $result);
                            } else if (isset($delegated[$i]["groupDelegated"]) && (isset($groupDelegated)) && $delegated[$i]["groupDelegated"] == $groupDelegated) {
                                $delegationId = $delegated[$i]["delegationId"];
                                removeDelegationValue($accessToken, $username, $delegationId, $result);
                            }
                            $i++;
                        }
                    }
                }
            }
        }
    }
    my_log($result);
    mysqli_close($link);
}else if ($action == "retry_insertion") {

    $token = $_REQUEST["token"];
    $id = $_REQUEST["id"];
    $result["log"].=$id;
    $organization = $_REQUEST["organization"];
    $result["log"].=$organization;
    $cb = $_REQUEST["contextbroker"];
    $eid = $organization . ":" . $cb . ":" . $id;
    //controlla che il device sia di proprietà di chi richiede il retry
    getOwnerShipDevice($accessToken, $result, $eid);

    //se non ritorna nulla il device non è mio
    if (!empty($result["keys"])) {

        //se nelle chiavi restituite da "getOwnershipDevice", cè il dispositivo con cui sto provando a fare il retry
        //allora è mio e posso continuare
        $keys = array_keys($result["keys"]);

        foreach ($keys as $devEid) {

            if ($devEid == $eid) {
                //il device è mio posso provare il retry
                $result["log"].=$_REQUEST['is_in_db'];
                //caso errore is_in_db (Database)
                if (isset($_REQUEST['is_in_db']) && $_REQUEST['is_in_db'] != "success") {
                    $is_in_db = $_REQUEST['is_in_db'];

                    //caso "uri_generated_but_not_inserted"
                    if ($is_in_db == "uri_generated_but_not_inserted") {
                        $result["log"].="entrato";
                        if (isset($_REQUEST['id']) && isset($_REQUEST['organization']) && isset($_REQUEST['contextbroker'])) {
                            uri_not_inserted_recovery($id, $organization, $cb, $result, $link);
                        }
                    }
                }
                //caso errore is_in_kb (Knowledge Base)
                if (isset($_REQUEST['is_in_kb']) && $_REQUEST['is_in_kb'] != "success") {
                    $is_in_kb = $_REQUEST['is_in_kb'];
                    //....gestire casi kb
                }
                //caso errore is_in_broker (broker)
                if (isset($_REQUEST['is_in_broker']) && $_REQUEST['is_in_broker'] != "success") {
                    $is_in_kb = $_REQUEST['is_in_broker'];
                    //....gestire casi broker
                }

            } else {
                //non è mio il device non posso fare retry
            }
        }
   } else {
        $result["msg"].= "tornato vuoto keys";

      //non è mio il device non posso fare retry
}



//
//        }
//    }
//    if(isset($_REQUEST['is_in_kb']) && $_REQUEST['is_in_kb'] != "success"){
//        $is_in_kb = $_REQUEST['is_in_kb'];
//        kb_conn_error_recovery();
//    }
//    if(isset($_REQUEST['is_in_broker']) && $_REQUEST['is_in_broker'] != "success"){
//        $is_in_broker = $_REQUEST['is_in_broker'];
//        broker_conn_error_recovery($_REQUEST,$link,$username,$role,$token,$result);
//
//    }
//
//
//    $result["msg"] .= empty($is_in_broker);
//    $result["msg"] .= $_REQUEST['contextbroker'];
//    $result["msg"] .= $_REQUEST['organization'];
//    $result["msg"] .= $_REQUEST['id'];
//        $result["status"] = $_REQUEST['is_in_kb'];
//        $result["status"] .= $_REQUEST['is_in_db'];
//    $result["status"] .= $_REQUEST['is_in_broker'];
//    if(isset($_REQUEST['is_in_kb'])){
//        $error_to_recover=$_REQUEST['error_to_recover'];
//    }
    //$result["status"]="ok";

    my_log($result);
    return $result;

}



/* used by nodered, is it still supported?
  else if ($action=="get_config_data")
  {
  $username = mysqli_real_escape_string($link, $_REQUEST['username']);
  $loggedrole= mysqli_real_escape_string($link, $_REQUEST['loggedrole']);
  $organization = mysqli_real_escape_string($link, $_REQUEST['organization']);


  if (!empty($accessToken))
  {
  getOwnerShipDevice($accessToken, $result);
  getDelegatedDevice($accessToken, $username, $result);

  }

  $q = "SELECT d.`contextBroker`, d.`id`, d.`uri`, d.`devicetype` AS entityType, d.`kind`,
  CASE WHEN mandatoryproperties AND mandatoryvalues THEN \"active\" ELSE \"idle\" END AS status1,
  d.`macaddress`, d.`model`, d.`producer`, d.`longitude`, d.`latitude`, d.`protocol`, d.`format`, d.`visibility`,
  d.`frequency`, d.`created`, d.`privatekey`, d.`certificate`,d.`organization`, cb.`name`, cb.`protocol` as type, cb.`accesslink` AS ip, cb.`accessport` AS port, cb.`sha`, d.`subnature`, d.`static_attributes` FROM `devices` d JOIN `contextbroker` cb ON (d.contextBroker=cb.name) where d.deleted IS null order by d.id";

  //$r=create_datatable_data($link,$_REQUEST,$q, "d.deleted IS null"); //order by d.id
  $r = mysqli_query($link, $q);

  if($r)
  {
  $res = array();
  $result["log"]= "\r\n action=get_config_data " . $q . " \r\n";
  while($row = mysqli_fetch_assoc($r))
  {

  $eid=$row["organization"].":".$row["contextBroker"].":".$row["id"];

  if ( (
  ($row["organization"]==$organization)&&
  (
  ($row["visibility"]=='public'
  ||
  (isset($result["delegation"][$eid])&& $result["delegation"][$eid]["kind"]=="anonymous")
  )
  )
  )
  ||
  (isset($result["delegation"][$eid])&& $result["delegation"][$eid]["kind"]!="anonymous")

  || (isset($result["keys"][$eid]) && $result["keys"][$eid]["owner"]==$username)

  )
  {

  $rec= array();
  $rec["contextBroker"]= $row["contextBroker"];
  $rec["id"]= $row["id"];
  $rec["uri"]= $row["uri"];
  $rec["entityType"]= $row["entityType"];
  $rec["kind"]= $row["kind"];
  $rec["status1"]= $row["status1"];
  $rec["macaddress"]= $row["macaddress"];
  $rec["model"]= $row["model"];
  $rec["producer"]= $row["producer"];
  $rec["longitude"]= $row["longitude"];
  $rec["latitude"]= $row["latitude"];
  $rec["protocol"]= $row["protocol"];
  $rec["format"]= $row["format"];
  $rec["frequency"]= $row["frequency"];
  $rec["created"]= $row["created"];
  $rec["organization"]= $row["organization"];
  $rec["subnature"]=($row["subnature"]==null)?"":$row["subnature"];
  $rec["staticAttributes"]=($row["static_attributes"]==null)?"":$row["static_attributes"];

  $rec["sha"]= $row["sha"];
  $rec["privatekey"]= "";
  $rec["certificate"]= "";
  $rec["edgegateway_type"]= "";
  $rec["edgegateway_uri"]= "";

  $rec["name"]=$row["name"];
  $rec["type"]=$row["type"];
  $rec["ip"]=$row["ip"];
  $rec["port"]=$row["port"];




  if(((isset($result["keys"][$eid]))&&($loggedrole!=='RootAdmin'))
  ||
  ((isset($result["keys"][$eid]))&& ($result["keys"][$eid]["owner"]==$username) && ($loggedrole==='RootAdmin')))

  {

  if ($row["visibility"]=="public")
  {
  $rec["visibility"]= "MyOwnPublic";
  }

  else
  {
  if (isset($result["delegation"][$row["uri"]])
  && $result["delegation"][$row["uri"]]["kind"]=="anonymous")
  $rec["visibility"]= "MyOwnPublic";

  else
  $rec["visibility"]="MyOwnPrivate";

  }

  $rec["k1"]=$result["keys"][$eid]["k1"];
  $rec["k2"]=$result["keys"][$eid]["k2"];
  $rec["edgegateway_type"]= $result["keys"][$eid]["edgegateway_type"];
  $rec["edgegateway_uri"]= $result["keys"][$eid]["edgegateway_uri"];

  // the following two information should be shown only to the device owner
  $rec["privatekey"]= $row["privatekey"];
  $rec["certificate"]= $row["certificate"];

  }

  else
  {//it's not mine
  if (isset($result["delegation"][$eid])
  && ($result["delegation"][$eid]["kind"]=="anonymous"))
  {//it's delegated as public

  //Fatima2019: I changed here the visibility to appear as public instead of delegated in case the
  //delegation was to "Anonymous"
  //$rec["visibility"]='publicly delegated';

  $rec["visibility"]='public';
  $rec["k1"]="";
  $rec["k2"]="";
  }
  else if (isset($result["delegation"][$eid]))
  {//it's delegated personally
  $rec["visibility"]='delegated';
  $rec["k1"]="";
  $rec["k2"]="";

  if (isset($result["delegation"][$eid]["k1"]))

  {
  $rec["k1"]= $result["delegation"][$eid]["k1"]; // to be fixed
  $rec["k2"]= $result["delegation"][$eid]["k2"]; // to be fixed

  }
  }

  else {
  $rec["visibility"]= $row["visibility"];
  $rec["k1"]="";
  $rec["k2"]="";

  }


  }
  $res[$row["id"]]=$rec;
  }
  }
  $result["status"]="ok";
  $result["content"]=$res;
  }
  else
  {
  $result["status"]="ko";
  $result['msg'] = generateErrorMessage($link);
  $result['log'] = "\r\n action=get_config_data " . generateErrorMessage($link);

  }
  my_log($result);
  mysqli_close($link);
  } */
/* used by nodered, still suppported?
  else if ($action == 'get_config_data_values')
  {
  //this action is used for node-red
  $username = mysqli_real_escape_string($link, $_REQUEST['username']);
  $loggedrole= mysqli_real_escape_string($link, $_REQUEST['loggedrole']);
  $organization = mysqli_real_escape_string($link, $_REQUEST['organization']);

  if (!empty($accessToken))
  {
  getOwnerShipDevice($accessToken, $result);
  getDelegatedDevice($accessToken, $username, $result);

  }
  $res=array();


  $q  = "SELECT v.cb, v.device, v.value_name, v.data_type, v.value_type, v.editable, v.value_unit, v.healthiness_criteria, d.organization, d.latitude, d.longitude, d.protocol, d.visibility, d.kind, d.uri FROM `event_values` v JOIN devices d ON (v.device=d.id and v.cb = d.contextbroker)  WHERE d.deleted IS null ORDER BY v.value_name;"; //  WHERE deleted IS null AND visibility=\"public\"  ORDER BY v.value_name;";


  //$r=create_datatable_data($link,$_REQUEST,$q, " d.deleted IS null ORDER BY v.value_name");
  $r = mysqli_query($link, $q);

  if($r)
  {
  $result["log"]= "\r\n action=get_config_data_values " . $q . " \r\n";

  while($row = mysqli_fetch_assoc($r))
  {
  $eid=$row["organization"].":".$row["cb"].":".$row["device"];
  if (
  //private
  (isset($result["keys"][$eid])
  &&((($result["keys"][$eid]["owner"]==$username) && ($loggedrole==='RootAdmin'))
  ||
  ($loggedrole!='RootAdmin')
  )
  )
  ||//delegated value
  (isset($row["uri"]) && $row["uri"]!="" &&
  (
  (isset($result["delegation"][$row["uri"] . "/" . $row["value_name"]])
  &&  $result["delegation"][$row["uri"] . "/" . $row["value_name"]]["kind"]!="anonymous")
  ||
  (isset($result["delegation"][$eid])
  &&  $result["delegation"][$eid]["kind"]!="anonymous")
  )

  )
  ||//device is public in the organization
  (
  ($row["organization"]==$organization)&&
  (
  ($row["visibility"]=='public'
  ||
  (isset($result["delegation"][$eid])&& $result["delegation"][$eid]["kind"]=="anonymous")
  )
  )
  )
  )
  {
  $rec= array();
  $rec["cb"]= $row["cb"];
  $rec["device"]= $row["device"];
  $rec["value_name"]= $row["value_name"];
  $rec["data_type"]= $row["data_type"];
  $rec["value_type"]= $row["value_type"];
  $rec["editable"]= $row["editable"];
  $rec["value_unit"]= $row["value_unit"];
  $rec["healthiness_criteria"]= $row["healthiness_criteria"];
  $rec["latitude"]= $row["latitude"];
  $rec["longitude"]= $row["longitude"];
  $rec["kind"]= $row["kind"];



  if(isset($result["keys"][$eid])
  &&((($result["keys"][$eid]["owner"]==$username) && ($loggedrole==='RootAdmin'))
  ||
  ($loggedrole!='RootAdmin')
  )
  )
  {
  $rec["visibility"]="private";
  if(isset($result["keys"][$rec["device"]])){
  $rec["k1"]=$result["keys"][$rec["device"]]["k1"];
  $rec["k2"]=$result["keys"][$rec["device"]]["k2"];
  $rec["edgegateway_type"]= $result["keys"][$rec["device"]]["edgegateway_type"];
  $rec["edgegateway_uri"]= $result["keys"][$rec["device"]]["edgegateway_uri"];
  }
  else{
  $rec["k1"]=$result["keys"][$eid]["k1"];
  $rec["k2"]=$result["keys"][$eid]["k2"];
  $rec["edgegateway_type"]= $result["keys"][$eid]["edgegateway_type"];
  $rec["edgegateway_uri"]= $result["keys"][$eid]["edgegateway_uri"];
  }

  }
  else if(isset($row["uri"]) && $row["uri"]!="" &&
  (
  (isset($result["delegation"][$row["uri"] . "/" . $row["value_name"]])
  &&  $result["delegation"][$row["uri"] . "/" . $row["value_name"]]["kind"]!="anonymous")
  ||
  (isset($result["delegation"][$eid])
  &&  $result["delegation"][$eid]["kind"]!="anonymous")
  )

  ){

  if (isset($result["delegation"][$row["uri"] . "/" . $row["value_name"]]["k1"]))
  {
  $rec["k1"]=$result["delegation"][$row["uri"] . "/" . $row["value_name"]]["k1"]; // to be fixed
  $rec["k2"]=$result["delegation"][$row["uri"] . "/" . $row["value_name"]]["k2"]; // to be fixed
  }
  else if(isset($result["delegation"][$eid]["k1"])){
  $rec["k1"]=$result["delegation"][$eid]["k1"]; // to be fixed
  $rec["k2"]=$result["delegation"][$eid]["k2"]; // to be fixed
  }
  $rec["visibility"]="delegated";
  }
  else{
  $rec["visibility"]="public";
  $rec["k1"]="";
  $rec["k2"]="";
  $rec["edgegateway_type"]= "";
  $rec["edgegateway_uri"]= "";
  }




  array_push($res, $rec);
  }
  }
  $result["status"]="ok";
  $result["content"]=$res;
  }
  else
  {
  $result["status"]="ko";
  $result['msg'] = generateErrorMessage($link);
  $result["log"]= "\r\n action=get_config_data_values " . $q .  generateErrorMessage($link) ." \r\n";
  }
  my_log($result);
  mysqli_close($link);
  } */ else {
    $result['status'] = 'ko';
    $result['msg'] = 'invalid action ' . $action;
    $result['log'] = 'invalid action ' . $action;
    my_log($result);
    mysqli_close($link);
}

function compare_values($obj_a, $obj_b) {
    return strcasecmp($obj_a->value_name, $obj_b->value_name);
}

function deviceBcCertification($name,$type,$frequency,$kind,$protocol,$format,$producer,$subnature,$staticAttributes,$service,$servicePath,$listAttributes,$organization,$accessToken,$contextbroker){
        if(isset($GLOBALS['blockchainApiBaseUrl'])) {
            $blockchainApiBaseUrl = $GLOBALS['blockchainApiBaseUrl'];
        } else {
            error_log("IOT-DIR-BC ERROR device.php missing blockchainApiBaseUrl in configuration");
            return -1;
        }
        $listAttributes = str_replace( '\\', '', $listAttributes);
        $staticAttributes = str_replace( '\\', '', $staticAttributes);

        $bcmessage = array(
            'name' => $name,
            'type' => $type,
            'contextbroker' => $contextbroker,
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
        $bearer='Bearer '.$accessToken;
        $headers = array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($bcmessage),
            'authorization: '. $bearer
        );

        $ch = curl_init();
        $urlBc= $blockchainApiBaseUrl . '/api/adddevice/';
        curl_setopt($ch, CURLOPT_URL, $urlBc);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $bcmessage);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response_bc = curl_exec($ch);

        if(curl_errno($ch)){
            error_log("IOT-DIR-BC ERROR device.php sending POST $urlBc ".$bcmessage." : ".curl_error($ch));
            //echo 'Request Error:' . curl_error($ch);
            $result = 0;
        } else {
            $response_bc = json_decode($response_bc);
            $result = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if($result != 200) {
                error_log("IOT-DIR-BC ERROR device.php sending POST $urlBc ".$bcmessage." : CODE " . $result . " " . $response_bc);
            }
        }
        curl_close($ch);
        return $result;

}

function obfuscate_data_values($json_data) {
    //oscura i dati del payload di un device se la delega è WRITE_ONLY
    foreach ($json_data as $key => &$value) {
        if (is_array($value) && array_key_exists("value", $value)) {
            $value["value"] = "HIDDEN";
        }
    }
    return $json_data;
}



function uri_not_inserted_recovery($id,$organization,$contextbroker,&$result,$link){
      try {
      $generated_uri="http://www.disit.org/km4city/resource/iot/" . $contextbroker . "/" .$organization."/".$id;

      //TODO:da sostituire prima parte dell url con $GLOBALS["superservicemapURI"]

      $SSM_url="http://dashboard/superservicemap/"."api/v1?serviceUri=".$generated_uri;

      //chiamata alla service map
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $SSM_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        $result_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        //400 -> device non trovato
        //401-> non autorizzato ma ha trovato il device
        //altri codici-> riprovare
        if($result_code == 400){
            $result['error_msg'] .= "Device not found in ServiceMap";
            $result['log'] .= "Device not found in ServiceMap";
            $result['msg'] .= "Error not recoverable";
            $result['status'] = 'ko' ;
            $result["is_in_db"]="Device not found in the Service map";

        }else if($result_code==401){
            $result['log'] .= "Device found in ServiceMap, ";
            $q = "UPDATE devices SET uri='$generated_uri' WHERE id='$id' and contextBroker='$contextbroker' and organization='$organization'";
            if(executeQueryWithRetries($link, $q)){
                $result['log'] .= "Uri inserted in DB, ";
                $q = "UPDATE devices SET is_in_db='success' WHERE id='$id' and contextBroker='$contextbroker' and organization='$organization'";
                if(executeQueryWithRetries($link, $q)){
                    $result["log"] .= "Uri recovered, ";
                    $result['status'] = 'ok' ;
                    $result["is_in_db"]="success";

                }else{
                    $result["error_msg"] .= "Timeout inserting uri in db, ";
                    $result['status'] = 'ko' ;
                    $result["is_in_db"] ="Timeout inserting uri in db";
                }

            }else{
                $result["error_msg"] .= "Timeout inserting uri in db, ";
                $result['status'] = 'ko' ;
                $result["is_in_db"] = "Timeout inserting uri in db";
            }
            //mysqli_close($link);
        }else{
            //ne 400 ne 401
            $result["log"] .= "ServiceMap is unavailable, ";
            $result['status'] = 'ko' ;
            $result["is_in_db"]="Service map unavailable";
        }
      }catch (Exception $e){
          $result["error_msg"].=$e;
          $result["is_in_db"]="Something went wrong";
      }
    return $result;
}


function kb_conn_error_recovery(){
    return 0;
}



//function broker_conn_error_recovery($req,$link,$username,$role,$accessToken,&$result){
//    try {
//        $id = $req['id'];
//        if (isset($req['id'])) {
//            $name = $req['id'];
//            $id = $req['id'];
//        }
//        if (isset($req['devicetype'])) {
//            $type = $req['devicetype'];
//        }
//        if (isset($req['contextbroker'])) {
//            $contextbroker = $req['contextbroker'];
//        }
//        if (isset($req['kind'])) {
//            $kind = $req['kind'];
//        }
//        if (isset($req['protocol'])) {
//            $protocol = $req['protocol'];
//        }
//        if (isset($req['format'])) {
//            $format = $req['format'];
//        }
//        if (isset($req['model'])) {
//            $model = $req['model'];
//        }
//        if (isset($req['latitude'])) {
//            $latitude = $req['latitude'];
//        }
//        if (isset($req['longitude'])) {
//            $longitude = $req['longitude'];
//        }
//        if (isset($req['visibility'])) {
//            $visibility = $req['visibility'];
//        }
//        if (isset($req['frequency'])) {
//            $frequency = $req['frequency'];
//        }
//        if (isset($req['service'])) {
//            $service = $req['service'];
//        }
//        if (isset($req['servicepath'])) {
//            $servicePath = $req['servicepath'];
//        }
//        $query = "SELECT * from contextbroker WHERE name = '$contextbroker'";
//        $r = mysqli_query($link, $query);
//        if (!$r) {//row should also be NOT empty since enforcement has been already made in the api
//            $result["status"] = 'ko';
//            $result["error_msg"] .= "Error in reading data from context broker.";
//            $result["msg"] .= ' error in reading data from context broker ' . mysqli_error($link);
//            $result["log"] .= ' error in reading data from context broker ' . mysqli_error($link) . $query;
//            return 1;
//        }
//        $rowCB = mysqli_fetch_assoc($r);
//        if ($rowCB["kind"] == 'external')
//
//            $ip = $rowCB["ip"];
//        $port = $rowCB["port"];
//
//
////        if ($protocol == "ngsi w/MultiService")
////            $id = $service . "." . $servicePath . "." . $id;
//        get_device($username, $role, $id, $contextbroker, $accessToken, $link, $result);
//
////    if (empty($result["content"])) {
////        $result["status"] = "ko";
////        $result['msg'] = "Unrecognized device";
////        $result["error_msg"] .= "Problem in get_device_attribute (Unrecognized device)";
////        $result["log"] = "action=get_device_attributes - error Unrecognized device\r\n";
////    } else {
////        $dev_organization = $result["content"]["organization"];
////        $eId = $dev_organization . ":" . $contextbroker . ":" . $id;
////
////        if (!enforcementRights($username, $accessToken, $role, $eId, 'IOTID', 'read', $result)) {
////            $result["status"] = "ko";
////            $result['msg'] = "Not ownership or enough right to update";
////            $result["error_msg"] .= "Problem in get device attributes (Not ownership or enough right to update)";
////            $result["log"] = "action=get_device_attributes - error Not ownership or enough right to update\r\n";
////        } else {
////            $q1 = "SELECT * FROM event_values WHERE cb = '$contextbroker' AND device = '$id'";
////            $r1 = mysqli_query($link, $q1);
////            $attributes = array();
////            if ($r1) {
////                while ($row = mysqli_fetch_assoc($r1)) {
////                    $rec = array();
////                    $rec["cb"] = $row["cb"];
////                    $rec["device"] = $row["device"];
////                    $rec["value_name"] = $row["value_name"];
////                    $rec["data_type"] = $row["data_type"];
////                    $rec["value_type"] = $row["value_type"];
////                    $rec["editable"] = $row["editable"];
////                    $rec["value_unit"] = $row["value_unit"];
////                    $rec["order"] = $row["order"];
////                    $rec["healthiness_criteria"] = $row["healthiness_criteria"];
////                    $rec["real_time_flag"]=$row["real_time_flag"];
////                    if ($rec["healthiness_criteria"] == "refresh_rate")
////                        $rec["healthiness_value"] = $row["value_refresh_rate"];
////                    if ($rec["healthiness_criteria"] == "different_values")
////                        $rec["healthiness_value"] = $row["different_values"];
////                    if ($rec["healthiness_criteria"] == "within_bounds")
////                        $rec["healthiness_value"] = $row["value_bounds"];
////                    array_push($attributes, $rec);
////                }
////                $result['status'] = 'ok';
////                $result['content'] = $attributes;
////                $result['log'] .= "\n\r action:get_device_attributes. access to " . $q1;
////            } else {
////                $result['status'] = 'ko';
////                $result['msg'] = 'Error: errors in reading data about devices. <br/>' . generateErrorMessage($link);
////                $result['log'] .= '\n\naction:get_device_attributes. Error: errors in reading data about devices. ' . generateErrorMessage($link);
////            }
////        }
//
//
////    insert_ngsi($link, $name, $type, $contextbroker, $kind, $protocol, $format, $model, $latitude, $longitude, $visibility, $frequency,
////        $listnewAttributes, $ip, $port, &$result, $service = "", $servicePath = "");
//        //return 0;
//    }catch(Exception $e){
//        $result["log"].= debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
//    }
//}
//function broker_excp_error_recovery(){
//    return 0;
//}

function executeQueryWithRetries($link, $query, $retries = 3, $delay = 1000000) {
    $attempts = 0;
    while ($attempts < $retries) {
        $result = mysqli_query($link, $query);
        if ($result) {
            return $result;
        }
        $attempts++;
        usleep($delay); // wait for the specified delay in microseconds (1 second = 1,000,000 microseconds)
    }
    return false;
}