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
class SkyfireUserAgentMatcher extends UserAgentMatcher {
	
	public static $constantIDs = array(
		'generic_skyfire_version1',
		'generic_skyfire_version2',
	);
	
	public static function canHandle(TeraWurflHttpRequest $httpRequest) {
		// Note, this would also work, but it would break caching: 
		// $httpRequest->getHeader('HTTP_X_REQUESTED_WITH') == "com.skyfire.browser"
		return $httpRequest->user_agent->contains('Skyfire');
	}
	
	public function applyConclusiveMatch() {
		$skyfire_idx = $this->userAgent->indexOf('Skyfire');
		// Matches the first decimal point after the Skyfire keyword: Skyfire/2.0
		return $this->risMatch($this->userAgent->indexOfOrLength('.', $skyfire_idx));
	}
	
	public function applyRecoveryMatch() {
		if ($this->userAgent->contains('Skyfire/2.')) {
			return 'generic_skyfire_version2';
		}
		return 'generic_skyfire_version1';
	}
}
