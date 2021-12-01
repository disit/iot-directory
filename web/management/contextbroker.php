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
    $default_title = "IoT Directory: Context Brokers";
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

require '../sso/autoload.php';

use Jumbojett\OpenIDConnectClient;

if (isset($_SESSION['refreshToken'])) {
    $oidc = new OpenIDConnectClient($keycloakHostUri, $clientId, $clientSecret);
    $oidc->providerConfigParam(array('token_endpoint' => $keycloakHostUri . '/auth/realms/master/protocol/openid-connect/token'));
    $tkn = $oidc->refreshToken($_SESSION['refreshToken']);
    $accessToken = $tkn->access_token;
    $_SESSION['refreshToken'] = $tkn->refresh_token;
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
                <?php if ($hide_menu == "hide") echo "display:none"; //MM201218   ?>
            }
        </style>

        <!-- Custom scripts     -->
        <script>
                var loggedRole = "<?php echo $_SESSION['loggedRole']; ?>";
                var loggedUser = "<?php echo $_SESSION['loggedUsername']; ?>";
                var admin = "<?php echo $_SESSION['loggedRole']; ?>";
            var organization = "<?php echo $_SESSION['organization']; ?>";
            var kbUrl = "<?php echo $_SESSION['kbUrl']; ?>";
            var gpsCentreLatLng = "<?php echo $_SESSION['gpsCentreLatLng']; ?>";
            var zoomLevel = "<?php echo $_SESSION['zoomLevel']; ?>";
            var deployOrionFilterAccess="<?php echo $deployOrionFilterAccess; ?>";	
            var deployOrionDirectAccess="<?php echo $deployOrionDirectAccess; ?>";	
                var titolo_default = "<?php echo $default_title; ?>";	
                var access_denied = "<?php echo $access_denied; ?>";
                var nascondi= "<?php echo $hide_menu; ?>";
                var sessionEndTime = "<?php echo $_SESSION['sessionEndTime']; ?>"; 
                var sessionToken = "<?php
                if (isset($_SESSION['refreshToken']))
                    echo $_SESSION['refreshToken'];
                else
                    echo "";
                ?>";
                var creatorVisibile = true;
                var detailView = true;
                var mypage = location.pathname.split("/").slice(-1)[0];
                var functionality = [];

            $.ajax({
                        url: "../api/functionality.php",
                        data: {
                                action: 'get_functionality', 
                                page : mypage, 
                                token: sessionToken
                        },
                        type: "GET",
                        async: false,
                        dataType: 'json',
                        success: function (mydata) {
                                if (mydata["status"]=='ok')
                                        functionality = mydata["content"];
                                else {
                                        console.log("Error from the DB" + mydata["msg"]);		   
                                        alert("An error occured when reading the data. <br/> Get in touch with the Snap4City Administrator. <br/>"+ mydata["error_msg"]);
                                }
                        },
                        error: function (mydata) {
                                console.log(JSON.stringify(mydata));
                                alert("An error occured when reading the data. <br/> Get in touch with the Snap4City Administrator");
                        }
                }); 
        </script>

        <script type="text/javascript" src="../js/dashboard_mng.js"></script>

        <!-- Custom scripts -->
        <script type="text/javascript" src="js/contextbroker.js"></script>

        <!-- Custom scripts -->
        <script type="text/javascript" src="js/cbsManagement.js"></script>
        <script type="text/javascript" src="js/cbsEditManagement.js"></script>
        <script type="text/javascript" src="js/fieldsManagement.js"></script>

        <!-- leaflet scripts -->
        <script type="text/javascript" src="../js/leaflet.js"></script>
        <script type="text/javascript" src="../js/leaflet.draw.js"></script>
        <script type="text/javascript" src="../js/jquery.fancytree-all.min.js"></script>

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
                    <?php } //MM201218 FINE    ?>
                    id="mainCnt">
                    <div class="row hidden-md hidden-lg">
                        <div id="mobHeaderClaimCnt" class="col-xs-12 hidden-md hidden-lg centerWithFlex">
                            Snap4City IoT Directory
                        </div>
                    </div>
                    <?php
