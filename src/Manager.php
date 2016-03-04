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

namespace Wurfl;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Wurfl\Handlers\Chain\UserAgentHandlerChainFactory;
use Wurfl\Request\GenericRequest;
use Wurfl\Request\GenericRequestFactory;

/**
 * WURFL Manager Class - serves as the core class that the developer uses to query
 * the API for device capabilities and WURFL information
 * Examples:
 * <code>
 * // Example 1. Instantiate Manager from Factory:
 * $wurflManager = $wurflManagerFactory->create();
 * // Example 2: Get Visiting Device from HTTP Request
 * $device = $wurflManager->getDeviceForHttpRequest($_SERVER);
 * // Example 3: Get Visiting Device from User Agent
 * $userAgent = 'Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) '
 * . 'Version/4.0.4 Mobile/7B334b Safari/531.21.10';
 * $device = $wurflManager->getDeviceForUserAgent($userAgent);
 * </code>
 *
 * @see     getWurflInfo(), getDeviceForHttpRequest(), getDeviceForUserAgent(), \Wurfl\WURFLManagerFactory::create()
 */
class Manager
{
    const WURFL_API_STATE = 'WURFL_API_STATE';

    /**
     * WURFL Configuration
     *
     * @var Configuration\Config
     */
    private $wurflConfig = null;
    /**
     * @var Storage\Storage
     */
    private $persistenceStorage = null;
    /**
     * @var Storage\Storage
     */
    private $cacheStorage = null;

    /**
     * @var \Wurfl\Device\DeviceRepositoryInterface
     */
    private $deviceRepository = null;

    /**
     * @var \Wurfl\Handlers\Chain\UserAgentHandlerChain
     */
    private $userAgentHandlerChain = null;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger = null;

    /**
     * Creates a new Wurfl Manager object
     *
     * @param Configuration\Config   $wurflConfig
     * @param \Wurfl\Storage\Storage $persistenceStorage
     * @param \Wurfl\Storage\Storage $cacheStorage
     */
    public function __construct(
        Configuration\Config $wurflConfig,
        Storage\Storage $persistenceStorage,
        Storage\Storage $cacheStorage
    ) {
        $this->setWurflConfig($wurflConfig);

        if (null === $persistenceStorage) {
            throw new \InvalidArgumentException('the persistence storage is missing');
        }

        if (null === $cacheStorage) {
            throw new \InvalidArgumentException('the cache storage is missing');
        }

        $this->setPersistenceStorage($persistenceStorage);
        $this->setCacheStorage($cacheStorage);

        if ($this->hasToBeReloaded()) {
            $this->reload();
        }
    }

    /**
     * @return \Wurfl\Storage\Storage
     */
    private function getCacheStorage()
    {
        return $this->cacheStorage;
    }

    /**
     * @param \Wurfl\Storage\Storage $cacheStorage
     */
    private function setCacheStorage(Storage\Storage $cacheStorage)
    {
        $this->cacheStorage = $cacheStorage;
    }

    /**
     * @return \Wurfl\Device\DeviceRepositoryInterface
     */
    private function getDeviceRepository()
    {
        if (null === $this->deviceRepository) {
            $this->init();
        }

        return $this->deviceRepository;
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        if (null === $this->logger) {
            $this->logger = new NullLogger();
        }

        return $this->logger;
    }

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return \Wurfl\Storage\Storage
     */
    private function getPersistenceStorage()
    {
        return $this->persistenceStorage;
    }

    /**
     * @param \Wurfl\Storage\Storage $persistenceStorage
     */
    private function setPersistenceStorage(Storage\Storage $persistenceStorage)
    {
        $this->persistenceStorage = $persistenceStorage;
    }

    /**
     * @return \Wurfl\Handlers\Chain\UserAgentHandlerChain
     */
    private function getUserAgentHandlerChain()
    {
        if (null === $this->userAgentHandlerChain) {
            $this->userAgentHandlerChain = UserAgentHandlerChainFactory::createFrom(
                $this->getPersistenceStorage(),
                $this->getCacheStorage(),
                $this->getLogger()
            );
        }

        return $this->userAgentHandlerChain;
    }

    /**
     * @return \Wurfl\Configuration\Config
     */
    public function getWurflConfig()
    {
        return $this->wurflConfig;
    }

    /**
     * @param \Wurfl\Configuration\Config $wurflConfig
     */
    public function setWurflConfig(Configuration\Config $wurflConfig)
    {
        $this->wurflConfig = $wurflConfig;
    }

    /**
     * Reload the WURFL Data into the persistence provider
     */
    public function reload()
    {
        $this->getPersistenceStorage()->setWURFLLoaded(false);
        $this->remove();
        $this->invalidateCache();
        $this->init();
        $this->getPersistenceStorage()->save(self::WURFL_API_STATE, $this->getState());
    }

