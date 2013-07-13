<?php
namespace Wurfl;

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
 * @package    WURFL
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */

/**
 * WURFL Service
 * @package    WURFL
 */
class Service
{
    /**
     * @var \Wurfl\DeviceRepository
     */
    private $_deviceRepository;
    /**
     * @var \Wurfl\UserAgentHandlerChain
     */
    private $_userAgentHandlerChain;
    /**
     * @var \Wurfl\Storage
     */
    private $_cacheProvider;
    
    public function __construct(
        DeviceRepository $deviceRepository, 
        UserAgentHandlerChain $userAgentHandlerChain, 
        Storage\StorageInterface $cacheProvider)
    {
        $this->_deviceRepository      = $deviceRepository;
        $this->_userAgentHandlerChain = $userAgentHandlerChain;
        $this->_cacheProvider         = $cacheProvider;
    }
    
    /**
     * Returns the version info about the loaded WURFL
     * @return \Wurfl\Xml\Info WURFL Version info
     * @see \Wurfl\DeviceRepository::getWurflInfo()
     */
    public function getWurflInfo()
    {
        return $this->_deviceRepository->getWurflInfo();
    }
    
    /**
     * Returns the Device for the given \Wurfl\Request_GenericRequest
     *
     * @param \Wurfl\Request\GenericRequest $request
     * @return \Wurfl\CustomDevice
     */
    public function getDeviceForRequest(Request\GenericRequest $request)
    {
        $deviceId = $this->deviceIdForRequest($request);
        return $this->getWrappedDevice($deviceId, $request->matchInfo);
    
    }
    
    /**
     * Retun a \Wurfl\Xml\ModelDevice for the given device id
     *
     * @param string $deviceID
     * @return \Wurfl\Xml\ModelDevice
     */
    public function getDevice($deviceID)
    {
        return $this->getWrappedDevice($deviceID);
    }
    
    /**
     * Returns all devices ID present in WURFL
     *
     * @return array of strings
     */
    public function getAllDevicesID()
    {
        return $this->_deviceRepository->getAllDevicesID();
    }
    
    /**
     * Returns an array of all the fall back devices starting from
     * the given device
     *
     * @param string $deviceID
     * @return array
     */
    public function getDeviceHierarchy($deviceID)
    {
        return $this->_deviceRepository->getDeviceHierarchy($deviceID);
    }
    
    public function getListOfGroups()
    {
        return $this->_deviceRepository->getListOfGroups();
    }
    
    
    public function getCapabilitiesNameForGroup($groupId)
    {
        return $this->_deviceRepository->getCapabilitiesNameForGroup($groupId);
    }
    
    // ******************** private functions *****************************
    

    /**
     * Returns the device id for the device that matches the $request
     * @param \Wurfl\Request_GenericRequest $request WURFL Request object
     * @return string WURFL device id
     */
    private function deviceIdForRequest($request)
    {
        $deviceId = $this->_cacheProvider->load($request->id);
        if (empty($deviceId)) {
            $deviceId = $this->_userAgentHandlerChain->match($request);
            // save it in cache
            $this->_cacheProvider->save($request->id, $deviceId);
        } else {
            $request->matchInfo->from_cache = true;
            $request->matchInfo->lookup_time = 0.0;
        }
        return $deviceId;
    }
    
    /**
     * Wraps the model device with \Wurfl\Xml_ModelDevice.  This function takes the
     * Device ID and returns the \Wurfl\CustomDevice with all capabilities.
     *
     * @param string $deviceID
     * @param string $matchInfo
     * @return \Wurfl\CustomDevice
     */
    private function getWrappedDevice($deviceID, $matchInfo = null)
    {
        $device = $this->_cacheProvider->load('DEV_'.$deviceID);
        if (empty($device)) {
            $modelDevices = $this->_deviceRepository->getDeviceHierarchy($deviceID);
            $device = new CustomDevice($modelDevices, $matchInfo);
            $this->_cacheProvider->save('DEV_'.$deviceID, $device);
        }
        return $device;
    }
}

