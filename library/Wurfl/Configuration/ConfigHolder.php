<?php
namespace Wurfl\Configuration;

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
 *
 * @category   WURFL
 * @package    WURFL_Configuration
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
use Wurfl\Exception;

/**
 * WURFL Configuration holder singleton
 *
 * @package    WURFL_Configuration
 */
class ConfigHolder
{

    /**
     * @var Config
     */
    private static $wurflConfig = null;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    /**
     * Returns a Configuration object
     *
     * @throws \Wurfl\Exception
     * @return Config
     */
    public static function getWURFLConfig()
    {
        if (self::$wurflConfig === null) {
            throw new Exception("The Configuration Holder is not initialized with a valid WURFLConfig object");
        }
        return self::$wurflConfig;
    }

    /**
     * Sets the Configuration object
     *
     * @param Config $wurflConfig
     */
    public static function setWURFLConfig(Config $wurflConfig)
    {
        self::$wurflConfig = $wurflConfig;
    }
}
