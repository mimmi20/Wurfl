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
 *
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */

namespace Wurfl\Handlers;

/**
 * SamsungUserAgentHandler
 *
 *
 * @category   WURFL
 *
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */
class SamsungHandler extends AbstractHandler
{
    protected $prefix = 'SAMSUNG';

    /**
     * @param string $userAgent
     *
     * @return bool
     */
    public function canHandle($userAgent)
    {
        if (Utils::isDesktopBrowser($userAgent)) {
            return false;
        }

        return Utils::checkIfContainsCaseInsensitive($userAgent, 'samsung')
            || Utils::checkIfStartsWithAnyOf($userAgent, array('SEC-', 'SPH', 'SGH', 'SCH'));
    }

    /**
     * @param string $userAgent
     *
     * @return null|string
     */
    public function applyConclusiveMatch($userAgent)
    {
        if (Utils::checkIfStartsWithAnyOf($userAgent, array('SEC-', 'SAMSUNG-', 'SCH'))) {
            $tolerance = Utils::firstSlash($userAgent);
        } else {
            if (Utils::checkIfStartsWithAnyOf($userAgent, array('Samsung', 'SPH', 'SGH'))) {
                $tolerance = Utils::firstSpace($userAgent);
            } else {
                $tolerance = Utils::secondSlash($userAgent);
            }
        }

        return $this->getDeviceIDFromRIS($userAgent, $tolerance);
    }

    /**
     * @param string $userAgent
     *
     * @return null|string
     */
    public function applyRecoveryMatch($userAgent)
    {
        $tolerance = Utils::indexOfOrLength($userAgent, '/', strpos($userAgent, 'Samsung'));

        return $this->getDeviceIDFromRIS($userAgent, $tolerance);
    }
}
