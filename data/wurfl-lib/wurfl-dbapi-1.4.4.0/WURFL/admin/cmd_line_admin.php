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
 * @package    WURFL_Admin
 * @copyright  ScientiaMobile, Inc.
 * @author     Steve Kamerman <steve AT scientiamobile.com>
 * @license    GNU Affero General Public License
 * @version    $id$
 */
/**
 * Include required files
 */
require_once realpath(dirname(__FILE__).'/../TeraWurfl.php');
require_once realpath(dirname(__FILE__).'/../TeraWurflUtils/TeraWurflCLI.php');

ini_set('display_errors', 'on');
error_reporting(E_ALL);

$cli = new TeraWurflCLI();
$cli->processArguments();
