<?php

function MAke_PROP($TOTatr, $link) {
    $attributes = array();
    foreach ($TOTatr as $key => $val) {       

        if ($key != 'type') {
            $SingleAttr = json_encode($val);
            if ($SingleAttr["checked"] != 'True') {

                $q = ' SELECT data_type, value_type, value_unit FROM iotdb.event_values where value_name = "' . $key . '" ';
                $r = mysqli_query($link, $q);
                
                
                if (!$r) {
                    $result['status'] = 'ko';
                    $result['msg'] = 'Error: errors in reading data about proposal value of model\'s attributes. <br/>' . generateErrorMessage($link);
                    $result["log"] = "action=$action of model " . $id . " error " . mysqli_error($link) . "\r\n";
                } else {
                    $SecondRow = mysqli_fetch_assoc($r);                    
                    while (mysqli_fetch_assoc($r)) {
                        $rec = array();
                        $rec["value_name"] = $key;
                        $rec["value_type"] = $SecondRow["value_type"];
                        $rec["data_type"] = $SecondRow["data_type"];
                        $rec["value_unit"] = $SecondRow["value_unit"];
                        array_push($attributes, $rec);
                    }
                }
            }
        }
    }
    return $attributes;
}

function checkRegisterOwnerShipObject($token, $object, &$result) {
    try {
        $url = $GLOBALS["ownershipURI"] . "ownership-api/v1/limits/?type=" . $object . "&accessToken=" . $token;
        $options = array(
            'http' => array(
                'header' => "Content-Type: application/json;charset=utf-8",
                'header' => "Access-Control-Allow-Origin: *",
                'method' => 'GET',
                'ignore_errors' => true,
                'timeout' => 30
            )
        );
        $context = stream_context_create($options);
        $local_result = @file_get_contents($url, false, $context);
        if (strpos($http_response_header[0], '200') !== false) {
            if ((json_decode($local_result)->limits[0]->current) < (json_decode($local_result)->limits[0]->limit)) {
                $result["status"] = 'ok';
                $result["msg"] .= "\n registration is possible";
                $result["log"] .= "\n registration is possible";
            } else {
                $result["status"] = 'ko';
                $result["error_msg"] .= "The registration is NOT possible. Reached limit of " . $object;
                $result["msg"] .= "\n The registration is NOT possible. Reached limit of " . $object . " (" . json_decode($local_result)->limits[0]->limit . ")";
                $result["log"] .= "\n The registration is NOT possible. Reached limit of " . $object . " (" . json_decode($local_result)->limits[0]->limit . ")";
            }
        } else {
            $result["status"] = 'ko';
            $result["error_msg"] .= "Error returned in checking the ownership. ";
            $result["msg"] .= "\n Error returned in checkRegisterOwnership" . $local_result;
            $result["log"] .= "\n Error returner in checkRegisterOwnership" . $local_result;
        }
    } catch (Exception $ex) {
        $result["status"] = 'ko';
        $result["error_msg"] .= 'General error in checking the ownership. ';
        $result["msg"] .= '\n general error in checkRegisterOwnership';
        $result["log"] .= '\n general error in checkRegisterOwnership' . $ex;
    }
}

function insert_device($link, $id, $devicetype, $contextbroker, $kind, $protocol, $format, $macaddress, $model,
        $producer, $latitude, $longitude, $visibility, $frequency, $k1, $k2, $edgegateway_type, $edgegateway_uri,
        $listAttributes, $subnature, $staticAttributes, $pathCertificate, $accessToken, &$result, $shouldbeRegistered = 'yes',
        $organization, $kbUrl = "", $username = "", $service = "", $servicePath = "",$wktGeometry="",$hlt) {
    if (($k1 == null) || ($k1 == ""))
        $k1 = guidv4();
    if (($k2 == null) || ($k2 == ""))
        $k2 = guidv4();
if(canBeRegistered($id, $devicetype, $contextbroker, $kind, $protocol, $format, $macaddress, $model, $producer, $latitude, $longitude,
        $visibility, $frequency, $listAttributes, $subnature, $staticAttributes, $result)){
    checkRegisterOwnerShipObject($accessToken, 'IOTID', $result);
    if ($result["status"] == 'ok') {
        $selectDevicesDeleted = "SELECT contextBroker, id
		FROM deleted_devices WHERE contextBroker = '$contextbroker'
		AND id = '$id';";
        $s3 = mysqli_query($link, $selectDevicesDeleted);
        if ($s3) {
            if (mysqli_num_rows($s3) == 0) {

                $isRegistered = registerCertificatePrivateKey($link, $contextbroker, $id, $model, $pathCertificate, $result, $username);
                if ($result["status"] == 'ok') {
                    if ($isRegistered) {
                        $privatekey = $id . "-key.pem";
                        $certificate = $id . "-crt.pem";
                        $publickey = $id . "-pubkey.pem";
                    } else {
                        $privatekey = "";
                        $certificate = "";
                        $publickey = "";
                    }
                } else {
                    //the registration failed, return error
                    return $result;
                }


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
                        $result["status"] = 'ko';
                    $result["error_msg"] .= "An error occurred while validating the static attributes: " . ($sace->getMessage());
                    $result["msg"] .= "\n An error occurred while validating the static attributes: " . ($sace->getMessage());
                    $result["log"] .= "\r\n An error occurred while validating the static attributes: " . ($sace->getMessage());
                        //query di errore qui non ha senso perchè è un controllo PRIMA di scrivere il device nel db, se fallisce questo non scrivo nulla
                }
                if ($result["status"] == 'ko')
                    return $result;
                    //query di errore qui non ha senso perchè è un controllo PRIMA di scrivere il device nel db, se fallisce questo non scrivo nulla

                $syntaxRes = 0;
                if ($protocol == 'ngsi w/MultiService') {
                    if (strlen($servicePath) > 0 && $servicePath{0} != "/") {//TODO: do we need to insert this if in any other place?
                        $servicePath = "/" + $servicePath;
                    }
                    $id = $service . "." . $servicePath . "." . $id;
                    $syntaxRes = servicePathSyntaxCheck($servicePath);
                    $service = "'$service'";
                    $servicePath = "'$servicePath'";
                } else {
                    $service = "NULL";
                    $servicePath = "NULL";
                }
                if($wktGeometry==""){
                    $wktGeometry= null;
                }
                if ($syntaxRes == 0) {

                    if ($result["status"] == 'ok' && $result["content"] == null) {
                        $q = "INSERT INTO devices(id, devicetype, contextBroker,  kind, protocol, format, macaddress, model, producer, latitude, longitude, visibility, frequency, privatekey, certificate, organization, subnature, static_attributes, service, servicePath, wktGeometry, hlt) " .
                                "VALUES('$id', '$devicetype', '$contextbroker', '$kind', '$protocol', '$format', '$macaddress', '$model', '$producer', '$latitude', '$longitude', '$visibility', '$frequency', '$privatekey','$certificate', '$organization', '$subnature', '$staticAttributes', $service, $servicePath,CASE WHEN '$wktGeometry' = '' THEN NULL ELSE '$wktGeometry' END, '$hlt')";
                    } else {
                        $q = "INSERT INTO devices(id, devicetype, contextBroker,  kind, protocol, format, macaddress, model, producer, latitude, longitude,uri, visibility,  frequency, privatekey, certificate, mandatoryproperties,mandatoryvalues, organization, subnature, static_attributes, service, servicePath,wktGeometry,hlt) " .
                                "VALUES('$id', '$devicetype', '$contextbroker', '$kind', '$protocol', '$format', '$macaddress', '$model', '$producer', '$latitude', '$longitude', '" . $result["content"] . "', '$visibility', '$frequency', '$privatekey','$certificate',1,1, '$organization', '$subnature', '$staticAttributes', $service, $servicePath,CASE WHEN '$wktGeometry' = '' THEN NULL ELSE '$wktGeometry' END, '$hlt')";
                    }

                    $r = mysqli_query($link, $q);
                    if ($r) {
                            $result["msg"] .= "\n Device $contextbroker/$id correctly inserted in db";
                            $result["log"] .= "\r\n Device $contextbroker/$id correctly inserted in db \r\n";

                            $q = "UPDATE devices SET is_in_db = 'success' WHERE id = '$id'";
                            mysqli_query($link, $q);
                            // inserimento ha avuto successo, scrivo nel db che l'inserimento è stato effettuato correttamente

                        // information to be passed to the interface
                        $result["visibility"] = $visibility;
                        if ($result["content"] == null)
                            $result["active"] = false;
                        else
                            $result["active"] = true;
                        // end of information to be passed to the interface
                        // $result["msg"] .= "prima della richiesta di update k1 e k2 ";
                        // $result["log"] .= "prima della richiesta di update k1 e k2";

                        if ($accessToken != "") {
                            $ownmsg = array();
                            $eId = $organization . ":" . $contextbroker . ":" . $id;
                            $ownmsg["elementId"] = $eId;
                            $ownmsg["elementName"] = $id;
                            $ownmsg["elementUrl"] = $result["content"];
                            $ownmsg["elementDetails"] = array();
                            $ownmsg["elementDetails"]["k1"] = $k1;
                            $ownmsg["elementDetails"]["k2"] = $k2;
                            if ($publickey != "") {
                                $pub_key_str = str_replace("\n", "", file_get_contents($pathCertificate . "/public/" . $publickey));
                                $ownmsg["elementDetails"]["publickey"] = substr($pub_key_str, 26, 216);
                            }
                            if ($edgegateway_type != "")
                                $ownmsg["elementDetails"]["edgegateway_type"] = $edgegateway_type;
                            if ($edgegateway_uri != "")
                                $ownmsg["elementDetails"]["edgegateway_uri"] = $edgegateway_uri;
                            $ownmsg["elementDetails"]["contextbroker"] = $contextbroker;
                            // $result["msg"] .= json_encode($ownmsg);
                            // $result["log"] .= json_encode($ownmsg);

                                //check if a device is certified
                                $param = str_replace( array( '\\', '"' , '[',']' ), '', $staticAttributes);
                                $param=explode(",",$param);
                                foreach ($param as $key => $value){
                                    if($value =="http://www.disit.org/km4city/schema#isCertified"){
                                        $ownmsg["elementDetails"]["Certified"]= "true";
                                    }
                                }

                            registerOwnerShipDevice($eId, $ownmsg, $accessToken, $result);
                        }
                        // $result["msg"] .= "passata richiesta di update k1 e k2 ";
                        // $result["log"] .= "passata richiesta di update k1 e k2 ";
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

                            $insertquery = "INSERT INTO `event_values`(`cb`, `device`, `value_name`, `data_type`, `order`, `value_type`, `editable`,`value_unit`,`healthiness_criteria`,`$hc`,`real_time_flag`) VALUES ('$contextbroker','$id','$att->value_name','$att->data_type','$b','$att->value_type','$att->editable','$att->value_unit','$att->healthiness_criteria','$att->healthiness_value','$att->real_time_flag');";

                            try {
                                $r1 = mysqli_query($link, $insertquery);
                                if ($r1) {
                                    $result["msg"] .= "\n attribute $att->value_name correctly inserted";
                                    $result["log"] .= "\n attribute $att->value_name correctly inserted";
                                } else {
                                    $result["error_msg"] .= "Attribute $att->value_name was not inserted. ";
                                    $result["msg"] .= "<br/> attribute $att->value_name was not inserted <br/>" . generateErrorMessage($link);
                                    $result["log"] .= "\r\n attribute $att->value_name was not inserted $insertquery " . generateErrorMessage($link);
                                    $ok = false;
                                }
                            } catch (Exception $eeee) {
                                    $result["error_msg"] .= "Attribute $att->value_name was not inserted because of an exception. ";
                                    $result["msg"] .= "<br/> attribute $att->value_name was not inserted because of an exception. <br/>" . generateErrorMessage($link);
                                    $result["log"] .= "\r\n attribute $att->value_name was not inserted because of an exception: $insertquery " . generateErrorMessage($link);
                                $ok = false;
                            }
                            $b++;
                            $a++;
                        }
                        if ($ok == true) {
                            $result["status"] = 'ok';
                        } else {
                            $result["status"] = 'ko';
                                $q= "select id from devices where id ='$id';";
                                $r = mysqli_query($link, $q);
                                if(mysqli_num_rows($r) == 0){
                                    $q = "UPDATE devices SET is_in_db = 'event_values_error' WHERE id = '$id';";
                                    mysqli_query($link, $q);
                                }
                        }
                    } else {
                        $result["status"] = 'ko';
                        $result["error_msg"] .= "Problem in inserting the device $id in the database. ";
                            $result["msg"] .= "\n Problem in inserting the device $id  in the database. :  <br/>" . generateErrorMessage($link);
                            $result["log"] .= "\r\n Problem in inserting the device $id  in the database. :  $q  " . generateErrorMessage($link);
                            //se il database non mi risponde è inutile che provi a scrivere l'errore del device perchè non ho il device nel db
                    }
                } else {
                    $result["status"] = 'ko';
                    $result["error_msg"] = $servicePath . " is NOT a valid servicePath";
                    }
                    if (!isset($shouldbeRegistered)) {
                        registerKB($link, $id, $devicetype, $contextbroker, $kind, $protocol,
                                $format, $macaddress, $model, $producer, $latitude, $longitude, $visibility,
                                $frequency, $listAttributes, $subnature, $staticAttributes, $result, 'yes', $organization, $kbUrl, $service, $servicePath, $accessToken,$wktGeometry,$hlt);
                    } else {
                        registerKB($link, $id, $devicetype, $contextbroker, $kind, $protocol,
                                $format, $macaddress, $model, $producer, $latitude, $longitude, $visibility,
                                $frequency, $listAttributes, $subnature, $staticAttributes, $result, $shouldbeRegistered, $organization, $kbUrl, $service, $servicePath, $accessToken,$wktGeometry,$hlt);
                    }

                    if ($result["status"] == 'ko')
                        return $result;

                    $q = "UPDATE devices SET uri = '" . $result['content'] . "' WHERE id = '$id'";
                    $r = mysqli_query($link, $q);
                    if(!$r) {
                        $result["status"] = 'ko';
                        $result["error_msg"] .= "Uri generated but not inserted in the database. ";
                        $result["msg"] .= "\n Uri generated but not inserted in the database:  <br/>" . generateErrorMessage($link);
                        $result["log"] .= "\r\n Uri generated but not inserted in the database:  $q  " . generateErrorMessage($link);
                        $q = "UPDATE devices SET is_in_db = 'uri_generated_but_not_inserted' WHERE id = '$id'";
                        mysqli_query($link, $q);
                    }else{
                        $result["status"] = 'ok';
                        $result["error_msg"] .= "Inserted the device uri in the database. ";
                        $result["msg"] .= "\n Inserted the device uri in the database  <br/>";
                        $result["log"] .= "\r\n Inserted the device uri in the database.  $q  ";

                }
            } else {
                $result["status"] = 'ko';
                $result["error_msg"] .= "Problem in inserting the device $id, device name already exists in deleted devices. ";
                $result["msg"] .= "\n Problem in inserting the device $id, device name already exists in deleted devices";
                $result["log"] .= "\r\n Problem in inserting the device $id, device name already exists in deleted devices";
                    //non ha senso la query perchè se entro in questo else vuol dire che un device con lo stesso nome è presente nei deleted device,
                    //di conseguenza non scrive nulla nella tabella devices
            }
        } else {
            $result["status"] = 'ko';
                $result["error_msg"] .= "Problem in inserting the device $id, cannot communicate with the db. ";
                $result["msg"] .= "\n Problem in inserting the device $id, cannot communicate with the db:  <br/>" . generateErrorMessage($link);
                $result["log"] .= "\r\n Problem in inserting the device $id, cannot communicate with the db:  " . generateErrorMessage($link);
                //scrittura del codice d'errore inutile perchè se fallisce questo ciclo vuol dire che il db non ha risposto
                //alla prima query, per cui nessuna scrittura di device è stata effettuata
        }
    } else {
        // limits reached, error message are already thrown  
    }
    } else {
        $result["error_msg"] .= "Missing some device data ";
        $result["msg"] .= "\n Missing some device data, device not saved";
        $result["log"] .= "\n Missing some device data, please check the input fields";
        $result["status"] = 'ko';
        //scrittura nel db inutile perchè se "CanBeRegistered()" ritorna falso nessuna scrittura sul db è effettuata
    }
}

/* function for loging */

function format_result($draw, $number_all_row, $number_filter_row, $data, $msg, $log, $status) {
    $output = array(
        "draw" => intval($draw),
        "recordsTotal" => $number_all_row,
        "recordsFiltered" => $number_filter_row,
        "data" => $data,
        "msg" => $msg,
        "log" => $log,
        "status" => $status
    );

    return $output;
}

function get_searchPanes_CB($data, $param, $total) {
    if ($param == 'contextbroker') {
        $searchPanes = array("options" => array("contextbroker" => array()));
        $cb = array();

        foreach ($data as $value) {
            array_push($cb, $value["contextbroker"]);
        }
        $occur = array_count_values($cb);
        $occur_tot = array_count_values($total);
        // echo json_decode($occur_tot);

        foreach (array_keys($occur) as $value) {

            array_push($searchPanes["options"]["contextbroker"], array("label" => $value, "total" => $occur_tot[$value], "value" => $value, "count" => $occur[$value]));
        }
    } else if ($param == 'rule') {
        $searchPanes = array("options" => array("mode" => array()));
        $rul = array();

        foreach ($data as $value) {
            array_push($rul, $value["mode"]);
        }
        $occur = array_count_values($rul);

        foreach (array_keys($occur) as $value) {
            if ($value == 'not active') {
                array_push($searchPanes["options"]["mode"], array("label" => 'not active', "total" => $total['not active'], "value" => '0', "count" => $occur[$value]));
            } else {
                array_push($searchPanes["options"]["mode"], array("label" => 'active', "total" => $total['active'], "value" => '1', "count" => $occur[$value]));
            }
        }
    }


    return $searchPanes;
}

function format_result_serverside($draw, $number_all_row, $number_filter_row, $data, $msg, $log, $status, $option, $searchPanes) {
    $output = array(
        "draw" => intval($draw),
        "recordsTotal" => $number_all_row,
        "recordsFiltered" => $number_filter_row,
        "data" => ($data),
        "msg" => $msg,
        "log" => $log,
        "status" => $status,
        "option" => $option,
        "searchPanes" => $searchPanes,
        "files" => []
    );
    return $output;
}

function create_datatable_data($link, $request, $query, $where) {
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

        foreach ($columns as $col) {
            if (!in_array($col["name"], $request["no_columns"]))
                $query .= " " . $col["name"] . ' LIKE "%' . $request["search"]["value"] . '%"  OR';
        }

        $query = substr($query, 0, -1);
        $query = substr($query, 0, -1);
        if ($where != "")
            $query .= ') ';

        if ($check_blanket == true) {
            $query .= ') ';
        }
    }

    if (isset($request["order"])) {
        $orderColumn=$columns[$request['order']['0']['column']]['name'];
        if($orderColumn=="retry"){
            //echo $query;
            $query .= ' ORDER BY (is_in_kb = FALSE OR is_in_db = FALSE OR is_in_broker = FALSE)  ' . $request['order']['0']['dir'] . '	';
        }else {

        $query .= ' ORDER BY ' . $columns[$request['order']['0']['column']]['name'] . ' ' . $request['order']['0']['dir'] . '	';
    }

    // echo $query;
    }


    $result = mysqli_query($link, $query);
    $GLOBALS['DataTableQuery'] = $query;
    return $result;
}

function my_log($result) {
    simple_log($result);
    echo json_encode($result);
}

function simple_log($result) {
    //TODO rotate
    $fp = fopen($GLOBALS["pathLog"], "a");
    if (!$fp) {
        //TODO create
        $result["status"] = 'ko';
        $result["error_msg"] = "\n Unable to open LOG file. Please contact an administrator";
    } else {
        flock($fp, LOCK_EX);
        $output = date("Y-m-d h:i:sa") . ": " . $result["log"] . "----" .$result["error_msg"]. "\r\n" ;
        fwrite($fp, $output);
        unset($result["log"]);
        flock($fp, LOCK_UN);
        fclose($fp);
    }
}

