
var gb_datatypes ="";
var gb_value_units ="";
var gb_value_types = "";

 		
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


function removeElementAt(parent,child) {
    var list = document.getElementById(parent);
	// var content = child.parentElement.parentElement.parentElement.innerHTML
  // console.log("elemento cancellato " + document.getElementById('deletedAttributes').innerHTML);
	if (parent=="editlistAttributes") 
	{     document.getElementById('deletedAttributes').appendChild(child.parentElement.parentElement.parentElement);}
	else list.removeChild(child.parentElement.parentElement.parentElement);
}


function drawAttributeMenu
(attrName, data_type, value_type, editable, value_unit, healthiness_criteria, value_refresh_rate, parent)
{
    options="";
    if (value_type!="") labelcheck= value_type;
    else labelcheck="";	
	for (var n=0; n < gb_value_types.length; n++)
	{
	  if (labelcheck == gb_value_types[n]) 
		 options += "<option value=\""+gb_value_types[n]+"\" selected>"+ gb_value_types[n]+ "</option>";
	  else options += "<option value=\""+gb_value_types[n]+"\">"+ gb_value_types[n]+ "</option>";
	}
	
    myunits="";// <option value=\"none\"></option>";
    if (value_unit!="") labelcheck= value_unit;
	else labelcheck="";
    for (var n=0; n < gb_value_units.length; n++)
	{
	  if (labelcheck == gb_value_units[n]) 
		 myunits += "<option value=\""+gb_value_units[n]+"\" selected>"+ gb_value_units[n]+ "</option>";
	  else myunits += "<option value=\""+gb_value_units[n]+"\">"+ gb_value_units[n]+ "</option>";
	}
	
	mydatatypes="";
    if (data_type!="") labelcheck= data_type;
	else labelcheck="";
    for (var n=0; n < gb_datatypes.length; n++)
	{
	  if (labelcheck == gb_datatypes[n]) 
		 mydatatypes += "<option value=\""+gb_datatypes[n]+"\" selected>"+ gb_datatypes[n]+ "</option>";
	  else mydatatypes += "<option value=\""+gb_datatypes[n]+"\">"+ gb_datatypes[n]+ "</option>";
	}
	
 return "<div class=\"row\" style=\"border:3px solid blue;\" ><div class=\"col-xs-6 col-md-3 modalCell\">" +
        "<div class=\"modalFieldCnt\"><input type=\"text\" class=\"modalInputTxt\""+
		"name=\"" +  attrName +  "\"  value=\"" + attrName + "\">" + 
        "</div><div class=\"modalFieldLabelCnt\">Value Name</div></div>"+
			
		"<div class=\"col-xs-6 col-md-3 modalCell\"><div class=\"modalFieldCnt\">"+
		"<select class=\"modalInputTxt\" name=\""+ attrName+"-type" +
		"\">" + mydatatypes + 
		"</select></div><div class=\"modalFieldLabelCnt\">Data Type</div></div>" + 
	
		"<div class=\"col-xs-6 col-md-3 modalCell\"><div class=\"modalFieldCnt\">" +
		"<select class=\"modalInputTxt\" name=\""+ value_type +
		"\">" + 		 options + 
		"</select></div><div class=\"modalFieldLabelCnt\">Value Type</div></div>" +
		
		"<div class=\"col-xs-6 col-md-3 modalCell\"><div class=\"modalFieldCnt\">" +
		"<select class=\"modalInputTxt\" name=\""+ editable +
		"\">" + 
		"<option value='0' default>false</option>" +
		"<option value='1'>true</option> </select>" +
		"</div><div class=\"modalFieldLabelCnt\">Editable</div></div>"+
		
		"<div class=\"col-xs-6 col-md-3 modalCell\"><div class=\"modalFieldCnt\">" +
		"<select class=\"modalInputTxt\" name=\""+ value_unit +
		"\">" + 
		 myunits + 
		"</select></div><div class=\"modalFieldLabelCnt\">Value Unit</div></div>"+
   		
		"<div class=\"col-xs-6 col-md-3 modalCell\"><div class=\"modalFieldCnt\">" +
		"<select class=\"modalInputTxt\" name=\"" + healthiness_criteria +
		"\" \>"+ 
			"<option value=\"refresh_rate\">Refresh rate</option>" +
			"<option value=\"different_values\">Different Values</option>" +
			"<option value=\"within_bounds\">Within bounds</option>" +
	       "</select></div><div class=\"modalFieldLabelCnt\">Healthiness Criteria</div></div>"+
		
		"<div class=\"col-xs-6 col-md-3 modalCell\"><div class=\"modalFieldCnt\">" +
		"<input type=\"text\" class=\"modalInputTxt\" name=\""+ value_refresh_rate +
		"\" value=\"" + value_refresh_rate + "\"></div><div class=\"modalFieldLabelCnt\">Healthiness_Value</div></div>"+
		
		
		"<div class=\"col-xs-6 col-md-3 modalCell\"><div class=\"modalFieldCnt\">" +
		//"<i class=\"fa fa-minus-square\" onclick=\"removeElementAt('" + parent + "',this); return true;\"  style=\"font-size:36px; color: #ffcc00\"></i></div></div></div>";
	    "<button class=\"btn btn-warning\" onclick=\"removeElementAt('" + parent + "',this); return true;\">Remove Value</button></div></div></div>";

		
}		


     
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
			  mydata = {action: "get_all_device"};
			}
			else
			{
			  mydata = {action: "get_subset_device", select : selected};
			}
            

            $.ajax({
                url: "../api/device.php",
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
									field: 'id',
									title: 'ID',
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
                                field: 'contextBroker',
								title: 'ContextBroker',
								filterControl: 'select',
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
                                field: 'protocol',
								title: 'Protocol',
								filterControl: 'select',
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
                                field: 'format',
								title: 'Format',
								filterControl: 'select',
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
                                field: 'devicetype',
								title: 'Device Type',
								filterControl: 'input',
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
                                field: 'visibility',
								title: 'Visibility',
								filterControl: 'input',
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
								// console.log("prop" +row.mandatoryproperties + "value" +row.mandatoryvalues);
								if (row.mandatoryproperties==1 && row.mandatoryvalues==1)
								return '<button type="button" class="btn btn-success"></button>';
								else 
								return '<button type="button" class="btn btn-warning"></button>';
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
                                    return '<div class="addMapBtn"><i  data-toggle="modal" data-target="#addMapShow" onclick="drawMap(\''+ row.latitude +"\',\'" + row.longitude + '\',1)\" class="fa fa-globe"  style=\"font-size:36px; color: #0000ff\"></i></div>';
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
                            uniqueId: "id",
                            striped: false,
                            searchTimeOut: 250,
                            classes: "table table-hover table-no-bordered",
							detailView: detailView,
							detailFormatter: function(index, row, element) {
                            return 'Kind: ' + data[index].kind   + ' | type: ' + data[index].type   + ' | MAC: ' + data[index].macaddress + ' | Model: ' + data[index].model + " | Producer: " + data[index].producer + " | Longitude: " + data[index].longitude + " | Latitude: " + data[index].latitude;
							},
                            rowAttributes: function(row, index){
                            return {
                                "data-id": row.id,
                                "data-devicetype": row.devicetype,
                                "data-kind": row.kind,
                                "data-contextBroker": row.contextBroker,
                                "data-uri": row.uri,
                                "data-protocol": row.protocol,
                                "data-format": row.format,
                                "data-created": row.created,
                                "data-macaddress": row.macaddress,
								"data-model": row.model,
                                "data-producer": row.producer,
                                "data-latitude": row.latitude,
                                "data-longitude": row.longitude,
                                "data-properties": row.properties,
                                "data-attributes": row.attributes,
								"data-visibility": row.visibility,
                                "data-owner": row.owner,
                                "data-frequency": row.frequency
                            };
						},
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
											url: "../api/device.php",
											data: {
											action: "get_all_device_latlong"
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
															
														   $("#addMap1").modal('show');
														    //drawMapAll(mylat, mylong);
                                                            drawMapAll(data);
														}
											 },
											 error: function (data) 
											 {
												 console.log("Ko result: " + data);
											 }
											
										});		
									
									
                                    //  $("#addMap").modal('show');
									//  var  mylat = [45.4350, 43.7845, 43.7812, 43.7856, 43.7756];
									//	var  mylong = [9.2312, 11.2325, 11.2112, 11.2356, 11.2378];
									//  drawMapAll(mylat, mylong);
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
									
									
									
									
									
									
									
									
									
										/* add lines related to attributes*/			
									$("#addAttrBtn").off("click");
									$("#addAttrBtn").click(function(){
									   console.log("#addAttrBtn");							   
									   content = drawAttributeMenu("","", "", "", "", "", "",  'addlistAttributes');
									    addDeviceConditionsArray['addlistAttributes'] = true;
									   //console.log("contenuto drawAttr" +content);
									   $('#addlistAttributes').append(content);
									});					
									/* add lines related to attributes in case of edit*/
									$("#addAttrMBtn").off("click");
									$("#addAttrMBtn").click(function(){				
									   console.log("#addAttrMBtn");					
									   content = drawAttributeMenu("","", "", "", "", "", "", 'addlistAttributesM');
									    editDeviceConditionsArray['addlistAttributesM'] = true;
									   $('#addlistAttributesM').append(content);
									});	
									
									/* Delete lines related to attributes */
									
									$("#attrNameDelbtn").off("click");
									$("#attrNameDelbtn").on("click", function(){
										console.log("#attrNameDelbtn");	
										$(this).parent('tr').remove();
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
									  
									  
									  
									  
											var x = checkStatus(id, type, contextbroker, kind, protocol, format,  macaddress, model, producer, latitude, longitude, visibility, owner, frequency);
											console.log(x);
											
											
											
											
											//$('#inputPropertiesDeviceM').val($(this).parents('tr').attr('data-properties'));
											$('#inputPropertiesDeviceM').val(x) ;
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
									var contextBroker = $(this).parents("tr").find("td").eq(2).html();
                                    $("#deleteDeviceModal div.modal-body").html('<div class="modalBodyInnerDiv"><span data-id = "' + id + '" data-contextBroker = "' + contextBroker + '">Do you want to confirm deletion of device <b>' + id + '</b>?</span></div>');
                                    $("#deleteDeviceModal").modal('show');
                                });
                            
									for (var func =0;func < functionality.length; func++)
										{
										  var element = functionality[func];
										  if (element.view=="popup")
										  {
											  if (element[loggedRole]==1)  
											   {   console.log(element.view + loggedRole + " " + element[loggedRole] + " " + element["class"]); 
												   $(element["class"]).show();
											   }			   
											   else 
											   { 
												  $(element["class"]).hide();
												 console.log(element.view + loggedRole + " " + element[loggedRole] + " " + element["class"]);
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
                $('#devicesTable').bootstrapTable('hideColumn', 'id');
                $('#devicesTable').bootstrapTable('hideColumn', 'contextBroker');
                //$('#devicesTable').bootstrapTable('hideColumn', 'uri');
                $('#devicesTable').bootstrapTable('hideColumn', 'protocol');
                $('#devicesTable').bootstrapTable('hideColumn', 'format');
                //$('#devicesTable').bootstrapTable('hideColumn', 'type');
            
            }
            else
            {
                $('#devicesTable').bootstrapTable('showColumn', 'id');
                $('#devicesTable').bootstrapTable('showColumn', 'contextBroker');
                //$('#devicesTable').bootstrapTable('showColumn', 'uri');
                $('#devicesTable').bootstrapTable('showColumn', 'protocol');
                $('#devicesTable').bootstrapTable('showColumn', 'format');
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
			   {   console.log(loggedRole + " " + element[loggedRole] + " " + element["class"]); 
				   $(element["class"]).show();
			   }			   
			   else 
			   { 
				 $(element["class"]).hide();
				 console.log($(element.class));
				 console.log(loggedRole + " " + element[loggedRole] + " " + element["class"]);
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
			drawMap(43.78, 11.23,0);
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
        
		});  // end of ready-state
	
		
        
	
		
        
        /*   ADD NEW DEVICE  (INSERT INTO DB) */
		
        $('#addNewDeviceConfirmBtn').off("click");
        $("#addNewDeviceConfirmBtn").click(function(){
		
		
		    mynewAttributes = [];
			num1 = document.getElementById('addlistAttributes').childElementCount;
            for (var m=0; m< num1; m++)
			{
			  //var selOpt= document.getElementById('addlistAttributes').childNodes[m].childNodes[2].childNodes[0].childNodes[0].options;
  			  //var selIndex= document.getElementById('addlistAttributes').childNodes[m].childNodes[2].childNodes[0].childNodes[0].selectedIndex;
			  var newatt= {value_name: document.getElementById('addlistAttributes').childNodes[m].childNodes[0].childNodes[0].childNodes[0].value.trim(), 
			            data_type:document.getElementById('addlistAttributes').childNodes[m].childNodes[1].childNodes[0].childNodes[0].value.trim(),
						value_type:document.getElementById('addlistAttributes').childNodes[m].childNodes[2].childNodes[0].childNodes[0].value.trim(),
						editable:document.getElementById('addlistAttributes').childNodes[m].childNodes[3].childNodes[0].childNodes[0].value.trim(),
						value_unit:document.getElementById('addlistAttributes').childNodes[m].childNodes[4].childNodes[0].childNodes[0].value.trim(),
						healthiness_criteria: document.getElementById('addlistAttributes').childNodes[m].childNodes[5].childNodes[0].childNodes[0].value.trim(),
						healthiness_value: document.getElementById('addlistAttributes').childNodes[m].childNodes[6].childNodes[0].childNodes[0].value.trim()};
				if (newatt.value_name!="" && newatt.data_type!="" && newatt.value_type!="" && newatt.editable!="" && newatt.value_unit!="" && newatt.healthiness_criteria!="" && newatt.healthiness_value!="") mynewAttributes.push(newatt);
			}
            document.getElementById('addlistAttributes').innerHTML = "";			
		
		
            $("#addDeviceModalTabs").hide();
			$('#addDeviceModal div.modalCell').hide();
            $("#addDeviceModalFooter").hide();
			$("#addAttrBtn").hide();
            $('#addDeviceLoadingMsg').show();
            $('#addDeviceLoadingIcon').show();

			
		
			
             $.ajax({
                 url: "../api/device.php",
                 data:{
					  action: "insert",   
					  attributes: JSON.stringify(mynewAttributes),
					  id: $('#inputNameDevice').val(),
					  type: $('#inputTypeDevice').val(),
					  kind: $('#selectKindDevice').val(),
					  contextbroker: $('#selectContextBroker').val(),
					  protocol: $('#selectProtocolDevice').val(),
					  format: $('#selectFormatDevice').val(),
					  mac: $('#inputMacDevice').val(),
					  model: $('#inputModelDevice').val(),
					  producer: $('#inputProducerDevice').val(),
					  latitude: $('#inputLatitudeDevice').val(),
					  longitude: $('#inputLongitudeDevice').val(),
					  visibility: $('#selectVisibilityDevice').val(),
					  owner: "marco",
					  frequency: $('#inputFrequencyDevice').val()
					 },
                 type: "GET",
                 async: true,
                 dataType: "text",
				 timeout: 0,
                 success: function (mydata) 
                 {
				    res= JSON.parse(mydata); 
					if(res["status"] === 'ko')
                    {
                        console.log("Error adding Device type");
                        console.log(data);
						$('#addDeviceLoadingMsg').hide();
                        $('#addDeviceLoadingIcon').hide();
                        $('#addDeviceKoMsg').show();
                        $('#addDeviceKoIcon').show();
                 
                        setTimeout(function(){
							
                            $('#addDeviceKoMsg').hide();
                            $('#addDeviceKoIcon').hide();
                            $('#addDeviceModalTabs').show();
                            $('#addDeviceModal div.modalCell').show();
                            $('#addDeviceModalFooter').show();
                        }, 3000);
                    }			 
					else (res["status"] === 'ok')
                    {
						console.log("Added Device");
						$('#addDeviceLoadingMsg').hide();
                        $('#addDeviceLoadingIcon').hide();
                        $('#addDeviceKoMsg').hide();
                        $('#addDeviceKoIcon').hide();
                        $('#addDeviceOkMsg').show();
                        $('#addDeviceOkIcon').show();
                         
						$('#dashboardTotNumberCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotNumberCnt .pageSingleDataCnt').html()) + 1);
                        if (res["active"])
						    $('#dashboardTotActiveCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotActiveCnt .pageSingleDataCnt').html()) + 1);
                        if (res["visibility"]=="public")          
	                           $('#dashboardTotPermCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotPermCnt .pageSingleDataCnt').html()) + 1);
						else
                              $('#dashboardTotPrivateCn .pageSingleDataCnt').html(parseInt($('#dashboardTotPrivateCn .pageSingleDataCnt').html()) + 1);

						   
					 
					 
				// dashboardTotPermCnt
					setTimeout(function(){
                            $('#addDeviceModal').modal('hide');
                            buildMainTable(true);

                            setTimeout(function(){
 							   
								  $('#addDeviceOkMsg').hide();
                                  $('#addDeviceOkIcon').hide();
								  
								  $('#inputNameDevice').val("");
								  $('#inputTypeDevice').val("");
								  $('#selectContextBroker').val("NULL");
								  $('#inputUriDevice').val("");
								  $('#selectProtocolDevice').val("NULL");
								  $('#selectFormatDevice').val("NULL");
								  $('#createdDateDevice').val("");
								  $('#inputMacDevice').val("");
								  $('#inputModelDevice').val("");
								  $('#inputProducerDevice').val("");
								  $('#inputLatitudeDevice').val("");
								  $('#inputLongitudeDevice').val("");
								  $('#inputLongitudeDevice').val("");
								  $('#selectVisibilityDevice').val("NULL");
								  $('#inputOwnerDevice').val("");
								  $('#inputFrequencyDevice').val("");
																
								  $('#addDeviceModalTabs').show();
                                  $('#addDeviceModal div.modalCell').show();
                                  $('#addDeviceModalFooter').show();
                            }, 500);
                        }, 3000);						
						
                    } 
					 
                 },
                 error: function (mydata)
                                        {
					   console.log("Error insert device");  
					   console.log("Error status -- Ko result: " + JSON.stringify(mydata));
                        $('#addDeviceLoadingMsg').hide();
                        $('#addDeviceLoadingIcon').hide();
                        $('#addDeviceKoMsg').show();
                        $('#addDeviceKoIcon').show();
                        setTimeout(function(){
                            $('#addDeviceKoMsg').hide();
                            $('#addDeviceKoIcon').hide();
                            $('#addDeviceModalTabs').show();
                            $('#addDeviceModal div.modalCell').show();
                            $('#addDeviceModalFooter').show();
                        }, 3000);
                 }
             });
        });
 

       /*  $('#selectionResults').change(function(){
		   
		   console.log("entrato selectionResult");
		   console.log($(this).html());
		   if ( $(this).html() == "")
   		         buildMainTable(true);
           else 
		          buildMainTable(true, $(this).html());
          $(this).html(""); 		   
        });*/
		
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
					contextbroker : contextbroker
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
        
		
		 /* EDIT DEVICE  */ 
		 
        $('#editDeviceConfirmBtn').off("click");
        $("#editDeviceConfirmBtn").click(function(){
           // $("#editDeviceModalBody").hide();
           // $("#editDeviceModalFooter").hide();
           // $("#editDeviceModalUpdating").show();
			
			mynewAttributes = [];
			num1 = document.getElementById('addlistAttributesM').childElementCount;
			//console.log(num1);
            for (var m=0; m< num1; m++)
			{
			  //var selOpt= document.getElementById('addlistAttributesM').childNodes[m].childNodes[2].childNodes[0].childNodes[0].options;
  			  //var selIndex= document.getElementById('addlistAttributesM').childNodes[m].childNodes[2].childNodes[0].childNodes[0].selectedIndex;
			var newatt= {value_name: document.getElementById('addlistAttributesM').childNodes[m].childNodes[0].childNodes[0].childNodes[0].value.trim(), 
			            data_type:document.getElementById('addlistAttributesM').childNodes[m].childNodes[1].childNodes[0].childNodes[0].value.trim(),
						value_type:document.getElementById('addlistAttributesM').childNodes[m].childNodes[2].childNodes[0].childNodes[0].value.trim(),
						editable:document.getElementById('addlistAttributesM').childNodes[m].childNodes[3].childNodes[0].childNodes[0].value.trim(),
						value_unit:document.getElementById('addlistAttributesM').childNodes[m].childNodes[4].childNodes[0].childNodes[0].value.trim(),
						healthiness_criteria: document.getElementById('addlistAttributesM').childNodes[m].childNodes[5].childNodes[0].childNodes[0].value.trim(),
						healthiness_value: document.getElementById('addlistAttributesM').childNodes[m].childNodes[6].childNodes[0].childNodes[0].value.trim()};
                                mynewAttributes.push(newatt);			 
			}
			
            myAttributes= [];
			num= document.getElementById('editlistAttributes').childElementCount;
            for (var j=0; j< num; j++)
			{
			  var selectOpt_value_type= document.getElementById('editlistAttributes').childNodes[j].childNodes[2].childNodes[0].childNodes[0].options;
  			  var selectIndex_value_type= document.getElementById('editlistAttributes').childNodes[j].childNodes[2].childNodes[0].childNodes[0].selectedIndex;
			  
			  var selectOpt_data_type= document.getElementById('editlistAttributes').childNodes[j].childNodes[1].childNodes[0].childNodes[0].options;
  			  var selectIndex_data_type= document.getElementById('editlistAttributes').childNodes[j].childNodes[1].childNodes[0].childNodes[0].selectedIndex;
			  
			  var selectOpt_value_unit= document.getElementById('editlistAttributes').childNodes[j].childNodes[4].childNodes[0].childNodes[0].options;
  			  var selectIndex_value_unit= document.getElementById('editlistAttributes').childNodes[j].childNodes[4].childNodes[0].childNodes[0].selectedIndex;
			  
			  var selectOpt_hc= document.getElementById('editlistAttributes').childNodes[j].childNodes[5].childNodes[0].childNodes[0].options;
  			  var selectIndex_hc= document.getElementById('editlistAttributes').childNodes[j].childNodes[5].childNodes[0].childNodes[0].selectedIndex;
			  
			  var selectOpt_edit= document.getElementById('editlistAttributes').childNodes[j].childNodes[3].childNodes[0].childNodes[0].options;
  			  var selectIndex_edit= document.getElementById('editlistAttributes').childNodes[j].childNodes[3].childNodes[0].childNodes[0].selectedIndex;
			  
			  var att= {value_name: document.getElementById('editlistAttributes').childNodes[j].childNodes[0].childNodes[0].childNodes[0].value.trim(), 
			       data_type:selectOpt_data_type[selectIndex_data_type].value,
				   value_type:selectOpt_value_type[selectIndex_value_type].value,
				   editable:selectOpt_edit[selectIndex_edit].value,
				   value_unit:selectOpt_value_unit[selectIndex_value_unit].value,
				   healthiness_criteria: selectOpt_hc[selectIndex_hc].value,
				   healthiness_value: document.getElementById('editlistAttributes').childNodes[j].childNodes[6].childNodes[0].childNodes[0].value.trim()};
                         myAttributes.push(att);
			  
			}
			 

            mydeletedAttributes= [];
			numDel= document.getElementById('deletedAttributes').childElementCount;
            for (var j=0; j< numDel; j++)
			{
			  var selectOpt_value_type= document.getElementById('deletedAttributes').childNodes[j].childNodes[2].childNodes[0].childNodes[0].options;
  			  var selectIndex_value_type= document.getElementById('deletedAttributes').childNodes[j].childNodes[2].childNodes[0].childNodes[0].selectedIndex;
			  
			  var selectOpt_data_type= document.getElementById('deletedAttributes').childNodes[j].childNodes[1].childNodes[0].childNodes[0].options;
  			  var selectIndex_data_type= document.getElementById('deletedAttributes').childNodes[j].childNodes[1].childNodes[0].childNodes[0].selectedIndex;
			  
			  var selectOpt_value_unit= document.getElementById('deletedAttributes').childNodes[j].childNodes[4].childNodes[0].childNodes[0].options;
  			  var selectIndex_value_unit= document.getElementById('deletedAttributes').childNodes[j].childNodes[4].childNodes[0].childNodes[0].selectedIndex;
			  
			  var selectOpt_hc= document.getElementById('deletedAttributes').childNodes[j].childNodes[5].childNodes[0].childNodes[0].options;
  			  var selectIndex_hc= document.getElementById('deletedAttributes').childNodes[j].childNodes[5].childNodes[0].childNodes[0].selectedIndex;
			  
			  var selectOpt_edit= document.getElementById('deletedAttributes').childNodes[j].childNodes[3].childNodes[0].childNodes[0].options;
  			  var selectIndex_edit= document.getElementById('deletedAttributes').childNodes[j].childNodes[3].childNodes[0].childNodes[0].selectedIndex;
			  
			  var att= {value_name: document.getElementById('deletedAttributes').childNodes[j].childNodes[0].childNodes[0].childNodes[0].value.trim(), 
			       data_type:selectOpt_data_type[selectIndex_data_type].value,
				   value_type:selectOpt_value_type[selectIndex_value_type].value,
				   editable:selectOpt_edit[selectIndex_edit].value,
				   value_unit:selectOpt_value_unit[selectIndex_value_unit].value,
				   healthiness_criteria: selectOpt_hc[selectIndex_hc].value,
				   healthiness_value: document.getElementById('deletedAttributes').childNodes[j].childNodes[6].childNodes[0].childNodes[0].value.trim()};
                         mydeletedAttributes.push(att);
			}


			
   			   document.getElementById('editlistAttributes').innerHTML = ""; 
		       document.getElementById('addlistAttributesM').innerHTML = ""; 
               document.getElementById('deletedAttributes').innerHTML = "";  

			
			$("#editDeviceModalTabs").hide();
			$('#editDeviceModal div.modalCell').hide();
            $("#editDeviceModalFooter").hide();
			$("#addAttrMBtn").hide();
			
			
            $('#editDeviceLoadingMsg').show();
            $('#editDeviceLoadingIcon').show();
            // console.log(JSON.stringify(deviceJson));
				
				var myowner ="";
				if  ($('#selectVisibilityDeviceM').val()=="public") myowner="";
				else myowner="marco";
				console.log("valore di owner" + myowner);

             $.ajax({
                 url: "../api/device.php",
                 data:{
				 action: "update", 
				 newattributes: JSON.stringify(mynewAttributes),
				 attributes: JSON.stringify(myAttributes),
				 deleteattributes: JSON.stringify(mydeletedAttributes), 
				 id: $('#inputNameDeviceM').val(),
			     type: $('#inputTypeDeviceM').val(),
			     kind: $('#selectKindDeviceM').val(),
			     contextbroker: $('#selectContextBrokerM').val(),
			     uri: $('#inputUriDeviceM').val(),
			     protocol: $('#selectProtocolDeviceM').val(),
			     format: $('#selectFormatDeviceM').val(),
			     mac: $('#inputMacDeviceM').val(),
			     model: $('#inputModelDeviceM').val(),
			     producer: $('#inputProducerDeviceM').val(),
			     latitude: $('#inputLatitudeDeviceM').val(),
			     longitude: $('#inputLongitudeDeviceM').val(),
				 visibility: $('#selectVisibilityDeviceM').val(),
			     owner:myowner,
			     frequency: $('#inputFrequencyDeviceM').val()
				 },
                 type: "POST",
                 async: true,
                 success: function (data) 
                 {
				   console.log("Marco edit Data " + data);

                    if(data["status"] === 'ko')
                    {
                        console.log("Error editing Device type");
                        console.log(data);
						$('#editDeviceLoadingMsg').hide();
                        $('#editDeviceLoadingIcon').hide();
                        $('#editDeviceLoadingIcon').hide();
                        $('#editDeviceKoMsg').show();
                        $('#editDeviceKoIcon').show();
                     
                        setTimeout(function(){
                            $('#editDeviceKoMsg').hide();
                            $('#editDeviceKoIcon').hide();
                            $('#editDeviceModalTabs').show();
                            $('#editDeviceModal div.modalCell').show();
                            $('#editDeviceModalFooter').show();
                        }, 3000);
                    }
					 
					else if (data["status"] === 'ok')
                    {
							
						$('#editDeviceLoadingMsg').hide();
                        $('#editDeviceLoadingIcon').hide();
                        $('#editDeviceOkMsg').show();
                        $('#editDeviceOkIcon').show();
                        			
						$("#addingDeviceModalInnerDiv1").html('Device &nbsp; <b>' + name + '</b> successfully Updated');
                        $("#addingDeviceModalInnerDiv2").html('<i class="fa fa-check" style="font-size:42px"></i>');
	                 
						setTimeout(function(){
                            $('#addDeviceModal').modal('hide');
                            buildMainTable(true);

                            setTimeout(function(){
                               // $('#addDeviceOkMsg').hide();
                               // $('#addDeviceOkIcon').hide();
								  $('#editDeviceOkMsg').hide();
                                  $('#editDeviceOkIcon').hide();
								  
								  $('#inputNameDevice').val("");
								  $('#inputTypeDevice').val("");
								  $('#selectKindDevice').val("");
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
								  $('#selectVisibilityDevice').val(),
								  $('#inputOwnerDevice').val(),
							      $('#inputFrequencyDevice').val()
									
                                   $('#editDeviceModal').hide();
							       setTimeout(updateDeviceTimeout, 100);	  
								  
                            }, 100);
                        }, 100);
						
						
			} else {console.log(data);}
					 
                 },
                 error: function (data) 
                 {
                     console.log("Ko result: " + JSON.stringify(data));
                     $("#editUserModal").modal('hide');
                     $("#editUserKoModalInnerDiv1").html(data["msg"]);
                     $("#editUserKoModal").modal('show');
                     $("#editUserModalUpdating").hide();
                     $("#editUserModalBody").show();
                     $("#editUserModalFooter").show();
                 }
             });
        });
        

		
		$("#editDeviceCancelBtn").off("click");
        $("#editDeviceCancelBtn").on('click', function(){
		
		   	   document.getElementById('editlistAttributes').innerHTML = ""; 
		       document.getElementById('addlistAttributesM').innerHTML = ""; 
               document.getElementById('deletedAttributes').innerHTML = "";  

		
		});
		
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

		
	
	/* To Validate the Value Tab Not Yet finished 
	
$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
	var target = $(e.target).attr("href");
	var message = null;
	var num1 = document.getElementById('addlistAttributes').childElementCount;
	
	if ((target == '#addSchemaTabDevice')) {	
		if (num1 == 0) {
			if (document.getElementById('#addAttrBtn').clicked == true) {
				console.log(num1);
				$("#addlistAttributesMsg").css("color", "red");
				message = 'At least one value is required/mandatory';
				$('#addlistAttributesMsg').html(message);
				$("#addNewDeviceConfirmBtn").attr("disabled", true);
			} else if (document.getElementById('#addAttrBtn').clicked == false){
				console.log("Clicked");
				$("#addlistAttributesMsg").css("color", "red");
				message = 'Value Name is mandatory';
				$('#addlistAttributesMsg').html(message);
				$("#addNewDeviceConfirmBtn").attr("disabled", false);
			} else {}
		} else {
			$("#addlistAttributesMsg").css("color", "#337ab7");
			message = 'Ok';
			$('#addlistAttributesMsg').html(message);
			 $("#addNewDeviceConfirmBtn").attr("disabled", false);
		}
	} else {//nothing
	}
});
*/
		
	
	/* Related to the Map */

