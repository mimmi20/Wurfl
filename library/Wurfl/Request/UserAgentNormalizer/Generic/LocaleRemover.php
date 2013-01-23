<?php
namespace Wurfl\Request\UserAgentNormalizer\Generic;

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
 * @package	WURFL_Request_UserAgentNormalizer_Generic
 * @copyright  ScientiaMobile, Inc.
 * @license	GNU Affero General Public License
 * @author	 Fantayeneh Asres Gizaw
 * @version	$id$
 */

use \Wurfl\Request\UserAgentNormalizer\NormalizerInterface;
use \Wurfl\Handlers\Utils;

/**
 * User Agent Normalizer - removes locale information from user agent
 * @package	WURFL_Request_UserAgentNormalizer_Generic
 */
class LocaleRemover implements NormalizerInterface
{
	public function normalize($userAgent)
    {
		return Utils::removeLocale($userAgent);
	}
}