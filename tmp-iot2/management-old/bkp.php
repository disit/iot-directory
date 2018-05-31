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
            //$('#sessionExpiringPopup').css("top", parseInt($('body').height() - $('#sessionExpiringPopup').height()) + "px");
            //$('#sessionExpiringPopup').css("left", parseInt($('body').width() - $('#sessionExpiringPopup').width()) + "px");
            
            if($(window).width() < 992)
            {
                $('#devicesTable').bootstrapTable('hideColumn', 'name');
                $('#devicesTable').bootstrapTable('hideColumn', 'contextbroker');
                $('#devicesTable').bootstrapTable('hideColumn', 'uri');
                $('#devicesTable').bootstrapTable('hideColumn', 'protocol');
                $('#devicesTable').bootstrapTable('hideColumn', 'format');
                $('#devicesTable').bootstrapTable('hideColumn', 'type');
                $('#devicesTable').bootstrapTable('hideColumn', 'created');
            }
            else
            {
                $('#devicesTable').bootstrapTable('showColumn', 'name');
                $('#devicesTable').bootstrapTable('showColumn', 'contextbroker');
                $('#devicesTable').bootstrapTable('showColumn', 'uri');
                $('#devicesTable').bootstrapTable('showColumn', 'protocol');
                $('#devicesTable').bootstrapTable('showColumn', 'format');
                $('#devicesTable').bootstrapTable('showColumn', 'type');
                $('#devicesTable').bootstrapTable('showColumn', 'created');
            }
        });
        
        $('#devicesLink .mainMenuItemCnt').addClass("mainMenuItemCntActive");
        $('#mobMainMenuPortraitCnt #devicesLink .mobMainMenuItemCnt').addClass("mainMenuItemCntActive");
        $('#mobMainMenuLandCnt #devicesLink .mobMainMenuItemCnt').addClass("mainMenuItemCntActive");
        
        $('#inputWidthDashboard').bootstrapSlider({
            tooltip_position: 'top',
            tooltip: 'always'
        });
        
        var cols = parseInt($('#inputWidthDashboard').val());
        var px = parseInt(cols*78 + 10);
        var percent = parseInt(px/screen.width*100);
        $('#pixelWidth').val(px + " px");
        $('#percentWidth').val(percent + " %");
        
        $('#headerVisible').bootstrapToggle({
            on: 'Yes',
            off: 'No',
            onstyle: 'info',
            offstyle: 'default',
            size: 'normal'
        });
        
        $('#headerFontSize').bootstrapSlider({
            tooltip_position: 'top'
        });
        
        $('#widgetsBorders').bootstrapToggle({
            on: 'Yes',
            off: 'No',
            onstyle: 'info',
            offstyle: 'default',
            size: 'normal'
        });
        
        /*$('#embeddable').bootstrapToggle({
            on: 'Yes',
            off: 'No',
            onstyle: 'info',
            offstyle: 'default',
            size: 'normal'
        });*/
        
        //$('#dashboardLastCnt').height($('#dashboardTotNumberCnt').height());
        
			var loggedRole = "<?= $_SESSION['loggedRole'] ?>";
            var loggedType = "<?= $_SESSION['loggedType'] ?>";
            var usr = "<?= $_SESSION['loggedUsername'] ?>";
			
			
			//var admin = "<?= $_SESSION['loggedRole'] ?>";
            var userVisibilitySet = null;
            var authorizedPages = [];
            var internalDest = false;
            var tableFirstLoad = true;
            
            $('.internalLink').on('mousedown', function(){
                internalDest = true;
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
			
			$('#inputDashboardVisibility').change(function(){
               if($(this).val() === 'restrict') 
               {
                   /*$('label[for="inputDashboardVisibilityUsersTable"]').show();
                   $('#inputDashboardVisibilityUsersTableContainer').show();*/
                   $('#inputDashboardVisibilityUsersTable').show();
               }
               else
               {
                   /*$('label[for="inputDashboardVisibilityUsersTable"]').hide();
                   $('#inputDashboardVisibilityUsersTableContainer').hide();*/
                   $('#inputDashboardVisibilityUsersTable').hide();
               }
            });
            
            $('#inputWidthDashboard').on('slide',function(e)
            {
                var cols = parseInt(e.value);
                var px = parseInt(cols*78 + 10);
                var percent = parseInt(px/screen.width*100);
                $('#pixelWidth').val(px + " px");
                $('#percentWidth').val(percent + " %");
            });
            
            setGlobals(loggedRole, usr, loggedType, userVisibilitySet);
            
            $("#logoutBtn").off("click");
            $("#logoutBtn").click(function(event)
            {
               event.preventDefault();
               location.href = "logout.php";
               
            });
			
			
			
			
			
				   
		$('#addNewDeviceConfirmBtn').off("click");
        $("#addNewDeviceConfirmBtn").click(function(){
            $("#addDeviceModalBody").hide();
            $("#addDeviceModalFooter").hide();
            $("#addDeviceModalCreating").show();

             
			 
			 newDeviceJson = {
                 inputNameDevice: $("#editDeviceForm #inputNameDevice").val(),
                 inputTypeDevice: $("#editDeviceForm #inputTypeDevice").val(),
                 selectContextBroker: $("#editDeviceForm #selectContextBroker").val(),
                 inputUriDevice: $("#editDeviceForm #inputUriDevice").val(),
                 selectProtocolDevice: $("#editDeviceForm #selectProtocolDevice").val(),
                 selectFormatDevice: $("#editDeviceForm #selectFormatDevice").val(),
                 createdDateDeviceM: $("#editDeviceForm #createdDateDevice").val(),
				 inputMacDevice: $("#editDeviceForm #inputMacDeviceM").val(),
                 inputModelDevice: $("#editDeviceForm #inputModelDevice").val(),
                 inputProducerDevice: $("#editDeviceForm #inputProducerDevice").val(),
                 inputLatitudeDevice: $("#editDeviceForm #inputLatitudeDevice").val(),
                 inputLongitudeDevice: $("#editDeviceForm #inputLongitudeDevice").val(),
                 inputPropertiesDevice: $("#editDeviceForm #inputPropertiesDevice").val(),
                 inputAttributesDevice: $("#editDeviceForm #inputAttributesDevice").val()
                
             };

			 
			 //Chiamata API di inserimento nuovo utente
             $.ajax({
                 url: "addDevice.php",
                 data:{newDeviceJson: JSON.stringify(newDeviceJson)},
                 type: "POST",
                 async: true,
                 success: function (data) 
                 {
                     switch(data)
                     {
                         case '0':
                             $("#addDeviceModal").modal('hide');
                             $("#addDeviceKoModalInnerDiv1").html('<h5>User <b>' + newUserJson.username + '</b> couldn\'t be registered because of a database failure while inserting data, please try again</h5>');
                             $("#addDeviceKoModal").modal('show');
                             $("#addDeviceModalCreating").hide();
                             $("#addDeviceModalBody").show();
                             $("#addDeviceModalFooter").show();
                             break;

                         case '1':
                             $("#addDeviceModal").modal('hide');
                             buildMainTable(true);
                             $("#addDeviceOkModalInnerDiv1").html('<h5>User <b>' + newUserJson.username + '</b> successfully registered</h5>');
                             $("#addDeviceOkModal").modal('show');
                             $("#addDeviceModalCreating").hide();
                             $("#addDeviceModalBody").show();
                             $("#addDeviceModalFooter").show();
                             setTimeout(function(){
                                 $("#addDeviceOkModal").modal('hide');
                             }, 2000);
                             break;

                         case '2':
                             $("#addDeviceModal").modal('hide');
                             $("#addDeviceKoModalInnerDiv1").html('<h5>User <b>' + newUserJson.username + '</b> couldn\'t be registered because of a database failure while checking for existing usernames, please try again</h5>');
                             $("#addDeviceKoModal").modal('show');
                             $("#addDeviceModalCreating").hide();
                             $("#addDeviceModalBody").show();
                             $("#addDeviceModalFooter").show();
                             break;

                         case '3':
                             $("#addDeviceModal").modal('hide');
                             $("#addDeviceKoModalInnerDiv1").html('<h5>User <b>' + newUserJson.username + '</b> couldn\'t be registered: this username is already in use, please change it and try again</h5>');
                             $("#addDeviceKoModal").modal('show');
                             $("#addDeviceModalCreating").hide();
                             $("#addDeviceModalBody").show();
                             $("#addDeviceModalFooter").show();
                             break;

                         case '4':
                             $("#addDeviceModal").modal('hide');
                             $("#addDeviceKoModalInnerDiv1").html('<h5>User <b>' + newUserJson.username + '</b> couldn\'t be registered: password is less than 8 chars long and/or doesn\'t have at least 1 char and 1 digit, please change it and try again</h5>');
                             $("#addDeviceKoModal").modal('show');
                             $("#addDeviceModalCreating").hide();
                             $("#addDeviceModalBody").show();
                             $("#addDeviceModalFooter").show();
                             break;

                         case '5':
                             $("#addDeviceModal").modal('hide');
                             $("#addDeviceKoModalInnerDiv1").html('<h5>User <b>' + newUserJson.username + '</b> couldn\'t be registered: password and password confirmation don\'t match, please correct and try again</h5>');
                             $("#addDeviceKoModal").modal('show');
                             $("#addDeviceModalCreating").hide();
                             $("#addDeviceModalBody").show();
                             $("#addDeviceModalFooter").show();
                             break;

                         case '6':
                             $("#addDeviceModal").modal('hide');
                             $("#addDeviceKoModalInnerDiv1").html('<h5>User <b>' + newUserJson.username + '</b> couldn\'t be registered: one between (first name - last name) and organization must be given, please correct and try again</h5>');
                             $("#addDeviceKoModal").modal('show');
                             $("#addDeviceModalCreating").hide();
                             $("#addDeviceModalBody").show();
                             $("#addDeviceModalFooter").show();
                             break;

                         case '7':
                             $("#addDeviceModal").modal('hide');
                             $("#addDeviceKoModalInnerDiv1").html('<h5>User <b>' + newUserJson.username + '</b> couldn\'t be registered: e-mail address doesn\'t respect mailbox@domain.ext pattern, please correct and try again</h5>');
                             $("#addDeviceKoModal").modal('show');
                             $("#addDeviceModalCreating").hide();
                             $("#addDeviceModalBody").show();
                             $("#addDeviceModalFooter").show();
                             break;

                         default:
                             break;
                     }
                 },
                 error: function (data) 
                 {
                     console.log("Ko result: " + data);
                     $("#addDeviceModal").modal('hide');
                     $("#addDeviceKoModalInnerDiv1").html('<h5>User <b>' + newUserJson.username + '</b> couldn\'t be registered because of an API call failure, please try again</h5>');
                     $("#addDeviceKoModal").modal('show');
                     $("#addDeviceModalCreating").hide();
                     $("#addDeviceModalBody").show();
                     $("#addDeviceModalFooter").show();
                 }
             });
        });
			
			
			
			
		$('#deleteDeviceConfirmBtn').off("click");
        $("#deleteDeviceConfirmBtn").click(function(){
            var name = $("#deleteDeviceModal span").attr("data-name");
    
            $("#deleteDeviceModal div.modal-body").html("");
            $("#deleteDeviceCancelBtn").hide();
            $("#deleteDeviceConfirmBtn").hide();
            $("#deleteDeviceModal div.modal-body").append('<div id="deleteDeviceModalInnerDiv1" class="modalBodyInnerDiv"><h5>Device deletion in progress, please wait</h5></div>');
            $("#deleteDeviceModal div.modal-body").append('<div id="deleteDeviceModalInnerDiv2" class="modalBodyInnerDiv"><i class="fa fa-circle-o-notch fa-spin" style="font-size:36px"></i></div>');

             //Chiamata API di cancellazione utente
            $.ajax({
                url: "deleteDevice.php",
                data:{name: name},
                type: "POST",
                async: false,
                success: function (data) 
                {
                    if(data === '0')
                    {
                        $("#deleteDeviceModalInnerDiv1").html('Device &nbsp; <b>' + name + '</b> &nbsp; deletion failed, please try again');
                        $("#deleteDeviceModalInnerDiv2").html('<i class="fa fa-frown-o" style="font-size:42px"></i>');
                    }
                    else if( data === '1')
                    {
                        $("#deleteDeviceModalInnerDiv1").html('Device &nbsp; <b>' + name + '</b> &nbsp;deleted successfully');
                        $("#deleteDeviceModalInnerDiv2").html('<i class="fa fa-check" style="font-size:42px"></i>');
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
                    $("#deleteDeviceModalInnerDiv1").html('User &nbsp; <b>' + name + '</b> &nbsp; deletion failed, please try again');
                    $("#deleteDeviceModalInnerDiv2").html('<i class="fa fa-frown-o" style="font-size:42px"></i>');
                }
            });
        });
        
			
		

			
        $('#editDeviceConfirmBtn').off("click");
        $("#editDeviceConfirmBtn").click(function(){
            $("#editDeviceModalBody").hide();
            $("#editDeviceModalFooter").hide();
            $("#editDeviceModalUpdating").show();

             deviceJson = {
                 inputNameDevice: $("#editDeviceForm #inputNameDeviceM").val(),
                 inputTypeDevice: $("#editDeviceForm #inputTypeDeviceM").val(),
                 selectContextBroker: $("#editDeviceForm #selectContextBrokerM").val(),
                 inputUriDevice: $("#editDeviceForm #inputUriDeviceM").val(),
                 selectProtocolDevice: $("#editDeviceForm #selectProtocolDeviceM").val(),
                 selectFormatDevice: $("#editDeviceForm #selectFormatDeviceM").val(),
                 createdDateDevice: $("#editDeviceForm #createdDateDeviceM").val(),
				 inputMacDevice: $("#editDeviceForm #inputMacDeviceM").val(),
                 inputModelDevice: $("#editDeviceForm #inputModelDeviceM").val(),
                 inputProducerDevice: $("#editDeviceForm #inputProducerDeviceM").val(),
                 inputLatitudeDevice: $("#editDeviceForm #inputLatitudeDeviceM").val(),
                 inputLongitudeDevice: $("#editDeviceForm #inputLongitudeDeviceM").val(),
                 inputPropertiesDevice: $("#editDeviceForm #inputPropertiesDeviceM").val(),
                 inputAttributesDevice: $("#editDeviceForm #inputAttributesDeviceM").val()
                
             };

            console.log(JSON.stringify(deviceJson));
			
             //Chiamata API di aggiornamento account utente
             $.ajax({
                 url: "editDevice.php",
                 data:{operation: "updateDevice", deviceJson: JSON.stringify(deviceJson)},
                 type: "POST",
                 async: true,
                 success: function (data) 
                 {
                     switch(data)
                     {
                         case '0':
                             $("#editDeviceModal").modal('hide');
                             $("#editDeviceKoModalInnerDiv1").html('<h5>Device <b>' + deviceJson.name + '</b> couldn\'t be updated because of a database failure while inserting data, please try again</h5>');
                             $("#editDeviceKoModal").modal('show');
                             $("#editDevicerModalUpdating").hide();
                             $("#editDeviceModalBody").show();
                             $("#editDeviceModalFooter").show();
                             break;

                         case '1':
                             $("#editDeviceModal").modal('hide');
                             $("#editDeviceOkModalInnerDiv1").html('<h5>Device <b>' + deviceJson.name + '</b> successfully updated</h5>');
                             $("#editDeviceOkModal").modal('show');
                             setTimeout(updateAccountTimeout, 2000);
                             break;

                         case '4':
                             $("#editDeviceModal").modal('hide');
                             $("#editDeviceKoModalInnerDiv1").html('<h5>Device <b>' + deviceJson.name + '</b> couldn\'t be updated: password is less than 8 chars long and/or doesn\'t have at least 1 char and 1 digit, please change it and try again</h5>');
                             $("#editDeviceKoModal").modal('show');
                             $("#editDevicerModalUpdating").hide();
                             $("#editDeviceModalBody").show();
                             $("#editDeviceModalFooter").show();
                             break;

                         case '5':
                             $("#editDeviceModal").modal('hide');
                             $("#editDeviceKoModalInnerDiv1").html('<h5>Device <b>' + deviceJson.name + '</b> couldn\'t be updated: , please fix and try again</h5>');
                             $("#editDeviceKoModal").modal('show');
                             $("#editDevicerModalUpdating").hide();
                             $("#editDeviceModalBody").show();
                             $("#editDeviceModalFooter").show();
                             break;

                         case '6':
                             $("#editDeviceModal").modal('hide');
                             $("#editDeviceKoModalInnerDiv1").html('<h5>Device <b>' + deviceJson.name + '</b> couldn\'t be updated:  please fix and try again</h5>');
                             $("#editDeviceKoModal").modal('show');
                             $("#editDevicerModalUpdating").hide();
                             $("#editDeviceModalBody").show();
                             $("#editDeviceModalFooter").show();
                             break;

                         case '7':
                             $("#editDeviceModal").modal('hide');
                             $("#editDeviceKoModalInnerDiv1").html('<h5>Device <b>' + deviceJson.name + '</b> couldn\'t be updated:  please fix and try again</h5>');
                             $("#editDeviceKoModal").modal('show');
                             $("#editDevicerModalUpdating").hide();
                             $("#editDeviceModalBody").show();
                             $("#editDeviceModalFooter").show();
                             break;

                         default:
                             break;
                     }
                 },
                 error: function (data) 
                 {
                     console.log("Ko result: " + data);
                     $("#editDeviceModal").modal('hide');
                     $("#editDeviceKoModalInnerDiv1").html('<h5>Account <b>' + accountJson.username + '</b> couldn\'t be updated because of an API call failure, please try again</h5>');
                     $("#editDeviceKoModal").modal('show');
                     $("#editDevicerModalUpdating").hide();
                     $("#editDeviceModalBody").show();
                     $("#editDeviceModalFooter").show();
                 }
             });
        });
			
			
			
			
			
			$("#addNewDeviceCancelBtn").off("click");
			$("#addNewDeviceCancelBtn").on('click', function(){
            $("#addDeviceForm").trigger("reset");
            $("#addDeviceAdminRoleChoiceOuterContainer").hide();
            $("#addDeviceAdminPoolsChoiceOuterContainer").hide();
            $("#addDeviceNewPoolNameOuterContainer").hide();
            $("#addDeviceAddUsersToNewPoolOuterContainer").hide();
            $("#addDevicePoolsOuterContainer").show();
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
        
        $("#addDeviceKoConfirmBtn").off("click");
        $("#addDeviceKoConfirmBtn").on('click', function(){
            $("#editDeviceKoModal").modal('hide');
            $("#editDeviceForm").trigger("reset");
        });
			
			
			
			
			
			
			
            
            
            
            $.ajax({
                url: "get_data.php",
                data: {
                    action: "getDevices"
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
                   
                    $('#devicesTable').bootstrapTable({
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
                            field: 'contextBroker',
                            title: 'ContextBroker',
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
                            field: 'format',
                            title: 'Format',
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
                            field: 'type',
                            title: 'Type',
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
                            return 'MAC: ' + data[index].macaddress + ' | Model: ' + data[index].model + " | Producer: " + data[index].producer + " | Longitude: " + data[index].longitude + " | Latitude: " + data[index].latitude;
                        },
                        searchTimeOut: 250,
                        onPostBody: function()
                        {
                            if(tableFirstLoad)
                            {
                                //Caso di primo caricamento della tabella
                                tableFirstLoad = false;
                                var addDeviceDiv = $('<div class="pull-right"><i id="addDeviceBtn" data-toggle="modal" data-target="#modal-add-metric" class="fa fa-plus-square" style="font-size:36px; color: #ffcc00"></i></div>');
                                $('div.fixed-table-toolbar').append(addDeviceDiv);
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
                                
                                $("#addDeviceBtn").off("click");
								$("#addDeviceBtn").click(showAddDeviceModal);
                                $('#devicesTable thead').css("background", "rgba(0, 162, 211, 1)");
                                $('#devicesTable thead').css("color", "white");
                                $('#devicesTable thead').css("font-size", "1.1em");
                            }
                            else
                            {
                                //Casi di cambio pagina
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

                            $('#devicesTable button.editDashBtn').off('hover');
                            $('#devicesTable button.editDashBtn').hover(function(){
                                $(this).css('background', '#ffcc00');
                                $(this).parents('tr').find('td').eq(0).css('background', '#ffcc00');
                            }, 
                            function(){
                                $(this).css('background', 'rgb(69, 183, 175)');
                                $(this).parents('tr').find('td').eq(0).css('background', $(this).parents('td').css('background'));
                            });
							
							$('#devicesTable button.editDashBtn').off('click');
							$('#devicesTable button.editDashBtn').click(function(){
								$("#editDeviceModalUpdating").hide();
								$("#editDeviceModalBody").show();
								$("#editDeviceModalFooter").show();
								$("#editDeviceModal").modal('show');
								$("#editDeviceModalLabel").html("Edit device - " + $(this).parents('tr').attr("data-name"));
								$("#inputNameDeviceM").val($(this).parents('tr').attr("data-name"));
								$("#inputTypeDeviceM").val($(this).parents('tr').attr("data-type"));
								$("#selectContextBrokerM").val($(this).parents('tr').attr("data-contextBroker"));
								$("#inputUriDeviceM").val($(this).parents('tr').attr("data-uri"));
								$("#selectProtocolDeviceM").val($(this).parents('tr').attr("data-protocol"));
								$("#createdDateDeviceM").val($(this).parents('tr').attr("data-created"));
								$("#inputMacDeviceM").val($(this).parents('tr').attr("data-macaddress"));
								$("#inputModelDeviceM").val($(this).parents('tr').attr("data-model"));
								$("#inputProducerDevice").val($(this).parents('tr').attr("data-producer"));
								$("#inputLatitudeDeviceM").val($(this).parents('tr').attr("data-latitude"));
								$("#inputLongitudeDeviceM").val($(this).parents('tr').attr("data-longtitude"));
								$("#inputPropertiesDeviceM").val($(this).parents('tr').attr("data-properties"));
								$("#inputAttributesDeviceM").val($(this).parents('tr').attr("data-attributes"));


								
							            $.ajax({
                                        url: "editDevice.php",
                                        data: {operation: "getDevicePoolMemberships", name: $(this).parents('tr').attr("data-name")},
                                        type: "GET",
                                        async: true,
                                        dataType: 'json',
                                        success: function (data) 
                                        {
                                          var row = null;

                                          $("#editUserPoolsTable tbody").empty();
                                          for(var i = 0; i < data.length; i++)
                                          {
                                             row = $('<tr><td class="checkboxCell editUserPoolsTableMakeMemberCheckbox"><input data-poolId="' + data[i].poolId + '" type="checkbox" /></td><td class="checkboxCell editUserPoolsTableMakeAdminCheckbox"><input data-poolId="' +  data[i].poolId + '" type="checkbox" /></td><td class="poolNameCell">' + data[i].poolName + '</td>');

                                             $("#editUserPoolsTable").append(row);
                                          }

                                        $("#editUserModalLoading").hide();

                                           showEditUserModalBody();
                                        },
                                        error: function (data)
                                        {
                                           console.log("Get user pool memberships KO");
                                           console.log(data);
                                        }
                                    });	
								
								 });
								
								
		
                            
			$('#devicesTable button.delDashBtn').off('hover');
			$('#devicesTable button.delDashBtn').hover(function(){
				$(this).css('background', '#ffcc00');
				$(this).parents('tr').find('td').eq(0).css('background', '#ffcc00');
			}, 
			function(){
				$(this).css('background', 'rgba(0, 162, 211, 1)');
				$(this).parents('tr').find('td').eq(0).css('background', $(this).parents('td').css('background'));
			});
							
			$('#devicesTable button.delDashBtn').off('click');
			$('#devicesTable button.delDashBtn').click(function(){
				var name = $(this).parents("tr").find("td").eq(0).html();
				$("#deleteDeviceModal div.modal-body").html('<div class="modalBodyInnerDiv"><span data-name = "' + name + '">Do you want to confirm deletion of device <b>' + name + '</b>?</span></div>');
				$("#deleteDeviceModal").modal('show');
			});				
							

		}
	});
},


            $('#dashboardLogoInput').change(function ()
            {
                $('#dashboardLogoLinkInput').removeAttr('disabled');
            });

            
            $('.customColorChoice').colorpicker({
                format: "rgba"
            });
            
            //Caricamento dell'insieme di visibilità per l'utente collegato
            $.ajax({
               url: "getUserVisibilitySet.php",
               type: "POST",
               async: true,
               dataType: 'JSON',
               cache: false, 
               success: function (data) 
               {
                   userVisibilitySet = data;

                   $("#inputDashboardVisibilityUsersTable").append('<tr><th class="selectCell">Select</th><th class="usernameCell">Username</th></tr>');

                   for(var i = 0; i < userVisibilitySet.length; i++)
                   {
                      $("#inputDashboardVisibilityUsersTable").append('<tr><td><input type="checkbox" name="selectedVisibilityUsers[]" value="' + userVisibilitySet[i] + '"/></td><td>' + userVisibilitySet[i] + '</td></tr>'); 
                   }

                   //Metodo apposito per settare/desettare gli attributi checked sulle checkbox
                   $('#inputDashboardVisibilityUsersTable input[type="checkbox"').off('click');
                   $('#inputDashboardVisibilityUsersTable input[type="checkbox"').click(function(){
                       if($(this).attr("checked") === "checked")
                       {
                           $(this).removeAttr("checked");
                       }
                       else
                       {
                           $(this).attr("checked", "true");
                       }
                   });
               },
               error: function (data) 
               {
                   //TBD
                   console.log("Error: " + JSON.stringify(data));
               }
           });
		   
		   
		   
		   
		   
	

		
		
	
		
		
		
		
		
		
		
	