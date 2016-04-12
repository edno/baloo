<?php

namespace Baloo\UnitTest;

abstract class DataBaseTestCase extends \PHPUnit_Extensions_Database_TestCase
{  
    // only instantiate pdo once for test clean-up/fixture load
    static private $pdo = null;

    // only instantiate PHPUnit_Extensions_Database_DB_IDatabaseConnection once per test
    private $conn = null;   

    final public function getConnection()
    {
        if ($this->conn === null) {
            if (self::$pdo == null) {
                self::$pdo = new \PDO($GLOBALS['DB_DSN'], $GLOBALS['DB_USER'], $GLOBALS['DB_PASSWD']);
            }
            $this->conn = $this->createDefaultDBConnection(self::$pdo, $GLOBALS['DB_DBNAME']);  
        
            self::initDatabase();
        }

        return $this->conn;
    }

    /**
     * Resets the database after each test case.
     *
     * @return \PHPUnit_Extensions_Database_Operation_Truncate
     */
    protected function getTearDownOperation()
    {
        return \PHPUnit_Extensions_Database_Operation_Factory::TRUNCATE();
    }
    
    /**
     * Set up the database
     */
    private function initDatabase() {
        $initSQL = file_get_contents( __DIR__.'/../_data/'.$GLOBALS['DB_SQL']);
        self::$pdo->exec($initSQL);
    }

    /**
     * Use reflection for setting private properties
     */
    final protected static function setPrivateProperty(string $class, string $property, $value) {
        $reflectedProperty = new \ReflectionProperty($class, $property);
        $reflectedProperty->setAccessible(true);
        $reflectedProperty->setValue($value);
    }
    
    /**
     * Use reflection for accessing private methods
     * @return method result
     */
    final protected static function invokePrivateMethod(string $class, string $method, ...$params) {
        $reflectedMethod = new \ReflectionMethod($class, $method);
        $reflectedMethod->setAccessible(true);
        $class = $reflectedMethod->isStatic() ? null : $class;
        return $reflectedMethod->invokeArgs($class, $params);
    }
    
}