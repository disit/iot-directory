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


$result=array("status"=>"","msg"=>"","content"=>"","log"=>"");	
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

//session_start();
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
$node_data = json_decode(file_get_contents("php://input"));
if($node_data != null){
//if(isset($_POST['data']) && !empty($_POST['data']) && isset($_POST['data_from_nodeJs']) && !empty($_POST['data_from_nodeJs'])) 
//{
// $node_data=$_POST['data'];
$data_parallel=$node_data->data_parallel;
$action= $node_data->action;
}
else if(isset($_REQUEST['action']) && !empty($_REQUEST['action'])) 
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


//sara 1510 start
if ($action=="insert")
{   

	//Sara2510 - for logging purpose
	$username = mysqli_real_escape_string($link, $_REQUEST['username']);
	$usernameNotHashed = $username;
	$username = md5($username);
	$organization= mysqli_real_escape_string($link, $_REQUEST['organization']);
	
	$id = mysqli_real_escape_string($link, $_REQUEST['id']);
	$contextbroker = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);  
	$format = mysqli_real_escape_string($link, $_REQUEST['format']);  
	$selector = mysqli_real_escape_string($link, $_REQUEST['selector']);  
	$kind = mysqli_real_escape_string($link, $_REQUEST['kind']);  
	$value_type = mysqli_real_escape_string($link, $_REQUEST['value_type']);  
	$value_unit = mysqli_real_escape_string($link, $_REQUEST['value_unit']);  
	$data_type= mysqli_real_escape_string($link, $_REQUEST['data_type']);
	$structure_flag= mysqli_real_escape_string($link, $_REQUEST['structure_flag']);
    

	$insertquery="INSERT INTO `extractionRules`(`id`, `contextbroker`, `format`, `selector`, `kind`, `value_type`, `value_unit`, `data_type`, `structure_flag`, `organization`, `username`) 
	VALUES ('$id','$contextbroker','$format', '$selector','$kind','$value_type','$value_unit','$data_type','$structure_flag','$organization','$username');";
	$r1 = mysqli_query($link, $insertquery);
	
	if($r1){
		$result['status'] = 'ok';
	}
	else{
	  $result["status"]='ko';
	  $result["msg"] .= "failure"; 
	  $result["log"] .= "\n Problem in inserting rule". generateErrorMessage($link); 
	}
		//Sara2510 - For logging purpose
	$deviceName = $id . " ".$contextbroker;

	if($result["status"]=="ok"){
			logAction($link,$usernameNotHashed,'extractionRule','insert',$deviceName,$organization,'','success');		
	}
	else if($result["status"]=="ko"){
			logAction($link,$usernameNotHashed,'extractionRule','insert',$deviceName,$organization,'','faliure');				
	}
    
	my_log($result);
	mysqli_close($link);
 
}
else if ($action=="get_cb_details"){
	$contextbroker = mysqli_real_escape_string($link,$_REQUEST['cb']);
	$username = mysqli_real_escape_string($link,$_REQUEST['username']);
	$organization = mysqli_real_escape_string($link,$_REQUEST['organization']);
	
	$q = "SELECT * FROM contextbroker WHERE name= '$contextbroker';";
	$r = mysqli_query($link, $q);
	
	$context = array();
	if($r){
		while($row = mysqli_fetch_assoc($r)) 
        {
			$rec= array();
			$rec["protocol"]=$row["protocol"];
			$rec["ip"]=$row["ip"];
			$rec["accessLink"]=$row["accesslink"];
			$rec["port"]=$row["port"];
			$rec["protocol"]=$row["protocol"];
			$rec["username"]=md5($username);
			$rec["apikey"]=$row["apikey"];
			$rec["kind"]=$row["kind"];
			$rec["path"]=$row["path"];

			array_push($context, $rec);         
		}	
		$result['status'] = 'ok';
		$result['content'] = $context;		
	}
	else{
	  $result["status"]='ko';
	  $result["msg"] .= "failure"; 
	  $result["log"] .= "\n Problem in get count temporary devices". generateErrorMessage($link); 
	 }
	 my_log($result);
     mysqli_close($link);
}

