<?php

include_once "../db/databases.php";
include_once "../db/contextbroker.php";


$cb = new contextbroker();

	$name=trim($_GET["id"]);
	$page=trim($_GET["page"]);
	

$cb->delete(array("name"=>$name));

if ($cb->error_status==1 && $cb->fetchNumRows()==1)
	{
	   header("Location:../index.php?op=$page");
	}
   else
   { 
      //header("Location:../index.php?op=errore&status=1");
	  $cb->error_redirect();
	}
   
unset($cb);   

?>