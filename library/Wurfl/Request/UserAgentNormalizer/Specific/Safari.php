<?php
namespace Wurfl\Request\UserAgentNormalizer\Specific;

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

use \Wurfl\Request\UserAgentNormalizer\NormalizerInterface;
use \Wurfl\Handlers\SafariHandler;
use \Wurfl\Constants;

/**
 * User Agent Normalizer
 * Return the safari user agent stripping out 
 * 	- all the chararcters between U; and Safari/xxx
 *	
 *  e.g Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_4_11; fr) AppleWebKit/525.18 (KHTML, like Gecko) Version/3.1.1 Safari/525.18
 * 		becomes
 * 		Mozilla/5.0 (Macintosh Safari/525
 * @package	WURFL_Request_UserAgentNormalizer_Specific
 */
class Safari implements NormalizerInterface
{
	public function normalize($userAgent)
    {
		$safari_version = SafariHandler::getSafariVersion($userAgent);
		if (!$safari_version) {
			return $userAgent;
		}
		$prefix = 'Safari ' . $safari_version . Constants::RIS_DELIMITER;
		return $prefix . $userAgent;
	}
}