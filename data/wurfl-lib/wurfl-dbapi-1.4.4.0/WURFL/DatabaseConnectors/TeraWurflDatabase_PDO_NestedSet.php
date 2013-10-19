<?php
/**
 * Copyright (c) 2011 ScientiaMobile, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Refer to the COPYING.txt file distributed with this package.
 *
 * @package    WURFL_Database
 * @copyright  ScientiaMobile, Inc.
 * @author     Steve Kamerman <steve AT scientiamobile.com>
 * @license    GNU Affero General Public License
 * @version    $id$
 */
/**
 * Include required files
 */
require_once realpath(dirname(__FILE__).'/TeraWurflDatabase_PDO.php');
/**
 * Provides connectivity from Tera-WURFL to MySQL 5
 * This version adds a right and left nested-set value (`rt` and `lt`) to the TeraWurflIndex
 * table, then uses those values and the Nested Set method to lookup the fallback tree in 
 * one very efficient query.
 * @package TeraWurflDatabase
 */
class TeraWurflDatabase_PDO_NestedSet extends TeraWurflDatabase_PDO{
	public $use_nested_set = true;
}