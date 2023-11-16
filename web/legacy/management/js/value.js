var tableFirstLoad = true;
var dataTable ="";
 
//--------to get the datatypes items---------- 
$.ajax({url: "../api/device.php",
	data: {
		action: 'get_param_values',
		token : sessionToken
	},
	type: "POST",
	async: true,
	dataType: 'json',
	success: function (mydata) {
		if (mydata["status"] === 'ok') {
			gb_datatypes= mydata["data_type"];
			gb_value_units= mydata["value_unit"];
			gb_value_types= mydata["value_type"];
		}
		else {
			console.log("error getting the data types "+data);
		}
	},
	error: function (mydata) {
		console.log(JSON.stringify(mydata));
		alert("Network errors. <br/> Get in touch with the Snap4City Administrator<br/>"+ JSON.stringify(mydata));
	}
});

//--------to get the list of context broker----------
$.ajax({
	url: "../api/contextbroker.php",
	data: {
		action: "get_all_contextbroker",
		token : sessionToken
	},
	type: "POST",
	async: true,
	success: function (data) {
		if (data["status"] === 'ok') {
			addCB($("#selectContextBroker"), data);
		}
		else {
			console.log("error getting the context brokers "+data);
		}
	},
	error: function (data) {
		console.log("error in the call to get the context brokers "+data);
		alert("Network errors. <br/> Get in touch with the Snap4City Administrator<br/>"+ JSON.stringify(data));
	}
});
 
function generateUUID() { // Public Domain/MIT
		var d = new Date().getTime();
		if (typeof performance !== 'undefined' && typeof performance.now === 'function'){
			d += performance.now(); //use high-precision timer if available
		}
		return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
        var r = (d + Math.random() * 16) % 16 | 0;
        d = Math.floor(d / 16);
        return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
		});
}
	
function format ( d ) {
	var showKey="";
	if (d.visibility =='MyOwnPublic' || d.visibility == 'MyOwnPrivate' || d.visibility=='delegated'){
		if(d.k1!="" && d.k2!="")
			showKey =  
			'<div class="row">' +
				'<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>K1:</b>' + "  " + d.k1 + '</div>' +
				'<div class="clearfix visible-xs"></div>' +
				'<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>K2:</b>' + "  " + d.k2  + '</div>' +	
			'</div>' ;	  
	}	

	var multitenancy = "";
	if (d.service && d.servicePath){
		multitenancy = 
			'<div class="row">' + 
				'<div id="service" class="col-xs-6 col-sm-6" style="background-color:#B3D9FF;"><b>Service/Tenant:</b>' + "  " + d.service + '</div>' +
				'<div id="service" class="clearfix visible-xs"></div>' +
				'<div class="col-xs-6 col-sm-6" style="background-color:#B3D9FF;"><b>ServicePath:</b>' + "  " + d.servicePath  + '</div>' +	
			'</div>' ;
	}
	
	return	'<div class="container-fluid">' +
			'<div class="row">' +
				'<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>Broker URI:</b>' + "  " + d.accesslink + '</div>' +
				'<div class="clearfix visible-xs"></div>' +
				'<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>Broker Port:</b>' + "  " + d.accessport + '</div>' +
			'</div>' +
			'<div class="row">' +
				'<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Device Type:</b>' + "  " + d.devicetype + '</div>' +
				'<div class="clearfix visible-xs"></div>' +
				'<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Organization:</b>' + "  " + d.organization + '</div>' +
			'</div>' +
			'<div class="row">' +
			'<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>Data Type:</b>' + "  " + d.data_type + '</div>' +
				'<div class="clearfix visible-xs"></div>' +
				'<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>Editable:</b>' + "  " + d.editable + '</div>' +								
			'</div>' +
			'<div class="row">' +
				'<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Healthiness criteria:</b>' + "  " + d.healthiness_criteria + '</div>' +
				'<div class="clearfix visible-xs"></div>' +
				'<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Healthiness value:</b>' + "  " + d.value_refresh_rate + '</div>' +
			'</div>' + 
			'<div class="row">' +
				'<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>Kind:</b>' + "  " +  d.kind + '</div>' +
				'<div class="clearfix visible-xs"></div>' +	
			'</div>' + 	 
			showKey +
			getInfoCert(d.privatekey, d.visibility, d.created, d.device, d.cb, d.certificate, d.sha) + 
			multitenancy +
			'</div>' ;
}
	
