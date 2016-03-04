<?php
/**
 * Copyright (c) 2015 ScientiaMobile, Inc.
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

namespace Wurfl\Handlers\MatcherInterface;

use Psr\Log\LoggerInterface;
use Wurfl\Handlers\Normalizer\NormalizerInterface;
use Wurfl\Request\GenericRequest;
use Wurfl\Storage\Storage;

/**
 * \Wurfl\Handlers\AbstractHandler is the base class that combines the classification of
 * the user agents and the matching process.
 *
 * @category   WURFL
 *
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */
interface HandlerInterface
{
    /**
     * @param \Wurfl\Handlers\Normalizer\NormalizerInterface $userAgentNormalizer
     * @return void
     */
    public function __construct(NormalizerInterface $userAgentNormalizer = null);

    /**
     * sets the logger
     *
     * @var \Psr\Log\LoggerInterface
     *
     * @return \Wurfl\Handlers\AbstractHandler
     */
    public function setLogger(LoggerInterface $logger = null);

    /**
     * sets the Persitence Cache
     *
     * @var \Wurfl\Storage\Storage
     *
     * @return \Wurfl\Handlers\AbstractHandler
     */
    public function setPersistenceProvider(Storage $persistenceProvider);

    /**
     * Alias for getPrefix()
     *
     * @return string Prefix
     *
     * @see getPrefix()
     */
    public function getName();

    /**
     * Updates the map containing the classified user agents.  These are stored in the associative
     * array userAgentsWithDeviceID like user_agent => deviceID.
     * Before adding the user agent to the map it normalizes by using the normalizeUserAgent
     * function.
     *
     * @see normalizeUserAgent()
     * @see userAgentsWithDeviceID
     *
     * @param string $userAgent
     * @param string $deviceID
     * @return void
     */
    public function updateUserAgentsWithDeviceIDMap($userAgent, $deviceID);

    /**
     * Normalizes the given $userAgent using this handler's User Agent Normalizer.
     * If you need to normalize the user agent you need to override the function in
     * the specific user agent handler.
     *
     * @see $userAgentNormalizer, \Wurfl\Handlers\Normalizer\UserAgentNormalizer
     *
     * @param string $userAgent
     *
     * @return string Normalized user agent
     */
    public function normalizeUserAgent($userAgent);

    /**
     * Returns a list of User Agents with their Device IDs
     *
     * @return array User agents and device IDs
     */
    public function getUserAgentsWithDeviceId();

    //********************************************************
    //    Matching
    //
    /**
     * Template method to apply matching system to user agent
     *
     * @param \Wurfl\Request\GenericRequest $request
     *
     * @return string Device ID
     */
    public function applyMatch(GenericRequest $request);

    /**
     * Attempt to find a exact match for the given $userAgent
     *
     * @param string $userAgent
     *
     * @return string Matching WURFL deviceID
     */
    public function applyExactMatch($userAgent);

    /**
     * Attempt to find a conclusive match for the given $userAgent
     *
     * @param string $userAgent
     *
     * @return string Matching WURFL deviceID
     */
    public function applyConclusiveMatch($userAgent);

    /**
     * Find a matching WURFL device from the given $userAgent. Override this method to give an alternative way to do
     * the matching
     *
     * @param string $userAgent
     *
     * @return string
     */
    public function lookForMatchingUserAgent($userAgent);

    public function getDeviceIDFromRIS($userAgent, $tolerance);

    public function getDeviceIDFromLD($userAgent, $tolerance = null);

    /**
     * Applies Recovery Match
     *
     * @param string $userAgent
     *
     * @return string $deviceID
     */
    public function applyRecoveryMatch($userAgent);

    /**
     * Applies Catch-All match
     *
     * @param string $userAgent
     *
     * @return string WURFL deviceID
     */
    public function applyRecoveryCatchAllMatch($userAgent);

    /**
     * Returns the prefix for this Handler, like BLACKBERRY_DEVICEIDS for the
     * BlackBerry Handler.  The 'BLACKBERRY_' portion comes from the individual
     * Handler's $prefix property and '_DEVICEIDS' is added here.
     *
     * @return string
     */
    public function getPrefix();

    /**
     * @return string
     */
    public function getNiceName();

    /**
     * @return string[]
     */
    public function __sleep();
}
