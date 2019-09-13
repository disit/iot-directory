
/* MYSQL setup */

var mysql = require('mysql');
var Promise = require('promise');
var ultimoSensore='sensor_9';
var nValori = 300;

var cid = mysql.createConnection({
  host: "localhost",
  port: 3306,
  user: "root",
  password: "!!orion__",
  database: "iotdb"
 });
 /* This works... */
var devices = [];
var devices2 = [];
var devTime = [];
var values=[];
var values1=[];
var values2=[];
var values3=[];
var values4=[];
var values5=[];
var values6=[];
var values7=[];
var values8=[];
var values9=[];
var values10=[];
var values11=[];
var values12=[];
var values13=[];
var values14=[];
var values15=[];
var values16=[];
var values17=[];
var values18=[];
var values19=[];
var values20=[];

   
/* MQTT setup */ 

const mqtt = require('mqtt');
var request = require('request');

var MQTT_CB             = 'mqttUNIMI';
var MQTT_PROTOCOL       = "mqtt";
var MQTT_TOPIC          = "#";
var MQTT_ADDR           = "mqtt://localhost"; 
var MQTT_PORT           = 1883;//process.argv[4];
var USER = 'b5f0162c90006cc5ba365f8ffe76aee0';
var MODEL = 'custom';
var EDGE_TYPE = process.argv[8];
var EDGE_URI = process.argv[9];

/* global variables */

var registeredDevices = [];

/*functions */

/* return the list of device ids stored in the database */
function getDeviceID(cb) {
   var devices = []; 
   var sql = "(SELECT id FROM devices WHERE contextBroker = '" + cb + "') UNION (SELECT id FROM temporary_devices WHERE contextBroker = '" + cb + "')";
   cid.query(sql, function (err, result, fields) {
     if (err) throw err;
	 // console.log("record selected " + result.length);
     for (i = 0; i < result.length; i++) {
       devices.push(result[i].id);
   }
  // console.log("Getted devices "+JSON.stringify(devices));
  });
 return devices;  
} 

/* store a device in the db*/
function storeDevice(devices, callback){
	console.log("STORE Devices callled");
  var sql ="INSERT INTO `temporary_devices`(`username`,`id`,`model`, `kind`, `protocol`,`format`, `contextBroker`,`edge_gateway_type`,`edge_gateway_uri`) VALUES ? ";   
  // console.log(JSON.stringify(devices));
   cid.query(sql, [devices], function(errSens) {
	//	console.log("insertion devi");
        if (errSens) {throw errSens;}
		callback();
	});	
}

/* extract the attributes from the device value and store them in the db*/
function storeDeviceSchema(values,callback){
//	console.log("values" + JSON.stringify(values));
	var sqlsesc  = "INSERT INTO `temporary_event_values`(`device`, `cb`,`value_name`,`data_type`) VALUES ?";
	
	//console.log("insert values");				
	cid.query(sqlsesc, [values], function(errSSch) {
		callback();
        if (errSSch) {
			
			throw errSSch;}
    });
	
  // var attributes = [];
  //attributes = extractSchema(value); 
	/*var promiseFor = new Promise(function(resolve, reject) {
		var count = 0;
	  for (i=0; i < attributes.length; i++)
	  {	

		att= attributes[i]; 
		//console.log("attributes.length "+deviceID + " attname "+att.name);

	//    var sql = "INSERT INTO `values`(`device`, `cb`,`value_name`, `data_type`, `value_sample`, `order`) VALUES ('"+ deviceID +"','" + cb + "','"  + att.name +"','"+ att.type +"','"+ att.value +"','"+ att.position +"')";
		var sql = "INSERT INTO `temporary_event_values`(`device`, `cb`,`value_name`,`data_type`) VALUES ('"+ deviceID +"','" + cb + "','"  + att.name +"','"  + att.type +"')";

	 //  console.log("SQL: " + sql);
		cid.query(sql, function (err, result, fields) {
			count++;
			//console.log(count + deviceID);
				if (err) throw err;
			if(deviceID.localeCompare(ultimoSensore)==0 && count == nValori)
				resolve();
		});
	  }	
	});
	promiseFor.then(function(res){
		console.timeEnd('retrieval');
	});*/
}


/* determine type of value and extract the contained attributes according to the following formats 
- format A (comma separated pairs):
  attribute:value, ...., attribute:value  
- format B (comma separated basic values):
  value, ..., value (in this case the attribute name would be csv_"pos" where pos is the position) 
- format C (JSON values):
  {attribute:value, ..., attribute:value}
- format D (XML values):
  <record><attribute>value</attribute>...<attribute>value</attribute></record>  
*/
function extractSchema(topic,cb,value)
{
  var attributes = [];
  var f = ""; // identified format
  // console.log("valore processato " + value);
  var first = value.charAt(0);
  switch (first)
  {
    case "{": // Format C
        attributes = parseJSON(value,topic,cb);
		f="json";
	    break;
    case "<": // Format D
        attributes = parseXML(value,topic,cb);
		f="xml";
	    break;
    default: // Format A or B
       attributes = parseCSV(value,topic,cb);
	   f="csv";
  }
  return {"format": f, "attr": attributes};
}

