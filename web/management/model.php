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
		$default_title = "IoT Directory: Models";
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
        <script type="text/javascript" src="../js/filestyle/src/bootstrap-filestyle.min.js"></script>

       <!-- Font awesome icons -->
        <link rel="stylesheet" href="../js/fontAwesome/css/font-awesome.min.css">

        <link href="https://fonts.googleapis.com/css?family=Cabin:400,500,600,700|Catamaran|Varela+Round" rel="stylesheet">
        
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
        
        <!-- Custom scripts -->
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
 var creatorVisibile = true;
 var detailView = true;
 //var statusVisibile = true;
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
<script type="text/javascript" src="js/model.js"></script>
<script type="text/javascript" src="js/modelManagement.js"></script>
<script type="text/javascript" src="js/modelEditManagement.js"></script>
<script type="text/javascript" src="js/fieldsManagement.js"></script>

        
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
                            Snap4City IoT Directory
                        </div>
                    </div>
					<?php //MM201218
					if (($hide_menu!="hide")) {?>
                    <div class="row" id="title_row">
                        <div class="col-xs-10 col-md-12 centerWithFlex" id="headerTitleCnt">IoT Directory: Models</div>
                        <div class="col-xs-2 hidden-md hidden-lg centerWithFlex" id="headerMenuCnt"><!--php include "mobMainMenu.php" ?--></div>
                    </div>
					<?php } //MM201218 FINE ?>
					
                    <div class="row">
                        <div class="col-xs-12" id="mainContentCnt">
                            <div id="synthesis" class="row hidden-xs hidden-sm mainContentRow">
                                <div class="col-xs-12 mainContentRowDesc"></div>
								
                                <div id="dashboardTotNumberCnt" class="col-md-3 mainContentCellCnt">
                                    <div class="col-md-12 centerWithFlex pageSingleDataCnt">
                                        <?php
                                            $query = "SELECT count(*) AS qt FROM model";
                                            $result = mysqli_query($link, $query);
                                            
                                            if($result)
                                            {
                                               $row = $result->fetch_assoc();                                
                                               echo $row['qt'] . ' Models';
                                            }
                                            else
                                            {
                                                echo '-' . ' Models';
                                            }
                                        ?>
                                    </div>
                                   
                                </div>
                               
                              </div>
                            <div class="row mainContentRow">
                                <div class="col-xs-12 mainContentRowDesc"></div>
                                <div class="col-xs-12 mainContentCellCnt">
                                   
								<div class="row" style= "background-color: rgb(241, 245, 244);">
									<div class="col-xs-12 col-md-6 modalCell" style= "background-color: rgb(241, 245, 244);">
									<div class="pull-right"></div>
									</div>
									<div class="col-xs-12 col-md-6 modalCell" style= "background-color: rgb(241, 245, 244);">
									<?php if ($_SESSION['loggedRole']=='RootAdmin'  || $_SESSION['loggedRole']=='ToolAdmin' || $_SESSION['loggedRole']=='AreaManager') { ?>
									<div class="pull-right"><button id="addModelBtn"  class="btn btn-primary">New Model</button></div>
									<?php } ?>
									</div>
								</div>
								   
								<div>
									 <table id="modelTable" class="table table-bordered table-striped" cellspacing="0" width="100%">
									 <thead>
									  <tr>
									 <th></th>	
									   <th data-cellTitle="name">Device Model</th>
										<th data-cellTitle="description">Description</th>
                                        <th data-cellTitle="ownership">Ownership</th>
										<th data-cellTitle="organization">Organization</th>
										<th data-cellTitle="owner">Owner</th>
										<th data-cellTitle="kind">Kind</th>
										<th data-cellTitle="producer">Producer</th>
										<th data-cellTitle="devicetype">Device Type</th>
										<th data-cellTitle="edit">Edit</th>
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
  
	   
	   
	    <!-- Adding a New Model -->
        <div class="modal fade" id="addModelModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
              <div class="modal-content">
                <div class="modalHeader centerWithFlex">
                  Add New Model
                </div>
        
					<div id="addModelModalBody" class="modal-body modalBody">
				
                
					<ul id="addModelModalTabs" class="nav nav-tabs nav-justified">
						<li class="active"><a data-toggle="tab" href="#addInfoTabModel">General Info</a></li>
                        <li><a data-toggle="tab" href="#addIOTBrokerTabModel">IOT Broker</a></li>
                        <li><a data-toggle="tab" href="#addSchemaTabModel">Values</a></li>
						
                    </ul>
                    
					
                    <div class="tab-content">
                       
                        <!-- General Info tab -->
                        <div id="addInfoTabModel" class="tab-pane fade in active">
                            <div class="row">
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputNameModel" id="inputNameModel" onkeyup="checkStrangeCharacters(this)" required> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Name</div>
									<div id="inputNameModelMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
								
								 <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputDescriptionModel" id="inputDescriptionModel"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Description</div>
									<div id="inputDescriptionModelMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
							</div>
							<div class="row">
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputTypeModel" id="inputTypeModel"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Device Type</div>
									<div id="inputTypeModelMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>

                               <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
										<select id="selectKindModel" name="selectKindModel" class="modalInputTxt">
											<option value="sensor">Sensor</option>
											<option value="actuator">Actuator</option>
										</select>
                                    </div>
                                    <div class="modalFieldLabelCnt">Kind</div>
									<div id="selectKindModelMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>			
						   </div>
							<div class="row">
								<div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputProducerModel" id="inputProducerModel"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Producer</div>
									<div id="inputProducerModelMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
								
								<div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputFrequencyModel" id="inputFrequencyModel" value="600"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Frequency</div>
									<div id="inputFrequencyModelMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
							</div>	
							
							<div class="row">
								<div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <select id="selectHCModel" name="selectHCModel" class="modalInputTxt">
											<option value="refresh_rate">Refresh Rate</option>
											<option value="different_values">Different Value</option>
											<option value="value_bounds">Value Bounds</option>
										</select>
                                    </div>
                                    <div class="modalFieldLabelCnt">Healthiness Criteria</div>
									<div id="selectHCModelMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
                               
							   <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputHVModel" id="inputHVModel" value='300'> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Healthiness Value</div>
									<div id="inputHVModelMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
							   
							</div>  
							 <div class="row">
							 <!--
							   <div class="col-xs-12 col-md-6 modalCell">
									<div class="modalFieldCnt">
                                        <select id="selectPolicyModel" name="selectPolicyModel" class="modalInputTxt">
											<option></option>
											<option value="basic">Basic</option>
											<option value="advances">Advanced</option>
										</select>
                                    </div>
									<div class="modalFieldLabelCnt">Policy</div>
									<div id="selectPolicyModelMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
								
								-->
								
								
								<div class="col-xs-12 col-md-6 modalCell">
									<div class="modalFieldCnt">
                                        <select id="selectKGeneratorModel" name="selectKGeneratorModel" class="modalInputTxt">
											<option value="normal" selected="selected">Automatically generated</option>
											<option value="special">User generated</option>
											<option value="authenticated">Automatically generated with device authentication</option>
										</select>
                                    </div>
									<div class="modalFieldLabelCnt">Key Generation</div>
									<div id="selectKGeneratorModelMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
				<div class="col-xs-12 col-md-6 modalCell">
					<div class="modalFieldCnt">
						<select name="selectEdgeGatewayType" id="selectEdgeGatewayType" class="modalInputTxt">
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
					<div id="selectEdgeGatewayTypeMsg" class="modalFieldMsgCnt">&nbsp;</div>
				</div>
								
								
							</div>
							
					
								
                        </div>
                        
                        <!-- IOT Broker tab -->
                        <div id="addIOTBrokerTabModel" class="tab-pane fade">
						
			              <div class="row">
								<div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <select id="selectContextBroker" name="selectContextBroker" class="modalInputTxt">
										<option></option>

                                         <!--?php
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
                                        ?-->
										</select>
                                    </div>
                                    <div class="modalFieldLabelCnt">ContextBroker</div>
								<div id="selectContextBrokerMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
								
							   <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <select id="selectProtocolModel" name="selectProtocolModel" class="modalInputTxt">
											<option></option>
											<option value="amqp">amqp</option>
											<option value="coap">coap</option>
											<option value="mqtt">mqtt</option>
											<option value="ngsi">ngsi</option>
										</select>
                                    </div>
                                    <div class="modalFieldLabelCnt">Protocol</div>
									<div id="selectProtocolModelMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
                            
							</div>	
								
							<div class="row">
                             
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <select id="selectFormatModel" name="selectFormatModel" class="modalInputTxt">
											<option></option>
											<option value="csv">csv</option>
											<option value="json">json</option>
											<option value="xml">xml</option>
										</select>
                                    </div>
                                    <div class="modalFieldLabelCnt">Format</div>
									<div id="selectFormatModelMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
								<!--
								<div class="col-xs-12 col-md-6 modalCell">
                                     
									<div class="modalFieldCnt">
                                        <select id="selectActiveModel" name="selectActiveModel" class="modalInputTxt">
											<option value="1">On</option>
											<option value="0">Off</option>
										</select>
                                    </div>
                                    <div class="modalFieldLabelCnt">Active</div>
                                    <div id="selectActiveModelMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
								-->
								
							</div>  

					
                        </div>
                        
             
						                        
                        <!-- Device Schema tab -->
                        <div id="addSchemaTabModel" class="tab-pane fade">
							<div id="addlistAttributes"></div>
							<div class="pull-left"><button id="addAttrBtn" class="btn btn-primary">Add Value</button></div>
							<div id="addlistAttributesMsg" class="modalFieldMsgCnt">&nbsp;</div>
		                </div>
							
                    </div>
					
                  </div>
					   
                  
                  <div class="row" id="addModelLoadingMsg">
                        <div class="col-xs-12 centerWithFlex">Adding Model, please wait</div>
                    </div>
                  <div class="row" id="addModelLoadingIcon">
                        <div class="col-xs-12 centerWithFlex"><i class="fa fa-circle-o-notch fa-spin" style="font-size:36px;"></i></div>
                    </div>
                  <div class="row" id="addModelOkMsg">
                        <div class="col-xs-12 centerWithFlex">Model added successfully</div>
                    </div>
                  <div class="row" id="addModelOkIcon">
                        <div class="col-xs-12 centerWithFlex"><i class="fa fa-thumbs-o-up" style="font-size:36px"></i></div>
                    </div>
                  <div class="row" id="addModelKoMsg">
					    <div class="col-xs-12 centerWithFlex"></div>
                        <div class="col-xs-12 centerWithFlex">Error adding model</div>
                    </div>
                  <div class="row" id="addModelKoIcon">
                        <div class="col-xs-12 centerWithFlex"><i class="fa fa-thumbs-o-down" style="font-size:36px"></i></div>
                    </div>
				
         		<div id="addModelModalFooter" class="modal-footer">
                  <button type="text" id="addNewModelCancelBtn" class="btn cancelBtn" data-dismiss="modal">Cancel</button>
                  <button type="button" id="addNewModelOkBtn" class="btn cancelBtn" data-dismiss="modal" style="display:none">Ok</button>
                  <button type="text" id="addNewModelConfirmBtn" name="addNewModelConfirmBtn" class="btn confirmBtn internalLink">Confirm</button>	  
				</div>
				
              </div>
            </div>
        </div>


	 
	
        <!-- Update Model -->
        <div class="modal fade" id="editModelModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog  modal-lg" role="document">
              <div class="modal-content">
                <div id="editModelModalLabel" class="modalHeader centerWithFlex">
                  Update Model
                </div>
        
                <div id="editModelModalBody" class="modal-body modalBody">
                    
                     <ul id="editModelModalTabs" class="nav nav-tabs nav-justified">
						<li class="active"><a data-toggle="tab" href="#editInfoTabModel">General Info</a></li>
                        <li><a data-toggle="tab" href="#editIOTBrokerTabModel">IoT Broker</a></li>
                        <li><a data-toggle="tab" href="#editSchemaTabModel">Values</a></li>
						
                    </ul>
                    
                    
                    
                    <div class="tab-content">
                       
                         <!-- General Info tab -->
                        <div id="editInfoTabModel" class="tab-pane fade in active">
						
						
						 <div class="row">
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputNameModelM" id="inputNameModelM" onkeyup="checkStrangeCharacters(this)" required> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Name</div>
									<div id="inputNameModelMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
								
								 <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputDescriptionModelM" id="inputDescriptionModelM"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Description</div>
									<div id="inputDescriptionModelMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
							</div>
							<div class="row">
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputTypeModelM" id="inputTypeModelM"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Device Type</div>
									<div id="inputTypeModelMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>

								<div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
										<select id="selectKindModelM" name="selectKindModelM" class="modalInputTxt">
											<option value="sensor">Sensor</option>
											<option value="actuator">Actuator</option>
										</select>
                                    </div>
                                    <div class="modalFieldLabelCnt">Kind</div>
									<div id="selectKindModelMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>	
						   </div>
							<div class="row">
								<div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputProducerModelM" id="inputProducerModelM"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Producer</div>
									<div id="inputProducerModelMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
								
								<div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputFrequencyModelM" id="inputFrequencyModelM" value="0"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Frequency</div>
									<div id="inputFrequencyModelMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
							</div>	
							
							<div class="row">
								<div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <select id="selectHCModelM" name="selectHCModelM" class="modalInputTxt">
											<option value="refresh_rate">Refresh Rate</option>
											<option value="different_values">Different Value</option>
											<option value="value_bounds">Value Bounds</option>
										</select>
                                    </div>
                                    <div class="modalFieldLabelCnt">Healthiness Criteria</div>
									<div id="selectHCModelMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
                               
							   <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputHVModelM" id="inputHVModelM"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Healthiness Value</div>
									<div id="inputHVModelMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
							   
							</div>  
							 <div class="row">
							 
							 <!--
							   <div class="col-xs-12 col-md-6 modalCell">
									<div class="modalFieldCnt">
                                        <select id="selectPolicyModelM" name="selectPolicyModelM" class="modalInputTxt">
											<option value="basic" selected="selected">Basic</option>
											<option value="advances">Advanced</option>
										</select>
                                    </div>
									<div class="modalFieldLabelCnt">Policy</div>
									<div id="selectPolicyModelMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
								-->
								<div class="col-xs-12 col-md-6 modalCell">
									<div class="modalFieldCnt">
                                        <select id="selectKGeneratorModelM" name="selectKGeneratorModelM" class="modalInputTxt">
											<option value="normal" selected="selected">Automatically generated</option>
											<option value="special">User generated</option>
											<option value="authenticated">Automatically generated with device authentication</option>

										</select>
                                    </div>
									<div class="modalFieldLabelCnt">Key Generation</div>
									<div id="selectKGeneratorModelMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
								
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
                                         <input type="text" class="modalInputTxt" name="inputIdModelM" id="inputIdModelM" hidden>
                                         <input type="text" class="modalInputTxt" name="inputOrganizationModelM" id="inputOrganizationModelM" hidden>
                                    </div>
                                     <div class="modalFieldLabelCnt"></div>
                                     
                                     <div id="inputIdModelMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
							</div>
                        </div>
                        
                        <!-- IOT Broker tab -->
                        <div id="editIOTBrokerTabModel" class="tab-pane fade">
			              <div class="row">
								<div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <select id="selectContextBrokerM" name="selectContextBrokerM" class="modalInputTxt">
										<option></option>

                                         <!--?php
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
                                        ?-->
										</select>
                                    </div>
                                    <div class="modalFieldLabelCnt">ContextBroker</div>
								<div id="selectContextBrokerMMsg" class="modalFieldMsgCnt">&nbsp;</div>
							</div>
								
								 <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <select id="selectProtocolModelM" name="selectProtocolModelM" class="modalInputTxt">
											<option></option>
											<option value="amqp">amqp</option>
											<option value="coap">coap</option>
											<option value="mqtt">mqtt</option>
											<option value="ngsi">ngsi</option>
										</select>
                                    </div>
                                    <div class="modalFieldLabelCnt">Protocol</div>
									<div id="selectProtocolModelMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
								
								<!--
                                <div class="col-xs-12 col-md-6 modalCell">
                                     
                                     <div class="modalFieldCnt">
                                        <select id="selectActiveModelM" name="selectActiveModelM" class="modalInputTxt">
											<option value="1">On</option>
											<option value="0">Off</option>
										</select>
                                    </div>
                                    <div class="modalFieldLabelCnt">Active</div>
                                    <div id="selectActiveModelMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
								-->
								
							</div>	
								
							<div class="row">
                               
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <select id="selectFormatModelM" name="selectFormatModelM" class="modalInputTxt">
											<option></option>
											<option value="csv">csv</option>
											<option value="json">json</option>
											<option value="xml">xml</option>
										</select>
                                    </div>
                                    <div class="modalFieldLabelCnt">Format</div>
									<div id="selectFormatModelMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
							</div>  

						
						
				
				 
                        </div>
                        
                        <!-- Attribute tab -->
                        <div id="editSchemaTabModel" class="tab-pane fade">
                           
							<div id="editlistAttributes"></div>
							<div id="addlistAttributesM"></div>
							<div id="deletedAttributes" style="display:none"></div>
							<!-- <div class="pull-left"><i id="addAttrMBtn" class="fa fa-plus-square" style="font-size:36px; color: #ffcc00"></i></div> -->
							<div class="pull-left"><button id="addAttrMBtn" class="btn btn-primary">Add Value</button></div>
                        </div>
					
						
                    </div>
					</div>
					
					<div class="row" id="editModelLoadingMsg">
                        <div class="col-xs-12 centerWithFlex">Updating model, please wait</div>
                    </div>
                    <div class="row" id="editModelLoadingIcon">
                        <div class="col-xs-12 centerWithFlex"><i class="fa fa-circle-o-notch fa-spin" style="font-size:36px;"></i></div>
                    </div>
                    <div class="row" id="editModelOkMsg">
                        <div class="col-xs-12 centerWithFlex">Model updated successfully</div>
                    </div>
                    <div class="row" id="editModelOkIcon">
                        <div class="col-xs-12 centerWithFlex"><i class="fa fa-thumbs-o-up" style="font-size:36px"></i></div>
                    </div>
                    <div class="row" id="editModelKoMsg">
                        <div class="col-xs-12 centerWithFlex">Error updating model</div>
                    </div>
                    <div class="row" id="editModelKoIcon">
                        <div class="col-xs-12 centerWithFlex"><i class="fa fa-thumbs-o-down" style="font-size:36px"></i></div>
                    </div>
		            
                
				<div id="editModelModalFooter" class="modal-footer">
                  <button type="button" id="editModelCancelBtn" class="btn cancelBtn" data-dismiss="modal">Cancel</button>
                  <button type="button" id="editModelConfirmBtn" class="btn confirmBtn internalLink" >Confirm</button>
                  <button type="button" id="editModelOkBtn" class="btn cancelBtn" data-dismiss="modal" style="display:none;" >Ok</button>
                </div>
	
              </div>
            </div>
        </div>


	
		
		      
		
		<div class="modal fade" id="deleteModelModal" tabindex="-1" role="dialog" aria-labelledby="deleteModelModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="deleteModelModalLabel">Model deletion</h5>
                </div>
                <div class="modal-body">

                </div>
                  
                  <div id="deleteModelModalInnerDiv1" class="modalBodyInnerDiv" style="display: none;"><h5>Model deletion in progress, please wait</h5></div>
                    <div id="deleteModelModalInnerDiv2" class="modalBodyInnerDiv" style="display: none;"><i class="fa fa-circle-o-notch fa-spin" style="font-size:36px"></i></div>
                  
                <div class="modal-footer">
                    <button type="button" id="deleteModelOkBtn" class="btn btn-primary" data-dismiss="modal" style="display: none;">Ok</button>
                  <button type="button" id="deleteModelCancelBtn" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                  <button type="button" id="deleteModelConfirmBtn" class="btn btn-primary">Confirm</button>
                </div>
              </div>
            </div>
        </div>
		
       
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



		
		

