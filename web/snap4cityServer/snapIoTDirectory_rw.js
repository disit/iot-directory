
//server.js
'use strict'
//dipendenze
var express = require('express');
// var bodyParser = require('body-parser');	//deprecated
var Promise = require('promise');
const spawn = require('child_process').spawn;

var registeredStub = new Map();

//istanze
var app = express();
var router = express.Router();
//porta (default=3001)

var port = process.env.API_PORT || 3001;
//configurazione api
// app.use(bodyParser.urlencoded({ extended: true }));	//deprecated
// app.use(bodyParser.json());							//deprecated
app.use(express.urlencoded({ extended: true }))
app.use(express.json());

app.use(function (req, res, next) {
	res.setHeader('Access-Control-Allow-Origin', '*');
	res.setHeader('Access-Control-Allow-Credentials', 'true');
	res.setHeader('Access-Control-Allow-Methods', 'GET,HEAD,OPTIONS,POST,PUT,DELETE');
	res.setHeader('Access-Control-Allow-Headers', 'Access-Control-Allow-Headers, Origin,Accept, X-Requested-With, Content-Type, Access-Control-Request-Method, Access-Control-Request-Headers');
	res.setHeader('Cache-Control', 'no-cache');
	next();
});

router.get('/', function (req, res) {
	res.json({ message: 'API Initialized!' });
});

//utilizzo configurazione router
app.use('/api', router);
//starts server
app.listen(port, function () {
	console.log('api running on port ' + port);
});

router.route('/ngsi')
	.post(function (req, res) {
		var args = [];
		if (registeredStub.get(req.body.contextbroker) != undefined) {
			if (req.body.ip == "kill") {
				registeredStub.get(req.body.contextbroker).kill();
				registeredStub.delete(req.body.contextbroker);

				var brokerActive = 'killing ' + req.body.contextbroker;
				if (registeredStub.size == 0) {
					brokerActive += '. There are no active brokers left.';
				}
				else {
					brokerActive += '. Broker still active: ';

					for (var key of registeredStub.keys()) {
						brokerActive += key + "; ";
					}
				}
				console.log("Active Broker:" + brokerActive);

			} else {
				console.log("Broker " + req.body.contextbroker + " is already active.");
				res.json({ message: 'stub already active for ORION context broker ' + req.body.contextbroker });
			}
		}
		else {
			console.log("Retrieval from " + req.body.contextbroker + " activated.");

			args = ['./snap4cityBroker/ngsi2IoTDirectory_rw.js',
				req.body.contextbroker,
				req.body.ip,
				req.body.user,
				req.body.al,
				req.body.model,
				req.body.edge_gateway_type,
				req.body.edge_gateway_uri,
				req.body.organization,
				req.body.path,
				req.body.kind,
				req.body.apikey,
				req.body.ap,
				req.body.token
			];

			console.log("Passed parameters: " + args);

			const child_ngsi = spawn('node', args, { stdio: 'inherit' });

			registeredStub.set(req.body.contextbroker, child_ngsi);

			var regBroker = "Broker registered: ";
			for (var key of registeredStub.keys()) {
				regBroker += key + "; ";
			}
			console.log("" + regBroker);
		}
	});

router.route('/ngsi_w_MultiService')
	.post(function (req, res) {
		var args = [];

		if (registeredStub.get(req.body.contextbroker) != undefined) {
			if (req.body.ip == "kill") {
				registeredStub.get(req.body.contextbroker).kill();
				registeredStub.delete(req.body.contextbroker);

				var brokerActive = 'killing ' + req.body.contextbroker;
				if (registeredStub.size == 0) {
					brokerActive += '. There are no active brokers left.';
				}
				else {
					brokerActive += '. Broker still active: ';

					for (var key of registeredStub.keys()) {
						brokerActive += key + "; ";
					}
				}
				console.log(brokerActive);

			} else {
				console.log("Broker " + req.body.contextbroker + " is already active.");
				res.json({ message: 'stub already active for ORION context broker ' + req.body.contextbroker });
			}
		}
		else {
			console.log("Retrieval from " + req.body.contextbroker + " activated.");
			args = ['./snap4cityBroker/ngsiMTSP2IoTDirectory_rw.js',
				req.body.contextbroker,
				req.body.ip,
				req.body.user,
				req.body.al,
				req.body.ap,
				req.body.model,
				req.body.edge_gateway_type,
				req.body.edge_gateway_uri,
				req.body.organization,
				req.body.path,
				req.body.kind,
				req.body.apikey,
				req.body.token
			];

			console.log('Passed parameters: ' + args);

			const child_ngsi = spawn('node', args, { stdio: 'inherit' });

			registeredStub.set(req.body.contextbroker, child_ngsi);

			var regBroker = "Broker registered: ";
			for (var key of registeredStub.keys()) {
				regBroker += key + "; ";
			}
			console.log(regBroker);
			// child.unref();
		}
	});

