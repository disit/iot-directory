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
        <script  src="../legacy/management/js/devices.js"></script>
        <script  src="../legacy/management/js/devicesManagement.js"></script>
        <script  src="../legacy/management/js/fieldsManagement.js"></script>
        <script  src="../legacy/management/js/devicesEditManagement.js"></script>
        <script  src="../js/dashboard_mng.js"></script>
        <script  src="../legacy/management/js/common.js"></script>	

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
                                        $query = "SELECT count(*) AS qt FROM devices";
                                        $result = mysqli_query($link, $query);
                                        if ($result) {
                                            $row = $result->fetch_assoc();
                                            echo $row['qt'] . ' devices';
                                        } else {
                                            echo '-' . ' devices';
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div id="dashboardTotActiveCnt" class="col-md-3 mainContentCellCnt">
                                    <div class="col-md-12 centerWithFlex pageSingleDataCnt">
                                        <?php
                                        //MM
                                        $query = "SELECT count(*) AS qt FROM devices WHERE  mandatoryproperties = true and mandatoryvalues = true ";
                                        $result = mysqli_query($link, $query);
                                        if ($result) {
                                            $row = $result->fetch_assoc();
                                            echo $row['qt'] . ' active';
                                        } else {
                                            echo '-' . ' active';
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div id="dashboardTotPermCnt" class="col-md-3 mainContentCellCnt">
                                    <div class="col-md-12 centerWithFlex pageSingleDataCnt">
                                        <?php
                                        //MM
                                        $query = "SELECT count(*) AS qt FROM devices where visibility='public'";
                                        $result = mysqli_query($link, $query);
                                        if ($result) {
                                            $row = $result->fetch_assoc();
                                            echo $row['qt'] . ' public';
                                        } else {
                                            echo '-' . ' public';
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div id="dashboardTotPrivateCnt" class="col-md-3 mainContentCellCnt">
                                    <div class="col-md-12 centerWithFlex pageSingleDataCnt">
                                        <?php
                                        $query = "SELECT count(*) AS qt FROM devices where visibility='private'";
                                        $result = mysqli_query($link, $query);
                                        if ($result) {
                                            $row = $result->fetch_assoc();
                                            echo $row['qt'] . ' private';
                                        } else {
                                            echo '-' . ' private';
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
                                        <div class="row">
                                            <div class="col-xs-12 col-md-6 modalCell">
                                                <div class="modalFieldCnt">
                                                    <input type="text" class="modalInputTxt" name="inputLatitudeDeviceUser" id="inputLatitudeDeviceUser"> 
                                                </div>
                                                <div class="modalFieldLabelCnt">Latitude</div>
                                                <div id="inputLatitudeDeviceUserMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                            </div>
                                            <div class="col-xs-12 col-md-6 modalCell">
                                                <div class="modalFieldCnt">
                                                    <input type="text" class="modalInputTxt" name="inputLongitudeDeviceUser" id="inputLongitudeDeviceUser"> 
                                                </div>
                                                <div class="modalFieldLabelCnt">Longitude</div>
                                                <div id="inputLongitudeDeviceUserMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                            </div>
                                        </div>
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

                                    <div id="addMapUser">
                                        <div>
                                            <h4 style="text-align: center; color:blue;"> Select Latitude/Longitude on Map </h4>
                                        </div>
                                        <div class="form-row iot-directory-form-row">
                                            <link rel="stylesheet" href="../css/leaflet.css" />
                                            <link rel="stylesheet" href="../css/leaflet.draw.css" />
                                            <div id="addDeviceMapModalBodyUser" style="width: 100%; height: 400px" class="modal-body modalBody">
                                            </div>
                                        </div> 
                                    </div>

                                </div>


                            </div>


                            <div id="displayAllDeviceRow" class="row mainContentRow">
                                <div class="col-xs-12 mainContentRowDesc "></div>
                                <div class="col-xs-12 mainContentCellCnt ">
                                    <div class="row" style= "background-color: rgb(241, 245, 244);">
                                        <div class="col-xs-12 col-md-6 modalCell " style= "background-color: rgb(241, 245, 244);">
                                            <div class="pull-left "><button id="ShowOnlyDelegated"  class="btn btn-primary">Show delegated dev.</button></div><div >&nbsp;<button id="ShowOnlyPublic"  class="btn btn-primary">Show public dev.</button>&nbsp;<button id="ShowOnlyOwn"  class="btn btn-primary">Show my dev.</button>&nbsp;<button id="ShowAll"  class="btn btn-primary">Show all dev.</button></div>
                                            <div id="displayDevicesMap" class="pull-right"><button type="button" class="btn btn-primary btn-round"><span class="glyphicon glyphicon-globe" style="font-size:36px; color: #0000ff"></span></button></div>
                                        </div>
                                        <div class="col-xs-12 col-md-6 modalCell" style= "background-color: rgb(241, 245, 244);">
                                            <div class="pull-right ">
                                                <button id="importDeviceBtn" class="btn btn-primary" style="margin-left:3px" >Import New Device</button>
                                            </div>
                                                <div class="pull-right ">
                                                    <button id="addDeviceBtn"  class="btn btn-primary" style="margin-left:3px">Add new device</button>
                                                </div>
                                            <?php if ($_SESSION['loggedRole'] == "RootAdmin" ) { ?>
                                            <div class="pull-left">
                                                <button id="checkDeviceBtn" class="btn btn-primary">Check devices</button>
                                            </div>
                                            <?php } ?>


                                        </div>
                                    </div>
                                    <div >
                                        <table id="devicesTable" class="table table-bordered table-striped nowrap" cellspacing="0"  width="100%">
                                            <thead>
                                                <tr style="background: rgb(0, 162, 211); color: rgb(255, 255, 255); font-size: 1em;">
                                                    <th></th>	
                                                    <th data-cellTitle="name">Device Identifier</th>
                                                    <th data-cellTitle="contextbroker">IOT Broker</th>
                                                    <th data-cellTitle="devicetype">Device Type</th>
                                                    <th data-cellTitle="model">Model</th>
                                                    <th data-cellTitle="ownership">Ownership</th>
                                                    <th data-cellTitle="status">Status</th>							
                                                    <th data-cellTitle="edit">Edit</th>
                                                    <th data-cellTitle="delete">Delete</th>		
                                                    <th data-cellTitle="location">Location</th>
                                                    <th data-cellTitle="check">View</th>									
                                                    <th data-cellTitle="retry">Retry</th>
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
        </div>



        <div class="modal fade" id="retryDeviceModal" tabindex="-1" role="dialog" aria-labelledby="retryDeviceModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">

                    <div id="retryHeaderModal" class="modalHeader centerWithFlex"></div>

                    <div id="retryDeviceModalBody" class="modal-body modalBody">
                        <b style="color: red">THIS FUNCTION IS IN DEVELOPMENT</b>
                        <br>
                        <br>
                        Snap4City reported the following errors:
                        <br>
                        <br>
                        <div class="row"">
                            <div id="dbReporting"></div>
                        </div>
                        <div class="row">
                            <div id="kbReporting"></div>
                        </div>
                        <div class="row">
                            <div id="brokerReporting"></div>
                        </div>
                    <div class="row">
                        <div id="ownershipReporting"></div>
                    </div>
                    <div class="row">
                        <div id="UNrecoverableMessage"><br>some of this are <b>Unrecoverable</b>.<br>Please delete the device and insert it again.</div>
                        <div id="recoverableMessage"><br>some of this are <b>Recoverable</b>.</div>
                    </div>
                    <div class="row">
                        <div id="UNrecoverableMessageOWN"><br><b>You can't recover the device because you are not the owner<b></div>
                        <div id="recoverableMessageOWN"><br><b>You can try to recover the device<b></div>
                    </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="retryDeviceCancelBtn" class="btn cancelBtn" data-dismiss="modal">Cancel</button>
                        <button type="button" id="retryDeviceBtn" class="btn confirmBtn internalLink" data-dismiss="modal"  disabled>Retry</button>
                    </div>
                </div>
            </div>
            </div>



        <div class="modal fade" id="retryDeviceResultModal" tabindex="-1" role="dialog" aria-labelledby="retryDeviceResultModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">

                    <div id="retryHeaderResultModal" class="modalHeader centerWithFlex"></div>

                    <div id="retryDeviceResultModalBody" class="modal-body modalBody">
                        <b style="color: red">THIS FUNCTION IS IN DEVELOPMENT</b>
                        <br>
                        <br>
                        <br>
                        <br>
                        <div class="row"">
                            <div id="dbReportingResult"></div>
                        </div>
                        <div class="row">
                            <div id="kbReportingResult"></div>
                        </div>
                        <div class="row">
                            <div id="brokerReportingResult"></div>
                        </div>

                    <div class="row">
                        <div id="retryResultMsgOk"><br>Device recovered succesfully</div>
                        <div id="retryResultMsgError"><br>Device not recovered</div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" id="retryDeviceResultBtn" class="btn confirmBtn internalLink" data-dismiss="modal" onclick="location.reload()">Ok</button>
                    </div>
                </div>
            </div>
            </div>
        </div>




        <!--MODALE TESTING -->
        <div class="modal fade" id="testDeviceModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog  modal-lg" role="document">
                <div class="modal-content">
                    <div id="DeviceModalTest" class="modalHeader centerWithFlex">
                        <h5 class="col-xs-12 centerWithFlex">Let's check the broke!</h5>
                    </div>
                    <div  class="modal-body">
                        <p id="TestDEVModalStatus"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- MODALE TESTING END -->


        <!-- NEW VALUES INPUT MODAL -->
        <div class="modal fade" id="NewValuesInputMODAL" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog  modal-lg" role="document">
                <div class="modal-content">
                    <div id="NewValuesInputMODAL" class="modalHeader centerWithFlex">
                        <h5 class="col-xs-12 centerWithFlex">Insert new data on your device</h5>
                    </div>


                    <div   class="modal-body">  

                        <ul id="InsertDeviceModalTabs" class="nav nav-tabs nav-justified">


                            <li class="active"><a id="Mtab" data-toggle="tab" href="#editGeoPositionTabDeviceNewValue">Insert new position</a></li>
                            <li ><a id="Itab" data-toggle="tab" href="#editAttributeValueTabDevice">Insert new value</a></li>

                            <div class="tab" id="InsertDataDeviceLoadingIcon">

                                <div class="col-xs-12 centerWithFlex"><i id="LoadingGif" class="fa fa-circle-o-notch fa-spin" style="font-size:36px;"></i></div></div>



                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane active" id="Itab" role="tabpanel" aria-labelledby="data-ex-tab1-tab">
                                <div id="ValuesINPUT" class="tabcontent">
                                    <div></div>
                                </div>
                                <p id="InsertModalStatus"></p>

                                <div id="NOMob" class="row">
                                    <div class="col-xs-12 col-md-6 modalCell">
                                        <div class="modalFieldCnt">
                                            <input type="text" class="modalInputTxt" name="inputLatitudeDeviceValue" id="inputLatitudeDeviceValue"> 
                                        </div>
                                        <div class="modalFieldLabelCnt">Latitude</div>

                                    </div>
                                    <div class="col-xs-12 col-md-6 modalCell">
                                        <div class="modalFieldCnt">
                                            <input type="text" class="modalInputTxt" name="inputLongitudeDeviceValue" id="inputLongitudeDeviceValue"> 
                                        </div>
                                        <div class="modalFieldLabelCnt">Longitude</div>

                                    </div>
                                </div>
                                <div class="tab-pane " id="Mtab" role="tabpanel" aria-labelledby="data-ex-tab1-tab">
                                    <p id="NoMobile"></p> 
                                    <div id="editLatLongValue" style="width: 100%; height: 400px" class="modal-body modalBody"></div>
                                </div> </div>
                        </div> </div> 


                    <div class="modal-footer">



                        <button type="button" id="newValuesInputCancelButton" class="btn cancelBtn" data-dismiss="modal" >Close</button>
                        <button type="button" id="NewValuesInputConfirmButton" class="btn confirmBtn">Confirm</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- NEW VALUES INPUT MODAL END -->


        <!-- NEW DEVICE MODAL MODAL END -->




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



        <div class="modal fade" id="deleteDeviceModal" tabindex="-1" role="dialog" aria-labelledby="deleteDeviceModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteDeviceModalLabel">Device deletion</h5>
                    </div>
                    <div class="modal-body">
                        Do you want to confirm deletion of the following device?
                    </div>
                    <div id="deleteDeviceModalInnerDiv1" class="modalBodyInnerDiv" style="display: none;"><h5>Device deletion in progress, please wait</h5></div>
                    <div id="deleteDeviceModalInnerDiv2" class="modalBodyInnerDiv" style="display: none;"><i class="fa fa-circle-o-notch fa-spin" style="font-size:36px"></i></div>
                    <div class="modal-footer">
                        <button type="button" id="deleteDeviceOkBtn" class="btn btn-primary" data-dismiss="modal" style="display: none;">Ok</button>
                        <button type="button" id="deleteDeviceCancelBtn" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="button" id="deleteDeviceConfirmBtn" class="btn btn-primary">Confirm</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Adding a New device -->
        <div class="modal fade" id="addDeviceModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modalHeader centerWithFlex">
                        Add a new device
                    </div>

                    <div id="addDeviceModalBody" class="modal-body modalBody">

                        <ul id="addDeviceModalTabs" class="nav nav-tabs nav-justified">
                            <li  class="active"><a data-toggle="tab" href="#addInfoTabDevice">Info</a></li>
                            <li><a data-toggle="tab" href="#addIOTBrokerTabDevice">IOT Broker</a></li>
                            <li><a data-toggle="tab" href="#addGeoPositionTabDevice">Position</a></li>
                            <li><a data-toggle="tab" href="#addStaticTabModel">Static Attributes</a></li>
                            <li><a data-toggle="tab" href="#addSchemaTabDevice">Values</a></li>
                            <li><a data-toggle="tab" href="#addHLTTabDevice">HLT</a></li>
                        </ul>

                        <div class="tab-content">

                            <!-- IOT Broker tab -->
                            <div id="addIOTBrokerTabDevice" class="tab-pane fade">

                                <div class="row">

                                    <div class="col-xs-12 col-md-6 modalCell">
                                        <div class="modalFieldCnt">
                                            <select id="selectContextBroker" name="selectContextBroker" class="modalInputTxt">
                                                <option></option>
                                            </select>
                                        </div>
                                        <div class="modalFieldLabelCnt">ContextBroker</div>
                                        <div id="selectContextBrokerMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                    </div>
                                    <div class="col-xs-12 col-md-6 modalCell">
                                        <div class="modalFieldCnt">
                                            <select id="selectKindDevice" name="selectKindDevice" class="modalInputTxt">
                                                <option value="sensor">sensor</option>
                                                <option value="actuator">actuator</option>
                                            </select>
                                        </div>
                                        <div class="modalFieldLabelCnt">Kind</div>
                                        <div id="selectKindDeviceMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                    </div>
                                </div>


                                <div class="row">

                                    <div class="col-xs-12 col-md-6 modalCell">
                                        <div class="modalFieldCnt">
                                            <select id="selectProtocolDevice" name="selectProtocolDevice" class="modalInputTxt">
                                                <option></option>
                                                <option value="amqp">amqp</option>
                                                <option value="coap">coap</option>
                                                <option value="mqtt">mqtt</option>
                                                <option value="ngsi">ngsi</option>
                                                <option value="ngsi w/MultiService">ngsi w/MultiService</option>
                                            </select>
                                        </div>
                                        <div class="modalFieldLabelCnt">Protocol</div>
                                        <div id="selectProtocolDeviceMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                    </div>
                                    <div class="col-xs-12 col-md-6 modalCell">
                                        <div class="modalFieldCnt">
                                            <select id="selectFormatDevice" name="selectFormatDevice" class="modalInputTxt">
                                                <option></option>
                                                <option value="csv">csv</option>
                                                <option value="json">json</option>
                                                <option value="xml">xml</option>
                                            </select>
                                        </div>
                                        <div class="modalFieldLabelCnt">Format</div>
                                        <div id="selectFormatDeviceMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                    </div>
                                    <div class="col-xs-12 col-md-6 modalCell"><div id="externalContextBrokerMsg" class="modalFieldMsgCnt"></div></div>
                                    <div class="col-xs-12 col-md-6 modalCell"></div>

                                </div>    

                                <!-- Start MultiService + ServicePath Section -->
                                <div class = "row" id = "multiServiceAndServicePath">
                                    <div class="col-xs-12 col-md-6 modalCell">
                                        <div class="modalFieldCnt">
                                            <select id="selectService" name="selectService" class="modalInputTxt" required>
                                                <option value="" selected></option>
                                                <!-- other options will be created dynamically-->
                                            </select>
                                        </div>
                                        <div id="selectServiceLabel" class="modalFieldLabelCnt">Service/Tenant</div>
                                        <div id="selectServiceMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                    </div>

                                    <div class="col-xs-12 col-md-6 modalCell">          
                                        <div class="modalFieldCnt">
                                            <input type="text" class="modalInputTxt" name="inputServicePathDevice" id="inputServicePathDevice" required>
                                        </div>
                                        <div id="inputServicePathLabel" class="modalFieldLabelCnt">ServicePath</div>
                                        <div id="inputServicePathMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                    </div>
                                </div>
                                <!-- End MultiService + ServicePath Section -->


                            </div>
                            <!-- Info tab -->
                            <div id="addInfoTabDevice" class="tab-pane fade in active">
                                <div class="row">
                                    <div class="col-xs-12 col-md-6 modalCell">
                                        <div class="modalFieldCnt">
                                            <input type="text" class="modalInputTxt" name="inputNameDevice" id="inputNameDevice" onkeyup="checkStrangeCharacters(this)" required> 
                                        </div>
                                        <div class="modalFieldLabelCnt">Device Identifier</div>
                                        <div id="inputNameDeviceMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                    </div>
                                    <div class="col-xs-12 col-md-6 modalCell">
                                        <div class="modalFieldCnt">
                                          <select id="selectModelDevice" name="selectModelDevice" class="modalInputTxt">
                                                <option></option>
                                                
                                            </select>
<!--										<select name="selectModelDevice" id="selectModelDevice" class="select2-selection__rendered">
                                                                                        <option></option>   
                                                                                    <option data_key="normal" value="custom">custom</option>    
                                                                                        </select>-->
                                        </div>
                                        <div class="modalFieldLabelCnt">Model</div>
                                        <div id="inputModelDeviceMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                    </div>

                                </div>
                                <div class="row">
                                    <div class="col-xs-12 col-md-6 modalCell">
                                        <div class="modalFieldCnt">
                                            <input type="text" class="modalInputTxt" onkeyup="checkStrangeCharacters(this)" name="inputTypeDevice" id="inputTypeDevice"> 
                                        </div>
                                        <div class="modalFieldLabelCnt">Device Type</div>
                                        <div id="inputTypeDeviceMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                    </div>

                                    <div class="col-xs-12 col-md-6 modalCell">
                                        <div class="modalFieldCnt">
                                            <input type="text" class="modalInputTxt" name="inputMacDevice" id="inputMacDevice"> 
                                        </div>
                                        <div class="modalFieldLabelCnt">Mac Address</div>
                                        <div id="inputMacDeviceMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12 col-md-6 modalCell">
                                        <div class="modalFieldCnt">
                                            <select name="selectEdgeGatewayType" id="selectEdgeGatewayType" class="modalInputTxt">   
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
                                        <div id="selectEdgeGatewayTypeMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                    </div>
                                    <div class="col-xs-12 col-md-6 modalCell">
                                        <div class="modalFieldCnt">
                                            <input type="text" class="modalInputTxt" name="inputEdgeGatewayUri" id="inputEdgeGatewayUri"> 
                                        </div>
                                        <div class="modalFieldLabelCnt">Edge-Gateway URI</div>
                                        <div id="inputEdgeGatewayUriMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12 col-md-6 modalCell">
                                        <div class="modalFieldCnt">
                                            <input type="text" class="modalInputTxt" name="inputProducerDevice" id="inputProducerDevice"> 
                                        </div>
                                        <div class="modalFieldLabelCnt">Producer</div>
                                        <div id="inputProducerDeviceMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                    </div>

                                    <div class="col-xs-12 col-md-6 modalCell">
                                        <div class="modalFieldCnt">
                                            <div class="input-group unity-input"> <input type="text" class="modalInputTxt" name="inputFrequencyDevice" id="inputFrequencyDevice" value="600"> <span class="input-group-addon" id="basic-addon2">sec</span></div>

                                        </div>
                                        <div class="modalFieldLabelCnt">Frequency</div>
                                        <div id="inputFrequencyDeviceMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                    </div>

                                </div>	
                                <div class="row">
                                    <div class="col-xs-12 col-md-6 modalCell">
                                        <div class="modalFieldCnt">
                                            <select id="selectVisibilityDevice" name="selectVisibilityDevice" class="modalInputTxt">
                                                <option value="private">Private</option>
                                            </select>
                                        </div>
                                        <div class="modalFieldLabelCnt">Ownership</div>
                                        <div id="selectVisibilityDeviceMsg" class="modalFieldMsgCnt">&nbsp;</div> 
                                    </div>
                                    <!--Fatima3-->
                                    <div class="modalFieldCnt">
                                        <button type="text" id="addNewDeviceGenerateKeyBtn" class="btn confirmBtn internalLink" onclick="generateKeysCLicked()">Generate Keys</button>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12 col-md-6 modalCell">
                                        <div class="modalFieldCnt">
                                            <input type="text" class="modalInputTxt" name="KeyOneDeviceUser" id="KeyOneDeviceUser"> 
                                        </div>
                                        <div class="modalFieldLabelCnt">KEY 1</div>
                                        <div id="KeyOneDeviceUserMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                    </div>
                                    <div class="col-xs-12 col-md-6 modalCell">
                                        <div class="modalFieldCnt">
                                            <input type="text" class="modalInputTxt" name="KeyTwoDeviceUser" id="KeyTwoDeviceUser"> 
                                        </div>
                                        <div class="modalFieldLabelCnt">KEY 2</div>
                                        <div id="KeyTwoDeviceUserMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                    </div>
                                </div>
                                <div id="sigFoxDeviceUserMsg" class="modalFieldMsgCnt">&nbsp;</div>
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
                                <h1>&nbsp;</h1>
                                <div class="form-row iot-directory-form-row">
                                    <link rel="stylesheet" href="../css/leaflet.css" />
                                    <link rel="stylesheet" href="../css/leaflet.draw.css" />
                                    <div id="addLatLong" style="width: 100%; height: 400px" class="modal-body modalBody">
                                    </div>
                                </div> 
                                <div id="positionMsgHint" class="modalFieldMsgCnt" hidden="true"><h1>&nbsp;</h1><p class="text-danger font-weight-bold">WARNING: the indicated GPS Position has to be intended as the Initial Position of the Device. To see the Current Position of the Device, please proceed via Service Map</p></div>	
                            </div>

                            <!-- Device Schema tab -->
                            <div id="addSchemaTabDevice" class="tab-pane fade">


                                <div id="addlistAttributes"></div>
                                <div class="pull-left"><button id="addAttrBtn" class="btn btn-primary">Add Value</button></div>
                                <div id="addlistAttributesMsg" class="modalFieldMsgCnt">&nbsp;</div>


                            </div>

                            <!-- Static Attributes tab -->
                            <div id="addStaticTabModel" class="tab-pane fade">
                                <div class="row">
                                    <div class="col-xs-12 col-md-8 modalCell" >
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="isMobileTick">
                                            <label class="custom-control-label" for="isMobileTick">Device in Mobility</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row" <?php echo enableIsCertified(); ?> >
                                    <div class="col-xs-12 col-md-8 modalCell">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="isCertifiedTick">
                                            <label class="custom-control-label" for="isCertifiedTick">Certified</label>
                                        </div>
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
                                <div class="row">
                                    <div class="col-xs-12 col-md-12 modalCell">
                                        <div id="addlistStaticAttributes"></div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12 col-md-12 modalCell">
                                        <div class="pull-left"><button type="text" id="addNewStaticBtn" class="btn confirmBtn" style="display: none;">Add Attribute</button></div>
                                    </div>
                                </div>
                            </div>

                            <div id="addHLTTabDevice" class="tab-pane fade">
                                <div class="row">

                                    <div class="col-xs-12 col-md-6 modalCell">
                                        <select id="selectHLT" name="selectHLT" class="modalInputTxt" required>
                                            <option value="iot_device_entity">IOT Device/entity</option>
                                            <option value="Heatmap">Heatmap</option>
                                            <option value="Traffic_flow">Traffic flow</option>
                                            <option value="Origin_destination_matrix">Origin Destination Matrix</option>
                                            <option value="Trajectory_path">Trajectory path</option>
                                            <option value="Gardens">Garden</option>
                                            <option value="Building_plant">Building plant</option>
                                            <option value="Building">Building 3D</option>
                                            <option value="Floor">Floor</option>
                                            <option value="Cycling_paths">Cycling Path</option>
                                            <option value="Entity_group">Entity Group</option>
                                            <option value="Typical_time_trend">Typical time trend</option>
                                            <option value="Scenario">Scenario</option>
                                            <option value="Vector_field">Vector Field</option>
                                            <option value="Parking_slot">Parking slot</option>
                                        </select>
                                        <div class="modalFieldLabelCnt">Select HLT</div>

                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12 col-md-12 modalCell">
                                        <textarea id="wktGeometryText" name="wktGeometry" rows="4" cols="0" style="max-width: 100%;min-width:100%"></textarea>
                                        <label for="wktGeometryText" style="display:block">wktGeometry</label>
                                        <div id="addHLTattributesMsg" class="modalFieldMsgCnt">&nbsp;</div>
                                    </div>
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
                                <button type="text" id="addNewDeviceCheckExternalBtn" name="addNewDeviceConfirmBtn" class="btn confirmBtn internalLink" style="display:none;">Check</button>
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
                                            console.log("entrato")
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

        <!-- Update -->
        <div class="modal fade" id="editDeviceModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog  modal-lg" role="document">
                <div class="modal-content">
                    <div id="editDeviceModalLabel" class="modalHeader centerWithFlex">
                        Update Device
                    </div>

                    <div id="editDeviceModalBody" class="modal-body modalBody">

                        <ul id="editDeviceModalTabs" class="nav nav-tabs nav-justified">
                            <li  class="active"><a data-toggle="tab" href="#editInfoTabDevice">Info</a></li>
                            <li><a data-toggle="tab" href="#editIOTBrokerTabDevice">IoT Broker</a></li>
                            <li><a data-toggle="tab" href="#editGeoPositionTabDevice">Position</a></li>
                            <li><a data-toggle="tab" href="#editStaticTabModel">Static Attributes</a></li>
                            <li><a data-toggle="tab" href="#editSchemaTabDevice">Values</a></li>
                            <li><a data-toggle="tab" href="#editHLTTabDevice">HLT</a></li>
                            <li><a id="EStatus" data-toggle="tab" href="#editStatusTabDevice">Status</a></li>

                        </ul>

                        <div class="tab-content">
                            <!-- IOT Broker tab -->
                            <div id="editIOTBrokerTabDevice" class="tab-pane fade">

                                <div class="row">

                                    <div class="col-xs-12 col-md-6 modalCell">
                                        <div class="modalFieldCnt">
                                            <select id="selectContextBrokerM" name="selectContextBrokerM" class="modalInputTxt">				
                                                
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
                                <div class = "row" id = "editMultiServiceAndServicePath">
                                    <div class="col-xs-12 col-md-6 modalCell">
                                        <div class="modalFieldCnt">
                                            <select id="editSelectService" name="editSelectService" class="modalInputTxt" required>
                                                <option value="" selected></option>
                                                <!-- other options will be created dynamically-->
                                            </select>
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
                            <div id="editInfoTabDevice" class="tab-pane fade in active">


                                <div class="row">

                                    <div class="col-xs-12 col-md-6 modalCell">
                                        <div class="modalFieldCnt">
                                            <input type="text" class="modalInputTxt" name="inputNameDeviceM" id="inputNameDeviceM" onkeyup="checkStrangeCharacters(this)" required> 
                                            <input type="text" class="modalInputTxt" name="inputOrganizationDeviceM" id="inputOrganizationDeviceM" style="display:none"> 
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
                                            <input type="text" class="modalInputTxt" onkeyup="checkStrangeCharacters(this)" name="inputTypeDeviceM" id="inputTypeDeviceM"> 
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
                                        <div class="modalFieldCnt" >
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
                                        <button type="text" id="editDeviceGenerateKeyBtn" class="btn confirmBtn internalLink" onclick="editGenerateKeysCLicked()">Generate Keys</button>
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
                                <div id="positionMsgHintM" class="modalFieldMsgCnt" hidden="true"><h1>&nbsp;</h1><p class="text-danger font-weight-bold">WARNING: the indicated GPS Position has to be intended as the Initial Position of the Device. To see the Current Position of the Device, please proceed via Service Map</p></div>
                            </div>

                            <!-- Attribute tab -->
                            <div id="editSchemaTabDevice" class="tab-pane fade">

                                <div id="editlistAttributes"></div>
                                <div id="addlistAttributesM"></div>
                                <div id="deletedAttributes" style="display:none"></div>
                                <div class="row">
                                <div class="pull-left"><button id="addAttrMBtn" class="btn btn-primary">Add Value</button></div></div>
                                <div id="editlistAttributesMsg" class="modalFieldMsgCnt">&nbsp;</div>
                            </div>

                            <!-- Static Attributes tab -->
                            <div id="editStaticTabModel" class="tab-pane fade">
                                <div class="row">
                                    <div class="col-xs-12 col-md-8 modalCell" >
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="isMobileTickM">
                                            <label class="custom-control-label" for="isMobileTickM">Device in Mobility</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row" <?php echo enableIsCertified(); ?> >
                                    <div class="col-xs-12 col-md-8 modalCell" >
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="isCertifiedTickM">
                                            <label class="custom-control-label" for="isCertifiedTickM">Certified</label>
                                        </div>
                                    </div>
                                </div>	
                                <div class="row">
                                    <div class="col-xs-12 col-md-6 modalCell">
                                        <div class="modalFieldCnt">
                                            <div class="modalFieldLabelCnt">Subnature</div>
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

                            <!-- Semantic Labeling tab -->
                            <div id="editStatusTabDevice" class="tab-pane fade">
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

                            <div id="editHLTTabDevice" class="tab-pane fade">
                                <div class="row">

                                    <div class="col-xs-12 col-md-6 modalCell">
                                        <select id="selectHLTM" name="selectHLTM" class="modalInputTxt" required>
                                            <option value="iot_device_entity">IOT Device/entity</option>
                                            <option value="Heatmap">Heatmap</option>
                                            <option value="Traffic_flow">Traffic flow</option>
                                            <option value="Origin_destination_matrix">Origin Destination Matrix</option>
                                            <option value="Trajectory_path">Trajectory path</option>
                                            <option value="Gardens">Garden</option>
                                            <option value="Building_plant">Building plant</option>
                                            <option value="Building">Building 3D</option>
                                            <option value="Floor">Floor</option>
                                            <option value="Cycling_paths">Cycling Path</option>
                                            <option value="Entity_group">Entity Group</option>
                                            <option value="Typical_time_trend">Typical time trend</option>
                                            <option value="Scenario">Scenario</option>
                                            <option value="Vector_field">Vector Field</option>
                                            <option value="Parking_slot">Parking slot</option>
                                        </select>
                                        <div class="modalFieldLabelCnt">Select HLT</div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12 col-md-12 modalCell">
                                        <textarea id="wktGeometryTextM" name="wktGeometry" rows="4" cols="0" style="max-width: 100%;min-width:100%"></textarea>
                                        <label for="wktGeometryText" style="display:block">wktGeometry</label>
                                        <div id="editHLTattributesMsg" class="modalFieldMsgCnt">&nbsp;</div>
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
                        <div id="editDeviceOkModalInnerDiv1" class="modalDelMsg col-xs-12 centerWithFlex">

                        </div>
                    </div>
                    <div class="row" id="editDeviceKoIcon">
                        <div class="col-xs-12 centerWithFlex"><i class="fa fa-thumbs-o-down" style="font-size:36px"></i></div>
                    </div>

                    <div id="editDeviceModalFooter" class="modal-footer">
                        <button type="button" id="saveAsDeviceBtn" class="btn confirmBtn internalLink pull-left" style="background-color: rgb(255, 165, 0);">Save as</button>
                        <button type="button" id="editDeviceCancelBtn" class="btn cancelBtn" data-dismiss="modal">Cancel</button>
                        <button type="button" id="editDeviceConfirmBtn" class="btn confirmBtn internalLink" >Confirm</button>
                        <button type="button" id="editDeviceOkBtn" class="btn cancelBtn" data-dismiss="modal" style="display:none;" >Ok</button>
                    </div>

                    <!-- </form>--> 	

                </div>
            </div>
        </div>
<!--        Tab for device name change during save as function-->
        <div class="modal fade" id="saveAsDeviceNameChange" tabindex="-1" role="dialog" hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modalHeader centerWithFlex">
                        <label id='saveAsDeviceModalLabel'></label>
                    </div>
                    <div class="modal-body">
                        <div class="row centerWithFlex">
                            <div class="col-xs-12 col-md-6 modalCell">
                                <div class="modalFieldCnt">
                                    <input type="text" class="modalInputTxt" name="saveAsNameDevice" id="saveAsNameDevice" onkeyup="checkStrangeCharacters(this); validateSaveAsName()" required>
                                </div>
                                <script>
                                    function validateSaveAsName(){
                                        var nameInput= document.getElementById("saveAsNameDevice").value;
                                        const name = String(nameInput);
                                        if(name.length > 3){

                                            $('#inputNameDeviceMsg p').replaceWith('<p style="color:cyan;">Ok</p>');
                                            $("#saveAsDeviceConfirmBtn").prop("disabled",false);
                                        }else{

                                            $('#inputNameDeviceMsg p').replaceWith('<p style="color:red;">Device name must be at least 3 characters</p>');
                                            $("#saveAsDeviceConfirmBtn").prop("disabled",true);

                                        }
                                    }
                                </script>

                                <div class="modalFieldLabelCnt pull-left">New Device name</div>
                                <div>&nbsp;</div>

                                <div id="inputNameDeviceMsg" class="modalFieldMsgCnt pull-left"><div></div><p style="color: red">Device name must be at least 3 characters</p></div>

                                <!--                                    <div id="inputNameDeviceMsg" class="modalFieldMsgCnt pull-right"><p style="color: red">no</p></div>-->
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" id="saveAsDeviceCancelBtn" class="btn btn-secondary" data-dismiss="modal" style="background-color:rgb(243,207,88);color:white" onClick="window.location.reload();">Cancel</button>
                            <button type="button" id="saveAsDeviceConfirmBtn" class="btn btn-primary" style="background-color:rgb(0,162,211);border:0px" disabled>Confirm</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

<!--tab to display result from save as function-->
        <div class="modal fade" id="saveAsDeviceResult" tabindex="-1" role="dialog" hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modalHeader centerWithFlex">
                        <label id='saveAsDeviceModalLabel'>Save As Result:</label>
                    </div>
                    <div class="modal-body">
                        <div class="row centerWithFlex">
                            <div class="col-xs-12 col-md-12 modalCell">
                                <div class="modalFieldCnt">
                                    <p hidden id="saveassuccessmessage" class="modalFieldLabelCnt">Device saved</p>
                                    <p hidden id="saveasfailedmessage" class="modalFieldLabelCnt">Error adding device</p>
                                    <p hidden id="saveaserrormessage" class="modalFieldLabelCnt"></p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="modal-footer pull-right">
                                <button type="button" id="saveAsDeviceResult" class="btn btn-primary pull-right" style="background-color:rgb(0,162,211);border:0px" onClick="window.location.reload();">Ok</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!--Success -->

        <div class="modal fade" id="editDeviceOkModal"  role="dialog" >
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modalHeader centerWithFlex">
                        Update device
                    </div>
                    <div id="editDeviceOkModalBody" class="modal-body modalBody">
                        <div class="row">
                            <div class="col-xs-12 modalCell">
                                <div id="editDeviceOkModalInnerDivA">

                                </div>
                                <div class="modalDelObjName col-xs-12 centerWithFlex" id="editDeviceOkModalInnerDivB"><i class="fa fa-check" style="font-size:36px"></i></div> 
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
        <div class="modal fade" id="editDeviceKoModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modalHeader centerWithFlex">
                        Update device
                    </div>
                    <div id="deleteDeviceModalBody" class="modal-body modalBody">
                        <div class="row">
                            <div class="col-xs-12 modalCell">
                                <div id="editDeviceKoModalInnerDivA" class="modalDelMsg col-xs-12 centerWithFlex">

                                </div>
                                <div class="modalDelObjName col-xs-12 centerWithFlex" id="editDeviceKoModalInnerDivB"><i class="fa fa-frown-o" style="font-size:36px"></i></div> 
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="addDeviceKoModalCancelBtn" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                    <!--<div class="modal-footer">
                      <button type="button" id="editDeviceKoBackBtn" class="btn cancelBtn">Go back to edit account form</button>
                      <button type="button" id="editDeviceKoConfirmBtn" class="btn confirmBtn">Go back to users page</button>
                    </div>-->
                </div>
            </div>
        </div> 





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
                        <button type="button" id="cancelMapBtn" class="btn cancelBtn"  data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>



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
                        <button type="button" id="cancelMapBtn" class="btn cancelBtn"  data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>  

        <div id="dialog" title="Please Enter the New Owner">
            <input type="text" name="addNewOwner" id="addNewOwner">
        </div>


        <!-- Modal for Ownership Visibility and Delegations
              <div class="modal fade" id="delegationsModal" tabindex="-1" role="dialog" aria-labelledby="modalAddWidgetTypeLabel" aria-hidden="true">
                      <div class="modal-dialog" role="document">
                        <div class="modal-content">
                              <div class="modalHeader centerWithFlex">
                                Management
                              </div>
                                      <div id="delegationsModalBody" class="modal-body modalBody">
                                              <ul id="delegationsTabsContainer" class="nav nav-tabs nav-justified">
                                                      <li id="ownershipTab" class="active"><a data-toggle="tab" href="#ownershipCnt" class="dashboardWizardTabTxt">Ownership</a></li>
                                                      <li id="visibilityTab"><a data-toggle="tab" href="#visibilityCnt" class="dashboardWizardTabTxt">Visibility</a></li>
                                              </ul> 
                                              
                                              <div id="delegationsModalRightCnt" class="col-xs-12 col-sm-7 col-sm-offset-1">
                                                      <div class="tab-content">
                                                              <div id="ownershipCnt" class="tab-pane fade in active">
                                                                      <div class="row" id="ownershipFormRow">
                                                                              <div class="col-xs-12 centerWithFlex delegationsModalLbl modalFirstLbl" id="changeOwnershipLbl">
                                                                                      Change ownership
                                                                              </div>
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
                                                              
                                                              <div id="visibilityCnt" class="tab-pane fade in">
                                                                      <div class="row" id="visibilityFormRow">
                                                                              <div class="col-xs-12 centerWithFlex delegationsModalLbl modalFirstLbl" id="changeOwnershipLbl">
                                                                                      Change visibility
                                                                              </div>
                                                                              <div class="col-xs-12" id="newVisibilityCnt">
                                                                              
                                                                                      <div class="input-group">
                                                                                      
                                                                                                      <div class="row">
                                                                                                              <input type="text" class="modalInputTxt" name="CurrentVisiblityTxt" id="CurrentVisiblityTxt" readonly> 
                                                                                                      </div>
                                      
                                                                                                <button type="button" id="newVisibilityPublicBtn" class="btn confirmBtn">Make It Public</button>
                                                                                                <button type="button" id="newVisibilityPrivateBtn" class="btn confirmBtn">Make It Private</button>
                                                                                      </div>
                                                                              </div>
                                                                          <div class="col-xs-12 centerWithFlex" id="newVisibilityResultMsg">
                                                                                
                                                                                      
                                                                              </div> 
                                                                      </div>    
                                                              </div>
                                                              
                                                              
                                                      </div>    
                                                      <input type="hidden" id="currDeviceId">
                                              </div>
                                              
                                              
                                              <div id="delegationsModalLeftCnt" class="col-xs-12 col-sm-4">
                                              
                                              </div>

                                              
                                      </div>
                                      <div id="delegationsModalFooter" class="modal-footer">
                                        <button type="button" id="delegationsCancelBtn" class="btn cancelBtn" data-dismiss="modal">Close</button>
                                      </div>
                              
                        </div>
                      </div>
              </div>
              
        -->


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
                                                    <option value="WRITE_ONLY">WRITE_ONLY</option>
                                                </select>
                                            </div>
                                            <span class="col-xs-12 input-group-btn" style="width:100%">
                                                <button type="button" id="newDelegationConfirmBtn" class="btn confirmBtn" style="margin:10px 0;width:100%" disabled>Confirm</button>
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
                                                        <option value="WRITE_ONLY">WRITE_ONLY</option>
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


        <!--check device modal-->
        <div class="modal fade" id="checkDeviceModal" tabindex="-2" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog modal-lg-deviceCheck" role="document">
                <div class="modal-content">

                    <div id="checkDeviceModal" class="modalHeader centerWithFlex">Devices check</div>


                    <div  class="modal-body modalBody">

                        <select id="selectCheckOrganization" name="selectCheckOrganization" class="modalInputTxt" style="font-weight: normal">
                            <option selected="true" disabled >Select an organization to check</option>
                        </select>
                        <div class="modalFieldLabelCnt" id="chooseOrgLabel">Choose an organization</div>


                        <div class="col-xs-12 centerWithFlex" ><i id="LoadingGifCheck" class="fa fa-circle-o-notch fa-spin" style="font-size:36px;" ></i></div></div>

                    <div >
                        <table id="devicesCheckTable" class="table table-bordered table-striped nowrap" cellspacing="0"  width="100%">
                            <thead>
                            <tr style="background: rgb(0, 162, 211); color: rgb(255, 255, 255); font-size: 1em;">
                                <th data-cellTitle="DeviceId">Device Id</th>
<!--                                <th data-cellTitle="Problem">Problem</th>-->
                                <th data-cellTitle="isInDB">DB</th>
                                <th data-cellTitle="isInKB">KB</th>
                                <th data-cellTitle="isInOwn">Ownership</th>
                                <th data-cellTitle="haveUri">have URI</th>
                                <th data-cellTitle="isInBroker">Broker</th>
                                <th data-cellTitle="MarkAsRetry"><div style="max-width: 170px; overflow: auto; white-space: nowrap;  word-wrap: break-word; font-size:10px">Mark as <b style="color: black">Retry</b> (Select all: <input type="checkbox" id="retryAllCheckbox">)</div></th>
                                <th data-cellTitle="MarkAsDelete"><div style="max-width: 170px; overflow: auto; white-space: nowrap;  word-wrap: break-word; font-size:10px">Mark as <b style="color: black">Delete</b> (Select all: <input type="checkbox" id="deleteAllCheckbox">)</div></th>
                                <th data-cellTitle="action">Action taken</th>

                            </tr>
                            </thead>
                            <tbody id="deviceRows">

                            </tbody>
                        </table>
                    </div>

                    <div class="col-xs-12 col-md-3 modalCell" id="ownNameDiv" hidden>
                        <input type="text" id="ownName" name="ownName" class="modalInputTxt">
                        <div class="modalFieldLabelCnt" id="chooseOrgLabel">Input an account name for the ownership:</div>

                    </div>
                    <div class="modal-footer d-flex justify-content-between align-items-center">
                        <button id="cancelCheckBtn" type="button" class="btn cancelBtn" data-dismiss="modal">Close</button>
                        <button id="runCheckBtn" type="button" class="btn btn-primary">Run</button>
                        <button id="previousRunBtn" type="button" class="btn btn-primary pull-left" disabled>No previous run found</button>

                        <button id="applySelected" type="button" class="btn btn-primary mx-auto" disabled>Apply selected</button>
                    </div>
                </div>
            </div>
        </div>
<!--        </div>-->

        <!--check device modal not RootAdmin-->
        <div class="modal fade" id="checkDeviceModalNoRoot" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg-deviceCheck" role="document">
                <div class="modal-content">

                    <div id="checkDeviceModal" class="modalHeader centerWithFlex">Devices check</div>


                    <div  class="modal-body modalBody">


                        <div class="modalFieldLabelCnt">You don't have enough permissions to run this action</div>


                        <div class="modal-footer">
                            <button id="cancelCheckBtn" type="button" class="btn cancelBtn" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!--check device modal-->
        <div class="modal fade" id="checkDeviceModalResult" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog modal-lg-deviceCheck" role="document">
                <div class="modal-content">

                    <div id="checkDeviceModalResult" class="modalHeader centerWithFlex">Devices recovery result</div>
                    <div  class="modal-body modalBody">

                        <div class="col-xs-12 centerWithFlex" ><i id="LoadingGifCheckResult" class="fa fa-circle-o-notch fa-spin" style="font-size:36px;" ></i></div>
                    </div>

                    <div>
                        <table id="devicesCheckTableResult" class="table table-bordered table-striped nowrap" cellspacing="0"  width="100%">
                            <thead>
                            <tr style="background: rgb(0, 162, 211); color: rgb(255, 255, 255); font-size: 1em;">
                                <th data-cellTitle="DeviceId">Device Id</th>
                                <th data-cellTitle="isInDB">DB</th>
                                <th data-cellTitle="isInKB">KB</th>
                                <th data-cellTitle="isInOwn">Ownership</th>
                                <th data-cellTitle="haveUri">URI</th>
                                <th data-cellTitle="isInBroker">Broker</th>
                                <th data-cellTitle="action">Action taken</th>
                            </tr>
                            </thead>
                            <tbody id="deviceRowsResult">

                            </tbody>
                        </table>
                    </div>

                    <div class="modal-footer d-flex justify-content-between align-items-center">
                        <button id="cancelResultBtn" type="button" class="btn cancelBtn" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        </div>









        <!-- Modal for Ownership Visibility and Delegations All the three Tab -- Just in Case
        <div class="modal fade" id="delegationsModal" tabindex="-1" role="dialog" aria-labelledby="modalAddWidgetTypeLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                  <div class="modal-content">
                        <div class="modalHeader centerWithFlex">
                          Management
                        </div>
                        <form id="delegationsForm" class="form-horizontal" name="delegationsForm" role="form" method="post" action="" data-toggle="validator">
                                <div id="delegationsModalBody" class="modal-body modalBody">
                                        <ul id="delegationsTabsContainer" class="nav nav-tabs nav-justified">
                                                <li id="ownershipTab" class="active"><a data-toggle="tab" href="#ownershipCnt" class="dashboardWizardTabTxt">Ownership</a></li>
                                                <li id="visibilityTab"><a data-toggle="tab" href="#visibilityCnt" class="dashboardWizardTabTxt">Visibility</a></li>
                                                <li id="delegationsTab"><a data-toggle="tab" href="#delegationsCnt" class="dashboardWizardTabTxt">Delegations</a></li>
                                        </ul> 
                                        
                                        <div id="delegationsModalLeftCnt" class="col-xs-12 col-sm-4">
                                                <div class="col-xs-12 centerWithFlex delegationsModalTxt modalFirstLbl" id="delegationsDashboardTitle">
                                                </div>
                                                
                                                <div id="delegationsDashPic" class="modalDelObjName col-xs-12 centerWithFlex"></div>
                                        </div>    
                                        
                                        <div id="delegationsModalRightCnt" class="col-xs-12 col-sm-7 col-sm-offset-1">
                                                <div class="tab-content">
                                                        <div id="ownershipCnt" class="tab-pane fade in active">
                                                                <div class="row" id="ownershipFormRow">
                                                                        <div class="col-xs-12 centerWithFlex delegationsModalLbl modalFirstLbl" id="changeOwnershipLbl">
                                                                                Change ownership
                                                                        </div>
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
                                                        
                                                        <div id="visibilityCnt" class="tab-pane fade in">
                                                                <div class="row" id="visibilityFormRow">
                                                                        <div class="col-xs-12 centerWithFlex delegationsModalLbl modalFirstLbl" id="changeOwnershipLbl">
                                                                                Change visibility
                                                                        </div>
                                                                        <div class="col-xs-12" id="newVisibilityCnt">
                                                                                <div class="input-group">
                                                                                        <select id="newVisibility" class="form-control">
                                                                                                <option value="public">Public</option>
                                                                                                <option value="private">Private</option>
                                                                                        </select>
                                                                                        <span class="input-group-btn">
                                                                                          <button type="button" id="newVisibilityConfirmBtn" class="btn confirmBtn">Confirm</button>
                                                                                        </span>
                                                                                </div>
                                                                        </div>
                                                                        <div class="col-xs-12 centerWithFlex" id="newVisibilityResultMsg">
                                                                                
                                                                        </div>  
                                                                </div>    
                                                        </div>
                                                        
                                                        <div id="delegationsCnt" class="tab-pane fade in">
                                                                <div class="row centerWithFlex modalFirstLbl" id="delegationsNotAvailableRow">
                                                                        Delegations are not possibile on a public dashboard
                                                                </div>    
                                                                <div class="row" id="delegationsFormRow">
                                                                        <div class="col-xs-12 centerWithFlex modalFirstLbl" id="newDelegationLbl">
                                                                                Add new delegation
                                                                        </div>
                                                                        <div class="col-xs-12" id="newDelegationCnt">
                                                                                <div class="input-group">
                                                                                        <input type="text" class="form-control" id="newDelegation" placeholder="Delegated username">
                                                                                        <span class="input-group-btn">
                                                                                          <button type="button" id="newDelegationConfirmBtn" class="btn confirmBtn disabled">Confirm</button>
                                                                                        </span>
                                                                                </div>
                                                                                <div class="col-xs-12 centerWithFlex delegationsModalMsg" id="newDelegatedMsg">
                                                                                        Delegated username can't be empty
                                                                                </div>    
                                                                        </div>

                                                                        <div class="col-xs-12 centerWithFlex" id="currentDelegationsLbl">
                                                                                Current delegations
                                                                        </div>
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
                                                </div>    
                                                <input type="hidden" id="currDeviceId">
                                        </div>
                                </div>
                                <div id="delegationsModalFooter" class="modal-footer">
                                  <button type="button" id="delegationsCancelBtn" class="btn cancelBtn" data-dismiss="modal">Close</button>
                                </div>
                        </form>    
                  </div>
                </div>
        </div> -->

    </body>
</html>		




