<?php
/**
 * Copyright (c) 2012 ScientiaMobile, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Refer to the COPYING.txt file distributed with this package.
 *
 *
 * @category   WURFL
 * @package	WURFL
 * @copyright  ScientiaMobile, Inc.
 * @license	GNU Affero General Public License
 * @version	$id$
 */
/**
 * WURFL user agent hander utilities
 * @package	WURFL
 */
class WURFL_Handlers_Utils {
	
	/**
	 * The worst allowed match tolerance
	 * @var unknown_type
	 */
	const WORST_MATCH = 7;
	
	/**
	 * @var array Collection of mobile browser keywords
	 */
	private static $mobileBrowsers = array (
		'midp',
		'mobile',
		'android',
		'samsung',
		'nokia',
		'up.browser',
		'phone',
		'opera mini',
		'opera mobi',
		'brew',
		'sonyericsson',
		'blackberry',
		'netfront',
		'uc browser',
		'symbian',
		'j2me',
		'wap2.',
		'up.link',
		' arm;',
		'windows ce',
		'vodafone',
		'ucweb',
		'zte-',
		'ipad;',
		'docomo',
		'armv',
		'maemo',
		'palm',
		'bolt',
		'fennec',
		'wireless',
		'adr-',
		// Required for HPM Safari
		'htc',
		// Used to keep Xbox away from the desktop matchers
		'; xbox',
		'nintendo',
		// These keywords keep IE-like mobile UAs out of the MSIE bucket
		// ex: Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; XBLWP7;  ZuneWP7) 
		'zunewp7',
		'skyfire',
		'silk',
		'untrusted',
		'lgtelecom',
		' gt-',
		'ventana',
	);
	
	private static $smartTVBrowsers = array(
		'googletv',
		'boxee',
		'sonydtv',
		'appletv',
		'smarttv',
		'smart-tv',
		'dlna',
		'netcast.tv',
		'ce-html',
		'inettvbrowser',
		'opera tv',
		'viera',
		'konfabulator',
		'sony bravia',
	);
	
	private static $desktopBrowsers = array(
		'wow64',
		'.net clr',
		'gtb7',
		'macintosh',
		'slcc1',
		'gtb6',
		'funwebproducts',
		'aol 9.',
		'gtb8',
		'iceweasel',
		'epiphany',
	);
	
	private static $robots = array(
		'+http',
		'bot',
		'crawler',
		'spider',
		'novarra',
		'transcoder',
		'yahoo! searchmonkey',
		'yahoo! slurp',
		'feedfetcher-google',
		'mowser'
	);
	
	/**
	 * Keyword => deviceID pair collection used for Catch-All matching
	 * @var array Array of (string)keyword => (string)deviceID
	 */
	private static $mobileCatchAllIds = array(
		 // Openwave
		'UP.Browser/7.2' => 'opwv_v72_generic',
		'UP.Browser/7' => 'opwv_v7_generic',
		'UP.Browser/6.2' => 'opwv_v62_generic',
		'UP.Browser/6' => 'opwv_v6_generic',
		'UP.Browser/5' => 'upgui_generic',
		'UP.Browser/4' => 'uptext_generic',
		'UP.Browser/3' => 'uptext_generic',

		// Series 60
		'Series60' => 'nokia_generic_series60',

		// Access/Net Front
		'NetFront/3.0' => 'generic_netfront_ver3',
		'ACS-NF/3.0' => 'generic_netfront_ver3',
		'NetFront/3.1' => 'generic_netfront_ver3_1',
		'ACS-NF/3.1' => 'generic_netfront_ver3_1',
		'NetFront/3.2' => 'generic_netfront_ver3_2',
		'ACS-NF/3.2' => 'generic_netfront_ver3_2',
		'NetFront/3.3' => 'generic_netfront_ver3_3',
		'ACS-NF/3.3' => 'generic_netfront_ver3_3',
		'NetFront/3.4' => 'generic_netfront_ver3_4',
		'NetFront/3.5' => 'generic_netfront_ver3_5',
		'NetFront/4.0' => 'generic_netfront_ver4_0',
		'NetFront/4.1' => 'generic_netfront_ver4_1',
		
		// CoreMedia
		'CoreMedia' => 'apple_iphone_coremedia_ver1',
		
		// Windows CE
		'Windows CE' => 'generic_ms_mobile',

		// Generic XHTML
		'Obigo' => WURFL_Constants::GENERIC_XHTML,
	   	'AU-MIC/2' => WURFL_Constants::GENERIC_XHTML,
		'AU-MIC-' =>  WURFL_Constants::GENERIC_XHTML,
		'AU-OBIGO/' =>  WURFL_Constants::GENERIC_XHTML,
		'Teleca Q03B1' =>  WURFL_Constants::GENERIC_XHTML,

		// Opera Mini
		'Opera Mini/1' => 'generic_opera_mini_version1',
		'Opera Mini/2' => 'generic_opera_mini_version2',
		'Opera Mini/3' => 'generic_opera_mini_version3',
		'Opera Mini/4' => 'generic_opera_mini_version4',
		'Opera Mini/5' => 'generic_opera_mini_version5',

		// DoCoMo
		'DoCoMo' => 'docomo_generic_jap_ver1',
		'KDDI' => 'docomo_generic_jap_ver1',
	);
	
