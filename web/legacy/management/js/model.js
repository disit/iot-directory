$.fn.modal.Constructor.prototype.enforceFocus = function () {};

var gb_datatypes = "";
var gb_value_units = "";
var gb_value_types = "";
var defaultPolicyValue = [];
var gb_options = [];
var gb_device = "";
var gb_latitude = "";
var gb_longitude = "";
var gb_key1;
var gb_key2;
var dataTable = "";
var indexValues = 0;//it keeps track of unique identirier on the values, so it's possible to enforce specific value type
var filterDefaults = {
    myOwnPrivate: 'MyOwnPrivate',
    myOwnPublic: 'MyOwnPublic',
    myPrivate: 'private',
    public: 'public'
};
var tableFirstLoad = true;

//--------to get the datatypes items----------
$.ajax({url: "../api/device.php",
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

//export model for sensor
// function exporta(name, devicetype, frequency, kind, protocol, format, producer, attributes, subnature, staticAttributes, service, servicePath) {
//     var txt = "name,device type,mac,frequency,kind,protocol,format,producer,lat,long,value name,data_type,value_type,editable,value_unit,healthiness_criteria,healthiness_value,k1,k2,subnature,static_attributes,service,service_path\r\n";
//
//     var arr = JSON.parse(atob(attributes));
//     for (var i = 0; i < arr.length; i++) {
//         var value = "<DEVICENAME>," + devicetype + ",\"\"," + frequency + "," + kind + "," + protocol + "," + format + "," + producer + ",<LAT>,<LONG>,";
//         var obj = arr[i];
//         for (var key in obj) {
//             var attrName = key;
//             var attrValue = obj[key];
//             if ("editable" === attrName) {
//                 if (attrValue == 0)
//                     attrValue = "FALSE";
//                 else
//                     attrValue = "TRUE";
//             }
//             value = value + attrValue + ",";
//         }
//
//         if (service === "null")
//             service = "";
//         if (servicePath === "bnVsbA==")
//             var servicePa = "\"\"";
//         else
//             var servicePa = "\"" + atob(servicePath).replace(/"/g, "\"\"") + "\"";
//
//         //TODO: also other fields probably need to be escaped like above (for other special character, live COMMA, SEMICOLON, ...)
//         var staticAtt = "\"" + atob(staticAttributes).replace(/"/g, "\"\"") + "\"";
//
//         var txt = txt + value + "\"\",\"\"," + subnature + "," + staticAtt + "," + service + "," + servicePa + "\r\n";
//     }
//
//     var txt = txt + "\r\n";
//
//     var element = document.createElement('a');
//     element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(txt.substring(0, txt.length - 1)));
//     element.setAttribute('download', name + "-model.csv");
//     element.style.display = 'none';
//     document.body.appendChild(element);
//     element.click();
//     document.body.removeChild(element);
// }

function exportJson(name,description, devicetype, frequency, kind, contextbroker, protocol, format, healthiness_criteria, healthiness_value, kgenerator, producer, attributes, subnature, staticAttributes, service, servicePath) {
    var obj = {
        name: name,
        description: description,
        device_type: devicetype,
        frequency: frequency,
        kind: kind,
        contextbroker: contextbroker,
        protocol: protocol,
        format: format,
        healthiness_criteria: healthiness_criteria,
        healthiness_value: healthiness_value,
        key_generator:kgenerator,
        producer: producer,
        subnature: subnature,
        static_attributes: atob(staticAttributes).replace(/"/g, "\"\""),
        service: service,
        service_path: servicePath,
        d_attributes: JSON.parse(atob(attributes))

    };

    if (servicePath === "bnVsbA==")
        obj.service_path = "";
    else
        obj.service_path = "\"" + atob(servicePath).replace(/"/g, "\"\"") + "\"";

    var element = document.createElement('a');
    var data = "text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(obj));

    element.setAttribute("href", "data:" + data);
    element.setAttribute('download', name + "-model.json");
    document.body.appendChild(element);
    element.click();
    document.body.removeChild(element);

//        var element = document.createElement('a');
//        element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(txt.substring(0, txt.length-1)));
//       
//        element.style.display = 'none';
//        document.body.appendChild(element);
//        element.click();
//        document.body.removeChild(element);

}


function removeElementAt(parent, child) {
    var list = document.getElementById(parent);
    // var content = child.parentElement.parentElement.parentElement.innerHTML
    // console.log("elemento cancellato " + document.getElementById('deletedAttributes').innerHTML);
    if (parent == "editlistAttributes")
    {
        document.getElementById('deletedAttributes').appendChild(child.parentElement.parentElement.parentElement);
    } else
        list.removeChild(child.parentElement.parentElement.parentElement);
    checkAtlistOneAttribute();
    checkEditAtlistOneAttributeM();
    checkAddModelConditions();
    checkEditModelConditions();
}


function drawAttributeMenu
        (attrName, data_type, value_type, editable, value_unit, healthiness_criteria, value_refresh_rate,realtime, parent, indice)
{
    if (attrName == "")
        msg = "<div style=\"color:red;\" class=\"modalFieldMsgCnt\"></div>";
    else
        msg = "<div class=\"modalFieldMsgCnt\">&nbsp;</div>";

    options = "";
    if (value_type == "") {
        options += "<option hidden disabled selected value=\"NOT VALID OPTION\"> -- select an option -- </option>";
        msg_value_type = "<div style=\"color:red;\" class=\"modalFieldMsgCnt\">Value type is mandatory</div>";
    } else
        msg_value_type = "<div style=\"color:#337ab7;\" class=\"modalFieldMsgCnt\">Ok</div>"
    //I value type indicati qui non avranno il checkbox realtime, magari in fututo potrebbe essere utile, basta aggiungere hidden nello style dell' else (al momento è disabilitato e timestamp è un placeholder)

    if(attrName === 'DateObserved' || attrName === 'dateObserved'){
        real_time_flag = "<input type=\"checkbox\"  id=\"realtime_flag"+indice+"\"  style=\"margin-right: 5px;display:none;\" class=\"realtime_checkbox\"><label for=\"realtime_flag"+indice+"\" style=\"margin-bottom: 5px;display:none;\">Real Time</label>"
    }else if (realtime === "true") {
        real_time_flag = "<input type=\"checkbox\"  id=\"realtime_flag" + indice + "\"  style=\"margin-right: 5px;\" checked ><label for=\"realtime_flag" + indice + "\" style=\"margin-bottom: 5px;\">Real Time</label>"
    }else {
    real_time_flag = "<input type=\"checkbox\"  id=\"realtime_flag"+indice+"\"  style=\"margin-right: 5px;\" class=\"realtime_checkbox\"><label for=\"realtime_flag"+indice+"\" style=\"margin-bottom: 5px;\">Real Time</label>"
}

    for (var n = 0; n < gb_value_types.length; n++)
    {
        if (value_type == gb_value_types[n].value)
            options += "<option value=\"" + gb_value_types[n].value + "\" selected>" + gb_value_types[n].label + " (" + gb_value_types[n].value + ")" + "</option>";
        else
            options += "<option value=\"" + gb_value_types[n].value + "\">" + gb_value_types[n].label + " (" + gb_value_types[n].value + ")" + "</option>";
    }

    myunits = "";// <option value=\"none\"></option>";
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
    validDataType = getValidDataType(value_type, data_type);
    if (validDataType !== "") {
        if (!validDataType.includes('selected')) {
            mydatatypes += "<option hidden disabled selected value=\"NOT VALID OPTION\"> -- select an option -- </option>";
            msg_data_unit = "<div style=\"color:red;\" class=\"modalFieldMsgCnt\">Value unit is mandatory</div>";
        }
        mydatatypes += validDataType;
    }
    if (value_refresh_rate === "")
        msg_refresh_rate = "<div style=\"color:red;\" class=\"modalFieldMsgCnt\"></div>";
    else
        msg_refresh_rate = "<div class=\"modalFieldMsgCnt\">&nbsp;</div>";


//    mydatatypes = "";
//    if (data_type != "")
//        labelcheck = data_type;
//    else
//        labelcheck = "";
//    for (var n = 0; n < gb_datatypes.length; n++)
//    {
//        if (labelcheck == gb_datatypes[n])
//            mydatatypes += "<option value=\"" + gb_datatypes[n] + "\" selected>" + gb_datatypes[n] + "</option>";
//        else
//            mydatatypes += "<option value=\"" + gb_datatypes[n] + "\">" + gb_datatypes[n] + "</option>";
//    }

    return "<div class=\"row\" style=\"border:2px solid blue;\" id=\"row"+indice+"\" ><div class=\"col-xs-6 col-md-3 modalCell\">" +
            "<div class=\"modalFieldCnt\" title=\"Insert a name for the sensor/actuator\"><input  id=\"InputVNM"+indice+"\" type=\"text\" class=\"modalInputTxt Input_readoonly\"" +
            "name=\"" + attrName + "\"  value=\"" + attrName + "\" onkeyup=\"checkStrangeCharacters(this)\" >" +
            "</div><div class=\"modalFieldLabelCnt\">Value Name</div>" + msg + "</div>" +
            "<div class=\"col-xs-6 col-md-3 modalCell\"><div class=\"modalFieldCnt\" title=\"select the type of the sensor/actuator\">" +
            "<select  class=\"modalInputTxt Select_readoonly\" id=\"value_type" + indice + "\" " +
            "onchange=valueTypeChanged(" + indice + ") " +
            "\">" + options +
            "</select></div><div  class=\"modalFieldLabelCnt\">Value Type" +
            "<button class=\"Hidebutton\" id=\"CopyButtAttrs\" onclick=\"copyClipboard('value_type" + indice + "')\"><img src=\"../img/clipboard.svg\" width=\"16\"></button>" +
            "</div>" + msg_value_type + "</div>" +
            "<div class=\"col-xs-6 col-md-3 modalCell\"><div class=\"modalFieldCnt\" title=\"select the unit of the data generated by the sensor/actuator\">" +
            "<select    class=\"modalInputTxt Select_readoonly\" id=\"value_unit" + indice + "\" " +
            "onchange=valueUnitChanged(" + indice + ") " +
            "\">" +
            myunits +
            "</select></div><div  class=\"modalFieldLabelCnt\">Value Unit" +
            "<button class=\"Hidebutton\" id=\"CopyButtAttr\" onclick=\"copyClipboard('value_unit" + indice + "')\"><img src=\"../img/clipboard.svg\" width=\"16\"></button>" +
            "</div>" + msg_value_unit + "</div>" +
            "<div class=\"col-xs-6 col-md-3 modalCell\"><div class=\"modalFieldCnt\" title=\"select the type of data generated by the sensor/actuator\">" +
            "<select  class=\"modalInputTxt Select_readoonly\"" + "  id=\"data_type" + indice + "\" " +
            "onchange=dataTypeChanged(" + indice + ") " +
            //"name=\"" + attrName + "-type" +
            "\">" + mydatatypes +
            "</select></div><div  class=\"modalFieldLabelCnt\">Data Type</div>" + msg_data_unit + "</div>" +
//            "<div class=\"col-xs-6 col-md-3 modalCell\"><div class=\"modalFieldCnt\" title=\"is the sensor/actuator editable?\">" +
//            "<select  id=\"SelectEdit\"   class=\"modalInputTxt Select_readoonly\" name=\"" + editable +
//            "\">" +
//            "<option value='0' default>false</option>" +
//            "<option value='1'>true</option> </select>" +
//            "</div><div class=\"modalFieldLabelCnt\">Editable</div></div>" +

            "<div class=\"col-xs-6 col-md-3 modalCell \"><div class=\"modalFieldCnt\" ><label  class=\"switch \"><span style=\"display:none\" ></span></label></div></div>" +
            "<div class=\"col-xs-6 col-md-3 modalCell\"><div  class=\"modalFieldCnt\" title=\"select a criterion as a reference to decide whether the sensor/actuator is working well\">" +
            "<select  id=\"SelectHC\" class=\"modalInputTxt Select_readoonly\" name=\"" + healthiness_criteria +
            "\" \>" +
            "<option value=\"refresh_rate\">Refresh rate</option>" +
            "<option value=\"different_values\">Different Values</option>" +
            "<option value=\"within_bounds\">Within bounds</option>" +
            "</select></div><div  class=\"modalFieldLabelCnt\">Healthiness Criteria</div></div>" +
            "<div class=\"col-xs-6 col-md-3 modalCell\"><div class=\"modalFieldCnt\" title=\"Insert the limit value(s) to consider the sensor/actuator as healthy, according to the selected criterion \">" +
            "<input  id=\"InputHV\" type=\"text\" class=\"modalInputTxt Select_readoonly \" " + value_refresh_rate +
            "\" value=\"" + value_refresh_rate + "\"></div><div class=\"modalFieldLabelCnt\">Healthiness Value</div>"+ msg_refresh_rate +" </div>" +
            "<div class=\"col-xs-6 col-md-3 modalCell\"><div class=\"modalFieldCnt\">"+ real_time_flag + "</div></div>" +
            "<div class=\"col-xs-6 col-md-3 modalCell\"><div class=\"modalFieldCnt\">" +
            //"<i class=\"fa fa-minus-square\" onclick=\"removeElementAt('" + parent + "',this); return true;\"  style=\"font-size:36px; color: #ffcc00\"></i></div></div></div>";
            "<button  id=\"RemoveButtAttr\" class=\"btn btn-danger Hidebutton\" onclick=\"removeElementAt('" + parent + "',this); return true;\">Remove Value</button></div></div></div>";
}

function format(d) {
    var multitenancy = "";
    if (d.service && d.servicePath) {
        multitenancy = '<div class="row">' +
                '<div class="col-xs-6 col-sm-6" style="background-color:#B3D9FF;"><b>Service/Tenant:</b>' + "  " + d.service + '</div>' +
                '<div class="clearfix visible-xs"></div>' +
                '<div class="col-xs-6 col-sm-6" style="background-color:#B3D9FF;"><b>ServicePath:</b>' + "  " + d.servicePath + '</div>' +
                '</div>';
    }

    // `d` is the original data object for the row
    return '<div class="container-fluid">' +
            '<div class="row">' +
            '<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>Frequency:</b>' + "  " + d.frequency + '</div>' +
            '<div class="clearfix visible-xs"></div>' +
            '<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>Contextbroker:</b>' + "  " + d.contextbroker + '</div>' +
            '</div>' +
            '<div class="row">' +
            '<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Link:</b>' + "  " + d.link + '</div>' +
            '<div class="clearfix visible-xs"></div>' +
            '<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Key Generator:</b>' + "  " + d.kgenerator + '</div>' +
            '</div>' + multitenancy +
            '<div class="row">'+
            '<div class="col-xs-12 col-sm-12" style="background-color:#D6CADD;"><button class="btn btn-info my-small-button" onclick="exportJson(\'' + d.name + '\' , \'' + d.description + '\',\'' + d.devicetype + '\',\'' + d.frequency + '\',\'' + d.kind + '\',\''+ d.contextbroker +'\',\'' + d.protocol + '\',\'' + d.format + '\',\'' + d.healthiness_criteria + '\',\'' + d.healthiness_value + '\',\'' + d.kgenerator + '\',\'' + d.producer + '\',\'' + btoa(d.attributes) + '\',\'' + d.subnature + '\',\'' + btoa(d.static_attributes) + '\',\'' + d.service + '\',\'' + btoa(d.servicePath) + '\');return true;" class="icon-download"><b>EXPORT JSON</b></button></div>' +
            '</div>' +
            '</div>';
}

function updateDeviceTimeout()
{
    $("#editModelOkModal").modal('hide');
    setTimeout(function () {
        location.reload();
    }, 500);
}


function CloneModel() {
    $('#selectModel').on({
        click: function () {
            var Model = this.value;

            $.ajax({
                url: "../api/model.php",
                data: {
                    action: "get_model",
                    token: sessionToken,
                    name: Model
                },
                type: "POST",
                async: true,
                datatype: 'json',
                success: function (myModel) {
                    console.log(myModel);
                    var Mod = myModel['content'];

                    $('#inputIdModel').val(Mod.name);
                     
                    $('#inputOrganizationModel').val(Mod.organization);
                    $('#inputNameModel').val(Mod.name);
                    $('#inputDescriptionModel').val(Mod.description);
                    $('#inputTypeModel').val(Mod.devicetype);
                    $('#selectKindModel').val(Mod.kind);
                    $('#inputProducerModel').val(Mod.producer);
                    $('#inputFrequencyModel').val(Mod.frequency);
                    $('#selectKGeneratorModel').val(Mod.kgenerator);
                    $('#selectEdgeGatewayType').val(Mod.edgegateway_type);
                    $('#selectContextBroker').val(Mod.contextbroker);
                    $('#selectProtocolModel').val(Mod.protocol);
                    $('#selectFormatModel').val(Mod.format);
                    $('#selectHCModel').val(Mod.hc);
                    $('#inputHVModel').val(Mod.hv);
                    $('#selectSubnature').val(Mod.subnature);
                    $('#selectSubnature').trigger('change');
                    subnatureChanged("view", (((Mod.static_attributes))));
                    $('#addNewStaticBtnM').hide();
                    fillMultiTenancyFormSection(Mod.service, Mod.servicePath, Mod.contextbroker, 'model');
                    checkModelSelectionCB_all();

                  $('a[data-toggle="tab"]').off('shown.bs.tab').on('shown.bs.tab', function (e) {
                        var target = $(e.target).attr("href");
                        if ((target == '#addSchemaTabModel')) {

                            if (document.querySelector("#addlistAttributes > div") == null) {
                                document.getElementById('addlistAttributes').innerHTML = "";
                            }

                            document.getElementById('addlistAttributes').innerHTML = "";

                            var row = null;
                            //  $("#editUserPoolsTable tbody").empty();
                            var myattributes = JSON.parse(Mod.attributes);
                            content = "";
                            k = 0;
                            while (k < myattributes.length)
                            {
                                content = drawAttributeMenu(myattributes[k].value_name,
                                        myattributes[k].data_type, myattributes[k].value_type, myattributes[k].editable, myattributes[k].value_unit, myattributes[k].healthiness_criteria,
                                        myattributes[k].healthiness_value, 'addlistAttributes', indexValues);
                                indexValues = indexValues + 1;
                                k++;
                                $('#addlistAttributes').append(content);
                                 
                            }
                            checkEditAtlistOneAttributeM();
                            $("#addSchemaTabModel #editlistAttributes .row input:even").each(function () {
                                checkModelValueName($(this));
                            });
                            checkAddModelConditions();
                            $(".Hidebutton").hide();
                        $('.Input_readoonly').attr('readonly', true);
                        $('.Select_readoonly').prop('disabled', true);
                        $('#editModelLoadingIcon').hide();


                        }
                    });
                    checkModelName();
                    checkModelDeviceType();
                    checkAtlistOneAttribute();
                    checkAddModelConditions();
                }
            });
        }
    }, );
}

function fetch_data(destroyOld/*, selected=null*/)
{
    if (destroyOld) {
        $('#modelTable').DataTable().clear().destroy();
        tableFirstLoad = true;
    }
    mydata = {
        action: "get_all_models_DataTable",
        token: sessionToken,
        no_columns: ["position", "owner", "edit", "delete", "view"]
    };

    dataTable = $('#modelTable').DataTable({
        "processing": true,
        "serverSide": true,
        scrollX: true,
        "paging": true,
        "ajax": {
            url: "../api/model.php",
            data: mydata,
            datatype: 'json',
            type: "POST"
        },
        "columns": [{
                "class": "details-control",
                "name": "position",
                "orderable": false,
                "data": null,
                "defaultContent": "",
                "render": function () {
                    return '<i class="fa fa-plus-square" aria-hidden="true"></i>';
                },
                width: "15px"
            }, {
                "name": "name",
                "data": function (row, type, val, meta) {
                    return row.name;
                }
            }, {
                "name": "description",
                "data": function (row, type, val, meta) {
                    return row.description;
                }
            }, {
                "name": "visibility",
                "data": function (row, type, val, meta) {
                    if (row.visibility == 'MyOwnPrivate') {
                        return '<button type="button"  class=\"myOwnPrivateBtn\" onclick="changeVisibility(\'' + row.name + '\',\'' + row.visibility +
                                '\',\'' + row.organization + '\',\'' + row.organization + ':' + row.name + '\')">' + row.visibility + '</button>';
                    } else if (row.visibility == 'MyOwnPublic') {
                        return '<button type="button"  class=\"myOwnPublicBtn\" onclick="changeVisibility(\'' + row.name + '\',\'' + row.visibility +
                                '\',\'' + row.organization + '\',\'' + row.organization + ':' + row.name + '\')">' + row.visibility + '</button>';
                    } else if (row.visibility == 'public') {
                        return '<button type="button"  class=\"publicBtn\" >' + row.visibility + '</button>';
                    } else {// value is private
                        return "<div class=\"delegatedBtn\">" + row.visibility + "</div>";
                    }
                }
            }, {
                "name": "organization",
                "data": function (row, type, val, meta) {
                    return row.organization;
                }
            }, {
                "name": "owner",
                "data": function (row, type, val, meta) {
                    return row.owner;
                }
            }, {
                "name": "kind",
                "data": function (row, type, val, meta) {
                    return row.kind;
                }
            }, {
                "name": "producer",
                "data": function (row, type, val, meta) {
                    return row.producer;
                }
            }, {
                "name": "devicetype",
                "data": function (row, type, val, meta) {
                    return row.devicetype;
                }
            }, {
                data: null,
                "name": "edit",
                "orderable": false,
                className: "center",
                render: function (d) {
                    if (loggedRole == 'RootAdmin' || d.visibility == 'MyOwnPrivate' || d.visibility == 'MyOwnPublic') {
                        return '<button type="button" class="editDashBtn" ' +
                                'data-id="' + d.id + '" ' +
                                'data-organization="' + d.organization + '" ' +
                                'data-name="' + d.name + '" ' +
                                'data-kind="' + d.kind + '" ' +
                                'data-description="' + d.description + '" ' +
                                'data-devicetype="' + d.devicetype + '" ' +
                                'data-producer="' + d.producer + '" ' +
                                'data-frequency="' + d.frequency + '" ' +
                                'data-format="' + d.format + '" ' +
                                'data-link="' + d.link + '" ' +
                                'data-protocol="' + d.protocol + '" ' +
                                'data-contextbroker="' + d.contextbroker + '" ' +
                                'data-healthiness_criteria="' + d.healthiness_criteria + '" ' +
                                'data-healthiness_value="' + d.healthiness_value + '" ' +
                                'data-kgenerator="' + d.kgenerator + '" ' +
                                'data-edgegateway_type="' + d.edgegateway_type + '" ' +
                                'data-attributes="' + d.attributes + '" ' +
                                'data-k1="' + d.k1 + '" ' +
                                'data-k2="' + d.k2 + '" ' +
                                'data-subnature="' + d.subnature + '" ' +
                                'data-static-attributes="' + btoa(d.static_attributes) + '" ' +
                                'data-service="' + d.service + '" ' +
                                'data-servicePath="' + d.servicePath + '" ' +
                                'data-hlt="' + d.hlt + '" ' +
                                'data-policy="' + d.policy + '">Edit</button>';
                    }
                    return '';
                }
            }, {
                data: null,
                "name": "delete",
                "orderable": false,
                className: "center",
                render: function (d) {
                    if (loggedRole == 'RootAdmin' || d.visibility == 'MyOwnPrivate' || d.visibility == 'MyOwnPublic') {
                        return '<button type="button" class="delDashBtn" ' +
                                'data-name="' + d.name + '" ' +
                                'data-id="' + d.id + '">Delete</button>';
                    }
                    return '';
                }
            }, {
                data: null,
                "name": "view",
                "orderable": false,
                className: "center",
                render: function (d) {

                    return '<button type="button" class="testDashBtn" ' +
                            'data-id="' + d.id + '" ' +
                            'data-organization="' + d.organization + '" ' +
                            'data-name="' + d.name + '" ' +
                            'data-kind="' + d.kind + '" ' +
                            'data-description="' + d.description + '" ' +
                            'data-devicetype="' + d.devicetype + '" ' +
                            'data-producer="' + d.producer + '" ' +
                            'data-frequency="' + d.frequency + '" ' +
                            'data-format="' + d.format + '" ' +
                            'data-link="' + d.link + '" ' +
                            'data-protocol="' + d.protocol + '" ' +
                            'data-contextbroker="' + d.contextbroker + '" ' +
                            'data-healthiness_criteria="' + d.healthiness_criteria + '" ' +
                            'data-healthiness_value="' + d.healthiness_value + '" ' +
                            'data-kgenerator="' + d.kgenerator + '" ' +
                            'data-edgegateway_type="' + d.edgegateway_type + '" ' +
                            'data-attributes="' + d.attributes + '" ' +
                            'data-k1="' + d.k1 + '" ' +
                            'data-k2="' + d.k2 + '" ' +
                            'data-subnature="' + d.subnature + '" ' +
                            'data-static-attributes="' + btoa(d.static_attributes) + '" ' +
                            'data-service="' + d.service + '" ' +
                            'data-servicePath="' + d.servicePath + '" ' +
                            'data-hlt="' + d.hlt + '" ' +
                            'data-policy="' + d.policy + '">View</button>';

                }

            }],
        "order": []
    });
    //function to search a model directly if "&model_name=..." is present in the URL
    const deviceNameInUrl = window.location.search;
    const urlParams = new URLSearchParams(deviceNameInUrl);
    const deviceToSearch = urlParams.get("model_name");
    if(deviceToSearch != null) {
        dataTable.search(deviceToSearch, true, false).draw();
    }

    //TODO can we use the get_functionalities here??
    if (loggedRole != 'RootAdmin') {
        dataTable.columns([5]).visible(false);//hide Owner
    }
}


$(document).ready(function ()
{



//fetch_data function will load the device table 	
    fetch_data(false);
    //detail control for device dataTable
    var detailRows = [];
    $('#modelTable tbody').on('click', 'td.details-control', function () {
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
//$('#modelTable').bootstrapTable('hideColumn', 'description');
//$('#modelTable').bootstrapTable('hideColumn', 'kind');

        } else
        {
//$('#modelTable').bootstrapTable('showColumn', 'name');
// $('#modelTable').bootstrapTable('showColumn', 'description');
//$('#modelTable').bootstrapTable('showColumn', 'devicetype');
//$('#modelTable').bootstrapTable('showColumn', 'kind');
//$('#modelTable').bootstrapTable('showColumn', 'producer');

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


    $('#modelLink .mainMenuItemCnt').addClass("mainMenuItemCntActive");
    $('#mobMainMenuPortraitCnt #modelLink .mobMainMenuItemCnt').addClass("mainMenuItemCntActive");
    $('#mobMainMenuLandCnt #modelLink .mobMainMenuItemCnt').addClass("mainMenuItemCntActive");
// START CLONE MODEL



    $("#CloneModelBtn").off("click");
    $('#CloneModelBtn').click(function () {
        
        
        
        const btn = document.getElementById('selectModel');
         btn.style.display = 'block';
         $("#selectModelDiv").val("");
         
          $("#addModelModalLabel").html("Choose the model : ");
        
        
        $("#addModelModalTabs").show();
        $('#selectModelDiv').show();
        $('.nav-tabs a[href="#addInfoTabModel"]').tab('show');
        $('#addModelModalBody').show();
        $('#addModelModal div.modalCell').show();
        $('#addNewModelCancelBtn').show();
        $('#addNewModelConfirmBtn').show();
        $('#addNewModelOkBtn').hide();
        $('#addModelOkMsg').hide();
        $('#addModelOkIcon').hide();
        $('#addModelKoMsg').hide();
        $('#addModelKoIcon').hide();
        $('#addModelLoadingMsg').hide();
        $('#addModelLoadingIcon').hide();
        showAddModelModal();
        $("#addSchemaTabModel #addlistAttributes .row input:even").each(function () {
            checkModelValueName($(this));
        });
        checkAddModelConditions();
    });



//Start import new model

    const getJsonUpload = () =>
        new Promise(resolve => {
            const inputFileElement = document.createElement('input')
            inputFileElement.setAttribute('type', 'file')
            inputFileElement.setAttribute('accept', 'application/json')

            inputFileElement.addEventListener(
                'change',
                async (event) => {
                    const { files } = event.target
                    if (!files || event.target.files[0].type !== "application/json") {
                        mydata={error_msg:"Not a valid Json"};
                        console.log(mydata['error_msg']);
                        mydata["status"]="ko";
                        alert("Not a valid JSON. Try Again")
                        $('#addModelKoMsg').show();
                        $('#addModelKoMsg div:first-child').html(mydata["error_msg"]);
                        $('#addModelKoIcon').show();
                        return
                    }

                    const filePromises = [...files].map(file => file.text())

                    resolve(await Promise.all(filePromises))
                },
                false,
            )
            inputFileElement.click()
        })


    document.getElementById('importModelBtn').onclick = async () => {
        let parsedJson;
        const jsonFiles = await getJsonUpload()
        try{
            parsedJson = JSON.parse(jsonFiles);
        }catch (e){
            //devo gestire errore
            console.log(e);
        };
        console.log(parsedJson);
        if(jsonFiles != undefined){
            importModel(parsedJson);
        }
        /*console.log({jsonFiles})*/
    }
    //$("#importModelBtn").off("click");

    function importModel(parsedJson) {

        $("#addModelModalTabs").show();
        $("#addModelModalLabel").html("Import new model ");
        const btn = document.getElementById('selectModel');
        btn.style.display = 'none';
        $("#selectModelDiv").html("");
        $('.nav-tabs a[href="#addInfoTabModel"]').tab('show');
        $('#addModelModalBody').show();
        $('#addModelModal div.modalCell').show();
        $('#addNewModelCancelBtn').show();
        $('#addNewModelConfirmBtn').show();
        $('#addNewModelOkBtn').hide();
        $('#addModelOkMsg').hide();
        $('#addModelOkIcon').hide();
        $('#addModelKoMsg').hide();
        $('#addModelKoIcon').hide();
        $('#addModelLoadingMsg').hide();
        $('#addModelLoadingIcon').hide();
        showAddModelModal();
        //load values from json

        $('#inputNameModel').val(parsedJson.name);
        $('#inputDescriptionModel').val(parsedJson.description);
        $('#inputTypeModel').val(parsedJson.device_type);
        $('#selectKindModel').val(parsedJson.kind);
        $('#inputProducerModel').val(parsedJson.producer);
        $('#inputFrequencyModel').val(parsedJson.frequency);
        $('#selectHCModel').val(parsedJson.healthiness_criteria);
        $('#inputHVModel').val(parsedJson.healthiness_value);
        $('#selectKGeneratorModel').val(parsedJson.key_generator);
        $('#selectEdgeGatewayType').val(parsedJson.edgegateway_type);

        $('#selectContextBroker').val(parsedJson.contextbroker);
        $('#selectProtocolModel').val(parsedJson.protocol);
        $('#selectFormatModel').val(parsedJson.format);
        $('#selectService').val(parsedJson.service);
        $('#inputServicePathModel').val(parsedJson.service_path);


        $('#selectSubnature').val(parsedJson.subnature);
        $('#selectSubnature').trigger('change');
        let static_attributes_from_json = parsedJson.static_attributes.replace(/[\\\[\]"]/g, '');
        static_attributes_from_json = static_attributes_from_json.split(",");
        console.log(static_attributes_from_json)
        for(let i=0;i<= static_attributes_from_json.length;i++){
            if (static_attributes_from_json[i]=="http://www.disit.org/km4city/schema#isMobile" ){
                $('#isMobileTick').prop('checked', true);
            };
        };



        //insert attributes
        var i=0;
        var j=0;
        var rowCount = $("#addlistAttributes .row").length;

        if(rowCount < parsedJson.d_attributes.length) {
            while (i < parsedJson.d_attributes.length) {
                $('#addAttrBtn').click();
                i++;
            }
        }
        //rowCount = $("#addlistAttributes .row").length;

        $("#addlistAttributes").find("[id^=\"InputVNM\"]").each(function() {
            $(this).val(parsedJson.d_attributes[j].value_name);
            j++
        });

        j=0;
        $("#addlistAttributes").find("[id^=\"value_type\"]").each(function() {
            $('option[value='+parsedJson.d_attributes[j].value_type+']',this).attr('selected','selected').change();
            j++;
        });
        j=0
        $("#addlistAttributes").find("[id^=\"value_unit\"]").each(function() {
            $('option[value='+parsedJson.d_attributes[j].value_unit+']',this).attr('selected','selected').change();
            j++
        });
        j=0
        $("#addlistAttributes").find("[id^=\"data_type\"]").each(function() {
            $(this).val(parsedJson.d_attributes[j].data_type).change();
            j++
        });

        $("#addlistAttributes").find("#SELECTHealthCriteria").each(function() {
            $(this).val(parsedJson.d_attributes[j].healthiness_criteria)
            j++
        });

        $("#addlistAttributes").find("#device_refresh_value").each(function() {
            $(this).val(parsedJson.d_attributes[j].healthiness_value)
            j++
        });
        j=0;
        $("#addlistAttributes").find("[id^=\"realtime_flag\"]").each(function() {
            if(parsedJson.d_attributes[j].value_name === "DateObserved" || parsedJson.d_attributes[j].value_name === "dateObserved" ){
                $(this).prop('hidden',true);
                console.log(this.id)
                $(this).find($("label[for='"+this.id+"']").prop('hidden',true));
            }
            if(parsedJson.d_attributes[j].real_time_flag === "true"){
                $(this).prop('checked',true);
            }
            j++
        });
        // $('#addlistAttributes').children('.row').each(function (){
        //
        //     $('#InputVNM',this).val(parsedJson.d_attributes[j].value_name).change();
        //     $('#value_type'+j+' option[value='+parsedJson.d_attributes[j].value_type+']').attr('selected','selected').change();
        //     $('#value_unit'+j+' option[value='+parsedJson.d_attributes[j].value_unit+']').attr('selected','selected').change();
        //     $('#data_type'+j+' option[value='+parsedJson.d_attributes[j].data_type+']').attr('selected','selected').change();
        //     $('#selectHC option[value='+parsedJson.d_attributes[j].healthiness_criteria+']',this).attr('selected','selected').change();
        //     $('#InputHV',this).val(parsedJson.d_attributes[j].healthiness_value).change();
        //     j++;
        // })



        $("#addSchemaTabModel #addlistAttributes .row input:even").each(function () {
            checkModelValueName($(this));
        });
        checkModelName();
        checkModelDeviceType();
        checkSelectionCB();
        checkSelectionProtocol();
        checkSelectionFormat();
        checkAddModelConditions();
    };

    /* add lines related to attributes*/
    $("#addAttrBtn").off("click");
    $("#addAttrBtn").click(function () {
        content = drawAttributeMenu("", "", "", "", "", "", "300", 'addlistAttributes', indexValues);
        indexValues = indexValues + 1;
        // addDeviceConditionsArray['addlistAttributes'] = true;
        $('#addlistAttributes').append(content);
        checkAtlistOneAttribute();
        $("#addSchemaTabModel #addlistAttributes .row input:even").each(function () {
            checkModelValueName($(this));
        });
        checkAddModelConditions();
    });
    $("#addSchemaTabModel").off("click");
    $("#addSchemaTabModel").on('click keyup', function () {

        //checkAtlistOneAttribute();
        $("#addSchemaTabModel #addlistAttributes .row input:even").each(function () {
            checkModelValueName($(this));
        });
        checkAddModelConditions();
    });
    function checkIfDuplicateExists(arr) {
        return new Set(arr).size !== arr.length;
    }
    $('#addNewModelConfirmBtn').off("click");
    $('#addNewModelConfirmBtn').click(function () {
        mynewAttributes = [];
        var regex = /[^a-z0-9_-]/gi;
        var someNameisWrong = false;
        var msg_whatiswrong = "";
        AttrName = [];
        num1 = document.getElementById('addlistAttributes').childElementCount;
        for (var m = 0; m < num1; m++)
        {
            AttrName.push(document.getElementById('addlistAttributes').childNodes[m].childNodes[0].childNodes[0].childNodes[0].value.trim());
            var newatt = {value_name: document.getElementById('addlistAttributes').childNodes[m].childNodes[0].childNodes[0].childNodes[0].value.trim(),
                data_type: document.getElementById('addlistAttributes').childNodes[m].childNodes[3].childNodes[0].childNodes[0].value.trim(),
                value_type: document.getElementById('addlistAttributes').childNodes[m].childNodes[1].childNodes[0].childNodes[0].value.trim(),
                editable: '0',
                value_unit: document.getElementById('addlistAttributes').childNodes[m].childNodes[2].childNodes[0].childNodes[0].value.trim(),
                healthiness_criteria: document.getElementById('addlistAttributes').childNodes[m].childNodes[5].childNodes[0].childNodes[0].value.trim(),
                healthiness_value: document.getElementById('addlistAttributes').childNodes[m].childNodes[6].childNodes[0].childNodes[0].value.trim(),
                real_time_flag: document.getElementById('addlistAttributes').childNodes[m].childNodes[7].childNodes[0].childNodes[0].checked.toString()};
            if (newatt.value_name == "" || regex.test(newatt.value_name) || newatt.value_name.length < 2) {
                someNameisWrong = true;
                msg_whatiswrong += "The Value name must be at least 2 characters. No special characters are allowed. "
            }
            if (newatt.data_type == "") {
                someNameisWrong = true;
                msg_whatiswrong += "The data type cannot be empty. ";
            }
            if (newatt.value_type == "") {
                someNameisWrong = true;
                msg_whatiswrong += "The value type cannot be empty. ";
            }
//            if (newatt.editable == "") {
//                someNameisWrong = true;
//                msg_whatiswrong += "The field 'Editable' must be specified. ";
//            }
            if (newatt.value_unit == "") {
                someNameisWrong = true;
                msg_whatiswrong += "The field 'value unit' must be specified. ";
            }
            if (newatt.healthiness_criteria == "") {
                someNameisWrong = true;
                msg_whatiswrong += "The field 'healthiness criteria' must be specified. ";
            }
            if (newatt.healthiness_value == "") {
                someNameisWrong = true;
                msg_whatiswrong += "The field 'healthiness value' must be specified. ";
            }
            if (!someNameisWrong) {
                mynewAttributes.push(newatt);
            }
        }

        if (checkIfDuplicateExists(AttrName)) {
            someNameisWrong = true;
            msg_whatiswrong += "The value name must be unique. ";
        }
        if ($('#selectKindModel').val() == '') {
            someNameisWrong = true;
            msg_whatiswrong += "The kind must be specificated. ";
        }

        if (!someNameisWrong) {
            //document.getElementById('addlistAttributes').innerHTML = "";

            $("#addModelModalTabs").hide();
            $('#addModelModalBody').hide();
            $('#addModelModal div.modalCell').hide();
            $('#addNewModelCancelBtn').hide();
            $('#addNewModelConfirmBtn').hide();
            $('#addNewModelOkBtn').hide();
            $('#addModelOkMsg').hide();
            $('#addModelOkIcon').hide();
            $('#addModelKoMsg').hide();
            $('#addModelKoIcon').hide();
            $('#addModelLoadingMsg').show();
            $('#addModelLoadingIcon').show();
            var service = $('#selectService').val();
            var servicePath = $('#inputServicePathModel').val();
            if ($('#selectProtocolModel').val() === "ngsi w/MultiService") {
                // servicePath value pre-processing
                if (servicePath[0] !== "/" || servicePath === "")
                    servicePath = "/" + servicePath;
                if (servicePath[servicePath.length - 1] === "/" && servicePath.length > 1)
                    servicePath = servicePath.substr(0, servicePath.length - 1);
            }

            $.ajax({
                url: "../api/model.php",
                data: {
                    action: "insert",
                    attributes: JSON.stringify(mynewAttributes),
                    name: $('#inputNameModel').val(),
                    description: $('#inputDescriptionModel').val(),
                    type: $('#inputTypeModel').val(),
                    kind: $('#selectKindModel').val(),
                    producer: $('#inputProducerModel').val(),
                    frequency: $('#inputFrequencyModel').val(),
                    kgenerator: $('#selectKGeneratorModel').val(),
                    edgegateway_type: $('#selectEdgeGatewayType').val(),
                    contextbroker: $('#selectContextBroker').val(),
                    protocol: $('#selectProtocolModel').val(),
                    format: $('#selectFormatModel').val(),
                    hc: $('#selectHCModel').val(),
                    hv: $('#inputHVModel').val(),
                    subnature: $('#selectSubnature').val(),
                    static_attributes: JSON.stringify(retrieveStaticAttributes("addlistStaticAttributes", false, "isMobileTick", "isCertifiedTick")),
                    service: service,
                    servicePath: servicePath,
                    token: sessionToken
                },
                type: "POST",
                async: true,
                dataType: "JSON",
                timeout: 0,
                success:  function (mydata) {
                    if (mydata["status"] === 'ko') {
                        console.log("Error adding Model");
                        console.log(mydata);
                        $('#addModelModalTabs').hide();
                        $('#addModelModalBody').hide();
                        $('#addModelModal div.modalCell').hide();
                        //$('#addContextBrokerModalFooter').hide();
                        $('#addNewModelCancelBtn').hide();
                        $('#addNewModelConfirmBtn').hide();
                        $('#addNewModelOkBtn').show();
                        $('#addModelOkMsg').hide();
                        $('#addModelOkIcon').hide();
                        $('#addModelLoadingMsg').hide();
                        $('#addModelLoadingIcon').hide();
                        $('#addModelKoMsg').show();
                        $('#addModelKoMsg div:first-child').html(mydata["error_msg"]);
                        $('#addModelKoIcon').show();
                    } else if (mydata["status"] === 'ok') {
                        console.log("Added Model");
                        //empty information
                        $('#inputNameModel').val("");
                        $('#inputDescriptionModel').val("");
                        $('#inputTypeModel').val("");
                        $('#selectKindModel').val("");
                        $('#selectHCModel').val("");
                        $('#inputHVModel').val("");
                        $('#selectContextBroker').val("");
                        $('#selectProtocolModel').val("");
                        $('#selectFormatModel').val("");
                        $('#inputProducerModel').val("");
                        $('#inputFrequencyModel').val("");
                        $('#selectKGeneratorModel').val("");
                        $('#addlistAttributes').html("");
                        $('#selectSubnature').val("");
                        $('#selectSubnature').trigger("change");
                        $("#addNewStaticBtn").hide();
                        removeStaticAttributes();
                        $('#addModelLoadingMsg').hide();
                        $('#addModelLoadingIcon').hide();
                        $('#addModelKoMsg').hide();
                        $('#addModelKoIcon').hide();
                        $('#addModelModalTabs').hide();
                        $('#addModelModalBody').hide();
                        $('#addModelModal div.modalCell').hide();
                        //$('#addContextBrokerModalFooter').hide();
                        $('#addNewModelCancelBtn').hide();
                        $('#addNewModelConfirmBtn').hide();
                        $('#addNewModelOkBtn').show();
                        $('#addModelOkMsg').show();
                        $('#addModelOkIcon').show();
                        $('#dashboardTotNumberCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotNumberCnt .pageSingleDataCnt').html()) + 1);
                        $('#modelTable').DataTable().destroy();
                        fetch_data(true);
                    }
                },
                error: function (mydata) {
                    console.log("Error insert model");
                    console.log("Error status -- Ko result: " + JSON.stringify(mydata));
                    $("#addModelModal").modal('hide');
                    $('#addModelModalTabs').hide();
                    $('#addModelModalBody').hide();
                    $('#addModelModal div.modalCell').hide();
                    $('#addModelModalFooter').hide();
                    $('#addModelOkMsg').hide();
                    $('#addModelOkIcon').hide();
                    $('#addModelKoMsg').show();
                    $('#addModelKoIcon').show();
                    $('#addNewModelCancelBtn').hide();
                    $('#addNewModelConfirmBtn').hide();
                    $('#addNewModelOkBtn').show();
                    $('#addModelLoadingMsg').hide();
                    $('#addModelLoadingIcon').hide();
                }
            });
        } else {
            alert("Check the values of your device, make sure that data you entered are valid. " + msg_whatiswrong);
        }
    });



//START ADD NEW Model  (INSERT INTO DB)


    // This is loading validation when the cursor is on 
    $("#addModelBtn").off("click");
    $('#addModelBtn').click(function () {
        
        $("#addModelModalTabs").show();
         $("#addModelModalLabel").html("Add new model ");
        const btn = document.getElementById('selectModel');
         btn.style.display = 'none';
         $("#selectModelDiv").html("");
        $('.nav-tabs a[href="#addInfoTabModel"]').tab('show');
        $('#addModelModalBody').show();
        $('#addModelModal div.modalCell').show();
        $('#addNewModelCancelBtn').show();
        $('#addNewModelConfirmBtn').show();
        $('#addNewModelOkBtn').hide();
        $('#addModelOkMsg').hide();
        $('#addModelOkIcon').hide();
        $('#addModelKoMsg').hide();
        $('#addModelKoIcon').hide();
        $('#addModelLoadingMsg').hide();
        $('#addModelLoadingIcon').hide();
        showAddModelModal();
        $("#addSchemaTabModel #addlistAttributes .row input:even").each(function () {
            checkModelValueName($(this));
        });
        checkAddModelConditions();
    });
    /* add lines related to attributes*/
    $("#addAttrBtn").off("click");
    $("#addAttrBtn").click(function () {
        content = drawAttributeMenu("", "", "", "", "", "", "300","", 'addlistAttributes', indexValues);
        indexValues = indexValues + 1;
        // addDeviceConditionsArray['addlistAttributes'] = true;
        $('#addlistAttributes').append(content);
        checkAtlistOneAttribute();
        $("#addSchemaTabModel #addlistAttributes .row input:even").each(function () {
            checkModelValueName($(this));
        });
        $("#addSchemaTabModel #addlistAttributes .row input:odd").each(function () {
            checkModelValueName($(this));
        });
        checkAddModelConditions();
    });
    $("#addlistAttributes").on("keyup", "input[id^='InputVNM']", function() {
        // Get the value of the input field you're currently interacting with
        var value = $(this).val();
        let eventFiredIndex = event.target.id
        eventFiredIndex = eventFiredIndex.match(/\d+$/);
        if (value === 'DateObserved' || value === 'dateObserved' ){
            $("#realtime_flag"+eventFiredIndex[0]).hide()
            $('label[for="realtime_flag' + eventFiredIndex + '"]').hide();
        }else {
            $("#realtime_flag"+eventFiredIndex[0]).show()
            $('label[for="realtime_flag' + eventFiredIndex + '"]').show();
        }
        checkAddModelConditions();
    });
    $("#addSchemaTabModel").off("click");
    $("#addSchemaTabModel").on('click keyup', function () {

        //checkAtlistOneAttribute();
        $("#addSchemaTabModel #addlistAttributes .row input:even").each(function () {
            checkModelValueName($(this));
        });
        $("#addSchemaTabModel #addlistAttributes .row input:odd").each(function () {
            checkModelValueName($(this));
        });
        checkAddModelConditions();
    });
//--------------------------------------------------------------------------------------------------------------ADD NEW MODEL



    function checkIfDuplicateExists(arr) {
        return new Set(arr).size !== arr.length;
    }
    $('#addNewModelConfirmBtn').off("click");
    $('#addNewModelConfirmBtn').click(function () {
        mynewAttributes = [];
        var regex = /[^a-z0-9_-]/gi;
        var regexSensibleInputName= /\b(?:id|type|value)\b/i;
        var someNameisWrong = false;
        var msg_whatiswrong = "";
        AttrName = [];
        num1 = document.getElementById('addlistAttributes').childElementCount;
        for (var m = 0; m < num1; m++)
        {
            AttrName.push(document.getElementById('addlistAttributes').childNodes[m].childNodes[0].childNodes[0].childNodes[0].value.trim());
            var newatt = {value_name: document.getElementById('addlistAttributes').childNodes[m].childNodes[0].childNodes[0].childNodes[0].value.trim(),
                data_type: document.getElementById('addlistAttributes').childNodes[m].childNodes[3].childNodes[0].childNodes[0].value.trim(),
                value_type: document.getElementById('addlistAttributes').childNodes[m].childNodes[1].childNodes[0].childNodes[0].value.trim(),
                editable: '0',
                value_unit: document.getElementById('addlistAttributes').childNodes[m].childNodes[2].childNodes[0].childNodes[0].value.trim(),
                healthiness_criteria: document.getElementById('addlistAttributes').childNodes[m].childNodes[5].childNodes[0].childNodes[0].value.trim(),
                healthiness_value: document.getElementById('addlistAttributes').childNodes[m].childNodes[6].childNodes[0].childNodes[0].value.trim(),
                real_time_flag: document.getElementById('addlistAttributes').childNodes[m].childNodes[7].childNodes[0].childNodes[0].checked.toString()};
            if (newatt.value_name == "" || regex.test(newatt.value_name) || newatt.value_name.length < 2) {
                someNameisWrong = true;
                msg_whatiswrong += "The Value name must be at least 2 characters. No special characters are allowed. "
            }
            if(regexSensibleInputName.test(newatt.value_name)){
                someNameisWrong = true;
                msg_whatiswrong += "No reserved names allowed for Attributes name"
            }
            if (newatt.data_type == "") {
                someNameisWrong = true;
                msg_whatiswrong += "The data type cannot be empty. ";
            }
            if (newatt.value_type == "") {
                someNameisWrong = true;
                msg_whatiswrong += "The value type cannot be empty. ";
            }
//            if (newatt.editable == "") {
//                someNameisWrong = true;
//                msg_whatiswrong += "The field 'Editable' must be specified. ";
//            }
            if (newatt.value_unit == "") {
                someNameisWrong = true;
                msg_whatiswrong += "The field 'value unit' must be specified. ";
            }
            if (newatt.healthiness_criteria == "") {
                someNameisWrong = true;
                msg_whatiswrong += "The field 'healthiness criteria' must be specified. ";
            }
            if (newatt.healthiness_value == "") {
                someNameisWrong = true;
                msg_whatiswrong += "The field 'healthiness value' must be specified. ";
            }
            if (!someNameisWrong) {
                mynewAttributes.push(newatt);
            }
        }

        if (checkIfDuplicateExists(AttrName)) {
            someNameisWrong = true;
            msg_whatiswrong += "The value name must be unique. ";
        }
        if ($('#selectKindModel').val() == '') {
            someNameisWrong = true;
            msg_whatiswrong += "The kind must be specificated. ";
        }

        if (!someNameisWrong) {
            //document.getElementById('addlistAttributes').innerHTML = "";			

            $("#addModelModalTabs").hide();
            $('#addModelModalBody').hide();
            $('#addModelModal div.modalCell').hide();
            $('#addNewModelCancelBtn').hide();
            $('#addNewModelConfirmBtn').hide();
            $('#addNewModelOkBtn').hide();
            $('#addModelOkMsg').hide();
            $('#addModelOkIcon').hide();
            $('#addModelKoMsg').hide();
            $('#addModelKoIcon').hide();
            $('#addModelLoadingMsg').show();
            $('#addModelLoadingIcon').show();
            var service = $('#selectService').val();
            var servicePath = $('#inputServicePathModel').val();
            if ($('#selectProtocolModel').val() === "ngsi w/MultiService") {
                // servicePath value pre-processing
                if (servicePath[0] !== "/" || servicePath === "")
                    servicePath = "/" + servicePath;
                if (servicePath[servicePath.length - 1] === "/" && servicePath.length > 1)
                    servicePath = servicePath.substr(0, servicePath.length - 1);
            }

            $.ajax({
                url: "../api/model.php",
                data: {
                    action: "insert",
                    attributes: JSON.stringify(mynewAttributes),
                    name: $('#inputNameModel').val(),
                    description: $('#inputDescriptionModel').val(),
                    type: $('#inputTypeModel').val(),
                    kind: $('#selectKindModel').val(),
                    producer: $('#inputProducerModel').val(),
                    frequency: $('#inputFrequencyModel').val(),
                    kgenerator: $('#selectKGeneratorModel').val(),
                    edgegateway_type: $('#selectEdgeGatewayType').val(),
                    contextbroker: $('#selectContextBroker').val(),
                    protocol: $('#selectProtocolModel').val(),
                    format: $('#selectFormatModel').val(),
                    hc: $('#selectHCModel').val(),
                    hv: $('#inputHVModel').val(),
                    subnature: $('#selectSubnature').val(),
                    static_attributes: JSON.stringify(retrieveStaticAttributes("addlistStaticAttributes", false, "isMobileTick")),
                    service: service,
                    servicePath: servicePath,
                    HLT: $('#selectHLT').val(),
                    token: sessionToken

                },
                type: "POST",
                async: true,
                dataType: "JSON",
                timeout: 0,
                success: function (mydata) {
                    if (mydata["status"] === 'ko') {
                        console.log("Error adding Model");
                        console.log(mydata);
                        $('#addModelModalTabs').hide();
                        $('#addModelModalBody').hide();
                        $('#addModelModal div.modalCell').hide();
                        $("#addModelModal").modal('hide');
                        //$('#addContextBrokerModalFooter').hide();
                        $('#addNewModelCancelBtn').hide();
                        $('#addNewModelConfirmBtn').hide();
                        $('#addNewModelOkBtn').show();
                        $('#addModelOkMsg').hide();
                        $('#addModelOkIcon').hide();
                        $('#addModelLoadingMsg').hide();
                        $('#addModelLoadingIcon').hide();
                        $('#addModelKoMsg').show();
                        $('#addModelKoMsg div:first-child').html(mydata["error_msg"]);
                        $('#addModelKoIcon').show();
                    } else if (mydata["status"] === 'ok') {
                        console.log("Added Model");
                        //empty information
                        $('#inputNameModel').val("");
                        $('#inputDescriptionModel').val("");
                        $('#inputTypeModel').val("");
                        $('#selectKindModel').val("");
                        $('#selectHCModel').val("");
                        $('#inputHVModel').val("");
                        $('#selectContextBroker').val("");
                        $('#selectProtocolModel').val("");
                        $('#selectFormatModel').val("");
                        $('#inputProducerModel').val("");
                        $('#inputFrequencyModel').val("");
                        $('#selectKGeneratorModel').val("");
                        $('#addlistAttributes').html("");
                        $('#selectSubnature').val("");
                        $('#selectSubnature').trigger("change");
                        $('#selectHLT').val("");
                        $('#selectHLT').trigger("change");
                        $("#addNewStaticBtn").hide();
                        removeStaticAttributes();
                        $('#addModelLoadingMsg').hide();
                        $('#addModelLoadingIcon').hide();
                        $('#addModelKoMsg').hide();
                        $('#addModelKoIcon').hide();
                        $('#addModelModalTabs').hide();
                        $('#addModelModalBody').hide();
                        $('#addModelModal div.modalCell').hide();
                        //$('#addContextBrokerModalFooter').hide();
                        $('#addNewModelCancelBtn').hide();
                        $('#addNewModelConfirmBtn').hide();
                        $('#addNewModelOkBtn').show();
                        $('#addModelOkMsg').show();
                        $('#addModelOkIcon').show();
                        $("#addModelModal").modal('hide');
                        $('#dashboardTotNumberCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotNumberCnt .pageSingleDataCnt').html()) + 1);
                        $('#modelTable').DataTable().destroy();
                        fetch_data(true);
                    }
                },
                error: function (mydata) {
                    console.log("Error insert model");
                    console.log("Error status -- Ko result: " + JSON.stringify(mydata));
                    $("#addModelModal").modal('hide');
                    $('#addModelModalTabs').hide();
                    $('#addModelModalBody').hide();
                    $('#addModelModal div.modalCell').hide();
                    $('#addModelModalFooter').hide();
                    $('#addModelOkMsg').hide();
                    $('#addModelOkIcon').hide();
                    $('#addModelKoMsg').show();
                    $('#addModelKoIcon').show();
                    $('#addNewModelCancelBtn').hide();
                    $('#addNewModelConfirmBtn').hide();
                    $('#addNewModelOkBtn').show();
                    $('#addModelLoadingMsg').hide();
                    $('#addModelLoadingIcon').hide();
                }
            });
        } else {
            alert("Check the values of your device, make sure that data you entered are valid. " + msg_whatiswrong);
        }
    });
// END ADD NEW MODEL  


//START DELETE MODEL 

    // Delete lines related to attributes 

    $("#attrNameDelbtn").off("click");
    $("#attrNameDelbtn").on("click", function () {
        $(this).parent('tr').remove();
    });
    $('#modelTable tbody').on('click', 'button.delDashBtn', function () {

        var name = $(this).attr("data-name");
        var id = $(this).attr("data-id");
        $("#deleteModelModal div.modal-body").html('<div class="modalBodyInnerDiv"><span data-id = "' + id + '" data-name = "' + name + '" >Do you want to confirm deletion of model ID <b>' + id + " - " + name + '</b>?</span></div>');
        $("#deleteModelModal").modal('show');
    });
    $('#deleteModelConfirmBtn').off("click");
    $("#deleteModelConfirmBtn").click(function () {

        var id = $("#deleteModelModal span").attr("data-id");
        var name = $("#deleteModelModal span").attr("data-name");
        //Sara2510 - for logging purpose
        /*$("#deleteModelModal div.modal-body").html("");
         $("#deleteModelCancelBtn").hide();
         $("#deleteModelConfirmBtn").hide();
         $("#deleteModelModal div.modal-body").append('<div id="deleteModelModalInnerDiv1" class="modalBodyInnerDiv"><h5>Model deletion in progress, please wait</h5></div>');
         $("#deleteModelModal div.modal-body").append('<div id="deleteModelModalInnerDiv2" class="modalBodyInnerDiv"><i class="fa fa-circle-o-notch fa-spin" style="font-size:36px"></i></div>');*/


        $("#deleteModelModal div.modal-body").html("");
        $("#deleteModelCancelBtn").hide();
        $("#deleteModelConfirmBtn").hide();
        $("#deleteModelOkBtn").hide();
        $("#deleteModelModalInnerDiv1").show();
        $("#deleteModelModalInnerDiv2").show();
        $.ajax({
            url: "../api/model.php",
            data: {
                action: "delete",
                id: id,
                token: sessionToken
            },
            type: "POST",
            datatype: "json",
            async: true,
            success: function (data)
            {
                $("#deleteModelOkBtn").show();
                if (data["status"] === 'ko')
                {

                    $("#deleteModelModalInnerDiv1").html(data["error_msg"]);
                    $("#deleteModelModalInnerDiv2").html('<i class="fa fa-frown-o" style="font-size:42px"></i>');
                } else if (data["status"] === 'ok')
                {

                    $("#deleteModelModalInnerDiv1").html('Model &nbsp; deleted successfully');
                    $("#deleteModelModalInnerDiv1").show();
                    $("#deleteModelModalInnerDiv2").html('<i class="fa fa-check" style="font-size:42px"></i>');
                    $('#dashboardTotNumberCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotNumberCnt .pageSingleDataCnt').html()) - 1);
                    $('#modelTable').DataTable().destroy();
                    fetch_data(true);
                }
            },
            error: function (data)
            {
                console.log(JSON.stringify(data));
                $("#deleteModelOkBtn").show();
                $("#deleteModelModalInnerDiv1").html(data["error_msg"]);
                $("#deleteModelModalInnerDiv2").html('<i class="fa fa-frown-o" style="font-size:42px"></i>');
            }
        });
    });
    $("#deleteModelOkBtn").off("click");
    $("#deleteModelOkBtn").click(function () {
        $("#deleteModelModal div.modal-body").html("Do you want to confirm deletion of the following Model?");
        $("#deleteModelOkBtn").hide();
        $("#deleteModelCancelBtn").show();
        $("#deleteModelConfirmBtn").show();
        $("#deleteModelModalInnerDiv1").html('<h5>Model deletion in progress, please wait</h5>');
        $("#deleteModelModalInnerDiv2").html('<i class="fa fa-circle-o-notch fa-spin" style="font-size:36px"></i>');
        $("#deleteModelModalInnerDiv1").hide();
        $("#deleteModelModalInnerDiv2").hide();
    });
// END DELETE MODEL




    function get_form(mode) {
        $('#editModelModalTabs').show();
        $('.nav-tabs a[href="#editInfoTabModel"]').tab('show');
        $("#editModelModalBody").show();
        $("#editModelModalFooter").show();
        $("#editModelCancelBtn").show();
        $("#editModelConfirmBtn").show();
        $("#editModelModal").modal('show');
        $('#editModelLoadingMsg').hide();
        $('#editModelLoadingIcon').hide();
        $('#editModelOkMsg').hide();
        $('#editModelOkIcon').hide();
        $('#editModelKoMsg').hide();
        $('#editModelKoIcon').hide();
        $('#editModelOkBtn').hide();
        $('#editModelModal div.modalCell').show();
        $("#addAttrMBtn").show();
        $('#editlistAttributes').html("");
        $('#inputNameModelM').attr('readonly', mode);
        $('#inputDescriptionModelM').attr('readonly', mode);
        $('#inputDescriptionModelM').attr('readonly', mode);
        $('#inputTypeModelM').attr('readonly', mode);
        $('#selectKindModelM').prop('disabled', mode);
        $('#inputProducerModelM').attr('readonly', mode);
        $('#inputFrequencyModelM').attr('readonly', mode);
        $('#selectKGeneratorModelM').prop('disabled', mode);
        $('#selectEdgeGatewayTypeM').prop('disabled', mode);
        $('#selectContextBrokerM').prop('disabled', mode);
        $('#selectProtocolModelM').prop('disabled', mode);
        $('#selectFormatModelM').prop('disabled', mode);
        $('#selectHCModelM').prop('disabled', mode);
        $('#inputHVModelM').attr('readonly', mode);
        $('#selectSubnatureM').prop('disabled', mode);
        $('#selectHLTM').prop('disabled', mode);
        document.getElementById("isMobileTickM").disabled = mode;
    }

    // START VIEW MODEL
    $('#modelTable tbody').on('click', 'button.testDashBtn', function () {

        get_form(true);
        $("#editModelModalLabel").html("View Model - " + $(this).attr("data-name"));
        var id = $(this).attr('data-id');
        var obj_organization = $(this).attr('data-organization');
        var name = $(this).attr('data-name');
        var description = $(this).attr('data-description');
        var type = $(this).attr('data-devicetype');
        var kind = $(this).attr('data-kind');
        var producer = $(this).attr('data-producer');
        var frequency = $(this).attr('data-frequency');
        var kgenerator = $(this).attr('data-kgenerator');
        var edgegateway_type = $(this).attr('data-edgegateway_type');
        var contextbroker = $(this).attr('data-contextBroker');
        var protocol = $(this).attr('data-protocol');
        var format = $(this).attr('data-format');
        var subnature = $(this).attr('data-subnature');
        var hc = $(this).attr('data-healthiness_criteria');
        var hv = $(this).attr('data-healthiness_value');
        var hlt =$(this).attr('data-hlt')
        $('#inputIdModelM').val(id);
        $('#inputOrganizationModelM').val(obj_organization);
        $('#inputNameModelM').val(name);
        $('#inputDescriptionModelM').val(description);
        $('#inputTypeModelM').val(type);
        $('#selectKindModelM').val(kind);
        $('#inputProducerModelM').val(producer);
        $('#inputFrequencyModelM').val(frequency);
        $('#selectKGeneratorModelM').val(kgenerator);
        $('#selectEdgeGatewayTypeM').val(edgegateway_type);
        $('#selectContextBrokerM').val(contextbroker);
        $('#selectProtocolModelM').val(protocol);
        $('#selectFormatModelM').val(format);
        $('#selectHCModelM').val(hc);
        $('#inputHVModelM').val(hv);
        $('#selectSubnatureM').val(subnature);
        $('#selectSubnatureM').trigger('change');
        $('#selectHLTM').val(hlt);
        $('#selectHLTM').trigger('change');
        subnatureChanged("view", JSON.parse(atob($(this).attr("data-static-attributes"))));
        $('#addNewStaticBtnM').hide();
        fillMultiTenancyFormSection($(this).attr('data-service'), $(this).attr('data-servicePath'), contextbroker, 'model');
        showEditModelModal();
        $('#editModelConfirmBtn').hide();
        $('#addAttrMBtn').hide();
        $('a[data-toggle="tab"]').off('shown.bs.tab').on('shown.bs.tab', function (e) {
            var target = $(e.target).attr("href");
            if ((target == '#editSchemaTabModel')) {

                document.getElementById('editlistAttributes').innerHTML = "";
                document.getElementById('addlistAttributesM').innerHTML = "";
                document.getElementById('deletedAttributes').innerHTML = "";
                $('#editModelLoadingIcon').show();
                $.ajax({
                    url: "../api/model.php",
                    data: {
                        action: "get_value_attributes_read",
                        id: document.getElementById('inputIdModelM').value,
                        token: sessionToken
                    },
                    type: "POST",
                    async: true,
                    dataType: 'json',
                    success: function (mydata)
                    {

                        var row = null;
                        $("#editUserPoolsTable tbody").empty();
                        var myattributes = JSON.parse(mydata.content.attributes);
                        content = "";
                        k = 0;
                        while (k < myattributes.length)
                        {
                            content = drawAttributeMenu(myattributes[k].value_name,
                                    myattributes[k].data_type, myattributes[k].value_type, myattributes[k].editable, myattributes[k].value_unit, myattributes[k].healthiness_criteria,
                                    myattributes[k].healthiness_value,myattributes[k].real_time_flag, 'editlistAttributes', indexValues);
                            indexValues = indexValues + 1;
                            k++;
                            $('#editlistAttributes').append(content);
                        }

                        checkEditAtlistOneAttributeM();
                        $("#editSchemaTabModel #editlistAttributes .row input:even").each(function () {
                            checkModelValueName($(this));
                        });
                        checkAddModelConditions();
//                   $("#RemoveButtAttr").hide();
//                  $("#CopyButtAttr").hide();
                        $(".Hidebutton").hide();
                        $(':checkbox').prop('disabled', true);
                        $('.Input_readoonly').attr('readonly', true);
                        $('.Select_readoonly').prop('disabled', true);
                        $('#editModelLoadingIcon').hide();
                    },
                    error: function (data)
                    {
                        alert("Error in reading data from the database<br/> Please get in touch with the Snap4city Administrator");
//				
                        $('#editlistAttributes').html("");
                        //$("#editModalModal").modal('show');

                    }
                });
            }
        });
    });
// END VIEW MODEL 


    //START EDIT MODEL 

    //add lines related to attributes in case of edit
    $("#addAttrMBtn").off("click");
    $("#addAttrMBtn").click(function () {
        content = drawAttributeMenu("", "", "", "", "", "", "300","", 'addlistAttributesM', indexValues);
        indexValues = indexValues + 1;
        $('#addlistAttributesM').append(content);
        checkEditAtlistOneAttributeM();
        $("#editSchemaTabModel #addistAttributesM .row input:even").each(function () {
            checkModelValueName($(this));
        });
        checkEditModelConditions();
    });
    $("#addlistAttributesM").on("keyup", "input[id^='InputVNM']", function() {
        // Get the value of the input field you're currently interacting with
        var value = $(this).val();
        let eventFiredIndex = event.target.id
        eventFiredIndex = eventFiredIndex.match(/\d+$/);
        if (value === 'DateObserved'){
            $("#realtime_flag"+eventFiredIndex[0]).hide()
            $('label[for="realtime_flag' + eventFiredIndex + '"]').hide();
        }else {
            $("#realtime_flag"+eventFiredIndex[0]).show()
            $('label[for="realtime_flag' + eventFiredIndex + '"]').show();
        }
    });
    $("#editSchemaTabModel").off("click");
    $("#editSchemaTabModel").on('click keyup', function () {

     // check if an edit of a model's attribute name is acceptable
        $("#editSchemaTabModel #editlistAttributes .row input:even").each(function () { //:even
           checkModelValueName($(this));
        })
        $("#editSchemaTabModel #editlistAttributes .row input:odd").each(function () {
            checkModelValueName($(this));
        })
        $("#editSchemaTabModel #addlistAttributesM .row input").each(function () { //:even
            checkModelValueNameM($(this));
        });
        checkEditModelConditions();
    });
    $('#modelTable tbody').on('click', 'button.editDashBtn', function () {

        get_form(false);
        $("#editModelModalLabel").html("Edit Model - " + $(this).attr("data-name"));
        var id = $(this).attr('data-id');
        var obj_organization = $(this).attr('data-organization');
        var name = $(this).attr('data-name');
        var description = $(this).attr('data-description');
        var type = $(this).attr('data-devicetype');
        var kind = $(this).attr('data-kind');
        var producer = $(this).attr('data-producer');
        var frequency = $(this).attr('data-frequency');
        var kgenerator = $(this).attr('data-kgenerator');
        var edgegateway_type = $(this).attr('data-edgegateway_type');
        var contextbroker = $(this).attr('data-contextBroker');
        var protocol = $(this).attr('data-protocol');
        var format = $(this).attr('data-format');
        var subnature = $(this).attr('data-subnature');
        var hc = $(this).attr('data-healthiness_criteria');
        var hv = $(this).attr('data-healthiness_value');
        var hlt = $(this).attr('data-hlt');
        $('#inputIdModelM').val(id);
        $('#inputOrganizationModelM').val(obj_organization);
        $('#inputNameModelM').val(name);
        $('#inputDescriptionModelM').val(description);
        $('#inputTypeModelM').val(type);
        $('#selectKindModelM').val(kind);
        $('#inputProducerModelM').val(producer);
        $('#inputFrequencyModelM').val(frequency);
        $('#selectKGeneratorModelM').val(kgenerator);
        $('#selectEdgeGatewayTypeM').val(edgegateway_type);
        $('#selectContextBrokerM').val(contextbroker);
        $('#selectProtocolModelM').val(protocol);
        $('#selectFormatModelM').val(format);
        $('#selectHCModelM').val(hc);
        $('#inputHVModelM').val(hv);
        $('#selectSubnatureM').val(subnature);
        $('#selectSubnatureM').trigger('change');

        if(hlt === "null"){
            console.log("prova")
            $('#selectHLTM').val($("#selectHLTM option:first").val());
        }else{
            $('#selectHLTM').val(hlt);
        }
        $('#selectHLTM').trigger('change');
        subnatureChanged(true, JSON.parse(atob($(this).attr("data-static-attributes"))));
        fillMultiTenancyFormSection($(this).attr('data-service'), $(this).attr('data-servicePath'), contextbroker, 'model');
        showEditModelModal();
        $('a[data-toggle="tab"]').off('shown.bs.tab').on('shown.bs.tab', function (e) {
            var target = $(e.target).attr("href");
            if ((target == '#editSchemaTabModel')) {

                if (document.querySelector("#addlistAttributesM > div") == null) {
                    document.getElementById('addlistAttributesM').innerHTML = "";
                }

                document.getElementById('editlistAttributes').innerHTML = "";
                document.getElementById('deletedAttributes').innerHTML = "";
                $.ajax({
                    url: "../api/model.php",
                    data: {
                        action: "get_value_attributes",
                        id: document.getElementById('inputIdModelM').value,
                        token: sessionToken
                    },
                    type: "POST",
                    async: true,
                    dataType: 'json',
                    success: function (mydata)
                    {

                        var row = null;
                        $("#editUserPoolsTable tbody").empty();
                        var myattributes = JSON.parse(mydata.content.attributes);
                        content = "";
                        k = 0;
                        while (k < myattributes.length)
                        {
                            content = drawAttributeMenu(myattributes[k].value_name,
                                    myattributes[k].data_type, myattributes[k].value_type, myattributes[k].editable, myattributes[k].value_unit, myattributes[k].healthiness_criteria,
                                    myattributes[k].healthiness_value,myattributes[k].real_time_flag, 'editlistAttributes', indexValues);
                            indexValues = indexValues + 1;
                            k++;
                            $('#editlistAttributes').append(content);
                        }
                        checkEditAtlistOneAttributeM();
                        $("#editSchemaTabModel #editlistAttributes .row input:even").each(function () {
                            checkModelValueNameM($(this));
                        });
                        checkAddModelConditions();
                    },
                    error: function (data)
                    {
                        alert("Error in reading data from the database<br/> Please get in touch with the Snap4city Administrator");
                        $('#inputNameModelM').val("");
                        $('#inputDescriptionModelM').val("");
                        $('#inputTypeModelM').val("");
                        $('#selectKindModelM').val("");
                        //$('#selectActiveModelM').val("");
                        $('#selectHCModelM').val("");
                        $('#inputHVModelM').val("");
                        $('#selectContextBrokerM').val("");
                        $('#selectProtocolModelM').val("");
                        $('#selectFormatModelM').val("");
                        $('#inputProducerModelM').val("");
                        $('#inputFrequencyModelM').val("");
                        //$('#inputPolicyModelM').val("");
                        $('#selectKGeneratorModelM').val("");
                        $('#selectEdgeGatewayTypeM').val("");
                        $('#editlistAttributes').html("");
                        $("#editModalModal").modal('hide');
                    }
                });
            }
        });
    });
    //$('#editModelConfirmBtn').off("click");
    $("#editModelConfirmBtn").off("click").click(function () {


        //MARCO: in case of model I do not have to distinguish between updated attributes and new inserted 
        // attributes. I can use a single variable for keeping trace of both of them
        //mynewAttributes = [];
        myAttributes = [];
        msg_whatiswrong = '';
        var regex = /[^a-z0-9_-]/gi;
        var someNameisWrong = false;
        AttrName = [];
        num1 = document.getElementById('addlistAttributesM').childElementCount;
        for (var m = 0; m < num1; m++)
        {
            AttrName.push(document.getElementById('addlistAttributesM').childNodes[m].childNodes[0].childNodes[0].childNodes[0].value.trim());
            //var selOpt= document.getElementById('addlistAttributesM').childNodes[m].childNodes[2].childNodes[0].childNodes[0].options;
            //var selIndex= document.getElementById('addlistAttributesM').childNodes[m].childNodes[2].childNodes[0].childNodes[0].selectedIndex;
            var newatt = {value_name: document.getElementById('addlistAttributesM').childNodes[m].childNodes[0].childNodes[0].childNodes[0].value.trim(),
                data_type: document.getElementById('addlistAttributesM').childNodes[m].childNodes[3].childNodes[0].childNodes[0].value.trim(),
                value_type: document.getElementById('addlistAttributesM').childNodes[m].childNodes[1].childNodes[0].childNodes[0].value.trim(),
                editable: '0',
                value_unit: document.getElementById('addlistAttributesM').childNodes[m].childNodes[2].childNodes[0].childNodes[0].value.trim(),
                healthiness_criteria: document.getElementById('addlistAttributesM').childNodes[m].childNodes[5].childNodes[0].childNodes[0].value.trim(),
                healthiness_value: document.getElementById('addlistAttributesM').childNodes[m].childNodes[6].childNodes[0].childNodes[0].value.trim(),
                real_time_flag: document.getElementById('addlistAttributesM').childNodes[m].childNodes[7].childNodes[0].childNodes[0].checked.toString()};
            //MARCO: mynewAttributes.push(newatt);

            if (newatt.value_name != "" && !regex.test(newatt.value_name) && newatt.value_name.length >= 2 && newatt.data_type != "" && newatt.value_type != "" && newatt.editable != "" && newatt.value_unit != "" && newatt.healthiness_criteria != "" && newatt.healthiness_value != "")
                myAttributes.push(newatt);
            else
                someNameisWrong = true;
        }

        //MARCO  myAttributes= [];
        num = document.getElementById('editlistAttributes').childElementCount;
        for (var j = 0; j < num; j++)
        {
            var selectOpt_value_type = document.getElementById('editlistAttributes').childNodes[j].childNodes[1].childNodes[0].childNodes[0].options;
            var selectIndex_value_type = document.getElementById('editlistAttributes').childNodes[j].childNodes[1].childNodes[0].childNodes[0].selectedIndex;
            var selectOpt_data_type = document.getElementById('editlistAttributes').childNodes[j].childNodes[3].childNodes[0].childNodes[0].options;
            var selectIndex_data_type = document.getElementById('editlistAttributes').childNodes[j].childNodes[3].childNodes[0].childNodes[0].selectedIndex;
            var selectOpt_value_unit = document.getElementById('editlistAttributes').childNodes[j].childNodes[2].childNodes[0].childNodes[0].options;
            var selectIndex_value_unit = document.getElementById('editlistAttributes').childNodes[j].childNodes[2].childNodes[0].childNodes[0].selectedIndex;
            var selectOpt_hc = document.getElementById('editlistAttributes').childNodes[j].childNodes[5].childNodes[0].childNodes[0].options;
            var selectIndex_hc = document.getElementById('editlistAttributes').childNodes[j].childNodes[5].childNodes[0].childNodes[0].selectedIndex;
//            var selectOpt_edit = document.getElementById('editlistAttributes').childNodes[j].childNodes[4].childNodes[0].childNodes[0].options;
//            var selectIndex_edit = document.getElementById('editlistAttributes').childNodes[j].childNodes[4].childNodes[0].childNodes[0].selectedIndex;

            AttrName.push(document.getElementById('editlistAttributes').childNodes[j].childNodes[0].childNodes[0].childNodes[0].value.trim());
            var att = {value_name: document.getElementById('editlistAttributes').childNodes[j].childNodes[0].childNodes[0].childNodes[0].value.trim(),
                data_type: selectOpt_data_type[selectIndex_data_type].value,
                value_type: selectOpt_value_type[selectIndex_value_type].value,
                editable: '0',
                value_unit: selectOpt_value_unit[selectIndex_value_unit].value,
                healthiness_criteria: selectOpt_hc[selectIndex_hc].value,
                healthiness_value: document.getElementById('editlistAttributes').childNodes[j].childNodes[6].childNodes[0].childNodes[0].value.trim(),
                real_time_flag: document.getElementById('editlistAttributes').childNodes[j].childNodes[7].childNodes[0].childNodes[0].checked.toString()};
            if (att.value_name != "" && !regex.test(att.value_name) && att.value_name.length >= 2 && att.data_type != "" && att.value_type != "" && att.editable != "" && att.value_unit != "" && att.healthiness_criteria != "" && att.healthiness_value != "")
                myAttributes.push(att);
            else {
                someNameisWrong = true;
                msg_whatiswrong += "The value name must be valid. All the field are been filled ";
            }

        }

        //MARCO: in case of model, there is no need to consider the deleted attributes
        // I have not to remove from a table. 
        /* mydeletedAttributes= [];
         numDel= document.getElementById('deletedAttributes').childElementCount;
         for (var j=0; j< numDel; j++)
         {
         var selectOpt_value_type= document.getElementById('deletedAttributes').childNodes[j].childNodes[2].childNodes[0].childNodes[0].options;
         var selectIndex_value_type= document.getElementById('deletedAttributes').childNodes[j].childNodes[2].childNodes[0].childNodes[0].selectedIndex;
         
         var selectOpt_data_type= document.getElementById('deletedAttributes').childNodes[j].childNodes[1].childNodes[0].childNodes[0].options;
         var selectIndex_data_type= document.getElementById('deletedAttributes').childNodes[j].childNodes[1].childNodes[0].childNodes[0].selectedIndex;
         
         var selectOpt_value_unit= document.getElementById('deletedAttributes').childNodes[j].childNodes[3].childNodes[0].childNodes[0].options;
         var selectIndex_value_unit= document.getElementById('deletedAttributes').childNodes[j].childNodes[3].childNodes[0].childNodes[0].selectedIndex;
         
         var selectOpt_hc= document.getElementById('deletedAttributes').childNodes[j].childNodes[5].childNodes[0].childNodes[0].options;
         var selectIndex_hc= document.getElementById('deletedAttributes').childNodes[j].childNodes[5].childNodes[0].childNodes[0].selectedIndex;
         
         var selectOpt_edit= document.getElementById('deletedAttributes').childNodes[j].childNodes[4].childNodes[0].childNodes[0].options;
         var selectIndex_edit= document.getElementById('deletedAttributes').childNodes[j].childNodes[4].childNodes[0].childNodes[0].selectedIndex;
         
         var att= {value_name: document.getElementById('deletedAttributes').childNodes[j].childNodes[0].childNodes[0].childNodes[0].value.trim(), 
         data_type:selectOpt_data_type[selectIndex_data_type].value,
         value_type:selectOpt_value_type[selectIndex_value_type].value,
         editable:selectOpt_edit[selectIndex_edit].value,
         value_unit:selectOpt_value_unit[selectIndex_value_unit].value,
         healthiness_criteria: selectOpt_hc[selectIndex_hc].value,
         healthiness_value: document.getElementById('deletedAttributes').childNodes[j].childNodes[6].childNodes[0].childNodes[0].value.trim()};
         mydeletedAttributes.push(att);
         }
         */

        if (checkIfDuplicateExists(AttrName)) {
            someNameisWrong = true;
            msg_whatiswrong += "The value name must be unique. ";
        }

        if ($('#selectKindModelM').val() == '') {
            someNameisWrong = true;
            msg_whatiswrong += "The kind must be specificated. ";
        }

        if (!someNameisWrong) {
            document.getElementById('editlistAttributes').innerHTML = "";
            document.getElementById('addlistAttributesM').innerHTML = "";
            document.getElementById('deletedAttributes').innerHTML = "";
            $("#editModelModalTabs").hide();
            $('#editModelModal div.modalCell').hide();
            //$("#editModelModalFooter").hide();
            $("#addAttrMBtn").hide();
            $('#editModelLoadingMsg').show();
            $('#editModelLoadingIcon').show();
            $("#editModelCancelBtn").hide();
            $("#editModelConfirmBtn").hide();
            $("#editModelModalBody").hide();
            var service = $('#editSelectService').val();
            var servicePath = $('#editInputServicePathModel').val();
            if ($('#selectProtocolModelM').val() === "ngsi w/MultiService") {
                // servicePath value pre-processing
                if (servicePath[0] !== "/" || servicePath === "")
                    servicePath = "/" + servicePath;
                if (servicePath[servicePath.length - 1] === "/" && servicePath.length > 1)
                    servicePath = servicePath.substr(0, servicePath.length - 1);
            }


            if (JSON.stringify(myAttributes) == "[]") {
                $.ajax({
                    url: "../api/model.php",
                    data: {
                        action: "get_value_attributes",
                        id: $('#inputIdModelM').val(),
                        token: sessionToken
                    },
                    type: "POST",
                    async: false,
                    dataType: 'json',
                    success: function (mydata)
                    {

                        myAttributes = JSON.parse(mydata.content.attributes);
                        console.log(myAttributes);
                    }});
            }



            $.ajax({
                url: "../api/model.php",
                data: {
                    action: "update",
                    attributes: JSON.stringify(myAttributes),
                    id: $('#inputIdModelM').val(),
                    name: $('#inputNameModelM').val(),
                    description: $('#inputDescriptionModelM').val(),
                    type: $('#inputTypeModelM').val(),
                    kind: $('#selectKindModelM').val(),
                    producer: $('#inputProducerModelM').val(),
                    frequency: $('#inputFrequencyModelM').val(),
                    kgenerator: $('#selectKGeneratorModelM').val(),
                    edgegateway_type: $('#selectEdgeGatewayTypeM').val(),
                    contextbroker: $('#selectContextBrokerM').val(),
                    protocol: $('#selectProtocolModelM').val(),
                    format: $('#selectFormatModelM').val(),
                    hc: $('#selectHCModelM').val(),
                    hv: $('#inputHVModelM').val(),
                    subnature: $('#selectSubnatureM').val(),
                    static_attributes: JSON.stringify(retrieveStaticAttributes("editlistStaticAttributes", false, "isMobileTickM", "isCertifiedTickM")),
                    service: service,
                    servicePath: servicePath,
                    hlt: $('#selectHLTM').val(),
                    token: sessionToken
                },
                type: "POST",
                async: true,
                success: function (data)
                {
                    if (data["status"] === 'ko')
                    {
                        console.log("Error editing Model type");
                        console.log(data);
                        ////////
                        $('#editModelLoadingMsg').hide();
                        $('#editModelLoadingIcon').hide();
                        $('#editModelOkMsg').hide();
                        $('#editModelOkIcon').hide();
                        $('#editModelKoMsg').show();
                        $('#editModelKoIcon').show();
                        $('#editModelOkBtn').show();
                        ///////////

                    } else if (data["status"] === 'ok')
                    {


                        //$("#editModelModalInnerDiv1").html('Model &nbsp; successfully Updated');
                        //$("#editModelModalInnerDiv2").html('<i class="fa fa-check" style="font-size:42px"></i>');

                        $('#editModelLoadingMsg').hide();
                        $('#editModelLoadingIcon').hide();
                        $('#editModelOkMsg').show();
                        $('#editModelOkIcon').show();
                        $('#editModelKoMsg').hide();
                        $('#editModelKoIcon').hide();
                        $('#editModelOkBtn').show();
                    } else {
                        console.log(data);
                    }

                    $('#modelTable').DataTable().destroy();
                    fetch_data(true);
                },
                error: function (data)
                {
                    console.log("Ko result: " + JSON.stringify(data));
                    $('#editModelLoadingMsg').hide();
                    $('#editModelLoadingIcon').hide();
                    $('#editModelOkMsg').hide();
                    $('#editModelOkIcon').hide();
                    $('#editModelKoMsg').show();
                    $('#editModelKoIcon').show();
                    $('#editModelOkBtn').show();
                    $('#inputNameModelM').val("");
                    $('#inputDescriptionModelM').val("");
                    $('#inputTypeModelM').val("");
                    $('#selectKindModelM').val("");
                    //$('#selectActiveModel').val("");
                    $('#selectHCModelM').val("");
                    $('#inputHVModelM').val("");
                    $('#selectContextBrokerM').val("");
                    $('#selectProtocolModelM').val("");
                    $('#selectFormatModelM').val("");
                    $('#inputProducerModelM').val("");
                    $('#inputFrequencyModelM').val("");
                    //$('#inputPolicyModel').val("");
                    $('#selectKGeneratorModelM').val("");
                    $('#editlistAttributes').html("");
                    $('#selectSubnatureM').val("");
                    $('#modelTable').DataTable().destroy();
                    fetch_data(true);
                }
            });
        } else {
            alert("Check the values of your device, make sure that data you entered are valid" + msg_whatiswrong);
        }
    });
//END EDIT MODEL
// START SAVE AS FUNCTION

    $("#saveAsModelBtn").off("click");
    $('#saveAsModelBtn').click(function () {

        $('#editModelModal').modal('hide');
        $('#saveAsModelNameChange').modal('show');
        $("#saveAsModelModalLabel").html("Save as new model ");
        $('#editModelModalTabs li > a[href="#editSchemaTabModel"]').click();

    });

    $("#saveAsModelCancelBtn").off("click");
    $('#saveAsModelCancelBtn').click(function () {
        $('#saveAsModelNameChange').hide();
        $('#editModelModal').modal('hide');

    });

    //on confirm click on the Save as dialog box, recover data written in all the inputs emulating
    //an "add new device" action behind the scenes
    $("#saveAsModelConfirmBtn").off("click");
    $('#saveAsModelConfirmBtn').click(function () {

        $('#inputNameModel').val($('#saveAsNameModel').val());
        $('#selectContextBroker').val($('#selectContextBrokerM').val());
        $('#inputDescriptionModel').val($('#inputDescriptionModelM').val());
        $('#inputTypeModel').val($('#inputTypeModelM').val());
        $('#selectKindModel').val($('#selectKindModelM').val());
        $('#inputProducerModel').val($('#inputProducerModelM').val());
        $('#inputFrequencyModel').val($('#inputFrequencyModelM').val());
        $('#selectKGeneratorModel').val($('#selectKGeneratorModelM').val());
        $('#selectEdgeGatewayType').val($('#selectEdgeGatewayTypeM').val());
        $('#selectProtocolModel').val($('#selectProtocolModelM').val());
        $('#selectFormatModel').val($('#selectFormatModelM').val());
        $('#selectHCModel').val($('#selectHCModelM').val());
        $('#inputHVModel').val($('#inputHVModelM').val());
        $('#selectSubnature').val($('#selectSubnatureM').val());
        $('#selectService').val($('#editSelectService').val());
        $('#inputServicePathModel').val($('#editInputServicePathModel').val());

        //check if the model has "device in mobility" and "certified" checkboxes checked, same with the selection box for
        //"subnatures"
        // if($('#editStaticTabModel #isCertifiedTickM').is(":checked")){
        //
        //     $('#addStaticTabModel #isCertifiedTick').prop('checked', true);
        //     $('#addStaticTabModel #isCertifiedTick').trigger('change');
        //
        // }
        if($('#editStaticTabModel #isMobileTickM').is(":checked")){

            $('#addStaticTabModel #isMobileTick').prop('checked', true);
            $('#addStaticTabModel #isMobileTick').trigger('change');

        }

        const selectedSubnature= $('#selectSubnatureM option:selected').val();
        $('#addStaticTabModel #selectSubnature').val(selectedSubnature).trigger('change');

        // cycle through Attributes to count them, then count deleted attributes and added attributes by the user on the edit page before
        //clicking the "Save As" button
        var attributesCount=$('#editSchemaTabModel #editlistAttributes').find('.row').length;
        var addedAttrCount=$('#editSchemaTabModel #addlistAttributesM').find('.row').length;
        var deletedAttrCount=$('#editSchemaTabModel #deletedAttributes').find('.row').length;

         //Click "Add value" button a number of times equal to total attribute in edit minus the deleted one plus the added one
        var i=0;
         while (i < attributesCount - deletedAttrCount + addedAttrCount){
              $('#addSchemaTabModel #addAttrBtn').click();
              i++;
          }
          var j=0;

         // creates 6 arrays one for each parameter of an attribute, specifically: Value Name,value type,value unit,data type,Healthiness Criteria,Healthiness value.
        //For each attribute, the 6 parameter for each attribute are on the same index (EX: first attribute has its parameters at index 0 of each array)
         const AttributesArray= []
        $("#editlistAttributes").find("[id^=\"InputVNM\"]").each(function() {
            AttributesArray.push($(this).val());
        });

         const ValueUnitArray=[]
        $("#editlistAttributes").find("[id^=\"value_unit\"]").each(function() {
            ValueUnitArray.push($(this).val());
        });


        const ValueTypeArray=[]
        $("#editlistAttributes").find("[id^=\"value_type\"]").each(function() {
            ValueTypeArray.push($(this).val());
        });


        const DataTypeArray=[]
        $("#editlistAttributes").find("[id^=\"data_type\"]").each(function() {
            DataTypeArray.push($(this).val());
        });


         const HCArray = []
        $("#editlistAttributes").find("#SelectHC").each(function() {
            HCArray.push($(this).val());
        });

         const HVArray = []
        $("#editlistAttributes").find("#InputHV").each(function() {
            HVArray.push($(this).val());
        });
        const RealTimeFlagArray = []
        $("#editlistAttributes").find("[id^=\"realtime_flag\"]").each(function() {
            RealTimeFlagArray.push($(this).prop('checked'));
        });
        //console.log(RealTimeFlagArray)
        //For each deleted attribute, search the name in the attributes array created before, get the index, and delete that index in all 6 arrays
        //Now we have the attributes minus the deleted ones
        $("#deletedAttributes").find("[id^=\"InputVNM\"]").each(function() {
            const deletedIndex = findStringIndex(AttributesArray, $(this).val);
            AttributesArray.splice(deletedIndex,1);
            ValueUnitArray.splice(deletedIndex,1);
            ValueTypeArray.splice(deletedIndex,1);
            DataTypeArray.splice(deletedIndex,1);
            HCArray.splice(deletedIndex,1);
            HVArray.splice(deletedIndex,1);
            RealTimeFlagArray.splice(deletedIndex,1);
        });

        function findStringIndex(arr, searchString) {
            return arr.indexOf(searchString);
        }

        //For each attribute, add his parameters to the end of the arrays created before,now we have 6 arrays with the included
        //addition or deletions made by the user on the edit page
        $("#addlistAttributesM").find("[id^=\"InputVNM\"]").each(function() {
            AttributesArray.push($(this).val());
        });

        $("#addlistAttributesM").find("[id^=\"value_unit\"]").each(function() {
            ValueUnitArray.push($(this).val());
        });

        $("#addlistAttributesM").find("[id^=\"value_type\"]").each(function() {
            ValueTypeArray.push($(this).val());
        });

        $("#addlistAttributesM").find("[id^=\"data_type\"]").each(function() {
            DataTypeArray.push($(this).val());
        });

        $("#addlistAttributesM").find("#SelectHC").each(function() {
            HCArray.push($(this).val());
        });

        $("#addlistAttributesM").find("#InputHV").each(function() {
            HVArray.push($(this).val());
        });
        $("#addlistAttributesM").find("[id^=\"realtime_flag\"]").each(function() {
            RealTimeFlagArray.push($(this).prop('checked'));
        });


        //Populate the attributes field using the values in the 6 arrays(EX: first attributes will have all the parameters saved at index 0 of each array)
        mynewAttributesSaveas = []
         $('#addlistAttributes').children('.row').each(function (){
             $("#InputVNM"+j,this).val(AttributesArray[j]);
             $("#value_type"+ j +" option[value="+ValueTypeArray[j]+"]").attr('selected', 'selected').change();
             $("#value_unit"+ j +" option[value="+ValueUnitArray[j]+"]").attr('selected', 'selected').change();
             $("#data_type"+ j +" option[value="+DataTypeArray[j]+"]").attr('selected', 'selected').change();
             $("#SelectHC option[value="+ HCArray[j] +"]").attr('selected', 'selected').change();
             $('#InputHV',this).val(HVArray[j]);


             var newattsaveas = {value_name: $("#InputVNM"+j).val(),
                value_type: $('#value_type'+j).val(),
                 data_type:$('#data_type'+j).val(),
                 editable: '0',
                 value_unit: $('#value_unit'+j).val(),
                 healthiness_criteria: $('#SelectHC',this).val(),
                 healthiness_value: $('#InputHV',this).val(),
                 real_time_flag: RealTimeFlagArray[j].toString()
             };
             j++;
             mynewAttributesSaveas.push(newattsaveas)
           })

        //call to add a new model
        $.ajax({
            url: "../api/model.php",
            data: {
                action: "insert",
                attributes: JSON.stringify(mynewAttributesSaveas),
                name: $('#inputNameModel').val(),
                description: $('#inputDescriptionModel').val(),
                type: $('#inputTypeModel').val(),
                kind: $('#selectKindModel').val(),
                producer: $('#inputProducerModel').val(),
                frequency: $('#inputFrequencyModel').val(),
                kgenerator: $('#selectKGeneratorModel').val(),
                edgegateway_type: $('#selectEdgeGatewayType').val(),
                contextbroker: $('#selectContextBroker').val(),
                protocol: $('#selectProtocolModel').val(),
                format: $('#selectFormatModel').val(),
                hc: $('#selectHCModel').val(),
                hv: $('#inputHVModel').val(),
                subnature: $('#selectSubnature').val(),
                static_attributes: JSON.stringify(retrieveStaticAttributes("editlistStaticAttributes", false, "isMobileTick"/*,"isCertifiedTick"*/)),
                service: $('#selectService').val(),
                servicePath: $('#inputServicePathModel').val(),
                token: sessionToken
            },
            type: "POST",
            async: true,
            dataType: "JSON",
            timeout: 0,
            success: function (mydata) {
                if (mydata["status"] === 'ko') {
                    //Error if the new name is duplicated
                    console.log("Error adding Model");
                    $('#saveAsModelNameChange').modal('hide');
                    $('#saveAsModelResult').modal('show');
                    $('#saveasfailedmessage').removeAttr('hidden');

                } else if (mydata["status"] === 'ok') {
                    //success message
                    console.log("Added Model");
                    $('#saveAsModelNameChange').modal('hide');
                    $('#saveAsModelResult').modal('show');
                    $('#saveassuccessmessage').removeAttr('hidden');

                }
            },
            error: function (mydata) {
                //Generic error, print the response from the ajax query
                console.log("Error insert model");
                console.log("Error status -- Ko result: " + JSON.stringify(mydata));
                console.log(mydata.responseText);
                $('#saveaserrormessage').html(mydata.responseText);
                $('#saveAsModelNameChange').modal('hide');
                $('#saveAsModelResult').modal('show');
                $('#saveaserrormessage').removeAttr('hidden');

            }
        });
});
//END SAVE AS

    $("#editModelCancelBtn").off("click");
    $("#editModelCancelBtn").on('click', function () {

        document.getElementById('editlistAttributes').innerHTML = "";
        document.getElementById('addlistAttributesM').innerHTML = "";
        document.getElementById('deletedAttributes').innerHTML = "";
    });
    $("#addNewModelCancelBtn").off("click");
    $("#addNewModelCancelBtn").on('click', function () {

        $('#addModelModal').modal('hide');
        $('#inputNameModel').val("");
        $('#inputDescriptionModel').val("");
        $('#inputTypeModel').val("");
        $('#selectKindModel').val("");
        //$('#selectActiveModel').val("");
        $('#selectHCModel').val("");
        $('#inputHVModel').val("");
        $('#selectContextBroker').val("");
        $('#selectProtocolModel').val("");
        $('#selectFormatModel').val("");
        $('#inputProducerModel').val("");
        $('#inputFrequencyModel').val("");
        //$('#inputPolicyModel').val("");
        $('#selectKGeneratorModel').val("");
        $('#addlistAttributes').html("");
        $('#selectSubnature').val("");
        $('#selectSubnature').trigger("change");
        $("#addNewStaticBtn").hide();
        removeStaticAttributes();
        location.reload();
    });
    $("#addModelKoBackBtn").off("click");
    $("#addModelKoBackBtn").on('click', function () {
        $("#addModelKoModal").modal('hide');
        $("#addModelModal").modal('show');
    });
    $("#addModelKoConfirmBtn").off("click");
    $("#addModelKoConfirmBtn").on('click', function () {
        $("#addModelKoModal").modal('hide');
        $("#addModelForm").trigger("reset");
    });
    $("#editModelKoBackBtn").off("click");
    $("#editModelKoBackBtn").on('click', function () {
        $("#editModelKoModal").modal('hide');
        $("#editModelModal").modal('show');
    });
    $("#editModelKoConfirmBtn").off("click");
    $("#editModelKoConfirmBtn").on('click', function () {
        $("#editModelKoModal").modal('hide');
        $("#editModelForm").trigger("reset");
    });
    $("#selectProtocolModel").change(function () {
        checkModelSelectionCB_all();
        checkAddModelConditions();
    });
    $("#selectFormatModel").change(function () {
        checkModelSelectionCB_all();
        checkAddModelConditions();
    });
    $("#selectContextBroker").change(function () {

        var index = document.getElementById("selectContextBroker").selectedIndex;
        var opt = document.getElementById("selectContextBroker").options;
        var valCB = opt[index].getAttribute("my_data");
        var valOrg = opt[index].getAttribute("data_org");
        if (valCB === 'ngsi')
        {
            document.getElementById("selectProtocolModel").value = 'ngsi';
            document.getElementById("selectFormatModel").value = 'json';
        } else if (valCB === 'ngsi w/MultiService')
        {
            document.getElementById("selectProtocolModel").value = 'ngsi w/MultiService';
            document.getElementById("selectFormatModel").value = 'json';
        } else if (valCB === 'mqtt')
        {
            document.getElementById("selectProtocolModel").value = 'mqtt';
            document.getElementById("selectFormatModel").value = 'csv';
        } else if (valCB === 'amqp')
        {
            document.getElementById("selectProtocolModel").value = 'amqp';
            document.getElementById("selectFormatModel").value = 'csv';
        } else
        {
            //alert("This is a new contextBroker");
            console.log("an error occurred");
        }

        checkModelSelectionCB_all();
        checkAddModelConditions();
        if (valOrg != null)
            $("#selectContextBrokerMsg").html($("#selectContextBrokerMsg").html() + " - Organization:" + valOrg);
    });
    $("#selectContextBrokerM").change(function () {

        var index = document.getElementById("selectContextBrokerM").selectedIndex;
        var opt = document.getElementById("selectContextBrokerM").options;
        var valCB = opt[index].getAttribute("my_data");
        var valOrg = opt[index].getAttribute("data_org");
        if (valCB === 'ngsi')
        {
            document.getElementById("selectProtocolModelM").value = 'ngsi';
            document.getElementById("selectFormatModelM").value = 'json';
        } else if (valCB === 'ngsi w/MultiService')
        {
            document.getElementById("selectProtocolModelM").value = 'ngsi w/MultiService';
            document.getElementById("selectFormatModelM").value = 'json';
        } else if (valCB === 'mqtt')
        {
            document.getElementById("selectProtocolModelM").value = 'mqtt';
            document.getElementById("selectFormatModelM").value = 'csv';
        } else if (valCB === 'amqp')
        {
            document.getElementById("selectProtocolModelM").value = 'amqp';
            document.getElementById("selectFormatModelM").value = 'csv';
        } else
        {
            //alert("This is a new contextBroker");
            console.log("an error occurred");
        }

        checkModelSelectionCBM_all();
        checkEditModelConditions();
        if (valOrg != null)
            $("#selectContextBrokerMsgM").html($("#selectContextBrokerMsgM").html() + " - Organization:" + valOrg);
    });
    $('#modelTable thead').css("background", "rgba(0, 162, 211, 1)");
    $('#modelTable thead').css("color", "white");
    $('#modelTable thead').css("font-size", "1em");
    //Validation of the name of the new owner during typing
    $('#newOwner').on('input', function (e)
    {

        if ($(this).val().trim() === '')
        {
            $('#newOwnerMsg').css('color', '#f3cf58');
            $('#newOwnerMsg').html('New owner username can\'t be empty');
            $('#newOwnershipConfirmBtn').addClass('disabled');
        } else
        {
            //if(($(this).val().trim() === "<?= $_SESSION['loggedUsername'] ?>")&&("<?= $_SESSION['loggedRole'] ?>" !== "RootAdmin"))
            if (($(this).val().trim() === loggedUser) && (loggedRole !== "RootAdmin") && (loggedRole !== "ToolAdmin"))

            {
                $('#newOwnerMsg').css('color', '#f3cf58');
                $('#newOwnerMsg').html('New owner can\'t be you');
                $('#newOwnershipConfirmBtn').addClass('disabled');
            } else
            {
                $('#newOwnerMsg').css('color', 'white');
                $('#newOwnerMsg').html('User can be new owner');
                $('#newOwnershipConfirmBtn').removeClass('disabled');
            }
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
                        console.log("adding empty");
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
//--------------------- static attribute ADD start

    $("#addNewStaticBtn").off("click");
    $("#addNewStaticBtn").click(function () {
        var row = createRowElem('', '', currentDictionaryStaticAttribAdd, "addlistStaticAttributes");
    });
//--------------------- static attribute ADD end		
//--------------------- static attribute EDIT start

    $("#addNewStaticBtnM").off("click");
    $("#addNewStaticBtnM").click(function () {
        var row = createRowElem('', '', currentDictionaryStaticAttribEdit, "editlistStaticAttributes");
    });
//--------------------- static attribute EDIT end


    $('#selectSubnature').on('select2:selecting', function (e) {
        checkSubnatureChanged($('#selectSubnature'), e.target.value, e.params.args.data.id, e);
    });
    $('#selectSubnatureM').on('select2:selecting', function (e) {
        checkSubnatureChanged($('#selectSubnatureM'), e.target.value, e.params.args.data.id, e, true);
    });
    $('#selectSubnature').on('select2:clearing', function (e) {
        checkSubnatureChanged($('#selectSubnature'), e.params.args.data.id, "", e);
    });
    $('#selectSubnatureM').on('select2:clearing', function (e) {
        checkSubnatureChanged($('#selectSubnatureM'), e.params.args.data.id, "", e, true);
    });
}); // end of ready-state






//   START TO CHANGE THE VISIBILITY  & OWNERSHIP 

function changeVisibility(name, visibility, obj_organization, accesslink) {
    $("#delegationsModal").modal('show');
    $("#delegationHeadModalLabel").html("Model - " + name);
    if (visibility == 'MyOwnPrivate') {
        newVisibility = 'public';
        $('#visID').css('color', '#f3cf58');
        $("#visID").html("Visibility - Private");
        document.getElementById('newVisibilityPrivateBtn').style.visibility = 'hidden';
        document.getElementById('newVisibilityPublicBtn').style.visibility = 'show';
    } else

    {
        newVisibility = 'private';
        $('#visID').css('color', '#f3cf58');
        $("#visID").html("Visibility - Public");
        document.getElementById('newVisibilityPrivateBtn').style.visibility = 'show';
        document.getElementById('newVisibilityPublicBtn').style.visibility = 'hidden';
    }

    $(document).on("click", "#newVisibilityPublicBtn", function (event) {
        $.ajax({
            url: "../api/contextbroker.php",
            data:
                    {
                        action: "change_visibility",
                        username: loggedUser,
                        object: "ModelID", //IOTModel
                        table: "model",
                        organization: organization,
                        obj_organization: obj_organization,
                        name: name,
                        accesslink: accesslink,
                        visibility: newVisibility,
                        token: sessionToken
                    },
            type: "POST",
            async: true,
            dataType: 'json',
            success: function (data)
            {
                if (data["status"] === 'ok')
                {
                    $('#newVisibilityResultMsg').show();
                    $("#visID").html("");
                    $('#visID').css('color', '#f3cf58');
                    $("#visID").html("Visibility - Private");
                    $('#newVisibilityResultMsg').html('New visibility set to Public');
                    $('#newVisibilityPublicBtn').addClass('disabled');
                    setTimeout(function ()
                    {
                        $('#devicesTable').DataTable().destroy();
                        fetch_data(true);
                        location.reload();
                    }, 3000);
                } else if (data["status"] === 'ko')
                {
                    $('#newVisibilityResultMsg').show();
                    $('#newVisibilityResultMsg').html('Error setting new visibility');
                    $('#newVisibilityPublicBtn').addClass('disabled');
                    setTimeout(function ()
                    {
                        $('#newVisibilityPublicBtn').removeClass('disabled');
                        $('#newVisibilityResultMsg').html('');
                        $('#newVisibilityResultMsg').hide();
                    }, 3000);
                } else {
                    console.log(data);
                }
            },
            error: function (errorData)
            {
                $('#newVisibilityResultMsg').show();
                $('#newVisibilityResultMsg').html('Error setting new visibility');
                $('#newVisibilityPublicBtn').addClass('disabled');
                setTimeout(function ()
                {
                    $('#newVisibilityPublicBtn').removeClass('disabled');
                    $('#newVisibilityResultMsg').html('');
                    $('#newVisibilityResultMsg').hide();
                }, 3000);
            }
        });
    });
    $(document).on("click", "#newVisibilityPrivateBtn", function (event) {
        $.ajax({
            url: "../api/contextbroker.php",
            data:
                    {
                        action: "change_visibility",
                        username: loggedUser,
                        object: "ModelID", //IOTModel
                        table: "model",
                        organization: organization,
                        obj_organization: obj_organization,
                        name: name,
                        accesslink: accesslink,
                        visibility: newVisibility,
                        token: sessionToken
                    },
            type: "POST",
            async: true,
            dataType: 'json',
            success: function (data)
            {
                if (data["status"] === 'ok')
                {
                    $('#newVisibilityResultMsg').show();
                    $('#newVisibilityResultMsg').html('New visibility set Private');
                    $('#newVisibilityPrivateBtn').addClass('disabled');
                    setTimeout(function ()
                    {
                        $('#devicesTable').DataTable().destroy();
                        fetch_data(true);
                        location.reload();
                    }, 3000);
                } else if (data["status"] === 'ko')
                {
                    $('#newVisibilityResultMsg').show();
                    $('#newVisibilityResultMsg').html('Error setting new visibility');
                    $('#newVisibilityPrivateBtn').addClass('disabled');
                    setTimeout(function ()
                    {
                        $('#newVisibilityPrivateBtn').removeClass('disabled');
                        $('#newVisibilityResultMsg').html('');
                        $('#newVisibilityResultMsg').hide();
                    }, 3000);
                } else {
                    console.log(data);
                }
            },
            error: function (errorData)
            {
                $('#newVisibilityResultMsg').show();
                $('#newVisibilityResultMsg').html('Error setting new visibility');
                $('#newVisibilityPrivateBtn').addClass('disabled');
                setTimeout(function ()
                {
                    $('#newVisibilityPrivateBtn').removeClass('disabled');
                    $('#newVisibilityResultMsg').html('');
                    $('#newVisibilityResultMsg').hide();
                }, 3000);
            }
        });
    });
    $(document).on("click", "#newOwnershipConfirmBtn", function (event) {
        $.ajax({
            url: "../api/contextbroker.php",
            data: {
                action: "change_owner",
                name: name,
                object: "ModelID", //IOTModel
                table: "model",
                accesslink: accesslink,
                organization: organization,
                obj_organization: obj_organization,
                owner: loggedUser,
                newOwner: $('#newOwner').val(),
                token: sessionToken
            },
            type: "POST",
            async: true,
            dataType: 'json',
            success: function (data)
            {
                if (data["status"] === 'ok')
                {
                    $('#newOwner').val('');
                    $('#newOwner').addClass('disabled');
                    $('#newOwnershipResultMsg').show();
                    $('#newOwnershipResultMsg').html('New ownership set correctly');
                    $('#newOwnershipConfirmBtn').addClass('disabled');
                    setTimeout(function ()
                    {
                        $('#devicesTable').DataTable().destroy();
                        fetch_data(true);
                        location.reload();
                    }, 3000);
                } else if (data["status"] === 'ko')
                {
                    $('#newOwner').addClass('disabled');
                    $('#newOwnershipResultMsg').html('Error setting new ownership: please try again');
                    $('#newOwnershipConfirmBtn').addClass('disabled');
                    setTimeout(function ()
                    {
                        $('#newOwner').removeClass('disabled');
                        $('#newOwnershipResultMsg').html('');
                        $('#newOwnershipResultMsg').hide();
                    }, 3000);
                } else {
                    console.log(data);
                }
            },
            error: function (errorData)
            {
                $('#newOwner').addClass('disabled');
                $('#newOwnershipResultMsg').html('Error setting new ownership: please try again');
                $('#newOwnershipConfirmBtn').addClass('disabled');
                setTimeout(function ()
                {
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
    $.ajax({
        url: "../api/contextbroker.php", //Checking the delegation table
        data:
                {

                    action: "get_delegations", // check the action and to be specified
                    accesslink: accesslink,
                    obj_organization: obj_organization,
                    name: name,
                    object: "ModelID", //IOTModel
                    user: loggedUser,
                    token: sessionToken,
                },
        type: "POST",
        async: true,
        dataType: 'json',
        success: function (data)
        {

            if (data["status"] == 'ok')
            {

                console.log(JSON.stringify(data));
                delegations = data["delegation"];
                $('#delegationsTable tbody').html("");
                $('#delegationsTableGroup tbody').html("");
                for (var i = 0; i < delegations.length; i++)
                {

                    if ((delegations[i].userDelegated != "ANONYMOUS") && (delegations[i].userDelegated != null)) {


                        $('#delegationsTable tbody').append('<tr class="delegationTableRow" data-delegationId="' + delegations[i].delegationId + '" data-delegated="' + delegations[i].userDelegated + '"><td class="delegatedName">' + delegations[i].userDelegated + '</td><td><i class="fa fa-remove removeDelegationBtn"></i></td></tr>');
                    } else if (delegations[i].groupDelegated != null) {

                        //extract cn and ou
                        var startindex = delegations[i].groupDelegated.indexOf("cn=");
                        var endindex_gr = delegations[i].groupDelegated.indexOf(",");
                        var gr = delegations[i].groupDelegated.substring(3, endindex_gr);
                        var endindex_ou = delegations[i].groupDelegated.indexOf(",", endindex_gr + 1);
                        var ou = delegations[i].groupDelegated.substring(endindex_gr + 4, endindex_ou);
                        var DN = "";
                        if (startindex != -1) {
                            DN = ou + "," + gr;
                        } else {
                            DN = gr;
                        }


                        $('#delegationsTableGroup tbody').append('<tr class="delegationTableRowGroup" data-delegationId="' + delegations[i].delegationId + '" data-delegated="' + ou + "," + gr + '"><td class="delegatedName">' + DN + '</td><td><i class="fa fa-remove removeDelegationBtnGroup"></i></td></tr>');
                    }

                }
                $('#delegationsTable tbody').on("click", "i.removeDelegationBtn", function () {
                    var rowToRemove = $(this).parents('tr');
                    $.ajax({
                        url: "../api/contextbroker.php", //check the url
                        data:
                                {
                                    action: "remove_delegation", // to be specified
                                    token: sessionToken,
                                    user: loggedUser,
                                    delegationId: $(this).parents('tr').attr('data-delegationId')
                                },
                        type: "POST",
                        async: true,
                        dataType: 'json',
                        success: function (data)
                        {
                            if (data["status"] === 'ok')
                            {
                                rowToRemove.remove();
                            } else
                            {
                                console.log("error removing delegation");
                            }
                        },
                        error: function (errorData)
                        {
                            console.log("error in call for removing delegation");
                        }
                    });
                });
                $('#delegationsTableGroup tbody').on("click", "i.removeDelegationBtnGroup", function () {
                    var rowToRemove = $(this).parents('tr');
                    $.ajax({
                        url: "../api/contextbroker.php", //check the url
                        data:
                                {
                                    action: "remove_delegation",
                                    token: sessionToken,
                                    user: loggedUser,
                                    delegationId: $(this).parents('tr').attr('data-delegationId')
                                },
                        type: "POST",
                        async: true,
                        dataType: 'json',
                        success: function (data)
                        {
                            if (data["status"] === 'ok')
                            {
                                rowToRemove.remove();
                            } else
                            {
                                console.log("error removing delegation");
                            }
                        },
                        error: function (errorData)
                        {
                            console.log("error in the call ro remove delegation");
                        }
                    });
                });
            } else
            {
                console.log(json_encode(data));
            }
        },
        error: function (errorData)
        {
            console.log(errorData); //TBD  insert a message of error
        }
    });
    //listen about the confimation
    $(document).on("click", "#newDelegationConfirmBtn", function (event) {
        var newDelegation = document.getElementById('newDelegation').value;
        $.ajax({
            url: "../api/contextbroker.php", //which api to use
            data:
                    {
                        action: "add_delegation",
                        accesslink: accesslink,
                        user: loggedUser,
                        obj_organization: obj_organization,
                        obj_name: name,
                        object: "ModelID", //IOTModel
                        token: sessionToken,
                        organization: organization,
                        delegated_user: newDelegation,
                    },
            type: "POST",
            async: true,
            dataType: 'json',
            success: function (data)
            {
                if (data["status"] === 'ok')
                {
                    $('#delegationsTable tbody').append('<tr class="delegationTableRow" data-delegationId="' + data["delegationId"] + '" data-delegated="' + $('#newDelegation').val() + '"><td class="delegatedName">' + $('#newDelegation').val() + '</td><td><i class="fa fa-remove removeDelegationBtn"></i></td></tr>');
                    $('#newDelegation').val('');
                    $('#newDelegation').addClass('disabled');
                    $('#newDelegatedMsg').css('color', 'white');
                    $('#newDelegatedMsg').html('New delegation added correctly');
                    $('#newDelegationConfirmBtn').addClass('disabled');
                    setTimeout(function ()
                    {
                        $('#newDelegation').removeClass('disabled');
                        $('#newDelegatedMsg').css('color', '#f3cf58');
                        $('#newDelegatedMsg').html('Delegated username can\'t be empty');
                    }, 1500);
                } else
                {
                    var errorMsg = null;
                    $('#newDelegation').val('');
                    $('#newDelegation').addClass('disabled');
                    $('#newDelegatedMsg').css('color', '#f3cf58');
                    $('#newDelegatedMsg').html(data["msg"]);
                    $('#newDelegationConfirmBtn').addClass('disabled');
                    setTimeout(function ()
                    {
                        $('#newDelegation').removeClass('disabled');
                        $('#newDelegatedMsg').css('color', '#f3cf58');
                        $('#newDelegatedMsg').html('Delegated username can\'t be empty');
                    }, 2000);
                }
            },
            error: function (errorData)
            {
                var errorMsg = "Error calling internal API";
                $('#newDelegation').val('');
                $('#newDelegation').addClass('disabled');
                $('#newDelegatedMsg').css('color', '#f3cf58');
                $('#newDelegatedMsg').html(errorMsg);
                $('#newDelegationConfirmBtn').addClass('disabled');
                setTimeout(function ()
                {
                    $('#newDelegation').removeClass('disabled');
                    $('#newDelegatedMsg').css('color', '#f3cf58');
                    $('#newDelegatedMsg').html('Delegated username can\'t be empty');
                }, 2000);
            }
        });
    });
    //group delegation -start------------------------------------------------------------------------------------------------------------
    $(document).on("click", "#newDelegationConfirmBtnGroup", function (event) {

        var delegatedDN = "";
        var e = document.getElementById("newDelegationGroup");
        if ((typeof e.options[e.selectedIndex] !== 'undefined') && (e.options[e.selectedIndex].text !== 'All groups')) {
            delegatedDN = "cn=" + e.options[e.selectedIndex].text + ",";
        }
        var e2 = document.getElementById("newDelegationOrganization");
        delegatedDN = delegatedDN + "ou=" + e2.options[e2.selectedIndex].text;
        $.ajax({
            url: "../api/contextbroker.php",
            data:
                    {
                        action: "add_delegation",
                        accesslink: accesslink,
                        user: loggedUser,
                        token: sessionToken,
                        obj_organization: obj_organization,
                        obj_name: name,
                        object: "ModelID", //IOTModel
                        delegated_group: delegatedDN
                    },
            type: "POST",
            async: true,
            dataType: 'json',
            success: function (data)
            {
                if (data["status"] === 'ok')
                {
                    var toadd = $('#newDelegationOrganization').val();
                    if (document.getElementById("newDelegationGroup").options[e.selectedIndex].text != '') {
                        toadd = toadd + "," + $('#newDelegationGroup').val();
                    }

                    $('#delegationsTableGroup tbody').append('<tr class="delegationTableRowGroup" data-delegationId="' + data["delegationId"] + '" data-delegated="' + toadd + '"><td class="delegatedNameGroup">' + toadd + '</td><td><i class="fa fa-remove removeDelegationBtnGroup"></i></td></tr>');
                    $('#newDelegatedMsgGroup').css('color', 'white');
                    $('#newDelegatedMsgGroup').html('New delegation added correctly');
                    setTimeout(function ()
                    {
                        $('#newDelegatedMsgGroup').css('color', '#f3cf58');
                        $('#newDelegatedMsgGroup').html('Delegated groupname can\'t be empty');
                    }, 1500);
                } else
                {
                    var errorMsg = null;
                    $('#newDelegatedMsgGroup').css('color', '#f3cf58');
                    $('#newDelegatedMsgGroup').html(data["msg"]);
                    setTimeout(function ()
                    {
                        $('#newDelegationGroup').removeClass('disabled');
                        $('#newDelegationOrganization').removeClass('disabled');
                        $('#newDelegatedMsgGroup').css('color', '#f3cf58');
                        $('#newDelegatedMsgGroup').html('Delegated groupname can\'t be empty');
                    }, 2000);
                }
            },
            error: function (errorData)
            {
                var errorMsg = "Error calling internal API";
                $('#newDelegatedMsgGroup').css('color', '#f3cf58');
                $('#newDelegatedMsgGroup').html(errorMsg);
                setTimeout(function ()
                {
                    $('#newDelegatedMsgGroup').css('color', '#f3cf58');
                    $('#newDelegatedMsgGroup').html('Delegated groupname can\'t be empty');
                }, 2000);
            }
        });
    }); //group delegation -end

// END TO CHANGE THE VISIBILITY
}
