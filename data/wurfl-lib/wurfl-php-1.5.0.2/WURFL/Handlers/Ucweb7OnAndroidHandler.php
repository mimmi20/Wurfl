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
 * Ucweb7OnAndroidUserAgentHandler
 * 
 *
 * @category   WURFL
 * @package	WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license	GNU Affero General Public License
 * @version	$id$
 */
class WURFL_Handlers_Ucweb7OnAndroidHandler extends WURFL_Handlers_Handler {
	
	protected $prefix = "UCWEB7ONANDROID";
	
	public static $constantIDs = array(
		'generic_android_ver1_6_ucweb',
		'generic_android_ver2_0_ucweb',
		'generic_android_ver2_1_ucweb',
		'generic_android_ver2_2_ucweb',
		'generic_android_ver2_3_ucweb',
	);
	
	public function canHandle($userAgent) {
		if (WURFL_Handlers_Utils::isDesktopBrowser($userAgent)) return false;
		return WURFL_Handlers_Utils::checkIfContainsAll($userAgent, array('Android', 'UCWEB7'));
	}
	
	public function applyConclusiveMatch($userAgent) {
		// The tolerance is after UCWEB7, not before
		$find = 'UCWEB7';
		$tolerance = WURFL_Handlers_Utils::indexOfOrLength($userAgent, $find) + strlen($find);
		if ($tolerance > strlen($userAgent)) {
			$tolerance = strlen($userAgent);
		}
		$this->getDeviceIDFromRIS($userAgent, $tolerance);
	}
	
	public function applyRecoveryMatch($userAgent) {
		$android_version_string = str_replace('.', '_', WURFL_Handlers_AndroidHandler::getAndroidVersion($userAgent));
		$deviceID = 'generic_android_ver'.$android_version_string.'_ucweb';
		if (in_array($deviceID, self::$constantIDs)) {
			return $deviceID;
		} else {
			return 'generic_android_ver2_0_ucweb';
		}
	}
}
