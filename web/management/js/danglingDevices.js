
var gb_datatypes ="";
var gb_value_units ="";
var gb_value_types = "";
var defaultPolicyValue = [];
// var mynewAttributes = [];

var gb_options = [];

var dataTable ="";

var gb_device ="";
var gb_latitude ="";
var gb_longitude = "";
// var gb_key1;
// var gb_key2;

var gb_old_cb="";

var valueTypeOpt = "";
var valueUnitOpt = "";
var gb_valVU = "";
var gb_valVT = "";

  var filterDefaults = {
			myOwnPrivate: 'MyOwnPrivate',
			myOwnPublic: 'MyOwnPublic',
			myPrivate: 'private',
            		public: 'public'
        };
		
		
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
     
function removeElementAt(parent,child) {
    var list = document.getElementById(parent);
	// var content = child.parentElement.parentElement.parentElement.innerHTML
  // console.log("elemento cancellato " + document.getElementById('deletedAttributes').innerHTML);
	if (parent=="editlistAttributes") 
	{     document.getElementById('deletedAttributes').appendChild(child.parentElement.parentElement.parentElement);}
	else list.removeChild(child.parentElement.parentElement.parentElement);
	checkAtlistOneAttribute();
}

function download(sourcename, devicename, contextbroker) {


 	$.ajax({url: "../api/device.php",
         data: {
			 token : sessionToken,
             action: 'download',
			 //Sara2510 - for logging purpose
			 username: loggedUser,
			 organization : organization, 		
			 filename: sourcename,
			 devicename:devicename,
             contextbroker:contextbroker
                         },
         type: "POST",
         async: true,
         dataType: 'json',
         success: function (mydata)
         {
		console.log(mydata);
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
		console.log(mydata);
         }
});


}

