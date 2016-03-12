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

namespace Wurfl\Device;

use Psr\Log\LoggerInterface;
use Wurfl\CustomDevice;
use Wurfl\Device\Xml\DeviceIterator;
use Wurfl\Device\Xml\DevicePatcher;
use Wurfl\Device\Xml\Info;
use Wurfl\Device\Xml\VersionIterator;
use Wurfl\Exception\ConsistencyException;
use Wurfl\FileUtils;
use Wurfl\Handlers\Chain\UserAgentHandlerChain;
use Wurfl\Logger\LogLevel;
use Wurfl\Storage\Storage;
use Wurfl\VirtualCapability\VirtualCapabilityProvider;

/**
 * Builds a \Wurfl\DeviceRepositoryInterface
 */
class DeviceRepositoryBuilder
{
    /**
     * @var \Wurfl\Storage\Storage
     */
    private $persistenceProvider;

    /**
     * @var \Wurfl\Handlers\Chain\UserAgentHandlerChain
     */
    private $userAgentHandlerChain;

    /**
     * @var \Wurfl\Device\Xml\DevicePatcher
     */
    private $devicePatcher;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger = null;

    /**
     * Filename of lockfile to prevent concurrent DeviceRepository builds
     *
     * @var string
     */
    private $lockFile;

    private $devices   = array();
    private $fallbacks = array();

    /**
     * @param \Wurfl\Storage\Storage                      $persistenceProvider
     * @param \Wurfl\Handlers\Chain\UserAgentHandlerChain $chain
     * @param \Wurfl\Device\Xml\DevicePatcher             $devicePatcher
     * @param \Psr\Log\LoggerInterface                    $logger
     */
    public function __construct(
        Storage $persistenceProvider,
        UserAgentHandlerChain $chain,
        DevicePatcher $devicePatcher,
        LoggerInterface $logger
    ) {
        $this->persistenceProvider   = $persistenceProvider;
        $this->userAgentHandlerChain = $chain;
        $this->devicePatcher         = $devicePatcher;
        $this->logger                = $logger;

        $this->lockFile = FileUtils::getTempDir() . '/wurfl_builder.lock';
    }

    /**
     * release the lock if this class is destroyed
     */
    public function __destruct()
    {
        $this->releaseLock();
    }

    /**
     * Builds DeviceRepositoryInterface in PersistenceProvider from $wurflFile and $wurflPatches using $capabilityFilter
     *
     * @param string $wurflFile        Filename of wurfl.xml or other complete WURFL file
     * @param array  $wurflPatches     Array of WURFL patch files
     * @param array  $capabilityFilter Array of capabilities to be included in the DeviceRepositoryInterface
     *
     * @throws \Exception
     * @throws \Wurfl\Device\Exception
     * @throws \Wurfl\Exception\ConsistencyException
     * @return CustomDeviceRepository
     */
    public function build($wurflFile, array $wurflPatches = array(), array $capabilityFilter = array())
    {
        if (!$this->isRepositoryBuilt()) {
            // If acquireLock() is false, the WURFL is being reloaded in another thread
            if ($this->acquireLock()) {
                try {
                    $infoIterator = new VersionIterator($wurflFile);
                } catch (\InvalidArgumentException $e) {
                    $this->releaseLock();

                    $this->logger->log(LogLevel::ERROR, $e);

                    throw $e;
                }

                try {
                    $deviceIterator = new DeviceIterator($wurflFile, $capabilityFilter);
                } catch (\InvalidArgumentException $e) {
                    $this->releaseLock();

                    $this->logger->log(LogLevel::ERROR, $e);

                    throw $e;
                }

                $patchIterators = $this->toPatchIterators($wurflPatches, $capabilityFilter);

                try {
                    $this->buildRepository($infoIterator, $deviceIterator, $patchIterators);
                } catch (Exception $e) {
                    $this->releaseLock();

                    $this->logger->log(LogLevel::ERROR, $e);

                    throw $e;
                }

                try {
                    $this->verifyRepository();
                } catch (ConsistencyException $e) {
                    $this->releaseLock();

                    $this->logger->log(LogLevel::ERROR, $e);

                    throw $e;
                }
                $this->setRepositoryBuilt();

                $this->releaseLock();
            }
        }

        return new CustomDeviceRepository($this->persistenceProvider, $this->deviceClassificationNames());
    }

