<?php

namespace Baloo;

/*
 * class BalooPDO
 * Class that extend PDO class
 *
 * @package
 */

class BalooPDO extends \PDO
{
    private $engine;
    private $host;
    private $database;
    private $user;
    private $pass;

    public function __construct($db, $host = 'localhost', $user = '', $pass = '', $engine = 'mysql')
    {
        $this->engine = $engine;
        $this->host = $host;
        $this->database = $db;
        $this->user = $user;
        $this->pass = $pass;

        switch ($this->engine) {
                case 'sqlite':
                    parent::__construct('sqlite:messaging.sqlite3');
                    break;
                case 'memory':
                    parent::__construct('sqlite::memory:');
                    break;
                default:
                    $dns = $this->engine.':dbname='.$this->database.';host='.$this->host;
                    parent::__construct($dns, $this->user, $this->pass);
            }
    }

    public function prepare($query, $options = null)
    {
        $query = preg_replace("/[\t\r\n]+/", ' ', $query);
        $statement = parent::prepare($query);
        if (BalooContext::$debug) {
            var_export($statement->queryString);
        }
        if ($statement) {
            return $statement;
        } else {
            throw new BalooException('Error Processing Query:: '.$query);
        }
    }
}
