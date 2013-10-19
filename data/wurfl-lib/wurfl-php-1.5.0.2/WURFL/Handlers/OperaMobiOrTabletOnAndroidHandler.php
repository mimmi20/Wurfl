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
 * OperaMobiOrTabletOnAndroidUserAgentHandler
 * 
 *
 * @category   WURFL
 * @package	WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license	GNU Affero General Public License
 * @version	$id$
 */
class WURFL_Handlers_OperaMobiOrTabletOnAndroidHandler extends WURFL_Handlers_Handler {
	
	protected $prefix = "OPERAMOBIORTABLETONANDROID";
	
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
	
	public function canHandle($userAgent) {
		if (WURFL_Handlers_Utils::isDesktopBrowser($userAgent)) return false;
		return (WURFL_Handlers_Utils::checkIfContains($userAgent, 'Android') && WURFL_Handlers_Utils::checkIfContainsAnyOf($userAgent, array('Opera Mobi', 'Opera Tablet')));
	}
	
	public function applyConclusiveMatch($userAgent) {
		$tolerance = WURFL_Handlers_Utils::toleranceToRisDelimeter($userAgent);
		if ($tolerance !== false) {
			return $this->getDeviceIDFromRIS($userAgent, $tolerance);
		}
		
		return WURFL_Constants::NO_MATCH;
	}
	
	public function applyRecoveryMatch($userAgent) {
		$is_opera_mobi = WURFL_Handlers_Utils::checkIfContains($userAgent, 'Opera Mobi');
		$is_opera_tablet = WURFL_Handlers_Utils::checkIfContains($userAgent, 'Opera Tablet');
		if ($is_opera_mobi || $is_opera_tablet) {
			$android_version = WURFL_Handlers_AndroidHandler::getAndroidVersion($userAgent);
			$android_version_string = str_replace('.', '_', $android_version);
			$type = $is_opera_tablet? 'tablet': 'mobi';
			$deviceID = 'generic_android_ver'.$android_version_string.'_opera_'.$type;
			if (in_array($deviceID, self::$constantIDs)) {
				return $deviceID;
			} else {
				return $is_opera_tablet? 'generic_android_ver2_1_opera_tablet': 'generic_android_ver2_0_opera_mobi';
			}
		}
		
		return WURFL_Constants::NO_MATCH;
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
