//values retrieved from snapIoTDirectory_cbRetrieval_c 
var ORION_CB = process.argv[2];
var DEVICE_NAME = process.argv[3];
var ORION_ADDR = process.argv[4];
var USER = process.argv[5];
var ACCESS_LINK = process.argv[6];
var ACCESS_PORT = process.argv[7];
var MODEL = process.argv[8];
var EDGE_GATEWAY_TYPE = process.argv[9];
var EDGE_GATEWAY_URI = process.argv[10];
var ORGANIZATION = process.argv[11];
var PATH = process.argv[12];
var KINDBROKER = process.argv[13];
var APIKEY = process.argv[14];
var SERVICE = process.argv[15];
var SERVICE_PATH = process.argv[16];
var TOKEN = process.argv[17];

//Static values
var ORION_PROTOCOL = "ngsi";
var MAC = "3D:F2:C9:A6:B3:4F";
var KIND = "sensor";
var FREQUENCY = 10;
var flagAntwerp = true;
var regexTimeZone0 = /^(\d{4,})-(\d{2})-(\d{2})[T ](\d{2}):(\d{2})(?::(\d{2}(?:\.\d+)?))[Z]?$/;
var regexTimeZone1 = /^(\d{4,})-(\d{2})-(\d{2})[T ](\d{2}):(\d{2})(?::(\d{2}(?:\.\d+)?))?$/;
var _serviceIP = "../stubs";
var deviceAttributes = ["uri", "devicetype", "kind", "macaddress", "producer", "latitude", "longitude", "protocol", "format", "frequency", "k1", "k2"];

/* global variables */
var registeredDevices = [];
var orionDevices = [];
var orionDevicesSchema = [];
var orionDevicesType = [];
var modelsdata = [];
var httpRequestOutput = "";
var gb_value_units = [];
var gb_datatypes = [];
var gb_value_types = [];

var _serviceIP = "../stubs";

var express = require('express');
var request = require('request');

/* MYSQL setup */
var mysql = require('mysql');
var Promise = require('promise');
const fs = require('fs');
var FileSaver = require('file-saver');
var Blob = require('blob');
var Parser = require('./Parser/Classes/Parser');

const { isInt, isFloat } = require('./Functions/functions.js');
const { manageExtractionRulesAtt, manageExtractionRulesDev, manageOtherParameters, verifyDevice, getLocation } = require('./Functions/manageData.js')

/* ORION setup */
var http = require("http");

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
	//console.log('Connection established');
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
		//console.log("dictionary retrieved");
	} else if (this.readyState == 4 && this.status != 200) {
		console.log("dictionary error " + this.status);
	}
}
req.send()

/*  MAIN PROGRAM */

var httpRequestOutput = "";

link = "";
var limit = 1000;
var offset2 = 900;
var offset = 0;
var smallSearch = 0;

if (!ACCESS_LINK.startsWith("http"))
	ACCESS_LINK = "http://" + ACCESS_LINK;

if (ACCESS_PORT !== undefined && ACCESS_PORT.localeCompare("null") !== 0 && ACCESS_PORT.localeCompare("") !== 0)
	ACCESS_LINK = ACCESS_LINK + ":" + ACCESS_PORT;

if (PATH == undefined || PATH.localeCompare("null") == 0 || PATH.localeCompare("") == 0) {
	link = ACCESS_LINK;
}
else {
	link = ACCESS_LINK + PATH;
}
// link = link + '/v2/entities'
if(!link.includes('/v2/entities')) link = link + '/v2/entities';

if (!link.includes(DEVICE_NAME)) {
	let link_provv = link.split("?");
	link = link_provv[0] + "/" + DEVICE_NAME;
	//console.log("Link " + link);
}
else {
	//console.log("notinclude " + link);
}

var xhttp = new XMLHttpRequest();

retrieveData(xhttp, link);

