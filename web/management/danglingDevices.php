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
	$default_title = "IoT Directory: Devices Without Owner";
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

/* 
require '../sso/autoload.php';
use Jumbojett\OpenIDConnectClient;


 if (isset($_SESSION['refreshToken'])) {
   $oidc = new OpenIDConnectClient($keycloakHostUri, $clientId, $clientSecret);
   $oidc->providerConfigParam(array('token_endpoint' => $keycloakHostUri.'/auth/realms/master/protocol/openid-connect/token'));
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
		
		<script  src="js/danglingDevices.js"></script>
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
                        <div class="col-xs-2 hidden-md hidden-lg centerWithFlex" id="headerMenuCnt"><?php include "mobMainMenu.php" ?></div> 
                    </div>
					<?php } //MM201218 FINE ?>
							
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
							</div>
							
							
                            <div id="displayAllDeviceRow" class="row mainContentRow">
                                <div class="col-xs-12 mainContentRowDesc"></div>
                                <div class="col-xs-12 mainContentCellCnt">
								<div class="row" style= "background-color: rgb(241, 245, 244);">
									<div class="col-xs-12 col-md-6 modalCell" style= "background-color: rgb(241, 245, 244);">
									</div>
									<div class="col-xs-12 col-md-6 modalCell" style= "background-color: rgb(241, 245, 244);">
										<div class="pull-right"><button id="deleteDanglingDeviceBtn"  class="btn btn-danger">Delete All</button></div>
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
										<th data-cellTitle="status">Status</th>							
										<th data-cellTitle="delete">Delete</th>												
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
		
		
		
     
        <div class="modal fade" id="deleteDanglingDeviceModal" tabindex="-1" role="dialog" aria-labelledby="deleteDanglingDeviceModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="deleteDanglingDeviceModalLabel">Dangling Device Deletion</h5>
                </div>
                <div class="modal-body">
					Do you want to confirm deletion of the following dangling device?
                </div>
                <div class="modal-footer">
                  <button type="button" id="deleteDanglingDeviceCancelBtn" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                  <button type="button" id="deleteDanglingDeviceConfirmBtn" class="btn btn-primary">Confirm</button>
                </div>
              </div>
            </div>
        </div>
		
		
		 <div class="modal fade" id="deleteAllDanglingDeviceModal" tabindex="-1" role="dialog" aria-labelledby="deleteAllDanglingDeviceModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="deleteAllDanglingDeviceModalLabel">Dangling Device Deletion</h5>
                </div>
                <div class="modal-body">
					Do you want to confirm deletion of all dangling device?
                </div>
                <div class="modal-footer">
                  <button type="button" id="deleteAllDanglingDeviceCancelBtn" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                  <button type="button" id="deleteAllDanglingDeviceConfirmBtn" class="btn btn-primary">Confirm</button>
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
	
		
    </body>
</html>		




