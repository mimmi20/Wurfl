<?php
namespace WurflTest;

/**
 * test case
 */
use Wurfl\CustomDevice;
use Wurfl\Xml\ModelDevice;

/**
 * \Wurfl\CustomDevice test case.
 */
class CustomDeviceTest extends \PHPUnit_Framework_TestCase
{

    public function testShouldLaunchExceptionIfPassedArraysDoesNotContainAtLeastOneDevice()
    {
        try {
            new CustomDevice(array());
        } catch (\InvalidArgumentException $expected) {
            return;
        }
        $this->fail('An expected exception has not been raised.');
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

    public function testShouldLaunchExceptionIfCapabilityNameIsNotDefined()
    {
        $modelDevice = new ModelDevice(
            'parent',
            'ua',
            'root',
            true,
            false,
            array('product_info' => array('claims_web_support' => 'true'))
        );
        $childModelDevice = new ModelDevice(
            'id',
            'ua',
            'parent',
            true,
            false,
            array('product_info' => array('is_wireless_device' => 'true'))
        );

        try {
            $device = new CustomDevice(array($childModelDevice, $modelDevice));
            $device->getCapability('inexistent_cap');
        } catch (\InvalidArgumentException $expected) {
            return;
        }
        $this->fail('An expected exception has not been raised.');
    }

    public function testShoulReturnTheDeviceProperties()
    {
        $device = new CustomDevice(array($this->mockModelDevice()));
        self::assertEquals($device->id, 'parent');
        self::assertEquals($device->userAgent, 'ua');
        self::assertEquals($device->fallBack, 'root');
        self::assertEquals($device->actualDeviceRoot, true);
    }

    public function testShouldLaunchExceptionForInvalidCapabilityName()
    {
        try {
            $device = new CustomDevice(array($this->mockModelDevice()));
            $device->getCapability('');
        } catch (\InvalidArgumentException $expected) {
            return;
        }
        $this->fail('An expected exception has not been raised.');
    }

    public function testShouldLaunchExceptionIfCapabilityNameIsNotDefined1()
    {
        try {
            $device = new CustomDevice(array($this->mockModelDevice()));
            $device->getCapability('inexistent');
        } catch (\InvalidArgumentException $expected) {
            return;
        }
        $this->fail('An expected exception has not been raised.');
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
        $device = new CustomDevice (array($modelDevice));

        $capabilityValue = $device->getCapability('is_wireless_device');
        self::assertEquals('true', $capabilityValue);
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
        self::assertEquals('false', $capabilityValue);
    }

    public function testShouldReturnAllCapabilities()
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
        $allCapabilities = $device->getAllCapabilities();
        self::assertEquals($allCapabilities, array('claims_web_support' => 'false', 'is_wireless_device' => 'true'));
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
            new ModelDevice('generic', '', '', '', false)
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
}

