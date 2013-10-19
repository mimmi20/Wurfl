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
 * @package    WURFL
 * @copyright  ScientiaMobile, Inc.
 * @author     Steve Kamerman <steve AT scientiamobile.com>
 * @license    GNU Affero General Public License
 * @version    $id$
 */
/**
 * Provides Exceptions for use in Tera-WURFL
 * @package TeraWurflExceptions
 *
 */
class TeraWurflException extends Exception {}
class TeraWurflInvalidUserAgentException extends TeraWurflException {}
class TeraWurflInvalidDeviceIDException extends TeraWurflException {}
class TeraWurflDatabaseException extends TeraWurflException {}
class TeraWurflCLIInvalidArgumentException extends TeraWurflException {}
class TeraWurflUpdateDownloaderException extends TeraWurflException {}