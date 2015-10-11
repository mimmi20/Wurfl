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

namespace Wurfl\Handlers;

use Wurfl\WurflConstants;

/**
 * OperaMobiOrTabletOnAndroidUserAgentHandler
 *
 *
 * @category   WURFL
 * @package    WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */
class OperaMobiOrTabletOnAndroidHandler extends AbstractHandler
{

    protected $prefix = 'OPERAMOBIORTABLETONANDROID';

    public static $constantIDs = array(
        'generic_android_ver1_5_opera_mobi',
        'generic_android_ver1_6_opera_mobi',
        'generic_android_ver2_0_opera_mobi',
        'generic_android_ver2_1_opera_mobi',
        'generic_android_ver2_2_opera_mobi',
        'generic_android_ver2_3_opera_mobi',
        'generic_android_ver4_0_opera_mobi',
        'generic_android_ver4_1_opera_mobi',
        'generic_android_ver4_2_opera_mobi',
        'generic_android_ver2_1_opera_tablet',
        'generic_android_ver2_2_opera_tablet',
        'generic_android_ver2_3_opera_tablet',
        'generic_android_ver3_0_opera_tablet',
        'generic_android_ver3_1_opera_tablet',
        'generic_android_ver3_2_opera_tablet',
        'generic_android_ver4_0_opera_tablet',
        'generic_android_ver4_1_opera_tablet',
        'generic_android_ver4_2_opera_tablet',
    );

    /**
     * @param string $userAgent
     *
     * @return bool
     */
    public function canHandle($userAgent)
    {
        if (Utils::isDesktopBrowser($userAgent)) {
            return false;
        }

        return (Utils::checkIfContains($userAgent, 'Android') && Utils::checkIfContainsAnyOf(
            $userAgent,
            array('Opera Mobi', 'Opera Tablet')
        ));
    }

    /**
     * @param string $userAgent
     *
     * @return null|string
     */
    public function applyConclusiveMatch($userAgent)
    {
        $tolerance = Utils::toleranceToRisDelimeter($userAgent);

        if ($tolerance !== false) {
            return $this->getDeviceIDFromRIS($userAgent, $tolerance);
        }

        return WurflConstants::NO_MATCH;
    }

    /**
     * @param string $userAgent
     *
     * @return null|string
     */
    public function applyRecoveryMatch($userAgent)
    {
        $isOperaMobi   = Utils::checkIfContains($userAgent, 'Opera Mobi');
        $isOperaTablet = Utils::checkIfContains($userAgent, 'Opera Tablet');

        if ($isOperaMobi || $isOperaTablet) {
            $androidVersion       = AndroidHandler::getAndroidVersion($userAgent);
            $androidVersionString = str_replace('.', '_', $androidVersion);
            $type                 = $isOperaTablet ? 'tablet' : 'mobi';
            $deviceID             = 'generic_android_ver' . $androidVersionString . '_opera_' . $type;

            if (in_array($deviceID, self::$constantIDs)) {
                return $deviceID;
            } else {
                return $isOperaTablet ? 'generic_android_ver2_1_opera_tablet' : 'generic_android_ver2_0_opera_mobi';
            }
        }

        return WurflConstants::NO_MATCH;
    }

    const OPERA_DEFAULT_VERSION = '10';

    public static $validOperaVersions = array('10', '11', '12');

    /**
     * Get the Opera browser version from an Opera Android user agent
     *
     * @param string  $userAgent  User Agent
     * @param boolean $useDefault Return the default version on fail, else return null
     *
     * @return string Opera version
     * @see self::$defaultOperaVersion
     */
    public static function getOperaOnAndroidVersion($userAgent, $useDefault = true)
    {
        if (preg_match('/Version\/(\d\d)/', $userAgent, $matches)) {
            $version = $matches[1];

            if (in_array($version, self::$validOperaVersions)) {
                return $version;
            }
        }

        return $useDefault ? self::OPERA_DEFAULT_VERSION : null;
    }
}
