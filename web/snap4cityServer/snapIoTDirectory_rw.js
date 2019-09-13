
//server.js
'use strict'
//dipendenze

var express = require('express');
var request = require('request');
var bodyParser = require('body-parser');
var mysql = require('mysql');
const spawn = require('child_process').spawn;
var registeredStub = [];
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
	console.log("path "+ req.body.path);
    var args = [];

	console.log("context "+ req.body.contextbroker);
	var keys = Object.keys(registeredStub);

	if(keys.indexOf(req.body.contextbroker)>=0)
	{
		if(req.body.ip == "kill"){
			console.log('kill');
			registeredStub[req.body.contextbroker].kill();	
			delete registeredStub[req.body.contextbroker];
			console.log('kill'+JSON.stringify(registeredStub));

		}else{
			res.json({ message: 'stub already active for ORION context broker ' + req.body.contextbroker});
		}
	}
	else
	{
		console.log("ciao "+ Object.keys(registeredStub))
		//args = ['./snap4cityBroker/ngsi2IoTDirectory_testing.js',
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


	    console.log("args: "+args);
		console.log("port: "+ req.body.port + "apiker "+ req.body.apikey);
	   //const spawn = require('child_process').spawn;
/*
	   const child_ngsi = spawn('node',args);
	
		child_ngsi.stdout.on('data', (data) => {
			var str = data.toString();
			var str2 = "";
			for(var i = 0; i < str.length; i++){
				var c = str.charAt(i);
				if(c.match(/[a-z]/i)){
					str2 = str2+c;
				}
			}

			if(str2.localeCompare("fatto")==0){
				request.state
				console.log("finalmente");
				res.status(200).send();
			}
		});
	*/
	   res.json({ message: 'activated stub for ORION'});


	 const child_ngsi = spawn('node',args, {
		//  detached: true,
		 stdio: 'inherit'
 	});
 	   registeredStub[req.body.contextbroker]=child_ngsi;
	   console.log("registeredStud "+ registeredStub[req.body.contextbroker]);

	   // child.unref();
	}  
});


router.route('/nodered-ngsi')
 .post(function(req, res) {
	console.log("node red, contextbroker "+ req.body.contextbroker);
	console.log("node red, selectedAttributes "+ req.body.selectedAttributes);
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


	    console.log("args: "+args);

	   //res.json({ message: 'activated stub for ORION from node red'});
	   const child_ngsi = spawn('node',args, {
			   stdio: 'pipe'
	   });
    
        var promiseResNodeRed = new Promise(function(resolve, reject){	
			child_ngsi.stdout.on('data', function(data) {
				let result = data.toString();
				console.log("result "+result);
                resolve(result);
			});
					
		})
		promiseResNodeRed.then(function(msg){
			console.log("msg "+ msg);
			res.json({ message: msg, note:'activated stub for ORION from node red' });
		});
		
		/*child_ngsi.stderr.on('data', (data) => {
			console.log(`stderr: ${data}`);
		});

		child_ngsi.on('close', (code) => {
			console.log(`child process exited with code ${code}`);
	});*/
});

router.route('/extract')
 .post(function(req, res) {
    var args = [];
	//console.log("Snap kind : "+req.body.kind);

	/*if (registeredStub[req.body.contextbroker] !== undefined || registeredStub[req.body.contextbroker] !== "")  res.json({ message: 'stub already active for ORION context broker ' + req.body.contextbroker});
	else
	{*/
		registeredStub.push(req.body.contextbroker);
		//args = ['./snap4cityBroker/ngsi2IoTDirectory_testing.js',
		args= ['./snap4cityBroker/externalBroker.js',
			req.body.contextbroker,
			req.body.device_name,
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
		
	  //  console.log("args: "+args);

		const child_ngsi = spawn('node',args, {stdio: 'pipe' });
		var promiseRes2 = new Promise(function(resolve, reject){	
			child_ngsi.stdout.on('data', function(data) {
				let result = data.toString();
				console.log("reeult "+result);
				if(result.startsWith("{") || result.localeCompare("not found\n") == 0){
					console.log("msg2" + result);
					resolve(result);

				}
			});
					
		})
		promiseRes2.then(function(msg){
			console.log("msg "+ msg);
			res.json({ message: msg});
		});
		
		child_ngsi.stderr.on('data', (data) => {
			console.log(`stderr: ${data}`);
		});

		child_ngsi.on('close', (code) => {
			console.log(`child process exited with code ${code}`);
	});
   // child.unref();

});

//Do not put this part in production
router.route('/rawdata')
 .post(function(req, res) {
	console.log("path "+ req.body.path);
    var args = [];
//	console.log("Snap kind : "+ req.body.model + " gateway "+ req.body.edge_gateway_type + " uri "+ req.body.edge_gateway_uri);

	/*if (registeredStub[req.body.contextbroker] !== undefined || registeredStub[req.body.contextbroker] !== "")  res.json({ message: 'stub already active for ORION context broker ' + req.body.contextbroker});
	else
	{*/
		registeredStub.push(req.body.contextbroker);
		//args = ['./snap4cityBroker/ngsi2IoTDirectory_testing.js',
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
				console.log("reeult "+result);
				if(result.startsWith("{") || result.localeCompare("not found\n") == 0){
					console.log("msg2" + result);
					resolve(result);
				}
			});
					
		})
		promiseRes.then(function(msg){
			console.log("msg "+ msg);
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
    console.log("entrato amqp");

		// console.log(req.body.contextbroker + " " +req.body.ip + " " +req.body.port);
		var args = [];
		var keys = Object.keys(registeredStub);

		if(keys.indexOf(req.body.contextbroker)>=0)
		{
			if(req.body.ip == "kill"){
				console.log('kill');
				registeredStub[req.body.contextbroker].kill();	
				delete registeredStub[req.body.contextbroker];
	
			}else{
				res.json({ message: 'stub already active for AMQP context broker ' + req.body.contextbroker});
			}
		}
		else
		{
		registeredStub.push(req.body.contextbroker);

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
	   // console.log(args);
//	   const spawn = require('child_process').spawn;

	   const child_amqp = spawn('node',args, {
			  //  detached: true,
			   stdio: 'inherit'
	   });
	   registeredStub[req.body.contextbroker]=child_ngsi;

	   // child.unref();

	 res.json({ message: 'activated stub for AMQP'});
	 
	}
});

router.route('/mqtt')
 .post(function(req, res) {
	var keys = Object.keys(registeredStub);

	if(keys.indexOf(req.body.contextbroker)>=0)
	{
		if(req.body.ip == "kill"){
			console.log('kill');
			registeredStub[req.body.contextbroker].kill();	
			delete registeredStub[req.body.contextbroker];
	
		}else{
			res.json({ message: 'stub already active for ORION context broker ' + req.body.contextbroker});
		}
	}
	else
	{
		registeredStub.push(req.body.contextbroker);

		console.log("edge gateway "+req.body.edge_gateway_type+ " " +req.body.ip + " " +req.body.port);
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
		   // console.log(args);
		   // const spawn = require('child_process').spawn;

		   const child_mqtt = spawn('node',args, {
			  //     detached: true,
				   stdio: 'inherit'
		   });
		   registeredStub[req.body.contextbroker]=child_ngsi;

		   // child.unref();
		   res.json({ message: 'activated stub for MQTT'});
		}	   
});
router.route('/status')
 .get(function(req, res) {
 console.log("entrato status"+Object.keys(registeredStub)+ "keys");
 
 res.json({ message: Object.keys(registeredStub)});
});

