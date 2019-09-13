tableFirstLoad=true;
var dataTable ="";
var gb_datatypes ="";
var gb_value_units ="";
var gb_value_types = "";
var defaultPolicyValue = [];


//--------to get the drop-down menus items----------// 

$.ajax({url: "../api/accessLog.php",
         data: {
			 action: 'get_param_values',
             organization: organization
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

/*************Table related ****************/


function updateDeviceTimeout()
        {
            $("#editDeviceOkModal").modal('hide');
            setTimeout(function(){
			location.reload();
            }, 1000);
        }
//---------------build the table-----------------------------//

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
				if(loggedRole == 'Root' || loggedRole == 'RootAdmin'){
					mydata = {action: "get_log", username: loggedUser, loggedrole: loggedRole,organization: organization, no_columns: ["id"]};
				}
				else{
					mydata = {action: "get_log", username: loggedUser, loggedrole: loggedRole, organization: organization, no_columns: ["id","access_log"]};
				}
			}


			
	  var col = [{"name": "time", "data": function ( row, type, val, meta ) {
					return row.time;
					} },	
			{"name": "target_entity_type", "data": function ( row, type, val, meta ) {
			
				  return row.target_entity_type;
				} },
			{"name": "access_type", "data": function ( row, type, val, meta ) {
			
				  return row.access_type;
				} },
			{"name": "entity_name", "data": function ( row, type, val, meta ) {
			
				  return row.entity_name;
				} },
			{"name": "notes", "data": function ( row, type, val, meta ) {
		
			  return row.notes;
			} },
			{"name": "result", "data": function ( row, type, val, meta ) {
		
			  return row.result;
			} }];
	  if(loggedRole == 'Root' || loggedRole == 'RootAdmin'){
		  col.splice(1,0,{"name": "accessed_by", "data": function ( row, type, val, meta ) {
				  return row.accessed_by;
				} });	
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
		 url:"../api/accessLog.php",
		 data: mydata,
		//token : sessionToken,
		 datatype: 'json',
		 type: "POST",                
		},
  	 "columns": col,  
    "order" : [] 
	  
   });
  
  }	 

 //end of fetch function 
	
     
     

$(document).ready(function () 
    {
//fetch_data function will load the device table 	
	fetch_data(false);	


// Device dataTable table Style 
  
	$('#devicesTable thead').css("background", "rgba(0, 162, 211, 1)");
	$('#devicesTable thead').css("color", "white");
	$('#devicesTable thead').css("font-size", "1em");

	console.log("logged role "+loggedRole);
	if(loggedRole != 'Root' && loggedRole != 'RootAdmin'){
		console.log("Inside if");
		document.getElementById("accessedby").remove();
	}
	
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

		$('#listDevicesLink .mainMenuItemCnt').addClass("mainMenuItemCntActive");
        $('#mobMainMenuPortraitCnt #listDevicesLink .mobMainMenuItemCnt').addClass("mainMenuItemCntActive");
        $('#mobMainMenuLandCnt #ListDevicesLink .mobMainMenuItemCnt').addClass("mainMenuItemCntActive");
    
    console.log("ok I am building the main table z");
		
});  // end of ready-state
function refresh(){
    location.reload();
}
