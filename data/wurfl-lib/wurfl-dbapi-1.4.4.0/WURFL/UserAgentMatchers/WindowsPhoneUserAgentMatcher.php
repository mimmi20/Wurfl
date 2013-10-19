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
class WindowsPhoneUserAgentMatcher extends UserAgentMatcher {
	
	public $runtime_normalization = true;
	
	public static $constantIDs = array(
		'generic_ms_winmo6_5',
		'generic_ms_phone_os7',
		'generic_ms_phone_os7_5',
		'generic_ms_phone_os8',
	);
	
	public static function canHandle(TeraWurflHttpRequest $httpRequest) {
		if ($httpRequest->isDesktopBrowser()) return false;
		return $httpRequest->user_agent->contains('Windows Phone');
	}
	
	public function applyConclusiveMatch() {
		$model = self::getWindowsPhoneModel($this->userAgent);
		$version = self::getWindowsPhoneVersion($this->userAgent);
		
		if ($model !== null && $version !== null) {
			// "WP" is for Windows Phone
			$prefix = 'WP'.$version.' '.$model.WurflConstants::RIS_DELIMITER;
			$this->userAgent->set($prefix.$this->userAgent);
			return $this->risMatch(strlen($prefix));
		}
		
		return WurflConstants::NO_MATCH;
	}
	public function applyRecoveryMatch() {
		if ($this->userAgent->contains('Windows Phone 8')) return 'generic_ms_phone_os8';
		if ($this->userAgent->contains('Windows Phone OS 7.5')) return 'generic_ms_phone_os7_5';
		if ($this->userAgent->contains('Windows Phone OS 7.0')) return 'generic_ms_phone_os7';
		if ($this->userAgent->contains('Windows Phone 6.5')) return 'generic_ms_winmo6_5';
		return WurflConstants::NO_MATCH;
	}
	
	public static function getWindowsPhoneModel($ua) {
		// This regex is relatively fast because there is not much backtracking, and almost all UAs will match
		if (preg_match('|IEMobile/\d+\.\d+;(?: ARM;)?(?: Touch;)? ?([^;\)]+(; ?[^;\)]+)?)|', $ua, $matches)) {
			$model = $matches[1];

			// Some UAs contain "_blocked" and that string causes matching errors:
			//   Mozilla/4.0 (compatible; MSIE 7.0; Windows Phone OS 7.5; Trident/3.1; IEMobile/7.0; LG_blocked; LG-E900)
			//   Mozilla/5.0 (compatible; MSIE 9.0; Windows Phone OS 7.5; Trident/5.0; IEMobile/9.0; SAMSUNG_blocked_blocked_blocked_blocked; SGH-i937)
			//   Mozilla/5.0 (compatible; MSIE 9.0; Windows Phone OS 7.5; Trident/5.0; IEMobile/9.0; SAMSUNG_blocked_blocked; SGH-i917)
			//   Mozilla/5.0 (compatible; MSIE 9.0; Windows Phone OS 7.5; Trident/5.0; IEMobile/9.0; SAMSUNG_blocked_blocked; SGH-i937)
			//   Mozilla/5.0 (compatible; MSIE 9.0; Windows Phone OS 7.5; Trident/5.0; IEMobile/9.0; SAMSUNG_blocked; OMNIA7)
			//   Mozilla/5.0 (compatible; MSIE 9.0; Windows Phone OS 7.5; Trident/5.0; IEMobile/9.0; SAMSUNG_blocked; SGH-i917)
			$model = str_replace('_blocked', '', $model);

			// Nokia Windows Phone 7.5/8 "RM-" devices make matching particularly difficult:
			//   Mozilla/5.0 (compatible; MSIE 9.0; Windows Phone OS 7.5; Trident/5.0; IEMobile/9.0; NOKIA; RM-821_eu_euro1)
			//   Mozilla/5.0 (compatible; MSIE 9.0; Windows Phone OS 7.5; Trident/5.0; IEMobile/9.0; NOKIA; RM-821_eu_euro2_248)
			//   Mozilla/5.0 (compatible; MSIE 9.0; Windows Phone OS 7.5; Trident/5.0; IEMobile/9.0; NOKIA; RM-824_nam_att_100)
			//   Mozilla/5.0 (compatible; MSIE 10.0; Windows Phone 8.0; Trident/6.0; IEMobile/10.0; ARM; Touch; NOKIA; RM-821_eu_euro1_276)
			//   Mozilla/5.0 (compatible; MSIE 10.0; Windows Phone 8.0; Trident/6.0; IEMobile/10.0; ARM; Touch; NOKIA; RM-821_eu_euro1_292)
			//   Mozilla/5.0 (compatible; MSIE 10.0; Windows Phone 8.0; Trident/6.0; IEMobile/10.0; ARM; Touch; NOKIA; RM-821_eu_euro2_224)
			//   Mozilla/5.0 (compatible; MSIE 10.0; Windows Phone 8.0; Trident/6.0; IEMobile/10.0; ARM; Touch; NOKIA; RM-821_eu_euro2_248)
			//   Mozilla/5.0 (compatible; MSIE 10.0; Windows Phone 8.0; Trident/6.0; IEMobile/10.0; ARM; Touch; NOKIA; RM-821_eu_sweden_235)
			$model = preg_replace('/NOKIA; (RM-.+?)_.*/', '$1', $model, 1);
			
			return $model;
		}
		return null;
	}
	
	public static function getWindowsPhoneVersion($ua) {
		if (preg_match('|Windows Phone(?: OS)? (\d+\.\d+)|', $ua, $matches)) {
			return $matches[1];
		}
		return null;
	}
}