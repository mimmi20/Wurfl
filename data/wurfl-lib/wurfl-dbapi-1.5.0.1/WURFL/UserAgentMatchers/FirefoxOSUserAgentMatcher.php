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
class FirefoxOSUserAgentMatcher extends UserAgentMatcher {
	
	public static $constantIDs = array(
		'generic_firefox_os',
		'firefox_os_ver1',
		'firefox_os_ver1_1',
	);
	
	public static function canHandle(TeraWurflHttpRequest $httpRequest) {
		return ($httpRequest->user_agent->contains('Firefox/') && $httpRequest->user_agent->contains('Mobile'));
	}
	
	public function applyConclusiveMatch() {
		// Mozilla/5.0 (Mobile; rv:18.0) Gecko/18.0 Firefox/18.0
		if (preg_match('#\brv:\d+\.\d+(.)#', $this->userAgent, $matches, PREG_OFFSET_CAPTURE)) {
			$tolerance = $matches[1][1] + 1;
			return $this->risMatch($tolerance);
		}	
		return WurflConstants::NO_MATCH;
	}
	
	public function applyRecoveryMatch() {
		if (preg_match('#\brv:(\d+\.\d+)#', $this->userAgent, $matches)) {
			if ($matches[1] > 18.0) {
				return 'firefox_os_ver1_1';
			} else {
				return 'firefox_os_ver1';
			} 
		}
		return 'generic_firefox_os';
	}
}