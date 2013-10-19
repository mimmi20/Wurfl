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
 * MSIEAgentHandler
 *
 *
 * @category   WURFL
 * @package	WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license	GNU Affero General Public License
 * @version	$id$
 */
class WURFL_Handlers_MSIEHandler extends WURFL_Handlers_Handler {
	
	protected $prefix = "MSIE";
	
	public static $constantIDs = array(
		'msie',
		'msie_4',
		'msie_5',
		'msie_5_5',
		'msie_6',
		'msie_7',
		'msie_8',
		'msie_9',
	);
	
	public function canHandle($userAgent) {
		if (WURFL_Handlers_Utils::isMobileBrowser($userAgent)) return false;
		if (WURFL_Handlers_Utils::checkIfContainsAnyOf($userAgent, array('Opera', 'armv', 'MOTO', 'BREW'))) return false;
		return WURFL_Handlers_Utils::checkIfStartsWith($userAgent, 'Mozilla') && WURFL_Handlers_Utils::checkIfContains($userAgent, 'MSIE');
	}
	
	public function applyConclusiveMatch($userAgent) {
		$matches = array();
		if(preg_match('/^Mozilla\/4\.0 \(compatible; MSIE (\d)\.(\d);/', $userAgent, $matches)){
			if (WURFL_Configuration_ConfigHolder::getWURFLConfig()->isHighPerformance()) {
				return WURFL_Constants::GENERIC_WEB_BROWSER;
			}
			switch($matches[1]){
				// cases are intentionally out of sequence for performance
				case 7:
					return 'msie_7';
					break;
				case 8:
					return 'msie_8';
					break;
				case 9:
					return 'msie_9';
					break;
				case 6:
					return 'msie_6';
					break;
				case 4:
					return 'msie_4';
					break;
				case 5:
					return ($matches[2]==5)? 'msie_5_5': 'msie_5';
					break;
				default:
					return 'msie';
					break;
			}
		}
		$tolerance = WURFL_Handlers_Utils::firstSlash($userAgent);
		return $this->getDeviceIDFromRIS($userAgent, $tolerance);
	}
}