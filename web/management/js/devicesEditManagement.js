

var devicenamesArray = new Array();
var editDeviceConditionsArray = new Array();

function showEditDeviceModal()
{
   
       // editDeviceConditionsArray['inputNameDeviceM'] = false;
	editDeviceConditionsArray['inputTypeDeviceM'] = false;
    editDeviceConditionsArray['inputLatitudeDeviceM'] = false;
    editDeviceConditionsArray['inputLongitudeDeviceM'] = false;
	editDeviceConditionsArray['inputMacDeviceM'] = false;
    //editDeviceConditionsArray['KeyOneDeviceUserM'] = false;
	//editDeviceConditionsArray['KeyTwoDeviceUserM'] = false;
	
	
	//$("#editDeviceConfirmBtn").attr("disabled", false);
	
    $("#editInfoTabDevice #inputTypeDeviceM").on('input', function(){checkEditDeviceType();checkEditDeviceConditions();}); 
	
	
	$("#editGeoPositionTabDevice #inputLatitudeDeviceM").on('input', function(){checkEditDeviceLatitude(); checkEditDeviceConditions();}); 
	

	$("#editGeoPositionTabDevice #inputLongitudeDeviceM").on('input', function(){checkEditDeviceLongitude(); checkEditDeviceConditions();}); 
	
	
	$("#editInfoTabDevice #inputMacDeviceM").on('input', function(){checkEditDeviceMAC(); checkEditDeviceConditions();}); 
    
    //$("#editInfoTabDevice #KeyOneDeviceUserM").on('input', function(){UserEditKey(); checkEditDeviceConditions();}); 
    //$("#editInfoTabDevice #KeyOneDeviceUserM").change(function(){UserEditKey(); checkEditDeviceConditions();});
    
    //$("#editInfoTabDevice #KeyTwoDeviceUserM").on('input', function(){UserEditKey(); checkEditDeviceConditions();}); 
    //$("#editInfoTabDevice #KeyTwoDeviceUserM").change( function(){UserEditKey(); checkEditDeviceConditions();}); 
    
    //$("#editInfoTabDevice #editDeviceGenerateKeyBtn").on('click',function(){UserEditKey(); checkEditDeviceConditions();});
	
	
	checkEditDeviceName();
	checkEditDeviceType();
	checkEditDeviceLatitude(); 
	checkEditDeviceLongitude();
	checkEditDeviceMAC();
	//UserEditKey(); 
	checkEditDeviceConditions();
    

	   
	   $("#editDeviceModal").modal('show');
	
}

function theSameNameAgain(myAttributes, mynewAttributes){
    AllAtt=myAttributes.concat(mynewAttributes);
    NameAllAtt=[];
   for(var i=0; i< AllAtt.length;i++ ){
       if(!NameAllAtt.includes(AllAtt[i].value_name)){
         NameAllAtt[i]=AllAtt[i].value_name;
     }else{
         NameAllAtt[i]='';
     }
     
   }
   if(NameAllAtt.includes(''))
       return false;

    return true;
}
function checkEditDeviceName()
{
    var message = null;
    
    if ( !$("#editInfoTabDevice #inputNameDeviceM").val() || $("#editInfoTabDevice #inputNameDeviceM").val().length === 0)
    {
        $("#inputNameDeviceMMsg").css("color", "red");
        message = 'Device name is not register but it is readonly';
        $("#inputNameDeviceM").prop("readonly", true);
        editDeviceConditionsArray['inputNameDeviceM'] = true;
    }
    else
    {
            $("#inputNameDeviceMMsg").css("color", "#337ab7");
            message = 'Device Name is ReadOnly';
            $("#inputNameDeviceM").prop("readonly", true);
            editDeviceConditionsArray['inputNameDeviceM'] = true;
        
    }
    
    $("#inputNameDeviceMsg").html(message);
}

