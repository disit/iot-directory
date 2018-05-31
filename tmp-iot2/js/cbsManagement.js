
var cbnamesArray, addCbConditionsArray;

function showAddCbModal()
{
    cbnamesArray = new Array();
    addCbConditionsArray = new Array();
    addCbConditionsArray['inputNameCB'] = false;
    addCbConditionsArray['inputIpCB'] = false;
    addCbConditionsArray['inputPortCB'] = false;
    //addCbConditionsArray['selectProtocolCB'] = false;
    addCbConditionsArray['inputLatitudeCB'] = false;
    addCbConditionsArray['inputLongitudeCB'] = false;
    addCbConditionsArray['inputLoginCB'] = true;
    addCbConditionsArray['inputPasswordCB'] = true;

     $("#addContextBrokerConfirmBtn").attr("disabled", true);

	$("#infoTabCB #inputNameCB").on('input', function(){checkCbName(); checkAddCbConditions(); });
    
	$("#infoTabCB #inputIpCB").on('input', function(){checkCbIp(); checkAddCbConditions(); });
	
	$("#infoTabCB #inputPortCB").on('input', function(){checkCbPort(); checkAddCbConditions(); });
	
	$("#geoPositionTabCB #inputLatitudeCB").on('input', function(){checkCbLatitude(); checkAddCbConditions(); });
	    
	$("#geoPositionTabCB #inputLongitudeCB").on('input', function(){checkCbLongitude(); checkAddCbConditions(); });
	$("#securityTabCB #inputLoginCB").on('input', function(){checkCbLogin(); checkAddCbConditions(); });
	$("#securityTabCB #inputPasswordCB").on('input', function(){checkCbpassword(); checkAddCbConditions(); });  
     
}


function checkCbName()
{
    var message = null;
    
    if($("#infoTabCB #inputNameCB").val().length === 0)
    {
        $("#inputNameCBMsg").css("color", "red");
        message = 'Context Broker name is mandatory';
        addCbConditionsArray['inputNameCB'] = false;
    }
    else if($("#infoTabCB #inputNameCB").val().length < 5)
    {
        $("#inputNameCBMsg").css("color", "red");
        message = 'Context Broker (at least 5 chars long)';
        addCbConditionsArray['inputNameCB'] = false;
    }
    else
    {
		//Check if this part is necessary
        if(($.inArray($("#infoTabCB #inputNameCB").val(), cbnamesArray, 0) > 0)||($("#infoTabCB #inputNameCB").val() === cbnamesArray[0]))
        {
            $("#inputNameCBMsg").css("color", "red");
            message = 'Context Broker name already used';
            addCbConditionsArray['inputNameCB'] = false;
        }
        else
        {
            $("#inputNameCBMsg").css("color", "#337ab7");
            message = 'Ok';
            addCbConditionsArray['inputNameCB'] = true;
        }
    }
    
    $("#inputNameCBMsg").html(message);
}




function checkCbIp()
{
    var message = null;
    var pattern = /^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;  /* /^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?).){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?) */
    var value = document.getElementById("inputIpCB").value;
	 
    if(value === '')
    {
        message = 'Ip is mandatory';
        addCbConditionsArray['inputIpCB'] = false;
        $("#inputIpCBMsg").css("color", "red");
    }
    else if(!pattern.test(value))
    {
        message = 'IP format is not correct (xxx.xxx.xxx.xxx)';
        addCbConditionsArray['inputIpCB'] = false;
        $("#inputIpCBMsg").css("color", "red");
    }
    else if(pattern.test(value))
    {
        message = 'Ok';
        addCbConditionsArray['inputIpCB'] = true;
        $("#inputIpCBMsg").css("color", "#337ab7");
    }
    
    $("#inputIpCBMsg").html(message);
}

function checkCbPort()
{
    var message = null;
    var pattern = /[0-9]{1,5}$/; /*  /^(0|[1-9][0-9]*)$/*/
    var value = document.getElementById("inputPortCB").value;
	
    if(value === '')
    {
        message = 'Port is mandatory';
        addCbConditionsArray['inputPortCB'] = false;
        $("#inputPortCBMsg").css("color", "red");
    }
    else if(!pattern.test(value))
    {
        message = 'Port should be postive number';
        addCbConditionsArray['inputPortCB'] = false;
        $("#inputPortCBMsg").css("color", "red");
    }
    else if(pattern.test(value))
    {
        message = 'Ok';
        addCbConditionsArray['inputPortCB'] = true;
        $("#inputPortCBMsg").css("color", "#337ab7");
    }
    
    $("#inputPortCBMsg").html(message);
}


