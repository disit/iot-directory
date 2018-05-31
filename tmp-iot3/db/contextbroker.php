<?php

/*
CREATE TABLE `contextbroker` (
  `name` varchar(20) PRIMARY KEY,
  `type` set('NGSI','MQTT','AMQP','COAP') NOT NULL,
  `ip` varchar(20) NOT NULL,
  `port` varchar(5) NOT NULL,  
  `login` varchar(20) DEFAULT NULL,
  `password` varchar(20) DEFAULT NULL,
  `latitude` varchar(20) DEFAULT NULL,
  `longitude` varchar(20) DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,

  
); 
*/

class contextbroker extends Database
{
 private  $columns = array(
				'name'  => 'string',
				'type'  => 'string',
				'ip'  => 'string',
				'port'  => 'string',
				'login'  => 'string',
				'password'  => 'string',
				'latitude'  => 'string',
				'longitude'  => 'string',
				'created' => 'string'
			  );

private function checkInput(array $val_cols)
{	
  $checked_val_cols=array();
  $error=0;
  
  foreach ($val_cols as $col => $value)
  {
     if (array_key_exists($col,  $this->columns))
	 { // il nome di campo Ë previsto nella tabella
	   
	   
	    $checked_val_cols[$col]=$this->check($this->columns[$col], $value);
	    if ($checked_val_cols[$col]==null) 
			{// il controllo di tipo del valore ha restituito un errore
			 $this->error_status=6;
			 $error=1;
			 echo $col;
			 break;
			}
		// seguono altri controlli specifici per le colonne di queta tabella
		
	 }
	 else
	 {
	   $error=1; 
	   $this->error_status=5;
	   echo $col;
	   break;
	 }
  
  }
  if ($error)  return 0;
  else return $checked_val_cols;
}

private function checkInputUpdate(array $val_cols)
{	
  $checked_val_cols=array();
  $error=0;
  
  foreach ($val_cols as $col => $value)
  {
     if (array_key_exists($col,  $this->columns))
	 { // il nome di campo Ë previsto nella tabella
	    if (!empty($value)) 
	    { $checked_val_cols[$col]=$this->check($this->columns[$col], $value);
	      if ($checked_val_cols[$col]==null) 
			{// il controllo di tipo del valore ha restituito un errore
			 $this->error_status=6;
			 $error=1;
			 echo $col;
			 break;
			}
		}
        else $checked_val_cols[$col]="";		
		// seguono altri controlli specifici per le colonne di queta tabella
		
	 }
	 else
	 {
	   $error=1; 
	   $this->error_status=5;
	   echo $col;
	   break;
	 }
  
  }
  if ($error)  return 0;
  else return $checked_val_cols;
}


public function visualizza(array $val_cols){

	echo "<div class=\"table-responsive\">\n";
	echo "\t<table class=\"table table-striped table-bordered table-hover\" id=\"dataTables-example\">\n";
	echo "\t\t<thead>\n\t\t<tr>\n";
	foreach ($val_cols as $column) echo "\t\t\t<th>$column</th>\n";
	
	echo "\t\t\t<th>Access</th>\n";
	echo "\t\t\t<th>Modify</th>\n";
	echo "\t\t\t<th>Stub</th>\n";
	echo "\t\t\t<th>Delete</th>\n";
	echo "\n\t\t</tr>\n\t\t</thead>\n\t\t<tbody>\n";
	
	$pos=0;
	foreach ($this->result  as $row)
	{
	  if ($pos)
			{ echo "\t\t\t<tr class=\"even\">\n"; $pos=0; }
		else
			{ echo "\t\t\t<tr class=\"odd\">\n"; $pos=1;}
        
		//print_r($row);
		
		foreach ($row as $col) echo  "\t\t\t\t<td>" . htmlspecialchars_decode($col), "</td>\n";
		/* management of cb */
		echo "\t\t\t\t<td><a class=\"btn btn-success btn-circle\" href=\"php/activateCB.php?id=" . $row["name"] .  "\"><i class=\"fa fa-link\"></i></a></td>\n";
		/* modification of cb */
			 echo "\t\t\t\t<td><a class=\"btn btn-warning btn-circle\" href=\"index.php?op=modifyCB&page=cb&id=" . $row["name"] .  "\"><i class=\"fa fa-pencil\"></i></a></td>\n";
	    /* management of stub */
		echo "\t\t\t\t<td><a class=\"btn btn-danger btn-circle\" 
		id=\"$row[name]\" onclick=\"return activateStub('$row[name]','$row[ip]','$row[port]','$row[type]');\"><i class=\"fa fa-stop\"></i></a></td>\n";
		/* delete of cb */
		echo "\t\t\t\t<td><a class=\"btn btn-danger btn-circle\"  onclick=\"return ConfirmDelete('Context Brokers','$row[name]','$row[port]');\" href=\"php/deleteCB.php?page=cb&id=" . $row["name"] .  "\"><i class=\"fa fa-times\"></i></a></td>\n";
		
		echo "\t\t\t</tr>";	 
	}
	echo "\t\t</tbody>\n \t</table>\n"; 
}
  
// $val_cols si aspetta un array indicizzato sui nome delle colonne della tabella che non abbia valore nullo
public function insert(array $val_cols){
  $checked_val_cols=$this->checkInput($val_cols);
  // se non Ë un array si Ë verificato un errore e restituisco il numero di errore
  
  if ($checked_val_cols==0) { $this->error_message=$checked_val_cols; $this->error_status=9;}  
  // atrimenti proseguo per l'inserimento
  else parent::insert($checked_val_cols); 
}


public function update(array $set_val_cols, array $cod_val_cols)
{
  $checked_set_val_cols=$this->checkInputUpdate($set_val_cols);
  // se non Ë un array si Ë verificato un errore e restituisco il numero di errore
  
  if (!is_array($checked_set_val_cols)) return $checked_set_val_cols;
  // atrimenti proseguo con la modifica
  return parent::update($checked_set_val_cols, $cod_val_cols);
}
	 
}


?>