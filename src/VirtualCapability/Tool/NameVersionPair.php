<?php
/**
 * Copyright (c) 2015 ScientiaMobile, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Refer to the LICENSE file distributed with this package.
 *
 *
 * @category   WURFL
 *
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */

namespace Wurfl\VirtualCapability\Tool;

/**
 */
class NameVersionPair extends PropertyList
{
    /**
     * @var string|null
     */
    public $name;

    /**
     * @var string|null
     */
    public $version;

    /**
     * @var array
     */
    protected $regexMatches = array();

    /**
     * @param string|null $name
     * @param string|null $version
     *
     * @return bool
     */
    public function set($name = null, $version = null)
    {
        if ($name !== null) {
            $this->name = trim($name);
        }

        if ($version !== null) {
            $this->version = trim($version);
        }

        return true;
    }

    /**
     * @param string $ua
     * @param string $regex
     * @param string $name
     * @param string $version
     *
     * @return bool
     */
    public function setRegex($ua, $regex, $name = null, $version = null)
    {
        // No need to capture the matches if we're not going to use them
        if (!is_int($name) && !is_int($version)) {
            if (preg_match($regex, $ua)) {
                $this->name    = trim($name);
                $this->version = trim($version);

                return true;
            }

            return false;
        }

        if (preg_match($regex, $ua, $this->regexMatches)) {
            if ($name !== null) {
                $this->name = is_int($name) ? $this->regexMatches[$name] : $name;
                $this->name = trim($this->name);
            }

            if (is_int($version) && isset($this->regexMatches[$version])) {
                $this->version = $this->regexMatches[$version];
                $this->version = trim($this->version);
            } elseif ($version !== null) {
                $this->version = $version;
                $this->version = trim($this->version);
            }

            return true;
        }

        return false;
    }

    /**
     * @param string $ua
     * @param string $needle
     * @param string $name
     * @param string $version
     *
     * @return bool
     */
    public function setContains($ua, $needle, $name, $version = null)
    {
        if (strpos($ua, $needle) !== false) {
            if ($name !== null) {
                $this->name = trim($name);
            }

            if ($version !== null) {
                $this->version = trim($version);
            }

            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public function getLastRegexMatches()
    {
        return $this->regexMatches;
    }

    /**
     * @param $matchNumber
     *
     * @return mixed
     */
    public function getLastRegexMatch($matchNumber)
    {
        return $this->regexMatches[$matchNumber];
    }
}
