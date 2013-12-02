<?php
namespace Wurfl\VirtualCapability\Single;

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
 * @package    \Wurfl\VirtualCapability\VirtualCapability
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
/**
 * Virtual capability helper
 * @package    \Wurfl\VirtualCapability\VirtualCapability
 */
 
class IsLargescreen extends \Wurfl\VirtualCapability\VirtualCapability {

    protected $required_capabilities = array(
        'resolution_width',
        'resolution_height',
    );

    protected function compute() {
        return ($this->device->resolution_width >= 480 && $this->device->resolution_height >= 480);
    }
}