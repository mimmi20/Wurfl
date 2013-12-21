<?php
namespace WurflTest\Xml;

/**
 * test case
 */

class DevicePatcherTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Wurfl\Xml\DevicePatcher */
    private $devicePatcher;

    public function setUp()
    {
        $this->devicePatcher = new \Wurfl\Xml\DevicePatcher();
    }

    public function testShouldReturnThePatchingDeviceIfForDifferentDevices()
    {
        $deviceToPatch  = new \Wurfl\Xml\ModelDevice("A", "A", "Z");
        $patchingDevice = new \Wurfl\Xml\ModelDevice("B", "B", "Z");

        $patchedDevice = $this->devicePatcher->patch($deviceToPatch, $patchingDevice);
        self::assertEquals($patchingDevice, $patchedDevice);
    }

    public function testShouldOverrideTheCapabilities()
    {
        $deviceToPatch                         = new \Wurfl\Xml\ModelDevice("A", "A", "Z", true, false, array());
        $groupIDMap                = array();
        $groupIDMap["A"]["cap1"] = "cap1";
        $capabilities                          = array();
        $capabilities["cap1"]                  = "cap1";

        $patchingDevice = new \Wurfl\Xml\ModelDevice("B", "B", "Z", true, false, $groupIDMap);
        $patchedDevice  = $this->devicePatcher->patch($deviceToPatch, $patchingDevice);

        self::assertEquals($capabilities, $patchedDevice->capabilities);
    }

    public function testShouldOnlyOverrideTheCapabilitiesSpecifiedByThePatcherDevices()
    {
        $groupIDMap                = array();
        $groupIDMap["A"]["cap1"] = "cap1";
        $groupIDMap["A"]["cap2"] = "cap2";

        $deviceToPatch = new \Wurfl\Xml\ModelDevice("A", "A", "Z", true, false, $groupIDMap);

        $groupIDMap                = array();
        $groupIDMap["A"]["cap1"] = "cap1";
        $groupIDMap["A"]["cap3"] = "cap3";

        $capabilities         = array();
        $capabilities["cap1"] = "cap1";
        $capabilities["cap2"] = "cap2";
        $capabilities["cap3"] = "cap3";

        $patchingDevice = new \Wurfl\Xml\ModelDevice("A", "A", "Z", true, false, $groupIDMap);
        $patchedDevice  = $this->devicePatcher->patch($deviceToPatch, $patchingDevice);
        self::assertEquals($capabilities, $patchedDevice->capabilities);
    }
}
