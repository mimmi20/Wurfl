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

namespace Wurfl\VirtualCapability;

use Wurfl\CustomDevice;
use Wurfl\Request\GenericRequest;

/**
 * Provides access to virtual capabilities
 */
class VirtualCapabilityProvider
{
    /**
     * @var string
     */
    const PREFIX_CONTROL = 'controlcap_';

    /**
     * @var string
     */
    const WURFL_CONTROL_GROUP = 'virtual';

    /**
     * @var string
     */
    const WURFL_CONTROL_DEFAULT = 'default';

    /**
     * @var \Wurfl\CustomDevice
     */
    private $device = null;

    /**
     * @var \Wurfl\Request\GenericRequest
     */
    private $request = null;

    /**
     * Map of WURFL names to \Wurfl\VirtualCapability\VirtualCapability classes.
     *
     * @var array
     */
    private static $virtualCapabilities = array(
        'is_android'                   => 'IsAndroid',
        'is_ios'                       => 'IsIos',
        'is_windows_phone'             => 'IsWindowsPhone',
        'is_app'                       => 'IsApp',
        'is_full_desktop'              => 'IsFullDesktop',
        'is_largescreen'               => 'IsLargescreen',
        'is_mobile'                    => 'IsMobile',
        'is_robot'                     => 'IsRobot',
        'is_smartphone'                => 'IsSmartphone',
        'is_touchscreen'               => 'IsTouchscreen',
        'is_wml_preferred'             => 'IsWmlPreferred',
        'is_xhtmlmp_preferred'         => 'IsXhtmlmpPreferred',
        'is_html_preferred'            => 'IsHtmlPreferred',
        'advertised_device_os'         => 'DeviceBrowser.DeviceOs',
        'advertised_device_os_version' => 'DeviceBrowser.DeviceOsVersion',
        'advertised_browser'           => 'DeviceBrowser.Browser',
        'advertised_browser_version'   => 'DeviceBrowser.BrowserVersion',
        'complete_device_name'         => 'CompleteDeviceName',
        'device_name'                  => 'DeviceName',
        'form_factor'                  => 'FormFactor',
        'is_phone'                     => 'IsPhone',
        'is_app_webview'               => 'IsAppWebview',
    );

    /**
     * Storage for the \Wurfl\VirtualCapability\VirtualCapability objects
     *
     * @var array
     */
    private $cache = array();

    /**
     * Storage for the \Wurfl\VirtualCapability\VirtualCapabilityCache objects
     *
     * @var array
     */
    private $groupCache = array();

    /**
     * @param \Wurfl\CustomDevice           $device
     * @param \Wurfl\Request\GenericRequest $request
     */
    public function __construct(CustomDevice $device, GenericRequest $request)
    {
        $this->device  = $device;
        $this->request = $request;
    }

    /**
     * Returns the names of all the available virtual capabilities
     *
     * @return array
     */
    public function getNames()
    {
        return array_keys(self::$virtualCapabilities);
    }

    /**
     * Returns an array of all the required capabilities for all virtual capabilities
     *
     * @return array
     */
    public static function getRequiredCapabilities()
    {
        $caps = array();

        foreach (self::$virtualCapabilities as $vcName) {
            if (strpos($vcName, '.') !== false) {
                // Group of capabilities
                $parts = explode('.', $vcName);
                $group = $parts[0];
                $class = '\\Wurfl\\VirtualCapability\\Group\\' . $group . 'Group';
            } else {
                // Individual capability
                $class = '\\Wurfl\\VirtualCapability\\Single\\' . $vcName;
            }

            /** @var $model \Wurfl\VirtualCapability\VirtualCapability */
            $model = new $class();
            $caps  = array_unique(array_merge($caps, $model->getRequiredCapabilities()));
            unset($model);
        }

        return $caps;
    }

    /**
     * Gets an array of all the virtual capabilities
     *
     * @return array Virtual capabilities in format 'name => value'
     */
    public function getAll()
    {
        $all = array();

        foreach ($this->getNames() as $name) {
            $all[$name] = $this->get($name);
        }

        return $all;
    }

    /**
     * Returns the value of the virtual capability
     *
     * @param string $name
     *
     * @return string|bool|int|float
     */
    public function get($name)
    {
        $controlValue = $this->getControlValue($name);

        $value = $controlValue;

        switch ($controlValue) {
            case null:
                // break intentionally omitted
            case self::WURFL_CONTROL_DEFAULT:
                // The value is null if it is not in the loaded WURFL, it's default if it is loaded and not overridden
                // The control capability was not used, use the \Wurfl\VirtualCapability\VirtualCapability provider
                $value = $this->getObject($name)->getValue();
                break;
            case 'force_true':
                $value = true;
                break;
            case 'force_false':
                $value = false;
                break;
            default:
                // nothing to do here
                break;
        }

        // Use the control value from WURFL
        return $value;
    }

    /**
     * Returns the \Wurfl\VirtualCapability\VirtualCapability object for the given $name.
     *
     * @param string $name
     *
     * @return \Wurfl\VirtualCapability\VirtualCapability
     */
    public function getObject($name)
    {
        if (!array_key_exists($name, $this->cache)) {
            if (strpos(self::$virtualCapabilities[$name], '.') !== false) {
                // Group of capabilities
                list($group, $property) = explode('.', self::$virtualCapabilities[$name]);

                if (!array_key_exists($group, $this->groupCache)) {
                    $class = '\\Wurfl\\VirtualCapability\\Group\\' . $group . 'Group';
                    // Cache the group

                    /** @var \Wurfl\VirtualCapability\Group\Group $groupClass */
                    $groupClass = new $class($this->device, $this->request);
                    $groupClass->compute();
                    $this->groupCache[$group] = $groupClass;
                } else {
                    /** @var \Wurfl\VirtualCapability\Group\Group $groupClass */
                    $groupClass = $this->groupCache[$group];
                }

                $value = $groupClass->get($property);

                // Cache the capability
                $this->cache[$name] = $value;
            } else {
                // Individual capability
                $class              = '\\Wurfl\\VirtualCapability\\Single\\' . self::$virtualCapabilities[$name];
                $this->cache[$name] = new $class($this->device, $this->request);
            }
        }

        return $this->cache[$name];
    }

    /**
     * True if the virtual capability exists
     *
     * @param string $name
     *
     * @return bool
     */
    public function exists($name)
    {
        return array_key_exists($name, self::$virtualCapabilities);
    }

    /**
     * @param string $name
     *
     * @return null|string
     */
    private function getControlValue($name)
    {
        // Check if loaded WURFL contains control caps
        if (!$this->device->getRootDevice()->isGroupDefined(self::WURFL_CONTROL_GROUP)) {
            return;
        }

        $controlCap = self::PREFIX_CONTROL . $name;

        // Check if loaded WURFL contains the requested control cap
        if (!$this->device->getRootDevice()->isCapabilityDefined($controlCap)) {
            return;
        }

        return $this->device->getCapability($controlCap);
    }
}
