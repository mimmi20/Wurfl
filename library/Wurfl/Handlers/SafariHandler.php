<?php
namespace Wurfl\Handlers;

/**
 * Copyright (c) 2012 ScientiaMobile, Inc.
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 * Refer to the COPYING.txt file distributed with this package.
 *
 * @category   WURFL
 * @package    \Wurfl\Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */

use Wurfl\Constants;

/**
 * SafariHandler
 *
 * @category   WURFL
 * @package    \Wurfl\Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
class SafariHandler extends Handler
{

    protected $prefix = "SAFARI";

    public function canHandle($userAgent)
    {
        if (Utils::isMobileBrowser($userAgent)) {
            return false;
        }

        return Utils::checkIfContains($userAgent, 'Safari')
        && Utils::checkIfStartsWithAnyOf($userAgent, array('Mozilla/5.0 (Macintosh', 'Mozilla/5.0 (Windows'));
    }

    public function applyConclusiveMatch($userAgent)
    {
        $tolerance = Utils::toleranceToRisDelimeter($userAgent);
        if ($tolerance !== false) {
            return $this->getDeviceIDFromRIS($userAgent, $tolerance);
        }

        return Constants::NO_MATCH;
    }

    public function applyRecoveryMatch($userAgent)
    {
        if (Utils::checkIfContainsAnyOf(
            $userAgent, array('Macintosh', 'Windows')
        )
        ) {
            return Constants::GENERIC_WEB_BROWSER;
        }

        return Constants::NO_MATCH;
    }

    public static function getSafariVersion($ua)
    {
        $search = 'Version/';
        $idx    = strpos($ua, $search) + strlen($search);
        if ($idx === false) {
            return null;
        }
        $end_idx = strpos($ua, '.', $idx);
        if ($end_idx === false) {
            return null;
        }

        return substr($ua, $idx, $end_idx - $idx);
    }
}