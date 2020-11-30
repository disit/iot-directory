
var modelnamesArray;
var editModelConditionsArray = new Array();

function showEditModelModal()
{
    modelnamesArray = new Array();
    editModelConditionsArray['inputNameModelM'] = false;
//    editModelConditionsArray['inputDescriptionModelM'] = false;
    editModelConditionsArray['inputTypeModelM'] = false;
///    editModelConditionsArray['inputProducerModelM'] = false;

    $("#editModelConfirmBtn").attr("disabled", false);       
	
	$("#editInfoTabModel #inputNameModelM").on('input', checkModelNameM); 
	$("#editInfoTabModel #inputNameModelM").on('input', checkEditModelConditions);
	
//	$("#editInfoTabModel #inputDescriptionModelM").on('input', checkModelDescriptionM);
//	$("#editInfoTabModel #inputDescriptionModelM").on('input', checkEditModelConditions);
	
	$("#editInfoTabModel #inputTypeModelM").on('input', checkModelDeviceTypeM);
	$("#editInfoTabModel #inputTypeModelM").on('input', checkEditModelConditions);
	
//	$("#editInfoTabModel #inputProducerModelM").on('input', checkModelProducerM);
//	$("#editInfoTabModel #inputProducerModelM").on('input', checkEditModelConditions);

	//field without constrains
	$("#editInfoTabModel #selectKindModelM").on('input', checkEditModelConditions);
        $("#editInfoTabModel #inputFrequencyModelM").on('input', checkEditModelConditions);
        $("#editInfoTabModel #selectHCModelM").on('input', checkEditModelConditions);
        $("#editInfoTabModel #inputHVModelM").on('input', checkEditModelConditions);
        $("#editInfoTabModel #selectKGeneratorModelM").on('input', checkEditModelConditions);
        $("#editInfoTabModel #selectEdgeGatewayTypeM").on('input', checkEditModelConditions);
	
	checkModelNameM();
//n	checkModelDescriptionM();
	checkModelDeviceTypeM();
//	checkModelProducerM();
	
	$("#editModelModal").modal('show');      
}

function checkModelSelectionCBM_all(){
        checkSelectionCBM();
        checkSelectionProtocolM();
        checkSelectionFormatM();
}

function checkSelectionCBM()
{
    var message = null;

    if ( !$("#editIOTBrokerTabModel #selectContextBrokerM").val() ||  $("#editIOTBrokerTabModel #selectContextBrokerM").val().length === 0)
    {
        $("#selectContextBrokerMMsg").css("color", "red");
        message = 'Context broker is mandatory';
        editModelConditionsArray['contextbroker'] = false;
    }
    else
    {
       $("#selectContextBrokerMMsg").css("color", "#337ab7");
       message = 'Ok';
       editModelConditionsArray['contextbroker'] = true;
    }

    $("#selectContextBrokerMMsg").html(message);
}

function checkSelectionProtocolM()
{
    var message = null;

        console.log("aa:"+$("#editIOTBrokerTabModel #selectProtocolModelM").val());

    if ( !$("#editIOTBrokerTabModel #selectProtocolModelM").val() || $("#editIOTBrokerTabModel #selectProtocolModelM").val().length === 0 )
    {
        $("#selectProtocolModelMMsg").css("color", "red");
        message = 'Device protocol is mandatory';
        editModelConditionsArray['protocol'] = false;
    }
    else
    {
                $("#selectProtocolModelMMsg").css("color", "#337ab7");
                message = 'Ok';
                editModelConditionsArray['protocol'] = true;
    }

    $("#selectProtocolModelMMsg").html(message);
}

function checkSelectionFormatM()
{
    var message = null;

        console.log("bb:"+$("#addIOTBrokerTabModel #selectFormatModel").val());

    if ( !$("#editIOTBrokerTabModel #selectFormatModelM").val() || $("#editIOTBrokerTabModel #selectFormatModelM").val().length === 0)
    {
        $("#selectFormatModelMMsg").css("color", "red");
        message = 'Device format is mandatory';
        editModelConditionsArray['format'] = false;
    }
    else
    {
                $("#selectFormatModelMMsg").css("color", "#337ab7");
                message = 'Ok';
                editModelConditionsArray['format'] = true;
    }

    $("#selectFormatModelMMsg").html(message);
}


