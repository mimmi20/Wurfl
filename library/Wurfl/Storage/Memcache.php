<?php
declare(ENCODING = 'utf-8');
namespace Wurfl\Storage;

/**
 * Copyright(c) 2011 ScientiaMobile, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or(at your option) any later version.
 *
 * Refer to the COPYING file distributed with this package.
 *
 * @category   WURFL
 * @package    WURFL_Storage
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @author     Fantayeneh Asres Gizaw
 * @version    $id$
 */
/**
 * WURFL Storage
 * @package    WURFL_Storage
 */
class Memcache extends Base
{
    const EXTENSION_MODULE_NAME = '_memcache';

    private $_memcache;
    private $_host;
    private $_port;
    private $_expiration;
    private $_namespace;

    private $_defaultParams = array(
        '_host' => '127.0.0.1',
        '_port' => '11211',
        '_namespace' => 'wurfl',
        '_expiration' => 0
);

    public function __construct($params = array())
    {
        $currentParams = is_array($params) ? array_merge($this->_defaultParams, $params) : $this->_defaultParams;
        $this->_toFields($currentParams);
        $this->initialize();
    }

    private function _toFields($params)
    {
        foreach ($params as $key => $value) {
            $key        = '_' . $key;
            $this->$key = $value;
        }
    }

    /**
     * Initializes the Memcache Module
     *
     */
    final public function initialize()
    {
        $this->_ensureModuleExistence();
        $this->_memcache = new \Memcache();
        // sup_port multiple _hosts using semicolon to separate _hosts
        $_hosts = explode(';', $this->_host);
        // different _ports for each _hosts the same way
        $_ports = explode(';', $this->_port);

        if (count($_hosts) > 1) {
            if (count($_ports) < 1) {
                $_ports = array_pad(count($_hosts), self::DEFAULT_PORT);
            } elseif(count($_ports) == 1) {
                // if we have just one _port, use it for all _hosts
                $_p = $_ports[0];
                $_ports = array_fill(0, count($_hosts), $_p);
            }
            foreach ($_hosts as $i => $_host) {
                $this->_memcache->addServer($_host, $_ports[$i]);
            }
        } else {
            // just connect to the single _host
            $this->_memcache->connect($_hosts[0], $_ports[0]);
        }
    }

    public function save($objectId, $object)
    {
        return $this->_memcache->set($this->encode($this->_namespace, $objectId), $object, FALSE, $this->_expiration);
    }

    public function load($objectId)
    {
        $value = $this->_memcache->get($this->encode($this->_namespace, $objectId));
        return $value ? $value : null;
    }


    public function clear()
    {
        $this->_memcache->flush();
    }


    /**
     * Ensures the existence of the the PHP Extension _memcache
     * @throws \Wurfl\Xml\PersistenceProvider\Exception required extension is unavailable
     */
    private function _ensureModuleExistence()
    {
        if (!extension_loaded(self::EXTENSION_MODULE_NAME)) {
            throw new \Wurfl\Xml\PersistenceProvider\Exception('The PHP extension _memcache must be installed and loaded in order to use the Memcached.');
        }
    }

}