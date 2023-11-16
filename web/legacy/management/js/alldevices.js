$.fn.modal.Constructor.prototype.enforceFocus = function () { };

var gb_datatypes = "";
var gb_value_units = "";
var gb_value_types = "";
var defaultPolicyValue = [];
var gb_device = "";
var gb_latitude = "";
var gb_longitude = "";
var dataTable = "";
var indexValues = 0;//it keeps track of unique identirier on the values, so it's possible to enforce specific value type 
var tableFirstLoad = true;
var gb_old_cb = "";

//--------to get the datatypes items----------
$.ajax({
    url: "../api/device.php",
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

//--------to get the list of context broker----------
$.ajax({
    url: "../api/contextbroker.php",
    data: {
        action: "get_all_contextbroker",
        token: sessionToken
    },
    type: "POST",
    async: true,
    success: function (data) {
        if (data["status"] === 'ok') {
            addCB($("#selectContextBrokerM"), data);
            addCB($("#selectContextBroker"), data);
        } else {
            console.log("error getting the context brokers " + data);
        }
    },
    error: function (data) {
        console.log("error in the call to get the context brokers " + data);
        alert("Network errors. <br/> Get in touch with the Snap4City Administrator<br/>" + JSON.stringify(data));
    }
});

//--------to get the list of context broker----------
$.ajax({
    url: "../api/model.php",
    data: {
        action: "get_all_models",
        token: sessionToken
    },
    type: "POST",
    async: true,
    success: function (data) {
        if (data["status"] === 'ok') {
            addModel($("#selectModelDevice"), data);
        } else {
            console.log("error getting the context brokers " + data);
        }
    },
    error: function (data) {
        console.log("error in the call to get the context brokers " + data);
        alert("Network errors. <br/> Get in touch with the Snap4City Administrator<br/>" + JSON.stringify(data));
    }
});

function removeElementAt(parent, child) {
    var list = document.getElementById(parent);
    if (parent == "editlistAttributes")
        document.getElementById('deletedAttributes').appendChild(child.parentElement.parentElement.parentElement);
    else
        list.removeChild(child.parentElement.parentElement.parentElement);
    checkAtlistOneAttribute();
    checkEditAtlistOneAttribute();
}

function drawAttributeMenu(attrName, data_type, value_type, editable, value_unit, healthiness_criteria, value_refresh_rate, old_value_name, parent, indice) {
    if (attrName == "")
        msg = "<div style=\"color:red;\" class=\"modalFieldMsgCnt\"></div>";
    else
        msg = "<div class=\"modalFieldMsgCnt\">&nbsp;</div>";

    options = "";
    //   mydatatypes = "";
    if (value_type == "") {
        options += "<option hidden disabled selected value=\"NOT VALID OPTION\"> -- select an option -- </option>";
        msg_value_type = "<div style=\"color:red;\" class=\"modalFieldMsgCnt\">Value type is mandatory</div>";
    } else
        msg_value_type = "<div style=\"color:#337ab7;\" class=\"modalFieldMsgCnt\">Ok</div>";

    for (var n = 0; n < gb_value_types.length; n++) {
        if (value_type == gb_value_types[n].value){
            options += "<option value=\"" + gb_value_types[n].value + "\" selected>" + gb_value_types[n].value +" ("+gb_value_types[n].label + ")</option>";
       //  mydatatypes += "<option value=\"" + gb_value_types[n].data_type_value + "\" selected>" +gb_value_types[n].data_type_value + "</option>";
     }
        else{
            options += "<option value=\"" + gb_value_types[n].value + "\">" +gb_value_types[n].value +" ("+ gb_value_types[n].label + ")</option>";
         //   mydatatypes += "<option value=\"" + gb_value_types[n].data_type_value + "\" selected>" +gb_value_types[n].data_type_value + "</option>";
    }}

    myunits = "";
    msg_value_unit = "<div style=\"color:#337ab7;\" class=\"modalFieldMsgCnt\">Ok</div>";
    //retrieve acceptable value unit, and select the selected if available
    validValueUnit = getValidValueUnit(value_type, value_unit);
    if (validValueUnit !== "") {
        if (!validValueUnit.includes('selected')) {
            myunits += "<option hidden disabled selected value=\"NOT VALID OPTION\"> -- select an option -- </option>";
            msg_value_unit = "<div style=\"color:red;\" class=\"modalFieldMsgCnt\">Value unit is mandatory</div>";
        }
        myunits += validValueUnit;
    }

  msg_data_unit = "<div style=\"color:#337ab7;\" class=\"modalFieldMsgCnt\">Ok</div>";
     mydatatypes = "";
   validDataType =  getValidDataType(value_type, data_type);
    if (validDataType !== "") {
        if (!validDataType.includes('selected')) {
            mydatatypes += "<option hidden disabled selected value=\"NOT VALID OPTION\"> -- select an option -- </option>";
            msg_data_unit = "<div style=\"color:red;\" class=\"modalFieldMsgCnt\">Data type is mandatory</div>";
        }
        mydatatypes += validDataType;
    }




//    mydatatypes = "";
//    if (data_type != "")
//        labelcheck = data_type;
//    else
//        labelcheck = "";
//    for (var n = 0; n < gb_datatypes.length; n++) {
//        if (labelcheck == gb_datatypes[n])
//       if (labelcheck ==  gb_value_types[n].data_type_value)
//         mydatatypes += "<option value=\"" + gb_value_types[n].data_type_value + "\" selected>" +gb_value_types[n].data_type_value + "</option>";
//            mydatatypes += "<option value=\"" + gb_datatypes[n] + "\" selected>" + gb_datatypes[n] + "</option>";
//        else
//            mydatatypes += "<option value=\"" + gb_datatypes[n] + "\">" + gb_datatypes[n] + "</option>";
//    }

    return "<div class=\"row\" style=\"border:2px solid blue; padding: 8px;\" id=\"value" + indice + "\">" +
            "<div class=\"col-xs-6 col-md-3 modalCell\">" +
            "<div class=\"modalFieldCnt\" title=\"Insert a name for the sensor/actuator\"><input type=\"text\" class=\"modalInputTxt valueName Input_onlyread\"" +
            "name=\"" + attrName + "\"  value=\"" + attrName + "\" onkeyup=\"checkStrangeCharacters(this)\">" +
            "</div><div class=\"modalFieldLabelCnt\">Value Name</div>" + msg + "</div>" +
            
            
            
            "<div class=\"col-xs-6 col-md-3 modalCell\"><div class=\"modalFieldCnt\">" +
            "<select class=\"modalInputTxt Select_onlyread\" id=\"value_type" + indice + "\" " +
            "onchange=valueTypeChanged(" + indice + ") " +
            "title=\"select the type of the sensor/actuator\">" + options +
            "</select></div><div class=\"modalFieldLabelCnt \">Value Type</div>" + msg_value_type + "</div>" +
            "<div class=\"col-xs-6 col-md-3 modalCell\"><div class=\"modalFieldCnt\" title=\"select the unit of the data generated by the sensor/actuator\">" +
            "<select class=\"modalInputTxt Select_onlyread\" id=\"value_unit" + indice + "\" " +
            "onchange=valueUnitChanged(" + indice + ") " +
            "\">" +
            myunits +
            "</select></div><div class=\"modalFieldLabelCnt\">Value Unit</div>" + msg_value_unit + "</div>" +
            
           " <div class=\"col-xs-6 col-md-3 modalCell\"><div class=\"modalFieldCnt\">" +
            "<select class=\"modalInputTxt Select_onlyread\"  id=\"data_type" + indice + "\" " +  
            "onchange=dataTypeChanged(" + indice + ") " +
            "\" title=\"select the type of data generated by the sensor/actuator\">" + mydatatypes +
            "</select></div><div class=\"modalFieldLabelCnt\">Data Type</div>" + msg_data_unit + "</div>" +
            
//            "<div class=\"col-xs-6 col-md-3 modalCell\"><div class=\"modalFieldCnt\" title=\"is the sensor/actuator editable?\">" +
//            "<select class=\"modalInputTxt Select_onlyread Hidden_insert\" name=\"" + editable +
//            "\">" +
//            "<option value='0' default>false</option>" +
//            "<option value='1'>true</option> </select>" +
//            "</div><div class=\"modalFieldLabelCnt Hidden_insert\">Editable</div></div>" +
            
            "<div class=\"col-xs-6 col-md-3 modalCell \"><div class=\"modalFieldCnt\" ><label  class=\"switch \"> <input type=\"checkbox\" onclick=\"disableInput(id)\" style=\"display:none\"  class=\" Check_BOX\" id=\"Checkbox_" + attrName + "\" checked> </input><span style=\"display:none\"   id=\"SpanCheckbox_" + attrName + "\" class=\" Check_BOX slider round\">Send value</span></label></div></div>" +
            

            "<div class=\"col-xs-6 col-md-3 modalCell\"><div class=\"modalFieldCnt\" title=\"select a criterion as a reference to decide whether the sensor/actuator is working well\">" +
            "<select id=\"SELECTHealthCriteria\" class=\"modalInputTxt Select_onlyread Hidden_insert\" name=\"" + healthiness_criteria +
            "\" \>" +
            "<option value=\"refresh_rate\">Refresh rate</option>" +
            "<option value=\"different_values\">Different Values</option>" +
            "<option value=\"within_bounds\">Within bounds</option>" +
            "</select></div><div class=\"modalFieldLabelCnt Hidden_insert\">Healthiness Criteria</div> </div>"+
            
            
          
            "<div class=\"col-xs-6 col-md-3 modalCell\"><div class=\"modalFieldCnt\" title=\"Insert the limit value(s) to consider the sensor/actuator as healthy, according to the selected criterion \">" +
            "<input type=\"text\" class=\"modalInputTxt Input_onlyread Hidden_insert\"  name=\"" + value_refresh_rate +
            "\" value=\"" + value_refresh_rate + "\"></div><div class=\"modalFieldLabelCnt Hidden_insert\">Healthiness Value</div></div>" +
            
            "<div class=\"col-xs-6 col-md-3 modalCell\">" + "<div style=\"display:none\"  class=\"modalFieldCnt INSERTValues\" title=\"Insert data in the sensor/actuator\"><input type=\"text\" class=\"modalInputTxt InputValue\"" +
            "id=\"Value" + attrName + "\" \"name=\"Value" + attrName + "\" onkeyup=\"checkStrangeCharacters(this)\">" +
            "</div><div  style=\"display:none\"  class=\"modalFieldLabelCnt INSERTValues\">Insert data</div> <span id=\"access-code-error" + attrName + "\" class=\"rsvp\" style=\"display:none; color:red;\"> Unvalid input</span></div>" +
           
            "<div class=\"col-xs-6 col-md-3 modalCell\"><div class=\"modalFieldCnt\">" +
            "<select class=\"modalInputTxt\" style=\"display:none\" name=\"" + old_value_name +
            "\" \>" +
            "<option value=\"" + old_value_name + "\">" + old_value_name + "</option>" +
            "</select></div></div>" +
            "<div class=\"col-xs-6 col-md-3 modalCell\"><div class=\"modalFieldCnt\">" +
            "<button  class=\"btn btn-danger RemoveAttrEdit\" onclick=\"removeElementAt('" + parent + "',this); return true;\">Remove Value</button></div></div>";
}

function format(d) {
    var showKey = "";
    if (d.visibility == 'MyOwnPublic' || d.visibility == 'MyOwnPrivate' || d.visibility == 'delegated') {
        if (d.k1 != "" && d.k2 != "")
            showKey =
                    '<div class="row">' +
                    '<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>K1:</b>' + "  " + d.k1 + '</div>' +
                    '<div class="clearfix visible-xs"></div>' +
                    '<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>K2:</b>' + "  " + d.k2 + '</div>' +
                    '</div>';
    }

    var multitenancy = "";
    if (d.service || d.servicePath) {
        multitenancy =
                '<div class="row">' +
                '<div class="col-xs-6 col-sm-6" style="background-color:#B3D9FF;"><b>Service/Tenant:</b>' + "  " + d.service + '</div>' +
                '<div class="clearfix visible-xs"></div>' +
                '<div class="col-xs-6 col-sm-6" style="background-color:#B3D9FF;"><b>ServicePath:</b>' + "  " + d.servicePath + '</div>' +
                '</div>';
    }

    var a = '<div class="container-fluid">' +
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
            '<div class="col-xs-12 col-sm-12" style="background-color:#E6E6FA;" data-toggle="tooltip" title="Go to the log LD"><b>Device Uri:</b><a href="' + d.url + '" target="_blank" > ' + d.uri + '</a> <a class="btn btn-info my-small-button pull-right" href="' + d.m_url + '" target="_blank"><b>VIEW IN SERVICE MAP</b></a></div>' +
            '</div>' +
            '</div>' +
            '<div class="row">' +
            '<div class="col-xs-12 col-sm-12" style="background-color:#D6CADD;"><b>Organization:</b>' + "  " + d.organization;
    var b = ' <button type="button"class="btn btn-info my-small-button pull-right" ' +
            'data-id="' + d.id + '" ' +
            'data-contextbroker="' + d.contextBroker + '" ' +
            'data-service="' + d.service + '" ' +
            'data-servicePath="' + d.servicePath + '" ' +
            'data-devicetype="' + d.devicetype + '" ' +
            'data-latitude="' + d.latitude + '" ' +
            'data-longitude="' + d.longitude + '" id="' + d.id + '_NewValuesInput" onclick="NewValuesOnDevice(id);"><b>NEW DATA IN</b> ' + d.id + '</button>';

    var c = '</div>' + '<div class="clearfix visible-xs"></div>' +
            '</div>' +
            '<div class="row">' +
            '<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><button class="btn btn-info my-small-button" onclick="datainspect(\'' +
            d.id + '\',\'' + d.devicetype + '\',\'' + d.contextBroker + '\',\'' + d.service + '\',\'' + d.servicePath + '\',\'v1\');return true;"><b>PAYLOAD NGSI v1</b></button></div>' +
            '<div class="clearfix visible-xs"></div>' +
            '<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><button class="btn btn-info my-small-button" onclick="datainspect(\'' +
            d.id + '\',\'' + d.devicetype + '\',\'' + d.contextBroker + '\',\'' + d.service + '\',\'' + d.servicePath + '\',\'v2\');return true;"><b>PAYLOAD NGSI v2</b></button></div>' +
            '</div>' +
            showKey +
            getInfoCert(d.privatekey, d.visibility, d.created, d.id, d.contextBroker, d.certificate, d.sha) +
            multitenancy +
            '</div>';
    if (d.protocol == "ngsi"|| d.protocol == "ngsi w/MultiService") {
        if ((loggedRole == "RootAdmin") || (loggedRole == "ToolAdmin") || d.visibility.substring(0, 5) == "MyOwn") {
            return a + b + c;
        }
    }
    return a + c;
}

//DataTable fetch_data function 
function fetch_data(destroyOld, selected = null) {
     $('#devicesTable').DataTable().clear().destroy();
    if (destroyOld) {
        $('#devicesTable').DataTable().clear().destroy();
        tableFirstLoad = true;

    }

    if (selected == null)//TODO uniform the two call below
    {
        mydata = {action: "get_all_device_admin", token: sessionToken, username: loggedUser, organization: organization, no_columns: ["position", "status1", "owner", "edit", "delete", "map"]};
    } else {
        mydata = {action: "get_all_device_admin", token: sessionToken, select: selected, username: loggedUser, organization: organization, no_columns: ["position", "status1", "owner", "edit", "delete", "map"]};
    }

    dataTable = $('#devicesTable').DataTable({
        "processing": true,
        "serverSide": true,
        "lengthMenu": [[5, 25, 50, 100, -1], [5, 25, 50, 100, "All"]],
        "pageLength": 5,
        "scrollX": true,

        "paging": true,
        "ajax": {
            url: "../api/device.php",
            data: mydata,
            //token : sessionToken,
            datatype: 'json',
            type: "POST",
            //"dataSrc": "";		 
        },
        "columns": [
            {
                "class": "details-control",
                "name": "position",
                "orderable": false,
                "data": null,
                "defaultContent": "",
                "render": function () {
                    return '<i class="fa fa-plus-square" aria-hidden="true"></i>';
                },
                width: "15px"
            },
            {
                "name": "d.id", "data": function (row, type, val, meta) {

                    return row.id;
                }
            },
            {
                "name": "d.contextBroker", "data": function (row, type, val, meta) {
                    return row.contextBroker;
                }
            },
            {
                "name": "d.devicetype", "data": function (row, type, val, meta) {

                    return row.devicetype;
                }
            },
            {
                "name": "d.model", "data": function (row, type, val, meta) {

                    return row.model;
                }
            },
            {
                "name": "d.visibility", "data": function (row, type, val, meta) {

                    //return row.visibility;

                    if (row.visibility == 'MyOwnPrivate') {
                        return '<button type="button"  class=\"myOwnPrivateBtn\" onclick="changeVisibility(\'' + row.id + '\',\'' + row.contextBroker + '\',\'' + row.organization + '\',\'' + row.visibility + '\',\'' + row.uri + '\',\'' + row.k1 + '\',\'' + row.k2 + '\',\'' + row.model + '\',\'' + row.protocol + '\',\'' + row.service + '\',\'' + row.servicePath + '\')">' + row.visibility + '</button>';
                    } else if (row.visibility == 'MyOwnPublic') {
                        return '<button type="button"  class=\"myOwnPublicBtn\" onclick="changeVisibility(\'' + row.id + '\',\'' + row.contextBroker + '\',\'' + row.organization + '\',\'' + row.visibility + '\',\'' + row.uri + '\',\'' + row.k1 + '\',\'' + row.k2 + '\',\'' + row.model + '\',\'' + row.protocol + '\',\'' + row.service + '\',\'' + row.servicePath + '\')">' + row.visibility + '</button>';
                    } else if (row.visibility == 'public' || row.visibility == 'Public') {
                        return '<button type="button"  class=\"publicBtn\" onclick="changeVisibility(\'' + row.id + '\',\'' + row.contextBroker + '\',\'' + row.organization + '\',\'' + row.visibility + '\',\'' + row.uri + '\',\'' + row.k1 + '\',\'' + row.k2 + '\',\'' + row.model + '\',\'' + row.protocol + '\',\'' + row.service + '\',\'' + row.servicePath + '\')">' + row.visibility + '</button>';
                    } else // value is private
                    {
                        return '<button type="button"  class=\"delegatedBtn\" onclick="changeVisibility(\'' + row.id + '\',\'' + row.contextBroker + '\',\'' + row.organization + '\',\'' + row.visibility + '\',\'' + row.uri + '\',\'' + row.k1 + '\',\'' + row.k2 + '\',\'' + row.model + '\',\'' + row.protocol + '\',\'' + row.service + '\',\'' + row.servicePath + '\')">' + row.visibility + '</button>';
                    }
                    return '';

                }
            },
            {
                "name": "d.organization", "data": function (row, type, val, meta) {

                    return row.organization;
                }
            },

            {
                "name": "owner", "data": function (row, type, val, meta) {

                    return row.owner;
                }
            },
            {
                "name": "status1", "data": function (row, type, val, meta) {

                    return row.status1;
                }
            },

            {
                data: null,
                "name": "edit",
                "orderable": false,
                className: "center",
                render: function (d) {

                    //defaultContent: '<button type="button" id="edit" class="editDashBtn data-id="'+ row.name +'"">Edit</button>'
                    if (loggedRole == 'RootAdmin' || d.visibility == 'MyOwnPublic' || d.visibility == 'MyOwnPrivate') {

                        return '<button type="button" class="editDashBtn" ' +
                                'data-id="' + d.id + '" ' +
                                'data-contextBroker="' + d.contextBroker + '" ' +
                                'data-organization="' + d.organization + '" ' +
                                'data-kind="' + d.kind + '" ' +
                                'data-model="' + d.model + '" ' +
                                'data-devicetype="' + d.devicetype + '" ' +
                                'data-uri="' + d.uri + '" ' +
                                'data-visibility="' + d.visibility + '" ' +
                                'data-frequency="' + d.frequency + '" ' +
                                'data-format="' + d.format + '" ' +
                                'data-ownership="' + d.ownership + '" ' +
                                'data-protocol="' + d.protocol + '" ' +
                                'data-macaddress="' + d.macaddress + '" ' +
                                'data-producer="' + d.producer + '" ' +
                                'data-longitude="' + d.longitude + '" ' +
                                'data-latitude="' + d.latitude + '" ' +
                                'data-edgegateway_type="' + d.edgegateway_type + '" ' +
                                'data-edgegateway_uri="' + d.edgegateway_uri + '" ' +
                                'data-k1="' + d.k1 + '" ' +
                                'data-k2="' + d.k2 + '" ' +
                                'data-subnature="' + d.subnature + '" ' +
                                'data-static-attributes="' + btoa(d.staticAttributes) + '" ' +
                                'data-service="' + d.service + '" ' +
                                'data-servicePath="' + d.servicePath + '" ' +
                                'data-status1="' + d.status1 + '">Edit</button>';
                        //"data-created": row.created,
                        //"data-properties": row.properties,
                        //"data-attributes": row.attributes,
                        //"data-owner": row.owner,
                        //"data-sha1": row.sha,		
                    }
                    return '';
                }
            },
            {
                data: null,
                "name": "delete",
                "orderable": false,
                className: "center",
                //defaultContent: '<button type="button" id="delete" class="delDashBtn delete">Delete</button>'
                render: function (d) {
                    if (loggedRole == 'RootAdmin' || d.visibility == 'MyOwnPublic' || d.visibility == 'MyOwnPrivate') {
                        return '<button type="button" class="delDashBtn" ' +
                                'data-id="' + d.id + '" ' +
                                'data-contextBroker="' + d.contextBroker + '" ' +
                                'data-organization="' + d.organization + '" ' +
                                'data-protocol="' + d.protocol + '" ' +
                                'data-service="' + d.service + '" ' +
                                'data-servicePath="' + d.servicePath + '" ' +
                                'data-uri="' + d.uri + '">Delete</button>';
                    }
                    return '';
                }
            },
            {
                data: null,
                "name": "map",
                "orderable": false,
                className: "center",
                //defaultContent: '<button type="button" id="map" class="delDashBtn delete">Location</button>'
                render: function (d) {
                    return '<div class="addMapBtn"><i  data-toggle="modal" data-target="#addMapShow" onclick="drawMap(\'' + d.latitude + '\',\'' + d.longitude + '\', \'' + d.id + '\', \'' + d.devicetype + '\', \'' + d.kind + '\', \'' + 'addDeviceMapModalBodyShow' + '\')\" class="fa fa-globe"  style=\"font-size:36px; color: #0000ff\"></i></div>';
                }
            }
        ],
        "order": []

    });


}

function NewValuesOnDevice(strID) {
    document.getElementById('editLatLongValue').innerHTML = "";
    document.getElementById('ValuesINPUT').innerHTML = "";
    $("#NewValuesInputMODAL").modal('show');
    $('#InsertDataDeviceLoadingIcon').show();
    $('.nav-tabs a[href="#editAttributeValueTabDevice"]').tab('show');
    //$('#Mtab').show();
    $('#NOMob').hide();
    $('#editLatLongValue').hide();
    
    document.getElementById('NewValuesInputConfirmButton').style.display = 'left';

    $("#InsertModalStatus").hide();

    $('#NewValuesInputConfirmButton').show();
    $("#InsertDeviceModalTabs").show();
    // $("#GETimeStamp").show();

    var strID = "#" + strID;


    var Nid = $(strID).attr('data-id');
    var Ntype = $(strID).attr('data-devicetype');
    var Ncb = $(strID).attr('data-contextbroker');
    var Nserv = $(strID).attr('data-service');
    var NservPath = $(strID).attr('data-servicePath');


    $.ajax({
        url: "../api/device.php",
        data: {
            action: "Loading_value",
            id: Nid,
            type: Ntype,
            contextbroker: Ncb,
            token: sessionToken,
            service: Nserv,
            servicePath: NservPath,
            version: "v2"
        },
        type: "POST",
        async: true,
        dataType: 'json',
        success: function (mydata)
        {

            var old_value = mydata['content'];


            $('a[data-toggle="tab"]').off('shown.bs.tab').on('shown.bs.tab', function (e) {
                //   $('#InsertDataDeviceLoadingIcon').show();
                $('#InsertDataDeviceLoadingIcon').hide();


                var target = $(e.target).attr("href");

                if ((target === '#editGeoPositionTabDeviceNewValue')) {
                    $('#InsertDataDeviceLoadingIcon').hide();
                    document.getElementById('editLatLongValue').innerHTML = "";
                    document.getElementById('ValuesINPUT').innerHTML = "";
                    $("#GETimeStamp").hide();
                    $('#editLatLongValue').show();
                    $("#NOMob").hide();
                    $("#ValuesINPUT").hide();
                    $('#NewValuesInputConfirmButton').show();

                    $("#InsertModalStatus").hide();
                    $("#NoMobile").hide();

                    if (mydata['isMobile'] == "false") {
                        $('#editLatLongValue').hide();
                        $("#NOMob").hide();
                        $("#NoMobile").show();
                        $('#InsertDataDeviceLoadingIcon').hide();
                        $("#InsertModalStatus").hide();
                        $("#NoMobile").html('<br><br>' + Nid + " is not mobile!  ");
                    } else {
                        $("#InsertModalStatus").hide();
                        $('#InsertDataDeviceLoadingIcon').hide();
                        $("#NOMob").show();

                        $('#inputLatitudeDeviceValue').val(old_value['latitude']);
                        $('#inputLongitudeDeviceValue').val(old_value['longitude']);

                        drawMap1(old_value['latitude'], old_value['longitude'], 2);

                    }
                    $('#InsertDataDeviceLoadingIcon').hide();


                } else if ((target === '#editAttributeValueTabDevice')) {
                    $('#InsertDataDeviceLoadingIcon').show();
                    // NewValuesInput management

                    $('#editLatLongValue').hide();
                    $("#GETimeStamp").show();
                    $("#NoMobile").hide();
                    $("#NOMob").hide();
                    $("#ValuesINPUT").show();
                    $('#NewValuesInputConfirmButton').show();
                    document.getElementById('ValuesINPUT').innerHTML = "";


                    if (old_value) {
                        delete old_value['id'];
                        delete old_value['type'];
                        delete old_value['model'];
                        //console.log(old_value);
                        console.log("Values loading");
                    }
                    var DT = {};
                    var NameAttrUp = new Array();
                    var strTIME;
                    $.ajax({
                        url: "../api/device.php",
                        data: {
                            action: "get_device_attributes",
                            id: $(strID).attr('data-id'),

                            contextbroker: $(strID).attr('data-contextbroker'),
                            //document.getElementById('selectContextBrokerM').value,
                            token: sessionToken,
                            service: $(strID).attr('data-service'),
                            servicePath: $(strID).attr('data-servicePath')
                        },
                        type: "POST",
                        async: true,
                        dataType: 'json',
                        success: function (mydata)
                        {
                            $('#InsertDataDeviceLoadingIcon').hide();
                            $("#NewValuesInputMODAL").modal('show');
                            $("#GETimeStamp").hide();
                            var strTIMEtemp;


                            var row = null;


                            $("#editUserPoolsTable tbody").empty();

                            myattributes = mydata['content'];
                            content = "";
                            k = 0;
                            while (k < myattributes.length)
                            {
                                content = drawAttributeMenu(myattributes[k].value_name,
                                        myattributes[k].data_type, myattributes[k].value_type, myattributes[k].editable, myattributes[k].value_unit, myattributes[k].healthiness_criteria,
                                        myattributes[k].healthiness_value, myattributes[k].value_name, 'ValuesINPUT', indexValues);


                                str_valName = "#Value" + myattributes[k].value_name;
                                str_checkBox = "Checkbox_" + myattributes[k].value_name;
                                NameAttrUp.push("" + str_checkBox + "");


                                indexValues = indexValues + 1;

                                k++;


                                $('#ValuesINPUT').append(content);
                                $(".INSERTValues").show();
                                $(".Check_BOX").show();
                                $(".RemoveAttrEdit").hide();
                                $('.Select_onlyread').prop('disabled', true);

                                $('.Input_onlyread').attr('readonly', true);
                                j = k - 1;

                                $(str_valName).val(old_value[myattributes[j].value_name]);
                                if ($(str_valName).val() == "") {
                                    document.getElementById('NewValuesInputConfirmButton').disabled = true;
                                } else {
                                    document.getElementById('NewValuesInputConfirmButton').disabled = false;
                                }

                                const input = document.querySelector(str_valName);
                                
                                var temp = {};
                                temp['' + myattributes[j].value_name + ''] = {type: myattributes[j].data_type, value: old_value['' + myattributes[j].value_name + '' ]};
                                $.extend(DT, temp);
                               // console.log(DT);
                                strTIMEtemp = "#Value" + myattributes[j].value_name;
                                if (myattributes[j].value_type == 'timestamp' || !strTIMEtemp) {
                                    
                                    strTIME = "#Value" + myattributes[j].value_name;
                                    var checkTime;
                                    checkTime = $(strTIME).val(old_value[myattributes[j].value_name]);

                                    $("#GETimeStamp").show();
                                    $("#" + str_checkBox).hide();
                                    $("#Span" + str_checkBox).hide();

                                }


                                input.addEventListener('keyup', function (e) {

                                    var a = (e.target.value);
                                    const okButton = document.getElementById('NewValuesInputConfirmButton');
                                    t = e.currentTarget.id.substring(5, e.currentTarget.id.length);
                                    str1 = "#access-code-error" + t;
                                    str2 = "#InputDataType" + t;


                                    switch (DT[t].type) {

                                        case "float"  :
                                        case "integer" :

                                            {

                                                if (/\s/.test(a) || a == "" || isNaN(a) || /[!@#$%^&*()_+\-=\[\]{};':"\\|<>\/?]+/.test(a)) {
                                                    $(str1).show();
                                                    okButton.disabled = true;
                                                } else {
                                                    a = parseFloat(a);
                                                    $(str1).hide();
                                                    okButton.disabled = false;
                                                }
                                            }

                                            break;
                                        case "binary":

                                            {

                                                if (/\s/.test(a) || a == "" || isNaN(a) || /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]+/.test(a)) {
                                                    $(str1).show();
                                                    okButton.disabled = true;
                                                } else {
                                                    a = a.toString(2);
                                                    $(str1).hide();
                                                    okButton.disabled = false;
                                                }
                                            }
                                            break;
                                        case "boolean"  :
                                        case "switch"  :
                                        case "button":
                                            {


                                                if (/\s/.test(a) || a == "" || a !== false || a !== true || /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]+/.test(a)) {
                                                    $(str1).show();
                                                    okButton.disabled = true;
                                                } else {
                                                    $(str1).hide();
                                                    a = Boolean(a);
                                                    okButton.disabled = false;
                                                }
                                            }
                                            break;
                                        case "date"  :
                                        case "datatime"  :
                                        case "time"  :
                                        case "timestamp":
                                            {


                                                if (/\s/.test(a) || a == "" || /[!@#$%^&*()_+\-=\[\]{};'"\\|,.<>\/?]+/.test(a) || e.key == " ") {
                                                    $(str1).show();
                                                    okButton.disabled = true;
                                                } else {
                                                    $(str1).hide();
                                                    a = toISOString(a);
                                                    okButton.disabled = false;
                                                }
                                            }

                                            break;
                                        case "json"  :
                                        case "collection"  :
                                        case "set"  :
                                        case "vector"  :
                                        case "shape"  :
                                        case "wkt" :
                                            {


                                                if (/\s/.test(a) || a == "" || /[!@#$%^&*()_+\-=;'"\\|.<>\/?]+/.test(a)) {
                                                    $(str1).show();
                                                    okButton.disabled = true;
                                                } else {
                                                    $(str1).hide();
                                                    a = JSON.parse(a);
                                                    okButton.disabled = false;
                                                }
                                            }
                                            break;
                                        case "xlm":
                                        case "string":
                                            {


                                                if (/\s/.test(a) || a == "" || /[!@#$%^&*/?]+/.test(a)) {
                                                    $(str1).show();
                                                    okButton.disabled = true;
                                                } else {
                                                    $(str1).hide();
                                                   // a = parseFromString(a, "text/xml");
                                                    okButton.disabled = false;
                                                }
                                            }
                                            break;
                                    }
                                });

                            }
                            $("#editSchemaTabDevice #ValuesINPUT .row input:even").each(function () {
                                checkEditValueName($(this));
                            });
                            $("#GETimeStamp").click(function () {
                                $(strTIME).val(old_value[myattributes[j].value_name]);
                                const currentTime = new Date().toISOString();
                                $(strTIME).val(currentTime.toString());

                            });
                            checkEditDeviceConditions();
                            $(".RemoveAttrEdit").hide();
                            $(".Hidden_insert").hide();

                            $('#NewValuesInputConfirmButton').off("click");

                            $('#NewValuesInputConfirmButton').click(function () { 
                                if(strTIME!=null && (old_value[strTIME.substr(6, strTIME.length)]==$(strTIME).val())){
                                    // if){
                                     $('#ValuesINPUT').hide();
                                       $("#GETimeStamp").hide();
                                        $("#InsertModalStatus").show();
                                     // $("#InsertModalStatus").html("You must insert a valid time!");
                                       $("#InsertModalStatus").html('<br><br>' + "You must insert a valid time!");
                                        $('#NewValuesInputConfirmButton').hide();
                                  //  }
                                }else {

                                var pay_new_data = CreateJsonNewValue(mydata['content'], NameAttrUp, old_value);

                                $("#NoMobile").hide();
                                $('#ValuesINPUT').hide();
                                $("#InsertDataDeviceLoadingIcon").show();

                                $.ajax({
                                    url: "../api/device.php",
                                    data: {
                                        action: "Insert_Value",
                                        id: Nid,
                                        type: Ntype,
                                        contextbroker: Ncb,
                                        token: sessionToken,
                                        service: Nserv,
                                        servicePath: NservPath,
                                        version: "v2",
                                        payload: JSON.stringify(pay_new_data)
                                    },
                                    type: "POST",
                                    async: true,
                                    dataType: 'json',
                                    success: function (mydata)
                                    {
                                        $('#ValuesINPUT').hide();
                                        $('#InsertDataDeviceLoadingIcon').hide();
                                        console.log(mydata);

                                        console.log("Values update");


                                        $("#InsertModalStatus").html('<br><br>' + Nid + "'s value updates! ");
                                        $("#GETimeStamp").hide();
                                        $("#NOMob").hide();
                                        $("#editLatLongValue").hide();

                                        $("#InsertModalStatus").show();
                                        $('#NewValuesInputConfirmButton').hide();
                                        document.getElementById('editLatLongValue').innerHTML = "";
                                        document.getElementById('ValuesINPUT').innerHTML = "";

                                    },
                                    error: function (data)
                                    {
                                        $('#InsertDataDeviceLoadingIcon').hide();
                                        console.log("Insert values pool KO");
                                        console.log((data));
                                        $('#ValuesINPUT').hide();
                                        $("#InsertModalStatus").html(data.responseText);
                                        $("#InsertModalStatus").show();
                                    }


                                });
                            }
                                
                            });



                        },
                        error: function (data)
                        {
                            $('#InsertDataDeviceLoadingIcon').hide();
                            console.log("Insert values pool KO");
                            console.log((data));
                            $('#ValuesINPUT').hide();
                            $("#InsertModalStatus").html(data.responseText);
                            $("#InsertModalStatus").show();
                            $('#LoadingGif').hide();


                            $('#inputLatitudeDeviceValue').hide();
                            $('#InsertDataDeviceLoadingIcon').hide();
                            console.log("Get values pool KO");
                            console.log(JSON.stringify(data));
                            alert("Error in reading data from the database<br/> Please get in touch with the Snap4city Administrator");



                        }


                    });


                }
            });
            $('a[href=#editGeoPositionTabDeviceNewValue]').click();
            $('#InsertDataDeviceLoadingIcon').hide();


        },
        error: function (data)
        {
            $('#InsertDataDeviceLoadingIcon').hide();
            console.log("Insert values pool KO");
            console.log((data));
            $('#ValuesINPUT').hide();
            $("#InsertModalStatus").html("<br/><br/>" + data.responseText);
            $("#InsertModalStatus").show();
            $('#LoadingGif').hide();
            $('#GETimeStamp').hide();
            $('#inputLatitudeDeviceValue').hide();
            $('#InsertDataDeviceLoadingIcon').hide();
            $('#NOMob').hide();
            $('#editLatLongValue').hide();
            $('#Mtab').hide();
            $('#Itab').hide();
            document.getElementById('NewValuesInputConfirmButton').style.display = 'none';



            console.log("Insert values pool KO");
            console.log((data));


        }


    });

}





//end of fetch function 



function disableInput(id) {
    InputToDisable = "Value" + id.substring(9, id.length);
    Check_okbutton="#access-code-error"+ id.substring(9, id.length);

    if (document.getElementById(id).checked == false) {
        document.getElementById(InputToDisable).readOnly = true;
         if($(Check_okbutton).is(":visible")){
            document.getElementById('NewValuesInputConfirmButton').disabled = false;
        }
          
    } else if (document.getElementById(id).checked == true) {
        document.getElementById(InputToDisable).readOnly = false;
        if($(Check_okbutton).is(":visible")){
            document.getElementById('NewValuesInputConfirmButton').disabled = true;
        }
    }

}



function CreateJsonNewValue(someData, NameAttrUp, old) {
    //console.log(new_co);
    var attr = {};
    var a = "";

    for (var i in someData) {
          if (document.getElementById(NameAttrUp[i]).checked == true) {
            a= document.getElementById('Value' + NameAttrUp[i].substring(9, NameAttrUp[i].length)).value;
        } else {
              a = old[NameAttrUp[i].substring(9, NameAttrUp[i].length)];
        }
        switch (someData[i].data_type) {

            case "float" || "integer" :
                a = parseFloat(a);
                break;
            case "binary":
                 
                a = parseInt(a).toString(2);
                break;
            case "boolean"  :
            case "switch"  :
            case "button":
                a = Boolean(a);
                break;
            case "date"  :
            case "datatime"  :
            case "time"  :
            case "timestamp":
                a = toISOString(a);
                break;
            case "json"  :
            case "collection"  :
            case "set"  :
            case "vector"  :
            case "shape"  :
            case "wkt" :
                a = JSON.parse(a);
                break;
            case "xlm":
            case "string":
                //a = parseFromString(a, "text/xml");
                break;
        }
       
            attr['' + someData[i].value_name + ''] = {"value": a, "type": someData[i].data_type};
        }


    
    if (!!document.getElementById("inputLatitudeDeviceValue").value) {
        attr["latitude"] = {"value": document.getElementById("inputLatitudeDeviceValue").value, "type": "float"};

        attr["longitude"] = {"value": document.getElementById("inputLongitudeDeviceValue").value, "type": "float"};
    }
    // console.log(attr);
    return attr;
}





//Start ready_state

$(document).ready(function () {

    //fetch_data function will load the device table 	
    fetch_data(false);

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

    //Titolo Default
    if (titolo_default != "") {
        $('#headerTitleCnt').text(titolo_default);
    }

    if (access_denied != "") {
        alert('You need to log in with the right credentials before to access to this page!');
    }

    ///// SHOW FRAME PARAMETER USE/////
    if (nascondi == 'hide') {
        $('#mainMenuCnt').hide();
        $('#title_row').hide();
        $('#mainCnt').removeClass('col-md-10');
        $('#mainCnt').addClass('col-md-12');
    }
    //// SHOW FRAME PARAMETER  ////

    $('#sessionExpiringPopup').css("top", parseInt($('body').height() - $('#sessionExpiringPopup').height()) + "px");
    $('#sessionExpiringPopup').css("left", parseInt($('body').width() - $('#sessionExpiringPopup').width()) + "px");

    setInterval(function () {
        var now = parseInt(new Date().getTime() / 1000);
        var difference = sessionEndTime - now;

        if (difference === 300) {
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

        if (difference === 120) {
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

        if ((difference > 0) && (difference <= 60)) {
            $('#sessionExpiringPopup').show();
            $('#sessionExpiringPopup').css("opacity", "1");
            $('#sessionExpiringPopupTime').html(difference + " seconds");
        }

        if (difference <= 0) {
            location.href = "logout.php?sessionExpired=true";
        }
    }, 1000);

    $('#mainContentCnt').height($('#mainMenuCnt').height() - $('#headerTitleCnt').height());

    $(window).resize(function () {
        $('#mainContentCnt').height($('#mainMenuCnt').height() - $('#headerTitleCnt').height());
        if ($(window).width() < 992) {

            //$('#devicesTable').bootstrapTable('hideColumn', 'devicetype');
            //$('#devicesTable').bootstrapTable('hideColumn', 'model');
            //$('#devicesTable').bootstrapTable('hideColumn', 'visibility');
            //$('#devicesTable').bootstrapTable('hideColumn', 'status1');
            //$('#devicesTable').bootstrapTable('hideColumn', 'owner');
            // $('#devicesTable').bootstrapTable('hideColumn', 'format');
            //$('#devicesTable').bootstrapTable('hideColumn', 'type');            
        } else {
            //$('#devicesTable').bootstrapTable('showColumn', 'devicetype');
            //$('#devicesTable').bootstrapTable('showColumn', 'model');
            //$('#devicesTable').bootstrapTable('showColumn', 'visibility');
            //$('#devicesTable').bootstrapTable('showColumn', 'status1');
            //$('#devicesTable').bootstrapTable('showColumn', 'owner');
            // $('#devicesTable').bootstrapTable('showColumn', 'format');
            //$('#devicesTable').bootstrapTable('showColumn', 'type');           
        }
    });

    $("#addMyNewDeviceRow").hide();

    for (var func = 0; func < functionality.length; func++) {
        var element = functionality[func];
        if (element.view == "view") {
            if (element[loggedRole] == 1) {   // console.log(loggedRole + " " + element[loggedRole] + " " + element["class"]); 
                $(element["class"]).show();
            } else {
                $(element["class"]).hide();
                // console.log($(element.class));
                //  console.log(loggedRole + " " + element[loggedRole] + " " + element["class"]);
            }
        }
    }


    $('#devicesLink .mainMenuItemCnt').addClass("mainMenuItemCntActive");
    $('#mobMainMenuPortraitCnt #devicesLink .mobMainMenuItemCnt').addClass("mainMenuItemCntActive");
    $('#mobMainMenuLandCnt #devicesLink .mobMainMenuItemCnt').addClass("mainMenuItemCntActive");


    fetch_data(false);

    $("#addMyNewDevice").click(function () {

        //console.log("add new device");	
        $("#displayAllDeviceRow").hide();
        $("#addMyNewDeviceRow").show();

        $('#inputNameDeviceUser').val("");
        $('#inputTypeDeviceUser').val("");
        $('#inputLatitudeDeviceUser').val("");
        $('#inputLongitudeDeviceUser').val("");
        // drawMapUser(43.78, 11.23);
        // showAddDeviceModal();



    });


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


    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        var target = $(e.target).attr("href");
        if ((target == '#addGeoPositionTabDevice')) {
            //console.log("Elf: Add Device Map");
            var latitude = 43.78;
            var longitude = 11.23;
            var flag = 0;
            drawMap1(latitude, longitude, flag);
        } else {//nothing
        }
    });


    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        var target = $(e.target).attr("href");
        if ((target == '#editGeoPositionTabDevice')) {
            //console.log("Elf : EditDeviceMap");
            var latitude = $("#inputLatitudeDeviceM").val();
            var longitude = $("#inputLongitudeDeviceM").val();
            var flag = 1;
            drawMap1(latitude, longitude, flag);
        } else {//nothing
        }
    });


    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        var target = $(e.target).attr("href");
        if ((target == '#editStatusTabDevice')) {

            var id = document.getElementById('inputNameDeviceM').value;
            var contextbroker = document.getElementById('selectContextBrokerM').value;
            var type = document.getElementById('inputTypeDeviceM').value;
            var kind = document.getElementById('selectKindDeviceM').value;
            var latitude = document.getElementById('inputLatitudeDeviceM').value;
            var longitude = document.getElementById('inputLongitudeDeviceM').value;
            var protocol = document.getElementById('selectProtocolDeviceM').value;

            if (id == null || id == "") {
                var idNote = ("\n id not specified");
            } else {
                idNote = "&#10004;";
            }
            if (contextbroker == null || contextbroker == "") {
                var contextbrokerNote = ("cb not specified");
            } else {
                contextbrokerNote = "&#10004;";
            }
            if (type == null || type == "") {
                var typeNote = ("type not specified");
            } else {
                typeNote = "&#10004;";
            }
            if (!(kind == "sensor" || kind == "actuator")) {
                var kindNote = ("\n kind not specified");
            } else {
                kindNote = "&#10004;";
            }
            if ((latitude < -90 && latitude > 90) || (latitude == "" || latitude == null)) {
                var latitudeNote = ("\n latitude not correct ");
            } else {
                latitudeNote = "&#10004;";
            }
            if ((longitude < -180 && longitude > 180) || (longitude == "" || longitude == null)) {
                var longitudeNote = ("\n longitude not correct ");
            } else {
                longitudeNote = "&#10004;";
            }
            if (!(protocol == "ngsi" || protocol == "mqtt" || protocol == "amqp" || protocol == "ngsi w/MultiService")) {
                var protocolNote = ("protocol not correct ");
            } else {
                protocolNote = "&#10004;";
            }

            //console.log(id + contextbroker + type + kind + latitude + longitude + protocol);

            if ((idNote == "&#10004;") && (contextbrokerNote == "&#10004;") && (typeNote == "&#10004;") && (kindNote == "&#10004;") && (latitudeNote == "&#10004;") && (longitudeNote == "&#10004;") && (protocolNote == "&#10004;")) {
                var statusNote = "<button class=\"btn btn-success btn-round\"></button>";
            } else {
                statusNote = "<button class=\"btn btn-danger btn-round\"></button>";
            }

            var x = inputPropertiesDeviceMMsg.innerHTML;

            var div = document.createElement("div");
            //console.log("IPDMM:" + x);

            if (x == "&nbsp;") {
            } else {
                inputPropertiesDeviceMMsg.innerHTML = "";
            }

            div.innerHTML = ("<div style=\"border:3px solid blue;\" >" +
                    "<h2>Device Status</h2>" +
                    "<table class=\"table\"><thead><tr><th>Property Status</th><th> checked</th></tr></thead>" +
                    "<tbody><tr><td>id</td><td>" + idNote + "</td></tr>" +
                    "<tr><td>Contextbroker</td><td>" + contextbrokerNote + "</td></tr>" +
                    "<tr><td>Type</td><td>" + typeNote + "</td></tr>" +
                    "<tr><td>Kind</td><td>" + kindNote + " </td></tr>" +
                    "<tr><td>Protocol</td><td>" + protocolNote + "</td></tr>" +
                    "<tr><td>Latitude</td><td>" + latitudeNote + " </td></tr>" +
                    "<tr><td>Longitude</td><td>" + longitudeNote + "</td></tr>" +
                    "<tr><td>Overall Status</td><td>" + statusNote + "</td></tr>" +
                    "</tbody></table></div>");
            inputPropertiesDeviceMMsg.appendChild(div);


        }


    });



    $("#selectEdgeGatewayType").click(function () {
        checkUri();
        checkAddDeviceConditions();
    });

    $("#selectModelDevice").click(function () {

        var nameOpt = document.getElementById('selectModelDevice').options;
        var selectednameOpt = document.getElementById('selectModelDevice').selectedIndex;
        var ownerSelect = document.getElementById('selectVisibilityDevice').options;
        var ownerOpt = document.getElementById('selectVisibilityDevice').selectedIndex;
        checkModel();

        //Fatima3	 
        if ((nameOpt[selectednameOpt].value != "custom") && (nameOpt[selectednameOpt].value != ""))
                //if (nameOpt[selectednameOpt].value !="custom") 
                {
                    $("#addNewDeviceGenerateKeyBtn").hide();

                    var gb_device = document.getElementById('inputNameDevice').value;
                    var gb_latitude = document.getElementById('inputLatitudeDevice').value;
                    var gb_longitude = document.getElementById('inputLongitudeDevice').value;

                    if (nameOpt[selectednameOpt].getAttribute("data_key") != "special") // && ownerSelect[ownerOpt].value=='private')
                    {

                        if ($("#KeyOneDeviceUser").val() == "") {
                            $("#sigFoxDeviceUserMsg").val("");


                            $("#KeyOneDeviceUserMsg").html("");
                            $("#KeyTwoDeviceUserMsg").html("");

                            $("#sigFoxDeviceUserMsg").html("These keys have been generated automatically for your device. Keep track of them. Details on <a href=\"https://www.snap4city.org/drupal/node/76\">info</a>");

                            $("#KeyOneDeviceUser").val(generateUUID());
                            $("#KeyTwoDeviceUser").val(generateUUID());
                        }
                    }

                    if (nameOpt[selectednameOpt].getAttribute("data_key") == "special") // && ownerSelect[ownerOpt].value=='private')
                    {
                        $("#sigFoxDeviceUserMsg").html("Generate in your SigFox server the keys and report them here.  Details on <a href=\"https://www.snap4city.org/drupal/node/76\">info</a>");
                        $("#KeyOneDeviceUser").val("");
                        $("#KeyTwoDeviceUser").val("");
                        // console.log("special "+ nameOpt[selectednameOpt].getAttribute("data_key")+gb_key1+gb_key2);							 
                    }


                    //console.log(nameOpt[selectednameOpt].value + " " + gb_device + " " + gb_longitude + " " + gb_latitude);

                    $.ajax({
                        url: "../api/model.php",
                        data: {
                            action: "get_model",
                            //organization : organization, 
                            name: nameOpt[selectednameOpt].value,
                            token: sessionToken
                        },
                        type: "POST",
                        async: true,
                        datatype: 'json',
                        success: function (data) {

                            if (data["status"] === 'ko') {
                                // data = data["content"];
                                alert("An error occured when reading the data. <br/> Get in touch with the Snap4City Administrator<br/>" + data["msg"]);
                            } else
                                (data["status"] === 'ok')
                            {
                                //console.log(data.content.attributes);

                                var model = data.content.name;
                                var type = data.content.devicetype;
                                var kind = data.content.kind;
                                var producer = data.content.producer;
                                //var mac = data.content.mac;
                                var frequency = data.content.frequency;
                                var contextbroker = data.content.contextbroker;
                                //var protocol = data.content.protocol;
                                var format = data.content.format;
                                var myattributes = JSON.parse(data.content.attributes);
                                var k = 0;
                                var content = "";
                                // population of the value tab with the values taken from the db						
                                while (k < myattributes.length) {
                                    //console.log(myattributes.length + " " +k); 
                                    content = drawAttributeMenu(myattributes[k].value_name,
                                            myattributes[k].data_type, myattributes[k].value_type, myattributes[k].editable, myattributes[k].value_unit, myattributes[k].healthiness_criteria,
                                            myattributes[k].healthiness_value, myattributes[k].value_name, 'addlistAttributes', indexValues);
                                    indexValues = indexValues + 1;
                                    k++;

                                    $('#addlistAttributes').append(content);
                                    $("#editSchemaTabDevice #addlistAttributes .row input:even").on('input', checkEditValueName);
                                    $("#editSchemaTabDevice #addlistAttributes .row input:even").on('input', checkEditDeviceConditions);
                                    // $("#addSchemaTabDevice #addlistAttributes .row input:even").each(function(){checkValueName();});

                                    checkEditDeviceConditions();
                                }


                                $('#inputTypeDevice').val(data.content.devicetype);
                                $('#selectKindDevice').val(data.content.kind);
                                $('#inputProducerDevice').val(data.content.producer);
                                $('#inputFrequencyDevice').val(data.content.frequency);
                                //$('#inputMacDevice').val(data.content.mac);
                                $('#selectContextBroker').val(data.content.contextbroker);
                                $('#selectProtocolDevice').val(data.content.protocol);
                                $('#selectFormatDevice').val(data.content.format);

                                //console.log(data.content.edgegateway_type);

                                $('#selectinputEdgeGatewayType').val(data.content.edgegateway_type);

                                addDeviceConditionsArray['contextbroker'] = true;
                                addDeviceConditionsArray['kind'] = true;
                                addDeviceConditionsArray['format'] = true;
                                addDeviceConditionsArray['protocol'] = true;
                                checkSelectionCB();
                                checkSelectionKind();
                                checkSelectionProtocol();
                                checkSelectionFormat();

                                addDeviceConditionsArray['inputTypeDevice'] = true;
                                checkDeviceType(); // checkAddDeviceConditions();
                                addDeviceConditionsArray['inputFrequencyDevice'] = true;
                                checkFrequencyType(); // checkAddDeviceConditions();
                                addDeviceConditionsArray['inputMacDevice'] = true;
                                checkMAC(); //checkAddDeviceConditions();
                                checkAtlistOneAttribute();
                                checkAddDeviceConditions();

                            }
                        },
                        error: function (data) {
                            console.log("Ko result: " + JSON.stringify(data));
                            $('#addlistAttributes').html("");

                            $('#inputTypeDevice').val("");
                            $('#selectKindDevice').val("");
                            $('#inputProducerDevice').val("");
                            $('#inputFrequencyDevice').val("600");
                            $('#inputMacDevice').val("");
                            $('#selectContextBroker').val("");
                            $('#selectProtocolDevice').val("");
                            $('#selectFormatDevice').val("");
                            alert("An error occured when reading the information about model. <br/> Try again or get in touch with the Snap4City Administrator<br/>");

                            // $("#addDeviceModal").modal('hide');								
                        }

                    });

                    if (nameOpt[selectednameOpt].getAttribute("data_key") != "special") {
                        $("#KeyOneDeviceUser").attr({'disabled': 'disabled'});
                        $("#KeyTwoDeviceUser").attr({'disabled': 'disabled'});
                    } else {
                        $("#KeyOneDeviceUser").removeAttr('disabled');
                        $("#KeyTwoDeviceUser").removeAttr('disabled');
                    }

                } else if (nameOpt[selectednameOpt].value == "") { // case not specified
            $('#inputTypeDevice').val("");
            //$('#selectKindDevice').val("");
            $('#inputProducerDevice').val("");
            $('#inputFrequencyDevice').val("600");

            $("#sigFoxDeviceUserMsg").html("");

            $('#inputMacDevice').val("");
            $('#selectContextBroker').val("");
            $('#selectProtocolDevice').val("");
            $('#selectFormatDevice').val("");
            $("#KeyOneDeviceUser").val("");
            $("#KeyTwoDeviceUser").val("");
            $('#KeyOneDeviceUserMsg').html("");
            $('#KeyTwoDeviceUserMsg').html("");
            $('#KeyOneDeviceUserMsg').val("");
            $('#KeyTwoDeviceUserMsg').val("");
            // $('#addlistAttributes').html("");

            addDeviceConditionsArray['contextbroker'] = false;
            addDeviceConditionsArray['kind'] = false;
            addDeviceConditionsArray['format'] = false;
            addDeviceConditionsArray['protocol'] = false;
            checkSelectionCB();
            checkSelectionKind();
            checkSelectionProtocol();
            checkSelectionFormat();

            addDeviceConditionsArray['inputTypeDevice'] = false;
            checkDeviceType();
            addDeviceConditionsArray['inputFrequencyDevice'] = false;
            checkFrequencyType();
            addDeviceConditionsArray['inputMacDevice'] = false;
            checkMAC();
            document.getElementById('addlistAttributes').innerHTML = "";
            $("#addNewDeviceGenerateKeyBtn").hide();
            checkAtlistOneAttribute();
            checkAddDeviceConditions();

        } else // case custom 
        {
            $("#addNewDeviceGenerateKeyBtn").show();
            $("#sigFoxDeviceUserMsg").html("Click on the generatekey botton to generate keys (if you need them)");

            if ($('#inputTypeDevice').val() == "")
                addDeviceConditionsArray['inputTypeDevice'] = false;
            else
                addDeviceConditionsArray['inputTypeDevice'] = true;
            checkDeviceType();
            checkAddDeviceConditions();
            if ($('#inputFrequencyDevice').val() == "")
                addDeviceConditionsArray['inputFrequencyDevice'] = false;
            else
                addDeviceConditionsArray['inputFrequencyDevice'] = true;
            checkFrequencyType();
            checkAddDeviceConditions();
            if ($('#inputMacDevice').val() == "")
                addDeviceConditionsArray['inputMacDevice'] = false;
            else
                addDeviceConditionsArray['inputMacDevice'] = true;
            checkMAC();
            checkAddDeviceConditions();

            $("#KeyOneDeviceUser").removeAttr('disabled');
            $("#KeyTwoDeviceUser").removeAttr('disabled');
        }

    });


    $('#displayDevicesMap').off('click');
    $('#displayDevicesMap').click(function () {

        $.ajax({
            url: "../api/device.php",
            data: {
                action: "get_all_device_latlong",
                organization: organization,
                loggedrole: loggedRole,
                username: loggedUser,
                token: sessionToken
            },
            type: "POST",
            async: true,
            datatype: 'json',
            success: function (data) {

                if (data["status"] === 'ko') {
                    alert("An error occured when reading the data. <br/> Get in touch with the Snap4City Administrator<br/>" + data["msg"]);
                    //data = data["content"];
                } else
                    (data["status"] === 'ok')
                {
                    var data = data["content"];
                    $("#addMap1").modal('show');
                    drawMapAll(data, 'searchDeviceMapModalBody');
                }
            },
            error: function (data) {
                console.log("Ko result: " + data);
                alert("Network errors. <br/> Get in touch with the Snap4City Administrator<br/>" + JSON.stringify(data));
            }

        });
    });


    //ADD NEW DEVICE  (INSERT INTO DB) 


    // add lines related to attributes

    $("#addAttrBtn").off("click");
    $("#addAttrBtn").click(function () {
        //console.log("#addAttrBtn");							   
        content = drawAttributeMenu("", "", "", "", "", "", "300", "", 'addlistAttributes', indexValues);
        indexValues = indexValues + 1;
        $('#addlistAttributes').append(content);
        checkAtlistOneAttribute();

        $("#addSchemaTabDevice #addlistAttributes .row input:even").each(function () {
            checkValueName($(this));
        });
        checkAddDeviceConditions();
    });


    $("#addSchemaTabDevice").off("click");
    $("#addSchemaTabDevice").click(function () {
        //console.log("#addAttrMBtn");					
        //checkAtlistOneAttribute();
        // $("#addSchemaTabDevice #addlistAttributes .row input:even").each(function(){checkValueName();});
        checkAddDeviceConditions();
    });



    // Add New Device 

    $("#addDeviceBtn").off("click");
    $("#addDeviceBtn").click(function () {
        // console.log(admin);
        $("#addDeviceModal").modal('show');
        // $("#addDeviceModalBody").modal('show');
        $("#addDeviceLoadingMsg").hide();
        $("#addDeviceLoadingIcon").hide();
        $("#addDeviceOkMsg").hide();
        $("#addDeviceOkIcon").hide();
        $("#addDeviceKoMsg").hide();
        $("#addDeviceKoIcon").hide();
        $("#addNewDeviceGenerateKeyBtn").hide();

        showAddDeviceModal();

    });


    // add lines related to attributes 

    $("#addAttrBtnUser").off("click");
    $("#addAttrBtnUser").click(function () {
        //console.log("#addAttrBtnUser");							   
        content = drawAttributeMenu("", "", "", "", "", "", "300", "", 'addlistAttributes', indexValues);
        indexValues = indexValues + 1;
        $('#addlistAttributesUser').append(content);
    });

    $('#addNewDeviceConfirmBtn').off("click");
    $('#addNewDeviceConfirmBtn').click(function () {

        mynewAttributes = [];
        num1 = document.getElementById('addlistAttributes').childElementCount;
        for (var m = 0; m < num1; m++) {
            var newatt = {
                
                value_name: document.getElementById('addlistAttributesM').childNodes[m].childNodes[0].childNodes[0].childNodes[0].value.trim(),
                data_type: document.getElementById('addlistAttributesM').childNodes[m].childNodes[4].childNodes[0].childNodes[0].value.trim(),
                value_type: document.getElementById('addlistAttributesM').childNodes[m].childNodes[1].childNodes[0].childNodes[0].value.trim(),
                editable:'0',
                value_unit: document.getElementById('addlistAttributesM').childNodes[m].childNodes[2].childNodes[0].childNodes[0].value.trim(),
                
                healthiness_criteria: document.getElementById('addlistAttributesM').childNodes[m].childNodes[6].childNodes[0].childNodes[0].value.trim(),
                healthiness_value: document.getElementById('addlistAttributesM').childNodes[m].childNodes[7].childNodes[0].childNodes[0].value.trim(),
                old_value_name: document.getElementById('addlistAttributesM').childNodes[m].childNodes[9].childNodes[0].childNodes[0].value.trim()
                
            };

            if (newatt.value_name!=$('#inputNameDevice').value && newatt.value_name != "" && newatt.data_type != "" && newatt.value_type != "" && newatt.editable != "" && newatt.healthiness_criteria != "" && newatt.healthiness_value != "")
                mynewAttributes.push(newatt);
        }
        document.getElementById('addlistAttributes').innerHTML = "";


        $("#addDeviceModalTabs").hide();
        $('#addDeviceModal div.modalCell').hide();
        $("#addDeviceModalFooter").hide();
        $("#addAttrBtn").hide();
        $('#addDeviceLoadingMsg').show();
        $('#addDeviceLoadingIcon').show();
        //console.log("LISTA" + JSON.stringify(mynewAttributes));


        $.ajax({
            url: "../api/device.php",
            data: {
                action: "insert",
                attributes: JSON.stringify(mynewAttributes),
                id: $('#inputNameDevice').val(),
                type: $('#inputTypeDevice').val(),
                kind: $('#selectKindDevice').val(),
                contextbroker: $('#selectContextBroker').val(),
                organization: organization,
                protocol: $('#selectProtocolDevice').val(),
                format: $('#selectFormatDevice').val(),
                mac: $('#inputMacDevice').val(),
                model: $('#selectModelDevice').val(),
                producer: $('#inputProducerDevice').val(),
                latitude: $('#inputLatitudeDevice').val(),
                longitude: $('#inputLongitudeDevice').val(),
                visibility: $('#selectVisibilityDevice').val(),
                frequency: $('#inputFrequencyDevice').val(),
                token: sessionToken,
                k1: $("#KeyOneDeviceUser").val(),
                k2: $("#KeyTwoDeviceUser").val(),
                edgegateway_type: $("#selectEdgeGatewayType").val(),
                edgegateway_uri: $("#inputEdgeGatewayUri").val(),
                subnature: $('#selectSubnature').val(),
                static_attributes: JSON.stringify(retrieveStaticAttributes("addStaticTabModel", false))

            },
            type: "POST",
            async: true,
            dataType: "JSON",
            timeout: 0,
            success: function (mydata) {
                if (mydata["status"] === 'ko') {
                    /* 
                     console.log("Error adding Device type");
                     console.log(mydata);
                     $('#addDeviceLoadingMsg').hide();
                     $('#addDeviceLoadingIcon').hide();
                     $('#addDeviceKoMsg').show();
                     $('#addDeviceKoIcon').show();
                     
                     setTimeout(function(){
                     
                     $('#addDeviceKoMsg').hide();
                     $('#addDeviceKoIcon').hide();
                     $('#addDeviceModalTabs').show();
                     $('#addDeviceModal div.modalCell').show();
                     $('#addDeviceModalFooter').show();
                     }, 3000);
                     */

                    console.log("Error adding Device type");
                    console.log(mydata);
                    $('#addDeviceLoadingMsg').hide();
                    $('#addDeviceLoadingIcon').hide();
                    $('#addDeviceKoMsg').show();
                    $('#addDeviceKoMsg div:first-child').html(mydata["msg"]);
                    $('#addDeviceKoIcon').show();
                    // $("#addDeviceModalInnerDiv1").html(mydata["msg"]);
                    // $("#addDeviceModalInnerDiv2").html('<i class="fa fa-frown-o" style="font-size:42px"></i>');


                    setTimeout(function () {
                        $('#addDeviceModal').modal('hide');
                        //  buildMainTable(true);

                        setTimeout(function () {

                            $('#addDeviceKoMsg').hide();
                            $('#addDeviceKoIcon').hide();

                            $('#inputNameDevice').val("");
                            $('#inputTypeDevice').val("");
                            $('#selectContextBroker').val("NULL");
                            $('#inputUriDevice').val("");
                            $('#selectProtocolDevice').val("NULL");
                            $('#selectFormatDevice').val("NULL");
                            $('#createdDateDevice').val("");
                            $('#inputMacDevice').val("");
                            $('#selectModelDevice').val("");
                            $('#inputProducerDevice').val("");
                            $('#inputLatitudeDevice').val("");
                            $('#inputLongitudeDevice').val("");
                            $('#inputLongitudeDevice').val("");
                            $('#selectVisibilityDevice').val("NULL");
                            $('#inputFrequencyDevice').val("600");
                            $("#KeyOneDeviceUser").val("");
                            $("#KeyTwoDeviceUser").val("");
                            $("#KeyOneDeviceUserMsg").html("");
                            $("#KeyTwoDeviceUserMsg").html("");

                            location.reload();

                            $('#addDeviceModalTabs').show();
                            $('#addDeviceModal div.modalCell').show();
                            $('#addDeviceModalFooter').show();
                        }, 500);
                    }, 3000);





                } else
                    (mydata["status"] === 'ok')
                {
                    //console.log("Added Device");
                    $('#addDeviceLoadingMsg').hide();
                    $('#addDeviceLoadingIcon').hide();
                    $('#addDeviceKoMsg').hide();
                    $('#addDeviceKoIcon').hide();
                    $('#addDeviceOkMsg').show();
                    $('#addDeviceOkIcon').show();

                    $('#dashboardTotNumberCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotNumberCnt .pageSingleDataCnt').html()) + 1);
                    if (mydata["active"])
                        $('#dashboardTotActiveCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotActiveCnt .pageSingleDataCnt').html()) + 1);
                    if (mydata["visibility"] == "public")
                        $('#dashboardTotPermCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotPermCnt .pageSingleDataCnt').html()) + 1);
                    else
                        $('#dashboardTotPrivateCn .pageSingleDataCnt').html(parseInt($('#dashboardTotPrivateCn .pageSingleDataCnt').html()) + 1);

                    // dashboardTotPermCnt
                    setTimeout(function () {
                        $('#addDeviceModal').modal('hide');
                        $('#devicesTable').DataTable().destroy();
                        fetch_data(true);
                        setTimeout(function () {

                            $('#addDeviceOkMsg').hide();
                            $('#addDeviceOkIcon').hide();

                            $('#inputNameDevice').val("");
                            $('#inputTypeDevice').val("");
                            $('#selectContextBroker').val("NULL");
                            $('#inputUriDevice').val("");
                            $('#selectProtocolDevice').val("NULL");
                            $('#selectFormatDevice').val("NULL");
                            $('#createdDateDevice').val("");
                            $('#inputMacDevice').val("");
                            $('#selectModelDevice').val("");
                            $('#inputProducerDevice').val("");
                            $('#inputLatitudeDevice').val("");
                            $('#inputLongitudeDevice').val("");
                            $('#inputLongitudeDevice').val("");
                            $('#selectVisibilityDevice').val("NULL");
                            $('#inputFrequencyDevice').val("600");

                            $('#addDeviceModalTabs').show();
                            $('#addDeviceModal div.modalCell').show();
                            $('#addDeviceModalFooter').show();
                        }, 500);
                    }, 3000);

                }

            },
            error: function (mydata) {
                console.log("Error insert device");
                console.log("Error status -- Ko result: " + JSON.stringify(mydata));
                $('#addDeviceLoadingMsg').hide();
                $('#addDeviceLoadingIcon').hide();
                $('#addDeviceKoMsg').show();
                $('#addDeviceKoIcon').show();
                setTimeout(function () {
                    $('#addDeviceKoMsg').hide();
                    $('#addDeviceKoIcon').hide();
                    $('#addDeviceModal').hide();

                    $('#addDeviceOkMsg').hide();
                    $('#addDeviceOkIcon').hide();

                    $('#inputNameDevice').val("");
                    $('#inputTypeDevice').val("");
                    $('#selectContextBroker').val("NULL");
                    $('#inputUriDevice').val("");
                    $('#selectProtocolDevice').val("NULL");
                    $('#selectFormatDevice').val("NULL");
                    $('#createdDateDevice').val("");
                    $('#inputMacDevice').val("");
                    $('#selectModelDevice').val("");
                    $('#inputProducerDevice').val("");
                    $('#inputLatitudeDevice').val("");
                    $('#inputLongitudeDevice').val("");
                    $('#inputLongitudeDevice').val("");
                    $('#selectVisibilityDevice').val("NULL");
                    $('#inputFrequencyDevice').val("600");
                    $("#KeyOneDeviceUser").val("");
                    $("#KeyTwoDeviceUser").val("");
                    $("#KeyOneDeviceUserMsg").html("");
                    $("#KeyTwoDeviceUserMsg").html("");

                    location.reload();

                    // $('#addDeviceModalTabs').show();
                    // $('#addDeviceModal div.modalCell').show();
                    // $('#addDeviceModalFooter').show();
                }, 3000);

            }
        });
    });

    //Start Related to Delete Device

    // Delete lines related to attributes 
    $("#attrNameDelbtn").off("click");
    $("#attrNameDelbtn").on("click", function () {
        //console.log("#attrNameDelbtn");	
        $(this).parent('tr').remove();
    });

    //Delete button hover - needs to be checked
    $('#devicesTable button.delDashBtn').off('hover');
    $('#devicesTable button.delDashBtn').hover(function () {
        $(this).css('background', '#ffcc00');
        $(this).parents('tr').find('td').eq(1).css('background', '#ffcc00');
    },
            function () {
                $(this).css('background', '#e37777');
                $(this).parents('tr').find('td').eq(1).css('background', $(this).parents('td').css('background'));
            });


    //Delete device button 	
    $('#devicesTable tbody').on('click', 'button.delDashBtn', function () {

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

    //DELETE DEVICE (DELETE FROM DB) 			
    $('#deleteDeviceConfirmBtn').off("click");
    $("#deleteDeviceConfirmBtn").click(function () {
        var id = $("#deleteDeviceModal span").attr("data-id");
        var contextbroker = $("#deleteDeviceModal span").attr("data-contextBroker");
        var dev_organization = $("#deleteDeviceModal span").attr("data-organization");
        var uri = $("#deleteDeviceModal span").attr("data-uri");
        var protocol = $("#deleteDeviceModal span").attr("data-protocol");
        var service = $("#deleteDeviceModal span").attr("data-service");
        var servicePath = $("#deleteDeviceModal span").attr("data-servicepath");
        if (service === "null")
            service = "";
        if (servicePath === "null")
            servicePath = "";

        $("#deleteDeviceModal div.modal-body").html("");
        $("#deleteDeviceOkBtn").hide();
        $("#deleteDeviceCancelBtn").hide();
        $("#deleteDeviceConfirmBtn").hide();
        $("#deleteDeviceModalInnerDiv1").show();
        $("#deleteDeviceModalInnerDiv2").show();


        $.ajax({
            url: "../api/device.php",
            data: {
                action: "delete",
                id: id,
                uri: uri,
                /*Sara711 - logging*/
                username: loggedUser,
                contextbroker: contextbroker,
                organization: organization,
                dev_organization: dev_organization,
                token: sessionToken,
                protocol: protocol,
                service: service,
                servicePath: servicePath
            },
            type: "POST",
            datatype: "json",
            async: true,
            success: function (data) {
                //console.log(JSON.stringify(data));
                $("#deleteDeviceOkBtn").show();
                if (data["status"] === 'ko') {
                    $("#deleteDeviceModalInnerDiv1").html(data["error_msg"]);
                    $("#deleteDeviceModalInnerDiv2").html('<i class="fa fa-frown-o" style="font-size:42px"></i>');
                } else if (data["status"] === 'ok') {
                    $("#deleteDeviceModalInnerDiv1").html('Device &nbsp; <b>' + id + '</b> &nbsp;deleted successfully');
                    $("#deleteDeviceModalInnerDiv1").show();
                    $("#deleteDeviceModalInnerDiv2").html('<i class="fa fa-check" style="font-size:42px"></i>');


                    $('#dashboardTotNumberCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotNumberCnt .pageSingleDataCnt').html()) - 1);
                    if (data["active"])
                        $('#dashboardTotActiveCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotActiveCnt .pageSingleDataCnt').html()) - 1);
                    if (data["visibility"] == "public")
                        $('#dashboardTotPermCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotPermCnt .pageSingleDataCnt').html()) - 1);
                    else
                        $('#dashboardTotPrivateCn .pageSingleDataCnt').html(parseInt($('#dashboardTotPrivateCn .pageSingleDataCnt').html()) - 1);

                    $('#devicesTable').DataTable().destroy();
                    fetch_data(true);

                    // $('#dashboardTotNumberCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotNumberCnt .pageSingleDataCnt').html()) - 1);
                    // $('#dashboardTotActiveCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotActiveCnt .pageSingleDataCnt').html()) - 1);

                }
            },
            error: function (data) {
                $("#deleteDeviceOkBtn").show();
                console.log(JSON.stringify(data));
                $("#deleteDeviceModalInnerDiv1").html(data["error_msg"]);
                $("#deleteDeviceModalInnerDiv2").html('<i class="fa fa-frown-o" style="font-size:42px"></i>');

            }
        });
    });

    $("#deleteDeviceOkBtn").off("click");
    $("#deleteDeviceOkBtn").click(function () {
        $("#deleteDeviceModal div.modal-body").html("Do you want to confirm deletion of the following device?");
        $("#deleteDeviceOkBtn").hide();
        $("#deleteDeviceCancelBtn").show();
        $("#deleteDeviceConfirmBtn").show();
        $("#deleteDeviceModalInnerDiv1").html('<h5>Device deletion in progress, please wait</h5>');
        $("#deleteDeviceModalInnerDiv2").html('<i class="fa fa-circle-o-notch fa-spin" style="font-size:36px"></i>');
        $("#deleteDeviceModalInnerDiv1").hide();
        $("#deleteDeviceModalInnerDiv2").hide();
    });

    //END DELETE DEVICE  		

    // Start Related to Edit Device

    // add lines related to attributes in case of edit
    $("#addAttrMBtn").off("click");
    $("#addAttrMBtn").click(function () {
        content = drawAttributeMenu("", "", "", "", "", "", "300", "", 'addlistAttributesM', indexValues)
        indexValues = indexValues + 1;
        //editDeviceConditionsArray['addlistAttributesM'] = true;
        $('#addlistAttributesM').append(content);

        checkEditAtlistOneAttribute();
        $("#editSchemaTabDevice #addlistAttributesM .row input:even").each(function () {
            checkEditValueName($(this));
        });
        $("#editSchemaTabDevice #editlistAttributes .row input:even").each(function () {
            checkEditValueName($(this));
        });
        checkEditDeviceConditions();
    });

    $("#editSchemaTabDevice").off("click");
    $("#editSchemaTabDevice").on('click keyup', function () {
        //console.log("#editSchemaTabDevice");

        //checkEditAtlistOneAttribute();
        $("#editSchemaTabDevice #addlistAttributesM .row input:even").each(function () {
            checkEditValueName($(this));
        });
        $("#editSchemaTabDevice #editlistAttributes .row input:even").each(function () {
            checkEditValueName($(this));
        });
        checkEditDeviceConditions();
    });


    //Edit button in dataTable 
    $('#devicesTable tbody').on('click', 'button.editDashBtn', function () {

        $("#editDeviceModalTabs").show();
        $('#editDeviceModal div.modalCell').show();
        $("#editDeviceCancelBtn").show();
        $("#editDeviceConfirmBtn").show();
        $("#addAttrMBtn").show();
        $("#editDeviceModalBody").show();
        $('#editDeviceLoadingMsg').hide();
        $('#editDeviceLoadingIcon').hide();
        $('#editDeviceOkMsg').hide();
        $('#editDeviceOkIcon').hide();
        $('#editDeviceKoMsg').hide();
        $('#editDeviceKoIcon').hide();
        $('#editDeviceOkBtn').hide();

        $("#editDeviceModalLabel").html("Edit device -  " + $(this).attr("data-id"));
        var id = $(this).attr('data-id');

        //if the user changed the device to edit, clean the list of value and update the currentEditId
        document.getElementById('editlistAttributes').innerHTML = "";
        document.getElementById('addlistAttributesM').innerHTML = "";
        document.getElementById('deletedAttributes').innerHTML = "";

        var contextbroker = $(this).attr('data-contextBroker');
        gb_old_cb = contextbroker;
        var type = $(this).attr('data-devicetype');
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
        //console.log(key1 + key2);

        if (model == "custom")
            $("#editDeviceGenerateKeyBtn").show();
        else
            $("#editDeviceGenerateKeyBtn").hide();

        $('#inputNameDeviceM').val($(this).attr('data-id'));
        $('#inputOrganizationDeviceM').val($(this).attr('data-organization'));
        $('#selectContextBrokerM').val(contextbroker);
        $('#inputTypeDeviceM').val(type);
        $('#selectKindDeviceM').val(kind);
        $('#inputUriDeviceM').val(uri);
        $('#selectProtocolDeviceM').val(protocol);
        $('#selectFormatDeviceM').val(format);
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
        $('#selectSubnatureM').trigger('change');
        subnatureChanged(true, JSON.parse(atob($(this).attr("data-static-attributes"))));

        if ($("#isMobileTickM").is(":checked"))
            $("#positionMsgHintM").show();
        else
            $("#positionMsgHintM").hide();

        fillMultiTenancyFormSection($(this).attr('data-service'), $(this).attr('data-servicePath'), contextbroker, 'device');

        //showEditDeviceModal();

        $.ajax({
            url: "../api/device.php",
            data: {
                action: "get_device_attributes",
                id: $(this).attr("data-id"),
                organization: organization,
                token: sessionToken,
                contextbroker: $(this).attr("data-contextBroker"),
                protocol: $(this).attr('data-protocol'),
                service: $(this).attr('data-service'),
                servicePath: $(this).attr('data-servicePath')
            },
            type: "POST",
            async: true,
            dataType: 'json',
            success: function (mydata) {
                var row = null;
                $("#editUserPoolsTable tbody").empty();
                myattributes = mydata['content'];
                content = "";
                k = 0;
                while (k < myattributes.length) {
                    // console.log(k); 
                    content = drawAttributeMenu(myattributes[k].value_name,
                            myattributes[k].data_type, myattributes[k].value_type, myattributes[k].editable, myattributes[k].value_unit, myattributes[k].healthiness_criteria,
                            myattributes[k].healthiness_value, myattributes[k].value_name, 'editlistAttributes', indexValues);
                    indexValues = indexValues + 1;
                    k++;

                    $('#editlistAttributes').append(content);
                    $("#editSchemaTabDevice #editlistAttributes .row input:even").each(function () {
                        checkEditValueName($(this));
                    });
                    checkEditDeviceConditions();
                    checkEditAtlistOneAttribute();
                }

            },
            error: function (data) {
                console.log("Get values pool KO");
                console.log(JSON.stringify(data));
                alert("Error in reading data from the database<br/> Please get in touch with the Snap4city Administrator");

                $('#inputNameDeviceM').val("");
                $('#inputOrganizationDeviceM').val("");
                $('#selectContextBrokerM').val("");
                $('#inputTypeDeviceM').val("");
                //$('#selectKindDeviceM').val("");
                $('#inputUriDeviceM').val("");
                $('#selectProtocolDeviceM').val("");
                $('#selectFormatDeviceM').val("");
                $('#createdDateDeviceM').val("");
                $('#inputMacDeviceM').val("");
                $('#selectModelDeviceM').val("");
                $('#inputProducerDeviceM').val("");
                $('#inputLatitudeDeviceM').val("");
                $('#inputLongitudeDeviceM').val("");
                $('#inputFrequencyDeviceM').val("");
                $('#selectVisibilityDeviceM').val("");
                $('#editlistAttributes').html("");
                $('#KeyOneDeviceUserM').val("");
                $('#KeyTwoDeviceUserM').val("");


                // $("#editDeviceModal").modal('hide');

            }
        });
        
        showEditDeviceModal();
        // $("#editDeviceConfirmBtn").disabled=false;
    });

    //EDIT button hover - needs to be checked
    $('#devicesTable tbody button.editDashBtn').off('hover');
    $('#devicesTable tbody button.editDashBtn').hover(function () {
        $(this).css('background', '#ffcc00');
        $(this).parents('tr').find('td').eq(1).css('background', '#ffcc00');
    },
            function () {
                $(this).css('background', 'rgb(69, 183, 175)');
                $(this).parents('tr').find('td').eq(1).css('background', $(this).parents('td').css('background'));
            });

    //EDIT DEVICE (DELETE FROM DB)


   // $('#editDeviceConfirmBtn').off("click");
    $("#editDeviceConfirmBtn").click(function () {
        // $("#editDeviceModalBody").hide();
        // $("#editDeviceModalFooter").hide();
        // $("#editDeviceModalUpdating").show();

        mynewAttributes = [];
        var regex = /[^a-z0-9_-]/gi;
        var someNameisWrong = false;
        num1 = document.getElementById('addlistAttributesM').childElementCount;
        //console.log(num1);
        for (var m = 0; m < num1; m++) {
            //var selOpt= document.getElementById('addlistAttributesM').childNodes[m].childNodes[2].childNodes[0].childNodes[0].options;
            //var selIndex= document.getElementById('addlistAttributesM').childNodes[m].childNodes[2].childNodes[0].childNodes[0].selectedIndex;
            var newatt = {
                value_name: document.getElementById('addlistAttributesM').childNodes[m].childNodes[0].childNodes[0].childNodes[0].value.trim(),
                data_type: document.getElementById('addlistAttributesM').childNodes[m].childNodes[4].childNodes[0].childNodes[0].value.trim(),
                value_type: document.getElementById('addlistAttributesM').childNodes[m].childNodes[1].childNodes[0].childNodes[0].value.trim(),
                editable:'0',
                value_unit: document.getElementById('addlistAttributesM').childNodes[m].childNodes[2].childNodes[0].childNodes[0].value.trim(),
                
                healthiness_criteria: document.getElementById('addlistAttributesM').childNodes[m].childNodes[6].childNodes[0].childNodes[0].value.trim(),
                healthiness_value: document.getElementById('addlistAttributesM').childNodes[m].childNodes[7].childNodes[0].childNodes[0].value.trim(),
                old_value_name: document.getElementById('addlistAttributesM').childNodes[m].childNodes[9].childNodes[0].childNodes[0].value.trim()
            };


            if (newatt.value_name != "" && !regex.test(newatt.value_name) && newatt.value_name.length >= 3 && newatt.data_type != "" && newatt.value_type != "" && newatt.editable != "" && newatt.value_unit != "" && newatt.healthiness_criteria != "" && newatt.healthiness_value != "")
                mynewAttributes.push(newatt);
            else
                someNameisWrong = true;


        }

        myAttributes = [];
        num = document.getElementById('editlistAttributes').childElementCount;
        for (var j = 0; j < num; j++) {
            var selectOpt_value_type = document.getElementById('editlistAttributes').childNodes[j].childNodes[1].childNodes[0].childNodes[0].options;
            var selectIndex_value_type = document.getElementById('editlistAttributes').childNodes[j].childNodes[1].childNodes[0].childNodes[0].selectedIndex;

            var selectOpt_data_type = document.getElementById('editlistAttributes').childNodes[j].childNodes[4].childNodes[0].childNodes[0].options;
            var selectIndex_data_type = document.getElementById('editlistAttributes').childNodes[j].childNodes[4].childNodes[0].childNodes[0].selectedIndex;

            var selectOpt_value_unit = document.getElementById('editlistAttributes').childNodes[j].childNodes[2].childNodes[0].childNodes[0].options;
            var selectIndex_value_unit = document.getElementById('editlistAttributes').childNodes[j].childNodes[2].childNodes[0].childNodes[0].selectedIndex;

            var selectOpt_hc = document.getElementById('editlistAttributes').childNodes[j].childNodes[6].childNodes[0].childNodes[0].options;
            var selectIndex_hc = document.getElementById('editlistAttributes').childNodes[j].childNodes[6].childNodes[0].childNodes[0].selectedIndex;

            //var selectOpt_edit = document.getElementById('editlistAttributes').childNodes[j].childNodes[4].childNodes[0].childNodes[0].options;
           // var selectIndex_edit = document.getElementById('editlistAttributes').childNodes[j].childNodes[4].childNodes[0].childNodes[0].selectedIndex;

            var att = {
                value_name: document.getElementById('editlistAttributes').childNodes[j].childNodes[0].childNodes[0].childNodes[0].value.trim(),
                data_type: selectOpt_data_type[selectIndex_data_type].value,
                value_type: selectOpt_value_type[selectIndex_value_type].value,
                editable: '0',
                value_unit: selectOpt_value_unit[selectIndex_value_unit].value,
                healthiness_criteria: selectOpt_hc[selectIndex_hc].value,
                healthiness_value: document.getElementById('editlistAttributes').childNodes[j].childNodes[7].childNodes[0].childNodes[0].value.trim(),
                old_value_name: document.getElementById('editlistAttributes').childNodes[j].childNodes[9].childNodes[0].childNodes[0].value
            };

            if (att.value_name != "" && !regex.test(att.value_name) && att.value_name.length >= 2 && att.data_type != "" && att.value_type != "" && att.editable != "" && att.healthiness_criteria != "" && att.healthiness_value != "")
                myAttributes.push(att);
            else
                someNameisWrong = true;

        }


        mydeletedAttributes = [];
        numDel = document.getElementById('deletedAttributes').childElementCount;
        for (var j = 0; j < numDel; j++) {
            var selectOpt_value_type = document.getElementById('deletedAttributes').childNodes[j].childNodes[1].childNodes[0].childNodes[0].options;
            var selectIndex_value_type = document.getElementById('deletedAttributes').childNodes[j].childNodes[1].childNodes[0].childNodes[0].selectedIndex;

            var selectOpt_data_type = document.getElementById('deletedAttributes').childNodes[j].childNodes[4].childNodes[0].childNodes[0].options;
            var selectIndex_data_type = document.getElementById('deletedAttributes').childNodes[j].childNodes[4].childNodes[0].childNodes[0].selectedIndex;

            var selectOpt_value_unit = document.getElementById('deletedAttributes').childNodes[j].childNodes[2].childNodes[0].childNodes[0].options;
            var selectIndex_value_unit = document.getElementById('deletedAttributes').childNodes[j].childNodes[2].childNodes[0].childNodes[0].selectedIndex;

            var selectOpt_hc = document.getElementById('deletedAttributes').childNodes[j].childNodes[6].childNodes[0].childNodes[0].options;
            var selectIndex_hc = document.getElementById('deletedAttributes').childNodes[j].childNodes[6].childNodes[0].childNodes[0].selectedIndex;

           // var selectOpt_edit = document.getElementById('deletedAttributes').childNodes[j].childNodes[4].childNodes[0].childNodes[0].options;
           // var selectIndex_edit = document.getElementById('deletedAttributes').childNodes[j].childNodes[4].childNodes[0].childNodes[0].selectedIndex;

            var att = {
                value_name: document.getElementById('deletedAttributes').childNodes[j].childNodes[0].childNodes[0].childNodes[0].value.trim(),
                data_type: selectOpt_data_type[selectIndex_data_type].value,
                value_type: selectOpt_value_type[selectIndex_value_type].value,
                editable: '0',
                value_unit: selectOpt_value_unit[selectIndex_value_unit].value,
                healthiness_criteria: selectOpt_hc[selectIndex_hc].value,
                healthiness_value: document.getElementById('deletedAttributes').childNodes[j].childNodes[7].childNodes[0].childNodes[0].value.trim(),
                old_value_name: document.getElementById('deletedAttributes').childNodes[j].childNodes[9].childNodes[0].childNodes[0].value
            };
            mydeletedAttributes.push(att);
        }
            if(theSameNameAgain(myAttributes, mynewAttributes)==false){
            someNameisWrong = true;
       }

        if (!someNameisWrong) {
            document.getElementById('editlistAttributes').innerHTML = "";
            document.getElementById('addlistAttributesM').innerHTML = "";
            document.getElementById('deletedAttributes').innerHTML = "";



            $("#editDeviceModalTabs").hide();
            $('#editDeviceModal div.modalCell').hide();
            //$("#editDeviceModalFooter").hide();
            $("#editDeviceCancelBtn").hide();
            $("#editDeviceConfirmBtn").hide();
            $("#addAttrMBtn").hide();

            $("#editDeviceModalBody").hide();

            $('#editDeviceLoadingMsg').show();
            $('#editDeviceLoadingIcon').show();
            // console.log(JSON.stringify(deviceJson));

            var service = $('#editSelectService').val();
            var servicePath = $('#editInputServicePathDevice').val();

            if ($('#selectProtocolDeviceM').val() === "ngsi w/MultiService") {
                // servicePath value pre-processing
                if (servicePath[0] !== "/" || servicePath === "")
                    servicePath = "/" + servicePath;
                if (servicePath[servicePath.length - 1] === "/" && servicePath.length > 1)
                    servicePath = servicePath.substr(0, servicePath.length - 1);
            }

            $.ajax({
                url: "../api/device.php",
                data: {
                    action: "update",
                    username: loggedUser,
                    newattributes: JSON.stringify(mynewAttributes),
                    attributes: JSON.stringify(myAttributes),
                    deleteattributes: JSON.stringify(mydeletedAttributes),
                    id: $('#inputNameDeviceM').val(),
                    dev_organization: $('#inputOrganizationDeviceM').val(),
                    type: $('#inputTypeDeviceM').val(),
                    kind: $('#selectKindDeviceM').val(),
                    contextbroker: $('#selectContextBrokerM').val(),
                    gb_old_cb: gb_old_cb,
                    organization: organization,
                    uri: $('#inputUriDeviceM').val(),
                    protocol: $('#selectProtocolDeviceM').val(),
                    format: $('#selectFormatDeviceM').val(),
                    mac: $('#inputMacDeviceM').val(),
                    model: $('#selectModelDeviceM').val(),
                    producer: $('#inputProducerDeviceM').val(),
                    latitude: $('#inputLatitudeDeviceM').val(),
                    longitude: $('#inputLongitudeDeviceM').val(),
                    visibility: $('#selectVisibilityDeviceM').val(),
                    frequency: $('#inputFrequencyDeviceM').val(),
                    token: sessionToken,
                    k1: $('#KeyOneDeviceUserM').val(),
                    k2: $('#KeyTwoDeviceUserM').val(),
                    edgegateway_type: $("#selectEdgeGatewayTypeM").val(),
                    edgegateway_uri: $("#inputEdgeGatewayUriM").val(),
                    subnature: $('#selectSubnatureM').val(),
                    static_attributes: JSON.stringify(retrieveStaticAttributes("editlistStaticAttributes", false, "isMobileTickM")),
                    service: service,
                    servicePath: servicePath
                },
                type: "POST",
                async: true,
                success: function (data) {

                    //console.log(JSON.stringify(data));
                    //console.log("myAttributes " + JSON.stringify(myAttributes));
                    //console.log("mynewAttributes " + JSON.stringify(mynewAttributes));
                    //console.log("mydeletedAttributes " + JSON.stringify(mydeletedAttributes));
                    if (data["status"] === 'ko') {
                        console.log("Error editing Device type");
                        console.log(data);

                        $('#editDeviceLoadingMsg').hide();
                        $('#editDeviceLoadingIcon').hide();
                        $('#editDeviceOkMsg').hide();
                        $('#editDeviceOkIcon').hide();
                        $('#editDeviceKoMsg').show();
                        $('#editDeviceKoIcon').show();
                        $('#editDeviceOkBtn').show();

                    } else if (data["status"] === 'ok') {
                        $('#inputNameDevice').val("");
                        $('#inputTypeDevice').val("");
                        //$('#selectKindDevice').val("");
                        $('#selectContextBroker').val("");
                        $('#inputUriDevice').val("");
                        //$('#selectProtocolDevice').val("");
                        //$('#selectFormatDevice').val("");
                        $('#createdDateDevice').val("");
                        $('#inputMacDevice').val("");
                        $('#selectModelDevice').val("");
                        $('#inputProducerDevice').val("");
                        $('#inputLatitudeDevice').val("");
                        $('#inputLongitudeDevice').val("");
                        $('#selectVisibilityDevice').val();
                        $('#inputFrequencyDevice').val();
                        $('#selectSubnatureM').val("");
                        $('#editDeviceLoadingMsg').hide();
                        $('#editDeviceLoadingIcon').hide();
                        $('#editDeviceOkMsg').show();
                        $('#editDeviceOkIcon').show();
                        $('#editDeviceKoMsg').hide();
                        $('#editDeviceKoIcon').hide();
                        $('#editDeviceOkBtn').show();

                        $('#devicesTable').DataTable().destroy();
                        fetch_data(true);

                    } else {
                        console.log(data);
                    }

                },
                error: function (data) {
                    console.log("Ko result: " + JSON.stringify(data));
                    console.log("newattributes " + JSON.stringify(mynewAttributes));
                    console.log("attributes " + JSON.stringify(myAttributes));
                    console.log("deleteattributes " + JSON.stringify(mydeletedAttributes));

                    $('#editDeviceLoadingMsg').hide();
                    $('#editDeviceLoadingIcon').hide();
                    $('#editDeviceOkMsg').hide();
                    $('#editDeviceOkIcon').hide();
                    $('#editDeviceKoMsg').show();
                    $('#editDeviceKoIcon').show();
                    $('#editDeviceOkBtn').show();

                }
            });
        } else {
            alert("Check the values of your device, make sure that data you entered are valid or  those have not the same value name");
        }
    });


    //EDIT DEVICE CANCEL BUTTON 				
    $("#editDeviceCancelBtn").off("click");
    $("#editDeviceCancelBtn").on('click', function () {
        document.getElementById('editlistAttributes').innerHTML = "";
        document.getElementById('addlistAttributesM').innerHTML = "";
        document.getElementById('deletedAttributes').innerHTML = "";
    });

    //END EDIT DEVICE CANCEL BUTTON  	

    //--------------------- static attribute EDIT start

    $("#addNewStaticBtnM").off("click");
    $("#addNewStaticBtnM").click(function () {
        var row = createRowElem('', '', currentDictionaryStaticAttribEdit, "editlistStaticAttributes");
    });

    //--------------------- static attribute EDIT end


    $('#selectSubnatureM').on('select2:selecting', function (e) {
        checkSubnatureChanged($('#selectSubnatureM'), e.target.value, e.params.args.data.id, e, true);
    });

    $('#selectSubnatureM').on('select2:clearing', function (e) {
        checkSubnatureChanged($('#selectSubnatureM'), e.params.args.data.id, "", e, true);
    });

    //START ISMOBILE PROPERTIES
    $("#isMobileTickM").change(function () {
        if (this.checked)
            $("#positionMsgHintM").show();
        else
            $("#positionMsgHintM").hide();
    });
    //END ISMOBILE PROPERTIES

    //ADD DEVICE CANCEL BUTTON 

    $("#addNewDeviceCancelBtn").off("click");
    $("#addNewDeviceCancelBtn").on('click', function () {

        $('#inputNameDevice').val("");
        $('#inputTypeDevice').val("");
        $('#selectContextBroker').val("");
        $('#inputUriDevice').val("");
        $('#selectProtocolDevice').val("");
        $('#selectFormatDevice').val("");
        $('#createdDateDevice').val("");
        $('#inputMacDevice').val("");
        $('#selectModelDevice').val("");
        $('#inputProducerDevice').val("");
        $('#inputLatitudeDevice').val("");
        $('#inputLongitudeDevice').val("");
        $('#addDeviceModal').modal('hide');
        $('#selectSubnatureM').val("");
        removeStaticAttributes();
        //.hide();
        location.reload();
        //  $('#addDeviceModalTabs').show();
        //  $('#addDeviceModal div.modalCell').show();
        //  $('#addDeviceModalFooter').show(); 
        document.getElementById('addlistAttributes').innerHTML = "";
    });

    $("#addDeviceKoBackBtn").off("click");
    $("#addDeviceKoBackBtn").on('click', function () {
        $("#addDeviceKoModal").modal('hide');
        $("#addDeviceModal").modal('show');
    });

    $("#addDeviceKoConfirmBtn").off("click");
    $("#addDeviceKoConfirmBtn").on('click', function () {
        $("#addDeviceKoModal").modal('hide');
        $("#addDeviceForm").trigger("reset");
    });

    $("#editDeviceKoBackBtn").off("click");
    $("#editDeviceKoBackBtn").on('click', function () {
        $("#editDeviceKoModal").modal('hide');
        $("#editDeviceModal").modal('show');
    });

    $("#editDeviceKoConfirmBtn").off("click");
    $("#editDeviceKoConfirmBtn").on('click', function () {
        $("#editDeviceKoModal").modal('hide');
        $("#editDeviceForm").trigger("reset");
    });


    $("#selectContextBroker").change(function () {

        var index = document.getElementById("selectContextBroker").selectedIndex;
        var opt = document.getElementById("selectContextBroker").options;
        var valCB = opt[index].getAttribute("my_data");
        // console.log("protocol" + JSON.stringify(valCB));

        if (valCB === 'ngsi') {
            document.getElementById("selectProtocolDevice").value = 'ngsi';
            document.getElementById("selectFormatDevice").value = 'json';
        } else if (valCB === "ngsi w/MultiService") {
            document.getElementById("selectProtocolDevice").value = 'ngsi w/MultiService';
            document.getElementById("selectFormatDevice").value = 'json';
        } else if (valCB === 'mqtt') {
            document.getElementById("selectProtocolDevice").value = 'mqtt';
            document.getElementById("selectFormatDevice").value = 'csv';
        } else if (valCB === 'amqp') {
            document.getElementById("selectProtocolDevice").value = 'amqp';
            document.getElementById("selectFormatDevice").value = 'csv';
        } else {
            //alert("This is a new contextBroker");
            console.log("an error occurred");
        }
        checkSelectionFormat();
        checkSelectionProtocol();
        checkAddMyDeviceConditions();

    });

    $('#newOwner').on('input', function (e) {

        if ($(this).val().trim() === '') {
            $('#newOwnerMsg').css('color', '#f3cf58');
            $('#newOwnerMsg').html('New owner username can\'t be empty');
            $('#newOwnershipConfirmBtn').addClass('disabled');
        } else {
            //if(($(this).val().trim() === "<?= $_SESSION['loggedUsername'] ?>")&&("<?= $_SESSION['loggedRole'] ?>" !== "RootAdmin"))
            if (($(this).val().trim() === loggedUser) && (loggedRole !== "RootAdmin")) {
                $('#newOwnerMsg').css('color', '#f3cf58');
                $('#newOwnerMsg').html('New owner can\'t be you');
                $('#newOwnershipConfirmBtn').addClass('disabled');
            } else {
                $('#newOwnerMsg').css('color', 'white');
                $('#newOwnerMsg').html('User can be new owner');
                $('#newOwnershipConfirmBtn').removeClass('disabled');
            }
        }
    });

    // Device dataTable table Style 

    $('#devicesTable thead').css("background", "rgba(0, 162, 211, 1)");
    $('#devicesTable thead').css("color", "white");
    $('#devicesTable thead').css("font-size", "1em");


    $('#devicesTable tbody tr').each(function () {
        if ((dataTable.row(this).index()) % 2 !== 0) {
            $('#devicesTable tbody').css("background", "rgba(0, 162, 211, 1)");
            //console.log( 'Row index: '+dataTable.row( this ).index() );
            $(this).find('td').eq(0).css("background-color", "rgb(230, 249, 255)");
            $(this).find('td').eq(0).css("border-top", "none");
        } else {
            $(this).find('td').eq(0).css("background-color", "white");
            $(this).find('td').eq(0).css("border-top", "none");
        }
    });


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
            success: function (data) {
                if (data["status"] === 'ko') {
                    $('#newDelegatedMsgGroup').css('color', '#f3cf58');
                    $('#newDelegatedMsgGroup').html(data["msg"]);
                } else if (data["status"] === 'ok') {
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
            error: function (data) {
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
            success: function (data) {
                if (data["status"] === 'ko') {
                    $('#newDelegatedMsgGroup').css('color', '#f3cf58');
                    $('#newDelegatedMsgGroup').html(data["msg"]);
                } else if (data["status"] === 'ok') {
                    var $dropdown = $("#newDelegationOrganization");
                    $.each(data['content'], function () {
                        $dropdown.append($("<option />").val(this).text(this));
                    });
                }
            },
            error: function (data) {
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
            success: function (data) {
                if (data["status"] === 'ko') {
                    console.log("Error: " + data);
                    //TODO: manage error
                } else if (data["status"] === 'ok') {
                    var $dropdown = $("#newDelegationOrganization");
                    $dropdown.append($("<option/>").val(data['content']).text(data['content']));
                }
            },
            error: function (data) {
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

    $('#newDelegation').on('input', function (e) {
        if ($(this).val().trim() === '') {
            $('#newDelegatedMsg').css('color', '#f3cf58');
            $('#newDelegatedMsg').html('Delegated username can\'t be empty');
            $('#newDelegationConfirmBtn').addClass('disabled');
        } else {
            $('#newDelegatedMsg').css('color', 'white');
            $('#newDelegatedMsg').html('User can be delegated');
            $('#newDelegationConfirmBtn').removeClass('disabled');

            $('#delegationsTable tbody tr').each(function (i) {
                if ($(this).attr('data-delegated').trim() === $('#newDelegation').val()) {
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
        if (i % 2 !== 0) {
            $(this).find('td').eq(0).css("background-color", "rgb(230, 249, 255)");
            $(this).find('td').eq(0).css("border-top", "none");
        } else {
            $(this).find('td').eq(0).css("background-color", "white");
            $(this).find('td').eq(0).css("border-top", "none");
        }
    });

    $('#delegationsModal').on('hidden.bs.modal', function (e) {
        $(this).removeData();
    });

});  // end of ready-state


//   START TO CHANGE THE VISIBILITY  & OWNERSHIP 

function changeVisibility(id, contextbroker, dev_organization, visibility, uri, k1, k2, model, protocol, service, servicePath) {
    $("#delegationsModal").modal('show');
    $("#delegationHeadModalLabel").html("Device - " + id);
    // document.getElementById('CurrentVisiblityTxt').value = "Current Visiblity: " + visibility; 			
    //if ((target == '#visibilityCnt')) {						
    if (visibility == 'MyOwnPrivate' || visibility.toLowerCase() == 'private') {
        newVisibility = 'public';
        $('#visID').css('color', '#f3cf58');
        $("#visID").html("Visibility - Private");
        document.getElementById('newVisibilityPrivateBtn').style.visibility = 'hidden';
        document.getElementById('newVisibilityPublicBtn').style.visibility = 'show';

    } else //(visibility=='MyOwnPublic'){
    {
        newVisibility = 'private';
        $('#visID').css('color', '#f3cf58');
        $("#visID").html("Visibility - Public");
        document.getElementById('newVisibilityPrivateBtn').style.visibility = 'show';
        document.getElementById('newVisibilityPublicBtn').style.visibility = 'hidden';
    }
    // To Change from Private to Public 
    //$('#newVisibilityPublicBtn').off("click");
    //$('#newVisibilityPublicBtn').click(function(e){
    $(document).on("click", "#newVisibilityPublicBtn", function (event) {
        $.ajax({
            url: "../api/device.php",
            data:
                    {
                        action: "change_visibility",
                        //Sara2510 - for logging purpose
                        username: loggedUser,
                        organization: organization,
                        dev_organization: dev_organization,
                        id: id,
                        contextbroker: contextbroker,
                        uri: uri,
                        visibility: newVisibility,
                        token: sessionToken,
                        k1: k1,
                        k2: k2,
                    },
            type: "POST",
            async: true,
            dataType: 'json',
            success: function (data) {
                if (data["status"] === 'ok') {
                    $('#newVisibilityResultMsg').show();
                    $("#visID").html("");
                    $('#visID').css('color', '#f3cf58');
                    $("#visID").html("Visibility - Private");
                    $('#newVisibilityResultMsg').html('New visibility set to Public');
                    //document.getElementById('newVisibilityPublicBtn').style.visibility = 'hidden';
                    //document.getElementById('CurrentVisiblityTxt').value = "Current Visiblity: " +  newVisibility; 
                    //document.getElementById('newVisibilityPrivateBtn').style.visibility = 'show';

                    $('#newVisibilityPublicBtn').addClass('disabled');

                    setTimeout(function () {
                        $('#devicesTable').DataTable().destroy();
                        fetch_data(true);
                        location.reload();
                    }, 3000);
                } else if (data["status"] === 'ko') {
                    $('#newVisibilityResultMsg').show();
                    $('#newVisibilityResultMsg').html('Error setting new visibility');
                    $('#newVisibilityPublicBtn').addClass('disabled');

                    setTimeout(function () {
                        $('#newVisibilityPublicBtn').removeClass('disabled');
                        $('#newVisibilityResultMsg').html('');
                        $('#newVisibilityResultMsg').hide();
                    }, 3000);
                } else {
                    console.log(data);
                }
            },
            error: function (errorData) {
                $('#newVisibilityResultMsg').show();
                $('#newVisibilityResultMsg').html('Error setting new visibility');
                $('#newVisibilityPublicBtn').addClass('disabled');

                setTimeout(function () {
                    $('#newVisibilityPublicBtn').removeClass('disabled');
                    $('#newVisibilityResultMsg').html('');
                    $('#newVisibilityResultMsg').hide();
                }, 3000);
            }
        });
    });


    // To Change from Private to Public 	
    //$('#newVisibilityPrivateBtn').off("click");
    //$('#newVisibilityPrivateBtn').click(function(e){
    $(document).on("click", "#newVisibilityPrivateBtn", function (event) {
        $.ajax({
            url: "../api/device.php",
            data:
                    {
                        action: "change_visibility",
                        //Sara2510 - for logging purpose
                        username: loggedUser,
                        organization: organization,
                        dev_organization: dev_organization,
                        id: id,
                        contextbroker: contextbroker,
                        uri: uri,
                        visibility: newVisibility,
                        token: sessionToken,
                        k1: k1,
                        k2: k2,
                    },
            type: "POST",
            async: true,
            dataType: 'json',
            success: function (data) {
                if (data["status"] === 'ok') {
                    $('#newVisibilityResultMsg').show();
                    $('#newVisibilityResultMsg').html('New visibility set Private');
                    //$('#newVisibilityPrivateBtn').addClass('disabled');
                    //document.getElementById('newVisibilityPrivateBtn').style.visibility = 'hidden';
                    $('#newVisibilityPrivateBtn').addClass('disabled');
                    //document.getElementById('CurrentVisiblityTxt').value = "Current Visiblity: " + newVisibility; 
                    //document.getElementById('newVisibilityPublicBtn').style.visibility = 'show';
                    setTimeout(function () {
                        $('#devicesTable').DataTable().destroy();
                        fetch_data(true);
                        location.reload();
                    }, 3000);
                } else if (data["status"] === 'ko') {
                    $('#newVisibilityResultMsg').show();
                    $('#newVisibilityResultMsg').html('Error setting new visibility');
                    $('#newVisibilityPrivateBtn').addClass('disabled');

                    setTimeout(function () {
                        $('#newVisibilityPrivateBtn').removeClass('disabled');
                        $('#newVisibilityResultMsg').html('');
                        $('#newVisibilityResultMsg').hide();
                    }, 3000);
                } else {
                    console.log(data);
                }
            },
            error: function (errorData) {
                $('#newVisibilityResultMsg').show();
                $('#newVisibilityResultMsg').html('Error setting new visibility');
                $('#newVisibilityPrivateBtn').addClass('disabled');

                setTimeout(function () {
                    $('#newVisibilityPrivateBtn').removeClass('disabled');
                    $('#newVisibilityResultMsg').html('');
                    $('#newVisibilityResultMsg').hide();
                }, 3000);
            }
        });
    });

    //$('#newOwnershipConfirmBtn').off("click");
    //$('#newOwnershipConfirmBtn').click(function(e){	
    $(document).on("click", "#newOwnershipConfirmBtn", function (event) {
        // I generate a new pair of keys for the new owner
        k1new = generateUUID();
        k2new = generateUUID();
        $.ajax({
            url: "../api/device.php",
            data: {
                action: "change_owner",
                id: id,
                contextbroker: contextbroker,
                uri: uri,
                organization: organization,
                dev_organization: dev_organization,
                owner: loggedUser,
                newOwner: $('#newOwner').val(),
                token: sessionToken,
                k1: k1new,
                k2: k2new,
                model: model
            },
            type: "POST",
            async: true,
            dataType: 'json',
            success: function (data) {
                if (data["status"] === 'ok') {
                    $('#newOwner').val('');
                    $('#newOwner').addClass('disabled');
                    $('#newOwnershipResultMsg').show();
                    $('#newOwnershipResultMsg').html('New ownership set correctly');
                    $('#newOwnershipConfirmBtn').addClass('disabled');


                    setTimeout(function () {
                        $('#devicesTable').DataTable().destroy();
                        fetch_data(true);
                        location.reload();
                    }, 3000);
                } else if (data["status"] === 'ko') {
                    $('#newOwner').addClass('disabled');
                    $('#newOwnershipResultMsg').html('Error setting new ownership: please try again');
                    $('#newOwnershipConfirmBtn').addClass('disabled');

                    setTimeout(function () {
                        $('#newOwner').removeClass('disabled');
                        $('#newOwnershipResultMsg').html('');
                        $('#newOwnershipResultMsg').hide();
                    }, 3000);
                } else {
                    console.log(data);
                }
            },
            error: function (errorData) {
                $('#newOwner').addClass('disabled');
                $('#newOwnershipResultMsg').html('Error setting new ownership: please try again');
                $('#newOwnershipConfirmBtn').addClass('disabled');

                setTimeout(function () {
                    $('#newOwner').removeClass('disabled');
                    $('#newOwnershipResultMsg').html('');
                    $('#newOwnershipResultMsg').hide();
                }, 3000);
            }
        });
    });



    $("#delegationsCancelBtn").off("click");
    $("#delegationsCancelBtn").on('click', function () {
        $('#newDelegation').val("");
        $('#newDelegationGroup').val("");
        $('#newDelegationOrganization').val("");
        $('#newOwner').val("");
        $("#newVisibilityResultMsg").html("");
        $("#newOwnershipResultMsg").html("");
        location.reload();
        $('#delegationsModal').modal('hide');
    });

    //	} //end of tab visibilityCnt


    //else if ((target == '#ownershipCnt')) {
    //Change ownership of a device
    //alert (id + contextbroker + uri +  $('#newOwner').val() + k1 + k2 + loggedUser + sessionToken);

    //} //end of delegationsCnt
    //else {console.log(data);}

    //populate the beginning of the tables and listen about the removal
    $.ajax({

        url: "../api/device.php", //Checking the delegation table
        data:
                {
                    action: "get_delegations", // check the action and to be specified 
                    id: id,
                    contextbroker: contextbroker,
                    uri: uri,
                    organization: organization,
                    dev_organization: dev_organization,
                    user: loggedUser,
                    token: sessionToken,
                },
        type: "POST",
        async: true,
        dataType: 'json',
        success: function (data) {
            if (data["status"] == 'ok') {

                delegations = data["delegation"];
                $('#delegationsTable tbody').html("");
                $('#delegationsTableGroup tbody').html("");
                for (var i = 0; i < delegations.length; i++) {
                    if ((delegations[i].userDelegated != "ANONYMOUS") && (delegations[i].userDelegated != null)) {
                        $('#delegationsTable tbody').append('<tr class="delegationTableRow" data-delegationId="' + delegations[i].delegationId + '" data-delegated="' + delegations[i].userDelegated + '"><td class="delegatedName">' + delegations[i].userDelegated + '</td><td><i class="fa fa-remove removeDelegationBtn"></i></td></tr>');

                    } else if (delegations[i].groupDelegated != null) {

                        //extract cn and ou
                        var startindex = delegations[i].groupDelegated.indexOf("cn=");
                        if (startindex == -1) {

                            gr = "All groups";
                            var endindex_ou = delegations[i].groupDelegated.indexOf(",");
                            var ou = delegations[i].groupDelegated.substring(3, endindex_ou);
                        } else {
                            var endindex_gr = delegations[i].groupDelegated.indexOf(",");
                            var gr = delegations[i].groupDelegated.substring(3, endindex_gr);
                            var endindex_ou = delegations[i].groupDelegated.indexOf(",", endindex_gr + 1);
                            var ou = delegations[i].groupDelegated.substring(endindex_gr + 4, endindex_ou);
                        }

                        var DN = ou + "," + gr;

                        $('#delegationsTableGroup tbody').append('<tr class="delegationTableRowGroup" data-delegationId="' + delegations[i].delegationId + '" data-delegated="' + ou + "," + gr + '"><td class="delegatedName">' + DN + '</td><td><i class="fa fa-remove removeDelegationBtnGroup"></i></td></tr>');
                    }

                }
                $('#delegationsTable tbody').on("click", "i.removeDelegationBtn", function () {
                    var rowToRemove = $(this).parents('tr');
                    $.ajax({
                        url: "../api/device.php", //check the url
                        data:
                                {
                                    action: "remove_delegation", // to be specified
                                    token: sessionToken,
                                    user: loggedUser,
                                    delegationId: $(this).parents('tr').attr('data-delegationId'),
                                    uri: uri,
                                    userDelegated: $(this).parents('tr').attr('data-delegated'),
                                    id: id,
                                    contextbroker: contextbroker
                                },
                        type: "POST",
                        async: true,
                        dataType: 'json',
                        success: function (data) {
                            if (data["status"] === 'ok') {
                                rowToRemove.remove();
                                //console.log("ermoving a row from the table");
                            } else {
                                //TBD insert a message of error
                            }
                        },
                        error: function (errorData) {
                            //TBD  insert a message of error
                        }
                    });
                });

                $('#delegationsTableGroup tbody').on("click", "i.removeDelegationBtnGroup", function () {
                    var rowToRemove = $(this).parents('tr');
                    $.ajax({
                        url: "../api/device.php", //check the url
                        data:
                                {
                                    action: "remove_delegation", // to be specified
                                    token: sessionToken,
                                    user: loggedUser,
                                    delegationId: $(this).parents('tr').attr('data-delegationId'),
                                    uri: uri,
                                    groupDelegated: $(this).parents('tr').attr('data-delegated'),
                                    id: id,
                                    contextbroker: contextbroker

                                },
                        type: "POST",
                        async: true,
                        dataType: 'json',
                        success: function (data) {
                            if (data["status"] === 'ok') {
                                rowToRemove.remove();
                            } else {
                                //TBD insert a message of error
                            }
                        },
                        error: function (errorData) {
                            //TBD  insert a message of error
                        }
                    });
                });




            } else {
                // hangling situation of error
                console.log(json_encode(data));

            }

        },
        error: function (errorData) {
            //TBD  insert a message of error
        }
    });



    //listen about the confimation
    $(document).on("click", "#newDelegationConfirmBtn", function (event) {
        var newDelegation = document.getElementById('newDelegation').value;
        newk1 = generateUUID();
        newk2 = generateUUID();
        $.ajax({
            url: "../api/device.php", //which api to use
            data:
                    {
                        action: "add_delegation",
                        contextbroker: contextbroker,
                        dev_organization: dev_organization,
                        id: id,
                        uri: uri,
                        user: loggedUser,
                        token: sessionToken,
                        delegated_user: newDelegation,
                        k1: newk1,
                        k2: newk2
                    },
            type: "POST",
            async: true,
            dataType: 'json',
            success: function (data) {
                if (data["status"] === 'ok') {
                    $('#delegationsTable tbody').append('<tr class="delegationTableRow" data-delegationId="' + data["delegationId"] + '" data-delegated="' + $('#newDelegation').val() + '"><td class="delegatedName">' + $('#newDelegation').val() + '</td><td><i class="fa fa-remove removeDelegationBtn"></i></td></tr>');


                    $('#newDelegation').val('');
                    $('#newDelegation').addClass('disabled');
                    $('#newDelegatedMsg').css('color', 'white');
                    $('#newDelegatedMsg').html('New delegation added correctly');
                    $('#newDelegationConfirmBtn').addClass('disabled');

                    setTimeout(function () {
                        $('#newDelegation').removeClass('disabled');
                        $('#newDelegatedMsg').css('color', '#f3cf58');
                        $('#newDelegatedMsg').html('Delegated username can\'t be empty');
                    }, 1500);
                } else {
                    var errorMsg = null;


                    $('#newDelegation').val('');
                    $('#newDelegation').addClass('disabled');
                    $('#newDelegatedMsg').css('color', '#f3cf58');
                    $('#newDelegatedMsg').html(data["msg"]);
                    $('#newDelegationConfirmBtn').addClass('disabled');

                    setTimeout(function () {
                        $('#newDelegation').removeClass('disabled');
                        $('#newDelegatedMsg').css('color', '#f3cf58');
                        $('#newDelegatedMsg').html('Delegated username can\'t be empty');
                    }, 3000);
                }
            },
            error: function (errorData) {
                var errorMsg = "Error calling internal API";
                $('#newDelegation').val('');
                $('#newDelegation').addClass('disabled');
                $('#newDelegatedMsg').css('color', '#f3cf58');
                $('#newDelegatedMsg').html(errorMsg);
                $('#newDelegationConfirmBtn').addClass('disabled');

                setTimeout(function () {
                    $('#newDelegation').removeClass('disabled');
                    $('#newDelegatedMsg').css('color', '#f3cf58');
                    $('#newDelegatedMsg').html('Delegated username can\'t be empty');
                }, 3000);
            }
        });

    });//single delegation -end

    //group delegation -start------------------------------------------------------------------------------------------------------------
    $(document).on("click", "#newDelegationConfirmBtnGroup", function (event) {
        var delegatedDN = "";
        var e = document.getElementById("newDelegationGroup");
        if ((typeof e.options[e.selectedIndex] !== 'undefined') && (e.options[e.selectedIndex].text !== 'All groups')) {
            delegatedDN = "cn=" + e.options[e.selectedIndex].text + ",";
        }
        var e2 = document.getElementById("newDelegationOrganization");
        delegatedDN = delegatedDN + "ou=" + e2.options[e2.selectedIndex].text;

        newk1 = generateUUID();
        newk2 = generateUUID();
        $.ajax({
            url: "../api/device.php",
            data:
                    {
                        action: "add_delegation",
                        contextbroker: contextbroker,
                        dev_organization: dev_organization,
                        id: id,
                        uri: uri,
                        user: loggedUser,
                        token: sessionToken,
                        delegated_group: delegatedDN,
                        k1: newk1,
                        k2: newk2
                    },
            type: "POST",
            async: true,
            dataType: 'json',
            success: function (data) {
                if (data["status"] === 'ok') {
                    var toadd = $('#newDelegationOrganization').val();
                    if (document.getElementById("newDelegationGroup").options[e.selectedIndex].text != '') {
                        toadd = toadd + "," + $('#newDelegationGroup').val();
                    }

                    $('#delegationsTableGroup tbody').append('<tr class="delegationTableRowGroup" data-delegationId="' + data["delegationId"] + '" data-delegated="' + toadd + '"><td class="delegatedNameGroup">' + toadd + '</td><td><i class="fa fa-remove removeDelegationBtnGroup"></i></td></tr>');
                    $('#newDelegatedMsgGroup').css('color', 'white');
                    $('#newDelegatedMsgGroup').html('New delegation added correctly');

                    setTimeout(function () {
                        $('#newDelegatedMsgGroup').css('color', '#f3cf58');
                        $('#newDelegatedMsgGroup').html('Delegated groupname can\'t be empty');
                    }, 1500);
                } else {
                    var errorMsg = null;
                    $('#newDelegatedMsgGroup').css('color', '#f3cf58');
                    $('#newDelegatedMsgGroup').html(data["msg"]);

                    setTimeout(function () {
                        $('#newDelegationGroup').removeClass('disabled');
                        $('#newDelegationOrganization').removeClass('disabled');
                        $('#newDelegatedMsgGroup').css('color', '#f3cf58');
                        $('#newDelegatedMsgGroup').html('Delegated groupname can\'t be empty');
                    }, 2000);
                }
            },
            error: function (errorData) {
                var errorMsg = "Error calling internal API";
                $('#newDelegatedMsgGroup').css('color', '#f3cf58');
                $('#newDelegatedMsgGroup').html(errorMsg);

                setTimeout(function () {
                    $('#newDelegatedMsgGroup').css('color', '#f3cf58');
                    $('#newDelegatedMsgGroup').html('Delegated groupname can\'t be empty');
                }, 2000);
            }
        });
    });     //group delegation -end

}

// END TO CHANGE THE VISIBILITY 


/* Related to the Map */


function drawMap1(latitude, longitude, flag) {
    var marker;
    if (typeof map1 === 'undefined' || !map1) {
        if (flag == 0) {
            var centerMapArr = gpsCentreLatLng.split(",", 2);
            var centerLat = parseFloat(centerMapArr[0].trim());
            var centerLng = parseFloat(centerMapArr[1].trim());
            map1 = L.map('addLatLong').setView([centerLat, centerLng], zoomLevel);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map1);
            window.node_input_map = map1;


            setTimeout(function () {
                map1.invalidateSize()
            }, 400);

            //L.marker([latitude,longitude]).addTo(map).bindPopup(latitude + ',' + longitude);

            map1.on("click", function (e) {

                var lat = e.latlng.lat;
                var lng = e.latlng.lng;
                lat = lat.toFixed(5);
                lng = lng.toFixed(5);
                //console.log("Check the format:" + lat + " " + lng);

                document.getElementById('inputLatitudeDevice').value = lat;
                document.getElementById('inputLongitudeDevice').value = lng;
                addDeviceConditionsArray['inputLatitudeDevice'] = true;
                checkDeviceLatitude();
                checkAddDeviceConditions();
                addDeviceConditionsArray['inputLongitudeDevice'] = true;
                checkDeviceLongitude();
                checkAddDeviceConditions();
                if (marker) {
                    map1.removeLayer(marker);
                }
                marker = new L.marker([lat, lng]).addTo(map1).bindPopup(lat + ',' + lng);

            });



        } else if (flag == 1) {

            map1 = L.map('editLatLong').setView([latitude, longitude], 10);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map1);
            window.node_input_map = map1;
            //L.marker([latitude,longitude]).addTo(map).bindPopup("Hi DEVICE");

            setTimeout(function () {
                map1.invalidateSize()
            }, 400);

            marker = new L.marker([latitude, longitude]).addTo(map1).bindPopup(longitude + ',' + longitude);

            map1.on("click", function (e) {

                var lat = e.latlng.lat;
                var lng = e.latlng.lng;
                lat = lat.toFixed(5);
                lng = lng.toFixed(5);
                //console.log("Check the format:" + lat + " " + lng);

                document.getElementById('inputLatitudeDeviceM').value = lat;
                document.getElementById('inputLongitudeDeviceM').value = lng;
                editDeviceConditionsArray['inputLatitudeDeviceM'] = true;
                editDeviceConditionsArray['inputLongitudeDeviceM'] = true;
                if (marker) {
                    map1.removeLayer(marker);
                }
                marker = new L.marker([lat, lng]).addTo(map1).bindPopup(lat + ',' + lng);

            });

        } else if (flag == 2) {


            map1 = L.map('editLatLongValue').setView([latitude, longitude], 10);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map1);
            //window.node_input_map = map;
            //L.marker([latitude,longitude]).addTo(map).bindPopup("Hi DEVICE");
            setTimeout(function () {
                map1.invalidateSize()
            }, 400);
            marker = L.marker([latitude, longitude]).addTo(map1).bindPopup(latitude + ',' + longitude);
            var lat;
            var lng;
            map1.on("click", function (e) {
                lat = (e.latlng.lat);
                lng = (e.latlng.lng);
                if (marker) {
                    map1.removeLayer(marker);
                }

                marker = new L.marker([lat, lng]).addTo(map1).bindPopup(lat.toFixed(5) + ',' + lng.toFixed(5));
                console.log(lat + ' - ' + lng);
                document.getElementById('inputLatitudeDeviceValue').value = lat;
                document.getElementById('inputLongitudeDeviceValue').value = lng;



            });
        }
    }
}

function drawMap(latitude, longitude, id, devicetype, kind, divName) {

    if (typeof map === 'undefined' || !map) {
        map = L.map(divName).setView([latitude, longitude], zoomLevel);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        window.node_input_map = map;
    }

    map.setView([latitude, longitude], 10);

    if (typeof theMarker != 'undefined') {
        map.removeLayer(theMarker);
    }
    theMarker = L.marker([latitude, longitude]).addTo(map).bindPopup(id + ', ' + devicetype + ', ' + kind);
    setTimeout(function () {
        map.invalidateSize()
    }, 400);
}

function drawMapAll(data, divName) {
    var latitude = 43.7800;
    var longitude = 11.2300;

    if (typeof map_all === 'undefined' || !map_all) {
        //map_all = L.map(divName).setView([latitude,longitude], 10);

        //console.log(gpsCentreLatLng);
        var centerMapArr = gpsCentreLatLng.split(",", 2);
        var centerLat = parseFloat(centerMapArr[0].trim());
        var centerLng = parseFloat(centerMapArr[1].trim());
        map_all = L.map(divName).setView([centerLat, centerLng], zoomLevel);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map_all);
        window.node_input_map = map_all;
        /**************************Fatima-start******************************/
        /*var blueIcon = L.icon({
         iconUrl: 'data:image/svg+xml;utf-8, \
         <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" width="512px" height="512px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve"><g><path d="M256,0C167.641,0,96,71.625,96,160c0,24.75,5.625,48.219,15.672,69.125C112.234,230.313,256,512,256,512l142.594-279.375   C409.719,210.844,416,186.156,416,160C416,71.625,344.375,0,256,0z M256,256c-53.016,0-96-43-96-96s42.984-96,96-96   c53,0,96,43,96,96S309,256,256,256z" fill="#006DF0"/></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg>',
         iconSize:     [38, 95], // size of the icon
         popupAnchor:  [-3, -76] // point from which the popup should open relative to the iconAnchor
         });
         //from https://www.flaticon.com/free-icon/map-marker_33622#
         
         var redIcon = L.icon({
         iconUrl: 'data:image/svg+xml;utf-8, \
         <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" width="512px" height="512px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve"><g><path d="M256,0C167.641,0,96,71.625,96,160c0,24.75,5.625,48.219,15.672,69.125C112.234,230.313,256,512,256,512l142.594-279.375   C409.719,210.844,416,186.156,416,160C416,71.625,344.375,0,256,0z M256,256c-53.016,0-96-43-96-96s42.984-96,96-96   c53,0,96,43,96,96S309,256,256,256z" fill="#D80027"/></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg>',
         iconSize:     [38, 95], // size of the icon
         popupAnchor:  [-3, -76] // point from which the popup should open relative to the iconAnchor
         });*/


        /****************************Fatima-End**********************************/
        /*************Fatima2-start*************/
        green_markersGroup = undefined;
        marker_selection = [];

        redIcon = new L.Icon({

            iconUrl: '../img/markerPrivate.png',
            iconSize: new L.Point(32, 32),
            iconAnchor: new L.Point(16, 16),
            popupAnchor: new L.Point(0, -18)

        });

        blueIcon = new L.Icon({

            iconUrl: '../img/markerPublic.png',
            iconSize: new L.Point(32, 32),
            iconAnchor: new L.Point(16, 16),
            popupAnchor: new L.Point(0, -18)

        });
        greenIcon = new L.Icon({

            iconUrl: '../img/markerGreen.png',
            iconSize: new L.Point(32, 32),
            iconAnchor: new L.Point(16, 16),
            popupAnchor: new L.Point(0, -18)

        });
        /*************Fatima2-end**************/
        var mapLayers = {};
        drawnItems = new L.FeatureGroup();
        map_all.addLayer(drawnItems);

        var editControl = new L.Control.Draw({
            draw: false,
            edit: {
                //Fatima2-add-line
                remove: false,
                featureGroup: drawnItems,
                poly: {
                    allowIntersection: false
                }
            }
        });
        map_all.addControl(editControl);

        drawControl = new L.Control.Draw({
            //Fatima2-add-line
            remove: false,
            draw: {
                position: 'topleft',
                //polyline: false,
                //marker: false,
                circlemarker: false,
                //polygon: false,
                rectangle: false,
                polygon: {
                    allowIntersection: false,
                    showArea: true
                }
            }
        });
        map_all.addControl(drawControl);

        L.control.layers(mapLayers, {
            'drawlayer': drawnItems
        }, {
            collapsed: true
        }).addTo(map_all);

        map_all.on(L.Draw.Event.CREATED, function (e) {
            var fence = e.layer;
            if (drawnItems.hasLayer(fence) == false) {
                drawnItems.addLayer(fence);
            }

            drawControl.remove();
            TYPE = e.layerType;
            layer = e.layer;

            var resultsOut = drawSelection(layer, TYPE, data);
            $('#addMap1').modal('hide');
            //Fatima2-moveAndupdate-1-line
            colorSelectedMarkers(resultsOut, greenIcon);
            fetch_data(true, JSON.stringify(resultsOut));
            //console.log(resultsOut);


        });

        map_all.on('draw:edited', function (e) {
            var fences = e.layers;
            fences.eachLayer(function (fence) {
                fence.shape = "geofence";
                if (drawnItems.hasLayer(fence) == false) {
                    drawnItems.addLayer(fence);
                }
            });
            drawnItems.eachLayer(function (layer) {
                var resultsOut = drawSelection(layer, TYPE, data);
                //console.log(resultsOut);
                $('#addMap1').modal('hide');
                //Fatima2-moveAndupdate-1-line
                colorSelectedMarkers(resultsOut, greenIcon);
                fetch_data(true, JSON.stringify(resultsOut));
            });

        });


        /******************Fatima-start*************************/
        /*map_all.on('draw:deleted', function(e) {
         drawControl.addTo(map_all);
         });*/

        L.Control.RemoveAll = L.Control.extend(
                {
                    options:
                            {
                                position: 'topleft',
                            },
                    onAdd: function (map_all) {
                        var controlDiv = L.DomUtil.create('div', 'leaflet-draw-toolbar leaflet-bar');
                        L.DomEvent
                                .addListener(controlDiv, 'click', L.DomEvent.stopPropagation)
                                .addListener(controlDiv, 'click', L.DomEvent.preventDefault)
                                .addListener(controlDiv, 'click', function () {
                                    drawnItems.clearLayers();

                                    if (typeof green_markersGroup != 'undefined') {
                                        map_all.removeLayer(green_markersGroup);
                                        green_markersGroup = undefined;
                                        green_marker_array = [];
                                        marker_selection = [];
                                        //Fatima2-moveLine 
                                        fetch_data(true);
                                    }

                                    drawControl.addTo(map_all);


                                });
                        var controlUI = L.DomUtil.create('a', 'leaflet-draw-edit-remove', controlDiv);
                        controlUI.title = 'Delete';
                        controlUI.href = '#';
                        return controlDiv;
                    }


                });
        var removeAllControl = new L.Control.RemoveAll();
        map_all.addControl(removeAllControl);

        /******************Fatima-end***************************/



        for (var i = 0; i < data.length; i++) {


            var mylat = data[i].latitude;
            var mylong = data[i].longitude;
            var myname = data[i].name;
            if (mylat != null && mylong != null) {



                if (data[i].visibility == "public") {
                    m = L.marker([mylat, mylong], {icon: blueIcon}).addTo(map_all).bindPopup(myname);
                } else {
                    m = L.marker([mylat, mylong], {icon: redIcon}).addTo(map_all).bindPopup(myname);
                }

                //console.log("Before My Marker: " + mylat);
            }
        }
        setTimeout(function () {
            map_all.invalidateSize()
        }, 400);
    }
}


function colorSelectedMarkers(selections, greenIcon) {
    green_marker_array = [];
    //console.log("selections are");
    //console.log(selections);
    for (var k in selections) {

        lat = Number(selections[k].latitude);
        lng = Number(selections[k].longitude);
        popup = selections[k].id;
        var m = L.marker([lat, lng], {icon: greenIcon}).bindPopup(popup);
        green_marker_array.push(m);
    }


    if (typeof green_markersGroup != 'undefined') {
        //Fatima2-adjust
        map_all.removeLayer(green_markersGroup);
    }
    green_markersGroup = L.layerGroup(green_marker_array);
    green_markersGroup.addTo(map_all);
    marker_selection = selections;

}


function drawSelection(layer, type, data) {
    var resultsOut = [];
    switch (type) {

        case 'circle':
            circles = {};

            drawnItems.eachLayer(function (layer) {
                circles[layer.nodeID] = layer.toGeoJSON();
                circles[layer.nodeID].properties.radius = Math.round(layer.getRadius()) / 1000;
            });


            var lat_map = (circles[layer.nodeID].geometry.coordinates[1]);
            var long_map = (circles[layer.nodeID].geometry.coordinates[0]);
            var center_latlong = new L.LatLng(lat_map, long_map);
            var rad_map = (circles[layer.nodeID].properties.radius);


            for (var deviceTocheck in data) {


                var deviceLatLng = new L.LatLng(Number(data[deviceTocheck]["latitude"]), Number(data[deviceTocheck]["longitude"]));


                if (Math.abs(center_latlong.distanceTo(deviceLatLng) / 1000) <= rad_map) {

                    resultsOut.push(data[deviceTocheck]);

                }
            }

            break;
        case 'polygon':

            var polyPoints = layer._latlngs[0];
            for (var deviceTocheck in data) {

                //Ray Casting algorithm for checking if a point lies inside of a polygon
                var x = Number(data[deviceTocheck]["latitude"]), y = Number(data[deviceTocheck]["longitude"]);

                var inside = false;
                for (var i = 0, j = polyPoints.length - 1; i < polyPoints.length; j = i++) {
                    var xi = polyPoints[i].lat, yi = polyPoints[i].lng;
                    var xj = polyPoints[j].lat, yj = polyPoints[j].lng;

                    var intersect = ((yi > y) != (yj > y)) && (x < (xj - xi) * (y - yi) / (yj - yi) + xi);
                    if (intersect) {
                        inside = !inside;
                    }
                }

                if (inside) {

                    resultsOut.push(data[deviceTocheck]);

                }
            }
            break;
        case 'marker':

            var markerPoint = layer.getLatLng();

            for (var deviceTocheck in data) {

                var deviceLatLng = new L.LatLng(Number(data[deviceTocheck]["latitude"]), Number(data[deviceTocheck]["longitude"]));


                if (Math.abs(markerPoint.distanceTo(deviceLatLng) / 1000) <= 1) { //1 km 

                    resultsOut.push(data[deviceTocheck]);

                }
            }
            break;
        case 'polyline':

            var polyVerts = layer._latlngs;

            for (var deviceTocheck in data) {

                isclose = false;

                var deviceLatLng = new L.LatLng(Number(data[deviceTocheck]["latitude"]), Number(data[deviceTocheck]["longitude"]));

                for (var vi = 0, vl = polyVerts.length; vi < vl; vi++) {
                    var d = polyVerts[vi].distanceTo(deviceLatLng);
                    if (d / 1000 <= 1) {
                        isclose = true;
                        break;
                    }
                }

                if (isclose) {
                    resultsOut.push(data[deviceTocheck]);
                }
            }
            break;


    }

    return resultsOut;
}


function generateUUID() { // Public Domain/MIT
    var d = new Date().getTime();
    if (typeof performance !== 'undefined' && typeof performance.now === 'function') {
        d += performance.now(); //use high-precision timer if available
    }
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
        var r = (d + Math.random() * 16) % 16 | 0;
        d = Math.floor(d / 16);
        return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
    });
}

function generateKeysCLicked() {
    var k1 = generateUUID();
    var k2 = generateUUID();
    $("#KeyOneDeviceUser").val(k1);
    $("#KeyTwoDeviceUser").val(k2);
    showAddDeviceModal();
}


function editGenerateKeysCLicked() {
    var k1 = generateUUID();
    var k2 = generateUUID();
    $("#KeyOneDeviceUserM").val(k1);
    $("#KeyTwoDeviceUserM").val(k2);
    showEditDeviceModal();
}


function UserKey() {
    var message = null;
    var pattern = /^[a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{12}$/;

    var value1 = document.getElementById("KeyOneDeviceUser").value;
    var value2 = document.getElementById("KeyTwoDeviceUser").value;

    if ((value1 === '') && (value2 === '')) {
        message = 'Specify Key for the selected option';
        document.getElementById("addMyNewDeviceConfirmBtn").disabled = true;
        $("#KeyOneDeviceUserMsg").css("color", "red");
        $("#KeyTwoDeviceUserMsg").css("color", "red");

    } else if (!pattern.test(value1) || !pattern.test(value2)) {
        message = 'The Key should contain at least one special character and a number';
        document.getElementById("addMyNewDeviceConfirmBtn").disabled = true;
        $("#KeyOneDeviceUserMsg").css("color", "red");
        $("#KeyTwoDeviceUserMsg").css("color", "red");
    } else if (pattern.test(value1) && pattern.test(value2)) {
        message = 'Ok';
        document.getElementById("addMyNewDeviceConfirmBtn").disabled = false;
        $("#KeyOneDeviceUserMsg").css("color", "#337ab7");
        $("#KeyTwoDeviceUserMsg").css("color", "#337ab7");
        // $("#KeyOneDeviceUser").value = gb_key1;
        // $("#KeyTwoDeviceUser").value = gb_key2;
    }

    $("#KeyOneDeviceUserMsg").html(message);
    $("#KeyTwoDeviceUserMsg").html(message);
}









