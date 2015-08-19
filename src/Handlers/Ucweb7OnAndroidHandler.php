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
 * @package    WURFL
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */

namespace Wurfl\Handlers;

/**
 * Ucweb7OnAndroidUserAgentHandler
 *
 *
 * @category   WURFL
 * @package    WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */
class Ucweb7OnAndroidHandler
    extends AbstractHandler
{
    protected $prefix = 'UCWEB7ONANDROID';

    public static $constantIDs = array(
        'generic_android_ver1_6_ucweb',
        'generic_android_ver2_0_ucweb',
        'generic_android_ver2_1_ucweb',
        'generic_android_ver2_2_ucweb',
        'generic_android_ver2_3_ucweb',
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

        return Utils::checkIfContainsAll($userAgent, array('Android', 'UCWEB7'));
    }

    /**
     * @param string $userAgent
     *
     * @return string|void
     */
    public function applyConclusiveMatch($userAgent)
    {
        // The tolerance is after UCWEB7, not before
        $find      = 'UCWEB7';
        $tolerance = Utils::indexOfOrLength($userAgent, $find) + strlen($find);

        if ($tolerance > strlen($userAgent)) {
            $tolerance = strlen($userAgent);
        }

        $this->getDeviceIDFromRIS($userAgent, $tolerance);
    }

    /**
     * @param string $userAgent
     *
     * @return string
     */
    public function applyRecoveryMatch($userAgent)
    {
        $androidVersion = str_replace('.', '_', AndroidHandler::getAndroidVersion($userAgent));
        $deviceID       = 'generic_android_ver' . $androidVersion . '_ucweb';

        if (in_array($deviceID, self::$constantIDs)) {
            return $deviceID;
        } else {
            return 'generic_android_ver2_0_ucweb';
        }
    }
}
