<?php

 
   include '../config.php';
   require '../sso/autoload.php';
   use Jumbojett\OpenIDConnectClient; 

   $ldapRole = null;
   $ldapOk = false;

   $page = 'mydevices.php';
   if(isset($_REQUEST['redirect']))
	   $page = $_REQUEST['redirect'];
   
   $oidc = new OpenIDConnectClient(
        $keycloakHostUri,
        $clientId,
        $clientSecret
    );

    $oidc->setVerifyHost(false);
    $oidc->setVerifyPeer(false);

    $oidc->providerConfigParam(array('authorization_endpoint'=>$keycloakHostUri.'/auth/realms/master/protocol/openid-connect/auth'));
    $oidc->providerConfigParam(array('token_endpoint'=>$keycloakHostUri.'/auth/realms/master/protocol/openid-connect/token'));
    $oidc->providerConfigParam(array('userinfo_endpoint'=>$keycloakHostUri.'/auth/realms/master/protocol/openid-connect/userinfo'));
    $oidc->providerConfigParam(array('jwks_uri'=>$keycloakHostUri.'/auth/realms/master/protocol/openid-connect/certs'));
    $oidc->providerConfigParam(array('issuer'=>$keycloakHostUri.'/auth/realms/master'));
    $oidc->providerConfigParam(array('end_session_endpoint'=>$keycloakHostUri.'/auth/realms/master/protocol/openid-connect/logout'));

    $oidc->addScope(array('openid','username','profile'));
    $oidc->setRedirectURL($redirectUri . '/management/ssoLogin.php?redirect='.$page);

    $oidc->authenticate();

    $username = $oidc->requestUserInfo('preferred_username');
    $ldapUsername = "cn=". $username . ",".$ldapBaseName;

    $ds = ldap_connect($ldapServer, $ldapPort);
    ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
    $bind = ldap_bind($ds, $ldapAdminName, $ldapAdminPwd);



    if($ds && $bind)
    {
        if(checkLdapMembership($ds, $ldapUsername, $ldapToolName, $ldapBaseName))
        {
           $organization= findLdapOrganizationalUnit($ds, $ldapUsername, $ldapToolName, $ldapBaseName);
           if(checkLdapRole($ds, $ldapUsername, "RootAdmin", $ldapBaseName))
           {
              $ldapRole = "RootAdmin";
              $ldapOk = true;
           }
           else if(checkLdapRole($ds, $ldapUsername, "ToolAdmin", $ldapBaseName))
           {
              $ldapRole = "ToolAdmin";
              $ldapOk = true;
           }
           else
           {
               if(checkLdapRole($ds, $ldapUsername, "AreaManager", $ldapBaseName))
               {
                  $ldapRole = "AreaManager";
                  $ldapOk = true;
               }
               else
               {
                  if(checkLdapRole($ds, $ldapUsername, "Manager", $ldapBaseName)) {
                     $ldapRole = "Manager";
                     $ldapOk = true;
                  } else {
					  $msg = "user $username does not have a role";
				  } /* else { //uncomment if Public role is managed
				     $ldapRole = "Public";
					 $ldapOk = true;
				  } */
               }
           }
        } else {
			$msg = "user $username cannot access to tool $ldapToolName";
		}
    } else {
		$msg = "cannot bind to LDAP server $ldapServer";
	}

    if($ldapOk)
    {

        ini_set('session.gc_maxlifetime', $sessionDuration);
        session_set_cookie_params($sessionDuration);
        $_SESSION['sessionEndTime'] = time() + $sessionDuration;
        $_SESSION['loggedUsername'] = $username;
        $_SESSION['loggedRole'] = $ldapRole;
        $_SESSION['loggedType'] = "ldap";
        $_SESSION['refreshToken']=$oidc->getRefreshToken();
        $_SESSION['accessToken']=$oidc->getAccessToken();
        $_SESSION['organization']=$organization;
        $_SESSION['kbUrl']="";
            $_SESSION['gpsCentreLatLng']="";
            $_SESSION['zoomLevel']="";
        
        if($organization!="NULL"){
            $info=get_organization_info($organizationApiURI, $organization);
            $_SESSION['kbUrl']=$info["kbUrl"];
            $_SESSION['gpsCentreLatLng']=$info["gpsCentreLatLng"];
            $_SESSION['zoomLevel']=$info["zoomLevel"];    
        }
        
        
        header("Location: $page");
    }
    else
    {
		echo $msg;
    }
	
function checkLdapMembership($connection, $userDn, $tool, $ldapBaseName) 
{
	 $result = ldap_search($connection, $ldapBaseName, '(&(objectClass=posixGroup)(memberUid=' . $userDn . '))');
	 $entries = ldap_get_entries($connection, $result);
	 foreach ($entries as $key => $value) 
	 {
		if(is_numeric($key)) 
		{
		   if($value["cn"]["0"] == $tool) {
			  return true;
		   }
		}
	 }
	 return false;
 }

function checkLdapRole($connection, $userDn, $role, $ldapBaseName) 
{
  $result = ldap_search($connection, $ldapBaseName, '(&(objectClass=organizationalRole)(cn=' . $role . ')(roleOccupant=' . $userDn . '))');
  $entries = ldap_get_entries($connection, $result);
  foreach ($entries as $key => $value) 
  {
	 if(is_numeric($key)) 
	 {
		if($value["cn"]["0"] == $role) {
		   return true;
		}
	 }
  }
  return false;
}

//Fatima
function findLdapOrganizationalUnit($connection, $userDn, $tool, $ldapBaseName) 
{
	$result = ldap_search($connection, $ldapBaseName, '(&(objectClass=organizationalUnit)(l=' . $userDn . '))');
	$entries = ldap_get_entries($connection, $result);
	
    //Print $entries here
    //echo_log(var_dump($entries));
    //echo_log($entries);
    


    if (ldap_count_entries($connection,$result)==0){
        //TODO thrown an error or return an error
//        echo_log("No LDAP organization Unit found for user".$userDn);
        return "NULL";
        }
    else{
        $ou=$entries["0"]["ou"][0];
  //      echo_log("Organization found is:".$ou);
        return $ou;
        
    }
	// foreach ($entries as $key => $value){
		// if(is_numeric($key)){
		   // if($value["ou"]["0"] == $tool) {
			  // return true;
		   // }
		// }
	// }
}

//Fatima: to get info related to organization
function get_organization_info($organizationApiURI, $ou_tmp){
	$url = $organizationApiURI.'organizations.php?org='.$ou_tmp;
	$context = stream_context_create(null);
	$result = file_get_contents($url, false, $context);


    $result_json = json_decode($result, true);
	if(sizeof($result_json)==1){
		return $result_json[0];
	}
	else{
		return null;
	}
}

//Fatima for organizations testing
function echo_log($words) {
 /* $fp=fopen("../log/echo_log_org.txt","a") or die("Unable to open file!");;
  flock($fp,LOCK_EX);
  $output = date("Y-m-d h:i:sa") . ": ". $words . "\r\n";
  fwrite($fp,$output);
  flock($fp,LOCK_UN);
  fclose($fp);*/
}
