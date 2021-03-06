<?php

namespace WurflTest\Device\Xml;

use Wurfl\Device\Xml\DeviceIterator;

/**
 * test case
 *
 * @group Device
 */
class DeviceIteratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testShouldLaunchExceptionForInvalidInputFile()
    {
        $wurflFile = '';
        new DeviceIterator($wurflFile);
    }
}
