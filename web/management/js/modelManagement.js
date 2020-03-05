
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
	
	checkModelSelectionCB_all();
	checkAtlistOneAttribute();
	
	 $("#addModelModal").modal('show');    
     
}

function checkModelSelectionCB_all(){
        checkSelectionCB();
        checkSelectionProtocol();
        checkSelectionFormat();
}

function checkSelectionCB()
{
    var message = null;

    if ( !$("#addIOTBrokerTabModel #selectContextBroker").val() ||  $("#addIOTBrokerTabModel #selectContextBroker").val().length === 0)
    {
        $("#selectContextBrokerMsg").css("color", "red");
        message = 'Context broker is mandatory';
        addModelConditionsArray['contextbroker'] = false;
    }
    else
    {
       $("#selectContextBrokerMsg").css("color", "#337ab7");
       message = 'Ok';
       addModelConditionsArray['contextbroker'] = true;
    }

    $("#selectContextBrokerMsg").html(message);
}

function checkSelectionProtocol()
{
    var message = null;

	console.log("aa:"+$("#addIOTBrokerTabModel #selectProtocolModel").val());

    if ( !$("#addIOTBrokerTabModel #selectProtocolModel").val() || $("#addIOTBrokerTabModel #selectProtocolModel").val().length === 0 )
    {
        $("#selectProtocolModelMsg").css("color", "red");
        message = 'Device protocol is mandatory';
        addModelConditionsArray['protocol'] = false;
    }
    else
    {
                $("#selectProtocolModelMsg").css("color", "#337ab7");
                message = 'Ok';
                addModelConditionsArray['protocol'] = true;
    }

    $("#selectProtocolModelMsg").html(message);
}

function checkSelectionFormat()
{
    var message = null;

	console.log("bb:"+$("#addIOTBrokerTabModel #selectFormatModel").val());

    if ( !$("#addIOTBrokerTabModel #selectFormatModel").val() || $("#addIOTBrokerTabModel #selectFormatModel").val().length === 0)
    {
        $("#selectFormatModelMsg").css("color", "red");
        message = 'Device format is mandatory';
        addModelConditionsArray['format'] = false;
    }
    else
    {
                $("#selectFormatModelMsg").css("color", "#337ab7");
                message = 'Ok';
                addModelConditionsArray['format'] = true;
    }

    $("#selectFormatModelMsg").html(message);
}


function checkModelName()
{
    var message = null;
    var regex=/[^a-z0-9_-\s]+$/gi;
    
    if( !$("#addInfoTabModel #inputNameModel").val() || $("#addInfoTabModel #inputNameModel").val().length === 0)
    {
        $("#inputNameModelMsg").css("color", "red");
        message = 'Model name is mandatory';
        addModelConditionsArray['inputNameModel'] = false;
    }
    else if($("#addInfoTabModel #inputNameModel").val().length < 2)
    {
        $("#inputNameModelMsg").css("color", "red");
        message = 'Model name (at least 5 chars long)';
        addModelConditionsArray['inputNameModel'] = false;
    } 
    
    else if(regex.test($("#addInfoTabModel #inputNameModel").val()))
    {
        $("#inputNameModelMsg").css("color", "red");
        message = 'No special characters are allowed in a model name';
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

function checkModelValueName(current)
{
    var message = null;
    var regex=/[^a-z0-9_-]/gi;
    value=current.val();
    element=current.parent().siblings().last();

    //console.log("valore identificato " + $(this).val());
    //console.log("elemento identificato " + $(this).parent().siblings().last().html());
	
    if ( !value || value.length === 0)
    {
        element.css("color", "red");
        message = 'Value name is mandatory';
        //addDeviceConditionsArray['inputNameValue'] = false;
    }
    else if(value.length < 2)
    {
        element.css("color", "red");
        message = 'Value name (at least 2 chars long)';
        //addDeviceConditionsArray['inputNameValue'] = false;
    }
    else if(regex.test(value))
    {
        element.css("color", "red");
        message = 'No special characters are allowed in Value name';
        //addDeviceConditionsArray['inputNameValue'] = false;
    }
    else
    {
		element.css("color", "#337ab7");
		message = 'Ok';
		//addDeviceConditionsArray['inputNameValue'] = true;
	
    }
    
    element.html(message);
}

function checkModelDescription()
{
    var message = null;
    
    if( !$("#addInfoTabModel #inputDescriptionModel").val() || $("#addInfoTabModel #inputDescriptionModel").val().length === 0)
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
    
    if( !$("#addInfoTabModel #inputTypeModel").val() || $("#addInfoTabModel #inputTypeModel").val().length === 0)
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
    
    if( !$("#addInfoTabModel #inputProducerModel").val() || $("#addInfoTabModel #inputProducerModel").val().length === 0)
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

        //check that any value has a correct name/syntax
        var n = $('#addSchemaTabModel #addlistAttributes .row input:even').filter(function(){return this.value.length>=2}).length;
        var n1 =$('#addSchemaTabModel #addlistAttributes .row input:even').length;

        //console.log("n: "+n+" n1:"+n1);
        if (n==n1)
        {
                addModelConditionsArray['attributeWithName'] = true;
        }
        else
        {
                addModelConditionsArray['attributeWithName'] = false;
        }

        //check that any value has a correct name/syntax. this enforce is done here since the list of values is dynamic
        var regex=/[^a-z0-9:._-]/gi;
        var o = $('#addSchemaTabModel #addlistAttributes .row input:even').filter(function(){return !regex.test(this.value)}).length;

        //console.log("o: "+o+" n1:"+n1);
        if (o==n1)
        {
                addModelConditionsArray['specialChars'] = true;
        }
        else
        {
                addModelConditionsArray['specialChars'] = false;
        }
        //check that any value has a value type selected
        var p = $('#addSchemaTabModel #addlistAttributes select[id*="value_type"]').filter(function(){return this.value!=="NOT VALID OPTION"}).length;

        //console.log("p: "+p+" n1:"+n1);
        if (p==n1)
        {
                addModelConditionsArray['attributeWithValueType'] = true;
        }
        else
        {
                addModelConditionsArray['attributeWithValueType'] = false;
        }

        //check that any value has a value unit selected
        var c = $('#addSchemaTabModel #addlistAttributes select[id*="value_unit"]').filter(function(){return this.value!=="NOT VALID OPTION"}).length;

        //console.log("c: "+c+" n1:"+n1);
        if (c==n1)
        {
                addModelConditionsArray['attributeWithValueUnit'] = true;
        }
        else
        {
                addModelConditionsArray['attributeWithValueUnit'] = false;
        }


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

function checkAtlistOneAttribute()
{
    var message = null;

    if ( !$("#addlistAttributes").html() || $("#addlistAttributes").html().length === 0)
    {
        $("#addlistAttributesMsg").css("color", "red");
        message = 'At least a value needs to be specified';
        addModelConditionsArray['oneAttribute'] = false;
    }
    else
    {
                $("#addlistAttributesMsg").css("color", "#337ab7");
                message = '';
                addModelConditionsArray['oneAttribute'] = true;
    }
    $("#addlistAttributesMsg").html(message);
}
