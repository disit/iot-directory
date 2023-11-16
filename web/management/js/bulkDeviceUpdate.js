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
var dataTable = "";
requiredHeaders = ["name", "devicetype", "macaddress", "frequency", "kind", "protocol", "format", "producer", /*"edge_gateway_type", "edge_gateway_uri",  commented by Sara*/ "latitude", "longitude", "value_name", "data_type", "value_type", "editable", "value_unit", "healthiness_criteria", "healthiness_value", "k1", "k2", "subnature", "static_attributes", "service", "service_path"];
var gb_datatypes = "";
var gb_value_units = "";
var gb_value_types = "";
var defaultPolicyValue = [];
var devicenamesArray = { 'if': 0, 'then': 0 };
var valueNamesArray = { 'if': 0, 'then': 0 };
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
var timerID = undefined;
var was_processing = 0;
// var list_temporary_event_value = ""
var indexValues = 0;//it keeps track of unique identirier on the values, so it's possible to enforce specific value type

//--------to get the datatypes items----------
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
		if (mydata["status"] === 'ok') {
			gb_datatypes = mydata["data_type"];
			gb_value_units = mydata["value_unit"];
			gb_value_types = mydata["value_type"];
			addSubnature($("#selectSubnatureM"), mydata["subnature"]);
		}
		else {
			console.log("error getting the data types " + data);
		}
	},
	error: function (mydata) {
		console.log(JSON.stringify(mydata));
		alert("Network errors. <br/> Get in touch with the Snap4City Administrator<br/>" + JSON.stringify(mydata));
	}
});

//--------to get the list of context broker----------
$.ajax({
	url: "../api/contextbroker.php",
	data: {
		action: "get_all_contextbroker",
		token: sessionToken
	},
	type: "POST",
	async: true,
	success: function (data) {
		if (data["status"] === 'ok') {
			addCB($("#selectContextBrokerM"), data);
			addCB($("#selectContextBrokerLD"), data);
		}
		else {
			console.log("error getting the context brokers " + data);
		}
	},
	error: function (data) {
		console.log("error in the call to get the context brokers " + data);
		alert("Network errors. <br/> Get in touch with the Snap4City Administrator<br/>" + JSON.stringify(data));
	}
});

//--------to get the models with their details----------------------
$.ajax({
	url: "../api/model.php",
	data: {
		action: "get_all_models",
		token: sessionToken
	},
	type: "POST",
	async: true,
	success: function (data) {
		if (data["status"] === 'ok') {
			modelsdata = data["content"];
			addModel($("#selectModelLD"), data);
		}
		else {
			console.log("error getting the context brokers " + data);
		}
	},
	error: function (data) {
		console.log("error in the call to get the context brokers " + data);
		alert("Network errors. <br/> Get in touch with the Snap4City Administrator<br/>" + JSON.stringify(data));
	}
});

//--------to get the list of temporary event_value----------
// $.ajax({
// 	url: "../api/bulkDeviceUpdate.php",
// 	data: {
// 		action: "get_all_temporary_attributes",
// 		should_be_registered: "no",
// 		token: sessionToken,
// 	},
// 	datatype: 'json',
// 	type: "POST",
// 	async: true,
// 	success: function (data) {
// 		if (data["status"] === 'ok') {
// 			list_temporary_event_value = data.content;
// 		}
// 		else {
// 			console.log("error getting temporary event values " + data);
// 		}
// 	},
// 	error: function (data) {
// 		console.log("error in the call to get  temporary event values " + data);
// 		alert("Network errors. <br/> Get in touch with the Snap4City Administrator<br/>" + JSON.stringify(data));
// 	}
// });

//--------------------Ajax call function to upload file data---------------------//
//sara 1510 start
function sendJsonToDb(jsondata) {
	var progress_modal = document.getElementById('myModal');
	var span = document.getElementsByClassName("close")[0];
	var spin = document.getElementById("loader_spin");
	var progress_ok = document.getElementById('progress_ok');

	var jsondataPiece = jsondata;
	var more = false;

	if (jsondata.length > 100) {
		jsondataPiece = jsondata.slice(0, 100);
		jsondata = jsondata.slice(100, jsondata.length);
		more = true;
	}

	$.ajax({
		url: "../api/bulkDeviceLoad.php",
		data: {
			action: "insert",
			//username: loggedUser,
			jsondata: JSON.stringify(jsondataPiece),
			token: sessionToken,
			//organization:organization
		},
		type: "POST",
		async: true,//ATTENTION
		dataType: "JSON",
		timeout: 0,//ATTENTION- itr was 2000, do we really need it?
		success: function (mydata) {
			var user_message_old = document.getElementById('myModalBody').innerHTML;

			if (mydata['content'] != undefined) {
				var content = mydata['content'];
				content = content[0];

				if (content == undefined) {
					user_message_old = document.getElementById('myModalBody').innerHTML;
					document.getElementById('myModalBody').innerHTML = user_message_old + "No device is inserted";
				}
				else {
					var user_message = "";
					for (var i = 0; i < content.length; i++) {
						//console.log("for i "+i+" length "+content[i].inserted);

						if (content[i].inserted == 'ok') {

							if (content[i].duplicated != undefined) {
								user_message = "Device: " + content[i].device + " on context broker " + content[i].cb + " correctly uploaded, " + content[i].duplicated + " attribute is duplicated, only one inserted;";
							}
							else {
								user_message = "Device: " + content[i].device + " on context broker " + content[i].cb + " correctly uploaded;";
							}//Sara2210 end

							user_message_old = document.getElementById('myModalBody').innerHTML;
						}
						else if (content[i].inserted == 'ko') {
							//user_message="Device: "+content[i].device+" on context broker "+ content[i].cb +" is invalid,  not inserted;";
							user_message = "</br>" + mydata["msg"] + "</br>";
							user_message_old = "";// document.getElementById('myModalBody').innerHTML;			
						}
						document.getElementById('myModalBody').innerHTML = user_message_old + "<p>" + user_message + "</p>";

					}
				}
			}
			else {
				user_message = mydata["msg"];
				document.getElementById('myModalBody').innerHTML = user_message_old + "<p>" + user_message + "</p>";

			}
		},//end success
		error: function (mydata) {
			var user_message_old = document.getElementById('myModalBody').innerHTML;
			var user_message = mydata.msg;

			document.getElementById('myModalBody').innerHTML = user_message_old + "<p> No device is inserted </p>";
			console.log("Error inserting device " + mydata["msg"]);
		},
		complete: function () {
			if (more) {
				setTimeout(function () {
					sendJsonToDb(jsondata);
				}, 500);
			}
			else {
				setTimeout(function () {
					var user_message_old = document.getElementById('myModalBody').innerHTML;
					user_message_old = user_message_old.replace("Uploading your data...", "");
					document.getElementById('myModalBody').innerHTML = user_message_old;
					progress_ok.style.display = "block";
					spin.style.display = "none";
				}, 1000);
			}
		}
	});
}//end send json to db
//sara 1510 end

function getText(obj) {
	return obj.textContent ? obj.textContent : obj.innerText;
}


/*------ Method for read uploded csv file ------*/
function uploadDealcsv() { };

uploadDealcsv.prototype.getCsv = function (e) {
	$('#dealCsv').change(function () {

		if (this.files && this.files[0]) {
			myFile = this.files[0];

			//console.log(document.getElementById("labelinputFile").value);

			var extension = myFile.name.split('.').pop();
			//console.log(extension);
			if (extension != "csv") {
				myFile = undefined;
				alert("please insert a CSV file");
			}
			else {
				document.getElementById("labelinputFile").value = myFile.name;
			}
		}
	});
}



uploadDealcsv.prototype.getParsecsvdata = function (data) {

	//	let parsedata = [];

	//console.log(data);
	var data2 = data;

	try {
		data = utf8decode(data2);
	}
	catch (err){
		data = data2;
	}

	jsondata = csvJSON(data);
	//Sara2910 
	if (jsondata.length == 0) {
		alert("Your file is empty");
	}
	if (jsondata != undefined /*Sara2910 */ && jsondata.length != 0) {

		//console.log(jsondata);
		fileData = jsondata;

		valuesData = [];
		devicesData = [];

		for (var i = 0; i < fileData.length; i++) {
			jElement = fileData[i];
			jElement = fileData[i];
			var existsIndevices = 0;

			if (jElement.editable.toLowerCase() == "true") { //0910Fatima
				jElement.editable = "1";
			}
			else if (jElement.editable.toLowerCase() == "false") {//0910Fatima
				jElement.editable = "0";
			}

			for (var j = 0; j < devicesData.length; j++) {
				d = devicesData[j];

				if (d.name == jElement.name) {
					existsIndevices = 1;
					var deviceValues = d.deviceValues;
					var vld = { "value_name": jElement.value_name, "data_type": jElement.data_type, "value_type": jElement.value_type, "editable": jElement.editable, "value_unit": jElement.value_unit, "healthiness_criteria": jElement.healthiness_criteria, "healthiness_value": jElement.healthiness_value };
					deviceValues.push(vld);
					d.deviceValues = deviceValues;
				}
			}

			if (existsIndevices == 0) {
				var vd = { "name": jElement.name, "devicetype": jElement.devicetype, "macaddress": jElement.macaddress, "frequency": jElement.frequency, "kind": jElement.kind, "protocol": jElement.protocol, "format": jElement.format, "producer": jElement.producer, /*"edge_gateway_type":jElement.edge_gateway_type, "edge_gateway_uri":jElement.edge_gateway_uri, commented by Sara*/"latitude": jElement.latitude, "longitude": jElement.longitude, "validity_msg": jElement.validity_msg, "k1": jElement.k1, "k2": jElement.k2, "subnature": jElement.subnature, "static_attributes": jElement.static_attributes, "service": jElement.service, "service_path": jElement.service_path, "deviceValues": [{ "value_name": jElement.value_name, "data_type": jElement.data_type, "value_type": jElement.value_type, "editable": jElement.editable, "value_unit": jElement.value_unit, "healthiness_criteria": jElement.healthiness_criteria, "healthiness_value": jElement.healthiness_value }] };
				devicesData.push(vd);
			}

			var vlv = { "name": jElement.name, "value_name": jElement.value_name, "data_type": jElement.data_type, "value_type": jElement.value_type, "editable": jElement.editable, "value_unit": jElement.value_unit, "healthiness_criteria": jElement.healthiness_criteria, "healthiness_value": jElement.healthiness_value };
			valuesData.push(vlv);
		}//end for    

		fileData = devicesData;
		contextbroker = $('#selectContextBrokerLD').val();
		selectedModel = "custom";

		if ($('#selectModelLD').val() != undefined && $('#selectModelLD').val().length > 1) {
			selectedModel = $('#selectModelLD').val();
		}
		else {
			selectedModel = "custom";
		}
		edgegatewaytype = $('#selectGatewayTypeLD').val();
		edgegatewayuri = $('#gatewayUri').val();

		for (var i = 0; i < fileData.length; i++) {

			fileData[i]['contextbroker'] = contextbroker;
			fileData[i]['model'] = selectedModel;
			fileData[i]['edge_gateway_type'] = edgegatewaytype;
			fileData[i]['edge_gateway_uri'] = edgegatewayuri;

			var fd = fileData[i];
			var verification = verifyDevice(fd);
			if (verification.isvalid) {
				fileData[i]['status'] = 'valid';
				fileData[i]['validity_msg'] = verification.message;
			}
			else {
				fileData[i]['status'] = 'invalid';
				fileData[i]['validity_msg'] = verification.message;
			}
		}

		// HERE THE UPLOAD TO THE SERVER of FILEDATA
		var progress_modal = document.getElementById('myModal');
		var span = document.getElementsByClassName("close")[0];
		var spin = document.getElementById("loader_spin");
		var progress_ok = document.getElementById('progress_ok');
		progress_modal.style.display = "block";
		spin.style.display = "block";
		progress_ok.style.display = "none";
		user_message = "Uploading your data...";
		document.getElementById('myModalBody').innerHTML = "<p>" + user_message + "</p>";
		sendJsonToDb(fileData);

	}//end if
}

