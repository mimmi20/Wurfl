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
 * @package    WURFL_XMLParser
 * @copyright  ScientiaMobile, Inc.
 * @author     Steve Kamerman <steve AT scientiamobile.com>
 * @license    GNU Affero General Public License
 * @version    $id$
 */
/**
 * Abstract class to provide a skeleton for the wurfl.xml parsers.
 * @abstract
 * @package TeraWurflXMLParser
 */
abstract class TeraWurflXMLParser {

    /**#@+
     * @var string File types
     */
	public static $TYPE_WURFL = 'wurfl';
	public static $TYPE_PATCH = 'patch';
    /**#@-*/

    /**
     * @var string Version string from wurfl.xml
     */
	public $wurflVersion;
    /**
     * @var string Last updated string from wurfl.xml
     */
	public $wurflLastUpdated;
    /**
     * @var array Array of devices
     */
	public $devices = array();
    /**
     * @var array Array of errors
     */
	public $errors = array();

    /**#@+
     * @var string Parser types
     */
	protected static $PARSER_SIMPLEXML = 'simplexml';
	protected static $PARSER_XMLREADER = 'xmlreader';
	/**#@-*/

    /**
     * @var string Parser type
     */
	protected $parser_type;
    /**
     * @var string File type
     */
	protected $file_type;
    /**
     * @var string XML Data
     */
	protected $xml;

    /**
     * Instantiates an XML Parser
     */
    public function __construct() {

    }
    /**
     * Opens the given $filename for processing
     * @param string $filename
     * @param string $file_type
     */
	abstract public function open($filename,$file_type);
    /**
     * Processes the XML data into the given $destination array
     * @param array $destination
     */
	abstract public function process(Array &$destination);
    /**
     * Cleans values from the XML data into native PHP datatypes
     * @param string $value
     * @return bool|float|int|string|null
     */
	protected function cleanValue($value){
		if($value === 'true') return true;
		if($value === 'false')return false;
		// Clean Numeric values by loosely comparing the (float) to the (string)
		$numval = (float)$value;
		if(strcmp($value,$numval)==0)$value=$numval;
		return $value;
	}
    /**
     * True if capability is enabled in the configuration, and therefore should be loaded
     * @param string $cap_or_group
     * @return bool
     */
	protected function enabled($cap_or_group){
		return in_array($cap_or_group,TeraWurflConfig::$CAPABILITY_FILTER);
	}

    /**
     * Creates and returns an XML Parser that is appropriate for this system
     * @throws Exception No suitable XML Parser found
     * @return TeraWurflXMLParser
     */
	final public static function getInstance(){
		if(class_exists('XMLReader',false)){
			require_once realpath(dirname(__FILE__).'/TeraWurflXMLParser_XMLReader.php');
			return new TeraWurflXMLParser_XMLReader();
		}elseif(function_exists('simplexml_load_file')){
			require_once realpath(dirname(__FILE__).'/TeraWurflXMLParser_SimpleXML.php');
			return new TeraWurflXMLParser_SimpleXML();
		}else{
			throw new Exception("No suitable XML Parser was found.  Please enable XMLReader or SimpleXML");
		}
	}
}








