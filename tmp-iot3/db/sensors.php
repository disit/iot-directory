<?php

/*
CREATE TABLE `sensors` (
  `contextBroker` varchar(20) NOT NULL,
  `id` varchar(40) NOT NULL,
  `status` set('unmapped','partial','mapped','') NOT NULL DEFAULT 'unmapped',
  `astatus` set('mapped','partial','unmapped','') NOT NULL DEFAULT 'unmapped',
  `macaddress` varchar(20) DEFAULT NULL,
  `type` varchar(20) DEFAULT NULL,
  `model` varchar(20) DEFAULT NULL,
  `producer` varchar(20) DEFAULT NULL,
  `longitude` varchar(20) DEFAULT NULL,
  `latitude` varchar(20) DEFAULT NULL,
  `kind` set('sensor','actuator') NOT NULL,
  `protocol` set('amqp','mqtt','ngsi','') NOT NULL,
  `format` set('csv','json','xml','') NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


*/
class sensors extends Database
{
 private  $columns = array('id'  => 'string',
                'status' => 'string', 
                'astatus' => 'string', 
                'macaddress'  => 'string', 
				'type'  => 'string',
				'model'  => 'string',
				'producer'  => 'string',
				'longitude'  => 'string',
				'latitude'  => 'string',
				'kind'  => 'string',
				'protocol'  => 'string',
				'format'  => 'string',
				'contextBroker'  => 'string',
				'created'  => 'string'
			  );

public function creaForm(){	
			echo "<div class=\"table-responsive\">\n";
			echo "\t<table class=\"table\" id=\"dataTables-creaForm\">\n";
			echo "\t\t<tbody>\n";
		
			echo "\t\t</tbody>\n \t</table>\n";
			echo "</div>\n";
			
		//echo "</form>";
		
}	
				  
public function visualizza(array $val_cols, $status){

	echo "<div class=\"table-responsive\">\n";
	echo "\t<table class=\"table table-striped table-bordered table-hover\" id=\"dataTables-example\">\n";
	echo "\t\t<thead>\n\t\t<tr>\n";
	foreach ($val_cols as $column) echo "\t\t\t<th>$column</th>\n";
	
	// echo "\t\t\t<th>Details</th>\n";
	echo "\t\t\t<th>Schema</th>\n";
	echo "\t\t\t<th>Modify</th>\n";
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
			 /* details */
			 // echo "\t\t\t\t<td><a class=\"btn btn-success btn-circle\" href=\"index.php?op=detailSensor&page=listSensors&id=" . $row["id"] . "&cb=" . $_SESSION["cb"] . "\"><i class=\"fa fa-link\"></i></a></td>\n";
			 /* schema details */
			 echo "\t\t\t\t<td><a class=\"btn btn-success btn-circle\" 
			 data-toggle=\"modal\"  data-target=\"#charts-modal1\" onClick=\"showSchema('$row[id]','$_SESSION[cb]','$status')\" 
			 href=\"index.php?op=detailSensorSchema&page=listSensors&id=" . $row["id"] . "&cb=" . $_SESSION["cb"] . "&status=" . $status . "\"><i class=\"fa fa-link\"></i></a></td>\n";
			 /* modify */
			 echo "\t\t\t\t<td><a class=\"btn btn-warning btn-circle\" href=\"index.php?op=modifySensor&page=listSensors&id=" . $row["id"] .  "&cb=" . $_SESSION["cb"] . "&status=" . $status .  "\"><i class=\"fa fa-pencil\"></i></a></td>\n";
	         /* delete */
			echo "\t\t\t\t<td><a class=\"btn btn-danger btn-circle\"  onclick=\"return ConfirmDelete('Sensor','$row[id]','$_SESSION[cb]');\" href=\"php/deleteSensor.php?page=listSensors&id=" . $row["id"] . "&cb=" . $_SESSION["cb"] . "&status=". $status .  "\"><i class=\"fa fa-times\"></i></a></td>\n";
		
		echo "\t\t\t</tr>";	 
	}
	echo "\t\t</tbody>\n \t</table>\n"; 
}
				  
				  

