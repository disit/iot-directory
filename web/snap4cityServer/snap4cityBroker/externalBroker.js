console.log('entered');

//values retrieved from snapIoTDirectory_cbRetrieval_c 
var ORION_CB = process.argv[2]; 
var DEVICE_NAME = process.argv[3];
var ORION_ADDR = process.argv[4];
var USER = process.argv[5];
var ACCESS_LINK = process.argv[6];
var ACCESS_PORT = process.argv[7];
var MODEL = process.argv[8];
var EDGE_GATEWAY_TYPE = process.argv[9];
var EDGE_GATEWAY_URI = process.argv[10];
var ORGANIZATION=process.argv[11];
var PATH = process.argv[12];
var KINDBROKER = process.argv[13];
var APIKEY = process.argv[14];

//Static values
var ORION_PROTOCOL = "ngsi";
var MAC = "3D:F2:C9:A6:B3:4F";
var KIND = "sensor";
var FREQUENCY = 10;
var flagAntwerp = true;
var regexTimeZone0 = /^(\d{4,})-(\d{2})-(\d{2})[T ](\d{2}):(\d{2})(?::(\d{2}(?:\.\d+)?))[Z]?$/;
var regexTimeZone1 = /^(\d{4,})-(\d{2})-(\d{2})[T ](\d{2}):(\d{2})(?::(\d{2}(?:\.\d+)?))?$/;
var _serviceIP = "../stubs";
var deviceAttributes=["uri", "devicetype", "kind", "macaddress", "producer", "latitude", "longitude", "protocol", "format", "frequency","k1","k2"];

/* global variables */
var registeredDevices = [];
var orionDevices= [];
var orionDevicesSchema=[];
var orionDevicesType = [];
var modelsdata=[];
var httpRequestOutput="";
var gb_value_units=[];
var gb_datatypes=[];
var gb_value_types = [];

var _serviceIP = "../stubs";

var express = require('express');
var request = require('request');
var bodyParser = require('body-parser');
const spawn = require('child_process').spawn;
var registeredStub = [];

/* MYSQL setup */
var mysql = require('mysql');
var Promise = require('promise');
const fs = require('fs');
var FileSaver = require('file-saver');
var Blob = require('blob');
var Parser = require('./Parser/Classes/Parser');

/* ORION setup */ 
var http = require("http");

var XMLHttpRequest = require("xmlhttprequest").XMLHttpRequest;

console.log('db?');


const ini = require('ini');
const config = ini.parse(fs.readFileSync('./snap4cityBroker/db_config.ini', 'utf-8'));
const c_host = config.database.host;
const c_user = config.database.user;
const c_port = config.database.port;
const c_password = config.database.password;
const c_database = config.database.database;


var cid = mysql.createConnection({
    host: c_host,
    port: c_port,
    user: c_user,
    password: c_password,
    database: c_database
  });
  

cid.connect(function(err){
      if(err){ 
              console.log('Error connecting to Db');
              throw err;
             }
    console.log('Connection established');
});
 
/*  MAIN PROGRAM */

var httpRequestOutput="";

var link ="";
var limit = 1000;
var offset2 = 900;
var offset = 0;
var smallSearch=0;

if(ACCESS_PORT !== undefined && ACCESS_PORT.localeCompare("null")!==0 && ACCESS_PORT.localeCompare("")!==0  ){
        ACCESS_LINK = ACCESS_LINK+":"+ACCESS_PORT;
}

//console.log("localeCompare if external");
if(PATH == undefined || PATH.localeCompare("null")==0 || PATH.localeCompare("")==0  ){
	link = ACCESS_LINK;
}
else{
	link = ACCESS_LINK+ PATH;	
}
if(!link.includes(DEVICE_NAME)){
	console.log("include");
	let link_provv = link.split("?");
	link  = link_provv[0] + "/" + DEVICE_NAME;
	console.log("Link "+ link);
}
else{
	console.log("notinclude "+ link);
}
var xhttp = new XMLHttpRequest();  

retrieveData(xhttp, link);

