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
 * @package WURFL_VirtualCapability
 */
class WURFL_VirtualCapability_DeviceBrowserGroup extends WURFL_VirtualCapability_Group {

	protected $required_capabilities = array();

	protected $storage = array(
		'DeviceOs' => null,
		'DeviceOsVersion' => null,
		'Browser' => null,
		'BrowserVersion' => null,
	);

	/**
	 * @var WURFL_VirtualCapability_UserAgentTool
	*/
	protected static $ua_tool;

	public function compute() {
		if (self::$ua_tool === null) {
			self::$ua_tool = new WURFL_VirtualCapability_UserAgentTool();
		}

		// Run the UserAgentTool to get the relevant details
		$device = self::$ua_tool->getDevice($this->request->userAgent);

		$this->storage['DeviceOs'] 			= new WURFL_VirtualCapability_ManualGroupChild($this->device, $this->request, $this, $device->os->name);
		$this->storage['DeviceOsVersion'] 	= new WURFL_VirtualCapability_ManualGroupChild($this->device, $this->request, $this, $device->os->version);
		$this->storage['Browser'] 			= new WURFL_VirtualCapability_ManualGroupChild($this->device, $this->request, $this, $device->browser->name);
		$this->storage['BrowserVersion'] 	= new WURFL_VirtualCapability_ManualGroupChild($this->device, $this->request, $this, $device->browser->version);
	}
}
