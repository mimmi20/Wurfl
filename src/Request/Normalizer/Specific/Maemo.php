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
 * @package    \Wurfl\Request\Normalizer\Specific
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @author     Fantayeneh Asres Gizaw
 * @version    $id$
 */
use Wurfl\Constants;
use Wurfl\Handlers\MaemoHandler;
use Wurfl\Request\Normalizer\NormalizerInterface;

/**
 * User Agent Normalizer
 *
 * @package    \Wurfl\Request\Normalizer\Specific
 */
class Maemo implements NormalizerInterface
{
    /**
     * @param string $userAgent
     *
     * @return string
     */
    public function normalize($userAgent)
    {
        $model = MaemoHandler::getMaemoModel($userAgent);

        if ($model !== null) {
            $prefix = 'Maemo ' . $model . Constants::RIS_DELIMITER;
            return $prefix . $userAgent;
        }

        return $userAgent;
    }
}
