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
/**
 * User Agent Normalizer
 * @package    \Wurfl\Request\Normalizer\UserAgentNormalizer_Specific
 */
class OperaMobiOrTabletOnAndroid implements \Wurfl\Request\Normalizer\NormalizerInterface {

    public function normalize($userAgent) {

        $is_opera_mobi = \Wurfl\Handlers\Utils::checkIfContains($userAgent, 'Opera Mobi');
        $is_opera_tablet = \Wurfl\Handlers\Utils::checkIfContains($userAgent, 'Opera Tablet');
        if ($is_opera_mobi || $is_opera_tablet) {
            $opera_version = \Wurfl\Handlers\OperaMobiOrTabletOnAndroidHandler::getOperaOnAndroidVersion($userAgent, false);
            $android_version = \Wurfl\Handlers\AndroidHandler::getAndroidVersion($userAgent, false);
            if ($opera_version !== null && $android_version !== null) {
                $opera_model = $is_opera_tablet? 'Opera Tablet': 'Opera Mobi';
                $prefix = $opera_model.' '.$opera_version.' Android '.$android_version.\Wurfl\Constants::RIS_DELIMITER;
                return $prefix.$userAgent;
            }
        }

        return $userAgent;
    }
}