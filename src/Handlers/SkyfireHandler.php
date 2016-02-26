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
 * SanyoUserAgentHandler
 *
 *
 * @category   WURFL
 *
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */
class SkyfireHandler extends AbstractHandler
{
    protected $prefix = 'SKYFIRE';

    public static $constantIDs = array(
        'generic_skyfire_version1',
        'generic_skyfire_version2',
    );

    /**
     * @param string $userAgent
     *
     * @return bool
     */
    public function canHandle($userAgent)
    {
        return Utils::checkIfContains($userAgent, 'Skyfire');
    }

    /**
     * @param string $userAgent
     *
     * @return null|string
     */
    public function applyConclusiveMatch($userAgent)
    {
        $skyfireIndex = strpos($userAgent, 'Skyfire');

        // Matches the first decimal point after the Skyfire keyword: Skyfire/2.0
        return $this->getDeviceIDFromRIS($userAgent, Utils::indexOfOrLength($userAgent, '.', $skyfireIndex));
    }

    /**
     * @param string $userAgent
     *
     * @return string
     */
    public function applyRecoveryMatch($userAgent)
    {
        if (Utils::checkIfContains($userAgent, 'Skyfire/2.')) {
            return 'generic_skyfire_version2';
        }

        return 'generic_skyfire_version1';
    }
}
