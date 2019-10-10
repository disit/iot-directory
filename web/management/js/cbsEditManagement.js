
var cbnamesArray, editCbConditionsArray;

editCbConditionsArray = new Array();

function showEditCbModal()
{
    cbnamesArray = new Array();
    editCbConditionsArray['inputIpCBM'] = false;
    editCbConditionsArray['inputPortCBM'] = false;
    //editCbConditionsArray['selectProtocolCB'] = false;
    editCbConditionsArray['inputLatitudeCBM'] = false;
    editCbConditionsArray['inputLongitudeCBM'] = false;
   // editCbConditionsArray['inputLoginCBM'] = true;
    //editCbConditionsArray['inputPasswordCBM'] = true;

     //$("#editContextBrokerConfirmBtn").attr("disabled", true);
	 
	 
	
	$("#editInfoTabCB #inputIpCBM").on('input', checkEditCbIp);
	$("#editInfoTabCB #inputIpCBM").on('input', checkEditCbConditions);
	
	$("#editInfoTabCB #inputPortCBM").on('input', checkEditCbPort);
	$("#editInfoTabCB #inputPortCBM").on('input', checkEditCbConditions);
	
	
	$("#editGeoPositionTabCB #inputLatitudeCBM").on('input', checkEditCbLatitude);
	$("#editGeoPositionTabCB #inputLatitudeCBM").on('input', checkEditCbConditions);
	
	$("#editGeoPositionTabCB #inputLongitudeCBM").on('input', checkEditCbLongitude);
	$("#editGeoPositionTabCB #inputLongitudeCBM").on('input', checkEditCbConditions);
	
	/* 
	$("#editSecurityTabCB #inputLoginCBM").on('input', checkEditCbLogin);
	$("#editSecurityTabCB #inputLoginCBM").on('input', checkEditCbConditions);
	
	$("#editSecurityTabCB #inputPasswordCBM").on('input', checkEditCbpassword);
	$("#editSecurityTabCB #inputPasswordCBM").on('input', checkEditCbConditions);

 */

	checkEditCbIp();
	checkEditCbPort();
	checkEditCbLatitude();
	checkEditCbLongitude();
	//checkEditCbLogin();
	//checkEditCbpassword();


     
}

/* 
function checkEditCbName()
{
    var message = null;
    
    if($("#editInfoTabCB #inputNameCBM").val().length === 0)
    {
        $("#inputNameCBMMsg").css("color", "red");
        message = 'Context Broker name is mandatory';
        editCbConditionsArray['inputNameCBM'] = false;
    }
    else if($("#editInfoTabCB #inputNameCBM").val().length < 5)
    {
        $("#inputNameCBMMsg").css("color", "red");
        message = 'Context Broker (at least 5 chars long)';
        editCbConditionsArray['inputNameCBM'] = false;
    }
    else
    {
		   $("#inputNameCBMMsg").css("color", "#337ab7");
            message = 'Ok';
            editCbConditionsArray['inputNameCBM'] = true;
        
    }
    
    $("#inputNameCBMMsg").html(message);
}

 */


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
