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
 * @package	WURFL_Request
 * @copyright  ScientiaMobile, Inc.
 * @license	GNU Affero General Public License
 * @author	 Fantayeneh Asres Gizaw
 * @version	$id$
 */
/**
 * Information about the matching process
 * @package	WURFL_Request
 */
class WURFL_Request_MatchInfo {
	
	/**
	 * @var boolean Response was returned from cache
	 */
	public $from_cache = false;
	/**
	 * @var string The type of match that was made
	 */
	public $match_type;
	/**
	 * @var string The responsible Matcher/Handler
	 */
	public $matcher;
	/**
	 * @var string The history of Matchers/Handlers
	 */
	public $matcher_history = '';
	/**
	 * @var float The time it took to lookup the user agent
	 */
	public $lookup_time;
	/**
	 * @var string The user agent after normalization
	 */
	public $normalized_user_agent;
}