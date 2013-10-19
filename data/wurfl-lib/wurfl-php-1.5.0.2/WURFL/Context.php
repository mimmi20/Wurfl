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
 * WURFL Context stores the persistence provider, cache provider and logger objects
 * @package	WURFL
 * 
 * @property-read WURFL_Storage_Base $persistenceProvider
 * @property-read WURFL_Storage_Base $cacheProvider
 * @property-read WURFL_Logger_Interface $logger
 */
class WURFL_Context {
	
	/**
	 * @var WURFL_Storage_Base
	 */
	private $_persistenceProvider;
	/**
	 * @var WURFL_Storage_Base
	 */
	private $_cacheProvider;
	/**
	 * @var WURFL_Logger_Interface
	 */
	private $_logger;
	
	public function __construct($persistenceProvider, $cacheProvider = null, $logger = null) {
		$this->_persistenceProvider = $persistenceProvider;
		$this->_cacheProvider = is_null($cacheProvider)? new WURFL_Storage_Null(): $cacheProvider;
		$this->_logger = is_null($logger)? new WURFL_Logger_NullLogger(): $logger;
	}
	
	public function cacheProvider($cacheProvider) {
		$this->_cacheProvider = is_null($cacheProvider)? new WURFL_Storage_Null(): $cacheProvider;
		return $this;
	}
	
	public function logger($logger) {
		$this->_logger = is_null($logger)? new WURFL_Logger_NullLogger(): $logger;
		return $this;
	}
	
	public function __get($name) {
		$name = '_'.$name;
		return $this->$name;
	}
}