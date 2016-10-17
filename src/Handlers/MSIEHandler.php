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
 * MSIEAgentHandler
 *
 *
 * @category   WURFL
 *
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */
class MSIEHandler extends AbstractHandler
{
    protected $prefix = 'MSIE';

    public static $constantIDs = array(
        0     => 'msie',
        4     => 'msie_4',
        5     => 'msie_5',
        '5.5' => 'msie_5_5',
        6     => 'msie_6',
        7     => 'msie_7',
        8     => 'msie_8',
        9     => 'msie_9',
        10    => 'msie_10',
        11    => 'msie_11',
        12    => 'msie_12',
        13    => 'edge_13',
    );

    /**
     * @param string $userAgent
     *
     * @return bool
     */
    public function canHandle($userAgent)
    {
        if (Utils::isMobileBrowser($userAgent)) {
            return false;
        }

        $s = \Stringy\create($userAgent);

        if (!$s->startsWith('Mozilla')) {
            return false;
        }

        if ($s->containsAny(array('Opera', 'armv', 'MOTO', 'BREW'))) {
            return false;
        }

        // Edge 12 signature
        $hasEdgeMode = $s->contains(' Edge/');

        // IE 11 signature
        $hasTridentRv = $s->containsAll(array('Trident', 'rv:'));

        // IE < 11 signature
        $hasMsie = $s->contains('MSIE');

        return ($hasMsie || $hasTridentRv || $hasEdgeMode);
    }

    /**
     * @param string $userAgent
     *
     * @return null|string
     */
    public function applyConclusiveMatch($userAgent)
    {
        $matches = array();

        if (preg_match('#^Mozilla/5\.0 \(Windows NT.+? Edge/(\d+)\.(\d+)#', $userAgent, $matches)
            || preg_match('#^Mozilla/5\.0 \(.+?Trident.+?; rv:(\d\d)\.(\d+)\)#', $userAgent, $matches)
            || preg_match('#^Mozilla/[45]\.0 \(compatible; MSIE (\d+)\.(\d+);#', $userAgent, $matches)
        ) {
            $major = (int) $matches[1];
            $minor = (int) $matches[2];

            // MSIE 5.5 is handled specifically
            if ($major === 5 && $minor === 5) {
                return 'msie_5_5';
            }

            // Look for version in constant ID array
            if (array_key_exists($major, self::$constantIDs)) {
                return self::$constantIDs[$major];
            }
        }

        return $this->getDeviceIDFromRIS($userAgent, Utils::indexOfOrLength($userAgent, 'Trident'));
    }

    /**
     * @param string $userAgent
     *
     * @return null|string
     */
    public function applyRecoveryMatch($userAgent)
    {
        $s = \Stringy\create($userAgent);

        if ($s->containsAny(
            array(
                'SLCC1',
                'Media Center PC',
                '.NET CLR',
                'OfficeLiveConnector',
            )
        )
        ) {
            return WurflConstants::GENERIC_WEB_BROWSER;
        }

        return WurflConstants::NO_MATCH;
    }
}