var parseCsv = new uploadDealcsv();
parseCsv.getCsv();

//var csv is the CSV file with headers
function csvJSON(csv) {

	var lines = csv.split("\n");
	var result = [];
	var headers = lines[0].split(",");
	var h_check = checkHeadersIfValid(headers);

	if (!h_check.isValid) {
		alert(h_check.msg);
		return;
	}
	headers = h_check.headers;

	for (var i = 1; i < lines.length; i++) {

		var obj = {};
		var line = lines[i].trim();
		line = line.replace(";", ",");
		line = line.replace("\\t", ",");

		var currentline = CSVToArray2(line);//line.split(",");

		if (currentline.length == 1 && currentline[0] == "") {
			continue;
		}

		if (currentline.length != headers.length) {
			alert("There is an error in the number of fields in row number " + (i + 1));
			return;
		}

		//REALLY I DONT UNDERSTAND WHY REPLACE 
		for (var j = 0; j < headers.length; j++) {
			if (requiredHeaders.indexOf(headers[j]) > -1) {
				obj[headers[j]] = currentline[j].trim();//.replace('"','');
			}
		}

		var objString = JSON.stringify(obj).replace("\\r", "");
		obj = JSON.parse(objString);

		//console.log(obj);
		result.push(obj);

	}
	//console.log(result);

	return result; //JavaScript object
}

//-----------------------UPload the temp-devices-----------------------------//


/*************Table related ****************/

function updateDeviceTimeout() {
	$("#editDeviceOkModal").modal('hide');
	setTimeout(function () {
		location.reload();
	}, 1000);
}
//---------------build the table-----------------------------//

function format(d) {

	var multitenancy = "";
	if (d.service && d.servicePath) {
		multitenancy = '<div class="row">' +
			'<div id="service" class="col-xs-6 col-sm-6" style="background-color:#B3D9FF;"><b>Service/Tenant:</b>' + "  " + d.service + '</div>' +
			'<div id="service" class="clearfix visible-xs"></div>' +
			'<div class="col-xs-6 col-sm-6" style="background-color:#B3D9FF;"><b>ServicePath:</b>' + "  " + d.servicePath + '</div>' +
			'</div>';
	}

	return '<div class="container-fluid">' +

		'<div class="row">' +
		'<div class="col-xs-6 col-sm-6 stripeSubtableLight"><b>Kind:</b>' + "  " + d.kind + '</div>' +
		'<div class="clearfix visible-xs"></div>' +
		'<div class="col-xs-6 col-sm-6 stripeSubtableLight"><b>Visibility:</b>' + "  " + d.visibility + '</div>' +
		'</div>' +
		'<div class="row">' +
		'<div class="col-xs-6 col-sm-6 stripeSubtableDark"><b>Device Type:</b>' + "  " + d.devicetype + '</div>' +
		'<div class="clearfix visible-xs"></div>' +
		'<div class="col-xs-6 col-sm-6 stripeSubtableDark"><b>Format:</b>' + "  " + d.format + '</div>' +
		'</div>' +
		'<div class="row">' +
		'<div class="col-xs-6 col-sm-6 stripeSubtableLight"><b>Protocol:</b>' + "  " + d.protocol + '</div>' +
		'<div class="clearfix visible-xs"></div>' +
		'<div class="col-xs-6 col-sm-6 stripeSubtableLight"><b>MAC:</b>' + "  " + d.macaddress + '</div>' +
		'</div>' +
		'<div class="row">' +
		'<div class="col-xs-6 col-sm-6 stripeSubtableDark"><b>Model:</b>' + "  " + d.model + '</div>' +
		'<div class="clearfix visible-xs"></div>' +
		'<div class="col-xs-6 col-sm-6 stripeSubtableDark"><b>Producer:</b>' + "  " + d.producer + '</div>' +
		'</div>' +
		'<div class="row">' +
		'<div class="col-xs-6 col-sm-6 stripeSubtableLight"><b>Longitude:</b>' + "  " + d.longitude + '</div>' +
		'<div class="clearfix visible-xs"></div>' +
		'<div class="col-xs-6 col-sm-6 stripeSubtableLight"><b>Latitude:</b>' + "  " + d.latitude + '</div>' +
		'</div>' +
		'<div class="row">' +
		'<div class="col-xs-6 col-sm-6 stripeSubtableDark"><b>Gateway/Edge Type:</b>' + "  " + d.edge_gateway_type + '</div>' +
		'<div class="clearfix visible-xs"></div>' +
		'<div class="col-xs-6 col-sm-6 stripeSubtableDark"><b>Gateway/Edge Uri:</b>' + "  " + d.edge_gateway_uri + '</div>' +
		'</div>' +
		'<div class="row">' +
		'<div class="col-xs-6 col-sm-6 stripeSubtableLight"><b>K1:</b>' + "  " + d.k1 + '</div>' +
		'<div class="clearfix visible-xs"></div>' +
		'<div class="col-xs-6 col-sm-6 stripeSubtableLight"><b>K2:</b>' + "  " + d.k2 + '</div>' +
		'</div>' + multitenancy +
		'</div>';
}


