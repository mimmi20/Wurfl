<?php
namespace Wurfl\Request\Normalizer\Specific;

/**
 * Copyright (c) 2014 ScientiaMobile, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Refer to the COPYING.txt file distributed with this package.
 *
 * @category   WURFL
 * @package    \Wurfl\Request\Normalizer\Specific
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */

use Wurfl\Request\Normalizer\NormalizerInterface;

/**
 * User Agent Normalizer
 *
 * @package    \Wurfl\Request\Normalizer\Specific
 */
class Apple implements NormalizerInterface
{
    public function normalize($userAgent)
    {
        // Normalize Skype SDK UAs
        if (preg_match('#^iOSClientSDK/\d+\.+[0-9\.]+ +?\((Mozilla.+)\)$#', $userAgent, $matches)) {
            return $matches[1];
        }
        return $userAgent;
    }
}