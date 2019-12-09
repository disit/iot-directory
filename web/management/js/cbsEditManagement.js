
var cbnamesArray, editCbConditionsArray;

editCbConditionsArray = new Array();

function showEditCbModal()
{
    cbnamesArray = new Array();
    editCbConditionsArray['inputIpCBM'] = false;
    editCbConditionsArray['inputPortCBM'] = false;
    editCbConditionsArray['inputLatitudeCBM'] = false;
    editCbConditionsArray['inputLongitudeCBM'] = false;
    editCbConditionsArray['inputUrlOrionCallbackM'] = false;
	
	$("#editInfoTabCB #inputIpCBM").on('input', checkEditCbIp);
	$("#editInfoTabCB #inputIpCBM").on('input', checkEditCbConditions);
	
	$("#editInfoTabCB #inputPortCBM").on('input', checkEditCbPort);
	$("#editInfoTabCB #inputPortCBM").on('input', checkEditCbConditions);

	$("#editInfoTabCB #selectProtocolCBM").on('change', checkEditCbUrlOrionCallback);
        $("#editInfoTabCB #selectProtocolCBM").on('change', checkEditCbConditions);

        $("#editInfoTabCB #selectKindCBM").on('change', checkEditCbUrlOrionCallback);
        $("#editInfoTabCB #selectKindCBM").on('change', checkEditCbConditions);
	
	$("#editGeoPositionTabCB #inputLatitudeCBM").on('input', checkEditCbLatitude);
	$("#editGeoPositionTabCB #inputLatitudeCBM").on('input', checkEditCbConditions);
	
	$("#editGeoPositionTabCB #inputLongitudeCBM").on('input', checkEditCbLongitude);
	$("#editGeoPositionTabCB #inputLongitudeCBM").on('input', checkEditCbConditions);

        $("#editSubscriptionTabCB #inputUrlOrionCallbackM").on('input', checkEditCbUrlOrionCallback);
        $("#editSubscriptionTabCB #inputUrlOrionCallbackM").on('input', checkEditCbConditions);
	
	checkEditCbIp();
	checkEditCbPort();
	checkEditCbLatitude();
	checkEditCbLongitude();
	checkEditCbUrlOrionCallback();
}

function checkEditCbIp()
{
    var message = null;
    var value = document.getElementById("inputIpCBM").value;
	 
    if(value === '')
    {
        message = 'Ip is mandatory';
        editCbConditionsArray['inputIpCBM'] = false;
        $("#inputIpCBMMsg").css("color", "red");
    }
    else 
    {
        message = 'Ok';
        editCbConditionsArray['inputIpCBM'] = true;
        $("#inputIpCBMMsg").css("color", "#337ab7");
    }
    
    $("#inputIpCBMMsg").html(message);
}

function checkEditCbPort()
{
    var message = null;
    var pattern = /[0-9]{1,5}$/; /*  /^(0|[1-9][0-9]*)$/*/
    var value = document.getElementById("inputPortCBM").value;
	
    if(value === '')
    {
        message = 'Port is mandatory';
        editCbConditionsArray['inputPortCBM'] = false;
        $("#inputPortCBMMsg").css("color", "red");
    }
    else if(!pattern.test(value))
    {
        message = 'Port should be postive number';
        editCbConditionsArray['inputPortCBM'] = false;
        $("#inputPortCBMMsg").css("color", "red");
    }
    else if(pattern.test(value))
    {
        message = 'Ok';
        editCbConditionsArray['inputPortCBM'] = true;
        $("#inputPortCBMMsg").css("color", "#337ab7");
    }
    
    $("#inputPortCBMMsg").html(message);
}


function checkEditCbLatitude()
{
    var message = null;
    //var pattern = /^(\+|-)?(?:90(?:(?:\.0{1,6})?)|(?:[0-9]|[1-8][0-9])(?:(?:\.[0-9]{1,6})?))$/;
    var pattern = /^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?)$/;
	var value = document.getElementById("inputLatitudeCBM").value;
	
    if(value === '')
    {
        message = 'Latitude is mandatory';
        editCbConditionsArray['inputLatitudeCBM'] = false;
        $("#inputLatitudeCBMMsg").css("color", "red");
    }
    else if(!pattern.test(value))
    {
        message = 'Latitude format is not correct ';
        editCbConditionsArray['inputLatitudeCBM'] = false;
        $("#inputLatitudeCBMMsg").css("color", "red");
    }
    else if(pattern.test(value))
    {
        message = 'Ok';
        editCbConditionsArray['inputLatitudeCBM'] = true;
        $("#inputLatitudeCBMMsg").css("color", "#337ab7");
    }
    
    $("#inputLatitudeCBMMsg").html(message);
}