function fetch_data(destroyOld, selected = null) {
	if (destroyOld) {
		$('#devicesTable').DataTable().destroy();
		tableFirstLoad = true;
	}

	if (selected == null) {
		mydata = { action: "get_temporary_devices", should_be_registered: "no", token: sessionToken, no_columns: ["position", "edit", "delete", "map"] };
	}

	dataTable = $('#devicesTable').DataTable({
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
				"class": "details-control",
				"name": "position",
				"orderable": false,
				"data": null,
				"defaultContent": "",
				"render": function () {
					return '<i class="fa-solid fa-circle-plus" aria-hidden="true"></i>';
				},
				width: "15px"
			},
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
				"name": "status", "data": function (row, type, val, meta) {
					// var myattributes = []
					// for (event_value of list_temporary_event_value) {
					// 	if (row.contextbroker == event_value.contextbroker && row.name == event_value.device) myattributes.push(event_value);
					// }
					// var toVerify = { "contextbroker": row.contextbroker, "name": row.name, "devicetype": row.devicetype, "model": row.model, "macaddress": row.macaddress, "frequency": row.frequency, "kind": row.kind, "protocol": row.protocol, "format": row.format, "service": row.service, "servicepath": row.servicePath, "latitude": row.latitude, "longitude": row.longitude, "visibility": row.visibility, "k1": row.k1, "k2": row.k2, "subnature": row.subnature, "static_attributes": row.static_attributes, "producer": row.producer, "edge_gateway_type": row.edge_gateway_type, "edge_gateway_uri": row.edge_gateway_uri, "deviceValues": myattributes };
					// var status = verifyDevice(toVerify);
					if (row.status == 'invalid') {
						return '<button type="button" id="invalid" class="btn btn-warning" onclick="showValidityMsg(\'' + row.status + '\',\'' + row.validity_msg + '\')\">Invalid</button>';
					}
					else if (row.status == 'valid') {
						return '<button type="button" id="valid"  class="btn btn-success" onclick="showValidityMsg(\'' + row.status + '\',\'' + row.validity_msg + '\')\">Valid</button>';
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
						'data-subnature="' + d.subnature + '" ' +
						'data-static-attributes="' + btoa(d.static_attributes) + '" ' +
						'data-service="' + d.service + '" ' +
						'data-servicePath="' + d.servicePath + '" ' +
						'data-status="' + d.status + '">Edit</button>';
				}
			},
			{
				data: null,
				"name": "delete",
				"orderable": false,
				className: "center",
				//defaultContent: '<button type="button" id="delete" class="delDashBtn delete">Delete</button>'
				render: function (d) {
					return '<button type="button" class="delDashBtn" ' +
						'data-id="' + d.name + '" ' +
						'data-contextBroker="' + d.contextbroker + '" ' +
						'data-status="' + d.status + '" ' +
						'data-uri="' + d.uri + '">Delete</button>';
				}
			},
			{
				data: null,
				"name": "map",
				"orderable": false,
				className: "center",
				//defaultContent: '<button type="button" id="map" class="delDashBtn delete">Location</button>'
				render: function (d) {
					return '<div class="addMapBtn"><i  data-toggle="modal" data-target="#addMapShow" onclick="drawMap(\'' + d.latitude + '\',\'' + d.longitude + '\', \'' + d.id + '\', \'' + d.devicetype + '\', \'' + d.kind + '\', \'' + 'addDeviceMapModalBodyShow' + '\')\" class="fa-solid fa-map-location-dot viewOnMap"></i></div>';
				}
			}
		],
		"order": []
	});
}
function buildPreview(attributesIf, destroyOld, selected = null) {
	if (destroyOld) {
		$('#devicePreviewTable').DataTable().destroy();
	}

	if (selected == null) {
		mydata = { action: "get_affected_devices", token: sessionToken, attributes: attributesIf, no_columns: [""] };
	}

	dataPreviewTable = $('#devicePreviewTable').DataTable({
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
		$('#valuesPreviewTable').DataTable().destroy();
	}

	if (selected == null) {
		mydata = { action: "get_affected_values", token: sessionToken, attributes: attributesIf, no_columns: [""] };
	}

	dataPreviewTable = $('#valuesPreviewTable').DataTable({
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

$(document).ready(function () {
	checkBulkStatus();
	//fetch_data function will load the device table 	
	fetch_data(false);

	var detailRows = [];

	$('#uploadbutton').click(function () {

		if (myFile != null) {
			var reader = new FileReader();
			reader.addEventListener('load', function (e) {
	
				let csvdata = e.target.result;
				parseCsv.getParsecsvdata(csvdata); // calling function for parse csv data 
				//buildMainTable(true);
			});
			reader.readAsBinaryString(myFile);
		}
		else {
			alert("Select a file first");
		}
	});

	$('#devicesTable tbody').on('click', 'td.details-control', function () {
		var tr = $(this).closest('tr');
		var tdi = tr.find("i.fa");
		var row = dataTable.row(tr);

		if (row.child.isShown()) {
			// This row is already open - close it
			row.child.hide();
			tr.removeClass('shown');
			tdi.first().removeClass('fa-minus-square');
			tdi.first().addClass('fa-plus-square');
		}
		else {
			// Open this row
			row.child(format(row.data())).show();

			tr.addClass('shown');
			tdi.first().removeClass('fa-plus-square');
			tdi.first().addClass('fa-minus-square');
		}
	});

	$('#devicesTable tbody').on('click', 'button.delDashBtn', function () {

		var id = $(this).attr('data-id');
		var contextbroker = $(this).attr('data-contextbroker');
		var uri = $(this).attr("data-uri");
		var status = $(this).attr("data-status");

		$("#deleteDeviceModal div.modal-body").html('<div class="modalBodyInnerDiv"><span data-id = "' + id + '" data-contextbroker = "' + contextbroker + '" data-status = "' + status + '"  data-uri ="' + uri + '">Do you want to confirm deletion of device <b>' + id + '</b>?</span></div>');
		$("#deleteDeviceModal").modal('show');
	});

	$('#devicesTable tbody').on('hover', 'button.delDashBtn', function () {

		//console.log($(this));
		$(this).css('background', '#ffcc00');
		$(this).parents('tr').find('td').eq(1).css('background', '#ffcc00');
	},
		function () {
			$(this).css('background', '#e37777');
			$(this).parents('tr').find('td').eq(1).css('background', $(this).parents('td').css('background'));
		});

	// $('#devicesTable thead').css("background", "rgba(0, 162, 211, 1)");
	// $('#devicesTable thead').css("color", "white");
	// $('#devicesTable thead').css("font-size", "1em");

	$('#devicesTable tbody tr').each(function () {
		if ((dataTable.row(this).index()) % 2 !== 0) {
			//$('#devicesTable tbody').css("background", "rgba(0, 162, 211, 1)");
			//console.log( 'Row index: '+dataTable.row( this ).index() );
			$(this).find('td').eq(0).css("background-color", "rgb(230, 249, 255)");
			$(this).find('td').eq(0).css("border-top", "none");
		}
		else {
			$(this).find('td').eq(0).css("background-color", "white");
			$(this).find('td').eq(0).css("border-top", "none");
		}
	});

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
				}

				else (data["status"] === 'ok')
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

	// // $('#mainContentCnt').height($('#mainMenuCnt').height() - $('#headerTitleCnt').height());
	$('#mainContentCnt').height($(document).height());

	$(window).resize(function () {
		// // $('#mainContentCnt').height($('#mainMenuCnt').height() - $('#headerTitleCnt').height());
		$('#mainContentCnt').height($(document).height());
	});

	for (var func = 0; func < functionality.length; func++) {
		var element = functionality[func];
		if (element.view == "view") {
			if (element[loggedRole] == 1) {
				$(element["class"]).show();
			}
			else {
				$(element["class"]).hide();

			}
		}
	}

	$('#listDevicesLink .mainMenuItemCnt').addClass("mainMenuItemCntActive");
	$('#mobMainMenuPortraitCnt #listDevicesLink .mobMainMenuItemCnt').addClass("mainMenuItemCntActive");
	$('#mobMainMenuLandCnt #ListDevicesLink .mobMainMenuItemCnt').addClass("mainMenuItemCntActive");

	//----INSERT VALID DEVICES ---//	
	$("#insertValidBtn").off("click");
	$('#insertValidBtn').click(function () {
		insertValidDevices();
	});

	$('#nodeJsTest').click(function () {
		nodeJsTest();
	});

	$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
		var target = $(e.target).attr("href");
		if ((target == '#addGeoPositionTabDevice')) {
			//console.log("Elf: Add Device Map");
			var latitude = 43.78;
			var longitude = 11.23;
			var flag = 0;
			drawMap1(latitude, longitude, flag);
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

			if (id == null || id == "") { var idNote = ("\n id not specified"); } else { idNote = "&#10004;"; }
			if (contextbroker == null || contextbroker == "") { var contextbrokerNote = ("cb not specified"); } else { contextbrokerNote = "&#10004;"; }
			if (type == null || type == "") { var typeNote = ("type not specified"); } else { typeNote = "&#10004;"; }
			if (!(kind == "sensor" || kind == "actuator")) { var kindNote = ("\n kind not specified"); } else { kindNote = "&#10004;"; }
			if ((latitude < -90 && latitude > 90) || (latitude == "" || latitude == null)) { var latitudeNote = ("\n latitude not correct "); } else { latitudeNote = "&#10004;"; }
			if ((longitude < -180 && longitude > 180) || (longitude == "" || longitude == null)) { var longitudeNote = ("\n longitude not correct "); } else { longitudeNote = "&#10004;"; }
			if (!(protocol == "ngsi" || protocol == "mqtt" || protocol == "amqp" || protocol == "ngsi w/MultiService")) { var protocolNote = ("protocol not correct "); } else { protocolNote = "&#10004;"; }

			if ((idNote == "&#10004;") && (contextbrokerNote == "&#10004;") && (typeNote == "&#10004;") && (kindNote == "&#10004;") && (latitudeNote == "&#10004;") && (longitudeNote == "&#10004;") && (protocolNote == "&#10004;")) { var statusNote = "<button class=\"btn btn-success btn-round\"></button>"; } else { statusNote = "<button class=\"btn btn-danger btn-round\"></button>"; }

			var x = inputPropertiesDeviceMMsg.innerHTML;

			var div = document.createElement("div");

			if (x == "&nbsp;") {
			}
			else {
				inputPropertiesDeviceMMsg.innerHTML = "";
			}

			div.innerHTML = ("<div>" +
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
	
	//INSERT BULK DEVICES 

	// add lines related to attributes			
	$("#addAttrBtn").off("click");
	$("#addAttrBtn").click(function () {
		//console.log("#addAttrBtn");							   
		content = drawAttributeMenu("", "", "", "", "", "", " ", " ", 'addlistAttributes', indexValues);
		indexValues = indexValues + 1;
		$('#addlistAttributes').append(content);

		checkAtlistOneAttribute();
		$("#addSchemaTabDevice #addlistAttributes .row input:even").each(function () { checkValueNameM($(this)); });
		checkEditDeviceConditions();

	});

	$("#addSchemaTabDevice").off("click");
	$("#addSchemaTabDevice").on('click keyup', function () {
		//console.log("#addSchemaTabDevice");

		//checkAtlistOneAttribute();
		$("#addSchemaTabDevice #addlistAttributes .row input:even").each(function () { checkValueNameM($(this)); });
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
		var status = $(this).attr("data-status");

		$("#deleteDeviceModal div.modal-body").html('<div class="modalBodyInnerDiv"><span data-id = "' + id + '" data-contextBroker = "' + contextBroker + '"  data-uri ="' + uri + '" data-status = "' + status + '">Do you want to confirm deletion of device <b>' + id + '</b>?</span></div>');
		$("#deleteDeviceModal").modal('show');
	});

	$('#deleteDeviceConfirmBtn').off("click");
	$("#deleteDeviceConfirmBtn").click(function () {

		var id = $("#deleteDeviceModal span").attr("data-id");
		var contextbroker = $("#deleteDeviceModal span").attr("data-contextbroker");
		var uri = $("#deleteDeviceModal span").attr("data-uri");
		var status = $("#deleteDeviceModal span").attr("data-status");
		//console.log("valori val "+id +" "+contextbroker + " " + status);

		$("#deleteDeviceModal div.modal-body").html("");
		$("#deleteDeviceCancelBtn").hide();
		$("#deleteDeviceConfirmBtn").hide();
		$("#deleteDeviceModal div.modal-body").append('<div id="deleteDeviceModalInnerDiv1" class="modalBodyInnerDiv"><h5>Device deletion in progress, please wait</h5></div>');
		$("#deleteDeviceModal div.modal-body").append('<div id="deleteDeviceModalInnerDiv2" class="modalBodyInnerDiv"><i class="fa fa-circle-o-notch fa-spin" style="font-size:36px"></i></div>');

		/*********Sara start - delete from json *****/
		var toDelete = { id: id, uri: uri, contextBroker: contextbroker, status: status };
		//deleteJSONvalues(toDelete);
		//console.log("GOING TO DELETE ");
		//console.log("id "+ id);
		//console.log("status"+status);
		/****Sara end****/

		$.ajax({
			url: "../api/bulkDeviceLoad.php",
			data: {
				action: "delete_temporary",
				id: id,
				uri: uri,
				//Sara2510 start
				//username: loggedUser,
				//Sara2510 end
				contextbroker: contextbroker,
				token: sessionToken,
				//organization:organization,
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
				}
				else if (data["status"] === 'ok') {
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

	//END INSERT VALID
	//DELETE ALL -- Sara
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
				}
				else if (data["status"] === 'ok') {
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
				}
				else {
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
		content = drawAttributeMenu("", "", "", "", "", "", "300", " ", 'addlistAttributesM', indexValues);
		indexValues = indexValues + 1;
		editDeviceConditionsArray['addlistAttributesM'] = true;
		$('#addlistAttributesM').append(content);

		checkEditAtlistOneAttribute();
		$("#editSchemaTabDevice #addlistAttributesM .row input:even").each(function () { checkEditValueName($(this)); });
		$("#editSchemaTabDevice #editlistAttributes .row input:even").each(function () { checkEditValueName($(this)); });
		checkEditDeviceConditions();

	});

	$("#editSchemaTabDevice").off("click");
	$("#editSchemaTabDevice").on('click keyup', function () {
		//console.log("#editSchemaTabDevice");

		//checkEditAtlistOneAttribute();
		$("#editSchemaTabDevice #addlistAttributesM .row input:even").each(function () { checkEditValueName($(this)); });
		$("#editSchemaTabDevice #editlistAttributes .row input:even").each(function () { checkEditValueName($(this)); });
		checkEditDeviceConditions();
	});


	$('#devicesTable tbody').on('click', 'button.editDashBtn', function () {

		document.getElementById('editlistAttributes').innerHTML = "";
		document.getElementById('addlistAttributesM').innerHTML = "";
		document.getElementById('deletedAttributes').innerHTML = "";

		$("#editDeviceModalLabel").html("Edit device - " + $(this).attr("data-id"));
		$("#editDeviceModalTabs").show();
		$('#editDeviceModal div.modalCell').show();
		$("#editDeviceCancelBtn").show();
		$("#editDeviceConfirmBtn").show();
		$("#addAttrMBtn").show();
		$("#editDeviceModalBody").show();
		$('#editDeviceLoadingMsg').hide();
		$('#editDeviceLoadingIcon').hide();
		$('#editDeviceOkMsg').hide();
		$('#editDeviceOkIcon').hide();
		$('#editDeviceKoMsg').hide();
		$('#editDeviceKoIcon').hide();
		$('#editDeviceOkBtn').hide();

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
		$('#inputLatitudeDeviceM').val(latitude);
		$('#inputLongitudeDeviceM').val(longitude);
		$('#inputFrequencyDeviceM').val(frequency);
		$('#selectVisibilityDeviceM').val(visibility);

		$('#KeyOneDeviceUserM').val(key1);
		$('#KeyTwoDeviceUserM').val(key2);

		$('#selectSubnatureM').val(subnature);
		$('#selectSubnatureM').trigger('change');
		subnatureChanged(true, JSON.parse(atob($(this).attr("data-static-attributes"))));

		if ($("#isMobileTickM").is(":checked"))
			$("#positionMsgHintM").show();
		else
			$("#positionMsgHintM").hide();

		fillMultiTenancyFormSection($(this).attr('data-service'), $(this).attr('data-servicePath'), contextbroker, 'device');

		checkEditDeviceConditions();

		showEditDeviceModal();

		// $("#editUserPoolsTable tbody").empty();

		// indexValues = 0;
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
				organization:organization,
				contextbroker: $(this).attr("data-contextBroker"),
				token: sessionToken,
			},
			type: "POST",
			async: true,
			dataType: 'json',
			success: function (mydata){
				//console.log("contenent logg "+JSON.stringify(mydata['content']));

				var row = null;
				$("#editUserPoolsTable tbody").empty();
				myattributes=mydata['content'];            
				content="";
				k=0;

				while (k < myattributes.length)
				{
					// console.log(k); 
					content = drawAttributeMenu(myattributes[k].value_name, 
					myattributes[k].data_type, myattributes[k].value_type, myattributes[k].editable, myattributes[k].value_unit, myattributes[k].healthiness_criteria, 
					myattributes[k].healthiness_value, myattributes[k].value_name, 'editlistAttributes', indexValues);
					indexValues=indexValues+1;
					k++;
                    $('#editlistAttributes').append(content);
 
					checkEditDeviceConditions();
					$("#editSchemaTabDevice #editlistAttributes .row input:even").each(function(){checkEditValueName($(this));}); 
                    
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
	$("#editDeviceConfirmBtn").click(function () {

		mynewAttributes = [];
		num1 = document.getElementById('addlistAttributesM').childElementCount;

		for (var m = 0; m < num1; m++) {
			var newatt = {
				value_name: document.getElementById('addlistAttributesM').childNodes[m].childNodes[0].childNodes[0].childNodes[0].value.trim(),
				data_type: document.getElementById('addlistAttributesM').childNodes[m].childNodes[1].childNodes[0].childNodes[0].value.trim(),
				value_type: document.getElementById('addlistAttributesM').childNodes[m].childNodes[2].childNodes[0].childNodes[0].value.trim(),
				editable: document.getElementById('addlistAttributesM').childNodes[m].childNodes[4].childNodes[0].childNodes[0].value.trim(),
				value_unit: document.getElementById('addlistAttributesM').childNodes[m].childNodes[3].childNodes[0].childNodes[0].value.trim(),
				healthiness_criteria: document.getElementById('addlistAttributesM').childNodes[m].childNodes[5].childNodes[0].childNodes[0].value.trim(),
				healthiness_value: document.getElementById('addlistAttributesM').childNodes[m].childNodes[6].childNodes[0].childNodes[0].value.trim()
			}
			mynewAttributes.push(newatt);
		}

		myAttributes = [];
		num = document.getElementById('editlistAttributes').childElementCount;
		for (var j = 0; j < num; j++) {
			var selectOpt_value_type = document.getElementById('editlistAttributes').childNodes[j].childNodes[2].childNodes[0].childNodes[0].options;
			var selectIndex_value_type = document.getElementById('editlistAttributes').childNodes[j].childNodes[2].childNodes[0].childNodes[0].selectedIndex;

			var selectOpt_data_type = document.getElementById('editlistAttributes').childNodes[j].childNodes[1].childNodes[0].childNodes[0].options;
			var selectIndex_data_type = document.getElementById('editlistAttributes').childNodes[j].childNodes[1].childNodes[0].childNodes[0].selectedIndex;

			var selectOpt_value_unit = document.getElementById('editlistAttributes').childNodes[j].childNodes[3].childNodes[0].childNodes[0].options;
			var selectIndex_value_unit = document.getElementById('editlistAttributes').childNodes[j].childNodes[3].childNodes[0].childNodes[0].selectedIndex;

			var selectOpt_hc = document.getElementById('editlistAttributes').childNodes[j].childNodes[5].childNodes[0].childNodes[0].options;
			var selectIndex_hc = document.getElementById('editlistAttributes').childNodes[j].childNodes[5].childNodes[0].childNodes[0].selectedIndex;

			var selectOpt_edit = document.getElementById('editlistAttributes').childNodes[j].childNodes[4].childNodes[0].childNodes[0].options;
			var selectIndex_edit = document.getElementById('editlistAttributes').childNodes[j].childNodes[4].childNodes[0].childNodes[0].selectedIndex;

			try { var dt = selectOpt_data_type[selectIndex_data_type].value } catch (err) { var dt = "" };
			try { var vt = selectOpt_value_type[selectIndex_value_type].value } catch (err) { var vt = "" };
			try { var vu = selectOpt_value_unit[selectIndex_value_unit].value } catch (err) { var vu = "" };

			var att = {
				value_name: document.getElementById('editlistAttributes').childNodes[j].childNodes[0].childNodes[0].childNodes[0].value.trim(),
				data_type: dt,
				value_type: vt,
				editable: selectOpt_edit[selectIndex_edit].value,
				value_unit: vu,
				healthiness_criteria: selectOpt_hc[selectIndex_hc].value,
				healthiness_value: document.getElementById('editlistAttributes').childNodes[j].childNodes[6].childNodes[0].childNodes[0].value.trim(),
				//sara start				   
				old_value_name: getText(document.getElementById('editlistAttributes').childNodes[j].childNodes[7].childNodes[0].childNodes[0])
			};
			//console.log("edit attr----------------------"+getText(document.getElementById('editlistAttributes').childNodes[j].childNodes[7].childNodes[0].childNodes[0]));

			//sara end
			myAttributes.push(att);

		}

		mydeletedAttributes = [];
		numDel = document.getElementById('deletedAttributes').childElementCount;
		for (var j = 0; j < numDel; j++) {
			var selectOpt_value_type = document.getElementById('deletedAttributes').childNodes[j].childNodes[2].childNodes[0].childNodes[0].options;
			var selectIndex_value_type = document.getElementById('deletedAttributes').childNodes[j].childNodes[2].childNodes[0].childNodes[0].selectedIndex;

			var selectOpt_data_type = document.getElementById('deletedAttributes').childNodes[j].childNodes[1].childNodes[0].childNodes[0].options;
			var selectIndex_data_type = document.getElementById('deletedAttributes').childNodes[j].childNodes[1].childNodes[0].childNodes[0].selectedIndex;

			var selectOpt_value_unit = document.getElementById('deletedAttributes').childNodes[j].childNodes[3].childNodes[0].childNodes[0].options;
			var selectIndex_value_unit = document.getElementById('deletedAttributes').childNodes[j].childNodes[3].childNodes[0].childNodes[0].selectedIndex;

			var selectOpt_hc = document.getElementById('deletedAttributes').childNodes[j].childNodes[5].childNodes[0].childNodes[0].options;
			var selectIndex_hc = document.getElementById('deletedAttributes').childNodes[j].childNodes[5].childNodes[0].childNodes[0].selectedIndex;

			var selectOpt_edit = document.getElementById('deletedAttributes').childNodes[j].childNodes[4].childNodes[0].childNodes[0].options;
			var selectIndex_edit = document.getElementById('deletedAttributes').childNodes[j].childNodes[4].childNodes[0].childNodes[0].selectedIndex;

			var att = {
				value_name: document.getElementById('deletedAttributes').childNodes[j].childNodes[0].childNodes[0].childNodes[0].value.trim(),
				data_type: selectOpt_data_type[selectIndex_data_type].value,
				value_type: selectOpt_value_type[selectIndex_value_type].value,
				editable: selectOpt_edit[selectIndex_edit].value,
				value_unit: (selectOpt_value_unit[selectIndex_value_unit] == undefined) ? "" : selectOpt_value_unit[selectIndex_value_unit].value,
				healthiness_criteria: selectOpt_hc[selectIndex_hc].value,
				healthiness_value: document.getElementById('deletedAttributes').childNodes[j].childNodes[6].childNodes[0].childNodes[0].value.trim(),
				//sara start				   
				old_value_name: document.getElementById('deletedAttributes').childNodes[j].childNodes[7].childNodes[0].childNodes[0]
			};

			//console.log(document.getElementById('editlistAttributes').childNodes[j].childNodes[7].childNodes[0].childNodes[0]);	

			//sara end
			mydeletedAttributes.push(att);
		}
		document.getElementById('editlistAttributes').innerHTML = "";
		document.getElementById('addlistAttributesM').innerHTML = "";
		document.getElementById('deletedAttributes').innerHTML = "";

		$("#editDeviceModalTabs").hide();
		$('#editDeviceModal div.modalCell').hide();
		//$("#editDeviceModalFooter").hide();
		$("#editDeviceCancelBtn").hide();
		$("#editDeviceConfirmBtn").hide();
		$("#addAttrMBtn").hide();

		$("#editDeviceModalBody").hide();

		$('#editDeviceLoadingMsg').show();
		$('#editDeviceLoadingIcon').show();
		// console.log(JSON.stringify(deviceJson));




		var arrayAttributes = [];
		for (var i = 0; i < myAttributes.length; i++) {
			arrayAttributes.push(myAttributes[i]);
		}
		for (var i = 0; i < mynewAttributes.length; i++) {
			arrayAttributes.push(mynewAttributes[i]);
		}

		//console.log("arrayAttributes "+JSON.stringify(arrayAttributes));

		var service = $('#editSelectService').val();
		var servicePath = $('#editInputServicePathDevice').val();

		if ($('#selectProtocolDeviceM').val() === "ngsi w/MultiService") {
			// servicePath value pre-processing
			if (servicePath[0] !== "/" || servicePath === "") servicePath = "/" + servicePath;
			if (servicePath[servicePath.length - 1] === "/" && servicePath.length > 1) servicePath = servicePath.substr(0, servicePath.length - 1);
		}



		//UPDATE FUNCTION
		var updatedDevice = { "contextbroker": $('#selectContextBrokerM').val(), "name": $('#inputNameDeviceM').val(), "devicetype": $('#inputTypeDeviceM').val(), "model": $('#selectModelDeviceM').val(), "macaddress": $('#inputMacDeviceM').val(), "frequency": $('#inputFrequencyDeviceM').val(), "kind": $('#selectKindDeviceM').val(), "protocol": $('#selectProtocolDeviceM').val(), "format": $('#selectFormatDeviceM').val(), "latitude": $('#inputLatitudeDeviceM').val(), "longitude": $('#inputLongitudeDeviceM').val(), "visibility": $('#selectVisibilityDeviceM').val(), "k1": $('#KeyOneDeviceUserM').val(), "k2": $('#KeyTwoDeviceUserM').val(), "subnature": $('#selectSubnatureM').val(), "static_attributes": JSON.stringify(retrieveStaticAttributes("editlistStaticAttributes", false, "isMobileTickM")), "service": service, "service_path": servicePath, "producer": $('#inputProducerDeviceM').val(), "edge_gateway_type": $('#selectEdgeGatewayTypeM').val(), "edge_gateway_uri": $('#inputEdgeGatewayUriM').val(), "deviceValues": arrayAttributes };

		var device_status = 'invalid';
		var verify = verifyDevice(updatedDevice);
		//console.log("verify "+JSON.stringify(verify));
		if (verify.isvalid) {
			device_status = 'valid';
		}
		//console.log(device_status);
		//console.log(verify.message);
		//console.log("attributes "+JSON.stringify(myAttributes));

		//console.log("old_id"+gb_old_id);
		//console.log("old_cb"+gb_old_cb);

		var service = $('#editSelectService').val();
		var servicePath = $('#editInputServicePathDevice').val();

		if ($('#selectProtocolDeviceM').val() === "ngsi w/MultiService") {
			// servicePath value pre-processing
			if (servicePath[0] !== "/" || servicePath === "") servicePath = "/" + servicePath;
			if (servicePath[servicePath.length - 1] === "/" && servicePath.length > 1) servicePath = servicePath.substr(0, servicePath.length - 1);
		}

		$.ajax({
			url: "../api/bulkDeviceLoad.php",
			data: {
				action: "update",
				//username: loggedUser,
				//organization:organization,
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
				mac: updatedDevice.macaddress,
				model: updatedDevice.model,
				producer: updatedDevice.producer,
				latitude: updatedDevice.latitude,
				longitude: updatedDevice.longitude,
				visibility: updatedDevice.visibility,
				frequency: updatedDevice.frequency,
				k1: updatedDevice.k1, //MM2909 this value need to be acquired from the 
				k2: updatedDevice.k2,  //MM2909
				subnature: updatedDevice.subnature,
				static_attributes: updatedDevice.static_attributes,
				service: service,
				servicePath: servicePath,
				token: sessionToken
				/***********************Sara end*************/
			},
			type: "POST",
			async: true,
			success: function (data) {
				//console.log("Marco edit Data " + JSON.stringify(data));

				if (data["status"] === 'ko') {
					console.log("Error editing Device type");
					console.log(data);



					$('#editDeviceLoadingMsg').hide();
					$('#editDeviceLoadingIcon').hide();
					$('#editDeviceOkMsg').hide();
					$('#editDeviceOkIcon').hide();
					$('#editDeviceKoMsg').show();
					$('#editDeviceKoIcon').show();
					$('#editDeviceOkBtn').show();

					//$('#editDeviceModal').modal('hide');
					fetch_data(true);

				}
				else if (data["status"] === 'ok') {

					$('#editDeviceLoadingMsg').hide();
					$('#editDeviceLoadingIcon').hide();
					$('#editDeviceOkMsg').show();
					$('#editDeviceOkIcon').show();

					$("#editDeviceModalInnerDiv1").html('Device &nbsp; successfully Updated');
					$("#editDeviceModalInnerDiv2").html('<i class="fa fa-check" style="font-size:42px"></i>');

					$('#editDeviceLoadingMsg').hide();
					$('#editDeviceLoadingIcon').hide();
					$('#editDeviceOkMsg').show();
					$('#editDeviceOkIcon').show();
					$('#editDeviceKoMsg').hide();
					$('#editDeviceKoIcon').hide();
					$('#editDeviceOkBtn').show();

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


					fetch_data(true);


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
					setTimeout(updateDeviceTimeout, 100);
				} else { console.log(data); }

			},
			error: function (data) {
				console.log("Ko result: " + JSON.stringify(data));
				/*$("#editDeviceKoModalInnerDiv1").html(data["msg"]);
				$("#editDeviceKoModal").modal('show');
				// $("#editDeviceModalUpdating").hide();
				$("#editDeviceModalBody").show();
				$("#editDeviceModalFooter").show();*/

				$('#editDeviceLoadingMsg').hide();
				$('#editDeviceLoadingIcon').hide();
				$('#editDeviceOkMsg').hide();
				$('#editDeviceOkIcon').hide();
				$('#editDeviceKoMsg').show();
				$('#editDeviceKoIcon').show();
				$('#editDeviceOkBtn').show();

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

				//$('#editDeviceModal').hide();
				//setTimeout(updateDeviceTimeout, 3000);
				setTimeout(updateDeviceTimeout, 100);

			}
		});
	});



	$("#editDeviceCancelBtn").off("click");
	$("#editDeviceCancelBtn").on('click', function () {
		document.getElementById('editlistAttributes').innerHTML = "";
		document.getElementById('addlistAttributesM').innerHTML = "";
		document.getElementById('deletedAttributes').innerHTML = "";
	});

	$("#addNewDeviceCancelBtn").off("click");
	$("#addNewDeviceCancelBtn").on('click', function () {

		$('#inputNameDevice').val("");
		$('#inputTypeDevice').val("");
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
		$("#KeyOneDeviceUser").val("");
		$("#KeyTwoDeviceUser").val("");
		$("#KeyOneDeviceUserMsg").html("");
		$("#KeyTwoDeviceUserMsg").html("");
		$('#addDeviceModal').modal('hide');

		//.hide();
		location.reload();
		//  $('#addDeviceModalTabs').show();
		//  $('#addDeviceModal div.modalCell').show();
		//  $('#addDeviceModalFooter').show(); 
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


	// $('#devicesTable thead').css("background", "rgba(0, 162, 211, 1)");
	// $('#devicesTable thead').css("color", "white");
	// $('#devicesTable thead').css("font-size", "1em");


	// This is for - Testing Propose Start --should be removed for the production 
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

		checkUpdateButton();

	});

	/************ update all devices  */

	$('#addifBlockBtn').off("click");
	$('#addifBlockBtn').click(function () {

		if ($('#ifBlockTable tbody tr').length == 0) {
			var row = $('<tr class="ifrow"><td><h3><span class="label label-danger">If</span></h3></td><td class="fieldTd"><select class="fieldIf"><option value="empty">--Select an option--</option><option value="contextBroker">Contextbroker</option><option value="id">Device name</option><option value="deviceType">Device Type</option><option value="model">Model</option><option value="producer">Producer</option><option value="frequency">Frequency</option><option value="kind">Kind</option><option value="protocol">Protocol</option><option value="format">Format</option><option value="latitude">Latitude</option><option value="longitude">Longitude</option><option value="macaddress">Mac address</option><option value="k1">Key1</option><option value="k2">Key2</option></select></td><td class="fieldEqual"><select class="fieldSelectEqual"><option value="IsEqual">Is Equal</option><option value="IsNotEqual">Is Not Equal</option><option value="IsNull">Is NULL</option><option value="Contains">Contains</option></select></td><td class="fieldName"> </td><td class="minusDevice"><i class="fa fa-minus"></i></td></tr>');
		}
		else {
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
				}
				else {
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
		//	updateConditions();

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
				}
				else {
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

	$('#updateAllCancelBtn').off("click");
	$('#updateAllCancelBtn').click(function(){
		$('#ifBlockTable tbody').html("");
		$('#decisionBlockTable tbody').html("");

		$('#devicePreviewTable').DataTable().destroy();
		$('#devicePreviewTable tbody').html("");
		$("#devicesFound").html("0 devices founded");
		devicenamesArray['if'] = 0;
		devicenamesArray['then'] = 0;

		$('#updateMultipleDeviceModal').modal('hide');
	})
	$('#updateAllValuesCancelBtn').off("click");
	$('#updateAllValuesCancelBtn').click(function(){
		$('#ifBlockTableValue tbody').html("");
		$('#decisionBlockTableValue tbody').html("");

		$('#valuesPreviewTable').DataTable().destroy();
		$('#valuesPreviewTable tbody').html("");
		$("#valueFound").html("0 values founded");
		valueNamesArray['if'] = 0;
		valueNamesArray['then'] = 0;
		$('#updateMultipleDeviceModal1').modal('hide');
	})

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
		}
		else {
			$("#updateAllConfirmBtn").attr("disabled", true);
		}
	}


	$(document).on({
		change: function () {

			var rowIndex = $(this).parents('tr').index();
			console.log("row index" + rowIndex);
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

	/*	function updateConditions(){
	
			$('#ifBlockTableValue .fieldSelectEqual').on('input', getAffectedRowsValue);
			getAffectedRowsValue();
	
		}*/

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

			var newIf = { "field": fieldIf, "operator": operatorIf, "value": valueIf };
			//console.log("newIf "+ JSON.stringify(newIf));
			attributesIf.push(newIf);
		}

		var attributesThen = [];
		var num2 = document.getElementById('decisionBlockTable').tBodies[0].childElementCount;
		for (var m = 0; m < num2; m++) {
			var fieldsThen = document.getElementById('decisionBlockTable').tBodies[0].rows.item(m).cells.item(1).childNodes[0].value;
			if (fieldsThen != "empty") {
				var operatorThen = document.getElementById('decisionBlockTable').tBodies[0].rows.item(m).cells.item(2).childNodes[0].innerHTML;

				var newThen = { "field": fieldsThen, "operator": operatorThen };
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
				}
				else {
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


	function getFields(fieldIf, pos, id, value) {
		$.ajax({
			url: "../api/bulkDeviceUpdate.php",
			data: {
				action: "get_fields",
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

					}
					else {
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
					}
					else {
						document.getElementById(id).tBodies[0].childNodes[pos].childNodes[3].innerHTML = myDataP[0].fieldsHtml;
					}
				}
				else {

					if (id.localeCompare("decisionBlockTable") == 0 || id.localeCompare("decisionBlockTableValue") == 0) {
						document.getElementById(id).tBodies[0].rows.item(pos).cells.item(2).innerHTML = myDataP[0].fieldsHtml;
					}
					else {
						document.getElementById(id).tBodies[0].childNodes[pos].childNodes[3].innerHTML = myDataP[0].fieldsHtml;


						if (value == 1) {
							getAffectedRowsValue();
						}
						else {
							getAffectedRows();
						}
					}
				}

				/*if(myDataP[0].autocomplete != null){

					$( ".tags" ).autocomplete({
						source: myDataP[0].autocomplete
					  });
				}*/

			},
			error: function (myData) {
				console.log("error" + JSON.stringify(myData));
				if (id.localeCompare("decisionBlockTable") == 0) {
					document.getElementById(id).tBodies[0].rows.item(pos).cells.item(2).innerHTML = "<input type=\"text\" class=\"fieldNameThen\" value=\"Empty\">";
				}
				else {
					document.getElementById(id).tBodies[0].childNodes[pos].childNodes[3].innerHTML = "<input type=\"text\" class=\"fieldNameIf\" value=\"Empty\">";
				}

			}
		});//end of ajax get_affected
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

				var newIf = { "field": fieldIf, "operator": operatorIf, "value": valueIf };
				attributesIf.push(newIf);
			}
			var attributesThen = [];

			for (var m = 0; m < num2; m++) {
				var fieldsThen = document.getElementById('decisionBlockTable').tBodies[0].rows.item(m).cells.item(1).childNodes[0].value;
				var valueThen = document.getElementById('decisionBlockTable').tBodies[0].childNodes[m ].childNodes[2].childNodes[0].value;

				if (valueThen.localeCompare("Empty") == 0) {
					valueThen = "";
				}
				var newThen = { "field": fieldsThen, "valueThen": valueThen };
				//console.log("newThen "+ JSON.stringify(newThen));
				attributesThen.push(newThen);
			}


			$.ajax({
				url: "../api/bulkDeviceUpdate.php",
				data: {
					action: "update_all_devices",
					token: sessionToken,
					//username: loggedUser,
					//organization:organization,
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

					}
					else if (myData['status'] == 'ko') {
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

		$('#updateMultipleDeviceModal1').modal('show');
		$('#updateMultipleDeviceModal').modal('hide');

		checkUpdateButtonValue();

	});
        
        
        
        
        


	$('#addifBlockBtnValue').off("click");
	$('#addifBlockBtnValue').click(function () {

		if ($('#ifBlockTableValue tbody tr').length == 0) {
			var row = $('<tr id="ifHV' + idCounterIf + '" class="ifrow"><td><h3><span class="label label-danger">If</span></h3></td><td class="fieldTdValue"><select class="fieldIfValue"><option value="empty">--Select an option--</option><option value="cb">Contextbroker</option><option value="device">Device name</option><option value="value_name">Value Name</option><option value="data_type">Data type</option><option value="model">Model</option><option value="producer">Producer</option><option value="frequency">Frequency</option><option value="kind">Kind</option><option value="protocol">Protocol</option><option value="format">Format</option><option value="latitude">Latitude</option><option value="longitude">Longitude</option><option value="macaddress">Mac address</option><option value="k1">Key1</option><option value="k2">Key2</option><option value="value_type">Value type</option><option value="value_unit">Value unit</option><option value="editable">Editable</option><option value="healthiness_criteria">Healthiness criteria</option><option value="healthiness_value">Healthiness value</option></select></td><td class="fieldEqualValue"><select class="fieldSelectEqualValue"><option value="IsEqual">Is Equal</option><option value="IsNotEqual">Is Not Equal</option><option value="IsNull">Is NULL</option><option value="Contains">Contains</option></select></td><td class="fieldNameIfValue"><input type="hidden"></td><td><i class="fa fa-minus"></i></td></tr>');
		}
		else {
			var row = $('<tr id="ifHV' + idCounterIf + '" class="ifrow"><td><h3><span class="label label-danger">AND</span></h3></td><td class="fieldTdValue"><select class="fieldIfValue"><option value="empty">--Select an option--</option><option value="cb">Contextbroker</option><option value="device">Device name</option><option value="value_name">Value Name</option><option value="data_type">Data type</option><option value="model">Model</option><option value="producer">Producer</option><option value="frequency">Frequency</option><option value="kind">Kind</option><option value="protocol">Protocol</option><option value="format">Format</option><option value="latitude">Latitude</option><option value="longitude">Longitude</option><option value="macaddress">Mac address</option><option value="k1">Key1</option><option value="k2">Key2</option><option value="value_type">Value type</option><option value="value_unit">Value unit</option><option value="editable">Editable</option><option value="healthiness_criteria">Healthiness criteria</option><option value="healthiness_value">Healthiness value</option></select></td><td class="fieldEqualValue"><select class="fieldSelectEqualValue"><option value="IsEqual">Is Equal</option><option value="IsNotEqual">Is Not Equal</option><option value="IsNull">Is NULL</option><option value="Contains">Contains</option></select></td><td class="fieldNameIfValue"><input type="hidden"></td><td><i class="fa fa-minus"></i></td></tr>');
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
				}
				else {
					$(this).html(value);
				}
			}
		});

		ifPages[rowIndex] = null;
		$('#authorizedPagesJson').val(JSON.stringify(ifPages));

		row.find('i.fa-minus').off("click");
		row.find('i.fa-minus').click(function () {

			var rowIndex = $(this).parents('tr').index();
			var health = document.getElementById('ifBlockTableValue').tBodies[0].rows.item(rowIndex).cells.item(1).childNodes[0].value;

			if (health.localeCompare("healthiness_value") == 0) {
				var idRow = document.getElementById('ifBlockTableValue').tBodies[0].rows.item(rowIndex).id;

				idRow = '#ifBlockTableValue tr#' + idRow + "criteria";
				$(idRow).remove();
				valueNamesArray['if'] = valueNamesArray['if'] - 1;
			}
			$('#ifBlockTableValue tbody tr').eq(rowIndex).remove();
			valueNamesArray['if'] = valueNamesArray['if'] - 1;

			if (rowIndex == 0 && document.getElementById('ifBlockTableValue').tBodies[0].rows.item(0) != null) {
				document.getElementById('ifBlockTableValue').tBodies[0].rows.item(0).childNodes[0].childNodes[0].innerHTML = "<span class=\"label label-danger\">If</span>";
			}

			checkUpdateButtonValue();
			getAffectedRowsValue();

			ifPages.splice(rowIndex, 1);
			$('#authorizedPagesJson').val(JSON.stringify(ifPages));
		});

		row.find('a.toBeEdited').off("save");
		row.find('a.toBeEdited').on('save', function (e, params) {
			var rowIndex = $(this).parents('tr').index();
			ifPages[rowIndex] = params.newValue;
			$('#authorizedPagesJson').val(JSON.stringify(ifPages));
		});
		//	updateConditionsValue();

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
		//console.log("update value");

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
				}
				else {
					$(this).html(value);
				}
			}
		});

		ifPages[rowIndex] = null;
		$('#authorizedPagesJson').val(JSON.stringify(ifPages));

		row.find('i.fa-minus').off("click");
		row.find('i.fa-minus').click(function () {
			var rowIndex = $(this).parents('tr').index();
			var health = document.getElementById('decisionBlockTableValue').tBodies[0].rows.item(rowIndex).cells.item(1).childNodes[0].value;

			if (health.localeCompare("healthiness_value") == 0) {
				var idRow = document.getElementById('decisionBlockTableValue').tBodies[0].rows.item(rowIndex).id;

				idRow = '#decisionBlockTableValue tr#' + idRow + "criteria";
				$(idRow).remove();

				//$('#decisionBlockTableValue tbody tr').eq(toDelete).remove();
				devicenamesArray['then'] = devicenamesArray['then'] - 1;
			}
			$('#decisionBlockTableValue tbody tr').eq(rowIndex).remove();
			devicenamesArray['then'] = devicenamesArray['then'] - 1;
			checkUpdateButtonValue();

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
		}
		else {
			$("#updateAllValuesConfirmBtn").attr("disabled", true);
		}
	}

	function getAffectedRowsValue() {

		var num1 = document.getElementById('ifBlockTableValue').tBodies[0].childElementCount;
		var attributesIfValues = [];
		for (var m = 0; m < num1; m++) {
			//var attribute= document.getElementById('ifBlockTable').rows[m].cells[1].selectedIndex;

			var fieldIf = document.getElementById('ifBlockTableValue').tBodies[0].rows.item(m).cells.item(1).childNodes[0].value;
			var operatorIf = document.getElementById('ifBlockTableValue').tBodies[0].rows.item(m).cells.item(2).childNodes[0].value;
			var valueIf = document.getElementById('ifBlockTableValue').tBodies[0].childNodes[m].childNodes[3].childNodes[0].value;
			//params.newValue
			if (valueIf.localeCompare("Empty") == 0) {
				valueIf = "";
			}
			if (fieldIf == undefined || fieldIf == null || fieldIf == "") {
				fieldIf = "healthiness_criteria";
			}

			var newIf = { "field": fieldIf, "operator": operatorIf, "value": valueIf };
			attributesIfValues.push(newIf);
		}
		if (num1 != 0) {
			$.ajax({
				url: "../api/bulkDeviceUpdate.php",
				data: {
					action: "get_affected_values_count",
					token: sessionToken,
					//username: loggedUser,
					//organization:organization,
					attributesIf: JSON.stringify(attributesIfValues)
					//attributesThen: JSON.stringify(attributesThen)	    
				},
				dataType: 'json',
				type: "POST",
				async: true,
				success: function (myData) {
					//console.log("data success "+ myData['content']);
					$("#valueFound").html(myData['content'] + " values founded");
					//	document.getElementById('devicesFound').value = myData['content'] + " devices found";

					if (attributesIfValues.length == 0) {
						buildPreviewValues(JSON.stringify(attributesIfValues), true);
					}
					else {
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
		}
		else {
			$("#valueFound").html("0 values founded");

		}
	}

	$("#updateAllValuesConfirmBtn").off("click");
	$("#updateAllValuesConfirmBtn").on("click", function () {
		var num1 = document.getElementById('ifBlockTableValue').tBodies[0].childElementCount;
		var num2 = document.getElementById('decisionBlockTableValue').tBodies[0].childElementCount;

		if (num1 != 0 & num2 != 0) {
			var attributesIfValues = [];
			for (var m = 0; m < num1; m++) {
				//var attribute= document.getElementById('ifBlockTable').rows[m].cells[1].selectedIndex;

				var fieldIf = document.getElementById('ifBlockTableValue').tBodies[0].rows.item(m).cells.item(1).childNodes[0].value;
				var operatorIf = document.getElementById('ifBlockTableValue').tBodies[0].rows.item(m).cells.item(2).childNodes[0].value;
				var valueIf = document.getElementById('ifBlockTableValue').tBodies[0].childNodes[m].childNodes[3].childNodes[0].value;

				if (valueIf.localeCompare("Empty") == 0) {
					valueIf = "";
				}

				var newIf = { "field": fieldIf, "operator": operatorIf, "value": valueIf };
				attributesIfValues.push(newIf);
			}
			var attributesThenValues = [];

			for (var m = 0; m < num2; m++) {
				var fieldsThen = document.getElementById('decisionBlockTableValue').tBodies[0].rows.item(m).cells.item(1).childNodes[0].value;

				if (fieldsThen != "empty") {
					var valueThen = document.getElementById('decisionBlockTableValue').tBodies[0].childNodes[m].childNodes[2].childNodes[0].value;

					if (valueThen.localeCompare("Empty") == 0) {
						valueThen = "";
					}
					var newThen = { "field": fieldsThen, "valueThen": valueThen };
					//console.log("newThen "+ JSON.stringify(newThen));
					attributesThenValues.push(newThen);
				}
			}


			$.ajax({
				url: "../api/bulkDeviceUpdate.php",
				data: {
					action: "update_all_values",
					token: sessionToken,
					//username: loggedUser,
					//organization:organization,
					attributesIf: JSON.stringify(attributesIfValues),
					attributesThen: JSON.stringify(attributesThenValues)
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
					}
					else if (myData['status'] == 'ko') {
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
		//console.log("#attrNameDelbtn");	
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


});  // end of ready-state


function drawAttributeMenu
	(attrName, data_type, value_type, editable, value_unit, healthiness_criteria, value_refresh_rate, old_value_name, parent, indice) {

	if (attrName == "") {
		msg = "<div style=\"color:red;\" class=\"modalFieldMsgCnt\"></div>";
	}
	else {
		msg = "<div class=\"modalFieldMsgCnt\">&nbsp;</div>";
	}


	options = "";
	if (value_type == "") {
		options += "<option hidden disabled selected value=\"NOT VALID OPTION\"> -- select an option -- </option>";
		msg_value_type = "<div style=\"color:red;\" class=\"modalFieldMsgCnt\">Value type is mandatory</div>";
	}
	else
		msg_value_type = "<div class=\"ok_label\" class=\"modalFieldMsgCnt\">Ok</div>";

	for (var n = 0; n < gb_value_types.length; n++) {
		if (value_type == gb_value_types[n].value)
			options += "<option value=\"" + gb_value_types[n].value + "\" selected>" + gb_value_types[n].label + "</option>";
		else
			options += "<option value=\"" + gb_value_types[n].value + "\">" + gb_value_types[n].label + "</option>";
	}

	myunits = "";// <option value=\"none\"></option>";
	msg_value_unit = "<div class=\"ok_label\" class=\"modalFieldMsgCnt\">Ok</div>";
	//retrieve acceptable value unit, and select the selected if available
	validValueUnit = getValidValueUnit(value_type, value_unit);

	if (validValueUnit !== "") {
		if (!validValueUnit.includes('selected')) {
			myunits += "<option hidden disabled selected value=\"NOT VALID OPTION\"> -- select an option -- </option>";
			msg_value_unit = "<div class=\"not_ok_label\" class=\"modalFieldMsgCnt\">Value unit is mandatory</div>";
		}
		myunits += validValueUnit;
	}

	//---start sara---
	if (value_refresh_rate === undefined) {
		value_refresh_rate = "";
	}
	var refresh_rate = "", different_values = "", within_bounds = "", healthiness_empty = ""; //0910Fatima
	switch (healthiness_criteria) {
		case "refresh_rate": refresh_rate = "selected";
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
	}
	else if (editable == "0") {
		editable_false = "selected";
	}
	else {
		editable_empty = "selected";
	}
	//0910Fatima--block modification end

	//---end sara

	mydatatypes = "";
	if (data_type != "") labelcheck = data_type;
	else { //0910Fatima
		labelcheck = "";
		//mydatatypes += "<option value=' ' selected> </option>";
	}

	for (var n = 0; n < gb_datatypes.length; n++) {
		if (labelcheck == gb_datatypes[n])
			mydatatypes += "<option value=\"" + gb_datatypes[n] + "\" selected>" + gb_datatypes[n] + "</option>";
		else mydatatypes += "<option value=\"" + gb_datatypes[n] + "\">" + gb_datatypes[n] + "</option>";
	}
	//console.log(data_type + "," + value_type + "," + editable + "," + value_unit + "," + healthiness_criteria + "," + value_refresh_rate + "," + parent);
	return "<div class=\"row\"><div class=\"col-xs-6 col-md-3 modalCell\">" +
		"<div class=\"modalFieldCnt\"><input type=\"text\" class=\"modalInputTxt\"" +
		"name=\"" + attrName + "\"  value=\"" + attrName + "\">" +
		"</div><div class=\"modalFieldLabelCnt\">Value Name</div>" + msg + "</div>" +

		"<div class=\"col-xs-6 col-md-3 modalCell\"><div class=\"modalFieldCnt\">" +
		"<select class=\"modalInputTxt\" name=\"" + attrName + "-type" +
		"\">" + mydatatypes +
		"</select></div><div class=\"modalFieldLabelCnt\">Data Type</div></div>" +

		"<div class=\"col-xs-6 col-md-3 modalCell\"><div class=\"modalFieldCnt\">" +
		"<select class=\"modalInputTxt\"  id=\"value_type" + indice + "\" " +
		"onchange=valueTypeChanged(" + indice + ") " +
		"\">" + options +
		"</select>" +
		"</div><div class=\"modalFieldLabelCnt\">Value Type" +
		"</div>" + msg_value_type + "</div>" +

		"<div class=\"col-xs-6 col-md-3 modalCell\"><div class=\"modalFieldCnt\">" +
		"<select class=\"modalInputTxt\" id=\"value_unit" + indice + "\" " +
		"onchange=valueUnitChanged(" + indice + ") " +
		"\">" +
		myunits +
		"</select>" +
		"</div><div class=\"modalFieldLabelCnt\">Value Unit" +
		"</div>" + msg_value_unit + "</div>" +

		"<div class=\"col-xs-6 col-md-3 modalCell\"><div class=\"modalFieldCnt\">" +
		"<select class=\"modalInputTxt\" name=\"" + editable +
		"\">" +
		"<option value='0' " + editable_false + ">false</option>" +
		"<option value='1' " + editable_true + ">true</option> </select>" +
		"<option value='' " + editable_empty + "> </option> </select>" + //0910Fatima
		"</div><div class=\"modalFieldLabelCnt\">Editable</div></div>" +


		"<div class=\"col-xs-6 col-md-3 modalCell\"><div class=\"modalFieldCnt\">" +
		"<select class=\"modalInputTxt\" name=\"" + healthiness_criteria +
		"\" \>" +
		"<option value=\"refresh_rate\" " + refresh_rate + ">Refresh rate</option>" +
		"<option value=\"different_values\" " + different_values + ">Different Values</option>" +
		"<option value=\"within_bounds\" " + within_bounds + ">Within bounds</option>" +
		"<option value= ' '" + healthiness_empty + "> </option>" +

		"</select></div><div class=\"modalFieldLabelCnt\">healthiness criteria</div></div>" +

		"<div class=\"col-xs-6 col-md-3 modalCell\"><div class=\"modalFieldCnt\">" +
		"<input type=\"text\" class=\"modalInputTxt\" name=\"" + value_refresh_rate +
		"\" value=\"" + value_refresh_rate + "\"></div><div class=\"modalFieldLabelCnt\">healthiness value</div></div>" +
		"<select class=\"modalInputTxt\" style=\"display:none\" name=\"" + old_value_name +
		"\" \>" +
		"<option value=\"" + old_value_name + "\">" + old_value_name + "</option>" +
		"</select>" +

		"<div class=\"col-xs-6 col-md-3 modalCell\"><div class=\"modalFieldCnt\">" +
		//"<i class=\"fa fa-minus-square\" onclick=\"removeElementAt('" + parent + "',this); return true;\"  style=\"font-size:36px; color: #ffcc00\"></i></div></div></div>";
		"<button class=\"btn btn-danger\" onclick=\"removeElementAt('" + parent + "',this); return true;\">Remove Value</button></div></div></div>";

}

function removeElementAt(parent, child) {
	var list = document.getElementById(parent);
	// var content = child.parentElement.parentElement.parentElement.innerHTML
	// console.log("elemento cancellato " + document.getElementById('deletedAttributes').innerHTML);
	if (parent == "editlistAttributes") { document.getElementById('deletedAttributes').appendChild(child.parentElement.parentElement.parentElement); }
	else list.removeChild(child.parentElement.parentElement.parentElement);
	checkAtlistOneAttribute();
	checkEditAtlistOneAttribute();
}
function verifyDevice(deviceToverify) {
	var msg = "";
	var regexpMAC = /^([0-9A-F]{2}[:-]){5}([0-9A-F]{2})$/;
	var regex_devName = /[^a-z0-9:._-]/gi;
	var regex_valueName = /[^a-z0-9._-]/gi;

	var answer = { "isvalid": true, "message": "Your device is valid" };

	if (deviceToverify.name == undefined || deviceToverify.name.length < 5) { msg += "-name is mandatory, of 5 characters at least."; }
	if (regex_devName.test(deviceToverify.name)) { msg += "-name cannot contain special characters. "; }
	if (deviceToverify.devicetype == undefined || deviceToverify.devicetype == "" || deviceToverify.devicetype.indexOf(' ') >= 0) { msg += "-devicetype is mandatory."; }
	if (deviceToverify.macaddress != undefined && deviceToverify.macaddress != "" && !regexpMAC.test(deviceToverify.macaddress)) { msg += "-Mac format should be Letter (A-F) and number (eg. 3D:F2:C9:A6:B3:4F)."; }
	if (deviceToverify.frequency == undefined || deviceToverify.frequency == "" || !isFinite(deviceToverify.frequency)) { msg += "-frequency is mandatory, and should be numeric."; }
	if (deviceToverify.kind == undefined || deviceToverify.kind == "") { msg += "-kind is mandatory."; }
	if (deviceToverify.protocol == undefined || deviceToverify.protocol == "") { msg += "-protocol is mandatory."; }
	if (deviceToverify.format == undefined || deviceToverify.format == "") { msg += "-format is mandatory."; }
	if (deviceToverify.latitude == undefined || !isLatitude(deviceToverify.latitude)) { msg += "-Latitude is mandatory, with the correct numeric format."; }
	if (deviceToverify.longitude == undefined || !isLongitude(deviceToverify.longitude)) { msg += "-Longitude is mandatory, with the correct numeric format."; }
	//	if(deviceToverify.k1==undefined || deviceToverify.k1==""){msg+="-k1 is mandatory.";}
	//	if(deviceToverify.k2==undefined || deviceToverify.k2==""){msg+="-k2 is mandatory.";}

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
			//                                                                                        msg+="-The device property: static_attributes does not comply with its model." ;}}

			//the model doenot permit a null value for service and servicepath, so they are always configured... so cannot be used for validation
			//            //TODO we need model to set null to activate this functionalities
			//if ((modelsdata[i].service!==null)&&(modelsdata[i].service!=deviceToverify.service)){{ answer.isvalid=false;
			//	msg+="-The device property: service does not comply with its model." ;}}
			//if ((modelsdata[i].servicePath!==null)&&(modelsdata[i].servicePath!=deviceToverify.service_path)){{ answer.isvalid=false;
			//	msg+="-The device property: servicePath does not comply with its model." ;}}
		}
	}
	else {
		var all_attr_msg = "";
		var all_attr_status = "true";
		var healthiness_criteria_options = ["refresh_rate", "different_values", "within_bounds"];

		for (var i = 0; i < deviceToverify.deviceValues.length; i++) {
			var v = deviceToverify.deviceValues[i];

			//if(v==undefined){
			//	continue;
			//}
			var attr_status = true;
			var attr_msg = "";
			var empty_name = false;
			var strangeChar_name = false;

			if (v.value_name == undefined || v.value_name == "") {
				attr_status = false;
				empty_name = true;
			}
			else if (regex_valueName.test(v.value_name)) {
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

			if (v.value_type == undefined || v.value_type == "" || gb_value_types.indexOf(v.value_type).value < 0) {
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
				}
				else if (strangeChar_name) {
					all_attr_msg = "The attribute name " + v.value_name + " cannot contain strange characters. ";
					if (attr_msg != "") {
						all_attr_msg = all_attr_msg + ", other errors in: " + attr_msg;
					}
				}
				else {
					all_attr_msg = "For the attribute: " + v.value_name + ", error in: " + attr_msg;
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
	setTimeout(function () { map.invalidateSize() }, 400);
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


			setTimeout(function () { map1.invalidateSize() }, 400);

			//L.marker([latitude,longitude]).addTo(map).bindPopup(latitude + ',' + longitude);

			map1.on("click", function (e) {

				var lat = e.latlng.lat;
				var lng = e.latlng.lng;
				lat = lat.toFixed(4);
				lng = lng.toFixed(4);
				//console.log("Check the format:" + lat + " " + lng);

				document.getElementById('inputLatitudeDevice').value = lat;
				document.getElementById('inputLongitudeDevice').value = lng;
				addDeviceConditionsArray['inputLatitudeDevice'] = true;
				checkDeviceLatitude(); checkEditDeviceConditions();
				addDeviceConditionsArray['inputLongitudeDevice'] = true;
				checkDeviceLongitude(); checkEditDeviceConditions();
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

			setTimeout(function () { map1.invalidateSize() }, 400);

			marker = new L.marker([latitude, longitude]).addTo(map1).bindPopup(longitude + ',' + longitude);

			map1.on("click", function (e) {

				var lat = e.latlng.lat;
				var lng = e.latlng.lng;
				lat = lat.toFixed(4);
				lng = lng.toFixed(4);
				//console.log("Check the format:" + lat + " " + lng);

				document.getElementById('inputLatitudeDeviceM').value = lat;
				document.getElementById('inputLongitudeDeviceM').value = lng;
				editDeviceConditionsArray['inputLatitudeDeviceM'] = true;
				editDeviceConditionsArray['inputLongitudeDeviceM'] = true;
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


				//console.log("Success "+ JSON.stringify(content));
				//console.log(typeof(parseInt(content)));
				if (typeof (parseInt(content)) == "number") {
					insertValidDevices(parseInt(content));
				}
				else {
					document.getElementById('myModalBody').innerHTML = "couldn't find any device to insert";
					progress_ok.style.display = "block";
					spin.style.display = "none";
				}

			}
			else {
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
		kbUrl: kbUrl,
		start: 1,
		end: 6,
		token: sessionToken,
		should_be_registered: "no"

	};
	alert("Request sent");
	$.post('../api/bulkDeviceLoad.php', { 'data': test_data, 'data_from_nodeJs': 1 }, function (data) {
		//console.log("done");
		//console.log(data);
	});
}

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

	//../api/bulkDeviceLoad.php
	$.post('../api/async_request.php', { 'data': data }, function (response_data) {

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
			if (mydata["status"] == 'ok') {
				is_processing = 0;
				refresh();
			}
			else {
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

		//console.log("was processing" + was_processing);

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
		}
		else {

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
					//console.log("bulkstatus checked " + JSON.stringify(mydata));
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
						}
						else {
							is_processing = 0;
							is_finished = mydata["is_finished"];
						}
					}
					else {
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

	var answer = { "isValid": "", "msg": "", "headers": [] };
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
		if (h.trim().toLowerCase().indexOf("subnature") > -1) {
			found = found + 1;
			csvheaders[i] = "subnature";
			b.add("subnature");
			continue;
		}
		if (h.trim().toLowerCase().indexOf("static_attributes") > -1) {
			found = found + 1;
			csvheaders[i] = "static_attributes";
			b.add("static_attributes");
			continue;
		}
		if (h.trim().toLowerCase().indexOf("service") > -1 && h.trim().toLowerCase().indexOf("path") > -1) {//before next one 
			found = found + 1;
			csvheaders[i] = "service_path";
			b.add("service_path");
			continue;
		}
		if (h.trim().toLowerCase().indexOf("service") > -1) {
			found = found + 1;
			csvheaders[i] = "service";
			b.add("service");
			continue;
		}

	}

	var difference = new Set([...a].filter(x => !b.has(x)));

	//console.log("difference is ");
	//console.log(difference);

	if (difference.size == 0) {
		answer.isValid = true;
		answer.msg = "your file headers are valid";
		answer.headers = csvheaders;
		return answer;
	}
	else if (difference.size == requiredHeaders.length) {
		answer.isValid = false;
		answer.msg = "your file has none of the required fields.";
		return answer;
	}

	else {
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
	//console.log(msg);
	alert(msg);
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
