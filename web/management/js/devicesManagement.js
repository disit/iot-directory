
var devicenamesArray= new Array();
var  addMyDeviceConditionsArray = new Array();
var  addDeviceConditionsArray = new Array();
    

	addMyDeviceConditionsArray['inputNameDeviceUser'] = false;
	addMyDeviceConditionsArray['inputLatitudeDeviceUser'] = false;
	addMyDeviceConditionsArray['inputLongitudeDeviceUser'] = false;
	addMyDeviceConditionsArray['inputTypeDeviceUser'] = false;
	addMyDeviceConditionsArray['KeyOneDeviceUser'] = false;
	addMyDeviceConditionsArray['KeyTwoDeviceUser'] = false;
	
	
	addDeviceConditionsArray['gatewayURI'] = false;
	addDeviceConditionsArray['contextbroker'] = false;
	addDeviceConditionsArray['kind'] = false;
	addDeviceConditionsArray['format'] = false;
	addDeviceConditionsArray['protocol'] = false;
	
	addMyDeviceConditionsArray['deviceModel'] = false;
	addMyDeviceConditionsArray['oneAttribute'] = false;
	addMyDeviceConditionsArray['attributeWithName'] = false;
	

function showAddDeviceModal()
{
//	var  addDeviceConditionsArray = new Array();
	addDeviceConditionsArray['inputNameDevice'] = false;
	//addDeviceConditionsArray['inputNameValue'] = false;
    addDeviceConditionsArray['inputLatitudeDevice'] = false;
    addDeviceConditionsArray['inputLongitudeDevice'] = false;	
	addDeviceConditionsArray['inputTypeDevice'] = false;
	addDeviceConditionsArray['inputFrequencyDevice'] = true;
	addDeviceConditionsArray['inputMacDevice'] = true;
	
	addDeviceConditionsArray['gatewayURI'] = false;
	addDeviceConditionsArray['contextbroker'] = false;
	addDeviceConditionsArray['kind'] = false;
	addDeviceConditionsArray['format'] = false;
	addDeviceConditionsArray['protocol'] = false;
	
	
	
	
	//addDeviceConditionsArray['addlistAttributes'] = false;
	addMyDeviceConditionsArray['KeyOneDeviceUser'] = false;
	addMyDeviceConditionsArray['KeyTwoDeviceUser'] = false;
	addMyDeviceConditionsArray['deviceModel'] = false;
	addMyDeviceConditionsArray['oneAttribute'] = false;
	addMyDeviceConditionsArray['attributeWithName'] = false;
	
	
	
	
	 $("#addNewDeviceConfirmBtn").attr("disabled", true);
	
	$("#addInfoTabDevice #inputNameDevice").on('input', checkDeviceName); 
	$("#addInfoTabDevice #inputNameDevice").on('input', checkAddDeviceConditions);
	
	$("#addGeoPositionTabDevice #inputLatitudeDevice").on('input', checkDeviceLatitude);
	$("#addGeoPositionTabDevice #inputLatitudeDevice").on('input', checkAddDeviceConditions);
	
	$("#addGeoPositionTabDevice #inputLongitudeDevice").on('input', checkDeviceLongitude);
	$("#addGeoPositionTabDevice #inputLongitudeDevice").on('input', checkAddDeviceConditions);
	
	
	$("#addInfoTabDevice #inputTypeDevice").on('input', checkDeviceType);
	$("#addInfoTabDevice #inputTypeDevice").on('input', checkAddDeviceConditions);
	
	$("#addInfoTabDevice #inputFrequencyDevice").on('input', checkFrequencyType);
	$("#addInfoTabDevice #inputFrequencyDevice").on('input', checkAddDeviceConditions);
	
	$("#addInfoTabDevice #inputMacDevice").on('input', checkMAC);
	$("#addInfoTabDevice #inputMacDevice").on('input', checkAddDeviceConditions);

	
	$("#KeyOneDeviceUser").on('input', UserKey);
	$("#KeyOneDeviceUser").on('input', checkAddMyDeviceConditions);
		

	$("#KeyTwoDeviceUser").on('input', UserKey);
	$("#KeyTwoDeviceUser").on('input', checkAddMyDeviceConditions);

	//marco: last inserted controls
	
	$("#selectContextBroker").on('input', checkSelectionCB);
	$("#selectContextBroker").on('input', checkAddMyDeviceConditions);

	
	$("#selectKindDevice").on('input', checkSelectionKind);
	$("#selectKindDevice").on('input', checkAddMyDeviceConditions);


    $("#selectProtocolDevice").on('input', checkSelectionProtocol);
	$("#selectProtocolDevice").on('input', checkAddMyDeviceConditions);
	
    $("#selectFormatDevice").on('input', checkSelectionFormat);
	$("#selectFormatDevice").on('input', checkAddMyDeviceConditions);


	$("#inputEdgeGatewayUri").on('input', checkUri);
	$("#inputEdgeGatewayUri").on('input', checkAddMyDeviceConditions);

	
    // end last inserted controls
	
    //$("#addSchemaTabDevice #addlistAttributes .row input:even").on('input', checkValueName);
	//$("#addSchemaTabDevice #addlistAttributes .row input:even").on('input', checkAddDeviceConditions);

	
	
	
	
    checkDeviceName();
    checkDeviceLatitude();
    checkDeviceLongitude();
    checkDeviceType();
    checkFrequencyType();
    checkMAC();
	checkModel();
	checkAtlistOneAttribute();
	checkSelectionCB();
	checkSelectionKind();
	checkSelectionProtocol();
	checkSelectionFormat();
	checkUri();
    $("#addDeviceModal").modal('show');

}