function drawAttributeMenu
(attrName, data_type, value_type, editable, value_unit, healthiness_criteria, value_refresh_rate, old_value_name, parent)
{
	console.log("parent1= "+parent);
    if (attrName=="")
		msg="<div style=\"color:red;\" class=\"modalFieldMsgCnt\"></div>";
	else 
		msg="<div class=\"modalFieldMsgCnt\">&nbsp;</div>";
    options="";
    if (value_type!="") labelcheck= value_type;
    else labelcheck="";	
	for (var n=0; n < gb_value_types.length; n++)
	{
	  if (labelcheck == gb_value_types[n]) 
		 options += "<option my_data=\""+gb_value_units[n]+"\" value=\""+gb_value_types[n]+"\" selected>"+ gb_value_types[n]+ "</option>";
	  else options += "<option my_data=\""+gb_value_units[n]+"\" value=\""+gb_value_types[n]+"\">"+ gb_value_types[n]+ "</option>";
	}
	
	
    myunits="";// <option value=\"none\"></option>";
    if (value_unit!="") labelcheck= value_unit;
	else labelcheck="#";
    for (var n=0; n < gb_value_units.length; n++)
	{
	  if (labelcheck == gb_value_units[n]) 
		 myunits += "<option value=\""+gb_value_units[n]+"\" selected>"+ gb_value_units[n]+ "</option>";
	  else myunits += "<option value=\""+gb_value_units[n]+"\">"+ gb_value_units[n]+ "</option>";
	}
	/*
	 myunits="";
	function getValueTypeUnit ()	{
	// <option value=\"none\"></option>";
  
	   myunits += "<option value=\""+valueUnitOpt[n]+"\">"+ valueUnitOpt[n]+ "</option>";
	
	
	}
	*/
	mydatatypes="";
    if (data_type!="") labelcheck= data_type;
	else labelcheck="";
    for (var n=0; n < gb_datatypes.length; n++)
	{
	  if (labelcheck == gb_datatypes[n]) 
		 mydatatypes += "<option value=\""+gb_datatypes[n]+"\" selected>"+ gb_datatypes[n]+ "</option>";
	  else mydatatypes += "<option value=\""+gb_datatypes[n]+"\">"+ gb_datatypes[n]+ "</option>";
	}
	
 console.log("parent2= "+parent);
    return "<div class=\"row\" style=\"border:3px solid blue;\" ><div class=\"col-xs-6 col-md-3 modalCell\">" +
        "<div class=\"modalFieldCnt\"><input type=\"text\" class=\"modalInputTxt\""+
		"name=\"" +  attrName +  "\"  value=\"" + attrName + "\">" + 
        "</div><div class=\"modalFieldLabelCnt\">Value Name</div>"+ msg +"</div>"+
			
		"<div class=\"col-xs-6 col-md-3 modalCell\"><div class=\"modalFieldCnt\">"+
		"<select class=\"modalInputTxt\" name=\""+ attrName+"-type" +
		"\">" + mydatatypes + 
		"</select></div><div class=\"modalFieldLabelCnt\">Data Type</div></div>" + 
	
		//"<div class=\"col-xs-6 col-md-3 modalCell\"><div class=\"modalFieldCnt\">" +
		//"<select class=\"modalInputTxt\" id=\"value_type\">" + 	valueTypeOpt + 	
		//"</select></div><div class=\"modalFieldLabelCnt\">Value Type</div></div>" +
		
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
		
		//"<div class=\"col-xs-6 col-md-3 modalCell\"><div class=\"modalFieldCnt\">" +
		//"<select class=\"modalInputTxt\" id=\"value_unit\">" + valueUnitOpt +
		 //myunits + 
		//"</select></div><div class=\"modalFieldLabelCnt\">Value Unit</div></div>"+
		
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
        "<input type=\"hidden\"  name=\""+ old_value_name +
        "\" value=\"" + old_value_name + "\"></div></div>"+
		
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
        
		/* 
		 $q = "SELECT d.`contextBroker`, d.`id`, d.`uri`, d.`devicetype`, d.`kind`, 
	      CASE WHEN mandatoryproperties AND mandatoryvalues THEN \"active\" ELSE \"idle\" END AS status1, 
	     d.`macaddress`, d.`model`, d.`producer`, d.`longitude`, d.`latitude`, d.`protocol`, d.`format`, d.`visibility`, 
	     d.`frequency`, d.`created`, d.`privatekey`, d.`certificate`, cb.`accesslink`, cb.`accessport`, cb.`sha` FROM `devices` d JOIN `contextbroker` cb ON (d.contextBroker=cb.name) "; //  WHERE visibility =\"public\"";
	 */
     
 
 
  function format ( d ) {
	  
	  var showKey="";
	  
	  if (d.visibility =='MyOwnPublic' || d.visibility == 'MyOwnPrivate' || d.visibility=='delegated'){
		if(d.k1!="" && d.k2!="")
          showKey =  '<div class="row">' +
			'<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>K1:</b>' + "  " + d.k1 + '</div>' +
			'<div class="clearfix visible-xs"></div>' +
			'<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>K2:</b>' + "  " + d.k2  + '</div>' +	
		'</div>' ;	  
		}
	else showKey=""; 
	
	var txtCert="";
	if (d.privatekey!="" && d.privatekey!= null && (d.visibility =='MyOwnPublic' || d.visibility == 'MyOwnPrivate')){
	
	console.log(d.created);
	x = new Date(d.created);
	x.setFullYear(x.getFullYear() + 1);
	//x.setDate(x.getDate()-1);
	y = x.toString();
	
	//console.log(x);
	//var tu = x.getYear();
		txtCert  = '<div class="row">' +
			'<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>Created on:</b>' + "  " + d.created + '</div>' +
			'<div class="clearfix visible-xs"></div>' +
			'<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>Expire on:</b>' + "  " + y + '</div>' +
			'</div>'+ 
			
			'<div class="row">' +
			'<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><button class="btn btn-warning" onclick="download(\'\/private\/'+d.privatekey+'\',\''+d.id+'\',\''+d.contextbroker+'\');return true;"><b>private key</b></button></div>' +
			'<div class="clearfix visible-xs"></div>' +
			'<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><button class="btn btn-warning" onclick="download(\'\/certsdb\/'+d.certificate+'\',\''+d.id+'\',\''+d.contextbroker+'\');return true;"><b>certificate</b></button></div>' +
			'</div>' +
			'<div class="row">' +
			'<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>SHA1:</b>' + "  " + d.sha + '</div>' +
			'<div class="clearfix visible-xs"></div>' +
			'</div>';
    }
	else
		txtCert = '<div class="row">' +
			'<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>Created on:</b>' + "  " + d.created + '</div>' +
			'<div class="clearfix visible-xs"></div>' +
			'</div>'; 
	
	// `d` is the original data object for the row
  	return '<div class="container-fluid">' +
		'<div class="row">' +
				'<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>IoT Broker URI:</b>' + "  " + d.accesslink +'</div>' +
				'<div class="clearfix visible-xs"></div>' +
				'<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>IoT Broker Port:</b>' + "  " + d.accessport + '</div>' +                        
		'</div>' +
		'<div class="row">' +
			'<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>Kind:</b>' + "  " + d.kind + '</div>' +	
			'<div class="clearfix visible-xs"></div>' +
            '<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>Visibility:</b>' + "  " + d.visibility + '</div>' +				
		'</div>' +
		'<div class="row">' +
			'<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Device Type:</b>' + "  " + d.devicetype + '</div>' +
			'<div class="clearfix visible-xs"></div>' +
			'<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Format:</b>' + "  " + d.format + '</div>' +
		'</div>' + 
		'<div class="row">' +
			'<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>Protocol:</b>' + "  " + d.protocol + '</div>' +
			'<div class="clearfix visible-xs"></div>' +
			'<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>MAC:</b>' + "  " + d.macaddress + '</div>' +	
		'</div>' +
		'<div class="row">' +											
			'<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Model:</b>' + "  " + d.model + '</div>' +
			'<div class="clearfix visible-xs"></div>' +
			'<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Producer:</b>' + "  " + d.producer + '</div>' +
		'</div>' + 
		'<div class="row">' +
			'<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>Longitude:</b>' + "  " + d.longitude + '</div>' +
			'<div class="clearfix visible-xs"></div>' +
			'<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>Latitude:</b>' + "  " + d.latitude  + '</div>' +
		'</div>' +                              
		'<div class="row">' +
			'<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Gateway/Edge Type:</b>' + "  " + d.edgegateway_type + '</div>' +
			'<div class="clearfix visible-xs"></div>' +
			'<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Gateway/Edge Uri:</b>' + "  " + d.edgegateway_uri  + '</div>' +	
		'</div>' + showKey +
		
		'<div class="row">' +
			'<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Organization:</b>' + "  " + d.organization + '</div>' +
			'<div class="clearfix visible-xs"></div>' +

		'</div>' + txtCert +
	'</div>' ;
	
}

//DataTable fetch_data function 


	function fetch_data(destroyOld, selected=null)
	{
		console.log("Enter:" + selected);
		if(destroyOld)
            {
				$('#devicesTable').DataTable().clear().destroy();
                tableFirstLoad = true;			
            }
           
			if (selected==null)
			{
			  mydata = {action: "get_all_dangling_device",username: loggedUser, organization : organization,  token : sessionToken,  loggedrole:loggedRole, no_columns: ["position","d.visibility","status1","edit","delete","map"]}; 
			}
			else
			{
			  //mydata = {action: "get_all_dangling_device",username: loggedUser, organization : organization,   token : sessionToken, loggedrole:loggedRole, select : selected, no_columns: ["position","d.visibility","status1","edit","delete","map"]};
			}
    
        var page_length=10;    
    
        if (loggedRole=="ToolAdmin"){
        page_length=5;
        }
        
	  dataTable = $('#devicesTable').DataTable({
		"processing" : true,
		"serverSide" : true,
        "lengthMenu" :[[5,25,50,100,-1],[5,25,50,100,"All"]],
        "pageLength":page_length,
		//"responsive" : true,
		"responsive": {
        details: false
		},
		"paging"   : true,
		"ajax" : {
		 url:"../api/device.php",
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
			{"name": "d.id", "data": function ( row, type, val, meta ) {
			
				return row.id;
				} },			
			{"name": "d.contextBroker", "data": function ( row, type, val, meta ) {
				  return row.contextBroker;
				} },	
			{"name": "d.devicetype", "data": function ( row, type, val, meta ) {
			
				  return row.devicetype;
				} },
			{"name": "d.model", "data": function ( row, type, val, meta ) {
			
				  return row.model;
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
				if (loggedRole=='RootAdmin' || d.visibility =='MyOwnPublic' || d.visibility == 'MyOwnPrivate') {
				return '<button type="button" class="delDashBtn" ' +
				'data-id="'+d.id+'" ' +
				'data-contextBroker="'+d.contextBroker+'" ' +
				'data-uri="'+d.uri+'">Delete</button>';
				 } else { }
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


//end of detail control for device dataTable 


  
//Start Related to Delete Device
	
		// Delete lines related to attributes 
	$("#attrNameDelbtn").off("click");
	$("#attrNameDelbtn").on("click", function(){
		console.log("#attrNameDelbtn");	
		$(this).parent('tr').remove();
		});	
		
		//Delete device button 
		
	$('#devicesTable tbody').on('click', 'button.delDashBtn', function () {
				console.log($(this));

		var id = $(this).attr('data-id');
		var contextbroker = $(this).attr('data-contextbroker');
		var dev_organization = $(this).attr('data-organization');
		var uri = $(this).attr("data-uri");

		$("#deleteDanglingDeviceModal div.modal-body").html('<div class="modalBodyInnerDiv"><span data-id = "' + id + '" data-contextbroker = "' + contextbroker+ '" data-organization = "' + dev_organization + '"  data-uri ="' + uri +'">Do you want to confirm deletion of device <b>' + id + '</b>?</span></div>');
		$("#deleteDanglingDeviceModal").modal('show');
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
								
	
		
	$("#deleteDanglingDeviceBtn").off("click");
    $("#deleteDanglingDeviceBtn").click(function(){
		$("#deleteAllDanglingDeviceModal div.modal-body").html('<div class="modalBodyInnerDiv">Do you want to confirm deletion of all device?</div>');
		$("#deleteAllDanglingDeviceModal").modal('show');
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

	/*$('#devicesTable tbody').on( 'click', 'tr', function () {
		alert( 'Row index: '+dataTable.row( this ).index() );
	});*/

	

//Default Title 

	if (titolo_default != ""){
		$('#headerTitleCnt').text(titolo_default);
	}
	
	if (access_denied != ""){
		alert('You need to log in with the right credentials before to access to this page!');
	}
	
// SHOW FRAME PARAMETER USE

	if (nascondi == 'hide'){
		$('#mainMenuCnt').hide();
		$('#title_row').hide();
		$('#mainCnt').removeClass('col-md-10');
		$('#mainCnt').addClass('col-md-12');
	}
	
// SHOW FRAME PARAMETER  
		
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
			//$('#devicesTable').bootstrapTable('hideColumn', 'model');
			//$('#devicesTable').bootstrapTable('hideColumn', 'visibility');
			//$('#devicesTable').bootstrapTable('hideColumn', 'status1');	
			//$('#devicesTable').bootstrapTable('hideColumn', 'type');
		
		}
		else
		{
			//$('#devicesTable').bootstrapTable('showColumn', 'id');
			//$('#devicesTable').bootstrapTable('showColumn', 'contextBroker');
			//$('#devicesTable').bootstrapTable('showColumn', 'devicetype');
			//$('#devicesTable').bootstrapTable('showColumn', 'model');
			//$('#devicesTable').bootstrapTable('showColumn', 'visibility');
			//$('#devicesTable').bootstrapTable('showColumn', 'status1');
			//$('#devicesTable').bootstrapTable('showColumn', 'uri');
			//$('#devicesTable').bootstrapTable('showColumn', 'protocol');
			//$('#devicesTable').bootstrapTable('showColumn', 'format');
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


 
//Add MyNewDevice Button  
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
		
// All Device Button 		
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
		

//GeoPosition Tab on Add Device Button 		
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

//Edit GeoPostion Tab on Edit Device Button 
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
	

//Status Tab on Edit Device 	
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
	
	
//EdgeGateWayType
	$("#selectEdgeGatewayType").click(function() {		
     checkUri();
	 checkAddDeviceConditions();
	}); 
	

//Select Model Device 	
	$("#selectModelDevice").click(function() {		
	  var nameOpt =  document.getElementById('selectModelDevice').options;
	  var selectednameOpt = document.getElementById('selectModelDevice').selectedIndex;
	  var ownerSelect =  document.getElementById('selectVisibilityDevice').options;
	  var ownerOpt = document.getElementById('selectVisibilityDevice').selectedIndex;
 	  checkModel(); 	
		//Fatima3	 
		if ((nameOpt[selectednameOpt].value !="custom")&&(nameOpt[selectednameOpt].value !="")) 
		//if (nameOpt[selectednameOpt].value !="custom") 
		{   
			 $("#addNewDeviceGenerateKeyBtn").hide();
			 
			 var gb_device =  document.getElementById('inputNameDevice').value;
			 var gb_latitude =  document.getElementById('inputLatitudeDevice').value;
			 var gb_longitude =  document.getElementById('inputLongitudeDevice').value;
			 
			if (nameOpt[selectednameOpt].getAttribute("data_key")!="special") // && ownerSelect[ownerOpt].value=='private')
			{
			 	 if ($("#KeyOneDeviceUser").val()=="") 
					 {	 
						$("#sigFoxDeviceUserMsg").val("");					
						$("#KeyOneDeviceUserMsg").html("");
						$("#KeyTwoDeviceUserMsg").html("");				
						$("#sigFoxDeviceUserMsg").html("These keys have been generated automatically for your device. Keep track of them. Details on <a href=\"https://www.snap4city.org/drupal/node/76\">info</a>");				
						$("#KeyOneDeviceUser").val(generateUUID());
						$("#KeyTwoDeviceUser").val(generateUUID());
					 }								 
			}			
			if (nameOpt[selectednameOpt].getAttribute("data_key")=="special") // && ownerSelect[ownerOpt].value=='private')
			{			
					$("#sigFoxDeviceUserMsg").html("Generate in your SigFox server the keys and report them here.  Details on <a href=\"https://www.snap4city.org/drupal/node/76\">info</a>");
					$("#KeyOneDeviceUser").val("");
					$("#KeyTwoDeviceUser").val("");			
			}
			console.log(nameOpt[selectednameOpt].value + " " + gb_device + " " + gb_longitude + " " + gb_latitude);
			
			//if(nameOpt[selectednameOpt].value !="custom" && nameOpt[selectednameOpt].value!="")
			//{ 
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
								  // data = data["content"];
								  alert("An error occured when reading the data. <br/> Get in touch with the Snap4City Administrator<br/>"+ data["msg"]);
							}

						 else (data["status"] === 'ok')
							{					
								console.log(data.content.attributes);
								var model = data.content.name;
								var type = data.content.devicetype;
								var kind = data.content.kind;
								var producer = data.content.producer;
								//var mac = data.content.mac;
								var frequency = data.content.frequency;
								var contextbroker = data.content.contextbroker;
								//var protocol = data.content.protocol;
								var format = data.content.format;
								var myattributes  = JSON.parse(data.content.attributes);
								var k =0;
								var content ="";
								// population of the value tab with the values taken from the db						
								while (k < myattributes.length)
								  {
									console.log(myattributes.length + " " +k); 
									content += drawAttributeMenu(myattributes[k].value_name, 
										 myattributes[k].data_type, myattributes[k].value_type, myattributes[k].editable, myattributes[k].value_unit, myattributes[k].healthiness_criteria, 
										 myattributes[k].healthiness_value, myattributes[k].old_value_name, 'addlistAttributes');
									k++;
								  }
								$('#addlistAttributes').html(content);
												
								$('#inputTypeDevice').val(data.content.devicetype);
								$('#selectKindDevice').val(data.content.kind);
								$('#inputProducerDevice').val(data.content.producer);
								$('#inputFrequencyDevice').val(data.content.frequency);
								//$('#inputMacDevice').val(data.content.mac);
								$('#selectContextBroker').val(data.content.contextbroker);
								$('#selectProtocolDevice').val(data.content.protocol);
								$('#selectFormatDevice').val(data.content.format); 
								$('#selectEdgeGatewayType').val(data.content.edgegateway_type);							
	
								addDeviceConditionsArray['contextbroker'] = true;
								addDeviceConditionsArray['kind'] = true;
								addDeviceConditionsArray['format'] = true;
								addDeviceConditionsArray['protocol'] = true;
								checkSelectionCB();
								checkSelectionKind();
								checkSelectionProtocol();
								checkSelectionFormat();
								
								
								
								addDeviceConditionsArray['inputTypeDevice'] = true;
								checkDeviceType(); // checkAddDeviceConditions();
								addDeviceConditionsArray['inputFrequencyDevice'] = true;
								checkFrequencyType(); // checkAddDeviceConditions();
								addDeviceConditionsArray['inputMacDevice'] = true;
								checkMAC(); 
								checkAtlistOneAttribute();
								checkAddDeviceConditions();
							}
					 },
					 error: function (data) 
					 {
						 console.log("Ko result: " + JSON.stringify(data));
						 $('#addlistAttributes').html("");
												
								$('#inputTypeDevice').val("");
								$('#selectKindDevice').val("");
								$('#inputProducerDevice').val("");
								$('#inputFrequencyDevice').val("600");
								$('#inputMacDevice').val("");
								$('#selectContextBroker').val("");
								$('#selectProtocolDevice').val("");
								$('#selectFormatDevice').val("");
                                alert("An error occured when reading the information about model. <br/> Try again or get in touch with the Snap4City Administrator<br/>");
													
					 }
					
				});		
             
				  if (nameOpt[selectednameOpt].getAttribute("data_key")!="special")
				{	 
					 $("#KeyOneDeviceUser").attr({'disabled': 'disabled'});
					 $("#KeyTwoDeviceUser").attr({'disabled': 'disabled'});
				 }
				 else{
					 $("#KeyOneDeviceUser").removeAttr('disabled');
					 $("#KeyTwoDeviceUser").removeAttr('disabled');
				 }
		}
		else if (nameOpt[selectednameOpt].value ==""){ // case not specified
			$('#inputTypeDevice').val("");
			$('#selectKindDevice').val("");
			$('#inputProducerDevice').val("");
			$('#inputFrequencyDevice').val("600");
			
			$("#sigFoxDeviceUserMsg").html("");
			$('#inputMacDevice').val("");
			$('#selectContextBroker').val("");
			$('#selectProtocolDevice').val("");
			$('#selectFormatDevice').val(""); 
			$("#KeyOneDeviceUser").val("");
		    $("#KeyTwoDeviceUser").val("");
			$('#KeyOneDeviceUserMsg').html("");
			$('#KeyTwoDeviceUserMsg').html("");
            $('#KeyOneDeviceUserMsg').val("");
			$('#KeyTwoDeviceUserMsg').val("");
		    // $('#addlistAttributes').html("");
			
			addDeviceConditionsArray['contextbroker'] = false;
			addDeviceConditionsArray['kind'] = false;
			addDeviceConditionsArray['format'] = false;
			addDeviceConditionsArray['protocol'] = false;
			checkSelectionCB();
			checkSelectionKind();
			checkSelectionProtocol();
			checkSelectionFormat();
			
			
			addDeviceConditionsArray['inputTypeDevice'] = false;
			checkDeviceType(); checkAddDeviceConditions();
			addDeviceConditionsArray['inputFrequencyDevice'] = false;
			checkFrequencyType(); checkAddDeviceConditions();
			addDeviceConditionsArray['inputMacDevice'] = false;
			checkMAC(); checkAddDeviceConditions();

			document.getElementById('addlistAttributes').innerHTML = "";
			$("#addNewDeviceGenerateKeyBtn").hide();
			checkAtlistOneAttribute();

		} else // case custom 
		{
			$("#addNewDeviceGenerateKeyBtn").show();
			
			$("#sigFoxDeviceUserMsg").html("Click on the generatekey botton to generate keys (if you need them)");
			if ($('#inputTypeDevice').val()=="") 
				addDeviceConditionsArray['inputTypeDevice'] = false;
			else
				addDeviceConditionsArray['inputTypeDevice'] = true;
			checkDeviceType(); checkAddDeviceConditions();
			if ($('#inputFrequencyDevice').val()=="")
				addDeviceConditionsArray['inputFrequencyDevice'] = false;
			else 
				addDeviceConditionsArray['inputFrequencyDevice'] = true;
			checkFrequencyType(); checkAddDeviceConditions();
			if ($('#inputMacDevice').val()=="")
				addDeviceConditionsArray['inputMacDevice'] = false;
			else 
				addDeviceConditionsArray['inputMacDevice'] = true;
			checkMAC(); checkAddDeviceConditions();
	
			$("#KeyOneDeviceUser").removeAttr('disabled');
			$("#KeyTwoDeviceUser").removeAttr('disabled');			
		}			

	});


	
//DELETE DEVICE (DELETE FROM DB) 			
	$('#deleteDanglingDeviceConfirmBtn').off("click");
	$("#deleteDanglingDeviceConfirmBtn").click(function(){		
		var id = $("#deleteDanglingDeviceModal span").attr("data-id");
		var dev_organization  = $("#deleteDanglingDeviceModal span").attr("data-organization");
		var contextbroker = $("#deleteDanglingDeviceModal span").attr("data-contextBroker");
		var uri = $("#deleteDanglingDeviceModal span").attr("data-uri");
		$("#deleteDanglingDeviceModal div.modal-body").html("");
		$("#deleteDanglingDeviceCancelBtn").hide();
		$("#deleteDanglingDeviceConfirmBtn").hide();
		$("#deleteDanglingDeviceModal div.modal-body").append('<div id="deleteDeviceModalInnerDiv1" class="modalBodyInnerDiv"><h5>Device deletion in progress, please wait</h5></div>');
		$("#deleteDanglingDeviceModal div.modal-body").append('<div id="deleteDeviceModalInnerDiv2" class="modalBodyInnerDiv"><i class="fa fa-circle-o-notch fa-spin" style="font-size:36px"></i></div>');
        
		$.ajax({
			url: "../api/device.php",
			data:{
				action: "delete",
				//Sara2510 - for logging purpose
				username: loggedUser,	
				organization : organization, 
				dev_organization : dev_organization, 
				id: id, 
				uri : uri,
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
				
					setTimeout(function()
					{  $("#deleteDeviceModal").modal('hide');  
					}, 3000);
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
						$('#devicesTable').DataTable().destroy();
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
					}, 3000);
			}
		});
	});
        
//END DELETE DEVICE (DELETE FROM DB) 		
		 
		 
//DELETE ALL DEVICE (DELETE FROM DB) 			
	$('#deleteAllDanglingDeviceConfirmBtn').off("click");
	$("#deleteAllDanglingDeviceConfirmBtn").click(function(){		
		
		$("#deleteAllDanglingDeviceModal div.modal-body").html("");
		$("#deleteAllDanglingDeviceCancelBtn").hide();
		$("#deleteAllDanglingDeviceConfirmBtn").hide();
		$("#deleteAllDanglingDeviceModal div.modal-body").append('<div id="deleteDeviceModalInnerDiv1" class="modalBodyInnerDiv"><h5>Device deletion in progress, please wait</h5></div>');
		$("#deleteAllDanglingDeviceModal div.modal-body").append('<div id="deleteDeviceModalInnerDiv2" class="modalBodyInnerDiv"><i class="fa fa-circle-o-notch fa-spin" style="font-size:36px"></i></div>');
        
		$.ajax({
			url: "../api/device.php",
			data:{
				action: "deleteAllDanglingDevice",
				username: loggedUser,	
				organization : organization, 
				dev_organization : dev_organization, 
				//id: id, 
				//uri : uri,
				//contextbroker : contextbroker,
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
				
					setTimeout(function()
					{  $("#deleteDeviceModal").modal('hide');  
					}, 3000);
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
						$('#devicesTable').DataTable().destroy();
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
					}, 3000);
			}
		});
	});
        
//END ALL DELETE DEVICE (DELETE FROM DB) 		
	

 		

//KO RELATED BUTTONS
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
		
//END KO RELATED BUTTONS	

//CONTEXTBROKER AND PROTOCOL RELATION FOR ADD DEVICE -SELECTOR 
 	
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
			//console.log("an error occurred");
			document.getElementById("selectProtocolDeviceM").value = '';
			document.getElementById("selectFormatDeviceM").value = '';
		}
		checkSelectionFormat();
		checkSelectionProtocol();
		checkAddMyDeviceConditions();
	
	});
	
