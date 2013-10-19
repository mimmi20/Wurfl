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

/**
 * Include Exceptions
 */
require_once realpath(dirname(__FILE__).'/TeraWurflExceptions/TeraWurflExceptions.php');

if (!class_exists('TeraWurflConfig',false)) {
	@include_once realpath(dirname(__FILE__).'/TeraWurflConfig.php');
	if (!class_exists('TeraWurflConfig',false)) {
		throw new TeraWurflException("Unable to load configuration file TeraWurflConfig.php.  Please create the config file, you can use TeraWurflConfig.php.example as a starting point.");
	}
}
/**#@+
 * Include required files
 */
require_once realpath(dirname(__FILE__).'/DatabaseConnectors/TeraWurflDatabase.php');
require_once realpath(dirname(__FILE__).'/WurflConstants.php');
require_once realpath(dirname(__FILE__).'/TeraWurflUtils/TeraWurflLoader.php');
require_once realpath(dirname(__FILE__).'/UserAgentMatcherFactory.php');
require_once realpath(dirname(__FILE__).'/UserAgentUtils.php');
require_once realpath(dirname(__FILE__).'/WurflSupport.php');
require_once realpath(dirname(__FILE__).'/UserAgentMatchers/UserAgentMatcher.php');
require_once realpath(dirname(__FILE__).'/HttpHeaderNormalizers/IHttpHeaderNormalizer.php');
require_once realpath(dirname(__FILE__).'/HttpHeaderNormalizers/GenericUserAgentNormalizer.php');
require_once realpath(dirname(__FILE__).'/TeraWurflHttpRequest/TeraWurflHttpRequest.php');
require_once realpath(dirname(__FILE__).'/VirtualCapability/VirtualCapabilityProvider.php');
/**#@-*/
/**
 * The main Tera-WURFL Class, provides all end-user methods and properties for interacting
 * with Tera-WURFL
 * 
 * @package TeraWurfl
 */
class TeraWurfl{

    /**#@+
     * @var string Settings constants
     */
	public static $SETTING_WURFL_VERSION = 'wurfl_version';
	public static $SETTING_WURFL_DATE = 'wurfl_date';
	public static $SETTING_LOADED_DATE = 'loaded_date';
	public static $SETTING_PATCHES_LOADED = 'patches_loaded';
    /**#@-*/

	/**
	 * Array of errors that were encountered while processing the request
	 * @var Array
	 */
	public $errors;
	/**
	 * Array of WURFL capabilities of the requested device
	 * @var Array
	 */
	public $capabilities;
	/**
	 * Flattened version of the capabilities for high-speed access
	 * @var Array
	 */
	protected $flat_capabilities;
	/**
	 * Database connector to be used, must extend TeraWurflDatabase.  All database functions are performed
	 * in the database connector through its methods and properties.
	 * @see TeraWurflDatabase
	 * @see TeraWurflDatabase_MySQL5
	 * @var TeraWurflDatabase
	 */
	public $db = false;
	/**
	 * The directory that TeraWurfl.php is in
	 * @var string
	 */
	public $rootdir;
	/**
	 * The incoming HTTP Request
	 * @var TeraWurflHttpRequest
	 */
	public $httpRequest;
	/**
	 * The UserAgentMatcher that is currently in use
	 * @var UserAgentMatcher
	 */
	public $userAgentMatcher;
	/**
	 * Was the evaluated device found in the cache
	 * @var boolean
	 */
	public $foundInCache;
	
	/**
	 * The installed branch of Tera-WURFL
	 * @var string
	 */
	public $release_branch = "Stable";
	/**
	 * The installed version of Tera-WURFL
	 * @var string
	 */
	public $release_version = "1.5.0.1";
	public $historical_release_version = "2.2.1";
	/**
	 * The required version of PHP for this release
	 * @var string
	 */
	public static $required_php_version = "5.1.0";
	
