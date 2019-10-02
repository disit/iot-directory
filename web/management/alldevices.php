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
	$default_title = "IoT Directory: Devices";
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
 
 
require '../sso/autoload.php';
use Jumbojett\OpenIDConnectClient;


 if (isset($_SESSION['refreshToken'])) {
   $oidc = new OpenIDConnectClient($keycloakHostUri, $clientId, $clientSecret);
   $oidc->providerConfigParam(array('token_endpoint' => $keycloakHostUri.'/auth/realms/master/protocol/openid-connect/token'));
   $tkn = $oidc->refreshToken($_SESSION['refreshToken']);
   $accessToken = $tkn->access_token;
   $_SESSION['refreshToken'] = $tkn->refresh_token;
 }
  
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

       <!-- Font awesome icons -->
        <link rel="stylesheet" href="../js/fontAwesome/css/font-awesome.min.css">

     <!--    <link href="https://fonts.googleapis.com/css?family=Cabin:400,500,600,700|Catamaran|Varela+Round" rel="stylesheet"> -->
        
        <!-- Custom CSS -->
        <link href="../css/dashboard.css" rel="stylesheet">
		<style> .btn-round { width: 30px; height:30px; border-radius: 50%; }
        #mainMenuCnt
		{
			background-color: rgba(51, 64, 69, 1);
			color: white;
			height: 100vh;
			<?php if ($hide_menu=="hide") echo "display:none"; //MM201218 ?>
		}
        
        </style>

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
             var bearerToken = "<?php  if (isset($_SESSION['accessToken'])) echo $_SESSION['accessToken']; else echo ""; ?>";		 
             var mypage = location.pathname.split("/").slice(-1)[0];
             var functionality = [];

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
		 
		</script>
 
        <!-- Custom scripts -->
		
		<script  src="js/alldevices.js"></script>
		<script  src="js/devicesManagement.js"></script>
		<script  src="js/devicesEditManagement.js"></script>
		<script  src="js/fieldsManagement.js"></script>
        <script  src="../js/dashboard_mng.js"></script>
		
		
		<!-- leaflet scripts -->
		
		<script  src="../js/leaflet.js"></script>
		<script  src="../js/leaflet.draw.js"></script>
		<script  src="../js/jquery.fancytree-all.min.js"></script>
		
		
		
        
        <!--<link href="https://fonts.googleapis.com/css?family=Cabin:400,500,600,700|Catamaran|Varela+Round" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css?family=Montserrat" rel="stylesheet">-->
	</head>
    <body class="guiPageBody">
		<div class="container-fluid">
           <?php include "sessionExpiringPopup.php" ?> 
            
             <div class="row mainRow"> 
                <?php include "mainMenu.php" ?> 
                 <div 
                     <?php //MM201218
				if (($hide_menu=="hide")) {?>
				class="col-xs-12 col-md-12" 
				<?php }else {?>
				class="col-xs-12 col-md-10" 
				<?php } //MM201218 FINE?>
                     id="mainCnt">
                    <div class="row hidden-md hidden-lg">
                        <div id="mobHeaderClaimCnt" class="col-xs-12 hidden-md hidden-lg centerWithFlex">
                            Snap4City
                        </div>
                    </div>
					<?php //MM201218
					if (($hide_menu!="hide")) {?>
                    <div class="row" id="title_row">
                        <div class="col-xs-10 col-md-12 centerWithFlex" id="headerTitleCnt">IoT Directory: Devices</div>
                        <div class="col-xs-2 hidden-md hidden-lg centerWithFlex" id="headerMenuCnt"><!--?php include "mobMainMenu.php" ?--></div> 
                    </div>
					<?php } //MM201218 FINE ?>
							
                    <div class="row">
                        <div class="col-xs-12" id="mainContentCnt">
						
						
				             <div id="synthesis" class="row hidden-xs hidden-sm mainContentRow">
                             <!--   <div  class="col-xs-12 mainContentRowDesc"></div> -->
                                <div id="dashboardTotNumberCnt" class="col-md-3 mainContentCellCnt">
                                    <div class="col-md-12 centerWithFlex pageSingleDataCnt">
                                        <?php
                                            $query = "SELECT count(*) AS qt FROM devices";
                                            $result = mysqli_query($link, $query);
                                            
                                            if($result)
                                            {
                                               $row = $result->fetch_assoc();
												echo $row['qt'] . ' devices';
                                            }
                                            else
                                            {
												echo '-' . ' devices';
                                            }
                                        ?>
										
                                    </div>
                                 
                                </div>
                                <div id="dashboardTotActiveCnt" class="col-md-3 mainContentCellCnt">
                                    <div class="col-md-12 centerWithFlex  pageSingleDataCnt">
                                        <?php //MM
                                            $query = "SELECT count(*) AS qt FROM devices WHERE  mandatoryproperties = true and mandatoryvalues = true ";
                                            $result = mysqli_query($link, $query);
                                            
                                            if($result)
                                            {
                                               $row = $result->fetch_assoc();
                                               echo $row['qt'] . ' active';
                                            }
                                            else
                                            {
                                                echo '-' . ' active';
                                            }
                                        ?>
                                    </div>
                                  
                                </div>
                                <div id="dashboardTotPermCnt" class="col-md-3 mainContentCellCnt">
                                    <div class="col-md-12 centerWithFlex  pageSingleDataCnt">
                                        <?php //MM
                                            $query = "SELECT count(*) AS qt FROM devices where visibility='public'";
                                            $result = mysqli_query($link, $query);
                                            
                                            if($result)
                                            {
                                               $row = $result->fetch_assoc();
                                               echo $row['qt'] . ' public';
                                            }
                                            else
                                            {
                                                echo '-' . ' public';
                                            }
                                        ?>
                                    </div>
                                   
                                </div>
								   <div id="dashboardTotPrivateCnt" class="col-md-3 mainContentCellCnt">
                                    <div class="col-md-12 centerWithFlex pageSingleDataCnt">
                                        <?php  
                                            $query = "SELECT count(*) AS qt FROM devices where visibility='private'";
                                            $result = mysqli_query($link, $query);
                                            
                                            if($result)
                                            {
                                               $row = $result->fetch_assoc();
                                               echo $row['qt'] . ' private';
                                            }
                                            else
                                            {
                                                echo '-' . ' private';
                                            }
                                        ?>
                                    </div>
                                   
                                </div>

                            </div>
							
							
										<div id="managerBoard" class="row mainContentRow">
											<!-- <div class="col-xs-12 mainContentRowDesc">My Devices Menu </div>-->
											<div class="col-xs-12 mainContentCellCnt">
											
											<button type="text" id="myDevice" name="myDevice"class="btn btn-primary">My Devices</button>
											<button type="text" id="delegatedDevice" name="delegatedDevice" class="btn btn-primary">Delegated Devices</button>			
											<button type="button" id="addMyNewDevice" class="btn btn-primary">Add New Device</button>
											</div>
										</div>
		
							
			
							<div id="addMyNewDeviceRow" class="row mainContentRow">
                                <div class="col-xs-12 mainContentRowDesc">Add My New Device</div>
                                <div class="col-xs-12 col-md-6 mainContentCellCnt">
								<div id="myDeviceForm" class="row mainContentRow">
								<div class="row">
									<div class="col-xs-12 col-md-6 modalCell">
										<div class="modalFieldCnt">
											<input type="text" class="modalInputTxt" name="inputNameDeviceUser" id="inputNameDeviceUser" required> 
										</div>
										<div class="modalFieldLabelCnt">Name</div>
										<div id="inputNameDeviceUserMsg" class="modalFieldMsgCnt">&nbsp;</div>
									</div>
									
									<div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <select id="selectModel" name="selectModel" class="modalInputTxt">
										<?php
                                            $query = "SELECT name, kgenerator FROM model";
                                            $result = mysqli_query($link, $query);

                                            if($result)
                                            {
                                               while($row = $result->fetch_assoc())
                                               { 
                                                 $name=$row["name"];
												 $kgen = $row["kgenerator"];
                                                 //echo "<option data_key=\"$kgen\" value=\"$name\">$name</option>";
												 echo "<option data_key=\"$kgen\" value=\"$name\">$name</option>";
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
                                    <div class="modalFieldLabelCnt">Model</div>
								<div id="selectModelMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
								</div>	
									<!--
									<div class="col-xs-12 col-md-6 modalCell">
										<div class="modalFieldCnt">
											<input type="text" class="modalInputTxt" name="inputTypeDeviceUser" id="inputTypeDeviceUser"> 
										</div>
										<div class="modalFieldLabelCnt">Type</div>
										<div id="inputTypeDeviceUserMsg" class="modalFieldMsgCnt">&nbsp;</div>
									</div>
									  <div class="col-xs-12 col-md-6 modalCell">
										 <div class="modalFieldCnt">
											<select id="selectKindDeviceUser" name="selectKindDeviceUser" class="modalInputTxt">
												<option value="sensor">sensor</option>
												<option value="actuator">actuator</option>
											</select>
										</div>
										<div class="modalFieldLabelCnt">Kind</div>
										<div id="selectKindDeviceUserMsg" class="modalFieldMsgCnt">&nbsp;</div>
									</div>
									<div class="col-xs-12 col-md-6 modalCell">
										<div class="modalFieldCnt">
											<input type="text" class="modalInputTxt" value="Private" id="inputDeviceOwnershipUser" name="inputDeviceOwnershipUser"readonly> 
										</div>
										<div class="modalFieldLabelCnt">Ownership</div>
										<div id="ownership" class="modalFieldMsgCnt">&nbsp;</div>
									</div> 
									-->
									<div class="row">
									<div class="col-xs-12 col-md-6 modalCell">
										<div class="modalFieldCnt">
											<input type="text" class="modalInputTxt" name="inputLatitudeDeviceUser" id="inputLatitudeDeviceUser"> 
										</div>
										<div class="modalFieldLabelCnt">Latitude</div>
										<div id="inputLatitudeDeviceUserMsg" class="modalFieldMsgCnt">&nbsp;</div>
									</div>
									<div class="col-xs-12 col-md-6 modalCell">
										<div class="modalFieldCnt">
											<input type="text" class="modalInputTxt" name="inputLongitudeDeviceUser" id="inputLongitudeDeviceUser"> 
										</div>
										<div class="modalFieldLabelCnt">Longitude</div>
										<div id="inputLongitudeDeviceUserMsg" class="modalFieldMsgCnt">&nbsp;</div>
									</div>
								</div>
								</div>
								<!--
								<div class="row mainContentRow">
									 <div id="addSchemaTabDevice">					
										<div id="addlistAttributesUser"></div>
										<div class="pull-left"><button id="addAttrBtnUser" class="btn btn-primary">Add Value</button></div>
										<div id="addlistAttributesUserMsg" class="modalFieldMsgCnt">&nbsp;</div>
									</div>
									<div id="addMyDeviceModalFooter" class="modal-footer"> 
										<button type="button" id="addMyNewDeviceConfirmBtn" class="btn btn-primary">Submit Device</button>  
									</div> 	 
                                </div>
								-->
								<div id="addMyDeviceModalFooter" class="modal-footer"> 
										<button type="button" id="addMyNewDeviceConfirmBtn" class="btn btn-primary">Submit Device</button>  
								</div> 	
								
								</div>
								
								<div class="col-xs-12 col-md-6 mainContentCellCnt">
				
									  <div id="addMapUser">
										<div>
											<h4 style="text-align: center; color:blue;"> Select Latitude/Longitude on Map </h4>
										</div>
										<div class="form-row iot-directory-form-row">
												<link rel="stylesheet" href="../css/leaflet.css" />
												<link rel="stylesheet" href="../css/leaflet.draw.css" />
												<div id="addDeviceMapModalBodyUser" style="width: 100%; height: 400px" class="modal-body modalBody">
												</div>
										</div> 
									  </div>
									 
                                </div>
		
								
                            </div>
							
						
                            <div id="displayAllDeviceRow" class="row mainContentRow">
                               <!-- <div class="col-xs-12 mainContentRowDesc"></div> -->
                                <div class="col-xs-12 mainContentCellCnt">
									<div class="row" style= "background-color: rgb(241, 245, 244);">
										<div class="col-xs-12 col-md-6 modalCell" style= "background-color: rgb(241, 245, 244);">
										<div id="displayDevicesMap" class="pull-right"><button type="button" class="btn btn-primary btn-round"><span class="glyphicon glyphicon-globe" style="font-size:36px; color: #0000ff"></span></button></div>
										</div>										
								    </div>
									<div>
									<table id="devicesTable" class="table table-bordered table-striped" cellspacing="0" width="100%">
										<thead>
										  <tr>
											<th></th>	
											<th data-cellTitle="name">IOT Device</th> 
											<th data-cellTitle="contextbroker">IOT Broker</th>
									  	    <th data-cellTitle="devicetype">Device Type</th> 
											<th data-cellTitle="model">Model</th>
											<th data-cellTitle="visibility">Ownership</th>
											<th data-cellTitle="organization">Organization</th>
											<th data-cellTitle="owner">Owner</th>
											<th data-cellTitle="status">Status</th>
											<th data-cellTitle="edit">Edit</th>
											<th data-cellTitle="delete">Delete</th>		
											<th data-cellTitle="location">Location</th>										
											</tr>
										</thead>
									</table>
									</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
		
		<div class="modal fade" id="successRegisterUserDeviceModal" tabindex="-1" role="dialog" aria-labelledby="successRegisterUserDeviceModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="successRegisterUserDeviceModalLabel">Successful Device Registration</h5>
                </div>
                <div class="modal-body">
					You have successfully registered your device - if you want to see more about configuration you can visit 
                </div>
                <div class="modal-footer">
                  <button type="button" id="successRegisterUserDeviceCancelBtn" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
			   </div>
              </div>
            </div>
        </div>
		
		
		
     
        <div class="modal fade" id="deleteDeviceModal" tabindex="-1" role="dialog" aria-labelledby="deleteDeviceModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="deleteDeviceModalLabel">Device deletion</h5>
                </div>
                <div class="modal-body">
					Do you want to confirm deletion of the following device?
                </div>
                  <div id="deleteDeviceModalInnerDiv1" class="modalBodyInnerDiv" style="display: none;"><h5>Device deletion in progress, please wait</h5></div>
                    <div id="deleteDeviceModalInnerDiv2" class="modalBodyInnerDiv" style="display: none;"><i class="fa fa-circle-o-notch fa-spin" style="font-size:36px"></i></div>
                <div class="modal-footer">
                    <button type="button" id="deleteDeviceOkBtn" class="btn btn-primary" data-dismiss="modal" style="display: none;">Ok</button>
                    <button type="button" id="deleteDeviceCancelBtn" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                  <button type="button" id="deleteDeviceConfirmBtn" class="btn btn-primary">Confirm</button>
                </div>
              </div>
            </div>
        </div>

          <!-- Adding a New device -->
        <div class="modal fade" id="addDeviceModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
              <div class="modal-content">
                <div class="modalHeader centerWithFlex">
                  Add new device
                </div>
        
		
				
					<div id="addDeviceModalBody" class="modal-body modalBody">
				
                
					<ul id="addDeviceModalTabs" class="nav nav-tabs nav-justified">
						<li class="active"><a data-toggle="tab" href="#addInfoTabDevice">Info</a></li>
                        <li><a data-toggle="tab" href="#addIOTBrokerTabDevice">IOT Broker</a></li>
                        <li><a data-toggle="tab" href="#addGeoPositionTabDevice">Position</a></li>
                        <li><a data-toggle="tab" href="#addSchemaTabDevice">Values</a></li>
						
                    </ul>
                    
					
                    <div class="tab-content">
                       
                        <!-- Info tab -->
                        <div id="addInfoTabDevice" class="tab-pane fade in active">
                            <div class="row">
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputNameDevice" id="inputNameDevice" required> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Name</div>
									<div id="inputNameDeviceMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
								 <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
										<select name="selectModelDevice" id="selectModelDevice" class="modalInputTxt">
										    <option></option>  
										    <option data_key="normal" value="custom">custom</option>    
											<?php
                                            $query = "SELECT name, kgenerator FROM model";
                                            $result = mysqli_query($link, $query);

                                            if($result)
                                            {
                                               while($row = $result->fetch_assoc())
                                               { 
                                                 $name=$row["name"];
												 $kgen = $row["kgenerator"];
                                                 //echo "<option data_key=\"$kgen\" value=\"$name\">$name</option>";
												 echo "<option data_key=\"$kgen\" value=\"$name\">$name</option>";
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
                                    <div class="modalFieldLabelCnt">Model</div>
									<div id="inputModelDeviceMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
								
								</div>
								<div class="row">
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputTypeDevice" id="inputTypeDevice"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Device Type</div>
									<div id="inputTypeDeviceMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>

                               <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputMacDevice" id="inputMacDevice"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Mac Address</div>
									<div id="inputMacDeviceMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
								</div>
								<div class="row">
								<div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <select name="selectEdgeGatewayType" id="selectinputEdgeGatewayType" class="modalInputTxt">
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
									<div id="inputEdgeGatewayTypeMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
								<div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputEdgeGatewayUri" id="inputEdgeGatewayUri"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Edge-Gateway URI</div>
									<div id="inputEdgeGatewayUriMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
                               </div>
							<div class="row">
								<div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputProducerDevice" id="inputProducerDevice"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Producer</div>
									<div id="inputProducerDeviceMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
								
								<div class="col-xs-12 col-md-6 modalCell">
									<div class="modalFieldCnt">
										<div class="input-group unity-input"> <input type="text" class="modalInputTxt" name="inputFrequencyDevice" id="inputFrequencyDevice" value="600"> <span class="input-group-addon" id="basic-addon2">sec</span></div>

									</div>
									<div class="modalFieldLabelCnt">Frequency</div>
									<div id="inputFrequencyDeviceMsg" class="modalFieldMsgCnt">&nbsp;</div>
	                           </div>
							   
							  </div> 
							  <div class="row">
								<div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <select id="selectVisibilityDevice" name="selectVisibilityDevice" class="modalInputTxt">
											<option value="private">Private</option>
											<!-- <option value="public">Public</option> -->
										</select>
                                    </div>
                                    <div class="modalFieldLabelCnt">Ownership</div>
									<div id="selectVisibilityDeviceMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
								<!--Fatima3 -->
                                    <div class="modalFieldCnt">
                                    <button type="text" id="addNewDeviceGenerateKeyBtn" class="btn confirmBtn internalLink" onclick="generateKeysCLicked()">Generate Keys</button>
                                    </div>
		                     </div>	
							 <div class="row">
									<div class="col-xs-12 col-md-6 modalCell">
										<div class="modalFieldCnt">
											<input type="text" class="modalInputTxt" name="KeyOneDeviceUser" id="KeyOneDeviceUser"> 
										</div>
										<div class="modalFieldLabelCnt">KEY 1</div>
										<div id="KeyOneDeviceUserMsg" class="modalFieldMsgCnt">&nbsp;</div>
									</div>
									<div class="col-xs-12 col-md-6 modalCell">
										<div class="modalFieldCnt">
											<input type="text" class="modalInputTxt" name="KeyTwoDeviceUser" id="KeyTwoDeviceUser"> 
										</div>
										<div class="modalFieldLabelCnt">KEY 2</div>
										<div id="KeyTwoDeviceUserMsg" class="modalFieldMsgCnt">&nbsp;</div>
									</div>
								</div>
								<div id="sigFoxDeviceUserMsg" class="modalFieldMsgCnt">&nbsp;</div>
                        </div>
                        
                        <!-- IOT Broker tab -->
                        <div id="addIOTBrokerTabDevice" class="tab-pane fade">
						
						
						
                            <div class="row">
							
							
							 <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <select id="selectContextBroker" name="selectContextBroker" class="modalInputTxt">
										<option></option>
                                       <?php
                                            $query = "SELECT name, protocol FROM contextbroker";
                                            $result = mysqli_query($link, $query);

                                            if($result)
                                            {
                                               while($row = $result->fetch_assoc())
                                               { 
                                                 $nameCB=$row["name"];
												 $protocol=$row["protocol"];
                                                 echo "<option my_data=\"$protocol\" value=\"$nameCB\">$nameCB</option>";
                                               }

                                            }
                                            else
                                            {
                                               
                                                $nameCB="ERROR";
                                                echo "<option value=\"$nameCB\">$nameCB</option>";
                                            }
                                        ?>
										</select>
                                    </div>
                                    <div class="modalFieldLabelCnt">ContextBroker</div>
								<div id="selectContextBrokerMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
                                <div class="col-xs-12 col-md-6 modalCell">
                                     
                                     <div class="modalFieldCnt">
                                        <select id="selectKindDevice" name="selectKindDevice" class="modalInputTxt">
											<option></option>
											<option value="sensor">sensor</option>
											<option value="actuator">actuator</option>
										</select>
                                    </div>
                                    <div class="modalFieldLabelCnt">Kind</div>
                                    <div id="selectKindDeviceMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>

                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <select id="selectProtocolDevice" name="selectProtocolDevice" class="modalInputTxt">
											<option></option>
											<option value="amqp">amqp</option>
											<option value="coap">coap</option>
											<option value="mqtt">mqtt</option>
											<option value="ngsi">ngsi</option>
										</select>
                                    </div>
                                    <div class="modalFieldLabelCnt">Protocol</div>
									<div id="selectProtocolDeviceMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <select id="selectFormatDevice" name="selectFormatDevice" class="modalInputTxt">
											<option></option>
											<option value="csv">csv</option>
											<option value="json">json</option>
											<option value="xml">xml</option>
										</select>
                                    </div>
                                    <div class="modalFieldLabelCnt">Format</div>
									<div id="selectFormatDeviceMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
								
						
                            </div>    
                        </div>
                        
                        <!-- Geo-Position tab -->
                           <div id="addGeoPositionTabDevice" class="tab-pane fade">
                            
							 <div class="row">
								<div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputLatitudeDevice" id="inputLatitudeDevice"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Latitude</div>
									<div id="inputLatitudeDeviceMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputLongitudeDevice" id="inputLongitudeDevice"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Longitude</div>
									<div id="inputLongitudeDeviceMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
	
                            </div>
							<div class="form-row iot-directory-form-row">
									<link rel="stylesheet" href="../css/leaflet.css" />
									<link rel="stylesheet" href="../css/leaflet.draw.css" />
									<div id="addLatLong" style="width: 100%; height: 400px" class="modal-body modalBody">
								</div>
							</div> 
							
                        </div>
						                        
                        <!-- Device Schema tab -->
                        <div id="addSchemaTabDevice" class="tab-pane fade">
					
														
										<div id="addlistAttributes"></div>
										<div class="pull-left"><button id="addAttrBtn" class="btn btn-primary">Add Value</button></div>
										<div id="addlistAttributesMsg" class="modalFieldMsgCnt">&nbsp;</div>
								

                        </div>
						
                        
						
						
                    </div>
					
					   
                    <div class="row" id="addDeviceLoadingMsg">
                        <div class="col-xs-12 centerWithFlex">Adding device, please wait</div>
                    </div>
                    <div class="row" id="addDeviceLoadingIcon">
                        <div class="col-xs-12 centerWithFlex"><i class="fa fa-circle-o-notch fa-spin" style="font-size:36px;"></i></div>
                    </div>
                    <div class="row" id="addDeviceOkMsg">
                        <div class="col-xs-12 centerWithFlex">Device added successfully</div>
                    </div>
                    <div class="row" id="addDeviceOkIcon">
                        <div class="col-xs-12 centerWithFlex"><i class="fa fa-thumbs-o-up" style="font-size:36px"></i></div>
                    </div>
                    <div class="row" id="addDeviceKoMsg">
					    <div class="col-xs-12 centerWithFlex"></div>
                        <div class="col-xs-12 centerWithFlex">Error adding device</div>
                    </div>
                    <div class="row" id="addDeviceKoIcon">
                        <div class="col-xs-12 centerWithFlex"><i class="fa fa-thumbs-o-down" style="font-size:36px"></i></div>
                    </div>
				</div> 	
		       
         		<div id="addDeviceModalFooter" class="modal-footer">
                  <button type="text" id="addNewDeviceCancelBtn" class="btn cancelBtn" data-dismiss="modal">Cancel</button>
                  <button type="text" id="addNewDeviceConfirmBtn" name="addNewDeviceConfirmBtn" class="btn confirmBtn internalLink">Confirm</button>	  
				</div>
				
              </div>
            </div>
        </div>

        <!-- Success  -->
        <div class="modal fade" id="addDeviceOkModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modalHeader centerWithFlex">
                  Add new device
                </div>
                <input type="hidden" id="deviceNameToDelete" />
                <div id="deleteDeviceModalBody" class="modal-body modalBody">
                    <div class="row">
                        <div class="col-xs-12 modalCell">
                            <div id="addDeviceOkModalInnerDiv1" class="modalDelMsg col-xs-12 centerWithFlex">
                                
                            </div>
                            <div class="modalDelObjName col-xs-12 centerWithFlex" id="addDeviceOkModalInnerDiv2"><i class="fa fa-check" style="font-size:36px"></i></div> 
                        </div>
                    </div>
                </div>
                <!--<div class="modal-footer">
                  
                </div>-->
              </div>
            </div>
        </div>
        
        <!-- fail -->
        <div class="modal fade" id="addDeviceKoModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modalHeader centerWithFlex">
                  Add new device
                </div>
                <input type="hidden" id="deviceNameToDelete" />
                <div id="deleteDeviceModalBody" class="modal-body modalBody">
                    <div class="row">
                        <div class="col-xs-12 modalCell">
                            <div id="addDeviceKoModalInnerDiv1" class="modalDelMsg col-xs-12 centerWithFlex">
                                
                            </div>
                            <div class="modalDelObjName col-xs-12 centerWithFlex" id="addDeviceKoModalInnerDiv2"><i class="fa fa-frown-o" style="font-size:36px"></i></div> 
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                  <button type="button" id="addDeviceKoBackBtn" class="btn cancelBtn">Go back to new user form</button>
                  <button type="button" id="addDeviceKoConfirmBtn" class="btn confirmBtn">Go back to users page</button>
                </div>
              </div>
            </div>
        </div>

        <!-- Update -->
        <div class="modal fade" id="editDeviceModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog  modal-lg" role="document">
              <div class="modal-content">
                <div id="editDeviceModalLabel" class="modalHeader centerWithFlex">
                  Update Device
                </div>
        
                <div id="editDeviceModalBody" class="modal-body modalBody">
                    
                     <ul id="editDeviceModalTabs" class="nav nav-tabs nav-justified">
						<li  class="active"><a data-toggle="tab" href="#editIOTBrokerTabDevice">IoT Broker</a></li>
                         <li><a data-toggle="tab" href="#editInfoTabDevice">Info</a></li>
                        <li><a data-toggle="tab" href="#editGeoPositionTabDevice">Position</a></li>
                        <li><a data-toggle="tab" href="#editSchemaTabDevice">Values</a></li>
						<li><a data-toggle="tab" href="#editStatusTabDevice">Status</a></li>
						
                    </ul>
                    
                    <div class="tab-content">
                       
                        
                        
                        <!-- IOT Broker tab -->
                        <div id="editIOTBrokerTabDevice" class="tab-pane fade in active">
                     
							<div class="row">
							
							   <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <select id="selectContextBrokerM" name="selectContextBrokerM" class="modalInputTxt">
										<!--?php
                                            $query = "SELECT name FROM contextbroker";
                                            $result = mysqli_query($link, $query);

                                            if($result)
                                            {
                                               while($row = $result->fetch_assoc())
                                               {
                                                 $nameCB=$row["name"];
                                                 echo "<option value=\"$nameCB\">$nameCB</option>";
                                               }

                                            }
                                            else
                                            {

                                                $nameCB="ERROR";
                                                echo "<option value=\"$nameCB\">$nameCB</option>";
                                            }
                                        ?-->
									</select>
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
                                        <select id="selectProtocolDeviceM" name="selectProtocolDeviceM" class="modalInputTxt">
											<option value="amqp">amqp</option>
											<option value="coap">coap</option>
											<option value="mqtt">mqtt</option>
											<option value="ngsi">ngsi</option>
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
				 
                        </div>
                        
                        <!-- Info tab -->
                        <div id="editInfoTabDevice" class="tab-pane fade">
						
						
                            <div class="row">
							
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputNameDeviceM" id="inputNameDeviceM" required> 
                                        <input type="text" class="modalInputTxt" name="inputOrganizationDeviceM" id="inputOrganizationDeviceM" style="display:none">
                                    </div>
                                    <div class="modalFieldLabelCnt">Name</div>
									<div id="inputNameDeviceMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
								
								 <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
										<!--
										<select name="selectModelDevice" id="selectModelDevice" class="modalInputTxt">
										    <option data_key="normal" value="custom">custom</option>    
										</select>
										-->
										
										<input id="selectModelDeviceM" name="selectModelDeviceM" class="modalInputTxt" readonly>	
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
									<div id="editLatLong" style="width: 100%; height: 400px" class="modal-body modalBody">
								</div>
							</div> 
							
							
                        </div>
                        
                        <!-- Attribute tab -->
                        <div id="editSchemaTabDevice" class="tab-pane fade">
                           
							<div id="editlistAttributes"></div>
							<div id="addlistAttributesM"></div>
							<div id="deletedAttributes" style="display:none"></div>
							<!-- <div class="pull-left"><i id="addAttrMBtn" class="fa fa-plus-square" style="font-size:36px; color: #ffcc00"></i></div> -->
							<div class="pull-left"><button id="addAttrMBtn" class="btn btn-primary">Add Value</button></div>
                        </div>
						
						<!-- Semantic Labeling tab -->
                        <div id="editStatusTabDevice" class="tab-pane fade">
                            <div class="row">
								<div class="col-xs-12 col-md-6 modalCell">
                                  <!--  <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputPropertiesDeviceM" id="inputPropertiesDeviceM"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Device Status</div> -->
									<div id="inputPropertiesDeviceMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
                                <div class="col-xs-12 col-md-6 modalCell">
                                   <!-- <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputAttributesDeviceM" id="inputAttributesDeviceM"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Attribute Status</div> -->
									<div id="inputAttributesDeviceMMsg" class="modalFieldMsgCnt">&nbsp;</div>
								</div>
                            </div>
							
                        </div>
						
						
                    </div>
					
					
		            
                </div>
				
				
				<div class="row" id="editDeviceLoadingMsg">
					<div class="col-xs-12 centerWithFlex">Updating device, please wait</div>
				</div>
				<div class="row" id="editDeviceLoadingIcon">
					<div class="col-xs-12 centerWithFlex"><i class="fa fa-circle-o-notch fa-spin" style="font-size:36px;"></i></div>
				</div>
				<div class="row" id="editDeviceOkMsg">
					<div class="col-xs-12 centerWithFlex">Device updated successfully</div>
				</div>
				<div class="row" id="editDeviceOkIcon">
					<div class="col-xs-12 centerWithFlex"><i class="fa fa-thumbs-o-up" style="font-size:36px"></i></div>
				</div>
				<div class="row" id="editDeviceKoMsg">
					<div class="col-xs-12 centerWithFlex">Error updating device</div>
					 <div id="editDeviceOkModalInnerDiv1" class="modalDelMsg col-xs-12 centerWithFlex">
                                
					</div>
				</div>
				<div class="row" id="editDeviceKoIcon">
					<div class="col-xs-12 centerWithFlex"><i class="fa fa-thumbs-o-down" style="font-size:36px"></i></div>
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
        
        <!--Success 
        <div class="modal fade" id="editDeviceOkModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modalHeader centerWithFlex">
                  Update device
                </div>
                <div class="modal-body modalBody">
                    <div class="row">
                        <div class="col-xs-12 modalCell">
                            <div id="editDeviceOkModalInnerDiv1" class="modalDelMsg col-xs-12 centerWithFlex">
                                
                            </div>
                            <div class="modalDelObjName col-xs-12 centerWithFlex" id="editDeviceOkModalInnerDiv2"><i class="fa fa-check" style="font-size:36px"></i></div> 
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                  
                </div>
              </div>
            </div>
        </div> -->
        
        <!-- Fail 
        <div class="modal fade" id="editDeviceKoModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modalHeader centerWithFlex">
                  Update device
                </div>
                <div id="deleteDeviceModalBody" class="modal-body modalBody">
                    <div class="row">
                        <div class="col-xs-12 modalCell">
                            <div id="editDeviceKoModalInnerDiv1" class="modalDelMsg col-xs-12 centerWithFlex">
                                
                            </div>
                            <div class="modalDelObjName col-xs-12 centerWithFlex" id="editDeviceKoModalInnerDiv2"><i class="fa fa-frown-o" style="font-size:36px"></i></div> 
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                  <button type="button" id="editDeviceKoBackBtn" class="btn cancelBtn">Go back to edit account form</button>
                  <button type="button" id="editDeviceKoConfirmBtn" class="btn confirmBtn">Go back to users page</button>
                </div>
              </div>
            </div>
        </div> -->
		

		
		
		
		    <div class="modal fade" id="addMapShow" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modalHeader centerWithFlex">
					Device Location on Map
                </div>
				<div class="form-row iot-directory-form-row">
				        <link rel="stylesheet" href="../css/leaflet.css" />
						<link rel="stylesheet" href="../css/leaflet.draw.css" />
						<div id="addDeviceMapModalBodyShow" style="width: 100%; height: 400px" class="modal-body modalBody">                  
					</div>
				</div> 
				<div class="modal-footer">
                  <button type="button" id="cancelMapBtn" class="btn cancelBtn"  data-dismiss="modal">Cancel</button>
                </div>
              </div>
            </div>
        </div>
    
	
	
		    <div class="modal fade" id="addMap1" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modalHeader centerWithFlex">
					Search Device Location on Map
                </div>
				<div class="form-row iot-directory-form-row">
				        <link rel="stylesheet" href="../css/leaflet.css" />
						<link rel="stylesheet" href="../css/leaflet.draw.css" />
						<div id="searchDeviceMapModalBody" style="width: 100%; height: 400px" class="modal-body modalBody">		   
					</div>
				</div> 
				<div class="modal-footer">
                  <button type="button" id="cancelMapBtn" class="btn cancelBtn"  data-dismiss="modal">Cancel</button>
                </div>
              </div>
            </div>
        </div>   


		<!-- Modal for Ownership Visibility and Delegations 
		<div class="modal fade" id="delegationsModal" tabindex="-1" role="dialog" aria-labelledby="modalAddWidgetTypeLabel" aria-hidden="true">
			<div class="modal-dialog" role="document">
			  <div class="modal-content">
				<div id="delegationHeadModalLabel"  class="modalHeader centerWithFlex">
				  
				</div>
	
					<div id="delegationsModalBody" class="modal-body modalBody">
						
						
						<div id="delegationsModalRightCnt" class="col-xs-12 col-sm-12">
						
								 <form class="form-horizontal">
						
								<div id="visibilityCnt" class="tab-pane fade in">
									<div class="row" id="visibilityFormRow">
										 <legend><div class="col-xs-12 centerWithFlex delegationsModalLbl modalFirstLbl" id="changeOwnershipLbl">
											Change visibility 
										</div> </legend>
										<div class="row" class="col-xs-12 col-md-6">
											<div class="col-xs-12 col-md-4" id="newVisibilityCnt">
											
												 <div id="visID"></div>
											</div>
											<div class="col-xs-12 col-md-4" id="newVisibilityCnt">	 
											<div>	 
												  <button type="button" id="newVisibilityPublicBtn" class="btn pull-right confirmBtn">Make It Public</button>
												  <button type="button" id="newVisibilityPrivateBtn" class="btn pull-right confirmBtn">Make It Private</button>
											</div>
											
											</div>
											<div class="col-xs-12 col-md-4" id="newVisibilityResultMsg">
											
											</div> 
										
										</div>
									</div>    
								</div>
							
								</form>
								
								<form class="form-horizontal">
								<div id="ownershipCnt" class="tab-pane fade in active">
									<div class="row" id="ownershipFormRow">
										 <legend><div class="col-xs-12 centerWithFlex delegationsModalLbl modalFirstLbl" id="changeOwnershipLbl">
											Change ownership
										</div> </legend>
										<div class="col-xs-12" id="newOwnershipCnt">
											<div class="input-group">
												<input type="text" class="form-control" id="newOwner" placeholder="New owner username">
												<span class="input-group-btn">
												  <button type="button" id="newOwnershipConfirmBtn" class="btn confirmBtn disabled">Confirm</button>
												</span>
											</div>
											<div class="col-xs-12 centerWithFlex delegationsModalMsg" id="newOwnerMsg">
												New owner username can't be empty
											</div>    
										</div>
										<div class="col-xs-12 centerWithFlex" id="newOwnershipResultMsg">
											
										</div>   
									</div>    
								</div>
								</form>
						
						</div>
				
					</div>
					<div id="delegationsModalFooter" class="modal-footer">
					  <button type="button" id="delegationsCancelBtn" class="btn cancelBtn" data-dismiss="modal">Close</button>
					</div>
			
			  </div>
			</div>
		</div>
		-->



		<div class="modal fade" id="delegationsModal" tabindex="-1" role="dialog" aria-labelledby="modalAddWidgetTypeLabel" aria-hidden="true">
			<div class="modal-dialog" role="document">
			  <div class="modal-content">
				<div id="delegationHeadModalLabel"  class="modalHeader centerWithFlex">
				  
				</div>
				<form class="form-horizontal">
	
					<div id="delegationsModalBody" class="modal-body modalBody">
                                                       <!-- Tabs -->
                                                       <ul id="delegationsTabsContainer" class="nav nav-tabs nav-justified">
                                                                   <li id="ownershipTab" class="active"><a data-toggle="tab" href="#ownershipCnt" class="dashboardWizardTabTxt" aria-expanded="false">Ownership</a></li>
                                                                   <li id="visibilityTab"><a data-toggle="tab" href="#visibilityCnt" class="dashboardWizardTabTxt">Visibility</a></li>
                                                                   <li id="delegationsTab"><a data-toggle="tab" href="#delegationsCnt" class="dashboardWizardTabTxt">Delegations</a></li>
                                                                   <li id="delegationsTabGroup"><a data-toggle="tab" href="#delegationsCntGroup" class="dashboardWizardTabTxt">Group Delegations</a></li>
                                                       </ul>
                                                       <!-- Fine tabs -->

                                                       <!-- Tab content -->
                                                       <div class="tab-content">

                                                               <!-- Visibility cnt -->
								<div id="visibilityCnt" class="tab-pane fade in">
									<div class="row" id="visibilityFormRow">
										 <legend><div class="col-xs-12 centerWithFlex delegationsModalLbl modalFirstLbl" id="changeOwnershipLbl">
											Change visibility 
										</div> </legend>
										<div class="row" class="col-xs-12 col-md-6">
											<!--<div class="col-xs-12" id="newVisibilityCnt"> -->
											<div class="col-xs-12 col-md-2" id="newVisibilityCnt">
											
												 <div id="visID"></div>
											</div>
											<div class="col-xs-12 col-md-6" id="newVisibilityCnt">	 
											<div  class="row">	
											
												  <button type="button" id="newVisibilityPublicBtn" class="btn pull-right confirmBtn">Make It Public</button> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
												
												  <button type="button" id="newVisibilityPrivateBtn" class="btn pull-right confirmBtn">Make It Private</button>
											  
											</div>
											
											</div>
											<!-- <div class="col-xs-12 centerWithFlex" id="newVisibilityResultMsg"> -->
											<div class="col-xs-12 col-md-4" id="newVisibilityResultMsg">
											
											</div> 
										
										</div>
									</div>    
								</div>
								
								<!-- Ownership cnt -->	
								<div id="ownershipCnt" class="tab-pane fade in active">
									<div class="row" id="ownershipFormRow">
										 <legend><div class="col-xs-12 centerWithFlex delegationsModalLbl modalFirstLbl" id="changeOwnershipLbl">
											Change ownership
										</div> </legend>
										<div class="col-xs-12" id="newOwnershipCnt">
											<div class="input-group">
												<input type="text" class="form-control" id="newOwner" placeholder="New owner username">
												<span class="input-group-btn">
												  <button type="button" id="newOwnershipConfirmBtn" class="btn confirmBtn disabled">Confirm</button>
												</span>
											</div>
											<div class="col-xs-12 centerWithFlex delegationsModalMsg" id="newOwnerMsg">
												New owner username can't be empty
											</div>    
										</div>
										<div class="col-xs-12 centerWithFlex" id="newOwnershipResultMsg">
											
										</div>   
									</div>    
								</div>

                                                               <!-- Delegation cnt -->
                                                               <div id="delegationsCnt" class="tab-pane fade in">
                                                                       <div class="row" id="delegationsFormRow">
                                                                               <legend><div class="col-xs-12 centerWithFlex modalFirstLbl" id="newDelegationLbl">
                                                                                       Add new delegation
                                                                               </div></legend>
                                                                               <div class="col-xs-12" id="newDelegationCnt">
                                                                                       <div class="input-group">
                                                                                               <input type="text" class="form-control" name="newDelegation" id="newDelegation" placeholder="Delegated username">
                                                                                               <span class="input-group-btn">
                                                                                                 <button type="button" id="newDelegationConfirmBtn" class="btn confirmBtn disabled">Confirm</button>
                                                                                               </span>
                                                                                       </div>
                                                                                       <div class="col-xs-12 centerWithFlex delegationsModalMsg" id="newDelegatedMsg">
                                                                                               Delegated username can't be empty
                                                                                       </div>
                                                                               </div>

                                                                               <legend><div class="col-xs-12 centerWithFlex" id="currentDelegationsLbl">
                                                                                       Current delegations
                                                                               </div></legend>
                                                                               <div class="col-xs-12" id="delegationsTableCnt">
                                                                                       <table id="delegationsTable">
                                                                                               <thead>
                                                                                                 <th>Delegated user</th>
                                                                                                 <th>Remove</th>
                                                                                               </thead>
                                                                                               <tbody>
                                                                                               </tbody>
                                                                                       </table>
                                                                               </div>
                                                                       </div>
                                                               </div>

                                                               <!-- Delegation Group cnt -->
                                                                <div id="delegationsCntGroup" class="tab-pane fade in">
                                                                        <div class="row" id="delegationsFormRowGroup">
                                                                                <legend><div class="col-xs-12 centerWithFlex modalFirstLbl" id="newDelegationLblGroup">
                                                                                        Add new Group delegation
                                                                                </div></legend>
                                                                          <div class="col-xs-12"  class="input-group">
                                                                                                       <div id="newDelegationCntGroup">
                                                                                                       <div class="col-xs-4">
                                                                                                               <select name="newDelegationOrganization" id="newDelegationOrganization" class="modalInputTxt">
                                                                                                               </select>
                                                                                                       </div>
                                                                                                       <div class="col-xs-4">
                                                                                                               <select name="newDelegationGroup" id="newDelegationGroup" class="modalInputTxt">
                                                                                                               </select>
                                                                                                       </div>
                                                                                                       <div class="col-xs-4">
                                                                                                               <span class="input-group-btn">
                                                                                                                       <button type="button" id="newDelegationConfirmBtnGroup" class="btn confirmBtn">Confirm</button>
                                                                                                               </span>
                                                                                                       </div>
                                                                                                       <div class="col-xs-12 centerWithFlex delegationsModalMsg" id="newDelegatedMsgGroup">
                                                                                                       </div>
                                                                                               </div>
                                                                                </div>
                                                                                <legend><div class="col-xs-12 centerWithFlex" id="currentDelegationsLblGroup">
                                                                                        Current Group delegations
                                                                                </div></legend>
                                                                                <div class="col-xs-12" id="delegationsTableCntGroup">
                                                                                        <table id="delegationsTableGroup">
                                                                                                <thead>
                                                                                                  <th>Delegated group</th>
                                                                                                  <th>Remove</th>
                                                                                                </thead>
                                                                                                <tbody>
                                                                                                </tbody>
                                                                                        </table>
                                                                                </div>
                                                                        </div>
                                                                </div>



						</div>
					</div>
					<div id="delegationsModalFooter" class="modal-footer">
					  <button type="button" id="delegationsCancelBtn" class="btn cancelBtn" data-dismiss="modal">Close</button>
					</div>
				 </form>	
		</div>
            </div>
        </div>
		

		
    </body>
</html>		



