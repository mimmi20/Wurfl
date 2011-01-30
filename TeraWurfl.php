<?php
declare(ENCODING = 'iso-8859-1');
namespace TeraWurfl;

/**
 * Tera_WURFL - PHP MySQL driven WURFL
 * 
 * Tera-WURFL was written by Steve Kamerman, and is based on the
 * Java WURFL Evolution package by Luca Passani and WURFL PHP Tools by Andrea Trassati.
 * This version uses a MySQL database to store the entire WURFL file, multiple patch
 * files, and a persistent caching mechanism to provide extreme performance increases.
 * 
 * @package TeraWurfl
 * @author Steve Kamerman <stevekamerman AT gmail.com>
 * @version Stable 2.1.3 $Date: 2010/09/18 15:43:21
 * @license http://www.mozilla.org/MPL/ MPL Vesion 1.1
 */

/**
 * The main Tera-WURFL Class, provides all end-user methods and properties for interacting
 * with Tera-WURFL
 * 
 * @package TeraWurfl
 */
class TeraWurfl
{
    public static $SETTING_WURFL_VERSION = 'wurfl_version';
    public static $SETTING_WURFL_DATE = 'wurfl_date';
    public static $SETTING_LOADED_DATE = 'loaded_date';
    public static $SETTING_PATCHES_LOADED = 'patches_loaded';
    
    /**
     * Array of errors that were encountered while processing the request
     * @var array
     */
    private $_errors = array();
    
    /**
     * Array of WURFL capabilities of the requested device
     * @var array
     */
    private $_capabilities = array();
    
    /**
     * Database connector to be used, must extend TeraWurflDatabase.  All database functions are performed
     * in the database connector through its methods and properties.
     * @see TeraWurflDatabase
     * @see TeraWurflDatabase_MySQL5
     * @var TeraWurflDatabase
     */
    private $_db = false;
    
    /**
     * The directory that TeraWurfl.php is in
     * @var String
     */
    private $_rootdir;
    
    /**
     * The user agent that is being evaluated
     * @var String
     */
    private $_userAgent; 
    
    /**
     * The HTTP Accept header that is being evaluated
     * @var String
     */
    private $_httpAccept;
    
    /**
     * The UserAgentMatcher that is currently in use
     * @var UserAgentMatcher
     */
    private $_userAgentMatcher;
    
    /**
     * Was the evaluated device found in the cache
     * @var Bool
     */
    private $_foundInCache;
    
    /**
     * The installed branch of Tera-WURFL
     * @var String
     */
    private $_release_branch = 'Stable';
    
    /**
     * The installed version of Tera-WURFL
     * @var String
     */
    private $_release_version = "2.1.3";
    
    /**
     * The required version of PHP for this release
     * @var String
     */
    private static $_required_php_version = "5.3.0";
    
    /**
     * Lookup start time
     * @var int
     */
    private $_lookup_start;
    
    /**
     * Lookup end time
     * @var int
     */
    private $_lookup_end;
    
    /**
     * The array key that is returned as a WURFL capability group in the capabilities
     * array that stored Tera-WURFL specific information about the request
     * @var String
     */
    private $_matchDataKey = 'tera_wurfl';
    
    /**
     * The Tera-WURFL specific data that is added to the capabilities array
     * @var array
     */
    private $_matchData;
    
    /**
     * Array of UserAgentMatchers and match attempt types that the API used to find a matching device
     * @var Array
     */
    private $_matcherHistory;
    
    /*
     * This keeps the device fallback lookup from running away.
     * The deepest device I've seen is sonyericsson_z520a_subr3c at 15
     */
    private $_maxDeviceDepth = 40;
    
    private $_config  = array();
    private $_helper  = null;
    private $_support = null;
    
