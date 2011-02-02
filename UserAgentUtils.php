<?php
declare(ENCODING = 'iso-8859-1');
namespace TeraWurfl;

/**
 * Tera_WURFL - PHP MySQL driven WURFL
 * 
 * Tera-WURFL was written by Steve Kamerman, and is based on the
 * Java WURFL Evolution package by Luca Passani and WURFL PHP Tools by Andrea Trassati.
 * This version uses a MySQL database to store the entire WURFL file, multiple patch
 * files, and a persistent caching mechanism to provide extreme performance increases.
 * 
 * @package TeraWurfl
 * @author Steve Kamerman <stevekamerman AT gmail.com>
 * @version Stable 2.1.3 $Date: 2010/09/18 15:43:21
 * @license http://www.mozilla.org/MPL/ MPL Vesion 1.1
 */
/**
 * Provides static functions for working with User Agents
 * @package TeraWurfl
 *
 */
class UserAgentUtils
{
    public static $WORST_MATCH = 7;
    
    /**
     * Find the matching Device ID for a given User Agent using RIS (Reduction in String) 
     * @param string User Agent
     * @param int How short the strings are allowed to get before a match is abandoned
     * @param UserAgentMatcher The UserAgentMatcher instance that is matching the User Agent
     * @return string WURFL ID
     */
    public static function risMatch($ua, $tolerance, UserAgentMatchers\AbstractMatcher $matcher)
    {
        // PHP RIS Function
        $devices = $matcher->deviceList;
        
        // Exact match
        $key = array_search($ua, $devices);
        
        if ($key !== false) {
            return $key;
        }
        
        // Narrow results to those that match the tolerance level
        $curlen = strlen($ua);
        
        if (count($devices)) {
            while($curlen >= $tolerance){
                foreach($devices as $testID => $testUA){
                    // Comparing substrings may be faster, but you would need to use strcmp() on the subs anyway,
                    // so this is probably the fastest - maybe preg /^$test/ would be faster???
                    //echo "testUA: $testUA, ua: $ua\n<br/>";
                    if(strpos($testUA,$ua) === 0){
                        return $testID;
                    }
                }
                $ua = substr($ua,0,strlen($ua)-1);
                $curlen = strlen($ua);
            }
        }
        return Constants::GENERIC;
    }
    
    /**
     * Find the matching Device ID for a given User Agent using LD (Leveshtein Distance)
     * @param string User Agent
     * @param int Tolerance that is still considered a match
     * @param UserAgentMatcher The UserAgentMatcher instance that is matching the User Agent
     * @return string WURFL ID
     */
    public static function ldMatch($ua,$tolerance=null,$matcher)
    {
        // PHP Leveshtein Distance Function
        if(is_null($tolerance)){
            $tolerance = self::$WORST_MATCH;
        }
        $devices = $matcher->deviceList;
        $key     = array_search($ua, $devices);
        if($key !== false){
            return $key;
        }
        $best = $tolerance;
        $current = 0;
        $match = Constants::GENERIC;
        foreach($devices as $testID => $testUA){
            $current = levenshtein($ua, $testUA);
            //echo "<hr/>$ua<br/>$testUA<br/>LD: $current<br/>";
            if($current <= $best){
                $best = $current;
                $match = $testID;
            }
        }
        return $match;
    }
    
    /**
     * Removes Vodafone garbage from user agent string
     * @param String User agent
     * @return String User agent
     */
    public static function removeVodafonePrefix($ua)
    {
        return preg_replace('/^Vodafone\/(\d\.\d\/)?/','',$ua,1);
    }
    
    /**
     * The character postition of the Nth occurance of a target string in a user agent
     * @param String User agent
     * @param String Target string to search for in user agent
     * @param int The Nth occurence to find
     * @return int Character position
     */
    public static function ordinalIndexOf($ua, $needle, $ordinal) 
    {
        if (is_null($ua) || empty($ua) || !is_integer($ordinal)) {
            return -1;
        }
        $found = 0;
        $index = -1;
        do {
            $index = strpos($ua, $needle, $index + 1);
            $index = is_int($index) ? $index : -1;
            if ($index < 0) {
                return $index;
            }
            $found++;
        } while($found < $ordinal);
        return $index;
    
    }
    
    /**
     * Checks for traces of mobile device signatures and returns an appropriate generic WURFL Device ID
     * @param String User agent
     * @return String WURFL ID
     */
    public static function lastAttempts($ua)
    {
        //before we give up and return generic, one last
        //attempt to catch well-behaved Nokia and Openwave browsers!
        if(self::checkIfContains($ua, 'UP.Browser/7'))
            return 'opwv_v7_generic';
        if(self::checkIfContains($ua, 'UP.Browser/6'))
            return 'opwv_v6_generic';
        if(self::checkIfContains($ua, 'UP.Browser/5'))
            return 'upgui_generic';
        if(self::checkIfContains($ua, 'UP.Browser/4'))
            return 'uptext_generic';
        if(self::checkIfContains($ua, 'UP.Browser/3'))
            return 'uptext_generic';
        if(self::checkIfContains($ua, 'Series60'))
            return 'nokia_generic_series60';
        if(self::checkIfContains($ua, 'Mozilla/4.0'))
            return 'generic_web_browser';
        if(self::checkIfContains($ua, 'Mozilla/5.0'))
            return 'generic_web_browser';
        if(self::checkIfContains($ua, 'Mozilla/6.0'))
            return 'generic_web_browser';
        
        return TeraWurfl\Constants::GENERIC;
    }
    
    /**
     * The given user agent is definitely from a mobile device
     * @param String User agent
     * @return Bool
     */
    public static function isMobileBrowser($ua)
    {
        if (self::isDesktopBrowser($ua) || self::isRobot($ua)) {
            return false;
        }
        
        $helper = new UserAgentMatchers\MatcherHelper($ua);
        
        if ($helper->contains(Constants::$MOBILE_BROWSERS)) {
            return true;
        }
        
        if ($helper->regexContains(array(
                // ARM Processor
                '/armv[5-9][l0-9]/',
                // Screen resolution in UA
                '/[^\d]\d{3}x\d{3}/'
            )
        )){
            return true;
        }
        return false;
    }
    
    /**
     * The given user agent is definitely from a desktop browser
     * @param String User agent
     * @return Bool
     */
    public static function isDesktopBrowser($ua)
    {
        $helper = new UserAgentMatchers\MatcherHelper($ua);
        
        if ($helper->contains(Constants::$DESKTOP_BROWSERS)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * The given user agent is definitely from a bot/crawler
     * @param String User agent
     * @return Bool
     */
    public static function isRobot($ua)
    {
        $helper = new UserAgentMatchers\MatcherHelper($ua);
        
        if ($helper->contains(Constants::$ROBOTS)) {
            return true;
        }
        
        return false;
    }
    
    public static function LD($s, $t)
    {
        // PHP's levenshtein() function requires arguments to be <= 255 chars
        return levenshtein(substr($s, 0, 255), substr($t, 0, 255));
    }
}
