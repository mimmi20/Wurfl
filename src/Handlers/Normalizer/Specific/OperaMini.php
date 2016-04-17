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
 *
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */

namespace Wurfl\Handlers\Normalizer\Specific;

use Wurfl\Handlers\Normalizer\NormalizerInterface;
use Wurfl\Handlers\OperaMiniHandler;
use Wurfl\WurflConstants;

/**
 * User Agent Normalizer
 */
class OperaMini implements NormalizerInterface
{
    public function normalize($userAgent)
    {
        $model = OperaMiniHandler::getOperaModel($userAgent, false);

        if ($model !== null) {
            $prefix    = $model . WurflConstants::RIS_DELIMITER;
            $userAgent = $prefix . $userAgent;

            return $userAgent;
        }

        return $userAgent;
    }
}
