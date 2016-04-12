<?php

use Baloo\Packman\Package;

class PackageTest extends \Baloo\UnitTest\TestCase
{
    /**
     * @covers Baloo\Packman\Package
     */
    public function testNewPackage()
    {
        $pack = new Package('test_pack', 'test_datasourcetype', 'test_datasource');
        $this->assertInstanceOf('Baloo\Packman\Package', $pack);
        $this->assertEquals('test_pack', $pack->name);
        $this->assertEquals('test_datasourcetype', $pack->datasourcetype->name);
        $this->assertEquals('test_datasource', $pack->datasource->name);
    }

    /**
     * @covers Baloo\Packman\Package
     */
    public function testNewPackageFromJson()
    {
        $pack = new Package('{"name": "test_pack", "datasourcetype": {"name": "test_datasourcetype"}, "datasource": {"name": "test_datasource"}}');
        $this->assertInstanceOf('Baloo\Packman\Package', $pack);
        $this->assertEquals('test_pack', $pack->name);
        $this->assertEquals('test_datasourcetype', $pack->datasourcetype->name);
        $this->assertEquals('test_datasource', $pack->datasource->name);
    }

    /**
     * @covers Baloo\Packman\Package
     * @expectedException Baloo\Packman\PackmanException
     */
    public function testNewPackageFromEmpty()
    {
        $pack = new Package();
        $this->expectExceptionMessage('Syntax error, malformed JSON.');
    }

    /**
     * @covers Baloo\Packman\Package
     * @expectedException Baloo\Packman\PackmanException
     */
    public function testNewPackageFromInvalidJson()
    {
        $pack = new Package('"name": "test_pack", "datasourcetype": {"name": "test_datasourcetype"}, "datasource": {"name": "test_datasource"}');
        $this->expectExceptionMessage('Syntax error, malformed JSON.');
    }

}
