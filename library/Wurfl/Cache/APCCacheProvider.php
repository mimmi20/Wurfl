<?php
declare(ENCODING = 'utf-8');
namespace Wurfl\Cache;

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
 *
 * @category   WURFL
 * @package    WURFL_Cache
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
/**
 * Cache provider that uses APC for in-memory caching
 * @package    WURFL_Cache
 */
class APCCacheProvider implements CacheProvider
{
    const EXTENSION_MODULE_NAME = 'apc';
    
    /**
     * @var int Cache expiration
     */
    private $expire;
    
    /**
     * @param array $params
     */
    public function __construct($params=null)
    {
        $this->_ensureModuleExistance();
        $this->expire = array_key_exists(CacheProvider::EXPIRATION, $params) ? $params[CacheProvider::EXPIRATION] : CacheProvider::NEVER;
    }
    
    public function get($key)
    {
        $value = apc_fetch($key);
        if ($value === FALSE) {
            return NULL;
        }
        return $value;
    }

    public function put($key, $value)
    {
        apc_store($key, $value, $this->expire);
    }
    
    public function clear() 
    {
        apc_clear_cache('user');
    }
    
     /**
      * Ensures the existence of the the PHP Extension apc
     * @throws \Wurfl\Xml\PersistenceProvider\Exception APC extension does not exist
     */
    private function _ensureModuleExistance() 
    {
        if (!extension_loaded(self::EXTENSION_MODULE_NAME)) {
            throw new \Wurfl\Xml\PersistenceProvider\Exception('The PHP extension \'apc\' must be installed and loaded in order to use this cache provider');
        }
    }
}