	/**
	 * Lookup start time
	 * @var int
	 */
	protected $lookup_start;
	/**
	 * Lookup end time
	 * @var int
	 */
	protected $lookup_end;
	/**
	 * The array key that is returned as a WURFL capability group in the capabilities
	 * array that stored Tera-WURFL specific information about the request
	 * @var string
	 */
	protected $matchDataKey = "tera_wurfl";
	/**
	 * The Tera-WURFL specific data that is added to the capabilities array
	 * @var array
	 */
	public $matchData;
	/**
	 * Array of UserAgentMatchers and match attempt types that the API used to find a matching device
	 * @var Array
	 */
	protected $matcherHistory;
	/**
	 * This keeps the device fallback lookup from running away.
	 * The deepest device I've seen is sonyericsson_z520a_subr3c at 15
	 * @var int
	 */
	protected $maxDeviceDepth = 40;
	
	/**
	 * @var VirtualCapabilityProvider
	 */
	public $virtual_cap_provider;
	
	/**
     * Instatiate a new TeraWurfl object
     */
	public function __construct() {
		$this->errors = array();
		$this->capabilities = array();
		$this->flat_capabilities = array();
		$this->matcherHistory = array();
		$this->rootdir = dirname(__FILE__).'/';
		$dbconnector = 'TeraWurflDatabase_'.TeraWurflConfig::$DB_CONNECTOR;
		if ($this->db === false) $this->db = new $dbconnector;
		if (!$this->db->connect()) {
			throw new TeraWurflDatabaseException("Cannot connect to database: ".$this->db->getLastError());
		}
	}
	
