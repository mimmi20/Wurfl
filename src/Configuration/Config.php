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
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */

namespace Wurfl\Configuration;

/**
 * Abstract base class for WURFL Configuration
 *
 * @property-read string  $configFilePath
 * @property-read string  $configurationFileDir
 * @property-read bool    $allowReload
 * @property-read array   $capabilityFilter
 * @property-read string  $wurflFile
 * @property-read array   $wurflPatches
 * @property-read array   $persistence
 * @property-read array   $cache
 * @property-read string  $matchMode
 */
abstract class Config
{
    const WURFL                  = 'wurfl';
    const MAIN_FILE              = 'main-file';
    const PATCHES                = 'patches';
    const PATCH                  = 'patch';
    const CACHE                  = 'cache';
    const PERSISTENCE            = 'persistence';
    const PROVIDER               = 'provider';
    const PARAMS                 = 'params';
    const ALLOW_RELOAD           = 'allow-reload';
    const CAPABILITY_FILTER      = 'capability-filter';
    const CAPABILITY             = 'capability';
    const DIR                    = 'dir';
    const EXPIRATION             = 'expiration';
    const MATCH_MODE             = 'match-mode';
    const MATCH_MODE_PERFORMANCE = 'performance';
    const MATCH_MODE_ACCURACY    = 'accuracy';

    /**
     * @var string Path to the configuration file
     */
    protected $configFilePath;

    /**
     * @var string Directory that the configuration file is in
     */
    protected $configurationFileDir;

    /**
     * @var bool true if a WURFL reload is allowed
     */
    protected $allowReload = false;

    /**
     * @var string wurfl file (normally wurfl.xml)
     */
    protected $wurflFile;

    /**
     * @var array Array of WURFL patches
     */
    protected $wurflPatches = array();

    /**
     * @var array Array of capabilities to be loaded
     */
    protected $capabilityFilter = array();

    /**
     * @var array
     */
    protected $persistence = array();

    /**
     * @var array
     */
    protected $cache = array();

    /**
     * Mode of operation (performance or accuracy)
     *
     * @var string
     */
    protected $matchMode = self::MATCH_MODE_ACCURACY;

    /**
     * Initialize Configuration
     *
     * @param array $configuration
     */
    protected function initialize(array $configuration)
    {
        if (array_key_exists(self::WURFL, $configuration)) {
            $this->setWurflConfiguration($configuration[self::WURFL]);
        }

        if (array_key_exists(self::PERSISTENCE, $configuration)) {
            $this->persistence = $this->buildPersistenceConfiguration($configuration[self::PERSISTENCE]);
        }

        if (array_key_exists(self::CACHE, $configuration)) {
            $this->cache = $this->buildPersistenceConfiguration($configuration[self::CACHE]);
        }

        if (array_key_exists(self::CAPABILITY_FILTER, $configuration)) {
            $this->capabilityFilter = $configuration[self::CAPABILITY_FILTER];
        }

        if (array_key_exists(self::MATCH_MODE, $configuration)) {
            $this->setMatchMode($configuration[self::MATCH_MODE]);
        }

        $this->allowReload = array_key_exists(self::ALLOW_RELOAD, $configuration)
            ? (boolean) $configuration[self::ALLOW_RELOAD] : false;
    }

    /**
     * @param array $wurflConfig
     */
    protected function setWurflConfiguration(array $wurflConfig)
    {
        if (array_key_exists(self::MAIN_FILE, $wurflConfig)) {
            $this->wurflFile = $this->getFullPath($wurflConfig[self::MAIN_FILE]);
        }

        if (array_key_exists(self::PATCHES, $wurflConfig)) {
            if (array_key_exists(self::PATCH, $wurflConfig[self::PATCHES])) {
                if (!is_array($wurflConfig[self::PATCHES][self::PATCH])) {
                    $wurflConfig[self::PATCHES][self::PATCH] = array($wurflConfig[self::PATCHES][self::PATCH]);
                }

                foreach ($wurflConfig[self::PATCHES][self::PATCH] as $wurflPatch) {
                    $this->wurflPatches[] = $this->getFullPath($wurflPatch);
                }
            } else {
                foreach ($wurflConfig[self::PATCHES] as $wurflPatch) {
                    $this->wurflPatches[] = $this->getFullPath($wurflPatch);
                }
            }
        }
    }