//this routine return two information:
//1 or 0 that means if a certificate has been created (information to fill the db)
//status == ok or ko that means an error is reached (error to be notified to gui)
function registerCertificatePrivateKey($link, $cb, $deviceId, $model, $path, &$result, $username = "") {
    $result["status"] = 'ok';

    if ($username == "") {
        $username = $_SESSION['loggedUsername'];
    }

    $q = "select name from model where kgenerator = 'authenticated' and name ='$model';";
    $res = mysqli_query($link, $q);
    if ($res) {

        $row = mysqli_fetch_assoc($res);
        if ($row["name"] == $model) {
            $result["msg"] .= "\n the model $model is of type authenticated (registerCertificatePrivateKey() )";
            $result["log"] .= "\n the model $model is of type authenticated. (registerCertificatePrivateKey() )";

            $cmd1 = "$path/generate-device-keys.sh $cb/$deviceId?" . $username . " $deviceId $path 2>&1";
            $output = shell_exec($cmd1);

            $result["msg"] .= "\n result of command " . $cmd1 . " is " . $output;
            $result["log"] .= "\n result of  command " . $cmd1 . " is " . $output;

            //check error string
            foreach (preg_split("/((\r?\n)|(\r\n?))/", $output) as $line) {
                if (strpos($line, "asn1 encoding routines:ASN1_mbstring_ncopy:string too long") !== false) { //openSSL error message returned if the CN is too long
                    $result["status"] = 'ko';
                    $result["error_msg"] .= "\n IOT Device name too long";
                    $result["msg"] .= "\n IOT Device name too long";
                    $result["log"] .= "\n IOT Device name too long";
                    return 0;
                } else if (strpos($line, "error") !== false) {
                    $result["status"] = 'ko';
                    $result["error_msg"] .= "An error has been catched generating the device certificate: " . $line;
                    $result["msg"] .= "\n an error has been returned from the script for certificate generation " . $line;
                    $result["log"] .= "\n an error has been returned from the script for certificate generation " . $line;
                    return 0;
                }
            }

            return 1;
        } else {
            $result["msg"] .= "\n the model $model is NOT of type authenticated";
            $result["log"] .= "\n the model $model is NOT of type authenticated";
            return 0;
        }
    } else {
        $result["status"] = 'ko';
        $result["error_msg"] .= "Error in the query for retrieving the type of model";
        $result["msg"] .= "\n error in the query for retrieving the type of model";
        $result["log"] .= "\n error in the query for retrieving the type of model" . generateErrorMessage($link);
        return 0;
    }
}

/* functions for getting parameters */

function generatedatatypes($link) {
    $query2 = "SELECT data_type FROM data_types order by data_type";
    $res = mysqli_query($link, $query2);
    $attributes = array();

    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            array_push($attributes, $row["data_type"]);
        }
    }
    return $attributes;
}

function generateAttributes($link, $name, $cb) {
    $query2 = "SELECT * FROM event_values WHERE cb = '$cb' AND device = '$name'";
    $res = mysqli_query($link, $query2) or die(mysqli_error($link));
    $attributes = array();

    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
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
            if ($rec["healthiness_criteria"] == "refresh_rate")
                $rec["healthiness_value"] = $row["value_refresh_rate"];
            if ($rec["healthiness_criteria"] == "different_values")
                $rec["healthiness_value"] = $row["different_values"];
            if ($rec["healthiness_criteria"] == "within_bounds")
                $rec["healthiness_value"] = $row["value_bounds"];
            $rec["real_time_flag"] = $row["real_time_flag"];
            array_push($attributes, $rec);
        }
    }
    return $attributes;
}

function generateErrorMessage($link) {
    $error_number = mysqli_errno($link);
    $errmsg = "";

    switch ($error_number) {
        case 1022:
            $errmsg = "A device/value already exist! Please use a different identifier";
            break;
        case 1061:
            $errmsg = "The proposed identifier has been already used";
            break;
        case 1062:
            $errmsg = "Duplicate entry";
            break;
        case 1169:
            $errmsg = "Can't write, because of unique constraint";
            break;
        case 1215:
            $errmsg = "You are trying to insert a value for a device that does not exist";
            break;
        case 6:
            $errmsg = "Error on delete";
            break;
        case 1025:
            $errmsg = "Error on rename";
            break;
        case 1032:
            $errmsg = "The device/value you are looking for does not exist";
            break;
        case 1048:
            $errmsg = mysqli_error($link);
            break;
        case 1176:
            $errmsg = mysqli_error($link);
            break;
        case 1215:
            $errmsg = "Cannot add foreign key constraint";
            break;
        case 1288:
            $errmsg = "he target table %s of the %s is not updatable";
            break;
        case 1294:
            $errmsg = "Invalid ON UPDATE clause for '%s' column";
            break;
        Default:
            $errmsg = mysqli_error($link);
            break;
    }
    return $errmsg;
}

function removeOwnerShipDevice($elementId, $token, &$result) {
    try {
        $url = $GLOBALS["ownershipURI"] . "ownership-api/v1/delete/?type=IOTID&elementId=" . $elementId . "&accessToken=" . $token;
        $options = array(
            'http' => array(
                'header' => "Content-Type: application/json;charset=utf-8",
                'header' => "Access-Control-Allow-Origin: *",
                'method' => 'POST',
                'ignore_errors' => true,
                'timeout' => 30
            )
        );
        $context = stream_context_create($options);
        $local_result = @file_get_contents($url, false, $context);
        if (strpos($http_response_header[0], '200') !== false) {
            $result["status"] = 'ok';
            $result["msg"] .= "\n the deletion of the ownership succeded";
            $result["log"] .= "\n the deletion of the ownership succeded";
        } else {
            $result["status"] = 'ko';
            $result["error_msg"] .= "Error in deleting the ownership. ";
            $result["msg"] .= "\n error in deleting the ownership";
            $result["log"] .= "\n error in deleting the ownership";
        }
    } catch (Exception $ex) {
        $result["status"] = 'ko';
        $result["error_msg"] .= 'Error in removing the ownership. ';
        $result["msg"] .= '\n error in removing the ownership ';
        $result["log"] .= '\n error in removing the ownership ' . $ex;
    }
}

function removeOwnerShipObject($elementId, $token, $object, &$result) {
    try {
        $result["log"] .= "\n\r Deletion of the ownership invoked on $elementId $object";
        $url = $GLOBALS["ownershipURI"] . "ownership-api/v1/delete/?type=" . $object . "&elementId=" . urlencode($elementId) . "&accessToken=" . $token;
        $options = array(
            'http' => array(
                'header' => "Content-Type: application/json;charset=utf-8",
                'header' => "Access-Control-Allow-Origin: *",
                'method' => 'POST',
                'ignore_errors' => true,
                'timeout' => 30
            )
        );
        $context = stream_context_create($options);
        $local_result = @file_get_contents($url, false, $context);
        if (strpos($http_response_header[0], '200') !== false) {
            $result["status"] = 'ok';
            $result["msg"] .= "\n the deletion of the ownership succeded";
            $result["log"] .= "\n the deletion of the ownership succeded";
        } else {
            $result["status"] = 'ko';
            $result["error_msg"] .= "Error in deleting the ownership. ";
            $result["msg"] .= "\n error in deleting the ownership";
            $result["log"] .= "\n error in deleting the ownership";
        }
    } catch (Exception $ex) {
        $result["status"] = 'ko';
        $result["error_msg"] .= 'Error in removing the ownership. ';
        $result["msg"] .= '\n error in removing the ownership ';
        $result["log"] .= '\n error in removing the ownership ' . $ex;
    }
}

function registerOwnerShipDevice($elementId, $msg, $token, &$result) {
    $msg["elementType"] = "IOTID";
    $result["msg"] .= "\n the element id is " . $elementId;

    try {
        $url = $GLOBALS["ownershipURI"] . "ownership-api/v1/register/?accessToken=" . $token;
        $options = array(
            'http' => array(
                'header' => "Content-Type: application/json;charset=utf-8",
                'header' => "Access-Control-Allow-Origin: *",
                'method' => 'POST',
                'content' => json_encode($msg),
                'ignore_errors' => true,
                'timeout' => 30
            )
        );
        $context = stream_context_create($options);
        $local_result = @file_get_contents($url, false, $context);

        if (strpos($http_response_header[0], '200') !== false) {
            $result["status"] = 'ok';
            $result["msg"] .= "\n the registration of the ownership succeded " . $elementId;
            $result["log"] .= "\n the registration of the ownership succeded" . $elementId . " " . $local_result;
        } else {
            $result["status"] = 'ok';
            $result["error_msg"] .= "The registration is NOT possible. Reached limit of IoT Devices. ";
            $result["msg"] .= "\n The registration is NOT possible. Reached limit of IoT Devices (" . json_decode($local_result)->limit . ")";
            $result["log"] .= "\n The registration is NOT possible. Reached limit of IoT Devices (" . json_decode($local_result)->limit . ")";
        }
    } catch (Exception $ex) {
        $result["status"] = 'ko';
        $result["error_msg"] .= 'Error in registering the ownership. ';
        $result["msg"] .= '\n error in registering the ownership ';
        $result["log"] .= '\n error in registering the ownership ' . $ex;
    }
}

function registerOwnerShipObject($msg, $token, $object, &$result) {
    try {
        $url = $GLOBALS["ownershipURI"] . "ownership-api/v1/register/?accessToken=" . $token;
        $options = array(
            'http' => array(
                'header' => "Content-Type: application/json;charset=utf-8",
                'header' => "Access-Control-Allow-Origin: *",
                'method' => 'POST',
                'content' => json_encode($msg),
                'ignore_errors' => true,
                'timeout' => 30
            )
        );
        $context = stream_context_create($options);
        $local_result = @file_get_contents($url, false, $context);

        if (strpos($http_response_header[0], '200') !== false) {
            $result["status"] = 'ok';
            $result["msg"] .= "\n the registration of the ownership succeded";
            $result["log"] .= "\n the registration of the ownership succeded" . $local_result;
        } else {
            $result["status"] = 'ok';
            $result["error_msg"] .= "The registration is NOT possible. Reached limit of IoT Devices. ";
            $result["msg"] .= "\n The registration is NOT possible. Reached limit of IoT Devices (" . $local_result . ")";
            $result["log"] .= "\n The registration is NOT possible. Reached limit of IoT Devices ";
        }
    } catch (Exception $ex) {
        $result["status"] = 'ko';
        $result["error_msg"] .= 'Error in registering the ownership. ';
        $result["msg"] .= '\n error in registering the ownership ';
        $result["log"] .= '\n error in registering the ownership ' . $ex;
    }
}

function delegateDeviceValue($elementId, $contextbroker, $value_name, $user, $userdelegated, $groupdelegated, $token, $k1, $k2, &$result, $kind = "READ_ACCESS") {
    $msg["elementId"] = $elementId;
    if ($value_name !== NULL) {//delegate a sensor scenario
        $msg["variableName"] = $value_name;
        $msg["elementType"] = "ServiceURI"; // ServiceUri
    } else {//delegate a device scenario
        $msg["elementType"] = "IOTID"; // ServiceUri
    }
    if ($userdelegated !== "")
        $msg["usernameDelegated"] = $userdelegated;
    if ($groupdelegated !== "") {

        $msg["groupnameDelegated"] = $groupdelegated . "," . $GLOBALS["ldapBaseName"];
    }
    if ($k1 != "" && $k2 != "") {
        $msg["delegationDetails"] = array();
        $msg["delegationDetails"]["k1"] = $k1;
        $msg["delegationDetails"]["k2"] = $k2;
    }
    $msg["kind"] = $kind;

    try {
        $url = $GLOBALS["delegationURI"] . "datamanager/api/v1/username/" . urlencode($user) . "/delegation?accessToken=" . $token .
                "&sourceRequest=iotdirectory";

        $options = array(
            'http' => array(
                'header' => "Content-Type: application/json;charset=utf-8\r\n" .
                "Access-Control-Allow-Origin: *",
                'method' => 'POST',
                'content' => json_encode($msg),
                'ignore_errors' => true,
                'timeout' => 30
            )
        );

        $context = stream_context_create($options);
        $local_result = file_get_contents($url, false, $context);

        if (strpos($http_response_header[0], '200') !== false) {
            $elem = json_decode($local_result);
            $result["status"] = 'ok';
            $result["delegationId"] = $elem->id;
            $result["kind"] = $kind;

            $result["msg"] = "\n the registration of the delegation succeded";
            $result["log"] .= "\n the registration of the delegation succeded" . $url . " result " . $local_result . " msg " .
                    json_encode($msg);
        } else {
            $result["status"] = 'ko';
            $result["error_msg"] = "Error in the delegation: ". $local_result;
            $result["msg"] .= "\n error in the delegation";
            $result["log"] .= "\n error in the delegation" . $url . " result " . $local_result . " msg " . json_encode($msg);
        }
    } catch (Exception $ex) {
        $result["status"] = 'ko';
        $result["error_msg"] .= 'Error in the delegation, exception occurred. ';
        $result["msg"] .= '\n error in the delegation, exception occurred.';
        $result["log"] .= '\n error in the delegation, exception occurred.' . $ex;
    }
}

function delegateObject($elementId, $user, $userdelegated, $groupdelegated, $object, $token, &$result) {
    $msg["elementId"] = $elementId;
    $msg["elementType"] = $object;

    if ($userdelegated !== "")
        $msg["usernameDelegated"] = $userdelegated;

    if ($groupdelegated !== "") {
        $msg["groupnameDelegated"] = $groupdelegated . "," . $GLOBALS["ldapBaseName"];
    }

    try {

        $url = $GLOBALS["delegationURI"] . "datamanager/api/v1/username/" . urlencode($user) . "/delegation?accessToken=" . $token . "&sourceRequest=iotdirectory";

        $options = array(
            'http' => array(
                'header' => "Content-Type: application/json;charset=utf-8\r\n" .
                "Access-Control-Allow-Origin: *",
                'method' => 'POST',
                'content' => json_encode($msg),
                'ignore_errors' => true,
                'timeout' => 30
            )
        );

        $context = stream_context_create($options);
        $local_result = file_get_contents($url, false, $context);

        if (strpos($http_response_header[0], '200') !== false) {
            $elem = json_decode($local_result);
            $result["status"] = 'ok';
            $result["delegationId"] = $elem->id;

            $result["msg"] .= "\n the registration of the delegation succeded";
            $result["log"] .= "\n the registration of the delegation succeded" . $url . " result " . $local_result . " msg " . json_encode($msg);
        } else {
            $result["status"] = 'ko';
            $result["error_msg"] .= "Error in the delegation. ";
            $result["msg"] .= "\n error in the delegation";
            $result["log"] .= "\n error in the delegation" . $url . " result " . $local_result . " msg " . json_encode($msg);
        }
    } catch (Exception $ex) {
        $result["status"] = 'ko';
        $result["error_msg"] .= 'Error in the delegation. ';
        $result["msg"] .= '\n error in the delegation';
        $result["log"] .= '\n error in the delegation' . $ex;
    }
}

function removeDelegationValue($token, $user, $delegationId, &$result) {
    $local_result = "";
    try {
        $url = $GLOBALS["delegationURI"] . "datamanager/api/v1/username/" . urlencode($user) . "/delegation/" . $delegationId . "?accessToken=" . $token .
                "&sourceRequest=iotdirectory";
        $options = array(
            'http' => array(
                'header' => "Content-Type: application/json;charset=utf-8\r\n" .
                "Access-Control-Allow-Origin: *",
                'method' => 'DELETE',
                'ignore_errors' => true,
                'timeout' => 30
            )
        );

        $context = stream_context_create($options);
        $local_result = file_get_contents($url, false, $context);
        if (strpos($http_response_header[0], '200') !== false) {
            $result["status"] = 'ok';
            $result["msg"] .= '\n The delegation has been removed ';
            $result["log"] .= '\n The delegation has been removed ' . $local_result;
        } else {
            $result["status"] = 'ko';
            $result["error_msg"] .= "Error in removing the delegation. ";
            $result["msg"] .= "\n error in removing the delegation";
            $result["log"] .= "\n error in removing the delegation";
        }
    } catch (Exception $ex) {
        $result["status"] = 'ko';
        $result["error_msg"] .= 'catched Error in removing the delegation. ';
        $result["msg"] .= '\n catched error in removing the delegation ';
        $result["log"] .= '\n catched error in removing the delegation ' . $ex;
    }
}

function getUserDelegatedDevice($token, $user, $type, &$result) {
    $local_result = "";
    $mykeys = (isset($result['delegation']) ? $result['delegation'] : array());
    try {
        $url = $GLOBALS["delegationURI"] . "datamanager/api/v2/username/" . urlencode($user) . "/delegated?accessToken=" . $token .
                "&sourceRequest=iotdirectory&elementType=" . $type;
        $local_result = file_get_contents($url);
    } catch (Exception $ex) {
        $result["status"] = 'ko';
        $result["error_msg"] .= 'Error in accessing the delegation. ';
        $result["msg"] .= '\n error in accessing the delegation ';
        $result["log"] .= '\n error in accessing the delegation ' . $ex;
    }
    if (strpos($http_response_header[0], '200') == true || strpos($http_response_header[0], '204') == true) {
        $lists = json_decode($local_result);
        $kind = ($user == "ANONYMOUS" ? 'anonymous' : 'specific');
        for ($i = 0; $i < count($lists); $i++) {
            if (isset($lists[$i]->elementType) && ($lists[$i]->elementType == "ServiceURI" || $lists[$i]->elementType == "IOTID")) {
                $a = $lists[$i]->elementId;
                if (isset($mykeys[$a])) {
                    switch ($mykeys[$a]["delegationKind"]) {
                        case "READ_ACCESS":
                            if ($lists[$i]->kind == "READ_WRITE" || $lists[$i]->kind == "MODIFY") {
                                if (isset($lists[$i]->delegationDetails)) {
                                    $delegationDetails = json_decode($lists[$i]->delegationDetails);
                                    $mykeys[$a] = array("usernameDelegator" => $lists[$i]->usernameDelegator, "delegationId" => $lists[$i]->id, "kind" => $kind, "k1" => $delegationDetails->k1, "k2" => $delegationDetails->k2, "delegationKind" => $lists[$i]->kind);
                                } else {
                                    $mykeys[$a] = array("usernameDelegator" => $lists[$i]->usernameDelegator, "delegationId" => $lists[$i]->id, "kind" => $kind, "k1" => "", "k2" => "", "delegationKind" => $lists[$i]->kind);
                                }
                            }
                            break;
                        case "READ_WRITE":
                            if ($lists[$i]->kind == "MODIFY") {
                                if (isset($lists[$i]->delegationDetails)) {
                                    $delegationDetails = json_decode($lists[$i]->delegationDetails);
                                    $mykeys[$a] = array("usernameDelegator" => $lists[$i]->usernameDelegator, "delegationId" => $lists[$i]->id, "kind" => $kind, "k1" => $delegationDetails->k1, "k2" => $delegationDetails->k2, "delegationKind" => $lists[$i]->kind);
                                } else {
                                    $mykeys[$a] = array("usernameDelegator" => $lists[$i]->usernameDelegator, "delegationId" => $lists[$i]->id, "kind" => $kind, "k1" => "", "k2" => "", "delegationKind" => $lists[$i]->kind);
                                }
                            }
                            break;
                        case "WRITE_ONLY":
                            if ($lists[$i]->kind == "WRITE_ONLY") {
                                if (isset($lists[$i]->delegationDetails)) {
                                    $delegationDetails = json_decode($lists[$i]->delegationDetails);
                                    $mykeys[$a] = array("usernameDelegator" => $lists[$i]->usernameDelegator, "delegationId" => $lists[$i]->id, "kind" => $kind, "k1" => $delegationDetails->k1, "k2" => $delegationDetails->k2, "delegationKind" => $lists[$i]->kind);
                                } else {
                                    $mykeys[$a] = array("usernameDelegator" => $lists[$i]->usernameDelegator, "delegationId" => $lists[$i]->id, "kind" => $kind, "k1" => "", "k2" => "", "delegationKind" => $lists[$i]->kind);
                                }
                            }
                            break;
                    }
                } else {
                    if (isset($lists[$i]->delegationDetails)) {
                        $delegationDetails = json_decode($lists[$i]->delegationDetails);
                        $mykeys[$a] = array("usernameDelegator" => $lists[$i]->usernameDelegator, "delegationId" => $lists[$i]->id, "kind" => $kind, "k1" => $delegationDetails->k1, "k2" => $delegationDetails->k2, "delegationKind" => $lists[$i]->kind);
                    } else {
                        $mykeys[$a] = array("usernameDelegator" => $lists[$i]->usernameDelegator, "delegationId" => $lists[$i]->id, "kind" => $kind, "k1" => "", "k2" => "", "delegationKind" => $lists[$i]->kind);
                    }
                }
            }
        }
        $result["status"] = 'ok';
        $result["delegation"] = $mykeys;

        // echo json_encode($result["delegation"]);
    } else {
        $result["status"] = 'ko';
        $result["error_msg"] .= 'Errors in reading delegations personal. ';
        $result["msg"] .= '\n errors in reading delegations personal ' . $local_result . $url . "------" . $http_response_header[0];
        $result["log"] .= '\n errors in reading delegations personal' . $local_result . $url . "------" . $http_response_header[0];
    }
}

