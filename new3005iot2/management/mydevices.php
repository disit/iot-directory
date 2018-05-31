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
  $oidc = new OpenIDConnectClient('https://www.snap4city.org', $clientId, $clientSecret);
  $oidc->providerConfigParam(array('token_endpoint' => 'https://www.snap4city.org/auth/realms/master/protocol/openid-connect/token'));
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
		
		<style>
		.btn-round {
			width: 30px;
			height:30px;
			border-radius: 50%;
		}
		</style>
 

		<script>
		 var loggedRole = "<?php echo $_SESSION['loggedRole']; ?>";
         var admin = "<?php echo $_SESSION['loggedRole']; ?>";
		 var titolo_default = "<?php echo $default_title; ?>";	
		 var access_denied = "<?php echo $access_denied; ?>";
		 var nascondi= "<?php echo $hide_menu; ?>";
		 var sessionEndTime = "<?php echo $_SESSION['sessionEndTime']; ?>";
         var sessionToken = "<?php  if (isset($_SESSION['refreshToken'])) echo $_SESSION['refreshToken']; else echo ""; ?>";		 
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
		
		<script  src="js/mydevices.js"></script>
		<script  src="js/devicesManagement.js"></script>
		<script  src="js/devicesEditManagement.js"></script>
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
                <div class="col-xs-12 col-md-10" id="mainCnt">
                    <div class="row hidden-md hidden-lg">
                        <div id="mobHeaderClaimCnt" class="col-xs-12 hidden-md hidden-lg centerWithFlex">
                            Snap4City
                        </div>
                    </div>
                    <div class="row" id="title_row">
                        <div class="col-xs-10 col-md-12 centerWithFlex" id="headerTitleCnt">IoT Directory: Devices</div>
                        <div class="col-xs-2 hidden-md hidden-lg centerWithFlex" id="headerMenuCnt"><?php include "mobMainMenu.php" ?></div> 
                    </div>
					
							
                    <div class="row">
                        <div class="col-xs-12" id="mainContentCnt">
						
						
				             <div id="synthesis" class="row hidden-xs hidden-sm mainContentRow">
                             <!--   <div  class="col-xs-12 mainContentRowDesc"></div> -->
                                <div id="dashboardTotNumberCnt" class="col-md-2 mainContentCellCnt">
                                    <div class="col-md-12 centerWithFlex pageSingleDataCnt">
                                        <?php
                                            $query = "SELECT count(*) AS qt FROM devices";
                                            $result = mysqli_query($link, $query);
                                            
                                            if($result)
                                            {
                                               $row = $result->fetch_assoc();
												echo $row['qt'];
                                            }
                                            else
                                            {
												echo '-';
                                            }
                                        ?>
                                    </div>
                                    <div class="col-md-12 centerWithFlex pageSingleDataLabel">
                                        devices
                                    </div>
                                </div>
                                <div id="dashboardTotActiveCnt" class="col-md-2 mainContentCellCnt">
                                    <div class="col-md-12 centerWithFlex pageSingleDataCnt">
                                        <?php //MM
                                            $query = "SELECT count(*) AS qt FROM devices WHERE  mandatoryproperties = true and mandatoryvalues = true ";
                                            $result = mysqli_query($link, $query);
                                            
                                            if($result)
                                            {
                                               $row = $result->fetch_assoc();
                                               echo $row['qt'];
                                            }
                                            else
                                            {
                                                echo '-';
                                            }
                                        ?>
                                    </div>
                                    <div class="col-md-12 centerWithFlex pageSingleDataLabel">
                                        active
                                    </div>
                                </div>
                                <div id="dashboardTotPermCnt" class="col-md-2 mainContentCellCnt">
                                    <div class="col-md-12 centerWithFlex pageSingleDataCnt">
                                        <?php //MM
                                            $query = "SELECT count(*) AS qt FROM devices where visibility='public'";
                                            $result = mysqli_query($link, $query);
                                            
                                            if($result)
                                            {
                                               $row = $result->fetch_assoc();
                                               echo $row['qt'];
                                            }
                                            else
                                            {
                                                echo '-';
                                            }
                                        ?>
                                    </div>
                                    <div class="col-md-12 centerWithFlex pageSingleDataLabel">
                                        public
                                    </div>
                                </div>
								   <div id="dashboardTotPrivateCnt" class="col-md-2 mainContentCellCnt">
                                    <div class="col-md-12 centerWithFlex pageSingleDataCnt">
                                        <?php  
                                            $query = "SELECT count(*) AS qt FROM devices where visibility='private'";
                                            $result = mysqli_query($link, $query);
                                            
                                            if($result)
                                            {
                                               $row = $result->fetch_assoc();
                                               echo $row['qt'];
                                            }
                                            else
                                            {
                                                echo '-';
                                            }
                                        ?>
                                    </div>
                                    <div class="col-md-12 centerWithFlex pageSingleDataLabel">
                                        private
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
                                            $query = "SELECT name,kgenerator FROM model";
                                            $result = mysqli_query($link, $query);

                                            if($result)
                                            {
                                               while($row = $result->fetch_assoc())
                                               { 
                                                 $name=$row["name"];
												 $kgen = $row["kgenerator"];
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
                                <div class="col-xs-12 mainContentRowDesc"></div>
                                <div class="col-xs-12 mainContentCellCnt">
                                    <table id="devicesTable" class="table"></table>
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
                <div class="modal-footer">
                  <button type="button" id="deleteDeviceCancelBtn" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                  <button type="button" id="deleteDeviceConfirmBtn" class="btn btn-primary">Confirm</button>
                </div>
              </div>
            </div>
        </div>

 
	
	     <div class="modal fade" id="registerDeviceModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modalHeader centerWithFlex">
                  Registering New Device
                </div>
                <div id="addDeviceModalCreating" class="modal-body container-fluid">
                    <div class="row">
                        <div class="col-sm-6 col-sm-offset-3 centerWithFlex">
                                Registering device, please wait
                        </div> 
                    </div>
                    <div class="row">
                        <div class="col-sm-6 col-sm-offset-3 centerWithFlex">
                                <div class="col-xs-12 centerWithFlex"><i class="fa fa-circle-o-notch fa-spin" style="font-size:36px;"></i></div>
                        </div> 
                    </div>
                </div> 
				</div>
			</div>
		</div>

        <!-- Success  -->
		
        <div class="modal fade" id="addDeviceOkModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modalHeader centerWithFlex">
                  Success device registration
                </div>
                <div id="addDeviceModalBody" class="modal-body modalBody">
                    <div class="row">
                        <div class="col-xs-12 modalCell">
                            <div id="addDeviceOkModalInnerDiv1" class="modalDelMsg col-xs-12 centerWithFlex">
                                
                            </div>
                            <div class="modalDelObjName col-xs-12 centerWithFlex" id="addDeviceOkModalInnerDiv2"><i class="fa fa-check" style="font-size:36px"></i></div> 
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                  <button type="button" id="addDeviceModalCancelBtn" class="btn btn-secondary" data-dismiss="modal">DONE</button>
                </div>
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
      
        <!--Success -->
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
                <!--<div class="modal-footer">
                  
                </div>-->
              </div>
            </div>
        </div>
        
        <!-- Fail -->
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
        </div>
		

		
		
		
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
    </body>
</html>		


