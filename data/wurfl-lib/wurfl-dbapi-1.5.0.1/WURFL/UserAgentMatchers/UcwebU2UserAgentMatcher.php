<?php
/**
 * Copyright (c) 2013 ScientiaMobile, Inc.
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
class UcwebU2UserAgentMatcher extends UserAgentMatcher {

	/**
	 * This flag tells the WurflLoader that the User Agent may be permanantly
	 * altered during matching
	 * @var boolean
	 */
	public $runtime_normalization = true;

	public static $constantIDs = array(
		'generic_ucweb',
	);

	public static function canHandle(TeraWurflHttpRequest $httpRequest) {
		if ($httpRequest->isDesktopBrowser()) return false;
		return ($httpRequest->user_agent->startsWith('UCWEB') && $httpRequest->user_agent->contains('UCBrowser'));
	}

	public function applyConclusiveMatch() {
		
		$ucb_version = UcwebU3UserAgentMatcher::getUcBrowserVersion($this->userAgent);
		if ($ucb_version === null) {
			return WurflConstants::NO_MATCH;
		}
		
		//Android U2K Mobile + Tablet
		if ($this->userAgent->contains('Adr ')) {

			$model = UcwebU3UserAgentMatcher::getUcAndroidModel($this->userAgent, false);
			$version = UcwebU3UserAgentMatcher::getUcAndroidVersion($this->userAgent, false);
			if ($model !== null && $version !== null) {
				$prefix = "$version U2Android $ucb_version $model".WurflConstants::RIS_DELIMITER;
				$this->userAgent->set($prefix.$this->userAgent);
				return $this->risMatch(strlen($prefix));
			}
		}
			
		//iPhone U2K
		else if ($this->userAgent->contains('iPh OS')) {

			if (preg_match('/iPh OS (\d)_?(\d)?[ _\d]?.+; iPh(\d), ?(\d)\) U2/', $this->userAgent, $matches)) {
				$version = $matches[1].'.'.$matches[2];
				$model = $matches[3].'.'.$matches[4];
				$prefix = "$version U2iPhone $ucb_version $model".WurflConstants::RIS_DELIMITER;
				$this->userAgent->set($prefix.$this->userAgent);
				return $this->risMatch(strlen($prefix));
			}
		}

		//WP7&8 U2K
		else if ($this->userAgent->contains('wds')) {
			//Add spaces and normalize
			$ua = preg_replace('|;(?! )|', '; ', $this->userAgent);
			$this->userAgent->set($ua);
			if (preg_match('/^UCWEB.+; wds (\d+)\.([\d]+);.+; ([ A-Za-z0-9_-]+); ([ A-Za-z0-9_-]+)\) U2/', $this->userAgent, $matches)) {
				$version = $matches[1].'.'.$matches[2];
				$model = $matches[3].'.'.$matches[4];
				//Standard normalization stuff from WP matcher
				$model = str_replace('_blocked', '', $model);
				$model = preg_replace('/(NOKIA.RM-.+?)_.*/', '$1', $model, 1);
				$prefix = "$version U2WindowsPhone $ucb_version $model".WurflConstants::RIS_DELIMITER;
				$this->userAgent->set($prefix.$this->userAgent);
				return $this->risMatch(strlen($prefix));
			}
		}
			
		//Symbian U2K
		else if ($this->userAgent->contains('Symbian')) {

			if (preg_match('/^UCWEB.+; S60 V(\d); .+; (.+)\) U2/', $this->userAgent, $matches)) {
				$version = 'S60 V'.$matches[1];
				$model = $matches[2];
				$prefix = "$version U2Symbian $ucb_version $model".WurflConstants::RIS_DELIMITER;
				$this->userAgent->set($prefix.$this->userAgent);
				return $this->risMatch(strlen($prefix));
			}
		}
			
		//Java U2K - check results for regex
		else if ($this->userAgent->contains('Java')) {

			if (preg_match('/^UCWEB[^\(]+\(Java; .+; (.+)\) U2/', $this->userAgent, $matches)) {
				$version = 'Java';
				$model = $matches[1];
				$prefix = "$version U2JavaApp $ucb_version $model".WurflConstants::RIS_DELIMITER;
				$this->userAgent->set($prefix.$this->userAgent);
				return $this->risMatch(strlen($prefix));
			}
		}

		return WurflConstants::NO_MATCH;
	}


	public function applyRecoveryMatch() {
		return 'generic_ucweb';

	}

}
