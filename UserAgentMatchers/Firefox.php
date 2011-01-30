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
class Firefox extends AbstractMatcher 
{
    
    public static $constantIDs = array("firefox_1","firefox_1_5","firefox_2","firefox_3","firefox_3_5");
    
    public function applyConclusiveMatch() 
    {
        $matches = array();
        if (preg_match('/Firefox\/(\d)\.(\d)/', $ua, $matches)) {
            switch ($matches[1]) {
                // cases are intentionally out of sequnce for performance
                case 3:
                    return ($matches[2] == 5)? 'firefox_3_5': 'firefox_3';
                    break;
                case 2:
                    return 'firefox_2';
                    break;
                case 1:
                    return ($matches[2] == 5)? 'firefox_1_5': 'firefox_1';
                    break;
                default:
                    //return 'firefox';
                    break;
            }
        }
        
        return $this->ldMatch($ua, 5);
    }
}
