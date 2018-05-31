<?php
 session_start();
 
   unset($_SESSION["cb"]);
   unset($_SESSION["ip"]);  
   unset($_SESSION["port"]);
   unset($_SESSION["protocol"]);
   unset($_SESSION["cbActive"]);
   echo "1";
?>
 