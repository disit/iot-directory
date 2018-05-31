
var _serviceIP = "../..";
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
	xhr.send();
	return true;
}



/*JQuery Started...*/

    $(document).ready(function () 
    {
    
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
            /*
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
            }*/
        });

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
				 console.log(loggedRole + " " + element[loggedRole] + " " + element["class"]);
			   }
			}   
		}
		
		$('#contextbrokerLink .mainMenuItemCnt').addClass("mainMenuItemCntActive");
        $('#mobMainMenuPortraitCnt #contextbrokerLink .mobMainMenuItemCnt').addClass("mainMenuItemCntActive");
        $('#mobMainMenuLandCnt #contextbrokerLink .mobMainMenuItemCnt').addClass("mainMenuItemCntActive");
    
            
			
			buildMainTable(false);
});			
			
			var internalDest = false;
            var tableFirstLoad = true;
			
			 $('.internalLink').on('mousedown', function(){
                internalDest = true;
            });
			
        /*  ADD CONTEXT BROKER (INSERT INTO DB) */ 	
		
        $('#addContextBrokerConfirmBtn').off("click");
        $("#addContextBrokerConfirmBtn").click(function(){
			
			$('#addContextBrokerModalTabs').hide();
            $('#addContextBrokerModal div.modalCell').hide();
            $('#addContextBrokerModalFooter').hide();
            $('#addCBLoadingMsg').show();
            $('#addCBLoadingIcon').show();
	
				$.ajax({
                 url: "../api/contextbroker.php",
                 data:{
					action: "insert",
					name: $('#inputNameCB').val(),
					ip: $('#inputIpCB').val(),
					port:  $('#inputPortCB').val(),
					protocol: $('#selectProtocolCB').val(),
					login: $('#inputLoginCB').val(),
					password: $('#inputPasswordCB').val(),
					latitude: $('#inputLatitudeCB').val(),
					longitude: $('#inputLongitudeCB').val(),
										 
				 },
                 type: "POST",
                 async: true,
                 success: function (data) 
                 {
                    if(data["status"] === 'ko')
                    {
                        console.log("Error adding Context Broker");
                        console.log(data);
						$('#addCBLoadingMsg').hide();
                        $('#addCBLoadingIcon').hide();
                        $('#addCBKoMsg').show();
                        $('#addCBKoIcon').show();
						
                        setTimeout(function(){
							
						$('#addCBKoMsg').hide();
						$('#addCBKoIcon').hide();
						$('#addContextBrokerModalTabs').show();
						$('#addContextBrokerModal div.modalCell').show();
						$('#addContextBrokerModalFooter').show();
							
                        }, 3000);
                    }
                    else (data["status"] === 'ok')
                    {
						console.log("Added Context Broker");
                        $('#addCBLoadingMsg').hide();
                        $('#addCBLoadingIcon').hide();
						$('#addCBKoMsg').hide();
						$('#addCBKoIcon').hide();
                        $('#addCBOkMsg').show();
                        $('#addCBOkIcon').show();
						
                        $('#dashboardTotNumberCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotNumberCnt .pageSingleDataCnt').html()) + 1);
                        $('#dashboardTotActiveCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotActiveCnt .pageSingleDataCnt').html()) + 1);
                                  
                        setTimeout(function(){
                            $('#addContextBrokerModal').modal('hide');
                            buildMainTable(true);
							
                            setTimeout(function(){
								
                                $('#addCBOkMsg').hide();
                                $('#addCBOkIcon').hide();
								
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
					 $('#addCBKoMsg').show();
					 $('#addCBKoIcon').show();
                     $("#addContextBrokerModal").show();
                     $("#addContextBrokerModalFooter").show();
					
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
                url: "../api/contextbroker.php",
                data:{
					action: "delete",
					name: name
					},
                type: "POST",
				datatype: "json",
                async: true,
				
                success: function (data) 
                {
					console.log(JSON.stringify(data));
                    if(data["status"] === 'ko')
                    {
                        $("#deleteContextBrokerModalInnerDiv1").html(data["msg"]);
                        $("#deleteContextBrokerModalInnerDiv2").html('<i class="fa fa-frown-o" style="font-size:42px"></i>');
                    }
                    else if(data["status"] === 'ok')
                    {
                        $("#deleteContextBrokerModalInnerDiv1").html('Contextbroker &nbsp; <b>' + name + '</b> &nbsp; deleted successfully');
                        $("#deleteContextBrokerModalInnerDiv2").html('<i class="fa fa-check" style="font-size:42px"></i>');
						
						$('#dashboardTotNumberCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotNumberCnt .pageSingleDataCnt').html()) - 1);
                        $('#dashboardTotActiveCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotActiveCnt .pageSingleDataCnt').html()) - 1);
					
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
					console.log(JSON.stringify(data));
                    $("#deleteContextBrokerModalInnerDiv1").html(data["msg"]);
                    $("#deleteContextBrokerModalInnerDiv2").html('<i class="fa fa-frown-o" style="font-size:42px"></i>');
                }
            });
        });	
				
				
			 /* EDIT CONTEXT BROKER  */ 
		
		
		    $('#editContextBrokerConfirmBtn').off("click");
			$("#editContextBrokerConfirmBtn").click(function(){
            $("#editContextBrokerModalBody").hide();
            $("#editContextBrokerModalFooter").hide();
		
            $("#editContextBrokerModalUpdating").show();

             $.ajax({
                 url: "../api/contextbroker.php",
                 data:{
					action: "update",
					name: $('#inputNameCBM').val(),
					ip: $('#inputIpCBM').val(),
					port: $('#inputPortCBM').val(),
					protocol: $('#selectProtocolCBM').val(),
					uri: $('#inputUriCBM').val(),
					login: $('#inputLoginCBM').val(),
					password: $('#inputPasswordCBM').val(),
					latitude: $('#inputLatitudeCBM').val(),
					longitude: $('#inputLongitudeCBM').val(),
					createdDate: $('#createdDateCBM').val()
				 
				 },
                 type: "POST",
                 async: true,
                 success: function (data) 
                 {
                     if(data["status"] === 'ko')
						{
							 $("#editContextBrokerModal").modal('hide');
							 $("#editContextBrokerKoModalInnerDiv1").html(data["msg"]);
							 $("#editContextBrokerKoModal").modal('show');
							 $("#editContextBrokerModalUpdating").hide();
							 $("#editContextBrokerModalBody").show();
							 $("#editContextBrokerModalFooter").show();
						}

					 else (data["status"] === 'ok')
						{
                             $("#editContextBrokerModal").modal('hide');
                             $("#editCBOkModalInnerDiv1").html('<h5>Context Broker <b>' + name + '</b> successfully updated</h5>');
                             $("#editCBOkModal").modal('show');
                            // setTimeout(updateCBTimeout, 500);
							setTimeout (function(){
								$('#contextBrokerTable').bootstrapTable("load");
									//location.reload();
									}, 500);
                          
                     }
                 },
                 error: function (data) 
                 {
                     console.log("Ko result: " + data);
                     $("#editContextBrokerModal").modal('hide');
                     $("#editContextBrokerKoModalInnerDiv1").html(data["msg"]);
                     $("#editContextBrokerKoModal").modal('show');
                     $("#editContextBrokerModalUpdating").hide();
                     $("#editContextBrokerModalBody").show();
                     $("#editContextBrokerModalFooter").show();
                 }
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
				$("#editCBOkModal").modal('hide');
				setTimeout(function(){
				   location.reload();
				}, 500);
			}
        
			
		function buildMainTable(destroyOld, selected=null)
        {
            if(destroyOld)
            {
                $('#contextBrokerTable').bootstrapTable('destroy');
                tableFirstLoad = true;
            }
            var creatorVisibile = true;
            if($(window).width() < 992)
            {
                
                creatorVisibile = false; 
                
            }
             if (selected==null)
			{
			  mydata = {action: "get_all_contextbroker"};
			}
			else
			{
			  mydata = {action: "get_subset_contextbroker", select : selected};
			}
		   
		   
            $.ajax({
                url: "../api/contextbroker.php",
                data: mydata,
                type: "GET",
                async: true,
                dataType: 'json',
                success: function(data) 
                {
					data = data["content"];
					console.log(JSON.stringify(data));
                    creatorVisibile = true;
                    detailView = true;
                    // statusVisibile = true;
                    
                    if($(window).width() < 992)
                    {
                        detailView = false;
                        creatorVisibile = false; 
                        // statusVisibile = false;
                    }
                   
                    $('#contextBrokerTable').bootstrapTable({
                        columns: [
                        {
                            field: 'name',
                            title: 'IOT Broker',
							filterControl: 'input',
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
							filterControl: 'input',
                            sortable: true,
                            valign: "middle",
                            align: "center",
                            halign: "center",
                           //  visible: creatorVisibile,
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
                            // visible: creatorVisibile,
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
							filterControl: 'input',
                            sortable: true,
                            valign: "middle",
                            align: "center",
                            halign: "center",
                            // visible: creatorVisibile,
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
							filterControl: 'select',
                            sortable: true,
                            valign: "middle",
                            align: "center",
                            halign: "center",
                            // visible: creatorVisibile,
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
							filterControl: 'input',
                            sortable: true,
                            valign: "middle",
                            align: "center",
                            halign: "center",
                            // visible: creatorVisibile,
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
						filterControl: true,
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
                               
                                tableFirstLoad = false;
                                //var addCBDiv = $('<div class="pull-right"><i id="addContextBrokerBtn" data-toggle="modal" data-target="#addContextBrokerModal" class="fa fa-plus-square" style="font-size:36px; color: #ffcc00"></i></div>');

							var addMapDiv = $('<div id="displayDevicesMapCB" class="pull-left"><button type="button" class="btn btn-primary btn-round"><span class="glyphicon glyphicon-globe" style="font-size:36px; color: #0000ff"></span></button></div>');
									$('div.fixed-table-toolbar').append(addMapDiv);
									addMapDiv.css("margin-top", "10px");
									addMapDiv.css("margin-left", "150px");
									addMapDiv.find('button.btn btn-primary btn-round').off('hover');
                                    addMapDiv.find('button.btn btn-primary btn-round').hover(function(){
                                        $(this).css('color', '#e37777');
                                       // $(this).css('background', '#ffcc00');
									$(this).parents('tr').find('td').eq(1).css('background', '#ffcc00');
                                    }, 
                                    function(){
                                      $(this).css('background', '#e37777');
                                       //$(this).parents('tr').find('td').eq(1).css('background', $(this).parents('td').css('background'));
                                    });
									
									 
									 $('#displayDevicesMapCB').off('click');
									 $('#displayDevicesMapCB').click(function(){
										
										$.ajax({
											url: "../api/contextbroker.php",
											data: {
											action: "get_all_contextbroker_latlong"
											},
											type: "GET",
											async: true,
											datatype: 'json',
											success: function (data) 
											 {
												
                                                 if(data["status"] === 'ko')
													{
                                                        data = data["content"];
													}
												 else (data["status"] === 'ok')
													{
														var data = data["content"];
														var mylat =[];
														var mylong=[];
														
														for (var i=0; i<data.length; i++){
															 mylat.push(data[i].latitude);
															 mylong.push(data[i].longitude);
															}
															
														   $("#addMap1CB").modal('show');
														    //drawMapAll(mylat, mylong);
                                                            drawMapAll(data);
															 
														}
											 },
											 error: function (data) 
											 {
												 console.log("Ko result: " + data);
											 }
											
										});
							
							     		});
							
								 var addCBDiv = $('<div class="pull-right"><button id="addContextBrokerBtn"  class="btn btn-primary">New IOT Broker</button></div>');

								
								
                                $('div.fixed-table-toolbar').append(addCBDiv);
                                addCBDiv.css("margin-top", "10px");
                                //addCBDiv.find('i.fa-plus-square').off('hover');
                                //addCBDiv.find('i.fa-plus-square').hover(function(){
								addCBDiv.find('button.btn btn-primary').off('hover');
                                addCBDiv.find('button.btn btn-primary').hover(function(){
                                    $(this).css('color', '#e37777');
                                    $(this).css('cursor', 'pointer');
                                }, 
                                function(){
                                    $(this).css('color', '#ffcc00');
                                    $(this).css('cursor', 'normal');
                                });
                                
								
                                $('#addContextBrokerBtn').off('click');
								$('#addContextBrokerBtn').click(function (){
									showAddCbModal();
								  $("#addContextBrokerModal").modal('show');
									
								});
								
                                $('#contextBrokerTable thead').css("background", "rgba(0, 162, 211, 1)");
                                $('#contextBrokerTable thead').css("color", "white");
                                $('#contextBrokerTable thead').css("font-size", "1.1em");
								
								
                            }
                            else
                            {
                                
                            }

														
							
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
                            aggiornaStub();
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
										$("#editCBModalLoading").hide();
											showEditCbModal();
										$("#editContextBrokerModalBody").show();
										$('#editContextBrokerModalTabs').show();
										//$('#editContextBrokerModalBody div.modalCell').show();
										$("#editContextBrokerModalFooter").show();
										$("#editContextBrokerModal").modal('show');
										$("#editCBModalLabel").html("Edit Context Broker - " + $(this).parents('tr').attr("data-name"));
										
									
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
							
							$('#contextBrokerTable button.viewDashBtn').off('click');
                            $('#contextBrokerTable button.viewDashBtn').click(function () 
                            {
						
							var name = $(this).parents('tr').attr('data-name');
							//var ip = $(this).parents('tr').find('td').eq(3).text();
							var ip = $(this).parents('tr').attr('data-ip');
							// var port = $(this).parents('tr').attr("data-port");
							var port = $(this).parents('tr').attr("data-port");
							var protocol = $(this).parents('tr').attr("data-protocol");
							//You can call the stub function here								
							activateStub(this,name,ip,port,protocol);

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
                        
						

							  for (var func =0;func < functionality.length; func++)
										{
										  var element = functionality[func];
										  if (element.view=="popup")
										  {
											  if (element[loggedRole]==1)  
											   {   //  console.log(element.view + loggedRole + " " + element[loggedRole] + " " + element["class"]); 
												   $(element["class"]).show();
											   }			   
											   else 
											   { 
												  $(element["class"]).hide();
												 // console.log(element.view + loggedRole + " " + element[loggedRole] + " " + element["class"]);
											   }
											}   
										}


						
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
		
		

    
		
	/* Related to the Map */
	
	function drawMap1(latitude,longitude, flag){	
			console.log("Elf: Before Map loading");
			var marker;
			
			if (flag ==0){
					 map = L.map('addLatLong').setView([latitude,longitude], 10);
					 L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
					 attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
				}).addTo(map);
			window.node_input_map = map;
			//L.marker([latitude,longitude]).addTo(map).bindPopup("Hi DEVICE");
			setTimeout(function(){ map.invalidateSize()}, 400);	
			
			map.on("click", function (e) {
				
					var lat = e.latlng.lat;
					var lng = e.latlng.lng;
					lat = lat.toFixed(4);
					lng = lng.toFixed(4);
					console.log("Check the format:" + lat + " " + lng);
				
				
				 document.getElementById('inputLatitudeCB').value = lat;
				 document.getElementById('inputLongitudeCB').value = lng;
				 if (marker){
					 map.removeLayer(marker);
				 }
				 marker = new L.marker([lat,lng]).addTo(map).bindPopup(lat + ',' + lng);
			
			});
			
			
			} else if (flag ==1) {
				map = L.map('addLatLongEdit').setView([latitude,longitude], 10);
					 L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
					 attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
				}).addTo(map);
			window.node_input_map = map;
			//L.marker([latitude,longitude]).addTo(map).bindPopup("Hi DEVICE");
			setTimeout(function(){ map.invalidateSize()}, 400);	
			
			marker = new L.marker([latitude,longitude]).addTo(map).bindPopup(longitude, longitude);
			map.on("click", function (e) {
				
				var lat = e.latlng.lat;
				var lng = e.latlng.lng;
				lat = lat.toFixed(4);
				lng = lng.toFixed(4);
				console.log("Check the format:" + lat + " " + lng);
				
				document.getElementById('inputLatitudeCBM').value = lat;
				document.getElementById('inputLongitudeCBM').value = lng;
				 if (marker){
					 map.removeLayer(marker);
				 }
				 marker = new L.marker([lat,lng]).addTo(map).bindPopup(lat + ',' + lng);
			
			});
			
			
			}
	
		}
		
function drawMapAll(data){
		var latitude = 43.7800;
		var longitude =11.2300;
if (typeof map === 'undefined' || !map) {
		map = L.map('searchDeviceMapModalBodyCB').setView([latitude,longitude], 10);
	   L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
	   attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
	  }).addTo(map);
	   window.node_input_map = map;
     
     var mapLayers = {};
    drawnItems = new L.FeatureGroup();
            map.addLayer(drawnItems);

            var editControl = new L.Control.Draw({
                draw: false,
                edit: {
                    featureGroup: drawnItems,
                    poly: {
                        allowIntersection: false
                    }
                }
            });
            map.addControl(editControl);
     
     drawControl = new L.Control.Draw({
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
            map.addControl(drawControl);
     
      L.control.layers(mapLayers, {
                'drawlayer': drawnItems
            }, {
                collapsed: true
            }).addTo(map);
     
     map.on(L.Draw.Event.CREATED, function(e) {
                var fence = e.layer;
                if (drawnItems.hasLayer(fence) == false) {
                    drawnItems.addLayer(fence);
                }

                drawControl.remove();
                TYPE= e.layerType;
                layer = e.layer;
                 
         var resultsOut=drawSelection(layer, TYPE, data);
         $('#addMap1CB').modal('hide');
		buildMainTable(true, JSON.stringify(resultsOut));
//      console.log(resultsOut);
     
     });
     
     map.on('draw:edited', function(e) {
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
		             buildMainTable(true, JSON.stringify(resultsOut));
                    });

		
            });

     map.on('draw:deleted', function(e) {
                drawControl.addTo(map);
            });
}
   
   for (var i=0; i<data.length; i++) {
	   
	var mylat=data[i].latitude;
    var mylong= data[i].longitude;  
	var myname = data[i].name;
															
    marker = new L.marker([mylat,mylong]).addTo(map).bindPopup(myname);
	//console.log("Before My Marker: " + mylat);
	}
		setTimeout(function(){ map.invalidateSize()}, 400);
	
 }

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
