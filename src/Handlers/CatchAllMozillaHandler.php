<?php
/**
 * Copyright (c) 2014 ScientiaMobile, Inc.
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 * Refer to the COPYING.txt file distributed with this package.
 *
 * @category   WURFL
 * @package    WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */

namespace Wurfl\Handlers;

/**
 * CatchAllUserAgentHandler
 *
 * @category   WURFL
 * @package    WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */
class CatchAllMozillaHandler
    extends AbstractHandler
{
    protected $prefix = 'CATCH_ALL_MOZILLA';

    /**
     * Final Interceptor: Intercept
     * Everything that has not been trapped by a previous handler
     *
     * @param string $userAgent
     *
     * @return boolean always true
     */
    public function canHandle($userAgent)
    {
        return (Utils::checkIfStartsWith($userAgent, 'Mozilla/4') || Utils::checkIfStartsWith($userAgent, 'Mozilla/5'));
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
        return $this->getDeviceIDFromLD($userAgent, 5);
    }
}
