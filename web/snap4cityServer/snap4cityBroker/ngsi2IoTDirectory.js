
var ORION_CB       = process.argv[2]; 
var ORION_PROTOCOL = "ngsi";
var ORION_ADDR = process.argv[3];
var ORION_PORT = process.argv[4];


/* global variables */

var registeredDevices = [];
var orionDevices= [];
var orionDevicesSchema=[];
  
/* MYSQL setup */

var mysql = require('mysql');
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
   

   
/* ORION setup */ 

var http = require("http");
var request = require('request');


/*functions */

Array.prototype.diff = function(a) {
    return this.filter(function(i) {return a.indexOf(i) < 0;});
};


function insertDevices(cid, se)
{
    var sqlse  = "INSERT INTO  `devices`(`id`, `devicetype`,`protocol`, `format`, `contextBroker`, `latitude`, `longitude`) VALUES ?";
                          
	cid.query(sqlse, [se], function(errSens) {
                              if (errSens) {console.log("device" + se); throw errSens;}
                              console.log("fatto device");
                          });	
}


function insertValues(cid, sesc)
{
	var sqlsesc  = "INSERT INTO `event_values`(`device`, `cb`,`value_name`, `data_type`,  `order`) VALUES ?";
						  
	cid.query(sqlsesc, [sesc], function(errSSch) {
                              if (errSSch) {console.log("attr" + sesc);throw errSSch;}
                              console.log("fatto schemi");
                         });
}


/* store a device in the db*/
function storeDevice(deviceID,type, protocol, format, cb, latitude,longitude){
  return [deviceID,type,protocol,format,cb,latitude,longitude];
}

/* extract the attributes from the device value and store them in the db*/
function storeDeviceSchema(cb, attributes, deviceID){
  var arr =[];
  var longitude="";
  var latitude="";
  for (i=0; i < attributes.length; i++)
  {	
    att= attributes[i];
    if (att.name=="latitude" || att.name=="geolocalization_lat") latitude=att.value;
    if (att.name=="longitude" || att.name=="geolocalization_lon") longitude=att.value;
  	
     // arr.push([deviceID,cb,att.name,att.type,att.value,att.position]);
     arr.push([deviceID,cb,att.name,att.type,att.position]);

  }
  return {"arr": arr, "latitude": latitude, "longitude": longitude};
}

/* extract the device schema from the NGSI-9/10 representation adopted by Orion  
*/
function extractSchema(value)
{
  var attributes = [];
  var f = ""; // identified format
  // console.log("valore processato " + value);
  attributes = parseOrionJSON(value);
  return {"format": "json", "attr": attributes};
}

function isTest(deviceSchema)
{
  if (deviceSchema.attr.length==1 && deviceSchema.attr[0].name=="test")
  return true;
  else return false;
}


function parseOrionJSON(obj)
{
  var attributes = [];
  // {"id":"ARDUINO_ST_4201_1516802097","type":"Temperature","Temperature":{"type":"float","value":"20.0","metadata":{}},"geolocalization":{"type":"string","value":"45.453701,9.214914","metadata":{}},"measure_units":{"type":"string","value":"Celsius","metadata":{}},"timestamp":{"type":"integer","value":"1516802097","metadata":{}}}
  var pos=1;
  for (var prop in obj)
  {
    if (prop != "id" && prop != "type")
	{
         // console.log(prop);
         // console.log(obj[prop].type);
         // console.log(obj[prop].value); 
	 attributes.push({"name": prop, "type": obj[prop].type.toLowerCase(), // "value": obj[prop].value, 
	 "position":pos});
	 pos++;
	}
  }
  return attributes;
}


/*  MAIN PROGRAM */

  var options = {
    host: ORION_ADDR,
    port: ORION_PORT,
    path: '/v2/entities?limit=10',
    method: 'GET'
  };

var output="";

