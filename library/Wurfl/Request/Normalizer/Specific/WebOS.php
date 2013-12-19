<?php
namespace Wurfl\Request\Normalizer\Specific;

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
 * @package    \Wurfl\Request\Normalizer\UserAgentNormalizer_Specific
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @author     Fantayeneh Asres Gizaw
 * @version    $id$
 */
use Wurfl\Constants;
use Wurfl\Handlers\WebOSHandler;
use Wurfl\Request\Normalizer\NormalizerInterface;

/**
 * User Agent Normalizer
 *
 * @package    \Wurfl\Request\Normalizer\UserAgentNormalizer_Specific
 */
class WebOS implements NormalizerInterface
{
    public function normalize($userAgent)
    {
        $model  = WebOSHandler::getWebOSModelVersion($userAgent);
        $os_ver = WebOSHandler::getWebOSVersion($userAgent);
        if ($model !== null && $os_ver !== null) {
            $prefix = $model . ' ' . $os_ver . Constants::RIS_DELIMITER;
            return $prefix . $userAgent;
        }
        return $userAgent;
    }
}