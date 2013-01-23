<?php
namespace Wurfl\Handlers;

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
 * @category   WURFL
 * @package	WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license	GNU Affero General Public License
 * @version	$id$
 */

/**
 * SPVUserAgentHandler
 *
 *
 * @category   WURFL
 * @package	WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license	GNU Affero General Public License
 * @version	$id$
 */
class SPVHandler extends Handler {
	
	protected $prefix = "SPV";
	
	public function canHandle($userAgent) {
		if (Utils::isDesktopBrowser($userAgent)) return false;
		return Utils::checkIfContains($userAgent, 'SPV');
	}
	
	public function applyConclusiveMatch($userAgent) {
		$tolerance = Utils::indexOfOrLength($userAgent, ';', strpos($userAgent, 'SPV'));
		return $this->getDeviceIDFromRIS($userAgent, $tolerance);
	}
	
}
