<?php

namespace Baloo\UnitTests;

use Baloo\Packman\Packman;
use org\bovigo\vfs\vfsStream;

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
        $this->packman = Packman::getInstance();
        // hack the default pack location for testing purpose
        self::setPrivateProperty($this->packman, 'packPath', self::$pathPack);
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
    public function testInstallPackUseMockObjectExceptionPackExist()
    {
        // mock core classes
        $mockDSManager = $this->getMockFromSingleton(
            'Baloo\DataSourceManager',
            ['getDataSourceTypeID',
                                                      'insertDataSourceType',
            'getDataSource']
        );
        $mockDSManager
            ->expects($this->at(1))
            ->method('getDataSource')
            ->willReturn(true);

        $package = 'pack4test';
        $pack = $this->packman->loadPackFile($package);
        $this->packman->installPack($pack);
    }
}