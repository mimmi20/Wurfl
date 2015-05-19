<?php
/**
 * Copyright (c) 2015 ScientiaMobile, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Refer to the COPYING.txt file distributed with this package.
 *
 *
 * @category   WURFL
 * @package    WURFL
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */

namespace Wurfl\Xml;

/**
 * Stores version and other info about the loaded WURFL
 *
 * @package WURFL_Xml
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
    const PERSISTENCE_KEY = '\\Wurfl\\Xml\\Info';

    /**
     * @var string
     */
    private $version;

    /**
     * @var string
     */
    private $lastUpdated;

    /**
     * @var string
     */
    private $officialUrl;

    /**
     * @param string $version     WURFL Version
     * @param string $lastUpdated WURFL Last Updated data
     * @param string $officialUrl WURFL URL
     */
    public function __construct($version, $lastUpdated, $officialUrl)
    {
        $this->version     = $version;
        $this->lastUpdated = $lastUpdated;
        $this->officialUrl = $officialUrl;
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
        switch ($name) {
            case 'officialURL':
                return $this->officialUrl;
                break;
            default:
                // nothing to do here
                break;
        }

        return $this->$name;
    }

    /**
     * @return Info Empty \Wurfl\Xml\Info object
     */
    public static function noInfo()
    {
        return new Info('', '', '');
    }
}
