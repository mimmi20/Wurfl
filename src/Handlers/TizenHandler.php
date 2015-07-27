<?php
/**
 * Copyright (c) 2015 ScientiaMobile, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Refer to the COPYING.txt file distributed with this package.
 *
 *
 * @category   WURFL
 * @package    WURFL
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */

namespace Wurfl\Handlers;

use Wurfl\WurflConstants;

/**
 * TizenUserAgentHandler
 *
 * @category   WURFL
 * @package    WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @author     Steve Kamerman <steve AT scientiamobile.com>
 * @license    GNU Affero General Public License
 * @version    $id$
 */
class TizenHandler
    extends AbstractHandler
{
    protected $prefix = 'Tizen';

    public static $constantIDs = array(
        'generic_tizen',
        'generic_tizen_ver1_0',
        'generic_tizen_ver2_0',
        'generic_tizen_ver2_1',
        'generic_tizen_ver2_2',
        'generic_tizen_ver2_3',
    );

    /**
     * @param string $userAgent
     *
     * @return bool
     */
    public function canHandle($userAgent)
    {
        return (Utils::checkIfStartsWith($userAgent, 'Mozilla') && Utils::checkIfContains($userAgent, 'Tizen'));
    }

    /**
     * @param string $userAgent
     *
     * @return null|string
     */
    public function applyConclusiveMatch($userAgent)
    {
        // Mozilla/5.0 (Linux; Tizen 2.2; SAMSUNG SM-Z910F) AppleWebKit/537.3 (KHTML, like Gecko) Version/2.2 Mobile Safari/537.3
        //                                                  ^ RIS tolerance
        $search = 'AppleWebKit/';
        $idx    = strpos($userAgent, $search);
        if ($idx !== false) {
            // Match to the end of the search string
            return $this->getDeviceIDFromRIS($userAgent, $idx + strlen($search));
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

        $version = self::getTizenVersion($userAgent);
        $version = 'generic_tizen_ver' . str_replace('.', '_', $version);
        if (in_array($version, self::$constantIDs)) {
            return $version;
        }

        return 'generic_tizen';
    }

    public static $validTizenVersions = array('1.0', '2.0', '2.1', '2.2', '2.3');

    /**
     * @param $ua
     *
     * @return string
     */
    public static function getTizenVersion($ua)
    {
        // Find Tizen version
        if (preg_match('#Tizen (\d+?\.\d+?)#', $ua, $matches) && in_array($matches[1], self::$validTizenVersions)) {
            return $matches[1];
        }

        //Default
        return '1.0';
    }
}
