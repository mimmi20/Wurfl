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
 * @package    \Wurfl\Cache
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
use Wurfl\Storage\StorageInterface;

/**
 * Holds WURFL Device capabilities
 *
 * @package    WURFL
 */
class CapabilitiesHolder
{
    /**
     * @var Xml\ModelDevice
     */
    private $_device;
    /**
     * @var DeviceRepository
     */
    private $_deviceRepository;
    /**
     * @var StorageInterface
     */
    private $_cacheProvider;

    /**
     * @param Xml\ModelDevice  $device
     * @param DeviceRepository $deviceRepository
     * @param StorageInterface $cacheProvider
     */
    public function __construct(
        Xml\ModelDevice $device,
        DeviceRepository $deviceRepository,
        StorageInterface $cacheProvider
    ) {
        $this->_device           = $device;
        $this->_deviceRepository = $deviceRepository;
        $this->_cacheProvider    = $cacheProvider;
    }

    /**
     * Returns the value of a given capability name
     *
     * @param string $capabilityName
     *
     * @return string Capability value
     */
    public function getCapability($capabilityName)
    {

        if (isset($this->_device->capabilities[$capabilityName])) {
            return $this->_device->capabilities[$capabilityName];
        }

        $key             = $this->_device->id . '_' . $capabilityName;
        $capabilityValue = $this->_cacheProvider->load($key);
        if (empty($capabilityValue)) {

            $capabilityValue = $this->_deviceRepository->getCapabilityForDevice(
                $this->_device->fallBack,
                $capabilityName
            );
            // save it in cache
            $this->_cacheProvider->save($key, $capabilityValue);
        }

        return $capabilityValue;
    }

    /**
     * Returns all the capabilities value of the current device as <capabilityName, capabilityValue>
     *
     * @return array All capabilities
     */
    public function getAllCapabilities()
    {
        return $this->_deviceRepository->getAllCapabilitiesForDevice($this->_device->id);
    }
}