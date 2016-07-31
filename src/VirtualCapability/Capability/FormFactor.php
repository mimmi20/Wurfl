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

namespace Wurfl\VirtualCapability\Capability;

use Wurfl\VirtualCapability\VirtualCapability;

/**
 * Virtual capability helper
 */
class FormFactor extends VirtualCapability
{
    /**
     * @var array
     */
    protected $requiredCapabilities = array(
        'ux_full_desktop',
        'is_smarttv',
        'is_wireless_device',
        'is_tablet',
        'can_assign_phone_number',
    );

    /**
     * @return int|mixed|string
     */
    public function compute()
    {
        $vcIsRobot = new IsRobot($this->device, $this->request);
        if ($vcIsRobot->getValue() === true) {
            return 'Robot';
        }

        if ($this->device->getCapability('ux_full_desktop') === true) {
            return 'Desktop';
        }

        if ($this->device->getCapability('is_smarttv') === true) {
            return 'Smart-TV';
        }

        if ($this->device->getCapability('is_wireless_device') === false) {
            return 'Other Non-Mobile';
        }

        if ($this->device->getCapability('is_tablet') === true) {
            return 'Tablet';
        }

        $vcIsSmartphone = new IsSmartphone($this->device, $this->request);
        if ($vcIsSmartphone->getValue() === true) {
            return 'Smartphone';
        }

        if ($this->device->getCapability('can_assign_phone_number') === true) {
            return 'Feature Phone';
        }

        return 'Other Mobile';
    }
}
