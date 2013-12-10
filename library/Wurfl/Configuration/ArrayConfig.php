<?php
namespace Wurfl\Configuration;

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
 *       'main-file' => "wurfl.xml",
 *       'patches' => array("web_browsers_patch.xml"),
 *   ),
 *   'match-mode' => 'high-accuracy',
 *   'allow-reload' => true,
 *   'capability-filter' => array(
 *     'is_wireless_device',
 *     'preferred_markup',
 *     'xhtml_support_level',
 *     'xhtmlmp_preferred_mime_type',
 *     'device_os',
 *     'device_os_version',
 *     'is_tablet',
 *     'mobile_browser_version',
 *     'pointing_method',
 *     'mobile_browser',
 *     'resolution_width',
 *   ),
 *   'persistence' => array(
 *       'provider' => "file",
 *       'params' => array(
 *         'dir' => "storage/persistence",
 *       ),
 *   ),
 *   'cache' => array(
 *       'provider' => "null",
 *   ),
 * );
 * ?>
 * <?php
 * // usage-example.php
 * require_once '/WURFL/Application.php';
 * // Here's where we use our config.php file above
 * $wurflConfig = new \Wurfl\Configuration\ArrayConfig('config.php');
 * $wurflManagerFactory = new \Wurfl\ManagerFactory($wurflConfig);
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
 * @deprecated
 */
class ArrayConfig extends \Wurfl\Configuration\Config {
    
    /**
     * Initialize class - gets called from the parent constructor
     * @throws \Wurfl\Exception configuration not present
     */
    protected function initialize() {
        include parent::getConfigFilePath();
        if(!isset($configuration) || !is_array($configuration)) {
            throw new \Wurfl\Exception("Configuration array must be defined in the configuraiton file");
        }
        
        $this->init($configuration);
    }
    
    
    private function init($configuration) {
        
        if (array_key_exists(\Wurfl\Configuration\Config::WURFL, $configuration)) {
            $this->setWurflConfiguration($configuration[\Wurfl\Configuration\Config::WURFL]);
        }
        
        if (array_key_exists(\Wurfl\Configuration\Config::PERSISTENCE, $configuration)) {
            $this->setPersistenceConfiguration($configuration[\Wurfl\Configuration\Config::PERSISTENCE]);
        }
        
        if (array_key_exists(\Wurfl\Configuration\Config::CACHE, $configuration)) {
            $this->setCacheConfiguration($configuration[\Wurfl\Configuration\Config::CACHE]);
        }
        
        if (array_key_exists(\Wurfl\Configuration\Config::CAPABILITY_FILTER, $configuration)) {
            $this->capabilityFilter = $configuration[\Wurfl\Configuration\Config::CAPABILITY_FILTER];
        }
        
        if (array_key_exists(\Wurfl\Configuration\Config::LOG_DIR, $configuration)) {
            $this->setLogDirConfiguration($configuration[\Wurfl\Configuration\Config::LOG_DIR]);
        }
        
        if (array_key_exists(\Wurfl\Configuration\Config::MATCH_MODE, $configuration)) {
            $this->setMatchMode($configuration[\Wurfl\Configuration\Config::MATCH_MODE]);
        }

        $this->allowReload = array_key_exists(\Wurfl\Configuration\Config::ALLOW_RELOAD, $configuration)? $configuration[\Wurfl\Configuration\Config::ALLOW_RELOAD]: false; 
    }
    
    private function setWurflConfiguration(array $wurflConfig) {
        
        if (array_key_exists(\Wurfl\Configuration\Config::MAIN_FILE, $wurflConfig)) {
            $this->wurflFile = parent::getFullPath($wurflConfig[\Wurfl\Configuration\Config::MAIN_FILE]);
        }
        
        if(array_key_exists(\Wurfl\Configuration\Config::PATCHES, $wurflConfig)) {
            foreach ($wurflConfig[\Wurfl\Configuration\Config::PATCHES] as $wurflPatch) {
                $this->wurflPatches[] = parent::getFullPath($wurflPatch);
            }
        }        
    }
    
    private function setPersistenceConfiguration(array $persistenceConfig) {
        $this->persistence = $persistenceConfig;
        if(array_key_exists('params', $this->persistence) && array_key_exists(\Wurfl\Configuration\Config::DIR, $this->persistence['params'])) {
            $this->persistence['params'][\Wurfl\Configuration\Config::DIR] = parent::getFullPath($this->persistence['params'][\Wurfl\Configuration\Config::DIR]);
        }
    }

    private function setCacheConfiguration(array $cacheConfig) {
        $this->cache = $cacheConfig;
    }
    
    private function setLogDirConfiguration($logDir) {
        if(!is_writable($logDir)) {
            throw new \InvalidArgumentException("log dir $logDir  must exist and be writable");
        }
        $this->logDir = $logDir;
    }
    
    private function setMatchMode($mode) {
        $this->matchMode = $mode;
    }
}