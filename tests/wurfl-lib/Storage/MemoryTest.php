<?php
use Wurfl\Storage\Memory;

/**
 * test case
 */

/**
 * test case.
 */
class Wurfl_Storage_MemoryTest extends PHPUnit_Framework_TestCase
{

    public function testNeverToExpireItems()
    {
        $storage = new Memory();
        $storage->save("foo", "foo");
        sleep(2);
        $this->assertEquals("foo", $storage->load("foo"));
    }

    public function testShouldClearAllItems()
    {
        $storage = new Memory(array());
        $storage->save("key1", "item1");
        $storage->save("key2", "item2");
        $storage->clear();

        $this->assertThatNoElementsAreInCache(array("key1", "key2"), $storage);
    }

    /**
     * @param array  $keys
     * @param Memory $storage
     */
    private function assertThatNoElementsAreInCache($keys = array(), Memory $storage)
    {
        foreach ($keys as $key) {
            $this->assertNull($storage->load($key));
        }
    }
}
