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
 * @package    WURFL
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */

namespace Wurfl\Handlers;

use Wurfl\Constants;

/**
 * WURFL user agent hander utilities
 *
 * @package    WURFL
 */
class Utils
{
    /**
     * The worst allowed match tolerance
     *
     * @var integer
     */
    const WORST_MATCH = 7;

    /**
     * @var array Collection of mobile browser keywords
     */
    private static $mobileBrowsers = array(
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
        'crkey',
        'sonycebrowser',
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
     * Alias of \Wurfl\Handlers\Matcher\RISMatcher::match()
     *
     * @param array  $collection
     * @param string $needle
     * @param int    $tolerance
     *
     * @return string Matched user agent
     * @see \Wurfl\Handlers\Matcher\RISMatcher::match()
     */
    public static function risMatch($collection, $needle, $tolerance)
    {
        return Matcher\RISMatcher::getInstance()
            ->match($collection, $needle, $tolerance);
    }

    /**
     * Alias of \Wurfl\Handlers\Matcher\LDMatcher::match()
     *
     * @param array  $collection
     * @param string $needle
     * @param int    $tolerance
     *
     * @return string Matched user agent
     * @see \Wurfl\Handlers\Matcher\LDMatcher::match()
     */
    public static function ldMatch($collection, $needle, $tolerance = 7)
    {
        return Matcher\LDMatcher::getInstance()
            ->match($collection, $needle, $tolerance);
    }

    /**
     * Returns the character position (index) of the $target in $string, starting from a given index.  If target is not found, returns length of user agent.
     *
     * @param string $haystack      Haystack to be searched in
     * @param string $needle        Target string to search for
     * @param int    $startingIndex Character postition to start looking for the target
     *
     * @return int Character position (index) or full length
     */
    public static function indexOfOrLength($haystack, $needle, $startingIndex = 0)
    {
        $length = strlen($haystack);

        if ($startingIndex === false || $startingIndex > $length) {
            return $length;
        }

        $pos = strpos($haystack, $needle, $startingIndex);

        return ($pos === false) ? $length : $pos;
    }

    /**
     * Lowest char index of the first instance of any of the $needles found in $userAgent, starting at $startIndex;
     * if no match is found, the length of the string is returned
     *
     * @param string $userAgent     haystack
     * @param array  $needles       Array of (string)needles to search for in $userAgent
     * @param int    $startingIndex Char index for start of search
     *
     * @return int Char index of left-most match or length of string
     */
    public static function indexOfAnyOrLength($userAgent, $needles = array(), $startingIndex = 0)
    {
        if (count($needles) === 0) {
            return strlen($userAgent);
        }

        $min = strlen($userAgent);
        foreach ($needles as $needle) {
            $index = self::indexOfOrLength($userAgent, $needle, $startingIndex);
            if ($index < $min) {
                $min = $index;
            }
        }

        return $min;
    }

    /**
     * Resets cached detection variables for performance
     */
    public static function reset()
    {
        self::$isDesktopBrowser = null;
        self::$isMobileBrowser  = null;
        self::$isSmartTv        = null;
        self::$isRobot          = null;
    }

    /**
     * Returns true if the give $userAgent is from a mobile device
     *
     * @param string $userAgent
     *
     * @return bool
     */
    public static function isMobileBrowser($userAgent)
    {
        if (self::$isMobileBrowser !== null) {
            return self::$isMobileBrowser;
        }

        self::$isMobileBrowser = false;
        $userAgent             = strtolower($userAgent);

        foreach (self::$mobileBrowsers as $key) {
            if (strpos($userAgent, $key) !== false) {
                self::$isMobileBrowser = true;
                break;
            }
        }

        return self::$isMobileBrowser;
    }

    private static $isMobileBrowser;

    /**
     * Returns true if the give $userAgent is from a desktop device
     *
     * @param string $userAgent
     *
     * @return bool
     */
    public static function isDesktopBrowser($userAgent)
    {
        if (self::$isDesktopBrowser !== null) {
            return self::$isDesktopBrowser;
        }

        self::$isDesktopBrowser = false;
        $userAgent              = strtolower($userAgent);

        foreach (self::$desktopBrowsers as $key) {
            if (strpos($userAgent, $key) !== false) {
                self::$isDesktopBrowser = true;
                break;
            }
        }

        return self::$isDesktopBrowser;
    }

    private static $isDesktopBrowser;

    /**
     * Returns true if the give $userAgent is from a robot
     *
     * @param string $userAgent
     *
     * @return bool
     */
    public static function isRobot($userAgent)
    {
        if (self::$isRobot !== null) {
            return self::$isRobot;
        }

        self::$isRobot = false;
        $userAgent     = strtolower($userAgent);

        foreach (self::$robots as $key) {
            if (strpos($userAgent, $key) !== false) {
                self::$isRobot = true;
                break;
            }
        }

        return self::$isRobot;
    }

    private static $isRobot;

    /**
     * Is the given user agent very likely to be a desktop browser
     *
     * @param string $userAgent
     *
     * @return bool
     */
    public static function isDesktopBrowserHeavyDutyAnalysis($userAgent)
    {
        // Check Smart TV keywords
        if (Utils::isSmartTV($userAgent)) {
            return false;
        }

        // Chrome
        if (Utils::checkIfContains($userAgent, 'Chrome') && !Utils::checkIfContainsAnyOf(
                $userAgent,
                array('Android', 'Ventana')
            )
        ) {
            return true;
        }

        // Check mobile keywords
        if (Utils::isMobileBrowser($userAgent)) {
            return false;
        }

        if (Utils::checkIfContains(
            $userAgent,
            'PPC'
        )
        ) {
            return false;
        } // PowerPC; not always mobile, but we'll kick it out

        // Firefox;  fennec is already handled in the \Wurfl\Constants::$MOBILE_BROWSERS keywords
        if (Utils::checkIfContains($userAgent, 'Firefox') && !Utils::checkIfContains($userAgent, 'Tablet')) {
            return true;
        }

        // Safari
        if (preg_match(
            '#^Mozilla/5\.0 \((?:Macintosh|Windows)[^\)]+\) AppleWebKit/[\d\.]+ \(KHTML, like Gecko\) Version/[\d\.]+ ' . 'Safari/[\d\.]+$#',
            $userAgent
        )
        ) {
            return true;
        }

        // Opera Desktop
        if (Utils::checkIfStartsWith($userAgent, 'Opera/9.80 (Windows NT', 'Opera/9.80 (Macintosh')) {
            return true;
        }

        // Check desktop keywords
        if (Utils::isDesktopBrowser($userAgent)) {
            return true;
        }

        // Internet Explorer 11
        if (preg_match('/^Mozilla\/5\.0 \(Windows NT.+?Trident.+?; rv:\d\d\.\d+\)/', $userAgent)) {
            return true;
        }

        // Internet Explorer 9 or 10
        if (preg_match('/^Mozilla\/5\.0 \(compatible; MSIE (9|10)\.0; Windows NT \d\.\d/', $userAgent)) {
            return true;
        }

        // Internet Explorer <9
        if (preg_match('/^Mozilla\/4\.0 \(compatible; MSIE \d\.\d; Windows NT \d\.\d/', $userAgent)) {
            return true;
        }

        return false;
    }

    /**
     * Returns true if the give $userAgent is from a mobile device
     *
     * @param string $userAgent
     *
     * @return bool
     */
    public static function isSmartTV($userAgent)
    {
        if (self::$isSmartTv !== null) {
            return self::$isSmartTv;
        }
        self::$isSmartTv = false;
        $userAgent       = strtolower($userAgent);
        foreach (self::$smartTVBrowsers as $key) {
            if (strpos($userAgent, $key) !== false) {
                self::$isSmartTv = true;
                break;
            }
        }

        return self::$isSmartTv;
    }

    private static $isSmartTv;

    /**
     * Returns true if the give $userAgent is from a spam bot or crawler
     *
     * @param string $userAgent
     *
     * @return bool
     */
    public static function isSpamOrCrawler($userAgent)
    {
        return self::checkIfContains($userAgent, "Spam") || self::checkIfContains($userAgent, "FunWebProducts");
    }

    /**
     *
     * Returns the position of third occurrence of a ;(semi-column) if it exists
     * or the string length if no match is found
     *
     * @param string $haystack
     *
     * @return int Char index of third semicolon or length
     */
    public static function thirdSemiColumn($haystack)
    {
        $thirdSemiColumnIndex = self::ordinalIndexOf($haystack, ";", 3);
        if ($thirdSemiColumnIndex < 0) {
            return strlen($haystack);
        }

        return $thirdSemiColumnIndex;
    }

    /**
     * The nth($ordinal) occurance of $needle in $haystack or -1 if no match is found
     *
     * @param string $haystack
     * @param string $needle
     * @param int    $ordinal
     *
     * @throws \InvalidArgumentException
     * @return int Char index of occurance
     */
    public static function ordinalIndexOf($haystack, $needle, $ordinal)
    {
        if (is_null($haystack) || empty ($haystack)) {
            throw new \InvalidArgumentException("haystack must not be null or empty");
        }

        if (!is_integer($ordinal)) {
            throw new \InvalidArgumentException("ordinal must be a positive ineger");
        }

        $found = 0;
        $index = -1;
        do {
            $index = strpos($haystack, $needle, $index + 1);
            $index = is_int($index) ? $index : -1;
            if ($index < 0) {
                return $index;
            }
            $found++;
        } while ($found < $ordinal);

        return $index;
    }

    /**
     * First occurance of a / character
     *
     * @param string $string Haystack
     *
     * @return int Char index
     */
    public static function firstSlash($string)
    {
        $firstSlash = strpos($string, "/");

        return ($firstSlash !== false) ? $firstSlash : strlen($string);
    }

    /**
     * Second occurance of a / character
     *
     * @param string $string Haystack
     *
     * @return int Char index
     */
    public static function secondSlash($string)
    {
        $firstSlash = strpos($string, "/");
        if ($firstSlash === false) {
            return strlen($string);
        }

        return strpos(substr($string, $firstSlash + 1), "/") + $firstSlash;
    }

    /**
     * First occurance of a space character
     *
     * @param string $string Haystack
     *
     * @return int Char index
     */
    public static function firstSpace($string)
    {
        $firstSpace = strpos($string, " ");

        return ($firstSpace === false) ? strlen($string) : $firstSpace;
    }

    /**
     * First occurance of a ; character or length
     *
     * @param string $string Haystack
     *
     * @return int Char index
     */
    public static function firstSemiColonOrLength($string)
    {
        return self::firstMatchOrLength($string, ";");
    }

    /**
     * First occurance of $toMatch string or length
     *
     * @param string $string  Haystack
     * @param string $toMatch Needle
     *
     * @return int Char index
     */
    public static function firstMatchOrLength($string, $toMatch)
    {
        $firstMatch = strpos($string, $toMatch);

        return ($firstMatch === false) ? strlen($string) : $firstMatch;
    }

    /**
     * Returns true if $haystack contains $needle
     *
     * @param string $haystack Haystack
     * @param string $needle   Needle
     *
     * @return bool
     */
    public static function checkIfContains($haystack, $needle)
    {
        return (strpos($haystack, $needle) !== false);
    }

    /**
     * Returns true if $haystack contains any of the (string)needles in $needles
     *
     * @param string $haystack Haystack
     * @param array  $needles  Array of (string)needles
     *
     * @return bool
     */
    public static function checkIfContainsAnyOf($haystack, $needles)
    {
        foreach ($needles as $needle) {
            if (self::checkIfContains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns true if $haystack contains all of the (string)needles in $needles
     *
     * @param string $haystack Haystack
     * @param array  $needles  Array of (string)needles
     *
     * @return bool
     */
    public static function checkIfContainsAll($haystack, $needles = array())
    {
        foreach ($needles as $needle) {
            if (!self::checkIfContains($haystack, $needle)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns true if $haystack contains $needle without regard for case
     *
     * @param string $haystack Haystack
     * @param string $needle   Needle
     *
     * @return bool
     */
    public static function checkIfContainsCaseInsensitive($haystack, $needle)
    {
        return stripos($haystack, $needle) !== false;
    }

    /**
     * Returns true if $haystack starts with $needle
     *
     * @param string $haystack Haystack
     * @param string $needle   Needle
     *
     * @return bool
     */
    public static function checkIfStartsWith($haystack, $needle)
    {
        return strpos($haystack, $needle) === 0;
    }

    /**
     * Returns true if $haystack starts with any of the $needles
     *
     * @param string $haystack Haystack
     * @param array  $needles  Array of (string)needles
     *
     * @return bool
     */
    public static function checkIfStartsWithAnyOf($haystack, $needles)
    {
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
     *
     * @param string $userAgent
     *
     * @return int|bool
     */
    public static function toleranceToRisDelimeter($userAgent)
    {
        $tolerance = strpos($userAgent, Constants::RIS_DELIMITER);
        if ($tolerance === false) {
            return false;
        }

        // Push the tolerance to the *end* of the RIS Delimiter
        return $tolerance + strlen(Constants::RIS_DELIMITER);
    }

    /**
     * Removes the locale portion from the userAgent
     *
     * @param string $userAgent
     *
     * @return string User agent without language string
     */
    public static function removeLocale($userAgent)
    {
        return preg_replace('/; ?[a-z]{2}(?:-r?[a-zA-Z]{2})?(?:\.utf8|\.big5)?\b-?(?!:)/', '; xx-xx', $userAgent);
    }
}
