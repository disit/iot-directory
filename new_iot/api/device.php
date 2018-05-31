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

header("Content-type: application/json");
header("Access-Control-Allow-Origin: *\r\n");
include ('../config.php');
include ('common.php');
// session_start();
$link = mysqli_connect($host, $username, $password) or die("failed to connect to server !!");
mysqli_select_db($link, $dbname);

//Altrimenti restituisce in output le warning
error_reporting(E_ERROR | E_NOTICE);


function compare_values($obj_a, $obj_b) {
  return  strcasecmp($obj_a->value_name,$obj_b->value_name);
}

if(!$link->set_charset("utf8")) 
{
    exit();
}

if(isset($_REQUEST['action']) && !empty($_REQUEST['action'])) 
    {
        $action = $_REQUEST['action'];
    }
else
{
    exit();
}

require '../sso/autoload.php';
use Jumbojett\OpenIDConnectClient;


if (isset($_REQUEST['token'])) {
  $oidc = new OpenIDConnectClient('https://www.snap4city.org', $clientId, $clientSecret);
  $oidc->providerConfigParam(array('token_endpoint' => 'https://www.snap4city.org/auth/realms/master/protocol/openid-connect/token'));
  $tkn = $oidc->refreshToken($_REQUEST['token']);
  $accessToken = $tkn->access_token;
}
else {$accessToken="";}



$result=array("status"=>"","msg"=>"","content"=>"");	
/* all the primitives return an array "result" with the following structure

result["status"] = ok/ko; reports the status of the operation (mandatory)
result["msg"] a message related to the execution of the operation (optional)
result["content"] in case of positive execution of the operation the content extracted from the db (optional)

This array should be encoded in json
*/	


