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
 * @version    $id$
 */

/**
 * WindowsCEUserAgentHandler
 *
 *
 * @category   WURFL
 * @package    WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
class WindowsCEHandler extends Handler
{
    protected $prefix = 'WINDOWS_CE';
    const TOLLERANCE = 3;
    
    public function __construct($wurflContext, $userAgentNormalizer = null) 
    {
        parent::__construct($wurflContext, $userAgentNormalizer);
    }
    
    /**
     * Intercept all UAs containing 'Mozilla/' and 'Windows CE'
     *
     * @param string $userAgent
     * @return boolean
     */
    public function canHandle($userAgent) 
    {
        return Utils::checkIfContains($userAgent, 'Mozilla/') && Utils::checkIfContains($userAgent, 'Windows CE');
    }
    
    /**
     * Apply LD with a threshold of 3
     *
     * @param string $userAgent
     * @return string
     */
    public function lookForMatchingUserAgent($userAgent) 
    {
        return Utils::ldMatch(array_keys($this->userAgentsWithDeviceID), $userAgent, self::TOLLERANCE);
    }
    
    /**
     * Apply Recovery Match
     *
     * @param string $userAgent
     * @return string
     */
    public function applyRecoveryMatch($userAgent) 
    {
        return 'generic_ms_mobile';
    }

}
