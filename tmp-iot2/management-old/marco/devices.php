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
        <script type="text/javascript" src="../js/dashboard_mng.js"></script>
        
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
                    <div class="row">
                        <div class="col-xs-10 col-md-12 centerWithFlex" id="headerTitleCnt">Devices</div>
                        <div class="col-xs-2 hidden-md hidden-lg centerWithFlex" id="headerMenuCnt"><?php include "mobMainMenu.php" ?></div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12" id="mainContentCnt">
                            <div class="row hidden-xs hidden-sm mainContentRow">
                                <div class="col-xs-12 mainContentRowDesc">Synthesis</div>
                                <div id="dashboardTotNumberCnt" class="col-md-2 mainContentCellCnt">
                                    <div class="col-md-12 centerWithFlex pageSingleDataCnt">
                                        <?php
                                            $query = "SELECT count(*) AS qt FROM iotdirectorydb.devices";
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
                                        devices
                                    </div>
                                </div>
                                <div id="dashboardTotActiveCnt" class="col-md-2 mainContentCellCnt">
                                    <div class="col-md-12 centerWithFlex pageSingleDataCnt">
                                        <?php
                                            $query = "SELECT count(*) AS qt FROM iotdirectorydb.devices";
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
                                        active
                                    </div>
                                </div>
                                <div id="dashboardTotPermCnt" class="col-md-2 mainContentCellCnt">
                                    <div class="col-md-12 centerWithFlex pageSingleDataCnt">
                                        <?php
                                            
                                        ?>
                                    </div>
                                    <div class="col-md-12 centerWithFlex pageSingleDataLabel">
                                        public
                                    </div>
                                </div>

                            </div>
                            <div class="row mainContentRow">
                                <div class="col-xs-12 mainContentRowDesc">List</div>
                                <div class="col-xs-12 mainContentCellCnt">
                                    <table id="devicesTable" class="table"></table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Modale di conferma cancellazione utente -->
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

        <!-- Modale di registrazione nuovo utente -->
        <div class="modal fade" id="addDeviceModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modalHeader centerWithFlex">
                  Add new device
                </div>
        
		
				<form id="addDeviceForm" name="addDeviceForm" role="form" method="post" action="" data-toggle="validator">
					<div id="addDeviceModalBody" class="modal-body modalBody">
				
                
					<ul id="addDeviceModalTabs" class="nav nav-tabs nav-justified">
						<li class="active"><a data-toggle="tab" href="#addInfoTabDevice">Info</a></li>
                        <li><a data-toggle="tab" href="#addManufacturerTabDevice">Manufacturer</a></li>
                        <li><a data-toggle="tab" href="#addGeoPositionTabDevice">Geo-Position</a></li>
                        <li><a data-toggle="tab" href="#addSchemaTabDevice">Device Schema</a></li>
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
                                        <input type="text" class="modalInputTxt" name="inputTypeDevice" id="inputTypeDevice"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Type</div>
									<div id="inputTypeDeviceMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <select id="selectContextBroker" name="selectContextBroker" class="modalInputTxt">

                                       <?php
                                            $query = "SELECT name FROM iotdirectorydb.contextbroker";
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
                                        ?>
										</select>
                                    </div>
                                    <div class="modalFieldLabelCnt">ContextBroker</div>
									<div id="selectContextBrokerMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputUriDevice" id="inputUriDevice"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">URI</div>
									<div id="inputUriDeviceMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <select id="selectProtocolDevice" name="selectProtocolDevice" class="modalInputTxt">
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
											<option value="csv">csv</option>
											<option value="json">json</option>
											<option value="xml">xml</option>
										</select>
                                    </div>
                                    <div class="modalFieldLabelCnt">Format</div>
									<div id="selectFormatDeviceMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input id="createdDateDevice" name="createdDateDevice" type="date">
                                    </div>
                                    <div class="modalFieldLabelCnt">Created</div>
									<div id="createdDateDeviceMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
                               
                            </div>
                        </div>
                        
                        <!-- Manufacturer tab -->
                        <div id="addManufacturerTabDevice" class="tab-pane fade">
                            <div class="row">
								<div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputMacDevice" id="inputMacDevice"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Mac Address</div>
									<div id="inputMacDeviceMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputModelDevice" id="inputModelDevice"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Model</div>
									<div id="inputModelDeviceMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
								<div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputProducerDevice" id="inputProducerDevice"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Producer</div>
									<div id="inputProducerDeviceMsg" class="modalFieldMsgCnt">&nbsp;</div>
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
                        </div>
                        
                        <!-- Device Schema tab -->
                        <div id="addSchemaTabDevice" class="tab-pane fade">
                            <div class="row">
								<div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputPropertiesDevice" id="inputPropertiesDevice"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Properties</div>
									<div id="inputPropertiesDeviceMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputAttributesDevice" id="inputAttributesDevice"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Attributes</div>
									<div id="inputAttributesDeviceMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
                            </div>
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
                        <div class="col-xs-12 centerWithFlex">Error adding device</div>
                    </div>
                    <div class="row" id="addDeviceKoIcon">
                        <div class="col-xs-12 centerWithFlex"><i class="fa fa-thumbs-o-down" style="font-size:36px"></i></div>
                    </div>
				</div> 	
		       
         		<div id="addDeviceModalFooter" class="modal-footer">
                  <button type="button" id="addNewDeviceCancelBtn" class="btn cancelBtn" data-dismiss="modal">Cancel</button>
                  <button type="submit" id="addNewDeviceConfirmBtn" name="addNewDeviceConfirmBtn" class="btn confirmBtn internalLink">Confirm</button>
				</div>
				
			</form>	 
	
              </div>
            </div>
        </div>

        <!-- Modale di notifica inserimento utente avvenuto con successo -->
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
        
        <!-- Modale di notifica inserimento utente fallito -->
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

        <!-- Modale di modifica account utente -->
        <div class="modal fade" id="editDeviceModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div id="editDeviceModalLabel" class="modalHeader centerWithFlex">
                  Update Device
                </div>
               
				<form id="editDeviceForm" name="editDeviceForm" role="form" method="post" action="" data-toggle="validator">
                <div id="editDeviceModalBody" class="modal-body modalBody">
                    
                     <ul id="editDeviceModalTabs" class="nav nav-tabs nav-justified">
						<li class="active"><a data-toggle="tab" href="#editInfoTabDevice">Info</a></li>
                        <li><a data-toggle="tab" href="#editManufacturerTabDevice">Manufacturer</a></li>
                        <li><a data-toggle="tab" href="#editGeoPositionTabDevice">Geo-Position</a></li>
                        <li><a data-toggle="tab" href="#editSchemaTabDevice">Device Schema</a></li>
                    </ul>
                    
                    <div class="tab-content">
                       
                        <!-- Info tab -->
                        <div id="editInfoTabDevice" class="tab-pane fade in active">
                            <div class="row">
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputNameDeviceM" id="inputNameDeviceM" required> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Name</div>
                                </div>
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputTypeDeviceM" id="inputTypeDeviceM"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Type</div>
                                </div>
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <select id="selectContextBrokerM" name="selectContextBrokerM" class="modalInputTxt">
										<?php
                                            $query = "SELECT name FROM iotdirectorydb.contextbroker";
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
                                        ?>
									</select>
                                    </div>
                                    <div class="modalFieldLabelCnt">ContextBroker</div>
                                </div>
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputUriDeviceM" id="inputUriDeviceM"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">URI</div>
                                </div>
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
                                </div>
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input id="createdDateDeviceM" name="createdDateDeviceM" type="date">
                                    </div>
                                    <div class="modalFieldLabelCnt">Created</div>
                                </div>
                               
                            </div>
                        </div>
                        
                        <!-- Manufacturer tab -->
                        <div id="editManufacturerTabDevice" class="tab-pane fade">
                            <div class="row">
								<div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputMacDeviceM" id="inputMacDeviceM"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Mac Address</div>
                                </div>
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputModelDeviceM" id="inputModelDeviceM"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Model</div>
                                </div>
								<div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputProducerDeviceM" id="inputProducerDeviceM"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Producer</div>
                                </div>
							
                            </div>    
                        </div>
                        
                        <!-- Geo-Position tab -->
                        <div id="editGeoPositionTabDevice" class="tab-pane fade">
                            <div class="row">
								<div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputLatitudeDeviceM" id="inputLatitudeDeviceM"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Latitude</div>
                                </div>
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputLongitudeDeviceM" id="inputLongitudeDeviceM"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Longitude</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Device Schema tab -->
                        <div id="editSchemaTabDevice" class="tab-pane fade">
                            <div class="row">
								<div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputPropertiesDeviceM" id="inputPropertiesDeviceM"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Properties</div>
                                </div>
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputAttributesDeviceM" id="inputAttributesDeviceM"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Attributes</div>
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
                    </div>
                    <div class="row" id="editDeviceKoIcon">
                        <div class="col-xs-12 centerWithFlex"><i class="fa fa-thumbs-o-down" style="font-size:36px"></i></div>
                    </div>
		            
                </div>
				<div id="editDeviceModalFooter" class="modal-footer">
                  <button type="button" id="editDeviceCancelBtn" class="btn cancelBtn" data-dismiss="modal">Cancel</button>
                  <button type="button" id="editDeviceConfirmBtn" class="btn confirmBtn internalLink" >Confirm</button>
                </div>
				
				</form> 	
                
              </div>
            </div>
        </div>
        
        <!-- Modale di notifica edit account utente avvenuto con successo -->
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
        
        <!-- Modale di notifica edit account utente fallito -->
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
                $('#devicesTable').bootstrapTable('hideColumn', 'name');
                $('#devicesTable').bootstrapTable('hideColumn', 'contextBroker');
                $('#devicesTable').bootstrapTable('hideColumn', 'uri');
                $('#devicesTable').bootstrapTable('hideColumn', 'protocol');
                $('#devicesTable').bootstrapTable('hideColumn', 'format');
                $('#devicesTable').bootstrapTable('hideColumn', 'type');
                $('#devicesTable').bootstrapTable('hideColumn', 'created');
            }
            else
            {
                $('#devicesTable').bootstrapTable('showColumn', 'name');
                $('#devicesTable').bootstrapTable('showColumn', 'contextBroker');
                $('#devicesTable').bootstrapTable('showColumn', 'uri');
                $('#devicesTable').bootstrapTable('showColumn', 'protocol');
                $('#devicesTable').bootstrapTable('showColumn', 'format');
                $('#devicesTable').bootstrapTable('showColumn', 'type');
                $('#devicesTable').bootstrapTable('showColumn', 'created');
            }
        });
        
        $('#devicesLink .mainMenuItemCnt').addClass("mainMenuItemCntActive");
        $('#mobMainMenuPortraitCnt #devicesLink .mobMainMenuItemCnt').addClass("mainMenuItemCntActive");
        $('#mobMainMenuLandCnt #devicesLink .mobMainMenuItemCnt').addClass("mainMenuItemCntActive");
        
        var admin = "<?= $_SESSION['loggedRole'] ?>";
        var existingPoolsJson = null;
        var internalDest = false;
        var tableFirstLoad = true;
        
        buildMainTable(false);
        
        //Settaggio dei globals per il file usersManagement.js
        setGlobals(admin, existingPoolsJson);
        

        /*   ADD NEW DEVICE  (EXEcURE INSERT) */
        $('#addNewDeviceConfirmBtn').off("click");
        $("#addNewDeviceConfirmBtn").click(function(){
            $("#addDeviceModalTabs").hide();
	    $('#addDeviceModal div.modalCell').hide();
            $("#addDeviceModalFooter").hide();
            $('#addDeviceLoadingMsg').show();
            $('#addDeviceLoadingIcon').show();
				/*
             newDeviceJson = {
                 inputNameDevice: $("#addDeviceForm #inputNameDevice").val(),
                 inputTypeDevice: $("#addDeviceForm #inputTypeDevice").val(),
                 selectContextBroker: $("#addDeviceForm #selectContextBroker").val(),
                 inputUriDevice: $("#addDeviceForm #inputUriDevice").val(),
                 selectProtocolDevice: $("#addDeviceForm #selectProtocolDevice").val(),
                 selectFormatDevice: $("#addDeviceForm #selectFormatDevice").val(),
                 createdDateDevice: $("#addDeviceForm #createdDateDevice").val(),
				 inputMacDevice: $("#addDeviceForm #inputMacDevice").val(),
                 inputModelDevice: $("#addDeviceForm #inputModelDevice").val(),
                 inputProducerDevice: $("#addDeviceForm #inputProducerDevice").val(),
                 inputLatitudeDevice: $("#addDeviceForm #inputLatitudeDevice").val(),
                 inputLongitudeDevice: $("#addDeviceForm #inputLongitudeDevice").val(),
                 inputPropertiesDevice: $("#addDeviceForm #inputPropertiesDevice").val(),
                 inputAttributesDevice: $("#addDeviceForm #inputAttributesDevice").val()

             };
			*/
			
			
             //Chiamata API di inserimento nuovo utente
             $.ajax({
                 url: "process-form.php",
                 data:{
					  addDevice: true,
					 //newDeviceJson: JSON.stringify(newDeviceJson)
					  inputNameDevice: $('#inputNameDevice').val(),
					  inputTypeDevice: $('#inputTypeDevice').val(),
					  selectContextBroker: $('#selectContextBroker').val(),
					  inputUriDevice: $('#inputUriDevice').val(),
					  selectProtocolDevice: $('#selectProtocolDevice').val(),
					  selectFormatDevice: $('#selectFormatDevice').val(),
					  createdDateDevice: $('#createdDateDevice').val(),
					  inputMacDevice: $('#inputMacDevice').val(),
					  inputModelDevice: $('#inputModelDevice').val(),
					  inputProducerDevice: $('#inputProducerDevice').val(),
					  inputLatitudeDevice: $('#inputLatitudeDevice').val(),
					  inputLongitudeDevice: $('#inputLongitudeDevice').val()
					 },
                 type: "POST",
                 async: true,
                 success: function (data) 
                 {
					 console.log("Elf result: " + data);
					 
		    if(data == 'Ko')
                    {
                        console.log("Error adding Device type");
                        console.log(data);
						$('#addDeviceLoadingMsg').hide();
                        $('#addDeviceLoadingIcon').hide();
                        $('#addDeviceKoMsg').show();
                        $('#addDeviceKoIcon').show();
                      //  $('#addDeviceLoadingMsg').hide();
                       // $('#addDeviceLoadingIcon').hide();
                       // $('#addDeviceKoMsg').show();
                        //$('#addDeviceKoIcon').show();
                        setTimeout(function(){
                            $('#addDeviceKoMsg').hide();
                            $('#addDeviceKoIcon').hide();
                            $('#addDeviceModalTabs').show();
                            $('#addDeviceModal div.modalCell').show();
                            $('#addDeviceModalFooter').show();
                        }, 3000);
                    }			 
		    else if (data == 'Ok')
                    {
						
						
			$('#addDeviceLoadingMsg').hide();
                        $('#addDeviceLoadingIcon').hide();
                        $('#addDeviceKoMsg').hide();
                        $('#addDeviceKoIcon').hide();
                        $('#addDeviceOkMsg').show();
                        $('#addDeviceOkIcon').show();
                                                 
                       // $('#addDeviceLoadingMsg').hide();
                      //  $('#addDeviceLoadingIcon').hide();
                       // $('#addDeviceOkMsg').show();
                        //$('#addDeviceOkIcon').show();
		
						
			// $("#addingDeviceModalInnerDiv1").html('Device &nbsp; <b>' + name + '</b> &nbsp;deleted successfully');
                        // $("#addingDeviceModalInnerDiv2").html('<i class="fa fa-check" style="font-size:42px"></i>');
	                 
			setTimeout(function(){
                            $('#addDeviceModal').modal('hide');
                            buildMainTable(true);

                            setTimeout(function(){
                               // $('#addDeviceOkMsg').hide();
                               // $('#addDeviceOkIcon').hide();
				  $('#addDeviceOkMsg').hide();
                                  $('#addDeviceOkIcon').hide();
								  $('#inputNameDevice').val("");
								  $('#inputTypeDevice').val("");
								  $('#selectContextBroker').val("");
								  $('#inputUriDevice').val("");
								  $('#selectProtocolDevice').val("");
								  $('#selectFormatDevice').val("");
								  $('#createdDateDevice').val("");
								  $('#inputMacDevice').val("");
								  $('#inputModelDevice').val("");
								  $('#inputProducerDevice').val("");
								  $('#inputLatitudeDevice').val("");
								  $('#inputLongitudeDevice').val("");
																
								  $('#addDeviceModalTabs').show();
                                  $('#addDeviceModal div.modalCell').show();
                                  $('#addDeviceModalFooter').show();
                            }, 500);
                        }, 3000);
						
						
						
						
						
						
						/*
						$("#addDeviceModal").modal('hide');
						 $('#dashboardTotNumberCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotNumberCnt .pageSingleDataCnt').html()) + 1);
                        $('#dashboardTotActiveCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotActiveCnt .pageSingleDataCnt').html()) + 1);
                          
                             buildMainTable(true);
                             $("#addDeviceOkModalInnerDiv1").html('<h5>User <b>' + newDeviceJson.name + '</b> successfully registered</h5>');
                             $("#addDeviceOkModal").modal('show');
                             $("#addDeviceModalCreating").hide();
                             $("#addDeviceModalBody").show();
                             $("#addDeviceModalFooter").show();
                             setTimeout(function(){
                                 $("#addDeviceOkModal").modal('hide');
                             }, 2000);
							 */
                    }
					 /*
                     switch(data)
                     {
                         case '0':
                             $("#addDeviceModal").modal('hide');
                             $("#addDeviceKoModalInnerDiv1").html('<h5>User <b>' + newDeviceJson.name + '</b> couldn\'t be registered because of a database failure while inserting data, please try again</h5>');
                             $("#addDeviceKoModal").modal('show');
                             $("#addDeviceModalCreating").hide();
                             $("#addDeviceModalBody").show();
                             $("#addDeviceModalFooter").show();
                             break;

                         case '1':
                             $("#addDeviceModal").modal('hide');
                             buildMainTable(true);
                             $("#addDeviceOkModalInnerDiv1").html('<h5>User <b>' + newDeviceJson.name + '</b> successfully registered</h5>');
                             $("#addDeviceOkModal").modal('show');
                             $("#addDeviceModalCreating").hide();
                             $("#addDeviceModalBody").show();
                             $("#addDeviceModalFooter").show();
                             setTimeout(function(){
                                 $("#addDeviceOkModal").modal('hide');
                             }, 2000);
                             break;

                         case '2':
                             $("#addDeviceModal").modal('hide');
                             $("#addDeviceKoModalInnerDiv1").html('<h5>User <b>' + newDeviceJson.name + '</b> couldn\'t be registered because of a database failure while checking for existing device, please try again</h5>');
                             $("#addDeviceKoModal").modal('show');
                             $("#addDeviceModalCreating").hide();
                             $("#addDeviceModalBody").show();
                             $("#addDeviceModalFooter").show();
                             break;

                         case '3':
                             $("#addDeviceModal").modal('hide');
                             $("#addDeviceKoModalInnerDiv1").html('<h5>User <b>' + newDeviceJson.name + '</b> couldn\'t be registered: this username is already in device, please change it and try again</h5>');
                             $("#addDeviceKoModal").modal('show');
                             $("#addDeviceModalCreating").hide();
                             $("#addDeviceModalBody").show();
                             $("#addDeviceModalFooter").show();
                             break;

                         
                         default:
                             break;
                     }
					 */
                 },
                 error: function (data) 
                 {
                     console.log("Ko result: " + data);
                     $("#addUserModal").modal('hide');
                     $("#addUserKoModalInnerDiv1").html('<h5>User <b>' + newUserJson.username + '</b> couldn\'t be registered because of an API call failure, please try again</h5>');
                     $("#addUserKoModal").modal('show');
                     $("#addUserModalCreating").hide();
                     $("#addUserModalBody").show();
                     $("#addUserModalFooter").show();
                 }
             });
        });
        
        $('#deleteDeviceConfirmBtn').off("click");
        $("#deleteDeviceConfirmBtn").click(function(){
            var name = $("#deleteDeviceModal span").attr("data-name");
    
            $("#deleteDeviceModal div.modal-body").html("");
            $("#deleteDeviceCancelBtn").hide();
            $("#deleteDeviceConfirmBtn").hide();
            $("#deleteDeviceModal div.modal-body").append('<div id="deleteDeviceModalInnerDiv1" class="modalBodyInnerDiv"><h5>Device deletion in progress, please wait</h5></div>');
            $("#deleteDeviceModal div.modal-body").append('<div id="deleteDeviceModalInnerDiv2" class="modalBodyInnerDiv"><i class="fa fa-circle-o-notch fa-spin" style="font-size:36px"></i></div>');

             //Chiamata API di cancellazione utente
            $.ajax({
                url: "deleteDevice.php",
                data:{name: name},
                type: "POST",
                async: false,
                success: function (data) 
                {
                    if(data === '0')
                    {
                        $("#deleteDeviceModalInnerDiv1").html('Device &nbsp; <b>' + name + '</b> &nbsp; deletion failed, please try again');
                        $("#deleteDeviceModalInnerDiv2").html('<i class="fa fa-frown-o" style="font-size:42px"></i>');
                    }
                    else if( data === '1')
                    {
                        $("#deleteDeviceModalInnerDiv1").html('Device &nbsp; <b>' + name + '</b> &nbsp;deleted successfully');
                        $("#deleteDeviceModalInnerDiv2").html('<i class="fa fa-check" style="font-size:42px"></i>');
						
						$('#dashboardTotNumberCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotNumberCnt .pageSingleDataCnt').html()) - 1);
                        $('#dashboardTotActiveCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotActiveCnt .pageSingleDataCnt').html()) - 1);
                        setTimeout(function()
                        {
                            buildMainTable(true);
                            $("#deleteDeviceModal").modal('hide');
                            setTimeout(function(){
                                $("#deleteDeviceCancelBtn").show();
                                $("#deleteDeviceConfirmBtn").show();
                            }, 500);
                        }, 2000);
                    }
                },
                error: function (data) 
                {
                    $("#deleteDeviceModalInnerDiv1").html('Device &nbsp; <b>' + name + '</b> &nbsp; deletion failed, please try again');
                    $("#deleteDeviceModalInnerDiv2").html('<i class="fa fa-frown-o" style="font-size:42px"></i>');
                }
            });
        });
        
        $('#editDeviceConfirmBtn').off("click");
        $("#editDeviceConfirmBtn").click(function(){
           // $("#editDeviceModalBody").hide();
           // $("#editDeviceModalFooter").hide();
           // $("#editDeviceModalUpdating").show();
			
			
			$("#editDeviceModalTabs").hide();
			$('#editDeviceModal div.modalCell').hide();
            $("#editDeviceModalFooter").hide();
            $('#editDeviceLoadingMsg').show();
            $('#editDeviceLoadingIcon').show();
			
			/*
             deviceJson = {
                 inputNameDevice: $("#editDeviceForm #inputNameDeviceM").val(),
                 inputTypeDevice: $("#editDeviceForm #inputTypeDeviceM").val(),
                 selectContextBroker: $("#editDeviceForm #selectContextBrokerM").val(),
                 inputUriDevice: $("#editDeviceForm #inputUriDeviceM").val(),
                 selectProtocolDevice: $("#editDeviceForm #selectProtocolDeviceM").val(),
                 selectFormatDevice: $("#editDeviceForm #selectFormatDeviceM").val(),
                 createdDateDevice: $("#editDeviceForm #createdDateDeviceM").val(),
				 inputMacDevice: $("#editDeviceForm #inputMacDeviceM").val(),
                 inputModelDevice: $("#editDeviceForm #inputModelDeviceM").val(),
                 inputProducerDevice: $("#editDeviceForm #inputProducerDeviceM").val(),
                 inputLatitudeDevice: $("#editDeviceForm #inputLatitudeDeviceM").val(),
                 inputLongitudeDevice: $("#editDeviceForm #inputLongitudeDeviceM").val(),
                 inputPropertiesDevice: $("#editDeviceForm #inputPropertiesDeviceM").val(),
                 inputAttributesDevice: $("#editDeviceForm #inputAttributesDeviceM").val()

             };
			*/

            // console.log(JSON.stringify(deviceJson));

             //Chiamata API di aggiornamento account utente
             $.ajax({
                 url: "process-form.php",
                 data:{
				 updateDevice: true, 
				 //deviceJson: JSON.stringify(deviceJson)
				 inputNameDeviceM: $('#inputNameDeviceM').val(),
			     inputTypeDeviceM: $('#inputTypeDeviceM').val(),
			     selectContextBrokerM: $('#selectContextBrokerM').val(),
			     inputUriDeviceM: $('#inputUriDeviceM').val(),
			     selectProtocolDeviceM: $('#selectProtocolDeviceM').val(),
			     selectFormatDeviceM: $('#selectFormatDeviceM').val(),
			     createdDateDeviceM: $('#createdDateDeviceM').val(),
			     inputMacDeviceM: $('#inputMacDeviceM').val(),
			     inputModelDeviceM: $('#inputModelDeviceM').val(),
			     inputProducerDeviceM: $('#inputProducerDeviceM').val(),
			     inputLatitudeDeviceM: $('#inputLatitudeDeviceM').val(),
			     inputLongitudeDeviceM: $('#inputLongitudeDeviceM').val()
				 },
                 type: "POST",
                 async: true,
                 success: function (data) 
                 {
		   if(data == 'Ko')
                    {
                        console.log("Error editing Device type");
                        console.log(data);
						$('#editDeviceLoadingMsg').hide();
                        $('#editDeviceLoadingIcon').hide();
                        $('#editDeviceKoMsg').show();
                        $('#editDeviceKoIcon').show();
                      //  $('#addDeviceLoadingMsg').hide();
                       // $('#addDeviceLoadingIcon').hide();
                       // $('#addDeviceKoMsg').show();
                        //$('#addDeviceKoIcon').show();
                        setTimeout(function(){
                            $('#editDeviceKoMsg').hide();
                            $('#editDeviceKoIcon').hide();
                            $('#editDeviceModalTabs').show();
                            $('#editDeviceModal div.modalCell').show();
                            $('#editDeviceModalFooter').show();
                        }, 3000);
                    }
					 
		   else if (data  == 'Ok')
                    {
						
			$('#editDeviceLoadingMsg').hide();
                        $('#editDeviceLoadingIcon').hide();
                        $('#editDeviceOkMsg').show();
                        $('#editDeviceOkIcon').show();
                        			
			$("#addingDeviceModalInnerDiv1").html('Device &nbsp; <b>' + name + '</b> &nbsp;deleted successfully');
                        $("#addingDeviceModalInnerDiv2").html('<i class="fa fa-check" style="font-size:42px"></i>');
	                 
						setTimeout(function(){
                            $('#addDeviceModal').modal('hide');
                            buildMainTable(true);

                            setTimeout(function(){
                               // $('#addDeviceOkMsg').hide();
                               // $('#addDeviceOkIcon').hide();
								  $('#editDeviceOkMsg').hide();
                                  $('#editDeviceOkIcon').hide();
								  $('#inputNameDevice').val("");
								  $('#inputTypeDevice').val("");
								  $('#selectContextBroker').val("");
								  $('#inputUriDevice').val("");
								  $('#selectProtocolDevice').val("");
								  $('#selectFormatDevice').val("");
								  $('#createdDateDevice').val("");
								  $('#inputMacDevice').val("");
								  $('#inputModelDevice').val("");
								  $('#inputProducerDevice').val("");
								  $('#inputLatitudeDevice').val("");
								  $('#inputLongitudeDevice').val("");
									// **MM							
				  //$('#editDeviceModalTabs').hide();
                                  // $('#editDeviceModal div.modalCell').hide();
                                  // $('#editDeviceModalFooter').hide();
                                  $('#editDeviceModal').hide();
                            }, 500);
                        }, 3000);
						
						
					}
					 
					 
					 
					 
					 
					 
					 
					 /*
                     switch(data)
                     {
                         case '0':
                             $("#editUserModal").modal('hide');
                             $("#editUserKoModalInnerDiv1").html('<h5>Account <b>' + accountJson.username + '</b> couldn\'t be updated because of a database failure while inserting data, please try again</h5>');
                             $("#editUserKoModal").modal('show');
                             $("#editUserModalUpdating").hide();
                             $("#editUserModalBody").show();
                             $("#editUserModalFooter").show();
                             break;

                         case '1':
                             $("#editUserModal").modal('hide');
                             $("#editUserOkModalInnerDiv1").html('<h5>Account <b>' + accountJson.username + '</b> successfully updated</h5>');
                             $("#editUserOkModal").modal('show');
                             setTimeout(updateAccountTimeout, 2000);
                             break;

                         case '4':
                             $("#editUserModal").modal('hide');
                             $("#editUserKoModalInnerDiv1").html('<h5>Account <b>' + accountJson.username + '</b> couldn\'t be updated: password is less than 8 chars long and/or doesn\'t have at least 1 char and 1 digit, please change it and try again</h5>');
                             $("#editUserKoModal").modal('show');
                             $("#editUserModalUpdating").hide();
                             $("#editUserModalBody").show();
                             $("#editUserModalFooter").show();
                             break;

                         case '5':
                             $("#editUserModal").modal('hide');
                             $("#editUserKoModalInnerDiv1").html('<h5>Account <b>' + accountJson.username + '</b> couldn\'t be updated: password and password confirmation don\'t match, please fix and try again</h5>');
                             $("#editUserKoModal").modal('show');
                             $("#editUserModalUpdating").hide();
                             $("#editUserModalBody").show();
                             $("#editUserModalFooter").show();
                             break;

                         case '6':
                             $("#editUserModal").modal('hide');
                             $("#editUserKoModalInnerDiv1").html('<h5>Account <b>' + accountJson.username + '</b> couldn\'t be updated: one between (first name - last name) and organization must be given, please fix and try again</h5>');
                             $("#editUserKoModal").modal('show');
                             $("#editUserModalUpdating").hide();
                             $("#editUserModalBody").show();
                             $("#editUserModalFooter").show();
                             break;

                         case '7':
                             $("#editUserModal").modal('hide');
                             $("#editUserKoModalInnerDiv1").html('<h5>Account <b>' + accountJson.username + '</b> couldn\'t be updated: e-mail address doesn\'t respect mailbox@domain.ext pattern, please fix and try again</h5>');
                             $("#editUserKoModal").modal('show');
                             $("#editUserModalUpdating").hide();
                             $("#editUserModalBody").show();
                             $("#editUserModalFooter").show();
                             break;

                         default:
                             break;
                     }
					 */
					
					
					 
					 
					 
					 
					 
					 
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
        
        $("#addNewDeviceCancelBtn").off("click");
        $("#addNewDeviceCancelBtn").on('click', function(){
            $("#addDeviceForm").trigger("reset");
            $("#addDeviceAdminRoleChoiceOuterContainer").hide();
            $("#addDeviceAdminPoolsChoiceOuterContainer").hide();
            $("#addDeviceNewPoolNameOuterContainer").hide();
            $("#addDeviceAddUsersToNewPoolOuterContainer").hide();
            $("#addDevicePoolsOuterContainer").show();
        });
        
        $("#addDeviceKoBackBtn").off("click");
        $("#addDeviceKoBackBtn").on('click', function(){
            $("#addDeviceKoModal").modal('hide');
            $("#addDeviceModal").modal('show');
        });
        
        $("#addDeviceKoConfirmBtn").off("click");
        $("#addDeviceKoConfirmBtn").on('click', function(){
            $("#addDeviceKoModal").modal('hide');
            $("#addDeviceForm").trigger("reset");
        });
        
        $("#editDeviceKoBackBtn").off("click");
        $("#editDeviceKoBackBtn").on('click', function(){
            $("#editDeviceKoModal").modal('hide');
            $("#editDeviceModal").modal('show');
        });
        
        $("#editDeviceKoConfirmBtn").off("click");
        $("#editDeviceKoConfirmBtn").on('click', function(){
            $("#editDeviceKoModal").modal('hide');
            $("#editDeviceForm").trigger("reset");
        });
        
       
        function updateAccountTimeout()
        {
            $("#editDeviceOkModal").modal('hide');
            setTimeout(function(){
               location.reload();
            }, 500);
        }
        
        function buildMainTable(destroyOld)
        {
            if(destroyOld)
            {
                $('#devicesTable').bootstrapTable('destroy');
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
                data: {action: "getDevices"},
                type: "GET",
                async: true,
                datatype: 'json',
                success: function (data)
                {
					
					var creatorVisibile = true;
                    var detailView = true;
                    var statusVisibile = true;
                   console.log(data);                    

                    if($(window).width() < 992)
                    {
                        detailView = false;
                        creatorVisibile = false; 
                        statusVisibile = false;
                    }
					
					
                    $('#devicesTable').bootstrapTable({
                            columns: [{
									field: 'name',
									title: 'Name',
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
                                field: 'contextBroker',
								title: 'ContextBroker',
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
                                field: 'protocol',
								title: 'Protocol',
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
                                field: 'format',
								title: 'Format',
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
                                field: 'type',
								title: 'Type',
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
                            field: 'created',
                            title: 'Created',
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
                                    return '<button type="button" class="editDashBtn">edit</button>';
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
                                    
                                    return '<button type="button" class="delDashBtn">del</button>';
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
                            }],
                            data: data,
                            search: true,
                            pagination: true,
                            pageSize: 10,
                            locale: 'en-US',
                            searchAlign: 'left',
                            uniqueId: "name",
                            striped: false,
                            searchTimeOut: 250,
                            classes: "table table-hover table-no-bordered",
							detailView: detailView,
							detailFormatter: function(index, row, element) {
                            return 'MAC: ' + data[index].macaddress + ' | Model: ' + data[index].model + " | Producer: " + data[index].producer + " | Longitude: " + data[index].longitude + " | Latitude: " + data[index].latitude;
							},
                            rowAttributes: function(row, index){
                            return {
                                "data-name": row.name,
                                "data-type": row.type,
                                "data-contextBroker": row.contextBroker,
                                "data-uri": row.uri,
                                "data-protocol": row.protocol,
                                "data-format": row.format,
                                "data-created": row.created,
                                "data-macaddress": row.macaddress,
								"data-model": row.model,
                                "data-producer": row.producer,
                                "data-latitude": row.latitude,
                                "data-longitude": row.longitude,
                                "data-properties": row.properties,
                                "data-attributes": row.attributes
                            };},
                            onPostBody: function()
                            {
                                if(tableFirstLoad)
                                {
                                    //Caso di primo caricamento della tabella
                                    tableFirstLoad = false;
                                    var addDeviceDiv = $('<div class="pull-right"><i id="addDeviceBtn" data-toggle="modal" data-target="#addDeviceModal" class="fa fa-plus-square" style="font-size:36px; color: #ffcc00"></i></div>');
                                    
                                    $('div.fixed-table-toolbar').append(addDeviceDiv);
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
				    $("#addDeviceBtn").off("click");
                                    $("#addDeviceBtn").click(function(){


                                      $("#addDeviceModalBody").modal('show');
                                      $("#addDeviceLoadingMsg").hide();
                                      $("#addDeviceLoadingIcon").hide();
                                      $("#addDeviceOkMsg").hide();
                                      $("#addDeviceOkIcon").hide();
                                      $("#addDeviceKoMsg").hide();
                                      $("#addDeviceKoIcon").hide();

                                   });
                                    

                                    $('#devicesTable thead').css("background", "rgba(0, 162, 211, 1)");
                                    $('#devicesTable thead').css("color", "white");
                                    $('#devicesTable thead').css("font-size", "1em");
                                }
                                else
                                {
                                    //Casi di cambio pagina
                                }

                                //Istruzioni da eseguire comunque
								
								 $('#devicesTable tbody tr').each(function(i){
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
                                $('#devicesTable').css("border-bottom", "none");
                                $('span.pagination-info').hide();

                                $('#devicesTable tbody button.editDashBtn').off('hover');
                                $('#devicesTable tbody button.editDashBtn').hover(function(){
                                    $(this).css('background', '#ffcc00');
                                    $(this).parents('tr').find('td').eq(1).css('background', '#ffcc00');
                                }, 
                                function(){
                                    $(this).css('background', 'rgb(69, 183, 175)');
                                    $(this).parents('tr').find('td').eq(1).css('background', $(this).parents('td').css('background'));
                                });

                                $('#devicesTable button.editDashBtn').off('click');
                                $('#devicesTable button.editDashBtn').click(function(){
                                    // $("#editDeviceModalUpdating").hide();
                                    $("#editDeviceModalBody").show();


                                      $("#editDeviceLoadingMsg").hide();
                                      $("#editDeviceLoadingIcon").hide();
                                      $("#editDeviceOkMsg").hide();
                                      $("#editDeviceOkIcon").hide();
                                      $("#editDeviceKoMsg").hide();
                                      $("#editDeviceKoIcon").hide(); 
                                    $("#editDeviceModalFooter").show();
                                    $("#editDeviceModal").modal('show');
                                    $("#editDeviceModalLabel").html("Edit device - " + $(this).parents('tr').attr("data-name"));
                                    $('#inputNameDeviceM').val($(this).parents('tr').attr('data-name'));
									$('#inputTypeDeviceM').val($(this).parents('tr').attr('data-type'));
									$('#selectContextBrokerM').val($(this).parents('tr').attr('data-contextBroker'));
									$('#inputUriDeviceM').val($(this).parents('tr').attr('data-uri'));
									$('#selectProtocolDeviceM').val($(this).parents('tr').attr('data-protocol'));
									$('#createdDateDeviceM').val($(this).parents('tr').attr('data-created'));
									$('#inputMacDeviceM').val($(this).parents('tr').attr('data-macaddress'));
									$('#inputModelDeviceM').val($(this).parents('tr').attr('data-model'));
									$('#inputProducerDeviceM').val($(this).parents('tr').attr('data-producer'));
									$('#inputLatitudeDeviceM').val($(this).parents('tr').attr('data-latitude'));
									$('#inputLongitudeDeviceM').val($(this).parents('tr').attr('data-longtitude'));
									$('#inputPropertiesDeviceM').val($(this).parents('tr').attr('data-properties'));
									$('#inputAttributesDeviceM').val($(this).parents('tr').attr('data-attributes'));

                                    $.ajax({
                                        url: "editDevice.php",
                                        data: {operation: "getUserPoolMemberships", name: $(this).parents('tr').attr("data-name")},
                                        type: "GET",
                                        async: true,
                                        dataType: 'json',
                                        success: function (data) 
                                        {
                                          var row = null;

                                          $("#editUserPoolsTable tbody").empty();
                                          for(var i = 0; i < data.length; i++)
                                          {
                                             row = $('<tr><td class="checkboxCell editUserPoolsTableMakeMemberCheckbox"><input data-poolId="' + data[i].poolId + '" type="checkbox" /></td><td class="checkboxCell editUserPoolsTableMakeAdminCheckbox"><input data-poolId="' +  data[i].poolId + '" type="checkbox" /></td><td class="poolNameCell">' + data[i].poolName + '</td>');

                                             switch(role)
                                             {
                                                case "Observer": case "Manager":
                                                   if(data[i].username !== null)
                                                   {
                                                      row.find(".editUserPoolsTableMakeMemberCheckbox input").attr("checked", true);
                                                   }
                                                   $(".editUserPoolsTableMakeAdminHeader").hide();
                                                   $(".editUserPoolsTableMakeAdminCheckbox").hide();
                                                   break;

                                                case "Area manager":
                                                   if(data[i].username !== null)
                                                   {
                                                      if(data[i].isAdmin === "1")
                                                      {
                                                         row.find(".editUserPoolsTableMakeAdminCheckbox input").attr("checked", true);
                                                      }
                                                      else
                                                      {
                                                         row.find(".editUserPoolsTableMakeMemberCheckbox input").attr("checked", true);
                                                      }
                                                   }

                                                   $(".editUserPoolsTableMakeAdminHeader").show();
                                                   $(".editUserPoolsTableMakeAdminCheckbox").show();
                                                   break;

                                                case "Tool admin":
                                                   break;   
                                             }

                                             $("#editUserPoolsTable").append(row);
                                          }

                                          switch(role)
                                          {
                                             case "Observer": 
                                                $("#editUserPoolsRow").show();
                                                $(".editUserPoolsTableMakeAdminHeader").hide();
                                                $(".editUserPoolsTableMakeAdminCheckbox").hide();
                                                break;

                                             case "Manager":   
                                                $("#editUserPoolsRow").show();
                                                $(".editUserPoolsTableMakeAdminHeader").hide();
                                                $(".editUserPoolsTableMakeAdminCheckbox").hide();
                                                break;

                                             case "Area manager":
                                                $(".editUserPoolsTableMakeMemberCheckbox input").click(function(){
                                                   $(this).parent().parent().find(".editUserPoolsTableMakeAdminCheckbox input").prop("checked", false);
                                                });

                                                $(".editUserPoolsTableMakeAdminCheckbox input").click(function(){
                                                   $(this).parent().parent().find(".editUserPoolsTableMakeMemberCheckbox input").prop("checked", false);
                                                });

                                                $("#editUserPoolsRow").show();
                                                $(".editUserPoolsTableMakeAdminHeader").show();
                                                $(".editUserPoolsTableMakeAdminCheckbox").show();
                                                break;

                                             case "Tool admin":
                                                $("#editUserPoolsRow").hide();
                                                break;   
                                          }

                                           $("#editUserModalLoading").hide();

                                           showEditUserModalBody();
                                        },
                                        error: function (data)
                                        {
                                           console.log("Get user pool memberships KO");
                                           console.log(data);
                                        }
                                    });
                                });

                                $('#devicesTable button.delDashBtn').off('hover');
                                $('#devicesTable button.delDashBtn').hover(function(){
                                    $(this).css('background', '#ffcc00');
                                    $(this).parents('tr').find('td').eq(1).css('background', '#ffcc00');
                                }, 
                                function(){
                                    $(this).css('background', '#e37777');
                                    $(this).parents('tr').find('td').eq(1).css('background', $(this).parents('td').css('background'));
                                });

                                $('#devicesTable button.delDashBtn').off('click');
                                $('#devicesTable button.delDashBtn').click(function(){
                                    var name = $(this).parents("tr").find("td").eq(1).html();
                                    $("#deleteDeviceModal div.modal-body").html('<div class="modalBodyInnerDiv"><span data-name = "' + name + '">Do you want to confirm deletion of device <b>' + name + '</b>?</span></div>');
                                    $("#deleteDeviceModal").modal('show');
                                });
                            }
                        });
                    }
            });
        }
    });
</script>  
