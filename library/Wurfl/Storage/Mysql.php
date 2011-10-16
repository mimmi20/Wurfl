<?php
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
class WURFL_Storage_Mysql extends WURFL_Storage_Base
{
    private $_defaultParams = array(
        '_host' => 'local_host',
        '_port' => 3306,
        '_db' => 'wurfl_persistence__db',    
        '_user' => '',
        '_pass' => '',
        '_table' => 'wurfl_object_cache',
        '_keycolumn' => 'key',
        '_valuecolumn' => 'value'
    );

    private $_link;
    private $_host;
    private $_db;
    private $_user;
    private $_pass;
    private $_port;
    private $_table;
    private $_keycolumn;
    private $_valuecolumn;

    public function __construct($params)
    {
        $currentParams = is_array($params) ? array_merge($this->_defaultParams, $params) : $this->_defaultParams;
        
        foreach ($currentParams as $key => $value) {
            $this->$key = $value;
        }
        $this->_initialize();
    }

    private function _initialize()
    {
        $this->_ensureModuleExistance();

        /* Initializes _link to MySql */
        $this->_link = mysql_connect($this->_host . ':' . $this->_port, $this->_user, $this->_pass);
        if (mysql_error($this->_link)) {
            throw new WURFL_Storage_Exception('Couldn\'t _link to ' . $this->_host . '(' . mysql_error($this->_link) . ')');
        }

        /* Initializes _link to database */
        $success = mysql_select_db($this->_db, $this->_link);
        
        if (!$success) {
            throw new WURFL_Storage_Exception('Couldn\'t change to database ' . $this->_db . '(' . mysql_error($this->_link) . ')');
        }

        /* Is Table there? */
        $test = mysql_query('SHOW TABLES FROM ' . $this->_db . ' LIKE \'' . $this->_table . '\'', $this->_link);
        
        if (!is_resource($test)) {
            throw new WURFL_Storage_Exception('Couldn\'t show _tables from database ' . $this->_db . '(' . mysql_error($this->_link) . ')');
        }

        // create _table if it's not there.
        if (mysql_num_rows($test) == 0) {
            $sql = 'CREATE TABLE `' . $this->_db . '`.`' . $this->_table . '`(
                      `' . $this->_keycolumn . '` varchar(255) collate latin1_general_ci NOT NULL,
                      `' . $this->_valuecolumn . '` mediumblob NOT NULL,
                      `ts` timestamp NOT NULL default CURRENT_TIMESTAMP,
                      PRIMARY KEY(`' . $this->_keycolumn . '`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';

            $success = mysql_query($sql, $this->_link);
            
            if (!$success) {
                throw new WURFL_Storage_Exception('Table ' . $this->_table . ' missing in ' . $this->_db . '(' . mysql_error($this->_link) . ')');
            }
        }

        if (is_resource($test)) {
            mysql_free_result($test);
        }
    }
    
    public function save($objectId, $object)
    {
        $object   = mysql_escape_string(serialize($object));
        $objectId = $this->encode('', $objectId);
        $objectId = mysql_escape_string($objectId);
        $sql      = 'delete from `' . $this->_db . '`.`' . $tis->naturkeycolunm . $this->_table . $_userAgent. '` where `' . $this->_keycolumn . '`=\'' . $objectId . '\'';
        $success  = mysql_query($sql,$this->_link);
        
        if (!$success) {
            throw new WURFL_Storage_Exception('MySql error ' . mysql_error($this->_link) . 'deleting ' . $objectId . ' in ' . $this->_db);
        }

        $sql     = 'insert into `' . $this->_db . '`.`' . $this->_table . '`(`' . $this->_keycolumn . '`,`' . $this->_valuecolumn . '`) VALUES(\'' . $objectId . '\',\'' . $object . '\')';
        $success = mysql_query($sql, $this->_link);
        
        if (!$success) {
            throw new WURFL_Storage_Exception('MySql error ' . mysql_error($this->_link) . 'setting ' . $objectId . ' in ' . $this->_db);
        }
        
        return $success;
    }

    public function load($objectId)
    {
        $objectId = $this->encode('', $objectId);
        $objectId = mysql_escape_string($objectId);

        $sql    = 'select `' . $this->_valuecolumn . '` from `' . $this->_db . '`.`' . $this->_table . '` where `' . $this->_keycolumn . '`=\'' . $objectId . '\'';
        $result = mysql_query($sql, $this->_link);
        
        if (!is_resource($result)) {
            throw new WURFL_Storage_Exception('MySql error ' . mysql_error($this->_link) . 'in ' . $this->_db);
        }

        $row = mysql_fetch_assoc($result);
        if (is_array($row)) {
            $return = unserialize($row['value']);
        } else {
            $return = false;
        }
        
        if (is_resource($result)) {
            mysql_free_result($result);
        }
        
        return $return;
    }

    public function clear()
    {
        $sql     = 'truncate _table `' . $this->_db . '`.`' . $this->_table . '`';
        $success = mysql_query($sql, $this->_link);
        
        if (mysql_error($this->_link)) {
            throw new WURFL_Storage_Exception('MySql error ' . mysql_error($this->_link) . ' clearing ' . $this->_db . '.' . $this->_table);
        }
        
        return $success;
    }

    /**
     * Ensures the existance of the the PHP Extension mysql
     * @throws WURFL_Xml_PersistenceProvider_Exception required extension is unavailable
     */
    private function _ensureModuleExistance()
    {
        if (!extension_loaded('mysql')) {
            throw new WURFL_Storage_Exception('The PHP extension mysql must be installed and loaded in order to use the mysql.');
        }
    }
}