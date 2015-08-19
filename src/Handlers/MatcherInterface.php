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
 * @package    WURFL
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */

namespace Wurfl\Handlers;

use Wurfl\Request\GenericRequest;

/**
 * \Wurfl\Handlers\MatcherInterface is the base interface that concrete classes
 * must implement to retrieve a device with the given request
 *
 * @category   WURFL
 * @package    WURFL
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */
interface MatcherInterface
{
    /**
     * Returns a matching device id for the given request,
     * if no matching device is found will return 'generic'
     *
     * @param GenericRequest $request
     *
     * @return string Matching device id
     */
    public function match(GenericRequest $request);
}
