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
 * @version   SVN: $Id$
 */

/**
 * EAcceleratorCacheProvider
 * 
 * An Implementation of the Cache using the eAccelerator cache
 * module.(http://eaccelerator.net/)
 *
 * @category   WURFL
 * @package    WURFL_Cache
 */
class EAcceleratorCacheProvider implements CacheProvider
{
    private $_expire;
    
    public function __construct($params)
    {
        if (is_array($params)) {
            $this->_expire = isset($params[CacheProvider::EXPIRATION]) ? $params[CacheProvider::EXPIRATION] : CacheProvider::NEVER;
        }
    }
    
    public function get($key)
    {
        return eaccelerator_get($key);
    }
    
    public function put($key, $value)
    {
        eaccelerator_put($key, $value, $this->_expire);
    }
    
    public function clear()
    {
        //
    }
}

