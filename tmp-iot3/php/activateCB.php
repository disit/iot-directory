<?php
session_start();
include_once "../db/databases.php";
include_once "../db/contextbroker.php";

$cb = new contextbroker();

$name=trim($_REQUEST["id"]);

$col  = array('name'  => $name);

$cb->fetchRecord($col);

if ($cb->error_status==1 && $cb->fetchNumRows()==1)
{
      $_SESSION["cb"]=$cb->result[0]["name"];
      $_SESSION["ip"]=$cb->result[0]["ip"];  
	  $_SESSION["port"]=$cb->result[0]["port"];
	  $_SESSION["protocol"]=$cb->result[0]["type"];
	  
	  $_SESSION["cbActive"]=true;
	  header("Location:../index.php?op=ok&status=4");	  

}
else
{
$cb->error_redirect();
}	

?>