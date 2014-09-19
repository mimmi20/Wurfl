<?php
namespace Wurfl\Handlers;

/**
 * Copyright (c) 2014 ScientiaMobile, Inc.
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
 * @package    WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */

use Wurfl\Constants;

/**
 * UbuntuTouchOSUserAgentHandler
 * 
 *
 * @category   WURFL
 * @package    WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
class UbuntuTouchOSHandler extends AbstractHandler
{
    
    protected $prefix = 'UbuntuTouchOS';
    
    public static $constantIDs = array(
        'generic_ubuntu_touch_os',
        'generic_ubuntu_touch_os_tablet',
    );
    
    public function canHandle($userAgent) {
        return (Utils::checkIfContains($userAgent, 'Ubuntu') && Utils::checkIfContainsAnyOf($userAgent, array('Mobile', 'Tablet')));
    }
    
    public function applyConclusiveMatch($userAgent) {
        // Mozilla/5.0 (Ubuntu; Mobile) WebKit/537.21
        // Mozilla/5.0 (Ubuntu; Tablet) WebKit/537.21
        //                      ^ RIS tolerance
        // Mozilla/5.0 (Linux; Ubuntu 14.04 like Android 4.4) AppleWebKit/537.36 Chromium/35.0.1870.2 Mobile Safari/537.36
        //                                  ^ RIS tolerance
        if (Utils::checkIfContains($userAgent, 'like Android')) {
            $search = 'like Android';
        } else {
            $search = 'WebKit/';
        }
        $idx = strpos($userAgent, $search);
        if ($idx !== false) {
            // Match to the end of the search string
            $tolerance = strlen($idx + strlen($search));
            return $this->getDeviceIDFromRIS($userAgent, $tolerance);
        }
        return Constants::NO_MATCH;
    }

    public function applyRecoveryMatch($userAgent) {
        if (Utils::checkIfContains($userAgent, 'Tablet')) {
            return 'generic_ubuntu_touch_os_tablet';
        }  else {
            return 'generic_ubuntu_touch_os';
        }
    }
    
}
