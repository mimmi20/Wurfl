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
 * MotorolaUserAgentHanlder
 *
 *
 * @category   WURFL
 * @package    WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version   SVN: $Id$
 */
class MotorolaHandler extends Handler
{
    protected $prefix = 'MOTOROLA';
    
    public function __construct($wurflContext, $userAgentNormalizer = null)
    {
        parent::__construct($wurflContext, $userAgentNormalizer);
    }
    
    /**
     *
     * Intercept all UAs starting with 'Mot-', or containing 'MOT-' or
     * 'Motorola'
     *
     * @param string $userAgent
     * @return boolean
     */
    public function canHandle($userAgent)
    {
        return Utils::checkIfContains($userAgent, 'Mot-') || Utils::checkIfContains($userAgent, 'MOT-') || Utils::checkIfContains($userAgent, 'Motorola');
    
    }
    
    /**
     * If the User Agent contains 'MIB/2.2' or 'MIB/BER2.2', 
     * return 'mot_mib22_generic'
     *
     * @param string $userAgent
     * @return string
     */
    public function applyRecoveryMatch($userAgent)
    {
        if(Utils::checkIfContains($userAgent, 'MIB/2.2') || Utils::checkIfContains($userAgent, 'MIB/BER2.2')) {
            return 'mot_mib22_generic';
        }
        
        return \Wurfl\Constants::GENERIC;
    }

}
