<?php
/**
 * test case
 */

/**
 * \Wurfl\CustomDevice test case.
 */
class Wurfl_CustomDeviceTest extends PHPUnit_Framework_TestCase
{

    public function testShouldLaunchExceptionIfPassedArraysDoesNotContainAtLeastOneDevice()
    {
        try {
            new \Wurfl\CustomDevice (array());
        } catch (InvalidArgumentException $expected) {
            return;
        }
        $this->fail('An expected exception has not been raised.');
    }

    public function testShouldTreatNullCapablityValuesAsValidValue()
    {
        $modelDevice = new \Wurfl\Xml\ModelDevice ("parent", "ua", "root", true, false, array("product_info" => array("claims_web_support" => null)));

        $device          = new \Wurfl\CustomDevice (array($modelDevice));
        $capabilityValue = $device->getCapability("claims_web_support");
        self::assertEquals("", $capabilityValue);
    }

    public function testShouldLaunchExceptionIfCapabilityNameIsNotDefined()
    {
        $modelDevice      = new \Wurfl\Xml\ModelDevice ("parent", "ua", "root", true, false, array("product_info" => array("claims_web_support" => "true")));
        $childModelDevice = new \Wurfl\Xml\ModelDevice ("id", "ua", "parent", true, false, array("product_info" => array("is_wireless_device" => "true")));

        try {
            $device = new \Wurfl\CustomDevice (array($childModelDevice, $modelDevice));
            $device->getCapability("inexistent_cap");
        } catch (\InvalidArgumentException $expected) {
            return;
        }
        $this->fail('An expected exception has not been raised.');
    }

    public function testShoulReturnTheDeviceProperties()
    {
        $device = new \Wurfl\CustomDevice (array($this->mockModelDevice()));
        self::assertEquals($device->id, "parent");
        self::assertEquals($device->userAgent, "ua");
        self::assertEquals($device->fallBack, "root");
        self::assertEquals($device->actualDeviceRoot, true);
    }

    public function testShouldLaunchExceptionForInvalidCapabilityName()
    {
        try {
            $device = new \Wurfl\CustomDevice (array($this->mockModelDevice()));
            $device->getCapability("");
        } catch (\InvalidArgumentException $expected) {
            return;
        }
        $this->fail('An expected exception has not been raised.');
    }

    public function testShouldLaunchExceptionIfCapabilityNameIsNotDefined1()
    {
        try {
            $device = new \Wurfl\CustomDevice (array($this->mockModelDevice()));
            $device->getCapability("inexistent");
        } catch (\InvalidArgumentException $expected) {
            return;
        }
        $this->fail('An expected exception has not been raised.');
    }

    public function testShouldReturnCapabilityDefinedInModelDevice()
    {
        $modelDevice = new \Wurfl\Xml\ModelDevice ("id", "ua", "root", true, false, array("product_info" => array("is_wireless_device" => "true")));
        $device      = new \Wurfl\CustomDevice (array($modelDevice));

        $capabilityValue = $device->getCapability("is_wireless_device");
        self::assertEquals("true", $capabilityValue);
    }

    public function testShouldRetrunCapabilityDefinedInParentModelDevices()
    {
        $modelDevice      = new \Wurfl\Xml\ModelDevice ("parent", "ua", "root", true, false, array("product_info" => array("claims_web_support" => "false")));
        $childModelDevice = new \Wurfl\Xml\ModelDevice ("id", "ua", "parent", true, false, array("product_info" => array("is_wireless_device" => "true")));

        $device          = new \Wurfl\CustomDevice (array($childModelDevice, $modelDevice));
        $capabilityValue = $device->getCapability("claims_web_support");
        self::assertEquals("false", $capabilityValue);
    }

    public function testShouldReturnAllCapabilities()
    {
        $modelDevice      = new \Wurfl\Xml\ModelDevice ("parent", "ua", "root", true, false, array("product_info" => array("claims_web_support" => "false")));
        $childModelDevice = new \Wurfl\Xml\ModelDevice ("id", "ua", "parent", true, false, array("product_info" => array("is_wireless_device" => "true")));

        $device          = new \Wurfl\CustomDevice (array($childModelDevice, $modelDevice));
        $allCapabilities = $device->getAllCapabilities();
        self::assertEquals($allCapabilities, array("claims_web_support" => "false", "is_wireless_device" => "true"));
    }

    private function mockModelDevice()
    {
        return new \Wurfl\Xml\ModelDevice ("parent", "ua", "root", true, false, array("product_info" => array("claims_web_support" => "false")));
    }

    public function testShouldBeNotSpecificIfHasNotActualDeviceRootInHierarchy()
    {
        $modelDevices = array(
            new \Wurfl\Xml\ModelDevice ("3", "", "", "", false), new \Wurfl\Xml\ModelDevice ("2", "", "", "", false),
            new \Wurfl\Xml\ModelDevice ("generic", "", "", "", false)
        );

        $device = new \Wurfl\CustomDevice ($modelDevices);
        self::assertFalse($device->isSpecific());
    }

    public function testShouldBeNotSpecificIfSpecificIsFalse()
    {
        $modelDevice = new \Wurfl\Xml\ModelDevice ("", "", "", "", false);
        $device      = new \Wurfl\CustomDevice (array($modelDevice));
        self::assertFalse($device->isSpecific());
    }

    public function testShouldBeSpecificIfSpecificIsTrue()
    {
        $modelDevice = new \Wurfl\Xml\ModelDevice ("", "", "", "", true);
        $device      = new \Wurfl\CustomDevice (array($modelDevice));
        self::assertTrue($device->isSpecific());
    }

    public function testShouldBeSpecificIfHasActualDeviceRootInHierarchy()
    {
        $modelDevice = new \Wurfl\Xml\ModelDevice ("", "", "", "", true);
        $device      = new \Wurfl\CustomDevice (array($modelDevice));
        self::assertTrue($device->isSpecific());
    }
}

