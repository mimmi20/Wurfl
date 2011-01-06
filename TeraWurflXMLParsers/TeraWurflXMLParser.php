<?php
/**
 * Tera_WURFL - PHP MySQL driven WURFL
 * 
 * Tera-WURFL was written by Steve Kamerman, and is based on the
 * Java WURFL Evolution package by Luca Passani and WURFL PHP Tools by Andrea Trassati.
 * This version uses a MySQL database to store the entire WURFL file, multiple patch
 * files, and a persistent caching mechanism to provide extreme performance increases.
 * 
 * @package TeraWurflXMLParser
 * @author Steve Kamerman <stevekamerman AT gmail.com>
 * @version Stable 2.1.3 $Date: 2010/09/18 15:43:21
 * @license http://www.mozilla.org/MPL/ MPL Vesion 1.1
 */
/**
 * Abstract class to provide a skeleton for the wurfl.xml parsers.
 * @abstract
 * @package TeraWurflXMLParser
 */
abstract class TeraWurflXMLParser {

	public static $TYPE_WURFL = 'wurfl';
	public static $TYPE_PATCH = 'patch';
	
	public $wurflVersion;
	public $wurflLastUpdated;
	public $devices = array();
	public $errors = array();
	
	protected static $PARSER_SIMPLEXML = 'simplexml';
	protected static $PARSER_XMLREADER = 'xmlreader';
	
	protected $parser_type;
	protected $file_type;
	protected $xml;
		
	abstract public function open($filename,$file_type);
	abstract public function process(Array &$destination);
	protected function cleanValue($value){
		if($value === 'true') return true;
		if($value === 'false')return false;
		// Clean Numeric values by loosely comparing the (float) to the (string)
		$numval = (float)$value;
		if(strcmp($value,$numval)==0)$value=$numval;
		return $value;
	}
	protected function enabled($cap_or_group){
		return in_array($cap_or_group,TeraWurflConfig::$CAPABILITY_FILTER);
	}
	
	final public static function getInstance(){
		if(function_exists('simplexml_load_file')){
			require_once realpath(dirname(__FILE__).'/TeraWurflXMLParser_SimpleXML.php');
			return new TeraWurflXMLParser_SimpleXML();
		}elseif(class_exists('XMLReader')){
			require_once realpath(dirname(__FILE__).'/TeraWurflXMLParser_XMLReader.php');
			return new TeraWurflXMLParser_XMLReader();
		}else{
			throw new Exception("No suitable XML Parser was found.  Please enable XMLReader or SimpleXML");
		}
	}
}








