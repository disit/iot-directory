<?php 

class Database{
	
public $connection;   // mantiene la connessione 
public $error_status=1; // contiene la presenza di un errore
public $error_message; // contiene il messaggio di errore associato allo stato di errore
private $num_rows=0; // contiene il numero di righe che sono state modificate dall'ultimo comando eseguito

// private $host="159.149.129.184";
// private $port=3306;
// private $user="root";
// private $pass="!!orion__";
// private $db="iotdirectory";


private $host="localhost";
private $port=3306;
private $user="root";
private $pass="!!orion__";
private $db="iotdirectory";



public $result;

public $msg= array(0=>'Problemi di connessione al server MySQL',
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


public function error_redirect()
{ 
  $string="Location:../index.php?op=errore&status=" . $this->error_status;
  $string .= "&msg=" . urlencode($this->connection->error);   
  header($string);
}

			   
public function error()
{ 
 if ($this->error_status != 1) {
    //  per verificare la presenza di errori decommentare la seguente riga
    die("Si Ë verificato il seguente errore " . $this->msg[$this->error_status]); 
    return true;
 }
 else return false;
}
			   
public function __construct(){
    $this->connection = new mysqli($this->host,$this->user,$this->pass,$this->db,$this->port);
    //Check Connection
    if($this->connection->connect_errno){
	    $this->error_status=0;
		die("Connection Fail ".$this->connection->connect_error);
		return 0;         
    }
    else{
        //echo 'connesso <br/>';
		mysqli_set_charset($this->connection,"utf8");
		$this->error_status=1;
		return 1;		
    }
}

public function __destruct() {
   // Close the connection
   $this->connection->close();
   $this->error_status=0;
}


public function fetchNumRows()
{
  return $this->num_rows;
}




public function fetchRecord(array $val_cols){
        $i=0;
		$table= get_class($this);
		foreach($val_cols as $key=>$value) {
			$exp[$i] = $key." = '".$this->test_input($value)."'";
		    $i++;
		}
		$Stexp = implode(" AND ",$exp);

		//echo "SELECT * FROM $table WHERE $Stexp <br/>";
		$result=$this->connection->query("SELECT * FROM $table WHERE $Stexp");
	
		if($this->connection->errno){
		    $this->error_status=4;
			die("Fail Select ".$this->connection->error);
			return 4;
		}
		// metto il numero di tuple restituite dall'interrogazione in un attributo della classe 
        $this->num_rows= $result->num_rows; 
		
		//return a single array as required columns result
		for ($res = array(); $tmp = $result->fetch_assoc();) $res[] = $tmp;
        $this->result = $res;
		
}


public function query($query){
        
		$result=$this->connection->query($query);
	
		if($this->connection->errno){
		    $this->error_status=4;
			die("Fail Select ".$this->connection->error);
			return 4;
		}
		// metto il numero di tuple restituite dall'interrogazione in un attributo della classe 
        $this->num_rows= $result->num_rows; 
		//return a single array as required columns result
		for ($res = array(); $tmp = $result->fetch_assoc();)  $res[] = $tmp;
        $this->result = $res;
}


//Fetch data by accepting columns(1 dimentional array) name, and a condition
public function fetch(array $columns, array $val_cols){
        $i=0;
		$table= get_class($this);
		foreach($val_cols as $key=>$value) {
			$exp[$i] = $key." = '".$this->test_input($value)."'";
		    $i++;
		}
		$Stexp = implode(" AND ",$exp);
		$columns=implode(",",$columns);
		//echo "SELECT $columns FROM $table WHERE $Stexp <br/>";
		$result=$this->connection->query("SELECT $columns FROM $table WHERE $Stexp");
	
		if($this->connection->errno){
		    $this->error_status=4;
			die("Fail Select ".$this->connection->error);
			return 4;
		}
		// metto il numero di tuple restituite dall'interrogazione in un attributo della classe 
        $this->num_rows= $result->num_rows; 

		//return two dimentional array as required columns result
		for ($res = array(); $tmp = $result->fetch_assoc();) $res[] = $tmp;
        $this->result = $res;
}


//Insert Data within table by accepting TableName and Table column
public function insert(array $val_cols){
        $tblname= get_class($this);
		
		$keysString = implode(", ", array_keys($val_cols));

		$i=0;
		foreach($val_cols as $key=>$value) {
			$StValue[$i] = "'".$this->test_input($value)."'";
		    $i++;
		}

		$StValues = implode(", ",$StValue);
		
		//Perform Insert operation
		echo "INSERT INTO $tblname ($keysString) VALUES ($StValues) <br/>"; 
		if($this->connection->query("INSERT INTO $tblname ($keysString) VALUES ($StValues)") === TRUE){
		    // metto il numero di tuple restituite dall'interrogazione in un attributo della classe 
           $this->num_rows= mysqli_affected_rows($this->connection); 
			$this->error_status=1;
		    return 1;
		}else{
		     $this->error_status=0;
			 $this->error_message= $this->connection->error;
			 echo "Error ".$this->connection->error . "<br/>";
			return 0;
		}

}

//Delete data form table; Accepting Table Name and Keys=>Values as associative array
public function delete(array $val_cols){
		
		$tblname= get_class($this);
		$i=0;
		foreach($val_cols as $key=>$value) {
			$exp[$i] = $key." = '".$value."'";
		    $i++;
		}

		$Stexp = implode(" AND ",$exp);

		//Perform Delete operation
		echo "DELETE FROM $tblname WHERE $Stexp";
		if($this->connection->query("DELETE FROM $tblname WHERE $Stexp") === TRUE){
		    // metto il numero di tuple restituite dall'interrogazione in un attributo della classe 
		    $this->num_rows= mysqli_affected_rows($this->connection);  
			if(mysqli_affected_rows($this->connection)){
				echo "Record has been deleted successfully";
				$this->error_status=1;
				return 1;
			}
			else{
				echo "The Record you want to delete is no loger exists";
				$this->error_status=2;
				return 2;
			}
		}
		else{
			echo "Error to delete".$this->connection->error . "<br/>";
			$this->error_status=0;
			$this->error_message= $this->connection->error;
			return 0;
		}
		
}

//Update data within table; Accepting Table Name and Keys=>Values as associative array
public function update(array $set_val_cols, array $cond_val_cols){
		//append set_val_cols associative array elements 
		$tblname= get_class($this);
		$i=0;
		foreach($set_val_cols as $key=>$value) {
			$set[$i] = $key." = '".$this->test_input($value)."'";
		    $i++;
		}

		$Stset = implode(", ",$set);

		//append cond_val_cols associative array elements
		$i=0;
		foreach($cond_val_cols as $key=>$value) {
		
		$cod[$i] = $key." = '".$this->test_input($value)."'";
		    $i++;
		}

		$Stcod = implode(" AND ",$cod);
        echo "UPDATE $tblname SET $Stset WHERE $Stcod";
		// exit(1);
		// Update operation
		if($this->connection->query("UPDATE $tblname SET $Stset WHERE $Stcod") != TRUE)
		{
			echo "Error to update".$this->connection->error . "<br/>";
			$this->error_status=0;
			$this->num_rows= 0;
			$this->error_message= $this->connection->error;
			return 0;
		}
		else
		{
		  $this->error_status=1;
		  $this->num_rows= mysqli_affected_rows($this->connection);
		  return 1;
		}
         		
}


private function test_input($value) {
  $search = array('ì', 'è', 'é', 'ò', 'à', 'ù'); 
  $replace = array('&igrave;', '&egrave;', '&eacute;', '&ograve;', '&agrave;', '&ugrave;'); 
      
  // echo "prima " . $value . "<br/>";
  $value = trim($value);
  $value = addslashes($value);
  $value = utf8_decode($value);
  $value = str_replace($search, $replace, $value); 
  $value = htmlspecialchars($value);
  
  // $value = mysqli_real_escape_string($value);
  // echo "dopo " . $value. "<br/>";
  return $value;
 }

private function isValidDateTimeString($str_dt, $str_dateformat) {
  $date = DateTime::createFromFormat($str_dateformat, $str_dt);
  return ($date); // && DateTime::getLastErrors()["warning_count"] == 0 && DateTime::getLastErrors()["error_count"] == 0);
}

private function checkDate($value){
    $safe_value = $this->test_input($value);
    if (empty($safe_value)) return null;
    if (!$this->isValidDateTimeString($safe_value, 'Y-m-d')) return null;

   return $safe_value;  
   }
 
 
private function checkString($value){
  $safe_value = $this->test_input($value);
  if (!empty($safe_value))
	return $safe_value;
  else 
	return null;
}

private function checkInt($value){
  $safe_value = $this->test_input($value);
  if (!is_numeric($safe_value)) return null;
  
  return $safe_value;
}

private function checkDecimal($value){
  $safe_value = $this->test_input($value);
  if (!is_numeric($safe_value)) return null;
  
  return $safe_value;
}

private function checkBool($value){
  $safe_value = $this->test_input($value);
  if (empty($safe_value)) return null;
  if ($value!='si' && $value!='no') return null;
  
  return $safe_value;
}

// questa funzione prende in input un array e prepara il valore di tipo set
private function checkSet($value){
  $safe_value = $this->test_input($value);
  if (empty($safe_value)) return null;
  return $safe_value;
}

private function checkTime($value){
  $safe_value = $this->test_input($value);
  if (empty($safe_value)) return null;
  if (!$this->isValidDateTimeString($safe_value, 'H:i')) return null;

  return $safe_value;
}

public function check($type,$value)
{
  switch ($type) { 
	case 'int': 
		return $this->checkInt($value);
		break; 
	case 'string': 
		return $this->checkString($value);
		break; 
	case 'date': 
		return $this->checkDate($value);
		break; 
	case 'time': 
		return $this->checkTime($value);
		break;
	case 'decimal': 
		return $this->checkDecimal($value);
		break;		
	case 'bool': 
		return $this->checkBool($value);
		break;		
	default: 
		echo 'funzione non presente' . "<br/>";	
}
return null;
}

}//End of class Database

?>
