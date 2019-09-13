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
                $('#usersTable').bootstrapTable('hideColumn', 'admin');
                $('#usersTable').bootstrapTable('hideColumn', 'status');
                $('#usersTable').bootstrapTable('hideColumn', 'name');
                $('#usersTable').bootstrapTable('hideColumn', 'surname');
                $('#usersTable').bootstrapTable('hideColumn', 'organization');
                $('#usersTable').bootstrapTable('hideColumn', 'email');
                $('#usersTable').bootstrapTable('hideColumn', 'reg_data');
            }
            else
            {
                $('#usersTable').bootstrapTable('showColumn', 'admin');
                $('#usersTable').bootstrapTable('showColumn', 'status');
                $('#usersTable').bootstrapTable('showColumn', 'name');
                $('#usersTable').bootstrapTable('showColumn', 'surname');
                $('#usersTable').bootstrapTable('showColumn', 'organization');
                $('#usersTable').bootstrapTable('showColumn', 'email');
                $('#usersTable').bootstrapTable('showColumn', 'reg_data');
            }
        });
        
        $('#link_user_register .mainMenuItemCnt').addClass("mainMenuItemCntActive");
        $('#mobMainMenuPortraitCnt #link_user_register .mobMainMenuItemCnt').addClass("mainMenuItemCntActive");
        $('#mobMainMenuLandCnt #link_user_register .mobMainMenuItemCnt').addClass("mainMenuItemCntActive");
        
		  buildMainTable(false);
		
});
        
		
		
		
		var admin = "<?= $_SESSION['loggedRole'] ?>";
        var existingPoolsJson = null;
        var internalDest = false;
        var tableFirstLoad = true;

        setGlobals(admin, existingPoolsJson);
		
		
	    $('#addNewUserConfirmBtn').off("click");
        $("#addNewUserConfirmBtn").click(function(){
            $("#addUserModalBody").hide();
            $("#addUserModalFooter").hide();
            $("#addUserModalCreating").show();

            $.ajax({
                 url: "../api/user.php",
                 data:{
						action: "insert",
						/*sara711 - for logging*/
						loggedUser: loggedUser,
						username: $("#addUserModalBody #username").val(),
						firstName: $("#addUserModalBody #firstName").val(),
						lastName: $("#addUserModalBody #lastName").val(),
						organization: $("#addUserModalBody #organization").val(),
						userType: $("#addUserModalBody #userType").val(),
						email: $("#addUserModalBody #email").val()
						},
						
                 type: "POST",
                 async: true,
				 success: function (data) 
                 {
                     console.log("insert "+data);
                          if(data["status"] === 'ko')
						  {
                             $("#addUserModal").modal('hide');
                             $("#addUserKoModalInnerDiv1").html('<h5>User <b>' + username + '</b> couldn\'t be registered because of a database failure while inserting data, please try again</h5>');
                             $("#addUserKoModal").modal('show');
                             $("#addUserModalCreating").hide();
                             $("#addUserModalBody").show();
                             $("#addUserModalFooter").show();
                          }

						  else(data["status"] === 'ok')
						  {
                             $("#addUserModal").modal('hide');
                             buildMainTable(true);
                             $("#addUserOkModalInnerDiv1").html('<h5>User <b>' + username + '</b> successfully registered</h5>');
                             $("#addUserOkModal").modal('show');
                             $("#addUserModalCreating").hide();
                             $("#addUserModalBody").show();
                             $("#addUserModalFooter").show();
                             setTimeout(function(){
                                 $("#addUserOkModal").modal('hide');
								 
                             }, 2000);
                        
                     }
                 },
				 
                 error: function (data) 
                 {
                     console.log("Ko result: " + data);
                     $("#addUserModal").modal('hide');
                     $("#addUserKoModalInnerDiv1").html('<h5>User <b>' + username + '</b> couldn\'t be registered because of an API call failure, please try again</h5>');
                     $("#addUserKoModal").modal('show');
                     $("#addUserModalCreating").hide();
                     $("#addUserModalBody").show();
                     $("#addUserModalFooter").show();
                 }
				
             });
        });
		
		
		
		
		
        
        $('#deleteUserConfirmBtn').off("click");
        $("#deleteUserConfirmBtn").click(function(){
            var username = $("#deleteUserModal span").attr("data-username");
    
            $("#deleteUserModal div.modal-body").html("");
            $("#deleteUserCancelBtn").hide();
            $("#deleteUserConfirmBtn").hide();
            $("#deleteUserModal div.modal-body").append('<div id="deleteUserModalInnerDiv1" class="modalBodyInnerDiv"><h5>User deletion in progress, please wait</h5></div>');
            $("#deleteUserModal div.modal-body").append('<div id="deleteUserModalInnerDiv2" class="modalBodyInnerDiv"><i class="fa fa-circle-o-notch fa-spin" style="font-size:36px"></i></div>');

          
            $.ajax({
                url: "../api/user.php",
                data:{
					action: "delete",
					username: username,
                    organization : organization,
					/*sara711 - for logging*/
					loggedUser: loggedUser
					},
                type: "POST",
				datatype: "json",
                async: true,
				
                success: function (data) 
                {
                     if(data["status"] === 'ko')
                    {
                        $("#deleteUserModalInnerDiv1").html(data["msg"]);
                        $("#deleteUserModalInnerDiv2").html('<i class="fa fa-frown-o" style="font-size:42px"></i>');
                    }
                    else if(data["status"] === 'ok')
                    {
                        $("#deleteUserModalInnerDiv1").html('User &nbsp; <b>' + username + '</b> &nbsp;deleted successfully');
                        $("#deleteUserModalInnerDiv2").html('<i class="fa fa-check" style="font-size:42px"></i>');
                        setTimeout(function()
                        {
                            buildMainTable(true);
                            $("#deleteUserModal").modal('hide');
                            setTimeout(function(){
                                $("#deleteUserCancelBtn").show();
                                $("#deleteUserConfirmBtn").show();
                            }, 500);
                        }, 2000);
                    }
                },
                error: function (data) 
                {
                    $("#deleteUserModalInnerDiv1").html('User &nbsp; <b>' + username + '</b> &nbsp; deletion failed, please try again');
                    $("#deleteUserModalInnerDiv2").html('<i class="fa fa-frown-o" style="font-size:42px"></i>');
                }
            });
        });
		
		
		
		
		
		
		
		
        
        $('#editUserConfirmBtn').off("click");
        $("#editUserConfirmBtn").click(function(){
            $("#editUserModalBody").hide();
            $("#editUserModalFooter").hide();
            $("#editUserModalUpdating").show();

     
             $.ajax({
                 url: "../api/user.php",
                 data:{ 
						 action: "update",
						 /*sara711 - for logging*/
						 loggedUser: loggedUser,
						 username: $("#editUserForm #usernameM").val(),
						 firstName: $("#editUserForm #firstNameM").val(),
						 lastName: $("#editUserForm #lastNameM").val(),
						 organization: $("#editUserForm #organizationM").val(),
						 userType: $("#editUserForm #userTypeM").val(),
						 userStatus: $("#editUserForm #userStatusM").val(),
						 email: $("#editUserForm #emailM").val(),
						 },
                 type: "POST",
                 async: true,
				 success: function (data) 
                 {
					  if(data["status"] === 'ko')
					  {
						 $("#editUserModal").modal('hide');
						 $("#editUserKoModalInnerDiv1").html('<h5>Account <b>' + username + '</b> couldn\'t be updated because of a database failure while inserting data, please try again</h5>');
						 $("#editUserKoModal").modal('show');
						 $("#editUserModalUpdating").hide();
						 $("#editUserModalBody").show();
						 $("#editUserModalFooter").show();
					  
					  }
					 else (data["status"] === 'ok')
					 {
						 
						 $("#editUserModal").modal('hide');
						 $("#editUserOkModalInnerDiv1").html('<h5>Account <b>' + username + '</b> successfully updated</h5>');
						 $("#editUserOkModal").modal('show');
						 setTimeout(updateAccountTimeout, 2000);
					 }
                 },
                 error: function (data) 
                 {
                     console.log("Ko result: " + data);
                     $("#editUserModal").modal('hide');
                     $("#editUserKoModalInnerDiv1").html('<h5>Account <b>' + username + '</b> couldn\'t be updated because of an API call failure, please try again</h5>');
                     $("#editUserKoModal").modal('show');
                     $("#editUserModalUpdating").hide();
                     $("#editUserModalBody").show();
                     $("#editUserModalFooter").show();
                 }
				 
             });
        });
		
		
	
		
        
        $("#addNewUserCancelBtn").off("click");
        $("#addNewUserCancelBtn").on('click', function(){
            $("#addUserForm").trigger("reset");
            $("#addUserAdminRoleChoiceOuterContainer").hide();
            $("#addUserAdminPoolsChoiceOuterContainer").hide();
            $("#addUserNewPoolNameOuterContainer").hide();
            $("#addUserAddUsersToNewPoolOuterContainer").hide();
            $("#addUserPoolsOuterContainer").show();
        });
        
        $("#addUserKoBackBtn").off("click");
        $("#addUserKoBackBtn").on('click', function(){
            $("#addUserKoModal").modal('hide');
            $("#addUserModal").modal('show');
        });
        
        $("#addUserKoConfirmBtn").off("click");
        $("#addUserKoConfirmBtn").on('click', function(){
            $("#addUserKoModal").modal('hide');
            $("#addUserForm").trigger("reset");
        });
        
        $("#editUserKoBackBtn").off("click");
        $("#editUserKoBackBtn").on('click', function(){
            $("#editUserKoModal").modal('hide');
            $("#editUserModal").modal('show');
        });
        
        $("#addUserKoConfirmBtn").off("click");
        $("#addUserKoConfirmBtn").on('click', function(){
            $("#editUserKoModal").modal('hide');
            $("#editUserForm").trigger("reset");
        });
        

        
  
        function updateAccountTimeout()
        {
            $("#editUserOkModal").modal('hide');
            setTimeout(function(){
               location.reload();
            }, 500);
        }
        
        function buildMainTable(destroyOld, selected=null)
        {
            if(destroyOld)
            {
                $('#usersTable').bootstrapTable('destroy');
                tableFirstLoad = true;
            }
            
            var accountVisibile = true;
            var statusVisible = true;
            var firstNameVisibile = true;
            var lastNameVisibile = true;
            var orgVisibile = true;
            var emailVisibile = true;
            var regDateVisibile = true;

            if($(window).width() < 992)
            {
                accountVisibile = false;
                statusVisible = false; 
                firstNameVisibile = false;
                lastNameVisibile = false;
                orgVisibile = false;
                emailVisibile = false;
                regDateVisibile = false;
            }
            
			if (selected==null)
			{
			  mydata = {action: "get_all_user",/*Sara611 -for logging*/ username: loggedUser, organization : organization};
			}
			else
			{
			 // mydata = {action: "get_subset_user", select : selected};
			}
		   
            $.ajax({
                url: "../api/user.php",
                data: mydata,			
                type: "GET",
                async: true,
                datatype: 'json',
                success: function (data)
                {
				data= data["content"];
				console.log(JSON.stringify(data));
                    $('#usersTable').bootstrapTable({
                            columns: [{
                                field: 'username',
                                title: 'Username',
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
                                field: 'admin',
                                title: 'Account',
                                sortable: true,
                                valign: "middle",
                                align: "center",
                                halign: "center",
                                visible: accountVisibile,
                                formatter: function(value, row, index)
                                {
                                    switch(value)
                                    {
                                       case "ToolAdmin":
                                          return "Tool admin";
                                          break;

                                       case "AreaManager":
                                          return "Area manager";
                                          break;

                                       case "Manager":
                                          return "Manager";
                                          break;
                                          
                                       default:
                                          return value;
                                          break;   
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
                                field: 'status',
                                title: 'Status',
                                sortable: true,
                                valign: "middle",
                                align: "center",
                                halign: "center",
                                visible: statusVisible,
                                formatter: function(value, row, index)
                                {
                                    if(value === '0')
                                    {
                                        return "Not active";
                                    }
                                    else 
                                    {
                                        return "Active";
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
                                field: 'name',
                                title: 'First name',
                                sortable: true,
                                valign: "middle",
                                align: "center",
                                halign: "center",
                                visible: firstNameVisibile,
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
                                field: 'surname',
                                title: 'Last name',
                                sortable: true,
                                valign: "middle",
                                align: "center",
                                halign: "center",
                                visible: lastNameVisibile,
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
                                field: 'organization',
                                title: 'Organization',
                                sortable: true,
                                valign: "middle",
                                align: "center",
                                halign: "center",
                                visible: orgVisibile,
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
                                field: 'email',
                                title: 'E-Mail',
                                sortable: true,
                                valign: "middle",
                                align: "center",
                                halign: "center",
                                visible: emailVisibile,
                                formatter: function(value, row, index)
                                {
                                    return value;
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
                                field: 'reg_data',
                                title: 'Registration date',
                                sortable: true,
                                valign: "middle",
                                align: "center",
                                halign: "center",
                                visible: regDateVisibile,
                                formatter: function(value, row, index)
                                {
                                    return value;
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
                                    //return '<span class="glyphicon glyphicon-remove"></span>'; 
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
                            }],
                            data: data,
                            search: true,
                            pagination: true,
                            pageSize: 10,
                            locale: 'en-US',
                            searchAlign: 'left',
                            uniqueId: "IdUser",
                            striped: false,
                            searchTimeOut: 250,
                            classes: "table table-hover table-no-bordered",
                            rowAttributes: function(row, index){
                            return {
                                "data-username": row.username,
                                "data-admin": row.admin,
                                "data-status": row.status,
                                "data-name": row.name,
                                "data-surname": row.surname,
                                "data-organization": row.organization,
                                "data-email": row.email,
                                "data-reg_data": row.reg_data
                            };},
                            onPostBody: function()
                            {
                                if(tableFirstLoad)
                                {
                                    //Caso di primo caricamento della tabella
                                    tableFirstLoad = false;
                             
								var addUserDiv = $('<div class="pull-right"><button id="addUserBtn"  class="btn btn-primary">New User</button></div>');

								
								
                                $('div.fixed-table-toolbar').append(addUserDiv);
                                addUserDiv.css("margin-top", "10px");
								addUserDiv.find('button.btn btn-primary').off('hover');
                                addUserDiv.find('button.btn btn-primary').hover(function(){
                                    $(this).css('color', '#e37777');
                                    $(this).css('cursor', 'pointer');
                                }, 
                                function(){
                                    $(this).css('color', '#ffcc00');
                                    $(this).css('cursor', 'normal');
                                });
                                
							
									
                                    $("#addUserBtn").off("click");
                                    $("#addUserBtn").click(showAddUserModal);
                                    $('#usersTable thead').css("background", "rgba(0, 162, 211, 1)");
                                    $('#usersTable thead').css("color", "white");
                                    $('#usersTable thead').css("font-size", "1em");
                                }
                                else
                                {
                                   
                                }

                               
                                $('#usersTable').css("border-bottom", "none");
                                $('span.pagination-info').hide();

                                $('#usersTable button.editDashBtn').off('hover');
                                $('#usersTable button.editDashBtn').hover(function(){
                                    $(this).css('background', '#ffcc00');
                                    $(this).parents('tr').find('td').eq(0).css('background', '#ffcc00');
                                }, 
                                function(){
                                    $(this).css('background', 'rgb(69, 183, 175)');
                                    $(this).parents('tr').find('td').eq(0).css('background', $(this).parents('td').css('background'));
                                });

                                $('#usersTable button.editDashBtn').off('click');
                                $('#usersTable button.editDashBtn').click(function(){
                                    $("#editUserModalUpdating").hide();
                                    $("#editUserModalBody").show();
                                    $("#editUserModalFooter").show();
                                    $("#editUserModal").modal('show');
                                    $("#editUserModalLabel").html("Edit account - " + $(this).parents('tr').attr("data-username"));
                                    $("#usernameM").val($(this).parents('tr').attr("data-username"));
                                    $("#firstNameM").val($(this).parents('tr').attr("data-name"));
                                    $("#lastNameM").val($(this).parents('tr').attr("data-surname"));
                                    $("#organizationM").val($(this).parents('tr').attr("data-organization"));
                                    $("#userStatusM").val($(this).parents('tr').attr("data-status"));
                                    $("#emailM").val($(this).parents('tr').attr("data-email"));
                                    var role = $(this).parents('tr').attr("data-admin");
                                    $("#userTypeM").val(role);

                               
                                });

                                $('#usersTable button.delDashBtn').off('hover');
                                $('#usersTable button.delDashBtn').hover(function(){
                                    $(this).css('background', '#ffcc00');
                                    $(this).parents('tr').find('td').eq(0).css('background', '#ffcc00');
                                }, 
                                function(){
                                    $(this).css('background', '#e37777');
                                    $(this).parents('tr').find('td').eq(0).css('background', $(this).parents('td').css('background'));
                                });

                                $('#usersTable button.delDashBtn').off('click');
                                $('#usersTable button.delDashBtn').click(function(){
                                    var username = $(this).parents("tr").find("td").eq(0).html();
                                    $("#deleteUserModal div.modal-body").html('<div class="modalBodyInnerDiv"><span data-username = "' + username + '">Do you want to confirm deletion of user <b>' + username + '</b>?</span></div>');
                                    $("#deleteUserModal").modal('show');
                                });
                            }
                        });
                    }
            });
        }
  
