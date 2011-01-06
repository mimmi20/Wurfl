<?php
/**
 * Tera_WURFL - PHP MySQL driven WURFL
 *
 * Tera-WURFL was written by Steve Kamerman, and is based on the
 * Java WURFL Evolution package by Luca Passani and WURFL PHP Tools by Andrea Trassati.
 * This version uses a MySQL database to store the entire WURFL file, multiple patch
 * files, and a persistent caching mechanism to provide extreme performance increases.
 *
 * @package TeraWurflDatabase
 * @author Steve Kamerman <stevekamerman AT gmail.com>
 * @version Stable 2.1.3 $Date: 2010/09/18 15:43:21
 * @license http://www.mozilla.org/MPL/ MPL Vesion 1.1
 */
/**
 * Include required files
 */
require_once realpath(dirname(__FILE__).'/TeraWurflDatabase_MySQL5.php');
/**
 * Provides connectivity from Tera-WURFL to MySQL 5
 * This version adds a right and left nested-set value (`rt` and `lt`) to the TeraWurflIndex
 * table, then uses those values and the Nested Set method to lookup the fallback tree in 
 * one very efficient query.
 * @package TeraWurflDatabase
 */
class TeraWurflDatabase_MySQL5_NestedSet extends TeraWurflDatabase_MySQL5{
	public $use_nested_set = true;
}