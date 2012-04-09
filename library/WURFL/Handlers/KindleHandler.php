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
 * KindleUserAgentHandler
 * 
 *
 * @category   WURFL
 * @package	WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license	GNU Affero General Public License
 * @version	$id$
 */
class WURFL_Handlers_KindleHandler extends WURFL_Handlers_Handler {
	
	protected $prefix = "KINDLE";
	
	public static $constantIDs = array(
		'amazon_kindle_ver1',
		'amazon_kindle2_ver1',
		'amazon_kindle3_ver1',
		'amazon_kindle_fire_ver1',
		'generic_amazon_android_kindle',
		'generic_amazon_kindle',
	);
	
	public function canHandle($userAgent) {
		return WURFL_Handlers_Utils::checkIfContainsAnyOf($userAgent, array('Kindle', 'Silk'));
	}
	
	public function applyConclusiveMatch($userAgent) {
		$search = 'Kindle/';
		$idx = strpos($userAgent, $search);
		if ($idx !== false) {
			// Version/4.0 Kindle/3.0 (screen 600x800; rotate) Mozilla/5.0 (Linux; U; zh-cn.utf8) AppleWebKit/528.5+ (KHTML, like Gecko, Safari/528.5+)
			//		$idx ^	  ^ $tolerance
			$tolerance = $idx + strlen($search) + 1;
			$kindle_version = $userAgent[$tolerance];
			// RIS match only Kindle/1-3
			if ($kindle_version >= 1 && $kindle_version <= 3) {
				return $this->getDeviceIDFromRIS($userAgent, $tolerance);
			}
		}
		$delimiter_idx = strpos($userAgent, WURFL_Constants::RIS_DELIMITER);
		if ($delimiter_idx !== false) {
			$tolerance = $delimiter_idx + strlen(WURFL_Constants::RIS_DELIMITER);
			return $this->getDeviceIDFromRIS($userAgent, $tolerance);
		}
		
		return WURFL_Constants::NO_MATCH;
	}
	
	public function applyRecoveryMatch($userAgent){
		if (WURFL_Handlers_Utils::checkIfContains($userAgent, 'Kindle/1')) return 'amazon_kindle_ver1';
		if (WURFL_Handlers_Utils::checkIfContains($userAgent, 'Kindle/2')) return 'amazon_kindle2_ver1';
		if (WURFL_Handlers_Utils::checkIfContains($userAgent, 'Kindle/3')) return 'amazon_kindle3_ver1';
		if (WURFL_Handlers_Utils::checkIfContainsAnyOf($userAgent, array('Kindle Fire', 'Silk'))) return 'amazon_kindle_fire_ver1';
		return 'generic_amazon_kindle';
	}
}
