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
 * @package    WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */

/**
 * SamsungUserAgentHandler
 *
 *
 * @category   WURFL
 * @package    WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
class SamsungHandler extends \Wurfl\Handlers\AbstractHandler {

    protected $prefix = "SAMSUNG";
    
    public function canHandle($userAgent) {
        if (\Wurfl\Handlers\Utils::isDesktopBrowser($userAgent)) return false;
        return \Wurfl\Handlers\Utils::checkIfContainsAnyOf($userAgent, array('Samsung', 'SAMSUNG'))
            || \Wurfl\Handlers\Utils::checkIfStartsWithAnyOf($userAgent, array('SEC-', 'SPH', 'SGH', 'SCH'));
    }
    
    public function applyConclusiveMatch($userAgent) {
        if (\Wurfl\Handlers\Utils::checkIfStartsWithAnyOf($userAgent, array("SEC-", "SAMSUNG-", "SCH"))) {
            $tolerance = \Wurfl\Handlers\Utils::firstSlash($userAgent);
        } else if (\Wurfl\Handlers\Utils::checkIfStartsWithAnyOf($userAgent, array("Samsung", "SPH", "SGH"))) {
            $tolerance = \Wurfl\Handlers\Utils::firstSpace($userAgent);
        } else {
            $tolerance = \Wurfl\Handlers\Utils::secondSlash($userAgent);
        }
        return $this->getDeviceIDFromRIS($userAgent, $tolerance);
    }
    
    public function applyRecoveryMatch($userAgent) {
        if (\Wurfl\Handlers\Utils::checkIfStartsWith($userAgent, 'SAMSUNG')) {
            $tolerance = 8;
            return $this->getDeviceIDFromLD($userAgent, $tolerance);
        } else {
            $tolerance = \Wurfl\Handlers\Utils::indexOfOrLength($userAgent, '/', strpos($userAgent, 'Samsung'));
            return $this->getDeviceIDFromRIS($userAgent, $tolerance);
        }
    }
}