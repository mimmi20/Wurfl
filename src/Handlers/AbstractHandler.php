<?php
/**
 * Copyright (c) 2015 ScientiaMobile, Inc.
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 * Refer to the COPYING.txt file distributed with this package.
 *
 * @category   WURFL
 *
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */

namespace Wurfl\Handlers;

use Psr\Log\LoggerInterface;
use Wurfl\Handlers\MatcherInterface\FilterInterface;
use Wurfl\Handlers\MatcherInterface\HandlerInterface;
use Wurfl\Handlers\MatcherInterface\MatcherCanHandleInterface;
use Wurfl\Handlers\Normalizer\NormalizerInterface;
use Wurfl\Handlers\Normalizer\NullNormalizer;
use Wurfl\Request\GenericRequest;
use Wurfl\Storage\Storage;
use Wurfl\WurflConstants;

/**
 * \Wurfl\Handlers\AbstractHandler is the base class that combines the classification of
 * the user agents and the matching process.
 *
 * @category   WURFL
 *
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */
abstract class AbstractHandler implements FilterInterface, HandlerInterface, MatcherCanHandleInterface
{
    /**
     * @var \Wurfl\Handlers\Normalizer\UserAgentNormalizer
     */
    protected $userAgentNormalizer;

    /**
     * @var string Prefix for this User Agent Handler
     */
    protected $prefix;

    /**
     * @var array Array of user agents with device IDs
     */
    protected $userAgentsWithDeviceID;

    /**
     * @var \Wurfl\Storage\Storage
     */
    protected $persistenceProvider;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var array Array of WURFL IDs that are hard-coded in this matcher
     */
    public static $constantIDs = array();

    /**
     * @param \Wurfl\Handlers\Normalizer\NormalizerInterface $userAgentNormalizer
     */
    public function __construct(NormalizerInterface $userAgentNormalizer = null)
    {
        if (is_null($userAgentNormalizer)) {
            $this->userAgentNormalizer = new NullNormalizer();
        } else {
            $this->userAgentNormalizer = $userAgentNormalizer;
        }
    }

    /**
     * sets the logger
     *
     * @var \Psr\Log\LoggerInterface
     *
     * @return \Wurfl\Handlers\AbstractHandler
     */
    public function setLogger(LoggerInterface $logger = null)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * sets the Persitence Cache
     *
     * @var \Wurfl\Storage\Storage
     *
     * @return \Wurfl\Handlers\AbstractHandler
     */
    public function setPersistenceProvider(Storage $persistenceProvider)
    {
        $this->persistenceProvider = $persistenceProvider;

        return $this;
    }

    /**
     * Alias for getPrefix()
     *
     * @return string Prefix
     * @see getPrefix()
     */
    public function getName()
    {
        return $this->getPrefix();
    }

    //********************************************************
    //
    //     Classification of the User Agents
    //
    //********************************************************
    /**
     * Classifies the given $userAgent and specified $deviceID
     *
     * @param string $userAgent
     * @param string $deviceID
     *
     * @return bool
     */
    public function filter($userAgent, $deviceID)
    {
        if ($this->canHandle($userAgent)) {
            $this->updateUserAgentsWithDeviceIDMap($userAgent, $deviceID);

            return true;
        }

        return false;
    }

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
     */
    final public function updateUserAgentsWithDeviceIDMap($userAgent, $deviceID)
    {
        $this->userAgentsWithDeviceID[$this->normalizeUserAgent($userAgent)] = $deviceID;
    }

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
    public function normalizeUserAgent($userAgent)
    {
        return $this->userAgentNormalizer->normalize($userAgent);
    }

    //********************************************************
    //    Persisting The classified user agents
    //
    //********************************************************
    /**
     * Saves the classified user agents in the persistence provider
     */
    public function persistData()
    {
        // we sort the array first, useful for doing ris match
        if (!empty($this->userAgentsWithDeviceID)) {
            ksort($this->userAgentsWithDeviceID);

            $this->persistenceProvider->save($this->getPrefix(), $this->userAgentsWithDeviceID);
        }
    }

