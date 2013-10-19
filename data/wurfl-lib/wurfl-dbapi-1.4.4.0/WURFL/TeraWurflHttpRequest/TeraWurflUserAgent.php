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
 * @package    WURFL_TeraWurflHttpRequest
 * @copyright  ScientiaMobile, Inc.
 * @author     Steve Kamerman <steve AT scientiamobile.com>
 * @license    GNU Affero General Public License
 * @version    $id$
 */
/**
 * User Agent
 * @package TeraWurfHttpRequest
 */
class TeraWurflUserAgent extends TeraWurflHttpRequestHeader {
	
	protected $_normalizer_name = 'GenericUserAgentNormalizer';
	
	public static function cleanUserAgent($ua) {
		$header = new TeraWurflUserAgent('HTTP_USER_AGENT', $ua);
		return $header->cleaned;
	}
}