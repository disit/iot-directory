$.fn.modal.Constructor.prototype.enforceFocus = function () {};
var gb_datatypes = "";
var gb_value_units = "";
var gb_value_types = "";
var defaultPolicyValue = [];
var gb_options = [];
var dataTable = "";
var gb_device = "";
var gb_latitude = "";
var gb_longitude = "";
var gb_old_cb = "";
var valueTypeOpt = "";
var valueUnitOpt = "";
var gb_valVU = "";
var gb_valVT = "";
var _serviceIP = "../stubs";
var indexValues = 0; //it keeps track of unique identirier on the values, so it's possible to enforce specific value type 
var currentEditId = ""; //it keeps the current id of device in edit, so it's possibile to avoid to add any time the same values of the current device id
var filterDefaults = {
    myOwnPrivate: 'MyOwnPrivate',
    myOwnPublic: 'MyOwnPublic',
    myPrivate: 'private',
    public: 'public'
};
var tableFirstLoad = true;
var  addDeviceConditionsArrayBC = new Array();
var certifiedDevices = new Array();


addDeviceConditionsArrayBC['inputNameDevice'] = false;
addDeviceConditionsArrayBC['inputFromDateBC'] = false;
addDeviceConditionsArrayBC['inputToDateBC'] = false;



//--------to get the datatypes items----------
$.ajax(
        {url: "../api/device.php",
            data: {
                action: 'get_param_values',
                token: sessionToken
            },
            type: "POST",
            async: true,
            dataType: 'json',
            success: function (mydata) {
                if (mydata["status"] === 'ok') {
                    gb_datatypes = mydata["data_type"];
                    gb_value_units = mydata["value_unit"];
                    gb_value_types = mydata["value_type"];
                    addSubnature($("#selectSubnature"), mydata["subnature"]);
                    addSubnature($("#selectSubnatureM"), mydata["subnature"]);
                } else {
                    console.log("error getting the data types " + data);
                }
            },
            error: function (mydata) {
                console.log(JSON.stringify(mydata));
                alert("Network errors. <br/> Get in touch with the Snap4City Administrator<br/>" + JSON.stringify(mydata));
            }
        });


$.ajax({
    url: "../api/device.php",
    data: {
        action: "get_all_device",
        token: sessionToken,
        only_certified: true
    },
    type: "POST",
    async: false,
    dataType: 'json',
    success: function (mydata)
    {
        // $('#addDeviceLoadingMsg').hide()
        // $('#addDeviceLoadingIcon').hide()
        let i=0;
        console.log(mydata.data.length)
        while(i < mydata.data.length){
            certifiedDevices.push(mydata.data[i].id)

            //select2Dropdown.append(new Option(mydata.data[i].id, mydata.data[i].id));
            i++;
        }

    },
    error: function (data)
    {

    }
});

//--------to get the models with their details----------------------


function ajaxRequest() {
    var request = false;
    try {
        request = new XMLHttpRequest()
    } catch (e1) {
        try {
            request = new ActiveXObject("Msxml2.XMLHTTP")
        } catch (e2) {
            try {
                request = new ActiveXObject("Microsoft.XMLHTTP")
            } catch (e3) {
                request = false
            }
        }
    }
    return request;
}

function removeElementAt(parent, child) {
    var list = document.getElementById(parent);
    if (parent == "editlistAttributes")
        document.getElementById('deletedAttributes').appendChild(child.parentElement.parentElement.parentElement);
    else
        list.removeChild(child.parentElement.parentElement.parentElement);
    checkAtlistOneAttribute();
    checkEditAtlistOneAttribute();
}

//LOAD attr of model 

// function SuccessOfLoadAttr(data, kindModel, version, domain, subdomain) {
//     if (data["status"] === 'ko') {
//         alert("An error occured when reading the data. <br/> Get in touch with the Snap4City Administrator<br/>" + data["msg"]);
//     } else if (data["status"] === 'ok') {
//         var k = 0;
//         var content = "";
//         if (kindModel == 'NATIVE') {
//             var model = data.content.name;
//             var type = data.content.devicetype;
//             var kind = data.content.kind;
//             var producer = data.content.producer;
//             var frequency = data.content.frequency;
//             var contextbroker = data.content.contextbroker;
//             var protocol = data.content.protocol;
//             var format = data.content.format;
//             var myattributes = JSON.parse(data.content.attributes);
//             var subnature = data.content.subnature;
//             var edgegateway_type = data.content.edgegateway_type;
//             var static_attributes = data.content.static_attributes;
//             var service = data.content.service;
//             var servicePath = data.content.servicePath;
//             var valOrg = data.content.cb_organization;
//             // population of the value tab with the values taken from the db
//             while (k < myattributes.length) {
//                 content += drawAttributeMenu(myattributes[k].value_name,
//                         myattributes[k].data_type, myattributes[k].value_type, myattributes[k].editable, myattributes[k].value_unit, myattributes[k].healthiness_criteria,
//                         myattributes[k].healthiness_value, myattributes[k].old_value_name, 'addlistAttributes', indexValues);
//                 indexValues = indexValues + 1;
//                 k++;
//             }
//             subnatureChanged(false, JSON.parse(static_attributes));
//             $('#inputTypeDevice').val(type);
//             $('#selectSubnature').val(subnature);
//             $('#selectSubnature').trigger('change');
//             if(subnature){
//              $("#addNewStaticBtn").show();}
//         } else {
//             var myattributes = JSON.parse(data.content.attributes);
//             Object.keys(myattributes).forEach(function (k) {
//                 if (myattributes[k].value_name != 'type') {
//                     content += drawAttributeMenu(myattributes[k].value_name,
//                             myattributes[k].data_type, myattributes[k].value_type, myattributes[k].editable, myattributes[k].value_unit, myattributes[k].healthiness_criteria,
//                             myattributes[k].healthiness_value, '', 'addlistAttributes', indexValues);
//                     indexValues = indexValues + 1;
//                 }
//
//             });
//
//         }
//
//         $('#addlistAttributes').html(content);
//
//         $('#selectKindDevice').val(kind);
//         $('#inputProducerDevice').val(producer);
//         $('#inputFrequencyDevice').val(frequency);
//         //$('#inputMacDevice').val(data.content.mac);
//         $('#selectContextBroker').val(contextbroker);
//         $('#selectProtocolDevice').val(protocol);
//         $('#selectFormatDevice').val(format);
//         $('#selectEdgeGatewayType').val(edgegateway_type);
//
//         addDeviceConditionsArray['contextbroker'] = true;
//         addDeviceConditionsArray['kind'] = true;
//         addDeviceConditionsArray['format'] = true;
//         addDeviceConditionsArray['protocol'] = true;
//         checkSelectionCB();
//         checkSelectionKind();
//         checkSelectionProtocol();
//         checkSelectionFormat();
//         addDeviceConditionsArray['inputTypeDevice'] = true;
//         checkDeviceType(); // checkAddDeviceConditions();
//         addDeviceConditionsArray['inputFrequencyDevice'] = true;
//         checkFrequencyType(); // checkAddDeviceConditions();
//         addDeviceConditionsArray['inputMacDevice'] = true;
//         checkMAC();
//         checkAtlistOneAttribute();
//         checkAddDeviceConditions();
//         getServicesByCBName($('#selectContextBroker').val(), 'add', service);
//         checkProtocol($('#selectProtocolDevice').val(), 'add', 'device');
//         $('#inputServicePathDevice').val(servicePath);
//         checkServicePath($('#inputServicePathDevice').val(), 'add', 'device');
//         checkAddDeviceConditions();
//         if (valOrg)
//             $("#selectContextBrokerMsg").html($("#selectContextBrokerMsg").html() + " - Organization:" + valOrg);
//     }
// }

// function drawAttributeMenu(attrName, data_type, value_type, editable, value_unit, healthiness_criteria, value_refresh_rate, old_value_name, parent, indice)
// {
//     if (attrName == "") {
//         msg = "<div style=\"color:red;\" class=\"modalFieldMsgCnt\"></div>";
//     } else {
//         msg = "<div class=\"modalFieldMsgCnt\">&nbsp;</div>";
//     }
//
//     options = "";
//     mydatatypes = "";
//     if (value_type == "") {
//         options += "<option hidden disabled selected value=\"NOT VALID OPTION\"> -- select an option -- </option>";
//         msg_value_type = "<div style=\"color:red;\" class=\"modalFieldMsgCnt\">Value type is mandatory</div>";
//     } else {
//         msg_value_type = "<div style=\"color:#337ab7;\" class=\"modalFieldMsgCnt\">Ok</div>";
//     }
//
//     for (var n = 0; n < gb_value_types.length; n++)
//     {
//         if (value_type == gb_value_types[n].value) {
//             options += "<option value=\"" + gb_value_types[n].value + "\" selected>" + gb_value_types[n].label + " (" + gb_value_types[n].value + ")</option>";
//         } else {
//             options += "<option value=\"" + gb_value_types[n].value + "\" >" + gb_value_types[n].label + " (" + gb_value_types[n].value + ")</option>";
//
//         }
//     }
//
//     myunits = "";
//     msg_value_unit = "<div style=\"color:#337ab7;\" class=\"modalFieldMsgCnt\">Ok</div>";
//     //retrieve acceptable value unit, and select the selected if available
//     validValueUnit = getValidValueUnit(value_type, value_unit);
//     if (validValueUnit !== "") {
//         if (!validValueUnit.includes('selected')) {
//             myunits += "<option hidden disabled selected value=\"NOT VALID OPTION\"> -- select an option -- </option>";
//             msg_value_unit = "<div style=\"color:red;\" class=\"modalFieldMsgCnt\">Value unit is mandatory</div>";
//         }
//         myunits += validValueUnit;
//     }
//
//     msg_data_type = "<div style=\"color:#337ab7;\" class=\"modalFieldMsgCnt\">Ok</div>";
//     mydatatypes = "";
//     validDataType = getValidDataType(value_type, data_type);
//     if (validDataType !== "") {
//         if (!validDataType.includes('selected')) {
//             mydatatypes += "<option hidden disabled selected value=\"NOT VALID OPTION\"> -- select an option -- </option>";
//             msg_data_type = "<div style=\"color:red;\" class=\"modalFieldMsgCnt\">Data type is mandatory</div>";
//         }
//         mydatatypes += validDataType;
//     }
//
//     return "<div class=\"row\" style=\"border:2px solid blue; padding: 8px;\" id=\"value" + indice + "\">" +
//             "<div class=\"col-xs-6 col-md-3 modalCell\">" +
//             "<div  class=\"modalFieldCnt \" title=\"Insert a name for the sensor/actuator\"><input id=\"value_name\" type=\"text\" class=\"modalInputTxt Input_onlyread  valueName\"" +
//             "name=\"" + attrName + "\"  value=\"" + attrName + "\" onkeyup=\"checkStrangeCharacters(this)\">" +
//             "</div><div class=\"modalFieldLabelCnt\">Value Name</div>" + msg + "</div>" +
//             "<div class=\"col-xs-6 col-md-3 modalCell\"><div class=\"modalFieldCnt\">" +
//             "<select  class=\"modalInputTxt Select_onlyread\" id=\"value_type" + indice + "\" " +
//             "onchange=valueTypeChanged(" + indice + ") " +
//             "title=\"select the type of the sensor/actuator\"> " + options +
//             "</select></div><div   class=\"modalFieldLabelCnt\">Value Type</div>" + msg_value_type + "</div>" +
//             "<div class=\"col-xs-6 col-md-3 modalCell\"><div class=\"modalFieldCnt\" title=\"select the unit of the data generated by the sensor/actuator\">" +
//             "<select class=\"modalInputTxt Select_onlyread\" id=\"value_unit" + indice + "\" " +
//             "onchange=valueUnitChanged(" + indice + ") " +
//             "\">" +
//             myunits +
//             "</select></div><div  id=\"SELECTunit\" class=\"modalFieldLabelCnt\">Value Unit</div>" + msg_value_unit + "</div>" +
//             "<div class=\"col-xs-6 col-md-3 modalCell\"><div class=\"modalFieldCnt\">" +
//             "<select  class=\"modalInputTxt Select_onlyread InputDataType" + attrName + "\" id=\"data_type" + indice + "\"" +
//             "onchange=dataTypeChanged(" + indice + ") " +
//             "\" title=\"select the type of data generated by the sensor/actuator\">" + mydatatypes +
//             "</select></div><div  class=\"modalFieldLabelCnt\">Data Type</div>" + msg_data_type + "</div>" +
//             "<div class=\"col-xs-6 col-md-3 modalCell\"><label   class=\"switch \"> <input type=\"checkbox\" onclick=\"disableInput(id)\" style=\"display:none\"  class=\" Check_BOX\" id=\"Checkbox_" + attrName + "\" checked> <span style=\"display:none\"   id=\"SpanCheckbox_" + attrName + "\" class=\" Check_BOX slider round\">Send value</span></label></div>" +
//             "<div class=\"col-xs-6 col-md-3 modalCell\"><div class=\"modalFieldCnt\" title=\"select a criterion as a reference to decide whether the sensor/actuator is working well\">" +
//             "<select id=\"SELECTHealthCriteria\" class=\"modalInputTxt Select_onlyread Hidden_insert\" name=\"" + healthiness_criteria +
//             "\" \>" +
//             "<option value=\"refresh_rate\">Refresh rate</option>" +
//             "<option value=\"different_values\">Different Values</option>" +
//             "<option value=\"within_bounds\">Within bounds</option>" +
//             "</select></div><div  class=\"modalFieldLabelCnt Hidden_insert\">Healthiness Criteria</div></div>" +
//             "<div class=\"col-xs-6 col-md-3 modalCell\"><div class=\"modalFieldCnt\" title=\"Insert the limit value(s) to consider the sensor/actuator as healthy, according to the selected criterion \">" +
//             "<input id=\"device_refresh_value\" type=\"text\"  class=\"modalInputTxt Input_onlyread Hidden_insert\" name=\"" + value_refresh_rate +
//             "\" value=\"" + value_refresh_rate + "\"></div><div   class=\"modalFieldLabelCnt Hidden_insert\">Healthiness Value</div></div>" +
//             "<div class=\"col-xs-6 col-md-3 modalCell\">" + "<div style=\"display:none\"  class=\"modalFieldCnt INSERTValues\" title=\"Insert data in the sensor/actuator\"><input type=\"text\" class=\"modalInputTxt InputValue\"" +
//             "id=\"Value" + attrName + "\" \"name=\"Value" + attrName + "\" >" +
//             "</div><div  style=\"display:none\"  class=\"modalFieldLabelCnt INSERTValues\">Insert data</div> <span id=\"access-code-error" + attrName + "\" class=\"rsvp\" style=\"display:none; color:red;\"> Unvalid input</span></div>" +
//             "<div class=\"col-xs-6 col-md-3 modalCell\"><div class=\"modalFieldCnt\">" +
//             "<select class=\"modalInputTxt\" style=\"display:none\" name=\"" + old_value_name +
//             "\" \>" +
//             "<option value=\"" + old_value_name + "\">" + old_value_name + "</option>" +
//             "</select></div></div>" +
//             "<div class=\"col-xs-6 col-md-3 modalCell\"><div class=\"modalFieldCnt\">" +
//             "<button id=\"RemoveAttrEdit\" class=\"btn btn-danger RemoveAttrEdit\" onclick=\"removeElementAt('" + parent + "',this); return true;\">Remove Value</button></div></div></div></div>";
// }



