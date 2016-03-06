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
        $map = array(
            'Robot'            => $this->device->getVirtualCapability('is_robot'),
            'Desktop'          => $this->device->getCapability('ux_full_desktop'),
            'Smart-TV'         => $this->device->getCapability('is_smarttv'),
            'Other Non-Mobile' => ('true' === $this->device->getCapability('is_wireless_device') ? 'false' : 'true'),
            'Tablet'           => $this->device->getCapability('is_tablet'),
            'Smartphone'       => $this->device->getVirtualCapability('is_smartphone'),
            'Feature Phone'    => $this->device->getCapability('can_assign_phone_number'),
        );

        foreach ($map as $type => $condition) {
            if ($condition === 'true') {
                return $type;
            }
        }

        return 'Other Mobile';
    }
}
