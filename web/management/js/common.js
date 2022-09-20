//------------------------------------------------------------------------------------------------------------------- Rules Load
//Function to create rules -///

function getIfRules2(num1) {
    var attributesIfValues = [];
    for (var m = 0; m < num1; m++) {
        //var attribute= document.getElementById('ifBlockTable').rows[m].cells[1].selectedIndex;

        var fieldIf = document.getElementById('ifBlockTable').tBodies[0].rows.item(m).cells.item(1).childNodes[0].value;
        var operatorIf = document.getElementById('ifBlockTable').tBodies[0].rows.item(m).cells.item(2).childNodes[0].value;
        var valueIf = document.getElementById('ifBlockTable').tBodies[0].childNodes[m].childNodes[3].childNodes[0].value;

        if (valueIf.localeCompare("Empty") == 0) {
            valueIf = "";
        }

        var newIf = {"field": fieldIf, "operator": operatorIf, "value": valueIf};
        attributesIfValues.push(newIf);
    }
    return attributesIfValues;
}

function getThenRules2(num2) {
    var attributesThenValues = [];

    for (var m = 0; m < num2; m++) {
        var fieldsThen = document.getElementById('decisionBlockTable').tBodies[0].rows.item(m).cells.item(1).childNodes[0].value;

        if (fieldsThen != "empty") {

            var valueThen = document.getElementById('decisionBlockTable').tBodies[0].rows.item(m).cells.item(2).childNodes[0].value;


            if (valueThen.localeCompare("Empty") == 0) {
                valueThen = "";
            }
            var newThen = {"field": fieldsThen, "valueThen": valueThen};
            attributesThenValues.push(newThen);
        }
    }
    return attributesThenValues;
}




/*get field call*/
function getFields(fieldIf, pos, id, value) {
    if (fieldIf == 'cb') {
        fieldIf = "contextBroker";
    }
    $.ajax({
        url: "../api/bulkDeviceUpdate.php",
        data: {
            action: "get_fields",
            IfThenFieldValue: value,
            fieldIf: fieldIf,
            token: sessionToken
        },
        dataType: 'json',
        type: "POST",
        async: true,
        success: function (myData) {

            let myDataP = myData['content'];
            //console.log(JSON.stringify(myDataP));
            //console.log("INDICE "+ pos + " field if "+ fieldIf);

            if (fieldIf.localeCompare("healthiness_value") == 0 && healthinessValueIsPresent(id) == 0) {
                var num1 = document.getElementById(id).tBodies[0].childElementCount;
                var idToPut = document.getElementById(id).tBodies[0].rows.item(pos).id;

                if (id.localeCompare("decisionBlockTableValue") == 0) {
                    var row = $('<tr id="' + idToPut + 'criteria"><td><h3><span class="label label-success">Then</span></h3></td><td class="fieldTdThenValue"><select><option value="healthiness_criteria">Healthiness Criteria</option></select></td></td><td class="fieldNameValue"><input type="text" class="fieldNameIfValue" value="Empty"><td></td></tr>');
                    devicenamesArray['then'] = devicenamesArray['then'] + 1;

                } else {
                    var row = $('<tr id="' + idToPut + 'criteria"><td><h3><span class="label label-danger">AND</span></h3></td><td class="fieldTdValue"><select><option value="healthiness_criteria">Healthiness Criteria</option></select></td><td class="fieldEqualValue"><select class="fieldSelectEqualValue"><option value="IsEqual">Is Equal</option><option value="IsNotEqual">Is Not Equal</option><option value="IsNull">Is NULL</option></select></td><td class="fieldNameIfValue"></td><td></td></tr>');
                    devicenamesArray['if'] = devicenamesArray['if'] + 1;
                }

                var idTemp = "#" + id + " tbody ";
                //$(idTemp).eq(pos+1).append(row);
                $(idTemp).append(row);


                $('#authorizedPagesJson').val(JSON.stringify(ifPages));


                getFields("healthiness_criteria", num1, id, value);




                if (id.localeCompare("decisionBlockTable") == 0 || id.localeCompare("decisionBlockTableValue") == 0) {
                    document.getElementById(id).tBodies[0].rows.item(pos).cells.item(2).innerHTML = myDataP[0].fieldsHtml;
                } else {
                    //TO change
                    document.getElementById(id).tBodies[0].childNodes[pos].childNodes[3].innerHTML = myDataP[0].fieldsHtml;
                }
            } else {

                if (id.localeCompare("decisionBlockTable") == 0 || id.localeCompare("decisionBlockTableValue") == 0) {
                    document.getElementById(id).tBodies[0].rows.item(pos).cells.item(2).innerHTML = myDataP[0].fieldsHtml;

                } else {
                    //TO change
                    document.getElementById(id).tBodies[0].childNodes[pos].childNodes[3].innerHTML = myDataP[0].fieldsHtml;


                    if (pos == "decisionBlockTable") {
                        getAffectedRows();
                    } else if (pos == "decisionBlockTableValue" || value == 1) {
                        getAffectedRowsValue();
                    }

                }
            }



        },
        error: function (myData) {
            console.log("error" + JSON.stringify(myData));
            if (id.localeCompare("decisionBlockTable") == 0) {
                document.getElementById(id).tBodies[0].rows.item(pos).cells.item(2).innerHTML = "<input type=\"text\" class=\"fieldNameThen\" value=\"Empty\">";
            } else {
                document.getElementById(id).tBodies[0].childNodes[pos].childNodes[3].innerHTML = "<input type=\"text\" class=\"fieldNameIf\" value=\"Empty\">";
            }

        }
    });//end of ajax get_affected
}

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



