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
 * @version   SVN: $Id$
 */
/**
 * Abstract base class for WURFL Configuration
 * @package    WURFL_Configuration
 */
abstract class Config
{
    const WURFL = 'wurfl';
    const MAIN_FILE = 'main-file';
    const PATCHES = 'patches';
    const PATCH = 'patch';
    const CACHE = 'cache';
    const PERSISTENCE = 'persistence';
    const PROVIDER = 'provider';
    const PARAMS = 'params';
    const LOG_DIR = 'logDir';
    const ALLOW_RELOAD = 'allow-reload';
    const DIR = 'dir';
    const EXPIRATION = 'expiration';
    
    /**
     * @var string Path to the configuration file
     */
    protected $_configFilePath;
    
    /**
     * @var string Directory that the configuration file is in
     */
    protected $_configurationFileDir;
    
    /**
     * @var bool true if a WURFL reload is allowed
     */
    protected $_allowReload = false;
    
    /**
     * @var string wurfl file(normally wurfl.xml)
     */
    protected $_wurflFile;
    
    /**
     * @var array Array of WURFL patches
     */
    protected $_wurflPatches;
    
    /**
     * @var array
     */
    protected $_persistence = array();
    
    /**
     * @var array
     */
    protected $_cache = array();
    
    /**
     * @var string
     */
    protected $_logDir;
    
    /**
     * Creates a new WURFL Configuration object from $configFilePath
     * @param string $configFilePath Complete filename of configuration file 
     */
    public function __construct($configFilePath)
    {
        if (!file_exists($configFilePath)) {
            throw new \InvalidArgumentException('The configuration file \'' . $configFilePath . '\' does not exist.');
        }
        $this->_configFilePath = $configFilePath;
        $this->_configurationFileDir = dirname($this->_configFilePath);
        $this->initialize();
    }

    /**
     * Initialize the Configuration object
     */
    protected abstract function initialize();
    
    /**
     * Magic Method 
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        $property = '_' . $name;
        
        return $this->$property;
    }    
    
    /**
     * @return string Config file including full path and filename
     */
    private function _getConfigFilePath()
    {
        return $this->_configFilePath;
    }
    
    /**
     * @return string Config file directory
     */
    private function _getConfigurationFileDir()
    {
        return $this->_configurationFileDir;
    }
    
    /**
     * @param string $confLocation
     * @return bool file exists
     */
    private function _fileExist($confLocation)
    {
        $fullFileLocation = $this->getFullPath($confLocation);
        return file_exists($fullFileLocation);
    }
        
    /**
     * Return the full path
     *
     * @param string $fileName
     * @throws \Wurfl\WURFLException The configuration file does not exist
     * @return string File name including full path
     */
    protected function getFullPath($fileName)
    {
        $fileName = trim($fileName);
        if (realpath($fileName) && !(basename($fileName) === $fileName)) {
            return realpath($fileName);
        }
        $fullName = join(DIRECTORY_SEPARATOR, array($this->_configurationFileDir, $fileName));
        
        if (file_exists($fullName)) {
            return $fullName;
        }
        throw new \Wurfl\WURFLException('The specified path \'' . $fullName . '\' does not exist');
    }
}