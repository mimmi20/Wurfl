<?php
namespace Wurfl;

/**
 * Copyright (c) 2012 ScientiaMobile, Inc.
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 * Refer to the COPYING.txt file distributed with this package.
 *
 * @category   WURFL
 * @package    WURFL
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */

use Psr\Log\LoggerInterface;

/**
 * WURFL Context stores the persistence provider, cache provider and logger objects
 *
 * @package    WURFL
 * @property-read \Wurfl\Storage\Base      $persistenceProvider
 * @property-read \Wurfl\Storage\Base      $cacheProvider
 * @property-read \Psr\Log\LoggerInterface $logger
 */
class Context
{
    /**
     * @var \Wurfl\Storage\Base
     */
    private $persistenceProvider;

    /**
     * @var \Wurfl\Storage\Base
     */
    private $cacheProvider;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        Storage\Base $persistenceProvider = null, Storage\Base $cacheProvider = null, LoggerInterface $logger = null)
    {
        $this->persistenceProvider = is_null($persistenceProvider) ? new Storage\NullStorage() : $persistenceProvider;
        $this->cacheProvider       = is_null($cacheProvider) ? new Storage\NullStorage() : $cacheProvider;
        $this->logger              = is_null($logger) ? new Logger\NullLogger() : $logger;
    }

    public function cacheProvider(Storage\Base $cacheProvider)
    {
        $this->cacheProvider = is_null($cacheProvider) ? new Storage\NullStorage() : $cacheProvider;

        return $this;
    }

    public function logger(LoggerInterface $logger)
    {
        $this->logger = is_null($logger) ? new Logger\NullLogger() : $logger;

        return $this;
    }

    public function __get($name)
    {
        return $this->$name;
    }
}