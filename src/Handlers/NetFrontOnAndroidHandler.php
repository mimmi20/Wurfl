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

/**
 * NetFrontOnAndroidUserAgentHandler
 *
 *
 * @category   WURFL
 * @package    WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
class NetFrontOnAndroidHandler
    extends AbstractHandler
{

    protected $prefix = "NETFRONTONANDROID";

    public static $constantIDs = array(
        'generic_android_ver2_0_netfrontlifebrowser',
        'generic_android_ver2_1_netfrontlifebrowser',
        'generic_android_ver2_2_netfrontlifebrowser',
        'generic_android_ver2_3_netfrontlifebrowser',
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

        return (Utils::checkIfContains($userAgent, 'Android') && Utils::checkIfContains(
                $userAgent,
                'NetFrontLifeBrowser/2.2'
            ));
    }

    /**
     * @param string $userAgent
     *
     * @return null|string
     */
    public function applyConclusiveMatch($userAgent)
    {
        $find      = 'NetFrontLifeBrowser/2.2';
        $tolerance = strpos($userAgent, $find) + strlen($find);

        if ($tolerance > strlen($userAgent)) {
            $tolerance = strlen($userAgent);
        }

        return $this->getDeviceIDFromRIS($userAgent, $tolerance);
    }

    /**
     * @param string $userAgent
     *
     * @return string
     */
    public function applyRecoveryMatch($userAgent)
    {
        $androidVersionString = str_replace('.', '_', AndroidHandler::getAndroidVersion($userAgent));
        $deviceID             = 'generic_android_ver' . $androidVersionString . '_netfrontlifebrowser';

        if (in_array($deviceID, self::$constantIDs)) {
            return $deviceID;
        } else {
            return 'generic_android_ver2_0_netfrontlifebrowser';
        }
    }
}