function checkEverything(){
    checkDeviceName();
    checkDeviceLatitude();
    checkDeviceLongitude();
    checkDeviceType();
    checkFrequencyType();
    checkMAC();
	checkModel();
	checkAtlistOneAttribute();
	checkSelectionCB();
	checkSelectionKind();
	checkSelectionProtocol();
	checkSelectionFormat();
	checkUri();
}

function showAddMyDeviceModal()
{
    $("#addNewDeviceConfirmBtn").attr("disabled", true);
	//$("#addMyNewDeviceConfirmBtn").attr("disabled", true); this will be in play for the mydevice

	$("#inputNameDeviceUser").on('keyup change', function(){checkDeviceNameUser(); checkAddMyDeviceConditions();});
	$("#inputLatitudeDeviceUser").on('keyup change', function(){checkDeviceLatitudeUser(); checkAddMyDeviceConditions(); });
	$("#inputLongitudeDeviceUser").on('keyup change', function(){checkDeviceLongitudeUser(); checkAddMyDeviceConditions(); });
	$("#inputTypeDeviceUser").on('keyup change', function(){checkDeviceTypeUser(); checkAddMyDeviceConditions(); });
    
    console.log("here");

}




function checkSelectionProtocol()
{
    var message = null;
    
    if($("#addIOTBrokerTabDevice #selectProtocolDevice").val().length === 0)
    {
        $("#selectProtocolDeviceMsg").css("color", "red");
        message = 'Device protocol  is mandatory';
        addDeviceConditionsArray['protocol'] = false;
    }
    else 
    {
		$("#selectProtocolDeviceMsg").css("color", "#337ab7");
		message = 'Ok';
		addDeviceConditionsArray['protocol'] = true;
    }
    
    $("#selectProtocolDeviceMsg").html(message);
}

function checkSelectionFormat()
{
    var message = null;
    
    if($("#addIOTBrokerTabDevice #selectFormatDevice").val().length === 0)
    {
        $("#selectFormatDeviceMsg").css("color", "red");
        message = 'Device format  is mandatory';
        addDeviceConditionsArray['format'] = false;
    }
    else 
    {
		$("#selectFormatDeviceMsg").css("color", "#337ab7");
		message = 'Ok';
		addDeviceConditionsArray['format'] = true;
    }
    
    $("#selectFormatDeviceMsg").html(message);
}

	

function checkSelectionKind()
{
    var message = null;
    
    if($("#addIOTBrokerTabDevice #selectKindDevice").val().length === 0)
    {
        $("#selectKindDeviceMsg").css("color", "red");
        message = 'Device kind  is mandatory';
        addDeviceConditionsArray['kind'] = false;
    }
    else 
    {
		$("#selectKindDeviceMsg").css("color", "#337ab7");
		message = 'Ok';
		addDeviceConditionsArray['kind'] = true;
    }
    
    $("#selectKindDeviceMsg").html(message);
}