    /**
     * Returns true if the WURFL is out of date or otherwise needs to be reloaded
     *
     * @return bool
     */
    private function hasToBeReloaded()
    {
        if (!$this->getWurflConfig()->allowReload) {
            return false;
        }

        $state = $this->getPersistenceStorage()->load(self::WURFL_API_STATE);

        return !$this->isStateCurrent($state);
    }

    /**
     * Returns true if the current application state is the same as the given state
     *
     * @param string $state
     *
     * @return bool
     */
    private function isStateCurrent($state)
    {
        return (strcmp($this->getState(), $state) === 0);
    }

    /**
     * Generates a string specific to the loaded WURFL API and WURFL Data to be used for checking cache state.
     * If the API Version or the WURFL data file timestamp changes, the state string changes.
     *
     * @return string
     */
    private function getState()
    {
        $wurflMtime = filemtime($this->getWurflConfig()->wurflFile);

        return Constants::API_VERSION . '::' . $wurflMtime;
    }

    /**
     * Invalidates (clears) cache in the cache provider
     *
     * @see \Wurfl\Storage\Storage::clear()
     */
    private function invalidateCache()
    {
        $this->getCacheStorage()->clear();
    }

    /**
     * Clears the data in the persistence provider
     *
     * @see \Wurfl\Storage\Storage::clear()
     */
    public function remove()
    {
        $this->getPersistenceStorage()->clear();
    }

    /**
     * Initializes the WURFL Manager Factory by assigning cache and persistence providers
     */
    private function init()
    {
        $devicePatcher           = new Device\Xml\DevicePatcher();
        $deviceRepositoryBuilder = new Device\DeviceRepositoryBuilder(
            $this->getPersistenceStorage(),
            $this->getUserAgentHandlerChain(),
            $devicePatcher
        );

        $this->deviceRepository = $deviceRepositoryBuilder->build(
            $this->getWurflConfig()->wurflFile,
            $this->getWurflConfig()->wurflPatches,
            $this->getWurflConfig()->capabilityFilter
        );
    }

    /**
     * Return the version info of the loaded wurfl xml file
     * Example:
     * <code>
     * $info = $wurflManager->getWurflInfo();
     * printf('Version: %s, Updated: %s, OfficialURL: %s',
     *     $info->version,
     *     $info->lastUpdated,
     *     $info->officialURL
     * );
     * </code>
     *
     * @return \Wurfl\Device\Xml\Info WURFL Version info
     *
     * @see \Wurfl\DeviceRepository::getWurflInfo()
     */
    public function getWurflInfo()
    {
        return $this->getDeviceRepository()->getWurflInfo();
    }

    /**
     * Return a device for the given http request(user-agent..)
     *
     * @param array $httpRequest                    HTTP Request array (normally $_SERVER)
     * @param bool  $override_sideloaded_browser_ua
     *
     * @throws Exception if $httpRequest is not set
     *
     * @return \Wurfl\CustomDevice device
     */
    public function getDeviceForHttpRequest(array $httpRequest = array(), $override_sideloaded_browser_ua = true)
    {
        if (!isset($httpRequest)) {
            throw new Exception('The $httpRequest parameter must be set.');
        }

        $requestFactory = new Request\GenericRequestFactory();

        $request = $requestFactory->createRequest($httpRequest, $override_sideloaded_browser_ua);

        return $this->getDeviceForRequest($request);
    }

    /**
     * Returns the Device for the given \Wurfl\Request_GenericRequest
     *
     * @param Request\GenericRequest $request
     *
     * @return \Wurfl\CustomDevice
     */
    private function getDeviceForRequest(Request\GenericRequest $request)
    {
        Handlers\Utils::reset();

        $deviceId = $this->deviceIdForRequest($request);

        return $this->getWrappedDevice($deviceId, $request);
    }

    /**
     * Returns a device for the given user-agent
     *
     * @param string $userAgent
     *
     * @throws Exception if $userAgent is not set
     *
     * @return \Wurfl\CustomDevice device
     */
    public function getDeviceForUserAgent($userAgent = '')
    {
        if (!is_string($userAgent)) {
            $userAgent = '';
        }

        $requestFactory = new Request\GenericRequestFactory();

        $request = $requestFactory->createRequestForUserAgent($userAgent);
        $device  = $this->getDeviceForRequest($request);

        return $device;
    }

