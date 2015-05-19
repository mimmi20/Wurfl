<?php
/**
 * Copyright (c) 2015 ScientiaMobile, Inc.
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
 */

namespace Wurfl;

/**
 * WURFL Custom Device - this is the core class that is used by developers to access the
 * properties and capabilities of a mobile device
 *
 * Examples:
 * <code>
 * // Create a WURFL Manager and detect device first
 * $wurflManagerFactory = new \Wurfl\ManagerFactory($wurflConfig);
 * $wurflManager = $wurflManagerFactory->create();
 * $device = $wurflManager->getDeviceForHttpRequest($_SERVER);
 *
 * // Example 1: Get display resolution from device
 * $width = $device->getCapability('resolution_width');
 * $height = $device->getCapability('resolution_height');
 * echo 'Resolution: $width x $height <br/>';
 *
 * // Example 2: Get the WURFL ID of the device
 * $wurflID = $device->id;
 * </code>
 *
 * @property-read string                        $id               WURFL Device ID
 * @property-read string                        $userAgent        User Agent
 * @property-read string                        $fallBack         Fallback Device ID
 * @property-read bool                          $actualDeviceRoot true if device is an actual root device
 * @property-read \Wurfl\Request\GenericRequest $request
 * @property-read \Wurfl\Xml\ModelDevice[]      $modelDevices
 * @property-read string                        $pointing_method
 * @property-read string                        $is_tablet
 * @property-read bool                          $can_assign_phone_number
 * @package WURFL
 */
class CustomDevice
{
    /**
     * @var \Wurfl\Xml\ModelDevice[] Array of \Wurfl\Xml\ModelDevice objects
     */
    private $modelDevices;

    /**
     * @var \Wurfl\Request\GenericRequest
     */
    private $request;

    /**
     * @var VirtualCapability\VirtualCapabilityProvider
     */
    private $virtualCapabilityProvider;

    /**
     * @param \Wurfl\Xml\ModelDevice[]      $modelDevices Array of \Wurfl\Xml\ModelDevice objects
     * @param \Wurfl\Request\GenericRequest $request
     *
     * @throws \InvalidArgumentException if $modelDevices is not an array of at least one \Wurfl\Xml\ModelDevice
     */
    public function __construct(array $modelDevices, Request\GenericRequest $request = null)
    {
        if (!is_array($modelDevices) || count($modelDevices) < 1) {
            throw new \InvalidArgumentException('modelDevices must be an array of at least one ModelDevice.');
        }

        $this->modelDevices = $modelDevices;

        if ($request === null) {
            // This might happen if a device is looked up by its ID directly, without providing a user agent
            $requestFactory = new Request\GenericRequestFactory();
            $request        = $requestFactory->createRequestForUserAgent($this->userAgent);
        }

        $this->request = $request;
    }

    /**
     * Magic Method
     *
     * @param string $name
     *
     * @return string
     */
    public function __get($name)
    {
        switch ($name) {
            case 'request':
                return $this->request;
                break;
            case 'matchInfo':
                return $this->request->matchInfo;
                break;
            case 'modelDevices':
                return $this->modelDevices;
                break;
            case 'userAgentsWithDeviceID':
                return $this->request->userAgentsWithDeviceID;
                break;
            case 'id':
            case 'userAgent':
            case 'fallBack':
            case 'actualDeviceRoot':
                return $this->modelDevices[0]->$name;
                break;
            default:
                if ($this->getVirtualCapabilityProvider()->exists($name)) {
                    return $this->getVirtualCapabilityProvider()->get($name);
                }

                return $this->getCapability($name);
                break;
        }
    }

