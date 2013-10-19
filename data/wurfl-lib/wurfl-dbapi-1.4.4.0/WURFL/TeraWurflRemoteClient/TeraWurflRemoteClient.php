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
 * @package    WURFL_RemoteClient
 * @copyright  ScientiaMobile, Inc.
 * @author     Steve Kamerman <steve AT scientiamobile.com>
 * @license    GNU Affero General Public License
 * @version    $id$
 */
/**
 * Tera-WURFL remote webservice client for PHP
 * @package TeraWurflRemoteClient
 */
class TeraWurflRemoteClient {
	
	/**
	 * XML Data Format - this should only be used to communicate with Tera-WURFL 2.1.1 and older
	 * @var String
	 */
	public static $FORMAT_XML = 'xml';
	/**
	 * The JSON Data Format is the default transport for Tera-WURFL 2.1.2 and newer due to it's smaller size
	 * and better performance with the builtin PHP functions 
	 * @var String
	 */
	public static $FORMAT_JSON = 'json';
	/**
	 * PHP URL Wrapper HTTP call method. If this is disabled on your server, you can use METHOD_CURL instead
	 * @var String
	 */
	public static $METHOD_URL_WRAPPER = 'urlwrap';
	/**
	 * PHP cURL Extension HTTP call method
	 * @var String
	 */
	public static $METHOD_CURL = 'curl';
	/**
	 * If you try to use a capability that has not been retrieved yet and this is set to true,
	 * it will generate another request to the webservice and retrieve this capability automatically.
	 * @var Bool
	 */
	public $autolookup = true;
	/**
	 * Flattened version of Tera-WURFL's capabilities array, containing only capability names and values.
	 * Since it is 'Flattened', there a no groups in this array, just individual capabilities.
	 * @var Array
	 */
	public $capabilities = array();
	/**
	 * Array of errors that were encountered while processing the request and/or response.
	 * @var Array
	 */
	public $errors;
	/**
	 * The HTTP Headers that Tera-WURFL will look through to find the best User Agent, if one is not specified
	 * @var Array
	 */
	public static $userAgentHeaders = array(
		'HTTP_X_DEVICE_USER_AGENT',
		'HTTP_X_ORIGINAL_USER_AGENT',
		'HTTP_X_OPERAMINI_PHONE_UA',
		'HTTP_X_SKYFIRE_PHONE',
		'HTTP_X_BOLT_PHONE_UA',
		'HTTP_USER_AGENT'
	);
	protected $format;
	protected $userAgent;
	protected $webserviceUrl;
	protected $xml;
	protected $json;
	protected $clientVersion = '2.1.4';
	protected $apiVersion;
	protected $loadedDate;
	protected $timeout;
	protected $method;
	
