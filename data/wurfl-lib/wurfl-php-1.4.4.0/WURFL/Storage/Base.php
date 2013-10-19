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
 * @package	WURFL_Storage
 * @copyright  ScientiaMobile, Inc.
 * @license	GNU Affero General Public License
 * @version	$id$
 */

/**
 * Base Storage Provider
 *
 * A Skeleton implementation of the Storage Interface
 *
 * @category   WURFL
 * @package	WURFL_Storage
 * @copyright  ScientiaMobile, Inc.
 * @license	GNU Affero General Public License
 * @author	 Fantayeneh Asres Gizaw
 * @version	$id$
 */
abstract class WURFL_Storage_Base implements WURFL_Storage {

	const APPLICATION_PREFIX = "WURFL_";
	const WURFL_LOADED = "WURFL_WURFL_LOADED";

	/**
	 * @var WURFL_Storage_Base
	 */
	private $cache;
	
	protected $is_volatile = false;
	protected $supports_secondary_caching = false;

	/**
	 * Creates a new WURFL_Storage_Base
	 * @param array $params
	 */
	public function __construct($params = array()) {}

	/**
	 * Saves the object
	 * @param string $objectId
	 * @param mixed $object
	 * @param integer $expiration If supported by the provider, this is used to specify the expiration
	 */
	public function save($objectId, $object, $expiration=null) {}

	/**
	 * Returns the object identified by $objectId
	 * @param string $objectId
	 * @return mixed value
	 */
	public function load($objectId) {}


	/**
	 * Removes the object identified by $objectId from the persistence provider
	 * @param string $objectId
	 */
	public function remove($objectId) {}


	/**
	 * Removes all entries from the Persistence Provider
	 */
	public function clear() {}

	/**
	 * Returns true if the cache is an in-memory volatile cache, like Memcache or APC, or false if
	 * it is a persistent cache like Filesystem or MySQL
	 * @return boolean
	 */
	public function isVolatile() {
		return $this->is_volatile;
	}
	
	/**
	 * This storage provider supports a caching layer in front of it, for example, the File provider 
	 * supports a volatile cache like Memcache in front of it, whereas APC does not.
	 * @return boolean
	 */
	public function supportsSecondaryCaching() {
		return $this->supports_secondary_caching;
	}
	
	/**
	 * This storage provider can be used as a secondary cache
	 * @param WURFL_Storage_Base $cache
	 * @return boolean
	 */
	public function validSecondaryCache(WURFL_Storage_Base $cache) {
		/**
		 * True if $this supports secondary caching and the cache provider is not the 
		 * same class type since this would always decrease performance
		 */
		return ($this->supports_secondary_caching && get_class($this) != get_class($cache));
	}
	
	/**
	 * Sets the cache provider for the persistence provider; this is used to 
	 * cache data in a volatile storage system like APC in front of a slow 
	 * persistence provider like the filesystem.
	 * 
	 * @param WURFL_Storage_Base $cache
	 */
	public function setCacheStorage(WURFL_Storage_Base $cache) {
		if (!$this->supportsSecondaryCaching()) {
			throw new WURFL_Storage_Exception("The storage provider ".get_class($cache)." cannot be used as a cache for ".get_class($this));
		}
		$this->cache = $cache;
	}
	
	protected function cacheSave($objectId, $object) {
		if ($this->cache === null) return;
		$this->cache->save('FCACHE_'.$objectId, $object);
	}
	
	protected function cacheLoad($objectId) {
		if ($this->cache === null) return null;
		return $this->cache->load('FCACHE_'.$objectId);
	}
	
	protected function cacheRemove($objectId) {
		if ($this->cache === null) return;
		$this->cache->remove('FCACHE_'.$objectId);
	}
	
	protected function cacheClear() {
		if ($this->cache === null) return;
		$this->cache->clear();
	}
	
	/**
	 * Checks if WURFL is Loaded
	 * @return bool
	 */
	public function isWURFLLoaded() {
		return $this->load(self::WURFL_LOADED);
	}

	/**
	 * Sets the WURFL Loaded flag
	 * @param bool $loaded
	 */
	public function setWURFLLoaded($loaded = true) {
		$this->save(self::WURFL_LOADED, $loaded);
	}


	/**
	 * Encode the Object Id using the Persistence Identifier
	 * @param string $namespace
	 * @param string $input
	 * @return string $input with the given $namespace as a prefix
	 */
	protected function encode($namespace, $input) {
		return join(":", array(self::APPLICATION_PREFIX, $namespace, $input));
	}

	/**
	 * Decode the Object Id
	 * @param string $namespace
	 * @param string $input
	 * @return string value
	 */
	protected function decode($namespace, $input) {
		$inputs = explode(":", $input);
		return $inputs[2];
	}


}
