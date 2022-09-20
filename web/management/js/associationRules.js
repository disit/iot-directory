$.fn.modal.Constructor.prototype.enforceFocus = function () { };
myFile = null;
tableFirstLoad = true;
fileData = [];
editDeviceConditionsArray = [];
modelsdata = [];
receivedData = [];
var dataPreviewTable = "";
var previewFirstLoad = false;
var previewValuesFirstLoad = false;
var previewRulesFirstLoad = false;

var dataTable = "";
var dataTable2 = "";
requiredHeaders = ["name", "devicetype", "macaddress", "frequency", "kind", "protocol", "format", "producer", "latitude", "longitude", "value_name", "data_type", "value_type", "editable", "value_unit", "healthiness_criteria", "healthiness_value", "k1", "k2", "subnature", "static_attributes"];

var dataTable3 = "";
var _serviceIP = "../stubs";

var indexValues = 0;//it keeps track of unique identirier on the values, so it's possible to enforce specific value type

var gb_datatypes = "";
var gb_value_units = "";
var gb_value_types = "";
var gb_active_brokers_names = [];
var defaultPolicyValue = [];
var devicenamesArray = {'if': 0, 'then': 0};
var valueNamesArray = {'if': 0, 'then': 0};
var gb_rulesCounter = 0;
var gb_association_rules = [];
var gb_first_opening = 0;

devicenamesArray['if'] = 0;
devicenamesArray['then'] = 0;
valueNamesArray['if'] = 0;
valueNamesArray['then'] = 0;
var indexHealthinessIf = [];
var idCounterThen = 0;
var idCounterIf = 0;

var ifPages = [];

var gb_options = [];

var gb_device = "";
var gb_latitude = "";
var gb_longitude = "";
var gb_key1;
var gb_key2;

var gb_old_id = "";
var gb_old_cb = "";

var dataTable = "";
var dataTable2 = "";

var extractionRules = "";
// var list_temporary_event_value = "";

var timerID = undefined;
var was_processing = 0;

function getText(obj) {
    return obj.textContent ? obj.textContent : obj.innerText;
}

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
    return request
}
//--------to get the models with their details----------------------//
$.ajax({
    url: "../api/model.php",
    data: {
        action: "get_all_models",
        token: sessionToken
    },
    type: "POST",
    async: true,
    datatype: 'json',
    success: function (data) {
        modelsdata = data["content"];
        addModel($("#selectModelLD"), data);
    },
    error: function (mydata) {
        console.log(JSON.stringify(mydata));
        alert("Network errors. <br/> Get in touch with the Snap4City Administrator<br/>" + JSON.stringify(mydata));
    }
});

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
        gb_datatypes = mydata["data_type"];
        gb_value_units = mydata["value_unit"];
        gb_value_types = mydata["value_type"];
        addSubnature($("#selectSubnatureM"), mydata["subnature"]);
        // console.log(gb_datatypes)
        // console.log(gb_value_units)
        // console.log(gb_value_types)
    },
    error: function (mydata) {
        console.log(JSON.stringify(mydata));
        alert("Network errors. <br/> Get in touch with the Snap4City Administrator<br/>" + JSON.stringify(mydata));
    }
});




////LOAD RULES////

var Regole = [];
function LoadRules() {
    $.ajax({
        url: "../api/bulkDeviceLoad.php",
        data: {
            action: "get_rules_ext",
            token: sessionToken,
             start:0,
                    length: -1
        },
        type: "POST",
        async: true,
        datatype: 'json',
        success: function (myRules) {

            for (let i = 0; i < myRules.data.length; i++) {
                Regole.push(myRules.data[i]["Name"]);

            }

            $('#selectRule').on({
                click: function () {
                    var Rule = this.value;
                    for (let i = 0; i < myRules.data.length; i++) {

                        if (myRules.data[i]["Name"] == Rule) {

                            RulesLoad(myRules.data[i]["Name"], myRules.data[i]["If_statement"], myRules.data[i]["Then_statement"], myRules.data[i]["mode"], 'ifBlockTableValue', 'decisionBlockTableValue');
                           let If_st = JSON.parse(myRules.data[i]["If_statement"].replaceAll('/"', ''));
                            getAffectedRowsValue(If_st);

                            break

                        }
                    }



                }
            }, );



        },
        error: function (mydata) {
            console.log(JSON.stringify(mydata));

        }
    });
}


////LOAD RULES END////   




//--------to get the list of context broker----------

function External_Organizzation(item) {
    //check for external+visibility+organization
    if (item.kind == "external" && item.organization == organization) {
        return true;
    } else {
        return false;
    }
}

var a = (getStatus());
var CB_NUM = [];




$.ajax({
    url: "../api/contextbroker.php",
    data: {
        action: "get_all_contextbroker",
        number: true,
        token: sessionToken
    },
    type: "POST",
    async: true,
    success: function (data) {

        //TO DO: mettere controllo ToolAdmin lato server
        if (data["status"] === 'ok' && (loggedRole == "ToolAdmin")) {
            data.data = data.data.filter(External_Organizzation);
            //item => item.kind === "external");
            addCB($("#selectContextBrokerM"), data);
            addCB($("#selectContextBrokerLD"), data);

            Element_Table(data.data);
        } else if (data["status"] === 'ok' && (loggedRole == "RootAdmin")) {
            data.data = data.data.filter(item => item.kind === "external");
            addCB($("#selectContextBrokerM"), data);
            addCB($("#selectContextBrokerLD"), data);

            Element_Table(data.data);
        } else {
            console.log("error getting the context brokers " + data);
        }
        CB_NUM = data.number;
    },
    error: function (data) {
        console.log("error in the call to get the context brokers " + data);
        alert("Network errors. <br/> Get in touch with the Snap4City Administrator<br/>" + JSON.stringify(data));
    }
});

//--------to get the list of extraction rules----------
$.ajax({
    url: "../api/extractionRules.php",
    data: {
        action: "get_rules",
        token: sessionToken
    },
    type: "POST",
    async: true,
    success: function (data) {
        if (data["status"] === 'ok') {
            extractionRules = data.data;
            //console.log(extractionRules)
            for (rule of extractionRules) {
                var attributeName = JSON.parse(rule.selector)['param']['s']
                //attributeName = attributeName.replace("$.", "");
                rule.attributeName = attributeName;
            }

            //console.log(extractionRules)
        } else {
            console.log("error getting extraction Rules " + data);
        }
    },
    error: function (data) {
        console.log("error in the call to get extraction rules " + data);
        alert("Network errors. <br/> Get in touch with the Snap4City Administrator<br/>" + JSON.stringify(data));
    }
});




//--------------------Ajax call function to upload file data about CB START---------------------//

function Element_Table(mydata) {



    dataTableCB = $('#contextExternalBrokerTable').DataTable({
        scrollX: true,
        "paging": true,
        data: mydata,
        "columns": [{
                "class": "details-control",
                "name": "position",
                "orderable": false,
                "data": null,
                "defaultContent": "",
                "render": function () {
                    return '<i class="fa fa-plus-square" aria-hidden="true" disabled ></i>';
                }
            }, {
                "name": "name",
                "data": function (row, type, val, meta) {
                    return row.name;
                }
            }, {
                "name": "StatusCB",
                "data": function (row, type, val, meta) {

                    A = JSON.parse(JSON.parse(a)['message'])
                    if( A.length==0)
                        return '<button id="ActivatebuttonB" type="button" data-contextbroker = "' + row.name + '" onclick="SetFreqNstart(\'' + row.name + '\')" class="StatusCBDashBtn btn" style=" background-color: rgb(69, 183, 175); color: white;" ><b>ACTIVATE </b>  </button>';
                       
                    for (var i = 0; i < A.length; i++) {
                       

                        if (A[i] == row.name) {
                            return '<button id="DisabledbuttonB" type="button" data-contextbroker = "' + row.name + '" onclick="killDiscoveryCB();"  class="StatusCBDashBtn btn btn-danger" > <b>DISABLED </b></button>';
                            
                        } else {
                            B= '<button id="ActivatebuttonB" type="button" data-contextbroker = "' + row.name + '" onclick="SetFreqNstart(\'' + row.name + '\')" class="StatusCBDashBtn btn" style=" background-color: rgb(69, 183, 175); color: white;" ><b>ACTIVATE </b>  </button>';
                        }
                    
                    }
                    return B;
                }
            },
            {
                "name": "frequencyCB",
                "data": function (row, type, val, meta) {
                    A = JSON.parse(JSON.parse(a)['message'])
                     if( A.length==0)
                        return '-';
                    for (var i = 0; i < A.length; i++) {

                        if (A[i] == row.name) {
                            freq = getFrequency(row.req_frequency);
                            
                        } else
                            freq= "-";
                        // getFreq_timestampStatus(row.name).req_frequency;
                    }return freq;

                }
                
            },
            {
                "name": "TimestampChangeStatusCB",
                "data": function (row, type, val, meta) {
                    A = JSON.parse(JSON.parse(a)['message'])
                     if( A.length==0)
                        return '-';
                    for (var i = 0; i < A.length; i++) {

                        if (A[i] == row.name) {
                            freq = (row.timestampstatus);
                            
                        } else
                            freq= "-";
                        // getFreq_timestampStatus(row.name).req_frequency;
                    
                    }
                    return freq;
                }


                // }
            }, {
                "name": "NumberNewDevice",
                "data": function (row, type, val, meta) {
                    var NUM_DEV = "-"
                    if (CB_NUM.length != 0) {
                        for (let i = 0; i < CB_NUM.length; i++) {
                            if (CB_NUM[i][row.name]) {
                                NUM_DEV = CB_NUM[i][row.name];
                            } else {
                                NUM_DEV = "-";
                            }

                            return NUM_DEV;
                        }
                    }
                    return NUM_DEV;


                }
            }
        ],
        "order": []
    });

}

//function to set frequency and start discovery
function SetFreqNstart(id) {
    $('#FREQactiveBrokersModal').modal('show');
    var contextbroker = id;

    $('#FREQsetButton').click(function () {
        $('#FREQsetButton').text("LOADING...PLEASE WAIT 10 seconds");
        $('#FREQsetButton').prop('disabled', true);

        var ip, port, protocol, user, accessLink, model, apikey, fiwareservice, kind;
        $.ajax({
            url: "../api/associationRulesApi.php",
            data: {
                action: "get_cb_details",
                cb: contextbroker,
                freq: getFrequency(''),
                token: sessionToken
                        //username: loggedUser,
                        //organization:organization
            },
            type: "POST",
            async: true,
            datatype: 'json',
            success: function (data) {
                //console.log("success returned from cbr");
                var content = data["content"];
                content = content[0];
                ip = content["ip"];
                protocol = content["protocol"];
                port = content["port"];
                user = content["username"];
                accesslink = content["accessLink"];
                accessport = content["accessport"];
                model = $('#selectModelLD').val();
                edge_gateway_type = $('#selectGatewayTypeLD').val();
                edge_gateway_uri = $('#gatewayUri').val();
                apikey = content["apikey"];
                path = content["path"];
                kind = content["kind"];
                //console.log("apikey "+apikey);

                var ipa = ip + ':' + port;
                if ($('#selectModelLD').val() === undefined || $('#selectModelLD').val().length < 1) {
                    model = "custom";
                }
                activateStub(contextbroker, ipa, protocol, user, accesslink, accessport, model, edge_gateway_type, edge_gateway_uri, path, apikey, kind);

            },
            error: function (data) {
                console.log("faliure" + JSON.stringify(data));
            }
        });

    });

}


//function to kill discovery 
function killDiscoveryCB() {

    var contextbroker = $('#DisabledbuttonB').attr('data-contextbroker');
    //    var contextbroker = document.getElementById('activeInactiveBrokes').value;
    var data = "contextbroker=" + contextbroker + "&ip=kill";
    var protocol = "";
    $.ajax({
        url: "../api/associationRulesApi.php",
        data: {
            action: "get_cb_details",
            cb: contextbroker,
            token: sessionToken

                    //username: loggedUser,
                    //organization:organization
        },
        type: "POST",
        async: true,
        datatype: 'json',
        success: function (data) {
            //console.log("success");
            var content = data["content"];
            content = content[0];
            protocol = content["protocol"];

            activateStub(contextbroker, "kill", protocol, "", "", "", "", "", "", "", "");
            //$("#activateButton").attr("disabled", false);
            // $("#inactivateButton").attr("disabled", true);
            // $("#activeInactiveBrokes option:selected").attr('name', "inactive");


        },
        error: function (data) {
            console.log("error retrieving protocol name");
        }
    });

    /*$("#activeInactiveBrokes").removeClass("active");
     $("#activeInactiveBrokes").addClass("inactive");*/



}
//--------------------Ajax call function to upload file data about CB END---------------------//


//--------------------Ajax call function to upload file data---------------------//


function activateStub(cb, ipa, protocol, user, accesslink, accessport, model, edge_type, edge_uri, path, apikey, kind) {
    //console.log("log "+ cb + " "+ipa+" "+accesslink+" "+model+ " api "+ apikey + " organization "+ organization + " kind "+kind);
    var data;
    if (apikey !== null || apikey !== undefined) {
        data = "contextbroker=" + cb + "&ip=" + ipa + "&user=" + user + "&al=" + accesslink + "&ap=" + accessport + "&model=" + model + "&edge_gateway_type=" + edge_type + "&edge_gateway_uri=" + edge_uri + "&organization=" + organization + "&path=" + path + "&kind=" + kind + "&frequency=" + getFrequency('') + "&apikey=" + apikey + "&token=" + sessionToken;
    } else {
        data = "contextbroker=" + cb + "&ip=" + ipa + "&user=" + user + "&al=" + accesslink + "&ap=" + accessport + "&model=" + model + "&edge_gateway_type=" + edge_type + "&edge_gateway_uri=" + edge_uri + "&organization=" + organization + "&path=" + path + "&kind=" + kind + "&frequency=" + getFrequency('') + "&token=" + sessionToken;
    }
    var service = _serviceIP + "/api/" + protocol.replace(" ", "_").replace("/", "_");

    //console.log("data to be sent "+data);
    //console.log("service "+ service);
    var xhr = ajaxRequest();
    //	location.reload();

    setTimeout(() => {
        location.reload();
    }, 10000);

    xhr.addEventListener("readystatechange", function () {
        console.log(" " + new Date() + " this.readyState " + this.readyState);
        if (this.readyState === 4 && this.status == 200) {


            return this.responseText;

        }
    });


    xhr.open("POST", service);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.setRequestHeader('Cache-Control', 'no-cache');
    xhr.setRequestHeader("Access-Control-Allow-Origin", "*");
    xhr.send(data);
    return true;
}

function getFrequency(secs) {

    var seconds = parseInt($("#FselectorSeconds option:selected").text(), 10) * 1000;
    var minutes = parseInt($("#FselectorMinutes option:selected").text(), 10) * 60 * 1000;
    var hours = parseInt($("#FselectorHours option:selected").text(), 10) * 60 * 60 * 1000;

    if (secs || secs != '') {
        seconds = secs / 1000;
        var hrs = ~~(seconds / 3600);
        var mins = ~~((seconds % 3600) / 60);
        secs = ~~seconds % 60;
        //secs + " seconds " +
        return  mins + " minutes " + hrs + " hours";
    } else {
        return seconds + minutes + hours;
    }

}

//-----------------------------------------------------------------------------//

/*************Table related ****************/


function updateDeviceTimeout() {
    $("#editDeviceOkModal").modal('hide');
    setTimeout(function () {
        location.reload();
    }, 1000);
}
//---------------build the table-----------------------------//


function format(d) {

    // `d` is the original data object for the row
    var string = '<div class="container-fluid">' +
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
            '<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Gateway/Edge Type:</b>' + "  " + d.edge_gateway_type + '</div>' +
            '<div class="clearfix visible-xs"></div>' +
            '<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Gateway/Edge Uri:</b>' + "" + d.edge_gateway_uri + '</div>' +
            '</div>' +
            '<div class="row">' +
            '<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>K1:</b>' + "  " + d.k1 + '</div>' +
            '<div class="clearfix visible-xs"></div>' +
            '<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><b>K2:</b>' + "  " + d.k2 + '</div>' +
            '</div>';
    if (d.protocol == "ngsi w/MultiService") {
        string = string + '<div class="row">' +
                '<div class="col-xs-6 col-sm-6" style="background-color:#AAAAFF;"><b>Tenant:</b>' + "  " + d.service + '</div>' +
                '<div class="clearfix visible-xs"></div>' +
                '<div class="col-xs-6 col-sm-6" style="background-color:#AAAAFF;"><b>ServicePath:</b>' + "  " + d.servicePath + '</div>' +
                '</div>' +
                '</div>';
    }

    return string;

}

function fetch_data(destroyOld, selected = null) {
    if (destroyOld) {
        $('#devicesTable').DataTable().destroy();
        $('#registeredDevicesTable').DataTable().destroy();
        tableFirstLoad = true;
    }


    if (selected == null) {
        mydata = {action: "get_temporary_devices", token: sessionToken, should_be_registered: "no", no_columns: ["position", "status1", "edit", "delete", "map"]};
        mydata2 = {action: "get_all_temporary_event_value_for_registered_devices", token: sessionToken, no_columns: ["position", "status1", "delete", "map", "edit"]};
    }

    dataTable = $('#devicesTable').DataTable({
        dom: 'Pfrtip',
        "processing": true,

        "paging": true,
        "ajax": {

            url: "../api/bulkDeviceUpdate.php",
            data: mydata,
            //{action: "get_temporary_devices", token: sessionToken, should_be_registered: "no", no_columns: ["position", "status1", "edit", "delete", "map"]},
            type: "POST"
        },

        "searchPanes": {
            "columns": [2],
            "initCollapsed": true,
            orderable: false

        },
        columnDefs: [{
                searchPanes: {
                    show: true
                },
                targets: [2]
            }],
        serverSide: true,
        select: true,
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
                "name": "id", "data": function (row, type, val, meta) {

                    return "<b>" + row.name + "</b>";
                }
            },
            {

                "name": "contextbroker",
                "data": "contextbroker"

            },
            {
                "name": "protocol", "data": function (row, type, val, meta) {

                    return row.protocol;
                }
            },
            {
                "name": "format", "data": function (row, type, val, meta) {

                    return row.format;
                }
            },
            {
                "name": "devicetype", "data": function (row, type, val, meta) {

                    return row.devicetype;
                }
            },
            {
                "name": "status1", "orderable": false, "data": function (row, type, val, meta) {
                    // var myattributes = []
                    // for (event_value of list_temporary_event_value) {
                    // 	if (row.contextbroker == event_value.contextbroker && row.name == event_value.device) myattributes.push(event_value);
                    // }
                    // var toVerify = { "contextbroker": row.contextbroker, "name": row.name, "devicetype": row.devicetype, "model": row.model, "macaddress": row.macaddress, "frequency": row.frequency, "kind": row.kind, "protocol": row.protocol, "format": row.format, "service": row.service, "servicepath": row.servicePath, "latitude": row.latitude, "longitude": row.longitude, "visibility": row.visibility, "k1": row.k1, "k2": row.k2, "subnature": row.subnature, "static_attributes": row.static_attributes, "producer": row.producer, "edge_gateway_type": row.edge_gateway_type, "edge_gateway_uri": row.edge_gateway_uri, "deviceValues": myattributes };
                    // var status = verifyDevice(toVerify);
                    if (row.status == 'invalid') {
                        return '<button type="button" id="invalid" class="btn btn-warning" onclick="showValidityMsg(\'' + row.status + '\',\'' + row.validity_msg + '\')\">Invalid</button>';
                    } else if (row.status == 'valid') {
                        return '<button type="button" id="valid"  class="btn btn-success"  data-id = "' + row.name + '"  onclick="showValidityMsg(\'' + row.status + '\',\'' + row.validity_msg + '\')\">Valid</button>';
                    }
                    // if (!status.isvalid) {
                    // 	row.status = 'invalid'
                    // 	row.validity_msg = status.message
                    // 	return '<button type="button" id="invalid" class="btn btn-warning" onclick="showValidityMsg(\'' + 'invalid' + '\',\'' + status.message + '\')\">Invalid</button>';
                    // }
                    // else if (status.isvalid) {
                    // 	row.status = 'valid'
                    // 	row.validity_msg = status.message
                    // 	return '<button type="button" id="valid"  class="btn btn-success" onclick="showValidityMsg(\'' + 'valid' + '\',\'' + status.message + '\')\">Valid</button>';
                    // }
                }
            },
            {
                data: null,
                "name": "edit",
                "orderable": false,
                className: "center",
                render: function (d) {
                    return '<button type="button" class="editDashBtn" ' +
                            'data-id="' + d.name + '" ' +
                            'data-contextBroker="' + d.contextbroker + '" ' +
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
                            'data-edge_gateway_type="' + d.edge_gateway_type + '" ' +
                            'data-edge_gateway_uri="' + d.edge_gateway_uri + '" ' +
                            'data-k1="' + d.k1 + '" ' +
                            'data-k2="' + d.k2 + '" ' +
                            'data-attributes="' + d.attributes + '" ' +
                            'data-service="' + d.service + '" ' +
                            'data-servicePath="' + d.servicePath + '" ' +
                            'data-subnature="' + d.subnature + '" ' +
                            'data-static-attributes="' + btoa(d.static_attributes) + '" ' +
                            'data-status1="' + d.status + '">Edit</button>';
                }
            },
            {
                data: null,
                "name": "delete",
                "orderable": false,
                className: "center",
                render: function (d) {
                    return '<button type="button" class="delDashBtn" ' +
                            'data-id="' + d.name + '" ' +
                            'data-contextBroker="' + d.contextbroker + '" ' +
                            'data-status1="' + d.status + '" ' +
                            'data-uri="' + d.uri + '">Delete</button>';
                }
            },
            {
                data: null,
                "name": "map",
                "orderable": false,
                className: "center",
                render: function (d) {
                    return '<div class="addMapBtn"><i  data-toggle="modal" data-target="#addMapShow" onclick="drawMap(\'' + d.latitude + '\',\'' + d.longitude + '\', \'' + d.id + '\', \'' + d.devicetype + '\', \'' + d.kind + '\', \'' + 'addDeviceMapModalBodyShow' + '\')\" class="fa fa-globe"  style=\"font-size:36px; color: #0000ff\"></i></div>';
                }
            },
        ],

        "order": []

    });



    dataTable2 = $('#registeredDevicesTable').DataTable({
        "processing": true,
        "serverSide": true,
        "responsive": {
            details: false
        },
        "paging": true,
        "ajax": {
            url: "../api/value.php",
            data: mydata2,
            datatype: 'json',
            type: "POST",
        },
        "columns": [
            {
                "name": "device", "data": function (row, type, val, meta) {
                    // return row.name.split(".").slice(-1)[0];
                    return '<b>' + row.device + '</b>';
                }
            },
            {
                "name": "cb", "data": function (row, type, val, meta) {
                    // return row.contextbroker;
                    return row.cb;
                }
            },
            {
                "name": "value_name", "data": function (row, type, val, meta) {
                    // return row.valuename;
                    return '<b>' + row.value_name + '</b>'
                }
            },
            {
                "name": "data_type", "data": function (row, type, val, meta) {
                    // return row.datatype;
                    return row.data_type;
                }
            },
            {
                "name": "value_type", "data": function (row, type, val, meta) {
                    // return row.valuetype;
                    return row.value_type;
                }
            },
            {
                "name": "value_unit", "data": function (row, type, val, meta) {
                    // return row.valueunit;
                    return row.value_unit;
                }
            },
            {
                "name": "healthiness_criteria", "data": function (row, type, val, meta) {
                    // return row.healthinesscriteria;
                    return row.healthiness_criteria;
                }
            },
            {
                "name": "value_refresh_rate", "data": function (row, type, val, meta) {
                    // return row.refreshrate;
                    return row.value_refresh_rate;
                }
            },
            {
                "name": "status1", "orderable": false, "data": function (row, type, val, meta) {
                    //row.editable = '0'

                    var status = verifyParameter(row);
                    if (!status.isvalid) {
                        row.status = 'invalid'
                        return '<button type="button" id="invalid" class="btn btn-warning" onclick="showValidityMsg(\'' + 'invalid' + '\',\'' + status.message + '\')\">Invalid</button>';
                    } else if (status.isvalid) {
                        row.status = 'valid'
                        return '<button type="button" id="valid"  class="btn btn-success" data-id = "' + row.value_name + '"  onclick="showValidityMsg(\'' + 'valid' + '\',\'' + status.message + '\')\">Valid</button>';
                    }
                    //return row.editable;
                }
            },
            {
                data: null,
                "name": "edit",
                "orderable": false,
                className: "center",
                render: function (d) {
                    var device = ""
                    if (d.service != null && d.servicePath != null)
                        device = d.service + '.' + d.servicePath + '.' + d.device;
                    else if (d.service != null && d.servicePath == null)
                        device = d.service + './.' + d.device;
                    else
                        device = d.device;
                    d.device_with_path = device;

                    return '<button type="button" class="editDashBtn" ' +
                            'data-id="' + device + '" ' +
                            'data-contextBroker="' + d.cb + '" ' +
                            'data-valuename="' + d.value_name + '" ' +
                            'data-datatype="' + d.data_type + '" ' +
                            'data-valuetype="' + d.value_type + '" ' +
                            'data-valueunit="' + d.value_unit + '" ' +
                            'data-healthinesscriteria="' + d.healthiness_criteria + '" ' +
                            'data-refreshrate="' + d.value_refresh_rate + '" ' +
                            'data-editable="' + d.editable + '" ' +
                            'data-service="' + d.service + '" ' +
                            'data-servicepath="' + d.servicePath + '" ' +
                            'data-model="' + d.model + '" ' +
                            '" >Edit</button>';
                    //return '<button type="button" class="editDashBtn" >Edit</button>';
                }
            },
            {
                data: null,
                "name": "delete",
                "orderable": false,
                className: "center",
                render: function (d) {
                    var device = ""
                    if (d.service != null && d.servicePath != null)
                        device = d.service + '.' + d.servicePath + '.' + d.device;
                    else if (d.service != null && d.servicePath == null)
                        device = d.service + './.' + d.device;
                    else
                        device = d.device;
                    return '<button type="button" class="delDashBtn" ' +
                            'data-id="' + device + '" ' +
                            'data-contextBroker="' + d.cb + '" ' +
                            'data-valuename="' + d.value_name + '">Delete</button>';
                    // return '<button type="button" class="delDashBtn" >Delete</button>';
                }
            }
        ],
        "order": []
    });
}

