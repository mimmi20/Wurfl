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

        self::assertNotNull($config->persistence);

        self::assertEquals(__DIR__ . DIRECTORY_SEPARATOR . "wurfl.xml", $config->wurflFile);
        self::assertEquals(array(__DIR__ . DIRECTORY_SEPARATOR . "browsers.xml"), $config->wurflPatches);

        self::assertEquals(true, $config->allowReload);

        $cacheDir    = __DIR__ . DIRECTORY_SEPARATOR . "cache";
        $persistence = $config->persistence;
        self::assertEquals("file", $persistence ["provider"]);
        self::assertEquals(array(\Wurfl\Configuration\Config::DIR => $cacheDir), $persistence ["params"]);

        $cache = $config->cache;
        self::assertEquals("file", $cache ["provider"]);
        self::assertEquals(
            array(\Wurfl\Configuration\Config::DIR => $cacheDir, 'expiration' => 36000),
            $cache ["params"]
        );
    }

    public function testShouldCreateConfigurationWithAPCPersistence()
    {
        $configPath = __DIR__ . DIRECTORY_SEPARATOR . "wurfl-config-apc-persistence.xml";
        $config     = new \Wurfl\Configuration\XmlConfig($configPath);
        self::assertNotNull($config->persistence);

        self::assertEquals(__DIR__ . DIRECTORY_SEPARATOR . "wurfl.xml", $config->wurflFile);
        self::assertEquals(array(__DIR__ . DIRECTORY_SEPARATOR . "browsers.xml"), $config->wurflPatches);

        self::assertEquals(true, $config->allowReload);

        $persistence = $config->persistence;

        self::assertEquals("apc", $persistence ["provider"]);
        self::assertEquals(array("namespace" => "wurflpersist"), $persistence ["params"]);

        $cache = $config->cache;
        self::assertEquals("apc", $cache ["provider"]);
        self::assertEquals(
            array(
                 "namespace"  => "wurfl",
                 "expiration" => 86400
            ),
            $cache ["params"]
        );
    }

    public function testShouldAcceptEmptyOptionalElements()
    {
        $configPath = __DIR__ . DIRECTORY_SEPARATOR . "wurfl-config-no-optional.xml";
        $config     = new \Wurfl\Configuration\XmlConfig($configPath);

        self::assertEquals(__DIR__ . DIRECTORY_SEPARATOR . "wurfl.xml", $config->wurflFile);
        self::assertEquals(array(), $config->wurflPatches);
        self::assertEquals(false, $config->allowReload);

        $persistence = $config->persistence;
        self::assertEquals("apc", $persistence ["provider"]);
        self::assertEquals(array("namespace" => "wurflpersist"), $persistence ["params"]);

        $cache = $config->cache;
        self::assertTrue(empty($cache));
    }
}

