<?php
namespace Wurfl\VirtualCapability\UserAgentTool;

    /**
     * Copyright (c) 2012 ScientiaMobile, Inc.
     * This program is free software: you can redistribute it and/or modify
     * it under the terms of the GNU Affero General Public License as
     * published by the Free Software Foundation, either version 3 of the
     * License, or (at your option) any later version.
     * Refer to the COPYING.txt file distributed with this package.
     *
     * @category   WURFL
     * @package    WURFL_VirtualCapability_UserAgentTool
     * @copyright  ScientiaMobile, Inc.
     * @license    GNU Affero General Public License
     * @version    $id$
     */
/**
 * @package WURFL_VirtualCapability_UserAgentTool
 */
class Device
{
    /**
     * @var NameVersionPair
     */
    public $browser;
    /**
     * @var NameVersionPair
     */
    public $os;

    public $ua;
    public $ua_lower;

    public function __construct($user_agent)
    {
        $this->ua       = $user_agent;
        $this->ua_lower = strtolower($user_agent);
        $this->browser  = new NameVersionPair($this);
        $this->os       = new NameVersionPair($this);
    }

    protected static $windows_map = array(
        '4.0' => 'NT 4.0',
        '5.0' => '2000',
        '5.1' => 'XP',
        '5.2' => 'XP',
        '6.0' => 'Vista',
        '6.1' => '7',
        '6.2' => '8',
        '6.3' => '8.1',
    );

    public function normalize()
    {
        $this->normalizeOS();
    }

    protected function normalizeOS()
    {
        if (strpos($this->ua, 'Windows') !== false) {
            if (preg_match('/Windows NT ([0-9]\.[0-9])/', $this->os->name, $matches)) {
                $this->os->name    = "Windows";
                $this->os->version = array_key_exists($matches[1], self::$windows_map) ? self::$windows_map[$matches[1]]
                    : $matches[1];

                return;
            }

            if (preg_match('/Windows [0-9\.]+/', $this->os->name)) {
                return;
            }
        }

        if ($this->os->setRegex('/PPC.+OS X ([0-9\._]+)/', 'Mac OS X')) {
            $this->os->version = str_replace('_', '.', $this->os->version);

            return;
        }
        if ($this->os->setRegex('/PPC.+OS X/', 'Mac OS X')) {
            return;
        }
        if ($this->os->setRegex('/Intel Mac OS X ([0-9\._]+)/', 'Mac OS X', 1)) {
            $this->os->version = str_replace('_', '.', $this->os->version);

            return;
        }
        if ($this->os->setContains('Mac_PowerPC', 'Mac OS X')) {
            return;
        }
        if ($this->os->setContains('CrOS', 'Chrome OS')) {
            return;
        }
        if ($this->os->name != '') {
            return;
        }
        // Last ditch efforts
        if (strpos($this->ua, 'Linux') !== false || strpos($this->ua, 'X11') !== false) {
            $this->os->name = 'Linux';

            return;
        }
    }
}