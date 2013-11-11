<?php
/**
 * test case
 */

/**
 *  test case.
 */
class Wurfl_Configuration_XmlConfigTest extends PHPUnit_Framework_TestCase
{

    public function testShouldCreateAConfiguration()
    {
        $configPath = __DIR__ . DIRECTORY_SEPARATOR . "wurfl-config.xml";
        $config     = new \Wurfl\Configuration\XmlConfig($configPath);
        $this->assertNotNull($config->persistence);

        $this->assertEquals(__DIR__ . DIRECTORY_SEPARATOR . "wurfl.xml", $config->wurflFile);
        $this->assertEquals(array(__DIR__ . DIRECTORY_SEPARATOR . "browsers.xml"), $config->wurflPatches);

        $this->assertEquals(true, $config->allowReload);

        $cacheDir    = __DIR__ . DIRECTORY_SEPARATOR . "cache";
        $persistence = $config->persistence;
        $this->assertEquals("file", $persistence ["provider"]);
        $this->assertEquals(array(\Wurfl\Configuration\Config::DIR => $cacheDir), $persistence ["params"]);

        $cache = $config->cache;
        $this->assertEquals("file", $cache ["provider"]);
        $this->assertEquals(
            array(\Wurfl\Configuration\Config::DIR => $cacheDir, \Wurfl\Configuration\Config::EXPIRATION => 36000),
            $cache ["params"]
        );
    }

    public function testShouldCreateConfigurationWithAPCPersistence()
    {
        $configPath = __DIR__ . DIRECTORY_SEPARATOR . "wurfl-config-apc-persistence.xml";
        $config     = new \Wurfl\Configuration\XmlConfig($configPath);
        $this->assertNotNull($config->persistence);

        $this->assertEquals(__DIR__ . DIRECTORY_SEPARATOR . "wurfl.xml", $config->wurflFile);
        $this->assertEquals(array(__DIR__ . DIRECTORY_SEPARATOR . "browsers.xml"), $config->wurflPatches);

        $this->assertEquals(true, $config->allowReload);

        $persistence = $config->persistence;

        $this->assertEquals("apc", $persistence ["provider"]);
        $this->assertEquals(array("namespace" => "wurflpersist"), $persistence ["params"]);

        $cache = $config->cache;
        $this->assertEquals("apc", $cache ["provider"]);
        $this->assertEquals(
            array(
                 "namespace"  => "wurfl",
                 "expiration" => 86400
            ), $cache ["params"]
        );
    }

    public function testShouldAcceptEmptyOptionalElements()
    {
        $configPath = __DIR__ . DIRECTORY_SEPARATOR . "wurfl-config-no-optional.xml";
        $config     = new \Wurfl\Configuration\XmlConfig($configPath);

        $this->assertEquals(__DIR__ . DIRECTORY_SEPARATOR . "wurfl.xml", $config->wurflFile);
        $this->assertEquals(array(), $config->wurflPatches);
        $this->assertEquals(false, $config->allowReload);

        $persistence = $config->persistence;
        $this->assertEquals("apc", $persistence ["provider"]);
        $this->assertEquals(array("namespace" => "wurflpersist"), $persistence ["params"]);

        $cache = $config->cache;
        $this->assertTrue(empty($cache));
    }
}

