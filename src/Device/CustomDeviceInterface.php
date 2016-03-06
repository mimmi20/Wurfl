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
 *
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */

namespace Wurfl\Device;

use Wurfl\Request\GenericRequest;
use Wurfl\VirtualCapability\VirtualCapabilityProvider;

/**
 * WURFL Custom Device - this is the core class that is used by developers to access the
 * properties and capabilities of a mobile device
 *
 * Examples:
 * <code>
 * // Create a WURFL Manager and detect device first
 * $wurflManagerFactory = new \Wurfl\ManagerFactory($wurflConfig);
 * $wurflManager = $wurflManagerFactory->create();
 * $device = $wurflManager->getDeviceForHttpRequest($_SERVER);
 *
 * // Example 1: Get display resolution from device
 * $width = $device->getCapability('resolution_width');
 * $height = $device->getCapability('resolution_height');
 * echo 'Resolution: $width x $height <br/>';
 *
 * // Example 2: Get the WURFL ID of the device
 * $wurflID = $device->id;
 * </code>
 *
 * @property-read string                               $id               WURFL Device ID
 * @property-read string                               $userAgent        User Agent
 * @property-read string                               $fallBack         Fallback Device ID
 * @property-read bool                                 $actualDeviceRoot true if device is an actual root device
 * @property-read GenericRequest        $request
 * @property-read \Wurfl\Device\ModelDeviceInterface[] $modelDevices
 * @property-read string                               $pointing_method
 * @property-read string                               $is_tablet
 * @property-read bool                                 $can_assign_phone_number
 */
interface CustomDeviceInterface
{
    /**
     * @param \Wurfl\Device\ModelDeviceInterface[] $modelDevices Array of \Wurfl\Xml\ModelDevice objects
     * @param \Wurfl\Request\GenericRequest        $request
     *
     * @throws \InvalidArgumentException if $modelDevices is not an array of at least one \Wurfl\Xml\ModelDevice
     */
    public function __construct(array $modelDevices, GenericRequest $request = null);

    /**
     * Magic Method
     *
     * @param string $name
     *
     * @return mixed Value of property
     */
    public function __get($name);

    /**
     * Device is a specific or actual WURFL device as defined by its capabilities
     *
     * @return bool
     */
    public function isSpecific();

    /**
     * Returns the value of a given capability name
     * for the current device
     *
     * @param string $capabilityName must be a valid capability name
     *
     * @throws \InvalidArgumentException The $capabilityName is is not defined in the loaded WURFL.
     *
     * @return string Capability value
     *
     * @see \Wurfl\Xml\ModelDeviceInterface::getCapability()
     */
    public function getCapability($capabilityName);

    /**
     * Returns the nearest actual device root in the fall back tree.  If this device is a device root itself,
     * it is returned.  Some devices have no device roots in their fall back tree, like generic_android, since
     * no devices above it (itself included) are real devices (actual device roots).
     *
     * @return \Wurfl\Device\ModelDeviceInterface
     */
    public function getActualDeviceRootAncestor();

    /**
     * Returns the match info for this device
     *
     * @return \Wurfl\Request\MatchInfo|null
     */
    public function getMatchInfo();

    /**
     * Returns an array with all the fall back devices, from the matched device to the root device ('generic')
     *
     * @return \Wurfl\Device\ModelDeviceInterface[]
     */
    public function getFallBackDevices();

    /**
     * Returns the top-most device.  This is the 'generic' device.
     *
     * @return \Wurfl\Device\ModelDeviceInterface
     */
    public function getRootDevice();

    /**
     * Returns capabilities and their values for the current device
     *
     * @return array Device capabilities array
     *
     * @see \Wurfl\Xml\ModelDeviceInterface::getCapabilities()
     */
    public function getAllCapabilities();

    /**
     * @return \Wurfl\VirtualCapability\VirtualCapabilityProvider
     */
    public function getVirtualCapabilityProvider();

    /**
     * @param \Wurfl\VirtualCapability\VirtualCapabilityProvider $virtualCapabilityProvider
     *
     * @return \Wurfl\CustomDevice
     */
    public function setVirtualCapabilityProvider(VirtualCapabilityProvider $virtualCapabilityProvider);

    /**
     * @param $name
     *
     * @return bool|float|int|string
     */
    public function getVirtualCapability($name);

    /**
     * @return array
     */
    public function getAllVirtualCapabilities();
}
