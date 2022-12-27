$.fn.modal.Constructor.prototype.enforceFocus = function () { };
var data;
var Data_call;
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

            addSubnature($("#selectSubnature"), mydata["subnature"]);
        } else {
            console.log("error getting the data types " + data);
        }
    },
    error: function (mydata) {
        console.log(JSON.stringify(mydata));
        alert("Network errors. <br/> Get in touch with the Snap4City Administrator<br/>" + JSON.stringify(mydata));
    }
});
if ((loggedRole == 'RootAdmin') || (loggedRole == 'ToolAdmin')) {
    ColNameEditView = 'Edit';
} else {
    ColNameEditView = 'View';
}
$(document).ready(function () {

// DATI
    data = {action: "get_fiware_model", token: sessionToken, should_be_registered: "no", no_columns: ["" + ColNameEditView + ", delete"]};
    dataTable3 = $('#FIWAREModelTable').DataTable({
        "processing": true,
        "paging": true,
        "ajax": {
            url: "../api/model.php",
            data: data,
            //datatype: 'json',
            type: "POST"
        },
        search: {
            return: true
        },
        select: true,
        "columns": [{
                "name": "Name",
                "orderable": true,
                "data": function (row, type, val, meta) {
                    return  "<b>" + row.model + "<b>";
                }
            }, {
                "name": "Subdomain",
                "data": function (row, type, val, meta) {
                    return row.subdomain;
                }
            },
            {
                "name": "Domain",
                "orderable": false,
                className: "center",
                data: function (row, type, val, meta) {

                    return row.domain;
                }
            }, {
                "name": "Version",
                "orderable": false,
                className: "center",
                data: function (row, type, val, meta) {

                    return row.version;
                }
            },
            {
                "name": ColNameEditView,
                "orderable": false,
                className: "center",
                data: null,
                render: function (d) {

                    if ((loggedRole == 'RootAdmin') || (loggedRole == 'ToolAdmin')) {

                        return '<button type="button" class="editDashBtn" ' +
                                'data-modelName="' + d.model + '" ' +
                                'data-modelDomain="' + d.domain + '" ' +
                                'data-modelSubDomain="' + d.subdomain + '" ' +
                                'data-version="' + d.version + '" ' +
                                'data-subnature="' + d.subnature + '" ' +
                                '"">Edit</button>';
                    } else {
                        return '<button type="button" class="viewDashBtn" ' +
                                'data-modelName="' + d.model + '" ' +
                                'data-modelDomain="' + d.domain + '" ' +
                                'data-modelSubDomain="' + d.subdomain + '" ' +
                                'data-version="' + d.version + '" ' +
                                'data-subnature="' + d.subnature + '" ' +
                                '"">View</button>';
                    }
                }
            }

        ],
        "order": []
    });
    var modelName;
    var modelDomain;
    var modelSubDomain;
    var Mversion;
    var flag_modify_exist_rule = false;
    var make_rule = false;
    //EDIT FIWARE MODEL

    function get_ready_form_edit_view(This, mode) {
        if (mode) {
            temp = 'disabled';
        } else {
            temp = '';
        }

        //Load Modal
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
        //Set Data
        $("#editModelModalLabel").html("Edit Model - " + This.attr("data-modelName"));
        modelName = This.attr('data-modelName');
        modelDomain = This.attr('data-modelDomain');
        modelSubDomain = This.attr('data-modelSubDomain');
        Mversion = This.attr('data-version');
        SubNat = This.attr('data-subnature');
        //Insert Data in the field
        $('#inputNameModel').val(modelName);
        $('#inputDomaninModel').val(modelDomain);
        $('#inputSubdomaninModel').val(modelSubDomain);
        $('#inputVersionModel').val(Mversion);
        $('#inputNameModel').attr('readonly', mode);
        $('#inputDomaninModel').attr('readonly', mode);
        $('#inputSubdomaninModel').attr('readonly', mode);
        $('#inputVersionModel').attr('readonly', mode);
        //subnature
        if (SubNat) {
            $('#selectSubnature').val(SubNat); // Select the option with a value of '1'
            $('#selectSubnature').trigger('change'); // Notify any JS components that the value changed

        } else {
            $("select").select2({
                // options 
                placeholder: 'Search:  ' + modelName

            });
        }
        version = Mversion;
        //make LINK
        if (!document.getElementById("GotoSchema")) {
            var linkGIT = 'https://github.com/smart-data-models/dataModel.' + modelSubDomain + '/blob/master/' + modelName + '/README.md';
            $('#editInfoTabModel').append(" <div class=\"col-xs-6 col-md-3 modalCell\"><div class=\"modalFieldCnt\">" +
                    "<button id=\"GotoSchema\" class=\"btn btn-primary\" onclick=\"window.open('" + linkGIT + " ' )\">Go to Git Reference</button></div></div>");
        }
        //Call for values
        $('a[data-toggle="tab"]').off('shown.bs.tab').on('shown.bs.tab', function (e) {
            make_rule = true;
            var target = $(e.target).attr("href");
            if ((target == '#editSchemaTabModel')) {

                document.getElementById('editlistAttributes').innerHTML = "";
                $('#editModelLoadingIcon').show();
                $.ajax({
                    url: "../api/model.php",
                    data: {
                        action: "get_value_attributes_fiware",
                        id: modelName,
                        version: version,
                        domain: modelDomain,
                        subdomain: modelSubDomain,
                        proposal: true,
                        token: sessionToken
                    },
                    type: "POST",
                    async: true,
                    dataType: 'json',
                    success: function (mydata)
                    {
                        var row = null;
                        $("#editUserPoolsTable tbody").empty();
                        $('#editModelLoadingIcon').hide();
                        var myattributes = JSON.parse(mydata.content.attributes); //
                        var myprop = (mydata.proposal);
                        var keys = Object.keys(myattributes);
                        content = "";
                        k = 0;
                        z = 0;
                        indexValues = 0;
                        while (k < keys.length)
                        {
                            D_type = "";
                            V_type = "";
                            V_unit = '';
                            value_name = myattributes[keys[k]].value_name;
                            if (myattributes[keys[k]].value_name != 'type') {
                                if (myattributes[keys[k]].checked == 'False') {
                                    descr = (myattributes[keys[k]].raw_attribute).description;
                                    if ((loggedRole == 'RootAdmin') || (loggedRole == 'ToolAdmin')) {
                                        for (i = 0; i < myprop.length; i++) {
                                            if (myprop[i].value_name == value_name) {
                                                D_type = myprop[i].data_type;
                                                V_type = myprop[i].value_type;
                                                V_unit = myprop[i].value_unit;
                                                break;
                                            }
                                        }
                                    } else {
                                        D_type = myattributes[keys[k]].data_type;
                                        V_type = myattributes[keys[k]].value_type;
                                        V_unit = myattributes[keys[k]].value_unit;
                                    }
                                } else {
                                    z++;
                                    if (typeof (myattributes[keys[k]].raw_attribute).description != 'undefined') {
                                        descr = (myattributes[keys[k]].raw_attribute).description;
                                    } else {
                                        descr = JSON.parse(myattributes[keys[k]].raw_attribute).description;
                                    }
                                    D_type = myattributes[keys[k]].data_type;
                                    V_type = myattributes[keys[k]].value_type;
                                    V_unit = myattributes[keys[k]].value_unit;
                                }
                                if (value_name == 'dateObserved') {
                                    D_type = 'string';
                                    V_type = 'timestamp';
                                    V_unit = 'timestamp';
                                }
                                content = drawAttributeMenu(value_name, //attrName
                                        D_type, //data_type
                                        V_type, //value_type
                                        myattributes[keys[k]].editable, // editable
                                        V_unit, //value_unit 
                                        myattributes[keys[k]].healthiness_criteria, // healthiness_criteria
                                        myattributes[keys[k]].healthiness_value, // value_refresh_rate
                                        myattributes[keys[k]].value_name, //old_value_name
                                        'editlistAttributes', //parent
                                        indexValues, temp, descr); //indice
                                indexValues = indexValues + 1;
                                $('#editlistAttributes').append(content);
                            }
                            k++;
                            $('.HIDE').hide();
                        }
                        if (z == (k - 1)) {
                            flag_modify_exist_rule = true;
                        }

                        checkEditAtlistOneAttributeM();
                        checkAddModelConditions();
                        $(".Hidebutton").hide();
                        $('.Input_readoonly').attr('readonly', true);
                        $('.Select_readoonly').prop('disabled', true);
                        $('#editModelLoadingIcon').hide();
                    },
                    error: function (data)
                    {
                        alert("Error in reading data from the database<br/> Please get in touch with the Snap4city Administrator");
                        $('#editlistAttributes').html("");
                    }
                });
            }
        });
    }
    $('#FIWAREModelTable tbody').on('click', 'button.editDashBtn', function () {
        get_ready_form_edit_view($(this), false);
    });
    $('#FIWAREModelTable tbody').on('click', 'button.viewDashBtn', function () {
        get_ready_form_edit_view($(this), true);
    });
//Update attributes
    var num1 = 0;
    var tot_attr = [];
//Set the payload and information 
    $("#editModelConfirmBtn").off("click").click(function () {
        var change = {};
        var id = modelName;
        var version = Mversion;
        var domain = modelDomain;
        var subdomain = modelSubDomain;
        msg_whatiswrong = '';
        var regex = /[^a-z0-9_-]/gi;
        var someNameisWrong = false;
        num1 = document.querySelector("#editlistAttributes").childElementCount;
        for (var m = 0; m < (num1); m++) {
            var attr = {};
            var value_name = document.querySelector("#value_name" + m + "").value;
            attr[value_name] = {
                data_type: document.querySelector("#data_type" + m + "").value,
                value_type: document.querySelector("#value_type" + m + "").value,
                value_unit: document.querySelector("#value_unit" + m + "").value
            };
            tot_attr.push(attr);
            if (attr.data_type != "" && attr.value_type != "" && attr.editable != ""
                    && attr.value_unit != "" && attr.healthiness_criteria != "" && attr.healthiness_value != "")
                Object.assign(change, attr);
            else
                someNameisWrong = true;
        }
//API to update
        if (!someNameisWrong) {
            document.getElementById('editlistAttributes').innerHTML = "";
            $("#editModelModalTabs").hide();
            $('#editModelModal div.modalCell').hide();
            $('#editModelLoadingMsg').show();
            $('#editModelLoadingIcon').show();
            $("#editModelCancelBtn").hide();
            $("#editModelConfirmBtn").hide();
            $("#editModelModalBody").hide();
            if (!$('#selectSubnature').val()) {
                var subNat = null;
            } else {
                var subNat = $('#selectSubnature').val();

            }

            Data_call = {
                action: "update_values_attributes_fiware",
                token: sessionToken,
                id: id,
                version: version,
                domain: domain,
                subdomain: subdomain,
                change: JSON.stringify(change),
                subNat: subNat,
                make_rule: true
            };

            $.ajax({
                url: "../api/model.php",
                data: Data_call,
                type: "POST",
                async: false,
                dataType: 'json',
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
                    ErrorConfirmEdit(data);
                }
            });
        } else {
            alert("Check the values of your model, make sure that data you entered are valid" + msg_whatiswrong);
        }

    });
    function ErrorConfirmEdit(data) {
        console.log("Ko result: " + JSON.stringify(data));
        $('#editModelLoadingMsg').hide();
        $('#editModelLoadingIcon').hide();
        $('#editModelOkMsg').hide();
        $('#editModelOkIcon').hide();
        $('#editModelKoMsg').show();
        $('#editModelKoIcon').show();
        $('#editModelOkBtn').show();
        $('#editlistAttributes').html("");
        $('#modelTable').DataTable().destroy();
        fetch_data(true);
    }
//END EDIT MODEL 

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
});