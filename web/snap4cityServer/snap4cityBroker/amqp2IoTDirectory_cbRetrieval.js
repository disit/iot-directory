
var AMQP_CB             = "rabbitUNIMI";//process.argv[2];
var AMQP_PROTOCOL       = "amqp";
var AMQP_EXCHANGE       = "sensor_rabbit";
var AMQP_DURABLE        = true;
var AMQP_TOPIC          = "#";
var AMQP_ADDR           = "amqp://localhost" //+ process.argv[3];
var AMQP_PORT           =5672; //process.argv[4];
//var amqp = require('amqplib/callback_api');
var USER = 'b5f0162c90006cc5ba365f8ffe76aee0';
var MODEL = 'custom';
var EDGE_TYPE = '';
var EDGE_URI = '';

//Static values
var MAC = "3D:F2:C9:A6:B3:4F";
var KIND = "sensor";
var ORGANIZATION = "DISIT"
var FREQUENCY = 10;

var amqp = require('amqplib/callback_api');
const spawn = require('child_process').spawn;
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
     //console.log('Connection established');
});
   

/*functions */

Array.prototype.diff = function(a) {
    return this.filter(function(i) {return a.indexOf(i) < 0;});
};
function insertDevices(cid, se, callback)
{
	//console.log("insertDevices");
    var sqlse  = "INSERT INTO  `temporary_devices`(`username`,`id`,`model`, `kind`,`devicetype`,`protocol`,`frequency`,`format`,`contextBroker`,`latitude`,`longitude`,macaddress,`status`,`validity_msg`,`should_be_registered`,`organization`) VALUES ?";
                          
	cid.query(sqlse, [se], function(errSens) {
		callback();

        if (errSens) {console.log("device" + se); throw errSens;}
        // console.log("fatto");
    });	
}


