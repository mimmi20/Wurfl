<?php
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
 * The server-side Tera-WURFL webservice provider.  Normally used with webservice.php
 * @package TeraWurfl
 *
 */
class TeraWurflWebservice {
	
	/**
	 * Allow clients to query the webservice only from the listed networks. Setting this
	 * variable to false disables the filter and allows connections from ANY client IP.
	 * To allow only certain networks, put them in CIDR notation in an array.  For example,
	 * to allow only the range 172.16.10.0/24 and the single IP 192.168.2.17 you would use
	 * this as the setting:
	 * 
	 * <code>
	 * public static $ALLOWED_CLIENT_IPS = array('172.16.10.0/24','192.168.2.17/32');
	 * </code>
	 * 
	 * NOTE: 127.0.0.1/32 is automatically allowed, however, some clients may use a different
	 * loopback address like 127.1.1.1.  In this case, add 127.0.0.0/8 to your list.
	 * 
	 * Unauthorized attempts to use this webservice are logged to the Tera-WURFL log file
	 * with a severity of LOG_WARNING.
	 * 
	 * @var Mixed
	 */
	public static $ALLOWED_CLIENT_IPS = false;
	
	public static $FORMAT_XML = 'xml';
	public static $FORMAT_JSON = 'json';
	
	/**
	 * Log all errors from the webservice
	 * @var Boolean Enable
	 */
	public $enable_error_log = true;
	/**
	 * Filename of error log
	 * @var String
	 */
	public $error_log_filename = 'webservice_error.log';
	/**
	 * The directory where the error log is stored.  Set to null to use the Tera-WURFL data/ directory
	 * @var String
	 */
	public $error_log_path = null;
	/**
	 * Log all access of the webservice
	 * @var Boolean Enable
	 */
	public $enable_access_log = false;
	/**
	 * Filename of access log
	 * @var String
	 */
	public $access_log_filename = 'webservice_access.log';
	/**
	 * The directory where the access log is stored.  Set to null to use the Tera-WURFL data/ directory
	 * @var String
	 */
	public $access_log_path = null;
	
	protected $format;
	protected $xml;
	protected $json;
	protected $out_cap = array();
	protected $search_results = array();
	protected $out_errors = array();
	protected $userAgent;
	protected $wurflObj;
	protected $flatCapabilities = array();
	
