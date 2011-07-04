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
class DoCoMo extends AbstractMatcher 
{
    
    public static $constantIDs = array("docomo_generic_jap_ver2","docomo_generic_jap_ver1");
    
    public function applyConclusiveMatch() 
    {
        $deviceId = '';
        
        if ($this->helper->numSlashes() >= 2) {
            $tolerance = $this->helper->secondSlash();
        } else {
            //  DoCoMo/2.0 F01A(c100;TB;W24H17)
            $tolerance = $this->helper->firstOpenParen();
        }
        
        $deviceId = $this->risMatch($this->userAgent, $tolerance);
        return $deviceId;
    }
    
    public function recoveryMatch()
    {
        $versionIndex = 7;
        $version      = $this->userAgent[$versionIndex];
        return ($version == '2') ? "docomo_generic_jap_ver2" : "docomo_generic_jap_ver1";
    }
}

