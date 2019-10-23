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



$result=array("status"=>"","msg"=>"","content"=>"","log"=>"", "error_msg"=>"");	
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


if (isset($_REQUEST['nodered']))
{
   if ($_REQUEST['token']!='undefined')
      $accessToken = $_REQUEST['token'];
   else $accessToken = "";
} 
else
{
if (isset($_REQUEST['token'])) {
  $oidc = new OpenIDConnectClient($keycloakHostUri, $clientId, $clientSecret);
  $oidc->providerConfigParam(array('token_endpoint' => $keycloakHostUri.'/auth/realms/master/protocol/openid-connect/token'));

  $tkn = $oidc->refreshToken($_REQUEST['token']);
  $accessToken = $tkn->access_token;
}
else $accessToken ="";
}

if (isset($_REQUEST['username'])) {
	$currentUser = $_REQUEST['username'];
}

foreach ($_REQUEST as $key =>$param) {
    if($key=="id" || $key=="device" || $key=="cb" || $key=="contextbroker" || $key=="model" || $key=="value_name"){
        if($key=="model"){
            $param=str_replace(' ', '', $param);
        }
        if($key=="id"|| $key=="device"){
            $param=str_replace(':','', $param);
        }
        preg_match($regphp, $param,  $matches);
        if (count($matches)>0){
            $result["status"]="ko";
            $result["error_msg"]="strange characters are not allowed";
            $action="";
            mysqli_close($link);
        }
    }
    else if($key=="attributes"|| $key=="newattributes" || $key=="deleteattributes"){
        $listAttributes= json_decode($param);
        $a=0;
        while ($a < count($listAttributes))
		{
		  $att=$listAttributes[$a];
          $attName=$att->value_name;
          preg_match($regphp, $attName,  $matches);
          if (count($matches)>0){
            $result["status"]="ko";
            $result["error_msg"]="strange characters are not allowed";
            $action="";
            mysqli_close($link);
          }
            $a++;
        }
    }
}


