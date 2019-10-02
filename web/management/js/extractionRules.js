myFile=null;
tableFirstLoad=true;
fileData=[];
editDeviceConditionsArray=[];
modelsdata=[];
receivedData=[];
var dataTable ="";
requiredHeaders=["name", "devicetype", "macaddress", "frequency", "kind", "protocol", "format", "producer", /*"edge_gateway_type", "edge_gateway_uri",  commented by Sara*/ "latitude", "longitude", "value_name", "data_type", "value_type", "editable", "value_unit", "healthiness_criteria", "healthiness_value", "k1", "k2"];

var gb_datatypes ="";
var gb_value_units ="";
var gb_value_types = "";
var defaultPolicyValue = [];
// var mynewAttributes = [];

var gb_options = [];

var gb_device ="";
var gb_latitude ="";
var gb_longitude = "";
var gb_key1;
var gb_key2;

var gb_old_id="";
var gb_old_cb="";
var dataTable ="";
//var _serviceIP = "http://localhost:3001";
var _serviceIP = "../stubs";
 //var _serviceIP = "https://159.149.129.184:3001";

 //var _serviceIP = "https://iot-app.snap4city.org/iotdirectory";
var timerID= undefined;
var was_processing=0;

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
//--------to get the models with their details----------------------//



$.ajax({url: "../api/extractionRules.php",
         data: {
			 action: 'get_param_values',
             organization:organization
			 },
         type: "POST",
         async: true,
         dataType: 'json',
         success: function (mydata)
         {
		   gb_datatypes= mydata["data_type"];
		   gb_value_units= mydata["value_unit"];
		   gb_value_types= mydata["value_type"];	
           console.log(mydata);
         },
		 error: function (mydata)
		 {
		   console.log(JSON.stringify(mydata));
		   alert("Network errors. <br/> Get in touch with the Snap4City Administrator<br/>"+ JSON.stringify(mydata));
		 }
});
//--------------------Ajax call function to upload file data---------------------//

//Sara811 - Start
   $("#newRule").off("click");
   $("#newRule").click(function(){
  
       $.ajax({
                url: "../api/value.php",
                data:{
                                          
                    action: "get_cb",
                    token : sessionToken, 
                    username: loggedUser, 
                    organization : organization, 
                    loggedrole:loggedRole                          
                },
                type: "POST",
                async: true,
                success: function (data)
                {
                        
                    if (data["status"] === 'ok')
                    {   
						var $dataType = $("#deviceTypeInput");        
                        $dataType.empty();
						$dataType.append($("<option />").text(""));  								
                        $.each(gb_datatypes, function() {
							$dataType.append($("<option />").val(this).text(this));  								
                        });
						
						var $valueType = $("#valueTypeInput");        
                        $valueType.empty();
						$valueType.append($("<option />").text(""));  								
                        $.each(gb_value_types, function() {
							$valueType.append($("<option />").val(this).text(this));  								
                        });

						var $valueUnit = $("#deviceValueUnit");        
                        $valueUnit.empty();
                        $.each(gb_value_units, function() {
							$valueUnit.append($("<option />").val(this).text(this));  								
                        });
						
                        var $dropdown = $("#selectContextBroker");        
                        $dropdown.empty();
                        $.each(data['content_cb'], function() {
							$dropdown.append($("<option />").val(this.name).text(this.name));  						
                        });
						
						
                        
                          $("#addDeviceModalBody").show();
                          $("#addDeviceLoadingMsg").hide();
                          $("#addDeviceLoadingIcon").hide();
                          $("#addDeviceOkMsg").hide();
                          $("#addDeviceOkIcon").hide();
                          $("#addDeviceKoMsg").hide();
                          $("#addDeviceKoIcon").hide();
                          $("#addDeviceModalTabs").show();
                          $("#addDeviceModalFooter").show();
						
                        showAddDeviceModal();
                        }
                    else{
                        console.log("error getting the context brokers and models "+data); 
                    }
                },
                error: function (data)
                {
                 console.log("error in the call to get the context brokers and models "+data);   
                }
          });
       
	
   });

//Sara811 - end

//-----------------------------------------------------------------------------//

/*************Table related ****************/


function updateDeviceTimeout()
        {
            $("#editDeviceOkModal").modal('hide');
            setTimeout(function(){
			location.reload();
            }, 1000);
        }
