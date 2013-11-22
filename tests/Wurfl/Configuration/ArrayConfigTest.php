<?php
/**
 * test case
 */

/**
 *  test case.
 */
class Wurfl_Configuration_ArrayConfigTest extends PHPUnit_Framework_TestCase
{

    private $arrayConfig;

    function setUp()
    {
        $configurationFile = __DIR__ . DIRECTORY_SEPARATOR . "wurfl-array-config.php";
        $this->arrayConfig = new \Wurfl\Configuration\ArrayConfig($configurationFile);
    }

    /**
     * @expectedException \InvalidArgumentException
     *
     */
    public function testShoudThrowInvalidArgumentExceptionForNullConfigurationFilePath()
    {
        $configurationFile = null;
        $arrayConfig       = new \Wurfl\Configuration\ArrayConfig($configurationFile);
        self::assertNotNull($arrayConfig);
    }

    public function testShouldCreateAConfigFormArrayFile()
    {
        $resourcesDir = __DIR__ . '/../../resources';
        $wurflFile    = realpath($resourcesDir . '/wurfl-regression.xml');
        self::assertEquals($wurflFile, $this->arrayConfig->wurflFile);
        $expectedWurlPatches = array(
            realpath($resourcesDir . '/web_browsers_patch.xml'),
            realpath($resourcesDir . '/spv_patch.xml')
        );
        self::assertAttributeEquals($expectedWurlPatches, "wurflPatches", $this->arrayConfig);
        self::assertTrue($this->arrayConfig->allowReload);
    }

    public function testShoudCreatePersistenceConfiguration()
    {
        $persistence = $this->arrayConfig->persistence;
        self::assertEquals("memcache", $persistence["provider"]);
        self::assertArrayHasKey("params", $persistence);
    }
}

