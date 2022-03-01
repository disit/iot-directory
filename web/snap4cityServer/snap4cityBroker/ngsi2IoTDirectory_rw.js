//values retrieved from snapIoTDirectory_cbRetrieval_c 
var ORION_CB = process.argv[2];
var ORION_ADDR = process.argv[3];
var USER = process.argv[4]
var ACCESS_LINK = process.argv[5];
var MODEL = process.argv[6];
var EDGE_GATEWAY_TYPE = process.argv[7];
var EDGE_GATEWAY_URI = process.argv[8];
var ORGANIZATION = process.argv[9];
var PATH = process.argv[10];
var KINDBROKER = process.argv[11];
var APIKEY = process.argv[12];
var ACCESS_PORT = process.argv[13];
var TOKEN = process.argv[14];
var FREQUENCY_SEARCH = process.argv[15];

//Static values
var ORION_PROTOCOL = "ngsi";
var KIND = "sensor";
var FREQUENCY = 10;
var FORMAT = "json";

/* global variables */
var modelsdata = [];
var gb_value_units = [];
var gb_datatypes = [];
var gb_value_types = [];

var express = require('express');
var request = require('request');

/* MYSQL setup */
var mysql = require('mysql');
var Promise = require('promise');
const fs = require('fs');
var FileSaver = require('file-saver');
var Blob = require('blob');
const ini = require('ini');

var conf_links = ini.parse(fs.readFileSync('./snap4cityBroker/conf/links.ini', 'utf-8'));
var apiLink = conf_links.links.link_api;

const config = ini.parse(fs.readFileSync('./snap4cityBroker/conf/db_config.ini', 'utf-8'));
const c_host = config.database.host;
const c_user = config.database.user;
const c_port = config.database.port;
const c_password = config.database.password;
const c_database = config.database.database;

/* ORION setup */
var http = require("http");
var Parser = require('./Parser/Classes/Parser');

const { removeDuplicates } = require('./Functions/functions.js');
const { insertDevices, insertValues, getModels } = require('./Functions/db_functions.js');
const { manageExtractionRulesAtt, manageExtractionRulesDev, verifyDevice, getLocation, manageOtherParameters, findNewValues, uuidv4 } = require('./Functions/manageData.js')

var XMLHttpRequest = require("xmlhttprequest").XMLHttpRequest;

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

/*  MAIN PROGRAM */

var link = "";
var limit = 1000;
var offset2 = 900;
var offset = 0;
var smallSearch = 0;

if(FREQUENCY_SEARCH.localeCompare("null") == 0 || FREQUENCY_SEARCH == undefined || FREQUENCY_SEARCH == null){
	FREQUENCY_SEARCH = 20000;
}


if (!ACCESS_LINK.startsWith("http"))
	ACCESS_LINK = "http://" + ACCESS_LINK;

if (ACCESS_PORT !== undefined && ACCESS_PORT.localeCompare("null") !== 0 && ACCESS_PORT.localeCompare("") !== 0)
	ACCESS_LINK = ACCESS_LINK + ":" + ACCESS_PORT;

if (KINDBROKER.localeCompare("external") == 0) {
	if (PATH.localeCompare("null") == 0 || PATH.localeCompare("") == 0 || PATH == undefined) {
		link = ACCESS_LINK;
	}
	else {
		link = ACCESS_LINK + PATH;
	}
}
else {
	if (PATH.localeCompare("null") == 0 || PATH == undefined || PATH == null || PATH.localeCompare(" ") == 0) {
		link = 'http://' + ORION_ADDR;
	}
	else {
		link = 'http://' + ORION_ADDR + PATH;
	}
}

console.log(ORION_CB);

var xhttp = new XMLHttpRequest();

