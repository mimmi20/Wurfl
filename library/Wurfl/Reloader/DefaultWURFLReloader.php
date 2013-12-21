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
 * @package    WURFL_Reloader
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 * @deprecated
 */
use Wurfl\Configuration\ArrayConfig;
use Wurfl\Configuration\XmlConfig;
use Wurfl\Manager;

/**
 * WURFL Reloader
 *
 * @package    WURFL_Reloader
 * @deprecated
 */
class DefaultWURFLReloader implements ReloaderInterface
{

    public function reload($configurationPath)
    {
        $wurflConfig = $this->fromFile($configurationPath);
        touch($wurflConfig->wurflFile);
        new Manager($wurflConfig);
    }

    private function fromFile($wurflConfigurationPath)
    {
        if ($this->endsWith($wurflConfigurationPath, ".xml")) {
            return new XmlConfig($wurflConfigurationPath);
        }
        return new ArrayConfig($wurflConfigurationPath);
    }

    private function endsWith($haystack, $needle)
    {
        return strrpos($haystack, $needle) === strlen($haystack) - strlen($needle);
    }
}
