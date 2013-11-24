<?php
namespace Wurfl\Chain;

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
 * @package    WURFL
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */

use SplDoublyLinkedList;
use Wurfl\Constants;
use Wurfl\Context;
use Wurfl\Handlers;
use Wurfl\Handlers\Utils;
use Wurfl\Request;
use Wurfl\Xml\ModelDevice;

/**
 * Handles the chain of \Wurfl\Handlers\Handler objects
 *
 * @package    WURFL
 * @see        \Wurfl\Handlers\Handler
 */
class UserAgentHandlerChain extends SplDoublyLinkedList
{
    /**
     * Adds the pair $userAgent, $deviceID to the clusters they belong to.
     *
     * @param \Wurfl\Context $wurflContext
     *
     * @return UserAgentHandlerChain
     *
     * @see      \Wurfl\Handlers\Handler::setupContext()
     */
    public function setupContext(Context $wurflContext)
    {
        Utils::reset();

        $this->rewind();

        while ($this->valid()) {
            /** @var $userAgentHandler Handlers\Handler */
            $userAgentHandler = $this->current();
            $userAgentHandler->setupContext($wurflContext);

            $this->next();
        }

        return $this;
    }

    /**
     * Adds a \Wurfl\Handlers\Handler to the chain
     *
     * @param Handlers\Handler $handler
     *
     * @return UserAgentHandlerChain
     */
    public function addUserAgentHandler(Handlers\Handler $handler)
    {
        $this->push($handler);

        return $this;
    }

    /**
     * @return array An array of all the \Wurfl\Handlers\Handler objects
     */
    public function getHandlers()
    {
        return $this->_userAgentHandlers;
    }

    /**
     * Adds the pair $userAgent, $deviceID to the clusters they belong to.
     *
     * @param ModelDevice $device
     *
     * @internal param String $userAgent
     * @internal param String $deviceID
     *
     * @return UserAgentHandlerChain
     * @see      \Wurfl\Handlers\Handler::filter()
     */
    public function filter(ModelDevice $device)
    {
        Utils::reset();

        $this->rewind();

        while ($this->valid()) {
            /** @var $userAgentHandler Handlers\Handler */
            $userAgentHandler = $this->current();

            if ($userAgentHandler->filter($device)) {
                break;
            }

            $this->next();
        }

        return $this;
    }

    /**
     * Return the the device id for the request
     *
     * @param Request\GenericRequest $request
     *
     * @return String deviceID
     */
    public function match(Request\GenericRequest $request)
    {
        Utils::reset();

        $this->rewind();

        $deviceId = Constants::GENERIC;

        while ($this->valid()) {
            /** @var $userAgentHandler Handlers\Handler */
            $userAgentHandler = $this->current();

            if ($userAgentHandler->canHandle($request->userAgent)) {
                $deviceId = $userAgentHandler->match($request);
                break;
            }

            $this->next();
        }

        return $deviceId;
    }

    /**
     * Save the data from each \Wurfl\Handlers\Handler
     *
     * @return UserAgentHandlerChain
     *
     * @see \Wurfl\Handlers\Handler::persistData()
     */
    public function persistData()
    {
        $this->rewind();

        while ($this->valid()) {
            /** @var $userAgentHandler Handlers\Handler */
            $userAgentHandler = $this->current();
            $userAgentHandler->persistData();

            $this->next();
        }

        return $this;
    }

    /**
     * Collect data
     *
     * @return array data
     */
    public function collectData()
    {
        $userAgentsWithDeviceId = array();

        $this->rewind();

        while ($this->valid()) {
            /** @var $userAgentHandler Handlers\Handler */
            $userAgentHandler = $this->current();
            $current          = $userAgentHandler->getUserAgentsWithDeviceId();

            if (is_array($current)) {
                $userAgentsWithDeviceId = array_merge($userAgentsWithDeviceId, $current);
            }

            $this->next();
        }

        return $userAgentsWithDeviceId;
    }

    public function getPrefixes()
    {
        $deviceClusterNames = array();

        $this->rewind();

        while ($this->valid()) {
            /** @var $userAgentHandler Handlers\Handler */
            $userAgentHandler     = $this->current();
            $deviceClusterNames[] = $userAgentHandler->getPrefix();

            $this->next();
        }

        return $deviceClusterNames;
    }
}