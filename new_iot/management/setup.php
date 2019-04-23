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
         var organization = "<?php echo $_SESSION['organization']; ?>";
                 var kbUrl = "<?php echo $_SESSION['kbUrl']; ?>";
                 var gpsCentreLatLng = "<?php echo $_SESSION['gpsCentreLatLng']; ?>";
                 var zoomLevel = "<?php echo $_SESSION['zoomLevel']; ?>";    
		 var titolo_default = "<?php echo $default_title; ?>";	
		 var access_denied = "<?php echo $access_denied; ?>";
		 var nascondi= "<?php echo $hide_menu; ?>";
		 var sessionEndTime = "<?php echo $_SESSION['sessionEndTime']; ?>";  
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
		
		<script  src="js/devices.js"></script>
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
                   
                            <div class="row accountEditRow">
                              <div class="col-xs-12 col-md-3 accountEditFieldContainer">
                                 <div class="accountEditIconContainer"><i class="fa fa-address-card-o" aria-hidden="true"></i></div> 
                                 <div class="accountEditDescContainer">Policy name</div>
                                 <div class="accountEditSubfieldContainer">
                                    <input type="text" id="policyName" name="policyName">
                                 </div>
                                 <div id="policyNameMsg" class="accountEditSubfieldContainer"></div>    
                             </div>
                             <div class="col-xs-12 col-md-3 col-md-offset-1 accountEditFieldContainer">
                                 <div class="accountEditIconContaaccountEditDescContaineriner"><i class="fa fa-address-card-o" aria-hidden="true"></i></div>
                                 <div class="accountEditDescContainer">IOT Broker</div>
                                 <div class="accountEditSubfieldContainer">
                                    <input type="text" id="policyContextbroker" name="policyContextbroker">
                                 </div>
                                 <div id="policyContextbrokerMsg" class="accountEditSubfieldContainer"></div>    
                             </div> 
                            
                           </div>
                           <div class="row accountEditRow">
                              <div class="col-xs-12 col-md-3 accountEditFieldContainer">
                                  <div class="accountEditIconContainer"><i class="fa fa-at"></i></div>
                                 <div class="accountEditDescContainer">Healthiness_Criteria</div>
                                 <div class="accountEditSubfieldContainer">
                                    <input type="text" id="policyHealthiness_Criteria" name="policyHealthiness_Criteria">
                                 </div>
                                 <div id="policyHealthiness_CriteriaMsg" class="accountEditSubfieldContainer"></div>    
                             </div>
                             <div class="col-xs-12 col-md-3 col-md-offset-1 accountEditFieldContainer">
                                 <div class="accountEditIconContainer"><i class="fa fa-key"></i></div>
                                 <div class="accountEditDescContainer">Healthiness_value</div>
                                 <div class="accountEditSubfieldContainer">
                                    <input type="text" id="policyHealthiness_value" name="policyHealthiness_value">
                                 </div>
                                 <div id="policyHealthiness_valueMsg" class="accountEditSubfieldContainer"></div>    
                             </div> 
                             
                           </div> 
                           <div class="row accountEditRow" id="editAccountBtnRow">
                              <button type="button" id="addPolicyConfirmBtn" class="btn pull-left internalLink" style="margin-right: 15px; background-color: rgba(0, 162, 211, 1); color: white; font-weight: bold">Apply policy</button>
                              <button type="button" id="policyCancelBtn" class="btn pull-left" data-dismiss="modal" style="background-color: #f3cf58; color: white; font-weight: bold">Cancel</button>
                           </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    
    
        <div class="modal fade" id="editAccountOkModal" tabindex="-1" role="dialog" aria-labelledby="editAccountOkModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="editAccountOkModalLabel">Account update</h5>
                </div>
                <div class="modal-body">
                    <div id="editAccountOkModalInnerDiv1" class="modalBodyInnerDiv">Account successfully updated</div>
                    <div id="editAccountOkModalInnerDiv2" class="modalBodyInnerDiv"><i class="fa fa-check" style="font-size:42px"></i></div>
                </div>
              </div>
            </div>
        </div>

        <div class="modal fade" id="editAccountKoModal" tabindex="-1" role="dialog" aria-labelledby="editAccountKoModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="editAccountKoModalLabel">Account update</h5>
                </div>
                <div class="modal-body">
                    <div id="addUserKoModalInnerDiv1" class="modalBodyInnerDiv">Account update failed, please try again</div>
                    <div id="addUserKoModalInnerDiv2" class="modalBodyInnerDiv"><i class="fa fa-frown-o" style="font-size:42px"></i></div>
                </div>
              </div>
            </div>
        </div>
        
    </body>
