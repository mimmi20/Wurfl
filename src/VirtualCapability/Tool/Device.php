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
 * @category   WURFL
 *
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */

namespace Wurfl\VirtualCapability\Tool;

use Wurfl\Request\GenericRequest;

/**
 */
class Device
{
    /**
     * @var \Wurfl\VirtualCapability\Tool\NameVersionPair
     */
    private $browser;

    /**
     * @var \Wurfl\VirtualCapability\Tool\NameVersionPair
     */
    private $os;

    /**
     * @var \Wurfl\Request\GenericRequest
     */
    private $httpRequest;

    /**
     * Device user agent string
     *
     * @var string
     */
    private $deviceUa;

    /**
     * Device user agent string normalized
     *
     * @var string
     */
    private $deviceUaNormalized;

    /**
     * Browser user agent string
     *
     * @var string
     */
    private $browserUa;

    /**
     * Browser user agent string normalized
     *
     * @var string
     */
    private $browserUaNormalized;

    /**
     * @param \Wurfl\Request\GenericRequest $request
     */
    public function __construct(GenericRequest $request)
    {
        $this->httpRequest = $request;

        $this->deviceUa            = $this->httpRequest->getDeviceUserAgent();
        $this->browserUa           = $this->httpRequest->getBrowserUserAgent();
        $this->deviceUaNormalized  = $this->httpRequest->getUserAgentNormalized();
        $this->browserUaNormalized = $this->deviceUaNormalized;

        $this->browser = new NameVersionPair($this);
        $this->os      = new NameVersionPair($this);
    }

    /**
     * @return \Wurfl\VirtualCapability\Tool\NameVersionPair
     */
    public function getBrowser()
    {
        return $this->browser;
    }

    /**
     * @return \Wurfl\VirtualCapability\Tool\NameVersionPair
     */
    public function getOs()
    {
        return $this->os;
    }

    /**
     * @return \Wurfl\Request\GenericRequest
     */
    public function getHttpRequest()
    {
        return $this->httpRequest;
    }

    /**
     * @return string
     */
    public function getDeviceUa()
    {
        return $this->deviceUa;
    }

    /**
     * @return string
     */
    public function getDeviceUaNormalized()
    {
        return $this->deviceUaNormalized;
    }

    /**
     * @return string
     */
    public function getBrowserUa()
    {
        return $this->browserUa;
    }

    /**
     * @return string
     */
    public function getBrowserUaNormalized()
    {
        return $this->browserUaNormalized;
    }
}
