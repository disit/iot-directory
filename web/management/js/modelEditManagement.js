
var modelnamesArray;
var editModelConditionsArray = new Array();

function showEditModelModal()
{
    modelnamesArray = new Array();
    editModelConditionsArray['inputNameModelM'] = false;
    editModelConditionsArray['inputDescriptionModelM'] = false;
    editModelConditionsArray['inputTypeModelM'] = false;
    editModelConditionsArray['inputProducerModelM'] = false;

    $("#editModelConfirmBtn").attr("disabled", false);       
	
	$("#editInfoTabModel #inputNameModelM").on('input', checkModelNameM); 
	$("#editInfoTabModel #inputNameModelM").on('input', checkEditModelConditions);
	
	$("#editInfoTabModel #inputDescriptionModelM").on('input', checkModelDescriptionM);
	$("#editInfoTabModel #inputDescriptionModelM").on('input', checkEditModelConditions);
	
	$("#editInfoTabModel #inputTypeModelM").on('input', checkModelDeviceTypeM);
	$("#editInfoTabModel #inputTypeModelM").on('input', checkEditModelConditions);
	
	$("#editInfoTabModel #inputProducerModelM").on('input', checkModelProducerM);
	$("#editInfoTabModel #inputProducerModelM").on('input', checkEditModelConditions);

	//field without constrains
	$("#editInfoTabModel #selectKindModelM").on('input', checkEditModelConditions);
        $("#editInfoTabModel #inputFrequencyModelM").on('input', checkEditModelConditions);
        $("#editInfoTabModel #selectHCModelM").on('input', checkEditModelConditions);
        $("#editInfoTabModel #inputHVModelM").on('input', checkEditModelConditions);
        $("#editInfoTabModel #selectKGeneratorModelM").on('input', checkEditModelConditions);
        $("#editInfoTabModel #selectEdgeGatewayTypeM").on('input', checkEditModelConditions);
	
	checkModelNameM();
	checkModelDescriptionM();
	checkModelDeviceTypeM();
	checkModelProducerM();
	
	$("#editModelModal").modal('show');      
}


function checkModelNameM()
{
    var message = null;
    var regex=/[^a-z0-9_-\s]+$/gi;
    
    if($("#editInfoTabModel #inputNameModelM").val().length === 0)
    {
        $("#inputNameModelMMsg").css("color", "red");
        message = 'Model name is mandatory';
        editModelConditionsArray['inputNameModelM'] = false;
    }
    else if($("#editInfoTabModel #inputNameModelM").val().length < 5)
    {
        $("#inputNameModelMMsg").css("color", "red");
        message = 'Model name (at least 5 chars long)';
        editModelConditionsArray['inputNameModelM'] = false;
    } 
    else if(regex.test($("#editInfoTabModel #inputNameModelM").val()))
    {
        $("#inputNameModelMMsg").css("color", "red");
        message = 'No special characters are allowed in a model name';
        editModelConditionsArray['inputNameModelM'] = false;
    }
    else
    {
		
		$("#inputNameModelMMsg").css("color", "#337ab7");
		message = 'Ok';
		editModelConditionsArray['inputNameModelM'] = true;
        
    }
    
    $("#inputNameModelMMsg").html(message);
}


function checkModelValueNameM()
{
    var message = null;
    var regex=/[^a-z0-9_-]/gi;
    console.log("valore identificato " + $(this).val());
	console.log("elemento identificato " + $(this).parent().siblings().last().html());
	
    if($(this).val().length === 0)
    {
        $(this).parent().siblings().last().css("color", "red");
        message = 'Value name is mandatory';
        //editDeviceConditionsArray['inputNameValueM'] = false;
    }
    else if($(this).val().length < 3)
    {
        $(this).parent().siblings().last().css("color", "red");
        message = 'Value name (at least 3 chars long)';
        //editDeviceConditionsArray['inputNameValueM'] = false;
    }
    else if(regex.test($(this).val()))
    {
        $(this).parent().siblings().last().css("color", "red");
        message = 'No special characters are allowed in Value name';
        //editDeviceConditionsArray['inputNameValueM'] = false;
    }
    else
    {
	
		$(this).parent().siblings().last().css("color", "#337ab7");
		message = 'Ok';
		//editDeviceConditionsArray['inputNameValueM'] = true;
	
    }
    
    $(this).parent().siblings().last().html(message);
}
function checkModelDescriptionM()
{
    var message = null;
    
    if($("#editInfoTabModel #inputDescriptionModelM").val().length === 0)
    {
        $("#inputDescriptionModelMMsg").css("color", "red");
        message = 'Model Description is mandatory';
        editModelConditionsArray['inputDescriptionModelM'] = false;
    }
    else if($("#editInfoTabModel #inputDescriptionModelM").val().length < 5)
    {
        $("#inputDescriptionModelMMsg").css("color", "red");
        message = 'Model description (at least 5 chars long)';
        editModelConditionsArray['inputDescriptionModelM'] = false;
    }
    else
    {
		
		$("#inputDescriptionModelMMsg").css("color", "#337ab7");
		message = 'Ok';
		editModelConditionsArray['inputDescriptionModelM'] = true;
        
    }
    
    $("#inputDescriptionModelMMsg").html(message);
}

function checkModelDeviceTypeM()
{
    var message = null;
    
    if($("#editInfoTabModel #inputTypeModelM").val().length === 0)
    {
        $("#inputTypeModelMMsg").css("color", "red");
        message = 'Device Type is mandatory';
        editModelConditionsArray['inputTypeModelM'] = false;
    }
  /*  else if($("#editInfoTabModel #inputTypeModelM").val().length < 5)
    {
        $("#inputTypeModelMMsg").css("color", "red");
        message = 'Model Device Type (at least 5 chars long)';
        editModelConditionsArray['inputTypeModelM'] = false;
    } */
    else
    {
		
		$("#inputTypeModelMMsg").css("color", "#337ab7");
		message = 'Ok';
		editModelConditionsArray['inputTypeModelM'] = true;
        
    }
    
    $("#inputTypeModelMMsg").html(message);
}

function checkModelProducerM()
{
    var message = null;
    
    if($("#editInfoTabModel #inputProducerModelM").val().length === 0)
    {
        $("#inputProducerModelMMsg").css("color", "red");
        message = 'Producer is mandatory';
        editModelConditionsArray['inputProducerModelM'] = false;
    }
    else if($("#editInfoTabModel #inputProducerModelM").val().length < 5)
    {
        $("#inputProducerModelMMsg").css("color", "red");
        message = 'Producer (at least 5 chars long)';
        editModelConditionsArray['inputProducerModelM'] = false;
    }
    else
    {
		
		$("#inputProducerModelMMsg").css("color", "#337ab7");
		message = 'Ok';
		editModelConditionsArray['inputProducerModelM'] = true;
        
    }
    
    $("#inputProducerModelMMsg").html(message);
}



function checkEditModelConditions()
{
    var enableButton = true;
    // console.log(editModelConditionsArray);
    for(var key in editModelConditionsArray) 
    {
        if(editModelConditionsArray[key] === false)
        {
            enableButton = false;
            break;
        }
    }
    console.log("value enabled" +  enableButton);
    if(enableButton)
    {
        $("#editModelConfirmBtn").attr("disabled", false);   
    }
    else
    {
        $("#editModelConfirmBtn").attr("disabled", true);   
    }
}
