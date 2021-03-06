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
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */

namespace Wurfl\Configuration;

/**
 * In-memory WURFL Configuration
 */
class InMemoryConfig extends Config
{
    /**
     * Initialize Configuration
     *
     * @param array $configuration
     */
    protected function initialize(array $configuration)
    {
        // nothing to do here
    }

    /**
     * @param string $wurflFile
     *
     * @return \Wurfl\Configuration\InMemoryConfig $this
     */
    public function wurflFile($wurflFile)
    {
        $this->wurflFile = $wurflFile;

        return $this;
    }

    /**
     * @param string $wurflPatch
     *
     * @return \Wurfl\Configuration\InMemoryConfig $this
     */
    public function wurflPatch($wurflPatch)
    {
        $this->wurflPatches[] = $wurflPatch;

        return $this;
    }

    /**
     * @param array $capabilityFilter
     *
     * @return \Wurfl\Configuration\InMemoryConfig $this
     */
    public function capabilityFilter(array $capabilityFilter)
    {
        $this->capabilityFilter = $capabilityFilter;

        return $this;
    }

    /**
     * Set persistence provider
     *
     * @param string $provider
     * @param array  $params
     *
     * @return \Wurfl\Configuration\InMemoryConfig $this
     */
    public function persistence($provider, array $params = array())
    {
        $this->persistence = array_merge(array(Config::PROVIDER => $provider), array(Config::PARAMS => $params));

        return $this;
    }

    /**
     * Set Cache provider
     *
     * @param string $provider
     * @param array  $params
     *
     * @return \Wurfl\Configuration\InMemoryConfig $this
     */
    public function cache($provider, array $params = array())
    {
        $this->cache = array_merge(array(config::PROVIDER => $provider), array(Config::PARAMS => $params));

        return $this;
    }

    /**
     * Set logging directory
     *
     * @param string $dir
     *
     * @return \Wurfl\Configuration\InMemoryConfig $this
     */
    public function logDir($dir)
    {
        $this->logDir = $dir;

        $this->buildFileLogger($this->logDir);

        return $this;
    }

    /**
     * Specifies whether reloading is allowed
     *
     * @param bool $reload
     *
     * @return \Wurfl\Configuration\InMemoryConfig $this
     */
    public function allowReload($reload = true)
    {
        $this->allowReload = $reload;

        return $this;
    }

    /**
     * Sets the API match mode
     *
     * @param string $mode
     *
     * @throws \InvalidArgumentException
     * @return \Wurfl\Configuration\InMemoryConfig
     */
    public function matchMode($mode)
    {
        if (!self::validMatchMode($mode)) {
            throw new \InvalidArgumentException('Invalid Match Mode: ' . $mode);
        }
        $this->matchMode = $mode;

        return $this;
    }
}
