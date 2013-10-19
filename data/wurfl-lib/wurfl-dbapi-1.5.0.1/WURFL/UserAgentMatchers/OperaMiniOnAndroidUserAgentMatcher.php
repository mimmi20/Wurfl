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
class OperaMiniOnAndroidUserAgentMatcher extends UserAgentMatcher {
	
	public static $constantIDs = array(
		'uabait_opera_mini_android_v50',
		'uabait_opera_mini_android_v51',
		'generic_opera_mini_android_version5',
	);
	
	public static function canHandle(TeraWurflHttpRequest $httpRequest) {
		if ($httpRequest->isDesktopBrowser()) return false;
		return $httpRequest->user_agent->contains('Android') && $httpRequest->user_agent->contains('Opera Mini');
	}
	
	public function applyConclusiveMatch() {
		if ($this->userAgent->contains(' Build/')) {
			return $this->risMatch($this->userAgent->indexOfOrLength(' Build/'));
		}
		$prefixes = array(
			'Opera/9.80 (J2ME/MIDP; Opera Mini/5' => 'uabait_opera_mini_android_v50',
			'Opera/9.80 (Android; Opera Mini/5.0' => 'uabait_opera_mini_android_v50',
			'Opera/9.80 (Android; Opera Mini/5.1' => 'uabait_opera_mini_android_v51',
		);
		foreach ($prefixes as $prefix => $defaultID) {
			if ($this->userAgent->startsWith($prefix)) {
				// If RIS returns a non-generic match, return it, else, return the default
				return $this->risMatchUAPrefix($prefix, $defaultID);
			}
		}
		return WurflConstants::NO_MATCH;
	}	
	
	public function applyRecoveryMatch(){
		return 'generic_opera_mini_android_version5';
	}
}