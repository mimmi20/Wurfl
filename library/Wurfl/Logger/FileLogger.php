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
use Wurfl\Exception;

/**
 * WURFL File Logger
 *
 * @package    \Wurfl\Logger
 */
class FileLogger implements LoggerInterface
{
    /**
     * @var string EMERGENCY Log level
     */
    const EMERGENCY = 'EMERGENCY';

    /**
     * @var string ALERT Log level
     */
    const ALERT = 'ALERT';

    /**
     * @var string CRITICAL Log level
     */
    const CRITICAL = 'CRITICAL';

    /**
     * @var string ERROR Log level
     */
    const ERROR = 'ERROR';

    /**
     * @var string WARNING Log level
     */
    const WARNING = 'WARNING';

    /**
     * @var string NOTICE Log level
     */
    const NOTICE = 'NOTICE';

    /**
     * @var string INFO Log level
     */
    const INFO = 'INFO';

    /**
     * @var string DEBUG Log level
     */
    const DEBUG = 'DEBUG';

    /**
     * @var int File pointer
     */
    private $fp;

    /**
     * Creates a new FileLogger object
     *
     * @param string $fileName
     *
     * @throws \InvalidArgumentException Log file specified is not writable
     * @throws Exception Unable to open log file
     */
    public function __construct($fileName)
    {
        if (!is_writable($fileName)) {
            throw new \InvalidArgumentException('Log file specified is not writable');
        }

        $this->fp = @fopen($fileName, 'a');

        if (!$this->fp) {
            throw new Exception('Unable to open log file: ');
        }
    }

    /**
     * Close open files
     */
    public function __destruct()
    {
        fclose($this->fp);
    }

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array  $context
     *
     * @return null
     */
    public function emergency($message, array $context = array())
    {
        $this->log(self::EMERGENCY, $message, $context);
    }

    /**
     * Action must be taken immediately.
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array  $context
     *
     * @return null
     */
    public function alert($message, array $context = array())
    {
        $this->log(self::ALERT, $message, $context);
    }

    /**
     * Critical conditions.
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array  $context
     *
     * @return null
     */
    public function critical($message, array $context = array())
    {
        $this->log(self::CRITICAL, $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array  $context
     *
     * @return null
     */
    public function error($message, array $context = array())
    {
        $this->log(self::ERROR, $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array  $context
     *
     * @return null
     */
    public function warning($message, array $context = array())
    {
        $this->log(self::WARNING, $message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array  $context
     *
     * @return null
     */
    public function notice($message, array $context = array())
    {
        $this->log(self::NOTICE, $message, $context);
    }

    /**
     * Interesting events.
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array  $context
     *
     * @return null
     */
    public function info($message, array $context = array())
    {
        $this->log(self::INFO, $message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array  $context
     *
     * @return null
     */
    public function debug($message, array $context = array())
    {
        $this->log(self::DEBUG, $message, $context);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     *
     * @return null
     */
    public function log($level, $message, array $context = array())
    {
        $time        = date('F jS Y, h:iA');
        $fullMessage = '[' . $time . '] [' . $level . '] ' . $message;

        fwrite($this->fp, $fullMessage . "\n");
    }
}