if ($action=="insert")
{   
	//Sara2510 - for logging purpose
	$username = mysqli_real_escape_string($link, $_REQUEST['username']);
	
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
	$organization= mysqli_real_escape_string($link, $_REQUEST['organization']);
    
	//MM 
	if (isset($_REQUEST['shouldbeRegistered'])) $shouldbeRegistered=$_REQUEST['shouldbeRegistered'];
	else $shouldbeRegistered=true;
	
	$k1= $_REQUEST['k1'];
	$k2= $_REQUEST['k2'];
	if (isset( $_REQUEST['edgegateway_type']))
			 $edgegateway_type = $_REQUEST['edgegateway_type'];
	else $edgegateway_type="";
	if (isset( $_REQUEST['edgegateway_uri']))
			 $edgegateway_uri = $_REQUEST['edgegateway_uri'];
	else	$edgegateway_uri="";	
	
		 
	$listAttributes= json_decode($_REQUEST['attributes']);

	//retrieve the kburl -> this is needed since this api can be called from OUTSIDE of the IoT directory
	$kburl="";
	if (!isset($_SESSION['kbUrl'])){
		$infokburl=get_organization_info($organizationApiURI, $organization);
	        if(!is_null($infokburl)){
        	        $kburl=$infokburl["kbUrl"];
	        }
	}
	else {
		$kburl=$_SESSION['kbUrl'];
	}

	insert_device($link, $id,$devicetype,$contextbroker,$kind,$protocol,$format,$macaddress,$model,$producer,
		   $latitude,$longitude,$visibility, $frequency, $k1, $k2, $edgegateway_type, $edgegateway_uri,
		   $listAttributes, $pathCertificate,$accessToken,$result,$shouldbeRegistered, $organization, $kburl, $username);
			//my_log($result);
	
        
	//Sara2510 - For logging purpose
	$deviceName = $id . " ".$contextbroker;

	if($result["status"]=="ok"){
			logAction($link,$username,'device','insert',$deviceName,$organization,'','success');		
	}
	else if($result["status"]=="ko"){
			logAction($link,$username,'device','insert',$deviceName,$organization,'','faliure');				
	}
         my_log($result); //MM0301
}
else if ($action=="update")
{   
	//Sara2510 - for logging purpose
	$username = mysqli_real_escape_string($link, $_REQUEST['username']);
    $organization = mysqli_real_escape_string($link, $_REQUEST['organization']);

    $id = mysqli_real_escape_string($link, $_REQUEST['id']);
	$devicetype = mysqli_real_escape_string($link, $_REQUEST['type']); 
	$contextbroker = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);  
	$old_contextbroker = mysqli_real_escape_string($link, $_REQUEST['gb_old_cb']); 
	$kind = mysqli_real_escape_string($link, $_REQUEST['kind']);  
	$protocol = mysqli_real_escape_string($link, $_REQUEST['protocol']);  
	$format = mysqli_real_escape_string($link, $_REQUEST['format']);  
	$macaddress = mysqli_real_escape_string($link, $_REQUEST['mac']);  
	$model = mysqli_real_escape_string($link, $_REQUEST['model']);  
	$producer = mysqli_real_escape_string($link, $_REQUEST['producer']);  
	$latitude= mysqli_real_escape_string($link, $_REQUEST['latitude']);  
	$longitude = mysqli_real_escape_string($link, $_REQUEST['longitude']);  
	$uri = mysqli_real_escape_string($link, $_REQUEST['uri']);
	$dev_organization = mysqli_real_escape_string($link, $_REQUEST['dev_organization']);
	$k1= $_REQUEST['k1'];
        $k2= $_REQUEST['k2'];
	if (isset( $_REQUEST['edgegateway_type']))
		$edgegateway_type = $_REQUEST['edgegateway_type'];
	else $edgegateway_type="";
	if (isset( $_REQUEST['edgegateway_uri']))
		$edgegateway_uri = $_REQUEST['edgegateway_uri'];
	else $edgegateway_uri="";
	//Sara2510 - for logging purpose
	$deviceName = $id . " ".$contextbroker;
	$visibility = mysqli_real_escape_string($link, $_REQUEST['visibility']);  
	$frequency= mysqli_real_escape_string($link, $_REQUEST['frequency']);
	
	$listdeleteAttributes= json_decode($_REQUEST['deleteattributes']);
	$listAttributes= json_decode($_REQUEST['attributes']);
	$listnewAttributes= json_decode($_REQUEST['newattributes']);
	
	if ($listAttributes==null) $merge=$listnewAttributes;
	else if ($listnewAttributes==null) $merge=$listAttributes;
	else $merge=array_merge($listAttributes,$listnewAttributes);
	//else $merge=array_merge($listAttributes,$listnewAttributes, 'compare_values');
	
	if ($listdeleteAttributes!=null) $merge= array_udiff($merge, $listdeleteAttributes, 'compare_values');
	
	
	$s1 = true; 
	$notDuplicate = true;
	
		if($old_contextbroker != $contextbroker){
        
        $selectDevicesDeleted = "SELECT contextBroker, id
		FROM deleted_devices WHERE contextBroker = '$contextbroker'
		AND id = '$id';";
		
		$s1 = mysqli_query($link, $selectDevicesDeleted);
		$notDuplicate = (mysqli_num_rows($s1) == 0);
		$result["msg"] .= "mysql s1 ".mysqli_num_rows($s1);
	}
    else
	{
		$result["msg"] .= " cb ".$contextbroker . " old cb ".$old_contextbroker ; 
	}
	
	if($notDuplicate){
	updateKB($link, $id, $devicetype, $contextbroker, $kind, $protocol, $format, $macaddress, $model, $producer, $latitude, $longitude,$visibility, $frequency, $merge, $uri, $dev_organization, $result); 
	
    if ($result["status"]=='ko'){
		//Sara2510 - for logging purpose
		logAction($link,$username,'device','update',$deviceName,$organization,'','faliure');		
		return $result;
	}	
	 
	if ($result["status"]=='ok' &&  $result["content"]==null)
	{
		$q = "UPDATE devices SET contextBroker='$contextbroker', devicetype='$devicetype', kind= '$kind', protocol='$protocol', format='$format', macaddress='$macaddress', model='$model', producer='$producer', latitude='$latitude', longitude='$longitude', frequency = '$frequency', organization='$dev_organization' WHERE id='$id' and contextBroker='$old_contextbroker'";
	}
	else {			
		$q = "UPDATE  devices SET uri = '". $result["content"] . "', mandatoryproperties=1, mandatoryvalues=1, contextBroker='$contextbroker', devicetype='$devicetype', kind= '$kind', protocol='$protocol', format='$format', macaddress='$macaddress', model='$model', producer='$producer', latitude='$latitude', longitude='$longitude', frequency = '$frequency',organization='$dev_organization'  WHERE id='$id' and contextBroker='$old_contextbroker'";
	}
	
	$r = mysqli_query($link, $q);
			
    if($r) 
    {
		$result["msg"] .= "\n Device $contextbroker/$id correctly updated";
		$result["log"] .= "\r\n Device $contextbroker/$id correctly updated";
		//Sara2510 - For logging purpose
		if($result["status"]=="ok"){
				logAction($link,$username,'device','update',$deviceName,$organization,'','success');		
		}
		// information to be passed to the interface
        $result["visibility"] = $visibility;
		if($result["content"]==null) $result["active"]=false;  else $result["active"]=true;
		// end of information to be passed to the interface
		
        // update to the registration of the device
			if ($accessToken != "")
			{
				$ownmsg = array();
                $eId=$dev_organization.":".$contextbroker.":".$id;
				$ownmsg["elementId"]= $eId;
				$ownmsg["elementName"]=$id;				    
				$ownmsg["elementUrl"]=$result["content"];
				$ownmsg["elementDetails"]=array();
				$ownmsg["elementDetails"]["k1"]= $k1;
				$ownmsg["elementDetails"]["k2"]= $k2;
				if ($edgegateway_type!="") $ownmsg["elementDetails"]["edgegateway_type"]= $edgegateway_type;
				if ($edgegateway_uri!="") $ownmsg["elementDetails"]["edgegateway_uri"]= $edgegateway_uri;					
				$ownmsg["elementDetails"]["contextbroker"]=$contextbroker;
                registerOwnerShipDevice($eId, $ownmsg, $accessToken, $result);
			}
		
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
		  $upquery="UPDATE event_values SET cb='$contextbroker', device = '$id',value_name='$att->value_name',data_type='$att->data_type', event_values.order='$b',value_type='$att->value_type', editable='$att->editable', value_unit='$att->value_unit', healthiness_criteria='$att->healthiness_criteria', value_refresh_rate='$att->healthiness_value', old_value_name= '$att->value_name' WHERE  cb='$old_contextbroker' AND device='$id' AND value_name='$att->old_value_name';";
		  $r1 = mysqli_query($link, $upquery);
		//   echo $upquery;
		  if ($r1) 
		  {
			  $result["msg"] .= "\n attribute $att->value_name with old name $att->old_value_name correctly updated";
			  $result["log"] .= "\n attribute $att->value_name correctly updated";
	      }
		  else 
		  {
			  $result["error_msg"] .= "Attribute $att->value_name was not updated. "; 
			  $result["msg"] .= "<br/> attribute $att->value_name was not updated " .  generateErrorMessage($link); 
			  $result["log"] .= "\r\n attribute $att->value_name was not updated " . $upquery . " " .  generateErrorMessage($link); 
			  $ok=false;
		  } 
            $a++;
		}
	//echo "valore di ok". $ok;    
		if ($ok==true)
		{
			$result["msg"] .= "\n old attributes correctly updated"; 
			$result["log"] .= "\n old attributes correctly updated" . $q; 
			
			$q="";
			$a=0;
			while ($a < count($listnewAttributes) && $ok)
			{
				$att=$listnewAttributes[$a];
				if ($att->healthiness_criteria=="refresh_rate")  $hc="value_refresh_rate";
		        else if ($att->healthiness_criteria=="different_values") $hc="different_values";
		        else $hc="value_bounds";
		   
				$insertquery="INSERT INTO `event_values`(`cb`, `device`, `old_value_name`, `value_name`, `data_type`, `order`, `value_type`, `editable`, `value_unit`, `healthiness_criteria`, `$hc`) 
				VALUES ('$contextbroker','$id','$att->value_name', '$att->value_name','$att->data_type','$b','$att->value_type','$att->editable','$att->value_unit','$att->healthiness_criteria','$att->healthiness_value');";
				$r1 = mysqli_query($link, $insertquery);
				if ($r1) 
				{
					$result["msg"] .= "\n attribute $att->value_name correctly inserted";
					$result["log"] .= "\n attribute $att->value_name correctly inserted";
				}
				else 
				{
					$result["error_msg"] .= "Attribute $att->value_name was not inserted. "; 
					$result["msg"] .= "\n attribute $att->value_name was not inserted " . generateErrorMessage($link); 
					$result["log"] .= "\n attribute $att->value_name was not inserted " . $insertquery . " " . generateErrorMessage($link); 
				    $ok=false;
				} 
				$b++;
				$a++;
			}
		 }
		  if ($ok==true)
		{
			$result["msg"] .= "\n new attributes correctly inserted"; 
			$result["log"] .= "\n new attributes correctly inserted" . $q; 
			$q="";
			$a=0;
			while ($a < count($listdeleteAttributes) && $ok)
			{
				$att=$listdeleteAttributes[$a];
				$a++;
				$deletequery="DELETE FROM `event_values` WHERE `cb`='$contextbroker' AND `device`='$id' AND value_name='". $att->value_name . "';";
				$r1 = mysqli_query($link, $deletequery);
				if ($r1) 
				{
					$result["msg"] .= "\n attribute $att->value_name correctly deleted";
					$result["log"] .= "\n attribute $att->value_name correctly deleted";
				}
				else 
				{
					$result["error_msg"] .= "Attribute $att->value_name was not deleted. "; 
					$result["msg"] .= "\n attribute $att->value_name was not deleted " . generateErrorMessage($link); 
					$result["log"] .= "\n attribute $att->value_name was not deleted " . $deletequery . " " . generateErrorMessage($link); 
					$ok=false;
				} 
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
		 my_log($result);
		 mysqli_close($link); 
	}
	
	}  //not s1
	else
	{
	  //Sara2510 - For logging purpose
	  logAction($link,$username,'device','update',$deviceName,$organization,'','faliure');		
	  $result["status"]='ko';
	  $result["error_msg"] .= "Problem in updating the device $id. "; 
	  $result["msg"] .= "\n Problem in updating the device $id:" . generateErrorMessage($link); 
	  $result["log"] .= "\n Problem in updating the device $id:" . $q . " " . generateErrorMessage($link); 
	  my_log($result);
	  mysqli_close($link); 
	}
}  
else if ($action=="delete")
{
     $id = $_REQUEST['id'];
	 $cb = $_REQUEST['contextbroker'];
	 $url = $_REQUEST['uri'];
	 
	 $username = mysqli_real_escape_string($link, $_REQUEST['username']);
	 $organization = mysqli_real_escape_string($link, $_REQUEST['organization']);
	 $dev_organization = mysqli_real_escape_string($link, $_REQUEST['dev_organization']);
	 $deviceName = $id . " ".$cb;
	 
    if($accessToken !=""){ 
   
	//retrieve the kburl -> this is needed since this api can be called from OUTSIDE of the IoT directory
       $kburl="";
       if (!isset($_SESSION['kbUrl'])){
               $infokburl=get_organization_info($organizationApiURI, $organization);
               if(!is_null($infokburl)){
                       $kburl=$infokburl["kbUrl"];
               }
       }
       else {
               $kburl=$_SESSION['kbUrl'];
       }

 
     deleteKB($link, $id, $cb, $kburl,$result);	   

	 //FF: was removed and then I turn it back 
     if ($result["status"]=='ko') return $result;
			
	 /*$query = "START TRANSACTION;
     UPDATE devices SET deleted = '". date("Y/m/d") . "' WHERE id = '$id' and contextBroker='$cb';
     INSERT INTO deleted_devices select * from devices WHERE id = '$id' and contextBroker='$cb'and deleted IS NOT NULL;
     INSERT INTO deleted_event_values (select cb,device,value_name,data_type,value_type,editable,value_unit,healthiness_criteria,value_refresh_rate, different_values,value_bounds, event_values.order from event_values where  device = '$id' and cb='$cb' );
     DELETE FROM devices WHERE id = '$id' and contextBroker='$cb' and deleted IS NOT NULL;
     COMMIT;";*/
    //FF:Just For Now
    $q1 = "UPDATE devices SET deleted = '". date("Y/m/d") . "' WHERE id = '$id' and contextBroker='$cb';";
     $q2="INSERT INTO deleted_devices select * from devices WHERE id = '$id' and contextBroker='$cb'and deleted IS NOT NULL;";
     $q3="INSERT INTO deleted_event_values (select cb,device,value_name,data_type,value_type,editable,value_unit,healthiness_criteria,value_refresh_rate, different_values,value_bounds, event_values.order from event_values where  device = '$id' and cb='$cb' );";
     $q4="DELETE FROM devices WHERE id = '$id' and contextBroker='$cb' and deleted IS NOT NULL;
     ";
     
    $r1 = mysqli_query($link, $q1);
    if ($r1){$r2 = mysqli_query($link, $q2);
        if($r2){$r3 = mysqli_query($link, $q3);
            if($r3){$r4 = mysqli_query($link, $q4);
                   }
               }
            
            }
     if($r4)
	 {
		 //Sara2510 - for logging purpose
		logAction($link,$username,'device','delete',$deviceName,$organization,'','success');	
		
		$result["status"]='ok';
		 if ($accessToken != "")
		{
			$eId=$dev_organization.":".$cb.":".$id;
            removeOwnerShipDevice($eId,$accessToken,$result);
		}
		 
	 }
	 else
	 {
	  //Sara2510 - for logging purpose
	  logAction($link,$username,'device','delete',$deviceName,$organization,'','faliure');	
	  $result["status"]='ko';
	  $result["msg"] .= "\n Problem in deleting the device $id: " . generateErrorMessage($link); 
	  $result["log"] .= "\n Problem in deleting the device $id: " . $query . " " . generateErrorMessage($link); 
	 }
     mysqli_close($link);
    }
    else{
        $result["status"]='ko';
	    $result["msg"] .= "\n Problem in the access Token "; 
	    $result["error_msg"] .= "\n Problem in the access Token "; 
	    $result["log"] .= "\n Problem in the access Token "; 
    }
    my_log($result);
        
} // end delete
else if ($action =='change_visibility')
{
	$id = mysqli_real_escape_string($link, $_REQUEST['id']);
	$contextbroker = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
	$visibility = mysqli_real_escape_string($link, $_REQUEST['visibility']);
	$uri = mysqli_real_escape_string($link, $_REQUEST['uri']);
	$username = mysqli_real_escape_string($link, $_REQUEST['username']);
	$organization = mysqli_real_escape_string($link, $_REQUEST['organization']);
	$dev_organization = mysqli_real_escape_string($link, $_REQUEST['dev_organization']);
	$deviceName = $id . " ".$contextbroker;
	
	
	$q = "UPDATE devices SET  visibility = '$visibility' WHERE id='$id' and contextBroker='$contextbroker'";
	$r = mysqli_query($link, $q);
			
	if($r) 
	{
		//Sara2510 - for logging purpose
		logAction($link,$username,'device','change_visibility',$deviceName,$organization,'new visibility '.$visibility,'success');			
		
		$result["status"]='ok'; 
		$result["msg"] .= "\n Device Visibility correctly updated"; 
		$result["log"] .= "\n Device $id: Visibility correctly updated"; 
		
		// information to be passed to the interface
		$result["visibility"] = $visibility;
		modify_valueKB($link, $id, $contextbroker, $organization, $result);
		//update delegation informations

		//retrieve any values of this this device
                
        $q1 = "SELECT * FROM event_values WHERE cb = '$contextbroker' and device='$id'";
        $values = mysqli_query($link, $q1);

        $eid=$dev_organization.":".$contextbroker.":".$id; 
		if ($visibility==='public'){
            delegateDeviceValue($eid, $contextbroker, NULL, $username, "ANONYMOUS", "", $accessToken, "", "", $result);           
            //make public any values of this device
                       
            while($value = mysqli_fetch_assoc($values))
            
            {
                delegateDeviceValue($uri ."/" . $value["value_name"], $contextbroker, $value["value_name"], $username, "ANONYMOUS", "", $accessToken, "", "", $result);           
            }
		}
		else {
			getDelegatorDevice($accessToken, $username, $result, $eid);
			$delegated=$result["delegation"];
		        $found=false;
		        $i=0;
		        while (!$found && $i < count($delegated))
		        {
		                if (isset($delegated[$i]["userDelegated"]) && $delegated[$i]["userDelegated"]=='ANONYMOUS')
		                {
                	        	$found=true;
	                        	$delegationId= $delegated[$i]["delegationId"];
        	        	}
                		$i++;
		        }
		        if ($found)
		        {

	        	        $result["status"]="ok";
        	        	$result["msg"]="The delegation to anonymous has been changed";
		                $result["log"]="The delegation to anonymous has been changed";
                		removeDelegationValue($accessToken, $username, $delegationId, $result);
		        }
                        //retrieve any public values
                        getDelegatedDevice($accessToken, "ANONYMOUS", $result);
                      $publicElement=$result["delegation"];
                       //remove public from any values of this device (if was public)
                        while($value = mysqli_fetch_assoc($values))
                        {
                                foreach ($publicElement as $public =>$public_value)
                                {
                                        if ($uri ."/" . $value["value_name"] === $public){
                                                removeDelegationValue($accessToken, $username, $public_value['delegationId'], $result);
                                        }
                                }
                        }

		}

	}
	else 
	{
	  //Sara2610 - for logging purpose
	  logAction($link,$username,'device','change_visibility',$deviceName,$organization,'new visibility '.$visibility,'faliure');			
		
	  $result["status"]='ko';
	  $result["msg"] .= "\n Problem in changing the visibility of the device $id: " . generateErrorMessage($link); 
	  $result["log"] .= "\n Problem in changing the visibility of the device $id: " . generateErrorMessage($link); 

	}
	my_log($result);
	mysqli_close($link);
}
else if ($action =='change_owner')
{
//TODO: in this routine the error returned from the ownership is not managed and UI in not updated with error information!!!
	$id = mysqli_real_escape_string($link, $_REQUEST['id']);
	$cb = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
	$newuser = mysqli_real_escape_string($link, $_REQUEST['newOwner']);
	$currentowner = mysqli_real_escape_string($link, $_REQUEST['owner']);      
	$k1 = mysqli_real_escape_string($link, $_REQUEST['k1']);
	$k2 = mysqli_real_escape_string($link, $_REQUEST['k2']);
	$model = mysqli_real_escape_string($link, $_REQUEST['model']);
	$url =  mysqli_real_escape_string($link, $_REQUEST['uri']);
	$organization =  mysqli_real_escape_string($link, $_REQUEST['organization']);
	$dev_organization =  mysqli_real_escape_string($link, $_REQUEST['dev_organization']);
	if (isset( $_REQUEST['edgegateway_type']))
		$edgegateway_type = $_REQUEST['edgegateway_type'];
	else $edgegateway_type="";
	if (isset( $_REQUEST['edgegateway_uri']))
		$edgegateway_uri = $_REQUEST['edgegateway_uri'];
	else $edgegateway_uri="";

	//Sara2510 - For logging purpose
	$deviceName = $id . " ".$cb;
	
	//for change ownership, a new certificate has to be created (if model is authenticated)
	$errorThrown=false;
        if (registerCertificatePrivateKey($link, $cb, $id, $model, $pathCertificate, $result))
        {
        	$privatekey = $id . "-key.pem";
            $certificate = $id . "-crt.pem";
            $publickey = $id . "-pubkey.pem";

		//update also local db information
		$q = "UPDATE devices SET privatekey='".$privatekey."', certificate='".$certificate."' where id='".$id."';";
		$r = mysqli_query($link, $q);
			if($r)
			{
				//Sara2510 - For logging purpose
				logAction($link,$currentowner,'device','change_owner',$deviceName,$organization,'new owner: '.$newuser,'success');			

				$result["msg"] .= "\n Device $cb/$id correctly updated";
				$result["log"] .= "\r\n Device $cb/$id correctly updated\r\n";
			}
			else {
				//Sara2510 - For logging purpose
				logAction($link,$currentowner,'device','change_owner',$deviceName,$organization,'new owner: '.$newuser,'faliure');
				
				$result["msg"] .= "\n Device $contextbroker/$id NOT correctly updated";
				$result["log"] .= "\r\n Device $contextbroker/$id NOT correctly updated\r\n";
				$errorThrown=true;
			}
        }
        else
        {
			$privatekey = "";
			$certificate = "";
            $publickey = "";
        }	
	
	if (($accessToken != "")&&(!$errorThrown))
	{
		$ownmsg = array();
        $eId=$dev_organization.":".$cb.":".$id;
		$ownmsg["elementId"]=$eId;
		$ownmsg["elementName"]=$id;				    
		$ownmsg["elementUrl"]=$url;
		$ownmsg["deleted"]= date("Y/m/d");
	    $ownmsg["username"]=$currentowner;
		$ownmsg["elementDetails"]=array();
		$ownmsg["elementDetails"]["k1"]= $k1;
		$ownmsg["elementDetails"]["k2"]= $k2;
		if ($edgegateway_type!="") $ownmsg["elementDetails"]["edgegateway_type"]= $edgegateway_type;
		if ($edgegateway_uri!="") $ownmsg["elementDetails"]["edgegateway_uri"]= $edgegateway_uri;								
		$ownmsg["elementDetails"]["contextbroker"]=$cb;
		registerOwnerShipDevice($eId, $ownmsg, $accessToken, $result);//delete old ownership
		unset($ownmsg["deleted"]);
		$ownmsg["username"]=$newuser;
		if ($publickey!="") {
                	$pub_key_str=str_replace("\n", "", file_get_contents($pathCertificate."/".$publickey));
                        $ownmsg["elementDetails"]["publickey"]= substr($pub_key_str, 26, 736);
                }
		registerOwnerShipDevice($eId, $ownmsg, $accessToken, $result);//insert new ownership
		$result["status"]='ok';
		
		//Sara2510 - For logging purpose
		logAction($link,$currentowner,'device','change_owner',$deviceName,$organization,'new owner: '.$newuser,'success');	
	}
	else {
		//Sara2510 - For logging purpose
		logAction($link,$currentowner,'device','change_owner',$deviceName,$organization,'new owner: '.$newuser,'faliure');
		$result["status"]='ko';
	}
 	my_log($result);
    	mysqli_close($link);
}

