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
 * @category   WURFL
 * @package    WURFL
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
/**
 * WURFL Manager Class - serves as the core class that the developer uses to query
 * the API for device capabilities and WURFL information
 * 
 * Examples:
 * <code>
 * // Example 1. Instantiate Manager from Factory:
 * $wurflManager = $wurflManagerFactory->create();
 * 
 * // Example 2: Get Visiting Device from HTTP Request
 * $device = $wurflManager->getDeviceForHttpRequest($_SERVER);
 * 
 * // Example 3: Get Visiting Device from User Agent
 * $userAgent = 'Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.10';
 * $device = $wurflManager->getDeviceForUserAgent($userAgent);
 * </code>
 * 
 * @package WURFL
 * @see getWurflInfo(), getDeviceForHttpRequest(), getDeviceForUserAgent(), \Wurfl\WURFLManagerFactory::create()
 */
class Manager
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
    
    /**
     * Creates a new Wurfl Manager object
     *
     * @param \Wurfl\DeviceRepository $deviceRepository
     * @param \Wurfl\UserAgentHandlerChain $userAgentHandlerChain
     * @param \Wurfl\Storage\StorageInterface $cacheProvider
     */
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
     * Return the version info of the loaded wurfl xml file
     * 
     * Example:
     * <code>
     * $info = $wurflManager->getWurflInfo();
     * printf('Version: %s, Updated: %s, OfficialURL: %s',
     *     $info->version,
     *     $info->lastUpdated,
     *     $info->officialURL
     * );
     * </code>
     *
     * @return \Wurfl\Xml\Info WURFL Version info
     * @see \Wurfl\DeviceRepository::getWurflInfo()
     */
    public function getWurflInfo()
    {
        return $this->_deviceRepository->getWurflInfo();
    }
    
    /**
     * Return a device for the given http request(user-agent..)
     *
     * @param array $httpRequest HTTP Request array (normally $_SERVER)
     * @return \Wurfl\CustomDevice device
     * @throws Exception if $httpRequest is not set
     */
    public function getDeviceForHttpRequest($httpRequest)
    {
        if (!isset($httpRequest)) {
            throw new Exception('The $httpRequest parameter must be set.');
        }
        
        $requestFactory = new Request\GenericRequestFactory();
        
        $request = $requestFactory->createRequest($httpRequest);
        
        return $this->getDeviceForRequest($request);
    }
    
    /**
     * Returns the Device for the given \Wurfl\Request_GenericRequest
     *
     * @param \Wurfl\Request\GenericRequest $request
     * @return \Wurfl\CustomDevice
     */
    private function getDeviceForRequest(Request\GenericRequest $request)
    {
        $deviceId = $this->deviceIdForRequest($request);
        return $this->getWrappedDevice($deviceId, $request->matchInfo);
    
    }
    
    /**
     * Returns a device for the given user-agent
     *
     * @param string $userAgent
     * @return \Wurfl\CustomDevice device
     * @throws Exception if $userAgent is not set
     */
    public function getDeviceForUserAgent($userAgent)
    {
        if (!isset($userAgent)) {
            $userAgent = '';
        }
        
        $requestFactory = new Request\GenericRequestFactory();
        
        $request = $requestFactory->createRequestForUserAgent($userAgent);
        return $this->getDeviceForRequest($request);
    }
    
    /**
     * Return a device for the given device id
     *
     * @param string $deviceId
     * @return \Wurfl\Xml\ModelDevice
     */
    public function getDevice($deviceId)
    {
        return $this->getWrappedDevice($deviceId);
    }
    
    /**
     * Returns an array of all wurfl group ids
     *
     * @return array
     */
    public function getListOfGroups()
    {
        return $this->_wurflService->getListOfGroups();
    }
    
    /**
     * Returns all capability names for the given $groupId
     *
     * @param string $groupId
     *
     * @return array
     */
    public function getCapabilitiesNameForGroup($groupId)
    {
        return $this->_deviceRepository->getCapabilitiesNameForGroup($groupId);
    }
    
    /**
     * Returns an array of all the fall back devices starting from
     * the given device
     *
     * @param string $deviceId
     *
     * @return array
     */
    public function getFallBackDevices($deviceId)
    {
        return $this->_deviceRepository->getDeviceHierarchy($deviceId);
    }
    
    /**
     * Returns all the device ids in wurfl
     *
     * @return array
     */
    public function getAllDevicesID()
    {
        return $this->_deviceRepository->getAllDevicesID();
    }
    
    // ******************** private functions *****************************
    

    /**
     * Returns the device id for the device that matches the $request
     *
     * @param \Wurfl\Request_GenericRequest $request WURFL Request object
     *
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
     * @param string $deviceId
     * @param string $matchInfo
     *
     * @return \Wurfl\CustomDevice
     */
    private function getWrappedDevice($deviceId, $matchInfo = null)
    {
        $device = $this->_cacheProvider->load('DEV_'.$deviceId);
        if (empty($device)) {
            $modelDevices = $this->_deviceRepository->getDeviceHierarchy($deviceId);
            $device = new CustomDevice($modelDevices, $matchInfo);
            $this->_cacheProvider->save('DEV_'.$deviceId, $device);
        }
        return $device;
    }
}