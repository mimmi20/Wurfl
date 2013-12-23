<?php
namespace Wurfl\Request;

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
     * @package    WURFL_Request
     * @copyright  ScientiaMobile, Inc.
     * @license    GNU Affero General Public License
     * @author     Fantayeneh Asres Gizaw
     * @version    $id$
     */
/**
 * Generic WURFL Request object containing User Agent, UAProf and xhtml device data; its id
 * property is the MD5 hash of the user agent
 *
 * @package    WURFL_Request
 *
 * @property string                   $userAgent
 * @property string                   $userAgentProfile
 * @property boolean                  $xhtmlDevice true if the device is known to be XHTML-MP compatible
 * @property string                   $id          Unique ID used for caching: MD5($userAgent)
 * @property \Wurfl\Request\MatchInfo $matchInfo   Information about the match (available after matching)
 */
class GenericRequest
{
    /**
     * @var string
     */
    private $userAgent;

    /**
     * @var null|string
     */
    private $userAgentProfile;

    /**
     * @var null|string
     */
    private $xhtmlDevice;

    /**
     * @var string
     */
    private $id;

    /**
     * @var MatchInfo
     */
    private $matchInfo;

    /**
     * @var array|null
     */
    private $userAgentsWithDeviceID;

    /**
     * @param string $userAgent
     * @param string $userAgentProfile
     * @param string $xhtmlDevice
     */
    public function __construct($userAgent, $userAgentProfile = null, $xhtmlDevice = null)
    {
        $this->userAgent              = $userAgent;
        $this->userAgentProfile       = $userAgentProfile;
        $this->xhtmlDevice            = $xhtmlDevice;
        $this->id                     = md5($userAgent);
        $this->matchInfo              = new MatchInfo();
        $this->userAgentsWithDeviceID = null;
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->$name;
    }

    /**
     * @param $name
     * @param $value
     *
     * @return GenericRequest
     */
    public function __set($name, $value)
    {
        $this->$name = $value;

        return $this;
    }
}
