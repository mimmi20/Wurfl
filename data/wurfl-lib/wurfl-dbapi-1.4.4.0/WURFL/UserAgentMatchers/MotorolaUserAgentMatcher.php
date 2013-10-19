<?php
/**
 * Copyright (c) 2011 ScientiaMobile, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Refer to the COPYING.txt file distributed with this package.
 *
 * @package    WURFL_UserAgentMatcher
 * @copyright  ScientiaMobile, Inc.
 * @author     Steve Kamerman <steve AT scientiamobile.com>
 * @license    GNU Affero General Public License
 * @version    $id$
 */
/**
 * Provides a specific user agent matching technique
 * @package TeraWurflUserAgentMatchers
 */
class MotorolaUserAgentMatcher extends UserAgentMatcher {
	
	public static $constantIDs = array(
		'mot_mib22_generic',
	);
	
	public static function canHandle(TeraWurflHttpRequest $httpRequest) {
		if ($httpRequest->isDesktopBrowser()) return false;
		return ($httpRequest->user_agent->startsWith(array('Mot-', 'MOT-', 'MOTO', 'moto')) ||
				$httpRequest->user_agent->contains('Motorola'));
	}
	
	public function applyConclusiveMatch() {
		if ($this->userAgent->startsWith(array('Mot-', 'MOT-', 'Motorola'))) {
			return $this->risMatch($this->userAgent->firstSlash());
		}
		return $this->ldMatch(5);
	}
	public function applyRecoveryMatch(){
		if ($this->userAgent->contains(array('MIB/2.2', 'MIB/BER2.2'))) {
			return 'mot_mib22_generic';
		}
		return WurflConstants::NO_MATCH;
	}
}
