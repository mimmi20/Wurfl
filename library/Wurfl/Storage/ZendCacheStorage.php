<?php
namespace Wurfl\Storage;

/**
 * Copyright (c) 2012 ScientiaMobile, Inc.
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 * Refer to the COPYING.txt file distributed with this package.
 *
 * @category   WURFL
 * @package    \Wurfl\Storage
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @author     Fantayeneh Asres Gizaw
 * @version    $id$
 */
use Zend\Cache\Storage;
use Zend\Cache\Storage\StorageInterface;
use Zend\Cache\StorageFactory;
use Zend\Config\Config;

/**
 * WURFL Storage
 *
 * @package    \Wurfl\Storage
 */
class ZendCacheStorage extends Base
{
    /** @var null|StorageInterface  */
    private $_zendCacheObject = null;

    protected $supports_secondary_caching = true;

    /**
     * Creates a new \Wurfl\Storage\Base
     *
     * @param array $params
     *
     * @throws Exception
     */
    public function __construct($params = array())
    {
        $this->_zendCacheObject = null;

        if ($params instanceof Config) {
            $params = $params->toArray();
        }

        if (is_array($params)) {
            $this->_zendCacheObject = StorageFactory::factory($params);
        } elseif ($params instanceof StorageInterface) {
            $this->_zendCacheObject = clone $params;
        }

        if (null === $this->_zendCacheObject) {
            throw new Exception('an invalid input parameter is given');
        }
    }

    public function load($key)
    {
        if (($value = $this->cacheLoad($key)) !== null) {
            return $value;
        }

        $success = false;
        $value   = $this->_zendCacheObject->getItem($key, $success);

        if ($success) {
            $this->cacheSave($key, $value);

            return $value;
        }

        return null;
    }

    public function save($key, $value, $expire = 0)
    {
        $this->cacheSave($key, $value);

        return $this->_zendCacheObject->setItem($key, $value);
    }

    /**
     * Removes the object identified by $objectId from the persistence provider
     *
     * @param string $objectId
     *
     * @return ZendCacheStorage
     */
    public function remove($objectId)
    {
        $this->_zendCacheObject->removeItem($objectId);

        return $this;
    }

    /**
     * Removes all entries from the Persistence Provider
     */
    public function clear()
    {
        $this->_zendCacheObject->clean('all', array('wurfl'));

        return $this;
    }
}