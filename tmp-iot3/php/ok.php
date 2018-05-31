<?php 
    $availableClasses= array(1 => "alert alert-success", 2 => "alert alert-info", 3 => "alert alert-warning", 4 => "alert alert-danger");
	
	switch ($_GET["status"]) {
    case "1":
        $classe= $availableClasses[1];
		$messaggio= "<b>Operation succeded</b>.";
		break;
	case "4":
        $classe= $availableClasses[1];
		$messaggio= "<b>Working with a Context Broker</b>.";
        break;
   case "6":
        $classe= $availableClasses[1];
		$messaggio= "<b>Closed the current Context Broker</b>.";
        break;		
    default:
       $classe= $availableClasses[1];
		$messaggio= "Si è verificato un errore che non &eacute; stato codificato";
}					
?>

<div class="panel panel-default">
    <div class="panel-heading"></div>
	<div class="panel-body">
        <div class="<?php echo $classe;?>">
            <?php echo $messaggio;?>
        </div>               
						
	</div>
</div>