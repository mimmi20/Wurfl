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
/**#@+
 * Include required files
 */
require_once realpath(dirname(__FILE__).'/TeraWurflHttpRequestHeader.php');
require_once realpath(dirname(__FILE__).'/TeraWurflUserAgent.php');
require_once realpath(dirname(__FILE__).'/TeraWurflUserAgentProfile.php');
/**#@-*/
/**
 * Stores the incoming HTTP Request
 * @property TeraWurflUserAgent $user_agent
 * @property TeraWurflUserAgentProfile $uaprof
 * @property TeraWurflHttpRequestHeader $accept
 */
class TeraWurflHttpRequest {
	
	/**
	 * The order in which HTTP Headers are searched to find the proper User Agent
	 * @var array
	 */
	private $_user_agent_search_order = array(
		'HTTP_DEVICE_STOCK_UA',
		'HTTP_X_OPERAMINI_PHONE_UA',
		'HTTP_USER_AGENT',
	);
	private $_user_agent_key;
	
	/**
	 * The order in which HTTP Headers are searched to find the proper User Agent Profile
	 * @var array
	 */
	private $_user_agent_profile_search_order = array(
		'HTTP_X_WAP_PROFILE',
		'HTTP_PROFILE',
		'HTTP_WAP_PROFILE',
	);
	private $_user_agent_profile_key;
	
	
	private $_http_headers;
	
	
	public function __construct($raw_http_headers=null) {
		$headers = ($raw_http_headers === null)? $_SERVER: $raw_http_headers;
		$this->importHeaders($headers);
		$this->assignUserAgent();
		$this->assignUserAgentProfile();
	}
	
	/**
	 * @var boolean Mobile browser cache
	 */
	private $_is_mobile_browser = null;
	/**
	 * This HTTP Request is from a mobile device
	 * @return bool
	 */
	public function isMobileBrowser() {
		if ($this->_is_mobile_browser === null) {
			if ($this->isDesktopBrowser()) {
				$this->_is_mobile_browser = false;
			} else if ($this->user_agent->iContains(WurflConstants::$MOBILE_BROWSERS)) {
				$this->_is_mobile_browser = true;
			} else if ($this->user_agent->regexContains('/[^\d]\d{3}x\d{3}/')) {
				$this->_is_mobile_browser = true;
			} else {
				$this->_is_mobile_browser = false;
			}
		}
		return $this->_is_mobile_browser;
	}
	/**
	 * @var boolean Smart TV cache
	 */
	private $_is_smart_tv = null;
	/**
	 * This HTTP Request is from a Smart TV
	 * @return bool
	 */
	public function isSmartTV() {
		if ($this->_is_smart_tv === null) {
			$this->_is_smart_tv = $this->user_agent->iContains(WurflConstants::$SMARTTV_BROWSERS)? true: false;
		}
		return $this->_is_smart_tv;
	}
	/**
	 * @var boolean Desktop browser cache
	 */
	private $_is_desktop_browser = null;
	/**
	 * This HTTP Request is from a desktop web browser
	 * @return bool
	 */
	public function isDesktopBrowser() {
		if ($this->_is_desktop_browser === null) {
			$this->_is_desktop_browser = $this->user_agent->iContains(WurflConstants::$DESKTOP_BROWSERS)? true: false;
		}
        return $this->_is_desktop_browser;
	}
	/**
	 * @var boolean Robot cache
	 */
	private $_is_robot = null;
	/**
	 * This HTTP Request is from a robot
	 * @return bool
	 */
	public function isRobot() {
		if ($this->_is_robot === null) {
			$this->_is_robot = $this->user_agent->iContains(WurflConstants::$ROBOTS)? true: false;
		}
		return $this->_is_robot;
	}
	
	/**
	 * Adds the headers from $raw_http_headers to the $_http_headers array
	 * @param array $raw_http_headers
	 */
	private function importHeaders($raw_http_headers) {
		$clean_http_headers = $this->cleanHeaders($raw_http_headers);
		$this->_http_headers = array();
		foreach ($clean_http_headers as $name => $value) {
			if (in_array($name, $this->_user_agent_search_order)) {
				$this->_http_headers[$name] = new TeraWurflUserAgent($name, $value);
				continue;
			}
			if (in_array($name, $this->_user_agent_profile_search_order)) {
				$this->_http_headers[$name] = new TeraWurflUserAgentProfile($name, $value);
				continue;
			}
			$this->_http_headers[$name] = new TeraWurflHttpRequestHeader($name, $value);
		}
	}
	
	/**
	 * Finds the correct User Agent Header for WURFL Matching and stores its key
	 */
	private function assignUserAgent() {
		foreach ($this->_user_agent_search_order as $header) {
			if (array_key_exists($header, $this->_http_headers)) {
				$this->_user_agent_key = $header;
				return;
			}
		}
	}
	
	/**
	 * Finds the correct User Agent Profile Header for WURFL Matching and stores its key
	 */
	private function assignUserAgentProfile() {
		foreach ($this->_user_agent_profile_search_order as $header) {
			if (array_key_exists($header, $this->_http_headers)) {
				$this->_user_agent_profile_key = $header;
				return;
			}
		}
		$this->_http_headers['null'] = null;
		$this->_user_agent_profile_key = 'null';
	}
	
	private function cleanHeaders($raw_http_headers) {
		if (!array_key_exists('HTTP_ACCEPT', $raw_http_headers)) {
			$raw_http_headers['HTTP_ACCEPT'] = '';
		}
		if (!array_key_exists('HTTP_USER_AGENT', $raw_http_headers)) {
			$raw_http_headers['HTTP_USER_AGENT'] = '';
		}
		return $raw_http_headers;
	}

	public function __get($name) {
		switch ($name) {
			case 'user_agent':
				return $this->_http_headers[$this->_user_agent_key];
				break;
			case 'uaprof':
				return $this->_http_headers[$this->_user_agent_profile_key];
				break;
			case 'accept':
				return $this->_http_headers['HTTP_ACCEPT'];
				break;
		}
		return null;
	}
	
	public function getHeader($name) {
		if (array_key_exists($name, $this->_http_headers)) {
			return $this->_http_headers[$name];
		}
		return null;
	}
	
	public function headerExists($name) {
		return array_key_exists($name, $this->_http_headers);
	}
	
}