<?php
declare(ENCODING = 'utf-8');
namespace WURFL\Handlers;

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
 * @package    WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */

/**
 * NintendoUserAgentHandler
 * 
 *
 * @category   WURFL
 * @package    WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
class NintendoHandler extends Handler {
    
    protected $prefix = "NINTENDO";
    
    public static $constantIDs = array(
        'nintendo_wii_ver1',
        'nintendo_dsi_ver1',
        'nintendo_ds_ver1',
    );
    
    public function canHandle($userAgent) {
        if (Utils::isDesktopBrowser($userAgent)) return false;
        if (Utils::checkIfContains($userAgent, 'Nintendo')) return true;
        return Utils::checkIfStartsWith($userAgent, 'Mozilla/') && Utils::checkIfContainsAll($userAgent, array('Nitro', 'Opera'));
    }
    
    public function applyConclusiveMatch($userAgent) {
        return $this->getDeviceIDFromLD($userAgent);
    }
    
    public function applyRecoveryMatch($userAgent) {
        if (Utils::checkIfContains($userAgent, 'Nintendo Wii')) return 'nintendo_wii_ver1';
        if (Utils::checkIfContains($userAgent, 'Nintendo DSi')) return 'nintendo_dsi_ver1';
        if ((Utils::checkIfStartsWith($userAgent, 'Mozilla/') && Utils::checkIfContainsAll($userAgent, array('Nitro', 'Opera')))) {
            return 'nintendo_ds_ver1';
        }
        return 'nintendo_wii_ver1';
    }
}