var dataTable ="";
var _serviceIP = "..";
// var _serviceIP = "http://159.149.129.184:3001";

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
        var service = _serviceIP + "/stub/"+protocol;
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
    // console.log("entratos"); 
	// console.log("This is inside js " + service);
	xhr.addEventListener("readystatechange", function () {
	  if (this.readyState === 4 && this.status == 200) {
		// console.log(this.responseText);
		var activeStub = JSON.parse(JSON.parse(this.responseText).message);
		console.log(activeStub);
		for (var i=0; i < activeStub.length; i++)
		{
		    var cb=activeStub[i];
			console.log(cb);
			
			
			 //document.getElementById('cb').css('border', '2px solid green');
			 //console.log(document.getElementById('cb').css('border'));
				$("#" + cb).css('border', '2px solid green');
			// 	console.log($("#" + cb).css('border'));
		            $("#" + cb).prop('onclick',null).off('click');

			
		}
	  }
	});
	xhr.open("GET", service);
	// xhr.setRequestHeader("Cache-Control", "no-cache");
	//xhr.send();
	return true;
}



function format ( d ) {
	// `d` is the original data object for the row
  	return '<div class="container-fluid">' +
				'<div class="row">' +
					'<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>IP:</b>' + "  " + d.ip + '</div>' +
					'<div class="clearfix visible-xs"></div>' +
					'<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Port:</b>' + "  " + d.port + '</div>' +								
				'</div>' +
				'<div class="row">' +
					'<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>Latitude:</b>' + "  " + d.latitude + '</div>' +
					'<div class="clearfix visible-xs"></div>' +
					'<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>Longitude:</b>' + "  " + d.longitude + '</div>' +								
				'</div>' +
				'<div class="row">' +
					'<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Login:</b>' + "  " + d.login  + '</div>' +
					'<div class="clearfix visible-xs"></div>' +
					'<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Password:</b>' + "  " + d.password + '</div>' +
				'</div>' +
				'<div class="row">' +
					'<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>SHA:</b>' + "  " + d.sha + '</div>' +
					'<div class="clearfix visible-xs"></div>' +
				'</div>' +
			'</div>' ;
	
}

	
			
	function fetch_data(destroyOld, selected=null)
	{
		if(destroyOld)
		{
			$('#contextBrokerTable').DataTable().clear().destroy();
			tableFirstLoad = true;
		}
	   
		 if (selected==null)
		{
		  mydata = {action: "get_all_contextbroker",token : sessionToken, username: loggedUser, organization : organization, loggedrole:loggedRole,  no_columns: ["position", "owner", "edit","delete"]};
		}
		else
		{
		  mydata = {action: "get_subset_contextbroker",token : sessionToken, username: loggedUser, organization : organization,  loggedrole:loggedRole,select : selected, no_columns: ["position", "owner", "edit","delete"]};
		}
	   
		   
        dataTable = $('#contextBrokerTable').DataTable({
		"processing" : true,
		"serverSide" : true,
		//"responsive" : true,
		"responsive": {
        details: false
		},
		"paging"   : true,
		"ajax" : {
		 url:"../api/contextbroker.php",
		 data: mydata,
		//token : sessionToken,
		 datatype: 'json',
		 type: "POST", 
		//"dataSrc": "";		 
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
			{"name": "name", "data": function ( row, type, val, meta ) {
			
				return row.name;
				} },			
			{"name": "accesslink", "data": function ( row, type, val, meta ) {
				  return row.accesslink;
				} },	
			{"name": "accessport", "data": function ( row, type, val, meta ) {
			
				  return row.accessport;
				} },
			{"name": "protocol", "data": function ( row, type, val, meta ) {
			
				  return row.protocol;
				} },
            {"name": "visibility", "data": function ( row, type, val, meta ) {
			
				  				  
				if (row.visibility=='MyOwnPrivate'){   
					return '<button type="button"  class=\"myOwnPrivateBtn\" onclick="changeVisibility(\''+ row.name + '\',\''+ row.visibility + '\',\''+ row.organization + '\',\''+ row.accesslink + '\')">' + row.visibility + '</button>';																				
					} 
				else if (row.visibility=='MyOwnPublic'){
					return '<button type="button"  class=\"myOwnPublicBtn\" onclick="changeVisibility(\''+ row.name + '\',\''+ row.visibility + '\',\''+ row.organization + '\',\''+ row.accesslink + '\')">' + row.visibility + '</button>';
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
			{"name": "organization", "data": function ( row, type, val, meta ) {
			
				  return row.organization;
				} },
			{"name": "owner", "data": function ( row, type, val, meta ) {
			
				  return row.owner;
				} },
            {"name": "created", "data": function ( row, type, val, meta ) {
			
				  return row.created;
				} },			
								 
			{
                data: null,
				"name": "edit",
				"orderable":      false,
                className: "center",
				render: function(d) {
                //defaultContent: '<button type="button" id="edit" class="editDashBtn data-id="'+ row.name +'"">Edit</button>'
				return '<button type="button" class="editDashBtn" ' +
				'data-name="'+d.name+'" ' +
				'data-organization="'+d.organization+'" ' +
				'data-kind="'+d.kind+'" ' +
				'data-ip="'+d.ip+'" ' +
				'data-protocol="'+d.protocol+'" ' +
				'data-version="'+d.version+'" ' +
				'data-port="'+d.port+'" ' +
				'data-uri="'+d.uri+'" ' +
				'data-created="'+d.created+'" ' +
				'data-visibility="'+d.visibility+'" ' +
				'data-longitude="'+d.longitude+'" ' +
				'data-latitude="'+d.latitude+'" ' +
				'data-login="'+d.login+'" ' +
				'data-password="'+d.password+'" ' +
				'data-accesslink="'+d.accesslink+'" ' +
				'data-accessport="'+d.accessport+'" ' +
				'data-apikey="'+d.apikey+'" ' +
				'data-path="'+d.path+'" ' +
				'data-sha="'+d.sha+'">Edit</button>';	
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
				'data-name="'+d.name+'">Delete</button>';
				}
            }
			/*,
			{
                data: null,
				"name": "stub",
				"orderable":      false,
                className: "center",
                //defaultContent: '<button type="button" id="map" class="delDashBtn delete">Location</button>'
				render: function(d) {
				return '<button type="button" id ="' + d.name +'" class="viewDashBtn">Stub</button>';
				}
            } */
        ],
    "order" : [] 
	  
   });
  
  
	if (loggedRole!='RootAdmin' && loggedRole!='ToolAdmin') {		
	dataTable.columns( [6,7, 9, 10] ).visible( false );		
	} 
    if (loggedRole=='ToolAdmin') {		
	dataTable.columns( [7] ).visible( false );		
	}
  
  }	 

 //end of fetch function 





/*JQuery Started...*/

    $(document).ready(function () 
    {
		
//fetch_data function will load the contextbroker table 	
		fetch_data(false);	
		//aggiornaStub();
//detail control for contextbroker dataTable
	var detailRows = [];
  	
	$('#contextBrokerTable tbody').on('click', 'td.details-control', function () {
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

//end of detail control for contextbroker dataTable 


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
            //$('#sessionExpiringPopup').css("top", parseInt($('body').height() - $('#sessionExpiringPopup').height()) + "px");
            //$('#sessionExpiringPopup').css("left", parseInt($('body').width() - $('#sessionExpiringPopup').width()) + "px");
            
            if($(window).width() < 992)
            {
                //$('#contextBrokerTable').bootstrapTable('hideColumn', 'name');
                //$('#contextBrokerTable').bootstrapTable('hideColumn', 'uri');
				//$('#contextBrokerTable').bootstrapTable('hideColumn', 'accesslink');
                //$('#contextBrokerTable').bootstrapTable('hideColumn', 'accessport');
				//$('#contextBrokerTable').bootstrapTable('hideColumn', 'protocol');
                //$('#contextBrokerTable').bootstrapTable('hideColumn', 'created');
            }
            else
            {
                //$('#contextBrokerTable').bootstrapTable('showColumn', 'name');
                //$('#contextBrokerTable').bootstrapTable('showColumn', 'uri');
				//$('#contextBrokerTable').bootstrapTable('showColumn', 'accesslink');
				//$('#contextBrokerTable').bootstrapTable('showColumn', 'accessport');
                //$('#contextBrokerTable').bootstrapTable('showColumn', 'protocol');
                //$('#contextBrokerTable').bootstrapTable('showColumn', 'created');
            }
        });

	   for (var func =0;func < functionality.length; func++)
		{
		  var element = functionality[func];
		  if (element.view=="view")
		  {
			  if (element[loggedRole]==1)  
			   {   
                                   console.log("yes " + loggedRole + " " + element[loggedRole] + " " + element["class"]); 
				   $(element["class"]).show();
			   }			   
			   else 
			   { 
				 $(element["class"]).hide();
				 // console.log($(element.class));
				 console.log("no " + loggedRole + " " + element[loggedRole] + " " + element["class"]);
			   }
			}   
		}
		
		$('#contextbrokerLink .mainMenuItemCnt').addClass("mainMenuItemCntActive");
        $('#mobMainMenuPortraitCnt #contextbrokerLink .mobMainMenuItemCnt').addClass("mainMenuItemCntActive");
        $('#mobMainMenuLandCnt #contextbrokerLink .mobMainMenuItemCnt').addClass("mainMenuItemCntActive");
    
            
			
			//buildMainTable(false);
			
		/*  ADD CONTEXT BROKER (INSERT INTO DB) */ 	
		
        $('#addContextBrokerConfirmBtn').off("click");
        $("#addContextBrokerConfirmBtn").click(function(){
			
			$('#addContextBrokerModalTabs').hide();
			$('#addContextBrokerModalBody').hide();	
            $('#addContextBrokerModal div.modalCell').hide();
            //$('#addContextBrokerModalFooter').hide();
            $('#addContextBrokerCancelBtn').hide();
            $('#addContextBrokerConfirmBtn').hide();
            $('#addContextBrokerOkBtn').hide();
			$('#addCBOkMsg').hide();
			$('#addCBOkIcon').hide();	
			$('#addCBKoMsg').hide();
			$('#addCBKoIcon').hide();	
			$('#addCBLoadingMsg').show();
            $('#addCBLoadingIcon').show();
            
            var accesslink= $('#inputAccessLinkCB').val();
            if (accesslink==""){
                accesslink=$('#inputIpCB').val();
            }
	
				$.ajax({
                 url: "../api/contextbroker.php",
                 data:{
					action: "insert",
					//Sara2610 - for logging purpose
					username: loggedUser,
                    token : sessionToken,
					organization : organization, 
					name: $('#inputNameCB').val(),
					kind: $('#selectKindCB').val(),
					ip: $('#inputIpCB').val(),
					port:  $('#inputPortCB').val(),
					protocol: $('#selectProtocolCB').val(),
					version: $('#inputVersionCB').val(),
					login: $('#inputLoginCB').val(),
					path: $('#inputPathCB').val(),
					visibility: $('#selectVisibilityCB').val(),
					password: $('#inputPasswordCB').val(),
					latitude: $('#inputLatitudeCB').val(),
					longitude: $('#inputLongitudeCB').val(),
					accesslink: accesslink,
					accessport: $('#inputAccessPortCB').val(),
					sha: $('#inputSHACB').val()
										 
				 },
                 type: "POST",
                 async: true,
                 success: function (data) 
                 {
                    if(data["status"] === 'ko')
                    {
                        console.log("Error adding Context Broker");
                        console.log(data);
						$('#addContextBrokerModalTabs').hide();
						$('#addContextBrokerModalBody').hide();	
						$('#addContextBrokerModal div.modalCell').hide();
						//$('#addContextBrokerModalFooter').hide();
                        $('#addContextBrokerCancelBtn').hide();
						$('#addContextBrokerConfirmBtn').hide();
						$('#addContextBrokerOkBtn').show();
						$('#addCBOkMsg').hide();
						$('#addCBOkIcon').hide();	
						$('#addCBLoadingMsg').hide();
                        $('#addCBLoadingIcon').hide();
                        $('#addCBKoMsg').show();
						$('#addCBKoMsg div:first-child').html(data["error_msg"]);
                        $('#addCBKoIcon').show();
						
                        /*setTimeout(function(){
							
						$('#addCBKoMsg').hide();
						$('#addCBKoIcon').hide();
						$('#addContextBrokerModalTabs').show();
						$('#addContextBrokerModal div.modalCell').show();
						//$('#addContextBrokerModalFooter').show();
                        $('#addContextBrokerCancelBtn').show();
						$('#addContextBrokerConfirmBtn').show();
							
                        }, 3000);*/
                    }
                    else if (data["status"] === 'ok')
                    {
						console.log("Added Context Broker");
                        $('#addCBLoadingMsg').hide();
                        $('#addCBLoadingIcon').hide();
						$('#addCBKoMsg').hide();
						$('#addCBKoIcon').hide();
						
						$('#addContextBrokerModalTabs').hide();
						$('#addContextBrokerModalBody').hide();	
						$('#addContextBrokerModal div.modalCell').hide();
						//$('#addContextBrokerModalFooter').hide();
						$('#addContextBrokerCancelBtn').hide();
						$('#addContextBrokerConfirmBtn').hide();
                        $('#addContextBrokerOkBtn').show();
						
						
                        $('#addCBOkMsg').show();
                        $('#addCBOkIcon').show();
                        $('#addContextBrokerOkBtn').show();
                        
						
                        $('#dashboardTotNumberCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotNumberCnt .pageSingleDataCnt').html()) + 1);
                        $('#dashboardTotActiveCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotActiveCnt .pageSingleDataCnt').html()) + 1);
                        
                        $('#contextBrokerTable').DataTable().destroy();
							fetch_data(true);
                       
                        $('#inputNameCB').val("");
								$('#selectKindCB').val("");
								$('#inputPathCB').val("");
								$('#selectVisibilityCB').val("");
								$('#inputVersionCB').val("");
								$('#inputIpCB').val("");
								$('#inputPortCB').val("");
								$('#selectProtocolCB').val("NULL");
								$('#inputUriCB').val("");
								$('#inputLoginCB').val("");
								$('#inputPasswordCB').val("");
								$('#inputLatitudeCB').val("");
								$('#inputLongitudeCB').val("");
								$('#createdDateCB').val("");
								$('#inputAccessLinkCB').val("");
								$('#inputAccessPortCB').val("");
								$('#inputSHACB').val("");
                                  
                       /* setTimeout(function(){
                            $('#addContextBrokerModal').modal('hide');
                            //buildMainTable(true);
							$('#contextBrokerTable').DataTable().destroy();
							fetch_data(true);
							
                            setTimeout(function(){
								
                                $('#addCBOkMsg').hide();
                                $('#addCBOkIcon').hide();
								
								$('#inputNameCB').val("");
								$('#selectKindCB').val("");
								$('#inputPathCB').val("");
								$('#selectVisibilityCB').val("");
								$('#inputVersionCB').val("");
								$('#inputIpCB').val("");
								$('#inputPortCB').val("");
								$('#selectProtocolCB').val("NULL");
								$('#inputUriCB').val("");
								$('#inputLoginCB').val("");
								$('#inputPasswordCB').val("");
								$('#inputLatitudeCB').val("");
								$('#inputLongitudeCB').val("");
								$('#createdDateCB').val("");
								$('#inputAccessLinkCB').val("");
								$('#inputAccessPortCB').val("");
								$('#inputSHACB').val("");
								
											
								$('#addContextBrokerModalTabs').show();
                                $('#addContextBrokerModal div.modalCell').show();
                                //$('#addContextBrokerModalFooter').show()
                                $('#addContextBrokerCancelBtn').show();
						        $('#addContextBrokerConfirmBtn').show();
                            }, 500); 
                        }, 3000);*/
                    }
                },
                error: function(data)
                {
					
					console.log("Ko result: " + data);
                     $("#addContextBrokerModal").modal('hide');
					 
					 $('#addContextBrokerModalTabs').hide();
					 $('#addContextBrokerModalBody').hide();	
					 $('#addContextBrokerModal div.modalCell').hide();
					 $('#addContextBrokerModalFooter').hide();
					 $('#addCBOkMsg').hide();
					 $('#addCBOkIcon').hide();	
					
			
					 $('#addCBKoMsg').show();
					 $('#addCBKoIcon').show();
                     //$("#addContextBrokerModal").show();
                   //  $("#addContextBrokerModalFooter").show();
					
                }
                 
             });
        });	


// DELETE CONTEXT BROKER 

		//To be modified 
	$('#contextBrokerTable button.delDashBtn').off('hover');
	$('#contextBrokerTable button.delDashBtn').hover(function(){
		$(this).css('background', '#ffcc00');
		$(this).parents('tr').find('td').eq(1).css('background', '#ffcc00');
	}, 
	function(){
		$(this).css('background', '#e37777');
		$(this).parents('tr').find('td').eq(1).css('background', $(this).parents('td').css('background'));
	});

	
	$('#contextBrokerTable tbody').on('click', 'button.delDashBtn', function () 
	{
		var name = $(this).attr('data-name');
		$("#deleteContextBrokerModal div.modal-body").html('<div class="modalBodyInnerDiv"><span data-name = "' + name + '">Do you want to confirm deletion of context broker <b>' + name + '</b>?</span></div>');
		      
        
		$("#deleteCBModalInnerDiv1").html('<h5>Context broker deletion in progress, please wait</h5>');
        $("#deleteCBModalInnerDiv2").html('<i class="fa fa-circle-o-notch fa-spin" style="font-size:36px"></i>');
        $("#deleteCBModalInnerDiv1").hide();
        $("#deleteCBModalInnerDiv2").hide();
        $("#deleteContextBrokerOkBtn").hide();
        $("#deleteContextBrokerCancelBtn").show();
        $("#deleteContextBrokerConfirmBtn").show();
        $("#deleteContextBrokerModal").modal('show'); 
        
        
	});		

				
		$('#deleteContextBrokerConfirmBtn').off("click");
		$("#deleteContextBrokerConfirmBtn").click(function(){
		 
		var name = $("#deleteContextBrokerModal span").attr("data-name");

		$("#deleteContextBrokerModal div.modal-body").html("");
		$("#deleteContextBrokerCancelBtn").hide();
		$("#deleteContextBrokerConfirmBtn").hide();
		$("#deleteContextBrokerOkBtn").hide();
		$("#deleteCBModalInnerDiv1").show();
		$("#deleteCBModalInnerDiv2").show();

		 
		$.ajax({
			url: "../api/contextbroker.php",
			data:{
				action: "delete",
				//Sara2610 - for logging purpose
				username: loggedUser,
				organization : organization, 
				name: name
				},
			type: "POST",
			datatype: "json",
			async: true,
			
			success: function (data) 
			{
				console.log(JSON.stringify(data));
				$("#deleteContextBrokerOkBtn").show();
                if(data["status"] === 'ko')
				{
					$("#deleteCBModalInnerDiv1").html(data["error_msg"]);
					$("#deleteCBModalInnerDiv2").html('<i class="fa fa-frown-o" style="font-size:42px"></i>');
				}
				else if(data["status"] === 'ok')
				{
					$("#deleteCBModalInnerDiv1").html('Contextbroker &nbsp; deleted successfully');
					$("#deleteCBModalInnerDiv1").show();
					$("#deleteCBModalInnerDiv2").html('<i class="fa fa-check" style="font-size:42px"></i>');
					
					$('#dashboardTotNumberCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotNumberCnt .pageSingleDataCnt').html()) - 1);
					$('#dashboardTotActiveCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotActiveCnt .pageSingleDataCnt').html()) - 1);
				
                    fetch_data(true);
                    
					/*setTimeout(function()
					{
						//buildMainTable(true);
						$('#contextBrokerTable').DataTable().destroy();
						fetch_data(true);
						$("#deleteContextBrokerModal").modal('hide');
						setTimeout(function(){
							$("#deleteContextBrokerCancelBtn").show();
							$("#deleteContextBrokerConfirmBtn").show();
						}, 500);
					}, 2000);*/
				}
				else
			   {
				 console.log("delete context broker error:" + data);
			   }
	  
			},
			error: function (data) 
			{
				$("#deleteContextBrokerOkBtn").show();
                console.log(JSON.stringify(data));
				$("#deleteCBModalInnerDiv1").html(data["error_msg"]);
				$("#deleteContextBrokerModalInnerDiv2").html('<i class="fa fa-frown-o" style="font-size:42px"></i>');
                
			}
		});
	});	

		$("#deleteContextBrokerOkBtn").off("click");
        $("#deleteContextBrokerOkBtn").click(function(){
            $("#deleteContextBrokerModal div.modal-body").html("Do you want to confirm deletion of the following Context broker?");
            $("#deleteContextBrokerOkBtn").hide();
            $("#deleteContextBrokerCancelBtn").show();
            $("#deleteContextBrokerConfirmBtn").show();
            $("#deleteCBModalInnerDiv1").html('<h5>Context broker deletion in progress, please wait</h5>');
            $("#deleteCBModalInnerDiv2").html('<i class="fa fa-circle-o-notch fa-spin" style="font-size:36px"></i>');
            $("#deleteCBModalInnerDiv1").hide();
            $("#deleteCBModalInnerDiv2").hide();
        });   
			
