<?php

namespace WurflTest\Storage;

use Wurfl\Storage\Factory;

/**
 * Class FactoryTest
 *
 * @group Storage
 */
class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        self::markTestSkipped('need to rewrite');
    }

    /**
     * tests creating a NullStorage
     */
    public function testCreateNullStorage()
    {
        $configuration = array('provider' => 'null');

        $result = Factory::create($configuration);

        self::assertInstanceOf('\Wurfl\Storage\Storage', $result);
        self::assertInstanceOf('\WurflCache\Adapter\NullStorage', $result->getAdapter());
    }

    /**
     * tests creating a MemoryStorage
     */
    public function testCreateMemoryStorage()
    {
        $configuration = array();

        $result = Factory::create($configuration);

        self::assertInstanceOf('\Wurfl\Storage\Storage', $result);
        self::assertInstanceOf('\WurflCache\Adapter\Memory', $result->getAdapter());
    }
}
