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
 * WindowsPhoneUserAgentHandler
 * 
 *
 * @category   WURFL
 * @package	WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license	GNU Affero General Public License
 * @version	$id$
 */
class WURFL_Handlers_WindowsPhoneHandler extends WURFL_Handlers_Handler {
	
	protected $prefix = "WINDOWSPHONE";
	
	public static $constantIDs = array(
		'generic_ms_winmo6_5',
		'generic_ms_phone_os7',
		'generic_ms_phone_os7_5',
	);
	
	public function canHandle($userAgent) {
		if (WURFL_Handlers_Utils::isDesktopBrowser($userAgent)) return false;
		return WURFL_Handlers_Utils::checkIfContains($userAgent, 'Windows Phone');
	}
	
	public function applyConclusiveMatch($userAgent) {
		// Exact and Recovery match only
		return WURFL_Constants::NO_MATCH;
	}
	
	public function applyRecoveryMatch($userAgent){
		if (WURFL_Handlers_Utils::checkIfContains($userAgent, 'Windows Phone 6.5')) return 'generic_ms_winmo6_5';
		if (WURFL_Handlers_Utils::checkIfContains($userAgent, 'Windows Phone OS 7.0')) return 'generic_ms_phone_os7';
		if (WURFL_Handlers_Utils::checkIfContains($userAgent, 'Windows Phone OS 7.5')) return 'generic_ms_phone_os7_5';
		return WURFL_Constants::NO_MATCH;
	}
}