router.route('/extract')
	.post(function (req, res) {
		var args = [];

		args = ['./snap4cityBroker/externalBroker.js',
			req.body.contextbroker,
			req.body.device_name,
			req.body.ip,
			req.body.user,
			req.body.al,
			req.body.ap,
			req.body.model,
			"null",	//req.body.edge_gateway_type, never used
			"null",	//req.body.edge_gateway_uri, never used
			req.body.organization,
			req.body.path,
			req.body.kind,
			req.body.apikey,
			req.body.service,
			req.body.service_path,
			req.body.token
		];

		console.log("invoking extract");

		console.log("Passed parameters: " + args);

		const child_ngsi = spawn('node', args, { stdio: 'pipe' });

		//serve?
		//registeredStub.set(req.body.contextbroker, child_ngsi);

		child_ngsi.stdout.on('data', (data) => {
			let result = data.toString();
			console.log(result);
			if (result.startsWith("{") || result.localeCompare("not found\n") == 0) {
				console.log("returing stdout:" + result);
				res.json({ message: result });
			}
			child_ngsi.kill()
		})

		child_ngsi.stderr.on('data', (data) => {
			let result = data.toString();
			console.log("returing stderr:" + result);
			res.json({ message: result });
			child_ngsi.kill();
		})

		//TODO how to uniform these two following Promise and function/then?
		//correct returning
		// var promiseRes_stdout = new Promise(function (resolve, reject) {
		// 	child_ngsi.stdout.on('data', function (data) {
		// 		console.log(data.toString())
		// 		let result = data.toString();
		// 		if (result.startsWith("{") || result.localeCompare("not found\n") == 0) {
		// 			resolve(result);
		// 		}
		// 	});
		// });
		// promiseRes_stdout.then(function (msg) {
		// 	console.log("returing stdout:" + msg);
		// 	res.json({ message: msg });
		// 	child_ngsi.kill();
		// });

		// //error mngt	
		// var promiseRes_stderr = new Promise(function (resolve, reject) {
		// 	child_ngsi.stderr.on('data', (data) => {
		// 		console.log(`stderr: ${data}`);
		// 		resolve(data.toString());
		// 	});
		// });
		// promiseRes_stderr.then(function (msg) {
		// 	console.log("returing stderr:" + msg);
		// 	res.json({ message: msg });
		// 	child_ngsi.kill();
		// });
	});

router.route('/discover')
	.post(function (req, res) {
		var args = [];

		args = ['./snap4cityBroker/discovery.js',
			req.body.contextbroker,
			req.body.ip,
			req.body.port,
			req.body.al,
			req.body.ap,
			req.body.path,
			req.body.organization,
			req.body.login,
			req.body.password,
			req.body.tenant,
			req.body.servicepath,
			req.body.apikey
		];

		console.log("invoking discovery");
		console.log("Passed parameters: " + args);

		const child_discover = spawn('node', args, { stdio: 'pipe' });

		//serve?
		//registeredStub.set(req.body.contextbroker, child_discover);

		child_discover.stdout.on('data', (data) => {
			console.log("returing stdout:" + data.toString());
			let result = JSON.parse(data);
			res.json({ message: result });
			child_discover.kill();
		})

		child_discover.stderr.on('data', (data) => {
			let result = data.toString();
			console.log("returing stderr:" + result);
			res.json({ message: result });
			child_discover.kill();
		})

		//TODO how to uniform these two following Promise and function/then?
		//correct returning
		// var promiseRes_stdout = new Promise(function (resolve, reject) {
		// 	child_discover.stdout.on('data', function (data) {
		// 		let result = JSON.parse(data);
		// 		resolve(result);
		// 	});
		// });
		// promiseRes_stdout.then(function (msg) {
		// 	console.log("returing stdout:" + msg);
		// 	res.json({ message: msg });
		// 	child_discover.kill();
		// });

		//error mngt
		// var promiseRes_stderr = new Promise(function (resolve, reject) {
		// 	child_discover.stderr.on('data', (data) => {
		// 		console.log("stderr: ${data}");
		// 		resolve(data.toString());
		// 	});
		// });
		// promiseRes_stderr.then(function (msg) {
		// 	console.log("returing stderr:" + msg);
		// 	res.json({ message: msg });
		// 	child_discover.kill();
		// });
	});

router.route('/nodered-ngsi')
	.post(function (req, res) {
		//console.log("node red, contextbroker "+ req.body.contextbroker);
		//console.log("node red, selectedAttributes "+ req.body.selectedAttributes);
		var args = [];
		registeredStub.push(req.body.contextbroker);
		args = ['./snap4cityBroker/nodeRed-ngsi.js',
			req.body.contextbroker,
			req.body.model,
			req.body.organization,
			req.body.dataToParse,
			//req.body.selectedAttributes.replace(/,/g,"_-_")
			req.body.selectedAttributes

		];

		//res.json({ message: 'activated stub for ORION from node red'});
		const child_ngsi = spawn('node', args, {
			stdio: 'pipe'
		});

		var promiseResNodeRed = new Promise(function (resolve, reject) {
			child_ngsi.stdout.on('data', function (data) {
				let result = data.toString();
				resolve(result);
			});

		})
		promiseResNodeRed.then(function (msg) {
			res.json({ message: msg, note: 'activated stub for ORION from node red' });
		});

	});

