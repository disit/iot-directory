
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

//print_r ($_REQUEST);

$result=array("status"=>"","msg"=>"","content"=>"","log"=>"", "error_msg"=>"");	
/* all the primitives return an array "result" with the following structure

result["status"] = ok/ko; reports the status of the operation (mandatory)
result["msg"] a message related to the execution of the operation (optional)
result["content"] in case of positive execution of the operation the content extracted from the db (optional)
result["log"] keep trace of the operations executed on the db

This array should be encoded in json
*/	


require '../sso/autoload.php';
use Jumbojett\OpenIDConnectClient;


if (isset($_REQUEST['token'])) {
  $oidc = new OpenIDConnectClient($keycloakHostUri, $clientId, $clientSecret);
  $oidc->providerConfigParam(array('token_endpoint' => $keycloakHostUri.'/auth/realms/master/protocol/openid-connect/token'));
  $tkn = $oidc->refreshToken($_REQUEST['token']);
  $accessToken = $tkn->access_token;
}
else {$accessToken="";}

if (isset($_REQUEST['username'])) {
	$currentUser = $_REQUEST['username'];
}

	
if ($action=="insert")
{
	$cb = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
	$device = mysqli_real_escape_string($link, $_REQUEST['device']);
	$value_name = mysqli_real_escape_string($link, $_REQUEST['value_name']);
	$data_type = mysqli_real_escape_string($link, $_REQUEST['data_type']);
	$value_type = mysqli_real_escape_string($link, $_REQUEST['value_type']);
	$editable = mysqli_real_escape_string($link, $_REQUEST['editable']);
	$value_unit = mysqli_real_escape_string($link, $_REQUEST['value_unit']);
	$healthiness_criteria = mysqli_real_escape_string($link, $_REQUEST['healthiness_criteria']);
	$healthiness_value = mysqli_real_escape_string($link, $_REQUEST['healthiness_value']);
	$organization = mysqli_real_escape_string($link, $_REQUEST['organization']);
	
	//Sara2610 - for logging purpose
	$username = mysqli_real_escape_string($link, $_REQUEST['username']);
	// $order = mysqli_real_escape_string($link, $_REQUEST['order']);

	$hc="";
	if ($healthiness_criteria=="refresh_rate") $hc="value_refresh_rate";
	 else if ($healthiness_criteria=="different_values") $hc="different_values";
	 else $hc="value_bounds";
	
	$q = "INSERT INTO event_values(cb, device, value_name, data_type, value_type, editable, value_unit, healthiness_criteria, `$hc`) " .
		 "VALUES('$cb', '$device', '$value_name',  '$data_type', '$value_type', '$editable', '$value_unit', '$healthiness_criteria', '$healthiness_value')"; //, '$order' )";
	$r = mysqli_query($link, $q);

	//Sara2610
	$deviceName = $device . " ".$cb." ".$value_name;
	
	if($r)
	{
	    $result["log"] .= "\r\n Value $cb/$device/$value_name correctly inserted \r\n";
		modify_valueKB($link, $device, $cb, $organization, $result);
        $result["editable"]=$editable;		
        if($result["content"]==null) $result["active"]=false;  else $result["active"]=true;
		$result["msg"] .= '\n insertion in the db of the value was ok';
		if (!isset($result["status"])){
			//Sara2610 - for logging purpose
			logAction($link,$username,'event_values','insert',$deviceName,$organization,'','success');

			$result["status"]="ok";
		}
		else if ($result["status"]=="ko")  
		{ 
			//Sara2610 - for logging purpose
			logAction($link,$username,'event_values','insert',$deviceName,$organization,'Error occurred in the KB','faliure');
		  $result["msg"] .= '\n an error occurred in the KB or context broker';
          $result["log"] .= '\n an error occurred in the KB or context broker';
        }
		else if($result["status"]=="ok"){
				//Sara2610 - for logging purpose
			logAction($link,$username,'event_values','insert',$deviceName,$organization,'','success');		
		}
		
	}
	else
	{
		//Sara2610 - for logging purpose
		logAction($link,$username,'event_values','insert',$deviceName,$organization,'Error occurred registering the value','faliure');
		
		 $result["status"]='ko';
		 $result["msg"] = "Error: An error occurred when registering the value
		 $value_name. <br/>" .   generateErrorMessage($link) .
						   '<br/> Please enter again the value_name';
		$result["log"] = "\n\r Error: An error occurred when registering the value
		 $value_name. <br/>" .   generateErrorMessage($link) .
						   '<br/> Please enter again the value_name';				   
	}
	 my_log($result);
	mysqli_close($link);
}
else
if ($action=="update")
{  
		
	$cb = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
	$device = mysqli_real_escape_string($link, $_REQUEST['device']);
	$value_name = mysqli_real_escape_string($link, $_REQUEST['value_name']);
	$data_type = mysqli_real_escape_string($link, $_REQUEST['data_type']);
	$value_type = mysqli_real_escape_string($link, $_REQUEST['value_type']);
	$editable = mysqli_real_escape_string($link, $_REQUEST['editable']);
	$value_unit = mysqli_real_escape_string($link, $_REQUEST['value_unit']);
	$healthiness_criteria = mysqli_real_escape_string($link, $_REQUEST['healthiness_criteria']);
	$healthiness_value = mysqli_real_escape_string($link, $_REQUEST['healthiness_value']);
	$username = mysqli_real_escape_string($link, $_REQUEST['username']);
	$organization = mysqli_real_escape_string($link, $_REQUEST['organization']);
	
	$hc="";
	if ($healthiness_criteria=="refresh_rate") $hc="value_refresh_rate";
	 else if ($healthiness_criteria=="different_values") $hc="different_values";
	 else $hc="value_bounds";

	$deviceName = $device . " ".$cb." ".$value_name;
	
	$q = "UPDATE event_values SET cb = '$cb', device = '$device', value_name = '$value_name', data_type = '$data_type', value_type = '$value_type', editable = '$editable', value_unit = '$value_unit', healthiness_criteria = '$healthiness_criteria', $hc = '$healthiness_value' 
	WHERE cb = '$cb' and device='$device' and value_name='$value_name'";
	$r = mysqli_query($link, $q);

	if($r)
	{
		modify_valueKB($link, $device, $cb, $organization, $result);	   
		$result["editable"]=$editable;
		if($result["content"]==null) $result["active"]=false;  else $result["active"]=true;
		$result["msg"] .= '\n update in the db of the value was ok';
		$result["log"] .= "\r\n Value $cb/$device/$value_name correctly updated";
		if (!isset($result["status"])){
			//Sara2610 - for logging purpose
			logAction($link,$username,'event_values','update',$deviceName,$organization,'','success');
			$result["status"]="ok";
		}		
		else {
			  if ($result["status"]=="ko")  
		      {
				logAction($link,$username,'event_values','update',$deviceName,$organization,'Error occurred in the KB','faliure'); 
			    $result["msg"] .= '\n an error occurred in the KB or context broker';
			    $result["log"] .= '\n an error occurred in the KB or context broker';
	          } 
			    if($result["status"]=="ok")
				{
			    logAction($link,$username,'event_values','update',$deviceName,$organization,'','success');		
			    }	
		}

	}
	else
    {
		logAction($link,$username,'event_values','update',$deviceName,$organization,'','faliure'); 
		
		$result["status"]='ko';
		 $result["msg"] = 'event_values <b>' . $value_name . '</b> &nbsp; update failed, ' .
         generateErrorMessage($link) .
         ' Please enter again.';
		 $result["log"] = '\n\r event_values <b>' . $value_name . '</b> &nbsp; update failed, ' .
         generateErrorMessage($link);
	}
	 my_log($result);
	mysqli_close($link);
}
else if ($action=="delete")
{
      $cb = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
	  $device = mysqli_real_escape_string($link, $_REQUEST['device']);
	  $value_name = mysqli_real_escape_string($link, $_REQUEST['value_name']);
	  $editable = mysqli_real_escape_string($link, $_REQUEST['editable']);
	  $username = mysqli_real_escape_string($link, $_REQUEST['username']);	  
	  $organization = mysqli_real_escape_string($link, $_REQUEST['organization']);	  
      
	  $deviceName = $device . " ".$cb." ".$value_name;
	  
      $q = "DELETE FROM event_values WHERE cb = '$cb' and device='$device' and value_name='$value_name'";
      $r = mysqli_query($link, $q);
      if($r)
	  {
		modify_valueKB($link, $device, $cb, $organization, $result);	   
        $result["editable"]=$editable;		
        if($result["content"]==null) $result["active"]=false;  else $result["active"]=true;
		$result["msg"] .= '\n delete in the db of the value was ok';
		$result["log"] .= '\n delete in the db of the value was ok';
		if (!isset($result["status"])){
			//Sara2610 - for logging purpose
			logAction($link,$username,'event_values','delete',$deviceName,$organization,'','success'); 
			$result["status"]="ok";
		}
		else {
		if ($result["status"]=="ko")  
		         { 
					//Sara2610 - for logging purpose
					logAction($link,$username,'event_values','delete',$deviceName,$organization,'Error occurred in the KB','faliure'); 
				   $result["error_msg"] .= 'An error occurred in the KB or context broker. ';
				   $result["msg"] .= '\n an error occurred in the KB or context broker';
				   $result["log"] .= '\n an error occurred in the KB or context broker';
				 }
				 if($result["status"]=="ok"){
					//Sara2610 - for logging purpose
					logAction($link,$username,'event_values','delete',$deviceName,$organization,'','success');		
				}
		    }

	  }
	  else
	  {
		//Sara2610 - for logging purpose
		logAction($link,$username,'event_values','delete',$deviceName,$organization,'','faliure'); 
		 $result["status"]='ko';
		 $result["error_msg"] = 'Event_values ' . $value_name . ': deletion failed, ';
		 $result["msg"] = 'event_values <b>' . $value_name . '</b> &nbsp; deletion failed, ' .
         generateErrorMessage($link) .
         ' Please enter again.';
		 $result["log"] = '\n\r event_values ' . $value_name . ' deletion failed, ' .
         generateErrorMessage($link);
	  }
	   my_log($result);
	  mysqli_close($link);
}
else if ($action=="check_if_last_value")
{
      $cb = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
	  $device = mysqli_real_escape_string($link, $_REQUEST['device']);
	  $username = mysqli_real_escape_string($link, $_REQUEST['username']);	  
	  $organization = mysqli_real_escape_string($link, $_REQUEST['organization']);	  
    
	  
      $q = "SELECT * FROM event_values WHERE cb = '$cb' and device='$device'";
      $r = mysqli_query($link, $q);
      if($r)
	  {
          $rowcount=mysqli_num_rows($r);
          logAction($link,$username,'event_values','check_if_last_value',$device,$organization,'','success'); 	
          $result["status"]="ok";
          $result["content"]=$rowcount;
	  }
	  else
	  {
		//Sara2610 - for logging purpose
		logAction($link,$username,'event_values','check_if_last_value',$device,$organization,'','faliure'); 
		 $result["status"]='ko';
		 $result["error_msg"] = 'Event_values ' . $device . ': check_if_last_value failed, ';
		 $result["msg"] = 'event_values <b>' . $device . '</b> &nbsp; check_if_last_value failed, ' .
         generateErrorMessage($link) .
         ' Please enter again.';
		 $result["log"] = '\n\r event_values ' . $device . ' check_if_last_value failed, ' .
         generateErrorMessage($link);
	  }
	   my_log($result);
	  mysqli_close($link);
}
else if($action == 'get_event_value') 
{
	$cb = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
	$device = mysqli_real_escape_string($link, $_REQUEST['device']);
	$value_name = mysqli_real_escape_string($link, $_REQUEST['value_name']);
	$organization = mysqli_real_escape_string($link, $_REQUEST['organization']);
	
	$q = "SELECT * FROM event_values WHERE cb = '$cb' and device='$device' and value_name='$value_name'";
	$r = mysqli_query($link, $q);

	if($r) 
	{
		$row = mysqli_fetch_assoc($r);
		$result['log'] .= "\n\r action:get_event_value. ok " . $q;
		$result['status'] = 'ok';
		$result['content'] = $row;
	} 
	else
	{
		$result['status'] = 'ko';
		$result['msg'] = "Error: reading data for the event_value $value_name. <br/>" .
						   generateErrorMessage($link);
		$result['log'] = "\n\r Error: reading data for the event_value $value_name. <br/>" .
						   generateErrorMessage($link);				   
	}
	 my_log($result);
	mysqli_close($link); 
}
else if($action == 'get_all_event_value')
{
    $organization = mysqli_real_escape_string($link, $_REQUEST['organization']);
    $loggedrole= mysqli_real_escape_string($link, $_REQUEST['loggedrole']);
    $username = mysqli_real_escape_string($link, $_REQUEST['username']);	
    if (!empty($accessToken)) 
	{ 
	 getOwnerShipDevice($accessToken, $result); 
	 getDelegatedDevice($accessToken, $username, $result);
	}
	
    $q = "SELECT cb.sha,cb.accesslink, cb.accessport, v.*, d.kind, d.contextbroker, d.latitude, d.longitude, d.visibility, d.devicetype, d.uri, d.created, d.privatekey, d.organization, d.certificate,d.visibility, CASE WHEN d.mandatoryproperties AND d.mandatoryvalues THEN \"active\" ELSE \"idle\" END	AS status1 FROM event_values v JOIN devices d ON (v.device=d.id AND d.contextbroker=v.cb) JOIN contextbroker cb ON (v.cb=cb.name )"; 
    
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
	
	
	if($r) 
	{
		$data = array();
			
		while($row = mysqli_fetch_assoc($r)) 
		{
		     
            $eid=$row["organization"].":".$row["contextbroker"].":".$row["device"];
            if ( ($loggedrole=="RootAdmin")|| 
                
                ((
                    (
                    ($row["organization"]==$organization)&&
                    (   
                        ($row["visibility"]=='public'  
                         ||
                         (isset($row["uri"]) && $row["uri"]!="" && 
                          isset($result["delegation"][$row["uri"] . "/" . $row["value_name"]]))&& $result["delegation"][$row["uri"] . "/" . $row["value_name"]]["kind"]=="anonymous")
                        )
                    )
                ) 
                ||
                    (isset($row["uri"]) && $row["uri"]!="" && 
                        ((isset($result["delegation"][$eid])&& $result["delegation"][$eid]["kind"]!="anonymous")
                        ||
                        (isset($result["delegation"][$row["uri"] . "/" . $row["value_name"]]))&& $result["delegation"][$row["uri"] . "/" . $row["value_name"]]["kind"]!="anonymous")
                    ) 
                    
                ||
                    (isset($result["keys"][$eid]))
               ))
                
		{	 
			$selectedrows++;
		        if (!$tobelimited || ($tobelimited && $selectedrows >= $start && $selectedrows < ($start+$offset)))
				{
	        $rec = array();
			$rec["cb"]= $row["cb"];
			$rec["device"]= $row["device"];
			$rec["devicetype"]= $row["devicetype"];
			$rec["value_name"]= $row["value_name"];
			$rec["data_type"]= $row["data_type"];
			$rec["value_type"]= $row["value_type"];
			$rec["editable"]= $row["editable"];
			$rec["value_unit"]= $row["value_unit"];
			$rec["healthiness_criteria"]= $row["healthiness_criteria"];
			$rec["order"]= $row["order"];
			$rec["value_refresh_rate"]= $row["value_refresh_rate"];
			$rec["organization"]= $row["organization"];
			$rec["latitude"]= $row["latitude"];
			$rec["longitude"]= $row["longitude"];
			// $rec["visibility"]= $row["visibility"];
			$rec["kind"]= $row["kind"];
			$rec["uri"]= $row["uri"];
            		$rec["status1"]= $row["status1"];			
			$rec["created"]=$row["created"];
			$rec["privatekey"]=$row["privatekey"];
			$rec["certificate"]=$row["certificate"];
			$rec["sha"]=$row["sha"];
			$rec["accesslink"]=$row["accesslink"];
                        $rec["accessport"]=$row["accessport"];
		
			if (isset($result["keys"][$eid]))
			{//it's mine
					if ((isset($result["delegation"][$row["uri"] . "/" . $row["value_name"]])
					     && $result["delegation"][$row["uri"] . "/" . $row["value_name"]]["kind"]=="anonymous")	
                        ||
                        (isset($result["delegation"][$eid])
					     && $result["delegation"][$eid]["kind"]=="anonymous")
                        )
				        $rec["visibility"]= "MyOwnPublic";
				    else  
					    $rec["visibility"]="MyOwnPrivate";
		
					
				$rec["k1"]=$result["keys"][$eid]["k1"];
				$rec["k2"]=$result["keys"][$eid]["k2"];
            }
			else
                       {//it's not mine
                               if ((isset($result["delegation"][$row["uri"] . "/" . $row["value_name"]])
                                       && ($result["delegation"][$row["uri"] . "/" . $row["value_name"]]["kind"]=="anonymous"))
                                   ||
                                   (isset($result["delegation"][$eid])
                                       && ($result["delegation"][$eid]["kind"]=="anonymous"))
                                   )
                               {//it's delegated as public
                                       $rec["visibility"]='public';
					$rec["k1"]="";
					$rec["k2"]="";
                               }
                               else if( (isset($result["delegation"][$row["uri"] . "/" . $row["value_name"]]))
                                   ||
                                   (isset($result["delegation"][$eid])))
                               {//it's delegated personally
                                       $rec["visibility"]='delegated';
                                        $rec["k1"]="";
                                        $rec["k2"]="";

					if (isset($result["delegation"][$row["uri"] . "/" . $row["value_name"]]["k1"]))
                       
					{
						$rec["k1"]= $result["delegation"][$row["uri"] . "/" . $row["value_name"]]["k1"]; // to be fixed 
						$rec["k2"]= $result["delegation"][$row["uri"] . "/" . $row["value_name"]]["k2"]; // to be fixed 
					}
                                   else if(isset($result["delegation"][$eid]["k1"])){
                                       $rec["k1"]= $result["delegation"][$eid]["k1"]; // to be fixed 
						               $rec["k2"]= $result["delegation"][$eid]["k2"]; // to be fixed 
                                   }
				}	
			    else	
				{//not mine, not delegated
				 $rec["visibility"]= $row["visibility"];
				 $rec["k1"]="";
				 $rec["k2"]="";
				} 
			}
     		array_push($data, $rec);
			}
		}

		
	   }
	   
	 $output= format_result($_REQUEST["draw"], $selectedrows+1, $selectedrows+1, $data, "", "\r\n action=get_all_event_value \r\n", 'ok');

	} 
	else
	{
			$output= format_result($_REQUEST["draw"], 0, 0, null, 'Error: errors in reading data about values. <br/>' . generateErrorMessage($link), '\n\r Error: errors in reading data about values.' . generateErrorMessage($link), 'ko');

			
	}

	 my_log($output);
	mysqli_close($link); 
}
else if($action == 'get_all_event_value_admin')
{				 
    $ownDevices= "";
    $username = mysqli_real_escape_string($link, $_REQUEST['username']);
    $organization = mysqli_real_escape_string($link, $_REQUEST['organization']);
    if (!empty($accessToken)) 
	{ 
	 $ownDevices = getOwnerShipDevice($accessToken, $result); 
	 if (isset($username)) getDelegatedDevice($accessToken, $username, $result);
	}
	
	$q = "SELECT v.*, d.`kind`, d.`latitude`, d.`longitude`, d.`organization`, d.`devicetype`, d.`visibility`, d.`uri`,d.`created`, cb.sha, cb.accesslink, cb.accessport, 
		 CASE WHEN d.mandatoryproperties AND d.mandatoryvalues THEN \"active\" ELSE \"idle\" END 
		 AS status1, d.`visibility` FROM `event_values` v JOIN `devices` d ON (v.device=d.id AND d.contextbroker=v.cb) JOIN contextbroker cb ON (v.cb=cb.name )"; 

	
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
	
	
	if($r) 
	{
			
        logAction($link,$username,'event_value','get_all_event_value_admin','',$organization,'','success');		
		$data = array();
		
		while($row = mysqli_fetch_assoc($r)) 
		{
			$selectedrows++;
		        if (!$tobelimited || ($tobelimited && $selectedrows >= $start && $selectedrows < ($start+$offset)))
				{
	        $rec = array();
			$rec["cb"]= $row["cb"];
			$rec["device"]= $row["device"];
			$rec["devicetype"]= $row["devicetype"];
			$rec["value_name"]= $row["value_name"];
			$rec["data_type"]= $row["data_type"];
			$rec["value_type"]= $row["value_type"];
			$rec["editable"]= $row["editable"];
			$rec["value_unit"]= $row["value_unit"];
			$rec["healthiness_criteria"]= $row["healthiness_criteria"];
			$rec["order"]= $row["order"];
			$rec["value_refresh_rate"]= $row["value_refresh_rate"];
			$rec["organization"]= $row["organization"];
			$rec["latitude"]= $row["latitude"];
			$rec["longitude"]= $row["longitude"];
			$rec["kind"]= $row["kind"];
			$rec["uri"]= $row["uri"];
            $rec["status1"]= $row["status1"];			
            $rec["sha"]= $row["status1"];
            $rec["accesslink"]= $row["accesslink"];
            $rec["accessport"]= $row["accessport"];
            $rec["created"]= $row["created"];

			$eid=$row["organization"].":".$row["cb"].":".$row["device"];
             if(isset($result["keys"][$eid]) && $result["keys"][$eid]["owner"]==$username){
                    if ($row["visibility"]=="public"){$rec["visibility"]= "MyOwnPublic";}
                    else 
                    {
                        if (isset($result["delegation"][$row["uri"] . "/" . $row["value_name"]])
                             && $result["delegation"][$row["uri"] . "/" . $row["value_name"]]["kind"]=="anonymous")				  				
                            $rec["visibility"]= "MyOwnPublic";
                        else  
                            $rec["visibility"]="MyOwnPrivate";
                    }
                    $rec["k1"]=$result["keys"][$eid]["k1"];
                    $rec["k2"]=$result["keys"][$eid]["k2"];
                }
            else
                {//it's not mine
                               
                if (isset($result["delegation"][$row["uri"] . "/" . $row["value_name"]])
                                      && ($result["delegation"][$row["uri"] . "/" . $row["value_name"]]["kind"]=="anonymous"))
                                {//it's delegated as public
                                       $rec["visibility"]='public';
                                        $rec["k1"]="";
                                        $rec["k2"]="";
                               }
                               
                else  if (isset($result["delegation"][$row["uri"] . "/" . $row["value_name"]]))
                               {//it's delegated personally
                                        $rec["visibility"]='delegated';
                                        $rec["k1"]="";
                                        $rec["k2"]="";
                                        
                                        if (isset($result["delegation"][$row["uri"] . "/" . $row["value_name"]]["k1"]))
                                        {
                                                $rec["k1"]= $result["delegation"][$row["uri"] . "/" . $row["value_name"]]["k1"]; // to be fixed
                                                $rec["k2"]= $result["delegation"][$row["uri"] . "/" . $row["value_name"]]["k2"]; // to be fixed
                                        }
                                }
                               
                else {//other
				   $rec["visibility"]= $row["visibility"]; // =="public")?"MyOwnPublic":"MyOwnPrivate";
				   $rec["k1"]="";
				   $rec["k2"]="";
				   $rec["owner"]="";
				}
			}

     		array_push($data, $rec);
		}
	   }
	   
	   	 $output= format_result($_REQUEST["draw"], $selectedrows+1, $selectedrows+1, $data, "", "\r\n action=get_all_event_value_admin \r\n", 'ok');
	} 
	else
	{
		//Sara611 - for logging purpose
			logAction($link,$username,'event_value','get_all_event_value_admin','',$organization,'Error: errors in reading data about values.','faliure');	
	$output= format_result($_REQUEST["draw"], 0, 0, null, 'Error: errors in reading data about values. <br/>' . generateErrorMessage($link), '\n\r Error: errors in reading data about values.' . generateErrorMessage($link), 'ko');

		//$result['status'] = 'ko';
		//$result['msg'] = 'Error:  in action get_all_event_value_admin <br/>' .
		//				   generateErrorMessage($link);
		//$result['log'] = 'Error:  in action get_all_event_value_admin' .
		//				   generateErrorMessage($link);				   
	}

	 my_log($output);
	mysqli_close($link); 
}