    /**
     * Iterates over XML files and pulls relevent data
     *
     * @param \Wurfl\Device\Xml\VersionIterator  $wurflInfoIterator
     * @param \Wurfl\Device\Xml\DeviceIterator   $deviceIterator
     * @param \Wurfl\Device\Xml\DeviceIterator[] $patchDeviceIterators Array of objects for patch files
     *
     * @throws \Wurfl\Device\Exception
     */
    private function buildRepository(
        VersionIterator $wurflInfoIterator,
        DeviceIterator $deviceIterator,
        array $patchDeviceIterators = array()
    ) {
        $this->persistWurflInfo($wurflInfoIterator);

        $patchingDevices = $this->toListOfPatchingDevices($patchDeviceIterators);

        try {
            $this->process($deviceIterator, $patchingDevices);
        } catch (Exception $exception) {
            $this->clean();

            throw new Exception(
                'Problem Building WURFL Repository: ' . $exception->getMessage(),
                null,
                $exception
            );
        }
    }

    /**
     * Returns an array of Xml\DeviceIterator for the given $wurflPatches and $capabilitiesToUse
     *
     * @param string[] $wurflPatches     Array of (string)filenames
     * @param string[] $capabilityFilter Array of (string) WURFL capabilities
     *
     * @return \Wurfl\Device\Xml\DeviceIterator[]
     */
    private function toPatchIterators(array $wurflPatches = array(), array $capabilityFilter = array())
    {
        $patchIterators = array();

        if (is_array($wurflPatches)) {
            foreach ($wurflPatches as $wurflPatch) {
                try {
                    $patchIterators[] = new DeviceIterator($wurflPatch, $capabilityFilter);
                } catch (\InvalidArgumentException $e) {
                    $this->logger->log(LogLevel::ERROR, $e);
                }
            }
        }

        return $patchIterators;
    }

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
     *
     * @see WURFL_Handlers_Handler::getPrefix()
     */
    private function deviceClassificationNames()
    {
        $deviceClusterNames = array();

        foreach ($this->userAgentHandlerChain->getHandlers() as $userAgentHandler) {
            /* @var $userAgentHandler \Wurfl\Handlers\AbstractHandler */
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
     * @param \Wurfl\Device\Xml\VersionIterator $wurflInfoIterator
     */
    private function persistWurflInfo(VersionIterator $wurflInfoIterator)
    {
        foreach ($wurflInfoIterator as $info) {
            $this->persistenceProvider->save(Info::PERSISTENCE_KEY, $info);

            return;
        }
    }

    /**
     * Process device iterator
     *
     * @param \Wurfl\Device\Xml\DeviceIterator $deviceIterator
     * @param array                            $patchingDevices
     */
    private function process(DeviceIterator $deviceIterator, array $patchingDevices = array())
    {
        $usedPatches = array();

        foreach ($deviceIterator as $device) {
            /* @var $device \Wurfl\Device\ModelDeviceInterface */
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
     * @param \Wurfl\Device\ModelDeviceInterface[] $newDevices Array of WURFL_Device objects
     */
    private function classifyAndPersistNewDevices(array $newDevices)
    {
        foreach ($newDevices as $newDevice) {
            $this->classifyAndPersistDevice($newDevice);
        }
    }

    /**
     * @param \Wurfl\Device\ModelDeviceInterface $device
     *
     * @return bool
     */
    private function validateDevice(ModelDeviceInterface $device)
    {
        // Must have a valid wurfl ID
        if (strlen($device->id) === 0) {
            return false;
        }

        // Must have a valid User Agent unless it's "generic"
        if (strlen($device->userAgent) === 0 && $device->id !== 'generic') {
            return false;
        }

        return true;
    }

    /**
     * Save given $device in the persistence provider.  This is called when loading the WURFL XML
     * data, directly after reading the complete device node.
     *
     * @param \Wurfl\Device\ModelDeviceInterface $device
     *
     * @see \Wurfl\Handlers\Chain\UserAgentHandlerChain::filter(), WURFL_Storage_Base::save()
     */
    private function classifyAndPersistDevice(ModelDeviceInterface $device)
    {
        if ($this->validateDevice($device) === false) {
            return;
        }

        array_push($this->devices, $device->id);

        if ($device->fallBack !== 'root') {
            $this->fallbacks[$device->fallBack] = $device->id;
        }

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
     * @param \Wurfl\Device\ModelDeviceInterface $device
     * @param \Wurfl\Device\ModelDeviceInterface $patchingDevice
     *
     * @return \Wurfl\Device\ModelDeviceInterface
     */
    private function patchDevice(ModelDeviceInterface $device, ModelDeviceInterface $patchingDevice)
    {
        return $this->devicePatcher->patch($device, $patchingDevice);
    }

    /**
     * @param \Wurfl\Device\Xml\DeviceIterator[] $patchingDeviceIterators Array of Wurfl\Xml\DeviceIterators
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
     * @param \Wurfl\Device\ModelDeviceInterface[] $currentPatchingDevices REFERENCE to array of current devices to be patches
     * @param \Wurfl\Device\ModelDeviceInterface[] $newPatchingDevices     Array of new devices to be patched in
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
     * @param \Wurfl\Device\Xml\DeviceIterator $deviceIterator
     *
     * @return ModelDeviceInterface[]
     */
    private function toArray(DeviceIterator $deviceIterator)
    {
        $patchingDevices = array();

        foreach ($deviceIterator as $device) {
            /* @var $device \Wurfl\Device\ModelDeviceInterface */
            $patchingDevices[$device->id] = $device;
        }

        return $patchingDevices;
    }

    private function verifyRepository()
    {
        /**
         * verify required device ids
         */
        foreach ($this->userAgentHandlerChain->getHandlers() as $handler) {
            /**
             * @var \Wurfl\Handlers\AbstractHandler
             */
            foreach ($handler::$constantIDs as $requireDeviceId) {
                $device = $this->persistenceProvider->load($requireDeviceId);

                if ($device === null) {
                    throw new ConsistencyException(
                        'wurfl.xml load error - device id [' . $requireDeviceId . '] is missing - '
                        . 'you may need to update the wurfl.xml file to a more recent version'
                    );
                }
            }
        }

        /**
         * verify required capabilities
         */
        $requiredCaps  = VirtualCapabilityProvider::getRequiredCapabilities();
        $genericDevice = new CustomDevice(array($this->persistenceProvider->load('generic')));
        $loadedCaps    = array_keys($genericDevice->getAllCapabilities());
        $missingCaps   = array_diff($requiredCaps, $loadedCaps);

        if (count($missingCaps) > 0) {
            throw new ConsistencyException(
                'Missing required WURFL Capabilities: ' . implode(', ', $missingCaps)
            );
        }

        $invalidFallbacks = array_diff(array_keys($this->fallbacks), $this->devices);

        if (count($invalidFallbacks) > 0) {
            foreach ($invalidFallbacks as $invalidFallback) {
                $device = new CustomDevice(
                    array($this->persistenceProvider->load($this->fallbacks[$invalidFallback]))
                );
                throw new ConsistencyException(
                    sprintf('Invalid Fallback %s for the device %s', $invalidFallback, $device->id)
                );
            }
        }

        $this->fallbacks = array();
        $this->devices   = array();
    }

    /**
     * Acquires a lock so only this thread reloads the WURFL data, returns false if it cannot be acquired
     *
     * @return bool
     */
    private function acquireLock()
    {
        return $this->persistenceProvider->acquireLock();
    }

    /**
     * Releases the lock if one was acquired
     */
    private function releaseLock()
    {
        return $this->persistenceProvider->releaseLock();
    }
}
