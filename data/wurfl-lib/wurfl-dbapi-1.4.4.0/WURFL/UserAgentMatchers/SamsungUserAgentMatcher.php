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
class SamsungUserAgentMatcher extends UserAgentMatcher {
	
	public static function canHandle(TeraWurflHttpRequest $httpRequest) {
		if ($httpRequest->isDesktopBrowser()) return false;
		return ($httpRequest->user_agent->iContains('samsung') ||
				$httpRequest->user_agent->startsWith(array('SEC-', 'SPH', 'SGH', 'SCH')));
	}
	
	public function applyConclusiveMatch() {
		if ($this->userAgent->startsWith(array('SAMSUNG-', 'SEC-', 'SCH'))) {
			$tolerance = $this->userAgent->firstSlash();
		} elseif ($this->userAgent->startsWith(array('Samsung', 'SPH', 'SGH'))) {
			$tolerance = $this->userAgent->firstSpace();
		} else {
			$tolerance = $this->userAgent->secondSlash();
		}
		return $this->risMatch($tolerance);
	}
	
	public function applyRecoveryMatch() {
		if ($this->userAgent->startsWith('SAMSUNG')) {
			$tolerance = 8;
			return $this->ldMatch($tolerance);
		} else {
			$tolerance = $this->userAgent->indexOfOrLength('/', $this->userAgent->indexOf('Samsung'));
			return $this->risMatch($tolerance);
		}
	}
}
