<?php
namespace Wurfl\Request\Normalizer\Specific;

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
 * @category   WURFL
 * @package    \Wurfl\Request\Normalizer\UserAgentNormalizer_Specific
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @author     Fantayeneh Asres Gizaw
 * @version    $id$
 */
use Wurfl\Constants;
use Wurfl\Handlers\Utils;
use Wurfl\Handlers\WindowsPhoneHandler;
use Wurfl\Request\Normalizer\NormalizerInterface;

/**
 * User Agent Normalizer
 *
 * @package    \Wurfl\Request\Normalizer\UserAgentNormalizer_Specific
 */
class WindowsPhone implements NormalizerInterface
{
    public function normalize($userAgent)
    {
        if (Utils::checkIfStartsWith($userAgent, 'Windows Phone Ad Client')) {
            $model   = WindowsPhoneHandler::getWindowsPhoneAdClientModel($userAgent);
            $version = WindowsPhoneHandler::getWindowsPhoneAdClientVersion($userAgent);
        } else {
            if (Utils::checkIfContains($userAgent, 'NativeHost')) {
                return $userAgent;
            } else {
                $model   = WindowsPhoneHandler::getWindowsPhoneModel($userAgent);
                $version = WindowsPhoneHandler::getWindowsPhoneVersion($userAgent);
            }
        }

        if ($model !== null && $version !== null) {
            $prefix = 'WP' . $version . ' ' . $model . Constants::RIS_DELIMITER;
            return $prefix . $userAgent;
        }
        return $userAgent;
    }
}