<?php
namespace Wurfl\Handlers;

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
 *
 * @category   WURFL
 * @package    \Wurfl\Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */

use \Wurfl\Constants;

/**
 * NokiaOviBrowserUserAgentHandler
 * 
 *
 * @category   WURFL
 * @package    \Wurfl\Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
class NokiaOviBrowserHandler extends Handler {
    
    protected $prefix = "NOKIAOVIBROWSER";
    
    public static $constantIDs = array(
        'nokia_generic_series40_ovibrosr',
    );
    
    public function canHandle($userAgent) {
        if (Utils::isDesktopBrowser($userAgent)) return false;
        return Utils::checkIfContains($userAgent, 'S40OviBrowser');
    }
    
    public function applyConclusiveMatch($userAgent) {
        $idx = strpos($userAgent, 'Nokia');
        if ($idx === false) return Constants::NO_MATCH;
        $tolerance = Utils::indexOfAnyOrLength($userAgent, array('/', ' '), $idx);
        return $this->getDeviceIDFromRIS($userAgent, $tolerance);
    }
    
    public function applyRecoveryMatch($userAgent){
        return 'nokia_generic_series40_ovibrosr';
    }
}