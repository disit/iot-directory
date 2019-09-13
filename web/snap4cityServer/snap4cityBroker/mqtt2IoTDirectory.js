
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
 
/* MQTT setup */ 


const mqtt = require('mqtt');
var request = require('request');

var MQTT_CB             = process.argv[2];
var MQTT_PROTOCOL       = "mqtt";
var MQTT_TOPIC          = "#";
var MQTT_ADDR           = "mqtt://" + process.argv[3]; 
var MQTT_PORT           = process.argv[4];




/* global variables */

var registeredDevices = [];

/*functions */

/* return the list of device ids stored in the database */
function getDeviceID(cb) {
   var devices = []; 
   var sql = "SELECT  id FROM devices WHERE contextBroker = '" + cb + "'";
   cid.query(sql, function (err, result, fields) {
     if (err) throw err;
	 // console.log("record selected " + result.length);
     for (i = 0; i < result.length; i++) {
       devices.push(result[i].id);
   }
  });
 return devices;  
} 

/* store a device in the db*/
function storeDevice(deviceID, kind, protocol, format, cb){
  var sql ="INSERT INTO  `devices`(`id`, `kind`, `protocol`, `format`, `contextBroker`) VALUES ('"+ deviceID +"','"+ kind +"','"+ protocol +"','"+ format +"','"+ cb +"')";
  console.log("SQL " + sql);
  cid.query(sql, function (err, result, fields) {
            if (err) throw err;
			console.log("1 record inserted");
  });
}

/* extract the attributes from the device value and store them in the db*/
function storeDeviceSchema(cb,attributes, deviceID){
  // var attributes = [];
  //attributes = extractSchema(value); 
	
  for (i=0; i < attributes.length; i++)
  {	
    att= attributes[i]; 
//    var sql = "INSERT INTO `values`(`device`, `cb`,`value_name`, `data_type`, `value_sample`, `order`) VALUES ('"+ deviceID +"','" + cb + "','"  + att.name +"','"+ att.type +"','"+ att.value +"','"+ att.position +"')";
    var sql = "INSERT INTO `values`(`device`, `cb`,`value_name`, `data_type`, `value_sample`, `order`) VALUES ('"+ deviceID +"','" + cb + "','"  + att.name +"','"+ att.type + "','"+ att.position +"')";

    console.log("SQL: " + sql);
    cid.query(sql, function (err, result, fields) {
            if (err) throw err;
			console.log("1 record inserted");
        });
  }		
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
function extractSchema(value)
{
  var attributes = [];
  var f = ""; // identified format
  // console.log("valore processato " + value);
  var first = value.charAt(0);
  switch (first)
  {
    case "{": // Format C
        attributes = parseJSON(value);
		f="json";
	    break;
    case "<": // Format D
        attributes = parseXML(value);
		f="xml";
	    break;
    default: // Format A or B
       attributes = parseCSV(value);
	   f="csv";
  }
  return {"format": f, "attr": attributes};
}

function parseCSV(value)
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
    if (prop.indexOf(":")==-1 || (prop.indexOf(":")>0 && prop.indexOf(":")!=prop.lastIndexOf(":")))
    { // condition is that ":" do not occur or
      // ":"  occurs once in any prop and not in the first position
        id ="csv_" + pos;
        pos++;
        val =prop;
		attributes.push({"name": id, "type": determineType(val), // "value": val, 
		"position":pos});
    }
    else
    {
	  cv= prop.split(":");
	  myid=cv[0].trim();
      val=cv[1].trim();    
      attributes.push({"name": myid, "type": determineType(val), // "value": val, 
	  "position":pos});
    }
  }
  return attributes;
}

/*  this function should be completed */
function parseXML(value)
{
  var attributes = [];
  var pos= 1;
	  attributes.push({"name": "xml_" + pos, "type": determineType(value.trim()), // "value": value.trim(),
	  "position":pos});
}

/*  this function should be completed */
function parseJSON(value)
{
 var attributes = [];
  var pos= 1;
	  attributes.push({"name": "json_" + pos, "type": determineType(value.trim()), // "value": value.trim(),
	  "position":pos});
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


/*  MAIN PROGRAM */

  registeredDevices= getDeviceID(MQTT_CB);

  var client  = mqtt.connect(MQTT_ADDR,{connectTimeout:1000, debug:true});

  console.log("registrati ",registeredDevices.length); 

client.on('connect', function () {
        console.log("strying toubscribed");
    client.subscribe(MQTT_TOPIC);
        console.log("subscribed");
});

  client.on('message', function (topic, message) {
    // message is Buffer
     console.log("messaggio " + message.toString());
     console.log("topic  " + topic);
	if (!registeredDevices.includes(topic))
	{
	      var deviceSchema= extractSchema(message.toString());
		  var promise1 = new Promise(function(resolve, reject) {
              storeDevice(topic, "sensor", MQTT_PROTOCOL, deviceSchema.format, MQTT_CB); });
          promise1.then(storeDeviceSchema(MQTT_CB,deviceSchema.attr, topic));
          registeredDevices.push(topic);
          console.log(registeredDevices);
	}

  });

  client.on('error', function(){
      console.log("ERROR")
      client.end()
  });
