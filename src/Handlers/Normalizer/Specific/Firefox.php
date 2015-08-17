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

namespace Wurfl\Handlers\Normalizer\Specific;

use Wurfl\Handlers\Normalizer\NormalizerInterface;

/**
 * User Agent Normalizer - Return the firefox string with the major and minor version only
 *
 * @package    \Wurfl\Handlers\Normalizer\Specific
 */
class Firefox
    implements NormalizerInterface
{
    /**
     * @param string $userAgent
     *
     * @return string
     */
    public function normalize($userAgent)
    {
        return $this->firefoxWithMajorAndMinorVersion($userAgent);
    }

    /**
     * Returns FireFox major and minor version numbers
     *
     * @param string $userAgent
     *
     * @return string Major and minor version
     */
    private function firefoxWithMajorAndMinorVersion($userAgent)
    {
        return substr($userAgent, strpos($userAgent, 'Firefox'));
    }
}
