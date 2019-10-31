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

require '../sso/autoload.php';
use Jumbojett\OpenIDConnectClient;
include '../config.php'; 

$organization="";


   
if ($_REQUEST['token']!='undefined')
      $accessToken = $_REQUEST['token'];
   
else $accessToken = "";


if (isset($_REQUEST['username'])) {
	$currentUser = $_REQUEST['username'];
}

 
$ldapRole = null;
$ldapOk = false;
   
   

    
    
$ldapUsername = "cn=". $currentUser . ",".$ldapBaseName;

$ds = ldap_connect($ldapServer, $ldapPort);
ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
$bind = ldap_bind($ds);    



if($ds && $bind)
    {
        
        if(checkLdapMembership($ds, $ldapUsername, $ldapToolName, $ldapBaseName))
        {
           $result["msg"].=" Ldap checked ";
           $organization= findLdapOrganizationalUnit($ds, $ldapUsername, $ldapToolName, $ldapBaseName);
           $result["msg"].=" organization found is $organization ";
            if(checkLdapRole($ds, $ldapUsername, "RootAdmin", $ldapBaseName))
           {
              $ldapRole = "RootAdmin";
              $ldapOk = true;
           }
           else if(checkLdapRole($ds, $ldapUsername, "ToolAdmin", $ldapBaseName))
           {
              $ldapRole = "ToolAdmin";
              $ldapOk = true;
           }
           else
           {
               if(checkLdapRole($ds, $ldapUsername, "AreaManager", $ldapBaseName))
               {
                  $ldapRole = "AreaManager";
                  $ldapOk = true;
               }
               else
               {
                  if(checkLdapRole($ds, $ldapUsername, "Manager", $ldapBaseName)) {
                     $ldapRole = "Manager";
                     $ldapOk = true;
                  } else {
					  $msg = "user $username does not have a role";
				  } /* else { //uncomment if Public role is managed
				     $ldapRole = "Public";
					 $ldapOk = true;
				  } */
               }
           }
        } else {
			$msg = "user $username cannot access to tool $ldapToolName";
		}
    } else {
		$msg = "cannot bind to LDAP server $ldapServer";
	}



if ($action=="get_organization")
{  
	
    if (!empty($accessToken)) 
	{ 
        if(!empty($organization) )
        $result["status"]="ok";
        $info=get_organization_info($organizationApiURI, $organization);
		$result["content"]=$organization;
		$result["info"]=$info;
        $result["msg"].=" ok";
	}
    
    
	my_log($result);
	mysqli_close($link);

}

function checkLdapMembership($connection, $userDn, $tool, $ldapBaseName) 
{
	 $result = ldap_search($connection, $ldapBaseName, '(&(objectClass=posixGroup)(memberUid=' . $userDn . '))');
	 $entries = ldap_get_entries($connection, $result);
	 foreach ($entries as $key => $value) 
	 {
		if(is_numeric($key)) 
		{
		   if($value["cn"]["0"] == $tool) {
			  return true;
		   }
		}
	 }
	 return false;
 }

function checkLdapRole($connection, $userDn, $role, $ldapBaseName) 
{
  $result = ldap_search($connection, $ldapBaseName, '(&(objectClass=organizationalRole)(cn=' . $role . ')(roleOccupant=' . $userDn . '))');
  $entries = ldap_get_entries($connection, $result);
  foreach ($entries as $key => $value) 
  {
	 if(is_numeric($key)) 
	 {
		if($value["cn"]["0"] == $role) {
		   return true;
		}
	 }
  }
  return false;
}

//Fatima
function findLdapOrganizationalUnit($connection, $userDn, $tool, $ldapBaseName) 
{
	$result = ldap_search($connection, $ldapBaseName, '(&(objectClass=organizationalUnit)(l=' . $userDn . '))');
	$entries = ldap_get_entries($connection, $result);
	
    //Print $entries here
    //echo_log(var_dump($entries));
    //echo_log($entries);
    


    if (ldap_count_entries($connection,$result)==0){
        //TODO thrown an error or return an error
//        echo_log("No LDAP organization Unit found for user".$userDn);
        return "NULL";
        }
    else{
        $ou=$entries["0"]["ou"][0];
  //      echo_log("Organization found is:".$ou);
        return $ou;
        
    }
	// foreach ($entries as $key => $value){
		// if(is_numeric($key)){
		   // if($value["ou"]["0"] == $tool) {
			  // return true;
		   // }
		// }
	// }
}