function checkSelectionCB()
{
    var message = null;
    
    if($("#addIOTBrokerTabDevice #selectContextBroker").val()!=undefined && $("#addIOTBrokerTabDevice #selectContextBroker").val().length === 0)
    {
        $("#selectContextBrokerMsg").css("color", "red");
        message = 'Context broker is mandatory';
        addDeviceConditionsArray['contextbroker'] = false;
    }
    else 
    {
		$("#selectContextBrokerMsg").css("color", "#337ab7");
		message = 'Ok';
		addDeviceConditionsArray['contextbroker'] = true;
    }
    
    $("#selectContextBrokerMsg").html(message);
}

function checkDeviceName()
{
    var message = null;
    var regex=/[^a-z0-9:_-]/gi;
    
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
    else if(regex.test($("#addInfoTabDevice #inputNameDevice").val()))
    {
        $("#inputNameDeviceMsg").css("color", "red");
        message = 'No special characters are allowed in Device name';
        addDeviceConditionsArray['inputNameDevice'] = false;
    }
    else
    {
	
		$("#inputNameDeviceMsg").css("color", "#337ab7");
		message = 'Ok';
		addDeviceConditionsArray['inputNameDevice'] = true;
	
    }
    
    $("#inputNameDeviceMsg").html(message);
}


function checkValueName()
{
    var message = null;
    var regex=/[^a-z0-9:._-]/gi;
    console.log("valore identificato " + $(this).val());
	console.log("elemento identificato " + $(this).parent().siblings().last().html());
	
    if($(this).val().length === 0)
    {
        $(this).parent().siblings().last().css("color", "red");
        message = 'Value name is mandatory';
        //addDeviceConditionsArray['inputNameValue'] = false;
    }
    else if($(this).val().length < 2)
    {
        $(this).parent().siblings().last().css("color", "red");
        message = 'Value name (at least 2 chars long)';
        //addDeviceConditionsArray['inputNameValue'] = false;
    }
    else if(regex.test($(this).val()))
    {
        $(this).parent().siblings().last().css("color", "red");
        message = 'No special characters are allowed in Value name';
        //addDeviceConditionsArray['inputNameValue'] = false;
    }
    else
    {
	
		$(this).parent().siblings().last().css("color", "#337ab7");
		message = 'Ok';
		//addDeviceConditionsArray['inputNameValue'] = true;
	
    }
    
    $(this).parent().siblings().last().html(message);
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
		//Check if this part is necessary
        if(($.inArray($("#inputNameDeviceUser").val(), devicenamesArray, 0) > 0)||($("#inputNameDeviceUser").val() === devicenamesArray[0]))
        {
            $("#inputNameDeviceUserMsg").css("color", "red");
            message = 'Device name already used';
            addMyDeviceConditionsArray['inputNameDeviceUser'] = false;
        }
        else
        {
            $("#inputNameDeviceUserMsg").css("color", "#337ab7");
            message = 'Ok';
            addMyDeviceConditionsArray['inputNameDeviceUser'] = true;
        }
    }
    
    $("#inputNameDeviceUserMsg").html(message);
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

    else
    {
       if ($("#addInfoTabDevice #inputTypeDevice").val().indexOf(' ')>-1)
                {
                        message = 'Device Type cannot contains blank space';
                        addDeviceConditionsArray['inputTypeDevice'] = false;
                        $("#inputTypeDeviceMsg").css("color", "red");
                }
                else
                {
                        message = 'Ok';
                        addDeviceConditionsArray['inputTypeDevice'] = true;
                        $("#inputTypeDeviceMsg").css("color", "#337ab7");
                } 
    }
    
    $("#inputTypeDeviceMsg").html(message);
}