function getDelegatedDevice($token, $user, &$result) {
    if (isset($GLOBALS['Cached_config']) && $GLOBALS['Cached_config']) {
        $GLOBALS['m'] = new Memcached();
        $GLOBALS['m']->addServer($GLOBALS['Cached_host'], $GLOBALS['Cached_port']);
        $IoT_Anonymus_id = $GLOBALS['m']->get('ANONYMOUS_IOTID');
        unset($IoT_Anonymus_id['log']);

        if (!($IoT_Anonymus_id)) {
            getUserDelegatedDevice($token, "ANONYMOUS", "IOTID", $result);
            if ($result['status'] == 'ko') {
                return;
            }
            getUserDelegatedDevice($token, "ANONYMOUS", "ServiceURI", $result);
            if ($result['status'] == 'ko') {
                return;
            }
            $result['log'] = '...omissis...';
            $r = $GLOBALS['m']->set('ANONYMOUS_IOTID', $result['delegation'], $GLOBALS['expTimeCache']);
            $result['cache'] = 'SAVED ' . $r . ' -- ' . $GLOBALS['m']->getResultCode();
        } else {
            $result['delegation'] = $IoT_Anonymus_id;
            $result['cache'] = 'READ';
        }
    } else {
        getUserDelegatedDevice($token, "ANONYMOUS", "IOTID", $result);
        if ($result['status'] == 'ko') {
            return;
        }
        getUserDelegatedDevice($token, "ANONYMOUS", "ServiceURI", $result);
        if ($result['status'] == 'ko') {
            return;
        }
        $result['cache'] = 'NO';
    }
    getUserDelegatedDevice($token, $user, "IOTID", $result);
    if ($result['status'] == 'ko') {
        return;
    }
    getUserDelegatedDevice($token, $user, "ServiceURI", $result);
    if ($result['status'] == 'ko') {
        return;
    }
}

function ServerCacheManage($token, $action) {
    if ($action == 'clear' && isset($GLOBALS['Cached_config'])) {
        $GLOBALS['m'] = new Memcached();
        $GLOBALS['m']->addServer($GLOBALS['Cached_host'], $GLOBALS['Cached_port']);
        $GLOBALS['m']->delete('ANONYMOUS_IOTID');
    }
}

/*
  function getDelegatedDevice($token, $user, &$result) {
  $local_result = "";
  $mykeys = array();
  try {
  $url = $GLOBALS["delegationURI"] . "datamanager/api/v2/username/" . urlencode($user) . "/delegated?accessToken=" . $token .
  "&sourceRequest=iotdirectory"; //&elementType=IOTID";
  $local_result = file_get_contents($url);
  } catch (Exception $ex) {
  $result["status"] = 'ko';
  $result["error_msg"] .= 'Error in accessing the delegation. ';
  $result["msg"] .= '\n error in accessing the delegation ';
  $result["log"] .= '\n error in accessing the delegation ' . $ex;
  }
  if (strpos($http_response_header[0], '200') == true || strpos($http_response_header[0], '204') == true) {
  $lists = json_decode($local_result);
  for ($i = 0; $i < count($lists); $i++) {
  if (isset($lists[$i]->elementType) && ($lists[$i]->elementType == "ServiceURI" || $lists[$i]->elementType == "IOTID")) {

  $a = $lists[$i]->elementId;
  if (isset($lists[$i]->delegationDetails)) {
  $delegationDetails = json_decode($lists[$i]->delegationDetails);
  $mykeys[$a] = array("usernameDelegator" => $lists[$i]->usernameDelegator, "delegationId" => $lists[$i]->id, "kind" => 'specific', "k1" => $delegationDetails->k1, "k2" => $delegationDetails->k2);
  } else {
  $mykeys[$a] = array("usernameDelegator" => $lists[$i]->usernameDelegator, "delegationId" => $lists[$i]->id, "kind" => 'specific', "k1" => "", "k2" => "");
  }
  }
  }
  try {
  $url = $GLOBALS["delegationURI"] . "datamanager/api/v1/username/ANONYMOUS/delegated?accessToken=" . $token .
  "&sourceRequest=iotdirectory"; //&elementType=IOTID";
  $local_result = file_get_contents($url);

  if (strpos($http_response_header[0], '200') == true || strpos($http_response_header[0], '204') == true) {
  $lists = json_decode($local_result);
  for ($i = 0; $i < count($lists); $i++) {
  if (isset($lists[$i]->elementType) && ($lists[$i]->elementType == "ServiceURI" || $lists[$i]->elementType == "IOTID")) {
  $a = $lists[$i]->elementId;
  if (isset($lists[$i]->delegationDetails) && isset($lists[$i]->delegationDetails->k1))
  $mykeys[$a] = array("usernameDelegator" => $lists[$i]->usernameDelegator, "delegationId" => $lists[$i]->id, "kind" => 'anonymous', "k1" => $lists[$i]->delegationDetails->k1, "k2" => $lists[$i]->delegationDetails->k2);
  else
  $mykeys[$a] = array("usernameDelegator" => $lists[$i]->usernameDelegator, "delegationId" => $lists[$i]->id, "kind" => 'anonymous', "k1" => "", "k2" => "");
  }
  }
  $result["status"] = 'ok';
  $result["delegation"] = $mykeys;
  //$result["msg"] .= '\n identified ' . count($lists) . ' anonymous delegated devices \n' . json_encode($mykeys);
  //$result["log"] .= '\n identified ' . count($lists) . ' anonymous delegated devices \n' . json_encode($mykeys);
  } else {
  $result["status"] = 'ko';
  $result["error_msg"] .= 'Errors in reading delegations anonymous. ';
  $result["msg"] .= '\n errors in reading delegations anonymous' . $local_result . $url . "------" . $http_response_header[0];
  $result["log"] .= '\n errors in reading delegations anonymous' . $local_result . $url . "------" . $http_response_header[0];
  }
  } catch (Exception $ex) {
  $result["status"] = 'ko';
  $result["error_msg"] .= 'Error in accessing the delegation. ';
  $result["msg"] .= '\n error in accessing the delegation ' . $ex;
  $result["log"] .= '\n error in accessing the delegation ' . $ex;
  }
  } else {
  $result["status"] = 'ko';
  $result["error_msg"] .= 'Errors in reading delegations personal. ';
  $result["msg"] .= '\n errors in reading delegations personal ' . $local_result . $url . "------" . $http_response_header[0];
  $result["log"] .= '\n errors in reading delegations personal' . $local_result . $url . "------" . $http_response_header[0];
  }
  }
 */

function getDelegatedObject($token, $user, $object, &$result) {
    $local_result = "";
    $mykeys = array();
    try {
        $url = $GLOBALS["delegationURI"] . "datamanager/api/v2/username/" . urlencode($user) . "/delegated?accessToken=" . $token . "&sourceRequest=iotdirectory&elementType=" . $object;
        $local_result = file_get_contents($url);
    } catch (Exception $ex) {
        $result["status"] = 'ko';
        $result["error_msg"] .= 'Error in accessing the delegation. ';
        $result["msg"] .= '\n error in accessing the delegation ';
        $result["log"] .= '\n error in accessing the delegation ' . $ex;
    }
    if (strpos($http_response_header[0], '200') == true || strpos($http_response_header[0], '204') == true) {
        $lists = json_decode($local_result);
        for ($i = 0; $i < count($lists); $i++) {
            if (isset($lists[$i]->elementType) && $lists[$i]->elementType == $object) {
                $a = $lists[$i]->elementId;
                if (isset($mykeys[$a])) {
                    switch ($mykeys[$a]["delegationKind"]) {
                        case "READ_ACCESS":
                            if ($lists[$i]->kind == "READ_WRITE" || $lists[$i]->kind == "MODIFY") {
                                $mykeys[$a] = array("usernameDelegator" => $lists[$i]->usernameDelegator, "delegationId" => $lists[$i]->id, "kind" => 'specific', "delegationKind" => $lists[$i]->kind);
                            }
                            break;
                        case "READ_WRITE":
                            if ($lists[$i]->kind == "MODIFY") {
                                $mykeys[$a] = array("usernameDelegator" => $lists[$i]->usernameDelegator, "delegationId" => $lists[$i]->id, "kind" => 'specific', "delegationKind" => $lists[$i]->kind);
                            }
                            break;
                        case "WRITE_ONLY":
                            if ($lists[$i]->kind == "WRITE_ONLY") {
                                $mykeys[$a] = array("usernameDelegator" => $lists[$i]->usernameDelegator, "delegationId" => $lists[$i]->id, "kind" => 'specific', "delegationKind" => $lists[$i]->kind);
                            }
                            break;
                    }
                } else {
                    $mykeys[$a] = array("usernameDelegator" => $lists[$i]->usernameDelegator, "delegationId" => $lists[$i]->id, "kind" => 'specific', "delegationKind" => $lists[$i]->kind);
                }
            }
        }
        try {
            $url = $GLOBALS["delegationURI"] . "datamanager/api/v1/username/ANONYMOUS/delegated?accessToken=" . $token . "&sourceRequest=iotdirectory&elementType=" . $object;
            $local_result = file_get_contents($url);
            if (strpos($http_response_header[0], '200') == true || strpos($http_response_header[0], '204') == true) {
                $lists = json_decode($local_result);
                for ($i = 0; $i < count($lists); $i++) {
                    if (isset($lists[$i]->elementType) && $lists[$i]->elementType == $object) {
                        $a = $lists[$i]->elementId;
                        if (isset($mykeys[$a])) {
                            switch ($mykeys[$a]["delegationKind"]) {
                                case "READ_ACCESS":
                                    if ($lists[$i]->kind == "READ_WRITE" || $lists[$i]->kind == "MODIFY") {
                                        $mykeys[$a] = array("usernameDelegator" => $lists[$i]->usernameDelegator, "delegationId" => $lists[$i]->id, "kind" => 'anonymous', "delegationKind" => $lists[$i]->kind);
                                    }
                                    break;
                                case "READ_WRITE":
                                    if ($lists[$i]->kind == "MODIFY") {
                                        $mykeys[$a] = array("usernameDelegator" => $lists[$i]->usernameDelegator, "delegationId" => $lists[$i]->id, "kind" => 'anonymous', "delegationKind" => $lists[$i]->kind);
                                    }
                                    break;
                            }
                        } else {
                            $mykeys[$a] = array("usernameDelegator" => $lists[$i]->usernameDelegator, "delegationId" => $lists[$i]->id, "kind" => 'anonymous', "delegationKind" => $lists[$i]->kind);
                        }
                    }
                }
                $result["status"] = 'ok';
                $result["delegation"] = $mykeys;
            } else {
                $result["status"] = 'ko';
            }
        } catch (Exception $ex) {
            $result["status"] = 'ko';
            $result["error_msg"] .= 'Error in accessing the delegation. ';
            $result["msg"] .= '\n error in accessing the delegation ' . $ex;
            $result["log"] .= '\n error in accessing the delegation ' . $ex;
        }
    } else {
        $result["status"] = 'ko';
        $result["error_msg"] .= 'Errors in reading delegations personal. ';
        $result["msg"] .= '\n errors in reading delegations personal ' . $local_result . $url . "------" . $http_response_header[0];
        $result["log"] .= '\n errors in reading delegations personal' . $local_result . $url . "------" . $http_response_header[0];
    }
}

//this function reuse an existent function
//TODO use the check apis from mypersonaldata
function checkDelegationObject($username, $token, $elementId, $elementType, &$result) {
    $toreturn = false;

    getDelegatedObject($token, $username, $elementType, $result);

    if ($result["status"] == "ok") {
        if (isset($result["delegation"][$elementId])) {
            $toreturn = true;
            $result["delegationKind"] = isset($result["delegation"][$elementId]["delegationKind"]) ? $result["delegation"][$elementId]["delegationKind"] : "READ_ACCESS";
        }
        $result["status"] = 'ok';
        $result["msg"] .= '\n check delegation ' . $toreturn;
        $result["log"] .= '\n check delegation ' . $toreturn;
    } else {
        $result["status"] = 'ko';
        $result["error_msg"] = 'Error in accessing the delegation. ';
        $result["msg"] = '\n error in accessing the delegation';
        $result["log"] = '\n error in accessing the delegation';
    }
    unset($result["delegation"]);

    return $toreturn;
}

//TODO show the k1 and k2 also if delegator==user (the owner of the delegation should be able to access k1 and k2 delegations)
function getDelegatorDevice($token, $user, &$result, $eId) {
    $local_result = "";
    $mykeys = array();
    try {
        $url = $GLOBALS["delegationURI"] . "datamanager/api/v2/username/" . urlencode($user) . "/delegator?accessToken=" . $token .
                "&sourceRequest=iotdirectory";
        $local_result = file_get_contents($url);
        if (strpos($http_response_header[0], '200') == true || strpos($http_response_header[0], '204') == true) {
            $lists = json_decode($local_result);

            for ($i = 0; $i < count($lists); $i++) {
                if (isset($lists[$i]->elementType) && ($lists[$i]->elementType == "ServiceURI" || $lists[$i]->elementType == "IOTID")) {
                    $a = $lists[$i]->elementId;
                    if ($eId == $a) {
                        $kind = isset($lists[$i]->kind) ? $lists[$i]->kind : "READ_ACCESS";
                        if (isset($lists[$i]->delegationDetails) && isset($lists[$i]->delegationDetails->k1)) {
                            if (isset($lists[$i]->usernameDelegated)) {
                                $mykeys[] = array("userDelegated" => $lists[$i]->usernameDelegated, "delegationId" => $lists[$i]->id, "k1" => $lists[$i]->delegationDetails->k1, "k2" => $lists[$i]->delegationDetails->k2, "kind" => $kind);
                            } else {
                                $mykeys[] = array("groupDelegated" => $lists[$i]->groupnameDelegated, "delegationId" => $lists[$i]->id, "k1" => $lists[$i]->delegationDetails->k1, "k2" => $lists[$i]->delegationDetails->k2, "kind" => $kind);
                            }
                        } else {
                            if (isset($lists[$i]->usernameDelegated)) {
                                $mykeys[] = array("userDelegated" => $lists[$i]->usernameDelegated, "delegationId" => $lists[$i]->id, "k1" => "", "k2" => "", "kind" => $kind);
                            } else {
                                $mykeys[] = array("groupDelegated" => $lists[$i]->groupnameDelegated, "delegationId" => $lists[$i]->id, "k1" => "", "k2" => "", "kind" => $kind);
                            }
                        }
                    }
                }
            }
            $result["status"] = 'ok';

            $result["delegation"] = $mykeys;
            $result["msg"] .= '\n identified ' . count($lists) . ' delegations\n' . $local_result . json_encode($mykeys);
            $result["log"] .= '\n identified ' . count($lists) . ' delegations\n' . json_encode($mykeys);
        } else {
            $result["status"] = 'ko';
            $result["error_msg"] .= 'Error in accessing the delegation. ';
            $result["msg"] .= '\n error in accessing the delegation ' . $http_response_header[0];
            $result["log"] .= '\n error in accessing the delegation ' . $http_response_header[0];
        }
    } catch (Exception $ex) {
        $result["status"] = 'ko';
        $result["error_msg"] .= 'Error happened while accessing the delegation. ';
        $result["msg"] .= '\n error in accessing the delegation ';
        $result["log"] .= '\n error in accessing the delegation ' . $ex;
    }
}

function getDelegatorObject($token, $user, &$result, $object, $delegationId) {
    $local_result = "";
    $mykeys = array();
    try {
        $url = $GLOBALS["delegationURI"] . "datamanager/api/v2/username/" . urlencode($user) . "/delegator?accessToken=" . $token . "&sourceRequest=iotdirectory";
        $local_result = file_get_contents($url);

        if (strpos($http_response_header[0], '200') == true || strpos($http_response_header[0], '204') == true) {
            $lists = json_decode($local_result);
            for ($i = 0; $i < count($lists); $i++) {
                if (isset($lists[$i]->elementType) && $lists[$i]->elementType == $object) {
                    $a = $lists[$i]->elementId;
                    if ($delegationId == $a) {
                        if (isset($lists[$i]->usernameDelegated)) {
                            $mykeys[] = array("userDelegated" => $lists[$i]->usernameDelegated, "delegationId" => $lists[$i]->id);
                        } else {
                            $mykeys[] = array("groupDelegated" => $lists[$i]->groupnameDelegated, "delegationId" => $lists[$i]->id);
                        }
                    }
                }
            }
            $result["status"] = 'ok';
            $result["delegation"] = $mykeys;
            $result["msg"] .= '\n identified ' . count($lists) . ' delegations\n' . $local_result . json_encode($mykeys);
            $result["log"] .= '\n identified ' . count($lists) . ' delegations\n' . json_encode($mykeys);
        } else {
            $result["status"] = 'ko';
            $result["error_msg"] .= 'Error in accessing the delegation. ';
            $result["msg"] .= '\n error in accessing the delegation ' . $http_response_header[0];
            $result["log"] .= '\n error in accessing the delegation ' . $http_response_header[0];
        }
    } catch (Exception $ex) {
        $result["status"] = 'ko';
        $result["error_msg"] .= 'Error happened when accessing the delegation. ';
        $result["msg"] .= '\n error in accessing the delegation ';
        $result["log"] .= '\n error in accessing the delegation ' . $ex;
    }
}

function getOwnerShipDevice($token, &$result, $elementId = null) {
    $listCondDevice = "";
    $local_result = "";
    $mykeys = array();
    try {
        if ($elementId)
            $url = $GLOBALS["ownershipURI"] . "ownership-api/v1/list/?elementId=$elementId&type=IOTID&accessToken=" . $token;
        else
            $url = $GLOBALS["ownershipURI"] . "ownership-api/v1/list/?type=IOTID&accessToken=" . $token;

        $local_result = file_get_contents($url);
        $result["log"] .= $local_result;
        if (strpos($http_response_header[0], '200') == true || strpos($http_response_header[0], '204') == true) {
            $lists = json_decode($local_result);
            for ($i = 0; $i < count($lists); $i++) {
                if (!isset($lists[$i]->deleted)) {
                    if (strpos($lists[$i]->elementId, ":") > 0) {
                        $org = substr($lists[$i]->elementId, 0, strpos($lists[$i]->elementId, ":"));
                        $cb_name = substr($lists[$i]->elementId, strpos($lists[$i]->elementId, ":") + 1, strlen($lists[$i]->elementId));
                        $cb = substr($cb_name, 0, strpos($cb_name, ":"));
                        $name = substr($cb_name, strpos($cb_name, ":") + 1, strlen($cb_name));
                    } else {
                        $name = $lists[$i]->elementId;
                    }

                    $listCondDevice .= " (id = '" . $name . "' AND contextbroker = '" . $lists[$i]->elementDetails->contextbroker . "') ";
                    if ($i != count($lists) - 1)
                        $listCondDevice .= " OR ";

                    $gtwtype = "";
                    $gtwuri = "";
                    if (isset($lists[$i]->elementDetails->edgegateway_type))
                        $gtwtype = $lists[$i]->elementDetails->edgegateway_type;
                    if (isset($lists[$i]->elementDetails->edgegateway_uri))
                        $gtwuri = $lists[$i]->elementDetails->edgegateway_uri;

                    $mykeys[$lists[$i]->elementId] = array("k1" => $lists[$i]->elementDetails->k1,
                        "k2" => $lists[$i]->elementDetails->k2,
                        "cb" => $lists[$i]->elementDetails->contextbroker,
                        "owner" => $lists[$i]->username,
                        "edgegateway_type" => $gtwtype,
                        "edgegateway_uri" => $gtwuri);
                    $result["username"] = $lists[$i]->username;
                }
            }
            $result["status"] = 'ok';
            $result["keys"] = $mykeys;
            $result["msg"] = '\n identified ' . count($lists) . ' private devices \n';
            $result["log"] .= '\n identified ' . count($lists) . ' private devices \n'; //  .  $listCondDevice . json_encode($mykeys);
        }
    } catch (Exception $ex) {
        $result["status"] = 'ko';
        $result["error_msg"] .= 'Error in accessing the ownership. ';
        $result["msg"] .= '\n error in accessing the ownership ';
        $result["log"] .= '\n error in accessing the ownership ' . $ex;
    }

    return $listCondDevice;
}