function retrieveData(xhttp, link) {
	var promiseAcquisition = new Promise(function (resolve2, reject) {
		xhttp = new XMLHttpRequest();
		xhttp.open("GET", link, true);
		if (APIKEY !== null && APIKEY !== undefined && APIKEY.localeCompare("null") !== 0)
			xhttp.setRequestHeader("apikey", APIKEY);
		xhttp.setRequestHeader("Fiware-Service", SERVICE);
		xhttp.setRequestHeader("Fiware-ServicePath", SERVICE_PATH);
		xhttp.send();

		xhttp.onreadystatechange = function () {
			//console.log("readyState " + this.readyState + " status " + this.status + this.responseText);
			if (this.readyState == 4 && this.status == 0) {
				console.error("not reacheable");
			}
			else if (this.readyState == 4 && this.status == 400) {
				console.error("path malformed");
			}
			else if (this.readyState == 4 && this.status == 404) {
				console.error("not found");
			}
			else if (this.readyState == 4 && this.status == 200) {
				//console.log("ready");
				//function that manages the output in order to create the data
				var responseText = this.responseText;
				//variable initialization
				orionDevices = [];
				orionDevicesType = [];
				orionDevicesSchema = new Object();

				var obj = JSON.parse(responseText);
				httpRequestOutput = "";

				//console.log('response: '+ responseText)

				if (obj instanceof Array) {
					for (i = 0; i < obj.length; i++) {
						let index = obj[i].id.toLowerCase();
						orionDevices.push(index);
						orionDevicesSchema[index.toLowerCase()] = obj[i];
						orionDevicesType[index.toLowerCase()] = obj[i].type;
					}
				}
				else {
					orionDevices.push(obj.id);
					orionDevicesSchema[obj.id.toLowerCase()] = obj;
					orionDevicesType[obj.id.toLowerCase()] = obj.type;
				}

				if (typeof modelsdata === undefined || MODEL.localeCompare("custom") == 0 || modelsdata.length <= 0)
					//checking if the devices already exist in the platform
					//console.log("registeredDevices " +registeredDevices.length + " orion length "+ orionDevices.length);
					var newDevices = orionDevices;
				//console.log("diff " +newDevices.length);
				//Checking duplicates into the same array
				var extractionRulesAtt = new Object();
				var extractionRulesDev = new Object();
				var promiseExtractionRules = new Promise(function (resolveExtraction, rejectExtraction) {
					if((SERVICE == undefined || SERVICE == "")&&(SERVICE_PATH == undefined || SERVICE_PATH == "")){
						var query = "SELECT * FROM extractionRules where contextbroker='" + ORION_CB + "';";
					}else{
						var query = "SELECT * FROM extractionRules where contextbroker='" + ORION_CB + "'AND service = '" + SERVICE + "' AND servicePath = '" + SERVICE_PATH + "';";
					}
					

					cid.query(query, function (err, resultRules, fields) {

						if (err) { console.log("sql " + query); throw err; }

						for (var x = 0; x < resultRules.length; x++) {
							//console.log("result rules");
							if (resultRules[x]["kind"].localeCompare("property") == 0) {
								extractionRulesDev[resultRules[x]["id"]] = resultRules[x];
							}
							else {
								//console.log("resultRules[x] "+ resultRules[x]["id"]);
								extractionRulesAtt[resultRules[x]["id"]] = resultRules[x];
							}

						}
						// if (resultRules.length == 0) {
						// 	console.log("extraction rules are empty");
						// 	rejectExtraction();
						// }
						// else {
						// 	//console.log("extraction rules are NOT empty");
						// 	resolveExtraction();
						// }
						resolveExtraction();
					}); //query

				});

				var se = [];
				var sesc = [];
				var ruleJSON, parser;
				promiseExtractionRules.then(function (resolveExtraction) {

					var type;
					var sensor;
					var rule;
					var devAttr = new Object();

					for (var i = 0; i < newDevices.length; i++) {
						var attProperty = [];
						var topic = newDevices[i];

						if (orionDevicesSchema[topic.toLowerCase()] == undefined) {
							//console.log("topic undefined " + topic + "m " + JSON.stringify(orionDevicesSchema));
							continue;
						}

						sesc, attProperty = manageExtractionRulesAtt(extractionRulesAtt, ORION_CB, topic, orionDevicesSchema[topic.toLowerCase()], sesc);
						sesc, attProperty = manageOtherParameters(ORION_CB, topic, orionDevicesSchema[topic.toLowerCase()], sesc, attProperty)
						devAttr = manageExtractionRulesDev(extractionRulesDev, orionDevicesSchema[topic.toLowerCase()], devAttr)

						if (devAttr["mac"] == undefined)
							devAttr["mac"] = "";
						if (devAttr["k1"] == undefined)
							devAttr["k1"] = "";
						if (devAttr["k2"] == undefined)
							devAttr["k2"] = "";
						if (devAttr["producer"] == undefined)
							devAttr["producer"] = "";
						if (devAttr["latitude"] == undefined && devAttr["longitude"] == undefined) {
							var location = getLocation(orionDevicesSchema[topic.toLowerCase()]);
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
							"format": devAttr["format"],
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
						if(attProperty.length != 0){
							console.log(JSON.stringify(toVerify));
						}else{
							console.log("no parameters are found")
						}
						

					}//end for i 

				})//end then extraction rules
					.catch(error => {
						console.log(error)
						console.log("no extraction rules found, returning error msg");
						console.error("extraction rules not found");
					});

			}//end readystate == 4
			if (this.readyState == 4 && this.status == 500) {
				//console.log("reject");			
			}
		};//end onreadystatechange
	});//end promiseAcquisition
}

/* extract the attributes from the device value and store them in the db*/
function storeDeviceSchema(cb, attributes, deviceSchema, deviceID) {
	var arr = [];
	var longitude = "";
	var latitude = "";
	var ob = deviceSchema[deviceID];
	var value_type = "";

	console.log("storeDeviceSchema deviceSchema[deviceID] " + JSON.stringify(ob));

	//	console.log("gb_value_units length "+ gb_value_units.length);

	if (cb == 'Antwerp' && flagAntwerp) {
		for (i = 0; i < ob.length; i++) {
			att = ob[i];
			var split = (att.id).split(".");

			if (gb_value_units.indexOf(split[1]) >= 0) {
				value_type = split[1];
			}
			else {
				value_type = getValueType(att.id, att.type, att.value);
			}
			var data_type = getDataType(att.id, att.type, att.value);
			var value_unit = getValueUnit(value_type);
			// console.log("push "+deviceID + att.id + "value_type "+ value_type);
			if (value_type === null || value_type === undefined || value_type == '' || value_type == ' ') {
				value_type = 'rain';
			}
			if (data_type === null || data_type === undefined) {
				data_type = 'float';
			}

			latitude = "51.219890";
			longitude = "4.4034600";
			// console.log("Device id "+ deviceID + " cb "+ cb + " att.id " + att.id + " data type "+ data_type);
			arr.push([deviceID, cb, att.id, data_type, value_type, value_unit, "refresh_rate", 300, att.id]);
		}
	}
	else {
		//console.log("storeDeviceSchema" + JSON.stringify(ob));
		for (i = 0; i < attributes.length; i++) {
			//console.log("for "+JSON.stringify(attributes));
			att = attributes[i];
			//console.log("ATT" + JSON.stringify(att));
			if (att.name == "latitude") {
				latitude = deviceSchema[deviceID].latitude.value;
			}
			else if (att.name == "geolocalization_lat") {
				latitude = deviceSchema[deviceID].geolocalization_lat.value;
			}
			//	latitude=deviceSchema[deviceID].latitude.value;
			else if (att.name == "longitude") {
				longitude = deviceSchema[deviceID].longitude.value;
			}
			else if (att.name == "geolocalization_lon") {
				longitude = deviceSchema[deviceID].geolocalization_lon.value;
			}
			//	longitude=deviceSchema[deviceID].longitude.value;
			else if (att.name == "location" && flagAntwerp) {
				longitude = deviceSchema[deviceID].location.value.coordinates[0];
				latitude = deviceSchema[deviceID].location.value.coordinates[1];
			}
			else if (att.name == "location") {
				longitude = deviceSchema[deviceID].location.value.coordinates[1].toFixed(4);
				latitude = deviceSchema[deviceID].location.value.coordinates[0].toFixed(4);
			}


			// arr.push([deviceID,cb,att.name,att.type,att.value,att.position]);
			value_type = getValueType(att.name, att.type, att.value);
			var data_type = getDataType(att.name, att.type, att.value);
			var value_unit = getValueUnit(value_type);
			//	 console.log("Device id "+ deviceID + " cb "+ cb + " att.id " + att.id + " data type "+ data_type);

			arr.push([deviceID, cb, att.name, data_type, value_type, value_unit, "refresh_rate", 300, att.name]);
		}
	}
	//console.log("latitude: "+latitude + " longitude: "+ longitude);
	return { "arr": arr, "latitude": latitude, "longitude": longitude };
}

/* extract the device schema from the NGSI-9/10 representation adopted by Orion  
*/

function extractSchema(value) {
	//console.log("Extract schema valueType "+ gb_value_types.length + " units " + gb_value_units.length + "data type " + gb_datatypes.length);

	var attributes = [];
	var f = ""; // identified format
	//	console.log("valore processato " + value);

	if (ORION_CB == 'Antwerp' && flagAntwerp) {
		//console.log("extract antw");
		attributes = parseAntwerpJSON(value);
	}
	else {
		//console.log("extractorion");
		attributes = parseOrionJSON(value);
	}
	// console.log("attributes "+JSON.stringify(attributes));
	return { "format": "json", "attr": attributes };
}

function parseAntwerpJSON(obj) {
	var attributes = [];
	// {"id":"ARDUINO_ST_4201_1516802097","type":"Temperature","Temperature":{"type":"float","value":"20.0","metadata":{}},"geolocalization":{"type":"string","value":"45.453701,9.214914","metadata":{}},"measure_units":{"type":"string","value":"Celsius","metadata":{}},"timestamp":{"type":"integer","value":"1516802097","metadata":{}}}
	var pos = 1;
	for (var prop in obj) {
		//console.log(obj[prop].type);
		// console.log(obj[prop].value); 
		if (obj[prop].type == null) {
			attributes.push({
				"name": prop, "type": obj[prop].type, /* uncommented by sara 2711*/"value": obj[prop].value,
				"position": pos
			});
		}
		else {
			attributes.push({
				"name": prop, "type": obj[prop].type.toLowerCase(), /* uncommented by sara 2711*/"value": obj[prop].value,
				"position": pos
			});
		}
		pos++;
	}
	return attributes;
}

function parseOrionJSON(obj) {
	console.log("ParseOrionJSON" + JSON.stringify(obj));
	var attributes = [];
	// {"id":"ARDUINO_ST_4201_1516802097","type":"Temperature","Temperature":{"type":"float","value":"20.0","metadata":{}},"geolocalization":{"type":"string","value":"45.453701,9.214914","metadata":{}},"measure_units":{"type":"string","value":"Celsius","metadata":{}},"timestamp":{"type":"integer","value":"1516802097","metadata":{}}}
	var pos = 1;
	for (var prop in obj) {

		if (prop != "id" && prop != "type") {
			console.log("prop parseOrionJSON " + prop);
			// console.log(prop);
			// console.log(obj[prop].type);
			// console.logconsole.log(obj[prop].value);
			//console.log("name:"+prop+"; type:"+obj[prop].type.toLowerCase()+"; value: "+obj[prop].value + ";position: "+pos);
			attributes.push({
				"name": prop, "type": obj[prop].type, "value": obj[prop].value,
				"position": pos
			});

			pos++;
		}
	}
	return attributes;
}

function getValueType(valuename, type, value) {
	//deviceID, cb, att.name, att.type
	if (type != null && type != undefined) {

		type = returnString(type.toString());
		type = type.toLowerCase();

		if (valuename === undefined || gb_value_units === undefined || gb_value_units.length <= 0)
			return null;

		var name = returnString(valuename.toString());
		name = name.toLowerCase();

		//console.log("NAME "+name);
		//console.log("gb_value_units "+gb_value_units);
		if (gb_value_units.indexOf(name) >= 0) {
			//console.log("if name units");
			return name;
		}
		if (regexTimeZone0.test(value) || regexTimeZone1.test(value) || name.localeCompare("dateobserved") == 0)
			return "timestamp";
		if (name.localeCompare("lamax") == 0 || name.localeCompare("laeq") == 0)
			return "sound_lv";

		if (name.localeCompare("sonometerclass") == 0)
			return "audio";

		if (name.localeCompare("location") == 0)
			return "latitude_longitude";

		if (name.localeCompare("geolocalization_lat") == 0)
			return "latitude";

		if (name.localeCompare("geolocalization_lon") == 0)
			return "longitude";

		if (name.localeCompare("description") == 0 || name.localeCompare("refairqualityobservedmodel") == 0 || name.localeCompare("nome") == 0 || name.localeCompare("stazione") == 0)
			return "entity_desc";

		if (type === undefined)
			return null;

		if (type.localeCompare("binary") == 0)
			return "button";

		if (name.localeCompare("model") == 0)
			return "status";

		if (name.localeCompare("no") == 0 || name.localeCompare("no2") == 0)
			return "NO2_concentration";

		if (name.localeCompare("pm25") == 0 || name.localeCompare("pm2.5") == 0)
			return "PM2.5_concentration";

		if (name.localeCompare("pm10") == 0)
			return "PM10_concentration";

		if (name.localeCompare("bc") == 0)
			return "benzene_concentration";

		if (name.localeCompare("temperature") == 0 || name.localeCompare("temperatura") == 0)
			return "temperature";

		if (name.localeCompare("stato") == 0 || name.localeCompare("quota") == 0)
			return "status";


		if (type.localeCompare("structuredvalue") == 0 && typeof (value) !== 'object' && name.localeCompare("refdevice") != 0) {
			var splitVar = (value.toString()).split("|");
			splitVar[0] = returnString(splitVar[0].toString());

			/*To remove blank spaces
			for(var i = 0; i < splitVar.length; i++){
				splitVar[i] = splitVar[i].split(' ').join('');
				console.log("splitVar "+splitVar[i]);
			}*/
			if (splitVar[0].localeCompare("lamax") == 0 || splitVar[0].localeCompare("laeq") == 0) {
				//console.log("sound_lv");
				return "sound_lv";
			}
			/*else{
				var vector = [];
				//get the vector 
				for(var i = 1; i < splitVar.length; i++){
					vector.push(splitVar[i]);
				}
				var res;	
				res = getValueType(splitVar[i],type, vector);
				if(res != null)
					return res;
			}*/
		}
		if (typeof (value) === 'object')
			return "latitude_longitude";
		return null;
	}// end if(type != null && type != undefined
	return null;
}

function getDataType(valuename, type, value) {
	//deviceID, cb, att.name, att.type
	if (type != null && type != undefined) {

		type = returnString(type.toString());
		type = type.toLowerCase();

		if (valuename === undefined || gb_datatypes === undefined || gb_datatypes.length <= 0)
			return null;

		var name = returnString(valuename.toString());
		name = name.toLowerCase();

		//If the data type is in the list
		if (gb_datatypes.indexOf(type) >= 0) {
			return type;
		}
		if (regexTimeZone0.test(value) || regexTimeZone1.test(value))
			return "time";

		if (type.localeCompare("text") == 0)
			return "string";

		if (type.localeCompare("number") == 0) {
			if (isInt(value))
				return "integer";
			if (isFloat(value))
				return "float";
		}

		if (type.localeCompare("structuredvalue") == 0 && typeof (value) !== 'object') {
			console.log("value" + value);
			var splitVar = (value.toString()).split("|");

			splitVar[1] = returnString(splitVar[1].toString());
			splitVar[1] = returnString(splitVar[1]);
			//To remove blank spaces
			/*for(var i = 0; i < splitVar.length; i++){
				splitVar[i] = splitVar[i].split(' ').join('');
				console.log("splitVar "+splitVar[i]);
			}*/
			if (isInt(splitVar[1]))
				return "integer";
			if (isFloat(splitVar[1]))
				return "float";

			/*else{
				var vector = [];
				//get the vector 
				for(var i = 1; i < splitVar.length; i++){
					vector.push(splitVar[i]);
				}
				var res;	
				res = getValueType(splitVar[i],type, vector);
				if(res != null)
					return res;
			}*/
		}
		if (typeof (value) === 'object')
			return "float";
		return null;
	}// end if(type != null && type != undefined
	return null;
}
function getValueUnit(value) {
	//console.log("value switch "+value);
	switch (value) {
		case "temperature": return "°C";
		case "speed": return "km/h";
		case "humidity": return "%";
		case "power": return "W";
		case "status": return "#";
		case "pressure": return "hPa";
		case "orientation": return "deg";
		case "timestamp": return "s";
		case "string": return "#";
		case "integer": return "#";
		case "float": return "#";
		case "benzene_concentration": return "ppm";
		case "NO_concentration": return "ppm";
		case "NO2_concentration": return "ppm";
		case "PM10_concentration": return "ppm";
		case "PM2.5_concentration": return "ppm";
		default: return "#";
	}

	return null;
}
function returnString(str) {
	var str2 = "";
	for (var i = 0; i < str.length; i++) {
		var c = str.charAt(i);
		if (c.match(/^[a-zA-Z0-9_.-]*$/)) {
			str2 = str2 + c;
		}
	}
	return str2;
}

/*functions */
Array.prototype.diff = function (arr) {
	//console.log ("diff");
	return this.filter(function (val) {
		//console.log("arr.index "+ arr.indexOf(val)+ " val "+ val);
		return arr.indexOf(val) < 0;
	});
};