	/** 
	 * Alias of WURFL_Handlers_Matcher_RISMatcher::match()
	 * @param array $collection
	 * @param string $needle
	 * @param int $tolerance
	 * @return string Matched user agent
	 * @see WURFL_Handlers_Matcher_RISMatcher::match()
	 */
	public static function risMatch($collection, $needle, $tolerance) {
		return WURFL_Handlers_Matcher_RISMatcher::INSTANCE ()->match ( $collection, $needle, $tolerance );
	}
	
	/**
	 * Alias of WURFL_Handlers_Matcher_LDMatcher::match()
	 * @param array $collection
	 * @param string $needle
	 * @param int $tolerance
	 * @return string Matched user agent
	 * @see WURFL_Handlers_Matcher_LDMatcher::match()
	 */
	public static function ldMatch($collection, $needle, $tolerance = 7) {
		return WURFL_Handlers_Matcher_LDMatcher::INSTANCE ()->match ( $collection, $needle, $tolerance );
	}
	
	/**
	 * Char index of the first instance of $string found in $target, starting at $startingIndex;
	 * if no match is found, the length of the string is returned
	 * @param string $string haystack
	 * @param string $target needle
	 * @param int $startingIndex Char index for start of search
	 * @return int Char index of match or length of string
	 */
	public static function indexOfOrLength($string, $target, $startingIndex = 0) {
		$length = strlen ( $string );
		$pos = strpos ( $string, $target, $startingIndex );
		return $pos === false ? $length : $pos;
	}
	
	/**
	 * Lowest char index of the first instance of any of the $needles found in $userAgent, starting at $startIndex;
	 * if no match is found, the length of the string is returned
	 * @param string $userAgent haystack
	 * @param array $needles Array of (string)needles to search for in $userAgent
	 * @param int $startIndex Char index for start of search
	 * @return int Char index of left-most match or length of string
	 */
	public static function indexOfAnyOrLength($userAgent, $needles = array(), $startIndex) {
		$positions = array ();
		foreach ( $needles as $needle ) {
			$pos = strpos ( $userAgent, $needle, $startIndex );
			if ($pos !== false) {
				$positions [] = $pos;
			}
		}
		sort ( $positions );		
		return count ( $positions ) > 0 ? $positions [0] : strlen ( $userAgent );
	}
	
	/**
	 * Resets cached detection variables for performance
	 */
	public static function reset() {
		self::$_is_desktop_browser = null;
		self::$_is_mobile_browser = null;
		self::$_is_smarttv = null;
		self::$_is_robot = null;
	}
	
	/**
	 * Returns true if the give $userAgent is from a mobile device
	 * @param string $userAgent
	 * @return bool
	 */
	public static function isMobileBrowser($userAgent) {
		if (self::$_is_mobile_browser !== null) return self::$_is_mobile_browser;
		self::$_is_mobile_browser = false;
		$userAgent = strtolower($userAgent);
		foreach (self::$mobileBrowsers as $key) {
			if (strpos($userAgent, $key) !== false) {
				self::$_is_mobile_browser = true;
				break;
			}
		}
		return self::$_is_mobile_browser;
	}
	private static $_is_mobile_browser;
	
	/**
	 * Returns true if the give $userAgent is from a desktop device
	 * @param string $userAgent
	 * @return bool
	 */
	public static function isDesktopBrowser($userAgent) {
		if (self::$_is_desktop_browser !== null) return self::$_is_desktop_browser;
		self::$_is_desktop_browser = false;
		$userAgent = strtolower($userAgent);
		foreach (self::$desktopBrowsers as $key) {
			if (strpos($userAgent, $key) !== false) {
				self::$_is_desktop_browser = true;
				break;
			}
		}
		return self::$_is_desktop_browser;
	}
	private static $_is_desktop_browser;
	
	/**
	 * Returns true if the give $userAgent is from a robot
	 * @param string $userAgent
	 * @return bool
	 */
	public static function isRobot($userAgent) {
		if (self::$_is_robot !== null) return self::$_is_robot;
		self::$_is_robot = false;
		$userAgent = strtolower($userAgent);
		foreach (self::$robots as $key) {
			if (strpos($userAgent, $key) !== false) {
				self::$_is_robot = true;
				break;
			}
		}
		return self::$_is_robot;
	}
	private static $_is_robot;
	
