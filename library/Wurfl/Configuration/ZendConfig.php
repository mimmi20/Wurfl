<?php
declare(ENCODING = 'utf-8');
namespace Wurfl\Configuration;

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
 * @package    WURFL_Configuration
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version   SVN: $Id$
 */
/**
 * XML Configuration
 * @package    WURFL_Configuration
 */
class ZendConfig extends ArrayConfig 
{
    private $_config = null;
    
    /**
     * Creates a new WURFL Configuration object from $configFilePath
     * @param string $configFilePath Complete filename of configuration file 
     */
    public function __construct($configFilePath) 
    {
        $config = $configFilePath;
        
        if ($config instanceof \Zend\Config) {
            //throw new InvalidArgumentException('The configuration file ' . $configFilePath . ' does not exist.');
            
            $config = $config->toArray();
        }
        
        $this->_config = $config;
        
        $this->initialize();
    }

    /**
     * Initialize XML Configuration
     */
    protected function initialize() 
    {
        $this->_init($this->_config);
    }
    
    private function _init($configuration) 
    {
        
        if (array_key_exists(Config::WURFL, $configuration)) {
            $this->_setWurflConfiguration($configuration[Config::WURFL]);
        }
        
        if (array_key_exists(Config::PERSISTENCE, $configuration)) {
            $this->_setPersistenceConfiguration($configuration[Config::PERSISTENCE]);
        }
        
        if (array_key_exists(Config::CACHE, $configuration)) {
            $this->_setCacheConfiguration($configuration[Config::CACHE]);
        }
        
        if (array_key_exists(Config::LOG_DIR, $configuration)) {
            $this->_setLogDirConfiguration($configuration[Config::LOG_DIR]);
        }

        $this->allowReload = array_key_exists(Config::ALLOW_RELOAD, $configuration)
                ? $configuration[Config::ALLOW_RELOAD] : false; 
    }
    
    private function _setWurflConfiguration(array $wurflConfig) 
    {
        if (array_key_exists(Config::MAIN_FILE, $wurflConfig)) {
            $this->_wurflFile = $this->getFullPath($wurflConfig[Config::MAIN_FILE]);
        }
        
        if (array_key_exists(Config::PATCHES, $wurflConfig)) {
            foreach($wurflConfig[Config::PATCHES] as $wurflPatch) {
                $this->_wurflPatches[] = $this->getFullPath($wurflPatch);
            }
        }        
    }
    
    private function _setPersistenceConfiguration(array $persistenceConfig) 
    {
        $this->persistence = $persistenceConfig;
        if (array_key_exists('params', $this->persistence) && array_key_exists(Config::DIR, $this->persistence['params'])) {
            $this->_persistence['params'][Config::DIR] = $this->getFullPath($this->persistence['params'][Config::DIR]);
        }
    }

    private function _setCacheConfiguration(array $cacheConfig) 
    {
        $this->_cache = $cacheConfig;
    }
    
    private function _setLogDirConfiguration($logDir) 
    {
        if (!is_writable($logDir)) {
            throw new \InvalidArgumentException('log dir ' . $logDir . ' must exist and be writable');
        }
        $this->_logDir = $logDir;
    }
}