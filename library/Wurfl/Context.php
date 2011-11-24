<?php
declare(ENCODING = 'utf-8');
namespace Wurfl;

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
 * @package    WURFL
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
/**
 * WURFL Context stores the persistence provider, cache provider and _logger objects
 * @package    WURFL
 */
class Context
{
    /**
     * @var WURFL_Xml_PersistenceProvider_AbstractPersistenceProvider
     */
    private $_persistenceProvider;
    
    /**
     * @var WURFL_Cache_CacheProvider
     */
    private $_cacheProvider;
    
    /**
     * @var WURFL_Logger_Interface
     */
    private $_logger;
    
    public function __construct($persistenceProvider, $cacheProvider = null, $logger = null)
    {
        $this->_persistenceProvider = $persistenceProvider;
        $this->_cacheProvider = is_null($cacheProvider) ? new \Wurfl\Cache\NullCacheProvider() : $cacheProvider;
        $this->_logger = is_null($logger) ? new \Wurfl\Logger\NullLogger() : $logger;
    }
    
    public function cacheProvider($cacheProvider)
    {
        return new self($this->_persistenceProvider, $cacheProvider, $this->_logger);
    }
    
    public function logger($logger)
    {
        return new self($this->_persistenceProvider, $this->_cacheProvider, $logger);
    }
    
    public function __get($name)
    {
        $name = '_' . $name;
        return $this->$name;
    }

}