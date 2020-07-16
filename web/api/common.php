<?php

function checkRegisterOwnerShipObject($token,$object, &$result) {
        try
        {
                $url= $GLOBALS["ownershipURI"] . "ownership-api/v1/limits/?type=".$object."&accessToken=" . $token;
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
                if(strpos($http_response_header[0], '200') !== false)
                {
                       if ((json_decode($local_result)->limits[0]->current)<(json_decode($local_result)->limits[0]->limit))
                       {
                               $result["status"]='ok';
                               $result["msg"] .= "\n registration is possible";
                               $result["log"] .= "\n registration is possible";
                       }
                       else
                       {
                               $result["status"]='ko';
                                $result["error_msg"] .= "The registration is NOT possible. Reached limit of ".$object;
                                $result["msg"] .= "\n The registration is NOT possible. Reached limit of ".$object." (".json_decode($local_result)->limits[0]->limit.")";
                                $result["log"] .= "\n The registration is NOT possible. Reached limit of ".$object." (".json_decode($local_result)->limits[0]->limit.")";

                       }
                }
                else
                {
                        $result["status"]='ko';
                        $result["error_msg"] .= "Error returned in checking the ownership. ";
                        $result["msg"] .= "\n Error returned in checkRegisterOwnership" . $local_result;
                        $result["log"] .= "\n Error returner in checkRegisterOwnership". $local_result;
                }
        }
        catch (Exception $ex)
        {
               $result["status"]='ko';
               $result["error_msg"] .= 'General error in checking the ownership. ';
               $result["msg"] .= '\n general error in checkRegisterOwnership';
               $result["log"] .= '\n general error in checkRegisterOwnership' . $ex;
        }
} 

function insert_device($link, $id, $devicetype, $contextbroker, $kind, $protocol, $format, $macaddress, $model,
	$producer, $latitude, $longitude, $visibility, $frequency, $k1, $k2, $edgegateway_type, $edgegateway_uri,
	$listAttributes, $subnature, $staticAttributes, $pathCertificate, $accessToken, &$result, $shouldbeRegistered='yes', 
	$organization, $kbUrl="", $username="", $service="", $servicePath="")
{
  checkRegisterOwnerShipObject($accessToken, 'IOTID', $result);
  if ($result["status"]=='ok')
  { 
    $selectDevicesDeleted = "SELECT contextBroker, id
		FROM deleted_devices WHERE contextBroker = '$contextbroker'
		AND id = '$id';";
    $s3 = mysqli_query($link, $selectDevicesDeleted);
    if($s3)
    {
      if(mysqli_num_rows($s3) == 0)
      {
 
	$isRegistered=registerCertificatePrivateKey($link, $contextbroker, $id, $model, $pathCertificate, $result, $username);
	if ($result["status"]=='ok')
        {
		if ($isRegistered)
		{
                	$privatekey = $id . "-key.pem";
	                $certificate = $id . "-crt.pem";
        	        $publickey = $id . "-pubkey.pem";
		}
		else 
		{
			$privatekey = "";
			$certificate = "";
			$publickey = "";
		}
        }
        else
        {
                //the registration failed, return error
                return $result;
        }



	if(!isset($shouldbeRegistered))
	{
		registerKB($link, $id, $devicetype, $contextbroker, $kind, $protocol,
		 $format, $macaddress, $model, $producer, $latitude, $longitude, $visibility, 
		 $frequency, $listAttributes, $subnature, $staticAttributes,$result,'yes', $organization,$kbUrl, $service, $servicePath); 
	}
	else
	{
	 registerKB($link, $id, $devicetype, $contextbroker, $kind, $protocol,
	 $format, $macaddress, $model, $producer, $latitude, $longitude, $visibility, 
	 $frequency, $listAttributes, $subnature, $staticAttributes,$result,$shouldbeRegistered, $organization,$kbUrl, $service, $servicePath); 
	}

	if ($result["status"]=='ko' ) return $result;
	
	//TODO check if needed
        $syntaxRes=0;
        if ($protocol == 'ngsi w/MultiService'){
		$id = $service . "." . $servicePath . "." . $id;
 	        $syntaxRes = servicePathSyntaxCheck($servicePath);
                $service="'$service'";
		$servicePath="'$servicePath'";
        }
        else {
                $service="NULL";
		$servicePath="NULL";
        }

	if ($syntaxRes == 0){

	 if ($result["status"]=='ok' &&  $result["content"]==null)
	 {
	  $q = "INSERT INTO devices(id, devicetype, contextBroker,  kind, protocol, format, macaddress, model, producer, latitude, longitude, visibility, frequency, privatekey, certificate, organization, subnature, static_attributes, service, servicePath) " .
		 "VALUES('$id', '$devicetype', '$contextbroker', '$kind', '$protocol', '$format', '$macaddress', '$model', '$producer', '$latitude', '$longitude', '$visibility', '$frequency', '$privatekey','$certificate', '$organization', '$subnature', '$staticAttributes', $service, $servicePath)";
	 }
	 else {
	  $q = "INSERT INTO devices(id, devicetype, contextBroker,  kind, protocol, format, macaddress, model, producer, latitude, longitude,uri, visibility,  frequency, privatekey, certificate, mandatoryproperties,mandatoryvalues, organization, subnature, static_attributes, service, servicePath) " .
		 "VALUES('$id', '$devicetype', '$contextbroker', '$kind', '$protocol', '$format', '$macaddress', '$model', '$producer', '$latitude', '$longitude', '" . $result["content"] . "', '$visibility', '$frequency', '$privatekey','$certificate',1,1, '$organization', '$subnature', '$staticAttributes', $service, $servicePath)";
	 }

	 $r = mysqli_query($link, $q);
	 if($r) 
	 {
		$result["msg"] .= "\n Device $contextbroker/$id correctly inserted with uri " . $result["content"];
		$result["log"] .= "\r\n Device $contextbroker/$id correctly inserted with uri" . $result["content"] . "\r\n";
		
		// information to be passed to the interface
		$result["visibility"] = $visibility;	
		if($result["content"]==null) $result["active"]=false;  else $result["active"]=true;
		// end of information to be passed to the interface
		// $result["msg"] .= "prima della richiesta di update k1 e k2 ";
		// $result["log"] .= "prima della richiesta di update k1 e k2";

		if ($accessToken != "")
		{
			$ownmsg = array();
		        $eId=$organization.":".$contextbroker.":".$id;
			$ownmsg["elementId"]=$eId;
			$ownmsg["elementName"]=$id;				    
			$ownmsg["elementUrl"]=$result["content"];
			$ownmsg["elementDetails"]=array();
			$ownmsg["elementDetails"]["k1"]= $k1;
			$ownmsg["elementDetails"]["k2"]= $k2;
			if ($publickey!="") {
				$pub_key_str=str_replace("\n", "", file_get_contents($pathCertificate."/public/".$publickey));
				$ownmsg["elementDetails"]["publickey"]= substr($pub_key_str, 26, 216);
			}
			if ($edgegateway_type!="") $ownmsg["elementDetails"]["edgegateway_type"]= $edgegateway_type;
			if ($edgegateway_uri!="") $ownmsg["elementDetails"]["edgegateway_uri"]= $edgegateway_uri;					
			$ownmsg["elementDetails"]["contextbroker"]=$contextbroker;
			// $result["msg"] .= json_encode($ownmsg);
			// $result["log"] .= json_encode($ownmsg);
			registerOwnerShipDevice($eId, $ownmsg, $accessToken, $result);
		}
		// $result["msg"] .= "passata richiesta di update k1 e k2 ";
		// $result["log"] .= "passata richiesta di update k1 e k2 ";
		$ok=true;
		$q="";
		$a=0;$b=1;
		while ($a < count($listAttributes) && $ok)
		{
		  $att=$listAttributes[$a];  
		  if ($att->healthiness_criteria=="refresh_rate") $hc="value_refresh_rate";
		  else if ($att->healthiness_criteria=="different_values") $hc="different_values";
		  else $hc="value_bounds";
		 
		  $insertquery="INSERT INTO `event_values`(`cb`, `device`, `value_name`, `data_type`, `order`, `value_type`, `editable`,`value_unit`,`healthiness_criteria`,`$hc`) VALUES ('$contextbroker','$id','$att->value_name','$att->data_type','$b','$att->value_type','$att->editable','$att->value_unit','$att->healthiness_criteria','$att->healthiness_value');";
		  //echo $insertquery;
		  $r1 = mysqli_query($link, $insertquery);
		  if ($r1) 
		  {
				$result["msg"] .= "\n attribute $att->value_name correctly inserted";
				$result["log"] .= "\n attribute $att->value_name correctly inserted";
		  }
		  else 
		  {
			  $result["error_msg"] .= "Attribute $att->value_name was not inserted. "; 
			  $result["msg"] .= "<br/> attribute $att->value_name was not inserted <br/>" . generateErrorMessage($link); 
			  $result["log"] .= "\r\n attribute $att->value_name was not inserted $insertquery " . generateErrorMessage($link); 
			  $ok=false;
		  }
		  $b++;
		  $a++;
		}
		 if ($ok==true)
		 {
			 $result["status"]='ok';
		 }
		 else
		 {
		   $result["status"]='ko';
		 }
	 } 
	 else 
	 {
	   $result["status"]='ko';
	   $result["error_msg"] .= "Problem in inserting the device $id in the database. "; 
	   $result["msg"] .= "\n Problem in inserting the device $id:  <br/>" .  generateErrorMessage($link); 
	   $result["log"] .= "\r\n Problem in inserting the device $id:  $q  " .  generateErrorMessage($link);  
	 }
	}
	else
	{
		$result["status"]='ko';
		$result["error_msg"] = $servicePath . " is NOT a valid servicePath";
	}
      }
      else
      {
             $result["status"]='ko';
             $result["error_msg"] .= "Problem in inserting the device $id, device name already exists in deleted devices. "; 
             $result["msg"] .= "\n Problem in inserting the device $id, device name already exists in deleted devices"; 
             $result["log"] .= "\r\n Problem in inserting the device $id, device name already exists in deleted devices";
      }
    }
    else
    {
           $result["status"]='ko';
           $result["error_msg"] .= "Problem in inserting the device $id, related to the deleted_devices table. " ;
           $result["msg"] .= "\n Problem in inserting the device $id, related to the deleted_devices table:  <br/>" .  generateErrorMessage($link); 
           $result["log"] .= "\r\n Problem in inserting the device $id, related to the deleted_devices table:   " .  generateErrorMessage($link);  
    }
  }
  else{
    // limits reached, error message are already thrown  
  }
}


/* function for loging */

function format_result($draw, $number_all_row, $number_filter_row, $data, $msg, $log, $status) 
{
 	$output = array(
	 "draw"    => intval($draw),
	 "recordsTotal"  =>  $number_all_row,
	 "recordsFiltered" => $number_filter_row,  
	 "data"    => $data,
	 "msg" => $msg,
	 "log" => $log,
	 "status"=>$status
	);
	return $output;
}


function create_datatable_data($link,$request,$query,$where)
{
	$columns=$request["columns"];
	if(isset($request["search"]["value"]))
	{
		$query .= ' WHERE ';
		if ($where != "") $query .= $where . ' AND (';
 
		foreach ($columns as $col)
		{
			if (!in_array($col["name"], $request["no_columns"]))
					$query .= " " . $col["name"] . ' LIKE "%'.$request["search"]["value"].'%"  OR';	
		}
		$query= substr($query, 0, -1);
		$query= substr($query, 0, -1);
		if ($where != "") $query .=  ') ';
	}

	if(isset($request["order"]))
	{
		$query .= 'ORDER BY '.$columns[$request['order']['0']['column']]['name'] .' '.$request['order']['0']['dir'].'	';
	}
	
	$result = mysqli_query($link, $query);
	return $result;
}



function my_log($result) {  
  // my_log
  $fp=fopen($GLOBALS["pathLog"],"a");
  if (!$fp)
  {
	$result["status"]='ko';
	$result["error_msg"] = "\n Unable to open LOG file. Please contact an administrator";
  }
  else
  {
  	flock($fp,LOCK_EX);
	$output = date("Y-m-d h:i:sa") . ": ". $result["log"] . "\r\n";
	fwrite($fp,$output);
	unset($result["log"]);
	flock($fp,LOCK_UN);
	fclose($fp);
  }

  // return json encoded result
  echo json_encode($result);
}


