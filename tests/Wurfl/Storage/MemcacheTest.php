<?php
use Wurfl\Storage\Memcache;

/**
 * test case
 */

/**
 * test case.
 */
class WURFL_Storage_MemcacheTest extends PHPUnit_Framework_TestCase
{

    public function testMultipleServerConfiguration()
    {
        $params = array(
            "host" => "127.0.0.1;127.0.0.2"
        );
        $this->checkDeps();
        new Memcache($params);
    }

    /**
     * @covers Memcache::save
     * @covers Memcache::load
     */
    public function testNeverToExpireItems()
    {
        $this->checkDeps();
        $storage = new Memcache();
        $storage->save("foo", "foo");
        sleep(2);
        self::assertEquals("foo", $storage->load("foo"));
    }

    /**
     * @covers Memcache::save
     * @covers Memcache::load
     */
    public function testShouldRemoveTheExpiredItem()
    {
        $this->checkDeps();
        $params  = array(\Wurfl\Configuration\Config::EXPIRATION => 1);
        $storage = new Memcache($params);
        $storage->save("key", "value");
        sleep(2);
        self::assertEquals(null, $storage->load("key"));
    }

    /**
     * @covers Memcache::save
     * @covers Memcache::load
     * @covers Memcache::clear
     */
    public function testShouldClearAllItems()
    {
        $this->checkDeps();
        $storage = new Memcache(array());
        $storage->save("key1", "item1");
        $storage->save("key2", "item2");
        $storage->clear();
        $this->assertThanNoElementsAreInStorage(array("key1", "key2"), $storage);
    }

    /**
     * @param array    $keys
     * @param Memcache $storage
     */
    private function assertThanNoElementsAreInStorage(array $keys = array(), $storage)
    {
        foreach ($keys as $key) {
            self::assertNull($storage->load($key));
        }
    }

    private function checkDeps()
    {
        if (!extension_loaded('memcache')) {
            $this->markTestSkipped(
                "PHP extension 'memcache' must be loaded and a local memcache server running to run this test."
            );
        }
    }
}