    /**
     * Returns a list of User Agents with their Device IDs
     *
     * @return array User agents and device IDs
     */
    public function getUserAgentsWithDeviceId()
    {
        $this->userAgentsWithDeviceID = $this->persistenceProvider->load($this->getPrefix());

        if (!is_array($this->userAgentsWithDeviceID)) {
            $this->userAgentsWithDeviceID = array();
        }

        return $this->userAgentsWithDeviceID;
    }

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
    final public function applyMatch(GenericRequest $request)
    {
        $className                        = get_class($this);
        $request->getMatchInfo()->matcher = $className;
        $startTime                        = microtime(true);

        $userAgent                                    = $this->normalizeUserAgent($request->getUserAgentNormalized());
        $request->getMatchInfo()->normalizedUserAgent = $userAgent;
        $this->logger->debug('START: Matching For ' . $userAgent);

        // Get The data associated with this current handler
        $this->getUserAgentsWithDeviceId();

        $matches = array(
            'exact'             => array(
                'history'  => '(exact),',
                'function' => 'applyExactMatch',
                'debug'    => 'Applying Exact Match',
            ),
            'conclusive'        => array(
                'history'  => '(conclusive),',
                'function' => 'applyConclusiveMatch',
                'debug'    => 'Applying Conclusive Match',
            ),
            'recovery'          => array(
                'history'  => '(recovery),',
                'function' => 'applyRecoveryMatch',
                'debug'    => 'Applying Recovery Match',
            ),
            'recovery-catchall' => array(
                'history'  => '(recovery-catchall),',
                'function' => 'applyRecoveryCatchAllMatch',
                'debug'    => 'Applying Catch All Recovery Match',
            ),
        );

        $deviceID = WurflConstants::NO_MATCH;

        foreach ($matches as $matchType => $matchProps) {
            $matchProps = (object) $matchProps;

            $request->getMatchInfo()->matcherHistory .= $className . $matchProps->history;
            $request->getMatchInfo()->matchType   = $matchType;
            $request->setUserAgentsWithDeviceID($this->userAgentsWithDeviceID);

            $this->logger->debug($this->prefix . ' :' . $matchProps->debug . ' for ua: ' . $userAgent);

            $function = $matchProps->function;
            $deviceID = $this->$function($userAgent);

            if (!$this->isBlankOrGeneric($deviceID)) {
                break;
            }
        }

        // All attempts to match have failed
        if ($this->isBlankOrGeneric($deviceID)) {
            $request->getMatchInfo()->matchType = 'none';

            if ($request->getUserAgentProfile()) {
                $deviceID = WurflConstants::GENERIC_MOBILE;
            } else {
                $deviceID = WurflConstants::GENERIC;
            }
        }

        $this->logger->debug('END: Matching For ' . $userAgent);
        $request->getMatchInfo()->lookupTime = microtime(true) - $startTime;

        return $deviceID;
    }

    /**
     * Given $deviceID is blank or generic, indicating no match
     *
     * @param string $deviceID
     *
     * @return bool
     */
    private function isBlankOrGeneric($deviceID)
    {
        return ($deviceID === WurflConstants::NO_MATCH || strcmp($deviceID, 'generic') === 0 || strlen(
            trim($deviceID)
        ) === 0);
    }

    /**
     * Attempt to find a exact match for the given $userAgent
     *
     * @param string $userAgent
     *
     * @return string Matching WURFL deviceID
     */
    public function applyExactMatch($userAgent)
    {
        if (array_key_exists($userAgent, $this->userAgentsWithDeviceID)) {
            return $this->userAgentsWithDeviceID[$userAgent];
        }

        return WurflConstants::NO_MATCH;
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
        $match = $this->lookForMatchingUserAgent($userAgent);

        if (!empty($match)) {
            return $this->userAgentsWithDeviceID[$match];
        }

        return WurflConstants::NO_MATCH;
    }

    /**
     * Find a matching WURFL device from the given $userAgent. Override this method to give an alternative way to do
     * the matching
     *
     * @param string $userAgent
     *
     * @return string
     */
    public function lookForMatchingUserAgent($userAgent)
    {
        $tolerance = Utils::firstSlash($userAgent);

        return Utils::risMatch(array_keys($this->userAgentsWithDeviceID), $userAgent, $tolerance);
    }

    /**
     * @param string  $userAgent
     * @param integer $tolerance
     *
     * @return null|string
     */
    public function getDeviceIDFromRIS($userAgent, $tolerance)
    {
        if ($tolerance === null) {
            return WurflConstants::NO_MATCH;
        }

        $match = Utils::risMatch(array_keys($this->userAgentsWithDeviceID), $userAgent, $tolerance);

        if (!empty($match)) {
            return $this->userAgentsWithDeviceID[$match];
        }

        return WurflConstants::NO_MATCH;
    }

    /**
     * @param string  $userAgent
     * @param integer $tolerance
     *
     * @return null|string
     */
    public function getDeviceIDFromLD($userAgent, $tolerance = null)
    {
        $match = Utils::ldMatch(array_keys($this->userAgentsWithDeviceID), $userAgent, $tolerance);

        if (!empty($match)) {
            return $this->userAgentsWithDeviceID[$match];
        }

        return WurflConstants::NO_MATCH;
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
        return WurflConstants::NO_MATCH;
    }

