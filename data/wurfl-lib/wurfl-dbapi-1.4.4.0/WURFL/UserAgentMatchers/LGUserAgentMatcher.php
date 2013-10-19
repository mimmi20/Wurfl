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
class LGUserAgentMatcher extends UserAgentMatcher {
	
	public static function canHandle(TeraWurflHttpRequest $httpRequest) {
		if ($httpRequest->isDesktopBrowser()) return false;
		return $httpRequest->user_agent->iStartsWith('lg');
	}
	
	public function applyConclusiveMatch() {
		$tolerance = $this->userAgent->indexOfOrLength('/', $this->userAgent->indexOf('LG'));
		return $this->risMatch($tolerance);
	}
	
	public function applyRecoveryMatch() {
		return $this->risMatch(7);
	}
}
