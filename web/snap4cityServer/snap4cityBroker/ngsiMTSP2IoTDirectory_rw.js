//values retrieved from snapIoTDirectory_cbRetrieval_c 
var ORION_CB = process.argv[2];
var ORION_ADDR = process.argv[3];
var USER = process.argv[4]
var ACCESS_LINK = process.argv[5];
var ACCESS_PORT = process.argv[6];
var MODEL = process.argv[7];
var EDGE_GATEWAY_TYPE = process.argv[8];
var EDGE_GATEWAY_URI = process.argv[9];
var ORGANIZATION=process.argv[10];
var PATH = process.argv[11];
var KINDBROKER = process.argv[12];
var APIKEY = process.argv[13];
var TOKEN=process.argv[14];
//Static values
var ORION_PROTOCOL = "ngsi w/MultiService";
var KIND = "sensor";
var FORMAT="json";
var FREQUENCY = 600;
/* global variables */
var apiLink = "http://localhost/iot2/api/";
var modelsdata=[];
var gb_value_units=[];
var gb_datatypes=[];
var gb_value_types = [];
var schema;
var deviceAttributes=["uri", "devicetype", "kind", "macaddress", "producer", "latitude", "longitude", "protocol", "format", "frequency", "k1", "k2", "subnature", "static_attributes"];

var express = require('express');
var request = require('request');
var bodyParser = require('body-parser');
const spawn = require('child_process').spawn;
var mysql = require('mysql');
var Promise = require('promise');
const fs = require('fs');
var FileSaver = require('file-saver');
var Blob = require('blob');
var http = require("http");
var Parser = require('./Parser/Classes/Parser');
var XMLHttpRequest = require("xmlhttprequest").XMLHttpRequest;

const ini = require('ini');
const config = ini.parse(fs.readFileSync('./snap4cityBroker/db_config.ini', 'utf-8'));
const c_host = config.database.host;
const c_user = config.database.user;
const c_port = config.database.port;
const c_password = config.database.password;
const c_database = config.database.database;

var cid = mysql.createConnection({
    host: c_host,
    port: c_port,
    user: c_user,
    password: c_password,
    database: c_database
});

cid.connect(function(err){
    if(err){
        console.log('Error connecting to Db');
        throw err;
    }
    console.log('Connection established');
});

var req = new XMLHttpRequest();
var link = apiLink+"device.php";
req.open("POST", link+"?action=get_param_values&token="+TOKEN, false);
req.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
        let resp= JSON.parse(this.responseText);
        gb_datatypes= resp["data_type"];
        gb_value_units= resp["value_unit"];
        gb_value_types= resp["value_type"];
        console.log("dictionary retrieved");
    }else if(this.readyState == 4 && this.status != 200){
        console.log("dictionary error "+this.status);
    }
}
req.send();

req = new XMLHttpRequest();
var connLink = apiLink+"deviceDiscoveryApi.php?action=getCBServiceTree&contextbroker="+ORION_CB+"&token="+TOKEN;
req.open("POST", connLink, false);
req.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
        schema = JSON.parse(this.responseText)["content"];
        if(schema.length>0) {
			console.log("tenants/paths structure retrieved");
        	inferImplicitPaths(schema);
        }else{
            console.log("tenants/paths structure empty response!");
        }
    }else if(this.readyState == 4 && this.status != 200){
        console.log("tenants/paths structure error "+this.status);
    }
}
req.send();

function inferImplicitPaths(schema){
    for(let i=0; i<schema.length; i++){
        let tenant = schema[i].service;
        let path = schema[i].servicePath;
        let slashes = (path.split("/")).length - 1;
        for (let j = 0; j < slashes; j++) {
            let found = false;
            path = path.substring(0, path.lastIndexOf("/"));
           	for(let k=0;k<schema.length;k++){
           	    if(schema[k].service == schema[i].service){
               	    if(schema[k].servicePath == path){
                   	    found=true;
                       	break;
                    }
   	            }
       	    }
            if ((!found)&&(path.length!=0)){	//PATH avoid empty path, otherwise take all the device!
                schema.push(new Object({service:tenant, servicePath:path}));
                console.log("Path inferred: " + path + " in tenant: " + tenant);
            }
        }
    }
    //console.log(content);
    retrieveDevices(schema)
}

