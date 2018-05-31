<?php 
    $availableClasses= array(1 => "alert alert-success", 2 => "alert alert-info", 3 => "alert alert-warning", 4 => "alert alert-danger");
    $msg= array(0=>'Problemi di connessione al server MySQL',
			   1=>'ok',
               2=>'Il record che vuoi cancellare non esiste',
			   3=>'Il record che vuoi modificare non esiste',
			   4=>'Il record che vuoi recuperare non esiste',
			   5=>'Una delle colonne specificate non appartengono alla tabella',
			   6=>'Specificato valore nullo per un attributo',
			   7=>'Specificato valore non congruo per una lista',
			   8=>'Valore non valido per un tipo SET',
			   9=>'Errore nel login'
			   );




	
	switch ($_GET["status"]) {
    case "21":
        $classe= $availableClasses[4];
		$messaggio= "<b>Errore di accesso al database</b>. <br/> Consultare tecnico";
		break;
    case "22":
        $classe= $availableClasses[4];
		$messaggio= "<b>Playlist cercata non trovata</b>";
        break;
    case "23":
        $classe= $availableClasses[4];
		$messaggio= "";
        break;
    default:
       $classe= $availableClasses[4];
		$messaggio= "Si &eacute; verificato l'errore $_GET[status]: <b>" .  $msg[$_GET["status"]] . "</b><br/>";
		if (isset($_GET["msg"])) $messaggio .= $_GET["msg"];
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