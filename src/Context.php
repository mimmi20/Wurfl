<?php
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
 */

namespace Wurfl;

use Psr\Log\LoggerInterface;

/**
 * WURFL Context stores the persistence provider, cache provider and logger objects
 *
 * @package    WURFL
 *
 * @property-read Storage\Storage $persistenceProvider
 * @property-read Storage\Storage $cacheProvider
 * @property-read LoggerInterface $logger
 */
class Context
{

    /**
     * @var Storage\Storage
     */
    private $persistenceProvider;
    /**
     * @var Storage\Storage
     */
    private $cacheProvider;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Storage\Storage $persistenceProvider
     * @param Storage\Storage $cacheProvider
     * @param LoggerInterface $logger
     */
    public function __construct(
        Storage\Storage $persistenceProvider,
        Storage\Storage $cacheProvider = null,
        LoggerInterface $logger = null
    ) {
        $this->persistenceProvider = $persistenceProvider;
        $this->setCacheProvider($cacheProvider);
        $this->logger = is_null($logger) ? new Logger\NullLogger() : $logger;
    }

    /**
     * @param $cacheProvider
     *
     * @return $this
     */
    public function setCacheProvider($cacheProvider)
    {
        $this->cacheProvider = is_null($cacheProvider) ? new Storage\Storage(Storage\Factory::create(
            array('provider' => 'null')
        )) : $cacheProvider;

        return $this;
    }

    /**
     * @param $logger
     *
     * @return $this
     */
    public function setLogger($logger)
    {
        $this->logger = is_null($logger) ? new Logger\NullLogger() : $logger;

        return $this;
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->$name;
    }
}
