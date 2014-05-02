<?php
namespace Wurfl;

/**
 * Copyright (c) 2012 ScientiaMobile, Inc.
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 * Refer to the COPYING.txt file distributed with this package.
 *
 * @category   WURFL
 * @package    WURFL
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
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
     * @var \WurflCache\Adapter\AdapterInterface
     */
    private $persistenceStorage = null;
    /**
     * @var \WurflCache\Adapter\AdapterInterface
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
        $this->wurflConfig = $wurflConfig;

        if (null === $persistenceStorage) {
            $persistenceStorage = Storage\Factory::create($this->wurflConfig->persistence);
        }

        if (null === $cacheStorage) {
            $cacheStorage = Storage\Factory::create($this->wurflConfig->cache);
        }

        $this->persistenceStorage = new Storage\Storage($persistenceStorage);
        $this->cacheStorage       = new Storage\Storage($cacheStorage);

        if ($this->persistenceStorage->validSecondaryCache($this->cacheStorage)) {
            $this->persistenceStorage->setCacheStorage($this->cacheStorage);
        }

        if ($this->hasToBeReloaded()) {
            $this->reload();
        } else {
            $this->init();
        }
    }

    /**
     * Reload the WURFL Data into the persistence provider
     */
    private function reload()
    {
        $this->persistenceStorage->setWURFLLoaded(false);
        $this->remove();
        $this->invalidateCache();
        $this->init();
        $this->persistenceStorage->save(self::WURFL_API_STATE, $this->getState());
    }

    /**
     * Returns true if the WURFL is out of date or otherwise needs to be reloaded
     *
     * @return bool
     */
    public function hasToBeReloaded()
    {
        if (!$this->wurflConfig->allowReload) {
            return false;
        }

        $state = $this->persistenceStorage->load(self::WURFL_API_STATE);

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
        $wurflMtime = filemtime($this->wurflConfig->wurflFile);
        return Constants::API_VERSION . '::' . $wurflMtime;
    }

    /**
     * Invalidates (clears) cache in the cache provider
     *
     * @see \Wurfl\Storage\Storage::clear()
     */
    private function invalidateCache()
    {
        $this->cacheStorage->clear();
    }

    /**
     * Clears the data in the persistence provider
     *
     * @see \Wurfl\Storage\Storage::clear()
     */
    public function remove()
    {
        $this->persistenceStorage->clear();
    }

    /**
     * Initializes the WURFL Manager Factory by assigning cache and persistence providers
     */
    private function init()
    {
        $logger  = new \WurflCache\Adapter\NullStorage();
        $context = new Context($this->persistenceStorage, $this->cacheStorage, $logger);

        $this->userAgentHandlerChain = UserAgentHandlerChainFactory::createFrom(
            $context,
            $this->persistenceStorage,
            $this->cacheStorage,
            $logger
        );

        $devicePatcher           = new Xml\DevicePatcher();
        $deviceRepositoryBuilder = new DeviceRepositoryBuilder(
            $this->persistenceStorage,
            $this->userAgentHandlerChain,
            $devicePatcher
        );

        $this->deviceRepository = $deviceRepositoryBuilder->build(
            $this->wurflConfig->wurflFile,
            $this->wurflConfig->wurflPatches,
            $this->wurflConfig->capabilityFilter
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
        return $this->deviceRepository->getWurflInfo();
    }

    /**
     * Return a device for the given http request(user-agent..)
     *
     * @param array $httpRequest HTTP Request array (normally $_SERVER)
     *
     * @return \Wurfl\CustomDevice device
     * @throws Exception if $httpRequest is not set
     */
    public function getDeviceForHttpRequest(array $httpRequest = array())
    {
        if (!isset($httpRequest)) {
            throw new Exception('The $httpRequest parameter must be set.');
        }

        $requestFactory = new Request\GenericRequestFactory();

        $request = $requestFactory->createRequest($httpRequest);

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

        if ($this->wurflConfig->isHighPerformance()
            && Handlers\Utils::isDesktopBrowserHeavyDutyAnalysis($request->userAgent)
        ) {
            // This device has been identified as a web browser programatically, so no call to WURFL is necessary
            $device = $this->getWrappedDevice(Constants::GENERIC_WEB_BROWSER, $request);
        } else {
            $deviceId = $this->deviceIdForRequest($request);

            $device = $this->getWrappedDevice($deviceId, $request);
        }

        return $device;
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
        if (!isset($userAgent)) {
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
        return $this->deviceRepository->getListOfGroups();
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
        return $this->deviceRepository->getCapabilitiesNameForGroup($groupId);
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
        return $this->deviceRepository->getDeviceHierarchy($deviceId);
    }

    /**
     * Returns all the device ids in wurfl
     *
     * @return array
     */
    public function getAllDevicesID()
    {
        return $this->deviceRepository->getAllDevicesID();
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
            $request->matchInfo->from_cache  = 'invalid id';
            $request->matchInfo->lookup_time = 0.0;

            return $this->userAgentHandlerChain->match($request);
        }

        $deviceId = $this->cacheStorage->load($id);

        if (empty($deviceId)) {
            $deviceId = $this->userAgentHandlerChain->match($request);
            // save it in cache
            $this->cacheStorage->save($id, $deviceId);
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
        $device = $this->cacheStorage->load('DEV_' . $deviceId);

        if (empty($device)) {
            $modelDevices = $this->deviceRepository->getDeviceHierarchy($deviceId);
            $device       = new CustomDevice($modelDevices, $request);
            $this->cacheStorage->save('DEV_' . $deviceId, $device);
        }

        return $device;
    }
}
