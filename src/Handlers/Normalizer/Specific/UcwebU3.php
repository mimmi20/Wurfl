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

use Wurfl\Handlers\AndroidHandler;
use Wurfl\Handlers\UcwebU3Handler;
use Wurfl\Handlers\Utils;
use Wurfl\Handlers\WindowsPhoneHandler;
use Wurfl\Handlers\Normalizer\NormalizerInterface;
use Wurfl\WurflConstants;

/**
 * User Agent Normalizer
 *
 * @package    \Wurfl\Handlers\Normalizer\Specific
 */
class UcwebU3
    implements NormalizerInterface
{
    /**
     * @param string $userAgent
     *
     * @return string
     */
    public function normalize($userAgent)
    {
        $ucbVersion = UcwebU3Handler::getUcBrowserVersion($userAgent);

        if ($ucbVersion === null) {
            return $userAgent;
        }

        // Windows Phone goes before Android
        if (Utils::checkIfContains($userAgent, 'Windows Phone')) {
            // Apply Version+Model--- matching normalization
            $model   = WindowsPhoneHandler::getWindowsPhoneModel($userAgent);
            $version = WindowsPhoneHandler::getWindowsPhoneVersion($userAgent);

            if ($model !== null && $version !== null) {
                $prefix = $version . ' U3WP ' . $ucbVersion . ' ' . $model . WurflConstants::RIS_DELIMITER;

                return $prefix . $userAgent;
            }
        } elseif (Utils::checkIfContains($userAgent, 'Android')) {
            // Android U3K Mobile + Tablet
            // Apply Version+Model--- matching normalization

            $model   = AndroidHandler::getAndroidModel($userAgent, false);
            $version = AndroidHandler::getAndroidVersion($userAgent, false);

            if ($model !== null && $version !== null) {
                $prefix = $version . ' U3Android ' . $ucbVersion . ' ' . $model . WurflConstants::RIS_DELIMITER;

                return $prefix . $userAgent;
            }
        } elseif (Utils::checkIfContains($userAgent, 'iPhone;')) {
            //iPhone U3K
            if (preg_match('/iPhone OS (\d+)(?:_(\d+))?(?:_\d+)* like/', $userAgent, $matches)) {
                $version = $matches[1] . '.' . $matches[2];
                $prefix  = $version . ' U3iPhone ' . $ucbVersion . WurflConstants::RIS_DELIMITER;

                return $prefix . $userAgent;
            }
        } elseif (Utils::checkIfContains($userAgent, 'iPad')) {
            //iPad U3K
            if (preg_match(
                '/CPU OS (\d)_?(\d)?.+like Mac.+; iPad([0-9,]+)\) AppleWebKit/',
                $userAgent,
                $matches
            )
            ) {
                $version = $matches[1] . '.' . $matches[2];
                $model   = $matches[3];
                $prefix  = $version . ' U3iPad ' . $ucbVersion . ' ' . $model . WurflConstants::RIS_DELIMITER;

                return $prefix . $userAgent;
            }
        }

        return $userAgent;
    }
}
