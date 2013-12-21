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
 *
 * @package    WURFL
 * @see        \Wurfl\Handlers\AbstractHandler
 */
class UserAgentHandlerChain
{

    /**
     * @var array of \Wurfl\Handlers\AbstractHandler objects
     */
    private $userAgentHandlers = array();

    /**
     * Adds a \Wurfl\Handlers\AbstractHandler to the chain
     *
     * @param Handlers\AbstractHandler $handler
     *
     * @return \Wurfl\UserAgentHandlerChain $this
     */
    public function addUserAgentHandler(Handlers\AbstractHandler $handler)
    {
        $size = count($this->userAgentHandlers);

        if ($size > 0) {
            $this->userAgentHandlers[$size - 1]->setNextHandler($handler);
        }

        $this->userAgentHandlers[] = $handler;
        return $this;
    }

    /**
     * @return array An array of all the \Wurfl\Handlers\AbstractHandler objects
     */
    public function getHandlers()
    {
        return $this->userAgentHandlers;
    }

    /**
     * Adds the pair $userAgent, $deviceID to the clusters they belong to.
     *
     * @param String $userAgent
     * @param String $deviceID
     *
     * @see \Wurfl\Handlers\AbstractHandler::filter()
     */
    public function filter($userAgent, $deviceID)
    {
        Handlers\Utils::reset();
        $this->userAgentHandlers[0]->filter($userAgent, $deviceID);
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
        Handlers\Utils::reset();
        return $this->userAgentHandlers[0]->match($request);
    }

    /**
     * Save the data from each \Wurfl\Handlers\AbstractHandler
     *
     * @see \Wurfl\Handlers\AbstractHandler::persistData()
     */
    public function persistData()
    {
        foreach ($this->userAgentHandlers as $userAgentHandler) {
            $userAgentHandler->persistData();
        }
    }

    /**
     * Collect data
     *
     * @return array data
     */
    public function collectData()
    {
        $userAgents = array();

        foreach ($this->userAgentHandlers as $userAgentHandler) {
            /**
             * @see \Wurfl\Handlers\AbstractHandler::getUserAgentsWithDeviceId()
             */
            $current = $userAgentHandler->getUserAgentsWithDeviceId();

            if (!empty($current)) {
                $userAgents = array_merge($userAgents, $current);
            }
        }

        return $userAgents;
    }
}
