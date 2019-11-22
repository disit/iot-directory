
//server.js
'use strict'
//dipendenze

var express = require('express');
var request = require('request');
var bodyParser = require('body-parser');
var mysql = require('mysql');
const spawn = require('child_process').spawn;
//var registeredStub = [];
var registeredStub = new Map();

var Promise = require('promise');


//istanze
var app = express();

var router = express.Router();
//porta (default=3001)

var port = process.env.API_PORT || 3001;
//configurazione api
app.use(bodyParser.urlencoded({ extended: true }));
app.use(bodyParser.json());

app.use(function(req, res, next) {
 res.setHeader('Access-Control-Allow-Origin', '*');
 res.setHeader('Access-Control-Allow-Credentials', 'true');
 res.setHeader('Access-Control-Allow-Methods', 'GET,HEAD,OPTIONS,POST,PUT,DELETE');
 res.setHeader('Access-Control-Allow-Headers', 'Access-Control-Allow-Headers, Origin,Accept, X-Requested-With, Content-Type, Access-Control-Request-Method, Access-Control-Request-Headers');
 res.setHeader('Cache-Control', 'no-cache');
 next();
});

router.get('/', function(req, res) {
 res.json({ message: 'API Initialized!'});
});

//utilizzo configurazione router
app.use('/api', router);
//starts server
 app.listen(port, function() {

 console.log('api running on port '+ port );
});


router.route('/ngsi')
 .post(function(req, res) {
    var args = [];
	
	if(registeredStub.get(req.body.contextbroker) != undefined)
	{
		if(req.body.ip == "kill"){
			registeredStub.get(req.body.contextbroker).kill();
			registeredStub.delete(req.body.contextbroker);
			
			var brokerActive = 'killing ' + req.body.contextbroker;
			if(registeredStub.size == 0){
				brokerActive += '. There are no active brokers left.';
			}
			else{
				brokerActive += '. Broker still active: ';
				
				for (var key of registeredStub.keys()) {
					brokerActive += key +"; ";
				}
			}
			console.log(brokerActive);

		}else{
			console.log("Broker "+ req.body.contextbroker + " is already active.");
			res.json({ message: 'stub already active for ORION context broker ' + req.body.contextbroker});
		}
	}
	else
	{
		console.log("Retrieval from "+ req.body.contextbroker + " activated.");
		//registeredStub.push(req.body.contextbroker);

		args= ['./snap4cityBroker/ngsi2IoTDirectory_rw.js',
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
			req.body.apikey
		];

	 const child_ngsi = spawn('node',args, {
		//  detached: true,
		 stdio: 'inherit'
 	});
	
	registeredStub.set(req.body.contextbroker, child_ngsi);

	var regBroker = "Broker registered: ";
	for (var key of registeredStub.keys()) {
	  regBroker += key + "; ";
	}
	console.log(regBroker);
	   // child.unref();
	}  
});


router.route('/nodered-ngsi')
 .post(function(req, res) {
	//console.log("node red, contextbroker "+ req.body.contextbroker);
	//console.log("node red, selectedAttributes "+ req.body.selectedAttributes);
    var args = [];
		registeredStub.push(req.body.contextbroker);
		args= ['./snap4cityBroker/nodeRed-ngsi.js',
			req.body.contextbroker,
			req.body.model,
			req.body.organization,
			req.body.dataToParse,
			//req.body.selectedAttributes.replace(/,/g,"_-_")
			req.body.selectedAttributes
               
		];

	   //res.json({ message: 'activated stub for ORION from node red'});
	   const child_ngsi = spawn('node',args, {
			   stdio: 'pipe'
	   });
    
        var promiseResNodeRed = new Promise(function(resolve, reject){	
			child_ngsi.stdout.on('data', function(data) {
				let result = data.toString();
                resolve(result);
			});
					
		})
		promiseResNodeRed.then(function(msg){
			res.json({ message: msg, note:'activated stub for ORION from node red' });
		});

});

