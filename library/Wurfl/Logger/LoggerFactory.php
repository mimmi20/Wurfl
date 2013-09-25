<?php
namespace Wurfl\Logger;

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
 * @package    \Wurfl\Logger
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
/**
 * Logging factory
 * 
 * @package    \Wurfl\Logger
 */
class LoggerFactory
{
    /**
     * Create Logger for undetected devices with filename undetected_devices.log
     *
     * @param \Wurfl\Configuration\Config $wurflConfig
     *
     * @return \Wurfl\Logger_Interface Logger object
     */
    public static function createUndetectedDeviceLogger(\Wurfl\Configuration\Config $wurflConfig = null)
    {    
        if (self::isLoggingConfigured($wurflConfig)) {
            return self::buildLogger($wurflConfig, 'undetected_devices.log');
        }
        
        return new NullLogger();
    }
    
    /**
     * Creates Logger for general logging (not undetected devices)
     *
     * @param \Wurfl\Configuration\Config $wurflConfig
     *
     * @return \Wurfl\Logger_Interface Logger object
     */
    public static function create(\Wurfl\Configuration\Config $wurflConfig = null)
    {
        if (self::isLoggingConfigured($wurflConfig)) {
            return self::buildLogger($wurflConfig, 'wurfl.log');
        }
        
        return new NullLogger();                
    }
    
    /**
     * Creates Logger for general logging (not undetected devices)
     *
     * @param \Wurfl\Configuration\Config $wurflConfig
     * @param string                      $fileName
     *
     * @return \Wurfl\Logger_Interface Logger object
     */
    private static function buildLogger(\Wurfl\Configuration\Config $wurflConfig = null, $fileName = null)
    {
        switch (strtolower($wurflConfig->logger['type'])) {
            case 'file':
                $logger = self::createFileLogger($wurflConfig, $fileName);
                break;
            case 'null':
            default:
                $logger = new NullLogger();
                break;
        }
        
        return $logger;
    }
    
    /**
     * Creates a new file logger
     * @param \Wurfl\Configuration\Config $wurflConfig
     * @param string $fileName
     * @return \Wurfl\Logger_FileLogger File logger
     */
    private static function createFileLogger(\Wurfl\Configuration\Config $wurflConfig, $fileName)
    {
        $logFileName = self::createLogFile($wurflConfig->logger['logDir'], $fileName);
        return new FileLogger($logFileName);
    }
    
    /**
     * Returns true if $wurflConfig specifies a Logger
     * @param \Wurfl\Configuration\Config $wurflConfig
     * @return bool
     */
    private static function isLoggingConfigured(\Wurfl\Configuration\Config $wurflConfig = null)
    {    
        if (is_null($wurflConfig)
            || is_null($wurflConfig->logger)
            || empty($wurflConfig->logger['type'])
        ) {
            return false;
        }
        
        if ('Null' === $wurflConfig->logger['type']) {
            // null logger
            return true;
        }
        
        if ('File' === $wurflConfig->logger['type']
            && !empty($wurflConfig->logger['logDir'])
            && is_dir($wurflConfig->logger['logDir'])
            && is_writable($wurflConfig->logger['logDir'])
        ) {
            // file logger and log dir is writable
            return true;
        }
        
        // every else
        return false;
    }
    
    /**
     * Creates a new log file in given $logDir with given $fileName
     * @param string $logDir
     * @param string $fileName
     * @return string Complete filename to created logfile
     */    
    private static function createLogFile($logDir, $fileName)
    {
        $file = $logDir . DIRECTORY_SEPARATOR . $fileName;
        
        touch($file);
        return $file;
    }
}