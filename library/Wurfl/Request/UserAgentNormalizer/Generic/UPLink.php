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
 * User Agent Normalizer - removes UP.Link garbage from user agent
 * @package    WURFL_Request_UserAgentNormalizer_Generic
 */
class UPLink implements \Wurfl\Request\UserAgentNormalizer\NormalizerInterface
{
    /**
     * This method remove the "UP.Link" substring from user agent string.
     *
     * @param string $userAgent
     * @return string Normalized user agent
     */
    public function normalize($userAgent)
    {
        $index = strpos($userAgent, ' UP.Link');
        if ($index > 0) {
            return substr($userAgent, 0, $index);
        }
        return $userAgent;
    }

}

