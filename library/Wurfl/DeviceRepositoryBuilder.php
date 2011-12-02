<?php
declare(ENCODING = 'utf-8');
namespace Wurfl;

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
 * @category   WURFL
 * @package    WURFL
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 * 
 */
/**
 * Builds a WURFL_DeviceRepository
 * @package    WURFL
 */
class DeviceRepositoryBuilder
{
    /**
     * @var WURFL_Xml_PersistenceProvider
     */
    private $_persistenceProvider;
    /**
     * @var WURFL_UserAgentHandlerChain
     */
    private $_userAgentHandlerChain;
    /**
     * @var WURFL_Xml_DevicePatcher
     */
    private $_devicePatcher;

    /**
     * Filename of lockfile to prevent concurrent DeviceRepository builds
     * @var string
     */
    private $_lockFile;
    /**
     * Determines the fopen() mode that is used on the lockfile 
     * @var string
     */
    private $_lockStyle = 'r';
    
    /**
     * @param WURFL_Xml_PersistenceProvider $persistenceProvider
     * @param WURFL_UserAgentHandlerChain $userAgentHandlerChain
     * @param WURFL_Xml_DevicePatcher $devicePatcher
     */
    public function __construct($persistenceProvider, $userAgentHandlerChain, $devicePatcher)
    {
        $this->_persistenceProvider   = $persistenceProvider;
        $this->_userAgentHandlerChain = $userAgentHandlerChain;
        $this->_devicePatcher         = $devicePatcher;
        
        if (strpos(PHP_OS, 'SunOS') === false) {
            $this->_lockFile = dirname(__FILE__) . '/DeviceRepositoryBuilder.php';
        } else {
            // Solaris can't handle exclusive file locks on files unless they are opened for RW
            $this->_lockStyle = 'w+';
            
            if (function_exists('sys_get_temp_dir')) {
                $this->_lockFile = realpath(sys_get_temp_dir());
            }
            $this->_lockFile = $this->_lockFile ? $this->_lockFile . '/wurfl.lock' : '/tmp/wurfl.lock';
        }
    }
    
    /**
     * Builds DeviceRepository in PersistenceProvider from $wurflFile and $wurflPatches using $capabilitiesToUse 
     * @param string $wurflFile Filename of wurfl.xml or other complete WURFL file
     * @param array $wurflPatches Array of WURFL patch files
     * @param array $capabilitiesToUse Array of capabilities to be included in the DeviceRepository
     * @return WURFL_CustomDeviceRepository
     */
    public function build($wurflFile, array $wurflPatches = array(), array $capabilitiesToUse = array())
    {
        if (!$this->_isRepositoryBuilt()) {
            if (!file_exists($wurflFile) || !is_readable($wurflFile)) {
                throw new \InvalidArgumentException('cannot locate[' . $inputFile . '] file or the file is not readable');
            }
            
            $xmlInfos     = \simplexml_load_string(\file_get_contents($wurflFile));
            $versionInfos = $xmlInfos->xpath('/wurfl/version');
            $versionInfos = $versionInfos[0];
            
            $info = new Xml\Info(
                (string)$versionInfos->ver, 
                (string)$versionInfos->last_updated, 
                (string)$versionInfos->official_url
            );
            
            $this->_persistenceProvider->save(Xml\Info::PERSISTENCE_KEY, $info);
            
            $devices = $this->_loadDevices($wurflFile);
            
            // Patches
            foreach ($wurflPatches as $patchFilePath) {
                $patchedDevices = $this->_loadDevices($patchFilePath);
                
                foreach ($patchedDevices as $patchKey => $patchedDevice) {
                    if (!array_key_exists($patchKey, $devices)) {
                        $devices[$patchKey] = $patchedDevice;
                    } else {
                        $devicesToPatch = array_intersect_key($devices, $patchedDevices);
                        
                        foreach ($devicesToPatch as $deviceKey => $device) {
                            $devices[$deviceKey] = $this->_devicePatcher->patch(
                                $devicesToPatch, $patchedDevice
                            );
                        }
                    }
                }
            }
            
            foreach ($devices as $deviceKey => $device) {
                while ('root' != $device->fallBack) {
                    $device = $this->_devicePatcher->merge(
                        $device, $devices[$device->fallBack]
                    );
                }
                
                $devices[$deviceKey] = $device;
            }
            
            foreach ($devices as $device) {
                $this->_persistenceProvider->save($device->id, $device);
            }
            
            $this->_setRepositoryBuilt();
        }
        
        return new CustomDeviceRepository($this->_persistenceProvider);
    }
    
