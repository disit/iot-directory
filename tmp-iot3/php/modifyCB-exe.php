  <?php
session_start();
	
include_once "../db/databases.php";
include_once "../db/contextbroker.php";

function is_alspace($str){
     return preg_match('/^[a-zA-Z ]+$/i',$str);
  }

function is_latitude($str)
{ 
  return preg_match('/^(\+|-)?(?:90(?:(?:\.0{1,6})?)|(?:[0-9]|[1-8][0-9])(?:(?:\.[0-9]{1,6})?))$/', $str);
  
  // ^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/', $str);
}  
  
function is_longitude($str)
{
  return preg_match('/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/', $str);
}   
  

 // `name`,`type`,`ip`,`port`,`login`,`password`,`latitude`,`longitude`,`created` 
  
  
    $dati= array("name" => trim($_POST["name"]),
	             "type"=> trim($_POST["type"]),
                 "ip"=> trim($_POST["ip"]),
                 "port"=>trim($_POST["port"]),
	             "login" => trim($_POST["login"]),
				 "password" => trim($_POST["password"]),
				 "latitude" => trim($_POST["latitude"]),
				 "longitude" => trim($_POST["longitude"]),
				 "created" => trim($_POST["created"]));
   $errore = array();
   if (!isset($dati["name"]) || strlen($dati["name"])== 0)
     $errore["name"]="This field is mandatory";
   if (!isset($dati["type"]) || strlen($dati["type"])== 0)
     $errore["type"]="This field is mandatory";
   if (!isset($dati["ip"]) || strlen($dati["ip"])== 0 || !filter_var($dati["ip"], FILTER_VALIDATE_IP))
     $errore["ip"]="This field is mandatory and needs to be a valid IP address";
   if (!isset($dati["port"]) || strlen($dati["port"])== 0 || !is_numeric($dati["port"])
    || $dati["port"]<=0)
     $errore["port"]="This field is mandatory and needs to be a positive integer";
   if (strlen($dati["login"])>0 && strlen($dati["login"])<5)
     $errore["login"]="This field when present needs to contain at least 5 characters";
   //if (!isset($dati["login"]) || strlen($dati["login"])== 0)
   // 	 unset($dati["login"]);
   if (strlen($dati["password"])>0 && strlen($dati["password"])<5)
     $errore["password"]="This field when present needs to contain at least 5 characters";
   // if (!isset($dati["password"]) || strlen($dati["password"])== 0)
   // 	 unset($dati["password"]);
   
   if (!isset($dati["longitude"]) || strlen($dati["longitude"])== 0 || !is_longitude($dati["longitude"]))
     $errore["longitude"]="This field is mandatory and should a longitude";
   if (!isset($dati["latitude"]) || strlen($dati["latitude"])== 0 || !is_latitude($dati["latitude"]))      
     $errore["latitude"]="This field is mandatory and should a latitude";
   
    if (empty($errore))
	{
       $cb = new contextbroker();
       
       $cb->update($dati, array("name"=> $dati["name"]));
	
	   
	   if ($cb->error_status==1)
	   {
	      header("Location:../index.php?op=ok&status=1");	
	   }
       else
      { 		
        $cb->error_redirect();
      }
   }
   else
   {
    $urlPortion= '&errore='.urlencode(serialize($errore)).
		             '&dati='.urlencode(serialize($dati)); 
    header("Location:../index.php?op=modifyCB". $urlPortion);   

  }   
   
unset($cb);   

?>