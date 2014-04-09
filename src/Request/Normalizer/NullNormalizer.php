<?php
namespace Wurfl\Request\Normalizer;

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
     * @package    \Wurfl\Request\Normalizer\UserAgentNormalizer
     * @copyright  ScientiaMobile, Inc.
     * @license    GNU Affero General Public License
     * @author     Fantayeneh Asres Gizaw
     * @version    $id$
     */
/**
 * Null User Agent Normalizer - does not normalize anything
 *
 * @package    \Wurfl\Request\Normalizer\UserAgentNormalizer
 */
class NullNormalizer implements NormalizerInterface
{
    /**
     * @param string $userAgent
     *
     * @return string
     */
    public function normalize($userAgent)
    {
        return $userAgent;
    }
}
