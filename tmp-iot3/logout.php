<?php
session_start();

unset($_SESSION["user"]);
unset($_SESSION["name"]);
unset($_SESSION["paziente"]);
unset($_SESSION["pazienteNome"]);  
unset($_SESSION["pazienteCognome"]);
unset($_SESSION["pazienteGruppo"]);
unset($_SESSION["pazienteAttivo"]);
unset($_SESSION["currentTest"]);
unset($_SESSION["pazientedataInizioCT"]);
unset($_SESSION["tipoPatologia"]);
session_destroy();
header("location:index.php");
?>