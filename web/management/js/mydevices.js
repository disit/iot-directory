
var gb_datatypes ="";
var gb_value_units ="";
var gb_value_types = "";
var defaultPolicyValue = [];
var gb_delegated= false;

var gb_device ="";
var gb_latitude ="";
var gb_longitude = "";
var gb_k1="";
var gb_k2="";
var gb_delegateDelete = false;


var dataTable ="";
// var gb_key1="";
// var gb_key2="";

     //   var existingPoolsJson = null;
        // var internalDest = false;
        var tableFirstLoad = true;

//Settaggio dei globals per il file usersManagement.js
 //       setGlobals(admin, existingPoolsJson);
        

	$.ajax({url: "../api/device.php",
		 data: {
			 organization : organization, 
			 action: 'get_param_values'
			 },
		 type: "POST",
		 async: true,
		 dataType: 'json',
		 success: function (mydata)
		 {
		   gb_datatypes= mydata["data_type"];
		   gb_value_units= mydata["value_unit"];
		   gb_value_types= mydata["value_type"];		   
		 },
		 error: function (mydata)
		 {
		   console.log(JSON.stringify(mydata));
		   alert("Network errors. <br/> Get in touch with the Snap4City Administrator<br/>"+ JSON.stringify(mydata));
		 }
	});
     
	function updateDeviceTimeout()
	{
		$("#editDeviceOkModal").modal('hide');
		setTimeout(function(){
		   location.reload();
		}, 500);
	}

	function download(sourcename, devicename, contextbroker) {
			$.ajax({url: "../api/device.php",
					data: {
							token : sessionToken,
							action: 'download',
							filename: sourcename,
							organization : organization, 
							devicename:devicename,
                            contextbroker: contextbroker
					},
					type: "POST",
					async: true,
					dataType: 'json',
					success: function (mydata) {
							var element = document.createElement('a');
							element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(mydata.msg));
							element.setAttribute('download', sourcename.substr(sourcename.indexOf("/", 2)+1));
							element.style.display = 'none';
							document.body.appendChild(element);
							element.click();
							document.body.removeChild(element);
					},
					error: function (mydata)
					{
							console.log("error:".mydata);
					}
			});
	}

	
  function format ( d ) {
	    var showKey="";
	  if (d.visibility =='MyOwnPublic' || d.visibility == 'MyOwnPrivate' || d.visibility == 'delegated' ){
		if(d.k1!="" && d.k2!="")
          showKey =  '<div class="row">' +
			'<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>K1:</b>' + "  " + d.k1 + '</div>' +
			'<div class="clearfix visible-xs"></div>' +
			'<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>K2:</b>' + "  " + d.k2  + '</div>' +	
		'</div>'; 	  
		}
	else showKey=""; 
	 
	var txtCert="";
		if (d.privatekey!="" && d.privatekey!= null&& (d.visibility =='MyOwnPublic' || d.visibility == 'MyOwnPrivate'))
			txtCert  = '<div class="row">' +
							'<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>SHA1:</b>' + "  " + d.sha + '</div>' +
							'<div class="clearfix visible-xs"></div>' +
							'<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Created on:</b>' + "  " + d.created + '</div>' +
						'</div>'+
						'<div class="row">' +
							'<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><button class="btn btn-warning" onclick="download(\'\/private\/'+d.privatekey+'\',\''+d.device+'\',\''+d.cb+'\');return true;"><b>Private Key</b></button></div>' +
							'<div class="clearfix visible-xs"></div>' +
							'<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><button class="btn btn-warning" onclick="download(\'\/certsdb\/'+d.certificate+'\',\''+d.device+'\',\''+d.cb+'\');return true;"><b>Certificate</b></button></div>' +
						'</div>'+
						'<div class="row">' +
                                                        '<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><a href="https://www.snap4city.org/ca/ca.pem" download><button class="btn btn-warning"><b>CA Certificate</b></button></a></div>' +
                                                '</div>';
		else
			txtCert = '<div class="row">' +
							'<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Created on:</b>' + "  " + d.created + '</div>' +
							'<div class="clearfix visible-xs"></div>' +
							
					  '</div>';
	
	// `d` is the original data object for the row
  	var result= '<div class="container-fluid">' +
		'<div class="row">' +
			'<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>IoT Broker URI:</b>' + "  " + d.accesslink + '</div>' +
			'<div class="clearfix visible-xs"></div>' +
			'<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>IoT Broker Port:</b>' + "  " + d.accessport + '</div>' +								
		'</div>' +
		'<div class="row">' +
			'<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Data Type:</b>' + "  " + d.data_type + '</div>' +
			'<div class="clearfix visible-xs"></div>' +
			'<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Editable:</b>' + "  " + d.editable + '</div>' +
		'</div>' + 
		'<div class="row">' +
			'<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>Healthiness Criteria:</b>' + "  " + d.healthiness_criteria + '</div>' +
			'<div class="clearfix visible-xs"></div>' +
			'<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>Refresh Rate:</b>' + "  " + d.value_refresh_rate + '</div>' +	
		'</div>' +
		'<div class="row">' +
			'<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Kind:</b>' + "  " + d.kind + '</div>' +
			'<div class="clearfix visible-xs"></div>' +
			'<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Value Name:</b>' + "  " + d.value_name + '</div>' +
		'</div>' + 
		'<div class="row">' +
			'<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>Longitude:</b>' + "  " + d.longitude + '</div>' +
			'<div class="clearfix visible-xs"></div>' +
			'<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>Latitude:</b>' + "  " + d.latitude  + '</div>' +
		'</div>' ; 
        	
      if (!gb_delegated) {   
		result += '<div class="row">' +
			'<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Gateway/Edge Type:</b>' + "  " + d.edgegateway_type + '</div>' +
			'<div class="clearfix visible-xs"></div>' +
			'<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Gateway/Edge Uri:</b>' + "  " + d.edgegateway_uri  + '</div>' +	
		'</div>' ;
      }
      
	result+=	'<div class="row">' +
			'<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Organization:</b>' + "  " + d.organization + '</div>' +
			'<div class="clearfix visible-xs"></div>' +
		'</div>'
		+ showKey  + txtCert + 
	'</div>' ;
      return result;
	
}

	
   
