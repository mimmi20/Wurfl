<?php
namespace TeraWurfl\UserAgentMatchers;

/**
 * Tera_WURFL - PHP MySQL driven WURFL
 * 
 * Tera-WURFL was written by Steve Kamerman, and is based on the
 * Java WURFL Evolution package by Luca Passani and WURFL PHP Tools by Andrea Trassati.
 * This version uses a MySQL database to store the entire WURFL file, multiple patch
 * files, and a persistent caching mechanism to provide extreme performance increases.
 * 
 * @package TeraWurflUserAgentMatchers
 * @author Steve Kamerman <stevekamerman AT gmail.com>
 * @version Stable 2.1.3 $Date: 2010/09/18 15:43:21
 * @license http://www.mozilla.org/MPL/ MPL Vesion 1.1
 */
/**
 * An abstract class that all UserAgentMatchers must extend.
 * @package TeraWurflUserAgentMatchers
 */
abstract class AbstractMatcher
{
    /**
     * @var TeraWurfl Running instance of Tera-WURFL
     */
    protected $wurfl = null;
    
    protected $userAgent = '';
    
    protected $helper = null;
    
    /**
     * WURFL IDs that are hardcoded in this connector.  Used for compatibility testing against new WURFLs
     * @var array
     */
    public static $constantIDs = array();
    
    /**
     * @var Array List of WURFL IDs => User Agents.  Typically used for matching user agents.
     */
    public $deviceList = array();
    
    public function __construct(\TeraWurfl\TeraWurfl $wurfl, $userAgent = '')
    {
        $this->wurfl     = $wurfl;
        $this->userAgent = $userAgent;
        
        $this->helper = new MatcherHelper($userAgent);
    }
    
    /**
     * Attempts to find a conclusively matching WURFL ID from a given user agent
     * @param String User agent
     * @return String Matching WURFL ID
     */
    public function applyConclusiveMatch() 
    {
        $tolerance = $this->firstSlash();
        return $this->risMatch($tolerance);
    }
    
    /**
     * Attempts to find a loosely matching WURFL ID from a given user agent
     * @param String User agent
     * @return String Matching WURFL ID
     */
    public function applyRecoveryMatch()
    {
        return $this->recoveryMatch();
    }
    
    /**
     * Overide this method in order to have an alternative matching algorithm
     * @param String User agent
     * @return String Matching WURFL ID
     */
    public function recoveryMatch()
    {
        return "generic";
    }
    
    /**
     * Updates the deviceList Array to contain all the WURFL IDs that are related to the current UserAgentMatcher
     * @return void
     */
    protected function updateDeviceList()
    {
        if(is_array($this->deviceList) && count($this->deviceList) > 0) {
            return;
        }
        
        $this->deviceList = array();//$this->wurfl->db->getFullDeviceList($this->wurfl->fullTableName());
    }
    
    /**
     * Attempts to match given user agent string to a device from the database by comparing less and less of the strings until a match is found (RIS, Reduction in String)
     * @param String User agent
     * @param int Tolerance, how many characters must match from left to right
     * @return String WURFL ID
     */
    public function risMatch($tolerance)
    {
        /*
        if($this->wurfl->db->db_implements_ris){
            return $this->wurfl->db->getDeviceFromUA_RIS($this->userAgent, $tolerance, $this);
        }
        */
        $this->updateDeviceList();
        return \TeraWurfl\UserAgentUtils::risMatch($this->userAgent,$tolerance,$this);
    }
    
    /**
     * Attempts to match given user agent string to a device from the database by calculating their Levenshtein Distance (LD)
     * @param String User agent
     * @param int Tolerance, how much difference is allowed
     * @return String WURFL ID
     */
    public function ldMatch($tolerance=null)
    {
        if($this->wurfl->db->db_implements_ld){
            return $this->wurfl->db->getDeviceFromUA_LD($this->userAgent,$tolerance,$this);
        }
        $this->updateDeviceList();
        return \TeraWurfl\UserAgentUtils::ldMatch($this->userAgent, $tolerance, $this);
    }
    
    /**
     * Returns the name of the UserAgentMatcher in use
     * @return String UserAgentMatcher name
     */
    public function matcherName()
    {
        return get_class($this);
    }
    
    /**
     * Returns the database table suffix for the current UserAgentMatcher
     * @return String Table suffix
     */
    public function tableSuffix()
    {
        $cname = $this->matcherName();
        return substr($cname, 0, strpos($cname, 'UserAgentMatcher'));
    }
}
