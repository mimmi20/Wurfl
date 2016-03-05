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
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */

namespace Wurfl\Handlers\Normalizer\Generic;

use Wurfl\Handlers\Normalizer\NormalizerInterface;

/**
 * User Agent Normalizer - Trims the version number to two digits (e.g. 2.1.1 -> 2.1)
 */
class Android implements NormalizerInterface
{
    /**
     * @param string $userAgent
     *
     * @return string
     */
    public function normalize($userAgent)
    {
        // Normalize Android version
        return preg_replace('/(Android)[ \-\/](\d\.\d)([^; \/\)]+)/', '$1 $2', $userAgent);
    }
}
