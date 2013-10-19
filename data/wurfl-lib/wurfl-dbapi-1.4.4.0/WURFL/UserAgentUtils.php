<?php
/**
 * Copyright (c) 2011 ScientiaMobile, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Refer to the COPYING.txt file distributed with this package.
 *
 * @package    WURFL
 * @copyright  ScientiaMobile, Inc.
 * @author     Steve Kamerman <steve AT scientiamobile.com>
 * @license    GNU Affero General Public License
 * @version    $id$
 */
/**
 * Provides static functions for working with User Agents
 * @package TeraWurfl
 *
 */
class UserAgentUtils {

    /**
     * @var int Minimum allowable matched string length
     */
	public static $WORST_MATCH = 7;
	
	/**
	 * Find the matching Device ID for a given User Agent using RIS (Reduction in String) 
	 * @param string $ua User Agent
	 * @param int $tolerance How short the strings are allowed to get before a match is abandoned
	 * @param UserAgentMatcher $matcher The UserAgentMatcher instance that is matching the User Agent
	 * @return string WURFL ID
	 */
	public static function risMatch($ua, $tolerance, UserAgentMatcher $matcher) {
		// PHP RIS Function
		$devices =& $matcher->deviceList;
		// Exact match
		$key = array_search($ua, $devices);
		if ($key !== false) {
			return $key;
		}
		// Narrow results to those that match the tolerance level
		$curlen = strlen($ua);
		while ($curlen >= $tolerance) {
			foreach ($devices as $testID => $testUA) {
				// Comparing substrings may be faster, but you would need to use strcmp() on the subs anyway,
				// so this is probably the fastest - maybe preg /^$test/ would be faster???
				//echo "testUA: $testUA, ua: $ua\n<br/>";
				if(strpos($testUA, $ua) === 0){
					return $testID;
				}
			}
			$ua = substr($ua,0,strlen($ua)-1);
			$curlen = strlen($ua);
        }
        return WurflConstants::NO_MATCH;
	}
	/**
	 * Find the matching Device ID for a given User Agent using LD (Leveshtein Distance)
	 * @param string $ua User Agent
	 * @param int $tolerance Tolerance that is still considered a match
	 * @param UserAgentMatcher $matcher The UserAgentMatcher instance that is matching the User Agent
	 * @return string WURFL ID
	 */
	public static function ldMatch($ua, $tolerance=null, $matcher) {
		// PHP Leveshtein Distance Function
		if (is_null($tolerance)) {
			$tolerance = self::$WORST_MATCH;
		}
		$devices =& $matcher->deviceList;
		$key = array_search($ua,$devices);
		if ($key !== false) {
			return $key;
		}
		$best = $tolerance;
		$current = 0;
		$match = WurflConstants::NO_MATCH;
		foreach ($devices as $testID => $testUA) {
			$current = @levenshtein($ua, $testUA);
			//if(strlen($ua) > 255 || strlen($testUA) > 255) echo "<pre>$ua\n$testUA</pre><hr/>";
			if ($current <= $best) {
				$best = $current;
				$match = $testID;
			}
		}
		return $match;
	}
    /**
     * Checks for traces of mobile device signatures and returns an appropriate generic WURFL Device ID
     * @param TeraWurflHttpRequest $httpRequest HTTP Request
     * @return string WURFL ID
     */
	public static function lastAttempts(TeraWurflHttpRequest $httpRequest) {
		//before we give up and return generic, one last
		//attempt to catch well-behaved Nokia and Openwave browsers!
		if($httpRequest->user_agent->contains('UP.Browser/7')) return 'opwv_v7_generic';
		if($httpRequest->user_agent->contains('UP.Browser/6')) return 'opwv_v6_generic';
		if($httpRequest->user_agent->contains('UP.Browser/5')) return 'upgui_generic';
		if($httpRequest->user_agent->contains('UP.Browser/4')) return 'uptext_generic';
		if($httpRequest->user_agent->contains('UP.Browser/3')) return 'uptext_generic';
		if($httpRequest->user_agent->contains('Series60'))     return 'nokia_generic_series60';
		if($httpRequest->user_agent->contains('Mozilla/4.0'))  return 'generic_web_browser';
		if($httpRequest->user_agent->contains('Mozilla/5.0'))  return 'generic_web_browser';
		if($httpRequest->user_agent->contains('Mozilla/6.0'))  return 'generic_web_browser';
		return WurflConstants::NO_MATCH;
	}
	
    /**
     * Calculate the levenshtein distance between $s and $t
     * @param string $s
     * @param string $t
     * @return int
     */
	public static function LD($s, $t) {
		// PHP's levenshtein() function requires arguments to be <= 255 chars
		if(strlen($s) > 255 || strlen($t) > 255){
			return levenshtein(substr($s, 0, 255),substr($t, 0, 255));
		}
		return levenshtein($s, $t);
	}
}