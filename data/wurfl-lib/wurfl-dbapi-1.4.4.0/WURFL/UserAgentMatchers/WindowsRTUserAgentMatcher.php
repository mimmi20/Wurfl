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
class WindowsRTUserAgentMatcher extends UserAgentMatcher {
	
	public static $constantIDs = array(
		'generic_windows_8_rt',
	);
	
	public static function canHandle(TeraWurflHttpRequest $httpRequest) {
		return $httpRequest->user_agent->contains('Windows NT 6.2') && $httpRequest->user_agent->contains(' ARM;');
	}
	
	public function applyConclusiveMatch() {
		// Example Windows 8 RT MSIE 10 UA:
		// Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.2; ARM; Trident/6.0; Touch)
		//                                                        ^ RIS Tolerance
		$search = ' ARM;';
		$idx = strpos($this->userAgent, $search);
		if ($idx !== false) {
			// Match to the end of the search string
			return $this->risMatch($idx + strlen($search));
		}
		return WurflConstants::NO_MATCH;
	}
	
	public function applyRecoveryMatch() {
		return 'generic_windows_8_rt';
	}
}