	/**
	 * Returns true if the give $userAgent is from a mobile device
	 * @param string $userAgent
	 * @return bool
	 */
	public static function getMobileCatchAllId($userAgent) {
		foreach (self::$mobileCatchAllIds as $key => $deviceId) {
			if (strpos($userAgent, $key) !== false) {
				return $deviceId;
			}
		}
		return WURFL_Constants::NO_MATCH;
	}
	
	/**
	 * Is the given user agent very likely to be a desktop browser
	 * @param string $userAgent
	 * @return bool
	 */
	public static function isDesktopBrowserHeavyDutyAnalysis($userAgent){
		// Check Smart TV keywords
		if (WURFL_Handlers_Utils::isSmartTV($userAgent)) return false;
		// Chrome
		if (WURFL_Handlers_Utils::checkIfContains($userAgent, 'Chrome') && !WURFL_Handlers_Utils::checkIfContainsAnyOf($userAgent, array('Android', 'Ventana'))) return true;
		// Check mobile keywords
		if (WURFL_Handlers_Utils::isMobileBrowser($userAgent)) return false;

		if (WURFL_Handlers_Utils::checkIfContains($userAgent, 'PPC')) return false; // PowerPC; not always mobile, but we'll kick it out
		// Firefox;  fennec is already handled in the WURFL_Constants::$MOBILE_BROWSERS keywords
		if (WURFL_Handlers_Utils::checkIfContains($userAgent, 'Firefox') && !WURFL_Handlers_Utils::checkIfContains($userAgent, 'Tablet')) return true;
		// Safari
		if (preg_match('#^Mozilla/5\.0 \((?:Macintosh|Windows)[^\)]+\) AppleWebKit/[\d\.]+ \(KHTML, like Gecko\) Version/[\d\.]+ Safari/[\d\.]+$#', $userAgent)) return true;
		// Opera Desktop
		if (WURFL_Handlers_Utils::checkIfStartsWith($userAgent, 'Opera/9.80 (Windows NT', 'Opera/9.80 (Macintosh')) return true;
		// Check desktop keywords
		if (WURFL_Handlers_Utils::isDesktopBrowser($userAgent)) return true;
		// Internet Explorer 9
		if (preg_match('/^Mozilla\/5\.0 \(compatible; MSIE 9\.0; Windows NT \d\.\d/', $userAgent)) return true;
		// Internet Explorer <9
		if (preg_match('/^Mozilla\/4\.0 \(compatible; MSIE \d\.\d; Windows NT \d\.\d/', $userAgent)) return true;
		return false;
	}
	
	/**
	 * Returns true if the give $userAgent is from a mobile device
	 * @param string $userAgent
	 * @return bool
	 */
	public static function isSmartTV($userAgent) {
		if (self::$_is_smarttv !== null) return self::$_is_smarttv;
		self::$_is_smarttv = false;
		$userAgent = strtolower($userAgent);
		foreach (self::$smartTVBrowsers as $key) {
			if (strpos($userAgent, $key) !== false) {
				self::$_is_smarttv = true;
				break;
			}
		}
		return self::$_is_smarttv;
	}
	private static $_is_smarttv;
	
	/**
	 * Returns true if the give $userAgent is from a spam bot or crawler
	 * @param string $userAgent
	 * @return bool
	 */
	public static function isSpamOrCrawler($userAgent) {
		//$spamOrCrawlers = array("FunWebProducts", "Spam");		
		return self::checkIfContains($userAgent, "Spam") || self::checkIfContains($userAgent, "FunWebProducts");
	}
	
	/**
	 * 
	 * Returns the position of third occurrence of a ;(semi-column) if it exists 
	 * or the string length if no match is found 
	 * @param string $haystack
	 * @return int Char index of third semicolon or length
	 */
	public static function thirdSemiColumn($haystack) {
		$thirdSemiColumnIndex = self::ordinalIndexOf ( $haystack, ";", 3 );
		if ($thirdSemiColumnIndex < 0) {
			return strlen ( $haystack );
		}
		return $thirdSemiColumnIndex;
	}
	
	/**
	 * The nth($ordinal) occurance of $needle in $haystack or -1 if no match is found
	 * @param string $haystack
	 * @param string $needle
	 * @param int $ordinal
	 * @throws InvalidArgumentException
	 * @return int Char index of occurance
	 */
	public static function ordinalIndexOf($haystack, $needle, $ordinal) {
		if (is_null ( $haystack ) || empty ( $haystack )) {
			throw new InvalidArgumentException ( "haystack must not be null or empty" );
		}
		
		if (! is_integer ( $ordinal )) {
			throw new InvalidArgumentException ( "ordinal must be a positive ineger" );
		}
		
		$found = 0;
		$index = - 1;
		do {
			$index = strpos ( $haystack, $needle, $index + 1 );
			$index = is_int ( $index ) ? $index : - 1;
			if ($index < 0) {
				return $index;
			}
			$found ++;
		} while ( $found < $ordinal );
		
		return $index;
	
	}
	
