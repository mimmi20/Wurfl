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
/**
 * An HTTP Request Header
 * @property string $original The header value as it was originally received
 * @property string $cleaned The header value after general cleaning (used for caching)
 * @property string $normalized The header value after UserAgentMatchers have normalized it (used for matching)
 */
class TeraWurflHttpRequestHeader {
	/**
	 * @var string HTTP Header name
	 */
	protected $_name;
	/**
	 * @var string Unmodified value
	 */
	protected $_original;
	/**
	 * @var string Value after general cleaning/normalization (used for caching)
	 */
	protected $_cleaned;
	/**
	 * @var string Value after runtime normalization (used for matching)
	 */
	protected $_normalized;
	/**
	 * The lowercase version of the normalized UA (used for case insensitive matching)
	 * @var string
	 */
	protected $_normalized_lowercase;
	/**
	 * @var string Name of the normalizer that is used for this header
	 */
	protected $_normalizer_name;
	/**
	 * @var IHttpHeaderNormalizer
	 */
	protected $_normalizer;
	/**
	 * @var boolean True if clean() has been run on this header
	 */
	protected $_clean_complete = false;
		
	public function __construct($name, $value) {
		$this->_name = $name;
		$this->_original = (string)$value;
		$this->clean();
	}
	
	public function __get($key) {
		switch ($key) {
			case 'original':
				return $this->_original;
				break;
			case 'cleaned':
				return $this->_cleaned;
				break;
			case 'normalized':
				return $this->_normalized;
				break;
			case 'name':
				return $this->_name;
				break;
		}
		throw new InvalidArgumentException("The property $key does not exist on ".__CLASS__);
	}
	
	public function __set($key, $value) {
		if ($key == 'normalized') {
			$this->set($value);
		} else if ($key = 'cleaned') {
			if ($this->_clean_complete) {
				throw new InvalidArgumentException("Cannot modify the cleaned value after cleaning is complete");
			} else {
				$this->_cleaned = $value;
			}
		} else {
			throw new InvalidArgumentException("Cannot modify the value of a read-only property");
		}
		
	}
	
	public function set($value) {
		$this->_normalized = $value;
		$this->_normalized_lowercase = strtolower($value);
	}
	
	public function __toString() {
		return $this->_normalized;
	}
	
	protected function clean() {
		$this->_cleaned = $this->_original;
		if (isset($this->_normalizer_name)) {
			$this->_normalizer = new $this->_normalizer_name;
			$this->_normalizer->normalize($this);
		}
		$this->set($this->_cleaned);
		$this->_clean_complete = true;
	}
	
	
	public function length() {
		return strlen($this->_normalized);
	}

