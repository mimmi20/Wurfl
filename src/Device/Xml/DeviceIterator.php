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
 *
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */

namespace Wurfl\Device\Xml;

use Wurfl\Device\ModelDevice;

/**
 * Extracts device capabilities from XML file
 */
class DeviceIterator extends AbstractIterator
{
    /**
     * @var array
     */
    private $capabilityFilter = array();

    /**
     * @var bool
     */
    private $useCapabilityFilter = false;

    /**
     * @param string $inputFile        XML file to be processed
     * @param array  $capabilityFilter Capabilities to process
     */
    public function __construct($inputFile, $capabilityFilter = array())
    {
        parent::__construct($inputFile);

        $this->capabilityFilter    = $capabilityFilter;
        $this->useCapabilityFilter = !empty($this->capabilityFilter);
    }

    /**
     *
     */
    public function readNextElement()
    {
        $deviceId = $groupId = $userAgent = $fallBack = $actualDeviceRoot = $specific = $groupIDCapabilitiesMap = null;

        while ($this->xmlReader->read()) {
            $nodeName = $this->xmlReader->name;

            switch ($this->xmlReader->nodeType) {
                case \XMLReader::ELEMENT:
                    switch ($nodeName) {
                        case XmlInterface::DEVICE:
                            $groupIDCapabilitiesMap = array();

                            $deviceId                   = $this->xmlReader->getAttribute(XmlInterface::ID);
                            $userAgent                  = $this->xmlReader->getAttribute(XmlInterface::USER_AGENT);
                            $fallBack                   = $this->xmlReader->getAttribute(XmlInterface::FALL_BACK);
                            $actualDeviceRoot           = $this->xmlReader->getAttribute(
                                XmlInterface::ACTUAL_DEVICE_ROOT
                            );
                            $specific                   = $this->xmlReader->getAttribute(XmlInterface::SPECIFIC);
                            $currentCapabilityNameValue = array();

                            if ($this->xmlReader->isEmptyElement) {
                                $this->currentElement = new ModelDevice($deviceId, $userAgent, $fallBack, $actualDeviceRoot, $specific);

                                break 3;
                            }

                            break;

                        case XmlInterface::GROUP:
                            $groupId = $this->xmlReader->getAttribute(XmlInterface::GROUP_ID);

                            if ($groupId !== 'virtual_capabilities') {
                                $groupIDCapabilitiesMap[$groupId] = array();
                            }
                            break;

                        case XmlInterface::CAPABILITY:
                            if ($groupId === 'virtual_capabilities') {
                                break;
                            }

                            $capabilityName = $this->xmlReader->getAttribute(XmlInterface::CAPABILITY_NAME);

                            if ($this->needToReadCapability($capabilityName)) {
                                $capabilityValue = $this->xmlReader->getAttribute(XmlInterface::CAPABILITY_VALUE);

                                $currentCapabilityNameValue[$capabilityName]       = $capabilityValue;
                                $groupIDCapabilitiesMap[$groupId][$capabilityName] = $capabilityValue;
                            }

                            break;
                        default:
                            // nothing to do here
                            break;
                    }

                    break;
                case \XMLReader::END_ELEMENT:
                    if ($nodeName === XmlInterface::DEVICE) {
                        $this->currentElement = new ModelDevice($deviceId, $userAgent, $fallBack, $actualDeviceRoot, $specific, $groupIDCapabilitiesMap);
                        break 2;
                    }
                    break;
                default:
                    // nothing to do here
                    break;
            }
        } // end of while
    }

    /**
     * Returns true if the given $capabilityName needs to be read
     *
     * @param string $capabilityName
     *
     * @return bool
     */
    private function needToReadCapability($capabilityName)
    {
        if (!$this->useCapabilityFilter) {
            return true;
        }

        return in_array($capabilityName, $this->capabilityFilter);
    }
}
