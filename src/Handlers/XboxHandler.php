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
 * XboxUserAgentHandler
 *
 *
 * @category   WURFL
 * @package    WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */
class XboxHandler extends AbstractHandler
{
    protected $prefix = 'XBOX';

    public static $constantIDs = array(
        'microsoft_xbox360_ver1',
        'microsoft_xbox360_ver1_subie10',
        'microsoft_xboxone_ver1',
    );

    /**
     * @param string $userAgent
     *
     * @return bool
     */
    public function canHandle($userAgent)
    {
        return Utils::checkIfContains($userAgent, 'Xbox');
    }

    /**
     * @param string $userAgent
     *
     * @return null|string
     */
    public function applyConclusiveMatch($userAgent)
    {
        // Exact and recovery matching only
        return WurflConstants::NO_MATCH;
    }

    /**
     * @param string $userAgent
     *
     * @return string
     */
    public function applyRecoveryMatch($userAgent)
    {
        if (Utils::checkIfContains($userAgent, 'MSIE 10.0') && Utils::checkIfContains(
            $userAgent,
            'Xbox One'
        )
        ) {
            return 'microsoft_xboxone_ver1';
        }
        if (Utils::checkIfContains($userAgent, 'MSIE 10.0')) {
            return 'microsoft_xbox360_ver1_subie10';
        }

        return 'microsoft_xbox360_ver1';
    }
}
