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

use Wurfl\Constants;
use Wurfl\Handlers\SafariHandler;
use Wurfl\Request\Normalizer\NormalizerInterface;

/**
 * User Agent Normalizer
 * Return the safari user agent stripping out
 *     - all the chararcters between U; and Safari/xxx
 *
 *  e.g Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_4_11; fr) ... Version/3.1.1 Safari/525.18
 *         becomes
 *         Mozilla/5.0 (Macintosh Safari/525
 *
 * @package    \Wurfl\Request\Normalizer\Specific
 */
class Safari
    implements NormalizerInterface
{
    /**
     * @param string $userAgent
     *
     * @return string
     */
    public function normalize($userAgent)
    {
        $safariVersion = SafariHandler::getSafariVersion($userAgent);

        if (!$safariVersion) {
            return $userAgent;
        }

        $prefix = 'Safari ' . $safariVersion . Constants::RIS_DELIMITER;

        return $prefix . $userAgent;
    }
}
