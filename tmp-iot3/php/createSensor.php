<?php



if (!isset($_GET["errore"]))
{
  $errore= array();
      $dati= array("id" => "",
	             "macaddress"=> "",
                 "type"=> "",
                 "model"=>"",
	             "producer" => "",
				 "longitude" => "",
				 "latitude" => "",
				 "kind" => "",
				 "protocol" => "",
				 "format" => "",
				 "contextBroker" => "");
}
else
{
  $dati = unserialize($_GET['dati']);
  $errore = unserialize($_GET['errore']);
}  
?>

<div class="col-lg-6 mb-4">
<div class="well well-lg">

<form role="form" method="POST" action="php/createSensor-exe.php">


<div class="form-group">
      <label>Identifier </label> <span class="text-danger pull-right"><?php if (isset($errore["id"])) echo $errore["id"];?></span>
      <input class="form-control" type="text" name="id" value="<?php echo $dati["id"];?>">
</div>


<div class="form-group">
      <label>Mac Address </label> <span class="text-danger pull-right"><?php if (isset($errore["macaddress"])) echo $errore["macaddress"];?></span>
      <input class="form-control" type="text" name="macaddress" value="<?php echo $dati["macaddress"];?>">
</div>

<div class="form-group">
      <label>Type </label> <span class="text-danger pull-right"><?php if (isset($errore["type"])) echo $errore["type"];?></span>
      <input class="form-control" type="text" name="type" value="<?php echo $dati["type"];?>">
</div>


<div class="form-group">
      <label>Model</label><span class="text-danger pull-right"><?php if (isset($errore["model"])) echo $errore["model"];?></span>
      <input class="form-control" type="text" name="model" value="<?php echo $dati["model"];?>">
</div>

<div class="form-group">
      <label>Producer</label><span class="text-danger pull-right"><?php if (isset($errore["producer"])) echo $errore["producer"];?></span>
      <input class="form-control" type="text" name="producer" value="<?php echo $dati["producer"];?>">
</div>

<div class="form-group">
      <label>Latitude</label><span class="text-danger pull-right"><?php if (isset($errore["latitude"])) echo $errore["latitude"];?></span>
      <input class="form-control" type="text" name="latitude" value="<?php echo $dati["latitude"];?>">
</div>

<div class="form-group">
      <label>Longitude</label><span class="text-danger pull-right"><?php if (isset($errore["longitude"])) echo $errore["longitude"];?></span>
      <input class="form-control" type="text" name="longitude" value="<?php echo $dati["longitude"];?>">
</div>

<div class="form-group">
    <label>Kind</label>
	    <select name="kind" class="form-control">
               <option value="sensor"  <?php if ($dati["kind"]=="sensor") echo "selected"; ?>>sensor</option>
			   <option value="actuator"   <?php if ($dati["kind"]=="actuator") echo "selected"; ?>>actuator</option>
        </select>
</div>  
  
<div class="form-group">
    <label>Protocol</label>
	    <select name="protocol" class="form-control">
               <option value="AMQP"  <?php if ($dati["protocol"]=="AMQP") echo "selected"; ?>>AMQP</option>
			   <!-- <option value="COAP"  <?php if ($dati["protocol"]=="COAP") echo "selected"; ?>>COAP</option>
			   -->
			   <option value="MQTT"   <?php if ($dati["protocol"]=="MQTT") echo "selected"; ?>>MQTT</option>
			   <option value="NGSI"   <?php if ($dati["protocol"]=="NGSI") echo "selected"; ?>>NGSI</option>
        </select>
</div>  
  
<div class="form-group">
    <label>Format</label>
	    <select name="format" class="form-control">
               <option value="CSV"  <?php if ($dati["format"]=="CSV") echo "selected"; ?>>CSV</option>
			   <option value="JSON"   <?php if ($dati["format"]=="JSON") echo "selected"; ?>>JSON</option>
			   <option value="XML"   <?php if ($dati["format"]=="XML") echo "selected"; ?>>XML</option>
        </select>
</div>
  
  
 <!-- `id`,`macaddress`,`type`,`model`,`producer`,`longitude`,`latitude`,`kind` set('sensor','actuator'),`protocol` set('AMQP','MQTT','NGSI',''),`format` set('CSV','JSON','XML',''),
  `contextBroker`,`created` -->
  
  
<div class="form-group">
      <label>Creation date</label><span class="text-danger pull-right"><?php if (isset($errore["created"])) echo $errore["created"];?></span>
      <input class="form-control" type="text" name="created" value="<?php if (isset($dati["created"])) echo $dati["created"];?>" readonly>
</div>

<input type="submit" class="btn btn-primary" value="Create"></input>
<input type="reset" class="btn btn-success" value="Cancel"></input>
</form>
</div>
</div>
