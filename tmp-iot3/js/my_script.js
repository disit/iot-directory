
var _serviceIP = ".."; "https://iot-app.snap4city.org/iotdirectory";


function toggleField(hideObj,showObj){
  hideObj.disabled=true;        
  hideObj.style.display='none';
  showObj.disabled=false;   
  showObj.style.display='inline';
  showObj.focus();
}

function ConfirmDelete(who, name, ip)
{
  var msg= "Are you sure to delete the " + who + " " + name + " with IP " + ip + "?";
  var x = confirm(msg);
  if (x)
      return true;
  else
    return false;
}

function ajaxRequest()
{var request=false;
  try { request = new XMLHttpRequest()}catch(e1){
	try{request = new ActiveXObject("Msxml2.XMLHTTP")}catch(e2){
		try{ request = new ActiveXObject("Microsoft.XMLHTTP")
		}catch(e3){request = false}
	}
  }
  return request
}


function aggiornaStub()
{
    var service = _serviceIP + "/api/status";
	var xhr = ajaxRequest();
    console.log("entratos"); 
	xhr.addEventListener("readystatechange", function () {
	  if (this.readyState === 4 && this.status == 200) {
		console.log(this.responseText);
		var activeStub = JSON.parse(JSON.parse(this.responseText).message);
		console.log(activeStub);
		for (var i=0; i < activeStub.length; i++)
		{
		    var cb=activeStub[i];
			console.log(cb);
			$("#" + cb).removeClass();
			$("#" + cb).addClass("btn btn-success btn-circle");
			// $("#" + cb + " > i").removeClass();
			$("#" + cb + " > i").addClass("fa fa-cogs");
			$("#" + cb).prop('onclick',null).off('click');
		}
	  }
	});
	xhr.open("GET", service);
	// xhr.setRequestHeader("Cache-Control", "no-cache");
	xhr.send();
	return true;
}

function activateStub(cb,ip,port,protocol)
{
    
	var data = "contextbroker=" + cb + "&ip=" + ip + "&port=" + port;
    var service = _serviceIP + "/api/"+protocol;
	console.log(data);
	console.log(service);
	var xhr = ajaxRequest();

	xhr.addEventListener("readystatechange", function () {
	  if (this.readyState === 4 && this.status == 200) {
		console.log(this.responseText);
		$("#" + cb).removeClass();
		$("#" + cb).addClass("btn btn-success btn-circle");
		// $("#" + cb + " > i").removeClass();
		$("#" + cb + " > i").addClass("fa fa-cogs");
		$("#" + cb).prop('onclick',null).off('click');
	  }
	});

	xhr.open("POST", service);
	xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	// xhr.setRequestHeader("Cache-Control", "no-cache");

	xhr.send(data);
	return true;
}

function concludiCB()
{
		xhr=ajaxRequest()
		xhr.open("GET","php/closeCB.php", false);
		
		xhr.onreadystatechange = function() {
				if (this.readyState == 4 && this.status == 200) {
					console.log(this.responseText);
					 window.location.href = 'index.php?op=ok&status=6';
				}
			};
			
		xhr.send();

}


function showSchema(sensorID, sensorCB, status)
{	
  	xhr=ajaxRequest();
	xhr.open("GET","php/attributeForm.php?id=" + sensorID + "&cb="+ sensorCB + "&status="+ status, false);
     
	xhr.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
			    //console.log(this.responseText);
				return this.responseText;
            }
        };
		
	xhr.send();
	$("#areaForm").attr("sensorID", sensorID);
	$("#areaForm").attr("sensorCB", sensorCB);
	$("#areaForm").html(xhr.responseText);
	// $("#areaForm").attr("data", window.btoa(xhr.responseText));
	console.log("#areaForm");
}


$(document).ready(function() {
    $('#dataTables-example').DataTable();
	aggiornaStub();
} );


/*		
$(window).on('load', function(){
    $('#charts-modal1').on('show.bs.modal', function (event) {
        setTimeout(function(){
		    //console.log(event); 
		    $("#areaForm").html(window.atob(areaForm.getAttribute("data")));
			 
			$(".js-loading").css("visibility", "hidden");
    	},1000);
    });
});
*/

function my_dismiss()
{

  // salvataggio dei dati nel db  
  console.log("fatto");
}


