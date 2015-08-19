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

namespace Wurfl\Handlers\Normalizer\Generic;

use Wurfl\Handlers\Normalizer\NormalizerInterface;

/**
 * User Agent Normalizer - removes UP.Link garbage from user agent
 *
 * @package    \Wurfl\Handlers\Normalizer\UserAgentNormalizer_Generic
 */
class UPLink
    implements NormalizerInterface
{
    /**
     * This method remove the 'UP.Link' substring from user agent string.
     *
     * @param string $userAgent
     *
     * @return string Normalized user agent
     */
    public function normalize($userAgent)
    {
        $index = strpos($userAgent, ' UP.Link');

        if ($index > 0) {
            return substr($userAgent, 0, $index);
        }

        return $userAgent;
    }
}