function retrieveData(xtp, link){
	var promiseAcquisition = new Promise(function(resolve2, reject){	
		xhttp = new XMLHttpRequest();  

		if(APIKEY !== null && APIKEY !== undefined && APIKEY.localeCompare("null")!==0){
			//console.log("apikey not null" + ORION_CB + APIKEY);		
			console.log("link ss"+ link);
			xhttp.open("GET", link, true);
			xhttp.setRequestHeader("apikey",APIKEY);
			xhttp.send(); 
		}//end if APIKEY != NULL
		else
		{ //if apikey is not defined
			console.log("apikey null");
			xhttp.open("GET", link, true);
			xhttp.send(); 
		}

		xhttp.onreadystatechange = function() {
			console.log("readyState " + this.readyState + " status " + this.status + this.responseText );
                        if (this.readyState == 4 && this.status == 0) {
                                console.error("not reacheable");
                        }
			else if (this.readyState == 4 && this.status == 400) {
                                console.error("path malformed");
                        }
			else if (this.readyState == 4 && this.status == 404) {
				console.error("not found");
			}
			else if (this.readyState == 4 && this.status == 200) {
				console.log("ready");
				//function that manages the output in order to create the data
			var responseText = this.responseText;
			//variable initialization
			orionDevices= [];
			orionDevicesType = [];
			orionDevicesSchema= new Object();
						 
			var obj = JSON.parse(responseText);
			httpRequestOutput="";
			
			if (obj instanceof Array)
			{

			//console.log("length obj "+obj.length);
			//console.log("obj "+obj);
				for (i=0; i < obj.length; i++) {
				//	console.log("obkid: " + obj[i].id);
					let index= obj[i].id.toLowerCase();
					//console.log("index "+ index);
					
					orionDevices.push(index);
					orionDevicesSchema[index.toLowerCase()]= obj[i];
					orionDevicesType[index.toLowerCase()]= obj[i].type;

				}
			
			}
			else
			{ 
				orionDevices.push(obj.id);			
				orionDevicesSchema[obj.id.toLowerCase()]= obj;
				orionDevicesType[obj.id.toLowerCase()]= obj.type;
			}
					 
			//if(typeof gb_value_units === undefined || gb_value_units.length <=0 )
			//	getParam(cid);
			if(typeof modelsdata === undefined || MODEL.localeCompare("custom")==0|| modelsdata.length <=0 )
				getModels(cid);
			
			var promiseValueType = new Promise(function(resolveValueType,rejectValueType){
			var valueType  = "SELECT value_type FROM value_types ORDER BY value_type";

				if(gb_value_types === undefined || gb_value_types.length <= 0){
					cid.query(valueType, function (err, result, fields) {
										
						// console.log(sql);
						if (err) {console.log("sql "+valueType); throw err;}
					//	console.log("result qye")
						for (i = 0; i < result.length; i++) {
							gb_value_types.push(result[i].value_type);
						}
						resolveValueType();	  
					}); //query
				}
				else{
					resolveValueType();	  				
				}
			});//end promiseValueType
			promiseValueType.then(function(resolveValueType){
				
				var promiseDataType = new Promise(function(resolveDataType, rejectDataType){
					var dataType= "SELECT data_type FROM data_types order by data_type";
						
						if(gb_datatypes === undefined || gb_datatypes.length <= 0){
							cid.query(dataType, function (err, result, fields) {

								// console.log(sql);
								if (err) {console.log("sql "+dataType); throw err;}
								
								for (i = 0; i < result.length; i++) {
									gb_datatypes.push(result[i].data_type);
								}
						
								resolveDataType();
							}); //query
						}
						else{
								resolveDataType();
						}
				});//end promise data type
				promiseDataType.then(function(resolveDataType){
					var promiseUnit = new Promise(function(resolveUnit, rejectUnit){
						var valueUnit= "SELECT DISTINCT value_unit_default FROM value_types ORDER BY value_unit_default";
						
						if(gb_value_units === undefined || gb_value_units.length <= 0){
							cid.query(valueUnit, function (err, result, fields) {

							// console.log(sql);
								if (err) {console.log("sql "+valueUnit); throw err;}
								for (i = 0; i < result.length; i++) {
								  gb_value_units.push(result[i].value_unit_default);
								}
								resolveUnit();
							}); //query
						}
						else{
							resolveUnit();
						}
					});//end promiseUnit
					promiseUnit.then(function(resolveDataType){					
											
						//checking if the devices already exist in the platform
						//console.log("registeredDevices " +registeredDevices.length + " orion length "+ orionDevices.length);
						var newDevices=orionDevices;
						//console.log("diff " +newDevices.length);
						//Checking duplicates into the same array
						var extractionRulesAtt = new Object();
						var extractionRulesDev=new Object();
						var promiseExtractionRules = new Promise(function (resolveExtraction, rejectExtraction){
							var query  = "SELECT * FROM extractionRules where contextbroker='"+ORION_CB+"';";

							cid.query(query, function (err, resultRules, fields) {
													
							if (err) {console.log("sql "+query); throw err;}
							console.log("extraction rules");
								for(var x = 0; x < resultRules.length; x++){
									//console.log("result rules");
									if(resultRules[x]["kind"].localeCompare("property") == 0){
										extractionRulesDev[resultRules[x]["id"]]=resultRules[x];
									}
									else{
										//console.log("resultRules[x] "+ resultRules[x]["id"]);
										extractionRulesAtt[resultRules[x]["id"]]=resultRules[x];
									}

								}
							if(resultRules.length==0){
								console.log("extraction rules are empty");
								rejectExtraction();
							}
							else{
								console.log("extraction rules are NOT empty");
								resolveExtraction();	
							}
							}); //query

						});

						var se = [];
						var sesc = [];
						//console.log("nodup "+ newDevices.length);
							//			console.log("oriondeviceschema2 "+ JSON.stringify(orionDevicesSchema));
						var ruleJSON,parser;
						promiseExtractionRules.then(function(resolveExtraction){
							/*for (var key in orionDevicesSchema) {
								console.log(key);			
							}*/
							//console.log("new devices "+ newDevices +  "registeredDevices "+ registeredDevices );

							var type;
							var sensor;
							//var sensorApplied = new Object();
							var rule;
							var devAttr = new Object();
							for (var i=0; i < newDevices.length; i++)
							{
								var attProperty=[];
								var topic= newDevices[i];
								
								if(orionDevicesSchema[topic.toLowerCase()] == undefined){
									console.log("topic undefined "+ topic + "m " + JSON.stringify(orionDevicesSchema));
									continue;
								}
							console.log("topic fdf" + topic);

								for(var j in extractionRulesAtt){

									rule= extractionRulesAtt[j]["selector"];
									var id = extractionRulesAtt[j]["id"];
									rule = JSON.stringify(rule);
									
								//	console.log("rule "+ rule);
									rule = rule.replace(/\\/g, "");
									rule = rule.replace("PM2,5","PM2x5");
									rule = rule.replace("PM2.5","PM2y5");

									rule = rule.slice(1,rule.length-1);
									
									let jsonRules = JSON.parse(rule);
									parser = new Parser();
									let typeData = (jsonRules.type).toUpperCase();
									parser.addObjRule(jsonRules,typeData);	
									let v = orionDevicesSchema[topic.toLowerCase()];
									v = JSON.stringify(v);
									v = v.replace("PM2,5","PM2x5");
									v = v.replace("PM2.5","PM2y5");

									let parserApply = parser.applyRules(v);	
									//console.log("parserApply2 "+JSON.stringify(parserApply));

									var attName, value_type, data_type;
									
									for(var p = 0; p < parserApply.length; p++){
										console.log("parserApply "+ JSON.stringify(parserApply)+ " pasr "+ parserApply[0].type);
										if(extractionRulesAtt[j]["value_type"].startsWith("{")){
											//console.log("startsWith");
											let parserValue = new Parser();
											let ruleValue = extractionRulesAtt[j]["value_type"];
											ruleValue =JSON.stringify(ruleValue);
											//console.log("ruleValue "+ ruleValue);
											ruleValue = ruleValue.replace(/\\/g, "");
											ruleValue = ruleValue.slice(1,rule.length-1);
											let jsonRuleValue = JSON.parse(ruleValue);
											parserValue.addObjRule(jsonRuleValue,"JSON");	
											let v2 = orionDevicesSchema[topic.toLowerCase()];
											v2 = JSON.stringify(v2);
											let parserApply2 = parserValue.applyRules(v2);	
											value_type = parserApply2[0];
										//	console.log("parserApply2" + value_type);
										}
										else{
											value_type = extractionRulesAtt[j]["value_type"];
										}
										
										//------name extraction ----
										if(extractionRulesAtt[j]["structure_flag"].localeCompare("yes")==0){
											let toSplit = (jsonRules.param.s).split(".");
											attName = toSplit[toSplit.length-1];
										}
										else{
											if(parserApply[p].id !== undefined){
												attName = parserApply[p].id;
											}
											else if(parserApply[p].id !== undefined){
												attName = parserApply[p].id;
											}
											else{
												attName = id;
											}
										console.log("attName "+ attName);
										}
										if(extractionRulesAtt[j]["data_type"] == null ||extractionRulesAtt[j]["data_type"].length == 0){
											data_type = parserApply[p].type;
										}
										else{
											data_type = extractionRulesAtt[j]["data_type"];
										}
										attName = attName.replace("PM2x5","PM2,5");
										attName = attName.replace("PM2y5","PM2.5");
			
										let objProp = {"value_name": attName, "value_type": value_type, "data_type": data_type, "value_unit": extractionRulesAtt[j]["value_unit"], "editable": false, "healthiness_criteria" : "refresh_rate", "healthiness_value":300};
										//console.log("objProp "+ JSON.stringify(objProp));
										attProperty.push(objProp);

											//sensor = {"name": attName, "value_type":}
									}//for p*/
								}//end for j

								for(var j in extractionRulesDev){
									var nameDev = extractionRulesDev[j]["id"];
								//	console.log("nameDev "+ nameDev + JSON.stringify(deviceAttributes));
									if(deviceAttributes.includes(nameDev)){
										let ruleDev= extractionRulesDev[j]["selector"];
										ruleDev = JSON.stringify(ruleDev);
										
										ruleDev = ruleDev.replace(/\\/g, "");
										ruleDev = ruleDev.slice(1,ruleDev.length-1);

										let jsonRulesDev = JSON.parse(ruleDev);
										parserDev = new Parser();

										parserDev.addObjRule(jsonRulesDev,"JSON");	
										let vDev = orionDevicesSchema[topic.toLowerCase()];
										//console.log("vDev "+ JSON.stringify(vDev));
										vDev= JSON.stringify(vDev);
										//console.log("before apply rules");
										let parserApply = parserDev.applyRules(vDev);	
										
										devAttr[nameDev] = parserApply[0];
										//console.log("devAttr"+ devAttr[nameDev] + " namedev "+ nameDev);
									}

								}//end for j dev
								var toVerify ={"name": topic,"username": USER,"contextBroker": ORION_CB, "id": topic, "model": MODEL, "devicetype":devAttr["devicetype"], "protocol":ORION_PROTOCOL, "format":devAttr["format"], "frequency": FREQUENCY, "kind":KIND,"latitude":devAttr["latitude"], "longitude":devAttr["longitude"],"macaddress":devAttr["mac"],"k1":devAttr["k1"], "k2": devAttr["k2"],"deviceValues":attProperty};
								var verify = verifyDevice(toVerify);
								var validity = "invalid";
								if(verify.isvalid)
									validity= "valid";
							

							console.log(JSON.stringify(toVerify));
						/*	se.push(storeDevice(USER,topic,MODEL,KIND,devAttr["devicetype"],
							ORION_PROTOCOL,FREQUENCY, devAttr["format"], ORION_CB,devAttr["latitude"],
							devAttr["longitude"],devAttr["mac"],validity,verify.message,"no",ORGANIZATION, EDGE_GATEWAY_TYPE,EDGE_GATEWAY_URI,devAttr["k1"],devAttr["k2"]));
							//console.log("Se "+ JSON.stringify(se));							   
							*/
						    // var value_type = getValueType(obj1.arr);

							}//end for i 

							})//end then extraction rules
							.catch(error => {
								console.log("no extraction rules found, returning error msg");
								console.error("extraction rules not found");
							});
					});//promise unit then 
				});//end then promise data type

			});//end then value type 
			
				
			}//end readystate == 4
			if (this.readyState == 4 && this.status == 500) {
				//console.log("reject");			
			}
		};//end onreadystatechange
	});//end promiseAcquisition
}	

