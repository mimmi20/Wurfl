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
class KindleUserAgentMatcher extends UserAgentMatcher {
	
	public $runtime_normalization = true;
	
	public static $constantIDs = array(
		'amazon_kindle_ver1',
		'amazon_kindle2_ver1',
		'amazon_kindle3_ver1',
		'amazon_kindle_fire_ver1',
		'generic_amazon_android_kindle',
		'generic_amazon_kindle',
	);
	
	public static function canHandle(TeraWurflHttpRequest $httpRequest) {
		return $httpRequest->user_agent->contains(array('Kindle', 'Silk'));
	}
	
	public function applyConclusiveMatch() {
		// Mobile-mode Kindle Fire
		if ($this->userAgent->contains('Android')) {
			$model = AndroidUserAgentMatcher::getAndroidModel($this->userAgent, false);
			$version = AndroidUserAgentMatcher::getAndroidVersion($this->userAgent, false);
			if ($model !== null && $version !== null) {
				$prefix = $version.' '.$model.WurflConstants::RIS_DELIMITER;
				$this->userAgent->set($prefix.$this->userAgent);
				return $this->risMatch(strlen($prefix));
			} else {
				
				$search = 'Silk/';
				$idx = strpos($this->userAgent, $search);
				if ($idx !== false) {
					// The model will be null for Kindle Fire 1st Gen Silk in mobile mode:
					// Mozilla/5.0 (Linux; U; Android 2.3.4; en-us; Silk/1.0.13.328_10008910) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1 Silk-Accelerated=true
					
					// Currently, only the original Kindle Fire in Mobile Mode sends an Android UA with the Silk keyword and without model information
					$tolerance = $idx + strlen($search) + 1;
					return $this->risMatch($tolerance);
				}
			}
		}
		
		// Desktop-mode Kindle Fire
		// Kindle Fire 2nd Gen Desktop Mode has no android version (even though "Build/I...." tells us it's ICS):
		// Mozilla/5.0 (Linux; U; en-us; KFOT Build/IML74K) AppleWebKit/535.19 (KHTML, like Gecko) Silk/2.0 Safari/535.19 Silk-Accelerated=false
		$idx = strpos($this->userAgent, 'Build/');
		if ($idx !== false) {
			return $this->risMatch($idx);
		}
		
		// Kindle e-reader
		$search = 'Kindle/';
		$idx = strpos($this->userAgent, $search);
		if ($idx !== false) {
			// Version/4.0 Kindle/3.0 (screen 600x800; rotate) Mozilla/5.0 (Linux; U; zh-cn.utf8) AppleWebKit/528.5+ (KHTML, like Gecko, Safari/528.5+)
			//        $idx ^      ^ $tolerance
			$tolerance = $idx + strlen($search) + 1;
			$kindle_version = $this->userAgent->normalized[$tolerance];
			// RIS match only Kindle/1-3
			if ($kindle_version >= 1 && $kindle_version <= 3) {
				return $this->risMatch($tolerance);
			}
		}
		
		// PlayStation Vita
		$search = 'PlayStation Vita';
		$idx = strpos($this->userAgent, $search);
		if ($idx !== false) {
			return $this->risMatch($idx + strlen($search) + 1);
		}
		
		return WurflConstants::NO_MATCH;
	}
	
	public function applyRecoveryMatch() {
		$map = array(
			'Kindle/1' => 'amazon_kindle_ver1',
			'Kindle/2' => 'amazon_kindle2_ver1',
			'Kindle/3' => 'amazon_kindle3_ver1',
			'Kindle Fire' => 'amazon_kindle_fire_ver1',
			'Silk' => 'amazon_kindle_fire_ver1',
		);
		foreach ($map as $keyword => $id) {
			if ($this->userAgent->contains($keyword)) return $id;
		}
		return 'generic_amazon_kindle';
	}
}
