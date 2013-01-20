<?php
/**
 * Copyright (c) 2012 ScientiaMobile, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Refer to the COPYING.txt file distributed with this package.
 *
 *
 * @category   WURFL
 * @package	WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license	GNU Affero General Public License
 * @version	$id$
 */

/**
 * AndroidUserAgentHandler
 * 
 *
 * @category   WURFL
 * @package	WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license	GNU Affero General Public License
 * @version	$id$
 */
class WURFL_Handlers_AndroidHandler extends WURFL_Handlers_Handler {
	
	protected $prefix = "ANDROID";
	
	public static $constantIDs = array(
		'generic_android',
		'generic_android_ver1_5',
		'generic_android_ver1_6',
		'generic_android_ver2',
		'generic_android_ver2_1',
		'generic_android_ver2_2',
		'generic_android_ver2_3',
		'generic_android_ver3_0',
		'generic_android_ver3_1',
		'generic_android_ver3_2',
		'generic_android_ver3_3',
		'generic_android_ver4',
		'generic_android_ver4_1',
		'generic_android_ver4_2',
		'generic_android_ver4_3',
		'generic_android_ver5_0',
		
		'uabait_opera_mini_android_v50',
		'uabait_opera_mini_android_v51',
		'generic_opera_mini_android_version5',
	
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
		
		'generic_android_ver2_0_fennec',
		'generic_android_ver2_0_fennec_tablet',
		'generic_android_ver2_0_fennec_desktop',
		
		'generic_android_ver1_6_ucweb',
		'generic_android_ver2_0_ucweb',
		'generic_android_ver2_1_ucweb',
		'generic_android_ver2_2_ucweb',
		'generic_android_ver2_3_ucweb',
	
		'generic_android_ver2_0_netfrontlifebrowser',
		'generic_android_ver2_1_netfrontlifebrowser',
		'generic_android_ver2_2_netfrontlifebrowser',
		'generic_android_ver2_3_netfrontlifebrowser',
	);
	
	public function canHandle($userAgent) {
		if (WURFL_Handlers_Utils::isDesktopBrowser($userAgent)) return false;
		return WURFL_Handlers_Utils::checkIfContains($userAgent, 'Android');
	}
	
	public function applyConclusiveMatch($userAgent) {
		// Look for RIS delimited UAs first
		$delimiter_idx = strpos($userAgent, WURFL_Constants::RIS_DELIMITER);
		if ($delimiter_idx !== false) {
			$tolerance = $delimiter_idx + strlen(WURFL_Constants::RIS_DELIMITER);
			return $this->getDeviceIDFromRIS($userAgent, $tolerance);
		}
		
		// Opera Mini
		if (WURFL_Handlers_Utils::checkIfContains($userAgent, 'Opera Mini')) {
			if (WURFL_Handlers_Utils::checkIfContains($userAgent, ' Build/')) {
				$tolerance = WURFL_Handlers_Utils::indexOfOrLength($userAgent, ' Build/');
				return $this->getDeviceIDFromRIS($userAgent, $tolerance);
			}
			$prefixes = array(
				'Opera/9.80 (J2ME/MIDP; Opera Mini/5' => 'uabait_opera_mini_android_v50',
				'Opera/9.80 (Android; Opera Mini/5.0' => 'uabait_opera_mini_android_v50',
				'Opera/9.80 (Android; Opera Mini/5.1' => 'uabait_opera_mini_android_v51',
			);
			foreach ($prefixes as $prefix => $defaultID) {
				if (WURFL_Handlers_Utils::checkIfStartsWith($userAgent, $prefix)) {
					return $this->getDeviceIDFromRIS($userAgent, strlen($prefix));
				}
			}
		}
		
		// Fennec
		if (WURFL_Handlers_Utils::checkIfContainsAnyOf($userAgent, array('Fennec', 'Firefox'))) {
			$tolerance = WURFL_Handlers_Utils::indexOfOrLength($userAgent, ')');
			return $this->getDeviceIDFromRIS($userAgent, $tolerance);
		}
		
		// UCWEB7
		if (WURFL_Handlers_Utils::checkIfContains($userAgent, 'UCWEB7')) {
			// The tolerance is after UCWEB7, not before
			$find = 'UCWEB7';
			$tolerance = strpos($userAgent, $find) + strlen($find);
			if ($tolerance > strlen($userAgent)) {
				$tolerance = strlen($userAgent);
			}
			return $this->getDeviceIDFromRIS($userAgent, $tolerance);
		}
		
		// NetFrontLifeBrowser
		if (WURFL_Handlers_Utils::checkIfContains($userAgent, 'NetFrontLifeBrowser/2.2')) {
			$find = 'NetFrontLifeBrowser/2.2';
			$tolerance = strpos($userAgent, $find) + strlen($find);
			if ($tolerance > strlen($userAgent)) {
				$tolerance = strlen($userAgent);
			}
			return $this->getDeviceIDFromRIS($userAgent, $tolerance);
		}
		
		// Standard RIS Matching
		$tolerance = min(WURFL_Handlers_Utils::indexOfOrLength($userAgent, ' Build/'), WURFL_Handlers_Utils::indexOfOrLength($userAgent, ' AppleWebKit'));
		return $this->getDeviceIDFromRIS($userAgent, $tolerance);
	}

