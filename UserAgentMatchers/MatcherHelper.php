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
abstract class MatcherHelper
{
	/**
	 * @var TeraWurfl Running instance of Tera-WURFL
	 */
	protected $wurfl = null;
    
    protected $userAgent = '';
    
	/**
	 * WURFL IDs that are hardcoded in this connector.  Used for compatibility testing against new WURFLs
	 * @var array
	 */
	public static $constantIDs = array();
    
	/**
	 * @var Array List of WURFL IDs => User Agents.  Typically used for matching user agents.
	 */
	public $deviceList;
	
	public function __construct(TeraWurfl\TeraWurfl $wurfl, $userAgent = '')
    {
		$this->wurfl     = $wurfl;
        $this->userAgent = $userAgent;
	}
    
    /**
     * Check if user agent contains target string
     * @param String User agent
     * @param String Target string or array of strings
     * @return Bool
     */
    public function contains($find)
    {
    	if (is_array($find)) {
    		foreach ($find as $part) {
                if ($this->contains($part)) {
    				return true;
    			}
    		}
            
    		return false;
    	} else {
	    	return (strpos($this->userAgent, $find) !== false);
    	}
    }
    
    /**
     * Check if user agent starts with target string
     * @param String User agent
     * @param String Target string or array of strings
     * @return Bool
     */
    public function startsWith($find)
    {
    	if (is_array($find)) {
    		foreach ($find as $part) {
    			if ($this->startsWith($part)) {
    				return true;
    			}
    		}
            
    		return false;
    	} else {
	    	return (strpos($this->userAgent, $find)===0);
    	}
    }
    
    /**
     * Check if user agent contains another string using PCRE (Perl Compatible Reqular Expressions)
     * @param String User agent
     * @param $find Target regex string or array of regex strings
     * @return Bool
     */
    public function regexContains($find)
    {
	    if (is_array($find)) {
    		foreach ($find as $part) {
    			if ($this->regexContains($part)) {
    				return true;
    			}
    		}
            
    		return false;
    	}else{
	    	return (preg_match($find, $this->userAgent));
    	}
    }
    
	/**
	 * The character position of the first slash.  If there are no slashes, returns string length
	 * @param String User Agent
	 * @return int Character position
	 */
	protected function firstSlash()
    {
		return $this->searchInAgent('/');
	}
    
	/**
	 * The character position of the second slash.  If there is no second slash, returns string length
	 * @param String User Agent
	 * @return int Character position
	 */
	protected function secondSlash()
    {
		$first = strpos($this->userAgent, '/');
		$first++;
		$position = strpos($this->userAgent, '/', $first);
		return ($position !== false) ? $position : strlen($this->userAgent);
	}
    
	/**
	 * The character position of the first open parenthisis.  If there are no open parenthisis, returns string length
	 * @param String User Agent
	 * @return int Character position
	 */
	protected function firstOpenParen()
    {
		return $this->searchInAgent('(');
	}
    
	/**
	 * Number of slashes ('/') found in the given user agent
	 * @param String User Agent
	 * @return int Count
	 */
	protected function numSlashes()
    {
		return substr_count($this->userAgent, '/');
	}
    
    /**
     * Returns the character position (index) of the target string in the given user agent, starting from a given index.  If target is not in user agent, returns length of user agent.
     * @param String User agent
     * @param String Target string to search for
     * @param int Character postition in the user agent at which to start looking for the target
     * @return int Character position (index) or user agent length
     */
	protected function indexOfOrLength($target, $startingIndex) 
    {
		$length = strlen($this->userAgent);
        
		if ($startingIndex === false) {
			return $length;
		}
		$pos = strpos($this->userAgent, $target, $startingIndex);
		return ($pos === false) ? $length : $pos;
	}
    
	/**
	 * The character position of the first space.  If there are no spaces, returns string length
	 * @param String User Agent
	 * @return int Character position
	 */
	protected function firstSpace()
    {
		return $this->searchInAgent(' ');
	}
    
	/**
	 * The character position of the first space.  If there are no spaces, returns string length
	 * @param String User Agent
	 * @return int Character position
	 */
	protected function searchInAgent($search)
    {
		$position = strpos($this->userAgent, $search);
		return ($position !== false) ? $position : strlen($this->userAgent);
	}
}
