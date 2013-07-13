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
 * BlackBerryUserAgentHandler
 * 
 *
 * @category   WURFL
 * @package    \Wurfl\Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */

class BlackBerryHandler extends Handler
{
    protected $prefix = "BLACKBERRY";
    
    public static $constantIDs = array(
        "2." => "blackberry_generic_ver2",
        "3.2" => "blackberry_generic_ver3_sub2",
        "3.3" => "blackberry_generic_ver3_sub30",
        "3.5" => "blackberry_generic_ver3_sub50",
        "3.6" => "blackberry_generic_ver3_sub60",
        "3.7" => "blackberry_generic_ver3_sub70",
        "4.1" => "blackberry_generic_ver4_sub10",
           "4.2" => "blackberry_generic_ver4_sub20",
        "4.3" => "blackberry_generic_ver4_sub30",
        "4.5" => "blackberry_generic_ver4_sub50",
           "4.6" => "blackberry_generic_ver4_sub60",
        "4.7" => "blackberry_generic_ver4_sub70",
        "4." => "blackberry_generic_ver4",    
        "5." => "blackberry_generic_ver5",
        "6." => "blackberry_generic_ver6"
    );
    
    public function canHandle($userAgent)
    {
        if (Utils::isDesktopBrowser($userAgent)) return false;
        return Utils::checkIfContainsCaseInsensitive($userAgent, "BlackBerry");
    }
        
    public function applyConclusiveMatch($userAgent)
    {
        if (Utils::checkIfStartsWith($userAgent, 'Mozilla/4')) {
            $tolerance = Utils::secondSlash($userAgent);
        } elseif (Utils::checkIfStartsWith($userAgent, 'Mozilla/5')) {
            $tolerance = Utils::ordinalIndexOf($userAgent, ';', 3);
        } else {
            $tolerance = Utils::firstSlash($userAgent);
        }
        return $this->getDeviceIDFromRIS($userAgent, $tolerance);
    }
    
    public function applyRecoveryMatch($userAgent)
    {
        // No need for case insensitivity here, BlackBerry was fixed in the normalizer 
        if (preg_match('#BlackBerry[^/\s]+/(\d.\d)#', $userAgent, $matches)) {
            $version = $matches[1];
            foreach (self::$constantIDs as $vercode => $deviceID) {
                if (strpos($version, $vercode) !== false) {
                    return $deviceID;
                }
            }
        }
        return null;
    }
}