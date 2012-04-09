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
 * LGPLUSUserAgentHandler
 *
 *
 * @category   WURFL
 * @package	WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license	GNU Affero General Public License
 * @version	$id$
 */
class WURFL_Handlers_LGUPLUSHandler extends WURFL_Handlers_Handler {

	protected $prefix = "LGUPLUS";

	public static $constantIDs = array(
		'generic_lguplus_rexos_facebook_browser',
		'generic_lguplus_rexos_webviewer_browser',
		'generic_lguplus_winmo_facebook_browser',
		'generic_lguplus_android_webkit_browser',
	);

	public function canHandle($userAgent) {
		if (WURFL_Handlers_Utils::isDesktopBrowser($userAgent)) return false;
		return WURFL_Handlers_Utils::checkIfContainsAnyOf($userAgent, array("LGUPLUS", "lgtelecom"));
	}

	public function applyConclusiveMatch($userAgent) {
		return WURFL_Constants::NO_MATCH;
	}


	private $lgupluses = array(
		"generic_lguplus_rexos_facebook_browser" => array("Windows NT 5", "POLARIS"),
		"generic_lguplus_rexos_webviewer_browser" => array("Windows NT 5"),
		"generic_lguplus_winmo_facebook_browser" => array("Windows CE", "POLARIS"),
		"generic_lguplus_android_webkit_browser" => array("Android", "AppleWebKit")
	);

	public function applyRecoveryMatch($userAgent) {
		foreach($this->lgupluses as $deviceId => $values) {
			if(WURFL_Handlers_Utils::checkIfContainsAll($userAgent, $values)) {
				return $deviceId;
			}
		}
		return null;
	}
}