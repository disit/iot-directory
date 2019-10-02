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

$result=array("status"=>"","msg"=>"","content"=>"","error_msg"=>"","log"=>"");	
/* all the primitives return an array "result" with the following structure

result["status"] = ok/ko; reports the status of the operation (mandatory)
result["msg"] a message related to the execution of the operation (optional)
result["content"] in case of positive execution of the operation the content extracted from the db (optional)
result["log"] keep trace of the operations executed on the db

This array should be encoded in json
*/	

/* MARCO: this action is not used
if ($action=="get_models")
{
    $res=array(); 
    $q  = "SELECT * FROM model;";
    $r = mysqli_query($link, $q);

	if($r) 
	{
		while($row = mysqli_fetch_assoc($r)) 
		{
			array_push($res, $row);
		}
		$result["status"]="ok";
		$result["content"]=$res;
		$result["log"]= "action=get_models \r\n";
	} 
	else
	{
		$result["status"]="ko";
		$result['msg'] = mysqli_error($link);
		$result["log"]= "action=get_models -" . " error " .  mysqli_error($link)  . "\r\n";
	}
	echo json_encode($result);
	mysqli_close($link);	
}

else */
if ($action == "get_model")
{
	$name = mysqli_real_escape_string($link, $_REQUEST['name']);
    $q  = "SELECT * FROM model WHERE name = '$name'";
	
	//$q  = "SELECT * FROM defaultpolicy  WHERE policyname = '$name'";
    $r = mysqli_query($link, $q);

	if($r) 
	{
		$row = mysqli_fetch_assoc($r);	
		$result["status"]="ok";
		$result["content"]=$row;
		$result["log"]= "action=get_model:" . $name . " \r\n";
		
	} 
	else
	{
		$result["status"]="ko";
		$result['msg'] = mysqli_error($link);
		$result["log"]= "action=get_model:" . $name . " error " .  mysqli_error($link) . "\r\n";
	}
	echo json_encode($result);
	mysqli_close($link);	
}
else if ($action=="get_all_models")
{
    $username = mysqli_real_escape_string($link, $_REQUEST['username']);
	$organization = mysqli_real_escape_string($link, $_REQUEST['organization']);
    $loggedrole= mysqli_real_escape_string($link, $_REQUEST['loggedrole']);
    
    if (!empty($accessToken)) 
	{
        getOwnerShipObject($accessToken, "ModelID", $result); #IOTModel 
        getDelegatedObject($accessToken, $username, "ModelID", $result); #IOTModel
	}

    $res=array(); 
    $q  = "SELECT * FROM model";
    $r = mysqli_query($link, $q);

	if($r) 
	{
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
            array_push($res, $row);
             }
		}
		$result["status"]="ok";
		$result["content"]=$res;
		$result["log"]= "action=get_all_models \r\n";
	} 
	else
	{
		$result["status"]="ko";
		$result['msg'] = mysqli_error($link);
		$result["log"]= "action=get_all_models -" . " error " .  mysqli_error($link)  . "\r\n";
	}
	my_log($result);
	mysqli_close($link);	
}

