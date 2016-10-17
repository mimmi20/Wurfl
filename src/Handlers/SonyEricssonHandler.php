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

use UaNormalizer\Helper\Utils;

/**
 * SonyEricssonUserAgentHandler
 *
 *
 * @category   WURFL
 *
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */
class SonyEricssonHandler extends AbstractHandler
{
    protected $prefix = 'SONY_ERICSSON';

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

        $s = \Stringy\create($userAgent);

        return $s->contains('Sony');
    }

    /**
     * @param string $userAgent
     *
     * @return null|string
     */
    public function applyConclusiveMatch($userAgent)
    {
        $s = \Stringy\create($userAgent);

        if ($s->startsWith('SonyEricsson')) {
            $tolerance = Utils::firstSlash($userAgent) - 2;

            return $this->getDeviceIDFromRIS($userAgent, $tolerance);
        }

        $tolerance = Utils::secondSlash($userAgent);

        return $this->getDeviceIDFromRIS($userAgent, $tolerance);
    }
}