function format(d) {
    var multitenancy = "";
    if (d.service || d.servicePath) {
        multitenancy =
                '<div class="row">' +
                '<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Service/Tenant:</b>' + "  " + d.service + '</div>' +
                '<div class="clearfix visible-xs"></div>' +
                '<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>ServicePath:</b>' + "  " + d.servicePath + '</div>' +
                '</div>';
    }

    var showKey = "";
    if (d.visibility == 'MyOwnPublic' || d.visibility == 'MyOwnPrivate' || d.visibility == 'delegated') {
        if (d.k1 != "" && d.k2 != "")
            showKey =
                    '<div class="row">' +
                    '<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>K1:</b>' + "  " + d.k1 + '</div>' +
                    '<div class="clearfix visible-xs"></div>' +
                    '<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>K2:</b>' + "  " + d.k2 + '</div>' +
                    '</div>';
    } else
        showKey = "";
    var showPayload = '<div class="row">' +
            '<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><button class="btn btn-info my-small-button" onclick="datainspect(\'' +
            d.id + '\',\'' + d.devicetype + '\',\'' + d.contextBroker + '\',\'' + d.service + '\',\'' + d.servicePath + '\',\'v1\');return true;"><b>PAYLOAD NGSI v1</b></button></div>' +
            '<div class="clearfix visible-xs"></div>';
    // if (d.version=='v2')
    showPayload = showPayload + '<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><button class="btn btn-info my-small-button" title ="Read from IoT broker to be use to feed the device" onclick="datainspect(\'' +
            d.id + '\',\'' + d.devicetype + '\',\'' + d.contextBroker + '\',\'' + d.service + '\',\'' + d.servicePath + '\',\'v2\');return true;"><b>PAYLOAD NGSI v2</b></button></div>';
    showPayload = showPayload + '</div>';
    //console.log(d);
    var a = '<div  class="container-fluid">' +
            '<div class="row">' +
            '<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Broker URI:</b>' + "  " + d.accesslink + '</div>' +
            '<div class="clearfix visible-xs"></div>' +
            '<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Broker Port:</b>' + "  " + d.accessport + '</div>' +
            '</div>' +
            '<div class="row">' +
            '<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>Kind:</b>' + "  " + d.kind + '</div>' +
            '<div class="clearfix visible-xs"></div>' +
            '<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>Visibility:</b>' + "  " + d.visibility + '</div>' +
            '</div>' +
            '<div class="row">' +
            '<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Device Type:</b>' + "  " + d.devicetype + '</div>' +
            '<div class="clearfix visible-xs"></div>' +
            '<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Format:</b>' + "  " + d.format + '</div>' +
            '</div>' +
            '<div class="row">' +
            '<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>Protocol:</b>' + "  " + d.protocol + '</div>' +
            '<div class="clearfix visible-xs"></div>' +
            '<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>MAC:</b>' + "  " + d.macaddress + '</div>' +
            '</div>' +
            '<div class="row">' +
            '<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Model:</b>' + "  " + d.model + '</div>' +
            '<div class="clearfix visible-xs"></div>' +
            '<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Producer:</b>' + "  " + d.producer + '</div>' +
            '</div>' +
            '<div class="row">' +
            '<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>Longitude:</b>' + "  " + d.longitude + '</div>' +
            '<div class="clearfix visible-xs"></div>' +
            '<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>Latitude:</b>' + "  " + d.latitude + '</div>' +
            '</div>' +
            '<div class="row">' +
            '<div class="col-xs-12 col-sm-12" style="background-color:#E6E6FA;" data-toggle="tooltip" title="Go to the log LD"><b>Device Uri:</b><a href="' + d.url + '" target="_blank"> ' + d.uri + '</a> <a class="btn btn-info my-small-button pull-right" href="' + d.m_url + '" target="_blank"><b>VIEW IN SERVICE MAP</b></a></div>' +
            '</div>' +
            '<div class="row">' +
            '<div class="col-xs-12 col-sm-12" style="background-color:#D6CADD;"><b>Organization:</b>' + "  " + d.organization ;
    
    var b = ' <button type="button"class="btn btn-info my-small-button pull-right" ' +
            'data-id="' + d.id + '" ' +
            'data-contextbroker="' + d.contextBroker + '" ' +
            'data-service="' + d.service + '" ' +
            'data-servicePath="' + d.servicePath + '" ' +
            'data-devicetype="' + d.devicetype + '" ' +
            'data-latitude="' + d.latitude + '" ' +
            'data-longitude="' + d.longitude + '"  id="' + d.id + '_NewValuesInput" onclick="NewValuesOnDevice(id);"><b>NEW DATA IN</b> ' + d.id + '</button>'+'</div>' ;
    
    var aa=  '<div class="row">' +
            '<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Owner:</b>' + "  " + d.owner+
            '</div>' + '<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><button class="btn btn-info my-small-button pull-right" onclick="exportJsonDevice(\'' + d.id + '\' , \'' + d.model + '\',\'' + d.devicetype + '\',\'' + d.frequency + '\',\'' + d.kind + '\',\''+ d.contextBroker +'\',\'' + d.protocol + '\',\'' + d.format + '\',\'' + d.latitude + '\',\'' + d.longitude + '\',\'' + d.macaddress + '\',\'' + d.producer + '\',\'' + d.subnature + '\',\'' + btoa(d.staticAttributes) + '\',\'' + d.service + '\',\'' + btoa(d.servicePath) + '\');return true;"><b>EXPORT JSON</b></button></div>';
    
    var c = '</div>' + '</div>' +
            '<div class="clearfix visible-xs"></div>' +
            '</div>' +
            '</div>' +
            showPayload +
            multitenancy +
            showKey +
            getInfoCert(d.privatekey, d.visibility, d.created, d.id, d.contextBroker, d.certificate, d.sha) +
            '</div>';
    if (d.protocol == "ngsi" || d.protocol == "ngsi w/MultiService") {
        if ((loggedRole == "RootAdmin") || (loggedRole == "ToolAdmin") || d.visibility.substring(0, 5) == "MyOwn") {
            return a +b+ aa + c;
        }
        if (d.delegationKind === 'READ_WRITE' || d.delegationKind === 'MODIFY') {
            return a + b + c;
        }
    }

    return a +  c;
}

