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

namespace Wurfl;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use WurflCache\Adapter\AdapterInterface;

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
 * @package WURFL
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
     * @var \Wurfl\DeviceRepository
     */
    private $deviceRepository = null;

    /**
     * @var \Wurfl\UserAgentHandlerChain
     */
    private $userAgentHandlerChain = null;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger = null;

    /**
     * Creates a new Wurfl Manager object
     *
     * @param Configuration\Config                 $wurflConfig
     * @param \WurflCache\Adapter\AdapterInterface $persistenceStorage
     * @param \WurflCache\Adapter\AdapterInterface $cacheStorage
     */
    public function __construct(
        Configuration\Config $wurflConfig,
        AdapterInterface $persistenceStorage = null,
        AdapterInterface $cacheStorage = null
    ) {
        $this->setWurflConfig($wurflConfig);

        if (null === $persistenceStorage) {
            $persistenceStorage = Storage\Factory::create($this->getWurflConfig()->persistence);
        }

        if (null === $cacheStorage) {
            $cacheStorage = Storage\Factory::create($this->getWurflConfig()->cache);
        }

        $this->setPersistenceStorage(new Storage\Storage($persistenceStorage));
        $this->setCacheStorage(new Storage\Storage($cacheStorage));

        if ($this->getPersistenceStorage()->validSecondaryCache($this->getCacheStorage())) {
            $this->getPersistenceStorage()->setCacheStorage($this->getCacheStorage());
        }

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
     * @return DeviceRepository
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
     * @return UserAgentHandlerChain
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
     * @return boolean
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
        $devicePatcher           = new Xml\DevicePatcher();
        $deviceRepositoryBuilder = new DeviceRepositoryBuilder(
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
     * @return \Wurfl\Xml\Info WURFL Version info
     * @see \Wurfl\DeviceRepository::getWurflInfo()
     */
    public function getWurflInfo()
    {
        return $this->getDeviceRepository()->getWurflInfo();
    }

    /**
     * Return a device for the given http request(user-agent..)
     *
     * @param array $httpRequest HTTP Request array (normally $_SERVER)
     * @param bool  $override_sideloaded_browser_ua
     *
     * @return \Wurfl\CustomDevice device
     * @throws Exception if $httpRequest is not set
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

        if ($this->getWurflConfig()->isHighPerformance()
            && Handlers\Utils::isDesktopBrowserHeavyDutyAnalysis($request->userAgent)
        ) {
            // This device has been identified as a web browser programatically,
            // so no call to WURFL is necessary
            $deviceId = Constants::GENERIC_WEB_BROWSER;
        } else {
            $deviceId = $this->deviceIdForRequest($request);
        }

        return $this->getWrappedDevice($deviceId, $request);
    }

    /**
     * Returns a device for the given user-agent
     *
     * @param string $userAgent
     *
     * @return \Wurfl\CustomDevice device
     * @throws Exception if $userAgent is not set
     */
    public function getDeviceForUserAgent($userAgent = '')
    {
        if (!is_string($userAgent)) {
            $userAgent = '';
        }

        $requestFactory = new Request\GenericRequestFactory();

        $request = $requestFactory->createRequestForUserAgent($userAgent);
        $device  = $this->getDeviceForRequest($request);

        $device->request->userAgent = $userAgent;

        return $device;
    }

    /**
     * Return a device for the given device id
     *
     * @param string                 $deviceId
     * @param Request\GenericRequest $request
     *
     * @return \Wurfl\Xml\ModelDevice
     */
    public function getDevice($deviceId, Request\GenericRequest $request = null)
    {
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
        return $this->getDeviceRepository()->getDeviceHierarchy($deviceId);
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
        $id = $request->id;

        if (!$id) {
            // $request->id is not set
            // -> do not try to get info from cache nor try to save to the cache
            $request->matchInfo->fromCache  = 'invalid id';
            $request->matchInfo->lookupTime = 0.0;

            return $this->getUserAgentHandlerChain()->match($request);
        }

        $deviceId = $this->getCacheStorage()->load($id);

        if (empty($deviceId)) {
            $deviceId = $this->getUserAgentHandlerChain()->match($request);
            // save it in cache
            $this->getCacheStorage()->save($id, $deviceId);
        } else {
            $request->matchInfo->fromCache  = true;
            $request->matchInfo->lookupTime = 0.0;
        }

        return $deviceId;
    }

    /**
     * Wraps the model device with \Wurfl\Xml_ModelDevice.  This function takes the
     * Device ID and returns the \Wurfl\CustomDevice with all capabilities.
     *
     * @param string                 $deviceId
     * @param Request\GenericRequest $request
     *
     * @return \Wurfl\CustomDevice
     */
    private function getWrappedDevice($deviceId, Request\GenericRequest $request = null)
    {
        $device = $this->getCacheStorage()->load('DEV_' . $deviceId);

        if (empty($device)) {
            $modelDevices = $this->getDeviceRepository()->getDeviceHierarchy($deviceId);
            $device       = new CustomDevice($modelDevices, $request);
            $this->getCacheStorage()->save('DEV_' . $deviceId, $device);
        }

        return $device;
    }
}
