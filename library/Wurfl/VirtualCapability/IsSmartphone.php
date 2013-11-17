<?php
namespace Wurfl\VirtualCapability;

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
use Wurfl\VirtualCapability;

/**
 * Virtual capability helper
 *
 * @package    WURFL_VirtualCapability
 */

class IsSmartphone extends VirtualCapability
{
    protected
        $use_caching = true;

    protected
        $required_capabilities = array(
        'is_wireless_device',
        'is_tablet',
        'pointing_method',
        'resolution_width',
        'device_os_version',
        'device_os',
    );

    protected function compute()
    {
        if ('true' !== $this->device->getCapability('is_wireless_device')) {
            return false;
        }

        if ('true' === $this->device->getCapability('is_tablet')) {
            return false;
        }

        if ('touchscreen' !== $this->device->getCapability('pointing_method')) {
            return false;
        }

        if ($this->device->getCapability('resolution_width') < 320) {
            return false;
        }

        $osVersion = (float) $this->device->getCapability('device_os_version');
        switch ($this->device->getCapability('device_os')) {
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
                return false;
                break;
        }
    }
}
