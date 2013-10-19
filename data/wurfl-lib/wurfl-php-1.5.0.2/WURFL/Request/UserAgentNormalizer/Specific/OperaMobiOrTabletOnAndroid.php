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
 * User Agent Normalizer
 * @package	WURFL_Request_UserAgentNormalizer_Specific
 */
class WURFL_Request_UserAgentNormalizer_Specific_OperaMobiOrTabletOnAndroid implements WURFL_Request_UserAgentNormalizer_Interface {
	
	public function normalize($userAgent) {
		
		$is_opera_mobi = WURFL_Handlers_Utils::checkIfContains($userAgent, 'Opera Mobi');
		$is_opera_tablet = WURFL_Handlers_Utils::checkIfContains($userAgent, 'Opera Tablet');
		if ($is_opera_mobi || $is_opera_tablet) {
			$opera_version = WURFL_Handlers_OperaMobiOrTabletOnAndroidHandler::getOperaOnAndroidVersion($userAgent, false);
			$android_version = WURFL_Handlers_AndroidHandler::getAndroidVersion($userAgent, false);
			if ($opera_version !== null && $android_version !== null) {
				$opera_model = $is_opera_tablet? 'Opera Tablet': 'Opera Mobi';
				$prefix = $opera_model.' '.$opera_version.' Android '.$android_version.WURFL_Constants::RIS_DELIMITER;
				return $prefix.$userAgent;
			}
		}
		
		return $userAgent;
	}
}