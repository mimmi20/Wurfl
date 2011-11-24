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
 * A Cache Provider that uses the File System as a storage
 *
 * @category   WURFL
 * @package    WURFL_Cache
 */

class FileCacheProvider implements CacheProvider
{
    private $_cacheDir;
    const DIR = 'dir';
    
    private $cacheIdentifier = 'FILE_CACHE_PROVIDER';
    private $expire;
    private $root;
    
    public function __construct($params)
    {
        if(is_array($params)) {
            if (!array_key_exists(self::DIR, $params)) {
                throw new \Wurfl\WURFLException('Specify a valid cache dir in the configuration file');
            }
            
            // Check if the directory exist and it is also write access
            if (!is_writable($params[self::DIR])) {
                throw new \Wurfl\WURFLException('The diricetory specified <' . $params[self::DIR] . ' > for the cache provider does not exist or it is not writable');
            }
            
            $this->_cacheDir = $params[self::DIR] . DIRECTORY_SEPARATOR . $this->cacheIdentifier;
            $this->root = $params[self::DIR] . DIRECTORY_SEPARATOR . $this->cacheIdentifier;
            $this->expire = isset($params[CacheProvider::EXPIRATION]) ? $params[CacheProvider::EXPIRATION] : CacheProvider::NEVER;
            
            \Wurfl\FileUtils::mkdir($this->_cacheDir);
        }
    
    }
    
    public function get($key)
    {
        $path = $this->_keyPath($key);
        $data = \Wurfl\FileUtils::read($path);
        if (!is_null($data) && $this->_expired($path)) {
            unlink($path);
            return NULL;
        }
        return $data;
    }
    
    public function put($key, $value)
    {
        $mtime = time() + $this->expire;
        $path = $this->_keyPath($key);
        \Wurfl\FileUtils::write($path, $value, $mtime);
    }
    
    public function clear()
    {
        \Wurfl\FileUtils::rmdirContents($this->root);
    }
    
    private function _expired($path)
    {
        if ($this->expire === 0) {
            return FALSE;
        }
        return filemtime($path) < time();
    }
    
    private function _neverToExpire()
    {
        return $this->expire === 0;
    }
    
    private function _keyPath($key)
    {
        return \Wurfl\FileUtils::join(array($this->root, $this->spread(md5($key))));
    }
    
    public function spread($md5, $n = 2)
    {
        $path = '';
        for ($i = 0; $i < $n; $i ++) {
            $path .= $md5[$i] . DIRECTORY_SEPARATOR;
        }
        $path .= substr($md5, $n);
        return $path;
    }

}

