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
$link = mysqli_connect($host, $username, $password) or die("failed to connect to server !!");
mysqli_select_db($link, $dbname);
error_reporting(E_ERROR | E_NOTICE);

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
	$result['status'] = 'ko';
	$result['msg'] = 'action not present'; 
	$result['log'] = 'action not present';
	my_log($result);
	mysqli_close($link);
	exit();
}

$headers = apache_request_headers();
if (isset($headers['Authorization']) && strlen($headers['Authorization'])>8 )
{
	$_REQUEST['token']=substr($headers['Authorization'],7);
}

require '../sso/autoload.php';
use Jumbojett\OpenIDConnectClient;

$oidc = new OpenIDConnectClient($keycloakHostUri, $clientId, $clientSecret);
$oidc->providerConfigParam(array('token_endpoint' => $keycloakHostUri.'/auth/realms/master/protocol/openid-connect/token',
                                        'userinfo_endpoint'=>$keycloakHostUri.'/auth/realms/master/protocol/openid-connect/userinfo'));
if (isset($_REQUEST['nodered']))
{
	if ((isset($_REQUEST['token']))&&($_REQUEST['token']!='undefined'))
		$accessToken = $_REQUEST['token'];
	else $accessToken = "";
} 
else
{
	if (isset($_REQUEST['token'])) 
	{
		$tkn = $oidc->refreshToken($_REQUEST['token']);
		$accessToken = $tkn->access_token;
	}
	else $accessToken ="";
}

