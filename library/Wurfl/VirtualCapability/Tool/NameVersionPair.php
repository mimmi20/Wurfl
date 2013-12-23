<?php
namespace Wurfl\VirtualCapability\Tool;

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
     * @package    \Wurfl\VirtualCapability\UserAgentTool
     * @copyright  ScientiaMobile, Inc.
     * @license    GNU Affero General Public License
     * @version    $id$
     */
/**
 * @package \Wurfl\VirtualCapability\UserAgentTool
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
     * @param      $regex
     * @param null $name
     * @param null $version
     *
     * @return bool
     */
    public function setRegex($regex, $name = null, $version = null)
    {
        // No need to capture the matches if we're not going to use them
        if (!is_int($name) && !is_int($version)) {
            if (preg_match($regex, $this->device->userAgent)) {
                $this->name    = trim($name);
                $this->version = trim($version);
                return true;
            } else {
                return false;
            }
        } elseif (preg_match($regex, $this->device->userAgent, $this->regexMatches)) {
            if ($name !== null) {
                $this->name = is_int($name) ? $this->regexMatches[$name] : $name;
                $this->name = trim($this->name);
            }

            if ($version !== null) {
                $this->version = is_int($version) ? $this->regexMatches[$version] : $version;
                $this->version = trim($this->version);
            }

            return true;
        }

        return false;
    }

    /**
     * @param string     $needle
     * @param string     $name
     * @param null $version
     *
     * @return bool
     */
    public function setContains($needle, $name, $version = null)
    {
        if (strpos($this->device->userAgent, $needle) !== false) {
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