	/**
	 * Detects the capabilities of a device from a given user agent and optionally, the HTTP Accept Headers
	 * @param string $userAgent HTTP User Agent
	 * @param string $httpAccept HTTP Accept Header
	 * @return boolean matching device was found
	 */
	public function getDeviceCapabilitiesFromAgent($userAgent=null, $httpAccept=null) {
		if ($userAgent === null && $httpAccept === null) {
			return $this->getDeviceCapabilitiesFromRequest();
		}
		$request_headers = array(
			'HTTP_USER_AGENT' => $userAgent,
			'HTTP_ACCEPT' => $httpAccept,
		);
		return $this->getDeviceCapabilitiesFromRequest($request_headers);
	}
	/**
	 * Detects the capabilities from a given request object ($_SERVER)
	 * @param array $request_headers Request object ($_SERVER contains this data)
	 * @return boolean Match
	 */
	public function getDeviceCapabilitiesFromRequest($request_headers=null) {
		$this->httpRequest = ($request_headers instanceof TeraWurflHttpRequest)? $request_headers: new TeraWurflHttpRequest($request_headers);
		$this->db->numQueries = 0;
		$this->matchData = array(
			"num_queries" => 0,
			"match_type" => '',
			"matcher" => '',
			"match"	=> false,
			"lookup_time" => 0,
		);
		$this->lookup_start = microtime(true);
		$this->foundInCache = false;
		$this->capabilities = array();
		$this->flat_capabilities = array();
		// Use the ultra high performance SimpleDesktopMatcher if enabled
		if (TeraWurflConfig::$SIMPLE_DESKTOP_ENGINE_ENABLE) {
			require_once realpath(dirname(__FILE__).'/UserAgentMatchers/SimpleDesktopUserAgentMatcher.php');
			if (SimpleDesktopUserAgentMatcher::isDesktopBrowserHeavyDutyAnalysis($this->httpRequest)) {
				$this->httpRequest->user_agent->set(WurflConstants::SIMPLE_DESKTOP_UA);
			}
		}
		// Check cache for device
		if (TeraWurflConfig::$CACHE_ENABLE) {
			$cacheData = $this->db->getDeviceFromCache($this->httpRequest->user_agent->cleaned);
			// Found in cache
			if ($cacheData !== false) {
				$this->capabilities = $cacheData;
				$this->foundInCache = true;
				$deviceID = $cacheData['id'];
			}
		}
		$this->virtual_cap_provider = new VirtualCapabilityProvider($this);
		if (!$this->foundInCache) {
			require_once realpath(dirname(__FILE__).'/UserAgentMatchers/SimpleDesktopUserAgentMatcher.php');
			// Find appropriate user agent matcher
			$this->userAgentMatcher = UserAgentMatcherFactory::createUserAgentMatcher($this, $this->httpRequest);
			// Find the best matching WURFL ID
			$deviceID = $this->getDeviceIDFromRequestLoose();
			// Get the capabilities of this device and all its ancestors
			$this->getFullCapabilities($deviceID);
			// Now add in the Tera-WURFL results array
			$this->lookup_end = microtime(true);
			$this->matchData['num_queries'] = $this->db->numQueries;
			$this->matchData['lookup_time'] = $this->lookup_end - $this->lookup_start;
			// Add the match data to the capabilities array so it gets cached
			$this->addCapabilities(array($this->matchDataKey => $this->matchData));
		}
		if (TeraWurflConfig::$CACHE_ENABLE==true && !$this->foundInCache) {
			// Since this device was not cached, cache it now.
			$this->db->saveDeviceInCache($this->httpRequest->user_agent->cleaned, $this->capabilities);
		}
		$this->flattenCapabilities();
		return $this->capabilities[$this->matchDataKey]['match'];
	}
	/**
	 * Builds the full capabilities array from the WURFL ID
	 * @param string $deviceID WURFL ID
	 */
	public function getFullCapabilities($deviceID) {
		$this->capabilities = array();
		$this->flat_capabilities = null;
		if ($this->virtual_cap_provider === null) {
			$this->virtual_cap_provider = new VirtualCapabilityProvider($this);
		}
		if (is_null($deviceID)) {
			$matcher = $this->userAgentMatcher? get_class($this->userAgentMatcher): '[none]';
			throw new Exception("Invalid Device ID: ".var_export($deviceID, true)."\nMatcher: $matcher\nUser Agent: ".$this->httpRequest->user_agent);
		}
		$this->matchData['actual_root_device'] = '';
		$this->matchData['fall_back_tree'] = '';
		// Now get all the devices in the fallback tree
		$fallbackIDs = array();
		if ($deviceID != WurflConstants::NO_MATCH && $this->db->db_implements_fallback) {
			$fallbackTree = $this->db->getDeviceFallBackTree($deviceID);
			if (!is_array($fallbackTree) || empty($fallbackTree)) {
				throw new Exception("Device has an invalid fall back tree: $deviceID");
			}
			$this->addTopLevelSettings($fallbackTree[0]);
			$fallbackTree = array_reverse($fallbackTree);
			foreach ($fallbackTree as $dev) {
				if (!is_array($dev)) throw new TeraWurflException("Invalid device in fallback tree for $deviceID");
				$fallbackIDs[] = $dev['id'];
				if (isset($dev['actual_device_root']) && $dev['actual_device_root'])$this->matchData['actual_root_device'] = $dev['id'];
				$this->addCapabilities($dev);
			}
			$this->matchData['fall_back_tree'] = implode(',',array_reverse($fallbackIDs));
		}else{
			$fallbackTree = array();
			$childDevice = $this->db->getDeviceFromID($deviceID);
			$fallbackTree[] = $childDevice;
			$fallbackIDs[] = $childDevice['id'];
			$currentDevice = $childDevice;
			$i=0;
			/**
			 * This loop starts with the best-matched device, and follows its fall_back until it reaches the GENERIC device
			 * Lets use "tmobile_shadow_ver1" for an example:
			 * 
			 * 'id' => 'tmobile_shadow_ver1', 'fall_back' => 'ms_mobile_browser_ver1'
			 * 'id' => 'ms_mobile_browser_ver1', 'fall_back' => 'generic_xhtml'
			 * 'id' => 'generic_xhtml', 'fall_back' => 'generic'
			 * 'id' => 'generic', 'fall_back' => 'root'
			 * 
			 * This fallback_tree in this example contains 4 elements in the order shown above.
			 * 
			 */
			while ($currentDevice['fall_back'] != "root") {
				$currentDevice = $this->db->getDeviceFromID($currentDevice['fall_back']);
				if (in_array($currentDevice['id'],$fallbackIDs)) {
					// The device we just looked up is already in the list, which means that
					// we are going to enter an infinate loop if we don't break from it.
					$this->toLog("The device we just looked up is already in the list, which means that we are going to enter an infinate loop if we don't break from it. DeviceID: $deviceID, FallbackIDs: [".implode(',',$fallbackIDs)."]",LOG_ERR);
					throw new Exception("Killed script to prevent infinate loop.  See log for details.");
					break;
				}
				if (!isset($currentDevice['fall_back']) || $currentDevice['fall_back'] == '') {
					$this->toLog("Empty fall_back detected. DeviceID: $deviceID, FallbackIDs: [".implode(',',$fallbackIDs)."]",LOG_ERR);
					throw new Exception("Empty fall_back detected.  See log for details.");
				}
				$fallbackTree[] = $currentDevice;
				$fallbackIDs[] = $currentDevice['id'];
				$i++;
				if ($i > $this->maxDeviceDepth) {
					$this->toLog("Exceeded maxDeviceDepth while trying to build capabilities for device. DeviceID: $deviceID, FallbackIDs: [".implode(',',$fallbackIDs)."]",LOG_ERR);
					throw new Exception("Killed script to prevent infinate loop.  See log for details.");
					break;
				}
			}
			$this->matchData['fall_back_tree'] = implode(',',$fallbackIDs);
			if ($fallbackTree[count($fallbackTree)-1]['id'] != WurflConstants::NO_MATCH) {
				// The device we are looking up cannot be traced back to the GENERIC device
				// and will likely not contain the correct capabilities
				$this->toLog("The device we are looking up cannot be traced back to the GENERIC device and will likely not contain the correct capabilities. DeviceID: $deviceID, FallbackIDs: [".implode(',',$fallbackIDs)."]",LOG_ERR);
			}
			/**
			 * Merge the device capabilities from the parent (GENERIC) to the child (DeviceID)
			 * We merge in this order because the GENERIC device contains all the properties that can be set
			 * Then the next child modifies them, then the next child, and the next child, etc... 
			 */
			while (count($fallbackTree)>0) {
				$dev = array_pop($fallbackTree);
				// actual_root_device is the most accurate device in the fallback tree that is a "real" device, not a sub version or generic
				if (isset($dev['actual_device_root']) && $dev['actual_device_root'])$this->matchData['actual_root_device'] = $dev['id'];
				$this->addCapabilities($dev);
			}
			$this->addTopLevelSettings($childDevice);
		}
	}
	/**
	 * Returns the value of the requested capability for the detected device
	 * @param string $capability Capability name (e.g. "is_wireless_device")
	 * @return integer|string|boolean|null Capability value
	 */
	public function getDeviceCapability($capability) {
		if ($this->flat_capabilities === null) $this->flattenCapabilities();
		if (!array_key_exists($capability, $this->flat_capabilities)) {
			if ($this->virtual_cap_provider->exists($capability)) {
				// This is a virtual capability, not a real one
				return $this->virtual_cap_provider->get($capability);
			}
			$this->toLog('I could not find the requested capability ('.$capability.'), returning NULL', LOG_WARNING);
			return null;
		}
		return $this->flat_capabilities[$capability];
	}
	/**
	 * Gets an array of all the available capability names, not including virtual capabilities.
	 * Only available after looking up at least one device
	 * @return array
	 */
	public function getLoadedCapabilityNames() {
		if ($this->flat_capabilities === null) $this->flattenCapabilities();
		return array_keys($this->flat_capabilities);
	}
	/**
	 * Returns the value of the given setting name
	 * @param string $key Setting value
     * @return string Value
	 */
	public function getSetting($key) {
		return $this->db->getSetting($key);
	}
    /**
     * Full name of the table in use in the current UserAgentMatcher
     * @return string
     */
	public function fullTableName() {
		return TeraWurflConfig::$TABLE_PREFIX.'_'.$this->userAgentMatcher->tableSuffix();
	}
	
