<?php
/**
 * Copyright (c) 2015 ScientiaMobile, Inc.
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
 *
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
 *
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @author     Fantayeneh Asres Gizaw
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
     * @var string
     */
    const WURFL_LOCKED = 'WURFL_LOCKED';

    /**
     * @var AdapterInterface
     */
    private $adapter;

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
     * @param string $objectId
     * @param mixed  $object
     * @param int    $expiration If supported by the provider, this is used to specify the expiration
     *
     * @return bool
     */
    public function save($objectId, $object, $expiration = null)
    {
        if (null !== $expiration) {
            $this->adapter->setExpiration($expiration);
        }

        return $this->adapter->setItem($objectId, $object);
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

        return;
    }

    /**
     * Removes the object identified by $objectId from the persistence provider
     *
     * @param string $objectId
     *
     * @return bool
     */
    public function remove($objectId)
    {
        return $this->adapter->removeItem($objectId);
    }

    /**
     * Removes all entries from the Persistence Provider
     *
     * @return bool
     */
    public function clear()
    {
        return $this->adapter->flush();
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
    }

    /**
     * Acquires a lock so only this thread reloads the WURFL data, returns false if it cannot be acquired
     *
     * @return bool
     */
    public function acquireLock()
    {
        if ($this->adapter->hasItem(self::WURFL_LOCKED)) {
            return false;
        }

        return $this->save(self::WURFL_LOCKED, true);
    }

    /**
     * Releases the lock if one was acquired
     *
     * @return bool
     */
    public function releaseLock()
    {
        return $this->remove(self::WURFL_LOCKED);
    }

    /**
     * @return \WurflCache\Adapter\AdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }
}