//DataTable fetch_data function 		
	function fetch_data(destroyOld, selected)
	{
		    //console.log("dentro builtMaintable");
            if(destroyOld)
            {
                $('#valuesTable').DataTable().clear().destroy();
                tableFirstLoad = true;

            }
            
         //TODO uniform this switch .. they call same stuff!!! 
            if (selected==null)
			{
                         if (loggedRole!='RootAdmin') {
			       mydata = {action: "get_all_event_value", token : sessionToken, organization : organization, loggedrole:loggedRole,username: loggedUser, no_columns: ["position","visibility","status1","edit","delete","map"]};
			}

                          else {
			       mydata = {action: "get_all_event_value_admin", token : sessionToken, username: loggedUser, organization : organization,loggedrole:loggedRole,  no_columns: ["position","visibility","status1","edit","delete","map"]};
			}
			}
			else
			{
				if (loggedRole!='RootAdmin') 
			       mydata = {action: "get_all_event_value", token : sessionToken, select: selected, no_columns: ["position","visibility","status1","edit","delete","map"]};
                          else
			       mydata = {action: "get_all_event_value_admin", token : sessionToken, select: selected, no_columns: ["position","visibility","status1","edit","delete","map"]};
			}    

	  dataTable = $('#valuesTable').DataTable({
		"processing" : true,
		"serverSide" : true,
		"scrollX": true,
		"paging"   : true,
		"ajax" : {
		 url:"../api/value.php",
		 data: mydata,
		 datatype: 'json',
		 type: "POST",                
		},
  	 "columns": [
          {
			"class":          "details-control",
			"name": "position",
			"orderable":      false,
			"data":           null,
			"defaultContent": "",
			"render": function () {
					 return '<i class="fa fa-plus-square" aria-hidden="true"></i>';
				 },
			width:"15px"
            }, 	
			{"name": "contextbroker", "data": function ( row, type, val, meta ) {
			
				return row.cb;
				} },			
			{"name": "device", "data": function ( row, type, val, meta ) {
				  return row.device;
				} },	
			{"name": "value_name", "data": function ( row, type, val, meta ) {
			
				  return row.value_name;
				} },
			{"name": "value_type", "data": function ( row, type, val, meta ) {
			
				  return row.value_type;
				} },
			{"name": "visibility", "data": function ( row, type, val, meta ) {
			
				  //return row.visibility;
				  				  
				if (row.visibility=='MyOwnPrivate'){   
					return '<button type="button"  class=\"myOwnPrivateBtn\"  onclick="changeofvisibility(\''+ row.device + '\',\''+ row.cb + '\',\''+ row.value_name + '\',\''+ row.visibility + '\',\''+ row.uri + '\',\''+ row.k1 + '\',\''+ row.k2 +'\')">' + row.visibility + '</button>';
					
					} 
				else if (row.visibility=='MyOwnPublic'){
					return '<button type="button"  class=\"myOwnPublicBtn\" onclick="changeofvisibility(\''+ row.device + '\',\''+ row.cb + '\',\''+ row.value_name + '\',\''+ row.visibility + '\',\''+ row.uri + '\',\''+ row.k1 + '\',\''+ row.k2 +'\')">' + row.visibility  + '</button>';
					}
				else if (row.visibility=='public') 
				{
					return '<button type="button"  class=\"publicBtn\" >' + row.visibility + '</button>';
					}
				else // value is private
				{
				  return "<div class=\"delegatedBtn\">"+ row.visibility + "</div>";								  
					}
					
				} },	
				{"name": "d.organization", "data": function ( row, type, val, meta ) {
			
				  return row.organization;
				} },	
				{"name": "status1", "data": function ( row, type, val, meta ) {
			
				  return row.status1;
				} },	
			{
                data: null,
				"name": "edit",
				"orderable":      false,
                className: "center",
				render: function(d) {
                //defaultContent: '<button type="button" id="edit" class="editDashBtn data-id="'+ row.name +'"">Edit</button>'
				if (loggedRole=='RootAdmin' || d.visibility =='MyOwnPublic' || d.visibility == 'MyOwnPrivate') {
				return '<button type="button" class="editDashBtn" ' +
				'data-cb="'+d.cb+'" ' +
				'data-device="'+d.device+'" ' +
				'data-value_name="'+d.value_name+'" ' +
				'data-data_type="'+d.data_type+'" ' +
				'data-value_type="'+d.value_type+'" ' +
				'data-editable="'+d.editable+'" ' +
				'data-value_unit="'+d.value_unit+'" ' +
				'data-healthiness_criteria="'+d.healthiness_criteria+'" ' +
				'data-value_refresh_rate="'+d.value_refresh_rate+'" ' +
				'data-different_values="'+d.different_values+'" ' +
				'data-value_bounds="'+d.value_bounds+'" ' +
				'data-order="'+d.order+'" ' +
				'data-kind="'+d.kind+'" ' +
				'data-longitude="'+d.longitude+'" ' +
				'data-latitude="'+d.latitude+'" ' +
				'data-mandatoryproperties="'+d.mandatoryproperties+'" ' +
				'data-mandatoryvalues="'+d.mandatoryvalues+'" ' +
				'data-k1="'+d.k1+'" ' +
				'data-k2="'+d.k2+'" ' +
				'data-status1="'+d.status1+'" '+
		                'data-service="'+d.service+'" ' + 
		                'data-servicePath="'+d.servicePath+'">Edit</button>';
				} else {
                                return '';}
				
				}
            },
			{
                data: null,
				"name": "delete",
				"orderable":      false,
                className: "center",
                //defaultContent: '<button type="button" id="delete" class="delDashBtn delete">Delete</button>'
				render: function(d) {
				if (loggedRole=='RootAdmin' || d.visibility =='MyOwnPublic' || d.visibility == 'MyOwnPrivate') {
				return '<button type="button" class="delDashBtn" ' +
				'data-cb="'+d.cb+'" ' +
				'data-device="'+d.device+'" ' +
		                'data-service="'+d.service+'" ' + 
                		'data-servicePath="'+d.servicePath+'" '+
				'data-value_name="'+d.value_name+'">Delete</button>';
				 } else {
                                 return ''; }
				}
            },
			{
                data: null,
				"name": "map",
				"orderable":      false,
                className: "center",
                //defaultContent: '<button type="button" id="map" class="delDashBtn delete">Location</button>'
				render: function(d) {
				return '<div class="addMapBtn"><i  data-toggle="modal" data-target="#addMap" onclick="drawMap(\''+ d.latitude + '\',\'' + d.longitude + '\', \'' + d.device + '\', \'' + d.devicetype + '\', \'' + d.kind + '\', \'' + 'addDeviceMapModalBody' + '\')\" class="fa fa-globe" title="Location of Values on Map" style=\"font-size:36px; color: #0000ff\"></i></div>';
				}
            }
        ],  
    "order" : [] 
	  
   });
		
	if (loggedRole!='RootAdmin') {		
	dataTable.columns( [6] ).visible( false );		
	} 
 }	 

 //end of fetch function 
	

       
	   
	$(document).ready(function () 
    {

//fetch_data function will load the device table 	
		 fetch_data(false, null);	 
		
//detail control for device dataTable
	var detailRows = [];
  	
	$('#valuesTable tbody').on('click', 'td.details-control', function () {
    var tr = $(this).closest('tr');
	var tdi = tr.find("i.fa");
    var row = dataTable.row( tr );
 
    if ( row.child.isShown() ) {
		// This row is already open - close it
        row.child.hide();
        tr.removeClass('shown');
		tdi.first().removeClass('fa-minus-square');
        tdi.first().addClass('fa-plus-square');
		}
    else {
		 // Open this row
        row.child( format(row.data()) ).show();

        tr.addClass('shown');
		tdi.first().removeClass('fa-plus-square');
        tdi.first().addClass('fa-minus-square');
		}
	});

//end of detail control for device dataTable 
	//Titolo Default
	if (titolo_default != ""){
		$('#headerTitleCnt').text(titolo_default);
	}
	
	if (access_denied != ""){
		alert('You need to log in with the right credentials before to access to this page!');
	}
	
		///// SHOW FRAME PARAMETER USE/////
		if (nascondi == 'hide'){
			$('#mainMenuCnt').hide();
			$('#title_row').hide();
			$('#mainCnt').removeClass('col-md-10');
			$('#mainCnt').addClass('col-md-12');
		}
		//// SHOW FRAME PARAMETER  ////   
		
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
        
		  // $('#valuesTable').bootstrapTable('showColumn', 'type');
           
		
        $(window).resize(function(){
            $('#mainContentCnt').height($('#mainMenuCnt').height() - $('#headerTitleCnt').height());
            if($(window).width() < 992)
            {
                $('#valuesTable').bootstrapTable('hideColumn', 'cb');
                //$('#valuesTable').bootstrapTable('hideColumn', 'device');
                //$('#valuesTable').bootstrapTable('hideColumn', 'value_name');
                $('#valuesTable').bootstrapTable('hideColumn', 'value_type');
				$('#valuesTable').bootstrapTable('hideColumn', 'status1');
                //$('#valuesTable').bootstrapTable('hideColumn', 'healthiness_criteria');
                //$('#valuesTable').bootstrapTable('hideColumn', 'value_refresh_rate');
               
            }
            else
            {
                $('#valuesTable').bootstrapTable('showColumn', 'cb');
                //$('#valuesTable').bootstrapTable('showColumn', 'device');
                //$('#valuesTable').bootstrapTable('showColumn', 'value_name');
                $('#valuesTable').bootstrapTable('showColumn', 'value_type');
				$('#valuesTable').bootstrapTable('hideColumn', 'status1');
                //$('#valuesTable').bootstrapTable('showColumn', 'healthiness_criteria');
                //$('#valuesTable').bootstrapTable('showColumn', 'value_refresh_rate');
    		
            }
        });
		
		if (functionality.length==0) console.log("ERRORE nella lettura delle funcionality");
		for (var func =0;func < functionality.length; func++)
		{
			
		  var element = functionality[func];
		  if (element.view=="view")
		  {
			  if (element[loggedRole]==1)  
			   {   
				//console.log(loggedRole + " " + element[loggedRole] + " " + element["class"]); 
				   $(element["class"]).show();
			   }			   
			   else 
			   { 
				 $(element["class"]).hide();
				 //console.log($(element.class));
				 //console.log(loggedRole + " " + element[loggedRole] + " " + element["class"]);
			   }
			}
            else
			{
			  if (element[loggedRole]==1 && element.class==".editDashBtn")    editButton =1;
			  if (element[loggedRole]==0 && element.class==".editDashBtn")    editButton =0;
			  
			  if (element[loggedRole]==1 && element.class==".delDashBtn")    deleteButton =1;
			  if (element[loggedRole]==0 && element.class==".delDashBtn")    deleteButton =0;
			  
			  if (element[loggedRole]==1 && element.class==".addMapBtn")    mapButton =1;
			  if (element[loggedRole]==0 && element.class==".addMapBtn")    mapButton =0;
			   
			}			
		}
		
		
		
		$('#valueLink .mainMenuItemCnt').addClass("mainMenuItemCntActive");
        $('#mobMainMenuPortraitCnt #valueLink .mobMainMenuItemCnt').addClass("mainMenuItemCntActive");
        $('#mobMainMenuLandCnt #valueLink .mobMainMenuItemCnt').addClass("mainMenuItemCntActive");
	
     //buildMainTable(false, null);	 
	  
	$('#displayDevicesMapSA').off('click');
	$('#displayDevicesMapSA').click(function(){

	$.ajax({
		url: "../api/value.php",
		data: {
		action: "get_all_value_latlong", 
		token : sessionToken
		},
		type: "POST",
		async: true,
		datatype: 'json',
		success: function (data) 
		 {
			
			 if(data["status"] === 'ko')
				{
					
					 alert("An error occured when reading the data. <br/> Get in touch with the Snap4City Administrator<br/>"+ data["msg"]);
					//$("#addDeviceKoModalInnerDiv1").html(data["msg"]);
					//$("#addDeviceKoModalInnerDiv1").html('<i class="fa fa-frown-o" style="font-size:42px"></i>');
					  //data = data["content"];
				}

			 else (data["status"] === 'ok')
				{
					var data = data["content"];
						
					   $("#addMap1SA").modal('show');
					   drawMapAll(data, 'searchDeviceMapModalBodySA');
						 
					}
		 },
		 error: function (data) 
		 {
			 console.log("Ko result: " + data);
			 alert("Network errors. <br/> Get in touch with the Snap4City Administrator<br/>"+ JSON.stringify(data));
		 }
		
	});		


	});
	
	
		
	$("#addValueBtn").off("click");
	$("#addValueBtn").click(function(){

                     $("#addValueModalTabs").show();
	        	     $('#addValueModal div.modalCell').show();
                     $("#addNewValueCancelBtn").show();
                     $("#addNewValueConfirmBtn").show();
                     $("#addNewValueOkBtn").hide();
                     $('#addValueLoadingMsg').hide();
                     $('#addValueLoadingIcon').hide();
                     $('#addValueKoMsg').hide();
                     $('#addValueKoIcon').hide();
                     $("#addValueOkMsg").hide();
                     $("#addValueOkIcon").hide();
                     $("#addValueKoMsg").hide();
                     $("#addValueModal").modal('show');
                      
			checkProtocol($('#selectContextBroker').children("option:selected").data("protocol"), 'add', 'value');
 
			var $datatype=$("#selectDataType");
			$datatype.empty();
			$.each(gb_datatypes, function() {
                            $datatype.append($("<option />").val(this).text(this));
                        });

			var $valuetype=$("#value_type-1");
			$valuetype.empty();
			$valuetype.append($("<option hidden disabled selected value=\"NOT VALID OPTION\"> -- select an option -- </option>"));
            $.each(gb_value_types, function() {
                            $valuetype.append($("<option />").val(this.value).text(this.label));
                        });
			$("#value_type-1").parent().siblings().last().css("color", "red");
            $("#value_type-1").parent().siblings().last().html("Value type is mandatory");
			$("#value_unit-1").parent().siblings().last().css("color", "#337ab7");
	        $("#value_unit-1").parent().siblings().last().html("Ok");
	});

	//$('#selectValueType, #selectValueTypeM').change(function() {		
	//$("#selectValueType").add("#selectValueTypeM").change(function() {

        /*   ADD NEW VALUE CONFIRMATION */
		
        $('#addNewValueConfirmBtn').off("click");
        $("#addNewValueConfirmBtn").click(function(){
	
            var deviceName = $('#inputNameDevice').val();
            var service = $('#selectService').val();
            var servicePath = $('#inputServicePathValue').val();

	    if ($('#selectContextBroker').children("option:selected").data("protocol") === "ngsi w/MultiService"){
                deviceName = service + "." + servicePath + "." + deviceName;
            }
		
		$("#addValueModalTabs").hide();
		$('#addValueModal div.modalCell').hide();
            //$("#addValueModalFooter").hide();
            $("#addNewValueCancelBtn").hide();
            $("#addNewValueConfirmBtn").hide();
            $("#addNewValueOkBtn").hide();
            $('#addValueLoadingMsg').show();
            $('#addValueLoadingIcon').show();
            

             $.ajax({
                 url: "../api/value.php",
                 data:{
					  action: "insert",
					  contextbroker: $('#selectContextBroker').val(),
		 			  device: deviceName,
					  value_name: $('#inputValueNameDevice').val(),
					  data_type: $('#selectDataType').val(),
					  value_type: $('#value_type-1').val(),
					  editable: $('#inputEditableValue').val(),
					  value_unit: $('#value_unit-1').val(),
					  healthiness_criteria: $('#selectHealthinessCriteria').val(),
					  healthiness_value: $('#inputHealthinessValue').val(),
					  token : sessionToken
					  },
                 type: "POST",
                 async: true,
				
                 success: function (data) 
                 {
				 //console.log("Elf result: " + JSON.stringify(data));
				 
					if(data["status"] === 'ko')
                    {
                        console.log("Error adding value");
                        console.log(data);
			$('#addValueLoadingMsg').hide();
                        $('#addValueLoadingIcon').hide();
                        $('#addValueKoMsg').show();
			$('#addValueKoMsg div:first-child').html(data["error_msg"]);
                        $('#addValueKoIcon').show();
                        $("#addNewValueOkBtn").show();
                    }			 
					else if (data["status"] === 'ok')
                    {
			$('#addValueLoadingMsg').hide();
                        $('#addValueLoadingIcon').hide();
                        $('#addValueKoMsg').hide();
                        $('#addValueKoIcon').hide();
                        $('#addValueOkMsg').show();
                        $('#addValueOkIcon').show();
                        $("#addNewValueOkBtn").show();
                        
                       $('#dashboardTotNumberCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotNumberCnt .pageSingleDataCnt').html()) + 1);
                       if (data["editable"])                       
							$('#dashboardTotPermCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotPermCnt .pageSingleDataCnt').html()) + 1);
					   else
							$('#dashboardTotActiveCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotActiveCnt .pageSingleDataCnt').html()) + 1);
	                 
					
                        fetch_data(true,null);
                        
                        $('#selectContextBroker').val("");
				        $('#inputNameDevice').val("");
				        $('#inputValueNameDevice').val("");
				        $('#selectDataType').val("");
						$('#value_type-1').val("");
						$('#inputEditableValue').val("");
						$('#value_unit-1').val("");
						$('#selectHealthinessCriteria').val("");
						$('#inputHealthinessValue').val("");
								  // $('#inputOrder').val();	
                        
                       /* setTimeout(function(){
                            $('#addValueModal').modal('hide');
                            fetch_data(true,null);

                            setTimeout(function(){
								  $('#addValueOkMsg').hide();
                                  $('#addValueOkIcon').hide();
								  
								  $('#selectContextBroker').val("");
								  $('#inputNameDevice').val("");
								  $('#inputValueNameDevice').val("");
								  $('#selectDataType').val("");
								  $('#value_type-1').val("");
								  $('#inputEditableValue').val("");
								  $('#value_unit-1').val("");
								  $('#selectHealthinessCriteria').val("");
								  $('#inputHealthinessValue').val("");
								  // $('#inputOrder').val();		
							
																
								  $('#addValueModalTabs').show();
                                  $('#addValueModal div.modalCell').show();
                                  $('#addValueModalFooter').show();
                            }, 500);
                        }, 3000);*/
						
				
                    } 
					 
                 },
                 error: function (data) 
                 {
                     console.log("Error status -- Ko result: " +  data);

			$('#addValueLoadingMsg').hide();
                        $('#addValueLoadingIcon').hide();
                        $('#addValueKoMsg').show();
			$('#addValueKoMsg div:first-child').html(data["error_msg"]);
                        $('#addValueKoIcon').show();
                        $("#addNewValueOkBtn").show();
                 }
             });
        });
        
		
		