//DataTable fetch_data function 
   
	function fetch_data(destroyOld, delegated=null, selected=null)
	{
		if(destroyOld)
		{
			 $('#devicesTable').DataTable().clear().destroy();
			//$('#devicesTable').DataTable().clear().draw();

			tableFirstLoad = true;
			
		}
	   
	   
		if (delegated==null)
		{	
			if (selected==null)
			{
			  mydata = {action: "get_all_private_event_value", /*Sara611 - logging */ username: loggedUser, organization : organization, loggedrole:loggedRole, token : sessionToken, no_columns: ["position","status1","delete","map"]};
			}
			else
			{
			  mydata = {action: "get_subset_event_value", /*Sara611 - logging */ username: loggedUser, organization : organization, loggedrole:loggedRole, token : sessionToken, select : selected, no_columns: ["position","status1","delete","map"]};
			}
		}
		else  			
		{
			if (selected==null)
			{
			  mydata = {action: "get_all_delegated_event_value", /* Sara611 - logging*/ username: loggedUser, organization : organization, loggedrole:loggedRole, token : sessionToken, no_columns: ["position","status1","delete","map"]};
			  gb_delegateDelete = true;
			}
			else
			{
			  mydata = {action: "get_subset_event_value", /* Sara611 - logging*/ username: loggedUser, organization : organization, loggedrole:loggedRole, token : sessionToken, select : selected, no_columns: ["position","status1","delete","map"]};
			}
            
		}
		 console.log(JSON.stringify(mydata));
			 
       
	  
        dataTable = $('#devicesTable').DataTable({
		"processing" : true,
		"serverSide" : true,
		"responsive": {
        details: false
		},
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
			"name": 		  "position",
			"orderable":      false,
			"content":           null,
			"defaultContent": "",
			"render": function () {
					 return '<i class="fa fa-plus-square" aria-hidden="true"></i>';
				 },
			width:"15px"
            }, 	
			{"name": "v.device", "data": function ( row, type, val, meta ) {
			
				return row.device;
				} },			
			{"name": "v.value_type", "data": function ( row, type, val, meta ) {
				  return row.value_type;
				} },	
			{"name": "d.devicetype", "data": function ( row, type, val, meta ) {
			
				  return row.devicetype;
				} },
            {"name": "d.visibility", "data": function ( row, type, val, meta ) {
			
				  //return row.visibility;
				  				  
				if (row.visibility=='MyOwnPrivate'){   
					return '<button type="button"  class=\"myOwnPrivateBtn\" onclick="changeVisibility(\''+ row.id + '\',\''+ row.cb + '\',\''+ row.value_name + '\',\''+ row.visibility + '\',\''+ row.uri + '\',\''+ row.k1 + '\',\''+ row.k2 +'\',\''+ row.model +'\')">' + row.visibility + '</button>';																				
					} 
				else if (row.visibility=='MyOwnPublic'){
					return '<button type="button"  class=\"myOwnPublicBtn\" onclick="changeVisibility(\''+ row.id + '\',\''+ row.cb + '\',\''+ row.value_name + '\',\''+ row.visibility + '\',\''+ row.uri + '\',\''+ row.k1 + '\',\''+ row.k2 +'\',\''+ row.model +'\')">' + row.visibility + '</button>';
					}
				/*else if (row.visibility=='public') 
				{
					return '<button type="button"  class=\"publicBtn\" >' + row.visibility + '</button>';
					}
				else // value is private
				{
				  return "<div class=\"delegatedBtn\">"+ row.visibility + "</div>";								  
					}*/
					
				} },
			
			{"name": "status1", "data": function ( row, type, val, meta ) {
			
				  return row.status1;
				} },	

			{
                data: null,
				"name": "delete",
				"orderable":      false,
                className: "center",
                //defaultContent: '<button type="button" id="delete" class="delDashBtn delete">Delete</button>'
				render: function(d) {
				return '<button type="button" class="delDashBtn" ' +
				'data-device="'+d.device+'" ' +
				'data-cb="'+d.cb+'" ' +
				'data-editable="'+d.editable+'" ' +
				'data-value_name="'+d.value_name+'">Delete</button>';
				}
            },
			{
                data: null,
				"name": "map",
				"orderable":      false,
                className: "center",
                //defaultContent: '<button type="button" id="map" class="delDashBtn delete">Location</button>'
				render: function(d) {
				return '<div class="addMapBtn"><i  data-toggle="modal" data-target="#addMapShow" onclick="drawMap(\''+ d.latitude + '\',\'' + d.longitude + '\', \'' + d.id + '\', \'' + d.devicetype + '\', \'' + d.kind + '\', \'' + 'addDeviceMapModalBodyShow' + '\')\" class="fa fa-globe"  style=\"font-size:36px; color: #0000ff\"></i></div>';
				}
            }
        ],  
    "order" : [] 
	  
   });
      
	if (gb_delegateDelete) {   
		console.log ("gb_delegateDelete" + true)	
		dataTable.columns( [4,6] ).visible( false );
		gb_delegateDelete = false;	
		
	} 
 
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
        
        $(window).resize(function(){
            $('#mainContentCnt').height($('#mainMenuCnt').height() - $('#headerTitleCnt').height());
            if($(window).width() < 992)
            {
                //$('#devicesTable').bootstrapTable('hideColumn', 'device');
               // $('#devicesTable').bootstrapTable('hideColumn', 'value_type');
                //$('#devicesTable').bootstrapTable('hideColumn', 'uri');
               // $('#devicesTable').bootstrapTable('hideColumn', 'status1');
                // $('#devicesTable').bootstrapTable('hideColumn', 'format');
                //$('#devicesTable').bootstrapTable('hideColumn', 'type');
            
            }
            else
            {
               // $('#devicesTable').bootstrapTable('showColumn', 'device');
               // $('#devicesTable').bootstrapTable('showColumn', 'value_type');
                //$('#devicesTable').bootstrapTable('showColumn', 'uri');
              //  $('#devicesTable').bootstrapTable('showColumn', 'status1');
                // $('#devicesTable').bootstrapTable('showColumn', 'format');
                //$('#devicesTable').bootstrapTable('showColumn', 'type');
           
            }
        });
		
		
		
	$("#addMyNewDeviceRow").hide();
		
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
		
		
	$('#devicesLink .mainMenuItemCnt').addClass("mainMenuItemCntActive");
	$('#mobMainMenuPortraitCnt #devicesLink .mobMainMenuItemCnt').addClass("mainMenuItemCntActive");
	$('#mobMainMenuLandCnt #devicesLink .mobMainMenuItemCnt').addClass("mainMenuItemCntActive");
	
        
	// buildMainTable(false);

	$("#addMyNewDevice").click(function() {
        
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
                        
                        var $dropdown = $("#selectModel");        
                        $dropdown.empty();   
                        $.each(data['content_model'], function(){ 
                            var opt= "<option data_key="+this.kgenerator+" value='"+this.name+"'>"+this.name+"</option>";
                            $dropdown.append(opt);        
                            //$dropdown.append($("<option />").val(this.name).text(this.name));        
                        });
                        
                            console.log("add new device heree");	
                            $("#displayAllDeviceRow").hide();
                            gb_delegated = false;
                            $("#addMyNewDeviceRow").show();
                            $('#inputNameDeviceUser').val("");
                            $('#inputTypeDeviceUser').val("");
                            $('#inputLatitudeDeviceUser').val("");
                            $('#inputLongitudeDeviceUser').val("");
                            showAddMyDeviceModal();
                            drawMapUser(43.78, 11.23);
				
                        }
                    else{
                        console.log("error getting the models "+data); 
                    }
                },
                error: function (data)
                {
                 console.log("error in the call to get the models "+data);   
                }
          });
			   
	});
		
		
	$("#allDevice").click(function() {
		$("#displayAllDeviceRow").show();
		gb_delegated=false;
		// $("#addDeviceModal").modal('show');
		$("#addMyNewDeviceRow").hide();
		//buildMainTable(true);
		fetch_data(true);
					
	});
	
	
	$("#delegatedDevice").click(function() {
		$("#displayAllDeviceRow").show();
		gb_delegated = true;
		// $("#addDeviceModal").modal('show');
		$("#addMyNewDeviceRow").hide();
		//buildMainTable(true,'delegated',null);
		fetch_data(true, 'delegated',null);
	});

	
	$("#myDevice").click(function() {	
		$("#displayAllDeviceRow").show();
		// $("#addDeviceModal").modal('show');
		$("#addMyNewDeviceRow").hide();
		gb_delegated=false;
		//buildMainTable(true);
		fetch_data(true);
	});
	

