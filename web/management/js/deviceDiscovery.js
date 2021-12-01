var _serviceIP = "../stubs";

var cbs = [];
var foundDevices = [];
var preExistingDevices = [];
var numOfGetRequests = 0;
var numOfReturnedGetRequests = 0;
var cbsWithErrors = [];
var currentEditId = "";
var editDeviceConditionsArray = [];
var gb_datatypes = "";
var gb_value_units = "";
var gb_value_types = "";
var indexValues = 0;	//it keeps track of unique identirier on the values, so it's possible to enforce specific value type
var list_temporary_event_value = ""

function ajaxRequest() {
	var request = false;
	try {
		request = new XMLHttpRequest()
	} catch (e1) {
		try {
			request = new ActiveXObject("Msxml2.XMLHTTP")
		} catch (e2) {
			try {
				request = new ActiveXObject("Microsoft.XMLHTTP")
			}
			catch (e3) { request = false }
		}
	}
	return request
}

$.ajax({
	url: "../api/bulkDeviceUpdate.php",
	data: {
		action: "get_all_temporary_attributes",
		should_be_registered: "no",
		token: sessionToken,
	},
	datatype: 'json',
	type: "POST",
	async: true,
	success: function (data) {
		if (data["status"] === 'ok') {
			list_temporary_event_value = data.content;
			//console.log(list_temporary_event_value)
		}
		else {
			console.log("error getting temporary event values " + data);
		}
	},
	error: function (data) {
		console.log("error in the call to get  temporary event values " + data);
		alert("Network errors. <br/> Get in touch with the Snap4City Administrator<br/>" + JSON.stringify(data));
	}
});

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
		gb_datatypes = mydata["data_type"];
		gb_value_units = mydata["value_unit"];
		gb_value_types = mydata["value_type"];
		//console.log(mydata);
	},
	error: function (mydata) {
		console.log(JSON.stringify(mydata));
		alert("Network errors. <br/> Get in touch with the Snap4City Administrator<br/>" + JSON.stringify(mydata));
	}
});

function activateStub(protocol, cb, ip, port, accesslink, accessport, path, organization, login, password, tenant, servicePath, apikey) {
	if (servicePath != null && servicePath != undefined &&
		servicePath.localeCompare("null") != 0 && servicePath.localeCompare("") != 0 &&
		servicePath.length > 1 && servicePath.charAt(0) != "/") {
		servicePath = "/" + servicePath;
	}

	//console.log("STUB: " + protocol + ". CB: " + cb + ", IP: " + ip + ", port: " + port + ", acc. link: " + accesslink + ", acc. port: " + accessport + ", path: " + path + ", organization: " + organization + ", tenant: " + tenant + ", service path: " + servicePath);

	var data;
	if (apikey != null && apikey != undefined) {
		data = "contextbroker=" + cb + "&ip=" + ip + "&port=" + port + "&al=" + accesslink + "&ap=" + accessport + "&path=" + path + "&organization=" + organization + "&login=" + login + "&password=" + password + "&tenant=" + tenant + "&servicepath=" + servicePath + "&apikey=" + apikey;
	}
	else {
		data = "contextbroker=" + cb + "&ip=" + ip + "&port=" + port + "&al=" + accesslink + "&ap=" + accessport + "&path=" + path + "&organization=" + organization + "&login=" + login + "&password=" + password + "&tenant=" + tenant + "&servicepath=" + servicePath
	}
	var service = _serviceIP + "/api/" + protocol;

	var xhr = ajaxRequest();

	xhr.addEventListener("readystatechange", function () {
		//console.log("this.readyState "+this.readyState);
		if (this.readyState === 4 && this.status == 200) {
			let jsonResponse = JSON.parse(this.responseText).message;
			//console.log(jsonResponse);
			if (jsonResponse == "not reacheable\n") {
				numOfGetRequests--;
				if (!cbsWithErrors.includes(cb)) {
					cbsWithErrors.push(cb);
					console.error("Context Broker " + cb + " is not reachable.");
					$('#statusLabel').text("Context Broker " + cb + " is not reachable.");
				}
			} else if (jsonResponse == "path malformed\n") {
				numOfGetRequests--;
				if (!cbsWithErrors.includes(cb)) {
					cbsWithErrors.push(cb);
					console.error("Context Broker " + cb + ": path malformed.");
					$('#statusLabel').text("Context Broker " + cb + ": path malformed.");
				}
			} else if (jsonResponse == "not found\n") {
				numOfGetRequests--;
				if (!cbsWithErrors.includes(cb)) {
					cbsWithErrors.push(cb);
					console.error("Context Broker " + cb + " not found.");
					$('#statusLabel').text("Context Broker " + cb + " not found.");
				}
			} else {
				let devices = [];
				for (let i = 0; i < jsonResponse.length; i++) {
					let device = new Object();
					device.id = jsonResponse[i].id;
					device.type = jsonResponse[i].type;
					device.attributes = jsonResponse[i];
					// let device = jsonResponse[i];
					device.contextBroker = cb;
					device.service = tenant;
					device.servicePath = servicePath;
					//console.log(device)
					devices.push(device);
				}
				//console.log(devices);
				for (let i = 0; i < devices.length; i++) {
					foundDevices.push(devices[i]);
				}
				numOfReturnedGetRequests++;
			}
			if (numOfReturnedGetRequests == numOfGetRequests) {
				console.log("Got all devices from external CBs. There are " + foundDevices.length + " devices.");
				// now we can proceed in getting all pre-existing external devices on the IOT, so we can later compare the two sets
				getPreExistingExternalDevices();
			}
		}
	});



	xhr.open("POST", service);
	xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	xhr.setRequestHeader('Cache-Control', 'no-cache');
	xhr.setRequestHeader("Access-Control-Allow-Origin", "*");
	xhr.send(data);
}

function getPreExistingExternalDevices() {
	console.log("Getting devices from IOT Directory");
	// call api get_all_ext_devices_in_iot_dir
	$.ajax({
		url: "../api/deviceDiscoveryApi.php",
		data: {
			action: "get_all_ext_devices_in_iot_dir",
			token: sessionToken
			//			organization: organization,
			//			username: loggedUser,
			//			loggedrole: loggedRole
		},
		type: "POST",
		async: true,
		datatype: 'json',
		success: function (data) {
			var content = data["content"];
			// Build device list, put them in preExistingDevices
			for (let i = 0; i < content.length; i++) {
				// In the DB the id of the device is in the form "tenant.path.deviceName
				// but I am only interested in the name
				content[i].id = content[i].id.replace(content[i].service, "").replace(content[i].servicePath, "").replace("..", "");
				if (content[i].servicePath.length > 0 && content[i].servicePath.charAt(0) != "/") {
					content[i].servicePath = "/" + content[i].servicePath;
				}
			}
			//console.log(foundDevices);
			//console.log(content);

			for (let i = 0; i < content.length; i++) {
				preExistingDevices.push(content[i]);
			}

			//console.log("Got all devices from the IOT Directory. There are "+preExistingDevices.length+" devices.");
			//console.log(preExistingDevices);

			// And we can finally begin to build our CB/Tenant/Paths/Devices tree
			buildD3Tree();

		},
		error: function (data) {
			console.log("ERROR in get_all_ext_devices_in_iot_dir: " + JSON.stringify(data));
		}
	});
}

