
var devicenamesArray, editDeviceConditionsArray;

function showEditDeviceModal()
{
    devicenamesArray = new Array();
    editDeviceConditionsArray = new Array();
    // editDeviceConditionsArray['inputNameDeviceM'] = false;
	editDeviceConditionsArray['inputTypeDeviceM'] = false;
    editDeviceConditionsArray['inputLatitudeDeviceM'] = false;
    editDeviceConditionsArray['inputLongitudeDeviceM'] = false;

	
	checkEditDeviceName();
	checkEditDeviceType();
	checkEditDeviceLatitude(); 
	checkEditDeviceLongitude();
	
    // $("#editDeviceConfirmBtn").attr("disabled", true);

	// $("#editInfoTabDevice #inputNameDeviceM").on('input', function(){checkEditDeviceName(); checkEditDeviceConditions(); });
	$("#editInfoTabDevice #inputTypeDeviceM").on('input', function(){checkEditDeviceType(); checkEditDeviceConditions(); });
	$("#editGeoPositionTabDevice #inputLatitudeDeviceM").on('input', function(){checkEditDeviceLatitude(); checkEditDeviceConditions(); });
	$("#editGeoPositionTabDevice #inputLongitudeDeviceM").on('input', function(){checkEditDeviceLongitude(); checkEditDeviceConditions(); });

        $("#editGeoPositionTabDevice #inputLatitudeDeviceM").trigger("change");
        $("#editGeoPositionTabDevice #inputLongitudeDeviceM").trigger("change");
	
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


	
function checkEditDeviceConditions()
{
    var enableButton = true;
	
	 // console.log(editDeviceConditionsArray);
    
    for(var key in editDeviceConditionsArray) 
    {
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
