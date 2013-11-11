<?php
namespace Wurfl;

    /**
     * Copyright (c) 2012 ScientiaMobile, Inc.
     * This program is free software: you can redistribute it and/or modify
     * it under the terms of the GNU Affero General Public License as
     * published by the Free Software Foundation, either version 3 of the
     * License, or (at your option) any later version.
     * Refer to the COPYING.txt file distributed with this package.
     *
     * @category   WURFL
     * @package    WURFL
     * @copyright  ScientiaMobile, Inc.
     * @license    GNU Affero General Public License
     * @version    $id$
     */
use Wurfl\CapabilitiesHolder;
use Wurfl\Xml\ModelDevice;

/**
 * A WURFL Device including methods to access its capabilities
 *
 * @package    WURFL
 */
class Device
{
    /**
     * @var ModelDevice
     */
    private $_modelDevice;
    /**
     * @var CapabilitiesHolder
     */
    private $_capabilitiesHolder;

    /**
     * Creates a new \Wurfl\Device using the given $modelDevice and $capabilitiesHolder
     *
     * @param ModelDevice    $modelDevice
     * @param CapabilitiesHolder $capabilitiesHolder
     */
    public function __construct(ModelDevice $modelDevice, CapabilitiesHolder $capabilitiesHolder)
    {
        $this->_modelDevice        = $modelDevice;
        $this->_capabilitiesHolder = $capabilitiesHolder;
    }

    /**
     * Magic Method
     *
     * @param string $name
     *
     * @throws Exception The field $name is invalid
     * @return string value
     */
    public function __get($name)
    {
        if (isset($name)) {
            switch ($name) {
                case "id":
                case "userAgent":
                case "fallBack":
                case "actualDeviceRoot":
                    return $this->_modelDevice->$name;
                    break;
                default:
                    throw new Exception("the field " . $name . " is not defined");
                    break;
            }
        }

        throw new Exception("the field " . $name . " is not defined");
    }

    /**
     * Returns the value of a given capability name
     * for the current device
     *
     * @param string $capabilityName must be a valid capability name
     *
     * @throws \InvalidArgumentException $capabilityName is null
     * @return string
     */
    public function getCapability($capabilityName)
    {
        if (!isset($capabilityName)) {
            throw new \InvalidArgumentException("capability name must not be null");
        }

        return $this->_capabilitiesHolder->getCapability($capabilityName);
    }

    /**
     * Returns all the value of the capabilities of the current device
     *
     * @return array All device capabilities
     */
    public function getAllCapabilities()
    {
        return $this->_capabilitiesHolder->getAllCapabilities();
    }
}