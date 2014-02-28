<?php
namespace Wurfl\VirtualCapability\Group;

/**
 * Copyright (c) 2012 ScientiaMobile, Inc.
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
 * @package    \Wurfl\VirtualCapability\VirtualCapability
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */

use Wurfl\VirtualCapability\UserAgentTool;

/**
 * @package \Wurfl\VirtualCapability\VirtualCapability
 */
class DeviceBrowserGroup extends Group
{
    /**
     * @var array
     */
    protected $requiredCapabilities = array();

    /**
     * @var array
     */
    protected $storage
        = array(
            'DeviceOs'        => null,
            'DeviceOsVersion' => null,
            'Browser'         => null,
            'BrowserVersion'  => null,
        );

    /**
     * @var UserAgentTool
     */
    protected static $userAgentTool;

    /**
     *
     */
    public function compute()
    {
        if (self::$userAgentTool === null) {
            self::$userAgentTool = new UserAgentTool();
        }

        // Run the UserAgentTool to get the relevant details
        $device = self::$userAgentTool->getDevice($this->request);

        $this->storage['DeviceOs'] = new ManualGroupChild($this->device, $this->request, $this, $device->platform->name);
        $this->storage['DeviceOsVersion']
                                   = new ManualGroupChild($this->device, $this->request, $this, $device->platform->version);
        $this->storage['Browser']
                                   = new ManualGroupChild($this->device, $this->request, $this, $device->browser->name);
        $this->storage['BrowserVersion']
                                   = new ManualGroupChild($this->device, $this->request, $this, $device->browser->version);
    }
}