function insertValues(cid, sesc, callback)
{
	//console.log("insertValues");
	var sqlsesc  = "INSERT INTO `temporary_event_values`(`device`, `cb`,`value_name`, `data_type`,`value_type`,`value_unit`,`healthiness_criteria`,`value_refresh_rate`,`old_value_name`) VALUES ?";
	
	//console.log("insert values");				
	cid.query(sqlsesc, [sesc], function(errSSch) {
		callback();
        if (errSSch) {
			
			throw errSSch;}
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
  attributes = parseOrionJSON(value);
//     console.log("valore processato " + value);

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
	
  var pos=1;
  for (var prop in obj)
  {
    if (prop != "id" && prop != "type")
	{
         // console.log(prop);
         // console.log(obj[prop].type);
         // console.log(obj[prop].value); 
	 attributes.push({"name": prop, "type": obj[prop].type, "value": obj[prop].value,  "position":pos});
	 pos++;
	}
  }
  return attributes;
}

function getSensorID(cb){
   var sensor = []; 
   var sql = "(SELECT id FROM devices WHERE kind = 'sensor' and contextBroker = '" + cb + "') UNION (SELECT id FROM temporary_devices WHERE kind = 'sensor' and contextBroker = '" + cb + "')";
   cid.query(sql, function (err, result, fields) {
     if (err) throw err;
	 // console.log("record selected " + result.length);
     for (i = 0; i < result.length; i++) {
       sensor.push(result[i].id);
   }
  });
 return sensor;  
} 
function storeDevice(user,deviceID,model,kind,type, protocol,frequency, format, cb, latitude,longitude,macaddress,status,validity_msg,shouldberegistered,organization){
  return [user,deviceID,model,kind,type,protocol,frequency,format,cb,latitude,longitude,macaddress,status, validity_msg,shouldberegistered,organization];
}
function storeDeviceSchema(cb, attributes, deviceSchema,deviceID){
  var arr =[];
  var longitude="";
  var latitude="";
  var ob = deviceSchema[deviceID];
  var value_type= ""; 
	
 // console.log("storeDeviceSchema" + JSON.stringify(ob));
	  for (i=0; i < attributes.length; i++)
	  {	
		//console.log("for "+JSON.stringify(attributes));
		att= attributes[i];
		arr.push([deviceID,cb,att.name,"float","temperature","°C","refresh_rate",300,att.name]);
	  }
  //console.log("latitude: "+latitude + " longitude: "+ longitude);
  return {"arr": arr, "latitude": latitude, "longitude": longitude};
}

/*  MAIN PROGRAM */


var output="";

var requestLoop = setInterval(function(){


var buffer=[];

registeredSensors= getSensorID(AMQP_CB);

//var exec = require('child_process').exec;

//exec('rabbitmqctl list_exchanges name type durable', function callback(error, stdout, stderr){
    
  /*  var exch=[];
    var dur=[]
    buffer= stdout.split("\n");

    for (i=0;i<buffer.length;i++)
    {
      if (!buffer[i].trim().startsWith("Timeout") && !buffer[i].trim().startsWith("Listing"))
      {
         cont = buffer[i].trim().split("\t");
		 console.log("oContx "+JSON.stringify(cont[0]));
         if (cont[0]!= "" && !cont[0].startsWith("amq") & cont[1]=="fanout"  && cont[2]!="")
         {
            console.log(cont[0] +"..."+cont[1] +"..."+cont[2]);
	        exch.push(cont[0]);
	        dur[cont[0]]=cont[2];
         }
      } 
    }*/
				    
	var se = [];
	var sesc = [];
	var se2 = [];
	var sesc2 = [];	
	var se3 = [];
	var sesc3 = [];	
	var se4 = [];
	var se5 = [];
	var se6 = [];
	var se7 = [];
	var se8 = [];
	var se9 = [];
	var sesc4= [];		
	var sesc5= [];		
	var sesc6= [];		
	var sesc7= [];		
	var sesc8= [];		
	var sesc9= [];		
	
	var devices=[];
	var attributes=[];
	var newDevices = [];
	var amqpTypes=[];
	
	var promise = new Promise(function(resolve,reject){
		amqp.connect('amqp://localhost', function(err, conn) {
		  conn.createChannel(function(err, ch) {
			var q = 'snap4city';

			ch.assertQueue(q, {durable: true});
			
			//console.log(" [*] Waiting for messages in %s. To exit press CTRL+C", q);

			ch.consume(q, function(msg) {
				console.time('retrieval');
				var array = msg.content.toString();
				//console.log("array "+ array);
				var obj = JSON.parse(array);
				//	console.log(" [x] Received %s" + array[0] );
				devices.push(obj.id);
				attributes[obj.id] = obj;
				 amqpTypes[obj.id]= obj.type;
				//console.log("newDevices length "+devices.length);

				//console.log("devices " + devices.length + array[0] + " nvalues "+ array[1]);
				newDevices=devices.diff(registeredSensors);
				if(newDevices.length == 10)
					resolve();
				}, {noAck: true});
			});
		});
	});
	
	promise.then(function(res){
		//gvar newDevices = registeredSensors;
		//console.log("Devices "+ JSON.stringify(devices) + "nuovi " + newDevices.length);
		//console.log("new dev length " + newDevices.length);
		for (var k=0; k < newDevices.length;k++)
		{
			
			var topic = devices[k];
		//	console.log("topic" + topic);
			var deviceSchema = extractSchema(attributes[topic]);
			var obj1 = storeDeviceSchema(AMQP_CB, deviceSchema.attr, attributes, topic);

			var deviceVal = {"data_type": obj1.arr.data_type, "value_type": obj1.arr[4], "editable":"false", "value_unit":obj1.arr[5],"healthiness_criteria":obj1.arr[6],"healthiness_value":obj1.arr[7]};

			var toVerify ={"name": topic,"username": USER,"contextBroker": AMQP_CB, "id": topic, "model": MODEL, "devicetype":amqpTypes[topic], "protocol":AMQP_PROTOCOL, "format":deviceSchema.format, "frequency": FREQUENCY, "kind":KIND,"latitude":obj1.latitude, "longitude":obj1.longitude,"macaddress":MAC,"deviceValues":deviceVal};
								
			var verify = verifyDevice(toVerify);
			var validity = "invalid";
			if(verify.isvalid)
			validity= "valid";
				
			se.push(storeDevice(USER,topic,MODEL,KIND,amqpTypes[topic],
			AMQP_PROTOCOL,FREQUENCY, deviceSchema.format, AMQP_CB,obj1.latitude,
			obj1.longitude,MAC,validity,verify.message,"no",ORGANIZATION));

			sesc= sesc.concat(obj1.arr);
		
		}
		var promise1 = new Promise(function(resolve, reject) {
			if ( se.length!=0)
			{
				insertDevices(cid, se,(res) =>{
						resolve();
				});
			}
		
		});
		
	//	console.log("sesc "+ sesc);
		promise1.then(function(result) { 
			insertValues(cid, sesc, (res)=>{
			//	console.log("resolve 1");
					console.timeEnd('retrieval');
			});
		});//end promise1
	});
}, 10000);

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
   
	//sconsole.log("device to ver k1 "+ deviceToverify.k1);
      
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