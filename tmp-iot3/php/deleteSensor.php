<?php

include_once "../db/databases.php";
include_once "../db/sensors.php";


$sensor = new sensors();

	$id=trim($_GET["id"]);
	$sens=trim($_GET["cb"]);
	$page=trim($_GET["page"]);
	$status=trim($_GET["status"]);
	

	$sensor->delete(array("id"=>$id,"contextBroker"=>$sens));

if ($sensor->error_status==1 && $sensor->fetchNumRows()==1)
	{	
	   header("Location:../index.php?op=$page&status=$status");
	}
   else
   { 
	  $sensor->error_redirect();
	}
   
unset($sensor);   

?>
