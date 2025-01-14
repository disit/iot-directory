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
    $default_title = "IoT Directory: Devices";
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
        <link href="https://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css">

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

        <!-- select2 -->
        <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>


        <!-- Filestyle -->
        <script  src="../js/filestyle/src/bootstrap-filestyle.min.js"></script>

        <!-- Font awesome icons -->
        <link rel="stylesheet" href="../js/fontAwesome/css/font-awesome.min.css">

        <!-- Custom CSS -->
        <link href="../css/dashboard.css" rel="stylesheet">
        <style> .btn-round {
                width: 30px;
                height:30px;
                border-radius: 50%;
            }
            #mainMenuCnt
            {
                background-color: rgba(51, 64, 69, 1);
                color: white;
                height: 100vh;
                <?php if ($hide_menu == "hide") echo "display:none"; //MM201218 ?>
            }


        </style>

        <script>
            $( function() {
                $( "#inputFromDateBC" ).datepicker({dateFormat: 'yy-mm-dd'}).on('changeDate',function(){$( "#inputFromDateBC" ).val()});
                $( "#inputToDateBC" ).datepicker({dateFormat: 'yy-mm-dd'}).on('changeDate',function(){$( "#inputToDateBC" ).val()});
            } );

        </script>

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
            var sessionToken = "<?php if (isset($_SESSION['refreshToken'])) echo $_SESSION['refreshToken'];
                else echo ""; ?>";
            var mypage = location.pathname.split("/").slice(-1)[0];
            var functionality = [];
            var currentDictionaryStaticAttribAdd = [];
            var currentDictionaryStaticAttribEdit = [];

            $.ajax(  {
                url: "../api/blockchainrequests.php",
                data: {
                    action: 'get_all_device',
                    page: mypage,
                    token: sessionToken
                },
                type: "GET",
                async: false,
                dataType: 'json',
                success: function (mydata) {
                    if (mydata["status"] == 'ok') {
                        functionality = mydata["content"];
                        console.log(JSON.stringify(mydata))
                    }else {
                        console.log("Error from the DB" + JSON.stringify(mydata));
                        alert("An error occured when reading the data. <br/> Get in touch with the Snap4City Administrator. <br/>" + mydata["error_msg"]);
                    }
                },
                error: function (mydata) {
                    console.log(JSON.stringify(mydata));
                    alert("An error occured when reading the data. <br/> Get in touch with the Snap4City Administrator");
                }
            });
            $.ajax({
                url: "../api/functionality.php",
                data: {
                    action: 'get_functionality',
                    page: mypage,
                    token: sessionToken
                },
                type: "GET",
                async: false,
                dataType: 'json',
                success: function (mydata) {
                    if (mydata["status"] == 'ok')
                        functionality = mydata["content"];
                    else {
                        console.log("Error from the DB" + mydata["msg"]);
                        alert("An error occured when reading the data. <br/> Get in touch with the Snap4City Administrator. <br/>" + mydata["error_msg"]);
                    }
                },
                error: function (mydata) {
                    console.log(JSON.stringify(mydata));
                    alert("An error occured when reading the data. <br/> Get in touch with the Snap4City Administrator");
                }
            });
        </script>
        <!-- Custom scripts -->
        <script  src="js/blockchainrequests.js"></script>
        <script  src="js/devicesManagement.js"></script>
        <script  src="js/fieldsManagement.js"></script>
        <script  src="js/devicesEditManagement.js"></script>
        <script  src="../js/dashboard_mng.js"></script>
        <script  src="js/common.js"></script>	

        <!-- leaflet scripts -->
        <script  src="../js/leaflet.js"></script>
        <script  src="../js/leaflet.draw.js"></script>
        <script  src="../js/jquery.fancytree-all.min.js"></script>

    </head>
    <body class="guiPageBody">
        <div class="container-fluid">
                <?php include "sessionExpiringPopup.php" ?>
            <div class="row mainRow">
                <?php include "mainMenu.php" ?>
                <div
                <?php //MM201218
                if (($hide_menu == "hide")) {
                    ?>
                        class="col-xs-12 col-md-12"
                    <?php } else { ?>
                        class="col-xs-12 col-md-10"
<?php } //MM201218 FINE ?>
                    id="mainCnt">
                    <div class="row hidden-md hidden-lg">
                        <div id="mobHeaderClaimCnt" class="col-xs-12 hidden-md hidden-lg centerWithFlex">
                            Snap4City
                        </div>
                    </div>
