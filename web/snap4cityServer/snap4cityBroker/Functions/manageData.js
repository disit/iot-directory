var Parser = require('../Parser/Classes/Parser')
const { performance } = require('perf_hooks');
const { isLatitude, isLongitude, isTest, flatten, getIndexofValueType } = require('./functions.js')
const { insertValues } = require('./db_functions.js')
var deviceAttributes = ["uri", "devicetype", "kind", "macaddress", "producer", "latitude", "longitude", "protocol", "format", "frequency", "k1", "k2", "subnature", "static_attributes"];
var alreadyRetrived = ['id', 'type', 'latitude', 'longitude', 'location', 'model']

function manageExtractionRulesAtt(extractionRulesAtt, orion_cb, device_id, schema, sesc) {
	var attProperty = []

	for (var j in extractionRulesAtt) {
		rule = extractionRulesAtt[j]["selector"];
		var id = extractionRulesAtt[j]["id"];
		rule = JSON.stringify(rule);

		rule = rule.replace(/\\/g, "");

		rule = rule.replace("PM2,5", "PM2x5");
		rule = rule.replace("PM2.5", "PM2y5");

		rule = rule.slice(1, rule.length - 1);
		let jsonRules = JSON.parse(rule);

		parser = new Parser();
		let typeData = (jsonRules.type).toUpperCase();

		parser.addObjRule(jsonRules, typeData);
		let v = schema;
		v = JSON.stringify(v);
		v = v.replace("PM2,5", "PM2x5");
		v = v.replace("PM2.5", "PM2y5");

		let parserApply = parser.applyRules(v);

		var attName, value_type, data_type;

		for (var p = 0; p < parserApply.length; p++) {

			if (extractionRulesAtt[j]["value_type"].startsWith("{")) {

				let parserValue = new Parser();
				let ruleValue = extractionRulesAtt[j]["value_type"];
				ruleValue = JSON.stringify(ruleValue);

				ruleValue = ruleValue.replace(/\\/g, "");
				ruleValue = ruleValue.slice(1, rule.length - 1);
				let jsonRuleValue = JSON.parse(ruleValue);
				parserValue.addObjRule(jsonRuleValue, "JSON");
				let v2 = schema;
				v2 = JSON.stringify(v2);
				let parserApply2 = parserValue.applyRules(v2);
				value_type = parserApply2[0];

			} else {
				value_type = extractionRulesAtt[j]["value_type"];
			}

			//------name extraction ----
			if (typeData == 'JSON') {
				let toSplit = (jsonRules.param.s).split(".");
				attName = toSplit[toSplit.length - 1];
			} else {
				if (extractionRulesAtt[j]["structure_flag"].localeCompare("yes") == 0) {
					let toSplit = (jsonRules.param.s).split(".");
					attName = toSplit[toSplit.length - 1];
				} else {
					console.log('parseapply: ' + JSON.stringify(parserApply))
					if (parserApply[p].id !== undefined) {
						attName = parserApply[p].id;
					} else if (parserApply[p] !== undefined) {
						attName = parserApply[p];
					} else {
						attName = id;
					}
				}
			}


			if (extractionRulesAtt[j]["data_type"] == null || extractionRulesAtt[j]["data_type"].length == 0) {
				data_type = parserApply[p].type;
			} else {
				data_type = extractionRulesAtt[j]["data_type"];
			}
			//JSON.stringify(attName) perchè attName = id = extractionRulesAtt[j]["id"];
			// attName = JSON.stringify(attName).replace("PM2x5", "PM2,5");

			attName = attName.replace("PM2x5", "PM2,5");
			attName = attName.replace("PM2y5", "PM2.5");

			let objProp = {
				"value_name": attName,
				"value_type": value_type,
				"data_type": data_type,
				"value_unit": extractionRulesAtt[j]["value_unit"],
				"editable": "0",
				"healthiness_criteria": "refresh_rate",
				"healthiness_value": 300
			};
			//console.log("objProp " + JSON.stringify(objProp));
			attProperty.push(objProp);
			sesc.push([device_id, orion_cb, objProp.value_name, objProp.data_type, objProp.value_type, objProp.value_unit, objProp.editable, objProp.healthiness_criteria, objProp.healthiness_value, objProp.value_name]);
		}
	}//end for j
	return sesc, attProperty;
}

