<?php
declare(ENCODING = 'utf-8');
namespace Wurfl\Request\UserAgentNormalizer\Generic;

/**
 * Copyright(c) 2011 ScientiaMobile, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or(at your option) any later version.
 *
 * Refer to the COPYING file distributed with this package.
 *
 * @category   WURFL
 * @package    WURFL_Request_UserAgentNormalizer_Generic
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @author     Fantayeneh Asres Gizaw
 * @version   SVN: $Id$
 */
/**
 * User Agent Normalizer - returns the substring starting from "BlackBerry"
 * @package    WURFL_Request_UserAgentNormalizer_Generic
 */
class BlackBerry implements \Wurfl\Request\UserAgentNormalizer\NormalizerInterface
{
    public function normalize($userAgent)
    {
        $index = strrpos($userAgent, 'BlackBerry');
        if ($index > 0 && strpos($userAgent, 'AppleWebKit') === FALSE) {
            return substr($userAgent, $index);
        }
        return $userAgent;
    }
}