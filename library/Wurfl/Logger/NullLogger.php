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
 * Class that is used to supress logging
 * @package    \Wurfl\Logger
 */
class NullLogger implements LoggerInterface
{
    public function log($message, $type="")
    {
        //echo $message . "\n";
    }
    
    public function debug($message)
    {
        //echo $message . "\n";        
    }
    
    public function info($message){}
}

