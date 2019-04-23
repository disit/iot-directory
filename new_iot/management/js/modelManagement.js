
var modelnamesArray;
var addModelConditionsArray = new Array();

function showAddModelModal()
{
    modelnamesArray = new Array();
    addModelConditionsArray['inputNameModel'] = false;
    addModelConditionsArray['inputDescriptionModel'] = false;
    addModelConditionsArray['inputTypeModel'] = false;
    addModelConditionsArray['inputProducerModel'] = false;
  
  

     $("#addNewModelConfirmBtn").attr("disabled", true);    

	
	$("#addInfoTabModel #inputNameModel").on('input', checkModelName); 
	$("#addInfoTabModel #inputNameModel").on('input', checkAddModelConditions);
	
	$("#addInfoTabModel #inputDescriptionModel").on('input', checkModelDescription);
	$("#addInfoTabModel #inputDescriptionModel").on('input', checkAddModelConditions);
	
	$("#addInfoTabModel #inputTypeModel").on('input', checkModelDeviceType);
	$("#addInfoTabModel #inputTypeModel").on('input', checkAddModelConditions);
	
	
	$("#addInfoTabModel #inputProducerModel").on('input', checkModelProducer);
	$("#addInfoTabModel #inputProducerModel").on('input', checkAddModelConditions);
	
	checkModelName();
	checkModelDescription();
	checkModelDeviceType();
	checkModelProducer();
	
	 $("#addModelModal").modal('show');    
     
}


function checkModelName()
{
    var message = null;
    
    if($("#addInfoTabModel #inputNameModel").val().length === 0)
    {
        $("#inputNameModelMsg").css("color", "red");
        message = 'Model name is mandatory';
        addModelConditionsArray['inputNameModel'] = false;
    }
    else if($("#addInfoTabModel #inputNameModel").val().length < 5)
    {
        $("#inputNameModelMsg").css("color", "red");
        message = 'Model name (at least 5 chars long)';
        addModelConditionsArray['inputNameModel'] = false;
    }
    else
    {
		
		$("#inputNameModelMsg").css("color", "#337ab7");
		message = 'Ok';
		addModelConditionsArray['inputNameModel'] = true;
        
    }
    
    $("#inputNameModelMsg").html(message);
}


function checkModelDescription()
{
    var message = null;
    
    if($("#addInfoTabModel #inputDescriptionModel").val().length === 0)
    {
        $("#inputDescriptionModelMsg").css("color", "red");
        message = 'Model Description is mandatory';
        addModelConditionsArray['inputDescriptionModel'] = false;
    }
    else if($("#addInfoTabModel #inputDescriptionModel").val().length < 5)
    {
        $("#inputDescriptionModelMsg").css("color", "red");
        message = 'Model description (at least 5 chars long)';
        addModelConditionsArray['inputDescriptionModel'] = false;
    }
    else
    {
		
		$("#inputDescriptionModelMsg").css("color", "#337ab7");
		message = 'Ok';
		addModelConditionsArray['inputDescriptionModel'] = true;
        
    }
    
    $("#inputDescriptionModelMsg").html(message);
}

function checkModelDeviceType()
{
    var message = null;
    
    if($("#addInfoTabModel #inputTypeModel").val().length === 0)
    {
        $("#inputTypeModelMsg").css("color", "red");
        message = 'Device Type is mandatory';
        addModelConditionsArray['inputTypeModel'] = false;
    }
    else
    {
       if ($("#addInfoTabModel #inputTypeModel").val().indexOf(' ')>-1)
                {
                        message = 'Model Type cannot contains blank space';
                        addModelConditionsArray['inputTypeModel'] = false;
                        $("#inputTypeModelMsg").css("color", "red");
                }
                else
                {
                        message = 'Ok';
                        addModelConditionsArray['inputTypeModel'] = true;
                        $("#inputTypeModelMsg").css("color", "#337ab7");
                }		
        
    }
    
    $("#inputTypeModelMsg").html(message);
}

function checkModelProducer()
{
    var message = null;
    
    if($("#addInfoTabModel #inputProducerModel").val().length === 0)
    {
        $("#inputProducerModelMsg").css("color", "red");
        message = 'Producer is mandatory';
        addModelConditionsArray['inputProducerModel'] = false;
    }
    else if($("#addInfoTabModel #inputProducerModel").val().length < 5)
    {
        $("#inputProducerModelMsg").css("color", "red");
        message = 'Producer (at least 5 chars long)';
        addModelConditionsArray['inputProducerModel'] = false;
    }
    else
    {
		
		$("#inputProducerModelMsg").css("color", "#337ab7");
		message = 'Ok';
		addModelConditionsArray['inputProducerModel'] = true;
        
    }
    
    $("#inputProducerModelMsg").html(message);
}



function checkAddModelConditions()
{
    var enableButton = true;
    // console.log(addModelConditionsArray);
    for(var key in addModelConditionsArray) 
    {
        if(addModelConditionsArray[key] === false)
        {
            enableButton = false;
            break;
        }
    }
    console.log("value enabled" +  enableButton);
    if(enableButton)
    {
        $("#addNewModelConfirmBtn").attr("disabled", false);   
    }
    else
    {
        $("#addNewModelConfirmBtn").attr("disabled", true);   
    }
}
