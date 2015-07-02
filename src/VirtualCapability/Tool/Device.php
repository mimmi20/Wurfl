<?php
/**
 * Copyright (c) 2015 ScientiaMobile, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Refer to the COPYING.txt file distributed with this package.
 *
 *
 * @category   WURFL
 * @package    WURFL
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */

namespace Wurfl\VirtualCapability\Tool;

use Wurfl\Request\GenericRequest;

/**
 * @package \Wurfl\VirtualCapability\UserAgentTool
 */
class Device
{
    /**
     * @var NameVersionPair
     */
    public $browser;

    /**
     * @var NameVersionPair
     */
    public $os;

    /**
     * @var \Wurfl\Request\GenericRequest
     */
    public $http_request;

    /**
     * Device user agent string
     *
     * @var string
     */
    public $device_ua;

    /**
     * Browser user agent string
     *
     * @var string
     */
    public $browser_ua;

    /**
     * Device user agent string normalized
     * @var string
     */
    public $device_ua_normalized;

    /**
     * Browser user agent string normalized
     * @var string
     */
    public $browser_ua_normalized;

    /**
     * @param \Wurfl\Request\GenericRequest $request
     */
    public function __construct(GenericRequest $request)
    {
        $this->http_request = $request;

        $this->device_ua             = $this->http_request->getDeviceUserAgent();
        $this->browser_ua            = $this->http_request->getBrowserUserAgent();
        $this->device_ua_normalized  = $this->http_request->getUserAgentNormalized();
        $this->browser_ua_normalized = $this->device_ua_normalized;

        $this->browser = new NameVersionPair($this);
        $this->os      = new NameVersionPair($this);
    }

    /**
     * @var array
     */
    protected static $windowsMap = array(
        '3.1'  => 'NT 3.1',
        '3.5'  => 'NT 3.5',
        '4.0'  => 'NT 4.0',
        '5.0'  => '2000',
        '5.1'  => 'XP',
        '5.2'  => 'XP',
        '6.0'  => 'Vista',
        '6.1'  => '7',
        '6.2'  => '8',
        '6.3'  => '8.1',
        '6.4'  => '10',
        '10.0' => '10',
    );

    protected static $trident_map = array(
        '7' => '11',
        '6' => '10',
        '5' => '9',
        '4' => '8',
    );

    protected static $wds_map = array(
        '7.10' => '7.5',
        '8.10' => '8.1',
        '8.15' => '10',
    );

    /**
     * @return Device
     */
    public function normalize()
    {
        $this->normalizeOS();
        $this->normalizeBrowser();

        return $this;
    }

    /**
     *
     */
    protected function normalizeOS()
    {
        if (strpos($this->device_ua, 'Windows') !== false) {
            if (preg_match('/Windows NT ([0-9]+?\.[0-9])/', $this->os->name, $matches)) {
                $this->os->name    = 'Windows';
                $this->os->version = array_key_exists($matches[1], self::$windowsMap) ? self::$windowsMap[$matches[1]]
                    : $matches[1];

                return;
            }

            if (preg_match('/Windows [0-9\.]+/', $this->os->name)) {
                return;
            }
        }

        if (strpos($this->os->name, 'Windows Phone') !== false) {
            if (array_key_exists($this->os->version, self::$wds_map)) {
                $this->os->version = self::$wds_map[$this->os->version];

                return;
            }
        }

        if ($this->os->setRegex($this->device_ua, '/PPC.+OS X ([0-9\._]+)/', 'Mac OS X')) {
            $this->os->version = str_replace('_', '.', $this->os->version);

            return;
        }

        if ($this->os->setRegex($this->device_ua, '/PPC.+OS X/', 'Mac OS X')) {
            return;
        }

        if (strpos($this->device_ua, 'CFNetwork') !== false) {
            if ($this->os->setRegex($this->device_ua_normalized, '/Intel Mac OS X ([0-9\._]+)/', 'Mac OS X', 1)) {
                $this->os->version = str_replace('_', '.', $this->os->version);

                return;
            }
        }

        if ($this->os->setRegex($this->device_ua, '/Intel Mac OS X ([0-9\._]+)/', 'Mac OS X', 1)) {
            $this->os->version = str_replace('_', '.', $this->os->version);

            return;
        }

        if ($this->os->setContains($this->device_ua, 'Mac_PowerPC', 'Mac OS X')) {
            return;
        }

        if ($this->os->setContains($this->device_ua, 'CrOS', 'Chrome OS')) {
            return;
        }

        if ($this->os->name != '') {
            return;
        }

        if (strpos($this->device_ua, 'FreeBSD') !== false) {
            $this->os->name = 'FreeBSD';

            return;
        }

        if (strpos($this->device_ua, 'NetBSD') !== false) {
            $this->os->name = 'NetBSD';

            return;
        }

        // Last ditch efforts
        if (strpos($this->device_ua, 'Linux') !== false || strpos($this->device_ua, 'X11') !== false) {
            $this->os->name = 'Linux';

            return;
        }
    }

    protected function normalizeBrowser()
    {
        if ($this->browser->name === 'IE' && preg_match('#Trident/(\d+)#', $this->device_ua, $matches)) {
            if (array_key_exists($matches[1], self::$trident_map)) {
                $compatibilityViewCheck = self::$trident_map[$matches[1]];

                if ($this->browser->version !== $compatibilityViewCheck) {
                    $this->browser->version = $compatibilityViewCheck . '(Compatibility View)';
                }

                return;
            }
        }
    }
}