//Related to Edit Button - need to be updated

	$('#devicesTable tbody button.editDashBtn').off('hover');
	$('#devicesTable tbody button.editDashBtn').hover(function(){
		$(this).css('background', '#ffcc00');
		$(this).parents('tr').find('td').eq(1).css('background', '#ffcc00');
	}, 
	function(){
		$(this).css('background', 'rgb(69, 183, 175)');
		$(this).parents('tr').find('td').eq(1).css('background', $(this).parents('td').css('background'));
	});


	$('#devicesTable tbody').on('click', 'button.editDashBtn', function () {
		// $("#editDeviceModalUpdating").hide();
		//******Edit Control function call
		
		
		$("#editDeviceModalBody").show();
		$('#editDeviceModalTabs').show();


		  $("#editDeviceLoadingMsg").hide();
		  $("#editDeviceLoadingIcon").hide();
		  $("#editDeviceOkMsg").hide();
		  $("#editDeviceOkIcon").hide();
		  $("#editDeviceKoMsg").hide();
		  $("#editDeviceKoIcon").hide(); 
		  $("#editDeviceModalFooter").show();
		  $("#editDeviceModal").modal('show');
		  $("#editDeviceModalLabel").html("Edit device - " + $(this).parents('tr').attr("data-id"));
		  
		   
		  var id = $(this).parents('tr').attr('data-id');
		  var contextbroker = $(this).parents('tr').attr('data-contextBroker');
		  var type = $(this).parents('tr').attr('data-devicetype');
		  var kind =  $(this).parents('tr').attr('data-kind');
		  var uri =   $(this).parents('tr').attr('data-uri');
		  var protocol = $(this).parents('tr').attr('data-protocol');
		  var format = $(this).parents('tr').attr('data-format');
		  var macaddress = $(this).parents('tr').attr('data-macaddress');
		  var model = $(this).parents('tr').attr('data-model');
		  var producer = $(this).parents('tr').attr('data-producer');
		  var edgegateway_type=$(this).parents('tr').attr('data-edgegateway_type');
		  var latitude = $(this).parents('tr').attr('data-latitude');
		  var longitude = $(this).parents('tr').attr('data-longitude');
		  var owner = $(this).parents('tr').attr('data-owner');
		  var frequency = $(this).parents('tr').attr('data-frequency');
		  var visibility = $(this).parents('tr').attr('data-visibility');
		  
		  console.log(id);
		  console.log(contextbroker);
		
		$('#inputNameDeviceM').val(id);
		$('#selectContextBrokerM').val(contextbroker);
		$('#inputTypeDeviceM').val(type);
		$('#selectKindDeviceM').val(kind);
		$('#inputUriDeviceM').val(uri);
		$('#selectProtocolDeviceM').val(protocol);
		$('#selectFormatDeviceM').val(format);
		$('#createdDateDeviceM').val($(this).parents('tr').attr('data-created'));
		$('#inputMacDeviceM').val(macaddress);
		$('#inputModelDeviceM').val(model);
		$('#inputProducerDeviceM').val(producer);
		$('#selectEdgeGatewayTypeM').val(edgegateway_type);
		
		$('#inputLatitudeDeviceM').val(latitude);															  
		$('#inputLongitudeDeviceM').val(longitude);	
		$('#inputOwnerDeviceM').val(owner);	
		$('#inputFrequencyDeviceM').val(frequency);
		$('#selectVisibilityDeviceM').val(visibility);
		  
	//var x = checkStatus(id, type, contextbroker, kind, protocol, format,  macaddress, model, producer, latitude, longitude, visibility, owner, frequency);
	//console.log(x);
	//$('#inputPropertiesDeviceM').val(x) ;

	//$('#inputPropertiesDeviceM').val($(this).parents('tr').attr('data-properties'));

	//	$('#inputAttri:butesDeviceM').val($(this).parents('tr').attr('data-attributes'));
	showEditDeviceModal();

	console.log("editing?");

	$.ajax({
		url: "../api/device.php",
		 data: {
			  action: "get_device_attributes", 
			   id: $(this).parents('tr').attr("data-id"),
			   organization : organization,
 			token : sessionToken,
			   contextbroker: $(this).parents('tr').attr("data-contextBroker")
			  },
		type: "POST",
		async: true,
		dataType: 'json',
		success: function (mydata) 
		{
		  var row = null;
		  $("#editUserPoolsTable tbody").empty();
		  myattributes=mydata['content'];
		  content="";
		  k=0;
		  while (k < myattributes.length)
		  {
			// console.log(k); 
			content += drawAttributeMenu(myattributes[k].value_name, 
				 myattributes[k].data_type, myattributes[k].value_type, myattributes[k].editable, myattributes[k].value_unit, myattributes[k].healthiness_criteria, 
				 myattributes[k].healthiness_value, 'editlistAttributes');
			k++;
		  }
		  $('#editlistAttributes').html(content);
		 },
		 error: function (data)
			{
			   console.log("Get values pool KO");
			   console.log(JSON.stringify(data));
			   alert("Network errors. <br/> Get in touch with the Snap4City Administrator<br/>"+ JSON.stringify(data));
			}
		});
	});


	
	
//End of Edit Button 	



//Related to Delete button  - need to be updated

	$('#devicesTable button.delDashBtn').off('hover');
	$('#devicesTable button.delDashBtn').hover(function(){
		$(this).css('background', '#ffcc00');
		$(this).parents('tr').find('td').eq(1).css('background', '#ffcc00');
	}, 
	function(){
		$(this).css('background', '#e37777');
		$(this).parents('tr').find('td').eq(1).css('background', $(this).parents('td').css('background'));
	});


		
	$('#devicesTable tbody').on('click', 'button.delDashBtn', function () {
				console.log($(this));
				console.log("Deleted ID" + $(this).attr('data-device'));

		var device = $(this).attr('data-device');
		var dev_organization = $(this).attr('data-organization');
		var contextbroker = $(this).attr('data-cb');
		var value_name = $(this).attr("data-value_name");
		var editable = $(this).attr("data-editable"); 
		
		$("#deleteDeviceModal div.modal-body").html('<div class="modalBodyInnerDiv"><span data-device = "' + device + '" data-editable = "' + editable + '" data-cb = "' + contextbroker + '" data-organization = "' + dev_organization+ '"  data-value_name ="' + value_name +'">Do you want to confirm deletion of device <b>' + device + '</b>?</span></div>');
        $("#deleteDeviceModalInnerDiv1").html('<h5>Device deletion in progress, please wait</h5>');
        $("#deleteDeviceModalInnerDiv2").html('<i class="fa fa-circle-o-notch fa-spin" style="font-size:36px"></i>');
        $("#deleteDeviceModalInnerDiv1").hide();
        $("#deleteDeviceModalInnerDiv2").hide();
        $("#deleteDeviceOkBtn").hide();
        $("#deleteDeviceCancelBtn").show();
        $("#deleteDeviceConfirmBtn").show();
        $("#deleteDeviceModal").modal('show');
	});
                            

