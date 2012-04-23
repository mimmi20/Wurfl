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
 * @category   WURFL
 * @package    WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */

/**
 * FirefoxUserAgentHandler
 *
 *
 * @category   WURFL
 * @package    WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
class FirefoxHandler extends Handler {
    
    protected $prefix = "FIREFOX";
    
    public static $constantIDs = array(
        'firefox',
        'firefox_1',
        'firefox_2',
        'firefox_3',
        'firefox_4_0',
        'firefox_5_0',
        'firefox_6_0',
        'firefox_7_0',
        'firefox_8_0',
        'firefox_9_0',
        'firefox_10_0',
        'firefox_11_0',
        'firefox_12_0',
    );
    
    public function canHandle($userAgent) {
        if (Utils::isMobileBrowser($userAgent)) return false;
        if (Utils::checkIfContainsAnyOf($userAgent, array('Tablet', 'Sony', 'Novarra', 'Opera'))) return false;
        return Utils::checkIfContains($userAgent, 'Firefox');
    }
    
    public function applyConclusiveMatch($userAgent) {
        return $this->getDeviceIDFromRIS($userAgent, Utils::indexOfOrLength($userAgent, '.'));
    }
    
    public function applyRecoveryMatch($userAgent) {
        $matches = array();
        if (preg_match('/Firefox\/(\d+)\.\d/', $userAgent, $matches)){
            $firefox_version = $matches[1];
            if ((int)$firefox_version <= 3) {
                $id = 'firefox_'.$firefox_version;
            } else {
                $id = 'firefox_'.$firefox_version.'_0';
            }
            if (in_array($id, self::$constantIDs)) return $id;
        }
        return 'firefox';
    }
}