//END CONTEXTBROKER AND PROTOCOL RELATION FOR ADD DEVICE -SELECTOR     
 
 
 //CONTEXTBROKER AND PROTOCOL RELATION FOR EDIT DEVICE -SELECTOR 
 	
	$("#selectContextBrokerM").change(function() {
		var index = document.getElementById("selectContextBrokerM").selectedIndex;
		var opt = document.getElementById("selectContextBrokerM").options;
		var valCB= opt[index].getAttribute("my_data");
		 //console.log("xxxprotocol" + JSON.stringify(valCB));


		if(valCB ==='ngsi')
		{
			document.getElementById("selectProtocolDeviceM").value = 'ngsi';
			document.getElementById("selectFormatDeviceM").value = 'json';
		} 
		else if(valCB ==='mqtt')
		{
			document.getElementById("selectProtocolDeviceM").value = 'mqtt';
			document.getElementById("selectFormatDeviceM").value = 'csv';
		} 
		else if (valCB ==='amqp')
		{
			document.getElementById("selectProtocolDeviceM").value = 'amqp';
			document.getElementById("selectFormatDeviceM").value = 'csv';
		} 
		else
		{
			//alert("This is a new contextBroker");
			//console.log("an error occurred");
			document.getElementById("selectProtocolDeviceM").value = '';
			document.getElementById("selectFormatDeviceM").value = '';
		}
		//checkSelectionFormat();
		//checkSelectionProtocol();
		//checkAddMyDeviceConditions();
	
	});
	