//End of Delete Button 		
	
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

	
	
	 $('#displayDevicesMap').off('click');
	 $('#displayDevicesMap').click(function(){
		 if (gb_delegated)
			  myaction = "get_all_delegated_event_value_map";   //		  myaction = {action: "get_all_delegated_event_value", token : sessionToken, no_columns: ["position","status1","delete","map"], columns: ["v.device", "v.value_type", "d.devicetype"], ch: "allVal" };
		 else 
			  myaction = "get_all_private_event_value_map";  	//         myaction = {action: "get_all_private_event_value", token : sessionToken, no_columns: ["position","status1","delete","map"], columns: ["v.device", "v.value_type", "d.devicetype"], ch: "allVal"  };  	

		$.ajax({
			url: "../api/value.php",
			data: {
			action: myaction,
			token : sessionToken,
			organization : organization, 
			/*Sara611 -logging*/
			username: loggedUser,
            loggedrole:loggedRole
			},
			type: "POST",
			async: true,
			datatype: 'json',
			success: function (data) 
			 {
				
				 if(data["status"] === 'ko')
					{
							alert("An error occured when reading the data. <br/> Get in touch with the Snap4City Administrator<br/>"+ data["msg"]);
							//$("#modelInsertModalInnerDiv1").html(data["msg"]);
							//$("#modelInsertModalInnerDiv2").html('<i class="fa fa-frown-o" style="font-size:42px"></i>');
						  //data = data["content"];
						  
					}

				 else (data["status"] === 'ok')
					{       
						
						var dataloc = data["content"];
						
						if (gb_delegated){
                            $("#addMap2").modal('show');
							drawMapAll_delegated(dataloc, 'searchDeviceMapModalBody_d');
                        }
                        else{
                            $("#addMap1").modal('show');
                            drawMapAll(dataloc, 'searchDeviceMapModalBody');   
                        }
                        
						}
			 },
			 error: function (data) 
			 {
				 console.log("Ko result: " + data);
				  alert("Network errors. <br/> Get in touch with the Snap4City Administrator<br/>"+ JSON.stringify(data));
			 }
			
		});		
	
	
	});
									


	$("#addMyNewDeviceConfirmBtn").off("click");
	$("#addMyNewDeviceConfirmBtn").click(function(){	
		 $("#registerDeviceModal").show();	                  
			
		 var nameOpt =  document.getElementById('selectModel').options;
		 var selectednameOpt = document.getElementById('selectModel').selectedIndex;
		 gb_device =  document.getElementById('inputNameDeviceUser').value;
		 
		 addMyDeviceConditionsArray['inputNameDeviceUser'] = true;
		 checkDeviceNameUser(); checkAddMyDeviceConditions(); 
		  
		 gb_latitude =  document.getElementById('inputLatitudeDeviceUser').value;
		 gb_longitude =  document.getElementById('inputLongitudeDeviceUser').value;
		 gb_k1 =  document.getElementById('KeyOneDeviceUser').value;
		 gb_k2 =  document.getElementById('KeyTwoDeviceUser').value;

		 // $("#addDeviceModal #inputModelDevice").val(nameOpt[selectednameOpt].value);
		 
		 console.log(nameOpt[selectednameOpt].value + " " + gb_device + " " + gb_longitude + " " + gb_latitude);
		 

		 // $("#addDeviceModal #inputNameDevice").val(device);
		 // $("#addDeviceModal #inputLatitudeDevice").val(latitude);
		 // $("#addDeviceModal #inputLongitudeDevice").val(longitude);
		 
		$.ajax({
			url: "../api/model.php",
			data: {
			action: "get_model",
			organization : organization, 
			name: nameOpt[selectednameOpt].value 
			},
			type: "POST",
			async: true,
			datatype: 'json',
			success: function (data) 
			 {
				
				 if(data["status"] === 'ko')
					{
						 alert("An error occured when reading the data. <br/> Get in touch with the Snap4City Administrator<br/>"+ data["msg"]);
						//$("#modelInsertModalInnerDiv1").html(data["msg"]);
						 //$("#modelInsertModalInnerDiv2").html('<i class="fa fa-frown-o" style="font-size:42px"></i>');
						  //data = data["content"];
					}

				 else (data["status"] === 'ok')
					{
				
						
						console.log(data);
						console.log(gb_latitude);
						console.log (data.content.kind);
						console.log(data.content.attributes);
						
						var model = data.content.name;
						var type = data.content.devicetype;
						var kind = data.content.kind;
						var producer = data.content.producer;
						//var mac = data.content.mac;
						var frequency = data.content.frequency;
						var contextbroker = data.content.contextbroker;
						var protocol = data.content.protocol;
						var format = data.content.format;
						var attrJSON = data.content.attributes;
						var edgegateway_type=data.content.edgegateway_type;	
						/* 
						$('#inputTypeDevice').val(data.content.devicetype);
						$('#selectKindDevice').val(data.content.kind);
						$('#inputProducerDevice').val(data.content.producer);
						$('#inputFrequencyDevice').val(data.content.frequency);
						
						$('#selectContextBroker').val(data.content.contextbroker);
						$('#selectProtocolDevice').val(data.content.protocol);
						$('#selectFormatDevice').val(data.content.format); 
						*/
						
					
				 $.ajax({
					 url: "../api/device.php",
					 data:{
						  action: "insert",   
						  attributes: attrJSON,
						  id: gb_device,
						  type: type,
						  organization : organization, 
						  kind: kind,
						  latitude: gb_latitude,
						  longitude: gb_longitude,
						 
						  /*Sara711 - logging*/
						  username: loggedUser, 
						 
						  mac: "",
						  model: model,
						  producer: producer,
						  visibility: "private",
						  frequency: frequency,
						  contextbroker : contextbroker,
						  protocol : protocol,
						  format : format,
						  token : sessionToken,
						  k1 :  gb_k1,
						  k2 : gb_k2,
						  edgegateway_type:edgegateway_type

						},
						 type: "POST",
						 async: true,
						 dataType: "json",
						 timeout: 0,
						 success: function (data) 
						 {
						//	 console.log(mydata);
						//   res= JSON.parse(mydata); 
                             $('#inputNameDeviceUser').val("");
								$('#inputLatitudeDeviceUser').val("");																		 
								$('#inputLongitudeDeviceUser').val("");
								$('#inputLatitudeDevice').val("");
								$('#KeyOneDeviceUser').val("");
								$('#KeyTwoDeviceUser').val("");
				
                             if(data["status"] === 'ko')
							{
								
								console.log("Error adding Device type");
								console.log(data);
								

                                $("#addDeviceKoModal").modal('show');
								$("#registerDeviceModal").hide();
								$("#addDeviceOkModal").hide();
								$("#addDeviceKoModalInnerDiv1").html('<h5>The following error occurred: ' + data["error_msg"]+ '</h5>');
								   
						 
							}			 
							else if (data["status"] === 'ok')
							{
								console.log("Added Device");
							
									$('#devicesTable').DataTable().destroy();
									fetch_data(true);
									 $("#addDeviceOkModalInnerDiv1").html('<h5>The device has been successfully registered. <p>You can find further information on how to use and set up your device at the following page:<p>https://www.snap4city.org/drupal/node/76</h5>');
									 $("#addDeviceOkModal").modal('show');
									 $("#registerDeviceModal").hide();
									
							} 
							 
						 },
						 error: function (data)
												{
							   console.log("Error insert device");  
							   console.log("Error status -- Ko result: " + JSON.stringify(data));
							   alert("Network errors. <br/> Get in touch with the Snap4City Administrator<br/>"+ JSON.stringify(data));
						  
							 
						 } 
					 });

						
						
					}
				 },
				 error: function (data) 
				 {
					 console.log("Ko result: " + JSON.stringify(data));
					 alert("Network errors. <br/> Get in touch with the Snap4City Administrator<br/>"+ JSON.stringify(data));
				 }
				
			});			
	/* 	

	  $("#addDeviceModal").modal('show');
	 // console.log(name);
	 // $("#addDeviceModalBody").modal('show');
	  $("#addDeviceLoadingMsg").hide();
	  $("#addDeviceLoadingIcon").hide();
	  $("#addDeviceOkMsg").hide();
	  $("#addDeviceOkIcon").hide();
	  $("#addDeviceKoMsg").hide();
	  $("#addDeviceKoIcon").hide();
	   */
	   

   });
	
	
// This is loading validation when the cursor is on 
															   
	$("#addDeviceBtn").off("click");
	$("#addDeviceBtn").click(function(){	
			// console.log(admin);
		  $("#addDeviceModal").modal('show');
		 // $("#addDeviceModalBody").modal('show');
		  $("#addDeviceLoadingMsg").hide();
		  $("#addDeviceLoadingIcon").hide();
		  $("#addDeviceOkMsg").hide();
		  $("#addDeviceOkIcon").hide();
		  $("#addDeviceKoMsg").hide();
		  $("#addDeviceKoIcon").hide();
		  
		  // showAddMyDeviceModal();
	});
								
	
	
	

