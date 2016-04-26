<?php

namespace Baloo\UnitTests;

use Baloo\Packman\Packman;

/**
 * Using mock singleton requires to execute tests in a separate process
 *
 * @runTestsInSeparateProcesses
 */
class PackmanMockTest extends Framework\TestCase
{
    public $packman;

    public static $pathPack;

    public static function setUpBeforeClass()
    {
        self::$pathPack = __ROOT__.$GLOBALS['PATH_PACKS'];
    }

    public function setUp()
    {
        // prepare stubs
        $this->stubBalooContext();
        $this->stubBalooSourceManager();

        // hack the default pack location for testing purpose
        $this->packman = Packman::getInstance();
        self::setPrivateProperty($this->packman, 'packPath', self::$pathPack);
    }

    public function stubBalooContext()
    {
        // stub pdo connection
        $stub = $this->getMockFromSingleton(
            'Baloo\BalooContext',
            ['getPDO']
        );
        $stub->method('getPDO')
             ->willReturn(new \StdClass());
    }

    public function stubBalooSourceManager()
    {
        $stub = $this->getMockFromSingleton(
            'Baloo\DataSourceManager',
            ['getDataSourceByName']
        );
        $stub->expects($this->any())
             ->method('getDataSourceByName')
             ->willReturn(true);
        self::setPrivateProperty('Baloo\Packman\Packman', 'dsManager', $stub);
    }

    public function tearDown()
    {
    }

    /**
     * @covers Baloo\Packman\Packman::installPack
     * @expectedException Baloo\Packman\PackmanException
     * @group public
     * @group mock
     */
    public function testInstallPackExceptionPackExist()
    {
        $package = 'pack4test';
        $pack = $this->packman->loadPackFile($package);
        $this->packman->installPack($pack);
    }
}
