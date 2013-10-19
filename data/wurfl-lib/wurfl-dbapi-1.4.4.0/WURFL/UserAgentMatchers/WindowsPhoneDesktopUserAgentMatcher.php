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
class WindowsPhoneDesktopUserAgentMatcher extends UserAgentMatcher {
	
	public static $constantIDs = array(
		'generic_ms_phone_os7_desktopmode',
		'generic_ms_phone_os7_5_desktopmode',
	);
	
	public static function canHandle(TeraWurflHttpRequest $httpRequest) {
		return $httpRequest->user_agent->contains('ZuneWP7');
	}
	
	public function applyConclusiveMatch() {
		// Exact and Recovery match only
		return WurflConstants::NO_MATCH;
	}
	public function applyRecoveryMatch() {
		if ($this->userAgent->contains('Trident/5.0')) return 'generic_ms_phone_os7_5_desktopmode';
		return 'generic_ms_phone_os7_desktopmode';
	}
}
