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

/**
 * FirefoxUserAgentHandler
 *
 * @category   WURFL
 * @package    \Wurfl\Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
class FirefoxHandler extends Handler
{
    protected $prefix = "FIREFOX";

    public static $constantIDs
        = array(
            'firefox',
        );

    public function canHandle($userAgent)
    {
        if (Utils::isMobileBrowser($userAgent)) {
            return false;
        }
        if (Utils::checkIfContainsAnyOf($userAgent, array('Tablet', 'Sony', 'Novarra', 'Opera'))) {
            return false;
        }

        return Utils::checkIfContains($userAgent, 'Firefox');
    }

    public function applyConclusiveMatch($userAgent)
    {
        return $this->getDeviceIDFromRIS($userAgent, Utils::indexOfOrLength($userAgent, '.'));
    }

    public function applyRecoveryMatch($userAgent)
    {
        return 'firefox';
    }
}