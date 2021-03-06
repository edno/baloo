<?php

namespace Baloo\UnitTests\Framework;

abstract class DataBaseTestCase extends \PHPUnit_Extensions_Database_TestCase
{
    use MockSingleton;
    use ReflectionPrivate;

    // only instantiate pdo once for test clean-up/fixture load
    private static $pdo = null;

    // only instantiate PHPUnit_Extensions_Database_DB_IDatabaseConnection once per test
    private $conn = null;

    /**
     * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
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

    final public function getPDO()
    {
        return $this->getConnection()->getConnection();
    }

    public function setUp()
    {
        parent::setUp();
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
    private function initDatabase()
    {
        $initSQL = file_get_contents(__ROOT__.'/'.$GLOBALS['PATH_DATA'].$GLOBALS['DB_SQL']);
        self::$pdo->exec($initSQL);
    }
}
