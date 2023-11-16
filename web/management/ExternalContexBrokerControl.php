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
            var gpsCentreLatLng = "<?php echo $_SESSION['gpsCentreLatLng']; ?>";
            var zoomLevel = "<?php echo $_SESSION['zoomLevel']; ?>";
            var titolo_default = "<?php echo $default_title; ?>";
            var access_denied = "<?php echo $access_denied; ?>";
            var nascondi = "<?php echo $hide_menu; ?>";
            var sessionEndTime = "<?php echo $_SESSION['sessionEndTime']; ?>";
            var sessionToken = "<?php if (isset($_SESSION['refreshToken']))
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
<?php } //MM201218 FINE  ?>
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
                            <div class="col-xs-2 hidden-md hidden-lg centerWithFlex" id="headerMenuCnt"></div> 
                        </div>
<?php } //MM201218 FINE   ?>

                    <div class="row">
                        <div class="col-xs-12" id="mainContentCntIot">

                            <div>
                                <div id="addMyNewDeviceRow" class="row mainContentRow">
                                    <table id="contextExternalBrokerTable" class="table table-striped" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                <th></th>	
                                                <th data-cellTitle="name">IOT Broker</th>
                                                <th data-cellTitle="StatusCB">Status</th>
                                                <th data-cellTitle="frequencyCB">Frequency of request</th>
                                                <th data-cellTitle="TimestampChangeStatusCB">Last change status</th>
                                                <th data-cellTitle="NumberNewDevice">New devices</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>  
                            </div>  


                            <!-- window to set frequency of broker discovery-->

                            <div class="modal fade" id="FREQactiveBrokersModal" tabindex="-1" role="dialog" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modalHeader centerWithFlex">
                                            Set frequency of activation broker discovery
                                        </div>
                                        <br>
                                        <div>
                                            <div class="col-xs-12 col-md-6 ">

                                                <div class=" col-xs-12 col-md-4">Hours:
                                                    <select id="FselectorHours" style="margin-left: 5px;">
                                                        <?php
                                                        for ($i = 0; $i <= 24; $i++) {
                                                            ?>
                                                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
    <?php
}
?>
                                                    </select>
                                                </div>

                                                <div class=" col-xs-12 col-md-4">Minutes:
                                                    <select id="FselectorMinutes" style="margin-left: 5px;">
                                                        <?php
                                                        for ($i = 1; $i <= 59; $i++) {
                                                            ?>
                                                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
    <?php
}
?>
                                                    </select>
                                                </div>


                                                <div class=" col-xs-12 col-md-4" style=" display: none;">Seconds:
                                                    <select id="FselectorSeconds" style="margin-left: 5px; display: none;" >
                                                        <?php
                                                        for ($i = 0; $i <= 59; $i++) {
                                                            
                                                                ?>
                                                               
                                                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
        <?php
    }

?>
                                                    </select>
                                                </div>

                                            </div></div>
                                        <br>
                                        <div class="modal-footer">
                                            <button type="button" id="FREQsetButton" class="btn btn-primary" >Activate</button>
                                            <button type="button" class="btn cancelBtn" data-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- the window that appears as message after clicking submit -->

                        </div>
                    </div>
                </div>



                </body>
                </html>		