else if($action == 'get_all_private_event_value')
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
	if ($ownDevices!="")
	{	
		
		$q = "SELECT v.*, d.`id`, d.`kind`, d.`contextbroker`,  d.`latitude`, d.`longitude`, d.`devicetype`,  d.`organization`, d.`visibility`, d.`uri`,d.`model`, d.`created`, d.`privatekey`, d.`certificate`, cb.`sha`,cb.`accesslink`, cb.`accessport`,
	      CASE WHEN d.mandatoryproperties AND d.mandatoryvalues THEN \"active\" ELSE \"idle\" END AS status1,
	      d.`visibility` FROM `event_values` v JOIN `devices` d ON (v.device=d.id AND d.contextbroker=v.cb) JOIN `contextbroker` cb ON (v.cb=cb.name )"; 
		

        $r=create_datatable_data($link,$_REQUEST,$q, "deleted IS null");	
        $selectedrows=-1;

        if($_REQUEST["length"] != -1)
			{		$start= $_REQUEST['start'];
					$offset=$_REQUEST['length'];
					$tobelimited=true;
			}	
        else
			{
				$tobelimited=false;
			}
			
			
		if($r) 
		{
			logAction($link,$username,'event_value','get_all_private_event_value','',$organization,'','success');		 

			$data = array();
			
			 while($row = mysqli_fetch_assoc($r)) 
			{
                 $eid=$row["organization"].":".$row["contextbroker"].":".$row["id"];
				  
			  if (
                  (isset($result["keys"][$eid])
                   &&((($result["keys"][$eid]["owner"]==$username) && ($loggedrole==='RootAdmin'))
                      ||
                     ($loggedrole!='RootAdmin')
                     )
                 )
                  
                 )
			  {	
                 
                 $selectedrows++;
		        if (!$tobelimited || ($tobelimited && $selectedrows >= $start && $selectedrows < ($start+$offset)))
				{
				$rec = array();
				$rec["id"]=$row["id"];
				$rec["cb"]= $row["cb"];
				$rec["uri"]= $row["uri"];
				$rec["model"]= $row["model"];
				$rec["device"]= $row["device"];
				$rec["devicetype"]= $row["devicetype"];
				$rec["value_name"]= $row["value_name"];
				$rec["data_type"]= $row["data_type"];
				$rec["value_type"]= $row["value_type"];
				$rec["editable"]= $row["editable"];
				
				$rec["value_unit"]= $row["value_unit"];
				$rec["healthiness_criteria"]= $row["healthiness_criteria"];
				$rec["order"]= $row["order"];
				$rec["value_refresh_rate"]= $row["value_refresh_rate"];
				$rec["latitude"]= $row["latitude"];
				$rec["longitude"]= $row["longitude"];
				$rec["organization"]= $row["organization"];
				$rec["kind"]= $row["kind"];
				$rec["status1"]= $row["status1"];			
				$rec["created"]=$row["created"];
				$rec["privatekey"]=$row["privatekey"];
				$rec["certificate"]=$row["certificate"];
				$rec["sha"]=$row["sha"];
				$rec["accesslink"]=$row["accesslink"];
				$rec["accessport"]=$row["accessport"];
                  
                   /* if ($row["visibility"]=="private")
                    {
                        $rec["visibility"]= "MyOwnPrivate";
                    }   
                    else{
                        $rec["visibility"]= "MyOwnPublic";
                    }*/
                    
                    
                                
                    if ((isset($result["delegation"][$row["uri"] . "/" . $row["value_name"]])
                         && $result["delegation"][$row["uri"] . "/" . $row["value_name"]]["kind"]=="anonymous")	            
                        ||
                        (isset($result["delegation"][$eid])
                         && $result["delegation"][$eid]["kind"]=="anonymous")           
                       )
                        $rec["visibility"]= "MyOwnPublic";      
                    else             
                        $rec["visibility"]="MyOwnPrivate";

                        
			
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
                    

                    
							
				array_push($data, $rec);
                }
			}	
		}
		  
		 $output= format_result($_REQUEST["draw"], $selectedrows+1, $selectedrows+1, $data, "", "\r\n action=get_all_private_event_value \r\n", 'ok');
		} 
		else
		{
			//Sara611 - for logging purpose
			logAction($link,$username,'event_value','get_all_private_event_value','',$organization,'Error: errors in reading data about devices.','faliure');		 

	       $output= format_result($_REQUEST["draw"], 0, 0, null, 'Error: errors in reading data about devices. <br/>' . generateErrorMessage($link), '\n\r Error: errors in reading data about devices.' . generateErrorMessage($link), 'ko');
			
		}
	}
	else
	{
		 $output= format_result($_REQUEST["draw"], 0, 0, array(), "", "\r\n action=get_all_private_event_value \r\n", 'ok');	
	}
	my_log($output); 
	mysqli_close($link); 
}
else if($action == 'get_all_delegated_event_value')
{
	//Sara611 - for logging purpose
	$username = mysqli_real_escape_string($link, $_REQUEST['username']);
	$organization = mysqli_real_escape_string($link, $_REQUEST['organization']);
    $loggedrole= mysqli_real_escape_string($link, $_REQUEST['loggedrole']);

	
    if (!empty($accessToken)) 
	{ 
	    getDelegatedDevice($accessToken, $username, $result);
	}
		
      	
		
    $q = "SELECT v.*, d.`id`,d.`contextbroker`, d.`kind`, d.`latitude`, d.`longitude`, d.`devicetype`,  d.`organization`, d.`visibility`, d.`uri`,d.`model`, d.`created` ,cb.`accesslink`, cb.`accessport`,CASE WHEN d.mandatoryproperties AND d.mandatoryvalues THEN \"active\" ELSE \"idle\" END AS status1, d.`visibility` FROM `event_values` v JOIN `devices` d ON (v.device=d.id AND d.contextbroker=v.cb) JOIN `contextbroker` cb ON (v.cb=cb.name )";
    
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
		
    if($r) 
		
    {
			
        logAction($link,$username,'event_value','get_all_delegated_event_value','',$organization,'','success');		 
        $data = array();
	 
        while($row = mysqli_fetch_assoc($r)) 
			
        {
 
             $eid=$row["organization"].":".$row["contextbroker"].":".$row["id"];
            if (isset($row["uri"]) && $row["uri"]!="" && 
                  (
                      (isset($result["delegation"][$row["uri"] . "/" . $row["value_name"]])
                        &&  $result["delegation"][$row["uri"] . "/" . $row["value_name"]]["kind"]!="anonymous")
                  ||
                     (isset($result["delegation"][$eid])
                        &&  $result["delegation"][$eid]["kind"]!="anonymous")
                  )

                 )
                  
			 {
				$selectedrows++;
		        if (!$tobelimited || ($tobelimited && $selectedrows >= $start && $selectedrows < ($start+$offset)))
				{
         		$rec = array();
                $rec["id"]=$row["id"];
				$rec["cb"]= $row["cb"];
				$rec["device"]= $row["device"];
				$rec["devicetype"]= $row["devicetype"];
				$rec["value_name"]= $row["value_name"];
				$rec["data_type"]= $row["data_type"];
				$rec["value_type"]= $row["value_type"];
				$rec["editable"]= $row["editable"];
				$rec["value_unit"]= $row["value_unit"];
				$rec["healthiness_criteria"]= $row["healthiness_criteria"];
				$rec["order"]= $row["order"];
				$rec["value_refresh_rate"]= $row["value_refresh_rate"];
				$rec["latitude"]= $row["latitude"];
				$rec["longitude"]= $row["longitude"];
				$rec["organization"]= $row["organization"];
                $rec["accesslink"]=$row["accesslink"];
				$rec["accessport"]=$row["accessport"];
				$rec["created"]=$row["created"];
				// $rec["visibility"]= $row["visibility"];
				$rec["kind"]= $row["kind"];
				$rec["status1"]= $row["status1"];
                $rec["k1"]="";
				$rec["k2"]=""; 				
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
				
				array_push($data, $rec);
				}
			 } 
			}
		 $output= format_result($_REQUEST["draw"], $selectedrows+1, $selectedrows+1, $data, "", "\r\n action=get_all_delegated_event_value \r\n", 'ok');
		
		} 
		else
		{
            logAction($link,$username,'event_value','get_all_delegated_event_value','',$organization,'','faliure');
            $output= format_result($_REQUEST["draw"], 0, 0, null, 'Error: errors in reading data about devices. <br/>' . generateErrorMessage($link), '\n\r Error: errors in reading data about devices.' . generateErrorMessage($link), 'ko');
			
		}

    my_log($output);
	mysqli_close($link); 
}
else if($action == 'get_subset_event_value')
{
	//Sara611 - for logging purpose
	$username = mysqli_real_escape_string($link, $_REQUEST['username']);
	$organization = mysqli_real_escape_string($link, $_REQUEST['organization']);
    $loggedrole= mysqli_real_escape_string($link, $_REQUEST['loggedrole']);
	
    $selection= json_decode($_REQUEST['select']);
	 $ownDevices= "";
    if (!empty($accessToken)) 
	{ 
	   $ownDevices = getOwnerShipDevice($accessToken, $result);	
        getDelegatedDevice($accessToken, $username, $result);
	}
	$a=0;
	$cond="";
	
	$q = "SELECT v.*, d.`kind`, d.`latitude`, d.`longitude`, d.`devicetype`, d.`id`, d.`uri`,  d.`organization`,    
	      CASE WHEN d.`mandatoryproperties` AND d.`mandatoryvalues` THEN \"active\" ELSE \"idle\" END 
	      AS status1, d.`visibility` FROM `event_values` v JOIN `devices` d ON (v.`device`=d.`id` AND d.`contextbroker`=v.`cb`)"; 
	if (count($selection)!=0)
	{

		while ($a < count($selection))
		{
			 $sel = $selection[$a];
			 $cond .= " (device='". $sel->id . "' AND cb = '" . $sel->cb . "' AND value_name= '". $sel->value_name ."') ";
			 if ($a != count($selection)-1)  $cond .= " OR ";
			 $a++;
		 }
		
		
        
        $r=create_datatable_data($link,$_REQUEST,$q, "deleted IS null AND (" . $cond . ")");

		
	}
    else
	{	
        $r=create_datatable_data($link,$_REQUEST,$q, "1=2");
	}
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
	
	if($r) 
	{
	    //Sara611 - for logging purpose
		logAction($link,$username,'event_value','get_subset_event_value','',$organization,'','success');
		
		$data = array();

		while($row = mysqli_fetch_assoc($r)) 
		{
	        
			$selectedrows++;
		        if (!$tobelimited || ($tobelimited && $selectedrows >= $start && $selectedrows < ($start+$offset)))
				{
            $eid=$row["organization"].":".$row["cb"].":".$row["id"];
            $rec= array();
			$rec["cb"]= $row["cb"];
			$rec["device"]= $row["device"];
			$rec["devicetype"]= $row["devicetype"];
			$rec["value_name"]= $row["value_name"];
			$rec["data_type"]= $row["data_type"];
			$rec["value_type"]= $row["value_type"];
			$rec["editable"]= $row["editable"];
			$rec["value_unit"]= $row["value_unit"];
			$rec["healthiness_criteria"]= $row["healthiness_criteria"];
			$rec["order"]= $row["order"];
			$rec["value_refresh_rate"]= $row["value_refresh_rate"];
			$rec["organization"]= $row["organization"];  //organization added 
			$rec["latitude"]= $row["latitude"];
			$rec["longitude"]= $row["longitude"];
			// $rec["visibility"]= $row["visibility"];
			$rec["kind"]= $row["kind"];
			$rec["status1"]= $row["status1"];			
			
                
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
						if (isset($result["delegation"][$row["uri"] . "/" . $row["value_name"]])
							 && $result["delegation"][$row["uri"] . "/" . $row["value_name"]]["kind"]=="anonymous")				  				
							$rec["visibility"]= "MyOwnPublic";
						else  
							$rec["visibility"]="MyOwnPrivate";
					}
				
				$rec["k1"]=$result["keys"][$eid]["k1"];
				$rec["k2"]=$result["keys"][$eid]["k2"];
            }
			else{
				if (isset($result["delegation"][$row["uri"] . "/" . $row["value_name"]]))
				{
					$rec["visibility"]='delegated';
					$rec["k1"]="";
					$rec["k2"]="";
					if (isset($result["delegation"][$row["uri"] . "/" . $row["value_name"]]["k1"]))
					{	
						$rec["k1"]=$result["delegation"][$row["uri"] . "/" . $row["value_name"]]["k1"];; // to be fixed 
						$rec["k2"]=$result["delegation"][$row["uri"] . "/" . $row["value_name"]]["k2"];; // to be fixed 
					}
				}
				else 
				{ 
					$rec["visibility"]=$row["visibility"];
					$rec["k1"]="";
					$rec["k2"]="";
                } 					
			}
	        array_push($data, $rec); 
			}			
				
		}
		
	   $output= format_result($_REQUEST["draw"], $selectedrows+1, $selectedrows+1, $data, "", "\r\n action=get_subset_event_value \r\n", 'ok');

	} 
	else
	{
	    //Sara611 - for logging purpose
		logAction($link,$username,'event_value','get_subset_event_value','',$organization,'Error: errors in reading data about devices.','faliure');
        $output= format_result($_REQUEST["draw"], 0, 0, null, 'Error: errors in reading data about devices. <br/>' . generateErrorMessage($link), '\n\r Error: errors in reading data about devices.' . generateErrorMessage($link), 'ko');
		
	}

	 my_log($output);
	mysqli_close($link); 
}


