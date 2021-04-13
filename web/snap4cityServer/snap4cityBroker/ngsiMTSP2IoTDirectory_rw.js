//values retrieved from snapIoTDirectory_cbRetrieval_c 
var ORION_CB = process.argv[2];
var ORION_ADDR = process.argv[3];
var USER = process.argv[4]
var ACCESS_LINK = process.argv[5];
var ACCESS_PORT = process.argv[6];
var MODEL = process.argv[7];
var EDGE_GATEWAY_TYPE = process.argv[8];
var EDGE_GATEWAY_URI = process.argv[9];
var ORGANIZATION = process.argv[10];
var PATH = process.argv[11];
var KINDBROKER = process.argv[12];
var APIKEY = process.argv[13];
var TOKEN = process.argv[14];
//Static values
var ORION_PROTOCOL = "ngsi w/MultiService";
var KIND = "sensor";
var FORMAT = "json";
var FREQUENCY = 600;

/* global variables */
var modelsdata = [];
var gb_value_units = [];
var gb_datatypes = [];
var gb_value_types = [];
var schema;

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

const { removeDuplicates, flatten } = require('./Functions/functions.js');
const { insertDevices, insertValues, getModels } = require('./Functions/db_functions.js')
const { manageExtractionRulesAtt, manageExtractionRulesDev,manageOtherParameters, verifyDevice, getLocation } = require('./Functions/manageData.js')

var XMLHttpRequest = require("xmlhttprequest").XMLHttpRequest;

const ini = require('ini');

var conf_links = ini.parse(fs.readFileSync('./snap4cityBroker/conf/links.ini', 'utf-8'));
var apiLink = conf_links.links.link_api;

const config = ini.parse(fs.readFileSync('./snap4cityBroker/conf/db_config.ini', 'utf-8'));
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

cid.connect(function (err) {
    if (err) {
        console.log('Error connecting to Db');
        throw err;
    }
    console.log('Connection established');
});

/*Retrieve types */
var req = new XMLHttpRequest();
var link = apiLink + "device.php";
req.open("POST", link + "?action=get_param_values&token=" + TOKEN, false);
req.onreadystatechange = function () {
	if (this.readyState == 4 && this.status == 200) {
        let resp = JSON.parse(this.responseText);
        gb_datatypes = resp["data_type"];
        gb_value_units = resp["value_unit"];
        gb_value_types = resp["value_type"];
        console.log("dictionary retrieved");
    } else if (this.readyState == 4 && this.status != 200) {
        console.log("dictionary error " + this.status);
    }
}
req.send()

req = new XMLHttpRequest();
var connLink = apiLink + "deviceDiscoveryApi.php?action=getCBServiceTree&contextbroker=" + ORION_CB + "&token=" + TOKEN;
req.open("POST", connLink, false);
req.onreadystatechange = function () {
    if (this.readyState == 4 && this.status == 200) {
        schema = JSON.parse(this.responseText)["content"];
        if (schema.length > 0) {
            console.log("tenants/paths structure retrieved");
            inferImplicitPaths(schema);
        } else {
            console.log("tenants/paths structure empty response!");
        }
    } else if (this.readyState == 4 && this.status != 200) {
        console.log("tenants/paths structure error " + this.status);
    }
}
req.send();

function inferImplicitPaths(schema) {
    for (let i = 0; i < schema.length; i++) {
        let tenant = schema[i].service;
        let path = schema[i].servicePath;
        let slashes = (path.split("/")).length - 1;
        for (let j = 0; j < slashes; j++) {
            let found = false;
            path = path.substring(0, path.lastIndexOf("/"));
            for (let k = 0; k < schema.length; k++) {
                if (schema[k].service == schema[i].service) {
                    if (schema[k].servicePath == path) {
                        found = true;
                        break;
                    }
                }
            }
            if ((!found) && (path.length != 0)) {	//PATH avoid empty path, otherwise take all the device!
                schema.push(new Object({ service: tenant, servicePath: path }));
                console.log("Path inferred: " + path + " in tenant: " + tenant);
            }
        }
    }
    //console.log(content);
    retrieveDevices(schema)
}

function retrieveDevices(schema) {
    if (KINDBROKER.localeCompare("external") == 0) {
        //TODO: what about management of https? it has to be specieied in the accessLink!
        if (!ACCESS_LINK.startsWith("http"))
            ACCESS_LINK = "http://" + ACCESS_LINK;

        if (ACCESS_PORT !== undefined && ACCESS_PORT.localeCompare("null") !== 0 && ACCESS_PORT.localeCompare("") !== 0) {
            ACCESS_LINK = ACCESS_LINK + ":" + ACCESS_PORT;
        }

        if (PATH.localeCompare("null") == 0 || PATH.localeCompare("") == 0 || PATH == undefined) {
            link = ACCESS_LINK;
        }
        else {
            link = ACCESS_LINK + PATH;
        }
    }
    else {
        // TODO : do we manage internal periofi update? NO!
        // throw an erro in this case or avoid management at all
        if (PATH.localeCompare("null") == 0 || PATH == undefined || PATH == null || PATH.localeCompare(" ") == 0) {
            link = 'http://' + ORION_ADDR;
        }
        else {
            link = 'http://' + ORION_ADDR + PATH;
        }
    }
    link = link + '/v2/entities'
    console.log("Connecting link is:" + link);
    retrieveDataCaller(schema);
}

function retrieveDataCaller(schema) {
    console.log("Retrieving data...");
    for (let i = 0; i < schema.length; i++) {
        var xhttp = new XMLHttpRequest();
        retrieveData(xhttp, link, schema[i].service, schema[i].servicePath);
    }
}

