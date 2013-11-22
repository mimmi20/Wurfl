<?php
namespace Wurfl\Configuration;

/**
 * Copyright (c) 2012 ScientiaMobile, Inc.
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 * Refer to the COPYING.txt file distributed with this package.
 *
 * @category   WURFL
 * @package    \Wurfl\Configuration
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */

use SimpleXMLElement;
use Wurfl\Exception;

/**
 * XML Configuration
 *
 * @package    \Wurfl\Configuration
 */
class XmlConfig extends Config
{
    /**
     * Initialize XML Configuration
     */
    protected function initialize()
    {
        /** @var $xmlConfig SimpleXMLElement */
        $xmlConfig = simplexml_load_file($this->configFilePath);

        $this->wurflFile = $this->wurflFile($xmlConfig->{Config::WURFL}->{Config::MAIN_FILE});

        if ($xmlConfig->{Config::WURFL}->{Config::PATCHES}->{Config::PATCH}) {
            $this->wurflPatches = $this->wurflPatches($xmlConfig->{Config::WURFL}->{Config::PATCHES}->{Config::PATCH});
        }

        $this->allowReload = (boolean)$xmlConfig->{Config::ALLOW_RELOAD};

        if ($xmlConfig->{Config::CAPABILITY_FILTER}->capability) {
            $this->capabilityFilter = $this->capabilityFilter(
                $xmlConfig->{Config::CAPABILITY_FILTER}->capability
            );
        }

        $this->persistence = $this->persistence($xmlConfig->{Config::PERSISTENCE});
        $this->cache       = $this->persistence($xmlConfig->{Config::CACHE});
        $this->logger      = $this->logger($xmlConfig->{Config::LOGGER});
        $this->matchMode   = $this->matchMode($xmlConfig->{Config::MATCH_MODE});
    }

    /**
     * Returns the full path to the WURFL file
     *
     * @param SimpleXMLElement $mainFileElement array of SimpleXMLElement objects
     *
     * @return string full path
     */
    private function wurflFile(SimpleXMLElement $mainFileElement)
    {
        return parent::getFullPath((string)$mainFileElement);
    }

    /**
     * Returns an array of full path WURFL patches
     *
     * @param SimpleXMLElement $patchElements array of SimpleXMLElement objects
     *
     * @return array WURFL Patches
     */
    private function wurflPatches(SimpleXMLElement $patchElements = null)
    {
        $patches = array();

        if ($patchElements) {
            foreach ($patchElements as $patchElement) {
                $patches[] = parent::getFullPath((string)$patchElement);
            }
        }

        return $patches;
    }

    /**
     * Returns an array of WURFL Capabilities
     *
     * @param SimpleXMLElement $capabilityFilter array of SimpleXMLElement objects
     *
     * @return array WURFL Capabilities
     */
    private function capabilityFilter(SimpleXMLElement $capabilityFilter = null)
    {
        $filter = array();

        if ($capabilityFilter) {
            foreach ($capabilityFilter as $filterElement) {
                $filter[] = (string)$filterElement;
            }
        }

        return $filter;
    }

    /**
     * Returns the mode of operation if set, otherwise null
     *
     * @param SimpleXMLElement $modeElement array of SimpleXMLElement objects
     *
     * @return boolean
     * @throws Exception
     */
    private function matchMode(SimpleXMLElement $modeElement)
    {
        if (!empty($modeElement)) {
            $mode = (string)$modeElement;

            if (!$mode) {
                return $this->matchMode;
            }

            if (!self::validMatchMode($mode)) {
                throw new Exception('Invalid Match Mode: ' . $mode);
            }

            $this->matchMode = $mode;
        }

        return $this->matchMode;
    }

    /**
     * Returns log directory from XML config
     *
     * @param SimpleXMLElement $logDirElement array of SimpleXMLElement objects
     *
     * @return string Log directory
     */
    private function logger(SimpleXMLElement $logDirElement)
    {
        $logger = array();

        if (!empty($logDirElement)) {
            $logger['logDir'] = parent::getFullPath((string)$logDirElement->logDir);
            $logger['type']   = (string)$logDirElement->type;
        }

        return $logger;
    }

    /**
     * Returns persistence provider info from XML config
     *
     * @param SimpleXMLElement $persistenceElement array of SimpleXMLElement objects
     *
     * @return array Persistence info
     */
    private function persistence(SimpleXMLElement $persistenceElement)
    {
        $persistence = array();

        if ($persistenceElement) {
            $persistence['provider'] = (string)$persistenceElement->{Config::PROVIDER};
            $persistence['params']   = $this->_toArray((string)$persistenceElement->{Config::PARAMS});
        }

        return $persistence;
    }

    /**
     * Converts given CSV $params to array of parameters
     *
     * @param string $params Comma-seperated list of parameters
     *
     * @return array Parameters
     */
    private function _toArray($params)
    {
        $paramsArray = array();

        foreach (explode(',', $params) as $param) {
            $paramNameValue = explode('=', $param);

            if (count($paramNameValue) > 1) {
                if (strcmp(Config::DIR, $paramNameValue[0]) == 0) {
                    $paramNameValue[1] = parent::getFullPath($paramNameValue[1]);
                }

                $paramsArray[trim($paramNameValue[0])] = trim($paramNameValue[1]);
            }
        }

        return $paramsArray;
    }

    /**
     * WURFL XML Schema
     *
     * @var string
     */
    const WURFL_CONF_SCHEMA
        = '<?xml version="1.0" encoding="utf-8" ?>
    <element name="wurfl-config" xmlns="http://relaxng.org/ns/structure/1.0">
        <element name="wurfl">
            <element name="main-file"><text/></element>
            <element name="patches">
                <zeroOrMore>
                      <element name="patch"><text/></element>
                </zeroOrMore>
              </element>
          </element>
        <optional>
              <element name="allow-reload"><text/></element>
        </optional>
        <optional>
              <element name="match-mode"><text/></element>
        </optional>
        <optional>
              <element name="logDir"><text/></element>
        </optional>
          <element name="persistence">
              <element name="provider"><text/></element>
              <optional>
                  <element name="params"><text/></element>
              </optional>
          </element>
          <element name="cache">
              <element name="provider"><text/></element>
              <optional>
                  <element name="params"><text/></element>
              </optional>
          </element>
    </element>';
}