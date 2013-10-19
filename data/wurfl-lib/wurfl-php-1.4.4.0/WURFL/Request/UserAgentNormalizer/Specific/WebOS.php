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
class WURFL_Request_UserAgentNormalizer_Specific_WebOS implements WURFL_Request_UserAgentNormalizer_Interface {
	public function normalize($userAgent) {
		$model = WURFL_Handlers_WebOSHandler::getWebOSModelVersion($userAgent);
		$os_ver = WURFL_Handlers_WebOSHandler::getWebOSVersion($userAgent);
		if ($model !== null && $os_ver !== null) {
			$prefix = $model.' '.$os_ver.WURFL_Constants::RIS_DELIMITER;
			return $prefix.$userAgent;
		}
		return $userAgent;
	}
}