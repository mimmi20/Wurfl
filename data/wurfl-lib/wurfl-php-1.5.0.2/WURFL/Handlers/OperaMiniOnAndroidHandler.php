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
 * OperaMiniOnAndroidUserAgentHandler
 * 
 *
 * @category   WURFL
 * @package	WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license	GNU Affero General Public License
 * @version	$id$
 */
class WURFL_Handlers_OperaMiniOnAndroidHandler extends WURFL_Handlers_Handler {
	
	protected $prefix = "OPERAMINIONANDROID";
	
	public static $constantIDs = array(
		'uabait_opera_mini_android_v50',
		'uabait_opera_mini_android_v51',
		'generic_opera_mini_android_version5',
	);
	
	public function canHandle($userAgent) {
		if (WURFL_Handlers_Utils::isDesktopBrowser($userAgent)) return false;
		return WURFL_Handlers_Utils::checkIfContainsAll($userAgent, array('Android', 'Opera Mini'));
	}
	
	public function applyConclusiveMatch($userAgent) {
		if (WURFL_Handlers_Utils::checkIfContains($userAgent, ' Build/')) {
			return $this->getDeviceIDFromRIS($userAgent, WURFL_Handlers_Utils::indexOfOrLength($userAgent, ' Build/'));
		}
		$prefixes = array(
			'Opera/9.80 (J2ME/MIDP; Opera Mini/5' => 'uabait_opera_mini_android_v50',
			'Opera/9.80 (Android; Opera Mini/5.0' => 'uabait_opera_mini_android_v50',
			'Opera/9.80 (Android; Opera Mini/5.1' => 'uabait_opera_mini_android_v51',
		);
		foreach ($prefixes as $prefix => $defaultID) {
			if (WURFL_Handlers_Utils::checkIfStartsWith($userAgent, $prefix)) {
				// If RIS returns a non-generic match, return it, else, return the default
				$tolerance = strlen($prefix);
				$deviceID = $this->getDeviceIDFromRIS($userAgent, $tolerance);
				
				if ($deviceID == WURFL_Constants::NO_MATCH) {
					return $defaultID;
				}
				return $deviceID;
			}
		}
		
		return WURFL_Constants::NO_MATCH;
	}
	
	public function applyRecoveryMatch($userAgent) {
		return 'generic_opera_mini_android_version5';
	}
}
