<?php
  
   include '../config.php';
   require '../sso/autoload.php';
   use Jumbojett\OpenIDConnectClient; 

   $ldapServer = '192.168.0.137';   
   $ldapRole = null;
   $ldapOk = false;
   $ldapToolName = "IOTDirectory";

   $appUrl = 'https://iotdirectory.snap4city.org';
   
   $page = 'contextbroker.php';
   if(isset($_REQUEST['redirect']))
	   $page = $_REQUEST['redirect'];
   
   $oidc = new OpenIDConnectClient(
        'https://www.snap4city.org',
        'php-iot-directory',
        '1893827e-22e0-431e-9d11-8a44e2260c3a'
    );

    $oidc->setVerifyHost(false);
    $oidc->setVerifyPeer(false);

    $oidc->providerConfigParam(array('authorization_endpoint'=>'https://www.snap4city.org/auth/realms/master/protocol/openid-connect/auth'));
    $oidc->providerConfigParam(array('token_endpoint'=>'https://www.snap4city.org/auth/realms/master/protocol/openid-connect/token'));
    $oidc->providerConfigParam(array('userinfo_endpoint'=>'https://www.snap4city.org/auth/realms/master/protocol/openid-connect/userinfo'));
    $oidc->providerConfigParam(array('jwks_uri'=>'https://www.snap4city.org/auth/realms/master/protocol/openid-connect/certs'));
    $oidc->providerConfigParam(array('issuer'=>'https://www.snap4city.org/auth/realms/master'));
    $oidc->providerConfigParam(array('end_session_endpoint'=>'https://www.snap4city.org/auth/realms/master/protocol/openid-connect/logout'));

    $oidc->addScope(array('openid','username','profile'));
    $oidc->setRedirectURL($appUrl . '/management/ssoLogin.php?redirect='.$page);
    $oidc->authenticate();

    $username = $oidc->requestUserInfo('preferred_username');
    $ldapUsername = "cn=". $username . ",dc=ldap,dc=disit,dc=org";

    $ds = ldap_connect($ldapServer, $ldapPort);
    ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
    $bind = ldap_bind($ds);

    if($ds && $bind)
    {
        if(checkLdapMembership($ds, $ldapUsername, $ldapToolName))
        {
           if(checkLdapRole($ds, $ldapUsername, "RootAdmin"))
           {
              $ldapRole = "RootAdmin";
              $ldapOk = true;
           }
           else if(checkLdapRole($ds, $ldapUsername, "ToolAdmin"))
           {
              $ldapRole = "ToolAdmin";
              $ldapOk = true;
           }
           else
           {
               if(checkLdapRole($ds, $ldapUsername, "AreaManager"))
               {
                  $ldapRole = "AreaManager";
                  $ldapOk = true;
               }
               else
               {
                  if(checkLdapRole($ds, $ldapUsername, "Manager")) {
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
        
        header("Location: $page");
    }
    else
    {
		echo $msg;
    }
	
function checkLdapMembership($connection, $userDn, $tool) 
{
	 $result = ldap_search($connection, 'dc=ldap,dc=disit,dc=org', '(&(objectClass=posixGroup)(memberUid=' . $userDn . '))');
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

function checkLdapRole($connection, $userDn, $role) 
{
  $result = ldap_search($connection, 'dc=ldap,dc=disit,dc=org', '(&(objectClass=organizationalRole)(cn=' . $role . ')(roleOccupant=' . $userDn . '))');
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
