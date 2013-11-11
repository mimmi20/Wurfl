<?php
namespace Wurfl\Storage;

    /**
     * Copyright (c) 2012 ScientiaMobile, Inc.
     * This program is free software: you can redistribute it and/or modify
     * it under the terms of the GNU Affero General Public License as
     * published by the Free Software Foundation, either version 3 of the
     * License, or (at your option) any later version.
     * Refer to the COPYING.txt file distributed with this package.
     *
     * @category   WURFL
     * @package    \Wurfl\Storage
     * @copyright  ScientiaMobile, Inc.
     * @license    GNU Affero General Public License
     * @author     Fantayeneh Asres Gizaw
     * @version    $id$
     */
/**
 * APC Storage class
 *
 * @package    \Wurfl\Storage
 */
class Apc extends Base
{
    const EXTENSION_MODULE_NAME = "apc";
    private $currentParams = array(
        "namespace"  => "wurfl",
        "expiration" => 0
    );

    protected $is_volatile = true;

    public function __construct($params = array())
    {
        if (is_array($params)) {
            array_merge($this->currentParams, $params);
        }
        //$this->initialize();
    }

    public function initialize()
    {
        $this->ensureModuleExistence();
    }

    public function save($objectId, $object, $expiration = null)
    {
        $value = apc_store(
            $this->encode($this->apcNameSpace(), $objectId), $object,
            (($expiration === null) ? $this->expire() : $expiration)
        );
        if ($value === false) {
            throw new Exception("Error saving variable in APC cache. Cache may be full.");
        }
    }

    public function load($objectId)
    {
        $value = apc_fetch($this->encode($this->apcNameSpace(), $objectId));

        return ($value !== false) ? $value : null;
    }

    public function remove($objectId)
    {
        apc_delete($this->encode($this->apcNameSpace(), $objectId));
    }

    /**
     * Removes all entry from the Persistence Provider

     */
    public function clear()
    {
        apc_clear_cache("user");
    }

    private function apcNameSpace()
    {
        return $this->currentParams["namespace"];
    }

    private function expire()
    {
        return $this->currentParams["expiration"];
    }

    /**
     * Ensures the existence of the the PHP Extension apc
     *
     * @throws Exception required extension is unavailable
     */
    private function ensureModuleExistence()
    {
        if (!(extension_loaded(self::EXTENSION_MODULE_NAME) && ini_get('apc.enabled') == true)) {
            throw new Exception ("The PHP extension apc must be installed, loaded and enabled.");
        }
    }
}