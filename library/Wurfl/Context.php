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
 * @property-read LoggerInterface          $logger
 */
class Context
{
    /**
     * @var Storage\StorageInterface
     */
    private $persistenceProvider;

    /**
     * @var Storage\StorageInterface
     */
    private $cacheProvider;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Storage\StorageInterface $persistenceProvider
     * @param Storage\StorageInterface $cacheProvider
     * @param LoggerInterface          $logger
     */
    public function __construct(
        Storage\StorageInterface $persistenceProvider = null,
        Storage\StorageInterface $cacheProvider = null,
        LoggerInterface $logger = null
    ) {
        $this->persistenceProvider = is_null($persistenceProvider) ? new Storage\NullStorage() : $persistenceProvider;
        $this->cacheProvider       = is_null($cacheProvider) ? new Storage\NullStorage() : $cacheProvider;
        $this->logger              = is_null($logger) ? new Logger\NullLogger() : $logger;
    }

    /**
     * @param Storage\StorageInterface $cacheProvider
     *
     * @return $this
     */
    public function cacheProvider(Storage\StorageInterface $cacheProvider)
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