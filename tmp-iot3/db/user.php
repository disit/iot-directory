<?php

/*
CREATE TABLE user (
  id int AUTO_INCREMENT,
  name varchar(50) NOT NULL,
  lastname varchar(50) DEFAULT NULL,
  role varchar(50) DEFAULT NULL,
  login varchar(50) DEFAULT NULL,
  password varchar(50) DEFAULT NULL,
  UNIQUE KEY (name,lastname),
  PRIMARY KEY (id)
); 
*/

class user extends Database
{
 private  $columns = array('login'  => 'string',
				'password'  => 'string',
				'name'  => 'string',
				'lastname'  => 'string',
				'role'  => 'string',
				'id'  => 'string'
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
			 break;
			}
		// seguono altri controlli specifici per le colonne di queta tabella
		
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
  $checked_val_cols=$this->checkInput($val_cols);
  // se non Ë un array si Ë verificato un errore e restituisco il numero di errore
  
  if ($checked_val_cols==0) { $this->error_message=$checked_val_cols; $this->error_status=9;} 
  // atrimenti proseguo per l'inserimento
  else  parent::insert($checked_val_cols); 
}


public function update(array $set_val_cols, array $cod_val_cols)
{
  $checked_set_val_cols=$this->checkInput($set_val_cols);
  // se non Ë un array si Ë verificato un errore e restituisco il numero di errore
  
  if (!is_array($checked_set_val_cols)) { $this->error_message=$checked_set_val_cols; $this->error_status=9;}  
  // atrimenti proseguo con la modifica
  else  parent::update($checked_set_val_cols, $cod_val_cols);
}

// questa funzione si aspetta un recod {login:"login",password:"passwors"}
public function login(array $val_cols)
{
  $checked_val_cols=$this->checkInput($val_cols);
  // se non Ë un array si Ë verificato un errore e restituisco il numero di errore
  if ($checked_val_cols==0) { $this->error_message=$checked_val_cols; $this->error_status=9;}
  else   parent::fetchRecord($checked_val_cols);
}	 
}

?>