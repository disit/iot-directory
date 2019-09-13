
var  addMyDeviceConditionsArray = new Array();



function showAddMyDeviceModal()
{
	
	addMyDeviceConditionsArray['inputNameDeviceUser'] = false;
	addMyDeviceConditionsArray['inputLatitudeDeviceUser'] = false;
	addMyDeviceConditionsArray['inputLongitudeDeviceUser'] = false;
	addMyDeviceConditionsArray['KeyOneDeviceUser'] = false;
	addMyDeviceConditionsArray['KeyTwoDeviceUser'] = false;
	addMyDeviceConditionsArray['deviceModel'] = false;
	//addMyDeviceConditionsArray['inputTypeDeviceUser'] = false;
	
	
	$("#addMyNewDeviceConfirmBtn").attr("disabled", true); 
	
	
	$("#inputNameDeviceUser").on('input', checkDeviceNameUser); 
	$("#inputNameDeviceUser").on('input', checkAddMyDeviceConditions);
	
	$("#inputLatitudeDeviceUser").on('input', checkDeviceLatitudeUser);
	$("#inputLatitudeDeviceUser").on('input', checkAddMyDeviceConditions);
	
	$("#inputLongitudeDeviceUser").on('input', checkDeviceLongitudeUser);
	$("#inputLongitudeDeviceUser").on('input', checkAddMyDeviceConditions);
	
	
	$("#inputLongitudeDeviceUser").on('input', checkDeviceLongitudeUser);
	$("#inputLongitudeDeviceUser").on('input', checkAddMyDeviceConditions);
	
//	$("#inputTypeDeviceUser").on('input', checkDeviceTypeUser);
//	$("#inputTypeDeviceUser").on('input', checkAddMyDeviceConditions);
	
	$("#KeyOneDeviceUser").on('input', UserKey);
	$("#KeyOneDeviceUser").on('input', checkAddMyDeviceConditions);
		

	$("#KeyTwoDeviceUser").on('input', UserKey);
	$("#KeyTwoDeviceUser").on('input', checkAddMyDeviceConditions);

	
	checkDeviceNameUser();
    checkDeviceLatitudeUser();
    checkDeviceLongitudeUser();
	UserKey(); 
	checkModel();
			
   // checkDeviceTypeUser();


}


function checkDeviceNameUser()
{
    var message = null;
    var regex=/[^a-z0-9_-]/gi;
    
    if($("#inputNameDeviceUser").val().length === 0)
    {
        $("#inputNameDeviceUserMsg").css("color", "red");
        message = 'Device name is mandatory';
        addMyDeviceConditionsArray['inputNameDeviceUser'] = false;
    }
    else if($("#inputNameDeviceUser").val().length < 5)
    {
        $("#inputNameDeviceUserMsg").css("color", "red");
        message = 'Device name (at least 5 chars long)';
        addMyDeviceConditionsArray['inputNameDeviceUser'] = false;
    }
    else if(regex.test($("#inputNameDeviceUser").val()))
    {
        $("#inputNameDeviceUserMsg").css("color", "red");
        message = 'No special characters are allowed in a device name ';
        addMyDeviceConditionsArray['inputNameDeviceUser'] = false;
    }
    else
    {
		
		$("#inputNameDeviceUserMsg").css("color", "#337ab7");
		message = 'Ok';
		addMyDeviceConditionsArray['inputNameDeviceUser'] = true;
        
    }
    
    $("#inputNameDeviceUserMsg").html(message);
}



function checkDeviceTypeUser()
{
    var message = null;
    
    if($("#inputTypeDeviceUser").val().length === 0)
    {
        $("#inputTypeDeviceUserMsg").css("color", "red");
        message = 'Device Type is mandatory';
        addMyDeviceConditionsArray['inputTypeDeviceUser'] = false;
    }

    else
    {
		
		$("#inputTypeDeviceUserMsg").css("color", "#337ab7");
		message = 'Ok';
		addMyDeviceConditionsArray['inputTypeDeviceUser'] = true;
        
    }
    
    $("#inputTypeDeviceUserMsg").html(message);
}


