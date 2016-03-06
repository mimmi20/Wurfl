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

/**
 * AppleUserAgentHandler
 *
 *
 * @category   WURFL
 *
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */
class AppleHandler extends AbstractHandler
{
    protected $prefix = 'APPLE';

    public static $constantIDs = array(
        'apple_ipod_touch_ver1',
        'apple_ipod_touch_ver2',
        'apple_ipod_touch_ver3',
        'apple_ipod_touch_ver4',
        'apple_ipod_touch_ver5',
        'apple_ipod_touch_ver6',
        'apple_ipod_touch_ver7',
        'apple_ipod_touch_ver8',
        'apple_ipod_touch_ver9',
        'apple_ipad_ver1',
        'apple_ipad_ver1_subua32',
        'apple_ipad_ver1_sub42',
        'apple_ipad_ver1_sub5',
        'apple_ipad_ver1_sub6',
        'apple_ipad_ver1_sub7',
        'apple_ipad_ver1_sub8',
        'apple_ipad_ver1_sub9',
        'apple_iphone_ver1',
        'apple_iphone_ver2',
        'apple_iphone_ver3',
        'apple_iphone_ver4',
        'apple_iphone_ver5',
        'apple_iphone_ver6',
        'apple_iphone_ver7',
        'apple_iphone_ver8',
        'apple_iphone_ver9',
        //iOS HW IDs
        'apple_ipad_ver1_subhw1',
        'apple_ipad_ver1_sub42_subhw1',
        'apple_ipad_ver1_sub43_subhw1',
        'apple_ipad_ver1_sub43_subhw2',
        'apple_ipad_ver1_sub51_subhw1',
        'apple_ipad_ver1_sub51_subhw2',
        'apple_ipad_ver1_sub51_subhw3',
        'apple_ipad_ver1_sub5_subhw1',
        'apple_ipad_ver1_sub5_subhw2',
        'apple_ipad_ver1_sub6_subhw2',
        'apple_ipad_ver1_sub6_subhw3',
        'apple_ipad_ver1_sub6_subhw4',
        'apple_ipad_ver1_sub61_subhw2',
        'apple_ipad_ver1_sub61_subhw3',
        'apple_ipad_ver1_sub61_subhw4',
        'apple_ipad_ver1_sub61_subhwmini1',
        'apple_ipad_ver1_sub6_subhwmini1',
        'apple_ipad_ver1_sub7_subhw2',
        'apple_ipad_ver1_sub7_subhw3',
        'apple_ipad_ver1_sub7_subhw4',
        'apple_ipad_ver1_sub7_subhwmini1',
        'apple_ipad_ver1_sub7_subhwmini2',
        'apple_ipad_ver1_sub7_subhwair',
        'apple_ipad_ver1_sub71_subhw2',
        'apple_ipad_ver1_sub71_subhw3',
        'apple_ipad_ver1_sub71_subhw4',
        'apple_ipad_ver1_sub71_subhwmini1',
        'apple_ipad_ver1_sub71_subhwmini2',
        'apple_ipad_ver1_sub71_subhwair',
        'apple_ipad_ver1_sub8_subhw2',
        'apple_ipad_ver1_sub8_subhw3',
        'apple_ipad_ver1_sub8_subhw4',
        'apple_ipad_ver1_sub8_subhwair',
        'apple_ipad_ver1_sub8_subhwmini1',
        'apple_ipad_ver1_sub8_subhwmini2',
        'apple_ipad_ver1_sub8_1_subhw2',
        'apple_ipad_ver1_sub8_1_subhw3',
        'apple_ipad_ver1_sub8_1_subhw4',
        'apple_ipad_ver1_sub8_1_subhwair',
        'apple_ipad_ver1_sub8_1_subhwair2',
        'apple_ipad_ver1_sub8_1_subhwmini1',
        'apple_ipad_ver1_sub8_1_subhwmini2',
        'apple_ipad_ver1_sub8_1_subhwmini3',
        'apple_ipad_ver1_sub8_2_subhw2',
        'apple_ipad_ver1_sub8_2_subhw3',
        'apple_ipad_ver1_sub8_2_subhw4',
        'apple_ipad_ver1_sub8_2_subhwair',
        'apple_ipad_ver1_sub8_2_subhwair2',
        'apple_ipad_ver1_sub8_2_subhwmini1',
        'apple_ipad_ver1_sub8_2_subhwmini2',
        'apple_ipad_ver1_sub8_2_subhwmini3',
        'apple_ipad_ver1_sub8_2_subhwmini3',
        'apple_ipad_ver1_sub8_3_subhw2',
        'apple_ipad_ver1_sub8_3_subhw3',
        'apple_ipad_ver1_sub8_3_subhw4',
        'apple_ipad_ver1_sub8_3_subhwair',
        'apple_ipad_ver1_sub8_3_subhwair2',
        'apple_ipad_ver1_sub8_3_subhwmini1',
        'apple_ipad_ver1_sub8_3_subhwmini2',
        'apple_ipad_ver1_sub8_3_subhwmini3',
        'apple_ipad_ver1_sub8_4_subhw2',
        'apple_ipad_ver1_sub8_4_subhw3',
        'apple_ipad_ver1_sub8_4_subhw4',
        'apple_ipad_ver1_sub8_4_subhwair',
        'apple_ipad_ver1_sub8_4_subhwair2',
        'apple_ipad_ver1_sub8_4_subhwmini1',
        'apple_ipad_ver1_sub8_4_subhwmini2',
        'apple_ipad_ver1_sub8_4_subhwmini3',
        'apple_ipad_ver1_sub9_subhw2',
        'apple_ipad_ver1_sub9_subhw3',
        'apple_ipad_ver1_sub9_subhw4',
        'apple_ipad_ver1_sub9_subhwair',
        'apple_ipad_ver1_sub9_subhwair2',
        'apple_ipad_ver1_sub9_subhwmini1',
        'apple_ipad_ver1_sub9_subhwmini2',
        'apple_ipad_ver1_sub9_subhwmini3',
        'apple_ipad_ver1_sub9_subhwmini4',
        'apple_ipad_ver1_sub9_1_subhw2',
        'apple_ipad_ver1_sub9_1_subhw3',
        'apple_ipad_ver1_sub9_1_subhw4',
        'apple_ipad_ver1_sub9_1_subhwair',
        'apple_ipad_ver1_sub9_1_subhwair2',
        'apple_ipad_ver1_sub9_1_subhwmini1',
        'apple_ipad_ver1_sub9_1_subhwmini2',
        'apple_ipad_ver1_sub9_1_subhwmini3',
        'apple_ipad_ver1_sub9_1_subhwmini4',
        'apple_ipad_ver1_sub9_1_subhwpro',
        'apple_ipad_ver1_sub9_2_subhw2',
        'apple_ipad_ver1_sub9_2_subhw3',
        'apple_ipad_ver1_sub9_2_subhw4',
        'apple_ipad_ver1_sub9_2_subhwair',
        'apple_ipad_ver1_sub9_2_subhwair2',
        'apple_ipad_ver1_sub9_2_subhwmini1',
        'apple_ipad_ver1_sub9_2_subhwmini2',
        'apple_ipad_ver1_sub9_2_subhwmini3',
        'apple_ipad_ver1_sub9_2_subhwmini4',
        'apple_ipad_ver1_sub9_2_subhwpro',
        'apple_ipad_ver1_sub9_3_subhw2',
        'apple_ipad_ver1_sub9_3_subhw3',
        'apple_ipad_ver1_sub9_3_subhw4',
        'apple_ipad_ver1_sub9_3_subhwair',
        'apple_ipad_ver1_sub9_3_subhwair2',
        'apple_ipad_ver1_sub9_3_subhwmini1',
        'apple_ipad_ver1_sub9_3_subhwmini2',
        'apple_ipad_ver1_sub9_3_subhwmini3',
        'apple_ipad_ver1_sub9_3_subhwmini4',
        'apple_ipad_ver1_sub9_3_subhwpro',
        'apple_iphone_ver1_subhw2g',
        'apple_iphone_ver2_subhw2g',
        'apple_iphone_ver2_subhw3g',
        'apple_iphone_ver2_1_subhw2g',
        'apple_iphone_ver2_1_subhw3g',
        'apple_iphone_ver2_2_subhw2g',
        'apple_iphone_ver2_2_subhw3g',
        'apple_iphone_ver3_subhw2g',
        'apple_iphone_ver3_subhw3g',
        'apple_iphone_ver3_subhw3gs',
        'apple_iphone_ver3_1_subhw2g',
        'apple_iphone_ver3_1_subhw3g',
        'apple_iphone_ver3_1_subhw3gs',
        'apple_iphone_ver4_subhw3g',
        'apple_iphone_ver4_subhw3gs',
        'apple_iphone_ver4_subhw4',
        'apple_iphone_ver4_1_subhw3g',
        'apple_iphone_ver4_1_subhw3gs',
        'apple_iphone_ver4_1_subhw4',
        'apple_iphone_ver4_2_subhw3g',
        'apple_iphone_ver4_2_subhw3gs',
        'apple_iphone_ver4_2_subhw4',
        'apple_iphone_ver4_3_subhw3gs',
        'apple_iphone_ver4_3_subhw4',
        'apple_iphone_ver5_subhw3gs',
        'apple_iphone_ver5_subhw4',
        'apple_iphone_ver5_subhw4s',
        'apple_iphone_ver5_1_subhw3gs',
        'apple_iphone_ver5_1_subhw4',
        'apple_iphone_ver5_1_subhw4s',
        'apple_iphone_ver6_subhw3gs',
        'apple_iphone_ver6_subhw4',
        'apple_iphone_ver6_subhw4s',
        'apple_iphone_ver6_subhw5',
        'apple_iphone_ver6_1_subhw3gs',
        'apple_iphone_ver6_1_subhw4',
        'apple_iphone_ver6_1_subhw4s',
        'apple_iphone_ver6_1_subhw5',
        'apple_iphone_ver7_subhw4',
        'apple_iphone_ver7_subhw4s',
        'apple_iphone_ver7_subhw5',
        'apple_iphone_ver7_subhw5c',
        'apple_iphone_ver7_subhw5s',
        'apple_iphone_ver7_1_subhw4',
        'apple_iphone_ver7_1_subhw4s',
        'apple_iphone_ver7_1_subhw5',
        'apple_iphone_ver7_1_subhw5c',
        'apple_iphone_ver7_1_subhw5s',
        'apple_iphone_ver8_subhw4s',
        'apple_iphone_ver8_subhw5',
        'apple_iphone_ver8_subhw5c',
        'apple_iphone_ver8_subhw5s',
        'apple_iphone_ver8_subhw6',
        'apple_iphone_ver8_subhw6plus',
        'apple_iphone_ver8_subua802_subhw4s',
        'apple_iphone_ver8_subua802_subhw5',
        'apple_iphone_ver8_subua802_subhw5c',
        'apple_iphone_ver8_subua802_subhw5s',
        'apple_iphone_ver8_subua802_subhw6',
        'apple_iphone_ver8_subua802_subhw6plus',
        'apple_iphone_ver8_1_subhw4s',
        'apple_iphone_ver8_1_subhw5',
        'apple_iphone_ver8_1_subhw5c',
        'apple_iphone_ver8_1_subhw5s',
        'apple_iphone_ver8_1_subhw6',
        'apple_iphone_ver8_1_subhw6plus',
        'apple_iphone_ver8_2_subhw4s',
        'apple_iphone_ver8_2_subhw5',
        'apple_iphone_ver8_2_subhw5c',
        'apple_iphone_ver8_2_subhw5s',
        'apple_iphone_ver8_2_subhw6',
        'apple_iphone_ver8_2_subhw6plus',
        'apple_iphone_ver8_3_subhw4s',
        'apple_iphone_ver8_3_subhw5',
        'apple_iphone_ver8_3_subhw5c',
        'apple_iphone_ver8_3_subhw5s',
        'apple_iphone_ver8_3_subhw6',
        'apple_iphone_ver8_3_subhw6plus',
        'apple_iphone_ver8_4_subhw4s',
        'apple_iphone_ver8_4_subhw5',
        'apple_iphone_ver8_4_subhw5c',
        'apple_iphone_ver8_4_subhw5s',
        'apple_iphone_ver8_4_subhw6',
        'apple_iphone_ver8_4_subhw6plus',
        'apple_iphone_ver9_subhw4s',
        'apple_iphone_ver9_subhw5',
        'apple_iphone_ver9_subhw5c',
        'apple_iphone_ver9_subhw5s',
        'apple_iphone_ver9_subhw6',
        'apple_iphone_ver9_subhw6plus',
        'apple_iphone_ver9_subhw6s',
        'apple_iphone_ver9_subhw6splus',
        'apple_iphone_ver9_1_subhw4s',
        'apple_iphone_ver9_1_subhw5',
        'apple_iphone_ver9_1_subhw5c',
        'apple_iphone_ver9_1_subhw5s',
        'apple_iphone_ver9_1_subhw6',
        'apple_iphone_ver9_1_subhw6plus',
        'apple_iphone_ver9_1_subhw6s',
        'apple_iphone_ver9_1_subhw6splus',
        'apple_iphone_ver9_2_subhw4s',
        'apple_iphone_ver9_2_subhw5',
        'apple_iphone_ver9_2_subhw5c',
        'apple_iphone_ver9_2_subhw5s',
        'apple_iphone_ver9_2_subhw6',
        'apple_iphone_ver9_2_subhw6plus',
        'apple_iphone_ver9_2_subhw6s',
        'apple_iphone_ver9_2_subhw6splus',
        'apple_iphone_ver9_3_subhw4s',
        'apple_iphone_ver9_3_subhw5',
        'apple_iphone_ver9_3_subhw5s',
        'apple_iphone_ver9_3_subhw5c',
        'apple_iphone_ver9_3_subhw6',
        'apple_iphone_ver9_3_subhw6plus',
        'apple_iphone_ver9_3_subhw6s',
        'apple_iphone_ver9_3_subhw6splus',
        'apple_ipod_touch_ver1_subhw1',
        'apple_ipod_touch_ver2_subhw1',
        'apple_ipod_touch_ver2_1_subhw1',
        'apple_ipod_touch_ver2_1_subhw2',
        'apple_ipod_touch_ver2_2_subhw1',
        'apple_ipod_touch_ver2_2_subhw2',
        'apple_ipod_touch_ver3_subhw1',
        'apple_ipod_touch_ver3_subhw2',
        'apple_ipod_touch_ver3_1_subhw1',
        'apple_ipod_touch_ver3_1_subhw2',
        'apple_ipod_touch_ver3_1_subhw3',
        'apple_ipod_touch_ver4_subhw2',
        'apple_ipod_touch_ver4_subhw3',
        'apple_ipod_touch_ver4_1_subhw2',
        'apple_ipod_touch_ver4_1_subhw3',
        'apple_ipod_touch_ver4_1_subhw4',
        'apple_ipod_touch_ver4_2_subhw2',
        'apple_ipod_touch_ver4_2_subhw3',
        'apple_ipod_touch_ver4_2_subhw4',
        'apple_ipod_touch_ver4_3_subhw3',
        'apple_ipod_touch_ver4_3_subhw4',
        'apple_ipod_touch_ver5_subhw3',
        'apple_ipod_touch_ver5_subhw4',
        'apple_ipod_touch_ver5_1_subhw3',
        'apple_ipod_touch_ver5_1_subhw4',
        'apple_ipod_touch_ver6_subhw3',
        'apple_ipod_touch_ver6_subhw4',
        'apple_ipod_touch_ver6_subhw5',
        'apple_ipod_touch_ver6_1_subhw4',
        'apple_ipod_touch_ver6_1_subhw5',
        'apple_ipod_touch_ver7_subhw5',
        'apple_ipod_touch_ver7_1_subhw5',
        'apple_ipod_touch_ver8_subhw5',
        'apple_ipod_touch_ver8_1_subhw5',
        'apple_ipod_touch_ver8_2_subhw5',
        'apple_ipod_touch_ver8_3_subhw5',
        'apple_ipod_touch_ver8_4_subhw5',
        'apple_ipod_touch_ver9_subhw5',
        'apple_ipod_touch_ver9_subhw6',
        'apple_ipod_touch_ver9_1_subhw5',
        'apple_ipod_touch_ver9_1_subhw6',
        'apple_ipod_touch_ver9_2_subhw5',
        'apple_ipod_touch_ver9_2_subhw6',
        'apple_ipod_touch_ver9_3_subhw5',
        'apple_ipod_touch_ver9_3_subhw6',
    );