if (isset($_REQUEST['username'])) 
{
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
	$missingParams=missingParameters(array('id', 'type', 'contextbroker', 'kind', 'format', 'model', 'producer',
		'latitude', 'longitude', 'k1', 'k2', 'frequency', 'attributes'));

	if (empty($accessToken))
	{
		$result["status"]="ko";
        $result['msg'] = "Access Token not present";
        $result["error_msg"] .= "Problem in insert device (Access Token not present)";
        $result["log"]= "action=insert - error AccessToken not present\r\n";
	}
	else if (!empty($missingParams))
	{
		$result["status"]="ko";
        $result['msg'] = "Missing Parameters";
        $result["error_msg"] .= "Problem in insert device (Missing parameters: ".implode(", ",$missingParams)." )";
        $result["log"]= "action=insert - error Missing Parameters: ".implode(", ",$missingParams)." \r\n";
	}
	else 
	{
		//retrieve username, organization and role from the accetoken
		//TODO avoid passing all the parameters for LDAP
		get_user_info($accessToken, $username, $organization, $oidc, $role, $result, $ldapBaseName, $ldapServer, $ldapPort, $ldapAdminName, $ldapAdminPwd);

		if ($result["status"]!="ok")
		{
			$result["status"]="ko";
            $result['msg'] = "Cannot retrieve user information";
   	        $result["error_msg"] .= "Problem in insert device (Cannot retrieve user information)";
           	$result["log"]= "action=insert - error Cannot retrieve user information\r\n";
		}
		else
		{
			$id = mysqli_real_escape_string($link, $_REQUEST['id']);
			$devicetype = mysqli_real_escape_string($link, $_REQUEST['type']); 
			$contextbroker = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);  
			$kind = mysqli_real_escape_string($link, $_REQUEST['kind']);  
			$format = mysqli_real_escape_string($link, $_REQUEST['format']);  
			if (isset($_REQUEST['mac'])) $macaddress = mysqli_real_escape_string($link, $_REQUEST['mac']);  
			else $macaddress="";
			if (isset($_REQUEST['model'])) $model = mysqli_real_escape_string($link, $_REQUEST['model']);  
			else $model = "custom";
			if (isset($_REQUEST['producer'])) $producer = mysqli_real_escape_string($link, $_REQUEST['producer']);  
			else $producer = "";
			$latitude= mysqli_real_escape_string($link, $_REQUEST['latitude']);  
			$longitude = mysqli_real_escape_string($link, $_REQUEST['longitude']);  
			if (isset($_REQUEST['visibility'])) $visibility = mysqli_real_escape_string($link, $_REQUEST['visibility']);  
			else $visibility="private";
			$frequency= mysqli_real_escape_string($link, $_REQUEST['frequency']);
			if (isset($_REQUEST['subnature'])) $subnature=mysqli_real_escape_string($link, $_REQUEST['subnature']);
			else $subnature="";
			if (isset($_REQUEST['static_attributes'])) $staticAttributes = mysqli_real_escape_string($link, $_REQUEST['static_attributes']);   
			else $staticAttributes ="[]";
			if (isset($_REQUEST['service'])) $service = mysqli_real_escape_string($link, $_REQUEST['service']);
			else $service = "";
			if (isset($_REQUEST['servicePath'])) $servicePath = mysqli_real_escape_string($link, $_REQUEST['servicePath']);
			else $servicePath="";
			if (isset($_REQUEST['shouldbeRegistered'])) $shouldbeRegistered=$_REQUEST['shouldbeRegistered'];
			else $shouldbeRegistered="yes";
			if (isset($_REQUEST['k1'])) $k1= $_REQUEST['k1'];
			else $k1="";
			if (isset($_REQUEST['k2'])) $k2= $_REQUEST['k2'];
			else $k2="";
			if (isset($_REQUEST['edgegateway_type'])) $edgegateway_type = $_REQUEST['edgegateway_type'];
			else $edgegateway_type="";
			if (isset($_REQUEST['edgegateway_uri'])) $edgegateway_uri = $_REQUEST['edgegateway_uri'];
			else $edgegateway_uri="";	
			$listAttributes= json_decode($_REQUEST['attributes']);

			$protocol = getProtocol($contextbroker, $link);

			if (empty($protocol))//it also ensure the contextbroker name is valid
			{
				$result["status"]="ko";
                $result['msg'] = "Unrecognized contextbroker/protocol";
       	        $result["error_msg"] .= "Problem in insert device (Unrecognized contextbroker/protocol)";
               	$result["log"]= "action=insert - error Unrecognized contextbroker/protocol\r\n";	
			}
			else 
			{
				insert_device($link, $id, $devicetype, $contextbroker, $kind, $protocol, $format, $macaddress, $model, $producer,
					$latitude, $longitude, $visibility, $frequency, $k1, $k2, $edgegateway_type, $edgegateway_uri,
					$listAttributes, $subnature, $staticAttributes, $pathCertificate, $accessToken, $result, $shouldbeRegistered, 
					$organization, retrieveKbUrl($organizationApiURI, $organization), $username, $service, $servicePath);
        
				if ($result["status"]=="ok")
				{
					logAction($link,$username,'device','insert',$id . " ".$contextbroker,$organization,'','success');		
				}
				else 
				{
					logAction($link,$username,'device','insert',$id . " ".$contextbroker,$organization,'','failure');				
				}
			}
		}
	}
	my_log($result);
    mysqli_close($link);
}
else if ($action=="update")
{
	$missingParams=missingParameters(array('id', 'type', 'contextbroker', 'gb_old_cb', 'kind', 'format', 'model', 'producer',
		'latitude', 'longitude', 'k1', 'k2', 'frequency'));
 
	if (empty($accessToken))
	{
		$result["status"]="ko";
		$result['msg'] = "Access Token not present";
		$result["error_msg"] .= "Problem update the device information (Access Token not present). ";
		$result["log"]= "action=update - error AccessToken not present\r\n";
		my_log($result);
		mysqli_close($link);
	}
	else if (!empty($missingParams))
    {
		$result["status"]="ko";
		$result['msg'] = "Missing Parameters";
		$result["error_msg"] .= "Problem update the device information (Missing parameters: ".implode(", ",$missingParams)." )";
		$result["log"]= "action=update - error Missing Parameters: ".implode(", ",$missingParams)." \r\n";
		my_log($result);
		mysqli_close($link);
	}
	else
	{
		//retrieve username, organization and role from the accetoken
		//TODO avoid passing all the parameters for LDAP
		get_user_info($accessToken, $username, $organization, $oidc, $role, $result, $ldapBaseName, $ldapServer, $ldapPort, $ldapAdminName, $ldapAdminPwd);

		if ($result["status"]!="ok")
		{
			$result["status"]="ko";
			$result['msg'] = "Cannot retrieve user information";
			$result["error_msg"] .= "Problem update the device information (Cannot retrieve user information)";
			$result["log"]= "action=update - error Cannot retrieve user information\r\n";
			my_log($result);
			mysqli_close($link);
		}
		else
		{
			$id = mysqli_real_escape_string($link, $_REQUEST['id']);
			$devicetype = mysqli_real_escape_string($link, $_REQUEST['type']); 
			$contextbroker = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);  
			$old_contextbroker = mysqli_real_escape_string($link, $_REQUEST['gb_old_cb']); 
			$kind = mysqli_real_escape_string($link, $_REQUEST['kind']);  
			$format = mysqli_real_escape_string($link, $_REQUEST['format']);  
			if (isset($_REQUEST['mac'])) $macaddress = mysqli_real_escape_string($link, $_REQUEST['mac']);  
			else $macaddress ="";
			if (isset($_REQUEST['model'])) $model = mysqli_real_escape_string($link, $_REQUEST['model']);  
			else $model = "custom";
			if (isset($_REQUEST['producer'])) $producer = mysqli_real_escape_string($link, $_REQUEST['producer']);  
			else $producer="";
			$latitude= mysqli_real_escape_string($link, $_REQUEST['latitude']);  
			$longitude = mysqli_real_escape_string($link, $_REQUEST['longitude']);  
			//$uri = mysqli_real_escape_string($link, $_REQUEST['uri']);
			//$dev_organization = mysqli_real_escape_string($link, $_REQUEST['dev_organization']);
			if (isset($_REQUEST['k1'])) $k1= $_REQUEST['k1'];
			else $k1= "";
			if (isset($_REQUEST['k2'])) $k2= $_REQUEST['k2'];
			else $k2= "";
			if (isset($_REQUEST['subnature'])) $subnature= mysqli_real_escape_string($link, $_REQUEST['subnature']);
			else $subnature="";
			if (isset($_REQUEST['static_attributes'])) $staticAttributes= mysqli_real_escape_string($link, $_REQUEST['static_attributes']);
			else $staticAttributes="[]";
			if (isset($_REQUEST['service'])) $service = mysqli_real_escape_string($link, $_REQUEST['service']);
			else $service="";
			if (isset($_REQUEST['servicePath'])) $servicePath = mysqli_real_escape_string($link, $_REQUEST['servicePath']);
			else $servicePath ="";
			if (isset( $_REQUEST['edgegateway_type'])) $edgegateway_type = $_REQUEST['edgegateway_type'];
			else $edgegateway_type="";
			if (isset( $_REQUEST['edgegateway_uri'])) $edgegateway_uri = $_REQUEST['edgegateway_uri'];
			else $edgegateway_uri="";
			if (isset($_REQUEST['visibility'])) $visibility = mysqli_real_escape_string($link, $_REQUEST['visibility']);  
			else  $visibility="private";
			$frequency= mysqli_real_escape_string($link, $_REQUEST['frequency']);
			if (isset($_REQUEST['deleteattributes'])) $listdeleteAttributes= json_decode($_REQUEST['deleteattributes']);
			else $listdeleteAttributes=json_decode("[]");
			$listAttributes= json_decode($_REQUEST['attributes']);
			if (isset($_REQUEST['newattributes'])) $listnewAttributes= json_decode($_REQUEST['newattributes']);
			else $listnewAttributes= json_decode("[]");
	
			if ($listAttributes==null) $merge=$listnewAttributes;
			else if ($listnewAttributes==null) $merge=$listAttributes;
			else $merge=array_merge($listAttributes,$listnewAttributes);
			if ($listdeleteAttributes!=null) $merge = array_udiff($merge, $listdeleteAttributes, 'compare_values');

			$protocol = getProtocol($contextbroker, $link);

			if (empty($protocol))//it also ensure the contextbroker name is valid
			{
				$result["status"]="ko";
				$result['msg'] = "Unrecognized contextbroker/protocol";
				$result["error_msg"] .= "Problem in update device (Unrecognized contextbroker/protocol)";
				$result["log"]= "action=update - error Unrecognized contextbroker/protocol\r\n";
				my_log($result);
				mysqli_close($link);
			}
			else
			{
				//id management
				if ($protocol == "ngsi w/MultiService")	$id = $service . "." . $servicePath . "." . $id;
				get_device($username, $role, $id, $contextbroker,  $accessToken, $link, $result);
				
				if (empty($result["content"]))
				{
					$result["status"]="ko";
    	            $result['msg'] = "Unrecognized device";
        	        $result["error_msg"] .= "Problem in update device (Unrecognized device)";
            	    $result["log"]= "action=update - error Unrecognized device\r\n";
                	my_log($result);
	                mysqli_close($link);
				}
				else
				{
				$dev_organization=$result["content"]["organization"];
				$uri=$result["content"]["uri"];				
				$eId=$dev_organization.":".$contextbroker.":".$id;
				
				if (!enforcementRights($username, $accessToken, $role, $eId, 'IOTID','write', $result))
				{
					$result["status"]="ko";
					$result['msg'] = "Not ownership or enough right to update";
					$result["error_msg"] .= "Problem in update device ()";
					$result["log"] .= "action=update - error Not ownership or enough right to update\r\n";
					my_log($result);
					mysqli_close($link);
            	}
				else
				{
 				 $notDuplicate = true;
				 $selectDevices = "SELECT contextBroker, id FROM devices WHERE contextBroker = '$contextbroker' AND id = '$id';";		
				 $s1 = mysqli_query($link, $selectDevices);
				 $notDuplicate = (mysqli_num_rows($s1) != 0);

				 if($notDuplicate)
				 {
				  updateKB($link, $id, $devicetype, $contextbroker, $kind, $protocol, $format, $macaddress, $model, $producer, $latitude, $longitude,
					$visibility, $frequency, $merge, $listdeleteAttributes, $uri, $dev_organization, $subnature, $staticAttributes, $result, 
					$service, $servicePath, retrieveKbUrl($organizationApiURI, $dev_organization)); 

				  if ($result["status"]=='ko')
				  {
		        	  logAction($link,$username,'device','update',$id . " ".$contextbroker,$organization,'','faliure');
			          $result["status"]='ko';
			          $result["error_msg"] .= "Problem in updating the device in the KB $id. ";
			          $result["msg"] .= "\n Problem in updating the device in the KB $id:" . generateErrorMessage($link);
		        	  $result["log"] .= "\n Problem in updating the device in the KB $id:" . generateErrorMessage($link);
			          my_log($result);
			          mysqli_close($link);
				  return $result;
				}	
	 
				if ($result["status"]=='ok' &&  $result["content"]==null)
				{
				$q = "UPDATE devices SET contextBroker='$contextbroker', devicetype='$devicetype', kind= '$kind', protocol='$protocol', format='$format', 
						macaddress='$macaddress', model='$model', producer='$producer', latitude='$latitude', longitude='$longitude', frequency = '$frequency', 
						organization='$dev_organization', subnature='$subnature', static_attributes='$staticAttributes' 
						WHERE id='$id' and contextBroker='$old_contextbroker'";
				}
				else 
				{			
				$q = "UPDATE  devices SET uri = '". $result["content"] . "', mandatoryproperties=1, mandatoryvalues=1, contextBroker='$contextbroker', 
						devicetype='$devicetype', kind= '$kind', protocol='$protocol', format='$format', macaddress='$macaddress', model='$model', 
						producer='$producer', latitude='$latitude', longitude='$longitude', frequency = '$frequency',organization='$dev_organization', 
						subnature='$subnature', static_attributes='$staticAttributes'  WHERE id='$id' and contextBroker='$old_contextbroker'";
				}

				$r = mysqli_query($link, $q);
			
				if($r) 
				{
					$result["msg"] .= "\n Device $contextbroker/$id correctly updated";
					$result["log"] .= "\r\n Device $contextbroker/$id correctly updated";
					if($result["status"]=="ok")
					{
						logAction($link,$username,'device','update',$id . " ".$contextbroker,$organization,'','success');		
					}
			        $result["visibility"] = $visibility;
					if($result["content"]==null) $result["active"]=false;  
					else $result["active"]=true;
		
					$ownmsg = array();
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
		
					$ok=true;
					$q="";
					$a=0;
					$b=1;
					while ($a < count($listAttributes) && $ok)
					{
				   $att=$listAttributes[$a];	
				   if ($att->healthiness_criteria=="refresh_rate")  $hc="value_refresh_rate";
				   else if ($att->healthiness_criteria=="different_values") $hc="different_values";
				   else $hc="value_bounds";
		 
				  $b=$a+1;
				  $upquery="UPDATE event_values SET cb='$contextbroker', device = '$id',value_name='$att->value_name',data_type='$att->data_type', 
					event_values.order='$b',value_type='$att->value_type', editable='$att->editable', value_unit='$att->value_unit', 
					healthiness_criteria='$att->healthiness_criteria', value_refresh_rate='$att->healthiness_value', old_value_name= '$att->value_name' 
					WHERE  cb='$old_contextbroker' AND device='$id' AND value_name='$att->old_value_name';";
				  $r1 = mysqli_query($link, $upquery);
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
		   
						$insertquery="INSERT INTO `event_values`(`cb`, `device`, `old_value_name`, `value_name`, `data_type`, `order`, 
							`value_type`, `editable`, `value_unit`, `healthiness_criteria`, `$hc`) 
							VALUES ('$contextbroker','$id','$att->value_name', '$att->value_name','$att->data_type','$b','$att->value_type',
							'$att->editable','$att->value_unit','$att->healthiness_criteria','$att->healthiness_value');";
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
				else  
				{
			          logAction($link,$username,'device','update',$id . " ".$contextbroker,$organization,'','faliure');
	        		  $result["status"]='ko';
		        	  $result["error_msg"] .= "Problem in updating the device value $id. ";
			          $result["msg"] .= "\n Problem in updating the device value $id:" . generateErrorMessage($link);
			          $result["log"] .= "\n Problem in updating the device value $id:" . $q . " " . generateErrorMessage($link);
		        	  my_log($result);
			          mysqli_close($link);
				  }
				 }  
				 else
			  	 {
					logAction($link,$username,'device','update',$id . " ".$contextbroker,$organization,'','failure');		
					$result["status"]='ko';
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
	}
}  
else if ($action=="delete")
{
	$missingParams=missingParameters(array('id', 'contextbroker'));

	if (empty($accessToken))
	{
		$result["status"]="ko";
		$result['msg'] = "Access Token not present";
		$result["error_msg"] .= "Problem deleting the device information (Access Token not present). ";
		$result["log"]= "action=delete - error AccessToken not present\r\n";
	}
	else if (!empty($missingParams))
	{
		$result["status"]="ko";
		$result['msg'] = "Missing Parameters";
		$result["error_msg"] .= "Problem deleting the device information (Missing parameters: ".implode(", ",$missingParams)." )";
		$result["log"]= "action=delete - error Missing Parameters: ".implode(", ",$missingParams)." \r\n";
	}
	else
	{
		//retrieve username, organization and role from the accetoken
		//TODO avoid passing all the parameters for LDAP
		get_user_info($accessToken, $username, $organization, $oidc, $role, $result, $ldapBaseName, $ldapServer, $ldapPort, $ldapAdminName, $ldapAdminPwd);

        if ($result["status"]!="ok")
        {
			$result["status"]="ko";
            $result['msg'] = "Cannot retrieve user information";
            $result["error_msg"] .= "Problem deleting the device information (Cannot retrieve user information)";
            $result["log"]= "action=delete - error Cannot retrieve user information\r\n";
		}
		else
        {
			$id = $_REQUEST['id'];
			$cb = $_REQUEST['contextbroker'];
			//$url = $_REQUEST['uri']; 
			//$dev_organization = mysqli_real_escape_string($link, $_REQUEST['dev_organization']);
			if (isset($_REQUEST['service'])) $service = mysqli_real_escape_string($link, $_REQUEST['service']);
			else $service ="";	
			if (isset($_REQUEST['servicePath'])) $servicePath = mysqli_real_escape_string($link, $_REQUEST['servicePath']);
			else $servicePath ="";

			$protocol = getProtocol($cb, $link);

			if (empty($protocol))//it also ensure the contextbroker name is valid
			{
				$result["status"]="ko";
				$result['msg'] = "Unrecognized contextbroker/protocol";
				$result["error_msg"] .= "Problem in delete device (Unrecognized contextbroker/protocol)";
				$result["log"]= "action=delete - error Unrecognized contextbroker/protocol\r\n";
			}
			else
			{
				//id management
				if ($protocol == "ngsi w/MultiService") $id = $service . "." . $servicePath . "." . $id;
				get_device($username, $role, $id, $cb,  $accessToken, $link, $result);
				
				if (empty($result["content"]))
                {
                    $result["status"]="ko";
                    $result['msg'] = "Unrecognized device";
                    $result["error_msg"] .= "Problem in delete device (Unrecognized device)";
                    $result["log"]= "action=delete - error Unrecognized device\r\n";
                }
                else
                {
				$dev_organization=$result["content"]["organization"];
				//$url=$result["content"]["uri"];				
				$eId=$dev_organization.":".$cb.":".$id;

				if (!enforcementRights($username, $accessToken, $role, $eId, 'IOTID','write', $result))
				{
					$result["status"]="ko";
					$result['msg'] = "Not ownership or enough right to delete";
					$result["error_msg"] .= "Problem in delete device (Not ownership or enough right to delete)";
					$result["log"]= "action=delete - error Not ownership or enough right to delete\r\n";		
				}
				else
				{
		     		deleteKB($link, $id, $cb, retrieveKbUrl($organizationApiURI, $dev_organization), $result, $service, $servicePath);	   
	
					if ($result["status"]=='ko') return $result;
			
					$q1 = "UPDATE devices SET deleted = '". date("Y/m/d") . "' WHERE id = '$id' and contextBroker='$cb';";
					$q2 = "INSERT INTO deleted_devices select * from devices WHERE id = '$id' and contextBroker='$cb'and deleted IS NOT NULL;";
					$q3 = "INSERT INTO deleted_event_values (select cb,device,value_name,data_type,value_type,editable,value_unit,healthiness_criteria,
						value_refresh_rate, different_values,value_bounds, event_values.order from event_values where  device = '$id' and cb='$cb' );";
					$q4 = "DELETE FROM devices WHERE id = '$id' and contextBroker='$cb' and deleted IS NOT NULL;";
     
					$r1 = mysqli_query($link, $q1);
					if ($r1)
					{
						$r2 = mysqli_query($link, $q2);
						if($r2)
						{
							$r3 = mysqli_query($link, $q3);
							if($r3)
							{
								$r4 = mysqli_query($link, $q4);
           					}
               			}
       		    	}
					if($r4)
					{
						logAction($link,$username,'device','delete',$id . " ".$cb,$organization,'','success');	
						$result["status"]='ok';
						removeOwnerShipDevice($eId,$accessToken,$result);
				 	}
		 			else
			 		{
				  		logAction($link,$username,'device','delete',$id . " ".$cb,$organization,'','faliure');	
						$result["status"]='ko';
						$result["msg"] .= "\n Problem in deleting the device $id: " . generateErrorMessage($link); 
						$result["log"] .= "\n Problem in deleting the device $id: " . $query . " " . generateErrorMessage($link); 
					}
				}
				}
			}
		}
	}
	my_log($result);
	mysqli_close($link);
}
//in case of RootAdmin, use the username of the owner of the device
else if ($action =='change_visibility')
{
	$missingParams=missingParameters(array('id', 'contextbroker', 'visibility'));

	if (empty($accessToken))
	{
		$result["status"]="ko";
        $result['msg'] = "Access Token not present";
        $result["error_msg"] .= "Problem changing visibility (Access Token not present). ";
        $result["log"]= "action=change_visibility - error AccessToken not present\r\n";
	}
	else if (!empty($missingParams))
	{
        $result["status"]="ko";
        $result['msg'] = "Missing Parameters";
        $result["error_msg"] .= "Problem changing visibility (Missing parameters: ".implode(", ",$missingParams)." )";
        $result["log"]= "action=change_visibility - error Missing Parameters: ".implode(", ",$missingParams)." \r\n";
	}
	else
	{
		//retrieve username, organization and role from the accetoken
		//TODO avoid passing all the parameters for LDAP
		get_user_info($accessToken, $username, $organization, $oidc, $role, $result, $ldapBaseName, $ldapServer, $ldapPort, $ldapAdminName, $ldapAdminPwd);

		if ($result["status"]!="ok")
		{
			$result["status"]="ko";
			$result['msg'] = "Cannot retrieve user information";
			$result["error_msg"] .= "Problem changing visibility (Cannot retrieve user information)";
			$result["log"]= "action=change_visibility - error Cannot retrieve user information\r\n";
		}
		else
		{
			$id = mysqli_real_escape_string($link, $_REQUEST['id']);
			$contextbroker = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
			$visibility = mysqli_real_escape_string($link, $_REQUEST['visibility']);
			//$uri = mysqli_real_escape_string($link, $_REQUEST['uri']);
			//$dev_organization = mysqli_real_escape_string($link, $_REQUEST['dev_organization']);
			if (isset($_REQUEST['service'])) $service = mysqli_real_escape_string($link, $_REQUEST['service']);
		    else $service ="";
		    if (isset($_REQUEST['servicePath'])) $servicePath = mysqli_real_escape_string($link, $_REQUEST['servicePath']);
		    else $servicePath ="";

			$protocol = getProtocol($contextbroker, $link);

			if (empty($protocol))//it also ensure the contextbroker name is valid
			{
				$result["status"]="ko";
				$result['msg'] = "Unrecognized contextbroker/protocol";
				$result["error_msg"] .= "Problem in change visibility  (Unrecognized contextbroker/protocol)";
				$result["log"]= "action=change_visibility - error Unrecognized contextbroker/protocol\r\n";
			}
			else
			{
				//id management
				if ($protocol == "ngsi w/MultiService")	$id = $service . "." . $servicePath . "." . $id;
				get_device($username, $role, $id, $contextbroker,  $accessToken, $link, $result);
				if (empty($result["content"]))
                {
                    $result["status"]="ko";
                    $result['msg'] = "Unrecognized device";
                    $result["error_msg"] .= "Problem in change visibility (Unrecognized device)";
                    $result["log"]= "action=change_visibility - error Unrecognized device\r\n";
                }
                else
                {
				$dev_organization=$result["content"]["organization"];
				$uri=$result["content"]["uri"];
				$eId=$dev_organization.":".$contextbroker.":".$id;

				if (!enforcementRights($username, $accessToken, $role, $eId, 'IOTID','write', $result))
				{
					$result["status"]="ko";
					$result['msg'] = "Not ownership or enough right to update";
					$result["error_msg"] .= "Problem in change visibility (Not ownership or enough right to update)";
					$result["log"]= "action=change_visibility - error Not ownership or enough right to update\r\n";
				}
				else
				{
					$q = "UPDATE devices SET  visibility = '$visibility' WHERE id='$id' and contextBroker='$contextbroker'";
					$r = mysqli_query($link, $q);
			
					if($r) 
					{
						logAction($link,$username,'device','change_visibility',$id . " ".$contextbroker,$organization,'new visibility '.$visibility,'success');			
		
						$result["status"]='ok'; 
						$result["msg"] .= "\n Device Visibility correctly updated"; 
						$result["log"] .= "\n Device $id: Visibility correctly updated"; 
		
						// information to be passed to the interface
						$result["visibility"] = $visibility;

						//retrieve any values of this this device
			        	$q1 = "SELECT * FROM event_values WHERE cb = '$contextbroker' and device='$id'";
	    	    		$values = mysqli_query($link, $q1);

						if ($visibility==='public')
						{
							//make public the device
							delegateDeviceValue($eId, $contextbroker, NULL, $username, "ANONYMOUS", "", $accessToken, "", "", $result);
			
							//make public any values of this device
            				while($value = mysqli_fetch_assoc($values))
							{
	            		    	delegateDeviceValue($uri ."/" . $value["value_name"], $contextbroker, $value["value_name"], $username, "ANONYMOUS", "", $accessToken, "", "", $result);           
							}
						}
						else 
						{
							getDelegatorDevice($accessToken, $username, $result, $eId);
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
    	            	            if ($uri ."/" . $value["value_name"] === $public)
									{
            	                   		removeDelegationValue($accessToken, $username, $public_value['delegationId'], $result);
		        	                }
                			    }
		                	}
							unset($result["delegation"]);
						}
						$result["status"]="ok";
						$result["msg"]="The delegation to anonymous has been changed";
						$result["log"]="The delegation to anonymous has been changed";
					}
					else 
					{
						logAction($link,$username,'device','change_visibility',$id . " ".$contextbroker,$organization,'new visibility '.$visibility,'failure');			
			
						$result["status"]='ko';
						$result["msg"] .= "\n Problem in changing the visibility of the device $id: " . generateErrorMessage($link); 
						$result["log"] .= "\n Problem in changing the visibility of the device $id: " . generateErrorMessage($link); 
					}
				}
				}
			}
		}
	}
	my_log($result);
	mysqli_close($link);
}
else if ($action =='change_owner')
{
	$missingParams=missingParameters(array('id', 'contextbroker', 'newOwner', 'k1', 'k2', 'model'));

	if (empty($accessToken))
	{
		$result["status"]="ko";
        $result['msg'] = "Access Token not present";
        $result["error_msg"] .= "Problem changing owner (Access Token not present). ";
        $result["log"]= "action=change_owner - error AccessToken not present\r\n";
	}
	else if (!empty($missingParams))
    {
 		$result["status"]="ko";
        $result['msg'] = "Missing Parameters";
        $result["error_msg"] .= "Problem changing owner (Missing parameters: ".implode(", ",$missingParams)." )";
        $result["log"]= "action=change_owner - error Missing Parameters: ".implode(", ",$missingParams)." \r\n";
	}
	else
    {
		//retrieve username, organization and role from the accetoken
		//TODO avoid passing all the parameters for LDAP
		get_user_info($accessToken, $currentowner, $organization, $oidc, $role, $result, $ldapBaseName, $ldapServer, $ldapPort, $ldapAdminName, $ldapAdminPwd);

		if ($result["status"]!="ok")
		{
			$result["status"]="ko";
			$result['msg'] = "Cannot retrieve user information";
			$result["error_msg"] .= "Problem changing owner (Cannot retrieve user information)";
			$result["log"]= "action=change_owner - error Cannot retrieve user information\r\n";
		}
		else
		{
			//TODO: in this routine the error returned from the ownership is not managed and UI in not updated with error information!!!
			$id = mysqli_real_escape_string($link, $_REQUEST['id']);
			$cb = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
			$newuser = mysqli_real_escape_string($link, $_REQUEST['newOwner']);
			//$currentowner = mysqli_real_escape_string($link, $_REQUEST['owner']);     //TODO this is the loggedUser... but should be the owner of the device, since rootadmin could act!
			$k1 = mysqli_real_escape_string($link, $_REQUEST['k1']);
			$k2 = mysqli_real_escape_string($link, $_REQUEST['k2']);
			$model = mysqli_real_escape_string($link, $_REQUEST['model']);
			//$url =  mysqli_real_escape_string($link, $_REQUEST['uri']);
			//$dev_organization =  mysqli_real_escape_string($link, $_REQUEST['dev_organization']);
			if (isset( $_REQUEST['edgegateway_type'])) $edgegateway_type = $_REQUEST['edgegateway_type'];
			else $edgegateway_type="";
			if (isset( $_REQUEST['edgegateway_uri'])) $edgegateway_uri = $_REQUEST['edgegateway_uri'];
			else $edgegateway_uri="";
			if (isset($_REQUEST['service'])) $service = mysqli_real_escape_string($link, $_REQUEST['service']);
			else $service ="";
			if (isset($_REQUEST['servicePath'])) $servicePath = mysqli_real_escape_string($link, $_REQUEST['servicePath']);
			else $servicePath ="";

			$protocol = getProtocol($cb, $link);

			if (empty($protocol))//it also ensure the contextbroker name is valid
			{
				$result["status"]="ko";
				$result['msg'] = "Unrecognized contextbroker/protocol";
				$result["error_msg"] .= "Problem in change owner (Unrecognized contextbroker/protocol)";
				$result["log"]= "action=change_owner - error Unrecognized contextbroker/protocol\r\n";
			}
			else
			{
				//id management
				if ($protocol == "ngsi w/MultiService") $id = $service . "." . $servicePath . "." . $id;
				get_device($currentowner, $role, $id, $cb,  $accessToken, $link, $result);
				if (empty($result["content"]))
                {
                    $result["status"]="ko";
                    $result['msg'] = "Unrecognized device";
                    $result["error_msg"] .= "Problem in change owner (Unrecognized device)";
                    $result["log"]= "action=change_owner - error Unrecognized device\r\n";
                }
                else
                {
				$dev_organization=$result["content"]["organization"];
				$url=$result["content"]["uri"];
				$eId=$dev_organization.":".$cb.":".$id;				

				if (!enforcementRights($username, $accessToken, $role, $eId, 'IOTID','write', $result))
				{
					$result["status"]="ko";
					$result['msg'] = "Not ownership or enough right to update";
					$result["error_msg"] .= "Problem in change owner(Not ownership or enough right to update)";
					$result["log"]= "action=change_owner - error Not ownership or enough right to update\r\n";
				}
				else
				{
					//for change ownership, a new certificate has to be created (if model is authenticated)
					$errorThrown=false;
			        if (registerCertificatePrivateKey($link, $cb, $id, $model, $pathCertificate, $result, $currentowner))
		    	    {
						$privatekey = $id . "-key.pem";
						$certificate = $id . "-crt.pem";
						$publickey = $id . "-pubkey.pem";

						//update also local db information
						$q = "UPDATE devices SET privatekey='".$privatekey."', certificate='".$certificate."' where id='".$id."';";
						$r = mysqli_query($link, $q);
						if($r)
						{
							logAction($link,$currentowner,'device','change_owner',$id . " ".$cb,$organization,'new owner: '.$newuser,'success');			

							$result["msg"] .= "\n cert correctly updated";
							$result["log"] .= "\r\n cert correctly updated\r\n";
						}
						else 
						{
							logAction($link,$currentowner,'device','change_owner',$id . " ".$cb,$organization,'new owner: '.$newuser,'faliure');
				
							$result["msg"] .= "\n cert NOT correctly updated";
							$result["log"] .= "\r\n cert NOT correctly updated\r\n";
							$errorThrown=true;
						}
	        		}
		        	else
	        		{
						$privatekey = "";
						$certificate = "";
				        $publickey = "";
	        		}	
	
					if (!$errorThrown)
					{
						$ownmsg = array();
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
						if ($publickey!="") 
						{
        	        		$pub_key_str=str_replace("\n", "", file_get_contents($pathCertificate."/".$publickey));
            	            $ownmsg["elementDetails"]["publickey"]= substr($pub_key_str, 26, 736);
	            	    }
						registerOwnerShipDevice($eId, $ownmsg, $accessToken, $result);//insert new ownership
						$result["status"]='ok';
		
						logAction($link,$currentowner,'device','change_owner',$id . " ".$cb,$organization,'new owner: '.$newuser,'success');	
					}
					else 
					{
						logAction($link,$currentowner,'device','change_owner',$id . " ".$cb,$organization,'new owner: '.$newuser,'faliure');
						$result["status"]='ko';
					}
				}
				}
			}
		}
	}
 	my_log($result);
   	mysqli_close($link);
}
else if($action == 'get_device_attributes')
{
	$missingParams=missingParameters(array('id', 'contextbroker'));

	if (empty($accessToken))
    {
                $result["status"]="ko";
                $result['msg'] = "Access Token not present";
                $result["error_msg"] .= "Problem getting all the device attr information (Access Token not present). ";
                $result["log"]= "action=get_device_attributes - error AccessToken not present\r\n";
	}
	else if (!empty($missingParams))
	{
                $result["status"]="ko";
                $result['msg'] = "Missing Parameters";
                $result["error_msg"] .= "Problem getting all the device attr information (Missing parameters: ".implode(", ",$missingParams)." )";
                $result["log"]= "action=get_device_attributes - error Missing Parameters: ".implode(", ",$missingParams)." \r\n";
	}
	else
	{
		 //retrieve username, organization and role from the accetoken
        //TODO avoid passing all the parameters for LDAP
        get_user_info($accessToken, $username, $organization, $oidc, $role, $result, $ldapBaseName, $ldapServer, $ldapPort, $ldapAdminName, $ldapAdminPwd);

        if ($result["status"]!="ok")
        {
            $result["status"]="ko";
            $result['msg'] = "Cannot retrieve user information";
            $result["error_msg"] .= "Problem changing owner (Cannot retrieve user information)";
            $result["log"]= "action=change_owner - error Cannot retrieve user information\r\n";
        }
        else
        {

			$id = mysqli_real_escape_string($link, $_REQUEST['id']);
			$cb = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
			if (isset($_REQUEST['service'])) $service = mysqli_real_escape_string($link, $_REQUEST['service']);
			else $service = "";
			if (isset($_REQUEST['servicePath'])) $servicePath = mysqli_real_escape_string($link, $_REQUEST['servicePath']);
			else $servicePath="";

			$protocol = getProtocol($cb, $link);

			if (empty($protocol))//it also ensure the contextbroker name is valid
	        {
				$result["status"]="ko";
        	    $result['msg'] = "Unrecognized contextbroker/protocol";
	            $result["error_msg"] .= "Problem in get device attributes (Unrecognized contextbroker/protocol)";
    	        $result["log"]= "action=get_device_attributes - error Unrecognized contextbroker/protocol\r\n";
			}
			else
			{
				//id management
				if($protocol == "ngsi w/MultiService")	$id = $service . "." . $servicePath . "." . $id;
				get_device($username, $role, $id, $cb,  $accessToken, $link, $result);
				if (empty($result["content"]))
                {
                    $result["status"]="ko";
                    $result['msg'] = "Unrecognized device";
                    $result["error_msg"] .= "Problem in get_device_attribute (Unrecognized device)";
                    $result["log"]= "action=get_device_attributes - error Unrecognized device\r\n";
                }
                else
                {
				$dev_organization=$result["content"]["organization"];
				$eId=$dev_organization.":".$cb.":".$id;

				if (!enforcementRights($username, $accessToken, $role, $eId, 'IOTID','read', $result))
				{	
					$result["status"]="ko";
					$result['msg'] = "Not ownership or enough right to update";
					$result["error_msg"] .= "Problem in get device attributes (Not ownership or enough right to update)";
					$result["log"]= "action=get_device_attributes - error Not ownership or enough right to update\r\n";
				}
				else
				{
					$q1 = "SELECT * FROM event_values WHERE cb = '$cb' AND device = '$id'";
					$r1 = mysqli_query($link, $q1);
					$attributes = array();
					if($r1)
					{
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
						$result['msg'] = 'Error: errors in reading data about devices. <br/>' .  generateErrorMessage($link);
						$result['log'] .= '\n\naction:get_device_attributes. Error: errors in reading data about devices. ' . generateErrorMessage($link);				  
					}
				}
				}
    		}
		}
	}
	my_log($result);
	mysqli_close($link);
}	
else if($action == 'get_param_values')
{	
	$newresult=array("status"=>"","msg"=>"","content"=>"","log"=>"", "error_msg"=>"");
	$newresult['status'] = 'ok'; 

	$newresult['data_type'] = generatedatatypes($link);
	mysqli_close($link);

	retrieveFromDictionary("value%20type", $result);
	if ($result["status"]=='ok'){
		$newresult['value_type'] = $result["content"];
		retrieveFromDictionary("value%20unit", $result);
		if ($result["status"]=='ok'){
			$newresult['value_unit'] = $result["content"];
			retrieveFromDictionary("subnature", $result);
			if ($result["status"]=='ok'){
	                        $newresult['subnature'] = $result["content"];
			}
			else{
        	                $newresult['status'] = 'ko';
	                        $newresult['error_msg'] = 'Problem contacting the Snap4City server (Dictionary). Please try later';
                        	$newresult['log'] .= '\n Problem contacting the Snap4City server (Dictionary subnature)';
                	}
		}
		else{
			$newresult['status'] = 'ko';
			$newresult['error_msg'] = 'Problem contacting the Snap4City server (Dictionary). Please try later';
			$newresult['log'] .= '\n Problem contacting the Snap4City server (Dictionary value unit)';
		}
	}
	else {
		$newresult['status'] = 'ko';
		$newresult['error_msg'] = 'Problem contacting the Snap4City server (Dictionary). Please try later';
		$newresult['log'] .= '\n Problem contacting the Snap4City server (Dictionary value type)';
	}
	
	$newresult['log'] .= '\n\naction:get_param_values';
	my_log($newresult);
}
else if ($action == 'get_available_static')
{
	$missingParams=missingParameters(array('subnature'));

	$newresult=array("status"=>"","msg"=>"","content"=>"","log"=>"", "error_msg"=>"");
        $newresult['status'] = 'ok';

	if (!empty($missingParams))
        {
                $newresult["status"]="ko";
                $newresult['msg'] = "Missing Parameters";
                $newresult["error_msg"] .= "Problem getting available static (Missing parameters: ".implode(", ",$missingParams)." )";
                $newresult["log"]= "action=get_available_static - error Missing Parameters: ".implode(", ",$missingParams)." \r\n";
        }
	else {
		$subnature = mysqli_real_escape_string($link, $_REQUEST['subnature']);

		retrieveAvailableStaticAttribute($subnature, $result);

		if ($result["status"]=='ok')
		{
			$newresult['availibility']=$result["content"];
                	$newresult['log'] .= "\n Returning ".$result["content"];
		}
		else {
			$newresult['status'] = 'ko';
                        $newresult['error_msg'] = 'Problem contacting the Snap4City server (Service map list-static-attr). Please try later';
                        $newresult['log'] .= '\n Problem contacting the Snap4City server (Service map list-static-attr)';
		}
	
		$newresult['log'] .= '\n\naction:get_available_static';
	}
	my_log($newresult);
}
//NEVER USED FROM FRONTEND... MAYBE SOME APIs ARE STILL USING?
//LONG TIME NOT MANTAINED (NO DELEGATION INVOLVED)... COMMENTING OUT
/*else if ($action == 'get_device'){
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
}*/
else if (($action == "get_device_simple")||($action == "get_device"))
{
	$missingParams=missingParameters(array('id', 'contextbroker'));

	if (empty($accessToken))
    {
                $result["status"]="ko";
                $result['msg'] = "Access Token not present";
                $result["error_msg"] .= "Problem getting the device information (Access Token not present). ";
                $result["log"]= "action=get_device - error AccessToken not present\r\n";
    }
	else if (!empty($missingParams))
    {
                $result["status"]="ko";
                $result['msg'] = "Missing Parameters";
                $result["error_msg"] .= "Problem getting device information (Missing parameters: ".implode(", ",$missingParams)." )";
                $result["log"]= "action=get_device - error Missing Parameters: ".implode(", ",$missingParams)." \r\n";
    }
    else 
	{
        //retrieve any parameteres from the accetoken
        //TODO avoid passing all the parameters for LDAP
        get_user_info($accessToken, $username, $organization, $oidc, $role, $result, $ldapBaseName, $ldapServer, $ldapPort, $ldapAdminName, $ldapAdminPwd);

        if ($result["status"]=="ok")
		{
			$id = mysqli_real_escape_string($link, $_REQUEST['id']);
        	$cb = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
			if (isset($_REQUEST['service'])) $service = mysqli_real_escape_string($link, $_REQUEST['service']);
            else $service = "";
            if (isset($_REQUEST['servicePath'])) $servicePath = mysqli_real_escape_string($link, $_REQUEST['servicePath']);
            else $servicePath="";

			$protocol = getProtocol($cb, $link);

            if (empty($protocol))//it also ensure the contextbroker name is valid
            {
                $result["status"]="ko";
                $result['msg'] = "Unrecognized contextbroker/protocol";
                $result["error_msg"] .= "Problem getting device information (Unrecognized contextbroker/protocol)";
                $result["log"]= "action=get_device - error Unrecognized contextbroker/protocol\r\n";
            }
            else
            {
				//id management
				if($protocol == "ngsi w/MultiService")  $id = $service . "." . $servicePath . "." . $id;
				get_device($username, $role, $id, $cb,  $accessToken, $link, $result);
				if (empty($result["content"]))
                {
                    $result["status"]="ko";
                    $result['msg'] = "Unrecognized device";
                    $result["error_msg"] .= "Problem in getting device information  (Unrecognized device)";
                    $result["log"]= "action=get_device - error Unrecognized device\r\n";
                }
                else
                {
					$dev_organization=$result["content"]["organization"];
					$eId=$dev_organization.":".$cb.":".$id;

					if (!enforcementRights($username, $accessToken, $role, $eId, 'IOTID','read', $result))
					{
						$result["status"]="ko";
						$result['msg'] = "Not ownership or enough right to update";
						$result["error_msg"] .= "Problem in getting device information (Not ownership or enough right to update)";
						$result["log"]= "action=get_device - error Not ownership or enough right to update\r\n";
					}
					else
					{								
			            get_device($username, $role, $id, $cb, $accessToken, $link, $result);
					}
				}
			}
		}
	}
    my_log($result);
}
else if ($action == "get_all_device")
{
	if (empty($accessToken))
	{
		$result["status"]="ko";
		$result['msg'] = "Access Token not present";
		$result["error_msg"] .= "Problem getting all the device information (Access Token not present). ";
		$result["log"]= "action=get_all_device - error AccessToken not present\r\n";
		my_log($result);
	}
	else
	{
		//retrieve username, organization and role from the accetoken
		//TODO avoid passing all the parameters for LDAP
		get_user_info($accessToken, $username, $organization, $oidc, $loggedrole, $result, $ldapBaseName, $ldapServer, $ldapPort, $ldapAdminName, $ldapAdminPwd);
		if ($result["status"]!="ok")
		{
			$result["status"]="ko";
			$result['msg'] = "Cannot retrieve user information";
			$result["error_msg"] .= "Problem getting all the device information (Cannot retrieve user information)";
			$result["log"]= "action=get_all_device - error Cannot retrieve user information\r\n";
			my_log($result);
		}
        else
       	{
			if (isset($_REQUEST['length']))	$length = mysqli_real_escape_string($link, $_REQUEST['length']);
			else $length=-1;
			$start=1;//default is 1 but should throw an error
			if (($length!=-1)&& (isset($_REQUEST['start']))) $start= mysqli_real_escape_string($link, $_REQUEST['start']);
			if (isset($_REQUEST['draw'])) $draw = mysqli_real_escape_string($link, $_REQUEST['draw']);
			else $draw=1;
			if (!isset($_REQUEST['columns'])) $_REQUEST["columns"]=array();
			if (isset($_REQUEST['select'])) $selection=json_decode($_REQUEST['select']);
			else $selection= array();

			$ownDevices= getOwnerShipDevice($accessToken, $result); 
			getDelegatedDevice($accessToken, $username, $result);
	
			$q = "SELECT d.`contextBroker`, d.`id`, d.`uri`, d.`devicetype`, d.`kind`,
				CASE WHEN mandatoryproperties AND mandatoryvalues THEN \"active\" ELSE \"idle\" END AS status1, 
				d.`macaddress`, d.`model`, d.`producer`, d.`longitude`, d.`latitude`, d.`protocol`, d.`format`, d.`visibility`, 
				d.`frequency`, d.`created`, d.`privatekey`, d.`certificate`,d.`organization`, cb.`accesslink`, cb.`accessport`, 
				cb.`sha`, d.`subnature`, d.`static_attributes`,d.`service`, d.`servicePath` FROM `devices` d JOIN `contextbroker` cb ON (d.contextBroker=cb.name)"; 

			if (count($selection)!=0)
			{
				$a=0;
				$cond="";	    
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
				$r=create_datatable_data($link,$_REQUEST,$q, "deleted IS null");
			}
    
			$selectedrows=-1;
			if($length!= -1)
			{
				$offset=$length;
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
					if ( (	($row["organization"]==$organization) &&
						(   
						($row["visibility"]=='public'	|| (isset($result["delegation"][$eid])&& $result["delegation"][$eid]["kind"]=="anonymous")
		                        )
                			    )
			                ) 
			                || (isset($result["delegation"][$eid])&& $result["delegation"][$eid]["kind"]!="anonymous")
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
							$rec["subnature"]=($row["subnature"]==null)?"":$row["subnature"];
							$rec["staticAttributes"]=($row["static_attributes"]==null)?"[]":$row["static_attributes"];
							$rec["service"] = $row["service"];
							$rec["servicePath"] = $row["servicePath"];	
					        $rec["accesslink"]= $row["accesslink"];
							$rec["accessport"]= $row["accessport"];
							$rec["sha"]= $row["sha"];
							$rec["privatekey"]= "";
							$rec["certificate"]= "";
							$rec["edgegateway_type"]= "";
							$rec["edgegateway_uri"]= "";
					
							if ($row["protocol"] == "ngsi w/MultiService")
							{
								// get the name from id
								$rec["id"] = explode(".", $row["id"])[2];
							}	
					
							if (((isset($result["keys"][$eid]))&&($loggedrole!=='RootAdmin'))
								||
								((isset($result["keys"][$eid]))&& ($result["keys"][$eid]["owner"]==$username) && ($loggedrole==='RootAdmin')))
							{
								//it's mine or RootAdmin
								if ($row["visibility"]=="public")
								{
									$rec["visibility"]= "MyOwnPublic";
								}
								else
								{
									if (isset($result["delegation"][$row["uri"]]) && $result["delegation"][$row["uri"]]["kind"]=="anonymous")
										$rec["visibility"]= "MyOwnPublic";
									else
										$rec["visibility"]="MyOwnPrivate";
								}
	
								$rec["k1"]=$result["keys"][$eid]["k1"];
								$rec["k2"]=$result["keys"][$eid]["k2"];
								$rec["edgegateway_type"]= $result["keys"][$eid]["edgegateway_type"];
								$rec["edgegateway_uri"]= $result["keys"][$eid]["edgegateway_uri"];
								$rec["privatekey"]= $row["privatekey"];
								$rec["certificate"]= $row["certificate"]; 
	                    	}
	                    	else
							{
								//it's not mine
								if (isset($result["delegation"][$eid]) && ($result["delegation"][$eid]["kind"]=="anonymous"))
								{
									//it's delegated as public
									$rec["visibility"]='public';
									$rec["k1"]="";
									$rec["k2"]="";
								}
								else if (isset($result["delegation"][$eid]))
								{
									//it's delegated personally
									$rec["visibility"]='delegated';
									$rec["k1"]="";
									$rec["k2"]="";
									if (isset($result["delegation"][$eid]["k1"]))
									{
										$rec["k1"]= $result["delegation"][$eid]["k1"]; // to be fixed
										$rec["k2"]= $result["delegation"][$eid]["k2"]; // to be fixed
									}
								}
								else 
								{
									$rec["visibility"]= $row["visibility"];
									$rec["k1"]="";
									$rec["k2"]="";
								}
							}			
							array_push($data, $rec);           
						}
					}	
				}
				$output= format_result($draw, $selectedrows+1, $selectedrows+1, $data, "", "\r\n action=get_all_device \r\n", 'ok');
				logAction($link,$username,'device','get_all_device','',$organization,'','success');
			}
			else
			{
				logAction($link,$username,'device','get_all_device','',$organization,'Error: errors in reading data about devices.','faliure');
				$output= format_result($_REQUEST["draw"], 0, 0, $data, 'Error: errors in reading data about devices. <br/>' . generateErrorMessage($link), '\n\r Error: errors in reading data about devices.' . generateErrorMessage($link), 'ko');
		  	}   
		}
		my_log($output);
	}
	mysqli_close($link);
}
//TODO can be unified with get_all_device
//assurance on ADMIN is guarranteed by getOwnerShipDevice
else if($action == "get_all_device_admin")
{
	if (empty($accessToken))
	{
		$result["status"]="ko";
		$result['msg'] = "Access Token not present";
		$result["error_msg"] .= "Problem getting all the device admin information (Access Token not present). ";
		$result["log"]= "action=get_all_device_admin - error AccessToken not present\r\n";
	}
	else
	{
		//retrieve username, organization and role from the accetoken
		//TODO avoid passing all the parameters for LDAP
		get_user_info($accessToken, $username, $organization, $oidc, $loggedrole, $result, $ldapBaseName, $ldapServer, $ldapPort, $ldapAdminName, $ldapAdminPwd);
		if ($result["status"]!="ok")
		{
			$result["status"]="ko";
			$result['msg'] = "Cannot retrieve user information";
			$result["error_msg"] .= "Problem getting all the device admin information (Cannot retrieve user information)";
			$result["log"]= "action=get_all_device_admin - error Cannot retrieve user information\r\n";
		}
		else
		{

			if (isset($_REQUEST['select'])) $selection=json_decode($_REQUEST['select']);
			else $selection= array();

			$ownDevices= getOwnerShipDevice($accessToken, $result); 
	
			$q = "SELECT d.`contextBroker`, d.`id`, d.`uri`, d.`devicetype`, d.`kind`, 
	    	  CASE WHEN mandatoryproperties AND mandatoryvalues THEN \"active\" ELSE \"idle\" END AS status1, 
		      d.`macaddress`, d.`model`, d.`producer`, d.`longitude`, d.`latitude`, d.`protocol`, d.`format`, d.`visibility`, d.`organization`,
		      d.`frequency`, d.`created`, d.`privatekey`, d.`certificate`, cb.`accesslink`,  cb.`accessport`,cb.`sha`, d.`subnature`, d.`static_attributes`, d.`service`, d.`servicePath` 
			  FROM `devices` d JOIN `contextbroker` cb ON (d.contextBroker=cb.name) "; //  WHERE visibility =\"public\"";

			if (count($selection)!=0)
			{
				$a=0;
				$cond="";
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
				$r=create_datatable_data($link,$_REQUEST,$q, "deleted IS null");
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
						$rec["subnature"]=($row["subnature"]==null)?"":$row["subnature"];
        		        $rec["staticAttributes"]=($row["static_attributes"]==null)?"[]":$row["static_attributes"];
						$rec["service"] = $row["service"];
						$rec["servicePath"] = $row["servicePath"];
		
						if ($row["protocol"] == "ngsi w/MultiService")
						{
							$rec["id"] = explode(".", $row["id"])[2];
						}
		
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
			 	$output= format_result($_REQUEST["draw"], $selectedrows+1, $selectedrows+1, $data, "", "\r\n action=get_all_device_admin \r\n", 'ok');
		    }
			else
			{
				logAction($link,$username,'device','get_all_device_admin','',$organization,'','faliure');	 
		        $output= format_result($_REQUEST["draw"], 0, 0, null, 'Error: errors in reading data about devices. <br/>' . generateErrorMessage($link), '\n\r Error: errors in reading data about devices.' . generateErrorMessage($link), 'ko');
			}    
		}
	}
	my_log($output);
	mysqli_close($link);
}/* never used? removed temporarly
else if($action == "get_all_private_device")
{
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
	     d.`frequency`, d.`created`, d.`privatekey`, d.`certificate`, cb.`accesslink`, cb.`accessport`,cb.`sha`,  d.`subnature`, d.`static_attributes` , d.`service`, d.`servicePath` 
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
			$rec["subnature"]=($row["subnature"]==null)?"":$row["subnature"];
                        $rec["staticAttributes"]=($row["static_attributes"]==null)?"[]":$row["static_attributes"];
			$rec["service"] = $row["service"];
                        $rec["servicePath"] = $row["servicePath"];

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
}*/
else if($action == "get_all_device_latlong")
{
	if (empty($accessToken))
	{
		$result["status"]="ko";
		$result['msg'] = "Access Token not present";
		$result["error_msg"] .= "Problem getting all the device information lat long(Access Token not present). ";
		$result["log"]= "action=get_all_device_latlong - error AccessToken not present\r\n";
	}
	else
	{
		//retrieve username, organization and role from the accetoken
		//TODO avoid passing all the parameters for LDAP
		get_user_info($accessToken, $username, $organization, $oidc, $loggedrole, $result, $ldapBaseName, $ldapServer, $ldapPort, $ldapAdminName, $ldapAdminPwd);
		if ($result["status"]!="ok")
		{
			$result["status"]="ko";
			$result['msg'] = "Cannot retrieve user information";
            $result["error_msg"] .= "Problem getting all the device information lat long (Cannot retrieve user information)";
            $result["log"]= "action=get_all_device_latlong - error Cannot retrieve user information\r\n";
        }
        else
        {
 
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
			else
			{
				$result['status'] = 'ko';
				$result['msg'] = 'Error: errors in reading data about devices. <br/>' .
						  generateErrorMessage($link);
				$result['msg'] = '\r\n action=get_all_device_latlong -- Error: errors in reading data about devices.' . $q .
						  generateErrorMessage($link);
			}    
		}
	}
	my_log($result);
	mysqli_close($link);
}
/* NEVEER USED
else if ($action=="exist_device")
{
	$missingParams=missingParameters(array('id', 'contextbroker');

	if (!empty($missingParams))
        {
                $result["status"]="ko";
                $result['msg'] = "Missing Parameters";
                $result["error_msg"] .= "Problem in exist device (Missing parameters: ".implode(", ",$missingParams)." )";
                $result["log"]= "action=exist_device  - error Missing Parameters: ".implode(", ",$missingParams)." \r\n";
        }
        else
        {
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
		else
		{
			$result['status'] = 'ko';
			$result['msg'] = 'The following errors occurred:' . generateErrorMessage($link);
			$result["log"]= "\r\n action=exist_device " . $q1 . 'The following errors occurred:' . generateErrorMessage($link);				   
		}    
	}
	my_log($result);
	mysqli_close($link);
}
*/
//NEVER USED FROM FRONTEND... MAYBE SOME APIs ARE STILL USING?
//LONG TIME NOT MANTAINED (NO DELEGATION INVOLVED)... COMMENTING OUT
/*else if($action == "get_device_with_attributes")
{ // Elf: this function is NOT to be included in the API 
    //Fatima: since it's not included, I will not update anyway
     
	$q = "SELECT d.id, d.devicetype, d.protocol, d.format,
       d.latitude AS dlat, d.longitude AS dlong, c.name,  c.ip, c.port, c.latitude, c.longitude FROM
	   devices d JOIN contextbroker c ON (d.contextbroker=c.name) WHERE deleted is null and mandatoryproperties=1 and mandatoryvalues=1 order by d.id";
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
	mysqli_close($link);
	}*/