function retrieveDevices(schema){
    if(KINDBROKER.localeCompare("external")==0) {
		//TODO: what about management of https? it has to be specieied in the accessLink!
        if (!ACCESS_LINK.startsWith("http"))
			ACCESS_LINK="http://" + ACCESS_LINK;

		if(ACCESS_PORT !== undefined && ACCESS_PORT.localeCompare("null")!==0 && ACCESS_PORT.localeCompare("")!==0  ) {
        	ACCESS_LINK = ACCESS_LINK + ":" + ACCESS_PORT;
		}
        
        if(PATH.localeCompare("null")==0 || PATH.localeCompare("")==0  || PATH == undefined) {
            link = ACCESS_LINK;
        }
        else {
            link = ACCESS_LINK + PATH;
        }
    }
    else {
		// TODO : do we manage internal periofi update? NO!
		// throw an erro in this case or avoid management at all
        if(PATH.localeCompare("null") ==0 || PATH == undefined || PATH == null || PATH.localeCompare(" ")==0){
            link = 'http://'+ORION_ADDR;
        }
        else{
            link = 'http://'+ORION_ADDR+PATH;
        }
    }

    console.log("Connecting link is:"+link);
    retrieveDataCaller(schema);
}

function retrieveDataCaller(schema){
	console.log("Retrieving data...");
    for (let i=0;i<schema.length;i++){
        var xhttp = new XMLHttpRequest();
        retrieveData(xhttp, link, schema[i].service, schema[i].servicePath);
    }
}