    // Constructor
    public function __construct($config = null, $db = null)
    {
        $this->_rootdir = dirname(__FILE__) . '/';
        
        if (is_object($config) && method_exists($config, 'toArray')) {
            $config = $config->toArray();
        } elseif (!is_array($config)) {
            $config = array();
        }
        
        $this->_config  = array_merge(Config::toArray(), $config);
        $this->_support = new Support();
        
        if (is_object($db) && $db instanceof \Doctrine\ORM\EntityManager) {
            $this->_db = $db;
        } else {
            $dbOptions = array(
                'driver'    => $this->_config['type'],
                'user'      => $this->_config['username'],
                'password'  => $this->_config['password'],
                'dbname'    => $this->_config['dbname'],
                'host'      => $this->_config['hostname'] . (isset($db['port']) ? ':' . $db['port'] : '')
            );
            
            if (isset($this->_config['modelpath'])) {
                $modelPath = $this->_config['modelpath'];
            } else {
                $modelPath = 'Model/Entities';
            }
            
            if (isset($this->_config['proxypath'])) {
                $proxyPath = $this->_config['proxypath'];
            } else {
                $proxyPath = 'Model/Proxies';
            }
            
            $dbConfig = new \Doctrine\ORM\Configuration;
            $cache    = new \Doctrine\Common\Cache\ArrayCache;
            $dbConfig->setMetadataCacheImpl($cache);
            $driverImpl = $dbConfig->newDefaultAnnotationDriver(realpath($this->_rootdir . $modelPath));
            $dbConfig->setMetadataDriverImpl($driverImpl);
            $dbConfig->setQueryCacheImpl($cache);
            $dbConfig->setProxyDir(realpath($this->_rootdir . $proxyPath));
            $dbConfig->setProxyNamespace('TeraWurfl\Model\Proxies');
            $dbConfig->setAutoGenerateProxyClasses(true);
            
            $this->_db = \Doctrine\ORM\EntityManager::create($dbOptions, $dbConfig);
        }
    }
    
    /**
     * Detects the capabilities from a given request object ($_SERVER)
     * @param Array Request object ($_SERVER contains this data)
     * @return Bool Match
     */
    public function getDeviceCapabilitiesFromRequest($server)
    {
        if (!isset($server) || !is_array($server)) {
            $server = $_SERVER;
        }
        
        $this->_support = new Support($server);
        
        return $this->getDeviceCapabilitiesFromAgent(
            $this->_support->getUserAgent(), 
            $this->_support->getAcceptHeader()
        );
    }
    
    /**
     * Detects the capabilities of a device from a given user agent and optionally, the HTTP Accept Headers
     * @param String HTTP User Agent
     * @param String HTTP Accept Header
     * @return Bool matching device was found
     */
    public function getDeviceCapabilitiesFromAgent($userAgent = null, $httpAccept = null)
    {
        $this->_matchData = array(
            "num_queries" => 0,
            "actual_root_device" => '',
            "match_type" => '',
            "matcher" => '',
            "match"    => false,
            "lookup_time" => 0,
            "fall_back_tree" => ''
        );
        $this->_lookup_start = microtime(true);
        $this->_foundInCache = false;
        $this->_capabilities = array();
        
        // Define User Agent
        if (is_null($userAgent)) {
            $userAgent = $this->_support->getUserAgent();
        }
        $this->_userAgent = $userAgent;
        
        if (is_null($httpAccept)) {
            $httpAccept = $this->_support->getAcceptHeader();
        }
        $this->_httpAccept = $httpAccept;
        
        if (strlen($this->_userAgent) > 255) {
            $this->_userAgent = substr($this->_userAgent, 0, 255);
        }
        
        $this->_userAgent = $this->_cleanUserAgent($this->_userAgent);
        
        $this->_helper = new UserAgentMatchers\MatcherHelper($this->_userAgent);
        
        // Find appropriate user agent matcher
        $factory = new UserAgentFactory($this, $this->_userAgent);
        $this->_userAgentMatcher = $factory->createUserAgentMatcher();
        
        // Find the best matching WURFL ID
        $deviceID = $this->getDeviceIDFromUALoose($this->_userAgent);
        
        // Get the capabilities of this device and all its ancestors
        $this->getFullCapabilities($deviceID);
        // Now add in the Tera-WURFL results array
        $this->_lookup_end = microtime(true);
        //$this->_matchData['num_queries'] = $this->_db->numQueries;
        $this->_matchData['lookup_time'] = $this->_lookup_end - $this->_lookup_start;
        // Add the match data to the capabilities array so it gets cached
        $this->addCapabilities(array($this->_matchDataKey => $this->_matchData));
        
        return $this->_capabilities[$this->_matchDataKey]['match'];
    }
    
