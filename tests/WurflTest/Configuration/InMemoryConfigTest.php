<?php
namespace WurflTest\Configuration;

/**
 * test case
 */
use Wurfl\Configuration\Config;
use Wurfl\Configuration\InMemoryConfig;

/**
 * test case.
 */
class InMemoryConfigTest
    extends \PHPUnit_Framework_TestCase
{

    public function testShouldCreateFilePersistence()
    {
        $config = new InMemoryConfig();
        $config->wurflFile('./wurfl.xml')
            ->wurflPatch('./new_web_browsers_patch.xml')
            ->wurflPatch('./spv_patch.xml')
            ->allowReload(true)
            ->persistence('file', array('dir' => './cache'))
            ->cache(
                'file',
                array(Config::DIR => './cache', Config::EXPIRATION => 3600)
            );

        self::assertNotNull($config->persistence);

        self::assertEquals('./wurfl.xml', $config->wurflFile);
        self::assertEquals(array('./new_web_browsers_patch.xml', './spv_patch.xml'), $config->wurflPatches);

        $persistence = $config->persistence;
        self::assertEquals('file', $persistence ['provider']);

        self::assertTrue($config->allowReload);
    }

    public function testShouldCreateConfiguration()
    {
        $config = new InMemoryConfig();
        $params = array('host' => '127.0.0.1');
        $config->wurflFile('./wurfl.xml')
            ->wurflPatch('./new_web_browsers_patch.xml')
            ->wurflPatch('./spv_patch.xml')
            ->persistence('memcache', $params)
            ->cache(
                'file',
                array(Config::DIR => './cache', Config::EXPIRATION => 3600)
            );

        self::assertNotNull($config->persistence);

        self::assertEquals('./wurfl.xml', $config->wurflFile);
        self::assertEquals(array('./new_web_browsers_patch.xml', './spv_patch.xml'), $config->wurflPatches);

        $persistence = $config->persistence;
        self::assertEquals('memcache', $persistence ['provider']);
        self::assertEquals(array('host' => '127.0.0.1'), $persistence ['params']);

        $cache = $config->cache;
        self::assertEquals('file', $cache ['provider']);
        self::assertEquals(
            array(Config::DIR => './cache', Config::EXPIRATION => 3600),
            $cache ['params']
        );
    }

    public function testShouldCreateConfigurationForMultipleMemcacheBackend()
    {
        $config = new InMemoryConfig();
        $params = array(
            'host'      => '10.211.55.10;10.211.55.2',
            'port'      => '11211',
            'namespace' => 'wurfl'
        );
        $config->wurflFile('wurfl.xml')
            ->wurflPatch('new_web_browsers_patch.xml')
            ->wurflPatch('spv_patch.xml')
            ->persistence('memcache', $params);
    }
}
