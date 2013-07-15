<?php
namespace Wurfl\Storage;

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
 * @package    \Wurfl\Storage
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @author     Fantayeneh Asres Gizaw
 * @version    $id$
 */
/**
 * WURFL Storage
 * @package    \Wurfl\Storage
 */
class ZendCacheStorage extends Base 
{
    private $_zendCacheObject = null;
    
    protected $supports_secondary_caching = true;
    
    /**
     * Creates a new \Wurfl\Storage_Base
     * @param array $params
     */
    public function __construct($params = array())
    {
        $this->_zendCacheObject = null;
        
        if ($params instanceof \Zend\Config\Config) {
            $params = $params->toArray();
        }
        
        if (is_array($params)) {
            $this->_zendCacheObject = \Zend\Cache\Cache::factory(
                $params['frontend'],
                $params['backend'],
                $params['front'],
                $params['back']
            );
        } elseif ($params instanceof \Zend\Cache\Frontend) {
            $this->_zendCacheObject = clone $params;
        } elseif ($params instanceof \Zend\Cache\Manager) {
            $this->_zendCacheObject = $params->getCache('wurfl');
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
        
        if (($value = $this->_zendCacheObject->load($key)) !== null) {
            $this->cacheSave($key, $value);
            
            return $value;
        }
        
        return null;
    }
    
    public function save($key, $value, $expire = 0)
    {
        $this->cacheSave($key, $value);
        
        return $this->_zendCacheObject->save($value, $key, array('wurfl'));
    }

    /**
     * Removes the object identified by $objectId from the persistence provider
     * @param string $objectId
     */
    public function remove($objectId)
    {
        $this->_zendCacheObject->remove($objectId);
        
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