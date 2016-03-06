<?php

namespace WurflTest\Storage;

use Wurfl\Storage\Factory;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
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
