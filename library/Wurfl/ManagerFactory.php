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

/**
 * This class is responsible for creating a WURFLManager instance
 * by instantiating and wiring together all the neccessary objects
 *
 * @category   WURFL
 * @package    WURFL
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */

class ManagerFactory
{
    const DEBUG           = false;
    const WURFL_API_STATE = 'WURFL_API_STATE';

    /**
     * WURFL Configuration
     *
     * @var \Wurfl\Configuration\Config
     */
    private $wurflConfig = null;

    /**
     * @var \Wurfl\Manager
     */
    private $wurflManager = null;

    /**
     * @var \Wurfl\Storage\Base
     */
    private $persistenceStorage = null;

    /**
     * @var \Wurfl\Storage\Base
     */
    private $cacheStorage = null;

    /** @var \Psr\Log\LoggerInterface */
    private $logger = null;

    /**
     * Create a new Wurfl Manager Factory
     *
     * @param \Wurfl\Configuration\Config $wurflConfig
     * @param \Wurfl\Storage\Base         $persistenceStorage
     * @param \Wurfl\Storage\Base         $cacheStorage
     * @param \Psr\Log\LoggerInterface    $logger
     */
    public function __construct(
        Configuration\Config $wurflConfig,
        Storage\Base $persistenceStorage = null,
        Storage\Base $cacheStorage = null,
        LoggerInterface $logger = null)
    {
        $this->wurflConfig = $wurflConfig;

        if (null === $persistenceStorage) {
            $persistenceStorage = Storage\Factory::create($this->wurflConfig->persistence);
        }
        $this->persistenceStorage = $persistenceStorage;

        if (null === $cacheStorage) {
            $cacheStorage = Storage\Factory::create($this->wurflConfig->cache);
        }
        $this->cacheStorage = $cacheStorage;

        if ($this->persistenceStorage->validSecondaryCache($this->cacheStorage)) {
            $this->persistenceStorage->setCacheStorage($this->cacheStorage);
        }

        if (!($logger instanceof LoggerInterface)) {
            $logger = Logger\LoggerFactory::create($wurflConfig);
        }

        $this->logger = $logger;
    }

    /**
     * Creates a new WURFLManager Object
     *
     * @return \Wurfl\Manager WURFL Manager object
     */
    public function create()
    {
        if (null === $this->wurflManager) {
            $this->init();
        }

        return $this->wurflManager;
    }

    /**
     * Clears the data in the persistence provider
     *
     * @see \Wurfl\Storage\Base::clear()
     */
    public function remove()
    {
        $this->persistenceStorage->clear();
        $this->wurflManager = null;
    }

    /**
     * Initializes the WURFL Manager Factory by assigning cache and persistence providers
     */
    private function init()
    {
        $context = new Context($this->persistenceStorage, $this->cacheStorage, $this->logger);

        $userAgentHandlerChain = UserAgentHandlerChainFactory::createFrom($context);

        $deviceRepository   = $this->deviceRepository($this->persistenceStorage, $userAgentHandlerChain);
        $this->wurflManager = new Manager($deviceRepository, $userAgentHandlerChain, $this->cacheStorage);
    }

    /**
     * Returns a Wurfl device repository
     *
     * @param \Wurfl\Storage\Base          $persistenceStorage
     * @param \Wurfl\UserAgentHandlerChain $userAgentHandlerChain
     *
     * @return \Wurfl\CustomDeviceRepository Device repository
     * @see \Wurfl\DeviceRepositoryBuilder::build()
     */
    private function deviceRepository($persistenceStorage, $userAgentHandlerChain)
    {
        $devicePatcher           = new Xml\DevicePatcher();
        $deviceRepositoryBuilder = new DeviceRepositoryBuilder(
            $persistenceStorage, $userAgentHandlerChain, $devicePatcher
        );

        $patches = $this->wurflConfig->wurflPatches;

        if (!is_array($patches)) {
            $patches = array();
        }

        return $deviceRepositoryBuilder->build(
            $this->wurflConfig->wurflFile,
            $patches,
            $this->wurflConfig->capabilityFilter
        );
    }
}