function parseCSV(value,topic,cb)
{
  /*
  format A (comma separated pairs): attribute:value, ..., attribute:value  
  format B (comma separated basic values):  value, ..., value
  */
  var delims = ","; 
  var properties = value.split(delims);
  var attributes = [];
  var format = "A";
  // assume that the format is A and check whether the condition is not satisfied (and in that case I know that I am in format B
  // condition is that ":" do not occur or
  // ":"  occurs once in any prop and not in the first position   

  for (i = 0; i < properties.length; i++)
  {
    var prop = properties[i].trim();
	//console.log("prop "+ prop);
   if (prop.indexOf(":")==-1 || (prop.indexOf(":")>0 && prop.indexOf(":")!=prop.lastIndexOf(":")))
    { 
		var pos = 1;
	 // condition is that ":" do not occur or
      // ":"  occurs once in any prop and not in the first position
        id ="csv_" + pos;
        pos++;
        val =prop;
		//attributes.push({"topic":topic, "cb":cb, "name": id, "type": determineType(val)/*,  "value": val, "position":pos*/});
		attributes.push([topic,cb,id,determineType(val)]);

    }
    else
    {
	  cv= prop.split(":");
	  myid=cv[0].trim();
      val=cv[1].trim();    
   //   attributes.push({"topic":topic, "cb":cb, "name": myid, "type": determineType(val)/*,  "value": val, "position":pos*/});
	attributes.push([topic,cb,myid,determineType(val)]);
    }
  }
  return attributes;
}

/*  this function should be completed */
function parseXML(value,topic,cb)
{
  var attributes = [];
  var pos= 1;
	 // attributes.push({"topic":topic, "cb":cb, "name": "xml_" + pos, "type": determineType(value.trim())/*,  "value": value.trim(),  "position":pos*/});
	 	attributes.push([topic,cb,"xml_" + pos,determineType(value.trim())]);

}

/*  this function should be completed */
function parseJSON(value,topic,cb)
{
 var attributes = [];
  var pos= 1;
	  //attributes.push({"topic":topic, "cb":cb, "name": "json_" + pos, "type": determineType(value.trim()),  "value": value.trim(),  "position":pos});
		 attributes.push([topic,cb,"json_" + pos, determineType(value.trim())]);
  
}


function validateFloat(strFloat) {
    if (/^(\-|\+)?([0-9]+(\.[0-9]+)?|Infinity)$/
      .test(strFloat))
      return true;
  return false;
}

function validateInt(strInt) {
    if (/^(\-|\+)?([0-9]+)$/
      .test(strInt))
      return true;
  return false;
}

// YYYY/MM/DD or MM/DD/YYYY

function validateDate(strDate) {
//  var t = /^(?=.+([\/.-])..\1)(?=.{10}$)(?:(\d{4}).|)(\d\d).(\d\d)(?:.(\d{4})|)$/;
//  strDate.replace(t, function($, _, y, m, d, y2) {
//    $ = new Date(y = y || y2, m, d);
//    t = $.getFullYear() != y || $.getMonth() != m || $.getDate() != d;
//  });
//  return !t;
   var mydate= Date.parse(strDate);
   if (isNaN(mydate)) return false;
   else return true; 
}
function registerDevices(devices){
	for(var i = 0; i < devices.length; i++){
		registeredDevices.push(devices[i].topic);
	}
	
}
  

