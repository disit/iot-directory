<?php

/*
CREATE TABLE `attributes` (
  `cb` varchar(20) NOT NULL,
  `sensor` varchar(40) NOT NULL,
  `name` varchar(20) NOT NULL,
  `type` varchar(20) NOT NULL,
  `order` int(11) NOT NULL,
  `slabel` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
*/
class attributes extends Database
{
 private  $columns = array('cb'  => 'string',
                'sensor'  => 'string', 
				'name'  => 'string',
				'type'  => 'string',
				'order'  => 'string',
				'slabel'  => 'string'
			  );

public function attributeForm($labels){
            echo "<div class=\"table-responsive\">\n";
			echo "\t<table class=\"table\" id=\"dataTables-creaForm\">\n";
			echo "\t\t<thead>\n\t\t<tr>\n";
            echo "\t\t\t<th>Name</th>\n";
	        echo "\t\t\t<th>Type</th>\n";
	        echo "\t\t\t<th>Semantic label</th>\n";
 	        echo "\n\t\t</tr>\n\t\t</thead>\n\t\t<tbody>\n";
			
			$pos=0;
			foreach ($this->result  as $row)
			{
				if ($pos)
					{ echo "\t\t\t<tr class=\"even\">\n"; $pos=0; }
				else
					{ echo "\t\t\t<tr class=\"odd\">\n"; $pos=1;}
			
			    //foreach ($row as $col) 
				echo  "\t\t\t\t<td>" . htmlspecialchars_decode($row["name"]), "</td>\n";
				echo  "\t\t\t\t<td>" . htmlspecialchars_decode($row["type"]), "</td>\n";
				
				echo  "\t\t\t\t<td>
					    <select name=\"$row[name]\" class=\"form-control\"	onchange=\"if(this.options[this.selectedIndex].value=='customOption'){toggleField(this,this.nextSibling);              this.selectedIndex='0';}\"
						
						>";
						echo "<option value=\"\"></option>";
						echo "<option value=\"customOption\">[type a custom value]</option>";
				foreach($labels as $label)
                {
				  $selected= (strtolower($label)==strtolower(trim($row["name"])))?"selected":"";
				  echo "<option value=\"$label\" $selected>$label</option>";
				}				
				echo "</select><input name=\"$row[name]\" class=\"form-control\" style=\"display:none;\" disabled=\"disabled\" onblur=\"if(this.value==''){toggleField(this,this.previousSibling);}\"></td>\n";
				echo "</tr>\n";
			}
			echo "\t\t</tbody>\n \t</table>\n";
			echo "</div>\n";
			
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