    // iOS hardware mappings
    public static $iphoneDeviceMap = array(
        '1,1' => '2g',
        '1,2' => '3g',
        '2,1' => '3gs',
        '3,1' => '4',
        '3,2' => '4',
        '3,3' => '4',
        '4,1' => '4s',
        '5,1' => '5',
        '5,2' => '5',
        '5,3' => '5c',
        '5,4' => '5c',
        '6,1' => '5s',
        '6,2' => '5s',
        '7,1' => '6plus',
        '7,2' => '6',
        '8,1' => '6s',
        '8,2' => '6splus',
    );

    public static $ipadDeviceMap = array(
        '1,1' => '1',
        '2,1' => '2',
        '2,2' => '2',
        '2,3' => '2',
        '2,4' => '2',
        '2,5' => 'mini1',
        '2,6' => 'mini1',
        '2,7' => 'mini1',
        '3,1' => '3',
        '3,2' => '3',
        '3,3' => '3',
        '3,4' => '4',
        '3,5' => '4',
        '3,6' => '4',
        '4,1' => 'air',
        '4,2' => 'air',
        '4,3' => 'air',
        '4,4' => 'mini2',
        '4,5' => 'mini2',
        '4,6' => 'mini2',
        '4,7' => 'mini3',
        '4,8' => 'mini3',
        '4,9' => 'mini3',
        '5,1' => 'mini4',
        '5,2' => 'mini4',
        '5,3' => 'air2',
        '5,4' => 'air2',
        '6,7' => 'pro',
        '6,8' => 'pro',
    );