    /**
     * Return a device for the given device id
     *
     * @param string                 $deviceId
     * @param Request\GenericRequest $request
     *
     * @return \Wurfl\Device\ModelDeviceInterface
     * @throws \InvalidArgumentException
     */
    public function getDevice($deviceId, Request\GenericRequest $request = null)
    {
        if ($request !== null) {
            if (!($request instanceof GenericRequest)) {
                throw new \InvalidArgumentException(
                    'Error: Request parameter must be null or instance of WURFL_Request_GenericRequest'
                );
            }

            // Normalization must be performed if request is passed so virtual capabilities can be
            // resolved correctly.  This is normally handled in self::deviceIdForRequest()
            $generic_normalizer = UserAgentHandlerChainFactory::createGenericNormalizers();
            $request->setUserAgentNormalized($generic_normalizer->normalize($request->getUserAgent()));
        }

        return $this->getWrappedDevice($deviceId, $request);
    }

    /**
     * Returns an array of all wurfl group ids
     *
     * @return array
     */
    public function getListOfGroups()
    {
        return $this->getDeviceRepository()->getListOfGroups();
    }

    /**
     * Returns all capability names for the given $groupId
     *
     * @param string $groupId
     *
     * @return array
     */
    public function getCapabilitiesNameForGroup($groupId)
    {
        return $this->getDeviceRepository()->getCapabilitiesNameForGroup($groupId);
    }

    /**
     * Returns an array of all the fall back devices starting from
     * the given device
     *
     * @param string $deviceId
     *
     * @return array
     */
    public function getFallBackDevices($deviceId)
    {
        $fallBackDevices = $this->getDeviceRepository()->getDeviceHierarchy($deviceId);

        array_shift($fallBackDevices);

        return array_map(array($this, 'deviceId'), $fallBackDevices);
    }

    /**
     * @param \Wurfl\Device\ModelDeviceInterface $device
     *
     * @return string
     */
    private function deviceId(Device\ModelDeviceInterface $device)
    {
        return $device->id;
    }

    /**
     * Returns all the device ids in wurfl
     *
     * @return array
     */
    public function getAllDevicesID()
    {
        return $this->getDeviceRepository()->getAllDevicesID();
    }

    // ******************** private functions *****************************

    /**
     * Returns the device id for the device that matches the $request
     *
     * @param \Wurfl\Request\GenericRequest $request WURFL Request object
     *
     * @return string WURFL device id
     */
    private function deviceIdForRequest(Request\GenericRequest $request)
    {
        $id = $request->getId();

        if (!$id) {
            // $request->id is not set
            // -> do not try to get info from cache nor try to save to the cache
            $request->getMatchInfo()->fromCache  = 'invalid id';
            $request->getMatchInfo()->lookupTime = 0.0;

            return $this->getUserAgentHandlerChain()->match($request);
        }

        $deviceId = $this->getCacheStorage()->load($id);

        if (empty($deviceId)) {
            $genericNormalizer            = UserAgentHandlerChainFactory::createGenericNormalizers();
            $request->setUserAgentNormalized($genericNormalizer->normalize($request->getUserAgent()));

            if ($this->getWurflConfig()->isHighPerformance()
                && Handlers\Utils::isDesktopBrowserHeavyDutyAnalysis($request->getUserAgent())
            ) {
                // This device has been identified as a web browser programatically,
                // so no call to WURFL is necessary
                return WurflConstants::GENERIC_WEB_BROWSER;
            }

            $deviceId = $this->getUserAgentHandlerChain()->match($request);
            // save it in cache
            $this->getCacheStorage()->save($id, $deviceId);
        } else {
            $request->getMatchInfo()->fromCache  = true;
            $request->getMatchInfo()->lookupTime = 0.0;
        }

        return $deviceId;
    }

    /**
     * Wraps the model device with \Wurfl\Xml\ModelDeviceInterface. This function takes the
     * Device ID and returns the \Wurfl\CustomDevice with all capabilities.
     *
     * @param string                 $deviceId
     * @param Request\GenericRequest $request
     *
     * @return \Wurfl\CustomDevice
     */
    private function getWrappedDevice($deviceId, Request\GenericRequest $request = null)
    {
        $modelDevices = $this->getCacheStorage()->load('DEVS_' . $deviceId);

        if (empty($modelDevices)) {
            $modelDevices = $this->getDeviceRepository()->getDeviceHierarchy($deviceId);
        }

        $this->getCacheStorage()->save('DEVS_' . $deviceId, $modelDevices);

        if ($request === null) {
            // If a request was not provided, we generate one from the WURFL entry itself
            // to help resolve the virtual capabilities
            $requestFactory    = new GenericRequestFactory();
            $request           = $requestFactory->createRequestForUserAgent($modelDevices[0]->userAgent);
            $genericNormalizer = UserAgentHandlerChainFactory::createGenericNormalizers();

            $request->setUserAgentNormalized($genericNormalizer->normalize($request->getUserAgent()));
        }

        // The CustomDevice is not cached since virtual capabilities must be recalculated
        // for every different request.
        return new CustomDevice($modelDevices, $request);
    }
}
