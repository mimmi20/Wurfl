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
 * @category   WURFL
 * @package	WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license	GNU Affero General Public License
 * @version	$id$
 */

/**
 * SafariHandler
 *
 *
 * @category   WURFL
 * @package	WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license	GNU Affero General Public License
 * @version	$id$
 */
class WURFL_Handlers_SafariHandler extends WURFL_Handlers_Handler {
	
	protected $prefix = "SAFARI";
	
	public function canHandle($userAgent) {
		if (WURFL_Handlers_Utils::isMobileBrowser($userAgent)) return false;
		return WURFL_Handlers_Utils::checkIfContains($userAgent, 'Safari') 
			&& WURFL_Handlers_Utils::checkIfStartsWithAnyOf($userAgent, array('Mozilla/5.0 (Macintosh', 'Mozilla/5.0 (Windows'));
	}
	
	public function applyConclusiveMatch($userAgent) {
		$tolerance = WURFL_Handlers_Utils::toleranceToRisDelimeter($userAgent);
		if ($tolerance !== false) {
			return $this->getDeviceIDFromRIS($userAgent, $tolerance);
		}
		
		return WURFL_Constants::NO_MATCH;
	}
	
	public function applyRecoveryMatch($userAgent){
		if (WURFL_Handlers_Utils::checkIfContainsAnyOf($userAgent, array('Macintosh', 'Windows'))) return WURFL_Constants::GENERIC_WEB_BROWSER;
		return WURFL_Constants::NO_MATCH;
	}
	
	public static function getSafariVersion($ua) {
		$search = 'Version/';
		$idx = strpos($ua, $search) + strlen($search);
		if ($idx === false) return null;
		$end_idx = strpos($ua, '.', $idx);
		if ($end_idx === false) return null;
		return substr($ua, $idx, $end_idx - $idx);
	}
}