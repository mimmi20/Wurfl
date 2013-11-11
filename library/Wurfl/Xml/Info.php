<?php
namespace Wurfl\Xml;

    /**
     * Copyright (c) 2012 ScientiaMobile, Inc.
     * This program is free software: you can redistribute it and/or modify
     * it under the terms of the GNU Affero General Public License as
     * published by the Free Software Foundation, either version 3 of the
     * License, or (at your option) any later version.
     * Refer to the COPYING.txt file distributed with this package.
     *
     * @category   WURFL
     * @package    \Wurfl\Xml
     * @copyright  ScientiaMobile, Inc.
     * @license    GNU Affero General Public License
     * @version    $id$
     */
/**
 * Stores version and other info about the loaded WURFL
 *
 * @package \Wurfl\Xml
 * @property-read string $version     Loaded WURFL Version
 * @property-read string $lastUpdated Loaded WURFL Last Updated Date
 * @property-read string $officialURL Loaded WURFL Official URL
 */
class Info
{
    /**
     * Key used in persistence provider to store version-related information
     *
     * @var string
     */
    const PERSISTENCE_KEY = 'Wurfl_XML_INFO';
    private $_version;
    private $_lastUpdated;
    private $_officialURL;

    /**
     * @param string $version     WURFL Version
     * @param string $lastUpdated WURFL Last Updated data
     * @param string $officialURL WURFL URL
     */
    public function __construct($version, $lastUpdated, $officialURL)
    {
        $this->_version     = $version;
        $this->_lastUpdated = $lastUpdated;
        $this->_officialURL = $officialURL;
    }

    /**
     * Returns the value for the given key (version, lastUpdated, officialURL)
     *
     * @param string $name
     *
     * @return string value
     */
    public function __get($name)
    {
        $name = '_' . $name;

        return $this->$name;
    }

    /**
     * @return Info Empty \Wurfl\Xml_Info object
     */
    public static function noInfo()
    {
        return new Info('', '', '');
    }
}