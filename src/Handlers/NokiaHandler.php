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

use Wurfl\WurflConstants;
use UaNormalizer\Helper\Utils;

/**
 * NokiaUserAgentHandler
 *
 *
 * @category   WURFL
 *
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */
class NokiaHandler extends AbstractHandler
{
    protected $prefix = 'NOKIA';

    public static $constantIDs = array(
        'nokia_generic_series60',
        'nokia_generic_series80',
        'nokia_generic_meego',
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

        return $s->contains('Nokia')
            && !$s->containsAny(array('Android', 'iPhone'));
    }

    /**
     * @param string $userAgent
     *
     * @return null|string
     */
    public function applyConclusiveMatch($userAgent)
    {
        $tolerance = Utils::indexOfAnyOrLength($userAgent, array('/', ' '), strpos($userAgent, 'Nokia'));

        if (Utils::checkIfStartsWithAnyOf($userAgent, array('Nokia/', 'Nokia '))) {
            $tolerance = strlen($userAgent);
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
        $s = \Stringy\create($userAgent);

        if ($s->contains('Series60')) {
            return 'nokia_generic_series60';
        }

        if ($s->contains('Series80')) {
            return 'nokia_generic_series80';
        }

        if ($s->contains('MeeGo')) {
            return 'nokia_generic_meego';
        }

        return WurflConstants::NO_MATCH;
    }
}
