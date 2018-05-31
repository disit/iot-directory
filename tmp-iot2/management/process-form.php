<?php
    /* Dashboard Builder.
   Copyright (C) 2017 DISIT Lab https://www.disit.org - University of Florence

   This program is free software; you can redistribute it and/or
   modify it under the terms of the GNU General Public License
   as published by the Free Software Foundation; either version 2
   of the License, or (at your option) any later version.
   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.
   You should have received a copy of the GNU General Public License
   along with this program; if not, write to the Free Software
   Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA. */

    header("Access-Control-Allow-Origin: *");
    include '../config.php';
    require '../phpmailer/PHPMailerAutoload.php';
    
    //Definizioni di funzione
    function notificatorLogin($username, $notificatorApiUsr, $notificatorApiPwd, $notificatorUrl, $notificatorToolName)
    {
        if($notificatorUrl != "")
        {
            $usr = md5($username);
            $clientApplication = md5($notificatorToolName);

            $data = '?apiUsr=' . $notificatorApiUsr . '&apiPwd=' . $notificatorApiPwd . '&operation=remoteLogin&usr=' . $usr . '&clientApplication=' . $clientApplication;
            $notificatorUrl = $notificatorUrl.$data;

            $options = array(
                  'http' => array(
                          'header'  => "Content-type: application/json\r\n",
                          'method'  => 'POST',
                          'timeout' => 30
                  )
            );

            try
            {
                   $context  = stream_context_create($options);
                   $callResult = @file_get_contents($notificatorUrl, false, $context);
            }
            catch (Exception $ex) 
            {
                   //Non facciamo niente di specifico in caso di mancata risposta dell'host
            }
        }
    }
    
    function notificatorLogout($username, $notificatorApiUsr, $notificatorApiPwd, $notificatorUrl, $notificatorToolName)
    {
        /*$logoutAuthObj = ['username' => $username, 'clientApplication' => $ldapTool];
        $logoutAuthJson = json_encode($logoutAuthObj);
        $logoutAuthJsonMd5 = md5($logoutAuthJson);*/
        $usr = md5($username);
        $clientApplication = md5($notificatorToolName);
        
        $data = '?apiUsr=' . $notificatorApiUsr . '&apiPwd=' . $notificatorApiPwd . '&operation=remoteLogout&usr=' . $usr . '&clientApplication=' . $clientApplication;
        $notificatorUrl = $notificatorUrl.$data;
      
      $options = array(
          'http' => array(
              'header'  => "Content-type: application/json\r\n",
              'method'  => 'POST',
              'timeout' => 30
          )
      );

      try
      {
         $context  = stream_context_create($options);
         $callResult = @file_get_contents($notificatorUrl, false, $context);
         
         //$file = fopen("C:\dashboardLog.txt", "w");
         //fwrite($file, "Call result: " . $callResult . "\n");
      }
      catch (Exception $ex) 
      {
         //Non facciamo niente di specifico in caso di mancata risposta dell'host
      }
    }
    
    function returnManagedStringForDb($original)
    {
        if($original == NULL)
        {
            return "NULL";
        }
        else
        {
            return "'" . $original . "'";
        }
    }
    
    function returnManagedNumberForDb($original)
    {
        if($original == NULL)
        {
            return "NULL";
        }
        else
        {
            return $original;
        }
    }
    
    function checkLdapMembership($connection, $userDn, $tool) 
    {
         $result = ldap_search($connection, 'dc=ldap,dc=disit,dc=org', '(&(objectClass=posixGroup)(memberUid=' . $userDn . '))');
         $entries = ldap_get_entries($connection, $result);
         foreach ($entries as $key => $value) 
         {
            if(is_numeric($key)) 
            {
               if($value["cn"]["0"] == $tool) 
               {
                  return true;
               }
            }
         }
         return false;
     }
   

   function checkLdapRole($connection, $userDn, $role) 
   {
      $result = ldap_search($connection, 'dc=ldap,dc=disit,dc=org', '(&(objectClass=organizationalRole)(cn=' . $role . ')(roleOccupant=' . $userDn . '))');
      $entries = ldap_get_entries($connection, $result);
      foreach ($entries as $key => $value) 
      {
         if(is_numeric($key)) 
         {
            if($value["cn"]["0"] == $role) 
            {
               return true;
            }
         }
      }
      return false;
  }
    
    
    function canEditDashboard()
    {
        $result = false;
        if(isset($_SESSION['loggedRole']))
        {
            if($_SESSION['loggedRole'] == "Manager")
            {
                //Utente non amministratore, edita una dashboard solo se ne é l'autore
                if((isset($_SESSION['loggedUsername']))&&(isset($_SESSION['dashboardId']))&&(isset($_SESSION['dashboardAuthorName']))&&($_SESSION['loggedUsername'] == $_SESSION['dashboardAuthorName']))
                {
                    $result = true;
                }
            }
            else if(($_SESSION['loggedRole'] == "AreaManager") || ($_SESSION['loggedRole'] == "ToolAdmin"))
            {
                //Utente amministratore, edita qualsiasi dashboard
                if((isset($_SESSION['loggedUsername']))&&(isset($_SESSION['dashboardId']))&&(isset($_SESSION['dashboardAuthorName'])))
                {
                    $result = true;
                }
            }
        }
        return $result;
    }
