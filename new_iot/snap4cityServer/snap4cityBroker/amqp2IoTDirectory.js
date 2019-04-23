
ar AMQP_CB             = process.argv[2];
var AMQP_PROTOCOL       = "amqp";
var AMQP_EXCHANGE       = "sensor_rabbit";
var AMQP_DURABLE        = true;
var AMQP_TOPIC          = "#";
var AMQP_ADDR           = "amqp://" + process.argv[3];
var AMQP_PORT           = process.argv[4];

var amqp = require('./amqplib/callback_api');

/* global variables */

var registeredDevices = [];
var orionDevices= [];
var orionDevicesSchema=[];
  
/* MYSQL setup */

var mysql = require('mysql');


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
   

/*functions */

Array.prototype.diff = function(a) {
    return this.filter(function(i) {return a.indexOf(i) < 0;});
};


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
	 attributes.push({"name": prop, "type": obj[prop].type.toLowerCase(), "value": obj[prop].value,  "position":pos});
	 pos++;
	}
  }
  return attributes;
}

/*
function insertOntology(ip,port,protocol,deviceData,deviceSchemaData)
{

//  console.log(deviceSchemaData);
  var msg={};
  msg["id"]=     deviceData[0];
  msg["type"]=   deviceData[2];
  msg["kind"]=   deviceData[1];;
  msg["protocol"]= deviceData[3];
  msg["format"]=   deviceData[4]; 
  msg["broker"]={};
  msg["broker"]["name"]=deviceData[5];
  msg["broker"]["type"]=protocol;
  msg["broker"]["ip"]=ip;
  msg["broker"]["port"]=port;
  msg["broker"]["latitude"]="43.77925";
  msg["broker"]["longitude"]="11.24626";

  var attr = [];
  for (var n=0; n < deviceSchemaData.length; n++)
  {
    if (deviceSchemaData[n][0]==deviceData[0])
    {
      var data= {"name": deviceSchemaData[n][2], "type":  deviceSchemaData[n][3], "order": deviceSchemaData[n][4]};
      attr.push(data);
    }
  }
  msg["attributes"]=attr;
  var msgtxt = JSON.stringify(msg);
  console.log(msgtxt);

  var options = { method: 'POST',
                  url: 'http://www.disit.org/ServiceMap/api/v1/iot/insert',
                  headers: {'Cache-Control': 'no-cache'},
                  body:  msgtxt};

  request(options, function (error, response, body) {
      if (error) throw new Error(error);
      my_body = JSON.parse(options.body);
      
   // console.log(my_body.id); 
   // console.log(my_body.broker.name); 
      var sql1 = "UPDATE devices SET uri =\"" + body + "\" WHERE id = \"" + my_body.id  +"\" AND contextbroker= \"" + my_body.broker.name + "\";";
     console.log(sql1); 
     cid.query(sql1, function (err, result) {
                           console.log(sql1);
                           if (err) {console.log(sql1); throw err;}
                              console.log("record updated");
     }); 
  //   console.log(response);
   });

}  
*/


/*  MAIN PROGRAM */

  

var output="";

var requestLoop = setInterval(function(){


var buffer=[];

registeredSensors= getSensorID(AMQP_CB);

var exec = require('child_process').exec;

exec('rabbitmqctl list_exchanges name type durable', function callback(error, stdout, stderr){
    
    var exch=[];
    var dur=[]
    buffer= stdout.split("\n");
    for (i=0;i<buffer.length;i++)
    {
      if (!buffer[i].trim().startsWith("Timeout") && !buffer[i].trim().startsWith("Listing"))
      {
         cont = buffer[i].trim().split("\t");
         if (cont[0]!= "" && !cont[0].startsWith("amq") & cont[1]=="fanout"  && cont[2]!="")
         {
                console.log(cont[0] +"..."+cont[1] +"..."+cont[2]);
	        exch.push(cont[0]);
	        dur[cont[0]]=cont[2];
         }
      } 
    }
	var newDevices=exch.diff(registeredSensors);
	console.log("nuovi" + newDevices);
    for (var k=0; k < newDevices.length;k++)
    {
      var mych=newDevices[k];
      var mydur= dur[mych];
      console.log("prima");
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
	  
      createChannel(conn, mych, mydur);
      console.log("dopo");
     
   }

});

}, 10000);