function getOwnerShipObject($token, $object, &$result) {
    $listCondDevice = "";
    $local_result = "";
    $mykeys = array();
    try {
        $url = $GLOBALS["ownershipURI"] . "ownership-api/v1/list/?type=" . $object . "&accessToken=" . $token;
        $local_result = file_get_contents($url);
        $result["log"] .= $local_result;

        if (strpos($http_response_header[0], '200') == true || strpos($http_response_header[0], '204') == true) {
            $lists = json_decode($local_result);
            for ($i = 0; $i < count($lists); $i++) {
                if (!isset($lists[$i]->deleted)) {
                    $org = substr($lists[$i]->elementId, 0, strpos($lists[$i]->elementId, ":"));
                    $name = substr($lists[$i]->elementId, strpos($lists[$i]->elementId, ":") + 1, strlen($lists[$i]->elementId));
                    $listCondDevice .= " (name = '" . $name . "' AND organization = '" . $org . "') ";
                    if ($i != count($lists) - 1)
                        $listCondDevice .= " OR ";
                    $mykeys[$lists[$i]->elementId] = array("owner" => $lists[$i]->username);
                    $result["username"] = $lists[$i]->username;
                }
            }
            $result["status"] = 'ok';
            $result["keys"] = $mykeys;
            $result["msg"] .= '\n identified ' . count($lists) . ' private objects \n';
            $result["log"] .= '\n identified ' . count($lists) . ' private objects \n';
        }
    } catch (Exception $ex) {
        $result["status"] = 'ko';
        $result["error_msg"] .= ' Error in accessing the ownership. ';
        $result["msg"] .= '\n error in accessing the ownership ';
        $result["log"] .= '\n error in accessing the ownership ' . $ex;
    }
    return $listCondDevice;
}

function checkOwnershipObject($token, $elementId, $elementType, &$result) {
    $toreturn = false;
    try {
        $url = $GLOBALS["ownershipURI"] . "ownership-api/v1/list/?elementId=" . urlencode($elementId) . "&type=" . urlencode($elementType) . "&accessToken=" . urlencode($token);
        $local_result = file_get_contents($url);

        if (strpos($http_response_header[0], '200') == true || strpos($http_response_header[0], '204') == true) {
            $lists = json_decode($local_result);
            for ($i = 0; $i < count($lists); $i++) {
                if (!isset($lists[$i]->deleted) &&
                        isset($lists[$i]->elementId) && ($lists[$i]->elementId == $elementId) &&
                        isset($lists[$i]->elementType) && ($lists[$i]->elementType == $elementType)
                ) {
                    $toreturn = true;
                }
            }
            $result["status"] = 'ok';
            $result["msg"] = '\n check ownership ' . $toreturn;
            $result["log"] = '\n check ownership ' . $toreturn;
        } else {
            $result["status"] = 'ko';
            $result["error_msg"] = 'Error in accessing the ownership. ';
            $result["msg"] = '\n error in accessing the ownership ';
            $result["log"] = '\n error in accessing the ownership. Returned code:  ' . $http_response_header[0];
        }
    } catch (Exception $ex) {
        $result["status"] = 'ko';
        $result["error_msg"] .= 'Error in accessing the ownership. ';
        $result["msg"] .= '\n error in accessing the ownership ';
        $result["log"] .= '\n error in accessing the ownership ' . $ex;
    }
    return $toreturn;
}

/*
  function generatelabels($link)
  {
  $query2 = "SELECT value_type FROM value_types ORDER BY value_type";
  $res = mysqli_query($link, $query2) or die(mysqli_error($link));
  $labels = array();
  if($res)
  {
  while($row = mysqli_fetch_assoc($res))
  {
  array_push($labels, $row["value_type"]);
  }
  }
  return $labels;
  }

  function generateunits($link)
  {
  $query2 = "SELECT DISTINCT value_unit_default FROM value_types ORDER BY value_unit_default";
  $res = mysqli_query($link, $query2) or die(mysqli_error($link));
  $labels = array();
  if($res)
  {
  while($row = mysqli_fetch_assoc($res))
  {
  array_push($labels, $row["value_unit_default"]);
  }
  }
  return $labels;
  }
 */

function retrieveFromDictionary($type, &$result) {

    $local_result = "";
    try {
        if (isset($GLOBALS["processLoaderURI"])) {
            $url = $GLOBALS["processLoaderURI"] . "dictionary/?type=" . $type;
        } else {
            $url = "default-" . $type . ".json";
        }
        $local_result = file_get_contents($url);
        $result["log"] .= $local_result;

        //TODO how to catch an 504
        if (($local_result !== FALSE) && (
                (!isset($GLOBALS["processLoaderURI"])) || //default scenario from file
                ((isset($GLOBALS["processLoaderURI"])) && (strpos($http_response_header[0], '200') == true || strpos($http_response_header[0], '204') == true))
                )) {
            $dictionary = json_decode($local_result);
            if ($dictionary->{'code'} == '200') {
                $result["status"] = 'ok';
                $result["content"] = $dictionary->{'content'};
                $result["msg"] .= '\n ok, returning dictionary';
                $result["log"] .= '\n ok, returning dictionary';
            } else {
                $result["status"] = 'ko';
                $result["error_msg"] = $dictionary{'result'};
                $result["msg"] .= '\n ko NOT returning dictionary';
                $result["log"] .= '\n ko NOT returning dictionary';
            }
        } else {
            $result["status"] = 'ko';
            $result["error_msg"] = " Dictionary NOT reacheable";
            $result["msg"] .= '\n ko dictionary not reacheable';
            $result["log"] .= '\n ko dictionary not reacheable';
        }
    } catch (Exception $ex) {
        $result["status"] = 'ko';
        $result["error_msg"] .= ' Error in accessing the dictionary. ';
        $result["msg"] .= '\n error in accessing the dictionary ';
        $result["log"] .= '\n error in accessing the dictionary ' . $ex;
    }
    return $result;
}

function retrieveAvailableStaticAttribute($subnature, &$result) {
    $local_result = "";
    if (!array_key_exists("msg", $result))
        $result["msg"] = "";
    if (!array_key_exists("log", $result))
        $result["log"] = "";
    try {
        $url = $GLOBALS["knowledgeBaseURI"] . "api/v1/iot/list-static-attr?subnature=" . $subnature;

        $local_result = file_get_contents($url);
        $result["log"] .= $local_result;

        //TODO how to catch an 504
        if (($local_result !== FALSE) && (strpos($http_response_header[0], '200') == true || strpos($http_response_header[0], '204') == true)) {
            $result["status"] = 'ok';
            $result["content"] = $local_result;
            $result["msg"] .= '\n ok, returning dictionary';
            $result["log"] .= '\n ok, returning dictionary';
        } else {
            $result["status"] = 'ko';
            $result["error_msg"] = " ServiceMap not reacheable NOT reacheable";
            $result["msg"] .= '\n ko SM not reacheable';
            $result["log"] .= '\n ko SM not reacheable';
        }
    } catch (Exception $ex) {
        $result["status"] = 'ko';
        $result["error_msg"] = ' Error in accessing the SM. ';
        $result["msg"] .= '\n error in accessing the SM ';
        $result["log"] .= '\n error in accessing the SM ' . $ex;
    }
    return $result;
}

/* * ***FUNCTIONS FOR THE REGISTRATION OF A DEVICE IN THE CONTEXT BROKER AND IN THE KNOWLEDGE BASE ****** */

function insert_ngsi($link, $name, $type, $contextbroker, $kind, $protocol, $format, $model, $latitude, $longitude, $visibility, $frequency,
        $listnewAttributes, $ip, $port, &$result, $service = "", $servicePath = "") {
    $res = "ok";
    $msg_orion = array();

    $msg_orion["id"] = $name;
    $msg_orion["type"] = $type;
    $msg_orion["latitude"] = array();
    $msg_orion["longitude"] = array();
    $msg_orion["latitude"]["value"] = $latitude;
    $msg_orion["longitude"]["value"] = $longitude;
    $msg_orion["latitude"]["type"] = "float";
    $msg_orion["longitude"]["type"] = "float";
    $a = 0;
    while ($a < count($listnewAttributes)) {
        $att = $listnewAttributes[$a];
        $msg_orion[$att->value_name] = array();
        $msg_orion[$att->value_name]["value"] = "";
        $msg_orion[$att->value_name]["type"] = $att->data_type;
        $a++;
    }
    $msg_orion["model"] = array();
    $msg_orion["model"]["value"] = $model;
    $msg_orion["model"]["type"] = "string";

    $url_orion = "http://$ip:$port/v2/entities/";

    try {
        // Setup cURL
        $ch = curl_init($url_orion);
        $authToken = 'OAuth 2.0 token here';

        $httpheader = array(
            'Authorization: ' . $authToken,
            'Content-Type: application/json');
        if ($service != "")
            array_push($httpheader, 'Fiware-Service: ' . $service);
        if ($servicePath != "")
            array_push($httpheader, 'Fiware-ServicePath: ' . $servicePath);

        curl_setopt_array($ch, array(
            CURLOPT_POST => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_HTTPHEADER => $httpheader,
            CURLOPT_POSTFIELDS => json_encode($msg_orion)
        ));

        $retries=0;
        $response_orion = FALSE;
        while($response_orion === FALSE && $retries<3){
        // Send the request
            if($retries!==0){
                usleep(10000);
            }
        $response_orion = curl_exec($ch);
            $retries++;
        }

        //echo("<script>console.log($response_orion);</script>");
        // Check for errors
        if ($response_orion === FALSE) {
            // die(curl_error($ch));
            $result["error_msg"] .= "Error in the connection with the ngsi context broker. Context Broker didn't respond.";
            $result["msg"] .= "\n error in the connection with the ngsi context broker. Context Broker didn't respond.";
            $result["log"] .= "\n error in the connection with the ngsi context broker. Context Broker didn't respond.";
            $res = 'ko';
            $q = "UPDATE devices SET is_in_broker = 'broker_didnt_respond' WHERE id = '$name'";
            mysqli_query($link, $q);
        } else {
            // Decode the response
            $responseData = json_decode($response_orion, TRUE);
            $result["status"] = 'ok';
            $result["msg"] .= '\n response from the ngsi context broker ';
            $result["log"] .= '\n response from the ngsi context broker ' . $response_orion;
            $res = 'ok';
            $q = "UPDATE devices SET is_in_broker = 'success' WHERE id = '$name'";
            mysqli_query($link, $q);
        }
    } catch (Exception $ex) {
        $result["status"] = 'ko';
        $result["error_msg"] .= ' Exception when communicating with the ngsi context broker. ';
        $result["msg"] .= ' Exception when communicating with the ngsi context broker ';
        $result["log"] .= ' Exception when communicating with the ngsi context broker ' . $ex->getMessage();
        $res = "ko";
        $q = "UPDATE devices SET is_in_broker = 'broker_exception' WHERE id = '$name'";
        mysqli_query($link, $q);
    }
    return $res;
}

function insert_mqtt($name, $type, $contextbroker, $kind, $protocol, $format, $latitude, $longitude, $visibility, $frequency,
        $listnewAttributes, $ip, $port, &$result) {
    return "ok";
}

function insert_amqp($name, $type, $contextbroker, $kind, $protocol, $format, $latitude, $longitude, $visibility, $frequency,
        $listnewAttributes, $ip, $port, &$result) {
    return "ok";
}

function canBeModified($name, $type, $contextbroker, $kind, $protocol, $format, $macaddress, $model, $producer, $latitude, $longitude,
        $visibility, $frequency, $listnewAttributes, &$result) {
    $error = false;
    if ($name == null || $name == "") {
        $error = true;
        $result["msg"] .= "\n id not specified";
        $result["error_msg"] .= " id not specified. ";
        $result["log"] .= "\n id not specified";
    }
    if ($contextbroker == null || $contextbroker == "") {
        $error = true;
        $result["msg"] .= "\n cb not specified";
        $result["error_msg"] .= "cb not specified. ";
        $result["log"] .= "\n cb not specified";
    }
    if ($type == null || $type == "") {
        $error = true;
        $result["msg"] .= "\n type not specified";
        $result["error_msg"] .= "type not specified. ";
        $result["log"] .= "\n type not specified";
    }
    if (!($kind == "sensor" || $kind == "actuator")) {
        $error = true;
        $result["msg"] .= "\n kind not specified";
        $result["error_msg"] .= "kind not specified. ";
        $result["log"] .= "\n kind not specified";
    }
    if ($latitude < -90 && $latitude > 90) {
        $error = true;
        $result["msg"] .= "\n latitude not correct ";
        $result["error_msg"] .= "latitude not correct. ";
        $result["log"] .= "\n latitude not correct ";
    }
    if ($longitude < -180 && $longitude > 180) {
        $error = true;
        $result["msg"] .= "\n longitude not correct ";
        $result["error_msg"] .= "longitude not correct. ";
        $result["log"] .= "\n longitude not correct ";
    }
    if (!($protocol == "ngsi" || $protocol == "mqtt" || $protocol == "amqp" || $protocol == "ngsi w/MultiService")) {
        $error = true;
        $result["msg"] .= "\n protocol not correct ";
        $result["error_msg"] .= "protocol not correct. ";
        $result["log"] .= "\n protocol not correct ";
    }
    if (count($listnewAttributes) == 0) {
        $error = true;
        $result["msg"] .= "\n at list one attribute";
        $result["error_msg"] .= " at least one attribute is required. ";
        $result["log"] .= "\n at list one attribute";
    }

    foreach ($listnewAttributes as $att) {
        if ($att["data_type"] == null || $att["data_type"] == "") {
            $error = true;
            $result["msg"] .= "\n data type for attribute $att[value_name] not specified";
            $result["error_msg"] .= " data type for attribute $att[value_name] not specified. ";
            $result["log"] .= "\n data type for attribute $att[value_name] not specified";
        }
        if ($att["value_unit"] == null || $att["value_unit"] == "") {
            $error = true;
            $result["msg"] .= "\n value unit for attribute $att[value_name] not specified";
            $result["error_msg"] .= " value unit for attribute $att[value_name] not specified. ";
            $result["log"] .= "\n value unit for attribute $att[value_name] not specified";
        }
        if ($att["value_type"] == null || $att["value_type"] == "") {
            $error = true;
            $result["msg"] .= "\n value type for attribute $att[value_name] not specified";
            $result["error_msg"] .= " value type for attribute $att[value_name] not specified. ";
            $result["log"] .= "\n value type for attribute $att[value_name] not specified";
        }
        if (!($att["healthiness_criteria"] == "refresh_rate" || $att["healthiness_criteria"] == "different_values" ||
                $att["healthiness_criteria"] == "within_bounds")) {
            $error = true;
        }
        if ($att["healthiness_criteria"] == "refresh_rate" && $att["healthiness_value"] == "") {
            $error = true;
            $result["msg"] .= "\n healthiness_criteria for attribute $att[value_name] not specified";
            $result["error_msg"] .= "healthiness_criteria for attribute $att[value_name] not specified. ";
            $result["log"] .= "\n healthiness_criteria for attribute $att[value_name] not specified";
        }
        // if ($att->healthiness_criteria=="different_values" && ($att->different_values=="" || !is_int($att->different_values))) 
//{$error=true;}
    }

    if ($error)
        return false;
    return true;
}

function canBeRegistered($name, $type, $contextbroker, $kind, $protocol, $format, $macaddress, $model, $producer, $latitude, $longitude,
        $visibility, $frequency, $listnewAttributes, $subnature, $staticAttr, &$result) {
    $error = false;
    if ($name == null || $name == "") {
        $error = true;
        $result["error_msg"] .= "id not specified. ";
        $result["msg"] .= "\n id not specified";
        $result["log"] .= "\n id not specified";
    }
    if ($contextbroker == null || $contextbroker == "") {
        $error = true;
        $result["error_msg"] .= "cb not specified. ";
        $result["msg"] .= "\n cb not specified";
        $result["log"] .= "\n cb not specified";
    }
    if ($type == null || $type == "") {
        $error = true;
        $result["error_msg"] .= "type not specified. ";
        $result["msg"] .= "\n type not specified";
        $result["log"] .= "\n type not specified";
    }
    if (!($kind == "sensor" || $kind == "actuator")) {
        $error = true;
        $result["error_msg"] .= "kind not specified. ";
        $result["msg"] .= "\n kind not specified";
        $result["log"] .= "\n kind not specified";
    }
    if ($latitude < -90 && $latitude > 90) {
        $error = true;
        $result["error_msg"] .= "latitude not correct. ";
        $result["msg"] .= "\n latitude not correct ";
        $result["log"] .= "\n latitude not correct ";
    }
    if ($longitude < -180 && $longitude > 180) {
        $error = true;
        $result["error_msg"] .= "longitude not correct. ";
        $result["msg"] .= "\n longitude not correct ";
        $result["log"] .= "\n longitude not correct ";
    }
    if (!($protocol == "ngsi" || $protocol == "mqtt" || $protocol == "amqp" || $protocol == "ngsi w/MultiService")) {
        $error = true;
        $result["error_msg"] .= "protocol not correct. ";
        $result["msg"] .= "\n protocol not correct ";
        $result["log"] .= "\n protocol not correct ";
    }
    if (count($listnewAttributes) == 0) {
        $error = true;
        $result["error_msg"] .= "at least one attribute must be added. ";
        $result["msg"] .= "\n at list one attribute";
        $result["log"] .= "\n at list one attribute";
    }

    foreach ($listnewAttributes as $att) {
        if ($att->data_type == null || $att->data_type == "") {
            $error = true;
            $result["error_msg"] .= "data type for attribute $att->value_name not specified. ";
            $result["msg"] .= "\n data type for attribute $att->value_name not specified";
            $result["log"] .= "\n data type for attribute $att->value_name not specified";
        }
        if ($att->value_unit == null || $att->value_unit == "") {
            $error = true;
            $result["error_msg"] .= "value unit for attribute $att->value_name not specified. ";
            $result["msg"] .= "\n value unit for attribute $att->value_name not specified";
            $result["log"] .= "\n value unit for attribute $att->value_name not specified";
        }
        if ($att->value_type == null || $att->value_type == "") {
            $error = true;
            $result["error_msg"] .= "value type for attribute $att->value_name not specified. ";
            $result["msg"] .= "\n value type for attribute $att->value_name not specified";
            $result["log"] .= "\n value type for attribute $att->value_name not specified";
        }
        if (!($att->healthiness_criteria == "refresh_rate" || $att->healthiness_criteria == "different_values" ||
                $att->healthiness_criteria == "within_bounds")) {
            $error = true;
            $result["error_msg"] .= "wrong healthiness_criteria. ";
            $result["msg"] .= "\n wrong healthiness_criteria";
            $result["log"] .= "\n wrong healthiness_criteria";
        }
        if ($att->healthiness_criteria == "refresh_rate" && $att->healthiness_value == "") {
            $error = true;
            $result["error_msg"] .= "healthiness_criteria for attribute $att->value_name not specified. ";
            $result["msg"] .= "\n healthiness_criteria for attribute $att->value_name not specified";
            $result["log"] .= "\n healthiness_criteria for attribute $att->value_name not specified";
        }
    }


    if ($error)
        return false;
    return true;
}

