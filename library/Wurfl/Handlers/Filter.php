<?php
namespace Wurfl\Handlers;

    /**
     * Copyright (c) 2012 ScientiaMobile, Inc.
     * This program is free software: you can redistribute it and/or modify
     * it under the terms of the GNU Affero General Public License as
     * published by the Free Software Foundation, either version 3 of the
     * License, or (at your option) any later version.
     * Refer to the COPYING.txt file distributed with this package.
     *
     * @category   WURFL
     * @package    \Wurfl\Handlers
     * @copyright  ScientiaMobile, Inc.
     * @license    GNU Affero General Public License
     * @version    $id$
     */
use Wurfl\Xml\ModelDevice;

/**
 * \Wurfl\Handlers_Filter is the base interface that concrete classes
 * must implement to classify the devices by user agent and then persist
 * the resulting datastructures.
 *
 * @category   WURFL
 * @package    \Wurfl\Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */

interface Filter
{
    /**
     * The filter() method is used to classify devices based on patterns
     * in their user agents.
     *
     * @param \Wurfl\Xml\ModelDevice $device
     *
     * @return
     */
    public function filter(ModelDevice $device);

    /**
     * The persistData() method is resposible to
     * saving the classification output(associative arrays that holds <userAgent, deviceID> pair))

     */
    public function persistData();
}