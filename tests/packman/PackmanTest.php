<?php

use Baloo\Packman\Packman;

class PackmanTest extends \Baloo\UnitTest\DatabaseTestCase
{  
    const CLASS_PACKMAN = 'Baloo\Packman\Packman';
    const TEST_PACKS_LOCATION = __DIR__.'/_packs/';
    
    public static function setUpBeforeClass() {
        // hack the default pack location for testing purpose
        self::setPrivateProperty(self::CLASS_PACKMAN, 'packPath', self::TEST_PACKS_LOCATION);
    }

    /**
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet() {
        return $this->createFlatXMLDataSet(__DIR__.'/../_data/ds_empty.xml');
    }   
    
    /**
     * @covers Baloo\Packman\Packman::_getPackFile
     * @group private
     */     
    public function testGetPackFileJSON() {
        $result = self::invokePrivateMethod(self::CLASS_PACKMAN, '_getPackFile', 'pack4test');
        $this->assertEquals(self::TEST_PACKS_LOCATION.'pack4test.pack.json', $result);
    }
    
    /**
     * @covers Baloo\Packman\Packman::_getPackFile
     * @group private
     */     
    public function testGetPackFileGZIP() {
        $result = self::invokePrivateMethod(self::CLASS_PACKMAN, '_getPackFile', 'pack4test_gz');
        $this->assertEquals(self::TEST_PACKS_LOCATION.'pack4test_gz.pack.json.gz', $result);    
    }
    
    /**
     * @covers Baloo\Packman\Packman::_getPackFile
     * @expectedException Baloo\Packman\PackmanException
     * @group private
     */     
    public function testGetPackFileNotPresent() {
        $result = self::invokePrivateMethod(self::CLASS_PACKMAN, '_getPackFile', 'nofile');
    }
    
    /**
     * @covers Baloo\Packman\Packman::_getPackFile
     * @expectedException Baloo\Packman\PackmanException     
     * @group private
     */     
    public function testGetPackFileNotReadable() {
        $this->markTestSkipped('todo');
    }   

    /**
     * @covers Baloo\Packman\Packman::loadPackFile
     * @group public
     */ 
    public function testLoadPackFileJSON() {
        $pack = Packman::loadPackFile('pack4test');
        $this->assertJsonStringEqualsJsonFile(self::TEST_PACKS_LOCATION.'pack4test.pack.json', json_encode($pack));
        $this->assertInstanceOf('Baloo\Packman\Package', $pack);
    }

    /**
     * @covers Baloo\Packman\Packman::loadPackFile
     * @group public
     */     
    public function testLoadPackFileGZIP() {
        $pack = Packman::loadPackFile('pack4test_gz');
        $this->assertJsonStringEqualsJsonFile(self::TEST_PACKS_LOCATION.'pack4test.pack.json', json_encode($pack));
        $this->assertInstanceOf('Baloo\Packman\Package', $pack);
    }   

    /**
     * @covers Baloo\Packman\Packman::loadPackFile  
     * @expectedException Baloo\Packman\PackmanException
     * @group public
     */ 
    public function testLoadPackFileNotPresent() {
        $pack = Packman::loadPackFile('nofile');
        $this->expectExceptionMessage('Invalid package name');
    }
    
    /**
     * @covers Baloo\Packman\Packman::loadPackFile  
     * @expectedException Baloo\Packman\PackmanException
     * @group public
     */ 
    public function testLoadPackFileEmpty() {
        $pack = Packman::loadPackFile('empty');
        $this->expectExceptionMessage('Syntax error, malformed JSON.');
    }
    
}