function registerKB($link, $name, $type, $contextbroker, $kind, $protocol, $format, $macaddress, $model, $producer, $latitude,
        $longitude, $visibility, $frequency, $listnewAttributes, $subnature, $staticAttributes, &$result, $shouldbeRegistered,
        $organization, $kbUrl = "", $service = "", $servicePath = "", $accessToken, $wktGeometry="",$hlt) {
    $result["status"] = 'ok';

    //vedere se questa la posso togliere visto che la chiamo in "insert_device()"
    if (canBeRegistered($name, $type, $contextbroker, $kind, $protocol, $format, $macaddress, $model, $producer, $latitude, $longitude,
                    $visibility, $frequency, $listnewAttributes, $subnature, $staticAttributes, $result)) {
        $query = "SELECT * from contextbroker WHERE name = '$contextbroker'";
        $r = mysqli_query($link, $query);
        if (!$r) {//row should also be NOT empty since enforcement has been already made in the api 
            $result["status"] = 'ko';
            $result["error_msg"] .= "Error in reading data from context broker.";
            $result["msg"] .= ' error in reading data from context broker ' . mysqli_error($link);
            $result["log"] .= ' error in reading data from context broker ' . mysqli_error($link) . $query;
            return 1;
        }
        $rowCB = mysqli_fetch_assoc($r);
        if ($rowCB["kind"] == 'external')
            $shouldbeRegistered = 'no';
        $ip = $rowCB["ip"];
        $port = $rowCB["port"];

        $msg = array();
        $msg["id"] = $name;
        if ($rowCB["protocol"] == "ngsi w/MultiService")
            $msg["id"] = $service . "." . $servicePath . "." . $name;
        $msg["type"] = $type;
        $msg["kind"] = $kind;
        $msg["protocol"] = $protocol;
        $msg["format"] = $format;
        $msg["macaddress"] = $macaddress;
        $msg["model"] = $model;
        $msg["producer"] = $producer;
        $msg["latitude"] = $latitude;
        $msg["longitude"] = $longitude;
        $msg["frequency"] = $frequency;
        $msg["organization"] = $organization;
        $msg["ownership"] = $visibility;
        $msg["subnature"] = $subnature;
        $msg["wktGeometry"] = $wktGeometry;
        $msg["highleveltype"] = $hlt;

        foreach (json_decode(stripcslashes($staticAttributes)) as $stAtt) {
            $msg[$stAtt[0]] = $stAtt[1];
        }

        $msg["broker"] = array();
        $msg["broker"]["name"] = $contextbroker;
        $msg["broker"]["type"] = $rowCB["protocol"];
        $msg["broker"]["ip"] = $rowCB["ip"];
        $msg["broker"]["port"] = $rowCB["port"];
        $msg["broker"]["login"] = ($rowCB["login"] == null) ? "" : $rowCB["login"];
        $msg["broker"]["password"] = ($rowCB["password"] == null) ? "" : $rowCB["password"];
        $msg["broker"]["latitude"] = $rowCB["latitude"];
        $msg["broker"]["longitude"] = $rowCB["longitude"];
        $msg["broker"]["created"] = $rowCB["created"];

        $myAttrs = array();
        $i = 1;
        foreach ($listnewAttributes as $att) {
            $myatt = array();
            $myatt["value_name"] = $att->value_name;
            $myatt["data_type"] = $att->data_type;
            $myatt["value_type"] = $att->value_type;
            $myatt["value_unit"] = $att->value_unit;
            $myatt["healthiness_criteria"] = $att->healthiness_criteria;
            if ($att->healthiness_criteria == "refresh_rate")
                $myatt["value_refresh_rate"] = $att->healthiness_value;
            // to be fixed
            //else if ($att["healthiness_criteria"]=="different_values")
            // $myatt["different_values"]=$att["different_values"];
            //else $myatt["value_bounds"]=$att["value_bounds"];
            $myatt["order"] = $i++;
            $myatt["realtime"]= $att->real_time_flag;
            $myAttrs[] = $myatt;
        }
        $msg["attributes"] = $myAttrs;

        $encoda = json_encode($msg, JSON_UNESCAPED_SLASHES);
        $result["log"] .= "\n Sending to insertKB:" . $encoda;
        $encoda = str_replace('\\\\', '\\\\u005C', $encoda);
        $encoda = str_replace('\"', '\\\\u0022', $encoda);
        $result["log"] .= "\n Sending to insertKB (after pulizia):" . $encoda;

        try {
            if ($kbUrl == "")
                $url = $_SESSION['kbUrl'] . "iot/insert";
            else
                $url = $kbUrl . "iot/insert";
            $options = array(
                'http' => array(
                    'header' => "Content-Type: application/json;charset=utf-8",
                    'header' => "Access-Control-Allow-Origin: *",
                    'header' => "Authorization: Bearer " . $accessToken,
                    'method' => 'POST',
                    'content' => $encoda,
                    'timeout' => 60,
                    'ignore_errors' => true
                )
            );
            $retries=0;
            $status="0";
            while($status !== "200" && $retries < 3) {
                if($retries !== 0){
                    usleep(10000);
                }
            $context = stream_context_create($options);
            $local_result = @file_get_contents($url, false, $context);

            $status_line = $http_response_header[0];
            preg_match('{HTTP\/\S*\s(\d{3})}', $status_line, $match);
            $status = $match[1];
                $retries++;
            }
            if ($status !== "200") {
                $result["status"] = 'ko';
                $result["content"] = "";
                $result["msg"] .= "\n no URI has been generated by the KB";
                $result["log"] .= "\n no URI has been generated by the KB. Error: " . $status . " " . $local_result;
                $res = 'ko'; //uri has not been generated

                $q= "select is_in_kb from devices where id ='$name';";
                $r = mysqli_query($link, $q);
                $row = mysqli_fetch_assoc($r);
                if($row['is_in_kb'] == null ) {
                    $q = "UPDATE devices SET is_in_kb = 'no_uri_generated_by_kb' WHERE id = '$name'";
                    $r = mysqli_query($link, $q);
                }
            } else {
                $result["status"] = 'ok';
                $result["content"] = $local_result;
                $result["msg"] .= "\n an URI has been generated by the KB: " . $local_result . " from: " . $url . " organization: " . $organization;
                $result["log"] .= "\n an URI has been generated by the KB: " . $local_result . " from: " . $url . " organization: " . $organization;
                $q = "UPDATE devices SET is_in_kb = 'success' WHERE id = '$name'";
                $r = mysqli_query($link, $q);

                // registration of the device in the corresponding context broker
                if (!isset($shouldbeRegistered) || (isset($shouldbeRegistered) && $shouldbeRegistered == 'yes')) {
                    switch ($protocol) {
                        case "ngsi":
                            $res = insert_ngsi($link,$name, $type, $contextbroker, $kind, $protocol, $format, $model, $latitude, $longitude,
                                    $visibility, $frequency, $listnewAttributes, $ip, $port, $result);
                            break;
                        case "ngsi w/MultiService":
                            $res = insert_ngsi($link,$name, $type, $contextbroker, $kind, $protocol, $format, $model, $latitude, $longitude,
                                    $visibility, $frequency, $listnewAttributes, $ip, $port, $result, $service, $servicePath);
                        case "mqtt":
                            $res = insert_mqtt($name, $type, $contextbroker, $kind, $protocol, $format, $latitude, $longitude,
                                    $visibility, $frequency, $listnewAttributes, $ip, $port, $result);
                            break;
                        case "amqp":
                            $res = insert_amqp($name, $type, $contextbroker, $kind, $protocol, $format, $latitude, $longitude,
                                    $visibility, $frequency, $listnewAttributes, $ip, $port, $result);
                            break;
                    }
                } else {
                    $res = 'no'; //should not be registered
                }
            }
        } catch (Exception $ex) {
            $result["status"] = 'ko';
            $result["error_msg"] .= 'Error in connecting with KB. ';
            $result["msg"] .= '\n error in connecting with KB ';
            $result["log"] .= '\n error in connecting with KB ' . $ex->getMessage();
            $res = 'ko'; //uri has not been generated
            $q= "select is_in_kb from devices where id ='$name';";
            $r = mysqli_query($link, $q);
            $row = mysqli_fetch_assoc($r);
            if($row['is_in_kb'] == null ) {
                $q = "UPDATE devices SET is_in_kb = 'error_connecting_to_kb' WHERE id = '$name'";
                mysqli_query($link, $q);
            }
        }

        if ($res == "ok") {
            $result["msg"] .= "\n ok registration in the context broker";
            $result["log"] .= "\n ok registration in the context broker";
        } elseif ($res == "ko") {
            $result["status"] = 'ko';
            $result["error_msg"] .= "No registration in the context broker. Error in registerKB() ";
            $result["msg"] .= "\n no registration in the context broker";
            $result["log"] .= "\n no registration in the context broker";

            $q= "select is_in_broker from devices where id ='$name';";
            $r = mysqli_query($link, $q);
            $row = mysqli_fetch_assoc($r);
            if($row['is_in_broker'] == null ) {
                $q = "UPDATE devices SET is_in_broker = 'error_in_registration_broker' WHERE id = '$name'";
                $r = mysqli_query($link, $q);
            }

        } else {// the value is no -- no registration in the context broker
            $result["status"] = 'ok';
            $result["msg"] .= "\n no registration in the context broker is required";
            $result["log"] .= "\n no registration in the context broker is required";
        }
        return 1;
    } else {
        $result["error_msg"] .= "Error in the validation w.r.t. the KB. ";
        $result["msg"] .= "\n error in the validation w.r.t. the KB";
        $result["log"] .= "\n error in the validation w.r.t. the KB";
        $result["status"] = 'ko';
        $q = "UPDATE devices SET is_in_kb = 'error_wrt_kb' WHERE id = '$name'";
        mysqli_query($link, $q);
        return 1;
    }
}

// end of function registerKB
// ****FUNCTIONS FOR THE MODIFICATION OF THE REGISTRATION OF A DEVICE IN THE KNOWLEDGE BASE AND IN THE CONTEXT BROKER ****************** 

function update_ngsi($link,$name, $type, $contextbroker, $kind, $protocol, $format, $model, $latitude, $longitude, $visibility, $frequency,
        $listnewAttributes, $deletedAttributes, $ip, $port, $uri, $staticAttributes, &$result, $service = "", $servicePath = "") {
    $res = "ok";
    $msg_orion = array();

    // $msg_orion["id"]= $name;
    // $msg_orion["type"]= $type;
    if (!isMobile($staticAttributes)) {
        $msg_orion["latitude"] = array();
        $msg_orion["longitude"] = array();
        $msg_orion["latitude"]["value"] = $latitude;
        $msg_orion["longitude"]["value"] = $longitude;
        $msg_orion["latitude"]["type"] = "float";
        $msg_orion["longitude"]["type"] = "float";
    }
    $a = 0;
    while ($a < count($listnewAttributes)) {
        $att = $listnewAttributes[$a];
        if (is_object($att)) {
            $msg_orion[$att->value_name] = array();
            $msg_orion[$att->value_name]["value"] = "";
            $msg_orion[$att->value_name]["type"] = $att->data_type;
        } else {
            $msg_orion[$att["value_name"]] = array();
            $msg_orion[$att["value_name"]]["value"] = "";
            $msg_orion[$att["value_name"]]["type"] = $att["data_type"];
        }
        $a++;
    }
    if ($model != null && $model != "") {
        $msg_orion["model"] = array();
        $msg_orion["model"]["value"] = $model;
        $msg_orion["model"]["type"] = "string";
    }
    if ($protocol == "ngsi w/MultiService") {
        // get the name from id
        $name = explode(".", $name)[2];
    }

    $url_orion = "http://$ip:$port/v2/entities/$name/attrs";
    try {
        // Setup cURL
        $ch = curl_init($url_orion);
        $authToken = 'OAuth 2.0 token here';

        $httpheader = array(
            'Authorization: ' . $authToken,
            'Content-Type: application/json');
        if ($service != "")
            array_push($httpheader, 'Fiware-Service: ' . $service);
        if ($servicePath != "")
            array_push($httpheader, 'Fiware-ServicePath: ' . $servicePath);

        curl_setopt_array($ch, array(
            CURLOPT_POST => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_HTTPHEADER => $httpheader,
            CURLOPT_POSTFIELDS => json_encode($msg_orion)
        ));

        $response_orion = curl_exec($ch);

        // Check for errors
        if ($response_orion === FALSE) {
            $result["error_msg"] .= "Error in the connection with the ngsi context broker. ";
            $result["msg"] .= "\n error in the connection with the ngsi context broker";
            $result["log"] .= "\n error in the connection with the ngsi context broker" . curl_error($ch);
            $res = 'ko';
            $q = "UPDATE devices SET is_in_broker = 'error_in_connecting_ngsi' WHERE id = '$name'";
            $r = mysqli_query($link, $q);
        } else {
            //eventually remove attributes TODO uniform with above
            $b = 0;
            while ($b < count($deletedAttributes)) {
                $att = $deletedAttributes[$b];
                // Setup cURL
                $ch = curl_init($url_orion . "/" . $att->value_name);

                if ($service != "")
                    array_push($httpheader, 'Fiware-Service: ' . $service);
                if ($servicePath != "")
                    array_push($httpheader, 'Fiware-ServicePath: ' . $servicePath);

                curl_setopt_array($ch, array(
                    CURLOPT_CUSTOMREQUEST => "DELETE",
                    CURLOPT_RETURNTRANSFER => TRUE
                ));

                $response_orion = curl_exec($ch);

                // Check for errors
                if ($response_orion === FALSE) {
                    $result["error_msg"] .= "Error in the DELETE connection with the ngsi context broker. ";
                    $result["msg"] .= "\n error in the DELETE connection with the ngsi context broker";
                    $result["log"] .= "\n error in the DELETE connection with the ngsi context broker" . curl_error($ch);
                    $res = 'ko';
                    $q = "UPDATE devices SET is_in_broker = 'error_in_connecting_ngsi' WHERE id = '$name'";
                    $r = mysqli_query($link, $q);
                    break;
                }
                $b++;
            }

            if ($response_orion !== FALSE) {
                $result["status"] = 'ok';
                $result["msg"] .= "\n response from the ngsi context broker ";
                $result["log"] .= "\n response from the ngsi context broker \"" . $response_orion . "\"";
                $res = 'ok';
                $q = "UPDATE devices SET is_in_broker = 'success' WHERE id = '$name'";
                $r = mysqli_query($link, $q);
            }
        }
    } catch (Exception $ex) {
        $result["status"] = 'ko';
        $result["error_msg"] .= ' Error in connecting with the ngsi context broker. ';
        $result["msg"] .= ' error in connecting with the ngsi context broker ';
        $result["log"] .= ' error in connecting with the ngsi context broker ' . $ex;
        $res = "ko";
    }
    return $res;
}

function update_mqtt($name, $type, $contextbroker, $kind, $protocol, $format, $latitude, $longitude, $visibility, $frequency,
        $listnewAttributes, $ip, $port, $uri, &$result) {
    return "ok";
}

function update_amqp($name, $type, $contextbroker, $kind, $protocol, $format, $latitude, $longitude, $visibility, $frequency,
        $listnewAttributes, $ip, $port, $uri, &$result) {
    return "ok";
}

function updateKB($link, $name, $type, $contextbroker, $kind, $protocol, $format, $macaddress, $model, $producer, $latitude, $longitude,
        $visibility, $frequency, $attributes, $deletedAttributes, $uri, $organization, $subnature, $staticAttributes, &$result, $service = "",
        $servicePath = "", $kbUrl = "", $accessToken,$hlt,$wktGeometry) {
    $result["status"] = 'ok';

    if (canBeRegistered($name, $type, $contextbroker, $kind, $protocol, $format, $macaddress, $model, $producer, $latitude, $longitude,
                    $visibility, $frequency, $attributes, $subnature, $staticAttributes, $result)) {
        $query = "SELECT * from contextbroker WHERE name = '$contextbroker'";
        $r = mysqli_query($link, $query);
        if (!$r) { //row should also be NOT empty since enforcement has been already made in the api
            $result["status"] = 'ko';
            $result["error_msg"] .= 'Error in reading data from context broker. ';
            $result["msg"] .= ' error in reading data from context broker ' . mysqli_error($link);
            $result["log"] .= ' error in reading data from context broker ' . mysqli_error($link) . $query;
            return 1;
        }

        $rowCB = mysqli_fetch_assoc($r);
        $ip = $rowCB["ip"];
        $port = $rowCB["port"];

        $msg = array();

        $msg["id"] = $name; //TODO
        //should be update in case of MULTISERVICE???
        $msg["uri"] = $uri;
        $msg["type"] = $type;
        $msg["kind"] = $kind;
        $msg["protocol"] = $protocol;
        $msg["format"] = $format;
        $msg["macaddress"] = $macaddress;
        $msg["model"] = $model;
        $msg["producer"] = $producer;
        if (!isMobile($staticAttributes)) {  //on update device, if the mobile is in mobility, dont't override is position on CB!!!!
            $msg["latitude"] = $latitude;
            $msg["longitude"] = $longitude;
        }
        $msg["frequency"] = $frequency;
        $msg["organization"] = $organization;
        $msg["ownership"] = $visibility;
        $msg["broker"] = array();
        $msg["broker"]["name"] = $contextbroker;
        $msg["broker"]["type"] = $rowCB["protocol"];
        $msg["broker"]["ip"] = $rowCB["ip"];
        $msg["broker"]["port"] = $rowCB["port"];
        $msg["broker"]["login"] = ($rowCB["login"] == null) ? "" : $rowCB["login"];
        $msg["broker"]["password"] = ($rowCB["password"] == null) ? "" : $rowCB["password"];
        $msg["broker"]["latitude"] = $rowCB["latitude"];
        $msg["broker"]["longitude"] = $rowCB["longitude"];
        $msg["broker"]["created"] = $rowCB["created"];
        $msg["subnature"] = $subnature;
        $msg["highleveltype"] = $hlt;
        $msg["wktGeometry"] = $wktGeometry;

        foreach (json_decode(stripcslashes($staticAttributes)) as $stAtt) {
            $msg[$stAtt[0]] = $stAtt[1];
        }

        $myAttrs = array();
        $i = 1;
        foreach ($attributes as $att) {
            $myatt = array();
            $myatt["value_name"] = $att->value_name;
            $myatt["data_type"] = $att->data_type;
            $myatt["value_type"] = $att->value_type;
            $myatt["value_unit"] = $att->value_unit;
            $myatt["healthiness_criteria"] = $att->healthiness_criteria;
            if ($att->healthiness_criteria == "refresh_rate")
                $myatt["value_refresh_rate"] = $att->healthiness_value;
            else if ($att->healthiness_criteria == "different_values")
                $myatt["different_values"] = $att->different_values;
            else
                $myatt["value_bounds"] = $att->value_bounds;
            $myatt["order"] = $i++;
            $myatt["realtime"]= $att->real_time_flag;
            $myAttrs[] = $myatt;
        }
        $msg["attributes"] = $myAttrs;
        $encoda = json_encode($msg, JSON_UNESCAPED_SLASHES);
        $result["log"] .= "\n Sending to updateKB:" . $encoda;
        $encoda = str_replace('\\\\', '\\\\u005C', $encoda);
        $encoda = str_replace('\"', '\\\\u0022', $encoda);
        $result["log"] .= "\n Sending to updateKB (after pulizia):" . $encoda;
        try {
            if ($kbUrl == "")
                $url = $_SESSION['kbUrl'] . "iot/insert";
            else
                $url = $kbUrl . "iot/insert";

            $options = array(
                'http' => array(
                    'header' => "Content-Type: application/json;charset=utf-8",
                    'header' => "Access-Control-Allow-Origin: *",
                    'header' => "Authorization: Bearer " . $accessToken,
                    'method' => 'POST',
                    'content' => $encoda,
                    'timeout' => 30,
                    'ignore_errors' => true
                )
            );
            $context = stream_context_create($options);
            $local_result = @file_get_contents($url, false, $context);

            $status_line = $http_response_header[0];
            preg_match('{HTTP\/\S*\s(\d{3})}', $status_line, $match);
            $status = $match[1];

            if ($status !== "200") {
                $result["status"] = 'ko';
                $result["content"] = "";
                $result["msg"] = "\n no URI has been generated by the KB";
                $result["log"] .= "\n no URI has been generated by the KB. Error: " . $status . " " . $local_result;
                $res = 'ko'; //uri has not been generated
                $q = "UPDATE devices SET is_in_kb = 'no_uri_generated_by_kb' WHERE id = '$name'";
                $r = mysqli_query($link, $q);
            } else {
                $result["status"] = 'ok';
                $result["content"] = $local_result;
                $result["msg"] = "\n an URI has been generated by the KB";
                $result["log"] .= "\n an URI has been generated by the KB" . $local_result;

                $q = "UPDATE devices SET is_in_kb = 'success' WHERE id = '$name'";
                $r = mysqli_query($link, $q);

                /* update of the device in the corresponding context broker */
                if ($rowCB["kind"] != 'external') {
                    switch ($protocol) {
                        case "ngsi":
                            $res = update_ngsi($link,$name, $type, $contextbroker, $kind, $protocol, $format, $model, $latitude, $longitude, $visibility, $frequency,
                                    $attributes, $deletedAttributes, $ip, $port, $uri, $staticAttributes, $result);
                            break;
                        case "ngsi w/MultiService":
                            $res = update_ngsi($link,$name, $type, $contextbroker, $kind, $protocol, $format, $model, $latitude, $longitude, $visibility, $frequency,
                                    $attributes, $deletedAttributes, $ip, $port, $uri, $staticAttributes, $result, $service, $servicePath);
                            break;
                        case "mqtt":
                            $res = update_mqtt($name, $type, $contextbroker, $kind, $protocol, $format, $latitude, $longitude, $visibility,
                                    $frequency, $attributes, $ip, $port, $uri, $result);
                            break;
                        case "amqp":
                            $res = update_amqp($name, $type, $contextbroker, $kind, $protocol, $format, $latitude, $longitude, $visibility,
                                    $frequency, $attributes, $ip, $port, $uri, $result);
                            break;
                    }
                } else {
                    $res = 'no';
                }
            }
        } catch (Exception $ex) {
            $result["status"] = 'ko';
            $result["error_msg"] .= ' Error in connecting with KB. ';
            $result["msg"] .= ' error in connecting with KB ';
            $result["log"] .= ' error in connecting with KB ' . $ex;
            $res = 'ko'; //uri has not been generated
            $q = "UPDATE devices SET is_in_kb = 'error_connecting_to_kb' WHERE id = '$name'";
            $r = mysqli_query($link, $q);
        }

        if ($res == "ok") {
            $result["msg"] .= "\n ok updated in the context broker";
            $result["log"] .= "\n ok updated in the context broker";
            $q = "UPDATE devices SET is_in_broker = 'success' WHERE id = '$name'";
            $r = mysqli_query($link, $q);
        } elseif ($res == "ko") {
            $result["status"] = 'ko';
            $result["error_msg"] .= "Error in update the context broker. ";
            $result["msg"] .= "\n Error in update the context broker";
            $result["log"] .= "\n no update in the context broker";
            $q = "UPDATE devices SET is_in_broker = 'error_updating_broker' WHERE id = '$name'";
            $r = mysqli_query($link, $q);
        } else { // the value is no -- no registration in the context broker
            $result["msg"] .= "\n context broker external, not updated";
            $result["log"] .= "\n context broker external, not updated";
        }
    } else {
        $result["error_msg"] .= "Error in the validation w.r.t. the KB. ";
        $result["msg"] .= "\n error in the validation w.r.t. the KB";
        $result["log"] .= "\n error in the validation w.r.t. the KB";
        $result["status"] = 'ko';
        $q = "UPDATE devices SET is_in_kb = 'error_wrt_kb' WHERE id = '$name'";
        $r = mysqli_query($link, $q);
    }
    return 1;
}

