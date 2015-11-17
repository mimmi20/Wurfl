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

namespace Wurfl\Handlers;

/**
 * SmartTVUserAgentHandler
 *
 *
 * @category   WURFL
 * @package    WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */
class SmartTVHandler extends AbstractHandler
{
    protected $prefix = 'SMARTTV';

    public static $constantIDs = array(
        'generic_smarttv_browser',
        'generic_smarttv_googletv_browser',
        'generic_smarttv_appletv_browser',
        'generic_smarttv_boxeebox_browser',
        'generic_smarttv_chromecast',
    );

    /**
     * @param string $userAgent
     *
     * @return bool
     */
    public function canHandle($userAgent)
    {
        return Utils::isSmartTV($userAgent);
    }

    /**
     * @param string $userAgent
     *
     * @return null|string
     */
    public function applyConclusiveMatch($userAgent)
    {
        $tolerance = strlen($userAgent);

        return $this->getDeviceIDFromRIS($userAgent, $tolerance);
    }

    /**
     * @param string $userAgent
     *
     * @return string
     */
    public function applyRecoveryMatch($userAgent)
    {
        if (Utils::checkIfContains($userAgent, 'SmartTV')) {
            return 'generic_smarttv_browser';
        }

        if (Utils::checkIfContains($userAgent, 'GoogleTV')) {
            return 'generic_smarttv_googletv_browser';
        }

        if (Utils::checkIfContains($userAgent, 'AppleTV')) {
            return 'generic_smarttv_appletv_browser';
        }

        if (Utils::checkIfContains($userAgent, 'Boxee')) {
            return 'generic_smarttv_boxeebox_browser';
        }

        if (Utils::checkIfContains($userAgent, 'CrKey')) {
            return 'generic_smarttv_chromecast';
        }

        return 'generic_smarttv_browser';
    }
}
