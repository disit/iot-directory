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
                        <div class="col-xs-10 col-md-12 centerWithFlex" id="headerTitleCnt">Context Broker</div>
                        <div class="col-xs-2 hidden-md hidden-lg centerWithFlex" id="headerMenuCnt"><?php include "mobMainMenu.php" ?></div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12" id="mainContentCnt">
                            <div class="row hidden-xs hidden-sm mainContentRow">
                                <div class="col-xs-12 mainContentRowDesc">Synthesis</div>
                                <div id="dashboardTotNumberCnt" class="col-md-2 mainContentCellCnt">
                                    <div class="col-md-12 centerWithFlex pageSingleDataCnt">
                                        <?php
                                            $query = "SELECT count(*) AS qt FROM iotdirectorydb.contextbroker";
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
                                        context brokers
                                    </div>
                                </div>
                                <div id="dashboardTotActiveCnt" class="col-md-2 mainContentCellCnt">
                                    <div class="col-md-12 centerWithFlex pageSingleDataCnt">
                                        <?php
                                            $query = "SELECT count(*) AS qt FROM iotdirectorydb.contextbroker";
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
                                    <table id="contextBrokerTable" class="table"></table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
		
		<div class="modal fade" id="deleteContextBrokerModal" tabindex="-1" role="dialog" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="deleteUserModalLabel">Context Broker deletion</h5>
                </div>
                <div class="modal-body">

                </div>
                <div class="modal-footer">
                  <button type="button" id="deleteContextBrokerCancelBtn" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                  <button type="button" id="deleteContextBrokerConfirmBtn" class="btn btn-primary">Confirm</button>
                </div>
              </div>
            </div>
        </div>
		
        <!-- Create Context Broker -->
        <div class="modal fade" id="addContextBrokerModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <form id="addContextBrokerForm" class="form-horizontal" name="addContextBrokerForm" role="form" method="post" action="" >  
              <div class="modal-content">
                <div class="modalHeader centerWithFlex">
                  Add new context broker 
                </div>

                <div id="addContextBrokerModalBody" class="modal-body modalBody">
                    <ul id="addContextBrokerModalTabs"class="nav nav-tabs nav-justified">
			<li class="active"><a data-toggle="tab" href="#infoTabCB">Info</a></li>
                        <li><a data-toggle="tab" href="#geoPositionTabCB">Geo-Position</a></li>
                        <li><a data-toggle="tab" href="#securityTabCB">Security</a></li>
                    </ul>
                    
                    <div class="tab-content">
                       
                        <!-- Info tab -->
                        <div id="infoTabCB" class="tab-pane fade in active">
                            <div class="row">
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputNameCB" id="inputNameCB" required> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Name</div>
                                </div>
                                <!-- <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputUriCB" id="inputUriCB" readonly> 
                                    </div>
                                    <div class="modalFieldLabelCnt">URI</div>
                                </div> -->
                              
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputIpCB" id="inputIpCB"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">IP</div>
                                </div>
                              
                                 <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputPortCB" id="inputPortCB">
                                    </div>
                                    <div class="modalFieldLabelCnt">Port</div>
                                </div>
 
				<div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <select id="selectProtocolCB" name="selectProtocolCB" class="modalInputTxt">
                                            <option value="none">None</option>
                                            <?php 
                                                $link = mysqli_connect($host, $username, $password);
                                                mysqli_select_db($link, $dbname);

                                                $q1 = "SELECT name FROM iotdirectorydb.protocols";
                                                $r1 = mysqli_query($link, $q1);

                                                if($r1)
                                                {
                                                    while($row = $r1->fetch_assoc())
                                                    {
                                                        echo '<option value="' . $row['name'] . '">' . $row['name'] . '</option>';
                                                    }
                                                }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="modalFieldLabelCnt">Protocol</div>
                                </div>
                              
                                <!-- <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input id="createdDateCB" name="createdDateCB" type="date" readonly>
                                    </div>
                                    <div class="modalFieldLabelCnt">Created</div>
                                </div> -->
                               
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
                                </div>
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputLongitudeCB" id="inputLongitudeCB"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Longitude</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Device Schema tab -->
                        <div id="securityTabCB" class="tab-pane fade">
                            <div class="row">
								<div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputLoginCB" id="inputLoginCB"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Login</div>
                                </div>
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputPasswordCB" id="inputPasswordCB"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Password</div>
                                </div>
                              
							
               
                            </div>
                        </div>
                    </div>
					
					<div class="row" id="addMetricLoadingMsg">
                        <div class="col-xs-12 centerWithFlex">Adding metric, please wait</div>
                    </div>
                    <div class="row" id="addMetricLoadingIcon">
                        <div class="col-xs-12 centerWithFlex"><i class="fa fa-circle-o-notch fa-spin" style="font-size:36px;"></i></div>
                    </div>
                    <div class="row" id="addMetricOkMsg">
                        <div class="col-xs-12 centerWithFlex">Metric added successfully</div>
                    </div>
                    <div class="row" id="addMetricOkIcon">
                        <div class="col-xs-12 centerWithFlex"><i class="fa fa-thumbs-o-up" style="font-size:36px"></i></div>
                    </div>
                    <div class="row" id="addMetricKoMsg">
                        <div class="col-xs-12 centerWithFlex">Error adding metric</div>
                    </div>
                    <div class="row" id="addMetricKoIcon">
                        <div class="col-xs-12 centerWithFlex"><i class="fa fa-thumbs-o-down" style="font-size:36px"></i></div>
                    </div>
					
					
				</div>
			   <div id="addContextBrokerModalFooter" class="modal-footer">
                  <button type="button" id="addContextBrokerCancelBtn" class="btn cancelBtn" data-dismiss="modal">Cancel</button>
                  <button type="submit" id="addContextBrokerConfirmBtn" name="addContextBrokerConfirmBtn" class="btn confirmBtn internalLink">Confirm</button>
                </div>
            </form>


				
            </div>
        </div>
	</div>
	
	
	<!-- Modale di modifica account utente -->
        <div class="modal fade" id="editContextBrokerModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div id="editUserModalLabel" class="modalHeader centerWithFlex">
                  
                </div>
                <div id="editUserModalLoading" class="modal-body container-fluid">
                    <div class="row">
                        <div class="col-sm-6 col-sm-offset-3 centerWithFlex">
                            Loading user's data, please wait
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
                                Updating user's data, please wait
                        </div> 
                    </div>
                    <div class="row">
                        <div class="col-sm-6 col-sm-offset-3 centerWithFlex">
                                <div class="col-xs-12 centerWithFlex"><i class="fa fa-circle-o-notch fa-spin" style="font-size:36px;"></i></div>
                        </div> 
                    </div>
                </div>  
                <div id="editContextBrokerModalBody" class="modal-body modalBody">
                    <form id="editUserForm" name="editUserForm" role="form" method="post" action="process-form.php" data-toggle="validator">
                        
					<ul id="editContextBrokerModalTabs"class="nav nav-tabs nav-justified">
						<li class="active"><a data-toggle="tab" href="#editInfoTabCB">Info</a></li>
                        <li><a data-toggle="tab" href="#editGeoPositionTabCB">Geo-Position</a></li>
                        <li><a data-toggle="tab" href="#editSecurityTabCB">Security</a></li>
                    </ul>
                    
                    <div class="tab-content">
                       
                        <!-- Info tab -->
                        <div id="editInfoTabCB" class="tab-pane fade in active">
                            <div class="row">
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputNameCBM" id="inputNameCBM" required readonly> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Name</div>
                                </div>
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputUriCBM" id="inputUriCBM" readonly> 
                                    </div>
                                    <div class="modalFieldLabelCnt">URI</div>
                                </div>
                              
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputIpCBM" id="inputIpCBM"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">IP</div>
                                </div>
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputPortCBM" id="inputPortCBM">
                                    </div>
                                    <div class="modalFieldLabelCnt">Port</div>
                                </div>
                               	  <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <select id="selectProtocolCBM" name="selectProtocolCBM" class="modalInputTxt">
                                            <option value="none">None</option>
                                            <?php 
                                                $link = mysqli_connect($host, $username, $password);
                                                mysqli_select_db($link, $dbname);

                                                $q1 = "SELECT name FROM iotdirectorydb.protocols";
                                                $r1 = mysqli_query($link, $q1);

                                                if($r1)
                                                {
                                                    while($row = $r1->fetch_assoc())
                                                    {
                                                        echo '<option value="' . $row['name'] . '">' . $row['name'] . '</option>';
                                                    }
                                                }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="modalFieldLabelCnt">Protocol</div>
                                </div>
                              
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input id="createdDateCBM" name="createdDateCBM" type="date" readonly>
                                    </div>
                                    <div class="modalFieldLabelCnt">Created</div>
                                </div>
                               
                            </div>
                        </div>
                         
                        <!-- Geo-Position tab -->
                        <div id="editGeoPositionTabCB" class="tab-pane fade">
                            <div class="row">
								<div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputLatitudeCBM" id="inputLatitudeCBM"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Latitude</div>
                                </div>
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputLongitudeCBM" id="inputLongitudeCBM"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Longitude</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Device Schema tab -->
                        <div id="editSecurityTabCB" class="tab-pane fade">
                            <div class="row">
								<div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputLoginCBM" id="inputLoginCBM"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Login</div>
                                </div>
                                <div class="col-xs-12 col-md-6 modalCell">
                                    <div class="modalFieldCnt">
                                        <input type="text" class="modalInputTxt" name="inputPasswordCBM" id="inputPasswordCBM"> 
                                    </div>
                                    <div class="modalFieldLabelCnt">Password</div>
                                </div>
                              
							
               
                            </div>
                        </div>
                    </div>
						
			
                       <input type="hidden" id="inputNameCBM" name="inputNameCBM"/>
                    </form>    
                </div>
                <div id="editContextBrokerModalFooter" class="modal-footer">
                  <button type="button" id="editUserCancelBtn" class="btn cancelBtn" data-dismiss="modal">Cancel</button>
                  <button type="button" id="editContextBrokerConfirmBtn" class="btn confirmBtn internalLink" >Confirm</button>
                </div>
              </div>
            </div>
        </div>
	
	
    </body>
