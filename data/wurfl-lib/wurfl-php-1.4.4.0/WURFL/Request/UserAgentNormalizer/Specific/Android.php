<?php
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
 * @category   WURFL
 * @package	WURFL_Request_UserAgentNormalizer_Specific
 * @copyright  ScientiaMobile, Inc.
 * @license	GNU Affero General Public License
 * @author	 Fantayeneh Asres Gizaw
 * @version	$id$
 */
/**
 * User Agent Normalizer - Trims the version number to two digits (e.g. 2.1.1 -> 2.1)
 * @package	WURFL_Request_UserAgentNormalizer_Specific
 */
class WURFL_Request_UserAgentNormalizer_Specific_Android implements WURFL_Request_UserAgentNormalizer_Interface {
	
	private $skip_normalization = array(
			'Opera Mini',
			'Fennec',
			'Firefox',
			'UCWEB7',
			'NetFrontLifeBrowser/2.2',
		);
	
	public function normalize($userAgent) {
		// Normalize Android version
		$userAgent = preg_replace('/(Android)[ \-](\d\.\d)([^; \/\)]+)/', '$1 $2', $userAgent);
		
		// Opera Mobi/Tablet
		$is_opera_mobi = WURFL_Handlers_Utils::checkIfContains($userAgent, 'Opera Mobi');
		$is_opera_tablet = WURFL_Handlers_Utils::checkIfContains($userAgent, 'Opera Tablet');
		if ($is_opera_mobi || $is_opera_tablet) {
			$opera_version = WURFL_Handlers_AndroidHandler::getOperaOnAndroidVersion($userAgent, false);
			$android_version = WURFL_Handlers_AndroidHandler::getAndroidVersion($userAgent, false);
			if ($opera_version !== null && $android_version !== null) {
				$opera_model = $is_opera_tablet? 'Opera Tablet': 'Opera Mobi';
				$prefix = $opera_model.' '.$opera_version.' Android '.$android_version.WURFL_Constants::RIS_DELIMITER;
				return $prefix.$userAgent;
			}
		}
		
		// Stock Android
		if (!WURFL_Handlers_Utils::checkIfContainsAnyOf($userAgent, $this->skip_normalization)) {
			$model = WURFL_Handlers_AndroidHandler::getAndroidModel($userAgent, false);
			$version = WURFL_Handlers_AndroidHandler::getAndroidVersion($userAgent, false);
			if ($model !== null && $version !== null) {
				$prefix = $version.' '.$model.WURFL_Constants::RIS_DELIMITER;
				return $prefix.$userAgent;
			}
		}
		return $userAgent;
	}
}