<?php

/**
 * class BalooPDO
 * Class that extend PDO class
 *
 * @package 
 */
class BalooPDO extends PDO {
   
    private $engine;
    private $host;
    private $database;
    private $user;
    private $pass;
	  
    public function __construct($db, $host = 'localhost', $user = '', $pass = '', $engine = 'mysql'){
        $this->engine 	= $engine;
        $this->host 	= $host;
        $this->database = $db;
        $this->user 	= $user;
        $this->pass 	= $pass;
		
        $dns = $this->engine .':dbname='. $this->database .';host='. $this->host;
        parent::__construct($dns, $this->user, $this->pass);
    }
	
	public function prepare($query, $options = null) {
		$statement = parent::prepare($query);
		if(BalooContext::$debug) {
			var_export($statement->queryString);
		}
		return $statement;
	}
}
?>
