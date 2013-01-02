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
 * @category   WURFL
 * @package	WURFL_Storage
 * @copyright  ScientiaMobile, Inc.
 * @license	GNU Affero General Public License
 * @author	 Fantayeneh Asres Gizaw
 * @version	$id$
 */
/**
 * WURFL Storage
 * @package	WURFL_Storage
 */
class WURFL_Storage_File extends WURFL_Storage_Base {

	private $defaultParams = array(
		"dir" => "/tmp",
		"expiration" => 0,
	);

	private $expire;
	private $root;
	
	const DIR = "dir";

	protected $supports_secondary_caching = true;
	
	public function __construct($params) {
		$currentParams = is_array($params)? array_merge($this->defaultParams, $params): $this->defaultParams;
		$this->initialize($currentParams);
	}

	public function initialize($params) {
		$this->root = $params[self::DIR];
		$this->createCacheDirIfNotExist();
		$this->expire = $params["expiration"];
	}
	private function createCacheDirIfNotExist() {
		if (!is_dir($this->root)) {
			@mkdir ($this->root, 0777, true);
			if(!is_dir($this->root)){
				throw new WURFL_Storage_Exception("The file cache directory does not exist and could not be created. Please make sure the cache directory is writeable: ".$this->root);
			}
		}
		if(!is_writeable($this->root)){
			throw new WURFL_Storage_Exception("The file cache directory is not writeable: ".$this->root);
		}
	}

	public function load($key) {
		if (($data = $this->cacheLoad($key)) !== null) {
			return $data->value();
		} else {
			$path = $this->keyPath($key);
			$value = WURFL_FileUtils::read($path);
			if ($value === null) {
				return null;
			}
			$this->cacheSave($key, $value);
			return $this->unwrap($value, $path);
		}
	}

	private function unwrap($value, $path) {
		if ($value->isExpired()) {
			unlink($path);
			return null;
		}
		return $value->value();
	}

	public function save($key, $value, $expiration=null) {
		$value = new StorageObject($value, (($expiration === null)? $this->expire: $expiration));
		$path = $this->keyPath($key);
		WURFL_FileUtils::write($path, $value);
	}

	public function clear() {
		$this->cacheClear();
		WURFL_FileUtils::rmdirContents($this->root);
	}


	private function keyPath($key) {
		return WURFL_FileUtils::join(array($this->root, $this->spread(md5($key))));
	}

	function spread($md5, $n = 2) {
		$path = "";
		for ($i = 0; $i < $n; $i++) {
			$path .= $md5 [$i] . DIRECTORY_SEPARATOR;
		}
		$path .= substr($md5, $n);
		return $path;
	}


}

/**
 * Object for storing data
 * @package WURFL_Storage
 */
class StorageObject {
	private $value;
	private $expiringOn;

	public function __construct($value, $expire) {
		$this->value = $value;
		$this->expiringOn = ($expire === 0) ? $expire : time() + $expire;
	}

	public function value() {
		return $this->value;
	}

	public function isExpired() {
		if ($this->expiringOn === 0) {
			return false;
		}
		return $this->expiringOn < time();
	}

	public function expiringOn() {
		return $this->expiringOn;
	}

}