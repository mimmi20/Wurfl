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

namespace Wurfl\Device\Xml;

use Wurfl\Device\ModelDevice;
use Wurfl\Device\ModelDeviceInterface;
use Wurfl\Utils;

/**
 * Device Patcher patches an existing device with a new device
 */
class DevicePatcher
{
    /**
     * Patch an existing $device with a $patchingDevice
     *
     * @param \Wurfl\Device\ModelDeviceInterface $device
     * @param \Wurfl\Device\ModelDeviceInterface $patchingDevice
     *
     * @return \Wurfl\Device\ModelDeviceInterface Patched device
     */
    public function patch(ModelDeviceInterface $device, ModelDeviceInterface $patchingDevice)
    {
        if (!$this->haveSameId($device, $patchingDevice)) {
            return $patchingDevice;
        }

        $groupIdCapabilitiesMap = Utils::arrayMergeRecursiveUnique(
            $device->getGroupIdCapabilitiesMap(),
            $patchingDevice->getGroupIdCapabilitiesMap()
        );

        return new ModelDevice($device->id, $device->userAgent, $device->fallBack, $device->actualDeviceRoot, $device->specific, $groupIdCapabilitiesMap);
    }

    /**
     * Returns true if $device and $patchingDevice have the same device id
     *
     * @param \Wurfl\Device\ModelDeviceInterface $device
     * @param \Wurfl\Device\ModelDeviceInterface $patchingDevice
     *
     * @return bool
     */
    private function haveSameId(ModelDeviceInterface $device, ModelDeviceInterface $patchingDevice)
    {
        return (strcmp($patchingDevice->id, $device->id) === 0);
    }
}
