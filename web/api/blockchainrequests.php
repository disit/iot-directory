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
    $result['log'] = 'blockchainrequests.php action not present';
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
        error_log("---- blockchainrequests.php:" . (microtime(true) - $mctime));
        $accessToken = $tkn->access_token;
    }
}
if ($action != 'get_param_values') {
    if (empty($accessToken)) {
        $result["status"] = "ko";
        $result['msg'] = "Access Token not present";
        $result["error_msg"] .= "Access Token not present";
        $result["log"] = "blockchainrequests.php AccessToken not present\r\n";
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

function create_datatable_data_bc($link, $request, $query, $where) {
    $check_blanket = false;
    $columns = $request["columns"];
    if (isset($request["searchPanes"]["mode"])) {
        $query .= ' WHERE (mode  =' . $request["searchPanes"]["mode"][0] . ')';
    }

    if (isset($request["searchPanes"]["contextbroker"])) {
        $query .= ' WHERE (contextBroker  ="' . $request["searchPanes"]["contextbroker"][0] . '")';
    }



    if (isset($request["search"]["value"]) && $request["search"]["value"] != '') {

        if (strpos($query, 'WHERE') == false) {
            $query .= ' WHERE ';
        } else {
            $query .= ' AND (';
            $check_blanket = true;
        }

        if ($where != "")
            $query .= $where . ' AND (';

        $query .= "device_id "  . ' LIKE "%' . $request["search"]["value"] . '%"';
//
//        foreach ($columns as $col) {
//            if (!in_array($col["device_id"], $request["no_columns"]))
//                $query .= " " . $col["device_id"] . ' LIKE "%' . $request["search"]["value"] . '%"  OR';
//        }

        //$query = substr($query, 0, -1);
        //$query = substr($query, 0, -1);
        if ($where != "")
            $query .= ') ';

        if ($check_blanket == true) {
            $query .= ') ';
        }
    }

    if (isset($request["order"])) {
        $query .= ' ORDER BY ' . $columns[$request['order']['0']['column']]['name'] . ' ' . $request['order']['0']['dir'] . '	';
    }

    $result = mysqli_query($link, $query);
    $GLOBALS['DataTableQuery'] = $query;
    return $result;
}

 if ($action == "get_all_device") {



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





    $ownDevices = getOwnerShipDevice($accessToken, $result);
    getDelegatedDevice($accessToken, $username, $result);

    //$q = "SELECT * FROM `Blockchain_verification_requests`";

  $q= "SELECT * FROM Blockchain_verification_requests";


    $r = create_datatable_data_bc($link, $_REQUEST, $q,"");



//     error_reporting(E_ALL);
//     ini_set('display_errors', 1);




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
//            //$eid = $row["organization"] . ":" . $row["contextBroker"] . ":" . $row["id"];
//
//            //$SelPub = ($row["organization"] == $organization) && ($row["visibility"] == 'public' || ( isset($result["delegation"][$eid]) && $result["delegation"][$eid]["kind"] == "anonymous") );
//            $SelDel = (isset($result["delegation"][$eid]) && ($result["delegation"][$eid]["kind"] != "anonymous") && $row["visibility"] != "public");
//
//            //$SelOwn = (isset($result["keys"][$eid]) && $result["keys"][$eid]["owner"] == $username );
//
//            if (!$subset) {
//                $COND = $SelPub || $SelDel || $SelOwn || $role === 'RootAdmin';
//            } else {
//                if (isset($_REQUEST['delegated'])) {
//                    $COND = $SelDel;
//                } else if (isset($_REQUEST['public'])) {
//                    $COND = $SelPub;
//                } else if (isset($_REQUEST['own'])) {
//                    $COND = $SelOwn;
//                }
//            }

            //if ($COND) {}
                $selectedrows++;
                if (!$tobelimited || ($tobelimited && $selectedrows >= $start && $selectedrows < ($start + $offset))) {
                    $rec = array();
                    $rec["device_id"] = $row["device_id"];
                    $rec["start_date"] = $row["start_date"];
                    $rec["end_date"] = $row["end_date"];
                    $rec["owner_id"] = $row["owner_id"];
                    $rec["request_status"] = $row["request_status"];
                    $rec["check_performed"] = $row["check_performed"];
                    $rec["missing_data"] = $row["missing_data"];





//                    if (((isset($result["keys"][$eid])) && ($role !== 'RootAdmin')) ||
//                            ((isset($result["keys"][$eid])) && ($result["keys"][$eid]["owner"] == $username) && ($role === 'RootAdmin'))
//                    ) {
//                        //it's mine or RootAdmin
//                        if ($row["visibility"] == "public") {
//                            $rec["visibility"] = "MyOwnPublic";
//                        } else {
//                            if (isset($result["delegation"][$row["uri"]]) && $result["delegation"][$row["uri"]]["kind"] == "anonymous")
//                                $rec["visibility"] = "MyOwnPublic";
//                            else
//                                $rec["visibility"] = "MyOwnPrivate";
//                        }
//
//
//                    } else {
//                        //it's not mine
//                        if (isset($result["delegation"][$eid]) && ($result["delegation"][$eid]["kind"] == "anonymous")) {
//                            //it's delegated as public
//                            $rec["visibility"] = 'public';
//                            $rec["k1"] = "";
//                            $rec["k2"] = "";
//                        } else if (isset($result["delegation"][$eid]) && ($result["delegation"][$eid]["kind"] != "anonymous") && $row["visibility"] != "public") {
//                            //it's delegated personally
//                            $rec["visibility"] = 'delegated';
//                            $rec["k1"] = "";
//                            $rec["k2"] = "";
//                            if (isset($result["delegation"][$eid]["k1"])) {
//                                $rec["k1"] = $result["delegation"][$eid]["k1"]; // to be fixed
//                                $rec["k2"] = $result["delegation"][$eid]["k2"]; // to be fixed
//                            }
//                        } else {
//                            $rec["visibility"] = $row["visibility"];
//                            $rec["k1"] = "";
//                            $rec["k2"] = "";
//                        }
//                    }


                    array_push($data, $rec);
                }


        }

        $output = format_result($draw, $selectedrows + 1, $selectedrows + 1, $data, "", "\r\n action=get_all_device \r\n", 'ok'); //ok
        $output['cache']=$result['cache'];
        //$output = format_result($draw, $selectedrows + 1, $selectedrows + 1, $data, "", "\r\n action=get_all_device \r\n", $p);
        logAction($link, $username, 'device', 'get_all_device', '', $organization, '', 'success');
    } else {
        logAction($link, $username, 'device', 'get_all_device', '', $organization, 'Error: errors in reading data about devices.', 'failure');//failure
        //$output = format_result($_REQUEST["draw"], 0, 0, $data, 'Error: errors in reading data about devices. <br/>' . generateErrorMessage($link), '\n\r Error: errors in reading data about devices.' . generateErrorMessage($link), 'ko');
    }
    my_log($output);
    mysqli_close($link);
}