//DataTable fetch_data function 
function fetch_data(destroyOld, selected = null) {
    //console.log("Enter:" + selected);
    if (destroyOld)
    {
        $('#devicesTable').DataTable().clear().destroy();
        tableFirstLoad = true;
    }
    var page_length = 10;
    if (loggedRole == "ToolAdmin") {
        page_length = 5;   
    }
    
//     if(loggedRole == "ToolAdmin" || loggedRole == "RootAdmin" ){
//         if (selected == null)//TODO uniform these below calls
//         {
//             mydata = {action: "get_all_device_admin", token: sessionToken, no_columns: ["DeviceName", "fromdate", "todate", "owner", "requeststatus"]};
//         } else if (selected == 'delegated') {
//             mydata = {action: "get_all_device_admin", delegated: true, token: sessionToken, no_columns: ["DeviceName", "fromdate", "todate", "owner", "requeststatus"]};
//         } else if (selected == 'public') {
//             mydata = {action: "get_all_device_admin", public: true, token: sessionToken, no_columns: ["DeviceName", "fromdate", "todate", "owner", "requeststatus"]};
//         } else if (selected == 'own') {
//             mydata = {action: "get_all_device_admin", own: true, token: sessionToken, no_columns: ["DeviceName", "d.fromdate", "todate", "owner", "requeststatus"]};
//         } else
//         {
//             mydata = {action: "get_all_device_admin", own: true, token: sessionToken, select: selected, no_columns: ["DeviceName", "fromdate", "todate", "owner", "requeststatus"]};
//         }
//
//         var COL_CAST=[
//
//             {"name": "DeviceName", "data": function (row, type, val, meta) {
//
//                     return row.device_identifier;
//                 }},
//             {"name": "fromdate", "data": function (row, type, val, meta) {
//                     return row.start_date;
//                 }},
//             {"name": "d.todate", "data": function (row, type, val, meta) {
//
//                     return row.end_date;
//                 }},
//             {"name": "d.owner", "data": function (row, type, val, meta) {
//
//                     return row.owner;
//                 }},
//             {"name": "d.requeststatus", "data": function (row, type, val, meta) {
//
//                     return row.request_status;
//                 }},
// ,
//         ];
//     } else {
        prova=true;
        if (prova)//TODO uniform these below calls
        {
            mydata = {action: "get_all_device", token: sessionToken, no_columns: ["device_id", "start_date", "end_date", "owner_id", "request_status","check_performed","missing_data"]};
        } else if (selected == 'delegated') {
            mydata = {action: "get_all_device", delegated: true, token: sessionToken, no_columns: ["device_id", "start_date", "end_date", "owner_id", "request_status","check_performed","missing_data"]};
        } else if (selected == 'public') {
            mydata = {action: "get_all_device", public: true, token: sessionToken, no_columns: ["device_id", "start_date", "end_date", "owner_id", "request_status","check_performed","missing_data"]};
        } else if (selected == 'own') {
            mydata = {action: "get_all_device", own: true, token: sessionToken, no_columns: ["device_id", "start_date", "end_date", "owner_id", "request_status","check_performed","missing_data"]};
        } else
        {
            mydata = {action: "get_all_device", own: true, token: sessionToken, select: selected, no_columns: ["device_id", "start_date", "end_date", "owner_id", "request_status","check_performed","missing_data"]};
        }

        var COL_CAST=[

            {"name": "device_id", "data": function (row, type, val, meta) {
                    return row.device_id;
                }},
            {"name": "start_date", "data": function (row, type, val, meta) {
                    return row.start_date;
                }},
            {"name": "end_date", "data": function (row, type, val, meta) {

                    return row.end_date;
                }},
            {"name": "owner_id", "data": function (row, type, val, meta) {

                    return row.owner_id;
                }},
            {"name": "request_status", "data": function (row, type, val, meta) {

                    return row.request_status;
                }},
            {"name": "downloadreport" , "orderable": false, "data": function (row, type, val, meta){

                    if (row.request_status === 'completed') {
                        return '<button type="button" class="report_button" onclick="download_report(this)">' + 'Download report' + '</button>';
                    } else if (row.request_status === 'pending' || row.request_status === 'execution') {
                        return '<button type="button"  class="report_button_disabled" onclick="" disabled>' + 'Download report' + '</button>';
                    }else{
                        return '<button type="button"  class="report_button_error" onClick="download_report(this)" >' + 'Download report' + '</button>';
                    }
                }
                },
            {"name": "check_Performed", "data": function (row, type, val, meta) {

                    return row.check_performed;
                }},
            {"name": "missing_data", "data": function (row, type, val, meta) {

                    return row.missing_data;
                }},


        ];
    // }
    console.log(mydata)
    console.log(COL_CAST)

    dataTable = $('#devicesTable').DataTable({

        "processing": true,
        "search": {
            return: true
        },
        "serverSide": true,
        "lengthMenu": [[5, 25, 50, 100, -1], [5, 25, 50, 100, "All"]],
        "pageLength": page_length,
        "scrollX": true,
        "paging": true,
        "ajax": {
            url: "../api/blockchainrequests.php",
            data: mydata,
            datatype: 'json',
            type: "POST"

        },
        "columns": COL_CAST,
        "order": []


    });



    //function to search a device directly if "&device_name=..." is present in the URL
    const deviceNameInUrl = window.location.search;
    const urlParams = new URLSearchParams(deviceNameInUrl);
    const deviceToSearch = urlParams.get("device_id");
    if(deviceToSearch != null) {
        dataTable.search(deviceToSearch, true, false).draw();
    }

}
function download_report(button){
    const row = button.closest('tr');

    // Get data from each cell in the row
    const rowData = Array.from(row.cells).map(cell => cell.textContent);

    

    // Display the data on the screen (you can customize this part)

    $.ajax({
        url: "../api/blockchainrequests.php",
        data: {
            action: "download_report",
            token: sessionToken,
            deviceId: rowData[0],
            startDate: rowData[1],
            endDate: rowData[2]
        },
        type: "POST",
        async: false,
        dataType: 'json',
        success: function (mydata)
        {
            let device_info
            if(mydata["content"]["request_status"]=="error"){
                console.log(mydata)
                device_info=[
                    {device_id: rowData[0], start_date: rowData[1], end_date: rowData[2]},
                    {request_status: mydata["content"]["request_status"]},
                    {log: mydata["content"]["report"]}
                ]
            }else {
                let mydata_missing = mydata["content"]["report_missing"]
                mydata = mydata["content"]["report"]

                // Convert the JSON object to a JSON string
                // const jsonString = JSON.stringify(mydata, null, 2);
                //mydata=JSON.parse(mydata)

                mydata = mydata.replace(/'/g, "\"")
                mydata_missing = mydata_missing.replace(/'/g, "\"")
                mydata = JSON.parse(mydata)
                mydata_missing = JSON.parse(mydata_missing)

                device_info = [
                    {device_id: rowData[0], start_date: rowData[1], end_date: rowData[2]},
                    {matching_values: mydata},
                    {missing_values: mydata_missing}
                ]
            }

            //mydata.unshift(mydata)
            mydata=JSON.stringify(device_info)
            // Create a Blob with the JSON string
            const blob = new Blob([mydata], { type: 'application/json' });

            // Create a link element
            const a = document.createElement('a');

            // Set the link's href attribute to a URL created from the Blob
            a.href = URL.createObjectURL(blob);

            // Set the download attribute with the desired file name
            a.download = rowData[0] + '-blockchain-report.json';

            // Append the link to the body
            document.body.appendChild(a);

            // Trigger a click on the link to start the download
            a.click();

            // Remove the link from the body
            document.body.removeChild(a);
            // $('#addDeviceLoadingMsg').hide()
            // $('#addDeviceLoadingIcon').hide()


        },
        error: function (mydata){
        //TODO:da fare gestione errore
            $("#addDeviceKoModal").modal('show');
            $("#addDeviceKoModalInnerDiv1").show()
            $("#addDeviceKoModalInnerDiv1").html('<h5>Operation Failed due to the following Error: ' + mydata["error_msg"] + '</h5><br>');
            $("#addDeviceKoModalInnerDiv3").show()
            $("#addDeviceKoModalInnerDiv3").html(mydata["log"]);



        }
    });

}


// function NewValuesOnDevice(strID) {
//     document.getElementById('editLatLongValue').innerHTML = "";
//     document.getElementById('ValuesINPUT').innerHTML = "";
//     $("#NewValuesInputMODAL").modal('show');
//     $('#InsertDataDeviceLoadingIcon').show();
//     $('.nav-tabs a[href="#editAttributeValueTabDevice"]').tab('show');
// //    $('#Mtab').show();
// //    $('#Itab').show();
//     $('#NOMob').hide();
//     $('#editLatLongValue').hide();
//     document.getElementById('NewValuesInputConfirmButton').style.display = 'left';
//     $("#InsertModalStatus").hide();
//     $('#NewValuesInputConfirmButton').show();
//     $("#InsertDeviceModalTabs").show();
//     $("#GETimeStamp").show();
//     var strID = "#" + strID;
//     var Nid = $(strID).attr('data-id');
//     var Ntype = $(strID).attr('data-devicetype');
//     var Ncb = $(strID).attr('data-contextbroker');
//     var Nserv = $(strID).attr('data-service');
//     var NservPath = $(strID).attr('data-servicePath');
//     $.ajax({
//         url: "../api/device.php",
//         data: {
//             action: "Loading_value",
//             id: Nid,
//             type: Ntype,
//             contextbroker: Ncb,
//             token: sessionToken,
//             service: Nserv,
//             servicePath: NservPath,
//             version: "v2"
//         },
//         type: "POST",
//         async: true,
//         dataType: 'json',
//         success: function (mydata)
//         {
//             var old_value = mydata['content'];
//             $('a[data-toggle="tab"]').off('shown.bs.tab').on('shown.bs.tab', function (e) {
//                 //   $('#InsertDataDeviceLoadingIcon').show();
//                 $('#InsertDataDeviceLoadingIcon').hide();
//                 var target = $(e.target).attr("href");
//                 if ((target === '#editGeoPositionTabDeviceNewValue')) {
//                     $('#InsertDataDeviceLoadingIcon').hide();
//                     document.getElementById('editLatLongValue').innerHTML = "";
//                     document.getElementById('ValuesINPUT').innerHTML = "";
//                     $("#GETimeStamp").hide();
//                     $('#editLatLongValue').show();
//                     $("#NOMob").hide();
//                     $("#ValuesINPUT").hide();
//                     $('#NewValuesInputConfirmButton').show();
//                     $("#InsertModalStatus").hide();
//                     $("#NoMobile").hide();
//                     if (mydata['isMobile'] == "false") {
//                         $('#editLatLongValue').hide();
//                         $("#NOMob").hide();
//                         $("#NoMobile").show();
//                         $('#InsertDataDeviceLoadingIcon').hide();
//                         $("#InsertModalStatus").hide();
//                         $("#NoMobile").html('<br><br>' + Nid + " is not mobile!  ");
//                     } else {
//                         $("#InsertModalStatus").hide();
//                         $('#InsertDataDeviceLoadingIcon').hide();
//                         $("#NOMob").show();
//                         $('#inputLatitudeDeviceValue').val(old_value['latitude']);
//                         $('#inputLongitudeDeviceValue').val(old_value['longitude']);
//                         drawMap1(old_value['latitude'], old_value['longitude'], 4);
//                     }
//                     $('#InsertDataDeviceLoadingIcon').hide();
//                 } else if ((target === '#editAttributeValueTabDevice')) {
//                     $('#InsertDataDeviceLoadingIcon').show();
//                     // NewValuesInput management
//
//                     $('#editLatLongValue').hide();
//                     $("#GETimeStamp").show();
//                     $("#NoMobile").hide();
//                     $("#NOMob").hide();
//                     $("#ValuesINPUT").show();
//                     $('#NewValuesInputConfirmButton').show();
//                     document.getElementById('ValuesINPUT').innerHTML = "";
//                     if (old_value) {
//                         delete old_value['id'];
//                         delete old_value['type'];
//                         delete old_value['model'];
//                     }
//                     //console.log(old_value);
//                     console.log("Values loading");
//                     var DT = {};
//                     var NameAttrUp = new Array();
//                     var strTIME;
//                     $.ajax({
//                         url: "../api/device.php",
//                         data: {
//                             action: "get_device_attributes",
//                             id: $(strID).attr('data-id'),
//                             contextbroker: $(strID).attr('data-contextbroker'),
//                             //document.getElementById('selectContextBrokerM').value,
//                             token: sessionToken,
//                             service: $(strID).attr('data-service'),
//                             servicePath: $(strID).attr('data-servicePath')
//                         },
//                         type: "POST",
//                         async: true,
//                         dataType: 'json',
//                         success: function (mydata)
//                         {
//                             $('#InsertDataDeviceLoadingIcon').hide();
//                             $("#NewValuesInputMODAL").modal('show');
//                             $("#GETimeStamp").hide();
//                             var row = null;
//                             var strTIMEtemp;
//                             $("#editUserPoolsTable tbody").empty();
//                             myattributes = mydata['content'];
//                             content = "";
//                             k = 0;
//                             while (k < myattributes.length)
//                             {
//                                 content = drawAttributeMenu(myattributes[k].value_name,
//                                         myattributes[k].data_type, myattributes[k].value_type, myattributes[k].editable, myattributes[k].value_unit, myattributes[k].healthiness_criteria,
//                                         myattributes[k].healthiness_value, myattributes[k].value_name, 'ValuesINPUT', indexValues);
//                                 str = "#Value" + myattributes[k].value_name;
//                                 str_checkBox = "Checkbox_" + myattributes[k].value_name;
//                                 NameAttrUp.push("" + str_checkBox + "");
//                                 indexValues = indexValues + 1;
//                                 k++;
//                                 $('#ValuesINPUT').append(content);
//                                 $(".INSERTValues").show();
//                                 $(".Check_BOX").show();
//                                 j = k - 1;
//                                 //$(str).val(old_value[myattributes[j].value_name]);
//                                 if ($(str).val() == "") {
//                                     document.getElementById('NewValuesInputConfirmButton').disabled = true;
//                                 } else {
//                                     document.getElementById('NewValuesInputConfirmButton').disabled = false;
//                                 }
//
//                                 const input = document.querySelector(str);
//                                 var temp = {};
//                                 temp['' + myattributes[j].value_name + ''] = {type: myattributes[j].data_type, value: old_value['' + myattributes[j].value_name + '' ],value_type: myattributes[j].value_type};
//                                 $.extend(DT, temp);
//                                 // console.log(DT);
//
//                                 //$("#GETimeStamp").hide();
//                                 strTIMEtemp = "#Value" + myattributes[j].value_name;
//
//                                 //if a timestamp attribute is present show a "get timestamp button" under it
//
//                                 if (myattributes[j].value_type == 'timestamp' || !strTIMEtemp) {
//
//                                     strTIME = "#Value" + myattributes[j].value_name;
//
//
//                                     var valueIndexForRefresh = indexValues-1;
//
//                                     $('#ValuesINPUT, .row ').find('#value'+valueIndexForRefresh+'').append(' <div class="col-xs-6 col-md-3 modalCell"></div>');
//                                     $('#ValuesINPUT, .row ').find('#value'+valueIndexForRefresh+'').append(' <div class="col-xs-6 col-md-3 modalCell"><button type="button" id="GETimeStamp" style="" class="btn confirmBtn">Get Time stamp</button></div>')
//
//
//
//
//                                 }
//                                 //if a date attribute is present, show a calendar on the input field
//
//                                  if (myattributes[j].value_type == 'date') {
//
//                                      if(j==0){
//
//                                         document.getElementById("Valuedata").type="date";
//                                         $('#Valuedata').attr('data-format', 'dd/MM/yy');
//
//                                      }else {
//
//                                          var valueDateSelector = j + 1;
//                                          valueDateSelector = "Valuedata" + valueDateSelector;
//                                          document.getElementById(valueDateSelector).type = "date";
//                                          $('#Valuedata').attr('data-format', 'dd/MM/yy');
//                                      }
//                                  }
//
//
//                                 input.addEventListener('change', InputValuesCheck)
//                                 input.addEventListener('keyup',InputValuesCheck)
//                                 function InputValuesCheck(e) {
//                                     var a = (e.target.value);
//                                     const okButton = document.getElementById('NewValuesInputConfirmButton');
//                                     okButton.disabled=true;
//                                     t = e.currentTarget.id.substring(5, e.currentTarget.id.length);
//                                     str1 = "#access-code-error" + t;
//                                     str2 = ".InputDataType" + t;
//
//                                     //Check for input correctness against "value_type" instead of "data_type" if an attribute it's a date or a timestamp
//                                     //because both of it are string and you can't check them properly without some data manipulation.
//                                     //So the logic is if "Value_type= date" validate it as a date, if "Value_type= timestamp" validate it as a ISOstring,
//                                     // if it's any other type, enter the switch clause and validate the input against the corresponding type.
//                                     //N.B- The timestamp is accepted as yyyy-mm-ddThh:mm:ss:mmmZ and as yyyy-mm-dd
//
//                                     if(DT[t].value_type =="date"){
//                                         var parts = a.split("-");
//                                         var year = parseInt(parts[0], 10);
//                                         if (year > 1000 && year < 9999) {
//                                             okButton.disabled = false;
//                                             $(str1).hide()
//                                         }else{
//                                             okButton.disabled=true;
//                                             $(str1).show()
//                                         }
//                                     }else if(DT[t].value_type =="timestamp"){
//                                         const isoDatePattern = /^(\d{4})-(\d{2})-(\d{2})(T(\d{2}):(\d{2})(:\d{2}(\.\d{1,3})?)?(Z|([+-]\d{2}:\d{2})))?$/;
//
//
//                                         if (isoDatePattern.test(a)) {
//                                             okButton.disabled = false;
//                                             $(str1).hide()
//                                       } else {
//                                         okButton.disabled=true;
//                                         $(str1).show()
//                                       }
//                                     }else{
//
//                                         switch (DT[t].type) {
//                                             case "float" :
//                                             {
//                                                 if (/\s/.test(a) || a == "" || isNaN(a) || /[!@#$%^&*()_+\=\[\]{};':"\\|<>\/?]+/.test(a)) {
//                                                     $(str1).show();
//                                                     okButton.disabled = true;
//                                                 } else {
//                                                     a = a.replace(/,/g, '.')
//                                                     a = parseFloat(a);
//                                                     $(str1).hide();
//                                                     okButton.disabled = false;
//                                                 }
//                                             }
//                                                break;
//
//                                             case "integer" :
//                                                 {
//                                                     if (/\s/.test(a) || a == "" || isNaN(a) || /[!@#$%^&*()_+\=\[\]{};':"\\|<>\/?]+/.test(a)) {
//                                                         $(str1).show();
//                                                         okButton.disabled = true;
//                                                     } else {
//                                                         a = parseFloat(a);
//                                                         $(str1).hide();
//                                                         okButton.disabled = false;
//                                                     }
//                                                 }
//                                                 break;
//
//                                             case "binary":
//                                                 {
//                                                     if (/\s/.test(a) || a == "" || isNaN(a) || /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]+/.test(a)) {
//                                                         $(str1).show();
//                                                         okButton.disabled = true;
//                                                     } else {
//                                                         a = a.toString(2);
//                                                         $(str1).hide();
//                                                         okButton.disabled = false;
//                                                     }
//                                                 }
//                                                 break;
//
//                                             case "boolean" :
//                                             case "switch":
//                                             case "button":
//                                                 {
//                                                     if (/\s/.test(a) || a == "" || a !== false || a !== true || /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]+/.test(a)) {
//                                                         $(str1).show();
//                                                         okButton.disabled = true;
//                                                     } else {
//                                                         $(str1).hide();
//                                                         a = Boolean(a);
//                                                         okButton.disabled = false;
//                                                     }
//                                                 }
//                                                 break;
//
//                                             case "date" :
//                                             case "datatime":
//                                             case "time":
//                                             case "timestamp":
//                                             case "json" :
//                                             {
//                                                 var IS_JSON = true;
//                                                 try {
//                                                     var json = $.parseJSON(a);
//                                                 } catch (err) {
//                                                     IS_JSON = false;
//                                                 }
//                                                 if (!IS_JSON) {
//                                                     $(str1).show();
//                                                     okButton.disabled = true;
//                                                 } else {
//
//                                                     $(str1).hide();
//                                                     if (a != DT[t].value) {
//                                                         okButton.disabled = false;
//                                                     }
//
//                                                 }
//                                             }
//                                                 break;
//
//                                             case "collection":
//                                             case "set" :
//                                             case "vector" :
//                                             case "shape" :
//                                             case "wkt" :
//                                                 {
//                                                     if (/\s/.test(a) || a == "" || /[!@#$%^&*()_+\-=;'"\\|,.<>\/?]+/.test(a)) {
//                                                         $(str1).show();
//                                                         okButton.disabled = true;
//                                                     } else {
//                                                         $(str1).hide();
//                                                         a = JSON.parse(a);
//                                                         okButton.disabled = false;
//                                                     }
//                                                 }
//                                                 break;
//
//                                             case "xlm" :
//                                             case "string":
//                                                 {
//                                                     if (/\s/.test(a) || a == "" || /["'=;\(\)]/.test(a)) {
//                                                         $(str1).show();
//                                                         okButton.disabled = true;
//                                                     } else {
//                                                         $(str1).hide();
//                                                         //a = parseFromString(a, "text/xml");
//                                                         okButton.disabled = false;
//                                                     }
//                                                 }
//                                                 break;
//
//                                         } }
//                                 };
//                             }
//                             $("#editSchemaTabDevice #ValuesINPUT .row input:even").each(function () {
//                                 checkEditValueName($(this));
//                             });
//                             $("#GETimeStamp").click(function () {
//
//                                 $(strTIME).val(old_value[myattributes[j].value_name]);
//                                 const currentTime = new Date().toISOString();
//                                 $(strTIME).val(currentTime.toString());
//                                 var timeStampInputSelector = strTIME.slice(1)
//                                 const timeStampInput = document.getElementById(timeStampInputSelector)
//                                 const changeEvent = new Event("change", { bubbles: true });
//                                 timeStampInput.dispatchEvent(changeEvent);
//
//                             });
//                             checkEditDeviceConditions();
//                             $(".RemoveAttrEdit").hide();
//                             $(".Hidden_insert").hide();
//                             $('.Select_onlyread').prop('disabled', true);
//                             $('.Input_onlyread').attr('readonly', true);
//                             $('#NewValuesInputConfirmButton').click(function () {
//                                 if (strTIME != null && (old_value[strTIME.substr(6, strTIME.length)] == $(strTIME).val())) {
//                                     // if(old_value[strTIME.substr(6, strTIME.length)]==$(strTIME).val()){
//                                     $('#ValuesINPUT').hide();
//                                     $("#GETimeStamp").hide();
//                                     $("#InsertModalStatus").show();
//                                     // $("#InsertModalStatus").html("You must insert a valid time!");
//                                     $("#InsertModalStatus").html('<br><br>' + "You must insert a valid time!");
//                                     $('#NewValuesInputConfirmButton').hide();
//                                     //  }
//                                 } else {
//                                     var pay_new_data = CreateJsonNewValue(mydata['content'], NameAttrUp, old_value);
//                                     $("#NoMobile").hide();
//                                     $('#ValuesINPUT').hide();
//                                     $("#InsertDataDeviceLoadingIcon").show();
//                                     $.ajax({
//                                         url: "../api/device.php",
//                                         data: {
//                                             action: "Insert_Value",
//                                             id: Nid,
//                                             type: Ntype,
//                                             contextbroker: Ncb,
//                                             token: sessionToken,
//                                             service: Nserv,
//                                             servicePath: NservPath,
//                                             version: "v2",
//                                             payload: JSON.stringify(pay_new_data)
//                                         },
//                                         type: "POST",
//                                         async: true,
//                                         dataType: 'json',
//                                         success: function (mydata)
//                                         {
//                                             $('#ValuesINPUT').hide();
//                                             $('#InsertDataDeviceLoadingIcon').hide();
//                                             console.log(mydata);
//                                             console.log("Values updated");
//                                             $("#InsertModalStatus").html('<br><br>' + Nid + "'s value updates! ");
//                                             $("#NOMob").hide();
//                                             $("#editLatLongValue").hide();
//                                             $("#GETimeStamp").hide();
//                                             $("#InsertModalStatus").show();
//                                             $('#NewValuesInputConfirmButton').hide();
//                                             document.getElementById('editLatLongValue').innerHTML = "";
//                                             document.getElementById('ValuesINPUT').innerHTML = "";
//                                         },
//                                         error: function (data)
//                                         {
//                                             $('#InsertDataDeviceLoadingIcon').hide();
//                                             console.log("Insert values pool KO");
//                                             console.log((data));
//                                             $('#ValuesINPUT').hide();
//                                             $("#InsertModalStatus").html(data.responseText);
//                                             $("#InsertModalStatus").show();
//                                         }
//                                     });
//                                 }
//                             });
//                         },
//                         error: function (data)
//                         {
//                             $('#InsertDataDeviceLoadingIcon').hide();
//                             $('#ValuesINPUT').hide();
//                             $("#InsertModalStatus").html(data.responseText);
//                             $("#InsertModalStatus").show();
//                             console.log("Get values pool KO");
//                             console.log(JSON.stringify(data));
//                             alert("Error in reading data from the database<br/> Please get in touch with the Snap4city Administrator");
//                         }
//                     });
//                 }
//             });
//             $('a[href=#editGeoPositionTabDeviceNewValue]').click();
//             $('#InsertDataDeviceLoadingIcon').hide();
//             // $('#InsertDataDeviceLoadingIcon').show();
//         },
//         error: function (data)
//         {
//             $('#InsertDataDeviceLoadingIcon').hide();
//             console.log("Insert values pool KO");
//             console.log((data));
//             $('#ValuesINPUT').hide();
//             $("#InsertModalStatus").html("<br/><br/>" + data.responseText);
//             $("#InsertModalStatus").show();
//             $('#LoadingGif').hide();
//             $('#GETimeStamp').hide();
//             $('#inputLatitudeDeviceValue').hide();
//             $('#InsertDataDeviceLoadingIcon').hide();
//             $('#NOMob').hide();
//             $('#editLatLongValue').hide();
//             $('#Mtab').hide();
//             $('#Itab').hide();
//             document.getElementById('NewValuesInputConfirmButton').style.display = 'none';
//         }
//     });
//
//     //this should handle the bug about data persisting and not being cleared between insertions when the user doesn't reload the page
//     $("#newValuesInputCancelButton").click(function() {
//       $(".modalInputTxt").val("");
//     });
//
// }

//end of fetch function

// function disableInput(id) {
//     InputToDisable = "Value" + id.substring(9, id.length);
//     Check_okbutton = "#access-code-error" + id.substring(9, id.length);
//     if (document.getElementById(id).checked == false) {
//         document.getElementById(InputToDisable).readOnly = true;
//         if ($(Check_okbutton).is(":visible")) {
//             document.getElementById('NewValuesInputConfirmButton').disabled = false;
//         }
//     } else if (document.getElementById(id).checked == true) {
//         document.getElementById(InputToDisable).readOnly = false;
//         if ($(Check_okbutton).is(":visible")) {
//             document.getElementById('NewValuesInputConfirmButton').disabled = true;
//         }
//     }
// }

// function CreateJsonNewValue(someData, NameAttrUp, old) {
// //console.log(new_co);
//     var attr = {};
//     var a = "";
//     for (var i in someData) {
//         if (document.getElementById(NameAttrUp[i]).checked == true) {
//             a = document.getElementById('Value' + NameAttrUp[i].substring(9, NameAttrUp[i].length)).value;
//         } else {
//             a = old[NameAttrUp[i].substring(9, NameAttrUp[i].length)];
//         }
//         switch (someData[i].data_type) {
//             case "float" || "integer" :
//                 a = parseFloat(a);
//                 break;
//             case "binary":
//                 a = parseInt(a).toString(2);
//                 break;
//             case "boolean" :
//             case "switch" :
//             case "button":
//                 a = Boolean(a);
//                 break;
//             case "date":
//             case "datatime":
//             case "time":
//             case "timestamp":
//                 a = toISOString(a);
//                 break;
//             case "json":
//             case "collection":
//             case "set":
//             case "vector":
//             case "shape" :
//             case "wkt" :
//                 a = JSON.parse(a);
//                 break;
//             case "xlm":
//             case "string":
//                 // a = parseFromString(a, "text/xml");
//                 break;
//         }
//
//         attr['' + someData[i].value_name + ''] = {"value": a, "type": someData[i].data_type};
//     }
//     if (!!document.getElementById("inputLatitudeDeviceValue").value) {
//         attr["latitude"] = {"value": document.getElementById("inputLatitudeDeviceValue").value, "type": "float"};
//         attr["longitude"] = {"value": document.getElementById("inputLongitudeDeviceValue").value, "type": "float"};
//     }
// // console.log(attr);
//     return attr;
// }

// function exportJsonDevice(name,model, devicetype, frequency, kind, contextbroker, protocol, format,latitude,longitude, macaddress, producer, subnature, DeviceStaticAttributes, service, servicePath) {
// console.log(atob(DeviceStaticAttributes));
// var DeviceAttributes;
//     $.ajax({
//         url: "../api/device.php",
//         data: {
//             action: "get_device_attributes",
//             id: name,
//             contextbroker: contextbroker,
//             token: sessionToken,
//             service: service,
//             servicePath: servicePath
//         },
//         type: "POST",
//         async: false,
//         dataType: 'json',
//         success: function (mydata)
//         {
//             DeviceAttributes=mydata['content']
//         },
//         error: function (data)
//         {
//             console.log("Get values pool KO");
//             console.log(JSON.stringify(data));
//             alert("Error in reading data from the database<br/> Please get in touch with the Snap4city Administrator");
//         }
//     });
//
//     var obj = {
//         name: name,
//         model: model,
//         device_type: devicetype,
//         frequency: frequency,
//         kind: kind,
//         contextbroker: contextbroker,
//         protocol: protocol,
//         format: format,
//         latitude: latitude,
//         longitude: longitude,
//         mac_address: macaddress,
//         producer: producer,
//         subnature: subnature,
//         static_attributes: atob(DeviceStaticAttributes).replace(/"/g, "\"\""),
//         service: service,
//         service_path: servicePath,
//         deviceattributes: DeviceAttributes
//     };
//
//     if (servicePath === "bnVsbA==")
//         obj.service_path = "";
//     else
//         obj.service_path = "\"" + atob(servicePath).replace(/"/g, "\"\"") + "\"";
//
//     var element = document.createElement('a');
//     var data = "text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(obj));
//
//     element.setAttribute("href", "data:" + data);
//     element.setAttribute('download', name + "-device.json");
//     document.body.appendChild(element);
//     element.click();
//     document.body.removeChild(element);
//
//
//
// }

function populateDropdownDevice(){

    // Get the Select2 dropdown element
    var select2Dropdown = $('#inputNameDevice');

    // //Initialize Select2 for the dropdown
    select2Dropdown.select2({
        minimumInputLength: 1
    });
    for(let i=0;i< certifiedDevices.length;i++){
        select2Dropdown.append(new Option(certifiedDevices[i], certifiedDevices[i]));
    }
}


function showAddDeviceBCModal()
{

    addDeviceConditionsArrayBC['inputNameDevice'] = false;
    addDeviceConditionsArrayBC['inputFromDateBC'] = false;


    $("#addNewDeviceConfirmBtn").attr("disabled", true);


    $("#addInfoTabDevice #inputNameDevice").on('input', checkDeviceNameBC);
    $("#addInfoTabDevice #inputNameDevice").on('input', checkAddVerification);

    $("#inputFromDateBC").on('input', checkDeviceBCfromDate);
    $("#inputFromDateBC").on('change', checkDeviceBCfromDate);
    $("#inputFromDateBC").on('input', checkAddVerification);
    $("#inputFromDateBC").on('change', checkAddVerification);

    $("#inputToDateBC").on('input', checkDeviceBCtoDate);
    $("#inputToDateBC").on('change', checkDeviceBCtoDate);
    $("#inputToDateBC").on('input', checkAddVerification);
    $("#inputToDateBC").on('change', checkAddVerification);

    //$("#KeyTwoDeviceUser").on('input', UserKey);
    //$("#KeyTwoDeviceUser").on('input', checkAddMyDeviceConditions);


    checkDeviceNameBC();
    checkDeviceBCfromDate();
    checkDeviceBCtoDate();
    checkAddVerification();

    $("#addDeviceModal").modal('show');

}


function checkDeviceNameBC()
{
    var message = null;
    var regex=/[^a-z0-9:_-]/gi;

    if ( !$("#addInfoTabDevice #inputNameDevice").val() || $("#addInfoTabDevice #inputNameDevice").val().length === 0)
    {
        $("#inputNameDeviceMsg").css("color", "red");
        message = 'Device Identifier is mandatory';
        addDeviceConditionsArrayBC['inputNameDevice'] = false;
    }
    else if($("#addInfoTabDevice #inputNameDevice").val().length < 5)
    {
        $("#inputNameDeviceMsg").css("color", "red");
        message = 'Device Identifier (at least 5 chars long)';
        addDeviceConditionsArrayBC['inputNameDevice'] = false;
    }
    else if(regex.test($("#addInfoTabDevice #inputNameDevice").val()))
    {
        $("#inputNameDeviceMsg").css("color", "red");
        message = 'No special characters are allowed in Device Identifier';
        addDeviceConditionsArrayBC['inputNameDevice'] = false;
    }
    else
    {

        $("#inputNameDeviceMsg").css("color", "#337ab7");
        message = 'Ok';
        addDeviceConditionsArrayBC['inputNameDevice'] = true;

    }
    console.log("aa"+addDeviceConditionsArrayBC['inputNameDevice'])
    $("#inputNameDeviceMsg").html(message);
}

function checkDeviceBCfromDate()
{
    var message = null;
    var regex=/[^a-z0-9_-]/gi;
    console.log("date:")
    console.log(Date.parse($("#addInfoTabDevice #inputFromDateBC").val()))

    if ( !$("#addInfoTabDevice #inputFromDateBC").val() || $("#addInfoTabDevice #inputFromDateBC").val().length === 0)
    {
        message = 'A start date is mandatory';
        $("#inputFromDateBCMsg").css("color", "red");
        addDeviceConditionsArrayBC['inputFromDateBC'] = false;
    }
    else if ( regex.test($("#addInfoTabDevice #inputFromDateBC").val()) )
    {
        $("#inputFromDateBCMsg").css("color", "red");
        message = 'No special characters are allowed';
        addDeviceConditionsArrayBC['inputFromDateBC'] = false;
    }
    else if ($("#addInfoTabDevice #inputFromDateBC").val().indexOf(' ')>-1)
        {
            message = 'Device Type cannot contains blank space';
            addDeviceConditionsArrayBC['inputFromDateBC'] = false;
            $("#inputFromDateBCMsg").css("color", "red");
        }
    else if (isNaN(Date.parse($("#addInfoTabDevice #inputFromDateBC").val())))
    {
        $("#inputFromDateBCMsg").css("color", "red");
        message = 'Invalid date format';
        addDeviceConditionsArrayBC['inputFromDateBC'] = false;
    }
    else
        {
            message = 'Ok';
            addDeviceConditionsArrayBC['inputFromDateBC'] = true;
            $("#inputFromDateBCMsg").css("color", "#337ab7");
        }

    console.log("bb" + addDeviceConditionsArrayBC['inputFromDateBC'])
    $("#inputFromDateBCMsg").html(message);
}

function checkDeviceBCtoDate()
{
    var message = null;
    var regex=/[^a-z0-9_-]/gi;
    if ( !$("#addInfoTabDevice #inputToDateBC").val() || $("#addInfoTabDevice #inputToDateBC").val().length === 0)
    {
        $("#inputToDateBCMsg").css("color", "red");
        message = 'An end date is mandatory';
        addDeviceConditionsArrayBC['inputToDateBC'] = false;
    }
    else if ( regex.test($("#addInfoTabDevice #inputToDateBC").val()) )
    {
        $("#inputToDateBCMsg").css("color", "red");
        message = 'No special characters are allowed';
        editDeviceConditionsArrayBC['inputToDateBC'] = false;
    }
    else if ($("#addInfoTabDevice #inputToDateBC").val().indexOf(' ')>-1)
    {
        message = 'End date cannot contain blank space';
        addDeviceConditionsArrayBC['inputToDateBC'] = false;
        $("#inputToDateBCMsg").css("color", "red");
    }
    else if (isNaN(Date.parse($("#addInfoTabDevice #inputToDateBC").val())))
    {
        $("#inputToDateBCMsg").css("color", "red");
        message = 'Invalid date format';
        addDeviceConditionsArrayBC['inputToDateBC'] = false;
    }
    else
    {
        message = 'Ok';
        addDeviceConditionsArrayBC['inputToDateBC'] = true;
        $("#inputToDateBCMsg").css("color", "#337ab7");
    }

    console.log("bb" + addDeviceConditionsArrayBC['inputToDateBC'])
    $("#inputToDateBCMsg").html(message);
}


function checkAddVerification(){
    var enableButton = true;
    for(var key in addDeviceConditionsArrayBC)
    {

        if(addDeviceConditionsArrayBC[key] === false)
        {
            console.log("array: "+ key)
            enableButton = false;
            break;
        }
    }
    if (Date.parse($("#addInfoTabDevice #inputToDateBC").val()) < Date.parse($("#addInfoTabDevice #inputFromDateBC").val()))
    {
        enableButton = false;
        $("#inputToDateBCMsg").css("color", "red");
        message = '"To Date" must be after "From Date"';
        $("#inputToDateBCMsg").html(message);
    }else if (!isNaN(Date.parse($("#addInfoTabDevice #inputToDateBC").val())))
    {
        $("#inputToDateBCMsg").css("color", "#337ab7");
        message = 'ok';
        $("#inputToDateBCMsg").html(message);
    }
    if(enableButton)
    {
        console.log("tutto ok")
        $("#addNewDeviceConfirmBtn").attr("disabled", false);
        $("#addNewDeviceConfirmBtn").trigger("change")
        //$("#addMyNewDeviceConfirmBtn").attr("disabled", false); this will be in action for mydevice
    }
    else
    {
        $("#addNewDeviceConfirmBtn").attr("disabled", true);
        //$("#addMyNewDeviceConfirmBtn").attr("disabled", false); this will be in action for the mydevice
    }
}



///END

$(document).ready(function () {
    $('#addDeviceBCBtn').click(function () {
        checkAddVerification();
    });
    $("#ShowOnlyDelegated").click(function () {
        fetch_data(true, 'delegated');
    });
    $("#ShowOnlyPublic").click(function () {
        fetch_data(true, 'public');
    });
    $("#ShowOnlyOwn").click(function () {
        fetch_data(true, 'own');
    });
    $("#ShowAll").click(function () {
        fetch_data(true);
    });
//fetch_data function will load the device table 	
    fetch_data(false, 'own');
//detail control for device dataTable
    var detailRows = [];
    $('#devicesTable tbody').on('click', 'td.details-control', function () {
        var tr = $(this).closest('tr');
        var tdi = tr.find("i.fa");
        var row = dataTable.row(tr);
        if (row.child.isShown()) {
            // This row is already open - close it
            row.child.hide();
            tr.removeClass('shown');
            tdi.first().removeClass('fa-minus-square');
            tdi.first().addClass('fa-plus-square');
        } else {
            // Open this row
            row.child(format(row.data())).show();
            tr.addClass('shown');
            tdi.first().removeClass('fa-plus-square');
            tdi.first().addClass('fa-minus-square');
        }

    });
    //end of detail control for device dataTable 
    // handle the click on "Add Device" button

    //Start Related to Add Device
    $("#addDeviceBCBtn").off("click");
    $("#addDeviceBCBtn").click(function () {
        $('#addNewDeviceCancelBtn').show();
        $('#addNewDeviceConfirmBtn').show();

        //document.getElementById('addlistAttributes').innerHTML = "";
        $('.modalInputTxt').attr('readonly', false);
        //select custom model
        $("#addDeviceModalTabs").show();
        $('.nav-tabs a[href="#addInfoTabDevice"]').tab('show');
        $("#addDeviceModalBody").show();
        $('#addDeviceModal div.modalCell').show();
        $("#addDeviceModalFooter").show();
        $("#addAttrBtn").show();
        $("#addDeviceOkMsg").hide();
        $("#addDeviceOkIcon").hide();
        $("#addDeviceKoMsg").hide();
        $("#addDeviceKoIcon").hide();
        $('#addDeviceLoadingMsg').hide();
        $('#addDeviceLoadingIcon').hide();
        $("#addDeviceLoadingMsg").hide();
        $("#addDeviceLoadingIcon").hide();
        $("#addDeviceOkIcon").hide();
        $("#addDeviceKoMsg").hide();
        $("#addDeviceKoIcon").hide();
        $("#addNewDeviceGenerateKeyBtn").hide();
        $("#addDeviceModalBody").show();
        $("#addDeviceModalTabs").show();
        $("#addDeviceModalFooter").show();
        $("#addNewDeviceGenerateKeyBtn").show();
        showAddDeviceBCModal();
        $("#selectContextBroker").change();
    });
    // Add lines related to attributes

    $("#addSchemaTabDevice").off("click");
    $("#addSchemaTabDevice").on('click keyup', function () {
        //console.log("#addSchemaTabDevice");

        //checkAtlistOneAttribute();
        $("#addSchemaTabDevice #addlistAttributes .row input:even").each(function () {
            checkValueName($(this));
        });
        checkAddDeviceConditions();
    });

    
//view details of device
    $('#devicesTable tbody').on('click', 'button.viewDashBtn', function () {
        get_form(true);
        $("#editDeviceConfirmBtn").hide();
        $("#addAttrMBtn").hide();
        $("#editDeviceModal").modal('show');
        $("#editDeviceModalLabel").html("View device -  " + $(this).attr("data-id"));
        // show GeoPostion Tab on view Device Button 

        var contextbroker = $(this).attr('data-contextBroker');
        gb_old_cb = contextbroker;
        var type = $(this).attr('data-devicetype');
        var dev_organization = $(this).attr('data-organization');
        var kind = $(this).attr('data-kind');
        var uri = $(this).attr('data-uri');
        var protocol = $(this).attr('data-protocol');
        var format = $(this).attr('data-format');
        var macaddress = $(this).attr('data-macaddress');
        var model = $(this).attr('data-model');
        var producer = $(this).attr('data-producer');
        var latitude = $(this).attr('data-latitude');
        var longitude = $(this).attr('data-longitude');
        var frequency = $(this).attr('data-frequency');
        var visibility = $(this).attr('data-visibility');
        var key1 = $(this).attr('data-k1');
        var key2 = $(this).attr('data-k2');
        var gtw_type = $(this).attr('data-edgegateway_type');
        var gtw_uri = $(this).attr('data-edgegateway_uri');
        var subnature = $(this).attr('data-subnature');
        fillMultiTenancyFormSection($(this).attr('data-service'), $(this).attr('data-servicePath'), contextbroker, 'device');
        var serv = $(this).attr('data-service');
        var servP = $(this).attr('data-servicePath');
        //console.log(key1 + key2);

        $("#editDeviceGenerateKeyBtn").hide();
        $('#inputNameDeviceM').val($(this).attr('data-id'));
        $('#inputOrganizationDeviceM').val($(this).attr('data-organization'));
        $('#selectContextBrokerM').val(contextbroker);
        $('#inputTypeDeviceM').val(type);
        $('#selectKindDeviceM').val(kind);
        $('#inputUriDeviceM').val(uri);
        $('#selectProtocolDeviceM').val(protocol);
        $('#selectFormatDeviceM').val(format);
        //$('#createdDateDeviceM').val($(this).parents('tr').attr('data-created'));
        $('#inputMacDeviceM').val(macaddress);
        $('#selectModelDeviceM').val(model);
        $('#inputProducerDeviceM').val(producer);
        $('#inputLatitudeDeviceM').val(latitude);
        $('#inputLongitudeDeviceM').val(longitude);
        $('#inputFrequencyDeviceM').val(frequency);
        $('#selectVisibilityDeviceM').val(visibility);
        $('#KeyOneDeviceUserM').val(key1);
        $('#KeyTwoDeviceUserM').val(key2);
        $('#selectEdgeGatewayTypeM').val(gtw_type);
        $('#inputEdgeGatewayUriM').val(gtw_uri);
        $('#selectSubnatureM').val(subnature);
        subnatureChanged("view", JSON.parse(atob($(this).attr("data-static-attributes"))));
        //$('#removeCBServiceBtn').hide();

        $('#addNewStaticBtnM').hide();
        $('#EStatus').hide();
        $('a[data-toggle="tab"]').off('shown.bs.tab').on('shown.bs.tab', function (e) {
            var target = $(e.target).attr("href");
            if ((target == '#editGeoPositionTabDevice')) {
                //console.log("Elf : EditDeviceMap");
                drawMap1(latitude, longitude, 3);
            } else if ((target == '#editSchemaTabDevice')) {
                document.getElementById('editlistAttributes').innerHTML = "";
                $('#editDeviceLoadingIcon').show();
                //console.log("Elf : Values ok ");
                $.ajax({
                    url: "../api/device.php",
                    data: {
                        action: "get_device_attributes",
                        id: document.getElementById('inputNameDeviceM').value,
                        contextbroker: document.getElementById('selectContextBrokerM').value,
                        token: sessionToken,
                        service: serv,
                        //$(this).attr('data-service'),
                        servicePath: servP
                                //$(this).attr('data-servicePath')
                    },
                    type: "POST",
                    async: true,
                    dataType: 'json',
                    success: function (mydata)
                    {
                        var row = null;
                        $("#editUserPoolsTable tbody").empty();
                        myattributes = mydata['content'];
                        content = "";
                        k = 0;
                        //console.log("adding new attribute for id:"+id);
                        while (k < myattributes.length)
                        {
                            // console.log(k); 
                            content = drawAttributeMenu(myattributes[k].value_name,
                                    myattributes[k].data_type, myattributes[k].value_type, myattributes[k].editable, myattributes[k].value_unit, myattributes[k].healthiness_criteria,
                                    myattributes[k].healthiness_value, myattributes[k].value_name, 'editlistAttributes', indexValues);
                            indexValues = indexValues + 1;
                            k++;
                            $('#editlistAttributes').append(content);

                        }

                        $("#editSchemaTabDevice #editlistAttributes .row input:even").each(function () {
                            checkEditValueName($(this));
                        });
                        checkEditDeviceConditions();
                        $(".RemoveAttrEdit").hide();
                        $('.Select_onlyread').prop('disabled', true);
                        $('.Input_onlyread').attr('readonly', true);
                        $('#editDeviceLoadingIcon').hide();
                    },
                    error: function (data)
                    {
                        console.log("Get values pool KO");
                        console.log(JSON.stringify(data));
                        alert("Error in reading data from the database<br/> Please get in touch with the Snap4city Administrator");
                        // $("#editDeviceModal").modal('hide');


                    }
                });
            }

        });
    });
//////////////////////////////  //
//     //Edit button in dataTable
//     $('#devicesTable tbody').on('click', 'button.editDashBtn', function () {
//         mydata = {action: "get_all_device", strat_time: '2022-04-13 2012:15:18', end_time: '2022-04-13 2012:25:18', token: sessionToken, no_columns: ["position", "d.visibility", "status1", "edit", "delete", "map", "check"]};
//         get_form(false);
//         $('#EStatus').show();
//         //$("#editDeviceModalFooter").show();
//         //
//
//         $("#editDeviceModalLabel").html("Edit device -  " + $(this).attr("data-id"));
//         //var id =$(this).attr('data-id');
//
//         if (currentEditId !== $(this).attr('data-id')) {
//             //if the user changed the device to edit, clean the list of value and update the currentEditId
//             document.getElementById('editlistAttributes').innerHTML = "";
//             document.getElementById('addlistAttributesM').innerHTML = "";
//             document.getElementById('deletedAttributes').innerHTML = "";
//             //currentEditId=id;
//             //$('#editLatLong').show();
//             var contextbroker = $(this).attr('data-contextBroker');
//             gb_old_cb = contextbroker;
//             var type = $(this).attr('data-devicetype');
//             var dev_organization = $(this).attr('data-organization');
//             var kind = $(this).attr('data-kind');
//             var uri = $(this).attr('data-uri');
//             var protocol = $(this).attr('data-protocol');
//             var format = $(this).attr('data-format');
//             var macaddress = $(this).attr('data-macaddress');
//             var model = $(this).attr('data-model');
//             var producer = $(this).attr('data-producer');
//             var latitude = $(this).attr('data-latitude');
//             var longitude = $(this).attr('data-longitude');
//             var frequency = $(this).attr('data-frequency');
//             var visibility = $(this).attr('data-visibility');
//             var key1 = $(this).attr('data-k1');
//             var key2 = $(this).attr('data-k2');
//             var gtw_type = $(this).attr('data-edgegateway_type');
//             var gtw_uri = $(this).attr('data-edgegateway_uri');
//             var subnature = $(this).attr('data-subnature');
//             fillMultiTenancyFormSection($(this).attr('data-service'), $(this).attr('data-servicePath'), contextbroker, 'device');
//             //console.log(key1 + key2);
//
//             $("#editDeviceGenerateKeyBtn").show();
//             $('#inputNameDeviceM').val($(this).attr('data-id'));
//             $('#inputOrganizationDeviceM').val($(this).attr('data-organization'));
//             $('#selectContextBrokerM').val(contextbroker);
//             $('#inputTypeDeviceM').val(type);
//             $('#selectKindDeviceM').val(kind);
//             $('#inputUriDeviceM').val(uri);
//             $('#selectProtocolDeviceM').val(protocol);
//             $('#selectFormatDeviceM').val(format);
//             //$('#createdDateDeviceM').val($(this).parents('tr').attr('data-created'));
//             $('#inputMacDeviceM').val(macaddress);
//             $('#selectModelDeviceM').val(model);
//             $('#inputProducerDeviceM').val(producer);
//             $('#inputLatitudeDeviceM').val(latitude);
//             $('#inputLongitudeDeviceM').val(longitude);
//             $('#inputFrequencyDeviceM').val(frequency);
//             $('#selectVisibilityDeviceM').val(visibility);
//             $('#KeyOneDeviceUserM').val(key1);
//             $('#KeyTwoDeviceUserM').val(key2);
//             $('#selectEdgeGatewayTypeM').val(gtw_type);
//             $('#inputEdgeGatewayUriM').val(gtw_uri);
//             $('#selectSubnatureM').val(subnature);
//             $('#selectSubnatureM').trigger('change');
//             subnatureChanged(true, JSON.parse(atob($(this).attr("data-static-attributes"))));
//             $('a[data-toggle="tab"]').off('shown.bs.tab').on('shown.bs.tab', function (e) {
//                 var target = $(e.target).attr("href");
//                 if ((target == '#editGeoPositionTabDevice')) {
//                     //console.log("Elf : EditDeviceMap");
//                     var latitude = document.getElementById('inputLatitudeDeviceM').value;
//                     var longitude = document.getElementById('inputLongitudeDeviceM').value;
//                     drawMap1(latitude, longitude, 1);
//                 } else if ((target == '#editStatusTabDevice')) {
//
//                     var id = document.getElementById('inputNameDeviceM').value;
//                     var contextbroker = document.getElementById('selectContextBrokerM').value;
//                     var type = document.getElementById('inputTypeDeviceM').value;
//                     var kind = document.getElementById('selectKindDeviceM').value;
//                     var latitude = document.getElementById('inputLatitudeDeviceM').value;
//                     var longitude = document.getElementById('inputLongitudeDeviceM').value;
//                     var protocol = document.getElementById('selectProtocolDeviceM').value;
//                     if (id == null || id == "") {
//                         var idNote = ("\n id not specified");
//                     } else {
//                         idNote = "&#10004;";
//                     }
//                     if (contextbroker == null || contextbroker == "") {
//                         var contextbrokerNote = ("cb not specified");
//                     } else {
//                         contextbrokerNote = "&#10004;";
//                     }
//                     if (type == null || type == "") {
//                         var typeNote = ("type not specified");
//                     } else {
//                         typeNote = "&#10004;";
//                     }
//                     if (!(kind == "sensor" || kind == "actuator")) {
//                         var kindNote = ("\n kind not specified");
//                     } else {
//                         kindNote = "&#10004;";
//                     }
//                     if ((latitude < -90 && latitude > 90) || (latitude == "" || latitude == null)) {
//                         var latitudeNote = ("\n latitude not correct ");
//                     } else {
//                         latitudeNote = "&#10004;";
//                     }
//                     if ((longitude < -180 && longitude > 180) || (longitude == "" || longitude == null)) {
//                         var longitudeNote = ("\n longitude not correct ");
//                     } else {
//                         longitudeNote = "&#10004;";
//                     }
//                     if (!(protocol == "ngsi" || protocol == "mqtt" || protocol == "amqp" || protocol == "ngsi w/MultiService")) {
//                         var protocolNote = ("protocol not correct ");
//                     } else {
//                         protocolNote = "&#10004;";
//                     }
//
//                     //console.log(id + contextbroker + type + kind + latitude + longitude + protocol);
//
//                     if ((idNote == "&#10004;") && (contextbrokerNote == "&#10004;") && (typeNote == "&#10004;") && (kindNote == "&#10004;") && (latitudeNote == "&#10004;") && (longitudeNote == "&#10004;") && (protocolNote == "&#10004;")) {
//                         var statusNote = "<button class=\"btn btn-success btn-round\"></button>";
//                     } else {
//                         statusNote = "<button class=\"btn btn-danger btn-round\"></button>";
//                     }
//
//                     var x = inputPropertiesDeviceMMsg.innerHTML;
//                     var div = document.createElement("div");
//                     //console.log("IPDMM:" + x);
//
//                     if (x == "&nbsp;") {
//                     } else {
//                         inputPropertiesDeviceMMsg.innerHTML = "";
//                     }
//
//                     div.innerHTML = ("<div style=\"border:3px solid blue;\" >" +
//                             "<h2>Device Status</h2>" +
//                             "<table class=\"table\"><thead><tr><th>Property Status</th><th> checked</th></tr></thead>" +
//                             "<tbody><tr><td>id</td><td>" + idNote + "</td></tr>" +
//                             "<tr><td>Contextbroker</td><td>" + contextbrokerNote + "</td></tr>" +
//                             "<tr><td>Type</td><td>" + typeNote + "</td></tr>" +
//                             "<tr><td>Kind</td><td>" + kindNote + " </td></tr>" +
//                             "<tr><td>Protocol</td><td>" + protocolNote + "</td></tr>" +
//                             "<tr><td>Latitude</td><td>" + latitudeNote + " </td></tr>" +
//                             "<tr><td>Longitude</td><td>" + longitudeNote + "</td></tr>" +
//                             "<tr><td>Overall Status</td><td>" + statusNote + "</td></tr>" +
//                             "</tbody></table></div>");
//                     inputPropertiesDeviceMMsg.appendChild(div);
//                 }
//             }
//             );
//             //UserEditKey();
//             checkEditDeviceConditions();
//             $.ajax({
//                 url: "../api/device.php",
//                 data: {
//                     action: "get_device_attributes",
//                     id: $(this).attr("data-id"),
//                     contextbroker: $(this).attr("data-contextBroker"),
//                     token: sessionToken,
//                     service: $(this).attr('data-service'),
//                     servicePath: $(this).attr('data-servicePath')
//                 },
//                 type: "POST",
//                 async: true,
//                 dataType: 'json',
//                 success: function (mydata)
//                 {
//                     var row = null;
//                     $("#editUserPoolsTable tbody").empty();
//                     myattributes = mydata['content'];
//                     content = "";
//                     k = 0;
//                     //console.log("adding new attribute for id:"+id);
//                     while (k < myattributes.length)
//                     {
//                         // console.log(k);
//                         content = drawAttributeMenu(myattributes[k].value_name,
//                                 myattributes[k].data_type, myattributes[k].value_type, myattributes[k].editable, myattributes[k].value_unit, myattributes[k].healthiness_criteria,
//                                 myattributes[k].healthiness_value, myattributes[k].value_name, 'editlistAttributes', indexValues);
//                         indexValues = indexValues + 1;
//
//
//                         k++;
//                         $('#editlistAttributes').append(content);
//                     }
//
//                     $("#editSchemaTabDevice #editlistAttributes .row input.valueName").each(function () {
//                         checkEditValueName($(this));
//                     });
//                     checkEditDeviceConditions();
//                 },
//                 error: function (data)
//                 {
//                     console.log("Get values pool KO");
//                     console.log(JSON.stringify(data));
//                     alert("Error in reading data from the database<br/> Please get in touch with the Snap4city Administrator");
//                     $('#inputNameDeviceM').val("");
//                     $('#inputOrganizationDeviceM').val("");
//                     $('#selectContextBrokerM').val("");
//                     $('#inputTypeDeviceM').val("");
//                     //$('#selectKindDeviceM').val("");
//                     $('#inputUriDeviceM').val("");
//                     //$('#selectProtocolDeviceM').val("");
//                     //$('#selectFormatDeviceM').val("");
//                     $('#createdDateDeviceM').val("");
//                     $('#inputMacDeviceM').val("");
//                     $('#selectModelDeviceM').val("");
//                     $('#inputProducerDeviceM').val("");
//                     $('#inputLatitudeDeviceM').val("");
//                     $('#inputLongitudeDeviceM').val("");
//                     $('#inputFrequencyDeviceM').val("");
//                     $('#selectVisibilityDeviceM').val("");
//                     $('#editlistAttributes').html("");
//                     $('#KeyOneDeviceUserM').val("");
//                     $('#KeyTwoDeviceUserM').val("");
//                     $('#selectSubnatureM').val("");
//                     // $("#editDeviceModal").modal('hide');
//
//                 }
//             });
//         }
//         showEditDeviceModal();
//     });
//     //Edit button hover - needs to be checked
//     $('#devicesTable tbody').on('hover', 'button.editDashBtn', function () {
//         //$('#devicesTable tbody button.editDashBtn').off('hover')
//         //$('#devicesTable tbody button.editDashBtn').hover(function(){
//         $(this).css('background', '#ffcc00');
//         $(this).parents('tr').find('td').eq(1).css('background', '#ffcc00');
//     },
//             function () {
//                 $(this).css('background', 'rgb(69, 183, 175)');
//                 $(this).parents('tr').find('td').eq(1).css('background', $(this).parents('td').css('background'));
//             });
//End Related to Edit Device






    // START SAVE AS FUNCTION


//END SAVE AS
//Start Related to Delete Device

    // Delete lines related to attributes 
    $("#attrNameDelbtn").off("click");
    $("#attrNameDelbtn").on("click", function () {
        //console.log("#attrNameDelbtn");	
        $(this).parent('tr').remove();
    });
    //Delete device button 

    $('#devicesTable tbody').on('click', 'button.delDashBtn', function () {
        //		console.log($(this));

        var id = $(this).attr('data-id');
        var contextbroker = $(this).attr('data-contextbroker');
        var dev_organization = $(this).attr('data-organization');
        var uri = $(this).attr("data-uri");
        var protocol = $(this).attr('data-protocol');
        var service = $(this).attr('data-service');
        var servicePath = $(this).attr('data-servicepath');
        $("#deleteDeviceModal div.modal-body").html('<div class="modalBodyInnerDiv"><span data-id = "' + id + '" data-contextbroker = "' + contextbroker + '" data-organization = "' + dev_organization + '"  data-uri ="' + uri + '" data-service ="' + service + '" data-servicepath="' + servicePath + '" data-protocol="' + protocol + '">Do you want to confirm deletion of device <b>' + id + '</b>?</span></div>');
        $("#deleteDeviceModalInnerDiv1").html('<h5>Device deletion in progress, please wait</h5>');
        $("#deleteDeviceModalInnerDiv2").html('<i class="fa fa-circle-o-notch fa-spin" style="font-size:36px"></i>');
        $("#deleteDeviceModalInnerDiv1").hide();
        $("#deleteDeviceModalInnerDiv2").hide();
        $("#deleteDeviceOkBtn").hide();
        $("#deleteDeviceCancelBtn").show();
        $("#deleteDeviceConfirmBtn").show();
        $("#deleteDeviceModal").modal('show');
    });
    //Delete button hover - needs to be checked
    $('#devicesTable tbody').on('hover', 'button.delDashBtn', function () {
        //$('#devicesTable button.delDashBtn').off('hover');
        //$('#devicesTable button.delDashBtn').hover(function(){
        //console.log($(this));
        $(this).css('background', '#ffcc00');
        $(this).parents('tr').find('td').eq(1).css('background', '#ffcc00');
    },
            function () {
                $(this).css('background', '#e37777');
                $(this).parents('tr').find('td').eq(1).css('background', $(this).parents('td').css('background'));
            });
//End Related to Delete Device

//--------------------- static attribute ADD start

    // $("#addNewStaticBtn").off("click");
    // $("#addNewStaticBtn").click(function () {
    //     createRowElem('', '', currentDictionaryStaticAttribAdd, "addlistStaticAttributes");
    // });
//--------------------- static attribute ADD end
//--------------------- static attribute EDIT start

    // $("#addNewStaticBtnM").off("click");
    // $("#addNewStaticBtnM").click(function () {
    //     createRowElem('', '', currentDictionaryStaticAttribEdit, "editlistStaticAttributes");
    // });
//--------------------- static attribute EDIT end


    // $('#selectSubnature').on('select2:selecting', function (e) {
    //     checkSubnatureChanged($('#selectSubnature'), e.target.value, e.params.args.data.id, e);
    // });
    // $('#selectSubnatureM').on('select2:selecting', function (e) {
    //     checkSubnatureChanged($('#selectSubnatureM'), e.target.value, e.params.args.data.id, e, true);
    // });
    // $('#selectSubnature').on('select2:clearing', function (e) {
    //     checkSubnatureChanged($('#selectSubnature'), e.params.args.data.id, "", e);
    // });
    // $('#selectSubnatureM').on('select2:clearing', function (e) {
    //     checkSubnatureChanged($('#selectSubnatureM'), e.params.args.data.id, "", e, true);
    // });
// Device dataTable table Style 

    $('#devicesTable thead').css("background", "rgba(0, 162, 211, 1)");
    $('#devicesTable thead').css("color", "white");
    $('#devicesTable thead').css("font-size", "1em");
    $('#devicesTable tbody tr').each(function () {
        if ((dataTable.row(this).index()) % 2 !== 0)
        {
            $('#devicesTable tbody').css("background", "rgba(0, 162, 211, 1)");
            //console.log( 'Row index: '+dataTable.row( this ).index() );
            $(this).find('td').eq(0).css("background-color", "rgb(230, 249, 255)");
            $(this).find('td').eq(0).css("border-top", "none");
        } else
        {
            $(this).find('td').eq(0).css("background-color", "white");
            $(this).find('td').eq(0).css("border-top", "none");
        }
    });
    /*$('#devicesTable tbody').on( 'click', 'tr', function () {
     alert( 'Row index: '+dataTable.row( this ).index() );
     });*/

//Display devices on the map 

//Default Title 

    if (titolo_default != "") {
        $('#headerTitleCnt').text(titolo_default);
    }

    if (access_denied != "") {
        alert('You need to log in with the right credentials before to access to this page!');
    }

// SHOW FRAME PARAMETER USE

    if (nascondi == 'hide') {
        $('#mainMenuCnt').hide();
        $('#title_row').hide();
        $('#mainCnt').removeClass('col-md-10');
        $('#mainCnt').addClass('col-md-12');
    }

// SHOW FRAME PARAMETER  

    $('#sessionExpiringPopup').css("top", parseInt($('body').height() - $('#sessionExpiringPopup').height()) + "px");
    $('#sessionExpiringPopup').css("left", parseInt($('body').width() - $('#sessionExpiringPopup').width()) + "px");
    setInterval(function () {
        var now = parseInt(new Date().getTime() / 1000);
        var difference = sessionEndTime - now;
        if (difference === 300)
        {
            $('#sessionExpiringPopupTime').html("5 minutes");
            $('#sessionExpiringPopup').show();
            $('#sessionExpiringPopup').css("opacity", "1");
            setTimeout(function () {
                $('#sessionExpiringPopup').css("opacity", "0");
                setTimeout(function () {
                    $('#sessionExpiringPopup').hide();
                }, 1000);
            }, 4000);
        }
        if (difference === 120)
        {
            $('#sessionExpiringPopupTime').html("2 minutes");
            $('#sessionExpiringPopup').show();
            $('#sessionExpiringPopup').css("opacity", "1");
            setTimeout(function () {
                $('#sessionExpiringPopup').css("opacity", "0");
                setTimeout(function () {
                    $('#sessionExpiringPopup').hide();
                }, 1000);
            }, 4000);
        }

        if ((difference > 0) && (difference <= 60))
        {
            $('#sessionExpiringPopup').show();
            $('#sessionExpiringPopup').css("opacity", "1");
            $('#sessionExpiringPopupTime').html(difference + " seconds");
        }

        if (difference <= 0)
        {
            location.href = "logout.php?sessionExpired=true";
        }
    }, 1000);
    $('#mainContentCnt').height($('#mainMenuCnt').height() - $('#headerTitleCnt').height());
    $(window).resize(function () {
        $('#mainContentCnt').height($('#mainMenuCnt').height() - $('#headerTitleCnt').height());
        if ($(window).width() < 992)
        {
            //$('#devicesTable').bootstrapTable('hideColumn', 'id');
            //$('#devicesTable').bootstrapTable('hideColumn', 'contextBroker');
            //$('#devicesTable').bootstrapTable('hideColumn', 'uri');
            //$('#devicesTable').bootstrapTable('hideColumn', 'protocol');
            //$('#devicesTable').bootstrapTable('hideColumn', 'format');
            //$('#devicesTable').bootstrapTable('hideColumn', 'devicetype');
            //$('#devicesTable').bootstrapTable('hideColumn', 'model');
            //$('#devicesTable').bootstrapTable('hideColumn', 'visibility');
            //$('#devicesTable').bootstrapTable('hideColumn', 'status1');	
            //$('#devicesTable').bootstrapTable('hideColumn', 'type');
        } else
        {
            //$('#devicesTable').bootstrapTable('showColumn', 'id');
            //$('#devicesTable').bootstrapTable('showColumn', 'contextBroker');
            //$('#devicesTable').bootstrapTable('showColumn', 'devicetype');
            //$('#devicesTable').bootstrapTable('showColumn', 'model');
            //$('#devicesTable').bootstrapTable('showColumn', 'visibility');
            //$('#devicesTable').bootstrapTable('showColumn', 'status1');
            //$('#devicesTable').bootstrapTable('showColumn', 'uri');
            //$('#devicesTable').bootstrapTable('showColumn', 'protocol');
            //$('#devicesTable').bootstrapTable('showColumn', 'format');
            //$('#devicesTable').bootstrapTable('showColumn', 'type');
        }
    });
    $("#addMyNewDeviceRow").hide();
    for (var func = 0; func < functionality.length; func++)
    {
        var element = functionality[func];
        if (element.view == "view")
        {
            if (element[loggedRole] == 1)
            {   // console.log(loggedRole + " " + element[loggedRole] + " " + element["class"]); 
                $(element["class"]).show();
            } else
            {
                $(element["class"]).hide();
                // console.log($(element.class));
                //  console.log(loggedRole + " " + element[loggedRole] + " " + element["class"]);
            }
        }
    }
    $('#devicesLink .mainMenuItemCnt').addClass("mainMenuItemCntActive");
    $('#mobMainMenuPortraitCnt #devicesLink .mobMainMenuItemCnt').addClass("mainMenuItemCntActive");
    $('#mobMainMenuLandCnt #devicesLink .mobMainMenuItemCnt').addClass("mainMenuItemCntActive");
//Add MyNewDevice Button  
//     $("#addMyNewDevice").click(function () {
//         //console.log("add new device");
//         $("#displayAllDeviceRow").hide();
//         $("#addMyNewDeviceRow").show();
//         $('#inputNameDeviceUser').val("");
//         $('#inputTypeDeviceUser').val("");
//         $('#inputLatitudeDeviceUser').val("");
//         $('#inputLongitudeDeviceUser').val("");
//         drawMapUser(43.78, 11.23);
//         // showAddDeviceModal();
//     });
// All Device Button 		
    $("#allDevice").click(function () {
        $("#displayAllDeviceRow").show();
        // $("#addDeviceModal").modal('show');
        $("#addMyNewDeviceRow").hide();
    });
    $("#myDevice").click(function () {

        $("#displayAllDeviceRow").show();
        // $("#addDeviceModal").modal('show');
        $("#addMyNewDeviceRow").hide();
    });

//EdgeGateWayType
//     $("#selectEdgeGatewayType").click(function () {
//         checkUri();
//         checkAddDeviceConditions();
//     });


//Error function load attr of model
//     function ErrorManager(data) {
//         console.log("Ko result: " + JSON.stringify(data));
//         $('#addlistAttributes').html("");
//         $('#inputTypeDevice').val("");
//         //$('#selectKindDevice').val("");
//         $('#inputProducerDevice').val("");
//         $('#inputFrequencyDevice').val("600");
//         $('#inputMacDevice').val("");
//         $('#selectContextBroker').val("");
//         //$('#selectProtocolDevice').val("");
//         //$('#selectFormatDevice').val("");
//         alert("An error occured when reading the information about model. <br/> Try again or get in touch with the Snap4City Administrator<br/>");
//     }
//     $("#selectModelDevice").change(function () {
//         var nameOpt = document.getElementById('selectModelDevice').options;
//         var selectednameOpt = document.getElementById('selectModelDevice').selectedIndex;
//         //var ownerSelect = document.getElementById('selectVisibilityDevice').options;
//         //var ownerOpt = document.getElementById('selectVisibilityDevice').selectedIndex;
//         checkModel();
//         //Fatima3
//         if (nameOpt[selectednameOpt].value == 'custom') {
//             $("#selectModelDevice").val('custom');
//             document.getElementById('addlistAttributes').innerHTML = "";
//             $('#selectSubnature').val('');
//             $('#selectSubnature').trigger('change');
//             $('#addNewStaticBtn').hide();
//             removeStaticAttributes();
//             document.getElementById('addlistAttributesMsg').innerHTML = "At least a value needs to be specified";
//             $('#addlistAttributesMsg').show();
//         } else if (nameOpt[selectednameOpt].attributes.data_kind.value == 'NATIVE') {
//             var nameOptValue = nameOpt[selectednameOpt].value;
//
//             LoadAttr('NATIVE', nameOpt, selectednameOpt, nameOptValue, '', '', '');
//         } else if (typeof nameOpt[selectednameOpt].attributes['data-version'] !== 'undefined') {
//             var nameOptValue = nameOpt[selectednameOpt].value.replace('( FIWARE )', '').trim();
//             var version = nameOpt[selectednameOpt].attributes['data-version'].value;
//             var domain = nameOpt[selectednameOpt].attributes['data-domain'].value;
//             var subdomain = nameOpt[selectednameOpt].attributes['data-modelsubdomain'].value;
//             var subnature = nameOpt[selectednameOpt].attributes['data_subnature'].value;
//             $('#inputTypeDevice').val(nameOptValue);
//             LoadAttr('FIWARE', nameOpt, selectednameOpt, nameOptValue, version, domain, subdomain, subnature);
//             $('#addlistAttributesMsg').hide();
//         } else {
//             $("#selectModelDevice").val('');
//             document.getElementById('addlistAttributes').innerHTML = "";
//             document.getElementById('addlistAttributesMsg').innerHTML = "At least a value needs to be specified";
//             $('#addlistAttributesMsg').show();
//         }
//     });
// ADD NEW VERIFICATION

    $('#addNewDeviceConfirmBtn').off("click");
    $('#addNewDeviceConfirmBtn').click(function () {
        $('#addNewDeviceCancelBtn').hide();
        $('#addNewDeviceConfirmBtn').hide();
        $('#addDeviceLoadingMsg').show();
        $('#addDeviceLoadingIcon').show();
        $("#addDeviceModalBody").hide();
        //mynewAttributes = [];
        //var regex = /[^a-z0-9:._-]/gi;
        //var someNameisWrong = false;


        $.ajax({
                url: "../api/blockchainrequests.php",
                data: {
                    action:"insert_new_device",
                    deviceId: $('#inputNameDevice').val(),
                    startDate: $('#inputFromDateBC').val(),
                    endDate: $('#inputToDateBC').val(),
                    token: sessionToken
                },
                type: "POST",
                async: true,
                dataType: "JSON",

                success: function (mydata)
                {
                   console.log(mydata)
                    if (mydata["status"] === 'ok')
                    {
                        $('#addDeviceLoadingMsg').hide();
                        $('#addDeviceLoadingIcon').hide();
                        $("#addDeviceModal").modal('hide');
                        $('#inputNameDevice').val("");
                        $('#inputFromDateBC').val("");
                        $('#inputToDateBC').val("");
                        $("#addDeviceOkModal").modal('show');
                        $("#addDeviceOkModalInnerDiv1").show()
                        $("#addDeviceOkModalInnerDiv1").html('<h5>The device was succesfully added to the queue for verification.</h5><br>');

                    } else if (mydata["status"] === 'ko')
                    {

                        $('#addDeviceLoadingMsg').hide();
                        $('#addDeviceLoadingIcon').hide();
                        $("#addDeviceModal").modal('hide');
                        $('#inputNameDevice').val("");
                        $('#inputFromDateBC').val("");
                        $('#inputToDateBC').val("");
                        $("#addDeviceKoModal").modal('show');
                        $("#addDeviceKoModal").hide();
                        $("#addDeviceKoModalInnerDiv1").show()
                        $("#addDeviceKoModalInnerDiv1").html('<h5>Operation Failed due to the following Error: ' + mydata["error_msg"] + '</h5><br>');
                        $("#addDeviceKoModalInnerDiv3").show()
                        $("#addDeviceKoModalInnerDiv3").html(mydata["log"]);



                        $('#devicesTable').DataTable().destroy();
                        fetch_data(true);
                    }

                },
                error: function (mydata)
                {
                    console.log("mydata")
                    console.log(mydata)
                    $('#addDeviceLoadingMsg').hide();
                    $('#addDeviceLoadingIcon').hide();
                    $("#addDeviceModal").modal('hide');
                    $('#inputNameDevice').val("");
                    $('#inputFromDateBC').val("");
                    $('#inputToDateBC').val("");
                    $("#addDeviceKoModal").modal('show');
                    $("#addDeviceKoModal").hide();

                    $("#addDeviceKoModalInnerDiv1").show()
                    $("#addDeviceKoModalInnerDiv1").html('<h5>Operation Failed due to the following Error: ' + mydata["error_msg"] + '</h5><br>');
                    $("#addDeviceKoModalInnerDiv3").show()
                    $("#addDeviceKoModalInnerDiv3").html(mydata["log"]);

                }
            });

    });
    // //add lines related to attributes - addAttrBtnUser
    // $("#addAttrBtnUser").off("click");
    // $("#addAttrBtnUser").click(function () {
    //     //console.log("#addAttrBtnUser");
    //     content = drawAttributeMenu("", "", "", "", "", "", "300", "", 'addlistAttributes', indexValues);
    //     indexValues = indexValues + 1;
    //     $('#addlistAttributesUser').append(content);
    // });
//END ADD NEW DEVICE  (INSERT INTO DB) 


//CONTEXTBROKER AND PROTOCOL RELATION FOR ADD DEVICE -SELECTOR 

//END CONTEXTBROKER AND PROTOCOL RELATION FOR ADD DEVICE -SELECTOR     


//CONTEXTBROKER AND PROTOCOL RELATION FOR EDIT DEVICE -SELECTOR 


//END CONTEXTBROKER AND PROTOCOL RELATION FOR EDIT DEVICE -SELECTOR     


// DELEGATIONS
    function updateGroupList(ouname) {
        $.ajax({
            url: "../api/ldap.php",
            data: {
                action: "get_group_for_ou",
                ou: ouname,
                token: sessionToken
            },
            type: "POST",
            async: true,
            success: function (data)
            {
                if (data["status"] === 'ko')
                {
                    $('#newDelegatedMsgGroup').css('color', '#f3cf58');
                    $('#newDelegatedMsgGroup').html(data["msg"]);
                } else if (data["status"] === 'ok')
                {
                    var $dropdown = $("#newDelegationGroup");
                    //remove old ones
                    $dropdown.empty();
                    //adding empty to rootadmin
                    if ((loggedRole == 'RootAdmin') || (loggedRole == 'ToolAdmin')) {
                        //console.log("adding empty");
                        $dropdown.append($("<option />").val("All groups").text("All groups"));
                    }
                    //add new ones
                    $.each(data['content'], function () {
                        $dropdown.append($("<option />").val(this).text(this));
                    });
                }
            },
            error: function (data)
            {
                $('#newDelegatedMsgGroup').css('color', '#f3cf58');
                $('#newDelegatedMsgGroup').html('Error calling internal API');
            }
        });
    }

    //populate organization list with any possibile value (if rootAdmin)
    if ((loggedRole == 'RootAdmin') || (loggedRole == 'ToolAdmin')) {
        $.ajax({
            url: "../api/ldap.php",
            data: {
                action: "get_all_ou",
                token: sessionToken
            },
            type: "POST",
            async: false,
            success: function (data)
            {
                if (data["status"] === 'ko')
                {
                    $('#newDelegatedMsgGroup').css('color', '#f3cf58');
                    $('#newDelegatedMsgGroup').html(data["msg"]);
                } else if (data["status"] === 'ok')
                {
                    var $dropdown = $("#newDelegationOrganization");
                    $.each(data['content'], function () {
                        $dropdown.append($("<option />").val(this).text(this));
                    });
                }
            },
            error: function (data)
            {
                $('#newDelegatedMsgGroup').css('color', '#f3cf58');
                $('#newDelegatedMsgGroup').html('Error calling internal API');
            }
        });
    }
    //populate organization list with myorganization (otherwise)
    else {
        $.ajax({
            url: "../api/ldap.php",
            data: {
                action: "get_logged_ou",
                token: sessionToken
            },
            type: "POST",
            async: false,
            success: function (data)
            {
                if (data["status"] === 'ko')
                {
                    console.log("Error: " + data);
                    alert("An error occured when reading the data. <br/> Get in touch with the Snap4City Administrator. <br/>" + data["error_msg"]);
                } else if (data["status"] === 'ok')
                {
                    var $dropdown = $("#newDelegationOrganization");
                    $dropdown.append($("<option/>").val(data['content']).text(data['content']));
                }
            },
            error: function (data)
            {
                console.log("Error: " + data);
                //TODO: manage error
            }
        });
    }

    //populate group list with selected organization
    updateGroupList($("#newDelegationOrganization").val());
    //eventually update the group list
    $('#newDelegationOrganization').change(function () {
        $(this).find(":selected").each(function () {
            updateGroupList($(this).val());
        });
    });
    $('#newDelegation').val('');
    $('#newDelegation').off('input');
    $('#newDelegation').on('input', function (e)
    {
        if ($(this).val().trim() === '')
        {
            $('#newDelegatedMsg').css('color', '#f3cf58');
            $('#newDelegatedMsg').html('Delegated username can\'t be empty');
            $('#newDelegationConfirmBtn').addClass('disabled');
        } else
        {
            $('#newDelegatedMsg').css('color', 'white');
            $('#newDelegatedMsg').html('User can be delegated');
            $('#newDelegationConfirmBtn').removeClass('disabled');
            $('#delegationsTable tbody tr').each(function (i)
            {
                if ($(this).attr('data-delegated').trim() === $('#newDelegation').val())
                {
                    $('#newDelegatedMsg').css('color', '#f3cf58');
                    $('#newDelegatedMsg').html('User already delegated');
                    $('#newDelegationConfirmBtn').addClass('disabled');
                }
            });
        }
    });
    $('#valuesTable thead').css("background", "rgba(0, 162, 211, 1)");
    $('#valuesTable thead').css("color", "white");
    $('#valuesTable thead').css("font-size", "1em");
    $('#valuesTable tbody tr').each(function (i) {
        if (i % 2 !== 0)
        {
            $(this).find('td').eq(0).css("background-color", "rgb(230, 249, 255)");
            $(this).find('td').eq(0).css("border-top", "none");
        } else
        {
            $(this).find('td').eq(0).css("background-color", "white");
            $(this).find('td').eq(0).css("border-top", "none");
        }
    });
    $('#delegationsModal').on('hidden.bs.modal', function (e)
    {
        $(this).removeData();
    });

    $("#addNewDeviceCheckExternalBtn").on('click', function () {

        $("#addDeviceCheckExternalLoadingIcon").show();
        var contextbroker = $('#selectContextBroker').val();
        var deviceService = $('#selectService').val();
        var deviceServicePath = $('#inputServicePathDevice').val();
        if ($('#selectProtocolDevice').val() === "ngsi w/MultiService") {
            // servicePath value pre-processing
            if (deviceServicePath[0] !== "/" || deviceServicePath === "")
                deviceServicePath = "/" + deviceServicePath;
            if (deviceServicePath[deviceServicePath.length - 1] === "/" && deviceServicePath.length > 1)
                deviceServicePath = deviceServicePath.substr(0, deviceServicePath.length - 1);
        }


        // $.ajax({//MIGRATE to test!!!!
        //     url: "../api/contextbroker.php",
        //     data: {
        //         action: "get_all_contextbroker",
        //         token: sessionToken,
        //     },
        //     type: "POST",
        //     async: true,
        //     datatype: 'json',
        //     success: function (data)
        //     {
        //         var content = data["data"]; //TOTEST
        //         for (let i = 0; i < content.length; i++) {
        //             if (content[i].name == contextbroker) {
        //                 var ip = content[i].ip;
        //                 var protocol = content[i].protocol;
        //                 var port = content[i].port;
        //                 var user = loggedUser;
        //                 var accesslink = content[i].accesslink;
        //                 var accessport = content[i].accessport;
        //                 var model = $('#selectModelDevice').val();
        //                 var edge_gateway_type = $('#selectEdgeGatewayType').val();
        //                 var edge_gateway_uri = $('#inputEdgeGatewayUri').val();
        //                 var apikey = content[i].apikey;
        //                 var path = content[i].path;
        //                 var kind = content[i].kind;
        //                 var device_name = $.trim($('#inputNameDevice').val());
        //                 var ipa = ip + ':' + port;
        //                 var latid = content[i].latitude;
        //                 var longi = content[i].longitude;
        //                 if ($('#selectModelDevice').val() === undefined || $('#selectModelDevice').val().length < 1) {
        //                     model = "custom";
        //                 }
        //                 //console.log("ACTIVATE STUD "+ kind);
        //                 //console.log("full link "+ accesslink+path);
        //                 activateStub(contextbroker, device_name, ipa, "extract", user, accesslink, accessport, model, edge_gateway_type, edge_gateway_uri, path, apikey, kind, latid, longi, deviceService, deviceServicePath);
        //             }
        //         }
        //
        //     },
        //     error: function (data) {
        //         $("#addDeviceCheckExternalLoadingIcon").hide();
        //         console.log("faliure" + JSON.stringify(data));
        //     }
        // });
    });
}); // end of ready-state
// function activateStub(cb, deviceName, ipa, protocol, user, accesslink, accessport, model, edge_type, edge_uri, path, apikey, kind, latid, longi, deviceService, deviceServicePath)
// {
//     //console.log("log "+ cb + " "+ipa+" "+accesslink+" "+accessport+" "+model+ " api "+ apikey + " organization "+ organization + " kind "+kind);
//     var data;
//     if (apikey !== null || apikey !== undefined) {
//         data = "contextbroker=" + cb + "&device_name=" + deviceName + "&ip=" + ipa + "&user=" + user + "&al=" + accesslink + "&ap=" + accessport + "&model=" + model + "&edge_gateway_type=" + edge_type + "&edge_gateway_uri=" + edge_uri + "&organization=" + organization + "&path=" + path + "&kind=" + kind + "&apikey=" + apikey;
//     } else {
//         data = "contextbroker=" + cb + "&device_name=" + deviceName + "&ip=" + ipa + "&user=" + user + "&al=" + accesslink + "&ap=" + accessport + "&model=" + model + "&edge_gateway_type=" + edge_type + "&edge_gateway_uri=" + edge_uri + "&organization=" + organization + "&path=" + path + "&kind=" + kind;
//     }
//
//     data += "&service=" + deviceService + "&service_path=" + deviceServicePath;
//     var service = _serviceIP + "/api/" + protocol;
//     //console.log(data);
//     //console.log(service);
//     var xhr = ajaxRequest();
//     xhr.addEventListener("readystatechange", function () {
//         if (this.readyState === 4 && this.status == 200) {
//             //console.log("RESPONSE TEXT"+this.responseText);
//             var resp = JSON.parse(this.responseText)
//             //console.log(resp);
//             //console.log(resp.message);
//
//             $("#addDeviceCheckExternalLoadingIcon").hide();
//             if (resp.message.indexOf("not found") == 0) {
//                 confirm("The device you entered does not exist on the Context Broker " + cb + ", modify the device's name and try again");
//             } else if (resp.message.indexOf("not reacheable") == 0) {
//                 confirm("The Context Broker " + cb + " is not reacheable");
//             } else if (resp.message.indexOf("path malformed") == 0) {
//                 confirm("The Context Broker " + cb + " contains a malformed access path");
//             } else if (resp.message.indexOf("extraction rules not found") == 0) {
//                 confirm("No extraction rules have been defined for the Context Broker " + cb);
//             } else {
//                 var msg = JSON.parse(resp.message);
//                 //console.log(msg);
//                 //console.log(msg.name);
//                 //console.log(msg.frequency);
//                 //console.log(msg.devicetype);
//
//                 $("#addNewDeviceCheckExternalBtn").hide();
//                 $("#addNewDeviceConfirmBtn").show();
//                 //$("#selectContextBrokerMsg").show();
//
//                 //$('#inputTypeDevice').val(msg.devicetype);		type now is inserted in input
//                 $("#inputTypeDevice").attr("disabled", false);
//                 $('#inputMacDevice').val("");
//                 $("#inputMacDevice").attr("disabled", false);
//                 //$('#selectEdgeGatewayType').val("");
//                 //$("#selectEdgeGatewayType").attr("disabled", true);
//                 //$('#inputEdgeGatewayUri').val("");
//                 //$("#inputEdgeGatewayUri").attr("disabled", true);
//                 $('#inputProducerDevice').val("");
//                 $("#inputProducerDevice").attr("disabled", false);
//                 $('#inputFrequencyDevice').val(msg.frequency);
//                 $("#inputFrequencyDevice").attr("disabled", false);
//                 $('#KeyOneDeviceUser').val("");
//                 $("#KeyOneDeviceUser").attr("disabled", false);
//                 $('#KeyTwoDeviceUser').val("");
//                 $("#KeyTwoDeviceUser").attr("disabled", false);
//                 //console.log(msg.latitude);
//                 if (msg.latitude !== undefined)
//                     $('#inputLatitudeDevice').val(msg.latitude);
//                 else {
//                     $('#inputLatitudeDevice').val(latid);
//                     msg.latitude = latid;
//                 }
//                 $("#inputLatitudeDevice").attr("disabled", false);
//                 if (msg.longitude !== undefined)
//                     $('#inputLongitudeDevice').val(msg.longitude);
//                 else {
//                     $('#inputLongitudeDevice').val(longi);
//                     msg.longitude = longi;
//                 }
//                 $("#inputLongitudeDevice").attr("disabled", false);
//                 drawMap1(msg.latitude, msg.longitude, 2);
//                 $("#selectModelDevice").attr("disabled", false);
//                 $("#selectModelDevice").val(msg.model);
//                 //$("#selectProtocolDevice").val(msg.protocol);
//                 $("#selectKindDevice").val(msg.kind);
//                 $("#addNewDeviceGenerateKeyBtn").attr("disabled", false);
//                 $("#addAttrBtn").attr("disabled", false);
//                 myattributes = msg.deviceValues;
//                 content = "";
//                 k = 0;
//                 while (k < myattributes.length)
//                 {
//                     content += drawAttributeMenu(myattributes[k].value_name,
//                             myattributes[k].data_type, myattributes[k].value_type, myattributes[k].editable, myattributes[k].value_unit, myattributes[k].healthiness_criteria,
//                             myattributes[k].healthiness_value, myattributes[k].value_name, 'addlistAttributes', indexValues);
//                     indexValues = indexValues + 1;
//                     k++;
//                 }
//                 $('#addlistAttributes').html(content);
//                 checkEverything();
//                 checkAddDeviceConditions();
//                 //checkAddMyDeviceConditions();
//             }
//
//             /*setTimeout(function(){
//              fetch_data(true);
//              }, 2000);*/
//         }
//     });
//     xhr.open("POST", service);
//     xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
//     /*	xhr.setRequestHeader("Content-Type", "text/plain;charset=UTF-8");*/
//     xhr.setRequestHeader("Access-Control-Allow-Origin", "*");
//     xhr.send(data);
//     return true;
// }

//   START TO CHANGE THE VISIBILITY  & OWNERSHIP 

// function changeVisibility(id, contextbroker, dev_organization, visibility, uri, k1, k2, model, protocol, service, servicePath) {
//     $("#delegationsModal").modal('show');
//     $("#delegationHeadModalLabel").html("Device - " + id);
//     // document.getElementById('CurrentVisiblityTxt').value = "Current Visiblity: " + visibility;
//     //if ((target == '#visibilityCnt')) {
//     if (visibility == 'MyOwnPrivate') {
//         newVisibility = 'public';
//         $('#visID').css('color', '#f3cf58');
//         $("#visID").html("Visibility - Private");
//         document.getElementById('newVisibilityPrivateBtn').style.visibility = 'hidden';
//         document.getElementById('newVisibilityPublicBtn').style.visibility = 'show';
//     } else //(visibility=='MyOwnPublic'){
//     {
//         newVisibility = 'private';
//         $('#visID').css('color', '#f3cf58');
//         $("#visID").html("Visibility - Public");
//         document.getElementById('newVisibilityPrivateBtn').style.visibility = 'show';
//         document.getElementById('newVisibilityPublicBtn').style.visibility = 'hidden';
//     }
//     // To Change from Private to Public
//     //$('#newVisibilityPublicBtn').off("click");
//     //$('#newVisibilityPublicBtn').click(function(e){
//     $(document).on("click", "#newVisibilityPublicBtn", function (event) {
//         $.ajax({
//             url: "../api/device.php",
//             data:
//                     {
//                         action: "change_visibility",
//                         id: id,
//                         contextbroker: contextbroker,
//                         visibility: newVisibility,
//                         token: sessionToken,
//                         k1: k1, //DEPRECATED?
//                         k2: k2, //DEPRECATED?
//                         service: service,
//                         servicePath: servicePath
//                     },
//             type: "POST",
//             async: true,
//             dataType: 'json',
//             success: function (data)
//             {
//                 if (data["status"] === 'ok')
//                 {
//                     $('#newVisibilityResultMsg').show();
//                     $("#visID").html("");
//                     $('#visID').css('color', '#f3cf58');
//                     $("#visID").html("Visibility - Private");
//                     $('#newVisibilityResultMsg').html('New visibility set to Public');
//                     //document.getElementById('newVisibilityPublicBtn').style.visibility = 'hidden';
//                     //document.getElementById('CurrentVisiblityTxt').value = "Current Visiblity: " +  newVisibility;
//                     //document.getElementById('newVisibilityPrivateBtn').style.visibility = 'show';
//
//                     $('#newVisibilityPublicBtn').addClass('disabled');
//                     setTimeout(function ()
//                     {
//                         $('#devicesTable').DataTable().destroy();
//                         fetch_data(true);
//                         location.reload();
//                     }, 3000);
//                 } else if (data["status"] === 'ko')
//                 {
//                     $('#newVisibilityResultMsg').show();
//                     $('#newVisibilityResultMsg').html('Error setting new visibility');
//                     $('#newVisibilityPublicBtn').addClass('disabled');
//                     setTimeout(function ()
//                     {
//                         $('#newVisibilityPublicBtn').removeClass('disabled');
//                         $('#newVisibilityResultMsg').html('');
//                         $('#newVisibilityResultMsg').hide();
//                     }, 3000);
//                 } else {
//                     console.log(data);
//                 }
//             },
//             error: function (errorData)
//             {
//                 $('#newVisibilityResultMsg').show();
//                 $('#newVisibilityResultMsg').html('Error setting new visibility');
//                 $('#newVisibilityPublicBtn').addClass('disabled');
//                 setTimeout(function ()
//                 {
//                     $('#newVisibilityPublicBtn').removeClass('disabled');
//                     $('#newVisibilityResultMsg').html('');
//                     $('#newVisibilityResultMsg').hide();
//                 }, 3000);
//             }
//         });
//     });
// // To Change from Private to Public
//     //$('#newVisibilityPrivateBtn').off("click");
//     //$('#newVisibilityPrivateBtn').click(function(e){
//     $(document).on("click", "#newVisibilityPrivateBtn", function (event) {
//         $.ajax({
//             url: "../api/device.php",
//             data:
//                     {
//                         action: "change_visibility",
//                         id: id,
//                         contextbroker: contextbroker,
//                         visibility: newVisibility,
//                         token: sessionToken,
//                         k1: k1, //DEPRECATED?
//                         k2: k2, //DEPRECATED?
//                         service: service,
//                         servicePath: servicePath
//                     },
//             type: "POST",
//             async: true,
//             dataType: 'json',
//             success: function (data)
//             {
//                 if (data["status"] === 'ok')
//                 {
//                     $('#newVisibilityResultMsg').show();
//                     $('#newVisibilityResultMsg').html('New visibility set Private');
//                     //$('#newVisibilityPrivateBtn').addClass('disabled');
//                     //document.getElementById('newVisibilityPrivateBtn').style.visibility = 'hidden';
//                     $('#newVisibilityPrivateBtn').addClass('disabled');
//                     //document.getElementById('CurrentVisiblityTxt').value = "Current Visiblity: " + newVisibility;
//                     //document.getElementById('newVisibilityPublicBtn').style.visibility = 'show';
//                     setTimeout(function ()
//                     {
//                         $('#devicesTable').DataTable().destroy();
//                         fetch_data(true);
//                         location.reload();
//                     }, 3000);
//                 } else if (data["status"] === 'ko')
//                 {
//                     $('#newVisibilityResultMsg').show();
//                     $('#newVisibilityResultMsg').html('Error setting new visibility');
//                     $('#newVisibilityPrivateBtn').addClass('disabled');
//                     setTimeout(function ()
//                     {
//                         $('#newVisibilityPrivateBtn').removeClass('disabled');
//                         $('#newVisibilityResultMsg').html('');
//                         $('#newVisibilityResultMsg').hide();
//                     }, 3000);
//                 } else {
//                     console.log(data);
//                 }
//             },
//             error: function (errorData)
//             {
//                 $('#newVisibilityResultMsg').show();
//                 $('#newVisibilityResultMsg').html('Error setting new visibility');
//                 $('#newVisibilityPrivateBtn').addClass('disabled');
//                 setTimeout(function ()
//                 {
//                     $('#newVisibilityPrivateBtn').removeClass('disabled');
//                     $('#newVisibilityResultMsg').html('');
//                     $('#newVisibilityResultMsg').hide();
//                 }, 3000);
//             }
//         });
//     });
//     //$('#newOwnershipConfirmBtn').off("click");
//     //$('#newOwnershipConfirmBtn').click(function(e){
//     $(document).on("click", "#newOwnershipConfirmBtn", function (event) {
//         // I generate a new pair of keys for the new owner
//         k1new = generateUUID();
//         k2new = generateUUID();
//         $.ajax({
//             url: "../api/device.php",
//             data: {
//                 action: "change_owner",
//                 id: id,
//                 contextbroker: contextbroker,
//                 newOwner: $('#newOwner').val(),
//                 token: sessionToken,
//                 k1: k1new,
//                 k2: k2new,
//                 model: model,
//                 service: service,
//                 servicePath: servicePath
//             },
//             type: "POST",
//             async: true,
//             dataType: 'json',
//             success: function (data)
//             {
//                 if (data["status"] === 'ok')
//                 {
//                     $('#newOwner').val('');
//                     $('#newOwner').addClass('disabled');
//                     $('#newOwnershipResultMsg').show();
//                     $('#newOwnershipResultMsg').html('New ownership set correctly');
//                     $('#newOwnershipConfirmBtn').addClass('disabled');
//                     setTimeout(function ()
//                     {
//                         $('#devicesTable').DataTable().destroy();
//                         fetch_data(true);
//                         location.reload();
//                     }, 3000);
//                 } else if (data["status"] === 'ko')
//                 {
//                     $('#newOwner').addClass('disabled');
//                     $('#newOwnershipResultMsg').html('Error setting new ownership: please try again');
//                     $('#newOwnershipConfirmBtn').addClass('disabled');
//                     setTimeout(function ()
//                     {
//                         $('#newOwner').removeClass('disabled');
//                         $('#newOwnershipResultMsg').html('');
//                         $('#newOwnershipResultMsg').hide();
//                     }, 3000);
//                 } else {
//                     console.log(data);
//                 }
//             },
//             error: function (errorData)
//             {
//                 $('#newOwner').addClass('disabled');
//                 $('#newOwnershipResultMsg').html('Error setting new ownership: please try again');
//                 $('#newOwnershipConfirmBtn').addClass('disabled');
//                 setTimeout(function ()
//                 {
//                     $('#newOwner').removeClass('disabled');
//                     $('#newOwnershipResultMsg').html('');
//                     $('#newOwnershipResultMsg').hide();
//                 }, 3000);
//             }
//         });
//     });
//     $("#delegationsCancelBtn").off("click");
//     $("#delegationsCancelBtn").on('click', function () {
//         $('#newDelegation').val("");
//         $('#newDelegationGroup').val("");
//         $('#newDelegationOrganization').val("");
//         $('#newOwner').val("");
//         $("#newVisibilityResultMsg").html("");
//         $("#newOwnershipResultMsg").html("");
//         location.reload();
//         $('#delegationsModal').modal('hide');
//     });
// //	} //end of tab visibilityCnt
//
//
//     //else if ((target == '#ownershipCnt')) {
//     //Change ownership of a device
//     //alert (id + contextbroker + uri +  $('#newOwner').val() + k1 + k2 + loggedUser + sessionToken);
//
//     //} //end of delegationsCnt
//     //else {console.log(data);}
//
//     //populate the beginning of the tables and listen about the removal
//     $.ajax({
//
//         url: "../api/device.php", //Checking the delegation table
//         data:
//                 {
//                     action: "get_delegations", // check the action and to be specified
//                     id: id,
//                     contextbroker: contextbroker,
//                     token: sessionToken,
//                     service: service,
//                     servicePath: servicePath
//                 },
//         type: "POST",
//         async: true,
//         dataType: 'json',
//         success: function (data)
//         {
//             if (data["status"] == 'ok')
//             {
//
//                 delegations = data["delegation"];
//                 $('#delegationsTable tbody').html("");
//                 $('#delegationsTableGroup tbody').html("");
//                 for (var i = 0; i < delegations.length; i++)
//                 {
//                     if ((delegations[i].userDelegated != "ANONYMOUS") && (delegations[i].userDelegated != null)) {
//                         $('#delegationsTable tbody').append(
//                                 '<tr class="delegationTableRow" data-delegationId="' +
//                                 delegations[i].delegationId +
//                                 '" data-delegated="' +
//                                 delegations[i].userDelegated +
//                                 '"><td class="delegatedName">' +
//                                 delegations[i].userDelegated +
//                                 '</td><td class="kind">' +
//                                 delegations[i].kind +
//                                 '</td><td><i class="fa fa-remove removeDelegationBtn"></i></td></tr>'
//                                 );
//                     } else if (delegations[i].groupDelegated != null) {
//
//                         //extract cn and ou
//                         var startindex = delegations[i].groupDelegated.indexOf("cn=");
//                         if (startindex == -1)
//                         {
//
//                             gr = "All groups";
//                             var endindex_ou = delegations[i].groupDelegated.indexOf(",");
//                             var ou = delegations[i].groupDelegated.substring(3, endindex_ou);
//                         } else {
//                             var endindex_gr = delegations[i].groupDelegated.indexOf(",");
//                             var gr = delegations[i].groupDelegated.substring(3, endindex_gr);
//                             var endindex_ou = delegations[i].groupDelegated.indexOf(",", endindex_gr + 1);
//                             var ou = delegations[i].groupDelegated.substring(endindex_gr + 4, endindex_ou);
//                         }
//
//                         var DN = ou + "," + gr;
//                         $('#delegationsTableGroup tbody').append(
//                                 '<tr class="delegationTableRowGroup" data-delegationId="' +
//                                 delegations[i].delegationId +
//                                 '" data-delegated="' +
//                                 ou +
//                                 ',' +
//                                 gr +
//                                 '"><td class="delegatedName">' +
//                                 DN +
//                                 '</td><td class="kind">' +
//                                 delegations[i].kind +
//                                 '</td><td><i class="fa fa-remove removeDelegationBtnGroup"></i></td></tr>'
//                                 );
//                     }
//
//                 }
//                 $('#delegationsTable tbody').on("click", "i.removeDelegationBtn", function () {
//                     var rowToRemove = $(this).parents('tr');
//                     $.ajax({
//                         url: "../api/device.php",
//                         data:
//                                 {
//                                     action: "remove_delegation",
//                                     token: sessionToken,
//                                     delegationId: $(this).parents('tr').attr('data-delegationId'),
//                                     userDelegated: $(this).parents('tr').attr('data-delegated'),
//                                     id: id,
//                                     contextbroker: contextbroker,
//                                     service: service,
//                                     servicePath: servicePath
//                                 },
//                         type: "POST",
//                         async: true,
//                         dataType: 'json',
//                         success: function (data)
//                         {
//                             if (data["status"] === 'ok')
//                             {
//                                 rowToRemove.remove();
//                                 //console.log("ermoving a row from the table");
//                             } else
//                             {
//                                 //TBD insert a message of error
//                             }
//                         },
//                         error: function (errorData)
//                         {
//                             //TBD  insert a message of error
//                         }
//                     });
//                 });
//                 $('#delegationsTableGroup tbody').on("click", "i.removeDelegationBtnGroup", function () {
//                     var rowToRemove = $(this).parents('tr');
//                     $.ajax({
//                         url: "../api/device.php",
//                         data:
//                                 {
//                                     action: "remove_delegation",
//                                     token: sessionToken,
//                                     delegationId: $(this).parents('tr').attr('data-delegationId'),
//                                     groupDelegated: $(this).parents('tr').attr('data-delegated'),
//                                     id: id,
//                                     contextbroker: contextbroker,
//                                     service: service,
//                                     servicePath: servicePath
//                                 },
//                         type: "POST",
//                         async: true,
//                         dataType: 'json',
//                         success: function (data)
//                         {
//                             if (data["status"] === 'ok')
//                             {
//                                 rowToRemove.remove();
//                             } else
//                             {
//                                 //TBD insert a message of error
//                             }
//                         },
//                         error: function (errorData)
//                         {
//                             //TBD  insert a message of error
//                         }
//                     });
//                 });
//             } else
//             {
//                 // hangling situation of error
//                 console.log(json_encode(data));
//             }
//
//         },
//         error: function (errorData)
//         {
//             //TBD  insert a message of error
//         }
//     });
//     //listen about the confimation
//     $(document).on("click", "#newDelegationConfirmBtn", function (event) {
//         var newDelegation = document.getElementById('newDelegation').value;
//         var kind = document.getElementById('newDelegationKind').value;
//         newk1 = generateUUID();
//         newk2 = generateUUID();
//         $.ajax({
//             url: "../api/device.php", //which api to use
//             data:
//                     {
//                         action: "add_delegation",
//                         contextbroker: contextbroker,
//                         id: id,
//                         token: sessionToken,
//                         delegated_user: newDelegation,
//                         k1: newk1,
//                         k2: newk2,
//                         kind: kind,
//                         service: service,
//                         servicePath: servicePath
//                     },
//             type: "POST",
//             async: true,
//             dataType: 'json',
//             success: function (data)
//             {
//                 if (data["status"] === 'ok')
//                 {
//                     $('#delegationsTable tbody').append(
//                             '<tr class="delegationTableRow" data-delegationId="' +
//                             data['delegationId'] +
//                             '" data-delegated="' +
//                             $('#newDelegation').val() +
//                             '"><td class="delegatedName">' +
//                             $('#newDelegation').val() +
//                             '</td><td class="kind">' +
//                             data['kind'] +
//                             '</td><td><i class="fa fa-remove removeDelegationBtn"></i></td></tr>'
//                             );
//                     $('#newDelegation').val('');
//                     $('#newDelegation').addClass('disabled');
//                     $('#newDelegatedMsg').css('color', 'white');
//                     $('#newDelegatedMsg').html('New delegation added correctly');
//                     $('#newDelegationConfirmBtn').addClass('disabled');
//                     setTimeout(function ()
//                     {
//                         $('#newDelegation').removeClass('disabled');
//                         $('#newDelegatedMsg').css('color', '#f3cf58');
//                         $('#newDelegatedMsg').html('Delegated username can\'t be empty');
//                     }, 1500);
//                 } else
//                 {
//                     var errorMsg = null;
//                     $('#newDelegation').val('');
//                     $('#newDelegation').addClass('disabled');
//                     $('#newDelegatedMsg').css('color', '#f3cf58');
//                     $('#newDelegatedMsg').html(data["msg"]);
//                     $('#newDelegationConfirmBtn').addClass('disabled');
//                     setTimeout(function ()
//                     {
//                         $('#newDelegation').removeClass('disabled');
//                         $('#newDelegatedMsg').css('color', '#f3cf58');
//                         $('#newDelegatedMsg').html('Delegated username can\'t be empty');
//                     }, 3000);
//                 }
//             },
//             error: function (errorData)
//             {
//                 var errorMsg = "Error calling internal API";
//                 $('#newDelegation').val('');
//                 $('#newDelegation').addClass('disabled');
//                 $('#newDelegatedMsg').css('color', '#f3cf58');
//                 $('#newDelegatedMsg').html(errorMsg);
//                 $('#newDelegationConfirmBtn').addClass('disabled');
//                 setTimeout(function ()
//                 {
//                     $('#newDelegation').removeClass('disabled');
//                     $('#newDelegatedMsg').css('color', '#f3cf58');
//                     $('#newDelegatedMsg').html('Delegated username can\'t be empty');
//                 }, 3000);
//             }
//         });
//     }); //single delegation -end
//
//     //group delegation -start------------------------------------------------------------------------------------------------------------
//     $(document).on("click", "#newDelegationConfirmBtnGroup", function (event) {
//         var delegatedDN = "";
//         var e = document.getElementById("newDelegationGroup");
//         if ((typeof e.options[e.selectedIndex] !== 'undefined') && (e.options[e.selectedIndex].text !== 'All groups')) {
//             delegatedDN = "cn=" + e.options[e.selectedIndex].text + ",";
//         }
//         var e2 = document.getElementById("newDelegationOrganization");
//         delegatedDN = delegatedDN + "ou=" + e2.options[e2.selectedIndex].text;
//         var kind = document.getElementById('newDelegationKindGroup').value;
//         newk1 = generateUUID();
//         newk2 = generateUUID();
//         $.ajax({
//             url: "../api/device.php",
//             data:
//                     {
//                         action: "add_delegation",
//                         contextbroker: contextbroker,
//                         id: id,
//                         token: sessionToken,
//                         delegated_group: delegatedDN,
//                         k1: newk1,
//                         k2: newk2,
//                         kind: kind,
//                         service: service,
//                         servicePath: servicePath
//                     },
//             type: "POST",
//             async: true,
//             dataType: 'json',
//             success: function (data)
//             {
//                 if (data["status"] === 'ok')
//                 {
//                     var toadd = $('#newDelegationOrganization').val();
//                     if (document.getElementById("newDelegationGroup").options[e.selectedIndex].text != '') {
//                         toadd = toadd + "," + $('#newDelegationGroup').val();
//                     }
//
//                     $('#delegationsTableGroup tbody').append(
//                             '<tr class="delegationTableRowGroup" data-delegationId="' +
//                             data['delegationId'] +
//                             '" data-delegated="' +
//                             toadd +
//                             '"><td class="delegatedNameGroup">' +
//                             toadd +
//                             '</td><td class="kind">' +
//                             data['kind'] +
//                             '</td><td><i class="fa fa-remove removeDelegationBtnGroup"></i></td></tr>'
//                             );
//                     $('#newDelegatedMsgGroup').css('color', 'white');
//                     $('#newDelegatedMsgGroup').html('New delegation added correctly');
//                     setTimeout(function ()
//                     {
//                         $('#newDelegatedMsgGroup').css('color', '#f3cf58');
//                         $('#newDelegatedMsgGroup').html('Delegated groupname can\'t be empty');
//                     }, 1500);
//                 } else
//                 {
//                     var errorMsg = null;
//                     $('#newDelegatedMsgGroup').css('color', '#f3cf58');
//                     $('#newDelegatedMsgGroup').html(data["msg"]);
//                     setTimeout(function ()
//                     {
//                         $('#newDelegationGroup').removeClass('disabled');
//                         $('#newDelegationOrganization').removeClass('disabled');
//                         $('#newDelegatedMsgGroup').css('color', '#f3cf58');
//                         $('#newDelegatedMsgGroup').html('Delegated groupname can\'t be empty');
//                     }, 2000);
//                 }
//             },
//             error: function (errorData)
//             {
//                 var errorMsg = "Error calling internal API";
//                 $('#newDelegatedMsgGroup').css('color', '#f3cf58');
//                 $('#newDelegatedMsgGroup').html(errorMsg);
//                 setTimeout(function ()
//                 {
//                     $('#newDelegatedMsgGroup').css('color', '#f3cf58');
//                     $('#newDelegatedMsgGroup').html('Delegated groupname can\'t be empty');
//                 }, 2000);
//             }
//         });
//     }); //group delegation -end
//
// }

// END TO CHANGE THE VISIBILITY 


// Related to the Map 














