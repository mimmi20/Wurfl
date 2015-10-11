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
 * SafariHandler
 *
 *
 * @category   WURFL
 * @package    WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */
class SafariHandler extends AbstractHandler
{
    protected $prefix = 'SAFARI';

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

        return Utils::checkIfContains($userAgent, 'Safari') && Utils::checkIfStartsWithAnyOf(
            $userAgent,
            array('Mozilla/5.0 (Macintosh', 'Mozilla/5.0 (Windows')
        );
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
        if (Utils::checkIfContainsAnyOf(
            $userAgent,
            array('Macintosh', 'Windows')
        )
        ) {
            return WurflConstants::GENERIC_WEB_BROWSER;
        }

        return WurflConstants::NO_MATCH;
    }

    /**
     * @param $userAgent
     *
     * @return null|string
     */
    public static function getSafariVersion($userAgent)
    {
        $search = 'Version/';
        $idx    = strpos($userAgent, $search);

        if ($idx === false) {
            return null;
        }

        $idx += strlen($search);
        $endIdx = strpos($userAgent, '.', $idx);

        if ($endIdx === false) {
            return null;
        }

        return substr($userAgent, $idx, $endIdx - $idx);
    }
}