    private function _loadDevices($file)
    {
        if (!file_exists($file) || !is_readable($file)) {
            throw new \InvalidArgumentException('cannot locate[' . $file . '] file or the file is not readable');
        }
        
        $xml         = \simplexml_load_string(\file_get_contents($file));
        $deviceInfos = $xml->xpath('/wurfl/devices');
        
        if (!isset($deviceInfos[0])) {
            return array();
        }
        
        $devices = array();
        
        foreach ($deviceInfos[0]->device as $deviceXml) {
            $deviceId         = (string) $deviceXml->attributes()->id;
            $userAgent        = (string) $deviceXml->attributes()->user_agent;
            $fallBack         = (string) $deviceXml->attributes()->fall_back;
            $actualDeviceRoot = (string) $deviceXml->attributes()->actual_device_root;
            $specific         = (string) $deviceXml->attributes()->specific;
            
            $groupIDCapabilitiesMap = array();
            
            foreach ($deviceXml->group as $group) {
                $groupId = (string) $group->attributes()->id;
                
                if (!array_key_exists($groupId, $groupIDCapabilitiesMap)) {
                    $groupIDCapabilitiesMap[$groupId] = array();
                }
                
                foreach ($group->capability as $capability) {
                    $name  = (string) $capability->attributes()->name;
                    $value = (string) $capability->attributes()->value;
                    
                    if (!array_key_exists($name, $groupIDCapabilitiesMap[$groupId])) {
                        $groupIDCapabilitiesMap[$groupId][$name] = $value;
                    }
                }
            }
            
            $devices[$deviceId] = new Xml\ModelDevice($deviceId, $userAgent, $fallBack, $actualDeviceRoot, $specific, $groupIDCapabilitiesMap);
        }
        
        return $devices;
    }
    
    /**
     * Returns an array of WURFL_Xml_DeviceIterator for the given $wurflPatches and $capabilitiesToUse
     * @param array $wurflPatches Array of(string)filenames
     * @param array $capabilitiesToUse Array of(string) WURFL capabilities
     * @return array Array of WURFL_Xml_DeviceIterator objects
     */
    private function _toPatchIterators($wurflPatches, $capabilitiesToUse)
    {
        $patchIterators = array();
        
        if (is_array($wurflPatches)) {
            foreach ($wurflPatches as $wurflPatch) {
                $patchIterators[] = new Xml\DeviceIterator($wurflPatch, $capabilitiesToUse);
            }
        }
        
        return $patchIterators;
    }
    
    /**
     * @return bool true if device repository is already built(WURFL is loaded in persistence proivder)
     */
    private function _isRepositoryBuilt()
    {
        return $this->_persistenceProvider->isWURFLLoaded();
    }
    
    /**
     * Marks the WURFL as loaded in the persistence provider
     * @see WURFL_Xml_PersistenceProvider_AbstractPersistenceProvider::setWURFLLoaded()
     */
    private function _setRepositoryBuilt()
    {
        $this->_persistenceProvider->setWURFLLoaded();
    }
    
    /**
     * Clears the devices from the persistence provider
     * @see WURFL_Xml_PersistenceProvider::clear()
     */
    private function _clean()
    {
        $this->_persistenceProvider->clear();
    }
    