//Do not put this part in production
router.route('/rawdata')
	.post(function (req, res) {
		var args = [];

		//registeredStub.push(req.body.contextbroker);
		args = ['./snap4cityBroker/retrieval_json_from_cb.js',
			req.body.contextbroker,
			req.body.ip,
			req.body.al,
			req.body.path,
			req.body.kind,
			req.body.apikey
		];

		const child_ngsi = spawn('node', args, { stdio: 'pipe' });
		var promiseRes = new Promise(function (resolve, reject) {

			child_ngsi.stdout.on('data', function (data) {
				let result = data.toString();
				if (result.startsWith("{") || result.localeCompare("not found\n") == 0) {
					resolve(result);
				}
			});

		})
		promiseRes.then(function (msg) {
			res.json({ message: msg });
		});

		child_ngsi.stderr.on('data', (data) => {
			console.log(`stderr: ${data}`);
		});

		child_ngsi.on('close', (code) => {
			console.log(`child process exited with code ${code}`);
		});

	});

router.route('/amqp')
	.post(function (req, res) {

		// console.log(req.body.contextbroker + " " +req.body.ip + " " +req.body.port);
		var args = [];

		if (registeredStub.get(req.body.contextbroker) != undefined) {
			if (req.body.ip == "kill") {
				registeredStub.get(req.body.contextbroker).kill();
				registeredStub.delete(req.body.contextbroker);

				var brokerActive = 'killing ' + req.body.contextbroker;
				if (registeredStub.size == 0) {
					brokerActive += '. There are no active brokers left.';
				}
				else {
					brokerActive += '. Broker still active: ';

					for (var key of registeredStub.keys()) {
						brokerActive += key + "; ";
					}
				}
				console.log(brokerActive);

			} else {
				console.log("Broker " + req.body.contextbroker + " is already active.");
				res.json({ message: 'stub already active for ORION context broker ' + req.body.contextbroker });
			}
		}
		else {
			console.log("Retrieval from " + req.body.contextbroker + " activated.");

			args = ['./snap4cityBroker/amqp2IoTDirectory_cbRetrieval.js',
				req.body.contextbroker,
				req.body.ip,
				req.body.port,
				req.body.user,
				req.body.al,
				req.body.model,
				req.body.edge_gateway_type,
				req.body.edge_gateway_uri
			];

			const child_amqp = spawn('node', args, {
				//  detached: true,
				stdio: 'inherit'
			});

			registeredStub.set(req.body.contextbroker, child_amqp);

			var regBroker = "Broker registered: ";
			for (var key of registeredStub.keys()) {
				regBroker += key + "; ";
			}
			console.log(regBroker);
			//	res.json({ message: 'activated stub for AMQP'});

		}
	});

router.route('/mqtt')
	.post(function (req, res) {

		if (registeredStub.get(req.body.contextbroker) != undefined) {
			if (req.body.ip == "kill") {
				registeredStub.get(req.body.contextbroker).kill();
				registeredStub.delete(req.body.contextbroker);

				var brokerActive = 'killing ' + req.body.contextbroker;
				if (registeredStub.size == 0) {
					brokerActive += '. There are no active brokers left.';
				}
				else {
					brokerActive += '. Broker still active: ';

					for (var key of registeredStub.keys()) {
						brokerActive += key + "; ";
					}
				}
				console.log(brokerActive);

			} else {
				console.log("Broker " + req.body.contextbroker + " is already active.");
				res.json({ message: 'stub already active for ORION context broker ' + req.body.contextbroker });
			}
		}
		else {
			console.log("Retrieval from " + req.body.contextbroker + " activated.");

			var args = [];
			args = ['./snap4cityBroker/mqtt2IoTDirectory_cbRetrieval.js',
				req.body.contextbroker,
				req.body.ip,
				req.body.port,
				req.body.user,
				req.body.al,
				req.body.model,
				req.body.edge_gateway_type,
				req.body.edge_gateway_uri
			];

			const child_mqtt = spawn('node', args, {
				//     detached: true,
				stdio: 'inherit'
			});
			//registeredStub[req.body.contextbroker]=child_mqtt;

			registeredStub.set(req.body.contextbroker, child_mqtt);

			var regBroker = "Broker registered: ";
			for (var key of registeredStub.keys()) {
				regBroker += key + "; ";
			}
			console.log(regBroker);
			// child.unref();
			// res.json({ message: 'activated stub for MQTT'});
		}
	});

router.route('/status')
	.get(function (req, res) {
		var brokers = [];
		for (var key of registeredStub.keys()) {
			brokers.push(key);
		}
		res.json({ message: JSON.stringify(brokers) });
	});