function buildD3Tree() {

	const data = getD3HierarchyFromData();

	const width = 1920;
	const dx = 30;
	const margin = new Object({ top: 48, right: 0, bottom: 48, left: 48 });
	const dy = (width / 8) - margin.left - margin.right;
	const tree = d3.tree().nodeSize([dx, dy]);
	const diagonal = d3.linkHorizontal().x(d => d.y).y(d => d.x);
	const root = d3.hierarchy(data);

	root.x0 = dy / 2;
	root.y0 = 0;
	root.descendants().forEach((d, i) => {
		d.id = i;
		d._children = d.children;
	});

	const svg = d3.select(document.getElementsByTagName("svg")[0]);
	svg.selectAll("*").remove();
	svg.attr("id", "chart")
		.attr("viewBox", [-margin.left, -margin.top, width, dx])
		.attr("font-family", '"Work Sans", "Raleway", "Helvetica Neue", Helvetica, sans-serif')
		.attr("font-size", 10)
		.style("user-select", "none");

	const gLink = svg.append("g")
		.attr("id", "tree-links")
		.attr("fill", "none")
		.attr("stroke", "#202630")
		.attr("stroke-opacity", 0.4)
		.attr("stroke-width", 1.5);

	const gNode = svg.append("g")
		.attr("id", "tree-nodes")
		.attr("pointer-events", "all");

	function update(source) {
		const duration = d3.event && d3.event.altKey ? 1000 : 200;
		const nodes = root.descendants().reverse();
		const links = root.links();

		// Compute the new tree layout
		tree(root);

		let left = root;
		let right = root;
		root.eachBefore(node => {
			if (node.x < left.x) left = node;
			if (node.x > right.x) right = node;
		});

		const height = right.x - left.x + margin.top + margin.bottom;

		const transition = svg.transition()
			.duration(duration)
			.attr("viewBox", [-margin.left, left.x - margin.top, width, height])
			.tween("resize", window.ResizeObserver ? null : () => () => svg.dispatch("toggle"));

		svg.selectAll("#source")
			.transition(transition)
			.attr("x", -margin.left)
			.attr("y", height + (left.x - margin.top))
			.attr("dy", "-1em");

		// Update the node data
		const node = gNode.selectAll("g")
			.data(nodes, d => d.id)
			.join(
				enter => { // Enter any new nodes at the parent's previous position.
					const nodeEnter = enter.append("g")
						.attr("class", "tree-nodes-dots")
						.attr("transform", d => `translate(${source.y0},${source.x0})`)
						.attr("fill-opacity", 0)
						.attr("stroke-opacity", 0)
						.on("click", d => {
							if (d.data.error == undefined || d.data.error == false) {
								//se è un path apri i children
								d.children = d.children ? null : d._children;
								//se è un device "verde" apre il dialog per l'edit
								if (d.data.preExisting != undefined && d.data.preExisting == false) {
									openEditDialog(d);
								}
								update(d);
							}
						});

					nodeEnter.append("circle")
						.attr("r", 6.5)
						.attr("stroke", d => d.depth <= 2 ? "lightgrey" : (d._children ? "lightgrey" : ((d.data.preExisting != undefined && d.data.preExisting == false) ? "white" : "blue")))
						.attr("stroke-width", 2)
						.attr("fill", d => (d.depth == 1 && d.data.error != undefined && d.data.error == true) ? "red" : (d.depth <= 2 ? "grey" : (d._children ? "grey" : ((d.data.preExisting != undefined && d.data.preExisting == false) ? "green" : "white"))))
						.attr("cursor", d => (d._children || (d.data.preExisting != undefined && d.data.preExisting == false)) ? "pointer" : "default");

					nodeEnter.append("text")
						.attr("fill", d => (d.depth == 1 && d.data.error != undefined && d.data.error == true) ? "red" : (d.depth <= 2 ? "black" : ((d.data.preExisting != undefined && d.data.preExisting == false) ? "white" : "black")))
						.attr("class", "tree-nodes-label")
						.attr("dy", "0.5em")
						.attr("x", d => (d.depth == 1 && d.data.error != undefined && d.data.error == true) ? -10 : (d._children ? -12 : 12))
						.attr("text-anchor", d => (d.depth == 1 && d.data.error != undefined && d.data.error == true) ? "end" : (d._children ? "end" : "start"))
						.attr("font-size", d => d.depth === 0 ? 20 : 16)
						.attr("cursor", d => (d._children || (d.data.preExisting != undefined && d.data.preExisting == false)) ? "pointer" : "default")
						.text(d => d.data.name)
						.clone(true).lower()
						.attr("aria-hidden", "true") // hide duplicate text from screen readers / assistive tech
						.style("user-select", "none")
						.attr("stroke", d => d.depth <= 2 ? "lightgrey" : (d._children ? "lightgrey" : ((d.data.preExisting != undefined && d.data.preExisting == false) ? "green" : "white")))
						.attr("stroke-linejoin", "round")
						.attr("stroke-width", 3);

					return nodeEnter;
				},
				update => update,
				exit => { // Transition exiting nodes to the parent's new position
					exit.transition(transition).remove()
						.attr("transform", d => `translate(${source.y},${source.x})`)
						.attr("fill-opacity", 0)
						.attr("stroke-opacity", 0);
					return exit;
				}
			);

		// Transition nodes to their new position
		node.transition(transition)
			.attr("transform", d => `translate(${d.y},${d.x})`)
			.attr("fill-opacity", 1)
			.attr("stroke-opacity", 1);

		// Update the links
		const link = gLink.selectAll("path")
			.data(links, d => d.target.id)
			.join(
				enter => { // Enter any new links at the parent's previous position
					const enterLink = enter.append("path")
						.attr("d", d => {
							const o = { x: source.x0, y: source.y0 };
							return diagonal({ source: o, target: o });
						});
					return enterLink;
				},
				update => update,
				exit => { // Transition exiting nodes to the parent's new position
					exit.transition(transition).remove()
						.attr("d", d => {
							const o = { x: source.x, y: source.y };
							return diagonal({ source: o, target: o });
						});
				}
			);

		// Transition links to their new position
		link.transition(transition)
			.attr("d", diagonal);

		// Stash the old positions for transition
		root.eachBefore(d => {
			d.x0 = d.x;
			d.y0 = d.y;
		});
	}

	update(root);

	$('#startDiscoveryButton').text("Start Discovery");
	$('#startDiscoveryButton').prop('disabled', false);
	$('#app').show();
	$('#statusLabel').hide();
	$('#colorsHint').show();
}

function getD3HierarchyFromData() {
	// D3 hierarchy is just a Json structure with parameters "name" and "children"
	var hierarchy = new Object({ name: "" });
	/*
	hierarchy.name = "Topolino";
	hierarchy.children = [];
	hierarchy.children.push({ name: "Qui"}, { name: "Quo"}, { name: "Qua"});
	 */
	let brokers = [];
	for (let i = 0; i < cbs.length; i++) {
		let cb = new Object({ name: cbs[i].name });
		if (cbsWithErrors.includes(cbs[i].name)) {
			cb.error = true;
		}
		else {
			let tenants = [];
			for (let j = 0; j < cbs[i].tenants.length; j++) {
				let tenant = new Object({ name: cbs[i].tenants[j].name != "" ? cbs[i].tenants[j].name : "DEFAULT TENANT" });
				let paths = [];

				let pathsToScan = cbs[i].tenants[j].servicePaths;

				// now we take a look at the paths:
				// we will have a list of paths like:
				// path1/path1_1/path1_1_1, path1/path1_1/path1_1_2, path1/path1_2, etc.
				// from which we have to build the same name/children structure as above,
				// so we will have only one path1 with only two children path1_1 and path1_2,
				// and path1_1 will have only two children path1_1_1 and path1_1_2.
				for (let k = 0; k < pathsToScan.length; k++) {
					let currentPath = pathsToScan[k].substring(1, pathsToScan[k].length);
					if (currentPath != "") {
						let pathSplit = currentPath.split("/");

						let tempPaths = paths;
						for (let w = 0; w < pathSplit.length; w++) {
							let pathNames = [];
							for (let x = 0; x < tempPaths.length; x++) {
								pathNames.push(tempPaths[x].name);
							}
							if (!pathNames.includes(pathSplit[w])) {
								let path = new Object({ name: pathSplit[w], children: [] });
								tempPaths.push(path);
								pathNames.push(path.name);
								tempPaths = path.children;
							} else {
								for (let x = 0; x < tempPaths.length; x++) {
									if (tempPaths[x].name == pathSplit[w]) {
										tempPaths = tempPaths[x].children;
									}
								}
							}
						}
					}
				}
				//console.log(cb.name+", "+tenant.name+": ");
				//console.log("Paths: "+paths);

				tenant.children = paths;
				tenants.push(tenant);
			}
			cb.children = tenants;
		}
		brokers.push(cb);
	}
	hierarchy.children = brokers;

	//console.log("JSON Hierarchy:");
	//console.log(hierarchy);

	// Now we just populated the tree with all CBs, tenants, and paths.
	// At this point we have to put found devices in the tree
	// and check which ones are already in the IOT Directory

	putDevicesInTree(hierarchy);

	// TODO it would be nice if the tree shows in grey devices that are in the
	// IOT Directory but not on the Context Broker.

	return hierarchy;
}

