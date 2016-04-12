<?php

use Baloo\Packman\Packman;

use org\bovigo\vfs\vfsStream;

class PackmanTest extends \Baloo\UnitTest\DatabaseTestCase
{
    const TEST_CLASS = 'Baloo\Packman\Packman';
    const TEST_PACKS = __DIR__.'/_packs/';
    const TEST_DATA = __DIR__.'/../_data/';

    public function setUp() {
        $pdo = $this->getConnection()->getConnection();
        new \Baloo\BalooContext($pdo);
        // hack the default pack location for testing purpose
        self::setPrivateProperty(self::TEST_CLASS, 'packPath', self::TEST_PACKS);
    }

    /**
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet() {
        return $this->createFlatXMLDataSet(self::TEST_DATA.'ds_empty.xml');
    }

    /**
     * @covers Baloo\Packman\Packman::_getPackFile
     * @group private
     */
    public function testGetPackFileJson() {
        $package = 'pack4test';
        $result = self::invokePrivateMethod(self::TEST_CLASS, '_getPackFile', $package);
        $this->assertEquals(self::TEST_PACKS."${package}.pack.json", $result);
    }

    /**
     * @covers Baloo\Packman\Packman::_getPackFile
     * @group private
     */
    public function testGetPackFileGzip() {
        $package = 'pack4test';
        $result = self::invokePrivateMethod(self::TEST_CLASS, '_getPackFile', "${package}_gz");
        $this->assertEquals(self::TEST_PACKS."${package}_gz.pack.json.gz", $result);
    }

    /**
     * @covers Baloo\Packman\Packman::_getPackFile
     * @expectedException Baloo\Packman\PackmanException
     * @group private
     */
    public function testGetPackFileNotPresent() {
        $package = 'nofile';
        $result = self::invokePrivateMethod(self::TEST_CLASS, '_getPackFile', $package);
    }

    /**
     * @covers Baloo\Packman\Packman::_getPackFile
     * @expectedException Baloo\Packman\PackmanException
     * @group private
     */
    public function testGetPackFileNotReadable() {
        $package = 'notreadable';
        $root = vfsStream::setup('packs');
        $file = vfsStream::newFile("${package}.pack.json", 0000)->at($root);
        self::setPrivateProperty(self::TEST_CLASS, 'packPath', $root->path());
        $result = self::invokePrivateMethod(self::TEST_CLASS, '_getPackFile', $package);
    }

    /**
     * @covers Baloo\Packman\Packman::loadPackFile
     * @group public
     */
    public function testLoadPackFileJson() {
        $package = 'pack4test';
        $pack = Packman::loadPackFile($package);
        $this->assertJsonStringEqualsJsonFile(self::TEST_PACKS."${package}.pack.json", json_encode($pack));
        $this->assertInstanceOf('Baloo\Packman\Package', $pack);
    }

    /**
     * @covers Baloo\Packman\Packman::loadPackFile
     * @group public
     */
    public function testLoadPackFileGzip() {
        $package = 'pack4test';
        $pack = Packman::loadPackFile("${package}_gz");
        $this->assertJsonStringEqualsJsonFile(self::TEST_PACKS."${package}.pack.json", json_encode($pack));
        $this->assertInstanceOf('Baloo\Packman\Package', $pack);
    }

    /**
     * @covers Baloo\Packman\Packman::loadPackFile
     * @expectedException Baloo\Packman\PackmanException
     * @group public
     */
    public function testLoadPackFileNotPresent() {
        $package = 'nofile';
        $pack = Packman::loadPackFile($package);
        $this->expectExceptionMessage('Invalid package name');
    }

    /**
     * @covers Baloo\Packman\Packman::loadPackFile
     * @expectedException Baloo\Packman\PackmanException
     * @group public
     */
    public function testLoadPackFileEmpty() {
        $package = 'empty';
        $pack = Packman::loadPackFile($package);
        $this->expectExceptionMessage('Syntax error, malformed JSON.');
    }

    /**
     * @covers Baloo\Packman\Packman::installPack
     * @group public
     */
    public function testInstallPack() {
        $package = 'pack4test';
        $pack = Packman::loadPackFile($package);
        $result = Packman::installPack($pack);
        $this->assertTrue($result);
    }

    /**
     * @covers Baloo\Packman\Packman::installPack
     * @expectedException Baloo\Packman\PackmanException
     * @group public
     */
    public function testInstallPackInvalid() {
        $pack = new \Baloo\Packman\Package();
        $result = Packman::installPack($pack);
        $this->assertNotTrue($result);
    }


}
