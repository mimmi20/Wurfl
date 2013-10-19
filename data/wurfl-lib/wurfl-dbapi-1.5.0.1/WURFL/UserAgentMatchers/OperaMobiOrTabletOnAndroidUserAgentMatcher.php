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
class OperaMobiOrTabletOnAndroidUserAgentMatcher extends UserAgentMatcher {
	
	public $runtime_normalization = true;
	
	public static $constantIDs = array(
		'generic_android_ver1_5_opera_mobi',
		'generic_android_ver1_6_opera_mobi',
		'generic_android_ver2_0_opera_mobi',
		'generic_android_ver2_1_opera_mobi',
		'generic_android_ver2_2_opera_mobi',
		'generic_android_ver2_3_opera_mobi',
		'generic_android_ver4_0_opera_mobi',
		'generic_android_ver4_1_opera_mobi',
		'generic_android_ver4_2_opera_mobi',
	
		'generic_android_ver2_1_opera_tablet',
		'generic_android_ver2_2_opera_tablet',
		'generic_android_ver2_3_opera_tablet',
		'generic_android_ver3_0_opera_tablet',
		'generic_android_ver3_1_opera_tablet',
		'generic_android_ver3_2_opera_tablet',
		'generic_android_ver4_0_opera_tablet',
		'generic_android_ver4_1_opera_tablet',
		'generic_android_ver4_2_opera_tablet',
	);
	
	public static function canHandle(TeraWurflHttpRequest $httpRequest) {
		if ($httpRequest->isDesktopBrowser()) return false;
		return $httpRequest->user_agent->contains('Android') && $httpRequest->user_agent->contains(array('Opera Mobi', 'Opera Tablet'));
	}
	
	public function applyConclusiveMatch() {
		$is_opera_mobi = $this->userAgent->contains('Opera Mobi');
		$is_opera_tablet = $this->userAgent->contains('Opera Tablet');
		if ($is_opera_mobi || $is_opera_tablet) {
			$opera_version = self::getOperaOnAndroidVersion($this->userAgent, false);
			$android_version = AndroidUserAgentMatcher::getAndroidVersion($this->userAgent, false);
			if ($opera_version !== null && $android_version !== null) {
				$opera_model = $is_opera_tablet? 'Opera Tablet': 'Opera Mobi';
				$prefix = $opera_model.' '.$opera_version.' Android '.$android_version.WurflConstants::RIS_DELIMITER;
				$this->userAgent->set($prefix.$this->userAgent);
				return $this->risMatch(strlen($prefix));
			}
		}
		return WurflConstants::NO_MATCH;
	}	
	
	public function applyRecoveryMatch() {
		$is_opera_mobi = $this->userAgent->contains('Opera Mobi');
		$is_opera_tablet = $this->userAgent->contains('Opera Tablet');
		if ($is_opera_mobi || $is_opera_tablet) {
			$android_version = AndroidUserAgentMatcher::getAndroidVersion($this->userAgent);
			$android_version_string = str_replace('.', '_', $android_version);
			$type = $is_opera_tablet? 'tablet': 'mobi';
			$deviceID = 'generic_android_ver'.$android_version_string.'_opera_'.$type;
			if (in_array($deviceID, self::$constantIDs)) {
				return $deviceID;
			} else {
				return $is_opera_tablet? 'generic_android_ver2_1_opera_tablet': 'generic_android_ver2_0_opera_mobi';
			}
		}
				
		return WurflConstants::NO_MATCH;
	}
	
	const OPERA_DEFAULT_VERSION = '10';
	
	public static $validOperaVersions = array('10', '11', '12');
	/**
	 * Get the Opera browser version from an Opera Android user agent
	 * @param string $ua User Agent
	 * @param boolean $use_default Return the default version on fail, else return null
	 * @return string Opera version
	 * @see self::$defaultOperaVersion
	 */
	public static function getOperaOnAndroidVersion($ua, $use_default=true) {
		if (preg_match('/Version\/(\d\d)/', $ua, $matches)) {
			$version = $matches[1];
			if (in_array($version, self::$validOperaVersions)) {
				return $version;
			}
		}
		return $use_default? self::OPERA_DEFAULT_VERSION: null;
	}
}