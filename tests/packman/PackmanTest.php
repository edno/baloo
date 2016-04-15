<?php

namespace Baloo\UnitTests;

use Baloo\Packman\Packman;
use org\bovigo\vfs\vfsStream;

class PackmanTest extends Framework\DatabaseTestCase
{
    public $packman;

    public static $pathPack;
    public static $pathData;

    public static function setUpBeforeClass()
    {
        self::$pathPack = __ROOT__.$GLOBALS['PATH_PACKS'];
        self::$pathData = __ROOT__.$GLOBALS['PATH_DATA'];
    }

    public function setUp()
    {
        $pdo = $this->getConnection()->getConnection();
        \Baloo\BalooContext::getInstance()->setPDO($pdo);

        $this->packman = Packman::getInstance();
        // hack the default pack location for testing purpose
        self::setPrivateProperty($this->packman, 'packPath', self::$pathPack);
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
     * @covers Baloo\Packman\Packman::__getPackFile
     * @group private
     */
    public function testGetPackFileUseJson()
    {
        $package = 'pack4test';
        $result = self::invokePrivateMethod($this->packman, '__getPackFile', $package);
        $this->assertEquals(self::$pathPack."${package}.pack.json", $result);
    }

    /**
     * @covers Baloo\Packman\Packman::__getPackFile
     * @group private
     */
    public function testGetPackFileUseGzip()
    {
        $package = 'pack4test';
        $result = self::invokePrivateMethod($this->packman, '__getPackFile', "${package}_gz");
        $this->assertEquals(self::$pathPack."${package}_gz.pack.json.gz", $result);
    }

    /**
     * @covers Baloo\Packman\Packman::__getPackFile
     * @expectedException Baloo\Packman\PackmanException
     * @group private
     */
    public function testGetPackFileExceptionNotPresent()
    {
        $package = 'nofile';
        $result = self::invokePrivateMethod($this->packman, '__getPackFile', $package);
    }

    /**
     * @covers Baloo\Packman\Packman::__getPackFile
     * @expectedException Baloo\Packman\PackmanException
     * @group private
     * @group mock
     */
    public function testGetPackFileExceptionNotReadable()
    {
        $package = 'notreadable';
        $root = vfsStream::setup('packs');
        $file = vfsStream::newFile("${package}.pack.json", 0000)->at($root);
        self::setPrivateProperty($this->packman, 'packPath', $root->path());
        $result = self::invokePrivateMethod($this->packman, '__getPackFile', $package);
    }

    /**
    * @covers Baloo\Packman\Packman::__getPackFile
    * @expectedException Baloo\Packman\PackmanException
    * @group private
    */
    public function testGetPackFileExceptionFolderNotPresent()
    {
        $package = 'package';
        self::setPrivateProperty($this->packman, 'packPath', __DIR__.'/notexist/');
        $result = self::invokePrivateMethod($this->packman, '__getPackFile', $package);
    }

    /**
    * @covers Baloo\Packman\Packman::__getPackFile
    * @expectedException Baloo\Packman\PackmanException
    * @group private
    */
    public function testGetPackFileExceptionFolderNotReadable()
    {
        $package = 'package';
        $root = vfsStream::setup('packs', 0000);
        $file = vfsStream::newFile("${package}.pack.json")->at($root);
        self::setPrivateProperty($this->packman, 'packPath', $root->path());
        $result = self::invokePrivateMethod($this->packman, '__getPackFile', $package);
    }

    /**
    * @covers Baloo\Packman\Packman::loadPackFile
     * @covers Baloo\Packman\Packman::loadPackFile
     * @depends Baloo\UnitTests\PackageTest::testNewPackageUseJson
     * @group public
     */
    public function testLoadPackFileUseJson()
    {
        $package = 'pack4test';
        $pack = $this->packman->loadPackFile($package);
        $this->assertJsonStringEqualsJsonFile(self::$pathPack."${package}.pack.json", json_encode($pack));
        $this->assertInstanceOf('Baloo\Packman\Package', $pack);
    }

    /**
     * @covers Baloo\Packman\Packman::loadPackFile
     * @depends Baloo\UnitTests\PackageTest::testNewPackageUseJson
     * @group public
     */
    public function testLoadPackFileUseGzip()
    {
        $package = 'pack4test';
        $pack = $this->packman->loadPackFile("${package}_gz");
        $this->assertJsonStringEqualsJsonFile(self::$pathPack."${package}.pack.json", json_encode($pack));
        $this->assertInstanceOf('Baloo\Packman\Package', $pack);
    }

    /**
     * @covers Baloo\Packman\Packman::loadPackFile
     * @expectedException Baloo\Packman\PackmanException
     * @group public
     */
    public function testLoadPackFileExceptionNotPresent()
    {
        $package = 'nofile';
        $pack = $this->packman->loadPackFile($package);
        $this->expectExceptionMessage('Invalid package name');
    }

    /**
     * @covers Baloo\Packman\Packman::loadPackFile
     * @expectedException Baloo\Packman\PackmanException
     * @group public
     */
    public function testLoadPackFileExceptionEmpty()
    {
        $package = 'empty';
        $pack = $this->packman->loadPackFile($package);
        $this->expectExceptionMessage('Syntax error, malformed JSON.');
    }

    /**
     * @covers Baloo\Packman\Packman::installPack
     * @depends Baloo\UnitTests\PackmanTest::testLoadPackFileUseJson
     * @group public
     */
    public function testInstallPack()
    {
        $package = 'pack4test';
        $this->assertFalse(\Baloo\DataSourceManager::getInstance()->getDataSource($package));
        $pack = $this->packman->loadPackFile($package);
        $result = $this->packman->installPack($pack);
        $this->assertTrue($result);
    }

    /**
     * @covers Baloo\Packman\Packman::installPack
     * @expectedException Baloo\Packman\PackmanException
     * @depends Baloo\UnitTests\PackageTest::testNewPackage
     * @group public
     */
    public function testInstallPackExceptionInvalid()
    {
        $pack = new \Baloo\Packman\Package();
        $result = $this->packman->installPack($pack);
        $this->assertNotTrue($result);
    }
}
