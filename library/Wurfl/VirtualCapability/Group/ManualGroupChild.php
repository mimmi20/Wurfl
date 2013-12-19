<?php
namespace Wurfl\VirtualCapability\Group;

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
 * @package    \Wurfl\VirtualCapability\VirtualCapability
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
use Wurfl\CustomDevice;
use Wurfl\Request\GenericRequest;
use Wurfl\VirtualCapability\VirtualCapability;

/**
 * Virtual capability helper
 *
 * @package    \Wurfl\VirtualCapability\VirtualCapability
 */

class ManualGroupChild extends VirtualCapability
{
    protected $use_caching = false;
    protected $manual_value;
    /**
     * @var Group
     */
    protected $group;

    public function __construct(
        CustomDevice $device, GenericRequest $request, Group $group, $value = null
    ) {
        $this->group = $group;
        parent::__construct($device, $request);
        $this->manual_value = $value;
    }

    public function compute()
    {
        return $this->manual_value;
    }

    public function hasRequiredCapabilities()
    {
        return $this->group->hasRequiredCapabilities();
    }

    public function getRequiredCapabilities()
    {
        return $this->group->getRequiredCapabilities();
    }
}