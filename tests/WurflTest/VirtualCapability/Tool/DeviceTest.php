<?php

namespace WurflTest\VirtualCapability\Tool;

use Wurfl\Request\GenericRequest;
use Wurfl\VirtualCapability\Tool\Device;

/**
 * Class DeviceTest
 *
 * @group VirtualCapability
 */
class DeviceTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     */
    public function testConstruct()
    {
        $userAgent = 'Mozilla/5.0 (Randomized by FreeSafeIP.com/upgrade-to-remove; compatible; MSIE 8.0; Windows NT 5.0) Chrome/21.0.1229.79';
        $header    = array(
            'HTTP_USER_AGENT' => $userAgent,
        );
        $request = new GenericRequest($header, $userAgent, null, false);

        $device = new Device($request);

        self::assertSame($request, $device->getHttpRequest());
        self::assertSame($userAgent, $device->getDeviceUa());
        self::assertSame($userAgent, $device->getBrowserUa());

        self::assertSame($device->getBrowserUaNormalized(), $device->getDeviceUaNormalized());

        self::assertInstanceOf('\Wurfl\VirtualCapability\Tool\NameVersionPair', $device->getBrowser());
        self::assertInstanceOf('\Wurfl\VirtualCapability\Tool\NameVersionPair', $device->getOs());
    }
}
