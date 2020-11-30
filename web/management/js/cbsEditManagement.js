
var cbnamesArray, editCbConditionsArray;

editCbConditionsArray = new Array();

function showEditCbModal()
{
    cbnamesArray = new Array();
    editCbConditionsArray['inputIpCBM'] = false;
    editCbConditionsArray['inputPortCBM'] = false;
    editCbConditionsArray['inputLatitudeCBM'] = false;
    editCbConditionsArray['inputLongitudeCBM'] = false;
    editCbConditionsArray['inputUrlOrionCallbackM'] = false;
	
	$("#editInfoTabCB #inputIpCBM").on('input', checkEditCbIp);
	$("#editInfoTabCB #inputIpCBM").on('input', checkEditCbConditions);
	
	$("#editInfoTabCB #inputPortCBM").on('input', checkEditCbPort);
	$("#editInfoTabCB #inputPortCBM").on('input', checkEditCbConditions);

	$("#editInfoTabCB #selectProtocolCBM").on('change', checkEditCbUrlOrionCallback);
        $("#editInfoTabCB #selectProtocolCBM").on('change', checkEditCbConditions);

        $("#editInfoTabCB #selectKindCBM").on('change', checkEditCbUrlOrionCallback);
        $("#editInfoTabCB #selectKindCBM").on('change', checkEditCbConditions);
	
	$("#editGeoPositionTabCB #inputLatitudeCBM").on('input', checkEditCbLatitude);
	$("#editGeoPositionTabCB #inputLatitudeCBM").on('input', checkEditCbConditions);
	
	$("#editGeoPositionTabCB #inputLongitudeCBM").on('input', checkEditCbLongitude);
	$("#editGeoPositionTabCB #inputLongitudeCBM").on('input', checkEditCbConditions);

        $("#editSubscriptionTabCB #inputUrlOrionCallbackM").on('input', checkEditCbUrlOrionCallback);
        $("#editSubscriptionTabCB #inputUrlOrionCallbackM").on('input', checkEditCbConditions);
	
	checkEditCbIp();
	checkEditCbPort();
	checkEditCbLatitude();
	checkEditCbLongitude();
	checkEditCbUrlOrionCallback();

    // --------check Edit CB service values
    checkEditCbServices();

    // Handle first service row
    $("#editServiceTenantTabCB").find('input[name="editInputServiceCB"]').on('input', checkEditCbServices);
    $("#editServiceTenantTabCB").find('input[name="editInputServiceCB"]').on('input', checkEditCbConditions);

    // Handle change protocol
    $('#selectProtocolCBM').on('change', checkEditCbServices);
    $('#selectProtocolCBM').on('change', checkEditCbConditions);


    // Handle the additional rows
    $("#editServiceTenantTabCB").on('input', 'div[name="additionalRow"]', checkEditCbServices);
    $("#editServiceTenantTabCB").on('input', 'div[name="additionalRow"]', checkEditCbConditions);

    // Observe the Multi-Service/Tenant Tab for child element creation/removal
    const targetNode = document.getElementById('editServiceTenantTabCB');
    // Options for the observer (which mutations to observe)
    const config = {childList: true};
    // Callback function to execute when mutations are observed
    const callback = function() {
        checkEditCbServices();
        checkEditCbConditions();
    };
    // Create an observer instance linked to the callback function
    const observer = new MutationObserver(callback);
    // Start observing the target node for configured mutations
    observer.observe(targetNode, config);
}

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
//	checkEditCbUrlOrionCallback();


    var enableButton = true;
    console.log(editCbConditionsArray);
    for(var key in editCbConditionsArray) 
    {
		console.log("check _"+key);
        if(editCbConditionsArray[key] === false)
        {
			console.log("false");
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

function checkEditCbUrlOrionCallback()
{
    var message = null;
    var pattern = /^(http:\/\/www\.|https:\/\/www\.|http:\/\/|https:\/\/)?[a-z0-9./:]+$/
    var kind = document.getElementById("selectKindCBM").value;
    var protocol = document.getElementById("selectProtocolCBM").value;
    var url = document.getElementById("inputUrlOrionCallbackM").value;

    //console.log("kind:"+kind+" protocol:"+protocol+" value:"+url);

    if ((kind === 'internal')&&(protocol.indexOf('ngsi')!==-1)){
        if(url === '')
        {
                message = 'Url Orion Callback is mandatory';
                editCbConditionsArray['inputUrlOrionCallbackM'] = false;
                $("#selectUrlOrionCallbackMsgM").css("color", "red");
        }
        else if(!pattern.test(url))
        {
                message = 'Url Orion Callback is malformed';
                editCbConditionsArray['inputUrlOrionCallbackM'] = false;
                $("#selectUrlOrionCallbackMsgM").css("color", "red");
        }
        else
        {
                message = 'Ok';
                editCbConditionsArray['inputUrlOrionCallbackM'] = true;
                $("#selectUrlOrionCallbackMsgM").css("color", "#337ab7");
        }
    }
	else
		editCbConditionsArray['inputUrlOrionCallbackM'] = true;
    //else is not considered because this tab is not showed

    $("#selectUrlOrionCallbackMsgM").html(message);
}

function checkEditCbServices(){
    // console.log("checkEditCbServices");

    // feedback message to the user
    var message = null;
    // service values
    var values = [];
    // check if the tab is hidden or not
    var isHidden = $('#editMultiServiceTabSelector').hasClass('hidden');

    // insert first row value 
    // the undefined check is done to avoid an error occuring when an update is done
    var firstValue = $('#editServiceCBRow1').find('input[name="editInputServiceCB"]').val();
    if (firstValue !== undefined) values.push(firstValue.trim());
    // get values of all the additional rows
    $('#editServiceTenantTabCB div[name="additionalRow"]').find('input[name="editInputServiceCB"]').each(function(){
        values.push($(this).val().trim());
    });

    // check if the MultiService tab is hidden
    if (isHidden) {
        editCbConditionsArray['inputServicesCBM'] = true;
        return;
    } else {

        if (values.length == 1){
            //console.log("un solo service");

            var serviceRegex = /^([a-z]|_|[0-9]){1,25}$/;
            if (values[0] !== "" && !serviceRegex.test(values[0])) {
                message = `Check your values <br>
                        <ul>
                            <li>white spaces are not allowed</li>
                            <li>use only lower case letters</li>
                            <li>special characters are not allowed (except for "_")</li>
                            <li>service/tenant name must not be longer than 25 characters</li>
                        </ul>`;
                editCbConditionsArray['inputServicesCBM'] = false;
                $("#editInputServiceCBMsg").removeClass("alert alert-info");
                $("#editInputServiceCBMsg").addClass("alert alert-danger");
                $("#editInputServiceCBMsg").html(message);
            } else {
                message = 'Ok';
                editCbConditionsArray['inputServicesCBM'] = true;
                $("#editInputServiceCBMsg").removeClass("alert alert-danger");
                $("#editInputServiceCBMsg").addClass("alert alert-info");
                $("#editInputServiceCBMsg").html(message);
            }
        } else {
            //console.log("più services");

            for(const value of values){
                var serviceRegex = /^([a-z]|_|[0-9]){1,25}$/;
                if(!serviceRegex.test(value)){
                    message = `Check your values <br>
                        <ul>
                            <li>white spaces are not allowed</li>
                            <li>use only lower case letters</li>
                            <li>special characters are not allowed (except for "_")</li>
                            <li>service/tenant name must not be longer than 25 characters</li>
                        </ul>`;
                    editCbConditionsArray['inputServicesCBM'] = false;
                    $("#editInputServiceCBMsg").removeClass("alert alert-info");
                    $("#editInputServiceCBMsg").addClass("alert alert-danger");
                    $("#editInputServiceCBMsg").html(message);
                    break;
                }else{
                    message = 'Ok';
                    editCbConditionsArray['inputServicesCBM'] = true;
                    $("#editInputServiceCBMsg").removeClass("alert alert-danger");
                    $("#editInputServiceCBMsg").addClass("alert alert-info");
                    $("#editInputServiceCBMsg").html(message);
                }
            }
        }
    }
} 