// add lines related to attributes USER 			
	$("#addAttrBtnUser").off("click");
	$("#addAttrBtnUser").click(function(){
	   console.log("#addAttrBtnUser");							   
	   content = drawAttributeMenuUser("","", "",  'addlistAttributesUser');
	   $('#addlistAttributesUser').append(content);
	});	
	
	$("#attrNameDelbtnUser").off("click");
	$("#attrNameDelbtnUser").on("click", function(){
		console.log("#attrNameDelbtnUser");	
		$(this).parent('tr').remove();
	});	

		
	$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
		var target = $(e.target).attr("href");
		if ((target == '#addGeoPositionTabDevice')) {
			console.log("Elf: Add Device Map");
			var latitude = 43.78; 
			var longitude = 11.23;
			var flag = 0;
			drawMap1(latitude,longitude, flag, 'addLatLong');
		} else {//nothing
		}
	});


	$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
		var target = $(e.target).attr("href");
		if ((target == '#editGeoPositionTabDevice')) {
			console.log("Elf : EditDeviceMap");
				var latitude = $("#inputLatitudeDeviceM").val(); 
				var longitude = $("#inputLongitudeDeviceM").val();
				var flag = 1;
			drawMap1(latitude,longitude, flag, 'editLatLong');
		} else {//nothing
		}
	});
		

	$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
	var target = $(e.target).attr("href");
	if ((target == '#editStatusTabDevice')) {
		
		var id = document.getElementById('inputNameDeviceM').value;
		var contextbroker = document.getElementById('selectContextBrokerM').value;
		var type = document.getElementById('inputTypeDeviceM').value;
		var kind = document.getElementById('selectKindDeviceM').value;
		var latitude = document.getElementById('inputLatitudeDeviceM').value;
		var longitude = document.getElementById('inputLongitudeDeviceM').value;
		var protocol = document.getElementById('selectProtocolDeviceM').value;
		
			   if (id==null || id=="") { var idNote = ("\n id not specified");} else{idNote = "&#10004;";}
			   if (contextbroker==null || contextbroker=="") {var contextbrokerNote = ("cb not specified");} else{contextbrokerNote = "&#10004;";}
			   if (type==null || type=="") {var typeNote = ("type not specified");} else{typeNote = "&#10004;";}
			   if (!(kind=="sensor" || kind=="actuator")) {var kindNote = ("\n kind not specified");}  else{kindNote = "&#10004;";}
			   if ((latitude < -90 && latitude > 90) || (latitude=="" || latitude==null)) {var latitudeNote = ("\n latitude not correct ");} else{latitudeNote = "&#10004;";}
			   if ((longitude < -180 && longitude > 180) || (longitude=="" || longitude==null)) {var longitudeNote = ("\n longitude not correct ");} else{longitudeNote = "&#10004;";}
			   if (!(protocol=="ngsi" || protocol=="mqtt" || protocol=="amqp")) {var protocolNote = ("protocol not correct ");} else{protocolNote = "&#10004;";}
		
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
		

// DELETE DEVICE (DELETE FROM DB) 			
			
	$('#deleteDeviceConfirmBtn').off("click");
	$("#deleteDeviceConfirmBtn").click(function(){
		
		var device = $("#deleteDeviceModal span").attr("data-device");
		var contextbroker = $("#deleteDeviceModal span").attr("data-cb");
		var value_name = $("#deleteDeviceModal span").attr("data-value_name");
		var editable = $("#deleteDeviceModal span").attr("data-editable");
	   var dev_organization  = $("#deleteDeviceModal span").attr("data-organization");
        
        $("#deleteDeviceModal div.modal-body").html("");
		$("#deleteDeviceOkBtn").hide();
        $("#deleteDeviceCancelBtn").hide();
		$("#deleteDeviceConfirmBtn").hide();
		$("#deleteDeviceModalInnerDiv1").show();
		$("#deleteDeviceModalInnerDiv2").show();
		

		
		$.ajax({
			url: "../api/value.php",
			data:{
				action: "delete",
				device: device,
				//uri : uri, 
				/*Sara711 - logging*/
				username: loggedUser,
				organization : organization, 
				dev_organization : dev_organization, 
				value_name: value_name,	
				contextbroker : contextbroker,
				editable : editable,
				token : sessionToken
				},
			type: "POST",
			datatype: "json",
			async: true,
			success: function (data) 
			{
				$("#deleteDeviceOkBtn").show();
                console.log(JSON.stringify(data));
				if(data["status"] === 'ko')
				{
					$("#deleteDeviceModalInnerDiv1").html(data["error_msg"]);
					$("#deleteDeviceModalInnerDiv2").html('<i class="fa fa-frown-o" style="font-size:42px"></i>');
				}
				else if(data["status"] === 'ok')
				{
					$("#deleteDeviceModalInnerDiv1").html('Device &nbsp; <b>' + device + '</b> &nbsp;deleted successfully');
					$("#deleteDeviceModalInnerDiv1").show();
                    $("#deleteDeviceModalInnerDiv2").html('<i class="fa fa-check" style="font-size:42px"></i>');
					
					 
					$('#dashboardTotNumberCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotNumberCnt .pageSingleDataCnt').html()) - 1);
					if (data["active"])
						$('#dashboardTotActiveCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotActiveCnt .pageSingleDataCnt').html()) - 1);
					if (data["visibility"]=="public")          
						   $('#dashboardTotPermCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotPermCnt .pageSingleDataCnt').html()) - 1);
					else
						  $('#dashboardTotPrivateCn .pageSingleDataCnt').html(parseInt($('#dashboardTotPrivateCn .pageSingleDataCnt').html()) - 1);
                    
                        $.ajax({
                            url: "../api/value.php",
                            data:{
                                action: "check_if_last_value",
                                device: device,
                                username: loggedUser,
                                organization : organization, 
                                dev_organization : dev_organization, 	
                                contextbroker : contextbroker,
                                token : sessionToken
                                },
                            type: "POST",
                            datatype: "json",
                            async: true,
                            success: function (data) 
                            {
                                    if(data["status"] === 'ok'){           
                                        if (data["content"]==0)  {      
                                                $.ajax({
                                                        url: "../api/device.php",
                                                        data:{
                                                            action: "delete",
                                                            username: loggedUser,	
                                                            organization : organization, 
                                                            dev_organization : dev_organization, 
                                                            id: device, 
                                                            uri : "", //it is not used in the api/php but I kept it just for consistensy
                                                            contextbroker : contextbroker,
                                                            token : sessionToken
                                                            },
                                                        type: "POST",
                                                        datatype: "json",
                                                        async: true,
                                                        success: function (data) 
                                                        {
                                                            console.log(JSON.stringify(data));
                                                            if(data["status"] === 'ko')
                                                            {
                                                               console.log("error deleting the device, eventhough it was the last value deleted "); 

                                                            }
                                                            else if(data["status"] === 'ok')
                                                            {
                                                               console.log("Success to delete the device itself"); 
                                                            }
                                                        },
                                                        error: function (data) 
                                                        {
                                                            console.log("error in the call for deleting the device, eventhough it was the last value deleted "); 

                                                        }
                                                    });
                                        }
                                        else{
                                            console.log("still other values exist for the same device");
                                        }
                                    }
                                else{
                                     console.log("ko error checking if other values exist for the same device");
                                }
                            },
                            error: function(data)
                            {
                                console.log("error checking if other values exist for the same device ");
                            }
                        });
                    
                    $('#devicesTable').DataTable().destroy();
				    fetch_data(true);


					// $('#dashboardTotNumberCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotNumberCnt .pageSingleDataCnt').html()) - 1);
					// $('#dashboardTotActiveCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotActiveCnt .pageSingleDataCnt').html()) - 1);
					
					/*setTimeout(function()
					{
						//buildMainTable(true);
						$('#devicesTable').DataTable().destroy();
						fetch_data(true);
						$("#deleteDeviceModal").modal('hide');
						
						setTimeout(function(){
							$("#deleteDeviceCancelBtn").show();
							$("#deleteDeviceConfirmBtn").show();
						}, 500);
					}, 2000);*/
				}
			},
			error: function (data) 
			{
				$("#deleteDeviceOkBtn").show();
                console.log(JSON.stringify(data));
				$("#deleteDeviceModalInnerDiv1").html(data["error_msg"]);
				$("#deleteDeviceModalInnerDiv2").html('<i class="fa fa-frown-o" style="font-size:42px"></i>');
                
			}
		});
	});
        
        $("#deleteDeviceOkBtn").off("click");
        $("#deleteDeviceOkBtn").click(function(){
           $("#deleteDeviceModal div.modal-body").html("Do you want to confirm deletion of the following device?");
            $("#deleteDeviceOkBtn").hide();
            $("#deleteDeviceCancelBtn").show();
            $("#deleteDeviceConfirmBtn").show();
            $("#deleteDeviceModalInnerDiv1").html('<h5>Device deletion in progress, please wait</h5>');
            $("#deleteDeviceModalInnerDiv2").html('<i class="fa fa-circle-o-notch fa-spin" style="font-size:36px"></i>');
            $("#deleteDeviceModalInnerDiv1").hide();
            $("#deleteDeviceModalInnerDiv2").hide();
        });    


        $("#KeyOneDeviceUser").val(generateUUID());
        $("#KeyTwoDeviceUser").val(generateUUID());	
        addMyDeviceConditionsArray['KeyOneDeviceUser'] = true;
        addMyDeviceConditionsArray['KeyTwoDeviceUser'] = true;
        $("#addNewDeviceGenerateKeyBtn").hide();
        
	 $("#KeyOneDeviceUser").change(function() {
		 addMyDeviceConditionsArray['KeyOneDeviceUser'] = true;
		 addMyDeviceConditionsArray['KeyTwoDeviceUser'] = true;
		 checkAddMyDeviceConditions();
		 UserKey(); 
		 });
	 $("#KeyTwoDeviceUser").change(function() {
		 addMyDeviceConditionsArray['KeyOneDeviceUser'] = true;
		 addMyDeviceConditionsArray['KeyTwoDeviceUser'] = true;
		 checkAddMyDeviceConditions();
		 UserKey();
		 });
        

		
		
	$("#selectModel").on('click', function() {

		var nameOpt =  document.getElementById('selectModel').options;
		var selectednameOpt = document.getElementById('selectModel').selectedIndex;
		$("#addNewDeviceGenerateKeyBtn").hide();
		checkModel();
		checkAddMyDeviceConditions();
		
		if (nameOpt[selectednameOpt].value =="")
		{
		  // nothing is selected
		  $("#sigFoxDeviceUserMsg").val("");
		  $("#KeyOneDeviceUserMsg").html("");
		  $("#KeyTwoDeviceUserMsg").html("");	
		  $("#KeyOneDeviceUser").val("");
		  $("#KeyTwoDeviceUser").val("");
		  $("#inputLatitudeDeviceUser").val("");
		  $("#inputLongitudeDeviceUser").val("");			  
		  addMyDeviceConditionsArray['KeyOneDeviceUser'] = false;
		  addMyDeviceConditionsArray['KeyTwoDeviceUser'] = false;
		  $("#KeyOneDeviceUser").attr({'disabled': 'disabled'});
		  $("#KeyTwoDeviceUser").attr({'disabled': 'disabled'}); 
		  checkAddMyDeviceConditions();			  
		}
		else 
		{	
		//Fatima6
		 // $("#KeyOneDeviceUser").removeAttr('disabled');
		 // $("#KeyOneDeviceUser").removeAttr('disabled');

		 // if(nameOpt[selectednameOpt].value.indexOf("Raspberry")!=-1 || nameOpt[selectednameOpt].value.indexOf("Arduino")!=-1 || nameOpt[selectednameOpt].value.indexOf("sigfox")!=-1 || nameOpt[selectednameOpt].value.indexOf("Sigfox")!=-1){

			 // $("#addNewDeviceGenerateKeyBtn").hide();
		 // }
		 // else{
			 // $("#addNewDeviceGenerateKeyBtn").show();
		 // }
		//
		
		if (nameOpt[selectednameOpt].getAttribute("data_key")!="special"){
		
			$("#sigFoxDeviceUserMsg").val("");
			$("#KeyOneDeviceUserMsg").html("");
			$("#KeyTwoDeviceUserMsg").html("");
			$("#sigFoxDeviceUserMsg").html("These keys have been generated automatically for your device. Keep track of them. Details on <a href=\"https://www.snap4city.org/drupal/node/76\">info</a>");
		
			$("#KeyOneDeviceUser").val(generateUUID());
			$("#KeyTwoDeviceUser").val(generateUUID());
		
			addMyDeviceConditionsArray['KeyOneDeviceUser'] = true;
			addMyDeviceConditionsArray['KeyTwoDeviceUser'] = true;
			checkAddMyDeviceConditions();
			//Fatima6
			
			$("#KeyOneDeviceUser").attr({'disabled': 'disabled'});
			$("#KeyTwoDeviceUser").attr({'disabled': 'disabled'});
									 
		} else
		{
			$("#sigFoxDeviceUserMsg").html("Generate in your SigFox server the keys and report them here.  Details on <a href=\"https://www.snap4city.org/drupal/node/76\">info</a>");
			$("#KeyOneDeviceUser").val("");
			$("#KeyTwoDeviceUser").val("");
			addMyDeviceConditionsArray['KeyOneDeviceUser'] = true;
			addMyDeviceConditionsArray['KeyTwoDeviceUser'] = true;
			checkAddMyDeviceConditions();
			
			//Fatima
			$("#KeyOneDeviceUser").removeAttr('disabled');
			$("#KeyTwoDeviceUser").removeAttr('disabled');
		}
	
		}	
	});