function checkEditCbLongitude()
{
    var message = null;
   // var reg = new RegExp("^-?([1-8]?[1-9]|[1-9]0)\.{1}\d{1,6}");
    var pattern = /^[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)$/;
	var value = document.getElementById("inputLongitudeCBM").value;
	if(value === '')
    {
        message = 'Longitude is mandatory';
        editCbConditionsArray['inputLongitudeCBM'] = false;
        $("#inputLongitudeCBMMsg").css("color", "red");
    }
    else if(!pattern.test(value))
    {
        message = 'Latitude format is not correct ';
        editCbConditionsArray['inputLongitudeCBM'] = false;
        $("#inputLongitudeCBMMsg").css("color", "red");
    }
    else if(pattern.test(value))
    {
        message = 'Ok';
        editCbConditionsArray['inputLongitudeCBM'] = true;
        $("#inputLongitudeCBMMsg").css("color", "#337ab7");
    }
    
    $("#inputLongitudeCBMMsg").html(message);
}


function checkEditCbLogin()
{
    var message = null;
    var value = document.getElementById("inputLoginCBM").value;
	
    if ((value.length < 6) && (value.length > 0))
      {
        message = 'If you have a login, it should contain at least 5 characters';
        editCbConditionsArray['inputLoginCBM'] = false;
        $("#inputLoginCBMMsg").css("color", "red");
    }
    else if(value === '')
    {
        message = 'Longin is not mandatory but good to have';
        editCbConditionsArray['inputLoginCBM'] = true;
        $("#inputLoginCBMMsg").css("color", "yellow");
    }
    else if(value.length > 5)
    {
        message = 'Ok';
        editCbConditionsArray['inputLoginCBM'] = true;
        $("#inputLoginCBMMsg").css("color", "#337ab7");
    }
    
    $("#inputLoginCBMMsg").html(message);
}


function checkEditCbpassword()
{
    var message = null;
     var value = document.getElementById("inputPasswordCBM").value;
    if ((value.length < 6) && (value.length > 0))
      {
        message = 'If you have a password, it should contain at least 5 characters';
        editCbConditionsArray['inputPasswordCBM'] = false;
        $("#inputPasswordCBMMsg").css("color", "red");
    }
    else if(value === '')
    {
        message = 'Longin is not mandatory but good to have';
        editCbConditionsArray['inputPasswordCBM'] = true;
        $("#inputPasswordCBMMsg").css("color", "yellow");
    }
    else if(value.length > 5)
    {
        message = 'Ok';
        editCbConditionsArray['inputPasswordCBM'] = true;
        $("#inputPasswordCBMMsg").css("color", "#337ab7");
    }
    
    $("#inputPasswordCBMMsg").html(message);

}


function checkEditCbConditions()
{
    var enableButton = true;
    console.log(editCbConditionsArray);
    for(var key in editCbConditionsArray) 
    {
        if(editCbConditionsArray[key] === false)
        {
            enableButton = false;
            break;
        }
    }
    console.log("value enabled" +  enableButton);
    if(enableButton)
    {
        $("#editContextBrokerConfirmBtn").attr("disabled", false);
    }
    else
    {
        $("#editContextBrokerConfirmBtn").attr("disabled", true);
    }
}

function checkEditCbUrlOrionCallback()
{
    var message = null;
    var pattern = /^(http:\/\/www\.|https:\/\/www\.|http:\/\/|https:\/\/)?[a-z0-9./:]+$/
    var kind = document.getElementById("selectKindCBM").value;
    var protocol = document.getElementById("selectProtocolCBM").value;
    var url = document.getElementById("inputUrlOrionCallbackM").value;

    console.log("kind:"+kind+" protocol:"+protocol+" value:"+url);

    if ((kind === 'internal')&&(protocol === 'ngsi')){
        if(url === '')
        {
                message = 'Url Orion Callback is mandatory';
                editCbConditionsArray['inputUrlOrionCallbackM'] = false;
                $("#selectUrlOrionCallbackMsgM").css("color", "red");
        }
        else if(!pattern.test(url))
        {
                message = 'Url Orion Callback is malformed';
                editCbConditionsArray['inputUrlOrionCallbackM'] = false;
                $("#selectUrlOrionCallbackMsgM").css("color", "red");
        }
        else
        {
                message = 'Ok';
                editCbConditionsArray['inputUrlOrionCallbackM'] = true;
                $("#selectUrlOrionCallbackMsgM").css("color", "#337ab7");
        }
    }
    //else is not considered because this tab is not showed

    $("#selectUrlOrionCallbackMsgM").html(message);
}

