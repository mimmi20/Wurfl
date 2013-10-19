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
class GenericUserAgentNormalizer implements IHttpHeaderNormalizer {
	
	/**
	 * @var TeraWurflUserAgent
	 */
	protected $_user_agent;
	
	public function normalize(TeraWurflHttpRequestHeader $http_header) {
		$this->_user_agent = $http_header;
		$this->_user_agent->cleaned = trim($this->_user_agent->cleaned);
		$this->normalizeUCWEB();
		$this->removeUPLink();
		$this->normalizeSerialNumbers();
		$this->normalizeLocale();
		$this->normalizeBlackberry();
		$this->normalizeAndroid();
		//$this->normalizeEncryptionLevel();
	}
	
	protected function normalizeEncryptionLevel() {
		$this->_user_agent->cleaned = str_replace(' U;', '', $this->_user_agent->cleaned);
	}
	protected function normalizeSerialNumbers() {
		$this->_user_agent->cleaned = preg_replace('/\/SN[\dX]+/', '/SNXXXXXXXXXXXXXXX', $this->_user_agent->cleaned);
		$this->_user_agent->cleaned = preg_replace('/\[(ST|TF|NT)[\dX]+\]/', 'TFXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', $this->_user_agent->cleaned);
	}
	protected function normalizeLocale() {
		//$this->_user_agent->cleaned = preg_replace('/; [a-z]{2}(?:-[a-zA-Z]{0,2})?(?=[;\)])([ ;\)])/', '; xx-xx$1', $this->_user_agent->cleaned);
		$this->_user_agent->cleaned = preg_replace('/; ?[a-z]{2}(?:-[a-zA-Z]{2})?(?:\.utf8|\.big5)?\b-?/', '; xx-xx', $this->_user_agent->cleaned);
	}
	/**
	 * Normalizes Android version numbers
	 */
	protected function normalizeAndroid() {
		$this->_user_agent->cleaned = preg_replace('/(Android)[ \-](\d\.\d)([^; \/\)]+)/', '$1 $2', $this->_user_agent->cleaned);
	}
	/**
	 * Normalizes BlackBerry user agent strings
	 */
	protected function normalizeBlackberry() {
		$ua = $this->_user_agent->cleaned;
		$ua = str_ireplace('blackberry', 'BlackBerry', $ua);
		$pos = strpos($ua, 'BlackBerry');
		if($pos !== false && $pos > 0) $ua = substr($ua, $pos);
		$this->_user_agent->cleaned = $ua;
	}
	/**
	 * Removes UP.Link traces from user agent strings
	 */
	protected function removeUPLink() {
		// Remove the gateway signatures from UA (UP.Link/x.x.x)
		$index = strpos($this->_user_agent->cleaned, 'UP.Link');
		if ($index !== false) {
			// Return the UA up to the UP.Link/xxxxxx part
			$this->_user_agent->cleaned = substr($this->_user_agent->cleaned, 0, $index);
		}
	}
	protected function normalizeUCWEB() {
		// Starts with 'JUC' or 'Mozilla/5.0(Linux;U;Android'
		if (strpos($this->_user_agent->cleaned, 'JUC') === 0 || strpos($this->_user_agent->cleaned, 'Mozilla/5.0(Linux;U;Android') === 0) {
			$this->_user_agent->cleaned = preg_replace('/^(JUC \(Linux; U;)(?= \d)/', '$1 Android', $this->_user_agent->cleaned);
			$this->_user_agent->cleaned = preg_replace('/(Android|JUC|[;\)])(?=[\w|\(])/', '$1 ', $this->_user_agent->cleaned);
		}
	}
	/**
	 * Removes Vodafone garbage from user agent string
	 */
	protected function removeVodafonePrefix() {
		$this->_user_agent->cleaned = preg_replace('/^Vodafone\/(\d\.\d\/)?/', '', $this->_user_agent->cleaned, 1);
	}
}