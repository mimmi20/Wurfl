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
 * SanyoUserAgentHandler
 *
 *
 * @category   WURFL
 * @package    WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
class SkyfireHandler extends \Wurfl\Handlers\AbstractHandler {
    
    protected $prefix = "SKYFIRE";
    
    public static $constantIDs = array(
        'generic_skyfire_version1',
        'generic_skyfire_version2',
    );

    public function canHandle($userAgent) {
        return \Wurfl\Handlers\Utils::checkIfContains($userAgent, 'Skyfire');
    }
    
    public function applyConclusiveMatch($userAgent) {
        $skyfire_idx = strpos($userAgent, 'Skyfire');
        // Matches the first decimal point after the Skyfire keyword: Skyfire/2.0
        return $this->getDeviceIDFromRIS($userAgent, \Wurfl\Handlers\Utils::indexOfOrLength($userAgent, '.', $skyfire_idx));
    }
    
    public function applyRecoveryMatch($userAgent) {
        if (\Wurfl\Handlers\Utils::checkIfContains($userAgent, 'Skyfire/2.')) {
            return 'generic_skyfire_version2';
        }
        return 'generic_skyfire_version1';
    }
    
}