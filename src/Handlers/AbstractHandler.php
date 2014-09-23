<?php
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
 *
 * @category   WURFL
 * @package    WURFL
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */

namespace Wurfl\Handlers;

use Psr\Log\LoggerInterface;
use Wurfl\Constants;
use Wurfl\Context;
use Wurfl\Request\GenericRequest;
use Wurfl\Request\Normalizer\NullNormalizer;
use Wurfl\Storage\Storage;

/**
 * \Wurfl\Handlers\AbstractHandler is the base class that combines the classification of
 * the user agents and the matching process.
 *
 * @category   WURFL
 * @package    WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
abstract class AbstractHandler
    implements FilterInterface
{
    /**
     * The next User Agent Handler
     *
     * @var \Wurfl\Handlers\AbstractHandler
     */
    protected $nextHandler;

    /**
     * @var \Wurfl\Request\Normalizer\UserAgentNormalizer
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
     * @param \Wurfl\Context                                $wurflContext
     * @param \Wurfl\Request\Normalizer\NormalizerInterface $userAgentNormalizer
     */
    public function __construct(Context $wurflContext, $userAgentNormalizer = null)
    {
        if (is_null($userAgentNormalizer)) {
            $this->userAgentNormalizer = new NullNormalizer();
        } else {
            $this->userAgentNormalizer = $userAgentNormalizer;
        }

        $this->setupContext($wurflContext);
    }

    /**
     * Sets the next Handler
     *
     * @param \Wurfl\Handlers\AbstractHandler $handler
     */
    public function setNextHandler(AbstractHandler $handler)
    {
        $this->nextHandler = $handler;
    }

    /**
     * sets the logger
     *
     * @var \Psr\Log\LoggerInterface $logger
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
     * @var \Wurfl\Storage\Storage $persistenceProvider
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

    /**
     * sets the logger and the storage from the context
     *
     * @param \Wurfl\Context $wurflContext
     *
     * @return \Wurfl\Handlers\AbstractHandler
     */
    public function setupContext(Context $wurflContext)
    {
        $this->setLogger($wurflContext->logger)
            ->setPersistenceProvider($wurflContext->persistenceProvider);

        return $this;
    }

    /**
     * Returns true if this handler can handle the given $userAgent
     *
     * @param string $userAgent
     *
     * @return bool
     */
    abstract public function canHandle($userAgent);

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
     * @return null
     */
    public function filter($userAgent, $deviceID)
    {
        if ($this->canHandle($userAgent)) {
            $this->updateUserAgentsWithDeviceIDMap($userAgent, $deviceID);

            return null;
        }

        if (isset($this->nextHandler)) {
            return $this->nextHandler->filter($userAgent, $deviceID);
        }

        return null;
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
     * @see $userAgentNormalizer, \Wurfl\Request\Normalizer\UserAgentNormalizer
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
        $className                   = get_class($this);
        $request->matchInfo->matcher = $className;
        $startTime                   = microtime(true);

        $userAgent                               = $this->normalizeUserAgent($request->userAgent);
        $request->matchInfo->normalizedUserAgent = $userAgent;
        $this->logger->debug('START: Matching For  ' . $userAgent);

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
            )
        );

        $deviceID = Constants::NO_MATCH;

        foreach ($matches as $matchType => $matchProps) {
            $matchProps = (object) $matchProps;

            $request->matchInfo->matcherHistory .= $className . $matchProps->history;
            $request->matchInfo->matchType   = $matchType;
            $request->userAgentsWithDeviceID = $this->userAgentsWithDeviceID;

            $this->logger->debug($this->prefix . ' :' . $matchProps->debug . ' for ua: ' . $userAgent);

            $function = $matchProps->function;
            $deviceID = $this->$function($userAgent);

            if (!$this->isBlankOrGeneric($deviceID)) {
                break;
            }
        }

        // All attempts to match have failed
        if ($this->isBlankOrGeneric($deviceID)) {
            $request->matchInfo->matchType = 'none';

            if ($request->userAgentProfile) {
                $deviceID = Constants::GENERIC_MOBILE;
            } else {
                $deviceID = Constants::GENERIC;
            }
        }

        $this->logger->debug('END: Matching For  ' . $userAgent);
        $request->matchInfo->lookupTime = microtime(true) - $startTime;

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
        return ($deviceID === Constants::NO_MATCH || strcmp($deviceID, 'generic') === 0 || strlen(
                trim($deviceID)
            ) == 0);
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

        return Constants::NO_MATCH;
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

        return Constants::NO_MATCH;
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

    public function getDeviceIDFromRIS($userAgent, $tolerance)
    {
        $match = Utils::risMatch(array_keys($this->userAgentsWithDeviceID), $userAgent, $tolerance);

        if (!empty($match)) {
            return $this->userAgentsWithDeviceID[$match];
        }

        return Constants::NO_MATCH;
    }

    public function getDeviceIDFromLD($userAgent, $tolerance = null)
    {
        $match = Utils::ldMatch(array_keys($this->userAgentsWithDeviceID), $userAgent, $tolerance);

        if (!empty($match)) {
            return $this->userAgentsWithDeviceID[$match];
        }

        return Constants::NO_MATCH;
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
        return Constants::NO_MATCH;
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
            return Constants::GENERIC_WEB_BROWSER;
        }

        $mobile  = Utils::isMobileBrowser($userAgent);
        $desktop = Utils::isDesktopBrowser($userAgent);

        if (!$desktop) {
            $deviceId = Utils::getMobileCatchAllId($userAgent);

            if ($deviceId !== Constants::NO_MATCH) {
                return $deviceId;
            }
        }

        if ($mobile) {
            return Constants::GENERIC_MOBILE;
        }

        if ($desktop) {
            return Constants::GENERIC_WEB_BROWSER;
        }

        return Constants::GENERIC;
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
            'nextHandler',
            'userAgentNormalizer',
            'prefix',
        );
    }
}
