<?php

include_once "../db/databases.php";
include_once "../db/attributes.php";
include_once "../db/semanticlabel.php";

$sensorID=$_GET["id"];
$cbID=$_GET["cb"];
$status = $_GET["status"];


$lab= new semanticlabel();

$lab->fetchRecord(array("1"=>"1"));

$attr = new attributes();


$attr->fetchRecord(array("sensor"=>$sensorID,"cb"=>$cbID));


if ($attr->error_status!=1)
{
     echo "<div class=\"panel panel-default\">
    <div class=\"panel-heading\"><h1 class=\"my-4\">Sensors</h1></div>
	<div class=\"panel-body\">
        <div class=\"alert alert-danger\">
             Si &eacute; The following error has been detected: $attr->msg[$attr->error_status]
        </div>		
	   </div>
   </div>";
}
else
{ 
  $target= "php/updateAttributes-exe.php?numAttr=". $attr->fetchNumRows() . "&status=" . $status. "&sensorID=" . $sensorID. "&cb=" . $cbID;

  ?>
  
  
    <div class="panel-body">
	<form role="form" method="POST" action="<?php echo $target;?>">

    <?php      $attr->attributeForm($lab->setOfLabels()); ?>
	  <input type="submit" class="btn btn-primary" value="Update"></input>
      <input type="reset" class="btn btn-success" value="Cancel"></input>
	</form>
	</div>
	</div>
	<?php
		  
}

unset($attr);  

?>
