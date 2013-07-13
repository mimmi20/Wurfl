<?php
namespace Wurfl\Request\UserAgentNormalizer\Specific;

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
 * @package    \Wurfl\Request_UserAgentNormalizer_Specific
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @author     Fantayeneh Asres Gizaw
 * @version    $id$
 */

use \Wurfl\Request\UserAgentNormalizer\NormalizerInterface;

/**
 * User Agent Normalizer - MSIE String with the Major and Minor Version Only.
 * @package    \Wurfl\Request_UserAgentNormalizer_Specific
 */
class MSIE implements NormalizerInterface
{
    public function normalize($userAgent)
    {
        return $this->msieWithVersion($userAgent);                
    }
    /**
     * Returns version info
     * @param string $userAgent
     * @return string Version info
     */
    private function msieWithVersion($userAgent)
    {
        // return preg_replace('/( \.NET CLR [\d\.]+;?| Media Center PC [\d\.]+;?| OfficeLive[a-zA-Z0-9\.\d]+;?| InfoPath[\.\d]+;?)/', '', $userAgent)
        return substr($userAgent, strpos($userAgent, "MSIE"), 8);
    }
}