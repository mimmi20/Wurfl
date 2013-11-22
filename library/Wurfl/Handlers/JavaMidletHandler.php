<?php
namespace Wurfl\Handlers;

    /**
     * Copyright (c) 2012 ScientiaMobile, Inc.
     * This program is free software: you can redistribute it and/or modify
     * it under the terms of the GNU Affero General Public License as
     * published by the Free Software Foundation, either version 3 of the
     * License, or (at your option) any later version.
     * Refer to the COPYING.txt file distributed with this package.
     *
     * @category   WURFL
     * @package    \Wurfl\Handlers
     * @copyright  ScientiaMobile, Inc.
     * @license    GNU Affero General Public License
     * @version    $id$
     */

/**
 * JavaMidletUserAgentHandler
 *
 * @category   WURFL
 * @package    \Wurfl\Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
class JavaMidletHandler extends Handler
{
    public static $constantIDs
        = array(
            'generic_midp_midlet',
        );

    public function canHandle($userAgent)
    {
        return Utils::checkIfContains($userAgent, 'UNTRUSTED/1.0');
    }

    public function applyConclusiveMatch($userAgent)
    {
        return 'generic_midp_midlet';
    }

    protected $prefix = "JAVAMIDLET";
}
