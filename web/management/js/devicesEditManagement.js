

var devicenamesArray = new Array();
var editDeviceConditionsArray = new Array();

function showEditDeviceModal()
{
   
       // editDeviceConditionsArray['inputNameDeviceM'] = false;
	editDeviceConditionsArray['inputTypeDeviceM'] = false;
    editDeviceConditionsArray['inputLatitudeDeviceM'] = false;
    editDeviceConditionsArray['inputLongitudeDeviceM'] = false;
	editDeviceConditionsArray['inputMacDeviceM'] = false;
    editDeviceConditionsArray['KeyOneDeviceUserM'] = false;
	editDeviceConditionsArray['KeyTwoDeviceUserM'] = false;
	
	
	$("#editDeviceConfirmBtn").attr("disabled", true);
	
    $("#editInfoTabDevice #inputTypeDeviceM").on('input', function(){checkEditDeviceType();checkEditDeviceConditions();}); 
	
	
	$("#editGeoPositionTabDevice #inputLatitudeDeviceM").on('input', function(){checkEditDeviceLatitude(); checkEditDeviceConditions();}); 
	

	$("#editGeoPositionTabDevice #inputLongitudeDeviceM").on('input', function(){checkEditDeviceLongitude(); checkEditDeviceConditions();}); 
	
	
	$("#editInfoTabDevice #inputMacDeviceM").on('input', function(){checkEditDeviceMAC(); checkEditDeviceConditions();}); 
    
    $("#editInfoTabDevice #KeyOneDeviceUserM").on('input', function(){UserEditKey(); checkEditDeviceConditions();}); 
    $("#editInfoTabDevice #KeyOneDeviceUserM").change(function(){UserEditKey(); checkEditDeviceConditions();});
    
    $("#editInfoTabDevice #KeyTwoDeviceUserM").on('input', function(){UserEditKey(); checkEditDeviceConditions();}); 
    $("#editInfoTabDevice #KeyTwoDeviceUserM").change( function(){UserEditKey(); checkEditDeviceConditions();}); 
    
    $("#editInfoTabDevice #editDeviceGenerateKeyBtn").on('click',function(){UserEditKey(); checkEditDeviceConditions();});
	
	
	checkEditDeviceName();
	checkEditDeviceType();
	checkEditDeviceLatitude(); 
	checkEditDeviceLongitude();
	checkEditDeviceMAC();
	UserEditKey(); 
	checkEditDeviceConditions();
    

	   
	   $("#editDeviceModal").modal('show');
	
}


function checkEditDeviceName()
{
    var message = null;
    
    if($("#editInfoTabDevice #inputNameDeviceM").val().length === 0)
    {
        $("#inputNameDeviceMMsg").css("color", "red");
        message = 'Device name is not register but it is readonly';
        $("#inputNameDeviceM").prop("readonly", true);
        editDeviceConditionsArray['inputNameDeviceM'] = true;
    }
    else
    {
            $("#inputNameDeviceMMsg").css("color", "#337ab7");
            message = 'Device Name is ReadOnly';
            $("#inputNameDeviceM").prop("readonly", true);
            editDeviceConditionsArray['inputNameDeviceM'] = true;
        
    }
    
    $("#inputNameDeviceMsg").html(message);
}

function checkEditDeviceType()
{
    var message = null;
    if($("#editInfoTabDevice #inputTypeDeviceM").val().length === 0)
    {
        $("#inputTypeDeviceMMsg").css("color", "red");
        message = 'Device Type is mandatory';
        editDeviceConditionsArray['inputTypeDeviceM'] = false;
    }
	/*
    else if($("#editInfoTabDevice #inputTypeDeviceM").val().length < 5)
    {
        $("#inputTypeDeviceMMsg").css("color", "red");
        message = 'Type name (at least 5 chars long)';
        editDeviceConditionsArray['inputTypeDeviceM'] = false;
    }
	*/
    else
    {
		//Check if this part is necessary
        if(($.inArray($("#editInfoTabDevice #inputTypeDeviceM").val(), devicenamesArray, 0) > 0)||($("#editInfoTabDevice #inputTypeDeviceM").val() === devicenamesArray[0]))
        {
            $("#inputTypeDeviceMMsg").css("color", "red");
            message = 'Device Type already used';
            editDeviceConditionsArray['inputTypeDeviceM'] = false;
        }
        else
        {
            $("#inputTypeDeviceMMsg").css("color", "#337ab7");
            message = 'Ok';
            editDeviceConditionsArray['inputTypeDeviceM'] = true;
        }
    }
    
    $("#inputTypeDeviceMMsg").html(message);
}


function checkEditDeviceLatitude()
{
    var message = null;
    var pattern = /^(\+|-)?(?:90(?:(?:\.0{1,6})?)|(?:[0-9]|[1-8][0-9])(?:(?:\.[0-9]{1,6})?))$/;
    //var pattern = /^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?)$/;
	var value = document.getElementById("inputLatitudeDeviceM").value;
	
    if(value === '')
    {
        message = 'Latitude is mandatory';
        editDeviceConditionsArray['inputLatitudeDeviceM'] = false;
        $("#inputLatitudeDeviceMMsg").css("color", "red");
    }
    else if(!pattern.test(value))
    {
        message = 'Latitude format is not correct ';
        editDeviceConditionsArray['inputLatitudeDeviceM'] = false;
        $("#inputLatitudeDeviceMMsg").css("color", "red");
    }
    else if(pattern.test(value))
    {
        message = 'Ok';
        editDeviceConditionsArray['inputLatitudeDeviceM'] = true;
        $("#inputLatitudeDeviceMMsg").css("color", "#337ab7");
    }
    
    $("#inputLatitudeDeviceMMsg").html(message);
}


