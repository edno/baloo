<?php

namespace Baloo\UnitTests;

use Baloo\DataSource;

class DataSourceTest extends Framework\DatabaseTestCase
{
    public static $pathData;

    public static function setUpBeforeClass()
    {
        self::$pathData = __ROOT__.$GLOBALS['PATH_DATA'];
    }

    public function setUp()
    {
        parent::setUp();

        // set database connection
        \Baloo\BalooContext::getInstance()->setPDO($this->getPDO());
    }

    public function tearDown()
    {
    }

    /**
     * @return \PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()
    {
        return $this->createFlatXMLDataSet(self::$pathData.'pack4test_nodata.xml');
    }

    /**
     * @cover Baloo\DataSource::__getDataSourceByColumn
     * @dataProvider providerColumnsValues
     */
    public function testGetDataSourceByColumn($column, $value)
    {
        $datasource = $this->getMockBuilder('\Baloo\DataSource')
                    ->disableOriginalConstructor()
                    ->getMock();
        self::setPrivateProperty($datasource, 'pdo', $this->getPDO());
        $result = $this->invokePrivateMethod($datasource, '__getDataSourceByColumn', $column, $value);
        $this->assertInstanceOf('\Baloo\DataSource', $result);
    }

    /**
     * @cover Baloo\DataSource::__getDataSourceByColumn
     */
    public function testGetDataSourceByColumnWithNoRecord()
    {
        $datasource = $this->getMockBuilder('\Baloo\DataSource')
                    ->disableOriginalConstructor()
                    ->getMock();
        self::setPrivateProperty($datasource, 'pdo', $this->getPDO());
        $result = $this->invokePrivateMethod($datasource, '__getDataSourceByColumn', 'id', 0);
        $this->assertFalse($result);
    }

    public function providerColumnsValues()
    {
        return [
            'Column Id' => ['id', '1'],
            'Column Name' => ['name', 'Pack 4 Test']
        ];
    }

    /**
     * @cover Baloo\DataSource::__getDataSourceByName
     * @depends Baloo\UnitTests\DataSourceTest::testGetDataSourceByColumn
     */
    public function testGetDataSourceByName()
    {
        $datasource = $this->getMockBuilder('\Baloo\DataSource')
                    ->disableOriginalConstructor()
                    ->getMock();
        self::setPrivateProperty($datasource, 'pdo', $this->getPDO());
        $result = $this->invokePrivateMethod($datasource, '__getDataSourceByName', 'Pack 4 Test');
        $this->assertInstanceOf('\Baloo\DataSource', $result);
    }

    /**
     * @cover Baloo\DataSource::__getDataSourceById
     * @depends Baloo\UnitTests\DataSourceTest::testGetDataSourceByColumn
     */
    public function testGetDataSourceById()
    {
        $datasource = $this->getMockBuilder('\Baloo\DataSource')
                    ->disableOriginalConstructor()
                    ->getMock();
        self::setPrivateProperty($datasource, 'pdo', $this->getPDO());
        $result = $this->invokePrivateMethod($datasource, '__getDataSourceById', 1);
        $this->assertInstanceOf('\Baloo\DataSource', $result);
    }

    /**
     * @cover Baloo\DataSource::__construct
     * @depends Baloo\UnitTests\DataSourceTest::testGetDataSourceByName
     * @depends Baloo\UnitTests\DataSourceTest::testGetDataSourceById
     * @dataProvider providerNewDataSource
     */
    public function testNewDataSource($value, $expected)
    {
        $datasource = new DataSource($value);
        $this->assertInstanceOf('\Baloo\DataSource', $datasource);
        $this->assertEquals($expected, $datasource->getId());
    }

    public function providerNewDataSource()
    {
        return [
            'No record' => ['Unit Test', 0],
            'Record by name' => ['Pack 4 Test', 1],
            'Record by id' => [1, 1]
        ];
    }

    /**
     * @cover Baloo\DataSource::__construct
     * @depends Baloo\UnitTests\DataSourceTest::testGetDataSourceByName
     * @depends Baloo\UnitTests\DataSourceTest::testGetDataSourceById
     * @dataProvider providerNewDataSourceExceptionInvalidParam
     * @expectedException Baloo\BalooException
     */
    public function testNewDataSourceExceptionInvalidParam($value)
    {
        $datasource = new DataSource($value);
        $this->assertInstanceOf('\Baloo\DataSource', $datasource);
    }

