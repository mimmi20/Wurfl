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
 * WindowsPhoneDesktopUserAgentHandler
 * 
 *
 * @category   WURFL
 * @package	WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license	GNU Affero General Public License
 * @version	$id$
 */
class WURFL_Handlers_WindowsPhoneDesktopHandler extends WURFL_Handlers_Handler {
	
	protected $prefix = "WINDOWSPHONEDESKTOP";
	
	public static $constantIDs = array(
		'generic_ms_phone_os7_desktopmode',
		'generic_ms_phone_os7_5_desktopmode',
	);
	
	public function canHandle($userAgent) {
		return WURFL_Handlers_Utils::checkIfContains($userAgent, 'ZuneWP7');
	}
	
	public function applyConclusiveMatch($userAgent) {
		// Exact and Recovery match only
		return WURFL_Constants::NO_MATCH;
	}
	
	public function applyRecoveryMatch($userAgent){
		if (WURFL_Handlers_Utils::checkIfContains($userAgent, 'Trident/5.0')) return 'generic_ms_phone_os7_5_desktopmode';
		return 'generic_ms_phone_os7_desktopmode';
	}
}