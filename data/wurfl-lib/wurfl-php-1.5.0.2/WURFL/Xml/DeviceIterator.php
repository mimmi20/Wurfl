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
 * @package	WURFL_Xml
 * @copyright  ScientiaMobile, Inc.
 * @license	GNU Affero General Public License
 * @version	$id$
 *
 */
/**
 * Extracts device capabilities from XML file
 * @package	WURFL_Xml
 */
class WURFL_Xml_DeviceIterator extends WURFL_Xml_AbstractIterator {
	
	private $capabilityFilter = array ();
	private $useCapabilityFilter = false;
	
	/**
	 * @param string $inputFile XML file to be processed
	 * @param array $capabilityFilter Capabiities to process
	 */
	public function __construct($inputFile, $capabilityFilter = array()) {
		parent::__construct($inputFile);
		$this->capabilityFilter = $capabilityFilter;
		$this->useCapabilityFilter = !empty($this->capabilityFilter);
	}
	
	public function readNextElement() {
		
		$deviceId = $groupId = $userAgent = $fallBack = $actualDeviceRoot = $specific = $groupIDCapabilitiesMap = null;
		
		while ($this->xmlReader->read()) {
			
			$nodeName = $this->xmlReader->name;
			switch ($this->xmlReader->nodeType) {
				case XMLReader::ELEMENT:
					switch ($nodeName) {
						case WURFL_Xml_Interface::DEVICE:
							$groupIDCapabilitiesMap = array();
							
							$deviceId = $this->xmlReader->getAttribute(WURFL_Xml_Interface::ID);
							$userAgent = $this->xmlReader->getAttribute(WURFL_Xml_Interface::USER_AGENT);
							$fallBack = $this->xmlReader->getAttribute(WURFL_Xml_Interface::FALL_BACK);
							$actualDeviceRoot = $this->xmlReader->getAttribute(WURFL_Xml_Interface::ACTUAL_DEVICE_ROOT);
							$specific = $this->xmlReader->getAttribute(WURFL_Xml_Interface::SPECIFIC);
							$currentCapabilityNameValue = array();
							if ($this->xmlReader->isEmptyElement) {
								$this->currentElement = new WURFL_Xml_ModelDevice($deviceId, $userAgent, $fallBack, $actualDeviceRoot, $specific);
								break 3;
							}
							break;
						
						case WURFL_Xml_Interface::GROUP:
							$groupId = $this->xmlReader->getAttribute(WURFL_Xml_Interface::GROUP_ID);
							$groupIDCapabilitiesMap[$groupId] = array();
							break;
						
						case WURFL_Xml_Interface::CAPABILITY:
							
							$capabilityName = $this->xmlReader->getAttribute(WURFL_Xml_Interface::CAPABILITY_NAME);
							if ($this->needToReadCapability($capabilityName)) {
								$capabilityValue = $this->xmlReader->getAttribute(WURFL_Xml_Interface::CAPABILITY_VALUE);
								$currentCapabilityNameValue[$capabilityName] = $capabilityValue;
								$groupIDCapabilitiesMap[$groupId][$capabilityName] = $capabilityValue;
							}
							
							break;
					}
					
					break;
				case XMLReader::END_ELEMENT:
					if ($nodeName == WURFL_Xml_Interface::DEVICE) {
						$this->currentElement = new WURFL_Xml_ModelDevice($deviceId, $userAgent, $fallBack, $actualDeviceRoot, $specific, $groupIDCapabilitiesMap);
						break 2;
					}
			}
		} // end of while
	}
	
	/**
	 * Returns true if the given $capabilityName needs to be read
	 * @param string $capabilityName
	 * @return bool
	 */
	private function needToReadCapability($capabilityName) {
		if (!$this->useCapabilityFilter) {
			return true;
		}
		return in_array($capabilityName, $this->capabilityFilter);
	}
}