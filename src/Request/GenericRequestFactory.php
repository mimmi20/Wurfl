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

use Wurfl\Utils;

/**
 * Creates a Generic WURFL Request from the raw HTTP Request
 *
 * @package    WURFL_Request
 */
class GenericRequestFactory
{
    /**
     * Creates Generic Request from the given HTTP Request (normally $_SERVER)
     *
     * @param array $request HTTP Request
     * @param bool  $override_sideloaded_browser_ua
     *
     * @return \Wurfl\Request\GenericRequest
     */
    public function createRequest(array $request, $override_sideloaded_browser_ua = true)
    {
        $userAgent        = Utils::getUserAgent($request, $override_sideloaded_browser_ua);
        $userAgentProfile = Utils::getUserAgentProfile($request);
        $isXhtmlDevice    = Utils::isXhtmlRequester($request);

        return new GenericRequest($request, $userAgent, $userAgentProfile, $isXhtmlDevice);
    }

    /**
     * Create a Generic Request from the given $userAgent
     *
     * @param string $userAgent
     *
     * @return \Wurfl\Request\GenericRequest
     */
    public function createRequestForUserAgent($userAgent)
    {
        $request = array('HTTP_USER_AGENT' => $userAgent);

        return new GenericRequest($request, $userAgent, null, false);
    }
}
