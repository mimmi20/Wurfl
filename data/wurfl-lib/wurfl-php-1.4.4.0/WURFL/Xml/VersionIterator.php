<?php
/**
 * Copyright (c) 2012 ScientiaMobile, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Refer to the COPYING.txt file distributed with this package.
 *
 * @category   WURFL
 * @package	WURFL_Xml
 * @copyright  ScientiaMobile, Inc.
 * @license	GNU Affero General Public License
 * @version	$id$
 *
 */
/**
 * Extracts version information from XML file
 * @package	WURFL_Xml
 */
class WURFL_Xml_VersionIterator extends WURFL_Xml_AbstractIterator {
	
	private $found_version_info = false;
	
	public function readNextElement() {
		$version = "";
		$lastUpdated = "";
		$officialURL = "";
		while ($this->xmlReader->read()) {
			$nodeName = $this->xmlReader->name;
			switch ($this->xmlReader->nodeType) {
				case XMLReader::ELEMENT:
					switch ($nodeName) {
						case 'ver':
							$version = $this->getTextValue();
							break;
						case 'last_updated':
							$lastUpdated = $this->getTextValue();
							break;
						case 'official_url':
							$officialURL = $this->getTextValue();
							break;
					}
					break;
				case XMLReader::END_ELEMENT:
					switch ($nodeName) {
						case 'version':
							$this->found_version_info = true;
							$this->currentElement = new WURFL_Xml_Info($version, $lastUpdated, $officialURL);
							return;
					}
					break;
			}
		} // end of while
	}
	
	
	public function valid() {
		// We're finished with the version node, nothing else to do
		if ($this->found_version_info === true) {
			return false;
		}
		if($this->currentElement === null) {
			$this->readNextElement();
		}
		return $this->currentElement != null;
	}
}