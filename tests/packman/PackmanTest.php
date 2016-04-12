<?php

use Baloo\Packman\Packman;

class PackmanTest extends \PHPUnit_Framework_TestCase
{	
	/**
     * @covers Baloo\Packman\Packman::loadPackFile
     */	
    public function testLoadPackFileJSON()
    {
		$pack = Packman::loadPackFile('pack4test');
		$this->assertJsonStringEqualsJsonFile('public/packs/pack4test.pack.json', json_encode($pack));
		$this->assertInstanceOf('Baloo\Packman\Package', $pack);
	}

	/**
     * @covers Baloo\Packman\Packman::loadPackFile
     */		
    public function testLoadPackFileGZJSON()
    {
		$pack = Packman::loadPackFile('pack4test_gz');
		$this->assertJsonStringEqualsJsonFile('public/packs/pack4test.pack.json', json_encode($pack));
		$this->assertInstanceOf('Baloo\Packman\Package', $pack);
	}	

	/**
     * @covers Baloo\Packman\Packman::loadPackFile	
     * @expectedException Baloo\Packman\PackmanException
     */	
	public function testLoadPackFileNoPresent()
    {
		$pack = Packman::loadPackFile('nofile');
		$this->expectExceptionMessage('Invalid package name');
	}
	
	/**
     * @covers Baloo\Packman\Packman::loadPackFile	
     * @expectedException Baloo\Packman\PackmanException
     */	
	public function testLoadPackFileEmpty()
    {
		$pack = Packman::loadPackFile('empty');
		$this->expectExceptionMessage('Syntax error, malformed JSON.');
	}
	
}