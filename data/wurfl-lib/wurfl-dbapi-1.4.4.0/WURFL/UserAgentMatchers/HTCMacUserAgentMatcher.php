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
class HTCMacUserAgentMatcher extends UserAgentMatcher {
	
	public $runtime_normalization = true;
	
	public static $constantIDs = array(
		'generic_android_htc_disguised_as_mac',
	);
	
	public static function canHandle(TeraWurflHttpRequest $httpRequest) {
		// Causes it to return false due to "Macintosh" in UA 
		//if ($httpRequest->isDesktopBrowser()) return false;
		return $httpRequest->user_agent->startsWith('Mozilla/5.0 (Macintosh') && $httpRequest->user_agent->contains('HTC');
	}
	
	public function applyConclusiveMatch() {
		$model = self::getHTCMacModel($this->userAgent, false);
		if ($model !== null) {
			$prefix = $model.WurflConstants::RIS_DELIMITER;
			$this->userAgent->set($prefix.$this->userAgent);
			return $this->risMatch(strlen($prefix));
		}
		return WurflConstants::NO_MATCH;
	}
	
	public function applyRecoveryMatch() {
		return 'generic_android_htc_disguised_as_mac';
	}
	
	public static function getHTCMacModel($ua) {
		if (preg_match('/(HTC[^;\)]+)/', $ua, $matches)) {
			$model = preg_replace('#[ _\-/]#', '~', $matches[1]);
			return $model;
		}
		return null;
	}
}
