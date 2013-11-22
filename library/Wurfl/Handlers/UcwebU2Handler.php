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
 * @package    WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */

use Wurfl\Constants;

/**
 * UcwebU2UserAgentHandler
 *
 * @category   WURFL
 * @package    WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
class UcwebU2Handler extends Handler
{
    protected $prefix = "UCWEBU2";

    public static $constantIDs
        = array(
            'generic_ucweb',
        );

    public function canHandle($userAgent)
    {
        if (Utils::isDesktopBrowser($userAgent)) {
            return false;
        }

        return (Utils::checkIfStartsWith($userAgent, 'UCWEB') && Utils::checkIfContains($userAgent, 'UCBrowser'));
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
        return 'generic_ucweb';
    }
}
