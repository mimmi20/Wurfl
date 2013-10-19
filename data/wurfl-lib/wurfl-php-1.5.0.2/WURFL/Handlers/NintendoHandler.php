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
 * NintendoUserAgentHandler
 * 
 *
 * @category   WURFL
 * @package	WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license	GNU Affero General Public License
 * @version	$id$
 */
class WURFL_Handlers_NintendoHandler extends WURFL_Handlers_Handler {
	
	protected $prefix = "NINTENDO";
	
	public static $constantIDs = array(
		'nintendo_wii_ver1',
		'nintendo_dsi_ver1',
		'nintendo_ds_ver1',
	);
	
	public function canHandle($userAgent) {
		if (WURFL_Handlers_Utils::isDesktopBrowser($userAgent)) return false;
		if (WURFL_Handlers_Utils::checkIfContains($userAgent, 'Nintendo')) return true;
		return WURFL_Handlers_Utils::checkIfStartsWith($userAgent, 'Mozilla/') && WURFL_Handlers_Utils::checkIfContainsAll($userAgent, array('Nitro', 'Opera'));
	}
	
	public function applyConclusiveMatch($userAgent) {
		return $this->getDeviceIDFromLD($userAgent);
	}
	
	public function applyRecoveryMatch($userAgent) {
		if (WURFL_Handlers_Utils::checkIfContains($userAgent, 'Nintendo Wii')) return 'nintendo_wii_ver1';
		if (WURFL_Handlers_Utils::checkIfContains($userAgent, 'Nintendo DSi')) return 'nintendo_dsi_ver1';
		if ((WURFL_Handlers_Utils::checkIfStartsWith($userAgent, 'Mozilla/') && WURFL_Handlers_Utils::checkIfContainsAll($userAgent, array('Nitro', 'Opera')))) {
			return 'nintendo_ds_ver1';
		}
		return 'nintendo_wii_ver1';
	}
}