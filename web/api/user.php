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

$result=array("status"=>"","msg"=>"","content"=>"","log"=>"");	
/* all the primitives return an array "result" with the following structure

result["status"] = ok/ko; reports the status of the operation (mandatory)
result["msg"] a message related to the execution of the operation (optional)
result["content"] in case of positive execution of the operation the content extracted from the db (optional)
result["log"] keep trace of the operations executed on the db

This array should be encoded in json
*/	

if ($action=="insert")
{   
    //Sara711 - for logging purpose
	$loggedUser = mysqli_real_escape_string($link, $_REQUEST['loggedUser']);

    $username = mysqli_real_escape_string($link, $_REQUEST['username']);
	$name = mysqli_real_escape_string($link, $_REQUEST['firstName']);
	$surname = mysqli_real_escape_string($link, $_REQUEST['lastName']);
	$organization = mysqli_real_escape_string($link, $_REQUEST['organization']);
	$admin = mysqli_real_escape_string($link, $_REQUEST['userType']);
	$email = mysqli_real_escape_string($link, $_REQUEST['email']);

	$q = "INSERT INTO users(username, name, surname, organization, admin, email) " .
		 "VALUES('$username', '$name', '$surname',  '$organization', '$admin', '$email' )";
	$r = mysqli_query($link, $q);
	if($r)
	{
		//Sara611 - for logging purpose
		logAction($link,$loggedUser,'user','insert',$username,$organization,'','success');

		$result["status"]='ok';
		$result["log"] .="\n\r action: insert ok " . $q;
	}
	else
	{
		//Sara611 - for logging purpose
		logAction($link,$loggedUser,'user','insert',$username,$organization,'','faliure');
		
		 $result["status"]='ko';
		 $result["msg"] = '<script type="text/javascript">'.
						 'alert("Error: An error occurred when registering the user $name. <br/>' .
						   mysqli_error($link) .
						   ' Please enter again the user")'. '</script>';
		$result["log"] = '\n\r Error: An error occurred when registering the user $name.' .
						   mysqli_error($link) . $q;				   
	}
	my_log($result);
	mysqli_close($link);
}	

else if($action=="delete")
{
     //Sara711 - for logging purpose
	 $loggedUser = mysqli_real_escape_string($link, $_REQUEST['loggedUser']);

      $username = mysqli_real_escape_string($link, $_REQUEST['username']);      
      $organization = mysqli_real_escape_string($link, $_REQUEST['organization']);      
      $q = "DELETE FROM users WHERE username = '$username'";
      $r = mysqli_query($link, $q);
      if($r)
	  {
	    //Sara611 - for logging purpose
		logAction($link,$loggedUser,'user','delete',$username,$organization,'','success');
		
	    $result["status"]='ok';
		$result["log"] .="\n\r action: delete ok " . $q;
	  }
	  else
	  {
	    //Sara611 - for logging purpose
		logAction($link,$loggedUser,'user','delete',$username,$organization,'','faliure');
		
		 $result["status"]='ko';
		 
		 $result["msg"] = 'User <b>' . $name . '</b> &nbsp; deletion failed, ' .
						   mysqli_error($link) . 
						   ' Please enter again.';
		$result["log"] = 'User <b>' . $name . '</b> &nbsp; deletion failed, ' .
						   mysqli_error($link) . $q;				   
	  }
	  my_log($result);
	  mysqli_close($link);
}

else if ($action=="update")
{
     //Sara711 - for logging purpose
	 $loggedUser = mysqli_real_escape_string($link, $_REQUEST['loggedUser']);

	$username = mysqli_real_escape_string($link, $_REQUEST['username']);
	$name = mysqli_real_escape_string($link, $_REQUEST['firstName']);
	$surname = mysqli_real_escape_string($link, $_REQUEST['lastName']);
	$organization = mysqli_real_escape_string($link, $_REQUEST['organization']);
	$admin = mysqli_real_escape_string($link, $_REQUEST['userType']);
	$status = mysqli_real_escape_string($link, $_REQUEST['userStatus']);
	$email = mysqli_real_escape_string($link, $_REQUEST['email']);
	
	
	$q = "UPDATE users SET username = '$username', name = '$name', surname = '$surname', organization = '$organization', admin = '$admin', status = '$status', email = '$email' WHERE name = '$name'";
	$r = mysqli_query($link, $q);

	if($r)
	{
	    //Sara611 - for logging purpose
		logAction($link,$loggedUser,'user','update',$username,$organization,'','success');
		
		$result["status"]='ok';
		$result["log"] .="\n\r action: update ok " . $q;
	}
	else
	{
	    //Sara611 - for logging purpose
		logAction($link,$loggedUser,'user','update',$username,$organization,'','faliure');
		
		 $result["status"]='ko';
		 $result["msg"] = '<script type="text/javascript">'.
						 'alert("Error: An error occurred when updating the user $name. <br/>' .
						   mysqli_error($link) . 
						   ' Please enter again the user")'. '</script>';
		 $result["log"] = "Error: An error occurred when updating the user $name." .
						   mysqli_error($link) . $q;				   
	}
	my_log($result);
	mysqli_close($link);
}

else if($action == 'get_all_user')
{
	$q = "SELECT * FROM users";
	$r = mysqli_query($link, $q);
	
	//Sara611 - for logging purpose
	$username = mysqli_real_escape_string($link, $_REQUEST['username']);
	$organization = mysqli_real_escape_string($link, $_REQUEST['organization']);
	
	if($r) 
	{
	    //Sara611 - for logging purpose
		logAction($link,$username,'user','get_all_user','',$organization,'','success');
		
		$result['status'] = 'ok';
		$result["log"] .="\n\r action: update ok " . $q;
		$result['content'] = array();
		 while($row = mysqli_fetch_assoc($r)) 
		{
			array_push($result['content'], $row);
		}
	} 
	else
	{
	    //Sara611 - for logging purpose
		logAction($link,$username,'user','get_all_user','',$organization,'','faliure');
		
		$result['status'] = 'ko';
		$result['msg'] = '<script type="text/javascript">'.
						 'alert("Error: errors in reading data about context brokers. <br/>' .
						   mysqli_error($link) . $q .
						   '")'. '</script>';
		$result['log'] = "Error: errors in reading data about context brokers" .
						   mysqli_error($link) . $q;				   
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