router.route('/extract')
 .post(function(req, res) {
    var args = [];

		//registeredStub.push(req.body.contextbroker);

		args= ['./snap4cityBroker/externalBroker.js',
			req.body.contextbroker,
			req.body.device_name,
			req.body.ip,
			req.body.user,
			req.body.al,
			req.body.ap,
			req.body.model,
			"null",
			"null",
			//req.body.edge_gateway_type, never used
			//req.body.edge_gateway_uri, never used
			req.body.organization,
			req.body.path,
			req.body.kind,
			req.body.apikey
		];
	
		console.log("invoking extract");
	
		const child_ngsi = spawn('node',args, {stdio: 'pipe' });
	
		registeredStub.set(req.body.contextbroker, child_ngsi);	

		//TODO how to uniform these two following Promise and function/then?
		//correct returning
		var promiseRes_stdout = new Promise(function(resolve, reject){	
			child_ngsi.stdout.on('data', function(data) {
				let result = data.toString();
				if(result.startsWith("{") || result.localeCompare("not found\n") == 0){
					resolve(result);
				}
			});
		});
		promiseRes_stdout.then(function(msg){
			console.log("returing stdout:"+msg);
			res.json({ message: msg});
			child_ngsi.kill();
		});

		//error mngt	
                var promiseRes_stderr = new Promise(function(resolve, reject){
			child_ngsi.stderr.on('data', (data) => {
                        	console.log(`stderr: ${data}`);
				resolve(data.toString());
	                });
                });
                promiseRes_stderr.then(function(msg){
                        console.log("returing stderr:"+msg);
                        res.json({ message: msg});
			child_ngsi.kill();
                });
});

//Do not put this part in production
router.route('/rawdata')
 .post(function(req, res) {
    var args = [];

		//registeredStub.push(req.body.contextbroker);
		args= ['./snap4cityBroker/retrieval_json_from_cb.js',
			req.body.contextbroker,
			req.body.ip,
			req.body.al,
			req.body.path,
			req.body.kind,
			req.body.apikey
		];

		const child_ngsi = spawn('node',args, {stdio: 'pipe' });
		var promiseRes = new Promise(function(resolve, reject){	

			child_ngsi.stdout.on('data', function(data) {
				let result = data.toString();
				if(result.startsWith("{") || result.localeCompare("not found\n") == 0){
					resolve(result);
				}
			});
					
		})
		promiseRes.then(function(msg){
			res.json({ message: msg});
		});
		
		child_ngsi.stderr.on('data', (data) => {
			console.log(`stderr: ${data}`);
		});

		child_ngsi.on('close', (code) => {
			console.log(`child process exited with code ${code}`);
	});

});

router.route('/amqp')
 .post(function(req, res) {

		// console.log(req.body.contextbroker + " " +req.body.ip + " " +req.body.port);
		var args = [];
	
		if(registeredStub.get(req.body.contextbroker) != undefined)
		{
			if(req.body.ip == "kill"){
				registeredStub.get(req.body.contextbroker).kill();
				registeredStub.delete(req.body.contextbroker);
				
				var brokerActive = 'killing ' + req.body.contextbroker;
				if(registeredStub.size == 0){
					brokerActive += '. There are no active brokers left.';
				}
				else{
					brokerActive += '. Broker still active: ';
					
					for (var key of registeredStub.keys()) {
						brokerActive += key +"; ";
					}
				}
				console.log(brokerActive);

			}else{
				console.log("Broker "+ req.body.contextbroker + " is already active.");
				res.json({ message: 'stub already active for ORION context broker ' + req.body.contextbroker});
			}
		}
		else
		{
		console.log("Retrieval from "+ req.body.contextbroker + " activated.");

		args= ['./snap4cityBroker/amqp2IoTDirectory_cbRetrieval.js',
			req.body.contextbroker,
			req.body.ip,
			req.body.port,
			req.body.user,
			req.body.al,
			req.body.model,
			req.body.edge_gateway_type,
			req.body.edge_gateway_uri
		];

	   const child_amqp = spawn('node',args, {
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
 .post(function(req, res) {

	if(registeredStub.get(req.body.contextbroker) != undefined)
		{
			if(req.body.ip == "kill"){
				registeredStub.get(req.body.contextbroker).kill();
				registeredStub.delete(req.body.contextbroker);
				
				var brokerActive = 'killing ' + req.body.contextbroker;
				if(registeredStub.size == 0){
					brokerActive += '. There are no active brokers left.';
				}
				else{
					brokerActive += '. Broker still active: ';
					
					for (var key of registeredStub.keys()) {
						brokerActive += key +"; ";
					}
				}
				console.log(brokerActive);

			}else{
				console.log("Broker "+ req.body.contextbroker + " is already active.");
				res.json({ message: 'stub already active for ORION context broker ' + req.body.contextbroker});
			}
		}
	else
	{
		console.log("Retrieval from "+ req.body.contextbroker + " activated.");

			var args = [];
			args= ['./snap4cityBroker/mqtt2IoTDirectory_cbRetrieval.js',
				req.body.contextbroker, 
				req.body.ip,
				req.body.port,
				req.body.user,
				req.body.al,
				req.body.model,
				req.body.edge_gateway_type,
				req.body.edge_gateway_uri
				];

		   const child_mqtt = spawn('node',args, {
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
 .get(function(req, res) {
	var brokers = [];
 	for (var key of registeredStub.keys()) {
	  brokers.push(key);
	}
	res.json({ message: JSON.stringify(brokers)});

 //res.json({ message: JSON.stringify(registeredStub)});
});

