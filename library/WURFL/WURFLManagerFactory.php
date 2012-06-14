<?php
namespace WURFL;

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

class WURFLManagerFactory
{
    const DEBUG = false;
    const WURFL_LAST_MODIFICATION_TIME = "WURFL_LAST_MODIFICATION_TIME";
    const WURFL_CURRENT_MODIFICATION_TIME = "WURFL_CURRENT_MODIFICATION_TIME";
    
    /**
     * WURFL Configuration
     * @var \WURFL\Configuration\Config
     */
    private $_wurflConfig;
    /**
     * @var \WURFL\WURFLManager
     */
    private $_wurflManager;
    /**
     * @var \WURFL\Storage\Base
     */
    private $_persistenceStorage;
    /**
     * @var \WURFL\Storage\Base
     */
    private $_cacheStorage;
    
    /**
     * Create a new WURFL Manager Factory
     * @param \WURFL\Configuration\Config $wurflConfig
     * @param \WURFL\Storage\Base $persistenceStorage
     * @param \WURFL\Storage\Base $cacheStorage
     */
    public function __construct(Configuration\Config $wurflConfig, $persistenceStorage = null, $cacheStorage = null)
    {
        $this->_wurflConfig = $wurflConfig;
        
        Configuration\ConfigHolder::setWURFLConfig($this->_wurflConfig);
        
        $this->_persistenceStorage = $persistenceStorage? $persistenceStorage: Storage\Factory::create($this->_wurflConfig->persistence);
        $this->_cacheStorage       = $cacheStorage? $cacheStorage: Storage\Factory::create($this->_wurflConfig->cache);
        
        if ($this->_persistenceStorage->validSecondaryCache($this->_cacheStorage)) {
            $this->_persistenceStorage->setCacheStorage($this->_cacheStorage);
        }
    }
    
    /**
     * Creates a new WURFLManager Object
     * @return WURFL_WURFLManager WURFL Manager object
     */
    public function create()
    {
        if (!isset($this->_wurflManager)) {
            $this->_init();
        }
        
        if ($this->hasToBeReloaded()) {
            $this->_reload();
        }
        
        return $this->_wurflManager;
    }
    
    /**
     * Initializes the WURFL Manager Factory by assigning cache and persistence providers
     */
    private function _init()
    {
        $logger  = null; //$this->logger($wurflConfig->logger);
        $context = new Context($this->_persistenceStorage, $this->_cacheStorage, $logger);
        
        $userAgentHandlerChain = UserAgentHandlerChainFactory::createFrom($context);
        $deviceRepository      = $this->_deviceRepository($this->_persistenceStorage, $userAgentHandlerChain);
        
        $wurflService   = new WURFLService($deviceRepository, $userAgentHandlerChain, $this->_cacheStorage);
        $requestFactory = new Request\GenericRequestFactory();
        
        $this->_wurflManager = new WURFLManager($wurflService, $requestFactory);
    }
    
    /**
     * Reload the WURFL Data into the persistence provider
     */
    private function _reload()
    {
        $this->_persistenceStorage->setWURFLLoaded(false);
        $this->_invalidateCache();
        $this->_init();
        $mtime = filemtime($this->_wurflConfig->wurflFile);
        $this->_persistenceStorage->save(self::WURFL_LAST_MODIFICATION_TIME, $mtime);
    }
    
    /**
     * Returns true if the WURFL is out of date or otherwise needs to be reloaded
     * @return bool
     */
    public function hasToBeReloaded()
    {
        if (!$this->_wurflConfig->allowReload) {
            return false;
        }
        $lastModificationTime = $this->_persistenceStorage->load(self::WURFL_LAST_MODIFICATION_TIME);
        $currentModificationTime = filemtime($this->_wurflConfig->wurflFile);
        return $currentModificationTime > $lastModificationTime;
    }
    
    /**
     * Invalidates (clears) cache in the cache provider
     * @see WURFL_Cache_CacheProvider::clear()
     */
    private function _invalidateCache()
    {
        $this->_cacheStorage->clear();
    }
    
    /**
     * Clears the data in the persistence provider
     * @see WURFL_Storage_Base::clear()
     */
    public function remove()
    {
        $this->_persistenceStorage->clear();
        $this->_wurflManager = null;
    }
    
    /**
     * Returns a WURFL device repository
     * @param WURFL_Storage_Base $persistenceStorage
     * @param WURFL_UserAgentHandlerChain $userAgentHandlerChain
     * @return WURFL_CustomDeviceRepository Device repository
     * @see WURFL_DeviceRepositoryBuilder::build()
     */
    private function _deviceRepository($persistenceStorage, $userAgentHandlerChain)
    {
        $devicePatcher           = new Xml\DevicePatcher();
        $deviceRepositoryBuilder = new DeviceRepositoryBuilder($persistenceStorage, $userAgentHandlerChain, $devicePatcher);
        //var_dump($this->_wurflConfig);exit;
        return $deviceRepositoryBuilder->build($this->_wurflConfig->wurflFile, $this->_wurflConfig->wurflPatches);
    }
}