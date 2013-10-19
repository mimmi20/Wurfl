<?php
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
 * @package	WURFL
 * @copyright  ScientiaMobile, Inc.
 * @license	GNU Affero General Public License
 * @version	$id$
 * 
 */
/**
 * WURFL Device Repository
 * @package	WURFL
 */
class WURFL_CustomDeviceRepository implements WURFL_DeviceRepository {
	
	const WURFL_USER_AGENTS_CLASSIFIED = "WURFL_USER_AGENTS_CLASSIFIED";
	
	/**
	 * The persistence provider for this device repository
	 * @var WURFL_Storage_Base
	 */
	private $persistenceStorage;
	/**
	 * @var array
	 */
	private $deviceClassificationNames;
	
	/**
	 * Map of groupID => array(capabilitiesNames)
	 * @var array
	 */
	private $_groupIDCapabilitiesMap = array();
	/**
	 * @var array
	 */
	private $_capabilitiesName = array();
	/**
	 * @var array
	 */
	private $_deviceCache = array();
	
	/**
	 * Creates a new Device Repository from the given $persistenceStorage and $deviceClassificationNames
	 * @param WURFL_Storage_Base $persistenceStorage
	 * @param array $deviceClassificationNames
	 * @throws InvalidArgumentException
	 */
	public function __construct($persistenceStorage, $deviceClassificationNames) {
		if (is_null($persistenceStorage)) {
			throw new InvalidArgumentException("Persistence Provider cannot be null");
		}
		$this->persistenceStorage = $persistenceStorage;
		$this->deviceClassificationNames = $deviceClassificationNames;
		$this->init();
	}
	
	/**
	 * Initializes this device repository by loading the base generic device capabilities names and group ID map
	 */
	private function init() {
		$genericDevice = $this->getDevice(WURFL_Constants::GENERIC);
		if (!is_null($genericDevice)) {
			$this->_capabilitiesName = array_keys($genericDevice->getCapabilities());
			$this->_groupIDCapabilitiesMap = $genericDevice->getGroupIdCapabilitiesNameMap();
		}
	}

	public function getWURFLInfo() {
		$wurflInfo = $this->persistenceStorage->load(WURFL_Xml_Info::PERSISTENCE_KEY);
		if ($wurflInfo != null) {
			return $wurflInfo;
		}
		return WURFL_Xml_Info::noInfo();
	}
	
	public function getVersion() {
		return $this->getWURFLInfo()->version;
	}
	
	public function getLastUpdated() {
		return $this->getWURFLInfo()->lastUpdated;
	}
	
	/**
	 * Returns a device for the given device ID
	 *
	 * @param string $deviceId
	 * @return WURFL_CustomDevice
	 * @throws WURFL_Exception if $deviceID is not defined in wurfl devices repository
	 */
	public function getDevice($deviceId) {
		if (!isset($this->_deviceCache[$deviceId])) {
			$device = $this->persistenceStorage->load($deviceId);
			if (is_null($device)) {
				throw new Exception("There is no device with ID [$deviceId] in the loaded WURFL Data");
			}
			$this->_deviceCache[$deviceId] = $device;
		}
		return $this->_deviceCache[$deviceId];
	}
	
	/**
	 * Returns all devices in the repository
	 * @return array
	 */
	public function getAllDevices() {
		$devices = array();
		$devicesId = $this->getAllDevicesID();
		foreach($devicesId as $deviceId) {
			$devices[] = $this->getDevice($deviceId);
		}
		
		return $devices;
	}
	
	/**
	 * Returns an array of all the device ids
	 * @return array
	 */
	public function getAllDevicesID() {
		$devicesId = array();
		foreach($this->deviceClassificationNames as $className) {
			$currentMap = $this->persistenceStorage->load($className);
			if (!is_array($currentMap)) {
				$currentMap = array ();
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
	 * @throws WURFL_WURFLException device ID or capability was not found
	 * @return string value
	 */
	public function getCapabilityForDevice($deviceId, $capabilityName) {
		if (! $this->isCapabilityDefined($capabilityName)) {
			throw new WURFL_WURFLException("capability name: " . $capabilityName . " not found");
		}
		$capabilityValue = null;
		// TODO: Prevent infinite recursion
		while (strcmp($deviceId, "root")) {
			$device = $this->persistenceStorage->load($deviceId);
			if (!$device) {
				throw new WURFL_WURFLException("the device with $deviceId is not found.");
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
	 * @return bool
	 */
	private function isCapabilityDefined($capability) {
		return in_array($capability, $this->_capabilitiesName);
	}
	
	/**
	 * Returns an associative array of capabilityName => capabilityValue for the given $deviceID
	 *
	 * @param string $deviceID
	 * @return array associative array of capabilityName, capabilityValue
	 */
	public function getAllCapabilitiesForDevice($deviceID) {
		$devices = array_reverse($this->getDeviceHierarchy($deviceID));
		$capabilities = array();
		
		foreach ($devices as $device) {
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
	 * @return array All WURFL_Device objects in the fallback tree
	 */
	public function getDeviceHierarchy($deviceId) {
		$devices = array();
		while (strcmp($deviceId, "root")) {
			$device = $this->getDevice($deviceId);
			$devices[] = $device;
			$deviceId = $device->fallBack;
		}
		return $devices;
	}
	
	/**
	 * Returns an array Of group IDs defined in wurfl
	 *
	 * @return array
	 */
	public function getListOfGroups() {
		return array_keys($this->_groupIDCapabilitiesMap);
	}
	
	/**
	 * Returns an array of all capability names defined in
	 * the given group ID
	 *
	 * @param string $groupID
	 * @throws WURFL_WURFLException The given $groupID does not exist
	 * @return array of capability names
	 */
	public function getCapabilitiesNameForGroup($groupID) {
		if (!array_key_exists($groupID, $this->_groupIDCapabilitiesMap)) {
			throw new WURFL_WURFLException("The Group ID " . $groupID . " supplied does not exist");
		}
		return $this->_groupIDCapabilitiesMap [$groupID];
	}
	
	/**
	 * Returns the group id that contains the given $capability
	 *
	 * @param string $capability
	 * @throws InvalidArgumentException an invalid $capability was specified 
	 * @return string
	 */
	public function getGroupIDForCapability($capability) {
		if (!isset($capability) || !array_key_exists($capability, $this->_groupIDCapabilitiesMap)) {
			throw new InvalidArgumentException("an invalid capability was specified.");
		}
		return $this->_groupIDCapabilitiesMap[$capability];
	}
}