<?php
class TeraWurflIdMap {
	
	public $id_map = array();
	
	public function load($wurfl_file) {
		$xml = new XMLReader();
		$xml->open($wurfl_file);
		while ($xml->read()) {
			if ($xml->localName == 'device') {
				$this->id_map[$xml->getAttribute('id')] = $xml->getAttribute('user_agent');
			}
		}
		$xml->close();
	}
	
	public function getIdFromUa($ua) {
		return array_search($ua, $this->id_map) || null;
	}
	
	public function getUaFromId($id) {
		return array_key_exists($id, $this->id_map)? $this->id_map[$id]: null;
	}
	
	public function __get($name) {
		return $this->getUaFromId($name);
	}
}