    /**
     * Returns the matching WURFL ID for a given User Agent
     * @return String WURFL ID
     */
    private function getDeviceIDFromUALoose()
    {
        $this->matcherHistory = array();
        
        // Return generic UA if userAgent is empty
        if (strlen($this->_userAgent) == 0) {
            $this->_matchData['matcher']    = "none"; 
            $this->_matchData['match_type'] = "none";
            $this->_matchData['match']      = false;
            
            $this->setMatcherHistory();
            
            return Constants::GENERIC;
        }
        
        /*
        // Check for exact match
        $deviceID = $this->_db->getDeviceFromUA($this->_userAgent);
        
        $this->matcherHistory[] = $this->_userAgentMatcher->matcherName() . "(exact)";
        if($deviceID !== false){
            $this->_matchData['matcher'] = $this->_userAgentMatcher->matcherName();
            $this->_matchData['match_type'] = "exact";
            $this->_matchData['match'] = true;
            $this->setMatcherHistory();
            return $deviceID;
        }
        */
        
        // Check for a conclusive match
        $deviceID = $this->_userAgentMatcher->applyConclusiveMatch();
        $this->matcherHistory[] = $this->_userAgentMatcher->matcherName() . "(conclusive)";
        if($deviceID != Constants::GENERIC){
            $this->_matchData['matcher'] = $this->_userAgentMatcher->matcherName();
            $this->_matchData['match_type'] = "conclusive";
            $this->_matchData['match'] = true;
            $this->setMatcherHistory();
            return $deviceID;
        }
        // Check for Vodafone magic
        if($this->_userAgentMatcher->matcherName()!="Vodafone" && $this->_helper->contains("Vodafone")){
            //@require_once realpath(dirname(__FILE__).'/UserAgentMatchers/VodafoneUserAgentMatcher.php');
            $vodafoneUserAgentMatcher = new UserAgentMatchers\Vodafone($this, $this->_userAgent);
            $this->matcherHistory[] = $vodafoneUserAgentMatcher->matcherName() . "(conclusive)";
            $deviceID = $vodafoneUserAgentMatcher->applyConclusiveMatch();
            if($deviceID != Constants::GENERIC){
                $this->_matchData['matcher'] = $vodafoneUserAgentMatcher->matcherName();
                $this->_matchData['match_type'] = "conclusive";
                $this->_matchData['match'] = true;
                $this->setMatcherHistory();
                return $deviceID;
            }
        }
        // Check for recovery match
        $deviceID = $this->_userAgentMatcher->applyRecoveryMatch();
        $this->matcherHistory[] = $this->_userAgentMatcher->matcherName() . "(recovery)";
        if($deviceID != Constants::GENERIC){
            $this->_matchData['matcher'] = $this->_userAgentMatcher->matcherName();
            $this->_matchData['match_type'] = "recovery";
            $this->_matchData['match'] = false;
            $this->setMatcherHistory();
            return $deviceID;
        }
        // Check CatchAll if it's not already in use
        if($this->_userAgentMatcher->matcherName()!="CatchAll"){
            $catchAllUserAgentMatcher = new UserAgentMatchers\CatchAll($this, $this->_userAgent);
            $this->matcherHistory[] = $catchAllUserAgentMatcher->matcherName() . "(recovery)";
            $deviceID = $catchAllUserAgentMatcher->applyRecoveryMatch();
            if($deviceID != Constants::GENERIC){
                // The CatchAll matcher is intelligent enough to determine the match properties
                $this->_matchData['matcher'] = $catchAllUserAgentMatcher->matcher;
                $this->_matchData['match_type'] = $catchAllUserAgentMatcher->match_type;
                $this->_matchData['match'] = $catchAllUserAgentMatcher->match;
                $this->setMatcherHistory();
                return $deviceID;
            }
        }
        
        //$support   = new Support();
        //$userAgent = $support->getUserAgent();
        
        
        // A matching device still hasn't been found - check HTTP ACCEPT headers
        if(strlen($this->_httpAccept) > 0){
            $this->matcherHistory[] = "http_accept";
            
            $helper = new UserAgentMatchers\MatcherHelper($this->_httpAccept);
            if($helper->contains(array(
                Constants::$ACCEPT_HEADER_VND_WAP_XHTML_XML,
                Constants::$ACCEPT_HEADER_XHTML_XML,
                Constants::$ACCEPT_HEADER_TEXT_HTML
              ))){
                $this->_matchData['matcher'] = "http_accept";
                $this->_matchData['match_type'] = "recovery";
                // This isn't really a match, it's a suggestion
                $this->_matchData['match'] = false;
                $this->setMatcherHistory();
                return Constants::GENERIC_XHTML;
            }
        }
        $this->_matchData['matcher'] = "none";
        $this->_matchData['match_type'] = "none";
        $this->_matchData['match'] = false;
        $this->setMatcherHistory();
        
        if(UserAgentUtils::isMobileBrowser($this->_userAgent)) return Constants::GENERIC_XHTML;
        return Constants::GENERIC_WEB_BROWSER;
    }
    
