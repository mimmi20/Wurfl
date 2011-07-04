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
class Apple extends AbstractMatcher 
{
    public static $constantIDs = array("apple_ipod_touch_ver1","apple_ipad_ver1","apple_iphone_ver1");
    
    public function applyConclusiveMatch() 
    {
        $deviceId  = '';
        $tolerance = $this->helper->indexOfOrLength(';', 0);
        $deviceId  = $this->helper->risMatch($tolerance);
        
        return $deviceId;
    }
    
    public function recoveryMatch()
    {
        if ($this->helper->contains('iPod')) {
            return 'apple_ipod_touch_ver1';
        }
        
        if ($this->helper->contains('iPad')) {
            return 'apple_ipad_ver1';
        }
        
        if ($this->helper->contains("iPhone")) {
            return 'apple_iphone_ver1';
        }
        
        return \TeraWurfl\Constants::GENERIC;
    }
}