function RulesLoad(name, if_st, then_st, mode, modalif, modalthen) {
    if (name.includes("_", 3)) {
        var If_st = JSON.parse(unescape(if_st).replaceAll('/"', ''));
        var Then_st = JSON.parse(unescape(then_st).replaceAll('/"', ''));
    } else {
        var If_st = JSON.parse(if_st.replaceAll('/"', ''));
        var Then_st = JSON.parse(then_st.replaceAll('/"', ''));
    }

    //clear the form
    $('.ifrow').remove();
    $('.Thenrow').remove();

    //setting index
    valueNamesArray['if'] = 0;
    valueNamesArray['then'] = 0;
    var idCounterThen = 0;
    var idCounterIf = 0;

    // create a number of row equal a number of If_st    
    for (let i = 0; i < Object.keys((If_st)).length; i++) {
        let str = '#ifHV' + i;

        if (i == 0) {

            var row1 = $('<tr id="ifHV' + idCounterIf + '" class="ifrow"><td><h3><span class="label label-danger">IF</span></h3></td><td class="fieldTdValue"><select class="fieldIfValue"><option value="empty">--Select an option--</option><option value="cb" >Contextbroker</option><option value="device">Device name</option><option value="deviceType" selected>Device type</option><option value="value_name">Value Name</option><option value="data_type">Data type</option><option value="model">Model</option><option value="producer">Producer</option><option value="frequency">Frequency</option><option value="kind">Kind</option><option value="protocol">Protocol</option><option value="format">Format</option><option value="latitude">Latitude</option><option value="longitude">Longitude</option><option value="macaddress">Mac address</option><option value="k1">Key1</option><option value="k2">Key2</option><option value="value_type">Value type</option><option value="value_unit">Value unit</option><option value="editable">Editable</option><option value="healthiness_criteria">Healthiness criteria</option><option value="healthiness_value">Healthiness value</option></select></td><td class="fieldEqualValue"><select class="fieldSelectEqualValue"><option value="IsEqual">Is Equal</option><option value="IsNotEqual">Is Not Equal</option><option value="IsNull">Is NULL</option><option value="Contains">Contains</option></select></td><td class="fieldNameIfValue"><input type="hidden"></td><td><i class="fa fa-minus"></i></td></tr>');

            $('#' + modalif + ' tbody').append(row1);

            devicenamesArray['if'] = devicenamesArray['if'] + 1;
            var rowIndex = row1.index();
            $(str + ' > td.fieldTdValue > select').val(If_st[i].field);
            getFields(If_st[i].field, i, "ifBlockTableValue", If_st[i].value);

            idCounterIf++;

        } else {

            var row2 = $('<tr id="ifHV' + idCounterIf + '" class="ifrow"><td><h3><span class="label label-danger">AND</span></h3></td><td class="fieldTdValue"><select class="fieldIfValue"><option value="empty">--Select an option--</option><option value="cb" >Contextbroker</option><option value="device">Device name</option><option value="deviceType" selected>Device type</option><option value="value_name">Value Name</option><option value="data_type">Data type</option><option value="model">Model</option><option value="producer">Producer</option><option value="frequency">Frequency</option><option value="kind">Kind</option><option value="protocol">Protocol</option><option value="format">Format</option><option value="latitude">Latitude</option><option value="longitude">Longitude</option><option value="macaddress">Mac address</option><option value="k1">Key1</option><option value="k2">Key2</option><option value="value_type">Value type</option><option value="value_unit">Value unit</option><option value="editable">Editable</option><option value="healthiness_criteria">Healthiness criteria</option><option value="healthiness_value">Healthiness value</option></select></td><td class="fieldEqualValue"><select class="fieldSelectEqualValue"><option value="IsEqual">Is Equal</option><option value="IsNotEqual">Is Not Equal</option><option value="IsNull">Is NULL</option><option value="Contains">Contains</option></select></td><td class="fieldNameIfValue"><input type="hidden"></td><td><i class="fa fa-minus"></i></td></tr>');

            $('#' + modalif + ' tbody').append(row2);
            devicenamesArray['if'] = devicenamesArray['if'] + 1;
            var rowIndex = row2.index();
            $(str + ' > td.fieldTdValue > select').val(If_st[i].field);
            getFields(If_st[i].field, i, "ifBlockTableValue", If_st[i].value);


            idCounterIf++;
        }
    }


    // create a number of row equal a number of Then_st    
    for (let i = 0; i < Object.keys((Then_st)).length; i++) {

        var row = $('<tr id="thenHV' + idCounterThen + '" class="Thenrow" ><td><h3><span class="label label-success">Then</span></h3></td><td class="fieldTdThenValue"><select class="fieldThenValue"><option value="empty">--Select an option--</option><option value="data_type">Data type</option><option value="value_type">Value type</option><option value="value_unit">Value unit</option><option value="editable">Editable</option><option value="producer">Producer</option>	<option value="healthiness_criteria">Healthiness criteria</option><option value="healthiness_value">Healthiness value</option></select></td></td><td class="fieldNameValue"><input type="text" class="fieldNameIfValue" value="Empty"><td><i class="fa fa-minus"></i></td></tr>');
        $('#' + modalthen + '  tbody').append(row);
        idCounterThen++;
        valueNamesArray['then'] = valueNamesArray['then'] + 1;
    }
    //Select Then statement
    for (let i = 0; i < Object.keys((Then_st)).length; i++) {

        let str = '#thenHV' + i;
        $(str + '> td.fieldTdThenValue  > select').val(Then_st[i].field);
        getFields(Then_st[i].field, i, "decisionBlockTableValue", Then_st[i].valueThen);

    }


    //Select mode
    $('#Update_mode').val(parseInt(mode));



}




//---------------------------------------------------------------------------------------------------------------------------------------------managing valuetype-valueunit

//TODO uniform with below
function valueTypeChanged(indice) {

    //get new value type that has been selected
    valueTypeNew = $("#value_type" + indice).val();

    //remove old value units
    valueUnitNew = $("#value_unit" + indice).find("option").remove().end();
    DataTypeNew = $("#data_type" + indice).find("option").remove().end();

    //retrieve valid value units basing on new value type selected (selected value unit is discarged)
    validValueUnit = getValidValueUnit(valueTypeNew, "");
    validDataType = getValidDataType(valueTypeNew, "");

    //if there are any valid value units, present to the users
    if (validValueUnit !== "") {
        if (!validValueUnit.includes('selected')) {
            valueUnitNew.append("<option hidden disabled selected value=\"NOT VALID OPTION\"> -- select an option -- </option>");
            //update msg_value_unit
            $("#value_unit" + indice).parent().siblings().last().css("color", "red");
            $("#value_unit" + indice).parent().siblings().last().html("Value unit is mandatory");
        }
        valueUnitNew.append(validValueUnit);
    }

    if (validDataType !== "") {
        if (!validDataType.includes('selected')) {
            DataTypeNew.append("<option hidden disabled selected value=\"NOT VALID OPTION\"> -- select an option -- </option>");
            //update msg_value_unit
            $("#data_type" + indice).parent().siblings().last().css("color", "red");
            $("#data_type" + indice).parent().siblings().last().html("Data type is mandatory");
        }
        DataTypeNew.append(validDataType);
    }
    //update msg_value_type
    $("#value_type" + indice).parent().siblings().last().css("color", "#337ab7");
    $("#value_type" + indice).parent().siblings().last().html("Ok");
//    $("#data_type" + indice).parent().siblings().last().css("color", "#337ab7");
//    $("#data_type" + indice).parent().siblings().last().html("Ok");
}
//function DataTypeChanged(indice) {

//update msg_value_unit
//    $("#data_type" + indice).parent().siblings().last().css("color", "#337ab7");
//    $("#data_type" + indice).parent().siblings().last().html("Ok");
//}
function valueUnitChanged(indice) {

    //update msg_value_unit
    $("#value_unit" + indice).parent().siblings().last().css("color", "#337ab7");
    $("#value_unit" + indice).parent().siblings().last().html("Ok");
}

function dataTypeChanged(indice) {

    //update msg_value_unit
    $("#data_type" + indice).parent().siblings().last().css("color", "#337ab7");
    $("#data_type" + indice).parent().siblings().last().html("Ok");
}

//TODO unifrom with above, used in extract
function valueTypeChangedM(indice) {

    //get new value type that has been selected
    valueTypeNew = $("#Mvalue_type" + indice).val();


    //remove old value units
    valueUnitNew = $("#Mvalue_unit" + indice).find("option").remove().end();

    // validDataType=  $("#Mdata_type" + indice).find("mydatatype").remove().end();

    //retrieve valid value units basing on new value type selected (selected value unit is discarged)
    validValueUnit = getValidValueUnit(valueTypeNew, "");
    // validDataType =  getValidDataType(valueTypeNew, "");

    //if there are any valid value units, present to the users
    if (validValueUnit !== "") {
        if (!validValueUnit.includes('selected')) {
            valueUnitNew.append("<option hidden disabled selected value=\"NOT VALID OPTION\"> -- select an option -- </option>");
            //update msg_value_unit
            $("#Mvalue_unit" + indice).parent().siblings().last().css("color", "red");
            $("#Mvalue_unit" + indice).parent().siblings().last().html("Value unit is mandatory");
        }
        valueUnitNew.append(validValueUnit);
    }

// if (validDataType !== "") {
//        if (!validDataType.includes('selected')) {
//            DataTypeNew.append("<option hidden disabled selected value=\"NOT VALID OPTION\"> -- select an option -- </option>");
//            //update msg_value_unit
//            $("#Mvalue_unit" + indice).parent().siblings().last().css("color", "red");
//            $("#Mvalue_unit" + indice).parent().siblings().last().html("Value unit is mandatory");
//        }
//        DataTypeNew.append(validDataType);
//    }


    //update msg_value_type
    $("#Mvalue_type" + indice).parent().siblings().last().css("color", "#337ab7");
    $("#Mvalue_type" + indice).parent().siblings().last().html("Ok");
}

