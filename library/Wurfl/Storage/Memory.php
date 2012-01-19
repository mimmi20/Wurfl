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
 * @version   SVN: $Id$
 */
/**
 * WURFL Storage
 * @package    WURFL_Storage
 */
class Memory extends Base
{
    const IN_MEMORY = 'memory';

    protected $_persistenceIdentifier = 'MEMORY_PERSISTENCE_PROVIDER';

    private $_defaultParams = array(
        '_namespace' => 'wurfl'
    );

    private $_namespace;
    private $_map;

    public function __construct($params=array())
    {
        $currentParams = is_array($params) ? array_merge($this->_defaultParams, $params) : $this->_defaultParams;
        $this->_namespace = $currentParams['_namespace'];
        $this->_map = array();
    }

    public function save($objectId, $object)
    {
        $this->_map[$this->encode($this->_namespace, $objectId)] = $object;
    }

    public function load($objectId)
    {
        $key = $this->encode($this->_namespace, $objectId);
        if (isset($this->_map[$key])) {
            return $this->_map[$key];
        }

        return NULL;

    }

    public function remove($objectId)
    {
        $key = $this->encode($this->_namespace, $objectId);
        if ($this->_map[$key]) {
            unset($this->_map[$key]);
        }

    }

    /**
     * Removes all entry from the Persistence Provier
     *
     */
    public function clear()
    {
        unset($this->_map);
    }
}
