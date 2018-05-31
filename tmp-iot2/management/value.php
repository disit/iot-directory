<?php

/* Dashboard Builder.
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
    include('process-form.php');
	
    session_start();
	
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
		
		
		<!-- leaflet scripts -->
		
		<script type="text/javascript" src="../js/leaflet.js"></script>
		<script type="text/javascript" src="../js/leaflet.draw.js"></script>
		<script type="text/javascript" src="../js/jquery.fancytree-all.min.js"></script>
        
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
        <script type="text/javascript" src="../js/dashboard_mng.js"></script>
		
		<!-- Custom scripts -->
        <script type="text/javascript" src="../js/devicesManagement.js"></script>
		
		<!-- leaflet scripts -->
		
		<script type="text/javascript" src="../js/leaflet.js"></script>
		<script type="text/javascript" src="../js/leaflet.draw.js"></script>
		<script type="text/javascript" src="../js/jquery.fancytree-all.min.js"></script>
		
		<!-- Custom scripts -->
        <script type="text/javascript" src="../js/devicesEditManagement.js"></script>
        
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
                    <div class="row">
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
                                    <div class="col-md-12 centerWithFlex pageSingleDataLabel">
                                        Sensors and Actuators 
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
        
		
				<form id="addValueForm" name="addValueForm" role="form" method="post" action="" data-toggle="validator">
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
                                        <input type="text" class="modalInputTxt" name="inputEditableValue" id="inputEditableValue"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Editable</div>
									<div id="inputEditableValueMsg" class="modalFieldMsgCnt">&nbsp;</div>
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
								
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <select id="selectHealthinessCriteria" name="selectHealthinessCriteria" class="modalInputTxt">
											<option value="value_refresh_rate">Value Refresh Rate</option>
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
								
								<div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputOrder" id="inputOrder"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Order</div>
									<div id="inputOrderMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
								
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
				
			</form>	 
	
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
                                        <select id="selectContextBrokerM" name="selectContextBrokerM" class="modalInputTxt">

                                       <?php
                                            $query = "SELECT name, protocol FROM contextbroker";
                                            $result = mysqli_query($link, $query);

                                            if($result)
                                            {
                                               while($row = $result->fetch_assoc())
                                               { 
                                                 $nameCB=$row["name"];
												 $protocol=$row["protocol"];
                                                 echo "<option my_data='$protocol' value='$nameCB'>$nameCB</option>";
                                               }

                                            }
                                            else
                                            {
                                               
                                                $nameCB="ERROR";
                                                echo "<option value='$nameCB'>$nameCB</option>";
                                            }
                                        ?>
										</select>
                                    </div>
                                    <div class="modalFieldLabelCnt">ContextBroker</div>
									<div id="selectContextBrokerMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
								
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputNameDeviceM" id="inputNameDeviceM" required> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Device</div>
									<div id="inputNameDeviceMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
								
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputValueNameDeviceM" id="inputValueNameDeviceM"> 
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
                                        <input type="text" class="modalInputTxt" name="inputEditableValueM" id="inputEditableValueM"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Editable</div>
									<div id="inputEditableValueMMsg" class="modalFieldMsgCnt">&nbsp;</div>
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
                                        <select id="selectHealthinessCriteriaM" name="selectHealthinessCriteriaM" class="modalInputTxt">
											<option value="value_refresh_rate">Value Refresh Rate</option>
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
								
								<div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputOrderM" id="inputOrderM"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Order</div>
									<div id="inputOrderMMsg" class="modalFieldMsgCnt">&nbsp;</div>
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
        
        <!-- Modale di notifica edit account utente avvenuto con successo -->
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
        
        <!-- Modale di notifica edit account utente fallito -->
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
					Device Location on Map
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
                location.href = "logout.php?sessionExpired=true";
            }
        }, 1000);
        
        $('#mainContentCnt').height($('#mainMenuCnt').height() - $('#headerTitleCnt').height());
        
        $(window).resize(function(){
            $('#mainContentCnt').height($('#mainMenuCnt').height() - $('#headerTitleCnt').height());
            if($(window).width() < 992)
            {
                $('#valuesTable').bootstrapTable('hideColumn', 'cb');
                $('#valuesTable').bootstrapTable('hideColumn', 'device');
                $('#valuesTable').bootstrapTable('hideColumn', 'value_name');
                $('#valuesTable').bootstrapTable('hideColumn', 'value_type');
                $('#valuesTable').bootstrapTable('hideColumn', 'healthiness_criteria');
                $('#valuesTable').bootstrapTable('hideColumn', 'value_refresh_rate');
               
            }
            else
            {
                $('#valuesTable').bootstrapTable('showColumn', 'cb');
                $('#valuesTable').bootstrapTable('showColumn', 'device');
                $('#valuesTable').bootstrapTable('showColumn', 'value_name');
                $('#valuesTable').bootstrapTable('showColumn', 'value_type');
                $('#valuesTable').bootstrapTable('showColumn', 'healthiness_criteria');
                $('#valuesTable').bootstrapTable('showColumn', 'value_refresh_rate');
    		
            }
        });
        
        $('#valueLink .mainMenuItemCnt').addClass("mainMenuItemCntActive");
        $('#mobMainMenuPortraitCnt #valueLink .mobMainMenuItemCnt').addClass("mainMenuItemCntActive");
        $('#mobMainMenuLandCnt #valueLink .mobMainMenuItemCnt').addClass("mainMenuItemCntActive");
        
        var admin = "<?= $_SESSION['loggedRole'] ?>";
        var existingPoolsJson = null;
        var internalDest = false;
        var tableFirstLoad = true;
        
        buildMainTable(false);
        setGlobals(admin, existingPoolsJson);
        
		
	

        /*   ADD NEW VALUE CONFIRMATION */
		
		/*
        $('#addNewValueConfirmBtn').off("click");
        $("#addNewValueConfirmBtn").click(function(){
	        $("#addValueModalTabs").hide();
			$('#addValueModal div.modalCell').hide();
            $("#addValueModalFooter").hide();
            $('#addValueLoadingMsg').show();
            $('#addValueLoadingIcon').show();

             $.ajax({
                 url: "process-form.php",
                 data:{
					  addValue: true,
					  
					  selectContextBroker: $('#selectContextBroker').val(),
		 			  inputNameDevice: $('#inputNameDevice').val(),
					  inputValueNameDevice: $('#inputValueNameDevice').val(),
					  selectDataType: $('#selectDataType').val(),
					  selectValueType: $('#selectValueType').val(),
					  inputEditableValue: $('#inputEditableValue').val(),
					  selectValueUnit: $('#selectValueUnit').val(),
					  selectHealthinessCriteria: $('#selectHealthinessCriteria').val(),
					  inputHealthinessValue: $('#inputHealthinessValue').val(),
					  inputOrder: $('#inputOrder').val()					 
					  },
                 type: "POST",
                 async: true,
				
                 success: function (data) 
                 {
					console.log("Elf result: " + data);
				 
					if(data.endsWith('Ko'))
                    {
                        console.log("Error adding value");
                        console.log(data);
						$('#addValueLoadingMsg').hide();
                        $('#addValueLoadingIcon').hide();
                        $('#addValueKoMsg').show();
                        $('#addValueKoIcon').show();
                      
                        setTimeout(function(){
                            $('#addValueKoMsg').hide();
                            $('#addValueKoIcon').hide();
                            $('#addValueModalTabs').show();
                            $('#addValueModal div.modalCell').show();
                            $('#addValueModalFooter').show();
                        }, 3000);
                    }			 
					else if (data.endsWith('Ok'))
                    {
						
						
						$('#addValueLoadingMsg').hide();
                        $('#addValueLoadingIcon').hide();
                        $('#addValueKoMsg').hide();
                        $('#addValueKoIcon').hide();
                        $('#addValueOkMsg').show();
                        $('#addValueOkIcon').show();
                      
	                 
					setTimeout(function(){
                            $('#addValueModal').modal('hide');
                            buildMainTable(true);

                            setTimeout(function(){
								  $('#addValueOkMsg').hide();
                                  $('#addValueOkIcon').hide();
								  
								  $('#selectContextBroker').val("");
								  $('#inputNameDevice').val("");
								  $('#inputValueNameDevice').val("");
								  $('#selectDataType').val("");
								  $('#selectValueType').val("");
								  $('#inputEditableValue').val("");
								  $('#selectValueUnit').val("");
								  $('#selectHealthinessCriteria').val("");
								  $('#inputHealthinessValue').val("");
								  $('#inputOrder').val();		
							
																
								  $('#addValueModalTabs').show();
                                  $('#addValueModal div.modalCell').show();
                                  $('#addValueModalFooter').show();
                            }, 500);
                        }, 3000);
						
				
                    } else {console.log("success with error " + data);}
					 
                 },
                 error: function (data) 
                 {
                     console.log("Error status -- Ko result: " +  data);

                        $('#addValueLoadingMsg').hide();
                        $('#addValueLoadingIcon').hide();
                        $('#addValueKoMsg').show();
                        $('#addValueKoIcon').show();
                        setTimeout(function(){
                            $('#addValueKoMsg').hide();
                            $('#addValueKoIcon').hide();
                            $('#addValueModalTabs').show();
                            $('#addValueModal div.modalCell').show();
                            $('#addValueModalFooter').show();
                        }, 3000);
                 }
             });
        });
        
		*/
		
		
        /*   DELETE VALUE CONFIRMATION */
		
		/*
        $('#deleteValueConfirmBtn').off("click");
        $("#deleteValueConfirmBtn").click(function(){
		  
			var device = $("#deleteValueModal span").attr("data-device");
			var cb = $("#deleteValueModal span").attr("data-cb");
			var value_name   = $("#deleteValueModal span").attr("data-value_name");
            
			console.log(cb);
			
            $("#deleteValueModal div.modal-body").html("");
            $("#deleteValueCancelBtn").hide();
            $("#deleteValueConfirmBtn").hide();
            $("#deleteValueModal div.modal-body").append('<div id="deleteValueModalInnerDiv1" class="modalBodyInnerDiv"><h5>Value deletion in progress, please wait</h5></div>');
            $("#deleteValueModal div.modal-body").append('<div id="deleteValueModalInnerDiv2" class="modalBodyInnerDiv"><i class="fa fa-circle-o-notch fa-spin" style="font-size:36px"></i></div>');
			
            // Delete value
            $.ajax({
                url: "process-form.php",
				data:{	
						deleteValue: true,
						device: device, 
						cb: cb, 
						value_name: value_name 			
						},
                type: "POST",
                async: true,
				
                success: function (data) 
                {
					console.log(data);
                    if(data === '0')
                    {
                        $("#deleteValueModalInnerDiv1").html('Value &nbsp; <b>' + value_name + '</b> &nbsp; deletion failed, please try again');
                        $("#deleteValueModalInnerDiv2").html('<i class="fa fa-frown-o" style="font-size:42px"></i>');
                    }
                    else if( data === '1')
                    {
                        $("#deleteValueModalInnerDiv1").html('Value &nbsp; <b>' + value_name + '</b> &nbsp;deleted successfully');
                        $("#deleteValueModalInnerDiv2").html('<i class="fa fa-check" style="font-size:42px"></i>');
						
						$('#dashboardTotNumberCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotNumberCnt .pageSingleDataCnt').html()) - 1);
                        $('#dashboardTotActiveCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotActiveCnt .pageSingleDataCnt').html()) - 1);
                        setTimeout(function()
                        {
                            buildMainTable(true);
                            $("#deleteValueModal").modal('hide');
                            setTimeout(function(){
                                $("#deleteValueCancelBtn").show();
                                $("#deleteValueConfirmBtn").show();
                            }, 500);
                        }, 2000);
                    }
                },
                error: function (data) 
                {
                    $("#deleteValueModalInnerDiv1").html('Value &nbsp; <b>' + value_name + '</b> &nbsp; deletion failed, please try again');
                    $("#deleteValueModalInnerDiv2").html('<i class="fa fa-frown-o" style="font-size:42px"></i>');
                }
            });
        });
		
		*/
		
		
        /*  EDIT VALUE CONFIRMATION */
		
		
		/*
        
        $('#editValueConfirmBtn').off("click");
        $("#editValueConfirmBtn").click(function(){
     
			
			$("#editValueModalTabs").hide();
			$('#editValueModal div.modalCell').hide();
            $("#editValueModalFooter").hide();
            $('#editValueLoadingMsg').show();
            $('#editValueLoadingIcon').show();
          

             // Edit Value
             $.ajax({
                 url: "process-form.php",
                 data:{
				  updateValue: true, 
				  selectContextBrokerM: $('#selectContextBrokerM').val(),
			      inputNameDeviceM: $('#inputNameDeviceM').val(),
				  inputValueNameDeviceM: $('#inputValueNameDeviceM').val(),
				  selectDataTypeM: $('#selectDataTypeM').val(),
				  selectValueTypeM: $('#selectValueTypeM').val(),
				  inputEditableValueM: $('#inputEditableValueM').val(),
				  selectValueUnitM: $('#selectValueUnitM').val(),
				  selectHealthinessCriteriaM: $('#selectHealthinessCriteriaM').val(),
				  inputHealthinessValueM: $('#inputHealthinessValueM').val(),
				  inputOrderM: $('#inputOrderM').val()
				 },
                 type: "POST",
                 async: true,
                 success: function (data) 
                 {
					if(data.endsWith('Ko'))
                    {
                        console.log("Error editing Device type");
                        console.log(data);
						$('#editValueLoadingMsg').hide();
                        $('#editValueLoadingIcon').hide();
                        $('#editValueLoadingIcon').hide();
                        $('#editValueKoMsg').show();
                        $('#editValueKoIcon').show();
                      
                        setTimeout(function(){
                            $('#editValueKoMsg').hide();
                            $('#editValueKoIcon').hide();
                            $('#editValueModalTabs').show();
                            $('#editValueModal div.modalCell').show();
                            $('#editValueModalFooter').show();
                        }, 3000);
                    }
					 
					else if (data.endsWith('Ok'))
                    {
							
						$('#editValueLoadingMsg').hide();
                        $('#editValueLoadingIcon').hide();
                        $('#editValueOkMsg').show();
                        $('#editValueOkIcon').show();
                        			
						$("#addingValueModalInnerDiv1").html('Value &nbsp; <b>' + value_name + '</b> &nbsp;edited successfully');
                        $("#addingValueModalInnerDiv2").html('<i class="fa fa-check" style="font-size:42px"></i>');
	                 
						setTimeout(function(){
                            $('#editValueModal').modal('hide');
                            buildMainTable(true);

                            setTimeout(function(){
                             
								  $('#editValueOkMsg').hide();
                                  $('#editValueOkIcon').hide();
								  
								  $('#selectContextBrokerM').val("");
								  $('#inputNameDeviceM').val("");
								  $('#inputValueNameDeviceM').val("");
								  $('#selectDataTypeM').val("");
								  $('#selectValueTypeM').val("");
								  $('#inputEditableValueM').val("");
								  $('#selectValueUnitM').val("");
								  $('#selectHealthinessCriteriaM').val("");
								  $('#inputHealthinessValueM').val("");
								  $('#inputOrderM').val();		
							
								  
                                   $('#editValueModal').hide();
							       setTimeout(updateDeviceTimeout, 100);	  
								  
                            }, 100);
                        }, 100);
						
						
			} else {console.log(data);}
					 
                 },
                 error: function (data) 
                 {
                     console.log("Ko result: " + data);
                     $("#editUserModal").modal('hide');
                     $("#editUserKoModalInnerDiv1").html('<h5>Account <b>' + accountJson.username + '</b> couldn\'t be updated because of an API call failure, please try again</h5>');
                     $("#editUserKoModal").modal('show');
                     $("#editUserModalUpdating").hide();
                     $("#editUserModalBody").show();
                     $("#editUserModalFooter").show();
                 }
             });
        });
		
		*/
        
        $("#addNewValueCancelBtn").off("click");
        $("#addNewValueCancelBtn").on('click', function(){
								  
			  $('#selectContextBroker').val("");
			  $('#inputNameDevice').val("");
			  $('#inputValueNameDevice').val("");
			  $('#selectDataType').val("");
			  $('#selectValueType').val("");
			  $('#inputEditableValue').val("");
			  $('#selectValueUnit').val("");
			  $('#selectHealthinessCriteria').val("");
			  $('#inputHealthinessValue').val("");
			  $('#inputOrder').val();		
								  
			  $('#addValueModal').modal('hide'); 						
			  
				location.reload();    								  
								
        });
        
        $("#addValueKoBackBtn").off("click");
        $("#addValueKoBackBtn").on('click', function(){
            $("#addValueKoModal").modal('hide');
            $("#addValueModal").modal('show');
        });
        
        $("#addValueKoConfirmBtn").off("click");
        $("#addValueKoConfirmBtn").on('click', function(){
            $("#addValueKoModal").modal('hide');
            $("#addValueForm").trigger("reset");   
        });
        
        $("#editValueKoBackBtn").off("click");
        $("#editValueKoBackBtn").on('click', function(){
            $("#editValueKoModal").modal('hide');
            $("#editValueModal").modal('show');
        });
        
        $("#editValueKoConfirmBtn").off("click");
        $("#editValueKoConfirmBtn").on('click', function(){
            $("#editValueKoModal").modal('hide');
            $("#editValueForm").trigger("reset");
        });
        
 
	   
        function updateDeviceTimeout()
        {
            $("#editValueOkModal").modal('hide');
            setTimeout(function(){
               location.reload();
            }, 500);
        }
        
        function buildMainTable(destroyOld)
        {
            if(destroyOld)
            {
                $('#valuesTable').bootstrapTable('destroy');
                tableFirstLoad = true;
            }
            
            var accountVisibile = true;
            var statusVisible = true;
            

            if($(window).width() < 992)
            {
                accountVisibile = false;
                statusVisible = false; 
                
            }
            

            $.ajax({
                url: "get_data.php",
                data: {action: "getEventValues"},
                type: "GET",
                async: true,
                datatype: 'json',
                success: function (data)
                {
					
					var creatorVisibile = true;
                    var detailView = true;
                    var statusVisibile = true;
                    // console.log("builtMainTable" + JSON.stringify(data));                    

                    if($(window).width() < 992)
                    {
                        detailView = false;
                        creatorVisibile = false; 
                        statusVisibile = false;
                    }
					
					
                    $('#valuesTable').bootstrapTable({
                            columns: [{
									field: 'cb',
									title: 'Context Broker',
									filterControl: 'select',
									sortable: true,
									valign: "middle",
									align: "center",
									halign: "center",
									formatter: function(value, row, index)
                                {
                                    var maxL = 50;
                                    if($(window).width() < 992)
                                    {
                                        maxL = 15;
                                    }
                                    
                                    if(value !== null)
                                    {
                                        if(value.length > maxL)
                                        {
                                           return value.substr(0, maxL) + " ...";
                                        }
                                        else
                                        {
                                           return value;
                                        } 
                                    }
                                },
                                cellStyle: function(value, row, index, field) {
                                    var fontSize = "1em"; 
                                    if($(window).width() < 992)
                                    {
                                        fontSize = "0.9em";
                                    }
                                    
                                    
                                    if(index%2 !== 0)
                                    {
                                        return {
                                            classes: null,
                                            css: {
                                                "color": "rgba(51, 64, 69, 1)", 
                                                "font-size": fontSize,
                                                "font-weight": "bold",
                                                "background-color": "rgb(230, 249, 255)",
                                                "border-top": "none"
                                            }
                                        };
                                    }
                                    else
                                    {
                                        return {
                                            classes: null,
                                            css: {
                                                "color": "rgba(51, 64, 69, 1)", 
                                                "font-size": fontSize,
                                                "font-weight": "bold",
                                                "background-color": "white",
                                                "border-top": "none"
                                            }
                                        };
                                    }
                                }
                            }, 
                            {
                                field: 'device',
								title: 'Device',
								filterControl: 'input',
								sortable: true,
								valign: "middle",
								align: "center",
								halign: "center",
								visible: creatorVisibile,
								formatter: function(value, row, index)
                                {
                                    if(value !== null)
									{
										if(value.length > 50)
										{
										   return value.substr(0, 50) + " ...";
										}
										else
										{
										   return value;
										} 
									}
                                },
                                cellStyle: function(value, row, index, field) {
                                    if(index%2 !== 0)
                                    {
                                        return {
                                            classes: null,
                                            css: {
                                                "background-color": "rgb(230, 249, 255)",
                                                "border-top": "none"
                                            }
                                        };
                                    }
                                    else
                                    {
                                        return {
                                            classes: null,
                                            css: {
                                                "background-color": "white",
                                                "border-top": "none"
                                            }
                                        };
                                    }
                                }
                            },
							{
                                field: 'value_name',
								title: 'Value Name',
								filterControl: 'input',
								sortable: true,
								valign: "middle",
								align: "center",
								halign: "center",
								visible: creatorVisibile,
								formatter: function(value, row, index)
                                {
                                    if(value !== null)
                                    {
                                        if(value.length > 50)
                                        {
                                           return value.substr(0, 50) + " ...";
                                        }
                                        else
                                        {
                                           return value;
                                        } 
                                    }
                                },
                                cellStyle: function(value, row, index, field) {
                                    if(index%2 !== 0)
                                    {
                                        return {
                                            classes: null,
                                            css: {
                                                "background-color": "rgb(230, 249, 255)",
                                                "border-top": "none"
                                            }
                                        };
                                    }
                                    else
                                    {
                                        return {
                                            classes: null,
                                            css: {
                                                "background-color": "white",
                                                "border-top": "none"
                                            }
                                        };
                                    }
                                }
                            },
                            {
                                field: 'value_type',
								title: 'Value Type',
								filterControl: 'select',
								sortable: true,
								valign: "middle",
								align: "center",
								halign: "center",
								visible: creatorVisibile,
								formatter: function(value, row, index)
                                {
                                    if(value !== null)
                                    {
                                        if(value.length > 50)
                                        {
                                           return value.substr(0, 50) + " ...";
                                        }
                                        else
                                        {
                                           return value;
                                        } 
                                    }
                                },
                                cellStyle: function(value, row, index, field) {
                                    if(index%2 !== 0)
                                    {
                                        return {
                                            classes: null,
                                            css: {
                                                "background-color": "rgb(230, 249, 255)",
                                                "border-top": "none"
                                            }
                                        };
                                    }
                                    else
                                    {
                                        return {
                                            classes: null,
                                            css: {
                                                "background-color": "white",
                                                "border-top": "none"
                                            }
                                        };
                                    }
                                }
                            },
                            {
                                field: 'healthiness_criteria',
								title: 'Healthiness Criteria',
								filterControl: 'select',
								sortable: true,
								valign: "middle",
								align: "center",
								halign: "center",
								visible: creatorVisibile,
								formatter: function(value, row, index)
                                {
                                    if(value !== null)
                                    {
                                        if(value.length > 50)
                                        {
                                           return value.substr(0, 50) + " ...";
                                        }
                                        else
                                        {
                                           return value;
                                        } 
                                    }
                                },
                                cellStyle: function(value, row, index, field) {
                                    if(index%2 !== 0)
                                    {
                                        return {
                                            classes: null,
                                            css: {
                                                "background-color": "rgb(230, 249, 255)",
                                                "border-top": "none"
                                            }
                                        };
                                    }
                                    else
                                    {
                                        return {
                                            classes: null,
                                            css: {
                                                "background-color": "white",
                                                "border-top": "none"
                                            }
                                        };
                                    }
                                }
                            },
							
							{
                                field: 'value_refresh_rate',
								title: 'Refresh Rate',
								filterControl: 'input',
								sortable: true,
								valign: "middle",
								align: "center",
								halign: "center",
								visible: creatorVisibile,
								formatter: function(value, row, index)
                                {
                                    if(value !== null)
                                    {
                                        if(value.length > 50)
                                        {
                                           return value.substr(0, 50) + " ...";
                                        }
                                        else
                                        {
                                           return value;
                                        } 
                                    }
                                },
                                cellStyle: function(value, row, index, field) {
                                    if(index%2 !== 0)
                                    {
                                        return {
                                            classes: null,
                                            css: {
                                                "background-color": "rgb(230, 249, 255)",
                                                "border-top": "none"
                                            }
                                        };
                                    }
                                    else
                                    {
                                        return {
                                            classes: null,
                                            css: {
                                                "background-color": "white",
                                                "border-top": "none"
                                            }
                                        };
                                    }
                                }
                            },
							
                            {
                                title: "",
                                align: "center",
                                valign: "middle",
                                align: "center",
                                halign: "center",
                                formatter: function(value, row, index)
                                { 
                                   //MM15 return '<button type="button" class="editDashBtn">edit</button>';
                                },
								cellStyle: function(value, row, index, field) {
                                    if(index%2 !== 0)
                                    {
                                        return {
                                            classes: null,
                                            css: {
                                                "background-color": "rgb(230, 249, 255)",
                                                "border-top": "none"
                                            }
                                        };
                                    }
                                    else
                                    {
                                        return {
                                            classes: null,
                                            css: {
                                                "background-color": "white",
                                                "border-top": "none"
                                            }
                                        };
                                    }
                                }
                            },
                            {
                                title: "",
                                align: "center",
                                valign: "middle",
                                align: "center",
                                halign: "center",
                                formatter: function(value, row, index)
                                {
                                    
                                   //MM return '<button type="button" class="delDashBtn">del</button>';
                                },
                                cellStyle: function(value, row, index, field) {
                                    if(index%2 !== 0)
                                    {
                                        return {
                                            classes: null,
                                            css: {
                                                "background-color": "rgb(230, 249, 255)",
                                                "border-top": "none"
                                            }
                                        };
                                    }
                                    else
                                    {
                                        return {
                                            classes: null,
                                            css: {
                                                "background-color": "white",
                                                "border-top": "none"
                                            }
                                        };
                                    }
                                }        
                            },
							{
                                title: "",
                                align: "center",
                                valign: "middle",
                                align: "center",
                                halign: "center",
                                formatter: function(value, row, index)
                                {
                                    return '<i id="addMapBtn" data-toggle="modal" data-target="#addMap" class="fa fa-globe" onclick="getLatLong(\''+ row.device + '\')\" style=\"font-size:36px; color: #0000ff\"></i>';
                                },
                                cellStyle: function(value, row, index, field) {
                                    if(index%2 !== 0)
                                    {
                                        return {
                                            classes: null,
                                            css: {
                                                "background-color": "rgb(230, 249, 255)",
                                                "border-top": "none",
												
                                            }
                                        };
                                    }
                                    else
                                    {
                                        return {
                                            classes: null,
                                            css: {
                                                "background-color": "white",
                                                "border-top": "none"
                                            }
                                        };
                                    }
                                }        
                            }							
							
							],
                            data: data,
                            search: true,
                            pagination: true,
                            pageSize: 10,
							filterControl: true,
                            locale: 'en-US',
                            searchAlign: 'left',
                            //uniqueId: "name",
                            striped: false,
                            searchTimeOut: 250,
                            classes: "table table-hover table-no-bordered",
							detailView: detailView,
							detailFormatter: function(index, row, element) {
                            return 'Data Type: ' + data[index].data_type   + ' | Editable: ' + data[index].editable + ' | Different Value: ' + data[index].different_values + " | Value Bound: " + data[index].value_bounds + " | Order: " + data[index].order;
							},
                            rowAttributes: function(row, index){
                            return {
                                "data-cb": row.cb,
                                "data-device": row.device,
                                "data-value_name": row.value_name,
                                "data-data_type": row.data_type,
                                "data-value_type": row.value_type,
                                "data-editable": row.editable,
                                "data-value_unit": row.value_unit,
                                "data-healthiness_criteria": row.healthiness_criteria,
                                "data-value_refresh_rate": row.value_refresh_rate,
								"data-different_values": row.different_values,
                                "data-value_bounds": row.value_bounds,
                                "data-order": row.order,
								"data-latitude": row.device.latitude,
                              };},
                            onPostBody: function()
                            {
                                if(tableFirstLoad)
                                {
                                    //Caso di primo caricamento della tabella
                                    tableFirstLoad = false;
                                    var addDeviceDiv = $('<div class="pull-right"><i id="addDeviceBtn" data-toggle="modal" data-target="#addValueModal" class="fa fa-plus-square" style="font-size:36px; color: #ffcc00"></i></div>');
                                    
                                    //MM15 $('div.fixed-table-toolbar').append(addDeviceDiv);
                                    addDeviceDiv.css("margin-top", "10px");
                                    addDeviceDiv.find('i.fa-plus-square').off('hover');
                                    addDeviceDiv.find('i.fa-plus-square').hover(function(){
                                        $(this).css('color', '#e37777');
                                        $(this).css('cursor', 'pointer');
                                    }, 
                                    function(){
                                        $(this).css('color', '#ffcc00');
                                        $(this).css('cursor', 'normal');
                                    });
									
									
				
									/* This is loading validation when the cursor is on */
								
									$("#addDeviceBtn").off("click");
                                    $("#addDeviceBtn").click(function(){
								
                                      $("#addValueModalBody").modal('show');
                                      $("#addValueLoadingMsg").hide();
                                      $("#addValueLoadingIcon").hide();
                                      $("#addValueOkMsg").hide();
                                      $("#addValueOkIcon").hide();
                                      $("#addValueKoMsg").hide();
                                      $("#addValueKoIcon").hide();
									  
									  /* will work on the validation  showAddDeviceModal(); */

                                   });
                                   
								
                                    $('#valuesTable thead').css("background", "rgba(0, 162, 211, 1)");
                                    $('#valuesTable thead').css("color", "white");
                                    $('#valuesTable thead').css("font-size", "1em");
                                }
                                else
                                {
                                    //other case
                                }

                               
								
								 $('#valuesTable tbody tr').each(function(i){
                                if(i%2 !== 0)
                                {
                                    $(this).find('td').eq(0).css("background-color", "rgb(230, 249, 255)");
                                    $(this).find('td').eq(0).css("border-top", "none");
                                }
                                else
                                {
                                    $(this).find('td').eq(0).css("background-color", "white");
                                    $(this).find('td').eq(0).css("border-top", "none");
                                }
                            });
                                $('#valuesTable').css("border-bottom", "none");
                                $('span.pagination-info').hide();

                                $('#valuesTable tbody button.editDashBtn').off('hover');
                                $('#valuesTable tbody button.editDashBtn').hover(function(){
                                    $(this).css('background', '#ffcc00');
                                    $(this).parents('tr').find('td').eq(1).css('background', '#ffcc00');
                                }, 
                                function(){
                                    $(this).css('background', 'rgb(69, 183, 175)');
                                    $(this).parents('tr').find('td').eq(1).css('background', $(this).parents('td').css('background'));
                                });

                                $('#valuesTable button.editDashBtn').off('click');
                               
                                $('#valuesTable button.editDashBtn').click(function(){
                                    // $("#editDeviceModalUpdating").hide();
									
									
									//******Edit Control function call
									
							/* must have it later		showEditDeviceModal(); */
									
									
									
                                    $("#editValueModalBody").show();


                                      $("#editValueLoadingMsg").hide();
                                      $("#editValueLoadingIcon").hide();
                                      $("#editValueOkMsg").hide();
                                      $("#editValueOkIcon").hide();
                                      $("#editValueKoMsg").hide();
                                      $("#editValueKoIcon").hide(); 
									  $("#editValueModalFooter").show();
									  $("#editValueModal").modal('show');
									  $("#editValueModalLabel").html("Edit value - " + $(this).parents('tr').attr("data-value_name"));
							
											
								  $('#selectContextBrokerM').val($(this).parents('tr').attr('data-cb'));
								  $('#inputNameDeviceM').val($(this).parents('tr').attr('data-device'));
								  $('#inputValueNameDeviceM').val($(this).parents('tr').attr('data-value_name'));
								  $('#selectDataTypeM').val($(this).parents('tr').attr('data-data_type'));
								  $('#selectValueTypeM').val($(this).parents('tr').attr('data-value_type'));
								  $('#inputEditableValueM').val($(this).parents('tr').attr('data-editable'));
								  $('#selectValueUnitM').val($(this).parents('tr').attr('data-value_unit'));
								  $('#selectHealthinessCriteriaM').val($(this).parents('tr').attr('data-healthiness_criteria'));
								  $('#inputHealthinessValueM').val($(this).parents('tr').attr('data-value_refresh_rate'));
								  $('#inputOrderM').val($(this).parents('tr').attr('data-order'));
					
                                });

                                $('#valuesTable button.delDashBtn').off('hover');
                                $('#valuesTable button.delDashBtn').hover(function(){
                                    $(this).css('background', '#ffcc00');
                                    $(this).parents('tr').find('td').eq(1).css('background', '#ffcc00');
                                }, 
                                function(){
                                    $(this).css('background', '#e37777');
                                    $(this).parents('tr').find('td').eq(1).css('background', $(this).parents('td').css('background'));
                                });

                                $('#valuesTable button.delDashBtn').off('click');
                                $('#valuesTable button.delDashBtn').click(function(){
                                    var cb = $(this).parents("tr").find("td").eq(1).html();
									var device = $(this).parents("tr").find("td").eq(2).html();
								    var value_name = $(this).parents("tr").find("td").eq(3).html();
									
						            $("#deleteValueModal div.modal-body").html('<div class="modalBodyInnerDiv"><span data-value_name = "' + value_name + '" data-cb = "' + cb + '" data-device = "' + device + '">Do you want to confirm deletion of value <b>' + value_name + '</b> from Device <b>' + device + '</b>?</span></div>');
                                    $("#deleteValueModal").modal('show');
                                });
                            }
                        });
                    }
            });
        }
    });
	
	
	 function drawMap(latitude,longitude){ 
	   map = L.map('addDeviceMapModalBody').setView([latitude,longitude], 10);
	   L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
	   attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
	  }).addTo(map);
	   window.node_input_map = map;
	   L.marker([latitude,longitude]).addTo(map).bindPopup("Hi DEVICE");
	   setTimeout(function(){ map.invalidateSize()}, 400);
	  }
	
	
	  function getLatLong(id){
		     
			console.log(id);
			var id = id;
				$.ajax({
					url: "get_data.php",
					data: {
						id: id, 
						action: "getLatLong_data"
					},
					type: "GET",
					async: true,
					dataType: 'json',
					success: function (data)
					{
						if(data.result === 'Ok')
						{
							console.log(data);
							//var latitude = data[6];
							//var longitude = data[7];
							
						}
						else
						{
							console.log("In: Error retrieving latitude and longitude data");
	
						}

					},
					error: function(errorData)
					{
						console.log("Out :Error retrieving latitude and longitude data");	
					}
				});
		
				//drawMap(latitude,longitude);
				drawMap(43.78, 11.23);
 
		   
	   }
	

	
</script>  


