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
 *
 * @category   WURFL
 * @package    WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
use Wurfl\Constants;

/**
 * XboxUserAgentHandler
 *
 *
 * @category   WURFL
 * @package    WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
class XboxHandler extends AbstractHandler
{

    protected $prefix = "XBOX";

    public static $constantIDs
        = array(
            'microsoft_xbox360_ver1',
            'microsoft_xbox360_ver1_subie10',
            'microsoft_xboxone_ver1',
        );

    public function canHandle($userAgent)
    {
        return Utils::checkIfContains($userAgent, 'Xbox');
    }

    public function applyConclusiveMatch($userAgent)
    {
        // Exact and recovery matching only
        return Constants::NO_MATCH;
    }

    public function applyRecoveryMatch($userAgent)
    {
        if (Utils::checkIfContains($userAgent, 'MSIE 10.0')
            && Utils::checkIfContains(
                $userAgent, 'Xbox One'
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
