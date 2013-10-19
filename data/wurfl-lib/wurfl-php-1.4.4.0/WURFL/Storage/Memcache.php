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
class WURFL_Storage_Memcache extends WURFL_Storage_Base {

	const EXTENSION_MODULE_NAME = "memcache";
	const DEFAULT_PORT = 11211;

	/**
	 * @var memcache
	 */
	private $memcache;
	private $host;
	private $port;
	private $expiration;
	private $namespace;

	private $defaultParams = array(
		"host" => "127.0.0.1",
		"port" => "11211",
		"namespace" => "wurfl",
		"expiration" => 0
	);
	
	protected $is_volatile = true;
	
	public function __construct($params=array()) {
		$currentParams = is_array($params) ? array_merge($this->defaultParams, $params) : $this->defaultParams;
		$this->toFields($currentParams);
		$this->initialize();
	}

	private function toFields($params) {
		foreach($params as $key => $value) {
			$this->$key = $value;
		}
	}

	/**
	 * Initializes the Memcache Module
	 *
	 */
	public final function initialize() {
		$this->_ensureModuleExistence();
		$this->memcache = new Memcache();
		// support multiple hosts using semicolon to separate hosts
		$hosts = explode(";", $this->host);
		// different ports for each hosts the same way
		$ports = explode(";", $this->port);
		if (count($hosts) > 1) {
			if (count($ports) < 1) {
				$ports = array_fill(0, count($hosts), self::DEFAULT_PORT);
			} elseif (count($ports) == 1) {
				// if we have just one port, use it for all hosts
				$_p = $ports[0];
				$ports = array_fill(0, count($hosts), $_p);
			}
			foreach ($hosts as $i => $host) {
				$this->memcache->addServer($host, $ports[$i]);
			}
		} else {
			// just connect to the single host
			$this->memcache->connect($hosts[0], $ports[0]);
		}
	}

	public function save($objectId, $object, $expiration=null) {
		return $this->memcache->set($this->encode($this->namespace, $objectId), $object, 0, (($expiration === null)? $this->expiration: $expiration));
	}

	public function load($objectId) {
		$value = $this->memcache->get($this->encode($this->namespace, $objectId));
		return ($value !== false)? $value: null;
	}


	public function clear() {
		$this->memcache->flush();
	}

	public function __destruct() {
		$this->memcache = null;
	}

	/**
	 * Ensures the existence of the the PHP Extension memcache
	 * @throws WURFL_Storage_Exception required extension is unavailable
	 */
	private function _ensureModuleExistence() {
		if (!extension_loaded(self::EXTENSION_MODULE_NAME)) {
			throw new WURFL_Storage_Exception("The PHP extension memcache must be installed and loaded in order to use the Memcached.");
		}
	}

}