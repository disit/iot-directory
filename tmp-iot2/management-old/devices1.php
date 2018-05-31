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
		
		
		
        
        <!-- Modale creazione device -->
        <div class="modal fade" id="addDeviceModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modalHeader centerWithFlex">
                  Add new device 
                </div>
				
			
				<form id="addDeviceForm" name="addDeviceForm" role="form" method="post" action="process-form.php" data-toggle="validator">
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
                                </div>
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputTypeDevice" id="inputTypeDevice"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Type</div>
                                </div>
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <select id="selectContextBroker" name="selectContextBroker" class="modalInputTxt">
											<option value="MosquittoUNIMI">MosquittoUNIMI</option>
											<option value="OrionUNIFI">OrionUNIFI</option>
											<option value="OrionUNIFI">OrionUNIFI</option>
											<option value="Prova">Prova</option>
											<option value="RabbitUNIMI">RabbitUNIMI</option>
										</select>
                                    </div>
                                    <div class="modalFieldLabelCnt">ContextBroker</div>
                                </div>
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputUriDevice" id="inputUriDevice"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">URI</div>
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
                                </div>
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input id="createdDateDevice" name="createdDateDevice" type="date">
                                    </div>
                                    <div class="modalFieldLabelCnt">Created</div>
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
                                </div>
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputModelDevice" id="inputModelDevice"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Model</div>
                                </div>
								<div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputProducerDevice" id="inputProducerDevice"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Producer</div>
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
                                </div>
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputLongitudeDevice" id="inputLongitudeDevice"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Longitude</div>
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
                                </div>
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputAttributesDevice" id="inputAttributesDevice"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Attributes</div>
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
                        <div class="col-xs-12 centerWithFlex">Metric added successfully</div>
                    </div>
                    <div class="row" id="addDeviceOkIcon">
                        <div class="col-xs-12 centerWithFlex"><i class="fa fa-thumbs-o-up" style="font-size:36px"></i></div>
                    </div>
                    <div class="row" id="addDeviceKoMsg">
                        <div class="col-xs-12 centerWithFlex">Error adding metric</div>
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
	
		
		
	<!-- Modale di modifica device utente -->
        <div class="modal fade" id="editDeviceModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div id="editDeviceModalLabel" class="modalHeader centerWithFlex">
                  Update device
                </div>
				<form id="editDeviceForm" name="editDeviceForm" role="form" method="post" action="process-form.php" data-toggle="validator">
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
											<option value="MosquittoUNIMI">MosquittoUNIMI</option>
											<option value="OrionUNIFI">OrionUNIFI</option>
											<option value="OrionUNIFI">OrionUNIFI</option>
											<option value="Prova">Prova</option>
											<option value="RabbitUNIMI">RabbitUNIMI</option>
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
			
				
				<div id="editDeviceModalFooter" class="modal-footer">
                  <button type="button" id="editDeviceCancelBtn" class="btn cancelBtn" data-dismiss="modal">Cancel</button>
                  <button type="button" id="editDeviceConfirmBtn" class="btn confirmBtn internalLink" disabled="true">Confirm</button>
                </div>
				
				</form> 	
				
              </div>
            </div>
        </div>
        
		
       

	
		
		
		<!-- Modal di conferma cancellazione  device-->
        <div class="modal fade" id="modalDelDevice" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modalHeader centerWithFlex">
              Delete device
            </div>
            <form id="delDeviceForm" name="delDeviceForm" role="form" method="post" action="process-form.php" data-toggle="validator"> 
                <input type="hidden" id="deviceIdToDel" name="deviceIdToDel" />
                <input type="hidden" id="deviceToDelActive" name="deviceToDelActive" />
                <div id="delDeviceModalBody" class="modal-body modalBody">
                    <div class="row">
                        <div id="delDeviceNameMsg" class="col-xs-12 modalCell">
                            <div class="modalDelMsg col-xs-12 centerWithFlex">
                                Do you want to confirm cancellation of the following device?
                            </div>
                            <div id="deviceNameToDel"  class="modalDelObjName col-xs-12 centerWithFlex"></div> 
                        </div>
                    </div>
                    <div class="row" id="delDeviceOkMsg">
                        <div class="col-xs-12 centerWithFlex" id="succesMsg">Device deleted successfully</div>
                    </div>
                    <div class="row" id="delDeviceOkIcon">
                        <div class="col-xs-12 centerWithFlex"><i class="fa fa-thumbs-o-up" style="font-size:36px"></i></div>
                    </div>
                    <div class="row" id="delDeviceKoMsg">
                        <div class="col-xs-12 centerWithFlex" id="errorMsg">Error deleting device</div>
                    </div>
                    <div class="row" id="delDeviceKoMsg">
                        <div class="col-xs-12 centerWithFlex" id="errorIcon"><div class="col-xs-12 centerWithFlex"><i class="fa fa-thumbs-o-down" style="font-size:36px"></i></div></div>
                    </div>
                </div>
                <div id="delDeviceModalFooter" class="modal-footer">
                  <button type="button" id="delDeviceCancelBtn" class="btn cancelBtn" data-dismiss="modal">Cancel</button>
                  <button type="button" id="delDeviceBtn" name="delDeviceBtn" class="btn confirmBtn internalLink">Confirm</button>
                </div>
            </form>
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
        
        //var admin = "<?= $_SESSION['loggedRole'] ?>";
       // var existingPoolsJson = null;
        var internalDest = false;
        var tableFirstLoad = true;
        
        buildMainTable(false);
		
        //Settaggio dei globals per il file usersManagement.js
       // setGlobals(admin, existingPoolsJson);
        
            $('#addNewDeviceConfirmBtn').off("click");
			$("#addNewDeviceConfirmBtn").click(function(){
			$('#addDeviceModalTabs').hide();
			$('#addDeviceModal div.modalCell').hide();
            //$("#addDeviceModalBody").hide();
            $("#addDeviceModalFooter").hide();
            //$("#addDeviceModalCreating").show();
			$('#addDeviceLoadingMsg').show();
            $('#addDeviceLoadingIcon').show();
			
			
			newDeviceJson = {
                 inputNameDevice: $("#addDeviceForm #inputNameDevice").val(),
                 inputTypeDevice: $("#addDeviceForm #inputTypeDevice").val(),
                 selectContextBroker: $("#addDeviceForm #selectContextBroker").val(),
                 inputUriDevice: $("#addDeviceForm #inputUriDevice").val(),
                 selectProtocolDevice: $("#addDeviceForm #selectProtocolDevice").val(),
                 selectFormatDevice: $("#addDeviceForm #selectFormatDevice").val(),
                 createdDateDeviceM: $("#addDeviceForm #createdDateDevice").val(),
				 inputMacDevice: $("#addDeviceForm #inputMacDeviceM").val(),
                 inputModelDevice: $("#addDeviceForm #inputModelDevice").val(),
                 inputProducerDevice: $("#addDeviceForm #inputProducerDevice").val(),
                 inputLatitudeDevice: $("#addDeviceForm #inputLatitudeDevice").val(),
                 inputLongitudeDevice: $("#addDeviceForm #inputLongitudeDevice").val(),
                 inputPropertiesDevice: $("#addDeviceForm #inputPropertiesDevice").val(),
                 inputAttributesDevice: $("#addDeviceForm #inputAttributesDevice").val()
                
             };
			
	      
             //Chiamata API di inserimento nuovo utente
             $.ajax({
                 url: "process-form.php",
                 data:{newDeviceJson: JSON.stringify(newDeviceJson)},
                 type: "POST",
                 async: true,
                 success: function (data) 
                 {
                   if(data !== 'Ok')
                    {
                        console.log(data);
                        $('#addDeviceLoadingMsg').hide();
                        $('#addDeviceLoadingIcon').hide();
                        $('#addDeviceKoMsg').show();
                        $('#addDeviceKoIcon').show();
                        setTimeout(function(){
                            $('#addDeviceKoMsg').hide();
                            $('#addDeviceKoIcon').hide();
                            $('#addDeviceModalTabs').show();
                            $('#addDeviceModal div.modalCell').show();
                            $('#addDeviceModalFooter').show();
                        }, 3000);
                    }
                    else
                    {
                        $('#addDeviceLoadingMsg').hide();
                        $('#addDeviceLoadingIcon').hide();
                        $('#addDeviceOkMsg').show();
                        $('#addDeviceOkIcon').show();
                                                 
                        $('#dashboardTotNumberCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotNumberCnt .pageSingleDataCnt').html()) + 1);
                        $('#dashboardTotActiveCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotActiveCnt .pageSingleDataCnt').html()) + 1);
                                  
                        setTimeout(function(){
                            $('#addDeviceModal').modal('hide');
                            buildMainTable(true);

                            setTimeout(function(){
                                  $('#addDeviceOkMsg').hide();
                                  $('#addDeviceOkIcon').hide();
								  $('#inputNameDevice').val("");
								  $('#inputTypeDevice').val("");
								  $('#selectContextBroker').val("NULL");
								  $('#inputUriDevice').val("");
								  $('#selectProtocolDevice').val("NULL");
								  $('#selectFormatDevice').val("NULL");
								  $('#createdDateDevice').val("");
								  $('#inputMacDeviceM').val("");
								  $('#inputModelDevice').val("");
								  $('#inputProducerDevice').val("");
								  $('#inputLatitudeDevice').val("");
								  $('#inputLongitudeDevice').val("");
								  $('#inputPropertiesDevice').val("");
								  $('#inputAttributesDevice').val("");
								  $('#addDeviceModalTabs').show();
								  $('#addDeviceModal div.modalCell').show();
								  $('#addDeviceModalFooter').show();
                            }, 500);
                        }, 3000);
                    }
                },
                error: function(errorData)
                {
                    $('#addDeviceLoadingMsg').hide();
                    $('#addDeviceLoadingIcon').hide();
                    $('#addDeviceKoMsg').show();
                    $('#addDeviceKoIcon').show();
                    setTimeout(function(){
                        $('#addDeviceKoMsg').hide();
                        $('#addDeviceKoIcon').hide();
                        $('#addDeviceModalTabs').show();
                        $('#addDeviceModal div.modalCell').show();
                        $('#addDeviceModalFooter').show();
                    }, 3000);
                    console.log("Error adding device");
                    console.log(errorData);
                }
					
             });
        });
		
			$('#editDeviceConfirmBtn').off("click");
			$("#editDeviceConfirmBtn").click(function(){
			$('#editDeviceModalTabs').hide();
			$('#editDeviceModal div.modalCell').hide();
            //$("#editDeviceModalBody").hide();
            $("#editDeviceModalFooter").hide();
            //$("#editDeviceModalUpdating").show();
			$('#editDeviceLoadingMsg').show();
            $('#editDeviceLoadingIcon').show();
			
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

             console.log(JSON.stringify(deviceJson));

		
		//Chiamata API di aggiornamento account utente
             $.ajax({
                 url: "process-form.php",
                 data:{operation: "updateDevice", deviceJson: JSON.stringify(deviceJson)},
                 type: "POST",
                 async: true,
                 success: function (data) 
                 {
                    if(data !== 'Ok')
                    {
                   
                        console.log(data);
                        $('#editDeviceLoadingMsg').hide();
                        $('#editDeviceLoadingIcon').hide();
                        $('#editDeviceKoMsg').show();
                        $('#editDeviceKoIcon').show();
                        setTimeout(function(){
                            $('#editDeviceKoMsg').hide();
                            $('#editDeviceKoIcon').hide();
                            $('#editDeviceModalTabs').show();
                            $('#editDeviceModal div.modalCell').show();
                            $('#editDeviceModalFooter').show();
                        }, 3000);
                    }
                    else
                    {
                        $('#editDeviceLoadingMsg').hide();
                        $('#editDeviceLoadingIcon').hide();
                        $('#editDeviceOkMsg').show();
                        $('#editDeviceOkIcon').show();

                        setTimeout(function(){
                            $('#editDeviceModal').modal('hide');
                            buildMainTable(true);

                            setTimeout(function(){
                                  $('#editDeviceOkMsg').hide();
                                  $('#editDeviceOkIcon').hide();
                                  $('#inputNameDeviceM').val("");
								  $('#inputTypeDeviceM').val("");
								  $('#selectContextBrokerM').val("NULL");
								  $('#inputUriDeviceM').val("");
								  $('#selectProtocolDeviceM').val("NULL");
								  $('#selectFormatDeviceM').val("NULL");
								  $('#createdDateDeviceM').val("");
								  $('#inputMacDeviceM').val("");
								  $('#inputModelDeviceM').val("");
								  $('#inputProducerDeviceM').val("");
								  $('#inputLatitudeDeviceM').val("");
								  $('#inputLongitudeDeviceM').val("");
								  $('#inputPropertiesDeviceM').val("");
								  $('#inputAttributesDeviceM').val("");
			                      $('#editDeviceModalTabs').show();
                                  $('#editDeviceModal div.modalCell').show();
                                  $('#editDeviceModalFooter').show();
                            }, 500);
                        }, 3000);
                    }
                },
                error: function(errorData)
                {
                    $('#editDeviceLoadingMsg').hide();
                    $('#editDeviceLoadingIcon').hide();
                    $('#editDeviceKoMsg').show();
                    $('#editDeviceKoIcon').show();
                    setTimeout(function(){
                        $('#editDeviceKoMsg').hide();
                        $('#editDeviceKoIcon').hide();
                        $('#editDeviceModalTabs').show();
                        $('#editDeviceModal div.modalCell').show();
                        $('#editDeviceModalFooter').show();
                    }, 3000);
                    console.log("Error updating device");
                    console.log(errorData);
                }
             });
        });
		
		
						$('#delDeviceBtn').off("click");
						$('#delDeviceBtn').click(function(){
                        $('#delDeviceModalFooter').hide();
                        $.ajax({
                            url: "process-form.php",
                            data: {
                                deleteDevice: true,
                                metricId: $('#deleteIdToDel').val()
                            },
                            type: "POST",
                            async: true,
                            success: function(data)
                            {
                                if(data === "Ok")
                                {
                                    $('#delDeviceMsg').hide();
                                    $('#delDeviceNameMsg').hide();
                                    $('#delDeviceOkMsg').show();
                                    $('#delDeviceOkIcon').show();

                                    $('#devicesTable').bootstrapTable('removeByUniqueId', $('#deviceIdToDel').val());
                                    if($('#deviceToDelActive').val() === "true")
                                    {
                                        console.log("Vero");
                                        $('#dashboardTotActiveCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotActiveCnt .pageSingleDataCnt').html()) - 1);
                                    }
                                    else
                                    {
                                        console.log("Falso");
                                    }

                                    setTimeout(function(){
                                       $('#deleteDeviceModal').modal('hide');
                                       setTimeout(function(){
                                           $('#delDeviceOkMsg').hide();
                                           $('#delDeviceOkIcon').hide();
                                           $('#delDeviceMsg').show();
                                           $('#delDeviceNameMsg').show();
                                           $('#delDeviceModalFooter').show();
                                       }, 300);
                                    }, 2000);
                                }
                                else
                                {
                                    console.log("Error deleting device");
                                    console.log(data);
                                    $('#delDeviceMsg').hide();
                                    $('#delDeviceNameMsg').hide();
                                    $('#delDeviceKoMsg').show();
                                    $('#delDeviceKoIcon').show();
                                    setTimeout(function(){
                                       $('#deleteDeviceModal').modal('hide');
                                       setTimeout(function(){
                                           $('#delDeviceKoMsg').hide();
                                           $('#delDeviceKoIcon').hide();
                                           $('#delDeviceMsg').show();
                                           $('#delDeviceNameMsg').show();
                                           $('#delDeviceModalFooter').show();
                                       }, 300);
                                    }, 2000);
                                }
                            },
                            error: function(errorData)
                            {
                                console.log("Error updating device status");
                                console.log(errorData);
                                $('#delDeviceMsg').hide();
                                $('#delDeviceNameMsg').hide();
                                $('#delDeviceKoMsg').show();
                                $('#delDeviceKoIcon').show();
                                setTimeout(function(){
                                   $('#deleteDeviceModal').modal('hide');
                                   setTimeout(function(){
                                       $('#delDeviceKoMsg').hide();
                                       $('#delDeviceKoIcon').hide();
                                       $('#delDeviceMsg').show();
                                       $('#delDeviceNameMsg').show();
                                       $('#delDeviceModalFooter').show();
                                   }, 300);
                                }, 2000);
                            }
                        });
                    });
		

        
		
		
		
		
		 function buildMainTable(destroyOld)
        {
            if(destroyOld)
            {
                $('#devicesTable').bootstrapTable('destroy');
                tableFirstLoad = true;
            }
            
			var descVisibile = true;
            var typeVisible = true;
            var sourceVisibile = true;
            var datasourceVisibile = true;
            var statusVisibile = true;

            if($(window).width() < 992)
            {
                descVisibile = false;
                typeVisible = false; 
                sourceVisibile = false;
                datasourceVisibile = false;
                statusVisibile = false;
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
                   // var statusVisibile = true;
                    
                    if($(window).width() < 992)
                    {
                        detailView = false;
                        creatorVisibile = false; 
                       // statusVisibile = false;
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
                                "data-attributes": row.attributes,
                              
                            };
							},
					});
				}, //sucess
				
							onPostBody: function()
                            {
                                if(tableFirstLoad)
                                {
                                    //Caso di primo caricamento della tabella
                                    tableFirstLoad = false;
                                    var addDeviceDiv = $('<div class="pull-right"><i id="link_add_device" data-toggle="modal" data-target="#addDeviceModal" class="fa fa-plus-square" style="font-size:36px; color: #ffcc00"></i></div>');
                                    
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
                                    //$("#link_add_device").off("click");
                                    //$("#link_add_device").click(showAddUserModal);
                                    $('#devicesTable thead').css("background", "rgba(0, 162, 211, 1)");
                                    $('#devicesTable thead').css("color", "white");
                                    $('#devicesTable thead').css("font-size", "1em");
                                }
                                else
                                {
                                    //Casi di cambio pagina
                                }

                                //Istruzioni da eseguire comunque
                                $('#devicesTable').css("border-bottom", "none");
                                $('span.pagination-info').hide();

                                $('#devicesTable button.editDashBtn').off('hover');
                                $('#devicesTable button.editDashBtn').hover(function(){
                                    $(this).css('background', '#ffcc00');
                                    $(this).parents('tr').find('td').eq(1).css('background', '#ffcc00');
                                }, 
                                function(){
                                    $(this).css('background', 'rgb(69, 183, 175)');
                                    $(this).parents('tr').find('td').eq(1).css('background', $(this).parents('td').css('background'));
                                });
								
								
								$('#devicesTable button.delDashBtn').off('hover');
								$('#devicesTable button.delDashBtn').hover(function(){
									$(this).css('background-color', '#ffcc00');
									$(this).parents('tr').find('td').eq(1).css('background', '#ffcc00');
								}, 
								function(){
									$(this).css('background-color', '#e37777');
									$(this).parents('tr').find('td').eq(1).css('background', $(this).parents('td').css('background'));
								});
								
								$('#devicesTable button.editDashBtn').off('click');
                                $('#devicesTable button.editDashBtn').click(function(){
									
									var deviceName = $(this).parents('tr').attr('data-UniqueDeviceName');
									
                                    $.ajax({
                                        url: "get_data.php",
                                        data: {
											
											deviceName: deviceName,
											action: "get_param_device", 
												
										},
                                        type: "GET",
                                        async: true,
                                        dataType: 'json',
                                        success: function (data) 
                                        {
                                        if(data.result === 'Ok')
                                        {
                                            $('#inputNameDeviceM').val($(this).parents('tr').attr('data-name'));
											$('#inputTypeDeviceM').val($(this).parents('tr').attr('data-type'));
											$('#selectContextBrokerM').val($(this).parents('tr').attr('data-contextBroker'));
											$('#inputUriDeviceM').val($(this).parents('tr').attr('data-uri'));
											$('#selectProtocolDeviceM').val($(this).parents('tr').attr('data-protocol'));
											$('#createdDateDeviceM').val($(this).parents('tr').attr('data-created'));
											$('#inputMacDeviceM').val($(this).parents('tr').attr('data-macaddress'));
											$('#inputModelDeviceM').val($(this).parents('tr').attr('data-model'));
											$('#inputProducerDevice').val($(this).parents('tr').attr('data-producer'));
											$('#inputLatitudeDeviceM').val($(this).parents('tr').attr('data-latitude'));
											$('#inputLongitudeDeviceM').val($(this).parents('tr').attr('data-longtitude'));
											$('#inputPropertiesDeviceM').val($(this).parents('tr').attr('data-properties'));
											$('#inputAttributesDeviceM').val($(this).parents('tr').attr('data-attributes'));

                                            $('#editDeviceModal').modal('show');
                                        }
                                        else
                                        {
                                            console.log("Error retrieving device data");
                                            console.log(JSON.stringify(errorData));
                                            alert("Error retrieving device data");
                                        }

                                    },
                                    error: function(errorData)
                                    {
                                        console.log("Error retrieving device data");
                                        console.log(JSON.stringify(errorData));
                                        alert("Error retrieving device data");
                                    }
								});
							});

                                
								$('#devicesTable tbody button.delDashBtn').off('click');
								$('#devicesTable tbody button.delDashBtn').on('click', function () {
                                $('#deviceIdToDel').val($(this).parents('tr').attr('data-UniqueDeviceName'));
                                $('#deviceNameToDel').html($(this).parents('tr').find('td').eq(1).text());
                                $('#deleteDeviceModal').modal('show');
                            });
					
					}
				
				
				
				
				
				
				
				
				
				
				
			});
			
			
			
			
			
		}	
			
			
			  
			
			
			
			
			
			
			
		
		   
    });
</script>  