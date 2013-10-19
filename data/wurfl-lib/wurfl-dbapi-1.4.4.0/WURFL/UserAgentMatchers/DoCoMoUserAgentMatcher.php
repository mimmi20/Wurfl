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
class DoCoMoUserAgentMatcher extends UserAgentMatcher {
	
	public static $constantIDs = array(
		'docomo_generic_jap_ver1',	
		'docomo_generic_jap_ver2',
	);
	
	public static function canHandle(TeraWurflHttpRequest $httpRequest) {
		if ($httpRequest->isDesktopBrowser()) return false;
		return $httpRequest->user_agent->startsWith('DoCoMo');
	}
	
	public function applyConclusiveMatch() {
		if ($this->userAgent->numSlashes() >= 2) {
			$tolerance = $this->userAgent->secondSlash();
		} else {
			//  DoCoMo/2.0 F01A(c100;TB;W24H17)
			$tolerance = $this->userAgent->firstOpenParen();
		}
		return $this->risMatch($tolerance);
	}
	
	public function applyRecoveryMatch() {
		$versionIndex = 7;
		$version = $this->userAgent->normalized[$versionIndex];
		return ($version == '2')? 'docomo_generic_jap_ver2': 'docomo_generic_jap_ver1';
	}
}