function checkEditDeviceLongitude()
{
    var message = null;
   // var reg = new RegExp("^-?([1-8]?[1-9]|[1-9]0)\.{1}\d{1,6}");
    var pattern = /^[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)$/;
	var value = document.getElementById("inputLongitudeDeviceM").value;
	if(value === '')
    {
        message = 'Longitude is mandatory';
        editDeviceConditionsArray['inputLongitudeDeviceM'] = false;
        $("#inputLongitudeDeviceMMsg").css("color", "red");
    }
    else if(!pattern.test(value))
    {
        message = 'Latitude format is not correct ';
        editDeviceConditionsArray['inputLongitudeDeviceM'] = false;
        $("#inputLongitudeDeviceMMsg").css("color", "red");
    }
    else if(pattern.test(value))
    {
        message = 'Ok';
        editDeviceConditionsArray['inputLongitudeDeviceM'] = true;
        $("#inputLongitudeDeviceMMsg").css("color", "#337ab7");
    }
    
    $("#inputLongitudeDeviceMMsg").html(message);
}


function checkEditDeviceMAC()
{
    var message = null;
	var pattern = /^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/;
	
	if($("#editInfoTabDevice #inputMacDeviceM").val().length === 0 || $("#editInfoTabDevice #inputMacDeviceM").val()==='null')
    {
        $("#inputMacDeviceMMsg").css("color", "red");
        message = '';
        editDeviceConditionsArray['inputMacDeviceM'] = true;
    }
	else {
			if(!pattern.test($("#editInfoTabDevice #inputMacDeviceM").val()))
				{
				message = 'Mac format should be Letter (A-F) and number (eg. 3D:F2:C9:A6:B3:4F)';
				editDeviceConditionsArray['inputMacDeviceM'] = false;
				$("#inputMacDeviceMMsg").css("color", "red");
				}
			else if(pattern.test($("#editInfoTabDevice #inputMacDeviceM").val()))
			{
				message = 'Ok';
				editDeviceConditionsArray['inputMacDeviceM'] = true;
				$("#inputMacDeviceMMsg").css("color", "#337ab7");
			}
    }
    
    $("#inputMacDeviceMMsg").html(message);
}


function UserEditKey()
	{
			var message = null;
			var pattern = /^[a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{12}$/;
			
			var value1 = document.getElementById("KeyOneDeviceUserM").value;
			var value2 = document.getElementById("KeyTwoDeviceUserM").value;
			
			if((value1 === '') &&  (value2 === ''))
			{
				message = 'Specify Key for the selected option';
				//document.getElementById("addMyNewDeviceConfirmBtn").disabled = true;
				editDeviceConditionsArray['KeyOneDeviceUserM'] = false;
				editDeviceConditionsArray['KeyTwoDeviceUserM'] = false;
				$("#KeyOneDeviceUserMsgM").css("color", "red");
				$("#KeyTwoDeviceUserMsgM").css("color", "red");
				
			}
			else if(!pattern.test(value1) || !pattern.test(value2))
			{
				message = 'The Key should contain at least one special character and a number';
				//document.getElementById("addMyNewDeviceConfirmBtn").disabled = true;
				editDeviceConditionsArray['KeyOneDeviceUserM'] = false;
				editDeviceConditionsArray['KeyTwoDeviceUserM'] = false;
				$("#KeyOneDeviceUserMsgM").css("color", "red");
				$("#KeyTwoDeviceUserMsgM").css("color", "red");
			}
			else if(pattern.test(value1) && pattern.test(value2))
			{
				message = 'Ok';
				//document.getElementById("addMyNewDeviceConfirmBtn").disabled = false;
				editDeviceConditionsArray['KeyOneDeviceUserM'] = true;
				editDeviceConditionsArray['KeyTwoDeviceUserM'] = true;
				$("#KeyOneDeviceUserMsgM").css("color", "#337ab7");
				$("#KeyTwoDeviceUserMsgM").css("color", "#337ab7");
				gb_key1 = $("#KeyOneDeviceUserM").value;
			    gb_key2 = $("#KeyTwoDeviceUserM").value;
			}
			
			$("#KeyOneDeviceUserMsgM").html(message);
			$("#KeyTwoDeviceUserMsgM").html(message);
	}
	
	
	
function checkEditDeviceConditions()
{
    var enableButton = true;
	
	 // console.log(editDeviceConditionsArray);
    
    for(var key in editDeviceConditionsArray) 
    {
        console.log(key);
        if(editDeviceConditionsArray[key] === false)
        {
            enableButton = false;
            break;
        }
    }
    
    if(enableButton)
    {
        $("#editDeviceConfirmBtn").attr("disabled", false);
    }
    else
    {
        $("#editDeviceConfirmBtn").attr("disabled", true);
    }
}
