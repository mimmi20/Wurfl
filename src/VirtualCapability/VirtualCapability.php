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

namespace Wurfl\VirtualCapability;

use Wurfl\CustomDevice;
use Wurfl\Request\GenericRequest;

/**
 * Defines the virtual capabilities
 */
abstract class VirtualCapability
{
    /**
     * @var array
     */
    protected $requiredCapabilities = array();

    /**
     * @var bool
     */
    protected $useCaching = false;

    /**
     * @var mixed
     */
    protected $cachedValue = null;

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
            self::$loadedCapabilities = $this->device->getRootDevice()->getCapabilityNames();
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
     * @return mixed|string
     */
    public function getValue()
    {
        $value = ($this->useCaching) ? $this->computeCached() : $this->compute();

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return $value;
    }

    /**
     * @return mixed
     */
    abstract protected function compute();

    /**
     * @return mixed
     */
    private function computeCached()
    {
        if ($this->cachedValue === null) {
            $this->cachedValue = $this->compute();
        }

        return $this->cachedValue;
    }
}