function checkCbLatitude()
{
    var message = null;
    //var pattern = /^(\+|-)?(?:90(?:(?:\.0{1,6})?)|(?:[0-9]|[1-8][0-9])(?:(?:\.[0-9]{1,6})?))$/;
    var pattern = /^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?)$/;
	var value = document.getElementById("inputLatitudeCB").value;
	
    if(value === '')
    {
        message = 'Latitude is mandatory';
        addCbConditionsArray['inputLatitudeCB'] = false;
        $("#inputLatitudeCBMsg").css("color", "red");
    }
    else if(!pattern.test(value))
    {
        message = 'Latitude format is not correct ';
        addCbConditionsArray['inputLatitudeCB'] = false;
        $("#inputLatitudeCBMsg").css("color", "red");
    }
    else if(pattern.test(value))
    {
        message = 'Ok';
        addCbConditionsArray['inputLatitudeCB'] = true;
        $("#inputLatitudeCBMsg").css("color", "#337ab7");
    }
    
    $("#inputLatitudeCBMsg").html(message);
}


function checkCbLongitude()
{
    var message = null;
   // var reg = new RegExp("^-?([1-8]?[1-9]|[1-9]0)\.{1}\d{1,6}");
    var pattern = /^[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)$/;
	var value = document.getElementById("inputLongitudeCB").value;
	if(value === '')
    {
        message = 'Longitude is mandatory';
        addCbConditionsArray['inputLongitudeCB'] = false;
        $("#inputLongitudeCBMsg").css("color", "red");
    }
    else if(!pattern.test(value))
    {
        message = 'Latitude format is not correct ';
        addCbConditionsArray['inputLongitudeCB'] = false;
        $("#inputLongitudeCBMsg").css("color", "red");
    }
    else if(pattern.test(value))
    {
        message = 'Ok';
        addCbConditionsArray['inputLongitudeCB'] = true;
        $("#inputLongitudeCBMsg").css("color", "#337ab7");
    }
    
    $("#inputLongitudeCBMsg").html(message);
}


function checkCbLogin()
{
    var message = null;
    var value = document.getElementById("inputLoginCB").value;
	
    if ((value.length < 6) && (value.length > 0))
      {
        message = 'If you have a login, it should contain at least 5 characters';
        addCbConditionsArray['inputLoginCB'] = false;
        $("#inputLoginCBMsg").css("color", "red");
    }
    else if(value === '')
    {
        message = 'Longin is not mandatory but good to have';
        addCbConditionsArray['inputLoginCB'] = true;
        $("#inputLoginCBMsg").css("color", "yellow");
    }
    else if(value.length > 5)
    {
        message = 'Ok';
        addCbConditionsArray['inputLoginCB'] = true;
        $("#inputLoginCBMsg").css("color", "#337ab7");
    }
    
    $("#inputLoginCBMsg").html(message);
}


function checkCbpassword()
{
    var message = null;
     var value = document.getElementById("inputPasswordCB").value;
    if ((value.length < 6) && (value.length > 0))
      {
        message = 'If you have a password, it should contain at least 5 characters';
        addCbConditionsArray['inputPasswordCB'] = false;
        $("#inputPasswordCBMsg").css("color", "red");
    }
    else if(value === '')
    {
        message = 'Longin is not mandatory but good to have';
        addCbConditionsArray['inputPasswordCB'] = true;
        $("#inputPasswordCBMsg").css("color", "yellow");
    }
    else if(value.length > 5)
    {
        message = 'Ok';
        addCbConditionsArray['inputPasswordCB'] = true;
        $("#inputPasswordCBMsg").css("color", "#337ab7");
    }
    
    $("#inputPasswordCBMsg").html(message);

}


function checkAddCbConditions()
{
    var enableButton = true;
    // console.log(addCbConditionsArray);
    for(var key in addCbConditionsArray) 
    {
        if(addCbConditionsArray[key] === false)
        {
            enableButton = false;
            break;
        }
    }
    console.log("value enabled" +  enableButton);
    if(enableButton)
    {
        $("#addContextBrokerConfirmBtn").attr("disabled", false);
    }
    else
    {
        $("#addContextBrokerConfirmBtn").attr("disabled", true);
    }
}