</html>

<script type='text/javascript'>
    $(document).ready(function () 
    {
       var sessionEndTime = "<?php echo $_SESSION['sessionEndTime']; ?>";
        $('#sessionExpiringPopup').css("top", parseInt($('body').height() - $('#sessionExpiringPopup').height()) + "px");
        $('#sessionExpiringPopup').css("left", parseInt($('body').width() - $('#sessionExpiringPopup').width()) + "px");
        
        setInterval(function(){
            var now = parseInt(new Date().getTime() / 1000);
            var difference = sessionEndTime - now;
            
            if(difference === 300)
            {
                $('#sessionExpiringPopupTime').html("5 minutes");
                $('#sessionExpiringPopup').show();
                $('#sessionExpiringPopup').css("opacity", "1");
                setTimeout(function(){
                    $('#sessionExpiringPopup').css("opacity", "0");
                    setTimeout(function(){
                        $('#sessionExpiringPopup').hide();
                    }, 1000);
                }, 4000);
            }
            
            if(difference === 120)
            {
                $('#sessionExpiringPopupTime').html("2 minutes");
                $('#sessionExpiringPopup').show();
                $('#sessionExpiringPopup').css("opacity", "1");
                setTimeout(function(){
                    $('#sessionExpiringPopup').css("opacity", "0");
                    setTimeout(function(){
                        $('#sessionExpiringPopup').hide();
                    }, 1000);
                }, 4000);
            }
            
            if((difference > 0)&&(difference <= 60))
            {
                $('#sessionExpiringPopup').show();
                $('#sessionExpiringPopup').css("opacity", "1");
                $('#sessionExpiringPopupTime').html(difference + " seconds");
            }
            
            if(difference <= 0)
            {
                //console.log("Logout");
                location.href = "logout.php?sessionExpired=true";
            }
            /*else
            {
                console.log("Keep in");
            }*/
        }, 1000);
        
       $('#mainContentCnt').height($('#mainMenuCnt').height() - $('#headerTitleCnt').height());
        
        $(window).resize(function(){
            $('#mainContentCnt').height($('#mainMenuCnt').height() - $('#headerTitleCnt').height());
        });
        
        $('#accountManagementLink .mainMenuItemCnt').addClass("mainMenuItemCntActive");
        $('#mobMainMenuPortraitCnt #accountManagementLink .mobMainMenuItemCnt').addClass("mainMenuItemCntActive");
        $('#mobMainMenuLandCnt #accountManagementLink .mobMainMenuItemCnt').addClass("mainMenuItemCntActive");
    
       
       $('#policyCancelBtn').off("click");
       $("#policyCancelBtn").click(function(){
			$('#policyName').val("");
			$('#policyContextbroker').val("");
			$('#policyHealthiness_Criteria').val("");
			$('#policyHealthiness_value').val("");
       });
       
       $('#addPolicyConfirmBtn').off("click");
       $("#addPolicyConfirmBtn").click(function(){
         		 $.ajax({
						 url: "../api/contextbroker.php",
						 data:{
							  action: "insert_default_policy",   
							  name: $('#policyName').val(),
							  cb: $('#policyContextbroker').val(),
							  hc: $('#policyHealthiness_Criteria').val(),
							  hv: $('#policyHealthiness_value').val()
							},
							 type: "POST",
							 async: true,
							 success: function (data) 
							 {
								if(data["status"] === 'ko')
								{
									console.log("Error adding Default policy");
									console.log(data);
									
							 
								}			 
								else (data["status"] === 'ok')
								{
									console.log("Added Default Policy");
									console.log(data);
									  $('#editAccountOkModal').show();
								
						        
								} 
								 
							 },
							 error: function (mydata)
													{
								   console.log("Error insert default policy");  
								   console.log("Error status -- Ko result: " + JSON.stringify(mydata));
							  
								 
							 } 
						 });
       });
       
    });//Fine document ready
</script>