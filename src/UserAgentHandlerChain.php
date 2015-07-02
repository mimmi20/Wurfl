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
     * @param \Wurfl\Handlers\AbstractHandler $handler
     *
     * @return \Wurfl\UserAgentHandlerChain $this
     */
    public function addUserAgentHandler(Handlers\AbstractHandler $handler)
    {
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

        $genericNormalizer = UserAgentHandlerChainFactory::createGenericNormalizers();
        $userAgent         = $genericNormalizer->normalize($userAgent);

        $handlers = $this->getHandlers();

        foreach ($handlers as $handler) {
            /** @var $handler Handlers\AbstractHandler */
            if ($handler->filter($userAgent, $deviceID)) {
                break;
            }
        }
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

        $handlers    = $this->getHandlers();
        $matchResult = Constants::NO_MATCH;

        foreach ($handlers as $handler) {
            /** @var $handler Handlers\AbstractHandler */
            if ($handler->canHandle($request->getUserAgentNormalized())) {
                $matchResult = $handler->applyMatch($request);
                break;
            }
        }

        return $matchResult;
    }

    /**
     * Save the data from each \Wurfl\Handlers\AbstractHandler
     *
     * @see \Wurfl\Handlers\AbstractHandler::persistData()
     */
    public function persistData()
    {
        $handlers = $this->getHandlers();

        foreach ($handlers as $handler) {
            /** @var $handler Handlers\AbstractHandler */
            $handler->persistData();
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
        $handlers   = $this->getHandlers();

        foreach ($handlers as $handler) {
            /** @var $handler Handlers\AbstractHandler */
            $current = $handler->getUserAgentsWithDeviceId();

            if (!empty($current)) {
                $userAgents = array_merge($userAgents, $current);
            }
        }

        return $userAgents;
    }
}
