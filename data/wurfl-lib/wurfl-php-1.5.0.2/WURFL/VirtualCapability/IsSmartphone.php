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
 * @package	WURFL_VirtualCapability
 * @copyright  ScientiaMobile, Inc.
 * @license	GNU Affero General Public License
 * @version	$id$
 */
/**
 * Virtual capability helper
 * @package	WURFL_VirtualCapability
 */
 
class WURFL_VirtualCapability_IsSmartphone extends WURFL_VirtualCapability {

	protected $use_caching = true;

	protected $required_capabilities = array(
		'is_wireless_device',
		'is_tablet',
		'pointing_method',
		'resolution_width',
		'device_os_version',
		'device_os',
	);

	protected function compute() {
		if ($this->device->is_wireless_device != "true") return false;
		if ($this->device->is_tablet == "true") return false;
		if ($this->device->pointing_method != 'touchscreen') return false;
		if ($this->device->resolution_width < 320) return false;
		$os_ver = (float)$this->device->device_os_version;
		switch ($this->device->device_os) {
			case 'iOS':
				return ($os_ver >= 3.0);
				break;
			case 'Android':
				return ($os_ver >= 2.2);
				break;
			case 'Windows Phone OS':
				return true;
				break;
			case 'RIM OS':
				return ($os_ver >= 7.0);
				break;
			case 'webOS':
				return true;
				break;
			case 'MeeGo':
				return true;
				break;
			case 'Bada OS':
				return ($os_ver >= 2.0);
				break;
			default:
				return false;
				break;
		}
	}
}