function checkModel()
{
    var message = null;
	var nameOpt =  document.getElementById('selectModelDevice').options;
	var selectednameOpt = document.getElementById('selectModelDevice').selectedIndex;
	 
    if(nameOpt[selectednameOpt].value =="")
    {
        $("#inputModelDeviceMsg").css("color", "red");
        message = 'Device Model is mandatory';
        addDeviceConditionsArray['deviceModel'] = false;
    }
    else
    {
		$("#inputModelDeviceMsg").css("color", "#337ab7");
		message = 'Ok';
		addDeviceConditionsArray['deviceModel'] = true;   
    }
    $("#inputModelDeviceMsg").html(message);
}


function checkAtlistOneAttribute()
{
    var message = null;
	 
    if($("#addlistAttributes").html().length === 0)
    {
        $("#addlistAttributesMsg").css("color", "red");
        message = 'At least a value needs to be specified';
        addDeviceConditionsArray['oneAttribute'] = false;
    }
    else
    {
		$("#addlistAttributesMsg").css("color", "#337ab7");
		message = '';
		addDeviceConditionsArray['oneAttribute'] = true;   
    }
    $("#addlistAttributesMsg").html(message);
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
        if(($.inArray($("#inputTypeDeviceUser").val(), devicenamesArray, 0) > 0)||($("#inputTypeDeviceUser").val() === devicenamesArray[0]))
        {
            $("#inputTypeDeviceUserMsg").css("color", "red");
            message = 'Device Type already used';
            addMyDeviceConditionsArray['inputTypeDeviceUser'] = false;
        }
        else
        {
            $("#inputTypeDeviceUserMsg").css("color", "#337ab7");
            message = 'Ok';
            addMyDeviceConditionsArray['inputTypeDeviceUser'] = true;
        }
    }
    
    $("#inputTypeDeviceUserMsg").html(message);
}

function checkFrequencyType()
{
    var message = null;
	var pattern = /^\d+$/;
	//var value = document.getElementById("inputFrequencyDevice").value;
    
    if($("#addInfoTabDevice #inputFrequencyDevice").val().length === 0)
    {
        $("#inputFrequencyDeviceMsg").css("color", "red");
        message = 'Device Frequency is mandatory';
        addDeviceConditionsArray['inputFrequencyDevice'] = false;
	}	
    else
    {
		 if(!pattern.test($("#addInfoTabDevice #inputFrequencyDevice").val()))
		{
			message = 'Frequency format is not correct ';
			addDeviceConditionsArray['inputFrequencyDevice'] = false;
			$("#inputFrequencyDeviceMsg").css("color", "red");
		}
		else if(pattern.test($("#addInfoTabDevice #inputFrequencyDevice").val()))
		{
			message = 'Ok';
			addDeviceConditionsArray['inputFrequencyDevice'] = true;
			$("#inputFrequencyDeviceMsg").css("color", "#337ab7");
		}
    }
    
    $("#inputFrequencyDeviceMsg").html(message);
}





