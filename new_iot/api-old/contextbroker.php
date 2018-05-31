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

$result=array();	
/* all the primitives return an array "result" with the following structure

result["status"] = ok/ko; reports the status of the operation (mandatory)
result["msg"] a message related to the execution of the operation (optional)
result["content"] in case of positive execution of the operation the content extracted from the db (optional)

This array should be encoded in json
*/	
	
if ($action=="insert")
{  $name = mysqli_real_escape_string($link, $_REQUEST['name']);
	$ip = mysqli_real_escape_string($link, $_REQUEST['ip']);
	$port = mysqli_real_escape_string($link, $_REQUEST['port']);
	$protocol = mysqli_real_escape_string($link, $_REQUEST['protocol']);
	$latitude = mysqli_real_escape_string($link, $_REQUEST['latitude']);
	$longitude = mysqli_real_escape_string($link, $_REQUEST['longitude']);
	$login = mysqli_real_escape_string($link, $_REQUEST['login']);
	$password = mysqli_real_escape_string($link, $_REQUEST['password']);

	$q = "INSERT INTO contextbroker(name, ip, protocol, port, latitude, longitude, login, password) " .
		 "VALUES('$name', '$ip', '$protocol',  '$port', '$latitude', '$longitude', '$login', '$password' )";
	$r = mysqli_query($link, $q);

	if($r)
	{
		$result["status"]='ok';
	}
	else
	{
		 $result["status"]='ko';
		 $result["msg"] = '<script type="text/javascript">'.
						 'alert("Error: An error occurred when registering the context broker $name. <br/>' .
						   mysqli_error($link) . $q .
						   ' Please enter again the context broker")'. '</script>';
	}
	echo json_encode($result);
	mysqli_close($link);
}
else
if ($action=="update")
{  $name = mysqli_real_escape_string($link, $_REQUEST['name']);
	// $uri = mysqli_real_escape_string($link, $_REQUEST['uri']);
	$ip = mysqli_real_escape_string($link, $_REQUEST['ip']);
	$port = mysqli_real_escape_string($link, $_REQUEST['port']);
	$protocol = mysqli_real_escape_string($link, $_REQUEST['protocol']);
	$latitude = mysqli_real_escape_string($link, $_REQUEST['latitude']);
	$longitude = mysqli_real_escape_string($link, $_REQUEST['longitude']);
	$login = mysqli_real_escape_string($link, $_REQUEST['login']);
	$password = mysqli_real_escape_string($link, $_REQUEST['password']);
	
	
	$q = "UPDATE contextbroker SET name = '$name', ip = '$ip', port = '$port', protocol = '$protocol', created = '$created', latitude = '$latitude', longitude = '$longitude', login = '$login', password = '$password' WHERE name = '$name'";
	$r = mysqli_query($link, $q);

	if($r)
	{
		$result["status"]='ok';
	}
	else
	{
		 $result["status"]='ko';
		 $result["msg"] = '<script type="text/javascript">'.
						 'alert("Error: An error occurred when updating the context broker $name. <br/>' .
						   mysqli_error($link) . $q .
						   ' Please enter again the context broker")'. '</script>';
	}
	echo json_encode($result);
	mysqli_close($link);
}
else if ($action=="delete")
{
      $name = mysqli_real_escape_string($link, $_REQUEST['name']);      
      $q = "DELETE FROM contextbroker WHERE name = '$name'";
      $r = mysqli_query($link, $q);
      if($r)
	  {
		$result["status"]='ok';
	  }
	  else
	  {
		 $result["status"]='ko';
		 
		 $result["msg"] = 'Context broker <b>' . $name . '</b> &nbsp; deletion failed, ' .
						   mysqli_error($link) . $q .
						   ' Please enter again.';
	  }
	  echo json_encode($result);
	  mysqli_close($link);
}
else if($action == 'get_contextbroker') 
{
	$name = mysqli_real_escape_string($link, $_REQUEST['name']);
	$q = "SELECT * FROM contextbroker WHERE name = '$name'";
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
						 'alert("Error: errors in reading data for the context broker $name. <br/>' .
						   mysqli_error($link) . $q .
						   '")'. '</script>';
	}
	echo json_encode($result);
	mysqli_close($link); 
}
else if($action == 'get_all_contextbroker')
{
	$q = "SELECT * FROM contextbroker";
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
else if($action == "get_subset_contextbroker")
{

    $selection= json_decode($_REQUEST['select']);
	$a=0;
	$cond="";
	if (count($selection)!=0)
	{
	    
		while ($a < count($selection))
		{
			 $sel = $selection[$a];
			 $cond .= " (name = '" . $sel->name . "') ";
			 if ($a != count($selection)-1)  $cond .= " OR ";
			 $a++;
		 }
		
		$q = "SELECT DISTINCT * FROM contextbroker WHERE " . $cond;
		// echo $q;
	}
    else
	    $q = "SELECT DISTINCT * FROM contextbroker";
	
    $r = mysqli_query($link, $q);
	
	if($r) 
	{
	 $result['status'] = 'ok';
	 $result['content'] = array();
     while($row = mysqli_fetch_assoc($r)) 
     {
	   array_push($result['content'], $row);
	 }
      $result['msg'] = $q;
	}
	else{
	   $result['status'] = 'ko';
	   $result['msg'] = '<script type="text/javascript">'.
						 'alert("Error: errors in reading data about contextbrokers. <br/>' .
						   mysqli_error($link) . $q .
						   '")'. '</script>';
	}    
	echo json_encode($result);
	mysqli_close($link);
}
else if($action == "get_all_contextbroker_latlong")
{

	$q = "SELECT name, latitude, longitude FROM contextbroker";
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
else if ($action == 'get_config_data')
{

   $q  = "SELECT d.id, d.devicetype AS entityType, d.kind, d.protocol,  d.longitude, d.latitude, d.contextBroker, cb.name, cb.protocol as type, cb.ip, cb.port FROM devices d JOIN contextbroker cb ON d.contextBroker = cb.name  ORDER BY id;";
   $r = mysqli_query($link, $q);

	if($r) 
	{
	
	    $result['status'] = 'ok';
		$res= array();
		while($row = mysqli_fetch_assoc($r)) 
		{
			$id = $row["id"];
			unset($row["id"]);
			$res[$id]=$row;
		}
		$result['content'] =$res;
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
	else if ($action == 'get_config_data_values')
	{
	   $res = array();
	   $q  = "SELECT v.cb, v.device, v.value_name, v.data_type, v.value_type, v.editable, v.value_unit, v.healthiness_criteria, d.latitude, d.longitude, d.protocol FROM `event_values` v JOIN devices d ON (v.device=d.id and v.cb = d.contextbroker)  ORDER BY v.value_name;";
	   $r = mysqli_query($link, $q);

		if($r) 
		{
		   $result['status'] = 'ok';
		   while($row = mysqli_fetch_assoc($r)) 
		   {
			array_push($res, $row);
		   }
		   $result['content'] =$res;
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
	else if ($action == 'get_default')
	{
	   $res = array();
	   $q  = "SELECT policyname, contextbroker, protocol, format, healthiness_criteria, healthiness_value FROM defaultpolicy WHERE active=1;";
	   $r = mysqli_query($link, $q);

		if($r) 
		{
		   $result['status'] = 'ok';
		   $row = mysqli_fetch_assoc($r);
		   $result['policyname'] = $row["policyname"];
		   $result['contextbroker'] = $row["contextbroker"];
		   $result['protocol'] = $row["protocol"];
		   $result['format'] = $row["format"];
		   $result['healthiness_criteria'] = $row["healthiness_criteria"];
		   $result['healthiness_value'] = $row["healthiness_value"];
		} 
		else
		{
		  $result['status'] = 'ko';
		  $result['msg'] = 'Error: errors in reading data about default policies ' .
						   mysqli_error($link) . $q;
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
