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
 * APC Storage class
 * @package    WURFL_Storage
 */
class Apc extends Base
{
    const EXTENSION_MODULE_NAME = 'apc';
    
    private $_currentParams = array(
        'namespace' => 'wurfl',
        'expiration' => 0
    );
    
    public function __construct($params = array())
    {
        if (is_array($params))  {
            array_merge($this->_currentParams, $params);
        }
        //$this->initialize();
    }

    public function initialize()
    {
        $this->_ensureModuleExistence();
    }

    public function save($objectId, $object)
    {
        apc_store($this->encode($this->_apcNameSpace(), $objectId), $object, $this->_expire());
    }

    public function load($objectId)
    {
        $value = apc_fetch($this->encode($this->_apcNameSpace(), $objectId));
        return $value !== false ? $value : null;
    }

    public function remove($objectId)
    {
        apc_delete($this->encode($this->_apcNameSpace(), $objectId));
    }

    /**
     * Removes all entry from the Persistence Provider
     *
     */
    public function clear()
    {
        apc_clear_cache('user');
    }


    private function _apcNameSpace()
    {
        return $this->_currentParams['namespace'];
    }

    private function _expire()
    {
        return $this->_currentParams['expiration'];   
    }

    /**
     * Ensures the existence of the the PHP Extension apc
     * @throws \Wurfl\Xml\PersistenceProvider\Exception required extension is unavailable
     */
    private function _ensureModuleExistence()
    {
        if (!(extension_loaded(self::EXTENSION_MODULE_NAME) && ini_get('apc.enabled') == true)) {
            throw new \Wurfl\Xml\PersistenceProvider\Exception('The PHP extension apc must be installed, loaded and enabled.');
        }
    }

}