//end of fetch function 
function getStatus(useStatus) {

    var service = _serviceIP + "/api/status";
    var xhr = ajaxRequest();

    xhr.addEventListener("readystatechange", function () {
        if (this.readyState === 4 && this.status == 200) {
            //console.log("USE STATUS "+ this.responseText);
            if (useStatus) {
                useStatus(this.responseText);
            } else {
                a = this.responseText;
                return a;
            }
        }

    });

    xhr.open("GET", service);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.setRequestHeader("Access-Control-Allow-Origin", "*");
    xhr.send("");


}



function buildPreview(attributesIf, destroyOld, selected = null) {
    if (destroyOld) {
        //  $('#devicePreviewTable').DataTable().destroy();
    }

    if (selected == null) {
        mydata = {action: "get_affected_devices", token: sessionToken, attributes: attributesIf, no_columns: [""]};
    }

    dataPreviewTable = $('#devicePreviewTable').DataTable({
        "destroy": true,
        "processing": true,
        "serverSide": true,
        "lengthMenu": [[5, 25, 50, 100, -1], [5, 25, 50, 100, "All"]],
        "pageLength": 5,
        //"responsive" : true,
        "responsive": {
            details: false
        },
        "paging": true,
        "ajax": {
            url: "../api/bulkDeviceUpdate.php",
            data: mydata,
            //token : sessionToken,
            datatype: 'json',
            type: "POST",
        },
        "columns": [
            {
                "name": "id", "data": function (row, type, val, meta) {
                    //console.log("Name buildtable "+ row.name);
                    return row.name;
                }
            },
            {
                "name": "contextbroker", "data": function (row, type, val, meta) {
                    return row.contextbroker;
                }
            },
            {
                "name": "protocol", "data": function (row, type, val, meta) {
                    return row.protocol;
                }
            },
            {
                "name": "format", "data": function (row, type, val, meta) {
                    return row.format;
                }
            },
            {
                "name": "devicetype", "data": function (row, type, val, meta) {
                    return row.devicetype;
                }
            }
        ],
        "order": []
    });

}


function buildPreviewValues(attributesIf, destroyOld, selected = null) {
    if (destroyOld) {
        // $('#valuesPreviewTable').DataTable().destroy();
    }

    if (selected == null) {
        mydata = {action: "get_affected_values", token: sessionToken, attributes: attributesIf, no_columns: [""]};
    }

    dataPreviewTable = $('#valuesPreviewTable').DataTable({
        "destroy": true,
        "processing": true,
        "serverSide": true,
        "lengthMenu": [[5, 25, 50, 100, -1], [5, 25, 50, 100, "All"]],
        "pageLength": 5,
        //"responsive" : true,
        "responsive": {
            details: false
        },
        "paging": true,
        "ajax": {
            url: "../api/bulkDeviceUpdate.php",
            data: mydata,
            //token : sessionToken,
            datatype: 'json',
            type: "POST",
        },
        "columns": [
            {
                "name": "id", "data": function (row, type, val, meta) {
                    return row.name;
                }
            },
            {
                "name": "contextbroker", "data": function (row, type, val, meta) {
                    return row.contextbroker;
                }
            },
            {
                "name": "value_name", "data": function (row, type, val, meta) {
                    return row.value_name;
                }
            },
            {
                "name": "data_type", "data": function (row, type, val, meta) {
                    return row.data_type;
                }
            },
            {
                "name": "value_type", "data": function (row, type, val, meta) {
                    return row.value_type;
                }
            },
            {
                "name": "value_unit", "data": function (row, type, val, meta) {
                    return row.value_unit;
                }
            },
            {
                "name": "healthiness_criteria", "data": function (row, type, val, meta) {
                    return row.healthiness_criteria;
                }
            }/*,
             {"name": "healthiness_value", "data": function ( row, type, val, meta ) {
             return row.healthiness_value;
             }
             }*/
        ],
        "order": []
    });

}

function buildPreviewAssociationRules(attributesIf, destroyOld, selected = null) {
    if (destroyOld) {
        //  $('#devicesSuggestionsTable').DataTable().destroy();
    }

    if (selected == null) {
        mydata = {action: "get_rules_affecting_data", token: sessionToken, attributes: attributesIf, value: "value_type", no_columns: [""]};
    }

    dataPreviewTable = $('#devicesSuggestionsTable').DataTable({
        "destroy": true,
        "processing": true,
        "serverSide": true,
        "lengthMenu": [[5, 25, 50, 100, -1], [5, 25, 50, 100, "All"]],
        "pageLength": 5,
        //"responsive" : true,
        "responsive": {
            details: false
        },
        "paging": true,
        "ajax": {
            url: "../api/associationRulesApi.php",
            data: mydata,
            //token : sessionToken,
            datatype: 'json',
            type: "POST",
        },
        "columns": [
            {
                "name": "id", "data": function (row, type, val, meta) {
                    return row.name;
                }
            },
            {
                "name": "contextbroker", "data": function (row, type, val, meta) {
                    return row.contextbroker;
                }
            },
            {
                "name": "value_name", "data": function (row, type, val, meta) {
                    return row.value_name;
                }
            },
            {
                "name": "data_type", "data": function (row, type, val, meta) {
                    return row.data_type;
                }
            },
            {
                "name": "value_type", "data": function (row, type, val, meta) {
                    return row.value_type;
                }
            },
            {
                "name": "value_unit", "data": function (row, type, val, meta) {
                    return row.value_unit;
                }
            },
            {
                "name": "healthiness_criteria", "data": function (row, type, val, meta) {
                    return row.healthiness_criteria;
                }
            }/*,
             {"name": "healthiness_value", "data": function ( row, type, val, meta ) {
             return row.healthiness_value;
             }
             }*/
        ],
        "order": []
    });

}


function getAffectedRowsValue(valueIfOp) {

    var num1 = document.getElementById('ifBlockTableValue').tBodies[0].childElementCount;
    var attributesIfValues = [];
    for (var m = 0; m < num1; m++) {
       
        var fieldIf = document.getElementById('ifBlockTableValue').tBodies[0].rows.item(m).cells.item(1).childNodes[0].value;
        var operatorIf = document.getElementById('ifBlockTableValue').tBodies[0].rows.item(m).cells.item(2).childNodes[0].value;


        if (!valueIfOp) {
            var valueIf = document.getElementById('ifBlockTableValue').tBodies[0].childNodes[m].childNodes[3].childNodes[0].value;
        } else if (!valueIf && !valueIfOp) {
            var valueIf = document.getElementById('ifBlockTableValue').tBodies[0].rows.item(m).cells.item(3).childNodes[0].value;
        } else if (valueIfOp[m].value) {
            var valueIf = valueIfOp[m].value;
        }
        //params.newValue
        if (valueIf.localeCompare("Empty") == 0) {
            valueIf = "";
        }
        if (fieldIf == undefined || fieldIf == null || fieldIf == "") {
            fieldIf = "healthiness_criteria";
        }

        var newIf = {"field": fieldIf, "operator": operatorIf, "value": valueIf};
        attributesIfValues.push(newIf);
    }

    if (num1 != 0) {
        $.ajax({
            url: "../api/bulkDeviceUpdate.php",
            data: {
                action: "get_affected_values_count",
                //username: loggedUser,
                //organization:organization,
                attributesIf: JSON.stringify(attributesIfValues),
                token: sessionToken
                        //attributesThen: JSON.stringify(attributesThen)	    
            },
            dataType: 'json',
            type: "POST",
            async: true,
            success: function (myData) {
                $("#valueFound").html(myData['content'] + " values founded");
                //	document.getElementById('devicesFound').value = myData['content'] + " devices found";

                if (attributesIfValues.length == 0) {
                    buildPreviewValues(JSON.stringify(attributesIfValues), true);
                } else {
                    buildPreviewValues(JSON.stringify(attributesIfValues), previewValuesFirstLoad);
                    previewValuesFirstLoad = true;
                }

            },
            error: function (myData) {
                console.log(JSON.stringify(myData));
                $("#valueFound").html("0 values founded");
                console.log("data faliure" + myData['msg']);
            }
        });//end of ajax get_affected*/
    } else {
        $("#valueFound").html("0 values founded");

    }
}







