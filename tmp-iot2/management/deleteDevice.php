<?php

/* Dashboard Builder.
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

   include '../config.php';
   
   //Altrimenti restituisce in output le warning
   error_reporting(E_ERROR | E_NOTICE);
   
//   session_start(); 
   $link = mysqli_connect($host, $username, $password);
   mysqli_select_db($link, $dbname);

 
/* ****FUNCTIONS FOR THE DELETION OF THE REGISTRATION OF A DEVICE IN THE KNOWLEDGE BASE AND IN THE CONTEXT BROKER ****************** */			

	function canBeRegistered($name, $type, $contextbroker, $kind, $protocol, $format,  $macaddress, $model, $producer, $latitude, $longitude, $listnewAttributes)
	{
	  $error=false;
	  // echo "valore di erroe" . $error;
	  if ($name==null || $name=="") { $error=true;}
      if ($contextbroker==null || $contextbroker=="") {$error=true;}	  
	  if ($type==null || $type=="") {$error=true;}
	  if (!($kind=="sensor" || $kind=="actuator")) {$error=true;}
	  if ($latitude < -90 && $latitude>90) {$error=true;}
	  if ($longitude < -180 && $longitude>180) {$error=true;}
	  if (!($protocol=="ngsi" || $protocol=="mqtt" || $protocol=="amqp")) {$error=true;}
	  // echo "valore di erroe" . $error;
	  // print_r($listnewAttributes);
	  if (count($listnewAttributes)==0) $error=true;
	  // echo "valore di erroeList" . $error;
	    
	   foreach($listnewAttributes as $att)
		  {
		    //print_r($att);
			if ($att->data_type==null || $att->data_type=="") {$error=true;}
			if ($att->value_unit==null || $att->value_unit=="") {$error=true;}
			if ($att->value_type==null || $att->value_type=="") {$error=true;}
		    if (!($att->healthiness_criteria=="refresh_rate" || $att->healthiness_criteria=="different_values" || 
			      $att->healthiness_criteria=="within_bounds")) {$error=true;}
		    if ($att->healthiness_criteria=="refresh_rate" && $att->healthiness_value=="") {$error=true;}
			// echo "valore di erroe" . $error;
			// if ($att->healthiness_criteria=="different_values" && ($att->different_values=="" || !is_int($att->different_values))) {$error=true;}
		  }
	   if ($error) return false;	  
	   return true; 
	}
	

				
	function delete_ngsi($name, $type, $contextbroker, $kind, $protocol, $format, $model, $latitude, $longitude, $listnewAttributes, $ip, $port,$uri)
	{
	  
	   $result = "Ok";
	   $url_orion="http://$ip:$port/v2/entities/$name";  
      
      try
         {  
			// Setup cURL
			$ch = curl_init($url_orion);
			$authToken = 'OAuth 2.0 token here';
			curl_setopt_array($ch, array(
    			CURLOPT_DELETE => TRUE,
    			CURLOPT_RETURNTRANSFER => TRUE,
    			CURLOPT_HTTPHEADER => array(
					'Authorization: '.$authToken,
					'Content-Type: application/json'),
				// CURLOPT_POSTFIELDS => json_encode($msg_orion)
		    ));

		    // Send the request
		    $response_orion = curl_exec($ch);

		    // Check for errors
		    if($response_orion === FALSE){
    			die(curl_error($ch));
			}

		    // Decode the response
		    $responseData = json_decode($response_orion, TRUE);

		    // Print the date from the response
		    // echo $responseData['published'];
            }
              catch (Exception $ex) 
            {
		      $result="errore"; echo $ex;
            } 
            return $result; 
	}
	
	function delete_mqtt($name, $type, $contextbroker, $kind, $protocol, $format, $latitude, $longitude, $listnewAttributes, $ip, $port,$uri)
	{
	  return "Ok";
	}
	
	function delete_amqp($name, $type, $contextbroker, $kind, $protocol, $format, $latitude, $longitude, $listnewAttributes, $ip, $port,$uri)
	{
	  return "Ok";
	}
	
	function generateAttributes($link,$name,$cb)
{
     $query2 = "SELECT * FROM event_values WHERE cb = '$cb' AND device = '$name'";
     $res = mysqli_query($link, $query2) or die(mysqli_error($link));
     $attributes = array();
	 
	  if($res){
         while($row = mysqli_fetch_assoc($res)) 
                { unset($row["different_values"]);
				  unset($row["value_bounds"]);
				  array_push($attributes, $row);
                }
     }
	 //print_r($attributes);
	 return $attributes;
}

	
	
	function deleteKB($link, $name, $contextbroker)
   {  
   
      $listnewAttributes=generateAttributes($link, $name, $contextbroker);
   
      $query  = "SELECT  d.uri, d.id, d.devicetype AS entityType, d.kind, d.format, d.macaddress, d.model, d.producer, d.protocol,  d.longitude, d.latitude, cb.name, cb.protocol as type, cb.ip, cb.port, cb.login, cb.password, cb.latitude as cblatitude, cb.longitude as cblongitude, cb.created FROM devices d JOIN contextbroker cb ON d.contextBroker = cb.name WHERE d.contextBroker='$contextbroker' and d.id='$name';";
           
		   $r_init = mysqli_query($link, $query);

      if($r_init) 
      {
	    //echo "dentro";
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
		$ip=$row["ip"];
		$port=$row["port"];
		$uri=$row["uri"];
	  } 
	  else return null;
      if ($uri==null || $uri="") return null;    
      if (canBeRegistered($name, $type, $contextbroker, $kind, $protocol, $format,  $macaddress, $model, $producer, $latitude, $longitude, $listnewAttributes))
	  {
		  $result = "Ok";
		  $res="Ok";
		  /* msg for the Knowledge base + registration on the KB */
		  
		  $msg=array();
		  $msg["id"]=     $name;
		  // $msg["uri"]=     $uri;
		  $msg["type"]=   $type;
		  $msg["kind"]=    $kind;
		  $msg["protocol"]=    $protocol;
		  $msg["format"]=      $format;
		  $msg["macaddress"]=      $macaddress;
		  $msg["model"]=      $model;
		  $msg["producer"]=      $producer;
		  $msg["latitude"]=    $latitude;
		  $msg["longitude"]=    $longitude;
		  $msg["frequency"]=    0;
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
			if ($att["healthiness_criteria"]=="refresh_rate") $myatt["value_refresh_rate"]=$att["value_refresh_rate"];
			else if ($att["healthiness_criteria"]=="different_values") $myatt["different_values"]=$att["different_values"];
					 else  $myatt["value_bounds"]=$att["value_bounds"];
			$myatt["order"]=$att["order"];
			$myAttrs[]=$myatt;
			
		  }	 
		  $msg["attributes"]=$myAttrs;
		  
		  //echo json_encode($msg);	  
		  
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
			 $result = @file_get_contents($url, false, $context);
		 } 
		 catch (Exception $ex) 
		  {
			$result="errore"; echo $ex; 
		 } 	
		/* registration of the device in the corresponding context broker */
		// echo $result;
		if ($result!="errore") 
		 {    
		 switch ($protocol)
		 {
		   case "ngsi": 
				 $res = delete_ngsi($name, $type, $contextbroker, $kind, $protocol, $format, $model, $latitude, $longitude, $listnewAttributes, $ip, $port,$uri);
				 break;
		   case "mqtt":
				$res = "Ok"; //delete_mqtt($name, $type, $contextbroker, $kind, $protocol, $format, $latitude, $longitude, $listnewAttributes, $ip, $port,$uri);
				 break;
			case "amqp":
                $res = "Ok";// delete_amqp($name, $type, $contextbroker, $kind, $protocol, $format, $latitude, $longitude, $listnewAttributes, $ip, $port,$uri);
				break;	 
		 }	 
		}
		    if ($res=="Ok") return $result;
			else return null;
	}
    else return null;	
   } // end of function deleteKB	
  
   
   if(!$link->set_charset("utf8")) 
   {
       die();
   }

  // if(isset($_SESSION['loggedRole']))
   if (true)
   {
      $name = $_POST['name'];
	  $cb = $_POST['cb'];
	  
      $res=deleteKB($link, $name, $cb);	   
//      $beginTransactionResult = mysqli_begin_transaction($link, MYSQLI_TRANS_START_READ_WRITE);
      //echo "sono uscito" . $res;
	  
	  //if ($res!=null)
	  //{
       $query = "DELETE FROM devices WHERE id = '$name' and contextbroker='$cb'";
       $result = mysqli_query($link, $query);
	   //echo $query;

       if($result)
       {
       //      $commit = mysqli_commit($link);
             mysqli_close($link);
             echo 1;
       }
       else
       {
      //       $rollbackResult = mysqli_rollback($link);
              mysqli_error($link);
        	   mysqli_close($link);
             echo 0;
       }
	  // }else {echo 0;} 
   }