//this routine return two information:
//1 or 0 that means if a certificate has been created (information to fill the db)
//status == ok or ko that means an error is reached (error to be notified to gui)
function registerCertificatePrivateKey($link, $cb, $deviceId, $model, $path, &$result, $username="")
{
	$result["status"]='ok';

	if ($username=="")
	{
		$username=$_SESSION['loggedUsername'];
	}

	$q = "select name from model where kgenerator = 'authenticated' and name ='$model';";
	$res = mysqli_query($link, $q);
	if ($res)
	{
	
	  $row = mysqli_fetch_assoc($res);
	  if ($row["name"]==$model)
	  {
		 $result["msg"] .= "\n the model $model is of type authenticated";
		 $result["log"] .= "\n the model $model is of type authenticated"; 
		
		 $cmd1="$path/generate-device-keys.sh $cb/$deviceId?".$username." $deviceId $path 2>&1";
		 $output = shell_exec($cmd1);

                 $result["msg"] .= "\n result of command " . $cmd1 . " is " . $output;
                 $result["log"] .= "\n result of  command " . $cmd1 . " is " . $output;
		
		//check error string
		foreach(preg_split("/((\r?\n)|(\r\n?))/", $output) as $line){
			if (strpos($line, "asn1 encoding routines:ASN1_mbstring_ncopy:string too long")!== false){ //openSSL error message returned if the CN is too long
				$result["status"]='ko';
                                $result["error_msg"] .= "\n IOT Device name too long";
                                $result["msg"] .= "\n IOT Device name too long";
                                $result["log"] .= "\n IOT Device name too long";
                                return 0;
			}
			else if (strpos($line, "error") !== false){
				$result["status"]='ko';				
				$result["error_msg"] .= "An error has been catched generating the device certificate: ".$line;
				$result["msg"] .= "\n an error has been returned from the script for certificate generation ".$line;
		                $result["log"] .= "\n an error has been returned from the script for certificate generation ".$line;
				return 0;
                	}
		}

		return 1;
	  }
	  else
	  {
		$result["msg"] .= "\n the model $model is NOT of type authenticated";
		$result["log"] .= "\n the model $model is NOT of type authenticated";
		return 0;
	  }
	}
	else
	{
		$result["status"]='ko';
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

     if($res){
         while($row = mysqli_fetch_assoc($res))
                {array_push($attributes, $row["data_type"]);
                }
     }
         return $attributes;
}

function generateAttributes($link,$name,$cb) {
     $query2 = "SELECT * FROM event_values WHERE cb = '$cb' AND device = '$name'";
     $res = mysqli_query($link, $query2) or die(mysqli_error($link));
     $attributes = array();
	 
	  if($res){
         while($row = mysqli_fetch_assoc($res))
                {
				  $rec=array();
				  $rec["cb"]=$row["cb"];
				  $rec["device"]=$row["device"];
				  $rec["value_name"]=$row["value_name"];
				  $rec["data_type"]=$row["data_type"];
				  $rec["value_type"]=$row["value_type"];
				  $rec["editable"]=$row["editable"];
				  $rec["value_unit"]=$row["value_unit"];
				  $rec["order"]=$row["order"];
				  $rec["healthiness_criteria"]=$row["healthiness_criteria"];
				  if($rec["healthiness_criteria"]=="refresh_rate")
				          $rec["healthiness_value"]=$row["value_refresh_rate"];
				  if($rec["healthiness_criteria"]=="different_values")
				          $rec["healthiness_value"]=$row["different_values"];
                  if($rec["healthiness_criteria"]=="within_bounds")
				          $rec["healthiness_value"]=$row["value_bounds"];
				  array_push($attributes, $rec);
                }
     }
	 //print_r($attributes);
	 return $attributes;
}


function generateErrorMessage($link)

{
  $error_number = mysqli_errno($link);
  $errmsg = "";
  
  switch ($error_number)
  {
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



function removeOwnerShipDevice($elementId,$token,&$result) {
	try
	{
		$url=$GLOBALS["ownershipURI"] . "ownership-api/v1/delete/?type=IOTID&elementId=" . $elementId . "&accessToken=" . $token;
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
		if(strpos($http_response_header[0], '200') !== false)
		{
		   $result["status"]='ok';
		   $result["msg"] .= "\n the deletion of the ownership succeded";
		   $result["log"] .= "\n the deletion of the ownership succeded"; //  . $local_result;

		}
		else
		{
		   $result["status"]='ok';
		   $result["error_msg"] .= "Error in deleting the ownership. ";
		   $result["msg"] .= "\n error in deleting the ownership";
		   $result["log"] .= "\n error in deleting the ownership";
		   
		}	
	} 
	catch (Exception $ex)
	{
		$result["status"]='ko';
		$result["error_msg"] .= 'Error in removing the ownership. ';
		$result["msg"] .= '\n error in removing the ownership ';
		$result["log"] .= '\n error in removing the ownership ' . $ex;
	} 
}

function removeOwnerShipObject($elementId,$token,$object,&$result) {
	try
	{
		$result["log"] .= "\n\r Deletion of the ownership invoked on $elementId $object";
		$url=$GLOBALS["ownershipURI"] . "ownership-api/v1/delete/?type=".$object."&elementId=" . $elementId . "&accessToken=" . $token;
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
		if(strpos($http_response_header[0], '200') !== false)
		{
		   $result["status"]='ok';
		   $result["msg"] .= "\n the deletion of the ownership succeded";
		   $result["log"] .= "\n the deletion of the ownership succeded"; //  . $local_result;

		}
		else
		{
		   $result["status"]='ok';
		   $result["error_msg"] .= "Error in deleting the ownership. ";
		   $result["msg"] .= "\n error in deleting the ownership";
		   $result["log"] .= "\n error in deleting the ownership";
		   
		}	
	} 
	catch (Exception $ex)
	{
		$result["status"]='ko';
		$result["error_msg"] .= 'Error in removing the ownership. ';
		$result["msg"] .= '\n error in removing the ownership ';
		$result["log"] .= '\n error in removing the ownership ' . $ex;
	} 
}

function registerOwnerShipDevice($elementId, $msg, $token, &$result) { //	$msg["elementId"]=$elementId;
	$msg["elementType"]="IOTID"; // $msg["elementName"]=$elementName; // $msg["accessToken"]=$token;
	 $result["msg"] .="\n the element id is ".$elementId;
 
	try
	{
		$url= $GLOBALS["ownershipURI"] . "ownership-api/v1/register/?accessToken=" . $token;
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
	 
		if(strpos($http_response_header[0], '200') !== false)
		{
		   $result["status"]='ok';
		   $result["msg"] .= "\n the registration of the ownership succeded ".$elementId;
		    $result["log"] .= "\n the registration of the ownership succeded" .$elementId." ". $local_result;
		}
		else
		{
			$result["status"]='ok';
			$result["error_msg"] .= "The registration is NOT possible. Reached limit of IoT Devices. ";
			$result["msg"] .= "\n The registration is NOT possible. Reached limit of IoT Devices (".json_decode($local_result)->limit.")";
            $result["log"] .= "\n The registration is NOT possible. Reached limit of IoT Devices (".json_decode($local_result)->limit.")";		
			// $result["msg"] .= "\n error in registering the ownership";
			// $result["log"] .= "\n error in registering the ownership" . $url . json_encode($msg) . $local_result;
		}	 
	} 
	catch (Exception $ex)
	{
	$result["status"]='ko';
	$result["error_msg"] .= 'Error in registering the ownership. ';
	$result["msg"] .= '\n error in registering the ownership ';
	$result["log"] .= '\n error in registering the ownership ' . $ex;
	} 	
}

function registerOwnerShipObject($msg, $token, $object, &$result) { 
	try
	{
		$url= $GLOBALS["ownershipURI"] . "ownership-api/v1/register/?accessToken=" . $token;
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
	 
		if(strpos($http_response_header[0], '200') !== false)
		{
		   $result["status"]='ok';
		   $result["msg"] .= "\n the registration of the ownership succeded";
		   $result["log"] .= "\n the registration of the ownership succeded" . $local_result;
		}
		else
		{
			$result["status"]='ok';
			$result["error_msg"] .= "The registration is NOT possible. Reached limit of IoT Devices. ";
			$result["msg"] .= "\n The registration is NOT possible. Reached limit of IoT Devices (".$local_result.")";
			$result["log"] .= "\n The registration is NOT possible. Reached limit of IoT Devices ";//(".json_decode($local_result)->limit.")";		
		}	 
	} 
	catch (Exception $ex)
	{
	$result["status"]='ko';
	$result["error_msg"] .= 'Error in registering the ownership. ';
	$result["msg"] .= '\n error in registering the ownership ';
	$result["log"] .= '\n error in registering the ownership ' . $ex;
	} 	
}

function delegateDeviceValue($elementId, $contextbroker, $value_name, $user, $userdelegated, $groupdelegated, $token, $k1, $k2, &$result) {
	$msg["elementId"]= $elementId;
	if ($value_name!== NULL){//delegate a sensor scenario
                $msg["variableName"]=$value_name;
                $msg["elementType"]="ServiceURI"; // ServiceUri
        }
        else {//delegate a device scenario
                $msg["elementType"]="IOTID"; // ServiceUri
        }
if ($userdelegated!=="") $msg["usernameDelegated"]=$userdelegated; // $msg["accessToken"]=$token;
if ($groupdelegated!=="") {
								
	$msg["groupnameDelegated"]=$groupdelegated.",".$GLOBALS["ldapBaseName"];
}
if ($k1!="" && $k2!=""){
    	$msg["delegationDetails"]=array();
	$msg["delegationDetails"]["k1"]= $k1;
	$msg["delegationDetails"]["k2"]= $k2;
}
	  
	try
	{
$url= $GLOBALS["delegationURI"] . "datamanager/api/v1/username/". urlencode($user) . "/delegation?accessToken=" . $token .
"&sourceRequest=iotdirectory";

 $options = array(
                          'http' => array(
					  'header' => "Content-Type: application/json;charset=utf-8\r\n".
                                                        "Access-Control-Allow-Origin: *",
                                          'method' => 'POST',
                                          'content' => json_encode($msg),
                                          'ignore_errors' => true,
                                          'timeout' => 30
                          )
                );


	 $context = stream_context_create($options);
	 $local_result = file_get_contents($url, false, $context);
	 
     if(strpos($http_response_header[0], '200') !== false)
		 {
		   $elem = json_decode($local_result);
		   $result["status"]='ok';
		   $result["delegationId"]= $elem->id;
		   
		   $result["msg"] .= "\n the registration of the delegation succeded";
		    $result["log"] .= "\n the registration of the delegation succeded" . $url . " result " . $local_result . " msg " . 
json_encode($msg);
                 }
		 else
		 {
		   $result["status"]='ko';
		   $result["error_msg"] .= "Error in the delegation. ";
		   $result["msg"] .= "\n error in the delegation";
		   $result["log"] .= "\n error in the delegation" . $url . " result " . $local_result . " msg " . json_encode($msg);
		   
		 }	 
	
	} 
	catch (Exception $ex)
	{
	$result["status"]='ko';
	$result["error_msg"] .= 'Error in the delegation of the sensor. ';
	$result["msg"] .= '\n error in the delegation of the sensor ';
	$result["log"] .= '\n error in the delegation of the sensor ' . $ex;
	} 
	
}

function delegateObject($elementId, $user, $userdelegated, $groupdelegated, $object, $token, &$result) {
	$msg["elementId"]= $elementId;
    $msg["elementType"]=$object; 
    
    if ($userdelegated!=="") 
        $msg["usernameDelegated"]=$userdelegated; 

    if ($groupdelegated!=="") {
        $msg["groupnameDelegated"]=$groupdelegated.",".$GLOBALS["ldapBaseName"];
    }
	  
	try
	{

        $url= $GLOBALS["delegationURI"] . "datamanager/api/v1/username/". urlencode($user) . "/delegation?accessToken=" . $token ."&sourceRequest=iotdirectory";

        $options = array(
                          'http' => array(
					  'header' => "Content-Type: application/json;charset=utf-8\r\n".
                                                        "Access-Control-Allow-Origin: *",
                                          'method' => 'POST',
                                          'content' => json_encode($msg),
                                          'ignore_errors' => true,
                                          'timeout' => 30
                          )
                );


        $context = stream_context_create($options);
        $local_result = file_get_contents($url, false, $context);
    
	 
     
        if(strpos($http_response_header[0], '200') !== false)
        {
		   $elem = json_decode($local_result);
		   $result["status"]='ok';
		   $result["delegationId"]= $elem->id;
		   
		   $result["msg"] .= "\n the registration of the delegation succeded";
		    $result["log"] .= "\n the registration of the delegation succeded" . $url . " result " . $local_result . " msg " . json_encode($msg);
        
         
        }
		 else
		 {
		   $result["status"]='ko';
		   $result["error_msg"] .= "Error in the delegation. ";
		   $result["msg"] .= "\n error in the delegation";
		   $result["log"] .= "\n error in the delegation" . $url . " result " . $local_result . " msg " . json_encode($msg);
		   
		 }	 
	
	} 
	catch (Exception $ex)
	{
	$result["status"]='ko';
	$result["error_msg"] .= 'Error in the delegation. ';
	$result["msg"] .= '\n error in the delegation';
	$result["log"] .= '\n error in the delegation' . $ex;
	} 
	
}

function removeDelegationValue($token, $user, $delegationId, &$result) {
	$local_result="";
    try
	{  
        $url= $GLOBALS["delegationURI"] . "datamanager/api/v1/username/". urlencode($user) . "/delegation/" . $delegationId . "?accessToken=" . $token . 
"&sourceRequest=iotdirectory";
          $options = array(
                          'http' => array(
					 'header' => "Content-Type: application/json;charset=utf-8\r\n".
                                                        "Access-Control-Allow-Origin: *",
                                          'method' => 'DELETE',
                                          'ignore_errors' => true,
                                          'timeout' => 30
                          )
                );

	 $context = stream_context_create($options);
		  $local_result = file_get_contents($url, false, $context);
	 if(strpos($http_response_header[0], '200') !== false)
		 {
		   $result["status"]='ok';
		   $result["msg"] .= '\n The delegation has been removed ';
		   $result["log"] .= '\n The delegation has been removed ' . $local_result;
		 }
		 else
		 {
		   $result["status"]='ko';
		   $result["error_msg"] .= "Error in removing the delegation. ";
		   $result["msg"] .= "\n error in removing the delegation";
		   $result["log"] .= "\n error in removing the delegation";
		   
		 }	 
	} 
	catch (Exception $ex)
	{
	$result["status"]='ko';
	$result["error_msg"] .= 'catched Error in removing the delegation. ';
	$result["msg"] .= '\n catched error in removing the delegation ';
	$result["log"] .= '\n catched error in removing the delegation ' . $ex;
	}
}

function getDelegatedDevice($token, $user, &$result) {
	$local_result="";
        $mykeys = array();
	try
	{
          $url= $GLOBALS["delegationURI"] . "datamanager/api/v2/username/". urlencode($user) . "/delegated?accessToken=" . $token . 
"&sourceRequest=iotdirectory";
          $local_result = file_get_contents($url);
	} 
	catch (Exception $ex)
	{
	$result["status"]='ko';
	$result["error_msg"] .= 'Error in accessing the delegation. ';
	$result["msg"] .= '\n error in accessing the delegation ';
	$result["log"] .= '\n error in accessing the delegation ' . $ex;
	}
    if(strpos($http_response_header[0], '200') == true || strpos($http_response_header[0], '204') == true)
	{
		$lists = json_decode($local_result);
		for ($i=0;$i < count($lists); $i++)
		{
			if ( isset( $lists[$i]->elementType)&&($lists[$i]->elementType=="ServiceURI" || $lists[$i]->elementType=="IOTID"))
			{
                      
			  $a = $lists[$i]->elementId;
                               if (isset($lists[$i]->delegationDetails))
                               {
				   $delegationDetails=json_decode($lists[$i]->delegationDetails);
                                   $mykeys[$a]= array("usernameDelegator" => $lists[$i]->usernameDelegator, "delegationId" => $lists[$i]->id, "kind" => 'specific', "k1" => $delegationDetails->k1, "k2" => $delegationDetails->k2);
                               }
                               else
                               {
                                   $mykeys[$a]= array("usernameDelegator" => $lists[$i]->usernameDelegator, "delegationId" => $lists[$i]->id, "kind" => 'specific', "k1" => "", "k2" => "");
                               }



			}
		}
		
		try
		{
          $url= $GLOBALS["delegationURI"] . "datamanager/api/v1/username/ANONYMOUS/delegated?accessToken=" . $token . 
"&sourceRequest=iotdirectory";
          $local_result = file_get_contents($url);

		  if(strpos($http_response_header[0], '200') == true || strpos($http_response_header[0], '204') == true)
	{
		    $lists = json_decode($local_result);
			for ($i=0;$i < count($lists); $i++)
			{
				if ( isset( $lists[$i]->elementType)&&($lists[$i]->elementType=="ServiceURI" || $lists[$i]->elementType=="IOTID"))
				{
                  $a = $lists[$i]->elementId;
			      if (isset($lists[$i]->delegationDetails) && isset($lists[$i]->delegationDetails->k1))
                     $mykeys[$a]=array("usernameDelegator" => $lists[$i]->usernameDelegator, "delegationId" => $lists[$i]->id, "kind" => 'anonymous', "k1" => $lists[$i]->delegationDetails->k1, "k2" => $lists[$i]->delegationDetails->k2);
                  else 
                     $mykeys[$a]= array("usernameDelegator" => $lists[$i]->usernameDelegator, "delegationId" => $lists[$i]->id, "kind" => 'anonymous', "k1" => "", "k2" => "");
                  
                 
				}
			}
			$result["status"]='ok';
		    $result["delegation"]=$mykeys;
			$result["msg"] .= '\n identified ' . count($lists) . ' anonymous delegated devices \n' . json_encode($mykeys);
			$result["log"] .= '\n identified ' . count($lists) . ' anonymous delegated devices \n' . json_encode($mykeys);
		}
		 else {
		   $result["status"]='ko';
		   $result["error_msg"] .= 'Errors in reading delegations anonymous. ';
		   $result["msg"] .= '\n errors in reading delegations anonymous' . $local_result . $url ."------". $http_response_header[0];
		   $result["log"] .= '\n errors in reading delegations anonymous' . $local_result . $url ."------" . $http_response_header[0];
	   }
			
		}	 
		catch (Exception $ex)
		{
		 $result["status"]='ko';
		 $result["error_msg"] .= 'Error in accessing the delegation. ';
		 $result["msg"] .= '\n error in accessing the delegation ' . $ex;
		  $result["log"] .= '\n error in accessing the delegation ' . $ex;
		}
		
       }
       else {
		   $result["status"]='ko';
		   $result["error_msg"] .= 'Errors in reading delegations personal. ';
		   $result["msg"] .= '\n errors in reading delegations personal ' . $local_result . $url ."------" .  $http_response_header[0];
		   $result["log"] .= '\n errors in reading delegations personal' . $local_result . $url ."------" . $http_response_header[0];
	   }	   
       // return $listCondDevice;
}

function getDelegatedObject($token, $user, $object, &$result) 
{
	$local_result="";
    $mykeys = array();
	try
	{
          
        $url= $GLOBALS["delegationURI"] . "datamanager/api/v2/username/". urlencode($user) . "/delegated?accessToken=" . $token ."&sourceRequest=iotdirectory";  
        $local_result = file_get_contents($url);
	} 
	catch (Exception $ex)
	{
	$result["status"]='ko';
	$result["error_msg"] .= 'Error in accessing the delegation. ';
	$result["msg"] .= '\n error in accessing the delegation ';
	$result["log"] .= '\n error in accessing the delegation ' . $ex;
	}
    if(strpos($http_response_header[0], '200') == true || strpos($http_response_header[0], '204') == true)
	{
        $lists = json_decode($local_result);
		for ($i=0;$i < count($lists); $i++)
		{
			if ( isset( $lists[$i]->elementType)&& $lists[$i]->elementType==$object)
			{
                $a = $lists[$i]->elementId;
                $mykeys[$a]= array("usernameDelegator" => $lists[$i]->usernameDelegator, "delegationId" => $lists[$i]->id, "kind" => 'specific');   
			}
		}
		try
		{
          $url= $GLOBALS["delegationURI"] . "datamanager/api/v1/username/ANONYMOUS/delegated?accessToken=" . $token ."&sourceRequest=iotdirectory";
          $local_result = file_get_contents($url);
		  if(strpos($http_response_header[0], '200') == true || strpos($http_response_header[0], '204') == true)
          {
		    $lists = json_decode($local_result);
			for ($i=0;$i < count($lists); $i++)
			{
				if ( isset( $lists[$i]->elementType)&& $lists[$i]->elementType==$object)
                {
                    $a = $lists[$i]->elementId; 
                    $mykeys[$a]=array("usernameDelegator" => $lists[$i]->usernameDelegator, "delegationId" => $lists[$i]->id, "kind" => 'anonymous'); 
				}
			}
			$result["status"]='ok';
		    $result["delegation"]=$mykeys;
            
			//$result["msg"] .= '\n identified ' . count($lists) . ' anonymous delegated devices \n' . json_encode($mykeys);
			//$result["log"] .= '\n identified ' . count($lists) . ' anonymous delegated devices \n' . json_encode($mykeys);
		}
		 else {
		   $result["status"]='ko';
		   //$result["msg"] .= '\n errors in reading delegations anonymous' . $local_result . $url ."------". $http_response_header[0];
		   //$result["log"] .= '\n errors in reading delegations anonymous' . $local_result . $url ."------" . $http_response_header[0];
	   }
			
		}	 
		catch (Exception $ex)
		{
		 $result["status"]='ko';
		 $result["error_msg"] .= 'Error in accessing the delegation. ';
		 $result["msg"] .= '\n error in accessing the delegation ' . $ex;
		  $result["log"] .= '\n error in accessing the delegation ' . $ex;
		}
		
       }
      else {
		   $result["status"]='ko';
		   $result["error_msg"] .= 'Errors in reading delegations personal. ';
		   $result["msg"] .= '\n errors in reading delegations personal ' . $local_result . $url ."------" .  $http_response_header[0];
		   $result["log"] .= '\n errors in reading delegations personal' . $local_result . $url ."------" . $http_response_header[0];
	   }	   
       
}

//this function reuse an existent function
//TODO use the check apis from mypersonaldata
function checkDelegationObject($username, $token, $elementId, $elementType, &$result)
{
	error_log("elementID".$elementId);	

    $toreturn=false;

	getDelegatedObject($token, $username, $elementType, $result);

	error_log("DELEG:".json_encode($result));

	if ($result["status"]=="ok")
	{

        if (isset($result["delegation"][$elementId])){
					error_log("TRUE");
                    $toreturn=true;
		}

		
		$result["status"]='ok';
		$result["msg"] .= '\n check delegation '.$toreturn;
		$result["log"] .= '\n check delegation '.$toreturn;
	}
	else
	{
            $result["status"]='ko';
            $result["error_msg"] = 'Error in accessing the delegation. ';
            $result["msg"] = '\n error in accessing the delegation';
            $result["log"] = '\n error in accessing the delegation';
	}

	unset($result["delegation"]);

    return $toreturn;
}


//TODO show the k1 and k2 also if delegator==user (the owner of the delegation should be able to access k1 and k2 delegations)
function getDelegatorDevice($token, $user, &$result, $eId) {
	$local_result="";
        $mykeys = array();
	try
	{
          $url= $GLOBALS["delegationURI"] . "datamanager/api/v2/username/". urlencode($user) . "/delegator?accessToken=" . $token . 
"&sourceRequest=iotdirectory";
          $local_result = file_get_contents($url);
	  if(strpos($http_response_header[0], '200') == true || strpos($http_response_header[0], '204') == true)
	  {
		$lists = json_decode($local_result);
          
		for ($i=0;$i < count($lists); $i++)
		{
			if ( isset( $lists[$i]->elementType)&&($lists[$i]->elementType=="ServiceURI" || $lists[$i]->elementType=="IOTID"))
			{
                          $a = $lists[$i]->elementId;
                          if ($eId== $a)
                           {
		         	if (isset($lists[$i]->delegationDetails) && isset($lists[$i]->delegationDetails->k1))
                               {
                                 if (isset($lists[$i]->usernameDelegated))
                                 {
                                    $mykeys[] = array("userDelegated" => $lists[$i]->usernameDelegated, "delegationId" => $lists[$i]->id, "k1" => $lists[$i]->delegationDetails->k1, "k2" => $lists[$i]->delegationDetails->k2);
                                 }
                                 else
                                 {
                                   $mykeys[] = array("groupDelegated" => $lists[$i]->groupnameDelegated, "delegationId" => $lists[$i]->id, "k1" => $lists[$i]->delegationDetails->k1, "k2" => $lists[$i]->delegationDetails->k2);
                                 }
                               }
                                else
                               {
                                 if (isset($lists[$i]->usernameDelegated))
                                  {
                                    $mykeys[] = array("userDelegated" => $lists[$i]->usernameDelegated, "delegationId" => $lists[$i]->id, "k1" => "", "k2" => "");
                                 }
                                 else {
                                   $mykeys[] = array("groupDelegated" => $lists[$i]->groupnameDelegated, "delegationId" => $lists[$i]->id, "k1" => "", "k2" => "");
                                 }
                               }

                         } 
			}
               
		}
		$result["status"]='ok';

		$result["delegation"]=$mykeys;
		$result["msg"] .= '\n identified ' . count($lists) . ' delegations\n' . $local_result .  json_encode($mykeys);
		$result["log"] .= '\n identified ' . count($lists) . ' delegations\n' . json_encode($mykeys);
	  }
	  else
	 {
	   $result["status"]='ko';
	   $result["error_msg"] .= 'Error in accessing the delegation. ';
	   $result["msg"] .= '\n error in accessing the delegation ' . $http_response_header[0];
	   $result["log"] .= '\n error in accessing the delegation ' . $http_response_header[0];
	 }	  
		  

	} 
	catch (Exception $ex)
	{
	$result["status"]='ko';
	$result["error_msg"] .= 'Error happened while accessing the delegation. ';
	$result["msg"] .= '\n error in accessing the delegation ';
	$result["log"] .= '\n error in accessing the delegation ' . $ex;
	
	}
       // $result["msg"] .= $local_result;
	
}

function getDelegatorObject($token, $user, &$result,$object, $delegationId) {
	$local_result="";    
    $mykeys = array();
	try
	{
        $url= $GLOBALS["delegationURI"] . "datamanager/api/v2/username/".urlencode($user)."/delegator?accessToken=".$token."&sourceRequest=iotdirectory";  
        $local_result = file_get_contents($url);
	  
        if(strpos($http_response_header[0], '200') == true || strpos($http_response_header[0], '204') == true)
        {
            $lists = json_decode($local_result);
            for ($i=0;$i < count($lists); $i++)
            {
                if ( isset( $lists[$i]->elementType)&& $lists[$i]->elementType==$object)
                {
                          
                    $a = $lists[$i]->elementId;      
                    if ($delegationId== $a)
                    {
		         	
                                 
                        if (isset($lists[$i]->usernameDelegated))
                                  
                        {                           
                            $mykeys[] = array("userDelegated" => $lists[$i]->usernameDelegated, "delegationId" => $lists[$i]->id);
                                 
                        }
                                 
                        else 
                        {   
                            $mykeys[] = array("groupDelegated" => $lists[$i]->groupnameDelegated, "delegationId" => $lists[$i]->id);
                                 
                        }
                        
                         
                    } 
			}
               
		}
		$result["status"]='ok';

		$result["delegation"]=$mykeys;
		$result["msg"] .= '\n identified ' . count($lists) . ' delegations\n' . $local_result .  json_encode($mykeys);
		$result["log"] .= '\n identified ' . count($lists) . ' delegations\n' . json_encode($mykeys);
	  }
	  else
	 {
	   $result["status"]='ko';
	   $result["error_msg"] .= 'Error in accessing the delegation. ';
	   $result["msg"] .= '\n error in accessing the delegation ' . $http_response_header[0];
	   $result["log"] .= '\n error in accessing the delegation ' . $http_response_header[0];
	 }	  
		  

	} 
	catch (Exception $ex)
	{
	$result["status"]='ko';
	$result["error_msg"] .= 'Error happened when accessing the delegation. ';
	$result["msg"] .= '\n error in accessing the delegation ';
	$result["log"] .= '\n error in accessing the delegation ' . $ex;
	
	}
	
}

function getOwnerShipDevice($token, &$result) 
{
	$listCondDevice = "";
	$local_result="";
	$mykeys = array();
	try
	{
		$url= $GLOBALS["ownershipURI"] . "ownership-api/v1/list/?type=IOTID&accessToken=" . $token;

		$local_result = file_get_contents($url);
		$result["log"] .= $local_result;
		if(strpos($http_response_header[0], '200') == true || strpos($http_response_header[0], '204') == true)
		{

			$lists = json_decode($local_result);
			for ($i=0;$i < count($lists); $i++)
			{
				if (!isset($lists[$i]->deleted))
				{
					if(strpos($lists[$i]->elementId,":")>0)
					{
						$org=substr($lists[$i]->elementId,0,strpos($lists[$i]->elementId,":"));
						$cb_name=substr($lists[$i]->elementId,strpos($lists[$i]->elementId,":")+1, strlen($lists[$i]->elementId));
						$cb=substr($cb_name,0,strpos($cb_name,":"));
						$name=substr($cb_name,strpos($cb_name,":")+1, strlen($cb_name));
					}
					else
					{
						$name=$lists[$i]->elementId;
					}
             
					$listCondDevice .= " (id = '" . $name . "' AND contextbroker = '" . $lists[$i]->elementDetails->contextbroker . "') ";
					if ($i != count($lists)-1) $listCondDevice .= " OR ";
              
					$gtwtype = "";
					$gtwuri = ""; 						  
					if (isset($lists[$i]->elementDetails->edgegateway_type)) $gtwtype=$lists[$i]->elementDetails->edgegateway_type;
					if (isset($lists[$i]->elementDetails->edgegateway_uri)) $gtwuri=$lists[$i]->elementDetails->edgegateway_uri;
						 
					$mykeys[$lists[$i]->elementId]= array("k1"=> $lists[$i]->elementDetails->k1,
                                                                "k2" => $lists[$i]->elementDetails->k2,
                                                                "cb" => $lists[$i]->elementDetails->contextbroker,
                                                                "owner" => $lists[$i]->username,
																"edgegateway_type" =>$gtwtype, 
																"edgegateway_uri" =>$gtwuri);
					$result["username"]=$lists[$i]->username;
				}
			}
			$result["status"]='ok';
        	$result["keys"]=$mykeys;
			$result["msg"] .= '\n identified ' . count($lists) . ' private devices \n';
			$result["log"] .= '\n identified ' . count($lists) . ' private devices \n'; //  .  $listCondDevice . json_encode($mykeys);
		}	
	} 
	catch (Exception $ex)
	{
		$result["status"]='ko';
		$result["error_msg"] .= 'Error in accessing the ownership. ';
		$result["msg"] .= '\n error in accessing the ownership ';
		$result["log"] .= '\n error in accessing the ownership ' . $ex;
	}

	return $listCondDevice;
}

function getOwnerShipObject($token, $object, &$result) 
{
	$listCondDevice = "";
	$local_result="";
	$mykeys = array();
	try
	{
		$url= $GLOBALS["ownershipURI"] . "ownership-api/v1/list/?type=".$object."&accessToken=" . $token;
		$local_result = file_get_contents($url);
		$result["log"] .= $local_result;
       
		if(strpos($http_response_header[0], '200') == true || strpos($http_response_header[0], '204') == true)
		{
		$lists = json_decode($local_result);
		for ($i=0;$i < count($lists); $i++)
		{
			if (!isset($lists[$i]->deleted))
			{
				$org=substr($lists[$i]->elementId,0,strpos($lists[$i]->elementId,":"));
				$name=substr($lists[$i]->elementId,strpos($lists[$i]->elementId,":")+1, strlen($lists[$i]->elementId));
				$listCondDevice .= " (name = '" . $name . "' AND organization = '" . $org . "') ";
				if ($i != count($lists)-1) 
					$listCondDevice .= " OR ";
				$mykeys[$lists[$i]->elementId]= array("owner" => $lists[$i]->username);
				$result["username"]=$lists[$i]->username; 
			}
		}
		$result["status"]='ok';
        $result["keys"]=$mykeys;
		$result["msg"] .= '\n identified ' . count($lists) . ' private objects \n';
		$result["log"] .= '\n identified ' . count($lists) . ' private objects \n'; 
       }	
	} 
	catch (Exception $ex)
	{
		$result["status"]='ko';
		$result["error_msg"] .= ' Error in accessing the ownership. ';
		$result["msg"] .= '\n error in accessing the ownership ';
		$result["log"] .= '\n error in accessing the ownership ' . $ex;
	}

	return $listCondDevice;
}

function checkOwnershipObject($token, $elementId, $elementType, &$result)
{
	$toreturn=false;

	try
	{
		$url= $GLOBALS["ownershipURI"] . "ownership-api/v1/list/?type=".$elementType."&accessToken=" . $token;
		error_log("URI:".$url);
		$local_result = file_get_contents($url);
		
		if(strpos($http_response_header[0], '200') == true || strpos($http_response_header[0], '204') == true)
		{
			error_log("OWN:".$local_result);
			$lists = json_decode($local_result);
			for ($i=0;$i < count($lists); $i++)
			{
				if ( !isset($lists[$i]->deleted) && 
					  isset($lists[$i]->elementId) && ($lists[$i]->elementId == $elementId) &&
					  isset($lists[$i]->elementType) && ($lists[$i]->elementType == $elementType)
					)
				{
					$toreturn=true;
				}
			}
			$result["status"]='ok';
			$result["msg"] = '\n check ownership '.$toreturn;
			$result["log"] = '\n check ownership '.$toreturn;
		}
		else 
		{
			$result["status"]='ko';
			$result["error_msg"] = 'Error in accessing the ownership. ';
			$result["msg"] = '\n error in accessing the ownership ';
			$result["log"] = '\n error in accessing the ownership. Returned code:  '.$http_response_header[0];
		}
    }
    catch (Exception $ex)
	{
		$result["status"]='ko';
		$result["error_msg"] .= 'Error in accessing the ownership. ';
		$result["msg"] .= '\n error in accessing the ownership ';
		$result["log"] .= '\n error in accessing the ownership ' . $ex;
    }

    return $toreturn;
}


function generatelabels($link) {
     $query2 = "SELECT value_type FROM value_types ORDER BY value_type";
     $res = mysqli_query($link, $query2) or die(mysqli_error($link));
     $labels = array();

          if($res){
         while($row = mysqli_fetch_assoc($res))
                {array_push($labels, $row["value_type"]);
                }
     }
         return $labels;
}

function generateunits($link) {
     $query2 = "SELECT DISTINCT value_unit_default FROM value_types ORDER BY value_unit_default";
     $res = mysqli_query($link, $query2) or die(mysqli_error($link));
     $labels = array();

          if($res){
         while($row = mysqli_fetch_assoc($res))
                {array_push($labels, $row["value_unit_default"]);
                }
     }
         return $labels;
}

function retrieveFromDictionary($type,&$result){

	$local_result="";
	try
	{
		if (isset($GLOBALS["processLoaderURI"])){
			$url= $GLOBALS["processLoaderURI"] . "dictionary/?type=".$type;
		}
		else {
			$url="default-".$type.".json";
		}
		$local_result = file_get_contents($url);
		$result["log"] .= $local_result;

		//TODO how to catch an 504
		if (($local_result!== FALSE) && (
			(!isset($GLOBALS["processLoaderURI"])) || //default scenario from file
			((isset($GLOBALS["processLoaderURI"])) && (strpos($http_response_header[0], '200') == true || strpos($http_response_header[0], '204') == true))
			))
		{
			$dictionary = json_decode($local_result);
			if ($dictionary->{'code'}=='200') {
				$result["status"]='ok';
				$result["content"]=$dictionary->{'content'};
				$result["msg"] .='\n ok, returning dictionary';
				$result["log"] .='\n ok, returning dictionary';
			}
			else {
				$result["status"]='ko';
				$result["error_msg"]=$dictionary{'result'};
				$result["msg"] .='\n ko NOT returning dictionary';
				$result["log"] .='\n ko NOT returning dictionary';
			}
		}
		else{
			$result["status"]='ko';
                        $result["error_msg"]=" Dictionary NOT reacheable";
                        $result["msg"] .='\n ko dictionary not reacheable';
                        $result["log"] .='\n ko dictionary not reacheable';
		}
	}
	catch (Exception $ex)
	{
		$result["status"]='ko';
		$result["error_msg"] .= ' Error in accessing the dictionary. ';
		$result["msg"] .= '\n error in accessing the dictionary ';
		$result["log"] .= '\n error in accessing the dictionary ' . $ex;
	}
	return $result;
}

function retrieveAvailableStaticAttribute($subnature, &$result){
	$local_result="";
        try
        {
		$url= $GLOBALS["knowledgeBaseURI"] . "api/v1/iot/list-static-attr?subnature=".$subnature;


                $local_result = file_get_contents($url);
                $result["log"] .= $local_result;

                //TODO how to catch an 504
                if (($local_result!== FALSE) && (strpos($http_response_header[0], '200') == true || strpos($http_response_header[0], '204') == true))
                {
                	$result["status"]='ok';
                        $result["content"]=$local_result;
                        $result["msg"] .='\n ok, returning dictionary';
                        $result["log"] .='\n ok, returning dictionary';
                }
                else{
                        $result["status"]='ko';
                        $result["error_msg"]=" ServiceMap not reacheable NOT reacheable";
                        $result["msg"] .='\n ko SM not reacheable';
                        $result["log"] .='\n ko SM not reacheable';
                }
        }
        catch (Exception $ex)
        {
                $result["status"]='ko';
                $result["error_msg"] .= ' Error in accessing the SM. ';
                $result["msg"] .= '\n error in accessing the SM ';
                $result["log"] .= '\n error in accessing the SM ' . $ex;
        }
        return $result;
}

/* ****FUNCTIONS FOR THE REGISTRATION OF A DEVICE IN THE CONTEXT BROKER AND IN THE KNOWLEDGE BASE ****** */
	
	function insert_ngsi($name, $type, $contextbroker, $kind, $protocol, $format, $model, $latitude, $longitude,$visibility, $frequency, 
$listnewAttributes, $ip, $port, &$result,  $service="", $servicePath="")
	{
	   $res = "ok";
	   $msg_orion=array();
      
	  $msg_orion["id"]= $name;
      $msg_orion["type"]= $type;
      $msg_orion["latitude"]=array();
      $msg_orion["longitude"]=array();
      $msg_orion["latitude"]["value"]= $latitude;
      $msg_orion["longitude"]["value"]= $longitude;
      $msg_orion["latitude"]["type"]= "float";
      $msg_orion["longitude"]["type"]= "float";
      $a=0;
      while ($a < count($listnewAttributes))
      {
         $att=$listnewAttributes[$a];
		 $msg_orion[$att->value_name]=array();
		 $msg_orion[$att->value_name]["value"]="";
 		 $msg_orion[$att->value_name]["type"]=$att->data_type;
         $a++;
      }
	  $msg_orion["model"]=array();
	  $msg_orion["model"]["value"]= $model;
 	  $msg_orion["model"]["type"]= "string";
        
	
	  $url_orion="http://$ip:$port/v2/entities/";
          //echo "stefano ".json_encode($msg_orion) .  $url_orion;
      
      try
         {
			// Setup cURL
			$ch = curl_init($url_orion);
			$authToken = 'OAuth 2.0 token here';
			
			$httpheader=array(
                                        'Authorization: '.$authToken,
                                        'Content-Type: application/json');
			if ($service!="")	
				array_push($httpheader,'Fiware-Service: ' . $service);
			if ($servicePath!="")
				array_push($httpheader,'Fiware-ServicePath: ' . $servicePath);

			curl_setopt_array($ch, array(
    			  CURLOPT_POST => TRUE,
    			  CURLOPT_RETURNTRANSFER => TRUE,
    			  CURLOPT_HTTPHEADER => $httpheader, 
			  CURLOPT_POSTFIELDS => json_encode($msg_orion)
		    	));

		    // Send the request
		    $response_orion = curl_exec($ch);

		    // Check for errors
		    if($response_orion === FALSE){
    			// die(curl_error($ch));
				$result["error_msg"].= "Error in the connection with the ngsi context broker. ";
				$result["msg"].= "\n error in the connection with the ngsi context broker";
				$result["log"].= "\n error in the connection with the ngsi context broker";
				$res='ko';
			}
            else
			{
		    // Decode the response
		     $responseData = json_decode($response_orion, TRUE);
			 $result["status"]='ok';
		     $result["msg"] .= '\n response from the ngsi context broker ';
			 $result["log"] .= '\n response from the ngsi context broker ' . $response_orion;
			 $res='ok';
			 
            } 
		    // Print the date from the response
		    // echo $responseData['published'];
                    // echo $response_orion;
            }
            catch (Exception $ex)
            {
		       $result["status"]='ko';
		       $result["error_msg"] .= 'Error in connecting with the ngsi context broker. ';
		       $result["msg"] .= ' error in connecting with the ngsi context broker ';
			   $result["log"] .= ' error in connecting with the ngsi context broker ' . $ex;
			   $res="ko";
            }
            return $res;
	}
	
	function insert_mqtt($name, $type, $contextbroker, $kind, $protocol, $format, $latitude, $longitude, $visibility, $frequency, 
$listnewAttributes, $ip, $port, &$result)
	{
	  return "ok";
	}
	
	function insert_amqp($name, $type, $contextbroker, $kind, $protocol, $format, $latitude, $longitude, $visibility, $frequency, 
$listnewAttributes, $ip, $port, &$result)
	{
	  return "ok";
	}

	function canBeModified($name, $type, $contextbroker, $kind, $protocol, $format, $macaddress, $model, $producer, $latitude, $longitude, 
$visibility, $frequency, $listnewAttributes, &$result)
	{
	  $error=false;
	  if ($name==null || $name=="")
	      {$error=true; $result["msg"].= "\n id not specified";$result["error_msg"].= " id not specified. ";$result["log"].= "\n id not specified";}
      if ($contextbroker==null || $contextbroker=="")
		  {$error=true; $result["msg"].= "\n cb not specified";$result["error_msg"].= "cb not specified. "; $result["log"].= "\n cb not specified";}
	  if ($type==null || $type=="")
		{$error=true; $result["msg"].= "\n type not specified";$result["error_msg"].= "type not specified. ";$result["log"].= "\n type not specified";}
	  if (!($kind=="sensor" || $kind=="actuator"))
		{$error=true; $result["msg"].= "\n kind not specified";$result["error_msg"].= "kind not specified. ";$result["log"].= "\n kind not specified";}
	  if ($latitude < -90 && $latitude>90)
		{$error=true; $result["msg"].= "\n latitude not correct ";$result["error_msg"].= "latitude not correct. "; $result["log"].= "\n latitude not correct ";}
	  if ($longitude < -180 && $longitude>180)
		{$error=true; $result["msg"].= "\n longitude not correct ";$result["error_msg"].= "longitude not correct. ";$result["log"].= "\n longitude not correct ";}
	  if (!($protocol=="ngsi" || $protocol=="mqtt" || $protocol=="amqp" || $protocol == "ngsi w/MultiService"))
		{$error=true; $result["msg"].= "\n protocol not correct ";$result["error_msg"].= "protocol not correct. ";$result["log"].= "\n protocol not correct ";}
	  if (count($listnewAttributes)==0)
		{$error=true; $result["msg"].= "\n at list one attribute";$result["error_msg"].= " at least one attribute is required. ";$result["log"].= "\n at list one attribute";}
	    
	   foreach($listnewAttributes as $att)
		  {
		   if ($att["data_type"]==null || $att["data_type"]=="")
		        {$error=true; $result["msg"].= "\n data type for attribute $att[value_name] not specified";$result["error_msg"].= " data type for attribute $att[value_name] not specified. ";
				              $result["log"].= "\n data type for attribute $att[value_name] not specified";}
                       if ($att["value_unit"]==null || $att["value_unit"]=="")
                               {$error=true; $result["msg"].= "\n value unit for attribute $att[value_name] not specified";$result["error_msg"].= " value unit for attribute $att[value_name] not specified. ";
                                             $result["log"].= "\n value unit for attribute $att[value_name] not specified";}
			if ($att["value_type"]==null || $att["value_type"]=="")
				{$error=true; $result["msg"].= "\n value type for attribute $att[value_name] not specified";$result["error_msg"].= " value type for attribute $att[value_name] not specified. ";
				              $result["log"].= "\n value type for attribute $att[value_name] not specified";}
		    if (!($att["healthiness_criteria"]=="refresh_rate" || $att["healthiness_criteria"]=="different_values" ||
			      $att["healthiness_criteria"]=="within_bounds")) {$error=true;}
		    if ($att["healthiness_criteria"]=="refresh_rate" && $att["healthiness_value"]=="")
				{$error=true; $result["msg"].= "\n healthiness_criteria for attribute $att[value_name] not specified";$result["error_msg"].= "healthiness_criteria for attribute $att[value_name] not specified. ";
				$result["log"].= "\n healthiness_criteria for attribute $att[value_name] not specified";}
			// echo "valore di erroe" . $error;
			// if ($att->healthiness_criteria=="different_values" && ($att->different_values=="" || !is_int($att->different_values))) 
//{$error=true;}
		  }


	   if ($error) return false;
	   return true;
	}


	
	function canBeRegistered($name, $type, $contextbroker, $kind, $protocol, $format, $macaddress, $model, $producer, $latitude, $longitude, 
$visibility, $frequency, $listnewAttributes, $subnature, $staticAttr, &$result)
	{
	  $error=false;
	  if ($name==null || $name=="")
		{ $error=true; 
	      $result["error_msg"].= "id not specified. ";
	      $result["msg"].= "\n id not specified";
		  $result["log"].= "\n id not specified";
		}
      if ($contextbroker==null || $contextbroker=="")
		 {$error=true; 
	      $result["error_msg"].= "cb not specified. ";
	      $result["msg"].= "\n cb not specified";
		  $result["log"].= "\n cb not specified";
		 }
	  if ($type==null || $type=="")
		{$error=true; 
	     $result["error_msg"].= "type not specified. ";
	     $result["msg"].= "\n type not specified";
		 $result["log"].= "\n type not specified";}
	  if (!($kind=="sensor" || $kind=="actuator"))
		{$error=true; 
	     $result["error_msg"].= "kind not specified. ";
	     $result["msg"].= "\n kind not specified";
		 $result["log"].= "\n kind not specified";}
	  if ($latitude < -90 && $latitude>90)
		{$error=true; 
	     $result["error_msg"].= "latitude not correct. "; 
	     $result["msg"].= "\n latitude not correct "; 
		 $result["log"].= "\n latitude not correct ";}
	  if ($longitude < -180 && $longitude>180)
		{
			$error=true; 
			$result["error_msg"].= "longitude not correct. ";
			$result["msg"].= "\n longitude not correct ";
			$result["log"].= "\n longitude not correct ";
		}
	  if (!($protocol=="ngsi" || $protocol=="mqtt" || $protocol=="amqp" || $protocol == "ngsi w/MultiService"))
		{
			$error=true; 
			$result["error_msg"].= "protocol not correct. ";
			$result["msg"].= "\n protocol not correct ";
			$result["log"].= "\n protocol not correct ";}
	  if (count($listnewAttributes)==0)
	     { $error=true; 
	       $result["error_msg"].= "at least one attribute must be added. ";
	       $result["msg"].= "\n at list one attribute";
		   $result["log"].= "\n at list one attribute";}
	    
	   foreach($listnewAttributes as $att)
		  {
		   if ($att->data_type==null || $att->data_type=="")
				{
					$error=true; 
					$result["error_msg"].= "data type for attribute $att->value_name not specified. ";
					$result["msg"].= "\n data type for attribute $att->value_name not specified";
					$result["log"].= "\n data type for attribute $att->value_name not specified";
				}
		   if ($att->value_unit==null || $att->value_unit=="")
				{
				 $error=true;
                               $result["error_msg"].= "value unit for attribute $att->value_name not specified. ";
                               $result["msg"].= "\n value unit for attribute $att->value_name not specified";
                                   $result["log"].= "\n value unit for attribute $att->value_name not specified";
				}
		   if ($att->value_type==null || $att->value_type=="")
			{
				$error=true; 
				$result["error_msg"].= "value type for attribute $att->value_name not specified. ";
				$result["msg"].= "\n value type for attribute $att->value_name not specified";
				$result["log"].= "\n value type for attribute $att->value_name not specified";
			}
		   if (!($att->healthiness_criteria=="refresh_rate" || $att->healthiness_criteria=="different_values" ||
			      $att->healthiness_criteria=="within_bounds")) 
		     {
				 $error=true;
				 $result["error_msg"].= "wrong healthiness_criteria. ";
				 $result["msg"].= "\n wrong healthiness_criteria";
				 $result["log"].= "\n wrong healthiness_criteria";
			 }
		    if ($att->healthiness_criteria=="refresh_rate" && $att->healthiness_value=="")
				{
					$error=true; 
					$result["error_msg"].= "healthiness_criteria for attribute $att->value_name not specified. ";
					$result["msg"].= "\n healthiness_criteria for attribute $att->value_name not specified";
                    $result["log"].= "\n healthiness_criteria for attribute $att->value_name not specified";
				}
		  }


	   if ($error) return false;
	   return true;
	}
	
	function registerKB($link, $name, $type, $contextbroker, $kind, $protocol, $format, $macaddress, $model, $producer, $latitude,
			$longitude, $visibility, $frequency, $listnewAttributes, $subnature, $staticAttributes,&$result, $shouldbeRegistered, $organization, $kbUrl="", $service="", $servicePath="")
	{
		$result["status"]='ok';
		
		if (canBeRegistered($name, $type, $contextbroker, $kind, $protocol, $format, $macaddress, $model, $producer, $latitude, $longitude, $visibility, $frequency, $listnewAttributes, $subnature, $staticAttributes,$result))
		{
			$query="SELECT * from contextbroker WHERE name = '$contextbroker'";
			$r = mysqli_query($link, $query);
			if (!$r) {
				$result["status"]='ko';
				$result["error_msg"] .="Error in reading data from context broker.";
				$result["msg"] .= ' error in reading data from context broker ' . mysqli_error($link);
				$result["log"] .= ' error in reading data from context broker ' . mysqli_error($link) . $query;
				return 1;
			}
			$rowCB = mysqli_fetch_assoc($r);
			if($rowCB["kind"]=='external')
				$shouldbeRegistered='no';
			$ip=$rowCB["ip"];
			$port=$rowCB["port"];
			  
			$msg=array();
			$msg["id"]= $name;
			if ($rowCB["protocol"] == "ngsi w/MultiService") 
				$msg["id"] = $service . "." . $servicePath . "." . $name;
			$msg["type"]= $type;
			$msg["kind"]= $kind;
			$msg["protocol"]= $protocol;
			$msg["format"]= $format;
			$msg["macaddress"]= $macaddress;
			$msg["model"]= $model;
			$msg["producer"]= $producer;
			$msg["latitude"]= $latitude;
			$msg["longitude"]= $longitude;
			$msg["frequency"]= $frequency;
			$msg["organization"]= $organization;
              		$msg["ownership"]= $visibility;
			$msg["subnature"]=$subnature;
	
			foreach(json_decode(stripcslashes($staticAttributes)) as $stAtt){
				$msg[$stAtt[0]]=$stAtt[1];
			}



			$msg["broker"]=array();
			$msg["broker"]["name"]=$contextbroker;
			$msg["broker"]["type"]=$rowCB["protocol"];

			$msg["broker"]["ip"]=$rowCB["ip"];
			$msg["broker"]["port"]=$rowCB["port"];
			$msg["broker"]["login"]=($rowCB["login"]==null)?"":$rowCB["login"];
			$msg["broker"]["password"]=($rowCB["password"]==null)?"":$rowCB["password"];
			$msg["broker"]["latitude"]=$rowCB["latitude"];
			$msg["broker"]["longitude"]=$rowCB["longitude"];
			$msg["broker"]["created"]=$rowCB["created"];
			  
			$myAttrs=array();
			$i=1;
			foreach($listnewAttributes as $att) {
				$myatt = array();
				$myatt["value_name"] =$att->value_name;
				$myatt["data_type"] =$att->data_type;
				$myatt["value_type"]=$att->value_type;
				$myatt["value_unit"]=$att->value_unit;
				$myatt["healthiness_criteria"]=$att->healthiness_criteria;
				if ($att->healthiness_criteria=="refresh_rate")
					$myatt["value_refresh_rate"]=$att->healthiness_value;
					// to be fixed
					//else if ($att["healthiness_criteria"]=="different_values")
					// $myatt["different_values"]=$att["different_values"];
					//else $myatt["value_bounds"]=$att["value_bounds"];
				$myatt["order"]=$i++;
				$myAttrs[]=$myatt;
			}	 
			$msg["attributes"]=$myAttrs;

			$encoda=json_encode($msg, JSON_UNESCAPED_SLASHES);
			error_log("Sending to insertKB:".$encoda);
			$encoda=str_replace('\\\\','\\\\u005C', $encoda);
			$encoda=str_replace('\"','\\\\u0022', $encoda);
			error_log("Sending to insertKB (after pulizia):".$encoda);

			try {
				if($kbUrl=="") 
					$url= $_SESSION['kbUrl']."iot/insert";
				else 
					$url= $kbUrl."iot/insert";
				$options = array(
					'http' => array(
						'header' => "Content-Type: application/json;charset=utf-8",
						'header' => "Access-Control-Allow-Origin: *",
						'method' => 'POST',
						'content' => $encoda,
						'timeout' => 30
					)
				);
				$context = stream_context_create($options);
				$local_result = @file_get_contents($url, false, $context);

				error_log("Returning from insertKB:".$local_result);

				if (($local_result!="errore")&&(strlen($local_result)>0)) {
					$result["status"]='ok';
					$result["content"]=$local_result;
					$result["msg"] .= "\n an URI has been generated by the KB: " . $local_result." ".$url." xxx ".$organization; 
					$result["log"] .= "\n an URI has been generated by the KB: " . $local_result." ".$url;
				}
				else {
					$result["status"]='ko';
					$result["content"]="";
					$result["msg"] .= "\n no URI has been generated by the KB" . $local_result;
					$result["log"] .= "\n no URI has been generated by the KB" . $local_result;
				}
				// registration of the device in the corresponding context broker
				if (($local_result!="errore")&&(strlen($local_result)>0)) {
					if (!isset($shouldbeRegistered) || (isset($shouldbeRegistered)&& $shouldbeRegistered=='yes')) {
						switch ($protocol) {
							case "ngsi":
								$res = insert_ngsi($name, $type, $contextbroker, $kind, $protocol, $format, $model, $latitude, $longitude, 
										$visibility, $frequency, $listnewAttributes, $ip, $port, $result);
								break;
							case "ngsi w/MultiService":
								$res = insert_ngsi($name, $type, $contextbroker, $kind, $protocol, $format, $model, $latitude, $longitude, 
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
					}
					else {
						$res='no';//should not be registered
					}
				}
				else {
					$res='ko';//uri has not been generated
				}
			} catch (Exception $ex) {
				$result["status"]='ko';
				$result["error_msg"] .= 'Error in connecting with KB. ';
				$result["msg"] .= '\n error in connecting with KB ';
				$result["log"] .= '\n error in connecting with KB ' . $ex;
			} 

			if ($res=="ok") {
				$result["msg"] .= "\n ok registration in the context broker";
				$result["log"] .= "\n ok registration in the context broker";
			}
			elseif ($res=="ko") {
				$result["status"]='ko';
				$result["error_msg"] .= "No registration in the context broker. ";
				$result["msg"] .= "\n no registration in the context broker";
				$result["log"] .= "\n no registration in the context broker";
			}
			else {// the value is no -- no registration in the context broker
				$result["status"]='ok';
				$result["msg"] .= "\n no registration in the context broker is required";
				$result["log"] .= "\n no registration in the context broker is required";
			}    
			return 1;
		}
		else {
			$result["error_msg"].="Error in the validation w.r.t. the KB. ";
			$result["msg"].="\n error in the validation w.r.t. the KB";
			$result["log"].="\n error in the validation w.r.t. the KB";
			$result["status"]='ko';
			return 1;
		}
	} // end of function registerKB
                
	// ****FUNCTIONS FOR THE MODIFICATION OF THE REGISTRATION OF A DEVICE IN THE KNOWLEDGE BASE AND IN THE CONTEXT BROKER ****************** 
				
	function update_ngsi($name, $type, $contextbroker, $kind, $protocol, $format, $model, $latitude, $longitude, $visibility, $frequency,
		$listnewAttributes, $deletedAttributes, $ip, $port, $uri, &$result, $service="", $servicePath="" )
	{
		$res = "ok";
		$msg_orion=array();
      
		// $msg_orion["id"]= $name;
		// $msg_orion["type"]= $type;
		$msg_orion["latitude"]=array();
		$msg_orion["longitude"]=array();
		$msg_orion["latitude"]["value"]= $latitude;
		$msg_orion["longitude"]["value"]= $longitude;
		$msg_orion["latitude"]["type"]= "float";
		$msg_orion["longitude"]["type"]= "float";
		$a=0;
		while ($a < count($listnewAttributes))
		{
			$att=$listnewAttributes[$a];
			if (is_object($att))
			{
				$msg_orion[$att->value_name]=array();
				$msg_orion[$att->value_name]["value"]="";
				$msg_orion[$att->value_name]["type"]=$att->data_type;
			 }
			 else
			 {
				$msg_orion[$att["value_name"]]=array();
				$msg_orion[$att["value_name"]]["value"]="";
				$msg_orion[$att["value_name"]]["type"]=$att["data_type"];
			}			 
		        $a++;
		}
		if ($model != null && $model != "")
		{
			$msg_orion["model"]=array();
			$msg_orion["model"]["value"]= $model;
			$msg_orion["model"]["type"]= "string";
		}  
    		if ($protocol == "ngsi w/MultiService"){
			// get the name from id
			$name = explode(".", $name)[2];
		}
 
		$url_orion="http://$ip:$port/v2/entities/$name/attrs";
		try
		{
			// Setup cURL
			$ch = curl_init($url_orion);
			$authToken = 'OAuth 2.0 token here';

			$httpheader=array(
                                        'Authorization: '.$authToken,
                                        'Content-Type: application/json');
                        if ($service!="")
                                array_push($httpheader,'Fiware-Service: ' . $service);
                        if ($servicePath!="")
                                array_push($httpheader,'Fiware-ServicePath: ' . $servicePath);

			curl_setopt_array($ch, array(
	    			CURLOPT_POST => TRUE,
    				CURLOPT_RETURNTRANSFER => TRUE,
    				CURLOPT_HTTPHEADER => $httpheader, 
				CURLOPT_POSTFIELDS => json_encode($msg_orion)
			));

			$response_orion = curl_exec($ch);

			// Check for errors
			if($response_orion === FALSE){
				$result["error_msg"].= "Error in the connection with the ngsi context broker. ";
				$result["msg"].= "\n error in the connection with the ngsi context broker";
				$result["log"].= "\n error in the connection with the ngsi context broker" . curl_error($ch);
				$res='ko';
			}
			else
			{
				//eventually remove attributes TODO uniform with above
				$b=0;
		                while ($b < count($deletedAttributes))
                		{
		                        $att=$deletedAttributes[$b];
					// Setup cURL
		                        $ch = curl_init($url_orion."/".$att->value_name);

		                        if ($service!="")
                		                array_push($httpheader,'Fiware-Service: ' . $service);
		                        if ($servicePath!="")
                		                array_push($httpheader,'Fiware-ServicePath: ' . $servicePath);

		                        curl_setopt_array($ch, array(
                		                CURLOPT_CUSTOMREQUEST => "DELETE",
                                		CURLOPT_RETURNTRANSFER => TRUE
		                        ));

                		        $response_orion = curl_exec($ch);
				
	
					// Check for errors
		                        if($response_orion === FALSE){
                		                $result["error_msg"].= "Error in the DELETE connection with the ngsi context broker. ";
                                		$result["msg"].= "\n error in the DELETE connection with the ngsi context broker";
		                                $result["log"].= "\n error in the DELETE connection with the ngsi context broker" . curl_error($ch);
                		                $res='ko';
						break;
		                        }
                        		$b++;
		                }		

				if($response_orion !== FALSE)
				{
					$result["status"]='ok';
					$result["msg"] .= '\n response from the ngsi context broker ';
					$result["log"] .= '\n response from the ngsi context broker ' . $response_orion;
					$res='ok';
				}
			} 
		}
		catch (Exception $ex)
		{
		       $result["status"]='ko';
		       $result["error_msg"] .= ' Error in connecting with the ngsi context broker. ';
		       $result["msg"] .= ' error in connecting with the ngsi context broker ';
		       $result["log"] .= ' error in connecting with the ngsi context broker ' . $ex;
			$res="ko";
		}
		return $res;
	}
	
	function update_mqtt($name, $type, $contextbroker, $kind, $protocol, $format, $latitude, $longitude, $visibility, $frequency, 
$listnewAttributes, $ip, $port,$uri,&$result)
	{
	  return "ok";
	}
	
	function update_amqp($name, $type, $contextbroker, $kind, $protocol, $format, $latitude, $longitude, $visibility, $frequency, 
$listnewAttributes, $ip, $port,$uri,&$result)
	{
	  return "ok";
	}
	
function updateKB($link, $name, $type, $contextbroker, $kind, $protocol, $format, $macaddress, $model, $producer, $latitude, $longitude, 
	$visibility, $frequency, $attributes, $deletedAttributes, $uri, $organization, $subnature, $staticAttributes, &$result, $service="", $servicePath="", $kbUrl="") 
{
  $result["status"]='ok';
        
  if (canBeRegistered($name, $type, $contextbroker, $kind, $protocol, $format, $macaddress, $model, $producer, $latitude, $longitude, 
$visibility, $frequency, $attributes,  $subnature, $staticAttributes,$result))
  {
	  $query="SELECT * from contextbroker WHERE name = '$contextbroker'";
	  $r = mysqli_query($link, $query);
	 
	  if (!$r) {
		$result["status"]='ko';
		$result["error_msg"] .= 'Error in reading data from context broker. ';
		$result["msg"] .= ' error in reading data from context broker ' . mysqli_error($link);
		$result["log"] .= ' error in reading data from context broker ' . mysqli_error($link) . $query;
		return 1;
	  }
	  $rowCB = mysqli_fetch_assoc($r);
	  $ip=$rowCB["ip"];
	  $port=$rowCB["port"];
	  
	  $msg=array();
	  $msg["id"]= $name;
	  $msg["type"]= $type;
	  $msg["kind"]= $kind;
	  $msg["protocol"]= $protocol;
	  $msg["format"]= $format;
	  $msg["macaddress"]= $macaddress;
	  $msg["model"]= $model;
	  $msg["producer"]= $producer;
	  $msg["latitude"]= $latitude;
	  $msg["longitude"]= $longitude;
	  $msg["frequency"]= $frequency;
	  $msg["organization"]= $organization;
      
	  // $msg["visibility"]= $visibility;
	  $msg["ownership"]= $visibility;
	  
	  // if ($msg["visibility"]=='private') $msg["owner"]=$owner;
	  $msg["broker"]=array();
	  $msg["broker"]["name"]=$contextbroker;
	  $msg["broker"]["type"]=$rowCB["protocol"];

	  $msg["broker"]["ip"]=$rowCB["ip"];
	  $msg["broker"]["port"]=$rowCB["port"];
	  $msg["broker"]["login"]=($rowCB["login"]==null)?"":$rowCB["login"];
	  $msg["broker"]["password"]=($rowCB["password"]==null)?"":$rowCB["password"];
	  $msg["broker"]["latitude"]=$rowCB["latitude"];
	  $msg["broker"]["longitude"]=$rowCB["longitude"];
	  $msg["broker"]["created"]=$rowCB["created"];
	   // $msg["attributes"]=array();
	 

			$msg["subnature"]=$subnature;

                        foreach(json_decode(stripcslashes($staticAttributes)) as $stAtt){
                                $msg[$stAtt[0]]=$stAtt[1];
                        }
 
	  $myAttrs=array();
	  $i=1;
	  foreach($attributes as $att)
	  {
		$myatt = array();
		$myatt["value_name"] =$att->value_name;
		$myatt["data_type"] =$att->data_type;
		$myatt["value_type"]=$att->value_type;
		$myatt["value_unit"]=$att->value_unit;
		$myatt["healthiness_criteria"]=$att->healthiness_criteria;
		if ($att->healthiness_criteria=="refresh_rate") $myatt["value_refresh_rate"]=$att->healthiness_value;
		else if ($att->healthiness_criteria=="different_values") $myatt["different_values"]=$att->different_values;
		else $myatt["value_bounds"]=$att->value_bounds;
		$myatt["order"]=$i++;
		$myAttrs[]=$myatt;
	}

		$msg["attributes"]=$myAttrs;

                $encoda=json_encode($msg, JSON_UNESCAPED_SLASHES);
                error_log("Sending to updateKB:".$encoda);
                $encoda=str_replace('\\\\','\\\\u005C', $encoda);
                $encoda=str_replace('\"','\\\\u0022', $encoda);
                error_log("Sending to updateKB (after pulizia):".$encoda);
	
		try
		{
			if ($kbUrl=="")	
				$url= $_SESSION['kbUrl']."iot/insert";
			else
				$url= $kbUrl."iot/insert";

			$options = array(
			  'http' => array(
						  'header' => "Content-Type: application/json;charset=utf-8",
						  'header' => "Access-Control-Allow-Origin: *",
						  'method' => 'POST',
			  'content' => $encoda,
						  'timeout' => 30)
			);
			$context = stream_context_create($options);
			$local_result = @file_get_contents($url, false, $context);

			error_log("Returning from insertKB:".$local_result);
		} 
		catch (Exception $ex)
		{
			$result["status"]='ko';
			$result["error_msg"] .= ' Error in connecting with KB. ';
			$result["msg"] .= ' error in connecting with KB ';
			$result["log"] .= ' error in connecting with KB ' . $ex;
		} 
	 if ($local_result!="errore")
	 {
	   $result["status"]='ok';
	   $result["content"]=$local_result;
	   $result["msg"] = "\n an URI has been generated by the KB";
	   $result["log"] = "\n an URI has been generated by the KB";
	 }
	 else
	 {
	   $result["status"]='ok';
	   $result["content"]="";
	   $result["msg"] .= "\n no URI has been generated by the KB";
	   $result["log"] .= "\n no URI has been generated by the KB";
	 }		 
	 
	/* update of the device in the corresponding context broker */
	if ($local_result!="errore")
	{
	if( $rowCB["kind"]!='external'){  
            switch ($protocol)
              {
               case "ngsi":
                    $res = update_ngsi($name, $type, $contextbroker, $kind, $protocol, $format, $model, $latitude, $longitude, $visibility, $frequency, 
        $attributes, $deletedAttributes, $ip, $port, $uri, $result);
                    break;
		case "ngsi w/MultiService":
			$res = update_ngsi($name, $type, $contextbroker, $kind, $protocol, $format, $model, $latitude, $longitude, $visibility, $frequency,
        $attributes, $deletedAttributes, $ip, $port, $uri, $result, $service, $servicePath);
                    break;
               case "mqtt":
                    $res = update_mqtt($name, $type, $contextbroker, $kind, $protocol, $format, $latitude, $longitude, $visibility, 
        $frequency, $attributes, $ip, $port,$uri, $result);
                     break;
                case "amqp":
                    $res = update_amqp($name, $type, $contextbroker, $kind, $protocol, $format, $latitude, $longitude, $visibility, 
        $frequency, $attributes, $ip, $port,$uri,$result);
                     break;
               }
        
        if ($res=="ok")
            {
             $result["msg"] .= "\n ok updated in the context broker";
             $result["log"] .= "\n ok updated in the context broker";
            }
            else
            {
              $result["status"]='ko';
              $result["error_msg"] .= "No update in the context broker. ";
              $result["msg"] .= "\n no update in the context broker";
              $result["log"] .= "\n no update in the context broker";
            }
        }
        else{
            $result["msg"] .= "\n context broker external, not updated";
	        $result["log"] .= "\n context broker external, not updated";

        }
	}
	
	return 1;
  }
  else
  {
   $result["error_msg"].="Error in the validation w.r.t. the KB. ";
   $result["msg"].="\n error in the validation w.r.t. the KB";
   $result["log"].="\n error in the validation w.r.t. the KB";
   $result["status"]='ko';
   return 1;
  }	
} // end of function updateKB

/* ****FUNCTIONS FOR THE DELETION OF THE REGISTRATION OF A DEVICE IN THE KNOWLEDGE BASE AND IN THE CONTEXT BROKER ****************** */


	function delete_ngsi($name, $type, $contextbroker, $kind, $protocol, $format, $model, $latitude, $longitude, $visibility, $frequency, 
$listnewAttributes, $ip, $port,$uri, &$result, $service="", $servicePath="")
	{
	if ($protocol == "ngsi w/MultiService"){
		//dev_log("delete_ngsi: multiservice device detected");
		// get the name from id
		$name = explode(".", $name)[2];
		//dev_log("delete_ngsi: device name: " . $name);
	}

	   $res = "ok";
	   $url_orion="http://$ip:$port/v2/entities/$name";
      
      try
         {
			// Setup cURL
			$ch = curl_init($url_orion);
			$authToken = 'OAuth 2.0 token here';

			$httpheader=array(
                                        'Authorization: '.$authToken
                                        );
                        if ($service!="")
                                array_push($httpheader,'Fiware-Service: ' . $service);
                        if ($servicePath!="")
                                array_push($httpheader,'Fiware-ServicePath: ' . $servicePath);


			curl_setopt_array($ch, array(
    			CURLOPT_CUSTOMREQUEST => 'DELETE',
    			CURLOPT_RETURNTRANSFER => TRUE,
    			CURLOPT_HTTPHEADER =>$httpheader, 
				// CURLOPT_POSTFIELDS => json_encode($msg_orion)
		    ));

		    // Send the request
		    $response_orion = curl_exec($ch);

		    // Check for errors
			if($response_orion === FALSE){
    			// die(curl_error($ch));
				$result["msg"].= "\n error in the connection with the ngsi context broker";
				$result["log"].= "\n error in the connection with the ngsi context broker";
				$res='ko';
			}
            else
			{
		    // Decode the response
		     $responseData = json_decode($response_orion, TRUE);
			 $result["status"]='ok';
		    $result["msg"] .= '\n response from the ngsi context broker ';
			$result["log"] .= '\n response from the ngsi context broker ' . $response_orion;
			$res='ok';
			 
            }
		    
		    // Print the date from the response
		    // echo $responseData['published'];
            }
              catch (Exception $ex)
            {
		       $result["status"]='ko';
		       $result["error_msg"] .= 'Error in connecting with the ngsi context broker. ';
		       $result["msg"] .= ' error in connecting with the ngsi context broker ';
		       $result["log"] .= ' error in connecting with the ngsi context broker ' . $ex;
			   $res="ko";
            }
            // echo json_encode($result);
            return $res;
	}
	
	function delete_mqtt($name, $type, $contextbroker, $kind, $protocol, $format, $latitude, $longitude, $visibility, $frequency, 
$listnewAttributes, $ip, $port,$uri, &$result)
	{
	  return "ok";
	}
	
	function delete_amqp($name, $type, $contextbroker, $kind, $protocol, $format, $latitude, $longitude, $visibility, $frequency, 
$listnewAttributes, $ip, $port,$uri, &$result)
	{
	  return "ok";
	}
	


	
	
function deleteKB($link, $name, $contextbroker, $kbUrl="", &$result, $service="", $servicePath="") {
	
	$result["status"]='ok';
  
	$listnewAttributes=generateAttributes($link, $name, $contextbroker);
	   
	$query = "SELECT d.organization, d.uri, d.id, d.devicetype AS entityType, d.kind, d.format, d.macaddress, d.model, d.producer, d.protocol, d.longitude,d.subnature, d.static_attributes, 
d.latitude, d.visibility, d.frequency, d.service, d.servicePath, cb.name, cb.protocol as type, cb.ip, cb.port, cb.login, cb.password, cb.latitude as cblatitude, 
cb.longitude as cblongitude, cb.created, cb.kind as cbkind FROM devices d JOIN contextbroker cb ON d.contextBroker = cb.name WHERE d.deleted is null and 
d.contextBroker='$contextbroker' and d.id='$name';";
	   
	$r_init = mysqli_query($link, $query);

	if (!$r_init) {
		$result["status"]='ko';
		$result["error_msg"] .= 'Error in reading data from context broker and device ' . mysqli_error($link);
		$result["msg"] .= '\n error in reading data from context broker and device ' . mysqli_error($link);
		$result["log"] .= '\n error in reading data from context broker and device ' . mysqli_error($link) . $query;
		return 1;
	}
  
	$row = mysqli_fetch_assoc($r_init);
	$name =$row["id"];
        $organization =$row["organization"];
	$type =$row["entityType"];
	$kind =$row["kind"];
	$protocol =$row["protocol"];
	$format =$row["format"];
	$macaddress =$row["macaddress"];
	$model =$row["model"];
	$producer =$row["producer"];
	$latitude =$row["latitude"];
	$longitude =$row["longitude"];
	$visibility = $row['visibility'];
	$frequency= $row['frequency'];
	$ip=$row["ip"];
	$port=$row["port"];
	$uri=$row["uri"];
	$subnature=$row["subnature"];
	$staticAttributes=$row["static_attributes"];

	$result["msg"] .="$name, $type, $contextbroker, $kind, $protocol, $format, $macaddress, $model, $producer, $latitude, $longitude, 
$visibility, $frequency,". count($listnewAttributes);
    
	if (canBeModified($name, $type, $contextbroker, $kind, $protocol, $format, $macaddress, $model, $producer, $latitude, $longitude, 
$visibility, $frequency, $listnewAttributes, $result))
	  {
		  /* msg for the Knowledge base + registration on the KB */
		  $msg=array();
		  $msg["id"]= $name;
		  if ($protocol == "ngsi w/MultiService") 
			$msg["id"] = $service . "." . $servicePath . "." . $name;
		  $msg["type"]= $type;
		  $msg["kind"]= $kind;
		  $msg["protocol"]= $protocol;
		  $msg["format"]= $format;
		  $msg["macaddress"]= $macaddress;
		  $msg["model"]= $model;
		  $msg["producer"]= $producer;
		  $msg["latitude"]= $latitude;
		  $msg["longitude"]= $longitude;
		  $msg["frequency"]= $frequency;
		  $msg["organization"]= $organization;
		  $msg["uri"]= $uri;
		  $msg["ownership"]= $visibility;
		  
		  $msg["broker"]=array();
		  $msg["broker"]["name"]=$contextbroker;
		  $msg["broker"]["type"]=$row["protocol"];

		  $msg["broker"]["ip"]=$row["ip"];
		  $msg["broker"]["port"]=$row["port"];
		  $msg["broker"]["login"]=($row["login"]==null)?"":$row["login"];
		  $msg["broker"]["password"]=($row["password"]==null)?"":$row["password"];
		  $msg["broker"]["latitude"]=$row["cblatitude"];
		  $msg["broker"]["longitude"]=$row["cblongitude"];
		  $msg["broker"]["created"]=$row["created"];
		  
		  $myAttrs=array();
		  $i=1;
		  foreach($listnewAttributes as $att)
		  {
			$myatt = array();
			$myatt["value_name"] =$att["value_name"];
			$myatt["data_type"] =$att["data_type"];
			$myatt["value_type"]=$att["value_type"];
			$myatt["value_unit"]=$att["value_unit"];
			$myatt["healthiness_criteria"]=$att["healthiness_criteria"];
			if ($att["healthiness_criteria"]=="refresh_rate") 
				$myatt["value_refresh_rate"]=$att["healthiness_value"];
			else if ($att["healthiness_criteria"]=="different_values") $myatt["different_values"]=$att["healthiness_value"];
				 else $myatt["value_bounds"]=$att["healthiness_value"];
			$myatt["order"]=$att["order"];
			$myAttrs[]=$myatt;
		  }	 
		  $msg["attributes"]=$myAttrs;
		  
		  $msg["subnature"]=$subnature;
                  foreach(json_decode(stripcslashes($staticAttributes)) as $stAtt){
                                $msg[$stAtt[0]]=$stAtt[1];
                  }

		  $encoda=json_encode($msg, JSON_UNESCAPED_SLASHES);
                  error_log("Deleting to insertKB:".$encoda);
                  $encoda=str_replace('\\\\','\\\\u005C', $encoda);
                  $encoda=str_replace('\"','\\\\u0022', $encoda);
                  error_log("Deleting to insertKB (after pulizia):".$encoda);

		try
		{
			if($kbUrl=="") {
				$url= $_SESSION['kbUrl']."iot/delete";
			}
			else {
				$url=$kbUrl."iot/delete";
			}
			 
			 $options = array(
					  'http' => array(
							  'header' => "Content-Type: application/json;charset=utf-8",
							  'header' => "Access-Control-Allow-Origin: *",
							  'method' => 'POST',
							  'content' => $encoda,
							  'timeout' => 30
					  )
				);
			 $context = stream_context_create($options);
			 $local_result = @file_get_contents($url, false, $context);

		} 
		catch (Exception $ex)
		{
			$result["status"]='ko';
			$result["error_msg"] .= 'Error in connecting with KB. ';
			$result["msg"] .= ' error in connecting with KB ';
			$result["log"] .= ' error in connecting with KB ' . $ex;
		} 	
		/* registration of the device in the corresponding context broker */
		if ($local_result!="errore")
		{
		   $result["status"]='ok';
		   $result["content"]=$local_result;
		   // information to be passed to the interface
		   $result["visibility"] = $visibility;
		   if($result["content"]==null) 
			$result["active"]=false; 
		   else 
			$result["active"]=true;
		   // end of information to be passed to the interface
		   $result["msg"] .= "\n the device has been deleted from the KB";
		   $result["log"] .= "\n the device has been deleted from the KB";
            
	           if($row["cbkind"]!='external')
        	   {
                   	switch ($protocol)
			{
	                    case "ngsi":
        	                 $res = delete_ngsi($name, $type, $contextbroker, $kind, $protocol, $format, $model, $latitude, $longitude, 
				        $visibility, $frequency, $listnewAttributes, $ip, $port,$uri, $result);
	                         break;
			    case "ngsi w/MultiService":
				$res = delete_ngsi($name, $type, $contextbroker, $kind, $protocol, $format, $model, $latitude, $longitude,
				        $visibility, $frequency, $listnewAttributes, $ip, $port,$uri, $result, $service, $servicePath);
				break;
        	            case "mqtt":
                	        $res = delete_mqtt($name, $type, $contextbroker, $kind, $protocol, $format, $latitude, $longitude, $visibility, 
				        $frequency, $listnewAttributes, $ip, $port,$uri, $result);
	                         break;
        	            case "amqp":
                		$res = delete_amqp($name, $type, $contextbroker, $kind, $protocol, $format, $latitude, $longitude, $visibility, $frequency, 
				        $listnewAttributes, $ip, $port,$uri, $result);
	                        break;
        	          }
                	  if ($res=='ok')
	                  {
        	            $result["msg"] .= "\n ok deletion from the context broker";
                	    $result["log"] .= "\n ok deletion from the context broker";
	                  }
        	          else
                	  {
	                   $result["status"]='ko';
        	           $result["error_msg"] .= "No deletion from the context broker. ";
                	   $result["msg"] .= "\n no deletion from the context broker";
	                   $result["log"] .= "\n no deletion from the context broker";
        	          }
            	}
	        else{
			$result["msg"] .= "\n context broker external, no deletion";
			$result["log"] .= "\n context broker external, no deletion";
            	}
		return 1;
	}
	else
	{
	  $result["error_msg"].="Error in the validation w.r.t. the KB. ";
	  $result["msg"].="\n error in the validation w.r.t. the KB";
	  $result["log"].="\n error in the validation w.r.t. the KB";
	  $result["status"]='ko';
	  return 1;
	}
 } 
    $result["msg"].="   \n it cannot be modified";
} // end of function deleteKB

/* functions for insert/update/delete values from a specific device in the KB and context brokers */

function modify_valueKB($link, $device, $contextbroker, $organization, &$result) {
    $result["status"]='ok';
    
	$listnewAttributes=generateAttributes($link, $device, $contextbroker);
	   
	$query = "SELECT d.uri, d.id, d.devicetype AS entityType, d.kind, d.format, d.macaddress, d.model, d.producer, d.protocol, d.longitude,d.service, d.servicePath,
d.latitude, d.visibility, d.frequency, cb.name, cb.protocol as type, cb.ip, cb.port, cb.login, cb.password, cb.latitude as cblatitude, 
cb.longitude as cblongitude, cb.created FROM devices d JOIN contextbroker cb ON d.contextBroker = cb.name WHERE d.deleted is null and 
d.contextBroker='$contextbroker' and d.id='$device';";
	   
	$r_init = mysqli_query($link, $query);

	if (!$r_init) {
		$result["status"]='ko';
		$result["error_msg"] .= 'Error in reading data from context broker and device. ';
		$result["msg"] .= '\n error in reading data from context broker and device ' . mysqli_error($link);
		$result["log"] .= '\n error in reading data from context broker and device ' . mysqli_error($link) . $query;
		return 1;
	}
  
	$row = mysqli_fetch_assoc($r_init);
	// $device =$row["id"];
	$type =$row["entityType"];
	$kind =$row["kind"];
	$protocol =$row["protocol"];
	$format =$row["format"];
	$macaddress =$row["macaddress"];
	$model =$row["model"];
	$producer =$row["producer"];
	$latitude =$row["latitude"];
	$longitude =$row["longitude"];
	$visibility = $row['visibility'];
	//$owner= $row['owner'];
	$frequency= $row['frequency'];
	$ip=$row["ip"];
	$port=$row["port"];
	$uri=$row["uri"];
	$service=$row["service"];
	$servicePath=$row["servicePath"];

	if (canBeModified($device, $type, $contextbroker, $kind, $protocol, $format, $macaddress, $model, $producer, $latitude, $longitude, 
$visibility, $frequency, $listnewAttributes, $result))
	  {
		


		  /* msg for the Knowledge base + registration on the KB */
		 // echo "entrato";
		  $msg=array();
		  $msg["id"]= $device;
		  $msg["type"]= $type;
		  $msg["kind"]= $kind;
		  $msg["protocol"]= $protocol;
		  $msg["format"]= $format;
		  $msg["macaddress"]= $macaddress;
		  $msg["model"]= $model;
		  $msg["producer"]= $producer;
		  $msg["latitude"]= $latitude;
		  $msg["longitude"]= $longitude;
		  $msg["frequency"]= $frequency;
		  $msg["ownership"]= $visibility;
		  $msg["broker"]=array();
		  $msg["broker"]["name"]=$contextbroker;
		  $msg["broker"]["type"]=$row["protocol"];
		  $msg["broker"]["ip"]=$row["ip"];
		  $msg["broker"]["port"]=$row["port"];
		  $msg["broker"]["login"]=($row["login"]==null)?"":$row["login"];
		  $msg["broker"]["password"]=($row["password"]==null)?"":$row["password"];
		  $msg["broker"]["latitude"]=$row["cblatitude"];
		  $msg["broker"]["longitude"]=$row["cblongitude"];
		  $msg["broker"]["created"]=$row["created"];
		  $msg["organization"]= $organization;
		   // $msg["attributes"]=array();
		  
		  $myAttrs=array();
		  $i=1;
		  foreach($listnewAttributes as $att)
		  {
			$myatt = array();
			$myatt["value_name"] =$att["value_name"];
			$myatt["data_type"] =$att["data_type"];
			$myatt["value_type"]=$att["value_type"];
			$myatt["value_unit"]=$att["value_unit"];
			$myatt["healthiness_criteria"]=$att["healthiness_criteria"];
			if ($att["healthiness_criteria"]=="refresh_rate") $myatt["value_refresh_rate"]=$att["healthiness_value"];
			else if ($att["healthiness_criteria"]=="different_values") $myatt["different_values"]=$att["healthiness_value"];
					 else $myatt["value_bounds"]=$att["healthiness_value"];
			$myatt["order"]=$att["order"];
			$myAttrs[]=$myatt;
			
		  }	 
		  $msg["attributes"]=$myAttrs;
		  
		  // echo json_encode($msg);
		  
		  try
		   {
			 //$url=$GLOBALS["knowledgeBaseURI"] . "api/v1/iot/insert";
			$url= $_SESSION['kbUrl']."iot/insert";


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
			 if ($local_result!="errore")
			 {
			   $result["status"]='ok';
			   $result["content"]=$local_result;
			   $result["msg"] .= "\n the device has been modified in the KB";
				$result["log"] .= "\n the device has been modified in the KB";
			   // echo "dentro" . json_encode($result);
			   switch ($protocol)
			   {
				case "ngsi":
					$res = update_ngsi($device, $type, $contextbroker, $kind, $protocol, $format, $model, $latitude,
$longitude, $visibility, $frequency, $listnewAttributes, $ip, $port,$uri, $result);
					break;
				case "ngsi w/MultiService":
		                        $res = update_ngsi($device, $type, $contextbroker, $kind, $protocol, $format, $model, $latitude, 
$longitude, $visibility, $frequency, $listnewAttributes, $ip, $port,$uri, $result, $service, $servicePath);
                        break;
				case "mqtt":
					$res =update_mqtt($device, $type, $contextbroker, $kind, $protocol, $format, $latitude, $longitude, 
$visibility, $frequency, $listnewAttributes, $ip, $port,$uri, $result);
					 break;
				case "amqp":
					$res = update_amqp($device, $type, $contextbroker, $kind, $protocol, $format, $latitude, $longitude, 
$visibility, $frequency, $listnewAttributes, $ip, $port,$uri, $result);
					break;
			  }
			  if ($res=='ok')
			  {
				$result["msg"] .= "\n ok modification in the context broker";
				$result["log"] .= "\n ok modification in the context broker";
			  }
			  else
			  {
			   $result["status"]='ko';
			   $result["error_msg"] .= "There has been no modification in the context broker. ";
			   $result["msg"] .= "\n no modification in the context broker";
			   $result["log"] .= "\n no modification in the context broker";
			  }
			  return 1;
			}
    else
    {
	  $result["error_msg"].="Error in the validation w.r.t. the KB. ";
	  $result["msg"].="\n error in the validation w.r.t. the KB";
	  $result["log"].="\n error in the validation w.r.t. the KB";
	  $result["status"]='ko';
	  return 1;
	}
		 } 
		 catch (Exception $ex)
		  {
			$result["status"]='ko';
		    $result["error_msg"] .= 'Error in connecting with KB. ';
		    $result["msg"] .= ' error in connecting with KB ' . $ex;
            $result["log"] .= ' error in connecting with KB ' . $ex;
		 } 	
		/* registration of the device in the corresponding context broker */
		 // echo "valore local_result" . $local_result;
		
 } 
} // end of function modify_valueKB

function get_organization_info($organizationApiURI, $ou_tmp){
        $url = $organizationApiURI.'organizations.php?org='.$ou_tmp;
        $context = stream_context_create(null);
        $result = file_get_contents($url, false, $context);

	$result_json = json_decode($result, true);
        if(sizeof($result_json)==1){
                return $result_json[0];
        }
        else{
                return null;
        }
}


//return subscription_id. can be FAILED
//returning ok also if subscribe failed (beside try and catch)
function nificallback_create($ip, $port, $name, $urlnificallback, $protocol, $services, &$result){
	$result["status"]='ok';
	$result["content"]='FAILED';
	$result["log"] .= "\n Received request of nificallback_create for ip:$ip port:$port cbname:$name";
	$subscriptions=array();
	try
        {
		$howmany=1;
		if ($protocol == 'ngsi w/MultiService'){
			$howmany= count($services)+1;//add scenario senza tenant 
		}

		for ($i = 0; $i < $howmany; $i++) {

			$msg = "{\"description\": \"$name nifi\",\"subject\": {	\"entities\": [{ \"idPattern\": \".*\",	\"typePattern\": \".*\"	}],\"condition\": {\"attrs\": []}},\"notification\": {	\"http\": {\"url\": \"$urlnificallback\" }},\"expires\": \"2030-01-01T00:00:00.00Z\"}";

        	        $url="http://".$ip.":".$port."/v2/subscriptions";

			$result["log"] .= "\n Payload to send is:".$msg;
			$result["log"] .= "\n Post url is:".$url;

			$http_headers = array("Content-Type: application/json;charset=utf-8");

			if ($protocol == 'ngsi w/MultiService'){
                                if ($i<$howmany-1) {//scenario senza tenant
					array_push($http_headers, "Fiware-Service: ".$services[$i]);
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

			$result["log"] .= "\n Response is:".$local_result;

                	if(isset($http_response_header) && is_array($http_response_header) && strpos($http_response_header[0], '201') !== false)
			{
				$sub_id=extract_subscription_id($http_response_header);
				array_push($subscriptions, $sub_id);
				$result["log"] .= "\n Response subscription_id is:". $sub_id;
	                }
			else 
			{
				$result["log"] .= "\n Error returned or not reachable";
			}
			//return status==ok even if the subscription failed
		}
		$result["content"]=implode(",",$subscriptions);
        }
        catch (Exception $ex)
        {
                $result["status"]='ko';
                $result["error_msg"] .= 'Error in creating the subscription for NIFI. ';
                $result["msg"] .= '\n Error in creating the subscription for NIFI ';
                $result["log"] .= '\n Error in creating the subscription for NIFI ' . $ex;
        }
}

function extract_subscription_id($headers)
{
	foreach ($headers as $header)
	{
		if (strpos($header, 'Location')!== false){
			return substr($header, 28);
		}
        }
	return "FAILED";
}

//returning ok also if unsubscribe failed (beside try and catch)
function nificallback_delete($ip, $port, $subscription_id, $name, $protocol, $services, &$result){
	$result["status"]='ok';
        $result["log"] .= " Received request of nificallback_delete for ip:$ip port:$port subscription_id:$subscription_id cbname:$name";

	$subscriptions=explode(",",$subscription_id);

        try
        {
		$howmany=1;
                if ($protocol == 'ngsi w/MultiService'){
                        $howmany= count($subscriptions);//add scenario senza tenant e senza tenant e senza servicePath
                }

		for ($i = 0; $i < $howmany; $i++) {
	
	                $url="http://".$ip.":".$port."/v2/subscriptions/".$subscriptions[$i];

        	        $result["log"] .= "\n Delete url is:".$url;

			$http_headers = array();//"Content-Type: application/json;charset=utf-8");

                        if ($protocol == 'ngsi w/MultiService'){
				if ($i<$howmany-1)//scenario senza tenant
	                                array_push($http_headers, "Fiware-Service: ".$services[$i]);
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

			$result["log"] .= "\n Response is: ".$local_result;
	
			//return status==ok even if the subscription failed
		}
        }
        catch (Exception $ex)
        {
                $result["status"]='ko';
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
    if ($servicePath[0] == "/") $servicePath = substr($servicePath, 1);
    if ($servicePath[strlen($servicePath) -1] == "/") $servicePath = substr($servicePath, 0, -1);

    // case: empty string
    if ($servicePath == "") return 0;

    // case: servicePath is too long
    if (strlen($servicePath) > 95) return 1;

    // get single servicePath "levels"
    $levels = explode("/", $servicePath);

    // case: too many levels
    if (count($levels) > 10) return 1;

    for ($i = 0; $i < count($levels); $i++) {
        // case: some level is too long
        if (strlen($levels[$i]) > 50) return 1;

        // case: there are some empty level
        if ($levels[$i] == "") return 1;

	// case: some level contains some semicolons
	if(strpos($servicePath, ".") !== false) return 1;

        // case: some level contains some whitespaces
        if (preg_match("/\s/", $levels[$i])) return 1;
    }

    // case: everything is ok
    return 0;
}

function get_all_models($username, $organization, $role, $accessToken, $link, &$result){

        getOwnerShipObject($accessToken, "ModelID", $result);
        getDelegatedObject($accessToken, $username, "ModelID", $result);

        $res=array();
        $q  = "SELECT * FROM model";
        $r = mysqli_query($link, $q);

        if($r)
        {
                while($row = mysqli_fetch_assoc($r))
                {
			$idTocheck=$row["organization"].":".$row["name"];
						if (
							($role=='RootAdmin')
							||($role=='ToolAdmin')
							||
							(
								($row["organization"]==$organization)&&
								(
									($row["visibility"]=='public'
									||
									(isset($result["delegation"][$idTocheck])&& $result["delegation"][$idTocheck]["kind"]=="anonymous")
									)
								)
							)
							||
								(isset($result["delegation"][$idTocheck])&& $result["delegation"][$idTocheck]["kind"]!="anonymous")
							||
								(isset($result["keys"][$idTocheck]) && $result["keys"][$idTocheck]["owner"]==$username)
			
						)
                        {
                                array_push($res, $row);
                        }
                }
                $result["status"]="ok";
                $result["content"]=$res;
                $result["log"]= "action=get_all_models \r\n";
        }
        else
        {
                $result["status"]="ko";
                $result['msg'] = mysqli_error($link);
                $result["log"]= "action=get_all_models -" . " error " .  mysqli_error($link)  . "\r\n";
        }
}

function get_device($username, $role, $id, $cb,  $accessToken, $link, &$result){

        getOwnerShipDevice($accessToken, $result);
        getDelegatedDevice($accessToken, $username, $result);

	unset($result['msg']);

	$q = "SELECT d.`uri`, d.`devicetype`, d.`kind`,
		CASE WHEN mandatoryproperties AND mandatoryvalues THEN \"active\" ELSE \"idle\" END AS status1,
		d.`macaddress`, d.`model`, d.`producer`, d.`longitude`, d.`latitude`, d.`protocol`, d.`format`, d.`visibility`,
		d.`frequency`, d.`created`, d.`privatekey`, d.`certificate`,d.`organization`, cb.`accesslink`, cb.`accessport`, 
		cb.`sha`, d.`subnature`, d.`static_attributes` 
		FROM `devices` d JOIN `contextbroker` cb ON (d.contextBroker=cb.name)
		WHERE d.`contextBroker`='$cb' AND d.`id`='$id'";

        $r = mysqli_query($link, $q);

        if($r)
        {
                while($row = mysqli_fetch_assoc($r))//should be just one data
                {
			$eid=$row["organization"].":".$cb.":".$id;
			if (
				($role=='RootAdmin')
				||
                                (isset($result["delegation"][$eid]))//if there are any delegation (public or personal)
                                ||
                                (isset($result["keys"][$eid]) && $result["keys"][$eid]["owner"]==$username)
                           )
                        {
				//it's permitted to access
			
				//in case of ownership or RootAdmin, enrich with k1, k2 credentials
                                if  (($role=='RootAdmin') || (isset($result["keys"][$eid]) && $result["keys"][$eid]["owner"]==$username))
                                {
                                        $row["k1"]=$result["keys"][$eid]["k1"];
                                        $row["k2"]=$result["keys"][$eid]["k2"];
                                }
				//in case of delegation, resetting privatekey info
				else 
				{
					unset($row["sha"]);
					unset($row["privatekey"]);
					unset($row["certificate"]);
	
					//in case of personal delegation, enrich with k1, k2 delegation credentials
					if ($result["delegation"][$eid]["kind"]!=="anonymous")
					{
						$row["k1"]= $result["delegation"][$eid]["k1"]; 
	                                        $row["k2"]= $result["delegation"][$eid]["k2"];
					}
				}
	                        $result["content"]=$row;        
				$result["status"]="ok";
		                $result["log"]= "action=get_device \r\n";
				break;
                        }
                }
        }
        else
        {
                $result["status"]="ko";
                $result['msg'] = mysqli_error($link);
                $result["log"]= "action=get_device -" . " error " .  mysqli_error($link)  . "\r\n";
        }
	
	unset($result["username"]);
	unset($result["keys"]);
	unset($result["delegation"]);
}

function get_all_contextbrokers($username, $organization, $loggedrole, $accessToken, $link, $length, $start, $draw, $request, &$result){
	
	getOwnerShipObject($accessToken, "BrokerID", $result);
	getDelegatedObject($accessToken, $username, "BrokerID", $result);

	$q = "SELECT * FROM contextbroker";
	$r=create_datatable_data($link,$request,$q, '');
	$selectedrows=-1;
	if($length != -1)
	{
		$offset=$length;
		$tobelimited=true;
	}
	else
	{
		$tobelimited=false;
	}

	if($r)
	{
		$data = array();
		while($row = mysqli_fetch_assoc($r))
		{
			$idTocheck=$row["organization"].":".$row["name"];
			if (
				($loggedrole=='RootAdmin')
				||
				($loggedrole=='ToolAdmin')
				||
				(
				 ($row["organization"]==$organization)&&
				 (
				  ($row["visibility"]=='public' || (isset($result["delegation"][$idTocheck])&& $result["delegation"][$idTocheck]["kind"]=="anonymous"))
				 )  
				)
				||
				(isset($result["delegation"][$idTocheck])&& $result["delegation"][$idTocheck]["kind"]!="anonymous")
				||
				(isset($result["keys"][$idTocheck]) && $result["keys"][$idTocheck]["owner"]==$username)
			   )
			{
				$selectedrows++;
				if (!$tobelimited || ($tobelimited && $selectedrows >= $start && $selectedrows < ($start+$offset)))
				{
					if (((isset($result["keys"][$idTocheck]))&&($loggedrole!=='RootAdmin')&&($loggedrole!=='ToolAdmin'))
						||
						((isset($result["keys"][$idTocheck]))&& ($result["keys"][$idTocheck]["owner"]==$username) && (($loggedrole==='RootAdmin')||($loggedrole==='ToolAdmin'))))
					{
						//it's mine
						if ($row["visibility"]=="public")
						{
							$row["visibility"]= "MyOwnPublic";
						}
						else
						{
							if (isset($result["delegation"][$row["accesslink"]]) && $result["delegation"][$row["accesslink"]]["kind"]=="anonymous")
								$row["visibility"]= "MyOwnPublic";
							else
								$row["visibility"]="MyOwnPrivate";
						}
					}
					else
					{
						//it's not mine
						if (isset($result["delegation"][$idTocheck]) && ($result["delegation"][$idTocheck]["kind"]=="anonymous"))
						{
							//it's delegated as public
							$row["visibility"]='public';
						}
						else if (isset($result["delegation"][$idTocheck]))
						{
							//it's delegated personally
							$row["visibility"]='delegated';
						}
						else
						{
							$row["visibility"]= $row["visibility"];
						}
					}

					$row["owner"]='';
					if(isset($result["keys"][$idTocheck]))
						$row["owner"]=$result["keys"][$idTocheck]["owner"];

					if ($row["protocol"] == "ngsi w/MultiService") 
					{
						// the CB supports MultiServices
						$brokerName = $row["name"];
						$servicesQueryString = "SELECT * FROM services WHERE broker_name = '$brokerName'";
						$sqr = mysqli_query($link, $servicesQueryString);
						if ($sqr) 
						{
							$row["services"] = array();
							while ($servicesRow = mysqli_fetch_assoc($sqr)) {
								array_push($row["services"], $servicesRow["name"]);
							}
						} 
						else 
						{
							$output= format_result($draw, 0, 0, null, 'Error: errors in reading data about IOT Broker. <br/>' . generateErrorMessage($link), '\n\r Error: errors in reading data about IOT Broker.' . generateErrorMessage($link), 'ko');
							logAction($link,$username,'contextbroker','get_all_contextbroker','',$organization,'Error: errors in reading data about IOT Broker.','faliure');
						}
					}
					array_push($data, $row);
				}
			}
		}

		$result= format_result($draw, $selectedrows+1, $selectedrows+1, $data, "", "\r\n action=get_all_contextbroker \r\n", 'ok');
		logAction($link,$username,'contextbroker','get_all_contextbroker','',$organization,'','success');
	}
	else
	{
		$result=format_result($draw, 0, 0, null, 'Error: errors in reading data about IOT Broker. <br/>' . generateErrorMessage($link), '\n\r Error: errors in reading data about IOT Broker.' . generateErrorMessage($link), 'ko');
		logAction($link,$username,'contextbroker','get_all_contextbroker','',$organization,'Error: errors in reading data about IOT Broker.','faliure');
	}

}

function get_user_info($accessToken, &$username, &$organization, $oidc, &$role, &$result, $ldapBaseName, $ldapServer, $ldapPort, $ldapAdminName, $ldapAdminPwd )
{

	$result["status"]="ok";

	$oidc->setAccessToken($accessToken);

	$userinfo=(array)$oidc->getAccessTokenPayload();
	
	if ($userinfo['preferred_username']!=null)
		$username=$userinfo["preferred_username"];
	else if ($userinfo["username"]!=null)
		$username=$userinfo["username"];
	else {
		$result["status"]="ko";
		$result['msg'] = "Username not found in AccessToken";
		$result['error_msg'] .="Username not found in AccessToken";
                $result["log"]= "action=get_user_info -" . " Username not found in AccessToken \r\n";
	}

	$uinfo = $oidc->requestUserInfo();
	if(isset($uinfo->error)) 
	{
		$result["status"]="ko";
		$result['msg'] = "Userinfo not found in AccessToken";
		$result['error_msg'] .="Userinfo not found in AccessToken ".json_encode($uinfo);
		$result["log"]= "action=get_user_info -" . " Userinfo not found in AccessToken ".json_encode($uinfo)."\r\n";
	}

	if ($result["status"]=="ok"){
		
		if (in_array("RootAdmin", $userinfo["roles"]))
			$role="RootAdmin";
		else if (in_array("ToolAdmin", $userinfo["roles"]))
                        $role="ToolAdmin";
		else if (in_array("AreaManager", $userinfo["roles"]))
                        $role="AreaManager";
		else if (in_array("Manager", $userinfo["roles"]))
                        $role="Manager";
		else {
			$result["status"]="ko";
                	$result['msg'] = "Role not found in AccessToken for user ".$username;
			$result["error_msg"] .= "Role not found in AccessToken for user ".$username;
                	$result["log"]= "action=get_user_info -" . " Role not found in AccessToken for user ".$username."\r\n";
		}
	}
		
	if ($result["status"]=="ok"){
		$organization= findLdapOrganizationalUnit($username, $ldapBaseName, $ldapServer, $ldapPort, $ldapAdminName, $ldapAdminPwd);
		if (empty($organization)){
			$result["status"]="ko";
                        $result['msg'] = "Organization not found in AccessToken for user ".$username;
			$result['error_msg'] .= "Organization not found in AccessToken for user ".$username;
                        $result["log"]= "action=get_user_info -" . " Organization not found in AccessToken for user ".$username."\r\n";
		}
	}
}

//TODO armonize with the code available in ssoLogin.php AND ldap.php in api
function findLdapOrganizationalUnit($username, $ldapBaseName, $ldapServer, $ldapPort, $ldapAdminName, $ldapAdminPwd)
{
	$ldapUsername = "cn=". strtolower($username) . ",".$ldapBaseName;

	$ds = ldap_connect($ldapServer, $ldapPort);
	ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
	$bind = ldap_bind($ds, $ldapAdminName, $ldapAdminPwd);

        $result = ldap_search($ds, $ldapBaseName, '(&(objectClass=organizationalUnit)(l=' . $ldapUsername . '))');
        $entries = ldap_get_entries($ds, $result);

    	if (ldap_count_entries($ds,$result)==0){
        	//TODO thrown an error or return an error
	        return "";
        }
	else{
        	$ou=$entries["0"]["ou"][0];
		return $ou;
	}
}

function missingParameters($requiredParams){
	$toreturn = array();

	foreach ($requiredParams as $param)
		if (!isset($_REQUEST[$param]))
			array_push($toreturn, $param);

	return $toreturn;
}

//it does not throw any error... just return an empty string in case of trouble
function getProtocol($contextBrokerName, $link)
{
	$toreturn="";

	$q = "SELECT protocol FROM iotdb.contextbroker where name='$contextBrokerName'";

        $r = mysqli_query($link, $q);

        if($r)
        {
                while($row = mysqli_fetch_assoc($r))//should be just one data
                {
			if (isset($row["protocol"])) 
			{
				$toreturn=$row["protocol"];
				break;
			}
                }
	}
	return $toreturn;
}

//return false if you have no right to access
//action can be 'write' or 'read'
function enforcementRights($username, $token, $role, $elementId, $elementType, $action, &$result)
{
	if ($role=="RootAdmin") 
	{
		$toreturn = true;//grant RootAdmin for everything
	}
	else 
	{
		//for write check owenrship
		if ($action=="write")
		{
			$toreturn=checkOwnershipObject($token, $elementId, $elementType, $result);
		}
		else if ($action=="read")
        {
            $toreturn= checkOwnershipObject($token, $elementId, $elementType, $result) || 
     				   checkDelegationObject($username, $token, $elementId, $elementType, $result);
					  
        }
	}

	return $toreturn;
}


function logAction($link,$accessed_by,$target_entity_type,$access_type,$entity_name,$organization,$notes,$result){
	$query = "INSERT INTO access_log(accessed_by,target_entity_type,access_type,entity_name,organization,notes,result) ".
		 "VALUES('$accessed_by','$target_entity_type','$access_type','$entity_name','$organization','$notes','$result')";
	$res = mysqli_query($link, $query) or die(mysqli_error($link));
	if($res){
		$result["msg"]="correctly logged\n" .$accessed_by. " ".$target_entity_type." ".$access_type. " ".$entity_name. 
		" ".$notes;
	}
	else{
		$result["msg"]="error in inserting log\n"; 
	}
	return $result["msg"];
}
?>


