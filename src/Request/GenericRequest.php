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
 * @package    WURFL
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */

namespace Wurfl\Request;

/**
 * Generic WURFL Request object containing User Agent, UAProf and xhtml device data; its id
 * property is the MD5 hash of the user agent
 *
 * @package    WURFL_Request
 *
 * @property string                   $userAgent
 * @property string                   $userAgentNormalized
 * @property string                   $userAgentProfile
 * @property boolean                  $xhtmlDevice true if the device is known to be XHTML-MP compatible
 * @property string                   $id          Unique ID used for caching: MD5($userAgent)
 * @property \Wurfl\Request\MatchInfo $matchInfo   Information about the match (available after matching)
 * @property array                    $userAgentsWithDeviceID
 */
class GenericRequest
{
    const MAX_HTTP_HEADER_LENGTH = 512;

    /**
     * @var array
     */
    private $request;

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
     * @param array  $request Original HTTP headers
     * @param string $userAgent
     * @param string $userAgentProfile
     * @param string $xhtmlDevice
     */
    public function __construct(array $request, $userAgent, $userAgentProfile = null, $xhtmlDevice = null)
    {
        $this->request                = $this->sanitizeHeaders($request);
        $this->userAgent              = $this->sanitizeHeaders($userAgent);
        $this->userAgentProfile       = $this->sanitizeHeaders($userAgentProfile);
        $this->xhtmlDevice            = $xhtmlDevice;
        $this->id                     = md5($userAgent);
        $this->matchInfo              = new MatchInfo();
        $this->userAgentsWithDeviceID = null;
    }

    /**
     * @param array|string $headers
     *
     * @return array|string
     */
    protected function sanitizeHeaders($headers)
    {
        if (!is_array($headers)) {
            return $this->truncateHeader($headers);
        }

        foreach ($headers as $header => $value) {
            $headers[$header] = $this->truncateHeader($value);
        }

        return $headers;
    }

    /**
     * @param string $header
     *
     * @return string
     */
    private function truncateHeader($header)
    {
        if (strpos($header, 'HTTP_') !== 0 || strlen($header) <= self::MAX_HTTP_HEADER_LENGTH) {
            return $header;
        }

        return substr($header, 0, self::MAX_HTTP_HEADER_LENGTH);
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->$name;
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return GenericRequest
     */
    public function __set($name, $value)
    {
        $this->$name = $value;

        return $this;
    }

    /**
     * Get the original HTTP header value from the request
     *
     * @param string $name
     *
     * @return string
     */
    public function getOriginalHeader($name)
    {
        return array_key_exists($name, $this->request) ? $this->request[$name] : null;
    }

    /**
     * Checks if the original HTTP header is set in the request
     *
     * @param string $name
     *
     * @return boolean
     */
    public function originalHeaderExists($name)
    {
        return array_key_exists($name, $this->request);
    }
}
