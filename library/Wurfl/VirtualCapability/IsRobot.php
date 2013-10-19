<?php
namespace Wurfl\VirtualCapability;

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
 
class IsRobot extends \Wurfl\VirtualCapability {

	protected $required_capabilities = array();

	protected function compute() {
		// Control cap, "controlcap_is_robot" is checked before this function is called

		// Check against standard bot list
		return \Wurfl\Handlers\Utils::isRobot($this->request->userAgent);
	}
}