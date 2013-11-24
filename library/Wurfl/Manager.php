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
use Psr\Log\LoggerInterface;
use Wurfl\Configuration\Config;
use Wurfl\Request\GenericRequest;
use Wurfl\Storage\StorageInterface;

/**
 * WURFL Manager Class - serves as the core class that the developer uses to query
 * the API for device capabilities and WURFL information
 * Examples:
 * <code>
 * // Example 1. Instantiate Manager:
 * $wurflManager = new $wurflManager();
 * // Example 2: Get Visiting Device from HTTP Request
 * $device = $wurflManager->getDeviceForHttpRequest($_SERVER);
 * // Example 3: Get Visiting Device from User Agent
 * $userAgent = 'Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.10';
 * $device = $wurflManager->getDeviceForUserAgent($userAgent);
 * </code>
 *
 * @package WURFL
 * @see     getWurflInfo(), getDeviceForHttpRequest(), getDeviceForUserAgent(), \Wurfl\WURFLManagerFactory::create()
 */
class Manager
{
    /**
     * @var DeviceRepository
     */
    private $deviceRepository = null;

    /**
     * @var StorageInterface
     */
    private $cacheStorage = null;

    /**
     * @var StorageInterface
     */
    private $persistenceStorage = null;

    /**
     * WURFL Configuration
     *
     * @var Config
     */
    private $wurflConfig = null;

    /**
     * WURFL Configuration
     *
     * @var LoggerInterface
     */
    private $logger = null;

    /**
     * Creates a new Wurfl Manager object
     *
     * @param Config                   $wurflConfig
     * @param Storage\StorageInterface $persistenceStorage
     * @param Storage\StorageInterface $cacheStorage
     */
    public function __construct(
        Config $wurflConfig = null,
        Storage\StorageInterface $persistenceStorage = null,
        Storage\StorageInterface $cacheStorage = null
    ) {
        if (null !== $wurflConfig) {
            $this->setWurflConfig($wurflConfig);
        }

        if (null !== $persistenceStorage) {
            $this->setPersistenceStorage($persistenceStorage);
        }

        if (null !== $cacheStorage) {
            $this->setcacheStorage($cacheStorage);
        }
    }

    /**
     * @param Config $wurflConfig
     *
     * @return Manager
     */
    public function setWurflConfig(Config $wurflConfig)
    {
        $this->wurflConfig = $wurflConfig;

        return $this;
    }

    /**
     * @param StorageInterface $cacheStorage
     *
     * @return Manager
     */
    public function setcacheStorage(StorageInterface $cacheStorage)
    {
        $this->cacheStorage = $cacheStorage;

        return $this;
    }

    /**
     * @param StorageInterface $persistenceStorage
     *
     * @return Manager
     */
    public function setPersistenceStorage(StorageInterface $persistenceStorage)
    {
        $this->persistenceStorage = $persistenceStorage;

        return $this;
    }

    /**
     * @param LoggerInterface $logger
     *
     * @return Manager
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
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
     * @return Xml\Info WURFL Version info
     * @see DeviceRepository::getWurflInfo()
     */
    public function getWurflInfo()
    {
        return $this->buildRepository()->getWurflInfo();
    }

    /**
     * Return a device for the given http request(user-agent..)
     *
     * @param array $httpRequest HTTP Request array (normally $_SERVER)
     *
     * @return CustomDevice device
     * @throws Exception if $httpRequest is not set
     */
    public function getDeviceForHttpRequest(array $httpRequest = array())
    {
        if (!isset($httpRequest)) {
            throw new Exception('The $httpRequest parameter must be set.');
        }

        /** @var $request Request\GenericRequest */
        $request = Request\GenericRequestFactory::createRequest($httpRequest);

        return $this->getDeviceForRequest($request);
    }

    /**
     * Returns a device for the given user-agent
     *
     * @param string $userAgent
     *
     * @return CustomDevice device
     * @throws Exception if $userAgent is not set
     */
    public function getDeviceForUserAgent($userAgent = '')
    {
        if (!isset($userAgent)) {
            throw new Exception('The $httpRequest parameter must be set.');
        }

        /** @var $request Request\GenericRequest */
        $request = Request\GenericRequestFactory::createRequestForUserAgent($userAgent);

        return $this->getDeviceForRequest($request);
    }

