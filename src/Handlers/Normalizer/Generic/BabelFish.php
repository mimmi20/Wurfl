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
 * User Agent Normalizer - removes BabelFish garbage from user agent
 *
 * @package    \Wurfl\Handlers\Normalizer\UserAgentNormalizer_Generic
 */
class BabelFish implements NormalizerInterface
{
    /**
     * @var string
     */
    const BABEL_FISH_REGEX = '/\\s*\\(via babelfish.yahoo.com\\)\\s*/';

    /**
     * @param string $userAgent
     *
     * @return mixed|string
     */
    public function normalize($userAgent)
    {
        return preg_replace(self::BABEL_FISH_REGEX, '', $userAgent);
    }
}
