
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

$result=array("status"=>"","msg"=>"","content"=>"");	
	
/* all the primitives return an array "result" with the following structure

result["status"] = ok/ko; reports the status of the operation (mandatory)
result["msg"] a message related to the execution of the operation (optional)
result["content"] in case of positive execution of the operation the content extracted from the db (optional)

This array should be encoded in json
*/	


require '../sso/autoload.php';
use Jumbojett\OpenIDConnectClient;


if (isset($_REQUEST['token'])) {
  $oidc = new OpenIDConnectClient('https://www.snap4city.org', $clientId, $clientSecret);
  $oidc->providerConfigParam(array('token_endpoint' => 'https://www.snap4city.org/auth/realms/master/protocol/openid-connect/token'));
  $tkn = $oidc->refreshToken($_REQUEST['token']);
  $accessToken = $tkn->access_token;
}
else {$accessToken="";}

	
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
	// $order = mysqli_real_escape_string($link, $_REQUEST['order']);

	$hc="";
	if ($healthiness_criteria=="refresh_rate") $hc="value_refresh_rate";
	 else if ($healthiness_criteria=="different_values") $hc="different_values";
	 else $hc="value_bounds";
	
	$q = "INSERT INTO event_values(cb, device, value_name, data_type, value_type, editable, value_unit, healthiness_criteria, `$hc`) " .
		 "VALUES('$cb', '$device', '$value_name',  '$data_type', '$value_type', '$editable', '$value_unit', '$healthiness_criteria', '$healthiness_value')"; //, '$order' )";
	$r = mysqli_query($link, $q);

	if($r)
	{
		modify_valueKB($link, $device, $cb, $result);
        $result["editable"]=$editable;		
        if($result["content"]==null) $result["active"]=false;  else $result["active"]=true;
		$result["msg"] .= '\n insertion in the db of the value was ok';
		if (!isset($result["status"])) $result["status"]="ok";
		else
		if ($result["status"]=="ko")  $result["msg"] .= '\n an error occurred in the KB or context broker';
	}
	else
	{
		 $result["status"]='ko';
		 $result["msg"] = '<script type="text/javascript">'.
						 'alert("Error: An error occurred when registering the value $value_name. <br/>' .
						   mysqli_error($link) . $q .
						   ' Please enter again the value_name")'. '</script>';
	}
	echo json_encode($result);
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
	// $order = mysqli_real_escape_string($link, $_REQUEST['order']);
	
	$hc="";
	if ($healthiness_criteria=="refresh_rate") $hc="value_refresh_rate";
	 else if ($healthiness_criteria=="different_values") $hc="different_values";
	 else $hc="value_bounds";
	
	// , `order` = '$order'
	$q = "UPDATE event_values SET cb = '$cb', device = '$device', value_name = '$value_name', data_type = '$data_type', value_type = '$value_type', editable = '$editable', value_unit = '$value_unit', healthiness_criteria = '$healthiness_criteria', $hc = '$healthiness_value' 
	WHERE cb = '$cb' and device='$device' and value_name='$value_name'";
	$r = mysqli_query($link, $q);

	if($r)
	{
		modify_valueKB($link, $device, $cb,$result);	   
        $result["editable"]=$editable;
        // echo "prima,,,,";
		// print_r($result);		
        if($result["content"]==null) $result["active"]=false;  else $result["active"]=true;
		$result["msg"] .= '\n update in the db of the value was ok';
		if (!isset($result["status"])) $result["status"]="ok";
		else {if ($result["status"]=="ko")  $result["msg"] .= '\n an error occurred in the KB or context broker';}
		// print_r($result);
	}
	else
	{
		 $result["status"]='ko';
		 $result["msg"] = 'event_values <b>' . $value_name . '</b> &nbsp; update failed, ' .
         mysqli_error($link) . $q .
         ' Please enter again.';
	}
	echo json_encode($result);
	mysqli_close($link);
}
else if ($action=="delete")
{
      $cb = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
	  $device = mysqli_real_escape_string($link, $_REQUEST['device']);
	  $value_name = mysqli_real_escape_string($link, $_REQUEST['value_name']);
	  $editable = mysqli_real_escape_string($link, $_REQUEST['editable']);
	  
      
      $q = "DELETE FROM event_values WHERE cb = '$cb' and device='$device' and value_name='$value_name'";
      $r = mysqli_query($link, $q);
      if($r)
	  {
		modify_valueKB($link, $device, $cb, $result);	   
        $result["editable"]=$editable;		
        if($result["content"]==null) $result["active"]=false;  else $result["active"]=true;
		$result["msg"] .= '\n delete in the db of the value was ok';
		if (!isset($result["status"])) $result["status"]="ok";
		else {if ($result["status"]=="ko")  $result["msg"] .= '\n an error occurred in the KB or context broker';}
		
	  }
	  else
	  {
		 $result["status"]='ko';
		 $result["msg"] = 'event_values <b>' . $value_name . '</b> &nbsp; deletion failed, ' .
         mysqli_error($link) . $q .
         ' Please enter again.';
	  }
	  echo json_encode($result);
	  mysqli_close($link);
}

