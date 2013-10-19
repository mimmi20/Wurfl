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
class SonyEricssonUserAgentMatcher extends UserAgentMatcher {
	
	public static function canHandle(TeraWurflHttpRequest $httpRequest) {
		if ($httpRequest->isDesktopBrowser()) return false;
		return $httpRequest->user_agent->contains('Sony');
	}
	
	public function applyConclusiveMatch() {
		// firstSlash() - 1 because some UAs have revisions that aren't getting detected properly:
		// SonyEricssonW995a/R1FA Browser/NetFront/3.4 Profile/MIDP-2.1 Configuration/CLDC-1.1 JavaPlatform/JP-8.4.3
		$tolerance = $this->userAgent->firstSlash() - 1;
		if($this->userAgent->startsWith('SonyEricsson')){
			return $this->risMatch($tolerance);
		}
		$tolerance = $this->userAgent->secondSlash();
		return $this->risMatch($tolerance);
	}
}
