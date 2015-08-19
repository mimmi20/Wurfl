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
 * @package    WURFL
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */

namespace Wurfl\Handlers\Normalizer\Specific;

use Wurfl\Handlers\Utils;
use Wurfl\Handlers\WindowsPhoneHandler;
use Wurfl\Handlers\Normalizer\NormalizerInterface;
use Wurfl\WurflConstants;

/**
 * User Agent Normalizer
 *
 * @package    \Wurfl\Handlers\Normalizer\Specific
 */
class WindowsPhone
    implements NormalizerInterface
{
    /**
     * @param string $userAgent
     *
     * @return string
     */
    public function normalize($userAgent)
    {
        if (Utils::checkIfContainsAnyOf($userAgent, array('WPDesktop', 'ZuneWP7'))
            || Utils::checkIfContainsAll($userAgent, array('Mozilla/5.0 (Windows NT ', ' ARM;', ' Edge/'))
        ) {
            $model   = WindowsPhoneHandler::getWindowsPhoneDesktopModel($userAgent);
            $version = WindowsPhoneHandler::getWindowsPhoneDesktopVersion($userAgent);
        } elseif (Utils::checkIfStartsWithAnyOf($userAgent, array('Windows Phone Ad Client', 'WindowsPhoneAdClient'))) {
            $model   = WindowsPhoneHandler::getWindowsPhoneAdClientModel($userAgent);
            $version = WindowsPhoneHandler::getWindowsPhoneVersion($userAgent);
        } elseif (Utils::checkIfContains($userAgent, 'NativeHost')) {
            return $userAgent;
        } else {
            $model   = WindowsPhoneHandler::getWindowsPhoneModel($userAgent);
            $version = WindowsPhoneHandler::getWindowsPhoneVersion($userAgent);
        }
        
        if ($model !== null && $version !== null) {
            // 'WP' is for Windows Phone
            $prefix = 'WP' . $version . ' ' . $model . WurflConstants::RIS_DELIMITER;

            return $prefix . $userAgent;
        }

        return $userAgent;
    }
}
