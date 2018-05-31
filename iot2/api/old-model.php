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

$result=array("status"=>"","msg"=>"","content"=>"");	
/* all the primitives return an array "result" with the following structure

result["status"] = ok/ko; reports the status of the operation (mandatory)
result["msg"] a message related to the execution of the operation (optional)
result["content"] in case of positive execution of the operation the content extracted from the db (optional)

This array should be encoded in json
*/	


if ($action=="get_models")
{
    $res=array(); 
    $q  = "SELECT * FROM model JOIN defaultpolicy ON (policy=policyname);";
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
else 
{
	$result['status'] = 'ko';
	$result['msg'] = 'invalid action ' . $action;
	echo json_encode($result);
}


