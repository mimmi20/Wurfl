<?php
/**
 * test case
 */
/**
 *  test case.
 */
class WURFL_Reloader_DefaultWURFLReloaderTest extends PHPUnit_Framework_TestCase
{

    const WURFL_CONFIG_FILE = "../../resources/wurfl-config-reloading.xml";

    protected function setUp()
    {
        parent::setUp();
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @deprecated
     */
    public function shoudLaunchExceptionForInvalidConfigurationFilePath()
    {
        $configurationFilePath = "";
        $wurflReloader         = new \Wurfl\Reloader\DefaultWurflReloader();
        $wurflReloader->reload($configurationFilePath);
    }

    /**
     * @test
     * @deprecated
     */
    public function shoudReloadTheWurfl()
    {
        $configurationFilePath = __DIR__ . DIRECTORY_SEPARATOR . self::WURFL_CONFIG_FILE;
        $wurflReloader         = new \Wurfl\Reloader\DefaultWurflReloader();
        $wurflReloader->reload($configurationFilePath);
    }

    protected function tearDown()
    {
        parent::tearDown();
    }
}

