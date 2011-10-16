<?php
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
 * @package    WURFL_Storage
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @author     Fantayeneh Asres Gizaw
 * @version    $id$
 */
/**
 * WURFL Storage
 * @package    WURFL_Storage
 */
class WURFL_Storage_File extends WURFL_Storage_Base
{
    private $_defaultParams = array(
        'dir' => '/var/tmp',
        'expiration' => 0,
);

    private $_expire;
    private $_root;

    const DIR = 'dir';

    public function __construct($params)
    {
        $currentParams = is_array($params) ? array_merge($this->_defaultParams, $params) : $this->_defaultParams;
        $this->initialize($currentParams);
    }

    public function initialize($params)
    {
        $this->_root = $params[self::DIR];
        $this->_createCacheDirIfNotExist();
        $this->_expire = $params['expiration'];
    }
    
    private function _createCacheDirIfNotExist()
    {
        if (!is_dir($this->_root)) {
            @mkdir($this->_root, 0777, TRUE);
            if(!is_dir($this->_root)){
                throw new WURFL_Storage_Exception('The file cache directory does not exist and could not be created. Please make sure the cache directory is writeable: ' . $this->_root);
            }
        }
        
        if (!is_writeable(dirname($this->_root))) {
            throw new WURFL_Storage_Exception('The file cache directory is not writeable: ' . $this->_root);
        }
    }

    public function load($key)
    {
        $path  = $this->_keyPath($key);
        $_value = WURFL_FileUtils::read($path);
        return isset($_value) ? $this->_unwrap($_value, $path) : NULL;
    }

    private function _unwrap($_value, $path)
    {
        if ($_value->isExpired()) {
            unlink($path);
            return NULL;
        }
        
        return $_value->value();
    }

    public function save($key, $_value)
    {
        $_value = new StorageObject($_value, $this->_expire);
        $path = $this->_keyPath($key);
        WURFL_FileUtils::write($path, $_value);
    }

    public function clear()
    {
        WURFL_FileUtils::rmdirContents($this->_root);
    }


    private function _keyPath($key)
    {
        return WURFL_FileUtils::join(array($this->_root, strtolower($key)/*$this->spread(md5($key))*/));
    }

    public function spread($md5, $n = 2)
    {
        $path = '';
        for ($i = 0; $i < $n; $i++) {
            $path .= $md5 [$i] . DIRECTORY_SEPARATOR;
        }
        $path .= substr($md5, $n);
        return $path;
    }


}

/**
 * Object for storing data
 * @package WURFL_Storage
 */
class StorageObject
{
    private $_value;
    private $_expiringOn;

    public function __construct($_value, $_expire)
    {
        $this->_value = $_value;
        $this->_expiringOn =($_expire === 0) ? $_expire : time() + $_expire;
    }

    public function value()
    {
        return $this->_value;
    }

    public function isExpired()
    {
        if ($this->_expiringOn === 0) {
            return false;
        }
        return $this->_expiringOn < time();
    }

    public function expiringOn()
    {
        return $this->_expiringOn;
    }

}