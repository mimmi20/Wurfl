<?php
namespace Wurfl\Logger;

/**
 * Copyright (c) 2012 ScientiaMobile, Inc.
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 * Refer to the COPYING.txt file distributed with this package.
 *
 * @category   WURFL
 * @package    \Wurfl\Logger
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
use Psr\Log\LoggerInterface;

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
     * @param array $wurflConfig
     *
     * @return LoggerInterface Logger object
     */
    public static function createUndetectedDeviceLogger(array $wurflConfig = null)
    {
        if (self::isLoggingConfigured($wurflConfig)) {
            return self::buildLogger($wurflConfig, 'undetected_devices.log');
        }

        return new NullLogger();
    }

    /**
     * Creates Logger for general logging (not undetected devices)
     *
     * @param array $wurflConfig
     *
     * @return LoggerInterface Logger object
     */
    public static function create(array $wurflConfig = null)
    {
        if (self::isLoggingConfigured($wurflConfig)) {
            return self::buildLogger($wurflConfig, 'wurfl.log');
        }

        return new NullLogger();
    }

    /**
     * Creates Logger for general logging (not undetected devices)
     *
     * @param array  $wurflConfig
     * @param string $fileName
     *
     * @return LoggerInterface Logger object
     */
    private static function buildLogger(array $wurflConfig = null, $fileName = null)
    {
        switch (strtolower($wurflConfig['type'])) {
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
     *
     * @param array  $wurflConfig
     * @param string $fileName
     *
     * @return LoggerInterface File logger
     */
    private static function createFileLogger(array $wurflConfig, $fileName)
    {
        $logFileName = self::createLogFile($wurflConfig['logDir'], $fileName);

        return new FileLogger($logFileName);
    }

    /**
     * Returns true if $wurflConfig specifies a Logger
     *
     * @param array $wurflConfig
     *
     * @return bool
     */
    private static function isLoggingConfigured(array $wurflConfig = null)
    {
        if (is_null($wurflConfig)
            || empty($wurflConfig['type'])
        ) {
            return false;
        }

        if ('Null' === $wurflConfig['type']) {
            // null logger
            return true;
        }

        if ('File' === $wurflConfig['type']
            && !empty($wurflConfig['logDir'])
            && is_dir($wurflConfig['logDir'])
            && is_writable($wurflConfig['logDir'])
        ) {
            // file logger and log dir is writable
            return true;
        }

        // every else
        return false;
    }

    /**
     * Creates a new log file in given $logDir with given $fileName
     *
     * @param string $logDir
     * @param string $fileName
     *
     * @return string Complete filename to created logfile
     */
    private static function createLogFile($logDir, $fileName)
    {
        $file = $logDir . DIRECTORY_SEPARATOR . $fileName;

        touch($file);

        return $file;
    }
}