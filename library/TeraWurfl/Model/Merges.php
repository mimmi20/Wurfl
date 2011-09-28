<?php
declare(ENCODING = 'utf-8');
namespace TeraWurfl\Model;

/**
 * Model
 *
 * PHP version 5
 *
 * @category  CreditCalc
 * @package   Models
 * @author    Thomas Mueller <t_mueller_stolzenhain@yahoo.de>
 * @copyright 2007-2010 Unister GmbH
 * @version   SVN: $Id: Campaigns.php 24 2011-02-01 20:55:24Z tmu $
 */

/**
 * Model
 *
 * @category  CreditCalc
 * @package   Models
 * @author    Thomas Mueller <t_mueller_stolzenhain@yahoo.de>
 * @copyright 2007-2010 Unister GmbH
 */
class Merges extends ModelAbstract
{
    /**
     * Table name
     *
     * @var String
     */
    protected $_name = 'merge';

    /**
     * Primary key
     *
     * @var String
     */
    protected $_primary = 'deviceID';
    
    public function getDeviceFromID($wurflID)
    {
        /**
         * @var Zend_Db_Table_Select
         */
        $select = $this->select()->setIntegrityCheck(false);
        
        $select->from(
            array('m' => $this->_name),
            array('m.capabilities')
        );
        $select->where('deviceID = ?', $wurflID);
        
        $result = $this->fetchAll($select)->current();
        
        return unserialize($result->capabilities);
    }
    
    public function getActualDeviceAncestor($wurflID)
    {
        if ($wurflID == '' || $wurflID == \TeraWurfl\Constants::GENERIC) {
            return \TeraWurfl\Constants::GENERIC;
        }
        
        $device = $this->getDeviceFromID($wurflID);
        
        if ($device['actual_device_root']) {
            return $device['id'];
        } else {
            return $this->getActualDeviceAncestor($device['fall_back']);
        }
    }
    
    public function getFullDeviceList($tablename)
    {
        /**
         * @var Zend_Db_Table_Select
         */
        $select = $this->select()->setIntegrityCheck(false);
        
        $select->from(
            array('x' => strtolower($tablename)),
            array('deviceID' => 'x.deviceID', 'userAgent' => 'x.user_agent')
        );
        
        $select->where('x.match = ?', 1);
        
        $result = $this->fetchAll($select);
        
        if (0 == count($result)) {
            return array();
        }
        
        $data = array();
        foreach ($result as $row) {
            $data[$row->deviceID] = $row->userAgent;
        }
        
        return $data;
    }
    
    // Exact Match
    public function getDeviceFromUA($userAgent)
    {
        /**
         * @var Zend_Db_Table_Select
         */
        $select = $this->select()->setIntegrityCheck(false);
        
        $select->from(
            array('m' => $this->_name),
            array('m.deviceID')
        );
        $select->where('m.user_agent = ?', $userAgent);
        $select->limit(1);
        
        $result = $this->fetchAll($select);
        
        if (0 == count($result)) {
            return false;
        }
        
        $result = $result->current();
        
        return $result->deviceID;
    }
    
    public function getDeviceFallBackTree($wurflID)
    {
        $query = 'CALL ' . $this->_tableprefix . '_FallBackDevices( :id )';
        $stmt  = new \Zend\Db\Statement\Pdo($this->_db, $query);
        $stmt->bindParam(':id', $wurflID, \PDO::PARAM_STR);
        $stmt->execute();
        
        /**
         * @var array
         */
        $rows = $stmt->fetchAll(\PDO::FETCH_CLASS);
        
        $i = 0;
        
        foreach ($rows as $row) {
            $data[$i++] = unserialize($row->capabilities);
        };
        
        return $data;
    }
    
    // RIS == Reduction in String (reduce string one char at a time)
    public function getDeviceFromUA_RIS($userAgent,$tolerance,UserAgentMatcher &$matcher)
    {
        $query = 'SELECT RIS(:ua,:tol,:matcher) AS deviceID';
        $stmt  = new \Zend\Db\Statement\Pdo($this->_db, $query);
        $stmt->bindParam(':ua', $userAgent, \PDO::PARAM_STR);
        $stmt->bindParam(':tol', $tolerance, \PDO::PARAM_STR);
        $stmt->bindParam(':matcher', $matcher->tableSuffix(), \PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll();
        $wurflid = $data[0]->DeviceID;
        return ($wurflid == 'NULL' || is_null($wurflid))? \TeraWurfl\Constants::GENERIC : $wurflid;
    }
    
    // TODO: Implement with Stored Proc
    // LD == Levesthein Distance
    public function getDeviceFromUA_LD($userAgent, $tolerance, UserAgentMatcher &$matcher)
    {
        /*
        throw new Exception("Error: this function (LD) is not yet implemented in MySQL");
        $safe_ua = $this->SQLPrep($userAgent);
        $this->numQueries++;
        //$res = $this->dbcon->query("call ".TeraWurflConfig::$TABLE_PREFIX."_LD($safe_ua,$tolerance)");
        // TODO: check for false
        $data = array();
        while($row = $res->fetch_assoc()){
            $data[]=$row;
        }
        $this->cleanConnection();
        return $data;
        /**/
    }
}