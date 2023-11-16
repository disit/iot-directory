<?php
/* Snap4City: IoT-Directory
  Copyright (C) 2022 DISIT Lab https://www.disit.org - University of Florence

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
if (isset($_REQUEST['showFrame'])) {
    if ($_REQUEST['showFrame'] == 'false') {
        $hide_menu = "hide";
    } else {
        $hide_menu = "";
    }
} else
    $hide_menu = "";
//// SHOW FRAME PARAMETER  ////

if (!isset($_GET['pageTitle'])) {
    $default_title = "FIWARE Models";
} else {
    $default_title = "";
}

if (isset($_REQUEST['redirect'])) {
    $access_denied = "denied";
} else {
    $access_denied = "";
}

$link = mysqli_connect($host, $username, $password);
mysqli_select_db($link, $dbname);

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
        <link href="https://cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/js/bootstrap-editable.min.js"></script>

        <!-- Bootstrap table -->
        <link rel="stylesheet" href="../boostrapTable/dist/bootstrap-table.css">
        <script src="../boostrapTable/dist/bootstrap-table.js"></script>
        <script src="../boostrapTable/dist/bootstrap-table-filter-control.js"></script>

        <!-- select2 -->
        <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
        <script src="select2.js"></script>
<script src="select2-searchInputPlaceholder.js"></script>

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
        <link href="../bootstrapSlider/css/bootstrap-slider.css" rel="stylesheet" />

        <!-- Filestyle -->
        <script src="../js/filestyle/src/bootstrap-filestyle.min.js"></script>

        <!-- utf8 -->
        <script src="js/utf8.js"></script>


        <!--     Custom CSS 
            <link href="../css/dashboard.css" rel="stylesheet">
            <link href="../css/bulkDeviceLoad.css" rel="stylesheet">-->

        <!-- Custom CSS -->
        <?php include "theme-switcher.php"?>
        
        <script>
            var loggedRole = "<?php echo $_SESSION['loggedRole']; ?>";
            var loggedUser = "<?php echo $_SESSION['loggedUsername']; ?>";
            var admin = "<?php echo $_SESSION['loggedRole']; ?>";
            var organization = "<?php echo $_SESSION['organization']; ?>";
            var kbUrl = "<?php echo $_SESSION['kbUrl']; ?>";
            var EndPointMakeRule = "<?php echo $GLOBALS['EndPointMakeRuleFiware']; ?>";
            
            var gpsCentreLatLng = "<?php echo $_SESSION['gpsCentreLatLng']; ?>";
            var zoomLevel = "<?php echo $_SESSION['zoomLevel']; ?>";
            var titolo_default = "<?php echo $default_title; ?>";
            var access_denied = "<?php echo $access_denied; ?>";
            var nascondi = "<?php echo $hide_menu; ?>";
            var sessionEndTime = "<?php echo $_SESSION['sessionEndTime']; ?>";
            var sessionToken = "<?php
                if (isset($_SESSION['refreshToken']))
                    echo $_SESSION['refreshToken'];
                else
                    echo "";
                ?>";
            var mypage = location.pathname.split("/").slice(-1)[0];
            var functionality = [];
            var currentDictionaryStaticAttribEdit = [];
        </script>

        <!-- Custom scripts -->
        <script src="js/FIWAREModel_script.js"></script>
        <script src="js/common.js"></script>
        <script src="js/associationRules.js"></script>
        <script src="js/modelEditManagement.js"></script>
        <script src="js/modelManagement.js"></script>
        <script src="../js/dashboard_mng.js"></script>

        <!-- leaflet scripts -->
        <script src="../js/leaflet.js"></script>
        <script src="../js/leaflet.draw.js"></script>
        <script src="../js/jquery.fancytree-all.min.js"></script>


    </head>
    <body class="guiPageBody IOTdevices">
        <div class="container-fluid">
            <?php include "sessionExpiringPopup.php" ?> 
            <div class="row mainRow"> 
                <div 
                <?php
                //MM201218
                if (($hide_menu == "hide")) {
                    ?>
                        class="col-xs-12 col-md-12" 
                    <?php } else { ?>
                        class="col-xs-12 col-md-10" 
                    <?php } //MM201218 FINE   ?>
                    id="mainCnt">
                    <div class="row hidden-md hidden-lg">
                        <div id="mobHeaderClaimCnt" class="col-xs-12 hidden-md hidden-lg centerWithFlex">
                            Snap4City
                        </div>
                    </div>
                    <?php
//MM201218
                    if (($hide_menu != "hide")) {
                        ?>
                        <div class="row" id="title_row">
                            <div class="col-xs-10 col-md-12 centerWithFlex" id="headerTitleCnt">FIWARE Models</div>
                            <div class="col-xs-2 hidden-md hidden-lg centerWithFlex" id="headerMenuCnt"></div> 
                        </div>
                    <?php } //MM201218 FINE    ?>

                    <div class="row">
                        <div class="col-xs-12" id="mainContentCntIot">

                            <div>
                                <div id="addMyNewDeviceRow" class="row mainContentRow">
                                    <table id="FIWAREModelTable" class="addWidgetWizardTable table table-striped dt-responsive nowrap dataTable no-footer dtr-inline" cellspacing="0" width="100%">
                                        <thead class="dataTableHeadColTitle">
                                            <tr>

                                                <th data-cellTitle="Name">Name</th>

                                                <th data-cellTitle="Subdomain">Subdomain</th>
                                                <th data-cellTitle="Domain">Domain</th>
                                                <th data-cellTitle="Version">Version</th>
                                                
                                                <th 
                                                    
                                                <?php if($_SESSION['loggedRole']=='RootAdmin' || $_SESSION['loggedRole']=='ToolAdmin'){?>
                                                    data-cellTitle="Edit"
                                              
                                               <?php }else{?>
                                                   data-cellTitle="View" 
                                              <?php  }?>
                                                    >
                                                     <?php if($_SESSION['loggedRole']=='RootAdmin' | $_SESSION['loggedRole']=='ToolAdmin'){?>
                                                    Edit
                                              
                                               <?php }else{?>
                                                  View
                                              <?php  }?>
                                                
                                               
                                               
                                                </th> 
                                            </tr>
                                        </thead>
                                    </table>
                                </div>  
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

                            <div id="editModelModalBody">

                                <ul id="editModelModalTabs" class="nav nav-tabs nav-justified">
                                    <li class="active"><a data-toggle="tab" href="#editInfoTabModel">General Info</a></li>
                                    <li><a data-toggle="tab" href="#editSchemaTabModel">Values</a></li>

                                </ul>



                                <div class="tab-content">

                                    <!-- General Info tab -->
                                    <div id="editInfoTabModel" class="tab-pane fade in active">

                                        <div class="row">
                                            <div class="col-xs-12 col-md-6 modalCell">
                                                <div class="modalFieldCnt">
                                                    <input type="text" class="modalInputTxt" name="inputNameModel" id="inputNameModel" readonly>
                                                </div>
                                                <div class="modalFieldLabelCnt" >Name Model</div>
                                                <div id="inputTypeModelMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                            </div>
                                            <div class="col-xs-12 col-md-6 modalCell">
                                                <div class="modalFieldCnt">
                                                    <input type="text" class="modalInputTxt" name="inputVersionModel" id="inputVersionModel" readonly>
                                                </div>
                                                <div class="modalFieldLabelCnt">Version</div>
                                                <div id="inputFrequencyModelMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                            </div>


                                        </div>


                                        <div class="row">
                                            <div class="col-xs-12 col-md-6 modalCell">
                                                <div class="modalFieldCnt">
                                                    <input type="text" class="modalInputTxt" name="inputDomaninModel" id="inputDomaninModel" readonly>
                                                </div>
                                                <div class="modalFieldLabelCnt">Domain</div>
                                                <div id="inputNameModelMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                            </div>

                                            <div class="col-xs-12 col-md-6 modalCell">
                                                <div class="modalFieldCnt">
                                                    <input type="text" class="modalInputTxt" name="inputSubdomaninModel" id="inputSubdomaninModel" readonly>
                                                </div>
                                                <div class="modalFieldLabelCnt">Subdomain</div>
                                                <div id="inputDescriptionModelMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                            </div>
                                        </div>
                                        <div class="row">
								<div class="col-xs-12 col-md-8 modalCell">
									<div class="modalFieldCnt">
										<div class="modalFieldLabelCnt">Subnature</div>
										<select id="selectSubnature" name="selectSubnature" class="modalInputTxt">
											<option></option>
										</select>
									</div>
								</div>
							</div>
                                        
<!--                                        <div class="row">
                                        
                                        <div class="col-xs-12 col-md-6 modalCell">
                                                <div class="modalFieldCnt">
                                                    <input type="text" class="modalInputTxt" name="inputKindDeviceModel" id="inputKindDeviceModel" readonly>
                                                </div>
                                                <div class="modalFieldLabelCnt">Kind</div>
                                                <div id="inputDescriptionModelMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                            </div>
                                        </div>-->



                                    </div>



                                    <!-- Attribute tab -->
                                    <div id="editSchemaTabModel" class="tab-pane fade">

                                        <div id="editlistAttributes"></div>
                                        <div id="addlistAttributesM"></div>
                                        <div id="deletedAttributes" style="display:none"></div>
                                        <!--<div class="pull-left"><button id="addAttrMBtn" class="btn btn-primary" style="display:none">Add Value</button></div>-->
                                        <div id="editlistAttributesMsg" class="modalFieldMsgCnt">&nbsp;</div>
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
                                <?php
                                $role = $_SESSION['loggedRole'];
                                if (($role == 'RootAdmin' ) || ($role == 'ToolAdmin')) {
                                    echo '<button type="button" id="editModelConfirmBtn" class="btn confirmBtn internalLink">Confirm</button>';
                                }
                                ?>

                                <button type="button" id="editModelOkBtn" class="btn cancelBtn" data-dismiss="modal" style="display:none;">Ok</button>
                            </div>

                        </div>
                    </div>
                </div>






                </body>
                </html>		




