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

namespace Wurfl\VirtualCapability\Single;

use Wurfl\VirtualCapability\VirtualCapability;

/**
 * Virtual capability helper
 *
 * @package    \Wurfl\VirtualCapability\VirtualCapability
 */
class IsSmartphone
    extends VirtualCapability
{
    /**
     * @var bool
     */
    protected $useCaching = true;

    /**
     * @var array
     */
    protected $requiredCapabilities = array(
        'is_wireless_device',
        'is_tablet',
        'pointing_method',
        'resolution_width',
        'device_os_version',
        'device_os',
        'can_assign_phone_number',
    );

    /**
     * @return bool|mixed
     */
    protected function compute()
    {
        if ($this->device->is_wireless_device != 'true'
            || $this->device->is_tablet == 'true'
            || $this->device->pointing_method != 'touchscreen'
            || $this->device->resolution_width < 320
            || $this->device->can_assign_phone_number == 'false'
        ) {
            return false;
        }

        $osVersion = (float) $this->device->device_os_version;

        switch ($this->device->device_os) {
            case 'iOS':
                return ($osVersion >= 3.0);
                break;
            case 'Android':
                return ($osVersion >= 2.2);
                break;
            case 'Windows Phone OS':
                return true;
                break;
            case 'RIM OS':
                return ($osVersion >= 7.0);
                break;
            case 'webOS':
                return true;
                break;
            case 'MeeGo':
                return true;
                break;
            case 'Bada OS':
                return ($osVersion >= 2.0);
                break;
            default:
                // nothing to do here
                break;
        }

        return false;
    }
}