function checkModelNameM()
{
    var message = null;
    var regex=/[^a-z0-9_-\s]+$/gi;
    
    if ( !$("#editInfoTabModel #inputNameModelM").val() || $("#editInfoTabModel #inputNameModelM").val().length === 0)
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


function checkModelValueNameM(current)
{
    var message = null;
    var regex=/[^a-z0-9_-]/gi;
    value=current.val();
    element=current.parent().siblings().last();
    
//console.log("valore identificato " + $(this).val());
//	console.log("elemento identificato " + $(this).parent().siblings().last().html());
	
    if( !value || value.length === 0)
    {
        element.css("color", "red");
        message = 'Value name is mandatory';
        //editDeviceConditionsArray['inputNameValueM'] = false;
    }
    else if(value.length < 2)
    {
        element.css("color", "red");
        message = 'Value name (at least 3 chars long)';
        //editDeviceConditionsArray['inputNameValueM'] = false;
    }
    else if(regex.test(value))
    {
        element.css("color", "red");
        message = 'No special characters are allowed in Value name';
        //editDeviceConditionsArray['inputNameValueM'] = false;
    }
    else
    {
	
		element.css("color", "#337ab7");
		message = 'Ok';
		//editDeviceConditionsArray['inputNameValueM'] = true;
	
    }
    
    element.html(message);
}

function checkEditAtlistOneAttributeM()
{
    var message = null;

    if (( !$("#editlistAttributes").html() ||  $("#editlistAttributes").html().length === 0)
	&&
	( !$("#addlistAttributesM").html() ||  $("#addlistAttributesM").html().length === 0))
    {
        $("#editlistAttributesMsg").css("color", "red");
        message = 'At least a value needs to be specified';
        editModelConditionsArray['oneAttribute'] = false;
    }
    else
    {
                $("#editlistAttributesMsg").css("color", "#337ab7");
                message = '';
                editModelConditionsArray['oneAttribute'] = true;
    }
    $("#editlistAttributesMsg").html(message);
}

/*
function checkModelDescriptionM()
{
    var message = null;
    
    if( !$("#editInfoTabModel #inputDescriptionModelM").val() || $("#editInfoTabModel #inputDescriptionModelM").val().length === 0)
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
}*/

function checkModelDeviceTypeM()
{
    var message = null;
    
    if ( !$("#editInfoTabModel #inputTypeModelM").val() || $("#editInfoTabModel #inputTypeModelM").val().length === 0)
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
/*
function checkModelProducerM()
{
    var message = null;
    
    if( !$("#editInfoTabModel #inputProducerModelM").val() || $("#editInfoTabModel #inputProducerModelM").val().length === 0)
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
*/


function checkEditModelConditions()
{
        //check that any value has a correct name/syntax
        var n = $('#editSchemaTabModel #editlistAttributes .row input:even').filter(function(){return this.value.length>=2}).length;
        var nx= $('#editSchemaTabModel #addlistAttributesM .row input:even').filter(function(){return this.value.length>=2}).length;
        var n1 =$('#editSchemaTabModel #editlistAttributes .row input:even').length;
        var n1x =$('#editSchemaTabModel #addlistAttributesM .row input:even').length;

        //console.log("n: "+n+" n1:"+n1+" nx:"+nx+" n1x:"+n1x);
        if ((n+nx)==(n1+n1x))
        {
                editModelConditionsArray['attributeWithName'] = true;
        }
        else
        {
                editModelConditionsArray['attributeWithName'] = false;
        }

        //check that any value has a correct name/syntax. this enforce is done here since the list of values is dynamic
        var regex=/[^a-z0-9:._-]/gi;
        var o = $('#editSchemaTabModel #editlistAttributes .row input:even').filter(function(){return !regex.test(this.value)}).length;
        var ox = $('#editSchemaTabModel #addlistAttributesM .row input:even').filter(function(){return !regex.test(this.value)}).length;

        //console.log("o: "+o+" n1:"+n1+" ox:"+ox);
        if ((o+ox)==(n1+n1x))
        {
                editModelConditionsArray['specialChars'] = true;
        }
        else
        {
                editModelConditionsArray['specialChars'] = false;
        }

        //check that any value has a value type selected
        var p = $('#editSchemaTabModel #editlistAttributes select[id*="value_type"]').filter(function(){return this.value!=="NOT VALID OPTION"}).length;
        var px = $('#editSchemaTabModel #addlistAttributesM select[id*="value_type"]').filter(function(){return this.value!=="NOT VALID OPTION"}).length;

        //console.log("p: "+p+" n1:"+n1+" px:"+px);
        if ((p+px)==(n1+n1x))
        {
                editModelConditionsArray['attributeWithValueType'] = true;
        }
        else
        {
                editModelConditionsArray['attributeWithValueType'] = false;
        }

        //check that any value has a value unit selected
        var c = $('#editSchemaTabModel #editlistAttributes select[id*="value_unit"]').filter(function(){return this.value!=="NOT VALID OPTION"}).length;
        var cx = $('#editSchemaTabModel #addlistAttributesM select[id*="value_unit"]').filter(function(){return this.value!=="NOT VALID OPTION"}).length;

        //console.log("c: "+c+" n1:"+n1+" cx:"+cx);
        if ((c+cx)==(n1+n1x))
        {
                editModelConditionsArray['attributeWithValueUnit'] = true;
        }
        else
        {
                editModelConditionsArray['attributeWithValueUnit'] = false;
        }


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