public function visualizzaSensore(){	
			echo "<div class=\"table-responsive\">\n";
			echo "\t<table class=\"table\" id=\"dataTables-creaForm\">\n";
			echo "\t\t<tbody>\n";
			
			echo  "\t\t\t<tr class=\"even\">\n";
			
			   echo "\t\t\t\t<td>Sensor Id </td>";
			   
			   echo "\t\t\t\t<td> 
			   		  <input class=\"form-control\" type=\"text\" value= \"" . $this->result[0]["id"] . "\" readonly/></td>";
			echo "\t\t\t</tr>";
			
			echo  "\t\t\t<tr class=\"even\">\n";
			
			   echo "\t\t\t\t<td>MAC address </td>";
			   
			   echo "\t\t\t\t<td> 
			   		  <input class=\"form-control\" type=\"text\" value= \"" . $this->result[0]["macaddress"] . "\" readonly/></td>";
            echo "\t\t\t</tr>";
			echo  "\t\t\t<tr class=\"even\">\n";
			
			   echo "\t\t\t\t<td>Type </td>";
			   echo "\t\t\t\t<td> 
			   		  <input class=\"form-control\" type=\"text\"value= \"" . $this->result[0]["type"] . "\" readonly/></td>";   
			  
			echo "\t\t\t</tr>";
			
			echo  "\t\t\t<tr class=\"even\">\n";
			
			   echo "\t\t\t\t<td>Model </td>";
			   echo "\t\t\t\t<td> 
			   		  <input class=\"form-control\" type=\"text\"value= \"" . $this->result[0]["model"] . "\" readonly/></td>";   
			  
			echo "\t\t\t</tr>";
			
			echo  "\t\t\t<tr class=\"even\">\n";
			
			   echo "\t\t\t\t<td>Producer </td>";
			   echo "\t\t\t\t<td> 
			   		  <input class=\"form-control\" type=\"text\"value= \"" . $this->result[0]["producer"] . "\" readonly/></td>";   
			  
			echo "\t\t\t</tr>";
			
			echo  "\t\t\t<tr class=\"even\">\n";
			
			   echo "\t\t\t\t<td>Location (latitude,longitude)</td>";
			   echo "\t\t\t\t<td> 
			   		  <input class=\"form-control\" type=\"text\"value= \"(" . $this->result[0]["latitude"] . ","  . $this->result[0]["latitude"] . ")\" readonly/></td>";   
			  
			echo "\t\t\t</tr>";
			
			echo  "\t\t\t<tr class=\"even\">\n";
			
			   echo "\t\t\t\t<td>Kind </td>";
			   echo "\t\t\t\t<td> 
			   		  <input class=\"form-control\" type=\"text\"value= \"" . $this->result[0]["kind"] . "\" readonly/></td>";   
			  
			echo "\t\t\t</tr>";
			
			echo  "\t\t\t<tr class=\"even\">\n";
			
			   echo "\t\t\t\t<td>Protocol </td>";
			   echo "\t\t\t\t<td> 
			   		  <input class=\"form-control\" type=\"text\"value= \"" . $this->result[0]["protocol"] . "\" readonly/></td>";   
			  
			echo "\t\t\t</tr>";
			
			echo  "\t\t\t<tr class=\"even\">\n";
			
			   echo "\t\t\t\t<td>Format </td>";
			   echo "\t\t\t\t<td> 
			   		  <input class=\"form-control\" type=\"text\"value= \"" . $this->result[0]["format"] . "\" readonly/></td>";   
			  
			echo "\t\t\t</tr>";
			
			echo  "\t\t\t<tr class=\"even\">\n";
			
			   echo "\t\t\t\t<td>Creation Date </td>";
			   echo "\t\t\t\t<td> 
			   		  <input class=\"form-control\" type=\"text\"value= \"" . $this->result[0]["created"] . "\" readonly/></td>";   
			  
			echo "\t\t\t</tr>";
			echo "\t\t</tbody>\n \t</table></div>\n";                                  
		//echo "</form>";
}	


				  
			  
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
			 break;
			}
		
		// seguono altri controlli specifici per le colonne di queta tabella
		if ($col=="kind" && !($value=="sensor" || $value=="actuator"))
		{
		  // il controllo di tipo del valore ha restituito un errore
			 $this->error_status=8;
			 $error=1;
			 break;
		}
		if ($col=="protocol" && !($value=="AMQP" || $value=="MQTT" || $value=="NGSI"))
		{
		  // il controllo di tipo del valore ha restituito un errore
			 $this->error_status=8;
			 $error=1;
			 break;
		}
		if ($col=="format" && !($value=="CSV" || $value=="JSON" || $value=="XML"))
		{
		  // il controllo di tipo del valore ha restituito un errore
			 $this->error_status=8;
			 $error=1;
			 break;
		}
	 }
	 else
	 {
	   $error=1; 
	   $this->error_status=5;
	   break;
	 }
  }
  if ($error) return 0;
  else return $checked_val_cols;
}
  
// $val_cols si aspetta un array indicizzato sui nome delle colonne della tabella che non abbia valore nullo
public function insert(array $val_cols){
  $checked_val_cols=$this->checkInputUpdate($val_cols);
  // se non Ë un array si Ë verificato un errore e restituisco il numero di errore

  if ($checked_val_cols==0) return $checked_val_cols;
  // atrimenti proseguo per l'inserimento
   //echo "dopo";
  return parent::insert($checked_val_cols); 
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
        // else $checked_val_cols[$col]="";		
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
