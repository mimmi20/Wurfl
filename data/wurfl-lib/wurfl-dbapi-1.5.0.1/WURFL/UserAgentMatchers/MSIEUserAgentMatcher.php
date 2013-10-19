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
class MSIEUserAgentMatcher extends UserAgentMatcher {
	
	public $runtime_normalization = true;
	
	public static $constantIDs = array(
		0     => 'msie',
		4     => 'msie_4',
		5     => 'msie_5',
		'5.5' => 'msie_5_5',
		6     => 'msie_6',
		7     => 'msie_7',
		8     => 'msie_8',
		9     => 'msie_9',
		10    => 'msie_10',
		11    => 'msie_11',
	);
	
	
	public static function canHandle(TeraWurflHttpRequest $httpRequest) {
		if ($httpRequest->isMobileBrowser()
				|| !$httpRequest->user_agent->startsWith('Mozilla')
				|| $httpRequest->user_agent->contains(array('Opera', 'armv', 'MOTO', 'BREW'))
			){
			return false;
		}
		
		// IE 11 signature
		$has_trident_rv = ($httpRequest->user_agent->contains('Trident') && $httpRequest->user_agent->contains('rv:'));
		// IE < 11 signature
		$has_msie = $httpRequest->user_agent->contains('MSIE');
		return ($has_msie || $has_trident_rv);
	}
	
	public function applyConclusiveMatch() {
		$matches = array();
		if (preg_match('/^Mozilla\/5\.0 \(.+?Trident.+?; rv:(\d\d)\.(\d+)\)/', $this->userAgent, $matches)
			|| preg_match('/^Mozilla\/[45]\.0 \(compatible; MSIE (\d+)\.(\d+);/', $this->userAgent, $matches)) {
			
			$major = (int)$matches[1];
			$minor = (int)$matches[2];
			
			// MSIE 5.5 is handled specifically
			if ($major == 5 && $minor == 5) {
				return 'msie_5_5';
			}
			
			// Look for version in constant ID array
			if (array_key_exists($major, self::$constantIDs)) {
				return self::$constantIDs[$major];
			}
		}
		$this->userAgent->set(preg_replace('/( \.NET CLR [\d\.]+;?| Media Center PC [\d\.]+;?| OfficeLive[a-zA-Z0-9\.\d]+;?| InfoPath[\.\d]+;?)/', '', $this->userAgent));
		return $this->risMatch($this->userAgent->indexOfOrLength('Trident'));
	}
	
	public function applyRecoveryMatch() {
		if ($this->userAgent->contains(array(
			'SLCC1',
			'Media Center PC',
			'.NET CLR',
			'OfficeLiveConnector'
		  ))) return WurflConstants::GENERIC_WEB_BROWSER;
		  
		return WurflConstants::NO_MATCH;
	}
}