//Validation of the name of the new owner during typing
	$('#newOwner').on('input',function(e)
	{
		
		if($(this).val().trim() === '')
		{
			$('#newOwnerMsg').css('color', '#f3cf58');
			$('#newOwnerMsg').html('New owner username can\'t be empty');
			$('#newOwnershipConfirmBtn').addClass('disabled');
		}
		else
		{
			//if(($(this).val().trim() === "<?= $_SESSION['loggedUsername'] ?>")&&("<?= $_SESSION['loggedRole'] ?>" !== "RootAdmin"))
			if(($(this).val().trim() === loggedUser)&&(loggedRole !== "RootAdmin"))
				
			{
				$('#newOwnerMsg').css('color', '#f3cf58');
				$('#newOwnerMsg').html('New owner can\'t be you');
				$('#newOwnershipConfirmBtn').addClass('disabled');
			}
			else
			{
				$('#newOwnerMsg').css('color', 'white');
				$('#newOwnerMsg').html('User can be new owner');
				$('#newOwnershipConfirmBtn').removeClass('disabled');
			}
		}
	});  

// DELEGATIONS
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
                               if ((loggedRole=='RootAdmin')||(loggedRole=='ToolAdmin')) {
                                       console.log("adding empty");
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
                                          username: loggedUser,
                                          token : sessionToken
                                          },
                type: "POST",
                async: false,
                success: function (data)
                {
                        if(data["status"] === 'ko')
                        {
                                console.log("Error: "+data);
                               //TODO: manage error
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
	 
	
		 

    
});  // end of ready-state
	
		
        
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
	//Fatima4
    function generateKeysCLicked(){
        var k1= generateUUID();
        var k2= generateUUID();
        $("#KeyOneDeviceUser").val(k1);
        $("#KeyTwoDeviceUser").val(k2);
        showAddDeviceModal();
    }
    
    //Fatima4
    function editGenerateKeysCLicked(){
        var k1= generateUUID();
        var k2= generateUUID();
        $("#KeyOneDeviceUserM").val(k1);
        $("#KeyTwoDeviceUserM").val(k2);
        showEditDeviceModal();
    }
	
 
		
        $("#addNewDeviceCancelBtn").off("click");
        $("#addNewDeviceCancelBtn").on('click', function(){
            
			  $('#inputNameDevice').val("");
			  $('#inputTypeDevice').val("");
			  $('#selectContextBroker').val("");
			  $('#inputUriDevice').val("");
			  $('#selectProtocolDevice').val("");
			  $('#selectFormatDevice').val("");
			  $('#createdDateDevice').val("");
			  $('#inputMacDevice').val("");
			  $('#inputModelDevice').val("");
			  $('#inputProducerDevice').val("");
			  $('#inputLatitudeDevice').val("");
			  $('#inputLongitudeDevice').val("");
			  $('#addDeviceModal').modal('hide'); 
                          $("#KeyOneDeviceUser").val("");
                          $("#KeyTwoDeviceUser").val("");
			  //.hide();
			  location.reload();    								  
			//  $('#addDeviceModalTabs').show();
			//  $('#addDeviceModal div.modalCell').show();
			//  $('#addDeviceModalFooter').show(); 
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
        
  	
	$("#selectContextBroker").change(function() {
	
		var index = document.getElementById("selectContextBroker").selectedIndex;
		var opt = document.getElementById("selectContextBroker").options;
		var valCB= opt[index].getAttribute("my_data");
		// console.log("protocol" + JSON.stringify(valCB));
		
		if(valCB ==='ngsi')
		{
			document.getElementById("selectProtocolDevice").value = 'ngsi';
			document.getElementById("selectFormatDevice").value = 'json';
		} 
		else if(valCB ==='mqtt')
		{
			document.getElementById("selectProtocolDevice").value = 'mqtt';
			document.getElementById("selectFormatDevice").value = 'csv';
		} 
		else if (valCB ==='amqp')
		{
			document.getElementById("selectProtocolDevice").value = 'amqp';
			document.getElementById("selectFormatDevice").value = 'csv';
		} 
		else
		{
			//alert("This is a new contextBroker");
			console.log("an error occurred");
		}
		
		
	});
	
	
		
	
	/* Related to the Map */
						




  function drawMap1(latitude,longitude,flag, divname){	
	var marker;
	if(typeof map1==='undefined' || !map1){
	if (flag ==0){
		
        var centerMapArr= gpsCentreLatLng.split(",",2);
        var centerLat= parseFloat(centerMapArr[0].trim());
        var centerLng= parseFloat(centerMapArr[1].trim());
        map1 = L.map(divname).setView([centerLat,centerLng], zoomLevel);
		L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
			attribution: '&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap</a> contributors'
		}).addTo(map1);
		window.node_input_map = map1;
		
		
		setTimeout(function(){ map1.invalidateSize()}, 400);
		
		//L.marker([latitude,longitude]).addTo(map).bindPopup(latitude + ',' + longitude);
		
			map1.on("click", function (e) {
			
			var lat = e.latlng.lat;
			var lng = e.latlng.lng;
				lat = lat.toFixed(5);
				lng = lng.toFixed(5);
				console.log("Check the format:" + lat + " " + lng);
				
				 document.getElementById('inputLatitudeDevice').value = lat;
				 document.getElementById('inputLongitudeDevice').value = lng;
				  addDeviceConditionsArray['inputLatitudeDevice'] = true;
                  addDeviceConditionsArray['inputLongitudeDevice'] = true;
				 if (marker){
					 map1.removeLayer(marker);
				 }
				 marker = new L.marker([lat,lng]).addTo(map1).bindPopup(lat + ',' + lng);
			
			});
        
		

	} else if (flag==1){
		
		map1 = L.map(divname).setView([latitude,longitude], 10);
		L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
			attribution: '&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap</a> contributors'
		}).addTo(map1);
		window.node_input_map = map1;
		//L.marker([latitude,longitude]).addTo(map).bindPopup("Hi DEVICE");
		
		setTimeout(function(){ map1.invalidateSize()}, 400);
		
		marker = new L.marker([latitude,longitude]).addTo(map1).bindPopup(longitude + ',' + longitude);
	
			map1.on("click", function (e) {
				
				var lat = e.latlng.lat;
				var lng = e.latlng.lng;
				lat = lat.toFixed(5);
				lng = lng.toFixed(5);
				console.log("Check the format:" + lat + " " + lng);
				
				document.getElementById('inputLatitudeDeviceM').value = lat;
				document.getElementById('inputLongitudeDeviceM').value = lng;
				 editDeviceConditionsArray['inputLatitudeDeviceM'] = true;
                 editDeviceConditionsArray['inputLongitudeDeviceM'] = true;
				 if (marker){
					 map1.removeLayer(marker);
				 }
				 marker = new L.marker([lat,lng]).addTo(map1).bindPopup(lat+ ',' + lng);
			
			});
		
	}
    }
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
  	


 function drawMapUser(latitude,longitude){ 
 var marker;
 var centerMapArr= gpsCentreLatLng.split(",",2);
 var centerLat= parseFloat(centerMapArr[0].trim());
 var centerLng= parseFloat(centerMapArr[1].trim());
  
 var map = L.map('addDeviceMapModalBodyUser').setView([centerLat,centerLng], zoomLevel);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
   attribution: '© <a href="https://openstreetmap.org/copyright">OpenStreetMap</a> contributors'
  }).addTo(map);
  window.node_input_map = map;
   
  setTimeout(function(){ map.invalidateSize()}, 400);
 
   map.on("click", function (e) {
   
   var lat = e.latlng.lat;
   var lng = e.latlng.lng;
    lat = lat.toFixed(5);
    lng = lng.toFixed(5);
    console.log("Check the format:" + lat + " " + lng);
    
     document.getElementById('inputLatitudeDeviceUser').value = lat;
     document.getElementById('inputLongitudeDeviceUser').value = lng;
		 addMyDeviceConditionsArray['inputLatitudeDeviceUser'] = true;
		 checkDeviceLatitudeUser(); checkAddMyDeviceConditions(); 
		 addMyDeviceConditionsArray['inputLongitudeDeviceUser'] = true;
		 checkDeviceLongitudeUser(); checkAddMyDeviceConditions(); 
     
     if (marker){
      map.removeLayer(marker);
     }
     marker = new L.marker([lat,lng]).addTo(map).bindPopup(lat + ',' + lng);
   
   });
  
  }




