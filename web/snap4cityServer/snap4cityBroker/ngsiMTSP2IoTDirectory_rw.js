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
var FREQUENCY_SEARCH = process.argv[15];

//console.log('frequenza: '+FREQUENCY_SEARCH);
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

var mysql = require('mysql');
var Promise = require('promise');
const fs = require('fs');
var FileSaver = require('file-saver');
var Blob = require('blob');
var http = require("http");
var Parser = require('./Parser/Classes/Parser');

const { removeDuplicates } = require('./Functions/functions.js');
const { insertDevices, insertValues, getModels } = require('./Functions/db_functions.js');
const { manageExtractionRulesAtt, manageExtractionRulesDev, manageOtherParameters, verifyDevice, getLocation, findNewValues, uuidv4 } = require('./Functions/manageData.js');

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
    database: c_database,
    multipleStatements: true
});

cid.connect(function (err) {
    if (err) {
        console.log('Error connecting to Db '+c_host+' '+c_port+' '+c_user+' '+c_password+' '+c_database);
        throw err;
    }
    console.log('Connection established to Db');
});

var limit = 1000;
var offset2 = 500;
var offset = 0;
var smallSearch = 0;

if (FREQUENCY_SEARCH.localeCompare("null") == 0 || FREQUENCY_SEARCH == undefined || FREQUENCY_SEARCH == null) {
    FREQUENCY_SEARCH = 20000;
}

/*Retrieve types */
var req = new XMLHttpRequest();
var link = apiLink + "device.php";
req.open("POST", link + "?action=get_param_values", false);
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
            console.log("tenants/paths structure retrieved "+JSON.stringify(schema));
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
    console.log(JSON.stringify(schema));
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
    if (!link.includes('/v2/entities')) link = link + '/v2/entities';
    // link = link + '/v2/entities'
    console.log("Connecting link is:" + link);
    retrieveDataCaller(schema);
}

function retrieveDataCaller(schema) {
    console.log("Retrieving data...");
    for (let i = 0; i < schema.length; i++) {
        limit = 1000;
        offset2 = 500;
        offset = 0;
        smallSearch = 0;
        var xhttp = new XMLHttpRequest();
        retrieveData(xhttp, link, schema[i].service, schema[i].servicePath, limit, offset);
    }
}

console.log(ORION_CB);

