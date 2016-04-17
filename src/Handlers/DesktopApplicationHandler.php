<?php
/**
 * Copyright (c) 2015 ScientiaMobile, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Refer to the LICENSE file distributed with this package.
 *
 *
 * @category   WURFL
 *
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */

namespace Wurfl\Handlers;

use Wurfl\WurflConstants;

/**
 * DesktopApplicationHandler
 *
 *
 * @category   WURFL
 *
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */
class DesktopApplicationHandler extends AbstractHandler
{
    protected $prefix = 'DESKTOPAPPLICATION';

    public static $constantIDs = array(
        'generic_desktop_application',
        'mozilla_thunderbird',
        'ms_outlook',
        'ms_outlook_subua14',
        'ms_outlook_subua15',
        'ms_office',
        'ms_office_subua12',
        'ms_office_subua14',
        'ms_office_subua15',
    );

    public function canHandle($userAgent)
    {
        if (Utils::isMobileBrowser($userAgent)) {
            return false;
        }

        return Utils::checkIfContainsAnyOf(
            $userAgent,
            array('Thunderbird', 'Microsoft Outlook', 'MSOffice', 'DesktopApp ')
        );
    }

    public function applyConclusiveMatch($userAgent)
    {
        if (Utils::checkIfContains($userAgent, 'Thunderbird')) {
            $idx = strpos($userAgent, '.');
            if ($idx !== false) {
                return $this->getDeviceIDFromRIS($userAgent, $idx + 1);
            }
        }

        // Check for Outlook before Office
        if (preg_match('#Microsoft Outlook ([0-9]+)\.#', $userAgent, $matches)) {
            $deviceID = 'ms_outlook_subua' . $matches[1];
            if (in_array($deviceID, self::$constantIDs)) {
                return $deviceID;
            }
        } else {
            if (preg_match('#MSOffice ([0-9]+)\b#', $userAgent, $matches)) {
                $deviceID = 'ms_office_subua' . $matches[1];
                if (in_array($deviceID, self::$constantIDs)) {
                    return $deviceID;
                }
            }
        }

        return WurflConstants::NO_MATCH;
    }

    public function applyRecoveryMatch($userAgent)
    {
        if (Utils::checkIfContains($userAgent, 'Thunderbird')) {
            return 'mozilla_thunderbird';
        } elseif (Utils::checkIfContains($userAgent, 'Microsoft Outlook')) {
            return 'ms_outlook';
        } elseif (Utils::checkIfContains($userAgent, 'MSOffice')) {
            return 'ms_office';
        } elseif (Utils::checkIfContains($userAgent, 'DesktopApp ')) {
            return 'generic_desktop_application';
        }

        return WurflConstants::GENERIC_WEB_BROWSER;
    }
}
