<?php
/**
 * Copyright (c) 2015 ScientiaMobile, Inc.
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
 * @package    WURFL
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */

namespace Wurfl\Configuration;

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
 *       'main-file' => 'wurfl.xml',
 *       'patches' => array('web_browsers_patch.xml'),
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
 *       'provider' => 'file',
 *       'params' => array(
 *         'dir' => 'storage/persistence',
 *       ),
 *   ),
 *   'cache' => array(
 *       'provider' => 'null',
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
 * $info = $wurflManager->getWurflInfo();
 * printf('Version: %s\nUpdated: %s\nOfficialURL: %s\n\n',
 *   $info->version,
 *   $info->lastUpdated,
 *   $info->officialURL
 * );
 * ?>
 * </code>
 *
 * @package    \Wurfl\Configuration
 */
class ArrayConfig
    extends Config
{
    /**
     * Initialize class - gets called from the parent constructor
     *
     * @throws \InvalidArgumentException configuration not present
     */
    protected function initialize()
    {
        include $this->getConfigFilePath();

        if (!isset($configuration) || !is_array($configuration)) {
            throw new \InvalidArgumentException('Configuration array must be defined in the configuraiton file');
        }

        $this->init($configuration);
    }

    /**
     * @param array $configuration
     */
    private function init(array $configuration)
    {
        if (array_key_exists(Config::WURFL, $configuration)) {
            $this->setWurflConfiguration($configuration[Config::WURFL]);
        }

        if (array_key_exists(Config::PERSISTENCE, $configuration)) {
            $this->setPersistenceConfiguration($configuration[Config::PERSISTENCE]);
        }

        if (array_key_exists(Config::CACHE, $configuration)) {
            $this->setCacheConfiguration($configuration[Config::CACHE]);
        }

        if (array_key_exists(Config::CAPABILITY_FILTER, $configuration)) {
            $this->capabilityFilter = $configuration[Config::CAPABILITY_FILTER];
        }

        if (array_key_exists(Config::LOG_DIR, $configuration)) {
            $this->setLogDirConfiguration($configuration[Config::LOG_DIR]);
        }

        if (array_key_exists(Config::MATCH_MODE, $configuration)) {
            $this->setMatchMode($configuration[Config::MATCH_MODE]);
        }

        $this->allowReload = array_key_exists(Config::ALLOW_RELOAD, $configuration)
            ? $configuration[Config::ALLOW_RELOAD] : false;
    }

    /**
     * @param array $wurflConfig
     */
    private function setWurflConfiguration(array $wurflConfig)
    {
        if (array_key_exists(Config::MAIN_FILE, $wurflConfig)) {
            $this->wurflFile = parent::getFullPath($wurflConfig[Config::MAIN_FILE]);
        }

        if (array_key_exists(Config::PATCHES, $wurflConfig)) {
            foreach ($wurflConfig[Config::PATCHES] as $wurflPatch) {
                $this->wurflPatches[] = parent::getFullPath($wurflPatch);
            }
        }
    }

    /**
     * @param array $persistenceConfig
     */
    private function setPersistenceConfiguration(array $persistenceConfig)
    {
        $this->persistence = $persistenceConfig;

        if (array_key_exists('params', $this->persistence) && array_key_exists(
                Config::DIR,
                $this->persistence['params']
            )
        ) {
            $this->persistence['params'][Config::DIR] = parent::getFullPath($this->persistence['params'][Config::DIR]);
        }
    }

    /**
     * @param array $cacheConfig
     */
    private function setCacheConfiguration(array $cacheConfig)
    {
        $this->cache = $cacheConfig;
    }

    /**
     * @param $logDir
     *
     * @throws \InvalidArgumentException
     */
    private function setLogDirConfiguration($logDir)
    {
        if (!is_writable($logDir)) {
            throw new \InvalidArgumentException('log dir $logDir  must exist and be writable');
        }

        $this->logDir = $logDir;
    }

    /**
     * @param string $mode
     */
    private function setMatchMode($mode)
    {
        $this->matchMode = $mode;
    }
}
