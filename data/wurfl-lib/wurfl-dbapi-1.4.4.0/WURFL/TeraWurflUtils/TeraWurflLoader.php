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
require_once realpath(dirname(__FILE__).'/../TeraWurflXMLParsers/TeraWurflXMLParser.php');
require_once realpath(dirname(__FILE__).'/../TeraWurflXMLParsers/TeraWurflXMLParser_XMLReader.php');
require_once realpath(dirname(__FILE__).'/../TeraWurflXMLParsers/TeraWurflXMLParser_SimpleXML.php');
/**
 * Loads the WURFL file from a local file or remote URL into the Tera-WURFL database.
 * @package TeraWurfl
 *
 */
class TeraWurflLoader{

	/**
	 * @var string WURFL Type Flag
	 */
	public static $WURFL_LOCAL = "local";
	/**
	 * @var string WURFL Type Flag
	 */
	public static $WURFL_REMOTE = "remote";
	/**
	 * @var string WURFL Type Flag
	 */
	public static $WURFL_PATCH = "patch";
	/**
	 * @var bool Reload existing cache during rebuild
	 */
	public static $PRESERVE_CACHE = true;
	/**
	 * @var string Temp prefix for loading data
	 */
	public static $TEMP_PREFIX = '_TMPLOAD_';

	// Properties
	/**
	* @var array Errors encountered during load
	*/
	public $errors;
	/**
	 * @var string Version string from wurfl.xml
	 */
	public $version;
	/**
	 * @var string Last updated string tom wurfl.xml
	 */
	public $last_updated;

	/**
	 * @var string Table name
	 */
	protected $table;
	/**
	 * @var string Filename of wurfl.xml
	 */
	protected $file;
	/**
	 * @var TeraWurfl Instance of TeraWurfl
	 */
	protected $wurfl;
	/**
	 * @var array Array of devices to be loaded
	 */
	protected $devices;
	/**
	 * @var array Array of tables to be used
	 */
	protected $tables;
	/**
	 * @var TeraWurflXMLParser XML Parser
	 */
	protected $parser;
	/**
	 * @var string Table prefix
	 */
	protected $table_prefix;
	/**
	 * @var string Temp Table Prefix
	 */
	protected $temp_table_prefix;
	/**
	 * @var int Count of devices
	 */
	public $mainDevices = 0;
	/**
	 * @var int Count of added devices in patch
	 */
	public $patchAddedDevices = 0;
	/**
	 * @var int Count of merged devices in patch
	 */
	public $patchMergedDevices = 0;

	/**#@+
	 * @var int Performance tracking variable
	 */
	protected $timestart;
	protected $timevalidate;
	protected $timesort;
	protected $timepatch;
	protected $timedatabase;
	protected $timecache;
	protected $timeend;
	/**#@-*/

	// Constructor
	/**
	* Instantiates a new TeraWurflLoader
	* @param TeraWurfl $wurfl
	*/
	public function __construct(TeraWurfl &$wurfl){
		$this->errors = array();
		$this->wurfl = $wurfl;
		$this->devices = array();
		$this->tables = array();
		$this->file = TeraWurfl::absoluteDataDir().TeraWurflConfig::$WURFL_FILE;
		$this->parser = TeraWurflXMLParser::getInstance();
		$this->table_prefix = TeraWurflConfig::$TABLE_PREFIX;
		// Use the original prefix with a suffix so it doesn't break installations
		// that require a certain table prefix
		$this->temp_table_prefix = TeraWurflConfig::$TABLE_PREFIX . self::$TEMP_PREFIX;
	}

