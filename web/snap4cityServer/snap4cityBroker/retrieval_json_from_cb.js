var ORION_CB = process.argv[2]; 
var ORION_ADDR = process.argv[3];
var ACCESS_LINK = process.argv[4];
var PATH = process.argv[5];
var KINDBROKER = process.argv[6];
var APIKEY = process.argv[7];

/* global variables */
var registeredDevices = [];
var orionDevices= [];
var orionDevicesSchema= new Object();

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
 
/*  MAIN PROGRAM */

var link ="";
var limit = 1000;
var offset2 = 900;
var offset = 0;
var smallSearch=0;

if(KINDBROKER.localeCompare("external")==0){
//	console.log("localeCompare if external");
	if(PATH.localeCompare("null")==0 || PATH.localeCompare("")==0  || PATH == undefined){
		link = ACCESS_LINK;
	}
	else{
		link = ACCESS_LINK+ PATH;	
	}
	//console.log("if link "+link);
}
else{
	if(PATH.localeCompare("null") ==0 || PATH == undefined || PATH == null || PATH.localeCompare(" ")==0){
		link = 'http://'+ORION_ADDR;
	//	console.log("if link "+ link);

	}
	else{
		link = 'http://'+ORION_ADDR+PATH;
//	console.log("else link "+ link);

	}
}
	var xhttp = new XMLHttpRequest();  

function retrieveData(xtp, link, limit, offset){
	
	var promiseAcquisition = new Promise(function(resolve2, reject){	
		xhttp = new XMLHttpRequest();  
		var linkNoLimit = link.split("?limit");
		link = linkNoLimit[0];

		link= link+"?limit="+limit+"&offset="+offset;
		console.log("Link split "+link);
			
		if(APIKEY !== null || APIKEY !== undefined){
			xhttp.open("GET", link, true);
			xhttp.setRequestHeader("apikey",APIKEY);
			xhttp.send(); 
		}//end if APIKEY != NULL
		else
		{ 
			xhttp.open("GET", link, true);
			xhttp.send(); 
		}
	

		xhttp.onreadystatechange = function() {

			if(this.status == 404) {
				console.log("not found");
			}
			//console.log("readyState " + this.readyState + " status " + this.status + this.responseText );

			if (this.readyState == 4 && this.status == 200) {
				console.log("ready");
				//function that manages the output in order to create the data
			var responseText = this.responseText;
			//variable initialization
			orionDevices= [];
			orionDevicesSchema= new Object();
		 
			var obj = JSON.parse(responseText);
			httpRequestOutput="";
			
			if (obj instanceof Array)
			{

			console.log("length obj "+obj.length);
			//console.log("obj "+obj);
				for (i=0; i < obj.length; i++) {
				//	console.log("obkid: " + obj[i].id);
					let index= obj[i].id;					
					orionDevices.push(index);
					orionDevicesSchema[index]= obj[i];
				}
			
			}
			else
			{ 
				console.log("not an array, id "+ obj.id);
				orionDevices.push(obj.id);			
				orionDevicesSchema[index]= obj;
			}
					 			
			var sql = "(SELECT id FROM temporary_devices WHERE contextBroker = '" + ORION_CB + "') UNION (SELECT id FROM devices WHERE contextBroker = '" + ORION_CB + "')";

			cid.query(sql, function (err, result, fields) {
				if (err) {console.log("sql "+sql); throw err;}
				for (i = 0; i < result.length; i++) {
				  registeredDevices.push(result[i].id);
				}
				
				//checking if the devices already exist in the platform
				console.log("registeredDevices " +registeredDevices.length + " orion length "+ orionDevices.length);
				var newDevices=orionDevices.diff(registeredDevices);
				console.log("diff " +newDevices.length);

				newDevices= removeDuplicates(newDevices);

				var jsonDevices="";
				newDevices.forEach(function(element) {

					jsonDevices = jsonDevices+ JSON.stringify(orionDevicesSchema[element]);
				});
				console.log(JSON.stringify(jsonDevices));
				
			}); //query
	
				
			}//end readystate == 4
			if (this.readyState == 4 && this.status == 500) {
				console.log("reject");			
				reject();
			}
		};//end onreadystatechange
	});//end promiseAcquisition
		
	promiseAcquisition.then(function(resolve2) { 
		console.log("result " + limit + " off ty" + offset);
		
		if(!smallSearch){
			offset2 = offset2+100;

			console.log("promise then ok3 " + offset2);
			
			xhttp = new XMLHttpRequest();  

			retrieveData(xhttp, link, 100, offset2);
			  console.log("**UPDATE**");
		}
		else{
			offset2 = offset2+1;
			xhttp = new XMLHttpRequest();  

			retrieveData(xhttp, link, 1, offset2);		
		}
	  },
	  function(error) {
			console.log("promise then error");

		if(!smallSearch){
			smallSearch=1;
			retrieveData(xhttp, link, 1, offset2);
			//Do not remove this log
		}	  
	  });

}//end retrieveData function
/*
var requestLoop = setInterval(function(){
	retrieveData(xhttp, link, limit, offset);
	registeredDevices = [];
}, 20000);*/
retrieveData(xhttp, link, limit, offset);


 /*functions */
Array.prototype.diff = function( arr ) {
	//console.log ("diff");
  return this.filter( function( val ) {
	  //console.log("arr.index "+ arr.indexOf(val)+ " val "+ val);
    return arr.indexOf( val ) < 0;
  });
};

function removeDuplicates(arr){
    let unique_array = []
    for(let i = 0;i < arr.length; i++){
        if(unique_array.indexOf(arr[i].replace("-","")) == -1){
            unique_array.push(arr[i])
        }
    }
    return unique_array
}