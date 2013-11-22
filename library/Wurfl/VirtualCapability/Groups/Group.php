<?php
namespace Wurfl\VirtualCapability\Groups;

/**
 * Copyright (c) 2012 ScientiaMobile, Inc.
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 * Refer to the COPYING.txt file distributed with this package.
 *
 * @category   WURFL
 * @package    WURFL_VirtualCapability
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
use Wurfl\CustomDevice;
use Wurfl\Request\GenericRequest;

/**
 * @package WURFL_VirtualCapability
 */
abstract class Group
{
    protected $required_capabilities = array();
    protected $virtual_capabilities = array();
    protected $storage = array();

    private static $loaded_capabilities;

    /**
     * @var CustomDevice
     */
    protected $device;

    /**
     * @var GenericRequest
     */
    protected $request;

    /**
     * @param CustomDevice   $device
     * @param GenericRequest $request
     */
    public function __construct(
        CustomDevice $device = null,
        GenericRequest $request = null
    ) {
        $this->device  = $device;
        $this->request = $request;
    }

    public function hasRequiredCapabilities()
    {
        if (empty($this->required_capabilities)) {
            return true;
        }

        if (self::$loaded_capabilities === null) {
            self::$loaded_capabilities = $this->device->getRootDevice()->getCapabilityNames();
        }

        $missing_caps = array_diff($this->required_capabilities, self::$loaded_capabilities);

        return empty($missing_caps);
    }

    /**
     * @return array
     */
    public function getRequiredCapabilities()
    {
        return $this->required_capabilities;
    }

    abstract public function compute();

    public function get($name)
    {
        return $this->storage[$name];
    }
}