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

namespace Wurfl\Handlers;

use Wurfl\WurflConstants;

/**
 * BotCrawlerTranscoderUserAgentHandler
 *
 *
 * @category   WURFL
 *
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */
class BotCrawlerTranscoderHandler extends AbstractHandler
{
    /**
     * @var string Prefix for this User Agent Handler
     */
    protected $prefix = 'BOT_CRAWLER_TRANSCODER';

    /**
     * Returns true if this handler can handle the given $userAgent
     *
     * @param string $userAgent
     *
     * @return bool
     */
    public function canHandle($userAgent)
    {
        return Utils::isRobot($userAgent);
    }

    /**
     * Attempt to find a conclusive match for the given $userAgent
     *
     * @param string $userAgent
     *
     * @return string Matching WURFL deviceID
     */
    public function applyConclusiveMatch($userAgent)
    {

        if (Utils::checkIfContains($userAgent, 'GoogleImageProxy')) {
            return 'google_image_proxy';
        }

        if (Utils::checkIfStartsWith($userAgent, 'Mozilla')) {
            $tolerance = Utils::firstCloseParen($userAgent);
        } else {
            $tolerance = Utils::firstSlash($userAgent);
        }

        return $this->getDeviceIDFromRIS($userAgent, $tolerance);
    }

    /**
     * Applies Recovery Match
     *
     * @param string $userAgent
     *
     * @return string $deviceID
     */
    public function applyRecoveryMatch($userAgent)
    {
        return WurflConstants::GENERIC_WEB_CRAWLER;
    }
}
