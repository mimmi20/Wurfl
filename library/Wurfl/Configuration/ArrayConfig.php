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
 *
 * @category   WURFL
 * @package    WURFL_Configuration
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
/**
 * Array-style WURFL configuration.  To use this method you must create a php file that contains 
 * an array called $configuration with all of the required settings.  NOTE: every path that you
 * specify in the configuration must be absolute or relative to the folder that it is in.
 * 
 * Example: Here is an example for file persistence without caching
 * <code>
 * <?php
 * // config.php
 * $configuration = array(
 *   'wurfl' => array(
 *   'main-file' => 'wurfl.xml',
 *     'patches' => array('web_browsers_patch.xml'),
 *),
 *   'allow-reload' => true,
 *   'persistence' => array(
 *     'provider' => 'file',
 *     'params' => array(
 *       'dir' => 'storage/persistence',
 *),
 *),
 *   'cache' => array(
 *     'provider' => 'null',
 *),
 *);
 * ?>
 * <?php
 * // usage-example.php
 * require_once '/WURFL/Application.php';
 * // Here's where we use our config.php file above
 * $wurflConfig = new WURFL_Configuration_ArrayConfig('config.php');
 * $wurflManagerFactory = new WURFL_WURFLManagerFactory($wurflConfig);
 * $wurflManager = $wurflManagerFactory->create();
 * $info = $wurflManager->getWURFLInfo();
 * printf('Version: %s\nUpdated: %s\nOfficialURL: %s\n\n',
 *   $info->version,
 *   $info->lastUpdated,
 *   $info->officialURL
 *);
 * ?>
 * </code>
 * @package    WURFL_Configuration
 */
class ArrayConfig extends Config
{
    /**
     * Initialize class - gets called from the parent constructor
     * @throws \Wurfl\WURFLException configuration not present
     */
    protected function initialize()
    {
        include parent::getConfigFilePath();
        
        if (!isset($configuration) || !is_array($configuration)) {
            throw new \WURFL\WURFLException('Configuration array must be defined in the configuraiton file');
        }
        
        $this->_init($configuration);
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
            $this->_wurflFile = parent::getFullPath($wurflConfig[Config::MAIN_FILE]);
        }
        
        if (array_key_exists(Config::PATCHES, $wurflConfig)) {
            foreach($wurflConfig[Config::PATCHES] as $wurflPatch) {
                $this->_wurflPatches[] = parent::getFullPath($wurflPatch);
            }
        }        
    }
    
    private function _setPersistenceConfiguration(array $persistenceConfig)
    {
        $this->persistence = $persistenceConfig;
        if (array_key_exists('params', $this->persistence) && array_key_exists(Config::DIR, $this->persistence['params'])) {
            $this->_persistence['params'][Config::DIR] = parent::getFullPath($this->persistence['params'][Config::DIR]);
        }
    }

    private function _setCacheConfiguration(array $cacheConfig)
    {
        $this->_cache = $cacheConfig;
    }
    
    private function _setLogDirConfiguration($logDir)
    {
        if (!is_writable($logDir)) {
            throw new \InvalidArgumentException('log dir ' . $logDir . '  must exist and be writable');
        }
        $this->_logDir = $logDir;
    }
}