$(document).ready(function () {



    checkBulkStatus();
    //fetch_data function will load the device table 	
    fetch_data(false);


    //detail control for device dataTable
    var detailRows = [];

    $('#devicesTable tbody').on('click', 'td.details-control', function () {
        var tr = $(this).closest('tr');
        var tdi = tr.find("i.fa");
        var row = dataTable.row(tr);
        //console.log('data: '+ JSON.stringify(row.data()));

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


    function useStatus(statusText) {
        //console.log("chiamo activate stub");
        var activeBrokers = [];
        var nContextBroker = document.getElementById('selectContextBrokerLD').length;

        var brokers = JSON.parse(statusText);
        var brokers = brokers.message;
        var row = "";
        //console.log("nContextBroker "+ JSON.stringify(brokers));
        $('#activeInactiveBrokes').html("");
        $('#inactivateButton').show();
        $('#stopAllBrokers').show();

        for (cb = 0; cb < nContextBroker; cb++) {

            var broker = document.getElementById('selectContextBrokerLD').item(cb).value;

            if (brokers.includes(broker)) {
                activeBrokers[broker] = "active";
                row = $('<option value="' + broker + '" name ="active">' + broker + '</option>');
                $("#activateButton").attr("disabled", true);
                //$("#statusSelectedBroker").css("visibility", "visible");
                $("#inactivateButton").attr("disabled", false);
                gb_active_brokers_names.push(broker);
            }
            /*	else{
             activeBrokers[broker]= "inactive";
             row= $('<option value="'+broker+'" name ="inactive">'+broker+'</option>');
             $("#activateButton").attr("disabled", false);
             $("#statusSelectedBroker").css("visibility", "hidden");
             $("#inactivateButton").attr("disabled", true);
             }*/
            $('#activeInactiveBrokes').append(row);
        }
        if (document.getElementById("activeInactiveBrokes").options.length == 0) {
            var r = $('<option value="No active Broker discovery" name ="inactive">' + "No active Broker discovery" + '</option>');
            $('#activeInactiveBrokes').append(r);
            $('#inactivateButton').hide();
            $('#stopAllBrokers').hide();
        }
    }





    $('#retrieveButton').click(function () {
        $('#retrieveButton').text("LOADING...PLEASE WAIT 10 seconds");
        $('#retrieveButton').prop('disabled', true);
        var contextbroker = $('#selectContextBrokerLD').val();
        var ip, port, protocol, user, accessLink, model, apikey, fiwareservice, kind;
        $.ajax({
            url: "../api/associationRulesApi.php",
            data: {
                action: "get_cb_details",
                cb: contextbroker,

                token: sessionToken
                        //username: loggedUser,
                        //organization:organization
            },
            type: "POST",
            async: true,
            datatype: 'json',
            success: function (data) {
                //console.log("success returned from cbr");
                var content = data["content"];
                content = content[0];
                ip = content["ip"];
                protocol = content["protocol"];
                port = content["port"];
                user = content["username"];
                accesslink = content["accessLink"];
                accessport = content["accessport"];
                model = $('#selectModelLD').val();
                edge_gateway_type = $('#selectGatewayTypeLD').val();
                edge_gateway_uri = $('#gatewayUri').val();
                apikey = content["apikey"];
                path = content["path"];
                kind = content["kind"];
                //console.log("apikey "+apikey);

                var ipa = ip + ':' + port;
                if ($('#selectModelLD').val() === undefined || $('#selectModelLD').val().length < 1) {
                    model = "custom";
                }
                activateStub(contextbroker, ipa, protocol, user, accesslink, accessport, model, edge_gateway_type, edge_gateway_uri, path, apikey, kind);

            },
            error: function (data) {
                console.log("faliure" + JSON.stringify(data));
            }
        });

    });//end of onclick

    $(document).on({
        change: function () {
            var selectedBroker = document.getElementById('activeInactiveBrokes').value;
            var status = $(this).find('option:selected').attr("name");
            if (status == "active") {
                $("#activateButton").attr("disabled", true);
                //$("#statusSelectedBroker").css("visibility", "visible");
                $("#inactivateButton").attr("disabled", false);
            } else {
                $("#activateButton").attr("disabled", false);
                //	 $("#statusSelectedBroker").css("visibility", "hidden");
                $("#inactivateButton").attr("disabled", true);
            }
        }
    }, '#selectBrokersActive select');

    $("#DisabledbuttonB").off("click");
    $('#DisabledbuttonB').on('click', function () {
        var contextbroker = $(this).attr('data-contextbroker');
        //    var contextbroker = document.getElementById('activeInactiveBrokes').value;
        var data = "contextbroker=" + contextbroker + "&ip=kill";
        var protocol = "";
        $.ajax({
            url: "../api/associationRulesApi.php",
            data: {
                action: "get_cb_details",
                cb: contextbroker,
                token: sessionToken
                        //username: loggedUser,
                        //organization:organization
            },
            type: "POST",
            async: true,
            datatype: 'json',
            success: function (data) {
                //console.log("success");
                var content = data["content"];
                content = content[0];
                protocol = content["protocol"];

                activateStub(contextbroker, "kill", protocol, "", "", "", "", "", "", "", "");
                //$("#activateButton").attr("disabled", false);
                // $("#inactivateButton").attr("disabled", true);
                // $("#activeInactiveBrokes option:selected").attr('name', "inactive");
                location.reload();
            },
            error: function (data) {
                console.log("error retrieving protocol name");
            }
        });

        /*$("#activeInactiveBrokes").removeClass("active");
         $("#activeInactiveBrokes").addClass("inactive");*/


    });

    $("#activateButton").off("click");
    $('#activateButton').on('click', function () {
        //console.log("activate called");
        var contextbroker = document.getElementById('activeInactiveBrokes').value;
        var ip, port, protocol, user, accessLink, model, apikey, fiwareservice, kind;
        $.ajax({
            url: "../api/associationRulesApi.php",
            data: {
                action: "get_cb_details",
                cb: contextbroker,
                token: sessionToken
                        //username: loggedUser,
                        //organization:organization
            },
            type: "POST",
            async: true,
            datatype: 'json',
            success: function (data) {
                $("#activateButton").attr("disabled", true);
                $("#inactivateButton").attr("disabled", false);
                $("#activeInactiveBrokes option:selected").attr('name', "active");
                /*$("#activeInactiveBrokes option:selected").removeClass("inactive");
                 $("#activeInactiveBrokes option:selected").addClass("active");*/
                //console.log("success");
                var content = data["content"];
                content = content[0];
                ip = content["ip"];
                protocol = content["protocol"];
                port = content["port"];
                user = content["username"];
                accesslink = content["accessLink"];
                accessport = content["port"];
                model = "custom";
                edge_gateway_type = "";
                edge_gateway_uri = "";
                apikey = content["apikey"];
                path = content["path"];
                kind = content["kind"];
                //console.log("apikey "+apikey);

                var ipa = ip + ':' + port;
                //console.log("ACTIVATE STUB "+ kind);
                //		console.log("MODEL " + model + " gateway " + edge_gateway_type +  " url " + edge_gateway_uri);

                //var retrieveModalWait = document.getElementById('retrieveModalWait');
                //var span_b = document.getElementsByClassName("close")[0];
                //	var spin_w = document.getElementById("loader_spinW");
                //	var progress_ok_w=document.getElementById('progress_ok_wait');
                //	document.getElementById('retrieveModalWait').innerHTML= "<p>Your data are being retrieved..</p> Click 'ok' to close this message.";
                //	retrieveModalWait.style.display = "block";
                //	spin_w.style.display="none";
                //	progress_ok_w.style.display="block";

                activateStub(contextbroker, ipa, protocol, user, accesslink, accessport, model, edge_gateway_type, edge_gateway_uri, path, apikey, kind);

            },
            error: function (data) {
                console.log("faliure" + JSON.stringify(data));
            }
        });
    });

    $("#stopAllBrokers").off("click");
    $('#stopAllBrokers').on('click', function () {
        //var nContextBroker = document.getElementById('activeInactiveBrokes').length;
        var nContextBroker = gb_active_brokers_names.length;

        for (cb = 0; cb < nContextBroker; cb++) {

            $("#activateButton").attr("disabled", false);
            $("#inactivateButton").attr("disabled", true);
            $("#activeInactiveBrokes option").attr('name', "inactive");
        }

        $.ajax({
            url: "../api/associationRulesApi.php",
            data: {
                action: "get_multiple_cb_details",
                cb: JSON.stringify(gb_active_brokers_names),
                token: sessionToken
                        //username: loggedUser,
                        //organization:organization
            },
            type: "POST",
            async: true,
            datatype: 'json',
            success: function (data) {
                var content = data["content"];

                for (var i = 0; i < content.length; i++) {
                    //console.log("cont2 "+ i+ " i"+ JSON.stringify(content[i]));
                    protocol = content[i].protocol;
                    activateStub(content[i].contextbroker, "kill", protocol, "", "", "", "", "", "", "", "");
                    //content[i].contextbroker
                }
            },
            error: function (data) {
                console.log("error retrieving protocol name");
            }
        });

    });
    //end of detail control for device dataTable 

//Start Related to Resigter a single Device

    $('#Register_Device').on('click', function () {
        var id = $('#valid').attr('data-id');
        console.log(id);

        $.ajax({
            url: "../api/bulkDeviceLoad.php",
            data: {
                action: "singleload",
                id: id,
                token: sessionToken
            },
            type: "POST",
            async: true,
            success: function (data) {
                if (data["status"] === 'ok') {

                    button = document.getElementById("Register_Device");
                    button.style.display = "none";

                    $("#ErrorDeviceAttrMsg").html("Your device is registered! ");
                    window.location.reload();
                } else {
                    $("#ErrorDeviceAttrMsg").html("There is some problem with your device registration ");
                }
            },
            error: function (data) {
                console.log("error in the call to get the context brokers " + data);
                alert("Network errors. <br/> Get in touch with the Snap4City Administrator<br/>" + JSON.stringify(data));
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

    //Delete device button 
    $("#progress_ok_wait").off("click");
    $("#progress_ok_wait").on("click", function () {
        var progress_modal_w = document.getElementById('retrieveModalWait');
        progress_modal_w.style.display = "none";
    });

    $('#devicesTable tbody').on('click', 'button.delDashBtn', function () {
        //console.log($(this));

        var id = $(this).attr('data-id');
        var contextbroker = $(this).attr('data-contextbroker');
        var uri = $(this).attr("data-uri");
        var status = $(this).attr("data-status1");

        $("#deleteDeviceModal div.modal-body").html('<div class="modalBodyInnerDiv"><span data-id = "' + id + '" data-contextbroker = "' + contextbroker + '" data-status1 = "' + status + '"  data-uri ="' + uri + '">Do you want to confirm deletion of device <b>' + id + '</b>?</span></div>');
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


    // Device dataTable table Style 

    $('#devicesTable thead').css("background", "rgba(0, 162, 211, 1)");
    $('#devicesTable thead').css("color", "white");
    $('#devicesTable thead').css("font-size", "1em");

    $('#registeredDevicesTable thead').css("background", "rgba(0, 162, 211, 1)");
    $('#registeredDevicesTable thead').css("color", "white");
    $('#registeredDevicesTable thead').css("font-size", "1em");


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

    $('#activeBrokers').off('click');
    $('#activeBrokers').click(function () {
        $('#activeBrokersModal').modal('show');

        getStatus(useStatus);
    });


    //Display devices on the map 
    $('#displayDevicesMap').off('click');
    $('#displayDevicesMap').click(function () {
        $.ajax({
            url: "../api/device.php",
            data: {
                action: "get_all_device_latlong",
                //organization:organization,
                //loggedrole:loggedRole
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
    });

    //$("#addMyNewDeviceRow").hide();

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


    $('#listDevicesLink .mainMenuItemCnt').addClass("mainMenuItemCntActive");
    $('#mobMainMenuPortraitCnt #listDevicesLink .mobMainMenuItemCnt').addClass("mainMenuItemCntActive");
    $('#mobMainMenuLandCnt #ListDevicesLink .mobMainMenuItemCnt').addClass("mainMenuItemCntActive");


    $("#insertValidBtn").off("click");
    $('#insertValidBtn').click(function () {
        insertValidDevices();
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
    function insertValidDevices() {
        var data = {
            action: "bulkload",
            //username: loggedUser,
            token: sessionToken,
            data_parallel: 1,
            //organization:organization,
            kbUrl: kbUrl,
            should_be_registered: "no"
        };

        $.post('../api/async_request.php', {'data': data}, function (response_data) {

            var progress_modal = document.getElementById('myModal');
            var span = document.getElementsByClassName("close")[0];
            var spin = document.getElementById("loader_spin");
            var progress_ok = document.getElementById('progress_ok');


        });
        var progress_modal_b = document.getElementById('myModal_forbulkstatus');
        var span_b = document.getElementsByClassName("close")[0];
        var spin_b = document.getElementById("loader_spin_forbulkstatus");
        var progress_ok_b = document.getElementById('progress_ok_forbulkstatus');
        var progress_stop_b = document.getElementById('progress_stop_forbulkstatus');
        document.getElementById('myModalBody_forbulkstatus').innerHTML = "<p>Inserting the valid devices is being processed...</p> By clicking 'Ok' you will be redirected to the main page, but the process will be running anyway.";
        progress_modal_b.style.display = "block";
        spin_b.style.display = "none";
        progress_ok_b.style.display = "block";
        progress_stop_b.style.display = "block";

        was_processing = 1;

        checkBulkStatus();


    }

    function stop_progress() {
        var progress_modal = document.getElementById('myModal_forbulkstatus');
        progress_modal.style.display = "none";

        if (timerID != undefined) {
            clearInterval(timerID); // The setInterval it cleared and doesn't run anymore.
        }

        $.ajax({
            url: "../api/bulkDeviceLoad.php",
            data: {
                action: "stop_bulk",
                //username: loggedUser,
                token: sessionToken,
                //organization:organization
            },
            type: "POST",
            async: true,
            dataType: "JSON",
            timeout: 0,
            success: function (mydata) {
                //console.log("bulk stop "+JSON.stringify(mydata));
                if (mydata["status"] == 'ok') {
                    is_processing = 0;
                    refresh();
                } else {
                    console.log("Error stoping the bulkload " + mydata);
                }
            },
            error: function (mydata) {
                console.log("Failure in stoping bulkload " + JSON.stringify(mydata));
            }
        });

    }
    function dismiss_dialog() {
        var progress_modal = document.getElementById('myModal_forbulkstatus');
        progress_modal.style.display = "none";

        if (timerID != undefined) {
            clearInterval(timerID); // The setInterval it cleared and doesn't run anymore.
        }

        window.location.replace('../management/devices.php');

    }

    function checkBulkStatus() {

        is_processing = 1;

        is_finished = 0;
        var progress_modal = document.getElementById('myModal_forbulkstatus');
        var span = document.getElementsByClassName("close")[0];
        var spin = document.getElementById("loader_spin_forbulkstatus");
        var progress_ok = document.getElementById('progress_ok_forbulkstatus');
        var progress_stop = document.getElementById('progress_stop_forbulkstatus');

        if (timerID != undefined) {
            clearInterval(timerID);
        }
        //console.log("is_processing "+ is_processing);
        timerID = setInterval(function () {

            if (is_processing == 0) {
                clearInterval(timerID);
                progress_modal.style.display = "none";

                if (was_processing == 1 && is_finished == 1) {
                    var progress_modal_ = document.getElementById('myModal');
                    var spin_ = document.getElementById("loader_spin");
                    var progress_ok_ = document.getElementById('progress_ok');
                    document.getElementById('myModalBody').innerHTML = "<p>Your valid devices have been uploaded.</p> ";
                    progress_modal_.style.display = "block";
                    spin_.style.display = "none";
                    progress_ok_.style.display = "block";
                    was_processing = 0;

                }
            } else {

                $.ajax({
                    url: "../api/bulkDeviceLoad.php",
                    data: {
                        action: "get_bulk_status",
                        //username: loggedUser,
                        token: sessionToken,
                        //organization:organization
                    },
                    type: "POST",
                    async: true,
                    dataType: "JSON",
                    timeout: 0,
                    success: function (mydata) {
                        //console.log("bulkstatus checked "+JSON.stringify(mydata));
                        if (mydata["status"] == 'ok') {
                            if (mydata['is_bulk_processing'] == 1 || mydata['is_bulk_processing'] == '1') {

                                is_processing = 1;
                                was_processing = 1;

                                var nb_processed = parseInt(mydata['number_processed']);
                                var totale_processed = parseInt(mydata['totale']);
                                var percentage_processed = 0;

                                if (nb_processed != undefined && nb_processed != 0 && !isNaN(nb_processed) && totale_processed != undefined && totale_processed != 0 && !isNaN(totale_processed)) {
                                    percentage_processed = Math.min(100, Math.ceil(nb_processed * 100 / totale_processed));

                                    document.getElementById('myModalBody_forbulkstatus').innerHTML = "<p>Inserting the valid devices is being processed...</p> By clicking 'Ok' you will be redirected to the main page, but the process will be running anyway.<p> " + percentage_processed + " % of your valid devices have been processed";
                                } else {
                                    document.getElementById('myModalBody_forbulkstatus').innerHTML = "<p>Inserting the valid devices is being processed...</p> By clicking 'Ok' you will be redirected to the main page, but the process will be running anyway.<p> ";
                                }
                                progress_modal.style.display = "block";
                                spin.style.display = "none";
                                progress_ok.style.display = "block";
                                progress_stop.style.display = "block";
                            } else {
                                is_processing = 0;
                                is_finished = mydata["is_finished"];
                            }
                        } else {
                            console.log("Error retrieving the bulkstatus " + mydata);
                        }
                    },
                    error: function (mydata) {
                        console.log("Failure in retrieving the bulkstatus " + JSON.stringify(mydata));
                        is_processing = 0;
                    }
                });
            }

        }, 3 * 1000);//each 3 seconds 
    }

    $("#selectModelDevice").click(function () {

        var nameOpt = document.getElementById('selectModelDevice').options;
        var selectednameOpt = document.getElementById('selectModelDevice').selectedIndex;
        var ownerSelect = document.getElementById('selectVisibilityDevice').options;
        var ownerOpt = document.getElementById('selectVisibilityDevice').selectedIndex;

        //Fatima3	 
        //if ((nameOpt[selectednameOpt].value !="custom")&&(nameOpt[selectednameOpt].value !="")) 
        if (nameOpt[selectednameOpt].value != "") {

            var gb_device = document.getElementById('inputNameDevice').value;
            var gb_latitude = document.getElementById('inputLatitudeDevice').value;
            var gb_longitude = document.getElementById('inputLongitudeDevice').value;

            // $("#addDeviceModal #selectModelDevice").val(nameOpt[selectednameOpt].value);

            //Fatima6
            $("#KeyOneDeviceUser").removeAttr('disabled');
            $("#KeyOneDeviceUser").removeAttr('disabled');

            if (nameOpt[selectednameOpt].value.indexOf("Raspberry") != -1 || nameOpt[selectednameOpt].value.indexOf("Arduino") != -1 || nameOpt[selectednameOpt].value.indexOf("sigfox") != -1 || nameOpt[selectednameOpt].value.indexOf("Sigfox") != -1) {

                $("#addNewDeviceGenerateKeyBtn").hide();
            } else {
                $("#addNewDeviceGenerateKeyBtn").show();
            }



            if (nameOpt[selectednameOpt].getAttribute("data_key") == "normal" && ownerSelect[ownerOpt].value == 'private') {

                //Fatima6 //Fatima3
                //$("#addNewDeviceGenerateKeyBtn").show();


                if ($("#KeyOneDeviceUser").val() == "" || nameOpt[selectednameOpt].value.indexOf("Raspberry") != -1 || nameOpt[selectednameOpt].value.indexOf("Arduino") != -1) {
                    $("#sigFoxDeviceUserMsg").val("");

                    gb_key1 = generateUUID();
                    gb_key2 = generateUUID();

                    $("#KeyOneDeviceUserMsg").html("");
                    $("#KeyTwoDeviceUserMsg").html("");

                    $("#sigFoxDeviceUserMsg").html("These keys have been generated automatically for your device. Keep track of them. Details on <a href=\"https://www.snap4city.org/drupal/node/76\">info</a>");

                    $("#KeyOneDeviceUser").val(gb_key1);
                    $("#KeyTwoDeviceUser").val(gb_key2);
                    //console.log("normal" +nameOpt[selectednameOpt].getAttribute("data_key")+gb_key1+gb_key2);							 
                }
            }
            if (nameOpt[selectednameOpt].getAttribute("data_key") == "special" && ownerSelect[ownerOpt].value == 'private') {
                //Fatima6 //Fatima3
                //$("#addNewDeviceGenerateKeyBtn").hide();

                if ($("#KeyOneDeviceUser").val() == "") {
                    $("#sigFoxDeviceUserMsg").html("Generate in your SigFox server the keys and report them here.  Details on <a href=\"https://www.snap4city.org/drupal/node/76\">info</a>");
                    $("#KeyOneDeviceUser").val("");
                    $("#KeyTwoDeviceUser").val("");
                    //console.log("special "+ nameOpt[selectednameOpt].getAttribute("data_key")+gb_key1+gb_key2);							 
                }
                // UserKey();
            }






            //console.log(nameOpt[selectednameOpt].value + " " + gb_device + " " + gb_longitude + " " + gb_latitude);

            if (nameOpt[selectednameOpt].value != "custom") {
                $.ajax({
                    url: "../api/model.php",
                    data: {
                        action: "get_model",
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
                            //console.log("maroc" + data.content.attributes);

                            var model = data.content.name;
                            var type = data.content.devicetype;
                            var kind = data.content.kind;
                            var producer = data.content.producer;
                            var mac = data.content.mac;
                            var frequency = data.content.frequency;
                            var contextbroker = data.content.contextbroker;
                            var protocol = data.content.protocol;
                            var format = data.content.format;
                            var myattributes = JSON.parse(data.content.attributes);
                            var k = 0;
                            var content = "";
                            // population of the value tab with the values taken from the db						
                            while (k < myattributes.length) {
                                //console.log(myattributes.length + " " +k); 
                                content += drawAttributeMenu(myattributes[k].value_name,
                                        myattributes[k].data_type, myattributes[k].value_type, myattributes[k].editable, myattributes[k].value_unit, myattributes[k].healthiness_criteria,
                                        myattributes[k].healthiness_value, myattributes[k].old_value_name,
                                        'addlistAttributes', indexValues, '');
                                k++;
                            }
                            $('#addlistAttributes').html(content);

                            $('#inputTypeDevice').val(data.content.devicetype);
                            $('#selectKindDevice').val(data.content.kind);
                            $('#inputProducerDevice').val(data.content.producer);
                            $('#inputFrequencyDevice').val(data.content.frequency);
                            $('#inputMacDevice').val(data.content.mac);
                            $('#selectContextBroker').val(data.content.contextbroker);
                            $('#selectProtocolDevice').val(data.content.protocol);
                            $('#selectFormatDevice').val(data.content.format);
                            addDeviceConditionsArray['inputTypeDevice'] = true;
                            checkDeviceType();
                            checkEditDeviceConditions();
                            addDeviceConditionsArray['inputFrequencyDevice'] = true;
                            checkFrequencyType();
                            checkEditDeviceConditions();


                        }
                    },
                    error: function (data) {
                        console.log("Ko result: " + JSON.stringify(data));
                        $('#addlistAttributes').html("");

                        $('#inputTypeDevice').val("");
                        $('#selectKindDevice').val("");
                        $('#inputProducerDevice').val("");
                        $('#inputFrequencyDevice').val("");
                        $('#inputMacDevice').val("");
                        $('#selectContextBroker').val("");
                        $('#selectProtocolDevice').val("");
                        $('#selectFormatDevice').val("");
                        alert("An error occured when reading the information about model. <br/> Try again or get in touch with the Snap4City Administrator<br/>");

                        // $("#addDeviceModal").modal('hide');								
                    }

                });
            }

            //Fatima6
            if (nameOpt[selectednameOpt].value.indexOf("Raspberry") != -1 || nameOpt[selectednameOpt].value.indexOf("Arduino") != -1) {

                $("#KeyOneDeviceUser").attr({'disabled': 'disabled'});
                $("#KeyTwoDeviceUser").attr({'disabled': 'disabled'});
            } else {
                $("#KeyOneDeviceUser").removeAttr('disabled');
                $("#KeyTwoDeviceUser").removeAttr('disabled');
            }

            /* 	
             
             $("#addDeviceModal").modal('show');
             // console.log(name);
             // $("#addDeviceModalBody").modal('show');
             $("#addDeviceLoadingMsg").hide();
             $("#addDeviceLoadingIcon").hide();
             $("#addDeviceOkMsg").hide();
             $("#addDeviceOkIcon").hide();
             $("#addDeviceKoMsg").hide();
             $("#addDeviceKoIcon").hide();
             */
        } else {
            $('#inputTypeDevice').val("");
            $('#selectKindDevice').val("");
            $('#inputProducerDevice').val("");
            $('#inputFrequencyDevice').val("");

            $('#inputMacDevice').val("");
            $('#selectContextBroker').val("");
            $('#selectProtocolDevice').val("");
            $('#selectFormatDevice').val("");
            gb_key1 = "";
            gb_key2 = "";
            $('#KeyOneDeviceUserMsg').html("");
            $('#KeyTwoDeviceUserMsg').html("");
            $('#KeyOneDeviceUserMsg').val("");
            $('#KeyTwoDeviceUserMsg').val("");
            // $('#addlistAttributes').html("");
            addDeviceConditionsArray['inputTypeDevice'] = false;
            checkDeviceType();
            checkEditDeviceConditions();
            addDeviceConditionsArray['inputFrequencyDevice'] = false;
            checkFrequencyType();
            checkEditDeviceConditions();

        }

    });

    //INSERT BULK DEVICES 

    $("#addSchemaTabDevice").off("click");
    $("#addSchemaTabDevice").on('click keyup', function () {
        console.log("#addSchemaTabDevice");

        //checkAtlistOneAttribute();
        $("#addSchemaTabDevice #addlistAttributes .row input:even").each(function () {
            checkValueNameM($(this));
        });
        checkEditDeviceConditions();
    });// add lines related to attributes			
    $("#addAttrBtn").off("click");
    $("#addAttrBtn").click(function () {
        //console.log("#addAttrBtn");							   
        content = drawAttributeMenu("", "", "", "", "", "", " ", " ", 'addlistAttributes', indexValues,'');
        indexValues = indexValues + 1;
        // addDeviceConditionsArray['addlistAttributes'] = true;
        //console.log("contenuto drawAttr" +content);
        $('#addlistAttributes').append(content);
        checkAtlistOneAttribute();
        $("#addSchemaTabDevice #addlistAttributes .row input:even").each(function () {
            checkValueNameM($(this));
        });
        checkEditDeviceConditions();
    });


    //DELETE DEVICE (DELETE FROM DB)  			

    // Delete lines related to attributes 

    $("#attrNameDelbtn").off("click");
    $("#attrNameDelbtn").on("click", function () {
        //console.log("#attrNameDelbtn");	
        $(this).parent('tr').remove();
    });

    $('#devicesTable button.delDashBtn').off('click');
    $('#devicesTable button.delDashBtn').click(function () {
        var id = $(this).attr('data-id');
        var contextBroker = $(this).attr("data-contextbroker");
        var uri = $(this).attr("data-uri");
        var status = $(this).attr("data-status1");

        $("#deleteDeviceModal div.modal-body").html('<div class="modalBodyInnerDiv"><span data-id = "' + id + '" data-contextBroker = "' + contextBroker + '"  data-uri ="' + uri + '" data-status1 = "' + status + '">Do you want to confirm deletion of device <b>' + id + '</b>?</span></div>');
        $("#deleteDeviceModal").modal('show');
    });



    $('#deleteDeviceConfirmBtn').off("click");
    $("#deleteDeviceConfirmBtn").click(function () {

        var id = $("#deleteDeviceModal span").attr("data-id");
        var contextbroker = $("#deleteDeviceModal span").attr("data-contextbroker");
        var uri = $("#deleteDeviceModal span").attr("data-uri");
        var status = $("#deleteDeviceModal span").attr("data-status1");
        //console.log("valori val "+id +" "+contextbroker + " " + status);

        $("#deleteDeviceModal div.modal-body").html("");
        $("#deleteDeviceCancelBtn").hide();
        $("#deleteDeviceConfirmBtn").hide();
        $("#deleteDeviceModal div.modal-body").append('<div id="deleteDeviceModalInnerDiv1" class="modalBodyInnerDiv"><h5>Device deletion in progress, please wait</h5></div>');
        $("#deleteDeviceModal div.modal-body").append('<div id="deleteDeviceModalInnerDiv2" class="modalBodyInnerDiv"><i class="fa fa-circle-o-notch fa-spin" style="font-size:36px"></i></div>');

        $.ajax({
            url: "../api/bulkDeviceLoad.php",
            data: {
                action: "delete_temporary",
                id: id,
                uri: uri,
                contextbroker: contextbroker,
                token: sessionToken,
                should_be_registered: "no"
            },
            type: "POST",
            datatype: "json",
            async: true,
            success: function (data) {
                //console.log(JSON.stringify(data));
                if (data["status"] === 'ko') {
                    $("#deleteDeviceModalInnerDiv1").html(data["msg"]);
                    $("#deleteDeviceModalInnerDiv2").html('<i class="fa fa-frown-o" style="font-size:42px"></i>');

                    setTimeout(function () {
                        $("#deleteDeviceModal").modal('hide');
                    }, 2000);
                } else if (data["status"] === 'ok') {
                    $("#deleteDeviceModalInnerDiv1").html('Device &nbsp; <b>' + id + '</b> &nbsp;deleted successfully');
                    $("#deleteDeviceModalInnerDiv2").html('<i class="fa fa-check" style="font-size:42px"></i>');

                    //console.log(status);
                    if (status == 'valid')
                        $('#dashboardTotActiveCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotActiveCnt .pageSingleDataCnt').html()) - 1);
                    else
                        $('#dashboardTotPermCnt .pageSingleDataCnt').html(parseInt($('#dashboardTotPermCnt .pageSingleDataCnt').html()) - 1);

                    setTimeout(function () {
                        fetch_data(true);
                        $("#deleteDeviceModal").modal('hide');

                        setTimeout(function () {
                            $("#deleteDeviceCancelBtn").show();
                            $("#deleteDeviceConfirmBtn").show();
                        }, 500);
                    }, 2000);
                }
            },
            error: function (data) {
                console.log(JSON.stringify(data));
                $("#deleteDeviceModalInnerDiv1").html(data["msg"]);
                $("#deleteDeviceModalInnerDiv2").html('<i class="fa fa-frown-o" style="font-size:42px"></i>');
                setTimeout(function () {
                    $("#deleteDeviceModal").modal('hide');
                }, 2000);
            }
        });
    });
    //DELETE ALL 
    $("#deleteAllBtn").off("click");
    $("#deleteAllBtn").on("click", function () {

        $("#deleteAllDevModal div.modal-body").html('<div class="modalBodyInnerDiv">Do you want to confirm the deletion of ALL the devices?</div>');
        $("#deleteAllDevModal").modal('show');
    });
    $('#deleteAllDevConfirmBtn').off("click");
    $("#deleteAllDevConfirmBtn").click(function () {
        $("#deleteAllDevModal div.modal-body").html("");
        $("#deleteAllDevCancelBtn").hide();
        $("#deleteAllDevConfirmBtn").hide();
        $("#deleteAllDevModal div.modal-body").append('<div id="deleteAllDevModalInnerDiv1" class="modalBodyInnerDiv"><h5>Deletion in progress, please wait</h5></div>');
        $("#deleteAllDevModal div.modal-body").append('<div id="deleteAllDevModalInnerDiv2" class="modalBodyInnerDiv"><i class="fa fa-circle-o-notch fa-spin" style="font-size:36px"></i></div>');

        $.ajax({
            url: "../api/bulkDeviceLoad.php",
            data: {
                action: "delete_all_temporary",
                //username: loggedUser, 
                token: sessionToken,
                //organization:organization
            },
            type: "POST",
            datatype: "json",
            async: true,
            success: function (data) {
                //console.log(JSON.stringify(data));
                if (data["status"] === 'ko') {
                    $("#deleteAllDevModalInnerDiv1").html(data["msg"]);
                    $("#deleteAllDevModalInnerDiv2").html('<i class="fa fa-frown-o" style="font-size:42px"></i>');

                    setTimeout(function () {
                        $("#deleteAllDevModal").modal('hide');
                    }, 2000);
                } else if (data["status"] === 'ok') {
                    $("#deleteAllDevModalInnerDiv1").html('Devices deleted successfully');
                    $("#deleteAllDevModalInnerDiv2").html('<i class="fa fa-check" style="font-size:42px"></i>');



                    $('#dashboardTotActiveCnt .pageSingleDataCnt').html(0);
                    $('#dashboardTotPermCnt .pageSingleDataCnt').html(0);

                    setTimeout(function () {
                        fetch_data(true);
                        $("#deleteAllDevModal").modal('hide');

                        setTimeout(function () {
                            $("#deleteAllDevCancelBtn").show();
                            $("#deleteAllDevConfirmBtn").show();
                        }, 500);
                    }, 2000);
                }
            },
            error: function (data) {
                console.log(JSON.stringify(data));
                if (data["msg"] != undefined) {
                    $("#deleteAllDevModalInnerDiv1").html(data["msg"]);
                } else {
                    $("#deleteAllDevModalInnerDiv1").html("Nothing is deleted");
                }
                $("#deleteAllDevModalInnerDiv2").html('<i class="fa fa-frown-o" style="font-size:42px"></i>');
                setTimeout(function () {
                    $("#deleteAllDevModal").modal('hide');
                    $("#deleteAllDevCancelBtn").show();
                    $("#deleteAllDevConfirmBtn").show();
                }, 2000);
            }
        });
    });

    //END DELETE ALL
    // EDIT DEVICE   


    // add lines related to attributes in case of edit
    $("#addAttrMBtn").off("click");
    $("#addAttrMBtn").click(function () {
        //console.log("#addAttrMBtn");					
        content = drawAttributeMenu("", "", "", "", "", "", "300", " ", 'addlistAttributesM', indexValues,'');
        indexValues = indexValues + 1;
        editDeviceConditionsArray['addlistAttributesM'] = true;
        $('#addlistAttributesM').append(content);
        checkEditAtlistOneAttribute();
        $("#editSchemaTabDevice #addlistAttributesM .row input:even").each(function () {
            //checkEditValueName($(this));
        });
        $("#editSchemaTabDevice #editlistAttributes .row input:even").each(function () {
            checkEditValueName($(this));
        });
        checkEditDeviceConditions();
    });

    $("#editSchemaTabDevice").off("click");
    $("#editSchemaTabDevice").on('click keyup', function () {
        console.log("#editSchemaTabDevice");

        //checkEditAtlistOneAttribute();
        $("#editSchemaTabDevice #addlistAttributesM .row input:even").each(function () {
            // checkEditValueName($(this));
        });
        $("#editSchemaTabDevice #editlistAttributes .row input:even").each(function () {
            //checkEditValueName($(this));
        });
        checkEditDeviceConditions();
    });



    $('#devicesTable tbody').on('click', 'button.editDashBtn', function () {

        document.getElementById('editlistAttributes').innerHTML = "";
        document.getElementById('addlistAttributesM').innerHTML = "";
        document.getElementById('deletedAttributes').innerHTML = "";

        $("#editDeviceModalBody").show();
        $('#editDeviceModalTabs').show();
        $("#editDeviceModalFooter").show();

        //$('#editDeviceModal div.modalCell').show();

        $('#editDeviceConfirmBtn').attr("disabled", false);

        $("#editDeviceLoadingMsg").hide();
        $("#editDeviceLoadingIcon").hide();
        $("#editDeviceOkMsg").hide();
        $("#editDeviceOkIcon").hide();
        $("#editDeviceKoMsg").hide();
        $("#editDeviceKoIcon").hide();

        $("#editDeviceModalLabel").html("Edit device - " + $(this).attr("data-id"));
        $("#editDeviceModal").modal('show');

        var id = $(this).attr('data-id');
        gb_old_id = id;
        var contextbroker = $(this).attr('data-contextbroker');
        gb_old_cb = contextbroker;
        var type = $(this).attr('data-devicetype');
        var kind = $(this).attr('data-kind');
        var uri = $(this).attr('data-uri');
        var protocol = $(this).attr('data-protocol');
        var format = $(this).attr('data-format');
        var macaddress = $(this).attr('data-macaddress');
        var model = $(this).attr('data-model');
        var producer = $(this).attr('data-producer');
        var edge_gateway_type = $(this).attr('data-edge_gateway_type');
        var edge_gateway_uri = $(this).attr('data-edge_gateway_uri');
        var latitude = $(this).attr('data-latitude');
        var longitude = $(this).attr('data-longitude');
        var frequency = $(this).attr('data-frequency');
        var visibility = $(this).attr('data-visibility');

        var service = $(this).attr('data-service');
        var servicepath = $(this).attr('data-servicepath');

        var key1 = $(this).attr('data-k1');
        var key2 = $(this).attr('data-k2');

        var subnature = $(this).attr('data-subnature');

        if (model == "custom")
            $("#editDeviceGenerateKeyBtn").show();
        else
            $("#editDeviceGenerateKeyBtn").hide();

        $('#inputNameDeviceM').val(id);
        $('#selectContextBrokerM').val(contextbroker);
        $('#inputTypeDeviceM').val(type);
        $('#selectKindDeviceM').val(kind);
        $('#inputUriDeviceM').val(uri);
        $('#selectProtocolDeviceM').val(protocol);
        $('#selectFormatDeviceM').val(format);
        $('#createdDateDeviceM').val($(this).parents('tr').attr('data-created'));
        $('#inputMacDeviceM').val(macaddress);
        $('#selectModelDeviceM').val(model);
        $('#inputProducerDeviceM').val(producer);
        $('#selectEdgeGatewayTypeM').val(edge_gateway_type);
        $('#inputEdgeGatewayUriM').val(edge_gateway_uri);
        $('#inputLatitudeDeviceM').val(latitude.substring(0, 8));
        $('#inputLongitudeDeviceM').val(longitude.substring(0, 8));
        $('#inputFrequencyDeviceM').val(frequency);
        $('#selectVisibilityDeviceM').val(visibility);

        if (protocol == 'ngsi w/MultiService') {
            $('#deviceService').val(service);
            $('#editInputServicePathDevice').val(servicepath);
        }


        $('#KeyOneDeviceUserM').val(key1);
        $('#KeyTwoDeviceUserM').val(key2);

        $('#selectSubnatureM').val(subnature);
        $('#selectSubnatureM').trigger('change');
        subnatureChanged(true, JSON.parse(atob($(this).attr("data-static-attributes"))));

        if ($("#isMobileTickM").is(":checked"))
            $("#positionMsgHintM").show();
        else
            $("#positionMsgHintM").hide();

        $('#selectContextBrokerM').attr("disabled", true);
        $('#selectProtocolDeviceM').attr("disabled", true);
        $('#selectFormatDeviceM').attr("disabled", true);
        $('#deviceService').attr("disabled", true);
        $('#editInputServicePathDevice').attr("disabled", true);
        $('#inputNameDeviceM').attr("disabled", true);
        $('#addAttrMBtn').hide();

        checkEditDeviceConditions();
        showEditDeviceModal();

        $('#editDeviceModal').show();

        // $("#editUserPoolsTable tbody").empty();

        // var indexValues = 0;
        // for (event_value of list_temporary_event_value) {
        // 	var content = "";
        // 	if (event_value.contextbroker == contextbroker && event_value.device == id) {
        // 		content = drawAttributeMenu(event_value.value_name,
        // 			event_value.data_type, event_value.value_type, event_value.editable, event_value.value_unit, event_value.healthiness_criteria,
        // 			event_value.healthiness_value, event_value.value_name, 'editlistAttributes', indexValues);
        // 		indexValues = indexValues + 1;
        // 		$('#editlistAttributes').append(content);
        // 		checkEditDeviceConditions();
        // 		$("#editSchemaTabDevice #editlistAttributes .row input:even").each(function () { checkEditValueName($(this)); });
        // 	}
        // }





        $.ajax({
            url: "../api/bulkDeviceUpdate.php",
            data: {
                action: "get_temporary_attributes",
                id: $(this).attr("data-id"),
                organization: organization,
                contextbroker: $(this).attr("data-contextBroker"),
                token: sessionToken,
                value: true,
                type: $(this).attr("data-devicetype"),
                service: $(this).attr("data-service"),
                servicePath: $(this).attr("data-servicepath"),
                version: 'v2'
            },
            type: "POST",
            async: true,
            dataType: 'json',
            success: function (mydata) {




                //console.log("contenent logg "+JSON.stringify(mydata['content']));

                var row = null;
                $("#editUserPoolsTable tbody").empty();
                myattributes = mydata['content'];
                myvalue = mydata["value"];
                content = "";
                k = 0;

                while (k < myattributes.length)
                {
                    // console.log(k); 
                    content = drawAttributeMenu(myattributes[k].value_name,
                            myattributes[k].data_type, myattributes[k].value_type, myattributes[k].editable, myattributes[k].value_unit, myattributes[k].healthiness_criteria,
                            myattributes[k].healthiness_value, myattributes[k].value_name, 'editlistAttributes', indexValues,'');

                    str = "#Value" + myattributes[k].value_name;


                    indexValues = indexValues + 1;
                    k++;
                    $('#editlistAttributes').append(content);




                    //checkEditDeviceConditions();
                    $("#editSchemaTabDevice #editlistAttributes .row input:even").each(function () {
                        //  checkEditValueName($(this));
                    });



                    let obj = JSON.stringify(myvalue[myattributes[k - 1].value_name]);
                    //.replace(/"/gm,'')
                    $(str).val(obj);

                    // $(str).val((myvalue[myattributes[k-1].value_name]).toString());

                }
            },
            error: function (data)
            {
                console.log("Get values pool KO");
                console.log(JSON.stringify(data));
                alert("Error in reading data from the database<br/> Please get in touch with the Snap4city Administrator");

                $('#inputNameDeviceM').val("");
                $('#selectContextBrokerM').val("");
                $('#inputTypeDeviceM').val("");
                $('#selectKindDeviceM').val("");
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
            }
        });




    });
    $('#editDeviceConfirmBtn').off("click");
    $("#editDeviceConfirmBtn").on('click', function () {

        mynewAttributes = [];
        num1 = document.getElementById('addlistAttributesM').childElementCount;

        for (var m = 0; m < num1; m++) {
            var newatt = {
                value_name: document.getElementById('addlistAttributesM').childNodes[m].childNodes[0].childNodes[0].childNodes[0].value.trim(),
                data_type: document.getElementById('addlistAttributesM').childNodes[m].childNodes[3].childNodes[0].childNodes[0].value.trim(),
                value_type: document.getElementById('addlistAttributesM').childNodes[m].childNodes[1].childNodes[0].childNodes[0].value.trim(),
                editable: '0',
                value_unit: document.getElementById('addlistAttributesM').childNodes[m].childNodes[2].childNodes[0].childNodes[0].value.trim(),
                healthiness_criteria: document.getElementById('addlistAttributesM').childNodes[m].childNodes[4].childNodes[0].childNodes[0].value.trim(),
                healthiness_value: document.getElementById('addlistAttributesM').childNodes[m].childNodes[5].childNodes[0].childNodes[0].value.trim()
            }
            mynewAttributes.push(newatt);
        }

        myAttributes = [];
        num = document.getElementById('editlistAttributes').childElementCount;
        for (var j = 0; j < num; j++) {
            var selectOpt_value_type = document.getElementById('editlistAttributes').childNodes[j].childNodes[1].childNodes[0].childNodes[0].options;
            var selectIndex_value_type = document.getElementById('editlistAttributes').childNodes[j].childNodes[1].childNodes[0].childNodes[0].selectedIndex;

            var selectOpt_data_type = document.getElementById('editlistAttributes').childNodes[j].childNodes[3].childNodes[0].childNodes[0].options;
            var selectIndex_data_type = document.getElementById('editlistAttributes').childNodes[j].childNodes[3].childNodes[0].childNodes[0].selectedIndex;

            var selectOpt_value_unit = document.getElementById('editlistAttributes').childNodes[j].childNodes[2].childNodes[0].childNodes[0].options;
            var selectIndex_value_unit = document.getElementById('editlistAttributes').childNodes[j].childNodes[2].childNodes[0].childNodes[0].selectedIndex;

            var selectOpt_hc = document.getElementById('editlistAttributes').childNodes[j].childNodes[4].childNodes[0].childNodes[0].options;
            var selectIndex_hc = document.getElementById('editlistAttributes').childNodes[j].childNodes[4].childNodes[0].childNodes[0].selectedIndex;

            //var selectOpt_edit = document.getElementById('editlistAttributes').childNodes[j].childNodes[4].childNodes[0].childNodes[0].options;
            //var selectIndex_edit = document.getElementById('editlistAttributes').childNodes[j].childNodes[4].childNodes[0].childNodes[0].selectedIndex;

            try {
                var dt = selectOpt_data_type[selectIndex_data_type].value
            } catch (err) {
                var dt = ""
            }
            ;
            try {
                var vt = selectOpt_value_type[selectIndex_value_type].value
            } catch (err) {
                var vt = ""
            }
            ;
            try {
                var vu = selectOpt_value_unit[selectIndex_value_unit].value
            } catch (err) {
                var vu = ""
            }
            ;

            var att = {
                value_name: document.getElementById('editlistAttributes').childNodes[j].childNodes[0].childNodes[0].childNodes[0].value.trim(),
                data_type: dt,
                value_type: vt,
                editable: '0',
                value_unit: vu,
                healthiness_criteria: selectOpt_hc[selectIndex_hc].value,
                healthiness_value: document.getElementById('editlistAttributes').childNodes[j].childNodes[5].childNodes[0].childNodes[0].value.trim(),
                old_value_name: getText(document.getElementById('editlistAttributes').childNodes[j].childNodes[7].childNodes[0].childNodes[0])
            };
            myAttributes.push(att);

        }

        mydeletedAttributes = [];
        numDel = document.getElementById('deletedAttributes').childElementCount;
        for (var j = 0; j < numDel; j++) {
            var selectOpt_value_type = document.getElementById('deletedAttributes').childNodes[j].childNodes[1].childNodes[0].childNodes[0].options;
            var selectIndex_value_type = document.getElementById('deletedAttributes').childNodes[j].childNodes[1].childNodes[0].childNodes[0].selectedIndex;

            var selectOpt_data_type = document.getElementById('deletedAttributes').childNodes[j].childNodes[3].childNodes[0].childNodes[0].options;
            var selectIndex_data_type = document.getElementById('deletedAttributes').childNodes[j].childNodes[3].childNodes[0].childNodes[0].selectedIndex;

            var selectOpt_value_unit = document.getElementById('deletedAttributes').childNodes[j].childNodes[2].childNodes[0].childNodes[0].options;
            var selectIndex_value_unit = document.getElementById('deletedAttributes').childNodes[j].childNodes[2].childNodes[0].childNodes[0].selectedIndex;

            var selectOpt_hc = document.getElementById('deletedAttributes').childNodes[j].childNodes[4].childNodes[0].childNodes[0].options;
            var selectIndex_hc = document.getElementById('deletedAttributes').childNodes[j].childNodes[4].childNodes[0].childNodes[0].selectedIndex;

//            var selectOpt_edit = document.getElementById('deletedAttributes').childNodes[j].childNodes[4].childNodes[0].childNodes[0].options;
//            var selectIndex_edit = document.getElementById('deletedAttributes').childNodes[j].childNodes[4].childNodes[0].childNodes[0].selectedIndex;

            var att = {
                value_name: document.getElementById('deletedAttributes').childNodes[j].childNodes[0].childNodes[0].childNodes[0].value.trim(),
                data_type: selectOpt_data_type[selectIndex_data_type].value,
                value_type: selectOpt_value_type[selectIndex_value_type].value,
                editable: '0',
                value_unit: (selectOpt_value_unit[selectIndex_value_unit] == undefined) ? "" : selectOpt_value_unit[selectIndex_value_unit].value,
                healthiness_criteria: selectOpt_hc[selectIndex_hc].value,
                healthiness_value: document.getElementById('deletedAttributes').childNodes[j].childNodes[5].childNodes[0].childNodes[0].value.trim(),
                old_value_name: document.getElementById('deletedAttributes').childNodes[j].childNodes[7].childNodes[0].childNodes[0]
            };
            mydeletedAttributes.push(att);
        }

        document.getElementById('editlistAttributes').innerHTML = "";
        document.getElementById('addlistAttributesM').innerHTML = "";
        document.getElementById('deletedAttributes').innerHTML = "";

        $("#editDeviceModalTabs").hide();
        $('#editDeviceModal div.modalCell').hide();
        $("#editDeviceModalFooter").hide();
        $("#addAttrMBtn").hide();
        $("#editDeviceGenerateKeyBtn").hide();

        $('#editDeviceLoadingMsg').show();
        $('#editDeviceLoadingIcon').show();

        var arrayAttributes = [];
        for (var i = 0; i < myAttributes.length; i++) {
            arrayAttributes.push(myAttributes[i]);
        }
        for (var i = 0; i < mynewAttributes.length; i++) {
            arrayAttributes.push(mynewAttributes[i]);
        }

        //UPDATE FUNCTION
        var updatedDevice = {"contextbroker": $('#selectContextBrokerM').val(), "name": $('#inputNameDeviceM').val(), "devicetype": $('#inputTypeDeviceM').val(), "model": $('#selectModelDeviceM').val(), "macaddress": $('#inputMacDeviceM').val(), "frequency": $('#inputFrequencyDeviceM').val(), "kind": $('#selectKindDeviceM').val(), "protocol": $('#selectProtocolDeviceM').val(), "format": $('#selectFormatDeviceM').val(), "service": $('#deviceService').val(), "servicepath": $('#editInputServicePathDevice').val(), "latitude": $('#inputLatitudeDeviceM').val(), "longitude": $('#inputLongitudeDeviceM').val(), "visibility": $('#selectVisibilityDeviceM').val(), "k1": $('#KeyOneDeviceUserM').val(), "k2": $('#KeyTwoDeviceUserM').val(), "subnature": $('#selectSubnatureM').val(), "static_attributes": JSON.stringify(retrieveStaticAttributes("editlistStaticAttributes", false, "isMobileTickM")), "producer": $('#inputProducerDeviceM').val(), "edge_gateway_type": $('#selectEdgeGatewayTypeM').val(), "edge_gateway_uri": $('#inputEdgeGatewayUriM').val(), "deviceValues": arrayAttributes};

        var device_status = 'invalid';
        var verify = verifyDevice(updatedDevice);
        if (verify.isvalid) {
            device_status = 'valid';
        }

        var service = $('#deviceService').val();
        var servicePath = $('#editInputServicePathDevice').val();

        if ($('#selectProtocolDeviceM').val() === "ngsi w/MultiService") {
            if (servicePath[0] !== "/" || servicePath === "")
                servicePath = "/" + servicePath;
            if (servicePath[servicePath.length - 1] === "/" && servicePath.length > 1)
                servicePath = servicePath.substr(0, servicePath.length - 1);
        }

        $.ajax({
            url: "../api/bulkDeviceLoad.php",
            data: {
                action: "update",
                newattributes: JSON.stringify(mynewAttributes),
                attributes: JSON.stringify(myAttributes),
                deleteattributes: JSON.stringify(mydeletedAttributes),
                old_id: gb_old_id,
                old_cb: gb_old_cb,
                status: device_status,
                validity_msg: verify.message,
                edge_gateway_type: updatedDevice.edge_gateway_type,
                edge_gateway_uri: updatedDevice.edge_gateway_uri,
                id: updatedDevice.name,
                type: updatedDevice.devicetype,
                kind: updatedDevice.kind,
                contextbroker: $('#selectContextBrokerM').val(),
                uri: $('#inputUriDeviceM').val(),
                protocol: updatedDevice.protocol,
                format: updatedDevice.format,
                service: updatedDevice.service,
                servicepath: updatedDevice.servicepath,
                mac: updatedDevice.macaddress,
                model: updatedDevice.model,
                producer: updatedDevice.producer,
                latitude: updatedDevice.latitude,
                longitude: updatedDevice.longitude,
                visibility: updatedDevice.visibility,
                frequency: updatedDevice.frequency,
                k1: updatedDevice.k1,
                k2: updatedDevice.k2,
                subnature: updatedDevice.subnature,
                static_attributes: updatedDevice.static_attributes,
                service: service,
                servicePath: servicePath,
                token: sessionToken
            },
            type: "POST",
            async: true,
            success: function (data) {
                if (data["status"] === 'ko') {
                    console.log("Error editing Device type");
                    console.log(data);
                    $('#editDeviceLoadingMsg').hide();
                    $('#editDeviceLoadingIcon').hide();
                    $('#editDeviceLoadingIcon').hide();
                    $('#editDeviceKoMsg').show();
                    $('#editDeviceKoIcon').show();

                    setTimeout(function () {
                        $('#editDeviceModal').modal('hide');
                        fetch_data(true);
                        setTimeout(function () {
                            $('#editDeviceModal').hide();
                            setTimeout(updateDeviceTimeout, 100);

                        }, 100);
                    }, 100);
                } else if (data["status"] === 'ok') {

                    $('#editDeviceLoadingMsg').hide();
                    $('#editDeviceLoadingIcon').hide();
                    $('#editDeviceOkMsg').show();
                    $('#editDeviceOkIcon').show();

                    $("#editDeviceModalInnerDiv1").html('Device &nbsp; successfully Updated');
                    $("#editDeviceModalInnerDiv2").html('<i class="fa fa-check" style="font-size:42px"></i>');

                    setTimeout(function () {
                        $('#editDeviceModal').modal('hide');
                        fetch_data(true);

                        setTimeout(function () {
                            // $('#addDeviceOkMsg').hide();
                            // $('#addDeviceOkIcon').hide();
                            $('#editDeviceOkMsg').hide();
                            $('#editDeviceOkIcon').hide();

                            $('#inputNameDevice').val("");
                            $('#inputTypeDevice').val("");
                            $('#selectKindDevice').val("");
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
                            $('#selectVisibilityDevice').val();
                            $('#inputFrequencyDevice').val();
                            $('#deviceService').val("");
                            $('#editInputServicePathDevice').val("");
                            $('#editDeviceModal').hide();
                            setTimeout(updateDeviceTimeout, 100);

                        }, 100);
                    }, 100);

                    //------------to verify----------//
                    var attributesToverify = myAttributes;
                    for (var i = 0; i < mynewAttributes.length; i++) {
                        attributesToverify.push(mynewAttributes[i]);
                    }
                    for (var i = 0; i < mydeletedAttributes.length; i++) {
                        for (var j = 0; j < attributesToverify.length; j++) {
                            if (mydeletedAttributes[i] == attributesToverify[j]) {
                                attributesToverify.splice(j, 1);
                                break;
                            }
                        }
                    }
                    // location.reload();
                } else {
                    console.log(data);
                }

            },
            error: function (data) {

                console.log("Ko result: " + JSON.stringify(data));
                $("#editDeviceKoModalInnerDiv1").html(data["msg"]);
                $("#editDeviceKoModal").modal('show');
                // $("#editDeviceModalUpdating").hide();
                $("#editDeviceModalBody").show();
                $("#editDeviceModalFooter").show();

                $('#inputNameDevice').val("");
                $('#inputTypeDevice').val("");
                $('#selectKindDevice').val("");
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
                $('#selectVisibilityDevice').val();
                $('#inputFrequencyDevice').val();
                $('#deviceService').val("");
                $('#editInputServicePathDevice').val("");
                $('#editDeviceModal').hide();
                setTimeout(updateDeviceTimeout, 3000);
                // location.reload();


            }
        });
    });



    $("#editDeviceCancelBtn").off("click");
    $("#editDeviceCancelBtn").on('click', function () {

        document.getElementById('editlistAttributes').innerHTML = "";
        document.getElementById('addlistAttributesM').innerHTML = "";
        document.getElementById('deletedAttributes').innerHTML = "";
    });

    $("#updateAllCancelBtn").off("click");
    $("#updateAllCancelBtn").on('click', function () {
        $('#ifBlockTable tbody').html("");
        $('#decisionBlockTable tbody').html("");

        $('#devicePreviewTable').DataTable().destroy();
        $('#devicePreviewTable tbody').html("");
        $("#devicesFound").html("0 devices founded");
        devicenamesArray['if'] = 0;
        devicenamesArray['then'] = 0;

        $('#updateMultipleDeviceModal').modal('hide');
    });

    $("#updateAllValuesCancelBtn").off("click");
    $("#updateAllValuesCancelBtn").on('click', function () {
        $('#ifBlockTableValue tbody').html("");
        $('#decisionBlockTableValue tbody').html("");

        $('#valuesPreviewTable').DataTable().destroy();
        $('#valuesPreviewTable tbody').html("");
        $("#valueFound").html("0 values founded");
        valueNamesArray['if'] = 0;
        valueNamesArray['then'] = 0;
        $('#updateMultipleDeviceModal1').modal('hide');

        // location.reload();
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


    // Update Multiple Fields 	 



    $("#updateMultipleModalBtn").off("click");
    $("#updateMultipleModalBtn").click(function () {

        $('#ifBlockTable tbody').html("");
        $('#decisionBlockTable tbody').html("");

        $('#devicePreviewTable').DataTable().destroy();
        $('#devicePreviewTable tbody').html("");
        $("#devicesFound").html("0 devices founded");
        devicenamesArray['if'] = 0;
        devicenamesArray['then'] = 0;

        $('#updateMultipleDeviceModal').modal('show');
        $('#updateMultipleDeviceModal1').modal('hide');


        var row = $('<tr class="ifrow"><td><h3><span class="label label-danger">If</span></h3></td><td class="fieldTd"><select class="fieldIf"><option value="empty">--Select an option--</option><option value="contextBroker" selected>Contextbroker</option><option value="id">Device name</option><option value="deviceType">Device Type</option><option value="model">Model</option><option value="producer">Producer</option><option value="frequency">Frequency</option><option value="kind">Kind</option><option value="protocol">Protocol</option><option value="format">Format</option><option value="latitude">Latitude</option><option value="longitude">Longitude</option><option value="macaddress">Mac address</option><option value="k1">Key1</option><option value="k2">Key2</option></select></td><td class="fieldEqual"><select class="fieldSelectEqual"><option value="IsEqual">Is Equal</option><option value="IsNotEqual">Is Not Equal</option><option value="IsNull">Is NULL</option><option value="Contains">Contains</option></select></td><td class="fieldName"> </td><td class="minusDevice"><i class="fa fa-minus"></i></td></tr>');



        $('#ifBlockTable tbody').append(row);
        devicenamesArray['if'] = devicenamesArray['if'] + 1;
        var rowIndex = row.index();
        var fieldIf = document.getElementById('ifBlockTable').tBodies[0].rows.item(rowIndex).cells.item(1).childNodes[0].value;
        getFields(fieldIf, rowIndex, 'ifBlockTable', '');



        $(document).ready(function () {
            row.find('i.fa-minus').off("click");
            row.find('i.fa-minus').on("click", function () {
                var rowIndex = $(this).parents('tr').index();
                $('#ifBlockTable tbody tr').eq(rowIndex).remove();

                if (rowIndex == 0 && document.getElementById('ifBlockTable').tBodies[0].rows.item(0) != null) {
                    document.getElementById('ifBlockTable').tBodies[0].rows.item(0).childNodes[0].childNodes[0].innerHTML = "<span class=\"label label-danger\">If</span>";
                }

                devicenamesArray['if'] = devicenamesArray['if'] - 1;
                checkUpdateButton();
                getAffectedRows();

                ifPages.splice(rowIndex, 1);
                $('#authorizedPagesJson').val(JSON.stringify(ifPages));
            });
        });



        var row2 = $('<tr class="ifrow"><td><h3><span class="label label-danger">AND</span></h3></td><td class="fieldTd"><select class="fieldIf"><option value="empty">--Select an option--</option><option value="contextBroker">Contextbroker</option><option value="id" >Device name</option><option value="deviceType" selected>Device Type</option><option value="model">Model</option><option value="producer">Producer</option><option value="frequency">Frequency</option><option value="kind">Kind</option><option value="protocol">Protocol</option><option value="format">Format</option><option value="latitude">Latitude</option><option value="longitude">Longitude</option><option value="macaddress">Mac address</option><option value="k1">Key1</option><option value="k2">Key2</option></select></td><td class="fieldEqual"><select class="fieldSelectEqual"><option value="IsEqual">Is Equal</option><option value="IsNotEqual">Is Not Equal</option><option value="IsNull">Is NULL</option><option value="Contains">Contains</option></select></td><td class="fieldName"> </td><td class="minusDevice"><i class="fa fa-minus"></i></td></tr>');
        $('#ifBlockTable tbody').append(row2);
        devicenamesArray['if'] = devicenamesArray['if'] + 1;
        var rowIndex = row2.index();
        var fieldIf = document.getElementById('ifBlockTable').tBodies[0].rows.item(rowIndex).cells.item(1).childNodes[0].value;
        getFields(fieldIf, rowIndex, 'ifBlockTable', '');

        $(document).ready(function () {
            row2.find('i.fa-minus').off("click");
            row2.find('i.fa-minus').on("click", function () {
                var rowIndex = $(this).parents('tr').index();
                $('#ifBlockTable tbody tr').eq(rowIndex).remove();

                if (rowIndex == 0 && document.getElementById('ifBlockTable').tBodies[0].rows.item(0) != null) {
                    document.getElementById('ifBlockTable').tBodies[0].rows.item(0).childNodes[0].childNodes[0].innerHTML = "<span class=\"label label-danger\">If</span>";
                }

                devicenamesArray['if'] = devicenamesArray['if'] - 1;
                checkUpdateButton();
                getAffectedRows();

                ifPages.splice(rowIndex, 1);
                $('#authorizedPagesJson').val(JSON.stringify(ifPages));
            });
        });


        checkUpdateButton();

    });


    $("#suggestionsButton").off("click");
    $("#suggestionsButton").click(function () {
        $('#suggestModifications').modal('show');
        if (gb_first_opening == 0) {
            findAssociationRules();
            gb_first_opening = 1;
        }
    });

    $("#skipSuggestion").click(function () {
        viewRules();
        getAffectedRulesValues();

    });

    $("#skipAll").click(function () {
        $('#suggestModifications').modal('hide');
    });

    $('#suggestionConfirm').off("click");
    $('#suggestionConfirm').on("click", function () {

        var num1 = document.getElementById('ifBlockSuggestions').tBodies[0].childElementCount;
        var num2 = document.getElementById('decisionBlockSuggestions').tBodies[0].childElementCount;

        if (num1 != 0 & num2 != 0) {
            var attributesIf = [];
            for (var m = 0; m < num1; m++) {
                //var attribute= document.getElementById('ifBlockTable').rows[m].cells[1].selectedIndex;
                var fieldIf = document.getElementById('ifBlockSuggestions').tBodies[0].rows.item(m).cells.item(1).childNodes[0].value;
                var operatorIf = document.getElementById('ifBlockSuggestions').tBodies[0].rows.item(m).cells.item(2).childNodes[0].value;
                var valueIf = document.getElementById('ifBlockSuggestions').tBodies[0].childNodes[m].childNodes[3].childNodes[0].textContent;

                if (valueIf.localeCompare("Empty") == 0) {
                    valueIf = "";
                }

                var newIf = {"field": fieldIf, "operator": operatorIf, "value": valueIf};
                attributesIf.push(newIf);

            }
            var attributesThen = [];

            for (var m = 0; m < num2; m++) {
                var fieldsThen = document.getElementById('decisionBlockSuggestions').tBodies[0].rows.item(m).cells.item(1).childNodes[0].value;
                var valueThen = document.getElementById('decisionBlockSuggestions').tBodies[0].childNodes[m].childNodes[2].childNodes[0].textContent;

                if (valueThen.localeCompare("Empty") == 0) {
                    valueThen = "";
                }
                var newThen = {"field": fieldsThen, "valueThen": valueThen};
                //console.log("newThen "+ JSON.stringify(newThen));
                attributesThen.push(newThen);
            }

            $.ajax({
                url: "../api/associationRulesApi.php",
                data: {
                    action: "apply_rules",
                    //username: loggedUser,
                    //organization:organization,
                    token: sessionToken,
                    attributesIf: JSON.stringify(attributesIf),
                    attributesThen: JSON.stringify(attributesThen)
                },
                dataType: 'json',
                type: "POST",
                async: true,
                success: function (myData) {
                    //console.log("suggestion apply suiccess "+ JSON.stringify(mydata));	

                    if (myData['status'] == 'ok') {

                        let mex = "Devices has been correctly updated.";
                        $('#bulkUpdateModalInnerDiv').html(mex);
                        $("#bulkUpdateModal").modal('show');
                        $('#updateMultipleDeviceModal').hide();

                    } else if (myData['status'] == 'ko') {
                        $("#bulkUpdateFaliure").modal('show');
                        $('#updateMultipleDeviceModal').hide();

                    }
                },
                error: function (myData) {
                    console.log("suggestion apply error " + JSON.stringify(mydata));
                    $("#bulkUpdateFaliure").modal('show');
                    $('#updateMultipleDeviceModal').hide();

                    $('#bulkUpdateFaliure').html(myData["msg"]);

                }
            });
        }
    });

    /************ update all devices  */

    $('#addifBlockBtn').off("click");
    $('#addifBlockBtn').click(function () {

        if ($('#ifBlockTable tbody tr').length == 0) {
            var row = $('<tr class="ifrow"><td><h3><span class="label label-danger">If</span></h3></td><td class="fieldTd"><select class="fieldIf"><option value="empty">--Select an option--</option><option value="contextBroker">Contextbroker</option><option value="id">Device name</option><option value="deviceType">Device Type</option><option value="model">Model</option><option value="producer">Producer</option><option value="frequency">Frequency</option><option value="kind">Kind</option><option value="protocol">Protocol</option><option value="format">Format</option><option value="latitude">Latitude</option><option value="longitude">Longitude</option><option value="macaddress">Mac address</option><option value="k1">Key1</option><option value="k2">Key2</option></select></td><td class="fieldEqual"><select class="fieldSelectEqual"><option value="IsEqual">Is Equal</option><option value="IsNotEqual">Is Not Equal</option><option value="IsNull">Is NULL</option><option value="Contains">Contains</option></select></td><td class="fieldName"> </td><td class="minusDevice"><i class="fa fa-minus"></i></td></tr>');
        } else {
            var row = $('<tr class="ifrow"><td><h3><span class="label label-danger">AND</span></h3></td><td class="fieldTd"><select class="fieldIf"><option value="empty">--Select an option--</option><option value="contextBroker">Contextbroker</option><option value="id">Device name</option><option value="deviceType">Device Type</option><option value="model">Model</option><option value="producer">Producer</option><option value="frequency">Frequency</option><option value="kind">Kind</option><option value="protocol">Protocol</option><option value="format">Format</option><option value="latitude">Latitude</option><option value="longitude">Longitude</option><option value="macaddress">Mac address</option><option value="k1">Key1</option><option value="k2">Key2</option></select></td><td class="fieldEqual"><select class="fieldSelectEqual"><option value="IsEqual">Is Equal</option><option value="IsNotEqual">Is Not Equal</option><option value="IsNull">Is NULL</option><option value="Contains">Contains</option></select></td><td class="fieldName"> </td><td class="minusDevice"><i class="fa fa-minus"></i></td></tr>');
        }

        $('#ifBlockTable tbody').append(row);

        devicenamesArray['if'] = devicenamesArray['if'] + 1;
        checkUpdateButton();

        var rowIndex = row.index();

        row.find('a').editable({
            emptytext: "Empty",
            display: function (value, response) {
                if (value.length > 35) {
                    $(this).html(value.substring(0, 32) + "...");
                } else {
                    $(this).html(value);
                }
            }
        });

        ifPages[rowIndex] = null;
        $('#authorizedPagesJson').val(JSON.stringify(ifPages));

        row.find('i.fa-minus').off("click");
        row.find('i.fa-minus').click(function () {
            var rowIndex = $(this).parents('tr').index();
            $('#ifBlockTable tbody tr').eq(rowIndex).remove();

            if (rowIndex == 0 && document.getElementById('ifBlockTable').tBodies[0].rows.item(0) != null) {
                document.getElementById('ifBlockTable').tBodies[0].rows.item(0).childNodes[0].childNodes[0].innerHTML = "<span class=\"label label-danger\">If</span>";
            }

            devicenamesArray['if'] = devicenamesArray['if'] - 1;
            checkUpdateButton();
            getAffectedRows();

            ifPages.splice(rowIndex, 1);
            $('#authorizedPagesJson').val(JSON.stringify(ifPages));
        });

        row.find('a.toBeEdited').off("save");
        row.find('a.toBeEdited').on('save', function (e, params) {
            var rowIndex = $(this).parents('tr').index();
            ifPages[rowIndex] = params.newValue;
            $('#authorizedPagesJson').val(JSON.stringify(ifPages));
        });


    });


    $('#addDecisionBlockBtn').off("click");
    $('#addDecisionBlockBtn').click(function () {
        var row = $('<tr><td><h3><span class="label label-success">Then</span></h3></td><td class="thenTd"><select class="thenSelect"><option value="empty">--Select an option--</option><option value="contextbroker">Contextbroker</option><option value="deviceType">Device Type</option><option value="model">Model</option><option value="producer">Producer</option><option value="frequency">Frequency</option><option value="kind">Kind</option><option value="protocol">Protocol</option><option value="format">Format</option><option value="latitude">Latitude</option><option value="longitude">Longitude</option><option value="macaddress">Mac address</option><option value="k1">Key1</option><option value="k2">Key2</option></select></td><td></td><td><i class="fa fa-minus"></i></td></tr>');
        $('#decisionBlockTable tbody').append(row);

        devicenamesArray['then'] = devicenamesArray['then'] + 1;
        checkUpdateButton();

        var rowIndex = row.index();

        row.find('a').editable({
            emptytext: "Empty",
            display: function (value, response) {
                if (value.length > 35) {
                    $(this).html(value.substring(0, 32) + "...");
                } else {
                    $(this).html(value);
                }
            }
        });

        ifPages[rowIndex] = null;
        $('#authorizedPagesJson').val(JSON.stringify(ifPages));

        row.find('i.fa-minus').off("click");
        row.find('i.fa-minus').click(function () {

            var rowIndex = $(this).parents('tr').index();
            $('#decisionBlockTable tbody tr').eq(rowIndex).remove();

            devicenamesArray['then'] = devicenamesArray['then'] - 1;

            checkUpdateButton();

            ifPages.splice(rowIndex, 1);
            $('#authorizedPagesJson').val(JSON.stringify(ifPages));
        });

        row.find('a.toBeEdited').off("save");
        row.find('a.toBeEdited').on('save', function (e, params) {
            var rowIndex = $(this).parents('tr').index();
            ifPages[rowIndex] = params.newValue;
            $('#authorizedPagesJson').val(JSON.stringify(ifPages));
        });
    });

    function checkUpdateButton() {

        var totIf = document.getElementById('ifBlockTable').tBodies[0].childElementCount;
        var totThen = document.getElementById('decisionBlockTable').tBodies[0].childElementCount;
        if (devicenamesArray['if'] > 0 & devicenamesArray['then'] > 0) {
            for (var i = 0; i < totIf; i++) {
                let valueIf = document.getElementById('ifBlockTable').tBodies[0].rows.item(i).cells.item(1).childNodes[0].value;
                if (valueIf != "empty") {
                    for (j = 0; j < totThen; j++) {
                        let valueThen = document.getElementById('decisionBlockTable').tBodies[0].rows.item(j).cells.item(1).childNodes[0].value;
                        if (valueThen != "empty") {
                            $("#updateAllConfirmBtn").attr("disabled", false);
                            break;
                        }
                    }

                }
            }
        } else {
            $("#updateAllConfirmBtn").attr("disabled", true);
        }
    }

    $(document).on({
        change: function () {

            var rowIndex = $(this).parents('tr').index();
            //console.log("row index" + rowIndex);
            var fieldIf = document.getElementById('ifBlockTable').tBodies[0].rows.item(rowIndex).cells.item(1).childNodes[0].value;
            getFields(fieldIf, rowIndex, 'ifBlockTable', 0);
            checkUpdateButton();

        }
    }, '.fieldTd select');

    $(document).on({
        change: function () {
            var rowIndex = $(this).parents('tr').index();
            var fieldsThen = document.getElementById('decisionBlockTable').tBodies[0].rows.item(rowIndex).cells.item(1).childNodes[0].value;
            getFields(fieldsThen, rowIndex, "decisionBlockTable", 0);
            checkUpdateButton();

        }
    }, '.thenTd select');

    $(document).on({
        input: function () {
            getAffectedRows();

        },
    }, '.fieldName input');

    $(document).on({
        change: function () {
            getAffectedRows();
        },
    }, '.fieldName select');

    $(document).on({
        change: function () {
            getAffectedRows();
        },
    }, '.fieldEqual select');

    function getAffectedRows() {
        var num1 = document.getElementById('ifBlockTable').tBodies[0].childElementCount;
        var attributesIf = [];

        for (var m = 0; m < num1; m++) {
            //var attribute= document.getElementById('ifBlockTable').rows[m].cells[1].selectedIndex;

            var fieldIf = document.getElementById('ifBlockTable').tBodies[0].rows.item(m).cells.item(1).childNodes[0].value;
            var operatorIf = document.getElementById('ifBlockTable').tBodies[0].rows.item(m).cells.item(2).childNodes[0].value
            var valueIf = document.getElementById('ifBlockTable').tBodies[0].childNodes[m].childNodes[3].childNodes[0].value;

            //params.newValue
            if (valueIf != undefined && valueIf.localeCompare("Empty") == 0) {
                valueIf = "";
            }

            var newIf = {"field": fieldIf, "operator": operatorIf, "value": valueIf};
            //console.log("newIf "+ JSON.stringify(newIf));
            attributesIf.push(newIf);
        }

        var attributesThen = [];
        var num2 = document.getElementById('decisionBlockTable').tBodies[0].childElementCount;
        for (var m = 0; m < num2; m++) {
            var fieldsThen = document.getElementById('decisionBlockTable').tBodies[0].rows.item(m).cells.item(1).childNodes[0].value;
            if (fieldsThen != "empty") {
                var operatorThen = document.getElementById('decisionBlockTable').tBodies[0].rows.item(m).cells.item(2).childNodes[0].innerHTML;

                var newThen = {"field": fieldsThen, "operator": operatorThen};
                attributesThen.push(newThen);
            }
        }


        $.ajax({
            url: "../api/bulkDeviceUpdate.php",
            data: {
                action: "get_affected_devices_count",
                //username: loggedUser,
                //organization:organization,
                attributesIf: JSON.stringify(attributesIf),
                token: sessionToken
                        //attributesThen: JSON.stringify(attributesThen)	    
            },
            dataType: 'json',
            type: "POST",
            async: true,
            success: function (myData) {

                //console.log("data success "+ myData['content']);
                $("#devicesFound").html(myData['content'] + " devices founded");

                if (attributesIf.length == 0) {
                    buildPreview(JSON.stringify(attributesIf), true);
                } else {
                    buildPreview(JSON.stringify(attributesIf), previewValuesFirstLoad);
                    previewValuesFirstLoad = true;
                }
                buildPreview(JSON.stringify(attributesIf), previewFirstLoad);
                previewFirstLoad = true;

                //	document.getElementById('devicesFound').value = myData['content'] + " devices found";
            },
            error: function (myData) {
                console.log(JSON.stringify(myData));
                $("#devicesFound").html("0 devices founded");
                console.log("data faliure" + myData['msg']);
            }
        });//end of ajax get_affected
    }

    function healthinessValueIsPresent(id) {
        var num1 = document.getElementById(id).tBodies[0].childElementCount;

        for (let i = 0; i < num1 - 1; i++) {
            var fieldIf = document.getElementById(id).tBodies[0].rows.item(i).cells.item(1).childNodes[0].value;
            if (fieldIf.localeCompare("healthiness_crieria") == 0) {
                return 1;
            }
        }
        return 0;

    }



    $("#updateAllConfirmBtn").off("click");
    $("#updateAllConfirmBtn").on("click", function () {
        var num1 = document.getElementById('ifBlockTable').tBodies[0].childElementCount;
        var num2 = document.getElementById('decisionBlockTable').tBodies[0].childElementCount;

        if (num1 != 0 & num2 != 0) {
            var attributesIf = [];
            for (var m = 0; m < num1; m++) {
                //var attribute= document.getElementById('ifBlockTable').rows[m].cells[1].selectedIndex;
                var fieldIf = document.getElementById('ifBlockTable').tBodies[0].rows.item(m).cells.item(1).childNodes[0].value;
                var operatorIf = document.getElementById('ifBlockTable').tBodies[0].rows.item(m).cells.item(2).childNodes[0].value
                var valueIf = document.getElementById('ifBlockTable').tBodies[0].childNodes[m].childNodes[3].childNodes[0].value;

                if (valueIf.localeCompare("Empty") == 0) {
                    valueIf = "";
                }

                var newIf = {"field": fieldIf, "operator": operatorIf, "value": valueIf};
                attributesIf.push(newIf);
            }
            var attributesThen = [];

            for (var m = 0; m < num2; m++) {
                var fieldsThen = document.getElementById('decisionBlockTable').tBodies[0].rows.item(m).cells.item(1).childNodes[0].value;
                var valueThen = document.getElementById('decisionBlockTable').tBodies[0].childNodes[m].childNodes[2].childNodes[0].value;

                if (valueThen.localeCompare("Empty") == 0) {
                    valueThen = "";
                }
                var newThen = {"field": fieldsThen, "valueThen": valueThen};
                //console.log("newThen "+ JSON.stringify(newThen));
                attributesThen.push(newThen);
            }


            $.ajax({
                url: "../api/bulkDeviceUpdate.php",
                data: {
                    action: "update_all_devices",
                    //username: loggedUser,
                    //organization:organization,
                    token: sessionToken,
                    attributesIf: JSON.stringify(attributesIf),
                    attributesThen: JSON.stringify(attributesThen)
                },
                dataType: 'json',
                type: "POST",
                async: true,
                success: function (myData) {
                    if (myData['status'] == 'ok') {

                        let mex = "Devices has been correctly updated.";
                        $('#bulkUpdateModalInnerDiv').html(mex);
                        $("#bulkUpdateModal").modal('show');
                        $('#updateMultipleDeviceModal').hide();

                    } else if (myData['status'] == 'ko') {
                        $("#bulkUpdateFaliure").modal('show');
                        $('#updateMultipleDeviceModal').hide();

                    }
                },
                error: function (myData) {
                    $("#bulkUpdateFaliure").modal('show');
                    $('#updateMultipleDeviceModal').hide();

                    $('#bulkUpdateFaliure').html(myData["msg"]);

                }
            });
        }
    });
    $("#attrNameDelbtn").off("click");
    $("#attrNameDelbtn").on("click", function () {
        //console.log("#attrNameDelbtn");	
        $(this).parent('tr').remove();
    });
    $("#editDeviceOkModalDoneBtn").off("click");
    $("#editDeviceOkModalDoneBtn").on("click", function () {
        setTimeout(function () {
            fetch_data(true);
            setTimeout(function () {
                location.reload();
            }, 100);
        }, 100);
    });
    $("#addDeviceKoModalCancelBtn").off("click");
    $("#addDeviceKoModalCancelBtn").on("click", function () {
        setTimeout(function () {
            fetch_data(true);
            setTimeout(function () {
                location.reload();
            }, 1000);
        }, 100);
    });


    //*************UPDATE VALUES ********************/
    $("#updateMultipleValueBtn").off("click");
    $("#updateMultipleValueBtn").click(function () {
        $('#ifBlockTableValue tbody').html("");
        $('#decisionBlockTableValue tbody').html("");

        $('#valuesPreviewTable').DataTable().destroy();
        $('#valuesPreviewTable tbody').html("");
        $("#valueFound").html("0 values founded");
        valueNamesArray['if'] = 0;
        valueNamesArray['then'] = 0;
        // $('#updateMultipleDeviceModal1').modal('hide');

        $('#updateMultipleDeviceModal1').modal('show');
        $('#updateMultipleDeviceModal').modal('hide');


        var row = $('<tr id="ifHV' + idCounterIf + '" class="ifrow"><td><h3><span class="label label-danger">If</span></h3></td><td class="fieldTdValue"><select class="fieldIfValue"><option value="empty">--Select an option--</option><option value="cb" selected>Contextbroker</option><option value="device">Device name</option><option value="deviceType">Device type</option><option value="value_name">Value Name</option><option value="data_type">Data type</option><option value="model">Model</option><option value="producer">Producer</option><option value="frequency">Frequency</option><option value="kind">Kind</option><option value="protocol">Protocol</option><option value="format">Format</option><option value="latitude">Latitude</option><option value="longitude">Longitude</option><option value="macaddress">Mac address</option><option value="k1">Key1</option><option value="k2">Key2</option><option value="value_type">Value type</option><option value="value_unit">Value unit</option><option value="editable">Editable</option><option value="healthiness_criteria">Healthiness criteria</option><option value="healthiness_value">Healthiness value</option></select></td><td class="fieldEqualValue"><select class="fieldSelectEqualValue"><option value="IsEqual">Is Equal</option><option value="IsNotEqual">Is Not Equal</option><option value="IsNull">Is NULL</option><option value="Contains">Contains</option></select></td><td class="fieldNameIfValue"><input type="hidden"></td><td><i class="fa fa-minus"></i></td></tr>');
        $('#ifBlockTableValue tbody').append(row);
        valueNamesArray['if'] = 1;
        //valueNamesArray['if'] + 
        getFields('contextBroker', 0, 'ifBlockTableValue', 1);
        idCounterIf++;

        $(document).ready(function () {
            row.find('i.fa-minus').off("click");
            row.find('i.fa-minus').on("click", function () {
                var rowIndex = $(this).parents('tr').index();
                $('#ifBlockTableValue tbody tr').eq(rowIndex).remove();

                if (rowIndex == 0 && document.getElementById('ifBlockTable').tBodies[0].rows.item(0) != null) {
                    document.getElementById('ifBlockTable').tBodies[0].rows.item(0).childNodes[0].childNodes[0].innerHTML = "<span class=\"label label-danger\">If</span>";
                }

                valueNamesArray['if'] = valueNamesArray['if'] - 1;
                //checkUpdateButton();
                //getAffectedRows();

                ifPages.splice(rowIndex, 1);
                $('#authorizedPagesJson').val(JSON.stringify(ifPages));
            });
        });


        var row2 = $('<tr id="ifHV' + idCounterIf + '" class="ifrow"><td><h3><span class="label label-danger">AND</span></h3></td><td class="fieldTdValue"><select class="fieldIfValue"><option value="empty">--Select an option--</option><option value="cb" >Contextbroker</option><option value="device">Device name</option><option value="deviceType" selected>Device type</option><option value="value_name">Value Name</option><option value="data_type">Data type</option><option value="model">Model</option><option value="producer">Producer</option><option value="frequency">Frequency</option><option value="kind">Kind</option><option value="protocol">Protocol</option><option value="format">Format</option><option value="latitude">Latitude</option><option value="longitude">Longitude</option><option value="macaddress">Mac address</option><option value="k1">Key1</option><option value="k2">Key2</option><option value="value_type">Value type</option><option value="value_unit">Value unit</option><option value="editable">Editable</option><option value="healthiness_criteria">Healthiness criteria</option><option value="healthiness_value">Healthiness value</option></select></td><td class="fieldEqualValue"><select class="fieldSelectEqualValue"><option value="IsEqual">Is Equal</option><option value="IsNotEqual">Is Not Equal</option><option value="IsNull">Is NULL</option><option value="Contains">Contains</option></select></td><td class="fieldNameIfValue"><input type="hidden"></td><td><i class="fa fa-minus"></i></td></tr>');

        $('#ifBlockTableValue tbody').append(row2);
        valueNamesArray['if'] = valueNamesArray['if'] + 1;
        idCounterIf++;

        getFields('deviceType', 1, 'ifBlockTableValue', '');


        $(document).ready(function () {
            row2.find('i.fa-minus').off("click");
            row2.find('i.fa-minus').on("click", function () {
                var rowIndex = $(this).parents('tr').index();
                $('#ifBlockTableValue tbody tr').eq(rowIndex).remove();

                if (rowIndex == 0 && document.getElementById('ifBlockTable').tBodies[0].rows.item(0) != null) {
                    document.getElementById('ifBlockTable').tBodies[0].rows.item(0).childNodes[0].childNodes[0].innerHTML = "<span class=\"label label-danger\">If</span>";
                }

                valueNamesArray['if'] = valueNamesArray['if'] - 1;
                checkUpdateButton();
               // getAffectedRows();

                ifPages.splice(rowIndex, 1);
                $('#authorizedPagesJson').val(JSON.stringify(ifPages));
            });
        });

    });

    function RemoveRow(obj, sbj) {

        var rowIndex = $(obj).parents('tr').index();
        var health = document.getElementById(sbj).tBodies[0].rows.item(rowIndex).cells.item(1).childNodes[0].value;

        if (health.localeCompare("healthiness_value") == 0) {

            if (sbj == 'decisionBlockTableValue') {
                var idRow = document.getElementById(sbj).tBodies[0].rows.item(rowIndex).id;
            } else {
                var idRow = document.getElementById(sbj).tBodies[0].rows.item(rowIndex).id;
            }

            idRow = '#' + sbj + ' tr#' + idRow + "criteria";
            $(idRow).remove();
            if (sbj == 'decisionBlockTableValue') {
                devicenamesArray['then'] = devicenamesArray['then'] - 1;
            } else {
                valueNamesArray['if'] = valueNamesArray['if'] - 1;
            }

        }
        $('#' + sbj + ' tbody tr').eq(rowIndex).remove();
        if (sbj == 'decisionBlockTableValue') {
            devicenamesArray['then'] = devicenamesArray['then'] - 1;
        } else {
            valueNamesArray['if'] = valueNamesArray['if'] - 1;
        }

        if (rowIndex == 0 && document.getElementById(sbj).tBodies[0].rows.item(0) != null) {
            document.getElementById(sbj).tBodies[0].rows.item(0).childNodes[0].childNodes[0].innerHTML = "<span class=\"label label-danger\">If</span>";
        }

        checkUpdateButtonValue();
        getAffectedRowsValue();

        ifPages.splice(rowIndex, 1);
        $('#authorizedPagesJson').val(JSON.stringify(ifPages));
    }

    $('#addifBlockBtnValue').off("click");
    $('#addifBlockBtnValue').click(function () {

        if ($('#ifBlockTableValue tbody tr').length == 0) {
            var row = $('<tr id="ifHV' + idCounterIf + '" class="ifrow"><td><h3><span class="label label-danger">If</span></h3></td><td class="fieldTdValue"><select class="fieldIfValue"><option value="empty">--Select an option--</option><option value="cb">Contextbroker</option><option value="device">Device name</option><option value="deviceType">Device type</option><option value="value_name">Value Name</option><option value="data_type">Data type</option><option value="model">Model</option><option value="producer">Producer</option><option value="frequency">Frequency</option><option value="kind">Kind</option><option value="protocol">Protocol</option><option value="format">Format</option><option value="latitude">Latitude</option><option value="longitude">Longitude</option><option value="macaddress">Mac address</option><option value="k1">Key1</option><option value="k2">Key2</option><option value="value_type">Value type</option><option value="value_unit">Value unit</option><option value="editable">Editable</option><option value="healthiness_criteria">Healthiness criteria</option><option value="healthiness_value">Healthiness value</option></select></td><td class="fieldEqualValue"><select class="fieldSelectEqualValue"><option value="IsEqual">Is Equal</option><option value="IsNotEqual">Is Not Equal</option><option value="IsNull">Is NULL</option><option value="Contains">Contains</option></select></td><td class="fieldNameIfValue"><input type="hidden"></td><td><i class="fa fa-minus"></i></td></tr>');
        } else {
            var row = $('<tr id="ifHV' + idCounterIf + '" class="ifrow"><td><h3><span class="label label-danger">AND</span></h3></td><td class="fieldTdValue"><select class="fieldIfValue"><option value="empty">--Select an option--</option><option value="cb">Contextbroker</option><option value="device">Device name</option><option value="deviceType">Device type</option><option value="value_name">Value Name</option><option value="data_type">Data type</option><option value="model">Model</option><option value="producer">Producer</option><option value="frequency">Frequency</option><option value="kind">Kind</option><option value="protocol">Protocol</option><option value="format">Format</option><option value="latitude">Latitude</option><option value="longitude">Longitude</option><option value="macaddress">Mac address</option><option value="k1">Key1</option><option value="k2">Key2</option><option value="value_type">Value type</option><option value="value_unit">Value unit</option><option value="editable">Editable</option><option value="healthiness_criteria">Healthiness criteria</option><option value="healthiness_value">Healthiness value</option></select></td><td class="fieldEqualValue"><select class="fieldSelectEqualValue"><option value="IsEqual">Is Equal</option><option value="IsNotEqual">Is Not Equal</option><option value="IsNull">Is NULL</option><option value="Contains">Contains</option></select></td><td class="fieldNameIfValue"><input type="hidden"></td><td><i class="fa fa-minus"></i></td></tr>');
        }
        idCounterIf++;
        $('#ifBlockTableValue tbody').append(row);

        valueNamesArray['if'] = valueNamesArray['if'] + 1;
        checkUpdateButtonValue();
        // updateConditionsValue();

        var rowIndex = row.index();

        row.find('a').editable({
            emptytext: "Empty",
            display: function (value, response) {
                if (value.length > 35) {
                    $(this).html(value.substring(0, 32) + "...");
                } else {
                    $(this).html(value);
                }
            }
        });

        ifPages[rowIndex] = null;
        $('#authorizedPagesJson').val(JSON.stringify(ifPages));

        row.find('i.fa-minus').off("click");
        row.find('i.fa-minus').click(function () {
            var obj = this;
            var sbj = 'ifBlockTableValue';

            var rowIndex = $(obj).parents('tr').index();
            var health = document.getElementById(sbj).tBodies[0].rows.item(rowIndex).cells.item(1).childNodes[0].value;

            if (health.localeCompare("healthiness_value") == 0) {

                if (sbj == 'decisionBlockTableValue') {
                    var idRow = document.getElementById(sbj).tBodies[0].rows.item(rowIndex).id;
                } else {
                    var idRow = document.getElementById(sbj).tBodies[0].rows.item(rowIndex).id;
                }

                idRow = '#' + sbj + ' tr#' + idRow + "criteria";
                $(idRow).remove();
                if (sbj == 'decisionBlockTableValue') {
                    devicenamesArray['then'] = devicenamesArray['then'] - 1;
                } else {
                    valueNamesArray['if'] = valueNamesArray['if'] - 1;
                }

            }
            $('#' + sbj + ' tbody tr').eq(rowIndex).remove();
            if (sbj == 'decisionBlockTableValue') {
                devicenamesArray['then'] = devicenamesArray['then'] - 1;
            } else {
                valueNamesArray['if'] = valueNamesArray['if'] - 1;
            }

            if (rowIndex == 0 && document.getElementById(sbj).tBodies[0].rows.item(0) != null) {
                document.getElementById(sbj).tBodies[0].rows.item(0).childNodes[0].childNodes[0].innerHTML = "<span class=\"label label-danger\">If</span>";
            }

            checkUpdateButtonValue();
            getAffectedRowsValue();

            ifPages.splice(rowIndex, 1);
            $('#authorizedPagesJson').val(JSON.stringify(ifPages));
        }
        );

        row.find('a.toBeEdited').off("save");
        row.find('a.toBeEdited').on('save', function (e, params) {
            var rowIndex = $(this).parents('tr').index();
            ifPages[rowIndex] = params.newValue;
            $('#authorizedPagesJson').val(JSON.stringify(ifPages));
        });
        //updateConditionsValue();

    });

    $(document).on({

        change: function () {
            var rowIndex = $(this).parents('tr').index();
            //console.log("row index" + rowIndex);
            var fieldIf = document.getElementById('ifBlockTableValue').tBodies[0].rows.item(rowIndex).cells.item(1).childNodes[0].value;
            //console.log("fieldIf" + fieldIf);
            switch (fieldIf) {
                case "cb":
                    fieldIf = "contextBroker";
                    break;
                case "id":
                    fieldIf = "device";
                    break;
            }

            getFields(fieldIf, rowIndex, 'ifBlockTableValue', 1);
            checkUpdateButtonValue();

        }
    }, '.fieldTdValue select');

    $(document).on({
        change: function () {
            var rowIndex = $(this).parents('tr').index();
            var fieldsThen = document.getElementById('decisionBlockTableValue').tBodies[0].rows.item(rowIndex).cells.item(1).childNodes[0].value;
            getFields(fieldsThen, rowIndex, "decisionBlockTableValue", 0);
            checkUpdateButtonValue();

        }

    }, '.fieldTdThenValue select');

    $(document).on({
        change: function () {
            getAffectedRowsValue();

        }
    }, '.fieldEqualValue select');

    $(document).on({
        input: function () {
            getAffectedRowsValue();

        }
    }, '.fieldNameIfValue input');

    $(document).on({
        change: function () {
            getAffectedRowsValue();
        }
    }, '.fieldNameIfValue select');

    $('#addDecisionBlockBtnValue').off("click");
    $('#addDecisionBlockBtnValue').click(function () {

        var row = $('<tr id="thenHV' + idCounterThen + '"><td><h3><span class="label label-success">Then</span></h3></td><td class="fieldTdThenValue"><select class="fieldThenValue"><option value="empty">--Select an option--</option><option value="data_type">Data type</option><option value="value_type">Value type</option><option value="value_unit">Value unit</option><option value="editable">Editable</option>	<option value="healthiness_criteria">Healthiness criteria</option><option value="healthiness_value">Healthiness value</option></select></td></td><td class="fieldNameValue"><input type="text" class="fieldNameIfValue" value="Empty"><td><i class="fa fa-minus"></i></td></tr>');
        $('#decisionBlockTableValue tbody').append(row);
        idCounterThen++;

        valueNamesArray['then'] = valueNamesArray['then'] + 1;
        checkUpdateButtonValue();

        var rowIndex = row.index();

        row.find('a').editable({
            emptytext: "Empty",
            display: function (value, response) {
                if (value.length > 35) {
                    $(this).html(value.substring(0, 32) + "...");
                } else {
                    $(this).html(value);
                }
            }
        });

        ifPages[rowIndex] = null;
        $('#authorizedPagesJson').val(JSON.stringify(ifPages));


        row.find('i.fa-minus').off("click");
        row.find('i.fa-minus').click(function () {
            //RemoveRow(this,'decisionBlockTableValue' )

            var obj = this;
            var sbj = 'decisionBlockTableValue';
            var rowIndex = $(obj).parents('tr').index();
            var health = document.getElementById(sbj).tBodies[0].rows.item(rowIndex).cells.item(1).childNodes[0].value;

            if (health.localeCompare("healthiness_value") == 0) {

                if (sbj == 'decisionBlockTableValue') {
                    var idRow = document.getElementById(sbj).tBodies[0].rows.item(rowIndex).id;
                } else {
                    var idRow = document.getElementById(sbj).tBodies[0].rows.item(rowIndex).id;
                }

                idRow = '#' + sbj + ' tr#' + idRow + "criteria";
                $(idRow).remove();
                if (sbj == 'decisionBlockTableValue') {
                    devicenamesArray['then'] = devicenamesArray['then'] - 1;
                } else {
                    valueNamesArray['if'] = valueNamesArray['if'] - 1;
                }

            }
            $('#' + sbj + ' tbody tr').eq(rowIndex).remove();
            if (sbj == 'decisionBlockTableValue') {
                devicenamesArray['then'] = devicenamesArray['then'] - 1;
            } else {
                valueNamesArray['if'] = valueNamesArray['if'] - 1;
            }

            if (rowIndex == 0 && document.getElementById(sbj).tBodies[0].rows.item(0) != null) {
                document.getElementById(sbj).tBodies[0].rows.item(0).childNodes[0].childNodes[0].innerHTML = "<span class=\"label label-danger\">If</span>";
            }

            checkUpdateButtonValue();
            getAffectedRowsValue();

            ifPages.splice(rowIndex, 1);
            $('#authorizedPagesJson').val(JSON.stringify(ifPages));
        }


        );


    });



    function checkUpdateButtonValue() {

        var totIf = document.getElementById('ifBlockTableValue').tBodies[0].childElementCount;
        var totThen = document.getElementById('decisionBlockTableValue').tBodies[0].childElementCount;
        if (valueNamesArray['if'] > 0 & valueNamesArray['then'] > 0) {
            for (i = 0; i < totIf; i++) {
                let valueIf = document.getElementById('ifBlockTableValue').tBodies[0].rows.item(i).cells.item(1).childNodes[0].value;

                if (valueIf != "empty") {
                    for (j = 0; j < totThen; j++) {
                        let valueThen = document.getElementById('decisionBlockTableValue').tBodies[0].rows.item(j).cells.item(1).childNodes[0].value;

                        if (valueThen != "empty") {
                            $("#updateAllValuesConfirmBtn").attr("disabled", false);
                            break;
                        }
                    }

                }
            }
        } else {
            $("#updateAllValuesConfirmBtn").attr("disabled", true);
        }
    }

//    function getAffectedRowsValue() {
//
//        var num1 = document.getElementById('ifBlockTableValue').tBodies[0].childElementCount;
//        var attributesIfValues = [];
//        for (var m = 0; m < num1; m++) {
//            //var attribute= document.getElementById('ifBlockTable').rows[m].cells[1].selectedIndex;
//
//            var fieldIf = document.getElementById('ifBlockTableValue').tBodies[0].rows.item(m).cells.item(1).childNodes[0].value;
//            var operatorIf = document.getElementById('ifBlockTableValue').tBodies[0].rows.item(m).cells.item(2).childNodes[0].value;
//            var valueIf = document.getElementById('ifBlockTableValue').tBodies[0].childNodes[m].childNodes[3].childNodes[0].value;
//            if (!valueIf) {
//                var valueIf = document.getElementById('ifBlockTableValue').tBodies[0].rows.item(m).cells.item(3).childNodes[0].value;
//            }
//            //params.newValue
//            if (valueIf.localeCompare("Empty") == 0) {
//                valueIf = "";
//            }
//            if (fieldIf == undefined || fieldIf == null || fieldIf == "") {
//                fieldIf = "healthiness_criteria";
//            }
//
//            var newIf = {"field": fieldIf, "operator": operatorIf, "value": valueIf};
//            attributesIfValues.push(newIf);
//        }
//        if (num1 != 0) {
//            $.ajax({
//                url: "../api/bulkDeviceUpdate.php",
//                data: {
//                    action: "get_affected_values_count",
//                    //username: loggedUser,
//                    //organization:organization,
//                    attributesIf: JSON.stringify(attributesIfValues),
//                    token: sessionToken
//                            //attributesThen: JSON.stringify(attributesThen)	    
//                },
//                dataType: 'json',
//                type: "POST",
//                async: true,
//                success: function (myData) {
//                    $("#valueFound").html(myData['content'] + " values founded");
//                    //	document.getElementById('devicesFound').value = myData['content'] + " devices found";
//
//                    if (attributesIfValues.length == 0) {
//                        buildPreviewValues(JSON.stringify(attributesIfValues), true);
//                    } else {
//                        buildPreviewValues(JSON.stringify(attributesIfValues), previewValuesFirstLoad);
//                        previewValuesFirstLoad = true;
//                    }
//
//                },
//                error: function (myData) {
//                    console.log(JSON.stringify(myData));
//                    $("#valueFound").html("0 values founded");
//                    console.log("data faliure" + myData['msg']);
//                }
//            });//end of ajax get_affected*/
//        } else {
//            $("#valueFound").html("0 values founded");
//
//        }
//    }
//   
//    

    function findAssociationRules() {
        $.ajax({
            url: "../api/associationRulesApi.php",
            data: {
                action: "suggest_associations",
                //username: loggedUser,
                //organization: organization,
                value: "value_type",
                token: sessionToken
            },
            dataType: 'json',
            type: "POST",
            async: true,
            success: function (myData) {
                useAssociationRules(myData['content']);

            },
            error: function (myData) {
                console.log("Error" + JSON.stringify(myData));
            }
        });
    }

    function useAssociationRules(content) {
        while (content.length > 0) {
            var rule = content.pop();
            gb_association_rules.push(rule);
        }
        viewRules();
    }

    function viewRules() {
        if (gb_association_rules.length > 0) {
            var rule = gb_association_rules[gb_rulesCounter];
            var htmlIf = "";
            var htmlElse = "";
            var rulesPrinted = 0;
            for (var i in rule) {

                var key = i;
                var val = rule[i];
                if (rule !== 'lift' && rule !== 'support') {
                    if (key.search("output_") > -1) {
                        var keySplitted = key.split("output_");
                        if (val != "") {
                            htmlElse += "<tr class=\"ifrow\"><td><h3><span class=\"label label-success\">THEN</span></h3></td><td class=\"thenTd\"><select class=\"thenSelect\"><option value=\"" + keySplitted[1] + "\">" + keySplitted[1] + "</option></select></td><td class=\"fieldName\">" + val + "</td><td><i class=\"fa fa-minus\"></i></td></tr>";
                        }
                    } else if (key.search("input_") > -1) {
                        var keySplitted = key.split("input_");
                        if (val != "") {
                            if (rulesPrinted == 0) {
                                htmlIf += "<tr class=\"ifrow\"><td><h3><span class=\"label label-danger\">If</span></h3></td><td class=\"fieldTd\"><select class=\"fieldIf\"><option value=\"" + keySplitted[1] + "\">" + keySplitted[1] + "</option></select></td><td class=\"fieldEqual\"><select class=\"fieldSelectEqual\"><option value=\"IsEqual\">Is Equal</option></select><td class=\"fieldName\">" + val + "</td><td><i class=\"fa fa-minus\"></i></td></tr>";
                                rulesPrinted++;
                            } else {
                                htmlIf += "<tr class=\"ifrow\"><td><h3><span class=\"label label-danger\">AND</span></h3></td><td class=\"fieldTd\"><select class=\"fieldIf\"><option value=\"" + keySplitted[1] + "\">" + keySplitted[1] + "</option></select></td><td class=\"fieldEqual\"><select class=\"fieldSelectEqual\"><option value=\"IsEqual\">Is Equal</option></select><td class=\"fieldName\">" + val + "</td><td><i class=\"fa fa-minus\"></i></td></tr>";
                                rulesPrinted++;
                            }
                        }
                    }

                }
            }


            document.getElementById('ifBlockSuggestions').tBodies[0].innerHTML = htmlIf;
            document.getElementById('decisionBlockSuggestions').tBodies[0].innerHTML = htmlElse;
            document.getElementById('numRulesFound').innerHTML = "Rule " + (gb_rulesCounter + 1) + " of " + (gb_association_rules.length) + " founded";
            gb_rulesCounter = (gb_rulesCounter + 1) % gb_association_rules.length;

            getAffectedRulesValues();
        } else {
            document.getElementById('numRulesFound').innerHTML = "No rules available";
            document.getElementById('ifBlockSuggestions').tBodies[0].innerHTML = "";
            document.getElementById('decisionBlockSuggestions').tBodies[0].innerHTML = "";
        }
    }
    function getAffectedRulesValues() {

        var num1 = document.getElementById('ifBlockSuggestions').tBodies[0].childElementCount;
        var attributesIfValues = [];
        for (var m = 0; m < num1; m++) {
            //var attribute= document.getElementById('ifBlockTable').rows[m].cells[1].selectedIndex;

            var num1 = document.getElementById('ifBlockSuggestions').tBodies[0].childElementCount;
            var fieldIf = document.getElementById('ifBlockSuggestions').tBodies[0].rows.item(m).cells.item(1).childNodes[0].value;
            var operatorIf = document.getElementById('ifBlockSuggestions').tBodies[0].rows.item(m).cells.item(2).childNodes[0].value;
            var valueIf = document.getElementById('ifBlockSuggestions').tBodies[0].childNodes[m].childNodes[3].childNodes[0].textContent;
            //params.newValue
            /*	if(valueIf.localeCompare("Empty")==0){
             valueIf = "";
             }
             if(fieldIf == undefined || fieldIf == null || fieldIf == ""){
             fieldIf= "healthiness_criteria";
             }*/

            var newIf = {"field": fieldIf, "operator": operatorIf, "value": valueIf};
            attributesIfValues.push(newIf);
        }

        if (num1 != 0) {
            $.ajax({
                url: "../api/associationRulesApi.php",
                data: {
                    action: "get_rules_affecting_count",
                    //username: loggedUser,
                    //organization:organization,
                    token: sessionToken,
                    value: "value_type",
                    attributesIf: JSON.stringify(attributesIfValues)
                            //attributesThen: JSON.stringify(attributesThen)	    
                },
                dataType: 'json',
                type: "POST",
                async: true,
                success: function (myData) {
                    $("#rulesMatchFound").html(myData['content'] + " values founded");
                    //	document.getElementById('devicesFound').value = myData['content'] + " devices found";

                    if (attributesIfValues.length == 0) {
                        buildPreviewAssociationRules(JSON.stringify(attributesIfValues), true);
                    } else {
                        buildPreviewAssociationRules(JSON.stringify(attributesIfValues), previewRulesFirstLoad);
                        previewRulesFirstLoad = true;
                    }
                },
                error: function (myData) {
                    $("#rulesMatchFound").html("0 values founded");
                }
            });//end of ajax get_affected*/
        } else {
            $("#valueFound").html("0 values founded");

        }
    }
//Function to create rules -///

    function getIfRules(num1) {
        var attributesIfValues = [];
        for (var m = 0; m < num1; m++) {
            //var attribute= document.getElementById('ifBlockTable').rows[m].cells[1].selectedIndex;

            var fieldIf = document.getElementById('ifBlockTableValue').tBodies[0].rows.item(m).cells.item(1).childNodes[0].value;
            var operatorIf = document.getElementById('ifBlockTableValue').tBodies[0].rows.item(m).cells.item(2).childNodes[0].value;
            var valueIf = document.getElementById('ifBlockTableValue').tBodies[0].childNodes[m].childNodes[3].childNodes[0].value;

            if (valueIf.localeCompare("Empty") == 0) {
                valueIf = "";
            }

            var newIf = {"field": fieldIf, "operator": operatorIf, "value": valueIf};
            attributesIfValues.push(newIf);
        }
        return attributesIfValues;
    }

    function getThenRules(num2) {
        var attributesThenValues = [];

        for (var m = 0; m < num2; m++) {
            var fieldsThen = document.getElementById('decisionBlockTableValue').tBodies[0].rows.item(m).cells.item(1).childNodes[0].value;

            if (fieldsThen != "empty") {
                var valueThen = document.getElementById('decisionBlockTableValue').tBodies[0].childNodes[m].childNodes[2].childNodes[0].value;

                if (valueThen.localeCompare("Empty") == 0) {
                    valueThen = "";
                }
                var newThen = {"field": fieldsThen, "valueThen": valueThen};
                attributesThenValues.push(newThen);
            }
        }
        return attributesThenValues;
    }

//////
    function getService(cb) {

        $.ajax({
            url: "../api/bulkDeviceLoad.php",
            data: {
                action: "get_service_and_path",
                cb: cb,
                token: sessionToken
            },
            type: "POST",
            async: true,
            datatype: 'json',
            success: function (data)
            {

                if (data["status"] === 'ok')
                {
                     $('.js-example-basic-multiple').select2();
                    var SERV = data["content"];

                    var opt = '';
                    var all = [];


                    for (let i = 0; i < SERV.length; i++) {
                        opt += '<option value="' + SERV[i]['service'] + '_' + SERV[i]['servicePath'] + '">' + SERV[i]['service'] + ' (with servicepath  ' + SERV[i]['servicePath'] + ' )</option>';
                        a = [SERV[i]['service'] + '_' + SERV[i]['servicePath']];
                        all.push(a);
                    }
                  

                    $('#OPTServiceRules').append(opt);
                    opt = '';


                }
            },
            error: function (data)
            {
                console.log("Ko result: " + data);
                alert("Network errors. <br/> Get in touch with the Snap4City Administrator<br/>" + JSON.stringify(data));
            }

        });

    }

///SAVE RULES ///
    $("#SAVEAllValuesRULEBtn").off("click");
    $("#SAVEAllValuesRULEBtn").on("click", function () {
        LoadRules();



        var num1 = document.getElementById('ifBlockTableValue').tBodies[0].childElementCount;
        var num2 = document.getElementById('decisionBlockTableValue').tBodies[0].childElementCount;
        var cb = null;



        for (let i = 0; i <= num1; i++) {
            if ((document.querySelector("#ifHV" + i + " > td.fieldTdValue > select"))) {
                if ((document.querySelector("#ifHV" + i + "  > td.fieldTdValue > select").value == 'cb')) {
                    //((document.querySelector("#ifHV"+i+" > td.fieldTdValue > select").value=='contextBroker' ||document.querySelector("#ifHV"+i+"  > td.fieldTdValue > select").value== 'cb')){
                    cb = document.querySelector("#ifHV" + i + "  > td.fieldNameIfValue > select").value;
                    break;

                }
            }
        }

        if (num1 != 0 || num2 != 0) {



            var attributesIfValues = getIfRules(num1);
            var attributesThenValues = getThenRules(num2);
            $('#updateMultipleDeviceModal1').hide();
            $('#NamingValuesRULEModal').modal('show');

            /////

            getService(cb)

            document.getElementById('NamingValuesRULEMInput').addEventListener('input', updateValue);

            function updateValue(e) {
                let button = $("#NamingValuesRULEConfirmBtn");
                let inp = document.getElementById('NamingValuesRULEMInput').value;
                if (inp.length < 2 || Regole.includes(inp)) {

                    //  button.disabled=false;
                    $('#NamingValuesRULEConfirmBtn').prop('disabled', true);
                } else {
                    //button.disabled=true;
                    $('#NamingValuesRULEConfirmBtn').prop('disabled', false);
                }
            }
            $("#CloseSave").on("click", function () {
                $('#NamedRule').modal('hide');
                $('#updateMultipleDeviceModal1').show();
            });

            $("#NamingValuesRULEConfirmBtn").on("click", function () {
                let name = document.getElementById('NamingValuesRULEMInput').value;

                if ($('#modeRules option').filter(':selected').val() == '0') {
                    var mode = '0';
                } else {
                    var mode = '1';
                }

                var service_path = $('#OPTServiceRules').val();
                var service = new Array();
                var servicePath = new Array();
                if (service_path.includes(',')) {
                    let l = service_path.split(',');
                    for (i = 0; i < l.length; i++) {
                        s = l[i].split('_')[0];
                        sp = l[i].split('_')[1];
                        service.push(s);
                        servicePath.push(sp);
                    }
                    service = service.toString();
                    servicePath = servicePath.toString();

                } else {
                    service = service_path.split("_")[0];
                    servicePath = service_path.split("_")[1];
                }


                $.ajax({
                    url: "../api/bulkDeviceUpdate.php",
                    data: {
                        action: "Save_device_rules",
                        //username: loggedUser,
                        //organization:organization,
                        attributesIf: JSON.stringify(attributesIfValues),
                        attributesThen: JSON.stringify(attributesThenValues),
                        name: name,
                        mode: mode,
                        service: service,
                        servicePath: servicePath,
                        contextbroker: cb,
                        token: sessionToken
                    },
                    dataType: 'json',
                    type: "POST",
                    async: true,
                    success: function (myData) {
                        if (myData['status'] == 'ok') {
                            $('#NamingValuesRULEModal').modal('hide');
                            //$('#updateMultipleDeviceModal1').show();
                            $('#NamedRule').modal('show');
                            $('#NamedRuleOK').html('Your rule is saved');
                        } else if (myData['status'] == 'ko') {
                            $('#NamedRule').modal('show');
                            $('#NamedRuleOK').html('There are some problems. Your rule is NOT saved');

                        }
                    },
                    error: function (myData) {

                        $('#NamedRule').modal('show');
                        $('#NamedRuleOK').html('There are some problems. Your rule is NOT saved');
                    }
                });
            });
        }
    });
    $("#NamingValuesRULEDismissBtn").on("click", function () {
        $('#updateMultipleDeviceModal1').show();
    });

    //NamingValuesRULEDismissBtn

///UPDATE DEVICE ///
    $("#updateAllValuesConfirmBtn").off("click");
    $("#updateAllValuesConfirmBtn").on("click", function () {
        var num1 = document.getElementById('ifBlockTableValue').tBodies[0].childElementCount;
        var num2 = document.getElementById('decisionBlockTableValue').tBodies[0].childElementCount;

        if (num1 != 0 & num2 != 0) {

            var attributesIfValues = getIfRules(num1);
            var attributesThenValues = getThenRules(num2);


            $.ajax({
                url: "../api/bulkDeviceUpdate.php",
                data: {
                    action: "update_all_values",
                    //username: loggedUser,
                    //organization:organization,
                    attributesIf: JSON.stringify(attributesIfValues),
                    attributesThen: JSON.stringify(attributesThenValues),
//                    cb: 'CapelonTampere',
//                    service: '1,tampere',
//                    service_path:'2,/',

                    token: sessionToken
                },
                dataType: 'json',
                type: "POST",
                async: true,
                success: function (myData) {
                    if (myData['status'] == 'ok') {
                        $('#updateMultipleDeviceModal1').hide();

                        let mex = "Values has been correctly updated."
                        $('#bulkUpdateModalInnerDiv').html(mex);

                        $("#bulkUpdateModal").modal('show');
                    } else if (myData['status'] == 'ko') {
                        $('#updateMultipleDeviceModal1').hide();
                        $("#bulkUpdateFaliure").modal('show');
                    }
                },
                error: function (myData) {
                    $('#updateMultipleDeviceModal1').hide();
                    $("#bulkUpdateFaliure").modal('show');

                }
            });
        }
    });
    $("#attrNameDelbtn").off("click");
    $("#attrNameDelbtn").on("click", function () {
        $(this).parent('tr').remove();
    });

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


    // EDIT PARAMETERS MODAL
    $('#registeredDevicesTable tbody').on('click', 'button.editDashBtn', function () {

        document.getElementById('editlistParameters').innerHTML = "";
        document.getElementById('deletedAttributes').innerHTML = "";
        document.getElementById('infoParamError').innerHTML = "";

        $("#editParametersModalBody").show();
        $('#editParametersModalTabs').show();

        $('#editParametersConfirmBtn').attr("disabled", true);

        $("#editParametersLoadingMsg").hide();
        $("#editParametersLoadingIcon").hide();
        $("#editParametersOkMsg").hide();
        $("#editParametersOkIcon").hide();
        $("#editParametersKoMsg").hide();
        $("#editParametersKoIcon").hide();
        $("#editParametersModalFooter").show();
        $("#editParametersModal").modal('show');


        var id = $(this).attr('data-id');
        var contextbroker = $(this).attr('data-contextbroker');
        var valuename = $(this).attr('data-valuename');
        var old_value_name = $(this).attr('data-valuename');
        var datatype = $(this).attr('data-datatype');
        var valuetype = $(this).attr('data-valuetype');
        var valueunit = $(this).attr('data-valueunit');
        var healthinesscriteria = $(this).attr('data-healthinesscriteria');
        var refreshrate = $(this).attr('data-refreshrate');
        var model = $(this).attr('data-model');

        $("#editParametersModalLabel").html("Edit Parameter of device: " + id.split(".").slice(-1)[0]);
        $("#editParametersModalLabel").attr('contextbroker', $(this).attr("data-contextbroker"));
        $("#editParametersModalLabel").attr('device', id);
        $("#editParametersModalLabel").attr('model', model);
        $("#editParametersModalLabel").attr('old_value_name', old_value_name);

        var indexValues = 0;
        var content = drawAttributeMenu(valuename,
                datatype, valuetype, false, valueunit, healthinesscriteria,
                refreshrate, valuename, 'editlistParameters', indexValues,'');
        $('#editlistParameters').append(content);
        $('.delValueButton').hide();

        $("#editSchemaTabParameters #editlistParameters .row").each(function () {

            var param = {
                cb: $("#editParametersModalLabel").attr('contextbroker'),
                model: $("#editParametersModalLabel").attr('model'),
                value_name: document.getElementById('editlistParameters').childNodes[0].childNodes[0].childNodes[0].childNodes[0].value.trim(),
                data_type: document.getElementById('editlistParameters').childNodes[0].childNodes[1].childNodes[0].childNodes[0].value.trim(),
                value_type: document.getElementById('editlistParameters').childNodes[0].childNodes[2].childNodes[0].childNodes[0].value.trim(),
                editable: document.getElementById('editlistParameters').childNodes[0].childNodes[4].childNodes[0].childNodes[0].value.trim(),
                value_unit: document.getElementById('editlistParameters').childNodes[0].childNodes[3].childNodes[0].childNodes[0].value.trim(),
                healthiness_criteria: document.getElementById('editlistParameters').childNodes[0].childNodes[5].childNodes[0].childNodes[0].value.trim(),
                value_refresh_rate: document.getElementById('editlistParameters').childNodes[0].childNodes[6].childNodes[0].childNodes[0].value.trim()
            }
            //console.log(param)

            console.log(param);
            var status = verifyParameter(param);
            console.log(status);
            if (status.isvalid) {
                $('#editParametersConfirmBtn').attr("disabled", false);
                $('#editParametersConfirmBtn').attr("data-id", true);
                document.getElementById('infoParamError').innerHTML = status.message;
                document.getElementById('infoParamError').style.color = 'green'
            } else {
                $('#editParametersConfirmBtn').attr("disabled", true);
                document.getElementById('infoParamError').innerHTML = status.message;
                document.getElementById('infoParamError').style.color = 'red'
            }

        });


    });
    $("#editParametersConfirmBtn").click(function () {

        $('#editParametersLoadingMsg').show();
        $('#editParametersLoadingIcon').show();
        var param = {
            contextbroker: $("#editParametersModalLabel").attr('contextbroker'),
            device: $("#editParametersModalLabel").attr('device'),
            old_value_name: $("#editParametersModalLabel").attr('old_value_name'),
            value_name: document.getElementById('editlistParameters').childNodes[0].childNodes[0].childNodes[0].childNodes[0].value.trim(),
            data_type: document.getElementById('editlistParameters').childNodes[0].childNodes[1].childNodes[0].childNodes[0].value.trim(),
            value_type: document.getElementById('editlistParameters').childNodes[0].childNodes[2].childNodes[0].childNodes[0].value.trim(),
            editable: document.getElementById('editlistParameters').childNodes[0].childNodes[4].childNodes[0].childNodes[0].value.trim(),
            value_unit: document.getElementById('editlistParameters').childNodes[0].childNodes[3].childNodes[0].childNodes[0].value.trim(),
            healthiness_criteria: document.getElementById('editlistParameters').childNodes[0].childNodes[5].childNodes[0].childNodes[0].value.trim(),
            healthiness_value: document.getElementById('editlistParameters').childNodes[0].childNodes[6].childNodes[0].childNodes[0].value.trim()
        }

        console.log(param);
        $.ajax({
            url: "../api/value.php",
            data: {
                action: "update_temporary_event_values_for_registered_devices",
                contextbroker: param.contextbroker,
                device: param.device,
                value_name: param.value_name,
                old_value_name: param.old_value_name,
                data_type: param.data_type,
                value_type: param.value_type,
                editable: param.editable,
                value_unit: param.value_unit,
                healthiness_criteria: param.healthiness_criteria,
                healthiness_value: param.healthiness_value,
                token: sessionToken
            },
            type: "POST",
            async: true,
            success: function (data) {
                if (data["status"] === 'ko') {
                    console.log("Error inserting Parameter");
                    console.log(data);
                    $('#editParametersLoadingMsg').hide();
                    $('#editParametersLoadingIcon').hide();

                    $('#editParametersKoMsg').show();
                    $('#editParametersKoIcon').show();

                    setTimeout(function () {
                        $('#editParametersModal').modal('hide');
                        fetch_data(true);
                        setTimeout(function () {
                            $('#editParametersModal').hide();
                            setTimeout(updateDeviceTimeout, 100);

                        }, 100);
                    }, 100);
                } else if (data["status"] === 'ok') {

                    $('#editParametersLoadingMsg').hide();
                    $('#editParametersLoadingIcon').hide();
                    $('#editParametersOkMsg').show();
                    $('#editParametersOkIcon').show();

                    $("#editDeviceModalInnerDiv1").html('Device &nbsp; successfully Updated');
                    $("#editDeviceModalInnerDiv2").html('<i class="fa fa-check" style="font-size:42px"></i>');

                    setTimeout(function () {
                        $('#editParametersModal').modal('hide');
                        fetch_data(true);

                        setTimeout(function () {
                            $('#editParametersOkMsg').hide();
                            $('#editParametersOkIcon').hide();
                            setTimeout(updateDeviceTimeout, 100);
                        }, 100);
                    }, 100);

                } else {
                    console.log(data);
                }

            },
            error: function (data) {

                console.log("Ko result: " + JSON.stringify(data));
                $("#editDeviceKoModalInnerDiv1").html(data["msg"]);
                $("#editDeviceKoModal").modal('show');
                // $("#editDeviceModalUpdating").hide();
                // $("#editParametersModalBody").show();
                // $("#editParametersModalFooter").show();

                $('#editParametersModal').hide();
                setTimeout(updateDeviceTimeout, 3000);
            }
        });


    })
    $('#registeredDevicesTable tbody').on('click', 'button.delDashBtn', function () {
        var id = $(this).attr('data-id');
        var contextBroker = $(this).attr("data-contextbroker");
        var value_name = $(this).attr('data-valuename');

        $("#deleteParameterModal div.modal-body").html('<div class="modalBodyInnerDiv"><span data-valuename = "' + value_name + '" data-id = "' + id + '" data-contextBroker = "' + contextBroker + '" >Do you want to confirm deletion of parameter <b>' + value_name + '</b> of device <b>' + id + '</b>?</span></div>');
        $("#deleteParameterModal").modal('show');
    })
    $('#deleteParameterConfirmBtn').off("click");
    $("#deleteParameterConfirmBtn").click(function () {

        var id = $("#deleteParameterModal span").attr("data-id");
        var contextbroker = $("#deleteParameterModal span").attr("data-contextbroker");
        var value_name = $("#deleteParameterModal span").attr("data-valuename");
        // var uri = $("#deleteDeviceModal span").attr("data-uri");
        // var status = $("#deleteDeviceModal span").attr("data-status1");
        //console.log("valori val "+id +" "+contextbroker + " " + status);

        $("#deleteParameterModal div.modal-body").html("");
        $("#deleteParameterCancelBtn").hide();
        $("#deleteParameterConfirmBtn").hide();
        $("#deleteParameterModal div.modal-body").append('<div id="deleteParameterModalInnerDiv1" class="modalBodyInnerDiv"><h5>Parameter deletion in progress, please wait</h5></div>');
        $("#deleteParameterModal div.modal-body").append('<div id="deleteParameterModalInnerDiv2" class="modalBodyInnerDiv"><i class="fa fa-circle-o-notch fa-spin" style="font-size:36px"></i></div>');


        $.ajax({
            url: "../api/value.php",
            data: {
                action: "delete_temporary_event_values_for_registered_devices",
                contextbroker: contextbroker,
                device: id,
                value_name: value_name,
                token: sessionToken,
            },
            type: "POST",
            async: true,
            success: function (data) {
                //console.log(JSON.stringify(data));
                if (data["status"] === 'ko') {
                    $("#deleteParameterModalInnerDiv1").html(data["msg"]);
                    $("#deleteParameterModalInnerDiv2").html('<i class="fa fa-frown-o" style="font-size:42px"></i>');

                    setTimeout(function () {
                        $("#deleteParameterModal").modal('hide');
                    }, 2000);
                } else if (data["status"] === 'ok') {
                    $("#deleteParameterModalInnerDiv1").html('Parameter &nbsp; <b>' + id + '</b> &nbsp;deleted successfully');
                    $("#deleteParameterModalInnerDiv2").html('<i class="fa fa-check" style="font-size:42px"></i>');



                    setTimeout(function () {
                        fetch_data(true);
                        $("#deleteParameterModal").modal('hide');

                        setTimeout(function () {
                            $("#deleteParameterCancelBtn").show();
                            $("#deleteParameterConfirmBtn").show();
                        }, 500);
                    }, 2000);
                }
            },
            error: function (data) {
                console.log(JSON.stringify(data));
                $("#deleteParameterModalInnerDiv1").html(data["msg"]);
                $("#deleteParameterModalInnerDiv2").html('<i class="fa fa-frown-o" style="font-size:42px"></i>');
                setTimeout(function () {
                    $("#deleteParameterModal").modal('hide');
                }, 2000);
            }
        });
    });
    $("#editParametersCancelBtn").off("click");
    $("#editParametersCancelBtn").on('click', function () {

        document.getElementById('editlistParameters').innerHTML = "";
        //document.getElementById('addlistAttributesM').innerHTML = "";
        document.getElementById('deletedAttributes').innerHTML = "";
    });

    $("#editSchemaTabParameters").off("click");
    $("#editSchemaTabParameters").on('click keyup', function () {
        console.log("#editSchemaTabParameters");

        $("#editSchemaTabParameters #editlistParameters .row").each(function () {

            var param = {
                cb: $("#editParametersModalLabel").attr('contextbroker'),
                model: $("#editParametersModalLabel").attr('model'),
                value_name: document.getElementById('editlistParameters').childNodes[0].childNodes[0].childNodes[0].childNodes[0].value.trim(),
                data_type: document.getElementById('editlistParameters').childNodes[0].childNodes[1].childNodes[0].childNodes[0].value.trim(),
                value_type: document.getElementById('editlistParameters').childNodes[0].childNodes[2].childNodes[0].childNodes[0].value.trim(),
                editable: document.getElementById('editlistParameters').childNodes[0].childNodes[4].childNodes[0].childNodes[0].value.trim(),
                value_unit: document.getElementById('editlistParameters').childNodes[0].childNodes[3].childNodes[0].childNodes[0].value.trim(),
                healthiness_criteria: document.getElementById('editlistParameters').childNodes[0].childNodes[5].childNodes[0].childNodes[0].value.trim(),
                value_refresh_rate: document.getElementById('editlistParameters').childNodes[0].childNodes[6].childNodes[0].childNodes[0].value.trim()
            }
            //console.log(param)

            console.log(param);
            var status = verifyParameter(param);
            console.log(status);
            if (status.isvalid) {
                $('#editParametersConfirmBtn').attr("disabled", false);
                document.getElementById('infoParamError').innerHTML = status.message;
                document.getElementById('infoParamError').style.color = 'green'
            } else {
                $('#editParametersConfirmBtn').attr("disabled", true);
                document.getElementById('infoParamError').innerHTML = status.message;
                document.getElementById('infoParamError').style.color = 'red'
            }

        });

        $("#editSchemaTabParameters #editlistParameters .row input:even").each(function () {
            //console.log($(this).val());
            checkEditValueName($(this));
        });
        //checkEditDeviceConditions();
    });

    $("#insertValidAttributeBtn").off("click");
    $('#insertValidAttributeBtn').click(function () {
        // insertValidParams();
        insertValidParams();
    });

});  // end of ready-state

function getValidParams() {
    var validParams = []
    $('#registeredDevicesTable').DataTable().rows().every(function (rowIdx, tableLoop, rowLoop) {
        var d = this.data();
        var ruleAlreadyExist = false;
        if (d.status == 'valid') {
            validParams.push({
                contextbroker: d.cb,
                device: d.device_with_path,
                attribute: {
                    value_name: d.value_name,
                    data_type: d.data_type,
                    value_type: d.value_type,
                    editable: d.editable,
                    value_unit: d.value_unit,
                    healthiness_criteria: d.healthiness_criteria,
                    healthiness_value: d.value_refresh_rate
                }
            })
            for (var rule of extractionRules) {
                if (rule.contextbroker == d.cb && rule.attributeName == '$.' + d.value_name && ((rule.service == d.service && rule.servicepath == d.servicePath) || ((rule.service == null || rule.service == "") && (rule.servicepath == null || rule.servicepath == "") && (d.service == null || d.service == "") && (d.servicePath == null || d.servicePath == "")))) {
                    ruleAlreadyExist = true;
                }
            }
            if (!ruleAlreadyExist) {
                var id = d.value_name + "_" + Math.random().toString(36).substring(0, 7);
                var selector = '{"param":{"s":"$.' + d.value_name + '","i":0},"type":"' + d.format + '"}'
                addNewExtractionRule(id, d.cb, d.format, selector, 'value', d.value_type, d.value_unit, d.data_type, 'yes', d.service, d.servicePath)

            }
        }
    });
    console.log('validParams: ' + validParams);
    return validParams;
}
function addNewExtractionRule(id, cb, format, selector, kind, value_type, value_unit, data_type, structure, service, servicePath) {
    $.ajax({
        url: "../api/extractionRules.php",
        data: {
            action: "insert",
            id: id,
            contextbroker: cb,
            format: format,
            selector: selector,
            kind: kind,
            value_type: value_type,
            value_unit: value_unit,
            data_type: data_type,
            structure_flag: structure,
            service: service,
            service_path: servicePath,
            token: sessionToken,
        },
        type: "POST",
        async: true,
        success: function (data) {
            if (data["status"] === 'ko') {
                console.log('error inserting new extraction rule')
            } else if (data["status"] === 'ok') {
                console.log('inserted new extraction rule')
            } else {
                console.log(JSON.stringify(data));
            }

        },
        error: function (data) {
            console.log(JSON.stringify(data));
        }
    });
}
function insertValidParams() {
    var validParams = getValidParams()
    if (validParams.length > 0) {
        $.ajax({
            url: "../api/value.php",
            data: {
                action: "insert_all_valid_event_values",
                listAttributes: JSON.stringify(validParams),
                token: sessionToken,
            },
            type: "POST",
            async: true,
            success: function (data) {
                if (data["status"] === 'ko') {
                    console.log("Error inserting Parameter");
                    console.log(data);
                    alert('an error occurred inserting parameters');
                    setTimeout(function () {
                        fetch_data(true);
                        setTimeout(function () {
                            setTimeout(updateDeviceTimeout, 100);

                        }, 100);
                    }, 100);
                } else if (data["status"] === 'ok') {
                    alert('Valid parameters inserted correctly');

                    setTimeout(function () {
                        fetch_data(true);
                        setTimeout(function () {
                            setTimeout(updateDeviceTimeout, 100);
                        }, 100);
                    }, 100);

                } else {
                    console.log(JSON.stringify(data));
                }

            },
            error: function (data) {
                console.log("Ko result: " + JSON.stringify(data));
                alert(JSON.stringify(data));
                setTimeout(updateDeviceTimeout, 3000);
            }
        })
    } else {
        alert("There are no valid parameters");
    }
}

function drawAttributeMenu
        (attrName, data_type, value_type, editable, value_unit, healthiness_criteria, value_refresh_rate, old_value_name, parent, indice, disabled_field) {

    if (attrName == "") {
        msg = "<div style=\"color:red;\" class=\"modalFieldMsgCnt\"></div>";
    } else {
        msg = "<div class=\"modalFieldMsgCnt\">&nbsp;</div>";
    }


    options = "";
    if (value_type == "") {
        options += "<option hidden disabled selected value=\"NOT VALID OPTION\"> -- select an option -- </option>";
        msg_value_type = "<div style=\"color:red;\" class=\"modalFieldMsgCnt\">Value type is mandatory</div>";
    } else
        msg_value_type = "<div style=\"color:#337ab7;\" class=\"modalFieldMsgCnt\">Ok</div>";

    for (var n = 0; n < gb_value_types.length; n++) {
        if (value_type == gb_value_types[n].value)
            options += "<option value=\"" + gb_value_types[n].value + "\" selected>" + gb_value_types[n].label + " (" + gb_value_types[n].value + ")</option>";
        else
            options += "<option value=\"" + gb_value_types[n].value + "\" >" + gb_value_types[n].label + " (" + gb_value_types[n].value + ")</option>";
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
    //---start sara---
    if (value_refresh_rate === undefined) {
        value_refresh_rate = "";
    }
    var refresh_rate = "", different_values = "", within_bounds = "", healthiness_empty = ""; //0910Fatima
    switch (healthiness_criteria) {
        case "refresh_rate":
            refresh_rate = "selected";
            break;
        case "different_values":
            different_values = "selected";
            break;
        case "within_bounds":
            within_bounds = "selected";
            break;
        default: //0910Fatima
            healthiness_empty = "selected";
            break;
    }

    //0910Fatima--block modification start	
    var editable_true = "", editable_false = "", editable_empty = "";
    if (editable == "1") {
        editable_true = "selected";
    } else if (editable == "0") {
        editable_false = "selected";
    } else {
        editable_empty = "selected";
    }

    console.log(data_type + "," + value_type + "," + editable + "," + value_unit + "," + healthiness_criteria + "," + value_refresh_rate + "," + parent);
    return "<div class=\"row\" style=\"border:2px solid blue;\" ><div class=\"col-xs-6 col-md-3 modalCell\">" +
            "<div class=\"modalFieldCnt\"><input type=\"text\" class=\"modalInputTxt valueName\"" +
            "name=\"" + attrName + "\"  value=\"" + attrName + "\"disabled id=\"value_name" + indice + "\" >" +
            
            "</div><div class=\"modalFieldLabelCnt\">Value Name</div>" + msg + "</div>" +
            "<div class=\"col-xs-6 col-md-3 modalCell\"><div class=\"modalFieldCnt\">" +
            "<select class=\"modalInputTxt\"  id=\"value_type" + indice + "\" " +
            "onchange=valueTypeChanged(" + indice + ") " +
            "\" "+ disabled_field +">" + options +
            "</select>" +
            "</div><div class=\"modalFieldLabelCnt\">Value Type" +
            "</div>" + msg_value_type + "</div>" +
            "<div class=\"col-xs-6 col-md-3 modalCell\"><div class=\"modalFieldCnt\">" +
            "<select class=\"modalInputTxt\" id=\"value_unit" + indice + "\" " +
            "onchange=valueUnitChanged(" + indice + ") " +
            "\" "+disabled_field +">" +
            myunits +
            "</select>" +
            "</div><div class=\"modalFieldLabelCnt\">Value Unit" +
            "</div>" + msg_value_unit + "</div>" +
            "<div class=\"col-xs-6 col-md-3 modalCell\"><div class=\"modalFieldCnt\">" +
            "<select class=\"modalInputTxt\" id=\"data_type" + indice + "\"" +
            "onchange=dataTypeChanged(" + indice + ") " + 
            "\" "+disabled_field +">" + mydatatypes +
            "</select></div><div class=\"modalFieldLabelCnt\">Data Type</div>" + msg_data_unit + "</div>" +
            "<div class=\"col-xs-6 col-md-3 modalCell\"><div class=\"modalFieldCnt\">" +
            "<select class=\"modalInputTxt\" name=\"" + healthiness_criteria +
            "\" "+disabled_field +">" +
            "<option value=\"refresh_rate\" " + refresh_rate + ">Refresh rate</option>" +
            "<option value=\"different_values\" " + different_values + ">Different Values</option>" +
            "<option value=\"within_bounds\" " + within_bounds + ">Within bounds</option>" +
            "<option value= ' '" + healthiness_empty + "> </option>" +
            "</select></div><div class=\"modalFieldLabelCnt\">Healthiness criteria</div></div>" +
            "<div class=\"col-xs-6 col-md-3 modalCell\"><div class=\"modalFieldCnt\">" +
            "<input type=\"text\" class=\"modalInputTxt\" name=\"" + value_refresh_rate +
            "\" value=\"" + value_refresh_rate + "\" "+disabled_field +"></div><div class=\"modalFieldLabelCnt\">Healthiness value</div></div>" +
            "<div class=\"col-xs-6 col-md-3 modalCell\">" + "<div   class=\"modalFieldCnt HIDE\" ><input type=\"text\" class=\"modalInputTxt \"" +
            "id=\"Value" + attrName + "\"  \"name=\"Value" + attrName + "\" readonly=\"readonly\"  >" +
            "</div><div  \"id=\"ValueLabel" + attrName + "\"  class=\"modalFieldLabelCnt HIDE \">Loading data</div> </div>" +
            "<select class=\"modalInputTxt\" style=\"display:none\" name=\"" + old_value_name +
            "\" "+disabled_field +"\>" +
            "<option value=\"" + old_value_name + "\">" + old_value_name + "</option>" +
            "</select>" +
            "<div class=\"col-xs-6 col-md-3 modalCell delValueButton\"><div class=\"modalFieldCnt\">" +
            "<button class=\"btn btn-danger HIDE\" onclick=\"removeElementAt('" + parent + "',this); return true;\">Remove Value</button></div></div></div>";
}

function removeElementAt(parent, child) {
    var list = document.getElementById(parent);
    if (parent == "editlistAttributes") {
        console.log(child.parentElement.parentElement.parentElement)
        document.getElementById('deletedAttributes').appendChild(child.parentElement.parentElement.parentElement);
    } else
        list.removeChild(child.parentElement.parentElement.parentElement);
    checkAtlistOneAttribute();
    checkEditAtlistOneAttribute();
}
function verifyDevice(deviceToverify) {

    var msg = "";
    var regexpMAC = /^([0-9A-F]{2}[:-]){5}([0-9A-F]{2})$/;
    var answer = {"isvalid": true, "message": "Your device is valid"};
    var regex_devName = /[^a-z0-9:._-]/gi;
    var regex_valueName = /[^a-z0-9._-]/gi;

    if (deviceToverify.name == undefined || deviceToverify.name.length < 5 || deviceToverify.name == null) {
        msg += "-name is mandatory, of 5 characters at least.";
    }
    if (regex_devName.test(deviceToverify.name)) {
        msg += "-name cannot contain special characters. ";
    }
    if (deviceToverify.devicetype == undefined || deviceToverify.devicetype == "" || deviceToverify.devicetype.indexOf(' ') >= 0 || deviceToverify.devicetype == null) {
        msg += "-devicetype is mandatory.";
    }
    if (deviceToverify.macaddress != undefined && deviceToverify.macaddress != "" && !regexpMAC.test(deviceToverify.macaddress)) {
        msg += "-Mac format should be Letter (A-F) and number (eg. 3D:F2:C9:A6:B3:4F).";
    }
    if (deviceToverify.frequency == undefined || deviceToverify.frequency == "" || !isFinite(deviceToverify.frequency) || deviceToverify.frequency == null) {
        msg += "-frequency is mandatory, and should be numeric.";
    }
    if (deviceToverify.kind == undefined || deviceToverify.kind == "" || deviceToverify.kind == null) {
        msg += "-kind is mandatory.";
    }
    if (deviceToverify.protocol == undefined || deviceToverify.protocol == "" || deviceToverify.protocol == null) {
        msg += "-protocol is mandatory.";
    }
    if (deviceToverify.format == undefined || deviceToverify.format == "" || deviceToverify.format == null) {
        msg += "-format is mandatory.";
    }
    if (deviceToverify.latitude == undefined || !isLatitude(deviceToverify.latitude) || deviceToverify.latitude == null) {
        msg += "-Latitude is mandatory, with the correct numeric format.";
    }
    if (deviceToverify.longitude == undefined || !isLongitude(deviceToverify.longitude || deviceToverify.longitude == null)) {
        msg += "-Longitude is mandatory, with the correct numeric format.";
    }
    if (deviceToverify.k1 == undefined || deviceToverify.k1 == "" || deviceToverify.k1 == "null") {
        msg += "-k1 is mandatory.";
    }
    if (deviceToverify.k2 == undefined || deviceToverify.k2 == "" || deviceToverify.k2 == "null") {
        msg += "-k2 is mandatory.";
    }

    //verify consistency subnature and its attributes
    if (deviceToverify.subnature !== "") {
        // TODO remove this comment or enable in another way this verify... 
        // if was removed beecause it delay too much
        //if (!verifySubnature(deviceToverify.subnature, deviceToverify.static_attributes)){
        //    	answer.isvalid=false;
        //            msg+="-The static attributes of the device do not comply with its subnature ("+deviceToverify.subnature+")";
        //    }
    }

    if (msg.length > 0)
        answer.isvalid = false;

    if (deviceToverify.deviceValues.length < 1) {
        answer.isvalid = false;
        msg += "-Your device should at least have 1 attributes.";
    }

    //var model_not_found=true;

    if (deviceToverify.model != "custom") {
        for (var i = 0; i < modelsdata.length; i++) {
            if (modelsdata[i].name != deviceToverify.model) {
                continue;
            }

            //model_not_found=false;
            var modelAttributes = JSON.parse(modelsdata[i].attributes);

            if (Object.keys(modelAttributes).length != Object.keys(deviceToverify.deviceValues).length) {
                answer.isvalid = false;
                msg += "-Your device has different number of attributes than the selected model ";
            } else {
                for (var j = 0; j < deviceToverify.deviceValues.length; j++) {
                    var found = 0;
                    for (var l = 0; l < modelAttributes.length; l++) {
                        if (modelAttributes[l].value_name == deviceToverify.deviceValues[j].value_name) {
                            found = 1;

                            var msg_attr_detail = "";

                            if (modelAttributes[l].value_type != deviceToverify.deviceValues[j].value_type) {
                                msg_attr_detail += " value type,";
                            }
                            if (modelAttributes[l].data_type != deviceToverify.deviceValues[j].data_type) {
                                msg_attr_detail += " data type,";
                            }
                            if (modelAttributes[l].editable != deviceToverify.deviceValues[j].editable) {
                                msg_attr_detail += " editable,";
                            }
                            if (modelAttributes[l].healthiness_criteria != deviceToverify.deviceValues[j].healthiness_criteria) {
                                msg_attr_detail += " healthiness criteria,";
                            }
                            if (modelAttributes[l].healthiness_value != deviceToverify.deviceValues[j].healthiness_value) {
                                msg_attr_detail += " healthiness value,";
                            }
                            if (modelAttributes[l].value_unit != deviceToverify.deviceValues[j].value_unit) {
                                msg_attr_detail += " value unit,";
                            }

                            if (msg_attr_detail.length > 0) {
                                answer.isvalid = false;
                                msg += "The attribute " + deviceToverify.deviceValues[j].value_name + " has the details:" + msg_attr_detail + " not compatible with its model.";
                            } else {
                                modelAttributes.splice(l, 1);
                            }
                        }
                    }
                    if (found == 0) {
                        answer.isvalid = false;
                        msg += "-The device attribute name " + deviceToverify.deviceValues[j].value_name + " do not comply with its model."
                    }
                }
            }

            if (modelsdata[i].contextbroker != deviceToverify.contextbroker) {
                answer.isvalid = false;
                msg += "-The device property: context broker does not comply with its model.";
            }
            if (modelsdata[i].devicetype != deviceToverify.devicetype) {
                answer.isvalid = false;
                msg += "-The device property: type does not comply with its model.";
            }
            if (modelsdata[i].format != deviceToverify.format) {
                answer.isvalid = false;
                msg += "-The device property: format does not comply with its model.";
            }
            if (modelsdata[i].frequency != deviceToverify.frequency) {
                answer.isvalid = false;
                msg += "-The device property: frequency does not comply with its model.";
            }
            if (modelsdata[i].kind != deviceToverify.kind) {
                answer.isvalid = false;
                msg += "-The device property: kind does not comply with its model.";
            }
            if (modelsdata[i].producer != deviceToverify.producer) {
                answer.isvalid = false;
                msg += "-The device property: producer does not comply with its model.";
            }
            if (modelsdata[i].protocol != deviceToverify.protocol) {
                {
                    answer.isvalid = false;
                    msg += "-The device property: protocol does not comply with its model.";
                }
            }
            if (modelsdata[i].subnature != deviceToverify.subnature) {
                {
                    answer.isvalid = false;
                    msg += "-The device property: subnature does not comply with its model.";
                }
            }
            //if(modelsdata[i].static_attributes!=deviceToverify.static_attributes){{ answer.isvalid=false;
            //msg+="-The device property: static_attributes does not comply with its model." ;}}	
            //the model doenot permit a null value for service and servicepath, so they are always configured... so cannot be used for validation
            //TODO we need model to set null to activate this functionalities
            //if ((modelsdata[i].service!==null)&&(modelsdata[i].service!=deviceToverify.service)){{ answer.isvalid=false;
            //		msg+="-The device property: service does not comply with its model." ;}}
            //if ((modelsdata[i].servicePath!==null)&&(modelsdata[i].servicePath!=deviceToverify.service_path)){{ answer.isvalid=false;
            //		msg+="-The device property: servicePath does not comply with its model." ;}}
        }
    } else {

        var all_attr_msg = "";
        var all_attr_status = "true";
        var healthiness_criteria_options = ["refresh_rate", "different_values", "within_bounds"];

        for (var i = 0; i < deviceToverify.deviceValues.length; i++) {
            var v = deviceToverify.deviceValues[i];

            var attr_status = true;
            var attr_msg = "";
            var empty_name = false;
            var strangeChar_name = false;

            if (v.value_name == undefined || v.value_name == "" || gb_value_types.indexOf(v.value_type).value < 0) {
                attr_status = false;
                empty_name = true;
            } else if (regex_valueName.test(v.value_name)) {
                attr_status = false;
                strangeChar_name = true;
            }
            //set default values
            if (v.data_type == undefined || v.data_type == "" || gb_datatypes.indexOf(v.data_type) < 0) {
                attr_status = false;
                attr_msg = attr_msg + " data_type";
            }
            if (v.value_unit == undefined || v.value_unit == "") {
                attr_status = false;
                attr_msg = attr_msg + " value_unit";
            }
            if (v.value_type == undefined || v.value_type == "" || v.value_type == "NOT VALID OPTION") {
                attr_status = false;
                attr_msg = attr_msg + " value_type";
            }
            if (v.editable != "0" && v.editable != "1") {
                attr_status = false;
                attr_msg = attr_msg + " editable";
            }
            if (v.healthiness_criteria == undefined || v.healthiness_criteria == "" || healthiness_criteria_options.indexOf(v.healthiness_criteria) < 0) {
                attr_status = false;
                attr_msg = attr_msg + " healthiness_criteria";
            }
            if (v.healthiness_value == undefined || v.healthiness_value == "") {
                attr_status = false;
                attr_msg = attr_msg + " healthiness_value";
            }

            if (attr_status == false) {
                all_attr_status = false;
                if (empty_name) {
                    all_attr_msg = "The attribute name cannot be empty";
                    if (attr_msg != "") {
                        all_attr_msg = all_attr_msg + ", other errors in: " + attr_msg;
                    }
                } else if (strangeChar_name) {
                    all_attr_msg += "The attribute name " + v.value_name + " cannot contain strange characters. ";
                    if (attr_msg != "") {
                        all_attr_msg += all_attr_msg + ", other errors in: " + attr_msg;
                    }
                } else {
                    all_attr_msg += "For the attribute: " + v.value_name + ", error in: " + attr_msg + "; ";
                }
            }
        }

        if (!all_attr_status) {
            answer.isvalid = false;
            msg = msg + " -" + all_attr_msg;
        }
    }

    if (answer.isvalid) {
        return answer;
    } else {
        answer.message = msg;
        return answer;
    }
}

function verifyParameter(row) {

    var parameters = [
        {
            value_name: row.value_name,
            value_type: row.value_type,
            data_type: row.data_type,
            value_unit: row.value_unit,
            editable: row.editable,
            healthiness_criteria: row.healthiness_criteria,
            healthiness_value: row.value_refresh_rate,
        }
    ]

    var deviceToverify = new Object();
    deviceToverify.contextbroker = row.cb;
    deviceToverify.model = row.model;
    deviceToverify.deviceValues = parameters;


    var msg = "";
    var regexpMAC = /^([0-9A-F]{2}[:-]){5}([0-9A-F]{2})$/;
    var answer = {"isvalid": true, "message": "This parameter is valid"};
    var regex_devName = /[^a-z0-9:._-]/gi;
    var regex_valueName = /[^a-z0-9._-]/gi;

    if (msg.length > 0)
        answer.isvalid = false;

    if (deviceToverify.deviceValues.length < 1) {
        answer.isvalid = false;
        msg += "-Your device should at least have 1 attributes.";
    }

    if (deviceToverify.model != "custom") {
        for (var i = 0; i < modelsdata.length; i++) {
            if (modelsdata[i].name != deviceToverify.model) {
                continue;
            }

            //model_not_found=false;
            var modelAttributes = JSON.parse(modelsdata[i].attributes);

            if (Object.keys(modelAttributes).length != Object.keys(deviceToverify.deviceValues).length) {
                answer.isvalid = false;
                msg += "-Your device has different number of attributes than the selected model ";
            } else {
                for (var j = 0; j < deviceToverify.deviceValues.length; j++) {
                    var found = 0;
                    for (var l = 0; l < modelAttributes.length; l++) {
                        if (modelAttributes[l].value_name == deviceToverify.deviceValues[j].value_name) {
                            found = 1;

                            var msg_attr_detail = "";

                            if (modelAttributes[l].value_type != deviceToverify.deviceValues[j].value_type) {
                                msg_attr_detail += " value type,";
                            }
                            if (modelAttributes[l].data_type != deviceToverify.deviceValues[j].data_type) {
                                msg_attr_detail += " data type,";
                            }
                            if (modelAttributes[l].editable != deviceToverify.deviceValues[j].editable) {
                                msg_attr_detail += " editable,";
                            }
                            if (modelAttributes[l].healthiness_criteria != deviceToverify.deviceValues[j].healthiness_criteria) {
                                msg_attr_detail += " healthiness criteria,";
                            }
                            if (modelAttributes[l].healthiness_value != deviceToverify.deviceValues[j].healthiness_value) {
                                msg_attr_detail += " healthiness value,";
                            }
                            if (modelAttributes[l].value_unit != deviceToverify.deviceValues[j].value_unit) {
                                msg_attr_detail += " value unit,";
                            }

                            if (msg_attr_detail.length > 0) {
                                answer.isvalid = false;
                                msg += "The attribute " + deviceToverify.deviceValues[j].value_name + " has the details:" + msg_attr_detail + " not compatible with its model.";
                            } else {
                                modelAttributes.splice(l, 1);
                            }
                        }
                    }
                    if (found == 0) {
                        answer.isvalid = false;
                        msg += "-The device attribute name " + deviceToverify.deviceValues[j].value_name + " do not comply with its model."
                    }
                }
            }

            if (modelsdata[i].contextbroker != deviceToverify.contextbroker) {
                answer.isvalid = false;
                msg += "-The device property: context broker does not comply with its model.";
            }

        }
    } else {

        var all_attr_msg = "";
        var all_attr_status = "true";
        var healthiness_criteria_options = ["refresh_rate", "different_values", "within_bounds"];

        for (var i = 0; i < deviceToverify.deviceValues.length; i++) {
            var v = deviceToverify.deviceValues[i];

            var attr_status = true;
            var attr_msg = "";
            var empty_name = false;
            var strangeChar_name = false;

            if (v.value_name == undefined || v.value_name == "" || gb_value_types.indexOf(v.value_type).value < 0) {
                attr_status = false;
                empty_name = true;
            } else if (regex_valueName.test(v.value_name)) {
                attr_status = false;
                strangeChar_name = true;
            }
            //set default values
            if (v.data_type == undefined || v.data_type == "" || gb_datatypes.indexOf(v.data_type) < 0) {
                attr_status = false;
                attr_msg = attr_msg + " data_type";
            }
            if (v.value_unit == undefined || v.value_unit == "") {
                attr_status = false;
                attr_msg = attr_msg + " value_unit";
            }
            if (v.value_type == undefined || v.value_type == "" || v.value_type == "NOT VALID OPTION") {
                attr_status = false;
                attr_msg = attr_msg + " value_type";
            }
            if (v.editable != "0" && v.editable != "1") {
                attr_status = false;
                attr_msg = attr_msg + " editable";
            }
            if (v.healthiness_criteria == undefined || v.healthiness_criteria == "" || healthiness_criteria_options.indexOf(v.healthiness_criteria) < 0) {
                attr_status = false;
                attr_msg = attr_msg + " healthiness_criteria";
            }
            if (v.healthiness_value == undefined || v.healthiness_value == "") {
                attr_status = false;
                attr_msg = attr_msg + " healthiness_value";
            }

            if (attr_status == false) {
                all_attr_status = false;
                if (empty_name) {
                    all_attr_msg = "The attribute name cannot be empty";
                    if (attr_msg != "") {
                        all_attr_msg = all_attr_msg + ", other errors in: " + attr_msg;
                    }
                } else if (strangeChar_name) {
                    all_attr_msg += "The attribute name " + v.value_name + " cannot contain strange characters. ";
                    if (attr_msg != "") {
                        all_attr_msg += all_attr_msg + ", other errors in: " + attr_msg;
                    }
                } else {
                    all_attr_msg += "For the attribute: " + v.value_name + ", error in: " + attr_msg + "; ";
                }
            }
        }

        if (!all_attr_status) {
            answer.isvalid = false;
            msg = msg + " -" + all_attr_msg;
        }
    }

    if (answer.isvalid) {
        return answer;
    } else {
        answer.message = msg;
        return answer;
    }
}

function getIndexofValueType(array, value_type) {
    for (var i = 0; i < array.length; i++) {
        if (array[i].value == value_type)
            return array[i].id
    }
    return -1;
}

function checkKeys(k1, k2) {
    var message = null;
    var pattern = /^[a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{12}$/;

    var value1 = k1;
    var value2 = k2;
    var valid = true;


    if ((value1 === '') && (value2 === '')) {
        valid = false;
        message = 'Specify Key for the selected option';

    } else if (!pattern.test(value1) || !pattern.test(value2)) {
        message = 'The Key should contain at least one special character and a number';
        valid = false;
    }

    return {isvalid: valid, validity_msg: message};

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

function isLatitude(lat) {
    return isFinite(lat) && Math.abs(lat) <= 90;
}

function isLongitude(lng) {
    return isFinite(lng) && Math.abs(lng) <= 180;
}

function drawMap1(latitude, longitude, flag) {
    var marker;
    if (typeof map1 === 'undefined' || !map1) {
        if (flag == 0) {

            map1 = L.map('addLatLong').setView([latitude, longitude], zoomLevel);
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
                lat = lat.toFixed(4);
                lng = lng.toFixed(4);

                document.getElementById('inputLatitudeDevice').value = lat;
                document.getElementById('inputLongitudeDevice').value = lng;
                addDeviceConditionsArray['inputLatitudeDevice'] = true;
                checkDeviceLatitude();
                checkEditDeviceConditions();
                addDeviceConditionsArray['inputLongitudeDevice'] = true;
                checkDeviceLongitude();
                checkEditDeviceConditions();
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
                lat = lat.toFixed(4);
                lng = lng.toFixed(4);

                document.getElementById('inputLatitudeDeviceM').value = lat;
                document.getElementById('inputLongitudeDeviceM').value = lng;
                editDeviceConditionsArray['inputLatitudeDeviceM'] = true;
                checkEditDeviceLatitude();
                checkEditDeviceConditions();
                editDeviceConditionsArray['inputLongitudeDeviceM'] = true;
                checkEditDeviceLongitude();
                checkEditDeviceConditions();
                if (marker) {
                    map1.removeLayer(marker);
                }
                marker = new L.marker([lat, lng]).addTo(map1).bindPopup(lat + ',' + lng);
            });
        }
    }
}

function nodeJsTest() {
    var progress_modal = document.getElementById('myModal');
    var span = document.getElementsByClassName("close")[0];
    var spin = document.getElementById("loader_spin");
    var progress_ok = document.getElementById('progress_ok');
    document.getElementById('myModalBody').innerHTML = "Inserting the valid devices...";
    progress_modal.style.display = "block";
    spin.style.display = "block";
    progress_ok.style.display = "none";


    $.ajax({
        url: "../api/bulkDeviceLoad.php",
        data: {
            action: "get_count_temporary_devices",
            //username: loggedUser,
            token: sessionToken,
            //organization:organization
        },
        type: "POST",
        async: true,
        dataType: "JSON",
        timeout: 0,
        success: function (mydata) {
            if (mydata['content'] != undefined) {
                var content = mydata['content'];


                if (typeof (parseInt(content)) == "number") {
                    insertValidDevices(parseInt(content));
                } else {
                    document.getElementById('myModalBody').innerHTML = "couldn't find any device to insert";
                    progress_ok.style.display = "block";
                    spin.style.display = "none";
                }

            } else {
                document.getElementById('myModalBody').innerHTML = "couldn't find any device to insert";
                progress_ok.style.display = "block";
                spin.style.display = "none";
            }
        },
        error: function (mydata) {
            console.log("Error " + JSON.stringify(mydata));
            document.getElementById('myModalBody').innerHTML = "couldn't find any device to insert";
            progress_ok.style.display = "block";
            spin.style.display = "none";

        }

    });


    var test_data = {
        action: "bulkload",
        //username: loggedUser,
        //organization:organization,
        start: 1,
        end: 6,
        token: sessionToken,
        should_be_registered: "no"

    };
    alert("Request sent");
    $.post('../api/bulkDeviceLoad.php', {'data': test_data, 'data_from_nodeJs': 1}, function (data) {
    });
    /*$.ajax({
     url: "https://www.snap4city.org/iotdirectorytest/stubs/bulkload",
     data:{
     action: "bulkload", 
     username: loggedUser,
     token : sessionToken
     },
     type: "POST",
     async: true,
     dataType: "json",
     timeout: 0,
     success: function (mydata) 
     {
     }
     
     ,
     error: function (mydata)
     {
     console.log("Error "+ JSON.stringify(mydata));
     
     }
     
     }); */

}

function insertValidDevices() {

    var data = {
        action: "bulkload",
        //username: loggedUser,
        token: sessionToken,
        data_parallel: 1,
        //organization:organization,
        should_be_registered: "no"
    };

    $.post('../api/async_request.php', {'data': data}, function (response_data) {

        var progress_modal = document.getElementById('myModal');
        var span = document.getElementsByClassName("close")[0];
        var spin = document.getElementById("loader_spin");
        var progress_ok = document.getElementById('progress_ok');
    });

    var progress_modal_b = document.getElementById('myModal_forbulkstatus');
    var span_b = document.getElementsByClassName("close")[0];
    var spin_b = document.getElementById("loader_spin_forbulkstatus");
    var progress_ok_b = document.getElementById('progress_ok_forbulkstatus');
    var progress_stop_b = document.getElementById('progress_stop_forbulkstatus');
    document.getElementById('myModalBody_forbulkstatus').innerHTML = "<p>Inserting the valid devices is being processed...</p> By clicking 'Ok' you will be redirected to the main page, but the process will be running anyway.";
    progress_modal_b.style.display = "block";
    spin_b.style.display = "none";
    progress_ok_b.style.display = "block";
    progress_stop_b.style.display = "block";

    checkBulkStatus();
}

function stop_progress() {
    var progress_modal = document.getElementById('myModal_forbulkstatus');
    progress_modal.style.display = "none";

    if (timerID != undefined) {
        clearInterval(timerID); // The setInterval it cleared and doesn't run anymore.
    }

    $.ajax({
        url: "../api/bulkDeviceLoad.php",
        data: {
            action: "stop_bulk",
            //username: loggedUser,
            token: sessionToken,
            //organization:organization
        },
        type: "POST",
        async: true,
        dataType: "JSON",
        timeout: 0,
        success: function (mydata) {
            if (mydata["status"] == 'ok') {
                is_processing = 0;
                refresh();
            } else {
                console.log("Error stoping the bulkload " + mydata);
            }
        },
        error: function (mydata) {
            console.log("Failure in stoping bulkload " + JSON.stringify(mydata));
        }
    });

}

function dismiss_dialog() {
    var progress_modal = document.getElementById('myModal_forbulkstatus');
    progress_modal.style.display = "none";

    if (timerID != undefined) {
        clearInterval(timerID); // The setInterval it cleared and doesn't run anymore.
    }

    //window.location = 'https://www.snap4city.org/iotdirectorytest/management/contextbroker.php';
    window.location.replace('../management/devices.php');

}

function checkBulkStatus() {

    is_processing = 1;

    is_finished = 0;
    var progress_modal = document.getElementById('myModal_forbulkstatus');
    var span = document.getElementsByClassName("close")[0];
    var spin = document.getElementById("loader_spin_forbulkstatus");
    var progress_ok = document.getElementById('progress_ok_forbulkstatus');
    var progress_stop = document.getElementById('progress_stop_forbulkstatus');

    if (timerID != undefined) {
        clearInterval(timerID);
    }

    timerID = setInterval(function () {

        if (is_processing == 0) {
            clearInterval(timerID);
            progress_modal.style.display = "none";

            if (was_processing == 1 && is_finished == 1) {
                var progress_modal_ = document.getElementById('myModal');
                var spin_ = document.getElementById("loader_spin");
                var progress_ok_ = document.getElementById('progress_ok');
                document.getElementById('myModalBody').innerHTML = "<p>Your valid devices have been uploaded.</p> ";
                progress_modal_.style.display = "block";
                spin_.style.display = "none";
                progress_ok_.style.display = "block";
                was_processing = 0;

            }
        } else {

            $.ajax({
                url: "../api/bulkDeviceLoad.php",
                data: {
                    action: "get_bulk_status",
                    //username: loggedUser,
                    //organization: organization,
                    token: sessionToken
                },
                type: "POST",
                async: true,
                dataType: "JSON",
                timeout: 0,
                success: function (mydata) {
                    if (mydata["status"] == 'ok') {
                        if (mydata['is_bulk_processing'] == 1 || mydata['is_bulk_processing'] == '1') {

                            is_processing = 1;
                            was_processing = 1;

                            var nb_processed = parseInt(mydata['number_processed']);
                            var totale_processed = parseInt(mydata['totale']);
                            var percentage_processed = 0;

                            if (nb_processed != undefined && nb_processed != 0 && !isNaN(nb_processed) && totale_processed != undefined && totale_processed != 0 && !isNaN(totale_processed)) {
                                percentage_processed = Math.min(100, Math.ceil(nb_processed * 100 / totale_processed));

                                document.getElementById('myModalBody_forbulkstatus').innerHTML = "<p>Inserting the valid devices is being processed...</p> By clicking 'Ok' you will be redirected to the main page, but the process will be running anyway.<p> " + percentage_processed + " % of your valid devices have been processed";
                            } else {
                                document.getElementById('myModalBody_forbulkstatus').innerHTML = "<p>Inserting the valid devices is being processed...</p> By clicking 'Ok' you will be redirected to the main page, but the process will be running anyway.<p> ";
                            }
                            progress_modal.style.display = "block";
                            spin.style.display = "none";
                            progress_ok.style.display = "block";
                            progress_stop.style.display = "block";
                        } else {
                            is_processing = 0;
                            is_finished = mydata["is_finished"];
                        }
                    } else {
                        console.log("Error retrieving the bulkstatus " + mydata);
                    }
                },
                error: function (mydata) {
                    console.log("Failure in retrieving the bulkstatus " + JSON.stringify(mydata));
                }
            });
        }

    }, 3 * 1000);//each 3 seconds 
}

function checkHeadersIfValid(csvheaders) {

    var answer = {"isValid": "", "msg": "", "headers": []};
    var a = new Set(requiredHeaders);
    var b = new Set([]);

    /* if(csvheaders.length < requiredHeaders.length){
     answer.isValid=false;
     answer.msg="Your input file is missing "+(requiredHeaders.length-csvheaders.length)+" fields, please add them and retry again."
     return answer;
     }*/

    var found = 0;

    for (var i = 0; i < csvheaders.length; i++) {

        var h = csvheaders[i];
        if (h.trim().toLowerCase().indexOf("name") > -1 && h.trim().toLowerCase().indexOf("data") < 0 && h.trim().toLowerCase().indexOf("val") < 0) {
            found = found + 1;
            csvheaders[i] = "name";
            b.add("name");
            continue;
        }
        if (h.trim().toLowerCase().indexOf("dev") > -1 && h.trim().toLowerCase().indexOf("type") > -1) {
            found = found + 1;
            csvheaders[i] = "devicetype";
            b.add("devicetype");
            continue;
        }
        if (h.trim().toLowerCase().indexOf("mac") > -1) {
            found = found + 1;
            csvheaders[i] = "macaddress";
            b.add("macaddress");
            continue;
        }
        if (h.trim().toLowerCase().indexOf("freq") > -1) {
            found = found + 1;
            csvheaders[i] = "frequency";
            b.add("frequency");
            continue;
        }
        if (h.trim().toLowerCase().indexOf("kind") > -1) {
            found = found + 1;
            csvheaders[i] = "kind";
            b.add("kind");
            continue;
        }
        if (h.trim().toLowerCase().indexOf("proto") > -1) {
            found = found + 1;
            csvheaders[i] = "protocol";
            b.add("protocol");
            continue;
        }
        if (h.trim().toLowerCase().indexOf("form") > -1) {
            found = found + 1;
            csvheaders[i] = "format";
            b.add("format");
            continue;
        }
        if (h.trim().toLowerCase().indexOf("prod") > -1) {
            found = found + 1;
            csvheaders[i] = "producer";
            b.add("producer");
            continue;
        }
        if (h.trim().toLowerCase().indexOf("gate") > -1 && h.trim().toLowerCase().indexOf("type") > -1) {
            found = found + 1;
            csvheaders[i] = "edge_gateway_type";
            b.add("edge_gateway_type");
            continue;
        }
        if (h.trim().toLowerCase().indexOf("gate") > -1 && h.trim().toLowerCase().indexOf("uri") > -1) {
            found = found + 1;
            csvheaders[i] = "edge_gateway_uri";
            b.add("edge_gateway_uri");
            continue;
        }
        if (h.trim().toLowerCase().indexOf("lat") > -1) {
            found = found + 1;
            csvheaders[i] = "latitude";
            b.add("latitude");
            continue;
        }
        if (h.trim().toLowerCase().indexOf("long") > -1) {
            found = found + 1;
            csvheaders[i] = "longitude";
            b.add("longitude");
            continue;
        }
        if (h.trim().toLowerCase().indexOf("k1") > -1) {
            found = found + 1;
            csvheaders[i] = "k1";
            b.add("k1");
            continue;
        }
        if (h.trim().toLowerCase().indexOf("k2") > -1) {
            found = found + 1;
            csvheaders[i] = "k2";
            b.add("k2");
            continue;
        }
        if (h.trim().toLowerCase().indexOf("value") > -1 && h.trim().toLowerCase().indexOf("name") > -1) {
            found = found + 1;
            csvheaders[i] = "value_name";
            b.add("value_name");
            continue;
        }
        if (h.trim().toLowerCase().indexOf("data") > -1 && h.trim().toLowerCase().indexOf("type") > -1) {
            found = found + 1;
            csvheaders[i] = "data_type";
            b.add("data_type");
            continue;
        }
        if (h.trim().toLowerCase().indexOf("value") > -1 && h.trim().toLowerCase().indexOf("type") > -1) {
            found = found + 1;
            csvheaders[i] = "value_type";
            b.add("value_type");
            continue;
        }
        if (h.trim().toLowerCase().indexOf("edit") > -1) {
            found = found + 1;
            csvheaders[i] = "editable";
            b.add("editable");
            continue;
        }
        if (h.trim().toLowerCase().indexOf("unit") > -1) {
            found = found + 1;
            csvheaders[i] = "value_unit";
            b.add("value_unit");
            continue;
        }
        if (h.trim().toLowerCase().indexOf("criteria") > -1 && h.trim().toLowerCase().indexOf("health") > -1) {
            found = found + 1;
            csvheaders[i] = "healthiness_criteria";
            b.add("healthiness_criteria");
            continue;
        }
        if (h.trim().toLowerCase().indexOf("health") > -1 && h.trim().toLowerCase().indexOf("value") > -1) {
            found = found + 1;
            csvheaders[i] = "healthiness_value";
            b.add("healthiness_value");
            continue;
        }

    }

    var difference = new Set([...a].filter(x => !b.has(x)));


    if (difference.size == 0) {
        answer.isValid = true;
        answer.msg = "your file headers are valid";
        answer.headers = csvheaders;
        return answer;
    } else if (difference.size == requiredHeaders.length) {
        answer.isValid = false;
        answer.msg = "your file has none of the required fields.";
        return answer;
    } else {
        var message = "";
        difference.forEach(function (item) {
            message += item + ", ";
        });
        answer.isValid = false;
        answer.msg = "your file is missing these fields: " + message + " please add them and try again.";
        return answer;
    }


}

function showValidityMsg(status, msg) {
    $("#ErrorDeviceAttr").modal('show');
    $("#ErrorDeviceAttrMsg").html(msg);
    if (status == "valid") {
        $("#ErrorDeviceAttrTitle").html("<b>Do you want register the device?</b>");
        button = document.getElementById("Register_Device");
        button.style.display = "initial";
    } else {
        $("#ErrorDeviceAttrTitle").html("<b>There are some problems</b>");
        button = document.getElementById("Register_Device");
        button.style.display = "none";
    }
    //
    // alert(msg);
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

function refresh() {
    location.reload();
}
