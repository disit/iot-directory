<?php

session_start();
	
include_once "../db/databases.php";
include_once "../db/sensors.php";

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
  
 $dati= array("id" => trim($_POST["id"]),
	             "macaddress"=> trim($_POST["macaddress"]),
                 "type"=> trim($_POST["type"]),
                 "model"=>trim($_POST["model"]),
	             "producer" => trim($_POST["producer"]),
				 "longitude" => trim($_POST["longitude"]),
				 "latitude" => trim($_POST["latitude"]),
				 "kind" => trim($_POST["kind"]),
				 "protocol" => trim($_POST["protocol"]),
				 "format" => trim($_POST["format"]),
				 // "contextBroker" => trim($_POST["contextBroker"]),
				 "created" => trim($_POST["created"]));
				 
   $errore = array();
   if (!isset($dati["id"]) || strlen($dati["id"])== 0)
     $errore["id"]="This field is mandatory";
   // if (!isset($dati["type"]) || strlen($dati["type"])== 0)
   //  $errore["type"]="This field is mandatory";
   
   
   if (!isset($dati["longitude"]) || strlen($dati["longitude"])== 0 || !is_longitude($dati["longitude"]))
     $errore["longitude"]="This field is mandatory and should a longitude";
   if (!isset($dati["latitude"]) || strlen($dati["latitude"])== 0 || !is_latitude($dati["latitude"]))      
     $errore["latitude"]="This field is mandatory and should a latitude";

	 if (!isset($dati["created"]) || strlen($dati["created"])== 0)
    	 unset($dati["created"]);
   

    if (empty($errore))
	{
       $sensor = new sensors();
       $dati["contextBroker"]=$_SESSION["cb"];
       
	   // if the following information has been set the sensor can be considered mapped
	   if (isset($dati["longitude"]) && isset($dati["longitude"]) && isset($dati["type"]) && isset($dati["kind"])  && isset($dati["protocol"]) && isset($dati["format"])) 
	   $dati["status"]="mapped";
	   else
	   $dati["status"]="unmapped";
	   
	   
       $sensor->insert($dati);
	
	   // echo "no error";
	   // print_r($dati);
	   // exit(1);
	   
	   if ($sensor->error_status==1)
	   {
	      header("Location:../index.php?op=ok&status=1");	
	   }
       else
      { 
        $sensor->error_redirect();
      }
   }
   else
   {
    if (!isset($dati["created"])) $dati["created"]="";
	 
  	 // echo "error";
	  // print_r($dati);
	  // print_r($errore);
	   // exit(1);
	   
    $urlPortion= '&errore='.urlencode(serialize($errore)).
		             '&dati='.urlencode(serialize($dati)); 
    header("Location:../index.php?op=createSensor". $urlPortion);   

  }     
   unset($sensor);

?>