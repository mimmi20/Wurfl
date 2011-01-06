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
 * Loads the wurfl.xml file using the stream-based XMLReader class.
 * This version is a HACK, made only for the purpose of extremely low
 * memory usage, unlike the other XMLParsers, which are designed for
 * speed of updating the database and sanity checking.
 * @package TeraWurflXMLParser
 */
class TeraWurflXMLParser_XMLReader_LowMem extends TeraWurflXMLParser{
	
	private $dbcon;
	private $pendingInsert = array();
	
	public function __construct(){
		if(class_exists('XMLReader')){
			$this->parser_type = self::$PARSER_XMLREADER;
		}else{
			throw new Exception("Cannot load XMLReader");
		}
		$this->dbcon = new mysqli(TeraWurflConfig::$DB_HOST,TeraWurflConfig::$DB_USER,TeraWurflConfig::$DB_PASS,TeraWurflConfig::$DB_SCHEMA);
		$this->xml = new XMLReader();
	}
	
	public function open($filename,$file_type){
		$this->file_type = $file_type;
		$this->xml->open($filename);
		//TODO: add error handling
	}
	public function process(Array &$destination){
		while($this->xml->read()){
			switch ($this->xml->nodeType){
				case XMLReader::ELEMENT:
					if($this->xml->name == "device"){
						$this->parseDevice();
						continue;
					}
					if($this->file_type == self::$TYPE_WURFL){
						if($this->xml->name == "ver" || $this->xml->name == "last_updated"){
							if($this->xml->name == "ver") $this->wurflVersion = $this->getValue();
							if($this->xml->name == "last_updated") $this->wurflLastUpdated = $this->getValue();
						}
					}
					break;
				case XMLReader::END_ELEMENT:
				default:
					break;
			} 
		}
	}
	protected function getValue(){
		$this->xml->read();
		return $this->xml->value;
	}
	protected function parseDevice(){
		if(count($this->pendingInsert)>0){
			foreach($this->pendingInsert as $device){
				$matcher = UserAgentFactory::userAgentType($this->wurfl,$device['user_agent']);
				$uatable = TeraWurflConfig::$TABLE_PREFIX.'_'.$matcher;
				
			}
			$this->pendingInsert = array();
		}
		$this->pendingInsert[$this->xml->getAttribute('id')] = array();
		$device =& $this->pendingInsert[$this->xml->getAttribute('id')];
		$device=array(
			'id' => $this->xml->getAttribute('id'),
			'user_agent' => UserAgentUtils::cleanUserAgent($this->xml->getAttribute('user_agent')),
			'fall_back' => $this->xml->getAttribute('fall_back'),
		);
		if($this->xml->getAttribute('actual_device_root')) $device['actual_device_root'] = ($this->xml->getAttribute('actual_device_root')=="true")?1:0;
		$groupdevice = '';
		$groupname = '';
		$filtering = (TeraWurflConfig::$CAPABILITY_FILTER)? true:false;
		$includegroup = false;
		while($this->xml->read()){
			if($this->xml->nodeType != XMLReader::ELEMENT) continue;
			// recurse back into this function for the rest of the devices
			switch($this->xml->name){
				case "device":
					$this->parseDevice();
					break;
				case "group":
					$groupname = $this->xml->getAttribute('id');
					if($filtering && $this->enabled($this->xml->getAttribute('id'))){
						$includegroup = true;
					}else{
						$includegroup = false;
						continue;
					}
					$device[$groupname] = array();
					break;
				case "capability":
					if(!$filtering || ($filtering && $includegroup)){
						// the groupdevice array must already exist
						$device[$groupname][$this->xml->getAttribute('name')] = self::cleanValue($this->xml->getAttribute('value'));
						continue;
					}
					if($filtering && !$includegroup && $this->enabled($this->xml->getAttribute('name'))){
						// the groupdevice array might already exists
						if(!array_key_exists($groupname,$device)) $device[$groupname] = array();
						$device[$groupname][$this->xml->getAttribute('name')] = self::cleanValue($this->xml->getAttribute('value'));
						continue;
					}
					break;
			}
		}
	}
}








