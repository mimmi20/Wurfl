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

namespace Wurfl\Handlers\Chain;

use Psr\Log\LoggerInterface;
use Wurfl\Handlers\AbstractHandler;
use Wurfl\Handlers\Utils;
use Wurfl\Request\GenericRequest;
use Wurfl\WurflConstants;

/**
 * Handles the chain of \Wurfl\Handlers\AbstractHandler objects
 *
 * @see        \Wurfl\Handlers\AbstractHandler
 */
class UserAgentHandlerChain
{
    /**
     * @var \Wurfl\Handlers\AbstractHandler[]
     */
    private $userAgentHandlers = array();

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger = null;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Adds a \Wurfl\Handlers\AbstractHandler to the chain
     *
     * @param \Wurfl\Handlers\AbstractHandler $handler
     *
     * @return \Wurfl\Handlers\Chain\UserAgentHandlerChain $this
     */
    public function addUserAgentHandler(AbstractHandler $handler)
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
     * @param string $userAgent
     * @param string $deviceID
     *
     * @see \Wurfl\Handlers\AbstractHandler::filter()
     */
    public function filter($userAgent, $deviceID)
    {
        Utils::reset();

        $genericNormalizer = UserAgentHandlerChainFactory::createGenericNormalizers();
        $userAgent         = $genericNormalizer->normalize($userAgent);

        $handlers = $this->getHandlers();

        foreach ($handlers as $handler) {
            /** @var $handler \Wurfl\Handlers\AbstractHandler */
            $handler->setLogger($this->logger);

            if ($handler->filter($userAgent, $deviceID)) {
                break;
            }
        }
    }

    /**
     * Return the the device id for the request
     *
     * @param \Wurfl\Request\GenericRequest $request
     *
     * @return string deviceID
     */
    public function match(GenericRequest $request)
    {
        Utils::reset();

        $handlers    = $this->getHandlers();
        $matchResult = WurflConstants::NO_MATCH;

        foreach ($handlers as $handler) {
            /** @var $handler \Wurfl\Handlers\AbstractHandler */
            $handler->setLogger($this->logger);

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
            /** @var $handler \Wurfl\Handlers\AbstractHandler */
            $handler->setLogger($this->logger);

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
            /* @var $handler \Wurfl\Handlers\AbstractHandler */
            $handler->setLogger($this->logger);

            $current = $handler->getUserAgentsWithDeviceId();

            if (!empty($current)) {
                $userAgents = array_merge($userAgents, $current);
            }
        }

        return $userAgents;
    }
}