function retrieveData(xhttp, link, service, servicePath, limit, offset) {
    var orionDevices = [];
    var orionDevicesType = {};
    var orionDevicesSchema = {};
    var registeredDevices = [];
    var registeredDevicesWithPath = [];
    var newDevices = [];
    var extractionRulesAtt = new Object();
    var extractionRulesDev = new Object();
    var se = [];
    var sesc = [];

    Promise.all([
        makeRequestToCB(xhttp, link, service, servicePath, limit, offset),
        getRegisteredDevices(service, servicePath),
        getTemporaryDevices(service, servicePath),
        getExtractionRules(service, servicePath)
    ]).then(function ([result1, result2, result3, result4]) {
        var obj = JSON.parse(result1);

        if (obj instanceof Array) {
            for (i = 0; i < obj.length; i++) {
                if (obj[i].type != "LogEntry") {
                    let index = obj[i].id;
                    orionDevices.push(index);
                    orionDevicesSchema[index] = obj[i];
                    orionDevicesType[index] = obj[i].type;
                }

            }
        } else {
            if (obj.type != "LogEntry") {
                orionDevices.push(obj.id);
                orionDevicesSchema[obj.id] = obj;
                orionDevicesType[obj.id] = obj.type;
            }

        }
        //console.log(JSON.stringify(orionDevicesType));

        if (typeof modelsdata === undefined || MODEL.localeCompare("custom") == 0 || modelsdata.length <= 0)
            modelsdata = getModels(cid, modelsdata);

        registeredDevices = result2.registeredDevices;
        registeredDevicesWithPath = result2.registeredDevicesWithPath;
        temporaryDevices = result3.temporaryDevices;
        extractionRulesAtt = result4.extractionRulesAtt;
        extractionRulesDev = result4.extractionRulesDev;
		


        findNewValues(cid, ORION_CB, orionDevices, orionDevicesSchema, registeredDevicesWithPath, temporaryDevices, extractionRulesAtt);

        allDevices = registeredDevices.concat(temporaryDevices);
        newDevices = orionDevices.diff(allDevices);
        newDevices = removeDuplicates(newDevices);

        console.log("There are " + newDevices.length + " new devices for the broker " + ORION_CB + " in tenant " + service + " in path " + servicePath + ".");



        for (var i = 0; i < newDevices.length; i++) {
            var attProperty = [];
            var devAttr = new Object();
            var topic = newDevices[i];

            if (orionDevicesSchema[topic] == undefined) {
                //console.log("topic undefined " + topic);
                continue;
            }

            sesc, attProperty = manageExtractionRulesAtt(extractionRulesAtt, ORION_CB, topic, orionDevicesSchema[topic], sesc);
            sesc, attProperty = manageOtherParameters(ORION_CB, topic, orionDevicesSchema[topic], sesc, attProperty)
            devAttr = manageExtractionRulesDev(extractionRulesDev, orionDevicesSchema[topic], devAttr)

            if (devAttr["mac"] == undefined)
                devAttr["mac"] = "";
            if (devAttr["k1"] == undefined)
                devAttr["k1"] = uuidv4();
            if (devAttr["k2"] == undefined)
                devAttr["k2"] = uuidv4();
            if (devAttr["producer"] == undefined)
                devAttr["producer"] = "";
            if (devAttr["latitude"] == undefined && devAttr["longitude"] == undefined) {
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

            se.push([
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
            ]);

        }//end for i
        if (se.length != 0) {
            //if there are devices to be inserted
            insertDevices(cid, se).then(function () {
                if (sesc.length != 0) {
                    //if there are attributes to be inserted
                    insertValues(cid, sesc, "temporary_event_values")
                    // .then(function () {
                    //     if (!smallSearch) {
                    //         offset2 = offset2 + 100;

                    //         xhttp = new XMLHttpRequest();
                    //         retrieveData(xhttp, link, service, servicePath, 100, offset2);

                            
                            
                    //         console.log("**UPDATE**");
                    //     }
                    //     else {
                    //         offset2 = offset2 + 1;
                    //         xhttp = new XMLHttpRequest();
                    //         retrieveData(xhttp, link, service, servicePath, 1, offset2);
                    //     }
                    // })
                } 
                // else {
                    // if (!smallSearch) {
                    //     offset2 = offset2 + 100;

                    //     xhttp = new XMLHttpRequest();
                
                    //     retrieveData(xhttp, link, service, servicePath, 100, offset2);
                    //     console.log("**UPDATE**");
                    // }
                    // else {
                    //     offset2 = offset2 + 1;
                    //     xhttp = new XMLHttpRequest();

                    //     retrieveData(xhttp, link, service, servicePath, 1, offset2);
                    // }
                // }
            });
        }
        if(obj instanceof Array && obj.length > 1){
            if (!smallSearch) {
                offset2 = offset2 + 500;

                xhttp = new XMLHttpRequest();
        
                retrieveData(xhttp, link, service, servicePath, 500, offset2);
                console.log("**UPDATE**");
            }
            else {
                offset2 = offset2 + 1;
                xhttp = new XMLHttpRequest();

                retrieveData(xhttp, link, service, servicePath, 1, offset2);
            }
        }


    }).catch(function (err) {
        console.log(err)
        if (!smallSearch) {
            smallSearch = 1;
            retrieveData(xhttp, link, service, servicePath, 1, offset2);
        }
    })

}//end retrieveData function



function makeRequestToCB(xhttp, link, service, servicePath, limit, offset) {
    return new Promise(function (resolve, reject) {
        xhttp = new XMLHttpRequest();
        
        if (limit != undefined && offset != undefined) {
            link = link + "?limit=" + limit + "&offset=" + offset;
        }
        console.log("Link split " + link);

        xhttp.open("GET", link, true);
        if (APIKEY !== null || APIKEY !== undefined) {
            xhttp.setRequestHeader("apikey", APIKEY);
        }
        xhttp.setRequestHeader("Fiware-Service", service);
        xhttp.setRequestHeader("Fiware-ServicePath", servicePath);

        xhttp.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                resolve(this.responseText)
            } else if (this.readyState == 4 && this.status == 500) {
                reject({
                    status: this.status,
                    statusText: this.statusText
                });
            }
        };
        xhttp.onerror = function () {
            reject({
                status: this.status,
                statusText: this.statusText
            });
        };
        xhttp.send()
    })
}