function checkUri()
{
    var message = null;
	var pattern = /^(?:http(s)?:\/\/)?[\w.-]+(?:\.[\w\.-]+)+[\w\-\._~:/?#[\]@!\$&'\(\)\*\+,;=.]+$/;
	
	if($("#addInfoTabDevice #inputEdgeGatewayUri").val().length === 0)
    {
        $("#inputEdgeGatewayUriMsg").css("color", "red");
        message = '';
        addDeviceConditionsArray['gatewayURI'] = true;
    }
	else {
			if(!pattern.test($("#addInfoTabDevice #inputEdgeGatewayUri").val()) || $("#addInfoTabDevice #selectEdgeGatewayType").val().length === 0)
				{
				message = 'An URI should be specified when selecting a edge/gateway type';
				addDeviceConditionsArray['gatewayURI'] = false;
				$("#inputEdgeGatewayUriMsg").css("color", "red");
				}
			else if(pattern.test($("#addInfoTabDevice #inputEdgeGatewayUri").val()))
			{
				message = 'Ok';
				addDeviceConditionsArray['gatewayURI'] = true;
				$("#inputEdgeGatewayUriMsg").css("color", "#337ab7");
			}
			
    }
    
    $("#inputEdgeGatewayUriMsg").html(message);
}



function checkMAC()
{
    var message = null;
	var pattern = /^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/;
	
	if($("#addInfoTabDevice #inputMacDevice").val().length === 0)
    {
        $("#inputMacDeviceMsg").css("color", "red");
        message = '';
        addDeviceConditionsArray['inputMacDevice'] = true;
    }
	else {
			if(!pattern.test($("#addInfoTabDevice #inputMacDevice").val()))
				{
				message = 'Mac format should be Letter (A-F) and number (eg. 3D:F2:C9:A6:B3:4F)';
				addDeviceConditionsArray['inputMacDevice'] = false;
				$("#inputMacDeviceMsg").css("color", "red");
				}
			else if(pattern.test($("#addInfoTabDevice #inputMacDevice").val()))
			{
				message = 'Ok';
				addDeviceConditionsArray['inputMacDevice'] = true;
				$("#inputMacDeviceMsg").css("color", "#337ab7");
			}
    }
    
    $("#inputMacDeviceMsg").html(message);
}

function checkDeviceLatitude()
{
    var message = null;
    //var pattern = /^(\+|-)?(?:90(?:(?:\.0{1,6})?)|(?:[0-9]|[1-8][0-9])(?:(?:\.[0-9]{1,6})?))$/;
    var pattern = /^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?)$/;
	//var value = document.getElementById("inputLatitudeDevice").value;
	
    if($("#addGeoPositionTabDevice #inputLatitudeDevice").val() === '')
    {
        message = 'Latitude is mandatory';
        addDeviceConditionsArray['inputLatitudeDevice'] = false;
        $("#inputLatitudeDeviceMsg").css("color", "red");
    }
    else if(!pattern.test($("#addGeoPositionTabDevice #inputLatitudeDevice").val()))
    {
        message = 'Latitude format is not correct ';
        addDeviceConditionsArray['inputLatitudeDevice'] = false;
        $("#inputLatitudeDeviceMsg").css("color", "red");
    }
    else if(pattern.test($("#addGeoPositionTabDevice #inputLatitudeDevice").val()))
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
	//var value = document.getElementById("inputLongitudeDevice").value;
	if($("#addGeoPositionTabDevice #inputLongitudeDevice").val() === '')
    {
        message = 'Longitude is mandatory';
        addDeviceConditionsArray['inputLongitudeDevice'] = false;
        $("#inputLongitudeDeviceMsg").css("color", "red");
    }
    else if(!pattern.test($("#addGeoPositionTabDevice #inputLongitudeDevice").val()))
    {
        message = 'Latitude format is not correct ';
        addDeviceConditionsArray['inputLongitudeDevice'] = false;
        $("#inputLongitudeDeviceMsg").css("color", "red");
    }
    else if(pattern.test($("#addGeoPositionTabDevice #inputLongitudeDevice").val()))
    {
        message = 'Ok';
        addDeviceConditionsArray['inputLongitudeDevice'] = true;
        $("#inputLongitudeDeviceMsg").css("color", "#337ab7");
    }
    
    $("#inputLongitudeDeviceMsg").html(message);
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
	
	   console.log(addDeviceConditionsArray);

	var n = $('#addSchemaTabDevice #addlistAttributes .row input:even').filter(function(){return this.value.length>=5}).length;
	var n1 =n;// $('#addSchemaTabDevice #addlistAttributes .row input:even').length;
    //troubles are in this function, removed, to be investigated further
	console.log("valore di n=" + n + " valore di n1" + n1);
	if (n==n1)
	{
		addDeviceConditionsArray['attributeWithName'] = true; 
    }	
	else
	{
		addDeviceConditionsArray['attributeWithName'] = false;
    }	
	
	
    for(var key in addDeviceConditionsArray) 
    {
        if(addDeviceConditionsArray[key] === false)
        {
            enableButton = false;
			
			console.log("need" + key);
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
        $("#addNewDeviceConfirmBtn").attr("disabled", false);
		//$("#addMyNewDeviceConfirmBtn").attr("disabled", false); this will be in action for mydevice
    }
    else
    {
        $("#addNewDeviceConfirmBtn").attr("disabled", true);
		//$("#addMyNewDeviceConfirmBtn").attr("disabled", false); this will be in action for the mydevice
    }
}