//MM201218
                    if (($hide_menu != "hide")) {
                        ?>
                        <div class="row" id="title_row">
                            <div class="col-xs-10 col-md-12 centerWithFlex" id="headerTitleCnt">IoT Directory: Context Brokers</div>
                            <div class="col-xs-2 hidden-md hidden-lg centerWithFlex" id="headerMenuCnt"><!--?php include "mobMainMenu.php" ?--></div>
                        </div>
                    <?php } //MM201218 FINE     ?>

                    <div class="row">
                        <div class="col-xs-12" id="mainContentCnt">
                            <div id="synthesis" class="row hidden-xs hidden-sm mainContentRow">
                                <div class="col-xs-12 mainContentRowDesc"></div>
                                <div id="dashboardTotNumberCnt" class="col-md-3 mainContentCellCnt">
                                    <div class="col-md-12 centerWithFlex pageSingleDataCnt">
                                        <?php
                                        $query = "SELECT count(*) AS qt FROM contextbroker";
                                        $result = mysqli_query($link, $query);
                                        if ($result) {
                                            $row = $result->fetch_assoc();
                                            echo $row['qt'] . ' Total';
                                        } else {
                                            echo '-' . ' Total';
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
                                            <div id="displayDevicesMapCB" class="pull-right"><button type="button" class="btn btn-primary btn-round"><span class="glyphicon glyphicon-globe" style="font-size:36px; color: #0000ff"></span></button></div>
                                        </div>
                                        <div class="col-xs-12 col-md-6 modalCell" style= "background-color: rgb(241, 245, 244);">
                                            <?php if ($_SESSION['loggedRole'] == 'RootAdmin' || $_SESSION['loggedRole'] == 'ToolAdmin') { ?>

                                                <?php if ($deployOrion == true) { ?>
                                                    <div class="pull-right "><button id="RegisterCBBtn"  class="btn btn-primary" style="margin:5px;">Deploy new orion broker</button></div>
                                                <?php } ?>                                                
                                                <div class="pull-right"><button id="addContextBrokerBtn"  class="btn btn-primary"  style="margin:5px;">Register new IOT Broker</button></div>

                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div>
                                        <table id="contextBrokerTable" class="table table-bordered table-striped" cellspacing="0" width="100%">
                                            <thead>
                                                <tr style="background: rgb(0, 162, 211); color: rgb(255, 255, 255); font-size: 1em;">
                                                    <th></th>	
                                                    <th data-cellTitle="name">IOT Broker</th>
                                                    <th data-cellTitle="accesslink">Access Link</th>
                                                    <th data-cellTitle="accessport">Access Port</th>
                                                    <th data-cellTitle="kind">Kind</th>
                                                    <th data-cellTitle="protocol">Protocol</th>
                                                    <th data-cellTitle="ownership">Ownership</th>
                                                    <th data-cellTitle="organization">Organization</th>
                                                    <th data-cellTitle="owner">Owner</th>
                                                    <th data-cellTitle="created">Created</th>
                                                    <th data-cellTitle="edit">Edit</th>
                                                    <th data-cellTitle="delete">Delete</th>
                                                    <th data-cellTitle="goto">Go to</th>
                                                    <th data-cellTitle="check">Utility</th>
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


        <div class="modal fade" id="deleteContextBrokerModal" tabindex="-1" role="dialog" aria-labelledby="deleteCBModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteCBModalLabel">Context Broker deletion</h5>
                    </div>
                    <div class="modal-body">
                        Do you want to confirm deletion of the following context broker?
                    </div>
                    <div id="deleteCBModalInnerDiv1" class="modalBodyInnerDiv" style="display: none;"><h5>Context broker deletion in progress, please wait</h5></div>
                    <div id="deleteCBModalInnerDiv2" class="modalBodyInnerDiv" style="display: none;"><i class="fa fa-circle-o-notch fa-spin" style="font-size:36px"></i></div>
                    <div class="modal-footer">
                        <button type="button" id="deleteContextBrokerOkBtn" class="btn btn-primary" data-dismiss="modal" style="display: none;">Ok</button>
                        <button type="button" id="deleteContextBrokerCancelBtn" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="button" id="deleteContextBrokerConfirmBtn" class="btn btn-primary">Confirm</button>
                    </div>
                </div>
            </div>
        </div>


        <!-- NEW DEVICE MODAL START -->
        <div class="modal fade" id="RegisterCBeModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog  modal-lg" role="document">
                <div class="modal-content">
                    <div id="RegisterCBModal" class="modalHeader centerWithFlex">
                        <h5 class="col-xs-12 centerWithFlex">Add a new  device</h5>
                    </div>
                    <div  class="modal-body">
                        <p id="RegisterCBStatus"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                        <button type="button" id="RegisterCBConfirmButton" class="btn confirmBtn">Confirm</button>
                    </div>
                </div>
            </div>
        </div> 
        <!-- NEW DEVICE MODAL END -->



        <!-- ADD DEVICE MODAL NULL START -->
        <!-- Modal -->
        <div class="modal fade" id="addContextBrokerModalNULL" role="dialog">
            <div class="modal-dialog">

                <!-- Modal content-->
                <div class="modal-content">
                    <div  class="modalHeader centerWithFlex">

                        <h5 class="col-xs-12 centerWithFlex">Oops, something went wrong</h5>
                    </div>
                    <div class="modal-body">
                        <p>Plese contact the admistrator for add a new Context broker</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn cancelBtn" data-dismiss="modal">Close</button>
                    </div>
                </div>

            </div>
        </div>


        <!-- ADD DEVICE MODAL NULL  END -->


        <!-- Create Context Broker -->
        <div class="modal fade" id="addContextBrokerModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div id= "addContextBrokerModalLabel" class="modalHeader centerWithFlex">

                    </div>

                    <div id="addContextBrokerModalBody" class="modal-body modalBody">
                        <ul id="addContextBrokerModalTabs" class="nav nav-tabs nav-justified">
                            <li id='tab-addCB-1' class="active"><a data-toggle="tab" href="#infoTabCB">Info</a></li>
                            <li id="multiServiceTabSelector" class="hidden"><a data-toggle="tab" href="#serviceTenantTabCB">Multitenancy</a></li>
                            <li id='tab-addCB-2'><a data-toggle="tab" href="#geoPositionTabCB">Geo-Position</a></li>
                            <li id='tab-addCB-3'><a data-toggle="tab" href="#securityTabCB">Security</a></li>
                            <li id='tab-addCB-4'><a data-toggle="tab"  href="#subscriptionTabCB">Subscription</a></li>
                        </ul>

                        <div class="tab-content">

                            <!-- Info tab -->
                            <div id="infoTabCB" class="tab-pane fade in active">
                                <div class="row">


                                    <div class="col-xs-12 col-md-6 modalCell">          
                                        <div class="modalFieldCnt">
                                            <select id="selectKindCB" name="selectKindCB" class="modalInputTxt">
                                                <option></option>
                                                <option value="internal">Internal</option>
                                                <option value="external">External</option>
                                            </select>
                                        </div>
                                        <div class="modalFieldLabelCnt">Kind</div>
                                        <div id="selectKindCBMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                    </div>

                                    <div class="col-xs-12 col-md-6 modalCell">
                                        <div class="modalFieldCnt">
                                            <input type="text" class="modalInputTxt" name="inputNameCB" id="inputNameCB" onkeyup="checkStrangeCharacters(this)" required>
                                        </div>
                                        <div class="modalFieldLabelCnt">Name</div>
                                        <div id="inputNameCBMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                    </div>

                                </div>	

                                <div class="row">							

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

                                </div>	
                                <div class="row">	
                                    <div class="col-xs-12 col-md-6 modalCell">
                                        <div class="modalFieldCnt">
                                            <select id="selectProtocolCB" name="selectProtocolCB" class="modalInputTxt">
                                                <!-- <option value="none">None</option> -->
                                                <?php
                                                $link = mysqli_connect($host, $username, $password);
                                                mysqli_select_db($link, $dbname);

                                                $q1 = "SELECT name FROM protocols";
                                                $r1 = mysqli_query($link, $q1);

                                                if ($r1) {
                                                    while ($row = $r1->fetch_assoc()) {
                                                        echo '<option value="' . $row['name'] . '">' . $row['name'] . '</option>';
                                                    }
                                                } else
                                                    echo mysqli_error($link);
                                                ?>
                                            </select>
                                        </div>
                                        <div class="modalFieldLabelCnt">Protocol</div>
                                        <div id="inputProtocolCBMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                    </div>

                                    <div class="col-xs-12 col-md-6 modalCell">
                                        <div class="modalFieldCnt">
                                            <input type="text" class="modalInputTxt" name="inputVersionCB" id="inputVersionCB"> 
                                        </div>
                                        <div class="modalFieldLabelCnt">Version</div>
                                        <div id="inputVersionCBMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                    </div>

                                </div>

                                <div class="row">
                                    <div class="col-xs-12 col-md-6 modalCell">
                                        <div class="modalFieldCnt">
                                            <input type="text" class="modalInputTxt" name="inputAccessLinkCB" id="inputAccessLinkCB"> 
                                        </div>
                                        <div class="modalFieldLabelCnt">Access Link</div>
                                        <div id="inputAccessLinkCBMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                    </div>

                                    <div class="col-xs-12 col-md-6 modalCell">
                                        <div class="modalFieldCnt">
                                            <input type="text" class="modalInputTxt" name="inputAccessPortCB" id="inputAccessPortCB"> 
                                        </div>
                                        <div class="modalFieldLabelCnt">Access Port</div>
                                        <div id="inputAccessPortCBMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                    </div>

                                </div>	

                                <div id="loginExternal" class="row">
                                    <div class="col-xs-12 col-md-6 modalCell">
                                        <div class="modalFieldCnt">
                                            <input type="text" class="modalInputTxt" name="inputApiKeyCB" id="inputApiKeyCB"> 
                                        </div>
                                        <div class="modalFieldLabelCnt">API Key</div>
                                        <div id="inputApiKeyCBMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                    </div>
                                    <div class="col-xs-12 col-md-6 modalCell">
                                        <div class="modalFieldCnt">
                                            <input type="text" class="modalInputTxt" name="inputPathCB" id="inputPathCB"> 
                                        </div>
                                        <div class="modalFieldLabelCnt">Path</div>
                                        <div id="inputPathCBMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                    </div>

                                </div>

                                <div class="row">

                                    <div class="col-xs-12 col-md-6 modalCell">
                                        <div class="modalFieldCnt">
                                            <select id="selectVisibilityCB" name="selectVisibilityCB" class="modalInputTxt">
                                                <option value="private">Private</option>
                                            </select>
                                        </div>
                                        <div class="modalFieldLabelCnt">Ownership</div>
                                        <div id="selectVisibilityCBMsg" class="modalFieldMsgCnt">&nbsp;</div> 
                                    </div>

                                </div>

                            </div>

                            <!-- MultiService/Tenant tab -->
                            <div id="serviceTenantTabCB" class="tab-pane fade">
                                <div id="inputServiceCBMsg" style="margin-top: 10px;">&nbsp;</div>
                                <div class="row" id="serviceCBRow1">
                                    <div class="col-xs-12 col-md-8 modalCell">          
                                        <div class="modalFieldCnt">
                                            <input type="text" class="modalInputTxt" name="inputServiceCB" id="inputServiceCB" onkeyup="checkStrangeCharacters(this)" required>
                                        </div>
                                        <div class="modalFieldLabelCnt">Multitenancy</div>
                                    </div>

                                    <div class="col-xs-12 col-md-4 modalCell">
                                        <div class="modalFieldCnt">
                                            <button type="text" id="addNewCBServiceBtn" class="btn confirmBtn">Add Service/Tenant</button>
                                        </div>
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
                                <div id="loginInternal" class="row">
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

                                <div class="row">
                                    <div class="col-xs-12 col-md-6 modalCell">
                                        <div class="modalFieldCnt">
                                            <input type="text" class="modalInputTxt" name="inputSHACB" id="inputSHACB"> 
                                        </div>
                                        <div class="modalFieldLabelCnt" name="inputSHACB_lab" id="inputSHACB_lab">SHA</div>
                                        <div>
                                            <div>
                                                <input type="checkbox" id="Login_CB" name="Login_CB"   >
                                                <label for="Login_CB" id="Login_CB_lab" style="display: contents;">Enable direct access to orion broker (not recommended)</label>
                                            </div>
                                            <div id="inputSHACBMsg" class="modalFieldMsgCnt">&nbsp;</div>

                                        </div>

                                    </div>

                                </div>
                            </div>

                            <!-- Subscription Tab -->
                            <div id="subscriptionTabCB" class="tab-pane fade">
                                <div class="row">
                                    <div class="col-xs-12 col-md-6 modalCell">
                                        <div class="modalFieldCnt">
                                            <input type="text" class="modalInputTxt" name="inputUrlOrionCallback"  style="display: block;"id="inputUrlOrionCallback">
                                        </div>
                                        <div class="modalFieldLabelCnt">Url Orion Callback</div>
                                        <div id="selectUrlOrionCallbackMsgHint" class="modalFieldMsgCnt">Warning:If you use an external context broker , the callback uri must be reached and visible from outside</div>
                                        <div id="selectUrlOrionCallbackMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                    </div>
                                </div>
                            </div>
                            <!-- End Subscription Tab-->


                        </div>


                    </div>


                    <div class="row" id="addCBLoadingMsg">
                        <div class="col-xs-12 centerWithFlex">Adding Contextbroker, please wait</div>
                    </div>
                    <div class="row" id="addCBLoadingIcon">
                        <div class="col-xs-12 centerWithFlex"><i class="fa fa-circle-o-notch fa-spin" style="font-size:36px;"></i></div>
                    </div>
                    <div class="row" id="addCBOkMsg">
                        <div class="col-xs-12 centerWithFlex">Contextbroker added successfully</div>
                        <div id="additionInfoOKadd" class="col-xs-12 centerWithFlex">&nbsp;</div>
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
                    <div id="addContextBrokerModalFooter" class="modal-footer">
                        <button type="button" id="addContextBrokerCancelBtn" class="btn cancelBtn" data-dismiss="modal">Cancel</button>
                        <button type="button" id="addContextBrokerOkBtn" class="btn cancelBtn" data-dismiss="modal" style="display:none">Ok</button>
                        <button type="submit" id="addContextBrokerConfirmBtn" name="addContextBrokerConfirmBtn" class="btn confirmBtn internalLink">Confirm</button>
                    </div>



                </div>
            </div>
        </div>
    </div>

    <!--  MODALE UPDATING -->
    <div class="modal fade" id="UpdatingContextBrokerModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog  modal-lg" role="document">
            <div class="modal-content">
                <div id="editCBModalTest" class="modalHeader centerWithFlex">
                    <h5 class="col-xs-12 centerWithFlex">Context Broker updating</h5>
                </div>
                <div  class="modal-body">
                    <p id="UpdatingModalStatus">The updating is started. You can check the status in orion status field.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="window.location.reload()">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!--  MODALE UPDATING END -->

    <!--  MODALE TESTING -->
    <div class="modal fade" id="testContextBrokerModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog  modal-lg" role="document">
            <div class="modal-content">
                <div id="editCBModalTest" class="modalHeader centerWithFlex">
                    <h5 class="col-xs-12 centerWithFlex">Let's check the broker!</h5>
                </div>
                <div  class="modal-body">
                    <p id="TestModalStatus"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal" >Close</button>
                </div>
            </div>
        </div>
    </div>
    <!--  MODALE TESTING END -->

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
                    <ul id="editContextBrokerModalTabs" class="nav nav-tabs nav-justified">
                        <li id="tab-editCB-1" class="active"><a  data-toggle="tab" href="#editInfoTabCB">Info</a></li>
                        <li id="editMultiServiceTabSelector" class="hidden"><a data-toggle="tab" href="#editServiceTenantTabCB">Multitenancy</a></li>
                        <li id="tab-editCB-2" ><a data-toggle="tab" href="#editGeoPositionTabCB">Geo-Position</a></li>
                        <li id="tab-editCB-3"><a data-toggle="tab" href="#editSecurityTabCB">Security</a></li>
                        <li id="tab-editCB-4"><a data-toggle="tab" href="#editSubscriptionTabCB">Subscription</a></li>
                    </ul>

                    <div class="tab-content">

                        <!-- Info tab -->
                        <div id="editInfoTabCB" class="tab-pane fade in active">
                            <div class="row">

                                <div class="col-xs-12 col-md-6 modalCell">          
                                    <div class="modalFieldCnt">
                                        <select id="selectKindCBM" name="selectKindCBM" class="modalInputTxt">
                                            <option></option>
                                            <option value="internal">Internal</option>
                                            <option value="external">External</option>
                                        </select>
                                    </div>
                                    <div class="modalFieldLabelCnt">Kind</div>
                                    <div id="selectKindCBMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>

                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputNameCBM" id="inputNameCBM" onkeyup="checkStrangeCharacters(this)" required readonly> 
                                        <input type="text" class="modalInputTxt" name="inputOrganizationCBM" id="inputOrganizationCBM" style="display:none"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Name</div>
                                    <div id="inputNameCBMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>


                            </div>

                            <div class="row">

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

                            </div>
                            <div class="row">
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <select id="selectProtocolCBM" name="selectProtocolCBM" class="modalInputTxt">
                                            <option value="none">None</option>
                                            <?php
                                            $link = mysqli_connect($host, $username, $password);
                                            mysqli_select_db($link, $dbname);

                                            $q1 = "SELECT name FROM protocols";
                                            $r1 = mysqli_query($link, $q1);

                                            if ($r1) {
                                                while ($row = $r1->fetch_assoc()) {
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
                                        <input type="text" class="modalInputTxt" name="inputVersionCBM" id="inputVersionCBM"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Version</div>
                                    <div id="inputVersionCBMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>

                            </div>	

                            <div class="row">
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputAccessLinkCBM" id="inputAccessLinkCBM"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Access Link</div>
                                    <div id="inputAccessLinkCBMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>

                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputAccessPortCBM" id="inputAccessPortCBM"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Access Port</div>
                                    <div id="inputAccessPortCBMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>

                            </div>	


                            <div class="row">

                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <select id="selectVisibilityCBM" name="selectVisibilityCBM" class="modalInputTxt">
                                            <option></option>	
                                            <option value="private">Private</option>
                                            <option value="public">Public</option>
                                        </select>
                                    </div>
                                    <div class="modalFieldLabelCnt">Ownership</div>
                                    <div id="selectVisibilityCBMMsg" class="modalFieldMsgCnt">&nbsp;</div> 
                                </div>


                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input id="createdDateCBM" name="createdDateCBM" type="text" readonly>
                                    </div>
                                    <div class="modalFieldLabelCnt">Created</div>
                                    <div id="createdDateCBMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>

                            </div>

                            <div class="row">
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputApiKeyCBM" id="inputApiKeyCBM"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">API Key</div>
                                    <div id="inputApiKeyCBMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>

                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputPathCBM" id="inputPathCBM"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Path</div>
                                    <div id="inputPathCBMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>

                            </div>

                        </div>

                        <div id="editServiceTenantTabCB" class="tab-pane fade">
                            <div id="editInputServiceCBMsg" style="margin-top: 10px;">&nbsp;</div>
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
                                        <input type="text" class="modalInputTxt NotShowIfDyn" name="inputLoginCBM" id="inputLoginCBM"> 
                                    </div>
                                    <div class="modalFieldLabelCnt NotShowIfDyn">Login</div>
                                    <div id="inputLoginCBMMsg" class="modalFieldMsgCnt NotShowIfDyn">&nbsp;</div>
                                </div>
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt NotShowIfDyn" name="inputPasswordCBM" id="inputPasswordCBM"> 
                                    </div>
                                    <div class="modalFieldLabelCnt NotShowIfDyn">Password</div>
                                    <div id="inputPasswordCBMMsg" class="modalFieldMsgCnt NotShowIfDyn">&nbsp;</div>
                                </div>

                            </div>
                            <div class="row">
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt NotShowIfDyn" name="inputSHACBM" id="inputSHACBM"> 
                                    </div>
                                    <div class="modalFieldLabelCnt NotShowIfDyn">SHA</div>
                                    <div>
                                        <input type="checkbox" class="ShowIfDyn"  id="Login_CB_view" name="Login_CB_view"   >
                                        <label for="Login_CB_view" class="ShowIfDyn" style="display: block;">Enable direct access to orion broker (not recommended)</label>

                                    </div>

                                    <div id="inputSHACBMMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                </div>
                            </div>
                            <a href="<?php echo $deployOrionDirectAccess; ?>" target="_blank" id="Login_CB_view_ref" name="Login_CB_view_ref" class="ShowIfDyn">
                                <div class="modalFieldLabelCnt ShowIfDyn" id="Login_CB_view_url" name="Login_CB_view_url"><?php echo $deployOrionDirectAccess; ?></div></a>





                        </div>

                        <!-- Subscription Tab -->
                        <div id="editSubscriptionTabCB" class="tab-pane fade">
                            <div class="row">
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputUrlOrionCallbackM" id="inputUrlOrionCallbackM">

                                        <div id="" class="modalFieldLabelCnt">Url Orion Callback</div>
                                        <div id="selectUrlOrionCallbackMsgHintM" class="modalFieldMsgCnt">Warning:If you use an external context broker , the callback uri must be reached and visible from outside </div>
                                        <div id="selectUrlOrionCallbackMsgurlOrionCallbackLabelMM" class="modalFieldMsgCnt">&nbsp;</div>
                                    </div> </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldLabelCnt" id="substatusCBMMsg" name="substatusCBMMsg">&nbsp;</div>
                                </div>
                            </div>
                        </div>
                        <!-- End Subscription Tab-->
                    </div>
                </div>

                <div class="row" id="editContextBrokerLoadingMsg">
                    <div class="col-xs-12 centerWithFlex">Updating context broker, please wait</div>
                </div>
                <div class="row" id="editContextBrokerLoadingIcon">
                    <div class="col-xs-12 centerWithFlex"><i class="fa fa-circle-o-notch fa-spin" style="font-size:36px;"></i></div>
                </div>
                <div class="row" id="editContextBrokerOkMsg">
                    <div class="col-xs-12 centerWithFlex">Context Broker updated successfully</div>
                    <div id="additionInfoOKedit" class="col-xs-12 centerWithFlex">&nbsp;</div>
                </div>
                <div class="row" id="editContextBrokerOkIcon">
                    <div class="col-xs-12 centerWithFlex"><i class="fa fa-thumbs-o-up" style="font-size:36px"></i></div>
                </div>
                <div class="row" id="editContextBrokerKoMsg">
                    <div class="col-xs-12 centerWithFlex">Error updating device</div>
                    <div id="editDeviceOkModalInnerDiv1" class="modalDelMsg col-xs-12 centerWithFlex">

                    </div>
                </div>
                <div class="row" id="editContextBrokerKoIcon">
                    <div class="col-xs-12 centerWithFlex"><i class="fa fa-thumbs-o-down" style="font-size:36px"></i></div>
                </div>
                <div id="editContextBrokerModalFooter" class="modal-footer">
                    <button type="button" id="editContextBrokerCancelBtn" class="btn cancelBtn" data-dismiss="modal" onclick="window.location.reload()">Cancel</button>
                    <button type="button" id="editContextBrokerConfirmBtn" class="btn confirmBtn internalLink" >Confirm</button>
                    <button type="button" id="editContextBrokerOkBtn" class="btn cancelBtn" data-dismiss="modal" style="display:none;" >Ok</button>
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
                <div class="modal-footer">
                    <button type="button" id="cancelMapBtn" class="btn cancelBtn"  data-dismiss="modal">Cancel</button>
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





