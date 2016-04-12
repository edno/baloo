<?php

use Baloo\Packman\Packman;

class PackmanTest extends \PHPUnit_Framework_TestCase
{  
    const CLASS_PACKMAN = 'Baloo\Packman\Packman';
    const TEST_PACKS_LOCATION = __DIR__.'/packs/';
    
    static private $methodGetPackFile;

    public static function setUpBeforeClass() {
        // hack the default pack location for testing purpose
        self::setPrivateProperty('packPath', self::TEST_PACKS_LOCATION);
    }

    /**
     * Use reflection for setting private properties
     */
    private static function setPrivateProperty(string $property, $value) {
        $reflectedProperty = new ReflectionProperty(self::CLASS_PACKMAN, $property);
        $reflectedProperty->setAccessible(true);
        $reflectedProperty->setValue($value);
    }
    
    /**
     * Use reflection for accessing private methods
     */
    private static function invokePrivateMethod(string $method, ...$params) {
        $reflectedMethod = new ReflectionMethod(self::CLASS_PACKMAN, $method);
        $reflectedMethod->setAccessible(true);
        $class = $reflectedMethod->isStatic() ? null : self::CLASS_PACKMAN;
        return $reflectedMethod->invokeArgs($class, $params);
    }
    
    /**
     * @covers Baloo\Packman\Packman::_getPackFile
     * @group private
     */     
    public function testGetPackFileJSON() {
        //$result = self::accessPrivateMethod('_getPackFile')->invoke(null, 'pack4test');
        $result = self::invokePrivateMethod('_getPackFile', 'pack4test');
        $this->assertEquals(self::TEST_PACKS_LOCATION.'pack4test.pack.json', $result);
    }
    
    /**
     * @covers Baloo\Packman\Packman::_getPackFile
     * @group private
     */     
    public function testGetPackFileGZIP() {
        $result = self::invokePrivateMethod('_getPackFile', 'pack4test_gz');
        $this->assertEquals(self::TEST_PACKS_LOCATION.'pack4test_gz.pack.json.gz', $result);    
    }
    
    /**
     * @covers Baloo\Packman\Packman::_getPackFile
     * @expectedException Baloo\Packman\PackmanException
     * @group private
     */     
    public function testGetPackFileNotPresent() {
        $result = self::invokePrivateMethod('_getPackFile', 'nofile');
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
    public function testLoadPackFileGZJSON() {
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