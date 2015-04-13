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

namespace Wurfl\Request\Normalizer\Generic;

use Wurfl\Request\Normalizer\NormalizerInterface;

/**
 * User Agent Normalizer - removes locale information from user agent
 *
 * @package    \Wurfl\Request\Normalizer\UserAgentNormalizer_Generic
 */
class TransferEncoding
    implements NormalizerInterface
{
    /**
     * @param string $userAgent
     *
     * @return mixed|string
     */
    public function normalize($userAgent)
    {
        return str_replace(',gzip(gfe)', '', $userAgent);
    }
}