	// Public Methods
	/**
	* Loads the WURFL and patch files into the database
	* @return bool Success
	*/
	public function load(){
		$this->wurfl->toLog("Loading WURFL",LOG_INFO);
		if(!is_readable($this->file)){
			$this->wurfl->toLog("The main WURFL file could not be opened: ".$this->file,LOG_ERR);
			$this->errors[]="The main WURFL file could not be opened: ".$this->file;
			return false;
		}
		/******* TEMP TABLE PREFIX ON **********/
		if ($this->wurfl->db->db_implements_atomic_rename === true) $this->enableTempPrefix();
		$this->timestart = microtime(true);
		// Parse XML data into $this->devices array
		$this->parser->open($this->file, TeraWurflXMLParser::$TYPE_WURFL);
		$this->parser->process($this->devices);
		$this->mainDevices = count($this->devices);
		$this->version = $this->parser->wurflVersion;
		$this->last_updated = $this->parser->wurflLastUpdated;
		$this->wurfl->toLog("Loading Patches",LOG_INFO);
		if(!$this->loadPatches()) return false;
		$this->wurfl->toLog("Validating WURFL Data",LOG_INFO);
		if(!$this->validate()) return false;
		$this->wurfl->toLog("Sorting WURFL Data",LOG_INFO);
		if(!$this->sort()) return false;
		$this->wurfl->toLog("Loading data into DB",LOG_INFO);
		if(!$this->loadIntoDB()) return false;
		if ($this->wurfl->db->db_implements_atomic_rename === true) {
			$this->disableTempPrefix();
			$this->wurfl->db->atomicRenameAll($this->temp_table_prefix, $this->table_prefix);
		}
		/******* TEMP TABLE PREFIX OFF **********/
		$this->timecache = microtime(true);
		if(self::$PRESERVE_CACHE){
			$this->wurfl->toLog("Rebuilding cache",LOG_INFO);
			$this->wurfl->db->rebuildCacheTable();
		}else{
			$this->wurfl->db->createCacheTable();
		}
		$this->timeend = microtime(true);
		$this->wurfl->db->updateSetting(TeraWurfl::$SETTING_PATCHES_LOADED,TeraWurflConfig::$PATCH_FILE);
		$this->wurfl->db->updateSetting(TeraWurfl::$SETTING_WURFL_VERSION,$this->version);
		$this->wurfl->db->updateSetting(TeraWurfl::$SETTING_WURFL_DATE,$this->last_updated);
		$this->wurfl->db->updateSetting(TeraWurfl::$SETTING_LOADED_DATE,time());
		$this->wurfl->toLog("Finished loading WURFL {$this->version} ({$this->last_updated}) in ".round($this->totalLoadTime(),2)." seconds",LOG_WARNING);
		return true;
	}
	/**
	 * Validates the data from the WURFL file or Patch file
	 * @return bool Valid
	 */
	public function validate(){
		$this->timevalidate = microtime(true);
		$before_errors = count($this->errors);
		foreach($this->devices as $id => &$device){
			if(!$id == "generic"){
				// Must have a valid wurfl ID
				if(strlen($id)==0){
					$this->wurfl->toLog("Skipping WURFL entry (invalid ID):\n".var_export($device,true),LOG_WARNING);
					$this->errors[] = "Skipping WURFL entry (invalid ID):\n".var_export($device,true);
					continue;
				}
				// Must have a valid User Agent unless it's "generic"
				if(strlen($device['user_agent'])==0){
					$this->wurfl->toLog("Skipping WURFL entry (invalid User Agent):\n".var_export($device,true),LOG_WARNING);
					$this->errors[] = "Skipping WURFL entry (invalid User Agent):\n".var_export($device,true);
					continue;
				}
				// Must have a valid fall_back
				if(!$this->validID($device['fall_back'])){
					$this->wurfl->toLog("Invalid Fallback '".$device['fall_back']."':\n".var_export($device,true),LOG_WARNING);
					$this->errors[] = "Invalid Fallback '".$device['fall_back']."':\n".var_export($device,true);
					continue;
				}
			}
		}
		return ($before_errors == count($this->errors));
	}
	/**
	 * Sorts the validated data from $this->devices into their respective UserAgentMatcher tables ($this->tables)
	 * based on the UserAgentMatcher that matches the device's user agent
	 * @return bool Success
	 */
	public function sort(){
		$this->timesort = microtime(true);
		foreach($this->devices as $id => &$device){
			$this->wurfl->httpRequest = new TeraWurflHttpRequest(array('HTTP_USER_AGENT' => $device['user_agent']));
			// This will return something like "Nokia", "Motorola", or "CatchAll"
			$matcher = UserAgentMatcherFactory::userAgentType($this->wurfl, $this->wurfl->httpRequest);
			// TeraWurfl_Nokia
			$device['user_agent'] = $this->wurfl->httpRequest->user_agent->normalized;
			$uatable = TeraWurflConfig::$TABLE_PREFIX.'_'.$matcher;
			if(!isset($this->tables[$uatable]))$this->tables[$uatable]=array();
			$this->tables[$uatable][$device['id']]=$device;
		}
		// Destroy the devices array
		$this->devices = array();
		return true;
	}
	/**
	 * Loads the WURFL devices into the database.
	 * @return bool Completed without error
	 */
	public function loadIntoDB(){
		$this->timedatabase = microtime(true);
		if($this->wurfl->db->loadDevices($this->tables)){
			return true;
		}else{
			$this->errors = array_merge($this->errors,$this->wurfl->db->errors);
			return false;
		}
	}
	/**
	 * Loads the patch files from TeraWurflConfig::PATCH_FILE
	 * @return bool Success
	 */
	public function loadPatches(){
		if(!TeraWurflConfig::$PATCH_ENABLE) return true;
		$this->timepatch = microtime(true);
		// Explode the patchfile string into an array of patch files (normally just one file)
		$patches = explode(';',TeraWurflConfig::$PATCH_FILE);
		foreach($patches as $patch){
			$patch_devices = array();
			$this->wurfl->toLog("Loading patch: ".$patch,LOG_WARNING);
			$patch_parser = TeraWurflXMLParser::getInstance();
			$patch_parser->open(TeraWurfl::absoluteDataDir().$patch, TeraWurflXMLParser::$TYPE_PATCH);
			$patch_parser->process($patch_devices);
			foreach($patch_devices as $id => &$device){
				// if the fall_back is blank, or equal to it's id, or the fall_back ID doesn't exist in the device table or this patch file,
				// then it will cause the API to fail.  We'll skip this device and report the error.  There is also the possibility that the
				// device has a valid fallback in a patch file that hasn't been prosessed yet, but this is not worth trying to detect.
				if(!isset($device['fall_back']) || $id == $device['fall_back']
				|| (!$this->validID($device['fall_back']) && !in_array($device['fall_back'], $patch_devices))) {
					$this->errors[] = "The device '$id' from patch file '$patch' has an invalid fall_back device ID and has been skipped.";
					$this->wurfl->toLog("The device '$id' from patch file '$patch' has an invalid fall_back device ID and has been skipped.", LOG_WARNING);
					continue;
				}
				if($this->validID($id)){
					// Merge this device on top of the existing device
					TeraWurfl::mergeCapabilities($this->devices[$id],$device);
					$this->patchMergedDevices++;
				}else{
					// Add this new device to the table
					$this->devices[$id] = $device;
					$this->patchAddedDevices++;
				}
			}
			unset($this->parser);
		}
		return true;
	}
	/**
	 * @return string Class name of XML Parser in use
	 */
	public function getParserName(){
		return get_class(TeraWurflXMLParser::getInstance());
	}

