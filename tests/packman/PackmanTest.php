<?php

use Baloo\Packman\Packman;

use Baloo\UnitTest\MockSingleton;

use org\bovigo\vfs\vfsStream;
use \Mockery as Mockery;

class PackmanTest extends \Baloo\UnitTest\DatabaseTestCase
{
    const TEST_PACKS = __DIR__.'/_packs/';
    const TEST_DATA = __DIR__.'/../_data/';
    
    public $packman;

    public function setUp() {
        $pdo = $this->getConnection()->getConnection();
        \Baloo\BalooContext::getInstance()->setPDO($pdo);
        
        $this->packman = Packman::getInstance();
        // hack the default pack location for testing purpose
        self::setPrivateProperty($this->packman, 'packPath', self::TEST_PACKS);
    }
    
    public function tearDown() {
        Mockery::close(); // remove all registered test doubles
    }

    /**
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet() {
        return $this->createFlatXMLDataSet(self::TEST_DATA.'ds_empty.xml');
    }

    /**
     * @covers Baloo\Packman\$this->packman->_getPackFile
     * @group private
     */
    public function testGetPackFileJson() {
        $package = 'pack4test';
        $result = self::invokePrivateMethod($this->packman, '_getPackFile', $package);
        $this->assertEquals(self::TEST_PACKS."${package}.pack.json", $result);
    }

    /**
     * @covers Baloo\Packman\$this->packman->_getPackFile
     * @group private
     */
    public function testGetPackFileGzip() {
        $package = 'pack4test';
        $result = self::invokePrivateMethod($this->packman, '_getPackFile', "${package}_gz");
        $this->assertEquals(self::TEST_PACKS."${package}_gz.pack.json.gz", $result);
    }

    /**
     * @covers Baloo\Packman\$this->packman->_getPackFile
     * @expectedException Baloo\Packman\PackmanException
     * @group private
     */
    public function testGetPackFileNotPresent() {
        $package = 'nofile';
        $result = self::invokePrivateMethod($this->packman, '_getPackFile', $package);
    }

    /**
     * @covers Baloo\Packman\$this->packman->_getPackFile
     * @expectedException Baloo\Packman\PackmanException
     * @group private
     * @group mock
     */
    public function testGetPackFileNotReadable() {
        $package = 'notreadable';
        $root = vfsStream::setup('packs');
        $file = vfsStream::newFile("${package}.pack.json", 0000)->at($root);
        self::setPrivateProperty($this->packman, 'packPath', $root->path());
        $result = self::invokePrivateMethod($this->packman, '_getPackFile', $package);
    }

    /**
     * @covers Baloo\Packman\$this->packman->loadPackFile
     * @depends PackageTest::testNewPackageFromJson
     * @group public
     */
    public function testLoadPackFileJson() {
        $package = 'pack4test';
        $pack = $this->packman->loadPackFile($package);
        $this->assertJsonStringEqualsJsonFile(self::TEST_PACKS."${package}.pack.json", json_encode($pack));
        $this->assertInstanceOf('Baloo\Packman\Package', $pack);
    }

    /**
     * @covers Baloo\Packman\$this->packman->loadPackFile
     * @depends PackageTest::testNewPackageFromJson
     * @group public
     */
    public function testLoadPackFileGzip() {
        $package = 'pack4test';
        $pack = $this->packman->loadPackFile("${package}_gz");
        $this->assertJsonStringEqualsJsonFile(self::TEST_PACKS."${package}.pack.json", json_encode($pack));
        $this->assertInstanceOf('Baloo\Packman\Package', $pack);
    }

    /**
     * @covers Baloo\Packman\$this->packman->loadPackFile
     * @expectedException Baloo\Packman\PackmanException
     * @group public
     */
    public function testLoadPackFileNotPresent() {
        $package = 'nofile';
        $pack = $this->packman->loadPackFile($package);
        $this->expectExceptionMessage('Invalid package name');
    }

    /**
     * @covers Baloo\Packman\$this->packman->loadPackFile
     * @expectedException Baloo\Packman\PackmanException
     * @group public
     */
    public function testLoadPackFileEmpty() {
        $package = 'empty';
        $pack = $this->packman->loadPackFile($package);
        $this->expectExceptionMessage('Syntax error, malformed JSON.');
    }
    
    /**
     * @covers Baloo\Packman\$this->packman->installPack
     * @depends PackmanTest::testLoadPackFileJson
     * @group public
     * @group mock
     */
    public function testInstallPackWithMockBaloo() {
        // mock core classes
        $mockDSManager = $this->getMockFromSingleton('Baloo\DataSourceManager', 
                                                     ['getDataSourceTypeID',
                                                     'insertDataSourceType',
                                                     'getDataSource',
                                                     'insertDataSource',
                                                     'insertDataTypeFieldType',
                                                     'insertDataType',
                                                     'insertDataTypeField']);
        $mockDSManager
            ->expects($this->any())
            ->method('getDataSource')
            ->with($this->greaterThan(0))
            ->willReturn(999);
        $package = 'pack4test';
        $this->assertEquals(999, Baloo\DataSourceManager::getInstance()->getDataSource(900));
        $pack = $this->packman->loadPackFile($package);
        $result = $this->packman->installPack($pack);
        $this->assertTrue($result);
        
        $this->resetMockSingleton('Baloo\DataSourceManager');
    }   

    /**
     * @covers Baloo\Packman\$this->packman->installPack
     * @depends PackmanTest::testLoadPackFileJson
     * @group public
     */
    public function testInstallPack() {
        $package = 'alm';
        $pack = $this->packman->loadPackFile($package);
        $result = $this->packman->installPack($pack);
        $this->assertTrue($result);
    }

    /**
     * @covers Baloo\Packman\$this->packman->installPack
     * @expectedException Baloo\Packman\PackmanException
     * @depends PackageTest::testNewPackage
     * @group public
     */
    public function testInstallPackInvalid() {
        $pack = new \Baloo\Packman\Package();
        $result = $this->packman->installPack($pack);
        $this->assertNotTrue($result);
    }
}