	/**
	 * First occurance of a / character
	 * @param string $string Haystack
	 * @return int Char index
	 */
	public static function firstSlash($string) {
		$firstSlash = strpos($string, "/");
		return ($firstSlash !== false)? $firstSlash: strlen($string);
	}
	
	/**
	 * Second occurance of a / character
	 * @param string $string Haystack
	 * @return int Char index
	 */
	public static function secondSlash($string) {
		$firstSlash = strpos($string, "/");
		if ($firstSlash === false)
			return strlen($string);
		return strpos(substr($string, $firstSlash + 1), "/") + $firstSlash;
	}
	
	/**
	 * First occurance of a space character
	 * @param string $string Haystack
	 * @return int Char index
	 */
	public static function firstSpace($string) {
		$firstSpace = strpos($string, " ");
		return ($firstSpace === false)? strlen($string) : $firstSpace;
	}
	
	/**
	 * First occurance of a ; character or length
	 * @param string $string Haystack
	 * @return int Char index
	 */
	public static function firstSemiColonOrLength($string) {
		return self::firstMatchOrLength($string, ";");
	}
	
	/**
	 * First occurance of $toMatch string or length
	 * @param string $string Haystack
	 * @param string $toMatch Needle
	 * @return int Char index
	 */
	public static function firstMatchOrLength($string, $toMatch) {
		$firstMatch = strpos ( $string, $toMatch );
		return ($firstMatch === false) ? strlen($string): $firstMatch;
	}
	
	/**
	 * Returns true if $haystack contains $needle
	 * @param string $haystack Haystack
	 * @param string $needle Needle
	 * @return bool
	 */
	public static function checkIfContains($haystack, $needle) {
		return (strpos($haystack, $needle) !== false);
	}
	
	/**
	 * Returns true if $haystack contains any of the (string)needles in $needles
	 * @param string $haystack Haystack
	 * @param array $needles Array of (string)needles
	 * @return bool
	 */
	public static function checkIfContainsAnyOf($haystack, $needles) {
		foreach ($needles as $needle) {
			if (self::checkIfContains($haystack, $needle)) return true;
		}
		return false;
	}
	
	/**
	 * Returns true if $haystack contains all of the (string)needles in $needles
	 * @param string $haystack Haystack
	 * @param array $needles Array of (string)needles
	 * @return bool
	 */
	public static function checkIfContainsAll($haystack, $needles=array()) {
		foreach ($needles as $needle) {
			if (!self::checkIfContains($haystack, $needle)) return false;
		}
		return true;

	}
	
	/**
	 * Returns true if $haystack contains $needle without regard for case
	 * @param string $haystack Haystack
	 * @param string $needle Needle
	 * @return bool
	 */
	public static function checkIfContainsCaseInsensitive($haystack, $needle) {
		return stripos($haystack, $needle) !== false;
	}
	
	/**
	 * Returns true if $haystack starts with $needle
	 * @param string $haystack Haystack
	 * @param string $needle Needle
	 * @return bool
	 */
	public static function checkIfStartsWith($haystack, $needle) {
		return strpos($haystack, $needle) === 0;
	}
	
	/**
	 * Returns true if $haystack starts with any of the $needles
	 * @param string $haystack Haystack
	 * @param array $needles Array of (string)needles
	 * @return bool
	 */
	public static function checkIfStartsWithAnyOf($haystack, $needles) {
		if (is_array($needles)) {
			foreach ($needles as $needle) {
				if (strpos($haystack, $needle) === 0) {
					return true;
				}
			}
		}
		
		return false;
	}
	
	/**
	 * Returns the string position of the end of the RIS delimiter, or false if there is no RIS delimiter
	 * @param string $userAgent
	 * @return int|bool
	 */
	public static function toleranceToRisDelimeter($userAgent) {
		$tolerance = strpos($userAgent, WURFL_Constants::RIS_DELIMITER);
		if ($tolerance === false) {
			return false;
		}
		// Push the tolerance to the *end* of the RIS Delimiter
		return $tolerance + strlen(WURFL_Constants::RIS_DELIMITER);
	}
	
	/**
	 * Removes the locale portion from the userAgent
	 * @param string $userAgent
	 * @return string User agent without language string
	 */
	public static function removeLocale($userAgent) {
		return preg_replace('/; ?[a-z]{2}(?:-[a-zA-Z]{2})?(?:\.utf8|\.big5)?\b-?(?!:)/', '; xx-xx', $userAgent);
	}
}