else if ($action=="get_all_models_DataTable")
{
    $username = mysqli_real_escape_string($link, $_REQUEST['username']);
	$organization = mysqli_real_escape_string($link, $_REQUEST['organization']);
    $loggedrole= mysqli_real_escape_string($link, $_REQUEST['loggedrole']);

    if (!empty($accessToken)) 
	{
        getOwnerShipObject($accessToken, "ModelID", $result); #IOTModel 
        getDelegatedObject($accessToken, $username, "ModelID", $result); #IOTModel
	}

	$q = "SELECT * FROM model";	
	$r=create_datatable_data($link,$_REQUEST,$q, '');
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
             
             $selectedrows++;
		        if (!$tobelimited || ($tobelimited && $selectedrows >= $start && $selectedrows < ($start+$offset)))
				{
			     
                    if (((isset($result["keys"][$idTocheck]))&&($loggedrole!=='RootAdmin')&&($loggedrole!=='ToolAdmin'))
                                       ||
                                    ((isset($result["keys"][$idTocheck]))&& ($result["keys"][$idTocheck]["owner"]==$username) && (($loggedrole==='RootAdmin')||($loggedrole==='ToolAdmin'))))
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
                    
                    $row["owner"]='';
                    if(isset($result["keys"][$idTocheck]))									   
                        $row["owner"]=$result["keys"][$idTocheck]["owner"];
                    array_push($data, $row);
                }
            }
		}
		
		 $output= format_result($_REQUEST["draw"], $selectedrows+1, $selectedrows+1, $data, "", "\r\n action=get_all_models_DataTable \r\n", 'ok');
		logAction($link,$username,'model','get_all_models_DataTable','',$organization,'','success');
	} 
	else
	{
		$output= format_result($_REQUEST["draw"], 0, 0, null, 'Error: errors in reading data about model. <br/>' . generateErrorMessage($link), '\n\r Error: errors in reading data about model.' . generateErrorMessage($link), 'ko');
		logAction($link,$username,'model','get_all_models_DataTable','',$organization,'Error: errors in reading data about model.','faliure');				   
	}

	my_log($output);
	mysqli_close($link); 	
}
else
if ($action=="insert")
{  
	//Sara2510 - for logging purpose
	$username = mysqli_real_escape_string($link, $_REQUEST['username']);
	
	$name = mysqli_real_escape_string($link, $_REQUEST['name']);
	$description = mysqli_real_escape_string($link, $_REQUEST['description']);
	$type = mysqli_real_escape_string($link, $_REQUEST['type']);
	$kind = mysqli_real_escape_string($link, $_REQUEST['kind']);
	$producer = mysqli_real_escape_string($link, $_REQUEST['producer']);
	$frequency = mysqli_real_escape_string($link, $_REQUEST['frequency']);
	//$policy = mysqli_real_escape_string($link, $_REQUEST['policy']);
	$kgenerator =  mysqli_real_escape_string($link,$_REQUEST['kgenerator']);
	$edgegateway_type = mysqli_real_escape_string($link,$_REQUEST['edgegateway_type']);
	$contextbroker = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
	$protocol = mysqli_real_escape_string($link, $_REQUEST['protocol']);
	$format = mysqli_real_escape_string($link, $_REQUEST['format']);
	//$active = mysqli_real_escape_string($link, $_REQUEST['active']);
	$hc = mysqli_real_escape_string($link, $_REQUEST['hc']);
	$hv = mysqli_real_escape_string($link, $_REQUEST['hv']);
	$listAttributes= mysqli_real_escape_string($link, $_REQUEST['attributes']);
	$organization= mysqli_real_escape_string($link, $_REQUEST['organization']);

	$q = "INSERT INTO model(name, description, devicetype, kind, producer, frequency, contextbroker, protocol, format, healthiness_criteria, healthiness_value, kgenerator, attributes, edgegateway_type, organization, visibility ) " .
		 "VALUES('$name', '$description', '$type', '$kind', '$producer', '$frequency', '$contextbroker', '$protocol', '$format', '$hc', '$hv', '$kgenerator', '$listAttributes', '$edgegateway_type', '$organization', 'private')";
	$r = mysqli_query($link, $q);

	if($r)
	{
		$result["status"]='ok';
		$result["log"]= "action=insert " . $q   . " \r\n";
        $ownmsg = array();
			$ownmsg["elementId"]=$organization . ':' . $name; // I am using the new identifier
			$ownmsg["elementName"]=$organization . ':' . $name;				    
			$ownmsg["elementUrl"]=$organization . ':' . $name;
            $ownmsg["elementType"]="ModelID"; #IOTModel
			
        
            registerOwnerShipObject($ownmsg, $accessToken, 'ModelID',$result); #IOTModel
            if($result["status"]=='ok'){
                logAction($link,$username,'model','insert',$name,$organization,'Registering the ownership of model','success');
            }
            else{
                logAction($link,$username,'model','insert',$name,$organization,'Registering the ownership of model','failure');
            }
		
		//Sara2510 - for logging purpose
		logAction($link,$username,'model','insert',$name,$organization,'','success');
	}
	else
	{
		//Sara2510 - for logging purpose
		logAction($link,$username,'model','insert',$name,$organization,'An error occurred when registering the Model','faliure');
		 $result["status"]='ko';
		 $result["msg"] = "Error: An error occurred when registering the Model $name. <br/>" .
						   mysqli_error($link) . 
						   ' Please enter again the Model';
		 $result["log"]= "action=insert -" . $q . " error " .  mysqli_error($link)  . "\r\n";
	}
	echo json_encode($result);
	mysqli_close($link);
}
else
if ($action=="update")
{  
	//Sara2510 - for logging purpose
	$username = mysqli_real_escape_string($link, $_REQUEST['username']);
	
	$id = mysqli_real_escape_string($link, $_REQUEST['id']);
	$name = mysqli_real_escape_string($link, $_REQUEST['name']);
	$description = mysqli_real_escape_string($link, $_REQUEST['description']);
	$type = mysqli_real_escape_string($link, $_REQUEST['type']);
	$kind = mysqli_real_escape_string($link, $_REQUEST['kind']);
	$producer = mysqli_real_escape_string($link, $_REQUEST['producer']);
	$frequency = mysqli_real_escape_string($link, $_REQUEST['frequency']);
	//$policy = mysqli_real_escape_string($link, $_REQUEST['policy']);
	$kgenerator =  mysqli_real_escape_string($link,$_REQUEST['kgenerator']);
	$edgegateway_type = mysqli_real_escape_string($link,$_REQUEST['edgegateway_type']);
	$contextbroker = mysqli_real_escape_string($link, $_REQUEST['contextbroker']);
	$protocol = mysqli_real_escape_string($link, $_REQUEST['protocol']);
	$format = mysqli_real_escape_string($link, $_REQUEST['format']);
	//$active = mysqli_real_escape_string($link, $_REQUEST['active']);
	$hc = mysqli_real_escape_string($link, $_REQUEST['hc']);
	$hv = mysqli_real_escape_string($link, $_REQUEST['hv']);
	$listAttributes= mysqli_real_escape_string($link, $_REQUEST['attributes']);
	$organization= mysqli_real_escape_string($link, $_REQUEST['organization']);
    $obj_organization = mysqli_real_escape_string($link, $_REQUEST['obj_organization']);
	//MARCO $listdeleteAttributes= mysqli_real_escape_string($link, $_REQUEST['deleteattributes']);
	//MARCO $listnewAttributes= mysqli_real_escape_string($link, $_REQUEST['newattributes']);
	

	$q = "UPDATE model SET name = '$name', attributes = '$listAttributes', description = '$description', devicetype = '$type', kind = '$kind',  producer= '$producer', frequency = '$frequency', contextbroker='$contextbroker', protocol = '$protocol', format = '$format', healthiness_criteria = '$hc', healthiness_value='$hv', kgenerator = '$kgenerator', edgegateway_type = '$edgegateway_type' WHERE id = '$id'";
	$r = mysqli_query($link, $q);

	if($r)
	{
		$ownmsg = array();
        $ownmsg["elementId"]=$obj_organization . ':' . $name; // I am using the new identifier	
        $ownmsg["elementName"]=$obj_organization . ':' . $name;				    
        $ownmsg["elementUrl"]=$obj_organization . ':' . $name;
        $ownmsg["elementType"]="ModelID";
        registerOwnerShipObject($ownmsg, $accessToken, 'ModelID',$result);
        
        if($result["status"]=='ok'){
            $result["log"]= "action=update " . $q   . " \r\n";
            logAction($link,$username,'model','update',$name,$organization,'','success');
        }
        else{
            logAction($link,$username,'model','update',$name,$organization,'in ownership registration','failure');
        }
		
		
	}
	else
	{
		logAction($link,$username,'model','update',$name,$organization,'An error occurred when updating the model','faliure');
		 $result["status"]='ko';
		 $result["msg"] = "Error: An error occurred when updating the model $name. <br/>" .
						   mysqli_error($link) .
						   ' Please enter again the model';
		 $result["log"]= "action=update -" . $q . " error " .  mysqli_error($link)  . "\r\n";				            
	}
	echo json_encode($result);
	mysqli_close($link);
}
else 
if ($action=="delete")
{	  
	  //Sara2510 - for logging purpose
	  $username = mysqli_real_escape_string($link, $_REQUEST['username']);
	  $name = mysqli_real_escape_string($link, $_REQUEST['name']);
	  $organization = mysqli_real_escape_string($link, $_REQUEST['organization']);
	  
      $id = mysqli_real_escape_string($link, $_REQUEST['id']);      
      $q = "DELETE FROM model WHERE id = '$id'";
      $r = mysqli_query($link, $q);
      if($r)
	  {
		logAction($link,$username,'model','delete',$name,$organization,'','success');
		
		$result["status"]='ok';
          
          if($accessToken!=""){
              $elementId=$organization . ':' . $name;
              removeOwnerShipObject($elementId,$accessToken,"ModelID",$result); #IOTModel
          }
          if($result["status"]='ok'){
              $result["log"].='\n\r action: delete ok. ' . $q;
              logAction($link,$username,'model','delete',$name,$organization,'','success');
          }
          else{
              $result["log"].='\n\r action: delete ok from database, delete Ownership failed. ';
              logAction($link,$username,'model','delete',$name,$organization,'delete ok from database, delete Ownership failed.','failure');
          }
		
	  }
	  else
	  {
		//Sara2510 - for logging purpose
		logAction($link,$username,'model','delete',$name,$organization,'','faliure');
		 $result["status"]='ko';
		 $result["log"]= "action=delete -" . $q . " error " .  mysqli_error($link)  . "\r\n";
		 $result["msg"] = 'Model <b>' . $name . '</b> &nbsp; deletion failed, ' .
						   mysqli_error($link) . $q .
						   ' Please enter again.';
	  }
	  echo json_encode($result);
	  mysqli_close($link);
}
else 
if($action == 'get_value_attributes')
{

	$id = mysqli_real_escape_string($link, $_REQUEST['id']);
	
	$result = array();

	$q = "SELECT attributes FROM model WHERE id = '$id'";
    $r = mysqli_query($link, $q);
	if($r){ 
		$row = mysqli_fetch_assoc($r);	
		$result["status"]="ok";
		$result["content"]=$row;
		$result["log"]= "action=get_value_attributes of model " . $id   . " \r\n";
     }
	 else
	 {
	    $result['status'] = 'ko'; 
		$result['msg'] = 'Error: errors in reading data about attributes of the model. <br/>' .
						  generateErrorMessage($link);
		$result["log"]= "action=get_value_attributes of model " . $id . " error " .  mysqli_error($link)  . "\r\n";
		 
	}
	echo json_encode($result);
	mysqli_close($link);  
}	
else 
{
	$result['status'] = 'ko';
	$result['msg'] = 'invalid action ' . $action;
    $result["log"]= "action=unknown \r\n";	
	echo json_encode($result);
}
