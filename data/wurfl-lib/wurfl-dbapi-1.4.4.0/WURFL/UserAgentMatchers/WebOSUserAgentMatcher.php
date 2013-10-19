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
class WebOSUserAgentMatcher extends UserAgentMatcher {
	
	public $runtime_normalization = true;
	
	public static $constantIDs = array(
		'hp_tablet_webos_generic',
		'hp_webos_generic',
	);
	
	public static function canHandle(TeraWurflHttpRequest $httpRequest) {
		if ($httpRequest->isDesktopBrowser()) return false;
		return ($httpRequest->user_agent->contains('webOS') || $httpRequest->user_agent->contains('hpwOS'));
	}
	
	public function applyConclusiveMatch() {
		$model = $this->getWebOSModelVersion($this->userAgent);
		$os_ver = $this->getWebOSVersion($this->userAgent);
		if ($model !== null && $os_ver !== null) {
			$prefix = $model.' '.$os_ver.WurflConstants::RIS_DELIMITER;
			$this->userAgent->set($prefix.$this->userAgent);
			return $this->risMatch(strlen($prefix));
		}
		return WurflConstants::NO_MATCH;
	}
	
	public function applyRecoveryMatch(){
		return $this->userAgent->contains('hpwOS/3')? 'hp_tablet_webos_generic': 'hp_webos_generic';
	}
	
	public function getWebOSModelVersion($ua) {
		/* Formats:
		 * Mozilla/5.0 (hp-tablet; Linux; hpwOS/3.0.5; U; es-US) AppleWebKit/534.6 (KHTML, like Gecko) wOSBrowser/234.83 Safari/534.6 TouchPad/1.0
		 * Mozilla/5.0 (Linux; webOS/2.2.4; U; de-DE) AppleWebKit/534.6 (KHTML, like Gecko) webOSBrowser/221.56 Safari/534.6 Pre/3.0
		 * Mozilla/5.0 (webOS/1.4.0; U; en-US) AppleWebKit/532.2 (KHTML, like Gecko) Version/1.0 Safari/532.2 Pre/1.0
		 */
		if (preg_match('# ([^/]+)/([\d\.]+)$#', $ua, $matches)) {
			return $matches[1].' '.$matches[2];
		} else {
			return null;
		}
	}
	
	public function getWebOSVersion($ua) {
		if (preg_match('#(?:hpw|web)OS.(\d)\.#', $ua, $matches)) {
			return 'webOS'.$matches[1];
		} else {
			return null;
		}
	}
}
