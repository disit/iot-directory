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

		   $name = mysqli_real_escape_string($link, $_POST['inputNameDevice']);
			$type = mysqli_real_escape_string($link, $_POST['inputTypeDevice']); 
			$contextbroker = mysqli_real_escape_string($link, $_POST['selectContextBroker']);  
			$uri = mysqli_real_escape_string($link, $_POST['inputUriDevice']);  
			$protocol = mysqli_real_escape_string($link, $_POST['selectProtocolDevice']);  
			$format = mysqli_real_escape_string($link, $_POST['selectFormatDevice']);  
			$created = mysqli_real_escape_string($link, $_POST['createdDateDevice']);  
			$macaddress = mysqli_real_escape_string($link, $_POST['inputMacDevice']);  
			$model = mysqli_real_escape_string($link, $_POST['inputModelDevice']);  
			$producer = mysqli_real_escape_string($link, $_POST['inputProducerDevice']);  
			$latitude= mysqli_real_escape_string($link, $_POST['inputLatitudeDevice']);  
			$longitude = mysqli_real_escape_string($link, $_POST['inputLongitudeDevice']);  
			//$properties = null;
			//$attributes = null;
			
			$insqDbtb2 = "INSERT INTO iotdirectorydb.devices
                   
				   
				   (name, type, contextBroker, uri, protocol,
                    format, created, macaddress, model, producer, 
					latitude, longitude, properties, attributes) 
                    VALUES ('$name', '$type', '$contextbroker', '$uri',
					$protocol, $format, '$created', '$macaddress', 
					'$model', $producer, '$latitude', '$longitude', '$properties', 
					'$attributes')";
					
           
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

