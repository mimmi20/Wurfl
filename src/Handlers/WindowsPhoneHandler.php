<?php
/**
 * Copyright (c) 2015 ScientiaMobile, Inc.
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
 *
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */

namespace Wurfl\Handlers;

use Wurfl\WurflConstants;

/**
 * WindowsPhoneUserAgentHandler
 *
 *
 * @category   WURFL
 *
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */
class WindowsPhoneHandler
    extends AbstractHandler
{
    /**
     * @var string
     */
    protected $prefix = 'WINDOWSPHONE';

    /**
     * @var array
     */
    public static $constantIDs = array(
        'generic_ms_winmo6_5',
        'generic_ms_phone_os7',
        'generic_ms_phone_os7_5',
        'generic_ms_phone_os7_8',
        'generic_ms_phone_os8',
        'generic_ms_phone_os8_1',
        'generic_ms_phone_os10',
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

        return Utils::checkIfContainsAnyOf($userAgent, array('Windows Phone', 'WindowsPhone', 'NativeHost'));
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
        if (Utils::checkIfContains($userAgent, 'NativeHost')) {
            return 'generic_ms_phone_os7';
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
        $version = self::getWindowsPhoneVersion($userAgent);

        if ($version === '10.0') {
            return 'generic_ms_phone_os10';
        }

        if ($version === '8.1') {
            return 'generic_ms_phone_os8_1';
        }

        if ($version === '8.0') {
            return 'generic_ms_phone_os8';
        }

        if ($version === '7.8') {
            return 'generic_ms_phone_os7_8';
        }

        if ($version === '7.5') {
            return 'generic_ms_phone_os7_5';
        }

        if ($version === '7.0') {
            return 'generic_ms_phone_os7';
        }

        if ($version === '6.5') {
            return 'generic_ms_winmo6_5';
        }

        return WurflConstants::NO_MATCH;
    }

    /**
     * @param string $userAgent
     *
     * @return mixed|null
     */
    public static function getWindowsPhoneModel($userAgent)
    {
        // Normalize spaces in UA before capturing parts
        $userAgent = preg_replace('|;(?! )|', '; ', $userAgent);
        // This regex is relatively fast because there is not much backtracking, and almost all UAs will match
        if (preg_match('|IEMobile/\d+\.\d+;(?: ARM;)?(?: Touch;)? ?([^;\)]+(; ?[^;\)]+)?)|', $userAgent, $matches)
            || preg_match('|Android [\d\.]+?; ([^;\)]+(; ?[^;\)]+)?).+?Edge/\d|', $userAgent, $matches)
        ) {
            $model = $matches[1];
            // Some UAs contain '_blocked' and that string causes matching errors:
            //   Mozilla/4.0 (compatible; MSIE 7.0; Windows Phone OS 7.5; Trident/3.1; IEMobile/7.0; LG_blocked; LG-E900)
            //   Mozilla/5.0 (compatible; MSIE 9.0; Windows Phone OS 7.5; Trident/5.0; IEMobile/9.0; SAMSUNG_blocked_blocked_blocked_blocked; SGH-i937)
            //   Mozilla/5.0 (compatible; MSIE 9.0; Windows Phone OS 7.5; Trident/5.0; IEMobile/9.0; SAMSUNG_blocked_blocked; SGH-i917)
            //   Mozilla/5.0 (compatible; MSIE 9.0; Windows Phone OS 7.5; Trident/5.0; IEMobile/9.0; SAMSUNG_blocked_blocked; SGH-i937)
            //   Mozilla/5.0 (compatible; MSIE 9.0; Windows Phone OS 7.5; Trident/5.0; IEMobile/9.0; SAMSUNG_blocked; OMNIA7)
            //   Mozilla/5.0 (compatible; MSIE 9.0; Windows Phone OS 7.5; Trident/5.0; IEMobile/9.0; SAMSUNG_blocked; SGH-i917)
            $model = str_replace('_blocked', '', $model);

            // Nokia Windows Phone 7.5/8 'RM-' devices make matching particularly difficult:
            //   Mozilla/5.0 (compatible; MSIE 9.0; Windows Phone OS 7.5; Trident/5.0; IEMobile/9.0; NOKIA; RM-821_eu_euro1)
            //   Mozilla/5.0 (compatible; MSIE 9.0; Windows Phone OS 7.5; Trident/5.0; IEMobile/9.0; NOKIA; RM-821_eu_euro2_248)
            //   Mozilla/5.0 (compatible; MSIE 9.0; Windows Phone OS 7.5; Trident/5.0; IEMobile/9.0; NOKIA; RM-824_nam_att_100)
            //   Mozilla/5.0 (compatible; MSIE 10.0; Windows Phone 8.0; Trident/6.0; IEMobile/10.0; ARM; Touch; NOKIA; RM-821_eu_euro1_276)
            //   Mozilla/5.0 (compatible; MSIE 10.0; Windows Phone 8.0; Trident/6.0; IEMobile/10.0; ARM; Touch; NOKIA; RM-821_eu_euro1_292)
            //   Mozilla/5.0 (compatible; MSIE 10.0; Windows Phone 8.0; Trident/6.0; IEMobile/10.0; ARM; Touch; NOKIA; RM-821_eu_euro2_224)
            //   Mozilla/5.0 (compatible; MSIE 10.0; Windows Phone 8.0; Trident/6.0; IEMobile/10.0; ARM; Touch; NOKIA; RM-821_eu_euro2_248)
            //   Mozilla/5.0 (compatible; MSIE 10.0; Windows Phone 8.0; Trident/6.0; IEMobile/10.0; ARM; Touch; NOKIA; RM-821_eu_sweden_235)
            $model = preg_replace('/(NOKIA; RM-.+?)_.*/', '$1', $model, 1);

            return $model;
        }

        return;
    }

    /**
     * @param $ua
     *
     * @return mixed|null
     */
    public static function getWindowsPhoneAdClientModel($ua)
    {
        // Normalize spaces in UA before capturing parts
        $ua = preg_replace('|;(?! )|', '; ', $ua);
        if (preg_match(
            '|Windows ?Phone ?Ad ?Client/[0-9\.]+ ?\(.+; ?Windows ?Phone(?: ?OS)? ?[0-9\.]+; ?([^;\)]+(; ?[^;\)]+)?)|',
            $ua,
            $matches
        )
        ) {
            $model = $matches[1];
            $model = str_replace('_blocked', '', $model);
            $model = preg_replace('/(NOKIA; RM-.+?)_.*/', '$1', $model, 1);

            return $model;
        }

        return;
    }

    /**
     * @param $ua
     *
     * @return null|string
     */
    public static function getWindowsPhoneVersion($ua)
    {
        if (preg_match('|Windows ?Phone(?: ?OS)? ?(\d+\.\d+)|', $ua, $matches)) {
            if (strpos($matches[1], '10.0') !== false) {
                return '10.0';
            } elseif (strpos($matches[1], '6.3') !== false || strpos($matches[1], '8.1') !== false) {
                return '8.1';
            } elseif (strpos($matches[1], '8.') !== false) {
                return '8.0';
            } elseif (strpos($matches[1], '7.8') !== false) {
                return '7.8';
            } elseif (strpos($matches[1], '7.10') !== false || strpos($matches[1], '7.5') !== false) {
                return '7.5';
            } elseif (strpos($matches[1], '6.5') !== false) {
                return '6.5';
            } else {
                return '7.0';
            }
        }

        return;
    }
}
