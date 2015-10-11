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
 * @package    WURFL
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */

namespace Wurfl\Device;

use Wurfl\Storage\Storage;
use Wurfl\WurflConstants;
use Wurfl\Device\Xml\Info;

/**
 * WURFL Device Repository
 *
 * @package    WURFL
 */
class CustomDeviceRepository implements DeviceRepositoryInterface
{
    /**
     * @var string
     */
    const WURFL_USER_AGENTS_CLASSIFIED = 'WURFL_USER_AGENTS_CLASSIFIED';

    /**
     * The persistence provider for this device repository
     *
     * @var Storage
     */
    private $persistenceStorage;

    /**
     * @var array
     */
    private $deviceClassificationNames;

    /**
     * Map of groupID => array(capabilitiesNames)
     *
     * @var array
     */
    private $groupIDCapabilitiesMap = array();

    /**
     * @var array
     */
    private $capabilitiesName = array();

    /**
     * @var array
     */
    private $deviceCache = array();

    /**
     * Creates a new Device Repository from the given $persistenceStorage and $deviceClassificationNames
     *
     * @param \Wurfl\Storage\Storage $persistenceStorage
     * @param array           $deviceClassificationNames
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(Storage $persistenceStorage, array $deviceClassificationNames)
    {
        if (is_null($persistenceStorage)) {
            throw new \InvalidArgumentException('Persistence Provider cannot be null');
        }

        $this->persistenceStorage        = $persistenceStorage;
        $this->deviceClassificationNames = $deviceClassificationNames;
        $this->init();
    }

    /**
     * Initializes this device repository by loading the base generic device capabilities names and group ID map
     *
     * @throws \InvalidArgumentException
     */
    private function init()
    {
        /** @var $genericDevice \Wurfl\Device\ModelDeviceInterface */
        $genericDevice = $this->getDevice(WurflConstants::GENERIC);

        if (!is_null($genericDevice)) {
            $this->capabilitiesName       = array_keys($genericDevice->getCapabilities());
            $this->groupIDCapabilitiesMap = $genericDevice->getGroupIdCapabilitiesNameMap();
        }
    }

    /**
     * @return mixed|\Wurfl\Device\Xml\Info
     */
    public function getWURFLInfo()
    {
        $wurflInfo = $this->persistenceStorage->load(Info::PERSISTENCE_KEY);

        if ($wurflInfo !== null) {
            return $wurflInfo;
        }

        return Info::noInfo();
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->getWURFLInfo()->version;
    }

    /**
     * @return string
     */
    public function getLastUpdated()
    {
        return $this->getWURFLInfo()->lastUpdated;
    }

    /**
     * Returns a device for the given device ID
     *
     * @param string $deviceId
     *
     * @throws \InvalidArgumentException
     * @return \Wurfl\Device\ModelDeviceInterface
     */
    public function getDevice($deviceId)
    {
        if (!isset($this->deviceCache[$deviceId])) {
            $device = $this->persistenceStorage->load($deviceId);

            if (!$device) {
                throw new \InvalidArgumentException(
                    'There is no device with ID [' . $deviceId . '] in the loaded WURFL Data'
                );
            }

            $this->deviceCache[$deviceId] = $device;
        }

        return $this->deviceCache[$deviceId];
    }

    /**
     * Returns all devices in the repository
     *
     * @throws \Wurfl\Exception
     * @return \Wurfl\Device\ModelDeviceInterface[]
     */
    public function getAllDevices()
    {
        $devices   = array();
        $devicesId = $this->getAllDevicesID();

        foreach ($devicesId as $deviceId) {
            /** @var $device \Wurfl\Device\ModelDeviceInterface */
            $device    = $this->getDevice($deviceId);
            $devices[] = $device;
        }

        return $devices;
    }

    /**
     * Returns an array of all the device ids
     *
     * @return array
     */
    public function getAllDevicesID()
    {
        $devicesId = array();

        foreach ($this->deviceClassificationNames as $className) {
            $currentMap = $this->persistenceStorage->load($className);

            if (!is_array($currentMap)) {
                $currentMap = array();
            }

            $devicesId = array_merge($devicesId, array_values($currentMap));
        }

        return $devicesId;
    }

