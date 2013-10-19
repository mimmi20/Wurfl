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
 * @package    WURFL_UserAgentMatcher
 * @copyright  ScientiaMobile, Inc.
 * @author     Steve Kamerman <steve AT scientiamobile.com>
 * @license    GNU Affero General Public License
 * @version    $id$
 */
/**
 * An abstract class that all UserAgentMatchers must extend.
 * @package TeraWurflUserAgentMatchers
 */
abstract class UserAgentMatcher {
	
	/**
	 * @var TeraWurfl Running instance of Tera-WURFL
	 */
	protected $wurfl;
	/**
	 * @var TeraWurflUserAgent
	 */
	protected $userAgent;
	/**
	 * WURFL IDs that are hardcoded in this connector.  Used for compatibility testing against new WURFLs
	 * @var array
	 */
	public static $constantIDs = array();
	/**
	 * @var Array List of WURFL IDs => User Agents.  Typically used for matching user agents.
	 */
	public $deviceList;
	/**
	 * If true, the matcher will not perform any RIS or LD matching
	 * @var boolean
	 */
	public $simulation = false;
	/**
	 * Set to true if this UserAgentMatcher is allowed to permenantly modify the User Agent while matching 
	 * @var boolean
	 */
	public $runtime_normalization = false;
    /**
     * Instantiates a new UserAgentMatcher
     * @param TeraWurfl $wurfl
     */
	public function __construct(TeraWurfl $wurfl) {
		$this->wurfl = $wurfl;
		$this->userAgent = $this->wurfl->httpRequest->user_agent;
	}

    /**
     * Attempts to find a conclusively matching WURFL ID
     * @return string Matching WURFL ID
     */
    abstract public function applyConclusiveMatch();
    
    /**
     * Attempts to find a loosely matching WURFL ID
     * @return string Matching WURFL ID
     */
    public function applyRecoveryMatch() {
        return WurflConstants::NO_MATCH;
    }
    
    /**
     * Returns true if this Matcher can handle the given $httpRequest
     * @param TeraWurflHttpRequest $httpRequest
     * @return boolean
     */
    public static function canHandle(TeraWurflHttpRequest $httpRequest) {
    	return true;
    }
    
    /**
     * Updates the deviceList Array to contain all the WURFL IDs that are related to the current UserAgentMatcher
     */
    protected function updateDeviceList() {
    	if(is_array($this->deviceList) && count($this->deviceList)>0) return;
    	$this->deviceList = $this->wurfl->db->getFullDeviceList($this->wurfl->fullTableName());
    }
    /**
     * Attempts to match given user agent string to a device from the database by comparing less and less of the strings until a match is found (RIS, Reduction in String)
     * @param int $tolerance Tolerance, how many characters must match from left to right
     * @return string WURFL ID
     */
    public function risMatch($tolerance) {
    	if ($this->simulation) return WurflConstants::NO_MATCH;
    	if ($this->wurfl->db->db_implements_ris) {
    		return $this->wurfl->db->getDeviceFromUA_RIS($this->userAgent->normalized, $tolerance, $this);
    	}
    	$this->updateDeviceList();
    	return UserAgentUtils::risMatch($this->userAgent->normalized, $tolerance, $this);
    }
    
    /**
     * Uses RIS to match the given User Agent $prefix, using the string length of the $prefix as the tolerance.  Returns device ID $default if a match is not found.
     * @param string $prefix The substring of the desired user agent, ex: "Mozilla/5"
     * @param string $default This device ID will be returned if the match fails
     */
    public function risMatchUAPrefix($prefix, $default=WurflConstants::NO_MATCH) {
    	$tolerance = strlen($prefix);
    	$deviceID = $this->risMatch($tolerance);
    	if ($deviceID == WurflConstants::NO_MATCH) {
    		return $default;
    	}
    	return $deviceID;
    }
    /**
     * Attempts to match given user agent string to a device from the database by calculating their Levenshtein Distance (LD)
     * @param int $tolerance Tolerance, how much difference is allowed
     * @return string WURFL ID
     */
    public function ldMatch($tolerance=null) {
    	if ($this->simulation) return WurflConstants::NO_MATCH;
    	if ($this->wurfl->db->db_implements_ld) {
    		return $this->wurfl->db->getDeviceFromUA_LD($this->userAgent->normalized, $tolerance, $this);
    	}
    	$this->updateDeviceList();
    	return UserAgentUtils::ldMatch($this->userAgent->normalized, $tolerance, $this);
    }
    /**
     * Returns the name of the UserAgentMatcher in use
     * @return string UserAgentMatcher name
     */
    public function matcherName(){
    	return get_class($this);
    }
    /**
     * Returns the database table suffix for the current UserAgentMatcher
     * @return string Table suffix
     */
    public function tableSuffix(){
    	$cname = $this->matcherName();
    	return substr($cname, 0, strpos($cname, 'UserAgentMatcher'));
    }
}