	public function applyRecoveryMatch($userAgent) {
		// Opera Mini
		if (WURFL_Handlers_Utils::checkIfContains($userAgent, 'Opera Mini')) {
			return 'generic_opera_mini_android_version5';
		}
		
		// Opera Mobi/Tablet
		$is_opera_mobi = WURFL_Handlers_Utils::checkIfContains($userAgent, 'Opera Mobi');
		$is_opera_tablet = WURFL_Handlers_Utils::checkIfContains($userAgent, 'Opera Tablet');
		if ($is_opera_mobi || $is_opera_tablet) {
			$android_version = self::getAndroidVersion($userAgent);
			$android_version_string = str_replace('.', '_', $android_version);
			$type = $is_opera_tablet? 'tablet': 'mobi';
			$deviceID = 'generic_android_ver'.$android_version_string.'_opera_'.$type;
			if (in_array($deviceID, self::$constantIDs)) {
				return $deviceID;
			} else {
				return $is_opera_tablet? 'generic_android_ver2_1_opera_tablet': 'generic_android_ver2_0_opera_mobi';
			}
		}
		
		// UCWEB7
		if (WURFL_Handlers_Utils::checkIfContains($userAgent, 'UCWEB7')) {
			$android_version_string = str_replace('.', '_', self::getAndroidVersion($userAgent));
			$deviceID = 'generic_android_ver'.$android_version_string.'_ucweb';
			if (in_array($deviceID, self::$constantIDs)) {
				return $deviceID;
			} else {
				return 'generic_android_ver2_0_ucweb';
			}
		}
		
		// Fennec
		$is_fennec = WURFL_Handlers_Utils::checkIfContains($userAgent, 'Fennec');
		$is_firefox = WURFL_Handlers_Utils::checkIfContains($userAgent, 'Firefox');
		if ($is_fennec || $is_firefox) {
			if ($is_fennec || WURFL_Handlers_Utils::checkIfContains($userAgent, 'Mobile')) return 'generic_android_ver2_0_fennec';
			if ($is_firefox) {
				if (WURFL_Handlers_Utils::checkIfContains($userAgent, 'Tablet')) return 'generic_android_ver2_0_fennec_tablet';
				if (WURFL_Handlers_Utils::checkIfContains($userAgent, 'Desktop')) return 'generic_android_ver2_0_fennec_desktop';
				return WURFL_Constants::NO_MATCH;
			}
		}
		
		// NetFrontLifeBrowser
		if (WURFL_Handlers_Utils::checkIfContains($userAgent, 'NetFrontLifeBrowser')) {
			// generic_android_ver2_0_netfrontlifebrowser
			$android_version_string = str_replace('.', '_', self::getAndroidVersion($userAgent));
			$deviceID = 'generic_android_ver'.$android_version_string.'_netfrontlifebrowser';
			if (in_array($deviceID, self::$constantIDs)) {
				return $deviceID;
			} else {
				return 'generic_android_ver2_0_netfrontlifebrowser';
			}
		}
		
		// Generic Android
		if (WURFL_Handlers_Utils::checkIfContains($userAgent, 'Froyo')){
			return 'generic_android_ver2_2';
		}
		$version_string = str_replace('.', '_', self::getAndroidVersion($userAgent));
		$deviceID = 'generic_android_ver'.$version_string;
		if ($deviceID == 'generic_android_ver2_0') $deviceID = 'generic_android_ver2';
		if ($deviceID == 'generic_android_ver4_0') $deviceID = 'generic_android_ver4';
		if (in_array($deviceID, self::$constantIDs)) {
			return $deviceID;
		}
		
		return 'generic_android';
	}
	
