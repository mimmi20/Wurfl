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
 *
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */

namespace Wurfl\Handlers\Normalizer\Specific;

use Wurfl\Handlers\AndroidHandler;
use Wurfl\Handlers\Normalizer\NormalizerInterface;
use Wurfl\Handlers\OperaMobiOrTabletOnAndroidHandler;
use Wurfl\Handlers\Utils;
use Wurfl\WurflConstants;

/**
 * User Agent Normalizer - Trims the version number to two digits (e.g. 2.1.1 -> 2.1)
 */
class Android implements NormalizerInterface
{
    /**
     * @var array
     */
    private $skip_normalization = array(
        'Opera Mini',
        'Fennec',
        'Firefox',
        'UCWEB7',
        'NetFrontLifeBrowser/2.2',
    );

    /**
     * @param string $userAgent
     *
     * @return string
     */
    public function normalize($userAgent)
    {
        // Normalize Android version
        $userAgent = preg_replace('/(Android)[ \-\/](\d\.\d)([^; \/\)]+)/', '$1 $2', $userAgent);

        // Opera Mobi/Tablet
        $isOperaMobile = Utils::checkIfContains($userAgent, 'Opera Mobi');
        $isOperaTablet = Utils::checkIfContains($userAgent, 'Opera Tablet');

        if ($isOperaMobile || $isOperaTablet) {
            $operaVersion   = OperaMobiOrTabletOnAndroidHandler::getOperaOnAndroidVersion($userAgent, false);
            $androidVersion = AndroidHandler::getAndroidVersion($userAgent, false);

            if ($operaVersion !== null && $androidVersion !== null) {
                $operaModel = $isOperaTablet ? 'Opera Tablet' : 'Opera Mobi';
                $prefix     = $operaModel . ' ' . $operaVersion . ' Android ' . $androidVersion . WurflConstants::RIS_DELIMITER;

                return $prefix . $userAgent;
            }
        }

        // Stock Android
        if (!Utils::checkIfContainsAnyOf($userAgent, $this->skip_normalization)) {
            $model   = AndroidHandler::getAndroidModel($userAgent, false);
            $version = AndroidHandler::getAndroidVersion($userAgent, false);

            if ($model !== null && $version !== null) {
                $prefix = $version . ' ' . $model . WurflConstants::RIS_DELIMITER;

                return $prefix . $userAgent;
            }
        }

        return $userAgent;
    }
}
