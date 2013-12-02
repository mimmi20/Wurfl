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
 * User Agent Normalizer - Trims the version number to two digits (e.g. 2.1.1 -> 2.1)
 * @package    \Wurfl\Request\Normalizer\UserAgentNormalizer_Specific
 */
class Android implements \Wurfl\Request\Normalizer\NormalizerInterface {

    private $skip_normalization = array(
            'Opera Mini',
            'Fennec',
            'Firefox',
            'UCWEB7',
            'NetFrontLifeBrowser/2.2',
        );

    public function normalize($userAgent) {
        // Normalize Android version
        $userAgent = preg_replace('/(Android)[ \-](\d\.\d)([^; \/\)]+)/', '$1 $2', $userAgent);

        // Opera Mobi/Tablet
        $is_opera_mobi = \Wurfl\Handlers\Utils::checkIfContains($userAgent, 'Opera Mobi');
        $is_opera_tablet = \Wurfl\Handlers\Utils::checkIfContains($userAgent, 'Opera Tablet');
        if ($is_opera_mobi || $is_opera_tablet) {
            $opera_version = \Wurfl\Handlers\AndroidHandler::getOperaOnAndroidVersion($userAgent, false);
            $android_version = \Wurfl\Handlers\AndroidHandler::getAndroidVersion($userAgent, false);
            if ($opera_version !== null && $android_version !== null) {
                $opera_model = $is_opera_tablet? 'Opera Tablet': 'Opera Mobi';
                $prefix = $opera_model.' '.$opera_version.' Android '.$android_version.\Wurfl\Constants::RIS_DELIMITER;
                return $prefix.$userAgent;
            }
        }

        // Stock Android
        if (!\Wurfl\Handlers\Utils::checkIfContainsAnyOf($userAgent, $this->skip_normalization)) {
            $model = \Wurfl\Handlers\AndroidHandler::getAndroidModel($userAgent, false);
            $version = \Wurfl\Handlers\AndroidHandler::getAndroidVersion($userAgent, false);
            if ($model !== null && $version !== null) {
                $prefix = $version.' '.$model.\Wurfl\Constants::RIS_DELIMITER;
                return $prefix.$userAgent;
            }
        }
        return $userAgent;
    }
}