<?php

include_once "./db/databases.php";
include_once "./db/contextbroker.php";


$cb = new contextbroker();

// , `created`
$query = "SELECT `name`, `ip`, `port`, `type` FROM contextbroker ORDER BY name";

$cb->query($query);

if ($cb->error_status!=1)
{
     echo "<div class=\"panel panel-default\">
    <div class=\"panel-heading\"><h1 class=\"my-4\">Context Brokers</h1></div>
	<div class=\"panel-body\">
        <div class=\"alert alert-danger\">
             Si &eacute; The following error has been detected: $cb->msg[$cb->error_status]
        </div>		
	   </div>
   </div>";
}
else
{
  ?>
    <div class="panel-heading"><h1 class="my-4">Context Brokers</h1></div>
    <div class="panel-body">
	
    <?php  // , 'Creation Date'    
	       $cb->visualizza(array('Name', 'IP Address',  'Port', 'Protocol')); ?>
	</div>
	</div>
	<?php
		  
}

unset($cb);  

?>