function retrieveData(xhttp, link, service, servicePath) {

    var promiseAcquisition = new Promise(function(resolve2, reject) {

        xhttp = new XMLHttpRequest();

        xhttp.open("GET", link, true);
        if(APIKEY !== null || APIKEY !== undefined){
            xhttp.setRequestHeader("apikey",APIKEY);
        }
        xhttp.setRequestHeader("Fiware-Service",service);
        xhttp.setRequestHeader("Fiware-ServicePath",servicePath);
        xhttp.send();

        xhttp.onreadystatechange = function() {

            if (this.readyState == 4 && this.status == 200) {
                //console.log("readyState " + this.readyState + " status " + this.status + this.responseText);
                //function that manages the output in order to create the data
                var responseText = this.responseText;

                //variable initialization
                var orionDevices = [];
                var orionDevicesType = [];
                var orionDevicesSchema = {};
                var registeredDevices = [];

                var obj = JSON.parse(responseText);

                if (obj instanceof Array) {

                    //console.log("length obj "+obj.length);
                    for (i = 0; i < obj.length; i++) {
                        let index = obj[i].id;
                        orionDevices.push(index);
                        //orionDevicesSchema[obj[i].id]= obj[i];
                        orionDevicesSchema[index] = obj[i];
                        orionDevicesType[index] = obj[i].type;
                    }

                } else {
                    orionDevices.push(obj.id);
                    orionDevicesSchema[obj.id] = obj;
                    orionDevicesType[obj.id] = obj.type;
                }

                //if(typeof gb_value_units === undefined || gb_value_units.length <=0 )
                //	getParam(cid);
                if (typeof modelsdata === undefined || MODEL.localeCompare("custom") == 0 || modelsdata.length <= 0)
                    getModels(cid);

                var sql = "(SELECT id FROM temporary_devices WHERE contextBroker = '" + ORION_CB + "' AND service = '"+service+"' AND servicePath = '"+servicePath+"') UNION (SELECT id FROM devices WHERE contextBroker = '" + ORION_CB + "' AND service = '"+service+"' AND servicePath = '"+servicePath+"')";

                cid.query(sql, function (err, result, fields) {
                    if (err) {
                        console.log("sql " + sql);
                        throw err;
                    }
                    for (i = 0; i < result.length; i++) {
                        registeredDevices.push(result[i].id.replace(service,"").replace(servicePath,"").replace("..",""));
                    }

                    //checking if the devices already exist in the platform
                    //console.log("registeredDevices " +registeredDevices.length + " orion length "+ orionDevices.length);
                    var newDevices = orionDevices.diff(registeredDevices);
                    console.log("There are " + newDevices.length + " new devices for the broker " + ORION_CB+ "in tenant "+service+ " in path "+ servicePath+ ".");

                    newDevices = removeDuplicates(newDevices);
                    //Checking duplicates into the same array
                    var extractionRulesAtt = new Object();
                    var extractionRulesDev = new Object();
                    var promiseExtractionRules = new Promise(function (resolveExtraction, rejectExtraction) {
                        var query = "SELECT * FROM extractionRules where contextbroker='" + ORION_CB + "';";
                        //console.log("rules");
                        cid.query(query, function (err, resultRules, fields) {

                            if (err) {
                                console.log("sql " + query);
                                throw err;
                            }
                            //console.log("extraction rules");
                            for (var x = 0; x < resultRules.length; x++) {
                                if (resultRules[x]["kind"].localeCompare("property") == 0) {
									//console.log("adding"+JSON.stringify(resultRules[x]));
                                    extractionRulesDev[resultRules[x]["id"]] = resultRules[x];
                                } else {
                                    //console.log("resultRules[x] "+ resultRules[x]["id"]);
                                    extractionRulesAtt[resultRules[x]["id"]] = resultRules[x];
                                }

                            }
                            if (resultRules.length == 0) {
                                rejectExtraction();
                            } else {
                                resolveExtraction();
                            }
                        }); //query

                    });

                    var se = [];
                    var sesc = [];
                    //console.log("nodup "+ newDevices.length);
                    //			console.log("oriondeviceschema2 "+ JSON.stringify(orionDevicesSchema));
                    var ruleJSON, parser;
                    promiseExtractionRules.then(function (resolveExtraction) {

                        //console.log("new devices "+ newDevices +  "registeredDevices "+ registeredDevices );

                        var type;
                        var sensor;
                        //var sensorApplied = new Object();
                        var rule;
                        var devAttr = new Object();
                        for (var i = 0; i < newDevices.length; i++) {
                            var attProperty = [];
                            var topic = newDevices[i];
                            //	console.log("topic");
                            if (orionDevicesSchema[topic] == undefined) {
                                //console.log("topic undefined " + topic);
                                continue;
                            }
                            //console.log("topic " + topic);
                            //console.log("extract " + JSON.stringify(extractionRulesAtt));
                            for (var j in extractionRulesAtt) {

                                rule = extractionRulesAtt[j]["selector"];
                                var id = extractionRulesAtt[j]["id"];
                                rule = JSON.stringify(rule);

                                rule = rule.replace(/\\/g, "");

                                rule = rule.replace("PM2,5", "PM2x5");
                                rule = rule.replace("PM2.5", "PM2y5");
                                //console.log("rule "+ rule);
                                rule = rule.slice(1, rule.length - 1);
                                let jsonRules = JSON.parse(rule);

                                parser = new Parser();
                                let typeData = (jsonRules.type).toUpperCase();
                                //	console.log("jsonRules parse "+ jsonRules.param.s);


                                parser.addObjRule(jsonRules, typeData);
                                let v = orionDevicesSchema[topic];
                                v = JSON.stringify(v);
                                v = v.replace("PM2,5", "PM2x5");
                                v = v.replace("PM2.5", "PM2y5");
                                //console.log("v "+ v);
                                //console.log("rulePre apl "+ rule);
                                let parserApply = parser.applyRules(v);
                                //	console.log("rule " + rule +" parserApply " + JSON.stringify(parserApply));
                                var attName, value_type, data_type;
                                //console.log("length" + parserApply.length);
                                for (var p = 0; p < parserApply.length; p++) {
                                    //	console.log("parserApply "+ JSON.stringify(parserApply)+ " pasr "+ parserApply[0].type);
                                    if (extractionRulesAtt[j]["value_type"].startsWith("{")) {
                                        //console.log("startsWith");
                                        let parserValue = new Parser();
                                        let ruleValue = extractionRulesAtt[j]["value_type"];
                                        ruleValue = JSON.stringify(ruleValue);
                                        //	console.log("ruleValue "+ ruleValue);
                                        ruleValue = ruleValue.replace(/\\/g, "");
                                        ruleValue = ruleValue.slice(1, rule.length - 1);
                                        let jsonRuleValue = JSON.parse(ruleValue);
                                        parserValue.addObjRule(jsonRuleValue, "JSON");
                                        let v2 = orionDevicesSchema[topic];
                                        v2 = JSON.stringify(v2);
                                        let parserApply2 = parserValue.applyRules(v2);
                                        value_type = parserApply2[0];
                                        //	console.log("parserApply2" + value_type);


                                    } else {
                                        value_type = extractionRulesAtt[j]["value_type"];
                                    }

                                    //------name extraction ----
                                    if (extractionRulesAtt[j]["structure_flag"].localeCompare("yes") == 0) {
                                        let toSplit = (jsonRules.param.s).split(".");
                                        attName = toSplit[toSplit.length - 1];
                                    } else {
                                        if (parserApply[p].id !== undefined) {
                                            attName = parserApply[p].id;
                                        } else if (parserApply[p] !== undefined) {
                                            attName = parserApply[p];
                                        } else {
                                            attName = id;
                                        }
                                        //console.log("attName "+ attName);
                                    }
                                    if (extractionRulesAtt[j]["data_type"] == null || extractionRulesAtt[j]["data_type"].length == 0) {
                                        data_type = parserApply[p].type;
                                    } else {
                                        data_type = extractionRulesAtt[j]["data_type"];
                                    }
                                    attName = attName.replace("PM2x5", "PM2,5");
                                    attName = attName.replace("PM2y5", "PM2.5");


                                    let objProp = {
                                        "value_name": attName,
                                        "value_type": value_type,
                                        "data_type": data_type,
                                        "value_unit": extractionRulesAtt[j]["value_unit"],
                                        "editable": false,
                                        "healthiness_criteria": "refresh_rate",
                                        "healthiness_value": 300
                                    };
                                    console.log("objProp " + JSON.stringify(objProp));
                                    attProperty.push(objProp);
                                    sesc.push(storeAttribute(topic, objProp.value_name, objProp.data_type, objProp.value_type, objProp.value_unit, objProp.healthiness_criteria, objProp.healthiness_value, objProp.value_name));

                                    //sensor = {"name": attName, "value_type":}
                                }
                            }//end for j

							for (var j in extractionRulesDev) {
								var nameDev = extractionRulesDev[j]["id"];
								if (deviceAttributes.includes(nameDev)) {
									let ruleDev = extractionRulesDev[j]["selector"];
									ruleDev = JSON.stringify(ruleDev);
									ruleDev = ruleDev.replace(/\\/g, "");
									ruleDev = ruleDev.slice(1, ruleDev.length - 1);
									console.log("device property: "+nameDev+" selector to apply: "+ ruleDev);
                                    
									let jsonRulesDev = JSON.parse(ruleDev);
                                    parserDev = new Parser();
                                    parserDev.addObjRule(jsonRulesDev, "JSON");
									let vDev = orionDevicesSchema[topic];
                                    vDev = JSON.stringify(vDev);
									console.log("entity: "+ vDev);
                                    
									let parserApply = parserDev.applyRules(vDev);
                                    devAttr[nameDev] = parserApply[0];
                                    console.log("apply attribute:"+ devAttr[nameDev]);
                                }
                            }//end for j dev

							//if no rules are defined for mac, k1, k2, set tthis field to empty
							if (devAttr["mac"]==undefined)
								devAttr["mac"]="";
							if (devAttr["k1"]==undefined)
                                devAttr["k1"]="";
							if (devAttr["k2"]==undefined)
                                devAttr["k2"]="";
							if (devAttr["producer"]==undefined)
								devAttr["producer"]="";
							if (devAttr["subnature"]==undefined)
								devAttr["subnature"]="";
                            if (devAttr["static_attributes"]==undefined)
                                devAttr["static_attributes"]="[]";

                            var toVerify = {
                                "name": topic,
                                "username": USER,
                                "contextbroker": ORION_CB,
                                "id": topic,
                                "model": MODEL,
								"producer": devAttr["producer"],
                                "devicetype": devAttr["devicetype"],
                                "protocol": ORION_PROTOCOL,
                                "format": FORMAT,
                                "frequency": FREQUENCY,
                                "kind": KIND,
                                "latitude": devAttr["latitude"],
                                "longitude": devAttr["longitude"],
                                "macaddress": devAttr["mac"],
                                "k1": devAttr["k1"],
                                "k2": devAttr["k2"],
								"subnature": devAttr["subnature"],
								"static_attributes": devAttr["static_attributes"],
                                "deviceValues": attProperty
                            };

                            var verify = verifyDevice(toVerify);
                            var validity = "invalid";
                            if (verify.isvalid)
                                validity = "valid";
							
                            se.push(storeDevice(USER, topic, MODEL, KIND, devAttr["devicetype"], ORION_PROTOCOL, FREQUENCY, FORMAT, ORION_CB, "","", devAttr["mac"], 
								validity, verify.message, "no", ORGANIZATION, EDGE_GATEWAY_TYPE, EDGE_GATEWAY_URI, devAttr["k1"], devAttr["k2"], service, servicePath, 
								devAttr["subnature"], devAttr["static_attributes"], devAttr["producer"]));

                        }//end for i

                        //   console.log("sesc"+ sesc.length + " json "+ JSON.stringify(sesc));
                        //  process.exit;
                        if (se.length != 0) {

                            //if there are devices to be inserted
                            var promise1 = new Promise(function (resolve, reject) {
                                insertDevices(cid, se, (res) => {
                                    //console.log("resolve 1");
                                    resolve();
                                });
                            });
                            //	console.log("sesc "+ sesc);
                            promise1.then(function (resolve) {
                                //console.log("SESC BEF "+ JSON.stringify(sesc)+ " SE "+ JSON.stringify(se));
                                insertValues(cid, sesc);
                                //console.log("vales");
                                resolve2();
                            });
                        }

                    });//end then extraction rules
                }); //query

            //promise unit then
}//end readystate == 4
else if (this.readyState == 4 && this.status == 500) {
    //console.log("reject");

    reject();
}
};//end onreadystatechange
});//end promiseAcquisition

promiseAcquisition.then(function(resolve2) {
        //console.log(resolve2);
    },
    function(error) {
        console.log("promise then error: "+error);
    });

}//end retrieveData function


