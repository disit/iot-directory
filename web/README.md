# iot-directory
This component is a PHP web application

## Requirements
- PHP 5.3.3 (not tested with PHP 7)
- MySQL 10.1.31 (MariaDB)
- Node JS 8.9.4

## Install
- Copy the front end folder in a web accessible directory (i.e. /var/www/html) and rename it as you like, or create a symb link
>debian@debian:~$ git clone https://github.com/disit/iot-directory.git  
>root@debian:/var/www/html# ln -s /home/debian/iot-directory/web iot2  
- Import DB tables
>execute createtables.sql in MySQL shell  

## Configure
- Edit /conf/environment.php to select the profile you want to use
>environment[value] = "prod"
- Edit /conf/database.ini  to set the host and credentials for the DB
>username[prod] = "username"  
>password[prod] = "password"  
- Edit /conf/serviceURI.ini to specify the endpoint for ownerships, delegation and knowledge base
>ownershipURI[prod] = "http://localhost/"  
>knowledgeBaseURI[prod] = http://kbssm:8080/ServiceMap/  
>delegationURI[prod] = http://localhost:8080/  
>organizationApiURI[prod] = http://localhost/dashboardSmartCity/api/  
- Change the permissions to the img folder to allow the webserver to write in this folder. (e.g. "chown -R www-data:www-data img")
>not needed?  
- In a private directory, create two folder (“log” and “certificate”) to store logs and certificates. These directory paths should be reported in the “/conf/general.ini”. Make sure to grant writing permissions on these directories.
>root@debian:/home/debian/iot-directory# mkdir certificate  
>root@debian:/home/debian/iot-directory# chown www-data:www-data -R certificate  
>root@debian:/home/debian/iot-directory# mkdir log  
>root@debian:/home/debian/iot-directory# chown www-data:www-data -R log  
- Create keyclock client id for iot-directory  
>client ID: php-iot-directory  
>valid redirect uris: http://dashboard/*  
>secret: <php-iot-directory-secret>  
>create the mapper for “roles”  
- Edit /conf/sso.ini
>-clientId[prod] = "php-iot-directory"  
>-clientSecret[prod] = "<php-iot-directory-secret>"  
>-redirectUri[prod] = http://dashboard/iot2  
>-keycloakHostUri[prod] = "http://dashboard:8088"  
- Create in LDAP the group for IOTDirectory and add in this group the users enabled to access the IOTDirectory
>-CN=IOTDirectory  
>-objectClass=posixGroup, top   
>-gidNumber=503  
- Edit /conf/ldap.ini
>-ldapBaseName[prod] = "dc=ldap,dc=organization,dc=com"  
- The "snap4cityServer" directory contains the nodejs code used for IoTDirectory Server  
>-From snap4cityServer folder, digit "npm install" to download locally the dependencies  
>-Configure the information in snap4cityServer/snap4cityBroker/db_config.ini  
>-TODO: Map the folder stub in apache  
>-TODO: Copy the Parser folder snap4cityServer/snap4cityBroker  
>-Digit "nodejs snapIoTDirectory_rw.js & > log.txt" to launch the server in background modality and to log the output in a log.txt named file  
- Update table "limits" (in schema "profiled") and table "Organizations" (in schema "Dashboard")
>In table limits add entry for brokerid, iotid, modelid  
>In table Organization update entry for kb url  

## (optional) Configure CA

To be executed just ONCE:

- copy in the certificate folder you created before all the contents available on master/web/certificate. make executable all the .sh file (chmod 755)
- configure information for the CA, editing exporting variable
>root@debian:/home/debian/iot-directory/certificate# vi generate-ca-keys.sh  
- generate CA information
>root@debian:/home/debian/iot-directory/certificate# ./generate-ca-keys.sh  
- take note of the specified IOTDIR_CA_PASSWORD and backup the private/ca-key.pem and ca-crt.pem in a safe storage
- configure information for the Client, editing exporting variable (edit just IOTDIR_CA_CRL_URI and IOTDIR_CA_PASSWORD)
>root@debian:/home/debian/iot-directory/certificate# vi generate-device-keys.sh  

## (optional) Configure Orion Broker

To be executed for any new Orion Broker you want to add:

- configure information for the new Orion Broker, editing exporting variable. Note, the Common name has to be different from the CA Common Name
>root@debian:/home/debian/iot-directory/certificate# vi generate-server-keys.sh  
- generate Orion Broker information
>root@debian:/home/debian/iot-directory/certificate# ./generate-ca-keys.sh  
- take note of the specified IOTDIR_SERVER_PK12_PASSWORD and backup the private/server-IOTDIR_SERVER_COMMON_NAME-key.pem, certsdb/server-$IOTDIR_SERVER_COMMON_NAME-crt.pem, server-$IOTDIR_SERVER_COMMON_NAME-crt.p12 in a safe storage
- copy the ca-crt.pem and server-$IOTDIR_SERVER_COMMON_NAME-crt.p12 in the Orion Broker VM (usually /opt/tomcat/conf)

In the Orion Broker VM
- configure information for the new Orion Broker, editing the first three exporting variable (IOTDIR_SERVER_COMMON_NAME, IOTDIR_SERVER_PK12_PASSWORD, IOTDIR_SERVER_TRUSTSTORE_PASSWORD)
>root@iotobsf:/opt/tomcat/conf# vi generate-server-conf.sh (the script has to be copied from orion filter home folder to /opt/tomcat/conf)  
- generate Orion Broker truststore and keystore
>root@iotobsf:/opt/tomcat/conf# ./generate-server-conf.sh
- configure tomcat conf/server.xml with proper password you insert before
>root@iotobsf:/opt/tomcat/conf# vi server.xml 
><Connector allowHostHeaderMismatch="true" SSLEnabled="true" clientAuth="want" truststoreFile="conf/caCerts.jks" truststorePass="<IOTDIR_SERVER_TRUSTSTORE_PASSWORD>" keystoreFile="conf/tomcat.keystore" keystorePass="<IOTDIR_SERVER_PK12_PASSWORD>" maxThreads="200" port="8443" protocol="org.apache.coyote.http11.Http11NioProtocol" scheme="https" secure="true" sslProtocol="TLS" sslEnabledProtocols="TLSv1.3,TLSv1.2,TLSv1.1" ciphers="TLS_RSA_WITH_AES_128_CBC_SHA, TLS_RSA_WITH_AES_256_CBC_SHA"/>  
-restart tomcat  

## License

This project is licensed under the GNU Affero General Public License - see the [LICENSE.md](LICENSE) file for details