function valueUnitChangedM(indice) {

    //update msg_value_unit
    $("#Mvalue_unit" + indice).parent().siblings().last().css("color", "#337ab7");
    $("#Mvalue_unit" + indice).parent().siblings().last().html("Ok");
}


function getValidDataType(valueType, data_type) {

    valueTypeObj = "";
    toReturn = "";

    //get value type STRUCTURE for passed valueType
    for (var n = 0; n < gb_value_types.length; n++)
        if (gb_value_types[n].value === valueType)
            valueTypeObj = gb_value_types[n];

    //console.log("Get validValueUnit for "+valueType);
    //console.log("Accepte valueUnit are"+JSON.stringify(valueTypeObj));

    //add new value unit OPTIONS according to new value type STRUCTURE
    if (valueTypeObj !== "")
    {
        if (valueTypeObj.data_type_value.length === 0)
        {
            toReturn += "<option selected value=\"-\">-</option>";//by default insert -
        } else
            for (var n = 0; n < valueTypeObj.data_type_value.length; n++)
            {
//                for (var j = 0; j < valueTypeObj.children_value.length; j++)
//                {
//                    if (valueTypeObj.data_type_value[j] === gb_datatypes[n])
//                    {
                //if (( gb_value_types[n].data_type_value === data_type) )
                if ((valueTypeObj.data_type_value[n] === data_type))
                    toReturn += "<option selected value=\"" + valueTypeObj.data_type_value[n] + "\">" + valueTypeObj.data_type_value[n] + " </option>";
                else
                    toReturn += "<option value=\"" + valueTypeObj.data_type_value[n] + "\">" + valueTypeObj.data_type_value[n] + "</option>";
//                   }
//                }
            }
    }

    return toReturn;
}
;



function getValidValueUnit(valueType, selectedValueUnit) {

    valueTypeObj = "";
    toReturn = "";

    //get value type STRUCTURE for passed valueType
    for (var n = 0; n < gb_value_types.length; n++)
        if (gb_value_types[n].value === valueType)
            valueTypeObj = gb_value_types[n];

    //console.log("Get validValueUnit for "+valueType);
    //console.log("Accepte valueUnit are"+JSON.stringify(valueTypeObj));

    //add new value unit OPTIONS according to new value type STRUCTURE
    if (valueTypeObj !== "")
    {
        if (valueTypeObj.children_value.length === 0)
        {
            toReturn += "<option selected value=\"-\">-</option>";//by default insert -
        } else
            for (var n = 0; n < gb_value_units.length; n++)
            {
                for (var j = 0; j < valueTypeObj.children_value.length; j++)
                {
                    if (valueTypeObj.children_value[j] === gb_value_units[n].value)
                    {
                        if ((gb_value_units[n].value === selectedValueUnit) || (valueTypeObj.children_value.length == 1))
                            toReturn += "<option selected value=\"" + gb_value_units[n].value + "\">" + gb_value_units[n].label + " (" + gb_value_units[n].value + ")</option>";
                        else
                            toReturn += "<option value=\"" + gb_value_units[n].value + "\">" + gb_value_units[n].label + " (" + gb_value_units[n].value + ")</option>";
                    }
                }
            }
    }

    return toReturn;
}
;

//--------------------------------------------------------------------------------------------------------------------------managing static attributes/subnature
var select2option = {
    placeholder: 'Select an option',
    width: 400,
    allowClear: true,
    closeOnSelect: false
};



function checkSubnatureChanged(element, old, nuovo, info, edit) {
    if ((old != "") && (!confirm("Are you sure you want to change the subnature? You will lose all the attributes inserted so far!"))) {
        info.preventDefault();
        element.select2("close");
        return;                  //abort!
    }
    element.val(nuovo);
    element.select2(select2option).select2("close");
    subnatureChanged(edit);
}

//called on edit + add
function subnatureChanged(edit, staticAttributes) {

    removeStaticAttributes(edit);
    updateIsMobile(edit, staticAttributes);//isMobile attributes is done separatly


    //get new subnature that has been selected
    if (edit) {
        subnatureNew = $("#selectSubnatureM").val();
        if (subnatureNew === "") {
            $("#addNewStaticBtnM").hide();
        } else if (checkIsMobile(staticAttributes)) {
            document.querySelector("#addNewStaticBtnM").disabled = true;
        } else {
            $("#addNewStaticBtnM").show();
        }
    } else if (edit == "view") {
        $('#removeCBServiceBtn').hide();

    } else {
        subnatureNew = $("#selectSubnature").val();
        if (subnatureNew === "") {
            $("#addNewStaticBtn").hide();

        } else if (checkIsMobile(staticAttributes)) {
            document.querySelector("#addNewStaticBtn").disabled = true;
        } else {
            $("#addNewStaticBtn").show();
        }
    }


    if (subnatureNew !== "") {
        //retrieve new avaialbaility
        $.ajax({
            url: "../api/device.php",
            data: {
                action: 'get_available_static',
                token: sessionToken,
                subnature: subnatureNew
            },
            type: "POST",
            async: true,
            dataType: 'json',
            success: function (mydata)
            {
                if (mydata["status"] === 'ok')
                {
                    //if called in edit, populate static attributes
                    if (edit) {
                        currentDictionaryStaticAttribEdit = JSON.parse(mydata["availibility"]);
                        if (staticAttributes)
                        {
                            for (let i = 0; i < staticAttributes.length; i++) {
                                createRowElem(staticAttributes[i][0], staticAttributes[i][1], currentDictionaryStaticAttribEdit, "editlistStaticAttributes");
                                if (edit == "view") {
                                    $('.removeCBServiceBtnView').hide();
                                    $('.Select_onlyread').prop('disabled', true);
                                    $('.Input_onlyread').attr('readonly', true);
                                }
                            }
                        }
                    } else {
                        currentDictionaryStaticAttribAdd = JSON.parse(mydata["availibility"]);
                        if (staticAttributes)
                        {
                            for (let i = 0; i < staticAttributes.length; i++) {
                                createRowElem(staticAttributes[i][0], staticAttributes[i][1], currentDictionaryStaticAttribAdd, "addlistStaticAttributes");
                                if (edit == "view") {
                                    $('.removeCBServiceBtnView').hide();
                                    $('.Select_onlyread').prop('disabled', true);
                                    $('.Input_onlyread').attr('readonly', true);
                                }
                            }

                        }
                        $("#addNewStaticBtn").show();

                    }
                } else
                {
                    console.log(JSON.stringify(mydata));
                    alert("Unknown error. <br/> Get in touch with the Snap4City Administrator<br/>" + JSON.stringify(mydata));
                    $("#addNewStaticBtn").hide();
                }
            },
            error: function (mydata)
            {
                console.log(JSON.stringify(mydata));
                alert("Network errors. <br/> Get in touch with the Snap4City Administrator<br/>" + JSON.stringify(mydata));
                $("#addNewStaticBtn").hide();
            }
        });
    }
}

//called on edit + add
function removeStaticAttributes(edit) {

    if (edit)
        removetab = "editStaticTabModel";
    else
        removetab = "addStaticTabModel";

    //remove any old value for static attributes
    $('#' + removetab + ' div[name="additionalRow"]').each(function () {
        this.remove();
    });
}