else if($action == "get_single_device"){
    if (isset($_REQUEST['singleDeviceId']))
        $singleDeviceId = mysqli_real_escape_string($link, $_REQUEST['singleDeviceId']);
    $q = "SELECT d.`contextBroker`, d.`id`, d.`uri`, d.`devicetype`, d.`kind`,
				d.`macaddress`, d.`model`, d.`producer`, d.`longitude`, d.`latitude`, d.`protocol`, d.`format`, d.`visibility`, 
				d.`frequency`, d.`created`, d.`privatekey`, d.`certificate`,d.`organization`, cb.`accesslink`, cb.`accessport`, cb.`version`,
				cb.`sha`, d.`subnature`, d.`static_attributes`,d.`service`, d.`servicePath` FROM `devices` d JOIN `contextbroker` cb ON (d.contextBroker=cb.name)";
    $provaquery = mysqli_query($link,$q);


    $result["msg"]=$link;
my_log($result);
}

else if($action == "insert_new_device"){

    if (isset($_REQUEST['deviceId']) && isset($_REQUEST['startDate']) && isset($_REQUEST['endDate'])){
        $deviceId = $_REQUEST['deviceId'];
        $startDate = "{$_REQUEST['startDate']}T00:00:00" ;
        $endDate = "{$_REQUEST['endDate']}T00:00:00";

        try{
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            //$q1 = $link->prepare("SELECT * FROM iotdb.Blockchain_verification_requests WHERE device_id ='prova7'");
            //$q->bind_param("s", $deviceId);
            $q1 = mysqli_query($link,"SELECT * FROM iotdb.Blockchain_verification_requests WHERE device_id ='$deviceId' AND start_date='$startDate' AND end_date='$endDate'");
            $qresult = mysqli_num_rows($q1);
            //$qresult = $q->get_result();
            //$qresult = $qresult->fetch_assoc();


        } catch(Exception $e) {
            $result['status'] = 'ko';
            $result['msg'] = 'error inserting into the database';
            $result['error_msg'] = $q1->$e;
            $result['log'] ='error inserting into the database';
            my_log($result);
            mysqli_close($link);
        }
        // Check if there are any results
        if ($qresult == 0) {

            if (isDateFormatValid($startDate) && isDateFormatValid($endDate)) {
                $deviceId = mysqli_real_escape_string($link, $deviceId);
                $startDate = mysqli_real_escape_string($link, $startDate);
                $endDate = mysqli_real_escape_string($link, $endDate);
                $requestStatus = "pending";
                $ownerId = "tochange";

                //TODO:da rivedere questo errore
                try {
                    $q = $link->prepare("INSERT INTO iotdb.Blockchain_verification_requests (device_id, start_date, end_date, request_status, owner_id) VALUES (?, ?, ?, ?, ?)");
                    $q->bind_param("sssss", $deviceId, $startDate, $endDate, $requestStatus, $ownerId);
                    $q->execute();

                } catch (Exception $e) {
                    $result['status'] = 'ko';
                    $result['msg'] = 'error inserting into the database';
                    $result['error_msg'] = $q->$e;
                    $result['log'] = 'error inserting into the database';
                    my_log($result);
                    mysqli_close($link);
                }
                $result['status'] = 'ok';
                $result['msg'] = 'certification request inserted succefully';
                $result['error_msg'] = 'certification request inserted succefully';
                $result['log'] = 'certification request inserted succefully';

            } else {
                $result['status'] = 'ko';
                $result['msg'] = 'date are not in the correct format';
                $result['error_msg'] = 'date are not in the correct format';
                $result['log'] = 'date are not in the correct format';
            }
        }else{
            $result['status'] = 'ko';
            $result['msg'] = 'A verification request with this timeframe already exists for device: '.$deviceId;
            $result['error_msg'] = 'A verification request with this timeframe already exists for device: '.$deviceId;
            $result['log'] = 'A verification request with this timeframe already exists for device: '.$deviceId;
        }
    }else{
        $result['status'] = 'ko';
        $result['msg'] = 'Missing data to complete request';
        $result['error_msg'] = 'Missing data to complete request';
        $result['log'] = 'Missing data to complete request';
    }
    my_log($result);
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
		      d.`frequency`, d.`created`, d.`privatekey`, d.`certificate`, cb.`accesslink`,  cb.`accessport`,cb.`sha`, d.`subnature`, d.`static_attributes`, d.`service`, d.`servicePath` 
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
} else if ($action == 'download_report') {

    $deviceId=mysqli_real_escape_string($link, $_REQUEST["deviceId"]);
    $startDate=mysqli_real_escape_string($link, $_REQUEST["startDate"]);
    $endDate=mysqli_real_escape_string($link, $_REQUEST["endDate"]);


    $q = "SELECT report,report_missing,request_status FROM iotdb.Blockchain_verification_requests WHERE device_id='$deviceId' AND (request_status = 'completed' OR request_status='error') AND start_date='$startDate' AND end_date='$endDate' ;";


    $r = mysqli_query($link, $q);

    if($r){
        $result['status'] = 'ok';
        $result['msg']= 'download_report ready';
        $result['content'] = $r -> fetch_array(MYSQLI_ASSOC);
        $result['log']= $q;
    }else {
        $result['status'] = 'ko';
        $result['msg'] = 'download_report failed';
        $result['content'] = "error during query for report";
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

                        //delegate any values of this device
                        while ($value = mysqli_fetch_assoc($values)) {
                            delegateDeviceValue($uri . "/" . $value["value_name"], $cb, $value["value_name"], $username, $delegated_user, $delegated_group, $accessToken, $k1, $k2, $result, $kind);
                        }

                        //delegate the device
                        delegateDeviceValue($eId, $cb, NULL, $username, $delegated_user, $delegated_group, $accessToken, $k1, $k2, $result, $kind);
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
} else {
    $result['status'] = 'ko';
    $result['msg'] = 'invalid action ' . $action;
    $result['log'] = 'invalid action ' . $action;
    my_log($result);
    mysqli_close($link);
}

function compare_values($obj_a, $obj_b) {
    return strcasecmp($obj_a->value_name, $obj_b->value_name);
}

function isDateFormatValid($dateString, $format = 'Y-m-d\TH:i:s') {
    $dateTime = DateTime::createFromFormat($format, $dateString);

    return $dateTime && $dateTime->format($format) === $dateString;
}


function deviceBcCertification($name,$type,$frequency,$kind,$protocol,$format,$producer,$subnature,$staticAttributes,$service,$servicePath,$listAttributes,$organization,$accessToken,$contextbroker){
        
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
        $urlBc='http://192.168.1.139:9999/api/adddevice/';
        curl_setopt($ch, CURLOPT_URL, $urlBc);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $bcmessage);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response_bc = curl_exec($ch);
        
        
        if(curl_errno($ch)){
            echo 'Request Error:' . curl_error($ch);
        }
        
        $response_bc = json_decode($response_bc);
        $result = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch); 
        return $result;

}