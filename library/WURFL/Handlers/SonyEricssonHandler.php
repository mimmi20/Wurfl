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
 * SonyEricssonUserAgentHandler
 *
 *
 * @category   WURFL
 * @package    WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
class SonyEricssonHandler extends Handler {
    
    protected $prefix = "SONY_ERICSSON";
    
    public function canHandle($userAgent) {
        if (Utils::isDesktopBrowser($userAgent)) return false;
        return Utils::checkIfContains($userAgent, 'Sony');
    }
    
    public function applyConclusiveMatch($userAgent) {
        if (Utils::checkIfStartsWith($userAgent, 'SonyEricsson')) {
            $tolerance = Utils::firstSlash($userAgent) - 1;
            return $this->getDeviceIDFromRIS($userAgent, $tolerance);
        }
        $tolerance = Utils::secondSlash($userAgent);
        return $this->getDeviceIDFromRIS($userAgent, $tolerance);
    }
}
