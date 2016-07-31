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
        'advertised_device_os'         => 'DeviceBrowserGroup',
        'advertised_device_os_version' => 'DeviceBrowserGroup',
        'advertised_browser'           => 'DeviceBrowserGroup',
        'advertised_browser_version'   => 'DeviceBrowserGroup',
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
     * @deprecated
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

        foreach (self::$virtualCapabilities as $capabilityName => $vcName) {
            // Individual capability
            $class = '\\Wurfl\\VirtualCapability\\Capability\\' . $vcName;

            /** @var $model \Wurfl\VirtualCapability\VirtualCapability */
            $model = new $class();
            $caps  = array_merge(
                $caps,
                $model->getRequiredCapabilities(),
                array(self::PREFIX_CONTROL . $capabilityName)
            );
            unset($model);
        }

        return array_unique($caps);
    }

    /**
     * Returns an array of all the control capabilities
     * @return array
     */
    public static function getControlCapabilities()
    {
        $caps = array();

        foreach (array_keys(self::$virtualCapabilities) as $capabilityName) {
            $caps[] = self::PREFIX_CONTROL . $capabilityName;
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

        foreach (array_keys(self::$virtualCapabilities) as $name) {
            $all[$name] = $this->get($name);
        }

        return $all;
    }

    /**
     * Returns the value of the virtual capability
     *
     * @param string $name
     *
     * @return bool|float|int|string
     * @throws \Wurfl\VirtualCapability\Exception
     */
    public function get($name)
    {
        if (!isset(self::$virtualCapabilities[$name])) {
            throw new Exception('Virtual capability "' . $name . '" does not exist in WURFL');
        }

        $controlValue = $this->getControlValue($name);

        $value = $controlValue;

        switch ($controlValue) {
            case null:
                // break intentionally omitted
            case self::WURFL_CONTROL_DEFAULT:
                // The value is null if it is not in the loaded WURFL, it's default if it is loaded and not overridden
                // The control capability was not used, use the \Wurfl\VirtualCapability\VirtualCapability provider
                $value = $this->getObject($name)->getValue();

                if (is_array($value)) {
                    $value = $value[$name];
                }

                if (null === $value) {
                    $value = $controlValue;
                }

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
            // Individual capability
            $class              = '\\Wurfl\\VirtualCapability\\Capability\\' . self::$virtualCapabilities[$name];
            $this->cache[$name] = new $class($this->device, $this->request);
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
            return null;
        }

        $controlCap = self::PREFIX_CONTROL . $name;

        // Check if loaded WURFL contains the requested control cap
        if (!$this->device->getRootDevice()->isCapabilityDefined($controlCap)) {
            return null;
        }

        return $this->device->getCapability($controlCap);
    }
}