else if($action == 'get_device_attributes')
{

	$id = mysqli_real_escape_string($link, $_REQUEST['id']);
	$cb = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
    
    if($accessToken !=""){ 

	//$result = array();
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
		$result['log'] .= "\n\r action:get_device_attributes. access to " . $q1;
		
     }
	 else
	 {
	    $result['status'] = 'ko'; // . $q1 . generateErrorMessage($link);
		$result['msg'] = 'Error: errors in reading data about devices. <br/>' .
						  generateErrorMessage($link);
		$result['log'] .= '\n\naction:get_device_attributes. Error: errors in reading data about devices. ' .
						  generateErrorMessage($link);				  
	}
	my_log($result);
	mysqli_close($link);  
        
    }
    else{
        $result["status"]='ko';
	    $result["msg"] .= "\n Problem in the access Token "; 
	    $result["error_msg"] .= "\n Problem in the access Token "; 
	    $result["log"] .= "\n Problem in the access Token "; 
    }
    
}	
else if($action == 'get_param_values')
{	
	// $result = array();
	$result['status'] = 'ok'; 
	$result['value_type'] = generatelabels($link);
	$result['data_type'] = generatedatatypes($link);
	$result['value_unit'] = generateunits($link);
	$result['log'] .= '\n\naction:get_param_values';
	my_log($result);
	mysqli_close($link); 
}
else if ($action == 'get_device'){
/*Sara611 not logged, this function is used in value.php to determine the visibility of a device before changing 
it, only the second part will be logged*/

    // Elf this function is not correct
    //  A device is identified by the pair (id, contextbroker)
    // the values of k1 and k2 should be determined using the accesstoken
	// consider the code of get_all_device and specify in the query the id and context broker
	// the behavior of this action is different from the other ones that have been developed 
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
			   cb.`accessport`,cb.`sha` 
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
				// $rec["visibility"]= $row["visibility"];
				$rec["frequency"]= $row["frequency"];
				$rec["created"]= $row["created"];
		        $rec["accesslink"]= $row["accesslink"];
			    $rec["accessport"]= $row["accessport"];
				$rec["sha"]= $row["sha"];
				$rec["privatekey"]= "";
				$rec["certificate"]= "";
				$rec["edgegateway_type"]= "";
				$rec["edgegateway_uri"]= "";
				
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
        mysqli_close($link);	
}		
else if($action == "get_all_device")
{
	$username = mysqli_real_escape_string($link, $_REQUEST['username']);
	$loggedrole= mysqli_real_escape_string($link, $_REQUEST['loggedrole']);
	$organization = mysqli_real_escape_string($link, $_REQUEST['organization']);

	$ownDevices= "";
   
    if (!empty($accessToken)) 
	{ 
        $ownDevices = getOwnerShipDevice($accessToken, $result); 
        getDelegatedDevice($accessToken, $username, $result);
        
	}
	
    $q = "SELECT d.`contextBroker`, d.`id`, d.`uri`, d.`devicetype`, d.`kind`, 
	      CASE WHEN mandatoryproperties AND mandatoryvalues THEN \"active\" ELSE \"idle\" END AS status1, 
	     d.`macaddress`, d.`model`, d.`producer`, d.`longitude`, d.`latitude`, d.`protocol`, d.`format`, d.`visibility`, 
	     d.`frequency`, d.`created`, d.`privatekey`, d.`certificate`,d.`organization`, cb.`accesslink`, cb.`accessport`, cb.`sha` FROM `devices` d JOIN `contextbroker` cb ON (d.contextBroker=cb.name)"; 
     
	$r=create_datatable_data($link,$_REQUEST,$q, "deleted IS null");
    

	$selectedrows=-1;
	if($_REQUEST["length"] != -1)
	{
			$start= $_REQUEST['start'];
			$offset=$_REQUEST['length'];
			$tobelimited=true;
	}
	else
	{
		$tobelimited=false;
	}
	

    $data = array();

	if ($r)
	{
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
                  
		        $selectedrows++;
		        if (!$tobelimited || ($tobelimited && $selectedrows >= $start && $selectedrows < ($start+$offset)))
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
				$rec["frequency"]= $row["frequency"];
				$rec["created"]= $row["created"];
				$rec["organization"]= $row["organization"];
		
		        $rec["accesslink"]= $row["accesslink"];
			$rec["accessport"]= $row["accessport"];
				$rec["sha"]= $row["sha"];
				$rec["privatekey"]= "";
				$rec["certificate"]= "";
				$rec["edgegateway_type"]= "";
				$rec["edgegateway_uri"]= "";
			
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
				array_push($data, $rec);           
			 }
			 }	
     }
	 $output= format_result($_REQUEST["draw"], $selectedrows+1, $selectedrows+1, $data, "", "\r\n action=get_all_device \r\n", 'ok');
	//Sara611 - For logging purpose
	logAction($link,$username,'device','get_all_device','',$organization,'','success');
	
	}
	else
{
	//THIS IS NOT AN ERROR... it's a empty list returned,,,, no device belong here
			//Sara611 - For logging purpose
		logAction($link,$username,'device','get_all_device','',$organization,'Error: errors in reading data about devices.','faliure');
	   // $result['status'] = 'ko';
	   // $result['msg'] = 'Error: errors in reading data about devices. <br/>' .
	   //					   generateErrorMessage($link);
	   // $result['log'] = '\n\r Error: errors in reading data about devices.' . generateErrorMessage($link);	
	$output= format_result($_REQUEST["draw"], 0, 0, $data, 'Error: errors in reading data about devices. <br/>' . generateErrorMessage($link), '\n\r Error: errors in reading data about devices.' . generateErrorMessage($link), 'ko');
	  
	}    
	my_log($output);
	mysqli_close($link);
}

