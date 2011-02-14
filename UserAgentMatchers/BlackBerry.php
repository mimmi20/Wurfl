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
class BlackBerry extends AbstractMatcher 
{
    public static $constantIDs = array(
        'blackberry_generic_ver2',
        'blackberry_generic_ver3_sub2',
        'blackberry_generic_ver3_sub30',
        'blackberry_generic_ver3_sub50',
        'blackberry_generic_ver3_sub60',
        'blackberry_generic_ver3_sub70',
        'blackberry_generic_ver4',
    );
    
    public function applyConclusiveMatch($this->userAgent)
    {
        $this->userAgent = $this->_cleanUa($this->userAgent);
        return parent::applyConclusiveMatch($this->userAgent);
    }
    
    public function recoveryMatch($this->userAgent)
    {
        // BlackBerry
        $this->userAgent = $this->_cleanUa($this->userAgent);
        
        //$this->wurfl->toLog('Applying '.get_class($this).' recovery match ($this->userAgent)',LOG_INFO);
        if ($this->helper->startsWith('BlackBerry')) {
            $position = $this->helper->firstSlash();
            
            if ($position !== false && ($position + 4) <= strlen($this->userAgent)) {
                $version = substr($this->userAgent, $position + 1, $position + 4);
                
                if ($this->helper->startsWith($version, '2.')) {
                    return 'blackberry_generic_ver2';
                }
                if ($this->startsWith($version, '3.2')) {
                    return 'blackberry_generic_ver3_sub2';
                }
                if ($this->startsWith($version, '3.3')) {
                    return 'blackberry_generic_ver3_sub30';
                }
                if ($this->startsWith($version, '3.5')) {
                    return 'blackberry_generic_ver3_sub50';
                }
                if ($this->startsWith($version, '3.6')) {
                    return 'blackberry_generic_ver3_sub60';
                }
                if ($this->startsWith($version, '3.7')) {
                    return 'blackberry_generic_ver3_sub70';
                }
                if ($this->startsWith($version, '4.')) {
                    return 'blackberry_generic_ver4';
                }
            }   
        }     
        
        return \TeraWurfl\Constants::GENERIC;
    }
    
    private function _cleanUa($this->userAgent)
    {
        return preg_replace('/^BlackBerry (\d+.*)$/', 'BlackBerry$1', $this->userAgent);
    }
}

