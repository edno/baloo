<?php

use Baloo\Packman\Packman;

use org\bovigo\vfs\vfsStream;

/**
 * Using mock singleton requires to execute tests in a separate process
 * @runTestsInSeparateProcesses
 */
class PackmanMockTest extends \Baloo\UnitTest\TestCase
{
    public $packman;

    static public $pathPack;

    static public function setUpBeforeClass()
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
     * @group public
     * @group mock
     */
    public function testInstallPackUseMockObjectExceptionPackExist()
    {
        //return $this->markTestSkipped('Issue with getMockFromSingleton');
        // mock core classes
        $mockDSManager = $this->getMockFromSingleton('Baloo\DataSourceManager',
                                                     ['getDataSourceTypeID']);
        $mockDSManager
            ->expects($this->at(1))
            ->method('getDataSource')
            ->willReturn(true);
        $package = 'pack4test';
        $pack = $this->packman->loadPackFile($package);
        $this->packman->installPack($pack)
    }
}
