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
class HTCUserAgentMatcher extends UserAgentMatcher {
	
	public static $constantIDs = array(
		'generic_ms_mobile',
	);
	
	public static function canHandle(TeraWurflHttpRequest $httpRequest) {
		if ($httpRequest->isDesktopBrowser()) return false;
		return $httpRequest->user_agent->contains(array('HTC', 'XV6875'));
	}
	
	public function applyConclusiveMatch() {
		if (preg_match('#^.*?HTC.+?[/ ;]#', $this->userAgent, $matches)) {
			// The length of the complete match (from the beginning) is the tolerance
			$tolerance = strlen($matches[0]);
		} else {
			$tolerance = strlen($this->userAgent);
		}
		
		return $this->risMatch($tolerance);
	}
	
	public function applyRecoveryMatch() {
		if ($this->userAgent->contains('Windows CE;')) {
			return 'generic_ms_mobile';
		}
		return $this->risMatch(6);
	}
}