function manageOtherParameters(orion_cb, device_id, schema, sesc, attProperty) {

	// console.log(JSON.stringify(attProperty))
	for (var i = 0; i < attProperty.length; i++) {
		alreadyRetrived.push(attProperty[i].value_name)
	}
	for (var propName in schema) {
		if (propName != undefined && deviceAttributes.indexOf(propName) < 0 && alreadyRetrived.indexOf(propName) < 0) {
			// console.log(propName)
			// console.log(schema[propName].type)
			if (schema[propName] !== null && typeof schema[propName] === 'object' && schema[propName].value != undefined) {
				var data_type = (schema[propName].type != undefined) ? schema[propName].type : ""
				let objProp = {
					"value_name": propName,
					"value_type": "",
					"data_type": data_type.toLowerCase(),
					"value_unit": "",
					"editable": false,
					"healthiness_criteria": "refresh_rate",
					"healthiness_value": 300
				};
				attProperty.push(objProp);
				sesc.push([device_id, orion_cb, objProp.value_name, objProp.data_type, objProp.value_type, objProp.value_unit, objProp.editable, objProp.healthiness_criteria, objProp.healthiness_value, objProp.value_name]);
			}
		}
	}
	return sesc, attProperty
}

function manageExtractionRulesDev(extractionRulesDev, schema, devAttr) {
	for (var j in extractionRulesDev) {

		var nameDev = extractionRulesDev[j]["id"];
		if (deviceAttributes.includes(nameDev)) {
			let ruleDev = extractionRulesDev[j]["selector"];
			ruleDev = JSON.stringify(ruleDev);

			ruleDev = ruleDev.replace(/\\/g, "");
			ruleDev = ruleDev.slice(1, ruleDev.length - 1);
			//	console.log("ruleDev "+ ruleDev);

			let jsonRulesDev = JSON.parse(ruleDev);
			parserDev = new Parser();

			parserDev.addObjRule(jsonRulesDev, "JSON");
			// console.log("jsonRuleDev: "+ruleDev);
			let vDev = schema;
			//	console.log("vDev "+ JSON.stringify(vDev));
			vDev = JSON.stringify(vDev);
			// console.log('vdev: '+vDev)
			//		console.log("before apply rules");
			let parserApply = parserDev.applyRules(vDev);

			devAttr[nameDev] = parserApply[0];
			console.log("devAttr" + devAttr[nameDev] + " namedev " + nameDev);
		}

	}//end for j dev
	return devAttr;

}

