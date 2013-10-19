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
class WURFL_Request_UserAgentNormalizer_Specific_UcwebU3 implements WURFL_Request_UserAgentNormalizer_Interface {
	
	public function normalize($userAgent) {
		
		$ucb_version = WURFL_Handlers_UcwebU3Handler::getUcBrowserVersion($userAgent);
		if ($ucb_version === null) {
			return $userAgent;
		}

		//Android U3K Mobile + Tablet
		if (WURFL_Handlers_Utils::checkIfContains($userAgent, 'Android')) {
			// Apply Version+Model--- matching normalization
	
			$model = WURFL_Handlers_AndroidHandler::getAndroidModel($userAgent, false);
			$version = WURFL_Handlers_AndroidHandler::getAndroidVersion($userAgent, false);
			if ($model !== null && $version !== null) {
				$prefix = "$version U3Android $ucb_version $model".WURFL_Constants::RIS_DELIMITER;
				return $prefix.$userAgent;
			}
		}
	
		//iPhone U3K
		else if (WURFL_Handlers_Utils::checkIfContains($userAgent, 'iPhone;')) {
	
			if (preg_match('/iPhone OS (\d+)(?:_(\d+))?(?:_\d+)* like/', $userAgent, $matches)) {
				$version = $matches[1].'.'.$matches[2];
				$prefix = "$version U3iPhone $ucb_version".WURFL_Constants::RIS_DELIMITER;
				return $prefix.$userAgent;
			}
		}
			
		//iPad U3K
		else if (WURFL_Handlers_Utils::checkIfContains($userAgent, 'iPad')) {
			
			if (preg_match('/CPU OS (\d)_?(\d)?.+like Mac.+; iPad([0-9,]+)\) AppleWebKit/', $userAgent, $matches)) {
				$version = $matches[1].'.'.$matches[2];
				$model = $matches[3];
				$prefix = "$version U3iPad $ucb_version $model".WURFL_Constants::RIS_DELIMITER;
				return $prefix.$userAgent;
			}
		}
		
		return $userAgent;
	}
}