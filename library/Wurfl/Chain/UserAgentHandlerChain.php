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

/**
 * Handles the chain of \Wurfl\Handlers\Handler objects
 * @package    WURFL
 * @see \Wurfl\Handlers\Handler
 */
class UserAgentHandlerChain extends SplDoublyLinkedList
{
    /**
     * Adds a \Wurfl\Handlers\Handler to the chain
     *
     * @param \Wurfl\Handlers\Handler $handler
     * @return UserAgentHandlerChain $this
     */
    public function addUserAgentHandler(\Wurfl\Handlers\Handler $handler)
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
     * @param String $userAgent
     * @param String $deviceID
     * @see \Wurfl\Handlers\Handler::filter()
     */
    public function filter($userAgent, $deviceID)
    {
        Handlers\Utils::reset();
        
        $this->rewind();

        while ($this->valid()) {
            /** @var $userAgentHandler Handlers\Handler */
            $userAgentHandler = $this->current();
            
            if ($userAgentHandler->filter($userAgent, $deviceID)) {
                break;
            }

            $this->next();
        }
    }
    
    /**
     * Return the the device id for the request 
     *
     * @param \Wurfl\Request\GenericRequest $request
     *
     * @return String deviceID
     */
    public function match(\Wurfl\Request\GenericRequest $request)
    {
        \Wurfl\Handlers\Utils::reset();
        
        $this->rewind();
        
        $found = false;

        while ($this->valid()) {
            /** @var $userAgentHandler Handlers\Handler */
            $userAgentHandler = $this->current();
            
            if ($userAgentHandler->canHandle($request->userAgent)) {
                $deviceId = $userAgentHandler->match($request);
                $found    = true;
                break;
            }

            $this->next();
        }
        
        if (!$found) {
            $deviceId = Constants::GENERIC;
        }
        
        return $deviceId;
    }
    
    /**
     * Save the data from each \Wurfl\Handlers\Handler
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
    }
    
    /**
     * Collect data
     * @return array data
     */
    public function collectData()
    {
        $userAgentsWithDeviceId = array();        
        
        foreach ($this->_userAgentHandlers as $userAgentHandler) {
            /**
             * @see \Wurfl\Handlers\Handler::getUserAgentsWithDeviceId()
             */
            $current = $userAgentHandler->getUserAgentsWithDeviceId();
            
            if(!empty($current)) {
                $userAgentsWithDeviceId = array_merge($userAgentsWithDeviceId, $current);
            } 
        }
        
        return $userAgentsWithDeviceId;
    }    
    
    public function getPrefixes()
    {
        $deviceClusterNames = array();
        
        $this->rewind();

        while ($this->valid()) {
            /** @var $userAgentHandler Handlers\Handler */
            $userAgentHandler = $this->current();
            $deviceClusterNames[] = $userAgentHandler->getPrefix();

            $this->next();
        }
        
        return $deviceClusterNames;
    }
}