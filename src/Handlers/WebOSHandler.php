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
 * WebOSUserAgentHandler
 *
 *
 * @category   WURFL
 *
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */
class WebOSHandler extends AbstractHandler
{
    protected $prefix = 'WEBOS';

    public static $constantIDs = array(
        'hp_tablet_webos_generic',
        'hp_webos_generic',
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

        return $s->containsAny(array('webOS', 'hpwOS'));
    }

    /**
     * @param string $userAgent
     *
     * @return null|string
     */
    public function applyConclusiveMatch($userAgent)
    {
        $delimiterIndex = strpos($userAgent, WurflConstants::RIS_DELIMITER);

        if ($delimiterIndex !== false) {
            $tolerance = $delimiterIndex + strlen(WurflConstants::RIS_DELIMITER);

            return $this->getDeviceIDFromRIS($userAgent, $tolerance);
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
        $s = \Stringy\create($userAgent);

        return $s->contains('hpwOS/3') ? 'hp_tablet_webos_generic' : 'hp_webos_generic';
    }

    /**
     * @param $userAgent
     *
     * @return null|string
     */
    public static function getWebOSModelVersion($userAgent)
    {
        /* Formats:
         * Mozilla/5.0 (hp-tablet; Linux; hpwOS/3.0.5; U; es-US) ... wOSBrowser/234.83 Safari/534.6 TouchPad/1.0
         * Mozilla/5.0 (Linux; webOS/2.2.4; U; de-DE) ... webOSBrowser/221.56 Safari/534.6 Pre/3.0
         * Mozilla/5.0 (webOS/1.4.0; U; en-US) ... Version/1.0 Safari/532.2 Pre/1.0
         */
        if (preg_match('# ([^/]+)/([\d\.]+)$#', $userAgent, $matches)) {
            return $matches[1] . ' ' . $matches[2];
        } else {
            return null;
        }
    }

    /**
     * @param $userAgent
     *
     * @return null|string
     */
    public static function getWebOSVersion($userAgent)
    {
        if (preg_match('#(?:hpw|web)OS.(\d)\.#', $userAgent, $matches)) {
            return 'webOS' . $matches[1];
        } else {
            return null;
        }
    }
}