function checkEditDeviceType()
{
    var message = null;
    if ( !$("#editInfoTabDevice #inputTypeDeviceM").val() || $("#editInfoTabDevice #inputTypeDeviceM").val().length === 0)
    {
        $("#inputTypeDeviceMMsg").css("color", "red");
        message = 'Device Type is mandatory';
        editDeviceConditionsArray['inputTypeDeviceM'] = false;
    }
	/*
    else if($("#editInfoTabDevice #inputTypeDeviceM").val().length < 5)
    {
        $("#inputTypeDeviceMMsg").css("color", "red");
        message = 'Type name (at least 5 chars long)';
        editDeviceConditionsArray['inputTypeDeviceM'] = false;
    }
	*/
    else
    {
		//Check if this part is necessary
        if(($.inArray($("#editInfoTabDevice #inputTypeDeviceM").val(), devicenamesArray, 0) > 0)||($("#editInfoTabDevice #inputTypeDeviceM").val() === devicenamesArray[0]))
        {
            $("#inputTypeDeviceMMsg").css("color", "red");
            message = 'Device Type already used';
            editDeviceConditionsArray['inputTypeDeviceM'] = false;
        }
        else
        {
            if($("#editInfoTabDevice #inputTypeDeviceM").val().indexOf(' ')>-1) {
				$("#inputTypeDeviceMMsg").css("color", "red");
				message = 'Device Type cannot contain blank spaces';
				editDeviceConditionsArray['inputTypeDeviceM'] = false;
			}
			else {
				$("#inputTypeDeviceMMsg").css("color", "#337ab7");
				message = 'Ok';
				editDeviceConditionsArray['inputTypeDeviceM'] = true;
			}
        }
    }
    
    $("#inputTypeDeviceMMsg").html(message);
}


function checkEditDeviceLatitude()
{
    var message = null;
    var pattern = /^(\+|-)?(?:90(?:(?:\.0{1,6})?)|(?:[0-9]|[1-8][0-9])(?:(?:\.[0-9]{1,6})?))$/;
    //var pattern = /^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?)$/;
	var value = document.getElementById("inputLatitudeDeviceM").value;
	
    if( !value || value === '')
    {
        message = 'Latitude is mandatory';
        editDeviceConditionsArray['inputLatitudeDeviceM'] = false;
        $("#inputLatitudeDeviceMMsg").css("color", "red");
    }
    else if(!pattern.test(value))
    {
        message = 'Latitude format is not correct ';
        editDeviceConditionsArray['inputLatitudeDeviceM'] = false;
        $("#inputLatitudeDeviceMMsg").css("color", "red");
    }
    else if(pattern.test(value))
    {
        message = 'Ok';
        editDeviceConditionsArray['inputLatitudeDeviceM'] = true;
        $("#inputLatitudeDeviceMMsg").css("color", "#337ab7");
    }
    
    $("#inputLatitudeDeviceMMsg").html(message);
}


function checkEditDeviceLongitude()
{
    var message = null;
   // var reg = new RegExp("^-?([1-8]?[1-9]|[1-9]0)\.{1}\d{1,6}");
    var pattern = /^[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)$/;
	var value = document.getElementById("inputLongitudeDeviceM").value;
	
	if(!value || value === '')
    {
        message = 'Longitude is mandatory';
        editDeviceConditionsArray['inputLongitudeDeviceM'] = false;
        $("#inputLongitudeDeviceMMsg").css("color", "red");
    }
    else if(!pattern.test(value))
    {
        message = 'Latitude format is not correct ';
        editDeviceConditionsArray['inputLongitudeDeviceM'] = false;
        $("#inputLongitudeDeviceMMsg").css("color", "red");
    }
    else if(pattern.test(value))
    {
        message = 'Ok';
        editDeviceConditionsArray['inputLongitudeDeviceM'] = true;
        $("#inputLongitudeDeviceMMsg").css("color", "#337ab7");
    }
    
    $("#inputLongitudeDeviceMMsg").html(message);
}


