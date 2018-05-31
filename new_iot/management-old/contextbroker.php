<?php
/* Dashboard Builder.
   Copyright (C) 2016 DISIT Lab https://www.disit.org - University of Florence

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
		$default_title = "IoT Directory: Context Brokers";
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
        <script type="text/javascript" src="../js/filestyle/src/bootstrap-filestyle.min.js"></script>

       <!-- Font awesome icons -->
        <link rel="stylesheet" href="../js/fontAwesome/css/font-awesome.min.css">

        <link href="https://fonts.googleapis.com/css?family=Cabin:400,500,600,700|Catamaran|Varela+Round" rel="stylesheet">
        
        <!-- Custom CSS -->
        <link href="../css/dashboard.css" rel="stylesheet">
        
        <!-- Custom scripts -->
<script>
 var loggedRole = "<?php echo $_SESSION['loggedRole']; ?>";
 var admin = "<?php echo $_SESSION['loggedRole']; ?>";
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

        <script type="text/javascript" src="../js/dashboard_mng.js"></script>

<!-- Custom scripts -->
<script type="text/javascript" src="js/contextbroker.js"></script>
		
		
<!-- Custom scripts -->
<script type="text/javascript" src="js/cbsManagement.js"></script>
<script type="text/javascript" src="js/cbsEditManagement.js"></script>

 
		
		<!-- leaflet scripts -->
		
		<script type="text/javascript" src="../js/leaflet.js"></script>
		<script type="text/javascript" src="../js/leaflet.draw.js"></script>
		<script type="text/javascript" src="../js/jquery.fancytree-all.min.js"></script>
        
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
                            Snap4City IoT Directory
                        </div>
                    </div>
                    <div class="row" id="title_row">
                        <div class="col-xs-10 col-md-12 centerWithFlex" id="headerTitleCnt">IoT Directory: Context Brokers</div>
                        <div class="col-xs-2 hidden-md hidden-lg centerWithFlex" id="headerMenuCnt"><?php include "mobMainMenu.php" ?></div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12" id="mainContentCnt">
                            <div class="row hidden-xs hidden-sm mainContentRow">
                                <div class="col-xs-12 mainContentRowDesc">Synthesis</div>
								
                                <div id="dashboardTotNumberCnt" class="col-md-2 mainContentCellCnt">
                                    <div class="col-md-12 centerWithFlex pageSingleDataCnt">
                                        <?php
                                            $query = "SELECT count(*) AS qt FROM contextbroker";
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
									<!-- MM 0105 -->
                                    <div class="col-md-12 centerWithFlex pageSingleDataLabel">
                                        Total
                                    </div>
                                </div>
                               
                              </div>
                            <div class="row mainContentRow">
                                <div class="col-xs-12 mainContentRowDesc">List</div>
                                <div class="col-xs-12 mainContentCellCnt">
                                    <table id="contextBrokerTable" class="table"></table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
		
		<div class="modal fade" id="deleteContextBrokerModal" tabindex="-1" role="dialog" aria-labelledby="deleteCBModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="deleteCBModalLabel">Context Broker deletion</h5>
                </div>
                <div class="modal-body">

                </div>
                <div class="modal-footer">
                  <button type="button" id="deleteContextBrokerCancelBtn" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                  <button type="button" id="deleteContextBrokerConfirmBtn" class="btn btn-primary">Confirm</button>
                </div>
              </div>
            </div>
        </div>
		
        <!-- Create Context Broker -->
        <div class="modal fade" id="addContextBrokerModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <form id="addContextBrokerForm" class="form-horizontal" name="addContextBrokerForm" role="form" method="post" action="">  
              <div class="modal-content">
                <div class="modalHeader centerWithFlex">
                  Add new context broker 
                </div>

                <div id="addContextBrokerModalBody" class="modal-body modalBody">
                    <ul id="addContextBrokerModalTabs" class="nav nav-tabs nav-justified">
						<li class="active"><a data-toggle="tab" href="#infoTabCB">Info</a></li>
                        <li><a data-toggle="tab" href="#geoPositionTabCB">Geo-Position</a></li>
                        <li><a data-toggle="tab" href="#securityTabCB">Security</a></li>
                    </ul>
                    
                    <div class="tab-content">
                       
                        <!-- Info tab -->
                        <div id="infoTabCB" class="tab-pane fade in active">
                            <div class="row">
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputNameCB" id="inputNameCB" required> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Name</div>
									<div id="inputNameCBMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
                              
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputIpCB" id="inputIpCB"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">IP</div>
									<div id="inputIpCBMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
                              
                                 <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputPortCB" id="inputPortCB">
                                    </div>
                                    <div class="modalFieldLabelCnt">Port</div>
									<div id="inputPortCBMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
 
									<div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <select id="selectProtocolCB" name="selectProtocolCB" class="modalInputTxt">
                                            <option value="none">None</option>
                                            <?php 
                                                $link = mysqli_connect($host, $username, $password);
                                                mysqli_select_db($link, $dbname);

                                                $q1 = "SELECT name FROM protocols";
                                                $r1 = mysqli_query($link, $q1);

                                                if($r1)
                                                {
                                                    while($row = $r1->fetch_assoc())
                                                    {
                                                        echo '<option value="' . $row['name'] . '">' . $row['name'] . '</option>';
                                                    }
                                                }
                                                else echo mysqli_error($link); 
                                            ?>
                                        </select>
                                    </div>
                                    <div class="modalFieldLabelCnt">Protocol</div>
									<div id="inputProtocolCBMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
        
                            </div>
                        </div>
                         
                        <!-- Geo-Position tab -->
                        <div id="geoPositionTabCB" class="tab-pane fade">
                            <div class="row">
								<div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputLatitudeCB" id="inputLatitudeCB"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Latitude</div>
									<div id="inputLatitudeCBMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputLongitudeCB" id="inputLongitudeCB"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Longitude</div>
									<div id="inputLongitudeCBMsg" class="modalFieldMsgCnt">&nbsp;</div>
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
                        <div id="securityTabCB" class="tab-pane fade">
                            <div class="row">
								<div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputLoginCB" id="inputLoginCB"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Login</div>
									<div id="inputLoginCBMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputPasswordCB" id="inputPasswordCB"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Password</div>
									<div id="inputPasswordCBMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
                 
                            </div>
                        </div>
                    </div>
					<!--
					<div class="row" id="addCBLoadingMsg">
                        <div class="col-xs-12 centerWithFlex">Adding Contextbroker, please wait</div>
                    </div>
                    <div class="row" id="addCBLoadingIcon">
                        <div class="col-xs-12 centerWithFlex"><i class="fa fa-circle-o-notch fa-spin" style="font-size:36px;"></i></div>
                    </div>
                    <div class="row" id="addCBOkMsg">
                        <div class="col-xs-12 centerWithFlex">Contextbroker added successfully</div>
                    </div>
                    <div class="row" id="addCBOkIcon">
                        <div class="col-xs-12 centerWithFlex"><i class="fa fa-thumbs-o-up" style="font-size:36px"></i></div>
                    </div>
                    <div class="row" id="addCBKoMsg">
                        <div class="col-xs-12 centerWithFlex">Error adding contextbroker</div>
                    </div>
                    <div class="row" id="addCBKoIcon">
                        <div class="col-xs-12 centerWithFlex"><i class="fa fa-thumbs-o-down" style="font-size:36px"></i></div>
                    </div>
					-->
				</div>
			   <div id="addContextBrokerModalFooter" class="modal-footer">
                  <button type="button" id="addContextBrokerCancelBtn" class="btn cancelBtn" data-dismiss="modal">Cancel</button>
                  <button type="submit" id="addContextBrokerConfirmBtn" name="addContextBrokerConfirmBtn" class="btn confirmBtn internalLink">Confirm</button>
                </div>
            </div>
            </form>		
        </div>
	</div>
	

        <div class="modal fade" id="editContextBrokerModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div id="editCBModalLabel" class="modalHeader centerWithFlex">
                  
                </div>
                <div id="editCBModalLoading" class="modal-body container-fluid">
                    <div class="row">
                        <div class="col-sm-6 col-sm-offset-3 centerWithFlex">
                            Loading CB's data, please wait
                        </div> 
                    </div>
                    <div class="row">
                        <div class="col-sm-6 col-sm-offset-3 centerWithFlex">
                            <div class="col-xs-12 centerWithFlex"><i class="fa fa-circle-o-notch fa-spin" style="font-size:36px;"></i></div>
                        </div> 
                    </div>
                </div>
                <div id="editContextBrokerModalUpdating" class="modal-body container-fluid">
                    <div class="row">
                        <div class="col-sm-6 col-sm-offset-3 centerWithFlex">
                                Updating CB's data, please wait
                        </div> 
                    </div>
                    <div class="row">
                        <div class="col-sm-6 col-sm-offset-3 centerWithFlex">
                                <div class="col-xs-12 centerWithFlex"><i class="fa fa-circle-o-notch fa-spin" style="font-size:36px;"></i></div>
                        </div> 
                    </div>
                </div>  
                <div id="editContextBrokerModalBody" class="modal-body modalBody">
                   <!--				   <form id="editUserForm" name="editUserForm" role="form" method="post" action="process-form.php" data-toggle="validator">-->
                        
					<ul id="editContextBrokerModalTabs" class="nav nav-tabs nav-justified">
						<li class="active"><a data-toggle="tab" href="#editInfoTabCB">Info</a></li>
                        <li><a data-toggle="tab" href="#editGeoPositionTabCB">Geo-Position</a></li>
                        <li><a data-toggle="tab" href="#editSecurityTabCB">Security</a></li>
                    </ul>
                    
                    <div class="tab-content">
                       
                        <!-- Info tab -->
                        <div id="editInfoTabCB" class="tab-pane fade in active">
                            <div class="row">
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputNameCBM" id="inputNameCBM" required readonly> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Name</div>
									<div id="inputNameCBMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
                              
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputIpCBM" id="inputIpCBM"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">IP</div>
									<div id="inputIpCBMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputPortCBM" id="inputPortCBM">
                                    </div>
                                    <div class="modalFieldLabelCnt">Port</div>
									<div id="inputPortCBMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
                               	  <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <select id="selectProtocolCBM" name="selectProtocolCBM" class="modalInputTxt">
                                            <option value="none">None</option>
                                            <?php 
                                                $link = mysqli_connect($host, $username, $password);
                                                mysqli_select_db($link, $dbname);

                                                $q1 = "SELECT name FROM protocols";
                                                $r1 = mysqli_query($link, $q1);

                                                if($r1)
                                                {
                                                    while($row = $r1->fetch_assoc())
                                                    {
                                                        echo '<option value="' . $row['name'] . '">' . $row['name'] . '</option>';
                                                    }
                                                }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="modalFieldLabelCnt">Protocol</div>
									<div id="selectProtocolCBMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
                              
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input id="createdDateCBM" name="createdDateCBM" type="text" readonly>
                                    </div>
                                    <div class="modalFieldLabelCnt">Created</div>
									<div id="createdDateCBMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
                               
                            </div>
                        </div>
                         
                        <!-- Geo-Position tab -->
                        <div id="editGeoPositionTabCB" class="tab-pane fade">
                            <div class="row">
								<div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputLatitudeCBM" id="inputLatitudeCBM"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Latitude</div>
									<div id="inputLatitudeCBMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputLongitudeCBM" id="inputLongitudeCBM"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Longitude</div>
									<div id="inputLongitudeCBMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
                            </div>
							
							<div class="form-row iot-directory-form-row">
									<link rel="stylesheet" href="../css/leaflet.css" />
									<link rel="stylesheet" href="../css/leaflet.draw.css" />
									<div id="addLatLongEdit" style="width: 100%; height: 400px" class="modal-body modalBody">
								</div>
							</div> 
							
                        </div>
                        
                        <!-- Device Schema tab -->
                        <div id="editSecurityTabCB" class="tab-pane fade">
                            <div class="row">
								<div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputLoginCBM" id="inputLoginCBM"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Login</div>
									<div id="inputLoginCBMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputPasswordCBM" id="inputPasswordCBM"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Password</div>
									<div id="inputPasswordCBMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
               
                            </div>
                        </div>
                    </div>
			
                       <!-- <input type="hidden" id="inputNameCBM" name="inputNameCBM"/>-->
                    <!--</form>-->    
                </div>
                <div id="editContextBrokerModalFooter" class="modal-footer">
                  <button type="button" id="editUserCancelBtn" class="btn cancelBtn" data-dismiss="modal">Cancel</button>
                  <button type="button" id="editContextBrokerConfirmBtn" class="btn confirmBtn internalLink" >Confirm</button>
                </div>
              </div>
            </div>
        </div>
		
		<div class="modal fade" id="addMap1CB" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modalHeader centerWithFlex">
					Search Context Broker Location on Map
                </div>
				<div class="form-row iot-directory-form-row">
				        <link rel="stylesheet" href="../css/leaflet.css" />
						<link rel="stylesheet" href="../css/leaflet.draw.css" />
						<div id="searchDeviceMapModalBodyCB" style="width: 100%; height: 400px" class="modal-body modalBody">
			
                  
					</div>
				</div> 
              </div>
            </div>
        </div>
	
    </body>
</html>



		
		
