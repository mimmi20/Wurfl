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
 * FirefoxOSUserAgentHandler
 * 
 *
 * @category   WURFL
 * @package	WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license	GNU Affero General Public License
 * @version	$id$
 */
class WURFL_Handlers_FirefoxOSHandler extends WURFL_Handlers_Handler {
	
	protected $prefix = "FIREFOXOS";
	
	public static $constantIDs = array(
		'generic_firefox_os',
		'firefox_os_ver1',
		'firefox_os_ver1_1',
	);
	
	public function canHandle($userAgent) {
		return (WURFL_Handlers_Utils::checkIfContains($userAgent, 'Firefox/') && WURFL_Handlers_Utils::checkIfContains($userAgent, 'Mobile'));
	}
	
	public function applyConclusiveMatch($userAgent) {
		// Mozilla/5.0 (Mobile; rv:18.0) Gecko/18.0 Firefox/18.0
		if (preg_match('#\brv:\d+\.\d+(.)#', $userAgent, $matches, PREG_OFFSET_CAPTURE)) {
			$tolerance = $matches[1][1] + 1;
			return $this->getDeviceIDFromRIS($userAgent, $tolerance);
		}
		return WURFL_Constants::NO_MATCH;
	}
	
	public function applyRecoveryMatch($userAgent) {
		if (preg_match('#\brv:(\d+\.\d+)#', $userAgent, $matches)) {
			if ($matches[1] > 18.0) {
				return 'firefox_os_ver1_1';
			} else {
				return 'firefox_os_ver1';
			} 
		}
		return 'generic_firefox_os';
	}
}
