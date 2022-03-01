var XMLHttpRequest = require("xmlhttprequest").XMLHttpRequest;
var Promise = require('promise');

function insertDevices(cid, se) {
	return new Promise(function (resolve, reject) {
		//console.log("insertDevices:" + se);
		var protocol = se[0][5]

		if (protocol == "ngsi") {
			var sqlse = "INSERT INTO `temporary_devices`(`username`,`id`,`model`, `kind`,`devicetype`,`protocol`,`frequency`,`format`,`contextBroker`,`latitude`,`longitude`,macaddress,`status`,`validity_msg`,`should_be_registered`,`organization`,`edge_gateway_type`,`edge_gateway_uri`,`k1`,`k2`,`subnature`,`static_attributes`, `producer`) VALUES ?";
		} else if (protocol == "ngsi w/MultiService") {
			var sqlse = "INSERT INTO `temporary_devices`(`username`,`id`,`model`, `kind`,`devicetype`,`protocol`,`frequency`,`format`,`contextBroker`,`latitude`,`longitude`,macaddress,`status`,`validity_msg`,`should_be_registered`,`organization`,`edge_gateway_type`,`edge_gateway_uri`,`k1`,`k2`,`service`,`servicePath`,`subnature`,`static_attributes`, `producer`) VALUES ?";
		}


		cid.query(sqlse, [se], function (errSens) {
			if (errSens) {
				console.log("devices insert error ");
				//console.log(errSens);
				reject(errSens)
				return;
			}
			resolve();
			// console.log("fatto");
		});
	})

}

function insertValues(cid, sesc, table) {
	return new Promise(function (resolve, reject) {
		//console.log("insertValues");
		var sqlsesc = "INSERT INTO `" + table + "`(`device`, `cb`,`value_name`, `data_type`,`value_type`,`value_unit`, `editable`, `healthiness_criteria`,`value_refresh_rate`,`old_value_name`) VALUES ?";
		//console.log("Sesc "+ JSON.stringify(sesc));
		cid.query(sqlsesc, [sesc], function (errSSch) {
			if (errSSch) {
				console.log("values insert error ");
				reject(errSens);
				return;
			}
			resolve();
		});
	});
}

//retrieve models directly from DB, it's ok because the model name is passed from aoutside
function getModels(cid, modelsdata) {
	var models = "SELECT * FROM model";
	cid.query(models, function (err, result, fields) {
		if (err) { console.log("sql " + sql); throw err; }
		for (i = 0; i < result.length; i++) {
			modelsdata.push(result[i]);
		}
	});
	return modelsdata;
}

//never used
function getParam(cid) {
	var gb_value_units = [];
	var gb_datatypes = [];
	var gb_value_types = [];
	var valueUnit = "SELECT value_type FROM value_types ORDER BY value_type";

	cid.query(valueUnit, function (err, result, fields) {

		// console.log(sql);
		if (err) { console.log("sql " + sql); throw err; }
		for (i = 0; i < result.length; i++) {
			gb_value_units.push(result[i].value_type);
		}

	}); //query

	var dataType = "SELECT data_type FROM data_types order by data_type";
	cid.query(dataType, function (err, result, fields) {
		console.log("result length dataType " + result.length);

		// console.log(sql);
		if (err) { console.log("sql " + sql); throw err; }
		for (i = 0; i < result.length; i++) {
			gb_datatypes.push(result[i].data_type);
		}
	}); //query
	var valueUnit = "SELECT DISTINCT value_unit_default FROM value_types ORDER BY value_unit_default";
	cid.query(valueUnit, function (err, result, fields) {
		console.log("result length valueunit " + result.length);

		// console.log(sql);
		if (err) { console.log("sql " + sql); throw err; }
		for (i = 0; i < result.length; i++) {
			gb_value_units.push(result[i].value_unit_default);
		}
	}); //query
	return gb_value_units, gb_datatypes, gb_value_types
}

module.exports = {
	insertDevices,
	insertValues,
	getModels,
	getParam
}