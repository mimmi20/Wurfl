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
 * @category   WURFL
 * @package    WURFL_Configuration
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
/**
 * XML Configuration
 * @package    WURFL_Configuration
 */
class WURFL_Configuration_ZendConfig extends WURFL_Configuration_ArrayConfig 
{
    private $_config = null;
    
    /**
     * Creates a new WURFL Configuration object from $configFilePath
     * @param string $configFilePath Complete filename of configuration file 
     */
    public function __construct($configFilePath) 
    {
        $config = $configFilePath;
        
        if ($config instanceof Zend_Config) {
            //throw new InvalidArgumentException("The configuration file " . $configFilePath . " does not exist.");
            
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
        $this->init($this->_config);
    }
    
    private function init($configuration) 
    {
        
        if (array_key_exists(WURFL_Configuration_Config::WURFL, $configuration)) {
            $this->setWurflConfiguration($configuration[WURFL_Configuration_Config::WURFL]);
        }
        
        if (array_key_exists(WURFL_Configuration_Config::PERSISTENCE, $configuration)) {
            $this->setPersistenceConfiguration($configuration[WURFL_Configuration_Config::PERSISTENCE]);
        }
        
        if (array_key_exists(WURFL_Configuration_Config::CACHE, $configuration)) {
            $this->setCacheConfiguration($configuration [WURFL_Configuration_Config::CACHE]);
        }
        
        if (array_key_exists(WURFL_Configuration_Config::LOG_DIR, $configuration)) {
            $this->setLogDirConfiguration($configuration[WURFL_Configuration_Config::LOG_DIR]);
        }

        $this->allowReload = array_key_exists(WURFL_Configuration_Config::ALLOW_RELOAD, $configuration)
                ? $configuration[WURFL_Configuration_Config::ALLOW_RELOAD] : false; 
    }
    
    private function setWurflConfiguration(array $wurflConfig) 
    {
        
        if (array_key_exists(WURFL_Configuration_Config::MAIN_FILE, $wurflConfig)) {
            $this->wurflFile = parent::getFullPath($wurflConfig[WURFL_Configuration_Config::MAIN_FILE]);
        }
        
        if(array_key_exists(WURFL_Configuration_Config::PATCHES, $wurflConfig)) {
            foreach ($wurflConfig[WURFL_Configuration_Config::PATCHES] as $wurflPatch) {
                $this->wurflPatches[] = parent::getFullPath($wurflPatch);
            }
        }        
    }
    
    private function setPersistenceConfiguration(array $persistenceConfig) 
    {
        $this->persistence = $persistenceConfig;
        if(array_key_exists('params', $this->persistence) && array_key_exists(WURFL_Configuration_Config::DIR, $this->persistence['params'])) {
            $this->persistence['params'][WURFL_Configuration_Config::DIR] = parent::getFullPath($this->persistence['params'][WURFL_Configuration_Config::DIR]);
        }
    }

    private function setCacheConfiguration(array $cacheConfig) 
    {
        $this->cache = $cacheConfig;
    }
    
    private function setLogDirConfiguration($logDir) 
    {
        if(!is_writable($logDir)) {
            throw new InvalidArgumentException("log dir $logDir  must exist and be writable");
        }
        $this->logDir = $logDir;
    }
}