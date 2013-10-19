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
 * Provides global access to Tera-WURFL Constants
 * @package TeraWurfl
 *
 */
class WurflConstants {

    /**#@+
     * @var string WURFL IDs
     */
	const NO_MATCH = 'generic';
	const GENERIC = 'generic';
	const GENERIC_MOBILE = 'generic_mobile';
	const GENERIC_XHTML = 'generic_xhtml';
	const GENERIC_SMARTTV = 'generic_smarttv_browser';
	const GENERIC_WEB_BROWSER = 'generic_web_browser';
	const SIMPLE_DESKTOP_UA = 'HighPerformanceMatcher/';
	const RIS_DELIMITER = '---';
    /**#@-*/
	
	/**
     * @var array Array of UserAgentMatcher names
     */
	public static $matchers = array(
		/**** Java Midlets ****/
		'JavaMidlet',
	
		/**** Smart TVs ****/
		'SmartTV',
		
		/**** Mobile devices ****/
		'Kindle',
		'LGUPLUS',
	
		/**** Mobile platforms ****/
		'Android',
		'Apple',
		'WindowsPhoneDesktop',
		'WindowsPhone',
		'NokiaOviBrowser', // must come before the Nokia matcher
		
		/**** High workload mobile matchers ****/
		'Nokia',
		'Samsung',
		'BlackBerry',
		'SonyEricsson',
		'Motorola',
		
		/**** Other mobile matchers ****/
		'Alcatel',
		'BenQ',
		'DoCoMo',
		'Grundig',
		'HTCMac',
		'HTC',
		'Kddi',
		'Kyocera',
		'LG',
		'Maemo',
		'Mitsubishi',
		'Nec',
		'Nintendo',
		'Panasonic',
		'Pantech',
		'Philips',
		'Portalmmm',
		'Qtek',
		'Reksio',
		'Sagem',
		'Sanyo',
		'Sharp',
		'Siemens',
		'SPV',
		'Toshiba',
		'Vodafone',
		'WebOS',
		// Opera Mini goes after the specific mobile matchers
		'OperaMini',
		
		/**** Tablet Browsers ****/
		'WindowsRT',
	
		/**** Robots / Crawlers ****/
		'Bot',
		
		/**** Game Consoles ****/
		'Xbox',
		
		/**** Desktop Browsers ****/
		'Chrome',
		'Firefox',
		'MSIE',
		'Opera',
		'Safari',
		'Konqueror',
		
		/**** All other requests ****/
		'CatchAll',
	);
	
	/**
	 * These mobile browser strings will be compared case-insensitively, so keep them all lowercase for faster searching
	 * Keywords are in order of frequency of occurence (descending)
	 * @var array Keywords found in mobile browsers
	 */
	public static $MOBILE_BROWSERS = array(
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
	
	public static $SMARTTV_BROWSERS = array(
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
	);

	/**
     * @var array Keywords found in desktop browsers
     */
	public static $DESKTOP_BROWSERS = array(
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
    /**
     * @var array Keywords found in robots / crawlers
     */
	public static $ROBOTS = array(
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
}