function putDevicesInTree(tree) {
	//console.log("Devices:")
	//console.log(foundDevices);
	//console.log(preExistingDevices);

	let ptr;
	for (let i = 0; i < foundDevices.length; i++) {
		for (let j = 0; j < tree.children.length; j++) {
			if (tree.children[j].name == foundDevices[i].contextBroker) {
				for (let k = 0; k < tree.children[j].children.length; k++) {
					if (tree.children[j].children[k].name == foundDevices[i].service || (tree.children[j].children[k].name == "DEFAULT TENANT" && foundDevices[i].service == "")) {
						ptr = tree.children[j].children[k].children;
						//entered in tenant
						let devicePath = foundDevices[i].servicePath;
						if (devicePath.length > 0 && devicePath.charAt(0) == "/") {
							devicePath = devicePath.substring(1, devicePath.length);
						}
						//console.log(devicePath);
						let devicePathSplit = devicePath.split("/");
						for (let w = 0; w < devicePathSplit.length; w++) {
							for (let x = 0; x < ptr.length; x++) {
								if (ptr[x].name == devicePathSplit[w]) {
									ptr = ptr[x].children;
								}
							}
						}
						// check if devices already exists in the IOT Directory
						let deviceToPut = new Object(
							{
								name: foundDevices[i].id,
								preExisting: false,
								contextBroker: foundDevices[i].contextBroker,
								latitude: foundDevices[i].latitude,
								longitude: foundDevices[i].longitude,
								service: foundDevices[i].service,
								servicePath: foundDevices[i].servicePath,
								type: foundDevices[i].type,
								attributes: foundDevices[i].attributes
							});
						for (let w = 0; w < preExistingDevices.length; w++) {
							if (preExistingDevices[w].contextBroker == foundDevices[i].contextBroker &&
								preExistingDevices[w].id == foundDevices[i].id &&
								preExistingDevices[w].service == foundDevices[i].service &&
								preExistingDevices[w].servicePath == foundDevices[i].servicePath &&
								preExistingDevices[w].type == foundDevices[i].type) {
								deviceToPut.preExisting = true;
								break;
							}
						}
						ptr.push(deviceToPut);
					}
				}
			}
		}
	}
}

function openEditDialog(deviceData) {
	document.getElementById('editlistAttributes').innerHTML = "";
	document.getElementById('addlistAttributesM').innerHTML = "";
	document.getElementById('deletedAttributes').innerHTML = "";
	$("#editDeviceModalBody").show();
	$('#editDeviceModalTabs').show();

	$("#editDeviceLoadingMsg").hide();
	$("#editDeviceLoadingIcon").hide();
	$("#editDeviceOkMsg").hide();
	$("#editDeviceOkIcon").hide();
	$("#editDeviceKoMsg").hide();
	$("#editDeviceKoIcon").hide();
	$("#editDeviceModalFooter").show();
	$("#editDeviceModalLabel").html("Adding device - " + deviceData.data.name);
	$("#editDeviceModal").modal('show');

	$("#inputModelDeviceM").val("custom");
	$("#inputModelDeviceM").prop("disabled", true);
	//console.log(deviceData);

	// fill dialog parameters with devices ones
	var id = deviceData.id;
	var contextBroker = deviceData.data.contextBroker;
	var attributes = deviceData.data.attributes;
	var deviceName = deviceData.data.name;

	if (currentEditId !== id) {

		currentEditId = id;
		editGenerateKeysCLicked();

		deviceData = deviceData.data;

		deviceName = deviceData.name;
		let type = deviceData.type;

		let latitude = deviceData.latitude;
		let longitude = deviceData.longitude;
		//console.log(latitude, longitude)
		if (latitude == undefined && longitude == undefined) {
			var latlong = getLatLong(deviceData.attributes);
			latitude = latlong[0]
			longitude = latlong[1]
			//console.log(latitude, longitude)
		}
		let service = deviceData.service;
		let servicePath = deviceData.servicePath;


		$('#inputNameDeviceM').prop("disabled", true); $('#inputNameDeviceM').val(deviceName);
		$('#deviceCB').prop("disabled", true); $('#deviceCB').val(contextBroker);
		$('#inputTypeDeviceM').val(type);
		$('#selectProtocolDeviceM').val("ngsi w/MultiService");
		$('#selectFormatDeviceM').val("json");
		if (latitude)
			$('#inputLatitudeDeviceM').val(latitude);
		else
			$('#inputLatitudeDeviceM').val("43")
		if (longitude)
			$('#inputLongitudeDeviceM').val(longitude);
		else
			$('#inputLongitudeDeviceM').val("11");
		$('#deviceService').prop("disabled", true); $('#deviceService').val(service);
		$('#editInputServicePathDevice').prop("disabled", true); $('#editInputServicePathDevice').val(servicePath);
	}
	checkEditDeviceConditions();
	//reperisci i values tramite le extraction rules prese tramite api
	indexValues = 0;
	var found = false;
	for (event_value of list_temporary_event_value) {
		var content = "";
		if (event_value.contextbroker == contextBroker && event_value.device == deviceName) {
			found = true
			content = drawAttributeMenu2(event_value.value_name,
				event_value.data_type, event_value.value_type, event_value.editable, event_value.value_unit, event_value.healthiness_criteria,
				event_value.healthiness_value, event_value.value_name, 'editlistAttributes', indexValues);
			indexValues = indexValues + 1;
			$('#editlistAttributes').append(content);
			checkEditDeviceConditions();
			$("#editSchemaTabDevice #editlistAttributes .row input:even").each(function () { checkEditValueName($(this)); });
		}
	}
	if (!found) {
		$.ajax({
			url: "../api/extractionRules.php",
			data: {
				action: 'get_rules',
				//			organization:organization,
				//			username:loggedUser,
				//			loggedrole: loggedRole,
				token: sessionToken,
			},
			type: "POST",
			async: true,
			dataType: 'json',
			success: function (mydata) {
				if (mydata["status"] === 'ko') {
					console.log(JSON.stringify(mydata));
					alert("Network errors while getting device extraction rules. Get in touch with the Snap4City Administrator." + JSON.stringify(mydata));
				} else if (mydata["status"] === 'ok') {
					//seleziona solo le rules inerenti al context broker interessato
					console.log(mydata);
					var rules = mydata["data"];
					if (rules.length > 0) {
						//console.log("device cb: "+contextBroker);
						for (let i = 0; i < rules.length; i++) {
							if (rules[i].contextbroker != contextBroker) {
								rules.splice(i, 1);
								--i;
							}
						}
						if (rules.length > 0) {
							//now we have all extraction rules belonging to current context broker.
							//so let's apply them all in the "Values" tab
							var content = "";
							for (let i = 0; i < rules.length; i++) {
								console.log(rules[i]);
								if (rules[i].kind == "value" && rules[i].service == deviceData.service && rules[i].servicepath == deviceData.servicePath) {
									let attributeName = rules[i].selector;

									attributeName = JSON.parse(attributeName)['param']['s']
									attributeName = attributeName.replace("$.", "");
									// console.log(attributeName);
									// console.log(attributes)
									if (Object.keys(attributes).includes(attributeName)) {

										content += drawAttributeMenu(attributeName, rules[i].data_type, rules[i].value_type, true,
											rules[i].value_unit, "refresh_rate", 300, attributeName,
											"editlistAttributes");
									}
								}
							}
							$('#editlistAttributes').html(content);
						} else {
							console.log("no extraction rules found for context broker '" + contextBroker + "'");
							alert("No extraction rules found for context broker '" + contextBroker + "'. Please add proper extraction rules or set device values manually.");
						}
					} else {
						console.log("no extraction rules found");
						alert("No extraction rules found. Please add proper extraction rules or set device values manually.");
					}
				}
			},
			error: function (mydata) {
				console.log(JSON.stringify(mydata));
				alert("Network errors while getting device extraction rules. Get in touch with the Snap4City Administrator" + JSON.stringify(mydata));
			}
		});
	}

	//$('#editDeviceModal').show();
	showEditDeviceModal()
}