	/**
	 * Creates a TeraWurflRemoteClient object.  NOTE: in Tera-WURFL 2.1.2 the default data format is JSON.
	 * This format is not supported in Tera-WURFL 2.1.1 or earlier, so if you must use this client with 
	 * an earlier version of the server, set the second parameter to TeraWurflRemoteClient::$FORMAT_XML
	 * @param String $TeraWurflWebserviceURL URL to the master Tera-WURFL Server's webservice.php
	 * @param String $data_format TeraWurflRemoteClient::$FORMAT_JSON or TeraWurflRemoteClient::$FORMAT_XML
	 * @param int $timeout Timeout in seconds
	 * @param String $method HTTP Call Method (TeraWurflRemoteClient::$METHOD_URL_WRAPPER or TeraWurflRemoteClient::$METHOD_CURL)
	 */
	public function __construct($TeraWurflWebserviceURL,$data_format='json',$timeout=1,$method='urlwrap'){
		$this->format = $data_format;
		if(!self::validURL($TeraWurflWebserviceURL)){
			throw new Exception("TeraWurflRemoteClient Error: the specified webservice URL is invalid.  Please make sure you pass the full url to Tera-WURFL's webservice.php.");
		}
		$this->capabilities = array();
		$this->errors = array();
		$this->webserviceUrl = $TeraWurflWebserviceURL;
		$this->timeout = $timeout;
		$this->method = $method;
	}
	/**
	 * Get the requested capabilities from Tera-WURFL for the given user agent
	 * @param String $userAgent HTTP User Agent of the device being detected
	 * @param Array $capabilities Array of capabilities that you would like to retrieve
	 * @return bool Success
	 */
	public function getDeviceCapabilitiesFromAgent($userAgent, Array $capabilities){
		$this->userAgent = (is_null($userAgent))? self::getUserAgent(): $userAgent;
		// build request string
		$uri = $this->webserviceUrl . (strpos($this->webserviceUrl,'?')===false?'?':'&') 
		. 'ua=' . urlencode($this->userAgent)
		. '&format=' . $this->format
		. '&search=' . implode('|',$capabilities);
		$this->callTeraWurfl($uri);
		$this->loadCapabilities();
		$this->loadErrors();
		return true;
	}
	/**
	 * Maintains backwards compatibility with Tera-WURFL <= 2.1.2.  This function is an
	 * alias for TeraWurflRemoteClient::getDeviceCapabilitiesFromAgent()
	 * @param String $userAgent HTTP User Agent of the device being detected
	 * @param Array $capabilities Array of capabilities that you would like to retrieve
	 * @return bool Success
	 */
	public function getCapabilitiesFromAgent($userAgent, Array $capabilities){
		return $this->getDeviceCapabilitiesFromAgent($userAgent,$capabilities);
	}
	/**
	 * Returns the value of the requested capability
	 * @param String $capability The WURFL capability you are looking for (e.g. "is_wireless_device")
	 * @return Mixed String, Numeric, Bool
	 */
	public function getDeviceCapability($capability){
		$capability = strtolower($capability);
		if(!array_key_exists($capability, $this->capabilities)){
			if($this->autolookup){
				$this->getDeviceCapabilitiesFromAgent($this->userAgent, array($capability));
				return $this->capabilities[$capability];
			}else{
				return null;
			}
		}
		return $this->capabilities[$capability];
	}
	/**
	 * Get the version of the Tera-WURFL Remote Client (this file)
	 * @return String
	 */
	public function getClientVersion(){
		return $this->clientVersion;
	}
	/**
	 * Get the version of the Tera-WURFL Webservice (webservice.php on server).  This is only available
	 * after a query has been made since it is returned in the response.
	 * @return String
	 */
	public function getAPIVersion(){
		return $this->apiVersion;
	}
	/**
	 * Get the date that the Tera-WURFL was last updated.  This is only available
	 * after a query has been made since it is returned in the response.
	 * @return String
	 */
	public function getLoadedDate(){
		return $this->loadedDate;
	}
	/**
	 * Make the webservice call to the server using the GET method and load the response.
	 * @param String $uri The URI of the master server's webservice.php
	 * @return void
	 */
	protected function callTeraWurfl($uri){
		try{
			// Load raw data
			switch($this->method){
				case self::$METHOD_URL_WRAPPER:
					$return_data = $this->loadURL_URLWrapper($uri);
					break;
				case self::$METHOD_CURL:
					$return_data = $this->loadURL_cURL($uri);
					break;
				default:
					throw new Exception("Invalid HTTP Method specified: ".$this->method);
					break;
			}
			// Process raw data
			switch($this->format){
				case self::$FORMAT_JSON:
					$this->json = json_decode($return_data,true);
					if(is_null($this->json)){
						// Trigger the catch block
						throw new Exception("foo");
					}
					break;
				default:
				case self::$FORMAT_XML:
					if(!$this->xml = simplexml_load_string($return_data)){
						throw new Exception("foo");
					}
					break;
			}
			unset($return_data);
		}catch(Exception $ex){
			// Can't use builtin logging here through Tera-WURFL since it is on the client, not the server
			throw new Exception("TeraWurflRemoteClient Error: Could not query Tera-WURFL master server.");
		}
	}
	/**
	 * Makes the HTTP call to the remote Tera-WURFL Server using PHP URL Wrappers 
	 * @param String $uri URL
     * @return string data
	 */
	protected function loadURL_URLWrapper($uri){
		$context_options = array(
			'http' => array(
				'user_agent' => 'Tera-WURFL/RemoteClient v'.$this->clientVersion,
			)
		);
		if(version_compare(PHP_VERSION, '5.2.1', '>=')){
			$context_options['http']['timeout'] = $this->timeout;
		}
		$context = stream_context_create($context_options);
		return file_get_contents($uri,false,$context);
	}
	/**
	 * Makes the HTTP call to the remote Tera-WURFL Server using the PHP cURL Extension
	 * @param String $uri URL
     * @return string
	 */
	protected function loadURL_cURL($uri){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $uri);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Tera-WURFL/RemoteClient v'.$this->clientVersion);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$return_data = curl_exec($ch);
		curl_close($ch);
		return $return_data;
	}
	/**
	 * Parse the response into the capabilities array
	 * @return void
	 */
	protected function loadCapabilities(){
		switch($this->format){
			case self::$FORMAT_JSON:
				$this->apiVersion = $this->json['apiVersion'];
				$this->loadedDate = $this->json['mtime'];
				$this->capabilities['id'] = $this->json['id'];
				$this->capabilities = array_merge($this->capabilities,$this->json['capabilities']);
				break;
			default:
			case self::$FORMAT_XML:
				$this->apiVersion = $this->xml->device['apiVersion'];
				$this->loadedDate = $this->xml->device['mtime'];
				foreach($this->xml->device->capability as $cap){
					$this->capabilities[(string)$cap['name']] = self::niceCast((string)$cap['value']);
				}
				$this->capabilities['id'] = (string)$this->xml->device['id'];
				break;
		}
	}
	/**
	 * Parse the response's errors into the errors array
	 * @return void
	 */
	protected function loadErrors(){
		switch($this->format){
			case self::$FORMAT_JSON:
				$this->errors &= $this->json['errors'];
				break;
			default:
			case self::$FORMAT_XML:
				foreach($this->xml->errors->error as $error){
					$this->errors[(string)$error['name']]=(string)$error['description'];
				}
				break;
		}
	}
	/**
	 * Cast strings into proper variable types, i.e. 'true' into true
	 * @param string $value
	 * @return String|Bool|Float
	 */
	protected static function niceCast($value){
		// Clean Boolean values
		if($value === 'true')$value=true;
		if($value === 'false')$value=false;
		if(!is_bool($value)){
			// Clean Numeric values by loosely comparing the (float) to the (string)
			$numval = (float)$value;
			if(strcmp($value,$numval)==0)$value=$numval;
		}
		return $value;
	}
	/**
	 * Is the given URL valid
	 * @param string $url
	 * @return Bool
	 */
	protected static function validURL($url){
		if(preg_match('/^(http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/',$url)) return true;
		return false;
	}	
	/**
	 * Return the requesting client's User Agent
	 * @param string $source
	 * @return String
	 */
	public static function getUserAgent($source=null){
		if(is_null($source) || !is_array($source))$source = $_SERVER;
		$userAgent = '';
		if(isset($_GET['UA'])){
			$userAgent = $_GET['UA'];
		}else{
			foreach(self::$userAgentHeaders as $header){
				if(array_key_exists($header,$source) && $source[$header]){
					$userAgent = $source[$header];
					break;
				}
			}
		}
		return $userAgent;
	}
}