function extractType(value)
{
  var first = value.charAt(0);
  var f;
  switch (first)
  {
    case "{": // Format C
		f="json";
	    break;
    case "<": // Format D
		f="xml";
	    break;
    default: // Format A or B
	   f="csv";
	   break;
  }
  return f;
}


 /*functions */
Array.prototype.diff = function( arr ) {
	//console.log ("diff");
  return this.filter( function( val ) {
	  //console.log("arr.index "+ arr.indexOf(val)+ " val "+ val);
    return arr.indexOf( val ) < 0;
  });
};


function getModels(cid)
{
	//console.log("getModels");
    var models  = "SELECT * FROM model";

	cid.query(models, function (err, result, fields){
		if (err) {console.log("sql "+sql); throw err;}
			for (i = 0; i < result.length; i++) {
				modelsdata.push(result[i]);
			}
	}); //query
}

function getParam(cid)
{
	
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
							console.log("result length dataType "+result.length);

		// console.log(sql);
		if (err) {console.log("sql "+sql); throw err;}
		for (i = 0; i < result.length; i++) {
		  gb_datatypes.push(result[i].data_type);
		}
	}); //query
	var valueUnit= "SELECT DISTINCT value_unit_default FROM value_types ORDER BY value_unit_default";
	cid.query(valueUnit, function (err, result, fields) {
							console.log("result length valueunit "+result.length);

		// console.log(sql);
		if (err) {console.log("sql "+sql); throw err;}
		for (i = 0; i < result.length; i++) {
		  gb_value_units.push(result[i].value_unit_default);
		}
	}); //query
	
}


