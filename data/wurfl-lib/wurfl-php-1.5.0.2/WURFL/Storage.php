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
 * @package	WURFL_Cache
 * @copyright  ScientiaMobile, Inc.
 * @license	GNU Affero General Public License
 * @version	$id$
 */


/**
 * Cache is the base interface for any type of caching implementation.
 * It provides an API that allows storing and retrieving resources.
 *
 * @category   WURFL
 * @package	WURFL
 * @copyright  ScientiaMobile, Inc.
 * @license	GNU Affero General Public License
 * @version	$id$
 */
interface WURFL_Storage {

	/**
	 * @var string Key for storing data expiration times
	 */
	const EXPIRATION = "expiration";
	const ONE_HOUR = 3600;
	const ONE_DAY = 86400;
	const ONE_WEEK = 604800;
	const ONE_MONTH = 2592000;
	const ONE_YEAR = 31556926;
	const NEVER = 0;


	/**
	 * Put the the computed data into the cache so that it can be
	 * retrieved later.
	 * @param string $key key for accessing the data
	 * @param mixed $value the actual data been stored
	 * @param integer $expiration the expiration in seconds
	 */
	public function save($key, $value, $expiration=null);

	/**
	 * Get the previosly saved data.
	 * @param string $key key for accesing the data
	 * @return mixed the actual data been stored
	 */
	public function load($key);

	/**
	 * Invalidates the Cache
	 *
	 */
	public function clear();
	
}