if ($action=="myinsert")
{
            $result["msg"]="";
            $contextbroker = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);  
            $id = mysqli_real_escape_string($link, $_REQUEST['id']);
			if ($accessToken!="")
		        registerPrivateDevice($contextbroker . "::" . $id, $contextbroker . "::" . $id, $accessToken, $result);
            print_r($result);	
}
else
if ($action=="insert")
{   

            $id = mysqli_real_escape_string($link, $_REQUEST['id']);
			$devicetype = mysqli_real_escape_string($link, $_REQUEST['type']); 
			$contextbroker = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);  
			$kind = mysqli_real_escape_string($link, $_REQUEST['kind']);  
			$protocol = mysqli_real_escape_string($link, $_REQUEST['protocol']);  
			$format = mysqli_real_escape_string($link, $_REQUEST['format']);  
			$macaddress = mysqli_real_escape_string($link, $_REQUEST['mac']);  
			$model = mysqli_real_escape_string($link, $_REQUEST['model']);  
			$producer = mysqli_real_escape_string($link, $_REQUEST['producer']);  
			$latitude= mysqli_real_escape_string($link, $_REQUEST['latitude']);  
			$longitude = mysqli_real_escape_string($link, $_REQUEST['longitude']);  
 			$visibility = mysqli_real_escape_string($link, $_REQUEST['visibility']);  
 			$frequency= mysqli_real_escape_string($link, $_REQUEST['frequency']);
                        $k1= $_REQUEST['k1'];
                        $k2= $_REQUEST['k2'];
 
		         
			$listAttributes= json_decode($_REQUEST['attributes']);

			registerKB($link, $id, $devicetype, $contextbroker, $kind, $protocol, $format, $macaddress, $model, $producer, $latitude, $longitude, $visibility, $frequency, $listAttributes, $result); 

			if ($result["status"]=='ko') return $result;
			
			if ($result["status"]=='ok' &&  $result["content"]==null)
			{
			 $q = "INSERT INTO devices(id, devicetype, contextBroker,  kind, protocol, format, macaddress, model, producer, latitude, longitude, visibility, frequency) " .
                 "VALUES('$id', '$devicetype', '$contextbroker', '$kind', '$protocol', '$format', '$macaddress', '$model', '$producer', '$latitude', '$longitude', '$visibility', '$frequency')";
			}
			else {
			$q = "INSERT INTO devices(id, devicetype, contextBroker,  kind, protocol, format, macaddress, model, producer, latitude, longitude,uri, visibility,  frequency,mandatoryproperties,mandatoryvalues) " .
                 "VALUES('$id', '$devicetype', '$contextbroker', '$kind', '$protocol', '$format', '$macaddress', '$model', '$producer', '$latitude', '$longitude', '" . $result["content"] . "', '$visibility', '$frequency',1,1)";
            }
			$r = mysqli_query($link, $q);
            if($r) 
            {
			    $result["msg"] .= "\n Device correctly inserted";
				// information to be passed to the interface
                $result["visibility"] = $visibility;	
				if($result["content"]==null) $result["active"]=false;  else $result["active"]=true;
				// end of information to be passed to the interface
				
				if ($visibility=="private" && $accessToken != "")
				{
				    $privmsg = array();
                                    $privmsg["elementId"]=$id;
                                    $privmsg["elementName"]=$id;				    
                                    $privmsg["elementUrl"]=$result["content"];
                                    $privmsg["elementDetails"]=array();
                                    $privmsg["elementDetails"]["k1"]= $k1;
                                    $privmsg["elementDetails"]["k2"]= $k2;
                                    $privmsg["elementDetails"]["contextbroker"]=$contextbroker;
                                    
									
									registerPrivateDevice($id, $privmsg, $accessToken, $result);
				}
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
				  if ($r1) {$result["msg"] .= "\n attribute $att->value_name correctly inserted";}
				  else {$result["msg"] .= "\n attribute $att->value_name was not inserted " . mysqli_error($link); $ok=false;}
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
         		 echo json_encode($result);
	             mysqli_close($link);   
            } 
            else 
            {
               $result["status"]='ko';
			   $result["msg"] .= "\n Problem in inserting the device $id:" . mysqli_error($link); 
			   echo json_encode($result);
	           mysqli_close($link); 
            }
}
else if ($action=="update")
{   
	$id = mysqli_real_escape_string($link, $_REQUEST['id']);
	$devicetype = mysqli_real_escape_string($link, $_REQUEST['type']); 
	$contextbroker = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);  
	$kind = mysqli_real_escape_string($link, $_REQUEST['kind']);  
	$protocol = mysqli_real_escape_string($link, $_REQUEST['protocol']);  
	$format = mysqli_real_escape_string($link, $_REQUEST['format']);  
	$macaddress = mysqli_real_escape_string($link, $_REQUEST['mac']);  
	$model = mysqli_real_escape_string($link, $_REQUEST['model']);  
	$producer = mysqli_real_escape_string($link, $_REQUEST['producer']);  
	$latitude= mysqli_real_escape_string($link, $_REQUEST['latitude']);  
	$longitude = mysqli_real_escape_string($link, $_REQUEST['longitude']);  
	$uri = mysqli_real_escape_string($link, $_REQUEST['uri']);
	
	$visibility = mysqli_real_escape_string($link, $_REQUEST['visibility']);  
	$frequency= mysqli_real_escape_string($link, $_REQUEST['frequency']);
	
	$listdeleteAttributes= json_decode($_REQUEST['deleteattributes']);
	$listAttributes= json_decode($_REQUEST['attributes']);
	$listnewAttributes= json_decode($_REQUEST['newattributes']);
	
	if ($listAttributes==null) $merge=$listnewAttributes;
	else if ($listnewAttributes==null) $merge=$listAttributes;
	else $merge=arrayu_merge($listAttributes,$listnewAttributes, 'compare_values');
	
	if ($listdeleteAttributes!=null) $merge= array_udiff($merge, $listdeleteAttributes, 'compare_values');
	
	 updateKB($link, $id, $devicetype, $contextbroker, $kind, $protocol, $format, $macaddress, $model, $producer, $latitude, $longitude,$visibility, $frequency, $merge, $uri, $result); 
	
    if ($result["status"]=='ko') return $result;
	
	 
	if ($result["status"]=='ok' &&  $result["content"]==null)
	{
		$q = "UPDATE devices SET devicetype='$devicetype', kind= '$kind', protocol='$protocol', format='$format', macaddress='$macaddress', model='$model', producer='$producer', latitude='$latitude', longitude='$longitude', frequency = '$frequency', visibility = '$visibility' WHERE id='$id' and contextBroker='$contextbroker'";
	}
	else {			
		$q = "UPDATE  devices SET uri = '". $result["content"] . "', mandatoryproperties=1, mandatoryvalues=1, devicetype='$devicetype', kind= '$kind', protocol='$protocol', format='$format', macaddress='$macaddress', model='$model', producer='$producer', latitude='$latitude', longitude='$longitude', frequency = '$frequency', visibility = '$visibility'  WHERE id='$id' and contextBroker='$contextbroker'";
	}
	
	$r = mysqli_query($link, $q);
			
    if($r) 
    {
		$result["msg"] .= "\n Device correctly updated" . $q; 
		// information to be passed to the interface
        $result["visibility"] = $visibility;
		if($result["content"]==null) $result["active"]=false;  else $result["active"]=true;
		// end of information to be passed to the interface
				
		$ok=true;$q="";
		$a=0;
		$b=1;
		while ($a < count($listAttributes) && $ok)
		{
		   $att=$listAttributes[$a];	
		   if ($att->healthiness_criteria=="refresh_rate")  $hc="value_refresh_rate";
		   else if ($att->healthiness_criteria=="different_values") $hc="different_values";
		   else $hc="value_bounds";
		 
		 // print_r($att);
		  $b=$a+1;
		  $upquery="UPDATE `event_values` SET `value_name`='". $att->value_name . "',`data_type`='" . $att->data_type . "',`order`=$b,`value_type`='". $att->value_type. "', `editable`='". $att->editable. "', `value_unit`='". $att->value_unit."', `healthiness_criteria`='". $att->healthiness_criteria."', `value_refresh_rate`='". $att->healthiness_value ."'  WHERE  `cb`='$contextbroker' AND `device`='$id' AND value_name='". $att->value_name . "';";
		  $r1 = mysqli_query($link, $upquery);
		//   echo $upquery;
		  if ($r1) {$result["msg"] .= "\n attribute $att->value_name correctly updated";}
		  else {$result["msg"] .= "\n attribute $att->value_name was not updated " . mysqli_error($link); $ok=false;} 
		  $a++;
		}
	//echo "valore di ok". $ok;    
		if ($ok==true)
		{
			$result["msg"] .= "\n old attributes correctly updated" . $q; 	    
			$q="";
			$a=0;
			while ($a < count($listnewAttributes) && $ok)
			{
				$att=$listnewAttributes[$a];
				if ($att->healthiness_criteria=="refresh_rate")  $hc="value_refresh_rate";
		        else if ($att->healthiness_criteria=="different_values") $hc="different_values";
		        else $hc="value_bounds";
		   
				$insertquery="INSERT INTO `event_values`(`cb`, `device`, `value_name`, `data_type`, `order`, `value_type`, `editable`, `value_unit`, `healthiness_criteria`, `$hc`) VALUES ('$contextbroker','$id','$att->value_name','$att->data_type','$b','$att->value_type','$att->editable','$att->value_unit','$att->healthiness_criteria','$att->healthiness_value');";
				$r1 = mysqli_query($link, $insertquery);
				if ($r1) {$result["msg"] .= "\n attribute $att->value_name correctly inserted";}
				else {$result["msg"] .= "\n attribute $att->value_name was not inserted " . mysqli_error($link); 
				      $ok=false;} 
				$b++;
				$a++;
			}
		 }
		  if ($ok==true)
		{
			$result["msg"] .= "\n new attributes correctly inserted" . $q; 
			$q="";
			$a=0;
			while ($a < count($listdeleteAttributes) && $ok)
			{
				$att=$listdeleteAttributes[$a];
				$a++;
				$deletequery="DELETE FROM `event_values` WHERE `cb`='$contextbroker' AND `device`='$id' AND value_name='". $att->value_name . "';";
				$r1 = mysqli_query($link, $deletequery);
				if ($r1) {$result["msg"] .= "\n attribute $att->value_name correctly deleted";}
				else {$result["msg"] .= "\n attribute $att->value_name was not deleted " . mysqli_error($link); $ok=false;} 
			}
		}					
		if ($ok==true)
		 {
			 $result["status"]='ok';
		 }
		 else
		 {
		   $result["status"]='ko';
		 }
		 echo json_encode($result);
		 mysqli_close($link); 
	}
	else
	{
	  $result["status"]='ko';
	  $result["msg"] .= "\n Problem in updating the device $id:" . $q . mysqli_error($link); 
	  echo json_encode($result);
	  mysqli_close($link); 
	}
}  
else if ($action=="delete")
{
     $id = $_REQUEST['id'];
	 $cb = $_REQUEST['contextbroker'];
	 
     $result=deleteKB($link, $id, $cb);	   

	 if ($result["status"]=='ko') return $result;
			
	 $query = "DELETE FROM devices WHERE id = '$id' and contextBroker='$cb'";
     $r = mysqli_query($link, $query);
	 
     if($r)
	 {
		 $result["status"]='ok';
		 
	 }
	 else
	 {
	  $result["status"]='ko';
	  $result["msg"] .= "\n Problem in deleting the device $id:" . mysqli_error($link); 
	 }
	 echo json_encode($result);
     mysqli_close($link);
} // end delete
else if($action == 'get_device_attributes')
{

	$id = mysqli_real_escape_string($link, $_REQUEST['id']);
	$cb = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
    
	
	$result = array();
	$q1 = "SELECT * FROM event_values WHERE cb = '$cb' AND device = '$id'";
    $r1 = mysqli_query($link, $q1);

     $attributes = array();
	 if($r1){
         while($row = mysqli_fetch_assoc($r1)) 
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
		$result['status'] = 'ok';
		$result['content'] = $attributes;
     }
	 else
	 {
	    $result['status'] = 'ko'; // . $q1 . mysqli_error($link);
		$result['msg'] = '<script type="text/javascript">'.
						 'alert("Error: errors in reading data about devices. <br/>' .
						   mysqli_error($link) . $q .
						   '")'. '</script>';
	}
	echo json_encode($result);
	mysqli_close($link);  
}	
else if($action == 'get_param_values')
{	
	$result = array();
	$result['status'] = 'ok'; 
	$result['value_type'] = generatelabels($link);
	$result['data_type'] = generatedatatypes($link);
	$result['value_unit'] = generateunits($link);
	echo json_encode($result);
	mysqli_close($link); 
}	
else if($action == "get_all_device")
{
	$privateDevices= "";
    if (!empty($accessToken)) 
	{ 
	 $privateDevices = getPrivateDevice($accessToken, $result); 
	}
    if ($privateDevices=="")
		$q = "SELECT `contextBroker`, `id`, `uri`, `devicetype`, `kind`, CASE WHEN mandatoryproperties AND mandatoryvalues THEN \"active\" ELSE \"iddle\" END AS status1, `macaddress`, `model`, `producer`, `longitude`, `latitude`, `protocol`, `format`, `visibility`, `frequency`, `created` FROM `devices` WHERE visibility =\"public\"";
	else 
		$q = "SELECT `contextBroker`, `id`, `uri`, `devicetype`, `kind`, 
	                 CASE WHEN mandatoryproperties AND mandatoryvalues 
					         THEN \"active\" ELSE \"iddle\" END AS status1, 
					`macaddress`, `model`, `producer`, `longitude`, `latitude`, 
					`protocol`, `format`, `visibility`, `frequency`, `created` 
					FROM `devices` WHERE visibility =\"public\" or (visibility=\"private\" and $privateDevices) ";
	$r = mysqli_query($link, $q);
	
	if($r) 
	{
	  $result['status'] = 'ok';
	  $result['content'] = array();
          while($row = mysqli_fetch_assoc($r)) 
          {
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
			$rec["visibility"]= $row["visibility"];
			$rec["frequency"]= $row["frequency"];
			$rec["created"]= $row["created"];
			
            if (isset($result["keys"][$rec["id"]]))
			{
				$rec["k1"]=$result["keys"][$rec["id"]]["k1"];
				$rec["k2"]=$result["keys"][$rec["id"]]["k2"];
                                $result["msg"].= "trovato"; 
			}
			else{
				$rec["k1"]="";
				$rec["k2"]="";	
			}
            array_push($result['content'], $rec);           
     }
    }
	else{
	   $result['status'] = 'ko';
	   $result['msg'] = '<script type="text/javascript">'.
						 'alert("Error: errors in reading data about devices. <br/>' .
						   mysqli_error($link) . $q .
						   '")'. '</script>';
	}    
	echo json_encode($result);
	mysqli_close($link);
}
else if($action == "get_all_private_device")
{
        $privateDevices= "";
    if (!empty($accessToken))
        {
         $privateDevices = getPrivateDevice($accessToken, $result);
        }
    if ($privateDevices!="")
    {                
          $q = "SELECT `contextBroker`, `id`, `uri`, `devicetype`, `kind`,
                         CASE WHEN mandatoryproperties AND mandatoryvalues
                                                 THEN \"active\" ELSE \"iddle\" END AS status1,
                                        `macaddress`, `model`, `producer`, `longitude`, `latitude`,
                                        `protocol`, `format`, `visibility`, `frequency`, `created`
                                        FROM `devices` WHERE visibility=\"private\" and $privateDevices ";
        $r = mysqli_query($link, $q);

        if($r)
        {
         $result['status'] = 'ok';
         $result['content'] = array();
         while($row = mysqli_fetch_assoc($r))
           {
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
			$rec["visibility"]= $row["visibility"];
			$rec["frequency"]= $row["frequency"];
			$rec["created"]= $row["created"];
			
            if (isset($result["keys"][$rec["id"]]))
			{
				$rec["k1"]=$result["keys"][$rec["id"]]["k1"];
				$rec["k2"]=$result["keys"][$rec["id"]]["k2"];
                                $result["msg"].= "trovato"; 
			}
			else{
				$rec["k1"]="";
				$rec["k2"]="";	
			}
            array_push($result['content'], $rec);           
           }
        }
        else{
           $result['status'] = 'ko';
           $result['msg'] = '<script type="text/javascript">'.
                                                 'alert("Error: errors in reading data about devices. <br/>' .
                                                   mysqli_error($link) . $q .
                                                   '")'. '</script>';
        }
     }
        echo json_encode($result);
        mysqli_close($link);
}
else if($action == "get_subset_device")
{
    if (!empty($accessToken))
        {
         $privateDevices = getPrivateDevice($accessToken, $result);
        }
    $selection= json_decode($_REQUEST['select']);
	$a=0;
	$cond="";
	if (count($selection)!=0)
	{
	    
		while ($a < count($selection))
		{
			 $sel = $selection[$a];
			 $cond .= " (id = '" . $sel->id . "' AND contextbroker = '" . $sel->contextBroker . "') ";
			 if ($a != count($selection)-1)  $cond .= " OR ";
			 $a++;
		 }
		
		$q = "
		SELECT DISTINCT `contextBroker`, `id`, `uri`, `devicetype`, `kind`, CASE WHEN mandatoryproperties AND mandatoryvalues THEN \"active\" ELSE \"iddle\" END 
	      AS status, `macaddress`, `model`, `producer`, `longitude`, `latitude`, `protocol`, `format`, `visibility`, `frequency`, `created`  FROM devices WHERE " . $cond;
		// echo $q;
	}
    else
	    $q = "SELECT DISTINCT * FROM devices WHERE 1=2";
	
    $r = mysqli_query($link, $q);
	
	if($r) 
	{
	 $result['status'] = 'ok';
	 $result['content'] = array();
     while($row = mysqli_fetch_assoc($r)) 
     {
 	   if (($row["visibility"]=="public") || ($row["visibility"]=="private" 
	       && isset($result["keys"][$row["id"]])))
	   {   
		   
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
			$rec["visibility"]= $row["visibility"];
			$rec["frequency"]= $row["frequency"];
			$rec["created"]= $row["created"];
			
            if (isset($result["keys"][$rec["id"]]))
			{
				$rec["k1"]=$result["keys"][$rec["id"]]["k1"];
				$rec["k2"]=$result["keys"][$rec["id"]]["k2"];
            }
			else{
				$rec["k1"]="";
				$rec["k2"]="";	
			}
	        array_push($result['content'], $rec); 
	   }
	 }
      $result['msg'] = $q;
	}
	else{
	   $result['status'] = 'ko';
	   $result['msg'] = '<script type="text/javascript">'.
						 'alert("Error: errors in reading data about devices. <br/>' .
						   mysqli_error($link) . $q .
						   '")'. '</script>';
	}    
	echo json_encode($result);
	mysqli_close($link);
}
else if($action == "get_all_device_latlong")
{
    $privateDevices= "";
    if (!empty($accessToken)) 
	{ 
	 $privateDevices = getPrivateDevice($accessToken, $result); 
	}
	if ($privateDevices=="")
      	$q = "SELECT id, contextBroker, latitude, longitude FROM devices WHERE visibility=\"public\"";
	else
        $q = "SELECT id, contextBroker, latitude, longitude FROM devices WHERE 
		visibility=\"public\" or (visibility=\"private\" and $privateDevices)";

	$r = mysqli_query($link, $q);
	
	if($r) 
	{
	 $result['status'] = 'ok';
	 $result['content'] = array();
     while($row = mysqli_fetch_assoc($r)) 
     {
	   array_push($result['content'], $row);
	 }
    }
	else{
	   $result['status'] = 'ko';
	   $result['msg'] = '<script type="text/javascript">'.
						 'alert("Error: errors in reading data about devices. <br/>' .
						   mysqli_error($link) . $q .
						   '")'. '</script>';
	}    
	echo json_encode($result);
	mysqli_close($link);
}
else if ($action=="get_config_data")
{
    $privateDevices= "";
    if (!empty($accessToken)) 
	{ 
	 $privateDevices = getPrivateDevice($accessToken, $result); 
	}
	if ($privateDevices=="")
	  $q  = "SELECT  d.uri, d.id, d.devicetype AS entityType, d.visibility, d.frequency, d.kind, d.protocol,  d.longitude, d.latitude, d.contextBroker, cb.name, cb.protocol as type, cb.ip, cb.port FROM devices d JOIN contextbroker cb ON d.contextBroker = cb.name  WHERE visibility=\"public\" ORDER BY d.id;";
	else
	    $q  = "SELECT  d.uri, d.id, d.devicetype AS entityType, d.visibility, d.frequency, d.kind, d.protocol,  d.longitude, d.latitude, d.contextBroker, cb.name, cb.protocol as type, cb.ip, cb.port FROM devices d JOIN contextbroker cb ON d.contextBroker = cb.name  WHERE visibility=\"public\" or (visibility=\"private\" and $privateDevices) ORDER BY d.id;";
	
	$r = mysqli_query($link, $q);

	if($r) 
	{
	     $res = array();
		 while($row = mysqli_fetch_assoc($r)) 
		{
		  $rec=array();
		  $rec["entityType"]=$row["entityType"];
		  $rec["visibility"]=$row["visibility"];
		  $rec["frequency"]=$row["frequency"];
		  $rec["uri"]=$row["uri"];
		  $rec["kind"]=$row["kind"];
		  $rec["protocol"]=$row["protocol"];
		  $rec["longitude"]=$row["longitude"];
		  $rec["latitude"]=$row["latitude"];
		  $rec["contextBroker"]=$row["contextBroker"];
		  $rec["name"]=$row["name"];
		  $rec["type"]=$row["type"];
		  $rec["ip"]=$row["ip"];
		  $rec["port"]=$row["port"];
		  
		  $res[$row["id"]]=$rec;  
		}
		$result["status"]="ok";
		$result["content"]=$res;
	} 
	else
	{
		$result["status"]="ko";
		$result['msg'] = mysqli_error($link);
	}
	echo json_encode($result);
	mysqli_close($link);
}
else if ($action == 'get_config_data_values')
{
    $privateDevices= "";
    if (!empty($accessToken)) 
	{ 
	 $privateDevices = getPrivateDevice($accessToken, $result); 
	}
    $res=array(); 

	if ($privateDevices=="")
       $q  = "SELECT v.cb, v.device, v.value_name, v.data_type, v.value_type, v.editable, v.value_unit, v.healthiness_criteria, d.latitude, d.longitude, d.protocol, d.visibility, d.kind FROM `event_values` v JOIN devices d ON (v.device=d.id and v.cb = d.contextbroker) WHERE visibility=\"public\"  ORDER BY v.value_name;";
    else
       $q  = "SELECT v.cb, v.device, v.value_name, v.data_type, v.value_type, v.editable, v.value_unit, v.healthiness_criteria, d.latitude, d.longitude, d.protocol, d.visibility, d.kind FROM `event_values` v JOIN devices d ON (v.device=d.id and v.cb = d.contextbroker) WHERE visibility=\"public\" or (visibility=\"private\" and $privateDevices)  ORDER BY v.value_name;";		
  
  $r = mysqli_query($link, $q);

	if($r) 
	{
		while($row = mysqli_fetch_assoc($r)) 
		{
			array_push($res, $row);
		}
		$result["status"]="ok";
		$result["content"]=$res;
	} 
	else
	{
		$result["status"]="ko";
		$result['msg'] = mysqli_error($link);
	}
	echo json_encode($result);
	mysqli_close($link);
}
else if ($action=="exist_device")
{
	$id = mysqli_real_escape_string($link, $_REQUEST['id']);
	$cb = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);

	$result = array();
	$q1 = "SELECT * FROM devices WHERE contextbroker = '$cb' AND id = '$id'";
    $r = mysqli_query($link, $q1);
	
	if($r) 
	{
	 $result['status'] = 'ok';
	 $row = mysqli_fetch_assoc($r);
	 if ($row)
	 {
	   $result['content']=1;
	 }
     else
	 {
	   $result['content']=0;
	 }
    }
	else{
	   $result['status'] = 'ko';
	   $result['msg'] = 'The following errors occurred:' .
						   mysqli_error($link) . $q1;
	}    
	echo json_encode($result);
	mysqli_close($link);
	
}
else if($action == "get_device_with_attributes")
{ // Elf: this function is NOT to be included in the API 

     
	$q = "SELECT d.id, d.devicetype, d.protocol, d.format,
       d.latitude AS dlat, d.longitude AS dlong, c.name,  c.ip, c.port, c.latitude, c.longitude FROM
	   devices d JOIN contextbroker c ON (d.contextbroker=c.name) WHERE mandatoryproperties=1 and mandatoryvalues=1 order by d.id";
	$r = mysqli_query($link, $q);

	 echo "devicetype\t protocol\t format\t dlat\t dlong\t name\t ip\t port\t latitude\t longitude\t";
	 for ($i=1; $i<=8; $i++)
	 {
	   echo "value_name$i\t data_type$i\t";
	 }
	 echo "\n";
	 while($row = mysqli_fetch_assoc($r)) 
     {
	   echo $row["devicetype"] . "\t" . $row["protocol"] . "\t" . $row["format"] . "\t" . $row["dlat"] . "\t" . $row["dlong"] . "\t" . $row["name"] . "\t" . $row["ip"] . "\t" . $row["port"] . "\t" . $row["latitude"] . "\t" . $row["longitude"];
	 
	   $q1 = "SELECT v.value_name, v.data_type FROM event_values v WHERE device='$row[id]' and cb='$row[name]' order by value_name;";
	   //echo $q1;
	   $r1 = mysqli_query($link, $q1);
	   $i=0;
	   while($row1 = mysqli_fetch_assoc($r1)) 
       {
	    echo $row1["value_name"] . "\t" . $row1["data_type"] . "\t";
		$i++;
	   }
	   for ($k=$i; $k <=8; $k++)
	   {
	     echo "null\t null\t";
	   }
	   echo "\n";
     }   
	// echo json_encode($result);
	mysqli_close($link);
}
else 
{
	$result['status'] = 'ko';
	$result['msg'] = 'invalid action ' . $action;
	echo json_encode($result);
}




