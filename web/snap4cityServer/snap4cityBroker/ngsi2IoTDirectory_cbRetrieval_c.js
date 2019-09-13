var ORION_CB = process.argv[2]; 
var ORION_PROTOCOL = "ngsi";
var ORION_ADDR = process.argv[3];
var ORION_PORT = process.argv[4];
var USER = process.argv[5];
var ACCESS_LINK = process.argv[6];
var MODEL = process.argv[7];
var ORGANIZATION=process.argv[10];
var APIKEY = process.argv[11];

//Static values
var MAC = "3D:F2:C9:A6:B3:4F";
var KIND = "sensor";
var FREQUENCY = 10;
var flagAntwerp = true;
/* global variables */

var registeredDevices = [];
var orionDevices= [];
var orionDevicesSchema=[];
var modelsdata=[];
/* MYSQL setup */

var mysql = require('mysql');
var Promise = require('promise');
var XMLHttpRequest = require("xmlhttprequest").XMLHttpRequest;

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

Array.prototype.diff = function( arr ) {
	console.log ("diff");
  return this.filter( function( val ) {
    return arr.indexOf( val ) < 0;
  });
};

var gb_value_units=[];
var gb_data_type=[];

function getModels(cid)
{
	console.log("getModels");
    var models  = "SELECT * FROM model";

	 cid.query(models, function (err, result, fields) {
						
                           // console.log(sql);
						  if (err) {console.log("sql "+sql); throw err;}
                          for (i = 0; i < result.length; i++) {
                              modelsdata.push(result[i]);
                          }
						  
             }); //query

	//ssssconsole.log("gb_value_units "+gb_value_units);
}

function getParam(cid)
{
	console.log("getParam");
    var valueUnit  = "SELECT value_type FROM value_types ORDER BY value_type";

	 cid.query(valueUnit, function (err, result, fields) {
						
                           // console.log(sql);
						  if (err) {console.log("sql "+sql); throw err;}
                          for (i = 0; i < result.length; i++) {
                              gb_value_units.push(result[i].value_type);
                          }
						  
             }); //query
	 var dataType= "SELECT data_type FROM data_types order by data_type";
	 cid.query(dataType, function (err, result, fields) {
					
					   // console.log(sql);
					  if (err) {console.log("sql "+sql); throw err;}
					  for (i = 0; i < result.length; i++) {
						  gb_data_type.push(result[i].data_type);
					  }
					  
		 }); //query
	//ssssconsole.log("gb_value_units "+gb_value_units);

}
function insertDevices(cid, se)
{
	console.log("insertDevices");
    var sqlse  = "INSERT INTO  `temporary_devices`(`username`,`id`,`model`, `kind`,`devicetype`,`protocol`,`frequency`,`format`,`contextBroker`,`latitude`,`longitude`,macaddress,`status`,`validity_msg`,`should_be_registered`,`organization`) VALUES ?";
                          
	cid.query(sqlse, [se], function(errSens) {
                              if (errSens) {console.log("device" + se); throw errSens;}
                              console.log("fatto");
                          });	
}


function insertValues(cid, sesc)
{
	//console.log("insertValues");
	var sqlsesc  = "INSERT INTO `temporary_event_values`(`device`, `cb`,`value_name`, `data_type`,`value_type`,`value_unit`,`healthiness_criteria`,`value_refresh_rate`,`old_value_name`) VALUES ?";
					
	/*console.log("sql sesc "+sqlsesc);
	console.log("sesc "+ JSON.stringify(sesc));*/
	cid.query(sqlsesc, [sesc], function(errSSch) {
                              if (errSSch) {throw errSSch;}

                         });

}


/* store a device in the db*/
function storeDevice(user,deviceID,model,kind,type, protocol,frequency, format, cb, latitude,longitude,macaddress,status,validity_msg,shouldberegistered,organization){
  return [user,deviceID,model,kind,type,protocol,frequency,format,cb,latitude,longitude,macaddress,status, validity_msg,shouldberegistered,organization];
}

/* extract the attributes from the device value and store them in the db*/
function storeDeviceSchema(cb, attributes, deviceSchema,deviceID){
  var arr =[];
  var longitude="";
  var latitude="";
  var ob = deviceSchema[deviceID];
  var value_type= ""; 
	
  if(cb == 'Antwerp' && flagAntwerp){	
	for (i=0; i < ob.length; i++){
	 att= ob[i];
	 var split = (att.id).split(".");
	
	if(gb_value_units.indexOf(split[1])>=0){
		value_type = split[1];
	}
	else{
	 value_type = getValueType(att.id,att.type,att.value);
	 }
	 var data_type = getDataType(att.id,att.type,att.value);
	 var value_unit = getValueUnit(value_type);
	 console.log("push "+deviceID + att.id + "value_type "+ value_type);
	 if(value_type === null || value_type === undefined || value_type == '' || value_type == ' '){
		value_type = 'rain';
	 }
	 if(data_type === null || data_type === undefined){
		data_type = 'float';
	 }
	 
	 latitude = "51.219890";
	 longitude = "4.4034600";
	 arr.push([deviceID,cb,att.id,data_type,value_type,value_unit,"refresh_rate",300,att.id]);		
	}
  }
  else{
	  //console.log("storeDeviceSchema" + JSON.stringify(ob));
	  for (i=0; i < attributes.length; i++)
	  {	
		//console.log("for "+JSON.stringify(attributes));
		att= attributes[i];
		if (att.name=="latitude"){
			latitude = deviceSchema[deviceID].latitude.value;
		}
		else if(att.name=="geolocalization_lat"){
			latitude=deviceSchema[deviceID].geolocalization_lat.value;
		}	
		//	latitude=deviceSchema[deviceID].latitude.value;
		else if (att.name=="longitude"){
			longitude=deviceSchema[deviceID].longitude.value;
		}
		else if(att.name=="geolocalization_lon"){
			longitude = deviceSchema[deviceID].geolocalization_lon.value;
		}
		//	longitude=deviceSchema[deviceID].longitude.value;
		else if (att.name=="location" && flagAntwerp){
			longitude = deviceSchema[deviceID].location.value.coordinates[0];
			latitude = deviceSchema[deviceID].location.value.coordinates[1];
		}
		else if(att.name=="location"){
			longitude = deviceSchema[deviceID].location.value.coordinates[1].toFixed(4);
			latitude = deviceSchema[deviceID].location.value.coordinates[0].toFixed(4);		
		}


		// arr.push([deviceID,cb,att.name,att.type,att.value,att.position]);
		 value_type = getValueType(att.name,att.type,att.value);
		 var data_type = getDataType(att.name,att.type,att.value);
		 var value_unit = getValueUnit(value_type);
		 arr.push([deviceID,cb,att.name,data_type,value_type,value_unit,"refresh_rate",300,att.name]);
	  }
	}
  //console.log("latitude: "+latitude + " longitude: "+ longitude);
  return {"arr": arr, "latitude": latitude, "longitude": longitude};
}

