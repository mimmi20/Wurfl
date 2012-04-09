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
 * HTCMacUserAgentHandler
 * 
 *
 * @category   WURFL
 * @package	WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license	GNU Affero General Public License
 * @version	$id$
 */
class WURFL_Handlers_HTCMacHandler extends WURFL_Handlers_Handler {
	
	protected $prefix = "HTCMAC";	
	
	public static $constantIDs = array(
		'generic_android_htc_disguised_as_mac',
	);
	
	public function canHandle($userAgent) {
		return WURFL_Handlers_Utils::checkIfStartsWith($userAgent, 'Mozilla/5.0 (Macintosh') && WURFL_Handlers_Utils::checkIfContains($userAgent, 'HTC');
	}
	
	public function applyConclusiveMatch($userAgent) {
		$delimiter_idx = strpos($userAgent, WURFL_Constants::RIS_DELIMITER);
		if ($delimiter_idx !== false) {
			$tolerance = $delimiter_idx + strlen(WURFL_Constants::RIS_DELIMITER);
			return $this->getDeviceIDFromRIS($userAgent, $tolerance);
		}
		return WURFL_Constants::NO_MATCH;
	}
	
	public function applyRecoveryMatch($userAgent) {
		return 'generic_android_htc_disguised_as_mac';
	}
	
	public static function getHTCMacModel($ua) {
		if (preg_match('/(HTC[^;\)]+)/', $ua, $matches)) {
			$model = preg_replace('#[ _\-/]#', '~', $matches[1]);
			return $model;
		}
		return null;
	}
	
}
