<?php
/**
 * Copyright (c) 2012 ScientiaMobile, Inc.
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

namespace Wurfl\Request\Normalizer\Specific;

use Wurfl\Request\Normalizer\NormalizerInterface;

/**
 * User Agent Normalizer - Return the Konqueror user agent with the major version
 * e.g
 *     Mozilla/5.0 (compatible; Konqueror/4.1; Linux) KHTML/4.1.2 (like Gecko) -> Konqueror/4
 *
 * @package    \Wurfl\Request\Normalizer\Specific
 */
class Konqueror
    implements NormalizerInterface
{
    /**
     * @var string
     */
    const KONQUEROR = "Konqueror";

    /**
     * @param string $userAgent
     *
     * @return string
     */
    public function normalize($userAgent)
    {
        return $this->konquerorWithMajorVersion($userAgent);
    }

    /**
     * Return KDE Konquerer major version
     *
     * @param string $userAgent
     *
     * @return string Major version number
     */
    private function konquerorWithMajorVersion($userAgent)
    {
        return substr($userAgent, strpos($userAgent, self::KONQUEROR), 10);
    }
}
