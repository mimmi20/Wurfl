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
 * @version    $id$
 */

namespace Wurfl\Handlers;

/**
 * CatchAllUserAgentHandler
 *
 * @category   WURFL
 * @package    WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
class CatchAllRisHandler
    extends AbstractHandler
{
    protected $prefix = 'CATCH_ALL_RIS';

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
        return true;
    }

    /**
     * Apply RIS on Firts slash
     *
     * @param string $userAgent
     *
     * @return string
     */
    public function applyConclusiveMatch($userAgent)
    {
        $tolerance = Utils::firstSlash($userAgent);

        return $this->getDeviceIDFromRIS($userAgent, $tolerance);
    }
}
