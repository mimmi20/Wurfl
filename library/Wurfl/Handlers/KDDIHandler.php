<?php
declare(ENCODING = 'utf-8');
namespace Wurfl\Handlers;

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
 *
 * @category   WURFL
 * @package    WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version   SVN: $Id$
 */

/**
 * KDDIUserAgentHandler
 *
 *
 * @category   WURFL
 * @package    WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version   SVN: $Id$
 */
class KDDIHandler extends Handler
{
    protected $prefix = 'KDDI';
    
    public function __construct($wurflContext, $userAgentNormalizer = null)
    {
        parent::__construct($wurflContext, $userAgentNormalizer);
    }
    
    /**
     * Intercept all UAs containing 'KDDI'
     *
     * @param string $userAgent
     * @return boolean
     */
    public function canHandle($userAgent)
    {
        return Utils::checkIfContains($userAgent, 'KDDI');
    }
    
    /**
     */
    public function lookForMatchingUserAgent($userAgent)
    {
        $tolerance = $this->tolerance($userAgent);
        return Utils::risMatch(array_keys($this->userAgentsWithDeviceID), $userAgent, $tolerance);
    }
    
    /**
     *
     * @param string $userAgent
     * @return string
     */
    public function applyRecoveryMatch($userAgent)
    {
        if(Utils::checkIfContains($userAgent, 'Opera')) {
            return 'opera';
        }
        return 'opwv_v62_generic';
    }
    
    private function tolerance($userAgent)
    {
        if(Utils::checkIfStartsWith($userAgent, 'KDDI/')) {
            return Utils::secondSlash($userAgent);
        }
        
        if(Utils::checkIfStartsWith($userAgent, 'KDDI')) {
            return Utils::firstSlash($userAgent);
        }
        
        return Utils::indexOfOrLength($userAgent, ')');
    
    }

}