//Start Related to Delete Device		

		//Delete button hover - needs to be checked
	$('#valuesTable button.delDashBtn').off('hover');
	$('#valuesTable button.delDashBtn').hover(function(){
		$(this).css('background', '#ffcc00');
		$(this).parents('tr').find('td').eq(2).css('background', '#ffcc00');
		$(this).parents('tr').find('td').eq(3).css('background', '#ffcc00');
		
	}, 
	function(){
		$(this).css('background', '#e37777');
		$(this).parents('tr').find('td').eq(2).css('background', $(this).parents('td').css('background'));
		$(this).parents('tr').find('td').eq(3).css('background', $(this).parents('td').css('background'));
	});

		//Delete device button 
	$('#valuesTable tbody').on('click', 'button.delDashBtn', function () {
		var cb = $(this).parents("tr").find("td").eq(1).html();
		var device = $(this).parents("tr").find("td").eq(2).html();
		var value_name = $(this).parents("tr").find("td").eq(3).html();
		var editable = $(this).parents("tr").find("td").eq(6).html();
		 
		var service = $(this).attr('data-service');
        	var servicePath = $(this).attr('data-servicePath');
		$("#deleteValueModal div.modal-body").html('<div class="modalBodyInnerDiv"><span data-value_name = "' + value_name + '" data-cb = "' + cb + '" data-device = "' + device + '" data-editable = "' + editable +'" data-service = "' + service + '" data-servicePath = "' + servicePath + '">Do you want to confirm deletion of value <b>' + value_name + '</b> from Device <b>' + device + '</b>?</span></div>');

	
        
		$("#deleteValueModalInnerDiv1").html('<h5>Device deletion in progress, please wait</h5>');
        $("#deleteValueModalInnerDiv2").html('<i class="fa fa-circle-o-notch fa-spin" style="font-size:36px"></i>');
        $("#deleteValueModalInnerDiv1").hide();
        $("#deleteValueModalInnerDiv2").hide();
        $("#deleteValueOkBtn").hide();
        $("#deleteValueCancelBtn").show();
        $("#deleteValueConfirmBtn").show();
        $("#deleteValueModal").modal('show');
	});
								

	//  DELETE VALUE CONFIRMATION 
	$('#deleteValueConfirmBtn').off("click");
	$("#deleteValueConfirmBtn").click(function(){
	  
		var device = $("#deleteValueModal span").attr("data-device");
		var cb = $("#deleteValueModal span").attr("data-cb");
		var value_name   = $("#deleteValueModal span").attr("data-value_name");	
		var editable = $("#deleteValueModal span").attr("data-editable");
	
		var service = $("#deleteValueModal span").attr("data-service");
	        var servicePath = $("#deleteValueModal span").attr("data-servicePath");
        	if (service !== "null" && servicePath !== "null"){
	            device = service + "." + servicePath + "." + device;
        	}
	
		$("#deleteValueModal div.modal-body").html("");
		$("#deleteValueOkBtn").hide();
		$("#deleteValueCancelBtn").hide();
		$("#deleteValueConfirmBtn").hide();
		$("#deleteValueModalInnerDiv1").show();
		$("#deleteValueModalInnerDiv2").show();
        
		// Delete value
		$.ajax({
			url: "../api/value.php",
			data:{	
					action: "delete",
					device: device, 
					contextbroker: cb, 
					value_name: value_name,
					editable : editable, 
					token : sessionToken						
					},
			type: "POST",
			datatype: "json",
			async: true,
			
			success: function (data) 
			{
				//console.log(JSON.stringify(data));
                 $("#deleteValueOkBtn").show();
				if(data["status"] === 'ko')
				{
					$("#deleteValueModalInnerDiv1").html(data["error_msg"]);
					$("#deleteValueModalInnerDiv2").html('<i class="fa fa-frown-o" style="font-size:42px"></i>');
				}
				else if(data["status"] === 'ok')
				{
					$("#deleteValueModalInnerDiv1").html('Value &nbsp; <b>' + value_name + '</b> &nbsp;deleted successfully');
                    $("#deleteValueModalInnerDiv1").show();
					$("#deleteValueModalInnerDiv2").html('<i class="fa fa-check" style="font-size:42px"></i>');
					
                    
				   $('#dashboardTotNumberCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotNumberCnt .pageSingleDataCnt').html()) - 1);
				   if (data["editable"])                       
						$('#dashboardTotPermCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotPermCnt .pageSingleDataCnt').html()) - 1);
				   else
						$('#dashboardTotActiveCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotActiveCnt .pageSingleDataCnt').html()) - 1);
                    fetch_data(true, null);
                    
					/*setTimeout(function()
					{
						//$('#valuesTable').DataTable().destroy();
						fetch_data(true, null);
						//buildMainTable(true, null);
						$("#deleteValueModal").modal('hide');
						setTimeout(function(){
							$("#deleteValueCancelBtn").show();
							$("#deleteValueConfirmBtn").show();
						}, 500);
					}, 2000);*/
				}
			},
			error: function (data) 
			{
				$("#deleteValueOkBtn").show();
		                console.log(JSON.stringify(data));
				$("#deleteValueModalInnerDiv1").html(data["error_msg"]);
				$("#deleteValueModalInnerDiv2").html('<i class="fa fa-frown-o" style="font-size:42px"></i>');
               
			}
		});
	});
        
         $("#deleteValueOkBtn").off("click");
        $("#deleteValueOkBtn").click(function(){
            
            $("#deleteValueModal div.modal-body").html("Do you want to confirm deletion of the following value?");
            $("#deleteValueModalInnerDiv1").html('<h5>Device deletion in progress, please wait</h5>');
            $("#deleteValueModalInnerDiv2").html('<i class="fa fa-circle-o-notch fa-spin" style="font-size:36px"></i>');
            $("#deleteValueModalInnerDiv1").hide();
            $("#deleteValueModalInnerDiv2").hide();
            $("#deleteValueOkBtn").hide();
            $("#deleteValueCancelBtn").show();
            $("#deleteValueConfirmBtn").show();
            $("#deleteValueModal").modal('show');
            
    
        }); 

