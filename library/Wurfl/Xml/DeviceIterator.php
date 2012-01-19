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
 * @category   WURFL
 * @package    WURFL_Xml
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version   SVN: $Id$
 *
 */
/**
 * Extracts device capabilities from XML file
 * @package    WURFL_Xml
 */
class DeviceIterator extends AbstractIterator
{
    private $_capabilitiesToSelect = array();
    private $_filterCapabilities;
    
    /**
     * @param string $inputFile XML file to be processed
     * @param array $capabilities Capabiities to process
     */
    public function __construct($inputFile, $capabilities = array())
    {
        parent::__construct($inputFile);
        
        foreach ($capabilities as $groupId => $capabilityNames) {
            $trimmedCapNames     = $this->_removeSpaces($capabilityNames);
            $capabilitiesAsArray = array();
            
            if (strlen($trimmedCapNames) != 0) {
                $capabilitiesAsArray = explode(',', $trimmedCapNames);
            }
            $this->_capabilitiesToSelect[$groupId] = $capabilitiesAsArray;
        }
        
        $this->_filterCapabilities = empty($this->_capabilitiesToSelect) ? false : true;
    }
    
    /**
     * Open the input file and position cursor at the beginning
     * @see $_inputFile
     */
    public function rewind()
    {
        //$this->_xmlReader = new \XMLReader();
        //$this->_xmlReader->open($this->_inputFile);
        
        $this->_xmlReader = \simplexml_load_string(\file_get_contents($this->_inputFile));
        
        $devices = $this->_xmlReader->xpath('/wurfl/devices');
        
        $this->_currentElement = null;
        $this->_currentElementId = null;
    }
    
    /**
     * Removes spaces from the given $subject
     * @param string $subject
     */
    private function _removeSpaces($subject)
    {
        return preg_replace('/\s*/', '', $subject);
    }
    
    public function readNextElement()
    {
        $deviceId = null;
        $groupId = null;
        
        while ($this->_xmlReader->valid()) {
            $nodeName = $this->_xmlReader->name;
            
            switch ($this->_xmlReader->nodeType) {
                case \XMLReader::ELEMENT:
                    switch ($nodeName) {
                        case XmlInterface::DEVICE:
                            $groupIDCapabilitiesMap = array();
                            
                            $deviceId = $this->_xmlReader->getAttribute(XmlInterface::ID);
                            $userAgent = $this->_xmlReader->getAttribute(XmlInterface::USER_AGENT);
                            $fallBack = $this->_xmlReader->getAttribute(XmlInterface::FALL_BACK);
                            $actualDeviceRoot = $this->_xmlReader->getAttribute(XmlInterface::ACTUAL_DEVICE_ROOT);
                            $specific = $this->_xmlReader->getAttribute(XmlInterface::SPECIFIC);
                            $currentCapabilityNameValue = array();
                            if ($this->_xmlReader->isEmptyElement) {
                                $this->currentElement = new ModelDevice($deviceId, $userAgent, $fallBack, $actualDeviceRoot, $specific);
                                break 3;
                            }
                            break;
                        case XmlInterface::GROUP:
                            $groupId = $this->_xmlReader->getAttribute(XmlInterface::GROUP_ID);
                            
                            if ($this->needToReadGroup($groupId)) {
                                $groupIDCapabilitiesMap[$groupId] = array();
                            } else {
                                $this->_moveToGroupEndElement();
                                break 2;
                            }
                            break;
                        case XmlInterface::CAPABILITY:
                            $capabilityName = $this->_xmlReader->getAttribute(XmlInterface::CAPABILITY_NAME);
                            
                            if ($this->neededToReadCapability($groupId, $capabilityName)) {
                                $capabilityValue = $this->_xmlReader->getAttribute(XmlInterface::CAPABILITY_VALUE);
                                $currentCapabilityNameValue[$capabilityName] = $capabilityValue;
                                $groupIDCapabilitiesMap[$groupId][$capabilityName] = $capabilityValue;
                            }
                            
                            break;
                    }
                    break;
                case \XMLReader::END_ELEMENT:
                    if ($nodeName == XmlInterface::DEVICE) {
                        $this->currentElement = new ModelDevice($deviceId, $userAgent, $fallBack, $actualDeviceRoot, $specific, $groupIDCapabilitiesMap);
                        break 2;
                    }
            }
        } // end of while
    }
    
    /**
     * Returns true if the group element needs to be processed
     * @param string $groupId
     * @return bool
     */
    private function needToReadGroup($groupId)
    {
        if ($this->_filterCapabilities) {
            return array_key_exists($groupId, $this->_capabilitiesToSelect);
        }
        
        return true;
    }
    
    /**
     * Returns true if the given $groupId's $capabilityName needs to be read
     * @param string $groupId
     * @param string $capabilityName
     * @return bool
     */
    private function neededToReadCapability($groupId, $capabilityName)
    {
        if (array_key_exists($groupId, $this->_capabilitiesToSelect)) {
            $capabilities = $this->_capabilitiesToSelect[$groupId];
            
            if (empty($capabilities)) {
                return true;
            }
            
            foreach ($capabilities as $capability) {
                if (strcmp($capabilityName, $capability) === 0) {
                    return true;
                }
            }
            
            return false;
        }
        
        return true;
    }
    
    private function _moveToGroupEndElement()
    {
        while (!$this->_groupEndElement()) {
            $this->_xmlReader->read();
        }
    }
    
    /**
     * Returns true if the current element is the ending tag of a group
     * @return bool
     */
    private function _groupEndElement()
    {
        return($this->_xmlReader->name === 'group') && ($this->_xmlReader->nodeType === XMLReader::END_ELEMENT);
    }
}