    /**
     * @param array $persistenceConfig
     *
     * @return array
     */
    protected function buildPersistenceConfiguration(array $persistenceConfig)
    {
        $persistence = array();

        if (!array_key_exists(self::PROVIDER, $persistenceConfig)) {
            $persistence[self::PROVIDER] = 'null';
        } else {
            $persistence[self::PROVIDER] = $persistenceConfig[self::PROVIDER];
        }

        if (array_key_exists(self::PARAMS, $persistenceConfig)) {
            if (!is_array($persistenceConfig[self::PARAMS])) {
                $persistenceConfig[self::PARAMS] = $this->toArray($persistenceConfig[self::PARAMS]);
            }

            $persistence[self::PARAMS] = $persistenceConfig[self::PARAMS];

            if (array_key_exists(self::DIR, $persistence[self::PARAMS])) {
                $persistence[self::PARAMS][self::DIR] = $this->getFullPath($persistence[self::PARAMS][self::DIR]);
            }
        }

        return $persistence;
    }

    /**
     * Converts given CSV $params to array of parameters
     *
     * @param string $params Comma-seperated list of parameters
     *
     * @return array Parameters
     */
    private function toArray($params)
    {
        $paramsArray = array();

        foreach (explode(',', $params) as $param) {
            $paramNameValue = explode('=', $param);
            if (count($paramNameValue) > 1) {
                if (strcmp(self::DIR, $paramNameValue[0]) === 0) {
                    $paramNameValue[1] = parent::getFullPath($paramNameValue[1]);
                }
                $paramsArray[trim($paramNameValue[0])] = trim($paramNameValue[1]);
            }
        }

        return $paramsArray;
    }

    /**
     * @param string $mode
     */
    protected function setMatchMode($mode)
    {
        if (!self::validMatchMode($mode)) {
            throw new \InvalidArgumentException('Invalid Match Mode: ' . $mode);
        }

        $this->matchMode = $mode;
    }

    /**
     * Magic Method
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->$name;
    }

    /**
     * True if the engine is in High Performance mode
     *
     * @return bool
     */
    public function isHighPerformance()
    {
        return ($this->matchMode === self::MATCH_MODE_PERFORMANCE);
    }

    /**
     * @param $mode
     *
     * @return bool
     */
    public static function validMatchMode($mode)
    {
        if ($mode === self::MATCH_MODE_PERFORMANCE || $mode === self::MATCH_MODE_ACCURACY) {
            return true;
        }

        return false;
    }

    /**
     * @return string Config file including full path and filename
     */
    protected function getConfigFilePath()
    {
        return $this->configFilePath;
    }

    /**
     * @return string Config file directory
     */
    protected function getConfigurationFileDir()
    {
        return $this->configurationFileDir;
    }

    /**
     * @param string $confLocation
     *
     * @return bool file exists
     */
    protected function fileExist($confLocation)
    {
        $fullFileLocation = $this->getFullPath($confLocation);

        return file_exists($fullFileLocation);
    }

    /**
     * Return the full path
     *
     * @param string $fileName
     *
     * @throws \InvalidArgumentException The configuration file does not exist
     * @return string                    File name including full path
     */
    protected function getFullPath($fileName)
    {
        if (!is_string($fileName)) {
            throw new \InvalidArgumentException('the given path is invalid');
        }

        $fileName = trim($fileName);

        if (realpath($fileName) && !(basename($fileName) === $fileName)) {
            return realpath($fileName);
        }

        $fullName = implode(DIRECTORY_SEPARATOR, array($this->configurationFileDir, $fileName));

        if (file_exists($fullName)) {
            return realpath($fullName);
        }

        throw new \InvalidArgumentException('The specified path \'' . $fullName . '\' does not exist');
    }
}