else if($action == 'get_all_private_event_value_map')
{
	$username = mysqli_real_escape_string($link, $_REQUEST['username']);
	$organization = mysqli_real_escape_string($link, $_REQUEST['organization']);
	$loggedrole= mysqli_real_escape_string($link, $_REQUEST['loggedrole']);
     $ownDevices= "";
    if (!empty($accessToken)) 
	{ 
	 $ownDevices = getOwnerShipDevice($accessToken, $result); 
	}
	if ($ownDevices!="")
	{	
      	$q = "SELECT v.*, d.`id`, d.`kind`, d.`latitude`, d.`longitude`, d.`devicetype`,  d.`organization`, d.`visibility`, d.`uri`, d.`created`, d.`privatekey`, d.`certificate`, cb.`sha`,cb.`accesslink`, cb.`accessport`,
	      CASE WHEN d.mandatoryproperties AND d.mandatoryvalues THEN \"active\" ELSE \"idle\" END AS status1,
	      d.`visibility` FROM `event_values` v JOIN `devices` d ON (v.device=d.id AND d.contextbroker=v.cb) JOIN `contextbroker` cb ON (v.cb=cb.name ) where deleted IS null"; 
		
        $r = mysqli_query($link, $q);

        
		if($r) 
		{
			logAction($link,$username,'event_value','get_all_private_event_value_map','',$organization,'','success');
			
			$result['status'] = 'ok';
			$result['log'] .= "\n\r action:get_all_private_event_value. ok " . $q;
		
			$result['content'] = array();
			 while($row = mysqli_fetch_assoc($r)) 
			{
				$eid=$row["organization"].":".$row["cb"].":".$row["id"];
                 
                 if (
                     isset($result["keys"][$eid])
                   &&((($result["keys"][$eid]["owner"]==$username) && ($loggedrole==='RootAdmin'))
                      ||
                     ($loggedrole!='RootAdmin')
                     )
                 
                    )
                {
                $rec = array();
				$rec["id"]=$row["id"];
				$rec["cb"]= $row["cb"];
				$rec["device"]= $row["device"];
				$rec["devicetype"]= $row["devicetype"];
				$rec["value_name"]= $row["value_name"];
				$rec["data_type"]= $row["data_type"];
				$rec["value_type"]= $row["value_type"];
				$rec["editable"]= $row["editable"];
				$rec["value_unit"]= $row["value_unit"];
				$rec["healthiness_criteria"]= $row["healthiness_criteria"];
				// $rec["different_values"]= $row["different_values"];
				// $rec["value_bounds"]= $row["value_bound"];
				$rec["order"]= $row["order"];
				$rec["value_refresh_rate"]= $row["value_refresh_rate"];
				$rec["latitude"]= $row["latitude"];
				$rec["longitude"]= $row["longitude"];
				// $rec["visibility"]= $row["visibility"];
				$rec["kind"]= $row["kind"];
				$rec["status1"]= $row["status1"];			
				$rec["created"]=$row["created"];
	                        $rec["privatekey"]=$row["privatekey"];
        	                $rec["certificate"]=$row["certificate"];
                	        $rec["sha"]=$row["sha"];
				$rec["accesslink"]=$row["accesslink"];
				$rec["accessport"]=$row["accessport"];

				if(isset($result["keys"][$eid])){
                         if ($row["visibility"]=="public")
					{
						$rec["visibility"]= "MyOwnPublic";
					}
					else 
					{
						if (isset($result["delegation"][$row["uri"] . "/" . $row["value_name"]])
							 && $result["delegation"][$row["uri"] . "/" . $row["value_name"]]["kind"]=="anonymous")				  				
							$rec["visibility"]= "MyOwnPublic";
						else  
							$rec["visibility"]="MyOwnPrivate";
					}
					
					
					$rec["k1"]=$result["keys"][$eid]["k1"];
					$rec["k2"]=$result["keys"][$eid]["k2"];
                         
                     }
				else{
					$rec["visibility"]= $row["visibility"];
					$rec["k1"]="";
					$rec["k2"]="";	
				}				
				array_push($result['content'], $rec);
			}
        }
		} 
		else
		{
			//Sara611 - for logging purpose
			logAction($link,$username,'event_value','get_all_private_event_value_map','',$organization,'','faliure');
			
			$result['status'] = 'ko';
			$result['msg'] = 'Error: errors in action get_all_private_event_value_map <br/>' .
							   generateErrorMessage($link);
			$result['log'] = 'Error: errors in action get_all_private_event_value_map <br/>' .
							   generateErrorMessage($link);				   
		}
	}
	 my_log($result);
	mysqli_close($link); 
}
else if($action == 'get_all_delegated_event_value_map')
{
	$username = mysqli_real_escape_string($link, $_REQUEST['username']);
	$organization = mysqli_real_escape_string($link, $_REQUEST['organization']);
    $loggedrole= mysqli_real_escape_string($link, $_REQUEST['loggedrole']);
	
    if (!empty($accessToken)) 
	{  
	    getDelegatedDevice($accessToken,$username, $result);  
	}
		
   $q = "SELECT v.*,d.`id`, d.`kind`, d.`latitude`, d.`longitude`, d.`devicetype`, d.`visibility`, d.`organization`, d.`uri`,
	      CASE WHEN d.`mandatoryproperties` AND d.`mandatoryvalues` THEN \"active\" ELSE \"idle\" END 
	      AS status1, d.`visibility` FROM `event_values` v JOIN `devices` d ON (v.`device`=d.`id` AND d.`contextbroker`=v.`cb`) where deleted IS null;";     

     $r = mysqli_query($link, $q);
    	
    if($r) 
		{
			
        logAction($link,$username,'event_value','get_all_delegated_event_value_map','',$organization,'','success');
        $result['status'] = 'ok';			
        $result['log'] .= "\n\r action:get_all_delegated_event_value_map. ok " . $q;
		
        $result['content'] = array(); 
        while($row = mysqli_fetch_assoc($r)) 
			{
		
            $eid=$row["organization"].":".$row["cb"].":".$row["id"];   
            if (isset($row["uri"]) && $row["uri"]!="" && 
                  (
                      (isset($result["delegation"][$row["uri"] . "/" . $row["value_name"]])
                        &&  $result["delegation"][$row["uri"] . "/" . $row["value_name"]]["kind"]!="anonymous")
                  ||
                     (isset($result["delegation"][$eid])
                        &&  $result["delegation"][$eid]["kind"]!="anonymous")
                  )

                 )
                  
			 {
         		$rec = array();
				$rec["id"]= $row["id"];
				$rec["cb"]= $row["cb"];
				$rec["device"]= $row["device"];
				$rec["devicetype"]= $row["devicetype"];
				$rec["value_name"]= $row["value_name"];
				$rec["data_type"]= $row["data_type"];
				$rec["value_type"]= $row["value_type"];
				$rec["editable"]= $row["editable"];
				$rec["value_unit"]= $row["value_unit"];
				$rec["healthiness_criteria"]= $row["healthiness_criteria"];
				$rec["order"]= $row["order"];
				$rec["value_refresh_rate"]= $row["value_refresh_rate"];
				$rec["latitude"]= $row["latitude"];
				$rec["longitude"]= $row["longitude"];
				// $rec["visibility"]= $row["visibility"];
				$rec["kind"]= $row["kind"];
				$rec["status1"]= $row["status1"];
                $rec["k1"]="";
				$rec["k2"]=""; 				
                if (isset($result["delegation"][$row["uri"] . "/" . $row["value_name"]]["k1"]))
                { 					
					$rec["k1"]=$result["delegation"][$row["uri"] . "/" . $row["value_name"]]["k1"]; // to be fixed
					$rec["k2"]=$result["delegation"][$row["uri"] . "/" . $row["value_name"]]["k2"]; // to be fixed 
				}
				$rec["visibility"]="delegated";
				
				array_push($result['content'], $rec);
			 } 
			}
		} 
		else
		{
			logAction($link,$username,'event_value','get_all_delegated_event_value_map','',$organization,'','faliure');	
			$result['status'] = 'ko';
			$result['msg'] = 'Error: errors in action: get_all_delegated_event_value_map. <br/>' .
							   generateErrorMessage($link);
			$result['log'] = '\n\r Error: errors in action: get_all_delegated_event_value_map.' .
							   generateErrorMessage($link);				   
		}
	 my_log($result);
	mysqli_close($link); 
}


