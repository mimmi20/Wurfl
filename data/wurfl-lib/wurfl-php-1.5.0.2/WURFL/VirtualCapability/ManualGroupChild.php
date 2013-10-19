<?php
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
 * @package	WURFL_VirtualCapability
 * @copyright  ScientiaMobile, Inc.
 * @license	GNU Affero General Public License
 * @version	$id$
 */
/**
 * Virtual capability helper
 * @package	WURFL_VirtualCapability
 */
 
class WURFL_VirtualCapability_ManualGroupChild extends WURFL_VirtualCapability {
	protected $use_caching = false;
	protected $manual_value;
	/**
	 * @var WURFL_VirtualCapabilityGroup
	 */
	protected $group;

	public function __construct(WURFL_CustomDevice $device, WURFL_Request_GenericRequest $request, WURFL_VirtualCapability_Group $group, $value=null) {
		$this->group = $group;
		parent::__construct($device, $request);
		$this->manual_value = $value;
	}

	public function compute() {
		return $this->manual_value;
	}

	public function hasRequiredCapabilities() {
		return $this->group->hasRequiredCapabilities();
	}

	public function getRequiredCapabilities() {
		return $this->group->getRequiredCapabilities();
	}
}