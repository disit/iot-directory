
//server.js
'use strict'
//dipendenze
var express = require('express');
var request = require('request');
var bodyParser = require('body-parser');
var mysql = require('mysql');
const spawn = require('child_process').spawn;

var registeredStub = [];


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
 console.log('api running on port ${port}');
});


router.route('/ngsi')
 .post(function(req, res) {
    console.log("entrato ngsi");

    // console.log(req.body.contextbroker + " " +req.body.ip + " " +req.body.port);
    var args = [];
	if (registeredStub.contains(req.body.contextbroker))  res.json({ message: 'stub already active for ORION'});
	else
	{
		registeredStub.push(req.body.contextbroker);
		args= ['./snap4cityBroker/ngsi2IoTDirectory.js',
			req.body.contextbroker,
			req.body.ip,
			req.body.port
		];
	   // console.log(args);
	   //const spawn = require('child_process').spawn;

	   const child_ngsi = spawn('node',args, {
			  //  detached: true,
			   stdio: 'inherit'
	   });
	   // child.unref();

	   res.json({ message: 'activated stub for ORION'});
	}   
});



router.route('/amqp')
 .post(function(req, res) {
    console.log("entrato amqp");
    if (registeredStub.contains(req.body.contextbroker))  res.json({ message: 'stub already active for AMQP'});
	else
	{ 
		// console.log(req.body.contextbroker + " " +req.body.ip + " " +req.body.port);
		var args = [];
		registeredStub.push(req.body.contextbroker);
		args= ['./snap4cityBroker/amqp2IoTDirectory.js',
			req.body.contextbroker,
			req.body.ip,
			req.body.port
		];
	   // console.log(args);
//	   const spawn = require('child_process').spawn;

	   const child_amqp = spawn('node',args, {
			  //  detached: true,
			   stdio: 'inherit'
	   });
	   // child.unref();

	 res.json({ message: 'activated stub for AMQP'});
	} 
});


router.route('/mqtt')
 .post(function(req, res) {
    console.log("entrato MQTT");
    // console.log(req.body.contextbroker + " " +req.body.ip + " " +req.body.port);
    if (registeredStub.contains(req.body.contextbroker))  res.json({ message: 'stub already active for MQTT'});
	else
	{ 	
			registeredStub.push(req.body.contextbroker);
			var args = [];
			args= ['./snap4cityBroker/mqtt2IoTDirectory.js',
				req.body.contextbroker, 
				req.body.ip,
				req.body.port
			];
		   // console.log(args);
		   // const spawn = require('child_process').spawn;

		   const child_mqtt = spawn('node',args, {
			  //     detached: true,
				   stdio: 'inherit'
		   });
		   // child.unref();
		   res.json({ message: 'activated stub for MQTT'});
	}	   
});

router.route('/status')
 .get(function(req, res) {
 console.log("entrato status");
 res.json({ message: JSON.stringify(registeredStub)});
});
