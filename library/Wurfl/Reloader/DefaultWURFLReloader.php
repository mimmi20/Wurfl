<?php
namespace Wurfl\Reloader;

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
 *
 * @category   WURFL
 * @package    \Wurfl\Reloader
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 * @deprecated
 */

use \Wurfl\ManagerFactory;
use \Wurfl\Configuration\XmlConfig;
use \Wurfl\Configuration\ArrayConfig;

/**
 * WURFL Reloader
 * @package    \Wurfl\Reloader
 * @deprecated
 */
class DefaultWurflReloader implements ReloaderInterface
{
    public function reload($wurflConfigurationPath)
    {
        $wurflConfig = $this->fromFile ( $wurflConfigurationPath );
        touch($wurflConfig->wurflFile);
        $wurflManagerFactory = new ManagerFactory($wurflConfig);
        $wurflManagerFactory->create();    
        
    }
    
    private function fromFile($wurflConfigurationPath)
    {
        if ($this->endsWith ( $wurflConfigurationPath, ".xml" )) {
            return new XmlConfig ( $wurflConfigurationPath );
        }
        return new ArrayConfig($wurflConfigurationPath);
    }
    
    private function endsWith($haystack, $needle)
    {
        return strrpos($haystack, $needle) === strlen($haystack)-strlen($needle);
    }
}
