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
 * @version    $id$
 */
/**
 * User Agent Normalizer - Return the Chrome string with the major version
 * @package    WURFL_Request_UserAgentNormalizer_Specific
 */
class Iron implements \Wurfl\Request\UserAgentNormalizer\NormalizerInterface
{
    public function normalize($userAgent)
    {
        return $this->_chromeWithMajorVersion($userAgent);        
    }
    
    /**
     * Returns Google Chrome's Major version number
     * @param string $userAgent
     * @return string|int Version number
     */
    private function _chromeWithMajorVersion($userAgent)
    {
        return substr($userAgent, strpos($userAgent, 'Iron'), 7);
    }

}

