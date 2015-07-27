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

namespace Wurfl\Device;

/**
 * Represents a device in the wurfl xml file
 *
 * @property-read bool   $actualDeviceRoot true if device is an actual root device
 * @property-read bool   $specific
 * @property-read string $id
 * @property-read array  $capabilities
 * @property-read string $fallBack
 * @property-read string $userAgent
 * @package    WURFL_Xml
 */
interface ModelDeviceInterface
{
    /**
     * Creates a WURFL Device based on the provided parameters
     *
     * @param string $id WURFL device ID
     * @param string $userAgent
     * @param string $fallBack
     * @param bool   $actualDeviceRoot
     * @param bool   $specific
     * @param array  $groupIdCapabilitiesMap
     */
    public function __construct(
        $id,
        $userAgent,
        $fallBack,
        $actualDeviceRoot = false,
        $specific = false,
        $groupIdCapabilitiesMap = null
    );

    /**
     * Magic getter method
     *
     * @param string $name Name of property to get
     *
     * @return mixed Value of property
     */
    public function __get($name);

    /**
     * Returns an array of the device capabilities
     *
     * @return array Capabilities
     */
    function getCapabilities();

    /**
     * Returns the group ID to capability name map
     *
     * @return array Group ID to capability name map
     */
    function getGroupIdCapabilitiesNameMap();

    /**
     * Returns the value of the given $capabilityName
     *
     * @param string $capabilityName
     *
     * @return mixed Value
     */
    public function getCapability($capabilityName);

    /**
     * @return array
     */
    public function getCapabilityNames();

    /**
     * Returns true if the capability exists
     *
     * @param string $capabilityName
     *
     * @return bool Defined
     */
    public function isCapabilityDefined($capabilityName);

    /**
     * Returns the capabilities by group name
     *
     * @return array capabilities
     */
    public function getGroupIdCapabilitiesMap();

    /**
     * @return array
     */
    public function getGroupNames();

    /**
     * Returns true if $groupId is defined
     *
     * @param string $groupId
     *
     * @return boolean
     */
    public function isGroupDefined($groupId);
}
