
var devicenamesArray, addDeviceConditionsArray;

function showAddDeviceModal()
{
    devicenamesArray = new Array();
    addDeviceConditionsArray = new Array();
    addDeviceConditionsArray['inputNameDevice'] = false;
    addDeviceConditionsArray['inputLatitudeDevice'] = false;
    addDeviceConditionsArray['inputLongitudeDevice'] = false;

//    $("#addNewDeviceConfirmBtn").attr("disabled", true);
     
    
	$("#addInfoTabDevice #inputNameDevice").on('input', function(){checkDeviceName(); checkAddDeviceConditions(); });
	$("#addGeoPositionTabDevice #inputLatitudeDevice").on('input', function(){checkDeviceLatitude(); checkAddDeviceConditions(); });
	$("#addGeoPositionTabDevice #inputLongitudeDevice").on('input', function(){checkDeviceLongitude(); checkAddDeviceConditions(); });
	
}


function checkDeviceName()
{
    var message = null;
    
    if($("#addInfoTabDevice #inputNameDevice").val().length === 0)
    {
        $("#inputNameDeviceMsg").css("color", "red");
        message = 'Device name is mandatory';
        addDeviceConditionsArray['inputNameDevice'] = false;
    }
    else if($("#addInfoTabDevice #inputNameDevice").val().length < 5)
    {
        $("#inputNameDeviceMsg").css("color", "red");
        message = 'Device name (at least 5 chars long)';
        addDeviceConditionsArray['inputNameDevice'] = false;
    }
    else
    {
		//Check if this part is necessary
        if(($.inArray($("#addInfoTabDevice #inputNameDevice").val(), devicenamesArray, 0) > 0)||($("#addInfoTabDevice #inputNameDevice").val() === devicenamesArray[0]))
        {
            $("#inputNameDeviceMsg").css("color", "red");
            message = 'Device name already used';
            addDeviceConditionsArray['inputNameDevice'] = false;
        }
        else
        {
            $("#inputNameDeviceMsg").css("color", "#337ab7");
            message = 'Ok';
            addDeviceConditionsArray['inputNameDevice'] = true;
        }
    }
    
    $("#inputNameDeviceMsg").html(message);
}


function checkDeviceLatitude()
{
    var message = null;
    var pattern = /^(\+|-)?(?:90(?:(?:\.0{1,6})?)|(?:[0-9]|[1-8][0-9])(?:(?:\.[0-9]{1,6})?))$/;
    //var pattern = /^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?)$/;
	var value = document.getElementById("inputLatitudeDevice").value;
	
    if(value === '')
    {
        message = 'Latitude is mandatory';
        addDeviceConditionsArray['inputLatitudeDevice'] = false;
        $("#inputLatitudeDeviceMsg").css("color", "red");
    }
    else if(!pattern.test(value))
    {
        message = 'Latitude format is not correct ';
        addDeviceConditionsArray['inputLatitudeDevice'] = false;
        $("#inputLatitudeDeviceMsg").css("color", "red");
    }
    else if(pattern.test(value))
    {
        message = 'Ok';
        addDeviceConditionsArray['inputLatitudeDevice'] = true;
        $("#inputLatitudeDeviceMsg").css("color", "#337ab7");
    }
    
    $("#inputLatitudeDeviceMsg").html(message);
}


function checkDeviceLongitude()
{
    var message = null;
   // var reg = new RegExp("^-?([1-8]?[1-9]|[1-9]0)\.{1}\d{1,6}");
    var pattern = /^[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)$/;
	var value = document.getElementById("inputLongitudeDevice").value;
	if(value === '')
    {
        message = 'Longitude is mandatory';
        addDeviceConditionsArray['inputLongitudeDevice'] = false;
        $("#inputLongitudeDeviceMsg").css("color", "red");
    }
    else if(!pattern.test(value))
    {
        message = 'Latitude format is not correct ';
        addDeviceConditionsArray['inputLongitudeDevice'] = false;
        $("#inputLongitudeDeviceMsg").css("color", "red");
    }
    else if(pattern.test(value))
    {
        message = 'Ok';
        addDeviceConditionsArray['inputLongitudeDevice'] = true;
        $("#inputLongitudeDeviceMsg").css("color", "#337ab7");
    }
    
    $("#inputLongitudeDeviceMsg").html(message);
}


	
function checkAddDeviceConditions()
{
    var enableButton = true;
	
	  // console.log(addDeviceConditionsArray);
    
    for(var key in addDeviceConditionsArray) 
    {
        if(addDeviceConditionsArray[key] === false)
        {
            enableButton = false;
            break;
        }
    }
    
    if(enableButton)
    {
        $("#addNewDeviceConfirmBtn").attr("disabled", false);
    }
    else
    {
        $("#addNewDeviceConfirmBtn").attr("disabled", true);
    }
}
