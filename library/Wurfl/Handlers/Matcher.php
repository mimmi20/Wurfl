<?php
declare(ENCODING = 'utf-8');
namespace Wurfl\Handlers;

/**
 * Copyright(c) 2011 ScientiaMobile, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or(at your option) any later version.
 *
 * Refer to the COPYING file distributed with this package.
 *
 *
 * @category   WURFL
 * @package    WURFL
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version   SVN: $Id$
 */

/**
 * Matcher is the base interface that concrete classes 
 * must implement to retrieve a device with the given request    
 *
 * @category   WURFL
 * @package    WURFL
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version   SVN: $Id$
 */
interface Matcher
{
    
    /**
     * Returns a matching device id for the given request, 
     * if no matching device is found will return 'generic'
     * 
     * @param \Wurfl\Request\GenericRequest $request
     * @return string Matching device id
     */
    public function match(\Wurfl\Request\GenericRequest $request);
    
}

