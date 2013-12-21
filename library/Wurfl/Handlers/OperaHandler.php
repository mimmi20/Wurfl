<?php
namespace Wurfl\Handlers;

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
     * @package    WURFL_Handlers
     * @copyright  ScientiaMobile, Inc.
     * @license    GNU Affero General Public License
     * @version    $id$
     */

/**
 * OperaHandler
 *
 *
 * @category   WURFL
 * @package    WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
class OperaHandler extends AbstractHandler
{

    protected $prefix = "OPERA";

    public static $constantIDs
        = array(
            'opera',
            'opera_7',
            'opera_8',
            'opera_9',
            'opera_10',
            'opera_11',
            'opera_12',
        );

    public function canHandle($userAgent)
    {
        if (Utils::isMobileBrowser($userAgent)) {
            return false;
        }

        return Utils::checkIfContains($userAgent, 'Opera');
    }

    public function applyConclusiveMatch($userAgent)
    {
        $operaIndex = strpos($userAgent, 'Opera');
        $tolerance  = Utils::indexOfOrLength($userAgent, '.', $operaIndex);

        return $this->getDeviceIDFromRIS($userAgent, $tolerance);
    }

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

    public static function getOperaVersion($userAgent)
    {
        if (preg_match('#Opera[ /]?(\d+\.\d+)#', $userAgent, $matches)) {
            return ($matches[1]);
        }

        return null;
    }
}