    public function providerNewDataSourceExceptionInvalidParam()
    {
        return [
            'Invalid param (array)' => [array()],
            'Invalid param (object)' => [new \StdClass()],
            'Invalid param (bool)' => [true],
            'Null param' => [],
            'Invalid id' => [99],
            'Empty string' => [''],
            'Blank string' => ['  ']
        ];
    }

    /**
     * @cover Baloo\DataSource::__insertDataSource
     * @depends Baloo\UnitTests\DataSourceTest::testNewDataSource
     */
    public function testInsertDataSource()
    {
        $name = 'Unit Test';
        $datasource = new DataSource($name);
        $result = $this->invokePrivateMethod($datasource, '__insertDataSource');
        $this->assertEquals(2, $datasource->getId());
        $result = $this->invokePrivateMethod($datasource, '__getDataSourceByName', $name);
        $this->assertInstanceOf('\Baloo\DataSource', $result);
    }

    /**
     * @cover Baloo\DataSource::__updateDataSource
     * @depends Baloo\UnitTests\DataSourceTest::testInsertDataSource
     */
    public function testUpdateDataSource()
    {
        $name = 'Pack 4 Test';
        $datasource = new DataSource($name);
        $this->assertInstanceOf('\Baloo\DataSource', $datasource);
        $this->assertEquals(1, $datasource->getId());
        $data = ['name' => 'Updated Pack', 'version' => '9.9'];
        $datasource->setName($data['name']);
        $datasource->setVersion($data['version']);
        $result = $this->invokePrivateMethod($datasource, '__updateDataSource');
        $this->assertTrue($result);
        $result = $this->invokePrivateMethod($datasource, '__getDataSourceByName', $data['name']);
        $this->assertInstanceOf('\Baloo\DataSource', $result);
        $this->assertEquals($data['name'], $result->getName());
        $this->assertEquals($data['version'], $result->getVersion());
        $this->assertEquals($datasource->getId(), $result->getId());
    }

    /**
     * @cover Baloo\DataSource::save
     * @depends Baloo\UnitTests\DataSourceTest::testInsertDataSource
     */
    public function testSaveNewDataSource()
    {
        $name = 'Unit Test';
        $datasource = new DataSource($name);
        $datasource->save();
        $result = $this->invokePrivateMethod($datasource, '__getDataSourceByName', $name);
        $this->assertInstanceOf('\Baloo\DataSource', $result);
        $this->assertEquals($name, $result->getName());
        $this->assertEquals($datasource->getId(), $result->getId());
    }

    /**
     * @cover Baloo\DataSource::save
     * @depends Baloo\UnitTests\DataSourceTest::testUpdateDataSource
     */
    public function testSaveExistDataSource()
    {
        $name = 'Pack 4 Test';
        $datasource = new DataSource($name);
        $data = ['name' => 'Updated Pack', 'version' => '9.9'];
        $datasource->setName($data['name']);
        $datasource->setVersion($data['version']);
        $datasource->save();
        $result = $this->invokePrivateMethod($datasource, '__getDataSourceByName', $data['name']);
        $this->assertInstanceOf('\Baloo\DataSource', $result);
        $this->assertEquals($data['name'], $result->getName());
        $this->assertEquals($data['version'], $result->getVersion());
        $this->assertEquals($datasource->getId(), $result->getId());
    }

    /**
     * @cover Baloo\DataSource::getEntityTypeList
     */
    public function testGetEntityTypeList()
    {
        $name = 'Pack 4 Test';
        $datasource = new DataSource($name);
        $result = $datasource->getEntityTypeList();
        $expected = $this->assertEquals($this->getConnection()->getRowCount('entitytype'), count($result));
    }

    /**
     * @cover Baloo\DataSource::getEntityList
     * @depends Baloo\UnitTests\DataSourceTest::getEntityTypeList
     * @depends Baloo\UnitTests\DataEntityTypeTest
     */
    public function testGetEntityList()
    {
        $name = 'Pack 4 Test';
        $datasource = new DataSource($name);
        $result = $datasource->getEntityList();
        $expected = $this->assertEquals($this->getConnection()->getRowCount('entityobject'), count($result));
    }
}
