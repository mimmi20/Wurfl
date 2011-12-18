<?php
declare(ENCODING = 'utf-8');
namespace Wurfl\Storage;

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
class File extends Base
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
        $this->_root = realpath($params[self::DIR]);
        $this->_createCacheDirIfNotExist();
        $this->_expire = (int) $params['expiration'];
    }
    
    private function _createCacheDirIfNotExist()
    {
        if (!is_dir($this->_root)) {
            @mkdir($this->_root, 0777, TRUE);
            if (!is_dir($this->_root)) {
                throw new Exception('The file cache directory does not exist and could not be created. Please make sure the cache directory is writeable: ' . $this->_root);
            }
        }
        
        if (!is_writeable(dirname($this->_root))) {
            throw new Exception('The file cache directory is not writeable: ' . $this->_root);
        }
    }

    public function load($key)
    {
        $path  = $this->_keyPath($key);
        $value = \Wurfl\FileUtils::read($path);
        
        //var_dump($path, $value);exit;
        return ($value ? $this->_unwrap($value, $path) : null);
    }

    private function _unwrap(array $value, $path)
    {
        if ($this->_isExpired($value)) {
            unlink($path);
            return null;
        }
        
        return $value['value'];
    }

    private function _isExpired(array $value)
    {
        if ($value['expire'] === 0) {
            return false;
        }
        return $value['expire'] < time();
    }

    public function save($key, $value)
    {
        $value = array('value' => $value, 'expire' => $this->_expire);
        $path = $this->_keyPath($key);
        \Wurfl\FileUtils::write($path, $value);
    }

    public function clear()
    {
        \Wurfl\FileUtils::rmdirContents($this->_root);
    }


    private function _keyPath($key)
    {
        return \Wurfl\FileUtils::join(array($this->_root, strtolower($key)));
    }
}