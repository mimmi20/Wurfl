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
 * WindowsRTUserAgentHandler
 * 
 *
 * @category   WURFL
 * @package	WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license	GNU Affero General Public License
 * @version	$id$
 */
class WURFL_Handlers_WindowsRTHandler extends WURFL_Handlers_Handler {
	
	protected $prefix = "WINDOWSRT";
	
	public static $constantIDs = array(
		'generic_windows_8_rt',
	);
	
	public function canHandle($userAgent) {
		return WURFL_Handlers_Utils::checkIfContainsAll($userAgent, array('Windows NT 6.2', ' ARM;'));
	}
	
	public function applyConclusiveMatch($userAgent) {
		$search = ' ARM;';
		$idx = strpos($userAgent, $search);
		if ($idx !== false) {
			// Match to the end of the search string
			return $this->getDeviceIDFromRIS($userAgent, $idx + strlen($search));
		}
		return WURFL_Constants::NO_MATCH;
	}
	
	public function applyRecoveryMatch($userAgent){
		return 'generic_windows_8_rt';
	}
}