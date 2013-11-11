<?php
namespace Wurfl\Xml;

/**
 * Copyright (c) 2012 ScientiaMobile, Inc.
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 * Refer to the COPYING.txt file distributed with this package.
 *
 * @category   WURFL
 * @package    \Wurfl\Xml
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
use Wurfl\Utils;

/**
 * Device Patcher patches an existing device with a new device
 *
 * @package    \Wurfl\Xml
 */
class DevicePatcher
{
    /**
     * Patch an existing $device with a $patchingDevice
     *
     * @param ModelDevice $device
     * @param ModelDevice $patchingDevice
     *
     * @return ModelDevice Patched device
     */
    public function patch($device, $patchingDevice)
    {
        if (!$this->haveSameId($device, $patchingDevice)) {
            return $patchingDevice;
        }
        $groupIdCapabilitiesMap = Utils::array_merge_recursive_unique(
            $device->getGroupIdCapabilitiesMap(), $patchingDevice->getGroupIdCapabilitiesMap()
        );

        return new ModelDevice($device->id, $device->userAgent, $device->fallBack, $device->actualDeviceRoot, $device->specific, $groupIdCapabilitiesMap);
    }

    /**
     * Returns true if $device and $patchingDevice have the same device id
     *
     * @param ModelDevice $device
     * @param ModelDevice $patchingDevice
     *
     * @return bool
     */
    private function haveSameId($device, $patchingDevice)
    {
        return (strcmp($patchingDevice->id, $device->id) === 0);
    }
}