else if($action == "get_all_device_admin")
{
	$ownDevices= "";
	$username = mysqli_real_escape_string($link, $_REQUEST['username']);
	$organization = mysqli_real_escape_string($link, $_REQUEST['organization']);
   
    if (!empty($accessToken)) 
	{ 
	 $ownDevices = getOwnerShipDevice($accessToken, $result); 
	}
	
	    $q = "SELECT d.`contextBroker`, d.`id`, d.`uri`, d.`devicetype`, d.`kind`, 
	      CASE WHEN mandatoryproperties AND mandatoryvalues THEN \"active\" ELSE \"idle\" END AS status1, 
	     d.`macaddress`, d.`model`, d.`producer`, d.`longitude`, d.`latitude`, d.`protocol`, d.`format`, d.`visibility`, d.`organization`,
	     d.`frequency`, d.`created`, d.`privatekey`, d.`certificate`, cb.`accesslink`,  cb.`accessport`,cb.`sha` FROM `devices` d JOIN `contextbroker` cb ON (d.contextBroker=cb.name) "; //  WHERE visibility =\"public\"";

        
	
    $r=create_datatable_data($link,$_REQUEST,$q, "deleted IS null");
	
	$selectedrows=-1;
	if($_REQUEST["length"] != -1)
	{
			$start= $_REQUEST['start'];
			$offset=$_REQUEST['length'];
			$tobelimited=true;
	}
	else
	{
		$tobelimited=false;
	}
	
	if ($r)
	{
	   $data = array();
	  logAction($link,$username,'device','get_all_device_admin','',$organization,'','success');	 
	
          while($row = mysqli_fetch_assoc($r)) 
          {
			  $selectedrows++;
		        if (!$tobelimited || ($tobelimited && $selectedrows >= $start && $selectedrows < ($start+$offset)))
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
			$rec["organization"]= $row["organization"];
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

			$eid=$row["organization"].":".$row["contextBroker"].":".$row["id"];
			
            if (isset($result["keys"][$eid]) && $result["keys"][$eid]["owner"]==$username)
			{
				$rec["visibility"]= ($row["visibility"]=="public")?"MyOwnPublic":"MyOwnPrivate";
				$rec["k1"]=$result["keys"][$eid]["k1"];
				$rec["k2"]=$result["keys"][$eid]["k2"];
				$rec["edgegateway_type"]= $result["keys"][$eid]["edgegateway_type"];
				$rec["edgegateway_uri"]= $result["keys"][$eid]["edgegateway_uri"];
				$rec["owner"]=$result["keys"][$eid]["owner"];
				// the following two information should be shown only to the device owner				
                $rec["privatekey"]= $row["privatekey"];
			    $rec["certificate"]= $row["certificate"];
            }
			 else if (isset($result["keys"][$eid]) && $result["keys"][$eid]["owner"]!=$username)
                     
			{

			   $rec["visibility"]= $row["visibility"]; // =="public")?"MyOwnPublic":"MyOwnPrivate";
			   $rec["k1"]=$result["keys"][$eid]["k1"];
			   $rec["k2"]=$result["keys"][$eid]["k2"];
			   $rec["edgegateway_type"]= $result["keys"][$eid]["edgegateway_type"];
			   $rec["edgegateway_uri"]= $result["keys"][$eid]["edgegateway_uri"];
			   $rec["owner"]=$result["keys"][$eid]["owner"];
			   // the following two information should be added, if we wish to give the chaance
			   // to the administrator to see the privatekey and certificate of one of its user_error 
			   // $rec["privatekey"]= $row["privatekey"];
		       // $rec["certificate"]= $row["certificate"];
            }
			 else 
			{
			   $rec["visibility"]= $row["visibility"]; // =="public")?"MyOwnPublic":"MyOwnPrivate";
			   $rec["k1"]="";
			   $rec["k2"]="";
			   $rec["owner"]="UnKnown";
			} 
            array_push($data, $rec);           
		}
	 }
	 // MM0310 100 is a tempory value. To be fixed
	 $output= format_result($_REQUEST["draw"], $selectedrows+1, $selectedrows+1, $data, "", "\r\n action=get_all_device_admin \r\n", 'ok');
    	
    }
	else{
	 //Sara611 - for logging purpose
	  logAction($link,$username,'device','get_all_device_admin','',$organization,'','faliure');	 
	   /* $result['status'] = 'ko';
	   $result['msg'] = 'Error: errors in reading data about devices. <br/>' .
						   generateErrorMessage($link);
	   $result['log'] = '\r\n Error: errors in reading data about devices.' .
						   generateErrorMessage($link);
      */  $output= format_result($_REQUEST["draw"], 0, 0, null, 'Error: errors in reading data about devices. <br/>' . generateErrorMessage($link), '\n\r Error: errors in reading data about devices.' . generateErrorMessage($link), 'ko');
	}    
	my_log($output);
	mysqli_close($link);
}
else if($action == "get_all_private_device")
{
/*Sara611 not used so is not logged*/
	$ownDevices= "";
    if (!empty($accessToken)) 
	{ 
	 $ownDevices = getOwnerShipDevice($accessToken, $result); 
	}
	
	
    if ($ownDevices!="")
	{                
         $q = "SELECT d.`contextBroker`, d.`id`, d.`uri`, d.`devicetype`, d.`kind`, 
	      CASE WHEN mandatoryproperties AND mandatoryvalues THEN \"active\" ELSE \"idle\" END AS status1, 
	     d.`macaddress`, d.`model`, d.`producer`, d.`longitude`, d.`latitude`, d.`protocol`, d.`format`, d.`visibility`, 
	     d.`frequency`, d.`created`, d.`privatekey`, d.`certificate`, cb.`accesslink`, cb.`accessport`,cb.`sha`  
		 FROM `devices` d JOIN `contextbroker` cb ON (d.contextBroker=cb.name)"; //  WHERE visibility =\"public\"";
    
	
	$r=create_datatable_data($link,$_REQUEST,$q, "deleted IS null and visibility=\"private\" and $ownDevices ");
	
	$selectedrows=-1;
	if($_REQUEST["length"] != -1)
	{
			$start= $_REQUEST['start'];
			$offset=$_REQUEST['length'];
			$tobelimited=true;
	}
	else
	{
		$tobelimited=false;
	}

	if ($r)
	{
	   $data = array();
	
         while($row = mysqli_fetch_assoc($r))
           {
			  $selectedrows++;
				if (!$tobelimited || ($tobelimited && $selectedrows >= $start && $selectedrows < ($start+$offset)))
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
			$rec["visibility"]= "MyOwnPrivate";
			$rec["frequency"]= $row["frequency"];
			$rec["created"]= $row["created"];
			$rec["k1"]=$result["keys"][$rec["id"]]["k1"];
			$rec["k2"]=$result["keys"][$rec["id"]]["k2"];
			$rec["accesslink"]= $row["accesslink"];
			$rec["accessport"]= $row["accessport"];
			$rec["sha"]= $row["sha"];
   			$rec["privatekey"]= $row["privatekey"];
			$rec["certificate"]= $row["certificate"];
            $rec["edgegateway_type"]= $result["keys"][$rec["id"]]["edgegateway_type"];
			$rec["edgegateway_uri"]= $result["keys"][$rec["id"]]["edgegateway_uri"];
			array_push($data, $rec);           
           }
		   
		   $output= format_result($_REQUEST["draw"], $selectedrows+1, $selectedrows+1, $data, "", "\r\n action=get_all_private_device \r\n", 'ok');
			}
        }
        else{
          
		    $output= format_result($_REQUEST["draw"], 0, 0, null, 'Error: errors in reading data about devices. <br/>' . generateErrorMessage($link), '\n\r Error: errors in reading data about devices.' . generateErrorMessage($link), 'ko');
        }
     }
        my_log($output);
        mysqli_close($link);
}
else if($action == "get_subset_device")
{
	$username = mysqli_real_escape_string($link, $_REQUEST['username']);
	$organization = mysqli_real_escape_string($link, $_REQUEST['organization']);
    $loggedrole= mysqli_real_escape_string($link, $_REQUEST['loggedrole']);
	
    
    $ownDevices= "";
    if (!empty($accessToken)) 
	{ 
        $ownDevices = getOwnerShipDevice($accessToken, $result); 
        getDelegatedDevice($accessToken, $username, $result);
	}
	
    $selection= json_decode($_REQUEST['select']);
	$a=0;
	$cond="";
    $q = "SELECT d.`contextBroker`, d.`id`, d.`uri`, d.`devicetype`, d.`kind`, 
	      CASE WHEN mandatoryproperties AND mandatoryvalues THEN \"active\" ELSE \"idle\" END AS status1, 
	     d.`macaddress`, d.`model`, d.`producer`, d.`longitude`, d.`latitude`, d.`protocol`, d.`format`, d.`visibility`, 
	     d.`frequency`, d.`organization`, d.`created`, d.`privatekey`, d.`certificate`, cb.`accesslink`, cb.`accessport`,cb.`sha` 
		 FROM `devices` d JOIN `contextbroker` cb ON (d.contextBroker=cb.name) ";
	if (count($selection)!=0)
	{
	    
		while ($a < count($selection))
		{
			 $sel = $selection[$a];
			 $cond .= " (id = '" . $sel->id . "' AND contextBroker = '" . $sel->contextBroker . "') ";
			 if ($a != count($selection)-1)  $cond .= " OR ";
			 $a++;
		 }
		
		 $r=create_datatable_data($link,$_REQUEST,$q, "deleted IS null AND (" . $cond . ") "); 
	}
    else
	{
	    //$q = "SELECT DISTINCT * FROM devices";//  WHERE organization='".$organization."'";
	    $r=create_datatable_data($link,$_REQUEST,$q,"1=2");
		
	}
    
    $selectedrows=-1;
    if($_REQUEST["length"] != -1)
    {
        $start= $_REQUEST['start'];	
        $offset=$_REQUEST['length'];
        $tobelimited=true;
    }
    else
    {
        $tobelimited=false;	
    }
	
	if($r)
	{
        $data = array();
	    logAction($link,$username,'device','get_subset_device','',$organization,'','success');	
     
        while($row = mysqli_fetch_assoc($r)) 
        {     
   
                $selectedrows++;
                if (!$tobelimited || ($tobelimited && $selectedrows >= $start && $selectedrows < ($start+$offset)))
				{
                    $eid=$row["organization"].":".$row["contextBroker"].":".$row["id"];
                    
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
                    // $rec["visibility"]= $row["visibility"];
                    $rec["frequency"]= $row["frequency"];
                    $rec["created"]= $row["created"];
                    $rec["organization"]= $row["organization"];
                    $rec["accesslink"]= $row["accesslink"];
                    $rec["accessport"]= $row["accessport"];
                    $rec["sha"]= $row["sha"];
                    $rec["privatekey"]= "";
                    $rec["certificate"]= "";
                    $rec["edgegateway_type"]= "";
                    $rec["edgegateway_uri"]= "";
			
                    if (((isset($result["keys"][$eid]))&&($loggedrole!=='RootAdmin'))
                                       ||            
                        ((isset($result["keys"][$eid]))&& ($result["keys"][$eid]["owner"]==$username) && ($loggedrole==='RootAdmin')))
                               	
                    {//it's mine               
                        if ($row["visibility"]=="public")
                        {
                            $rec["visibility"]= "MyOwnPublic";   
                        }
                        else
                        {
                            if (isset($result["delegation"][$eid])&& $result["delegation"][$eid]["kind"]=="anonymous")
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
                    
                        if (isset($result["delegation"][$eid])&& ($result["delegation"][$eid]["kind"]=="anonymous"))
                        {
                            //it's delegated as public
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
				array_push($data, $rec);
                    
			}
		
	 }
	 //MM0310 100 is a temporary value
        $output= format_result($_REQUEST["draw"], $selectedrows+1, $selectedrows+1, $data, "", "\r\n action=get_subset_device \r\n", 'ok');
        
    
	}
    else{
	  
	
        $output= format_result($_REQUEST["draw"], 0, 0, null, 'Error: errors in reading data about devices. <br/>' . generateErrorMessage($link), '\n\r Error: errors in reading data about devices.' . generateErrorMessage($link), 'ko');
		logAction($link,$username,'device','get_subset_device','',$organization,'','faliure');
    }    
	my_log($output);
	mysqli_close($link);
}
else if($action == "get_subset_device_admin")
{
    $ownDevices= "";
	$username = mysqli_real_escape_string($link, $_REQUEST['username']);
	$organization = mysqli_real_escape_string($link, $_REQUEST['organization']);
 
    if (!empty($accessToken)) 
	{ 
	 $ownDevices = getOwnerShipDevice($accessToken, $result); 
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
		
		$q = "SELECT d.`contextBroker`, d.`id`, d.`uri`, d.`devicetype`, d.`kind`, 
	      CASE WHEN mandatoryproperties AND mandatoryvalues THEN \"active\" ELSE \"idle\" END AS status1, 
	     d.`macaddress`, d.`model`, d.`producer`, d.`longitude`, d.`latitude`, d.`protocol`, d.`format`, d.`visibility`, d.`organization`,
	     d.`frequency`, d.`created`, d.`privatekey`, d.`certificate`, cb.`accesslink`, cb.`accessport`, cb.`sha` 
		 FROM `devices` d JOIN `contextbroker` cb ON (d.contextBroker=cb.name)";
		
	 $r=create_datatable_data($link,$_REQUEST,$q, "deleted IS null AND (" . $cond . ")");
	 
	 $selectedrows=-1;
		if($_REQUEST["length"] != -1)
		{
				$start= $_REQUEST['start'];
				$offset=$_REQUEST['length'];
				$tobelimited=true;
		}
		else
		{
			$tobelimited=false;
		}
	}
    else
	{
	    $q = "SELECT DISTINCT * FROM devices";
	    $r=create_datatable_data($link,$_REQUEST,$q,"1=2");
	}
	
	
	if($r) 
	{
	
	  logAction($link,$username,'device','get_subset_device_admin','',$organization,'','success');	
     $data = array(); 
     while($row = mysqli_fetch_assoc($r)) 
     {
		  $selectedrows++;
			if (!$tobelimited || ($tobelimited && $selectedrows >= $start && $selectedrows < ($start+$offset)))
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
			$rec["organization"]= $row["organization"];
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
			
			$eid=$row["organization"].":".$row["contextBroker"].":".$row["id"];
                
			if (isset($result["keys"][$eid]) && $result["keys"][$eid]["owner"]==$username)
			{
				$rec["visibility"]= ($row["visibility"]=="public")?"MyOwnPublic":"MyOwnPrivate";
				$rec["k1"]=$result["keys"][$eid]["k1"];
				$rec["k2"]=$result["keys"][$eid]["k2"];
				$rec["edgegateway_type"]= $result["keys"][$eid]["edgegateway_type"];
				$rec["edgegateway_uri"]= $result["keys"][$eid]["edgegateway_uri"];
				$rec["owner"]=$result["keys"][$eid]["owner"];
				$rec["privatekey"]= $row["privatekey"];
			    $rec["certificate"]= $row["certificate"];
            }
			else  if (isset($result["keys"][$eid]) && $result["keys"][$eid]["owner"]!=$username)
			{

			   $rec["visibility"]= $row["visibility"]; // =="public")?"MyOwnPublic":"MyOwnPrivate";
			   $rec["k1"]=$result["keys"][$eid]["k1"];
			   $rec["k2"]=$result["keys"][$eid]["k2"];
			   $rec["edgegateway_type"]= $result["keys"][$eid]["edgegateway_type"];
			   $rec["edgegateway_uri"]= $result["keys"][$eid]["edgegateway_uri"];
			   $rec["owner"]=$result["keys"][$eid]["owner"];
			   // the following two information should be added, if we wish to give the chaance
			   // to the administrator to see the privatekey and certificate of one of its user_error 
			   // $rec["privatekey"]= $row["privatekey"];
		       // $rec["certificate"]= $row["certificate"];
            }
			else 
			{
			   $rec["visibility"]= $row["visibility"]; // =="public")?"MyOwnPublic":"MyOwnPrivate";
			   $rec["k1"]="";
			   $rec["k2"]="";
			   $rec["owner"]="";
			}
				
	        array_push($data, $rec); 
		}
	 }
	 //MM0310 100 is a temporary value
	  $output= format_result($_REQUEST["draw"], $selectedrows+1, $selectedrows+1, $data, "", "\r\n action=get_subset_device_admin \r\n", 'ok');
 
    //   $result['msg'] = $q;
	}
	else{
	   /* $result['status'] = 'ko';
	   $result['msg'] = 'Error: errors in reading data about devices. <br/>' .
						   generateErrorMessage($link);
		$result['log'] = '\r\n Error: errors in reading data about devices.' . $q .
						   generateErrorMessage($link);	 */	
	$output= format_result($_REQUEST["draw"], 0, 0, null, 'Error: errors in reading data about devices. <br/>' . generateErrorMessage($link), '\n\r Error: errors in reading data about devices.' . generateErrorMessage($link), 'ko');
	 //Sara611 - for logging purpose
	  logAction($link,$username,'device','get_subset_device_admin','',$organization,'','success');	     						   
	}    
	my_log($output);
	mysqli_close($link);
}

else if($action == "get_all_device_latlong")
{
/* Sara611 not logged because the granularity of this action is too small */
    $organization = mysqli_real_escape_string($link, $_REQUEST['organization']);
    $loggedrole= mysqli_real_escape_string($link, $_REQUEST['loggedrole']);
    $username = mysqli_real_escape_string($link, $_REQUEST['username']);
    
    $ownDevices= "";
    if (!empty($accessToken)) 
	{ 
	 getOwnerShipDevice($accessToken, $result);
     getDelegatedDevice($accessToken, $username, $result);
	}
	
    $q = "SELECT DISTINCT id, contextBroker, latitude, longitude, uri, visibility,organization FROM devices where deleted IS null; ";
			 
    $r = mysqli_query($link, $q);
	
	if($r) 
	{
	 $result['status'] = 'ok';
     $result["log"]= "\r\n action=get_all_device_latlong " . $q . " \r\n";
	 $result['content'] = array();
     
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
                ||
                    (isset($result["keys"][$eid]) && $result["keys"][$eid]["owner"]==$username)
                    
               )
        
        array_push($result['content'], $row);
	 }
    }
	else{
	   $result['status'] = 'ko';
	   $result['msg'] = 'Error: errors in reading data about devices. <br/>' .
						  generateErrorMessage($link);
	  $result['msg'] = '\r\n action=get_all_device_latlong -- Error: errors in reading data about devices.' . $q .
						  generateErrorMessage($link);
	}    
	my_log($result);
	mysqli_close($link);
}

