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
 * LGPLUSUserAgentHandler
 *
 *
 * @category   WURFL
 *
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */
class LGUPLUSHandler extends AbstractHandler
{
    protected $prefix = 'LGUPLUS';

    public static $constantIDs = array(
        'generic_lguplus_rexos_facebook_browser',
        'generic_lguplus_rexos_webviewer_browser',
        'generic_lguplus_winmo_facebook_browser',
        'generic_lguplus_android_webkit_browser',
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

        return $s->containsAny(array('LGUPLUS', 'lgtelecom'));
    }

    /**
     * @param string $userAgent
     *
     * @return null|string
     */
    public function applyConclusiveMatch($userAgent)
    {
        return WurflConstants::NO_MATCH;
    }

    /**
     * @param string $userAgent
     *
     * @return null|string
     */
    public function applyRecoveryMatch($userAgent)
    {
        $s = \Stringy\create($userAgent);

        if ($s->containsAll(array('Windows NT 5', 'POLARIS'))) {
            return 'generic_lguplus_rexos_facebook_browser';
        }

        if ($s->contains('Windows NT 5')) {
            return 'generic_lguplus_rexos_webviewer_browser';
        }

        if ($s->containsAll(array('Windows CE', 'POLARIS'))) {
            return 'generic_lguplus_winmo_facebook_browser';
        }

        if ($s->containsAll(array('Android', 'AppleWebKit'))) {
            return 'generic_lguplus_android_webkit_browser';
        }

        return WurflConstants::NO_MATCH;
    }
}
