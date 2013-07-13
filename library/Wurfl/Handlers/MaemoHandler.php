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
 * MaemoUserAgentHandler
 * 
 *
 * @category   WURFL
 * @package    \Wurfl\Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
class MaemoHandler extends Handler {
    
    protected $prefix = "MAEMO";
    
    public static $constantIDs = array(
        'generic_opera_mobi_maemo',
        'nokia_generic_maemo_with_firefox',
        'nokia_generic_maemo',
    );
    
    public function canHandle($userAgent) {
        return Utils::checkIfContains($userAgent, 'Maemo');
    }
    
    public function applyConclusiveMatch($userAgent) {
        $tolerance = Utils::toleranceToRisDelimeter($userAgent);
        if ($tolerance !== false) {
            return $this->getDeviceIDFromRIS($userAgent, $tolerance);
        }
        
        return $this->getDeviceIDFromLD($userAgent, 7);
    }
    
    public function applyRecoveryMatch($userAgent){
        if (Utils::checkIfContains($userAgent, 'Opera Mobi')) {
            return 'generic_opera_mobi_maemo';
        }
        if (Utils::checkIfContains($userAgent, 'Firefox')) {
            return 'nokia_generic_maemo_with_firefox';
        }
        return 'nokia_generic_maemo';
    }
    
    public static function getMaemoModel($ua) {
        if (preg_match('/Maemo [bB]rowser [\d\.]+ (.+)/', $ua, $matches)) {
            $model = $matches[1];
            $idx = strpos($model, ' GTB');
            if ($idx !== false) {
                $model = substr($model, 0, $idx);
            }
            return $model;
        }
        return null;
    }
}