function retrieveData(xhttp, link, service, servicePath) {

    var promiseAcquisition = new Promise(function (resolve2, reject) {

        xhttp = new XMLHttpRequest();

        xhttp.open("GET", link, true);
        if (APIKEY !== null || APIKEY !== undefined) {
            xhttp.setRequestHeader("apikey", APIKEY);
        }
        xhttp.setRequestHeader("Fiware-Service", service);
        xhttp.setRequestHeader("Fiware-ServicePath", servicePath);
        xhttp.send();

        xhttp.onreadystatechange = function () {

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

                if (typeof modelsdata === undefined || MODEL.localeCompare("custom") == 0 || modelsdata.length <= 0)
                    modelsdata = getModels(cid, modelsdata);

                var sql = "(SELECT id FROM temporary_devices WHERE contextBroker = '" + ORION_CB + "' AND service = '" + service + "' AND servicePath = '" + servicePath + "') UNION (SELECT id FROM devices WHERE contextBroker = '" + ORION_CB + "' AND service = '" + service + "' AND servicePath = '" + servicePath + "')";

                cid.query(sql, function (err, result, fields) {
                    if (err) {
                        console.log("sql " + sql);
                        throw err;
                    }
                    for (i = 0; i < result.length; i++) {
                        registeredDevices.push(result[i].id.replace(service, "").replace(servicePath, "").replace("..", ""));
                    }

                    //checking if the devices already exist in the platform
                    //console.log("registeredDevices " +registeredDevices.length + " orion length "+ orionDevices.length);
                    var newDevices = orionDevices.diff(registeredDevices);
                    console.log("There are " + newDevices.length + " new devices for the broker " + ORION_CB + "in tenant " + service + " in path " + servicePath + ".");

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
                            //console.log(topic);
                            if (orionDevicesSchema[topic] == undefined) {
                                //console.log("topic undefined " + topic);
                                continue;
                            }

                            sesc, attProperty = manageExtractionRulesAtt(extractionRulesAtt, ORION_CB, topic, orionDevicesSchema[topic], sesc);
                            sesc, attProperty = manageOtherParameters(ORION_CB, topic, orionDevicesSchema[topic],sesc, attProperty)
                            devAttr = manageExtractionRulesDev(extractionRulesDev, orionDevicesSchema[topic], devAttr)

                            //if no rules are defined for mac, k1, k2, set tthis field to empty
                            if (devAttr["mac"] == undefined)
                                devAttr["mac"] = "";
                            if (devAttr["k1"] == undefined)
                                devAttr["k1"] = "";
                            if (devAttr["k2"] == undefined)
                                devAttr["k2"] = "";
                            if (devAttr["producer"] == undefined)
                                devAttr["producer"] = "";
                            if (devAttr["latitude"] == undefined && devAttr["longitude"] == undefined){
                                var location = getLocation(orionDevicesSchema[topic]);
                                devAttr["latitude"] = location[0]
                                devAttr["longitude"] = location[1]
                            }
								
                            if (devAttr["subnature"] == undefined)
                                devAttr["subnature"] = "";
                            if (devAttr["static_attributes"] == undefined)
                                devAttr["static_attributes"] = "[]";
                            if (devAttr["devicetype"] == undefined)
                                devAttr["devicetype"] = orionDevicesType[topic];

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

                            var verify = verifyDevice(toVerify, modelsdata, gb_datatypes, gb_value_types, gb_value_units);
                            var validity = "invalid";
                            if (verify.isvalid)
                                validity = "valid";
                        

                            se.push(
                                storeDevice(
                                    USER,
                                    topic,
                                    MODEL,
                                    KIND,
                                    devAttr["devicetype"],
                                    ORION_PROTOCOL,
                                    FREQUENCY,
                                    FORMAT,
                                    ORION_CB,
									devAttr["latitude"],
									devAttr["longitude"],
                                    devAttr["mac"],
                                    validity,
                                    verify.message,
                                    "no",
                                    ORGANIZATION,
                                    EDGE_GATEWAY_TYPE,
                                    EDGE_GATEWAY_URI,
                                    devAttr["k1"],
                                    devAttr["k2"],
                                    service,
                                    servicePath,
                                    devAttr["subnature"],
                                    devAttr["static_attributes"],
                                    devAttr["producer"]
                                ));

                        }//end for i

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
                                if(sesc.length!=0){
                                    insertValues(cid, sesc);
                                }
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

    promiseAcquisition.then(function (resolve2) {
        //console.log(resolve2);
    },
        function (error) {
            console.log("promise then error: " + error);
        });

}//end retrieveData function


/* store a device in the db*/
function storeDevice(user, deviceID, model, kind, type, protocol, frequency, format, cb, latitude, longitude, macaddress, status, validity_msg, shouldberegistered,
    organization, edge_type, edge_uri, k1, k2, service, servicePath, subnature, staticAttributes, producer) {
    return [user, deviceID, model, kind, type, protocol, frequency, format, cb, latitude, longitude, macaddress, status, validity_msg, shouldberegistered, organization, edge_type,
        edge_uri, k1, k2, service, servicePath, subnature, staticAttributes, producer];
}

/*functions */
Array.prototype.diff = function (arr) {
    //console.log ("diff");
    return this.filter(function (val) {
        //console.log("arr.index "+ arr.indexOf(val)+ " val "+ val);
        return arr.indexOf(val) < 0;
    });
};
var requestLoop = setInterval(function () {
    retrieveDataCaller(schema);
}, 20000);
