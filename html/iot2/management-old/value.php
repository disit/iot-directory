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
		$default_title = "IoT Directory : Sensors and Actuators";
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

      <!--  <link href="https://fonts.googleapis.com/css?family=Cabin:400,500,600,700|Catamaran|Varela+Round" rel="stylesheet"> -->
		
        <!-- Custom CSS -->
        <link href="../css/dashboard.css" rel="stylesheet">
		
		<style>
		.btn-round {
			width: 30px;
			height:30px;
			border-radius: 50%;
		}
		</style>
        
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
		 var editButton = 1;
		 var deleteButton = 1;
		 var mapButton = 1;
		 

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

        <script type="text/javascript" src="js/value.js"></script>
		
		
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
                            Snap4City 
                        </div>
                    </div>
                    <div class="row" id="title_row">
                        <div class="col-xs-10 col-md-12 centerWithFlex" id="headerTitleCnt">IoT Directory : Sensors and Actuators</div>
                        <div class="col-xs-2 hidden-md hidden-lg centerWithFlex" id="headerMenuCnt"><?php include "mobMainMenu.php" ?></div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12" id="mainContentCnt">
                            <div class="row hidden-xs hidden-sm mainContentRow">
                                <div class="col-xs-12 mainContentRowDesc">Synthesis</div>
                                <div id="dashboardTotNumberCnt" class="col-md-2 mainContentCellCnt">
                                    <div class="col-md-12 centerWithFlex pageSingleDataCnt">
                                        <?php
                                            $query = "SELECT count(*) AS qt FROM event_values";
                                            $result = mysqli_query($link, $query);
                                            
                                            if($result)
                                            {
                                               $row = $result->fetch_assoc();
                                              // $dashboardsQt = $row['qt'];
                                               echo $row['qt'];
                                            }
                                            else
                                            {
                                               // $dashboardsQt = "-";
                                                echo '-';
                                            }
                                        ?>
                                    </div>
									<!-- MM 0105 -->
                                    <div class="col-md-12 centerWithFlex pageSingleDataLabel">
                                        Total 
                                    </div>
                                </div>
                                <div id="dashboardTotActiveCnt" class="col-md-2 mainContentCellCnt">
                                    <div class="col-md-12 centerWithFlex pageSingleDataCnt">
                                        <?php //MM
                                            $query = "SELECT count(*) AS qt FROM event_values WHERE editable =0"; // v JOIN devices d ON (v.cb=d.contextbroker and v.device=d.id) where d.kind='sensor'";
                                            $result = mysqli_query($link, $query);
                                            
                                            if($result)
                                            {
                                               $row = $result->fetch_assoc();
                                              // $dashboardsQt = $row['qt'];
                                               echo $row['qt'];
                                            }
                                            else
                                            {
                                               // $dashboardsQt = "-";
                                                echo '-';
                                            }
                                        ?>
                                    </div>
                                    <div class="col-md-12 centerWithFlex pageSingleDataLabel">
                                        Sensors
                                    </div>
                                </div>
                                <div id="dashboardTotPermCnt" class="col-md-2 mainContentCellCnt">
                                    <div class="col-md-12 centerWithFlex pageSingleDataCnt">
                                        <?php //MM
                                            $query = "SELECT count(*) AS qt FROM event_values v WHERE editable=1;"; //JOIN devices d ON (v.cb=d.contextbroker and v.device=d.id) where d.kind='actuator'";
                                            $result = mysqli_query($link, $query);
                                            
                                            if($result)
                                            {
                                               $row = $result->fetch_assoc();
                                              // $dashboardsQt = $row['qt'];
                                               echo $row['qt'];
                                            }
                                            else
                                            {
                                               // $dashboardsQt = "-";
                                                echo '-';
                                            }
                                        ?>
                                    </div>
                                    <div class="col-md-12 centerWithFlex pageSingleDataLabel">
                                        Actuators
                                    </div>
                                </div>
			
							</div>
                            <div class="row mainContentRow">
                                <div class="col-xs-12 mainContentRowDesc">List</div>
                                <div class="col-xs-12 mainContentCellCnt">
                                    <table id="valuesTable" class="table"></table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        

        <div class="modal fade" id="deleteValueModal" tabindex="-1" role="dialog" aria-labelledby="deleteValueModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="deleteValueModalLabel">Value deletion</h5>
                </div>
                <div class="modal-body">
					Do you want to confirm deletion of the following value?
                </div>
                <div class="modal-footer">
                  <button type="button" id="deleteValueCancelBtn" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                  <button type="button" id="deleteValueConfirmBtn" class="btn btn-primary">Confirm</button>
                </div>
              </div>
            </div>
        </div>

        <!-- Adding a New Value -->
        <div class="modal fade" id="addValueModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modalHeader centerWithFlex">
                  Add new value
                </div>
        
		
				<!-- <form id="addValueForm" name="addValueForm" role="form" method="post" action="" data-toggle="validator"> -->
					<div id="addValueModalBody" class="modal-body modalBody">
				
                
					<ul id="addValueModalTabs" class="nav nav-tabs nav-justified">
						<li class="active"><a data-toggle="tab" href="#addInfoTabValue">Value to Be Added </a></li>
                    </ul>
                    
					
                    <div class="tab-content">
                       
                        <!-- Value Add Tab -->
                        <div id="addInfoTabValue" class="tab-pane fade in active">
                            <div class="row">
							
								 <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <select id="selectContextBroker" name="selectContextBroker" class="modalInputTxt">

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
                                        <input type="text" class="modalInputTxt" name="inputNameDevice" id="inputNameDevice"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Device</div>
									<div id="inputNameDeviceMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
								
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputValueNameDevice" id="inputValueNameDevice"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Value Name</div>
									<div id="inputValueNameDeviceMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
								
								<div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <select id="selectDataType" name="selectDataType" class="modalInputTxt">
											<?php
												$query = "SELECT data_type FROM data_types order by data_type";
													 $result = mysqli_query($link, $query) or die(mysqli_error($link));
														if($result){
															while($row = mysqli_fetch_assoc($result)) 
																{
																	$label=$row["data_type"];
																	echo "<option value='$label'>$label</option>";		
																}
													 }
													 
											 ?>
										</select>
                                    </div>
                                    <div class="modalFieldLabelCnt">Data Type</div>
									<div id="selectDataTypeMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
								
								<div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputEditableValue" id="inputEditableValue"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Editable</div>
									<div id="inputEditableValueMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
								
								 <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <select id="selectValueType" name="selectValueType" class="modalInputTxt">
											<?php
												$query = "SELECT value_type FROM value_types ORDER BY value_type";
												$result = mysqli_query($link, $query);

												if($result)
												{
												   while($row = $result->fetch_assoc())
												   { 
													 $label=$row["value_type"];
													 echo "<option value='$label'>$label</option>";
												   }

												}
												else
												{
												   
													$label="ERROR";
													echo "<option value='$label'>$label</option>";
												}
											?>
										</select>
                                    </div>
                                    <div class="modalFieldLabelCnt">Value Type</div>
									<div id="selectValueTypeMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
								
			                       <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <select id="selectValueUnit" name="selectValueUnit" class="modalInputTxt">
											<?php
												$query = "SELECT DISTINCT value_unit_default FROM value_types ORDER BY value_unit_default"; 
												$result = mysqli_query($link, $query) or die(mysqli_error($link));
												$labels = array();

												if($result)
												{
													
													while($row = mysqli_fetch_assoc($result)) 
     											   { 
													$label =$row["value_unit_default"];
													echo "<option value='$label'>$label</option>";
												   }

												}
												else
												{
												   
													$label="ERROR";
													echo "<option value='$label'>$label</option>";
												}
											?>
										</select>							
                                    </div>
                                    <div class="modalFieldLabelCnt">Value Unit</div>
									<div id="selectValueUnitMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
								
								<!--  MM 0105 -->
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <select id="selectHealthinessCriteria" name="selectHealthinessCriteria" class="modalInputTxt">
											<option value="refresh_rate">Refresh Rate</option>
											<option value="different_values">Different Value</option>
											<option value="value_bounds">Value Bounds</option>
										</select>
                                    </div>
                                    <div class="modalFieldLabelCnt">Healthiness Criteria</div>
									<div id="selectHealthinessCriteriaMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
								
								<div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputHealthinessValue" id="inputHealthinessValue"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Healthiness Value</div>
									<div id="inputHealthinessValueMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
								
								<!-- <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputOrder" id="inputOrder"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Order</div>
									<div id="inputOrderMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div> -->
								
                           </div>
                        </div>
                     
                    </div>
					   
                    <div class="row" id="addValueLoadingMsg">
                        <div class="col-xs-12 centerWithFlex">Adding device, please wait</div>
                    </div>
                    <div class="row" id="addValueLoadingIcon">
                        <div class="col-xs-12 centerWithFlex"><i class="fa fa-circle-o-notch fa-spin" style="font-size:36px;"></i></div>
                    </div>
                    <div class="row" id="addValueOkMsg">
                        <div class="col-xs-12 centerWithFlex">Device added successfully</div>
                    </div>
                    <div class="row" id="addValueOkIcon">
                        <div class="col-xs-12 centerWithFlex"><i class="fa fa-thumbs-o-up" style="font-size:36px"></i></div>
                    </div>
                    <div class="row" id="addValueKoMsg">
                        <div class="col-xs-12 centerWithFlex">Error adding device</div>
                    </div>
                    <div class="row" id="addValueKoIcon">
                        <div class="col-xs-12 centerWithFlex"><i class="fa fa-thumbs-o-down" style="font-size:36px"></i></div>
                    </div>
				</div> 	
		       
         		<div id="addValueModalFooter" class="modal-footer">
                  <button type="button" id="addNewValueCancelBtn" class="btn cancelBtn" data-dismiss="modal">Cancel</button>
                  <button type="submit" id="addNewValueConfirmBtn" name="addNewValueConfirmBtn" class="btn confirmBtn internalLink">Confirm</button>	  
				</div>
				
			 <!-- </form> -->	 
	
              </div>
            </div>
        </div>

        <!-- Value Insertion Success Notification -->
        <div class="modal fade" id="addDeviceOkModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modalHeader centerWithFlex">
                  Add new value
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
        
        <!-- Value Insertion Failure Notification -->
        <div class="modal fade" id="addValueKoModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modalHeader centerWithFlex">
                  Add new value
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
                  <button type="button" id="addValueKoBackBtn" class="btn cancelBtn">Go back to new user form</button>
                  <button type="button" id="addValueKoConfirmBtn" class="btn confirmBtn">Go back to users page</button>
                </div>
              </div>
            </div>
        </div>

        <!-- Modify the Value -->
        <div class="modal fade" id="editValueModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div id="editValueModalLabel" class="modalHeader centerWithFlex">
                  Update Value
                </div>
               
				<form id="editValueForm" name="editValueForm" role="form" method="post" action="" data-toggle="validator">
                <div id="editValueModalBody" class="modal-body modalBody">
                    
                     <ul id="editValueModalTabs" class="nav nav-tabs nav-justified">
						<li class="active"><a data-toggle="tab" href="#editInfoTabValue">Info</a></li>
                        
                    </ul>
                    
                    <div class="tab-content">
					
					 <!-- Value Edit Tab -->
                        <div id="editInfoTabValue" class="tab-pane fade in active">
                            <div class="row">
							
								 <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" id="selectContextBrokerM" name="selectContextBrokerM" class="modalInputTxt" readonly>
									</div>
                                    <div class="modalFieldLabelCnt">ContextBroker</div>
									<div id="selectContextBrokerMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
								
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputNameDeviceM" id="inputNameDeviceM" readonly> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Device</div>
									<div id="inputNameDeviceMMsg" class="modalFieldMsgCnt" >&nbsp;</div>
                                </div>
								
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputValueNameDeviceM" id="inputValueNameDeviceM" readonly> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Value Name</div>
									<div id="inputValueNameDeviceMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
								
								<div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <select id="selectDataTypeM" name="selectDataTypeM" class="modalInputTxt">
											<?php
												$query = "SELECT data_type FROM data_types order by data_type";
													 $result = mysqli_query($link, $query) or die(mysqli_error($link));
														if($result){
															while($row = mysqli_fetch_assoc($result)) 
																{
																	$label=$row["data_type"];
																	echo "<option value='$label'>$label</option>";		
																}
													 }
													 
											 ?>
										</select>
                                    </div>
                                    <div class="modalFieldLabelCnt">Data Type</div>
									<div id="selectDataTypeMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
								
								 <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <select id="selectValueTypeM" name="selectValueTypeM" class="modalInputTxt">
											<?php
												$query = "SELECT value_type FROM value_types ORDER BY value_type";
												$result = mysqli_query($link, $query);

												if($result)
												{
												   while($row = $result->fetch_assoc())
												   { 
													 $label=$row["value_type"];
													 echo "<option value='$label'>$label</option>";
												   }

												}
												else
												{
												   
													$label="ERROR";
													echo "<option value='$label'>$label</option>";
												}
											?>
										</select>
                                    </div>
                                    <div class="modalFieldLabelCnt">Value Type</div>
									<div id="selectValueTypeMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
								
									<div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <select id="selectValueUnitM" name="selectValueUnitM" class="modalInputTxt">
											<?php
												$query = "SELECT DISTINCT value_unit_default FROM value_types ORDER BY value_unit_default"; 
												$result = mysqli_query($link, $query) or die(mysqli_error($link));
												$labels = array();

												if($result)
												{
													
													while($row = mysqli_fetch_assoc($result)) 
     											   { 
													$label =$row["value_unit_default"];
													echo "<option value='$label'>$label</option>";
												   }

												}
												else
												{
												   
													$label="ERROR";
													echo "<option value='$label'>$label</option>";
												}
											?>
										</select>							
                                    </div>
                                    <div class="modalFieldLabelCnt">Value Unit</div>
									<div id="selectValueUnitMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
								
								<div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputEditableValueM" id="inputEditableValueM"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Editable</div>
									<div id="inputEditableValueMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
								
								<!--  MM 0105 -->
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <select id="selectHealthinessCriteriaM" name="selectHealthinessCriteriaM" class="modalInputTxt">
											<option value="refresh_rate">Refresh Rate</option>
											<option value="different_values">Different Value</option>
											<option value="value_bounds">Value Bounds</option>
										</select>
                                    </div>
                                    <div class="modalFieldLabelCnt">Healthiness Criteria</div>
									<div id="selectHealthinessCriteriaMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
								
								<div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputHealthinessValueM" id="inputHealthinessValueM"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Healthiness Value</div>
									<div id="inputHealthinessValueMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
					
								
                           </div>
                        </div>
                       
                      
                    </div>
					
					
					<div class="row" id="editValueLoadingMsg">
                        <div class="col-xs-12 centerWithFlex">Updating value, please wait</div>
                    </div>
                    <div class="row" id="editValueLoadingIcon">
                        <div class="col-xs-12 centerWithFlex"><i class="fa fa-circle-o-notch fa-spin" style="font-size:36px;"></i></div>
                    </div>
                    <div class="row" id="editValueOkMsg">
                        <div class="col-xs-12 centerWithFlex">Value updated successfully</div>
                    </div>
                    <div class="row" id="editValueOkIcon">
                        <div class="col-xs-12 centerWithFlex"><i class="fa fa-thumbs-o-up" style="font-size:36px"></i></div>
                    </div>
                    <div class="row" id="editValueKoMsg">
                        <div class="col-xs-12 centerWithFlex">Error updating value</div>
                    </div>
                    <div class="row" id="editValueKoIcon">
                        <div class="col-xs-12 centerWithFlex"><i class="fa fa-thumbs-o-down" style="font-size:36px"></i></div>
                    </div>
		            
                </div>
				<div id="editValueModalFooter" class="modal-footer">
                  <button type="button" id="editValueCancelBtn" class="btn cancelBtn" data-dismiss="modal">Cancel</button>
                  <button type="button" id="editValueConfirmBtn" class="btn confirmBtn internalLink" >Confirm</button>
                </div>
				
				</form> 	
                
              </div>
            </div>
        </div>
        
        <!-- Edit Success  -->
        <div class="modal fade" id="editValueOkModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modalHeader centerWithFlex">
                  Update value
                </div>
                <div class="modal-body modalBody">
                    <div class="row">
                        <div class="col-xs-12 modalCell">
                            <div id="editValueOkModalInnerDiv1" class="modalDelMsg col-xs-12 centerWithFlex">
                                
                            </div>
                            <div class="modalDelObjName col-xs-12 centerWithFlex" id="editValueOkModalInnerDiv2"><i class="fa fa-check" style="font-size:36px"></i></div> 
                        </div>
                    </div>
                </div>
                <!--<div class="modal-footer">
                  
                </div>-->
              </div>
            </div>
        </div>
        
        <!-- Fail -->
        <div class="modal fade" id="editValueKoModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modalHeader centerWithFlex">
                  Update value
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
                  <button type="button" id="editValueKoBackBtn" class="btn cancelBtn">Go back to edit account form</button>
                  <button type="button" id="editValueKoConfirmBtn" class="btn confirmBtn">Go back to users page</button>
                </div>
              </div>
            </div>
        </div>
		
		
			<div class="modal fade" id="addMap" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modalHeader centerWithFlex">
					Sensor/Actuator Location on Map
                </div>
				<div class="form-row iot-directory-form-row">
				        <link rel="stylesheet" href="../css/leaflet.css" />
						<link rel="stylesheet" href="../css/leaflet.draw.css" />
						<div id="addDeviceMapModalBody" style="width: 100%; height: 400px" class="modal-body modalBody">
	 			</div>
				</div> 
              </div>
            </div>
        </div>

	<div class="modal fade" id="addMap1SA" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modalHeader centerWithFlex">
					Search Sensor/Actuator Location on Map
                </div>
				<div class="form-row iot-directory-form-row">
				        <link rel="stylesheet" href="../css/leaflet.css" />
						<link rel="stylesheet" href="../css/leaflet.draw.css" />
						<div id="searchDeviceMapModalBodySA" style="width: 100%; height: 400px" class="modal-body modalBody">
			
                  
					</div>
				</div> 
              </div>
            </div>
        </div>		
		
        
    </body>
</html>