function checkEditDeviceMAC()
{
    var message = null;
	var pattern = /^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/;
	
	if( !$("#editInfoTabDevice #inputMacDeviceM").val() ||  $("#editInfoTabDevice #inputMacDeviceM").val().length === 0 || $("#editInfoTabDevice #inputMacDeviceM").val()==='null')
    {
        $("#inputMacDeviceMMsg").css("color", "red");
        message = '';
        editDeviceConditionsArray['inputMacDeviceM'] = true;
    }
	else {
			if(!pattern.test($("#editInfoTabDevice #inputMacDeviceM").val()))
				{
				message = 'Mac format should be Letter (A-F) and number (eg. 3D:F2:C9:A6:B3:4F)';
				editDeviceConditionsArray['inputMacDeviceM'] = false;
				$("#inputMacDeviceMMsg").css("color", "red");
				}
			else if(pattern.test($("#editInfoTabDevice #inputMacDeviceM").val()))
			{
				message = 'Ok';
				editDeviceConditionsArray['inputMacDeviceM'] = true;
				$("#inputMacDeviceMMsg").css("color", "#337ab7");
			}
    }
    
    $("#inputMacDeviceMMsg").html(message);
}


function UserEditKey()
	{
			var message = null;
			var pattern = /^[a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{12}$/;
			
			var value1 = document.getElementById("KeyOneDeviceUserM").value;
			var value2 = document.getElementById("KeyTwoDeviceUserM").value;
			
			if((value1 === '') &&  (value2 === ''))
			{
				message = 'Specify Key for the selected option';
				//document.getElementById("addMyNewDeviceConfirmBtn").disabled = true;
				editDeviceConditionsArray['KeyOneDeviceUserM'] = false;
				editDeviceConditionsArray['KeyTwoDeviceUserM'] = false;
				$("#KeyOneDeviceUserMsgM").css("color", "red");
				$("#KeyTwoDeviceUserMsgM").css("color", "red");
				
			}
			else if(!pattern.test(value1) || !pattern.test(value2))
			{
				message = 'The Key should contain at least one special character and a number';
				//document.getElementById("addMyNewDeviceConfirmBtn").disabled = true;
				editDeviceConditionsArray['KeyOneDeviceUserM'] = false;
				editDeviceConditionsArray['KeyTwoDeviceUserM'] = false;
				$("#KeyOneDeviceUserMsgM").css("color", "red");
				$("#KeyTwoDeviceUserMsgM").css("color", "red");
			}
			else if(pattern.test(value1) && pattern.test(value2))
			{
				message = 'Ok';
				//document.getElementById("addMyNewDeviceConfirmBtn").disabled = false;
				editDeviceConditionsArray['KeyOneDeviceUserM'] = true;
				editDeviceConditionsArray['KeyTwoDeviceUserM'] = true;
				$("#KeyOneDeviceUserMsgM").css("color", "#337ab7");
				$("#KeyTwoDeviceUserMsgM").css("color", "#337ab7");
				gb_key1 = $("#KeyOneDeviceUserM").value;
			    gb_key2 = $("#KeyTwoDeviceUserM").value;
			}
			
			$("#KeyOneDeviceUserMsgM").html(message);
			$("#KeyTwoDeviceUserMsgM").html(message);
	}
	
	
	
function checkEditDeviceConditions()
{
	//check that any value has a correct name/syntax
	var n = $('#editSchemaTabDevice #editlistAttributes .row input.valueName').filter(function(){return this.value.length>=2}).length;
	var nx= $('#editSchemaTabDevice #addlistAttributesM .row input.valueName').filter(function(){return this.value.length>=2}).length;
	var n1 =$('#editSchemaTabDevice #editlistAttributes .row input.valueName').length;
	var n1x =$('#editSchemaTabDevice #addlistAttributesM .row input.valueName').length;

	//console.log("n: "+n+" n1:"+n1+" nx:"+nx+" n1x:"+n1x);
	if ((n+nx)==(n1+n1x))
	{
		editDeviceConditionsArray['attributeWithName'] = true;
	}
	else
	{
		editDeviceConditionsArray['attributeWithName'] = false;
	}

	//check that any value has a correct name/syntax. this enforce is done here since the list of values is dynamic
	var regex=/([^a-z0-9:._-])/gi  ;
	var o = $('#editSchemaTabDevice #editlistAttributes .row input.valueName').filter(function(){return !regex.test(this.value)}).length;
	var ox = $('#editSchemaTabDevice #addlistAttributesM .row input.valueName').filter(function(){return !regex.test(this.value)}).length;

	//console.log("o: "+o+" n1:"+n1+" ox:"+ox);
	if ((o+ox)==(n1+n1x))
	{
		editDeviceConditionsArray['specialChars'] = true;
	}
	else
	{
		editDeviceConditionsArray['specialChars'] = false;
	}
      

	//check that any value has a value type selected
	var p = $('#editSchemaTabDevice #editlistAttributes select[id*="value_type"]').filter(function(){return this.value!=="NOT VALID OPTION"}).length;
	var px = $('#editSchemaTabDevice #addlistAttributesM select[id*="value_type"]').filter(function(){return this.value!=="NOT VALID OPTION"}).length;

	//console.log("p: "+p+" n1:"+n1+" px:"+px);
	if ((p+px)==(n1+n1x))
	{
		editDeviceConditionsArray['attributeWithValueType'] = true;
	}
	else
	{
		editDeviceConditionsArray['attributeWithValueType'] = false;
	}

	//check that any value has a value unit selected
	var c = $('#editSchemaTabDevice #editlistAttributes select[id*="value_unit"]').filter(function(){return this.value!=="NOT VALID OPTION"}).length;
	var cx = $('#editSchemaTabDevice #addlistAttributesM select[id*="value_unit"]').filter(function(){return this.value!=="NOT VALID OPTION"}).length;

	//console.log("c: "+c+" n1:"+n1+" cx:"+cx);
	if ((c+cx)==(n1+n1x))
	{
		editDeviceConditionsArray['attributeWithValueUnit'] = true;
	}
	else
	{
		editDeviceConditionsArray['attributeWithValueUnit'] = false;
	}

	var enableButton = true;
	for(var key in editDeviceConditionsArray) 
	{
		if(editDeviceConditionsArray[key] === false)
		{
			enableButton = false;
			console.log("need:" + key);
			break;
		}
	}
    
	if(enableButton)
	{
		$("#editDeviceConfirmBtn").attr("disabled", false);
	}
	else
	{
		$("#editDeviceConfirmBtn").attr("disabled", true);
	}
}

//don't use ConditionsArray here since the list of Values is dynamic and should be checked in all together in checkAddDeviceConditions
function checkEditValueName(current)
{
    value=current.val();
    element=current.parent().siblings().last();

    //console.log("Check edit value name on:"+value);

    var message = null;
    var regex=/[^a-z0-9:._-]/gi;
      var regex2=/\bid\b/;
     var regex3= /\btype\b/;

    if  (!value || value.length === 0)
    {
        element.css("color", "red");
        message = 'Value name is mandatory';
    }else if(regex2.test(value) || regex3.test(value) )
    {
        element.css("color", "red");
        message = 'No valid Value name: you can not use <b>id</b> or  <b>type</b>';
    }
    else if(value.length < 2)
    {
        element.css("color", "red");
        message = 'Value name (at least 2 chars long)';
    }
    else if(regex.test(value))
    {
        element.css("color", "red");
        message = 'No special characters are allowed in Value name';
    }
    else
    {
        element.css("color", "#337ab7");
        message = 'Ok';
    }

    element.html(message);
}

function checkEditAtlistOneAttribute()
{
    var message = null;

    if (( !$("#editlistAttributes").html() ||  $("#editlistAttributes").html().length === 0)
	&&
	(!$("#addlistAttributesM").html() ||  $("#addlistAttributesM").html().length === 0))
    {
        $("#editlistAttributesMsg").css("color", "red");
        message = 'At least a value needs to be specified';
        editDeviceConditionsArray['oneAttribute'] = false;
    }
    else
    {
                $("#editlistAttributesMsg").css("color", "#337ab7");
                message = '';
                editDeviceConditionsArray['oneAttribute'] = true;
    }
    $("#editlistAttributesMsg").html(message);
}

