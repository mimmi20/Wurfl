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

namespace Wurfl\VirtualCapability\Single;

use Wurfl\VirtualCapability\VirtualCapability;

/**
 * Virtual capability helper
 */
class IsLargescreen extends VirtualCapability
{
    /**
     * @var array
     */
    protected $requiredCapabilities = array(
        'resolution_width',
        'resolution_height',
    );

    /**
     * @return bool
     */
    protected function compute()
    {
        return ($this->device->getCapability('resolution_width') >= 480 && $this->device->getCapability('resolution_height') >= 480);
    }
}