function extractType(value)
{
    var first = value.charAt(0);
    var f;
    switch (first)
    {
        case "{": // Format C
            f="json";
            break;
        case "<": // Format D
            f="xml";
            break;
        default: // Format A or B
            f="csv";
            break;
    }
    return f;
}


/*functions */
Array.prototype.diff = function( arr ) {
    //console.log ("diff");
    return this.filter( function( val ) {
        //console.log("arr.index "+ arr.indexOf(val)+ " val "+ val);
        return arr.indexOf( val ) < 0;
    });
};

//retrieve models directly from DB, it's ok because the model name is passed from aoutside
function getModels(cid)
{
    var models  = "SELECT * FROM model";
    cid.query(models, function (err, result, fields){
        if (err) {console.log("sql "+sql); throw err;}
        for (i = 0; i < result.length; i++) {
            modelsdata.push(result[i]);
        }
    }); 
}

function insertDevices(cid, se, callback)
{
    console.log("insertDevices:"+se);
    var sqlse  = "INSERT INTO `temporary_devices`(`username`,`id`,`model`, `kind`,`devicetype`,`protocol`,`frequency`,`format`,`contextBroker`,`latitude`,`longitude`,macaddress,`status`,`validity_msg`,`should_be_registered`,`organization`,`edge_gateway_type`,`edge_gateway_uri`,`k1`,`k2`,`service`,`servicePath`,`subnature`,`static_attributes`, `producer`) VALUES ?";

    cid.query(sqlse, [se], function(errSens) {
        callback();

        if (errSens) {console.log("devices insert error " ); throw errSens;}
        // console.log("fatto");
    });
}

