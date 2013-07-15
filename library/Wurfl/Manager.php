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
     * @var \Wurfl\WURFLService
     */
    private $_wurflService;
    
    /**
     * Creates a new WURFL Manager object
     * @param \Wurfl\Service $wurflService
     * @param \Wurfl\Request\GenericRequestFactory $requestFactory
     */
    public function __construct(Service $wurflService)
    {
        $this->_wurflService   = $wurflService;
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
     * @return \Wurfl\Xml\Info WURFL Version info
     * @see \Wurfl\WURFLService::getWurflInfo(), \Wurfl\DeviceRepository::getWurflInfo()
     */
    public function getWurflInfo()
    {
        return $this->_wurflService->getWurflInfo();
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
        
        return $this->_wurflService->getDeviceForRequest($request);
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
        return $this->_wurflService->getDeviceForRequest($request);
    }
    
    /**
     * Return a device for the given device id
     *
     * @param string $deviceID
     * @return \Wurfl\CustomDevice
     */
    public function getDevice($deviceID)
    {
        return $this->_wurflService->getDevice($deviceID);
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
     * Returns all capability names for the given $groupID
     *
     * @param string $groupID
     * @return array
     */
    public function getCapabilitiesNameForGroup($groupID)
    {
        return $this->_wurflService->getCapabilitiesNameForGroup($groupID);
    }
    
    /**
     * Returns an array of all the fall back devices starting from the given device
     *
     * @param string $deviceID
     * @return array
     */
    public function getFallBackDevices($deviceID)
    {
        return $this->_wurflService->getDeviceHierarchy($deviceID);
    }
    
    /**
     * Returns all the device ids in wurfl
     *
     * @return array
     */
    public function getAllDevicesID()
    {
        return $this->_wurflService->getAllDevicesID();
    }
}