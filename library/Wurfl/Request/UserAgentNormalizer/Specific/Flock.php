<?php
declare(ENCODING = 'utf-8');
namespace Wurfl\Request\UserAgentNormalizer\Specific;

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
 * @package    WURFL_Request_UserAgentNormalizer_Specific
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @author     Fantayeneh Asres Gizaw
 * @version   SVN: $Id$
 */
/**
 * User Agent Normalizer - Return the firefox string with the major and minor version only
 * @package    WURFL_Request_UserAgentNormalizer_Specific
 */
class Flock implements \Wurfl\Request\UserAgentNormalizer\NormalizerInterface
{
    public function normalize($userAgent)
    {
        return $this->flockWithMajorAndMinorVersion($userAgent);        
    }
    /**
     * Returns FireFox major and minor version numbers
     * @param string $userAgent
     * @return string Major and minor version
     */
    private function flockWithMajorAndMinorVersion($userAgent)
    {
        return substr($userAgent, strpos($userAgent, 'Flock'), 11);
    }

}