    /**
     * Builds the full capabilities array from the WURFL ID
     * @param String WURFL ID
     * @return void
     */
    public function getFullCapabilities($deviceID)
    {
        if(is_null($deviceID)){
            throw new Exception("Invalid Device ID: ".var_export($deviceID,true)."\nMatcher: {$this->_userAgentMatcher->matcherName()}\nUser Agent: ".$this->_userAgent);
            exit(1);
        }
        // Now get all the devices in the fallback tree
        $fallbackIDs = array();
        /*
        if($deviceID != Constants::GENERIC && $this->_db->db_implements_fallback){
            $fallbackTree = $this->_db->getDeviceFallBackTree($deviceID);
            $this->addTopLevelSettings($fallbackTree[0]);
            $fallbackTree = array_reverse($fallbackTree);
            foreach($fallbackTree as $dev){
                $fallbackIDs[] = $dev['id'];
                if(isset($dev['actual_device_root']) && $dev['actual_device_root'])$this->_matchData['actual_root_device'] = $dev['id'];
                $this->addCapabilities($dev);
            }
            $this->_matchData['fall_back_tree'] = implode(',',array_reverse($fallbackIDs));
        }else{
            $fallbackTree = array();
            $childDevice = $this->_db->getDeviceFromID($deviceID);
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
             *
            while($currentDevice['fall_back'] != "root"){
                $currentDevice = $this->_db->getDeviceFromID($currentDevice['fall_back']);
                if(in_array($currentDevice['id'],$fallbackIDs)){
                    // The device we just looked up is already in the list, which means that
                    // we are going to enter an infinate loop if we don't break from it.
                    $this->toLog("The device we just looked up is already in the list, which means that we are going to enter an infinate loop if we don't break from it. DeviceID: $deviceID, FallbackIDs: [".implode(',',$fallbackIDs)."]",LOG_ERR);
                    throw new Exception("Killed script to prevent infinate loop.  See log for details.");
                    break;
                }
                if(!isset($currentDevice['fall_back']) || $currentDevice['fall_back'] == ''){
                    $this->toLog("Empty fall_back detected. DeviceID: $deviceID, FallbackIDs: [".implode(',',$fallbackIDs)."]",LOG_ERR);
                    throw new Exception("Empty fall_back detected.  See log for details.");
                }
                $fallbackTree[] = $currentDevice;
                $fallbackIDs[] = $currentDevice['id'];
                $i++;
                if($i > $this->maxDeviceDepth){
                    $this->toLog("Exceeded maxDeviceDepth while trying to build capabilities for device. DeviceID: $deviceID, FallbackIDs: [".implode(',',$fallbackIDs)."]",LOG_ERR);
                    throw new Exception("Killed script to prevent infinate loop.  See log for details.");
                    break;
                }
            }
            $this->_matchData['fall_back_tree'] = implode(',',$fallbackIDs);
            if($fallbackTree[count($fallbackTree)-1]['id'] != Constants::GENERIC){
                // The device we are looking up cannot be traced back to the GENERIC device
                // and will likely not contain the correct capabilities
                $this->toLog("The device we are looking up cannot be traced back to the GENERIC device and will likely not contain the correct capabilities. DeviceID: $deviceID, FallbackIDs: [".implode(',',$fallbackIDs)."]",LOG_ERR);
            }
            /**
             * Merge the device capabilities from the parent (GENERIC) to the child (DeviceID)
             * We merge in this order because the GENERIC device contains all the properties that can be set
             * Then the next child modifies them, then the next child, and the next child, etc... 
             *
            while(count($fallbackTree)>0){
                $dev = array_pop($fallbackTree);
                // actual_root_device is the most accurate device in the fallback tree that is a "real" device, not a sub version or generic
                if(isset($dev['actual_device_root']) && $dev['actual_device_root'])$this->_matchData['actual_root_device'] = $dev['id'];
                $this->addCapabilities($dev);
            }
            $this->addTopLevelSettings($childDevice);
        }
        */
    }
    /**
     * Returns the value of the requested capability for the detected device
     * @param String Capability name (e.g. "is_wireless_device")
     * @return Mixed Capability value
     */
    public function getDeviceCapability($capability)
    {
        // TODO: Optimize function, one method is to flatten the capabilities array, or create a group=>cap index
        $this->toLog('Searching for '.$capability.' as a capability', LOG_INFO);
        foreach ( $this->_capabilities as $group ) {
            if ( !is_array($group) ) {
                continue;
            }
            while ( list($key, $value)=each($group) ) {
                if ($key==$capability) {
                    $this->toLog('I found it, value is '.$value, LOG_INFO);
                    return $value;
                }
            }
        }
        $this->toLog('I could not find the requested capability ('.$capability.'), returning NULL', LOG_WARNING);
        // since 1.5.2, I can't return "false" because that is a valid value.  Now I return NULL, use is_null() to check
        return null;
    }
    /**
     * Returns the value of the given setting name
     * @param String Setting value
     */
    public function getSetting($key)
    {
        return $this->_db->getSetting($key);
    }
    public function fullTableName()
    {
        return TeraWurflConfig::$TABLE_PREFIX.'_'.$this->_userAgentMatcher->tableSuffix();
    }
    /**
     * Log an error in the Tera-WURFL log file
     * @see TeraWurflConfig
     * @param String The error message text
     * @param Int The log level / severity of the error
     * @param String The function or code that was being run when the error occured
     * @return void
     */
    public function toLog($text, $requestedLogLevel=LOG_NOTICE, $func="Tera-WURFL")
    {
        if($requestedLogLevel == LOG_ERR) $this->errors[] = $text;
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
        if(!is_writeable($logfile)){
            throw new Exception("Tera-WURFL Error: cannot write to log file ($logfile)");
        }
        $_logFP = fopen($logfile, "a+");
        fputs($_logFP, $_textToLog."\n");
        fclose($_logFP);
    }
    /**
     * Adds the top level properties to the capabilities array, like id and user_agent
     * @param Array New properties to be added
     * @return void
     */
    public function addTopLevelSettings(Array $newCapabilities)
    {
        foreach($newCapabilities as $key => $val){
            if(is_array($val))continue;
            $this->_capabilities[$key] = $val;
        }
    }
    /**
     * Add new capabilities to the capabilities array
     * @param Array Capabilities that are to be added
     * @return void
     */
    public function addCapabilities(Array $newCapabilities)
    {
        self::mergeCapabilities($this->_capabilities,$newCapabilities);
    }
    /**
     * Combines the MatcherHistory array into a string and stores it in the matchData
     * @return void
     */
    protected function setMatcherHistory()
    {
        $this->_matchData['matcher_history'] = implode(',',$this->matcherHistory);
    }
    /**
     * Merges given $addedDevice array onto $baseDevice array
     * @param Array Main capabilities array
     * @param Array New capabilities array
     * @return void
     */
    public static function mergeCapabilities(Array &$baseDevice, Array $addedDevice)
    {
        if(count($baseDevice) == 0){
            // Base device is empty
            $baseDevice = $addedDevice;
            return;
        }
        foreach($addedDevice as $levOneKey => $levOneVal){
            // Check if the base device has defined this value yet
            if(!is_array($levOneVal)){
                // This is top level setting, not a capability
                continue;
            }else{
                if(!array_key_exists($levOneKey,$baseDevice))$baseDevice[$levOneKey]=array();
                // This is an array value, merge the contents
                foreach($levOneVal as $levTwoKey => $levTwoVal){
                    // This is just a scalar value, apply it
                    $baseDevice[$levOneKey][$levTwoKey] = $levTwoVal;
                    continue;
                }
            }
        }
    }
    /**
     * Get the absolute path to the data directory on the filesystem
     * @return String Absolute path to data directory
     */
    public static function absoluteDataDir()
    {
        return realpath($this->_config['datadir']);
    }
    
