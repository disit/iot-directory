<?php

/* Snap4City: IoT-Directory
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
    include('../config.php');

	//include('process-form.php');
    session_start();


	 ///// SHOW FRAME PARAMETER /////
if (isset($_REQUEST['showFrame'])){
	if ($_REQUEST['showFrame'] == 'false'){
		//echo ('true');
		$hide_menu= "hide";
	}else{
		$hide_menu= "";
	}	
}else{$hide_menu= "";} 
//// SHOW FRAME PARAMETER  ////

if (!isset($_GET['pageTitle'])){
	$default_title = "IOT Broker Device Discovery";
}else{
	$default_title = "";
}

if (isset($_REQUEST['redirect'])){
	$access_denied = "denied";
}else{
	$access_denied = "";
}	

	$link = mysqli_connect($host, $username, $password);
    mysqli_select_db($link, $dbname);

    if(!isset($_SESSION['loggedRole']))
    {
        header("location: unauthorizedUser.php");
    }


/*require '../sso/autoload.php';
use Jumbojett\OpenIDConnectClient;
 if (isset($_SESSION['refreshToken'])) {
   $oidc = new OpenIDConnectClient('https://www.snap4city.org', $clientId, $clientSecret);
   $oidc->providerConfigParam(array('token_endpoint' => 'https://www.snap4city.org/auth/realms/master/protocol/openid-connect/token'));
   $tkn = $oidc->refreshToken($_SESSION['refreshToken']);
   $accessToken = $tkn->access_token;
   $_SESSION['refreshToken'] = $tkn->refresh_token;
}
else 
	$accessToken ="";
  */
  $accessToken = "";
?>



<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Snap4City IoT Directory</title>

        <!-- Bootstrap Core CSS -->
        <link href="../css/bootstrap.css" rel="stylesheet">

        <link href="../css/bootstrap-colorpicker.min.css" rel="stylesheet">

        <!-- jQuery -->
        <script src="../js/jquery-1.10.1.min.js"></script>

        <!-- JQUERY UI -->
        <script src="../js/jqueryUi/jquery-ui.js"></script>

        <!-- Bootstrap Core JavaScript -->
        <script src="../js/bootstrap.min.js"></script>

        <!-- Custom Core JavaScript -->
        <script src="../js/bootstrap-colorpicker.min.js"></script>

        <!-- Bootstrap toggle button -->
       <link href="../bootstrapToggleButton/css/bootstrap-toggle.min.css" rel="stylesheet">
       <script src="../bootstrapToggleButton/js/bootstrap-toggle.min.js"></script>

       <!-- Bootstrap editable tables -->
       <link href="https://cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet"/>
       <script src="https://cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/js/bootstrap-editable.min.js"></script>

       <!-- Bootstrap table -->
       <link rel="stylesheet" href="../boostrapTable/dist/bootstrap-table.css">
       <script src="../boostrapTable/dist/bootstrap-table.js"></script>
	   <script src="../boostrapTable/dist/bootstrap-table-filter-control.js"></script>

	   	   <!-- DataTables -->

	    <script type="text/javascript" charset="utf8" src="../js/DataTables/datatables.js"></script>
        <link rel="stylesheet" type="text/css" href="../js/DataTables/datatables.css">
        <script type="text/javascript" charset="utf8" src="../js/DataTables/dataTables.bootstrap.min.js"></script>
        <script type="text/javascript" charset="utf8" src="../js/DataTables/dataTables.responsive.min.js"></script>
        <script type="text/javascript" charset="utf8" src="../js/DataTables/responsive.bootstrap.min.js"></script>


        <link rel="stylesheet" type="text/css" href="../css/DataTables/dataTables.bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="../css/DataTables/responsive.bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="../css/DataTables/jquery.dataTables.min.css">


       <!-- Questa inclusione viene sempre DOPO bootstrap-table.js -->
       <script src="../boostrapTable/dist/locale/bootstrap-table-en-US.js"></script>

       <!-- Bootstrap slider -->
        <script src="../bootstrapSlider/bootstrap-slider.js"></script>
        <link href="../bootstrapSlider/css/bootstrap-slider.css" rel="stylesheet"/>

        <!-- Filestyle -->
        <script  src="../js/filestyle/src/bootstrap-filestyle.min.js"></script>

		<!-- utf8 -->
		<script  src="js/utf8.js"></script>

       

        <!-- Custom CSS -->
        <?php include "theme-switcher.php"?>
        <link href="../css/bulkDeviceLoad.css" rel="stylesheet">
        <link href="../css/d3tree.css" rel="stylesheet">


		<script>
		 var loggedRole = "<?php echo $_SESSION['loggedRole']; ?>";
		 var loggedUser = "<?php echo $_SESSION['loggedUsername']; ?>";
         var admin = "<?php echo $_SESSION['loggedRole']; ?>";
         var organization = "<?php echo $_SESSION['organization']; ?>";
                 var kbUrl = "<?php echo $_SESSION['kbUrl']; ?>";
                 var gpsCentreLatLng = "<?php echo $_SESSION['gpsCentreLatLng']; ?>";
                 var zoomLevel = "<?php echo $_SESSION['zoomLevel']; ?>";    
		 var titolo_default = "<?php echo $default_title; ?>";	
		 var access_denied = "<?php echo $access_denied; ?>";
		 var nascondi= "<?php echo $hide_menu; ?>";
		 var sessionEndTime = "<?php echo $_SESSION['sessionEndTime']; ?>";
         var sessionToken = "<?php  if (isset($_SESSION['refreshToken'])) echo $_SESSION['refreshToken']; else echo ""; ?>";
		 var mypage = location.pathname.split("/").slice(-1)[0];
         var functionality = [];