// end of function updateKB

/* * ***FUNCTIONS FOR THE DELETION OF THE REGISTRATION OF A DEVICE IN THE KNOWLEDGE BASE AND IN THE CONTEXT BROKER ****************** */

function delete_ngsi($name, $type, $contextbroker, $kind, $protocol, $format, $model, $latitude, $longitude, $visibility, $frequency,
        $listnewAttributes, $ip, $port, $uri, &$result, $service = "", $servicePath = "") {
    if ($protocol == "ngsi w/MultiService") {
        // get the name from id
        $name = explode(".", $name)[2];
    }

    $res = "ok";
    $url_orion = "http://$ip:$port/v2/entities/$name";

    try {
        // Setup cURL
        $ch = curl_init($url_orion);
        $authToken = 'OAuth 2.0 token here';

        $httpheader = array(
            'Authorization: ' . $authToken
        );
        if ($service != "")
            array_push($httpheader, 'Fiware-Service: ' . $service);
        if ($servicePath != "")
            array_push($httpheader, 'Fiware-ServicePath: ' . $servicePath);


        curl_setopt_array($ch, array(
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_HTTPHEADER => $httpheader,
                // CURLOPT_POSTFIELDS => json_encode($msg_orion)
        ));

        // Send the request
        $response_orion = curl_exec($ch);

        // Check for errors
        if ($response_orion === FALSE) {
            // die(curl_error($ch));
            $result["msg"] .= "\n error in the connection with the ngsi context broker";
            $result["log"] .= "\n error in the connection with the ngsi context broker";
            $res = 'ko';
        } else {
            // Decode the response
            $responseData = json_decode($response_orion, TRUE);
            $result["status"] = 'ok';
            $result["msg"] .= "\n response from the ngsi context broker ";
            $result["log"] .= "\n response from the ngsi context broker \"" . $response_orion . "\"";
            $res = 'ok';
        }
    } catch (Exception $ex) {
        $result["status"] = 'ko';
        $result["error_msg"] .= 'Error in connecting with the ngsi context broker. ';
        $result["msg"] .= ' error in connecting with the ngsi context broker ';
        $result["log"] .= ' error in connecting with the ngsi context broker ' . $ex;
        $res = "ko";
    }
    return $res;
}

function delete_mqtt($name, $type, $contextbroker, $kind, $protocol, $format, $latitude, $longitude, $visibility, $frequency,
        $listnewAttributes, $ip, $port, $uri, &$result) {
    return "ok";
}

function delete_amqp($name, $type, $contextbroker, $kind, $protocol, $format, $latitude, $longitude, $visibility, $frequency,
        $listnewAttributes, $ip, $port, $uri, &$result) {
    return "ok";
}

function delete_from_opensearch($serviceUri, $organization, &$result) {
    $index = $GLOBALS['openSearchDeviceStateIndex'];
    if(!$index) {        
        $res = $result["status"] = 'ko';
        $result["msg"] .= "\n error no openSearchDeviceStateIndex defined, cannot delete $serviceUri from opensearch";
        $result["log"] .= "\n error no openSearchDeviceStateIndex defined, cannot delete $serviceUri from opensearch";
} else {
        $res = delete_from_opensearch_index($serviceUri, $index, $result);
        if($res == 'ok') {
            $index = $GLOBALS['openSearchFullDeviceStateIndex_'.$organization];
            if(!$index) {
                $index = $GLOBALS['openSearchFullDeviceStateIndex'];
                if(!$index) {
                    $res = $result["status"] = 'ok';
                    $result["msg"] .= "\n no openSearchFullDeviceStateIndex defined, cannot delete $serviceUri from opensearch";
                    $result["log"] .= "\n no openSearchFullDeviceStateIndex defined, cannot delete $serviceUri from opensearch";        
                }
            } 
            if($index) {
                $res = delete_from_opensearch_index($serviceUri, $index, $result);
            }
        }
    }
    return $res;
}

function delete_from_opensearch_index($serviceUri, $index, &$result) {
    $res = "ok";

    $url_opensearch = "https://".$GLOBALS['openSearchHostIP'].':'.$GLOBALS['openSearchPort'].'/'.$index.'/_delete_by_query';

    $username = $GLOBALS['openSearchUser'];
    $password = $GLOBALS['openSearchPwd'];
    try {
        // Setup cURL
        $ch = curl_init($url_opensearch);

        $query = '{
              "query": {
                "bool": {
                  "must": [
                    { "terms": {"serviceUri.keyword": ["' . $serviceUri . '"]} }
                  ]
                }
              }
            }';

        curl_setopt_array($ch, array(             
            //switches the request type from get to post
            CURLOPT_POST=>true,
             
            //attach the encoded string in the post field using CURLOPT_POSTFIELDS
            CURLOPT_POSTFIELDS=>$query,
             
            //setting curl option RETURNTRANSFER to true 
            //so that it returns the response
            //instead of outputting it 
            CURLOPT_RETURNTRANSFER=>true,
             
            //Using the CURLOPT_HTTPHEADER set the Content-Type to application/json
            CURLOPT_HTTPHEADER=>array('Content-Type:application/json'),

            CURLOPT_SSL_VERIFYPEER=> false,
            CURLOPT_SSL_VERIFYHOST=> false
        ));

        if($username) {
            curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        }

        // Send the request
        $response_opensearch = curl_exec($ch);

        // Check for errors
        if ($response_opensearch === FALSE) {
            // die(curl_error($ch));
            $result["msg"] .= "\n error sending to $url_opensearch : ".curl_error($ch);
            $result["log"] .= "\n error sending to $url_opensearch : ".curl_error($ch);
            $res = 'ko';
        } else {
            // Decode the response
            //$responseData = json_decode($response_opensearch, TRUE);
            $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if($http_status == 200) {
                $result["status"] = 'ok';
                $result["msg"] .= "\n response from opensearch ";
                $result["log"] .= "\n response from opensearch $url_opensearch " . $response_opensearch;
                $res = 'ok';
            } else {
                $result["status"] = 'ko';
                $result["msg"] .= "\n error response $http_status from opensearch deleting $serviceUri ";
                $result["log"] .= "\n error response $url_opensearch $http_status from opensearch deleting $serviceUri " . $response_opensearch;
                $res = 'ko';
            }
        }
    } catch (Exception $ex) {
        $result["status"] = 'ko';
        $result["error_msg"] .= " Error in connecting with opensearch. ";
        $result["msg"] .= " Error in connecting with opensearch. ";
        $result["log"] .= " Error in connecting with opensearch. " . $ex;
        $res = "ko";
    }
    return $res;
}

function deleteKB($link, $name, $contextbroker, $kbUrl = "", &$result, $service = "", $servicePath = "", $accessToken) {
    $res = $result["status"] = 'ok';
    
    $listnewAttributes = generateAttributes($link, $name, $contextbroker);

    $query = "SELECT d.organization, d.uri, d.id, d.devicetype AS entityType, d.kind, d.format, d.macaddress, d.model, d.producer, d.protocol, d.longitude,d.subnature, d.static_attributes, 
		d.latitude, d.visibility, d.frequency, d.service, d.servicePath,d.hlt,d.wktGeometry, cb.name, cb.protocol as type, cb.ip, cb.port, cb.login, cb.password, cb.latitude as cblatitude, 
		cb.longitude as cblongitude, cb.created, cb.kind as cbkind FROM devices d JOIN contextbroker cb ON d.contextBroker = cb.name WHERE d.deleted is null and 
		d.contextBroker='$contextbroker' and d.id='$name';";

    $r_init = mysqli_query($link, $query);

    if (!$r_init) { //row should also be NOT empty since enforcement has been already made in the api
        $result["status"] = 'ko';
        $result["error_msg"] .= 'Error in reading data from context broker and device ' . mysqli_error($link);
        $result["msg"] .= '\n error in reading data from context broker and device ' . mysqli_error($link);
        $result["log"] .= '\n error in reading data from context broker and device ' . mysqli_error($link) . $query;
        return 1;
    }

    $row = mysqli_fetch_assoc($r_init);
    $name = $row["id"];
    $organization = $row["organization"];
    $type = $row["entityType"];
    $kind = $row["kind"];
    $protocol = $row["protocol"];
    $format = $row["format"];
    $macaddress = $row["macaddress"];
    $model = $row["model"];
    $producer = $row["producer"];
    $latitude = $row["latitude"];
    $longitude = $row["longitude"];
    $visibility = $row['visibility'];
    $frequency = $row['frequency'];
    $ip = $row["ip"];
    $port = $row["port"];
    $uri = $row["uri"];
    $subnature = $row["subnature"];
    $staticAttributes = $row["static_attributes"];
    $hlt=$row["hlt"];
    $wktGeometry=$row["wktGeometry"];

    $result["msg"] = "$name, $type, $contextbroker, $kind, $protocol, $format, $macaddress, $model, $producer, $latitude, $longitude, 
		$visibility, $frequency," . count($listnewAttributes);

    if (canBeModified($name, $type, $contextbroker, $kind, $protocol, $format, $macaddress, $model, $producer, $latitude, $longitude,
                    $visibility, $frequency, $listnewAttributes, $result)) {
        /* msg for the Knowledge base + registration on the KB */
        $msg = array();
        $msg["id"] = $name;
        if ($protocol == "ngsi w/MultiService")
            $msg["id"] = $service . "." . $servicePath . "." . $name;
        $msg["type"] = $type;
        $msg["kind"] = $kind;
        $msg["protocol"] = $protocol;
        $msg["format"] = $format;
        $msg["macaddress"] = $macaddress;
        $msg["model"] = $model;
        $msg["producer"] = $producer;
        $msg["latitude"] = $latitude;
        $msg["longitude"] = $longitude;
        $msg["frequency"] = $frequency;
        $msg["organization"] = $organization;
        $msg["uri"] = $uri;
        $msg["ownership"] = $visibility;
        $msg["broker"] = array();
        $msg["broker"]["name"] = $contextbroker;
        $msg["broker"]["type"] = $row["protocol"];
        $msg["broker"]["ip"] = $row["ip"];
        $msg["broker"]["port"] = $row["port"];
        $msg["broker"]["login"] = ($row["login"] == null) ? "" : $row["login"];
        $msg["broker"]["password"] = ($row["password"] == null) ? "" : $row["password"];
        $msg["broker"]["latitude"] = $row["cblatitude"];
        $msg["broker"]["longitude"] = $row["cblongitude"];
        $msg["broker"]["created"] = $row["created"];
        $msg["subnature"] = $subnature;
        $msg["highleveltype"]=$hlt;
        $msg["wktGeometry"]=$wktGeometry;

        $myAttrs = array();
        $i = 1;
        foreach ($listnewAttributes as $att) {
            $myatt = array();
            $myatt["value_name"] = $att["value_name"];
            $myatt["data_type"] = $att["data_type"];
            $myatt["value_type"] = $att["value_type"];
            $myatt["value_unit"] = $att["value_unit"];
            $myatt["healthiness_criteria"] = $att["healthiness_criteria"];
            if ($att["healthiness_criteria"] == "refresh_rate")
                $myatt["value_refresh_rate"] = $att["healthiness_value"];
            else if ($att["healthiness_criteria"] == "different_values")
                $myatt["different_values"] = $att["healthiness_value"];
            else
                $myatt["value_bounds"] = $att["healthiness_value"];
            $myatt["order"] = $att["order"];
            $myAttrs[] = $myatt;
        }
        $msg["attributes"] = $myAttrs;

        foreach (json_decode(stripcslashes($staticAttributes)) as $stAtt) {
            $msg[$stAtt[0]] = $stAtt[1];
        }

        $encoda = json_encode($msg, JSON_UNESCAPED_SLASHES);
        $result["log"] .= "\n Deleting to insertKB:" . $encoda;
        $encoda = str_replace('\\\\', '\\\\u005C', $encoda);
        $encoda = str_replace('\"', '\\\\u0022', $encoda);
        $result["log"] .= "\n Deleting to insertKB (after pulizia):" . $encoda;

        try {
            if ($kbUrl == "") {
                $url = $_SESSION['kbUrl'] . "iot/delete";
            } else {
                $url = $kbUrl . "iot/delete";
            }

            $options = array(
                'http' => array(
                    'header' => "Content-Type: application/json;charset=utf-8",
                    'header' => "Access-Control-Allow-Origin: *",
                    'header' => "Authorization: Bearer " . $accessToken,
                    'method' => 'POST',
                    'content' => $encoda,
                    'timeout' => 30,
                    'ignore_errors' => true
                )
            );
            $context = stream_context_create($options);
            $local_result = @file_get_contents($url, false, $context);

            $status_line = $http_response_header[0];
            preg_match('{HTTP\/\S*\s(\d{3})}', $status_line, $match);
            $status = $match[1];

            if ($status !== "200") {
                $result["status"] = 'ko';
                $result["content"] = "";
                $result["msg"] .= "\n no URI has been generated by the KB";
                $result["log"] .= "\n no URI has been generated by the KB. Error: " . $status . " " . $local_result;
                $res = 'ko'; //uri has not been generated
            } else {
                $result["status"] = 'ok';
                $result["content"] = $local_result;
                // information to be passed to the interface
                $result["visibility"] = $visibility;
                if ($result["content"] == null)
                    $result["active"] = false;
                else
                    $result["active"] = true;
                // end of information to be passed to the interface
                $result["msg"] .= "\n the device has been deleted from the KB";
                $result["log"] .= "\n the device has been deleted from the KB";

                //delete from opensearch indexes
                if(isset($GLOBALS['useOpenSearch']) && $GLOBALS['useOpenSearch'] == 'yes') {
                    $res = delete_from_opensearch($uri, $organization, $result);
                } else {
                    $result["msg"] .= "\n useOpenSearch not set to yes, skipping delete from opensearch of $uri";
                    $result["log"] .= "\n useOpenSearch not set to yes, skipping delete from opensearch of $uri";    
                }

                if($res == 'ok') {
                    if ($row["cbkind"] != 'external') {
                        switch ($protocol) {
                            case "ngsi":
                                $res = delete_ngsi($name, $type, $contextbroker, $kind, $protocol, $format, $model, $latitude, $longitude,
                                        $visibility, $frequency, $listnewAttributes, $ip, $port, $uri, $result);
                                break;
                            case "ngsi w/MultiService":
                                $res = delete_ngsi($name, $type, $contextbroker, $kind, $protocol, $format, $model, $latitude, $longitude,
                                        $visibility, $frequency, $listnewAttributes, $ip, $port, $uri, $result, $service, $servicePath);
                                break;
                            case "mqtt":
                                $res = delete_mqtt($name, $type, $contextbroker, $kind, $protocol, $format, $latitude, $longitude, $visibility,
                                        $frequency, $listnewAttributes, $ip, $port, $uri, $result);
                                break;
                            case "amqp":
                                $res = delete_amqp($name, $type, $contextbroker, $kind, $protocol, $format, $latitude, $longitude, $visibility, $frequency,
                                        $listnewAttributes, $ip, $port, $uri, $result);
                                break;
                        }
                    } else {
                        $res = 'no'; //should not be registered
                    }
                    if ($res == 'ok') {
                        $result["msg"] .= "\n ok deletion from the context broker";
                        $result["log"] .= "\n ok deletion from the context broker";
                    } elseif ($res == "ko") {
                        $result["status"] = 'ko';
                        $result["error_msg"] .= "No deletion from the context broker. ";
                        $result["msg"] .= "\n no deletion from the context broker";
                        $result["log"] .= "\n no deletion from the context broker";
                    } else {// the value is no -- no registration in the context broker
                        $result["status"] = 'ok';
                        $result["msg"] .= "\n no registration in the context broker is required";
                        $result["log"] .= "\n no registration in the context broker is required";
                    }
            
                }
            }
        } catch (Exception $ex) {
            $result["status"] = 'ko';
            $result["error_msg"] .= 'Error in connecting with KB. ';
            $result["msg"] .= ' error in connecting with KB ';
            $result["log"] .= ' error in connecting with KB ' . $ex;
            $res = 'ko'; //uri has not been generated
        }

    } else {
        $result["error_msg"] .= "Error in the validation w.r.t. the KB. ";
        $result["msg"] .= "\n error in the validation w.r.t. the KB";
        $result["log"] .= "\n error in the validation w.r.t. the KB";
        $result["status"] = 'ko';
    }
    return 1;
}

// end of function deleteKB

/* functions for insert/update/delete values from a specific device in the KB and context brokers */

