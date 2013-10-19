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
class OperaMiniUserAgentMatcher extends UserAgentMatcher {
	
	public static $constantIDs = array(
		'Opera Mini/1' => 'generic_opera_mini_version1',
        'Opera Mini/2' => 'generic_opera_mini_version2',
        'Opera Mini/3' => 'generic_opera_mini_version3',
        'Opera Mini/4' => 'generic_opera_mini_version4',
        'Opera Mini/5' => 'generic_opera_mini_version5',
	);
	
	public static function canHandle(TeraWurflHttpRequest $httpRequest) {
		if ($httpRequest->isDesktopBrowser()) return false;
		return $httpRequest->user_agent->contains(array('Opera Mini', 'Opera Mobi'));
	}
	
	public function applyConclusiveMatch() {
		return $this->risMatch($this->userAgent->firstSlash());
	}
	public function applyRecoveryMatch(){
		foreach (self::$constantIDs as $keyword => $device_id) {
			if ($this->userAgent->contains($keyword)) {
				return $device_id;
			}
		}
		if ($this->userAgent->contains('Opera Mobi')) {
			return 'generic_opera_mini_version4';
		}
		return 'generic_opera_mini_version1';
	}
}
