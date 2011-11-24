<?php
declare(ENCODING = 'utf-8');
namespace Wurfl\Xml;

/**
 * Copyright(c) 2011 ScientiaMobile, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or(at your option) any later version.
 *
 * Refer to the COPYING file distributed with this package.
 *
 * @category   WURFL
 * @package    WURFL_Xml
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 *
 */
/**
 * Extracts version information from XML file
 * @package    WURFL_Xml
 */
class VersionIterator extends AbstractIterator
{
    public function readNextElement()
    {
        $version = '';
        $lastUpdated = '';
        $officialURL = '';
        
        while ($this->_xmlReader->read()) {
            $nodeName = $this->_xmlReader->name;
            
            switch ($this->_xmlReader->nodeType) {
                case \XMLReader::TEXT:
                    $currentText = $this->_xmlReader->value;
                    break;
                case \XMLReader::END_ELEMENT:
                    switch ($nodeName) {
                        case 'version':
                            $this->currentElement = new Info($version, $lastUpdated, $officialURL);
                            break 2;
                        case 'ver':
                            $version = $currentText;
                            break;
                        case 'last_updated':
                            $lastUpdated = $currentText;
                            break;
                        case 'official_url':
                            $officialURL = $currentText;
                            break;
                    }
                    
                    break;
            }
        } // end of while
    }
}