    /**
     * Removes garbage from user agent string
     * @param String User agent
     * @return String User agent
     */
    private function _cleanUserAgent($ua)
    {
        $ua = $this->_removeUPLinkFromUA($ua);
        
        // Remove serial number
        $ua = preg_replace('/\/SN\d{15}/', '/SNXXXXXXXXXXXXXXX', $ua);
        
        // Remove locale identifier
        $ua = preg_replace('/([ ;])[a-zA-Z]{2}-[a-zA-Z]{2}([ ;\)])/', '$1xx-xx$2', $ua);
        $ua = $this->_normalizeBlackberry($ua);
        $ua = rtrim($ua);
        
        return $ua;
    }
    
    /**
     * Normalizes BlackBerry user agent strings
     * @param String User agent
     * @return String User agent
     */
    private function _normalizeBlackberry($ua)
    {
        $pos = strpos($ua,'BlackBerry');
        if($pos !== false && $pos > 0) $ua = substr($ua,$pos);
        return $ua;
    }
    
    /**
     * Removes UP.Link traces from user agent strings
     * @param String User agent
     * @return String User agent
     */
    private function _removeUPLinkFromUA($ua)
    {
        // Remove the gateway signatures from UA (UP.Link/x.x.x)
        $index = strpos($ua, 'UP.Link');
        
        if ($index===false) {
            return $ua;
        } else {
            // Return the UA up to the UP.Link/xxxxxx part
            return substr($ua, 0, $index);
        }
    }
}
