<?php
declare(ENCODING = 'utf-8');
namespace Wurfl;

/**
 * Copyright(c) 2011 ScientiaMobile, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or(at your option) any later version.
 *
 * Refer to the COPYING file distributed with this package.
 *
 * @category   WURFL
 * @package    WURFL
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version   SVN: $Id$
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
 * @version   SVN: $Id$
 */

class WURFLManagerFactory
{
    
    const DEBUG = false;
    const WURFL_LAST_MODIFICATION_TIME = 'WURFL_LAST_MODIFICATION_TIME';
    
    /**
     * WURFL Configuration
     * @var Config
     */
    private $_wurflConfig;
    /**
     * @var WURFL_WURFLManager
     */
    private $_wurflManager;
    /**
     * @var WURFL_Xml_PersistenceProvider
     */
    private $_persistenceStorage;
    
    /**
     * Create a new WURFL Manager Factory
     * @param Config $wurflConfig
     * @param WURFL_Xml_PersistenceProvider $persistenceStorage
     */
    public function __construct(Configuration\Config $wurflConfig, $persistenceStorage=null)
    {
        $this->_wurflConfig = $wurflConfig;
        $this->_persistenceStorage = $persistenceStorage ? $persistenceStorage : self::_persistenceStorage($this->_wurflConfig->persistence);
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
            $this->reload();
        }
        
        return $this->_wurflManager;
    }
    
    /**
     * Reload the WURFL Data into the persistence provider
     */
    private function reload()
    {
        $this->_persistenceStorage->setWURFLLoaded(false);
        $this->_invalidateCache();
        $this->_init();
        $this->_persistenceStorage->save(self::WURFL_LAST_MODIFICATION_TIME, filemtime($this->_wurflConfig->wurflFile));
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
     * Invalidates(clears) cache in the cache provider
     * @see CacheProvider::clear()
     */
    private function _invalidateCache()
    {
        $cacheProvider = self::_cacheProvider($this->_wurflConfig->cache);
        $cacheProvider->clear();
    }
    
    /**
     * Clears the data in the persistence provider
     * @see WURFL_Xml_PersistenceProvider::clear()
     */
    public function remove()
    {
        $this->_persistenceStorage->clear();
        $this->_wurflManager = null;
    }
    
    /**
     * Initializes the WURFL Manager Factory by assigning cache and persistence providers
     */
    private function _init()
    {
        $cacheProvider = self::_cacheProvider($this->_wurflConfig->cache);
        $logger = null; //$this->logger($wurflConfig->logger);
        
        $context = new Context($this->_persistenceStorage);
        $context = $context->cacheProvider($cacheProvider)->logger($logger);
        
        $userAgentHandlerChain = UserAgentHandlerChainFactory::createFrom($context);
        $deviceRepository      = $this->_deviceRepository($this->_persistenceStorage, $userAgentHandlerChain);
        $wurflService          = new WURFLService($deviceRepository, $userAgentHandlerChain, $cacheProvider);
        
        $requestFactory = new Request\GenericRequestFactory();
        
        $this->_wurflManager = new WURFLManager($wurflService, $requestFactory);
    }
    
    /**
     * Returns a Persistance Storage object
     * @param array $persistenceConfig
     * @return WURFL_Storage_Base
     * @see WURFL_Storage_Factory::create()
     */
    private static function _persistenceStorage($persistenceConfig)
    {
        return Storage\Factory::create($persistenceConfig);
    }
    
    /**
     * Returns a Cache Storage object
     * @param array $cacheConfig
     * @return WURFL_Storage_Base
     * @see WURFL_Storage_Factory::create()
     */
    private static function _cacheProvider($cacheConfig)
    {
        return Storage\Factory::create($cacheConfig);
    }
    
    /**
     * Returns a WURFL device repository
     * @param $userAgentHandlerChain WURFL_UserAgentHandlerChain
     * @return WURFL_CustomDeviceRepository Device repository
     * @see WURFL_DeviceRepositoryBuilder::build()
     */
    private function _deviceRepository($persistenceStorage, $userAgentHandlerChain)
    {
        $devicePatcher = new Xml\DevicePatcher();
        $deviceRepositoryBuilder = new DeviceRepositoryBuilder($persistenceStorage, $userAgentHandlerChain, $devicePatcher);
        return $deviceRepositoryBuilder->build($this->_wurflConfig->wurflFile, $this->_wurflConfig->wurflPatches);
    }
}