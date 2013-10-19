<?php
/**
 * Copyright (c) 2013 ScientiaMobile, Inc.
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

class InFuzeProvider {
	
	protected $prefix;
	protected $data = array();
	protected $settings = array();
	
	public function __construct($raw_data) {
		$this->setDataFromRaw($raw_data);
	}
	
	public function getCapabilities() {
		return $this->data;
	}
	
	public function getSettings() {
		return $this->settings;
	}
	
	protected function setDataFromRaw($raw_data) {
		foreach ($raw_data as $key => $val) {
			if (strpos($key, $this->prefix) === 0) {
				$this->data[strtolower(substr($key, strlen($this->prefix)))] = $this->cleanValue($val);
			}
		}
		$this->settings['user_agent'] = $this->data['useragent'];
		$this->settings['actual_device_root'] = $this->cleanValue(strtolower($this->data['isdevroot']));
		$this->settings['id'] = $this->data['id'];
		$this->settings['actual_root_device'] = $this->data['rootid'];
		
		unset($this->data['useragent'], $this->data['isdevroot'], $this->data['id'], $this->data['rootid']);
	}
	
	protected function cleanValue($value){
		if($value === 'true') return true;
		if($value === 'false')return false;
		// Clean Numeric values by loosely comparing the (float) to the (string)
		$numval = (float)$value;
		if(strcmp($value,$numval)==0)$value=$numval;
		return $value;
	}
	
	protected function toPhpUpper($key) {
		return ($key == null)? $key: strtoupper(str_replace('-', '_', $key));
	}
}

class InFuzeProvider_Environment extends InFuzeProvider {
	
	public function __construct($prefix='WURFL_', $raw_data=null) {
		$this->prefix = $this->toPhpUpper($prefix);
		parent::__construct($raw_data? $raw_data: $_SERVER);
	}
}

class InFuzeProvider_HttpHeaders extends InFuzeProvider {
	
	public function __construct($prefix='x-wurfl-') {
		$this->prefix = 'HTTP_'.$this->toPhpUpper($prefix);
		parent::__construct($_SERVER);
	}
}

class TeraWurfl_Mock {
	
	public $capabilities;
	public $release_version = 'InFuze';
	
	public function __construct($inFuzeProvider) {
		$this->capabilities = $inFuzeProvider->getCapabilities() + $inFuzeProvider->getSettings();
	}
	
	public function getSetting($key) {
		switch ($key) {
			case 'loaded_date':
				return time();
				break;
		}
		return null;
	}
	
	public function getDeviceCapabilitiesFromAgent($userAgent) {}
}

if (!class_exists('TeraWurflConfig', false)) {
	class TeraWurflConfig {
		public static $DATADIR = 'data/';
		public static $LOG_FILE = 'wurfl.log';
		public static $LOG_LEVEL = LOG_WARNING;
	}
}

if (!class_exists('TeraWurfl', false)) {
	class TeraWurfl extends TeraWurfl_Mock {
		public static $SETTING_WURFL_VERSION = 'wurfl_version';
		public static $SETTING_WURFL_DATE = 'wurfl_date';
		public static $SETTING_LOADED_DATE = 'loaded_date';
		public static $SETTING_PATCHES_LOADED = 'patches_loaded';
	}
}

/**
 * The server-side Tera-WURFL-compatible webservice for use with WURFL InFuze.
* @package TeraWurfl
*
*/
class TeraWurflInFuzeProxy extends TeraWurflWebservice {
	
	/**
	 * Instantiates a new TeraWurflWebservice
	 * @param string $userAgent User Agent
	 * @param string $searchPhrase Search phrase
	 * @param string $data_format String
	 * @param InFuzeProvider $inFuzeProvider Instance of an InFuzeProvider to use for detection
	*/
	public function __construct($userAgent, $searchPhrase, $data_format='xml', $inFuzeProvider){
		set_exception_handler(array($this,'__handleExceptions'));
		$this->format = $data_format;
		$this->userAgent = $userAgent;
		$this->wurflObj = new TeraWurfl_Mock($inFuzeProvider);
		$this->flatCapabilities = $this->wurflObj->capabilities;
		if (!$this->isClientAllowed()) {
			$this->logError("Denied webservice access to client {$_SERVER['REMOTE_ADDR']}",LOG_WARNING);
			echo "access is denied from ".$_SERVER['REMOTE_ADDR'];
			exit(0);
		}
		if($this->enable_access_log) $this->logAccess();
		$this->search($searchPhrase);
		switch($this->format){
			case self::$FORMAT_JSON:
				$this->generateJSON();
				break;
			default:
			case self::$FORMAT_XML:
				$this->generateXML();
				break;
		}
	}
	
	protected function search($searchPhrase) {
		if (!empty($searchPhrase)) {
			$capabilities = explode('|',$_REQUEST['search']);
			foreach ($capabilities as $cap) {
				$cap = strtolower($cap);
				$cap = preg_replace('/[^a-z0-9_\- ]/','',$cap);
				// Individual Capability
				if (array_key_exists($cap,$this->flatCapabilities)) {
					$this->search_results[$cap] = $this->flatCapabilities[$cap];
				} else {
					$this->addError($cap,"The group or capability is not valid.");
					$this->search_results[$cap] = null;
				}
			}
		} else {
			$this->search_results = $this->flatCapabilities;
		}
	}
}
