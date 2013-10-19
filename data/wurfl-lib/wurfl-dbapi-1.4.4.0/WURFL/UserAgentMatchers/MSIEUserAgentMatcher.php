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
class MSIEUserAgentMatcher extends UserAgentMatcher {
	
	public $runtime_normalization = true;
	
	public static $constantIDs = array(
		'msie',
		'msie_4',
		'msie_5',
		'msie_5_5',
		'msie_6',
		'msie_7',
		'msie_8',
		'msie_9',
		'msie_10',
	);
	
	
	public static function canHandle(TeraWurflHttpRequest $httpRequest) {
		if ($httpRequest->isMobileBrowser()) return false;
		return ($httpRequest->user_agent->startsWith('Mozilla') && $httpRequest->user_agent->contains('MSIE')
				&& !$httpRequest->user_agent->contains(array('Opera', 'armv', 'MOTO', 'BREW')));
	}
	
	public function applyConclusiveMatch() {
		$matches = array();
		if(preg_match('/^Mozilla\/[45]\.0 \(compatible; MSIE (\d+)\.(\d+);/', $this->userAgent, $matches)){
			switch((int)$matches[1]){
				// cases are intentionally out of sequence for performance
				case 10:
					return 'msie_10';
					break;
				case 9:
					return 'msie_9';
					break;
				case 8:
					return 'msie_8';
					break;
				case 7:
					return 'msie_7';
					break;
				case 6:
					return 'msie_6';
					break;
				case 4:
					return 'msie_4';
					break;
				case 5:
					return ($matches[2]==5)? 'msie_5_5': 'msie_5';
					break;
				default:
					return 'msie';
					break;
			}
		}
		$this->userAgent->set(preg_replace('/( \.NET CLR [\d\.]+;?| Media Center PC [\d\.]+;?| OfficeLive[a-zA-Z0-9\.\d]+;?| InfoPath[\.\d]+;?)/', '', $this->userAgent));
		return $this->risMatch($this->userAgent->firstSlash());
	}
	public function applyRecoveryMatch(){
		if($this->userAgent->contains(array(
			'SLCC1',
			'Media Center PC',
			'.NET CLR',
			'OfficeLiveConnector'
		  ))) return WurflConstants::GENERIC_WEB_BROWSER;
		  
		return WurflConstants::NO_MATCH;
	}
}
