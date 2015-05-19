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

namespace Wurfl\VirtualCapability\Group;

use Wurfl\CustomDevice;
use Wurfl\Request\GenericRequest;

/**
 * @package \Wurfl\VirtualCapability\VirtualCapability
 */
abstract class Group
{
    /**
     * @var array
     */
    protected $requiredCapabilities = array();

    /**
     * @var array
     */
    protected $virtualCapabilities = array();

    /**
     * @var array
     */
    protected $storage = array();

    /**
     * @var array
     */
    private static $loadedCapabilities = array();

    /**
     * @var \Wurfl\CustomDevice
     */
    protected $device = null;

    /**
     * @var \Wurfl\Request\GenericRequest
     */
    protected $request = null;

    /**
     * @param \Wurfl\CustomDevice           $device
     * @param \Wurfl\Request\GenericRequest $request
     */
    public function __construct(CustomDevice $device = null, GenericRequest $request = null)
    {
        $this->device  = $device;
        $this->request = $request;
    }

    /**
     * @return bool
     */
    public function hasRequiredCapabilities()
    {
        if (empty($this->requiredCapabilities)) {
            return true;
        }

        if (self::$loadedCapabilities === null) {
            self::$loadedCapabilities = $this->device->getRootDevice()
                ->getCapabilityNames();
        }

        $missingCaps = array_diff($this->requiredCapabilities, self::$loadedCapabilities);

        return empty($missingCaps);
    }

    /**
     * @return array
     */
    public function getRequiredCapabilities()
    {
        return $this->requiredCapabilities;
    }

    /**
     * @return mixed
     */
    abstract public function compute();

    /**
     * @param $name
     *
     * @return mixed
     */
    public function get($name)
    {
        return $this->storage[$name];
    }
}
