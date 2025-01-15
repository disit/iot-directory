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

error_reporting(E_ERROR);

if (!$link->set_charset("utf8")) {
    exit();
}

if (isset($_REQUEST['action']) && !empty($_REQUEST['action'])) {
    $action = $_REQUEST['action'];
} else {
    $result['status'] = 'ko';
    $result['msg'] = 'action not present';
    $result['error_msg'] = 'action not present';
    $result['log'] = 'contextbroker.php action not present';
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
        error_log("---- contextbroker.php:" . (microtime(true) - $mctime));
        $accessToken = $tkn->access_token;
    }
}
if (empty($accessToken)) {
    $result["status"] = "ko";
    $result['msg'] = "Access Token not present";
    $result["error_msg"] .= "Access Token not present";
    $result["log"] = "contextbroker.php AccessToken not present\r\n";
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
    $result["log"] = "contextbroker.php action=$action - error Cannot retrieve user information $accessToken \r\n";
    my_log($result);
    mysqli_close($link);
    echo json_encode($result);
    exit();
}

if ($action == 'is_broker_up') {
    $result["log"] .= "\n invoked is_broker_up from device.php";

    $missingParams = missingParameters(array('contextbroker', 'version'));

    if (!empty($missingParams)) {
        $result["status"] = "ko";
        $result['msg'] = "Missing Parameters";
        $result["error_msg"] = "Problem getting device data (Missing parameters: " . implode(", ", $missingParams) . " )";
        $result["log"] .= "\n action=is_broker_up - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
        my_log($result);
        echo $missingParams;
    } else {
        $cb = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
        $version = mysqli_real_escape_string($link, $_REQUEST['version']);
        if (isset($_REQUEST['services']))
            $service = mysqli_real_escape_string($link, $_REQUEST['services']);
        else
            $service = "";
        if (isset($_REQUEST['path']))
            $servicePath = mysqli_real_escape_string($link, $_REQUEST['path']);
        else
            $servicePath = "";

        $result["log"] .= "\n cb:" . $cb . " services:" . $service . " path:" . $servicePath;

        $protocol = getProtocol($cb, $link);

        if (empty($protocol)) {//it also ensure the contextbroker name is valid
            $result["status"] = "ko";
            $result['msg'] = "Unrecognized contextbroker/protocol";
            $result["error_msg"] = "Problem in get device data (Unrecognized contextbroker/protocol)";
            $result["log"] .= "\n action=is_broker_up - error Unrecognized contextbroker/protocol\r\n";
        } else {
            is_broker_up($link, $cb, $service, $servicePath, $version, $organization, $result);
        }
    }

    $result["log"] .= "\n returning " . json_encode($result) . " from is_broker_up from device.php";

    simple_log($result);
    mysqli_close($link);
} else if ($action == "take_default_CB_take") {

    $organization = findLdapOrganizationalUnit($username, $ldapBaseName, $ldapServer, $ldapPort, $ldapAdminName, $ldapAdminPwd);
    if (empty($organization)) {
        $result["status"] = "ko";
        $result['msg'] = "Organization not found in AccessToken for user " . $username;
        $result['error_msg'] .= "Organization not found in AccessToken for user " . $username;
        $result["log"] = "action=get_user_info -" . " Organization not found in AccessToken for user " . $username . "\r\n";
    }


    $query = "SELECT * FROM orionbrokers where ( status='free' or  status='taken') and organization= '$organization'";

    $r = mysqli_query($link, $query);
    $DefaultCB = mysqli_fetch_assoc($r);
    if (!$r) { //existence of cb is guaranteed from previously enforcement
        $result["status"] = 'ko';
        $result["error_msg"] = "Error in reading data from orionbrokers table.";
        $result["msg"] = ' error in reading data from orionbrokers table' . mysqli_error($link);
        $result["log"] .= '\n error in reading data from orionbrokers table ' . mysqli_error($link) . $query;
        return 1;
    } else {

        $result["status"] = '{"status": "Ok - get CB "}';
        $result["content"] = json_encode($DefaultCB);

        $result["msg"] = 'response from the query in the  orionbrokers ';
        $result["log"] .= '\n response from the query  query in the  orionbrokers' . json_encode($DefaultCB);

        $name = $DefaultCB["name"];

        $query2 = "UPDATE orionbrokers SET status='taken', status_timestamp=NOW() WHERE name='$name' and organization='$organization'";
        $r2 = mysqli_query($link, $query2);
        $DefaultCB2 = mysqli_fetch_assoc($r2);
        $result["msg2"] = json_encode($DefaultCB2);

        echo json_encode($result);
    }
} else if ($action == "update_orion") {

    $name = mysqli_real_escape_string($link, $_REQUEST['name']);
    $org = mysqli_real_escape_string($link, $_REQUEST['org']);

    $q = "UPDATE `orionbrokers` SET `status`='upgrade', status_timestamp=NOW() where name= '$name' and organization='$org'";

    $r = mysqli_query($link, $q);

    $rupdate = mysqli_fetch_assoc($r);

    if (!$r) {
        $result["status"] = "ko";
        $result["error_msg"] = "Error in the connection with the orion context broker. ";
        $result["msg"] = "Error in the connection with the orion context broker";
        $result["log"] .= "\n Error in the connection with the orion context broker";
    } else {
        $result["status"] = "ok";
        $result["error_msg"] = "Connection with the orion context broker. ";
        $result["msg"] = "Succefull connection with the orion context broker";
        $result["log"] .= "\n Succefull connection with the orion context broker";
        $result["data"] = json_encode($rupdate);
    }
    echo json_encode($result);
} else if ($action == "orion_version") {



    $name = mysqli_real_escape_string($link, $_REQUEST['name']);
    $org = mysqli_real_escape_string($link, $_REQUEST['org']);

    $query_IP = "select ipaddr from  orionbrokers where name= '$name' and organization='$org'";
    $query_Port = "select external_port from  orionbrokers where name= '$name' and organization='$org'";
    $query_status = "select status, status_timestamp from  orionbrokers where name= '$name' and organization='$org'";

    $r_IP = mysqli_query($link, $query_IP);
    $r_Port = mysqli_query($link, $query_Port);
    $r_status = mysqli_query($link, $query_status);
    $rupdate_ip = mysqli_fetch_assoc($r_IP);
    $rupdate_Port = mysqli_fetch_assoc($r_Port);
    $rupdate_status = mysqli_fetch_assoc($r_status);

    $ip = json_encode($rupdate_ip, true);
    $ip = substr($ip, 11, -2);

    $port = json_encode($rupdate_Port);
    $port = substr($port, 18, -2);
    // echo $port;

    $url_orion = $ip . ':' . $port . '/version';
    if (substr($url_orion, 0, 4) != 'http') {
        $url_orion = "http://" . $url_orion;
    }


    //
    $result["Upstatus"] = null;
    $result["data"] = null;
    $result["Upstatus"] = ($rupdate_status);
    //  echo $result["Upstatus"];
    // echo $url_orion;
    try {

        $ch = curl_init($url_orion);
        $httpheader = array();
        if ($service != "")
            array_push($httpheader, 'Fiware-Service: ' . $service);
        if ($servicePath != "")
            array_push($httpheader, 'Fiware-ServicePath: ' . $servicePath);


        curl_setopt_array($ch, array(
            CURLOPT_HTTPGET => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_HTTPHEADER => $httpheader,
        ));

        $response_orion = curl_exec($ch);

        if ($response_orion === FALSE) {
            $result["status"] = "ko";
            $result["error_msg"] = "Error in the connection with the ngsi context broker. ";
            $result["msg"] = "Error in the connection with the ngsi context broker";
            $result["log"] .= "\n Error in the connection with the ngsi context broker";
        } else {
            $result["status"] = "ok";
            $result["error_msg"] = "Connection with the ngsi context broker. ";
            $result["msg"] = "Succefull connection with the ngsi context broker";
            $result["log"] .= "\n Succefull connection with the ngsi context broker";
            $result["data"] = json_decode($response_orion);
        }
    } catch (Exception $ex) {
        $result["status"] = 'ko';
        $result["error_msg"] = 'Error in connecting with the ngsi context broker. ';
        $result["msg"] = 'error in connecting with the ngsi context broker ';
        $result["log"] .= '\n error in connecting with the ngsi context broker ' . $ex;
        $result["Upstatus"] = json_decode($rupdate_status);
    }
    echo json_encode($result);
} else if ($action == "insert") {
    $missingParams = missingParameters(array('name', 'kind', 'ip', 'port', 'protocol', 'latitude', 'longitude'));

    if (!empty($missingParams)) {
        $result["status"] = "ko";
        $result['msg'] = "Missing Parameters";
        $result["error_msg"] .= "Problem in insert context broker (Missing parameters: " . implode(", ", $missingParams) . " )";
        $result["log"] = "action=insert - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
    } else {
        $name = mysqli_real_escape_string($link, $_REQUEST['name']);

        $kind = mysqli_real_escape_string($link, $_REQUEST['kind']);
        $ip = mysqli_real_escape_string($link, $_REQUEST['ip']);
        $port = mysqli_real_escape_string($link, $_REQUEST['port']);
        $protocol = mysqli_real_escape_string($link, $_REQUEST['protocol']);
        $latitude = mysqli_real_escape_string($link, $_REQUEST['latitude']);
        $longitude = mysqli_real_escape_string($link, $_REQUEST['longitude']);
        if (isset($_REQUEST['version']))
            $version = mysqli_real_escape_string($link, $_REQUEST['version']);
        else
            $version = "";
        if (isset($_REQUEST['login']))
            $login = mysqli_real_escape_string($link, $_REQUEST['login']);
        else
            $login = "";
        if (isset($_REQUEST['password']))
            $password = mysqli_real_escape_string($link, $_REQUEST['password']);
        else
            $password = "";
        if (isset($_REQUEST['accesslink']))
            $accesslink = mysqli_real_escape_string($link, $_REQUEST['accesslink']);
        else
            $accesslink = "";
        if (isset($_REQUEST['accessport']))
            $accessport = mysqli_real_escape_string($link, $_REQUEST['accessport']);
        else
            $accessport = "";
        if (isset($_REQUEST['path']))
            $path = mysqli_real_escape_string($link, $_REQUEST['path']);
        else
            $path = "";
        if (isset($_REQUEST['visibility']))
            $visibility = mysqli_real_escape_string($link, $_REQUEST['visibility']);
        else
            $visibility = "";
        if (isset($_REQUEST['sha']))
            $sha = mysqli_real_escape_string($link, $_REQUEST['sha']);
        else
            $sha = "";
        if (isset($_REQUEST['urlnificallback']))
            $urlnificallback = mysqli_real_escape_string($link, $_REQUEST['urlnificallback']); //TODO enforce mandatory for ngsi scenario
        else
            $urlnificallback = "";
        if (isset($_REQUEST['services']))
            $services = json_decode($_REQUEST['services']);
        else
            $services = json_decode("[]");
        for ($i = 0; $i < count($services); $i++) {
            $services[$i] = mysqli_real_escape_string($link, $services[$i]);
        }
        ////
        //     
        // change_Status($link, $name, $organization);
        $enable_direct_access = mysqli_real_escape_string($link, $_REQUEST['input_log']);
        $id = mysqli_real_escape_string($link, $_REQUEST['id']);
        $flag_reg = mysqli_real_escape_string($link, $_REQUEST['flag_CB']);

        ///
        //id management
        $eId = $organization . ':' . $name;

        checkRegisterOwnerShipObject($accessToken, 'BrokerID', $result);
        if ($result["status"] == 'ok') {
            // begin transaction
            mysqli_autocommit($link, FALSE);
            $success = TRUE;
            // queries execution
            $q = "INSERT INTO contextbroker(name, ip, kind, protocol, version, port, latitude, longitude, login, password, accesslink, accessport, 
					path, visibility, sha, organization, urlnificallback )
					VALUES('$name', '$ip', '$kind', '$protocol', '$version', '$port', '$latitude', '$longitude', '$login', '$password', '$accesslink','$accessport', 
					'$path', '$visibility', '$sha', '$organization', '$urlnificallback')";
            if (!mysqli_query($link, $q)){
                $success = FALSE;
                
                //echo $q;
            }
            if ($protocol == 'ngsi w/MultiService' && count($services) > 0) {
                // regex for syntax checking
                $serviceRegex = "/^([a-z]|_|[0-9]){1,25}$/";
                for ($i = 0; $i < count($services); $i++) {
                    $service = $services[$i];
                    // syntax checking
                    if (!preg_match($serviceRegex, $service))
                        $success = FALSE;
                    $qs = "INSERT INTO services(name, broker_name) VALUES ('$service', '$name')";
                    if (!mysqli_query($link, $qs))
                        $success = FALSE;
                }
            }
            if ($success) {
                // successful transaction
                mysqli_commit($link);

                logAction($link, $username, 'contextbroker', 'insert', $name, $organization, 'insertion CB into database', 'success');
                $result["status"] = 'ok';
                $result["log"] .= '\n\r action: insert ok. ' . $q;

                $ownmsg = array();
                $ownmsg["elementId"] = $eId;
                $ownmsg["elementName"] = $eId;
                $ownmsg["elementUrl"] = $accesslink;
                $ownmsg["elementType"] = "BrokerID";
                registerOwnerShipObject($ownmsg, $accessToken, 'BrokerID', $result);

                if ($flag_reg == true) {
                    if ($name != $default_name) {
                        $query = "UPDATE orionbrokers SET name='$name' WHERE id_orionbroker='$id' ";
                        $r = mysqli_query($link, $query);
                        $rupdate = mysqli_fetch_assoc($r);
                    }
                    if ($protocol == "ngsi w/MultiService") {
                        $query = "UPDATE orionbrokers SET multitenacy='true' WHERE id_orionbroker='$id' ";
                        $r = mysqli_query($link, $query);
                        $rupdate = mysqli_fetch_assoc($r);
                    }
                    if ($enable_direct_access == 'false') {
                        $query = "UPDATE orionbrokers SET status='deploy', enable_direct_access=0 WHERE id_orionbroker='$id' and organization='$organization'";
                    } else {
                        $query = "UPDATE orionbrokers SET status='deploy', enable_direct_access=1 WHERE id_orionbroker='$id' and organization='$organization'  ";
                    }
                    $r = mysqli_query($link, $query);
                    $rupdate = mysqli_fetch_assoc($r);
                    mysqli_commit($link);
                }
                if ($result["status"] == 'ok') {
                    logAction($link, $username, 'contextbroker', 'insert', $name, $organization, 'Registering the ownership of CB', 'success');

                    //if ngsi, try to subscribe
                    if (strpos($protocol, 'ngsi') !== false) {
                        $count = 0;
                        do {
                            nificallback_create($ip, $port, $name, $urlnificallback, $protocol, $services, $result); //TODO uniform with below (update scenario), same code is there
                            if ($result["retry"] == true) {
                                sleep(10);
                                $count++;
                            } else {
                                break;
                            }
                        } while ($count < 12);
                        //save subscription_id
                        $q = "UPDATE contextbroker SET subscription_id='" . $result["content"] . "' WHERE name='$name';";
                        $r = mysqli_query($link, $q);
                        if ($r) {
                            logAction($link, $username, 'contextbroker', 'insert', $name, $organization, 'Subscribe URL NIFI CALLBACK', 'success');
                            $result["status"] = 'ok';
                            $result["log"] .= '\n\r action: subscribe ok. ' . $q;
                        } else {
                            logAction($link, $username, 'contextbroker', 'insert', $name, $organization, 'Subscribe URL NIFI CALLBACK', 'faliure');
                            $result["status"] = 'ko';
                            $result["error_msg"] = "Error occurred when registering the subscription";
                            $result["log"] = "\n\r Error: An error occurred when subscription <br/>" .
                                    $result["msg"] = "Error: An error occurred when subscribe $name. <br/>" .
                                    mysqli_error($link) . ' Please enter again the context broker';
                        }
                    }
                } else {
                    logAction($link, $username, 'contextbroker', 'insert', $name, $organization, 'Registering the ownership of CB', 'faliure');
                }

                // successful transaction ---- include in transaction nifi scenario
                mysqli_commit($link);
            } else {
                // unsuccessful transaction
                mysqli_rollback($link);
                $result["status"] = 'ko';
                $result["error_msg"] = "Error occurred when registering the context broker $name. ";
                $result["msg"] = "Error: An error occurred when registering the context broker $name. <br/>" . mysqli_error($link) . ' Please enter again the context broker';
                $result["log"] = "\n\r Error: An error occurred when registering the context broker $name. <br/>" . mysqli_error($link);
                logAction($link, $username, 'contextbroker', 'insert', $name, $organization, '', 'faliure');
            }
        } else {
            $result["status"] = 'ko';
            $result["error_msg"] = "Error occurred when registering the context broker: limit quota excessed";
            $result["msg"] = "Error: An error occurred when registering the context broker: limit quota excessed";
            $result["log"] = "\n\r Error: An error occurred when registering the context broker: limit quota excessed";
            logAction($link, $username, 'contextbroker', 'insert', $name, $organization, 'limit quota excessed', 'faliure');
        }
    }

    my_log($result);
    mysqli_close($link);
} else if ($action == "update") {
    $missingParams = missingParameters(array('name', 'kind', 'ip', 'port', 'protocol', 'latitude', 'longitude'));

    if (!empty($missingParams)) {
        $result["status"] = "ko";
        $result['msg'] = "Missing Parameters";
        $result["error_msg"] .= "Problem in update context broker (Missing parameters: " . implode(", ", $missingParams) . " )";
        $result["log"] = "action=update - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
    } else {
        $name = mysqli_real_escape_string($link, $_REQUEST['name']);
        $kind = mysqli_real_escape_string($link, $_REQUEST['kind']);
        $ip = mysqli_real_escape_string($link, $_REQUEST['ip']);
        $port = mysqli_real_escape_string($link, $_REQUEST['port']);
        $protocol = mysqli_real_escape_string($link, $_REQUEST['protocol']);
        $latitude = mysqli_real_escape_string($link, $_REQUEST['latitude']);
        $longitude = mysqli_real_escape_string($link, $_REQUEST['longitude']);
        $direct_access_enable = mysqli_real_escape_string($link, $_REQUEST['log_orion']);
        if (isset($_REQUEST['version']))
            $version = mysqli_real_escape_string($link, $_REQUEST['version']);
        else
            $version = "";
        if (isset($_REQUEST['login']))
            $login = mysqli_real_escape_string($link, $_REQUEST['login']);
        else
            $login = "";
        if (isset($_REQUEST['password']))
            $password = mysqli_real_escape_string($link, $_REQUEST['password']);
        else
            $password = "";
        if (isset($_REQUEST['accesslink']))
            $accesslink = mysqli_real_escape_string($link, $_REQUEST['accesslink']);
        else
            $accesslink = "";
        if (isset($_REQUEST['accessport']))
            $accessport = mysqli_real_escape_string($link, $_REQUEST['accessport']);
        else
            $accessport = "";
        if (isset($_REQUEST['path']))
            $path = mysqli_real_escape_string($link, $_REQUEST['path']);
        else
            $path = "";
        if (isset($_REQUEST['visibility']))
            $visibility = mysqli_real_escape_string($link, $_REQUEST['visibility']);
        else
            $visibility = "";
        if (isset($_REQUEST['sha']))
            $sha = mysqli_real_escape_string($link, $_REQUEST['sha']);
        else
            $sha = "";
        if (isset($_REQUEST['urlnificallback']))
            $urlnificallback = mysqli_real_escape_string($link, $_REQUEST['urlnificallback']); //TODO enforce mandatory for ngsi scenario
        else
            $urlnificallback = "";
        if (isset($_REQUEST['services']))
            $services = json_decode($_REQUEST['services']);
        else
            $services = json_decode("[]");
        for ($i = 0; $i < count($services); $i++) {
            $services[$i] = mysqli_real_escape_string($link, $services[$i]);
        }

        //get the old urlnificallback info to eventually update. get also organization
        $q = "SELECT ip, port,urlnificallback, subscription_id,organization FROM iotdb.contextbroker  where name='$name'";
        $r = mysqli_query($link, $q);
        //$r = mysqli_fetch_assoc($r); 

        if ($r) {

            while ($row = mysqli_fetch_assoc($r)) {
                $old_ip = $row["ip"];
                $old_port = $row["port"];
                $old_urlnificallback = $row["urlnificallback"];
                $old_subscription_id = $row["subscription_id"];
                $obj_organization = $row["organization"];
            }
            if (!isset($obj_organization)) {
                $result["status"] = "ko";
                $result['msg'] = "Unrecognized context broker";
                $result["error_msg"] .= "Problem in update context broker (Unrecognized context broker)";
                $result["log"] = "action=update - error Unrecognized contect broker\r\n";
            } else {
                //id_management
                $eId = $obj_organization . ':' . $name;
                if (!enforcementRights($username, $accessToken, $role, $eId, 'BrokerID', 'write', $result)) {
                    $result["status"] = "ko";
                    $result['msg'] = "Not ownership or enough right to update";
                    $result["error_msg"] .= "Problem in update context broker  (Not ownership or enough right to update)";
                    $result["log"] .= "action=update - error Not ownership or enough right to update\r\n";
                } else {
                    //check that, if some tenents where removed, that tenants had no device register on it
                    // we do: old_services - new_services. the result contains the removed services
                    // we then check if we have one or more devices with a removed service
                    // if there are such devices, then throw error and abort
                    ////also we handle nifi subscriptions
                    $q = "SELECT name FROM  services where broker_name='$name'";
                    $r = mysqli_query($link, $q);
                    if ($r) {
                        $old_services = [];
                        $removed_services = [];
                        $added_services = [];
                        while ($row = mysqli_fetch_assoc($r)) {
                            array_push($old_services, $row["name"]);
                        }
                        for ($i = 0; $i < count($old_services); $i++) {
                            if (!in_array($old_services[$i], $services)) {
                                array_push($removed_services, $old_services[$i]);
                            }
                        }
                        for ($i = 0; $i < count($removed_services); $i++) {
                            $result["log"] .= "\n\r REMOVED SERVICE: " . $removed_services[$i];
                        }
                        for ($i = 0; $i < count($services); $i++) {
                            if (!in_array($services[$i], $old_services)) {
                                array_push($added_services, $services[$i]);
                            }
                        }
                        for ($i = 0; $i < count($added_services); $i++) {
                            $result["log"] .= "ADDED SERVICE: " . $added_services[$i];
                        }

                        $error_costrain_service = false;
                        if (count($removed_services) > 0) {
                            $a = "SELECT count(*) as count FROM iotdb.devices WHERE contextBroker = '$name' AND (";
                            for ($i = 0; $i < count($removed_services); $i++) {
                                $serv = $removed_services[$i];
                                $a = $a . "service = '" . $removed_services[$i] . "' OR ";
                            }
                            $a = substr($a, 0, -4);
                            $a = $a . ")";
                            $result["log"] .= "\n\r QUERY: '$a'";
                            $z = mysqli_query($link, $a);
                            if ($z) {
                                $row = mysqli_fetch_assoc($z);
                                $count = $row["count"];
                                if ($count > 0) {
                                    $error_costrain_service = true;
                                    $result["log"] .= "RESULT: R>0";
                                }
                            } else {
                                $error_costrain_service = true;
                                $result["log"] .= "query issue";
                            }
                        }

                        if ($error_costrain_service) {
                            logAction($link, $username, 'contextbroker', 'update', $name, $organization, 'One or more devices are associated with a deleted Service. Cannot continue', 'faliure');
                            $result["status"] = 'ko';
                            $result["error_msg"] = "One or more devices are associated with a deleted Service. Cannot continue";
                            $result["log"] .= "\n\r Error: One or more devices are associated with a deleted Service. Cannot continue <br/>" .
                                    $result["msg"] = "Error: One or more devices are associated with a deleted Service. Cannot continue. <br/>";
                        } else {

                            if (count($removed_services) > 0 || count($added_services) > 0 || $old_subscription_id == "") {
                                nificallback_delete($old_ip, $old_port, $old_subscription_id, $name, $protocol, $old_services, $result);
                                nificallback_create($ip, $port, $name, $urlnificallback, $protocol, $services, $result);

                                //and now update db
                                $q = "UPDATE contextbroker SET subscription_id='" . $result["content"] . "' WHERE name='$name';";
                                $r = mysqli_query($link, $q);
                                if ($r) {
                                    logAction($link, $username, 'contextbroker', 'insert', $name, $organization, 'Subscribe URL NIFI CALLBACK', 'success');
                                    $result["status"] = 'ok';
                                    $result["log"] .= '\n\r action: subscribe ok. ' . $q;
                                } else {
                                    //TODOC propagate error
                                    logAction($link, $username, 'contextbroker', 'insert', $name, $organization, 'Subscribe URL NIFI CALLBACK', 'failure');
                                    $result["status"] = 'ko';
                                    $result["error_msg"] = "Error occurred when registering the subscription";
                                    $result["log"] = "\n\r Error: An error occurred when subscription <br/>" .
                                            $result["msg"] = "Error: An error occurred when subscribe $name. <br/>" . mysqli_error($link) . ' Please enter again the context broker';
                                }
                            }



                            // begin transaction
                            mysqli_autocommit($link, FALSE);
                            $success = TRUE;
                            $q = "UPDATE contextbroker SET name = '$name', kind = '$kind', ip = '$ip', port = '$port', protocol = '$protocol', 
									version = '$version', latitude = '$latitude', longitude = '$longitude', login = '$login', password = '$password', 
									accesslink = '$accesslink', accessport = '$accessport', path = '$path', visibility = '$visibility', sha = '$sha', 
									organization='$obj_organization', urlnificallback='$urlnificallback' WHERE name = '$name' and organization='$obj_organization';";

                            if (!mysqli_query($link, $q)) {
                                $success = FALSE;
                            }

                            // delete old services
                            $qrs = "DELETE FROM services WHERE broker_name = '$name'";
                            if (!mysqli_query($link, $qrs))
                                $success = FALSE;
                            if ($protocol == 'ngsi w/MultiService' && count($services) > 0) {
                                // Regex for Syntax Checking
                                $serviceRegex = "/^([a-z]|_|[0-9]){1,25}$/";
                                // insert new services
                                for ($i = 0; $i < count($services); $i++) {
                                    $service = $services[$i];
                                    // Syntax Checking
                                    if (!preg_match($serviceRegex, $service))
                                        $success = FALSE;
                                    $qs = "INSERT INTO services(name, broker_name) VALUES ('$service', '$name')";
                                    if (!mysqli_query($link, $qs))
                                        $success = FALSE;
                                }
                            }
                            if ($direct_access_enable == '0' || $direct_access_enable == '1') {
                                $r = "UPDATE orionbrokers SET enable_direct_access='$direct_access_enable' WHERE name = '$name' and organization='$obj_organization';";
                                $a = mysqli_query($link, $r);
                                if (!$a) {
                                    $success = FALSE;
                                }
                            }

                            if ($success) {
                                $ownmsg = array();
                                $ownmsg["elementId"] = $eId;
                                $ownmsg["elementName"] = $eId;
                                $ownmsg["elementUrl"] = $accesslink;
                                $ownmsg["elementType"] = "BrokerID";
                                registerOwnerShipObject($ownmsg, $accessToken, 'BrokerID', $result);
                                if ($result["status"] == 'ok') {
                                    $result["log"] .= '\n\r action: update ok. ' . $q;
                                    logAction($link, $username, 'contextbroker', 'update', $name, $obj_organization, '', 'success');
                                    //update subscription if different urlnificallback
                                    if (($old_urlnificallback !== $urlnificallback || $old_ip !== $ip || $old_port != $port || $old_subscription_id == 'register') && ($urlnificallback !== 'null')) {
                                        $result["log"] .= '\n\r urlnificallback is changed to: ' . $urlnificallback . ' from ' . $old_urlnificallback;
                                        if ($old_subscription_id !== 'undefined' && $old_subscription_id !== '' && $old_subscription_id !== 'FAILED' && $old_subscription_id !== 'register') {
                                            nificallback_delete($old_ip, $old_port, $old_subscription_id, $name, $protocol, $services, $result);
                                        }
                                        nificallback_create($ip, $port, $name, $urlnificallback, $protocol, $services, $result); //TODO uniform with above (insert scenario), same code is there
                                        //save subscription_id
                                        $q = "UPDATE contextbroker SET subscription_id='" . $result["content"] . "' WHERE name='$name';";
                                        $r = mysqli_query($link, $q);
                                        if ($r) {
                                            logAction($link, $username, 'contextbroker', 'insert', $name, $obj_organization, 'Subscribe URL NIFI CALLBACK', 'success');
                                            $result["status"] = 'ok';
                                            $result["log"] .= '\n\r action: subscribe ok. ' . $q;
                                        } else {
                                            logAction($link, $username, 'contextbroker', 'insert', $name, $obj_organization, 'Subscribe URL NIFI CALLBACK', 'faliure');
                                            $result["status"] = 'ko';
                                            $result["error_msg"] = "Error occurred when registering the subscription";
                                            $result["log"] .= "\n\r Error: An error occurred when subscription <br/>" . mysqli_error($link) . ' Please enter again the context broker';
                                            $result["msg"] = "Error: An error occurred when subscribe $name. <br/>" . mysqli_error($link) . ' Please enter again the context broker';
                                        }
                                    }
                                } else {
                                    logAction($link, $username, 'contextbroker', 'update', $name, $obj_organization, 'in register ownership', 'faliure');
                                }
                                // successful transaction ----include in transaction nifi scenario
                                mysqli_commit($link);
                            } else {
                                // unsuccessful transaction
                                mysqli_rollback($link);
                                logAction($link, $username, 'contextbroker', 'update', $name, $obj_organization, '', 'faliure');
                                $result["status"] = 'ko';
                                $result["error_msg"] = "Error occurred when updating the context broker $name. <br/>. ";
                                $result["msg"] = "Error: An error occurred when updating the context broker $name. <br/>" . mysqli_error($link) . ' Please enter again the context broker';
                                $result["log"] .= "\n\r Error: An error occurred when updating the context broker $name. <br/>" . mysqli_error($link);
                            }
                        }
                    } else {
                        logAction($link, $username, 'contextbroker', 'update', $name, $obj_organization, '', 'faliure');
                        $result["status"] = 'ko';
                        $result["error_msg"] = "Error occurred when retrieve tenant. <br/>. ";
                        $result["msg"] = "Error: An error occurred when retrieve tenant. <br/>" . mysqli_error($link) . ' Please enter again the context broker';
                        $result["log"] .= "\n\r Error: An error occurred when retrieve tenant." . mysqli_error($link);
                    }
                }
            }
        } else {
            logAction($link, $username, 'contextbroker', 'update', $name, $obj_organization, '', 'faliure');
            $result["status"] = 'ko';
            $result["error_msg"] = "Error occurred when reading-updating the context broker $name. <br/>. ";
            $result["msg"] = "Error: An error occurred when reading-updating the context broker $name. <br/>" . mysqli_error($link) . ' Please enter again the context broker';
            $result["log"] .= "\n\r Error: An error occurred when reading-updating the context broker $name. <br/>" . mysqli_error($link);
        }
    }

    my_log($result);
    mysqli_close($link);
} else if ($action == "delete") {
    $missingParams = missingParameters(array('name'));

    if (!empty($missingParams)) {
        $result["status"] = "ko";
        $result['msg'] = "Missing Parameters";
        $result["error_msg"] .= "Problem in insert device (Missing parameters: " . implode(", ", $missingParams) . " )";
        $result["log"] = "action=insert - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
    } else {
        $name = mysqli_real_escape_string($link, $_REQUEST['name']);

        //id management
        $dev_organization = "";
        $q = "SELECT * FROM contextbroker WHERE name='$name'";
        $r = mysqli_query($link, $q);
        if ($r) {
            while ($row = mysqli_fetch_assoc($r)) {
                $dev_organization = $row["organization"];
                break;
            }

            if (empty($dev_organization)) {
                $result["status"] = "ko";
                $result['msg'] = "CB not found";
                $result["error_msg"] .= "Problem in delete context broker (CB not found)";
                $result["log"] .= "action=delete - error CB not found\r\n";
            } else {
                $elementId = $dev_organization . ':' . $name;
                if (!enforcementRights($username, $accessToken, $role, $elementId, 'IOTID', 'write', $result)) {
                    $result["status"] = "ko";
                    $result['msg'] = "Not ownership or enough right to update";
                    $result["error_msg"] .= "Problem in delete context broker (Not ownership or enough right to delete)";
                    $result["log"] .= "action=delete - error Not ownership or enough right to update\r\n";
                } else {
                    //ensure the context broker has no device currently register on it, to avoid dangling pending device in the ownership and KB
                    $q = "SELECT * FROM iotdb.devices where contextBroker= '$name';";
                    $r = mysqli_query($link, $q);
                    if ($r && mysqli_num_rows($r) == 0) {

                        //firstly retrieve data to invoke the unsubscription
                        $q = "SELECT * FROM contextbroker WHERE name='$name'";
                        $r = mysqli_query($link, $q);
                        //at this point, cb exist and is not empty!!!
                        //if ngsi, try to unsubscribe
                        while ($row = mysqli_fetch_assoc($r)) {
                            if (strpos($row["protocol"], 'ngsi') !== false) {
                                $servicesQueryString = "SELECT * FROM services WHERE broker_name = '$name'";
                                $sqr = mysqli_query($link, $servicesQueryString);
                                if ($sqr) {
                                    $services = array();
                                    while ($servicesRow = mysqli_fetch_assoc($sqr)) {
                                        array_push($services, $servicesRow["name"]);
                                    }
                                    nificallback_delete($row["ip"], $row["port"], $row["subscription_id"], $name, $row["protocol"], $services, $result);
                                } else {
                                    //return ok even if error are thrown
                                }
                            }
                        }

                        $q = "DELETE FROM contextbroker WHERE name = '$name' and organization='$organization';";
                        $r = mysqli_query($link, $q);
                        if ($r) {
                            $result["status"] = 'ok';
                            removeOwnerShipObject($elementId, $accessToken, "BrokerID", $result);
                            $query2 = "UPDATE iotdb.orionbrokers SET status='delete', status_timestamp=NOW() WHERE name='$name'";
                            $r2 = mysqli_query($link, $query2);
                            $DefaultCB2 = mysqli_fetch_assoc($r2);
                            if (!$r2) { //existence of cb is guaranteed from previously enforcement
                                $result["status"] = 'ko';
                                $result["error_msg"] = "Error in reading data from orionbrokers table.";
                                $result["msg"] = ' error in reading data from orionbrokers table' . mysqli_error($link);
                                $result["log"] .= '\n error in reading data from orionbrokers table ' . mysqli_error($link) . $query2;
                                return 1;
                            } else {

                                $result["status"] = 'ok';
                                $result["content"] = json_encode($DefaultCB2);

                                $result["msg"] = 'response from the query in the  orionbrokers ';
                                $result["log"] .= '\n response from the query  query in the  orionbrokers' . $DefaultCB2;
                                //   echo json_encode($result);
                            }
                            if ($result["status"] == 'ok') {
                                $result["log"] .= '\n\r action: delete ok. ' . $q;
                                logAction($link, $username, 'contextbroker', 'delete', $name, $organization, '', 'success');
                            } else {
                                $result["log"] .= '\n\r action: delete ok from database, delete Ownership failed. ';
                                logAction($link, $username, 'contextbroker', 'delete', $name, $organization, 'delete ok from database, delete Ownership failed.', 'faliure');
                            }
                        } else {
                            logAction($link, $username, 'contextbroker', 'delete', $name, $organization, '', 'faliure');
                            $result["status"] = 'ko';
                            $result["error_msg"] = 'Context broker ' . $name . ' &nbsp; deletion failed. ';
                            $result["msg"] = 'Context broker <b>' . $name . '</b> &nbsp; deletion failed, ' . mysqli_error($link) . ' Please enter again.';
                            $result["log"] = '\n\r Context broker <b>' . $name . '</b> &nbsp; deletion failed, ' . mysqli_error($link) . $q;
                        }
                    } else {
                        logAction($link, $username, 'contextbroker', 'delete', $name, $organization, '', 'faliure');
                        $result["status"] = 'ko';
                        $result["error_msg"] = 'Context broker ' . $name . ' deletion failed. <br>The choosen context broker still got some device registered.';
                        $result["log"] = '\n\r Context broker ' . $name . ' deletion failed. The choosen context broker still got some device registered';
                    }
                }
            }
        } else {
            logAction($link, $username, 'contextbroker', 'delete', $name, $organization, '', 'faliure');
            $result["status"] = 'ko';
            $result["error_msg"] = 'Context broker ' . $name . ' &nbsp; deletion failed. Not found. ';
            $result["msg"] = 'Context broker <b>' . $name . '</b> &nbsp; deletion failed, Not Found' . mysqli_error($link) . ' Please enter again.';
            $result["log"] = '\n\r Context broker <b>' . $name . '</b> &nbsp; deletion failed, Not found' . mysqli_error($link) . $q;
        }
    }



    my_log($result);
    mysqli_close($link);
} 
else if ($action == 'get_all_contextbroker' || $action == "get_all_contextbroker_simple") {
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

    if (isset($_REQUEST['number'])) {
       // $cb = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
        $q = "SELECT DISTINCT count(*), contextBroker FROM iotdb.temporary_devices group by contextBroker ;";
        $r = mysqli_query($link, $q);

        if ($r) {
            // $row = mysqli_fetch_assoc($r);
            // $row= json_encode($row);
            $cb_number = array();
            while ($row = mysqli_fetch_assoc($r)) {

                $sub_row = array($row["contextBroker"] => $row["count(*)"]);
                array_push($cb_number, $sub_row);
            }

            $result['status'] = 'ok';
           
            $result['log'] .= "\n\r action:get_all_contextbroker: number. access to " . $q;
        } else {
            $result['status'] = 'ko'; // . $q1 . generateErrorMessage($link);
            $result['msg'] = 'Error: errors in reading data about cb. <br/>' . generateErrorMessage($link);
            $result['log'] .= '\n\naction:get_all_contextbroker: number. Error: errors in reading data about devices. ' . generateErrorMessage($link);
                my_log($result);
                return;
        }
        
    }

    get_all_contextbrokers($username, $organization, $role, $accessToken, $link, $length, $start, $draw, $_REQUEST, $selection, $result);
 $result['number'] = $cb_number;
    my_log($result);
    mysqli_close($link);
} else if ($action == "get_all_contextbroker_latlong") {
    getOwnerShipObject($accessToken, "BrokerID", $result);
    getDelegatedObject($accessToken, $username, $result);

    $q = "SELECT name, latitude, longitude, visibility, organization, accesslink FROM contextbroker;";
    $r = mysqli_query($link, $q);

    if ($r) {
        $result['status'] = 'ok';
        $result['content'] = array();
        $result["log"] .= '\n\r action: get_all_contextbroker_latlong ok. ' . $q;

        while ($row = mysqli_fetch_assoc($r)) {
            $idTocheck = $row["organization"] . ":" . $row["name"];
            if (($role == 'RootAdmin') || ($role == 'ToolAdmin') ||
                    (
                    ($row["organization"] == $organization) &&
                    (
                    ($row["visibility"] == 'public' || (isset($result["delegation"][$idTocheck]) && $result["delegation"][$idTocheck]["kind"] == "anonymous"))
                    )
                    ) ||
                    (isset($result["delegation"][$idTocheck]) && $result["delegation"][$idTocheck]["kind"] != "anonymous") ||
                    (isset($result["keys"][$idTocheck]) && $result["keys"][$idTocheck]["owner"] == $username)
            )
                array_push($result['content'], $row);
        }
    } else {
        $result['status'] = 'ko';
        $result['msg'] = 'Error: errors in reading data about devices. <br/>' . mysqli_error($link);
        $result["error_msg"] .= "Errors in reading data about devices";
        $result['log'] = '\n\r Error: errors in reading data about devices. <br/>' . mysqli_error($link);
    }

    my_log($result);
    mysqli_close($link);
} else if ($action == "get_delegations") {
    $missingParams = missingParameters(array('name', 'object'));

    if (!empty($missingParams)) {
        $result["status"] = "ko";
        $result['msg'] = "Missing Parameters";
        $result["error_msg"] .= "Problem in get delegations (Missing parameters: " . implode(", ", $missingParams) . " )";
        $result["log"] = "action=get_delegations - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
    } else {
        $name = mysqli_real_escape_string($link, $_REQUEST['name']);
        $object = mysqli_real_escape_string($link, $_REQUEST['object']);

        //read broker info, it also enforce its existence
        if ($object == "BrokerID")
            $q = "SELECT organization FROM iotdb.contextbroker where name='$name'";
        else if ($object == "ModelID")
            $q = "SELECT organization FROM iotdb.model where name='$name'";
        $r = mysqli_query($link, $q);
        if ($r) {
            while ($row = mysqli_fetch_assoc($r)) {
                $obj_organization = $row["organization"];
            }

            if (!isset($obj_organization)) {
                $result["status"] = "ko";
                $result['msg'] = "Unrecognized context broker";
                $result["error_msg"] .= "Problem in get delegations (Unrecognized context broker)";
                $result["log"] = "action=get_delegations - error Unrecognized contect broker\r\n";
            } else {
                //id managermnt
                $eId = $obj_organization . ":" . $name;
                if (!enforcementRights($username, $accessToken, $role, $eId, $object, 'read', $result)) {
                    $result["status"] = "ko";
                    $result['msg'] = "Not ownership or enough right to update";
                    $result["error_msg"] .= "Problem in get delegations (Not ownership or enough right to get)";
                    $result["log"] .= "action=get_delegations - error Not ownership or enough right to update\r\n";
                } else {
                    getDelegatorObject($accessToken, $username, $result, $object, $eId);
                }
            }
        } else {
            $result["status"] = "ko";
            $result['msg'] = "Unrecognized context broker entry";
            $result["error_msg"] .= "Problem in get delegations  (Unrecognized context broker entry)";
            $result["log"] = "action=get_delegations - error Unrecognized contect broker entry\r\n";
        }
    }

    my_log($result);
    mysqli_close($link);
}
//TODO armonize with model
else if ($action == "add_delegation") {
    $missingParams = missingParameters(array('obj_name', 'object'));

    if (!empty($missingParams)) {
        $result["status"] = "ko";
        $result['msg'] = "Missing Parameters";
        $result["error_msg"] .= "Problem in add delegation (Missing parameters: " . implode(", ", $missingParams) . " )";
        $result["log"] = "action=add_delegation - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
    } else {
        $obj_name = mysqli_real_escape_string($link, $_REQUEST['obj_name']);
        $object = mysqli_real_escape_string($link, $_REQUEST['object']);
        $delegated_user = (isset($_REQUEST['delegated_user'])) ? mysqli_real_escape_string($link, $_REQUEST['delegated_user']) : "";
        $delegated_group = (isset($_REQUEST['delegated_group'])) ? mysqli_real_escape_string($link, $_REQUEST['delegated_group']) : "";

        //read broker info, it also enforce its existence
        if ($object == "BrokerID")
            $q = "SELECT organization FROM iotdb.contextbroker where name='$obj_name'";
        else if ($object == "ModelID")
            $q = "SELECT organization FROM iotdb.model where name='$obj_name'";
        $r = mysqli_query($link, $q);
        if ($r) {
            while ($row = mysqli_fetch_assoc($r)) {
                $obj_organization = $row["organization"];
            }

            if (!isset($obj_organization)) {
                $result["status"] = "ko";
                $result['msg'] = "Unrecognized context broker";
                $result["error_msg"] .= "Problem in add delegations (Unrecognized context broker)";
                $result["log"] = "action=add_delegations - error Unrecognized contect broker\r\n";
            } else {
                //id managermnt
                $eId = $obj_organization . ":" . $obj_name;
                if (!enforcementRights($username, $accessToken, $role, $eId, $object, 'write', $result)) {
                    $result["status"] = "ko";
                    $result['msg'] = "Not ownership or enough right to update";
                    $result["error_msg"] .= "Problem in add delegations (Not ownership or enough right to get)";
                    $result["log"] .= "action=add_delegations - error Not ownership or enough right to update\r\n";
                } else {
                    if (($delegated_user != "" || $delegated_group != "") && $username != "") {
                        delegateObject($eId, $username, $delegated_user, $delegated_group, $object, $accessToken, $result);
                    } else {
                        $result["status"] = 'ko';
                        $result["error_msg"] = 'The function delegate_object has been called without specifying mandatory parameters. ';
                        $result["msg"] = '\n the function delegate_object has been called without specifying mandatory parameters';
                        $result["log"] = '\n the function delegate_object has been called without specifying mandatory parameters';
                    }
                }
            }
        } else {
            $result["status"] = "ko";
            $result['msg'] = "Unrecognized context broker entry";
            $result["error_msg"] .= "Problem in add delegation (Unrecognized context broker entry)";
            $result["log"] = "action=add_delegations - error Unrecognized contect broker entry\r\n";
        }
    }

    my_log($result);
    mysqli_close($link);
} else if ($action == "remove_delegation") {
    $missingParams = missingParameters(array('delegationId'));

    if (!empty($missingParams)) {
        $result["status"] = "ko";
        $result['msg'] = "Missing Parameters";
        $result["error_msg"] .= "Problem in remove delegation (Missing parameters: " . implode(", ", $missingParams) . " )";
        $result["log"] = "action=remove_delegation - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
    } else {
        $delegationId = mysqli_real_escape_string($link, $_REQUEST['delegationId']); //TODO check delegationId enforce ownership

        removeDelegationValue($accessToken, $username, $delegationId, $result);
    }

    my_log($result);
    mysqli_close($link);
} else if ($action == "get_specific_contextbroker") {
    $missingParams = missingParameters(array('sub_ID'));

    if (!empty($missingParams)) {
        $result["status"] = "ko";
        $result['msg'] = "Missing Parameters";
        $result["error_msg"] .= "Problem in get info of the broker (Missing parameters: " . implode(", ", $missingParams) . " )";
        $result["log"] = "action=get_specific_contextbroker - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
    } else {
        $sub_ID = mysqli_real_escape_string($link, $_REQUEST['sub_ID']);
        get_specific_contextbroker($link, $accessToken, $sub_ID, $resourceLink, $result);
        my_log($result);
    }
}
//TODO armonize with model
else if ($action == 'change_visibility') {
    $missingParams = missingParameters(array('name', 'visibility', 'object'));

    if (!empty($missingParams)) {
        $result["status"] = "ko";
        $result['msg'] = "Missing Parameters";
        $result["error_msg"] .= "Problem in change visibility (Missing parameters: " . implode(", ", $missingParams) . " )";
        $result["log"] = "action=change_visibility - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
    } else {
        $name = mysqli_real_escape_string($link, $_REQUEST['name']);
        $visibility = mysqli_real_escape_string($link, $_REQUEST['visibility']);
        $object = mysqli_real_escape_string($link, $_REQUEST['object']);

        //read broker info, it also enforce its existence
        if ($object == "BrokerID")
            $q = "SELECT organization FROM iotdb.contextbroker where name='$name'";
        else if ($object == "ModelID")
            $q = "SELECT organization FROM iotdb.model where name='$name'";

        $r = mysqli_query($link, $q);
        if ($r) {
            while ($row = mysqli_fetch_assoc($r)) {
                $obj_organization = $row["organization"];
            }

            if (!isset($obj_organization)) {
                $result["status"] = "ko";
                $result['msg'] = "Unrecognized context broker";
                $result["error_msg"] .= "Problem in change visibility (Unrecognized context broker)";
                $result["log"] = "action=change visibility - error Unrecognized contect broker\r\n";
            } else {
                //id managermnt
                $eId = $obj_organization . ":" . $name;
                if (!enforcementRights($username, $accessToken, $role, $eId, $object, 'write', $result)) {
                    $result["status"] = "ko";
                    $result['msg'] = "Not ownership or enough right to update";
                    $result["error_msg"] .= "Problem in change visibility (Not ownership or enough right to get)";
                    $result["log"] .= "action=change visibility - error Not ownership or enough right to update\r\n";
                } else {
                    $q = "UPDATE contextbroker  SET  visibility = '$visibility' WHERE name='$name' and organization='$obj_organization'";
                    $r = mysqli_query($link, $q);

                    if ($r) {
                        logAction($link, $username, 'contextbroker', 'change_visibility', $name, $obj_organization, 'new visibility ' . $visibility, 'success');

                        $result["status"] = 'ok';
                        $result["msg"] .= "\n cb Visibility correctly updated";
                        $result["log"] .= "\n cb $name: Visibility correctly updated";
                        // information to be passed to the interface
                        $result["visibility"] = $visibility;
                        if ($visibility === 'public') {
                            delegateObject($eId, $username, "ANONYMOUS", "", $object, $accessToken, $result);
                        } else {
                            getDelegatorObject($accessToken, $username, $result, $object, $eId);
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
                                $result["status"] = "ok";
                                $result["msg"] = "The delegation to anonymous has been changed";
                                $result["log"] = "The delegation to anonymous has been changed";
                                removeDelegationValue($accessToken, $username, $delegationId, $result);
                            }
                        }
                    } else {
                        logAction($link, $username, 'contextbroker', 'change_visibility', $name, $obj_organization, 'new visibility ' . $visibility, 'faliure');
                        $result["status"] = 'ko';
                        $result["error_msg"] .= "Problem in changing the visibility of the contextbroker $name. ";
                        $result["msg"] .= "\n Problem in changing the visibility of the contextbroker $name: " . generateErrorMessage($link);
                        $result["log"] .= "\n Problem in changing the visibility of the contextbroker $name: " . generateErrorMessage($link);
                    }
                }
            }
        } else {
            $result["status"] = "ko";
            $result['msg'] = "Unrecognized context broker entry";
            $result["error_msg"] .= "Problem in change visibility (Unrecognized context broker entry)";
            $result["log"] = "action=change visibility - error Unrecognized contect broker entry\r\n";
        }
//		}
    }

    my_log($result);
    mysqli_close($link);
}
//TODO armonize with model
else if ($action == 'change_owner') {
    $missingParams = missingParameters(array('name', 'newOwner', 'object'));

    if (!empty($missingParams)) {
        $result["status"] = "ko";
        $result['msg'] = "Missing Parameters";
        $result["error_msg"] .= "Problem in change owner  (Missing parameters: " . implode(", ", $missingParams) . " )";
        $result["log"] = "action=change_owner - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
    } else {
        $name = mysqli_real_escape_string($link, $_REQUEST['name']);
        $newuser = mysqli_real_escape_string($link, $_REQUEST['newOwner']);
        $object = mysqli_real_escape_string($link, $_REQUEST['object']);

        //read broker info, it also enforce its existence
        if ($object == "BrokerID")
            $q = "SELECT organization, accesslink FROM iotdb.contextbroker where name='$name'";
        else if ($object == "ModelID")
            $q = "SELECT organization FROM iotdb.model where name='$name'";
        $r = mysqli_query($link, $q);
        if ($r) {
            while ($row = mysqli_fetch_assoc($r)) {
                $obj_organization = $row["organization"];
                if (isset($row["accesslink"]))
                    $accesslink = $row["accesslink"];
            }

            if (!isset($obj_organization)) {
                $result["status"] = "ko";
                $result['msg'] = "Unrecognized context broker";
                $result["error_msg"] .= "Problem in change owner (Unrecognized context broker)";
                $result["log"] = "action=change_owner - error Unrecognized contect broker\r\n";
            } else {
                //id managermnt
                $eId = $obj_organization . ":" . $name;
                if (!enforcementRights($username, $accessToken, $role, $eId, $object, 'write', $result)) {
                    $result["status"] = "ko";
                    $result['msg'] = "Not ownership or enough right to update";
                    $result["error_msg"] .= "Problem in change visibility (Not ownership or enough right to get)";
                    $result["log"] .= "action=change visibility - error Not ownership or enough right to update\r\n";
                } else {
                    //TODO for change ownership, a new certificate has to be created (if model is authenticated)
                    $ownmsg = array();
                    $ownmsg["elementId"] = $eId; // I am using the new identifier
                    $ownmsg["elementName"] = $eId;
                    if (isset($accesslink))
                        $ownmsg["elementUrl"] = $accesslink;
                    else
                        $ownmsg["elementUrl"] = $eId;
                    $ownmsg["elementType"] = $object;

                    $ownmsg["deleted"] = date("Y/m/d");
                    $ownmsg["username"] = $username; //TODO in case of RootAdmin this is not working
                    $ownmsg["elementDetails"] = array();
                    registerOwnerShipObject($ownmsg, $accessToken, $object, $result); //delete old ownership

                    unset($ownmsg["deleted"]);
                    $ownmsg["username"] = $newuser;
                    registerOwnerShipObject($ownmsg, $accessToken, $object, $result); //insert new ownership
                    $result["status"] = 'ok';

                    logAction($link, $username, 'contextbroker', 'change_owner', $name, $organization, 'new owner: ' . $newuser, 'success');
                }
            }
        } else {
            $result["status"] = "ko";
            $result['msg'] = "Unrecognized context broker entry";
            $result["error_msg"] .= "Problem in change owner (Unrecognized context broker entry)";
            $result["log"] = "action=change_owner - error Unrecognized contect broker entry\r\n";
        }
    }

    my_log($result);
    mysqli_close($link);
}
// Used for retrieve all the services, given a CB name
else if ($action == 'get_services_by_cb_name') {
    $missingParams = missingParameters(array('name'));

    if (!empty($missingParams)) {
        $result["status"] = "ko";
        $result['msg'] = "Missing Parameters";
        $result["error_msg"] .= "Problem in get service (Missing parameters: " . implode(", ", $missingParams) . " )";
        $result["log"] = "action=get_services_by_cb_name - error Missing Parameters: " . implode(", ", $missingParams) . " \r\n";
    } else {
        $brokerName = mysqli_real_escape_string($link, $_REQUEST['name']);

        //read broker info, it also enforce its existence
        $q = "SELECT organization FROM iotdb.contextbroker where name='$brokerName'";
        $r = mysqli_query($link, $q);
        if ($r) {
            while ($row = mysqli_fetch_assoc($r)) {
                $obj_organization = $row["organization"];
            }

            if (!isset($obj_organization)) {
                $result["status"] = "ko";
                $result['msg'] = "Unrecognized context broker";
                $result["error_msg"] .= "Problem in get services (Unrecognized context broker)";
                $result["log"] = "action=get_services_by_cb_name - error Unrecognized contect broker\r\n";
            } else {

                //id managermnt
                $eId = $obj_organization . ":" . $brokerName;
                if (!enforcementRights($username, $accessToken, $role, $eId, 'BrokerID', 'read', $result)) {
                    $result["status"] = "ko";
                    $result['msg'] = "Not ownership or enough right to update";
                    $result["error_msg"] .= "Problem in get services (Not ownership or enough right to get)";
                    $result["log"] .= "action=get_services_by_cb_name - error Not ownership or enough right to update\r\n";
                } else {
                    $services = array();
                    $queryString = "SELECT name FROM services WHERE broker_name = '$brokerName'";
                    // query execution
                    $res = mysqli_query($link, $queryString);

                    if ($res) {
                        // successful query
                        while ($row = mysqli_fetch_assoc($res)) {
                            array_push($services, $row);
                        }
                        $result["status"] = "ok";
                        $result["content"] = $services;
                    } else {
                        // unsuccessful query
                        $result["status"] = "ko";
                    }
                }
            }
        } else {
            $result["status"] = "ko";
            $result['msg'] = "Unrecognized context broker entry";
            $result["error_msg"] .= "Problem in get services (Unrecognized context broker entry)";
            $result["log"] = "action=get_services_by_cb_name - error Unrecognized contect broker entry\r\n";
        }
    }

    my_log($result);
    mysqli_close($link);
} else {
    $result['status'] = 'ko';
    $result['msg'] = 'invalid action ' . $action;
    $result['log'] = 'invalid action ' . $action;
    my_log($result);
    mysqli_close($link);
}
