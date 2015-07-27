<?php
namespace WurflTest\Configuration;

/**
 * test case
 */
use Wurfl\Configuration\XmlConfig;

/**
 *  test case.
 */
class XmlConfigTest
    extends \PHPUnit_Framework_TestCase
{

    public function testShouldCreateAConfiguration()
    {
        $configPath = 'tests/resources/wurfl-config.xml';
        $config     = new XmlConfig($configPath);

        self::assertNotNull($config->persistence);

        self::assertEquals(realpath('tests/resources/wurfl-regression.xml'), $config->wurflFile);
        self::assertEquals(
            array(
                realpath('tests/resources/web_browsers_patch.xml'),
                realpath('tests/resources/spv_patch.xml'),
                realpath('tests/resources/browsers.xml'),
            ),
            $config->wurflPatches
        );

        self::assertEquals(true, $config->allowReload);

        $persistence = $config->persistence;
        self::assertEquals('memory', $persistence['provider']);
        self::assertEquals(
            array(
                'host' => '127.0.0.1',
                'port' => '11211',
            ),
            $persistence['params']
        );

        $cache = $config->cache;
        self::assertEquals('null', $cache['provider']);
        self::assertEquals(array(), $cache['params']);
    }

    public function testShouldCreateConfigurationWithAPCPersistence()
    {
        $configPath = 'tests/resources/wurfl-config-apc-persistence.xml';
        $config     = new XmlConfig($configPath);
        self::assertNotNull($config->persistence);

        self::assertEquals(realpath('tests/resources/wurfl.xml'), $config->wurflFile);
        self::assertEquals(array(realpath('tests/resources/browsers.xml')), $config->wurflPatches);

        self::assertEquals(true, $config->allowReload);

        $persistence = $config->persistence;

        self::assertEquals('apc', $persistence['provider']);
        self::assertEquals(array('namespace' => 'wurflpersist'), $persistence['params']);

        $cache = $config->cache;
        self::assertEquals('apc', $cache['provider']);
        self::assertEquals(
            array(
                'namespace'  => 'wurfl',
                'expiration' => 86400
            ),
            $cache['params']
        );
    }

    public function testShouldAcceptEmptyOptionalElements()
    {
        $configPath = 'tests/resources/wurfl-config-no-optional.xml';
        $config     = new XmlConfig($configPath);

        self::assertEquals(realpath('tests/resources/wurfl.xml'), $config->wurflFile);
        self::assertEquals(array(), $config->wurflPatches);
        self::assertEquals(false, $config->allowReload);

        $persistence = $config->persistence;
        self::assertEquals('apc', $persistence['provider']);
        self::assertEquals(array('namespace' => 'wurflpersist'), $persistence['params']);

        $cache = $config->cache;
        self::assertTrue(empty($cache));
    }
}
