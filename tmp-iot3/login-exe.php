<?php

include_once "./db/databases.php";
include_once "./db/user.php";


$user = new user();

	$login=trim($_POST["email"]);
    $pwd=trim($_POST["password"]);


$user->login(array("login"=>$login,"password"=>$pwd));

if ($user->error_status==1 && $user->fetchNumRows()==1)
	{
        session_start();
		$_SESSION["user"]=$user->result[0]["id"];
		$_SESSION["name"]=$user->result[0]["name"];
		$_SESSION["lastname"]=$user->result[0]["lastname"];
		$_SESSION["role"]=$user->result[0]["role"];
		header("Location:./index.php");
	}
   else
   { header("Location:./login.php");}
   
unset($user);   

?>