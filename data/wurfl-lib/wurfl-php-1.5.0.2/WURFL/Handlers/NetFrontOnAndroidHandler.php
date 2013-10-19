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
 * NetFrontOnAndroidUserAgentHandler
 * 
 *
 * @category   WURFL
 * @package	WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license	GNU Affero General Public License
 * @version	$id$
 */
class WURFL_Handlers_NetFrontOnAndroidHandler extends WURFL_Handlers_Handler {
	
	protected $prefix = "NETFRONTONANDROID";
	
	public static $constantIDs = array(
		'generic_android_ver2_0_netfrontlifebrowser',
		'generic_android_ver2_1_netfrontlifebrowser',
		'generic_android_ver2_2_netfrontlifebrowser',
		'generic_android_ver2_3_netfrontlifebrowser',
	);
	
	public function canHandle($userAgent) {
		if (WURFL_Handlers_Utils::isDesktopBrowser($userAgent)) return false;
		return (WURFL_Handlers_Utils::checkIfContains($userAgent, 'Android') && WURFL_Handlers_Utils::checkIfContains($userAgent, 'NetFrontLifeBrowser/2.2'));
	}
	
	public function applyConclusiveMatch($userAgent) {
		$find = 'NetFrontLifeBrowser/2.2';
		$tolerance = strpos($userAgent, $find) + strlen($find);
		if ($tolerance > strlen($userAgent)) {
			$tolerance = strlen($userAgent);
		}
		return $this->getDeviceIDFromRIS($userAgent, $tolerance);
	}
	
	public function applyRecoveryMatch($userAgent) {
		$android_version_string = str_replace('.', '_', WURFL_Handlers_AndroidHandler::getAndroidVersion($userAgent));
		$deviceID = 'generic_android_ver'.$android_version_string.'_netfrontlifebrowser';
		if (in_array($deviceID, self::$constantIDs)) {
			return $deviceID;
		} else {
			return 'generic_android_ver2_0_netfrontlifebrowser';
		}
	}
}