//called on edit + add
function createRowElem(initialValueDictiornary, initialValue, currentDictionaryStaticAttrib, element) {
    // creation of the components of a row element
    var row = document.createElement("div");
    $(row).attr('class', 'row');
    $(row).attr('name', 'additionalRow');
    $(row).attr('style', 'border:2px solid blue');


    //selection
    var modalCell0 = document.createElement("div");
    $(modalCell0).attr('class', 'col-xs-12 col-md-4 modalCell');

    var modalFieldCnt0 = document.createElement("div");
    $(modalFieldCnt0).attr('class', 'modalFieldCnt ');

    //console.log("previously already addeed:"+retrieveStaticAttributes(element));
    var alreadyInserted = retrieveStaticAttributes(element, true);

    var atLeastOneEntry = false;
    var atLeastOneSelected = false;
    var modalInputTxt0 = document.createElement("select");
    $(modalInputTxt0).attr('class', ' Select_onlyread');
    for (var i = 0; i < currentDictionaryStaticAttrib.length; i++) {
        if (((currentDictionaryStaticAttrib[i].type === "http://www.w3.org/2001/XMLSchema#string") || (currentDictionaryStaticAttrib[i].type === "https://www.w3.org/2001/XMLSchema#integer")) &&
                (checkNotInsert(alreadyInserted, currentDictionaryStaticAttrib[i].uri)) &&
                (currentDictionaryStaticAttrib[i].uri !== "http://www.disit.org/km4city/schema#isMobile"))//isMobile management outside 
        {
            var option = document.createElement("option");
            option.value = currentDictionaryStaticAttrib[i].uri;
            option.text = currentDictionaryStaticAttrib[i].label;
            if (initialValueDictiornary === option.value) {
                option.selected = true;
                atLeastOneSelected = true;
            }
            modalInputTxt0.appendChild(option);
            atLeastOneEntry = true;
        }
    }

    if ((initialValueDictiornary != "") && (!atLeastOneSelected))
        atLeastOneEntry = false;

    //enter text
    var modalCell1 = document.createElement("div");
    $(modalCell1).attr('class', 'col-xs-12 col-md-4 modalCell');

    var modalFieldCnt1 = document.createElement("div");
    $(modalFieldCnt1).attr('class', 'modalFieldCnt');

    var modalInputTxt = document.createElement("input");
    $(modalInputTxt).attr('type', 'text');
    $(modalInputTxt).attr('class', 'modalInputTxt Input_onlyread');
    $(modalInputTxt).attr('onkeyup', 'checkStrangeCharacters(this)');
    $(modalInputTxt).val(initialValue);

    var modalFieldLabelCnt = document.createElement("div");
    $(modalFieldLabelCnt).attr('class', 'modalFieldLabelCnt');
    $(modalFieldLabelCnt).text("Value");

    //remove button
    var modalCell2 = document.createElement("div");
    $(modalCell2).attr('class', 'col-xs-12 col-md-4 modalCell');

    var modalFieldCnt2 = document.createElement("div");
    $(modalFieldCnt2).attr('class', 'modalFieldCnt');

    var rmButton = document.createElement("button");
    $(rmButton).attr('type', 'text');
    $(rmButton).attr('id', 'removeCBServiceBtn');
    $(rmButton).attr('name', 'removeCBServiceBtn');
    $(rmButton).attr('class', 'btn btn-danger removeCBServiceBtnView');
    $(rmButton).text("Remove");

    rmButton.addEventListener('click', function () {
        row.remove()
    });

    // row element composition
    $(row).append(modalCell0);
    $(modalCell0).append(modalFieldCnt0);
    $(modalFieldCnt0).append(modalInputTxt0);
    $(row).append(modalCell1);
    $(modalCell1).append(modalFieldCnt1);
    $(modalFieldCnt1).append(modalInputTxt);
    $(modalCell1).append(modalFieldLabelCnt);
    $(row).append(modalCell2)
    $(modalCell2).append(modalFieldCnt2);
    $(modalFieldCnt2).append(rmButton);

    // get static tab
    if (atLeastOneEntry) {
        var stTab = $("#" + element).last();
        stTab.append(row);
        //stTab.parent().parent().scrollTop(100000);
    }
}

//called on edit + add
//if all is true, it return also the entryes that have no value... to be used to present a new valid entry
function retrieveStaticAttributes(source, all, isMobileTick) {
    var staticArr = $('#' + source + ' div[name="additionalRow"]').find("select");
    var staticArr2 = $('#' + source + ' div[name="additionalRow"]').find("input");
    var staticValues = [];
    for (let i = 0; i < staticArr.length; i++) {
        if ((staticArr2[i].value) || (all !== true)) {
            var array = [];
            array.push(staticArr[i].value);
            array.push(staticArr2[i].value);
            try {
                array.push($(staticArr[i]).find('option[value="' + staticArr[i].value + '"]').text());
            } catch (lpe) {
            }
            staticValues.push(array);
        }
    }
    if ((isMobileTick !== undefined) && $('#' + isMobileTick).is(':checked')) {			//management of the isMobile attributes is done differently
        var array = [];
        array.push("http://www.disit.org/km4city/schema#isMobile");
        array.push("true");
        staticValues.push(array);
    }
    return staticValues;
}

function checkNotInsert(inserted, check) {
    for (let i = 0; i < inserted.length; i++) {
        //console.log("chcek against"+(inserted[0]));
        if (inserted[i][0] === check)
            return false;
    }
    return true;
}

function addSubnature(element, data) {
    $.each(data, function () {
        element.append("<option value='" + this.value + "'>" + this.label + "&#160;&#160;&#160;<font size=\"2\">(" + this.parent_value[0] + ")</font>" + "</option>");
    });
    element.select2(select2option);
}

function verifySubnature(subnature, static_attributes) {

    var tocheck = JSON.parse(static_attributes);
    var toreturn = true;

    //retrieve avaialbaility
    $.ajax({
        url: "../api/device.php",
        data: {
            action: 'get_available_static',
            token: sessionToken,
            subnature: subnature
        },
        type: "POST",
        async: false,
        dataType: 'json',
        success: function (mydata)
        {
            if (mydata["status"] === 'ok')
            {
                currentDictionary = JSON.parse(mydata["availibility"]);
                for (let  i = 0; i < tocheck.length; i++) {
                    var found = false;
                    for (let j = 0; j < currentDictionary.length; j++) {
                        if (tocheck[i][0] == currentDictionary[j].uri) {
                            found = true;
                            break;
                        }
                    }
                    if (!found) {
                        toreturn = false;
                        return;
                    }
                }
            } else
            {
                console.log(JSON.stringify(mydata));
                alert("Unknown error. <br/> Get in touch with the Snap4City Administrator<br/>" + JSON.stringify(mydata));
                $("#addNewStaticBtn").hide();
            }
        },
        error: function (mydata)
        {
            console.log(JSON.stringify(mydata));
            alert("Network errors. <br/> Get in touch with the Snap4City Administrator<br/>" + JSON.stringify(mydata));
            $("#addNewStaticBtn").hide();
        }
    });

    return toreturn;

}

//------------------------------------------------------------------------------------------------------------------- common routine 
function addModel(element, data) {
    $.each(data['content'], function () {
        element.append("<option data_key=" + this.kgenerator + " data_subnature='" + this.subnature + "' data_static=" + btoa(this.static_attributes) + " value='" + this.name + "'>" + this.name + "</option>");
    });



}


