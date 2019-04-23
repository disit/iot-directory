<?php
 

    header("Access-Control-Allow-Origin: *");
    include '../config.php';
   // require '../phpmailer/PHPMailerAutoload.php';
    
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

            $selqDbtbCheck = "SELECT * FROM users WHERE username='$username'";
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
                      header("location: value.php");
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
                             header("location: value.php");
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
               header("location: value.php");
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
 