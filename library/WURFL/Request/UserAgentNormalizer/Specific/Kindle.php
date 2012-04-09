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
class WURFL_Request_UserAgentNormalizer_Specific_Kindle implements WURFL_Request_UserAgentNormalizer_Interface {
	public function normalize($userAgent) {
		if (WURFL_Handlers_Utils::checkIfContainsAll($userAgent, array('Android', 'Kindle Fire'))) {
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