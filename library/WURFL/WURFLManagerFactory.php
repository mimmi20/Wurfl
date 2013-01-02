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
 * @category   WURFL
 * @package	WURFL
 * @copyright  ScientiaMobile, Inc.
 * @license	GNU Affero General Public License
 * @version	$id$
 */

/**
 * This class is responsible for creating a WURFLManager instance
 * by instantiating and wiring together all the neccessary objects
 * 
 *
 * @category   WURFL
 * @package	WURFL
 * @copyright  ScientiaMobile, Inc.
 * @license	GNU Affero General Public License
 * @version	$id$
 */

class WURFL_WURFLManagerFactory {
	
	const DEBUG = false;
	const WURFL_LAST_MODIFICATION_TIME = "WURFL_LAST_MODIFICATION_TIME";
	const WURFL_CURRENT_MODIFICATION_TIME = "WURFL_CURRENT_MODIFICATION_TIME";
	
	/**
	 * WURFL Configuration
	 * @var WURFL_Configuration_Config
	 */
	private $wurflConfig;
	/**
	 * @var WURFL_WURFLManager
	 */
	private $wurflManager;
	/**
	 * @var WURFL_Storage_Base
	 */
	private $persistenceStorage;
	/**
	 * @var WURFL_Storage_Base
	 */
	private $cacheStorage;
	
	/**
	 * Create a new WURFL Manager Factory
	 * @param WURFL_Configuration_Config $wurflConfig
	 * @param WURFL_Storage_Base $persistenceStorage
	 * @param WURFL_Storage_Base $cacheStorage
	 */
	public function __construct(WURFL_Configuration_Config $wurflConfig, $persistenceStorage=null, $cacheStorage=null) {
		$this->wurflConfig = $wurflConfig;
		WURFL_Configuration_ConfigHolder::setWURFLConfig($this->wurflConfig);
		$this->persistenceStorage = $persistenceStorage? $persistenceStorage: WURFL_Storage_Factory::create($this->wurflConfig->persistence);
		$this->cacheStorage = $cacheStorage? $cacheStorage: WURFL_Storage_Factory::create($this->wurflConfig->cache);
		if ($this->persistenceStorage->validSecondaryCache($this->cacheStorage)) {
			$this->persistenceStorage->setCacheStorage($this->cacheStorage);
		}
	}
	
	/**
	 * Creates a new WURFLManager Object
	 * @return WURFL_WURFLManager WURFL Manager object
	 */
	public function create() {
		if (!isset($this->wurflManager)) {
			$this->init();
		}
		if ($this->hasToBeReloaded()) {
			$this->reload();
		}
		
		return $this->wurflManager;
	}
	
	/**
	 * Reload the WURFL Data into the persistence provider
	 */
	private function reload() {
		$this->persistenceStorage->setWURFLLoaded(false);
		$this->invalidateCache();
		$this->init();
		$mtime = filemtime($this->wurflConfig->wurflFile);
		$this->persistenceStorage->save(self::WURFL_LAST_MODIFICATION_TIME, $mtime);
	}
	
	/**
	 * Returns true if the WURFL is out of date or otherwise needs to be reloaded
	 * @return bool
	 */
	public function hasToBeReloaded() {
		if (!$this->wurflConfig->allowReload) {
			return false;
		}
		$lastModificationTime = $this->persistenceStorage->load(self::WURFL_LAST_MODIFICATION_TIME);
		$currentModificationTime = filemtime($this->wurflConfig->wurflFile);
		return $currentModificationTime > $lastModificationTime;
	}
	
	/**
	 * Invalidates (clears) cache in the cache provider
	 * @see WURFL_Cache_CacheProvider::clear()
	 */
	private function invalidateCache() {
		$this->cacheStorage->clear();
	}
	
	/**
	 * Clears the data in the persistence provider
	 * @see WURFL_Storage_Base::clear()
	 */
	public function remove() {
		$this->persistenceStorage->clear();
		$this->wurflManager = null;
	}
	
	/**
	 * Initializes the WURFL Manager Factory by assigning cache and persistence providers
	 */
	private function init() {
		$logger = null; //$this->logger($wurflConfig->logger);
		$context = new WURFL_Context($this->persistenceStorage, $this->cacheStorage, $logger);
		$userAgentHandlerChain = WURFL_UserAgentHandlerChainFactory::createFrom($context);
		$deviceRepository = $this->deviceRepository($this->persistenceStorage, $userAgentHandlerChain);
		$wurflService = new WURFL_WURFLService($deviceRepository, $userAgentHandlerChain, $this->cacheStorage);
		$requestFactory = new WURFL_Request_GenericRequestFactory();
		$this->wurflManager = new WURFL_WURFLManager($wurflService, $requestFactory);
	}
	
	/**
	 * Returns a WURFL device repository
	 * @param WURFL_Storage_Base $persistenceStorage
	 * @param WURFL_UserAgentHandlerChain $userAgentHandlerChain
	 * @return WURFL_CustomDeviceRepository Device repository
	 * @see WURFL_DeviceRepositoryBuilder::build()
	 */
	private function deviceRepository($persistenceStorage, $userAgentHandlerChain) {
		$devicePatcher = new WURFL_Xml_DevicePatcher();
		$deviceRepositoryBuilder = new WURFL_DeviceRepositoryBuilder($persistenceStorage, $userAgentHandlerChain, $devicePatcher);
		return $deviceRepositoryBuilder->build($this->wurflConfig->wurflFile, $this->wurflConfig->wurflPatches);
	}
}