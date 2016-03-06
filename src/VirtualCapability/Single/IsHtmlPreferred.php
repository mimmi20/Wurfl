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
class IsHtmlPreferred extends VirtualCapability
{
    /**
     * @var array
     */
    protected $requiredCapabilities = array('preferred_markup');

    /**
     * @return bool
     */
    protected function compute()
    {
        return (strpos($this->device->getCapability('preferred_markup'), 'html_web') === 0);
    }
}