	/**
	 * Returns true if the value equals $find
	 * @param string $find
	 */
	public function equals($find) {
		return (strcmp($find, $this->_normalized) === 0);
	}
	/**
	 * Returns true if the value equals $find (case insensitive)
	 * @param string $find
	 */
	public function iEquals($find) {
		return (strcmp($find, $this->_normalized_lowercase) === 0);
	}
    /**
     * Check if value contains target string
     * @param string|array $find Target string or array of strings
     * @return bool
     */
    public function contains($find){
    	if(is_array($find)){
    		foreach($find as $part){
    			if(strpos($this->_normalized, $part)!==false){
    				return true;
    			}
    		}
    		return false;
    	}else{
	    	return (strpos($this->_normalized, $find)!==false);
    	}
    }
	/**
     * Check if value contains target string (case-insensitive)
     * @param string|array $find Target string or array of strings
     * @return bool
     */
    public function iContains($find){
    	if(is_array($find)){
    		foreach($find as $part){
    			if(strpos($this->_normalized_lowercase, $part)!==false){
    				return true;
    			}
    		}
    		return false;
    	}else{
	    	return (strpos($this->_normalized_lowercase, $find)!==false);
    	}
    }
    /**
     * Check if value starts with target string
     * @param string|array $find Target string or array of strings
     * @return bool
     */
    public function startsWith($find){
    	if(is_array($find)){
    		foreach($find as $part){
    			if(strpos($this->_normalized, $part)===0){
    				return true;
    			}
    		}
    		return false;
    	}else{
	    	return (strpos($this->_normalized, $find)===0);
    	}
    }
	/**
     * Check if value starts with target string (case-insensitive)
     * @param string|array $find Target string or array of strings
     * @return bool
     */
    public function iStartsWith($find){
    	if(is_array($find)){
    		foreach($find as $part){
    			if(strpos($this->_normalized_lowercase, $part)===0){
    				return true;
    			}
    		}
    		return false;
    	}else{
	    	return (strpos($this->_normalized_lowercase, $find)===0);
    	}
    }
    /**
     * Check if value contains another string using PCRE (Perl Compatible Reqular Expressions)
     * @param string|array $find Target regex string or array of regex strings
     * @return bool
     */
    public function regexContains($find){
	    if(is_array($find)){
    		foreach($find as $part){
    			if(preg_match($part, $this->_normalized)){
    				return true;
    			}
    		}
    		return false;
    	}else{
	    	return (preg_match($find, $this->_normalized));
    	}
    }
	/**
	 * Number of slashes ('/')
	 * @return int Count
	 */
	public function numSlashes() {
		return substr_count($this->_normalized, '/');
	}
	/**
	 * The character position of the first slash.  If there are no slashes, returns string length
	 * @return int Character position
	 */
	public function firstSlash() {
		$position = strpos($this->_normalized, '/');
		return ($position !== false)? $position: strlen($this->_normalized);
	}
	/**
	 * The character position of the second slash.  If there is no second slash, returns string length
	 * @return int Character position
	 */
	public function secondSlash() {
		$first = strpos($this->_normalized, '/');
		$first++;
		$position = strpos($this->_normalized, '/', $first);
		return ($position !== false)? $position: strlen($this->_normalized);
	}
	/**
	 * The character position of the first space.  If there are no spaces, returns string length
	 * @return int Character position
	 */
	public function firstSpace() {
		$position = strpos($this->_normalized, ' ');
		return ($position !== false)? $position: strlen($this->_normalized);
	}
	/**
	 * The character position of the first open parenthisis.  If there are no open parenthisis, returns string length
	 * @return int Character position
	 */
	public function firstOpenParen() {
		$position = strpos($this->_normalized, '(');
		return ($position !== false)? $position: strlen($this->_normalized);
	}
	/**
	 * Returns the character position of the $target string, starting from $startingIndex
	 * @param string $target
	 * @param int $startingIndex
	 */
	public function indexOf($target, $startingIndex=0) {
		return strpos($this->_normalized, $target, $startingIndex);
	}
    /**
     * Returns the character position (index) of the target string, starting from a given index.  If target is not found, returns length of user agent.
     * @param string|array $target Target string to search for, or, Array of Strings to search for
     * @param int $startingIndex Character postition to start looking for the target
     * @return int Character position (index) or full length
     */
	public function indexOfOrLength($target, $startingIndex=0) {
		$length = strlen($this->_normalized);
		if($startingIndex === false) {
			return $length;
		}
		if(is_array($target)){
			foreach($target as $target_n){
				$pos = strpos($this->_normalized, $target_n, $startingIndex);
				if($pos !== false) return $pos;
			}
			return $length;
		}else{
			$pos = strpos($this->_normalized, $target, $startingIndex);
			return ($pos === false)? $length : $pos;
		}
	}
	/**
	 * The character postition of the Nth occurance of the $needle string
	 * @param string $needle Target string to search for
	 * @param int $ordinal The Nth occurence to find
	 * @return int Character position or -1 if $needle is not found $ordinal times
	 */
	public function ordinalIndexOf($needle, $ordinal) {
		if (is_null($this->_normalized) || empty($this->_normalized) || !is_integer($ordinal)){
			return -1;
		}
		$found = 0;
		$index = -1;
		do {
			$index = strpos($this->_normalized, $needle, $index + 1);
			$index = is_int($index)? $index: -1;
			if ($index < 0) {
				return $index;
			}
			$found++;
		} while ($found < $ordinal);
		return $index;
	}
}