	public function __construct($userAgent,$searchPhrase,$data_format='xml',$teraWurflInstance=null){
		set_exception_handler(array($this,'__handleExceptions'));
		require_once realpath(dirname(__FILE__).'/TeraWurfl.php');
		$this->format = $data_format;
		$this->userAgent = $userAgent;
		if(!is_null($teraWurflInstance)){
			$this->wurflObj =& $teraWurflInstance;
		}else{
			$this->wurflObj = new TeraWurfl();
		}
		if(!$this->isClientAllowed()){
			$this->logError("Denied webservice access to client {$_SERVER['REMOTE_ADDR']}",LOG_WARNING);
			echo "access is denied from ".$_SERVER['REMOTE_ADDR'];
			exit(0);
		}
		if($this->enable_access_log) $this->logAccess();
		$this->wurflObj->getDeviceCapabilitiesFromAgent($this->userAgent);
		$this->flattenCapabilities();
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
	/**
	 * Get the response that would normally be sent to the client.
	 * @return String Response
	 */
	public function getResponse(){
		switch($this->format){
			case self::$FORMAT_JSON:
				return $this->json;
				break;
			default:
			case self::$FORMAT_XML:
				return $this->xml;
				break;
		}
	}
	/**
	 * Send the HTTP Headers for the return data
	 * @return void
	 */
	public function sendHTTPHeaders(){
		header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
		switch($this->format){
			case self::$FORMAT_JSON:
				header("Content-Type: application/json");
				break;
			default:
			case self::$FORMAT_XML:
				header("Content-Type: text/xml");
				break;
		}
	}
	/**
	 * Send the complete response to the client, including the HTTP Headers and the response.
	 * @return void
	 */
	public function sendResponse(){
		$this->sendHTTPHeaders();
		echo $this->getResponse();
	}
	/**
	 * See if a given ip ($ip) is in a given CIDR network ($cidr_range)
	 * @param String CIDR Network (e.g. "192.168.2.0/24")
	 * @param String IP Address
	 * @return Bool IP Address is in CIDR Network
	 */
	public static function ipInCIDRNetwork($cidr_network,$ip){
		// Thanks Bill Grady for posting a *working* IP in CIDR network function!
		// Source: http://billgrady.com/wp/2009/05/21/ip-matching-with-cidr-notation-in-php/
		// Get the base and the bits from the CIDR
		list($base, $bits) = explode('/', $cidr_network);
		if($bits < 8 || $bits > 32){
			throw new Exception("Error: Invalid CIDR mask specified.");
		}
		// Now split it up into it's classes
		list($a, $b, $c, $d) = explode('.', $base);
		// Now do some bit shifting/switching to convert to ints
		$i    = ($a << 24) + ($b << 16) + ( $c << 8 ) + $d;
		$mask = $bits == 0 ? 0: (~0 << (32 - $bits));
		// Here's our lowest int
		$low = $i & $mask;
		// Here's our highest int
		$high = $i | (~$mask & 0xFFFFFFFF);
		// Now split the ip we're checking against up into classes
		list($a, $b, $c, $d) = explode('.', $ip);
		// Now convert the ip we're checking against to an int
		$check = ($a << 24) + ($b << 16) + ( $c << 8 ) + $d;
		// If the ip is within the range, including highest/lowest values,
		// then it's witin the CIDR range
		if ($check >= $low && $check <= $high) return true;
		return false;
	}
	/**
	 * Is the connecting client allowed to use this webservice
	 * @return Bool
	 */
	protected function isClientAllowed(){
		if(!self::$ALLOWED_CLIENT_IPS || $_SERVER['REMOTE_ADDR'] == '127.0.0.1') return true;
		$ip = $_SERVER['REMOTE_ADDR'];
		foreach(self::$ALLOWED_CLIENT_IPS as $cidr_range){
			if(self::ipInCIDRNetwork($cidr_range,$ip)) return true;
		}
		return false;
	}
	/**
	 * Converts PHP variables to an XML friendly string
	 * @param Mixed Value
	 * @return String Value
	 */
	protected function exportValue($in){
		if(is_bool($in))return var_export($in,true);
		if(is_null($in) || !isset($in))return '';
		return $in;
	}
	/**
	 * Add an error to the errors array that will be sent in the response
	 * @param String Capability name that is in error
	 * @param String Description of the error
	 * @return void
	 */
	protected function addError($name,$desc){
		if($this->enable_error_log) $this->logError("Client ".$_SERVER['REMOTE_ADDR']." requested an invalid capability: $name",LOG_WARNING);
		$this->out_errors[] = array('name'=>$name,'desc'=>$desc);
	}
	/**
	 * Search through all the capabilities and place the requested ones in search_results to
	 * be sent in the response.
	 * @param String Search phrase (e.g. "is_wireless_device|streaming|tera_wurfl")
	 * @return void
	 */
	protected function search($searchPhrase){
		if (!empty($searchPhrase)){
			$capabilities = explode('|',$_REQUEST['search']);
			foreach($capabilities as $cap){
				$cap = strtolower($cap);
				$cap = preg_replace('/[^a-z0-9_\- ]/','',$cap);
				// Individual Capability
				if(array_key_exists($cap,$this->flatCapabilities)){
					$this->search_results[$cap] = $this->flatCapabilities[$cap];
					continue;
				}
				// Group
				if(array_key_exists($cap,$this->wurflObj->capabilities) && is_array($this->wurflObj->capabilities[$cap])){
					foreach($this->wurflObj->capabilities[$cap] as $group_cap => $value){
						$this->search_results[$group_cap] = $value;
					}
					continue;
				}
				$this->addError($cap,"The group or capability is not valid.");
				$this->search_results[$cap] = null;
			}
		}else{
			$this->search_results = $this->flatCapabilities;
		}
	}
	/**
	 * Flatten the multi-tiered capabilities array into a list of capabilities.
	 * @return void
	 */
	protected function flattenCapabilities(){
		$this->flatCapabilities = array();
		foreach($this->wurflObj->capabilities as $key => $value){
			if(is_array($value)){
				foreach($value as $subkey => $subvalue){
					$this->flatCapabilities[$subkey] = $subvalue;
				}
			}else{
				$this->flatCapabilities[$key] = $value;
			}
		}
	}
	/**
	 * Generate the XML response
	 * @return void
	 */
	protected function generateXML(){
		$this->xml = '<?xml version="1.0" encoding="iso-8859-1"?>'."\n";
		$this->xml .= "<TeraWURFLQuery>\n";
		$this->xml .= sprintf("\t".'<device apiVersion="%s" mtime="%s" useragent="%s" id="%s">'."\n",
			$this->wurflObj->release_version,
			$this->wurflObj->getSetting(TeraWurfl::$SETTING_LOADED_DATE),
			str_replace('&','&amp;',$this->wurflObj->capabilities['user_agent']),
			$this->wurflObj->capabilities['id']
		);
		foreach( $this->search_results as $cap_name => $value){
			$value = $this->exportValue($value);
			$value = str_replace('&','&amp;',$value);
			$this->xml .= "\t\t<capability name=\"$cap_name\" value=\"$value\"/>\n";
		}
		$this->xml .= "\t</device>\n";
		$this->xml .= $this->generateXMLErrors();
		$this->xml .= "</TeraWURFLQuery>";
	}
	/**
	 * Generate JSON response
	 * @return void
	 */
	protected function generateJSON(){
		$data = array(
			'apiVersion'	=> $this->wurflObj->release_version,
			'mtime'			=> $this->wurflObj->getSetting(TeraWurfl::$SETTING_LOADED_DATE),
			'useragent'		=> $this->wurflObj->capabilities['user_agent'],
			'id'			=> $this->wurflObj->capabilities['id'],
			'capabilities'	=> $this->search_results,
			'errors'		=> $this->out_errors,
		);
		$this->json = json_encode($data);
		unset($data);
	}
	/**
	 * Generate the errors section of the XML response
	 * @return String XML errors section
	 */
	protected function generateXMLErrors(){
		$xml = '';
		if(count($this->out_errors)==0){
			$xml .= "\t<errors/>\n";
		}else{
			$xml .= "\t<errors>\n";
			foreach($this->out_errors as $error){
				$xml .= "\t\t<error name=\"{$error['name']}\" description=\"{$error['desc']}\"/>\n";
			}
			$xml .= "\t</errors>\n";
		}
		return $xml;
	}
	/**
	 * Log this access with the IP of the requestor and the user agent
	 */
	protected function logAccess(){
		$_textToLog = sprintf('%s [%s %s][%s] %s',
			date('r'),
			php_uname('n'),
			getmypid(),
			$_SERVER['REMOTE_ADDR'],
			$this->userAgent
		)."\n";
		$path = is_null($this->access_log_path)? dirname(__FILE__).'/'.TeraWurflConfig::$DATADIR: $this->access_log_path.'/';
		$logfile = $path.$this->access_log_filename;
		@file_put_contents($logfile,$_textToLog,FILE_APPEND);
	}
/**
	 * Log an error in the TeraWurflWebservice log file
	 * @param String The error message text
`	 * @param Int The log level / severity of the error
	 * @param String The function or code that was being run when the error occured
	 * @return void
	 */
	protected function logError($text, $requestedLogLevel=LOG_NOTICE, $func="TeraWurflWebservice"){
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
		$_textToLog = date('r')." [".php_uname('n')." ".getmypid()."]"."[$func] ".$warn_banner . $text . "\n";
		$path = is_null($this->access_log_path)? dirname(__FILE__).'/'.TeraWurflConfig::$DATADIR: $this->access_log_path.'/';
		$logfile = $path.$this->error_log_filename;
		@file_put_contents($logfile,$_textToLog,FILE_APPEND);
	}
	public function __handleExceptions(Exception $exception){
		$this->logError($exception->getMessage(),LOG_ERR);
	}
}