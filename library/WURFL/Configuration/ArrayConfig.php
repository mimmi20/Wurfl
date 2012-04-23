<?php
declare(ENCODING = 'utf-8');
namespace WURFL\Configuration;

/**
 * Copyright (c) 2012 ScientiaMobile, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Refer to the COPYING.txt file distributed with this package.
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
 *     'main-file' => "wurfl.xml",
 *     'patches' => array("web_browsers_patch.xml"),
 *   ),
 *   'match-mode' => 'high-accuracy',
 *   'allow-reload' => true,
 *   'persistence' => array(
 *     'provider' => "file",
 *     'params' => array(
 *       'dir' => "storage/persistence",
 *     ),
 *   ),
 *   'cache' => array(
 *     'provider' => "null",
 *   ),
 * );
 * ?>
 * <?php
 * // usage-example.php
 * require_once '/WURFL/Application.php';
 * // Here's where we use our config.php file above
 * $wurflConfig = new WURFL_Configuration_ArrayConfig('config.php');
 * $wurflManagerFactory = new \WURFL\WURFLManagerFactory($wurflConfig);
 * $wurflManager = $wurflManagerFactory->create();
 * $info = $wurflManager->getWURFLInfo();
 * printf("Version: %s\nUpdated: %s\nOfficialURL: %s\n\n",
 *   $info->version,
 *   $info->lastUpdated,
 *   $info->officialURL
 * );
 * ?>
 * </code>
 * @package    WURFL_Configuration
 */
class ArrayConfig extends Config {
    
    /**
     * Initialize class - gets called from the parent constructor
     * @throws WURFL_WURFLException configuration not present
     */
    protected function initialize() {
        include parent::getConfigFilePath();
        if(!isset($configuration) || !is_array($configuration)) {
            throw new WURFL_WURFLException("Configuration array must be defined in the configuraiton file");
        }
        
        $this->init($configuration);
    }
    
    
    private function init($configuration) {
        
        if (array_key_exists(\WURFL\Configuration\Config::WURFL, $configuration)) {
            $this->setWurflConfiguration($configuration[\WURFL\Configuration\Config::WURFL]);
        }
        
        if (array_key_exists(\WURFL\Configuration\Config::PERSISTENCE, $configuration)) {
            $this->setPersistenceConfiguration($configuration[\WURFL\Configuration\Config::PERSISTENCE]);
        }
        
        if (array_key_exists(\WURFL\Configuration\Config::CACHE, $configuration)) {
            $this->setCacheConfiguration($configuration [\WURFL\Configuration\Config::CACHE]);
        }
        
        if (array_key_exists(\WURFL\Configuration\Config::LOG_DIR, $configuration)) {
            $this->setLogDirConfiguration($configuration[\WURFL\Configuration\Config::LOG_DIR]);
        }
        
        if (array_key_exists(\WURFL\Configuration\Config::MATCH_MODE, $configuration)) {
            $this->setMatchMode($configuration[\WURFL\Configuration\Config::MATCH_MODE]);
        }

        $this->allowReload = array_key_exists(\WURFL\Configuration\Config::ALLOW_RELOAD, $configuration)? $configuration[\WURFL\Configuration\Config::ALLOW_RELOAD]: false; 
    }
    
    private function setWurflConfiguration(array $wurflConfig) {
        
        if (array_key_exists(\WURFL\Configuration\Config::MAIN_FILE, $wurflConfig)) {
            $this->wurflFile = parent::getFullPath($wurflConfig[\WURFL\Configuration\Config::MAIN_FILE]);
        }
        
        if(array_key_exists(\WURFL\Configuration\Config::PATCHES, $wurflConfig)) {
            foreach ($wurflConfig[\WURFL\Configuration\Config::PATCHES] as $wurflPatch) {
                $this->wurflPatches[] = parent::getFullPath($wurflPatch);
            }
        }        
    }
    
    private function setPersistenceConfiguration(array $persistenceConfig) {
        $this->persistence = $persistenceConfig;
        if(array_key_exists('params', $this->persistence) && array_key_exists(\WURFL\Configuration\Config::DIR, $this->persistence['params'])) {
            $this->persistence['params'][\WURFL\Configuration\Config::DIR] = parent::getFullPath($this->persistence['params'][\WURFL\Configuration\Config::DIR]);
        }
    }

    private function setCacheConfiguration(array $cacheConfig) {
        $this->cache = $cacheConfig;
    }
    
    private function setLogDirConfiguration($logDir) {
        if(!is_writable($logDir)) {
            throw new InvalidArgumentException("log dir $logDir  must exist and be writable");
        }
        $this->logDir = $logDir;
    }
    
    private function setMatchMode($mode) {
        $this->matchMode = $mode;
    }
}