// Start Related to Edit Device
	
		//Edit button hover - needs to be checked
	$('#valuesTable tbody button.editDashBtn').off('hover');
	$('#valuesTable tbody button.editDashBtn').hover(function(){
		$(this).css('background', '#ffcc00');
		$(this).parents('tr').find('td').eq(2).css('background', '#ffcc00');
		$(this).parents('tr').find('td').eq(3).css('background', '#ffcc00');
		
	}, 
	function(){
		$(this).css('background', 'rgb(69, 183, 175)');                         
		$(this).parents('tr').find('td').eq(2).css('background', $(this).parents('td').css('background'));
		$(this).parents('tr').find('td').eq(3).css('background', $(this).parents('td').css('background'));
	});

		//Edit button click
	$('#valuesTable tbody').on('click', 'button.editDashBtn', function () {
	
		$("#editValueModalTabs").show();
        $('#editValueModal div.modalCell').show();
        $("#editValueCancelBtn").show();
		$("#editValueConfirmBtn").show();
		$("#editValueOkBtn").hide();
		$("#editValueModalBody").show();
		  $("#editValueLoadingMsg").hide();
		  $("#editValueLoadingIcon").hide();
		  $("#editValueOkMsg").hide();
		  $("#editValueOkIcon").hide();
		  $("#editValueKoMsg").hide();
		  $("#editValueKoIcon").hide(); 
		  $("#editValueModalFooter").show();
		  $("#editValueModal").modal('show');
        
        
    
		  
		  $("#editValueModalLabel").html("Edit value - " + $(this).attr("data-value_name"));

				
	  $('#selectContextBrokerM').val($(this).attr('data-cb'));
	  $('#inputNameDeviceM').val($(this).attr('data-device'));
	  $('#inputValueNameDeviceM').val($(this).attr('data-value_name'));
	  $('#selectDataTypeM').val($(this).attr('data-data_type'));
	  $('#selectValueTypeM').val($(this).attr('data-value_type'));
	  $('#inputEditableValueM').val($(this).attr('data-editable'));
	  $('#selectValueUnitM').val($(this).attr('data-value_unit'));
	  $('#selectHealthinessCriteriaM').val($(this).attr('data-healthiness_criteria'));
	  $('#inputHealthinessValueM').val($(this).attr('data-value_refresh_rate'));
	  $('#inputOrderM').val($(this).attr('data-order'));
	  
	// Author: Antonino Mauro Liuzzo
        var service = $(this).attr('data-service');
        var servicePath = $(this).attr('data-servicePath');
        if(service !== 'null' && servicePath !== 'null') {
            // embed service and servicePath infos into name device input element
            $('#inputNameDeviceM').attr('service', service);
            $('#inputNameDeviceM').attr('servicePath', servicePath);
            $('#editSelectService').val(service);
            $('#editInputServicePathValue').val(servicePath);
        } else {
            // remove service and servicePath infos from name device input element
            $('#inputNameDeviceM').removeAttr('service');
            $('#inputNameDeviceM').removeAttr('servicePath');
            $('#editSelectService').val("");
            $('#editInputServicePathValue').val("");
        }

	});


		// EDIT VALUE CONFIRMATION 
	$('#editValueConfirmBtn').off("click");
	$("#editValueConfirmBtn").click(function(){

		var deviceName = $('#inputNameDeviceM').val();
	        var service = $('#inputNameDeviceM').attr('service');
        	var servicePath = $('#inputNameDeviceM').attr('servicePath');

	        if (service && servicePath){
        	    deviceName = service + "." + servicePath + "." + deviceName;
	        }
		
		$("#editValueModalTabs").hide();
		$('#editValueModal div.modalCell').hide();
		//$("#editValueModalFooter").hide();
		$('#editValueCancelBtn').hide();
		$('#editValueConfirmBtn').hide();
		$('#editValueOkBtn').show();
		$('#editValueLoadingMsg').show();
		$('#editValueLoadingIcon').show();
        //$("#editValueModalBody").hide();

	  

		 // Edit Value
		 $.ajax({
			 url: "../api/value.php",
			 data:{
			  action: "update", 
			  contextbroker: $('#selectContextBrokerM').val(),
			  device: deviceName,
			  value_name: $('#inputValueNameDeviceM').val(),
			  data_type: $('#selectDataTypeM').val(),
			  value_type: $('#selectValueTypeM').val(),
			  editable: $('#inputEditableValueM').val(),
			  value_unit: $('#selectValueUnitM').val(),
			  healthiness_criteria: $('#selectHealthinessCriteriaM').val(),
			  healthiness_value: $('#inputHealthinessValueM').val(),
			 },
			 type: "POST",
			 async: true,
			 success: function (mydata) 
			 {
				if(mydata["status"] === 'ko')
				{
						 /*$("#editValueModal").modal('hide');
						 $("#editValueKoModalInnerDiv1").html(mydata["msg"]);
						 $("#editValueKoModal").modal('show');
						 $("#editValueModalUpdating").hide();
						 $("#editValueModalBody").show();
						 $("#editValueModalFooter").show();*/
                    
                        $("#editValueModalBody").show();    
                        $('#editValueLoadingMsg').hide();
                        $('#editValueLoadingIcon').hide();
                        $('#editValueOkMsg').hide();
                        $('#editValueOkIcon').hide();
                        $('#editValueKoMsg').show();
                        $('#editValueKoIcon').show();
                        $('#editValueOkBtn').show();
				}
				 
				else if (mydata["status"] === 'ok')
				{
						
						 /*$("#editValueModal").modal('hide');
						 $("#editValueOkModalInnerDiv1").html('<h5>Value successfully updated</h5>');
						 $("#editValueOkModal").modal('show');
						// setTimeout(updateCBTimeout, 500);
						setTimeout (function(){
							$('#valueTable').bootstrapTable("load");
								location.reload();
								}, 500);*/
                    
                    $('#editValueLoadingMsg').hide();
                    $('#editValueLoadingIcon').hide();
                    $('#editValueOkMsg').show();
                    $('#editValueOkIcon').show();
                    $('#editValueKoMsg').hide();
                    $('#editValueKoIcon').hide();
                    $('#editValueOkBtn').show();
                    
                    $('#inputNameDeviceM').val("");
                    $('#inputValueNameDeviceM').val("");
                    $('#inputEditableValueM').val("");
                    $('#inputHealthinessValueM').val("");
				
                    fetch_data(true);
					
					
				} 	
				 
			 },
			 error: function (mydata) 
			 {
				 console.log("Ko result: " + JSON.stringify(mydata));
				 /*$("#editValueModal").modal('hide');
				 $("#editValueKoModalInnerDiv1").html(mydata["msg"]);
				 $("#editValueKoModal").modal('show');
				 $("#editValueModalUpdating").hide();
				 $("#editValueModalBody").show();
				 $("#editValueModalFooter").show();*/
                 
                $("#editValueModalBody").show();
                 $('#editValueLoadingMsg').hide();
				$('#editValueLoadingIcon').hide();
				$('#editValueOkMsg').hide();
				$('#editValueOkIcon').hide();
				$('#editValueKoMsg').show();
				$('#editValueKoIcon').show();
				$('#editValueOkBtn').show();
				
  
			 }
		 });
	});
		
	

	$('#valuesTable tbody').on('click', 'button.publicBtn', function () {
		var id = $(this).parents("tr").find("td").eq(1).html();
		document.getElementById('currDeviceId').value= id;
		//$("#delegationsModal div.modal-body modalBody").html('<div class="modalBodyInnerDiv"><span data-id = "' +  id + '"</span></div>');
		$("#delegationsModal").modal('show');
	});
        
        $("#addNewValueCancelBtn").off("click");
        $("#addNewValueCancelBtn").on('click', function(){
								  
			  $('#selectContextBroker').val("");
			  $('#inputNameDevice').val("");
			  $('#inputValueNameDevice').val("");
			  $('#selectDataType').val("");
			  $('#value_type-1').val("");
			  $('#inputEditableValue').val("");
			  $('#value_unit-1').val("");
			  $('#selectHealthinessCriteria').val("");
			  $('#inputHealthinessValue').val("");
			  // $('#inputOrder').val();		
								  
			  $('#addValueModal').modal('hide'); 						
			  
				location.reload();    								  
								
        });
        
        $("#addValueKoBackBtn").off("click");
        $("#addValueKoBackBtn").on('click', function(){
            $("#addValueKoModal").modal('hide');
            $("#addValueModal").modal('show');
        });
        
        $("#addValueKoConfirmBtn").off("click");
        $("#addValueKoConfirmBtn").on('click', function(){
            $("#addValueKoModal").modal('hide');
            $("#addValueForm").trigger("reset");   
        });
        
        $("#editValueKoBackBtn").off("click");
        $("#editValueKoBackBtn").on('click', function(){
            $("#editValueKoModal").modal('hide');
            $("#editValueModal").modal('show');
        });
        
        $("#editValueKoConfirmBtn").off("click");
        $("#editValueKoConfirmBtn").on('click', function(){
            $("#editValueKoModal").modal('hide');
            $("#editValueForm").trigger("reset");
        });
        
		
		//======================================================================================
	function updateGroupList(ouname){
          $.ajax({
                url: "../api/ldap.php",
                data:{
                                          action: "get_group_for_ou",
                                          ou: ouname,
                                          token : sessionToken
                                          },
                type: "POST",
                async: true,
                success: function (data)
                {
                        if(data["status"] === 'ko')
                        {
                                $('#newDelegatedMsgGroup').css('color', '#f3cf58');
                                $('#newDelegatedMsgGroup').html(data["msg"]);
                        }
                        else if (data["status"] === 'ok')
                        {
                                var $dropdown = $("#newDelegationGroup");
				//remove old ones
                                $dropdown.empty();
				//adding empty to rootadmin
				if ((loggedRole=='RootAdmin')||(loggedRole=='ToolAdmin')){
					//console.log("adding empty");
					$dropdown.append($("<option />").val("All groups").text("All groups"));
				}
				//add new ones
                                $.each(data['content'], function() {
                                    $dropdown.append($("<option />").val(this).text(this));
                                });
				
                        }
                },
                error: function (data)
                {
				$('#newDelegatedMsgGroup').css('color', '#f3cf58');
                                $('#newDelegatedMsgGroup').html('Error calling internal API');
                }
          });
        }
	
	//populate organization list with any possibile value (if rootAdmin)
	if ((loggedRole=='RootAdmin')||(loggedRole=='ToolAdmin')) {
		$.ajax({
        	url: "../api/ldap.php",
			data:{
				action: "get_all_ou",
                token : sessionToken
                },
                type: "POST",
                async: false,
                success: function (data)
		{
                        if(data["status"] === 'ko')
                    	{
				 $('#newDelegatedMsgGroup').css('color', '#f3cf58');
                                $('#newDelegatedMsgGroup').html(data["msg"]);
			}
	                else if (data["status"] === 'ok')
        	        {
				var $dropdown = $("#newDelegationOrganization");
				$.each(data['content'], function() {
				    $dropdown.append($("<option />").val(this).text(this));
				});
			}
		}, 
		error: function (data)
                {
				$('#newDelegatedMsgGroup').css('color', '#f3cf58');
                                $('#newDelegatedMsgGroup').html('Error calling internal API');
                }
		});	
	}
	//populate organization list with myorganization (otherwise)
	else {
		$.ajax({
                url: "../api/ldap.php",
                data:{
                                          action: "get_logged_ou",
                                          token : sessionToken
                                          },
                type: "POST",
                async: false,
                success: function (data)
                {
                        if(data["status"] === 'ko')
                        {
                                console.log("Error: "+data);
				alert("An error occured when reading the data. <br/> Get in touch with the Snap4City Administrator. <br/>"+ data["error_msg"]);
                        }
                        else if (data["status"] === 'ok')
                        {
                                var $dropdown = $("#newDelegationOrganization");
                                $dropdown.append($("<option/>").val(data['content']).text(data['content']));
                        }
                },
                error: function (data)
                {
                     	console.log("Error: " +  data);
			//TODO: manage error
                }
                });
	}

	//populate group list with selected organization
	updateGroupList($("#newDelegationOrganization").val());

	//eventually update the group list
	$('#newDelegationOrganization').change( function() {
   		$(this).find(":selected").each(function () {
			updateGroupList($(this).val());
    		});
 	});


		

		//orginal
		    $('#newDelegation').val('');

			$('#newDelegation').off('input');

			$('#newDelegation').on('input',function(e)
			{
				if($(this).val().trim() === '')
				{
					$('#newDelegatedMsg').css('color', '#f3cf58');
					$('#newDelegatedMsg').html('Delegated username can\'t be empty');
					$('#newDelegationConfirmBtn').addClass('disabled');
				}
				else
				{
					$('#newDelegatedMsg').css('color', 'white');
					$('#newDelegatedMsg').html('User can be delegated');
					$('#newDelegationConfirmBtn').removeClass('disabled');
					
					$('#delegationsTable tbody tr').each(function(i)
					{
					   if($(this).attr('data-delegated').trim() === $('#newDelegation').val())
					   {
						   $('#newDelegatedMsg').css('color', '#f3cf58');
						   $('#newDelegatedMsg').html('User already delegated');
						   $('#newDelegationConfirmBtn').addClass('disabled');
					   }
					});
				}
			});
		
	$('#valuesTable thead').css("background", "rgba(0, 162, 211, 1)");
	$('#valuesTable thead').css("color", "white");
	$('#valuesTable thead').css("font-size", "1em");

	$('#valuesTable tbody tr').each(function(i){
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
		
	$('#delegationsModal').on('hidden.bs.modal', function(e)
    { 
        $(this).removeData();
    });
    

	
	});     // end of ready state   
	
	

