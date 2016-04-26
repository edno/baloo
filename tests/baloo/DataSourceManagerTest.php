<?php

namespace Baloo\UnitTests;

use Baloo\DataSourceManager;

class DataSourceManagerTest extends Framework\DatabaseTestCase
{
    public static $pathData;

    public $manager;

    public static function setUpBeforeClass()
    {
        self::$pathData = __ROOT__.$GLOBALS['PATH_DATA'];
    }

    public function setUp()
    {
        // set database connection
        $pdo = $this->getConnection()->getConnection();
        \Baloo\BalooContext::getInstance()->setPDO($pdo);

        $this->manager = DataSourceManager::getInstance();
    }

    public function tearDown()
    {
    }

    /**
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()
    {
        return $this->createFlatXMLDataSet(self::$packData.'ds_empty.xml');
    }

    /**
     * @cover \Baloo\DataSourceManager::getInstance
     */
    public function testGetInstance()
    {
        $this->assertInstanceOf('\Baloo\DataSourceManager', $this->manager);
    }
}