function editGenerateKeysCLicked() {
	var k1 = generateUUID();
	var k2 = generateUUID();
	$("#KeyOneDeviceUserM").val(k1);
	$("#KeyTwoDeviceUserM").val(k2);
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

function getLatLong(schema) {
	var latitude = "";
	var longitude = "";
	if (schema.latitude != undefined && schema.longitude != undefined) {
		latitude = schema.latitude.value;
		longitude = schema.longitude.value;
	} else if (schema.location != undefined) {
		if (schema.location.type == "geo:json") {
			latitude = schema.location.value.coordinates[0]
			longitude = schema.location.value.coordinates[1]
		}

	}

	return [latitude, longitude]
}

function drawAttributeMenu
	(attrName, data_type, value_type, editable, value_unit, healthiness_criteria, value_refresh_rate, old_value_name, parent) {
	options = "";

	if (value_type != "" && value_type != undefined) labelcheck = value_type;
	else { //0910Fatima
		labelcheck = "";
		options += "<option value=' ' selected> </option>";
	}
	for (let n = 0; n < gb_value_types.length; n++) {
		if (labelcheck == gb_value_types[n].value)
			options += "<option value=\"" + gb_value_types[n].value + "\" selected>" + gb_value_types[n].label + "</option>";
		else options += "<option value=\"" + gb_value_types[n].value + "\">" + gb_value_types[n].label + "</option>";
	}

	myunits = "";// <option value=\"none\"></option>";
	if (value_unit != "" && value_unit != undefined && value_unit != null) {
		labelcheck = value_unit;
	} else {
		labelcheck = "";
		myunits += "<option value=' ' selected> </option>";
	}
	for (let n = 0; n < gb_value_units.length; n++) {
		if (labelcheck == gb_value_units[n].value)
			myunits += "<option value=\"" + gb_value_units[n].value + "\" selected>" + gb_value_units[n].label + "</option>";
		else myunits += "<option value=\"" + gb_value_units[n].value + "\">" + gb_value_units[n].label + "</option>";
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
	if (data_type != "" && data_type != undefined) {
		labelcheck = data_type;
	}
	else { //0910Fatima
		labelcheck = "";
		mydatatypes += "<option value=' ' selected> </option>";
	}

	for (let n = 0; n < gb_datatypes.length; n++) {
		if (labelcheck == gb_datatypes[n])
			mydatatypes += "<option value=\"" + gb_datatypes[n] + "\" selected>" + gb_datatypes[n] + "</option>";
		else mydatatypes += "<option value=\"" + gb_datatypes[n] + "\">" + gb_datatypes[n] + "</option>";
	}
	console.log(data_type + "," + value_type + "," + editable + "," + value_unit + "," + healthiness_criteria + "," + value_refresh_rate + "," + parent);
	return "<div class=\"row\" style=\"border:3px solid blue;\" ><div class=\"col-xs-6 col-md-3 modalCell\">" +
		"<div class=\"modalFieldCnt\"><input type=\"text\" class=\"modalInputTxt\"" +
		"name=\"" + attrName + "\"  value=\"" + attrName + "\">" +
		"</div><div class=\"modalFieldLabelCnt\">Value Name</div></div>" +

		"<div class=\"col-xs-6 col-md-3 modalCell\"><div class=\"modalFieldCnt\">" +
		"<select class=\"modalInputTxt\" name=\"" + attrName + "-type" +
		"\">" + mydatatypes +
		"</select></div><div class=\"modalFieldLabelCnt\">Data Type</div></div>" +

		"<div class=\"col-xs-6 col-md-3 modalCell\"><div class=\"modalFieldCnt\">" +
		"<select class=\"modalInputTxt\" name=\"" + value_type +
		"\">" + options +
		"</select></div><div class=\"modalFieldLabelCnt\">Value Type</div></div>" +

		"<div class=\"col-xs-6 col-md-3 modalCell\"><div class=\"modalFieldCnt\">" +
		"<select class=\"modalInputTxt\" name=\"" + editable +
		"\">" +
		"<option value='0' " + editable_false + ">false</option>" +
		"<option value='1' " + editable_true + ">true</option> </select>" +
		"<option value='' " + editable_empty + "> </option> </select>" + //0910Fatima
		"</div><div class=\"modalFieldLabelCnt\">Editable</div></div>" +

		"<div class=\"col-xs-6 col-md-3 modalCell\"><div class=\"modalFieldCnt\">" +
		"<select class=\"modalInputTxt\" name=\"" + value_unit +
		"\">" +
		myunits +
		"</select></div><div class=\"modalFieldLabelCnt\">Value Unit</div></div>" +

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
		//sara start
		"<div class=\"col-xs-6 col-md-3 modalCell\"><div class=\"modalFieldCnt\">" +
		"<input type=\"hidden\"  name=\"" + old_value_name +
		"\" value=\"" + old_value_name + "\"></div></div>" +
		//sara end
		"<div class=\"col-xs-6 col-md-3 modalCell\"><div class=\"modalFieldCnt\">" +
		//+"<i class=\"fa fa-minus-square\" onclick=\"removeElementAt('" + parent + "',this); return true;\"  style=\"font-size:36px; color: #ffcc00\"></i></div></div></div>";
		"<button class=\"btn btn-warning\" onclick=\"removeElementAt('" + parent + "',this);return true;\">Remove Value</button></div></div></div>";
	/*	+
   "<div class=\"newButton modalCell\"> <button class=\"btn btn-warning\" onclick=\"generateMissingValue('" + parent + "',this); return true;\">Predict Value</button></div></div></div>"
		;*/

}

function drawAttributeMenu2
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
		msg_value_type = "<div style=\"color:#337ab7;\" class=\"modalFieldMsgCnt\">Ok</div>";

	for (var n = 0; n < gb_value_types.length; n++) {
		if (value_type == gb_value_types[n].value)
			options += "<option value=\"" + gb_value_types[n].value + "\" selected>" + gb_value_types[n].label + "</option>";
		else
			options += "<option value=\"" + gb_value_types[n].value + "\">" + gb_value_types[n].label + "</option>";
	}

	myunits = "";// <option value=\"none\"></option>";
	msg_value_unit = "<div style=\"color:#337ab7;\" class=\"modalFieldMsgCnt\">Ok</div>";
	//retrieve acceptable value unit, and select the selected if available
	validValueUnit = getValidValueUnit(value_type, value_unit);

	if (validValueUnit !== "") {
		if (!validValueUnit.includes('selected')) {
			myunits += "<option hidden disabled selected value=\"NOT VALID OPTION\"> -- select an option -- </option>";
			msg_value_unit = "<div style=\"color:red;\" class=\"modalFieldMsgCnt\">Value unit is mandatory</div>";
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
	return "<div class=\"row\" style=\"border:2px solid blue;\" ><div class=\"col-xs-6 col-md-3 modalCell\">" +
		"<div class=\"modalFieldCnt\"><input type=\"text\" class=\"modalInputTxt\"" +
		"name=\"" + attrName + "\"  value=\"" + attrName + "\"disabled>" +
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

		"<div class=\"col-xs-6 col-md-3 modalCell delValueButton\"><div class=\"modalFieldCnt\">" +
		"<button class=\"btn btn-danger\" onclick=\"removeElementAt('" + parent + "',this); return true;\">Remove Value</button></div></div></div>";
}

function removeElementAt(parent, child) {
	var list = document.getElementById(parent);
	// var content = child.parentElement.parentElement.parentElement.innerHTML
	// console.log("elemento cancellato " + document.getElementById('deletedAttributes').innerHTML);
	if (parent == "editlistAttributes") { document.getElementById('deletedAttributes').appendChild(child.parentElement.parentElement.parentElement); }
	else list.removeChild(child.parentElement.parentElement.parentElement);
}

$('#editDeviceConfirmBtn').off("click");
$('#editDeviceConfirmBtn').click(function () {
	mynewAttributes = [];
	var regex = /[^a-z0-9:._-]/gi;
	var someNameisWrong = false;
	let num1 = document.getElementById('editlistAttributes').childElementCount;
	for (var m = 0; m < num1; m++) {
		var newatt = {
			value_name: document.getElementById('editlistAttributes').childNodes[m].childNodes[0].childNodes[0].childNodes[0].value.trim(),
			data_type: document.getElementById('editlistAttributes').childNodes[m].childNodes[1].childNodes[0].childNodes[0].value.trim(),
			value_type: document.getElementById('editlistAttributes').childNodes[m].childNodes[2].childNodes[0].childNodes[0].value.trim(),
			editable: document.getElementById('editlistAttributes').childNodes[m].childNodes[4].childNodes[0].childNodes[0].value.trim(),
			value_unit: document.getElementById('editlistAttributes').childNodes[m].childNodes[3].childNodes[0].childNodes[0].value.trim(),
			healthiness_criteria: document.getElementById('editlistAttributes').childNodes[m].childNodes[5].childNodes[0].childNodes[0].value.trim(),
			healthiness_value: document.getElementById('editlistAttributes').childNodes[m].childNodes[6].childNodes[0].childNodes[0].value.trim()
		};

		//console.log("new att:"+JSON.stringify(newatt));

		if (newatt.value_name != "" && !regex.test(newatt.value_name) && newatt.data_type != "" && newatt.value_type != "" && newatt.editable != "" && newatt.healthiness_criteria != "" && newatt.healthiness_value != "")
			mynewAttributes.push(newatt);
		else
			someNameisWrong = true;
	}

	if (mynewAttributes.length > 0 && !someNameisWrong) {

		document.getElementById('editlistAttributes').innerHTML = "";

		$("#editDeviceModalTabs").hide();
		$("#editDeviceModalBody").hide();
		$('#editDeviceModal div.modalCell').hide();
		$("#editDeviceModalFooter").hide();
		$("#editDeviceOkMsg").hide();
		$("#editDeviceOkIcon").hide();
		$("#editDeviceKoMsg").hide();
		$("#editDeviceKoIcon").hide();
		$('#editDeviceLoadingMsg').show();
		$('#editDeviceLoadingIcon').show();


		//console.log("LISTA" + JSON.stringify(mynewAttributes));
		var d = new Date();
		var t = d.getTime();
		//console.log("time before the insert request in milliseconds");
		//console.log(t);
		//console.log($('#inputLatitudeDevice'));
		//console.log($('#inputLatitudeDevice').val());
		//console.log($('#selectContextBroker'));

		var service = $('#deviceService').val();
		var servicePath = $('#editInputServicePathDevice').val();
		if (servicePath.charAt(0) == "/" && servicePath.length > 1) {
			servicePath = servicePath.substring(1, servicePath.length);
		}

		//console.log($('#selectProtocolDeviceM').val());
		if ($('#selectProtocolDeviceM').val() == "ngsi w/MultiService") {
			// servicePath value pre-processing
			if (servicePath[0] !== "/" || servicePath === "") servicePath = "/" + servicePath;
			if (servicePath[servicePath.length - 1] === "/" && servicePath.length > 1) servicePath = servicePath.substr(0, servicePath.length - 1);
		}

		//var id = service+"."+servicePath+"."+$.trim($('#inputNameDeviceM').val());
		var id = $.trim($('#inputNameDeviceM').val());
		var data = {
			action: "insert",
			attributes: JSON.stringify(mynewAttributes),
			id: id,
			type: $('#inputTypeDeviceM').val(),
			kind: $('#selectKindDeviceM').val(),
			contextbroker: $('#deviceCB').val(),
			protocol: $('#selectProtocolDeviceM').val(),
			format: $('#selectFormatDeviceM').val(),
			mac: $('#inputMacDeviceM').val(),
			model: $('#inputModelDeviceM').val(),
			producer: $('#inputProducerDeviceM').val(),
			latitude: $('#inputLatitudeDeviceM').val(),
			longitude: $('#inputLongitudeDeviceM').val(),
			visibility: $('#selectVisibilityDeviceM').val(),
			frequency: $('#inputFrequencyDeviceM').val(),
			token: sessionToken,
			k1: $("#KeyOneDeviceUserM").val(),
			k2: $("#KeyTwoDeviceUserM").val(),
			edgegateway_type: $("#selectEdgeGatewayTypeM").val(),
			edgegateway_uri: $("#inputEdgeGatewayUriM").val(),
			subnature: $('#selectSubnatureM').val(),
			static_attributes: JSON.stringify(retrieveStaticAttributes("addlistStaticAttributes", false)),
			service: service,
			servicePath: servicePath
		}
		console.log(data)

		$.ajax({
			url: "../api/device.php",
			data: {
				action: "insert",
				attributes: JSON.stringify(mynewAttributes),
				id: id,
				type: $('#inputTypeDeviceM').val(),
				kind: $('#selectKindDeviceM').val(),
				contextbroker: $('#deviceCB').val(),
				protocol: $('#selectProtocolDeviceM').val(),
				format: $('#selectFormatDeviceM').val(),
				mac: $('#inputMacDeviceM').val(),
				model: $('#inputModelDeviceM').val(),
				producer: $('#inputProducerDeviceM').val(),
				latitude: $('#inputLatitudeDeviceM').val(),
				longitude: $('#inputLongitudeDeviceM').val(),
				visibility: $('#selectVisibilityDeviceM').val(),
				frequency: $('#inputFrequencyDeviceM').val(),
				token: sessionToken,
				k1: $("#KeyOneDeviceUserM").val(),
				k2: $("#KeyTwoDeviceUserM").val(),
				edgegateway_type: $("#selectEdgeGatewayTypeM").val(),
				edgegateway_uri: $("#inputEdgeGatewayUriM").val(),
				subnature: $('#selectSubnatureM').val(),
				static_attributes: JSON.stringify(retrieveStaticAttributes("addlistStaticAttributes", false)),
				service: service,
				servicePath: servicePath
			},
			type: "POST",
			async: true,
			dataType: "JSON",
			//timeout: 0,
			success: function (mydata) {
				var d = new Date();
				var t = d.getTime();
				//console.log("time after a successful insert request in milliseconds");
				//console.log(t);
				//console.log(mydata["msg"]);
				if (mydata["status"] === 'ko') {
					alert("Error adding Device");
					console.log("Error adding Device");
					console.log(mydata);
					$('#editDeviceLoadingMsg').hide();
					$('#editDeviceLoadingIcon').hide();

					$("#editDeviceModal").modal('hide');

					$('#inputNameDeviceM').val("");
					$('#inputTypeDeviceM').val("");
					//$('#selectKindDevice').val(""),
					$('#deviceCB').val("NULL");
					$('#inputUriDeviceM').val("");
					//$('#selectProtocolDeviceM').val("NULL");
					//$('#selectFormatDevice').val("NULL");
					$('#inputMacDeviceM').val("");
					$('#inputModelDeviceM').val("");
					$('#inputProducerDeviceM').val("");
					$('#inputLatitudeDeviceM').val("");
					$('#inputLongitudeDeviceM').val("");
					$('#selectVisibilityDeviceM').val("NULL");
					$('#inputFrequencyDeviceM').val("600");
					$("#KeyOneDeviceUserM").val("");
					$("#KeyTwoDeviceUserM").val("");

					$('#selectSubnatureM').val("");
					$('#selectSubnatureM').trigger("change");
					$("#addNewStaticBtnM").hide();

					$("#editDeviceKoModal").modal('show');
					$("#editDeviceOkModal").hide();

				}
				else if (mydata["status"] === 'ok') {
					alert("Success adding Device");
					console.log("Success adding Device");
					//console.log(JSON.stringify(mydata));

					console.log(mydata);
					$('#editDeviceLoadingMsg').hide();
					$('#editDeviceLoadingIcon').hide();

					$("#editDeviceModal").modal('hide');

					$('#inputNameDeviceM').val("");
					$('#inputTypeDeviceM').val("");
					//$('#selectKindDevice').val(""),
					$('#deviceCB').val("NULL");
					$('#inputUriDeviceM').val("");
					//$('#selectProtocolDeviceM').val("NULL");
					//$('#selectFormatDevice').val("NULL");
					$('#inputMacDeviceM').val("");
					$('#inputModelDeviceM').val("");
					$('#inputProducerDeviceM').val("");
					$('#inputLatitudeDeviceM').val("");
					$('#inputLongitudeDeviceM').val("");
					$('#selectVisibilityDeviceM').val("NULL");
					$('#inputFrequencyDeviceM').val("600");
					$("#KeyOneDeviceUserM").val("");
					$("#KeyTwoDeviceUserM").val("");

					$('#selectSubnatureM').val("");
					$('#selectSubnatureM').trigger("change");
					$("#addNewStaticBtnM").hide();

					$("#editDeviceKoModal").hide();
					$("#editDeviceOkModalInnerDiv1").html('<h5>The device has been successfully registered. You can find further information on how to use and set up your device at the following page:</h5>' + "   " + '<h5>https://www.snap4city.org/drupal/node/76</h5>');
					$("#editDeviceOkModal").modal('show');

					$('#statusLabel').text("DEVICE " + id + " SUCCESFULLY ADDED.");

					setTimeout(() => {
						$("#editDeviceOkModal").modal('hide');
						$('#startDiscoveryButton').click();
					}, 2000);


				}

			},
			error: function (mydata) {
				alert("Error insert device");
				console.log("Error insert device");
				console.log("Error status -- Ko result: " + JSON.stringify(mydata));

				$('#editDeviceLoadingMsg').hide();
				$('#editDeviceLoadingIcon').hide();

				$("#editDeviceModal").modal('hide');

				$('#inputNameDeviceM').val("");
				$('#inputTypeDeviceM').val("");
				//$('#selectKindDevice').val(""),
				$('#deviceCB').val("NULL");
				$('#inputUriDeviceM').val("");
				//$('#selectProtocolDeviceM').val("NULL");
				//$('#selectFormatDevice').val("NULL");
				$('#inputMacDeviceM').val("");
				$('#inputModelDeviceM').val("");
				$('#inputProducerDeviceM').val("");
				$('#inputLatitudeDeviceM').val("");
				$('#inputLongitudeDeviceM').val("");
				$('#selectVisibilityDeviceM').val("NULL");
				$('#inputFrequencyDeviceM').val("600");
				$("#KeyOneDeviceUserM").val("");
				$("#KeyTwoDeviceUserM").val("");

				$('#selectSubnatureM').val("");
				$('#selectSubnatureM').trigger("change");
				$("#addNewStaticBtnM").hide();

				console.log("Error adding Device type");
				console.log(mydata);
				$("#editDeviceKoModal").modal('show');
				$("#editDeviceOkModal").hide();
			}
		});

	}
	else if (mynewAttributes.length == 0) {
		alert("Check the values tab, there must be at least one value");
	}
	else {
		alert("Check the values of your device, make sure that data you entered are valid!");
	}
});

$(document).ready(function () {
	$('#startDiscoveryButton').prop('disabled', false);
	$('#statusLabel').hide();
	$('#colorsHint').hide();

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

	$("#editSchemaTabDevice").off("click");
	$("#editSchemaTabDevice").on('click keyup', function () {
		console.log("#editSchemaTabDevice");

		//checkEditAtlistOneAttribute();
		$("#editSchemaTabDevice #addlistAttributesM .row input:even").each(function () { checkEditValueName($(this)); });
		$("#editSchemaTabDevice #editlistAttributes .row input:even").each(function () { checkEditValueName($(this)); });
		checkEditDeviceConditions();
	});

	$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
		var target = $(e.target).attr("href");
		if ((target == '#editGeoPositionTabDevice')) {
			//console.log("Elf : EditDeviceMap");
			var latitude = $("#inputLatitudeDeviceM").val();
			var longitude = $("#inputLongitudeDeviceM").val();
			var flag = 1;
			drawMap1(latitude, longitude, flag);
		} else {//nothing
		}
	});

	$('#startDiscoveryButton').off("click");
	$('#startDiscoveryButton').click(function () {
		$('#startDiscoveryButton').text("Discovering: Please wait...");
		$('#startDiscoveryButton').prop('disabled', true);
		console.log("Discovery started");
		$('#statusLabel').show();
		$('#app').hide();

		cbs = [];
		cbsWithErrors = []
		foundDevices = [];
		preExistingDevices = [];
		numOfGetRequests = 0;
		numOfReturnedGetRequests = 0;

		$.ajax({
			url: "../api/deviceDiscoveryApi.php",
			data: {
				action: "getCBServiceTrees",
				token: sessionToken
				//			organization: organization,
				//			username: loggedUser,
				//			loggedrole: loggedRole
			},
			type: "POST",
			async: true,
			datatype: 'json',
			success: function (data) {
				var content = data["content"];
				if (content.length > 0) {
					//Building JSON hierarchy
					let ips = [];
					let ports = [];
					let accesslinks = [];
					let accessports = [];
					let paths = [];
					let logins = [];
					let passwords = [];

					for (row of content) {
						if (!cbs.includes(row.contextBroker)) {
							cbs.push(row.contextBroker);
							ips.push(row.ip);
							ports.push(row.port);
							accesslinks.push(row.accesslink);
							accessports.push(row.accessport);
							paths.push(row.path);
							logins.push(row.login);
							passwords.push(row.password);
						}
					}
					for (let i = 0; i < cbs.length; i++) {
						let cbName = cbs[i];
						cbs[i] = new Object({
							name: cbName, ip: ips[i], port: ports[i], accesslink: accesslinks[i], accessport: accessports[i],
							path: paths[i], login: logins[i], password: passwords[i]
						});
						let tenants = [];
						for (row of content) {
							if (row.contextBroker == cbName) {
								if (!tenants.includes(row.service))
									tenants.push(row.service != null ? row.service : "");
							}
						}
						cbs[i].tenants = tenants;
					}

					for (let i = 0; i < cbs.length; i++) {
						for (let j = 0; j < cbs[i].tenants.length; j++) {
							let tenantName = cbs[i].tenants[j];
							cbs[i].tenants[j] = new Object({ name: tenantName });
							let servicePaths = [];

							for (let k = 0; k < content.length; k++) {
								let servicePath = content[k].servicePath;

								if (content[k].contextBroker == cbs[i].name && (content[k].service == tenantName ||
									(content[k].service == null && tenantName == ""))) {
									servicePaths.push(servicePath);
								}
							}
							var uniqueServicePath = []
							$.each(servicePaths, function (i, el) {
								if ($.inArray(el, uniqueServicePath) === -1) uniqueServicePath.push(el);
							});
							cbs[i].tenants[j].servicePaths = uniqueServicePath;
						}
					}

					console.log("Temporary JSON hierarchy:");
					console.log(cbs);

					// Now we have to check for implicit missing paths. What I mean is:
					// Let's assume that in a certain Tenant we have paths "path1" and "path1/path1_1/path1_1_1"
					// We want to scan for new devices also in "path1/path1_1"! The situation described above happens
					// if we don't have a Device, deleted device, or Device model, with the explicit path "path1/path1_1".

					for (let i = 0; i < cbs.length; i++) {
						for (let j = 0; j < cbs[i].tenants.length; j++) {
							for (let k = 0; k < cbs[i].tenants[j].servicePaths.length; k++) {
								let path = cbs[i].tenants[j].servicePaths[k];
								let slashes = (path.split("/")).length - 1;
								for (let w = 0; w < slashes; w++) {
									path = path.substring(0, path.lastIndexOf("/"));
									if (!cbs[i].tenants[j].servicePaths.includes(path)) {
										if ((path == "/" || path == "") && (!cbs[i].tenants[j].servicePaths.includes("") && !cbs[i].tenants[j].servicePaths.includes("/"))) {
											cbs[i].tenants[j].servicePaths.push(path);
											k = 0;
											console.log("Path inferred: " + path + " in CB: " + cbs[i].name + ", tenant: " + cbs[i].tenants[j].name);
										} else if (path != "" && path != "/") {
											cbs[i].tenants[j].servicePaths.push(path);
											k = 0;
											console.log("Path inferred: " + path + " in CB: " + cbs[i].name + ", tenant: " + cbs[i].tenants[j].name);
										}
									}
								}
							}
						}
					}

					console.log("Completed JSON hierarchy:");
					console.log(cbs);

					// Now we have to retrieve all devices in every path of every tenant of every contextBroker,
					// and this is done by calling activateStub on every path.
					for (let i = 0; i < cbs.length; i++) {
						for (let j = 0; j < cbs[i].tenants.length; j++) {
							for (let k = 0; k < cbs[i].tenants[j].servicePaths.length; k++) {
								numOfGetRequests++;
							}
						}
					}

					//console.log(numOfGetRequests);

					for (let i = 0; i < cbs.length; i++) {
						for (let j = 0; j < cbs[i].tenants.length; j++) {
							for (let k = 0; k < cbs[i].tenants[j].servicePaths.length; k++) {
								activateStub("discover", cbs[i].name, cbs[i].ip, cbs[i].port, cbs[i].accesslink, cbs[i].accessport,
									cbs[i].path, organization, cbs[i].login, cbs[i].password, cbs[i].tenants[j].name, cbs[i].tenants[j].servicePaths[k]);
							}
						}
					}

				} else {
					console.log("Warning - Empty response!");
					alert("No external Context Broker with ngsi Multitenancy support DETECTED");
					$('#startDiscoveryButton').text("Start Discovery");
					$('#startDiscoveryButton').prop('disabled', false);
				}
			},
			error: function (data) {
				console.log("ERROR in getCBServiceTrees: " + JSON.stringify(data));
				$('#startDiscoveryButton').text("Start Discovery");
				$('#startDiscoveryButton').prop('disabled', false);
			}
		});

	});

	$('#editDeviceConfirmBtn').off("click");
	$('#editDeviceConfirmBtn').click(function () {
		mynewAttributes = [];
		var regex = /[^a-z0-9:._-]/gi;
		var someNameisWrong = false;
		let num1 = document.getElementById('editlistAttributes').childElementCount;
		for (var m = 0; m < num1; m++) {
			var newatt = {
				value_name: document.getElementById('editlistAttributes').childNodes[m].childNodes[0].childNodes[0].childNodes[0].value.trim(),
				data_type: document.getElementById('editlistAttributes').childNodes[m].childNodes[1].childNodes[0].childNodes[0].value.trim(),
				value_type: document.getElementById('editlistAttributes').childNodes[m].childNodes[2].childNodes[0].childNodes[0].value.trim(),
				editable: document.getElementById('editlistAttributes').childNodes[m].childNodes[4].childNodes[0].childNodes[0].value.trim(),
				value_unit: document.getElementById('editlistAttributes').childNodes[m].childNodes[3].childNodes[0].childNodes[0].value.trim(),
				healthiness_criteria: document.getElementById('editlistAttributes').childNodes[m].childNodes[5].childNodes[0].childNodes[0].value.trim(),
				healthiness_value: document.getElementById('editlistAttributes').childNodes[m].childNodes[6].childNodes[0].childNodes[0].value.trim()
			};

			//console.log("new att:"+JSON.stringify(newatt));

			if (newatt.value_name != "" && !regex.test(newatt.value_name) && newatt.data_type != "" && newatt.value_type != "" && newatt.editable != "" && newatt.healthiness_criteria != "" && newatt.healthiness_value != "")
				mynewAttributes.push(newatt);
			else
				someNameisWrong = true;
		}

		if (mynewAttributes.length > 0 && !someNameisWrong) {

			document.getElementById('editlistAttributes').innerHTML = "";

			$("#editDeviceModalTabs").hide();
			$("#editDeviceModalBody").hide();
			$('#editDeviceModal div.modalCell').hide();
			$("#editDeviceModalFooter").hide();
			$("#editDeviceOkMsg").hide();
			$("#editDeviceOkIcon").hide();
			$("#editDeviceKoMsg").hide();
			$("#editDeviceKoIcon").hide();
			$('#editDeviceLoadingMsg').show();
			$('#editDeviceLoadingIcon').show();


			//console.log("LISTA" + JSON.stringify(mynewAttributes));
			var d = new Date();
			var t = d.getTime();
			//console.log("time before the insert request in milliseconds");
			//console.log(t);
			//console.log($('#inputLatitudeDevice'));
			//console.log($('#inputLatitudeDevice').val());
			//console.log($('#selectContextBroker'));

			var service = $('#deviceService').val();
			var servicePath = $('#editInputServicePathDevice').val();
			if (servicePath.charAt(0) == "/" && servicePath.length > 1) {
				servicePath = servicePath.substring(1, servicePath.length);
			}

			//console.log($('#selectProtocolDeviceM').val());
			if ($('#selectProtocolDeviceM').val() == "ngsi w/MultiService") {
				// servicePath value pre-processing
				if (servicePath[0] !== "/" || servicePath === "") servicePath = "/" + servicePath;
				if (servicePath[servicePath.length - 1] === "/" && servicePath.length > 1) servicePath = servicePath.substr(0, servicePath.length - 1);
			}

			//var id = service+"."+servicePath+"."+$.trim($('#inputNameDeviceM').val());
			var id = $.trim($('#inputNameDeviceM').val());
			var data = {
				action: "insert",
				attributes: JSON.stringify(mynewAttributes),
				id: id,
				type: $('#inputTypeDeviceM').val(),
				kind: $('#selectKindDeviceM').val(),
				contextbroker: $('#deviceCB').val(),
				protocol: $('#selectProtocolDeviceM').val(),
				format: $('#selectFormatDeviceM').val(),
				mac: $('#inputMacDeviceM').val(),
				model: $('#inputModelDeviceM').val(),
				producer: $('#inputProducerDeviceM').val(),
				latitude: $('#inputLatitudeDeviceM').val(),
				longitude: $('#inputLongitudeDeviceM').val(),
				visibility: $('#selectVisibilityDeviceM').val(),
				frequency: $('#inputFrequencyDeviceM').val(),
				token: sessionToken,
				k1: $("#KeyOneDeviceUserM").val(),
				k2: $("#KeyTwoDeviceUserM").val(),
				edgegateway_type: $("#selectEdgeGatewayTypeM").val(),
				edgegateway_uri: $("#inputEdgeGatewayUriM").val(),
				subnature: $('#selectSubnatureM').val(),
				static_attributes: JSON.stringify(retrieveStaticAttributes("addlistStaticAttributes", false)),
				service: service,
				servicePath: servicePath
			}
			console.log(data)

			$.ajax({
				url: "../api/device.php",
				data: {
					action: "insert",
					attributes: JSON.stringify(mynewAttributes),
					id: id,
					type: $('#inputTypeDeviceM').val(),
					kind: $('#selectKindDeviceM').val(),
					contextbroker: $('#deviceCB').val(),
					protocol: $('#selectProtocolDeviceM').val(),
					format: $('#selectFormatDeviceM').val(),
					mac: $('#inputMacDeviceM').val(),
					model: $('#inputModelDeviceM').val(),
					producer: $('#inputProducerDeviceM').val(),
					latitude: $('#inputLatitudeDeviceM').val(),
					longitude: $('#inputLongitudeDeviceM').val(),
					visibility: $('#selectVisibilityDeviceM').val(),
					frequency: $('#inputFrequencyDeviceM').val(),
					token: sessionToken,
					k1: $("#KeyOneDeviceUserM").val(),
					k2: $("#KeyTwoDeviceUserM").val(),
					edgegateway_type: $("#selectEdgeGatewayTypeM").val(),
					edgegateway_uri: $("#inputEdgeGatewayUriM").val(),
					subnature: $('#selectSubnatureM').val(),
					static_attributes: JSON.stringify(retrieveStaticAttributes("addlistStaticAttributes", false)),
					service: service,
					servicePath: servicePath
				},
				type: "POST",
				async: true,
				dataType: "JSON",
				//timeout: 0,
				success: function (mydata) {
					var d = new Date();
					var t = d.getTime();
					//console.log("time after a successful insert request in milliseconds");
					//console.log(t);
					//console.log(mydata["msg"]);
					if (mydata["status"] === 'ko') {
						alert("Error adding Device");
						console.log("Error adding Device");
						console.log(mydata);
						$('#editDeviceLoadingMsg').hide();
						$('#editDeviceLoadingIcon').hide();

						$("#editDeviceModal").modal('hide');

						$('#inputNameDeviceM').val("");
						$('#inputTypeDeviceM').val("");
						//$('#selectKindDevice').val(""),
						$('#deviceCB').val("NULL");
						$('#inputUriDeviceM').val("");
						//$('#selectProtocolDeviceM').val("NULL");
						//$('#selectFormatDevice').val("NULL");
						$('#inputMacDeviceM').val("");
						$('#inputModelDeviceM').val("");
						$('#inputProducerDeviceM').val("");
						$('#inputLatitudeDeviceM').val("");
						$('#inputLongitudeDeviceM').val("");
						$('#selectVisibilityDeviceM').val("NULL");
						$('#inputFrequencyDeviceM').val("600");
						$("#KeyOneDeviceUserM").val("");
						$("#KeyTwoDeviceUserM").val("");

						$('#selectSubnatureM').val("");
						$('#selectSubnatureM').trigger("change");
						$("#addNewStaticBtnM").hide();

						$("#editDeviceKoModal").modal('show');
						$("#editDeviceOkModal").hide();

					}
					else if (mydata["status"] === 'ok') {
						alert("Success adding Device");
						console.log("Success adding Device");
						//console.log(JSON.stringify(mydata));

						console.log(mydata);
						$('#editDeviceLoadingMsg').hide();
						$('#editDeviceLoadingIcon').hide();

						$("#editDeviceModal").modal('hide');

						$('#inputNameDeviceM').val("");
						$('#inputTypeDeviceM').val("");
						//$('#selectKindDevice').val(""),
						$('#deviceCB').val("NULL");
						$('#inputUriDeviceM').val("");
						//$('#selectProtocolDeviceM').val("NULL");
						//$('#selectFormatDevice').val("NULL");
						$('#inputMacDeviceM').val("");
						$('#inputModelDeviceM').val("");
						$('#inputProducerDeviceM').val("");
						$('#inputLatitudeDeviceM').val("");
						$('#inputLongitudeDeviceM').val("");
						$('#selectVisibilityDeviceM').val("NULL");
						$('#inputFrequencyDeviceM').val("600");
						$("#KeyOneDeviceUserM").val("");
						$("#KeyTwoDeviceUserM").val("");

						$('#selectSubnatureM').val("");
						$('#selectSubnatureM').trigger("change");
						$("#addNewStaticBtnM").hide();

						$("#editDeviceKoModal").hide();
						$("#editDeviceOkModalInnerDiv1").html('<h5>The device has been successfully registered. You can find further information on how to use and set up your device at the following page:</h5>' + "   " + '<h5>https://www.snap4city.org/drupal/node/76</h5>');
						$("#editDeviceOkModal").modal('show');

						$('#statusLabel').text("DEVICE " + id + " SUCCESFULLY ADDED.");

						setTimeout(() => {
							$("#editDeviceOkModal").modal('hide');
							$('#startDiscoveryButton').click();
						}, 2000);


					}

				},
				error: function (mydata) {
					alert("Error insert device");
					console.log("Error insert device");
					console.log("Error status -- Ko result: " + JSON.stringify(mydata));

					$('#editDeviceLoadingMsg').hide();
					$('#editDeviceLoadingIcon').hide();

					$("#editDeviceModal").modal('hide');

					$('#inputNameDeviceM').val("");
					$('#inputTypeDeviceM').val("");
					//$('#selectKindDevice').val(""),
					$('#deviceCB').val("NULL");
					$('#inputUriDeviceM').val("");
					//$('#selectProtocolDeviceM').val("NULL");
					//$('#selectFormatDevice').val("NULL");
					$('#inputMacDeviceM').val("");
					$('#inputModelDeviceM').val("");
					$('#inputProducerDeviceM').val("");
					$('#inputLatitudeDeviceM').val("");
					$('#inputLongitudeDeviceM').val("");
					$('#selectVisibilityDeviceM').val("NULL");
					$('#inputFrequencyDeviceM').val("600");
					$("#KeyOneDeviceUserM").val("");
					$("#KeyTwoDeviceUserM").val("");

					$('#selectSubnatureM').val("");
					$('#selectSubnatureM').trigger("change");
					$("#addNewStaticBtnM").hide();

					console.log("Error adding Device type");
					console.log(mydata);
					$("#editDeviceKoModal").modal('show');
					$("#editDeviceOkModal").hide();
				}
			});

		}
		else if (mynewAttributes.length == 0) {
			alert("Check the values tab, there must be at least one value");
		}
		else {
			alert("Check the values of your device, make sure that data you entered are valid!");
		}
	});

	$("#addAttrMBtn").click(function () {
		//console.log("#addAttrMBtn");
		content = drawAttributeMenu("", "", "", "", "", "", "300", " ", 'addlistAttributesM');
		editDeviceConditionsArray['addlistAttributesM'] = true;
		$('#addlistAttributesM').append(content);
	});
});

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

				document.getElementById('inputLatitudeDeviceM').value = lat;
				document.getElementById('inputLongitudeDeviceM').value = lng;
				editDeviceConditionsArray['inputLatitudeDeviceM'] = true;
				checkEditDeviceLatitude(); checkEditDeviceConditions();
				editDeviceConditionsArray['inputLongitudeDeviceM'] = true;
				checkEditDeviceLongitude(); checkEditDeviceConditions();
				if (marker) {
					map1.removeLayer(marker);
				}
				marker = new L.marker([lat, lng]).addTo(map1).bindPopup(lat + ',' + lng);
			});
		}
	}
}