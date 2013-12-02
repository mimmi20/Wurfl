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
 * AppleUserAgentHandler
 *
 *
 * @category   WURFL
 * @package    WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
class AppleHandler extends \Wurfl\Handlers\AbstractHandler {
    
    protected $prefix = "APPLE";
    
    public static $constantIDs = array(
        'apple_ipod_touch_ver1',
        'apple_ipod_touch_ver2',
        'apple_ipod_touch_ver3',
        'apple_ipod_touch_ver4',
        'apple_ipod_touch_ver5',
        'apple_ipod_touch_ver6',
        'apple_ipod_touch_ver7',
    
        'apple_ipad_ver1',
        'apple_ipad_ver1_subua32',
        'apple_ipad_ver1_sub42',
        'apple_ipad_ver1_sub5',
        'apple_ipad_ver1_sub6',
        'apple_ipad_ver1_sub7',
    
        'apple_iphone_ver1',
        'apple_iphone_ver2',
        'apple_iphone_ver3',
        'apple_iphone_ver4',
        'apple_iphone_ver5',
        'apple_iphone_ver6',
        'apple_iphone_ver7',
    );
    
    public function canHandle($userAgent) {
        if (\Wurfl\Handlers\Utils::isDesktopBrowser($userAgent)) return false;
        return (\Wurfl\Handlers\Utils::checkIfStartsWith($userAgent, 'Mozilla/5') && \Wurfl\Handlers\Utils::checkIfContainsAnyOf($userAgent, array('iPhone', 'iPod', 'iPad')));
    }
    
    public function applyConclusiveMatch($userAgent) {
        $tolerance = strpos($userAgent, '_');
        if ($tolerance !== false) {
            // The first char after the first underscore
            $tolerance++;
        } else {
            $index = strpos($userAgent, 'like Mac OS X;');
            if ($index !== false) {
                // Step through the search string to the semicolon at the end
                $tolerance = $index + 14;
            } else {
                // Non-typical UA, try full length match
                $tolerance = strlen($userAgent);
            }
        }
        return $this->getDeviceIDFromRIS($userAgent, $tolerance);
    }
    
    public function applyRecoveryMatch($userAgent) {
        if (preg_match('/ (\d)_(\d)[ _]/', $userAgent, $matches)) {
            $major_version = (int)$matches[1];
            $minor_version = (int)$matches[2];
        } else {
            $major_version = -1;
            $minor_version = -1;
        }
        // Check iPods first since they also contain 'iPhone'
        if (\Wurfl\Handlers\Utils::checkIfContains($userAgent, 'iPod')) {
            $deviceID = 'apple_ipod_touch_ver'.$major_version;
            if (in_array($deviceID, self::$constantIDs)) {
                return $deviceID;
            } else {
                return 'apple_ipod_touch_ver1';
            }
        
        // Now check for iPad
        } else if (\Wurfl\Handlers\Utils::checkIfContains($userAgent, 'iPad')) {
            $deviceID = 'apple_ipad_ver1_sub'.$major_version;
            
            if ($major_version == 3) {
                return 'apple_ipad_ver1_subua32';
            } else if ($major_version == 4) {
                return 'apple_ipad_ver1_sub42';
            }
            
            if (in_array($deviceID, self::$constantIDs)) {
                return $deviceID;
            } else {
                return 'apple_ipad_ver1';
            }
        
        // Check iPhone last
        } else if (\Wurfl\Handlers\Utils::checkIfContains($userAgent, 'iPhone')) {
            $deviceID = 'apple_iphone_ver'.$major_version;
            if (in_array($deviceID, self::$constantIDs)) {
                return $deviceID;
            } else {
                return 'apple_iphone_ver1';
            }
        }
        return \Wurfl\Constants::NO_MATCH;
    }

}
