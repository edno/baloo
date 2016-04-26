<?php

namespace Baloo\UnitTests;

use Baloo\BalooContext;

class BalooContextTest extends Framework\TestCase
{
    public function setUp()
    {
    }

    public function tearDown()
    {
    }

    /**
     * @covers Baloo\BalooContext:getInstance
     * @group public
     */
    public function testGetInstance()
    {
        $context = BalooContext::getInstance();
        $this->assertInstanceOf('\Baloo\BalooContext', $context);
    }

    /**
     * @covers Baloo\BalooContext:getPDO
     * @expectedException Baloo\BalooException
     * @group public
     */
    public function testGetPdoExceptionNoPdo()
    {
        $context = BalooContext::getInstance();
        $context->getPDO();
    }

    /**
     * @covers Baloo\BalooContext::loadLibrary
     * @dataProvider providerLoadLibrary
     * @group public
     */
    public function testLoadLibrary($library, $namespace, $function)
    {
        $result = BalooContext::loadLibrary($library);
        $this->assertTrue($result);
        $this->assertTrue(function_exists("${namespace}\\${function}"));
    }

    /**
     * @covers Baloo\BalooContext::loadLibrary
     * @dataProvider providerLoadLibrary
     */
    public function testLoadLibraryWithNullParam($library, $namespace, $function)
    {
        $result = BalooContext::loadLibrary();
        $this->assertTrue($result);
        $this->assertTrue(function_exists("${namespace}\\${function}"));
    }

    /**
     * @covers Baloo\BalooContext::loadLibrary
     * @expectedException Baloo\BalooException
     */
    public function testLoadLibraryExceptionInvalidLibrary()
    {
        $result = BalooContext::loadLibrary('invalid');
        $this->assertNotTrue($result);
    }

    public function providerLoadLibrary()
    {
        return [
            'Lib\Json' => ['json', 'Baloo\Lib\Json', 'json_valid'],
            'Lib\Arrays' => ['arrays', 'Baloo\Lib\Arrays', 'array_diff_assoc_recursive'],
            'Lib\Console' => ['console', 'Baloo\Lib\Console', 'read_console']
        ];
    }
}
