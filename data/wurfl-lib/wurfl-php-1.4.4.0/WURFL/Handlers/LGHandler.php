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
 * @package	WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license	GNU Affero General Public License
 * @version	$id$
 */

/**
 * LGUserAgentHandler
 *
 *
 * @category   WURFL
 * @package	WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license	GNU Affero General Public License
 * @version	$id$
 */
class WURFL_Handlers_LGHandler extends WURFL_Handlers_Handler {
	
	protected $prefix = "LG";
	
	public function canHandle($userAgent) {
		if (WURFL_Handlers_Utils::isDesktopBrowser($userAgent)) return false;
		return WURFL_Handlers_Utils::checkIfStartsWithAnyOf($userAgent, array('lg', 'LG'));
	}
	
	public function applyConclusiveMatch($userAgent) {
		$tolerance = WURFL_Handlers_Utils::indexOfOrLength($userAgent, '/', stripos($userAgent, 'LG'));
		return $this->getDeviceIDFromRIS($userAgent, $tolerance);
	}
	
	public function applyRecoveryMatch($userAgent) {
		return $this->getDeviceIDFromRIS($userAgent, 7);
	}
}