<?php

namespace WurflTest\Configuration;

/**
 * test case
 */
use Wurfl\Configuration\Config;
use Wurfl\Configuration\FileConfig;

/**
 * test case.
 *
 * @group Configuration
 */
class XmlConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldCreateAConfiguration()
    {
        $configPath = 'tests/resources/wurfl-config.xml';
        $config     = new FileConfig($configPath);

        self::assertNotNull($config->persistence);

        self::assertEquals(realpath('tests/resources/wurfl.xml'), $config->wurflFile);
        self::assertTrue($config->allowReload);

        $persistence = $config->persistence;
        self::assertEquals('memory', $persistence[Config::PROVIDER]);
        self::assertEquals(
            array(
                'host' => '127.0.0.1',
                'port' => '11211',
            ),
            $persistence[Config::PARAMS]
        );

        $cache = $config->cache;
        self::assertEquals('null', $cache[Config::PROVIDER]);
        self::assertEquals(array(), $cache[Config::PARAMS]);
    }

    public function testShouldCreateConfigurationWithAPCPersistence()
    {
        $configPath = 'tests/resources/wurfl-config-apc-persistence.xml';
        $config     = new FileConfig($configPath);
        self::assertNotNull($config->persistence);

        self::assertEquals(realpath('tests/resources/wurfl.xml'), $config->wurflFile);
        self::assertEquals(array(realpath('tests/resources/browsers.xml')), $config->wurflPatches);

        self::assertTrue($config->allowReload);

        $persistence = $config->persistence;

        self::assertEquals('apc', $persistence[Config::PROVIDER]);
        self::assertEquals(array('namespace' => 'wurflpersist'), $persistence[Config::PARAMS]);

        $cache = $config->cache;
        self::assertEquals('apc', $cache[Config::PROVIDER]);
        self::assertEquals(
            array(
                'namespace'  => 'wurfl',
                'expiration' => 86400,
            ),
            $cache[Config::PARAMS]
        );
    }

    public function testShouldAcceptEmptyOptionalElements()
    {
        $configPath = 'tests/resources/wurfl-config-no-optional.xml';
        $config     = new FileConfig($configPath);

        self::assertEquals(realpath('tests/resources/wurfl.xml'), $config->wurflFile);
        self::assertEquals(array(), $config->wurflPatches);
        self::assertFalse($config->allowReload);

        $persistence = $config->persistence;
        self::assertEquals('apc', $persistence[Config::PROVIDER]);
        self::assertEquals(array('namespace' => 'wurflpersist'), $persistence[Config::PARAMS]);

        $cache = $config->cache;
        self::assertTrue(empty($cache));
    }
}
