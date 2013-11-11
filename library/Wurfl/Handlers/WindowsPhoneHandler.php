<?php
namespace Wurfl\Handlers;

/**
 * Copyright (c) 2012 ScientiaMobile, Inc.
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 * Refer to the COPYING.txt file distributed with this package.
 *
 * @category   WURFL
 * @package    \Wurfl\Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */

use Wurfl\Constants;

/**
 * WindowsPhoneUserAgentHandler
 *
 * @category   WURFL
 * @package    \Wurfl\Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
class WindowsPhoneHandler extends Handler
{
    protected $prefix = "WINDOWSPHONE";

    public static $constantIDs = array(
        'generic_ms_winmo6_5',
        'generic_ms_phone_os7',
        'generic_ms_phone_os7_5',
        'generic_ms_phone_os8',
    );

    public function canHandle($userAgent)
    {
        if (Utils::isDesktopBrowser($userAgent)) {
            return false;
        }

        return Utils::checkIfContains($userAgent, 'Windows Phone');
    }

    public function applyConclusiveMatch($userAgent)
    {
        $tolerance = Utils::toleranceToRisDelimeter($userAgent);
        if ($tolerance !== false) {
            return $this->getDeviceIDFromRIS($userAgent, $tolerance);
        }

        return Constants::NO_MATCH;
    }

    public function applyRecoveryMatch($userAgent)
    {
        if (Utils::checkIfContains($userAgent, 'Windows Phone 8')) {
            return 'generic_ms_phone_os8';
        }
        if (Utils::checkIfContains($userAgent, 'Windows Phone OS 7.5')) {
            return 'generic_ms_phone_os7_5';
        }
        if (Utils::checkIfContains($userAgent, 'Windows Phone OS 7.0')) {
            return 'generic_ms_phone_os7';
        }
        if (Utils::checkIfContains($userAgent, 'Windows Phone 6.5')) {
            return 'generic_ms_winmo6_5';
        }

        return Constants::NO_MATCH;
    }

    public static function getWindowsPhoneModel($ua)
    {
        // This regex is relatively fast because there is not much backtracking, and almost all UAs will match
        if (preg_match('|IEMobile/\d+\.\d+;(?: ARM;)?(?: Touch;)? ?([^;\)]+(; ?[^;\)]+)?)|', $ua, $matches)) {
            $model = $matches[1];
            $model = str_replace('_blocked', '', $model);
            $model = preg_replace('/NOKIA; (RM-.+?)_.*/', '$1', $model, 1);

            return $model;
        }

        return null;
    }

    public static function getWindowsPhoneVersion($ua)
    {
        if (preg_match('|Windows Phone(?: OS)? (\d+\.\d+)|', $ua, $matches)) {
            return $matches[1];
        }

        return null;
    }
}