<?php //MM201218
if (($hide_menu != "hide")) {
    ?>
                        <div class="row" id="title_row">
                            <div class="col-xs-10 col-md-12 centerWithFlex" id="headerTitleCnt">IoT Directory: Devices</div>
                            <div class="col-xs-2 hidden-md hidden-lg centerWithFlex" id="headerMenuCnt"><!--?php include "mobMainMenu.php" ?--></div>
                        </div>
<?php } //MM201218 FINE  ?>

                    <div class="row">
                        <div class="col-xs-12" id="mainContentCnt">
                            <div id="synthesis" class="row hidden-xs hidden-sm mainContentRow">
                                <div id="dashboardTotNumberCnt" class="col-md-3 mainContentCellCnt">
                                    <div class="col-md-12 centerWithFlex pageSingleDataCnt">
                                        <?php
                                        $query = "SELECT count(*) AS qt FROM Blockchain_verification_requests WHERE request_status = 'pending'";
                                        $result = mysqli_query($link, $query);
                                        if ($result) {
                                            $row = $result->fetch_assoc();
                                            echo $row['qt'] . ' pending';
                                        } else {
                                            echo '-' . ' pending';
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div id="dashboardTotActiveCnt" class="col-md-3 mainContentCellCnt">
                                    <div class="col-md-12 centerWithFlex pageSingleDataCnt">
                                        <?php
                                        //MM
                                        $query = "SELECT count(*) AS qt FROM Blockchain_verification_requests WHERE request_status = 'execution'";
                                        $result = mysqli_query($link, $query);
                                        if ($result) {
                                            $row = $result->fetch_assoc();
                                            echo $row['qt'] . ' in execution';
                                        } else {
                                            echo '-' . ' in execution';
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div id="dashboardTotPermCnt" class="col-md-3 mainContentCellCnt">
                                    <div class="col-md-12 centerWithFlex pageSingleDataCnt">
                                        <?php
                                        //MM
                                        $query = "SELECT count(*) AS qt FROM Blockchain_verification_requests WHERE request_status = 'failed'";
                                        $result = mysqli_query($link, $query);
                                        if ($result) {
                                            $row = $result->fetch_assoc();
                                            echo $row['qt'] . ' failed';
                                        } else {
                                            echo '-' . ' failed';
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div id="dashboardTotPrivateCnt" class="col-md-3 mainContentCellCnt">
                                    <div class="col-md-12 centerWithFlex pageSingleDataCnt">
                                        <?php
                                        $query = "SELECT count(*) AS qt FROM Blockchain_verification_requests WHERE request_status = 'completed'";
                                        $result = mysqli_query($link, $query);
                                        if ($result) {
                                            $row = $result->fetch_assoc();
                                            echo $row['qt'] . ' completed';
                                        } else {
                                            echo '-' . ' completed';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <div id="addMyNewDeviceRow" class="row mainContentRow">
                                <div class="col-xs-12 mainContentRowDesc">Add My New Device</div>
                                <div class="col-xs-12 col-md-6 mainContentCellCnt">
                                    <div id="myDeviceForm" class="row mainContentRow">
                                        <div class="row">
                                            <div class="col-xs-12 col-md-6 modalCell">
                                                <div class="modalFieldCnt">
                                                    <input type="text" class="modalInputTxt" name="inputNameDeviceUser" id="inputNameDeviceUser" onkeyup="checkStrangeCharacters(this)" required>
                                                </div>
                                                <div class="modalFieldLabelCnt">Device Identifier</div>
                                                <div id="inputNameDeviceUserMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                            </div>

                                            <div class="col-xs-12 col-md-6 modalCell">
                                                <div class="modalFieldCnt">
                                                    <select id="selectModel" name="selectModel" class="modalInputTxt">
                                                        <!--?php
                    $query = "SELECT name, kgenerator FROM model";
                    $result = mysqli_query($link, $query);

                    if($result)
                    {
                       while($row = $result->fetch_assoc())
                       {
                         $name=$row["name"];
                                                                         $kgen = $row["kgenerator"];
                         //echo "<option data_key=\"$kgen\" value=\"$name\">$name</option>";
                                                                         echo "<option data_key=\"$kgen\" value=\"$name\">$name</option>";
                       }

                    }
                    else
                    {

                        $name="ERROR";
                        echo "<option value=\"$name\">$name</option>";
                    }
                ?-->
                                                    </select>
                                                </div>
                                                <div class="modalFieldLabelCnt">Model</div>
                                                <div id="selectModelMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                            </div>
                                        </div>
<!--                                        <div class="row">-->
<!--                                            <div class="col-xs-12 col-md-6 modalCell">-->
<!--                                                <div class="modalFieldCnt">-->
<!--                                                    <input type="text" class="modalInputTxt" name="inputLatitudeDeviceUser" id="inputLatitudeDeviceUser">-->
<!--                                                </div>-->
<!--                                                <div class="modalFieldLabelCnt">Latitude</div>-->
<!--                                                <div id="inputLatitudeDeviceUserMsg" class="modalFieldMsgCnt">&nbsp;</div>-->
<!--                                            </div>-->
<!--                                            <div class="col-xs-12 col-md-6 modalCell">-->
<!--                                                <div class="modalFieldCnt">-->
<!--                                                    <input type="text" class="modalInputTxt" name="inputLongitudeDeviceUser" id="inputLongitudeDeviceUser">-->
<!--                                                </div>-->
<!--                                                <div class="modalFieldLabelCnt">Longitude</div>-->
<!--                                                <div id="inputLongitudeDeviceUserMsg" class="modalFieldMsgCnt">&nbsp;</div>-->
<!--                                            </div>-->
<!--                                        </div>-->
                                    </div>
                                    <!--
                                    <div class="row mainContentRow">
                                             <div id="addSchemaTabDevice">
                                                    <div id="addlistAttributesUser"></div>
                                                    <div class="pull-left"><button id="addAttrBtnUser" class="btn btn-primary">Add Value</button></div>
                                                    <div id="addlistAttributesUserMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                            </div>
                                            <div id="addMyDeviceModalFooter" class="modal-footer">
                                                    <button type="button" id="addMyNewDeviceConfirmBtn" class="btn btn-primary">Submit Device</button>
                                            </div>
    </div>
                                    -->
                                    <div id="addMyDeviceModalFooter" class="modal-footer">
                                        <button type="button" id="addMyNewDeviceConfirmBtn" class="btn btn-primary">Submit Device</button>
                                    </div>

                                </div>

                                <div class="col-xs-12 col-md-6 mainContentCellCnt">

<!--                                    <div id="addMapUser">-->
<!--                                        <div>-->
<!--                                            <h4 style="text-align: center; color:blue;"> Select Latitude/Longitude on Map </h4>-->
<!--                                        </div>-->
<!--                                        <div class="form-row iot-directory-form-row">-->
<!--                                            <link rel="stylesheet" href="../css/leaflet.css" />-->
<!--                                            <link rel="stylesheet" href="../css/leaflet.draw.css" />-->
<!--                                            <div id="addDeviceMapModalBodyUser" style="width: 100%; height: 400px" class="modal-body modalBody">-->
<!--                                            </div>-->
<!--                                        </div>-->
<!--                                    </div>-->

                                </div>


                            </div>


                            <div id="displayAllDeviceRow" class="row mainContentRow">
                                <div class="col-xs-12 mainContentRowDesc "></div>
                                <div class="col-xs-12 mainContentCellCnt ">
                                    <div class="row" style= "background-color: rgb(241, 245, 244);">
                                        <div class="col-xs-12 col-md-6 modalCell " style= "background-color: rgb(241, 245, 244);">
<!--                                            <div class="pull-left "><button id="ShowOnlyDelegated"  class="btn btn-primary">Show delegated dev.</button></div><div >&nbsp;<button id="ShowOnlyPublic"  class="btn btn-primary">Show public dev.</button>&nbsp;<button id="ShowOnlyOwn"  class="btn btn-primary">Show my dev.</button>&nbsp;<button id="ShowAll"  class="btn btn-primary">Show all dev.</button></div>-->
<!--                                            <div id="displayDevicesMap" class="pull-right"><button type="button" class="btn btn-primary btn-round"><span class="glyphicon glyphicon-globe" style="font-size:36px; color: #0000ff"></span></button></div>-->
                                        </div>
                                        <div class="col-xs-12 col-md-6 modalCell" style= "background-color: rgb(241, 245, 244);">
                                            <div><div class="pull-right "><button id="addDeviceBCBtn"  class="btn btn-primary" onclick="populateDropdownDevice()">Add new request</button></div></div>
                                        </div>
                                    </div>
                                    <div >
                                        <table id="devicesTable" class="table table-bordered table-striped nowrap" cellspacing="0"  width="100%">
                                            <thead>
                                                <tr style="background: rgb(0, 162, 211); color: rgb(255, 255, 255); font-size: 1em;">
                                                    <th data-cellTitle="device_id">Device Identifier</th>
                                                    <th data-cellTitle="start_date">From date</th>
                                                    <th data-cellTitle="end_date">To date</th>
                                                    <th data-cellTitle="owner_id">Owner</th>
                                                    <th data-cellTitle="request_status">Request Status</th>
                                                    <th data-cellTitle="downloadreport">Report</th>
                                                    <th data-cellTitle="check_performed">Check Performed</th>
                                                    <th data-cellTitle="missing_data">missing data</th>
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





<!--        MODALE TESTING -->
<!--        <div class="modal fade" id="testDeviceModal" tabindex="-1" role="dialog" aria-hidden="true">-->
<!--            <div class="modal-dialog  modal-lg" role="document">-->
<!--                <div class="modal-content">-->
<!--                    <div id="DeviceModalTest" class="modalHeader centerWithFlex">-->
<!--                        <h5 class="col-xs-12 centerWithFlex">Let's check the broke!</h5>-->
<!--                    </div>-->
<!--                    <div  class="modal-body">-->
<!--                        <p id="TestDEVModalStatus"></p>-->
<!--                    </div>-->
<!--                    <div class="modal-footer">-->
<!--                        <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>-->
<!--                    </div>-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->
        <!-- MODALE TESTING END -->


        <!-- NEW VALUES INPUT MODAL END -->


        <!-- NEW DEVICE MODAL MODAL END -->










        <!-- Adding a New device -->
        <div class="modal fade" id="addDeviceModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modalHeader centerWithFlex">
                        Add a new verification request
                    </div>

                    <div id="addDeviceModalBody" class="modal-body modalBody">

                        <ul id="addDeviceModalTabs" class="nav nav-tabs nav-justified">
                            <li  class="active"><a data-toggle="tab" href="#addInfoTabDevice">New Request</a></li>

                        </ul>

                        <div class="tab-content">


                            <!-- Info tab -->
                            <div id="addInfoTabDevice" class="tab-pane fade in active">
                                <div class="row">
                                    <div class="col-xs-12 col-md-6 modalCell">
                                        <div class="modalFieldCnt">
                                            <select class="selectInputNameDevice" name="inputNameDevice" id="inputNameDevice"></select>
<!--                                            <input type="text" class="modalInputTxt" name="inputNameDevice" id="inputNameDevice" onkeyup="checkStrangeCharacters(this)" required>-->
                                        </div>
                                        <div class="modalFieldLabelCnt">Device Identifier</div>
                                        <div id="inputNameDeviceMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12 col-md-6 modalCell">
                                        <div class="modalFieldCnt">
                                            <input type="text" class="modalInputTxt" onkeyup="checkStrangeCharacters(this)" name="inputFromDateBC" id="inputFromDateBC">
                                        </div>
                                        <div class="modalFieldLabelCnt">From date (yyyy-mm-dd)</div>
                                        <div id="inputFromDateBCMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                    </div>
                                    <div class="col-xs-12 col-md-6 modalCell">
                                        <div class="modalFieldCnt">
                                            <input type="text" class="modalInputTxt" name="inputToDateBC" id="inputToDateBC">
                                        </div>
                                        <div class="modalFieldLabelCnt">To date (yyyy-mm-dd)</div>
                                        <div id="inputToDateBCMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                    </div>
                                </div>

                                <div id="sigFoxDeviceUserMsg" class="modalFieldMsgCnt">&nbsp;</div>
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
                        <div class="col-xs-12 centerWithFlex"></div>
                        <div class="col-xs-12 centerWithFlex">Error adding device</div>
                    </div>
                    <div class="row" id="addDeviceKoIcon">
                        <div class="col-xs-12 centerWithFlex"><i class="fa fa-thumbs-o-down" style="font-size:36px"></i></div>
                    </div>


                    <div id="addDeviceModalFooter" class="modal-footer">
                        <div class="row">

                            <div align="left">
                                <div id="addDeviceCheckExternalLoadingIcon" style="display:none;">
                                    <i class="fa fa-circle-o-notch fa-spin" style="font-size:36px;"></i> <i>checking...</i> </div>
                            </div>
                            <div  align="right">
                                <button type="text" id="addNewDeviceCancelBtn" class="btn cancelBtn" data-dismiss="modal">Cancel</button>
                                <button type="text" id="addNewDeviceConfirmBtn" name="addNewDeviceConfirmBtn" class="btn confirmBtn internalLink">Confirm</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success  -->
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
                                <div id="addDeviceOkModalInnerDiv1" ></div>
                                <div id="addDeviceOkModalInnerDiv3" hidden><h5 ><b>A \'Timestamp\' attribute called \'DateObserved\' has been added to the device because it was not previously present.</b></h5></div>
                                    <script>
                                        function removeTimestampDiv(){
                                            $("#addDeviceOkModalInnerDiv3").hide();
                                         }
                                    </script>
                                <div class="modalDelObjName col-xs-12 centerWithFlex" id="addDeviceOkModalInnerDiv2"><i class="fa fa-check" style="font-size:36px"></i></div>

                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="addDeviceOKDoneBtn" class="btn cancelBtn"  data-dismiss="modal" onclick="removeTimestampDiv()">Done</button>
                    </div>
                </div>
            </div>
        </div>




        <!-- Success  -->

        <div class="modal fade" id="addDeviceOkModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modalHeader centerWithFlex">
                        Success device registration
                    </div>
                    <div id="addDeviceModalBody" class="modal-body modalBody">
                        <div class="row">
                            <div class="col-xs-12 modalCell">
                                <div id="addDeviceOkModalInnerDiv1">
                                </div>
                                <div class="modalDelObjName col-xs-12 centerWithFlex" id="addDeviceOkModalInnerDiv2"><i class="fa fa-check" style="font-size:36px"></i></div> 
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="addDeviceModalCancelBtn" class="btn btn-secondary" data-dismiss="modal">DONE</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- fail -->
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
                        <button type="button" id="addDeviceKoCancelBtn" class="btn cancelBtn" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
















        <!-- Modal for Ownership Visibility and Delegations -->
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
                                        <div class="col-xs-12 input-group" id="newDelegationCnt">
                                            <div class="col-xs-8">
                                                <input type="text" class="form-control" name="newDelegation" id="newDelegation" placeholder="Delegated username">
                                            </div>
                                            <div class="col-xs-4">
                                                <select name="newDelegationKind" id="newDelegationKind" class="modalInputTxt">
                                                    <option value="READ_ACCESS">READ_ACCESS</option>
                                                    <option value="READ_WRITE">READ_WRITE</option>
                                                    <option value="MODIFY">MODIFY</option>
                                                </select>
                                            </div>
                                            <span class="col-xs-12 input-group-btn" style="width:100%">
                                                <button type="button" id="newDelegationConfirmBtn" class="btn confirmBtn disabled" style="margin:10px 0;width:100%">Confirm</button>
                                            </span>
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
                                                <th>Kind</th>
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
                                                    <select name="newDelegationKindGroup" id="newDelegationKindGroup" class="modalInputTxt">
                                                        <option value="READ_ACCESS">READ_ACCESS</option>
                                                        <option value="READ_WRITE">READ_WRITE</option>
                                                        <option value="MODIFY">MODIFY</option>
                                                    </select>
                                                </div>
                                                <span class="col-xs-12 input-group-btn" style="width:100%">
                                                    <button type="button" id="newDelegationConfirmBtnGroup" class="btn confirmBtn" style="margin:10px 0;width:100%">Confirm</button>
                                                </span>
                                                <div class="col-xs-12 centerWithFlex delegationsModalMsg" id="newDelegatedMsgGroup">
                                                </div>
                                            </div>
                                        </div>
                                        <legend><div class="col-xs-12 centerWithFlex" id="currentDelegationsLblGroup">
                                                Current Group delegations
                                            </div></legend>
                                        <div class="col-xs-12" id="delegationsTableCntGroup">
                                            <table id="delegationsTableGroup" style="width:100%">
                                                <thead>
                                                <th>Delegated group</th>
                                                <th>Kind</th>
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