function getRegisteredDevices(service, servicePath) {
    return new Promise(function (resolve, reject) {
        var registeredDevices = [];
        var registeredDevicesWithPath = [];
        var sql = "SELECT id FROM devices WHERE contextBroker = '" + ORION_CB + "' AND service = '" + service + "' AND servicePath = '" + servicePath + "';";

        cid.query(sql, function (error, result, fields) {
            if (error) {
                reject(error)
				return;
            }

            for (i = 0; i < result.length; i++) {
                registeredDevices.push(result[i].id.replace(service, "").replace(servicePath, "").replace("..", ""));
                registeredDevicesWithPath.push(result[i].id);
            }

            //checking if the devices already exist in the platform
            //console.log("registeredDevices " +registeredDevices.length + " orion length "+ orionDevices.length);
            resolve({
                registeredDevices: registeredDevices,
                registeredDevicesWithPath: registeredDevicesWithPath,
            })
        })
    })
}

function getTemporaryDevices(service, servicePath) {
    return new Promise(function (resolve, reject) {
        var temporaryDevices = [];
        //var registeredDevicesWithPath = [];
        var sql = "SELECT id FROM temporary_devices WHERE contextBroker = '" + ORION_CB + "' AND service = '" + service + "' AND servicePath = '" + servicePath + "';"

        cid.query(sql, function (error, result, fields) {
            if (error) {
                reject(error)
				return;
            }

            for (i = 0; i < result.length; i++) {
                //registeredDevices.push(result[i].id.replace(service, "").replace(servicePath, "").replace("..", ""));
                temporaryDevices.push(result[i].id);
            }

            resolve({
                temporaryDevices: temporaryDevices
            })
        })


    })
}

function writeFreqAndTimestampStatus(){
	return new Promise(function (resolve, reject){
	var query= "UPDATE `iotdb`.`contextbroker` SET `req_frequency`='"+ FREQUENCY_SEARCH +"', timestampstatus=NOW() WHERE `name`='" + ORION_CB + "';"; // query update rows with freq and Timestamp  Status
	cid.query(query, function(error, result){
		 console.log('result: '+JSON.stringify(result)+JSON.stringify(error))
            if (error) {
                reject(error);
                return;
            }
		resolve(result);
	});
	
	});
}

function getExtractionRules(service, servicePath) {
    return new Promise(function (resolve, reject) {
        var extractionRulesDev = new Object();
        var extractionRulesAtt = new Object();
        var query = "SELECT * FROM extractionRules where contextbroker='" + ORION_CB + "'AND service = '" + service + "' AND servicePath = '" + servicePath + "';";
        cid.query(query, function (error, resultRules, fields) {
            console.log('result: '+JSON.stringify(resultRules)+JSON.stringify(error))
            if (error) {
                reject(error);
                return;
            }
            for (var x = 0; x < resultRules.length; x++) {
                if (resultRules[x]["kind"].localeCompare("property") == 0) {
                    extractionRulesDev[resultRules[x]["id"]] = resultRules[x];
                } else {
                    extractionRulesAtt[resultRules[x]["id"]] = resultRules[x];
                }
            }
            resolve({
                extractionRulesDev: extractionRulesDev,
                extractionRulesAtt: extractionRulesAtt
            });
        });
    });
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
	writeFreqAndTimestampStatus().then(value=>{console.log(value+" Update db with freq and timestamp")});
}, FREQUENCY_SEARCH);
