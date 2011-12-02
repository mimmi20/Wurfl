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
 * Iterates over a WURFL/Patch XML file
 * @package    WURFL_Xml
 */
abstract class AbstractIterator implements \Iterator
{
    /**
     * @var string filename with path to wurfl.xml or patch file
     */
    protected $_inputFile;
    
    /**
     * @var XMLReader
     */
    protected $_xmlReader;
    
    protected $_currentElement;
    
    protected $_currentElementId;
    
    /**
     * Loads given XML $_inputFile
     * @param string $_inputFile
     */
    public function __construct($inputFile)
    {
        if (!file_exists($inputFile)) {
            throw new \InvalidArgumentException('cannot locate[' . $inputFile . '] file!');
        }
        $this->_inputFile = realpath(Utils::getXMLFile($inputFile));
        
        $this->rewind();
    }
    
    /**
     * Returns the current XML element
     * @return XMLReader Current XML element
     */
    public function current()
    {
        return $this->_currentElement;
    }
    
    /**
     * Prepare for next XML element
     */
    public function next()
    {
        $this->_currentElement = null;
    }
    
    /**
     * Returns the current element id
     * @return string Current element id
     */
    public function key()
    {
        return $this->_currentElementId;
    }
    
    /**
     * Returns true if the current XML element is valid for processing
     * @return bool
     */
    public function valid()
    {
        if ($this->_currentElement === null) {
            $this->readNextElement();
        }
        return $this->_currentElement != null;
    }
    
    /**
     * Open the input file and position cursor at the beginning
     * @see $_inputFile
     */
    public function rewind()
    {
        //$this->_xmlReader = new \XMLReader();
        //$this->_xmlReader->open($this->_inputFile);
        
        $this->_xmlReader = \simplexml_load_string(\file_get_contents($this->_inputFile));
        
        $this->_currentElement = null;
        $this->_currentElementId = null;
    }
    
    /**
     * Move the XMLReader pointer to the next element and read data
     */
    abstract public function readNextElement();
}
