<?php
/**
 * test case
 */
/**
 * test case.
 */
class WURFL_Storage_FileTest extends PHPUnit_Framework_TestCase
{

    const STORAGE_DIR = "../../../resources/storage";

    public function setUp()
    {
        \Wurfl\FileUtils::mkdir(self::storageDir());
    }

    public function tearDown()
    {
        \Wurfl\FileUtils::rmdir(self::storageDir());
    }

    public function testShouldTryToCreateTheStorage()
    {
        $cachepath = $this->realpath(self::STORAGE_DIR . "/cache");
        $params    = array(
            "dir" => $cachepath
        );
        new \Wurfl\Storage\File($params);
        $this->assertStorageDirectoryIsCreated($cachepath);
        \Wurfl\FileUtils::rmdir($cachepath);
    }

    private function realpath($path)
    {
        return __DIR__ . DIRECTORY_SEPARATOR . $path;
    }

    private function assertStorageDirectoryIsCreated($dir)
    {
        self::assertTrue(file_exists($dir) && is_writable($dir));
    }

    public function testNeverToExpireItems()
    {
        $params = array(
            "dir"                                   => self::storageDir(),
            \Wurfl\Configuration\Config::EXPIRATION => 0
        );

        $storage = new \Wurfl\Storage\File($params);

        $storage->save("foo", "foo");
        sleep(1);
        self::assertEquals("foo", $storage->load("foo"));
    }

    public function testShouldRemoveTheExpiredItem()
    {

        $params = array(
            "dir"                                   => self::storageDir(),
            \Wurfl\Configuration\Config::EXPIRATION => 1
        );

        $storage = new \Wurfl\Storage\File($params);

        $storage->save("item2", "item2");
        self::assertEquals("item2", $storage->load("item2"));
        sleep(2);
        self::assertEquals(null, $storage->load("item2"));
    }

    public static function storageDir()
    {
        return __DIR__ . DIRECTORY_SEPARATOR . self::STORAGE_DIR;
    }
}