/*
          $.ajax({url: "../api/functionality.php",
			 data: {action: 'get_functionality', page : mypage},
			 type: "GET",
			 async: false,
			 dataType: 'json',
			 success: function (mydata)
			 {
			   // console.log(JSON.stringify(mydata));
			   if (mydata["status"]=='ok')
				 functionality = mydata["content"];
			   else
				  console.log("Error from the DB" + mydata["msg"]);		   
			 },
			 error: function (mydata)
			 {
			   console.log(JSON.stringify(mydata));
			 }
		 });
		 */
		</script>

        <!-- Custom scripts -->
		<!--
		<script  src="js/devices.js"></script>	-->
		<script  src="js/devicesManagement.js"></script>
		<script  src="js/common.js"></script>
		<script  src="js/devicesEditManagement.js"></script>
        <script  src="../js/dashboard_mng.js"></script>


		<!-- leaflet scripts -->

		<script  src="../js/leaflet.js"></script>
		<script  src="../js/leaflet.draw.js"></script>
		<script  src="../js/jquery.fancytree-all.min.js"></script>

        <style>

            .labelinput{
                padding: 10px;
                background: red; 
                display: table;
                color: #fff;
                 }



            input[type="file"] {
                display: none;
            }

            /* The Modal (background) */
            .progress-modal {
                display: none; /* Hidden by default */
                position: fixed; /* Stay in place */
                z-index: 1; /* Sit on top */
                padding-top: 100px; /* Location of the box */
                left: 0;
                top: 0;
                width: 100%; /* Full width */
                height: 100%; /* Full height */
                overflow: auto; /* Enable scroll if needed */
                background-color: rgb(0,0,0); /* Fallback color */
                background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
            }

            /* Modal Content */
            .progress-modal-content {
                position: relative;
                background-color: #fefefe;
                margin: auto;
                padding: 0;
                border: 1px solid #888;
                width: 80%;
                box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2),0 6px 20px 0 rgba(0,0,0,0.19);
                -webkit-animation-name: animatetop;
                -webkit-animation-duration: 0.4s;
                animation-name: animatetop;
                animation-duration: 0.4s
            }

            /* Add Animation */
            @-webkit-keyframes animatetop {
                from {top:-300px; opacity:0} 
                to {top:0; opacity:1}
            }

            @keyframes animatetop {
                from {top:-300px; opacity:0}
                to {top:0; opacity:1}
            }

            /* The Close Button */
            .close {
                color: white;
                float: right;
                font-size: 28px;
                font-weight: bold;
            }

            .close:hover,
            .close:focus {
                color: #000;
                text-decoration: none;
                cursor: pointer;
            }

            .progress-modal-header {
                padding: 2px 10px;
                background-color:#00A2D3;
                color: white;
            }

            .progress-modal-body {padding: 2px 16px;}

            .progress-modal-footer {
                padding: 2px 4px;
                background-color: #00A2D3;
                color: white;
            }


            .loader {
              border: 8px solid #f3f3f3;
              border-radius: 50%;
              border-top: 8px solid #3498db;
              width: 60px;
              height: 60px;
              -webkit-animation: spin 2s linear infinite; /* Safari */
              animation: spin 2s linear infinite;
            }

            /* Safari */
            @-webkit-keyframes spin {
              0% { -webkit-transform: rotate(0deg); }
              100% { -webkit-transform: rotate(360deg); }
            }

            @keyframes spin {
              0% { transform: rotate(0deg); }
              100% { transform: rotate(360deg); }
            }


        </style>

	</head>
    <body class="guiPageBody IOTdevices">
		<div class="container-fluid">
           <?php include "sessionExpiringPopup.php" ?> 

             <div class="row mainRow"> 
                <div class="col-xs-12 col-md-10" id="mainCnt">
                    <div class="row hidden-md hidden-lg">
                        <div id="mobHeaderClaimCnt" class="col-xs-12 hidden-md hidden-lg centerWithFlex">
                            Snap4City
                        </div>
                    </div>
                    <div class="row" id="title_row">
                        <div class="col-xs-10 col-md-12 centerWithFlex" id="headerTitleCnt">IoT Directory: Devices</div>
                        <div class="col-xs-2 hidden-md hidden-lg centerWithFlex" id="headerMenuCnt"></div> 
                    </div>


					<div class="row">
                        <div class="col-xs-12" id="mainContentCntIot">
							<div id="synthesis" class="row hidden-xs hidden-sm mainContentRow">
            	                <div id="hintEng" class="simple-modal row">
                	                In this page it's possible to launch a scan on every external Context Broker of the "ngsi w/ Multiservice" kind, in order to find new
                    	            Devices and add them to the IOT Directory.<BR> <BR>
									Note: In the "Multi-tenancy" and/or "Service Path" capable Context Brokers the scan will take place in the default Tenant and then only 
									in known Service/Tenants (those already registered in the Context Broker info, in the IOT Directory) and only in known Service Paths 
									(in the root directory and those in which you already have a device added in the IOT Directory or those registered in the Device Models).<BR> <BR>
									Devices that are already added to the IOT Directory are marked in white, the new ones, that are not yet in the IOT Directory, are marked in green.

									<div class="button pull-right">
                                    	<button type="text" id="startDiscoveryButton" name="startDiscoveryButton" class="btn btn-info" disabled>Start Discovery</button>
	                                </div>
    	                        </div>
							</div>
							<div id="info" class="row mainContentRow">
                            	<div id="app">
	                                <svg width="1920" height="1080" overflow=auto white-space=nowrap></svg>
    	                            <script src="https://d3js.org/d3.v5.min.js"></script>
        	                    </div>
							</div>
                        </div>
                    </div>

                    <!--div class="col-xs-12" id="deviceTree">
                        Press the "Start Discovery" button to obtain the device tree.
                    </div-->
                    <div class="col-xs-12" id="statusLabel">
                        Please wait...
                    </div>
                    <!--div class="row">
                        <div class="col-xs-12" id="colorsHint">
                            <div id="colorsHintIta" class="row">
                                I devices già presenti nella IOT Directory sono segnati in bianco, quelli nuovi non ancora aggiunti alla IOT Directory
                                sono segnati in verde.
                            </div>
                            <div id="colorsHintEng" class="row">
                                Devices that are already added to the IOT Directory are marked in white, the new ones, that are not
                                yet in the IOT Directory, are marked in green.
                            </div>
                        </div>
                    </div-->

                    <div class="modal fade" id="editDeviceModal" tabindex="-1" role="dialog" aria-hidden="true">
                                <div class="modal-dialog  modal-lg" role="document">
                                  <div class="modal-content">
                                    <div id="editDeviceModalLabel" class="modalHeader centerWithFlex">
                                      Update Device
                                    </div>

                                    <div id="editDeviceModalBody">

                                         <ul id="editDeviceModalTabs" class="nav nav-tabs nav-justified">
                    						<li  class="active"><a data-toggle="tab" href="#editIOTBrokerTabDevice">IoT Broker</a></li>
                                             <li><a data-toggle="tab" href="#editInfoTabDevice">Info</a></li>
                                            <li><a data-toggle="tab" href="#editGeoPositionTabDevice">Position</a></li>
                    			<li><a data-toggle="tab" href="#editStaticTabModel">Static Attributes</a></li>
                                            <li><a data-toggle="tab" href="#editSchemaTabDevice">Values</a></li>

                                        </ul>

                                        <div class="tab-content">
                                           <!-- IOT Broker tab -->
                                            <div id="editIOTBrokerTabDevice" class="tab-pane fade in active">

                    							<div class="row">

                    							   <div class="col-xs-12 col-md-6 modalCell">
                                                        <div class="modalFieldCnt">
                                                            <input type="text" class="modalInputTxt" name="deviceCB" id="deviceCB" required>
                                                        </div>
                                                        <div class="modalFieldLabelCnt">ContextBroker</div>
                    									<div id="selectContextBrokerMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                                    </div>

                                                     <div class="col-xs-12 col-md-6 modalCell">
                                                        <div class="modalFieldCnt">
                                                            <select id="selectKindDeviceM" name="selectKindDeviceM" class="modalInputTxt">
                                                                                                            <option value="sensor">sensor</option>
                                                                                                            <option value="actuator">actuator</option>
                                                                                                    </select>
                                                        </div>
                                                        <div class="modalFieldLabelCnt">Kind</div>
                    									<div id="selectKindDeviceMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                                    </div>

                    							</div>

                    							<div class="row">

                    							  <div class="col-xs-12 col-md-6 modalCell">
                                                        <div class="modalFieldCnt">
                                                            <select id="selectProtocolDeviceM" name="selectProtocolDeviceM" class="modalInputTxt" required>
                    											<option value="ngsi w/MultiService">ngsi w/MultiService</option>
                    										</select>
                                                        </div>
                                                        <div class="modalFieldLabelCnt">Protocol</div>
                    									<div id="selectProtocolDeviceMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                                    </div>
                                                    <div class="col-xs-12 col-md-6 modalCell">
                                                        <div class="modalFieldCnt">
                                                            <select id="selectFormatDeviceM" name="selectFormatDeviceM" class="modalInputTxt">
                    											<option value="csv">csv</option>
                    											<option value="json">json</option>
                    											<option value="xml">xml</option>
                    										</select>
                                                        </div>
                                                        <div class="modalFieldLabelCnt">Format</div>
                    									<div id="selectFormatDeviceMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                                    </div>

                    							</div>
                    				<!-- Start MultiService + ServicePath Section -->
                    				<div class = "row" id = "editMultiServiceAndServicePath">
                    					<div class="col-xs-12 col-md-6 modalCell">
                                            	            <div class="modalFieldCnt">
                                                    	        <input type="text" class="modalInputTxt" name="deviceService" id="deviceService" required>
                    	                                    </div>
                            	                            <div id="editSelectServiceLabel" class="modalFieldLabelCnt">Service/Tenant</div>
                    					    <div id="editSelectServiceMsg" class="modalFieldMsgCnt">&nbsp;</div>
                    	                                </div>

                    					<div class="col-xs-12 col-md-6 modalCell">
                    						<div class="modalFieldCnt">
                                    		                        <input type="text" class="modalInputTxt" name="editInputServicePathDevice" id="editInputServicePathDevice" required>
                                                    		</div>
                    		                                <div id="editInputServicePathLabel" class="modalFieldLabelCnt">ServicePath</div>
                    						<div id="editInputServicePathMsg" class="modalFieldMsgCnt">&nbsp;</div>
                            	                        </div>
                    				</div>
                    				<!-- End MultiService + ServicePath Section -->
                                            </div>
                                             <!-- Info tab -->
                                            <div id="editInfoTabDevice" class="tab-pane fade ">


                                                <div class="row">

                                                    <div class="col-xs-12 col-md-6 modalCell">
                                                        <div class="modalFieldCnt">
                                                            <input type="text" class="modalInputTxt" name="inputNameDeviceM" id="inputNameDeviceM" onkeyup="checkStrangeCharacters(this)" required>
                                                            <input type="text" class="modalInputTxt" name="inputOrganizationDeviceM" id="inputOrganizationDeviceM" style="display:none">
                                                        </div>
                                                        <div class="modalFieldLabelCnt">Name</div>
                    									<div id="inputNameDeviceMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                                    </div>

                    								 <div class="col-xs-12 col-md-6 modalCell">
                                                        <div class="modalFieldCnt">
                                                        <input type="text" class="modalInputTxt" name="inputModelDeviceM" id="inputModelDeviceM">
                                                        </div>
                                                        <div class="modalFieldLabelCnt">Model</div>
                    									<div id="inputModelDeviceMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                                    </div>

                    							</div>

                    							<div class="row">


                    								<div class="col-xs-12 col-md-6 modalCell">
                                                        <div class="modalFieldCnt">
                                                            <input type="text" class="modalInputTxt" name="inputTypeDeviceM" id="inputTypeDeviceM">
                                                        </div>
                                                        <div class="modalFieldLabelCnt">Device Type</div>
                    									<div id="inputTypeDeviceMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                                    </div>

                    								<div class="col-xs-12 col-md-6 modalCell">
                                                        <div class="modalFieldCnt">
                                                            <input type="text" class="modalInputTxt" name="inputMacDeviceM" id="inputMacDeviceM">
                                                        </div>
                                                        <div class="modalFieldLabelCnt">Mac Address</div>
                    									<div id="inputMacDeviceMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                                    </div>
                    								</div>
                    								<div class="row">
                    								<div class="col-xs-12 col-md-6 modalCell">
                                                        <div class="modalFieldCnt">
                                                            <select name="selectEdgeGatewayTypeM" id="selectEdgeGatewayTypeM" class="modalInputTxt">
                    											<option value=""></option>
                    											<?php
                                                                $query = "SELECT name FROM edgegatewaytype";
                                                                $result = mysqli_query($link, $query);

                                                                if($result)
                                                                {
                                                                   while($row = $result->fetch_assoc())
                                                                   {
                                                                     $name=$row["name"];
                    												 echo "<option value=\"$name\">$name</option>";
                                                                   }

                                                                }
                                                                else
                                                                {
                                                                    $name="ERROR";
                                                                    echo "<option value=\"$name\">$name</option>";
                                                                }
                                                            ?>
                    										</select>
                                                        </div>
                                                        <div class="modalFieldLabelCnt">Edge-Gateway Type</div>
                    									<div id="selectEdgeGatewayTypeMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                                    </div>
                    								<div class="col-xs-12 col-md-6 modalCell">
                                                        <div class="modalFieldCnt">
                                                            <input type="text" class="modalInputTxt" name="inputEdgeGatewayUriM" id="inputEdgeGatewayUriM">
                                                        </div>
                                                        <div class="modalFieldLabelCnt">Edge-Gateway URI</div>
                    									<div id="inputEdgeGatewayUriMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                                    </div>

                    							</div>

                    							<div class="row">

                    								<div class="col-xs-12 col-md-6 modalCell">
                                                        <div class="modalFieldCnt">
                                                            <input type="text" class="modalInputTxt" name="inputProducerDeviceM" id="inputProducerDeviceM">
                                                        </div>
                                                        <div class="modalFieldLabelCnt">Producer</div>
                    									<div id="inputProducerDeviceMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                                    </div>


                    	      						 <div class="col-xs-12 col-md-6 modalCell">
                                                        <div class="modalFieldCnt">
                    									<!--
                                                            <select id="selectVisibilityDeviceM" name="selectVisibilityDeviceM" class="modalInputTxt">
                    											<option></option>
                    										</select>
                    										-->
                    										<input id="selectVisibilityDeviceM" name="selectVisibilityDeviceM" class="modalInputTxt" readonly>
                                                        </div>
                                                        <div class="modalFieldLabelCnt">Ownership</div>
                    									<div id="selectVisibilityDeviceMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                                    </div>

                    							</div>

                    							<div class="row">

                    								<div class="col-xs-12 col-md-6 modalCell">
                    										<div class="modalFieldCnt" >
                    												<div class="input-group unity-input"> <input type="text" class="modalInputTxt" name="inputFrequencyDeviceM" id="inputFrequencyDeviceM"> <span class="input-group-addon" id="basic-addon2">sec</span></div>
                    										</div>
                    											<div class="modalFieldLabelCnt">Frequency</div>
                    											<div id="inputFrequencyDeviceMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                                    </div>

                    								  <div class="col-xs-12 col-md-6 modalCell">
                                                        <div class="modalFieldCnt">
                                                            <input type="text" class="modalInputTxt" name="inputUriDeviceM" id="inputUriDeviceM" readonly>
                                                        </div>
                                                        <div class="modalFieldLabelCnt">Service URI</div>
                    									<div id="inputUriDeviceMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                                    </div>

                                                </div>

                                                <!--Fatima4-->
                                                <div class="row">
                                                <div class="modalFieldCnt">
                                                        <button type="text" id="editDeviceGenerateKeyBtn" class="btn confirmBtn internalLink" onclick="editGenerateKeysCLicked()">Generate Keys</button>
                                                        </div>
                                                </div>

                    							<div class="row">

                    									<div class="col-xs-12 col-md-6 modalCell">
                    										<div class="modalFieldCnt">
                    											<input type="text" class="modalInputTxt" name="KeyOneDeviceUserM" id="KeyOneDeviceUserM">
                    										</div>
                    										<div class="modalFieldLabelCnt">KEY 1</div>
                    										<div id="KeyOneDeviceUserMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                    									</div>
                    									<div class="col-xs-12 col-md-6 modalCell">
                    										<div class="modalFieldCnt">
                    											<input type="text" class="modalInputTxt" name="KeyTwoDeviceUserM" id="KeyTwoDeviceUserM">
                    										</div>
                    										<div class="modalFieldLabelCnt">KEY 2</div>
                    										<div id="KeyTwoDeviceUserMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                    									</div>
                    								</div>

                    								<div id="sigFoxDeviceUserMMsg" class="modalFieldMsgCnt">&nbsp;</div>

                                            </div>



                                            <!-- Geo-Position tab -->
                                            <div id="editGeoPositionTabDevice" class="tab-pane fade">
                                                <div class="row">
                    								<div class="col-xs-12 col-md-6 modalCell">
                                                        <div class="modalFieldCnt">
                                                            <input type="text" class="modalInputTxt" name="inputLatitudeDeviceM" id="inputLatitudeDeviceM">
                                                        </div>
                                                        <div class="modalFieldLabelCnt">Latitude</div>
                    									<div id="inputLatitudeDeviceMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                                    </div>
                                                    <div class="col-xs-12 col-md-6 modalCell">
                                                        <div class="modalFieldCnt">
                                                            <input type="text" class="modalInputTxt" name="inputLongitudeDeviceM" id="inputLongitudeDeviceM">
                                                        </div>
                                                        <div class="modalFieldLabelCnt">Longitude</div>
                    									<div id="inputLongitudeDeviceMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                                    </div>
                                                </div>

                    								<div class="form-row iot-directory-form-row">
                    									<link rel="stylesheet" href="../css/leaflet.css" />
                    									<link rel="stylesheet" href="../css/leaflet.draw.css" />
                    									<div id="editLatLong" style="width: 100%; height: 400px">
                    								</div>
                    							</div>


                                            </div>

                                            <!-- Attribute tab -->
                                            <div id="editSchemaTabDevice" class="tab-pane fade">

                    							<div id="editlistAttributes"></div>
                                                                            <div id="addlistAttributesM"></div>
                                                                            <div id="deletedAttributes" style="display:none"></div>
                    							<div class="pull-left"><button id="addAttrMBtn" class="btn btn-primary">Add Value</button></div>
                    							<div id="editlistAttributesMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                            </div>

                                           <!-- Static Attributes tab -->
                                            <div id="editStaticTabModel" class="tab-pane fade">
                                                    <div class="row">
                                                            <div class="col-xs-12 col-md-6 modalCell">
                                                                    <div class="modalFieldCnt">
                                                                            <select id="selectSubnatureM" name="selectSubnatureM" class="modalInputTxt">
                                                                                    <option></option>
                                                                            </select>
                                                                    </div>
                                                                    <div class="modalFieldLabelCnt">Subnature</div>
                                                            </div>
                                                    </div>
                    				<div class="row">
                                                            <div id="editlistStaticAttributes"></div>
                                                    </div>
                                                    <div class="row">
                                                            <div class="pull-left"><button type="text" id="addNewStaticBtnM" class="btn confirmBtn" style="display: none;">Add Attribute</button></div>
                                                    </div>
                                            </div>

                                        </div>

                                    </div>

                                        <div class="modal fade" id="editDeviceOkModal" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                              <div class="modal-content">
                                                <div class="modalHeader centerWithFlex">
                                                  Success device registration
                                                </div>
                                                <div id="editDeviceModalBody">
                                                    <div class="row">
                                                        <div class="col-xs-12 modalCell">
                                                            <div id="editDeviceOkModalInnerDiv1">

                                                            </div>
                                                            <div class="modalDelObjName col-xs-12 centerWithFlex" id="editDeviceOkModalInnerDiv2"><i class="fa fa-check" style="font-size:36px"></i></div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                  <button type="button" id="editDeviceModalCancelBtn" class="btn btn-secondary" data-dismiss="modal">DONE</button>
                                                </div>
                                              </div>
                                            </div>
                                        </div>
                                        <!-- fail -->
                                        <div class="modal fade" id="editDeviceKoModal" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                              <div class="modal-content">
                                                <div class="modalHeader centerWithFlex">
                                                  Add new device
                                                </div>
                                                <input type="hidden" id="deviceNameToDelete" />
                                                <div id="deleteDeviceModalBody">
                                                    <div class="row">
                                                        <div class="col-xs-12 modalCell">
                                                            <div id="editDeviceKoModalInnerDiv1" class="modalDelMsg col-xs-12 centerWithFlex">

                                                            </div>
                                                            <div class="modalDelObjName col-xs-12 centerWithFlex" id="editDeviceKoModalInnerDiv2"><i class="fa fa-frown-o" style="font-size:36px"></i></div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                  <button type="button" id="editDeviceKoCancelBtn" class="btn cancelBtn" data-dismiss="modal">Cancel</button>
                                                </div>
                                              </div>
                                            </div>
                                        </div>


                    				<div id="editDeviceModalFooter" class="modal-footer">
                                      <button type="button" id="editDeviceCancelBtn" class="btn cancelBtn" data-dismiss="modal">Cancel</button>
                                      <button type="button" id="editDeviceConfirmBtn" class="btn confirmBtn internalLink" >Confirm</button>
                                      <button type="button" id="editDeviceOkBtn" class="btn cancelBtn" data-dismiss="modal" style="display:none;" >Ok</button>
                                    </div>

                    				<!-- </form>-->

                                  </div>
                                </div>
                            </div>

		<script  src="js/deviceDiscovery.js"></script>
    </body>
</html>		