    public static $ipodDeviceMap = array(
        '1,1' => '1',
        '2,1' => '2',
        '3,1' => '3',
        '4,1' => '4',
        '5,1' => '5',
        '7,1' => '6',
    );

    public function canHandle($userAgent)
    {
        if (Utils::isDesktopBrowser($userAgent)) {
            return false;
        }

        return (Utils::checkIfContainsAnyOf($userAgent, array('iPhone', 'iPod', 'iPad')))
            && !Utils::checkIfContains($userAgent, 'Symbian');
    }

    public function applyConclusiveMatch($userAgent)
    {
        // Normalize AFNetworking and server-bag UAs
        // Pippo/2.4.3 (iPad; iOS 8.0.2; Scale/2.00)
        // server-bag [iPhone OS,8.2,12D508,iPhone4,1]
        // iPhone4,1/8.2 (12D508)
        if (preg_match(
            '#^[^/]+?/[\d\.]+? \(i[A-Za-z]+; iOS ([\d\.]+); Scale/[\d\.]+\)#',
            $userAgent,
            $matches
        ) || preg_match('#^server-bag \[iPhone OS,([\d\.]+),#', $userAgent, $matches) || preg_match(
            '#^i(?:Phone|Pad|Pod)\d+?,\d+?/([\d\.]+)#',
            $userAgent,
            $matches
        )
        ) {
            $matches[1] = str_replace('.', '_', $matches[1]);
            if (Utils::checkIfContains($userAgent, 'iPad')) {
                $userAgent = 'Mozilla/5.0 (iPad; CPU OS {' . $matches[1] . '} like Mac OS X) AppleWebKit/538.39.2 (KHTML, like Gecko) Version/7.0 Mobile/12A4297e Safari/9537.53 ' . $userAgent;
            } elseif (Utils::checkIfContains($userAgent, 'iPod touch')) {
                $userAgent = 'Mozilla/5.0 (iPod touch; CPU iPhone OS {' . $matches[1] . '} like Mac OS X) AppleWebKit/538.41 (KHTML, like Gecko) Version/7.0 Mobile/12A307 Safari/9537.53 ' . $userAgent;
            } elseif (Utils::checkIfContains($userAgent, 'iPod')) {
                $userAgent = 'Mozilla/5.0 (iPod; CPU iPhone OS {' . $matches[1] . '} like Mac OS X) AppleWebKit/538.41 (KHTML, like Gecko) Version/7.0 Mobile/12A307 Safari/9537.53 ' . $userAgent;
            } else {
                $userAgent = 'Mozilla/5.0 (iPhone; CPU iPhone OS {' . $matches[1] . '} like Mac OS X) AppleWebKit/601.1.10 (KHTML, like Gecko) Version/8.0 Mobile/12E155 Safari/600.1.4 ' . $userAgent;
            }
        }

        // Normalize Skype SDK UAs
        if (preg_match('#^iOSClientSDK/\d+\.+[0-9\.]+ +?\((Mozilla.+)\)$#', $userAgent, $matches)) {
            $userAgent = $matches[1];
        }

        // Normalize iOS {Ver} style UAs
        //Eg: Mozilla/5.0 (iPhone; U; CPU iOS 7.1.2 like Mac OS X; en-us) AppleWebKit/528.18 (KHTML, like Gecko) Safari/528.16
        if (preg_match('#CPU iOS \d+?\.\d+?#', $userAgent)) {
            $ua = Utils::checkIfContains($userAgent, 'iPad') ? str_replace('CPU iOS', 'CPU OS', $userAgent) : str_replace('CPU iOS', 'CPU iPhone OS', $userAgent);

            if (preg_match('#(CPU(?: iPhone)? OS [\d\.]+ like)#', $ua, $matches)) {
                $versionUnderscore = str_replace('.', '_', $matches[1]);
                $ua                = str_replace(' U;', '', $ua);
                $ua                = preg_replace('#CPU(?: iPhone)? OS ([\d\.]+) like#', $versionUnderscore, $ua);
                $userAgent         = $ua;
            }
        }

        // Attempt to find hardware version
        $device_version = null;
        if (preg_match('#(?:iPhone|iPad|iPod) ?(\d,\d)#', $userAgent, $matches)) {
            // Check for iPod first since they contain 'iPhone'
            if (Utils::checkIfContains($userAgent, 'iPod')) {
                if (array_key_exists($matches[1], self::$ipodDeviceMap)) {
                    $device_version = str_replace(
                        array_keys(self::$ipodDeviceMap),
                        array_values(self::$ipodDeviceMap),
                        $matches[1]
                    );
                }
            } elseif (Utils::checkIfContains($userAgent, 'iPad')) {
                if (array_key_exists($matches[1], self::$ipadDeviceMap)) {
                    $device_version = str_replace(
                        array_keys(self::$ipadDeviceMap),
                        array_values(self::$ipadDeviceMap),
                        $matches[1]
                    );
                }
            } elseif (Utils::checkIfContains($userAgent, 'iPhone')) {
                if (array_key_exists($matches[1], self::$iphoneDeviceMap)) {
                    $device_version = str_replace(
                        array_keys(self::$iphoneDeviceMap),
                        array_values(self::$iphoneDeviceMap),
                        $matches[1]
                    );
                }
                // Set $device_version to null if UA contains unrecognized hardware version or does not satisfy any of the above 'if' statements
            } else {
                $device_version = null;
            }
        }

        $tolerance = strpos($userAgent, '_');

        if ($tolerance !== false) {
            // The first char after the first underscore
            ++$tolerance;
        } else {
            $index = strpos($userAgent, 'like Mac OS X;');

            if ($index !== false) {
                // Step through the search string to the semicolon at the end
                $tolerance = $index + 14;
            } else {
                // Non-typical UA, try full length match
                $tolerance = strlen($userAgent);
            }
        }
        $ris_id = $this->getDeviceIDFromRIS($userAgent, $tolerance);

        //Assemble and check iOS HW ID
        if ($device_version !== null) {
            $test_id = $ris_id . '_subhw' . $device_version;
            if (in_array($test_id, self::$constantIDs)) {
                return $test_id;
            }
        }

        return $ris_id;
    }

