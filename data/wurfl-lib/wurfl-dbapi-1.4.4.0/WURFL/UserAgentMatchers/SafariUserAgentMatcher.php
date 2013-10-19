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
class SafariUserAgentMatcher extends UserAgentMatcher {

	public $runtime_normalization = true;

	public static function canHandle(TeraWurflHttpRequest $httpRequest) {
		if ($httpRequest->isMobileBrowser()) return false;
		return $httpRequest->user_agent->contains('Safari') && $httpRequest->user_agent->startsWith(array('Mozilla/5.0 (Macintosh', 'Mozilla/5.0 (Windows'));
	}

	public function applyConclusiveMatch() {
		$safari_version = self::getSafariVersion($this->userAgent);
		if ($safari_version !== null) {
			$prefix = 'Safari '.$safari_version.WurflConstants::RIS_DELIMITER;
			$this->userAgent->set($prefix.$this->userAgent);
			return $this->risMatch(strlen($prefix));
		}

		return WurflConstants::NO_MATCH;
	}

	public function applyRecoveryMatch(){
		if($this->userAgent->contains(array('Macintosh', 'Windows'))) {
			return WurflConstants::GENERIC_WEB_BROWSER;
		}
		return WurflConstants::NO_MATCH;
	}

	public static function getSafariVersion($ua) {
		$search = 'Version/';
		$idx = strpos($ua, $search) + strlen($search);
		if ($idx === false) return null;
		$end_idx = strpos($ua, '.', $idx);
		if ($end_idx === false) return null;
		return substr($ua, $idx, $end_idx - $idx);
	}
}
