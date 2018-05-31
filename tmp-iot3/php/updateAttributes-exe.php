  <?php
session_start();
	
include_once "../db/databases.php";
include_once "../db/attributes.php";
include_once "../db/sensors.php";

$numAttr=$_GET["numAttr"];
$status=$_GET["status"];
$sensorID=$_GET["sensorID"];
$cbID=$_GET["cb"];

$modify= $_POST;
print_r($modify);

$con = new attributes();
       
$error=false;       
$count=0;
foreach ($modify as $att=>$value)
{   
   //  echo "attr " . $att . " vale " . $value;
   if (!empty($value))
   {   
       $con->update(array("slabel"=>$value), array("cb"=> $cbID, "sensor"=>$sensorID, "name"=>$att));
      if ($con->error_status!=1)
	   {
	     $error=true;
		 break;
	   } 
	   $count++;
   } 	  
}


if ($error)
{
  $con->error_redirect();
}

if ($count==$numAttr)
{
  $sens = new sensors();
  $sens->update(array("astatus"=>"mapped"),array("contextbroker"=> $cbID, "id"=>$sensorID));
  if ($sens->error_status!=1) {$sens->error_redirect();}
}  


unset($con);
unset($sens);

header("Location:../index.php?op=listSensors&status=" . $status);	   

?>