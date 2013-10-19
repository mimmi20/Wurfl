<?php
namespace Wurfl\Xml;

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
 * @category   WURFL
 * @package    \Wurfl\Xml
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 *
 */
/**
 * Device Patcher patches an existing device with a new device
 * @package    \Wurfl\Xml
 */
class DevicePatcher
{
    /**
     * Patch an existing $device with a $patchingDevice
     * @param \Wurfl\Xml_ModelDevice $device
     * @param \Wurfl\Xml_ModelDevice $patchingDevice
     * @return \Wurfl\Xml_ModelDevice Patched device
     */
    public function patch($device, $patchingDevice)
    {
        if (!$this->haveSameId($device, $patchingDevice)) {
            return $patchingDevice;
        }
        $groupIdCapabilitiesMap = \Wurfl\Utils::array_merge_recursive_unique($device->getGroupIdCapabilitiesMap(), $patchingDevice->getGroupIdCapabilitiesMap());    
        return new ModelDevice($device->id, $device->userAgent, $device->fallBack, $device->actualDeviceRoot, $device->specific, $groupIdCapabilitiesMap);
    
    }
    
    /**
     * Returns true if $device and $patchingDevice have the same device id
     * @param \Wurfl\Xml_ModelDevice $device
     * @param \Wurfl\Xml_ModelDevice $patchingDevice
     * @return bool
     */
    private function haveSameId($device, $patchingDevice)
    {
        return (strcmp($patchingDevice->id, $device->id) === 0);
    }
    
    /**
     * Returns true if a $patchingDevice can be used to patch $device
     * @param \Wurfl\Xml_ModelDevice $device
     * @param \Wurfl\Xml_ModelDevice $patchingDevice
     * @throws \Wurfl\WURFLException
     * @return bool
     * @deprecated
     */
    private function checkIfCanPatch($device, $patchingDevice)
    {
        if (strcmp ( $patchingDevice->userAgent, $device->userAgent ) !== 0) {
            $message = "Patch Device : " . $patchingDevice->id . " can't override user agent " . $device->userAgent . " with " . $patchingDevice->userAgent;
            throw new Exception ( $message );
        }
    }
}