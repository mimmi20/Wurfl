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

namespace Wurfl\Configuration;

use Noodlehaus\Config as ConfigLoader;

/**
 * XML Configuration
 *
 * @package    WURFL_Configuration
 */
class FileConfig extends Config
{
    /**
     * Creates a new WURFL Configuration object from $configFilePath
     *
     * @param string $configFilePath Complete filename of configuration file
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($configFilePath)
    {
        if (!file_exists($configFilePath)) {
            throw new \InvalidArgumentException('The configuration file ' . $configFilePath . ' does not exist.');
        }

        $this->configFilePath       = $configFilePath;
        $this->configurationFileDir = dirname($this->configFilePath);

        $configLoader = new ConfigLoader($this->configFilePath);
        $this->initialize($configLoader->all());
    }

    /**
     * WURFL XML Schema
     *
     * @var string
     */
    const WURFL_CONF_SCHEMA = "<?xml version='1.0' encoding='utf-8' ?>
    <element name='wurfl-config' xmlns='http://relaxng.org/ns/structure/1.0'>
        <element name='wurfl'>
            <element name='main-file'><text/></element>
            <element name='patches'>
                <zeroOrMore>
                      <element name='patch'><text/></element>
                </zeroOrMore>
              </element>
          </element>
        <optional>
              <element name='allow-reload'><text/></element>
        </optional>
        <optional>
              <element name='match-mode'><text/></element>
        </optional>
        <optional>
              <element name='logDir'><text/></element>
        </optional>
          <element name='persistence'>
              <element name='provider'><text/></element>
              <optional>
                  <element name='params'><text/></element>
              </optional>
          </element>
          <element name='cache'>
              <element name='provider'><text/></element>
              <optional>
                  <element name='params'><text/></element>
              </optional>
          </element>
    </element>";
}