//   ========================================= START TO CHANGE THE VISIBILITY  & DELEGATION OF VALUE ============================================	

		function changeofvisibility(id, contextbroker, valueName, visibility, uri, k1, k2) {
                        //$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                                        //var target = $(e.target).attr("href");

                                        //if ((target == '#visibilityCnt')) {
			
			$("#delegationsModal").modal('show');
			$("#delegationHeadModalLabel").html('Device: ' + id + ' Value Name - ' + valueName);


				if(visibility=='MyOwnPrivate'){
								newVisibility = 'public';
								$('#visID').css('color', '#f3cf58');
								$("#visID").html("Visibility - Private");
								$("#newVisibilityPrivateBtn").hide();
								$("#newVisibilityPublicBtn").show();
								
								document.getElementById("delegationsCntGroup").style.visibility = 'show';	

				} else // if (visibility=='MyOwnPublic'){
				{
								newVisibility = 'private';
								$('#visID').css('color', '#f3cf58');
								$("#visID").html("Visibility - Public");
								$("#newVisibilityPrivateBtn").show();
								$("#newVisibilityPublicBtn").hide();
				}
                                        //var x = document.getElementsByName('newDelegation')[0].value;

                                                //to change the visibility of a device (from private to public and vice-versa)

                                                // To Change from Private to Public
                                                //$('#newVisibilityPublicBtn').off("click");
                                                //$('#newVisibilityPublicBtn').click(function(){
                                                $(document).on("click", "#newVisibilityPublicBtn", function(event){
                                                        //alert(id + contextbroker + valueName + newVisibility + k1 + k2);
                                                        newk1 = generateUUID();
                                                        newk2 = generateUUID();
                                                        $.ajax({
                                                                 url: "../api/value.php",
                                                                 data:{
                                                                 action: "delegate_value",  // check the action -- there is no action for change_visiblity like device
                                                                 contextbroker: contextbroker,
                                                                 value_name: valueName,
                                                                 visibility :newVisibility,//TODO i cannot find the api relationship with making sensor public
                                                                 uri : uri,
                                                                 token : sessionToken,
                                                                 delegated_user: "ANONYMOUS",
                                                                 k1: newk1,
                                                                 k2: newk2
                                                                 },
                                                                 type: "POST",
                                                                 async: true,
                                                                 dataType: 'json',
                                                                 success: function(data)
                                                                {
                                                                        if (data["status"] === 'ok')
																		{
                                                                                $('#newVisibilityResultMsg').show();
                                                                                //$('#visID').css('color', '#f3cf58');
                                                                                //$("#visID").html("Visibility - Private");
                                                                                $('#newVisibilityResultMsg').html('New visibility set to "ANONYMOUS" correctly');
                                                                                //document.getElementById("newVisibilityPublicBtn").disabled = true;
                                                                                //$('#newVisibilityPublicBtn').addClass('disabled');
                                                                                $('#newVisibilityPublicBtn').addClass('disabled')
                                                                                 setTimeout(function()
                                                                                 {
                                                                                         // buildMainTable(true);
                                                                                         location.reload();
                                                                                 }, 3000);
                                                                        }
                                                                        else if (data["status"] === 'ko')
                                                                        {
                                                                                $('#newVisibilityResultMsg').show();
                                                                                $('#newVisibilityResultMsg').html('Error setting new visibility');
                                                                                $('#newVisibilityPublicBtn').addClass('disabled');

                                                                                setTimeout(function()
                                                                                {
                                                                                        $('#newVisibilityPublicBtn').removeClass('disabled');
                                                                                        $('#newVisibilityResultMsg').html('');
                                                                                        $('#newVisibilityResultMsg').hide();
                                                                                }, 3000);
                                                                        }
                                                                        else {console.log(data);}
                                                                },
                                                                error: function(errorData)
                                                                {
                                                                        $('#newVisibilityResultMsg').show();
                                                                        $('#newVisibilityResultMsg').html('Error setting new visibility');
                                                                        $('#newVisibilityPublicBtn').addClass('disabled');

                                                                        setTimeout(function()
                                                                        {
                                                                                $('#newVisibilityPublicBtn').removeClass('disabled');
                                                                                $('#newVisibilityResultMsg').html('');
                                                                                $('#newVisibilityResultMsg').hide();
                                                                        }, 3000);
                                                                }
                                                        });
                                                });

                                                // To Change from  Public to Private
                                                //$('#newVisibilityPrivateBtn').off("click");
                                                //$('#newVisibilityPrivateBtn').click(function(){
                                                $(document).on("click", "#newVisibilityPrivateBtn", function(event){
                                                        //alert(id + contextbroker, value_name, visibility, newVisibility, k1, k2, uri);
                                                        $.ajax({
                                                                 url: "../api/value.php",
                                                                 data:{
                                                                 action: "remove_delegate_value",  // check the action -- there is no action for change_visiblity like device
                                                                 id: id,
                                                                 contextbroker: contextbroker,
                                                                 value_name: valueName,
                                                                 visibility : visibility,//TODO find relationship with API
                                                                 uri: uri,
                                                                 delegated_user: "ANONYMOUS",
																  //delegated_user: $('#newVisibility').val(), //check the attribute delegated_user
                                                                 token : sessionToken,
                                                                 k1: k1,
                                                                 k2: k2
                                                                 },
                                                                 type: "POST",
                                                                 async: true,
                                                                 dataType: 'json',
                                                                 success: function(data)
                                                                {
                                                                        if (data["status"] === 'ok')
                                                                        {
                                                                                $('#newVisibilityResultMsg').show();
                                                                                $('#newVisibilityResultMsg').html('New visibility set "Private" correctly');
                                                                                //$('#newVisibilityPrivateBtn').addClass('disabled');
                                                                                //document.getElementById("newVisibilityPrivateBtn").disabled = true;
                                                                                $('#newVisibilityPrivateBtn').addClass('disabled');
                                                                                setTimeout(function()
                                                                                {
                                                                                        // buildMainTable(true);
                                                                                        location.reload();
                                                                                 }, 3000);
                                                                        }
                                                                        else if (data["status"] === 'ko')
                                                                        {
                                                                                $('#newVisibilityResultMsg').show();
                                                                                $('#newVisibilityResultMsg').html('Error setting new visibility');
                                                                                $('#newVisibilityPrivateBtn').addClass('disabled');

                                                                                setTimeout(function()
                                                                                {
                                                                                        $('#newVisibilityPrivateBtn').removeClass('disabled');
                                                                                        $('#newVisibilityResultMsg').html('');
                                                                                        $('#newVisibilityResultMsg').hide();
                                                                                }, 3000);
                                                                        }
                                                                        else {console.log(data);}
                                                                },
                                                                error: function(errorData)
                                                                {
                                                                        $('#newVisibilityResultMsg').show();
                                                                        $('#newVisibilityResultMsg').html('Error setting new visibility');
                                                                        $('#newVisibilityPrivateBtn').addClass('disabled');

                                                                        setTimeout(function()
                                                                        {
                                                                                $('#newVisibilityPrivateBtn').removeClass('disabled');
                                                                                $('#newVisibilityResultMsg').html('');
                                                                                $('#newVisibilityResultMsg').hide();
                                                                        }, 3000);
                                                                }
                                                        });
                                                });

                                //      } // end of visibilityCnt Tab



                                //      else if((target == '#delegationsCnt')) {

                                                    $.ajax({
                                    url: "../api/value.php",   //Checking the delegation table
									data:
                                    {
										action: "delegate_value_list",  // check the action and to be specified
										value_name: valueName,
										uri: uri,
										token : sessionToken
                                    },
                                    type: "POST",
                                    async: true,
                                    dataType: 'json',
                                    success: function(data)
                                    {
											if (data["status"]=='ok')
											{

											delegations = data["delegation"];
											$('#delegationsTable tbody').html("");
											$('#delegationsTableGroup tbody').html("");
										for(var i = 0; i < delegations.length; i++)
										{
										if ((delegations[i].userDelegated!=null)&&(delegations[i].userDelegated !=="ANONYMOUS")) {
	                                                                               $('#delegationsTable tbody').append('<tr class="delegationTableRow" data-delegationId="' + delegations[i].delegationId + '" data-delegated="' + delegations[i].userDelegated + '"><td class="delegatedName">' + delegations[i].userDelegated + '</td><td><i class="fa fa-remove removeDelegationBtn"></i></td></tr>');
                                                                       		}
										else if (delegations[i].groupDelegated!=null){
                                                                               		//console.log("adding user delegation"+delegations[i]);

                                                                               //extract cn and ou
									       var startindex=delegations[i].groupDelegated.indexOf("cn=");
                                                                               var endindex_gr= delegations[i].groupDelegated.indexOf(",");
                                                                               var gr=delegations[i].groupDelegated.substring(3, endindex_gr);
                                                                               var endindex_ou=delegations[i].groupDelegated.indexOf(",", endindex_gr+1);
                                                                               var ou=delegations[i].groupDelegated.substring(endindex_gr+4, endindex_ou);
										
										var DN="";
										if (startindex!=-1){
											DN=ou+","+gr;
										}
										else{
											DN=gr;
										}


                                                                                $('#delegationsTableGroup tbody').append('<tr class="delegationTableRowGroup" data-delegationId="' + delegations[i].delegationId + '" data-delegated="' + ou + "," +gr+ '"><td class="delegatedName">' + DN + '</td><td><i class="fa fa-remove removeDelegationBtnGroup"></i></td></tr>');
                                                                       }

										}
						$('#delegationsTable tbody').on("click","i.removeDelegationBtn",function(){
                                                    var rowToRemove = $(this).parents('tr');
                                                    $.ajax({
                                                        url: "../api/value.php",     //check the url
                                                        data:
                                                        {
                                                                action: "remove_delegation",    // to be specified
                                                                value_name: valueName,
                                                                uri: uri,
                                                                token : sessionToken,
                                                                delegationId: $(this).parents('tr').attr('data-delegationId')
                                                        },
                                                        type: "POST",
                                                        async: true,
                                                        dataType: 'json',
                                                        success: function(data)
                                                        {
                                                           if (data["status"] === 'ok')
														   {
                                                                rowToRemove.remove();
                                                                //console.log("ermoving a row from the table");
                                                            }
                                                            else
                                                            {
                                                                //TBD insert a message of error
                                                            }
                                                        },
                                                        error: function(errorData)
                                                        {
                                                           //TBD  insert a message of error
                                                        }
                                                    });
                                                });

                                                                                               $('#delegationsTableGroup tbody').on("click","i.removeDelegationBtnGroup",function(){
                                                                                                       //console.log("toremove:");
                                                                                                       var rowToRemove = $(this).parents('tr');
                                                                                                       $.ajax({
                                                                                                               url: "../api/value.php",     //check the url
                                                                                                               data:
                                                                                                               {
                                                                                                                       action: "remove_delegation",    // to be specified
                                                                                                                       value_name: valueName,
                                                                                                                       uri: uri,
                                                                                                                       token : sessionToken,
                                                                                                                       delegationId: $(this).parents('tr').attr('data-delegationId')
                                                                                                               },
                                                                                                               type: "POST",
                                                                                                               async: true,
                                                                                                               dataType: 'json',
                                                                                                               success: function(data)
                                                                                                               {
                                                                                                                       if (data["status"] === 'ok')
                                                                                                                       {
                                                                                                                               rowToRemove.remove();
                                                                                                                       }
                                                                                                                       else
                                                                                                                       {
                                                                                                                               //TBD insert a message of error
                                                                                                                       }
                                                                                                               },
                                                                                                               error: function(errorData)
                                                                                                               {
                                                                                                                       //TBD  insert a message of error
                                                                                                               }
                                                                                                       });
                                                                                               });




                                            }
                                            else
                                            {
                                              // hangling situation of error
                                                console.log(json_encode(data));

                                            }

                                            },
                                            error: function(errorData)
                                            {
                                               //TBD  insert a message of error
                                            }
                                        });

                                                //$('#newDelegationConfirmBtn').off("click");
                                                //$('#newDelegationConfirmBtn').click(function(){
//single delegation -start------------------------------------------------------------------------------------------------------------------
                                        $(document).on("click", "#newDelegationConfirmBtn", function(event){
                                                //var id = document.getElementById("currDeviceId").value;
                                                var newDelegation = document.getElementById('newDelegation').value;
                                                //alert("The owner is " + id + " The new owner is " + newDelegation);
                                                newk1 = generateUUID();
                                            newk2 = generateUUID();
                                                $.ajax({
                                                        url: "../api/value.php",       //which api to use
                                                        data:
                                                        {
                                                          action: "delegate_value",
                                                          contextbroker: contextbroker,
                                                          value_name: valueName,
                                                          uri : uri,
                                                          token : sessionToken,
                                                          delegated_user: newDelegation,
                                                          k1: newk1,
                                                          k2: newk2
                                                        },
                                                        type: "POST",
                                                        async: true,
                                                        dataType: 'json',
                                                        success: function(data)
                                                        {
                                                                if (data["status"] === 'ok')
								{
                                                                        $('#delegationsTable tbody').append('<tr class="delegationTableRow" data-delegationId="' + data["delegationId"] + '" data-delegated="' + $('#newDelegation').val() + '"><td class="delegatedName">' + $('#newDelegation').val() + '</td><td><i class="fa fa-remove removeDelegationBtn"></i></td></tr>');


                                                                        $('#newDelegation').val('');
                                                                        $('#newDelegation').addClass('disabled');
                                                                        $('#newDelegatedMsg').css('color', 'white');
                                                                        $('#newDelegatedMsg').html('New delegation added correctly');
                                                                        $('#newDelegationConfirmBtn').addClass('disabled');

                                                                        setTimeout(function()
                                                                        {
                                                                                $('#newDelegation').removeClass('disabled');
                                                                                $('#newDelegatedMsg').css('color', '#f3cf58');
                                                                                $('#newDelegatedMsg').html('Delegated username can\'t be empty');
                                                                        }, 1500);
                                                                }
                                                                else
                                                                {
                                                                        var errorMsg = null;


                                                                        $('#newDelegation').val('');
                                                                        $('#newDelegation').addClass('disabled');
                                                                        $('#newDelegatedMsg').css('color', '#f3cf58');
                                                                        $('#newDelegatedMsg').html(data["msg"]);
                                                                        $('#newDelegationConfirmBtn').addClass('disabled');

                                                                        setTimeout(function()
                                                                        {
                                                                                $('#newDelegation').removeClass('disabled');
                                                                                $('#newDelegatedMsg').css('color', '#f3cf58');
                                                                                $('#newDelegatedMsg').html('Delegated username can\'t be empty');
                                                                        }, 2000);
                                                                }
                                                        },
                                                        error: function(errorData)
                                                        {
                                                                var errorMsg = "Error calling internal API";
                                                                $('#newDelegation').val('');
                                                                $('#newDelegation').addClass('disabled');
                                                                $('#newDelegatedMsg').css('color', '#f3cf58');
                                                                $('#newDelegatedMsg').html(errorMsg);
                                                                $('#newDelegationConfirmBtn').addClass('disabled');

                                                                setTimeout(function()
                                                                {
                                                                        $('#newDelegation').removeClass('disabled');
                                                                        $('#newDelegatedMsg').css('color', '#f3cf58');
                                                                        $('#newDelegatedMsg').html('Delegated username can\'t be empty');
                                                                }, 2000);
                                                        }
                                                });
									                                        });//single delegation -end
                                                                               //group delegation -start------------------------------------------------------------------------------------------------------------
                                                                               $(document).on("click", "#newDelegationConfirmBtnGroup", function(event){
											var delegatedDN="";
											var e = document.getElementById("newDelegationGroup");
											if ((typeof e.options[e.selectedIndex] !== 'undefined')&&(e.options[e.selectedIndex].text!=='All groups')){
												delegatedDN = "cn="+e.options[e.selectedIndex].text+",";
											}
                                                                                       var e2 = document.getElementById("newDelegationOrganization");
											delegatedDN=delegatedDN+"ou="+e2.options[e2.selectedIndex].text;

                                                                                       newk1 = generateUUID();
                                                                                       newk2 = generateUUID();
                                                                                       $.ajax({
                                                                                               url: "../api/value.php",
                                                                                               data:
                                                                                               {
                                                                                                       action: "delegate_value",
                                                                                                       contextbroker: contextbroker,
                                                                                                       value_name: valueName,
                                                                                                       uri : uri,
                                                                                                       token : sessionToken,
                                                                                                       delegated_group: delegatedDN,
                                                                                                       k1: newk1,
                                                                                                       k2: newk2
                                                                                               },
                                                                                               type: "POST",
                                                                                               async: true,
                                                                                               dataType: 'json',
                                                                                               success: function(data)
                                                                                               {
                                                                                                       if (data["status"] === 'ok')
                                                                                                       {
														var toadd= $('#newDelegationOrganization').val();
														if ( document.getElementById("newDelegationGroup").options[e.selectedIndex].text!='All groups'){
															toadd=toadd+","+$('#newDelegationGroup').val();
														}

                                                                                                               $('#delegationsTableGroup tbody').append('<tr class="delegationTableRowGroup" data-delegationId="' + data["delegationId"] + '" data-delegated="' + toadd+ '"><td class="delegatedNameGroup">' +toadd + '</td><td><i class="fa fa-remove removeDelegationBtnGroup"></i></td></tr>');
                                                                                                               $('#newDelegatedMsgGroup').css('color', 'white');
                                                                                                               $('#newDelegatedMsgGroup').html('New delegation added correctly');

                                                                                                               setTimeout(function()
                                                                                                               {
                                                                                                                       $('#newDelegatedMsgGroup').css('color', '#f3cf58');
                                                                                                                       $('#newDelegatedMsgGroup').html('');
                                                                                                               }, 1500);
                                                                                                       }
                                                                                                       else
                                                                                                       {
                                                                                                               var errorMsg = null;
                                                                                                               $('#newDelegatedMsgGroup').css('color', '#f3cf58');
                                                                                                               $('#newDelegatedMsgGroup').html(data["msg"]);

                                                                                                               setTimeout(function()
                                                                                                               {
                                                                                                                       $('#newDelegatedMsgGroup').css('color', '#f3cf58');
                                                                                                                       $('#newDelegatedMsgGroup').html('');
                                                                                                               }, 2000);
                                                                                                       }
                                                                                               },
                                                                                               error: function(errorData)
                                                                                               {
                                                                                                       var errorMsg = "Error calling internal API";
                                                                                                       $('#newDelegatedMsgGroup').css('color', '#f3cf58');
                                                                                                       $('#newDelegatedMsgGroup').html(errorMsg);

                                                                                                       setTimeout(function()
                                                                                                       {
                                                                                                               $('#newDelegatedMsgGroup').css('color', '#f3cf58');
                                                                                                               $('#newDelegatedMsgGroup').html('');
                                                                                                       }, 2000);
                                                                                               }
                                                                                       });
                                                                               });     //group delegation -end

	

		}
						