else if ($action=="exist_device")
{
	//we don't need to add the organization here, because name & context broker should not repeat even in different organizations
    $id = mysqli_real_escape_string($link, $_REQUEST['id']);
	$cb = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);

	$result = array();
	$q1 = "SELECT * FROM devices WHERE deleted is null and contextbroker = '$cb' AND id = '$id'";
    $r = mysqli_query($link, $q1);
	
	if($r) 
	{
	 $result['status'] = 'ok';
	 $result["log"]= "\r\n action=exist_device " . $q1;
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
						   generateErrorMessage($link);
		$result["log"]= "\r\n action=exist_device " . $q1 . 'The following errors occurred:' .
						   generateErrorMessage($link);				   
	}    
	my_log($result);
	mysqli_close($link);
	
}
else if($action == "get_device_with_attributes")
{ // Elf: this function is NOT to be included in the API 
    //Fatima: since it's not included, I will not update anyway
     
	$q = "SELECT d.id, d.devicetype, d.protocol, d.format,
       d.latitude AS dlat, d.longitude AS dlong, c.name,  c.ip, c.port, c.latitude, c.longitude FROM
	   devices d JOIN contextbroker c ON (d.contextbroker=c.name) WHERE deleted is null and mandatoryproperties=1 and mandatoryvalues=1 order by d.id";
	/*
	$q = "SELECT d.id, d.devicetype, d.protocol, d.format,
       d.latitude AS dlat, d.longitude AS dlong, c.name,  c.ip, c.port, c.latitude, c.longitude FROM
	   devices d JOIN contextbroker c ON (d.contextbroker=c.name) WHERE c.name='orionFinland' OR c.name='antwerp' OR c.name='antwerp2' AND deleted is null and mandatoryproperties=1 and mandatoryvalues=1 order by d.id";
	*/
	$r = mysqli_query($link, $q);

	$count = "SELECT max(c) AS max FROM (SELECT d.id, count(*) as c
	FROM event_values ev JOIN devices d ON ev.cb = d.contextBroker AND ev.device = d.id 
	GROUP BY ev.cb, ev.device) AS t";
	
	$r = mysqli_query($link, $q);
	$val = mysqli_query($link, $count);
	$max = 0;
	if($val){
		$row = mysqli_fetch_assoc($val);
		$max = $row['max'];
	}
	
	 echo "devicetype\t protocol\t format\t dlat\t dlong\t ip\t port\t latitude\t longitude\t";
	// echo "devicetype\t protocol\t dlat\t dlong\t";
	 for ($i=1; $i<= $max; $i++)
	 {
	  if($i == $max)
	   echo "value_name$i\t data_type$i";
	  else
	  	   echo "value_name$i\t data_type$i\t";

	 }
	 echo "\n";
	 while($row = mysqli_fetch_assoc($r)) 
     {
	   echo $row["devicetype"] . "\t" . $row["protocol"] . "\t" . $row["format"] . "\t" . $row["dlat"] . "\t" . $row["dlong"] . "\t" . $row["ip"] . "\t" . $row["port"] . "\t" . $row["latitude"] . "\t" . $row["longitude"] . "\t";


	   $q1 = "SELECT v.value_name, v.data_type FROM event_values v WHERE device='$row[id]' and cb='$row[name]' order by value_name;";
	   //echo $q1;
	   $r1 = mysqli_query($link, $q1);
	   $i=0;
	   while($row1 = mysqli_fetch_assoc($r1)) 
       {
		if($row1["value_name"] == "PM2,5"){
			echo "PM2.5". "\t" . $row1["data_type"] . "\t";
						$i++;

		}
		else{
			echo $row1["value_name"] . "\t" . $row1["data_type"] . "\t";
			$i++;
			}
	   }
	   for ($k=$i; $k < $max; $k++)
	   {
		 if($k == $max-1)
			echo "null\t null";
		 else
			echo "null\t null\t";
	   }
	   echo "\n";
     }   
	// my_log($result);
	mysqli_close($link);
	}
