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

namespace Wurfl\Storage;

use WurflCache\Adapter\AdapterInterface;

/**
 * Base Storage Provider
 *
 * A Skeleton implementation of the Storage Interface
 *
 * @category   WURFL
 * @package    WURFL_Storage
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @author     Fantayeneh Asres Gizaw
 * @version    $id$
 */
class Storage
{
    /**
     * @var string
     */
    const APPLICATION_PREFIX = 'WURFL_';

    /**
     * @var string
     */
    const WURFL_LOADED = 'WURFL_WURFL_LOADED';

    /**
     * @var AdapterInterface
     */
    private $adapter;

    /**
     * @var \Wurfl\Storage\Storage
     */
    private $cache;

    /**
     * Creates a new WURFL_Storage_Base
     *
     * @param \WurflCache\Adapter\AdapterInterface $adapter
     */
    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Saves the object
     *
     * @param string  $objectId
     * @param mixed   $object
     * @param integer $expiration If supported by the provider, this is used to specify the expiration
     */
    public function save($objectId, $object, $expiration = null)
    {
        if (null !== $expiration) {
            $this->adapter->setExpiration($expiration);
        }

        $this->adapter->setItem($objectId, $object);
    }

    /**
     * Returns the object identified by $objectId
     *
     * @param string $objectId
     *
     * @return mixed value
     */
    public function load($objectId)
    {
        $success = null;
        $value   = $this->adapter->getItem($objectId, $success);

        if ($success) {
            return $value;
        }

        return null;
    }

    /**
     * Removes the object identified by $objectId from the persistence provider
     *
     * @param string $objectId
     */
    public function remove($objectId)
    {
        $this->adapter->removeItem($objectId);
    }

    /**
     * Removes all entries from the Persistence Provider
     */
    public function clear()
    {
        $this->adapter->flush();
    }

    /**
     * This storage provider can be used as a secondary cache
     *
     * @param \Wurfl\Storage\Storage $cache
     *
     * @return boolean
     */
    public function validSecondaryCache(Storage $cache)
    {
        /**
         * True if $this supports secondary caching and the cache provider is not the
         * same class type since this would always decrease performance
         */
        return (get_class($this) != get_class($cache));
    }

    /**
     * Sets the cache provider for the persistence provider; this is used to
     * cache data in a volatile storage system like APC in front of a slow
     * persistence provider like the filesystem.
     *
     * @param \Wurfl\Storage\Storage $cache
     *
     * @return Storage
     * @throws Exception
     */
    public function setCacheStorage(Storage $cache)
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * saves an entry to the cache
     *
     * @param $objectId
     * @param $object
     */
    protected function cacheSave($objectId, $object)
    {
        if ($this->cache === null) {
            return;
        }

        $this->cache->save('FCACHE_' . $objectId, $object);
    }

    /**
     * loads an entry from the cache
     *
     * @param $objectId
     *
     * @return mixed|null
     */
    protected function cacheLoad($objectId)
    {
        if ($this->cache === null) {
            return null;
        }

        return $this->cache->load('FCACHE_' . $objectId);
    }

    /**
     * removes an entry from the cache
     *
     * @param $objectId
     */
    protected function cacheRemove($objectId)
    {
        if ($this->cache === null) {
            return;
        }

        $this->cache->remove('FCACHE_' . $objectId);
    }

    /**
     * clears the cache
     */
    protected function cacheClear()
    {
        if ($this->cache === null) {
            return;
        }

        $this->cache->clear();
    }

    /**
     * Checks if WURFL is Loaded
     *
     * @return bool
     */
    public function isWURFLLoaded()
    {
        return $this->load(self::WURFL_LOADED);
    }

    /**
     * Sets the WURFL Loaded flag
     *
     * @param bool $loaded
     */
    public function setWURFLLoaded($loaded = true)
    {
        $this->save(self::WURFL_LOADED, $loaded);
        $this->cacheSave(self::WURFL_LOADED, $loaded);
    }
}