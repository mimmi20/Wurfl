<?php
declare(ENCODING = 'utf-8');
namespace Wurfl;

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
 * @package    WURFL
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version   SVN: $Id$
 */
/**
 * Helper to load PHP classes on demand
 * @package    WURFL
 */
class ClassLoader
{
    const CLASS_PREFIX = 'Wurfl\\';
    
    /**
     * Loads a Class given the class name
     *
     * @param string $className
     */
    public static function loadClass($className)
    {        
        if ($className === NULL) {
            throw new \WURFL\WURFLException('Unable To Load Class : ' . $className);
        }
                
        if (substr($className, 0, 6) !== self::CLASS_PREFIX) {
            return FALSE;
        }
        
        if (!class_exists($className, false)) {
            $ROOT = dirname(__FILE__) . DIRECTORY_SEPARATOR;

            $classFilePath = str_replace('_', DIRECTORY_SEPARATOR, substr($className, 6)) . '.php';
            require_once $ROOT . $classFilePath;
        }
        
        return FALSE;
    }

}

// register class loader
spl_autoload_register(array('\\Wurfl\\ClassLoader', 'loadClass'));
