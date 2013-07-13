<?php
namespace Wurfl;

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
 * @category   WURFL
 * @package    WURFL
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */

/**
 * This class is responsible for creating a WURFLManager instance
 * by instantiating and wiring together all the neccessary objects
 * 
 *
 * @category   WURFL
 * @package    WURFL
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */

class ManagerFactory
{
    const DEBUG = false;
    const WURFL_LAST_MODIFICATION_TIME = 'Wurfl_LAST_MODIFICATION_TIME';
    const WURFL_CURRENT_MODIFICATION_TIME = 'Wurfl_CURRENT_MODIFICATION_TIME';
    
    /**
     * WURFL Configuration
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
    
    /**
     * Create a new WURFL Manager Factory
     * @param \Wurfl\Configuration\Config $wurflConfig
     * @param \Wurfl\Storage\Base $persistenceStorage
     * @param \Wurfl\Storage\Base $cacheStorage
     */
    public function __construct(
        Configuration\Config $wurflConfig, 
        Storage\Base $persistenceStorage = null, 
        Storage\Base $cacheStorage = null)
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
    }
    
    /**
     * Creates a new WURFLManager Object
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
     * Initializes the WURFL Manager Factory by assigning cache and persistence providers
     */
    private function init()
    {
        $logger  = null; //$this->logger($wurflConfig->logger);
        $context = new Context($this->persistenceStorage, $this->cacheStorage, $logger);
        
        $userAgentHandlerChain = UserAgentHandlerChainFactory::createFrom($context);
        
        $deviceRepository   = $this->deviceRepository($this->persistenceStorage, $userAgentHandlerChain);
        $wurflService       = new Service($deviceRepository, $userAgentHandlerChain, $this->cacheStorage);
        $this->wurflManager = new Manager($wurflService);
    }
    
    /**
     * Clears the data in the persistence provider
     * @see \Wurfl\Storage\Base::clear()
     */
    public function remove()
    {
        $this->persistenceStorage->clear();
        $this->wurflManager = null;
    }
    
    /**
     * Returns a Wurfl device repository
     *
     * @param \Wurfl\Storage\Base $persistenceStorage
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
        
        return $deviceRepositoryBuilder->build($this->wurflConfig->wurflFile, $patches);
    }
}