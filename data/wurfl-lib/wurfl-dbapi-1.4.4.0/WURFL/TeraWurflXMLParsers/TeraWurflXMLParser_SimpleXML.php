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
		if(isset($device['user_agent'])) $this->devices[$id]['user_agent'] = TeraWurflUserAgent::cleanUserAgent((string)$device['user_agent']);
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
