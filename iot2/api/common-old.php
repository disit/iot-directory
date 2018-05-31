<?php

/*  functions for getting parameters */


function generatedatatypes($link)
{
     $query2 = "SELECT data_type FROM data_types order by data_type";
     $res = mysqli_query($link, $query2) or die(mysqli_error($link));
     $attributes = array();

          if($res){
         while($row = mysqli_fetch_assoc($res))
                {array_push($attributes, $row["data_type"]);
                }
     }
         return $attributes;
}

	function generateAttributes($link,$name,$cb)
{
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

/* function generateAttributes($link,$name,$cb)
{
     $query2 = "SELECT * FROM event_values WHERE cb = '$cb' AND device = '$name'";
     $res = mysqli_query($link, $query2) or die(mysqli_error($link));
     $attributes = array();

          if($res){
         while($row = mysqli_fetch_assoc($res))
                {array_push($attributes, $row);
                }
     }
         return $attributes;
}*/


function registerPrivateDevice($elementId, $msg, $token, &$result)
{
//	$msg["elementId"]=$elementId;
	$msg["elementType"]="IOTID";
//	$msg["elementName"]=$elementName;	
//	$msg["accessToken"]=$token;	
	  
	try
	{
	 $url="http://192.168.0.207/ownership-api/v1/register/?accessToken=" . $token;
	 $options = array(
			  'http' => array(
					  'header'  => "Content-Type: application/json;charset=utf-8",
					  'header'  => "Access-Control-Allow-Origin: *",
					  'method'  => 'POST',
		                          'content' => json_encode($msg),
                                          'ignore_errors' => true,
					  'timeout' => 30
			  )
		);
	 $context  = stream_context_create($options);
	 $local_result = @file_get_contents($url, false, $context);
	} 
	catch (Exception $ex) 
	{
	$result["status"]='ko';
	$result["msg"] .= '\n error in registering the ownership ' . $ex;
	} 
//        $result["msg"] .= "value of status" . $local_result;
	if ($local_result!="errore")
		 {
		   $result["status"]='ok';
		   $result["msg"] .= "\n the registration of the ownership succeded" . $local_result;
		 }
		 else
		 {
		   $result["status"]='ok';
		   $result["msg"] .= "\n error in registering the ownership";
		 }
}


function getPrivateDevice($token, &$result)
{	
	$listCondDevice = "";
        $local_result=""; 
        $mykeys = array(); 
	try
	{
	 $url="http://192.168.0.207/ownership-api/v1/list/?type=IOTID&accessToken=" . $token;
         $local_result = file_get_contents($url);
	} 
	catch (Exception $ex) 
	{
	$result["status"]='ko';
	$result["msg"] .= '\n error in accessing the ownership ' . $ex;
	}
         $result["msg"] .= $local_result;
	if ($local_result!="")
	{		
		$lists = json_decode($local_result);
		for ($i=0;$i < count($lists); $i++)
		{       // print_r($lists[$i]->elementDetails);
			// $elem = explode("::",$lists[$i]->elementId);
			$listCondDevice .= " (id = '" . $lists[$i]->elementId . "' AND contextbroker = '" . $lists[$i]->elementDetails->contextbroker . "') ";
			if ($i != count($lists)-1)  $listCondDevice .= " OR ";
                        $mykeys[$lists[$i]->elementId]= array("k1"=> $lists[$i]->elementDetails->k1, "k2" => $lists[$i]->elementDetails->k2, "cb" => $lists[$i]->elementDetails->contextbroker);

		}
		$result["status"]='ok';
                $result["keys"]=$mykeys;
		$result["msg"] .= '\n identified  ' . count($lists) . ' private devices \n' .  $listCondDevice;
       }	
       return $listCondDevice;
}



function generatelabels($link)
{
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

function generateunits($link)
{
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



/* ****FUNCTIONS FOR THE REGISTRATION OF A DEVICE IN THE CONTEXT BROKER AND IN THE KNOWLEDGE BASE ******  */
	
	function insert_ngsi($name, $type, $contextbroker, $kind, $protocol, $format, $model, $latitude, $longitude,$visibility, $frequency, $listnewAttributes, $ip, $port, &$result)
	{
	   $res = "ok";
	   $msg_orion=array();
      
	  $msg_orion["id"]=     $name; 
      $msg_orion["type"]=   $type;
      $msg_orion["latitude"]=array();
      $msg_orion["longitude"]=array();
      $msg_orion["latitude"]["value"]=    $latitude;
      $msg_orion["longitude"]["value"]=    $longitude;
      $msg_orion["latitude"]["type"]= "float";     
      $msg_orion["longitude"]["type"]= "float";
      $a=0;
      while ($a < count($listnewAttributes))
      {
         $att=$listnewAttributes[$a];
		 $msg_orion[$att->value_name]=array();
		 $msg_orion[$att->value_name]["value"]="0";
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
			curl_setopt_array($ch, array(
    			CURLOPT_POST => TRUE,
    			CURLOPT_RETURNTRANSFER => TRUE,
    			CURLOPT_HTTPHEADER => array(
					'Authorization: '.$authToken,
					'Content-Type: application/json'),
				CURLOPT_POSTFIELDS => json_encode($msg_orion)
		    ));

		    // Send the request
		    $response_orion = curl_exec($ch);

		    // Check for errors
		    if($response_orion === FALSE){
    			// die(curl_error($ch));
				$result["msg"].= "\n error in the connection with the ngsi context broker";
				$res='ko';
			}
            else
			{
		    // Decode the response
		     $responseData = json_decode($response_orion, TRUE);
			 $result["status"]='ok';
		     $result["msg"] .= '\n response from the ngsi context broker ' . $response_orion;
			 $res='ok';
			 
            } 
		    // Print the date from the response
		    // echo $responseData['published'];
                    // echo $response_orion; 
            }
            catch (Exception $ex) 
            {
		       $result["status"]='ko';
		       $result["msg"] .= ' error in connecting with the ngsi context broker ' . $ex;
			   $res="ko";
            }
            return $res; 
	}
	
	function insert_mqtt($name, $type, $contextbroker, $kind, $protocol, $format, $latitude, $longitude, $visibility,  $frequency, $listnewAttributes, $ip, $port, &$result)
	{
	  return "ok";
	}
	
	function insert_amqp($name, $type, $contextbroker, $kind, $protocol, $format, $latitude, $longitude, $visibility,  $frequency, $listnewAttributes, $ip, $port, &$result)
	{
	  return "ok";
	}

	function canBeModified($name, $type, $contextbroker, $kind, $protocol, $format,  $macaddress, $model, $producer, $latitude, $longitude, $visibility,  $frequency, $listnewAttributes, &$result)
	{
	  $error=false;
	  if ($name==null || $name=="") { $error=true; $result["msg"].= "\n id not specified";}
          if ($contextbroker==null || $contextbroker=="") {$error=true; $result["msg"].= "\n cb not specified";}	  
	  if ($type==null || $type=="") {$error=true; $result["msg"].= "\n type not specified";}
	  if (!($kind=="sensor" || $kind=="actuator")) {$error=true; $result["msg"].= "\n kind not specified";}
	  if ($latitude < -90 && $latitude>90) {$error=true; $result["msg"].= "\n latitude not correct ";}
	  if ($longitude < -180 && $longitude>180) {$error=true; $result["msg"].= "\n longitude not correct ";}
	  if (!($protocol=="ngsi" || $protocol=="mqtt" || $protocol=="amqp")) {$error=true; $result["msg"].= "\n protocol not correct ";}
	  if (count($listnewAttributes)==0) { $error=true; $result["msg"].= "\n at list one attribute";}
	    
	   foreach($listnewAttributes as $att)
		  {
		   if ($att["data_type"]==null || $att["data_type"]=="") {$error=true; $result["msg"].= "\n data type for attribute $att[value_name] not specified";}
			if ($att["value_unit"]==null || $att["value_unit"]=="") {$error=true; $result["msg"].= "\n value unit for attribute $att[value_name] not specified";}
			if ($att["value_type"]==null || $att["value_type"]=="") {$error=true; $result["msg"].= "\n value type for attribute $att[value_name] not specified";}
		    if (!($att["healthiness_criteria"]=="refresh_rate" || $att["healthiness_criteria"]=="different_values" || 
			      $att["healthiness_criteria"]=="within_bounds")) {$error=true;}
		    if ($att["healthiness_criteria"]=="refresh_rate" && $att["healthiness_value"]=="") {$error=true; $result["msg"].= "\n healthiness_criteria for attribute $att[value_name] not specified";}
			// echo "valore di erroe" . $error;
			// if ($att->healthiness_criteria=="different_values" && ($att->different_values=="" || !is_int($att->different_values))) {$error=true;}
		  }
	   if ($error) return false;	  
	   return true; 
	}


	
	function canBeRegistered($name, $type, $contextbroker, $kind, $protocol, $format,  $macaddress, $model, $producer, $latitude, $longitude, $visibility,  $frequency, $listnewAttributes, &$result)
	{
	  $error=false;
	  if ($name==null || $name=="") { $error=true; $result["msg"].= "\n id not specified";}
          if ($contextbroker==null || $contextbroker=="") {$error=true; $result["msg"].= "\n cb not specified";}	  
	  if ($type==null || $type=="") {$error=true; $result["msg"].= "\n type not specified";}
	  if (!($kind=="sensor" || $kind=="actuator")) {$error=true; $result["msg"].= "\n kind not specified";}
	  if ($latitude < -90 && $latitude>90) {$error=true; $result["msg"].= "\n latitude not correct ";}
	  if ($longitude < -180 && $longitude>180) {$error=true; $result["msg"].= "\n longitude not correct ";}
	  if (!($protocol=="ngsi" || $protocol=="mqtt" || $protocol=="amqp")) {$error=true; $result["msg"].= "\n protocol not correct ";}
	  if (count($listnewAttributes)==0) { $error=true; $result["msg"].= "\n at list one attribute";}
	    
	   foreach($listnewAttributes as $att)
		  {
		   if ($att->data_type==null || $att->data_type=="") {$error=true; $result["msg"].= "\n data type for attribute $att->value_name not specified";}
			if ($att->value_unit==null || $att->value_unit=="") {$error=true; $result["msg"].= "\n value unit for attribute $att->value_name not specified";}
			if ($att->value_type==null || $att->value_type=="") {$error=true; $result["msg"].= "\n value type for attribute $att->value_name not specified";}
		    if (!($att->healthiness_criteria=="refresh_rate" || $att->healthiness_criteria=="different_values" || 
			      $att->healthiness_criteria=="within_bounds")) {$error=true;}
		    if ($att->healthiness_criteria=="refresh_rate" && $att->healthiness_value=="") {$error=true; $result["msg"].= "\n healthiness_criteria for attribute $att->value_name not specified";}
			// echo "valore di erroe" . $error;
			// if ($att->healthiness_criteria=="different_values" && ($att->different_values=="" || !is_int($att->different_values))) {$error=true;}
		  }
	   if ($error) return false;	  
	   return true; 
	}
	
	function registerKB($link, $name, $type, $contextbroker, $kind, $protocol, $format,  $macaddress, $model, $producer, $latitude, $longitude, $visibility,  $frequency, $listnewAttributes, &$result)
    {  
	  //$result=array();
      $result["status"]='ok';
      $result["msg"]='';	  
      if (canBeRegistered($name, $type, $contextbroker, $kind, $protocol, $format,  $macaddress, $model, $producer, $latitude, $longitude, $visibility,  $frequency, $listnewAttributes, $result))
      {	  
		  $query="SELECT * from contextbroker WHERE name = '$contextbroker'";
		  
		  $r = mysqli_query($link, $query);
		  
		  if (!$r) {
		    $result["status"]='ko';
		    $result["msg"] .= ' error in reading data from context broker ' . mysqli_error($link) . $query;
		    return 1;
		  }
		  $rowCB = mysqli_fetch_assoc($r);
		  $ip=$rowCB["ip"];
		  $port=$rowCB["port"];
		  
		  $msg=array();
		  $msg["id"]=     $name;
		  $msg["type"]=   $type;
		  $msg["kind"]=    $kind;
		  $msg["protocol"]=    $protocol;
		  $msg["format"]=      $format;
		  $msg["macaddress"]=      $macaddress;
		  $msg["model"]=      $model;
		  $msg["producer"]=      $producer;
		  $msg["latitude"]=    $latitude;
		  $msg["longitude"]=    $longitude;
		  $msg["frequency"]=    $frequency;
		  $msg["visibility"]=    $visibility;
		  // if ($msg["visibility"]=='private')   $msg["owner"]=$owner;
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
		  
		  $myAttrs=array();
		  $i=1;
		  foreach($listnewAttributes as $att)
		  {
                        // print_r($att);  
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
                        //      $myatt["different_values"]=$att["different_values"];
			//else  $myatt["value_bounds"]=$att["value_bounds"];
			$myatt["order"]=$i++;
			$myAttrs[]=$myatt;
			
		  }	 
		  $msg["attributes"]=$myAttrs;
		  
		  // echo json_encode($msg);	  
		  
		  try
		   {
			 $url="http://servicemap.disit.org/WebAppGrafo/api/v1/iot/insert";
			 $options = array(
					  'http' => array(
							  'header'  => "Content-Type: application/json;charset=utf-8",
							  'header'  => "Access-Control-Allow-Origin: *",
							  'method'  => 'POST',
				  'content' => json_encode($msg),
							  'timeout' => 30
					  )
				);
			 $context  = stream_context_create($options);
			 $local_result = @file_get_contents($url, false, $context);
		 } 
		 catch (Exception $ex) 
		  {
		   $result["status"]='ko';
		   $result["msg"] .= '\n error in connecting with KB ' . $ex;
		 } 
		/* registration of the device in the corresponding context broker */
		 if ($local_result!="errore")
		 {
		   $result["status"]='ok';
		   $result["content"]=$local_result;
		   $result["msg"] .= "\n an URI has been generated by the KB";
		 }
		 else
		 {
		   $result["status"]='ok';
		   $result["content"]="";
		   $result["msg"] .= "\n no URI has been generated by the KB";
		 }
		 if ($local_result!="errore")
		 {
			 switch ($protocol)
			 {
			   case "ngsi": 
					 $res = insert_ngsi($name, $type, $contextbroker, $kind, $protocol, $format, $model, $latitude, $longitude, $visibility,  $frequency, $listnewAttributes, $ip, $port, $result);
					 break;
			   case "mqtt":
					$res = insert_mqtt($name, $type, $contextbroker, $kind, $protocol, $format, $latitude, $longitude, $visibility,  $frequency, $listnewAttributes, $ip, $port, $result);
					 break;
				case "amqp":	  
					$res = insert_amqp($name, $type, $contextbroker, $kind, $protocol, $format, $latitude, $longitude, $visibility, $frequency, $listnewAttributes, $ip, $port, $result);
					 break;	 
			 }	
		 }		 
		// echo "dopo protocol" . $res . " " . $local_result;
		if ($res=="ok") 
		{
		 $result["msg"] .= "\n ok registration in the context broker";
		}
		else 
		{
		  $result["status"]='ko';
		  $result["msg"] .= "\n no registration in the context broker";
		}
		return 1;
    }		
	else 
	{
	  $result["msg"].="\n error in the validation w.r.t. the KB";
	  $result["status"]='ko';
	  return 1;
	}	
   } // end of function registerKB	
    
                
	/* ****FUNCTIONS FOR THE MODIFICATION OF THE REGISTRATION OF A DEVICE IN THE KNOWLEDGE BASE AND IN THE CONTEXT BROKER ****************** */			
				
	function update_ngsi($name, $type, $contextbroker, $kind, $protocol, $format, $model, $latitude, $longitude, $visibility,  $frequency, $listnewAttributes, $ip, $port,$uri, &$result)
	{
	   $res = "ok";
	   $msg_orion=array();
      
	  // $msg_orion["id"]=     $name; 
      // $msg_orion["type"]=   $type;
      $msg_orion["latitude"]=array();
      $msg_orion["longitude"]=array();
      $msg_orion["latitude"]["value"]=    $latitude;
      $msg_orion["longitude"]["value"]=    $longitude;
      $msg_orion["latitude"]["type"]= "float";     
      $msg_orion["longitude"]["type"]= "float";
      $a=0;
      while ($a < count($listnewAttributes))
      {
         $att=$listnewAttributes[$a];
		 $msg_orion[$att->value_name]=array();
		 $msg_orion[$att->value_name]["value"]="0";
 		 $msg_orion[$att->value_name]["type"]=$att->data_type;
         $a++;
      }
	  if ($model != null && $model != "")
	  {
	    $msg_orion["model"]=array();
	    $msg_orion["model"]["value"]= $model;
 	    $msg_orion["model"]["type"]= "string";
      }  
      // echo "stefano ".json_encode($msg_orion);
	
	  $url_orion="http://$ip:$port/v2/entities/$name/attrs";  
      
      
      try
         {  
			// Setup cURL
			$ch = curl_init($url_orion);
			$authToken = 'OAuth 2.0 token here';
			curl_setopt_array($ch, array(
    			CURLOPT_POST => TRUE,
    			CURLOPT_RETURNTRANSFER => TRUE,
    			CURLOPT_HTTPHEADER => array(
					'Authorization: '.$authToken,
					'Content-Type: application/json'),
				CURLOPT_POSTFIELDS => json_encode($msg_orion)
		    ));

		    $response_orion = curl_exec($ch);

		    // Check for errors
		    if($response_orion === FALSE){
    			// die(curl_error($ch));
				$result["msg"].= "\n error in the connection with the ngsi context broker" . curl_error($ch);
				$res='ko';
			}
            else
			{
		    // Decode the response
		     $responseData = json_decode($response_orion, TRUE);
			 $result["status"]='ok';
		     $result["msg"] .= '\n response from the ngsi context broker ' . $response_orion;
			 $res='ok';
			 
            } 
		    // Print the date from the response
		    // echo $responseData['published'];
                    // echo $response_orion; 
            }
            catch (Exception $ex) 
            {
		       $result["status"]='ko';
		       $result["msg"] .= ' error in connecting with the ngsi context broker ' . $ex;
			   $res="ko";
            }
            return $res;
	}
	
	function update_mqtt($name, $type, $contextbroker, $kind, $protocol, $format, $latitude, $longitude, $visibility,  $frequency, $listnewAttributes, $ip, $port,$uri,&$result)
	{
	  return "ok";
	}
	
	function update_amqp($name, $type, $contextbroker, $kind, $protocol, $format, $latitude, $longitude, $visibility,  $frequency, $listnewAttributes, $ip, $port,$uri,&$result)
	{
	  return "ok";
	}
	
function updateKB($link, $name, $type, $contextbroker, $kind, $protocol, $format,  $macaddress, $model, $producer, $latitude, $longitude, $visibility,  $frequency, $attributes, $uri, &$result)
{      
  // $result=array();
  $result["status"]='ok';
  $result["msg"]='';	  
        
  if (canBeRegistered($name, $type, $contextbroker, $kind, $protocol, $format,  $macaddress, $model, $producer, $latitude, $longitude, $visibility,  $frequency, $attributes, $result))
  {	
	  $query="SELECT * from contextbroker WHERE name = '$contextbroker'";
	  $r = mysqli_query($link, $query);
	 
	  if (!$r) {
		$result["status"]='ko';
		$result["msg"] .= ' error in reading data from context broker ' . mysqli_error($link) . $query;
		return 1;
	  }
	  $rowCB = mysqli_fetch_assoc($r);
	  $ip=$rowCB["ip"];
	  $port=$rowCB["port"];
	  
	  $msg=array();
	  $msg["id"]=     $name;
	  $msg["type"]=   $type;
	  $msg["kind"]=    $kind;
	  $msg["protocol"]=    $protocol;
	  $msg["format"]=      $format;
	  $msg["macaddress"]=      $macaddress;
	  $msg["model"]=      $model;
	  $msg["producer"]=      $producer;
	  $msg["latitude"]=    $latitude;
	  $msg["longitude"]=    $longitude;
	  $msg["frequency"]=    $frequency;
	  $msg["visibility"]=    $visibility;
	  // if ($msg["visibility"]=='private')   $msg["owner"]=$owner;
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
		else  $myatt["value_bounds"]=$att->value_bounds;
		$myatt["order"]=$i++;
		$myAttrs[]=$myatt;
		
	  }	 
	  $msg["attributes"]=$myAttrs;
	
	  try
	   {
		 $url="http://servicemap.disit.org/WebAppGrafo/api/v1/iot/insert";
		 $options = array(
			  'http' => array(
						  'header'  => "Content-Type: application/json;charset=utf-8",
						  'header'  => "Access-Control-Allow-Origin: *",
						  'method'  => 'POST',
			  'content' => json_encode($msg),
						  'timeout' => 30)
			);
		 $context  = stream_context_create($options);
		 $local_result  = @file_get_contents($url, false, $context);
	 } 
	 catch (Exception $ex)
	{
	   $result["status"]='ko';
	   $result["msg"] .= ' error in connecting with KB ' . $ex;
	 } 
	 if ($local_result!="errore")
	 {
	   $result["status"]='ok';
	   $result["content"]=$local_result;
	   $result["msg"] .= "\n an URI has been generated by the KB";
	 }
	 else
	 {
	   $result["status"]='ok';
	   $result["content"]="";
	   $result["msg"] .= "\n no URI has been generated by the KB";
	 }		 
	 
	/* update of the device in the corresponding context broker */
	if ($local_result!="errore") 
	{    
	  switch ($protocol)
	  {
	   case "ngsi": 
			 $res = update_ngsi($name, $type, $contextbroker, $kind, $protocol, $format, $model, $latitude, $longitude, $visibility,  $frequency, $attributes, $ip, $port,$uri, $result);
			 break;
	   case "mqtt":
			$res = update_mqtt($name, $type, $contextbroker, $kind, $protocol, $format, $latitude, $longitude, $visibility,  $frequency, $attributes, $ip, $port,$uri, $result);
			 break;
		case "amqp":	  
			$res = update_amqp($name, $type, $contextbroker, $kind, $protocol, $format, $latitude, $longitude, $visibility,  $frequency, $attributes, $ip, $port,$uri,$result);
			 break;	 
	   }	 
	}
	if ($res=="ok") 
	{
	 $result["msg"] .= "\n ok updated in the context broker";
	}
	else 
	{
	  $result["status"]='ko';
	  $result["msg"] .= "\n no update in the context broker";
	}
	return 1;
  }
  else 
  {
   $result["msg"].="\n error in the validation w.r.t. the KB";
   $result["status"]='ko';
   return 1;
  }	
} // end of function updateKB	

/* ****FUNCTIONS FOR THE DELETION OF THE REGISTRATION OF A DEVICE IN THE KNOWLEDGE BASE AND IN THE CONTEXT BROKER ****************** */			


	function delete_ngsi($name, $type, $contextbroker, $kind, $protocol, $format, $model, $latitude, $longitude, $visibility, $frequency, $listnewAttributes, $ip, $port,$uri, &$result)
	{
	   $res = "ok";
	   $url_orion="http://$ip:$port/v2/entities/$name";  
      
      try
         {  
			// Setup cURL
			$ch = curl_init($url_orion);
			$authToken = 'OAuth 2.0 token here';
			curl_setopt_array($ch, array(
    			CURLOPT_CUSTOMREQUEST => 'DELETE',
    			CURLOPT_RETURNTRANSFER => TRUE,
    			CURLOPT_HTTPHEADER => array(
					'Authorization: '.$authToken //, 'Content-Type: application/json'
					),
				// CURLOPT_POSTFIELDS => json_encode($msg_orion)
		    ));

		    // Send the request
		    $response_orion = curl_exec($ch);

		    // Check for errors
			if($response_orion === FALSE){
    			// die(curl_error($ch));
				$result["msg"].= "\n error in the connection with the ngsi context broker";
				$res='ko';
			}
            else
			{
		    // Decode the response
		     $responseData = json_decode($response_orion, TRUE);
			 $result["status"]='ok';
		     $result["msg"] .= '\n response from the ngsi context broker ' . $response_orion;
			 $res='ok';
			 
            }
		    
		    // Print the date from the response
		    // echo $responseData['published'];
            }
              catch (Exception $ex) 
            {
		       $result["status"]='ko';
		       $result["msg"] .= ' error in connecting with the ngsi context broker ' . $ex;
			   $res="ko";
            }
            // echo json_encode($result);			
            return $res; 
	}
	
	function delete_mqtt($name, $type, $contextbroker, $kind, $protocol, $format, $latitude, $longitude, $visibility,  $frequency, $listnewAttributes, $ip, $port,$uri, &$result)
	{
	  return "ok";
	}
	
	function delete_amqp($name, $type, $contextbroker, $kind, $protocol, $format, $latitude, $longitude, $visibility,  $frequency, $listnewAttributes, $ip, $port,$uri, &$result)
	{
	  return "ok";
	}
	


	
	
function deleteKB($link, $name, $contextbroker)
{  
	
	$result=array();
    $result["status"]='ok';
    $result["msg"]='';
  
	$listnewAttributes=generateAttributes($link, $name, $contextbroker);
	   
	$query  = "SELECT  d.uri, d.id, d.devicetype AS entityType, d.kind, d.format, d.macaddress, d.model, d.producer, d.protocol,  d.longitude, d.latitude, d.visibility,  d.frequency, cb.name, cb.protocol as type, cb.ip, cb.port, cb.login, cb.password, cb.latitude as cblatitude, cb.longitude as cblongitude, cb.created FROM devices d JOIN contextbroker cb ON d.contextBroker = cb.name WHERE d.contextBroker='$contextbroker' and d.id='$name';";
	   
	$r_init = mysqli_query($link, $query);

	if (!$r_init) {
		$result["status"]='ko';
		$result["msg"] .= '\n error in reading data from context broker and device ' . mysqli_error($link) . $query;
		return $result;
	}
  
	$row = mysqli_fetch_assoc($r_init);
	$name =$row["id"]; 
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
	// $owner= $row['owner'];
	$frequency= $row['frequency'];
	$ip=$row["ip"];
	$port=$row["port"];
	$uri=$row["uri"];

	if (canBeModified($name, $type, $contextbroker, $kind, $protocol, $format,  $macaddress, $model, $producer, $latitude, $longitude, $visibility,  $frequency, $listnewAttributes, $result))
	  {
		  /* msg for the Knowledge base + registration on the KB */
		 // echo "entrato";
		  $msg=array();
		  $msg["id"]=     $name;
		  $msg["type"]=   $type;
		  $msg["kind"]=    $kind;
		  $msg["protocol"]=    $protocol;
		  $msg["format"]=      $format;
		  $msg["macaddress"]=      $macaddress;
		  $msg["model"]=      $model;
		  $msg["producer"]=      $producer;
		  $msg["latitude"]=    $latitude;
		  $msg["longitude"]=    $longitude;
		  $msg["frequency"]=    $frequency;
	      // $msg["visibility"]=    $visibility;
	      // if ($msg["visibility"]=='private')   $msg["owner"]=$owner;
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
					 else  $myatt["value_bounds"]=$att["healthiness_value"];
			$myatt["order"]=$att["order"];
			$myAttrs[]=$myatt;
			
		  }	 
		  $msg["attributes"]=$myAttrs;
		  
		  // echo json_encode($msg);	  
		  
		  try
		   {
			 $url="http://servicemap.disit.org/WebAppGrafo/api/v1/iot/delete";
			 $options = array(
					  'http' => array(
							  'header'  => "Content-Type: application/json;charset=utf-8",
							  'header'  => "Access-Control-Allow-Origin: *",
							  'method'  => 'POST',
				  'content' => json_encode($msg),
							  'timeout' => 30
					  )
				);
			 $context  = stream_context_create($options);
			 $local_result = @file_get_contents($url, false, $context);
		 } 
		 catch (Exception $ex) 
		  {
			$result["status"]='ko';
		    $result["msg"] .= ' error in connecting with KB ' . $ex; 
		 } 	
		/* registration of the device in the corresponding context broker */
		 // echo "valore local_result" . $local_result;
		if ($local_result!="errore") 
		 {
		   $result["status"]='ok';
		   $result["content"]=$local_result;
		   // information to be passed to the interface
           $result["visibility"] = $visibility;
		   if($result["content"]==null) $result["active"]=false;  else $result["active"]=true;
		   // end of information to be passed to the interface	
		   $result["msg"] .= "\n the device has been deleted from the KB";
		   // echo json_encode($result);
		   switch ($protocol)
		   {
		    case "ngsi": 
				 $res = delete_ngsi($name, $type, $contextbroker, $kind, $protocol, $format, $model, $latitude, $longitude, $visibility,  $frequency, $listnewAttributes, $ip, $port,$uri, $result);
				 break;
		    case "mqtt":
				$res = delete_mqtt($name, $type, $contextbroker, $kind, $protocol, $format, $latitude, $longitude, $visibility, $frequency, $listnewAttributes, $ip, $port,$uri, $result);
				 break;
			case "amqp":
                $res = delete_amqp($name, $type, $contextbroker, $kind, $protocol, $format, $latitude, $longitude, $visibility, $frequency, $listnewAttributes, $ip, $port,$uri, $result);
				break;	 
		  }
          if ($res=='ok')
          {
		    $result["msg"] .= "\n ok deletion from the context broker";
		  }
		  else 
		  {
		   $result["status"]='ko';
		   $result["msg"] .= "\n no deletion from the context broker";
		  }
		  return $result;
	}
    else
    {
	  $result["msg"].="\n error in the validation w.r.t. the KB";
	  $result["status"]='ko';
	  return $result;
	}
 } 
} // end of function deleteKB	


/* functions for insert/update/delete values from a specific device in the KB and context brokers */

function modify_valueKB($link, $device, $contextbroker, &$result)
{  
	
	//$result=array();
    $result["status"]='ok';
	$result["content"]='';
    $result["msg"]='';
  
	$listnewAttributes=generateAttributes($link, $device, $contextbroker);
	   
	$query  = "SELECT  d.uri, d.id, d.devicetype AS entityType, d.kind, d.format, d.macaddress, d.model, d.producer, d.protocol,  d.longitude, d.latitude, d.visibility, d.frequency, cb.name, cb.protocol as type, cb.ip, cb.port, cb.login, cb.password, cb.latitude as cblatitude, cb.longitude as cblongitude, cb.created FROM devices d JOIN contextbroker cb ON d.contextBroker = cb.name WHERE d.contextBroker='$contextbroker' and d.id='$device';";
	   
	$r_init = mysqli_query($link, $query);

	if (!$r_init) {
		$result["status"]='ko';
		$result["msg"] .= '\n error in reading data from context broker and device ' . mysqli_error($link) . $query;
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

	if (canBeModified($device, $type, $contextbroker, $kind, $protocol, $format,  $macaddress, $model, $producer, $latitude, $longitude, $visibility,  $frequency, $listnewAttributes, $result))
	  {
		  /* msg for the Knowledge base + registration on the KB */
		 // echo "entrato";
		  $msg=array();
		  $msg["id"]=     $device;
		  $msg["type"]=   $type;
		  $msg["kind"]=    $kind;
		  $msg["protocol"]=    $protocol;
		  $msg["format"]=      $format;
		  $msg["macaddress"]=      $macaddress;
		  $msg["model"]=      $model;
		  $msg["producer"]=      $producer;
		  $msg["latitude"]=    $latitude;
		  $msg["longitude"]=    $longitude;
		  $msg["frequency"]=    $frequency;
	      // $msg["visibility"]=    $visibility;
	      // if ($msg["visibility"]=='private')   $msg["owner"]=$owner;
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
					 else  $myatt["value_bounds"]=$att["healthiness_value"];
			$myatt["order"]=$att["order"];
			$myAttrs[]=$myatt;
			
		  }	 
		  $msg["attributes"]=$myAttrs;
		  
		  // echo json_encode($msg);	  
		  
		  try
		   {
			 $url="http://servicemap.disit.org/WebAppGrafo/api/v1/iot/insert";
			 $options = array(
					  'http' => array(
							  'header'  => "Content-Type: application/json;charset=utf-8",
							  'header'  => "Access-Control-Allow-Origin: *",
							  'method'  => 'POST',
				  'content' => json_encode($msg),
							  'timeout' => 30
					  )
				);
			 $context  = stream_context_create($options);
			 $local_result = @file_get_contents($url, false, $context);
		 } 
		 catch (Exception $ex) 
		  {
			$result["status"]='ko';
		    $result["msg"] .= ' error in connecting with KB ' . $ex; 
		 } 	
		/* registration of the device in the corresponding context broker */
		 // echo "valore local_result" . $local_result;
		if ($local_result!="errore") 
		 {
		   $result["status"]='ok';
		   $result["content"]=$local_result;
		   $result["msg"] .= "\n the device has been modified in the KB";
		   // echo "dentro" . json_encode($result);
		   switch ($protocol)
		   {
		    case "ngsi": 
				 $res = update_ngsi($device, $type, $contextbroker, $kind, $protocol, $format, $model, $latitude, $longitude, $visibility,  $frequency, $listnewAttributes, $ip, $port,$uri, $result);
				 break;
		    case "mqtt":
				$res =update_mqtt($device, $type, $contextbroker, $kind, $protocol, $format, $latitude, $longitude, $visibility,  $frequency, $listnewAttributes, $ip, $port,$uri, $result);
				 break;
			case "amqp":
                $res = update_amqp($device, $type, $contextbroker, $kind, $protocol, $format, $latitude, $longitude, $visibility,  $frequency, $listnewAttributes, $ip, $port,$uri, $result);
				break;	 
		  }
          if ($res=='ok')
          {
		    $result["msg"] .= "\n ok modification in the context broker";
		  }
		  else 
		  {
		   $result["status"]='ko';
		   $result["msg"] .= "\n no modification in the context broker";
		  }
		  return 1;
	}
    else
    {
	  $result["msg"].="\n error in the validation w.r.t. the KB";
	  $result["status"]='ko';
	  return 1;
	}
 } 
} // end of function modify_valueKB	



  
?>