	/********* Android Utility Functions ***********/
	const ANDROID_DEFAULT_VERSION = 2.0;
	
	public static $validAndroidVersions = array('1.0', '1.5', '1.6', '2.0', '2.1', '2.2', '2.3', '2.4', '3.0', '3.1', '3.2', '3.3', '4.0', '4.1', '4.2', '4.3', '5.0');
	public static $androidReleaseMap = array(
		'Cupcake' => '1.5',
		'Donut' => '1.6',
		'Eclair' => '2.1',
		'Froyo' => '2.2',
		'Gingerbread' => '2.3',
		'Honeycomb' => '3.0',
		'Ice Cream Sandwich' => '4.0',
		'Jelly Bean' => '4.1', // Note: 4.2 is also Jelly Bean
		'Key Lime Pie' => '5.0',
	);
	/**
	 * Get the Android version from the User Agent, or the default Android version is it cannot be determined
	 * @param string $ua User Agent
	 * @param boolean $use_default Return the default version on fail, else return null
	 * @return string Android version
	 * @see self::$defaultAndroidVersion
	 */
	public static function getAndroidVersion($ua, $use_default=true) {
		// Replace Android version names with their numbers
		// ex: Froyo => 2.2
		$ua = str_replace(array_keys(self::$androidReleaseMap), array_values(self::$androidReleaseMap), $ua);
		if (preg_match('/Android (\d\.\d)/', $ua, $matches)) {
			$version = $matches[1];
			if (in_array($version, self::$validAndroidVersions)) {
				return $version;
			}
		}
		return $use_default? self::ANDROID_DEFAULT_VERSION: null;
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
	
	public static function getAndroidModel($ua, $use_default=true) {
		if (!preg_match('#Android [^;]+;(?: xx-xx;)? (.+?) Build/#', $ua, $matches)) {
			return null;
		}
		// Trim off spaces and semicolons
		$model = rtrim($matches[1], ' ;');
		// The previous RegEx may return just "Build/.*" for UAs like:
		// HTC_Dream Mozilla/5.0 (Linux; U; Android 1.5; xx-xx; Build/CUPCAKE) AppleWebKit/528.5+ (KHTML, like Gecko) Version/3.1.2 Mobile Safari/525.20.1
		if (strpos('Build/', $model) === 0) {
			return null;
		}
		
		// HTC
		if (strpos($model, 'HTC') !== false) {
			// Normalize "HTC/"
			$model = preg_replace('#HTC[ _\-/]#', 'HTC~', $model);
			// Remove the version
			$model = preg_replace('#(/| V?[\d\.]).*$#', '', $model);
			$model = preg_replace('#/.*$#', '', $model);
		}
		// Samsung
		$model = preg_replace('#(SAMSUNG[^/]+)/.*$#', '$1', $model);
		// Orange
		$model = preg_replace('#ORANGE/.*$#', 'ORANGE', $model);
		// LG
		$model = preg_replace('#(LG-[^/]+)/[vV].*$#', '$1', $model);
		// Serial Number
		$model = preg_replace('#\[[\d]{10}\]#', '', $model);
		
		return trim($model);
	}
	
}
