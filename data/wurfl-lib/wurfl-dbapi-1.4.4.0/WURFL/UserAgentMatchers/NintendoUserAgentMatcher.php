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
class NintendoUserAgentMatcher extends UserAgentMatcher {
	
	public static $constantIDs = array(
		'nintendo_wii_ver1',
		'nintendo_dsi_ver1',
		'nintendo_ds_ver1',
	);
	
	public static function canHandle(TeraWurflHttpRequest $httpRequest) {
		if ($httpRequest->isDesktopBrowser()) return false;
		if ($httpRequest->user_agent->contains('Nintendo')) return true;
		// Nintendo DS: Mozilla/4.0 (compatible; MSIE 6.0; Nitro) Opera 8.50 [en]
		return ($httpRequest->user_agent->startsWith('Mozilla/') && $httpRequest->user_agent->contains('Nitro') && $httpRequest->user_agent->contains('Opera'));
	}
	
	public function applyConclusiveMatch() {
		return $this->ldMatch();
	}
	
	public function applyRecoveryMatch(){
		if ($this->userAgent->contains('Nintendo Wii')) return 'nintendo_wii_ver1';
		if ($this->userAgent->contains('Nintendo DSi')) return 'nintendo_dsi_ver1';
		if (($this->userAgent->startsWith('Mozilla/') && $this->userAgent->contains('Nitro') && $this->userAgent->contains('Opera'))) {
			return 'nintendo_ds_ver1';
		}
		return 'nintendo_wii_ver1';
	}
}
