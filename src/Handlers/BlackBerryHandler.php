<?php
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
 * @package    WURFL
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */

namespace Wurfl\Handlers;

use Wurfl\Constants;

/**
 * BlackBerryUserAgentHandler
 *
 *
 * @category   WURFL
 * @package    WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
class BlackBerryHandler
    extends AbstractHandler
{

    protected $prefix = "BLACKBERRY";

    public static $constantIDs = array(
        '2.' => 'blackberry_generic_ver2',
        '3.2' => 'blackberry_generic_ver3_sub2',
        '3.3' => 'blackberry_generic_ver3_sub30',
        '3.5' => 'blackberry_generic_ver3_sub50',
        '3.6' => 'blackberry_generic_ver3_sub60',
        '3.7' => 'blackberry_generic_ver3_sub70',
        '4.1' => 'blackberry_generic_ver4_sub10',
        '4.2' => 'blackberry_generic_ver4_sub20',
        '4.3' => 'blackberry_generic_ver4_sub30',
        '4.5' => 'blackberry_generic_ver4_sub50',
        '4.6' => 'blackberry_generic_ver4_sub60',
        '4.7' => 'blackberry_generic_ver4_sub70',
        '4.' => 'blackberry_generic_ver4',
        '5.' => 'blackberry_generic_ver5',
        '6.' => 'blackberry_generic_ver6',
        '10' => 'blackberry_generic_ver10',
        '10t' => 'blackberry_generic_ver10_tablet',
    );

    public function canHandle($userAgent)
    {
        if (Utils::isDesktopBrowser($userAgent)) {
            return false;
        }

        return (Utils::checkIfContainsCaseInsensitive($userAgent, 'blackberry') || Utils::checkIfContains(
                $userAgent,
                '(BB10;'
            ));
    }

    public function applyConclusiveMatch($userAgent)
    {
        if (Utils::checkIfContains($userAgent, 'BB10')) {
            $tolerance = Utils::indexOfOrLength($userAgent, ')');
        } else {
            if (Utils::checkIfStartsWith($userAgent, 'Mozilla/4')) {
                $tolerance = Utils::secondSlash($userAgent);
            } else {
                if (Utils::checkIfStartsWith($userAgent, 'Mozilla/5')) {
                    $tolerance = Utils::ordinalIndexOf($userAgent, ';', 3);
                } else {
                    $tolerance = Utils::firstSlash($userAgent);
                }
            }
        }

        return $this->getDeviceIDFromRIS($userAgent, $tolerance);
    }

    public function applyRecoveryMatch($userAgent)
    {
        // BlackBerry 10
        if (Utils::checkIfContains($userAgent, 'BB10')) {
            if (Utils::checkIfContains($userAgent, 'Mobile')) {
                return 'blackberry_generic_ver10';
            } else {
                return 'blackberry_generic_ver10_tablet';
            }
        } elseif (preg_match('#Black[Bb]erry[^/\s]+/(\d.\d)#', $userAgent, $matches)) {
            $version = $matches[1];

            foreach (self::$constantIDs as $vercode => $deviceID) {
                if (strpos($version, $vercode) !== false) {
                    return $deviceID;
                }
            }
        }

        return Constants::NO_MATCH;
    }
}