</html>

<script type='text/javascript'>
// **MM ***

 var _serviceIP = "../..";
// var _serviceIP = "http://iot-app.snap4city.org/iotdirectory";


function ajaxRequest()
{var request=false;
  try { request = new XMLHttpRequest()}catch(e1){
	try{request = new ActiveXObject("Msxml2.XMLHTTP")}catch(e2){
		try{ request = new ActiveXObject("Microsoft.XMLHTTP")
		}catch(e3){request = false}
	}
  }
  return request
}

function activateStub(button,cb,ip,port,protocol)
{
    
	var data = "contextbroker=" + cb + "&ip=" + ip + "&port=" + port;
        var service = _serviceIP + "/api/"+protocol;
	console.log(data);
	console.log(service);
	var xhr = ajaxRequest();

	xhr.addEventListener("readystatechange", function () {
	  if (this.readyState === 4 && this.status == 200) {
		console.log(this.responseText);
                

		$(button).css('border', '2px solid green');
		//$(button).addClass("btn btn-success btn-circle");
		// $("#" + cb + " > i").removeClass();
		// $("#" + cb + " > i").addClass("fa fa-cogs");
		$(button).prop('onclick',null).off('click');
	  }
	});

	xhr.open("POST", service);
	xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	xhr.send(data);
	return true;
}


