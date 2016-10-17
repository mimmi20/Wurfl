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
 * KindleUserAgentHandler
 *
 *
 * @category   WURFL
 *
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */
class KindleHandler extends AbstractHandler
{
    protected $prefix = 'KINDLE';

    public static $constantIDs = array(
        'amazon_kindle_ver1',
        'amazon_kindle2_ver1',
        'amazon_kindle3_ver1',
        'amazon_kindle_fire_ver1',
        'generic_amazon_android_kindle',
        'generic_amazon_kindle',
    );

    /**
     * @param string $userAgent
     *
     * @return bool
     */
    public function canHandle($userAgent)
    {
        $s = \Stringy\create($userAgent);

        if ($s->contains('Android')
            && $s->containsAny(array('/Kindle', 'Silk'))
        ) {
            return false;
        }

        return $s->containsAny(array('/Kindle', 'Silk'));
    }

    /**
     * @param string $userAgent
     *
     * @return null|string
     */
    public function applyConclusiveMatch($userAgent)
    {
        // Desktop-mode Kindle Fire
        // Kindle Fire 2nd Gen Desktop Mode has no android version (even though "Build/I...." tells us it's ICS):
        // Mozilla/5.0 (Linux; U; en-us; KFOT Build/IML74K) AppleWebKit/535.19 (KHTML, like Gecko) Silk/2.0 Safari/535.19 Silk-Accelerated=false
        $idx = strpos($userAgent, 'Build/');

        if ($idx !== false) {
            return $this->getDeviceIDFromRIS($userAgent, $idx);
        }

        // Kindle e-reader
        $search = 'Kindle/';
        $idx    = strpos($userAgent, $search);

        if ($idx !== false) {
            // Version/4.0 Kindle/3.0 (screen 600x800; rotate) Mozilla/5.0 (Linux; U; zh-cn.utf8) AppleWebKit/528.5+ ...
            //        $idx ^      ^ $tolerance
            $tolerance     = $idx + strlen($search) + 1;
            $kindleVersion = $userAgent[$tolerance];

            // RIS match only Kindle/1-3
            if ($kindleVersion >= 1 && $kindleVersion <= 3) {
                return $this->getDeviceIDFromRIS($userAgent, $tolerance);
            }
        }

        // PlayStation Vita
        $search = 'PlayStation Vita';
        $idx    = strpos($userAgent, $search);

        if ($idx !== false) {
            return $this->getDeviceIDFromRIS($userAgent, $idx + strlen($search) + 1);
        }

        return WurflConstants::NO_MATCH;
    }

    /**
     * @param string $userAgent
     *
     * @return string
     */
    public function applyRecoveryMatch($userAgent)
    {
        $map = array(
            'Kindle/1'    => 'amazon_kindle_ver1',
            'Kindle/2'    => 'amazon_kindle2_ver1',
            'Kindle/3'    => 'amazon_kindle3_ver1',
            'Kindle Fire' => 'amazon_kindle_fire_ver1',
            'Silk'        => 'amazon_kindle_fire_ver1',
        );

        $s = \Stringy\create($userAgent);

        foreach ($map as $keyword => $id) {
            if ($s->contains($keyword)) {
                return $id;
            }
        }

        return 'generic_amazon_kindle';
    }
}