//sara 1510 end
else if ($action=="get_rules"){
	$username = mysqli_real_escape_string($link,$_REQUEST['username']);
	$organization = mysqli_real_escape_string($link,$_REQUEST['organization']);
	$loggedrole = mysqli_real_escape_string($link,$_REQUEST['loggedrole']);
	$username = md5($username);

	$q = "SELECT contextbroker, id, selector, kind, format, data_type, value_type,value_unit, structure_flag
	FROM extractionRules"; // WHERE username = '$username' AND deleted IS null;";
	//$r = mysqli_query($link, $q);	
	if($loggedrole == 'Root' || $loggedrole == 'RootAdmin'){
		$r=create_datatable_data($link,$_REQUEST,$q,"");
	}
	else{
		$r=create_datatable_data($link,$_REQUEST,$q, "username = '$username' AND organization='$organization'");
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
      //$result['status'] = 'ok';
	  //$result['content'] = array();
	  $rules = array();
	  $result["log"]= "\r\n action=get_rules \r\n";
      	 
	 while($row = mysqli_fetch_assoc($r)) 
        {	
			$selectedrows++;
			if (!$tobelimited || ($tobelimited && $selectedrows >= $start && $selectedrows < ($start+$offset)))
			{
				$rec= array();
				$rec["id"]=$row["id"];
				$rec["contextbroker"]=$row["contextbroker"];
				$rec["selector"]=$row["selector"];
				$rec["format"]=$row["format"];
				$rec["kind"]=$row["kind"];
				$rec["data_type"]=$row["data_type"];
				$rec["value_type"]=$row["value_type"];
				$rec["value_unit"]=$row["value_unit"];
				$rec["structure_flag"]=$row["structure_flag"];
				
				array_push($rules, $rec);           
			}
		}
	 $output= format_result($_REQUEST["draw"], $selectedrows+1, $selectedrows+1, $rules, "", "\r\n action=get_rules \r\n", 'ok');	
    }
	else{
	$output= format_result($_REQUEST["draw"], 0, 0, null, 'Error: errors in reading data about  rules. <br/>' . generateErrorMessage($link), 'ko');

	}    
	my_log($output);
	mysqli_close($link);
}
else if ($action=="update")
{   
	$result["msg"] .= "update"; 
	//Sara2210 start
	$username = mysqli_real_escape_string($link, $_REQUEST['username']);
	$organization = mysqli_real_escape_string($link, $_REQUEST['organization']);
	//Sara2210 end
	$id = mysqli_real_escape_string($link, $_REQUEST['id']);
	$old_id = mysqli_real_escape_string($link, $_REQUEST['old_id']);
	$contextbroker = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);  
	$old_cb = mysqli_real_escape_string($link, $_REQUEST['old_cb']);  
	$selector = mysqli_real_escape_string($link, $_REQUEST['selector']);  
	$format = mysqli_real_escape_string($link, $_REQUEST['format']);  
	$kind = mysqli_real_escape_string($link, $_REQUEST['kind']);  
	$data_type = mysqli_real_escape_string($link, $_REQUEST['data_type']);  
	$value_type = mysqli_real_escape_string($link, $_REQUEST['value_type']);  
	$value_unit = mysqli_real_escape_string($link, $_REQUEST['value_unit']);  
	$structure_flag = mysqli_real_escape_string($link, $_REQUEST['structure_flag']);  

	//Sara2510 - for logging purpose
	$deviceName= $old_id . " ".$old_cb; 
					
	// The following code has to decide the search key in the tables, then the behavior is analogous.
	$q = "UPDATE extractionRules 
	SET id='$id', 
	contextBroker='$contextbroker',
	selector='$selector', 
	format='$format', 
	kind='$kind', 
	data_type='$data_type', 
	value_type='$value_type', 
	value_unit='$value_unit', 
	structure_flag='$structure_flag'
	WHERE id='$old_id' and contextBroker='$old_cb';";
	
	$r = mysqli_query($link, $q);
		
	if($r){
		$result['status'] = 'ok';
	}
	else{
	  $result["status"]='ko';
	  $result["msg"] .= "failure"; 
	  $result["log"] .= "\n Problem in updating rule $id". generateErrorMessage($link); 
	}
		//Sara2510 - For logging purpose
	$deviceName = $id . " ".$contextbroker;

	if($result["status"]=="ok"){
			logAction($link,$username,'extractionRule','update',$deviceName,$organization,'','success');		
	}
	else if($result["status"]=="ko"){
			logAction($link,$username,'extractionRule','update',$deviceName,$organization,'','faliure');				
	}
    
	my_log($result);
	mysqli_close($link);
	
	
}  
else if ($action=="delete_rule")
{
     $id = mysqli_real_escape_string($link,$_REQUEST['id']);
	 $cb = mysqli_real_escape_string($link,$_REQUEST['contextbroker']);

	 $organization = mysqli_real_escape_string($link,$_REQUEST['organization']);
	 
	 //Sara2510
	 $username = mysqli_real_escape_string($link,$_REQUEST['username']);
	 $deviceName = $id . " ".$cb;
	
	// if ($result["status"]=='ko') return $result;
			
	//  $query = "UPDATE temporary_devices SET deleted = '". date("Y/m/d") . "' WHERE id = '$id' and contextBroker='$cb'";
     $query = "DELETE FROM extractionRules  WHERE id = '$id' and contextBroker='$cb'";
	 $r = mysqli_query($link, $query);

     if($r)
	 {
		 $result["status"]='ok';
		 $result["msg"] .= "\n Device $id/$cb and corresponding values correctly removed from temporary devices"; 
		 $result["log"] .= "\n Device $id/$cb and corresponding values correctly removed from temporary devices";
		
		//Sara2510
		 logAction($link,$username,'extractionRules','delete',$deviceName,$organization,'','success');
	 }
	 else
	 {
	  $result["status"]='ko';
	  $result["msg"] .= "\n Problem in deleting the device $id: " . generateErrorMessage($link); 
	  $result["log"] .= "\n Problem in deleting the device $id: " . $query . " " . generateErrorMessage($link); 
	//Sara2510
	logAction($link,$username,'extractionRules','delete',$deviceName,$organization,'','faliure');
	 }
	 my_log($result);
     mysqli_close($link);
}

else if($action == 'get_param_values')
{	
	// $result = array();
	$result['status'] = 'ok'; 
	$result['value_type'] = generatelabels($link);
	$result['data_type'] = generatedatatypes($link);
	$result['value_unit'] = generateunits($link);
	//$result['log'] .= '\n\naction:get_param_values';
	my_log($result);
	mysqli_close($link); 

}

//----Sara end---
else 
{
	$result['status'] = 'ko';
	$result['msg'] = 'invalid action ' . $action;
	$result['log'] = 'invalid action ' . $action;
	my_log($result);
}
