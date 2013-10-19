<?php
/**
 * Copyright (c) 2013 ScientiaMobile, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Refer to the COPYING.txt file distributed with this package.
 *
 * @package    WURFL_UserAgentMatcher
 * @copyright  ScientiaMobile, Inc.
 * @author     Steve Kamerman <steve AT scientiamobile.com>
 * @license    GNU Affero General Public License
 * @version    $id$
 */
/**
 * Provides a specific user agent matching technique
 * @package TeraWurflUserAgentMatchers
 */
class Ucweb7OnAndroidUserAgentMatcher extends UserAgentMatcher {
	
	public static $constantIDs = array(
		'generic_android_ver1_6_ucweb',
		'generic_android_ver2_0_ucweb',
		'generic_android_ver2_1_ucweb',
		'generic_android_ver2_2_ucweb',
		'generic_android_ver2_3_ucweb',
	);
	
	public static function canHandle(TeraWurflHttpRequest $httpRequest) {
		if ($httpRequest->isDesktopBrowser()) return false;
		return $httpRequest->user_agent->contains('Android') && $httpRequest->user_agent->contains('UCWEB7');
	}
	
	public function applyConclusiveMatch() {
		// The tolerance is after UCWEB7, not before
		$find = 'UCWEB7';
		$tolerance = $this->userAgent->indexOf($find) + strlen($find);
		if ($tolerance > $this->userAgent->length()) {
			$tolerance = $this->userAgent->length();
		}
		return $this->risMatch($tolerance);
	}
	
	public function applyRecoveryMatch(){
		$android_version_string = str_replace('.', '_', AndroidUserAgentMatcher::getAndroidVersion($this->userAgent));
		$deviceID = 'generic_android_ver'.$android_version_string.'_ucweb';
		if (in_array($deviceID, self::$constantIDs)) {
			return $deviceID;
		} else {
			return 'generic_android_ver2_0_ucweb';
		}
	}
}