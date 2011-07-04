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
class Vodafone extends UserAgentMatcher 
{
    public function applyConclusiveMatch($this->userAgent) 
    {
        $clean_ua = $this->userAgent;
        if($this->contains($this->userAgent,"/SN") && !$this->contains($this->userAgent,"XXXXXXXXXXXX")){
            //not using RegEx for the time being
            $start_idx = strpos($this->userAgent,"/SN")+3;
            $sub_str = substr($this->userAgent,$start_idx);
            $end_idx = strpos($sub_str," ");
            if($end_idx !== false && $sub_str != "" && strlen($sub_str) > $end_idx){
                $num_digits = strlen($sub_str) - $end_idx;
                $new_ua = substr($this->userAgent,0,$start_idx);
                for($i=0;$i<$end_idx;$i++){
                    $new_ua .= "X";
                }
                $new_ua .= substr($this->userAgent,$end_idx);
                $clean_ua = $new_ua;
            }
        }
        
        $tolerance = $this->firstSlash($clean_ua);
        //$this->wurfl->toLog("Applying ".get_class($this)." Conclusive Match: RIS with threshold $tolerance",LOG_INFO);
        $match = $this->risMatch($clean_ua, $tolerance);
        if($match == TeraWurfl\Constants::GENERIC){
            //$this->wurfl->toLog("Applying ".get_class($this)." Conclusive Match: LD",LOG_INFO);
            return $this->ldMatch($this->userAgent);
        }
        return $match;
    }
}
