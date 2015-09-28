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

namespace Wurfl\Handlers\Normalizer\Generic;

use Wurfl\Handlers\Normalizer\NormalizerInterface;

/**
 * User Agent Normalizer - CFNetwork UA Resolution
 *
 * @package    WURFL_Request_UserAgentNormalizer_Generic
 */
class CFNetwork implements NormalizerInterface
{
    /**
     * @param string $userAgent
     *
     * @return mixed|string
     */
    public function normalize($userAgent)
    {

        //Match a CFNetwork UA
        if (preg_match('#CFNetwork/(\d+\.?[0-9]*)#', $userAgent, $matches)) {
            $cfNetworkVersion = sprintf('%.2f', round($matches[1], 2, PHP_ROUND_HALF_DOWN));

            //Look for a direct match in the lookup tables
            $normalizedcfUA = $this->cfnetworkLookup($cfNetworkVersion);
            if ($normalizedcfUA !== false) {
                return $normalizedcfUA;
            }
        }

        return $userAgent;
    }

    private function cfnetworkLookup($cfVersion)
    {

        $cfnetworkMap = array(
            //CFNetwork Version (2 decimal places with leading zeros) => array(Mac OS X version','Safari Version
            '1.20'   => array('OSX', '10_3', '1.3'),
            '1.10'   => array('OSX', '10_2', '1.0'),
            '128.00' => array('OSX', '10_4', '4.1.3'),
            '129.00' => array('OSX', '10_4', '4.1.3'),
            '217.00' => array('OSX', '10_5', '5.0.6'),
            '220.00' => array('OSX', '10_5', '5.0.6'),
            '330.00' => array('OSX', '10_5', '5.0.6'),
            '339.00' => array('OSX', '10_5', '5.0.6'),
            '422.00' => array('OSX', '10_5', '5.0.6'),
            '438.00' => array('OSX', '10_5', '5.0.6'),
            '454.00' => array('OSX', '10_6', '5.1.10'),
            '520.00' => array('OSX', '10_7', '6.1.6'),
            '596.00' => array('OSX', '10_8', '6.2.3'),
            '673.00' => array('OSX', '10_9', '7.1.3'),
            '705.00' => array('OSX', '10_10', '8.0.3'),
            '708.00' => array('OSX', '10_10', '8.0.3'),
            '714.00' => array('OSX', '10_10', '8.0.3'),
            '718.00' => array('OSX', '10_10', '8.0.3'),
            '720.00' => array('OSX', '10_10', '8.0.3'),
            //CFNetwork Version (2 decimal places with leading zeros) => iOS Version
            '459.00' => array('iPhone', '3_1'),
            '467.00' => array('iPhone', '3_2'),
            '485.20' => array('iPhone', '4_0'),
            '485.10' => array('iPhone', '4_1'),
            '485.12' => array('iPhone', '4_2'),
            '485.13' => array('iPhone', '4_3'),
            '548.00' => array('iPhone', '5_0'),
            '548.10' => array('iPhone', '5_1'),
            '602.00' => array('iPhone', '6_0'),
            '609.00' => array('iPhone', '6_0'),
            '609.10' => array('iPhone', '6_1'),
            '672.00' => array('iPhone', '7_0'),
            '672.10' => array('iPhone', '7_1'),
            '711.00' => array('iPhone', '8_0'),
            '711.10' => array('iPhone', '8_1'),
            '711.20' => array('iPhone', '8_2'),
        );

        if (array_key_exists($cfVersion, $cfnetworkMap)) {
            $version = $cfnetworkMap[$cfVersion];
            if ($version[0] === "iPhone") {
                return "Mozilla/5.0 (iPhone; CPU iPhone OS {$version[1]} like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) Version/8.0 Mobile/0000 Safari/600.1.4 CFNetwork";
            } else if ($version[0] === "OSX") {
                return "Mozilla/5.0 (Macintosh; Intel Mac OS X {$version[1]}) AppleWebKit/537.75.14 (KHTML, like Gecko) Version/{$version[2]} Safari/537.75.14 CFNetwork";
            }
        }

        return false;
    }
}
