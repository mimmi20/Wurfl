<?php
namespace Wurfl\Configuration;

/**
 * Copyright (c) 2012 ScientiaMobile, Inc.
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 * Refer to the COPYING.txt file distributed with this package.
 *
 * @category   WURFL
 * @package    \Wurfl\Configuration
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
use Zend\Config\Config as ZendConfigConfig;

/**
 * Array-style WURFL configuration.  To use this method you must create a php file that contains
 * an array called $configuration with all of the required settings.  NOTE: every path that you
 * specify in the configuration must be absolute or relative to the folder that it is in.
 * Example: Here is an example for file persistence without caching
 * <code>
 * <?php
 * // config.php
 * $configuration = array(
 *   'wurfl' => array(
 *     'main-file' => "wurfl.xml",
 *     'patches' => array("web_browsers_patch.xml"),
 *   ),
 *   'match-mode' => 'accuracy',
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
 * $wurflConfig = new \Wurfl\Configuration_ArrayConfig('config.php');
 * $wurflManagerFactory = new \WURFL\WURFLManagerFactory($wurflConfig);
 * $wurflManager = $wurflManagerFactory->create();
 * $info = $wurflManager->getWurflInfo();
 * printf("Version: %s\nUpdated: %s\nOfficialURL: %s\n\n",
 *   $info->version,
 *   $info->lastUpdated,
 *   $info->officialURL
 * );
 * ?>
 * </code>
 *
 * @package    \Wurfl\Configuration
 */
class ZendConfig extends Config
{
    /**
     * Creates a new WURFL Configuration object from $configFilePath
     *
     * @param array|ZendConfigConfig $configuration
     *
     * @internal param string $configFilePath Complete filename of configuration file
     */
    public function __construct($configuration)
    {
        if ($configuration instanceof ZendConfigConfig) {
            $configuration = $configuration->toArray();
        }

        $this->init($configuration);
    }

    /**
     * Initialize the Configuration object
     */
    protected function initialize()
    {
        //
    }

    private function init($configuration)
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

        if (array_key_exists('logger', $configuration)) {
            $this->setLoggerConfiguration($configuration['logger']);
        }

        if (array_key_exists(Config::MATCH_MODE, $configuration)) {
            $this->setMatchMode($configuration[Config::MATCH_MODE]);
        }

        $this->allowReload = array_key_exists(Config::ALLOW_RELOAD, $configuration)
            ? $configuration[Config::ALLOW_RELOAD] : false;
    }

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

    private function setPersistenceConfiguration(array $persistenceConfig)
    {
        $this->persistence = $persistenceConfig;

        if (array_key_exists('params', $this->persistence)
            && array_key_exists(
                Config::DIR,
                $this->persistence['params']
            )
        ) {
            $this->persistence['params'][Config::DIR] = parent::getFullPath($this->persistence['params'][Config::DIR]);
        }
    }

    private function setCacheConfiguration(array $cacheConfig)
    {
        $this->cache = $cacheConfig;
    }

    private function setLoggerConfiguration(array $logger)
    {
        if (!empty($logger['logDir']) && !is_writable($logger['logDir'])) {
            $this->logger['type'] = 'Null';
            unset($logger['logDir']);
        }

        $this->logger = $logger;
    }

    private function setMatchMode($mode)
    {
        $this->matchMode = $mode;
    }
}