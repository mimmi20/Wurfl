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
 *
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */

namespace Wurfl\Storage;

use Wurfl\Configuration\Config;

/**
 * WURFL Storage factory
 */
class Factory
{
    /**
     * @var array Default configuration
     */
    private static $defaultConfiguration = array(
        'provider' => 'memory',
        'params'   => array(),
    );

    /**
     * Create a configuration based on the default configuration with the differences from $configuration
     *
     * @param array $configuration
     *
     * @return \Wurfl\Storage\Storage Storage object, initialized with the given $configuration
     */
    public static function create(array $configuration)
    {
        $currentConfiguration = is_array($configuration) ? array_merge(self::$defaultConfiguration, $configuration)
            : self::$defaultConfiguration;

        $class = self::className($currentConfiguration);

        $adapter = new $class($currentConfiguration[Config::PARAMS]);

        return new Storage($adapter);
    }

    /**
     * Return the Storage Provider Class name from the given $configuration by using its 'provider' element
     *
     * @param array $configuration
     *
     * @return string WurflCache Storage Provider class name
     */
    private static function className($configuration)
    {
        $provider = (empty($configuration[Config::PROVIDER]) ? 'null' : $configuration[Config::PROVIDER]);

        if ('null' === $provider) {
            $provider = 'NullStorage';
        }

        return '\\WurflCache\\Adapter\\' . ucfirst($provider);
    }
}
