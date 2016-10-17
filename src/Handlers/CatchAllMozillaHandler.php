<?php
/**
 * Copyright (c) 2014 ScientiaMobile, Inc.
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 * Refer to the LICENSE file distributed with this package.
 *
 * @category   WURFL
 *
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */

namespace Wurfl\Handlers;

use UaNormalizer\Helper\Utils;

/**
 * CatchAllUserAgentHandler
 *
 * @category   WURFL
 *
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */
class CatchAllMozillaHandler extends AbstractHandler
{
    protected $prefix = 'CATCH_ALL_MOZILLA';

    /**
     * Final Interceptor: Intercept
     * Everything that has not been trapped by a previous handler
     *
     * @param string $userAgent
     *
     * @return bool always true
     */
    public function canHandle($userAgent)
    {
        return Utils::checkIfStartsWithAnyOf($userAgent, array('Mozilla/3', 'Mozilla/4', 'Mozilla/5'));
    }

    /**
     * If UA starts with Mozilla, apply LD with tollerance 5.
     *
     * @param string $userAgent
     *
     * @return string
     */
    public function applyConclusiveMatch($userAgent)
    {
        //High accuracy mode
        $tolerance = Utils::firstCloseParen($userAgent);

        return $this->getDeviceIDFromRIS($userAgent, $tolerance);
    }
}
