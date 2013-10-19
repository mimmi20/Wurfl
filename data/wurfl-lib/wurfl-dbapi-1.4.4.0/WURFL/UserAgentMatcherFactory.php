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
 * @package	WURFL
 * @copyright  ScientiaMobile, Inc.
 * @author	 Steve Kamerman <steve AT scientiamobile.com>
 * @license	GNU Affero General Public License
 * @version	$id$
 */
/**
 * Evaluates the user agent using keywords, regular expressions, UserAgentMatchers and HTTP Headers
 * @package TeraWurfl
 * @see UserAgentMatcher
 *
 */
class UserAgentMatcherFactory{

	// Properties
	/**
	 * @var array Array of errors
	 */
	public $errors;
	
	// Constructor
	/**
	 * Instantiate a new UserAgentMatcherFactory
	 */
	public function __construct() {
		$this->errors = array();
	}
	
	// Public Methods
	/**
	 * Determines which UserAgentMatcher is the best fit for the incoming user agent and returns it
	 * @param TeraWurfl $wurfl
	 * @param TeraWurflHttpRequest $httpRequest
	 * @return UserAgentMatcher
	 */
	public static function createUserAgentMatcher(TeraWurfl $wurfl, TeraWurflHttpRequest $httpRequest) {
		if (TeraWurflConfig::$SIMPLE_DESKTOP_ENGINE_ENABLE && $httpRequest->user_agent == WurflConstants::SIMPLE_DESKTOP_UA) {
			return new SimpleDesktopUserAgentMatcher($wurfl);
		}
		foreach (WurflConstants::$matchers as $matcher_name) {
			$matcher_class_name = $matcher_name.'UserAgentMatcher';
			/*
			 * Yes, call_user_func is slow and nasty, but it's required for PHP < 5.3 to call variable-named object static methods.
			 * This will be changed to "if ($matcher_class_name::canHandle($httpRequest)) {" once PHP 5.2 is deprecated
			 * Performance impact is about 0.0005 sec per request worst case
			 */
			if (call_user_func(array($matcher_class_name, 'canHandle'), $httpRequest)) {
				return new $matcher_class_name($wurfl);
			}
		}
		// This should never be possible, since the CatchAllUserAgentMatcher can handle anything
		throw new TeraWurflException('Fatal Error: No suitable matcher found for request!');
	}
	/**
	 * Return the UserAgentMatcher name for the given $httpRequest
	 * @param TeraWurfl $wurfl
	 * @param TeraWurflHttpRequest $httpRequest
	 * @return string UserAgentMatcher UserAgentMatcher name
	 */
	public static function userAgentType(TeraWurfl $wurfl, TeraWurflHttpRequest $httpRequest) {
		$matcher = self::createUserAgentMatcher($wurfl, $httpRequest);
		if ($matcher->runtime_normalization) {
			$matcher->simulation = true;
			$matcher->applyConclusiveMatch();
			$matcher->applyRecoveryMatch();
		}
		$type = get_class($matcher);
		return str_replace('UserAgentMatcher', '', $type);
	}
	
	public static function loadMatchers() {
		$dir = dirname(__FILE__).'/UserAgentMatchers/';
		require_once $dir.'UserAgentMatcher.php';
		foreach (WurflConstants::$matchers as $matcher_name) {
			$matcher_class_name = $matcher_name.'UserAgentMatcher';
			if (!class_exists($matcher_class_name, false)) {
				include $dir.$matcher_class_name.'.php';
			}
		}
	}
}
/**
 * Load User Agent Matchers
 */
UserAgentMatcherFactory::loadMatchers();