    /**
     * Wraps the model device with \Wurfl\Xml\ModelDevice.  This function takes the
     * Device ID and returns the \Wurfl\CustomDevice with all capabilities.
     *
     * @param string         $deviceId
     * @param GenericRequest $request
     *
     * @return CustomDevice
     *
     * @param string         $deviceId
     * @param GenericRequest $request
     *
     * @return CustomDevice
     */
    public function getDevice($deviceId, GenericRequest $request = null)
    {
        $cache = $this->buildCacheStorage();

        /** @var $device CustomDevice */
        $device = $cache->load('DEV_' . $deviceId);

        if (empty($device)) {
            /** @var $modelDevices array */
            $modelDevices = $this->buildRepository()->getDeviceHierarchy($deviceId);
            $device       = new CustomDevice($modelDevices, $request);

            $cache->save('DEV_' . $deviceId, $device);
        }

        return $device;
    }

    /**
     * Returns an array of all wurfl group ids
     *
     * @return array
     */
    public function getListOfGroups()
    {
        return $this->buildRepository()->getListOfGroups();
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
        return $this->buildRepository()->getCapabilitiesNameForGroup($groupId);
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
        return $this->buildRepository()->getDeviceHierarchy($deviceId);
    }

    /**
     * Returns all the device ids in wurfl
     *
     * @return array
     */
    public function getAllDevicesID()
    {
        return $this->buildRepository()->getAllDevicesID();
    }

    // ******************** private functions *****************************

    /**
     * Returns the Device for the given \Wurfl\Request_GenericRequest
     *
     * @param GenericRequest $request
     *
     * @throws Exception
     * @return CustomDevice
     */
    private function getDeviceForRequest(GenericRequest $request)
    {
        if (!isset($request)) {
            throw new Exception('The request parameter must be set.');
        }

        Handlers\Utils::reset();

        if (null !== $this->wurflConfig
            && $this->wurflConfig->isHighPerformance()
            && Handlers\Utils::isDesktopBrowserHeavyDutyAnalysis($request->userAgent)
        ) {
            // This device has been identified as a web browser programatically,
            // so no call to WURFL is necessary
            return $this->getDevice(Constants::GENERIC_WEB_BROWSER, $request);
        }

        $deviceId = $this->deviceIdForRequest($request);

        return $this->getDevice($deviceId, $request);
    }

    /**
     * Returns the device id for the device that matches the $request
     *
     * @param GenericRequest $request WURFL Request object
     *
     * @return string WURFL device id
     */
    private function deviceIdForRequest(GenericRequest $request)
    {
        $cache    = $this->buildCacheStorage();
        $deviceId = $cache->load($request->id);

        if (empty($deviceId)) {
            /** @var $userAgentHandlerChain Chain\UserAgentHandlerChain */
            $userAgentHandlerChain = $this->buildChain();
            $deviceId              = $userAgentHandlerChain->match($request);

            // save it in cache
            $cache->save($request->id, $deviceId);
        } else {
            $request->matchInfo->from_cache  = true;
            $request->matchInfo->lookup_time = 0.0;
        }

        return $deviceId;
    }

    /**
     * Returns a Wurfl device repository
     *
     * @return DeviceRepository
     */
    private function buildRepository()
    {
        if (null !== $this->deviceRepository) {
            return $this->deviceRepository;
        }

        $devicePatcher           = new Xml\DevicePatcher();
        $deviceRepositoryBuilder = new DeviceRepositoryBuilder(
            $this->buildPersistenceStorage(), $this->buildChain(), $devicePatcher
        );

        $patches = $this->wurflConfig->wurflPatches;

        if (!is_array($patches)) {
            $patches = array();
        }

        $this->deviceRepository = $deviceRepositoryBuilder->build(
            $this->wurflConfig->wurflFile,
            $patches,
            $this->wurflConfig->capabilityFilter
        );

        return $this->deviceRepository;
    }

    /**
     * @return Chain\UserAgentHandlerChain
     */
    private function buildChain()
    {
        $context = new Context($this->buildPersistenceStorage(), $this->buildCacheStorage(), $this->buildLogger());

        return Chain\UserAgentHandlerChainFactory::createFrom($context);
    }

    /**
     * @return StorageInterface
     */
    private function buildCacheStorage()
    {
        if (null !== $this->cacheStorage) {
            return $this->cacheStorage;
        }

        $this->cacheStorage = Storage\Factory::create($this->wurflConfig->cache);

        return $this->cacheStorage;
    }

    /**
     * @return StorageInterface
     */
    private function buildPersistenceStorage()
    {
        if (null !== $this->persistenceStorage) {
            return $this->persistenceStorage;
        }

        $this->persistenceStorage = Storage\Factory::create($this->wurflConfig->persistence);

        return $this->persistenceStorage;
    }

    /**
     * @return LoggerInterface
     */
    private function buildLogger()
    {
        if (null !== $this->logger) {
            return $this->logger;
        }

        $this->logger = Logger\LoggerFactory::create($this->wurflConfig->logger);

        return $this->logger;
    }
}