/* store a device in the db*/
function storeDevice(user,deviceID,model,kind,type, protocol,frequency, format, cb, latitude,longitude,macaddress,status,validity_msg,shouldberegistered,organization,edge_type,edge_uri,k1,k2){
	return [user,deviceID,model,kind,type,protocol,frequency,format,cb,latitude,longitude,macaddress,status, validity_msg,shouldberegistered,organization,edge_type,edge_uri,k1,k2];
}
function storeAttribute(topic, name, data_type, value_type, value_unit, healthiness_criteria, healthiness_value){
	return [topic, ORION_CB, name, data_type, value_type, value_unit, healthiness_criteria, healthiness_value, name];
}
function getAttributes(topic, extractionRulesAtt, resApply){
	var arr = [];

	/*for(var key in resApply){
		arr.push([topic,resApply[key]["contextbroker"],res])
			 arr.push([deviceID,cb,att.id,data_type,value_type,value_unit,"refresh_rate",300,att.id]);		

	}*/
}
/* extract the attributes from the device value and store them in the db*/
function storeDeviceSchema(cb, attributes, deviceSchema,deviceID){
	var arr =[];
	var longitude="";
	var latitude="";
	var ob = deviceSchema[deviceID];
	var value_type= ""; 
	
	console.log("storeDeviceSchema deviceSchema[deviceID] "+ JSON.stringify(ob));
	
//	console.log("gb_value_units length "+ gb_value_units.length);

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
	// console.log("push "+deviceID + att.id + "value_type "+ value_type);
	 if(value_type === null || value_type === undefined || value_type == '' || value_type == ' '){
		value_type = 'rain';
	 }
	 if(data_type === null || data_type === undefined){
		data_type = 'float';
	 }
	 
	 latitude = "51.219890";
	 longitude = "4.4034600";
	// console.log("Device id "+ deviceID + " cb "+ cb + " att.id " + att.id + " data type "+ data_type);
	 arr.push([deviceID,cb,att.id,data_type,value_type,value_unit,"refresh_rate",300,att.id]);		
	}
  }
  else{
	  //console.log("storeDeviceSchema" + JSON.stringify(ob));
	  for (i=0; i < attributes.length; i++)
	  {	
		//console.log("for "+JSON.stringify(attributes));
		att= attributes[i];
		//console.log("ATT" + JSON.stringify(att));
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
		 //	 console.log("Device id "+ deviceID + " cb "+ cb + " att.id " + att.id + " data type "+ data_type);

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
	//console.log("Extract schema valueType "+ gb_value_types.length + " units " + gb_value_units.length + "data type " + gb_datatypes.length);

	var attributes = [];
	var f = ""; // identified format
//	console.log("valore processato " + value);
	
	if(ORION_CB == 'Antwerp' && flagAntwerp ){
	//console.log("extract antw");
		attributes= parseAntwerpJSON(value);
	}
	else{
	//console.log("extractorion");
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
	console.log("ParseOrionJSON" + JSON.stringify(obj));
  var attributes = [];
  // {"id":"ARDUINO_ST_4201_1516802097","type":"Temperature","Temperature":{"type":"float","value":"20.0","metadata":{}},"geolocalization":{"type":"string","value":"45.453701,9.214914","metadata":{}},"measure_units":{"type":"string","value":"Celsius","metadata":{}},"timestamp":{"type":"integer","value":"1516802097","metadata":{}}}
  var pos=1;
  for (var prop in obj)
  {

    if (prop != "id" && prop != "type")
	{
		console.log("prop parseOrionJSON "+ prop);
         // console.log(prop);
         // console.log(obj[prop].type);
         // console.logconsole.log(obj[prop].value);
	//console.log("name:"+prop+"; type:"+obj[prop].type.toLowerCase()+"; value: "+obj[prop].value + ";position: "+pos);
	 attributes.push({"name": prop, "type": obj[prop].type, "value": obj[prop].value, 
	 "position":pos});
	 
	 pos++;
	}
  }
  return attributes;
}

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
		//console.log("if name units");
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
	
	if(name.localeCompare("description")==0 || name.localeCompare("refairqualityobservedmodel")==0 ||name.localeCompare("nome")==0 ||name.localeCompare("stazione")==0 )
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
	
	if(name.localeCompare("temperature")==0 || name.localeCompare("temperatura")==0)
		return "temperature";
	
	if(name.localeCompare("stato")==0 || name.localeCompare("quota")==0)
		return "status";
		
	
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

	if(valuename === undefined || gb_datatypes === undefined || gb_datatypes.length <= 0)
		return null;
	
	var name = returnString(valuename.toString());
	name = name.toLowerCase();
	
	//If the data type is in the list
	if(gb_datatypes.indexOf(type)>=0){
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
	//console.log("value switch "+value);
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
	
	//console.log("deviceToVerify lengh"+ deviceToverify.deviceValues.length);
//	console.log("device To verify "+ deviceToverify.name + " dim "+ deviceToverify.deviceValues.length);
	
//	console.log("verify valueType "+ gb_value_types.length + " units " + gb_value_units.length + "data type " + gb_datatypes.length);
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
   
	//console.log("device to ver k1 "+ deviceToverify.k1);
      
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
            var all_attr_status=true;
            var healthiness_criteria_options=["refresh_rate", "different_values", "within_bounds"];

			 //console.log("deviceToVerify lengh"+ JSON.stringify(deviceToverify.deviceValues));
           for (var i=0; i<deviceToverify.deviceValues.length; i++){
                var v=deviceToverify.deviceValues[i];

				//  if(v==undefined){continue;}
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
						//console.log("valueType");
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


function removeDuplicates(arr){
    let unique_array = []
    for(let i = 0;i < arr.length; i++){
        if(unique_array.indexOf(arr[i].toLowerCase().replace("-","")) == -1){
            unique_array.push(arr[i].toLowerCase())
        }
    }
    return unique_array
}

//this is needed???
Array.prototype.removeDuplicatesSchema = function( arr ) {
	//console.log ("diff");
  return this.filter( function( val ) {
    return arr.indexOf( val ) < 0;
  });
};
