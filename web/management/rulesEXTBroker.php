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

        <!--     Custom CSS -->
        <link href="../css/dashboard.css" rel="stylesheet">
        <link href="../css/bulkDeviceLoad.css" rel="stylesheet">

        <!-- Custom CSS -->
        <link href="../css/dashboard.css" rel="stylesheet">
        <style> .btn-round { width: 30px; height:30px; border-radius: 50%; }
            #mainMenuCnt
            {
                background-color: rgba(51, 64, 69, 1);
                color: white;
                height: 100vh;
                <?php if ($hide_menu == "hide") echo "display:none"; //MM201218 ?>
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
        <script src="js/RulesEXTBroker.js"></script>
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
                            <div class="col-xs-10 col-md-12 centerWithFlex" id="headerTitleCnt">IoT Directory: Devices</div>
                            <div class="col-xs-2 hidden-md hidden-lg centerWithFlex" id="headerMenuCnt"><!--?php include "mobMainMenu.php" ?--></div> 
                        </div>
                    <?php } //MM201218 FINE    ?>

                    <div class="row">
                        <div class="col-xs-12" id="mainContentCnt">



                            <hr style="border-top: 5px solid #2e6da4; border-radius: 5px; margin:0;">
                            <div class="row mainContentRow" style="padding-left: 5%; font-size:20px;"><u><b>Rules</b></u> </div>
                            <!-- the table-->
                            <div id="displayAllDeviceRow" class="row mainContentRow">
                                <div class="col-xs-12 mainContentRowDesc">

                                </div>

                                <div id="addMyNewDeviceRow" class="col-xs-12 mainContentCellCnt">

                                    <table id="RulesContextExternalBrokerTable" class="table " cellspacing="0" width="100%">
                                        <thead>
                                            <tr style="background: rgb(0, 162, 211); color: rgb(255, 255, 255); font-size: 1em;">
                                                <th></th>	
                                                <th data-cellTitle="nameRule">Name</th>
                                                <th data-cellTitle="ModeRule">Mode</th> 
                                                <th data-cellTitle="Organization">Organization</th>
                                                <th data-cellTitle="EditRule">Edit</th>
                                                 <th data-cellTitle="Delete">Delete</th>
                                                <!--<th data-cellTitle="ViewRule">View</th>-->

                                            </tr>
                                        </thead>
                                    </table>
                                </div>  
                            </div>  
                            
                            
                            
                            
                            <!---<!-- Modal Delete rule -->
                             <div class="modal fade" id="deleteRuleModal" tabindex="-1" role="dialog" aria-labelledby="deleteRuleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteRuleModalLabel">Rule deletion</h5>
                    </div>
                    <div class="modal-body">
                        Do you want to confirm deletion of the following Rule?
                    </div>
                    <div id="deleteRuleModalInnerDiv1" class="modalBodyInnerDiv" style="display: none;"><h5>Rule deletion in progress, please wait</h5></div>
                    <div id="deleteRuleModalInnerDiv2" class="modalBodyInnerDiv" style="display: none;"><i class="fa fa-circle-o-notch fa-spin" style="font-size:36px"></i></div>
                    <div class="modal-footer">
                        <button type="button" id="deleteRuleOkBtn" class="btn btn-primary" data-dismiss="modal" style="display: none;">Ok</button>
                        <button type="button" id="deleteRuleCancelBtn" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="button" id="deleteRuleConfirmBtn" class="btn btn-primary">Confirm</button>
                    </div>
                </div>
            </div>
        </div>




                            <!-- Start of Update Multiple devices -->

                            <div class="modal fade" id="updateMultipleDeviceModal" tabindex="-1" role="dialog" aria-hidden="true">
                                <div class="modal-dialog modal-lg" role="document">
                                    <div class="modal-content">
                                        <div id="title_rule_form" class="modalHeader centerWithFlex">

                                        </div>

                                        <div id="addContextBrokerModalBody" class="modal-body modalBody">

                                            <div class="tab-content">

                                                <!-- Info tab -->
                                                <div id="infoTabCB" class="tab-pane fade in active">
                                                    
                                                    <input type="text" id="NAMERule" style="display: none">  
                                                    
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
                                                                <table id="decisionBlockTableValue" class="decisionBlockBulk" >
                                                                    <thead>
                                                                    <th width="15%"></th>
                                                                    <th width="40%">Fields</th>
                                                                    <th width="40%">Predicted Value</th>
                                                                    <th width="5%"><i id="addDecisionBlockBtnValue" class="fa fa-plus"></i></th>
                                                                    </thead>
                                                                    <tbody>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                            <input type="hidden" id="authorizedPagesJson" name="authorizedPagesJson" />
                                                        </div>
                                                    </div>

                                                    <!-- modal activation -->
                                                    
                                                    <div class="row">

                                <div class="col-xs-12 modalCell">
                                    <div class="modalFieldCnt">
                                        <table id="resultBlockTable" class="resultBlockBulk">
                                            <thead>
                                            <th></th>
                                            <th>Is the rule iterable?</th>
                                            <th></th>
                                            <th></th>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        
                                                    </td>
                                                    <td>
                                                        <select id="Update_mode" class="modalInputTxt" style="width:40%">
                                                                        <option value="0">No, it is not</option>
                                                                        <option value="1">Yes, it is</option>
                                                                    </select>
                                                    </td>
                                                    <td>
                                                        
                                                    </td>
                                                    <td> 

                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>

                                       
                                    </div>
                                    <input type="hidden" id="authorizedPagesJson" name="authorizedPagesJson" />
                                </div>
                            </div>
                                                    

                                                    

                                                    <div class="modal-footer">
                                                        <button type="button" id="Update_rules" class="btn confirmBtn">Ok</button>
                                                        <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
 <!-- modal confirm rule updating -->

    <div class="modal fade" id="updateMultipleDeviceModalConfirm" tabindex="-1" role="dialog" aria-labelledby="deleteAllDevModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteAllDevModalLabel">Rule updates</h5>
                </div>
                <div class="modal-body">
                   The rule is updated
                </div>
                <div class="modal-footer">
                    <button type="button" id="deleteAllDevCancelBtn" class="btn btn-secondary" data-dismiss="modal">OK</button>
                    
                </div>
            </div>
        </div>
    </div>

 <!-- modal confirm rule updating -->

    <div class="modal fade" id="updateMultipleDeviceModalProblem" tabindex="-1" role="dialog" aria-labelledby="deleteAllDevModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteAllDevModalLabel">Rule updates</h5>
                </div>
                <div class="modal-body">
                   The rule is problem for uptading
                </div>
                <div class="modal-footer">
                    <button type="button" id="deleteAllDevCancelBtn" class="btn btn-secondary" data-dismiss="modal">OK</button>
                    
                </div>
            </div>
        </div>
    </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>    
    </body>
</html>		