function drawMapAll(data, divName){
    
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
  
        green_markersGroup= undefined;
        marker_selection=[];

                 redIcon = new L.Icon({

                                iconUrl: 'https://www.snap4city.org/iotdirectorytest/markerPrivate.png',
                                iconSize: new L.Point(32, 32),
                                iconAnchor: new L.Point(16, 16),
                                popupAnchor: new L.Point(0, -18)

                            });

                blueIcon = new L.Icon({

                                iconUrl: 'https://www.snap4city.org/iotdirectorytest/markerPublic.png',
                                iconSize: new L.Point(32, 32),
                                iconAnchor: new L.Point(16, 16),
                                popupAnchor: new L.Point(0, -18)

                            });
                greenIcon = new L.Icon({

                                iconUrl: 'https://www.snap4city.org/iotdirectorytest/markerGreen.png',
                                iconSize: new L.Point(32, 32),
                                iconAnchor: new L.Point(16, 16),
                                popupAnchor: new L.Point(0, -18)

                            });

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

             var resultsOut=drawSelection(layer, TYPE,drawnItems, data);
             $('#addMap1').modal('hide');
             //Fatima2-moveAndupdate-1-line
             colorSelectedMarkers(resultsOut, greenIcon);
			 if (gb_delegated)
			       fetch_data(true, 'delegated', JSON.stringify(resultsOut));
             else  fetch_data(true, null, JSON.stringify(resultsOut));
			// console.log(resultsOut);


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
                        var resultsOut=drawSelection(layer, TYPE,drawnItems, data);     
                        //console.log(resultsOut);
                         $('#addMap1').modal('hide');
                        //Fatima2-moveAndupdate-1-line
                            colorSelectedMarkers(resultsOut, greenIcon);
                         if (gb_delegated)
			                  fetch_data(true, 'delegated', JSON.stringify(resultsOut));
                          else  fetch_data(true, null, JSON.stringify(resultsOut));
                        });
		

                });
        
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
                  
               }
            }
            setTimeout(function(){ map_all.invalidateSize()}, 400);
    }
 }


function drawMapAll_delegated(data, divName){
    
    if (typeof map_all_delegated === 'undefined' || !map_all_delegated) {
            //map_all_delegated = L.map(divName).setView([latitude,longitude], 10);
        var centerMapArr= gpsCentreLatLng.split(",",2);
        var centerLat= parseFloat(centerMapArr[0].trim());
        var centerLng= parseFloat(centerMapArr[1].trim());
        map_all_delegated = L.map(divName).setView([centerLat,centerLng], zoomLevel);   
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
           attribution: '&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap</a> contributors'
          }).addTo(map_all_delegated);
           window.node_input_map = map_all_delegated;
  
        green_markersGroup_d= undefined;
        marker_selection_d=[];

                 redIcon = new L.Icon({

                                iconUrl: 'https://www.snap4city.org/iotdirectorytest/markerPrivate.png',
                                iconSize: new L.Point(32, 32),
                                iconAnchor: new L.Point(16, 16),
                                popupAnchor: new L.Point(0, -18)

                            });

                blueIcon = new L.Icon({

                                iconUrl: 'https://www.snap4city.org/iotdirectorytest/markerPublic.png',
                                iconSize: new L.Point(32, 32),
                                iconAnchor: new L.Point(16, 16),
                                popupAnchor: new L.Point(0, -18)

                            });
                greenIcon = new L.Icon({

                                iconUrl: 'https://www.snap4city.org/iotdirectorytest/markerGreen.png',
                                iconSize: new L.Point(32, 32),
                                iconAnchor: new L.Point(16, 16),
                                popupAnchor: new L.Point(0, -18)

                            });

         var mapLayers_d = {};
        drawnItems_d = new L.FeatureGroup();
                map_all_delegated.addLayer(drawnItems_d);

                var editControl_d = new L.Control.Draw({
                    draw: false,
                    edit: {
                        remove: false,
                        featureGroup: drawnItems_d,
                        poly: {
                            allowIntersection: false
                        }
                    }
                });
                map_all_delegated.addControl(editControl_d);

         drawControl_d = new L.Control.Draw({
             remove: false,       
             draw: {
                        position: 'topleft',
                        circlemarker: false,
                        rectangle: false,
                        polygon: {
                            allowIntersection: false,
                            showArea: true
                        }
                    }
                });
                map_all_delegated.addControl(drawControl_d);

          L.control.layers(mapLayers_d, {
                    'drawlayer': drawnItems_d
                }, {
                    collapsed: true
                }).addTo(map_all_delegated);

         map_all_delegated.on(L.Draw.Event.CREATED, function(e) {
                    var fence = e.layer;
                    if (drawnItems_d.hasLayer(fence) == false) {
                        drawnItems_d.addLayer(fence);
                    }

                    drawControl_d.remove();
                    TYPE= e.layerType;
                    layer = e.layer;

             var resultsOut_d=drawSelection(layer, TYPE,drawnItems_d, data);
             $('#addMap2').modal('hide');
             colorSelectedMarkers_delegated(resultsOut_d, greenIcon);
			 fetch_data(true, 'delegated', JSON.stringify(resultsOut_d));

         });

         map_all_delegated.on('draw:edited', function(e) {
                    var fences = e.layers;
                    fences.eachLayer(function(fence) {
                        fence.shape = "geofence";
                        if (drawnItems_d.hasLayer(fence) == false) {
                            drawnItems_d.addLayer(fence);
                        }
                    });
                    drawnItems_d.eachLayer(function(layer) {
                        var resultsOut_d=drawSelection(layer, TYPE,drawnItems_d, data);     
                         $('#addMap2').modal('hide');
                            colorSelectedMarkers_delegated(resultsOut_d, greenIcon);
			                fetch_data(true, 'delegated', JSON.stringify(resultsOut_d));
                        });
                });
        
        L.Control.RemoveAll = L.Control.extend(
                    {
                        options:
                        {
                            position: 'topleft',
                        },
                        onAdd: function (map_all_delegated) {
                            var controlDiv_d = L.DomUtil.create('div', 'leaflet-draw-toolbar leaflet-bar');
                            L.DomEvent
                                .addListener(controlDiv_d, 'click', L.DomEvent.stopPropagation)
                                .addListener(controlDiv_d, 'click', L.DomEvent.preventDefault)
                            .addListener(controlDiv_d, 'click', function () {
                                drawnItems_d.clearLayers();
                                
                                if(typeof green_markersGroup_d!= 'undefined'){
                                     map_all_delegated.removeLayer(green_markersGroup_d);
                                     green_markersGroup_d= undefined;
                                     green_marker_array=[];
                                     marker_selection_d=[];
                                    fetch_data(true);
                                } 
                                
                                 drawControl_d.addTo(map_all_delegated);
                
                            });
							
                            var controlUI_d = L.DomUtil.create('a', 'leaflet-draw-edit-remove', controlDiv_d);
                            controlUI_d.title = 'Delete';
                            controlUI_d.href = '#';
                            return controlDiv_d;
                        }
                        
                      
                    });
                var removeAllControl_d = new L.Control.RemoveAll();
                map_all_delegated.addControl(removeAllControl_d);
        

           for (var i=0; i<data.length; i++) {


            var mylat=data[i].latitude;
            var mylong= data[i].longitude;  
            var myname = data[i].name;
               if(mylat!=null && mylong!=null){

                   if(data[i].visibility=="public"){
                    m = L.marker([mylat,mylong],{icon: blueIcon}).addTo(map_all_delegated).bindPopup(myname);
                   }
                   else{
                    m = L.marker([mylat,mylong], {icon: redIcon}).addTo(map_all_delegated).bindPopup(myname);
                   }     
                  
               }
            }
            setTimeout(function(){ map_all_delegated.invalidateSize()}, 400);
    }
 }

