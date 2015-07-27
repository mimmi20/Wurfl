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

namespace Wurfl;

/**
 * Builds a \Wurfl\DeviceRepository
 *
 * @package    WURFL
 */
class DeviceRepositoryBuilder
{
    /**
     * @var Storage\Storage
     */
    private $persistenceProvider;

    /**
     * @var UserAgentHandlerChain
     */
    private $userAgentHandlerChain;

    /**
     * @var Xml\DevicePatcher
     */
    private $devicePatcher;

    /**
     * @param Storage\Storage       $persistenceProvider
     * @param UserAgentHandlerChain $chain
     * @param Xml\DevicePatcher     $devicePatcher
     */
    public function __construct(
        Storage\Storage $persistenceProvider,
        UserAgentHandlerChain $chain,
        Xml\DevicePatcher $devicePatcher
    ) {
        $this->persistenceProvider   = $persistenceProvider;
        $this->userAgentHandlerChain = $chain;
        $this->devicePatcher         = $devicePatcher;
    }

    /**
     * Builds DeviceRepository in PersistenceProvider from $wurflFile and $wurflPatches using $capabilityFilter
     *
     * @param string $wurflFile        Filename of wurfl.xml or other complete WURFL file
     * @param array  $wurflPatches     Array of WURFL patch files
     * @param array  $capabilityFilter Array of capabilities to be included in the DeviceRepository
     *
     * @return CustomDeviceRepository
     */
    public function build($wurflFile, array $wurflPatches = array(), array $capabilityFilter = array())
    {
        // TODO: Create a better locking solution
        if (!$this->isRepositoryBuilt()) {
            // Determine Lockfile location
            $lockFile  = FileUtils::getTempDir() . '/wurfl.lock';
            $lockStyle = 'r';

            if (!file_exists($lockFile) || strpos(PHP_OS, 'SunOS') !== false) {
                // Solaris can't handle exclusive file locks on files unless they are opened for RW
                $lockStyle = 'w+';
            }

            // Update Data
            if ($filePointer = fopen($lockFile, $lockStyle)) {
                if (flock($filePointer, LOCK_EX | LOCK_NB)) {
                    $infoIterator   = new Xml\VersionIterator($wurflFile);
                    $deviceIterator = new Xml\DeviceIterator($wurflFile, $capabilityFilter);
                    $patchIterators = $this->toPatchIterators($wurflPatches, $capabilityFilter);

                    $this->buildRepository($infoIterator, $deviceIterator, $patchIterators);
                    $this->setRepositoryBuilt();
                    flock($filePointer, LOCK_UN);
                }

                fclose($filePointer);
            }
        }

        return new CustomDeviceRepository($this->persistenceProvider, $this->deviceClassificationNames());
    }

    /**
     * Iterates over XML files and pulls relevent data
     *
     * @param Xml\VersionIterator  $wurflInfoIterator
     * @param Xml\DeviceIterator   $deviceIterator
     * @param Xml\DeviceIterator[] $patchDeviceIterators Array of objects for patch files
     *
     * @throws Exception
     */
    private function buildRepository(
        Xml\VersionIterator $wurflInfoIterator,
        Xml\DeviceIterator $deviceIterator,
        array $patchDeviceIterators = array()
    ) {
        $this->persistWurflInfo($wurflInfoIterator);

        $patchingDevices = $this->toListOfPatchingDevices($patchDeviceIterators);

        try {
            $this->process($deviceIterator, $patchingDevices);
        } catch (Exception $exception) {
            $this->clean();

            throw new Exception('Problem Building WURFL Repository: ' . $exception->getMessage(), $exception->getCode(
                ), $exception);
        }

        $this->setRepositoryBuilt();
    }

    /**
     * Returns an array of Xml\DeviceIterator for the given $wurflPatches and $capabilitiesToUse
     *
     * @param array $wurflPatches     Array of (string)filenames
     * @param array $capabilityFilter Array of (string) WURFL capabilities
     *
     * @return \Wurfl\Xml\DeviceIterator[]
     */
    private function toPatchIterators(array $wurflPatches = array(), array $capabilityFilter = array())
    {
        $patchIterators = array();

        if (is_array($wurflPatches)) {
            foreach ($wurflPatches as $wurflPatch) {
                $patchIterators[] = new Xml\DeviceIterator($wurflPatch, $capabilityFilter);
            }
        }

        return $patchIterators;
    }

    /**
     * Returns an array of \Wurfl\Xml\DeviceIterator for the given $wurflPatches and $capabilityFilter
     *
     * @param array $wurflPatches     Array of (string)filenames
     * @param array $capabilityFilter Array of (string) WURFL capabilities
     *
     * @return \Wurfl\Xml\DeviceIterator[]
     */

    /**
     * @return bool true if device repository is already built (WURFL is loaded in persistence proivder)
     */
    private function isRepositoryBuilt()
    {
        return $this->persistenceProvider->isWurflLoaded();
    }

    /**
     * Marks the WURFL as loaded in the persistence provider
     *
     * @see WURFL_Storage_Base::setWurflLoaded()
     */
    private function setRepositoryBuilt()
    {
        $this->persistenceProvider->setWurflLoaded();
    }