var requestLoop = setInterval(function(){

  var req = http.request(options, function(res) {
     console.log('STATUS: ' + res.statusCode);
   //  console.log('HEADERS: ' + JSON.stringify(res.headers));
     res.setEncoding('utf8');
   //  console.log('options ' + JSON.stringify(options));

     res.on('data', function (chunk) {
            output += chunk;
            console.log("pezzo");
      });

     res.on('end', function () {
                 console.log('BODY: finito');
                   orionDevices= [];
                   orionDevicesType = [];
                   orionDevicesSchema= [];

		 var obj = JSON.parse(output);
                 output="";
                 // console.log(obj); 
                 if (obj instanceof Array)
                 {
					for (i=0; i < obj.length; i++) {
                              orionDevices.push(obj[i].id);
		                      orionDevicesSchema[obj[i].id]= obj[i];
		                      orionDevicesType[obj[i].id]= obj[i].type;
                             
					}
				 }
                 else
                 { 
                   orionDevices.push(obj.id);
                   orionDevicesSchema[obj.id]= obj;
                   orionDevicesType[obj.id]= obj.type;
                 }
                // console.log(orionDevicesSchema);
  
               var sql = "SELECT id FROM devices WHERE contextBroker = '" + ORION_CB + "'";

              cid.query(sql, function (err, result, fields) {
                           // console.log(sql);
                           if (err) {console.log(sql); throw err;}
	                      console.log("record selected " + result.length);
                          for (i = 0; i < result.length; i++) {
                              registeredDevices.push(result[i].id);
                              }
  
                          var newDevices=orionDevices.diff(registeredDevices);
                                // console.log("device nuovi" + newDevices);
                          console.log("device nuovi" + newDevices.length);
                          var se = [];
                          var sesc = [];
                          for (var i=0; i < newDevices.length; i++)
                          {
                             var topic= newDevices[i];
                             
	                         var deviceSchema=extractSchema(orionDevicesSchema[topic]);
	                         if (!isTest(deviceSchema))
                                 {
								   /*extract latitude and longitude from devices*/
	   		                       obj1=storeDeviceSchema(ORION_CB, deviceSchema.attr, topic);
				                   // console.log(JSON.stringify(obj1));
                                   // process.exit();				   
                                    se.push(storeDevice(topic,orionDevicesType[topic], ORION_PROTOCOL, deviceSchema.format, ORION_CB,obj1.latitude,obj1.longitude));
								   
	                               sesc= sesc.concat(obj1.arr);
                                 }
 //                                console.log(deviceSchema.attr);
                          }  
//                        // console.log(se);
                          //  console.log(JSON.stringify(sesc));
                          //  process.exit;
                         if ( se.length!=0)
                         {
                          
						  var sqlse  = "INSERT INTO  `devices`(`id`, `devicetype`,`protocol`, `format`, `contextBroker`, `latitude`, `longitude`) VALUES ?";
                          // var sqlsesc  = "INSERT INTO `values`(`device`, `cb`,`value_name`, `data_type`, `value_sample`, `order`) VALUES ?";
						  var sqlsesc  = "INSERT INTO `values`(`device`, `cb`,`value_name`, `data_type`,  `order`) VALUES ?";


                              var promise1 = new Promise(function(resolve, reject) {
                              insertDevices(cid, se); });
			      promise1.then(insertValues(cid, sesc));
						  
						/*  cid.query(sqlse, [se], function(errSens) {
                              if (errSens) {console.log("device" + se); throw errSens;}
                              console.log("fatto device");
                          }).then(function(){
                          cid.query(sqlsesc, [sesc], function(errSSch) {
                              if (errSSch) {console.log("attr" + sesc);throw errSSch;}
                              console.log("fatto schemi");
                         });}); */ 
	                } 
                          }); //query
}); // res.on 

	 });
 

  req.on('error', function(e) {
           console.log('problem with request: ' + e.message);
  });

  req.end(); 

}, 10000);