//---------------build the table-----------------------------//


  function format ( d ) {
		
	// `d` is the original data object for the row
  	return '<div class="container-fluid">' +
		
		'<div class="row">' +
			'<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>Selector:</b>' + "  " + d.selector + '</div>' +	
			'<div class="clearfix visible-xs"></div>' +
            '<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>Data type:</b>' + "  " + d.data_type + '</div>' +				
		'</div>' +
		'<div class="row">' +
			'<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Value Type:</b>' + "  " + d.value_type + '</div>' +
			'<div class="clearfix visible-xs"></div>' +
			'<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Value Unit</b>' + "  " + d.value_unit + '</div>' +
		'</div>' + 
		'<div class="row">' +
			'<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>Structure Value:</b>' + "  " + d.structure_flag + '</div>' +
			'<div class="clearfix visible-xs"></div>' +
		'</div>' +
	'</div>' ;
	
}

function fetch_data(destroyOld, selected=null)
        {
			console.log("usernametoDebug "+loggedUser);
         
		 //data=[];
            
            if(destroyOld)
            {
				$('#devicesTable').DataTable().destroy();
                tableFirstLoad = true;
				
            }  
			
			if (selected==null)
			{
			  mydata = {action: "get_rules", username: loggedUser, organization:organization,loggedrole:loggedRole, no_columns: ["position","edit","delete"]}; 
			  console.log("logged user" + loggedUser +  " organization "+ organization);
			}
			
            
	  dataTable = $('#devicesTable').DataTable({
		"processing" : true,
		"serverSide" : true,
		//"responsive" : true,
		"responsive": {
        details: false
		},
		"paging"   : true,
		"ajax" : {
		 url:"../api/extractionRules.php",
		 data: mydata,
		//token : sessionToken,
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
			{"name": "id", "data": function ( row, type, val, meta ) {
			
				return row.id;
				} },			
			{"name": "contextbroker", "data": function ( row, type, val, meta ) {
				  return row.contextbroker;
				} },
			{"name": "selector", "data": function ( row, type, val, meta ) {
				  return row.selector;
				} },
			{"name": "format", "data": function ( row, type, val, meta ) {
				  return row.format;
				} },
			{"name": "kind", "data": function ( row, type, val, meta ) {
				  return row.kind;
				} },
			{
                data: null,
				"name": "edit",
				"orderable":false,
                className: "center",
				render: function(d) {
				let sel = d.selector.replace(/"/g, '\'');
                //defaultContent: '<button type="button" id="edit" class="editDashBtn data-id="'+ row.name +'"">Edit</button>'
				return '<button type="button" class="editDashBtn" ' +
				'data-id="'+d.id+'" ' +
				'data-contextBroker="'+d.contextbroker+'" ' +
				'data-format="'+d.format+'" ' +
				'data-selector="'+sel+'" ' +
				'data-kind="'+d.kind+'" ' +
				'data-structure-flag="'+d.structure_flag+'" '+
				'data-d-type="'+d.data_type+'" ' +
				'data-value-type="'+d.value_type+'" ' +
				'data-value-unit="'+d.value_unit+'">Edit</button>';
				}
            },
			{
                data: null,
				"name": "delete",
				"orderable":      false,
                className: "center",
                //defaultContent: '<button type="button" id="delete" class="delDashBtn delete">Delete</button>'
				render: function(d) {
				return '<button type="button" class="delDashBtn" ' +
				'data-id="'+d.id+'" ' +
				'data-contextBroker="'+d.contextbroker+'" ' +
                'data-selector="'+d.selector+'" ' +
				'data-kind="'+d.kind+'">Delete</button>';
				}
            }
        ],  
    "order" : [] 
	  
   });
  
  }	 

 //end of fetch function 
	
     

$(document).ready(function () 
    {
//fetch_data function will load the device table 	
	fetch_data(false);	
		
//detail control for device dataTable
	var detailRows = [];
  	
	$('#devicesTable tbody').on('click', 'td.details-control', function () {
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


//delete attributes
	$("#attrNameDelbtn").off("click");
	$("#attrNameDelbtn").on("click", function(){
		console.log("#attrNameDelbtn");	
		$(this).parent('tr').remove();
	});	
		
		
	//Delete device button 
	$('#devicesTable tbody').on('click', 'button.delDashBtn', function () {
		console.log($(this));

		var id = $(this).attr('data-id');
		console.log("id delete "+ id);
		var contextbroker = $(this).attr('data-contextbroker');
		console.log("contextbroker "+ contextbroker);
		var uri = $(this).attr("data-uri");
        var status = $(this).attr("data-status1");

		$("#deleteDeviceModal div.modal-body").html('<div class="modalBodyInnerDiv"><span data-id = "' + id + '" data-contextbroker = "' + contextbroker + '" data-status1 = "' + status+ '"  data-uri ="' + uri +'">Do you want to confirm deletion of device <b>' + id + '</b>?</span></div>');
		$("#deleteDeviceModal").modal('show');
	});

	
	//Delete button hover - needs to be checked
	$('#devicesTable tbody').on('hover', 'button.delDashBtn', function () {
	//$('#devicesTable button.delDashBtn').off('hover');
	//$('#devicesTable button.delDashBtn').hover(function(){
		console.log($(this));
		$(this).css('background', '#ffcc00');
		$(this).parents('tr').find('td').eq(1).css('background', '#ffcc00');
	}, 
	function(){
		$(this).css('background', '#e37777');
		$(this).parents('tr').find('td').eq(1).css('background', $(this).parents('td').css('background'));
	});

//End Related to Delete Device
  

// Device dataTable table Style 
  
	$('#devicesTable thead').css("background", "rgba(0, 162, 211, 1)");
	$('#devicesTable thead').css("color", "white");
	$('#devicesTable thead').css("font-size", "1em");
	
	
	$('#devicesTable tbody tr').each(function(){
		if((dataTable.row( this ).index())%2 !== 0)
		{
			$('#devicesTable tbody').css("background", "rgba(0, 162, 211, 1)");
			console.log( 'Row index: '+dataTable.row( this ).index() );
			$(this).find('td').eq(0).css("background-color", "rgb(230, 249, 255)");
			$(this).find('td').eq(0).css("border-top", "none");
		}
		else
		{
			$(this).find('td').eq(0).css("background-color", "white");
			$(this).find('td').eq(0).css("border-top", "none");
		}
	});



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
        
        $(window).resize(function(){
            $('#mainContentCnt').height($('#mainMenuCnt').height() - $('#headerTitleCnt').height());
            if($(window).width() < 992)
            {
                //$('#devicesTable').bootstrapTable('hideColumn', 'id');
                //$('#devicesTable').bootstrapTable('hideColumn', 'contextBroker');
                //$('#devicesTable').bootstrapTable('hideColumn', 'uri');
                //$('#devicesTable').bootstrapTable('hideColumn', 'protocol');
                //$('#devicesTable').bootstrapTable('hideColumn', 'format');
				//$('#devicesTable').bootstrapTable('hideColumn', 'devicetype');
				//$('#devicesTable').bootstrapTable('hideColumn', 'visibility');
				//$('#devicesTable').bootstrapTable('hideColumn', 'status1');
				
                //$('#devicesTable').bootstrapTable('hideColumn', 'type');
            
            }
            else
            {
                //$('#devicesTable').bootstrapTable('showColumn', 'id');
                //$('#devicesTable').bootstrapTable('showColumn', 'contextBroker');
				//$('#devicesTable').bootstrapTable('showColumn', 'devicetype');
				//$('#devicesTable').bootstrapTable('showColumn', 'visibility');
				//$('#devicesTable').bootstrapTable('showColumn', 'status1');
                //$('#devicesTable').bootstrapTable('showColumn', 'uri');
                //$('#devicesTable').bootstrapTable('showColumn', 'protocol');
                //$('#devicesTable').bootstrapTable('showColumn', 'format');
                //$('#devicesTable').bootstrapTable('showColumn', 'type');
           
            }
        });
		
		//$("#addMyNewDeviceRow").hide();
		
		for (var func =0;func < functionality.length; func++)
		{
		  var element = functionality[func];
		  if (element.view=="view")
		  {
			  if (element[loggedRole]==1)  
			   {   // console.log(loggedRole + " " + element[loggedRole] + " " + element["class"]); 
				   $(element["class"]).show();
			   }			   
			   else 
			   { 
				 $(element["class"]).hide();
				 // console.log($(element.class));
				//  console.log(loggedRole + " " + element[loggedRole] + " " + element["class"]);
			   }
			}   
		}
		
		
		$('#listDevicesLink .mainMenuItemCnt').addClass("mainMenuItemCntActive");
        $('#mobMainMenuPortraitCnt #listDevicesLink .mobMainMenuItemCnt').addClass("mainMenuItemCntActive");
        $('#mobMainMenuLandCnt #ListDevicesLink .mobMainMenuItemCnt').addClass("mainMenuItemCntActive");
    
    

        
    console.log("ok I am building the main table z");
   // buildMainTable(false);
	



	$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
		var target = $(e.target).attr("href");
		if ((target == '#editGeoPositionTabDevice')) {
			console.log("Elf : EditDeviceMap");
			var latitude = $("#inputLatitudeDeviceM").val(); 
			var longitude = $("#inputLongitudeDeviceM").val();
			var flag = 1;
			drawMap1(latitude,longitude, flag);
		} 
	});
		
	
	$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
	var target = $(e.target).attr("href");
	if ((target == '#editStatusTabDevice')) {
		
		var id = document.getElementById('inputNameRuleM').value;
		var contextbroker = document.getElementById('selectContextBrokerM').value;
		var kind = document.getElementById('inputRuleTypeM').value;
		var latitude = document.getElementById('inputLatitudeDeviceM').value;
		var longitude = document.getElementById('inputLongitudeDeviceM').value;
		var protocol = document.getElementById('selectProtocolDeviceM').value;
		
	/*   if (id==null || id=="") { var idNote = ("\n id not specified");} else{idNote = "&#10004;";}
	   if (contextbroker==null || contextbroker=="") {var contextbrokerNote = ("cb not specified");} else{contextbrokerNote = "&#10004;";}
	   if (type==null || type=="") {var typeNote = ("type not specified");} else{typeNote = "&#10004;";}
	   if (!(kind=="sensor" || kind=="actuator")) {var kindNote = ("\n kind not specified");}  else{kindNote = "&#10004;";}
	   if ((latitude < -90 && latitude > 90) || (latitude=="" || latitude==null)) {var latitudeNote = ("\n latitude not correct ");} else{latitudeNote = "&#10004;";}
	   if ((longitude < -180 && longitude > 180) || (longitude=="" || longitude==null)) {var longitudeNote = ("\n longitude not correct ");} else{longitudeNote = "&#10004;";}
	   if (!(protocol=="ngsi" || protocol=="mqtt" || protocol=="amqp")) {var protocolNote = ("protocol not correct ");} else{protocolNote = "&#10004;";}
	*/	
		console.log(id + contextbroker + type + kind + latitude + longitude + protocol);
	
			if ((idNote == "&#10004;") && (contextbrokerNote == "&#10004;") && (typeNote == "&#10004;") && (kindNote == "&#10004;") && (latitudeNote == "&#10004;") && (longitudeNote == "&#10004;") && (protocolNote == "&#10004;")){var statusNote = "<button class=\"btn btn-success btn-round\"></button>";} else{statusNote= "<button class=\"btn btn-danger btn-round\"></button>";}
		
		var x =inputPropertiesDeviceMMsg.innerHTML;
		
		var div = document.createElement("div");
		console.log("IPDMM:" + x);
		
		if (x =="&nbsp;"){
			}
		else{
			inputPropertiesDeviceMMsg.innerHTML="";
		}

		div.innerHTML = ("<div style=\"border:3px solid blue;\" >" +
		"<h2>Device Status</h2>" +
		"<table class=\"table\"><thead><tr><th>Property Status</th><th> checked</th></tr></thead>" +
		"<tbody><tr><td>id</td><td>" + idNote + "</td></tr>" +
		"<tr><td>Contextbroker</td><td>" + contextbrokerNote + "</td></tr>" +
		"<tr><td>Type</td><td>" + typeNote + "</td></tr>" +
		"<tr><td>Kind</td><td>" + kindNote +" </td></tr>" +
		"<tr><td>Protocol</td><td>" + protocolNote + "</td></tr>" +
		"<tr><td>Latitude</td><td>"+ latitudeNote +" </td></tr>" +
		"<tr><td>Longitude</td><td>"+ longitudeNote + "</td></tr>" +
		"<tr><td>Overall Status</td><td>"+ statusNote + "</td></tr>" +
		"</tbody></table></div>");
		inputPropertiesDeviceMMsg.appendChild(div);
		
		
		} 

		
	});			


	

//INSERT BULK DEVICES 

// add lines related to attributes			
	$("#addAttrBtn").off("click");
	$("#addAttrBtn").click(function(){
	   console.log("#addAttrBtn");							   
	   content = drawAttributeMenu("","", "", "", "", "", " "," ",  'addlistAttributes');
		// addDeviceConditionsArray['addlistAttributes'] = true;
	   //console.log("contenuto drawAttr" +content);
	   $('#addlistAttributes').append(content);
	});			
	
//DELETE DEVICE (DELETE FROM DB)  			
		
	// Delete lines related to attributes 

	$("#attrNameDelbtn").off("click");
	$("#attrNameDelbtn").on("click", function(){
		console.log("#attrNameDelbtn");	
		$(this).parent('tr').remove();
		});	
					
	 $('#devicesTable button.delDashBtn').off('click');
	 $('#devicesTable button.delDashBtn').click(function(){
		var id = $(this).attr('data-id');
		var contextBroker = $(this).attr("data-contextbroker");
		var uri = $(this).attr("data-uri");
		var status = $(this).attr("data-status1");
		
		$("#deleteDeviceModal div.modal-body").html('<div class="modalBodyInnerDiv"><span data-id = "' + id + '" data-contextBroker = "' + contextBroker + '"  data-uri ="' + uri + '" data-status1 = "' + status+'">Do you want to confirm deletion of device <b>' + id + '</b>?</span></div>');
		$("#deleteDeviceModal").modal('show');
	});
								
		
	
        $('#deleteDeviceConfirmBtn').off("click");
        $("#deleteDeviceConfirmBtn").click(function(){
			
            var id = $("#deleteDeviceModal span").attr("data-id");
			var contextbroker = $("#deleteDeviceModal span").attr("data-contextbroker");
            var uri = $("#deleteDeviceModal span").attr("data-uri");
            var status = $("#deleteDeviceModal span").attr("data-status1");
            console.log("valori val "+id +" "+contextbroker + " " + status);
	 
            $("#deleteDeviceModal div.modal-body").html("");
            $("#deleteDeviceCancelBtn").hide();
            $("#deleteDeviceConfirmBtn").hide();
            $("#deleteDeviceModal div.modal-body").append('<div id="deleteDeviceModalInnerDiv1" class="modalBodyInnerDiv"><h5>Device deletion in progress, please wait</h5></div>');
            $("#deleteDeviceModal div.modal-body").append('<div id="deleteDeviceModalInnerDiv2" class="modalBodyInnerDiv"><i class="fa fa-circle-o-notch fa-spin" style="font-size:36px"></i></div>');

            /*********Sara start - delete from json *****/
			var toDelete = {id: id, uri: uri, contextBroker: contextbroker, status: status};
			//deleteJSONvalues(toDelete);
            console.log("GOING TO DELETE ");
            console.log("id "+ id);
            console.log("status"+status);
			/****Sara end****/
            
            $.ajax({
                url: "../api/extractionRules.php",
				data:{
					action: "delete_rule",
					id: id, 
					username: loggedUser,
					contextbroker : contextbroker,
					token : sessionToken,
                    organization:organization
					},
                type: "POST",
				datatype: "json",
                async: true,
                success: function (data) 
                {
					console.log(JSON.stringify(data));
                    if(data["status"] === 'ko')
                    {
                        $("#deleteDeviceModalInnerDiv1").html(data["msg"]);
                        $("#deleteDeviceModalInnerDiv2").html('<i class="fa fa-frown-o" style="font-size:42px"></i>');
                    
					    setTimeout(function()
                        {  $("#deleteDeviceModal").modal('hide');  
                        }, 2000);
					}
                    else if(data["status"] === 'ok')
                    {
                        $("#deleteDeviceModalInnerDiv1").html('Device &nbsp; <b>' + id + '</b> &nbsp;deleted successfully');
                        $("#deleteDeviceModalInnerDiv2").html('<i class="fa fa-check" style="font-size:42px"></i>');
						
						setTimeout(function()
                        {
                            fetch_data(true);							
                            $("#deleteDeviceModal").modal('hide');
							
                            setTimeout(function(){
                                $("#deleteDeviceCancelBtn").show();
                                $("#deleteDeviceConfirmBtn").show();
                            }, 500);
                        }, 2000);
                    }
                },
                error: function (data) 
                {
					console.log(JSON.stringify(data));
                    $("#deleteDeviceModalInnerDiv1").html(data["msg"]);
                    $("#deleteDeviceModalInnerDiv2").html('<i class="fa fa-frown-o" style="font-size:42px"></i>');
					setTimeout(function()
                        {  $("#deleteDeviceModal").modal('hide');  
                        }, 2000);
                }
            });
        });
       

$('#devicesTable tbody').on('click', 'button.editDashBtn', function () {
	$("#editDeviceModalBody").show();
	$('#editDeviceModalTabs').show();

	$("#editDeviceLoadingMsg").hide();
	$("#editDeviceLoadingIcon").hide();
	$("#editDeviceOkMsg").hide();
	$("#editDeviceOkIcon").hide();
	$("#editDeviceKoMsg").hide();
	$("#editDeviceKoIcon").hide(); 
	$("#editDeviceModalFooter").show();
	$("#editDeviceModalLabel").html("Edit device - " + $(this).attr("data-id"));
	$("#editDeviceModal").modal('show');
	  
	   
	var id = $(this).attr('data-id');
		gb_old_id = id;
	var contextbroker = $(this).attr('data-contextBroker');
			gb_old_cb = contextbroker;
	var kind = $(this).attr('data-kind');
	if(kind.localeCompare("property")==0){
		 $("#dataTypeSelM").hide();
		 $("#valueTypeSelM").hide();
		 $("#valueUnitSelM").hide();
	}
	else if(kind.localeCompare("value")==0){
		 $("#dataTypeSelM").show();
		 $("#valueTypeSelM").show();
		 $("#valueUnitSelM").show();		
	}
	var selector = $(this).attr('data-selector');
	selector = selector.replace(/'/g, '\"');

	var format = $(this).attr('data-format');
	var data_type =$(this).attr('data-d-type');
	var value_type = $(this).attr('data-value-type');
	var value_unit = $(this).attr('data-value-unit');
	var structure_flag = $(this).attr('data-structure-flag');

	$("#editDeviceGenerateKeyBtn").show();
	$('#inputNameRuleM').val(id);
	$('#selectContextBrokerM').val(contextbroker);
	$('#selectKindDeviceM').val(kind);
	$('#inputSelectorM').val(selector);
	$('#inputFormatM').val(format);
	$('#inputDataTypeM').val(data_type);
	$('#inputValueTypeM').val(value_type);
	$('#valueUnitDeviceM').val(value_unit);
	$('#structureValueFlagM').val(structure_flag);

	var $dataType = $("#inputDataTypeM");        
	$dataType.empty();
	$dataType.append($("<option />").text(""));  	
    $dataType.append($("<option selected/>").val(data_type).text(data_type));	
	$.each(gb_datatypes, function() {
        if(this != data_type)
		$dataType.append($("<option />").val(this).text(this));  								
	});
	
	var $valueType = $("#inputValueTypeM");        
	$valueType.empty();
	$valueType.append($("<option />").text(""));  
    $valueType.append($("<option selected/>").val(value_type).text(value_type));		
	$.each(gb_value_types, function() {
        if(this != value_type)
		$valueType.append($("<option />").val(this).text(this));  								
	});

	var $valueUnit = $("#valueUnitDeviceM");        
	$valueUnit.empty();
    $valueUnit.append($("<option selected/>").val(value_unit).text(value_unit));		
	$.each(gb_value_units, function() {
        if(this != value_unit)
		$valueUnit.append($("<option />").val(this).text(this));  								
	});
	//sara -> could be a problem
	$('#editDeviceModal').show();	

		$.ajax({
                url: "../api/value.php",
                data:{
                                          
                    action: "get_cb",
                    token : sessionToken, 
                    username: loggedUser, 
                    organization : organization, 
                    loggedrole:loggedRole                          
                },
                type: "POST",
                async: true,
                success: function (data)
                {
                        
                    if (data["status"] === 'ok')
                    {        
                        var $dropdown = $("#selectContextBrokerM");        
                        $dropdown.empty();
                        $dropdown.append($("<option selected/>").val(contextbroker).text(contextbroker));
                        $.each(data['content'], function() {
                            if(this.name != contextbroker)
                            $dropdown.append($("<option />").val(this.name).text(this.name));        
                        });
                          showEditDeviceModal();

                         //$('#editDeviceModal').show();
				
                        }
                    else{
                        console.log("error getting the context brokers "+data); 
                    }
                },
                error: function (data)
                {
                 console.log("error in the call to get the context brokers "+data);   
                }
          });
});	 

$('#editRuleConfirmBtn').off("click");
$("#editRuleConfirmBtn").click(function(){

	$("#editDeviceModalTabs").hide();
	$('#editDeviceModal div.modalCell').hide();
	$("#editDeviceModalFooter").hide();
	$("#addAttrMBtn").hide();
	$("#editDeviceGenerateKeyBtn").hide();
			
	$('#editDeviceLoadingMsg').show();
	$('#editDeviceLoadingIcon').show();


    //UPDATE FUNCTION
	$.ajax({
        url: "../api/extractionRules.php",
        data:{
		    action: "update", 
			username: loggedUser,
            organization:organization,
			id: $('#inputNameRuleM').val(),
		    contextbroker: $('#selectContextBrokerM').val(),
			selector:  $('#inputSelectorM').val(),
			old_id: gb_old_id,
		    old_cb: gb_old_cb,
			format: $('#inputFormatM').val(),
			kind: $('#selectKindDeviceM').val(),
			data_type: $('#inputDataTypeM').val(),
			value_type: $('#inputValueTypeM').val(),
			value_unit: $('#valueUnitDeviceM').val(),
			structure_flag: $('#structureValueFlagM').val()
		},
        type: "POST",
        async: true,
        success: function (data) 
        {
			console.log("Marco edit Data " + JSON.stringify(data));

			if(data["status"] === 'ko')
            {
				console.log("Error editing rule type");
				console.log(data);
				$('#editDeviceLoadingMsg').hide();
				$('#editDeviceLoadingIcon').hide();
				$('#editDeviceLoadingIcon').hide();
				$('#editDeviceKoMsg').show();
				$('#editDeviceKoIcon').show();
				 
				setTimeout(function(){
					$('#editDeviceModal').modal('hide');
					fetch_data(true);
					setTimeout(function(){
						   $('#editDeviceModal').hide();
						   setTimeout(updateDeviceTimeout, 100);	  
						  
					}, 100); 
				}, 100);
			} 
			else if (data["status"] === 'ok')
			{
				$('#editDeviceLoadingMsg').hide();
				$('#editDeviceLoadingIcon').hide();
				$('#editDeviceOkMsg').show();
				$('#editDeviceOkIcon').show();
							
				$("#editDeviceModalInnerDiv1").html('Rule &nbsp; successfully Updated');
				$("#editDeviceModalInnerDiv2").html('<i class="fa fa-check" style="font-size:42px"></i>');
			 
				setTimeout(function(){
					$('#editDeviceModal').modal('hide');
					fetch_data(true);

					setTimeout(function(){
						
						$('#editDeviceOkMsg').hide();
						$('#editDeviceOkIcon').hide();
						$('#inputNameRule').val("");
						$('#inputFormat').val("");
						$('#selectContextBroker').val("");
						$('#inputSelector').val("");
						$('#deviceTypeInput').val("");
						$('#valueTypeInput').val("");
						$('#deviceValueUnit').val("");
						$('#structureValueFlag').val("");
							
						$('#editDeviceModal').hide();
						setTimeout(updateDeviceTimeout, 100);	  
						  
					}, 100);
				}, 100);
				
			} else {console.log(data);}
	 
		},
		error: function (data) 
		{
			console.log("Ko result: " + JSON.stringify(data));
			$("#editDeviceKoModalInnerDiv1").html(data["msg"]);
			$("#editDeviceKoModal").modal('show');
			 // $("#editDeviceModalUpdating").hide();
			$("#editDeviceModalBody").show();
			$("#editDeviceModalFooter").show();
							  
			$('#inputNameRule').val("");
			$('#inputFormat').val("");
			$('#selectContextBroker').val("");
			$('#inputSelector').val("");
			$('#deviceTypeInput').val("");
			$('#valueTypeInput').val("");
			$('#deviceValueUnit').val("");
			$('#structureValueFlag').val("");
								
			$('#editDeviceModal').hide();
			setTimeout(updateDeviceTimeout, 3000);
				  
		}
	}); 
});


$("#editDeviceCancelBtn").off("click");
$("#editDeviceCancelBtn").on('click', function(){


});	
			
$("#addNewDeviceCancelBtn").off("click");
$("#addNewDeviceCancelBtn").on('click', function(){
            
	$('#inputNameRule').val("");
	$('#inputFormat').val("");
	$('#selectContextBroker').val("");
	$('#inputSelector').val("");
	$('#deviceTypeInput').val("");
	$('#valueTypeInput').val("");
	$('#deviceValueUnit').val("");
	$('#structureValueFlag').val("");
			  
	location.reload();    								  
			//  $('#addDeviceModalTabs').show();
			//  $('#addDeviceModal div.modalCell').show();
});
        
$("#addDeviceKoBackBtn").off("click");

$("#addDeviceKoBackBtn").on('click', function(){
	$("#addDeviceKoModal").modal('hide');
	$("#addDeviceModal").modal('show');
});
        
$("#addDeviceKoConfirmBtn").off("click");
$("#addDeviceKoConfirmBtn").on('click', function(){
	$("#addDeviceKoModal").modal('hide');
	$("#addDeviceForm").trigger("reset");
});

$("#editDeviceKoBackBtn").off("click");
$("#editDeviceKoBackBtn").on('click', function(){
	$("#editDeviceKoModal").modal('hide');
	$("#editDeviceModal").modal('show');
});

$("#editDeviceKoConfirmBtn").off("click");
$("#editDeviceKoConfirmBtn").on('click', function(){
	$("#editDeviceKoModal").modal('hide');
	$("#editDeviceForm").trigger("reset");
});
		
	
$('#devicesTable thead').css("background", "rgba(0, 162, 211, 1)");
$('#devicesTable thead').css("color", "white");
$('#devicesTable thead').css("font-size", "1em"); 
   
   
});  // end of ready-state

