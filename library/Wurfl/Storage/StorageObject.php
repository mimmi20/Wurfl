<?php
namespace Wurfl\Storage;

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
 * @package    \Wurfl\Storage
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */

/**
 * Base Storage Provider
 *
 * A Skeleton implementation of the Storage Interface
 *
 * @category   WURFL
 * @package    \Wurfl\Storage
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @author     Fantayeneh Asres Gizaw
 * @version    $id$
 */
class StorageObject
{

    private $value;
	private $expiringOn;

    public function __construct($value, $expire)
    {
		$this->value = $value;
		$this->expiringOn = ($expire === 0) ? $expire : time() + $expire;
	}

	public function value()
    {
		return $this->value;
	}

	public function isExpired()
    {
		if ($this->expiringOn === 0) {
			return false;
		}
        
		return $this->expiringOn < time();
	}

	public function expiringOn()
    {
		return $this->expiringOn;
	}
}