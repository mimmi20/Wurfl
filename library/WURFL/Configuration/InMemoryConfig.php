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
 * In-memory WURFL Configuration
 * @package	WURFL_Configuration
 */
class WURFL_Configuration_InMemoryConfig extends  WURFL_Configuration_Config {

	/**
	 * Instantiate an In-Memory Configuration
	 */
	public function __construct() {}
	
	/**
	 * @param string $wurflFile
	 * @return WURFL_Configuration_InMemoryConfig $this
	 */
	public function wurflFile($wurflFile) {
		$this->wurflFile = $wurflFile;
		return $this;
	}
	
	/**
	 * @param string $wurflPatch
	 * @return WURFL_Configuration_InMemoryConfig $this
	 */
	public function wurflPatch($wurflPatch) {
		$this->wurflPatches[] = $wurflPatch;
		return $this;
	}
	/**
	 * Set persistence provider
	 * @param string $provider
	 * @param array $params
	 * @return WURFL_Configuration_InMemoryConfig $this
	 */
	public function persistence($provider, $params = array()) {
		$this->persistence = array_merge(array("provider"=> $provider), array("params" => $params));
		return $this;				
	}
	/**
	 * Set Cache provider
	 * @param string $provider
	 * @param array $params
	 * @return WURFL_Configuration_InMemoryConfig $this
	 */
	public function cache($provider, $params = array()) {
		$this->cache = array_merge(array("provider"=> $provider), array("params" => $params));
		return $this;
	}
	/**
	 * Set logging directory
	 * @param string $dir
	 * @return WURFL_Configuration_InMemoryConfig $this
	 */
	public function logDir($dir) {
		$this->logDir = $dir;
		return $this;
	}
	/**
	 * Specifies whether reloading is allowed
	 * @param bool $reload
	 * @return WURFL_Configuration_InMemoryConfig $this
	 */
	public function allowReload($reload=true) {
		$this->allowReload = $reload;
		return $this;
	}
	
	/**
	 * Sets the API match mode
	 * @param string $mode
	 * @return WURFL_Configuration_InMemoryConfig
	 */
	public function matchMode($mode) {
		if (!self::validMatchMode($mode)) {
			throw new WURFL_WURFLException('Invalid Match Mode: '.$mode);
		}
		$this->matchMode = $mode;
		return $this;
	}
	
	protected function initialize() {}
}