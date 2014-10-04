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
 *
 * @category   WURFL
 * @package    WURFL
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */

namespace Wurfl\VirtualCapability\Single;

use Wurfl\VirtualCapability\VirtualCapability;

/**
 * Virtual capability helper
 *
 * @package    \Wurfl\VirtualCapability\VirtualCapability
 */
class IsTouchscreen
    extends VirtualCapability
{
    /**
     * @var array
     */
    protected $requiredCapabilities = array('pointing_method');

    /**
     * @return bool|mixed
     */
    protected function compute()
    {
        return ('touchscreen' === $this->device->pointing_method);
    }
}
