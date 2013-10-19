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
 * Matches desktop browsers.  This UserAgentMatcher is unlike the rest in that it is does not use any database functions to find a matching device.  If a device is not matched with this UserAgentMatcher, another one is assigned to match it using the database.
 * @package TeraWurflUserAgentMatchers
 */
class SimpleDesktopUserAgentMatcher extends UserAgentMatcher {
	
	public static function canHandle(TeraWurflHttpRequest $httpRequest) {
		return (TeraWurflConfig::$SIMPLE_DESKTOP_ENGINE_ENABLE && $httpRequest->user_agent == WurflConstants::SIMPLE_DESKTOP_UA);
	}
	
	public function applyConclusiveMatch() {
		return WurflConstants::GENERIC_WEB_BROWSER;
	}
	
	/**
	 * Is the given user agent very likely to be a desktop browser
	 * @param TeraWurflHttpRequest $httpRequest
	 * @return bool
	 */
	public static function isDesktopBrowserHeavyDutyAnalysis(TeraWurflHttpRequest $httpRequest){
		$user_agent = $httpRequest->user_agent;
		// Check UAProf
		if ($httpRequest->uaprof instanceof TeraWurflUserAgentProfile && $httpRequest->uaprof->containsValidUrl()) return false;
		// Check Smart TV keywords
		if ($user_agent->iContains(WurflConstants::$SMARTTV_BROWSERS)) return false;
		// Chrome
		if ($user_agent->contains('Chrome') && !$user_agent->contains(array('Android', 'Ventana'))) return true;
		// Check mobile keywords
		if ($user_agent->iContains(WurflConstants::$MOBILE_BROWSERS)) return false;
		if ($user_agent->contains('PPC')) return false; // PowerPC; not always mobile, but we'll kick it out of SimpleDesktop and match it in the WURFL DB
		// Firefox;  fennec is already handled in the WurflConstants::$MOBILE_BROWSERS keywords
		if ($user_agent->contains('Firefox') && !$user_agent->contains('Tablet')) return true;
		// Safari
		if ($user_agent->regexContains('#^Mozilla/5\.0 \((?:Macintosh|Windows)[^\)]+\) AppleWebKit/[\d\.]+ \(KHTML, like Gecko\) Version/[\d\.]+ Safari/[\d\.]+$#')) return true;
		// Opera Desktop
		if ($user_agent->startsWith(array('Opera/9.80 (Windows NT', 'Opera/9.80 (Macintosh'))) return true;
		// Check desktop keywords
		if ($user_agent->iContains(WurflConstants::$DESKTOP_BROWSERS)) return true;
		if ($user_agent->regexContains(array(
			// Internet Explorer 9
			'/^Mozilla\/5\.0 \(compatible; MSIE 9\.0; Windows NT \d\.\d/',
			// Internet Explorer <9
			'/^Mozilla\/4\.0 \(compatible; MSIE \d\.\d; Windows NT \d\.\d/',
		))) return true;
		return false;
	}
}