//add this code to the jquery
						
$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
	var target = $(e.target).attr("href");
	if ((target == '#addGeoPositionTabDevice')) {
		console.log("Elf");
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
		console.log("Elf");
			var latitude = $("#inputLatitudeDeviceM").val(); 
			var longitude = $("#inputLongitudeDeviceM").val();
			var flag = 1;
		drawMap1(latitude,longitude, flag);
	} else {//nothing
	}
});

function drawMap1(latitude,longitude,flag){	
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
				
				 document.getElementById('inputLatitudeDevice').value = lat;
				 document.getElementById('inputLongitudeDevice').value = lng;
				  addDeviceConditionsArray['inputLatitudeDevice'] = true;
                 addDeviceConditionsArray['inputLongitudeDevice'] = true;
				 if (marker){
					 map.removeLayer(marker);
				 }
				 marker = new L.marker([lat,lng]).addTo(map).bindPopup(lat, lng);
			
			});
		

	} else if (flag==1){
		
		map = L.map('editLatLong').setView([latitude,longitude], 10);
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
				
				document.getElementById('inputLatitudeDeviceM').value = lat;
				document.getElementById('inputLongitudeDeviceM').value = lng;
				 editDeviceConditionsArray['inputLatitudeDeviceM'] = true;
                 editDeviceConditionsArray['inputLongitudeDeviceM'] = true;
				 if (marker){
					 map.removeLayer(marker);
				 }
				 marker = new L.marker([lat,lng]).addTo(map).bindPopup(lat, lng);
			
			});
		
	}
		
}

	
 function drawMap(latitude,longitude, position){ 
   if (position==1)    map = L.map('addDeviceMapModalBodyShow').setView([latitude,longitude], 10);
    else  map = L.map('addDeviceMapModalBody').setView([latitude,longitude], 10);
   L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
   attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
  }).addTo(map);
   window.node_input_map = map;
   L.marker([latitude,longitude]).addTo(map).bindPopup("Hi DEVICE");
   setTimeout(function(){ map.invalidateSize()}, 400);
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
															
    marker = new L.marker([mylat,mylong]).addTo(map).bindPopup(mylat);
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
		
		
		var div = document.createElement("div");
		
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

  
	