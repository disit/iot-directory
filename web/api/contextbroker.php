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
$link = mysqli_connect($host, $username, $password) or die("failed to connect to server !!");
mysqli_select_db($link, $dbname);

//Altrimenti restituisce in output le warning
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
    exit();
}

require '../sso/autoload.php';
use Jumbojett\OpenIDConnectClient;

$oidc = new OpenIDConnectClient($keycloakHostUri, $clientId, $clientSecret);

if (isset($_REQUEST['nodered']))
{
	if ($_REQUEST['token']!='undefined')
		$accessToken = $_REQUEST['token'];
	else $accessToken = "";
} 
else
{
	if (isset($_REQUEST['token'])) 
	{
		$oidc = new OpenIDConnectClient($keycloakHostUri, $clientId, $clientSecret);
		$oidc->providerConfigParam(array('token_endpoint' => $keycloakHostUri.'/auth/realms/master/protocol/openid-connect/token'));
		$tkn = $oidc->refreshToken($_REQUEST['token']);
		$accessToken = $tkn->access_token;
	}
	else $accessToken ="";
}

$result=array("status"=>"","msg"=>"","content"=>"","log"=>"", "error_msg"=>"");	
/* all the primitives return an array "result" with the following structure

result["status"] = ok/ko; reports the status of the operation (mandatory)
result["msg"] a message related to the execution of the operation (optional)
result["content"] in case of positive execution of the operation the content extracted from the db (optional)
result["log"] keep trace of the operations executed on the db

This array should be encoded in json
*/	
	
