<?php
declare(ENCODING = 'utf-8');
namespace Wurfl\Xml;

/**
 * Copyright(c) 2011 ScientiaMobile, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or(at your option) any later version.
 *
 * Refer to the COPYING file distributed with this package.
 *
 *
 * @category   WURFL
 * @package    WURFL_Xml
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
/**
 * Represents a device in the wurfl xml file
 * @package    WURFL_Xml
 */
class ModelDevice
{

    /**
     * @var string WURFL device ID
     */
    private $_id;
    
    /**
     * @var string Fallback WURFL device ID
     */
    private $_fallBack;
    
    /**
     * @var string User agent
     */
    private $_userAgent;
    
    /**
     * @var bool This device is an actual root device
     */
    private $_actualDeviceRoot;
    
    /**
     * @var bool This device is a specific device
     */
    private $_specific;
    
    /**
     * @var array Array of capabilities
     */
    private $_capabilities = array();
    
    /**
     * @var array Mapping of group IDs to capability names
     */
    private $_groupIdCapabilitiesNameMap = array();
    
    /**
     * Creates a WURFL Device based on the provided parameters
     * @param string $id WURFL device ID
     * @param string $userAgent
     * @param string $fallBack
     * @param bool $actualDeviceRoot
     * @param bool $specific
     * @param array $groupIdCapabilitiesMap
     */
    public function __construct($id, $userAgent, $fallBack, $actualDeviceRoot=false, $specific=false, $groupIdCapabilitiesMap = null)
    {
        $this->_id               = $id;
        $this->_userAgent        = $userAgent;
        $this->_fallBack         = $fallBack; 
        $this->_actualDeviceRoot = $actualDeviceRoot ? true : false;
        $this->_specific         = $specific ? true : false;
        
        if (is_array($groupIdCapabilitiesMap)) {
            foreach ($groupIdCapabilitiesMap as $groupId => $capabilitiesNameValue) {
                $this->_groupIdCapabilitiesNameMap[$groupId] = array_keys($capabilitiesNameValue); 
                $this->_capabilities = array_merge($this->_capabilities, $capabilitiesNameValue);
            }
            
        }
    }
 
    /**
     * Magic getter method
     * @param string $name Name of property to get
     */
    public function __get($name)
    {
        $name = '_' . $name;
        return (isset($this->$name) ? $this->$name : null);
    }
    
    /**
     * Returns an array of the device capabilities
     * @return array Capabilities
     */
    public function getCapabilities()
    {
        return $this->_capabilities;
    }
    
    /**
     * Returns the group ID to capability name map
     * @return array Group ID to capability name map
     */
    public function getGroupIdCapabilitiesNameMap()
    {
        return $this->_groupIdCapabilitiesNameMap;
    }
    
    /**
     * Returns the value of the given $capabilityName
     * @param string $capabilityName
     * @return mixed Value
     */
    public function getCapability($capabilityName)
    {
        if ($this->isCapabilityDefined($capabilityName)) {
            return $this->_capabilities[$capabilityName];
        }
        return null;
    }
    
    /**
     * Returns true if the capability exists
     * @param string $capabilityName
     * @return bool Defined
     */
    public function isCapabilityDefined($capabilityName)
    {
        return array_key_exists($capabilityName, $this->_capabilities);
    }
    
    /**
     * Returns the capabilities by group name
     * @return array capabilities
     */
    public function getGroupIdCapabilitiesMap()
    {
        $groupIdCapabilitiesMap = array();
        
        foreach ($this->_groupIdCapabilitiesNameMap as $groupId => $capabilitiesName) {
            foreach ($capabilitiesName as $capabilityName) {
                $groupIdCapabilitiesMap[$groupId][$capabilityName] = $this->_formatCapabilityValue($this->_capabilities[$capabilityName]);
            }
        }
        
        return $groupIdCapabilitiesMap;
    }
    
    /**
     * Returns true if $groupId is defined 
     * @param string $groupId
     * @returns bool
     */
    public function isGroupDefined($groupId)
    {
        return array_key_exists($groupId, $this->_groupIdCapabilitiesNameMap);
    }
    
    private function _formatCapabilityValue($value)
    {
        if (is_array($value)) {
            return array_map(array($this, __FUNCTION__), $value);
        }
        
        if ('false' === $value) {
            return false;
        }
        
        if ('true' === $value) {
            return true;
        }
        
        if ('0' === $value) {
            return 0;
        }
        
        return $value;
    }
}