<?php
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
 * @category   WURFL
 * @package    WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */

/**
 * ChromeUserAgentHandler
 *
 *
 * @category   WURFL
 * @package    WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
class ChromeHandler extends Handler {
    
    protected $prefix = "CHROME";
    
    public static $constantIDs = array(
        'google_chrome'
    );
    
    public function canHandle($userAgent) {
        if (Utils::isMobileBrowser($userAgent)) return false;
        return Utils::checkIfContains($userAgent, 'Chrome');
    }
    
    public function applyConclusiveMatch($userAgent) {
        $tolerance = Utils::indexOfOrLength('/', $userAgent, strpos($userAgent, 'Chrome'));
        return $this->getDeviceIDFromRIS($userAgent, $tolerance);
    }
    
    public function applyRecoveryMatch($userAgent) {
        return 'google_chrome';
    }
}