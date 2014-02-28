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
use Wurfl\Constants;

/**
 * WindowsPhoneUserAgentHandler
 *
 *
 * @category   WURFL
 * @package    WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
class WindowsPhoneHandler extends AbstractHandler
{
    /**
     * @var string
     */
    protected $prefix = "WINDOWSPHONE";

    /**
     * @var array
     */
    public static $constantIDs
        = array(
            'generic_ms_winmo6_5',
            'generic_ms_phone_os7',
            'generic_ms_phone_os7_5',
            'generic_ms_phone_os7_8',
            'generic_ms_phone_os8',
        );

    /**
     * @param string $userAgent
     *
     * @return bool
     */
    public function canHandle($userAgent)
    {
        if (Utils::isDesktopBrowser($userAgent)) {
            return false;
        }
        return Utils::checkIfContainsAnyOf($userAgent, array('Windows Phone', 'NativeHost'));
    }

    /**
     * @param string $userAgent
     *
     * @return null|string
     */
    public function applyConclusiveMatch($userAgent)
    {
        $tolerance = Utils::toleranceToRisDelimeter($userAgent);

        if ($tolerance !== false) {
            return $this->getDeviceIDFromRIS($userAgent, $tolerance);
        }

        if (Utils::checkIfContains($userAgent, 'NativeHost')) {
            return 'generic_ms_phone_os7';
        }

        return Constants::NO_MATCH;
    }

    /**
     * @param string $userAgent
     *
     * @return null|string
     */
    public function applyRecoveryMatch($userAgent)
    {
        // "Windows Phone OS 8" is for MS Ad SDK issues
        if (Utils::checkIfContainsAnyOf(
            $userAgent,
            array('Windows Phone 8', 'Windows Phone OS 8')
        )
        ) {
            return 'generic_ms_phone_os8';
        }

        if (Utils::checkIfContains($userAgent, 'Windows Phone OS 7.8')) {
            return 'generic_ms_phone_os7_8';
        }

        // WP OS 7.10 = Windows Phone 7.5 or 7.8
        if (Utils::checkIfContainsAnyOf(
            $userAgent,
            array('Windows Phone OS 7.5', 'Windows Phone OS 7.10')
        )
        ) {
            return 'generic_ms_phone_os7_5';
        }

        // Looking for "Windows Phone OS 7" instead of "Windows Phone OS 7.0" to address all WP 7 UAs that we may
        // not catch else where
        if (Utils::checkIfContains($userAgent, 'Windows Phone OS 7')) {
            return 'generic_ms_phone_os7';
        }

        if (Utils::checkIfContains($userAgent, 'Windows Phone 6.5')) {
            return 'generic_ms_winmo6_5';
        }

        return Constants::NO_MATCH;
    }

    /**
     * @param $userAgent
     *
     * @return mixed|null
     */
    public static function getWindowsPhoneModel($userAgent)
    {
        // Normalize spaces in UA before capturing parts
        $userAgent = preg_replace('|;(?! )|', '; ', $userAgent);

        // This regex is relatively fast because there is not much backtracking, and almost all UAs will match
        if (preg_match('|IEMobile/\d+\.\d+;(?: ARM;)?(?: Touch;)? ?([^;\)]+(; ?[^;\)]+)?)|', $userAgent, $matches)) {
            $model = $matches[1];

            // Some UAs contain "_blocked" and that string causes matching errors:
            $model = str_replace('_blocked', '', $model);

            // Nokia Windows Phone 7.5/8 "RM-" devices make matching particularly difficult:
            $model = preg_replace('/(NOKIA; RM-.+?)_.*/', '$1', $model, 1);

            return $model;
        }

        return null;
    }

    public static function getWindowsPhoneAdClientModel($userAgent)
    {
        // Normalize spaces in UA before capturing parts
        $userAgent = preg_replace('|;(?! )|', '; ', $userAgent);

        if (preg_match(
            '|Windows Phone Ad Client/[0-9\.]+ \(.+; ?Windows Phone(?: OS)? [0-9\.]+; ?([^;\)]+(; ?[^;\)]+)?)|',
            $userAgent,
            $matches
        )
        ) {
            $model = $matches[1];
            $model = str_replace('_blocked', '', $model);
            $model = preg_replace('/(NOKIA; RM-.+?)_.*/', '$1', $model, 1);
            return $model;
        }

        return null;
    }

    public static function getWindowsPhoneVersion($userAgent)
    {
        if (preg_match('|Windows Phone(?: OS)? (\d+\.\d+)|', $userAgent, $matches)) {
            return $matches[1];
        }
        return null;
    }

    public static function getWindowsPhoneAdClientVersion($userAgent)
    {
        if (preg_match('|Windows Phone(?: OS)? (\d+)\.(\d+)|', $userAgent, $matches)) {
            switch ((int)$matches[1]) {
                case 8:
                    return '8.0';
                    break;
                case 7:
                    return ((int)$matches[2] == 10) ? '7.5' : '7.0';
                    break;
            }
        }
        return null;
    }
}
