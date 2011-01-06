<?php
/**
 * Tera_WURFL - PHP MySQL driven WURFL
 * 
 * Tera-WURFL was written by Steve Kamerman, and is based on the
 * Java WURFL Evolution package by Luca Passani and WURFL PHP Tools by Andrea Trassati.
 * This version uses a MySQL database to store the entire WURFL file, multiple patch
 * files, and a persistent caching mechanism to provide extreme performance increases.
 * 
 * @package TeraWurflDatabase
 * @author Steve Kamerman <stevekamerman AT gmail.com>
 * @version Stable 2.1.3 $Date: 2010/09/18 15:43:21
 * @license http://www.mozilla.org/MPL/ MPL Vesion 1.1
 */
/**
 * Include required files
 */
require_once realpath(dirname(__FILE__).'/TeraWurflDatabase_MySQL5.php');
/**
 * Include required files
 */
require_once realpath(dirname(__FILE__).'/phpMyProfiler.php');
/**
 * Provides connectivity from Tera-WURFL to MySQL 5
 * This "Profiling" connector logs profile data from MySQL during its queries
 * @package TeraWurflDatabase
 */
class TeraWurflDatabase_MySQL5_Profiling extends TeraWurflDatabase_MySQL5{
	protected $profiler;
	/**
	 * The path and file prefix to use for storing MySQL Query Profiles
	 * @var string
	 */
	protected $profile_log = "/tmp/TeraWurflProfile-";
	/**
	 * Establishes connection to database (does not check for DB sanity)
	 */
	public function connect(){
		parent::connect();
		$this->profiler = new phpMyProfiler($this->dbcon,$this->profile_log);
	}
	public function getDeviceFromUA_RIS($userAgent,$tolerance,UserAgentMatcher &$matcher){
		$return = parent::getDeviceFromUA_RIS($userAgent,$tolerance,$matcher);
		$this->profiler->log();
		return $return;
	}
}
