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
 * @category   WURFL
 * @package    WURFL_Cache
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
/**
 * Cache Provider factory
 * @package    WURFL_Cache
 */
class CacheProviderFactory
{
    const FILE_CACHE_PROVIDER_DIR = 'devices';
    const DEFAULT_CACHE_PROVIDER_NAME = 'file';
    
    /**
     * @var CacheProvider
     */
    private static $_cacheProvider;
    
    // prevent instantiation
    private function __construct()
    {
        //
    }
    
    private function __clone()
    {
        //
    }
    
    /**
     * Returns a CacheProvider based on the given $cacheConfig
     * @param WURFL_Configuration_Config $cacheConfig 
     * @return CacheProvider
     */
    static public function getCacheProvider($cacheConfig=null)
    {
        $cacheConfig = is_null($cacheConfig) ? \Wurfl\Configuration\ConfigHolder::getWURFLConfig()->cache : $cacheConfig;
        $provider = isset($cacheConfig['provider']) ? $cacheConfig['provider'] : NULL;
        $cache = isset($cacheConfig['params']) ? $cacheConfig['params'] : NULL;
        switch ($provider) {
            case Constants::FILE:
                self::$_cacheProvider = new \Wurfl\Cache\FileCacheProvider($cache);
                break;
            case Constants::MEMCACHE:
                self::$_cacheProvider = new \Wurfl\Cache\MemcacheCacheProvider($cache);
                break;
            case Constants::APC:
                self::$_cacheProvider = new \Wurfl\Cache\APCCacheProvider($cache);
                break;
            case Constants::EACCELERATOR:
                self::$_cacheProvider = new \Wurfl\Cache\EAcceleratorCacheProvider($cache);
                break;
            case Constants::MYSQL:
                self::$_cacheProvider = new \Wurfl\Cache\MysqlCacheProvider($cache);
                break;
            default:
                self::$_cacheProvider = new \Wurfl\Cache\NullCacheProvider();
                break;
        }
        return self::$_cacheProvider;
    }
}

