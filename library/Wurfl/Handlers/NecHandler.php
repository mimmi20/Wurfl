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
 * NecUserAgentHandler
 *
 *
 * @category   WURFL
 * @package    WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
class NecHandler extends \Wurfl\Handlers\AbstractHandler {
    
    const NEC_KGT_TOLERANCE = 2;
    protected $prefix = "NEC";
    
    public function canHandle($userAgent) {
        if (\Wurfl\Handlers\Utils::isDesktopBrowser($userAgent)) return false;
        return \Wurfl\Handlers\Utils::checkIfStartsWithAnyOf($userAgent, array('NEC-', 'KGT'));
    }
    
    public function applyConclusiveMatch($userAgent) {
        if (\Wurfl\Handlers\Utils::checkIfStartsWith($userAgent, "NEC-")) {
            $tolerance = \Wurfl\Handlers\Utils::firstSlash($userAgent);
            return $this->getDeviceIDFromRIS($userAgent, $tolerance);
        }
        return $this->getDeviceIDFromLD($userAgent, self::NEC_KGT_TOLERANCE);
    }
}
