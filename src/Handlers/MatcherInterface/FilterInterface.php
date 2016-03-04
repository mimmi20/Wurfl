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

namespace Wurfl\Handlers\MatcherInterface;

/**
 * \Wurfl\Handlers\FilterInterface is the base interface that concrete classes
 * must implement to classify the devices by user agent and then persist
 * the resulting datastructures.
 *
 * @category   WURFL
 *
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */

interface FilterInterface
{
    /**
     * The filter() method is used to classify devices based on patterns
     * in their user agents.
     *
     * @param string $userAgent User Agent of the device
     * @param string $deviceID  id of the the device
     *
     * @return boolean
     */
    public function filter($userAgent, $deviceID);

    /**
     * The persistData() method is resposible to
     * saving the classification output(associative arrays that holds <userAgent, deviceID> pair))
     *
     * @return void
     */
    public function persistData();
}