if ($action=="insert")
{   
	$name = mysqli_real_escape_string($link, $_REQUEST['name']);
	$kind = mysqli_real_escape_string($link, $_REQUEST['kind']);
	$ip = mysqli_real_escape_string($link, $_REQUEST['ip']);
	$port = mysqli_real_escape_string($link, $_REQUEST['port']);
	$protocol = mysqli_real_escape_string($link, $_REQUEST['protocol']);
	$version = mysqli_real_escape_string($link, $_REQUEST['version']);
	$latitude = mysqli_real_escape_string($link, $_REQUEST['latitude']);
	$longitude = mysqli_real_escape_string($link, $_REQUEST['longitude']);
	$login = mysqli_real_escape_string($link, $_REQUEST['login']);
	$password = mysqli_real_escape_string($link, $_REQUEST['password']);
	$accesslink = mysqli_real_escape_string($link, $_REQUEST['accesslink']);
	$accessport = mysqli_real_escape_string($link, $_REQUEST['accessport']);
	$path = mysqli_real_escape_string($link, $_REQUEST['path']);
	$visibility = mysqli_real_escape_string($link, $_REQUEST['visibility']);
	$sha = mysqli_real_escape_string($link, $_REQUEST['sha']);
	$urlnificallback = mysqli_real_escape_string($link, $_REQUEST['urlnificallback']);
	$organization = mysqli_real_escape_string($link, $_REQUEST['organization']);
	$username = mysqli_real_escape_string($link, $_REQUEST['username']);
	$services = json_decode($_REQUEST['services']);
	for($i = 0; $i < count($services); $i++){
		$services[$i] = mysqli_real_escape_string($link, $services[$i]);
	}
 
    if($accessToken!=""){   
	checkRegisterOwnerShipObject($accessToken, 'BrokerID',$result);
	if ($result["status"]=='ok'){ 

		// begin transaction
		mysqli_autocommit($link, FALSE);
		$success = TRUE;

		// queries execution
        	$q = "INSERT INTO contextbroker(name, ip, kind, protocol, version, port, latitude, longitude, login, password, accesslink, accessport, path, visibility, sha, organization, urlnificallback) " .
		"VALUES('$name', '$ip', '$kind', '$protocol', '$version', '$port', '$latitude', '$longitude', '$login', '$password', '$accesslink','$accessport', '$path', '$visibility', '$sha', '$organization', '$urlnificallback')";
		//error_log("1".$q);
		if (!mysqli_query($link, $q)) $success = FALSE;
		if ($protocol == 'ngsi w/MultiService' && count($services) > 0){

			// regex for syntax checking
			$serviceRegex = "/^([a-z]|_){1,25}$/";

			for($i = 0; $i < count($services); $i++){
				$service = $services[$i];

				// syntax checking
				if(!preg_match($serviceRegex, $service)){
					$success = FALSE;
				}
					
				$qs = "INSERT INTO services(name, broker_name) VALUES ('$service', '$name')";
				if(!mysqli_query($link, $qs)) $success = FALSE;
			}	
		}

		if ($success)
		{
			// successful transaction
			mysqli_commit($link);

			logAction($link,$username,'contextbroker','insert',$name,$organization,'insertion CB into database','success');

        		$result["status"]='ok';
			$result["log"].='\n\r action: insert ok. ' . $q;
			$ownmsg = array();
			$ownmsg["elementId"]=$organization . ':' . $name; // I am using the new identifier
			$ownmsg["elementName"]=$organization . ':' . $name;				    
			$ownmsg["elementUrl"]=$accesslink;
		        $ownmsg["elementType"]="BrokerID";
			
            		registerOwnerShipObject($ownmsg, $accessToken, 'BrokerID',$result);
		        if($result["status"]=='ok'){
                		logAction($link,$username,'contextbroker','insert',$name,$organization,'Registering the ownership of CB','success');

				//if internal and ngsi, try to subscribe
				if ($kind === "internal" && (strpos($protocol,'ngsi')!==false))
				{
					nificallback_create($ip, $port, $name, $urlnificallback, $protocol, $services, $result);//TODO uniform with below (update scenario), same code is there
					//save subscription_id
	        		        $q = "UPDATE contextbroker SET subscription_id='".$result["content"]."' WHERE name='$name';";
					error_log("update wuery:".$q);
			                $r = mysqli_query($link, $q);
        			        if($r)
		                	{
                		                logAction($link,$username,'contextbroker','insert',$name,$organization,'Subscribe URL NIFI CALLBACK','success');
                                		$result["status"]='ok';
		                                $result["log"].='\n\r action: subscribe ok. ' . $q;
	        		        }
		        	        else
                			{
                                		logAction($link,$username,'contextbroker','insert',$name,$organization,'Subscribe URL NIFI CALLBACK','failure');
		                                $result["status"]='ko';
                		                $result["error_msg"] = "Error occurred when registering the subscription" ;
                                		$result["log"] = "\n\r Error: An error occurred when subscription <br/>" .
		                                $result["msg"] = "Error: An error occurred when subscribe $name. <br/>" .
                	                                   mysqli_error($link) . ' Please enter again the context broker';
			                }
				}
				
			}
			else
			{
		                logAction($link,$username,'contextbroker','insert',$name,$organization,'Registering the ownership of CB','failure');
		        }
			
			// successful transaction ---- include in transaction nifi scenario
                        mysqli_commit($link);
		} 
		else 
		{
			// unsuccessful transaction
			mysqli_rollback($link);
		 	$result["status"]='ko';
			$result["error_msg"] = "Error occurred when registering the context broker $name. " ;
			$result["msg"] = "Error: An error occurred when registering the context broker $name. <br/>" .
						   mysqli_error($link) . 
						   ' Please enter again the context broker';
			$result["log"] = "\n\r Error: An error occurred when registering the context broker $name. <br/>" .
						   mysqli_error($link);	
			logAction($link,$username,'contextbroker','insert',$name,$organization,'','failure');						   
		}
	}
	else
	{
		$result["status"]='ko';
		$result["error_msg"] = "Error occurred when registering the context broker: limit quota excessed";
		$result["msg"] = "Error: An error occurred when registering the context broker: limit quota excessed";
		$result["log"] = "\n\r Error: An error occurred when registering the context broker: limit quota excessed";
		logAction($link,$username,'contextbroker','insert',$name,$organization,'limit quota excessed','failure');
	}
  }
  else
  {
     $result["status"]='ko';
     $result["error_msg"] = "Error occurred when registering the context broker: accessToken is empty. ";
     $result["msg"] = "Error: An error occurred when registering the context broker: accessToken is empty";
     $result["log"] = "\n\r Error: An error occurred when registering the context broker: accessToken is empty";
     logAction($link,$username,'contextbroker','insert',$name,$organization,'accessToken is empty','failure');
  }
	my_log($result);
	mysqli_close($link);
}
else
if ($action=="update")
{  
	$name = mysqli_real_escape_string($link, $_REQUEST['name']);
	$kind = mysqli_real_escape_string($link, $_REQUEST['kind']);
	$ip = mysqli_real_escape_string($link, $_REQUEST['ip']);
	$port = mysqli_real_escape_string($link, $_REQUEST['port']);
	$protocol = mysqli_real_escape_string($link, $_REQUEST['protocol']);
	$version = mysqli_real_escape_string($link, $_REQUEST['version']);
	$latitude = mysqli_real_escape_string($link, $_REQUEST['latitude']);
	$longitude = mysqli_real_escape_string($link, $_REQUEST['longitude']);
	$login = mysqli_real_escape_string($link, $_REQUEST['login']);
	$password = mysqli_real_escape_string($link, $_REQUEST['password']);
	$accesslink = mysqli_real_escape_string($link, $_REQUEST['accesslink']);
	$accessport = mysqli_real_escape_string($link, $_REQUEST['accessport']);
	$path = mysqli_real_escape_string($link, $_REQUEST['path']);
	$visibility = mysqli_real_escape_string($link, $_REQUEST['visibility']);
	$sha = mysqli_real_escape_string($link, $_REQUEST['sha']);
	$organization = mysqli_real_escape_string($link, $_REQUEST['organization']);
	$obj_organization = mysqli_real_escape_string($link, $_REQUEST['obj_organization']);
	$username = mysqli_real_escape_string($link, $_REQUEST['username']);
	$urlnificallback = mysqli_real_escape_string($link, $_REQUEST['urlnificallback']);
	$services = json_decode($_REQUEST['services']);
	for($i = 0; $i < count($services); $i++){
		$services[$i] = mysqli_real_escape_string($link, $services[$i]);
	}

	//get the old urlnificallback info to eventually update
	$q ="SELECT ip, port, urlnificallback, subscription_id FROM iotdb.contextbroker where name='$name'";
	$r = mysqli_query($link, $q);
	if($r)
        {
		while($row = mysqli_fetch_assoc($r))
                {
			$old_ip=$row["ip"];
			$old_port=$row["port"];
			$old_urlnificallback=$row["urlnificallback"];
			$old_subscription_id=$row["subscription_id"];
		}
	
		// begin transaction
		mysqli_autocommit($link, FALSE);
		$success = TRUE;

		$q = "UPDATE contextbroker SET name = '$name', kind = '$kind', ip = '$ip', port = '$port', protocol = '$protocol', version = '$version', latitude = '$latitude', longitude = '$longitude', login = '$login', password = '$password', accesslink = '$accesslink', accessport = '$accessport', path = '$path', visibility = '$visibility', sha = '$sha', organization='$organization', urlnificallback='$urlnificallback'  WHERE name = '$name' and organization='$organization';";

		if (!mysqli_query($link, $q)) $success = FALSE;	

		// delete old services
		$qrs = "DELETE FROM services WHERE broker_name = '$name'";
		if (!mysqli_query($link, $qrs)) $success = FALSE;

		if ($protocol == 'ngsi w/MultiService' && count($services) > 0) {

			// Regex for Syntax Checking
			$serviceRegex = "/^([a-z]|_){1,25}$/";

			// insert new services
			for($i = 0; $i < count($services); $i++){
				$service = $services[$i];

				// Syntax Checking
				if(!preg_match($serviceRegex, $service)){
					$success = FALSE;
				}

				$qs = "INSERT INTO services(name, broker_name) VALUES ('$service', '$name')";
				if(!mysqli_query($link, $qs)) $success = FALSE;
			}	
		}

		if($success){
	
		        $ownmsg = array();
		        $ownmsg["elementId"]=$obj_organization . ':' . $name; // I am using the new identifier	
		        $ownmsg["elementName"]=$obj_organization . ':' . $name;				    
		        $ownmsg["elementUrl"]=$accesslink;
		        $ownmsg["elementType"]="BrokerID";
		        registerOwnerShipObject($ownmsg, $accessToken, 'BrokerID',$result);
		
		        if($result["status"]=='ok')
			{
				$result["log"].='\n\r action: update ok. ' . $q;  
				logAction($link,$username,'contextbroker','update',$name,$organization,'','success');

				//update subscription if different urlnificallback
				if (($old_urlnificallback !== $urlnificallback || $old_ip !== $ip || $old_port != $port)&&($urlnificallback!=='null'))
				{
					$result["log"] .= '\n\r urlnificallback is changed to: '.$urlnificallback.' from '.$old_urlnificallback;
					if ($old_subscription_id!=='undefined' && $old_subscription_id!=='' && $old_subscription_id!=='FAILED')
					{
						nificallback_delete($old_ip, $old_port, $old_subscription_id, $name, $protocol, $services, $result);
					}
					nificallback_create($ip, $port, $name, $urlnificallback, $protocol, $services, $result);//TODO uniform with above (insert scenario), same code is there
                        		//save subscription_id
			                $q = "UPDATE contextbroker SET subscription_id='".$result["content"]."' WHERE name='$name';";
                        		$r = mysqli_query($link, $q);
			                if($r)
                        		{
			               		logAction($link,$username,'contextbroker','insert',$name,$organization,'Subscribe URL NIFI CALLBACK','success');
                        			$result["status"]='ok';
			                        $result["log"].='\n\r action: subscribe ok. ' . $q;
			                }
                        		else
			                {
                        			logAction($link,$username,'contextbroker','insert',$name,$organization,'Subscribe URL NIFI CALLBACK','failure');
			                        $result["status"]='ko';
                        			$result["error_msg"] = "Error occurred when registering the subscription" ;
			                        $result["log"] = "\n\r Error: An error occurred when subscription <br/>" .
                        			$result["msg"] = "Error: An error occurred when subscribe $name. <br/>" .
                                                	   mysqli_error($link) .
	                                                   ' Please enter again the context broker';
					}
				}
		        }
		        else
			{
				logAction($link,$username,'contextbroker','update',$name,$organization,'in register ownership','failure');
        		}
			// successful transaction ----include in transaction nifi scenario
                        mysqli_commit($link);
		}
		else
		{
			// unsuccessful transaction
			mysqli_rollback($link);

			logAction($link,$username,'contextbroker','update',$name,$organization,'','failure');
		
			 $result["status"]='ko';
			 $result["error_msg"] = "Error occurred when updating the context broker $name. <br/>. " ;
			 $result["msg"] = "Error: An error occurred when updating the context broker $name. <br/>" .
						   mysqli_error($link) .
						   ' Please enter again the context broker';
			 $result["log"] = "\n\r Error: An error occurred when updating the context broker $name. <br/>" .
						   mysqli_error($link);				   
		}
	}
	else 
	{
		logAction($link,$username,'contextbroker','update',$name,$organization,'','failure');

                $result["status"]='ko';
                $result["error_msg"] = "Error occurred when reading-updating the context broker $name. <br/>. " ;
                $result["msg"] = "Error: An error occurred when reading-updating the context broker $name. <br/>" .
                                                   mysqli_error($link) .
                                                   ' Please enter again the context broker';
                $result["log"] = "\n\r Error: An error occurred when reading-updating the context broker $name. <br/>" .
                                                   mysqli_error($link);
	}
	my_log($result);
	mysqli_close($link);
}
else if ($action=="delete")
{
	$name = mysqli_real_escape_string($link, $_REQUEST['name']);      
	$organization = mysqli_real_escape_string($link, $_REQUEST['organization']);      
	$username = mysqli_real_escape_string($link, $_REQUEST['username']);

	//firstly retrieve data to invoke the unsubscription 
	$q = "SELECT * FROM contextbroker WHERE name='$name'";
	$r = mysqli_query($link, $q);
	if($r)
	{
		//if internal and ngsi, try to unsubscribe
		while($row = mysqli_fetch_assoc($r))
		{
		        if ($row["kind"] === "internal" && (strpos($row["protocol"],'ngsi')!==false))
        		{
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

	      if($r)
		  {
	          $result["status"]='ok';
          
        	  if($accessToken!=""){
	              $elementId=$organization . ':' . $name;
        	      removeOwnerShipObject($elementId,$accessToken,"BrokerID",$result);
	          }
        	  if($result["status"]='ok'){
	              $result["log"].='\n\r action: delete ok. ' . $q;
        	      logAction($link,$username,'contextbroker','delete',$name,$organization,'','success');
	          }
        	  else{
	              $result["log"].='\n\r action: delete ok from database, delete Ownership failed. ';
        	      logAction($link,$username,'contextbroker','delete',$name,$organization,'delete ok from database, delete Ownership failed.','failure');
	          }
		
		  }
		  else
		  {
			logAction($link,$username,'contextbroker','delete',$name,$organization,'','faliure');
			 $result["status"]='ko';
			 $result["error_msg"] = 'Context broker ' . $name . ' &nbsp; deletion failed. ';
			 $result["msg"] = 'Context broker <b>' . $name . '</b> &nbsp; deletion failed, ' .
						   mysqli_error($link) . 
						   ' Please enter again.';
			 $result["log"] = '\n\r Context broker <b>' . $name . '</b> &nbsp; deletion failed, ' .
							   mysqli_error($link) . $q;				   
		  }
	}
	else {
		logAction($link,$username,'contextbroker','delete',$name,$organization,'','failure');
                $result["status"]='ko';
                $result["error_msg"] = 'Context broker ' . $name . ' &nbsp; deletion failed. Not found. ';
                $result["msg"] = 'Context broker <b>' . $name . '</b> &nbsp; deletion failed, Not Found' .
                                                   mysqli_error($link) .
                                                   ' Please enter again.';
                $result["log"] = '\n\r Context broker <b>' . $name . '</b> &nbsp; deletion failed, Not found' .
                                                           mysqli_error($link) . $q;

	}
	
	my_log($result);
	mysqli_close($link);
}
else if ($action=="get_all_contextbroker_simple")//simple require just the accesstoken, and not the role, username, organization
{
	if (empty($accessToken))
        {
                $result["status"]="ko";
                $result['msg'] = "Access Token not present";
                $result["error_msg"] .= "Problem getting the list of the avaialable context brokers (Access Token not present). ";
                $result["log"]= "action=get_all_contextbroker_simple - error AccessToken not present\r\n";
        }
        else {
		if (isset($_REQUEST['length']))
                	$length = mysqli_real_escape_string($link, $_REQUEST['lenght']);
	        else
        	        $length=-1;
		$start=1;//default is 1 but should throw an error
	        if (($length!=-1)&& (isset($_REQUEST['start'])))
                        $start= mysqli_real_escape_string($link, $_REQUEST['start']);
		if (isset($_REQUEST['draw']))
                        $draw = mysqli_real_escape_string($link, $_REQUEST['draw']);
                else
                        $draw=1;
		if (!isset($_REQUEST['columns']))
			$_REQUEST["columns"]=array();

                //retrieve any parameteres from the accetoken
                //TODO avoid passing all the parameters for LDAP
                get_user_info($accessToken, $username, $organization, $oidc, $role, $result, $ldapBaseName, $ldapServer, $ldapPort, $ldapAdminName, $ldapAdminPwd);

                if ($result["status"]=="ok")
                        get_all_contextbrokers($username, $organization, $role, $accessToken, $link, $length, $start, $draw, $_REQUEST, $result);
        }
        
	my_log($result);
	mysqli_close($link);
}
else if($action == 'get_all_contextbroker')
{
	if (empty($accessToken)) 
	{               
		$result["status"]="ko";
                $result['msg'] = "Access Token not present";
                $result["error_msg"] .= "Problem getting the list of the avaialable context brokers (Access Token not present). ";
                $result["log"]= "action=get_all_contextbroker_simple - error AccessToken not present\r\n";
	}
	else 
	{
		$username = mysqli_real_escape_string($link, $_REQUEST['username']);
        	$organization = mysqli_real_escape_string($link, $_REQUEST['organization']);
	        $loggedrole= mysqli_real_escape_string($link, $_REQUEST['loggedrole']);
        	$length=mysqli_real_escape_string($link, $_REQUEST['length']);
	        $start=mysqli_real_escape_string($link, $_REQUEST['start']);
		$draw=mysqli_real_escape_string($link, $_REQUEST['draw']);
		
		get_all_contextbrokers($username, $organization, $loggedrole, $accessToken, $link, $length, $start, $draw, $_REQUEST, $result);
	}

	my_log($result);
	mysqli_close($link); 
}
else if($action == "get_subset_contextbroker")
{
	//Sara511 - For testing purpose
	$username = mysqli_real_escape_string($link, $_REQUEST['username']);
	$organization = mysqli_real_escape_string($link, $_REQUEST['organization']);
    $loggedrole= mysqli_real_escape_string($link, $_REQUEST['loggedrole']);

	// SEGNALAZIONE: Se entra in questo blocco, al client non viene fornito alcun risultato	
     if (!empty($accessToken)) 
	{
        getOwnerShipObject($accessToken, "BrokerID", $result); 
        getDelegatedObject($accessToken, $username, $result);
	}
    
    $selection= json_decode($_REQUEST['select']);
	$a=0;
	$cond="";
	if (count($selection)!=0)
	{
	    
		while ($a < count($selection))
		{
			 $sel = $selection[$a];
			 $cond .= " (name = '" . $sel->name . "' AND organization = '".$sel->organization."') ";
			 if ($a != count($selection)-1)  $cond .= " OR ";
			 $a++;
		 }
		
		$q = "SELECT DISTINCT * FROM contextbroker WHERE " . $cond;
	}
    else
	    $q = "SELECT DISTINCT * FROM contextbroker";
	
    $r = mysqli_query($link, $q);
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
     while($row = mysqli_fetch_assoc($r)) 
     {
	   
         $selectedrows++;       
         if (!$tobelimited || ($tobelimited && $selectedrows >= $start && $selectedrows < ($start+$offset)))		
         {  
             $idTocheck=$row["organization"].":".$row["name"];       
             if (((isset($result["keys"][$idTocheck]))
                  &&($loggedrole!=='RootAdmin')
                  &&($loggedrole!=='ToolAdmin'))
                 ||
                 ((isset($result["keys"][$idTocheck]))
                  && 
                  ($result["keys"][$idTocheck]["owner"]==$username) 
                  && (($loggedrole==='RootAdmin')
                      ||($loggedrole==='ToolAdmin'))))
                               	
             {
                 //it's mine                      
                 if ($row["visibility"]=="public")                      
                 { 
                     $row["visibility"]= "MyOwnPublic";  
                 }
                 else
                 {
                     if (isset($result["delegation"][$idTocheck])   
                         && $result["delegation"][$idTocheck]["kind"]=="anonymous")    
                         $row["visibility"]= "MyOwnPublic";
	                     
                      else 
                          $row["visibility"]="MyOwnPrivate";
					
                 }
	 				
             }
                               
             else
                               
             {//it's not mine
             
                                   
                 if (isset($result["delegation"][$idTocheck])                         
                     && ($result["delegation"][$idTocheck]["kind"]=="anonymous"))
                                       
                 {//it's delegated as public
                     $row["visibility"]='public';                     
                 }                     
                 else if (isset($result["delegation"][$idTocheck]))                     
                 {//it's delegated personally                               
                     $row["visibility"]='delegated';              
                 }             
                                
                 else                 
                 {                    
                     $row["visibility"]= $row["visibility"];           
                 }
				}
			         if ($row["protocol"] == "ngsi w/MultiService") {
					// the CB supports MultiServices
					$brokerName = $row["name"];
					$servicesQueryString = "SELECT * FROM services WHERE broker_name = '$brokerName'";
					
					// query to services table
					$sqr = mysqli_query($link, $servicesQueryString);

					if ($sqr) {
						
						// dev_log($servicesQueryString . " OK");
						$row["services"] = array();

						while ($servicesRow = mysqli_fetch_assoc($sqr)) {
							array_push($row["services"], $servicesRow["name"]);
						}
					} else {
						
						// dev_log($servicesQueryString . " ERROR");
						$output= format_result($_REQUEST["draw"], 0, 0, null, 'Error: errors in reading data about IOT Broker. <br/>' . generateErrorMessage($link), '\n\r Error: errors in reading data about IOT Broker.' . generateErrorMessage($link), 'ko');
						logAction($link,$username,'contextbroker','get_all_contextbroker','',$organization,'Error: errors in reading data about IOT Broker.','faliure');
					}
				}           
             array_push($data, $row);
			}
				
         $output= format_result($_REQUEST["draw"], $selectedrows+1, $selectedrows+1, $data, "", "\r\n action=get_subset_contextbroker \r\n", 'ok');
		
         logAction($link,$username,'contextbroker','get_subset_contextbroker','',$organization,'','success');

	 }
	}
	else{
		
		$output= format_result($_REQUEST["draw"], 0, 0, null, 'Error: errors in reading data about IOT Broker. <br/>' . generateErrorMessage($link), '\n\r Error: errors in reading data about IOT Broker.' . generateErrorMessage($link), 'ko');
		logAction($link,$username,'contextbroker','get_subset_contextbroker','',$organization,'Error: errors in reading data about IOT Broker.','faliure');
	}    
	my_log($output);
	mysqli_close($link);
}
else if($action == "get_all_contextbroker_latlong")
{
	$organization = mysqli_real_escape_string($link, $_REQUEST['organization']);
    $loggedrole= mysqli_real_escape_string($link, $_REQUEST['loggedrole']);
    $username = mysqli_real_escape_string($link, $_REQUEST['username']);
    
     if (!empty($accessToken)) 
	{
        getOwnerShipObject($accessToken, "BrokerID", $result); 
        getDelegatedObject($accessToken, $username, $result);
	}
    
    $q = "SELECT name, latitude, longitude, visibility, organization, accesslink FROM contextbroker;";
	$r = mysqli_query($link, $q);
	
	if($r) 
	{
	 $result['status'] = 'ok';
	 $result['content'] = array();
     $result["log"].='\n\r action: get_all_contextbroker_latlong ok. ' . $q;

	 while($row = mysqli_fetch_assoc($r)) 
     {
         $idTocheck=$row["organization"].":".$row["name"];    
         if (($loggedrole=='RootAdmin')
                 ||($loggedrole=='ToolAdmin') 
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
         array_push($result['content'], $row);
	 }
    }
	else{
	   $result['status'] = 'ko';
	   $result['msg'] = 'Error: errors in reading data about devices. <br/>' .
						   mysqli_error($link);
	   $result['log'] = '\n\r Error: errors in reading data about devices. <br/>' .
						   mysqli_error($link);				   
	}    
	my_log($result);
	mysqli_close($link);
}

else if($action == "get_delegations")
{
       $accesslink =  mysqli_real_escape_string($link, $_REQUEST['accesslink']);
       $user = mysqli_real_escape_string($link, $_REQUEST['user']);
       $object = mysqli_real_escape_string($link, $_REQUEST['object']);
       $obj_organization = mysqli_real_escape_string($link, $_REQUEST['obj_organization']);
       $name = mysqli_real_escape_string($link, $_REQUEST['name']);
         
       $delegationId= $obj_organization.":".$name;

       getDelegatorObject($accessToken, $user, $result, $object, $delegationId);
       my_log($result);
}
else if($action == "add_delegation")
{  
    $accesslink =  mysqli_real_escape_string($link, $_REQUEST['accesslink']);
    $user = mysqli_real_escape_string($link, $_REQUEST['user']);
    $object = mysqli_real_escape_string($link, $_REQUEST['object']);
    $obj_organization = mysqli_real_escape_string($link, $_REQUEST['obj_organization']);
    $obj_name = mysqli_real_escape_string($link, $_REQUEST['obj_name']);
    $delegated_user = (isset($_REQUEST['delegated_user']))?mysqli_real_escape_string($link, $_REQUEST['delegated_user']):"";
    $delegated_group= (isset($_REQUEST['delegated_group']))?mysqli_real_escape_string($link, $_REQUEST['delegated_group']):"";

    $delId= $obj_organization.":".$obj_name;
    
    if (($delegated_user != "" || $delegated_group != "") && $user != ""){         
        delegateObject($delId, $user, $delegated_user, $delegated_group, $object,$accessToken, $result);    
    }    
    else
    {
        $result["status"]='ko';
        $result["error_msg"]='The function delegate_object has been called without specifying mandatory parameters. ';
        $result["msg"]='\n the function delegate_object has been called without specifying mandatory parameters';
        $result["log"]='\n the function delegate_object has been called without specifying mandatory parameters';    
    }    
    my_log($result);
}
else if($action == "remove_delegation")
{
    $user = mysqli_real_escape_string($link, $_REQUEST['user']);
    $delegationId = mysqli_real_escape_string($link, $_REQUEST['delegationId']);
       
    removeDelegationValue($accessToken, $user, $delegationId, $result);   
    my_log($result);
}
else if ($action =='change_visibility')
{
	$name = mysqli_real_escape_string($link, $_REQUEST['name']);
	$object = mysqli_real_escape_string($link, $_REQUEST['object']);
	$table = mysqli_real_escape_string($link, $_REQUEST['table']);
	$name = mysqli_real_escape_string($link, $_REQUEST['name']);
	$visibility = mysqli_real_escape_string($link, $_REQUEST['visibility']);
	$accesslink = mysqli_real_escape_string($link, $_REQUEST['accesslink']);
	$username = mysqli_real_escape_string($link, $_REQUEST['username']);
	$organization = mysqli_real_escape_string($link, $_REQUEST['organization']);
	$obj_organization = mysqli_real_escape_string($link, $_REQUEST['obj_organization']);
	$cbName = $organization . ":".$name;
	
	$q = "UPDATE ".$table."  SET  visibility = '$visibility' WHERE name='$name' and organization='$organization'";
	$r = mysqli_query($link, $q);
			
	if($r) 
	{
        logAction($link,$username,$table,'change_visibility',$cbName,$organization,'new visibility '.$visibility,'success');			
		
		$result["status"]='ok'; 
		$result["msg"] .= "\n cb Visibility correctly updated"; 
		$result["log"] .= "\n cb $cbName: Visibility correctly updated"; 
		$delId= $obj_organization.":".$name;
		// information to be passed to the interface
		$result["visibility"] = $visibility;
		if ($visibility==='public'){    
            delegateObject($delId, $username,  "ANONYMOUS", "", $object, $accessToken, $result);
		}
		else 
        {
            getDelegatorObject($accessToken, $username, $result,$object, $delId);
            $delegated=$result["delegation"];    
            $found=false;    
            $i=0;
		        while (!$found && $i < count($delegated))
		        {
		                if ($delegated[$i]["userDelegated"]=='ANONYMOUS')
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
		}

	}
	else 
	{
	  logAction($link,$username,$table,'change_visibility',$name,$organization,'new visibility '.$visibility,'faliure');			
	  $result["status"]='ko';
	  $result["error_msg"] .= "Problem in changing the visibility of the $table $cbName. " ; 
	  $result["msg"] .= "\n Problem in changing the visibility of the $table $cbName: " . generateErrorMessage($link); 
	  $result["log"] .= "\n Problem in changing the visibility of the $table $cbName: " . generateErrorMessage($link); 

	}
	my_log($result);
	mysqli_close($link);
}
else if ($action =='change_owner')
{
	$name = mysqli_real_escape_string($link, $_REQUEST['name']);
	$object = mysqli_real_escape_string($link, $_REQUEST['object']);
	$table = mysqli_real_escape_string($link, $_REQUEST['table']);
	$newuser = mysqli_real_escape_string($link, $_REQUEST['newOwner']);
	$currentowner = mysqli_real_escape_string($link, $_REQUEST['owner']);      
	$accesslink =  mysqli_real_escape_string($link, $_REQUEST['accesslink']);
	$organization =  mysqli_real_escape_string($link, $_REQUEST['organization']);
	$obj_organization =  mysqli_real_escape_string($link, $_REQUEST['obj_organization']);
		
    $cbName = $organization . ":".$name;
	
	//for change ownership, a new certificate has to be created (if model is authenticated)

	if (($accessToken != ""))
	{
		$ownmsg = array();
        $ownmsg["elementId"]=$obj_organization . ':' . $name; // I am using the new identifier
        $ownmsg["elementName"]=$obj_organization . ':' . $name;				    
        $ownmsg["elementUrl"]=$accesslink;
        $ownmsg["elementType"]=$object;
    
		$ownmsg["deleted"]= date("Y/m/d");
	    $ownmsg["username"]=$currentowner;
		$ownmsg["elementDetails"]=array();
        registerOwnerShipObject($ownmsg, $accessToken, $object, $result);//delete old ownership
		
        unset($ownmsg["deleted"]);
		$ownmsg["username"]=$newuser;
		registerOwnerShipObject($ownmsg, $accessToken, $object, $result);//insert new ownership
		$result["status"]='ok';
		
		logAction($link,$currentowner,$table,'change_owner',$cbName,$organization,'new owner: '.$newuser,'success');	
	}
	else {
		logAction($link,$currentowner,$table,'change_owner',$cbName,$organization,'new owner: '.$newuser,'faliure');
		$result["status"]='ko';
	}
 	my_log($result);
    	mysqli_close($link);
}
// Used for retrieve all the services, given a CB name
else if($action == 'get_services_by_cb_name'){
	// dev_log("get services: begin");

	$brokerName = mysqli_real_escape_string($link, $_REQUEST['brokerName']);
	// dev_log("get services: $brokerName");

	$services = array();
	$queryString = "SELECT name FROM services WHERE broker_name = '$brokerName'";

	// query execution
	$res = mysqli_query($link, $queryString);

	if ($res) {
		// successful query
		// dev_log("query success");

		while($row = mysqli_fetch_assoc($res)){
			array_push($services, $row);
		}
		$result["status"]="ok";
		$result["content"] = $services;
		// dev_log("get_services: success");
	} else {
		// unsuccessful query
		$result["status"]="ko";
		// dev_log("get_services: error " . mysqli_error($link));
	}

	// send result to client
	echo json_encode($result);
	// dev_log("get services: end\n");
}
else 
	{
	    $result['status'] = 'ko';
		$result['msg'] = 'invalid action ' . $action;
		$result['log'] = 'invalid action ' . $action;
		my_log($result);
	}

