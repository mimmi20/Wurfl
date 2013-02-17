<?php
namespace Wurfl\Handlers;

/**
 * Copyright (c) 2012 ScientiaMobile, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Refer to the COPYING.txt file distributed with this package.
 *
 *
 * @category   WURFL
 * @package	WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license	GNU Affero General Public License
 * @version	$id$
 */

use \Wurfl\Constants;

/**
 * KindleUserAgentHandler
 * 
 *
 * @category   WURFL
 * @package	WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license	GNU Affero General Public License
 * @version	$id$
 */
class KindleHandler extends Handler {
	
	protected $prefix = "KINDLE";
	
	public static $constantIDs = array(
		'amazon_kindle_ver1',
		'amazon_kindle2_ver1',
		'amazon_kindle3_ver1',
		'amazon_kindle_fire_ver1',
		'generic_amazon_android_kindle',
		'generic_amazon_kindle',
	);
	
	public function canHandle($userAgent) {
		return Utils::checkIfContainsAnyOf($userAgent, array('Kindle', 'Silk'));
	}
	
	public function applyConclusiveMatch($userAgent) {
		// Mobile-mode Kindle Fire
		if (Utils::checkIfContains($userAgent, 'Android')) {
			// UA was already restructured by the specific normalizer
			$tolerance = Utils::toleranceToRisDelimeter($userAgent);
			if ($tolerance) {
				return $this->getDeviceIDFromRIS($userAgent, $tolerance);
			} else {
				$search = 'Silk/';
				$idx = strpos($userAgent, $search);
				if ($idx !== false) {
					$tolerance = $idx + strlen($search) + 1;
					return $this->getDeviceIDFromRIS($userAgent, $tolerance);
				}
			}
		}
		
		// Desktop-mode Kindle Fire
		$idx = strpos($userAgent, 'Build/');
		if ($idx !== false) {
			return $this->getDeviceIDFromRIS($userAgent, $idx);
		}
		
		// Kindle e-reader
		$search = 'Kindle/';
		$idx = strpos($userAgent, $search);
		if ($idx !== false) {
			// Version/4.0 Kindle/3.0 (screen 600x800; rotate) Mozilla/5.0 (Linux; U; zh-cn.utf8) AppleWebKit/528.5+ (KHTML, like Gecko, Safari/528.5+)
			//		$idx ^	  ^ $tolerance
			$tolerance = $idx + strlen($search) + 1;
			$kindle_version = $userAgent[$tolerance];
			// RIS match only Kindle/1-3
			if ($kindle_version >= 1 && $kindle_version <= 3) {
				return $this->getDeviceIDFromRIS($userAgent, $tolerance);
			}
		}
		
		// PlayStation Vita
		$search = 'PlayStation Vita';
		$idx = strpos($userAgent, $search);
		if ($idx !== false) {
			return $this->getDeviceIDFromRIS($userAgent, $idx + strlen($search) + 1);
		}
		
		return Constants::NO_MATCH;
	}
	
	public function applyRecoveryMatch($userAgent){
		$map = array(
			'Kindle/1' => 'amazon_kindle_ver1',
			'Kindle/2' => 'amazon_kindle2_ver1',
			'Kindle/3' => 'amazon_kindle3_ver1',
			'Kindle Fire' => 'amazon_kindle_fire_ver1',
			'Silk' => 'amazon_kindle_fire_ver1',
		);
		foreach ($map as $keyword => $id) {
			if (Utils::checkIfContains($userAgent, $keyword)) return $id;
		}
		return 'generic_amazon_kindle';
	}
}