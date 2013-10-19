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
 * SonyEricssonUserAgentHandler
 *
 *
 * @category   WURFL
 * @package	WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license	GNU Affero General Public License
 * @version	$id$
 */
class WURFL_Handlers_SonyEricssonHandler extends WURFL_Handlers_Handler {
	
	protected $prefix = "SONY_ERICSSON";
	
	public function canHandle($userAgent) {
		if (WURFL_Handlers_Utils::isDesktopBrowser($userAgent)) return false;
		return WURFL_Handlers_Utils::checkIfContains($userAgent, 'Sony');
	}
	
	public function applyConclusiveMatch($userAgent) {
		if (WURFL_Handlers_Utils::checkIfStartsWith($userAgent, 'SonyEricsson')) {
			$tolerance = WURFL_Handlers_Utils::firstSlash($userAgent) - 1;
			return $this->getDeviceIDFromRIS($userAgent, $tolerance);
		}
		$tolerance = WURFL_Handlers_Utils::secondSlash($userAgent);
		return $this->getDeviceIDFromRIS($userAgent, $tolerance);
	}
}
