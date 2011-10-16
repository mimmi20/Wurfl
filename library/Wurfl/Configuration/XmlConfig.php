<?php
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
 * @package    WURFL_Configuration
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
/**
 * XML Configuration
 * @package    WURFL_Configuration
 */
class WURFL_Configuration_XmlConfig extends WURFL_Configuration_Config
{
    /**
     * Initialize XML Configuration
     */
    private function _initialize()
    {
        $xmlConfig = simplexml_load_file($this->configFilePath);
        $this->_wurflFile = $this->_wurflFile($xmlConfig->xpath('/wurfl-config/wurfl/main-file'));
        $this->_wurflPatches = $this->_wurflPatches($xmlConfig->xpath('/wurfl-config/wurfl/patches/patch'));
        $this->_allowReload = $this->_allowReload($xmlConfig->xpath('/wurfl-config/allow-reload'));
        $this->_persistence = $this->_persistence($xmlConfig->xpath('/wurfl-config/persistence'));
        $this->_cache = $this->_persistence($xmlConfig->xpath('/wurfl-config/cache'));
        $this->_logDir = $this->_logDir($xmlConfig->xpath('/wurfl-config/logDir'));
    }

    /**
     * Returns the full path to the WURFL file
     * @param array $mainFileElement array of SimpleXMLElement objects 
     * @return string full path
     */
    private function _wurflFile($mainFileElement)
    {
        return parent::getFullPath((string) $mainFileElement[0]);
    }
    
    /**
     * Returns an array of full path WURFL patches
     * @param array $patchElements array of SimpleXMLElement objects
     * @return array WURFL Patches
     */
    private function _wurflPatches($patchElements)
    {
        $patches = array();
        if ($patchElements) {
            foreach ($patchElements as $patchElement) {
                $patches[] = parent::getFullPath((string) $patchElement);
            }
        }
        return $patches;
    }

    /**
     * Returns true if reload is allowed, according to $_allowReloadElement
     * @param array $_allowReloadElement array of SimpleXMLElement objects
     */
    private function _allowReload($_allowReloadElement)
    {
        if (!empty($_allowReloadElement)) {
            return(bool) $_allowReloadElement[0];
        }
        return false;
    }
    
    /**
     * Returns log directory from XML config
     * @param array $logDirElement array of SimpleXMLElement objects
     * @return string Log directory
     */
    private function _logDir($logDirElement)
    {
        if (!empty($logDirElement)) {
            return parent::getFullPath((string) $logDirElement[0]);
        }
        return null;
    }

    /**
     * Returns persistence provider info from XML config
     * @param array $persistenceElement array of SimpleXMLElement objects
     * @return array Persistence info
     */
    private function _persistence($persistenceElement)
    {
        $persistence = array();
        if ($persistenceElement) {
            $persistence['provider'] = (string)$persistenceElement[0]->provider;
            $persistence['params']   = $this->_toArray((string)$persistenceElement[0]->params);
        }
        return $persistence;
    }

    /**
     * Converts given CSV $params to array of parameters
     * @param string $params Comma-seperated list of parameters
     * @return array Parameters
     */
    private function _toArray($params)
    {
        $paramsArray = array();

        foreach (explode(',', $params) as $param) {
            $paramNameValue = explode('=', $param);
            if (count($paramNameValue) > 1) {
                if (strcmp(WURFL_Configuration_Config::DIR, $paramNameValue[0]) == 0) {
                    $paramNameValue[1] = parent::getFullPath($paramNameValue[1]);
                }
                $paramsArray[trim($paramNameValue[0])] = trim($paramNameValue[1]);                                
            }
        }
        return $paramsArray;
    }

    /**
     * WURFL XML Schema
     * @var string
     */
    const WURFL_CONF_SCHEMA = '<?xml version='1.0' encoding='utf-8' ?>
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
    </element>';
}