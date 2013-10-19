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
 *
 * @category   WURFL
 * @package	WURFL_VirtualCapability_UserAgentTool
 * @copyright  ScientiaMobile, Inc.
 * @license	GNU Affero General Public License
 * @version	$id$
 */
/**
 * @package WURFL_VirtualCapability_UserAgentTool
 */
class WURFL_VirtualCapability_UserAgentTool_NameVersionPair extends WURFL_VirtualCapability_UserAgentTool_PropertyList {
	public $name;
	public $version;

	protected $regex_matches = array();

	public function set($name=null, $version=null) {
		if ($name !== null) $this->name = trim($name);
		if ($version !== null) $this->version = trim($version);
		return true;
	}

	public function setRegex($regex, $name=null, $version=null) {
		// No need to capture the matches if we're not going to use them
		if (!is_int($name) && !is_int($version)) {
			if (preg_match($regex, $this->device->ua)) {
				$this->name = trim($name);
				$this->version = trim($version);
				return true;
			} else {
				return false;
			}
		} else if (preg_match($regex, $this->device->ua, $this->regex_matches)) {
			if ($name !== null) {
				$this->name = is_int($name)? $this->regex_matches[$name]: $name;
				$this->name = trim($this->name);
			}
			if ($version !== null) {
				//if (!isset($this->regex_matches[$version])) throw new Exception(var_export(array('regex'=>$regex,'matches'=>$this->regex_matches), true));
				$this->version = is_int($version)? $this->regex_matches[$version]: $version;
				$this->version = trim($this->version);
			}
			return true;
		}
		return false;
	}

	public function setContains($needle, $name, $version=null) {
		if (strpos($this->device->ua, $needle) !== false) {
			if ($name !== null) $this->name = trim($name);
			if ($version !== null) $this->version = trim($version);
			return true;
		}
		return false;
	}

	public function getLastRegexMatches() {
		return $this->regex_matches;
	}

	public function getLastRegexMatch($match_number) {
		return $this->regex_matches[$match_number];
	}
}