function retrieveData(xhttp, link, limit, offset) {
	var orionDevices = [];
	var orionDevicesType = {};
	var orionDevicesSchema = {};
	var registeredDevices = [];
	var temporaryDevices = [];
	var newDevices = [];
	var extractionRulesAtt = new Object();
	var extractionRulesDev = new Object();
	var se = [];
	var sesc = [];

	Promise.all([
		makeRequestToCB(xhttp, link, limit, offset),
		getRegisteredDevices(),
		getTemporaryDevices(),
		getExtractionRules()
	]).then(function ([result1, result2, result3, result4]) {
		var obj = JSON.parse(result1);
		if (obj instanceof Array) {

			for (i = 0; i < obj.length; i++) {
				let index = obj[i].id;
				orionDevices.push(index);
				orionDevicesSchema[index] = obj[i];
				orionDevicesType[index] = obj[i].type;
			}
		}
		else {
			orionDevices.push(obj.id);
			orionDevicesSchema[obj.id] = obj;
			orionDevicesType[obj.id] = obj.type;
		}

		if (typeof modelsdata === undefined || MODEL.localeCompare("custom") == 0 || modelsdata.length <= 0)
			modelsdata = getModels(cid, modelsdata);

		registeredDevices = result2.registeredDevices;
		temporaryDevices = result3.temporaryDevices;
		extractionRulesAtt = result4.extractionRulesAtt;
		extractionRulesDev = result4.extractionRulesDev;

		findNewValues(cid, ORION_CB, orionDevices, orionDevicesSchema, registeredDevices, temporaryDevices, extractionRulesAtt);

		allDevices = registeredDevices.concat(temporaryDevices);
		newDevices = orionDevices.diff(allDevices);
		newDevices = removeDuplicates(newDevices);
		console.log("There are " + newDevices.length + " new devices for the broker " + ORION_CB);

		
		
		for (var i = 0; i < newDevices.length; i++) {
			var attProperty = [];
			var topic = newDevices[i];
			var devAttr = new Object();

			if (orionDevicesSchema[topic] == undefined) {
				//console.log("topic undefined " + topic);
				continue;
			}

			sesc, attProperty = manageExtractionRulesAtt(extractionRulesAtt, ORION_CB, topic, orionDevicesSchema[topic], sesc);
			sesc, attProperty = manageOtherParameters(ORION_CB, topic, orionDevicesSchema[topic], sesc, attProperty)
			devAttr = manageExtractionRulesDev(extractionRulesDev, orionDevicesSchema[topic], devAttr)

			//if no rules are defined for mac, k1, k2, set tthis field to empty
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
				"contextBroker": ORION_CB,
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

			// console.log('MESSAGGIO: ' + verify.message)
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
				devAttr["subnature"],
				devAttr["static_attributes"],
				devAttr["producer"]
			]);
		}//end for i 
		if (se.length != 0) {
			//if there are devices to be inserted
			insertDevices(cid, se).then(function (resolve) {
				//if there are attributes to be inserted
				if (sesc.length != 0) {
					insertValues(cid, sesc, "temporary_event_values");
				}
			});
			if (!smallSearch) {
				offset2 = offset2 + 100;

				xhttp = new XMLHttpRequest();

				retrieveData(xhttp, link, 100, offset2);
				console.log("**UPDATE**");
			}
			else {
				offset2 = offset2 + 1;
				xhttp = new XMLHttpRequest();

				retrieveData(xhttp, link, 1, offset2);
			}
		}
	}).catch(function (err) {
		console.log(err);
		if (!smallSearch) {
			smallSearch = 1;
			retrieveData(xhttp, link, 1, offset2);
		}
	})

}//end retrieveData function

function makeRequestToCB(xhttp, link, limit, offset) {
	return new Promise(function (resolve, reject) {
		xhttp = new XMLHttpRequest();
		var linkNoLimit = link.split("?limit");
		if(!linkNoLimit[0].includes('/v2/entities')) link = linkNoLimit[0] + '/v2/entities';
		

		if (limit != undefined && offset != undefined) {
			link = link + "?limit=" + limit + "&offset=" + offset;
		}
		console.log("Link split " + link);

		xhttp.open("GET", link, true);

		if (APIKEY !== null || APIKEY !== undefined) {
			xhttp.setRequestHeader("apikey", APIKEY);
		}

		xhttp.onreadystatechange = function () {
			if (this.readyState == 4 && this.status == 200) {
				resolve(this.responseText)
			} else if (this.readyState == 4 && this.status == 500) {
				reject({
					status: this.status,
					statusText: this.statusText
				});
			}
		}
		xhttp.onerror = function () {
			reject({
				status: this.status,
				statusText: this.statusText
			});
		}
		xhttp.send();
	})
}

function getRegisteredDevices() {
	return new Promise(function (resolve, reject) {
		var registeredDevices = [];
		var sql = "SELECT id FROM devices WHERE contextBroker = '" + ORION_CB + "';";

		cid.query(sql, function (error, result, fields) {
			if (error) {
				reject(error)
			}

			for (i = 0; i < result.length; i++) {
				registeredDevices.push(result[i].id);
			}

			//checking if the devices already exist in the platform
			//console.log("registeredDevices " +registeredDevices.length + " orion length "+ orionDevices.length);
			resolve({
				registeredDevices: registeredDevices,
			})
		})
	})
}

function getTemporaryDevices() {
	return new Promise(function (resolve, reject) {
		var temporaryDevices = [];
		var sql = "SELECT id FROM temporary_devices WHERE contextBroker = '" + ORION_CB + "' ;";

		cid.query(sql, function (error, result, fields) {
			if (error) {
				reject(error)
			}

			for (i = 0; i < result.length; i++) {
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
function getExtractionRules() {
	return new Promise(function (resolve, reject) {
		var extractionRulesDev = new Object();
		var extractionRulesAtt = new Object();
		var query = "SELECT * FROM extractionRules where contextbroker='" + ORION_CB + "';";
		cid.query(query, function (error, resultRules, fields) {
			//console.log('result: '+JSON.stringify(resultRules))
			if (error) {
				reject(error);
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
	retrieveData(xhttp, link, limit, offset);
	registeredDevices = [];
	writeFreqAndTimestampStatus().then(value=>{console.log(value+" Update db with freq and timestamp")});
}, FREQUENCY_SEARCH);