	/**#@+
	 * Get performance information
	 * @return int Duration in seconds
	 */
	public function totalLoadTime(){
		return ($this->timeend - $this->timestart);
	}
	public function parseTime(){
		return ($this->timepatch - $this->timestart);
	}
	public function patchTime(){
		return ($this->timevalidate - $this->timepatch);
	}
	public function validateTime(){
		return ($this->timesort - $this->timevalidate);
	}
	public function sortTime(){
		return ($this->timedatabase - $this->timesort);
	}
	public function databaseTime(){
		return ($this->timecache - $this->timedatabase);
	}
	public function cacheRebuildTime(){
		return ($this->timeend - $this->timecache);
	}
	/**#@-*/

	/**
	 * Enables the Temp table prefix
	 */
	protected function enableTempPrefix() {
		TeraWurflConfig::$TABLE_PREFIX = $this->temp_table_prefix;
	}

	/**
	 * Disables the Temp table prefix
	 */
	protected function disableTempPrefix() {
		TeraWurflConfig::$TABLE_PREFIX = $this->table_prefix;
	}

	/**
	 * Is WURFL Device ID Valid?
	 * @param string $id WURFL ID
	 * @return bool
	 */
	protected function validID($id){
		if(strlen($id)==0) return false;
		return ($id == 'root' || array_key_exists($id,$this->devices));
	}
}