else if($action == 'get_subset_event_value_admin')
{	
    $selection= json_decode($_REQUEST['select']);
	$username = mysqli_real_escape_string($link, $_REQUEST['username']);
	$organization = mysqli_real_escape_string($link, $_REQUEST['organization']);
 
	 $ownDevices= "";
    if (!empty($accessToken)) 
	{ 
	 $ownDevices = getOwnerShipDevice($accessToken, $result); 
	 if (isset($result["username"])) getDelegatedDevice($accessToken, $result["username"], $result);	 
	 
	}
	$a=0;
	$cond="";
	if (count($selection)!=0)
	{
	    
		while ($a < count($selection))
		{
			 $sel = $selection[$a];
			 $cond .= " (device='". $sel->id . "' AND cb = '" . $sel->contextbroker . "' AND value_name= '". $sel->value_name ."') ";
			 if ($a != count($selection)-1)  $cond .= " OR ";
			 $a++;
		 }
		
		//$q = "SELECT v.*, d.kind, d.latitude, d.longitude, d.devicetype, d.uri, d.id, 
		//CASE WHEN d.mandatoryproperties AND d.mandatoryvalues THEN \"active\" ELSE \"idle\" END AS 
		//status, d.visibility FROM event_values v JOIN devices d ON (v.device=d.id AND d.contextbroker=v.cb) WHERE d.deleted is null and (" . $cond . ")";
		// echo $q;
		
				
		$q = "SELECT v.*, d.`kind`, d.`latitude`, d.`longitude`, d.`devicetype`, d.`id`, d.`organization`,
	      CASE WHEN d.`mandatoryproperties` AND d.`mandatoryvalues` THEN \"active\" ELSE \"idle\" END 
	      AS status1, d.`visibility` FROM `event_values` v JOIN `devices` d ON (v.`device`=d.`id` AND d.`contextbroker`=v.`cb`)"; // WHERE (" . $cond . ")";  //d.deleted is null and 


	}
    else
	    $q = "SELECT  * FROM event_values WHERE 1=2";
	
	
	
    //$r = mysqli_query($link, $q);
	
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
	
	if($r) 
	{
		//$result['status'] = 'ok';
		//$result['log'] .= "\n\r action:get_subset_event_value_admin ok " . $q;
		
		//$result['content'] = array();
		//Sara611 - for logging purpose
		logAction($link,$username,'event_value','get_subset_event_value_admin','',$organization,'','success');
		while($row = mysqli_fetch_assoc($r)) 
		{	 
			$selectedrows++;
			if (!$tobelimited || ($tobelimited && $selectedrows >= $start && $selectedrows < ($start+$offset)))
			{
            $rec= array();
			$rec["cb"]= $row["cb"];
			$rec["device"]= $row["device"];
			$rec["devicetype"]= $row["devicetype"];
			$rec["value_name"]= $row["value_name"];
			$rec["data_type"]= $row["data_type"];
			$rec["value_type"]= $row["value_type"];
			$rec["editable"]= $row["editable"];
			$rec["value_unit"]= $row["value_unit"];
			$rec["healthiness_criteria"]= $row["healthiness_criteria"];
			//$rec["different_values"]= $row["different_values"];
			// $rec["value_bounds"]= $row["value_bound"];
			$rec["order"]= $row["order"];
			$rec["value_refresh_rate"]= $row["value_refresh_rate"];
			$rec["organization"]= $row["organization"];
			$rec["latitude"]= $row["latitude"];
			$rec["longitude"]= $row["longitude"];
			// $rec["visibility"]= $row["visibility"];
			$rec["kind"]= $row["kind"];
			$rec["status"]= $row["status"];			
                
			$eid=$row["organization"].":".$row["cb"].":".$row["id"];
            
            if (isset($result["keys"][$eid]) && $result["keys"][$eid]["owner"]==$username)
			{
				// $rec["visibility"]= ($row["visibility"]=="public")?"MyOwnPublic":"MyOwnPrivate";
				if ($row["visibility"]=="public")
					{
						$rec["visibility"]= "MyOwnPublic";
					}
					else 
					{
						if (isset($result["delegation"][$row["uri"] . "/" . $row["value_name"]])
							 && $result["delegation"][$row["uri"] . "/" . $row["value_name"]]["kind"]=="anonymous")				  				
							$rec["visibility"]= "MyOwnPublic";
						else  
							$rec["visibility"]="MyOwnPrivate";
					}
				
				$rec["k1"]=$result["keys"][$eid]["k1"];
				$rec["k2"]=$result["keys"][$eid]["k2"];
                        }
			else  if (isset($result["keys"][$eid]) && $result["keys"][$eid]["owner"]!=$username)
			{
			   $rec["visibility"]= $row["visibility"]; // =="public")?"MyOwnPublic":"MyOwnPrivate";
			   $rec["k1"]=$result["keys"][$eid]["k1"];
			   $rec["k2"]=$result["keys"][$eid]["k2"];
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
		
	   $output= format_result($_REQUEST["draw"], $selectedrows+1, $selectedrows+1, $data, "", "\r\n action=get_subset_event_value_admin \r\n", 'ok');

	} 
	else
	{
		//Sara611 - for logging purpose
		logAction($link,$username,'event_value','get_subset_event_value_admin','',$organization,'Error: errors in reading data about values.','faliure');
		
		$output= format_result($_REQUEST["draw"], 0, 0, null, 'Error: errors in reading data about values. <br/>' . generateErrorMessage($link), '\n\r Error: errors in reading data about values.' . generateErrorMessage($link), 'ko');
			   
	}

	 my_log($output);
	mysqli_close($link); 
}
else if($action == 'get_value_unit_data') 
{	
	//Sara611 - for logging purpose
	$username = mysqli_real_escape_string($link, $_REQUEST['username']);
	$organization = mysqli_real_escape_string($link, $_REQUEST['organization']);

	$value_type = mysqli_real_escape_string($link, $_REQUEST['value_type']);
	
	$q = "SELECT * FROM value_types WHERE value_type = '$value_type'";
	$r = mysqli_query($link, $q);

	if($r) 
	{
	//Sara611 - for logging purpose
	logAction($link,$username,'event_value','get_value_unit_data','',$organization,'','success');
	
	 $result['status'] = 'ok';
	 $result['log'] .= "\n\r action:get_value_unit_data. ok " . $q;
	 $result['content'] = array();
     while($row = mysqli_fetch_assoc($r)) 
     {
	   array_push($result['content'], $row);
	 }
    }
	else{
		//Sara611 - for logging purpose
		logAction($link,$username,'event_value','get_value_unit_data','',$organization,'','faliure');
		
	   $result['status'] = 'ko';
	   $result['msg'] = 'Error: errors in get_value_unit_data. <br/>' .
						   generateErrorMessage($link);
	   $result['log'] = 'Error: errors in get_value_unit_data. <br/>' .
						   generateErrorMessage($link);					   
	}    
	 my_log($result);
	mysqli_close($link);
}
else if($action == "get_value_latlong")
{
    $id = mysqli_real_escape_string($link, $_REQUEST['id']);
	$cb = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
	
	$q = "SELECT d.id, d.contextbroker, v.value_name, d.latitude, d.longitude  FROM devices d JOIN event_values v ON (d.id=v.device and d.contextbroker=v.cb) WHERE d.deleted IS null and d.id='$id' and d.contextbroker='$cb'";
	$r = mysqli_query($link, $q);
	
	if($r) 
	{
	 $result['status'] = 'ok';
	 $result['log'] .= "\n\r action:get_value_latlong. ok " . $q;
	 
	 $result['content'] = array();
     while($row = mysqli_fetch_assoc($r)) 
     {
	   array_push($result['content'], $row);
	 }
    }
	else{
	   $result['status'] = 'ko';
	   $result['msg'] = 'Error: errors in reading data about location of the device. <br/>' .
						   generateErrorMessage($link);
		$result['log'] = 'Error: errors in reading data about location of the device. <br/>' .
						   generateErrorMessage($link);				   
	}    
	 my_log($result);
	mysqli_close($link);
}
else if($action == "get_all_value_latlong")
{
    
	$loggedrole= mysqli_real_escape_string($link, $_REQUEST['loggedrole']);
    $organization = mysqli_real_escape_string($link, $_REQUEST['organization']);
    $username = mysqli_real_escape_string($link, $_REQUEST['username']);
    if (!empty($accessToken)) 
	{ 
	 $ownDevices = getOwnerShipDevice($accessToken, $result); 	 
	 getDelegatedDevice($accessToken, $username, $result);	 
	}
	
    $q = "SELECT cb.sha,cb.accesslink, cb.accessport, v.*, d.id,d.contextbroker, d.kind, d.latitude, d.longitude, d.visibility, d.devicetype, d.uri, d.created, d.privatekey, d.organization, d.certificate,d.visibility, CASE WHEN d.mandatoryproperties AND d.mandatoryvalues THEN \"active\" ELSE \"idle\" END	AS status1 FROM event_values v JOIN devices d ON (v.device=d.id AND d.contextbroker=v.cb) JOIN contextbroker cb ON (v.cb=cb.name ) where deleted IS null;"; 

			
	$r = mysqli_query($link, $q);
	
	if($r) 
	{
	 $result['status'] = 'ok';
	 $result['log'] .= "\n\r action:get_all_value_latlong. ok " . $q;
	 $result['content'] = array();
        
     while($row = mysqli_fetch_assoc($r)) 
     {
         $eid=$row["organization"].":".$row["contextbroker"].":".$row["device"];
         if ( ($loggedrole=="RootAdmin")|| 
                
                ((
                    (
                    ($row["organization"]==$organization)&&
                    (   
                        ($row["visibility"]=='public'  
                         ||
                         (isset($row["uri"]) && $row["uri"]!="" && 
                          isset($result["delegation"][$row["uri"] . "/" . $row["value_name"]]))&& $result["delegation"][$row["uri"] . "/" . $row["value_name"]]["kind"]=="anonymous")
                        )
                    )
                ) 
                ||
                    (isset($row["uri"]) && $row["uri"]!="" && 
                        ((isset($result["delegation"][$eid])&& $result["delegation"][$eid]["kind"]!="anonymous")
                        ||
                        (isset($result["delegation"][$row["uri"] . "/" . $row["value_name"]]))&& $result["delegation"][$row["uri"] . "/" . $row["value_name"]]["kind"]!="anonymous")
                    ) 
                    
                ||
                 (isset($result["keys"][$eid]))
                    
               )){
             
             array_push($result['content'], $row);
             
         }
	   
	 }
    }
	else{
	   $result['status'] = 'ko';
	   $result['msg'] = 'Error: errors in reading data about location of the device. <br/>' .
						   generateErrorMessage($link);
	   $result['log'] = 'Error: errors in reading data about location of the device. <br/>' .
						   generateErrorMessage($link);					   
	}    
	 my_log($result);
	mysqli_close($link);
}
else
if($action == "delegate_value_list")
{
	$id = mysqli_real_escape_string($link, $_REQUEST['id']);
	$cb = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
	$value_name = mysqli_real_escape_string($link, $_REQUEST['value_name']);
	$visibility = mysqli_real_escape_string($link, $_REQUEST['visibility']);
	$uri =  mysqli_real_escape_string($link, $_REQUEST['uri']);
	$user = mysqli_real_escape_string($link, $_REQUEST['user']);
	
	
	getDelegatorDevice($accessToken, $user, $result, $uri . "/" . $value_name);
	
	 my_log($result);
	 
	
}
else if($action == "delegate_value")
{
	
    $id = mysqli_real_escape_string($link, $_REQUEST['id']);
	$cb = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
	$value_name = mysqli_real_escape_string($link, $_REQUEST['value_name']);
	$visibility = mysqli_real_escape_string($link, $_REQUEST['visibility']);
	$uri =  mysqli_real_escape_string($link, $_REQUEST['uri']);
	$user = mysqli_real_escape_string($link, $_REQUEST['user']);
	$delegated_user = (isset($_REQUEST['delegated_user']))?mysqli_real_escape_string($link, $_REQUEST['delegated_user']):"";
	$delegated_group= (isset($_REQUEST['delegated_group']))?mysqli_real_escape_string($link, $_REQUEST['delegated_group']):"";
	$k1 = mysqli_real_escape_string($link, $_REQUEST['k1']);
	$k2 = mysqli_real_escape_string($link, $_REQUEST['k2']);
	$organization = mysqli_real_escape_string($link, $_REQUEST['organization']);

	//Sara2610
    $deviceName = $id . " ".$cb." ".$value_name;

	if (($delegated_user != "" || $delegated_group != "") && $user != ""){	

		  delegateDeviceValue($uri ."/" . $value_name, $cb, $value_name, $user, $delegated_user, $delegated_group, $accessToken, $k1, $k2, $result);
 
			 //Sara2610 - for logging purpose
			 if($result["status"]=='ok'){
				//Sara2610 - for logging purpose
				logAction($link,$user,'event_values','delegate_value',$deviceName,$organization,'Delegated user: '.$delegated_user,'success');		 
			 }
			 else if($result["status"]=='ko'){
				//Sara2610 - for logging purpose
				logAction($link,$user,'event_values','delegate_value',$deviceName,$organization,'Delegated user: '.$delegated_user,'faliure');		 
			 }
        }
        else 
        {
		  //Sara2610 - for logging purpose
		  logAction($link,$user,'event_values','delegate_value',$deviceName,$organization,'Mandatory parameters not specified','faliure');
          $result["status"]='ko';
          $result["error_msg"]='The value delegation has been called without specifying mandatory parameters. ';
          $result["msg"]='\n the function delegate_value has been called without specifying mandatory parameters';
          $result["log"]='\n the function delegate_value has been called without specifying mandatory parameters';
        } 	   
        my_log($result);
}
else
if($action == "remove_delegation")
{

	$id = mysqli_real_escape_string($link, $_REQUEST['id']);
	$uri = mysqli_real_escape_string($link, $_REQUEST['uri']);	
	$cb = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
	$value_name = mysqli_real_escape_string($link, $_REQUEST['value_name']);
	$user = mysqli_real_escape_string($link, $_REQUEST['user']);
	$delegationId = mysqli_real_escape_string($link, $_REQUEST['delegationId']);
	$organization = mysqli_real_escape_string($link, $_REQUEST['organization']);
	
	removeDelegationValue($accessToken, $user, $delegationId, $result);
	
	//Sara2610
    $deviceName = $id . " ".$cb." ".$value_name;
	
		 //Sara2610 - for logging purpose
	 if($result["status"]=='ok'){
		//Sara2610 - for logging purpose
		logAction($link,$user,'event_values','remove_delegation',$deviceName,$organization,'','success');		 
	 }
	 else if($result["status"]=='ko'){
		//Sara2610 - for logging purpose
		logAction($link,$user,'event_values','remove_delegation',$deviceName,$organization,'','faliure');		 
	 }
	
	/* getDelegatedDevice($accessToken, $user, $result);
	$delegation = $result["delegation"][$uri . "/" . $value_name];
	
	$rowtables = "";
	for ($i=0;$i < count($delegation); $i++)
		{       
		   $rowtables = "<tr><td>" . delegation[$i]["username"]. "</td><td><i class=\"fa fa-minus-square\" onclick=\"removeDelegation('" + $parentTableId + "',this, " . .  "); return true;\"  style=\"font-size:12px; color: #ffcc00\"></i></td></tr>"; // to be fixed 
		}
	
	
	
	$idDelegationToBeRemoved = $result["delegation"][$uri . "/" . $value_name]["delegationId"]
	
	delegateDeviceValue($uri, $cb, $value_name, $user, $delegated_user, $accessToken, $result); */
	 my_log($result);
}	
else 
if($action == "remove_delegate_value")
{

	$id = mysqli_real_escape_string($link, $_REQUEST['id']);
	$uri = mysqli_real_escape_string($link, $_REQUEST['uri']);	
	$cb = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
	$value_name = mysqli_real_escape_string($link, $_REQUEST['value_name']);
	$user = mysqli_real_escape_string($link, $_REQUEST['user']);
	$organization = mysqli_real_escape_string($link, $_REQUEST['organization']);

	//Sara2610
    $deviceName = $id . " ".$cb." ".$value_name;
	
	getDelegatorDevice($accessToken, $user, $result, $uri."/".$value_name);
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
		//Sara2610 - for logging purpose
		logAction($link,$user,'event_values','remove_delegate_value',$deviceName,$organization,'','success');	
		
		$result["status"]="ok";
		$result["msg"]="The delegation to anonymous has been changed";
		$result["log"]="The delegation to anonymous has been changed";
		removeDelegationValue($accessToken, $user, $delegationId, $result);
	}
    else
	{
		//Sara2610 - for logging purpose
		logAction($link,$user,'event_values','remove_delegate_value',$deviceName,$organization,'','faliure');	
		
		$result["status"]="ko";
		$result["error_msg"]="The delegation to anonymous was not found. ";
		$result["msg"]="The delegation to anonymous was not found";
		$result["log"]="The delegation to anonymous was not found";
	}		
	my_log($result);
}
else if($action == "get_cb")
{
	$username = mysqli_real_escape_string($link, $_REQUEST['username']);
	$organization = mysqli_real_escape_string($link, $_REQUEST['organization']);
    $loggedrole= mysqli_real_escape_string($link, $_REQUEST['loggedrole']);
    
    if (!empty($accessToken)) 
	{
        getOwnerShipObject($accessToken, "BrokerID", $result); 
        getDelegatedObject($accessToken, $username, "BrokerID", $result);
	}

	$q = "SELECT * FROM contextbroker";	
	$r = mysqli_query($link, $q);
	
	if($r) 
	{
        $result['status'] = 'ok';
        $result['log'] .= "\n\r action:get_cb. ok ";
        $result['content'] = array();		 
        $result['content_cb'] = array();		 
        $result['content_model'] = array();		 
		 
        while($row = mysqli_fetch_assoc($r)) 
		{
             $idTocheck=$row["organization"].":".$row["name"];
             if (
                 ($loggedrole=='RootAdmin')
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
            {
                    
                    array_push($result['content'], $row);
                    array_push($result['content_cb'], $row);
                
            }
		}
		
        if (!empty($accessToken)) 
        {
            getOwnerShipObject($accessToken, "ModelID", $result); 
            getDelegatedObject($accessToken, $username, "ModelID", $result);
        }
        $q = "SELECT * FROM model";
        $m = mysqli_query($link, $q);
        if($m) 
	{
		
		 
		 while($row = mysqli_fetch_assoc($m)) 
		{
             $idTocheck=$row["organization"].":".$row["name"];
             if (
                 ($loggedrole=='RootAdmin')
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
            {

                   array_push($result['content_model'], $row);
                
            }
		}
		
	}
        else
	{
		$result['status'] = 'ko';
	    $result['msg'] = 'Error: errors in getting models. <br/>' .
						   generateErrorMessage($link);
	    $result['log'] = 'Error: errors in getting models. <br/>' .
						   generateErrorMessage($link);				   
	}
        
	} 
	else
	{
		$result['status'] = 'ko';
	    $result['msg'] = 'Error: errors in getting context brokers. <br/>' .
						   generateErrorMessage($link);
	    $result['log'] = 'Error: errors in getting context brokers. <br/>' .
						   generateErrorMessage($link);				   
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




