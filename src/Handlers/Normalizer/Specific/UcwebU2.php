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

namespace Wurfl\Handlers\Normalizer\Specific;

use Wurfl\Handlers\Normalizer\NormalizerInterface;
use Wurfl\Handlers\UcwebU3Handler;
use Wurfl\Handlers\Utils;
use Wurfl\WurflConstants;

/**
 * User Agent Normalizer
 */
class UcwebU2 implements NormalizerInterface
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

        //Android U2K Mobile + Tablet
        if (Utils::checkIfContains($userAgent, 'Adr ')) {
            $model   = UcwebU3Handler::getUcAndroidModel($userAgent, false);
            $version = UcwebU3Handler::getUcAndroidVersion($userAgent, false);

            if ($model !== null && $version !== null) {
                $prefix = $version . ' U2Android ' . $ucbVersion . ' ' . $model . WurflConstants::RIS_DELIMITER;

                return $prefix . $userAgent;
            }
        } elseif (Utils::checkIfContains($userAgent, 'iPh OS')) {
            //iPhone U2K
            if (preg_match('/iPh OS (\d)_?(\d)?[ _\d]?.+; iPh(\d), ?(\d)\) U2/', $userAgent, $matches)) {
                $version = $matches[1] . '.' . $matches[2];
                $model   = $matches[3] . '.' . $matches[4];
                $prefix  = $version . ' U2iPhone ' . $ucbVersion . ' ' . $model . WurflConstants::RIS_DELIMITER;

                return $prefix . $userAgent;
            }
        } elseif (Utils::checkIfContains($userAgent, 'wds')) {
            //WP7&8 U2K
            //Add spaces and normalize
            $userAgent = preg_replace('|;(?! )|', '; ', $userAgent);
            if (preg_match(
                '/^UCWEB.+; wds (\d+)\.([\d]+);.+; ([ A-Za-z0-9_-]+); ([ A-Za-z0-9_-]+)\) U2/',
                $userAgent,
                $matches
            )) {
                $version = $matches[1] . '.' . $matches[2];
                $model   = $matches[3] . '.' . $matches[4];
                //Standard normalization stuff from WP matcher
                $model  = str_replace('_blocked', '', $model);
                $model  = preg_replace('/(NOKIA.RM-.+?)_.*/', '$1', $model, 1);
                $prefix = $version . ' U2WindowsPhone ' . $ucbVersion . ' ' . $model . WurflConstants::RIS_DELIMITER;

                return $prefix . $userAgent;
            }
        } elseif (Utils::checkIfContains($userAgent, 'Symbian')) {
            //Symbian U2K
            if (preg_match('/^UCWEB.+; S60 V(\d); .+; (.+)\) U2/', $userAgent, $matches)) {
                $version = 'S60 V' . $matches[1];
                $model   = $matches[2];
                $prefix  = $version . ' U2Symbian ' . $ucbVersion . ' ' . $model . WurflConstants::RIS_DELIMITER;

                return $prefix . $userAgent;
            }
        } elseif (Utils::checkIfContains($userAgent, 'Java')) {
            //Java U2K - check results for regex
            if (preg_match('/^UCWEB[^\(]+\(Java; .+; (.+)\) U2/', $userAgent, $matches)) {
                $version = 'Java';
                $model   = $matches[1];
                $prefix  = $version . ' U2JavaApp ' . $ucbVersion . ' ' . $model . WurflConstants::RIS_DELIMITER;

                return $prefix . $userAgent;
            }
        }

        return $userAgent;
    }
}