function toInsert(devices,values){
	var vLen = values.length;
	console.log("vLen " + vLen);
	vLen = vLen/20;
	vLen = parseInt(vLen);
	
	values1 = values.splice(vLen);
	values2 = values1.splice(vLen);
	values3 = values2.splice(vLen);
	values4 = values3.splice(vLen);
	values5 = values4.splice(vLen);
	values6 = values5.splice(vLen);
	values7 = values6.splice(vLen);
	values8 = values7.splice(vLen);
	values9 = values8.splice(vLen);
	values10 = values9.splice(vLen);
	values11 = values10.splice(vLen);
	values12 = values11.splice(vLen);
	values13 = values12.splice(vLen);
	values14 = values13.splice(vLen);
	values15 = values14.splice(vLen);
	values16 = values15.splice(vLen);
	values17 = values16.splice(vLen);
	values18 = values17.splice(vLen);
	values19 = values18.splice(vLen);
	values20 = values19.splice(vLen);

	var promiseDevice = new Promise(function(resolve,reject){
		//console.log("1devices "+ devices.length + " devices2 "+ devices2.length);

		devices2= devices.splice(2500);
		console.log("devices "+ devices.length + " devices2 "+ devices2.length);
		if(devices.length != 0){
		//	console.log("DeviceS "+devices.length);
			storeDevice(devices, (res)=>{
				devices = [];
				console.log("end store devices " + devices2.length);
				registerDevices(devices);

				storeDevice(devices2,(res)=>{
					console.log("devices 2 inserted");
					devices2=[];
					registerDevices(devices2);
					resolve();
				});
			});
		}
	});
				
				
				
		var promiseDevice2 = new Promise(function(resolve,reject){
			promiseDevice.then(function(result){
				console.log("promiseDeviceValues");
				if(values != 0)
				{
				//	console.log("values ");
					storeDeviceSchema(values, (res)=>{
								console.log("values inserted");

						resolve();
					});
				}	
				
			});
				
		});
		var promiseDevice3 = new Promise(function(resolve,reject){
			promiseDevice2.then(function(result){		
					
				if(values2 != 0)
				{
											console.log("values2 ");

					storeDeviceSchema(values2, (res)=>{
												//	console.log("values2 inserted ");

						resolve();
					});	
				}
			});
		});
		var promiseDevice4 = new Promise(function(resolve,reject){
			promiseDevice3.then(function(res){
					
				if(values3 != 0)
				{
												//console.log("values3 ");

					storeDeviceSchema(values3, (res)=>{
													//console.log("values3 inserted ");

						resolve();
					});	
				}					
				
			});
		});
		var promiseDevice5 = new Promise(function(resolve,reject){
			promiseDevice4.then(function(res){
					
				if(values4 != 0)
				{
					storeDeviceSchema(values4, (res)=>{
					resolve();
					});	
				}					
				
			});
		});		
		var promiseDevice6 = new Promise(function(resolve,reject){
			promiseDevice5.then(function(res){
					
				if(values5 != 0)
				{
					storeDeviceSchema(values5, (res)=>{
					resolve();
					});	
				}					
				
			});
		});		
		var promiseDevice7 = new Promise(function(resolve,reject){
			promiseDevice6.then(function(res){
					
				if(values6 != 0)
				{
					storeDeviceSchema(values6, (res)=>{
					resolve();
					});	
				}					
				
			});
		});		
		var promiseDevice8 = new Promise(function(resolve,reject){
			promiseDevice7.then(function(res){
					
				if(values7 != 0)
				{
					storeDeviceSchema(values7, (res)=>{
					resolve();
					});	
				}					
				
			});
		});	
		var promiseDevice9 = new Promise(function(resolve,reject){
			promiseDevice8.then(function(res){
					
				if(values8 != 0)
				{
					storeDeviceSchema(values8, (res)=>{
					resolve();
					});	
				}					
				
			});
		});			
		var promiseDevice10 = new Promise(function(resolve,reject){
			promiseDevice9.then(function(res){
					
				if(values9 != 0)
				{
					storeDeviceSchema(values9, (res)=>{
					resolve();
					});	
				}					
				
			});
		});			
		var promiseDevice11 = new Promise(function(resolve,reject){
			promiseDevice10.then(function(res){
					
				if(values10 != 0)
				{
					storeDeviceSchema(values10, (res)=>{
					resolve();
					});	
				}					
				
			});
		});
		var promiseDevice12 = new Promise(function(resolve,reject){
			promiseDevice11.then(function(res){
					
				if(values11 != 0)
				{
					storeDeviceSchema(values11, (res)=>{
					resolve();
					});	
				}					
				
			});
		});	
		var promiseDevice12 = new Promise(function(resolve,reject){
			promiseDevice11.then(function(res){
					
				if(values11 != 0)
				{
					storeDeviceSchema(values11, (res)=>{
					resolve();
					});	
				}					
				
			});
		});		
		var promiseDevice13 = new Promise(function(resolve,reject){
			promiseDevice12.then(function(res){
					
				if(values12 != 0)
				{
					storeDeviceSchema(values12, (res)=>{
					resolve();
					});	
				}					
				
			});
		});		
		var promiseDevice14 = new Promise(function(resolve,reject){
			promiseDevice13.then(function(res){
					
				if(values13 != 0)
				{
					storeDeviceSchema(values13, (res)=>{
					resolve();
					});	
				}					
				
			});
		});		
		var promiseDevice15 = new Promise(function(resolve,reject){
			promiseDevice14.then(function(res){
					
				if(values14 != 0)
				{
					storeDeviceSchema(values14, (res)=>{
					resolve();
					});	
				}					
				
			});
		});
		var promiseDevice16 = new Promise(function(resolve,reject){
			promiseDevice15.then(function(res){
					
				if(values15 != 0)
				{
					storeDeviceSchema(values15, (res)=>{
					resolve();
					});	
				}					
				
			});
		});		
		var promiseDevice17 = new Promise(function(resolve,reject){
			promiseDevice16.then(function(res){
					
				if(values16 != 0)
				{
					storeDeviceSchema(values16, (res)=>{
					resolve();
					});	
				}					
				
			});
		});		
		var promiseDevice18 = new Promise(function(resolve,reject){
			promiseDevice17.then(function(res){
					
				if(values17 != 0)
				{
					storeDeviceSchema(values17, (res)=>{
					resolve();
					});	
				}					
				
			});
		});		
		var promiseDevice19 = new Promise(function(resolve,reject){
			promiseDevice18.then(function(res){
					
				if(values18 != 0)
				{
					storeDeviceSchema(values18, (res)=>{
					resolve();
					});	
				}					
				
			});
		});		
		var promiseDevice20 = new Promise(function(resolve,reject){
			promiseDevice19.then(function(res){
					
				if(values19 != 0)
				{
					storeDeviceSchema(values19, (res)=>{
					resolve();
					});	
				}					
				
			});
		});				
		promiseDevice20.then(function(res){
			if(values20 != 0)
			{
				storeDeviceSchema(values20, (res)=>{

					console.timeEnd('retrieval');
				});	
			}	
		});

	}