    public function applyRecoveryMatch($userAgent)
    {
        if (preg_match('/ (\d)_(\d)[ _]/', $userAgent, $matches)) {
            $majorVersion = (int) $matches[1];
        } else {
            $majorVersion = -1;
        }

        // Core-media
        if (Utils::checkIfContains($userAgent, 'CoreMedia')) {
            return 'apple_iphone_coremedia_ver1';
        }

        // Check iPods first since they also contain 'iPhone'
        if (Utils::checkIfContains($userAgent, 'iPod')) {
            $deviceID = 'apple_ipod_touch_ver' . $majorVersion;

            if (in_array($deviceID, self::$constantIDs)) {
                return $deviceID;
            } else {
                return 'apple_ipod_touch_ver1';
            }
            // Now check for iPad
        } else {
            if (Utils::checkIfContains($userAgent, 'iPad')) {
                $deviceID = 'apple_ipad_ver1_sub' . $majorVersion;

                if ($majorVersion === 3) {
                    return 'apple_ipad_ver1_subua32';
                } else {
                    if ($majorVersion === 4) {
                        return 'apple_ipad_ver1_sub42';
                    }
                }

                if (in_array($deviceID, self::$constantIDs)) {
                    return $deviceID;
                } else {
                    return 'apple_ipad_ver1';
                }
                // Check iPhone last
            } else {
                if (Utils::checkIfContains($userAgent, 'iPhone')) {
                    $deviceID = 'apple_iphone_ver' . $majorVersion;
                    if (in_array($deviceID, self::$constantIDs)) {
                        return $deviceID;
                    } else {
                        return 'apple_iphone_ver1';
                    }
                }
            }
        }

        return WurflConstants::NO_MATCH;
    }
}