    /**
     * @return array Array of (string)User Agent Handler prefixes
     * @see WURFL_Handlers_Handler::getPrefix()
     */
    private function deviceClassificationNames()
    {
        $deviceClusterNames = array();

        foreach ($this->userAgentHandlerChain->getHandlers() as $userAgentHandler) {
            /** @var $userAgentHandler \Wurfl\Handlers\AbstractHandler */
            $deviceClusterNames[] = $userAgentHandler->getPrefix();
        }

        return $deviceClusterNames;
    }

    /**
     * Clears the devices from the persistence provider
     *
     * @see WURFL_Storage_Base::clear()
     */
    private function clean()
    {
        $this->persistenceProvider->clear();
    }

    /**
     * Save Loaded WURFL info in the persistence provider
     *
     * @param Xml\VersionIterator $wurflInfoIterator
     */
    private function persistWurflInfo(Xml\VersionIterator $wurflInfoIterator)
    {
        foreach ($wurflInfoIterator as $info) {
            $this->persistenceProvider->save(Xml\Info::PERSISTENCE_KEY, $info);

            return;
        }
    }

    /**
     * Process device iterator
     *
     * @param Xml\DeviceIterator $deviceIterator
     * @param array              $patchingDevices
     */
    private function process(Xml\DeviceIterator $deviceIterator, array $patchingDevices = array())
    {
        $usedPatches = array();

        foreach ($deviceIterator as $device) {
            /* @var $device Xml\ModelDevice */
            $toPatch = isset($patchingDevices[$device->id]);

            if ($toPatch) {
                $device                   = $this->patchDevice($device, $patchingDevices [$device->id]);
                $usedPatches[$device->id] = $device->id;
            }

            $this->classifyAndPersistDevice($device);
        }

        $this->classifyAndPersistNewDevices(array_diff_key($patchingDevices, $usedPatches));
        $this->persistClassifiedDevicesUserAgentMap();
    }

    /**
     * Save all $newDevices in the persistence provider
     *
     * @param array $newDevices Array of WURFL_Device objects
     */
    private function classifyAndPersistNewDevices(array $newDevices)
    {
        foreach ($newDevices as $newDevice) {
            $this->classifyAndPersistDevice($newDevice);
        }
    }

    /**
     * Save given $device in the persistence provider.  This is called when loading the WURFL XML
     * data, directly after reading the complete device node.
     *
     * @param Xml\ModelDevice $device
     *
     * @see \Wurfl\UserAgentHandlerChain::filter(), WURFL_Storage_Base::save()
     */
    private function classifyAndPersistDevice(Xml\ModelDevice $device)
    {
        $this->userAgentHandlerChain->filter($device->userAgent, $device->id);

        $this->persistenceProvider->save($device->id, $device);
    }

    /**
     * Save the User Agent Map in the UserAgentHandlerChain
     *
     * @see WURFL_UserAgentHandlerChain::persistData()
     */
    private function persistClassifiedDevicesUserAgentMap()
    {
        $this->userAgentHandlerChain->persistData();
    }

    /**
     * @param Xml\ModelDevice $device
     * @param Xml\ModelDevice $patchingDevice
     *
     * @return Xml\ModelDevice
     */
    private function patchDevice(Xml\ModelDevice $device, Xml\ModelDevice $patchingDevice)
    {
        return $this->devicePatcher->patch($device, $patchingDevice);
    }

    /**
     * @param array $patchingDeviceIterators Array of Wurfl\Xml\DeviceIterators
     *
     * @return array Merged array of current patch devices
     */
    private function toListOfPatchingDevices($patchingDeviceIterators)
    {
        if (!is_array($patchingDeviceIterators)) {
            return array();
        }

        $currentPatchingDevices = array();

        foreach ($patchingDeviceIterators as $deviceIterator) {
            $newPatchingDevices = $this->toArray($deviceIterator);
            $this->patchDevices($currentPatchingDevices, $newPatchingDevices);
        }

        return $currentPatchingDevices;
    }

    /**
     * Adds the given $newPatchingDevices to the $currentPatchingDevices array
     *
     * @param array $currentPatchingDevices REFERENCE to array of current devices to be patches
     * @param array $newPatchingDevices     Array of new devices to be patched in
     */
    private function patchDevices(array &$currentPatchingDevices, array $newPatchingDevices)
    {
        foreach ($newPatchingDevices as $deviceId => $newPatchingDevice) {
            if (isset($currentPatchingDevices[$deviceId])) {
                $currentPatchingDevices[$deviceId] = $this->patchDevice(
                    $currentPatchingDevices[$deviceId],
                    $newPatchingDevice
                );
            } else {
                $currentPatchingDevices[$deviceId] = $newPatchingDevice;
            }
        }
    }

    /**
     * Returns an array of devices in the form 'WURFL_Device::id => WURFL_Device'
     *
     * @param Xml\DeviceIterator $deviceIterator
     *
     * @return array
     */
    private function toArray(Xml\DeviceIterator $deviceIterator)
    {
        $patchingDevices = array();

        foreach ($deviceIterator as $device) {
            /* @var $device Xml\ModelDevice */
            $patchingDevices[$device->id] = $device;
        }

        return $patchingDevices;
    }
}
