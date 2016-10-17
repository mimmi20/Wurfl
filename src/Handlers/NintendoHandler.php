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
 * NintendoUserAgentHandler
 *
 *
 * @category   WURFL
 *
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */
class NintendoHandler extends AbstractHandler
{
    protected $prefix = 'NINTENDO';

    public static $constantIDs = array(
        'nintendo_wii_u_ver1',
        'nintendo_wii_ver1',
        'nintendo_dsi_ver1',
        'nintendo_ds_ver1',
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

        if ($s->contains('Nintendo')) {
            return true;
        }

        return $s->startsWith('Mozilla/')
            && $s->containsAll(array('Nitro', 'Opera'));
    }

    /**
     * @param string $userAgent
     *
     * @return string
     */
    public function applyConclusiveMatch($userAgent)
    {
        $s = \Stringy\create($userAgent);

        if ($s->contains('Nintendo WiiU')) {
            return 'nintendo_wii_u_ver1';
        }

        if ($s->contains('Nintendo Wii')) {
            return 'nintendo_wii_ver1';
        }

        if ($s->contains('Nintendo DSi')) {
            return 'nintendo_dsi_ver1';
        }

        if ($s->startsWith('Mozilla/')
            && $s->containsAll(array('Nitro', 'Opera'))
        ) {
            return 'nintendo_ds_ver1';
        }

        return 'nintendo_wii_ver1';
    }
}
