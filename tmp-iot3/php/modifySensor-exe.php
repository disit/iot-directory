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
   
   
   if (isset($dati["longitude"]) && !is_longitude($dati["longitude"]))
     $errore["longitude"]="This field should be a longitude";
   if (isset($dati["latitude"]) && !is_latitude($dati["latitude"]))      
     $errore["latitude"]="This field should be a latitude";

	 if (!isset($dati["created"]) || strlen($dati["created"])== 0)
    	 unset($dati["created"]);
 
    if (empty($errore))
	{
       $sens = new sensors();

        if (isset($dati["longitude"]) && isset($dati["longitude"]) && 
		    isset($dati["type"   ]) &&      isset($dati["kind"])  && 
			isset($dati["protocol"]) && isset($dati["format"])) 
	         $dati["status"]="mapped";
	   else
	         $dati["status"]="unmapped";
       
       $sens->update($dati, array("id"=> $dati["id"]));
	
	   
	   if ($sens->error_status==1)
	   {
	      header("Location:../index.php?op=ok&status=1");	
	   }
       else
      { 		
        $sens->error_redirect();
      }
   }
   else
   {
    $urlPortion= '&errore='.urlencode(serialize($errore)).
		             '&dati='.urlencode(serialize($dati)); 
    header("Location:../index.php?op=modifySensor". $urlPortion);   

  }   
   
unset($sens);   

?>