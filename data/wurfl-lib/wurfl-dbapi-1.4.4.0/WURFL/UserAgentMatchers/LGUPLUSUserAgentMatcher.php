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
class LGUPLUSUserAgentMatcher extends UserAgentMatcher {
	
	public static $constantIDs = array(
		'generic_lguplus_rexos_facebook_browser',
        'generic_lguplus_rexos_webviewer_browser',
        'generic_lguplus_winmo_facebook_browser',
        'generic_lguplus_android_webkit_browser',
	);
	
	public static function canHandle(TeraWurflHttpRequest $httpRequest) {
		if ($httpRequest->isDesktopBrowser()) return false;
		return $httpRequest->user_agent->contains(array('LGUPLUS', 'lgtelecom'));
	}
	
	public function applyConclusiveMatch() {
		return WurflConstants::NO_MATCH;
	}
	public function applyRecoveryMatch() {
		if ($this->userAgent->contains('Windows NT 5') && $this->userAgent->contains('POLARIS')) return 'generic_lguplus_rexos_facebook_browser';
		if ($this->userAgent->contains('Windows NT 5')) return 'generic_lguplus_rexos_webviewer_browser';
		if ($this->userAgent->contains('Windows CE') && $this->userAgent->contains('POLARIS')) return 'generic_lguplus_winmo_facebook_browser';
		if ($this->userAgent->contains('Android') && $this->userAgent->contains('AppleWebKit')) return 'generic_lguplus_android_webkit_browser';
		return WurflConstants::NO_MATCH;
	}
}