else if($action == 'get_event_value') 
{


	$cb = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
	$device = mysqli_real_escape_string($link, $_REQUEST['device']);
	$value_name = mysqli_real_escape_string($link, $_REQUEST['value_name']);
	
	$q = "SELECT * FROM event_values WHERE cb = '$cb' and device='$device' and value_name='$value_name'";
	$r = mysqli_query($link, $q);

	if($r) 
	{
		$row = mysqli_fetch_assoc($r);
		$result['status'] = 'ok';
		$result['content'] = $row;
	} 
	else
	{
		$result['status'] = 'ko';
		$result['msg'] = '<script type="text/javascript">'.
						 'alert("Error: errors in reading data for the event_value $value_name. <br/>' .
						   mysqli_error($link) . $q .
						   '")'. '</script>';
	}
	echo json_encode($result);
	mysqli_close($link); 
}
else if($action == 'get_all_event_value')
{
    $privateDevices= "";
    if (!empty($accessToken)) 
	{ 
	 $privateDevices = getPrivateDevice($accessToken, $result); 
	}

	if ($privateDevices=="")
	$q = "SELECT v.*, d.kind, d.latitude, d.longitude, d.visibility,
	      CASE WHEN d.mandatoryproperties AND d.mandatoryvalues THEN \"active\" ELSE \"iddle\" END 
	      AS status1, d.visibility FROM event_values v JOIN devices d ON (v.device=d.id AND d.contextbroker=v.cb) where d.visibility=\"public\"";
	else
	$q = "SELECT v.*, d.kind, d.latitude, d.longitude, d.visibility,
	      CASE WHEN d.mandatoryproperties AND d.mandatoryvalues THEN \"active\" ELSE \"iddle\" END 
	      AS status1, d.visibility FROM event_values v JOIN devices d ON (v.device=d.id AND d.contextbroker=v.cb) where d.visibility=\"public\" or (visibility=\"private\" and $privateDevices) ";
		
	
	$r = mysqli_query($link, $q);
	if($r) 
	{
		$result['status'] = 'ok';
		$result['content'] = array();
		
		 while($row = mysqli_fetch_assoc($r)) 
		{
	        $rec = array();
			$rec["cb"]= $row["cb"];
			$rec["device"]= $row["device"];
			$rec["value_name"]= $row["value_name"];
			$rec["data_type"]= $row["data_type"];
			$rec["value_type"]= $row["value_type"];
			$rec["editable"]= $row["editable"];
			$rec["value_unit"]= $row["value_unit"];
			$rec["healthiness_criteria"]= $row["healthiness_criteria"];
			// $rec["different_values"]= $row["different_values"];
			//$rec["value_bounds"]= $row["value_bound"];
			$rec["order"]= $row["order"];
			$rec["value_refresh_rate"]= $row["value_refresh_rate"];
			$rec["latitude"]= $row["latitude"];
			$rec["longitude"]= $row["longitude"];
			$rec["visibility"]= $row["visibility"];
			$rec["kind"]= $row["kind"];
            $rec["status1"]= $row["status1"];			

			if (isset($result["keys"][$rec["device"]]))
			{
				$rec["k1"]=$result["keys"][$rec["device"]]["k1"];
				$rec["k2"]=$result["keys"][$rec["device"]]["k2"];
            }
			else{
				$rec["k1"]="";
				$rec["k2"]="";	
			}
     		array_push($result['content'], $rec);
		}
	} 
	else
	{
		$result['status'] = 'ko';
		$result['msg'] = '<script type="text/javascript">'.
						 'alert("Error: errors in reading data about context brokers. <br/>' .
						   mysqli_error($link) . $q .
						   '")'. '</script>';
	}

	echo json_encode($result);
	mysqli_close($link); 
}
else if($action == 'get_all_private_event_value')
{
    $privateDevices= "";
    if (!empty($accessToken)) 
	{ 
	 $privateDevices = getPrivateDevice($accessToken, $result); 
	}	
	if ($privateDevices!="")
	{	
      	$q = "SELECT v.*, d.kind, d.latitude, d.longitude, d.visibility,
	      CASE WHEN d.mandatoryproperties AND d.mandatoryvalues THEN \"active\" ELSE \"iddle\" END 
	      AS status1, d.visibility FROM event_values v JOIN devices d ON (v.device=d.id AND d.contextbroker=v.cb) where visibility=\"private\" and $privateDevices ";
		
	
		$r = mysqli_query($link, $q);
		if($r) 
		{
			$result['status'] = 'ok';
			$result['content'] = array();
			 while($row = mysqli_fetch_assoc($r)) 
			{
				$rec = array();
				$rec["cb"]= $row["cb"];
				$rec["device"]= $row["device"];
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
				$rec["visibility"]= $row["visibility"];
				$rec["kind"]= $row["kind"];
				$rec["status1"]= $row["status1"];			

				if (isset($result["keys"][$rec["device"]]))
				{
					$rec["k1"]=$result["keys"][$rec["device"]]["k1"];
					$rec["k2"]=$result["keys"][$rec["device"]]["k2"];
				}
				else{
					$rec["k1"]="";
					$rec["k2"]="";	
				}				
				array_push($result['content'], $rec);
			}
		} 
		else
		{
			$result['status'] = 'ko';
			$result['msg'] = '<script type="text/javascript">'.
							 'alert("Error: errors in reading data about context brokers. <br/>' .
							   mysqli_error($link) . $q .
							   '")'. '</script>';
		}
	}
	echo json_encode($result);
	mysqli_close($link); 
}
else if($action == 'get_subset_event_value')
{
    $selection= json_decode($_REQUEST['select']);
	if (!empty($accessToken))
        {
         $privateDevices = getPrivateDevice($accessToken, $result);
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
		
		$q = "SELECT v.*, d.kind, d.latitude, d.longitude, CASE WHEN d.mandatoryproperties AND d.mandatoryvalues THEN \"active\" ELSE \"iddle\" END AS status, d.visibility FROM event_values v JOIN devices d ON (v.device=d.id AND d.contextbroker=v.cb) WHERE " . $cond;
		// echo $q;
	}
    else
	    $q = "SELECT  * FROM event_values";
	
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
			$rec["cb"]= $row["cb"];
				$rec["device"]= $row["device"];
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
				$rec["latitude"]= $row["latitude"];
				$rec["longitude"]= $row["longitude"];
				$rec["visibility"]= $row["visibility"];
				$rec["kind"]= $row["kind"];
				$rec["status1"]= $row["status1"];			

			
            if (isset($result["keys"][$rec["device"]]))
			{
				$rec["k1"]=$result["keys"][$rec["device"]]["k1"];
				$rec["k2"]=$result["keys"][$rec["device"]]["k2"];
            }
			else{
				$rec["k1"]="";
				$rec["k2"]="";	
			}
	        array_push($result['content'], $rec); 
	   }	
		}
	} 
	else
	{
		$result['status'] = 'ko';
		$result['msg'] = '<script type="text/javascript">'.
						 'alert("Error: errors in reading data about values. <br/>' .
						   mysqli_error($link) . $q .
						   '")'. '</script>';
	}

	echo json_encode($result);
	mysqli_close($link); 
}
else if($action == 'get_value_unit_data') 
{
	
	$value_type = mysqli_real_escape_string($link, $_REQUEST['value_type']);
	
	$q = "SELECT * FROM value_types WHERE value_type = '$value_type'";
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
else if($action == "get_value_latlong")
{
    $id = mysqli_real_escape_string($link, $_REQUEST['id']);
	$cb = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
	
	$q = "SELECT d.id, d.contextbroker, v.value_name, d.latitude, d.longitude  FROM devices d JOIN event_values v ON (d.id=v.device and d.contextbroker=v.cb) WHERE d.id='$id' and d.contextbroker='$cb'";
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
						 'alert("Error: errors in reading data about location of the device. <br/>' .
						   mysqli_error($link) . $q .
						   '")'. '</script>';
	}    
	echo json_encode($result);
	mysqli_close($link);
}
else if($action == "get_all_value_latlong")
{
    
	$privateDevices= "";
    if (!empty($accessToken)) 
	{ 
	 $privateDevices = getPrivateDevice($accessToken, $result); 
	}
	
	if ($privateDevices=="")
		$q = "SELECT d.id, d.contextbroker, v.value_name, d.latitude, d.longitude  FROM devices d JOIN event_values v ON (d.id=v.device and d.contextbroker=v.cb) WHERE d.visibility=\"public\"";
	else
		$q = "SELECT d.id, d.contextbroker, v.value_name, d.latitude, d.longitude  FROM devices d JOIN event_values v ON (d.id=v.device and d.contextbroker=v.cb) WHERE d.visibility=\"public\" or (visibility=\"private\" and $privateDevices)";
	
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
						 'alert("Error: errors in reading data about location of the device. <br/>' .
						   mysqli_error($link) . $q .
						   '")'. '</script>';
	}    
	echo json_encode($result);
	mysqli_close($link);
}
else 
{
	$result['status'] = 'ko';
	$result['msg'] = 'invalid action ' . $action;
	echo json_encode($result);
}


