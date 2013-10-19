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
 * Provides a generic user agent matching technique
 * @package TeraWurflUserAgentMatchers
 */
class CatchAllUserAgentMatcher extends UserAgentMatcher {
	
	public static $constantIDs = array(
		'opwv_v72_generic',
		'opwv_v7_generic',
		'opwv_v62_generic',
		'opwv_v6_generic',
		'upgui_generic',
		'uptext_generic',
		'nokia_generic_series60',
		'generic_netfront_ver3',
		'generic_netfront_ver3_1',
		'generic_netfront_ver3_2',
		'generic_netfront_ver3_3',
		'generic_netfront_ver3_4',
		'generic_netfront_ver3_5',
		'generic_netfront_ver4_0',
		'docomo_generic_jap_ver1',
		'generic_ms_mobile',
		'apple_iphone_coremedia_ver1',
	);
	public $matcher;
	public $match_type;
	public $match = false;
	
	public function __construct(TeraWurfl $wurfl) {
		parent::__construct($wurfl);
		$this->matcher = $this->matcherName();
	}
	
	public static function canHandle(TeraWurflHttpRequest $httpRequest) {
		return true;
	}
	
	public function applyConclusiveMatch() {
		$this->match_type = 'conclusive';
		if ($this->userAgent->startsWith('Mozilla')) {
			$deviceID = $this->ldMatch(5);
			if ($deviceID != WurflConstants::NO_MATCH) $this->match = true;
			return $deviceID;
		}
		$deviceID = $this->risMatch($this->userAgent->firstSlash());
		if ($deviceID != WurflConstants::NO_MATCH) $this->match = true;
		return $deviceID;
	}
	public function applyRecoveryMatch() {
		// At this point, a recovery match is really no match at all.
		$this->match_type = 'none';
		$this->match = false;
		if (TeraWurflConfig::$SIMPLE_DESKTOP_ENGINE_ENABLE === false && 
			SimpleDesktopUserAgentMatcher::isDesktopBrowserHeavyDutyAnalysis($this->wurfl->httpRequest)) return WurflConstants::GENERIC_WEB_BROWSER;
		
		if ($this->userAgent->contains('CoreMedia')) return 'apple_iphone_coremedia_ver1';
		
		if ($this->userAgent->contains('Windows CE')) return 'generic_ms_mobile';
		
		if ($this->userAgent->contains('UP.Browser/7.2')) return 'opwv_v72_generic';
		if ($this->userAgent->contains('UP.Browser/7'))   return 'opwv_v7_generic';
		if ($this->userAgent->contains('UP.Browser/6.2')) return 'opwv_v62_generic';
		if ($this->userAgent->contains('UP.Browser/6'))   return 'opwv_v6_generic';
		if ($this->userAgent->contains('UP.Browser/5'))   return 'upgui_generic';
		if ($this->userAgent->contains('UP.Browser/4'))   return 'uptext_generic';
		if ($this->userAgent->contains('UP.Browser/3'))   return 'uptext_generic';
		
		//Series 60
		if ($this->userAgent->contains('Series60')) return 'nokia_generic_series60';
		
		// Access/Net Front
		if ($this->userAgent->contains(array('NetFront/3.0', 'ACS-NF/3.0'))) return 'generic_netfront_ver3';
		if ($this->userAgent->contains(array('NetFront/3.1', 'ACS-NF/3.1'))) return 'generic_netfront_ver3_1';
		if ($this->userAgent->contains(array('NetFront/3.2', 'ACS-NF/3.2'))) return 'generic_netfront_ver3_2';
		if ($this->userAgent->contains(array('NetFront/3.3', 'ACS-NF/3.3'))) return 'generic_netfront_ver3_3';
		if ($this->userAgent->contains('NetFront/3.4')) return 'generic_netfront_ver3_4';
		if ($this->userAgent->contains('NetFront/3.5')) return 'generic_netfront_ver3_5';
		if ($this->userAgent->contains('NetFront/4.0')) return 'generic_netfront_ver4_0';
		
		// Contains Mozilla/, but not at the beginning of the UA
		// ie: MOTORAZR V8/R601_G_80.41.17R Mozilla/4.0 (compatible; MSIE 6.0 Linux; MOTORAZR V88.50) Profile/MIDP-2.0 Configuration/CLDC-1.1 Opera 8.50[zh]
		if ($this->userAgent->indexOf('Mozilla/') > 0) return WurflConstants::GENERIC_XHTML;
		
		if ($this->userAgent->contains(array('Obigo','AU-MIC/2','AU-MIC-','AU-OBIGO/', 'Teleca Q03B1'))) {
			return WurflConstants::GENERIC_XHTML;
		}
		
		// DoCoMo
		if ($this->userAgent->startsWith(array('DoCoMo', 'KDDI'))) return 'docomo_generic_jap_ver1';
		return WurflConstants::NO_MATCH;
	}
}