    /**
     * Applies Catch-All match
     *
     * @param string $userAgent
     *
     * @return string WURFL deviceID
     */
    public function applyRecoveryCatchAllMatch($userAgent)
    {
        if (Utils::isDesktopBrowserHeavyDutyAnalysis($userAgent)) {
            return WurflConstants::GENERIC_WEB_BROWSER;
        }

        if (Utils::checkIfContains($userAgent, 'CoreMedia')) {
            return 'apple_iphone_coremedia_ver1';
        }

        if (Utils::checkIfContains($userAgent, 'Windows CE')) {
            return 'generic_ms_mobile';
        }

        if (Utils::checkIfContains($userAgent, 'UP.Browser/7.2')) {
            return 'opwv_v72_generic';
        }

        if (Utils::checkIfContains($userAgent, 'UP.Browser/7')) {
            return 'opwv_v7_generic';
        }

        if (Utils::checkIfContains($userAgent, 'UP.Browser/6.2')) {
            return 'opwv_v62_generic';
        }

        if (Utils::checkIfContains($userAgent, 'UP.Browser/6')) {
            return 'opwv_v6_generic';
        }

        if (Utils::checkIfContains($userAgent, 'UP.Browser/5')) {
            return 'upgui_generic';
        }

        if (Utils::checkIfContains($userAgent, 'UP.Browser/4')) {
            return 'uptext_generic';
        }

        if (Utils::checkIfContains($userAgent, 'UP.Browser/3')) {
            return 'uptext_generic';
        }

        // Series 60
        if (Utils::checkIfContains($userAgent, 'Series60')) {
            return 'nokia_generic_series60';
        }

        // Access/Net Front
        if (Utils::checkIfContainsAnyOf($userAgent, array('NetFront/3.0', 'ACS-NF/3.0'))) {
            return 'generic_netfront_ver3';
        }

        if (Utils::checkIfContainsAnyOf($userAgent, array('NetFront/3.1', 'ACS-NF/3.1'))) {
            return 'generic_netfront_ver3_1';
        }

        if (Utils::checkIfContainsAnyOf($userAgent, array('NetFront/3.2', 'ACS-NF/3.2'))) {
            return 'generic_netfront_ver3_2';
        }

        if (Utils::checkIfContainsAnyOf($userAgent, array('NetFront/3.3', 'ACS-NF/3.3'))) {
            return 'generic_netfront_ver3_3';
        }

        if (Utils::checkIfContains($userAgent, 'NetFront/3.4')) {
            return 'generic_netfront_ver3_4';
        }

        if (Utils::checkIfContains($userAgent, 'NetFront/3.5')) {
            return 'generic_netfront_ver3_5';
        }

        if (Utils::checkIfContains($userAgent, 'NetFront/4.0')) {
            return 'generic_netfront_ver4_0';
        }

        // Contains Mozilla/, but not at the beginning of the UA
        // ie: MOTORAZR V8/R601_G_80.41.17R Mozilla/4.0 (compatible; MSIE 6.0 Linux; MOTORAZR V88.50) Profile/MIDP-2.0 Configuration/CLDC-1.1 Opera 8.50[zh]
        if (strpos($userAgent, 'Mozilla/') > 0) {
            return WurflConstants::GENERIC_XHTML;
        }

        if (Utils::checkIfContainsAnyOf(
            $userAgent,
            array('Obigo', 'AU-MIC/2', 'AU-MIC-', 'AU-OBIGO/', 'Teleca Q03B1')
        )
        ) {
            return WurflConstants::GENERIC_XHTML;
        }

        // DoCoMo
        if (Utils::checkIfStartsWithAnyOf($userAgent, array('DoCoMo', 'KDDI'))) {
            return 'docomo_generic_jap_ver1';
        }

        if (Utils::isMobileBrowser($userAgent)) {
            return WurflConstants::GENERIC_MOBILE;
        }

        return WurflConstants::GENERIC;
    }

    /**
     * Returns the prefix for this Handler, like BLACKBERRY_DEVICEIDS for the
     * BlackBerry Handler.  The 'BLACKBERRY_' portion comes from the individual
     * Handler's $prefix property and '_DEVICEIDS' is added here.
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix . '_DEVICEIDS';
    }

    public function getNiceName()
    {
        $className = get_class($this);

        // \Wurfl\Handlers\AlcatelHandler
        preg_match('/^\Wurfl\Handlers\(.+)Handler$/', $className, $matches);

        return $matches[1];
    }

    /**
     * Returns true if given $deviceId exists
     *
     * @param string $deviceId
     *
     * @return bool
     */
    protected function isDeviceExist($deviceId)
    {
        $ids = array_values($this->userAgentsWithDeviceID);

        if (in_array($deviceId, $ids)) {
            return true;
        }

        return false;
    }

    public function __sleep()
    {
        return array(
            'userAgentNormalizer',
            'prefix',
        );
    }
}