// EDIT CONTEXT BROKER  

		//To be modified 
	$('#contextBrokerTable button.editDashBtn').off('hover');
	$('#contextBrokerTable button.editDashBtn').hover(function(){
		$(this).css('background', '#ffcc00');
		$(this).parents('tr').find('td').eq(1).css('background', '#ffcc00');
		}, 
		function(){
		$(this).css('background', 'rgb(69, 183, 175)');
		$(this).parents('tr').find('td').eq(1).css('background', $(this).parents('td').css('background'));
	});

// Edit Context broker			
	$('#contextBrokerTable tbody').on('click', 'button.editDashBtn', function () {

			$("#editContextBrokerModalUpdating").hide();
			$("#editCBModalLoading").hide();
			$("#editContextBrokerModalBody").show();
			//$('#editContextBrokerModalBody div.modalCell').show();
			$("#editContextBrokerModalFooter").show();
            $("#editContextBrokerCancelBtn").show();
            $("#editContextBrokerConfirmBtn").show();
			$("#editContextBrokerModal").modal('show');
			$("#editCBModalLabel").html("Edit Context Broker - " + $(this).attr("data-name"));
            $('#editContextBrokerLoadingMsg').hide();
            $('#editContextBrokerLoadingIcon').hide();
            $('#editContextBrokerOkMsg').hide();		
            $('#editContextBrokerOkIcon').hide();
            $('#editContextBrokerKoMsg').hide();
            $('#editContextBrokerKoIcon').hide();
            $('#editContextBrokerOkBtn').hide();
			
        /*$("#editContextBrokerModalUpdating").hide();
		$("#editCBModalLoading").hide();
		$("#editContextBrokerModalBody").show();
		//$('#editContextBrokerModalBody div.modalCell').show();
        $("#editContextBrokerModalFooter").show();
        $("#editCBModalLabel").html("Edit Context Broker - " + $(this).attr("data-name"));
        $("#editContextBrokerModalTabs").show();
		$('#editContextBrokerModal div.modalCell').show();
		$("#editContextBrokerCancelBtn").show();
		$("#editContextBrokerConfirmBtn").show();
		$('#editContextBrokerLoadingMsg').hide();
		$('#editContextBrokerLoadingIcon').hide();
        $('#editContextBrokerOkMsg').hide();		
        $('#editContextBrokerOkIcon').hide();
        $('#editContextBrokerKoMsg').hide();
        $('#editContextBrokerKoIcon').hide();
        $('#editContextBrokerOkBtn').hide();
        $("#editContextBrokerModal").modal('show');*/
        
		
			$("#inputNameCBM").val($(this).attr("data-name"));
			$("#inputOrganizationCBM").val($(this).attr("data-organization"));
			$("#selectKindCBM").val($(this).attr("data-kind"));
			$("#inputPathCBM").val($(this).attr("data-path"));
			$("#inputVersionCBM").val($(this).attr("data-version"));
			$("#selectVisibilityCBM").val($(this).attr("data-visibility"));
			$("#inputIpCBM").val($(this).attr("data-ip"));
			$("#inputPortCBM").val($(this).attr("data-port"));
			$("#selectProtocolCBM").val($(this).attr("data-protocol"));
			$("#inputUriCBM").val($(this).attr("data-uri"));
			$("#createdDateCBM").val($(this).attr("data-created"));
			$("#inputLatitudeCBM").val($(this).attr("data-latitude"));
			$("#inputLongitudeCBM").val($(this).attr("data-longitude"));
			$("#inputLoginCBM").val($(this).attr("data-login"));
			$("#inputPasswordCBM").val($(this).attr("data-password"));
			$("#inputAccessLinkCBM").val($(this).attr("data-accesslink"));
			$("#inputAccessPortCBM").val($(this).attr("data-accessport"));
			$("#inputApiKeyCBM").val($(this).attr("data-apikey"));
			$("#inputSHACBM").val($(this).attr("data-sha"));
					showEditCbModal();
			

			/*

		$.ajax({
			url: "../api/contextbroker.php",
			data: {
					action: "get_contextbroker", 
					name: $(this).parents('tr').attr("data-name")
					},
			type: "GET",
			async: true,
			dataType: 'json',
			success: function (data) 
			{
			  
			   if(data.result === 'ok')
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
				
			
			
				$('#inputNameCBM').val(data.contextBrokerData.name);
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
				console.log(JSON.stringify(data));
			  //  alert("Error retrieving  data");
			}


			  // showEditUserModalBody();
			},
			error: function (data)
			{
			   console.log("KO");
			   console.log(data);
			}
		});
		
		*/
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

	
	$('#contextBrokerTable tbody').on('click', 'button.viewDashBtn', function () {

	var name = $(this).attr('data-name');
	//var ip = $(this).parents('tr').find('td').eq(3).text();
	var ip = $(this).attr('data-ip');
	// var port = $(this).parents('tr').attr("data-port");
	var port = $(this).attr("data-port");
	var protocol = $(this).attr("data-protocol");
	//You can call the stub function here								
	activateStub(this,name,ip,port,protocol);

	});
	
		                     
		
		
 $('#editContextBrokerConfirmBtn').off("click");
 $("#editContextBrokerConfirmBtn").click(function(){
            /*$("#editContextBrokerModalBody").hide();
            $("#editContextBrokerModalFooter").hide();
		
            $("#editContextBrokerModalUpdating").show();*/
     
     
		//$('#editContextBrokerModal div.modalCell').hide();
		//$("#editDeviceModalFooter").hide();
		$("#editContextBrokerCancelBtn").hide();
		$("#editContextBrokerConfirmBtn").hide();
		$("#editContextBrokerModalBody").hide();
		$('#editContextBrokerLoadingMsg').show();
		$('#editContextBrokerLoadingIcon').show();
		// console.log(JSON.stringify(deviceJson));

             $.ajax({
                 url: "../api/contextbroker.php",
                 data:{
					action: "update",
					//Sara2610 - For logging purpose
					username: loggedUser,
					organization : organization, 
					obj_organization : $('#inputOrganizationCBM').val(), 
					name: $('#inputNameCBM').val(),
					kind: $('#selectKindCBM').val(),
					path: $('#inputPathCBM').val(),
					version: $('#inputVersionCBM').val(),
					visibility: $('#selectVisibilityCBM').val(),
					ip: $('#inputIpCBM').val(),
					port: $('#inputPortCBM').val(),
					protocol: $('#selectProtocolCBM').val(),
					uri: $('#inputUriCBM').val(),
					login: $('#inputLoginCBM').val(),
					password: $('#inputPasswordCBM').val(),
					latitude: $('#inputLatitudeCBM').val(),
					longitude: $('#inputLongitudeCBM').val(),
					createdDate: $('#createdDateCBM').val(),
					accesslink: $('#inputAccessLinkCBM').val(),
					accessport: $('#inputAccessPortCBM').val(),
					apikey: $('#inputApiKeyCBM').val(),
					sha: $('#inputSHACBM').val()
				 
				 },
                 type: "POST",
                 async: true,
                 success: function (data) 
                 {
                     if(data["status"] === 'ko')
						{
							/* $("#editContextBrokerModal").modal('hide');
							 $("#editContextBrokerKoModalInnerDiv1").html(data["msg"]);
							 $("#editContextBrokerKoModal").modal('show');
							 $("#editContextBrokerModalUpdating").hide();
							 $("#editContextBrokerModalBody").show();
							 $("#editContextBrokerModalFooter").show();*/
                            
                            $('#editContextBrokerLoadingMsg').hide();
                            $('#editContextBrokerLoadingIcon').hide();
                            $('#editContextBrokerOkMsg').hide();
                            $('#editContextBrokerOkIcon').hide();
                            $('#editContextBrokerKoMsg').show();
                            $('#editContextBrokerKoIcon').show();
                            $('#editContextBrokerOkBtn').show();
						}

					 else (data["status"] === 'ok')
						{
                             
                            $('#inputNameCBM').val("");
                            $('#inputIpCBM').val("");
                            $('#inputPortCBM').val("");
                            $('#inputVersionCBM').val("");
                            $('#inputAccessLinkCBM').val("");
                            $('#inputAccessPortCBM').val("");
                            $('#inputApiKeyCBM').val("");
                            $('#inputPathCBM').val("");
                            $('#inputLatitudeCBM').val("");
                            $('#inputLongitudeCBM').val("");
                            $('#inputLoginCBM').val("");
                            $('#inputPasswordCBM').val("");
                            $('#inputSHACBM').val("");         

                            $('#editContextBrokerLoadingMsg').hide();
                            $('#editContextBrokerLoadingIcon').hide();
                            $('#editContextBrokerOkMsg').show();
                            $('#editContextBrokerOkIcon').show();
                            $('#editContextBrokerKoMsg').hide();
                            $('#editContextBrokerKoIcon').hide();
                            $('#editContextBrokerOkBtn').show();

                            $('#contextBrokerTable').DataTable().destroy();
				            fetch_data(true);
                            
                            /*$("#editContextBrokerModal").modal('hide');
                             $("#editCBOkModalInnerDiv1").html('<h5>Context Broker successfully updated</h5>');
                             $("#editCBOkModal").modal('show');
                            // setTimeout(updateCBTimeout, 500);
							setTimeout (function(){
								// $('#contextBrokerTable').bootstrapTable("load");
								   // buildMainTable(true);
								   $('#contextBrokerTable').DataTable().destroy();
									fetch_data(true);
									location.reload();
									}, 500);*/
                          
                     }
                 },
                 error: function (data) 
                 {
                     console.log("Ko result: " + data);
                    /* $("#editContextBrokerModal").modal('hide');
                     $("#editContextBrokerKoModalInnerDiv1").html(data["msg"]);
                     $("#editContextBrokerKoModal").modal('show');
                     $("#editContextBrokerModalUpdating").hide();
                     $("#editContextBrokerModalBody").show();
                     $("#editContextBrokerModalFooter").show();*/
                     
                     $('#editContextBrokerLoadingMsg').hide();
                    $('#editContextBrokerLoadingIcon').hide();
                    $('#editContextBrokerOkMsg').hide();
                    $('#editContextBrokerOkIcon').hide();
                    $('#editContextBrokerKoMsg').show();
                    $('#editContextBrokerKoIcon').show();
                    $('#editContextBrokerOkBtn').show();
                 }
             });
        });
        
    


		$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
			var target = $(e.target).attr("href");
			if ((target == '#geoPositionTabCB')) {
					console.log("Elf");
					var latitude = 43.7800; 
					var longitude = 11.2300;
					var flag = 0;
					drawMap1(latitude,longitude, flag);
				} else {
				//nothing
			}
		});
		
		$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
			var target = $(e.target).attr("href");
			if ((target == '#editGeoPositionTabCB')) {
					console.log("Elf");
					var latitude = $("#inputLatitudeCBM").val(); 
					var longitude = $("#inputLongitudeCBM").val();                                        
					var flag =1;
					drawMap1(latitude,longitude, flag);
				} else {
				//nothing
			}
		});

	$('#addContextBrokerBtn').off('click');
	$('#addContextBrokerBtn').click(function (){
		
		$('#addContextBrokerModalTabs').show();
		$('#addContextBrokerModalBody').show();	
		$('#addContextBrokerModal div.modalCell').show();
		$('#addContextBrokerModalFooter').show();
		$('#addContextBrokerCancelBtn').show();
		$('#addContextBrokerConfirmBtn').show();
		$('#addContextBrokerOkBtn').hide();
		$('#addCBOkMsg').hide();
		$('#addCBOkIcon').hide();	
		$('#addCBKoMsg').hide();
		$('#addCBKoIcon').hide();	
		$('#addCBLoadingMsg').hide();
		$('#addCBLoadingIcon').hide();
		
		$("#loginExternal").hide();
		$("#loginInternal").hide();

		showAddCbModal();
	 // $("#addContextBrokerModal").modal('show');
		
	});

	$('#contextBrokerTable thead').css("background", "rgba(0, 162, 211, 1)");
	$('#contextBrokerTable thead').css("color", "white");
	$('#contextBrokerTable thead').css("font-size", "1.1em");
	
	/*$('#contextBrokerTable tbody tr').each(function(i){
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
	}); */
	
	
	$('#displayDevicesMapCB').off('click');
	$('#displayDevicesMapCB').click(function(){

	$.ajax({
		url: "../api/contextbroker.php",
		data: {
		organization : organization,
            username: loggedUser, 
            loggedrole:loggedRole,
        
		action: "get_all_contextbroker_latlong"
		},
		type: "POST",
		async: true,
		datatype: 'json',
        token : sessionToken,
		success: function (data) 
		 {
			
			 if(data["status"] === 'ko')
				{
					alert("An error occured when reading the data. <br/> Get in touch with the Snap4City Administrator<br/>"+ data["msg"]);
				}
			 else (data["status"] === 'ok')
				{
					var data = data["content"];
					
						
					   $("#addMap1CB").modal('show');
						
						drawMapAll(data, 'searchDeviceMapModalBodyCB');
						 
					}
		 },
		 error: function (data) 
		 {
			 console.log("Ko result: " + data);
			 alert("Network errors. <br/> Get in touch with the Snap4City Administrator<br/>"+ JSON.stringify(data));
		 }
		
	});

	});
	
        
	
	
	//CONTEXTBROKER AND PROTOCOL RELATION FOR EDIT DEVICE -SELECTOR 
 	
	$("#selectKindCB").change(function() {
	
		var value = document.getElementById("selectKindCB").value;
	
		 console.log("Value" + value);
		
		if(value ==='internal')
		{

			$("#loginExternal").hide();
			$("#loginInternal").show();
			document.getElementById("inputApiKeyCB").value = '';
			document.getElementById("inputPathCB").value = '';
					
		} 
		else if(value ==='external')
		{
			$("#loginInternal").hide();
			$("#loginExternal").show();
			document.getElementById("inputLoginCB").value = '';
			document.getElementById("inputPasswordCB").value = '';
		} 
		else if(value ==='')
		{
			$("#loginInternal").hide();
			$("#loginExternal").hide();
			document.getElementById("inputLoginCB").value = '';
			document.getElementById("inputPasswordCB").value = '';
			document.getElementById("inputApiKeyCB").value = '';
			document.getElementById("inputPathCB").value = '';
		} 
		else
		{
			document.getElementById("inputApiKeyCB").value = '';
			document.getElementById("inputPathCB").value = '';
			document.getElementById("inputLoginCB").value = '';
			document.getElementById("inputPasswordCB").value = '';
			
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
			if(($(this).val().trim() === loggedUser)&&(loggedRole !== "RootAdmin") && (loggedRole !== "ToolAdmin"))
				
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
		
}); // end of ready function			
			
			var internalDest = false;
            var tableFirstLoad = true;
			
			 $('.internalLink').on('mousedown', function(){
                internalDest = true;
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
				$("#editCBOkModal").modal('hide');
				setTimeout(function(){
				   location.reload();
				}, 500);
			}
        

		
		
//   START TO CHANGE THE VISIBILITY  & OWNERSHIP 
				
	function changeVisibility(name, visibility,obj_organization, accesslink) {	   	   
		$("#delegationsModal").modal('show');   
	    $("#delegationHeadModalLabel").html("Device - " + name);   			

        if(visibility=='MyOwnPrivate'){
				newVisibility = 'public';
				$('#visID').css('color', '#f3cf58');
				$("#visID").html("Visibility - Private");
				document.getElementById('newVisibilityPrivateBtn').style.visibility = 'hidden';
				document.getElementById('newVisibilityPublicBtn').style.visibility = 'show';
				
			} else
				
            {
				newVisibility = 'private';
				$('#visID').css('color', '#f3cf58');
				$("#visID").html("Visibility - Public");
				document.getElementById('newVisibilityPrivateBtn').style.visibility = 'show';
				document.getElementById('newVisibilityPublicBtn').style.visibility = 'hidden';
			}			  
	   
		$(document).on("click", "#newVisibilityPublicBtn", function(event){	
			$.ajax({
				url: "../api/contextbroker.php",
				data: 
				{	
					action: "change_visibility",
					username: loggedUser,					
					organization : organization,
					obj_organization : obj_organization,
                    name:name,
                    object:"BrokerID",
                    table:"contextbroker",
					accesslink: accesslink,
					visibility: newVisibility,
					token : sessionToken
				},
				type: "POST",
				async: true,
				dataType: 'json',
				success: function(data) 
				{
					if (data["status"] === 'ok')
					{
						$('#newVisibilityResultMsg').show();
						$("#visID").html("");
						$('#visID').css('color', '#f3cf58');
						$("#visID").html("Visibility - Private");
						$('#newVisibilityResultMsg').html('New visibility set to Public');
						
						$('#newVisibilityPublicBtn').addClass('disabled');
						
						setTimeout(function()
						{
							$('#devicesTable').DataTable().destroy();
							fetch_data(true);
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
		
		
		$(document).on("click", "#newVisibilityPrivateBtn", function(event){
		$.ajax({
				url: "../api/contextbroker.php",
				data: 
				{	
					action: "change_visibility", 
					username: loggedUser,
					organization : organization, 
					obj_organization : obj_organization, 
					name: name,
                    object:"BrokerID",
                    table:"contextbroker",
					accesslink: accesslink,
					visibility: newVisibility,
					token : sessionToken
					},
					type: "POST",
					async: true,
					dataType: 'json',	
            success: function(data) 
				{
					if (data["status"] === 'ok')
					{
						$('#newVisibilityResultMsg').show();
						$('#newVisibilityResultMsg').html('New visibility set Private');
						$('#newVisibilityPrivateBtn').addClass('disabled');
						setTimeout(function()
						{
							$('#devicesTable').DataTable().destroy();
							fetch_data(true);
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
	   
	$(document).on("click", "#newOwnershipConfirmBtn", function(event){
				$.ajax({
				 url: "../api/contextbroker.php",
				 data:{
				 action: "change_owner",
                 table:"contextbroker",
                 object:"BrokerID",
				 name: name,
				 accesslink: accesslink,
				 organization : organization, 
				 obj_organization : obj_organization, 
				 owner: loggedUser,
				 newOwner:  $('#newOwner').val(),
				 token : sessionToken
			 },	
			type: "POST",
			async: true,
			dataType: 'json',
			success: function(data) 
			{
				if (data["status"] === 'ok')
				{
					$('#newOwner').val('');
					$('#newOwner').addClass('disabled');
					$('#newOwnershipResultMsg').show();
					$('#newOwnershipResultMsg').html('New ownership set correctly');
					$('#newOwnershipConfirmBtn').addClass('disabled');
					
					
					setTimeout(function()
					{
						$('#devicesTable').DataTable().destroy();
						fetch_data(true);
						location.reload();
					}, 3000);
				}
				else if (data["status"] === 'ko')
				{
					$('#newOwner').addClass('disabled');
					$('#newOwnershipResultMsg').html('Error setting new ownership: please try again');
					$('#newOwnershipConfirmBtn').addClass('disabled');
					
					setTimeout(function()
					{
						$('#newOwner').removeClass('disabled');
						$('#newOwnershipResultMsg').html('');
						$('#newOwnershipResultMsg').hide();
					}, 3000);
				}
				else {console.log(data);}
			},
			error: function(errorData)
			{
				$('#newOwner').addClass('disabled');
				$('#newOwnershipResultMsg').html('Error setting new ownership: please try again');
				$('#newOwnershipConfirmBtn').addClass('disabled');

				setTimeout(function()
				{
					$('#newOwner').removeClass('disabled');
					$('#newOwnershipResultMsg').html('');
					$('#newOwnershipResultMsg').hide();
				}, 3000);
			}
		});
	});  
	


	$("#delegationsCancelBtn").off("click");
	$("#delegationsCancelBtn").on('click', function(){        
		$('#newDelegation').val("");
                $('#newDelegationGroup').val("");
                $('#newDelegationOrganization').val("");  
		$('#newOwner').val("");
		  $("#newVisibilityResultMsg").html("");
		  $("#newOwnershipResultMsg").html("");
		   location.reload(); 
		  $('#delegationsModal').modal('hide'); 		    								  		
	});
			

       $.ajax({
			url: "../api/contextbroker.php",   //Checking the delegation table
		   data:
			{
													   
                action: "get_delegations",  // check the action and to be specified
                accesslink: accesslink,
                obj_organization:obj_organization,
                name:name,
                user : loggedUser,
                object:"BrokerID",
                token : sessionToken,
			},
			type: "POST",
			async: true,
			dataType: 'json',
			success: function(data)
			{
					   
                if (data["status"]=='ok')
                {
					   
                    console.log(JSON.stringify(data));																					   delegations = data["delegation"];
                    $('#delegationsTable tbody').html("");   
                    $('#delegationsTableGroup tbody').html("");
			          
                    for(var i = 0; i < delegations.length; i++)
			   {
		
                   if ((delegations[i].userDelegated !="ANONYMOUS")&&(delegations[i].userDelegated!=null)) {
			   
                       console.log("adding user delegation");
			   
                       $('#delegationsTable tbody').append('<tr class="delegationTableRow" data-delegationId="' + delegations[i].delegationId + '" data-delegated="' + delegations[i].userDelegated + '"><td class="delegatedName">' + delegations[i].userDelegated + '</td><td><i class="fa fa-remove removeDelegationBtn"></i></td></tr>');

	   
                   }
	   
                   else  if (delegations[i].groupDelegated !=null){
			   
                       console.log("adding user delegation"+delegations[i]);
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
						url: "../api/contextbroker.php",     //check the url
						data:
						{
                            action: "remove_delegation",    // to be specified
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
								console.log("success removing delegation");
							}
							else
							{
								console.log("error removing delegation");
							}
						},
						error: function(errorData)
						{
						   console.log("error in call for removing delegation");
						}
					});
               });
                    $('#delegationsTableGroup tbody').on("click","i.removeDelegationBtnGroup",function(){
                        console.log("toremove:");
                        var rowToRemove = $(this).parents('tr');
                        $.ajax({
                            url: "../api/contextbroker.php",     //check the url
                            data:
                            {
                                action: "remove_delegation",
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
                                   console.log("error removing delegation");	
                                }	
                            },
                            error: function(errorData)
                            {
                                console.log("error in the call ro remove delegation");
							}
                        });	
                    });

                }
                else
				{				 
				console.log(json_encode(data));
				}
				},
				error: function(errorData)
			   {
				  console.log(errorData);//TBD  insert a message of error
				}
			});


       //listen about the confimation
       $(document).on("click", "#newDelegationConfirmBtn", function(event){
               var newDelegation = document.getElementById('newDelegation').value;
                $.ajax({
						url: "../api/contextbroker.php",       //which api to use
						data:
						{
						  action: "add_delegation",
						  accesslink : accesslink,
                          obj_organization:obj_organization,
                          obj_name: name,
                          object:"BrokerID",
						  user : loggedUser,
						  token : sessionToken,
                          organization:organization,
						  delegated_user: newDelegation,
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

       //group delegation -start------------------------------------------------------------------------------------------------------------
        $(document).on("click", "#newDelegationConfirmBtnGroup", function(event){
               
            var delegatedDN="";
               var e = document.getElementById("newDelegationGroup");
               if ((typeof e.options[e.selectedIndex] !== 'undefined')&&(e.options[e.selectedIndex].text!=='All groups')){
                       delegatedDN = "cn="+e.options[e.selectedIndex].text+",";
               }
                var e2 = document.getElementById("newDelegationOrganization");
               delegatedDN=delegatedDN+"ou="+e2.options[e2.selectedIndex].text;

                $.ajax({
                       url: "../api/contextbroker.php",
			   data:
			   {
					   action: "add_delegation",
					   accesslink : accesslink,
                       obj_organization: obj_organization,
                       obj_name: name,
                       object:"BrokerID",
					   user : loggedUser,
					   token : sessionToken,
					   delegated_group: delegatedDN
			   },
			   type: "POST",
			   async: true,
			   dataType: 'json',
			   success: function(data)
			   {
					   if (data["status"] === 'ok')
					   {
							   var toadd= $('#newDelegationOrganization').val();
							   if ( document.getElementById("newDelegationGroup").options[e.selectedIndex].text!=''){
									   toadd=toadd+","+$('#newDelegationGroup').val();
							   }

							   $('#delegationsTableGroup tbody').append('<tr class="delegationTableRowGroup" data-delegationId="' + data["delegationId"] + '" data-delegated="' + toadd+ '"><td class="delegatedNameGroup">' +toadd + '</td><td><i class="fa fa-remove removeDelegationBtnGroup"></i></td></tr>');
							   $('#newDelegatedMsgGroup').css('color', 'white');
							   $('#newDelegatedMsgGroup').html('New delegation added correctly');

							   setTimeout(function()
							   {
									   $('#newDelegatedMsgGroup').css('color', '#f3cf58');
									   $('#newDelegatedMsgGroup').html('Delegated groupname can\'t be empty');
							   }, 1500);
					   }
					   else
					   {
							   var errorMsg = null;
							   $('#newDelegatedMsgGroup').css('color', '#f3cf58');
							   $('#newDelegatedMsgGroup').html(data["msg"]);

							   setTimeout(function()
							   {
									   $('#newDelegationGroup').removeClass('disabled');
									   $('#newDelegationOrganization').removeClass('disabled');
									   $('#newDelegatedMsgGroup').css('color', '#f3cf58');
									   $('#newDelegatedMsgGroup').html('Delegated groupname can\'t be empty');
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
							   $('#newDelegatedMsgGroup').html('Delegated groupname can\'t be empty');
					   }, 2000);
			   }
               });
       });     //group delegation -end
	
	}

// END TO CHANGE THE VISIBILITY 		
    
	
	
		
/* Related to the Map */
	
		
	function drawMap1(latitude,longitude, flag){	
			console.log("Elf: Before Map loading");
            var marker;
            if (typeof map !== 'undefined') {
                map.remove();
            }
			if (flag ==0){
				    var centerMapArr= gpsCentreLatLng.split(",",2);
                    var centerLat= parseFloat(centerMapArr[0].trim());
                    var centerLng= parseFloat(centerMapArr[1].trim());
                    map = L.map('addLatLong').setView([centerLat,centerLng], zoomLevel);
					 L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
					 attribution: '&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap</a> contributors'
				}).addTo(map);
			window.node_input_map = map;
			//L.marker([latitude,longitude]).addTo(map).bindPopup("Hi DEVICE");
			setTimeout(function(){ map.invalidateSize()}, 400);	
			
			map.on("click", function (e) {
				
					var lat = e.latlng.lat;
					var lng = e.latlng.lng;
					lat = lat.toFixed(5);
					lng = lng.toFixed(5);
					console.log("Check the format:" + lat + " " + lng);
				
				
				 document.getElementById('inputLatitudeCB').value = lat;
				 document.getElementById('inputLongitudeCB').value = lng;
                                 //checkAddCbLatitude();
                                 //checkAddCbLongitude();
                                 addCbConditionsArray['inputLatitudeCB'] = true;
                                 checkCbLatitude(); checkAddCbConditions(); 
                                 addCbConditionsArray['inputLongitudeDevice'] = true;
                                 checkCbLongitude(); checkAddCbConditions();

                                 // addCbConditionsArray['inputLatitudeCB'] = true;
                                 // addCbConditionsArray['inputLongitudeCB'] = true;
				 // checkCbLatitude();
	                         // checkCbLongitude();
                                 if (marker){
					 map.removeLayer(marker);
				 }
				 marker = new L.marker([lat,lng]).addTo(map).bindPopup(lat + ',' + lng);
			
			});
			
			
			} else if (flag ==1) {
				map = L.map('addLatLongEdit').setView([latitude,longitude], 10);
					 L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
					 attribution: '&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap</a> contributors'
				}).addTo(map);
			window.node_input_map = map;
			//L.marker([latitude,longitude]).addTo(map).bindPopup("Hi DEVICE");
			setTimeout(function(){ map.invalidateSize()}, 400);	
			
			marker = new L.marker([latitude,longitude]).addTo(map).bindPopup(longitude, longitude);
			map.on("click", function (e) {
				
				var lat = e.latlng.lat;
				var lng = e.latlng.lng;
				lat = lat.toFixed(5);
				lng = lng.toFixed(5);
				console.log("Check the format:" + lat + " " + lng);
				
				document.getElementById('inputLatitudeCBM').value = lat;
				document.getElementById('inputLongitudeCBM').value = lng;
                                // checkEditCbLatitude();
                                //  checkEditCbLongitude();
                                 editCbConditionsArray['inputLatitudeCBM'] = true;
                                 editCbConditionsArray['inputLongitudeCBM'] = true;

				 if (marker){
					 map.removeLayer(marker);
				 }
				 marker = new L.marker([lat,lng]).addTo(map).bindPopup(lat + ',' + lng);
			
			});
			
			
			}
            
	
		}
	
	
	
		
	
	
function drawMapAll(data, divName){
		var latitude = 43.7800;
		var longitude =11.2300;
    
    if (typeof map_all === 'undefined' || !map_all) {
           // map_all = L.map(divName).setView([latitude,longitude], 10);
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
        
        /**************************Fatima-end******************************/
        
        /*************Fatima2-start*************/  
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
             $('#addMap1CB').modal('hide');
             //Fatima2-moveAndupdate-1-line
             colorSelectedMarkers(resultsOut, greenIcon);
            fetch_data(true, JSON.stringify(resultsOut));
    //      console.log(resultsOut);
	  

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
                         $('#addMap1CB').modal('hide');
                            //Fatima2-moveAndupdate-1-line
                            colorSelectedMarkers(resultsOut, greenIcon);
                         fetch_data(true, JSON.stringify(resultsOut));
                        });
				
	
                });

       
        /******************Fatima-start*************************/  
       /* map_all.on('draw:deleted', function(e) {
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
                console.log("selections are");
                console.log(selections);
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

