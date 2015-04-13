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
 * WURFL File Logger
 *
 * @package    WURFL_Logger
 */
class FileLogger
    extends AbstractLogger
{
    /**
     * @var int File pointer
     */
    private $filePointer;

    /**
     * Creates a new FileLogger object
     *
     * @param string $fileName
     *
     * @throws \InvalidArgumentException Log file specified is not writable
     * @throws \Wurfl\Exception Unable to open log file
     */
    public function __construct($fileName)
    {
        if (!is_writable($fileName)) {
            throw new \InvalidArgumentException("Log file specified is not writable");
        }

        $this->filePointer = @fopen($fileName, "a");

        if (!$this->filePointer) {
            throw new Exception("Unable to open log file: ");
        }
    }

    /**
     * Close open files
     */
    public function __destruct()
    {
        fclose($this->filePointer);
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
        $time        = date("F jS Y, h:iA");
        $fullMessage = '[' . $time . '] [' . $level . '] ' . $message;
        fwrite($this->filePointer, $fullMessage . "\n");
    }
}
