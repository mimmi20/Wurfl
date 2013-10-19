<?php
/**
 * Copyright (c) 2011 ScientiaMobile, Inc.
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
class AppleUserAgentMatcher extends UserAgentMatcher {
	
	public static $constantIDs = array(
		'apple_ipod_touch_ver1',
		'apple_ipod_touch_ver2',
		'apple_ipod_touch_ver3',
		'apple_ipod_touch_ver4',
		'apple_ipod_touch_ver5',
	
		'apple_ipad_ver1',
		'apple_ipad_ver1_sub42',
		'apple_ipad_ver1_sub5',
	
		'apple_iphone_ver1',
		'apple_iphone_ver2',
		'apple_iphone_ver3',
		'apple_iphone_ver4',
		'apple_iphone_ver5',
	);
	
	public static function canHandle(TeraWurflHttpRequest $httpRequest) {
		if ($httpRequest->isDesktopBrowser()) return false;
		return ($httpRequest->user_agent->startsWith('Mozilla/5') && $httpRequest->user_agent->contains(array('iPhone', 'iPod', 'iPad')));
	}
	
	public function applyConclusiveMatch() {
		$tolerance = $this->userAgent->indexOf('_');
		if ($tolerance !== false) {
			// The first char after the first underscore
			$tolerance++;
		} else {
			$index = $this->userAgent->indexOf('like Mac OS X;');
			if ($index !== false) {
				// Step through the search string to the semicolon at the end
				$tolerance = $index + 14;
			} else {
				// Non-typical UA, try full length match
				$tolerance = $this->userAgent->length();
			}
		}
		return $this->risMatch($tolerance);
	}
	
	public function applyRecoveryMatch() {
		if (preg_match('/ (\d)_(\d)[ _]/', $this->userAgent, $matches)) {
			$major_version = (int)$matches[1];
			$minor_version = (int)$matches[2];
		} else {
			$major_version = -1;
			$minor_version = -1;
		}
		// Check iPods first since they also contain 'iPhone'
		if ($this->userAgent->contains('iPod')) {
			$deviceID = 'apple_ipod_touch_ver'.$major_version;
			if (in_array($deviceID, self::$constantIDs)) {
				return $deviceID;
			} else {
				return 'apple_ipod_touch_ver1';
			}
		} else if ($this->userAgent->contains('iPad')) {
			if ($major_version == 5) {
				return 'apple_ipad_ver1_sub5';
			} else if ($major_version == 4) {
				return 'apple_ipad_ver1_sub42';
			} else {
				return 'apple_ipad_ver1';
			}
		} else if ($this->userAgent->contains('iPhone')) {
			$deviceID = 'apple_iphone_ver'.$major_version;
			if (in_array($deviceID, self::$constantIDs)) {
				return $deviceID;
			} else {
				return 'apple_iphone_ver1';
			}
		}
		return WurflConstants::NO_MATCH;
	}
}