/* extract the device schema from the NGSI-9/10 representation adopted by Orion  
*/
function extractSchema(value)
{
	var attributes = [];
	var f = ""; // identified format
	// console.log("valore processato " + value);
	
	if(ORION_CB == 'Antwerp' && flagAntwerp ){
	console.log("extract antw");
		attributes= parseAntwerpJSON(value);
	}
	else{
	console.log("extractorion");
		attributes = parseOrionJSON(value);
	}
	// console.log("attributes "+JSON.stringify(attributes));
	return {"format": "json", "attr": attributes};
}

function isTest(deviceSchema)
{
  if (deviceSchema.attr.length==1 && deviceSchema.attr[0].name=="test")
  return true;
  else return false;
}
function parseAntwerpJSON(obj)
{
  var attributes = [];
  // {"id":"ARDUINO_ST_4201_1516802097","type":"Temperature","Temperature":{"type":"float","value":"20.0","metadata":{}},"geolocalization":{"type":"string","value":"45.453701,9.214914","metadata":{}},"measure_units":{"type":"string","value":"Celsius","metadata":{}},"timestamp":{"type":"integer","value":"1516802097","metadata":{}}}
  var pos=1;
  for (var prop in obj)
  {
          //console.log(obj[prop].type);
         // console.log(obj[prop].value); 
	if(obj[prop].type == null){
		 attributes.push({"name": prop, "type": obj[prop].type, /* uncommented by sara 2711*/"value": obj[prop].value, 
	 "position":pos});
	}
	else{
	 attributes.push({"name": prop, "type": obj[prop].type.toLowerCase(), /* uncommented by sara 2711*/"value": obj[prop].value, 
	 "position":pos});
	 }
	 pos++;
	}
  return attributes;
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
	//console.log("name:"+prop+"; type:"+obj[prop].type.toLowerCase()+"; value: "+obj[prop].value + ";position: "+pos);
	 attributes.push({"name": prop, "type": obj[prop].type.toLowerCase(), "value": obj[prop].value, 
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
		path: '/v2/entities?limit=1000',
		method: 'GET'
};	

var output="";
console.log("MODEL "+ORION_CB);
if(ORION_ADDR == "0.0.0.0"){
	if(ORION_CB == "Antwerp"){	
		if(APIKEY !== null || APIKEY !== undefined){
		console.log("apikeynot null" + ORION_CB);
	
		var link3 = ACCESS_LINK+'/api/v1/scopes/cot.smartzone/things/davis.davis.weather.1';
		var xhttp = new XMLHttpRequest();  
		xhttp.onreadystatechange = function() {
		
			if (this.readyState == 4 && this.status == 200) {
				//console.log(" response ant "+this.responseText);
				orionDevices= [];
				orionDevicesType = [];
				orionDevicesSchema= [];
				 
				   var obj = JSON.parse(this.responseText);
					 output="";
					//console.log("obj antwerp "+JSON.stringify(obj));
					 if (obj instanceof Array)
					 {
						//console.log("length obj "+obj.length);
						//console.log("obj "+obj);
						for (i=0; i < obj.length; i++) {
						 orionDevices.push(obj[i].id);
						 orionDevicesSchema[obj[i].id]= obj[i].allMetrics;
						 //console.log("orion schema "+JSON.stringify(orionDevicesSchema[obj[i].id]));
						 orionDevicesType[obj[i].id]= obj[i].type;
						 					   console.log("orionDevices id "+obj.id);
					//   console.log("orionDevicesSchema id "+obj);
					  // console.log("orionDevicesType id "+obj.type);
						}
					}
					 else
					 { 
					   orionDevices.push(obj.id);
					   orionDevicesSchema[obj.id]= obj.declaredMetrics;
					   orionDevicesType[obj.id]= "weather";/*obj.type;*/
					   
					  /* console.log("orionDevices id "+obj.id);
					   console.log("orionDevicesSchema id "+obj);
					   console.log("orionDevicesType id "+obj.type);*/

					 }

				   var sql = "(SELECT id FROM temporary_devices WHERE contextBroker = '" + ORION_CB + "') UNION (SELECT id FROM devices WHERE contextBroker = '" + ORION_CB + "')";
					//Devo fare diff anche con device?
				 
				 if(typeof gb_value_units === undefined || gb_value_units.length <=0 )
					getParam(cid);

				if(typeof modelsdata === undefined || MODEL.localeCompare("custom")==0|| modelsdata.length <=0 )
					getModels(cid);
				
				  cid.query(sql, function (err, result, fields) {
							   // console.log(sql);
							  if (err) {console.log("sql "+sql); throw err;}
							 // console.log("record selected " + result.length);
							  for (i = 0; i < result.length; i++) {
								//  console.log("result i"+JSON.stringify(result[i]));
								  registeredDevices.push(result[i].id);
							  }
							  var ori =[];
							 console.log("orion devices " + JSON.stringify(orionDevices) + " registeredDevices " + JSON.stringify(registeredDevices));
							  
							  var newDevices=orionDevices.diff(registeredDevices);
									// console.log("device nuovi" + newDevices);
							  
							//  console.log("device nuovi" + newDevices.length + " dev "+ JSON.stringify(newDevices));		  
							  
							  var se = [];
							  var sesc = [];
							  for (var i=0; i < newDevices.length; i++)
							  {
								  
								 var topic= newDevices[i];
								 
								console.log("orionDevicesSchema "+topic +" "+ JSON.stringify(orionDevicesSchema[topic]));
								flagAntwerp = true;
								 var deviceSchema=extractSchema(orionDevicesSchema[topic]);
								 if (!isTest(deviceSchema))
									 {
								//extract latitude and longitude from devices

								       obj1=storeDeviceSchema(ORION_CB, deviceSchema.attr, orionDevicesSchema, topic);
									   // process.exit();	
									   var deviceVal = {"data_type": obj1.arr.data_type, "value_type": obj1.arr[4], "editable":"false", "value_unit":obj1.arr[5],"healthiness_criteria":obj1.arr[6],"healthiness_value":obj1.arr[7]};

										var toVerify ={"name": topic,"username": USER,"contextBroker": ORION_CB, "id": topic, "model": MODEL, "devicetype":orionDevicesType[topic], "protocol":ORION_PROTOCOL, "format":deviceSchema.format, "frequency": FREQUENCY, "kind":KIND,"latitude":obj1.latitude, "longitude":obj1.longitude,"macaddress":MAC,"deviceValues":deviceVal};
										
										var verify = verifyDevice(toVerify);
										var validity = "invalid";
										if(verify.isvalid)
											validity= "valid";
										
										se.push(storeDevice(USER,topic,MODEL,KIND,orionDevicesType[topic],
										ORION_PROTOCOL,FREQUENCY, deviceSchema.format, ORION_CB,obj1.latitude,
										obj1.longitude,MAC,validity,verify.message,"no",ORGANIZATION));
										//console.log("Se "+ JSON.stringify(se));							   

									   // var value_type = getValueType(obj1.arr);
									   sesc= sesc.concat(obj1.arr);
									 }
	 //                                console.log(deviceSchema.attr);
							  }  
	//                        // console.log(se);
							  //  console.log(JSON.stringify(sesc));
							  //  process.exit;
							 if ( se.length!=0)
							 {
						  var sqlse  = "INSERT INTO  `temporary_devices`(`username`,`id`, `model`,`kind`,`devicetype`,`protocol`, `format`,`contextBroker`,`latitude`,`longitude`,macaddress,`status`,`validity_msg`,`should_be_registered`,`organization`) VALUES ?";
							  var sqlsesc  = "INSERT INTO `temporary_event_values`(`device`, `cb`,`value_name`, `data_type`,`value_type`,`value_unit`,`healthiness_criteria`,`value_refresh_rate`,`old_value_name`) VALUES ?";


					  var promise1 = new Promise(function(resolve, reject) {
						  insertDevices(cid, se);
						  });
					  promise1.then(insertValues(cid, sesc));
							  
							
						}						
				 }); //query
		  console.log("fatto antwerp1");

		 	
		var link4 = 'https://ext-api-gw-p.antwerpen.be/digipolis/aovmma/v1/entities';
		var xhttp2 = new XMLHttpRequest();  
		xhttp2.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
				//console.log(" response ant "+this.responseText);
				orionDevices2= [];
				orionDevicesType2 = [];
				orionDevicesSchema2 = [];
				 
				   var obj = JSON.parse(this.responseText);
					 output="";
					//console.log("obj antwerp "+JSON.stringify(obj));
					if (obj instanceof Array)
                 {
					//console.log("length obj "+obj.length);
					for (i=0; i < obj.length; i++) {
					 orionDevices2.push(obj[i].id);
					 orionDevicesSchema2[obj[i].id]= obj[i];
					 //console.log("orion schema "+JSON.stringify(orionDevicesSchema2[obj[i].id]));
					 orionDevicesType2[obj[i].id]= obj[i].type;
					}
				}
                 else
                 { 
                   orionDevices2.push(obj.id);
                   orionDevicesSchema2[obj.id]= obj;
                   orionDevicesType[obj.id]= obj.type;
                 }

               var sql = "(SELECT id FROM temporary_devices WHERE contextBroker = '" + ORION_CB + "') UNION (SELECT id FROM devices WHERE contextBroker = '" + ORION_CB + "')";
				console.log("SQL STRING "+ORION_CB);
				//Devo fare diff anche con device?
			 
			 if(typeof gb_value_units === undefined || gb_value_units.length <=0 )
				getParam(cid);

			if(typeof modelsdata === undefined || MODEL.localeCompare("custom")==0|| modelsdata.length <=0 )
				getModels(cid);
			
			  cid.query(sql, function (err, result, fields) {
                           // console.log(sql);
						  if (err) {console.log("sql "+sql); throw err;}
	                     // console.log("record selected " + result.length);
						 console.log("inside Query "+ORION_CB);
                          for (i = 0; i < result.length; i++) {
							//  console.log("result i"+JSON.stringify(result[i]));
                              registeredDevices.push(result[i].id);
                          }
						  var ori =[];
						 console.log("orion devices " + JSON.stringify(orionDevices2) + " registeredDevices " + JSON.stringify(registeredDevices));
						  
                          var newDevices=orionDevices2.diff(registeredDevices);
                                // console.log("device nuovi" + newDevices);
                          
						//  console.log("device nuovi" + newDevices.length + " dev "+ JSON.stringify(newDevices));		  
						  
						  var se = [];
                          var sesc = [];
                          for (var i=0; i < newDevices.length; i++)
                          {
							  
                             var topic2= newDevices[i];
                             
							//console.log("orionDevicesSchema2 "+topic +" "+ JSON.stringify(orionDevicesSchema2[topic]));
							flagAntwerp = false;
	                         var deviceSchema2=extractSchema(orionDevicesSchema2[topic2]);

	                         if (!isTest(deviceSchema2))
                                 {
							 /*extract latitude and longitude from devices*/
	   		                       obj1=storeDeviceSchema(ORION_CB, deviceSchema2.attr, orionDevicesSchema2, topic2);
                                   // process.exit();	
								   var deviceVal = {"data_type": obj1.arr.data_type, "value_type": obj1.arr[4], "editable":"false", "value_unit":obj1.arr[5],"healthiness_criteria":obj1.arr[6],"healthiness_value":obj1.arr[7]};

									var toVerify ={"name": topic2,"username": USER,"contextBroker": ORION_CB, "id": topic2, "model": MODEL, "devicetype":orionDevicesType[topic2], "protocol":ORION_PROTOCOL, "format":deviceSchema2.format, "frequency": FREQUENCY, "kind":KIND,"latitude":obj1.latitude, "longitude":obj1.longitude,"macaddress":MAC,"deviceValues":deviceVal};
									
									var verify = verifyDevice(toVerify);
									var validity = "invalid";
									if(verify.isvalid)
										validity= "valid";
										
									console.log("ORION DEVICE TYPE "+orionDevicesType2[topic2]);
									se.push(storeDevice(USER,topic2,MODEL,KIND,orionDevicesType2[topic2],
									ORION_PROTOCOL,FREQUENCY, deviceSchema2.format, ORION_CB,obj1.latitude,
									obj1.longitude,MAC,validity,verify.message,"no",ORGANIZATION));
									//console.log("Se "+ JSON.stringify(se));							   

								   // var value_type = getValueType(obj1.arr);
	                               sesc= sesc.concat(obj1.arr);
                                 }
 //                                console.log(deviceSchema.attr);
                          }  
//                        // console.log(se);
                          //  console.log(JSON.stringify(sesc));
                          //  process.exit;
                         if ( se.length!=0)
                         {
					  var sqlse  = "INSERT INTO  `temporary_devices`(`username`,`id`, `model`,`kind`,`devicetype`,`protocol`, `format`,`contextBroker`,`latitude`,`longitude`,macaddress,`status`,`validity_msg`,`should_be_registered`,`organization`) VALUES ?";
						  var sqlsesc  = "INSERT INTO `temporary_event_values`(`device`, `cb`,`value_name`, `data_type`,`value_type`,`value_unit`,`healthiness_criteria`,`value_refresh_rate`,`old_value_name`) VALUES ?";


                  var promise1 = new Promise(function(resolve, reject) {
                      insertDevices(cid, se);
					  });
			      promise1.then(insertValues(cid, sesc));
							
						}						
				 }); //query
		  console.log("fatto antwerp1");
			}//end onreadystatechange 2
		  };//end onreadystatechange 2 
		  
			  xhttp2.open("GET", link4, true);
			  xhttp2.setRequestHeader("apikey",'34a11f81-0a37-4c46-bd6b-3cf07cc81900');
			  xhttp2.send();

		  
		  
		  
		 }//end readystate == 4
		};//end onreadystatechange
		  xhttp.open("GET", link3, true);
		 
		  xhttp.setRequestHeader("apikey",APIKEY);
		  xhttp.send();

		  
		}//end if APIKEY != NULL
	}//end if ORION_CB = antwerp	
	else if(ORION_CB == "orionFinland"){

	console.log("attributes  cb:"+ ORION_CB + "model "+ MODEL + " protocol "+ ORION_PROTOCOL + " port "+ ORION_PORT+ " USER "+ USER + " Access LINK "+ ACCESS_LINK+ " APikey "+APIKEY);
	console.log("zero1 "+ ACCESS_LINK);
	var link = ACCESS_LINK+'/v2/entities?limit=10';
	var req = http.get(link, function(res) {
     console.log('STATUS: ' + res.statusCode);
	 res.on('data', function (chunk) {
            output += chunk;
            console.log("pezzo ");
      });

     res.on('end', function () {
	 
                 console.log('BODY: finito ' +ORION_CB);
					
				orionDevices= [];
                 orionDevicesType = [];
                 orionDevicesSchema= [];

		 var obj = JSON.parse(output);
                 output="";
			//	console.log("obj "+JSON.stringify(obj));
                 if (obj instanceof Array)
                 {
					//console.log("length obj "+obj.length);
					for (i=0; i < obj.length; i++) {
					 orionDevices.push(obj[i].id);
					 orionDevicesSchema[obj[i].id]= obj[i];
					 //console.log("orion schema "+JSON.stringify(orionDevicesSchema[obj[i].id]));
					 orionDevicesType[obj[i].id]= obj[i].type;
					}
				}
                 else
                 { 
                   orionDevices.push(obj.id);
                   orionDevicesSchema[obj.id]= obj;
                   orionDevicesType[obj.id]= obj.type;
                 }

               var sql = "(SELECT id FROM temporary_devices WHERE contextBroker = '" + ORION_CB + "') UNION (SELECT id FROM devices WHERE contextBroker = '" + ORION_CB + "')";
				console.log("SQL STRING "+ORION_CB);
				//Devo fare diff anche con device?
			 
			 if(typeof gb_value_units === undefined || gb_value_units.length <=0 )
				getParam(cid);

			if(typeof modelsdata === undefined || MODEL.localeCompare("custom")==0|| modelsdata.length <=0 )
				getModels(cid);
			
			  cid.query(sql, function (err, result, fields) {
                           // console.log(sql);
						  if (err) {console.log("sql "+sql); throw err;}
	                     // console.log("record selected " + result.length);
						 console.log("inside Query "+ORION_CB);
                          for (i = 0; i < result.length; i++) {
							//  console.log("result i"+JSON.stringify(result[i]));
                              registeredDevices.push(result[i].id);
                          }
						  var ori =[];
						 console.log("orion devices " + JSON.stringify(orionDevices) + " registeredDevices " + JSON.stringify(registeredDevices));
						  
                          var newDevices=orionDevices.diff(registeredDevices);
                                // console.log("device nuovi" + newDevices);
                          
						//  console.log("device nuovi" + newDevices.length + " dev "+ JSON.stringify(newDevices));		  
						  
						  var se = [];
                          var sesc = [];
                          for (var i=0; i < newDevices.length; i++)
                          {
							  
                             var topic= newDevices[i];
                             
							//console.log("orionDevicesSchema "+topic +" "+ JSON.stringify(orionDevicesSchema[topic]));

	                         var deviceSchema=extractSchema(orionDevicesSchema[topic]);

	                         if (!isTest(deviceSchema))
                                 {
							 /*extract latitude and longitude from devices*/
	   		                       obj1=storeDeviceSchema(ORION_CB, deviceSchema.attr, orionDevicesSchema, topic);
                                   // process.exit();	
								   var deviceVal = {"data_type": obj1.arr.data_type, "value_type": obj1.arr[4], "editable":"false", "value_unit":obj1.arr[5],"healthiness_criteria":obj1.arr[6],"healthiness_value":obj1.arr[7]};

									var toVerify ={"name": topic,"username": USER,"contextBroker": ORION_CB, "id": topic, "model": MODEL, "devicetype":orionDevicesType[topic], "protocol":ORION_PROTOCOL, "format":deviceSchema.format, "frequency": FREQUENCY, "kind":KIND,"latitude":obj1.latitude, "longitude":obj1.longitude,"macaddress":MAC,"deviceValues":deviceVal};
									
									var verify = verifyDevice(toVerify);
									var validity = "invalid";
									if(verify.isvalid)
										validity= "valid";
									se.push(storeDevice(USER,topic,MODEL,KIND,orionDevicesType[topic],
									ORION_PROTOCOL,FREQUENCY, deviceSchema.format, ORION_CB,obj1.latitude,
									obj1.longitude,MAC,validity,verify.message,"no",ORGANIZATION));
									//console.log("Se "+ JSON.stringify(se));							   

								   // var value_type = getValueType(obj1.arr);
	                               sesc= sesc.concat(obj1.arr);
                                 }
 //                                console.log(deviceSchema.attr);
                          }  
//                        // console.log(se);
                          //  console.log(JSON.stringify(sesc));
                          //  process.exit;
                         if ( se.length!=0)
                         {
					  var sqlse  = "INSERT INTO  `temporary_devices`(`username`,`id`, `model`,`kind`,`devicetype`,`protocol`, `format`,`contextBroker`,`latitude`,`longitude`,macaddress,`status`,`validity_msg`,`should_be_registered`,`organization`) VALUES ?";
						  var sqlsesc  = "INSERT INTO `temporary_event_values`(`device`, `cb`,`value_name`, `data_type`,`value_type`,`value_unit`,`healthiness_criteria`,`value_refresh_rate`,`old_value_name`) VALUES ?";


                  var promise1 = new Promise(function(resolve, reject) {
                      insertDevices(cid, se);
					  });
			      promise1.then(insertValues(cid, sesc));
						  
						
	                } 
             }); //query
		}); // res.on 
		res.on('finished',function(){
						 console.log("fatto");

		});
	 });//end req
	}

}
else{
	console.log("ELSE");
	
var req = http.request(options, function(res) {
     console.log('STATUS: ' + res.statusCode);
   //  console.log('HEADERS: ' + JSON.stringify(res.headers));
     res.setEncoding('utf8');
   //  console.log('options ' + JSON.stringify(options));

     res.on('data', function (chunk) {
            output += chunk;
            console.log("pezzo ");
      });

     res.on('end', function () {
                 console.log('BODY: finito ');
					
				orionDevices= [];
                 orionDevicesType = [];
                 orionDevicesSchema= [];

		console.log("output ");
		 var obj = JSON.parse(output);
                 output="";
                 // console.log(obj); 
                 if (obj instanceof Array)
                 {
					//console.log("length obj "+obj.length);
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
               var sql = "(SELECT id FROM temporary_devices WHERE contextBroker = '" + ORION_CB + "') UNION (SELECT id FROM devices WHERE contextBroker = '" + ORION_CB + "')";
			
			 if(typeof gb_value_units === undefined || gb_value_units.length <=0 )
				getParam(cid);
			
			if(typeof modelsdata === undefined || MODEL.localeCompare("custom")==0|| modelsdata.length <=0 )
				getModels(cid);
			
              cid.query(sql, function (err, result, fields) {
                           // console.log(sql);
						  if (err) {console.log("sql "+sql); throw err;}
	                      //console.log("record selected " + result.length);
                          for (i = 0; i < result.length; i++) {
                              registeredDevices.push(result[i].id);
                              }
						  var ori =[];
                          var newDevices=orionDevices.diff(registeredDevices);
                                // console.log("device nuovi" + newDevices);
                          console.log("device nuovi" + newDevices.length);		  
						  
						  var se = [];
                          var sesc = [];
                          for (var i=0; i < newDevices.length; i++)
                          {
							  
                             var topic= newDevices[i];
                           //  console.log("extract ORIONdeviceSchema "+JSON.stringify(orionDevicesSchema[topic]));
	                         var deviceSchema=extractSchema(orionDevicesSchema[topic]);
							 //console.log("Device schema "+JSON.stringify(deviceSchema));
	                         if (!isTest(deviceSchema))
                                 {
								   /*extract latitude and longitude from devices*/
	   		                       obj1=storeDeviceSchema(ORION_CB, deviceSchema.attr, orionDevicesSchema, topic);
								 //  console.log("obj1" +JSON.stringify(obj1));
                                   // process.exit();	
								   var deviceVal = {"data_type": obj1.arr[3], "value_type": obj1.arr[4], "editable":"false", "value_unit":obj1.arr[5],"healthiness_criteria":obj1.arr[6],"healthiness_value":obj1.arr[7]};

									var toVerify ={"username": USER,"contextBroker": ORION_CB, "name": topic, "model": MODEL, "devicetype":orionDevicesType[topic], "protocol":ORION_PROTOCOL, "format":deviceSchema.format, "latitude":obj1.latitude, "longitude":obj1.longitude,"frequency":FREQUENCY,"kind": KIND,"macaddress":MAC,"deviceValues":deviceVal};
									var verify = verifyDevice(toVerify);
									var validity = "invalid";
									if(verify.isvalid)
										validity= "valid";
									
                                    se.push(storeDevice(USER,topic,MODEL,KIND,orionDevicesType[topic], 
									ORION_PROTOCOL,FREQUENCY,deviceSchema.format, ORION_CB,obj1.latitude,
									obj1.longitude,MAC,validity,verify.message,"no",ORGANIZATION));
									//console.log("Se "+ JSON.stringify(se));							   
	                               sesc= sesc.concat(obj1.arr);

                                 }
 //                                console.log(deviceSchema.attr);
                          }  
//                        // console.log(se);
                          //  console.log(JSON.stringify(sesc));
                          //  process.exit;
                         if ( se.length!=0)
                         {
					  var sqlse  = "INSERT INTO  `temporary_devices`(`username`,`id`, `model`,`kind`,`devicetype`,`protocol`,`frequency`,`format`,`contextBroker`,`latitude`,`longitude`,macaddress,`status`,`validity_msg`,`should_be_registered`,`organization`) VALUES ?";
						  var sqlsesc  = "INSERT INTO `temporary_event_values`(`device`, `cb`,`value_name`, `data_type`,`value_type`,`value_unit`,`healthiness_criteria`,`value_refresh_rate`) VALUES ?";


                  var promise1 = new Promise(function(resolve, reject) {
                      insertDevices(cid, se); });
			      promise1.then(insertValues(cid, sesc));
						
	                } 
                          }); //query
}); // res.on 

	 });
	   req.end(); 

}

//var regexLatLong = ^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?),\s*[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)$;
var regexTimeZone0 = /^(\d{4,})-(\d{2})-(\d{2})[T ](\d{2}):(\d{2})(?::(\d{2}(?:\.\d+)?))[Z]?$/;
var regexTimeZone1 = /^(\d{4,})-(\d{2})-(\d{2})[T ](\d{2}):(\d{2})(?::(\d{2}(?:\.\d+)?))?$/;


function getValueType(valuename,type,value){
//deviceID, cb, att.name, att.type
if(type != null && type != undefined){

	type = returnString(type.toString());
	type = type.toLowerCase();
	
	if(valuename === undefined || gb_value_units === undefined || gb_value_units.length <= 0)
		return null;
	
	var name = returnString(valuename.toString());
	name = name.toLowerCase();
	
	//console.log("NAME "+name);
	//console.log("gb_value_units "+gb_value_units);
	if(gb_value_units.indexOf(name)>=0){
		console.log("if name units");
		return name;
	}
	if(regexTimeZone0.test(value) || regexTimeZone1.test(value) || name.localeCompare("dateobserved")==0)
		return "timestamp";
	if(name.localeCompare("lamax") ==0 || name.localeCompare("laeq")==0)
		return "sound_lv";
	
	if(name.localeCompare("sonometerclass")==0)
		return "audio";

	if(name.localeCompare("location")==0 )
		return "latitude_longitude";	
		
	if(name.localeCompare("geolocalization_lat")==0)
		return "latitude";
	
	if(name.localeCompare("geolocalization_lon")==0)
		return "longitude";
	
	if(name.localeCompare("description")==0 || name.localeCompare("refairqualityobservedmodel")==0)
		return "entity_desc";
		
	if(type === undefined)
		return null;
	
	if(type.localeCompare("binary")==0)
		return "button";
	
	if(name.localeCompare("model")==0)
		return "status";
	
	if(name.localeCompare("no")==0 || name.localeCompare("no2")==0)
		return "NO2_concentration";

	if(name.localeCompare("pm25")==0 || name.localeCompare("pm2.5")==0 )
		return "PM2.5_concentration";		
	
	if(name.localeCompare("pm10")==0 )
		return "PM10_concentration";		
	
	if(name.localeCompare("bc")==0)
		return "benzene_concentration";
		
	if(type.localeCompare("structuredvalue")==0 && typeof(value) !== 'object' && name.localeCompare("refdevice")!=0){		
		var splitVar = (value.toString()).split("|");
		splitVar[0] = returnString(splitVar[0].toString());
		
		/*To remove blank spaces
		for(var i = 0; i < splitVar.length; i++){
			splitVar[i] = splitVar[i].split(' ').join('');
			console.log("splitVar "+splitVar[i]);
		}*/
		if(splitVar[0].localeCompare("lamax") ==0 || splitVar[0].localeCompare("laeq")==0){
			//console.log("sound_lv");
			return "sound_lv";
		}
		/*else{
			var vector = [];
			//get the vector 
			for(var i = 1; i < splitVar.length; i++){
				vector.push(splitVar[i]);
			}
			var res;	
			res = getValueType(splitVar[i],type, vector);
			if(res != null)
				return res;
		}*/
	}
	if(typeof(value) === 'object')
		return "latitude_longitude";
	return null;
	}// end if(type != null && type != undefined
	return null;
}

function getDataType(valuename,type,value){
//deviceID, cb, att.name, att.type
if(type != null && type != undefined){

	type = returnString(type.toString());
	type = type.toLowerCase();

	if(valuename === undefined || gb_data_type === undefined || gb_data_type.length <= 0)
		return null;
	
	var name = returnString(valuename.toString());
	name = name.toLowerCase();
	
	//If the data type is in the list
	if(gb_data_type.indexOf(type)>=0){
		return type;
	}
	if(regexTimeZone0.test(value) || regexTimeZone1.test(value))
		return "time";
	
	if(type.localeCompare("text")==0)
		return "string";
	
	if(type.localeCompare("number")==0){
			if(isInt(value))
				return "integer";
			if(isFloat(value))
				return "float";
		}
	
	if(type.localeCompare("structuredvalue")==0 && typeof(value)!== 'object'){		
			console.log("value"+ value);
		var splitVar = (value.toString()).split("|");
		
		splitVar[1] = returnString(splitVar[1].toString());
		splitVar[1] = returnString(splitVar[1]);
		//To remove blank spaces
		/*for(var i = 0; i < splitVar.length; i++){
			splitVar[i] = splitVar[i].split(' ').join('');
			console.log("splitVar "+splitVar[i]);
		}*/
		if(isInt(splitVar[1]))
			return "integer";
		if(isFloat(splitVar[1]))
			return "float";
		
		/*else{
			var vector = [];
			//get the vector 
			for(var i = 1; i < splitVar.length; i++){
				vector.push(splitVar[i]);
			}
			var res;	
			res = getValueType(splitVar[i],type, vector);
			if(res != null)
				return res;
		}*/
	}
	if(typeof(value) === 'object')
		return "float";
	return null;
	}// end if(type != null && type != undefined
	return null;
}
function getValueUnit(value){
	console.log("value switch "+value);
	switch(value){
		case "temperature": return "°C";
		case "speed": return "km/h";
		case "humidity": return "%";
		case "power": return "W";
		case "status": return "#";
		case "pressure": return "hPa";
		case "orientation": return "deg";
		case "timestamp": return "s";
		case "string": return "#";
		case "integer":return "#";
		case "float":return "#";
		case "benzene_concentration": return "ppm";
		case "NO_concentration": return "ppm";
		case "NO2_concentration": return "ppm";
		case "PM10_concentration": return "ppm";
		case "PM2.5_concentration": return "ppm";
		default: return "#";
	}
	
	return null;
}
function returnString(str){
	var str2 = "";
	for(var i = 0; i < str.length; i++){
		var c = str.charAt(i);
		if(c.match(/^[a-zA-Z0-9_.-]*$/)){
			str2 = str2+c;
		}
	}
	return str2;
}
function isInt(n){
    return Number(n) == n && n % 1 === 0;
}
function isFloat(n){
    return Number(n) == n && n % 1 !== 0;
}

function verifyDevice(deviceToverify){
  //  console.log("VERIFYING THE DEVICE "+JSON.stringify(deviceToverify));
    
	var msg="";
    var regexpMAC = /^([0-9A-F]{2}[:-]){5}([0-9A-F]{2})$/;
    var answer={"isvalid":true, "message":"Your device is valid"};
   
    
   // console.log("First checking its properties validity");
    
    if(deviceToverify.name==undefined || deviceToverify.name.length<5 || deviceToverify.name == null){ msg+= "-name is mandatory, of 5 characters at least.";}
    if(deviceToverify.devicetype==undefined || deviceToverify.devicetype=="" || deviceToverify.devicetype.indexOf(' ')>=0|| deviceToverify.devicetype == null){msg+="-devicetype is mandatory.";}
    if(deviceToverify.macaddress!=undefined && !regexpMAC.test(deviceToverify.macaddress) || deviceToverify.macaddress == null){msg+="-macaddress is mandatory and Mac format should be Letter (A-F) and number (eg. 3D:F2:C9:A6:B3:4F).";}
    if(deviceToverify.frequency==undefined ||deviceToverify.frequency=="" || !isFinite(deviceToverify.frequency) || deviceToverify.frequency == null){msg+= "-frequency is mandatory, and should be numeric.";}
	if(deviceToverify.kind==undefined || deviceToverify.kind=="" || deviceToverify.kind == null){msg+="-kind is mandatory.";}
    if(deviceToverify.protocol==undefined || deviceToverify.protocol=="" || deviceToverify.protocol == null){msg+="-protocol is mandatory.";}
    if(deviceToverify.format==undefined || deviceToverify.format=="" || deviceToverify.format == null){msg+="-format is mandatory.";}
    if(deviceToverify.latitude==undefined || !isLatitude(deviceToverify.latitude)|| deviceToverify.latitude ==null ){msg+="-Latitude is mandatory, with the correct numeric format.";}
    if(deviceToverify.longitude==undefined ||!isLongitude(deviceToverify.longitude || deviceToverify.longitude ==null)){msg+="-Longitude is mandatory, with the correct numeric format.";}
    if(deviceToverify.k1==undefined || deviceToverify.k1=="" || deviceToverify.k1==null){msg+="-k1 is mandatory.";}
    if(deviceToverify.k2==undefined || deviceToverify.k2=="" || deviceToverify.k2==null){msg+="-k2 is mandatory.";}
   
	console.log("device to ver k1 "+ deviceToverify.k1);
      
    if(msg.length>0) answer.isvalid=false;
     
	if(deviceToverify.deviceValues.length<1){
           answer.isvalid=false;
           msg+="-Your device should at least have 1 attributes.";
        }
        
   // console.log("Now we check the model conformity");
    
	if(deviceToverify.model!="custom"){
		
		//console.log("The model is not custom, it is "+ deviceToverify.model);
        
        for(var i=0; i<modelsdata.length; i++){
                
                
			if(modelsdata[i].name!=deviceToverify.model){
                continue;
				}
            
			var modelAttributes= JSON.parse(modelsdata[i].attributes);
                
          //  console.log("model attributes " + JSON.stringify(modelAttributes));
            //console.log("deviceToVerify attributes " + JSON.stringify(deviceToverify.deviceValues));
          //  console.log(Object.keys(modelAttributes).length);
          //  console.log(Object.keys(deviceToverify.deviceValues).length);

            if(Object.keys(modelAttributes).length!=Object.keys(deviceToverify.deviceValues).length){
                   
                answer.isvalid=false;
                msg+="-Your device has different number of attributes than the selected model ";
                   }
                
            else{
                        
                            
                for (var j=0; j<deviceToverify.deviceValues.length; j++){
                                var found=0;
                                for(var l= 0; l<modelAttributes.length; l++){
                               //  console.log(" attributes model "+ modelAttributes[l].value_name);   
                                // console.log(" attributes device to verify "+deviceToverify.deviceValues[j].value_name);   
                                    if(modelAttributes[l].value_name==deviceToverify.deviceValues[j].value_name){
                                        found=1;
									/*	console.log(modelAttributes[l].value_type!=deviceToverify.deviceValues[j].value_type );
										console.log(modelAttributes[l].data_type!=deviceToverify.deviceValues[j].data_type );
										console.log(modelAttributes[l].editable!=deviceToverify.deviceValues[j].editable);
										console.log(modelAttributes[l].healthiness_criteria!=deviceToverify.deviceValues[j].healthiness_criteria);
										console.log(modelAttributes[l].healthiness_value!=deviceToverify.deviceValues[j].healthiness_value);
										console.log(modelAttributes[l].value_unit!=deviceToverify.deviceValues[j].value_unit);
                                        */
                                        var msg_attr_detail=""
                                        
                                        if(modelAttributes[l].value_type!=deviceToverify.deviceValues[j].value_type)
                                        {msg_attr_detail+=" value type,";}
                                        if(modelAttributes[l].data_type!=deviceToverify.deviceValues[j].data_type)
                                        {msg_attr_detail+=" data type,";}
                                        if(modelAttributes[l].editable!=deviceToverify.deviceValues[j].editable)
                                        {msg_attr_detail+=" editable,";}
                                        if(modelAttributes[l].healthiness_criteria!=deviceToverify.deviceValues[j].healthiness_criteria)
                                        {msg_attr_detail+=" healthiness criteria,";}
                                        if(modelAttributes[l].healthiness_value!=deviceToverify.deviceValues[j].healthiness_value){msg_attr_detail+=" healthiness value,";}
                                        if(modelAttributes[l].value_unit!=deviceToverify.deviceValues[j].value_unit)
                                            {msg_attr_detail+=" value unit,";}
                                        
                                        if(msg_attr_detail.length>0){
                                            answer.isvalid=false;
                                            msg+="The attribute "+deviceToverify.deviceValues[j].value_name+" has the details:"+msg_attr_detail+" not compatible with its model.";
                                        }
                                        else{
                                            modelAttributes.splice(l,1);                                        }
                                    }
                                }
                                if(found==0){
                                    answer.isvalid=false;
                                    msg+="-The device attribute name "+ deviceToverify.deviceValues[j].value_name+ " do not comply with its model."     
                                }

                            }
                        
                
                    
                   
                   }
            
            
          /* console.log("modelsdata[i].edge_gateway_type");
                console.log(modelsdata[i].edge_gateway_type);
                console.log("deviceToverify.edge_gateway_type");
                console.log(deviceToverify.edge_gateway_type);
               */
                var h3= (modelsdata[i].edge_gateway_type==deviceToverify.edge_gateway_type)||
                        (
                            (modelsdata[i].edge_gateway_type==undefined || modelsdata[i].edge_gateway_type=="" || modelsdata[i].edge_gateway_type== null)&&
                            (deviceToverify.edge_gateway_type==undefined || deviceToverify.edge_gateway_type=="" || deviceToverify.edge_gateway_type== null)
                            
                        );
                     
                       
                if(modelsdata[i].contextbroker!=deviceToverify.contextbroker){ answer.isvalid=false; 
                                                                              msg+="-The device property: context broker does not comply with its model." ;} 
                if(modelsdata[i].devicetype!=deviceToverify.devicetype) {answer.isvalid=false;
                                                                         msg+="-The device property: type does not comply with its model." ;}
                if(!h3){ answer.isvalid=false; 
                        msg+="-The device property: edge gateway type does not comply with its model." ;}
                if(modelsdata[i].format!=deviceToverify.format){ answer.isvalid=false;
                                                                msg+="-The device property: format does not comply with its model." ;}
                if(modelsdata[i].frequency!=deviceToverify.frequency){ answer.isvalid=false; 
                                                                      msg+="-The device property: frequency does not comply with its model." ;}
                if(modelsdata[i].kind!=deviceToverify.kind){ answer.isvalid=false;
                                                            msg+="-The device property: kind does not comply with its model." ;}
                if(modelsdata[i].producer!=deviceToverify.producer){ answer.isvalid=false;
                                                            msg+="-The device property: producer does not comply with its model." ;}
                if(modelsdata[i].protocol!=deviceToverify.protocol){{ answer.isvalid=false;
                                                            msg+="-The device property: protocol does not comply with its model." ;}}
                              
            }
            
        }
        
        else{
          //  console.log("model is custom so we check the values details");
            var all_attr_msg="";
            var all_attr_status="true";
            var healthiness_criteria_options=["refresh_rate", "different_values", "within_bounds"];

            for (var i=0; i<deviceToverify.deviceValues.length; i++){
                var v=deviceToverify.deviceValues[i];

                if(v==undefined){continue;}
                var attr_status=true;
                var attr_msg="";
           /*     console.log(v);
                console.log(deviceToverify.deviceValues.length);
                console.log(deviceToverify);
				*/
				//Sara3010
				var empty_name = false;

                if(v.value_name==undefined || v.value_name==""){
                   attr_status=false;
				   empty_name = true;
               }
                //set default values
                if(v.data_type==undefined || v.data_type==""|| gb_datatypes.indexOf(v.data_type)<0){
                        attr_status=false;
                        attr_msg = attr_msg+ " data_type";
                }
				//Sara3010 - Start
                if(v.value_unit==undefined || v.value_unit==""){
                        attr_status=false;
                        attr_msg = attr_msg+ " value_unit";
                }				
				//Sara3010 - End
                if(v.value_type==undefined || v.value_type==""|| gb_value_types.indexOf(v.value_type)<0){
                        attr_status=false;
                        attr_msg =attr_msg+ " value_type";
                }
                if(v.editable!="0" && v.editable!="1"){
                        attr_status=false;
                        attr_msg =attr_msg+ " editable";
                }
                if(v.healthiness_criteria==undefined || v.healthiness_criteria==""||healthiness_criteria_options.indexOf(v.healthiness_criteria)<0){
                        attr_status=false;
                        attr_msg =attr_msg+ " healthiness_criteria";
                }
                if(v.healthiness_value==undefined || v.healthiness_value==""){
                        attr_status=false;
                        attr_msg =attr_msg+ " healthiness_value";
                }

                if (attr_status==false){
                    
					all_attr_status=false;
					//Sara3010
					if(empty_name){
						all_attr_msg= "The attribute name cannot be empty";
						if(attr_msg != ""){
							all_attr_msg= all_attr_msg+", other errors in: "+attr_msg;
						}
					}
					else{
						all_attr_msg= "For the attribute: "+ v.value_name+", error in: "+attr_msg;
					}
					
					
					
					
                }

            }

            if(!all_attr_status){
                answer.isvalid=false;
                msg= msg+ " -"+all_attr_msg;
        }
        }
    //}
    
    //answer.isvalid=true;
    if(answer.isvalid){
        return answer;
    }
    else{
        answer.message=msg;
        return answer;
    }
    
}
function isLatitude(lat) {
  return isFinite(lat) && Math.abs(lat) <= 90;
}

function isLongitude(lng) {
  return isFinite(lng) && Math.abs(lng) <= 180;
}
