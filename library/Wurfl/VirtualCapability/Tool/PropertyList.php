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
class PropertyList
{
    /**
     * @var \Wurfl\VirtualCapability\Tool\Device
     */
    protected $device;

    public function __construct($device)
    {
        $this->device = $device;
    }

    public function set()
    {
        return true;
    }
}