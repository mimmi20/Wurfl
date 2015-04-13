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

namespace Wurfl\VirtualCapability\Group;

use Wurfl\CustomDevice;
use Wurfl\Request\GenericRequest;
use Wurfl\VirtualCapability\VirtualCapability;

/**
 * Virtual capability helper
 *
 * @package    \Wurfl\VirtualCapability\VirtualCapability
 */
class ManualGroupChild
    extends VirtualCapability
{
    /**
     * @var bool
     */
    protected $useCaching = false;

    /**
     * @var CustomDevice
     */
    protected $manualValue;

    /**
     * @var Group
     */
    protected $group;

    /**
     * @param CustomDevice   $device
     * @param GenericRequest $request
     * @param Group          $group
     * @param null           $value
     */
    public function __construct(CustomDevice $device, GenericRequest $request, Group $group, $value = null)
    {
        $this->group = $group;
        parent::__construct($device, $request);
        $this->manualValue = $value;
    }

    /**
     * @return mixed|null|CustomDevice
     */
    public function compute()
    {
        return $this->manualValue;
    }

    /**
     * @return bool
     */
    public function hasRequiredCapabilities()
    {
        return $this->group->hasRequiredCapabilities();
    }

    /**
     * @return array
     */
    public function getRequiredCapabilities()
    {
        return $this->group->getRequiredCapabilities();
    }
}