function insertValues(cid, sesc)
{
    //console.log("insertValues");
    var sqlsesc  = "INSERT INTO `temporary_event_values`(`device`, `cb`,`value_name`, `data_type`,`value_type`,`value_unit`,`healthiness_criteria`,`value_refresh_rate`,`old_value_name`) VALUES ?";
    //console.log("Sesc "+ JSON.stringify(sesc));
    cid.query(sqlsesc, [sesc], function(errSSch) {
        if (errSSch) {throw errSSch;}
    });
}

/* store a device in the db*/
function storeDevice(user, deviceID, model, kind, type, protocol, frequency, format, cb, latitude, longitude, macaddress, status, validity_msg, shouldberegistered,
	organization, edge_type, edge_uri, k1, k2, service, servicePath, subnature, staticAttributes, producer) {
    return [user, deviceID, model, kind, type, protocol, frequency, format, cb, latitude, longitude, macaddress, status, validity_msg, shouldberegistered, organization, edge_type,
		edge_uri, k1, k2, service, servicePath, subnature, staticAttributes, producer];
}
function storeAttribute(topic, name, data_type, value_type, value_unit, healthiness_criteria, healthiness_value) {
    return [topic, ORION_CB, name, data_type, value_type, value_unit, healthiness_criteria, healthiness_value, name];
}