function LoadAttr(kindModel, nameOpt, selectednameOpt, nameOptValue, version, domain, subdomain) {
    if ((nameOptValue != "custom") && (nameOptValue != ""))
            //if (nameOpt[selectednameOpt].value !="custom") 
            {
                $("#addNewDeviceGenerateKeyBtn").hide();
                var gb_device = document.getElementById('inputNameDevice').value;
                var gb_latitude = document.getElementById('inputLatitudeDevice').value;
                var gb_longitude = document.getElementById('inputLongitudeDevice').value;
                if (nameOpt[selectednameOpt].getAttribute("data_key") != "special") // && ownerSelect[ownerOpt].value=='private')
                {
                    if ($("#KeyOneDeviceUser").val() == "")
                    {
                        $("#sigFoxDeviceUserMsg").val("");
                        $("#KeyOneDeviceUserMsg").html("");
                        $("#KeyTwoDeviceUserMsg").html("");
                        // $("#sigFoxDeviceUserMsg").html("These keys have been generated automatically for your device. Keep track of them. Details on <a href=\"https://www.snap4city.org/drupal/node/76\">info</a>");
                        $("#KeyOneDeviceUser").val(generateUUID());
                        $("#KeyTwoDeviceUser").val(generateUUID());
                    }
                }
                if (nameOpt[selectednameOpt].getAttribute("data_key") == "special") // && ownerSelect[ownerOpt].value=='private')
                {
                    $("#sigFoxDeviceUserMsg").html("Generate in your SigFox server the keys and report them here.  Details on <a href=\"https://www.snap4city.org/drupal/node/76\">info</a>");
                    $("#KeyOneDeviceUser").val("");
                    $("#KeyTwoDeviceUser").val("");
                }

                //if(nameOpt[selectednameOpt].value !="custom" && nameOpt[selectednameOpt].value!="")
                //{ 

                if (kindModel == 'NATIVE') {
                    $.ajax({
                        url: "../api/model.php",
                        data: {
                            action: "get_model",
                            name: nameOptValue,
                            token: sessionToken
                        },
                        type: "POST",
                        async: true,
                        datatype: 'json',
                        success: function (data) {
                            SuccessOfLoadAttr(data, kindModel, '', '', '');
                        },
                        error: function (data)
                        {
                            ErrorManager(data);
                        }
                    });
                } else {
                    $.ajax({
                        url: "../api/model.php",
                        data: {
                            action: "get_value_attributes_FIWIRE",
                            id: nameOptValue,
                            version: version,
                            domain: domain,
                            subdomain: subdomain,
                            token: sessionToken
                        },
                        type: "POST",
                        async: true,
                        datatype: 'json',
                        success: function (data)
                        {
                            SuccessOfLoadAttr(data, kindModel, version, domain, subdomain);
                        },
                        error: function (data)
                        {
                            ErrorManager(data);
                        }
                    });
                }
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
        //$('#selectProtocolDevice').val("");
        //$('#selectFormatDevice').val(""); 
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
        checkAddDeviceConditions();
        addDeviceConditionsArray['inputFrequencyDevice'] = false;
        checkFrequencyType();
        checkAddDeviceConditions();
        addDeviceConditionsArray['inputMacDevice'] = false;
        checkMAC();
        checkAddDeviceConditions();
        document.getElementById('addlistAttributes').innerHTML = "";
        $("#addNewDeviceGenerateKeyBtn").hide();
        checkAtlistOneAttribute();
    } else {// case custom 
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
}


function addModel2(element, data, kind) {



    if (kind == 'NATIVE' && element.selector != $("#selectModelDevice").selector) {
        $.each(data, function () {
            element.append("<option data_key= '" + this.kgenerator + "' data_contextbroker='" + this.contextbroker + "' data_service='" + this.service + "' data_servicePath='" + this.servicePath + "' data_subnature='" + this.subnature + "' data_kind='" + kind + "' data_attributes='" + this.attributes + "' data_static= '" + btoa(this.static_attributes) + "'value='" + this.name + "'>" + this.name + "&#160;&#160;&#160;<font size=\"2\">( " + kind + " )</font>" + "</option>");
        });
        element.select2(select2option);
    } else if (kind == 'NATIVE' && element.selector == $("#selectModelDevice").selector) {
        $.each(data, function () {
            element.append("<option data_key= '" + this.kgenerator + "' data_subnature='" + this.subnature + "' data_kind='" + kind + "' data_attributes='" + this.attributes + "' data_static= '" + btoa(this.static_attributes) + "'value='" + this.name + "'>" + this.name + "&#160;&#160;&#160;<font size=\"2\">( " + kind + " )</font>" + "</option>");
        });
        element.select2(select2option);
    } else if (kind != 'NATIVE' && element.selector != $("#selectModel").selector) {


        $.each(data, function () {
            label = "FIWIRE RULED";
            $.each(JSON.parse(this.attributes), function () {
                if (this.checked == 'False') {
                    label = "FIWARE UNRULED";
                    return false;
                }
            });

            element.append("<option data-version='" + this.version + "' data_kind='" + kind + "'data-modelSubDomain='" + this.subdomain + "'data-Domain='" + this.domain + "'value='" + this.model + "'>" + this.model + "&#160;&#160;&#160;<font size=\"2\">( " + label + " )</font>" + "</option>");
        });
        element.select2(select2option);
    } else {
        $.each(data, function () {
            label = "FIWIRE RULED";
            $.each(JSON.parse(this.attributes), function () {
                if (this.checked == 'False') {
                    label = "FIWARE UNRULED";
                    return false;
                }
            });
            if (label != "FIWARE UNRULED") {
                element.append("<option data_kindDEVICE= 'sensor'  data-version='" + this.version + "' data_kind='FIWIRE' data-modelSubDomain='" + this.subdomain + "'data-Domain='" + this.domain + "'value='" + this.model + "'>" + this.model + "&#160;&#160;&#160;<font size=\"2\">( " + label + " )</font>" + "</option>");
            }
        });
        element.select2(select2option);
    }



}
// format attribute for FIWIRE 
function keepAttr_FIWIRE(full_attr_field) {
    var full_attr = JSON.parse(full_attr_field);
    var k = Object.keys(full_attr);
    var element = [];
 

    for (var i = 0; i < k.length; i++) {
        
         chiave = k[i];
         if(full_attr[chiave].value_name!='type'){


        element.push({
            "value_name": full_attr[chiave].value_name,
            "data_type": full_attr[chiave].data_type,
            "value_type": full_attr[chiave].value_type,
            "editable": full_attr[chiave].editable,
            "value_unit": full_attr[chiave].value_unit,
            "healthiness_criteria": full_attr[chiave].healthiness_criteria,
            "healthiness_value": full_attr[chiave].healthiness_value
        });}
    }

 
    return JSON.stringify(element);
}
// end

function addCB(element, data) {
    $.each(data['data'], function () {
        element.append("<option my_data=" + JSON.stringify(this.protocol) + " data_org=" + this.organization + " data_kind=" + this.kind + " value='" + this.name + "'>" + this.name + "</option>");
    });
}

function copyClipboard(elementName) {
    //copy text from elementName
    var copyText = document.getElementById(elementName);
    var value = "<input value=\"" + copyText.value + "\" id=\"selVal\" />";
    $(value).insertAfter(copyText);
    $("#selVal").select();
    document.execCommand("copy");
    $('body').find("#selVal").remove();

    //give feedback for a second
    var feedback = "<div class=\"modalFieldMsgCnt\" id=\"feedback\">Copied</div>"
    oldText = copyText.parentElement.nextElementSibling.nextElementSibling.textContent;
    oldColor = copyText.parentElement.nextElementSibling.nextElementSibling.style.color;
    setTimeout(function () {
        copyText.parentElement.nextElementSibling.nextElementSibling.textContent = oldText;
        copyText.parentElement.nextElementSibling.nextElementSibling.style.color = oldColor;
    }, 1000);
    copyText.parentElement.nextElementSibling.nextElementSibling.textContent = "Copied";
    copyText.parentElement.nextElementSibling.nextElementSibling.style.color = "black";
}

function CSVToArray2(text) {
    let ret = [''], i = 0, p = '', s = true;
    for (let l in text) {
        l = text[l];
        if ('"' === l) {
            s = !s;
            if ('"' === p) {
                ret[i] += '"';
                l = '-';
            } else if ('' === p)
                l = '-';
        } else if (s && ',' === l)
            l = ret[++i] = '';
        else
            ret[i] += l;
        p = l;
    }
    return ret;
}

//--------------------------------------------------------------------------------------------------------------------------------------------- managing multitenancy
// This file contains some functions used during Models and Device management
$(document).ready(function () {

    // handle the click on "Add Model" button
    $('#addModelBtn').click(function () {
        checkServicePath($('#inputServicePathModel').val(), 'add', 'model');
        checkProtocol($('#selectProtocolModel').val(), 'add', 'model');
        checkAddModelConditions();
    });

    // handle the click on "Edit Model" buttons
    $('#modelTable tbody').on('click', 'button.editDashBtn', function () {
        checkServicePath($('#editInputServicePathModel').val(), 'edit', 'model');
        checkProtocol($('#selectProtocolModelM').val(), 'edit', 'model');
        checkEditModelConditions();
    });

    // handle the click on "Add Device" button
    $('#addDeviceBtn').click(function () {
        checkServicePath($('#inputServicePathDevice').val(), 'add', 'device');
        checkProtocol($('#selectProtocolDeviceM').val(), 'add', 'device');
        checkAddDeviceConditions();
    });

    // handle the click on "Edit Device" buttons
    $('#devicesTable tbody').on('click', 'button.editDashBtn', function () {
        checkServicePath($('#editInputServicePathDevice').val(), 'edit', 'device');
        checkProtocol($('#selectProtocolDeviceM').val(), 'edit', 'device');
        checkEditDeviceConditions();
    });

    // handle the "ServicePath" input into the "Add model" section
    $('#inputServicePathModel').on('input', function () {
        checkServicePath($('#inputServicePathModel').val(), 'add', 'model');
        checkAddModelConditions();
    });

    // handle the "ServicePath" input into the "Edit model" section
    $('#editInputServicePathModel').on('input', function () {
        checkServicePath($('#editInputServicePathModel').val(), 'edit', 'model');
        checkEditModelConditions();
    });

    // handle the "ServicePath" input into the "Add device" section
    $('#inputServicePathDevice').on('input', function () {
        checkServicePath($('#inputServicePathDevice').val(), 'add', 'device');
        checkAddDeviceConditions();
    });

    // handle the "ServicePath" input into the "Edit device" section
    $('#editInputServicePathDevice').on('input', function () {
        checkServicePath($('#editInputServicePathDevice').val(), 'edit', 'device');
        checkEditDeviceConditions();
    });

    // handle model protocol value change into "Add Model" section
    $('#selectProtocolModel').change(function () {
        checkProtocol($('#selectProtocolModel').val(), 'add', 'model');
        checkAddModelConditions();
    });

    // handle model protocol value change into "Edit Model" section
    $('#selectProtocolModelM').change(function () {
        checkProtocol($('#selectProtocolModelM').val(), 'edit', 'model');
        checkEditModelConditions();
    });

    // handle model protocol value change into "Add Device" section
    $('#selectProtocolDevice').change(function () {
        checkProtocol($('#selectProtocolDevice').val(), 'add', 'device');
        checkAddDeviceConditions();
    });

    // handle model protocol value change into "Edit Device" section
    $('#selectProtocolDeviceM').change(function () {
        checkProtocol($('#selectProtocolDeviceM').val(), 'edit', 'device');
        checkEditDeviceConditions();
    });

    // Handle changes in "Add Model/Device" broker select element
    $('#selectContextBroker').change(function () {
        getServicesByCBName($('#selectContextBroker').val(), 'add');

        if ($('#inputServicePathDevice').val() !== undefined) {
            checkProtocol($('#selectProtocolDevice').val(), 'add', 'device');
        }

        if ($('#inputServicePathModel').val() !== undefined) {
            checkProtocol($('#selectProtocolModel').val(), 'add', 'model');
        }

        if ($('#editInputServicePathValue').val() !== undefined) {
            var protocol = $('#selectContextBroker').children("option:selected").data("protocol");
            //console.log(protocol);
            checkProtocol(protocol, 'add', 'value');
        }

    });

    // Handle changes in "Edit Model/Device" broker select element
    $('#selectContextBrokerM').change(function () {
        getServicesByCBName($('#selectContextBrokerM').val(), 'edit');

        if ($('#editInputServicePathDevice').val() !== undefined) {
            checkProtocol($('#selectProtocolDeviceM').val(), 'edit', 'device');
        }

        if ($('#editInputServicePathModel').val() !== undefined) {
            checkProtocol($('#selectProtocolModelM').val(), 'edit', 'model');
        }
    });


});

/**
 * 
 * @param {string} value: servicePath value to check 
 * @param {string} mode: add or edit
 * @param {string} context: model, device or value
 */
function checkServicePath(value, mode, context) {
    value = value.trim();
    var message = null;

    var servicePathModelMsg = null;
    var conditionsArray = null;

    if (mode === 'add') {
        servicePathModelMsg = $('#inputServicePathMsg');

        if (context === 'model') {
            // console.log('checkServicePath: add model');
            conditionsArray = addModelConditionsArray;
        } else if (context === 'device') {
            // console.log('checkServicePath: add device');
            conditionsArray = addDeviceConditionsArray;
        } else if (context === 'value') {
            // console.log('checkServicePath: add value');
        } else {
            // console.log('checkServicePath: (add) error in context value: ' + context);
            return;
        }
    } else if (mode == 'edit') {
        servicePathModelMsg = $('#editInputServicePathMsg');

        if (context === 'model') {
            // console.log('checkServicePath: edit model');
            conditionsArray = editModelConditionsArray;
        } else if (context === 'device') {
            // console.log('checkServicePath: edit device');
            conditionsArray = editDeviceConditionsArray;
        } else {
            // console.log('checkServicePath: (edit) error in context value: ' + context);
            return;
        }
    } else {
        // console.log("checkServicePath: error in mode value");
        return;
    }

    var checkValue = servicePathSyntaxCheck(value);
    switch (checkValue) {
        case 0:
        case 1:

            // In this case, following operations are made only for graphical purpose
            // During model creation, these operations must be done before sending data to server
            var valueToPrint = value;
            // console.log(valueToPrint);
            if (valueToPrint[0] !== "/" || valueToPrint === "")
                valueToPrint = "/" + valueToPrint;
            if (valueToPrint[valueToPrint.length - 1] === "/" && valueToPrint.length > 1)
                valueToPrint = valueToPrint.substr(0, valueToPrint.length - 1);
            message = "servicePath preview: " + valueToPrint;

            if (message.length >= 50)
                message = message.substring(0, 45) + "...";

            // set the message color and text
            servicePathModelMsg.css("color", "#337ab7");
            servicePathModelMsg.html(message);

            if (context !== "value")
                conditionsArray['servicePath'] = true;
            break;
        case 2:
            message = "you can't use more than 10 levels";

            // set the message color and text
            servicePathModelMsg.css("color", "red");
            servicePathModelMsg.html(message);

            if (context !== "value")
                conditionsArray['servicePath'] = false;
            break;
        case 3:
            message = "every level must be shorter than 50 characters";

            // set the message color and text
            servicePathModelMsg.css("color", "red");
            servicePathModelMsg.html(message);

            conditionsArray['servicePath'] = false;
            break;
        case 4:
            message = "you can't use empty levels";

            // set the message color and text
            servicePathModelMsg.css("color", "red");
            servicePathModelMsg.html(message);

            if (context !== "value")
                conditionsArray['servicePath'] = false;
            break;
        case 5:
            message = "you can't use whitespaces or semicolons";

            // set the message color and text
            servicePathModelMsg.css("color", "red");
            servicePathModelMsg.html(message);

            if (context !== "value")
                conditionsArray['servicePath'] = false;
            break;
        case 6:
            message = "servicePath is too long";

            // set the message color and text
            servicePathModelMsg.css("color", "red");
            servicePathModelMsg.html(message);

            if (context !== "value")
                conditionsArray['servicePath'] = false;
            break;
        default:
            message = "error in servicePathSyntaxCheck function";

            // set the message color and text
            servicePathModelMsg.css("color", "red");
            servicePathModelMsg.html(message);

            conditionsArray['servicePath'] = false;
            break;
    }
}

/**
 * 
 * @param {string} servicePath 
 * @returns {number}:
 *      1 if the string is empty
 *      2 if there are more than 10 levels
 *      3 if some level has more than 50 characters
 *      4 if there are some empty level
 *      5 if some level contains some whitespaces or some semicolons
 *      6 if servicePath is too long
 *      0 otherwise
 */
function servicePathSyntaxCheck(servicePath) {
    // remove initial and final "/", if any
    if (servicePath[0] === "/")
        servicePath = servicePath.substr(1);
    if (servicePath[servicePath.length - 1] === "/")
        servicePath = servicePath.substr(0, servicePath.length - 1);

    // case: empty string
    if (servicePath === "")
        return 1;

    // case: servicePath is too long
    if (servicePath.length > 95)
        return 6;

    // get single servicePath "levels"
    var levels = servicePath.split("/");
    // console.log(levels);

    // case: too many levels
    if (levels.length > 10)
        return 2;

    for (let i = 0; i < levels.length; i++) {
        // case: some level is too long
        if (levels[i].length > 50)
            return 3;

        // case: there are some empty level
        if (levels[i] === "")
            return 4;

        // case: some level contains some whitespaces or some semicolons
        if (/\s/.test(levels[i]) || levels[i].includes("."))
            return 5;
    }

    // case: everything is ok
    return 0;
}

/**
 * @description this function enables/disables Service/Tenant select and ServicePath input, based on protocol value
 * @param {string} value: protocol value to check
 * @param {string} mode: add or edit
 * @param {string} context: model, device or value
 */
function checkProtocol(value, mode, context) {
    // servicePath elements
    var servicePath = null;
    var servicePathMsg = null;
    var servicePathLabel = null;

    // service elements
    var selectService = null;
    var selectServiceMsg = null;
    var selectServiceLabel = null;

    if (mode === 'add') {

        if (context === "model") {
            // console.log("checkProtocol add model case");
            servicePath = $('#inputServicePathModel');
        } else if (context === "device") {
            // console.log("checkProtocol add device case");
            servicePath = $('#inputServicePathDevice');
        } else if (context === "value") {
            // console.log("checkProtocol add value case");
            servicePath = $('#inputServicePathValue');
        } else {
            // console.log('checkServicePath: (add) error in context value: ' + context);
            return;
        }

        servicePathMsg = $('#inputServicePathMsg');
        servicePathLabel = $('#inputServicePathLabel');

        selectService = $('#selectService');
        selectServiceMsg = $('#selectServiceMsg');
        selectServiceLabel = $('#selectServiceLabel');
    } else if (mode === 'edit') {
        if (context === "model") {
            // console.log("checkProtocol edit model case");
            servicePath = $('#editInputServicePathModel');
        } else if (context === "device") {
            // console.log("checkProtocol edit device case");
            servicePath = $('#editInputServicePathDevice');
        } else {
            // console.log('checkServicePath: (edit) error in context value: ' + context);
            return;
        }

        servicePathMsg = $('#editInputServicePathMsg');
        servicePathLabel = $('#editInputServicePathLabel');

        selectService = $('#editSelectService');
        selectServiceMsg = $('#editSelectServiceMsg');
        selectServiceLabel = $('#editSelectServiceLabel');
    } else {
        // console.log("checkProtocol error case");
        return;
    }

    if (value === "ngsi w/MultiService") {
        // console.log("checkProtocol: equal");
        // enable ServicePath input (and put some graphical sugar for the user)
        servicePath.prop('disabled', false);
        servicePathLabel.css("color", "black");
        checkServicePath(servicePath.val(), mode, context);

        // enable Service/Tenant select (and put some graphical sugar for the user)
        selectService.prop('disabled', false);
        selectServiceLabel.css("color", "black");
        selectServiceMsg.css("color", "#337ab7");
        selectServiceMsg.html("select one Service/Tenant");
    } else {
        // console.log("checkProtocol: not equal");
        // disable ServicePath input (and put some graphical sugar for the user)
        servicePath.val("");
        servicePathLabel.css("color", "lightgrey");
        servicePathMsg.css("color", "lightgrey");
        servicePathMsg.html("only ngsi w/MultiService supports ServicePath");
        servicePath.prop('disabled', true);

        // disable Service/Tenant select (and put some graphical sugar for the user)
        selectService.val("");
        selectServiceLabel.css("color", "lightgrey");
        selectServiceMsg.css("color", "lightgrey");
        selectServiceMsg.html("only ngsi w/MultiService supports Service/Tenant selection");
        selectService.prop('disabled', true);
    }

    if (context == "value") {
        servicePathMsg.html("");
        selectServiceMsg.html("");
    }
}

/**
 * 
 * @param {string} name: context broker's name
 * @param {string} mode: add or edit
 * @param {string} initialValue: initial selected option (default = null)
 */
function getServicesByCBName(name, mode, initialValue = null) {

    // console.log("CB name: " + name);

    // data to send to server
    var data = {
        action: "get_services_by_cb_name",
        name: name,
        token: sessionToken
    };

    // send POST request to server and manage its result
    $.post('../api/contextbroker.php', data).done(function (data) {

        var servicesObj = data['content'];
        var services = [];

        for (let i = 0; i < servicesObj.length; i++) {
            services.push(servicesObj[i]['name']);
        }
        // console.log(JSON.stringify(services));

        var selectService = null;

        if (mode == 'add') {
            // console.log('getServicesByCBName : add case');
            selectService = $('#selectService');
        } else if (mode == 'edit') {
            // console.log('getServicesByCBName : edit case');
            selectService = $('#editSelectService');
        } else {
            //console.log('getServicesByCBName : ERROR');
            return;
        }

        // remove "old" services
        selectService.find('.extraService').remove();

        // option elements creation
        for (let i = 0; i < services.length; i++) {
            var option = document.createElement('option');
            $(option).attr('class', 'extraService');
            $(option).attr('value', services[i]);
            $(option).html(services[i]);

            selectService.append(option);
        }

        if (initialValue) {
            // console.log(initialValue);
            selectService.val(initialValue);
        } else {
            // console.log("no initial value");
        }

        // console.log("getServicesByCBName END");
        return;
    }).fail(function () {
        alert("Something wrong during getting services");
        return;
    });
}

/**
 * 
 * @param {string} serviceVal: service value, taken from edit button's attributes
 * @param {string} servicePathVal: servicePath value, taken from edit button's attributes
 * @param {string} brokerName: broker name, used to get services options
 * @param {string} context: model or device
 */
function fillMultiTenancyFormSection(serviceVal, servicePathVal, brokerName, context) {

    // console.log("serviceVal:" + serviceVal + " " + typeof(serviceVal));
    // console.log("servicePathVal:" + servicePathVal + " " + typeof(serviceVal));
    // console.log("context:" + context);

    // servicePath elements
    var servicePath = null;
    var servicePathMsg = null;
    var servicePathLabel = null;

    // service elements
    var selectService = null;
    var selectServiceMsg = null;
    var selectServiceLabel = null;

    // protocol element
    var selectProtocol = null;

    // data to send to server
    var data = {
        action: "get_services_by_cb_name",
        name: brokerName,
        token: sessionToken
    };

    // send POST request to server and manage its result
    $.post('../api/contextbroker.php', data).done(function (data) {

        var servicesObj = data['content'];
        var services = [];

        for (let i = 0; i < servicesObj.length; i++) {
            services.push(servicesObj[i]['name']);
        }
        // console.log(JSON.stringify(services));

        // select element for Service/Tenant
        selectService = $('#editSelectService');

        // remove "old" services
        selectService.find('.extraService').remove();

        // option elements creation
        for (let i = 0; i < services.length; i++) {
            var option = document.createElement('option');
            $(option).attr('class', 'extraService');
            $(option).attr('value', services[i]);
            $(option).html(services[i]);

            selectService.append(option);
        }

        if (context === 'model') {
            // console.log("fillMultiTenancyFormSection model context");
            servicePath = $('#editInputServicePathModel');
            selectProtocol = $('#selectProtocolModelM');
        } else if (context === 'device') {
            // console.log("fillMultiTenancyFormSection device context");
            servicePath = $('#editInputServicePathDevice');
            selectProtocol = $('#selectProtocolDeviceM');
        } else if (context === 'contextbroker') {
            // console.log("fillMultiTenancyFormSection device context");
            servicePath = $('#editInputServicePathCB');
            selectProtocol = $('#selectProtocolDeviceM');
        } else {
            // console.log("ERROR in fillMultiTenancyFormSection evaluation");
            return;
        }

        servicePathMsg = $('#editInputServicePathMsg');
        servicePathLabel = $('#editInputServicePathLabel');

        selectServiceMsg = $('#editSelectServiceMsg');
        selectServiceLabel = $('#editSelectServiceLabel');

        if (selectProtocol.val() === 'ngsi w/MultiService') {
            // console.log("equal");

            // enable ServicePath input (and put some graphical sugar for the user)
            servicePath.prop('disabled', false);
            servicePathLabel.css("color", "black");
            if (servicePathVal !== "null")
                servicePath.val(servicePathVal);
            checkServicePath(servicePath.val(), 'edit', context);

            // enable Service/Tenant select (and put some graphical sugar for the user)
            selectService.prop('disabled', false);
            selectServiceLabel.css("color", "black");
            selectServiceMsg.css("color", "#337ab7");
            selectServiceMsg.html("select one Service/Tenant");
            if (serviceVal !== "null")
                selectService.val(serviceVal);
        } else {
            // console.log("not equal");

            // disable ServicePath input (and put some graphical sugar for the user)
            servicePath.val("");
            servicePathLabel.css("color", "lightgrey");
            servicePathMsg.css("color", "lightgrey");
            servicePathMsg.html("only ngsi w/MultiService supports ServicePath");
            servicePath.prop('disabled', true);

            // disable Service/Tenant select (and put some graphical sugar for the user)
            selectService.val("");
            selectServiceLabel.css("color", "lightgrey");
            selectServiceMsg.css("color", "lightgrey");
            selectServiceMsg.html("only ngsi w/MultiService supports Service/Tenant selection");
            selectService.prop('disabled', true);
        }

        // set device section as read-only
        if (context === 'device') {
            servicePath.prop('disabled', true);
            selectService.prop('disabled', true);
        }

    }).fail(function () {
        alert("Something wrong during getting services");
    });
}
//--------------------------------------------------------------------------------------------------------------------------------------------- isMobile management

function updateIsMobile(edit, staticAttributes) {
    var element = "#isMobileTick";
    if (edit)
        element = "#isMobileTickM"

    if (checkIsMobile(staticAttributes))
        $(element).prop('checked', true);
    else
        $(element).prop('checked', false);
}

function checkIsMobile(staticAttributes) {
    if (staticAttributes)
        for (let i = 0; i < staticAttributes.length; i++) {
            if ((staticAttributes[i][0] == "http://www.disit.org/km4city/schema#isMobile") &&
                    (staticAttributes[i][1] == "true"))
                return true;
        }
    return false;
}

//------------------------------------------------------------------------------------------------------------------------------------------ INFO LABEL
function getInfoCert(privatekey, visibility, created, id, contextbroker, certificate, sha) {
    var txtCert = "";
    if (privatekey != "" && privatekey != null && (visibility == 'MyOwnPublic' || visibility == 'MyOwnPrivate')) {
        x = new Date(created);
        x.setFullYear(x.getFullYear() + 1);
        y = x.toString();
        txtCert =
                '<div class="row">' +
                '<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Created on:</b>' + "  " + created + '</div>' +
                '<div class="clearfix visible-xs"></div>' +
                '<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>Expire on:</b>' + "  " + y + '</div>' +
                '</div>' +
                '<div class="row">' +
                '<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><button class="btn btn-primary my-small-button" onclick="download(\'\/private\/' +
                privatekey + '\',\'' + id + '\',\'' + contextbroker + '\');return true;"><b>PRIVATE KEY</b></button></div>' +
                '<div class="clearfix visible-xs"></div>' +
                '<div class="col-xs-6 col-sm-6" style="background-color:#D6CADD;"><button class="btn btn-primary my-small-button" onclick="download(\'\/certsdb\/' +
                certificate + '\',\'' + id + '\',\'' + contextbroker + '\');return true;"><b>CERTIFICATE</b></button></div>' +
                '</div>' +
                '<div class="row">' +
                '<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><a href="https://www.snap4city.org/ca/ca.pem" download>' +
                '<button class="btn btn-primary my-small-button"><b>CA CERTIFICATE</b></button></a></div>' +
                '<div class="clearfix visible-xs"></div>' +
                '<div class="col-xs-6 col-sm-6" style="background-color:#E6E6FA;"><b>SHA1:</b>' + "  " + sha + '</div>' +
                '</div>';
    } else
        txtCert =
                '<div class="row">' +
                '<div class="col-xs-12 col-sm-12" style="background-color:#E6E6FA;"><b>Created on:</b>' + "  " + created + '</div>' +
                '<div class="clearfix visible-xs"></div>' +
                '</div>';
    return txtCert;
}

function download(sourcename, devicename, contextbroker) {
    $.ajax({url: "../api/device.php",
        data: {
            token: sessionToken,
            action: 'download',
            filename: sourcename,
            organization: organization,
            id: devicename,
            contextbroker: contextbroker
        },
        type: "POST",
        async: true,
        dataType: 'json',
        success: function (mydata) {
            var element = document.createElement('a');
            element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(mydata.msg));
            element.setAttribute('download', sourcename.substr(sourcename.indexOf("/", 2) + 1));
            element.style.display = 'none';
            document.body.appendChild(element);
            element.click();
            document.body.removeChild(element);
        },
        error: function (mydata)
        {
            console.log("error:".mydata);
            alert("Error in reading data. Please get in touch with the Snap4city Administrator");
        }
    });
}

