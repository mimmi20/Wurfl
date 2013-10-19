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
class OperaUserAgentMatcher extends UserAgentMatcher {
	
	public $runtime_normalization = true;
	
	public static $constantIDs = array(
		'opera',
		'opera_7',
		'opera_8',
		'opera_9',
		'opera_10',
		'opera_11',
		'opera_12',
	);
	
	public static function canHandle(TeraWurflHttpRequest $httpRequest) {
		if ($httpRequest->isMobileBrowser()) return false;
		return $httpRequest->user_agent->contains('Opera');
	}
	
	/**
	 * @var string Opera version is stored here for performance
	 */
	protected $opera_version;
	public function applyConclusiveMatch() {
		// Repair Opera user agents using fake version 9.80
		// Normalize: Opera/9.80 (X11; Linux x86_64; U; sv) Presto/2.9.168 Version/11.50
		// Into:      Opera/11.50 (X11; Linux x86_64; U; sv) Presto/2.9.168 Version/11.50
		if ($this->userAgent->startsWith('Opera/9.80')) {
			if (preg_match('#Version/(\d+\.\d+)#', $this->userAgent, $matches)) {
				$this->userAgent->set(str_replace('Opera/9.80', 'Opera/'.$matches[1], $this->userAgent));
				$this->opera_version = $matches[1];
			}
			// Match to the '.' in the Opera version number
			return $this->risMatch($this->userAgent->indexOf('.'));
		}
		$opera_idx = $this->userAgent->indexOf('Opera');
		$tolerance = $this->userAgent->indexOfOrLength('.', $opera_idx);
		return $this->risMatch($tolerance);
	}
	public function applyRecoveryMatch() {
		if ($this->opera_version === null) {
			if (preg_match('#Opera[ /]?(\d+\.\d+)#', $this->userAgent, $matches)) {
				$this->opera_version = $matches[1];
			} else {
				return 'opera';
			}
		}
		$major_version = floor($this->opera_version);
		$id = 'opera_' . $major_version;
		if (in_array($id, self::$constantIDs)) return $id;
		return 'opera';
	}
}