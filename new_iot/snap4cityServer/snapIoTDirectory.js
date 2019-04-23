
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

var cid = mysql.createConnection({
    host: "localhost",
    port: 3306,
    user: "root",
    password: "!!orion__",
    database: "iotdb"
  });

cid.connect(function(err){
      if(err){ 
              console.log('Error connecting to Db');
              throw err;
             }
     console.log('Connection established');
});

var md5=require('md5');


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

app.post('/bulkload',function(req,res){
    console.log("entrato bulkload");
    //console.log(req);
    var username= req.body.username;
    console.log(username);
    username=md5(username);
    console.log(username);
    
    var sessionToken= req.body.token;
    
    findTotalDevices(username, sessionToken);
    
	res.json({ status:'200', message: 'we are hereee '});
});


//utilizzo configurazione router
app.use('/stubs', router);
//starts server
app.listen(port, function() {
 console.log('api running on port '+ port);
});


router.route('/ngsi')
 .post(function(req, res) {
    console.log("entrato ngsi");

    // console.log(req.body.contextbroker + " " +req.body.ip + " " +req.body.port);
    var args = [];
	if (registeredStub.contains(req.body.contextbroker))  res.json({ message: 'stub already active for ORION context broker ' + req.body.contextbroker});
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
    if (registeredStub.contains(req.body.contextbroker))  res.json({ message: 'stub already active for AMQP context broker ' + req.body.contextbroker});
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
    if (registeredStub.contains(req.body.contextbroker))  res.json({ message: 'stub already active for MQTT context broker ' + req.body.contextbroker});
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


//================================


function findTotalDevices(username, sessionToken){
    var q = "SELECT count(*) AS count FROM temporary_devices WHERE username =?AND deleted IS null";
    console.log("In findTotalDevices");
    cid.query(q,[username], function(err, rows, fields) {
        if (err) {throw err;}

          
        console.log(rows);
        console.log(rows[0].count);
        console.log("Finishing findTotalDevices");  
        doTheBulkLoad(username,sessionToken, rows[0].count);
            });
}
              


function doTheBulkLoad(username,sessionToken, totalDevices){
    
    console.log("In doTheBulkLoad");
    var bulk_offset=10;
    var start_index=1;
    var end_index=bulk_offset;
    if(totalDevices<bulk_offset){
        end_index=totalDevices;
    }
    
    if(end_index<=totalDevices){
        
       insertValidDevicesByPieces(username,sessionToken, start_index,end_index,totalDevices,bulk_offset);
	
    }
}

function insertValidDevicesByPieces(username,sessionToken, start_index,end_index,totalDevices,bulk_offset){
    
    //insert the devices from start_index till end_index
    //delete each device
    
    /*when finished:
    if end_index==totalDevices => end_index=totalDevices+1, and finish
    else:
        sleep for 500ms, then:
            start_index=end_index+1
            if(end_index+bulk_offset<totalDevices){
                    end_index=end_index+bulk_offset;
                    }                
                    else{
                        end_index=totalDevices;    
                    }
             repeat: insertValidDevicesByPieces(username,start_index,end_index,totalDevices,bulk_offset);       
                    */

    console.log("In insertValidDevicesByPieces");
    
    var q="SELECT contextBroker, id, devicetype, model, status, macaddress,frequency,kind, protocol,format,latitude, longitude, visibility, k1, k2,producer, edge_gateway_type, edge_gateway_uri, validity_msg FROM temporary_devices  WHERE username=? AND deleted IS null;"
    
    cid.query(q,[username], function(err, rows, fields) {
        if (err) {throw err};

          //for (var i = 0; i < rows.length; i++) {
        rows.forEach(dev =>  {  
        //console.log(rows[i]);
              console.log("dev outside is "+ dev.id);
              if(dev.status=='valid'){
                 
                  cid.query("SELECT * FROM temporary_event_values WHERE device =? AND cb = ?",[dev.id, dev.contextBroker], function(err_v, rows_v, fields_v) {
                      console.log("dev inside is "+ dev.id);
                      if (err_v) {throw err_v};
                      var dev_attributes=[];
                      for(var j=0; j<rows_v.length; j++){
                          
                          var one_attribute={};
                          
                          one_attribute["value_name"]=rows_v[j].value_name;
						  one_attribute["data_type"]=rows_v[j].data_type;
						  one_attribute["value_type"]=rows_v[j].value_type;
						  one_attribute["editable"]=rows_v[j].editable;
						  one_attribute["value_unit"]=rows_v[j].value_unit;
						  one_attribute["healthiness_criteria"]=rows_v[j].healthiness_criteria;
						  if(one_attribute["healthiness_criteria"]=="refresh_rate") 
								  one_attribute["healthiness_value"]=rows_v[j].value_refresh_rate;
						  if(one_attribute["healthiness_criteria"]=="different_values") 
								  one_attribute["healthiness_value"]=rows_v[j].different_values;
						  if(one_attribute["healthiness_criteria"]=="within_bounds") 
								  one_attribute["healthiness_value"]=rows_v[j].value_bounds;						  
						  dev_attributes.push(one_attribute);
                      }
                      
                      if(dev_attributes.length>0){
                          console.log(dev_attributes.length);
                          insertOneDevice(username,dev,dev_attributes,sessionToken); 
                      } 
              });
              
            }
        });
    });
}

function insertOneDevice(loggedUser,device,dev_attributes, sessionToken){
    
    console.log("In insertOneDevice");
    /*console.log(dev_attributes);
	console.log('loggedUser');
	console.log(loggedUser);
	console.log('sessionToken');
	console.log(sessionToken);
	console.log('device');
	console.log(device);*/
    var data={
			  action: "insert_from_nodeJS",   
			  username: loggedUser,
			  attributes: JSON.stringify(dev_attributes),
			  id: device.id,
			  type: device.devicetype,
			  kind: device.kind,
			  contextbroker: device.contextBroker,
			  protocol: device.protocol,
			  format: device.format,
			  mac: device.macaddress,
			  model: device.model,
			  producer: device.producer,
			  latitude: device.latitude,
			  longitude: device.longitude,
			  visibility: device.visibility,
			  frequency: device.frequency,
			  token : sessionToken,
			  k1 : device.k1,
			  k2 : device.k2,
			  edgegateway_type : device.edge_gateway_type,
			  edgegateway_uri : device.edge_gateway_uri	   
			 };
    
request.post({
  headers: {'content-type' : 'application/x-www-form-urlencoded'},
  url:     'https://www.snap4city.org/iotdirectorytest/api/device.php',
  body:    "data="+data+"&data_from_nodeJs=1"
}, function(error, response, body){
  // console.log(body);
  console.log("ok");
});
	
    //request.post("http://localhost:80/marco0110/api/device_nodejs.php", {json: true, body: data}, function(err, res, body) {
    // request.post("https://www.snap4city.org/iotdirectorytest/api/device.php", {json: true, 'data' : data}, function(err, res, body) {
      // if (!err && res.statusCode === 200) {
          // console.log(res.statusCode);
		  // console.log(res.body);
		  // console.log("ok");
      // }
        // else{
            // console.log("error");
            // console.log('err');
            // console.log(err);
            // /*console.log('res');
            // console.log(res);
            // console.log('body');
            // console.log(body);*/
        // }
  // });
    
    
    
}
