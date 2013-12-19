<?php
namespace Wurfl\Request\Normalizer\Generic;

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
 * @category   WURFL
 * @package    \Wurfl\Request\Normalizer\UserAgentNormalizer_Generic
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @author     Fantayeneh Asres Gizaw
 * @version    $id$
 */
use Wurfl\Request\Normalizer\NormalizerInterface;

/**
 * User Agent Normalizer - removes BabelFish garbage from user agent
 *
 * @package    \Wurfl\Request\Normalizer\UserAgentNormalizer_Generic
 */
class BabelFish implements NormalizerInterface
{

    const BABEL_FISH_REGEX = "/\\s*\\(via babelfish.yahoo.com\\)\\s*/";

    public function normalize($userAgent)
    {
        return preg_replace(self::BABEL_FISH_REGEX, "", $userAgent);
    }
}