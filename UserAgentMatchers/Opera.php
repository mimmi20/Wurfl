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
 * Provides a specific user agent matching technique
 * @package TeraWurflUserAgentMatchers
 */
class Opera extends AbstractMatcher 
{
    public static $constantIDs = array("opera","opera_7","opera_8","opera_9","opera_10");
    
    public function applyConclusiveMatch() 
    {
        if ($this->helper->contains("Opera/10")) {
            return "opera_10";
        } elseif ($this->helper->contains("Opera/9")) {
            return "opera_9";
        } elseif ($this->helper->contains("Opera/8")) {
            return "opera_8";
        } elseif ($this->helper->contains("Opera/7")) {
            return "opera_7";
        }
        
        $tolerance = 5;
        
        return $this->ldMatch($ua, $tolerance);
    }
    
    public function recoveryMatch()
    {
            return "opera";
    }
}
