<?php
namespace Wurfl\Request\UserAgentNormalizer\Specific;

/**
 * Copyright (c) 2012 ScientiaMobile, Inc.
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 * Refer to the COPYING.txt file distributed with this package.
 *
 * @category   WURFL
 * @package    WURFL_Request_UserAgentNormalizer_Specific
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @author     Fantayeneh Asres Gizaw
 * @version    $id$
 */

use Wurfl\Constants;
use Wurfl\Handlers\UcwebU3Handler;
use Wurfl\Handlers\Utils;
use Wurfl\Request\UserAgentNormalizer\NormalizerInterface;

/**
 * User Agent Normalizer
 *
 * @package    WURFL_Request_UserAgentNormalizer_Specific
 */
class UcwebU2 implements NormalizerInterface
{
    public function normalize($userAgent)
    {

        $ucb_version = UcwebU3Handler::getUcBrowserVersion($userAgent);
        if ($ucb_version === null) {
            return $userAgent;
        }

        //Android U2K Mobile + Tablet
        if (Utils::checkIfContains($userAgent, 'Adr ')) {

            $model   = UcwebU3Handler::getUcAndroidModel($userAgent, false);
            $version = UcwebU3Handler::getUcAndroidVersion($userAgent, false);

            if ($model !== null && $version !== null) {
                $prefix = "$version U2Android $ucb_version $model" . Constants::RIS_DELIMITER;

                return $prefix . $userAgent;
            }
        } //iPhone U2K
        else {
            if (Utils::checkIfContains($userAgent, 'iPh OS')) {
                if (preg_match('/iPh OS (\d)_?(\d)?[ _\d]?.+; iPh(\d), ?(\d)\) U2/', $userAgent, $matches)) {
                    $version = $matches[1] . '.' . $matches[2];
                    $model   = $matches[3] . '.' . $matches[4];
                    $prefix  = "$version U2iPhone $ucb_version $model" . Constants::RIS_DELIMITER;

                    return $prefix . $userAgent;
                }
            } //WP7&8 U2K
            else {
                if (Utils::checkIfContains($userAgent, 'wds')) {
                    //Add spaces and normalize
                    $userAgent = preg_replace('|;(?! )|', '; ', $userAgent);

                    if (preg_match(
                        '/^UCWEB.+; wds (\d+)\.([\d]+);.+; ([ A-Za-z0-9_-]+); ([ A-Za-z0-9_-]+)\) U2/',
                        $userAgent,
                        $matches
                    )
                    ) {
                        $version = $matches[1] . '.' . $matches[2];
                        $model   = $matches[3] . '.' . $matches[4];

                        //Standard normalization stuff from WP matcher
                        $model  = str_replace('_blocked', '', $model);
                        $model  = preg_replace('/(NOKIA.RM-.+?)_.*/', '$1', $model, 1);
                        $prefix = "$version U2WindowsPhone $ucb_version $model" . Constants::RIS_DELIMITER;

                        return $prefix . $userAgent;
                    }
                } //Symbian U2K
                else {
                    if (Utils::checkIfContains($userAgent, 'Symbian')) {
                        if (preg_match('/^UCWEB.+; S60 V(\d); .+; (.+)\) U2/', $userAgent, $matches)) {
                            $version = 'S60 V' . $matches[1];
                            $model   = $matches[2];
                            $prefix  = "$version U2Symbian $ucb_version $model" . Constants::RIS_DELIMITER;

                            return $prefix . $userAgent;
                        }
                    } //Java U2K - check results for regex
                    else {
                        if (Utils::checkIfContains($userAgent, 'Java')) {
                            if (preg_match('/^UCWEB[^\(]+\(Java; .+; (.+)\) U2/', $userAgent, $matches)) {
                                $version = 'Java';
                                $model   = $matches[1];
                                $prefix  = "$version U2JavaApp $ucb_version $model" . Constants::RIS_DELIMITER;

                                return $prefix . $userAgent;
                            }
                        }
                    }
                }
            }
        }

        return $userAgent;
    }
}