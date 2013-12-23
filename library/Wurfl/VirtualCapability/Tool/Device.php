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
class Device
{
    /**
     * @var NameVersionPair
     */
    public $browser;

    /**
     * @var NameVersionPair
     */
    public $platform;

    /**
     * @var string
     */
    public $userAgent;

    /**
     * @var string
     */
    public $userAgentLower;

    /**
     * @param string $userAgent
     */
    public function __construct($userAgent)
    {
        $this->userAgent       = $userAgent;
        $this->userAgentLower = strtolower($userAgent);
        $this->browser  = new NameVersionPair($this);
        $this->platform       = new NameVersionPair($this);
    }

    /**
     * @var array
     */
    protected static $windowsMap
        = array(
            '4.0' => 'NT 4.0',
            '5.0' => '2000',
            '5.1' => 'XP',
            '5.2' => 'XP',
            '6.0' => 'Vista',
            '6.1' => '7',
            '6.2' => '8',
            '6.3' => '8.1',
        );

    /**
     *
     */
    public function normalize()
    {
        $this->normalizeOS();
    }

    /**
     *
     */
    protected function normalizeOS()
    {
        if (strpos($this->userAgent, 'Windows') !== false) {
            if (preg_match('/Windows NT ([0-9]\.[0-9])/', $this->platform->name, $matches)) {
                $this->platform->name    = "Windows";
                $this->platform->version = array_key_exists($matches[1], self::$windowsMap)
                    ? self::$windowsMap[$matches[1]]
                    : $matches[1];
                return;
            }

            if (preg_match('/Windows [0-9\.]+/', $this->platform->name)) {
                return;
            }
        }

        if ($this->platform->setRegex('/PPC.+OS X ([0-9\._]+)/', 'Mac OS X')) {
            $this->platform->version = str_replace('_', '.', $this->platform->version);
            return;
        }

        if ($this->platform->setRegex('/PPC.+OS X/', 'Mac OS X')) {
            return;
        }

        if ($this->platform->setRegex('/Intel Mac OS X ([0-9\._]+)/', 'Mac OS X', 1)) {
            $this->platform->version = str_replace('_', '.', $this->platform->version);
            return;
        }

        if ($this->platform->setContains('Mac_PowerPC', 'Mac OS X')) {
            return;
        }

        if ($this->platform->setContains('CrOS', 'Chrome OS')) {
            return;
        }

        if ($this->platform->name != '') {
            return;
        }

        // Last ditch efforts
        if (strpos($this->userAgent, 'Linux') !== false || strpos($this->userAgent, 'X11') !== false) {
            $this->platform->name = 'Linux';
            return;
        }
    }
}
