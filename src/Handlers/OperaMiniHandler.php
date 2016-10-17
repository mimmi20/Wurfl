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

namespace Wurfl\Handlers;

use UaNormalizer\Helper\Utils;

/**
 * OperaHandlder
 *
 *
 * @category   WURFL
 *
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */
class OperaMiniHandler extends AbstractHandler
{
    protected $prefix = 'OPERA_MINI';

    public static $constantIDs = array(
        'Opera Mini/1' => 'generic_opera_mini_version1',
        'Opera Mini/2' => 'generic_opera_mini_version2',
        'Opera Mini/3' => 'generic_opera_mini_version3',
        'Opera Mini/4' => 'generic_opera_mini_version4',
        'Opera Mini/5' => 'generic_opera_mini_version5',
        'Opera Mini/6' => 'generic_opera_mini_version6',
        'Opera Mini/7' => 'generic_opera_mini_version7',
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

        $s = \Stringy\create($userAgent);

        return $s->containsAny(array('Opera Mini', 'OperaMini', 'Opera Mobi', 'OperaMobi'));
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

        $operaMiniIndex = Utils::indexOfOrLength($userAgent, 'Opera Mini');

        if ($operaMiniIndex !== false) {
            // Match up to the first '.' after 'Opera Mini'
            $tolerance = strpos($userAgent, '.', $operaMiniIndex);

            if ($tolerance !== false) {
                // +1 to match just after the '.'
                return $this->getDeviceIDFromRIS($userAgent, $tolerance + 1);
            }
        }

        $tolerance = Utils::firstSlash($userAgent);

        return $this->getDeviceIDFromRIS($userAgent, $tolerance);
    }

    /**
     * @param string $userAgent
     *
     * @return string
     */
    public function applyRecoveryMatch($userAgent)
    {
        $s = \Stringy\create($userAgent);

        foreach (self::$constantIDs as $keyword => $deviceId) {
            if ($s->contains($keyword)) {
                return $deviceId;
            }
        }

        if ($s->contains('Opera Mobi')) {
            return 'generic_opera_mini_version4';
        }

        return 'generic_opera_mini_version1';
    }

    /**
     * Get the model name from the provided user agent or null if it cannot be determined
     *
     * @param string $ua
     *
     * @return false|string
     */
    public static function getOperaModel($ua)
    {
        if (preg_match('#^Opera/[\d\.]+ .+?\d{3}X\d{3} (.+)$#', $ua, $matches)) {
            return $matches[1];
        }

        return false;
    }
}
