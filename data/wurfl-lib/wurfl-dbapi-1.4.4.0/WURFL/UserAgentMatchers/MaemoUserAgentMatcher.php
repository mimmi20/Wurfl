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
class MaemoUserAgentMatcher extends UserAgentMatcher {
	
	public static $constantIDs = array(
		'generic_opera_mobi_maemo',
		'nokia_generic_maemo_with_firefox',
		'nokia_generic_maemo',
	);
	
	public $runtime_normalization = true;

	public static function canHandle(TeraWurflHttpRequest $httpRequest) {
		return $httpRequest->user_agent->contains('Maemo');
	}

	public function applyConclusiveMatch() {
		$model = self::getMaemoModel($this->userAgent);
		if ($model) {
			$prefix = 'Maemo '.$model.WurflConstants::RIS_DELIMITER;
			$this->userAgent->set($prefix.$this->userAgent);
			return $this->risMatch(strlen($prefix));
		}
		
		return $this->ldMatch(7);
	}

	public function applyRecoveryMatch(){
		if ($this->userAgent->contains('Opera Mobi')) {
			return 'generic_opera_mobi_maemo';
		}
		if ($this->userAgent->contains('Firefox')) {
			return 'nokia_generic_maemo_with_firefox';
		}
		return 'nokia_generic_maemo';
	}

	public static function getMaemoModel($ua) {
		if (preg_match('/Maemo [bB]rowser [\d\.]+ (.+)/', $ua, $matches)) {
			$model = $matches[1];
			$idx = strpos($model, ' GTB');
			if ($idx !== false) {
				$model = substr($model, 0, $idx);
			}
			return $model;
		}
		return null;
	}
}
