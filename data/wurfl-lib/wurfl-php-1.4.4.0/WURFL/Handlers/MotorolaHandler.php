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
 * MotorolaUserAgentHandler
 *
 *
 * @category   WURFL
 * @package	WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license	GNU Affero General Public License
 * @version	$id$
 */
class WURFL_Handlers_MotorolaHandler extends WURFL_Handlers_Handler {
	
	protected $prefix = "MOTOROLA";
	
	public static $constantIDs = array(
		'mot_mib22_generic',
	);
	
	public function canHandle($userAgent) {
		if (WURFL_Handlers_Utils::isDesktopBrowser($userAgent)) return false;
		return (WURFL_Handlers_Utils::checkIfStartsWithAnyOf($userAgent, array('Mot-', 'MOT-', 'MOTO', 'moto')) ||
			WURFL_Handlers_Utils::checkIfContains($userAgent, 'Motorola'));	
	}
	
	public function applyConclusiveMatch($userAgent) {
		if (WURFL_Handlers_Utils::checkIfStartsWithAnyOf($userAgent, array('Mot-', 'MOT-', 'Motorola'))) {
			return $this->getDeviceIDFromRIS($userAgent, WURFL_Handlers_Utils::firstSlash($userAgent));
		}
		return $this->getDeviceIDFromLD($userAgent, 5);
	}
	
	public function applyRecoveryMatch($userAgent) {
		if (WURFL_Handlers_Utils::checkIfContainsAnyOf($userAgent, array('MIB/2.2', 'MIB/BER2.2'))) {
			return "mot_mib22_generic";
		}
		return null;
	}
}