// ADD NEW DEVICE  (INSERT INTO DB) 

	$('#addNewRuleConfirmBtn').off("click");
	$('#addNewRuleConfirmBtn').click(function(){
		
		var data_type = $('#deviceTypeInput').val();
		var value_type = $('#valueTypeInput').val();
		var value_unit = $('#deviceValueUnit').val();
		var structure_value = $('#structureValueFlag').val();
		
		console.log("confirm "+ data_type + " value type "+ value_type + " value unit "+ value_unit);
		$("#addDeviceModalTabs").hide();
		$("#addDeviceModalBody").hide();
		$('#addDeviceModal div.modalCell').hide();
		$("#addDeviceModalFooter").hide();
		$("#addDeviceOkMsg").hide();
		$("#addDeviceOkIcon").hide();
		$("#addDeviceKoMsg").hide();
		$("#addDeviceKoIcon").hide();
		$('#addDeviceLoadingMsg').show();
		$('#addDeviceLoadingIcon').show();

	$.ajax({
		 url: "../api/extractionRules.php",
		 data:{
			  action: "insert",   
			  //Sara2510 - for logging purpose
			  username: loggedUser,
			  organization : organization,  
			  id: $('#inputNameRule').val(),
			  contextbroker: $('#selectContextBroker').val(),
			  format: $('#inputFormat').val(),
			  selector: $('#inputSelector').val(),
			  kind: $('#selectKindDevice').val(),
			  data_type: $('#deviceTypeInput').val(),
			  value_type: $('#valueTypeInput').val(),
			  value_unit: $('#deviceValueUnit').val(), 
			  structure_flag: $('#structureValueFlag').val()
			},
		 type: "POST",
		 async: true,
		 dataType: "JSON",
		 //timeout: 0,
		 success: function (mydata) 
		 {
			var d = new Date();
			var t = d.getTime();
			console.log("time after a successful insert request in milliseconds");
			console.log(t);
			console.log(mydata["msg"]);
			if(mydata["status"] === 'ko')
			{
				console.log("Ko adding rule");
				console.log(mydata);
				$('#addDeviceLoadingMsg').hide();
				$('#addDeviceLoadingIcon').hide();
						
				$("#addDeviceModal").hide();
                $('#inputNameRule').val("");
				$('#inputFormat').val("");
				$('#selectContextBroker').val("");
				$('#inputSelector').val("");
				$('#deviceTypeInput').val("");
				$('#valueTypeInput').val("");
				$('#deviceValueUnit').val("");
				$('#structureValueFlag').val("");			
				$("#addDeviceKoModal").modal('show');
				$("#addDeviceOkModal").hide();
				if(mydata["error_msg"]!='undefined' && mydata["error_msg"]!="")                
				    $("#addDeviceKoModalInnerDiv1").html('<h5>Operation failed, due to the following Error: ' + mydata["error_msg"]+ '</h5>');
                else
                    $("#addDeviceKoModalInnerDiv1").html('<h5>An error occurred, operation failed.</h5>');
		        
				$('#devicesTable').DataTable().destroy();
                fetch_data(true);
				setTimeout(updateDeviceTimeout, 1000);
			}			 
			else if (mydata["status"] === 'ok')
			{
				console.log("Success adding rule");
				console.log(JSON.stringify(mydata));
				$('#addDeviceLoadingMsg').hide();
				$('#addDeviceLoadingIcon').hide();
							
				$("#addDeviceModal").hide();
                $('#inputNameRule').val("");
				$('#inputFormat').val("");
				$('#selectContextBroker').val("NULL");
				$('#inputSelector').val("");
				$('#deviceTypeInput').val("");
				$('#valueTypeInput').val("");
				$('#deviceValueUnit').val("");
				$('#structureValueFlag').val("");
			
								
				$("#addDeviceOkModal").modal('show');
				$("#addDevicekoModal").hide();
                
				$("#addDeviceOkModalInnerDiv1").html('The device has been successfully registered.');
                $("#addDeviceOkModalInnerDiv2").html('<i class="fa fa-check" style="font-size:42px"></i>');
						
				$('#devicesTable').DataTable().destroy();
                fetch_data(true);
				setTimeout(updateDeviceTimeout, 1000);
			} 
			 
		 },
		 error: function (mydata)
								{
			   console.log("Error inserting rule");  
			   console.log("Error status -- Ko result: " + JSON.stringify(mydata));
				$('#addDeviceLoadingMsg').hide();
				$('#addDeviceLoadingIcon').hide();
							
				$("#addDeviceModal").modal('hide');

                $('#inputNameRule').val("");
				$('#inputFormat').val("");
				$('#selectContextBroker').val("NULL");
				$('#inputSelector').val("");
				$('#deviceTypeInput').val("NULL");
				$('#valueTypeInput').val("NULL");
				$('#deviceValueUnit').val("");
				$('#structureValueFlag').val("");
			
				console.log("Error adding rule");
				console.log(mydata);						
				$("#addDeviceKoModal").modal('show');
				$("#addDeviceOkModal").hide();
                if(mydata["error_msg"]!='undefined' && mydata["error_msg"]!="")                
				    $("#addDeviceKoModalInnerDiv1").html('<h5>Operation failed, due to the following Error: ' + mydata["error_msg"]+ '</h5>');
                else
                    $("#addDeviceKoModalInnerDiv1").html('<h5>An error occurred, operation failed.</h5>');
				setTimeout(updateDeviceTimeout, 1000);
			}
		});
	});	



 
/*****Sara end**/

function refresh(){
    location.reload();
}