else if ($action == 'download'){
        $result["log"]="\r\n download invoked\n";
		
		//Sara2510 - for logging purpose
		$username = mysqli_real_escape_string($link, @$_REQUEST['username']);
		$organization = mysqli_real_escape_string($link, @$_REQUEST['organization']);
		$contextbroker = mysqli_real_escape_string($link, @$_REQUEST['contextbroker']);
		
        if (!empty($accessToken))
		{
		//retrieve ownership of devicename	
        $eID=$organization.":".$contextbroker.":".$_REQUEST['devicename'];
		$url= $GLOBALS["ownershipURI"] . "ownership-api/v1/list/?type=IOTID&accessToken=" . $accessToken . "&elementId=".$eID;
     		$local_result = file_get_contents($url);
		if(strpos($http_response_header[0], '200') === false)
                {
		   $result["status"]='ko';
                   $result["msg"] .= "\n error in acceding the ownership";
                   $result["log"] .= "\n error in acceding the ownership";
                }
                else
                {
			if (strpos($_REQUEST['filename'], $_REQUEST['devicename']) === false)
                        {
									//Sara2610 - For logging purpose
									logAction($link,$currentUser,'devices','download',$_REQUEST['devicename'] . " ". $_REQUEST["filename"] . " filename was tempered",$organization,'','faliure');
									
                                   $result["status"]='ko';
                                   $result["msg"] .= "\n the filename was tempered";
                                   $result["log"] .= "\n the filename was tempered";
                        }
                        else{
				if (strpos($local_result, $_REQUEST['devicename']) === false)
	                        {		
									//Sara2510 - For logging purpose
									logAction($link,$currentUser,'devices','download',$_REQUEST['devicename'] . " ". $_REQUEST["filename"]. " you don't own the requested device",$organization,'',success);
                                   $result["status"]='ko';
                                   $result["msg"] .= "\n you don't own the requested device";
                                   $result["log"] .= "\n you don't own the requested device";
        	                }
                	        else{
                        	        $result['status'] = 'ok';
	                                $result['msg'] = file_get_contents($pathCertificate.$_REQUEST['filename']);
        	                        $result['log']='\r\n download success';
									
									//Sara2510 - For logging purpose
									logAction($link,$currentUser,'devices','download',$_REQUEST['devicename'] . " ". $_REQUEST["filename"],$organization,'','success');
                	        }
                        }
                }		
        }
        else{
				//Sara2510 - For logging purpose
				logAction($link,$currentUser,'devices','download',$_REQUEST['devicename'] . " ". $_REQUEST["filename"],$organization,'','faliure');
                $result['status'] = 'ko';
                $result['msg'] = 'Error: no specified token';
                $result['log'] = '\n\r Error: no specified token';
        }
        my_log($result);
}
else if($action == "get_delegations")
{
       $uri =  mysqli_real_escape_string($link, $_REQUEST['uri']);
       $user = mysqli_real_escape_string($link, $_REQUEST['user']);
       $cb = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
       $dev_organization = mysqli_real_escape_string($link, $_REQUEST['dev_organization']);
       $id = mysqli_real_escape_string($link, $_REQUEST['id']);
    
        $eid=$dev_organization.":".$cb.":".$id;

       getDelegatorDevice($accessToken, $user, $result, $eid);
       my_log($result);
}
else if($action == "add_delegation")
{
       $cb = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
       $dev_organization = mysqli_real_escape_string($link, $_REQUEST['dev_organization']);
       $id = mysqli_real_escape_string($link, $_REQUEST['id']);
       $uri =  mysqli_real_escape_string($link, $_REQUEST['uri']);
       $user = mysqli_real_escape_string($link, $_REQUEST['user']);
       $delegated_user = (isset($_REQUEST['delegated_user']))?mysqli_real_escape_string($link, $_REQUEST['delegated_user']):"";
       $delegated_group= (isset($_REQUEST['delegated_group']))?mysqli_real_escape_string($link, $_REQUEST['delegated_group']):"";
       $k1 = mysqli_real_escape_string($link, $_REQUEST['k1']);
       $k2 = mysqli_real_escape_string($link, $_REQUEST['k2']);

       $delId= $dev_organization.":".$cb.":".$id;
       if (($delegated_user != "" || $delegated_group != "") && $user != ""){
		//retrieve any values of this this device
	        $q1 = "SELECT * FROM event_values WHERE cb = '$cb' and device='$id'";
	        $values = mysqli_query($link, $q1);

		//delegate any values of this device
		while($value = mysqli_fetch_assoc($values))
		{
			delegateDeviceValue($uri ."/" . $value["value_name"], $cb, $value["value_name"], $user, $delegated_user, $delegated_group, $accessToken, $k1, $k2, $result);
		}

		 //delegate the device
                delegateDeviceValue($delId, $cb, NULL, $user, $delegated_user, $delegated_group, $accessToken, $k1, $k2, $result);
	}
        else
        {
          $result["status"]='ko';
          $result["msg"]='\n the function delegate_value has been called without specifying mandatory parameters';
          $result["log"]='\n the function delegate_value has been called without specifying mandatory parameters';
        }
        my_log($result);
}
else if($action == "remove_delegation")
{
	$user = mysqli_real_escape_string($link, $_REQUEST['user']);
	$delegationId = mysqli_real_escape_string($link, $_REQUEST['delegationId']);
	$uri = mysqli_real_escape_string($link, $_REQUEST['uri']);
	if (isset($_REQUEST['userDelegated']))
		$userDelegated = mysqli_real_escape_string($link, $_REQUEST['userDelegated']);
	if (isset($_REQUEST['groupDelegated'])){

		$provgroupDelegated = mysqli_real_escape_string($link, $_REQUEST['groupDelegated']);

		$indexcomma=strrpos($provgroupDelegated, "All groups");
		if ($indexcomma==false){
                        $indexcomma=strrpos($provgroupDelegated, ",");
                        $groupDelegated="cn=".substr($provgroupDelegated, $indexcomma+1).",ou=".substr($provgroupDelegated, 0, $indexcomma).",".$GLOBALS["ldapBaseName"];
		}
		else {
                        $groupDelegated="ou=".substr($provgroupDelegated, 0,$indexcomma-1).",".$GLOBALS["ldapBaseName"];
		}
	}
	$cb = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
	$id = mysqli_real_escape_string($link, $_REQUEST['id']);


	//remove delegation the device
	removeDelegationValue($accessToken, $user, $delegationId, $result);

	//retrieve any values of this device
	$q1 = "SELECT * FROM event_values WHERE cb = '$cb' and device='$id'";
        $values = mysqli_query($link, $q1);

	//retrieve any delegation made on values of this this device
	while($value = mysqli_fetch_assoc($values))
	{

		getDelegatorDevice($accessToken, $user, $result, $uri ."/" . $value["value_name"]);
		$delegated=$result["delegation"];
		$i=0;
		while ($i < count($delegated))
		{
			
			if (isset($delegated[$i]["userDelegated"]) && (isset($userDelegated)) && $delegated[$i]["userDelegated"]==$userDelegated)
			{
					$delegationId= $delegated[$i]["delegationId"];
					removeDelegationValue($accessToken, $user, $delegationId, $result);
			}
			else if (isset($delegated[$i]["groupDelegated"]) && (isset($groupDelegated)) && $delegated[$i]["groupDelegated"]==$groupDelegated)
                        {
                                        $delegationId= $delegated[$i]["delegationId"];
                                        removeDelegationValue($accessToken, $user, $delegationId, $result);
                        }
			$i++;
		}
	}

	my_log($result);
}

else if ($action=="get_config_data")
{
	//This action is used for node-red
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
	     d.`frequency`, d.`created`, d.`privatekey`, d.`certificate`,d.`organization`, cb.`name`, cb.`protocol` as type, cb.`accesslink` AS ip, cb.`accessport` AS port, cb.`sha` FROM `devices` d JOIN `contextbroker` cb ON (d.contextBroker=cb.name) where d.deleted IS null order by d.id"; 
     
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
}
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

       /*$q  = "SELECT v.cb, v.device, v.value_name, v.data_type, v.value_type, v.editable, v.value_unit, v.healthiness_criteria, d.latitude, d.longitude, d.protocol, d.visibility, d.kind, d.uri FROM `event_values` v JOIN devices d ON (v.device=d.id and v.cb = d.contextbroker)  ";*/ 
    
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
}

else 
{
	$result['status'] = 'ko';
	$result['msg'] = 'invalid action ' . $action;
	$result['log'] = 'invalid action ' . $action;
	my_log($result);
}

