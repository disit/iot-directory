

var devicenamesArray = new Array();
var editRuleConditionsArray = new Array();

function showEditDeviceModal()
{
	editRuleConditionsArray['format'] = false;
	editRuleConditionsArray['contextbroker'] = false;
	editRuleConditionsArray['kind'] = false;
	editRuleConditionsArray['selector'] = false;
	editRuleConditionsArray['nameRule'] = false;

	
	$("#editRuleConfirmBtn").attr("disabled", true);
	
	$("#editInfoTabDevice #inputNameRuleM").on('input', checkRuleNameM); 
	$("#editInfoTabDevice #inputNameRuleM").on('input', checkEditDeviceConditions);

	$("#editInfoTabDevice #selectKindDeviceM").on('input', checkSelectionKindM);
	$("#editInfoTabDevice #selectKindDeviceM").on('input', checkEditDeviceConditions);
		
	$("#editInfoTabDevice #selectContextBrokerM").on('input', checkSelectionCBM);
	$("#editInfoTabDevice #selectContextBrokerM").on('input', checkEditDeviceConditions);

    $("#editInfoTabDevice #inputFormatM").on('input', checkSelectionFormatM);
	$("#editInfoTabDevice #inputFormatM").on('input', checkEditDeviceConditions);
	
    $("#editInfoTabDevice #inputSelectorM").on('input', checkSelectorM);
	$("#editInfoTabDevice #inputSelectorM").on('input', checkEditDeviceConditions);

	
	checkRuleNameM();
	checkSelectionKindM();
	checkSelectionCBM(); 
	checkSelectionFormatM();
	checkSelectorM();
  
	$("#editDeviceModal").modal('show');
	
}


function checkSelectionFormatM()
{
	  var message = null;
    
    if($("#editInfoTabDevice #inputFormatM").val().length === 0)
    {
        $("#inputFormatMsg").css("color", "red");
        message = 'Rule format is mandatory';
        editRuleConditionsArray['format'] = false;
    }
    else 
    {
		$("#inputFormatMMsg").css("color", "#337ab7");
		message = 'Ok';
		editRuleConditionsArray['format'] = true;
    }
    
    $("#inputFormatMMsg").html(message);
	
}

function checkSelectionKindM()
{
    var message = null;
        //if($("#editInfoTabDevice #selectContextBrokerM").val().localeCompare("property") != 0 || $("#editInfoTabDevice #selectContextBrokerM").val().localeCompare("value") != 0)

	if($("#editInfoTabDevice #selectKindDeviceM").val().localeCompare("property")==0){
		 $("#dataTypeSelM").hide();
		 $("#valueTypeSelM").hide();
		 $("#valueUnitSelM").hide();
	}
	else if($("#editInfoTabDevice #selectKindDeviceM").val().localeCompare("value")==0){
		 $("#dataTypeSelM").show();
		 $("#valueTypeSelM").show();
		 $("#valueUnitSelM").show();		
	}

	editRuleConditionsArray['kind'] = true;
    
    
    $("#selectKindDeviceMMsg").html(message);
}



function checkSelectionCBM()
{
    var message = null;
    
    if($("#editInfoTabDevice #selectContextBrokerM").val().length === 0)
	{
        $("#selectContextBrokerMMsg").css("color", "red");
        message = 'Context broker is mandatory';
        editRuleConditionsArray['contextbroker'] = false;
    }
    else 
    {
		$("#selectContextBrokerMMsg").css("color", "#337ab7");
		message = 'Ok';
		editRuleConditionsArray['contextbroker'] = true;
    }
    
    $("#selectContextBrokerMMsg").html(message);
}
function checkSelectorM()
{
	//{"param":{"s": "$.address", "i":0},"type":"JSON"}
    var message = null;
    
    if($("#editInfoTabDevice #inputSelectorM").val().length === 0)
    {
        $("#inputSelectorMMsg").css("color", "red");
        message = 'Selector is mandatory';
        editRuleConditionsArray['selector'] = false;
    }
	else if($("#editInfoTabDevice #inputSelectorM").val().charAt(0) != '{')
    {
        $("#inputSelectorMMsg").css("color", "red");
        message = 'A selector should start with { character';
        editRuleConditionsArray['selector'] = false;
    }
	else if(!($("#editInfoTabDevice #inputSelectorM").val().startsWith("{\"param\":{\"s\":")))
    {
        $("#inputSelectorMMsg").css("color", "red");
        message = 'Should be in the format: {"param":{"s": PATH, "i": 0},"type":TYPE}';
        editRuleConditionsArray['selector'] = false;
    }
    else
    {
	
		$("#inputSelectorMMsg").css("color", "#337ab7");
		message = 'Ok';
		editRuleConditionsArray['selector'] = true;
	
    }
    
    $("#inputSelectorMMsg").html(message);
}
function checkRuleNameM()
{
    var message = null;
    
    if($("#editInfoTabDevice #inputNameRuleM").val().length === 0)
    {
        $("#inputNameRuleMMsg").css("color", "red");
        message = 'Name is mandatory';
        editRuleConditionsArray['nameRule'] = false;
    }
    else if($("#editInfoTabDevice #inputNameRuleM").val().length < 3)
    {
        $("#inputNameRuleMMsg").css("color", "red");
        message = 'Name should have at least 2 characters';
        editRuleConditionsArray['nameRule'] = false;
    }
    else
    {
		
		$("#inputNameRuleMMsg").css("color", "#337ab7");
		message = 'Ok';
		editRuleConditionsArray['nameRule'] = true;
	
    }
    
    $("#inputNameRuleMMsg").html(message);
}

	
function checkEditDeviceConditions()
{
    var enableButton = true;
	
	 // console.log(editRuleConditionsArray);
    
    for(var key in editRuleConditionsArray) 
    {
        console.log(key);
        if(editRuleConditionsArray[key] === false)
        {
            enableButton = false;
            break;
        }
    }
    
    if(enableButton)
    {
        $("#editRuleConfirmBtn").attr("disabled", false);
    }
    else
    {
        $("#editRuleConfirmBtn").attr("disabled", true);
    }
}