	/**
	 * Prints the contents of the API's UserAgentMatcher buckets
	 */
	public function dumpBuckets() {
		require_once dirname(__FILE__).'/TeraWurflUtils/TeraWurflIdMap.php';
		$map = new TeraWurflIdMap();
		if (!($this->httpRequest instanceof TeraWurflHttpRequest)) {
			$this->httpRequest = new TeraWurflHttpRequest(array('HTTP_USER_AGENT' => 'debug'));
		}
		
		// Setup WurflNodes
		$wurfl_file = $this->rootdir.TeraWurflConfig::$DATADIR.TeraWurflConfig::$WURFL_FILE;
		$map->load($wurfl_file);
		
		UserAgentMatcherFactory::loadMatchers();
		$matchers = WurflConstants::$matchers;
		sort($matchers);
		foreach ($matchers as $matcher_name) {
			$matcher_class_name = $matcher_name.'UserAgentMatcher';
			/* @var $matcher UserAgentMatcher */
			$matcher = new $matcher_class_name($this);
			$bucket = $matcher->tableSuffix();
			$bucket_data = $this->db->getFullDeviceList(TeraWurflConfig::$TABLE_PREFIX.'_'.$bucket);
			ksort($bucket_data);
			foreach ($bucket_data as $device_id => $user_agent) {
				$original_ua = $map->$device_id;
				echo implode("\t", array(
						$bucket,
						$device_id,
						$user_agent,
						$original_ua,
				))."\n";
			}
		}
	}
	
