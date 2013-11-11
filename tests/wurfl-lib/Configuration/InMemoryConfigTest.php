<?php
/**
 * test case
 */

/**
 * test case.
 */
class Wurfl_Configuration_InMemoryConfigTest extends PHPUnit_Framework_TestCase
{

    public function testShouldCreateFilePersistence()
    {
        $config = new \Wurfl\Configuration\InMemoryConfig ();
        $config->wurflFile("./wurfl.xml")
            ->wurflPatch("./new_web_browsers_patch.xml")
            ->wurflPatch("./spv_patch.xml")
            ->allowReload(true)
            ->persistence("file", array("dir" => "./cache"))
            ->cache(
                "file",
                array(\Wurfl\Configuration\Config::DIR => "./cache", \Wurfl\Configuration\Config::EXPIRATION => 3600)
            );

        $this->assertNotNull($config->persistence);

        $this->assertEquals("./wurfl.xml", $config->wurflFile);
        $this->assertEquals(array("./new_web_browsers_patch.xml", "./spv_patch.xml"), $config->wurflPatches);

        $persistence = $config->persistence;
        $this->assertEquals("file", $persistence ["provider"]);

        $this->assertTrue($config->allowReload);
    }

    public function testShouldCreateConfiguration()
    {
        $config = new \Wurfl\Configuration\InMemoryConfig ();
        $params = array("host" => "127.0.0.1");
        $config->wurflFile("wurfl.xml")->wurflPatch("new_web_browsers_patch.xml")->wurflPatch("spv_patch.xml")
            ->persistence("memcache", $params)
            ->cache(
                "file",
                array(\Wurfl\Configuration\Config::DIR => "./cache", \Wurfl\Configuration\Config::EXPIRATION => 3600)
            );

        $this->assertNotNull($config->persistence);

        $this->assertEquals("wurfl.xml", $config->wurflFile);
        $this->assertEquals(array("new_web_browsers_patch.xml", "spv_patch.xml"), $config->wurflPatches);

        $persistence = $config->persistence;
        $this->assertEquals("memcache", $persistence ["provider"]);
        $this->assertEquals(array("host" => "127.0.0.1"), $persistence ["params"]);

        $cache = $config->cache;
        $this->assertEquals("file", $cache ["provider"]);
        $this->assertEquals(
            array(\Wurfl\Configuration\Config::DIR => "./cache", \Wurfl\Configuration\Config::EXPIRATION => 3600),
            $cache ["params"]
        );
    }

    public function testShouldCreateConfigurationWithAPCPersistenceProviderAndAPCCacheProvider()
    {
        $config = new \Wurfl\Configuration\InMemoryConfig ();
        $params = array("host" => "127.0.0.1");
        $config->wurflFile("wurfl.xml")
            ->wurflPatch("new_web_browsers_patch.xml")->wurflPatch("spv_patch.xml")
            ->persistence(\Wurfl\Storage\Apc::EXTENSION_MODULE_NAME, $params)
            ->cache(\Wurfl\Storage\Apc::EXTENSION_MODULE_NAME, $params);

        $this->assertNotNull($config->persistence);

        $this->assertEquals("wurfl.xml", $config->wurflFile);
        $this->assertEquals(array("new_web_browsers_patch.xml", "spv_patch.xml"), $config->wurflPatches);

        $persistence = $config->persistence;
        $this->assertEquals("apc", $persistence ["provider"]);
        $this->assertEquals($params, $persistence ["params"]);

        $cache = $config->cache;
        $this->assertEquals("apc", $cache ["provider"]);
        $this->assertEquals($params, $cache ["params"]);
    }

    public function testShouldCreateConfigurationForMultipleMemcacheBackend()
    {
        $config = new \Wurfl\Configuration\InMemoryConfig ();
        $params = array(
            "host"      => "10.211.55.10;10.211.55.2",
            "port"      => "11211",
            "namespace" => "wurfl"
        );
        $config->wurflFile("wurfl.xml")
            ->wurflPatch("new_web_browsers_patch.xml")->wurflPatch("spv_patch.xml")
            ->persistence("memcache", $params);
    }
}

