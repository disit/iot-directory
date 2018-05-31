
var devicenamesArray= new Array();
var  addDeviceConditionsArray = new Array();
    addDeviceConditionsArray['inputNameDevice'] = false;
    addDeviceConditionsArray['inputLatitudeDevice'] = false;
    addDeviceConditionsArray['inputLongitudeDevice'] = false;
	addDeviceConditionsArray['inputTypeDevice'] = false;
	addDeviceConditionsArray['inputFrequencyDevice'] = true;
	addDeviceConditionsArray['addlistAttributes'] = false;

function showAddDeviceModal()
{
    $("#addNewDeviceConfirmBtn").attr("disabled", true);
    	
    $("#addInfoTabDevice #inputNameDevice").on('keyup change', function(){checkDeviceName(); checkAddDeviceConditions();});
   // $("#addInfoTabDevice #inputNameDevice").on('input', checkAddDeviceConditions);
	//$("#addInfoTabDevice #inputNameDevice").on('input', function(){checkDeviceName(); checkAddDeviceConditions(); });
	$("#addGeoPositionTabDevice #inputLatitudeDevice").on('keyup change', function(){checkDeviceLatitude(); checkAddDeviceConditions(); });
	$("#addGeoPositionTabDevice #inputLongitudeDevice").on('keyup change', function(){checkDeviceLongitude(); checkAddDeviceConditions(); });
	$("#addInfoTabDevice #inputTypeDevice").on('keyup change', function(){checkDeviceType(); checkAddDeviceConditions(); });
	$("#addInfoTabDevice #inputFrequencyDevice").on('keyup change', function(){checkFrequencyType(); checkAddDeviceConditions(); });
	$("#addSchemaTabDevice").on('keyup change', function(){//checkValue();
	checkAddDeviceConditions(); });
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

function checkDeviceType()
{
    var message = null;
    
    if($("#addInfoTabDevice #inputTypeDevice").val().length === 0)
    {
        $("#inputTypeDeviceMsg").css("color", "red");
        message = 'Device Type is mandatory';
        addDeviceConditionsArray['inputTypeDevice'] = false;
    }
	/*
    else if($("#addInfoTabDevice #inputTypeDevice").val().length < 5)
    {
        $("#inputTypeDeviceMsg").css("color", "red");
        message = 'Type name (at least 5 chars long)';
        addDeviceConditionsArray['inputTypeDevice'] = false;
    }
	*/
    else
    {
		//Check if this part is necessary
        if(($.inArray($("#addInfoTabDevice #inputTypeDevice").val(), devicenamesArray, 0) > 0)||($("#addInfoTabDevice #inputTypeDevice").val() === devicenamesArray[0]))
        {
            $("#inputTypeDeviceMsg").css("color", "red");
            message = 'Device Type already used';
            addDeviceConditionsArray['inputTypeDevice'] = false;
        }
        else
        {
            $("#inputTypeDeviceMsg").css("color", "#337ab7");
            message = 'Ok';
            addDeviceConditionsArray['inputTypeDevice'] = true;
        }
    }
    
    $("#inputTypeDeviceMsg").html(message);
}
function checkFrequencyType()
{
    var message = null;
	var pattern = /^\d+$/;
	var value = document.getElementById("inputFrequencyDevice").value;
    
    if($("#addInfoTabDevice #inputFrequencyDevice").val().length === 0)
    {
        $("#inputFrequencyDeviceMsg").css("color", "red");
        message = 'Device Frequency is mandatory';
        addDeviceConditionsArray['inputFrequencyDevice'] = false;
    }
    else
    {
		 if(!pattern.test(value))
		{
			message = 'Frequency format is not correct ';
			addDeviceConditionsArray['inputFrequencyDevice'] = false;
			$("#inputFrequencyDeviceMsg").css("color", "red");
		}
		else if(pattern.test(value))
		{
			message = 'Ok';
			addDeviceConditionsArray['inputFrequencyDevice'] = true;
			$("#inputFrequencyDeviceMsg").css("color", "#337ab7");
		}
    }
    
    $("#inputFrequencyDeviceMsg").html(message);
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

/*
function checkValue()
{
    var message = null;
    
    if($("#addSchemaTabDevice #addlistAttributes").val().length === 0)
    {
        $("#addlistAttributesMsg").css("color", "red");
        message = 'One value is mandatory';
        addDeviceConditionsArray['addlistAttributes'] = false;
    }
    else
    {
		
		$("#addlistAttributesMsg").css("color", "#337ab7");
		message = 'Ok';
		addDeviceConditionsArray['addlistAttributes'] = true;
        
    }
    
    $("#addlistAttributesMsg").html(message);
}
*/
	
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
    console.log(JSON.stringify(addDeviceConditionsArray));
    if(enableButton)
    {
        $("#addNewDeviceConfirmBtn").attr("disabled", false);
    }
    else
    {
        $("#addNewDeviceConfirmBtn").attr("disabled", true);
    }
}
