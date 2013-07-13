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

/**
 * NokiaUserAgentHandler
 *
 *
 * @category   WURFL
 * @package    \Wurfl\Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
class NokiaHandler extends Handler {
    
    protected $prefix = "NOKIA";
    
    public static $constantIDs = array(
        'nokia_generic_series60',
        'nokia_generic_series80',
        'nokia_generic_meego',
    );
    
    public function canHandle($userAgent) {
        if (Utils::isDesktopBrowser($userAgent)) return false;
        return Utils::checkIfContains($userAgent, 'Nokia');
    }
    
    public function applyConclusiveMatch($userAgent) {
        $tolerance = Utils::indexOfAnyOrLength($userAgent, array('/', ' '), strpos($userAgent, 'Nokia'));
        return $this->getDeviceIDFromRIS($userAgent, $tolerance);
    }
    
    public function applyRecoveryMatch($userAgent) {
        if (Utils::checkIfContains($userAgent, 'Series60')) return 'nokia_generic_series60';
        if (Utils::checkIfContains($userAgent, 'Series80')) return 'nokia_generic_series80';
        if (Utils::checkIfContains($userAgent, 'MeeGo')) return 'nokia_generic_meego';
        return null;
    }
}