/* ****FUNCTIONS FOR THE REGISTRATION OF A DEVICE IN THE CONTEXT BROKER AND IN THE KNOWLEDGE BASE ******  */
	
	function insert_ngsi($name, $type, $contextbroker, $kind, $protocol, $format, $model, $latitude, $longitude, $listnewAttributes, $ip, $port)
	{
	   $result = "Ok";
	   $msg_orion=array();
      
	  $msg_orion["id"]=     $name; 
      $msg_orion["type"]=   $type;
      $msg_orion["latitude"]=array();
      $msg_orion["longitude"]=array();
      $msg_orion["latitude"]["value"]=    $latitude;
      $msg_orion["longitude"]["value"]=    $longitude;
      $msg_orion["latitude"]["type"]= "float";     
      $msg_orion["longitude"]["type"]= "float";
      $a=0;
      while ($a < count($listnewAttributes))
      {
         $att=$listnewAttributes[$a];
		 $msg_orion[$att->value_name]=array();
		 $msg_orion[$att->value_name]["value"]="0";
 		 $msg_orion[$att->value_name]["type"]=$att->data_type;
         $a++;
      }
	  $msg_orion["model"]=array();
	  $msg_orion["model"]["value"]= $model;
 	  $msg_orion["model"]["type"]= "string";
        
	
	  $url_orion="http://$ip:$port/v2/entities/";  
          //echo "stefano ".json_encode($msg_orion) .  $url_orion;
      
      try
         {
			// Setup cURL
			$ch = curl_init($url_orion);
			$authToken = 'OAuth 2.0 token here';
			curl_setopt_array($ch, array(
    			CURLOPT_POST => TRUE,
    			CURLOPT_RETURNTRANSFER => TRUE,
    			CURLOPT_HTTPHEADER => array(
					'Authorization: '.$authToken,
					'Content-Type: application/json'),
				CURLOPT_POSTFIELDS => json_encode($msg_orion)
		    ));

		    // Send the request
		    $response_orion = curl_exec($ch);

		    // Check for errors
		    if($response_orion === FALSE){
    			die(curl_error($ch));
			}

		    // Decode the response
		    $responseData = json_decode($response_orion, TRUE);

		    // Print the date from the response
		    // echo $responseData['published'];
                    // echo $response_orion; 
            }
            catch (Exception $ex) 
            {
		      $result="errore"; echo $ex;
            }
            return $result; 
	}
	
	function insert_mqtt($name, $type, $contextbroker, $kind, $protocol, $format, $latitude, $longitude, $listnewAttributes, $ip, $port)
	{
	  return "Ok";
	}
	
	function insert_amqp($name, $type, $contextbroker, $kind, $protocol, $format, $latitude, $longitude, $listnewAttributes, $ip, $port)
	{
	  return "Ok";
	}
	function canBeRegistered($name, $type, $contextbroker, $kind, $protocol, $format,  $macaddress, $model, $producer, $latitude, $longitude, $listnewAttributes)
	{
	  $error=false;
	  // echo "valore di erroe" . $error;
	  if ($name==null || $name=="") { $error=true;}
          if ($contextbroker==null || $contextbroker=="") {$error=true;}	  
	  if ($type==null || $type=="") {$error=true;}
	  if (!($kind=="sensor" || $kind=="actuator")) {$error=true;}
	  if ($latitude < -90 && $latitude>90) {$error=true;}
	  if ($longitude < -180 && $longitude>180) {$error=true;}
	  if (!($protocol=="ngsi" || $protocol=="mqtt" || $protocol=="amqp")) {$error=true;}
	  // echo "valore di erroe" . $error;
	  // print_r($listnewAttributes);
	  if (count($listnewAttributes)==0) $error=true;
	  // echo "valore di erroeList" . $error;
	    
	   foreach($listnewAttributes as $att)
		  {
		      // print_r($att);
			if ($att->data_type==null || $att->data_type=="") {$error=true;}
			if ($att->value_unit==null || $att->value_unit=="") {$error=true;}
			if ($att->value_type==null || $att->value_type=="") {$error=true;}
		    if (!($att->healthiness_criteria=="refresh_rate" || $att->healthiness_criteria=="different_values" || 
			      $att->healthiness_criteria=="within_bounds")) {$error=true;}
		    if ($att->healthiness_criteria=="refresh_rate" && $att->healthiness_value=="") {$error=true;}
			// echo "valore di erroe" . $error;
			// if ($att->healthiness_criteria=="different_values" && ($att->different_values=="" || !is_int($att->different_values))) {$error=true;}
		  }
	   if ($error) return false;	  
	   return true; 
	}
	
	function registerKB($link, $name, $type, $contextbroker, $kind, $protocol, $format,  $macaddress, $model, $producer, $latitude, $longitude, $listnewAttributes)
   {  
      if (canBeRegistered($name, $type, $contextbroker, $kind, $protocol, $format,  $macaddress, $model, $producer, $latitude, $longitude, $listnewAttributes))
      {	  
		  $result="Ok";
                  // echo "passed condition";
		  /* msg for the Knowledge base + registration on the KB */
		  $query="SELECT * from contextbroker WHERE name = '$contextbroker'";
		  
		  $r = mysqli_query($link, $query);
		  
		  if (!$r) {echo mysqli_error($link);return null;}

		  
		  $rowCB = mysqli_fetch_assoc($r);
		  $ip=$rowCB["ip"];
		  $port=$rowCB["port"];
		  
		  $msg=array();
		  $msg["id"]=     $name;
		  $msg["type"]=   $type;
		  $msg["kind"]=    $kind;
		  $msg["protocol"]=    $protocol;
		  $msg["format"]=      $format;
		  $msg["macaddress"]=      $macaddress;
		  $msg["model"]=      $model;
		  $msg["producer"]=      $producer;
		  $msg["latitude"]=    $latitude;
		  $msg["longitude"]=    $longitude;
		  $msg["frequency"]=    0;
		  $msg["broker"]=array();
		  $msg["broker"]["name"]=$contextbroker;
		  $msg["broker"]["type"]=$rowCB["protocol"];
		  $msg["broker"]["ip"]=$rowCB["ip"];
		  $msg["broker"]["port"]=$rowCB["port"];
		  $msg["broker"]["login"]=($rowCB["login"]==null)?"":$rowCB["login"];
		  $msg["broker"]["password"]=($rowCB["password"]==null)?"":$rowCB["password"];
		  $msg["broker"]["latitude"]=$rowCB["latitude"];
		  $msg["broker"]["longitude"]=$rowCB["longitude"];
		  $msg["broker"]["created"]=$rowCB["created"];
		   // $msg["attributes"]=array();
		  
		  $myAttrs=array();
		  $i=1;
		  foreach($listnewAttributes as $att)
		  {
                        // print_r($att);  
			$myatt = array();
			$myatt["value_name"] =$att->value_name;
			$myatt["data_type"] =$att->data_type;
			$myatt["value_type"]=$att->value_type;
			$myatt["value_unit"]=$att->value_unit;
			$myatt["healthiness_criteria"]=$att->healthiness_criteria;
			if ($att->healthiness_criteria=="refresh_rate") 
                              $myatt["value_refresh_rate"]=$att->healthiness_value;
			// to be fixed
                        //else if ($att["healthiness_criteria"]=="different_values") 
                        //      $myatt["different_values"]=$att["different_values"];
			//else  $myatt["value_bounds"]=$att["value_bounds"];
			$myatt["order"]=$i++;
			$myAttrs[]=$myatt;
			
		  }	 
		  $msg["attributes"]=$myAttrs;
		  
		  // echo json_encode($msg);	  
		  
		  try
		   {
			 $url="http://servicemap.disit.org/WebAppGrafo/api/v1/iot/insert";
			 $options = array(
					  'http' => array(
							  'header'  => "Content-Type: application/json;charset=utf-8",
							  'header'  => "Access-Control-Allow-Origin: *",
							  'method'  => 'POST',
				  'content' => json_encode($msg),
							  'timeout' => 30
					  )
				);
			 $context  = stream_context_create($options);
			 $result = @file_get_contents($url, false, $context);
		 } 
		 catch (Exception $ex) 
		  {
			$result="errore"; echo $ex;  
		 } 
		/* registration of the device in the corresponding context broker */
		 // echo "dopo KB" + $result;
		 if ($result!="errore")
		 {
			 switch ($protocol)
			 {
			   case "ngsi": 
					 $res = insert_ngsi($name, $type, $contextbroker, $kind, $protocol, $format, $model, $latitude, $longitude, $listnewAttributes, $ip, $port);
					 break;
			   case "mqtt":
					$res = insert_mqtt($name, $type, $contextbroker, $kind, $protocol, $format, $latitude, $longitude, $listnewAttributes, $ip, $port);
					 break;
				case "amqp":	  
					$res = insert_amqp($name, $type, $contextbroker, $kind, $protocol, $format, $latitude, $longitude, $listnewAttributes, $ip, $port);
					 break;	 
			 }	
		 }		 
		echo "dopo protocol" . $res . " " . $result;
		if ($result!="errore" && $res=="Ok") return $result;
		else return null;
    }		
	else return null;	
   } // end of function registerKB	
    
                
	/* ****FUNCTIONS FOR THE MODIFICATION OF THE REGISTRATION OF A DEVICE IN THE KNOWLEDGE BASE AND IN THE CONTEXT BROKER ****************** */			
				
	function update_ngsi($name, $type, $contextbroker, $kind, $protocol, $format, $model, $latitude, $longitude, $listnewAttributes, $ip, $port,$uri)
	{
	   $result = "Ok";
	   $msg_orion=array();
      
	  // $msg_orion["id"]=     $name; 
      // $msg_orion["type"]=   $type;
      $msg_orion["latitude"]=array();
      $msg_orion["longitude"]=array();
      $msg_orion["latitude"]["value"]=    $latitude;
      $msg_orion["longitude"]["value"]=    $longitude;
      $msg_orion["latitude"]["type"]= "float";     
      $msg_orion["longitude"]["type"]= "float";
      $a=0;
      while ($a < count($listnewAttributes))
      {
         $att=$listnewAttributes[$a];
		 $msg_orion[$att->value_name]=array();
		 $msg_orion[$att->value_name]["value"]="0";
 		 $msg_orion[$att->value_name]["type"]=$att->data_type;
         $a++;
      }
	  if ($model != null && $model != "")
	  {
	    $msg_orion["model"]=array();
	    $msg_orion["model"]["value"]= $model;
 	    $msg_orion["model"]["type"]= "string";
      }  
      // echo "stefano ".json_encode($msg_orion);
	
	  $url_orion="http://$ip:$port/v2/entities/$name/attrs";  
      
      
      try
         {  
			// Setup cURL
			$ch = curl_init($url_orion);
			$authToken = 'OAuth 2.0 token here';
			curl_setopt_array($ch, array(
    			CURLOPT_POST => TRUE,
    			CURLOPT_RETURNTRANSFER => TRUE,
    			CURLOPT_HTTPHEADER => array(
					'Authorization: '.$authToken,
					'Content-Type: application/json'),
				CURLOPT_POSTFIELDS => json_encode($msg_orion)
		    ));

		    // Send the request
		    $response_orion = curl_exec($ch);

		    // Check for errors
		    if($response_orion === FALSE){
    			die(curl_error($ch));
			}

		    // Decode the response
		    $responseData = json_decode($response_orion, TRUE);

		    // Print the date from the response
 		   // echo $responseData['published'];
                      echo $response_orion;  
            }
              catch (Exception $ex) 
            {
		      $result="errore"; echo $ex;
            } 
            return $result; 
	}
	
	function update_mqtt($name, $type, $contextbroker, $kind, $protocol, $format, $latitude, $longitude, $listnewAttributes, $ip, $port,$uri)
	{
	  return "Ok";
	}
	
	function update_amqp($name, $type, $contextbroker, $kind, $protocol, $format, $latitude, $longitude, $listnewAttributes, $ip, $port,$uri)
	{
	  return "Ok";
	}
	
	function updateKB($link, $name, $type, $contextbroker, $kind, $protocol, $format,  $macaddress, $model, $producer, $latitude, $longitude, $listnewAttributes, $uri)
   {  
        echo "prima";
	  $okkb=canBeRegistered($name, $type, $contextbroker, $kind, $protocol, $format,  $macaddress, $model, $producer, $latitude, $longitude, $listnewAttributes);
	  // echo "valore di okkb". $okkb;
      if ($okkb)
	  {
		  $result = "Ok";
		  // echo "dentro";
		  /* msg for the Knowledge base + registration on the KB */
		  $query="SELECT * from contextbroker WHERE name = '$contextbroker'";
		  
		  $r = mysqli_query($link, $query);
		  
		  if (!$r) {echo mysqli_error($link); return null;}

		  $rowCB = mysqli_fetch_assoc($r);
		  $ip=$rowCB["ip"];
		  $port=$rowCB["port"];
		  
		  $msg=array();
		  $msg["id"]=     $name;
		  $msg["type"]=   $type;
		  $msg["kind"]=    $kind;
		  $msg["protocol"]=    $protocol;
		  $msg["format"]=      $format;
		  $msg["macaddress"]=      $macaddress;
		  $msg["model"]=      $model;
		  $msg["producer"]=      $producer;
		  $msg["latitude"]=    $latitude;
		  $msg["longitude"]=    $longitude;
		  $msg["frequency"]=    0;
		  $msg["broker"]=array();
		  $msg["broker"]["name"]=$contextbroker;
		  $msg["broker"]["type"]=$rowCB["protocol"];
		  $msg["broker"]["ip"]=$rowCB["ip"];
		  $msg["broker"]["port"]=$rowCB["port"];
		  $msg["broker"]["login"]=($rowCB["login"]==null)?"":$rowCB["login"];
		  $msg["broker"]["password"]=($rowCB["password"]==null)?"":$rowCB["password"];
		  $msg["broker"]["latitude"]=$rowCB["latitude"];
		  $msg["broker"]["longitude"]=$rowCB["longitude"];
		  $msg["broker"]["created"]=$rowCB["created"];
		   // $msg["attributes"]=array();
		  
		  $myAttrs=array();
		  $i=1;
		  foreach($listnewAttributes as $att)
		  {
			$myatt = array();
			$myatt["value_name"] =$att->value_name;
			$myatt["data_type"] =$att->data_type;
			$myatt["value_type"]=$att->value_type;
			$myatt["value_unit"]=$att->value_unit;
			$myatt["healthiness_criteria"]=$att->healthiness_criteria;
			if ($att->healthiness_criteria=="refresh_rate") $myatt["value_refresh_rate"]=$att->healthiness_value;
			else if ($att->healthiness_criteria=="different_values") $myatt["different_values"]=$att->different_values;
					 else  $myatt["value_bounds"]=$att->value_bounds;
			$myatt["order"]=$i++;
			$myAttrs[]=$myatt;
			
		  }	 
		  $msg["attributes"]=$myAttrs;
		  
		  echo json_encode($msg);	  
		  
		  try
		   {
			 $url="http://servicemap.disit.org/WebAppGrafo/api/v1/iot/insert";
			 $options = array(
					  'http' => array(
							  'header'  => "Content-Type: application/json;charset=utf-8",
							  'header'  => "Access-Control-Allow-Origin: *",
							  'method'  => 'POST',
				  'content' => json_encode($msg),
							  'timeout' => 30
					  )
				);
			 $context  = stream_context_create($options);
			 $result = @file_get_contents($url, false, $context);
		 } 
		 catch (Exception $ex) 
		  {
			$result="errore"; echo $ex; 
		 } 
         // echo "KB result ".$result; 			
		/* update of the device in the corresponding context broker */
		if ($result!="errore") 
		 {    
		 switch ($protocol)
		 {
		   case "ngsi": 
				 $res = update_ngsi($name, $type, $contextbroker, $kind, $protocol, $format, $model, $latitude, $longitude, $listnewAttributes, $ip, $port,$uri);
				 break;
		   case "mqtt":
				$res = update_mqtt($name, $type, $contextbroker, $kind, $protocol, $format, $latitude, $longitude, $listnewAttributes, $ip, $port,$uri);
				 break;
			case "amqp":	  
				$res = update_amqp($name, $type, $contextbroker, $kind, $protocol, $format, $latitude, $longitude, $listnewAttributes, $ip, $port,$uri);
				 break;	 
		 }	 
		}
			if ($res=="Ok") return $result;
			else return null;
	}
    else return null;	
   } // end of function updateKB	
               

    
    //Corpo dell'API
    $link = mysqli_connect($host, $username, $password) or die("Failed to connect to server");
    mysqli_select_db($link, $dbname);
    error_reporting(E_ERROR | E_NOTICE);
    
    if(!$link->set_charset("utf8")) 
    {
        echo '<script type="text/javascript">';
        echo 'alert("Error loading character set utf8: %s\n");';
        echo '</script>';
        exit();
    }

    if(isset($_REQUEST['register_confirm']))
    {
        session_start();
        if(isset($_SESSION['loggedRole']))
        {
            $username = mysqli_real_escape_string($link, $_POST['inputUsername']);
            $password = mysqli_real_escape_string($link, $_POST['inputPassword']); 
            $firstname = mysqli_real_escape_string($link, $_POST['inputNameUser']); 
            $lastname = mysqli_real_escape_string($link, $_POST['inputSurnameUser']);
            $email = mysqli_real_escape_string($link, $_POST['inputEmail']);

            //24/03/2017 - Cambierà via via che implementiamo la nuova profilazione utente
            if(isset($_POST['adminCheck'])) 
            {
                $valueAdmin = 1;
            } 
            else 
            {
                $valueAdmin = 0;
            }

            $selqDbtbCheck = "SELECT * FROM `Dashboard`.`Users` WHERE username='$username'";
            $resultCheck = mysqli_query($link, $selqDbtbCheck) or die(mysqli_error($link));

            if(mysqli_num_rows($resultCheck) > 0) 
            { 
                mysqli_close($link);
                echo '<script type="text/javascript">';
                echo 'alert("Username già in uso da altro utente: Ripetere registrazione");';
                echo 'window.location.href = "dashboard_register.php";';
                echo '</script>';
            } 
            else 
            {
                $insqDbtb = "INSERT INTO Dashboard.Users(IdUser, username, password, name, surname, email, reg_data, status, ret_code, admin) VALUES (NULL, '$username', '$password', '$firstname', '$lastname', '$email', now(), 1, 1, '$valueAdmin')";
                $result = mysqli_query($link, $insqDbtb) or die(mysqli_error($link));

                if($result) 
                {
                    mysqli_close($link);
                    echo '<script type="text/javascript">';
                    echo 'alert("Registrazione avvenuta con successo");';
                    echo 'window.location.href = "dashboard_mng.php";';
                    echo '</script>';
                } 
                else
                {
                    mysqli_close($link);
                    echo '<script type="text/javascript">';
                    echo 'alert("Error: Ripetere registrazione");';
                    echo 'window.location.href = "dashboard_register.php";';
                    echo '</script>';
                }
            }
        }
    }
    else if(isset($_REQUEST['login']))
    {
        $username = mysqli_real_escape_string($link, $_POST['loginUsername']);
        $ldapUsername = "cn=". $_POST['loginUsername'] . ",dc=ldap,dc=disit,dc=org";
        $password = mysqli_real_escape_string($link, $_POST['loginPassword']);
        $ldapPassword = $_POST['loginPassword'];
        $ldapOk = false;
        $file = fopen("C:\dashboardLog.txt", "w");
        fwrite($file, "LDAP server: " . $ldapServer . "\n");
        fwrite($file, "LDAP port: " . $ldapPort . "\n");
        //Per prima cosa verifichiamo se è su LDAP, altrimenti su account list locale
        //$ds = ldap_connect($ldapServer, $ldapPort);
        //fwrite($file, "LDAP connect: " . $ds . "\n");
        //ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
        //$bind = ldap_bind($ds, $ldapUsername, $ldapPassword);
        
        if($ldapActive == "yes")
        {
            if($ds && $bind)
            {
                if(checkLdapMembership($ds, $ldapUsername, $ldapTool))
                {
                   if(checkLdapRole($ds, $ldapUsername, "ToolAdmin"))
                   {
                      $ldapRole = "ToolAdmin";
                      $ldapOk = true;
                      ini_set('session.gc_maxlifetime', $sessionDuration);
                      session_set_cookie_params($sessionDuration);
                      session_start();
                      session_regenerate_id();
                      $_SESSION['loggedUsername'] = $username;
                      $_SESSION['loggedRole'] = "ToolAdmin";
                      $_SESSION['loggedType'] = "ldap";
                      notificatorLogin($username, $notificatorApiUsr, $notificatorApiPwd, $notificatorUrl, $ldapTool);
                      mysqli_close($link);
                      header("location: devices.php");
                   }
                   else
                   {
                       if(checkLdapRole($ds, $ldapUsername, "AreaManager"))
                       {
                          $ldapRole = "AreaManager";
                          $ldapOk = true;
                          ini_set('session.gc_maxlifetime', $sessionDuration);
                          session_set_cookie_params($sessionDuration);
                          session_start();
                          session_regenerate_id();
                          $_SESSION['loggedUsername'] = $username;
                          $_SESSION['loggedRole'] = "AreaManager";
                          $_SESSION['loggedType'] = "ldap";
                          notificatorLogin($username, $notificatorApiUsr, $notificatorApiPwd, $notificatorUrl, $ldapTool);
                          mysqli_close($link);
                          header("location: devices.php");
                       }
                       else
                       {
                          if(checkLdapRole($ds, $ldapUsername, "Manager"))
                          {
                             $ldapRole = "Manager";
                             $ldapOk = true;
                             ini_set('session.gc_maxlifetime', $sessionDuration);
                             session_set_cookie_params($sessionDuration);
                             session_start();
                             session_regenerate_id();
                             $_SESSION['loggedUsername'] = $username;
                             $_SESSION['loggedRole'] = "Manager";
                             $_SESSION['loggedType'] = "ldap";
                             notificatorLogin($username, $notificatorApiUsr, $notificatorApiPwd, $notificatorUrl, $ldapTool);
                             mysqli_close($link);
                             header("location: devices.php");
                          }
                       }
                   }
                }
            }
            else
            {
                
                fwrite($file, "LDAP fail\n");
            }
        }
        else
        {
            
            fwrite($file, "LDAP not active\n");
        }
        
        //Verifica su lista account locali se LDAP fallisce
        if(!$ldapOk)
        {
            $md5Pwd = md5($password);
            $query = "SELECT * FROM users WHERE username = '$username' AND password = '$md5Pwd' AND status = 1 AND admin <> 'Observer'";
            $result = mysqli_query($link, $query);

            if($result == false) 
            {
                die(mysqli_error($link));
            }

            if(mysqli_num_rows($result) > 0) 
            {
               $row = $result->fetch_assoc();
               ini_set('session.gc_maxlifetime', $sessionDuration);
               session_set_cookie_params($sessionDuration);
               session_start();
               session_regenerate_id();
               $_SESSION['sessionEndTime'] = time() + $sessionDuration;
               $_SESSION['loggedUsername'] = $username;
               $_SESSION['loggedRole'] = $row["admin"];
               $_SESSION['loggedType'] = "local";
               notificatorLogin($username, $notificatorApiUsr, $notificatorApiPwd, $notificatorUrl, $ldapTool);
               mysqli_close($link);
               header("location: devices.php");
            } 
            else 
            {
                mysqli_close($link);
                echo '<script type="text/javascript">';
                echo 'alert("Username e/o password errata/i: ripetere login");';
                echo 'window.location.href = "index.php";';
                echo '</script>';
            }
        }
    } 
    else if(isset($_POST['addDevice']))
    {
        // session_start();

        // if(isset($_SESSION['loggedRole']))
           if (true)
        {
		   //  print_r($_POST);
                        $id = mysqli_real_escape_string($link, $_POST['inputNameDevice']);
	                $devicetype = mysqli_real_escape_string($link, $_POST['inputTypeDevice']); 
			$contextbroker = mysqli_real_escape_string($link, $_POST['selectContextBroker']);  
			//$uri = mysqli_real_escape_string($link, $_POST['inputUriDevice']);
                        $kind = mysqli_real_escape_string($link, $_POST['selectKindDevice']);  
			$protocol = mysqli_real_escape_string($link, $_POST['selectProtocolDevice']);  
			$format = mysqli_real_escape_string($link, $_POST['selectFormatDevice']);  
			// $created = mysqli_real_escape_string($link, $_POST['createdDateDevice']);  
			$macaddress = mysqli_real_escape_string($link, $_POST['inputMacDevice']);  
			$model = mysqli_real_escape_string($link, $_POST['inputModelDevice']);  
			$producer = mysqli_real_escape_string($link, $_POST['inputProducerDevice']);  
			$latitude= mysqli_real_escape_string($link, $_POST['inputLatitudeDevice']);  
			$longitude = mysqli_real_escape_string($link, $_POST['inputLongitudeDevice']);  
 			$listnewAttributes= json_decode($_POST['newattributesJson']);
			$uri= registerKB($link, $id, $devicetype, $contextbroker, $kind, $protocol, $format, $macaddress, $model, $producer, $latitude, $longitude,  $listnewAttributes); 
			
			if ($uri==null)
			{
			 $q = "INSERT INTO devices(id, devicetype, contextBroker,  kind, protocol, format, macaddress, model, producer, latitude, longitude) " .
                 "VALUES('$id', '$devicetype', '$contextbroker', '$kind', '$protocol', '$format', '$macaddress', '$model', '$producer', '$latitude', '$longitude')";
			}
			else {
			$q = "INSERT INTO devices(id, devicetype, contextBroker,  kind, protocol, format, macaddress, model, producer, latitude, longitude,uri) " .
                 "VALUES('$id', '$devicetype', '$contextbroker', '$kind', '$protocol', '$format', '$macaddress', '$model', '$producer', '$latitude', '$longitude', '$uri')";
            }
			$r = mysqli_query($link, $q);
			
//	       echo $q;
            if($r) 
            {
			    $listnewAttributes= json_decode($_POST['newattributesJson']);
				$ok=true;$q="";
				$a=0;$b=1;
				while ($a < count($listnewAttributes) && $ok)
				{
				  $att=$listnewAttributes[$a];  
				  // print_r($att);
				 if ($att->healthiness_criteria=="refresh_rate") $hc="value_refresh_rate";
				 else if ($att->healthiness_criteria=="different_values") $hc="different_values";
				 else $hc="value_bounds";
				 
				  $insertquery="INSERT INTO `event_values`(`cb`, `device`, `value_name`, `data_type`, `order`, `value_type`, `editable`,`value_unit`,`healthiness_criteria`,`$hc`) VALUES ('$contextbroker','$id','$att->value_name','$att->data_type','$b','$att->value_type','$att->editable','$att->value_unit','$att->healthiness_criteria','$att->healthiness_value');";
				  echo $insertquery;
				  $r1 = mysqli_query($link, $insertquery);
	                          $ok= $ok && $r1; 			
    			          $b++;
				  $a++;
				}
                 if ($ok==true)
				 {
				    echo "Ok";
				 }
                 else
				 {
				   echo mysqli_error($link); echo "Ko";
				 }
         		 mysqli_close($link);
                
            } 
            else 
            {
                mysqli_close($link);
                echo "Ko";
            }
        }
    }
	
	else if(isset($_POST['updatebulkDevices']))
    {
       if (true)
        {
        
			$typeUpdate = mysqli_real_escape_string($link, $_POST['inputTypeDeviceM']); 
			$contextbrokerUpdate = mysqli_real_escape_string($link, $_POST['selectContextBrokerM']);  
			$kind = mysqli_real_escape_string($link, $_POST['selectKindDevice']);
			$protocolUpdate = mysqli_real_escape_string($link, $_POST['selectProtocolDeviceM']);  
			$formatUpdate = mysqli_real_escape_string($link, $_POST['selectFormatDeviceM']);  
			$listIdUpdate= json_decode($_POST['arrayChkIdJson']);
			
			
			$i=0;
			
			while ($i < count($listIdUpdate)){
				
				$idUpdate = $listIdUpdate[$i];
				$query="UPDATE devices SET `name`='". $att->name . "',`type`='" . $att->val . "',`order`=$b,`slabel`='". $att->label. "' WHERE  `cb`='$contextbrokerM' AND `device`='$nameM' AND name='". $att->name . "';";
				$r = mysqli_query($link, $query);
				
				
			}
      
			$q = "UPDATE devices SET uri = '$uri', type='$typeM', kind= '$kindM',contextBroker='$contextbrokerM', protocol='$protocolM', format='$formatM', macaddress='$macaddressM', model='$modelM', producer='$producerM', latitude='$latitudeM', longitude='$longitudeM' WHERE name='$nameM'";
            $r = mysqli_query($link, $q);
			
			// echo $q . $r;
            if($r) 
            {
				// print_r($listAttributes);
				$ok=true;$q="";
				$a=0;
				while ($a < count($listAttributes))
				{
				  $att=$listAttributes[$a];
				  $b=$a+1;
				  $upquery="UPDATE `attributes` SET `name`='". $att->name . "',`type`='" . $att->val . "',`order`=$b,`slabel`='". $att->label. "' WHERE  `cb`='$contextbrokerM' AND `device`='$nameM' AND name='". $att->name . "';";
				  $r1 = mysqli_query($link, $upquery);
	              $ok= $ok && $r1; 			
    				$a++;
				}
			   
                 if ($ok==true)
				 {
				    echo "Ok";
				 }
                 else
				 {
				   echo "Ko";
				 }
				 				
				mysqli_close($link);
            } 
            else 
            {
                mysqli_close($link);
                echo "Ko". mysqli_error($link);
            }
        }
    }
	
	
	else if(isset($_POST['updateDevice']))
    {
        // session_start();

        // if(isset($_SESSION['loggedRole']))
        if (true)
        {
            $idM = mysqli_real_escape_string($link, $_POST['inputNameDeviceM']);
			$devicetypeM = mysqli_real_escape_string($link, $_POST['inputTypeDeviceM']); 
			$kindM = mysqli_real_escape_string($link, $_POST['selectKindDeviceM']); 
			$contextbrokerM = mysqli_real_escape_string($link, $_POST['selectContextBrokerM']);  
			$uriM = mysqli_real_escape_string($link, $_POST['inputUriDeviceM']);  
			$protocolM = mysqli_real_escape_string($link, $_POST['selectProtocolDeviceM']);  
			$formatM = mysqli_real_escape_string($link, $_POST['selectFormatDeviceM']);  
			// $createdM = mysqli_real_escape_string($link, $_POST['createdDateDeviceM']);  
			$macaddressM = mysqli_real_escape_string($link, $_POST['inputMacDeviceM']);  
			$modelM = mysqli_real_escape_string($link, $_POST['inputModelDeviceM']);  
			$producerM = mysqli_real_escape_string($link, $_POST['inputProducerDeviceM']);  
			$latitudeM = mysqli_real_escape_string($link, $_POST['inputLatitudeDeviceM']);  
			$longitudeM = mysqli_real_escape_string($link, $_POST['inputLongitudeDeviceM']);  
			$listAttributes= json_decode($_POST['attributesJson']);
            $listnewAttributes= json_decode($_POST['newattributesJson']);

			if ($listAttributes==null) $merge=$listnewAttributes;
			else if ($listnewAttributes==null) $merge=$listAttributes;
			else $merge=array_merge($listAttributes,$listnewAttributes);
			
			 $uri= updateKB($link, $idM, $devicetypeM, $contextbrokerM, $kindM, $protocolM, $formatM, $macaddressM, $modelM, $producerM, $latitudeM, $longitudeM, $merge, $uriM); 
			
            if ($uri==null)
			{
			$q = "UPDATE devices SET devicetype='$devicetypeM', kind= '$kindM',contextBroker='$contextbrokerM', protocol='$protocolM', format='$formatM', macaddress='$macaddressM', model='$modelM', producer='$producerM', latitude='$latitudeM', longitude='$longitudeM' WHERE id='$idM'";
			}
            else {			
			$q = "UPDATE  devices SET uri = '$uri', mandatoryproperties=1, mandatoryvalues=1, devicetype='$devicetypeM', kind= '$kindM',contextBroker='$contextbrokerM', protocol='$protocolM', format='$formatM', macaddress='$macaddressM', model='$modelM', producer='$producerM', latitude='$latitudeM', longitude='$longitudeM' WHERE id='$idM'";}
            
			
			$r = mysqli_query($link, $q);
			
			echo $q . $r;
            if($r) 
            {
			
			 
				$ok=true;$q="";
				$a=0;
				while ($a < count($listAttributes))
				{
					
				  $att=$listAttributes[$a];	
				 if ($att->healthiness_criteria=="refresh_rate") 
                                      $hc="value_refresh_rate";
				 else if ($att->healthiness_criteria=="different_values") $hc="different_values";
				 else $hc="value_bounds";
				 
				 // print_r($att);
				  $b=$a+1;
				  $upquery="UPDATE `event_values` SET `value_name`='". $att->value_name . "',`data_type`='" . $att->data_type . "',`order`=$b,`value_type`='". $att->value_type. "', `editable`='". $att->editable. "', `value_unit`='". $att->value_unit."', `healthiness_criteria`='". $att->healthiness_criteria."', `value_refresh_rate`='". $att->healthiness_value ."'  WHERE  `cb`='$contextbrokerM' AND `device`='$idM' AND value_name='". $att->value_name . "';";
				  $r1 = mysqli_query($link, $upquery);
				//   echo $upquery;
                  	              $ok= $ok && $r1; 			
    				$a++;
				}
			    //echo "valore di ok". $ok;    
                               if ($ok==true)
				{
				 
				    $ok=true;$q="";
				    $a=0;
                                    // print_r($listnewAttributes); 
				    while ($a < count($listnewAttributes))
				    {
				        $att=$listnewAttributes[$a];
                                        // print_r($att);
				        $insertquery="INSERT INTO `event_values`(`cb`, `device`, `value_name`, `data_type`, `order`, `value_type`, `editable`, `value_unit`, `healthiness_criteria`, `$hc`) VALUES ('$contextbrokerM','$idM','$att->value_name','$att->data_type','$b','$att->value_type','$att->editable','$att->value_unit','$att->healthiness_criteria','$att->healthiness_value');";
				        $r1 = mysqli_query($link, $insertquery);
	                                echo $insertquery;
				        $ok= $ok && $r1; 			
    			                $b++;
				        $a++;
				    }
                                    if ($ok==true){ echo "Ok";}else{ echo "Ko"; }
				 
				}
				else
				{
				 echo "Ko";
				}
				mysqli_close($link);
            } 
            else 
            {
               echo mysqli_error($link). "Ko";
               mysqli_close($link);
            }
        }
    }
	
	else if(isset($_POST['addContextBrokerConfirmBtn']))
        {
        // session_start();

        // if(isset($_SESSION['loggedRole']))
        // {

            $name = mysqli_real_escape_string($link, $_POST['inputNameCB']);
            // $uri = mysqli_real_escape_string($link, $_POST['inputUriCB']);
            $ip = mysqli_real_escape_string($link, $_POST['inputIpCB']);
            $port = mysqli_real_escape_string($link, $_POST['inputPortCB']);
            $protocol = mysqli_real_escape_string($link, $_POST['selectProtocolCB']);
            // $created = mysqli_real_escape_string($link, $_POST['createdDateCB']);
            $latitude = mysqli_real_escape_string($link, $_POST['inputLatitudeCB']);
            $longitude = mysqli_real_escape_string($link, $_POST['inputLongitudeCB']);
            $login = mysqli_real_escape_string($link, $_POST['inputLoginCB']);
            $password = mysqli_real_escape_string($link, $_POST['inputPasswordCB']);

            $q = "INSERT INTO contextbroker(name, ip, protocol, port, latitude, longitude, login, password) " .
                 "VALUES('$name', '$ip', '$protocol',  '$port', '$latitude', '$longitude', '$login', '$password' )";
            $r = mysqli_query($link, $q);

            if($r)
            {
                mysqli_close($link);
                echo "Ok";
            }
            else
            {
                 echo '<script type="text/javascript">';
                 echo 'alert("Error: An error occurred when registering the context broker. <br/>' .
                   mysqli_error($link) . $q .
                       ' Please enter again the context broker")';
                 echo '</script>';
                echo "Ko";

            }
        // }
    }
   
 
	else if(isset($_REQUEST['editContextBroker']))
    {
        session_start();

        if(isset($_SESSION['loggedRole']))
        {
			
			$name = mysqli_real_escape_string($link, $_POST['inputNameCB']);
            $uri = mysqli_real_escape_string($link, $_POST['inputUriCB']);
            $ip = mysqli_real_escape_string($link, $_POST['inputIpCB']);
            $protocol = mysqli_real_escape_string($link, $_POST['selectProtocolCB']);
            $created = mysqli_real_escape_string($link, $_POST['createdDateCB']);
            $latitude = mysqli_real_escape_string($link, $_POST['inputLatitudeCB']);
            $longitude = mysqli_real_escape_string($link, $_POST['inputLongitudeCB']);
            $login = mysqli_real_escape_string($link, $_POST['inputLoginCB']);
            $password = mysqli_real_escape_string($link, $_POST['inputPasswordCB']);
			
            
            $q = "UPDATE contextbroker SET name = '$name', uri = '$uri', ip = '$ip', protocol = '$protocol', created = '$created', latitude = '$latitude', longitude = '$longitude', login = '$login', password = '$password' WHERE name = '$name'";
            $r = mysqli_query($link, $q);

            if($r) 
            {
                mysqli_close($link);
                echo "Ok";
            } 
            else 
            {
                mysqli_close($link);
                echo "Ko";
            }
        }
    }
    
    elseif(isset($_REQUEST['updateConfigFile']))
    {
        session_start();
        
        if(isset($_SESSION['loggedRole']))
        {
           if($_SESSION['loggedRole'] == "ToolAdmin")
           {
                $fileName = $_POST['fileName'];
                $fileOriginalContent = parse_ini_file("../conf/" . $fileName);
                try 
                {
                    $fileOriginalContent = parse_ini_file("../conf/" . $fileName);
                } 
                catch(Exception $e) 
                {
                    echo "parsingOriginalFileKo";
                    exit();
                }

                try 
                {
                    $file = fopen("../conf/" . $fileName, "w");
                } 
                catch(Exception $e) 
                {
                    echo "openingOriginalFileKo";
                    exit();
                }

                switch($fileName)
                {
                    case "environment.ini":
                        $newActiveEnv = $_REQUEST['activeEnvironment'];
                        $fileOriginalContent["environment"]["value"] = $newActiveEnv;
                        break;

                    default:
                        $dataFromForm = json_decode($_REQUEST['data']);

                        for($i = 0; $i < count($dataFromForm); $i++)
                        {
                            foreach($dataFromForm[$i] as $key => $value) 
                            {
                                if($key == "name")
                                {
                                    $updatedKey = $value;
                                }
                                else
                                {
                                    if($key == "value")
                                    {
                                        $updatedValue = $value;
                                        if(strpos($updatedKey, '[dev]') !== false)
                                        {
                                            $shortKey = str_replace("[dev]", "", $updatedKey);
                                            $fileOriginalContent[$shortKey]["dev"] = $updatedValue;
                                        }

                                        if(strpos($updatedKey, '[test]') !== false)
                                        {
                                            $shortKey = str_replace("[test]", "", $updatedKey);
                                            $fileOriginalContent[$shortKey]["test"] = $updatedValue;
                                        }

                                        if(strpos($updatedKey, '[prod]') !== false)
                                        {
                                            $shortKey = str_replace("[prod]", "", $updatedKey);
                                            $fileOriginalContent[$shortKey]["prod"] = $updatedValue;
                                        }
                                    }
                                }
                            }
                        }
                        break;
                }

                foreach($fileOriginalContent as $key => $value) 
                {
                    if(is_array($value))
                    {
                        foreach($value as $subkey => $subvalue) 
                        {
                            try 
                            {
                                fwrite($file, $key . "[" . $subkey . "] = '" . $subvalue . "'\n");
                            } 
                            catch(Exception $e) 
                            {
                                echo "writingOriginalFileKo";
                                exit();
                            }
                        }
                    }
                    else
                    {
                        try 
                        {
                            fwrite($file, $key . " = '" . $value . "'\n");
                        } 
                        catch(Exception $e) 
                        {
                            echo "writingOriginalFileKo";
                            exit();
                        }
                    }
                }

                echo "Ok";
           }
        }
    }
    elseif(isset($_REQUEST['deleteConfigFile']))
    {
        session_start();
        
        if(isset($_SESSION['loggedRole']))
        {
           if($_SESSION['loggedRole'] == "ToolAdmin")
           {
               $fileName = $_POST['fileName'];
        
                try 
                {
                    if(unlink("../conf/" . $fileName))
                    {
                        echo "Ok";
                        exit();
                    }
                    else
                    {
                        echo "deleteModuleFileKo";
                        exit();
                    }
                } 
                catch(Exception $e) 
                {
                    echo "deleteModuleFileKo";
                    exit();
                }
           }
        }
    }
 else if(isset($_POST['addValue']))
        {
			  
			$cb = mysqli_real_escape_string($link, $_POST['selectContextBroker']);
            $device = mysqli_real_escape_string($link, $_POST['inputNameDevice']);
            $valueName = mysqli_real_escape_string($link, $_POST['inputValueNameDevice']);
            $dType = mysqli_real_escape_string($link, $_POST['selectDataType']);
            $valueType = mysqli_real_escape_string($link, $_POST['selectValueType']);
            $editable = mysqli_real_escape_string($link, $_POST['inputEditableValue']);
            $valueUnit = mysqli_real_escape_string($link, $_POST['selectValueUnit']);
            $hCriteria = mysqli_real_escape_string($link, $_POST['selectHealthinessCriteria']);
            $hValue = mysqli_real_escape_string($link, $_POST['inputHealthinessValue']);
			$order = mysqli_real_escape_string($link, $_POST['inputOrder']);
			
		     
			$query = "INSERT INTO event_values(cb, device, value_name, data_type, value_type, editable, value_unit, healthiness_criteria, value_refresh_rate, `order`) " .
                 "VALUES('$cb', '$device', '$valueName',  '$dType', '$valueType', '$editable', '$valueUnit', '$hCriteria', '$hValue', '$order' )";
            $result = mysqli_query($link, $query);

            if($result)
            {
                echo "Ok";
            }
            else
            {
                 echo "Ko";
             
            }
        
    }
	
	 elseif(isset($_POST['deleteValue']))
    {
         if(true)
        {
          	$device = mysqli_real_escape_string($link, $_POST['device']);
			$cb = mysqli_real_escape_string($link, $_POST['cb']); 
			$value_name = mysqli_real_escape_string($link, $_POST['value_name']); 
			
			$query = "DELETE FROM event_values WHERE device = '$device' and cb = '$cb' and value_name = '$value_name'";
			$result = mysqli_query($link, $query);
	
		   if($result)
		   {
				 mysqli_close($link);
				 echo 1;
		   }
		   else
		   {
				mysqli_error($link);
				mysqli_close($link);
				 echo 0;
		   }
        }
    }
	
	else if(isset($_POST['updateValue']))
    {
 
        if (true)
        {
			
            $selectContextBrokerM = mysqli_real_escape_string($link, $_POST['selectContextBrokerM']);
			$inputNameDeviceM = mysqli_real_escape_string($link, $_POST['inputNameDeviceM']); 
			$inputValueNameDeviceM = mysqli_real_escape_string($link, $_POST['inputValueNameDeviceM']); 	 
			$selectDataTypeM = mysqli_real_escape_string($link, $_POST['selectDataTypeM']);  
			$selectValueTypeM = mysqli_real_escape_string($link, $_POST['selectValueTypeM']);  
			$inputEditableValueM = mysqli_real_escape_string($link, $_POST['inputEditableValueM']);  
			$selectValueUnitM = mysqli_real_escape_string($link, $_POST['selectValueUnitM']);  
			$selectHealthinessCriteriaM = mysqli_real_escape_string($link, $_POST['selectHealthinessCriteriaM']);  
			$inputHealthinessValueM = mysqli_real_escape_string($link, $_POST['inputHealthinessValueM']);  
			$inputOrderM = mysqli_real_escape_string($link, $_POST['inputOrderM']);  

			$query = "UPDATE event_values SET cb='$selectContextBrokerM', device= '$inputNameDeviceM', value_name='$inputValueNameDeviceM', data_type='$selectDataTypeM', value_type='$selectValueTypeM', editable='$inputEditableValueM', value_unit='$selectValueUnitM', healthiness_criteria='$selectHealthinessCriteriaM', value_refresh_rate='$inputHealthinessValueM', `order`='$inputOrderM' WHERE device='$inputNameDeviceM' AND cb='$selectContextBrokerM' AND value_name='$inputValueNameDeviceM'";
			
			$result = mysqli_query($link, $query);
					
			echo ($query);
		
			
			if($result) 
            {
                echo "Ok";
            } 
            else
            {
                echo "Ko";		
            }

       
          // echo "Ko". mysqli_error($link); 
        }
    }
