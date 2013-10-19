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
 * OperaHandler
 *
 *
 * @category   WURFL
 * @package	WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license	GNU Affero General Public License
 * @version	$id$
 */
class WURFL_Handlers_OperaHandler extends WURFL_Handlers_Handler {
	
	protected $prefix = "OPERA";
	
	public static $constantIDs = array(
		'opera',
		'opera_7',
		'opera_8',
		'opera_9',
		'opera_10',
		'opera_11',
		'opera_12',
	);
	
	public function canHandle($userAgent) {
		if (WURFL_Handlers_Utils::isMobileBrowser($userAgent)) return false;
		return WURFL_Handlers_Utils::checkIfContains($userAgent, 'Opera');
	}
	
	public function applyConclusiveMatch($userAgent) {
		$opera_idx = strpos($userAgent, 'Opera');
		$tolerance = WURFL_Handlers_Utils::indexOfOrLength($userAgent, '.', $opera_idx);
		return $this->getDeviceIDFromRIS($userAgent, $tolerance);
	}
	
	public function applyRecoveryMatch($userAgent) {
		$opera_version = self::getOperaVersion($userAgent);
		if ($opera_version === null) {
			return 'opera';
		}
		$major_version = floor($opera_version);
		$id = 'opera_' . $major_version;
		if (in_array($id, self::$constantIDs)) return $id;
		return 'opera';
	}
	
	public static function getOperaVersion($userAgent) {
		if (preg_match('#Opera[ /]?(\d+\.\d+)#', $userAgent, $matches)) {
			return($matches[1]);
		}
		return null;
	}
}