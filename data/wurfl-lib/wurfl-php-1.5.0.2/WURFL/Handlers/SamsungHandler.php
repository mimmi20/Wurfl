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
 * SamsungUserAgentHandler
 *
 *
 * @category   WURFL
 * @package	WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license	GNU Affero General Public License
 * @version	$id$
 */
class WURFL_Handlers_SamsungHandler extends WURFL_Handlers_Handler {

	protected $prefix = "SAMSUNG";
	
	public function canHandle($userAgent) {
		if (WURFL_Handlers_Utils::isDesktopBrowser($userAgent)) return false;
		return WURFL_Handlers_Utils::checkIfContainsAnyOf($userAgent, array('Samsung', 'SAMSUNG'))
			|| WURFL_Handlers_Utils::checkIfStartsWithAnyOf($userAgent, array('SEC-', 'SPH', 'SGH', 'SCH'));
	}
	
	public function applyConclusiveMatch($userAgent) {
		if (WURFL_Handlers_Utils::checkIfStartsWithAnyOf($userAgent, array("SEC-", "SAMSUNG-", "SCH"))) {
			$tolerance = WURFL_Handlers_Utils::firstSlash($userAgent);
		} else if (WURFL_Handlers_Utils::checkIfStartsWithAnyOf($userAgent, array("Samsung", "SPH", "SGH"))) {
			$tolerance = WURFL_Handlers_Utils::firstSpace($userAgent);
		} else {
			$tolerance = WURFL_Handlers_Utils::secondSlash($userAgent);
		}
		return $this->getDeviceIDFromRIS($userAgent, $tolerance);
	}
	
	public function applyRecoveryMatch($userAgent) {
		if (WURFL_Handlers_Utils::checkIfStartsWith($userAgent, 'SAMSUNG')) {
			$tolerance = 8;
			return $this->getDeviceIDFromLD($userAgent, $tolerance);
		} else {
			$tolerance = WURFL_Handlers_Utils::indexOfOrLength($userAgent, '/', strpos($userAgent, 'Samsung'));
			return $this->getDeviceIDFromRIS($userAgent, $tolerance);
		}
	}
}