<?php

/*Dashboard Builder.
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

   include '../config.php';
   require '../phpmailer/PHPMailerAutoload.php';
   
   //Altrimenti restituisce in output le warning
   error_reporting(E_ERROR | E_NOTICE);
   
  
   //Corpo dell'API
   session_start(); 
   $link = mysqli_connect($host, $username, $password) or die("Failed to connect to server");
   mysqli_select_db($link, $dbname);
   
   $queryFail = null;
   
   if(!$link->set_charset("utf8")) 
   {
       die();
   }
   
   if(isset($_SESSION['loggedRole']))
   {
       if($_SESSION['loggedRole'] == "ToolAdmin")
       {

		   $newDeviceJson = json_decode($_POST['newDeviceJson']);
		   $inputNameDevice = mysqli_real_escape_string($link, $newDeviceJson->inputNameDevice); 
		   $inputTypeDevice = mysqli_real_escape_string($link, $newDeviceJson->inputTypeDevice);
		   $selectContextBroker = mysqli_real_escape_string($link, $newDeviceJson->selectContextBroker);
		   $inputUriDevice = mysqli_real_escape_string($link, $newDeviceJson->inputUriDevice);
		   $selectProtocolDevice = mysqli_real_escape_string($link, $newDeviceJson->selectProtocolDevice);
		   $selectFormatDevice = mysqli_real_escape_string($link, $newDeviceJson->selectFormatDevice);
		   $createdDateDeviceM = mysqli_real_escape_string($link, $newDeviceJson->createdDateDeviceM);
		   $inputMacDevice = mysqli_real_escape_string($link, $newDeviceJson->inputMacDevice);
		   $inputModelDevice = mysqli_real_escape_string($link, $newDeviceJson->inputModelDevice);
		   $inputProducerDevice = mysqli_real_escape_string($link, $newDeviceJson->inputProducerDevice);
		   $inputLatitudeDevice = mysqli_real_escape_string($link, $newDeviceJson->inputLatitudeDevice);
		   $inputLongitudeDevice = mysqli_real_escape_string($link, $newDeviceJson->inputLongitudeDevice);
		   $inputPropertiesDevice = mysqli_real_escape_string($link, $newDeviceJson->inputPropertiesDevice);
		   $inputAttributesDevice = mysqli_real_escape_string($link, $newDeviceJson->inputAttributesDevice);
			   
           
           //Controllo presenza username
           $query = "INSERT INTO iotdirectorydb.devices(name, type, contextBroker, uri, protocol, format, created, macaddress, model, producer, latitude, longitude, properties, attributes) VALUES 
		   ('$inputNameDevice', '$inputTypeDevice', '$selectContextBroker', '$inputUriDevice',
		   '$selectProtocolDevice', '$selectFormatDevice', '$createdDateDeviceM', '$inputMacDevice',
		   '$inputModelDevice', '$inputProducerDevice', '$inputLatitudeDevice', '$inputLongitudeDevice', '$inputPropertiesDevice', '$inputAttributesDevice')";
           $result = mysqli_query($link, $query) or die(mysqli_error($link));
           
          if($result) 
         {
            mysqli_close($link);
            echo 1;
         }
         else
         {
            mysqli_close($link);
            echo 0;
         }
       }
   }

