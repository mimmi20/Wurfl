<?php
declare(ENCODING = 'utf-8');
namespace WURFL\Request\UserAgentNormalizer\Generic;

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
 * @package    WURFL_Request_UserAgentNormalizer_Generic
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @author     Fantayeneh Asres Gizaw
 * @version    $id$
 */
/**
 * User Agent Normalizer - returns the substring starting from "BlackBerry"
 * @package    WURFL_Request_UserAgentNormalizer_Generic
 */
class BlackBerry implements \WURFL\Request\UserAgentNormalizer\NormalizerInterface  {

    public function normalize($userAgent) {
        // Normalize mixed-case BlackBerry
        $userAgent = str_ireplace('blackberry', 'BlackBerry', $userAgent);
        $index = strrpos($userAgent, "BlackBerry");
        if ($index > 0 && strpos($userAgent, "AppleWebKit") === false) {
            return substr($userAgent, $index);
        }
        return $userAgent;
    }
}