    /**
     * Returns the value for the given $deviceId and $capabilityName
     *
     * @param string $deviceId
     * @param string $capabilityName
     *
     * @throws \InvalidArgumentException device ID or capability was not found
     * @return string value
     */
    public function getCapabilityForDevice($deviceId, $capabilityName)
    {
        if (!$this->isCapabilityDefined($capabilityName)) {
            throw new \InvalidArgumentException('capability name: ' . $capabilityName . ' not found');
        }

        $capabilityValue = null;

        // TODO: Prevent infinite recursion
        while (strcmp($deviceId, 'root')) {
            $device = $this->persistenceStorage->load($deviceId);

            if (!$device) {
                throw new \InvalidArgumentException('the device with ' . $deviceId . ' is not found.');
            }

            if (isset($device->capabilities[$capabilityName])) {
                $capabilityValue = $device->capabilities[$capabilityName];
                break;
            }

            $deviceId = $device->fallBack;
        }

        return $capabilityValue;
    }

    /**
     * Checks if the capability name specified by $capability is defined in the repository
     *
     * @param string $capability
     *
     * @return bool
     */
    private function isCapabilityDefined($capability)
    {
        return in_array($capability, $this->capabilitiesName);
    }

    /**
     * Returns an associative array of capabilityName => capabilityValue for the given $deviceID
     *
     * @param string $deviceID
     *
     * @return array associative array of capabilityName, capabilityValue
     */
    public function getAllCapabilitiesForDevice($deviceID)
    {
        $devices      = array_reverse($this->getDeviceHierarchy($deviceID));
        $capabilities = array();

        foreach ($devices as $device) {
            /** @var $device \Wurfl\Device\ModelDeviceInterface */
            if (is_array($device->capabilities)) {
                $capabilities = array_merge($capabilities, $device->capabilities);
            }
        }

        return $capabilities;
    }

    /**
     * Returns an array containing all devices from the root
     * device to the device of the given $deviceId
     *
     * @param string $deviceId
     *
     * @throws \InvalidArgumentException
     * @return \Wurfl\Device\ModelDeviceInterface[] All ModelDevice objects in the fallback tree
     */
    public function getDeviceHierarchy($deviceId)
    {
        $devices = array();

        while (strcmp($deviceId, 'root')) {
            /** @var $device \Wurfl\Device\ModelDeviceInterface */
            $device = $this->getDevice($deviceId);

            if (!($device instanceof ModelDeviceInterface)) {
                throw new \InvalidArgumentException('one of the parent devices is missing for deviceId ' . $deviceId);
            }

            $devices[] = $device;
            $deviceId  = $device->fallBack;
        }

        return $devices;
    }

    /**
     * Returns an array Of group IDs defined in wurfl
     *
     * @return array
     */
    public function getListOfGroups()
    {
        return array_keys($this->groupIDCapabilitiesMap);
    }

    /**
     * Returns an array of all capability names defined in
     * the given group ID
     *
     * @param string $groupID
     *
     * @throws \InvalidArgumentException The given $groupID does not exist
     * @return array of capability names
     */
    public function getCapabilitiesNameForGroup($groupID)
    {
        if (!array_key_exists($groupID, $this->groupIDCapabilitiesMap)) {
            throw new \InvalidArgumentException('The Group ID ' . $groupID . ' supplied does not exist');
        }

        return $this->groupIDCapabilitiesMap[$groupID];
    }

    /**
     * Returns the group id that contains the given $capability
     *
     * @param string $capability
     *
     * @throws \InvalidArgumentException an invalid $capability was specified
     * @return string
     */
    public function getGroupIDForCapability($capability)
    {
        if (!isset($capability) || !array_key_exists($capability, $this->groupIDCapabilitiesMap)) {
            throw new \InvalidArgumentException('an invalid capability was specified.');
        }

        return $this->groupIDCapabilitiesMap[$capability];
    }
}
