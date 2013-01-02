<?php
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
 * @package	WURFL_Configuration
 * @copyright  ScientiaMobile, Inc.
 * @license	GNU Affero General Public License
 * @version	$id$
 */
/**
 * Abstract base class for WURFL Configuration
 * @package	WURFL_Configuration
 * 
 * @property string $configFilePath
 * @property string $configurationFileDir
 * @property boolean $allowReload
 * @property string $wurflFile
 * @property array $wurflPatches
 * @property array $persistence 
 * @property array $cache 
 * @property string $logDir
 * @property string $matchMode
 */
abstract class  WURFL_Configuration_Config {

	const WURFL = "wurfl";
	const MAIN_FILE = "main-file";
	const PATCHES = "patches";
	const PATCH = "patch";
	const CACHE = "cache";
	const PERSISTENCE = "persistence";
	const PROVIDER = "provider";
	const PARAMS = "params";
	const LOG_DIR = "logDir";
	const ALLOW_RELOAD = "allow-reload";
	const DIR = "dir";
	const EXPIRATION = "expiration";
	const MATCH_MODE = "match-mode";
	const MATCH_MODE_PERFORMANCE = "performance";
	const MATCH_MODE_ACCURACY = "accuracy";
	
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
	protected $wurflPatches;
	/**
	 * @var array
	 */
	protected $persistence = array();
	/**
	 * @var array
	 */
	protected $cache = array();
	/**
	 * @var string
	 */
	protected $logDir;
	/**
	 * Mode of operation (performance or accuracy)
	 * @var string
	 */
	protected $matchMode = self::MATCH_MODE_ACCURACY;
	
	/**
	 * Creates a new WURFL Configuration object from $configFilePath
	 * @param string $configFilePath Complete filename of configuration file 
	 */
	public function __construct($configFilePath) {
		if(!file_exists($configFilePath)) {
			throw new InvalidArgumentException("The configuration file " . $configFilePath . " does not exist.");
		}
		$this->configFilePath = $configFilePath;
		$this->configurationFileDir = dirname($this->configFilePath);
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
	public function __get($name) {
		return $this->$name;
	}
	
	/**
	 * True if the engine is in High Performance mode
	 * @return boolean
	 */
	public function isHighPerformance() {
		return ($this->matchMode == self::MATCH_MODE_PERFORMANCE);
	}
	
	public static function validMatchMode($mode) {
		if ($mode == self::MATCH_MODE_PERFORMANCE || $mode == self::MATCH_MODE_ACCURACY) {
			return true;
		}
		return false;
	}
	
	/**
	 * @return string Config file including full path and filename
	 */
	protected function getConfigFilePath() {
		return $this->configFilePath;
	}
	
	/**
	 * @return string Config file directory
	 */
	protected function getConfigurationFileDir() {
		return $this->configurationFileDir;
	}
	
	/**
	 * @param string $confLocation
	 * @return bool file exists
	 */
	protected function fileExist($confLocation) {
		$fullFileLocation = $this->getFullPath($confLocation);
		return file_exists($fullFileLocation);
	}
		
	/**
	 * Return the full path
	 *
	 * @param string $fileName
	 * @throws WURFL_WURFLException The configuration file does not exist
	 * @return string File name including full path
	 */
	protected function getFullPath($fileName) {;
		$fileName = trim($fileName);
		if(realpath($fileName) && !(basename($fileName) === $fileName )) {
			return realpath($fileName);
		}
		$fullName = join(DIRECTORY_SEPARATOR, array($this->configurationFileDir, $fileName));
		
		if(file_exists($fullName)) {
			return $fullName;
		}
		throw new WURFL_WURFLException("The specified path '" . $fullName . "' does not exist");
	}
}