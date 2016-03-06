<?php

namespace WurflTest;

/*
 * test case
 */
use Wurfl\CustomDevice;
use Wurfl\Device\ModelDevice;

/**
 * \Wurfl\CustomDevice test case.
 */
class CustomDeviceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage modelDevices must be an array of at least one ModelDevice.
     */
    public function testShouldLaunchExceptionIfPassedArraysDoesNotContainAtLeastOneDevice()
    {
        new CustomDevice(array());
    }

    public function testShouldTreatNullCapablityValuesAsValidValue()
    {
        $modelDevice = new ModelDevice(
            'parent',
            'ua',
            'root',
            true,
            false,
            array('product_info' => array('claims_web_support' => null))
        );

        $device          = new CustomDevice(array($modelDevice));
        $capabilityValue = $device->getCapability('claims_web_support');
        self::assertEquals('', $capabilityValue);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage each ModelDevice has to implement the \Wurfl\Device\ModelDeviceInterface
     */
    public function testShouldLaunchExceptionIfWrongTypeIsInArray()
    {
        $modelDevice = new ModelDevice(
            'parent',
            'ua',
            'root',
            true,
            false,
            array('product_info' => array('claims_web_support' => 'true'))
        );

        new CustomDevice(array($modelDevice, null));
    }

    public function testShoulReturnTheDeviceProperties()
    {
        $device = new CustomDevice(array($this->mockModelDevice()));

        self::assertEquals('parent', $device->id);
        self::assertEquals('ua', $device->userAgent);
        self::assertEquals('root', $device->fallBack);
        self::assertTrue($device->actualDeviceRoot);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage capability name must not be empty
     */
    public function testShouldLaunchExceptionForInvalidCapabilityName()
    {
        $device = new CustomDevice(array($this->mockModelDevice()));
        $device->getCapability('');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage no capability named [inexistent] is present in wurfl.
     */
    public function testShouldLaunchExceptionIfCapabilityNameIsNotDefined1()
    {
        $device = new CustomDevice(array($this->mockModelDevice()));
        $device->getCapability('inexistent');
    }

    public function testShouldReturnCapabilityDefinedInModelDevice()
    {
        $modelDevice = new ModelDevice(
            'id',
            'ua',
            'root',
            true,
            false,
            array('product_info' => array('is_wireless_device' => 'true'))
        );
        $device      = new CustomDevice(array($modelDevice));

        $capabilityValue = $device->getCapability('is_wireless_device');
        self::assertSame('true', $capabilityValue);
    }

    public function testShouldRetrunCapabilityDefinedInParentModelDevices()
    {
        $modelDevice = new ModelDevice(
            'parent',
            'ua',
            'root',
            true,
            false,
            array('product_info' => array('claims_web_support' => 'false'))
        );

        $childModelDevice = new ModelDevice(
            'id',
            'ua',
            'parent',
            true,
            false,
            array('product_info' => array('is_wireless_device' => 'true'))
        );

        $device          = new CustomDevice(array($childModelDevice, $modelDevice));
        $capabilityValue = $device->getCapability('claims_web_support');
        self::assertSame('false', $capabilityValue);
    }

    public function testShouldReturnAllCapabilities()
    {
        $modelDevice      = new ModelDevice(
            'parent',
            'ua',
            'root',
            true,
            false,
            array('product_info' => array('claims_web_support' => 'false'))
        );
        $childModelDevice = new ModelDevice(
            'id',
            'ua',
            'parent',
            true,
            false,
            array('product_info' => array('is_wireless_device' => 'true'))
        );

        $device          = new CustomDevice(array($childModelDevice, $modelDevice));
        $allCapabilities = $device->getAllCapabilities();
        self::assertSame(array('claims_web_support' => 'false', 'is_wireless_device' => 'true'), $allCapabilities);
    }

    private function mockModelDevice()
    {
        return new ModelDevice(
            'parent',
            'ua',
            'root',
            true,
            false,
            array('product_info' => array('claims_web_support' => 'false'))
        );
    }

    public function testShouldBeNotSpecificIfHasNotActualDeviceRootInHierarchy()
    {
        $modelDevices = array(
            new ModelDevice('3', '', '', '', false),
            new ModelDevice('2', '', '', '', false),
            new ModelDevice('generic', '', '', '', false),
        );

        $device = new CustomDevice($modelDevices);
        self::assertFalse($device->isSpecific());
    }

    public function testShouldBeNotSpecificIfSpecificIsFalse()
    {
        $modelDevice = new ModelDevice('', '', '', '', false);
        $device      = new CustomDevice(array($modelDevice));
        self::assertFalse($device->isSpecific());
    }

    public function testShouldBeSpecificIfSpecificIsTrue()
    {
        $modelDevice = new ModelDevice('', '', '', '', true);
        $device      = new CustomDevice(array($modelDevice));
        self::assertTrue($device->isSpecific());
    }

    public function testShouldBeSpecificIfHasActualDeviceRootInHierarchy()
    {
        $modelDevice = new ModelDevice('', '', '', '', true);
        $device      = new CustomDevice(array($modelDevice));
        self::assertTrue($device->isSpecific());
    }

    public function testShouldReturnChildDevice()
    {
        $modelDevice      = new ModelDevice(
            'parent',
            'ua',
            'root',
            true,
            false,
            array('product_info' => array('claims_web_support' => 'false'))
        );
        $childModelDevice = new ModelDevice(
            'id',
            'ua',
            'parent',
            true,
            false,
            array('product_info' => array('is_wireless_device' => 'true'))
        );

        $device = new CustomDevice(array($childModelDevice, $modelDevice));
        $result = $device->getActualDeviceRootAncestor();
        self::assertInstanceOf('\Wurfl\Device\ModelDeviceInterface', $result);
        self::assertSame($childModelDevice, $result);
    }

    public function testShouldReturnParentDevice()
    {
        $modelDevice      = new ModelDevice(
            'parent',
            'ua',
            'root',
            true,
            false,
            array('product_info' => array('claims_web_support' => 'false'))
        );
        $childModelDevice = new ModelDevice(
            'id',
            'ua',
            'parent',
            false,
            false,
            array('product_info' => array('is_wireless_device' => 'true'))
        );

        $device = new CustomDevice(array($childModelDevice, $modelDevice));
        $result = $device->getActualDeviceRootAncestor();
        self::assertInstanceOf('\Wurfl\Device\ModelDeviceInterface', $result);
        self::assertSame($modelDevice, $result);
    }

    public function testGetMatchInfo()
    {
        $modelDevice = new ModelDevice('', '', '', '', true);
        $device      = new CustomDevice(array($modelDevice));

        self::assertInstanceOf('\Wurfl\Request\MatchInfo', $device->getMatchInfo());
    }
}
