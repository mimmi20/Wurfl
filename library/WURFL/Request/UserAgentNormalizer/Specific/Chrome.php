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
 * User Agent Normalizer - Return the Chrome string with the major version
 * @package	WURFL_Request_UserAgentNormalizer_Specific
 */
class WURFL_Request_UserAgentNormalizer_Specific_Chrome implements WURFL_Request_UserAgentNormalizer_Interface {
	
	public function normalize($userAgent) {
		return $this->chromeWithMajorVersion($userAgent);		
	}
	
	/**
	 * Returns Google Chrome's Major version number
	 * @param string $userAgent
	 * @return string|int Version number
	 */
	private function chromeWithMajorVersion($userAgent) {
		$start_idx = strpos($userAgent, 'Chrome');
		$end_idx = strpos($userAgent, '.', $start_idx);
		if ($end_idx === false) {
			return substr($userAgent, $start_idx);
		} else {
			return substr($userAgent, $start_idx, ($end_idx - $start_idx));
		}
	}
}