function modify_valueKB($link, $device, $contextbroker, $organization, $kbUrl = "", &$result) {
    $result["status"] = 'ok';

    $listnewAttributes = generateAttributes($link, $device, $contextbroker);

    $query = "SELECT d.uri, d.id, d.devicetype AS entityType, d.kind, d.format, d.macaddress, d.model, d.producer, d.protocol, d.longitude,d.service, d.servicePath,
d.latitude, d.visibility, d.frequency, cb.name, cb.protocol as type, cb.ip, cb.port, cb.login, cb.password, cb.latitude as cblatitude, 
cb.longitude as cblongitude, cb.created, d.`static_attributes` FROM devices d JOIN contextbroker cb ON d.contextBroker = cb.name WHERE d.deleted is null and 
d.contextBroker='$contextbroker' and d.id='$device';";

    $r_init = mysqli_query($link, $query);

    if (!$r_init) {
        $result["status"] = 'ko';
        $result["error_msg"] .= 'Error in reading data from context broker and device. ';
        $result["msg"] .= '\n error in reading data from context broker and device ' . mysqli_error($link);
        $result["log"] .= '\n error in reading data from context broker and device ' . mysqli_error($link) . $query;
        return 1;
    }

    $row = mysqli_fetch_assoc($r_init);
    $type = $row["entityType"];
    $kind = $row["kind"];
    $protocol = $row["protocol"];
    $format = $row["format"];
    $macaddress = $row["macaddress"];
    $model = $row["model"];
    $producer = $row["producer"];
    $latitude = $row["latitude"];
    $longitude = $row["longitude"];
    $visibility = $row['visibility'];
    $frequency = $row['frequency'];
    $ip = $row["ip"];
    $port = $row["port"];
    $uri = $row["uri"];
    $service = $row["service"];
    $servicePath = $row["servicePath"];
    $staticAttributes = $row["static_attributes"];

    if (canBeModified($device, $type, $contextbroker, $kind, $protocol, $format, $macaddress, $model, $producer, $latitude, $longitude,
                    $visibility, $frequency, $listnewAttributes, $result)) {
        /* msg for the Knowledge base + registration on the KB */
        $msg = array();
        $msg["id"] = $device;
        $msg["type"] = $type;
        $msg["kind"] = $kind;
        $msg["protocol"] = $protocol;
        $msg["format"] = $format;
        $msg["macaddress"] = $macaddress;
        $msg["model"] = $model;
        $msg["producer"] = $producer;
        $msg["latitude"] = $latitude;
        $msg["longitude"] = $longitude;
        $msg["frequency"] = $frequency;
        $msg["ownership"] = $visibility;
        $msg["broker"] = array();
        $msg["broker"]["name"] = $contextbroker;
        $msg["broker"]["type"] = $row["protocol"];
        $msg["broker"]["ip"] = $row["ip"];
        $msg["broker"]["port"] = $row["port"];
        $msg["broker"]["login"] = ($row["login"] == null) ? "" : $row["login"];
        $msg["broker"]["password"] = ($row["password"] == null) ? "" : $row["password"];
        $msg["broker"]["latitude"] = $row["cblatitude"];
        $msg["broker"]["longitude"] = $row["cblongitude"];
        $msg["broker"]["created"] = $row["created"];
        $msg["organization"] = $organization;

        $myAttrs = array();
        $i = 1;
        foreach ($listnewAttributes as $att) {
            $myatt = array();
            $myatt["value_name"] = $att["value_name"];
            $myatt["data_type"] = $att["data_type"];
            $myatt["value_type"] = $att["value_type"];
            $myatt["value_unit"] = $att["value_unit"];
            $myatt["healthiness_criteria"] = $att["healthiness_criteria"];
            if ($att["healthiness_criteria"] == "refresh_rate")
                $myatt["value_refresh_rate"] = $att["healthiness_value"];
            else if ($att["healthiness_criteria"] == "different_values")
                $myatt["different_values"] = $att["healthiness_value"];
            else
                $myatt["value_bounds"] = $att["healthiness_value"];
            $myatt["order"] = $att["order"];
            $myAttrs[] = $myatt;
        }
        $msg["attributes"] = $myAttrs;

        try {
            if ($kbUrl == "")
                $url = $_SESSION['kbUrl'] . "iot/insert";
            else
                $url = $kbUrl . "iot/insert";

            $options = array(
                'http' => array(
                    'header' => "Content-Type: application/json;charset=utf-8",
                    'header' => "Access-Control-Allow-Origin: *",
                    'method' => 'POST',
                    'content' => json_encode($msg),
                    'timeout' => 30
                )
            );
            $context = stream_context_create($options);
            $local_result = @file_get_contents($url, false, $context);
            if ($local_result != "errore") {
                $result["status"] = 'ok';
                $result["content"] = $local_result;
                $result["msg"] = "\n the device has been modified in the KB";
                $result["log"] .= "\n the device has been modified in the KB";
                //TODO manage deletion of attributes
                switch ($protocol) {
                    case "ngsi":
                        $res = update_ngsi($device, $type, $contextbroker, $kind, $protocol, $format, $model, $latitude,
                                $longitude, $visibility, $frequency, $listnewAttributes, array(), $ip, $port, $uri, $staticAttributes, $result);
                        break;
                    case "ngsi w/MultiService":
                        $res = update_ngsi($device, $type, $contextbroker, $kind, $protocol, $format, $model, $latitude,
                                $longitude, $visibility, $frequency, $listnewAttributes, array(), $ip, $port, $uri, $staticAttributes, $result, $service, $servicePath);
                        break;
                    case "mqtt":
                        $res = update_mqtt($device, $type, $contextbroker, $kind, $protocol, $format, $latitude, $longitude,
                                $visibility, $frequency, $listnewAttributes, $ip, $port, $uri, $result);
                        break;
                    case "amqp":
                        $res = update_amqp($device, $type, $contextbroker, $kind, $protocol, $format, $latitude, $longitude,
                                $visibility, $frequency, $listnewAttributes, $ip, $port, $uri, $result);
                        break;
                }
                if ($res == 'ok') {
                    $result["msg"] .= "\n ok modification in the context broker";
                    $result["log"] .= "\n ok modification in the context broker";
                } else {
                    $result["status"] = 'ko';
                    $result["error_msg"] .= "There has been no modification in the context broker. ";
                    $result["msg"] .= "\n no modification in the context broker";
                    $result["log"] .= "\n no modification in the context broker";
                }
                return 1;
            } else {
                $result["error_msg"] .= "Error in the validation w.r.t. the KB. ";
                $result["msg"] .= "\n error in the validation w.r.t. the KB";
                $result["log"] .= "\n error in the validation w.r.t. the KB";
                $result["status"] = 'ko';
                return 1;
            }
        } catch (Exception $ex) {
            $result["status"] = 'ko';
            $result["error_msg"] .= 'Error in connecting with KB. ';
            $result["msg"] .= ' error in connecting with KB ' . $ex;
            $result["log"] .= ' error in connecting with KB ' . $ex;
        }
        /* registration of the device in the corresponding context broker */
    }
}

// end of function modify_valueKB

function get_organization_info($organizationApiURI, $ou_tmp) {
    $url = $organizationApiURI . 'organizations.php?org=' . $ou_tmp;
    $context = stream_context_create(null);
    $result = file_get_contents($url, false, $context);

    $result_json = json_decode($result, true);
    if (sizeof($result_json) == 1) {
        return $result_json[0];
    } else {
        return null;
    }
}

function get_LDgraph_link($logUriLD, $organizationApiURI, $org, $uri) {
    $kurl = retrieveKbUrl($organizationApiURI, $org);
    $url = $logUriLD . '?sparql=' . substr($kurl, 0, 4) . substr($kurl, 5, -8) . '/sparql&uri=' . $uri;
    return $url;
}

function get_ServiceMap_link($uri, $organizationApiURI, $org) {
    $kurl = retrieveKbUrl($organizationApiURI, $org);
    $m_url = $kurl . '?serviceUri=' . $uri . '&format=html';
    return $m_url;
}

//return subscription_id. can be FAILED
//returning ok also if subscribe failed (beside try and catch)
function nificallback_create($ip, $port, $name, $urlnificallback, $protocol, $services, &$result) {
    $result["retry"] = false;
    $result["status"] = 'ok';
    $result["content"] = 'FAILED';
    $result["log"] .= "\n Received request of nificallback_create for ip:$ip port:$port cbname:$name";
    $subscriptions = array();
    try {
        $howmany = 1;
        if ($protocol == 'ngsi w/MultiService') {
            $howmany = count($services) + 1; //add scenario senza tenant 
        }

        for ($i = 0; $i < $howmany; $i++) {

            $msg = "{\"description\": \"$name nifi\",\"subject\": {	\"entities\": [{ \"idPattern\": \".*\",	\"typePattern\": \".*\"	}],\"condition\": {\"attrs\": []}},\"notification\": {	\"http\": {\"url\": \"$urlnificallback\" }}}";

            $IP_PORT = checkIP($ip, $port);

            $url = $IP_PORT . "/v2/subscriptions";

            // echo $IP_PORT;

            $result["log"] .= "\n Payload to send is:" . $msg;
            $result["log"] .= "\n Post url is:" . $url;

            $http_headers = array("Content-Type: application/json;charset=utf-8");

            if ($protocol == 'ngsi w/MultiService') {
                if ($i < $howmany - 1) {//scenario senza tenant
                    array_push($http_headers, "Fiware-Service: " . $services[$i]);
                    array_push($http_headers, "Fiware-ServicePath: /#");
                }
            }

            $options = array(
                'http' => array(
                    'header' => $http_headers,
                    'method' => 'POST',
                    'ignore_errors' => true,
                    'timeout' => 30,
                    'content' => $msg
                )
            );

            $context = stream_context_create($options);
            $local_result = @file_get_contents($url, false, $context);

            $result["log"] .= "\n Response is:" . $local_result;

            if (isset($http_response_header) && is_array($http_response_header) && strpos($http_response_header[0], '201') !== false) {
                $sub_id = extract_subscription_id($http_response_header);

                array_push($subscriptions, $sub_id);
                $result["log"] .= "\n Response subscription_id is:" . $sub_id;
            } else {
                $result["retry"] = true;
                $result["log"] .= "\n Error returned or not reachable";
                break;
            }
            //return status==ok even if the subscription failed
        }
        $result["content"] = implode(",", $subscriptions);
    } catch (Exception $ex) {
        $result["status"] = 'ko';
        $result["error_msg"] .= 'Error in creating the subscription for NIFI. ';
        $result["msg"] .= '\n Error in creating the subscription for NIFI ';
        $result["log"] .= '\n Error in creating the subscription for NIFI ' . $ex;
    }
}

function checkIP($IP, $PORT) {

    if (substr($IP, -1) == '/')
        $IP = substr($IP, 0, -1);

    if ($PORT != "")
        $PORT = ":" . $PORT;

    if (substr($IP, 0, 4) != "http") {
        $IP = "http://" . $IP;
    }
    $IP_PORT = $IP . $PORT;

    return $IP_PORT;
}

function extract_subscription_id($headers) {
    foreach ($headers as $header) {
        if (strpos($header, 'Location') !== false) {
            return substr($header, 28);
        }
    }
    return "FAILED";
}

//returning ok also if unsubscribe failed (beside try and catch)
function nificallback_delete($ip, $port, $subscription_id, $name, $protocol, $services, &$result) {
    $result["status"] = 'ok';
    $result["log"] .= " Received request of nificallback_delete for ip:$ip port:$port subscription_id:$subscription_id cbname:$name";

    $subscriptions = explode(",", $subscription_id);

    try {
        $howmany = 1;
        if ($protocol == 'ngsi w/MultiService') {
            $howmany = count($subscriptions); //add scenario senza tenant e senza tenant e senza servicePath
        }

        for ($i = 0; $i < $howmany; $i++) {
            $url = "http://" . $ip . ":" . $port . "/v2/subscriptions/" . $subscriptions[$i];

            $result["log"] .= "\n Delete url is:" . $url;

            $http_headers = array();

            if ($protocol == 'ngsi w/MultiService') {
                if ($i < $howmany - 1)//scenario senza tenant
                    array_push($http_headers, "Fiware-Service: " . $services[$i]);
                //array_push($http_headers, "Fiware-ServicePath: /#");
            }

            $options = array(
                'http' => array(
                    'header' => $http_headers,
                    'method' => 'DELETE',
                    'ignore_errors' => true,
                    'timeout' => 30
                )
            );

            $context = stream_context_create($options);
            $local_result = @file_get_contents($url, false, $context);

            $result["log"] .= "\n Response is: " . $local_result;

            //return status==ok even if the subscription failed
        }
    } catch (Exception $ex) {
        $result["status"] = 'ko';
        $result["error_msg"] .= 'Error in removing the subscription for NIFI. ';
        $result["msg"] .= '\n Error in removing the subscription for NIFI ';
        $result["log"] .= '\n Error in removing the subscription for NIFI ' . $ex;
    }
}

/**
 * Returns 0 if the value if valid, 1 otherwise
 */
function servicePathSyntaxCheck($servicePath) {
    // remove initial and final "/", if any
    if ($servicePath[0] == "/")
        $servicePath = substr($servicePath, 1);
    if ($servicePath[strlen($servicePath) - 1] == "/")
        $servicePath = substr($servicePath, 0, -1);

    // case: empty string
    if ($servicePath == "")
        return 0;

    // case: servicePath is too long
    if (strlen($servicePath) > 95)
        return 1;

    // get single servicePath "levels"
    $levels = explode("/", $servicePath);

    // case: too many levels
    if (count($levels) > 10)
        return 1;

    for ($i = 0; $i < count($levels); $i++) {
        // case: some level is too long
        if (strlen($levels[$i]) > 50)
            return 1;

        // case: there are some empty level
        if ($levels[$i] == "")
            return 1;

        // case: some level contains some semicolons
        if (strpos($servicePath, ".") !== false)
            return 1;

        // case: some level contains some whitespaces
        if (preg_match("/\s/", $levels[$i]))
            return 1;
    }

    // case: everything is ok
    return 0;
}

function get_all_models($username, $organization, $role, $accessToken, $link, &$result) {
    getOwnerShipObject($accessToken, "ModelID", $result);
    getDelegatedObject($accessToken, $username, "ModelID", $result);

    $res = array();
    $q = "SELECT * FROM model";
    $r = mysqli_query($link, $q);

    if ($r) {
        while ($row = mysqli_fetch_assoc($r)) {
            $idTocheck = $row["organization"] . ":" . $row["name"];
            if (
                    ($role == 'RootAdmin') || //roles
                    ($role == 'ToolAdmin') ||
                    (
                    ($row["organization"] == $organization) && //public
                    (($row["visibility"] == 'public' || (isset($result["delegation"][$idTocheck]) && $result["delegation"][$idTocheck]["kind"] == "anonymous")) )
                    ) ||
                    (isset($result["delegation"][$idTocheck]) && $result["delegation"][$idTocheck]["kind"] != "anonymous") || //delegation
                    (isset($result["keys"][$idTocheck]) && $result["keys"][$idTocheck]["owner"] == $username)       //owner
            ) {
                array_push($res, $row);
            }
        }
        $result["status"] = "ok";
        $result["content"] = $res;
        $result["log"] = "action=get_all_models \r\n";
    } else {
        $result["status"] = "ko";
        $result['msg'] = mysqli_error($link);
        $result["log"] = "action=get_all_models -" . " error " . mysqli_error($link) . "\r\n";
    }
}

function is_broker_up($link, $cb, $service, $servicePath, $version, $organization, &$result) {

    $query = "SELECT * from contextbroker WHERE name = '$cb'";
    $r = mysqli_query($link, $query);
    if (!$r) { //existence of cb is guaranteed from previously enforcement
        $result["status"] = 'ko';
        $result["error_msg"] = "Error in reading data from context broker.";
        $result["msg"] = ' error in reading data from context broker ' . mysqli_error($link);
        $result["log"] .= '\n error in reading data from context broker ' . mysqli_error($link) . $query;
        return 1;
    }
    $rowCB = mysqli_fetch_assoc($r);
    $ip = $rowCB["ip"];
    $port = $rowCB["port"];

    if ($version == "v2") {

        $IP_PORT = checkIP($ip, $port);
        $url_orion = "$IP_PORT/v2/entities";
    } else
        $url_orion = "http://$ip:$port/v1/queryContext";


    try {

        $ch = curl_init($url_orion);
        $httpheader = array();
        if ($service != "")
            array_push($httpheader, 'Fiware-Service: ' . $service);
        if ($servicePath != "")
            array_push($httpheader, 'Fiware-ServicePath: ' . $servicePath);

        if ($version == "v2") {
            curl_setopt_array($ch, array(
                CURLOPT_HTTPGET => TRUE,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_HTTPHEADER => $httpheader
            ));
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        } else {
            array_push($httpheader, 'Content-Type:application/json');
            $payload = "{\"entities\":[{\"isPattern\": \"true\",  \"id\": \".*\"}]}";
            curl_setopt_array($ch, array(
                CURLOPT_POST => TRUE,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_HTTPHEADER => $httpheader,
                CURLOPT_POSTFIELDS => $payload
            ));
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        }
        $response_orion = curl_exec($ch);

        if ($response_orion === FALSE) {
            $result["status"] = "ko";
            $result["error_msg"] = "Error in the connection with the ngsi context broker. ";
            $result["msg"] = "Error in the connection with the ngsi context broker";
            $result["log"] .= "\n Error in the connection with the ngsi context broker";
        } else {

            $result["status"] = 'ok';
            $result["msg"] = 'response from the ngsi context broker ';
            $result["log"] .= '\n response from the ngsi context broker ' . $response_orion;
            $result["content"] = $url_orion;
        }
    } catch (Exception $ex) {
        $result["status"] = 'ko';
        $result["error_msg"] = 'Error in connecting with the ngsi context broker. ';
        $result["msg"] = 'error in connecting with the ngsi context broker ';
        $result["log"] .= '\n error in connecting with the ngsi context broker ' . $ex;
    }
    echo json_encode($result);
}

function get_device($username, $role, $id, $cb, $accessToken, $link, &$result, $onlyIfOwned = false) {



    $q = "SELECT d.`uri`, d.`devicetype`, d.`kind`,
		CASE WHEN mandatoryproperties AND mandatoryvalues THEN \"active\" ELSE \"idle\" END AS status1,
		d.`macaddress`, d.`model`, d.`producer`, d.`longitude`, d.`latitude`, d.`protocol`, d.`format`, d.`visibility`,
		d.`frequency`, d.`created`, d.`privatekey`, d.`certificate`,d.`organization`, cb.`accesslink`, cb.`accessport`, 
		cb.`sha`, d.`subnature`, d.`static_attributes` 
		FROM `devices` d JOIN `contextbroker` cb ON (d.contextBroker=cb.name)
		WHERE d.`contextBroker`='$cb' AND d.`id`='$id'";

    $r = mysqli_query($link, $q);

    if ($r) {
        while ($row = mysqli_fetch_assoc($r)) {//should be just one data
            $eid = $row["organization"] . ":" . $cb . ":" . $id;

            getOwnerShipDevice($accessToken, $result, $eid);
            if (!$onlyIfOwned)
                getDelegatedDevice($accessToken, $username, $result);
            unset($result['msg']);

            if (
                    ($role == 'RootAdmin') ||
                    (isset($result["delegation"][$eid]))//if there are any delegation (public or personal)
                    ||
                    (isset($result["keys"][$eid]) && $result["keys"][$eid]["owner"] == $username)
            ) {
                //it's permitted to access
                //in case of ownership or RootAdmin, enrich with k1, k2 credentials
                if (($role == 'RootAdmin') || (isset($result["keys"][$eid]) && $result["keys"][$eid]["owner"] == $username)) {
                    $row["k1"] = $result["keys"][$eid]["k1"];
                    $row["k2"] = $result["keys"][$eid]["k2"];
                }
                // in case of delegation, resetting privatekey info
                else {
                    unset($row["sha"]);
                    unset($row["privatekey"]);
                    unset($row["certificate"]);

                    //in case of personal delegation, enrich with k1, k2 delegation credentials
                    if ($result["delegation"][$eid]["kind"] !== "anonymous") {
                        $row["k1"] = $result["delegation"][$eid]["k1"];
                        $row["k2"] = $result["delegation"][$eid]["k2"];
                    }
                }
                $result["content"] = $row;
                $result["status"] = "ok";
                $result["log"] = "action=get_device \r\n";
                break;
            }
        }
    } else {
        $result["status"] = "ko";
        $result['msg'] = mysqli_error($link);
        $result["log"] = "action=get_device -" . " error " . mysqli_error($link) . "\r\n";
    }

    unset($result["username"]);
    unset($result["keys"]);
    unset($result["delegation"]);
}

function change_Status($link, $name, $organization) {


    // echo $id_CB;
    $query = "UPDATE iotdb.orionbrokers SET status='deploy' WHERE name='$name' and organization='$organization'";
    //  $checkQuery = " SELECT * FROM iotdb.orionbrokers WHERE `name`='$name'";


    $r = mysqli_query($link, $query);
    //  $r = mysqli_query($link, $checkQuery);

    $rupdate = mysqli_fetch_assoc($r);

    if (!$r) { //existence of cb is guaranteed from previously enforcement
        $result["status"] = 'ko';
        $result["error_msg"] = "Error in reading data from orionbrokers table.";
        $result["msg"] = ' error in reading data from orionbrokers table' . mysqli_error($link);
        $result["log"] .= '\n error in reading data from orionbrokers table ' . mysqli_error($link) . $query;
        return 1;
    }


    if ($r) {

        $result["status"] = '{"status": "Ok - get CB "}';
        $result["content"] = json_encode($rupdate);

        $result["msg"] = 'response from the query in the  orionbrokers ';
        $result["log"] .= '\n response from the query  query in the  orionbrokers' . $rupdate;
        // echo json_encode( json_encode($rupdate) );
    }
}