function checkDeviceLatitudeUser()
{
    var message = null;
    var pattern = /^(\+|-)?(?:90(?:(?:\.0{1,6})?)|(?:[0-9]|[1-8][0-9])(?:(?:\.[0-9]{1,6})?))$/;
    //var pattern = /^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?)$/;
	var value = document.getElementById("inputLatitudeDeviceUser").value;
	
    if(value === '')
    {
        message = 'Latitude is mandatory';
        addMyDeviceConditionsArray['inputLatitudeDeviceUser'] = false;
        $("#inputLatitudeDeviceUserMsg").css("color", "red");
    }
    else if(!pattern.test(value))
    {
        message = 'Latitude format is not correct ';
        addMyDeviceConditionsArray['inputLatitudeDeviceUser'] = false;
        $("#inputLatitudeDeviceUserMsg").css("color", "red");
    }
    else if(pattern.test(value))
    {
        message = 'Ok';
        addMyDeviceConditionsArray['inputLatitudeDeviceUser'] = true;
        $("#inputLatitudeDeviceUserMsg").css("color", "#337ab7");
    }
    
    $("#inputLatitudeDeviceUserMsg").html(message);
}


function checkDeviceLongitudeUser()
{
    var message = null;
   // var reg = new RegExp("^-?([1-8]?[1-9]|[1-9]0)\.{1}\d{1,6}");
    var pattern = /^[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)$/;
	var value = document.getElementById("inputLongitudeDeviceUser").value;
	if(value === '')
    {
        message = 'Longitude is mandatory';
        addMyDeviceConditionsArray['inputLongitudeDeviceUser'] = false;
        $("#inputLongitudeDeviceUserMsg").css("color", "red");
    }
    else if(!pattern.test(value))
    {
        message = 'Latitude format is not correct ';
        addMyDeviceConditionsArray['inputLongitudeDeviceUser'] = false;
        $("#inputLongitudeDeviceUserMsg").css("color", "red");
    }
    else if(pattern.test(value))
    {
        message = 'Ok';
        addMyDeviceConditionsArray['inputLongitudeDeviceUser'] = true;
        $("#inputLongitudeDeviceUserMsg").css("color", "#337ab7");
    }
    
    $("#inputLongitudeDeviceUserMsg").html(message);
}

function checkModel()
{
    var message = null;
	var nameOpt =  document.getElementById('selectModel').options;
	var selectednameOpt = document.getElementById('selectModel').selectedIndex;
	 
    if(nameOpt[selectednameOpt].value =="")
    {
        $("#selectModelMsg").css("color", "red");
        message = 'Device Model is mandatory';
        addMyDeviceConditionsArray['deviceModel'] = false;
    }
    else
    {
		$("#selectModelMsg").css("color", "#337ab7");
		message = 'Ok';
		addMyDeviceConditionsArray['deviceModel'] = true;   
    }
    $("#selectModelMsg").html(message);
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
				//document.getElementById("addMyNewDeviceConfirmBtn").disabled = true;
				addMyDeviceConditionsArray['KeyOneDeviceUser'] = false;
				addMyDeviceConditionsArray['KeyTwoDeviceUser'] = false;
				$("#KeyOneDeviceUserMsg").css("color", "red");
				$("#KeyTwoDeviceUserMsg").css("color", "red");
				
			}
			else if(!pattern.test(value1) || !pattern.test(value2))
			{
				message = 'The Key should contain at least one special character and a number';
				//document.getElementById("addMyNewDeviceConfirmBtn").disabled = true;
				addMyDeviceConditionsArray['KeyOneDeviceUser'] = false;
				addMyDeviceConditionsArray['KeyTwoDeviceUser'] = false;
				$("#KeyOneDeviceUserMsg").css("color", "red");
				$("#KeyTwoDeviceUserMsg").css("color", "red");
			}
			else if(pattern.test(value1) && pattern.test(value2))
			{
				message = 'Ok';
				//document.getElementById("addMyNewDeviceConfirmBtn").disabled = false;
				addMyDeviceConditionsArray['KeyOneDeviceUser'] = true;
				addMyDeviceConditionsArray['KeyTwoDeviceUser'] = true;
				$("#KeyOneDeviceUserMsg").css("color", "#337ab7");
				$("#KeyTwoDeviceUserMsg").css("color", "#337ab7");
				// gb_key1 = $("#KeyOneDeviceUser").value;
			    // gb_key2 = $("#KeyTwoDeviceUser").value;
			}
			
			$("#KeyOneDeviceUserMsg").html(message);
			$("#KeyTwoDeviceUserMsg").html(message);
	}


function checkAddMyDeviceConditions()
{
    var enableButton = true;
	
	  // console.log(addMyDeviceConditionsArray);
    
    for(var key in addMyDeviceConditionsArray) 
    {
        if(addMyDeviceConditionsArray[key] === false)
        {
            enableButton = false;
            break;
        }
    }
    console.log(JSON.stringify(addMyDeviceConditionsArray));
    if(enableButton)
    {
      
		$("#addMyNewDeviceConfirmBtn").attr("disabled", false); 
    }
    else
    {
       
		$("#addMyNewDeviceConfirmBtn").attr("disabled", true); 
    }
}