function verifyDevice(deviceToverify, modelsdata, gb_datatypes, gb_value_types, gb_value_units) {

	gb_datatypes = flatten(gb_datatypes)
	gb_value_types = flatten(gb_value_types)
	gb_value_units = flatten(gb_value_units)

	var msg = "";
	var regexpMAC = /^([0-9A-F]{2}[:-]){5}([0-9A-F]{2})$/;
	var answer = { "isvalid": true, "message": "Your device is valid" };


	// console.log("First checking its properties validity");

	if (deviceToverify.name == undefined || deviceToverify.name.length < 5 || deviceToverify.name == null) { msg += "-name is mandatory, of 5 characters at least."; }
	if (deviceToverify.devicetype == undefined || deviceToverify.devicetype == "" || deviceToverify.devicetype.indexOf(' ') >= 0 || deviceToverify.devicetype == null) { msg += "-devicetype is mandatory."; }
	if (deviceToverify.macaddress != undefined && deviceToverify.macaddress != null && deviceToverify.macaddress != "" && !regexpMAC.test(deviceToverify.macaddress)) { msg += "-macaddress format should be Letter (A-F) and number (eg. 3D:F2:C9:A6:B3:4F)."; }
	if (deviceToverify.frequency == undefined || deviceToverify.frequency == "" || !isFinite(deviceToverify.frequency) || deviceToverify.frequency == null) { msg += "-frequency is mandatory, and should be numeric."; }
	if (deviceToverify.kind == undefined || deviceToverify.kind == "" || deviceToverify.kind == null) { msg += "-kind is mandatory."; }
	if (deviceToverify.protocol == undefined || deviceToverify.protocol == "" || deviceToverify.protocol == null) { msg += "-protocol is mandatory."; }
	if (deviceToverify.format == undefined || deviceToverify.format == "" || deviceToverify.format == null) { msg += "-format is mandatory."; }
	if (deviceToverify.latitude == undefined || !isLatitude(deviceToverify.latitude) || deviceToverify.latitude == null) { msg += "-Latitude is mandatory, with the correct numeric format."; }
	if (deviceToverify.longitude == undefined || !isLongitude(deviceToverify.longitude || deviceToverify.longitude == null)) { msg += "-Longitude is mandatory, with the correct numeric format."; }
	if (deviceToverify.k1 == undefined || deviceToverify.k1 == "" || deviceToverify.k1 == null) { msg += "-k1 is mandatory."; }
	if (deviceToverify.k2 == undefined || deviceToverify.k2 == "" || deviceToverify.k2 == null) { msg += "-k2 is mandatory."; }

	//verify consistency subnature and its attributes
	if (deviceToverify.subnature !== "") {
		// TODO remove this comment or enable in another way this verify... 
		// if was removed beecause it delay too much
		//if (!verifySubnature(deviceToverify.subnature, deviceToverify.static_attributes)){
		//    	answer.isvalid=false;
		//            msg+="-The static attributes of the device do not comply with its subnature ("+deviceToverify.subnature+")";
		//    }
	}

	if (msg.length > 0) answer.isvalid = false;
	if (deviceToverify.deviceValues.length < 1) {
		answer.isvalid = false;
		msg += "-Your device should at least have 1 attributes.";
	}

	if (deviceToverify.model != "custom") {

		for (var i = 0; i < modelsdata.length; i++) {
			if (modelsdata[i].name != deviceToverify.model) {
				continue;
			}

			var modelAttributes = JSON.parse(modelsdata[i].attributes);

			if (Object.keys(modelAttributes).length != Object.keys(deviceToverify.deviceValues).length) {

				answer.isvalid = false;
				msg += "-Your device has different number of attributes than the selected model ";
			}

			else {

				for (var j = 0; j < deviceToverify.deviceValues.length; j++) {
					var found = 0;
					for (var l = 0; l < modelAttributes.length; l++) {
						if (modelAttributes[l].value_name == deviceToverify.deviceValues[j].value_name) {
							found = 1;

							var msg_attr_detail = ""

							if (modelAttributes[l].value_type != deviceToverify.deviceValues[j].value_type) { msg_attr_detail += " value type,"; }
							if (modelAttributes[l].data_type != deviceToverify.deviceValues[j].data_type) { msg_attr_detail += " data type,"; }
							if (modelAttributes[l].editable != deviceToverify.deviceValues[j].editable) { msg_attr_detail += " editable,"; }
							if (modelAttributes[l].healthiness_criteria != deviceToverify.deviceValues[j].healthiness_criteria) { msg_attr_detail += " healthiness criteria,"; }
							if (modelAttributes[l].healthiness_value != deviceToverify.deviceValues[j].healthiness_value) { msg_attr_detail += " healthiness value,"; }
							if (modelAttributes[l].value_unit != deviceToverify.deviceValues[j].value_unit) { msg_attr_detail += " value unit,"; }

							if (msg_attr_detail.length > 0) {
								answer.isvalid = false;
								msg += "The attribute " + deviceToverify.deviceValues[j].value_name + " has the details:" + msg_attr_detail + " not compatible with its model.";
							}
							else {
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

			/* console.log("modelsdata[i].edge_gateway_type");
				  console.log(modelsdata[i].edge_gateway_type);
				  console.log("deviceToverify.edge_gateway_type");
				  console.log(deviceToverify.edge_gateway_type);
				 */
			// var h3 = (modelsdata[i].edge_gateway_type == deviceToverify.edge_gateway_type) ||
			// 	(
			// 		(modelsdata[i].edge_gateway_type == undefined || modelsdata[i].edge_gateway_type == "" || modelsdata[i].edge_gateway_type == null) &&
			// 		(deviceToverify.edge_gateway_type == undefined || deviceToverify.edge_gateway_type == "" || deviceToverify.edge_gateway_type == null)

			// 	);


			if (modelsdata[i].contextbroker != deviceToverify.contextbroker) {
				answer.isvalid = false;
				msg += "-The device property: context broker does not comply with its model.";
			}
			if (modelsdata[i].devicetype != deviceToverify.devicetype) {
				answer.isvalid = false;
				msg += "-The device property: type does not comply with its model.";
			}
			// if (!h3) {
			// 	answer.isvalid = false;
			// 	msg += "-The device property: edge gateway type does not comply with its model.";
			// }
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

		}

	}

	else {

		var all_attr_msg = "";
		var all_attr_status = true;
		var healthiness_criteria_options = ["refresh_rate", "different_values", "within_bounds"];

		for (var i = 0; i < deviceToverify.deviceValues.length; i++) {
			var v = deviceToverify.deviceValues[i];


			var attr_status = true;
			var attr_msg = "";
			var empty_name = false;

			if (v.value_name == undefined || v.value_name == "") {
				attr_status = false;
				empty_name = true;
			}

			if (v.data_type == undefined || v.data_type == "" || gb_datatypes.indexOf(v.data_type) < 0) {
				attr_status = false;
				attr_msg = attr_msg + " data_type";
			}

			if (v.value_unit == undefined || v.value_unit == "" || getIndexofValueType(gb_value_units, v.value_unit) < 0) {
				attr_status = false;
				attr_msg = attr_msg + " value_unit";
			}

			if (v.value_type == undefined || v.value_type == "" || getIndexofValueType(gb_value_types, v.value_type) < 0) {
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
					all_attr_msg += "The attribute name cannot be empty";
					if (attr_msg != "") {
						all_attr_msg += all_attr_msg + ", other errors in: " + attr_msg;
					}
				}
				else {
					all_attr_msg += "For the attribute: " + v.value_name + ", error in: " + attr_msg + '; ';
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
	}
	else {
		answer.message = msg;
		return answer;
	}

}

function getLocation(schema) {
	/**
	"location": {
		"type": "geo:json",
		"value": {
			"type": "Point",
			"coordinates": [-3.712247222222222, 40.423852777777775]
		}
	}
	or
	"location": {
		"type": "GeoProperty",
		"value": {
			 "type": "Point",
			 "coordinates": [13.3986, 52.5547]
		}
	}
	or
	"latitude":{
		"type":"float",
		"value": "63.382782"
	}
	"longitude":{
		"type":"float",
		"value": "63.382782"
	}
	
	 */
	var latitude = "";
	var longitude = "";
	if (schema.latitude != undefined && schema.longitude != undefined) {
		latitude = schema.latitude.value;
		longitude = schema.longitude.value;
	} else if (schema.location != undefined) {
		if (schema.location.type != undefined && (schema.location.type == "geo:json" || schema.location.type == "GeoProperty")) {
			if (schema.location.value != undefined) {
				if (schema.location.value.type != undefined && schema.location.value.type == "Point") {
					if (schema.location.value.coordinates instanceof Array && schema.location.value.coordinates.length == 2) {
						longitude = schema.location.value.coordinates[0]
						latitude = schema.location.value.coordinates[1]
					}
				}
			}
		}
	}

	return [latitude, longitude]
}

function findNewValues(cid, orion_cb, orionDevices, orionDevicesSchema, registeredDevices, idTemporaryDevices/*, extractionRulesAtt*/) {
	/**
	 * orionDevices: 		id of devices of context broker
	 * orionDevicesSchema: 	schema of device, with values
	 * registeredDevices: 	id of devices registered and temporary devices
	 * temporaryEventValue:	temporary parameters
	 * eventValue: 			parameters of devices registered
	 * 
	 * for MTSP registered device id is: tenant1./path1.MT_SP_Test_device_01
	 */

	var sql = "(SELECT cb, device, value_name FROM temporary_event_values WHERE cb = '" + orion_cb + "')";
	sql += " UNION (SELECT cb, device, value_name FROM event_values WHERE cb = '" + orion_cb + "')";
	sql += "UNION  (SELECT cb, device, value_name FROM temporary_event_values_for_registered_devices WHERE cb = '" + orion_cb + "')";

	var valuesTemporaryDevices = [];
	var valuesRegisteredDevices = [];

	cid.query(sql, function (err, result, fields) {
		if (err) {
			console.log("sql " + sql);
			throw err;
		}
		//passare anche extractionRulesAtt?
		var eventValue = result;
		//var sesc = []
		var idRegisteredDevices = [];

		//registeredDevices= registeredDevices.concat(idTemporaryDevices);

		for (var id of registeredDevices) {
			idRegisteredDevices.push(id.split(".").slice(-1)[0])	//if path is present, remove it from id
		}


		for (var id of orionDevices) {
			if (idRegisteredDevices.includes(id)) {
				var index = idRegisteredDevices.indexOf(id)
				var schema = orionDevicesSchema[id];
				var params_name = Object.keys(schema);

				for (var param of params_name) {
					var isAlreadyAdded = false;
					for (var i = 0; i < eventValue.length; i++) {
						var registeredParam = eventValue[i];
						if (registeredParam.cb == orion_cb && registeredParam.device == registeredDevices[index] && registeredParam.value_name == param) isAlreadyAdded = true;
					}
					if (!isAlreadyAdded && deviceAttributes.indexOf(param) < 0 && alreadyRetrived.indexOf(param) < 0) {

						//chiamare manageExtractionRulesAtt() e ricevere sesc con objprop completo se c'è l'extraction rule,
						//altrimenti lo inserisco come sotto.
						var prop = { [param]: schema[param] }
						//console.log(prop)
					//	var _, attProperty = manageExtractionRulesAtt(extractionRulesAtt, orion_cb, registeredDevices[index], prop, [])
						//console.log(attProperty[0])
				/*		var objProp = new Object()
						if (attProperty[0] != undefined) {
							objProp = attProperty[0];
						} else {*/
							if (schema[param] !== null && typeof schema[param] === 'object' && schema[param].value != undefined) {
								var data_type = (schema[param].type != undefined) ? schema[param].type : ""
								objProp = {
									"value_name": param,
									"value_type": "",
									"data_type": data_type.toLowerCase(),
									"value_unit": "",
									"editable": "0",
									"healthiness_criteria": "refresh_rate",
									"healthiness_value": 300
								};
							}

					//	}
						if (objProp.value_name != undefined) {
							valuesRegisteredDevices.push([registeredDevices[index], orion_cb, objProp.value_name, objProp.data_type, objProp.value_type, objProp.value_unit, objProp.editable, objProp.healthiness_criteria, objProp.healthiness_value, objProp.value_name]);

						}
					}
				}

			}
			else if (idTemporaryDevices.includes(id)) {
				//var index = idRegisteredDevices.indexOf(id)
				var schema = orionDevicesSchema[id];
				var params_name = Object.keys(schema);

				for (var param of params_name) {
					var isAlreadyAdded = false;
					for (var i = 0; i < eventValue.length; i++) {
						var registeredParam = eventValue[i];
						if (registeredParam.cb == orion_cb && registeredParam.device == id && registeredParam.value_name == param) isAlreadyAdded = true;
					}
					if (!isAlreadyAdded && deviceAttributes.indexOf(param) < 0 && alreadyRetrived.indexOf(param) < 0) {

						//chiamare manageExtractionRulesAtt() e ricevere sesc con objprop completo se c'è l'extraction rule,
						//altrimenti lo inserisco come sotto.

						var prop = { [param]: schema[param] }
						//console.log(prop)
			//			var _, attProperty = manageExtractionRulesAtt(extractionRulesAtt, orion_cb, registeredDevices[index], prop, [])
						//console.log(attProperty[0])
						var objProp = new Object()
			/*			if (attProperty[0] != undefined) {
							objProp = attProperty[0];
						} else {
*/							if (schema[param] !== null && typeof schema[param] === 'object' && schema[param].value != undefined) {
								var data_type = (schema[param].type != undefined) ? schema[param].type : ""
								objProp = {
									"value_name": param,
									"value_type": "",
									"data_type": data_type.toLowerCase(),
									"value_unit": "",
									"editable": "0",
									"healthiness_criteria": "refresh_rate",
									"healthiness_value": 300
								};
							}
	//					}
						if (objProp.value_name != undefined) {
							valuesTemporaryDevices.push([id, orion_cb, objProp.value_name, objProp.data_type, objProp.value_type, objProp.value_unit, objProp.editable, objProp.healthiness_criteria, objProp.healthiness_value, objProp.value_name]);

						}
					}
				}
			}
		}
		// console.log('values temporary devices')
		// console.log(valuesTemporaryDevices);
		// console.log('values registered devices')
		// console.log(valuesRegisteredDevices)
		if (valuesTemporaryDevices.length > 0) {
			insertValues(cid, valuesTemporaryDevices, "temporary_event_values")
		}
		if (valuesRegisteredDevices.length > 0) {
			insertValues(cid, valuesRegisteredDevices, "temporary_event_values_for_registered_devices")
		}
	})
}

function uuidv4() {
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



module.exports = {
	manageExtractionRulesAtt,
	manageExtractionRulesDev,
	verifyDevice,
	getLocation,
	manageOtherParameters,
	findNewValues,
	uuidv4
}