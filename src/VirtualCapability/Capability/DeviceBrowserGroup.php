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

namespace Wurfl\VirtualCapability\Capability;

use Wurfl\VirtualCapability\Tool\DeviceFactory;
use Wurfl\VirtualCapability\VirtualCapability;

/**
 */
class DeviceBrowserGroup extends VirtualCapability
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
     *
     */
    public function compute()
    {
        // Run the DeviceFactory to get the relevant details
        $device = DeviceFactory::build($this->request, $this->device);

        return array(
            'advertised_device_os' => $device->getOs()->name,
            'advertised_device_os_version' => $device->getOs()->version,
            'advertised_browser' => $device->getBrowser()->name,
            'advertised_browser_version' => $device->getBrowser()->version,
        );
    }
}