/**********************Fatima2-start*****************************/

    
function colorSelectedMarkers(selections, greenIcon){
                green_marker_array=[];
                console.log("selections are");
                console.log(selections);
                for(var k in selections){

                    lat=Number(selections[k].latitude); 
                    lng=Number(selections[k].longitude);
                    popup= selections[k].id;
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

function colorSelectedMarkers_delegated(selections, greenIcon){
                green_marker_array_d=[];
                console.log("selections are");
                console.log(selections);
                for(var k in selections){

                    lat=Number(selections[k].latitude); 
                    lng=Number(selections[k].longitude);
                    popup= selections[k].id;
                    var  m = L.marker([lat, lng],{icon: greenIcon}).bindPopup(popup);
                    green_marker_array_d.push(m);
                }
                

                    if (typeof green_markersGroup_d != 'undefined') {
                        map_all_delegated.removeLayer(green_markersGroup_d); 
                    }
                    green_markersGroup_d = L.layerGroup(green_marker_array_d);
                    green_markersGroup_d.addTo(map_all_delegated);
                    marker_selection_d=selections;
                
            }
    /**********************Fatima-end******************************/


	function drawSelection(layer, type,drawnItems, data){
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


function changeVisibility(id, contextbroker, valueName, visibility, uri, k1, k2) {
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
								//document.getElementById('newVisibilityPrivateBtn').style.visibility = 'hidden';
								//document.getElementById('newVisibilityPublicBtn').style.visibility = 'show';
					console.log("I am here "+ visibility);			
                    //$('#newVisibilityPrivateBtn').addClass('disabled');
								//$('#newVisibilityPublicBtn').removeClass('disabled');
								
								
								//$("#delegationsModal").modal('show');
								document.getElementById("delegationsCntGroup").style.visibility = 'show';	

				} else // if (visibility=='MyOwnPublic'){
				{
								newVisibility = 'private';
								$('#visID').css('color', '#f3cf58');
								$("#visID").html("Visibility - Public");
								//document.getElementById('newVisibilityPrivateBtn').style.visibility = 'show';
								//document.getElementById('newVisibilityPublicBtn').style.visibility = 'hidden';
								$("#newVisibilityPrivateBtn").show();
								$("#newVisibilityPublicBtn").hide();
								console.log("I am here "+ visibility);
								//$("#delegationsModal").modal('show');
								//???document.getElementById("delegationsCntGroup").style.visibility = 'hidden';
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
                                                                 id: id,
                                                                 contextbroker: contextbroker,
																 organization : organization, 
                                                                 value_name: valueName,
                                                                 visibility :newVisibility,
                                                                 uri : uri,
                                                                 user : loggedUser,
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
                                                                 visibility : visibility,
																 organization : organization, 
                                                                 uri: uri,
                                                                 user : loggedUser,
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
										id: id,
										contextbroker: contextbroker,
										organization : organization, 
										value_name: valueName,
										visibility : visibility,
										uri: uri,
										user : loggedUser,
										// delegated_user: $('#newVisibility').val(), //check the attribute delegated_user
										token : sessionToken,
										// k1: k1,
										// k2: k2,
                                    },
                                    type: "POST",
                                    async: true,
                                    dataType: 'json',
                                    success: function(data)
                                    {
											if (data["status"]=='ok')
											{

											console.log(JSON.stringify(data));
																							delegations = data["delegation"];
											// if (delegations[uri + "/" + valueName])
											//          my_delegation = delegations[uri + "/" + valueName];
											// else if (delegations[id + "/" + valueName])
											//            my_delegation = delegations[id + "/" + valueName];
											// else  my_delegation = delegations[id];
											console.log(uri + "/" + valueName + "---" + JSON.stringify(delegations));
											 $('#delegationsTable tbody').html("");
											$('#delegationsTableGroup tbody').html("");
										for(var i = 0; i < delegations.length; i++)
										{
										if ((delegations[i].userDelegated!=null)&&(delegations[i].userDelegated !=="ANONYMOUS")) {
                                                                               		//console.log("adding user delegation");
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
                                                                id: id,
                                                                contextbroker: contextbroker,
																organization : organization, 
                                                                value_name: valueName,
                                                                visibility : visibility,
                                                                uri: uri,
                                                                // delegated_user: $('#newVisibility').val(), //check the attribute delegated_user
                                                                token : sessionToken,
                                                                // k1: k1,
                                                                // k2: k2,
                                                                user : loggedUser,
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
                                                                console.log("ermoving a row from the table");
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
                                                                                                       console.log("toremove:");
                                                                                                       var rowToRemove = $(this).parents('tr');
                                                                                                       $.ajax({
                                                                                                               url: "../api/value.php",     //check the url
                                                                                                               data:
                                                                                                               {
                                                                                                                       action: "remove_delegation",    // to be specified
                                                                                                                       id: id,
                                                                                                                       contextbroker: contextbroker,
																													   organization : organization, 
                                                                                                                       value_name: valueName,
                                                                                                                       visibility : visibility,
                                                                                                                       uri: uri,
                                                                                                                       token : sessionToken,
                                                                                                                       user : loggedUser,
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
                                            console.log(newDelegation);
                                                //alert("The owner is " + id + " The new owner is " + newDelegation);
                                                newk1 = generateUUID();
                                            newk2 = generateUUID();
                                                $.ajax({
                                                        url: "../api/value.php",       //which api to use
                                                        data:
                                                        {
                                                          action: "delegate_value",
                                                          id: id,
                                                          contextbroker: contextbroker,
														  organization : organization, 
                                                          value_name: valueName,
                                                          visibility :visibility,
                                                          uri : uri,
                                                          user : loggedUser,
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
									                                        });
    //single delegation -end
    
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
                                                                                                       id: id,
                                                                                                       contextbroker: contextbroker,
																									   organization : organization, 
                                                                                                       value_name: valueName,
                                                                                                       visibility :visibility,
                                                                                                       uri : uri,
                                                                                                       user : loggedUser,
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
