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
 * ChromeUserAgentHandler
 *
 *
 * @category   WURFL
 *
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */
class ChromeHandler extends AbstractHandler
{
    protected $prefix = 'CHROME';

    public static $constantIDs = array(
        'google_chrome',
    );

    public function canHandle($userAgent)
    {
        if (Utils::isMobileBrowser($userAgent)) {
            return false;
        }

        $s = \Stringy\create($userAgent);

        return $s->contains('Chrome');
    }

    public function applyConclusiveMatch($userAgent)
    {
        $tolerance = Utils::indexOfOrLength($userAgent, '.');

        return $this->getDeviceIDFromRIS($userAgent, $tolerance);
    }

    public function applyRecoveryMatch($userAgent)
    {
        return 'google_chrome';
    }
}
