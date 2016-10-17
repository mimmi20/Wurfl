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
 * NokiaOviBrowserUserAgentHandler
 *
 *
 * @category   WURFL
 *
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */
class NokiaOviBrowserHandler extends AbstractHandler
{
    protected $prefix = 'NOKIAOVIBROWSER';

    public static $constantIDs = array(
        'nokia_generic_series30plus',
        'nokia_generic_series40_ovibrosr',
    );

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

        return $s->contains('S40OviBrowser');
    }

    /**
     * @param string $userAgent
     *
     * @return null|string
     */
    public function applyConclusiveMatch($userAgent)
    {
        $idx       = strpos($userAgent, 'Nokia');
        $tolerance = Utils::indexOfAnyOrLength($userAgent, array('/', ' '), $idx);

        return $this->getDeviceIDFromRIS($userAgent, $tolerance);
    }

    /**
     * @param string $userAgent
     *
     * @return string
     */
    public function applyRecoveryMatch($userAgent)
    {
        $s = \Stringy\create($userAgent);

        if ($s->contains('Series30Plus')) {
            return 'nokia_generic_series30plus';
        } else {
            return 'nokia_generic_series40_ovibrosr';
        }
    }
}
