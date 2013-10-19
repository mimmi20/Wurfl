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
class WURFL_Storage_Memory extends WURFL_Storage_Base {

	const IN_MEMORY = "memory";

	protected $persistenceIdentifier = "MEMORY_PERSISTENCE_PROVIDER";
	
	private $map;

	public function __construct($params=array()) {
		$this->clear();
	}

	public function save($objectId, $object, $expiration=null) {
		$key = hash('md5', $objectId);
		$this->map[$key[0]][substr($key, 1)] = $object;
	}

	public function load($objectId) {
		$key = hash('md5', $objectId);
		$idx = substr($key, 1);
		if (array_key_exists($idx, $this->map[$key[0]])) {
			return $this->map[$key[0]][$idx];
		}
		return null;
	}

	public function remove($objectId) {
		$key = hash('md5', $objectId);
		$idx = substr($key, 1);
		if (array_key_exists($idx, $this->map[$key[0]])) {
			unset($this->map[$key[0]][$idx]);
		}
	}

	private $tree_template = array('0'=>array(),'1'=>array(),'2'=>array(),'3'=>array(),'4'=>array(),'5'=>array(),'6'=>array(),'7'=>array(),'8'=>array(),'9'=>array(),'a'=>array(),'b'=>array(),'c'=>array(),'d'=>array(),'e'=>array(),'f'=>array());
	
	/**
	 * Removes all entry from the Persistence Provier
	 */
	public function clear() {
		// Setup empty btree to assist PHP in array index lookups
		$this->map = $this->tree_template;
	}
}