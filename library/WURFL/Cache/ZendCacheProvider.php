<?php
/**
 * Copyright (c) 2011 ScientiaMobile, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Refer to the COPYING file distributed with this package.
 *
 *
 * @category   WURFL
 * @package    WURFL_Cache
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */

/**
 * A Cache Provider that uses the File System as a storage
 *
 * @category   WURFL
 * @package    WURFL_Cache
 */

class WURFL_Cache_ZendCacheProvider implements WURFL_Cache_CacheProvider 
{
    private $_cache;
    
    private $_cacheIdentifier = "ZEND_CACHE_PROVIDER";
    private $_expire;
    private $_root;
    
    public function __construct($params) 
    {
        if ($params instanceof Zend_Config) {
            $params = $params->toArray();
        }
        
        if ($params instanceof Zend_Cache_Manager) {
            $allowedNames = array('wurfl', 'wurfldata');
            $cacheFound   = false;
            
            foreach ($allowedNames as $name) {
                if ($params->hasCache($name)) {
                    $params     = $params->getCache($name);
                    $cacheFound = true;
                    break;
                }
            }
            
            if (!$found) {
                throw new WURFL_WURFLException('the allowed key names wasn\'t found in the given Zend_Cache_Manager object, only the key names \'wurfl\' or \'wurfldata\' are supported');
            }
        }
        
        if (!is_array($params) && !($params instanceof Zend_Cache)) {
            throw new WURFL_WURFLException('the parameter must be an array, an Zend_Config object or an Zend_Cache object');
        }
        
        if (is_array($params)) {
            $this->_cache = Zend_Cache::factory(
                $params['frontend'],
                $params['backend'],
                $params['frontendoptions'],
                $params['backendoptions']
            );
        } else {
            // $params instanceof Zend_Cache
            $this->_cache = $params;
        }
    
    }
    
    public function get($key) 
    {
        if (!$this->_cache->test($key)) {
            return null;
        }
        
        return $this->_cache->load($key);
    }
    
    public function put($key, $value) 
    {
        return $this->_cache->save($value, $key, array('wurfldata'));
    }
    
    public function clear() 
    {
        return $this->_cache->clean('all', array('wurfldata'));
    }
}

