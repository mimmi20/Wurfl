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
 * @package    WURFL_HttpHeaderNormalizers
 * @copyright  ScientiaMobile, Inc.
 * @author     Steve Kamerman <steve AT scientiamobile.com>
 * @license    GNU Affero General Public License
 * @version    $id$
 */
/**
 * Normalizes User Agents
 * @package HttpHeaderNormalizers
 */
interface IHttpHeaderNormalizer {
	
	/**
	 * Normalizes the $http_header
	 * @param TeraWurflHttpRequestHeader $http_header
	 */
	public function normalize(TeraWurflHttpRequestHeader $http_header);
	
}