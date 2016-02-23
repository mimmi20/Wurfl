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

/**
 * OperaHandler
 *
 *
 * @category   WURFL
 *
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */
class OperaHandler
    extends AbstractHandler
{
    protected $prefix = 'OPERA';

    public static $constantIDs = array(
        'opera',
        'opera_7',
        'opera_8',
        'opera_9',
        'opera_10',
        'opera_11',
        'opera_12',
        'opera_15',
        'opera_16',
        'opera_17',
        'opera_18',
        'opera_19',
        'opera_20',
        'opera_21',
        'opera_22',
        'opera_23',
        'opera_24',
        'opera_25',
        'opera_26',
        'opera_27',
        'opera_28',
    );

    /**
     * @param string $userAgent
     *
     * @return bool
     */
    public function canHandle($userAgent)
    {
        if (Utils::isMobileBrowser($userAgent)) {
            return false;
        }

        return Utils::checkIfContainsAnyOf($userAgent, array('Opera', 'OPR/'));
    }

    /**
     * @param string $userAgent
     *
     * @return null|string
     */
    public function applyConclusiveMatch($userAgent)
    {
        $operaIndex = strpos($userAgent, 'Opera');
        $tolerance  = Utils::indexOfOrLength($userAgent, '.', $operaIndex);

        return $this->getDeviceIDFromRIS($userAgent, $tolerance);
    }

    /**
     * @param string $userAgent
     *
     * @return string
     */
    public function applyRecoveryMatch($userAgent)
    {
        $operaVersion = self::getOperaVersion($userAgent);

        if ($operaVersion === null) {
            return 'opera';
        }

        $majorVersion = floor($operaVersion);
        $id           = 'opera_' . $majorVersion;

        if (in_array($id, self::$constantIDs)) {
            return $id;
        }

        return 'opera';
    }

    /**
     * @param $userAgent
     */
    public static function getOperaVersion($userAgent)
    {
        if (preg_match('#Opera[ /]?(\d+\.\d+)#', $userAgent, $matches)) {
            return ($matches[1]);
        }

        return;
    }
}