$(document).on("click", "#delegationsCancelBtn", function(event){
		$('#newDelegation').val("");
		$("#newVisibilityResultMsg").html("");
		$("#newOwnershipResultMsg").html("");
		location.reload();
		$('#delegationsModal').modal('hide');
		 fetch_data(true);
});

/*	 
$('#delegationsModal').on('hidden.bs.modal', '.modal', function () {
     $(this).removeData('bs.modal');
});
*/
		
        function updateDeviceTimeout()
        {
            $("#editValueOkModal").modal('hide');
            setTimeout(function(){
               location.reload();
            }, 500);
        }
        
       
	function drawMap(latitude,longitude, id, devicetype, kind, divName){ 
     
     if (typeof map === 'undefined' || !map) { 
             map = L.map(divName).setView([latitude,longitude], zoomLevel);
             L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                 attribution: '&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap</a> contributors'
             }).addTo(map);

             window.node_input_map = map;   
         }
         
         map.setView([latitude,longitude], 10);

     if (typeof theMarker != 'undefined') {
             map.removeLayer(theMarker); 
            }
         theMarker= L.marker([latitude,longitude]).addTo(map).bindPopup(id + ', ' + devicetype + ', ' + kind);
         setTimeout(function(){ map.invalidateSize()}, 400);
  }
 
	 