    /**
     * Save Loaded WURFL info in the persistence provider 
     * @param Wurfl\Xml\VersionIterator $wurflInfoIterator
     */
    private function _persistWurflInfo(Xml\VersionIterator $wurflInfoIterator)
    {
        foreach ($wurflInfoIterator as $info) {
            $this->_persistenceProvider->save(Xml\Info::PERSISTENCE_KEY, $info);
        }
    }
    
    /**
     * Process device iterator
     * @param WURFL_Xml_DeviceIterator $deviceIterator
     * @param unknown_type $patchingDevices
     */
    private function _process($deviceIterator, $patchingDevices)
    {
        $usedPatchingDeviceIds = array();
        
        foreach ($deviceIterator as $device) {
            $toPatch = isset($patchingDevices[$device->id]);
            
            if ($toPatch) {
                $device = $this->_patchDevice($device, $patchingDevices[$device->id]);
                $usedPatchingDeviceIds[$device->id] = $device->id;
            }
            
            $this->_classifyAndPersistDevice($device);
        }
        $this->_classifyAndPersistNewDevices(array_diff_key($patchingDevices, $usedPatchingDeviceIds));
        $this->_persistClassifiedDevicesUserAgentMap();
    }
    
    /**
     * Save all $newDevices in the persistence provider
     * @param array $newDevices Array of WURFL_Device objects
     */
    private function _classifyAndPersistNewDevices($newDevices)
    {
        foreach($newDevices as $newDevice) {
            $this->_classifyAndPersistDevice($newDevice);
        }
    }

    /**
     * Save given $device in the persistence provider
     * @param WURFL_Device $device
     * @see WURFL_UserAgentHandlerChain::filter(), WURFL_Xml_PersistenceProvider::save()
     */
    private function _classifyAndPersistDevice($device)
    {
        $this->_userAgentHandlerChain->filter($device->userAgent, $device->id);
        $this->_persistenceProvider->save($device->id, $device);
    }
    
    /**
     * Save the User Agent Map in the UserAgentHandlerChain
     * @see WURFL_UserAgentHandlerChain::persistData()
     */
    private function _persistClassifiedDevicesUserAgentMap()
    {
        $this->_userAgentHandlerChain->persistData();
    }
    
    private function _patchDevice($device, $patchingDevice)
    {
        return $this->_devicePatcher->patch($device, $patchingDevice);
    }
    
    /**
     * @param array $patchingDeviceIterators Array of WURFL_Xml_DeviceIterators
     * @return array Merged array of current patch devices
     */
    private function _toListOfPatchingDevices($patchingDeviceIterators)
    {
        $currentPatchingDevices = array();
        
        if (is_null($patchingDeviceIterators)) {
            return $currentPatchingDevices;
        }
        
        foreach ($patchingDeviceIterators as $deviceIterator) {
            $newPatchingDevices = $this->_toArray($deviceIterator);
            $this->_patchDevices($currentPatchingDevices, $newPatchingDevices);
        }
        
        return $currentPatchingDevices;
    }
    
    /**
     * Adds the given $newPatchingDevices to the $currentPatchingDevices array
     * @param array $currentPatchingDevices REFERENCE to array of current devices to be patches
     * @param array $newPatchingDevices Array of new devices to be patched in
     */
    private function _patchDevices(&$currentPatchingDevices, $newPatchingDevices)
    {
        foreach ($newPatchingDevices as $deviceId => $newPatchingDevice) {
            if (isset($currentPatchingDevices[$deviceId])) {
                $currentPatchingDevices[$deviceId] = $this->_patchDevice($currentPatchingDevices[$deviceId], $newPatchingDevice);
            } else {
                $currentPatchingDevices[$deviceId] = $newPatchingDevice;
            }
        }
    }
    
    /**
     * Returns an array of devices in the form 'WURFL_Device::id => WURFL_Device'
     * @param WURFL_Xml_DeviceIterator $deviceIterator
     * @return array
     */
    private function _toArray($deviceIterator)
    {
        $patchingDevices = array();
        foreach ($deviceIterator as $device) {
            $patchingDevices[$device->id] = $device;
        }
        return $patchingDevices;
    }

}