else if ($action == 'download')
{
	$missingParams=missingParameters(array('id', 'contextbroker', 'filename'));

	if (empty($accessToken))
	{
		$result["status"]="ko";
		$result['msg'] = "Access Token not present";
		$result["error_msg"] .= "Problem in download (Access Token not present)";
		$result["log"]= "action=download - error AccessToken not present\r\n";
	}
	else if (!empty($missingParams))
	{
		$result["status"]="ko";
		$result['msg'] = "Missing Parameters";
		$result["error_msg"] .= "Problem in download (Missing parameters: ".implode(", ",$missingParams)." )";
		$result["log"]= "action=download - error Missing Parameters: ".implode(", ",$missingParams)." \r\n";
	}
	else
	{
		//retrieve username, organization and role from the accetoken
        //TODO avoid passing all the parameters for LDAP
        get_user_info($accessToken, $currentUser, $organization, $oidc, $role, $result, $ldapBaseName, $ldapServer, $ldapPort, $ldapAdminName, $ldapAdminPwd);

        if ($result["status"]!="ok")
        {
        	$result["status"]="ko";
            $result['msg'] = "Cannot retrieve user information";
            $result["error_msg"] .= "Problem in download (Cannot retrieve user information)";
            $result["log"]= "action=download - error Cannot retrieve user information\r\n";
		}
        else
        {
			$result["log"]="\r\n download invoked\n";
			
			$id = mysqli_real_escape_string($link, $_REQUEST['id']);
			$contextbroker = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
			$filename = mysqli_real_escape_string($link, $_REQUEST['filename']);


			$protocol = getProtocol($contextbroker, $link);
	
			if (empty($protocol))//it also ensure the contextbroker name is valid
            {
                $result["status"]="ko";
                $result['msg'] = "Unrecognized contextbroker/protocol";
                $result["error_msg"] .= "Problem in download (Unrecognized contextbroker/protocol)";
                $result["log"]= "action=download - error Unrecognized contextbroker/protocol\r\n";
            }
            else
            {
				//id management
				if($protocol == "ngsi w/MultiService")  $id = $service . "." . $servicePath . "." . $id;
				get_device($currentUser, $role, $id, $contextbroker,  $accessToken, $link, $result);
				if (empty($result["content"]))
                {
                    $result["status"]="ko";
                    $result['msg'] = "Unrecognized device";
                    $result["error_msg"] .= "Problem in download (Unrecognized device)";
                    $result["log"]= "action=download - error Unrecognized device\r\n";
                }
                else
                {
				$dev_organization=$result["content"]["organization"];
				$eId=$dev_organization.":".$contextbroker.":".$id;

				if (!enforcementRights($username, $accessToken, $role, $eId, 'IOTID','read', $result))
				{	
					$result["status"]="ko";
					$result['msg'] = "Not ownership or enough right to update";
					$result["error_msg"] .= "Problem in download (Not ownership or enough right to update)";
					$result["log"]= "action=download - error Not ownership or enough right to update\r\n";
				}
				else 
				{
					//TODO support Multi-tenancy scenario
					//TODO remove this check... this is already enforced above
					$url= $GLOBALS["ownershipURI"] . "ownership-api/v1/list/?type=IOTID&accessToken=" . $accessToken . "&elementId=".$eId;
					$local_result = file_get_contents($url);
					if(strpos($http_response_header[0], '200') === false)
		            {
						$result["status"]='ko';
						$result["msg"] .= "\n error in acceding the ownership";
						$result["log"] .= "\n error in acceding the ownership";
					}
					else
					{
						if (strpos($filename, $id) === false)
						{
							logAction($link,$currentUser,'devices','download',$id . " ". $filename . " filename was tempered",$organization,'','faliure');
											
							$result["status"]='ko';
							$result["msg"] .= "\n the filename was tempered";
							$result["log"] .= "\n the filename was tempered";
						}
						else
						{
							if (strpos($local_result, $id) === false)
							{		
								logAction($link,$currentUser,'devices','download',$id . " ". $filename. " you don't own the requested device",$organization,'',success);
								$result["status"]='ko';
								$result["msg"] .= "\n you don't own the requested device";
								$result["log"] .= "\n you don't own the requested device";
							}
							else
							{
								$result['status'] = 'ok';
								$result['msg'] = file_get_contents($pathCertificate.$filename);
								$result['log']='\r\n download success';
								logAction($link,$currentUser,'devices','download',$id . " ". $filename,$organization,'','success');
      			        	}
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
else if($action == "get_delegations")
{
	$missingParams=missingParameters(array('id', 'contextbroker'));

	if (empty($accessToken))
    {
		$result["status"]="ko";
        $result['msg'] = "Access Token not present";
        $result["error_msg"] .= "Problem in get delegations (Access Token not present)";
        $result["log"]= "action=get_delegations - error AccessToken not present\r\n";
	}
	else if (!empty($missingParams))
    {
		$result["status"]="ko";
		$result['msg'] = "Missing Parameters";
        $result["error_msg"] .= "Problem in get delegations (Missing parameters: ".implode(", ",$missingParams)." )";
        $result["log"]= "action=get_delegations - error Missing Parameters: ".implode(", ",$missingParams)." \r\n";
	}
    else
    {
		//retrieve username, organization and role from the accetoken
		//TODO avoid passing all the parameters for LDAP
        get_user_info($accessToken, $user, $organization, $oidc, $role, $result, $ldapBaseName, $ldapServer, $ldapPort, $ldapAdminName, $ldapAdminPwd);

		if ($result["status"]!="ok")
        {
        	$result["status"]="ko";
            $result['msg'] = "Cannot retrieve user information";
            $result["error_msg"] .= "Problem in get delegations (Cannot retrieve user information)";
            $result["log"]= "action=get_delegations - error Cannot retrieve user information\r\n";
		}
        else
        {
			//$uri =  mysqli_real_escape_string($link, $_REQUEST['uri']);
			$cb = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
			//$dev_organization = mysqli_real_escape_string($link, $_REQUEST['dev_organization']);
			$id = mysqli_real_escape_string($link, $_REQUEST['id']);
			if (isset($_REQUEST['service'])) $service = mysqli_real_escape_string($link, $_REQUEST['service']);
            else $service ="";
            if (isset($_REQUEST['servicePath'])) $servicePath = mysqli_real_escape_string($link, $_REQUEST['servicePath']);
            else $servicePath ="";
	
			$protocol = getProtocol($cb, $link);

			if (empty($protocol))//it also ensure the contextbroker name is valid
			{
				$result["status"]="ko";
				$result['msg'] = "Unrecognized contextbroker/protocol";
				$result["error_msg"] .= "Problem in retreive delegations (Unrecognized contextbroker/protocol)";
				$result["log"]= "action=get_delegations - error Unrecognized contextbroker/protocol\r\n";
			}
			else
			{
				//id management
				if ($protocol == "ngsi w/MultiService")	$id = $service . "." . $servicePath . "." . $id;
				get_device($user, $role, $id, $cb,  $accessToken, $link, $result);
				if (empty($result["content"]))
                {
                    $result["status"]="ko";
                    $result['msg'] = "Unrecognized device";
                    $result["error_msg"] .= "Problem in retrieve delegation (Unrecognized device)";
                    $result["log"]= "action=get_delegations - error Unrecognized device\r\n";
                }
                else
                {
					$dev_organization=$result["content"]["organization"];
					$eId=$dev_organization.":".$cb.":".$id;			
				
					if (!enforcementRights($username, $accessToken, $role, $eId, 'IOTID','read', $result))
					{
						$result["status"]="ko";
						$result['msg'] = "Not ownership or enough right to update";
						$result["error_msg"] .= "Problem in retrieve delegations (Not ownership or enough right to update)";
						$result["log"]= "action=get_delegations - error Not ownership or enough right to update\r\n";
					}
					else
					{ 
						getDelegatorDevice($accessToken, $user, $result, $eId);
					}
				}
			}
		}
	}
	my_log($result);
	mysqli_close($link);
}
//TODO in case of RootAdmin, use the username of the owner of the device
else if($action == "add_delegation")
{
	$missingParams=missingParameters(array('id', 'contextbroker', 'k1', 'k2'));

	if (empty($accessToken))
    {
    	$result["status"]="ko";
        $result['msg'] = "Access Token not present";
        $result["error_msg"] .= "Problem in add delegation (Access Token not present)";
        $result["log"]= "action=add_delegation - error AccessToken not present\r\n";
	}
    else if (!empty($missingParams))
    {
		$result["status"]="ko";
        $result['msg'] = "Missing Parameters";
		$result["error_msg"] .= "Problem in add delegation (Missing parameters: ".implode(", ",$missingParams)." )";
		$result["log"]= "action=add_delegation - error Missing Parameters: ".implode(", ",$missingParams)." \r\n";
	}
    else
    {
		//retrieve username, organization and role from the accetoken
		//TODO avoid passing all the parameters for LDAP
		get_user_info($accessToken, $user, $organization, $oidc, $role, $result, $ldapBaseName, $ldapServer, $ldapPort, $ldapAdminName, $ldapAdminPwd);

        if ($result["status"]!="ok")
        {
			$result["status"]="ko";
			$result['msg'] = "Cannot retrieve user information";
            $result["error_msg"] .= "Problem in add delegation (Cannot retrieve user information)";
            $result["log"]= "action=add_delegation - error Cannot retrieve user information\r\n";
		}
        else
        {
			$cb = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
			//$dev_organization = mysqli_real_escape_string($link, $_REQUEST['dev_organization']);
			$id = mysqli_real_escape_string($link, $_REQUEST['id']);
			//$uri =  mysqli_real_escape_string($link, $_REQUEST['uri']);
			$delegated_user = (isset($_REQUEST['delegated_user']))?mysqli_real_escape_string($link, $_REQUEST['delegated_user']):"";
			$delegated_group= (isset($_REQUEST['delegated_group']))?mysqli_real_escape_string($link, $_REQUEST['delegated_group']):"";
			$k1 = mysqli_real_escape_string($link, $_REQUEST['k1']);
			$k2 = mysqli_real_escape_string($link, $_REQUEST['k2']);
			if (isset($_REQUEST['service'])) $service = mysqli_real_escape_string($link, $_REQUEST['service']);
            else $service ="";
            if (isset($_REQUEST['servicePath'])) $servicePath = mysqli_real_escape_string($link, $_REQUEST['servicePath']);
            else $servicePath ="";

			$protocol = getProtocol($cb, $link);

			if (empty($protocol))//it also ensure the contextbroker name is valid
			{
				$result["status"]="ko";
				$result['msg'] = "Unrecognized contextbroker/protocol";
				$result["error_msg"] .= "Problem in adding delegations (Unrecognized contextbroker/protocol)";
				$result["log"]= "action=add_delegations - error Unrecognized contextbroker/protocol\r\n";
			}
			else
			{
				//id management
				if ($protocol == "ngsi w/MultiService")	$id = $service . "." . $servicePath . "." . $id;
				get_device($user, $role, $id, $cb,  $accessToken, $link, $result);
				if (empty($result["content"]))
                {
                    $result["status"]="ko";
                    $result['msg'] = "Unrecognized device";
                    $result["error_msg"] .= "Problem in adding delegations (Unrecognized device)";
                    $result["log"]= "action=add_delegations - error Unrecognized device\r\n";
                }
                else
                {
				$dev_organization=$result["content"]["organization"];
				$uri=$result["content"]["uri"];
				$eId=$dev_organization.":".$cb.":".$id;

				if (!enforcementRights($username, $accessToken, $role, $eId, 'IOTID','write', $result))
				{
					$result["status"]="ko";
					$result['msg'] = "Not ownership or enough right to update";
					$result["error_msg"] .= "Problem in adding delegation (Not ownership or enough right to update)";
					$result["log"]= "action=add_delegations - error Not ownership or enough right to update\r\n";
				}
				else
				{
					if (($delegated_user != "" || $delegated_group != "") && $user != "")
					{
						//retrieve any values of this this device
			        	$q1 = "SELECT * FROM event_values WHERE cb = '$cb' and device='$id'";
	        			$values = mysqli_query($link, $q1);
	
						//delegate any values of this device
						while($value = mysqli_fetch_assoc($values))
						{
							delegateDeviceValue($uri ."/" . $value["value_name"], $cb, $value["value_name"], $user, $delegated_user, $delegated_group, $accessToken, $k1, $k2, $result);
						}

						//delegate the device
    	    	        delegateDeviceValue($eId, $cb, NULL, $user, $delegated_user, $delegated_group, $accessToken, $k1, $k2, $result);
					}
		    	    else
	    	    	{
						$result["status"]='ko';
						$result["msg"]='\n the function delegate_value has been called without specifying mandatory parameters';
						$result["log"]='\n the function delegate_value has been called without specifying mandatory parameters';
			        }
				}
				}
			}
		}
	}
    my_log($result);
	mysqli_close($link);
}
else if($action == "remove_delegation")
{
	$missingParams=missingParameters(array('id', 'contextbroker', 'delegationId'));

	if (empty($accessToken))
    {
		$result["status"]="ko";
        $result['msg'] = "Access Token not present";
        $result["error_msg"] .= "Problem in remove delegation (Access Token not present)";
        $result["log"]= "action=remove_delegation - error AccessToken not present\r\n";
	}
    else if (!empty($missingParams))
    {
		$result["status"]="ko";
		$result['msg'] = "Missing Parameters";
		$result["error_msg"] .= "Problem in remove delegation (Missing parameters: ".implode(", ",$missingParams)." )";
		$result["log"]= "action=remove_delegation - error Missing Parameters: ".implode(", ",$missingParams)." \r\n";
	}
	else
	{
		//retrieve username, organization and role from the accetoken
		//TODO avoid passing all the parameters for LDAP
		get_user_info($accessToken, $user, $organization, $oidc, $role, $result, $ldapBaseName, $ldapServer, $ldapPort, $ldapAdminName, $ldapAdminPwd);

		if ($result["status"]!="ok")
		{
			$result["status"]="ko";
			$result['msg'] = "Cannot retrieve user information";
			$result["error_msg"] .= "Problem in remove delegation (Cannot retrieve user information)";
			$result["log"]= "action=remove_delegation - error Cannot retrieve user information\r\n";
		}
		else
		{
			$delegationId = mysqli_real_escape_string($link, $_REQUEST['delegationId']);
			//$uri = mysqli_real_escape_string($link, $_REQUEST['uri']);
			if (isset($_REQUEST['userDelegated'])) $userDelegated = mysqli_real_escape_string($link, $_REQUEST['userDelegated']);
			if (isset($_REQUEST['groupDelegated']))
			{
				$provgroupDelegated = mysqli_real_escape_string($link, $_REQUEST['groupDelegated']);
				$indexcomma=strrpos($provgroupDelegated, "All groups");
				if ($indexcomma==false)
				{
		                        $indexcomma=strrpos($provgroupDelegated, ",");
                		        $groupDelegated="cn=".substr($provgroupDelegated, $indexcomma+1).",ou=".substr($provgroupDelegated, 0, $indexcomma).",".$GLOBALS["ldapBaseName"];
				}
				else 
				{
        		                $groupDelegated="ou=".substr($provgroupDelegated, 0,$indexcomma-1).",".$GLOBALS["ldapBaseName"];
				}
			}
			$cb = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
			$id = mysqli_real_escape_string($link, $_REQUEST['id']);
			if (isset($_REQUEST['service'])) $service = mysqli_real_escape_string($link, $_REQUEST['service']);
            else $service ="";
            if (isset($_REQUEST['servicePath'])) $servicePath = mysqli_real_escape_string($link, $_REQUEST['servicePath']);
            else $servicePath ="";		

			$protocol = getProtocol($cb, $link);

			if (empty($protocol))//it also ensure the contextbroker name is valid
			{
				$result["status"]="ko";
				$result['msg'] = "Unrecognized contextbroker/protocol";
				$result["error_msg"] .= "Problem in remove delegations (Unrecognized contextbroker/protocol)";
				$result["log"]= "action=remove_delegations - error Unrecognized contextbroker/protocol\r\n";
			}
			else
			{
				//id management
				if ($protocol == "ngsi w/MultiService") $id = $service . "." . $servicePath . "." . $id;
				get_device($user, $role, $id, $cb,  $accessToken, $link, $result);
				if (empty($result["content"]))
                {
                    $result["status"]="ko";
                    $result['msg'] = "Unrecognized device";
                    $result["error_msg"] .= "Problem in remove_delegations (Unrecognized device)";
                    $result["log"]= "action=remove_delegations - error Unrecognized device\r\n";
                }
                else
                {
					$dev_organization=$result["content"]["organization"];
					$uri=$result["content"]["uri"];
					$eId=$dev_organization.":".$cb.":".$id;

					if (!enforcementRights($username, $accessToken, $role, $eId, 'IOTID','write', $result))
					{
						$result["status"]="ko";
						$result['msg'] = "Not ownership or enough right to update";
						$result["error_msg"] .= "Problem in remove delegations (Not ownership or enough right to update)";
						$result["log"]= "action=remove_delegations - error Not ownership or enough right to update\r\n";
					}
					else
					{
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
					}
				}
			}
		}
	}

	my_log($result);
	mysqli_close($link);
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
}*/
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
}*/
else 
{
	$result['status'] = 'ko';
	$result['msg'] = 'invalid action ' . $action;
	$result['log'] = 'invalid action ' . $action;
	my_log($result);
	mysqli_close($link);
}

function retrieveKbUrl($organizationApiURI, $org)
{
	//retrieve the kburl -> this is needed since this api can be called from OUTSIDE of the IoT directory
        $kburl="";
        if (!isset($_SESSION['kbUrl']))
        {
		$infokburl=get_organization_info($organizationApiURI, $org);
                if(!is_null($infokburl)){
                	$kburl=$infokburl["kbUrl"];
                }
        }
        else {
  	      $kburl=$_SESSION['kbUrl'];
        }
	return $kburl;
}

function compare_values($obj_a, $obj_b) {
  return  strcasecmp($obj_a->value_name,$obj_b->value_name);
}

