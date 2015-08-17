<?php
namespace WurflTest\Device\Xml;

use Wurfl\Device\Xml\DeviceIterator;

/**
 * test case
 */
class DeviceIteratorTest
    extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testShouldLaunchExceptionForInvalidInputFile()
    {
        $wurflFile = '';
        new DeviceIterator($wurflFile);
    }

    public function testShouldReadTheSpecificAttribute()
    {
        $deviceIterator = new DeviceIterator('tests/resources/wurfl-specific-attribute.xml');
        $devices        = $this->toList($deviceIterator);

        self::assertEquals('foo', $devices[0]->id);
        self::assertTrue($devices[0]->specific);

        self::assertFalse($devices[1]->specific);
    }

    private function toList($deviceIterator)
    {
        $deviceList = array();
        foreach ($deviceIterator as $device) {
            $deviceList[] = $device;
        }

        return $deviceList;
    }
}
