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
class SmartTVUserAgentMatcher extends UserAgentMatcher {
	
	public static $constantIDs = array(
		'generic_smarttv_browser',
		'generic_smarttv_googletv_browser',
		'generic_smarttv_appletv_browser',
		'generic_smarttv_boxeebox_browser',
	);
	
	public static function canHandle(TeraWurflHttpRequest $httpRequest) {
		return $httpRequest->isSmartTV();
	}
	
	public function applyConclusiveMatch() {
		$tolerance = $this->userAgent->length();
		return $this->risMatch($tolerance);
	}
	
	public function applyRecoveryMatch(){
		if ($this->userAgent->contains('SmartTV')) return 'generic_smarttv_browser';
		if ($this->userAgent->contains('GoogleTV')) return 'generic_smarttv_googletv_browser';
		if ($this->userAgent->contains('AppleTV')) return 'generic_smarttv_appletv_browser';
		if ($this->userAgent->contains('Boxee')) return 'generic_smarttv_boxeebox_browser';
		return 'generic_smarttv_browser';
	}
}