/*	  function getLatLong(id, cb){
		     
			//console.log(id);
			var id = id;
				$.ajax({
					url: "../api/value.php",
					data: {
						id: id, 
						contextbroker : cb,
						organization : organization, 
						action: "get_value_latlong"
					},
					type: "POST",
					async: true,
					dataType: 'json',
					dataType: 'json',
					success: function (data) 
					{
					  //console.log(data);
					  var mylat =[];
					  var mylong=[];
					  var id =[];
					  if (data["status"] === 'ok')
					  {
						var data = data["content"];								
						for (var i=0; i<data.length; i++){
							 mylat.push(data[i].latitude);
							 mylong.push(data[i].longitude);
							 myid.push(data[i].id);
							}
							
							latitude = mylat[0];
							longitude = mylong[0];
							id = myid[0]
						  // $("#addMap").modal('show');
							drawMap(latitude,longitude, id);	 
						}			  
					},
					error: function (data)
					{
					   console.log("Error ajax in retrieving latitude and longitude data");
						console.log(JSON.stringify(data));
					}
					
				});
	   }*/
	   


function drawMapAll(data, divName){
		var latitude = 43.7800;
		var longitude =11.2300;
    
    if (typeof map_all === 'undefined' || !map_all) {
            //map_all = L.map(divName).setView([latitude,longitude], 10);
        var centerMapArr= gpsCentreLatLng.split(",",2);
        var centerLat= parseFloat(centerMapArr[0].trim());
        var centerLng= parseFloat(centerMapArr[1].trim());
        map_all = L.map(divName).setView([centerLat,centerLng], zoomLevel);   
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
           attribution: '&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap</a> contributors'
          }).addTo(map_all);
           window.node_input_map = map_all;
        
          /**************************Fatima-start******************************/
        /*var blueIcon = L.icon({
                        iconUrl: 'data:image/svg+xml;utf-8, \
                                  <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" width="512px" height="512px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve"><g><path d="M256,0C167.641,0,96,71.625,96,160c0,24.75,5.625,48.219,15.672,69.125C112.234,230.313,256,512,256,512l142.594-279.375   C409.719,210.844,416,186.156,416,160C416,71.625,344.375,0,256,0z M256,256c-53.016,0-96-43-96-96s42.984-96,96-96   c53,0,96,43,96,96S309,256,256,256z" fill="#006DF0"/></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg>',
                        iconSize:     [38, 95], // size of the icon
                        popupAnchor:  [-3, -76] // point from which the popup should open relative to the iconAnchor
                    });
            //from https://www.flaticon.com/free-icon/map-marker_33622#

        var redIcon = L.icon({
                        iconUrl: 'data:image/svg+xml;utf-8, \
                                  <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" width="512px" height="512px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve"><g><path d="M256,0C167.641,0,96,71.625,96,160c0,24.75,5.625,48.219,15.672,69.125C112.234,230.313,256,512,256,512l142.594-279.375   C409.719,210.844,416,186.156,416,160C416,71.625,344.375,0,256,0z M256,256c-53.016,0-96-43-96-96s42.984-96,96-96   c53,0,96,43,96,96S309,256,256,256z" fill="#D80027"/></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg>',
                        iconSize:     [38, 95], // size of the icon
                        popupAnchor:  [-3, -76] // point from which the popup should open relative to the iconAnchor
                    });*/

        
        /****************************Fatima-End**********************************/
        /*************Fatima2-start*************/  
        green_markersGroup= undefined;
        marker_selection=[];

                 redIcon = new L.Icon({

                                iconUrl: '../img/markerPrivate.png',
                                iconSize: new L.Point(32, 32),
                                iconAnchor: new L.Point(16, 16),
                                popupAnchor: new L.Point(0, -18)

                            });

                blueIcon = new L.Icon({

                                iconUrl: '../img/markerPublic.png',
                                iconSize: new L.Point(32, 32),
                                iconAnchor: new L.Point(16, 16),
                                popupAnchor: new L.Point(0, -18)

                            });
                greenIcon = new L.Icon({

                                iconUrl: '../img/markerGreen.png',
                                iconSize: new L.Point(32, 32),
                                iconAnchor: new L.Point(16, 16),
                                popupAnchor: new L.Point(0, -18)

                            });
    /*************Fatima2-end**************/

         var mapLayers = {};
        drawnItems = new L.FeatureGroup();
                map_all.addLayer(drawnItems);

                var editControl = new L.Control.Draw({
                    draw: false,
                    edit: {
                        //Fatima2-add-line
                        remove: false,
                        featureGroup: drawnItems,
                        poly: {
                            allowIntersection: false
                        }
                    }
                });
                map_all.addControl(editControl);

         drawControl = new L.Control.Draw({
             //Fatima2-add-line
             remove: false,      
             draw: {
                        position: 'topleft',
                        //polyline: false,
                        //marker: false,
                        circlemarker: false,
                        //polygon: false,
                        rectangle: false,
                        polygon: {
                            allowIntersection: false,
                            showArea: true
                        }
                    }
                });
                map_all.addControl(drawControl);

          L.control.layers(mapLayers, {
                    'drawlayer': drawnItems
                }, {
                    collapsed: true
                }).addTo(map_all);

         map_all.on(L.Draw.Event.CREATED, function(e) {
                    var fence = e.layer;
                    if (drawnItems.hasLayer(fence) == false) {
                        drawnItems.addLayer(fence);
                    }

                    drawControl.remove();
                    TYPE= e.layerType;
                    layer = e.layer;

             var resultsOut=drawSelection(layer, TYPE, data);
             $('#addMap1SA').modal('hide');
             //Fatima2-moveAndupdate-1-line
             colorSelectedMarkers(resultsOut, greenIcon);
            fetch_data(true, JSON.stringify(resultsOut));
				//console.log(resultsOut);
				

         });

         map_all.on('draw:edited', function(e) {
                    var fences = e.layers;
                    fences.eachLayer(function(fence) {
                        fence.shape = "geofence";
                        if (drawnItems.hasLayer(fence) == false) {
                            drawnItems.addLayer(fence);
                        }
                    });
                    drawnItems.eachLayer(function(layer) {
                        var resultsOut=drawSelection(layer, TYPE, data);     
                        //console.log(resultsOut);
                         $('#addMap1SA').modal('hide');
                        //Fatima2-moveAndupdate-1-line
                            colorSelectedMarkers(resultsOut, greenIcon);
                         fetch_data(true, JSON.stringify(resultsOut));
                        });

			
                });

         /******************Fatima-start*************************/ 
        /*map_all.on('draw:deleted', function(e) {
                    drawControl.addTo(map_all);
                });*/
        
        L.Control.RemoveAll = L.Control.extend(
                    {
                        options:
                        {
                            position: 'topleft',
                        },
                        onAdd: function (map_all) {
                            var controlDiv = L.DomUtil.create('div', 'leaflet-draw-toolbar leaflet-bar');
                            L.DomEvent
                                .addListener(controlDiv, 'click', L.DomEvent.stopPropagation)
                                .addListener(controlDiv, 'click', L.DomEvent.preventDefault)
                            .addListener(controlDiv, 'click', function () {
                                drawnItems.clearLayers();
                                
                                if(typeof green_markersGroup!= 'undefined'){
                                     map_all.removeLayer(green_markersGroup);
                                     green_markersGroup= undefined;
                                     green_marker_array=[];
                                     marker_selection=[];
                                    //Fatima2-moveLine 
                                    fetch_data(true);
                                } 
                                
                                 drawControl.addTo(map_all);
                
                            });
							 
							
                            var controlUI = L.DomUtil.create('a', 'leaflet-draw-edit-remove', controlDiv);
                            controlUI.title = 'Delete';
                            controlUI.href = '#';
                            return controlDiv;
                        }
                        
                       
                    });
                var removeAllControl = new L.Control.RemoveAll();
                map_all.addControl(removeAllControl);
        
        /******************Fatima-end***************************/



           for (var i=0; i<data.length; i++) {


            var mylat=data[i].latitude;
            var mylong= data[i].longitude;  
            var myname = data[i].name;
               if(mylat!=null && mylong!=null){

           
                    
                   if(data[i].visibility=="public"){
                    m = L.marker([mylat,mylong],{icon: blueIcon}).addTo(map_all).bindPopup(myname);
                   }
                   else{
                    m = L.marker([mylat,mylong], {icon: redIcon}).addTo(map_all).bindPopup(myname);
                   }     
                  
            //console.log("Before My Marker: " + mylat);
               }
            }
            setTimeout(function(){ map_all.invalidateSize()}, 400);
    }
} 

	
	
	/**********************Fatima2-start*****************************/

    function colorSelectedMarkers(selections, greenIcon){
                green_marker_array=[];
                //console.log("selections are");
                //console.log(selections);
                for(var k in selections){

                    lat=Number(selections[k].latitude); 
                    lng=Number(selections[k].longitude);
                    popup= selections[k].name;
                    var  m = L.marker([lat, lng],{icon: greenIcon}).bindPopup(popup);
                    green_marker_array.push(m);
                }
                

                    if (typeof green_markersGroup != 'undefined') {
                        //Fatima2-adjust
                        map_all.removeLayer(green_markersGroup); 
                    }
                    green_markersGroup = L.layerGroup(green_marker_array);
                    green_markersGroup.addTo(map_all);
                    marker_selection=selections;
                
            }

    /**********************Fatima-end******************************/
	
	function drawSelection(layer, type, data){
        var resultsOut=[]; 
        switch(type){
                 
             case 'circle':
                 circles = {};
		
		                drawnItems.eachLayer(function(layer) {
		                    circles[layer.nodeID] = layer.toGeoJSON();
		                    circles[layer.nodeID].properties.radius = Math.round(layer.getRadius()) / 1000;
		                });
		
		               
						var lat_map = (circles[layer.nodeID].geometry.coordinates[1]);
						var long_map = (circles[layer.nodeID].geometry.coordinates[0]);
						var center_latlong = new L.LatLng(lat_map, long_map);
						var rad_map = (circles[layer.nodeID].properties.radius);
						
						
						for (var deviceTocheck in data){
							
							
							var deviceLatLng = new L.LatLng(Number(data[deviceTocheck]["latitude"]), Number(data[deviceTocheck]["longitude"]));
												
							
							if(Math.abs(center_latlong.distanceTo(deviceLatLng)/1000) <= rad_map){
								
								resultsOut.push(data[deviceTocheck]);
							
								}
						}
                        
                 break;
             case 'polygon':
                 
                 var polyPoints = layer._latlngs[0];
					for (var deviceTocheck in data){
						
						//Ray Casting algorithm for checking if a point lies inside of a polygon
						var x = Number(data[deviceTocheck]["latitude"]), y= Number(data[deviceTocheck]["longitude"]);
										
						var inside = false;
		                for (var i = 0, j = polyPoints.length - 1; i < polyPoints.length; j = i++) {
		                    var xi = polyPoints[i].lat, yi = polyPoints[i].lng;
		                    var xj = polyPoints[j].lat, yj = polyPoints[j].lng;

		                    var intersect = ((yi > y) != (yj > y)) && (x < (xj - xi) * (y - yi) / (yj - yi) + xi);
		                    if (intersect) {
		                    	inside = !inside;
		                    }
		                }
						
						if(inside){
							
							resultsOut.push(data[deviceTocheck]);
						
							}
					}
                 break;
             case 'marker':
                 
                 var markerPoint = layer.getLatLng();
					
					for (var deviceTocheck in data){
						
						var deviceLatLng = new L.LatLng(Number(data[deviceTocheck]["latitude"]), Number(data[deviceTocheck]["longitude"]));
											
						
						if(Math.abs(markerPoint.distanceTo(deviceLatLng)/1000) <= 1){ //1 km 
							
							resultsOut.push(data[deviceTocheck]);
						
							}
					}
                 break;
             case 'polyline':
                   
          		var polyVerts = layer._latlngs;
					
					for (var deviceTocheck in data){
						
						isclose=false;
					
						var deviceLatLng = new L.LatLng(Number(data[deviceTocheck]["latitude"]), Number(data[deviceTocheck]["longitude"]));
						
						for (var vi=0, vl=polyVerts.length; vi<vl; vi++) {
	            	        var d = polyVerts[vi].distanceTo(deviceLatLng);
	            	        if (d/1000 <= 1) {
	            	        	isclose= true;
	            	        	break;
	            	        }
	            	    }
						
						if (isclose){
							resultsOut.push(data[deviceTocheck]);
						}
					}
                 break;
                 
                 
         }
        
        return resultsOut;
    }	




