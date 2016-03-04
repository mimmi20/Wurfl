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
 * BenQUserAgentHandler
 *
 *
 * @category   WURFL
 *
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */
class BenQHandler extends AbstractHandler
{
    protected $prefix = 'BENQ';

    public function canHandle($userAgent)
    {
        if (Utils::isDesktopBrowser($userAgent)) {
            return false;
        }

        return Utils::checkIfStartsWith($userAgent, 'BenQ') || Utils::checkIfStartsWith($userAgent, 'BENQ');
    }

    public function applyConclusiveMatch($userAgent)
    {
        $tolerance = Utils::firstSlash($userAgent);
        return $this->getDeviceIDFromRIS($userAgent, $tolerance);
    }
}