//END CONTEXTBROKER AND PROTOCOL RELATION FOR EDIT DEVICE -SELECTOR     

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
	   
	   
/*	   
$(function(){
 // $('#value_type')[0].selectedIndex = 0;
  $('#value_type').change(function(){
      var index = $(this)[0].selectedIndex;
      console.log(index);
      var opt = $(this)[0].options;
        console.log(opt);
      gb_valVT = opt[index].value;
      gb_valVU = opt[index].getAttribute("my_data");
      $('#value_unit')[0].selectedIndex = index;
      console.log("Value Type= " + gb_valVT +  " Value Unit= " + gb_valVU);
  });    
});
*/

$(document).on('change', '#value_type', function() {
//$("#value_type").change(function() {
	var index = document.getElementById("value_type").selectedIndex;
	console.log(index);	
	var opt = document.getElementById("value_type").options;
	var gb_valVU = opt[index].getAttribute("my_data");
	document.getElementById("value_unit").value = gb_valVU ;
	gb_valVT = opt[index].value;
	console.log("Value Type= " + gb_valVT +  " Value Unit= " + gb_valVU);	
	
});
	

	   
});  // end of ready-state

	
//   START TO CHANGE THE VISIBILITY  & OWNERSHIP 
				
	function changeVisibility(id, contextbroker, dev_organization, visibility, uri, k1, k2, model) {	   	   
		$("#delegationsModal").modal('show');   
	    $("#delegationHeadModalLabel").html("Device - " + id);   
	    // document.getElementById('CurrentVisiblityTxt').value = "Current Visiblity: " + visibility; 			
			//if ((target == '#visibilityCnt')) {						
			if(visibility=='MyOwnPrivate'){
				newVisibility = 'public';
				$('#visID').css('color', '#f3cf58');
				$("#visID").html("Visibility - Private");
				document.getElementById('newVisibilityPrivateBtn').style.visibility = 'hidden';
				document.getElementById('newVisibilityPublicBtn').style.visibility = 'show';
				
			} else //(visibility=='MyOwnPublic'){
				{
				newVisibility = 'private';
				$('#visID').css('color', '#f3cf58');
				$("#visID").html("Visibility - Public");
				document.getElementById('newVisibilityPrivateBtn').style.visibility = 'show';
				document.getElementById('newVisibilityPublicBtn').style.visibility = 'hidden';
			}			  
	   // To Change from Private to Public 
		//$('#newVisibilityPublicBtn').off("click");
		//$('#newVisibilityPublicBtn').click(function(e){
		$(document).on("click", "#newVisibilityPublicBtn", function(event){	
			$.ajax({
				url: "../api/device.php",
				data: 
				{	
					action: "change_visibility",
					//Sara2510 - for logging purpose
					username: loggedUser,					
					organization : organization, 
					dev_organization : dev_organization, 
					id: id,
					contextbroker: contextbroker,
					uri:uri, 
					visibility: newVisibility,
					token : sessionToken,
					k1: k1,
					k2: k2,
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
						//document.getElementById('newVisibilityPublicBtn').style.visibility = 'hidden';
						//document.getElementById('CurrentVisiblityTxt').value = "Current Visiblity: " +  newVisibility; 
						//document.getElementById('newVisibilityPrivateBtn').style.visibility = 'show';

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
		
		
// To Change from Private to Public 	
			//$('#newVisibilityPrivateBtn').off("click");
			//$('#newVisibilityPrivateBtn').click(function(e){
		$(document).on("click", "#newVisibilityPrivateBtn", function(event){
		$.ajax({
				url: "../api/device.php",
				data: 
				{	
					action: "change_visibility", 
					//Sara2510 - for logging purpose
					username: loggedUser,
					organization : organization, 
					dev_organization : dev_organization, 
					id: id,
					contextbroker: contextbroker,
					uri: uri,
					visibility: newVisibility,
					token : sessionToken,
					k1: k1,
					k2: k2,
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
						//$('#newVisibilityPrivateBtn').addClass('disabled');
						//document.getElementById('newVisibilityPrivateBtn').style.visibility = 'hidden';
						$('#newVisibilityPrivateBtn').addClass('disabled');
						//document.getElementById('CurrentVisiblityTxt').value = "Current Visiblity: " + newVisibility; 
						//document.getElementById('newVisibilityPublicBtn').style.visibility = 'show';
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
	   
	   //$('#newOwnershipConfirmBtn').off("click");
		//$('#newOwnershipConfirmBtn').click(function(e){	
	$(document).on("click", "#newOwnershipConfirmBtn", function(event){
			// I generate a new pair of keys for the new owner
		k1new = generateUUID();
		k2new = generateUUID(); 
		$.ajax({
				 url: "../api/device.php",
				 data:{
				 action: "change_owner", 
				 id: id,
				 contextbroker: contextbroker,
				 uri: uri,
				 organization : organization, 
                 dev_organization:dev_organization,
				 owner: loggedUser,
				 newOwner:  $('#newOwner').val(),
				 token : sessionToken,
				 k1: k1new,
				 k2: k2new,
				model:model
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
			
//	} //end of tab visibilityCnt
	
	
	//else if ((target == '#ownershipCnt')) {
		//Change ownership of a device
		//alert (id + contextbroker + uri +  $('#newOwner').val() + k1 + k2 + loggedUser + sessionToken);
		
	//} //end of delegationsCnt
	//else {console.log(data);}

       //populate the beginning of the tables and listen about the removal
       $.ajax({
                                    
           url: "../api/device.php",   //Checking the delegation table
           data:                         
           {                                                                
               action: "get_delegations",  // check the action and to be specified 
               id: id,
               contextbroker: contextbroker,
               uri: uri,
               organization : organization,   
               dev_organization:dev_organization,
               user : loggedUser,
               token : sessionToken,
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
                                                        url: "../api/device.php",     //check the url
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
                                                                                                               url: "../api/device.php",     //check the url
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



       //listen about the confimation
       $(document).on("click", "#newDelegationConfirmBtn", function(event){
               var newDelegation = document.getElementById('newDelegation').value;
                newk1 = generateUUID();
                newk2 = generateUUID();
                $.ajax({
                                                        url: "../api/device.php",       //which api to use
                                                        data:
                                                        {
                                                          action: "add_delegation",
                                                          contextbroker: contextbroker,
                                                          dev_organization: dev_organization,
                                                          id:id,
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
                                                                        }, 3000);
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
                                                                }, 3000);
                                                        }
               });

       });//single delegation -end

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
                       url: "../api/device.php",
                                                                                               data:
                                                                                               {
                                                                                                       action: "add_delegation",
                                                                                                       contextbroker: contextbroker,
                                                                                                       dev_organization: dev_organization,
                                                                                                       id:id,
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
		
	
// Related to the Map 
						
	function drawMap1(latitude,longitude,flag){	
		var marker;

    if(typeof map1!='undefined' && map1){
        map1.remove();
        map1=null;
    }
		if (flag ==0){
            var centerMapArr= gpsCentreLatLng.split(",",2);
            var centerLat= parseFloat(centerMapArr[0].trim());
            var centerLng= parseFloat(centerMapArr[1].trim());
			map1 = L.map('addLatLong').setView([centerLat,centerLng], zoomLevel);
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
			 checkDeviceLatitude(); checkAddDeviceConditions(); 
			 addDeviceConditionsArray['inputLongitudeDevice'] = true;
			 checkDeviceLongitude(); checkAddDeviceConditions(); 
			 if (marker){
				 map1.removeLayer(marker);
			 }
			 marker = new L.marker([lat,lng]).addTo(map1).bindPopup(lat + ',' + lng);
		
		});	

	} else if (flag==1){
		
        map1 = L.map('editLatLong').setView([latitude,longitude], 10);
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
				 checkEditDeviceLatitude(); checkEditDeviceConditions();
                 editDeviceConditionsArray['inputLongitudeDeviceM'] = true;
				 checkEditDeviceLongitude(); checkEditDeviceConditions();
				 if (marker){
					 map1.removeLayer(marker);
				 }
				 marker = new L.marker([lat,lng]).addTo(map1).bindPopup(lat+ ',' + lng);
			
			});
		
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

  
  function drawMapAll(data, divName){
		var latitude = 43.7800;
		var longitude =11.2300;
    
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

        
        /****************************Fatima-End**********************************/
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
             $('#addMap1').modal('hide');
            //Fatima2-moveAndupdate-1-line
            colorSelectedMarkers(resultsOut, greenIcon);
			$('#devicesTable').DataTable().destroy();
			console.log (JSON.stringify(resultsOut));
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
                         $('#addMap1').modal('hide');
                        //Fatima2-moveAndupdate-1-line
                        colorSelectedMarkers(resultsOut, greenIcon);
						$('#devicesTable').DataTable().destroy();
						console.log (JSON.stringify(resultsOut));
                         fetch_data(true, JSON.stringify(resultsOut));
                        });
						  


                });

        /******************Fatima-start*************************/ 
        /*map_all.on('draw:deleted', function(e) {
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
									$('#devicesTable').DataTable().destroy();
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
        //showAddDeviceModal();
    }
    
    //Fatima4
    function editGenerateKeysCLicked(){
        var k1= generateUUID();
        var k2= generateUUID();
        $("#KeyOneDeviceUserM").val(k1);
        $("#KeyTwoDeviceUserM").val(k2);
        //showEditDeviceModal();
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
				// $("#KeyOneDeviceUser").value = gb_key1;
			   // $("#KeyTwoDeviceUser").value = gb_key2;
			}
			
			$("#KeyOneDeviceUserMsg").html(message);
			$("#KeyTwoDeviceUserMsg").html(message);
	}

 
	