function get_all_contextbrokers($username, $organization, $loggedrole, $accessToken, $link, $length, $start, $draw, $request, $selection, &$result) {
    getOwnerShipObject($accessToken, "BrokerID", $result);
    getDelegatedObject($accessToken, $username, "BrokerID", $result);

    $q = "SELECT * FROM contextbroker";

    if (count($selection) != 0) {
        $a = 0;
        $cond = "";
        while ($a < count($selection)) {
            $sel = $selection[$a];
            $cond .= " (name = '" . $sel->name . "' AND organization = '" . $sel->organization . "') ";
            if ($a != count($selection) - 1)
                $cond .= " OR ";
            $a++;
        }
        $r = create_datatable_data($link, $_REQUEST, $q, $cond);
    } else {
        $r = create_datatable_data($link, $request, $q, '');
    }
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

            $row["dynamic"] = false;
            $row["enable_direct_access"] = false;

            $p = "SELECT status FROM orionbrokers where name= '" . $row["name"] . "' AND organization = '" . $row["organization"] . "';";

            $rq = mysqli_query($link, $p);

            $Prov = (mysqli_fetch_assoc($rq));

            if ($Prov) {
                $row["dynamic"] = true;
            } else {
                $row["dynamic"] = false;
            }
            $p = "SELECT  enable_direct_access FROM orionbrokers where name= '" . $row["name"] . "' AND organization = '" . $row["organization"] . "';";

            $rq = mysqli_query($link, $p);

            $Prov = (mysqli_fetch_assoc($rq));

            if ($Prov['enable_direct_access'] == 1) {
                $row["enable_direct_access"] = true;
            } else {
                $row["enable_direct_access"] = false;
            }

            if (
                    ($loggedrole == 'RootAdmin') ||
                    ($loggedrole == 'ToolAdmin') ||
                    (
                    ($row["organization"] == $organization) &&
                    (
                    ($row["visibility"] == 'public' || (isset($result["delegation"][$idTocheck]) && $result["delegation"][$idTocheck]["kind"] == "anonymous"))
                    )
                    ) ||
                    (isset($result["delegation"][$idTocheck]) && $result["delegation"][$idTocheck]["kind"] != "anonymous") ||
                    (isset($result["keys"][$idTocheck]) && $result["keys"][$idTocheck]["owner"] == $username)
            ) {
                $selectedrows++;
                if (!$tobelimited || ($tobelimited && $selectedrows >= $start && $selectedrows < ($start + $offset))) {
                    if (((isset($result["keys"][$idTocheck])) && ($loggedrole !== 'RootAdmin') && ($loggedrole !== 'ToolAdmin')) ||
                            ((isset($result["keys"][$idTocheck])) && ($result["keys"][$idTocheck]["owner"] == $username) && (($loggedrole === 'RootAdmin') || ($loggedrole === 'ToolAdmin')))) {
                        //it's mine
                        if ($row["visibility"] == "public") {
                            $row["visibility"] = "MyOwnPublic";
                        } else {
                            if (isset($result["delegation"][$row["accesslink"]]) && $result["delegation"][$row["accesslink"]]["kind"] == "anonymous")
                                $row["visibility"] = "MyOwnPublic";
                            else
                                $row["visibility"] = "MyOwnPrivate";
                        }
                    } else {
                        //it's not mine
                        if (isset($result["delegation"][$idTocheck]) && ($result["delegation"][$idTocheck]["kind"] == "anonymous")) {
                            //it's delegated as public
                            $row["visibility"] = 'public';
                        } else if (isset($result["delegation"][$idTocheck])) {
                            //it's delegated personally
                            $row["visibility"] = 'delegated';
                        } else {
                            $row["visibility"] = $row["visibility"];
                        }
                    }

                    $row["owner"] = '';
                    if (isset($result["keys"][$idTocheck]))
                        $row["owner"] = $result["keys"][$idTocheck]["owner"];

                    if ($row["protocol"] == "ngsi w/MultiService") {
                        // the CB supports MultiServices
                        $brokerName = $row["name"];
                        $servicesQueryString = "SELECT * FROM services WHERE broker_name = '$brokerName'";
                        $sqr = mysqli_query($link, $servicesQueryString);
                        if ($sqr) {
                            $row["services"] = array();
                            while ($servicesRow = mysqli_fetch_assoc($sqr)) {
                                array_push($row["services"], $servicesRow["name"]);
                            }
                        } else {
                            $output = format_result($draw, 0, 0, null, 'Error: errors in reading data about IOT Broker. <br/>' . generateErrorMessage($link), '\n\r Error: errors in reading data about IOT Broker.' . generateErrorMessage($link), 'ko');
                            logAction($link, $username, 'contextbroker', 'get_all_contextbroker', '', $organization, 'Error: errors in reading data about IOT Broker.', 'faliure');
                        }
                    }


                    array_push($data, $row);
                }
            }
        }

        $result = format_result($draw, $selectedrows + 1, $selectedrows + 1, $data, "", "\r\n action=get_all_contextbroker \r\n", 'ok');
        logAction($link, $username, 'contextbroker', 'get_all_contextbroker', '', $organization, '', 'success');
    } else {
        $result = format_result($draw, 0, 0, null, 'Error: errors in reading data about IOT Broker. <br/>' . generateErrorMessage($link), '\n\r Error: errors in reading data about IOT Broker.' . generateErrorMessage($link), 'ko');
        logAction($link, $username, 'contextbroker', 'get_all_contextbroker', '', $organization, 'Error: errors in reading data about IOT Broker.', 'faliure');
    }
}

function get_user_info($accessToken, &$username, &$organization, $oidc, &$role, &$result, $ldapBaseName, $ldapServer, $ldapPort, $ldapAdminName, $ldapAdminPwd) {
    $mctime = microtime(true);

    $result["status"] = "ok";

    $oidc->setAccessToken($accessToken);

    $userinfo = (array) $oidc->getAccessTokenPayload();

    if ((isset($userinfo['preferred_username'])) && ($userinfo['preferred_username'] != null))
        $username = $userinfo["preferred_username"];
    else if ((isset($userinfo["username"])) && ($userinfo["username"] != null))
        $username = $userinfo["username"];
    else {
        $result["status"] = "ko";
        $result['msg'] = "Username not found in AccessToken";
        $result['error_msg'] .= "Username not found in AccessToken";
        $result["log"] = "action=get_user_info -" . " Username not found in AccessToken \r\n";
    }

    $uinfo = $oidc->requestUserInfo();
    if (isset($uinfo->error)) {
        $result["status"] = "ko";
        $result['msg'] = "Userinfo not found in AccessToken";
        $result['error_msg'] .= "Userinfo not found in AccessToken " . json_encode($uinfo);
        $result["log"] = "action=get_user_info -" . " Userinfo not found in AccessToken " . json_encode($uinfo) . "\r\n";
    }

    if ($result["status"] == "ok") {

        if (in_array("RootAdmin", $userinfo["roles"]))
            $role = "RootAdmin";
        else if (in_array("ToolAdmin", $userinfo["roles"]))
            $role = "ToolAdmin";
        else if (in_array("AreaManager", $userinfo["roles"]))
            $role = "AreaManager";
        else if (in_array("Manager", $userinfo["roles"]))
            $role = "Manager";
        else {
            $result["status"] = "ko";
            $result['msg'] = "Role not found in AccessToken for user " . $username;
            $result["error_msg"] .= "Role not found in AccessToken for user " . $username;
            $result["log"] = "action=get_user_info -" . " Role not found in AccessToken for user " . $username . "\r\n";
        }
    }

    if ($result["status"] == "ok") {
        $organization = findLdapOrganizationalUnit($username, $ldapBaseName, $ldapServer, $ldapPort, $ldapAdminName, $ldapAdminPwd);
        if (empty($organization)) {
            $result["status"] = "ko";
            $result['msg'] = "Organization not found in AccessToken for user " . $username;
            $result['error_msg'] .= "Organization not found in AccessToken for user " . $username;
            $result["log"] = "action=get_user_info -" . " Organization not found in AccessToken for user " . $username . "\r\n";
        }
    }
}

//TODO armonize with the code available in ssoLogin.php AND ldap.php in api and in nodeRed.php
function findLdapOrganizationalUnit($username, $ldapBaseName, $ldapServer, $ldapPort, $ldapAdminName, $ldapAdminPwd) {
    $toreturn = "";

    $ldapUsername = "cn=" . strtolower($username) . "," . $ldapBaseName;
    $ds = ldap_connect($ldapServer, $ldapPort);
    ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
    $bind = ldap_bind($ds, $ldapAdminName, $ldapAdminPwd);
    $result = ldap_search($ds, $ldapBaseName, '(&(objectClass=organizationalUnit)(l=' . $ldapUsername . '))');
    $entries = ldap_get_entries($ds, $result);

    if (ldap_count_entries($ds, $result) == 0) {
        //TODO thrown an error or return an error, here we just return empty string
    } else {
        $toreturn = $entries["0"]["ou"][0];
    }

    return $toreturn;
}

function missingParameters($requiredParams) {
    $toreturn = array();

    foreach ($requiredParams as $param)
        if (!isset($_REQUEST[$param]))
            array_push($toreturn, $param);

    return $toreturn;
}

//it does not throw any error... just return an empty string in case of trouble
function getProtocol($contextBrokerName, $link) {
    $toreturn = "";

    $q = "SELECT protocol FROM iotdb.contextbroker where name='$contextBrokerName'";

    $r = mysqli_query($link, $q);

    if ($r) {
        while ($row = mysqli_fetch_assoc($r)) {//should be just one data
            if (isset($row["protocol"])) {
                $toreturn = $row["protocol"];
                break;
            }
        }
    }
    return $toreturn;
}

//return false if you have no right to access
//action can be 'write' or 'read'
function enforcementRights($username, $token, $role, $elementId, $elementType, $action, &$result) {
    if (($role == "RootAdmin") || ($role == "ToolAdmin")) {
        $toreturn = true; //grant RootAdmin for everything
    } else {
        //for write check owenrship
        if ($action == "write") {
            if (checkOwnershipObject($token, $elementId, $elementType, $result)) {
                $toreturn = true;
            } else {
                checkDelegationObject($username, $token, $elementId, $elementType, $result);
                $toreturn = $result["delegationKind"] == "READ_WRITE" || $result["delegationKind"] == "MODIFY" || $result["delegationKind"]=="WRITE_ONLY";
                $result["delegationKind"] = $result["delegationKind"];
            }
        } else if ($action == "read") {
            $toreturn = checkOwnershipObject($token, $elementId, $elementType, $result) ||
                    checkDelegationObject($username, $token, $elementId, $elementType, $result);
        }
    }

    return $toreturn;
}

function retrieveKbUrl($organizationApiURI, $org) {
    //retrieve the kburl -> this is needed since this api can be called from OUTSIDE of the IoT directory
    $kburl = "";
    //if (!isset($_SESSION['kbUrl']))
    //{
    $infokburl = get_organization_info($organizationApiURI, $org);
    if (!is_null($infokburl)) {
        $kburl = $infokburl["kbUrl"];
    }
    /* }
      else
      {
      $kburl=$_SESSION['kbUrl'];
      }i */
    return $kburl;
}

//FUNZIONE PER TEST da prendere spunto



function get_device_data($link, $id, $type, $cb, $service, $servicePath, $version, &$result) {
    //retrieve cb information
    $query = "SELECT * from contextbroker WHERE name = '$cb'";
    $r = mysqli_query($link, $query);
    if (!$r) { //existence of cb is guaranteed from previously enforcement
        $result["status"] = 'ko';
        $result["error_msg"] = "Error in reading data from context broker.";
        $result["msg"] = ' error in reading data from context broker ' . mysqli_error($link);
        $result["log"] .= '\n error in reading data from context broker ' . mysqli_error($link) . $query;
        return 1;
    }
    $rowCB = mysqli_fetch_assoc($r);
    $ip = $rowCB["ip"];
    $port = $rowCB["port"];

    if ($version == "v2")
        $url_orion = "http://$ip:$port/v2/entities/$id?type=$type";
    else
        $url_orion = "http://$ip:$port/v1/queryContext";

    try {

        $ch = curl_init($url_orion);

        $httpheader = array();
        if ($service != "")
            array_push($httpheader, 'Fiware-Service: ' . $service);
        if ($servicePath != "")
            array_push($httpheader, 'Fiware-ServicePath: ' . $servicePath);

        if ($version == "v2")
            curl_setopt_array($ch, array(
                CURLOPT_HTTPGET => TRUE,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_HTTPHEADER => $httpheader,
            ));
        else {
            array_push($httpheader, 'Content-Type:application/json');
            $payload = "{\"entities\":[{\"type\":\"" . $type . "\",\"id\":\"" . $id . "\"}]}";
            curl_setopt_array($ch, array(
                CURLOPT_POST => TRUE,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_HTTPHEADER => $httpheader,
                CURLOPT_POSTFIELDS => $payload
            ));
        }

        $response_orion = curl_exec($ch);

        if ($response_orion === FALSE) {
            $result["status"] = "ko";
            $result["error_msg"] = "Error in the connection with the ngsi context broker. ";
            $result["msg"] = "Error in the connection with the ngsi context broker";
            $result["log"] .= "\n Error in the connection with the ngsi context broker";
        } else {
            $result["status"] = 'ok';
            if ($version == "v2") {
                $res = json_decode($response_orion);
                foreach ($res as $key => $value) {
                    if (isset($res->$key->metadata))//clear metadata field
                        unset($res->$key->metadata);
                    error_log($key);
                    if ($key == "model")//clear model field
                        unset($res->$key);
                }
                $result["content"] = json_encode($res);
            } else {
                $res = json_decode($response_orion);
                unset($res->contextResponses[0]->contextElement->isPattern); //clear isPatter field
                foreach ($res->contextResponses[0]->contextElement->attributes as $key => $value) {//clear model field
                    if ($value->name == "model") {
                        unset($res->contextResponses[0]->contextElement->attributes[$key]);
                        break;
                    }
                }
                $res->contextResponses[0]->contextElement->attributes = array_values($res->contextResponses[0]->contextElement->attributes); //avoid using index
                $result["content"] = json_encode($res->contextResponses[0]->contextElement);
            }
            $result["msg"] = 'response from the ngsi context broker ';
            $result["log"] .= '\n response from the ngsi context broker ' . $response_orion;
        }
    } catch (Exception $ex) {
        $result["status"] = 'ko';
        $result["error_msg"] = 'Error in connecting with the ngsi context broker. ';
        $result["msg"] = 'error in connecting with the ngsi context broker ';
        $result["log"] .= '\n error in connecting with the ngsi context broker ' . $ex;
    }
}

//// Loading temporany value
function get_value_attribute() {
    
}

/// Loading value 

function Loading_value($link, $id, $type, $cb, $service, $servicePath, $version, &$result) {

    //retrieve cb information
    $query = "SELECT * from contextbroker WHERE name = '$cb'";
    $queryMobile = "select * from iotdb.devices where static_attributes like '%[\"http://www.disit.org/km4city/schema#isMobile\",\"true\"]%' and id='$id' ";
    $r = mysqli_query($link, $query);

    if (!$r) { //existence of cb is guaranteed from previously enforcement
        $result["status"] = 'ko';
        $result["error_msg"] = "Error in reading data from context broker.";
        $result["msg"] = ' error in reading data from context broker ' . mysqli_error($link);
        $result["log"] .= '\n error in reading data from context broker ' . mysqli_error($link) . $query;
        return 1;
    }
    $rowCB = mysqli_fetch_assoc($r);
    $ip = $rowCB["ip"];
    $port = $rowCB["port"];
    $r = mysqli_query($link, $queryMobile);

    if (!$r) {
        $result["status"] = 'ko';
        $result["error_msg"] = "Error in reading data from devices." . mysqli_error($link);
        $result["msg"] = ' error in reading data from device ' . mysqli_error($link);
        $result["log"] .= '\n error in reading data from device ' . mysqli_error($link) . $query;
        return 1;
    }
    $isMobile = (mysqli_fetch_assoc($r));

    $IP_PORT = checkIP($ip, $port);

    if ($version == "v2")
        $url_orion = "$IP_PORT/v2/entities/$id?options=keyValues";
    //"?options=keyValues";
//    else
//        $url_orion = "http://$ip:$port/v1/queryContext";

    try {

        $ch = curl_init();
        $httpheader = array();
        if ($service != "")
            array_push($httpheader, 'Fiware-Service: ' . $service);
        if ($servicePath != "")
            array_push($httpheader, 'Fiware-ServicePath: ' . $servicePath);

        if ($version == "v2") {
            curl_setopt($ch, CURLOPT_URL, $url_orion);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        } else {
            array_push($httpheader, 'Content-Type:application/json');

            curl_setopt_array($ch, array(
                CURLOPT_POST => TRUE,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_HTTPHEADER => $httpheader
            ));
        }

        $response_orion = curl_exec($ch);

        if ($response_orion === FALSE) {

            $result["status"] = "ko";
            $result["error_msg"] = "Error in the connection with the ngsi context broker. ";
            $result["msg"] = "Error in the connection with the ngsi context broker";
            $result["log"] .= "\n Error in the connection with the ngsi context broker";
        } else {

            $result["status"] = 'ok';
            $result["content"] = json_decode($response_orion);
            // echo $result["content"];
            $result["isMobile"] = $isMobile ? "true" : "false";
            //'{"status": "Ok - Keeping data"}';

            $result["msg"] = 'response from the ngsi context broker ';
            $result["log"] .= '\n response from the ngsi context broker ' . $response_orion;
        }
    } catch (Exception $ex) {
        $result["status"] = 'ko';
        $result["error_msg"] = 'Error in connecting with the ngsi context broker. ';
        $result["msg"] = 'error in connecting with the ngsi context broker ';
        $result["log"] .= '\n error in connecting with the ngsi context broker ' . $ex;
    }
}

/// Get CB saved
/// Given id_sub get CB

function get_specific_contextbroker($link, $accessToken, $sub_ID, $resourceLink, &$result) {

    $query = "SELECT * FROM contextbroker where subscription_id like '%$sub_ID%'";

    $r = mysqli_query($link, $query);
    $infoCB = mysqli_fetch_assoc($r);
    if (!$r) { //existence of cb is guaranteed from previously enforcement
        $result["status"] = 'ko';
        $result["error_msg"] = "Error in reading data from context broker table.";
        $result["msg"] = ' error in reading data from context broker table' . mysqli_error($link);
        $result["log"] .= '\n error in reading data from context broker table ' . mysqli_error($link) . $query;
        return 1;
    }


    $infoCB["serviceUriPrefix"] = $resourceLink . $infoCB["name"] . '/' . $infoCB["organization"];

    if ($r) {


        $result["status"] = '{"status": "Ok - get info CB "}';
        $result["content"] = ($infoCB);

        $result["msg"] = 'response from the query about context broker, given id_sub ';
        $result["log"] .= '\n response from the query about context broker, given id_sub' . $infoCB;
    }
}

///INSERT  value
function Insert_Value($link, $id, $type, $cb, $service, $servicePath, $version, $payload, &$result) {

    //retrieve cb information
    $query = "SELECT * from contextbroker WHERE name = '$cb'";
    $r = mysqli_query($link, $query);
    if (!$r) { //existence of cb is guaranteed from previously enforcement
        $result["status"] = 'ko';
        $result["error_msg"] = "Error in reading data from context broker.";
        $result["msg"] = ' error in reading data from context broker ' . mysqli_error($link);
        $result["log"] .= '\n error in reading data from context broker ' . mysqli_error($link) . $query;
        return 1;
    }
    $rowCB = mysqli_fetch_assoc($r);
    $ip = $rowCB["ip"];
    $port = $rowCB["port"];

    if ($version == "v2")
        $url_orion = "http://$ip:$port/v2/entities/$id/attrs";

//    else
//        $url_orion = "http://$ip:$port/v1/queryContext";

    try {

        $ch = curl_init();
        $httpheader = array();
        if ($service != "")
            array_push($httpheader, 'Fiware-Service: ' . $service);
        if ($servicePath != "")
            array_push($httpheader, 'Fiware-ServicePath: ' . $servicePath);

        array_push($httpheader, 'Content-Type:application/json');
        array_push($httpheader, 'Content-Length: ' . strlen($payload));

        if ($version == "v2") {


            curl_setopt($ch, CURLOPT_URL, $url_orion);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        } else {

            curl_setopt_array($ch, array(
                CURLOPT_POST => TRUE,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_HTTPHEADER => $httpheader,
                CURLOPT_POSTFIELDS => $payload
            ));
        }

        $response_orion = curl_exec($ch);

        if ($response_orion === FALSE) {

            $result["status"] = "ko";
            $result["error_msg"] = "Error in the connection with the ngsi context broker. ";
            $result["msg"] = "Error in the connection with the ngsi context broker";
            $result["log"] .= "\n Error in the connection with the ngsi context broker";
        } else {

            $result["status"] = 'ok';
            $result["content"] = '{"status": "Ok - insert data"}';

            $result["msg"] = 'response from the ngsi context broker ';
            $result["log"] .= '\n response from the ngsi context broker ' . $response_orion;
        }
    } catch (Exception $ex) {
        $result["status"] = 'ko';
        $result["error_msg"] = 'Error in connecting with the ngsi context broker. ';
        $result["msg"] = 'error in connecting with the ngsi context broker ';
        $result["log"] .= '\n error in connecting with the ngsi context broker ' . $ex;
    }
}

function isMobile($staticAttributes) {
    $index1 = strpos($staticAttributes, "http://www.disit.org/km4city/schema#isMobile");
    $index2 = strpos($staticAttributes, "true", $index1);
    return (($index1 !== FALSE) && ($index2 === $index1 + 49));
}

function guidv4() {
    $data = openssl_random_pseudo_bytes(16);

    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function logAction($link, $accessed_by, $target_entity_type, $access_type, $entity_name, $organization, $notes, $result) {
    $query = "INSERT INTO access_log(accessed_by,target_entity_type,access_type,entity_name,organization,notes,result) " .
            "VALUES('$accessed_by','$target_entity_type','$access_type','$entity_name','$organization','" . substr($notes, 0, 65000) . "','$result')";
    $res = mysqli_query($link, $query);
    if ($res) {
        $result["msg"] = "correctly logged\n" . $accessed_by . " " . $target_entity_type . " " . $access_type . " " . $entity_name .
                " " . $notes;
    } else {
        $result["log"] =$result["log"] . " --- error in inserting log " . $query . "\n";
    }
    return $result["msg"];
}

?>