function aggiornaStub()
{
    var service = _serviceIP + "/api/status";
	var xhr = ajaxRequest();
    console.log("entratos"); 
	xhr.addEventListener("readystatechange", function () {
	  if (this.readyState === 4 && this.status == 200) {
		console.log(this.responseText);
		var activeStub = JSON.parse(JSON.parse(this.responseText).message);
		console.log(activeStub);
		for (var i=0; i < activeStub.length; i++)
		{
		    var cb=activeStub[i];
			console.log(cb);
			
					$("#" + cb).css('border', '2px solid green');
		            $("#" + cb).prop('onclick',null).off('click');

			
		}
	  }
	});
	xhr.open("GET", service);
	// xhr.setRequestHeader("Cache-Control", "no-cache");
	xhr.send();
	return true;
}



// end **MM 

    $(document).ready(function () 
    {
        var sessionEndTime = "<?php echo $_SESSION['sessionEndTime']; ?>";
        $('#sessionExpiringPopup').css("top", parseInt($('body').height() - $('#sessionExpiringPopup').height()) + "px");
        $('#sessionExpiringPopup').css("left", parseInt($('body').width() - $('#sessionExpiringPopup').width()) + "px");
        
		aggiornaStub();
        
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
            //$('#sessionExpiringPopup').css("top", parseInt($('body').height() - $('#sessionExpiringPopup').height()) + "px");
            //$('#sessionExpiringPopup').css("left", parseInt($('body').width() - $('#sessionExpiringPopup').width()) + "px");
            
            if($(window).width() < 992)
            {
                $('#contextBrokerTable').bootstrapTable('hideColumn', 'name');
                $('#contextBrokerTable').bootstrapTable('hideColumn', 'uri');
				$('#contextBrokerTable').bootstrapTable('hideColumn', 'ip');
                $('#contextBrokerTable').bootstrapTable('hideColumn', 'protocol');
                $('#contextBrokerTable').bootstrapTable('hideColumn', 'created');
            }
            else
            {
                $('#contextBrokerTable').bootstrapTable('showColumn', 'name');
                $('#contextBrokerTable').bootstrapTable('showColumn', 'uri');
				$('#contextBrokerTable').bootstrapTable('showColumn', 'ip');
                $('#contextBrokerTable').bootstrapTable('showColumn', 'protocol');
                $('#contextBrokerTable').bootstrapTable('showColumn', 'created');
            }
        });
        
        $('#contextBrokerLink .mainMenuItemCnt').addClass("mainMenuItemCntActive");
        $('#mobMainMenuPortraitCnt #contextBrokerLink .mobMainMenuItemCnt').addClass("mainMenuItemCntActive");
        $('#mobMainMenuLandCnt #contextBrokerLink .mobMainMenuItemCnt').addClass("mainMenuItemCntActive");
    
        
      
			//var loggedRole = "<?= $_SESSION['loggedRole'] ?>";
            //var loggedType = "<?= $_SESSION['loggedType'] ?>";
            //var usr = "<?= $_SESSION['loggedUsername'] ?>";
            //var userVisibilitySet = null;
            var authorizedPages = [];
            var internalDest = false;
            var tableFirstLoad = true;
			
			buildMainTable(false);
            
            $('.internalLink').on('mousedown', function(){
                internalDest = true;
            });


        /* ADD CONTEXT BROKER (INSERT INTO DB) */ 				
        $('#addContextBrokerConfirmBtn').off("click");
        $("#addContextBrokerConfirmBtn").click(function(){
			
			$('#addContextBrokerModalTabs').hide();
            $('#addContextBrokerModal div.modalCell').hide();
            $('#addContextBrokerModalFooter').hide();
            $('#addMetricLoadingMsg').show();
            $('#addMetricLoadingIcon').show();
	
				$.ajax({
                 url: "process-form.php",
                 data:{
					 addContextBrokerConfirmBtn: true,
					 inputNameCB: $('#inputNameCB').val(),
					 inputIpCB: $('#inputIpCB').val(),
                                         inputPortCB:  $('#inputPortCB').val(),
					 selectProtocolCB: $('#selectProtocolCB').val(),
					 // inputUriCB: $('#inputUriCB').val(),
					 inputLoginCB: $('#inputLoginCB').val(),
					 inputPasswordCB: $('#inputPasswordCB').val(),
					 inputLatitudeCB: $('#inputLatitudeCB').val(),
					 inputLongitudeCB: $('#inputLongitudeCB').val(),
					 // createdDateCB: $('#createdDateCB').val()
					 
				 },
                 type: "POST",
                 async: true,
                 success: function (data) 
                 {
                    if(data != 'Ok')
                    {
                        console.log("Error adding Context Broker");
                        console.log(data);
			$('#addMetricLoadingMsg').hide();
                        $('#addMetricLoadingIcon').hide();
                        $('#addMetricKoMsg').show();
                        $('#addMetricKoIcon').show();
						
                        setTimeout(function(){
							
						$('#addMetricKoMsg').hide();
						$('#addMetricKoIcon').hide();
						$('#addContextBrokerModalTabs').show();
						$('#addContextBrokerModal div.modalCell').show();
						$('#addContextBrokerModalFooter').show();
							
                        }, 3000);
                    }
                    else
                    {
			$('#addMetricLoadingMsg').hide();
                        $('#addMetricLoadingIcon').hide();
                        $('#addMetricOkMsg').show();
                        $('#addMetricOkIcon').show();
						
                        $('#dashboardTotNumberCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotNumberCnt .pageSingleDataCnt').html()) + 1);
                        $('#dashboardTotActiveCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotActiveCnt .pageSingleDataCnt').html()) + 1);
                                  
                        setTimeout(function(){
                            $('#addContextBrokerModal').modal('hide');
                            buildMainTable(true);
							
                            setTimeout(function(){
								
                                $('#addMetricOkMsg').hide();
                                $('#addMetricOkIcon').hide();
								
								$('#inputNameCB').val("");
								$('#inputIpCB').val("");
								$('#inputPortCB').val("");
								$('#selectProtocolCB').val("NULL");
								$('#inputUriCB').val("");
								$('#inputLoginCB').val("");
								$('#inputPasswordCB').val("");
								$('#inputLatitudeCB').val("");
								$('#inputLongitudeCB').val("");
								$('#createdDateCB').val("");
											
								$('#addContextBrokerModalTabs').show();
                                $('#addContextBrokerModal div.modalCell').show();
                                $('#addContextBrokerModalFooter').show()
                            }, 500); 
                        }, 3000);
                    }
                },
                error: function(data)
                {
					
		     console.log("Ko result: " + data);
                     $("#addContextBrokerModal").modal('hide');
                     //$("#addUserKoModalInnerDiv1").html('<h5>User <b>' + newUserJson.username + '</b> couldn\'t be registered because of an API call failure, please try again</h5>');
                    // $("#addMetricKoModal").modal('show');
		    $('#addMetricKoMsg').show();
		    $('#addMetricKoIcon').show();
                     //$("#addUserModalCreating").hide();
                     $("#addContextBrokerModal").show();
                     $("#addContextBrokerModalFooter").show();
					
					/*
					$('#addMetricLoadingMsg').hide();
                    $('#addMetricLoadingIcon').hide();
                    $('#addMetricKoMsg').show();
                    $('#addMetricKoIcon').show();
					
                    setTimeout(function(){
						
						$('#addMetricKoMsg').hide();
                        $('#addMetricKoIcon').hide();
                        $('#addContextBrokerModalTabs').show();
                        $('#addContextBrokerModal div.modalCell').show();
                        $('#addContextBrokerModalFooter').show();
						
                    }, 3000);
                    console.log("Error adding contextBroker type");
                    console.log(errorData);
					*/
					
					
                }
                 
             });
        });


                   /* DELETE CONTEXT BROKER (DELETE FROM DB) */ 				

				
			$('#deleteContextBrokerConfirmBtn').off("click");
			$("#deleteContextBrokerConfirmBtn").click(function(){
             
			var name = $("#deleteContextBrokerModal span").attr("data-name");
    
            $("#deleteContextBrokerModal div.modal-body").html("");
            $("#deleteContextBrokerCancelBtn").hide();
            $("#deleteContextBrokerConfirmBtn").hide();
            $("#deleteContextBrokerModal div.modal-body").append('<div id="deleteContextBrokerModalInnerDiv1" class="modalBodyInnerDiv"><h5>' + name + 'Context Broker deletion in progress, please wait</h5></div>');
            $("#deleteContextBrokerModal div.modal-body").append('<div id="deleteContextBrokerModalInnerDiv2" class="modalBodyInnerDiv"><i class="fa fa-circle-o-notch fa-spin" style="font-size:36px"></i></div>');

             
            $.ajax({
                url: "deleteContextBroker.php",
                data:{name: name},
                type: "POST",
                async: false,
                success: function (data) 
                {
                    console.log("valore di data:" + data);
                    if(data === '0')
                    {
                        $("#deleteContextBrokerModalInnerDiv1").html('User &nbsp; <b>' + name + '</b> &nbsp; deletion failed, please try again');
                        $("#deleteContextBrokerModalInnerDiv2").html('<i class="fa fa-frown-o" style="font-size:42px"></i>');
                    }
                    else if( data === '1')
                    {
                        $("#deleteContextBrokerModalInnerDiv1").html('User &nbsp; <b>' + name + '</b> &nbsp;deleted successfully');
                        $("#deleteContextBrokerModalInnerDiv2").html('<i class="fa fa-check" style="font-size:42px"></i>');
                        setTimeout(function()
                        {
                            buildMainTable(true);
                            $("#deleteContextBrokerModal").modal('hide');
                            setTimeout(function(){
                                $("#deleteContextBrokerCancelBtn").show();
                                $("#deleteContextBrokerConfirmBtn").show();
                            }, 500);
                        }, 2000);
                    }
                    else
                   {
                     console.log("delete context broker error:" + data);
                   }
          
                },
                error: function (data) 
                {
                    $("#deleteContextBrokerModalInnerDiv1").html('Context Broker &nbsp; <b>' + name + '</b> &nbsp; deletion failed, please try again');
                    $("#deleteContextBrokerModalInnerDiv2").html('<i class="fa fa-frown-o" style="font-size:42px"></i>');
                }
            });
        });	
				
		    $('#editContextBrokerConfirmBtn').off("click");
			$("#editContextBrokerConfirmBtn").click(function(){
            $("#editContextBrokerModalBody").hide();
            $("#editContextBrokerModalFooter").hide();
            $("#editContextBrokerModalUpdating").show();

             $.ajax({
                 url: "process-form.php",
                 data:{
					 editContextBroker: true,
					 inputNameCB: $('#inputNameCBM').val(),
					 inputIpCB: $('#inputIpCBM').val(),
					 selectProtocolCB: $('#selectProtocolCBM').val(),
					// port: $('#port').val(),
					 inputUriCB: $('#inputUriCBM').val(),
					 inputLoginCB: $('#inputLoginCBM').val(),
					 inputPasswordCB: $('#inputPasswordCBM').val(),
					 inputLatitudeCB: $('#inputLatitudeCBM').val(),
					 inputLongitudeCB: $('#inputLongitudeCBM').val(),
					 createdDateCB: $('#createdDateCBM').val()
				 
				 },
                 type: "POST",
                 async: true,
                 success: function (data) 
                 {
                     if(data !== 'Ok')
						{
                             $("#editContextBrokerModal").modal('hide');
                             $("#editContextBrokerKoModalInnerDiv1").html('<h5>Context Broker <b>' + name + '</b> couldn\'t be updated because of a database failure while inserting data, please try again</h5>');
                             $("#editContextBrokerKoModal").modal('show');
                             $("#editContextBrokerModalUpdating").hide();
                             $("#editContextBrokerModalBody").show();
                             $("#editContextBrokerModalFooter").show();
						}

					 else 
						{
                             $("#editContextBrokerModal").modal('hide');
                             $("#editUserOkModalInnerDiv1").html('<h5>Context Broker <b>' + name + '</b> successfully updated</h5>');
                             $("#editUserOkModal").modal('show');
                             setTimeout(updateCBTimeout, 2000);
                          
                     }
                 },
                 error: function (data) 
                 {
                     console.log("Ko result: " + data);
                     $("#editContextBrokerModal").modal('hide');
                     $("#editContextBrokerKoModalInnerDiv1").html('<h5>Context Broker <b>' + name + '</b> couldn\'t be updated because of an API call failure, please try again</h5>');
                     $("#editContextBrokerKoModal").modal('show');
                     $("#editContextBrokerModalUpdating").hide();
                     $("#editContextBrokerModalBody").show();
                     $("#editContextBrokerModalFooter").show();
                 }
             });
        });
		
	        $('#authorizedPagesJson').val(JSON.stringify(authorizedPages));
            //$('label[for=authorizedPages]').hide();
            //$('#authorizedPagesTable').parent().hide();
            $('#color_hf').css("background-color", '#ffffff');
           
            $('#addAuthorizedPageBtn').off("click");
            $('#addAuthorizedPageBtn').click(function(){
                 var row = $('<tr><td><a href="#" class="toBeEdited" data-type="text" data-mode="popup"></a></td><td><i class="fa fa-minus"></i></td></tr>');
                 $('#authorizedPagesTable tbody').append(row);
                 
                 var rowIndex = row.index();
                 
                 row.find('a').editable({
                    emptytext: "Empty",
                    display: function(value, response){
                        if(value.length > 35)
                        {
                            $(this).html(value.substring(0, 32) + "...");
                        }
                        else
                        {
                           $(this).html(value); 
                        }
                    }
                });
                
                authorizedPages[rowIndex] = null;
                $('#authorizedPagesJson').val(JSON.stringify(authorizedPages));
                
                row.find('i.fa-minus').off("click");
                row.find('i.fa-minus').click(function(){
                    var rowIndex = $(this).parents('tr').index();
                    $('#authorizedPagesTable tbody tr').eq(rowIndex).remove();
                    authorizedPages.splice(rowIndex, 1);
                    $('#authorizedPagesJson').val(JSON.stringify(authorizedPages));
                });
                
                row.find('a.toBeEdited').off("save");
                row.find('a.toBeEdited').on('save', function(e, params){
                    var rowIndex = $(this).parents('tr').index();
                    authorizedPages[rowIndex] = params.newValue;
                    $('#authorizedPagesJson').val(JSON.stringify(authorizedPages));
                });
            });
            
            setGlobals(loggedRole, usr, loggedType, userVisibilitySet);
            
            $("#logoutBtn").off("click");
            $("#logoutBtn").click(function(event)
            {
               event.preventDefault();
               location.href = "logout.php";
               
            });
            
			 function updateCBTimeout()
			{
				$("#editUserOkModal").modal('hide');
				setTimeout(function(){
				   location.reload();
				}, 500);
			}
        
			
		function buildMainTable(destroyOld)
        {
            if(destroyOld)
            {
                $('#contextBrokerTable').bootstrapTable('destroy');
                tableFirstLoad = true;
            }
            var statusVisible = true;
            if($(window).width() < 992)
            {
                
                statusVisible = false; 
                
            }
           
            $.ajax({
                url: "get_data.php",
                data: {
                    action: "getContextBroker"
                },
                type: "GET",
                async: true,
                dataType: 'json',
                success: function(data) 
                {
					
                    var creatorVisibile = true;
                    var detailView = true;
                    var statusVisibile = true;
                    
                    if($(window).width() < 992)
                    {
                        detailView = false;
                        creatorVisibile = false; 
                        statusVisibile = false;
                    }
                   
                    $('#contextBrokerTable').bootstrapTable({
                        columns: [
                        {
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
                            field: 'port',
                            title: 'Port',
                            sortable: true,
                            valign: "middle",
                            align: "center",
                            halign: "center",
                            visible: creatorVisibile,
                            formatter: function(value, row, index)
                            {
                                if(value !== null)
                                {
                                    if(value.length > 90)
                                    {
                                       return value.substr(0, 90) + " ...";
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
						/*
                        {
                            field: 'uri',
                            title: 'URI',
                            sortable: true,
                            valign: "middle",
                            align: "center",
                            halign: "center",
                            visible: creatorVisibile,
                            formatter: function(value, row, index)
                            {
                                if(value !== null)
                                {
                                    if(value.length > 90)
                                    {
                                       return value.substr(0, 90) + " ...";
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
						*/
						
						{
                            field: 'ip',
                            title: 'IP',
                            sortable: true,
                            valign: "middle",
                            align: "center",
                            halign: "center",
                            visible: creatorVisibile,
                            formatter: function(value, row, index)
                            {
                                if(value !== null)
                                {
                                    if(value.length > 90)
                                    {
                                       return value.substr(0, 90) + " ...";
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
                                    if(value.length > 90)
                                    {
                                       return value.substr(0, 90) + " ...";
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
                        },
						{
                            title: "",
                            align: "center",
                            valign: "middle",
                            align: "center",
                            halign: "center",
                            formatter: function(value, row, index)
                            {
                                return '<button type="button" id ="' + row.name +'" class="viewDashBtn">Stub</button>';
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
                        }
						
                        ],
                        data: data,
                        search: true,
                        pagination: true,
                        pageSize: 10,
                        locale: 'en-US',
                        searchAlign: 'left',
                        uniqueId: "Id",
                        striped: false,
                        classes: "table table-hover table-no-bordered",
                        detailView: detailView,
                        detailFormatter: function(index, row, element) {
                            return 'Latitude: ' + data[index].latitude + ' | Longitude: ' + data[index].longitude + ' | uri: ' + data[index].uri + " | Login: " + data[index].login + " | Password: " + data[index].password;
                        },
						
						rowAttributes: function(row, index){
                            return {
                                "data-name": row.name,
                                "data-ip": row.ip,
                                "data-protocol": row.protocol,
                                "data-port": row.port,
                                "data-uri": row.uri,
                                "data-created": row.created,
                                "data-latitude": row.latitude,
                                "data-longitude": row.longitude,
                                "data-login": row.login,
				"data-password": row.password
                            };
						},
                        searchTimeOut: 250,
                        onPostBody: function()
                        {
                            if(tableFirstLoad)
                            {
                                //Caso di primo caricamento della tabella
                                tableFirstLoad = false;
                                var addDasboardDiv = $('<div class="pull-right"><i id="addContextBrokerBtn" data-toggle="modal" data-target="#modal-add-metric" class="fa fa-plus-square" style="font-size:36px; color: #ffcc00"></i></div>');
                                $('div.fixed-table-toolbar').append(addDasboardDiv);
                                addDasboardDiv.css("margin-top", "10px");
                                addDasboardDiv.find('i.fa-plus-square').off('hover');
                                addDasboardDiv.find('i.fa-plus-square').hover(function(){
                                    $(this).css('color', '#e37777');
                                    $(this).css('cursor', 'pointer');
                                }, 
                                function(){
                                    $(this).css('color', '#ffcc00');
                                    $(this).css('cursor', 'normal');
                                });
                                
								
                                $('#addContextBrokerBtn').off('click');
                                $('#addContextBrokerBtn').click(function(){
									var authorizedPages = [];
                                    $('#addContextBrokerModal').modal('show');
                                });
                                
                                $('#contextBrokerTable thead').css("background", "rgba(0, 162, 211, 1)");
                                $('#contextBrokerTable thead').css("color", "white");
                                $('#contextBrokerTable thead').css("font-size", "1.1em");
								
								
                            }
                            else
                            {
                                //Casi di cambio pagina
                            }

                            //Istruzioni da eseguire comunque
                            $('#contextBrokerTable tbody tr').each(function(i){
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
                            
                            $('#contextBrokerTable').css("border-bottom", "none");
                            $('span.pagination-info').hide();

                            $('#contextBrokerTable button.editDashBtn').off('hover');
                            $('#contextBrokerTable button.editDashBtn').hover(function(){
                                $(this).css('background', '#ffcc00');
                                $(this).parents('tr').find('td').eq(1).css('background', '#ffcc00');
                            }, 
                            function(){
                                $(this).css('background', 'rgb(69, 183, 175)');
                                $(this).parents('tr').find('td').eq(1).css('background', $(this).parents('td').css('background'));
                            });
							
							
				

                                /* Modification of the Context broker */			
				$('#contextBrokerTable button.editDashBtn').off('click');
                                $('#contextBrokerTable button.editDashBtn').click(function(){
                                    $("#editContextBrokerModalUpdating").hide();
									$("#editUserModalLoading").hide();
                                    $("#editContextBrokerModalBody").show();
									$('#editContextBrokerModalTabs').show();
									//$('#editContextBrokerModalBody div.modalCell').show();
                                    $("#editContextBrokerModalFooter").show();
                                    $("#editContextBrokerModal").modal('show');
                                    $("#editUserModalLabel").html("Edit Context Broker - " + $(this).parents('tr').attr("data-name"));
									
									
                                    $("#inputNameCBM").val($(this).parents('tr').attr("data-name"));
                                    $("#inputIpCBM").val($(this).parents('tr').attr("data-ip"));
                                    $("#inputPortCBM").val($(this).parents('tr').attr("data-port"));
                                    $("#selectProtocolCBM").val($(this).parents('tr').attr("data-protocol"));
                                    $("#inputUriCBM").val($(this).parents('tr').attr("data-uri"));
                                    $("#createdDateCBM").val($(this).parents('tr').attr("data-created"));
                                    $("#inputLatitudeCBM").val($(this).parents('tr').attr("data-latitude"));
                                    $("#inputLongitudeCBM").val($(this).parents('tr').attr("data-longitude"));
                                    $("#inputLoginCBM").val($(this).parents('tr').attr("data-login"));
                                    $("#inputPasswordCBM").val($(this).parents('tr').attr("data-password"));
                                    

                                    $.ajax({
                                        url: "get_data.php",
                                        data: {operation: "get_param_contextbroker", 
					       name: $(this).parents('tr').attr("data-name")
					},
                                        type: "GET",
                                        async: true,
                                        dataType: 'json',
                                        success: function (data) 
                                        {
                                          
                                           if(data.result === 'Ok')
                                        {
											
					   $("#inputNameCBM").val($(this).parents('tr').attr("data-name"));
					   $("#inputIpCBM").val($(this).parents('tr').attr("data-ip"));
					   $("#inputPortCBM").val($(this).parents('tr').attr("data-port"));
					   $("#selectProtocolCBM").val($(this).parents('tr').attr("data-protocol"));
					   $("#inputUriCBM").val($(this).parents('tr').attr("data-uri"));
					   $("#createdDateCBM").val($(this).parents('tr').attr("data-created"));
					   $("#inputLatitudeCBM").val($(this).parents('tr').attr("data-latitude"));
					   $("#inputLongitudeCBM").val($(this).parents('tr').attr("data-longitude"));
					   $("#inputLoginCBM").val($(this).parents('tr').attr("data-login"));
					   $("#inputPasswordCBM").val($(this).parents('tr').attr("data-password"));
													
											
                                            $('#inputNameCBM').val(data.contextBrokerData.IdMetric);
                                            $('#inputIpCBM').val(data.contextBrokerData.ip);
                                            $('#selectProtocolCBM').val(data.contextBrokerData.protocol);
                                            $('#inputUriCBM').val(data.contextBrokerData.uri);
                                            $('#createdDateCBM').val(data.contextBrokerData.created);
                                            $('#inputLatitudeCBM').val(data.contextBrokerData.latitude);
                                            $('#inputLongitudeCBM').val(data.contextBrokerData.longitude);
                                            $('#inputLoginCBM').val(data.contextBrokerData.login);
                                            $('#inputPasswordCBM').val(data.contextBrokerData.password);
                                            
                                            
                                         
                                            $('#inputNameCBM').val(inputNameCBM);
                                            $('#editContextBrokerModal').modal('show');
                                        }
                                        else
                                        {
                                            console.log("Error retrieving  data");
                                            console.log(JSON.stringify(errorData));
                                            alert("Error retrieving  data");
                                        }

										  

                                          // showEditUserModalBody();
                                        },
                                        error: function (data)
                                        {
                                           console.log("Get user pool memberships KO");
                                           console.log(data);
                                        }
                                    });
                                });
							
		                     
							$('#contextBrokerTable button.viewDashBtn').off('hover');
                            $('#contextBrokerTable button.viewDashBtn').hover(function(){
                                $(this).css('background', '#ffcc00');
                                $(this).parents('tr').find('td').eq(1).css('background', '#ffcc00');
                            }, 
                            function(){
                                $(this).css('background', 'rgba(0, 162, 211, 1)');
                                $(this).parents('tr').find('td').eq(1).css('background', $(this).parents('td').css('background'));
                            });
							
							$('#contextBrokerTable button.viewDashBtn').off('click');
                            $('#contextBrokerTable button.viewDashBtn').click(function () 
                            {
								
			       //************************************************
								
                                var name = $(this).parents('tr').attr('data-name');
				//var ip = $(this).parents('tr').find('td').eq(3).text();
				var ip = $(this).parents('tr').attr('data-ip');
				// var port = $(this).parents('tr').attr("data-port");
				var port = $(this).parents('tr').attr("data-port");
                var protocol = $(this).parents('tr').attr("data-protocol");
				//You can call the stub function here								
				activateStub(this,name,ip,port,protocol);
				//****************************************************
                            });
							
                            $('#contextBrokerTable button.delDashBtn').off('hover');
                            $('#contextBrokerTable button.delDashBtn').hover(function(){
                                $(this).css('background', '#ffcc00');
                                $(this).parents('tr').find('td').eq(1).css('background', '#ffcc00');
                            }, 
                            function(){
                                $(this).css('background', '#e37777');
                                $(this).parents('tr').find('td').eq(1).css('background', $(this).parents('td').css('background'));
                            });
                            
							
                            $('#contextBrokerTable button.delDashBtn').off('click');
                            $('#contextBrokerTable button.delDashBtn').click(function () 
                            {
                                var name = $(this).parents('tr').find("td").eq(1).html();
								$("#deleteContextBrokerModal div.modal-body").html('<div class="modalBodyInnerDiv"><span data-name = "' + name + '">Do you want to confirm deletion of user <b>' + name + '</b>?</span></div>');
							    $("#deleteContextBrokerModal").modal('show');
                              
                            });
                           
                        }
                    });
                },
                error: function(errorData)
                {
                    console.log("KO");
                    console.log(errorData);
                }
            });

		} //buildMainTable;
    });
</script>  
