

    $(document).ready(function () 
    {
        var sessionEndTime = "<?php echo $_SESSION['sessionEndTime']; ?>";
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
                $('#valuesTable').bootstrapTable('hideColumn', 'cb');
                $('#valuesTable').bootstrapTable('hideColumn', 'device');
                $('#valuesTable').bootstrapTable('hideColumn', 'value_name');
                $('#valuesTable').bootstrapTable('hideColumn', 'value_type');
                $('#valuesTable').bootstrapTable('hideColumn', 'healthiness_criteria');
                $('#valuesTable').bootstrapTable('hideColumn', 'value_refresh_rate');
               
            }
            else
            {
                $('#valuesTable').bootstrapTable('showColumn', 'cb');
                $('#valuesTable').bootstrapTable('showColumn', 'device');
                $('#valuesTable').bootstrapTable('showColumn', 'value_name');
                $('#valuesTable').bootstrapTable('showColumn', 'value_type');
                $('#valuesTable').bootstrapTable('showColumn', 'healthiness_criteria');
                $('#valuesTable').bootstrapTable('showColumn', 'value_refresh_rate');
    		
            }
        });
        
        $('#valueLink .mainMenuItemCnt').addClass("mainMenuItemCntActive");
        $('#mobMainMenuPortraitCnt #valueLink .mobMainMenuItemCnt').addClass("mainMenuItemCntActive");
        $('#mobMainMenuLandCnt #valueLink .mobMainMenuItemCnt').addClass("mainMenuItemCntActive");
        
        var admin = "<?= $_SESSION['loggedRole'] ?>";
        var existingPoolsJson = null;
        var internalDest = false;
        var tableFirstLoad = true;
        
        buildMainTable(false);
        setGlobals(admin, existingPoolsJson);
        
		
	

        /*   ADD NEW VALUE CONFIRMATION */
		
		/*
        $('#addNewValueConfirmBtn').off("click");
        $("#addNewValueConfirmBtn").click(function(){
	        $("#addValueModalTabs").hide();
			$('#addValueModal div.modalCell').hide();
            $("#addValueModalFooter").hide();
            $('#addValueLoadingMsg').show();
            $('#addValueLoadingIcon').show();

             $.ajax({
                 url: "process-form.php",
                 data:{
					  addValue: true,
					  
					  selectContextBroker: $('#selectContextBroker').val(),
		 			  inputNameDevice: $('#inputNameDevice').val(),
					  inputValueNameDevice: $('#inputValueNameDevice').val(),
					  selectDataType: $('#selectDataType').val(),
					  selectValueType: $('#selectValueType').val(),
					  inputEditableValue: $('#inputEditableValue').val(),
					  selectValueUnit: $('#selectValueUnit').val(),
					  selectHealthinessCriteria: $('#selectHealthinessCriteria').val(),
					  inputHealthinessValue: $('#inputHealthinessValue').val(),
					  inputOrder: $('#inputOrder').val()					 
					  },
                 type: "POST",
                 async: true,
				
                 success: function (data) 
                 {
					console.log("Elf result: " + data);
				 
					if(data.endsWith('Ko'))
                    {
                        console.log("Error adding value");
                        console.log(data);
						$('#addValueLoadingMsg').hide();
                        $('#addValueLoadingIcon').hide();
                        $('#addValueKoMsg').show();
                        $('#addValueKoIcon').show();
                      
                        setTimeout(function(){
                            $('#addValueKoMsg').hide();
                            $('#addValueKoIcon').hide();
                            $('#addValueModalTabs').show();
                            $('#addValueModal div.modalCell').show();
                            $('#addValueModalFooter').show();
                        }, 3000);
                    }			 
					else if (data.endsWith('Ok'))
                    {
						
						
						$('#addValueLoadingMsg').hide();
                        $('#addValueLoadingIcon').hide();
                        $('#addValueKoMsg').hide();
                        $('#addValueKoIcon').hide();
                        $('#addValueOkMsg').show();
                        $('#addValueOkIcon').show();
                      
	                 
					setTimeout(function(){
                            $('#addValueModal').modal('hide');
                            buildMainTable(true);

                            setTimeout(function(){
								  $('#addValueOkMsg').hide();
                                  $('#addValueOkIcon').hide();
								  
								  $('#selectContextBroker').val("");
								  $('#inputNameDevice').val("");
								  $('#inputValueNameDevice').val("");
								  $('#selectDataType').val("");
								  $('#selectValueType').val("");
								  $('#inputEditableValue').val("");
								  $('#selectValueUnit').val("");
								  $('#selectHealthinessCriteria').val("");
								  $('#inputHealthinessValue').val("");
								  $('#inputOrder').val();		
							
																
								  $('#addValueModalTabs').show();
                                  $('#addValueModal div.modalCell').show();
                                  $('#addValueModalFooter').show();
                            }, 500);
                        }, 3000);
						
				
                    } else {console.log("success with error " + data);}
					 
                 },
                 error: function (data) 
                 {
                     console.log("Error status -- Ko result: " +  data);

                        $('#addValueLoadingMsg').hide();
                        $('#addValueLoadingIcon').hide();
                        $('#addValueKoMsg').show();
                        $('#addValueKoIcon').show();
                        setTimeout(function(){
                            $('#addValueKoMsg').hide();
                            $('#addValueKoIcon').hide();
                            $('#addValueModalTabs').show();
                            $('#addValueModal div.modalCell').show();
                            $('#addValueModalFooter').show();
                        }, 3000);
                 }
             });
        });
        
		*/
		
		
        /*   DELETE VALUE CONFIRMATION */
		
		/*
        $('#deleteValueConfirmBtn').off("click");
        $("#deleteValueConfirmBtn").click(function(){
		  
			var device = $("#deleteValueModal span").attr("data-device");
			var cb = $("#deleteValueModal span").attr("data-cb");
			var value_name   = $("#deleteValueModal span").attr("data-value_name");
            
			console.log(cb);
			
            $("#deleteValueModal div.modal-body").html("");
            $("#deleteValueCancelBtn").hide();
            $("#deleteValueConfirmBtn").hide();
            $("#deleteValueModal div.modal-body").append('<div id="deleteValueModalInnerDiv1" class="modalBodyInnerDiv"><h5>Value deletion in progress, please wait</h5></div>');
            $("#deleteValueModal div.modal-body").append('<div id="deleteValueModalInnerDiv2" class="modalBodyInnerDiv"><i class="fa fa-circle-o-notch fa-spin" style="font-size:36px"></i></div>');
			
            // Delete value
            $.ajax({
                url: "process-form.php",
				data:{	
						deleteValue: true,
						device: device, 
						cb: cb, 
						value_name: value_name 			
						},
                type: "POST",
                async: true,
				
                success: function (data) 
                {
					console.log(data);
                    if(data === '0')
                    {
                        $("#deleteValueModalInnerDiv1").html('Value &nbsp; <b>' + value_name + '</b> &nbsp; deletion failed, please try again');
                        $("#deleteValueModalInnerDiv2").html('<i class="fa fa-frown-o" style="font-size:42px"></i>');
                    }
                    else if( data === '1')
                    {
                        $("#deleteValueModalInnerDiv1").html('Value &nbsp; <b>' + value_name + '</b> &nbsp;deleted successfully');
                        $("#deleteValueModalInnerDiv2").html('<i class="fa fa-check" style="font-size:42px"></i>');
						
						$('#dashboardTotNumberCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotNumberCnt .pageSingleDataCnt').html()) - 1);
                        $('#dashboardTotActiveCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotActiveCnt .pageSingleDataCnt').html()) - 1);
                        setTimeout(function()
                        {
                            buildMainTable(true);
                            $("#deleteValueModal").modal('hide');
                            setTimeout(function(){
                                $("#deleteValueCancelBtn").show();
                                $("#deleteValueConfirmBtn").show();
                            }, 500);
                        }, 2000);
                    }
                },
                error: function (data) 
                {
                    $("#deleteValueModalInnerDiv1").html('Value &nbsp; <b>' + value_name + '</b> &nbsp; deletion failed, please try again');
                    $("#deleteValueModalInnerDiv2").html('<i class="fa fa-frown-o" style="font-size:42px"></i>');
                }
            });
        });
		
		*/
		
		
        /*  EDIT VALUE CONFIRMATION */
		
		
		/*
        
        $('#editValueConfirmBtn').off("click");
        $("#editValueConfirmBtn").click(function(){
     
			
			$("#editValueModalTabs").hide();
			$('#editValueModal div.modalCell').hide();
            $("#editValueModalFooter").hide();
            $('#editValueLoadingMsg').show();
            $('#editValueLoadingIcon').show();
          

             // Edit Value
             $.ajax({
                 url: "process-form.php",
                 data:{
				  updateValue: true, 
				  selectContextBrokerM: $('#selectContextBrokerM').val(),
			      inputNameDeviceM: $('#inputNameDeviceM').val(),
				  inputValueNameDeviceM: $('#inputValueNameDeviceM').val(),
				  selectDataTypeM: $('#selectDataTypeM').val(),
				  selectValueTypeM: $('#selectValueTypeM').val(),
				  inputEditableValueM: $('#inputEditableValueM').val(),
				  selectValueUnitM: $('#selectValueUnitM').val(),
				  selectHealthinessCriteriaM: $('#selectHealthinessCriteriaM').val(),
				  inputHealthinessValueM: $('#inputHealthinessValueM').val(),
				  inputOrderM: $('#inputOrderM').val()
				 },
                 type: "POST",
                 async: true,
                 success: function (data) 
                 {
					if(data.endsWith('Ko'))
                    {
                        console.log("Error editing Device type");
                        console.log(data);
						$('#editValueLoadingMsg').hide();
                        $('#editValueLoadingIcon').hide();
                        $('#editValueLoadingIcon').hide();
                        $('#editValueKoMsg').show();
                        $('#editValueKoIcon').show();
                      
                        setTimeout(function(){
                            $('#editValueKoMsg').hide();
                            $('#editValueKoIcon').hide();
                            $('#editValueModalTabs').show();
                            $('#editValueModal div.modalCell').show();
                            $('#editValueModalFooter').show();
                        }, 3000);
                    }
					 
					else if (data.endsWith('Ok'))
                    {
							
						$('#editValueLoadingMsg').hide();
                        $('#editValueLoadingIcon').hide();
                        $('#editValueOkMsg').show();
                        $('#editValueOkIcon').show();
                        			
						$("#addingValueModalInnerDiv1").html('Value &nbsp; <b>' + value_name + '</b> &nbsp;edited successfully');
                        $("#addingValueModalInnerDiv2").html('<i class="fa fa-check" style="font-size:42px"></i>');
	                 
						setTimeout(function(){
                            $('#editValueModal').modal('hide');
                            buildMainTable(true);

                            setTimeout(function(){
                             
								  $('#editValueOkMsg').hide();
                                  $('#editValueOkIcon').hide();
								  
								  $('#selectContextBrokerM').val("");
								  $('#inputNameDeviceM').val("");
								  $('#inputValueNameDeviceM').val("");
								  $('#selectDataTypeM').val("");
								  $('#selectValueTypeM').val("");
								  $('#inputEditableValueM').val("");
								  $('#selectValueUnitM').val("");
								  $('#selectHealthinessCriteriaM').val("");
								  $('#inputHealthinessValueM').val("");
								  $('#inputOrderM').val();		
							
								  
                                   $('#editValueModal').hide();
							       setTimeout(updateDeviceTimeout, 100);	  
								  
                            }, 100);
                        }, 100);
						
						
			} else {console.log(data);}
					 
                 },
                 error: function (data) 
                 {
                     console.log("Ko result: " + data);
                     $("#editUserModal").modal('hide');
                     $("#editUserKoModalInnerDiv1").html('<h5>Account <b>' + accountJson.username + '</b> couldn\'t be updated because of an API call failure, please try again</h5>');
                     $("#editUserKoModal").modal('show');
                     $("#editUserModalUpdating").hide();
                     $("#editUserModalBody").show();
                     $("#editUserModalFooter").show();
                 }
             });
        });
		
		*/
        
        $("#addNewValueCancelBtn").off("click");
        $("#addNewValueCancelBtn").on('click', function(){
								  
			  $('#selectContextBroker').val("");
			  $('#inputNameDevice').val("");
			  $('#inputValueNameDevice').val("");
			  $('#selectDataType').val("");
			  $('#selectValueType').val("");
			  $('#inputEditableValue').val("");
			  $('#selectValueUnit').val("");
			  $('#selectHealthinessCriteria').val("");
			  $('#inputHealthinessValue').val("");
			  $('#inputOrder').val();		
								  
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
        
 
	   
        function updateDeviceTimeout()
        {
            $("#editValueOkModal").modal('hide');
            setTimeout(function(){
               location.reload();
            }, 500);
        }
        
        function buildMainTable(destroyOld)
        {
            if(destroyOld)
            {
                $('#valuesTable').bootstrapTable('destroy');
                tableFirstLoad = true;
            }
            
            var accountVisibile = true;
            var statusVisible = true;
            

            if($(window).width() < 992)
            {
                accountVisibile = false;
                statusVisible = false; 
                
            }
            

            $.ajax({
                url: "get_data.php",
                data: {action: "getEventValues"},
                type: "GET",
                async: true,
                datatype: 'json',
                success: function (data)
                {
					
					var creatorVisibile = true;
                    var detailView = true;
                    var statusVisibile = true;
                    // console.log("builtMainTable" + JSON.stringify(data));                    

                    if($(window).width() < 992)
                    {
                        detailView = false;
                        creatorVisibile = false; 
                        statusVisibile = false;
                    }
					
					
                    $('#valuesTable').bootstrapTable({
                            columns: [{
									field: 'cb',
									title: 'Context Broker',
									filterControl: 'select',
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
                                field: 'device',
								title: 'Device',
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
                                field: 'value_name',
								title: 'Value Name',
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
                                field: 'value_type',
								title: 'Value Type',
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
                                field: 'healthiness_criteria',
								title: 'Healthiness Criteria',
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
                                field: 'value_refresh_rate',
								title: 'Refresh Rate',
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
                                   //MM15 return '<button type="button" class="editDashBtn">edit</button>';
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
                                    
                                   //MM return '<button type="button" class="delDashBtn">del</button>';
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
                                    return '<i id="addMapBtn" data-toggle="modal" data-target="#addMap" class="fa fa-globe" onclick="getLatLong(\''+ row.device + '\')\" style=\"font-size:36px; color: #0000ff\"></i>';
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
                            //uniqueId: "name",
                            striped: false,
                            searchTimeOut: 250,
                            classes: "table table-hover table-no-bordered",
							detailView: detailView,
							detailFormatter: function(index, row, element) {
                            return 'Data Type: ' + data[index].data_type   + ' | Editable: ' + data[index].editable + ' | Different Value: ' + data[index].different_values + " | Value Bound: " + data[index].value_bounds + " | Order: " + data[index].order;
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
								"data-latitude": row.device.latitude,
                              };},
                            onPostBody: function()
                            {
                                if(tableFirstLoad)
                                {
                                    //Caso di primo caricamento della tabella
                                    tableFirstLoad = false;
                                    var addDeviceDiv = $('<div class="pull-right"><i id="addDeviceBtn" data-toggle="modal" data-target="#addValueModal" class="fa fa-plus-square" style="font-size:36px; color: #ffcc00"></i></div>');
                                    
                                    //MM15 $('div.fixed-table-toolbar').append(addDeviceDiv);
                                    addDeviceDiv.css("margin-top", "10px");
                                    addDeviceDiv.find('i.fa-plus-square').off('hover');
                                    addDeviceDiv.find('i.fa-plus-square').hover(function(){
                                        $(this).css('color', '#e37777');
                                        $(this).css('cursor', 'pointer');
                                    }, 
                                    function(){
                                        $(this).css('color', '#ffcc00');
                                        $(this).css('cursor', 'normal');
                                    });
									
									
				
									/* This is loading validation when the cursor is on */
								
									$("#addDeviceBtn").off("click");
                                    $("#addDeviceBtn").click(function(){
								
                                      $("#addValueModalBody").modal('show');
                                      $("#addValueLoadingMsg").hide();
                                      $("#addValueLoadingIcon").hide();
                                      $("#addValueOkMsg").hide();
                                      $("#addValueOkIcon").hide();
                                      $("#addValueKoMsg").hide();
                                      $("#addValueKoIcon").hide();
									  
									  /* will work on the validation  showAddDeviceModal(); */

                                   });
                                   
								
                                    $('#valuesTable thead').css("background", "rgba(0, 162, 211, 1)");
                                    $('#valuesTable thead').css("color", "white");
                                    $('#valuesTable thead').css("font-size", "1em");
                                }
                                else
                                {
                                    //other case
                                }

                               
								
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
                                $('#valuesTable').css("border-bottom", "none");
                                $('span.pagination-info').hide();

                                $('#valuesTable tbody button.editDashBtn').off('hover');
                                $('#valuesTable tbody button.editDashBtn').hover(function(){
                                    $(this).css('background', '#ffcc00');
                                    $(this).parents('tr').find('td').eq(1).css('background', '#ffcc00');
                                }, 
                                function(){
                                    $(this).css('background', 'rgb(69, 183, 175)');
                                    $(this).parents('tr').find('td').eq(1).css('background', $(this).parents('td').css('background'));
                                });

                                $('#valuesTable button.editDashBtn').off('click');
                               
                                $('#valuesTable button.editDashBtn').click(function(){
                                    // $("#editDeviceModalUpdating").hide();
									
									
									//******Edit Control function call
									
							/* must have it later		showEditDeviceModal(); */
									
									
									
                                    $("#editValueModalBody").show();


                                      $("#editValueLoadingMsg").hide();
                                      $("#editValueLoadingIcon").hide();
                                      $("#editValueOkMsg").hide();
                                      $("#editValueOkIcon").hide();
                                      $("#editValueKoMsg").hide();
                                      $("#editValueKoIcon").hide(); 
									  $("#editValueModalFooter").show();
									  $("#editValueModal").modal('show');
									  $("#editValueModalLabel").html("Edit value - " + $(this).parents('tr').attr("data-value_name"));
							
											
								  $('#selectContextBrokerM').val($(this).parents('tr').attr('data-cb'));
								  $('#inputNameDeviceM').val($(this).parents('tr').attr('data-device'));
								  $('#inputValueNameDeviceM').val($(this).parents('tr').attr('data-value_name'));
								  $('#selectDataTypeM').val($(this).parents('tr').attr('data-data_type'));
								  $('#selectValueTypeM').val($(this).parents('tr').attr('data-value_type'));
								  $('#inputEditableValueM').val($(this).parents('tr').attr('data-editable'));
								  $('#selectValueUnitM').val($(this).parents('tr').attr('data-value_unit'));
								  $('#selectHealthinessCriteriaM').val($(this).parents('tr').attr('data-healthiness_criteria'));
								  $('#inputHealthinessValueM').val($(this).parents('tr').attr('data-value_refresh_rate'));
								  $('#inputOrderM').val($(this).parents('tr').attr('data-order'));
					
                                });

                                $('#valuesTable button.delDashBtn').off('hover');
                                $('#valuesTable button.delDashBtn').hover(function(){
                                    $(this).css('background', '#ffcc00');
                                    $(this).parents('tr').find('td').eq(1).css('background', '#ffcc00');
                                }, 
                                function(){
                                    $(this).css('background', '#e37777');
                                    $(this).parents('tr').find('td').eq(1).css('background', $(this).parents('td').css('background'));
                                });

                                $('#valuesTable button.delDashBtn').off('click');
                                $('#valuesTable button.delDashBtn').click(function(){
                                    var cb = $(this).parents("tr").find("td").eq(1).html();
									var device = $(this).parents("tr").find("td").eq(2).html();
								    var value_name = $(this).parents("tr").find("td").eq(3).html();
									
						            $("#deleteValueModal div.modal-body").html('<div class="modalBodyInnerDiv"><span data-value_name = "' + value_name + '" data-cb = "' + cb + '" data-device = "' + device + '">Do you want to confirm deletion of value <b>' + value_name + '</b> from Device <b>' + device + '</b>?</span></div>');
                                    $("#deleteValueModal").modal('show');
                                });
                            }
                        });
                    }
            });
        }
    });
	
	
	 function drawMap(latitude,longitude){ 
	   map = L.map('addDeviceMapModalBody').setView([latitude,longitude], 10);
	   L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
	   attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
	  }).addTo(map);
	   window.node_input_map = map;
	   L.marker([latitude,longitude]).addTo(map).bindPopup("Hi DEVICE");
	   setTimeout(function(){ map.invalidateSize()}, 400);
	  }
	
	
	  function getLatLong(id){
		     
			console.log(id);
			var id = id;
				$.ajax({
					url: "get_data.php",
					data: {
						id: id, 
						action: "getLatLong_data"
					},
					type: "GET",
					async: true,
					dataType: 'json',
					success: function (data)
					{
						if(data.result === 'Ok')
						{
							console.log(data);
							//var latitude = data[6];
							//var longitude = data[7];
							
						}
						else
						{
							console.log("In: Error retrieving latitude and longitude data");
	
						}

					},
					error: function(errorData)
					{
						console.log("Out :Error retrieving latitude and longitude data");	
					}
				});
		
				//drawMap(latitude,longitude);
				drawMap(43.78, 11.23);
 
		   
	   }
	



