<?php
/**
 * Copyright (c) 2015 ScientiaMobile, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Refer to the LICENSE file distributed with this package.
 *
 *
 * @category   WURFL
 *
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */

namespace Wurfl\VirtualCapability\Group;

use Wurfl\VirtualCapability\Tool\DeviceFactory;

/**
 */
class DeviceBrowserGroup extends Group
{
    /**
     * @var array
     */
    protected $requiredCapabilities = array(
        'device_os',
        'device_os_version',
        'mobile_browser_version',
    );

    /**
     * @var array
     */
    protected $storage = array(
        'DeviceOs'        => null,
        'DeviceOsVersion' => null,
        'Browser'         => null,
        'BrowserVersion'  => null,
    );

    /**
     *
     */
    public function compute()
    {
        // Run the DeviceFactory to get the relevant details
        $device = DeviceFactory::build($this->request, $this->device);

        $this->storage = array(
            'DeviceOs' => new ManualGroupChild(
                $this->device,
                $this->request,
                $this,
                $device->getOs()->name
            ),
            'DeviceOsVersion' => new ManualGroupChild(
                $this->device,
                $this->request,
                $this,
                $device->getOs()->version
            ),
            'Browser' => new ManualGroupChild(
                $this->device,
                $this->request,
                $this,
                $device->getBrowser()->name
            ),
            'BrowserVersion' => new ManualGroupChild(
                $this->device,
                $this->request,
                $this,
                $device->getBrowser()->version
            ),
        );
    }
}
