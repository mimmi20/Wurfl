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
 * WindowsPhoneDesktopUserAgentHandler
 *
 *
 * @category   WURFL
 * @package    WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
class WindowsPhoneDesktopHandler extends AbstractHandler
{
    /**
     * @var string
     */
    protected $prefix = "WINDOWSPHONEDESKTOP";

    /**
     * @var array
     */
    public static $constantIDs
        = array(
            'generic_ms_phone_os7_desktopmode',
            'generic_ms_phone_os7_5_desktopmode',
            'generic_ms_phone_os8_desktopmode',
        );

    /**
     * @param string $userAgent
     *
     * @return bool
     */
    public function canHandle($userAgent)
    {
        return Utils::checkIfContainsAnyOf($userAgent, array('WPDesktop', 'ZuneWP7'));
    }

    /**
     * @param string $userAgent
     *
     * @return null|string
     */
    public function applyConclusiveMatch($userAgent)
    {
        // Exact and Recovery match only
        return Constants::NO_MATCH;
    }

    /**
     * @param string $userAgent
     *
     * @return string
     */
    public function applyRecoveryMatch($userAgent)
    {
        if (Utils::checkIfContains($userAgent, 'WPDesktop')) {
            return 'generic_ms_phone_os8_desktopmode';
        }
        if (Utils::checkIfContains($userAgent, 'Trident/5.0')) {
            return 'generic_ms_phone_os7_5_desktopmode';
        }
        return 'generic_ms_phone_os7_desktopmode';
    }
}
