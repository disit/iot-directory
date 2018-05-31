
var gb_datatypes ="";
var gb_value_units ="";
var gb_value_types = "";
var defaultPolicyValue = [];


var gb_device ="";
var gb_latitude ="";
var gb_longitude = "";
var gb_k1="";
var gb_k2="";


var gb_key1="";
var gb_key2="";

     //   var existingPoolsJson = null;
        // var internalDest = false;
        var tableFirstLoad = true;

//Settaggio dei globals per il file usersManagement.js
 //       setGlobals(admin, existingPoolsJson);
        

 $.ajax({url: "../api/device.php",
         data: {
			 action: 'get_param_values'
			 },
         type: "GET",
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
		 }
});
     
        function updateDeviceTimeout()
        {
            $("#editDeviceOkModal").modal('hide');
            setTimeout(function(){
               location.reload();
            }, 500);
        }
        
        function buildMainTable(destroyOld, selected=null)
        {
            if(destroyOld)
            {
                $('#devicesTable').bootstrapTable('destroy');
                tableFirstLoad = true;
				
            }
           
            var statusVisible = true;
            

            if($(window).width() < 992)
            {
     
                statusVisible = false; 
                
            }
			if (selected==null)
			{
			  mydata = {action: "get_all_private_event_value", token : sessionToken};
			}
			else
			{
			  mydata = {action: "get_subset_event_value", token : sessionToken, select : selected};
			}
            

            $.ajax({
                url: "../api/value.php",
                data: mydata,
                type: "POST",
                async: true,
                datatype: 'json',
                success: function (data)
                {
					data = data["content"];
					var creatorVisibile = true;
                    var detailView = true;
                    var statusVisibile = true;

                    if($(window).width() < 992)
                    {
                        detailView = false;
                        creatorVisibile = false; 
                        statusVisibile = false;
                    }
				
                    $('#devicesTable').bootstrapTable({
                                columns: [{
									
                                field: 'device',
								title: 'Device',
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
								
								mycss = {"border-top": "none", "font-weight": "bold"};
								if(index%2 !== 0)
								{
								  mycss["background-color"]="rgb(230, 249, 255)";
								}
								else
								{
								   mycss["background-color"]="white"; 
								}
								if (row.kind=='sensor')
								{
								  mycss["color"]="rgb(69, 183, 175)";
								}
								else
								{
								   mycss["color"]="#e37777"; 
								}
							   return {
										classes: null,
										css: mycss
									};
                                    }
                            }, 
							
                            {
                                field: 'value_type',
								title: 'Value Type',
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
							
							    field: 'status1',
								title: 'Status',
								filterControl: 'select',
                                align: "center",
                                valign: "middle",
                                align: "center",
                                halign: "center",
                                formatter: function(value, row, index)
                                {
								// console.log("prop" +row.mandatoryproperties + "value" +row.mandatoryvalues);
								if (row.status1=='active')
								return '<button type="button" id="active" class="btn btn-success"><span style="font-size:8px; color: white">active</span></button>';
								else 
								return '<button type="button" id="iddle" class="btn btn-warning"><span style="font-size:8px; color: white">iddle</span></button>';
                                 },
                                cellStyle: function(value, row, index, field) {
                                    if(index%2 !== 0)
                                    {
                                        return {
                                            classes: null,
                                            css: {
                                                "background-color": "rgb(230, 249, 255)",
                                                "border-top": "none",
												
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
                                     //if (row.visibility != "delegated")
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
								    //if (mapButton)
                                    return '<div class="addMapBtn"><i data-toggle="modal" data-target="#addMap" class="fa fa-globe" onclick="drawMap(\''+ row.latitude +  "\',\'" + row.longitude + '\',\'' + row.device + '\')\" style=\"font-size:36px; color: #0000ff\"></i></div>';
                                },
                                cellStyle: function(value, row, index, field) {
                                    if(index%2 !== 0)
                                    {
                                        return {
                                            classes: null,
                                            css: {
                                                "background-color": "rgb(230, 249, 255)",
                                                "border-top": "none",
												
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
                            uniqueId: "value_name",
                            striped: false,
                            classes: "table table-hover table-no-bordered",
							detailView: detailView,
							detailFormatter: function(index, row, element) {
                            var txt ="";
                            if (data[index].cb=="orionUNIFI") txt = ' IoT BRoker URI: ' + ' http://iot-app.snap4city.org/orion-broker/'; 
                            return 'IoT Broker: ' + data[index].cb  + txt +  '  k1: ' + row.k1 + '  k2: ' + row.k2  + ' Value Name: ' + data[index].value_name   + ' Data Type: ' + data[index].data_type   + '  Editable: ' + data[index].editable + 'Healthiness Criteria: ' + data[index].healthiness_criteria   + ' Refresh Rate: ' + data[index].value_refresh_rate   + ' | Different Value: ' + data[index].different_values + " | Value Bound: " + data[index].value_bounds + " | Order: " + data[index].order + " | Kind: " + data[index].kind;
							},
                            rowAttributes: function(row, index){
                            return {
                                "data-cb": row.cb,
                                "data-device": row.device,
                                "data-value_name": row.value_name,
                                "data-data_type": row.data_type,
                                "data-value_type": row.value_type,
                                "data-editable": row.editable,
                                "data-value_unit": row.value_unit,
                                "data-healthiness_criteria": row.healthiness_criteria,
                                "data-value_refresh_rate": row.value_refresh_rate,
				"data-different_values": row.different_values,
                                "data-value_bounds": row.value_bounds,
                                "data-order": row.order,
				"data-kind" : row.kind,
				 "data-latitude" : row.latitude,
				 "data-longitude" : row.longitude,
				 "data-mandatoryproperties" : row.mandatoryproperties,
				 "data-mandatoryvalues" : row.mandatoryvalues,
                                 "data-k1": row.k1,
                                 "data-k2": row.k2

                              };
						  },
						   searchTimeOut: 250,
                            onPostBody: function()
                            {
                                if(tableFirstLoad)
                                {
                                   
								   console.log("Inside the post");
								   tableFirstLoad = false;
									
									
									var addMapDiv = $('<div id="displayDevicesMap" class="pull-left"><button type="button" class="btn btn-primary btn-round"><span class="glyphicon glyphicon-globe" style="font-size:36px; color: #0000ff"></span></button></div>');
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
									
									 
									 $('#displayDevicesMap').off('click');
									 $('#displayDevicesMap').click(function(){
										
										$.ajax({
											url: "../api/value.php",
											data: {
											action: "get_all_private_event_value",
											token : sessionToken
											},
											type: "POST",
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
														
														var dataloc = data["content"];
														var mylat =[];
														var mylong=[];
														
														   $("#addMap1").modal('show');
                                                            drawMapAll(dataloc);
														}
											 },
											 error: function (data) 
											 {
												 console.log("Ko result: " + data);
											 }
											
										});		
									
									
                                });
									
							
                                   // var addDeviceDiv = $('<div class="pull-right"><i id="addDeviceBtn" data-toggle="modal" data-target="#addDeviceModal" alt="New Device" class="fa fa-plus-square" style="font-size:36px; color: #ffcc00"></i></div>');
									var addDeviceDiv = $('<div class="pull-right"><button id="addDeviceBtn"  class="btn btn-primary">New Device</button></div>');
                                    
                                    $('div.fixed-table-toolbar').append(addDeviceDiv);
                                    addDeviceDiv.css("margin-top", "10px");
									//addDeviceDiv.css("margin-right", "30px");
									//addDeviceDiv.find('i.fa-plus-square').off('hover');
									//addDeviceDiv.find('i.fa-plus-square').hover(function(){
                                    addDeviceDiv.find('button.btn btn-primary').off('hover');
                                    addDeviceDiv.find('button.btn btn-primary').hover(function(){
                                        $(this).css('color', '#e37777');
                                        $(this).css('cursor', 'pointer');
                                    }, 
                                    function(){
                                        $(this).css('color', '#ffcc00');
                                        $(this).css('cursor', 'normal');
                                    });
									
									
									$("#addMyNewDeviceConfirmBtn").off("click");
                                    $("#addMyNewDeviceConfirmBtn").click(function(){
										
									 $("#registerDeviceModal").show();	                  
										
									 var nameOpt =  document.getElementById('selectModel').options;
									 var selectednameOpt = document.getElementById('selectModel').selectedIndex;
									 gb_device =  document.getElementById('inputNameDeviceUser').value;
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
											name: nameOpt[selectednameOpt].value 
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
																	  kind: kind,
																	  latitude: gb_latitude,
																	  longitude: gb_longitude,
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
                                                                                                                                          k2 : gb_k2
																	},
																	 type: "POST",
																	 async: true,
																	 dataType: "json",
																	 timeout: 0,
																	 success: function (data) 
																	 {
																	//	 console.log(mydata);
																    //   res= JSON.parse(mydata); 
																		if(data["status"] === 'ko')
																		{
																			console.log("Error adding Device type");
																			console.log(data);
																			
																	 
																		}			 
																		else (data["status"] === 'ok')
																		{
																			console.log("Added Device");
																		
																				 buildMainTable(true);
																				 $("#addDeviceOkModalInnerDiv1").html('<h5>You have successfully registered your device - if you want to see more about configuration you can visit <b>' + "" + '</b> successfully registered</h5>');
																				 $("#addDeviceOkModal").modal('show');
																				 $("#registerDeviceModal").hide();
																				
																		} 
																		 
																	 },
																	 error: function (data)
																							{
																		   console.log("Error insert device");  
																		   console.log("Error status -- Ko result: " + JSON.stringify(data));
																	  
																		 
																	 } 
																 });
						 
													
													
													}
											 },
											 error: function (data) 
											 {
												 console.log("Ko result: " + JSON.stringify(data));
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
								
					
					
					
					
					
					
					
					
					
					
					
					
					
					
					
					
					
					
					
					
									/* This is loading validation when the cursor is on */
								
									
								   
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
									  
									   showAddDeviceModal();

                                   });
								
									
									/* This is a test validation starts on load*/
									
									//$("#addDeviceBtn").off("click");
                                    //$("#addDeviceBtn").click(showAddDeviceModal);
								
                                    $('#devicesTable thead').css("background", "rgba(0, 162, 211, 1)");
                                    $('#devicesTable thead').css("color", "white");
                                    $('#devicesTable thead').css("font-size", "1em");
                                }
                                else
                                {
                                  
                                }

                                //Istruzioni da eseguire comunque
								
								 $('#devicesTable tbody tr').each(function(i){
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
                                $('#devicesTable').css("border-bottom", "none");
                                $('span.pagination-info').hide();

                                $('#devicesTable tbody button.editDashBtn').off('hover');
                                $('#devicesTable tbody button.editDashBtn').hover(function(){
                                    $(this).css('background', '#ffcc00');
                                    $(this).parents('tr').find('td').eq(1).css('background', '#ffcc00');
                                }, 
                                function(){
                                    $(this).css('background', 'rgb(69, 183, 175)');
                                    $(this).parents('tr').find('td').eq(1).css('background', $(this).parents('td').css('background'));
                                });

                                $('#devicesTable button.editDashBtn').off('click');
                               
                                $('#devicesTable button.editDashBtn').click(function(){
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
									$('#inputLatitudeDeviceM').val(latitude);															  
									$('#inputLongitudeDeviceM').val(longitude);	
									$('#inputOwnerDeviceM').val(owner);	
									$('#inputFrequencyDeviceM').val(frequency);
									$('#selectVisibilityDeviceM').val(visibility);
									  
									  
									  
									  
											//var x = checkStatus(id, type, contextbroker, kind, protocol, format,  macaddress, model, producer, latitude, longitude, visibility, owner, frequency);
											//console.log(x);
											//$('#inputPropertiesDeviceM').val(x) ;
											
											
											
											//$('#inputPropertiesDeviceM').val($(this).parents('tr').attr('data-properties'));
											
										//	$('#inputAttributesDeviceM').val($(this).parents('tr').attr('data-attributes'));
											showEditDeviceModal();

				$.ajax({
					url: "../api/device.php",
					 data: {
						  action: "get_device_attributes", 
					       id: $(this).parents('tr').attr("data-id"),
					       contextbroker: $(this).parents('tr').attr("data-contextBroker")
						  },
					type: "GET",
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
                                        }
                                    });
                                });

                                $('#devicesTable button.delDashBtn').off('hover');
                                $('#devicesTable button.delDashBtn').hover(function(){
                                    $(this).css('background', '#ffcc00');
                                    $(this).parents('tr').find('td').eq(1).css('background', '#ffcc00');
                                }, 
                                function(){
                                    $(this).css('background', '#e37777');
                                    $(this).parents('tr').find('td').eq(1).css('background', $(this).parents('td').css('background'));
                                });

                                $('#devicesTable button.delDashBtn').off('click');
                                $('#devicesTable button.delDashBtn').click(function(){
                                    var id = $(this).parents("tr").find("td").eq(1).html();
									var contextBroker = $(this).parents("tr").attr("data-cb");
                                    $("#deleteDeviceModal div.modal-body").html('<div class="modalBodyInnerDiv"><span data-id = "' + id + '" data-contextBroker = "' + contextBroker + '">Do you want to confirm deletion of device <b>' + id + '</b>?</span></div>');
                                    $("#deleteDeviceModal").modal('show');
                                });
                            
									for (var func =0;func < functionality.length; func++)
										{
										  var element = functionality[func];
										  if (element.view=="popup")
										  {
											  if (element[loggedRole]==1)  
											   {   // console.log(element.view + loggedRole + " " + element[loggedRole] + " " + element["class"]); 
												   $(element["class"]).show();
											   }			   
											   else 
											   { 
												  $(element["class"]).hide();
												//  console.log(element.view + loggedRole + " " + element[loggedRole] + " " + element["class"]);
											   }
											}   
										}
							
							
							
							
							}
                        });
                    }
            });
        }
    



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
            if($(window).width() < 992)
            {
                $('#devicesTable').bootstrapTable('hideColumn', 'device');
                $('#devicesTable').bootstrapTable('hideColumn', 'value_type');
                //$('#devicesTable').bootstrapTable('hideColumn', 'uri');
                $('#devicesTable').bootstrapTable('hideColumn', 'status1');
                // $('#devicesTable').bootstrapTable('hideColumn', 'format');
                //$('#devicesTable').bootstrapTable('hideColumn', 'type');
            
            }
            else
            {
                $('#devicesTable').bootstrapTable('showColumn', 'device');
                $('#devicesTable').bootstrapTable('showColumn', 'value_type');
                //$('#devicesTable').bootstrapTable('showColumn', 'uri');
                $('#devicesTable').bootstrapTable('showColumn', 'status1');
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
        
        
        buildMainTable(false);
        
		$("#addMyNewDevice").click(function() {
		
			console.log("add new device");	
			$("#displayAllDeviceRow").hide();
			$("#addMyNewDeviceRow").show();

			$('#inputNameDeviceUser').val("");
			$('#inputTypeDeviceUser').val("");
			$('#inputLatitudeDeviceUser').val("");
			$('#inputLongitudeDeviceUser').val("");
			drawMapUser(43.78, 11.23);
			// showAddDeviceModal();
			
				 
			
		});
		
		$("#allDevice").click(function() {
			
			$("#displayAllDeviceRow").show();
			// $("#addDeviceModal").modal('show');
			$("#addMyNewDeviceRow").hide();
		});
	
		$("#myDevice").click(function() {
			
		 $("#displayAllDeviceRow").show();
		// $("#addDeviceModal").modal('show');
		 $("#addMyNewDeviceRow").hide();
		 
		});
		
								/* add lines related to attributes USER */			
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
				drawMap1(latitude,longitude, flag);
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
				drawMap1(latitude,longitude, flag);
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
		
    

			   /* DELETE DEVICE (DELETE FROM DB) */ 			
		
		
        $('#deleteDeviceConfirmBtn').off("click");
        $("#deleteDeviceConfirmBtn").click(function(){
			
            var id = $("#deleteDeviceModal span").attr("data-id");
			var contextbroker = $("#deleteDeviceModal span").attr("data-contextBroker");
            
	 
            $("#deleteDeviceModal div.modal-body").html("");
            $("#deleteDeviceCancelBtn").hide();
            $("#deleteDeviceConfirmBtn").hide();
            $("#deleteDeviceModal div.modal-body").append('<div id="deleteDeviceModalInnerDiv1" class="modalBodyInnerDiv"><h5>Device deletion in progress, please wait</h5></div>');
            $("#deleteDeviceModal div.modal-body").append('<div id="deleteDeviceModalInnerDiv2" class="modalBodyInnerDiv"><i class="fa fa-circle-o-notch fa-spin" style="font-size:36px"></i></div>');

            
            $.ajax({
                url: "../api/device.php",
				data:{
					action: "delete",
					id: id, 
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
                        $("#deleteDeviceModalInnerDiv1").html(data["msg"]);
                        $("#deleteDeviceModalInnerDiv2").html('<i class="fa fa-frown-o" style="font-size:42px"></i>');
                    }
                    else if(data["status"] === 'ok')
                    {
                        $("#deleteDeviceModalInnerDiv1").html('Device &nbsp; <b>' + id + '</b> &nbsp;deleted successfully');
                        $("#deleteDeviceModalInnerDiv2").html('<i class="fa fa-check" style="font-size:42px"></i>');
						
						 
						$('#dashboardTotNumberCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotNumberCnt .pageSingleDataCnt').html()) - 1);
                        if (data["active"])
						    $('#dashboardTotActiveCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotActiveCnt .pageSingleDataCnt').html()) - 1);
                        if (data["visibility"]=="public")          
	                           $('#dashboardTotPermCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotPermCnt .pageSingleDataCnt').html()) - 1);
						else
                              $('#dashboardTotPrivateCn .pageSingleDataCnt').html(parseInt($('#dashboardTotPrivateCn .pageSingleDataCnt').html()) - 1);

						// $('#dashboardTotNumberCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotNumberCnt .pageSingleDataCnt').html()) - 1);
                        // $('#dashboardTotActiveCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotActiveCnt .pageSingleDataCnt').html()) - 1);
						
                        setTimeout(function()
                        {
                            buildMainTable(true);
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
                }
            });
        });


		 $("#KeyOneDeviceUser").change(function() {UserKey();});
		 $("#KeyTwoDeviceUser").change(function() {UserKey();});

		
		
		$("#selectModel").on('click', function() {
	
		    var nameOpt =  document.getElementById('selectModel').options;
		    var selectednameOpt = document.getElementById('selectModel').selectedIndex;
 		    
			if (nameOpt[selectednameOpt].getAttribute("data_key")=="normal"){
			
			$("#sigFoxDeviceUserMsg").val("");
			
			gb_key1 = generateUUID();
			gb_key2 = generateUUID();

                        $("#KeyOneDeviceUserMsg").html("");
                        $("#KeyTwoDeviceUserMsg").html("");
			
                       $("#sigFoxDeviceUserMsg").html("These keys have been generated automatically for your device. Keep track of them. Details on <a href=\"https://www.snap4city.org/drupal/node/76\">info</a>");
			
                        $("#KeyOneDeviceUser").val(gb_key1);
			$("#KeyTwoDeviceUser").val(gb_key2);
			console.log("normal" +nameOpt[selectednameOpt].getAttribute("data_key")+gb_key1+gb_key2);							 
										 
			} else
			{
				$("#sigFoxDeviceUserMsg").html("Generate in your SigFox server the keys and report them here.  Details on <a href=\"https://www.snap4city.org/drupal/node/76\">info</a>");
				$("#KeyOneDeviceUser").val("");
                                $("#KeyTwoDeviceUser").val("");
			console.log("special "+ nameOpt[selectednameOpt].getAttribute("data_key")+gb_key1+gb_key2);							 

			UserKey();
			}
		
		
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
	
	function UserKey()
	{
			var message = null;
			var pattern = /^[a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{12}$/;
			
			var value1 = document.getElementById("KeyOneDeviceUser").value;
			var value2 = document.getElementById("KeyTwoDeviceUser").value;
			
			if((value1 === '') &&  (value2 === ''))
			{
				message = 'Specify Key for the selected option';
				document.getElementById("addMyNewDeviceConfirmBtn").disabled = true;
				$("#KeyOneDeviceUserMsg").css("color", "red");
				$("#KeyTwoDeviceUserMsg").css("color", "red");
				
			}
			else if(!pattern.test(value1) || !pattern.test(value2))
			{
				message = 'The Key should contain at least one special character and a number';
				document.getElementById("addMyNewDeviceConfirmBtn").disabled = true;
				$("#KeyOneDeviceUserMsg").css("color", "red");
				$("#KeyTwoDeviceUserMsg").css("color", "red");
			}
			else if(pattern.test(value1) && pattern.test(value2))
			{
				message = 'Ok';
				document.getElementById("addMyNewDeviceConfirmBtn").disabled = false;
				$("#KeyOneDeviceUserMsg").css("color", "#337ab7");
				$("#KeyTwoDeviceUserMsg").css("color", "#337ab7");
				$("#KeyOneDeviceUser").value = gb_key1;
			    $("#KeyTwoDeviceUser").value = gb_key2;
			}
			
			$("#KeyOneDeviceUserMsg").html(message);
			$("#KeyTwoDeviceUserMsg").html(message);
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
						




function drawMap1(latitude,longitude,flag){	
	var marker;
	
	if (flag ==0){
		var map = L.map('addLatLong').setView([latitude,longitude], 10);
		L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
			attribution: '&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap</a> contributors'
		}).addTo(map);
		window.node_input_map = map;
		
		
		setTimeout(function(){ map.invalidateSize()}, 400);
		
		//L.marker([latitude,longitude]).addTo(map).bindPopup(latitude + ',' + longitude);
		
			map.on("click", function (e) {
			
			var lat = e.latlng.lat;
			var lng = e.latlng.lng;
				lat = lat.toFixed(4);
				lng = lng.toFixed(4);
				console.log("Check the format:" + lat + " " + lng);
				
				 document.getElementById('inputLatitudeDevice').value = lat;
				 document.getElementById('inputLongitudeDevice').value = lng;
				  addDeviceConditionsArray['inputLatitudeDevice'] = true;
                  addDeviceConditionsArray['inputLongitudeDevice'] = true;
				 if (marker){
					 map.removeLayer(marker);
				 }
				 marker = new L.marker([lat,lng]).addTo(map).bindPopup(lat + ',' + lng);
			
			});
		

	} else if (flag==1){
		
		var map = L.map('editLatLong').setView([latitude,longitude], 10);
		L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
			attribution: '&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap</a> contributors'
		}).addTo(map);
		window.node_input_map = map;
		//L.marker([latitude,longitude]).addTo(map).bindPopup("Hi DEVICE");
		
		setTimeout(function(){ map.invalidateSize()}, 400);
		
		marker = new L.marker([latitude,longitude]).addTo(map).bindPopup(longitude + ',' + longitude);
	
			map.on("click", function (e) {
				
				var lat = e.latlng.lat;
				var lng = e.latlng.lng;
				lat = lat.toFixed(4);
				lng = lng.toFixed(4);
				console.log("Check the format:" + lat + " " + lng);
				
				document.getElementById('inputLatitudeDeviceM').value = lat;
				document.getElementById('inputLongitudeDeviceM').value = lng;
				 editDeviceConditionsArray['inputLatitudeDeviceM'] = true;
                 editDeviceConditionsArray['inputLongitudeDeviceM'] = true;
				 if (marker){
					 map.removeLayer(marker);
				 }
				 marker = new L.marker([lat,lng]).addTo(map).bindPopup(lat+ ',' + lng);
			
			});
		
	}
		
}

	
 function drawMap(latitude,longitude, device){ 
  // if (position==1)    
    map = L.map('addDeviceMapModalBodyShow').setView([latitude,longitude], 10);
    // else  map = L.map('addDeviceMapModalBody').setView([latitude,longitude], 10);
   L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
   attribution: '&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap</a> contributors'
  }).addTo(map);
   window.node_input_map = map;
   L.marker([latitude,longitude]).addTo(map).bindPopup();
   setTimeout(function(){ map.invalidateSize()}, 400);
  }

  	
 function drawMapUser(latitude,longitude){ 
	var marker;
 		var map = L.map('addDeviceMapModalBodyUser').setView([latitude,longitude], 10);
		L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
			attribution: '&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap</a> contributors'
		}).addTo(map);
		window.node_input_map = map;
			
		setTimeout(function(){ map.invalidateSize()}, 400);
	
			map.on("click", function (e) {
			
			var lat = e.latlng.lat;
			var lng = e.latlng.lng;
				lat = lat.toFixed(4);
				lng = lng.toFixed(4);
				console.log("Check the format:" + lat + " " + lng);
				
				 document.getElementById('inputLatitudeDeviceUser').value = lat;
				 document.getElementById('inputLongitudeDeviceUser').value = lng;
				 
				 if (marker){
					 map.removeLayer(marker);
				 }
				 marker = new L.marker([lat,lng]).addTo(map).bindPopup(lat + ',' + lng);
			
			});
  
  }



 function drawMapAll(data){
	var latitude = 43.7800;
	var longitude =11.2300;
	if (typeof map === 'undefined' || !map)	{
		map = L.map('searchDeviceMapModalBody').setView([latitude,longitude], 10);
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
        
         // console.log(resultsOut);
		 // $('#selectionResults').append(JSON.stringify(resultsOut));
		 // $('#addMap1').hide();
		 $('#addMap1').modal('hide');
		  buildMainTable(true, JSON.stringify(resultsOut));

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
                        // console.log(resultsOut);
						$('#addMap1').modal('hide');
						buildMainTable(true, JSON.stringify(resultsOut));
                    });
		
            });

            map.on('draw:deleted', function(e) {
                drawControl.addTo(map);
            });
     
   for (var i=0; i<data.length; i++) {
	var mylat=data[i].latitude;
    var mylong= data[i].longitude; 
	var myid = data[i].id; 
	
															
    marker = new L.marker([mylat,mylong]).addTo(map).bindPopup(myid);
	//console.log("Before My Marker: " + mylat);
	}
		setTimeout(function(){ map.invalidateSize()}, 400);
 }	
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
		
   

	




