<?php

namespace WurflTest\VirtualCapability\Tool;

use Wurfl\CustomDevice;
use Wurfl\Device\ModelDevice;
use Wurfl\Request\GenericRequest;
use Wurfl\VirtualCapability\Tool\DeviceFactory;

/**
 * Class DeviceFactoryTest
 */
class DeviceFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     */
    public function testBuild()
    {
        $userAgent = '';
        $header    = array('HTTP_USER_AGENT' => $userAgent);
        $request   = new GenericRequest($header, $userAgent, null, false);
        $device    = new CustomDevice(array($this->mockModelDevice()));

        $device = DeviceFactory::build($request, $device);

        self::assertInstanceOf('\Wurfl\VirtualCapability\Tool\Device', $device);
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
}
