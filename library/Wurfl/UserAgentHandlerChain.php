<?php
namespace Wurfl;

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
/**
 * Handles the chain of \Wurfl\Handlers\AbstractHandler objects
 * @package    WURFL
 * @see \Wurfl\Handlers\AbstractHandler
 */
class UserAgentHandlerChain {
     
    /**
     * @var array of \Wurfl\Handlers\AbstractHandler objects
     */
    private $_userAgentHandlers = array();
    
    /**
     * Adds a \Wurfl\Handlers\AbstractHandler to the chain
     *
     * @param \Wurfl\Handlers\AbstractHandler $handler
     * @return \Wurfl\UserAgentHandlerChain $this
     */
    public function addUserAgentHandler(\Wurfl\Handlers\AbstractHandler $handler) {
        $size = count($this->_userAgentHandlers); 
        if ($size > 0) {
            $this->_userAgentHandlers[$size-1]->setNextHandler($handler);
        }
        $this->_userAgentHandlers[] = $handler;
        return $this;
    }
    
    /**
     * @return array An array of all the \Wurfl\Handlers\AbstractHandler objects
     */
    public function getHandlers() {
        return $this->_userAgentHandlers;
    }
    
    /**
     * Adds the pair $userAgent, $deviceID to the clusters they belong to.
     *
     * @param String $userAgent
     * @param String $deviceID
     * @see \Wurfl\Handlers\AbstractHandler::filter()
     */
    public function filter($userAgent, $deviceID) {
        \Wurfl\Handlers\Utils::reset();
        $this->_userAgentHandlers[0]->filter($userAgent, $deviceID);
    }
    
    
    
    /**
     * Return the the device id for the request 
     *
     * @param \Wurfl\Request\GenericRequest $request
     * @return String deviceID
     */
    public function match(\Wurfl\Request\GenericRequest $request) {
        \Wurfl\Handlers\Utils::reset();
        return $this->_userAgentHandlers[0]->match($request);
    }
    
    /**
     * Save the data from each \Wurfl\Handlers\AbstractHandler
     * @see \Wurfl\Handlers\AbstractHandler::persistData()
     */
    public function persistData() {
        foreach ($this->_userAgentHandlers as $userAgentHandler) {
            $userAgentHandler->persistData();
        }
        
    }
    
    /**
     * Collect data
     * @return array data
     */
    public function collectData() {
        $userAgentsWithDeviceId = array();        
        foreach ($this->_userAgentHandlers as $userAgentHandler) {
            /**
             * @see \Wurfl\Handlers\AbstractHandler::getUserAgentsWithDeviceId()
             */
            $current = $userAgentHandler->getUserAgentsWithDeviceId();
            if(!empty($current)) {
                $userAgentsWithDeviceId = array_merge($userAgentsWithDeviceId, $current);
            } 
        }
        return $userAgentsWithDeviceId;
    }    
}