function verifyDevice(deviceToverify){
    var msg="";
    var regexpMAC = /^([0-9A-F]{2}[:-]){5}([0-9A-F]{2})$/;
	//devicename and value don't need to be verified since are retrieved from the correct fotmat on ngsi
    var answer={"isvalid":true, "message":"Your device is valid"};

    if(deviceToverify.name==undefined || deviceToverify.name.length<5 || deviceToverify.name == null){ msg+= "-name is mandatory, of 5 characters at least.";}
    if(deviceToverify.devicetype==undefined || deviceToverify.devicetype=="" || deviceToverify.devicetype.indexOf(' ')>=0|| deviceToverify.devicetype == null){msg+="-devicetype is mandatory.";}
    if(deviceToverify.macaddress!=undefined && deviceToverify.macaddress!="" && !regexpMAC.test(deviceToverify.macaddress)){msg+="-macaddress is mandatory and Mac format should be Letter (A-F) and number (eg. 3D:F2:C9:A6:B3:4F).";}
    if(deviceToverify.frequency==undefined ||deviceToverify.frequency=="" || !isFinite(deviceToverify.frequency) || deviceToverify.frequency == null){msg+= "-frequency is mandatory, and should be numeric.";}
    if(deviceToverify.kind==undefined || deviceToverify.kind==""){msg+="-kind is mandatory.";}
    if(deviceToverify.protocol==undefined || deviceToverify.protocol==""){msg+="-protocol is mandatory.";}
    if(deviceToverify.format==undefined || deviceToverify.format==""){msg+="-format is mandatory.";}
    if(deviceToverify.latitude==undefined || !isLatitude(deviceToverify.latitude)){msg+="-Latitude is mandatory, with the correct numeric format.";}
    if(deviceToverify.longitude==undefined ||!isLongitude(deviceToverify.longitude)){msg+="-Longitude is mandatory, with the correct numeric format.";}
//    if(deviceToverify.k1==undefined || deviceToverify.k1==""){msg+="-k1 is mandatory.";}
//    if(deviceToverify.k2==undefined || deviceToverify.k2==""){msg+="-k2 is mandatory.";}

	//verify consistency subnature and its attributes
	if (deviceToverify.subnature!==""){
			// TODO remove this comment or enable in another way this verify... 
			// if was removed beecause it delay too much
        	//if (!verifySubnature(deviceToverify.subnature, deviceToverify.static_attributes)){
            //    	answer.isvalid=false;
            //            msg+="-The static attributes of the device do not comply with its subnature ("+deviceToverify.subnature+")";
            //    }
	}

    if(msg.length>0) answer.isvalid=false;
    
	if(deviceToverify.deviceValues.length<1) {
        answer.isvalid=false;
        msg+="-Your device should at least have 1 attributes.";
    }

    if(deviceToverify.model!="custom") {
        for(var i=0; i<modelsdata.length; i++){
            if(modelsdata[i].name!=deviceToverify.model){
                continue;
            }

            var modelAttributes= JSON.parse(modelsdata[i].attributes);

            if(Object.keys(modelAttributes).length!=Object.keys(deviceToverify.deviceValues).length){
                answer.isvalid=false;
                msg+="-Your device has different number of attributes than the selected model ";
            }
            else {
                for (var j=0; j<deviceToverify.deviceValues.length; j++){
                    var found=0;
                    for(var l= 0; l<modelAttributes.length; l++){
                        if(modelAttributes[l].value_name==deviceToverify.deviceValues[j].value_name){
                            found=1;
                            var msg_attr_detail="";

                            if(modelAttributes[l].value_type!=deviceToverify.deviceValues[j].value_type)
                            {msg_attr_detail+=" value type,";}
                            if(modelAttributes[l].data_type!=deviceToverify.deviceValues[j].data_type)
                            {msg_attr_detail+=" data type,";}
                            if(modelAttributes[l].editable!=deviceToverify.deviceValues[j].editable)
                            {msg_attr_detail+=" editable,";}
                            if(modelAttributes[l].healthiness_criteria!=deviceToverify.deviceValues[j].healthiness_criteria)
                            {msg_attr_detail+=" healthiness criteria,";}
                            if(modelAttributes[l].healthiness_value!=deviceToverify.deviceValues[j].healthiness_value){msg_attr_detail+=" healthiness value,";}
                            if(modelAttributes[l].value_unit!=deviceToverify.deviceValues[j].value_unit)
                            {msg_attr_detail+=" value unit,";}

                            if(msg_attr_detail.length>0){
                                answer.isvalid=false;
                                msg+="The attribute "+deviceToverify.deviceValues[j].value_name+" has the details:"+msg_attr_detail+" not compatible with its model.";
                            }
                            else{
                                modelAttributes.splice(l,1);                                        }
                        }
                    }
                    if(found==0){
                        answer.isvalid=false;
                        msg+="-The device attribute name "+ deviceToverify.deviceValues[j].value_name+ " do not comply with its model."
                    }
                }
            }

            if(modelsdata[i].contextbroker!=deviceToverify.contextbroker){ answer.isvalid=false;
                msg+="-The device property: context broker does not comply with its model." ;}
            if(modelsdata[i].devicetype!=deviceToverify.devicetype) {answer.isvalid=false;
                msg+="-The device property: type does not comply with its model." ;}
            if(modelsdata[i].format!=deviceToverify.format){ answer.isvalid=false;
                msg+="-The device property: format does not comply with its model." ;}
            if(modelsdata[i].frequency!=deviceToverify.frequency){ answer.isvalid=false;
                msg+="-The device property: frequency does not comply with its model." ;}
            if(modelsdata[i].kind!=deviceToverify.kind){ answer.isvalid=false;
                msg+="-The device property: kind does not comply with its model." ;}
            if(modelsdata[i].producer!=deviceToverify.producer){ answer.isvalid=false;
                msg+="-The device property: producer does not comply with its model." ;}
            if(modelsdata[i].protocol!=deviceToverify.protocol){{ answer.isvalid=false;
                msg+="-The device property: protocol does not comply with its model." ;}}
			if(modelsdata[i].subnature!=deviceToverify.subnature){{ answer.isvalid=false;
				msg+="-The device property: subnature does not comply with its model." ;}}
			//if(modelsdata[i].static_attributes!=deviceToverify.static_attributes){{ answer.isvalid=false;
				//msg+="-The device property: static_attributes does not comply with its model." ;}}
			//the model doenot permit a null value for service and servicepath, so they are always configured... so cannot be used for validation
			//TODO we need model to set null to activate this functionalities
			//if ((modelsdata[i].service!==null)&&(modelsdata[i].service!=deviceToverify.service)){{ answer.isvalid=false;
			//	msg+="-The device property: service does not comply with its model." ;}}
			//if ((modelsdata[i].servicePath!==null)&&(modelsdata[i].servicePath!=deviceToverify.service_path)){{ answer.isvalid=false;
			//	msg+="-The device property: servicePath does not comply with its model." ;}}
        }
    }
    else {
        var all_attr_msg="";
        var all_attr_status=true;
        var healthiness_criteria_options=["refresh_rate", "different_values", "within_bounds"];

        for (var i=0; i<deviceToverify.deviceValues.length; i++) {
            var v=deviceToverify.deviceValues[i];

            var attr_status=true;
            var attr_msg="";
            var empty_name = false;

            if(v.value_name==undefined || v.value_name==""){
                attr_status=false;
                empty_name = true;
            }
            //set default values
            if(v.data_type==undefined || v.data_type=="" || gb_datatypes.indexOf(v.data_type)<0){
				attr_status=false;
				attr_msg = attr_msg+ " data_type";
            }
            if(v.value_unit==undefined || v.value_unit==""){
                attr_status=false;
                attr_msg = attr_msg+ " value_unit";
            }
            if(v.value_type==undefined || v.value_type==""|| gb_value_types.filter(element => (element.value===v.value_type)).length==0){
                attr_status=false;
                attr_msg =attr_msg+ " value_type";
            }
            if(v.editable!="0" && v.editable!="1"){
                attr_status=false;
                attr_msg =attr_msg+ " editable";
            }
            if(v.healthiness_criteria==undefined || v.healthiness_criteria==""||healthiness_criteria_options.indexOf(v.healthiness_criteria)<0){
                attr_status=false;
                attr_msg =attr_msg+ " healthiness_criteria";
            }
            if(v.healthiness_value==undefined || v.healthiness_value==""){
                attr_status=false;
                attr_msg =attr_msg+ " healthiness_value";
            }

            if (attr_status==false) {
                all_attr_status=false;
                if(empty_name) {
                    all_attr_msg= "The attribute name cannot be empty";
                    if(attr_msg != "") {
                        all_attr_msg= all_attr_msg+", other errors in: "+attr_msg;
                    }
                }
                else {
                    all_attr_msg= "For the attribute: "+ v.value_name+", error in: "+attr_msg;
                }
            }
        }

        if (!all_attr_status) {
            answer.isvalid=false;
            msg= msg+ " -"+all_attr_msg;
        }
    }

    if(answer.isvalid) {
        return answer;
    }
    else {
        answer.message=msg;
        return answer;
    }
}
function isLatitude(lat) {
    return isFinite(lat) && Math.abs(lat) <= 90;
}

function isLongitude(lng) {
    return isFinite(lng) && Math.abs(lng) <= 180;
}

function removeDuplicates(arr){
    let unique_array = []
    for(let i = 0;i < arr.length; i++){
        if(unique_array.indexOf(arr[i].replace("-","")) == -1){
            unique_array.push(arr[i])
        }
    }
    return unique_array
}

Array.prototype.removeDuplicatesSchema = function( arr ) {
    return this.filter( function( val ) {
        return arr.indexOf( val ) < 0;
    });
};

var requestLoop = setInterval(function(){
    retrieveDataCaller(schema);
}, 20000);
