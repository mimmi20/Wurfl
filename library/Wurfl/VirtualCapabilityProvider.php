<?php
namespace Wurfl;

    /**
     * Copyright (c) 2012 ScientiaMobile, Inc.
     * This program is free software: you can redistribute it and/or modify
     * it under the terms of the GNU Affero General Public License as
     * published by the Free Software Foundation, either version 3 of the
     * License, or (at your option) any later version.
     * Refer to the COPYING.txt file distributed with this package.
     *
     * @category   WURFL
     * @package    WURFL
     * @copyright  ScientiaMobile, Inc.
     * @license    GNU Affero General Public License
     * @version    $id$
     */
/**
 * Provides access to virtual capabilities
 *
 * @package    WURFL
 */
class VirtualCapabilityProvider
{
    const PREFIX_VIRTUAL        = '';
    const PREFIX_CONTROL        = 'controlcap_';
    const WURFL_CONTROL_GROUP   = 'virtual';
    const WURFL_CONTROL_DEFAULT = 'default';

    /**
     * @var CustomDevice
     */
    private $device;

    /**
     * @var Request\GenericRequest
     */
    private $request;

    public function __construct(CustomDevice $device, Request\GenericRequest $request)
    {
        $this->device  = $device;
        $this->request = $request;
    }

    /**
     * Map of WURFL names to VirtualCapability classes.
     *
     * @var array
     */
    public static $virtual_capabilities = array(
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
    );

    /**
     * Storage for the WURFL_VirtualCapability objects
     *
     * @var array
     */
    protected $cache = array();

    /**
     * Storage for the WURFL_VirtualCapabilityCache objects
     *
     * @var array
     */
    protected $group_cache = array();

    /**
     * Returns the names of all the available virtual capabilities
     *
     * @return array
     */
    public function getNames()
    {
        return array_keys(self::$virtual_capabilities);
    }

    /**
     * Returns an array of all the required capabilities for all virtual capabilities
     *
     * @return array
     */
    public static function getRequiredCapabilities()
    {
        $caps = array();

        foreach (self::$virtual_capabilities as $vc_name) {
            if (strpos($vc_name, '.') !== false) {
                // Group of capabilities
                list($group, $property) = explode('.', $vc_name);
                $class = 'VirtualCapability\\Groups\\' . $group . 'Group';
            } else {
                // Individual capability
                $class = 'VirtualCapability\\' . $vc_name;
            }

            /** @var $model VirtualCapability */
            $model = new $class();
            $caps  = array_unique(array_merge($caps, $model->getRequiredCapabilities()));
            unset($model);
        }

        return $caps;
    }

    /**
     * Gets an array of all the virtual capabilities
     *
     * @return array Virtual capabilities in format "name => value"
     */
    public function getAll()
    {
        $all = array();

        foreach ($this->getNames() as $name) {
            $all[self::PREFIX_VIRTUAL . $name] = $this->get($name);
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
        if (!in_array($name, $this->getNames())) {
            return null;
        }

        $controlValue = $this->getControlValue($name);
        $returnValue  = null;

        switch ($controlValue) {
            /*
             * The value is null if it is not in the loaded WURFL, 
             * it's default if it is loaded and not overridden
             */
            case null:
                // break omitted
            case self::WURFL_CONTROL_DEFAULT:
                /*
                 * The control capability was not used, 
                 * -> use the \Wurfl\VirtualCapability provider
                 */
                $returnValue = $this->getObject($name)->getValue();
                break;
            case 'force_true':
                // Forced capabilities
                $returnValue = true;
                break;
            case 'force_false':
                // Forced capabilities
                $returnValue = false;
                break;
            default:
                // Use the control value from WURFL
                $returnValue = $controlValue;
                break;
        }

        return $returnValue;
    }

    /**
     * Returns the WURFL_VirtualCapability object for the given $name.
     *
     * @param string $name
     *
     * @return VirtualCapability
     */
    public function getObject($name)
    {
        $name = $this->cleanCapabilityName($name);

        if (!array_key_exists($name, $this->cache)) {
            if (($pos = strpos(self::$virtual_capabilities[$name], '.')) !== false) {
                // Group of capabilities
                list($group, $property) = explode('.', self::$virtual_capabilities[$name]);

                if (!array_key_exists($group, $this->group_cache)) {
                    $class = '\\Wurfl\\VirtualCapability\\Groups\\' . $group . 'Group';
                    // Cache the group
                    $this->group_cache[$group] = new $class($this->device, $this->request);
                    $this->group_cache[$group]->compute();
                }

                // Cache the capability
                $this->cache[$name] = $this->group_cache[$group]->get($property);
            } else {
                // Individual capability
                $class              = '\\Wurfl\\VirtualCapability\\' . self::$virtual_capabilities[$name];
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
     * @return boolean
     */
    public function exists($name)
    {
        return array_key_exists($this->cleanCapabilityName($name), self::$virtual_capabilities);
    }

    protected function getControlValue($name)
    {
        // Check if loaded WURFL contains control caps
        if (!$this->device->getRootDevice()->isGroupDefined(self::WURFL_CONTROL_GROUP)) {
            return null;
        }

        $control_cap = self::PREFIX_CONTROL . $this->cleanCapabilityName($name);

        // Check if loaded WURFL contains the requested control cap
        if (!$this->device->getRootDevice()->isCapabilityDefined($control_cap)) {
            return null;
        }

        return $this->device->getCapability($control_cap);
    }

    protected function cleanCapabilityName($name)
    {
        return str_replace(self::PREFIX_VIRTUAL, '', $name);
    }
}