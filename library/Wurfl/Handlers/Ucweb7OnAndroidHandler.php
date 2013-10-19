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
 *
 * @category   WURFL
 * @package	WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license	GNU Affero General Public License
 * @version	$id$
 */

use \Wurfl\Constants;

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
class Ucweb7OnAndroidHandler extends Handler
{
	protected $prefix = "UCWEB7ONANDROID";
	
	public static $constantIDs = array(
		'generic_android_ver1_6_ucweb',
		'generic_android_ver2_0_ucweb',
		'generic_android_ver2_1_ucweb',
		'generic_android_ver2_2_ucweb',
		'generic_android_ver2_3_ucweb',
	);
	
	public function canHandle($userAgent) {
		if (Utils::isDesktopBrowser($userAgent)) return false;
		return Utils::checkIfContainsAll($userAgent, array('Android', 'UCWEB7'));
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
		$android_version_string = str_replace('.', '_', AndroidHandler::getAndroidVersion($userAgent));
		$deviceID = 'generic_android_ver'.$android_version_string.'_ucweb';
		if (in_array($deviceID, self::$constantIDs)) {
			return $deviceID;
		} else {
			return 'generic_android_ver2_0_ucweb';
		}
	}
}