	/**
	 * Log an error in the Tera-WURFL log file
	 * @see TeraWurflConfig
	 * @param string $text The error message text
	 * @param integer $requestedLogLevel The log level / severity of the error
	 * @param string $func The function or code that was being run when the error occurred
	 */
	public function toLog($text, $requestedLogLevel=LOG_NOTICE, $func="Tera-WURFL") {
		if ($requestedLogLevel == LOG_ERR) $this->errors[] = $text;
		if (TeraWurflConfig::$LOG_LEVEL == 0 || ($requestedLogLevel-1) >= TeraWurflConfig::$LOG_LEVEL ) {
			return;
		}
		if ( $requestedLogLevel == LOG_ERR ) {
			$warn_banner = 'ERROR: ';
		} else if ( $requestedLogLevel == LOG_WARNING ) {
			$warn_banner = 'WARNING: ';
		} else {
			$warn_banner = '';
		}
		$_textToLog = date('r')." [".php_uname('n')." ".getmypid()."]"."[$func] ".$warn_banner . $text;
		$logfile = $this->rootdir.TeraWurflConfig::$DATADIR.TeraWurflConfig::$LOG_FILE;
		$_logFP = fopen($logfile, "a+");
		fputs($_logFP, $_textToLog."\n");
		fclose($_logFP);
	}
	/**
	 * Adds the top level properties to the capabilities array, like id and user_agent
	 * @param array $newCapabilities New properties to be added
	 */
	public function addTopLevelSettings($newCapabilities) {
		if (!is_array($newCapabilities)) return;
		foreach ($newCapabilities as $key => $val) {
			if (is_array($val))continue;
			$this->capabilities[$key] = $val;
		}
	}
	/**
	 * Add new capabilities to the capabilities array
	 * @param array $newCapabilities Capabilities that are to be added
	 */
	public function addCapabilities(Array $newCapabilities) {
		self::mergeCapabilities($this->capabilities, $newCapabilities);
	}
	/**
	 * Returns the matching WURFL ID for a given User Agent
	 * @return string WURFL ID
	 */
	protected function getDeviceIDFromRequestLoose() {
		$this->matcherHistory = array();
		// Return generic UA if userAgent is empty
		if (strlen($this->httpRequest->user_agent)==0) {
			$this->matchData['matcher'] = "none"; 
			$this->matchData['match_type'] = "none";
			$this->matchData['match'] = false;
			$this->setMatcherHistory();
			if ($this->httpRequest->uaprof instanceof TeraWurflUserAgentProfile && $this->httpRequest->uaprof->containsValidUrl()) {
				return WurflConstants::GENERIC_MOBILE;
			} else {
				return WurflConstants::NO_MATCH;
			}
		}
		
		// Check for exact match
		if (TeraWurflConfig::$SIMPLE_DESKTOP_ENGINE_ENABLE && $this->httpRequest->user_agent == WurflConstants::SIMPLE_DESKTOP_UA) {
			// SimpleDesktop UA Matching avoids querying the database here
			$this->matchData['matcher'] = $this->userAgentMatcher->matcherName();
			$this->matchData['match_type'] = "high_performance";
			$this->matchData['match'] = true;
			$this->matcherHistory[] = $this->matchData['matcher'] . "(high_performance)";
			$this->setMatcherHistory();
			return WurflConstants::GENERIC_WEB_BROWSER;
		}else{
			$deviceID = $this->db->getDeviceFromUA($this->httpRequest->user_agent->normalized);
		}
		$this->matcherHistory[] = $this->userAgentMatcher->matcherName() . "(exact)";
		if ($deviceID !== false) {
			$this->matchData['matcher'] = $this->userAgentMatcher->matcherName();
			$this->matchData['match_type'] = "exact";
			$this->matchData['match'] = true;
			$this->setMatcherHistory();
			return $deviceID;
		}
		// Check for a conclusive match
		$deviceID = $this->userAgentMatcher->applyConclusiveMatch($this->httpRequest);
		$this->matcherHistory[] = $this->userAgentMatcher->matcherName() . "(conclusive)";
		if ($deviceID != WurflConstants::NO_MATCH) {
			$this->matchData['matcher'] = $this->userAgentMatcher->matcherName();
			$this->matchData['match_type'] = "conclusive";
			$this->matchData['match'] = true;
			$this->setMatcherHistory();
			return $deviceID;
		}
		/*
		// Check for Vodafone magic
		if ($this->userAgentMatcher->matcherName()!="VodafoneUserAgentMatcher" && $this->httpRequest->user_agent->contains("Vodafone")) {
			@require_once realpath(dirname(__FILE__).'/UserAgentMatchers/VodafoneUserAgentMatcher.php');
			$vodafoneUserAgentMatcher = new VodafoneUserAgentMatcher($this);
			$this->matcherHistory[] = $vodafoneUserAgentMatcher->matcherName() . "(conclusive)";
			$deviceID = $vodafoneUserAgentMatcher->applyConclusiveMatch($this->httpRequest);
			if ($deviceID != WurflConstants::NO_MATCH) {
				$this->matchData['matcher'] = $vodafoneUserAgentMatcher->matcherName();
				$this->matchData['match_type'] = "conclusive";
				$this->matchData['match'] = true;
				$this->setMatcherHistory();
				return $deviceID;
			}
		}
		*/
		// Check for recovery match
		$deviceID = $this->userAgentMatcher->applyRecoveryMatch($this->httpRequest);
		$this->matcherHistory[] = $this->userAgentMatcher->matcherName() . "(recovery)";
		if ($deviceID != WurflConstants::NO_MATCH) {
			$this->matchData['matcher'] = $this->userAgentMatcher->matcherName();
			$this->matchData['match_type'] = "recovery";
			$this->matchData['match'] = false;
			$this->setMatcherHistory();
			return $deviceID;
		}
		// Check CatchAll if it's not already in use
		if ($this->userAgentMatcher->matcherName()!="CatchAllUserAgentMatcher") {
			$catchAllUserAgentMatcher = new CatchAllUserAgentMatcher($this);
			$this->matcherHistory[] = $catchAllUserAgentMatcher->matcherName() . "(recovery)";
			$deviceID = $catchAllUserAgentMatcher->applyRecoveryMatch($this->httpRequest);
			if ($deviceID != WurflConstants::NO_MATCH) {
				// The CatchAll matcher is intelligent enough to determine the match properties
				$this->matchData['matcher'] = $catchAllUserAgentMatcher->matcher;
				$this->matchData['match_type'] = $catchAllUserAgentMatcher->match_type;
				$this->matchData['match'] = $catchAllUserAgentMatcher->match;
				$this->setMatcherHistory();
				return $deviceID;
			}
		}
		
		// A matching device still hasn't been found - check HTTP ACCEPT headers
		if ($this->httpRequest->accept->length() > 0) {
			$this->matcherHistory[] = 'http_accept';
			if ($this->httpRequest->accept->contains('application/vnd.wap.xhtml+xml')) {
				$this->matchData['matcher'] = 'http_accept';
				$this->matchData['match_type'] = 'recovery';
				// This isn't really a match, it's a suggestion
				$this->matchData['match'] = false;
				$this->setMatcherHistory();
				return WurflConstants::GENERIC_MOBILE;
			}
		}
		$this->matchData['matcher'] = "none";
		$this->matchData['match_type'] = "none";
		$this->matchData['match'] = false;
		$this->setMatcherHistory();
		
		if ($this->httpRequest->uaprof instanceof TeraWurflUserAgentProfile && $this->httpRequest->uaprof->containsValidUrl()) return WurflConstants::GENERIC_MOBILE;
		if ($this->httpRequest->isMobileBrowser()) return WurflConstants::GENERIC_MOBILE;
		if ($this->httpRequest->isSmartTV()) return WurflConstants::GENERIC_SMARTTV;
		return WurflConstants::GENERIC;
	}
	/**
	 * Combines the MatcherHistory array into a string and stores it in the matchData
	 */
	protected function setMatcherHistory() {
		$this->matchData['matcher_history'] = implode(',',$this->matcherHistory);
	}
	/**
	 * Merges given $addedDevice array onto $baseDevice array
	 * @param array $baseDevice Main capabilities array
	 * @param array $addedDevice New capabilities array
	 */
	public static function mergeCapabilities(Array &$baseDevice, Array $addedDevice) {
		if (count($baseDevice) == 0) {
			// Base device is empty
			$baseDevice = $addedDevice;
			return;
		}
		foreach ($addedDevice as $levOneKey => $levOneVal) {
			// Check if the base device has defined this value yet
			if (!is_array($levOneVal)) {
				// This is top level setting, not a capability
				continue;
			}else{
				if (!array_key_exists($levOneKey,$baseDevice))$baseDevice[$levOneKey]=array();
				// This is an array value, merge the contents
				foreach ($levOneVal as $levTwoKey => $levTwoVal) {
					// This is just a scalar value, apply it
					$baseDevice[$levOneKey][$levTwoKey] = $levTwoVal;
					continue;
				}
			}
		}
	}
	
