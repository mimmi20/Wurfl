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
class FennecOnAndroidUserAgentMatcher extends UserAgentMatcher {
	
	public static $constantIDs = array(
		'generic_android_ver2_0_fennec',
		'generic_android_ver2_0_fennec_tablet',
		'generic_android_ver2_0_fennec_desktop',
	);
	
	public static function canHandle(TeraWurflHttpRequest $httpRequest) {
		if ($httpRequest->isDesktopBrowser()) return false;
		return $httpRequest->user_agent->contains('Android') && $httpRequest->user_agent->contains(array('Fennec', 'Firefox'));
	}
	
	public function applyConclusiveMatch() {
		// Captures the index of the first decimal point in the Firefox verison "rv:nn.nn.nn"
		// Example:
		//   Mozilla/5.0 (Android; Tablet; rv:17.0) Gecko/17.0 Firefox/17.0
		//   Mozilla/5.0 (Android; Tablet; rv:17.
		if (preg_match('|^.+?\(.+?rv:\d+(\.)|', $this->userAgent, $matches, PREG_OFFSET_CAPTURE)) {
			return $this->risMatch($matches[1][1] + 1);
		}
		return WurflConstants::NO_MATCH;
	}
	
	public function applyRecoveryMatch() {
		$is_fennec = $this->userAgent->contains('Fennec');
		$is_firefox = $this->userAgent->contains('Firefox');
		if ($is_fennec || $is_firefox) {
			if ($is_fennec || $this->userAgent->contains('Mobile')) return 'generic_android_ver2_0_fennec';
			if ($is_firefox) {
				if ($this->userAgent->contains('Tablet')) return 'generic_android_ver2_0_fennec_tablet';
				if ($this->userAgent->contains('Desktop')) return 'generic_android_ver2_0_fennec_desktop';
				return WurflConstants::NO_MATCH;
			}
		}
		return WurflConstants::NO_MATCH;
	}
}