function datainspect(id, type, contextbroker, service, servicePath, version) {
    if (service != "null" && servicePath != "null")
        $('<form method="post" action="../api/device.php" target="_blank">' +
                '<input type="hidden" name="token" value="' + sessionToken + '">' +
                '<input type="hidden" name="action" value="get_device_data">' +
                '<input type="hidden" name="id" value="' + id + '">' +
                '<input type="hidden" name="type" value="' + type + '">' +
                '<input type="hidden" name="contextbroker" value="' + contextbroker + '">' +
                '<input type="hidden" name="service" value="' + service + '">' +
                '<input type="hidden" name="servicePath" value="' + servicePath + '">' +
                '<input type="hidden" name="version" value="' + version + '">' +
                '</form>').appendTo('body').submit().remove();
    else
        $('<form method="post" action="../api/device.php" target="_blank">' +
                '<input type="hidden" name="token" value="' + sessionToken + '">' +
                '<input type="hidden" name="action" value="get_device_data">' +
                '<input type="hidden" name="id" value="' + id + '">' +
                '<input type="hidden" name="type" value="' + type + '">' +
                '<input type="hidden" name="contextbroker" value="' + contextbroker + '">' +
                '<input type="hidden" name="version" value="' + version + '">' +
                '</form>').appendTo('body').submit().remove();
}