	/**
	 * Returns the value of the requested capability for the detected device
	 * @param string $capability Capability name (e.g. "is_wireless_device")
	 * @return integer|string|boolean|null Capability value
	 * @throws TeraWurflInvalidCapabilityException Invalid virtual capability
	 * @see VirtualCapability::get()
	 */
	public function getVirtualCapability($capability) {
		if (!$this->virtual_cap_provider->exists($capability)) {
			throw new TeraWurflInvalidCapabilityException("The virtual capability $capability does not exist");
		}
		return $this->virtual_cap_provider->get($capability);
	}
	
	/**
	 * Gets an array of all the virtual capabilities
	 * @return array Virtual capabilities in format "name => value"
	 * @see VirtualCapability::getAll()
	 */
	public function getAllVirtualCapabilities() {
		return $this->virtual_cap_provider->getAll();
	}
	
	public function __get($capability) {
		return $this->getDeviceCapability($capability);
	}
	
	/**
	 * Get the absolute path to the data directory on the filesystem
	 * @return string Absolute path to data directory
	 */
	public static function absoluteDataDir() {
		return dirname(__FILE__).'/'.TeraWurflConfig::$DATADIR;
	}
	
	/**
	 * Flattens the capabilities array for fast access
	 */
	protected function flattenCapabilities() {
		$this->flat_capabilities = array();
		foreach ($this->capabilities as $group_name => $group) {
			if (is_array($group)) {
				foreach ($group as $cap_name => $cap) {
					$this->flat_capabilities[$cap_name] = $cap;
				}
			} else {
				$this->flat_capabilities[$group_name] = $group;
			}
		}
	}
}
