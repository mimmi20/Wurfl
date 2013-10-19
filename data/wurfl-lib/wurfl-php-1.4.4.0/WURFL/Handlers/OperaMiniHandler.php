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
 * @package	WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license	GNU Affero General Public License
 * @version	$id$
 */

/**
 * OperaHandlder
 *
 *
 * @category   WURFL
 * @package	WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license	GNU Affero General Public License
 * @version	$id$
 */
class WURFL_Handlers_OperaMiniHandler extends WURFL_Handlers_Handler {

	protected $prefix = "OPERA_MINI";

	public function canHandle($userAgent) {
		return WURFL_Handlers_Utils::checkIfContains($userAgent, "Opera Mini");
	}

	private $operaMinis = array(
		'Opera Mini/1' => 'generic_opera_mini_version1',
		'Opera Mini/2' => 'generic_opera_mini_version2',
		'Opera Mini/3' => 'generic_opera_mini_version3',
		'Opera Mini/4' => 'generic_opera_mini_version4',
		'Opera Mini/5' => 'generic_opera_mini_version5',
	);

	function applyRecoveryMatch($userAgent) {
		foreach ($this->operaMinis as $key => $deviceId) {
			if (WURFL_Handlers_Utils::checkIfContains($userAgent, $key)) {
				return $deviceId;
			}
		}
		if (WURFL_Handlers_Utils::checkIfContains($userAgent, 'Opera Mobi')) {
			return 'generic_opera_mini_version4';
		}
		return 'generic_opera_mini_version1';
	}
}