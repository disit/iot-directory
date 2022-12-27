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
    $default_title = "IoT Directory: List of Devices";
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

        <!-- Font awesome icons -->
        <link rel="stylesheet" href="../js/fontAwesome/css/font-awesome.min.css">

        <!-- Custom CSS -->
        <link href="../css/dashboard.css" rel="stylesheet">
        <link href="../css/bulkDeviceLoad.css" rel="stylesheet">

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
        <script src="js/associationRules.js"></script>
        <script src="js/common.js"></script>
        <script src="js/devicesManagement.js"></script>
        <script src="js/devicesEditManagement.js"></script>
        <script src="../js/dashboard_mng.js"></script>

        <!-- leaflet scripts -->
        <script src="../js/leaflet.js"></script>
        <script src="../js/leaflet.draw.js"></script>
        <script src="../js/jquery.fancytree-all.min.js"></script>

        <style>
            .labelinput {
                padding: 10px;
                background: red;
                display: table;
                color: #fff;
            }

            input[type="file"] {
                display: none;
            }

            /* The Modal (background) */
            .progress-modal {
                display: none;
                /* Hidden by default */
                position: fixed;
                /* Stay in place */
                z-index: 1;
                /* Sit on top */
                padding-top: 100px;
                /* Location of the box */
                left: 0;
                top: 0;
                width: 100%;
                /* Full width */
                height: 100%;
                /* Full height */
                overflow: auto;
                /* Enable scroll if needed */
                background-color: rgb(0, 0, 0);
                /* Fallback color */
                background-color: rgba(0, 0, 0, 0.4);
                /* Black w/ opacity */
            }

            /* Modal Content */
            .progress-modal-content {
                position: relative;
                background-color: #fefefe;
                margin: auto;
                padding: 0;
                border: 1px solid #888;
                width: 80%;
                box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
                -webkit-animation-name: animatetop;
                -webkit-animation-duration: 0.4s;
                animation-name: animatetop;
                animation-duration: 0.4s
            }

            /* Add Animation */
            @-webkit-keyframes animatetop {
                from {
                    top: -300px;
                    opacity: 0
                }

                to {
                    top: 0;
                    opacity: 1
                }
            }

            @keyframes animatetop {
                from {
                    top: -300px;
                    opacity: 0
                }

                to {
                    top: 0;
                    opacity: 1
                }
            }

            /* The Close Button */
            .close {
                color: white;
                float: right;
                font-size: 28px;
                font-weight: bold;
            }

            .close:hover,
            .close:focus {
                color: #000;
                text-decoration: none;
                cursor: pointer;
            }

            .progress-modal-header {
                padding: 2px 10px;
                background-color: #00A2D3;
                color: white;
            }

            .progress-modal-body {
                padding: 2px 16px;
            }

            .progress-modal-footer {
                padding: 2px 4px;
                background-color: #00A2D3;
                color: white;
            }

            .loader {
                border: 8px solid #f3f3f3;
                border-radius: 50%;
                border-top: 8px solid #3498db;
                width: 60px;
                height: 60px;
                -webkit-animation: spin 2s linear infinite;
                /* Safari */
                animation: spin 2s linear infinite;
            }

            /* Safari */
            @-webkit-keyframes spin {
                0% {
                    -webkit-transform: rotate(0deg);
                }

                100% {
                    -webkit-transform: rotate(360deg);
                }
            }

            @keyframes spin {
                0% {
                    transform: rotate(0deg);
                }

                100% {
                    transform: rotate(360deg);
                }
            }
        </style>
    </head>

    <body class="guiPageBody">
        <div class="container-fluid">
            <?php include "sessionExpiringPopup.php" ?>

            <div class="row mainRow">
                <?php include "mainMenu.php" ?>
                <div <?php
                //MM201218
                if (($hide_menu == "hide")) {
                    ?> class="col-xs-12 col-md-12" <?php } else { ?> class="col-xs-12 col-md-10" <?php } //MM201218 FINE
                ?> id="mainCnt">
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
                            <div class="col-xs-10 col-md-12 centerWithFlex" id="headerTitleCnt">IoT Directory: Devices</div>
                            <div class="col-xs-2 hidden-md hidden-lg centerWithFlex" id="headerMenuCnt">
                                <!--?php include "mobMainMenu.php" ?-->
                            </div>
                        </div>
                    <?php } //MM201218 FINE 
                    ?>

                    <div class="row">
                        <div class="col-xs-12" id="mainContentCnt">

                            <!--the statistics bar section -->


                            <div id="synthesis" class="row hidden-xs hidden-sm mainContentRow">
                                <!--   <div  class="col-xs-12 mainContentRowDesc"></div> -->
                                <div id="dashboardTotActiveCnt" class="col-md-4 mainContentCellCnt">
                                    <div class="col-md-12 centerWithFlex pageSingleDataCnt">
                                        <?php
                                        $u = md5($_SESSION['loggedUsername']);
                                        $query = "SELECT count(*) AS qt FROM temporary_devices WHERE status='valid' AND username ='" . $u . "' AND should_be_registered= 'no' AND deleted IS null";
                                        $result = mysqli_query($link, $query);
                                        if ($result) {
                                            $row = $result->fetch_assoc();
                                            echo $row['qt'] . ' valid devices';
                                        } else {
                                            echo '- valid devices';
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div id="dashboardTotPermCnt" class="col-md-4 mainContentCellCnt">
                                    <div class="col-md-12 centerWithFlex pageSingleDataCnt">
                                        <?php
                                        //MM
                                        $u = md5($_SESSION['loggedUsername']);
                                        $query = "SELECT count(*) AS qt FROM temporary_devices WHERE status='invalid' AND should_be_registered= 'no' AND username ='" . $u . "' AND deleted IS null";
                                        $result = mysqli_query($link, $query);
                                        if ($result) {
                                            $row = $result->fetch_assoc();
                                            echo $row['qt'] . ' invalid devices';
                                        } else {
                                            echo '- invalid devices';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>


                            <!-- TODO use apis and not direct query to DB!!!!! -->

                            <!-- the row of buttons: My devices, Delegated Devices, Add New Device; it appears in mydevices.php-->
                            <!--                        <div id="managerBoard" class="row mainContentRow">
                                                        <div id="uploadFileBoard" class="row mainContentRow">
                                                             
                                                            <div class="col-xs-12 mainContentCellCntWhiteLines">
                                                                <div class="col-xs-12 col-md-6">
                                                                    <div class="paddingTop"> Context broker </div>
                                                                </div>
                                                                <div class="col-xs-12 col-md-6">
                                                                    <div class="modalFieldCnt">
                                                                        <select id="selectContextBrokerLD" name="selectContextBrokerLD" class="modalInputTxt">
                            
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                            
                            
                                                        </div>-->


                            <hr style="border-top: 5px solid #2e6da4; border-radius: 5px; margin:0;">
                            <div class="row mainContentRow" style="padding-left: 5%; font-size:20px;"><u><b>New Temporary devices</b></u> </div>
                            <!-- the table-->
                            <div id="displayAllDeviceRow" class="row mainContentRow">
                                <div class="col-xs-12 mainContentRowDesc">

                                </div>
                                <div class="col-xs-12 mainContentCellCnt">
                                    <table id="devicesTable" class="table" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                <th></th>
                                                <th data-cellTitle="name">Device Identifier</th>
                                                <th data-cellTitle="contextbroker">IOT Broker</th>
                                                <th data-cellTitle="protocol">Protocol</th>
                                                <th data-cellTitle="format">Format</th>
                                                <th data-cellTitle="devicetype">Device Type</th>
                                                <th data-cellTitle="status">Status</th>
                                                <th data-cellTitle="edit">Edit</th>
                                                <th data-cellTitle="delete">Delete</th>
                                                <th data-cellTitle="location">Location</th>
                                            </tr>
                                        </thead>
                                    </table>

                                    <button type="text" id="deleteAllBtn" name="myDevice" class="btn btn-primary">Delete All</button>
                                    <button type="text" id="updateMultipleModalBtn" name="updateMultipleModalBtn" class="btn btn-info">Update Devices</button>
                                    <button type="text" id="updateMultipleValueBtn" name="updateMultipleValueBtn" class="btn btn-info">Update Values</button>
                                    <button type="text" id="ApplyRulesBtn" name="ApplyRulesBtn" class="btn btn-info">Apply rules</button>
                                    <!--Sara2210 start -->
                                    <button type="text" id="insertValidBtn" name="myDevice" class="btn btn-primary pull-right">Register Valid Devices</button>
                                    <!--Sara2210 end -->
                                </div>
                            </div>
                            <!-- the parameters table-->

                            <hr style="border-top: 5px solid #2e6da4; border-radius: 5px; margin:0;">
                            <div class="row mainContentRow" style="padding-left: 5%; font-size:20px;"><u><b>New Parameters for registered devices</b></u> </div>
                            <div id="displayAllDeviceRow" class="row mainContentRow">
                                <div class="col-xs-12 mainContentRowDesc">

                                </div>
                                <div class="col-xs-12 mainContentCellCnt">
                                    <table id="registeredDevicesTable" class="table" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                <th data-cellTitle="name">Device Identifier</th>
                                                <th data-cellTitle="contextbroker">IOT Broker</th>
                                                <th data-cellTitle="valuename">Parameter Name</th>
                                                <th data-cellTitle="datatype">Data Type</th>
                                                <th data-cellTitle="valuetype">Value Type</th>
                                                <th data-cellTitle="valueunit">Value Unit</th>
                                                <th data-cellTitle="healthinesscriteria">Healthiness Criteria</th>
                                                <th data-cellTitle="refreshrate">Refresh Rate</th>
                                                <th data-cellTitle="status">Status</th>
                                                <th data-cellTitle="edit">Edit</th>
                                                <th data-cellTitle="delete">Delete</th>
                                            </tr>
                                        </thead>
                                    </table>

                                    <button type="text" id="insertValidAttributeBtn" name="myDevice" class="btn btn-primary pull-right">Register Valid Parameters</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!--MODALE error -->
        <div class="modal fade" id="ErrorDeviceAttr" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog  modal-lg" role="document">
                <div class="modal-content">
                    <div  class="modalHeader centerWithFlex">
                        <h5 class="col-xs-12 centerWithFlex"  id="ErrorDeviceAttrTitle" ></h5>
                    </div>
                    <div  class="modal-body">
                        <p id="ErrorDeviceAttrMsg"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="Register_Device" class="btn confirmBtn" style="display: none">Register device</button>
                        <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>

                    </div>
                </div>
            </div>
        </div>
        <!-- MODALE error END -->

        <!--MODALE Named rule  ok -->
        <div class="modal fade" id="NamedRule" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog  modal-lg" role="document">
                <div class="modal-content">
                    <div  class="modalHeader centerWithFlex">
                        <h5 class="col-xs-12 centerWithFlex" >Save the rule</h5>
                    </div>
                    <div  class="modal-body">
                        <p id="NamedRuleOK"></p>
                    </div>
                    <div class="modal-footer">
                        <!--<button type="button" id="Register_Device" class="btn confirmBtn" style="display: none">Register device</button>-->
                        <button type="button" class="btn btn-primary" id="CloseSave">Close</button>

                    </div>
                </div>
            </div>
        </div>
        <!-- MODALE named rule ok END -->


        <!-- window to show active broker discovery-->

        <div class="modal fade" id="activeBrokersModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modalHeader centerWithFlex">
                        Active broker discovery
                    </div>
                    <div id="displayAllBrokers" class="row mainContentRow">
                        <div class="modal-body">

                            <div id="selectBroker" class="col-xs-12 mainContentRowDesc">
                                <div id="selectBrokersActive" class="col-xs-12 col-md-8">
                                    <select id="activeInactiveBrokes"></select>
                                </div>
                                <div class="col-xs-12 col-md-8">
                                    <button type="button" id="activateButton" class="btn btn-success" style="display: none;">Activate</button>
                                    <button type="button" id="inactivateButton" class="btn btn-danger">Inactivate</button>
                                </div>
                            </div>
                            <!--	<div id="statusSelectedBroker" class="col-xs-12 col-md-6" style=" visibility:hidden; color:green; padding-left:30px;" >This Broker is active</div>-->

                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" id="stopAllBrokers" class="btn btn-primary">Stop all</button>
                        <button type="button" id="successRegisterUserDeviceCancelBtn" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- the window that appears as message after clicking submit on adding new device, success or failure (I guess)-->

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

        <!-- the window that appears as message after clicking delete on some device row-->

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
        <div class="modal fade" id="deleteParameterModal" tabindex="-1" role="dialog" aria-labelledby="deleteDeviceModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteParameterModalLabel">Parameter deletion</h5>
                    </div>
                    <div class="modal-body">
                        Do you want to confirm deletion of the following Parameter?
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="deleteParameterCancelBtn" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="button" id="deleteParameterConfirmBtn" class="btn btn-primary">Confirm</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Start of Update Multiple devices -->

    <div class="modal fade" id="updateMultipleDeviceModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modalHeader centerWithFlex">
                    Bulk Update Rule - Devices
                </div>

                <div id="addContextBrokerModalBody" class="modal-body modalBody">

                    <div class="tab-content">

                        <!-- Info tab -->
                        <div id="infoTabCB" class="tab-pane fade in active">
                            <div class="row">
                                <div class="col-xs-12 modalCell">
                                    <div class="modalFieldCnt">
                                        <h3 align="center">IF STATEMENT</h3>
                                        <table id="ifBlockTable" class="ifBlockBulk">
                                            <thead>
                                            <th width="11%"></th>
                                            <th width="29%">Fields</th>
                                            <th width="30%">Operator</th>
                                            <th width="28%">Value</th>
                                            <th width="5%"><i id="addifBlockBtn" class="fa fa-plus"></i></th>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                    <input type="hidden" id="authorizedPagesJson" name="authorizedPagesJson" />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-12 modalCell">
                                    <div class="modalFieldCnt">
                                        <h3 align="center">UPDATE STATEMENT</h3>
                                        <table id="decisionBlockTable" class="decisionBlockBulk">
                                            <thead>
                                            <th width="15%"></th>
                                            <th width="40%">Fields</th>
                                            <th width="40%">Predicted Value</th>
                                            <th width="5%"><i id="addDecisionBlockBtn" class="fa fa-plus"></i></th>
                                            </thead>
                                            <tbody>

                                            </tbody>
                                        </table>
                                    </div>
                                    <input type="hidden" id="authorizedPagesJson" name="authorizedPagesJson" />
                                </div>
                            </div>
                            <div class="row">

                                <div class="col-xs-12 modalCell">
                                    <div class="modalFieldCnt">
                                        <table id="resultBlockTable" class="resultBlockBulk">
                                            <thead>
                                            <th></th>
                                            <th>Result</th>
                                            <th></th>
                                            <th></th>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <h3><span class="label label-warning">AFFECTED</span></h3>
                                                    </td>
                                                    <td>
                                                        <div id="devicesFound" style="border:0px; background-color:rgb(230, 249, 255)">0 devices founded</div>
                                                    </td>
                                                    <td>
                                                        <button type="text" id="updateAllConfirmBtn" name="updateAllConfirmBtn" class="btn confirmBtn internalLink">Update All</button>
                                                    </td>
                                                    <td> <button type="text" id="updateAllCancelBtn" class="btn cancelBtn" data-dismiss="modal">Cancel</button>

                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <div id="displayAllDevicePreview" class="row mainContentRow">
                                            <div class="col-xs-12 mainContentRowDesc">

                                            </div>
                                            <div class="col-xs-12 mainContentCellCnt">
                                                <table id="devicePreviewTable" class="table" cellspacing="0" width="100%">
                                                    <thead>
                                                        <tr>
                                                            <th data-cellTitle="name">Device Identifier</th>
                                                            <th data-cellTitle="contextbroker">IOT Broker</th>
                                                            <th data-cellTitle="protocol">Protocol</th>
                                                            <th data-cellTitle="format">Format</th>
                                                            <th data-cellTitle="devicetype">Device Type</th>
                                                        </tr>
                                                    </thead>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" id="authorizedPagesJson" name="authorizedPagesJson" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Start of Naming Rules  -->

    <div class="modal fade" id="NamingValuesRULEModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog  modal-lg" role="document">
            <div class="modal-content">
                <div class="modalHeader centerWithFlex">
                    <h5 class="col-xs-12 centerWithFlex">Naming your rule</h5>
                </div>
                <div  class="modal-body">
                    <label for="firstname">Name</label>
                    <input type="text" class="form-control" id="NamingValuesRULEMInput" placeholder="Insert a name (min 2 character)">
                    <br>
                    <br>

                    <label for="modeRules">The rule is applied:</label>

                    <select class = "modalInputTxt" style="width: 70%;" name="modeRules" id="modeRules">
                        <option value="0">not recursiving</option>
                        <option value="1">recursiving</option>

                    </select>
                    <br>
                    <br>

                    <label for="ServiceRules">The rule is applied on service/s:</label>

                    <select class="js-example-basic-multiple"  multiple="multiple" style="width: 70%;" name="OPTServiceRules" id="OPTServiceRules">
                        <!--<div id="OPTServiceRules"> </div>-->


                    </select>

                   


                </div>


                <div class="modal-footer">
                    <button type="button" id="NamingValuesRULEDismissBtn" class="btn btn-primary" data-dismiss="modal">Close</button>
                    <button type="text" id="NamingValuesRULEConfirmBtn" name="NamingValuesRULEConfirmBtn" class="btn confirmBtn internalLink" disabled>OK</button>
                </div>
            </div>
        </div>
    </div>

    <!-- END of Naming Rules -->


    <!-- Start of Update Multiple Values -->

    <div class="modal fade" id="updateMultipleDeviceModal1" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modalHeader centerWithFlex">
                    Bulk Update Rule - Values
                </div>
                <div id="addContextBrokerModalBody1" class="modal-body modalBody">

                    <div class="tab-content">
                        <div id="infoTabCB1" class="tab-pane fade in active">

    <!--<label for=" SAVEAllValuesRULEBtn">Save Rules <input type="checkbox" id="SAVEAllValuesRULEBtn" name="SAVEAllValuesRULEBtn" class="btn confirmBtn internalLink"></input></label>-->

                            <div class="modalFieldCnt">
                                <label> If you need, load a rule:  </label>
                                <select id="selectRule" name="selectRule" class="modalInputTxt" style="width: 40%;"  onclick="LoadRules()">
                                    <?php
                                    $query = "SELECT Name FROM iotdb.EXT_values_rules;";
                                    $result = mysqli_query($link, $query);

                                    if ($result) {
                                        echo "<option value=\"NoOne\">  Select an option </option>";
                                        while ($row = $result->fetch_assoc()) {
                                            $nameRule = $row["Name"];
                                            echo "<option value=\"$nameRule\">$nameRule</option>";
                                        }
                                    } else {

                                        $nameRule = "ERROR";
                                        echo "<option value=\"$nameRule\">$nameRule</option>";
                                    }
                                    ?>
                                </select>
                            </div> 



                            <button style="float: right;" type="text" id="SAVEAllValuesRULEBtn" name="SAVEAllValuesRULEBtn" class="btn confirmBtn ">Save Rules</button>


                            <div class="row">
                                <div class="col-xs-12 modalCell">
                                    <div class="modalFieldCnt">
                                        <h3 align="center">IF STATEMENT</h3>
                                        <table id="ifBlockTableValue" class="ifBlockBulk">
                                            <thead>
                                            <th width="11%"></th>
                                            <th width="29%">Fields</th>
                                            <th width="30%">Operator</th>
                                            <th width="28%">Value</th>
                                            <th width="5%"><i id="addifBlockBtnValue" class="fa fa-plus"></i></th>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                    <input type="hidden" id="authorizedPagesJson" name="authorizedPagesJson" />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-12 modalCell">
                                    <div class="modalFieldCnt">
                                        <h3 align="center">UPDATE STATEMENT</h3>
                                        <table id="decisionBlockTableValue" class="decisionBlockBulk">
                                            <thead>
                                            <th width="15%"></th>
                                            <th width="40%">Fields</th>
                                            <th width="40%">Predicted Value</th>
                                            <th width="5%"><i id="addDecisionBlockBtnValue" class="fa fa-plus"></i></th>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                    <input type="hidden" id="authorizedPagesJson" name="authorizedPagesJson" />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-12 modalCell">
                                    <div class="modalFieldCnt">
                                        <table id="resultBlockTableValue" class="resultBlockBulk">
                                            <thead>
                                            <th></th>
                                            <th>Result</th>
                                            <th></th>
                                            <th></th>

                                            </thead>
                                            <tbody>
                                                <tr>

                                                    <td>
                                                        <h3><span class="label label-warning">AFFECTED</span></h3>
                                                    </td>
                                                    <td>
                                                        <div id="valueFound" style="border:0px; background-color:rgb(230, 249, 255)">0 values founded</div>
                                                    </td>

                                                    <td>
                                                        <button type="text" id="updateAllValuesConfirmBtn" name="updateAllValuesConfirmBtn" class="btn confirmBtn internalLink">Update All</button>
                                                    </td>
                                                    <td> <button type="text" id="updateAllValuesCancelBtn" class="btn cancelBtn" data-dismiss="modal">Cancel</button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <div id="displayAllDevicePreview" class="row mainContentRow">
                                            <div class="col-xs-12 mainContentRowDesc">

                                            </div>
                                            <div class="col-xs-12 mainContentCellCnt">
                                                <table id="valuesPreviewTable" class="table" cellspacing="0" width="100%">
                                                    <thead>
                                                        <tr>
                                                            <th data-cellTitle="name">Device Identifier</th>
                                                            <th data-cellTitle="contextbroker">IOT Broker</th>
                                                            <th data-cellTitle="value_name">Value Name</th>
                                                            <th data-cellTitle="data_type">Data Type</th>
                                                            <th data-cellTitle="value_type">Value Type</th>
                                                            <th data-cellTitle="value_unit">Value Unit</th>
                                                            <th data-cellTitle="healthiness_criteria">Healthiness Criteria</th>
                                                            <!--<th data-cellTitle="healthiness_value">Healthiness Value</th>-->

                                                        </tr>
                                                    </thead>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" id="authorizedPagesJson" name="authorizedPagesJson" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--  SUGGESTIONS MODAL  -->

    <div class="modal fade" id="suggestModifications" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modalHeader centerWithFlex">
                    Suggest Updates
                </div>

                <div id="numRulesFound" style="margin-top:5px; margin-left:5px; font-size: 17px;"> 0 rules found</div>

                <div id="suggestModificationsModal" class="modal-body modalBody">

                    <div class="tab-content">
                        <div id="infoTabCB1" class="tab-pane fade in active">
                            <div class="row">
                                <div class="col-xs-12 modalCell">
                                    <div class="modalFieldCnt">
                                        <h3 align="center">IF STATEMENT</h3>
                                        <table id="ifBlockSuggestions" class="ifBlockBulk">
                                            <thead>
                                            <th width="11%"></th>
                                            <th width="29%">Fields</th>
                                            <th width="30%">Operator</th>
                                            <th width="28%">Value</th>
                                            <th width="5%"></th>
                                            <!--<i id="addifBlockBtnValue" class="fa fa-plus"></i></th>-->
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                    <input type="hidden" id="authorizedPagesJson" name="authorizedPagesJson" />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-12 modalCell">
                                    <div class="modalFieldCnt">
                                        <h3 align="center">UPDATE STATEMENT</h3>
                                        <table id="decisionBlockSuggestions" class="decisionBlockBulk">
                                            <thead>
                                            <th width="15%"></th>
                                            <th width="40%">Fields</th>
                                            <th width="40%">Predicted Value</th>
                                            <th width="5%">
                                                </td>
                                                <!--<i id="addDecisionBlockBtnSuggestions" class="fa fa-plus"></i></th>-->
                                                </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                    <input type="hidden" id="authorizedPagesJson" name="authorizedPagesJson" />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-12 modalCell">
                                    <div class="modalFieldCnt">
                                        <table id="resultBlockTableSuggestions" class="resultBlockBulk">
                                            <thead>
                                            <th></th>
                                            <th>Result</th>
                                            <th> <br></th>
                                            <th></th>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td style="border:0px; background-color:rgb(230, 249, 255)">
                                                        <h3><span class="label label-warning">AFFECTED</span></h3>
                                                    </td>
                                                    <td>
                                                        <div id="rulesMatchFound">0 values founded</div>
                                                    </td>
                                                    <td><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                                    <td><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>

                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div id="applySuggestionMessage">Do you want to apply this suggestion?</div>
                                                    </td>
                                                    <td>
                                                        <button type="text" id="suggestionConfirm" name="suggestionConfirm" class="btn confirmBtn internalLink">Apply</button>
                                                    </td>
                                                    <td> <button type="text" id="skipSuggestion" class="btn confirmBtn internalLink">Skip</button>

                                                    </td>
                                                    <td> <button type="text" id="skipAll" class="btn confirmBtn internalLink">Skip all</button>

                                                    </td>

                                                </tr>
                                            </tbody>
                                        </table>

                                        <div id="displayAllDevicePreviewSuggestions" class="row mainContentRow">
                                            <div class="col-xs-12 mainContentRowDesc">

                                            </div>
                                            <div class="col-xs-12 mainContentCellCnt">
                                                <table id="devicesSuggestionsTable" class="table" cellspacing="0" width="100%">
                                                    <thead>
                                                        <tr>
                                                            <th data-cellTitle="name">Device Identifier</th>
                                                            <th data-cellTitle="contextbroker">IOT Broker</th>
                                                            <th data-cellTitle="value_name">Value Name</th>
                                                            <th data-cellTitle="data_type">Data Type</th>
                                                            <th data-cellTitle="value_type">Value Type</th>
                                                            <th data-cellTitle="value_unit">Value Unit</th>
                                                            <th data-cellTitle="healthiness_criteria">Healthiness Criteria</th>
                                                            <!--<th data-cellTitle="healthiness_value">Healthiness Value</th>-->

                                                        </tr>
                                                    </thead>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" id="authorizedPagesJson" name="authorizedPagesJson" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- the window that appears as message after clicking delete all button -->

    <div class="modal fade" id="deleteAllDevModal" tabindex="-1" role="dialog" aria-labelledby="deleteAllDevModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteAllDevModalLabel">Devices deletion</h5>
                </div>
                <div class="modal-body">
                    Do you want to confirm deletion of all devices?
                </div>
                <div class="modal-footer">
                    <button type="button" id="deleteAllDevCancelBtn" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" id="deleteAllDevConfirmBtn" class="btn btn-primary">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Success  Ownership-->
    <div class="modal fade" id="changeOwnershipOkModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modalHeader centerWithFlex">
                    Change Ownership
                </div>
                <div id="changeOwnershipModalBody" class="modal-body modalBody">
                    <div class="row">
                        <div class="col-xs-12 modalCell">
                            <div id="changeOwnershipOkModalInnerDiv1" class="modalDelMsg col-xs-12 centerWithFlex">

                            </div>
                            <div class="modalDelObjName col-xs-12 centerWithFlex" id="changeOwnershipOkModalInnerDiv2"><i class="fa fa-check" style="font-size:36px"></i></div>
                        </div>
                    </div>
                </div>
                <!--<div class="modal-footer">
                  
                </div>-->
            </div>
        </div>
    </div>



    <!-- Update -->
    
     <div class="modal fade" id="ApplyRulesModal1" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modalHeader centerWithFlex">
                        Update device
                    </div>
                    
                    <div id="ApplyRulesModal1Body" class="modal-body modalBody">
                        <div class="row">
                            
                            <div class="row" id="ApplyRulesModal1QuestMsg">
                        <div class="col-xs-12 centerWithFlex">Are you sure to apply the Rules? </div>
                    </div>
                            
                            <div class="col-xs-12 modalCell">
                              
                                <div class="modalDelObjName col-xs-12 centerWithFlex" id="addDeviceOkModalInnerDiv2" style='display:none'><i class="fa fa-check" style="font-size:36px"></i></div> 
                            </div>
                              <div class="row" id="ApplyRulesModal1OkMsg">
                        <div class="col-xs-12 centerWithFlex">Devices are updated! </div>
                    </div>
                           <div class="row" id="ApplyRulesModal1KoMsg">
                        <div class="col-xs-12 centerWithFlex">There are some problems </div>
                    </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="ApplyRulesCnBtn" class="btn cancelBtn"  data-dismiss="modal">Cancel</button>
                        <button type="button" id="ApplyRulesOkBtn" class="btn confirmBtn" >Ok</button>
                    </div>
                </div>
            </div>
        </div>
    
    
    
    
    <!-- the edit window, when you click on "edit" from a device row-->
    <div class="modal fade" id="editDeviceModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog  modal-lg" role="document">
            <div class="modal-content">
                <div id="editDeviceModalLabel" class="modalHeader centerWithFlex">
                    Update Device
                </div>

                <div id="editDeviceModalBody" class="modal-body modalBody">

                    <ul id="editDeviceModalTabs" class="nav nav-tabs nav-justified">
                        <li class="active"><a data-toggle="tab" href="#editIOTBrokerTabDevice">IoT Broker</a></li>
                        <li><a data-toggle="tab" href="#editInfoTabDevice">Info</a></li>
                        <li><a data-toggle="tab" href="#editGeoPositionTabDevice">Position</a></li>
                        <li><a data-toggle="tab" href="#editStaticTabModel">Static Attributes</a></li>
                        <li><a data-toggle="tab" href="#editSchemaTabDevice">Values</a></li>
                        <!--<li><a data-toggle="tab" href="#editStatusTabDevice">Status</a></li>-->

                    </ul>

                    <div class="tab-content">

                        <!-- IOT Broker tab -->
                        <div id="editIOTBrokerTabDevice" class="tab-pane fade in active">

                            <div class="row">

                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <select id="selectContextBrokerM" name="selectContextBrokerM" class="modalInputTxt">
                                            <?php
                                            $query = "SELECT name FROM contextbroker WHERE kind like 'external'";
                                            $result = mysqli_query($link, $query);

                                            if ($result) {
                                                while ($row = $result->fetch_assoc()) {
                                                    $nameCB = $row["name"];
                                                    echo "<option value=\"$nameCB\">$nameCB</option>";
                                                }
                                            } else {

                                                $nameCB = "ERROR";
                                                echo "<option value=\"$nameCB\">$nameCB</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="modalFieldLabelCnt">ContextBroker</div>
                                    <div id="selectContextBrokerMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>

                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <select id="selectKindDeviceM" name="selectKindDeviceM" class="modalInputTxt">
                                            <option value="sensor">sensor</option>
                                            <option value="actuator">actuator</option>
                                        </select>
                                    </div>
                                    <div class="modalFieldLabelCnt">Kind</div>
                                    <div id="selectKindDeviceMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>

                            </div>

                            <div class="row">

                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <select id="selectProtocolDeviceM" name="selectProtocolDeviceM" class="modalInputTxt">
                                            <option value="amqp">amqp</option>
                                            <option value="coap">coap</option>
                                            <option value="mqtt">mqtt</option>
                                            <option value="ngsi">ngsi</option>
                                            <option value="ngsi w/MultiService">ngsi w/MultiService</option>
                                        </select>
                                    </div>
                                    <div class="modalFieldLabelCnt">Protocol</div>
                                    <div id="selectProtocolDeviceMMsg" class="modalFieldMsgCnt">&nbsp;</div>
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
                                    <div id="selectFormatDeviceMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>

                            </div>
                            <!-- Start MultiService + ServicePath Section -->
                            <div class="row" id="editMultiServiceAndServicePath">
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="deviceService" id="deviceService" required>
                                    </div>
                                    <div id="editSelectServiceLabel" class="modalFieldLabelCnt">Service/Tenant</div>
                                    <div id="editSelectServiceMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>

                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="editInputServicePathDevice" id="editInputServicePathDevice" required>
                                    </div>
                                    <div id="editInputServicePathLabel" class="modalFieldLabelCnt">ServicePath</div>
                                    <div id="editInputServicePathMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
                            </div>
                            <!-- End MultiService + ServicePath Section -->
                        </div>
                        <!-- Info tab -->
                        <div id="editInfoTabDevice" class="tab-pane fade">

                            <div class="row">

                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputNameDeviceM" id="inputNameDeviceM" required>
                                    </div>
                                    <div class="modalFieldLabelCnt">Device Identifier</div>
                                    <div id="inputNameDeviceMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>

                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <!--
                                                                                <select name="selectModelDevice" id="selectModelDevice" class="modalInputTxt">
                                                                                    <option data_key="normal" value="custom">custom</option>    
                                                                                </select>
                                        -->

                                        <input id="selectModelDeviceM" name="selectModelDeviceM" class="modalInputTxt" readonly>
                                    </div>
                                    <div class="modalFieldLabelCnt">Model</div>
                                    <div id="inputModelDeviceMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>

                            </div>

                            <div class="row">


                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputTypeDeviceM" id="inputTypeDeviceM">
                                    </div>
                                    <div class="modalFieldLabelCnt">Device Type</div>
                                    <div id="inputTypeDeviceMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>

                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputMacDeviceM" id="inputMacDeviceM">
                                    </div>
                                    <div class="modalFieldLabelCnt">Mac Address</div>
                                    <div id="inputMacDeviceMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <select name="selectEdgeGatewayTypeM" id="selectEdgeGatewayTypeM" class="modalInputTxt">
                                            <option value=""></option>
                                            <?php
                                            $query = "SELECT name FROM edgegatewaytype";
                                            $result = mysqli_query($link, $query);

                                            if ($result) {
                                                while ($row = $result->fetch_assoc()) {
                                                    $name = $row["name"];
                                                    echo "<option value=\"$name\">$name</option>";
                                                }
                                            } else {
                                                $name = "ERROR";
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
                                        <input type="text" class="modalInputTxt" name="inputEdgeGatewayUriM" id="inputEdgeGatewayUriM">
                                    </div>
                                    <div class="modalFieldLabelCnt">Edge-Gateway URI</div>
                                    <div id="inputEdgeGatewayUriMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>

                            </div>

                            <div class="row">

                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputProducerDeviceM" id="inputProducerDeviceM">
                                    </div>
                                    <div class="modalFieldLabelCnt">Producer</div>
                                    <div id="inputProducerDeviceMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>


                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <!--
                                        <select id="selectVisibilityDeviceM" name="selectVisibilityDeviceM" class="modalInputTxt">								
                                                                                        <option></option>
                                                                                </select>
                                        -->
                                        <input id="selectVisibilityDeviceM" name="selectVisibilityDeviceM" class="modalInputTxt" readonly>
                                    </div>
                                    <div class="modalFieldLabelCnt">Ownership</div>
                                    <div id="selectVisibilityDeviceMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>

                            </div>

                            <div class="row">

                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <div class="input-group unity-input"> <input type="text" class="modalInputTxt" name="inputFrequencyDeviceM" id="inputFrequencyDeviceM"> <span class="input-group-addon" id="basic-addon2">sec</span></div>
                                    </div>
                                    <div class="modalFieldLabelCnt">Frequency</div>
                                    <div id="inputFrequencyDeviceMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>

                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputUriDeviceM" id="inputUriDeviceM" readonly>
                                    </div>
                                    <div class="modalFieldLabelCnt">Service URI</div>
                                    <div id="inputUriDeviceMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>

                            </div>

                            <!--Fatima4-->
                            <div class="row">
                                <div class="modalFieldCnt">
                                    <button type="text" id="editDeviceGenerateKeyBtn" class="btn confirmBtn internalLink" onclick="editGenerateKeysCLicked()" style="display: none;">Generate Keys</button>
                                </div>
                            </div>

                            <div class="row">

                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="KeyOneDeviceUserM" id="KeyOneDeviceUserM">
                                    </div>
                                    <div class="modalFieldLabelCnt">KEY 1</div>
                                    <div id="KeyOneDeviceUserMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="KeyTwoDeviceUserM" id="KeyTwoDeviceUserM">
                                    </div>
                                    <div class="modalFieldLabelCnt">KEY 2</div>
                                    <div id="KeyTwoDeviceUserMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
                            </div>

                            <div id="sigFoxDeviceUserMMsg" class="modalFieldMsgCnt">&nbsp;</div>

                        </div>
                        <!-- Geo-Position tab -->
                        <div id="editGeoPositionTabDevice" class="tab-pane fade">
                            <div class="row">
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputLatitudeDeviceM" id="inputLatitudeDeviceM">
                                    </div>
                                    <div class="modalFieldLabelCnt">Latitude</div>
                                    <div id="inputLatitudeDeviceMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputLongitudeDeviceM" id="inputLongitudeDeviceM">
                                    </div>
                                    <div class="modalFieldLabelCnt">Longitude</div>
                                    <div id="inputLongitudeDeviceMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
                            </div>
                            <h1>&nbsp;</h1>
                            <div class="form-row iot-directory-form-row">
                                <link rel="stylesheet" href="../css/leaflet.css" />
                                <link rel="stylesheet" href="../css/leaflet.draw.css" />
                                <div id="editLatLong" style="width: 100%; height: 400px" class="modal-body modalBody"></div>
                            </div>
                            <div id="positionMsgHintM" class="modalFieldMsgCnt" hidden="true">
                                <h1>&nbsp;</h1>
                                <p class="text-danger font-weight-bold">WARNING: the indicated GPS Position has to be intended as the Initial Position of the Device. To see the Current Position of the Device, please proceed via Service Map</p>
                            </div>
                        </div>

                        <!-- Static Attributes tab -->
                        <div id="editStaticTabModel" class="tab-pane fade">
                            <div class="row">
                                <div class="col-xs-12 col-md-8 modalCell">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="isMobileTickM">
                                        <label class="custom-control-label" for="isMobileTickM">Device in Mobility</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldLabelCnt">Subnature</div>
                                    <div class="modalFieldCnt">
                                        <select id="selectSubnatureM" name="selectSubnatureM" class="modalInputTxt">
                                            <option></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-12 col-md-12 modalCell">
                                    <div id="editlistStaticAttributes"></div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-12 col-md-12 modalCell">
                                    <div class="pull-left"><button type="text" id="addNewStaticBtnM" class="btn confirmBtn" style="display: none;">Add Attribute</button></div>
                                </div>
                            </div>
                        </div>

                        <!-- Attribute tab -->
                        <div id="editSchemaTabDevice" class="tab-pane fade">

                            <div id="editlistAttributes"></div>
                            <div id="addlistAttributesM"></div>
                            <div id="deletedAttributes" style="display:none"></div>
                            <!-- <div class="pull-left"><i id="addAttrMBtn" class="fa fa-plus-square" style="font-size:36px; color: #ffcc00"></i></div> -->
                            <div class="pull-left"><button id="addAttrMBtn" class="btn btn-primary">Add Value</button></div>
                            <div id="editlistAttributesMsg" class="modalFieldMsgCnt">&nbsp;</div>
                        </div>

                        <!-- Semantic Labeling tab -->
                        <div id="editStatusTabDevice" class="tab-pane fade" style="display:none">
                            <div class="row">
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <!--  <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputPropertiesDeviceM" id="inputPropertiesDeviceM"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Device Status</div> -->
                                    <div id="inputPropertiesDeviceMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <!-- <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputAttributesDeviceM" id="inputAttributesDeviceM"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Attribute Status</div> -->
                                    <div id="inputAttributesDeviceMMsg" class="modalFieldMsgCnt">&nbsp;</div>
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
                    <button type="button" id="editDeviceConfirmBtn" class="btn confirmBtn internalLink">Confirm</button>
                </div>

                <!-- </form>-->

            </div>
        </div>
    </div>

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
                            <div class="modalDelObjName col-xs-12 centerWithFlex" id="editDeviceKoModalInnerDiv2">
                                <i class="fa fa-frown-o" style="font-size:36px">
                                </i>You entered some invalid values, the update has failed. Adjust the data and try again.
                            </div>
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

    <!-- bulk update modal --->
    <div class="modal fade" id="bulkUpdateModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modalHeader centerWithFlex">
                    Update device
                </div>
                <div class="modal-body modalBody">
                    <div class="row">
                        <div class="col-xs-12 modalCell">
                            <div id="bulkUpdateModalInnerDiv" class="modalDelMsg col-xs-12 centerWithFlex">
                                Your devices has been correctly updated.
                            </div>
                            <div class="modalDelObjName col-xs-12 centerWithFlex" id="bulkUpdateModalInnerDiv2"><i class="fa fa-check" style="font-size:36px"></i></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="editDeviceOkModalDoneBtn" class="btn btn-secondary" data-dismiss="modal">DONE</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Fail -->
    <div class="modal fade" id="bulkUpdateFaliure" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modalHeader centerWithFlex">
                    Update device
                </div>
                <div id="deleteDeviceModalBody" class="modal-body modalBody">
                    <div class="row">
                        <div class="col-xs-12 modalCell">
                            <div id="bulkUpdateModalInnerDivFaliure" class="modalDelMsg col-xs-12 centerWithFlex">
                                An error has occurred during the update.
                            </div>
                            <div class="modalDelObjName col-xs-12 centerWithFlex" id="bulkUpdateModalInnerDivFaliure2"><i class="fa fa-frown-o" style="font-size:36px"></i>You entered some invalid values, the update has failed. Adjust the data and try again.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="addDeviceKoModalCancelBtn" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    <!-- used for progress showing-------->

    <div id="myModal" class="modal">

        <!-- Modal content -->
        <div class="progress-modal-content">
            <div class="progress-modal-header">
                <span id="progress_span" class="close">&times;</span>
                <h2>Your file is being uploaded...</h2>
            </div>
            <div id="loader_spin" class="loader" style="margin: 0 auto;"></div>
            <div class="modal-body" id="myModalBody" style="height: 200px;width:100%; overflow:scroll;">
            </div>
            <div class="modal-footer">
                <h3></h3>
                <button type="button" id="progress_ok" class="btn confirmBtn" style="position: absolute; right:50%; bottom: 0; padding: 10px; margin: 10px; display: none" onclick="refresh()">Ok</button>
            </div>
        </div>

    </div>

    <div id="retrieveModalWait" class="modal">

        <!-- Modal content -->
        <div class="progress-modal-content">
            <div class="progress-modal-header">
                <span id="progress_spanW" class="close">&times;</span>
                <h2>Your data are being retrieved...</h2>
            </div>
            <div id="loader_spinW" class="loader" style="margin: 0 auto;"></div>
            <div class="modal-body" id="myModalBodyW" style="height: 200px;width:100%; overflow:scroll;">
            </div>
            <div class="modal-footerW">
                <h3></h3>
                <button type="button" id="progress_ok_wait" class="btn confirmBtn" style="position: absolute; right:50%; bottom: 0; padding: 10px; margin: 10px; display: none" onclick="refresh()">Ok</button>
            </div>
        </div>

    </div>
    <div id="myModal_forbulkstatus" class="modal">

        <!-- Modal content -->
        <div class="progress-modal-content">
            <div class="progress-modal-header">
                <span id="progress_span_forbulkstatus" class="close">&times;</span>
                <h2>Your valid devices are being processed...</h2>
            </div>
            <div id="loader_spin_forbulkstatus" class="loader" style="margin: 0 auto;"></div>
            <div class="modal-body" id="myModalBody_forbulkstatus" style="height: 200px;width:100%; overflow:scroll;">
            </div>
            <div class="modal-footer">
                <h3></h3>
                <button type="button" id="progress_ok_forbulkstatus" class="btn confirmBtn" style="position: absolute; right:30%; bottom: 0; padding: 10px; margin: 10px; display: none" onclick="dismiss_dialog()">Ok</button>
                <button type="button" id="progress_stop_forbulkstatus" class="btn confirmBtn" style="position: absolute; right:60%; bottom: 0; padding: 10px; margin: 10px; display: none" onclick="stop_progress()">Stop the Process</button>

            </div>
        </div>

    </div>


    <div class="modal fade" id="progress-update" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modalHeader centerWithFlex">
                    Device Location on Map
                </div>
                <div class="form-row iot-directory-form-row">
                    <div id="progress-update-body" style="width: 100%; height: 400px" class="modal-body modalBody">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- map, showing a single location on the map-->

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
                    <button type="button" id="cancelMapBtn" class="btn cancelBtn" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- map, showing the all devices locations-->

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
                    <button type="button" id="cancelMapBtn" class="btn cancelBtn" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Ownership Visibility and Delegations -->
    <div class="modal fade" id="delegationsModal" tabindex="-1" role="dialog" aria-labelledby="modalAddWidgetTypeLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div id="delegationHeadModalLabel" class="modalHeader centerWithFlex">

                </div>

                <div id="delegationsModalBody" class="modal-body modalBody">


                    <div id="delegationsModalRightCnt" class="col-xs-12 col-sm-12">

                        <form class="form-horizontal">

                            <div id="visibilityCnt" class="tab-pane fade in">
                                <div class="row" id="visibilityFormRow">
                                    <legend>
                                        <div class="col-xs-12 centerWithFlex delegationsModalLbl modalFirstLbl" id="changeOwnershipLbl">
                                            Change visibility
                                        </div>
                                    </legend>
                                    <div class="row" class="col-xs-12 col-md-6">
                                        <!--<div class="col-xs-12" id="newVisibilityCnt"> -->
                                        <div class="col-xs-12 col-md-4" id="newVisibilityCnt">

                                            <div id="visID"></div>
                                        </div>
                                        <div class="col-xs-12 col-md-4" id="newVisibilityCnt">
                                            <div>
                                                <button type="button" id="newVisibilityPublicBtn" class="btn pull-right confirmBtn">Make It Public</button>
                                                <button type="button" id="newVisibilityPrivateBtn" class="btn pull-right confirmBtn">Make It Private</button>
                                            </div>

                                        </div>
                                        <!-- <div class="col-xs-12 centerWithFlex" id="newVisibilityResultMsg"> -->
                                        <div class="col-xs-12 col-md-4" id="newVisibilityResultMsg">

                                        </div>

                                    </div>
                                </div>
                            </div>

                        </form>

                        <form class="form-horizontal">
                            <div id="ownershipCnt" class="tab-pane fade in active">
                                <div class="row" id="ownershipFormRow">
                                    <legend>
                                        <div class="col-xs-12 centerWithFlex delegationsModalLbl modalFirstLbl" id="changeOwnershipLbl">
                                            Change ownership
                                        </div>
                                    </legend>
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
                        </form>

                    </div>

                </div>
                <div id="delegationsModalFooter" class="modal-footer">
                    <button type="button" id="delegationsCancelBtn" class="btn cancelBtn" data-dismiss="modal">Close</button>
                </div>

            </div>
        </div>
    </div>

    <!-- Modal for edit Parameter -->
    <div class="modal fade" id="editParametersModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog  modal-lg" role="document">
            <div class="modal-content">
                <div id="editParametersModalLabel" class="modalHeader centerWithFlex">
                    Update Parameters
                </div>

                <div id="editParametersModalBody" class="modal-body modalBody">

                    <ul id="editParametersModalTabs" class="nav nav-tabs nav-justified">
                        <!-- <li class="active"><a data-toggle="tab" href="#editIOTBrokerTabDevice">IoT Broker</a></li>
                        <li><a data-toggle="tab" href="#editInfoTabDevice">Info</a></li>
                        <li><a data-toggle="tab" href="#editGeoPositionTabDevice">Position</a></li>
                        <li><a data-toggle="tab" href="#editStaticTabModel">Static Attributes</a></li> -->
                        <li class="active"><a data-toggle="tab" href="#editSchemaTabParameters">Parameter</a></li>
                    </ul>

                    <div class="tab-content">

                        <!-- Attribute tab -->
                        <div id="editSchemaTabParameters" class="tab-pane fade in active">

                            <div id="editlistParameters"></div>
                            <!-- <div id="addlistAttributesM"></div> -->
                            <div id="deletedAttributes" style="display:none"></div>

                            <!-- <div class="pull-left"><button id="addAttrMBtn" class="btn btn-primary">Add Value</button></div> -->
                            <div id="editlistAttributesMsg" class="modalFieldMsgCnt">&nbsp;</div>
                            <div class="row" id="infoParamError"></div>
                        </div>

                        <!-- Semantic Labeling tab -->
                        <div id="editStatusTabDevice" class="tab-pane fade" style="display:none">
                            <div class="row">
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <!--  <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputPropertiesDeviceM" id="inputPropertiesDeviceM"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Device Status</div> -->
                                    <div id="inputPropertiesDeviceMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <!-- <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputAttributesDeviceM" id="inputAttributesDeviceM"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Attribute Status</div> -->
                                    <div id="inputAttributesDeviceMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
                            </div>

                        </div>


                    </div>


                    <div class="row" id="editParametersLoadingMsg">
                        <div class="col-xs-12 centerWithFlex">Updating device, please wait</div>
                    </div>
                    <div class="row" id="editParametersLoadingIcon">
                        <div class="col-xs-12 centerWithFlex"><i class="fa fa-circle-o-notch fa-spin" style="font-size:36px;"></i></div>
                    </div>
                    <div class="row" id="editParametersOkMsg">
                        <div class="col-xs-12 centerWithFlex">Device updated successfully</div>
                    </div>
                    <div class="row" id="editParametersOkIcon">
                        <div class="col-xs-12 centerWithFlex"><i class="fa fa-thumbs-o-up" style="font-size:36px"></i></div>
                    </div>
                    <div class="row" id="editParametersKoMsg">
                        <div class="col-xs-12 centerWithFlex">Error updating device</div>
                    </div>
                    <div class="row" id="editParametersKoIcon">
                        <div class="col-xs-12 centerWithFlex"><i class="fa fa-thumbs-o-down" style="font-size:36px"></i></div>
                    </div>

                </div>
                <div id="editParametersModalFooter" class="modal-footer">
                    <button type="button" id="editParametersCancelBtn" class="btn cancelBtn" data-dismiss="modal">Cancel</button>
                    <button type="button" id="editParametersConfirmBtn" class="btn confirmBtn internalLink">Confirm</button>
                </div>

                <!-- </form>-->

            </div>
        </div>
    </div>



</body>

</html>