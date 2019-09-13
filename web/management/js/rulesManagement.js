
var  addDeviceConditionsArray = new Array();
    
addDeviceConditionsArray['format'] = false;
addDeviceConditionsArray['contextbroker'] = false;
addDeviceConditionsArray['kind'] = false;
addDeviceConditionsArray['selector'] = false;
addDeviceConditionsArray['nameRule'] = false;

	
function showAddDeviceModal()
{
	var  addDeviceConditionsArray = new Array();
	addDeviceConditionsArray['format'] = false;
	addDeviceConditionsArray['contextbroker'] = false;
	addDeviceConditionsArray['kind'] = false;
	addDeviceConditionsArray['selector'] = false;
	addDeviceConditionsArray['nameRule'] = false;

	
	$("#addNewRuleConfirmBtn").attr("disabled", true);
	
	$("#addInfoTabDevice #inputNameRule").on('input', checkRuleName); 
	$("#addInfoTabDevice #inputNameRule").on('input', checkAddDeviceConditions);

	$("#addInfoTabDevice #selectKindDevice").on('input', checkSelectionKind);
	$("#addInfoTabDevice #selectKindDevice").on('input', checkAddDeviceConditions);
		
	$("#addInfoTabDevice #selectContextBroker").on('input', checkSelectionCB);
	$("#addInfoTabDevice #selectContextBroker").on('input', checkAddDeviceConditions);

    $("#addInfoTabDevice #inputFormat").on('input', checkSelectionFormat);
	$("#addInfoTabDevice #inputFormat").on('input', checkAddDeviceConditions);
	
    $("#addInfoTabDevice #inputSelector").on('input', checkSelector);
	$("#addInfoTabDevice #inputSelector").on('input', checkAddDeviceConditions);

		
	//marco: last inserted controls

	$("#addDeviceModal").modal('show');

    checkRuleName();
	checkSelectionCB();
	checkSelectionFormat();
    //checkDeviceType();
	checkSelectionKind();
	checkSelector();

}
function showAddMyDeviceModal()
{
    $("#addNewRuleConfirmBtn").attr("disabled", true);
	
	$("#inputNameRuleUser").on('keyup change', function(){checkRuleName(); checkAddMyDeviceConditions();});
	$("#inputFormatUser").on('keyup change', function(){checkSelectionFormat(); checkAddMyDeviceConditions(); });
	$("#inputKindUser").on('keyup change', function(){checkSelectionKind(); checkAddMyDeviceConditions(); });
	$("#inputContextBrokerUser").on('keyup change', function(){checkSelectionCB(); checkAddMyDeviceConditions(); });
	$("#inputSelector").on('keyup change', function(){checkSelector(); checkAddMyDeviceConditions(); });
    
    console.log("here");

}
function checkSelectionFormat()
{
    var message = null;
    
    if($("#addInfoTabDevice #inputFormat").val().length === 0)
    {
        $("#inputFormatMsg").css("color", "red");
        message = 'Rule format is mandatory';
        addDeviceConditionsArray['format'] = false;
    }
    else 
    {
		$("#inputFormatMsg").css("color", "#337ab7");
		message = 'Ok';
		addDeviceConditionsArray['format'] = true;
    }
    
    $("#inputFormatMMsg").html(message);
}

	

function checkSelectionKind()
{
    var message = null;
        //if($("#addInfoTabDevice #selectContextBroker").val().localeCompare("property") != 0 || $("#addInfoTabDevice #selectContextBroker").val().localeCompare("value") != 0)

	if($("#addInfoTabDevice #selectKindDevice").val().localeCompare("property")==0){
		 $("#dataTypeSel").hide();
		 $("#valueTypeSel").hide();
		 $("#valueUnitSel").hide();
	}
	else if($("#addInfoTabDevice #selectKindDevice").val().localeCompare("value")==0){
		 $("#dataTypeSel").show();
		 $("#valueTypeSel").show();
		 $("#valueUnitSel").show();		
	}
   
		addDeviceConditionsArray['kind'] = true;
   
   // $("#selectRuleKindMsg").html(message);
}



function checkSelectionCB()
{
    var message = null;
    
    if($("#addInfoTabDevice #selectContextBroker").val().length === 0)
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
function checkSelector()
{
	//{"param":{"s": "$.address", "i":0},"type":"JSON"}
    var message = null;
    
    if($("#addInfoTabDevice #inputSelector").val().length === 0)
    {
        $("#inputSelectorMsg").css("color", "red");
        message = 'Selector is mandatory';
        addDeviceConditionsArray['selector'] = false;
    }
	else if($("#addInfoTabDevice #inputSelector").val().charAt(0) != '{')
    {
        $("#inputSelectorMsg").css("color", "red");
        message = 'A selector should start with { character';
        addDeviceConditionsArray['selector'] = false;
    }
	else if(!($("#addInfoTabDevice #inputSelector").val().startsWith("{\"param\":{\"s\":")))
    {
        $("#inputSelectorMsg").css("color", "red");
        message = 'Should be in the format: {"param":{"s": PATH, "i": 0},"type":TYPE}';
        addDeviceConditionsArray['selector'] = false;
    }
    else
    {
	
		$("#inputSelectorMsg").css("color", "#337ab7");
		message = 'Ok';
		addDeviceConditionsArray['selector'] = true;
	
    }
    
    $("#inputSelectorMsg").html(message);
}
function checkRuleName()
{
    var message = null;
    
    if($("#addInfoTabDevice #inputNameRule").val().length === 0)
    {
        $("#inputNameRuleMsg").css("color", "red");
        message = 'Name is mandatory';
        addDeviceConditionsArray['nameRule'] = false;
    }
    else if($("#addInfoTabDevice #inputNameRule").val().length < 3)
    {
        $("#inputNameRuleMsg").css("color", "red");
        message = 'Name should have at least 2 characters';
        addDeviceConditionsArray['nameRule'] = false;
    }
    else
    {
		
		$("#inputNameRuleMsg").css("color", "#337ab7");
		message = 'Ok';
		addDeviceConditionsArray['nameRule'] = true;
	
    }
    
    $("#inputNameRuleMsg").html(message);
}

function checkAddDeviceConditions()
{
    var enableButton = true;
	
	 console.log(addDeviceConditionsArray);
	
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
        $("#addNewRuleConfirmBtn").attr("disabled", false);
    }
    else
    {
        $("#addNewRuleConfirmBtn").attr("disabled", true);
    }
}


