<?php

namespace WurflTest\Storage;

/*
 * test case
 */
use Wurfl\Storage\Storage;
use WurflCache\Adapter\Memory;

/**
 * test case.
 */
class StorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Wurfl\Storage\Storage
     */
    private $root = null;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    public function setUp()
    {
        $adapter    = new Memory();
        $this->root = new Storage($adapter);
    }

    /**
     * tests Saving and Loading a record with expiration time
     */
    public function testSaveAndLoad()
    {
        $params = array(
            'dir' => 'abc',
        );

        $this->root->save('Test', $params, 60);

        self::assertSame($params, $this->root->load('Test'));
    }

    /**
     * tests Saving and Loading a record without expiration time
     */
    public function testSaveAndLoadWithoutExpiration()
    {
        $params = array(
            'dir' => 'abc',
        );

        $this->root->save('Test', $params);

        self::assertSame($params, $this->root->load('Test'));
    }

    /**
     * tests Deleting a record
     */
    public function testRemove()
    {
        $params = array(
            'dir' => 'abc',
        );

        $this->root->save('Test', $params);
        $this->root->remove('Test');

        self::assertNull($this->root->load('Test'));
    }

    /**
     * tests Clearing the cache
     */
    public function testClear()
    {
        $params = array(
            'dir' => 'abc',
        );

        $this->root->save('Test', $params);
        $this->root->clear();

        self::assertNull($this->root->load('Test'));
    }

    /**
     * tests Saving and Loading a record without expiration time
     */
    public function testWurflLoaded()
    {
        $this->root->setWURFLLoaded(true);

        self::assertTrue($this->root->isWURFLLoaded());
    }
}