function determineType(value)
{
  var selectorType= "";
  if (validateInt(value)) {selectorType = "integer";}
  else if (value.match("[A-Za-z][A-Za-z0-9]*")) {selectorType = "string";}
  else if (validateFloat(value)) {selectorType = "float";}
  else if (validateDate(value)) {selectorType = "date";
  }else {selectorType = "object";}
  return selectorType;
}
function formatDevice(user,topic,kind,model, protocol, format, cb, edge_gateway_type,edge_gateway_uri){
	return [user,topic,kind,model, protocol, format, cb, edge_gateway_type,edge_gateway_uri];
}
function formatValues(topic, cb,schemaAttr,){
	return [topic, cb,schemaAttr,];
}
/*  MAIN PROGRAM */
/* This is not working as expected */



registeredDevices= getDeviceID(MQTT_CB);
var client  = mqtt.connect(MQTT_ADDR,{clientId: 'bgtestnodejs', protocolId: 'MQIsdp', protocolVersion: 3, connectTimeout:1000, debug:true});

//console.log("before connect "+MQTT_TOPIC + MQTT_ADDR);
client.on('connect', function () {
	//console.log("connected")
    client.subscribe(MQTT_TOPIC);
       // console.log("subscribed");
});

  client.on('message', function (topic, message) {
console.time('retrieval');
	
    // message is Buffer
	//console.log("topic "+ topic);
  //console.log("messaggio " + message.toString());
   
	if (!registeredDevices.includes(topic))
	{
//	console.log("registered ");
	      var deviceSchema= extractSchema(topic,MQTT_CB, message.toString());
		//  devices.push(formatDevice(USER,topic,MODEL,"sensor", MQTT_PROTOCOL,deviceSchema.format,MQTT_CB,EDGE_TYPE, EDGE_URI));
		  //values.concat(deviceSchema.attr);
		//console.log("attr pruna "+JSON.stringify(deviceSchema.attr));
		  //devices.push(formatDevice());
		 devTime.push({createdAt: Date.now(), schema: deviceSchema.attr, user: USER, topic: topic, model: MODEL, kind: "sensor", protocol: MQTT_PROTOCOL, format: deviceSchema.format,cb: MQTT_CB,edgeType: EDGE_TYPE, edgeUri: EDGE_URI});
		// check once per second
		//console.log("devices.length "+ devices.length + " values "+ values.length);
		var cont = 0;
		
		
		
		function checkItems(){
			console.log("devTime "+devTime.length);
			devTime.forEach(function(item){
				if(Date.now() - 1000 > item.createdAt)
				{
					//console.log("date.now");
					cont++;
					devices.push(formatDevice(item.user,item.topic, item.model, item.kind, item.protocol, item.format, item.cb, item.edgeType, item.edgeUri));
					values = values.concat(item.schema);
				}
			});
			console.log("devices length " + devices.length);

			if(devices.length ==5000 ){
				console.log("callback called");
				toInsert(devices,values);
				
				//da fare  -> 		

			}
		}
		var x =  setInterval(checkItems, 5000);

 		 // console.log("devschema "+ JSON.stringify(deviceSchema));
	}
  });
		
		  
  client.on('error', function(){
      console.log("ERROR")
      client.end()
  });
