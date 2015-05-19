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

namespace Wurfl\Logger;

/**
 * Logging factory
 *
 * @package    WURFL_Logger
 */
class LoggerFactory
{
    /**
     * Creates Logger for general logging (not undetected devices)
     *
     * @param \Wurfl\Configuration\Config $wurflConfig
     *
     * @return \Psr\Log\LoggerInterface Logger object
     */
    public static function create($wurflConfig = null)
    {
        if (self::isLoggingConfigured($wurflConfig)) {
            return self::createFileLogger($wurflConfig, 'wurfl.log');
        }

        return new NullLogger();
    }

    /**
     * Creates a new file logger
     *
     * @param \Wurfl\Configuration\Config $wurflConfig
     * @param string                      $fileName
     *
     * @return FileLogger File logger
     */
    private static function createFileLogger($wurflConfig, $fileName)
    {
        $logFileName = self::createLogFile($wurflConfig->logDir, $fileName);

        return new FileLogger($logFileName);
    }

    /**
     * Returns true if $wurflConfig specifies a Logger
     *
     * @param \Wurfl\Configuration\Config $wurflConfig
     *
     * @return bool
     */
    private static function isLoggingConfigured($wurflConfig)
    {
        if (is_null($wurflConfig)) {
            return false;
        }

        return !is_null($wurflConfig->logDir) && is_writable($wurflConfig->logDir);
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
        $file = realpath($logDir . DIRECTORY_SEPARATOR . $fileName);
        touch($file);

        return $file;
    }
}
