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
 * MaemoUserAgentHandler
 *
 *
 * @category   WURFL
 *
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */
class MaemoHandler
    extends AbstractHandler
{
    protected $prefix = 'MAEMO';

    public static $constantIDs = array(
        'generic_opera_mobi_maemo',
        'nokia_generic_maemo_with_firefox',
        'nokia_generic_maemo',
    );

    /**
     * @param string $userAgent
     *
     * @return bool
     */
    public function canHandle($userAgent)
    {
        return Utils::checkIfContains($userAgent, 'Maemo');
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

        $tolerance = Utils::firstSlash($userAgent);

        return $this->getDeviceIDFromLD($userAgent, $tolerance);
    }

    /**
     * @param string $userAgent
     *
     * @return string
     */
    public function applyRecoveryMatch($userAgent)
    {
        if (Utils::checkIfContains($userAgent, 'Opera Mobi')) {
            return 'generic_opera_mobi_maemo';
        }
        if (Utils::checkIfContains($userAgent, 'Firefox')) {
            return 'nokia_generic_maemo_with_firefox';
        }

        return 'nokia_generic_maemo';
    }

    /**
     * @param $userAgent
     *
     * @return null|string
     */
    public static function getMaemoModel($userAgent)
    {
        if (preg_match('/Maemo [bB]rowser [\d\.]+ (.+)/', $userAgent, $matches)) {
            $model = $matches[1];
            $idx   = strpos($model, ' GTB');

            if ($idx !== false) {
                $model = substr($model, 0, $idx);
            }

            return $model;
        }

        return WurflConstants::NO_MATCH;
    }
}