    /**
     * Device is a specific or actual WURFL device as defined by its capabilities
     *
     * @return bool
     */
    public function isSpecific()
    {
        foreach ($this->modelDevices as $modelDevice) {
            if ($modelDevice->specific === true || $modelDevice->actualDeviceRoot === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the value of a given capability name
     * for the current device
     *
     * @param string $capabilityName must be a valid capability name
     *
     * @return string Capability value
     * @throws \InvalidArgumentException The $capabilityName is is not defined in the loaded WURFL.
     * @see \Wurfl\Xml\ModelDevice::getCapability()
     */
    public function getCapability($capabilityName)
    {
        if (empty($capabilityName)) {
            throw new \InvalidArgumentException('capability name must not be empty');
        }

        if (!$this->getRootDevice()->isCapabilityDefined($capabilityName)) {
            throw new \InvalidArgumentException('no capability named [' . $capabilityName . '] is present in wurfl.');
        }

        foreach ($this->modelDevices as $modelDevice) {
            /* @var \Wurfl\Xml\ModelDevice $modelDevice */
            $capabilityValue = $modelDevice->getCapability($capabilityName);
            if ($capabilityValue != null) {
                return $capabilityValue;
            }
        }

        return '';
    }

    /**
     * Returns the nearest actual device root in the fall back tree.  If this device is a device root itself,
     * it is returned.  Some devices have no device roots in their fall back tree, like generic_android, since
     * no devices above it (itself included) are real devices (actual device roots).
     *
     * @return \Wurfl\Xml\ModelDevice
     */
    public function getActualDeviceRootAncestor()
    {
        if ($this->actualDeviceRoot) {
            return $this;
        }
        foreach ($this->modelDevices as $modelDevice) {
            /* @var \Wurfl\Xml\ModelDevice $modelDevice */
            if ($modelDevice->actualDeviceRoot) {
                return $modelDevice;
            }
        }

        return null;
    }

    /**
     * Returns the match info for this device
     *
     * @return \Wurfl\Request\MatchInfo|null
     */
    public function getMatchInfo()
    {
        return ($this->request instanceof Request\GenericRequest) ? $this->request->matchInfo : null;
    }

    /**
     * Returns an array with all the fall back devices, from the matched device to the root device ('generic')
     *
     * @return \Wurfl\Xml\ModelDevice[]
     */
    public function getFallBackDevices()
    {
        return $this->modelDevices;
    }

    /**
     * Returns the top-most device.  This is the 'generic' device.
     *
     * @return \Wurfl\Xml\ModelDevice
     */
    public function getRootDevice()
    {
        return $this->modelDevices[count($this->modelDevices) - 1];
    }

    /**
     * Returns capabilities and their values for the current device
     *
     * @return array Device capabilities array
     * @see \Wurfl\Xml\ModelDevice::getCapabilities()
     */
    public function getAllCapabilities()
    {
        $capabilities = array();
        foreach (array_reverse($this->modelDevices) as $modelDevice) {
            /** @var \Wurfl\Xml\ModelDevice $modelDevice */
            $capabilities = array_merge($capabilities, $modelDevice->getCapabilities());
        }

        return $capabilities;
    }

    /**
     * @return VirtualCapability\VirtualCapabilityProvider
     */
    public function getVirtualCapabilityProvider()
    {
        if (null === $this->virtualCapabilityProvider) {
            $this->setVirtualCapabilityProvider(new VirtualCapability\VirtualCapabilityProvider($this, $this->request));
        }

        return $this->virtualCapabilityProvider;
    }

    /**
     * @param VirtualCapability\VirtualCapabilityProvider $virtualCapabilityProvider
     *
     * @return \Wurfl\CustomDevice
     */
    public function setVirtualCapabilityProvider($virtualCapabilityProvider)
    {
        $this->virtualCapabilityProvider = $virtualCapabilityProvider;

        return $this;
    }

    /**
     * @param $name
     *
     * @return bool|float|int|string
     */
    public function getVirtualCapability($name)
    {
        return $this->getVirtualCapabilityProvider()->get($name);
    }

    /**
     * @return array
     */
    public function getAllVirtualCapabilities()
    {
        return $this->getVirtualCapabilityProvider()->getAll();
    }
}
