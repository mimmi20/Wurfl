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
 * Loads the wurfl.xml file using SimpleXML
 * @package TeraWurflXMLParser
 */
class TeraWurflXMLParser_SimpleXML extends TeraWurflXMLParser {

	public function __construct(){
		if(function_exists('simplexml_load_file')){
			$this->parser_type = self::$PARSER_SIMPLEXML;
		}else{
			throw new Exception("Cannot load SimpleXML");
		}
	}
	
	public function open($filename,$file_type){
		$this->file_type = $file_type;
		if(function_exists('libxml_use_internal_errors')){
			// Use advanced logging from libXML
			//TODO: Figure out why LibXML doesn't properly report "out of memory" errors
			//      when "libxml_use_internal_errors(true);".  The errors are accounted
			//      for, but their ::message property is null.
			//libxml_use_internal_errors(true);
			$this->xml = simplexml_load_file($filename);
			if (!$this->xml) {
				$errors = libxml_get_errors();
				foreach ($errors as $error) {
					$type = '';
					switch ($error->level) {
						case LIBXML_ERR_WARNING:
							$type = "Warning";
							break;
						case LIBXML_ERR_ERROR:
							$type = "Error";
							break;
						case LIBXML_ERR_FATAL:
							$type = "Fatal Error";
							break;
					}
					$this->errors[] = "$type: " . trim($error->message);
				}
				libxml_clear_errors();
			}
		}else{
			try{
				$this->xml = simplexml_load_file($filename);
			}catch(Exception $ex){}
			if(!$this->xml){
				$this->errors[] = "Error: cannot parse XML file: $filename.";
			}
		}
		if(count($this->errors) > 0){
			throw new Exception("SimpleXML reported the following errors:\n".implode("\n",$this->errors));
		}
	}
	public function process(Array &$destination){
		$this->devices =& $destination;
		if($this->file_type == self::$TYPE_WURFL && isset($this->xml->version)){
			$this->wurflVersion = (string) $this->xml->version->ver;
			$this->wurflLastUpdated = (string) $this->xml->version->last_updated;
		}
		$before_errors = count($this->errors);
		foreach($this->xml->devices->device as $device){
			$this->loadDeviceXMLToArray($device);
		}
	}
	protected function loadDeviceXMLToArray(&$device){
		$id = (string)$device['id'];
		$this->devices[$id] = array('id'=>$id);
		$filtering = (TeraWurflConfig::$CAPABILITY_FILTER)? true:false;
		$includegroup = false;
		if(isset($device['fall_back'])) $this->devices[$id]['fall_back'] = (string)$device['fall_back'];
		if(isset($device['user_agent'])) $this->devices[$id]['user_agent'] = UserAgentUtils::cleanUserAgent((string)$device['user_agent']);
		if(isset($device['actual_device_root'])){
			$this->devices[$id]['actual_device_root'] = (string)$device['actual_device_root'];
			$this->devices[$id]['actual_device_root'] = ($this->devices[$id]['actual_device_root'])?1:0;
		}
		foreach($device->group as $group){
			$groupname = (string)$group['id'];
			if($filtering && $this->enabled($groupname)){
				$includegroup = true;
			}else{
				$includegroup = false;
			}
			$groupdata = array();
			foreach($group->capability as $cap){
				$capname = (string)$cap['name'];
				if(!$filtering || ($filtering && $includegroup) || ($filtering && !$includegroup && $this->enabled($capname))){
					$groupdata[$capname]=$this->cleanValue((string)$cap['value']);
				}
			}
			if(count($groupdata) > 0){
				$this->devices[$id][$groupname] = $groupdata;
			}
			unset($groupdata);
		}
	}
}
