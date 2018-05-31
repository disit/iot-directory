<?php

include_once "./db/databases.php";
include_once "./db/sensors.php";


$sensor = new sensors();

$query = "SELECT  `id`, `type`, `kind`, `created` FROM `sensors` 
          WHERE `status`='" . $_GET["status"] . "' AND `contextBroker`='" . $_SESSION["cb"] ."';";

		  
		  
$sensor->query($query);

if ($sensor->error_status!=1)
{
     echo "<div class=\"panel panel-default\">
    <div class=\"panel-heading\"><h1 class=\"my-4\">Sensors</h1></div>
	<div class=\"panel-body\">
        <div class=\"alert alert-danger\">
             Si &eacute; The following error has been detected: $sensor->msg[$sensor->error_status]
        </div>		
	   </div>
   </div>";
}
else
{ 
  ?>
    <div class="panel-heading"><h1 class="my-4">Devices</h1></div>
    <div class="panel-body">
    <?php      $sensor->visualizza(	array('Identifier', 'Type',  'Kind', 'Creation Date'), $_GET["status"]); ?>
	</div>
	</div>
	<?php
		  
}

unset($sensor);  

?>


<div id="charts-modal1" class="modal fade charts-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
       <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="my_dismiss()"><span aria-hidden="true">X</span></button>
            <h4 class="modal-title" id="myModalLabel">Semantic Labeling of Sensor Attributes</h4>
      </div>    
        <!-- <div class="js-loading text-center">
            <h3>Caricamento...</h3>
        </div> -->
        <div id="areaForm"></div>
        <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-dismiss="modal" onclick="my_dismiss()">Close</button>
        </div>    
    </div>
  </div>
</div>
